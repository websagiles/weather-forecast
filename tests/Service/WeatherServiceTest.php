<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Dto\Weather\CurrentWeatherDto;
use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WeatherServiceTest extends TestCase
{
    private const string WEATHER_API_URL = 'https://api.open-meteo.com/v1/forecast';

    /**
     * @throws TransportExceptionInterface
     */
    public function testFetchCurrentWeatherSuccess(): void
    {
        $latitude = 52.52;
        $longitude = 13.41;
        $mockResponseJson = json_encode([
            'current' => [
                'time' => '2023-10-27T10:00',
                'temperature_2m' => 12.5,
                'relative_humidity_2m' => 70,
                'precipitation' => 0.0,
                'rain' => 0.0
            ]
        ]);
        if (false === $mockResponseJson) {
            $this->fail('Failed to encode mock JSON response.');
        }

        $mockResponse = new MockResponse($mockResponseJson, ['http_code' => 200]);
        $httpClient = new MockHttpClient($mockResponse);

        $service = new WeatherService($httpClient, self::WEATHER_API_URL);
        $result = $service->fetchCurrentWeather($latitude, $longitude);

        $this->assertInstanceOf(CurrentWeatherDto::class, $result);
        $this->assertEquals(12.5, $result->temperature);
        $this->assertEquals(70, $result->relativeHumidity);
        $this->assertEquals('GET', $mockResponse->getRequestMethod());
        $requestOptions = $mockResponse->getRequestOptions();
        $this->assertArrayHasKey('query', $requestOptions);
        $this->assertEquals([
            'latitude' => 52.52,
            'longitude' => 13.41,
            'hourly' => 'temperature_2m',
            'current' => 'temperature_2m,relative_humidity_2m,precipitation,rain',
            'timezone' => 'auto',
        ], $requestOptions['query']);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFetchCurrentWeatherApiError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/^Weather API server error for lat\/lon 50\.00\/10\.00: HTTP 500 returned for ".+"\. Original: HTTP 500 returned for ".+"\.$/'
        );

        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResponse);

        $service = new WeatherService($httpClient, self::WEATHER_API_URL);
        $service->fetchCurrentWeather(50.0, 10.0);
    }
}
