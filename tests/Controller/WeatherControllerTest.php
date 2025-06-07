<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Dto\Geocoding\GeocodingResultDto;
use App\Dto\Weather\CurrentWeatherDto;
use App\Service\GeocodingService;
use App\Service\WeatherService;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WeatherControllerTest extends WebTestCase
{
    public function testIndexPageLoadsAndFormRenders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="city_form"]');
        $this->assertSelectorTextContains('h1', 'Weather Forecast');
    }

    /**
     * @throws Exception
     */
    public function testSubmitCityAndDisplayWeather(): void
    {
        $client = static::createClient();

        // Mock services
        $geocodingServiceMock = $this->createMock(GeocodingService::class);
        $geocodingServiceMock->method('fetchCoordinatesForCity')
            ->with('Berlin')
            ->willReturn(new GeocodingResultDto(1, 'Berlin', 52.52, 13.41, 'Germany'));

        $weatherServiceMock = $this->createMock(WeatherService::class);
        $weatherServiceMock->method('fetchCurrentWeather')
            ->with(52.52, 13.41)
            ->willReturn(new CurrentWeatherDto('2023-01-01T12:00', 10.5, 80, 0.0, 0.0));

        // Get the test container and replace the services
        /** @var ContainerInterface $container */
        $container = static::getContainer();
        $container->set(GeocodingService::class, $geocodingServiceMock);
        $container->set(WeatherService::class, $weatherServiceMock);

        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('Get Weather')->form([
            'city_form[city]' => 'Berlin',
        ]);
        $client->submit($form);

        // DEBUG: Output response content
        $responseContent = $client->getResponse()->getContent();
        file_put_contents('/tmp/debug_weather_berlin.html', $responseContent);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Weather in Berlin');
        $this->assertSelectorTextContains('div.weather-data', 'Temperature:');
        $this->assertSelectorTextContains('div.weather-data', '°C');
        $this->assertSelectorTextContains('div.weather-data', 'Relative Humidity:');
        $this->assertSelectorTextContains('div.weather-data', '%');
        $this->assertSelectorTextContains('div.weather-data', 'Precipitation:');
        $this->assertSelectorTextContains('div.weather-data', 'mm');
    }

    /**
     * @throws Exception
     */
    public function testSubmitNonExistentCity(): void
    {
        $client = static::createClient();

        $geocodingServiceMock = $this->createMock(GeocodingService::class);
        $geocodingServiceMock->method('fetchCoordinatesForCity')
            ->with('NonExistentCity123')
            ->willReturn(null);

        /** @var ContainerInterface $container */
        $container = static::getContainer();
        $container->set(GeocodingService::class, $geocodingServiceMock);
        // WeatherService should not be called
        $container->set(WeatherService::class, $this->createMock(WeatherService::class));


        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('Get Weather')->form([
            'city_form[city]' => 'NonExistentCity123',
        ]);
        $client->submit($form);

        // DEBUG: Output response content
        $responseContent = $client->getResponse()->getContent();
        file_put_contents('/tmp/debug_weather_nonexistent.html', $responseContent);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-warning', 'City "NonExistentCity123" not found.');
    }
}
