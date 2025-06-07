<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Dto\Geocoding\GeocodingResultDto;
use App\Service\GeocodingService;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GeocodingServiceTest extends TestCase
{
    private const string GEOCODING_API_URL = 'https://geocoding-api.open-meteo.com/v1/search';

    /**
     * @throws TransportExceptionInterface
     */
    public function testFetchCoordinatesForCitySuccess(): void
    {
        $cityName = 'Berlin';
        $mockResponseJson = json_encode([
            "results" => [
                [
                    "id" => 2511767,
                    "name" => "Rociana",
                    "latitude" => 37.30899,
                    "longitude" => -6.59564,
                    "elevation" => 106,
                    "feature_code" => "PPL",
                    "country_code" => "ES",
                    "admin1_id" => 2593109,
                    "admin2_id" => 2516547,
                    "admin3_id" => 6358237,
                    "timezone" => "Europe/Madrid",
                    "country_id" => 2510769,
                    "country" => "Spain",
                    "admin1" => "Andalusia",
                    "admin2" => "Huelva",
                    "admin3" => "Rociana del Condado"
                ]
            ],
            "generationtime_ms" => 0.49602985
        ]);

        if (false === $mockResponseJson) {
            $this->fail('Failed to encode mock JSON response.');
        }
        $mockResponse = new MockResponse($mockResponseJson, ['http_code' => 200]);

        $httpClient = new MockHttpClient($mockResponse);

        $service = new GeocodingService($httpClient, self::GEOCODING_API_URL);
        $result = $service->fetchCoordinatesForCity($cityName);

        $this->assertInstanceOf(GeocodingResultDto::class, $result);
        $this->assertEquals('Rociana', $result->name);
        $this->assertEquals(37.30899, $result->latitude);
        $this->assertEquals(-6.59564, $result->longitude);

        $this->assertEquals('GET', $mockResponse->getRequestMethod());

        $requestOptions = $mockResponse->getRequestOptions();
        $this->assertArrayHasKey('query', $requestOptions);
        $this->assertEquals([
            'name' => 'Berlin', // Corrected: should match $cityName
            'count' => 1,
            'language' => 'en',
            'format' => 'json',
        ], $requestOptions['query']);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFetchCoordinatesForCityNotFound(): void
    {
        $cityName = 'NonExistentCity123';
        $mockResponseJson = json_encode([
            'results' => []
        ]);

        if (false === $mockResponseJson) {
            $this->fail('Failed to encode mock JSON response.');
        }
        $mockResponse = new MockResponse($mockResponseJson, ['http_code' => 200]);
        $httpClient = new MockHttpClient($mockResponse);

        $service = new GeocodingService($httpClient, self::GEOCODING_API_URL);
        $result = $service->fetchCoordinatesForCity($cityName);

        $this->assertNull($result);

        $this->assertEquals('GET', $mockResponse->getRequestMethod());
        $requestOptions = $mockResponse->getRequestOptions();
        $this->assertArrayHasKey('query', $requestOptions);
        $this->assertEquals([
            'name' => 'NonExistentCity123',
            'count' => 1,
            'language' => 'en',
            'format' => 'json',
        ], $requestOptions['query']);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFetchCoordinatesApiError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/^Geocoding API server error for city "TestCity": HTTP 500 returned for ".+"\. Original: HTTP 500 returned for ".+"\.$/'
        );

        $cityName = 'TestCity';
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResponse);

        $service = new GeocodingService($httpClient, self::GEOCODING_API_URL);

        $service->fetchCoordinatesForCity($cityName);
    }
}
