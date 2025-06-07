<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Weather\CurrentWeatherDto;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class WeatherService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string              $weatherApiBaseUrl
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function fetchCurrentWeather(float $latitude, float $longitude): ?CurrentWeatherDto
    {
        try {
            $response = $this->httpClient->request('GET', $this->weatherApiBaseUrl, [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'hourly' => 'temperature_2m',
                    'current' => 'temperature_2m,relative_humidity_2m,precipitation,rain',
                    'timezone' => 'auto',
                ],
            ]);

            // This will throw for >=300 status codes or decoding issues
            $data = $response->toArray();

            if (!isset($data['current'])) {
                throw new RuntimeException('Current weather data not found in API response payload.');
            }

            return CurrentWeatherDto::createFromArray($data['current']);

        } catch (ServerExceptionInterface $e) {
            $response = $e->getResponse();
            throw new RuntimeException(sprintf(
                'Weather API server error for lat/lon %.2f/%.2f: HTTP %d returned for "%s". Original: %s',
                $latitude,
                $longitude,
                $response->getStatusCode(),
                $response->getInfo('url') ?? $this->weatherApiBaseUrl,
                $e->getMessage()
            ), 0, $e);
        } catch (ClientExceptionInterface $e) {
            $response = $e->getResponse();
            throw new RuntimeException(sprintf(
                'Weather API client error for lat/lon %.2f/%.2f: HTTP %d returned for "%s". Original: %s',
                $latitude,
                $longitude,
                $response->getStatusCode(),
                $response->getInfo('url') ?? $this->weatherApiBaseUrl,
                $e->getMessage()
            ), 0, $e);
        } catch (DecodingExceptionInterface $e) {
            throw new RuntimeException(sprintf(
                'Failed to decode Weather API response for lat/lon %.2f/%.2f. Original: %s',
                $latitude,
                $longitude,
                $e->getMessage()
            ), 0, $e);
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException(sprintf(
                'Transport error while fetching weather for lat/lon %.2f/%.2f. Original: %s',
                $latitude,
                $longitude,
                $e->getMessage()
            ), 0, $e);
        } catch (RedirectionExceptionInterface $e) {
            $response = $e->getResponse();
            throw new RuntimeException(sprintf(
                'Weather API redirection error for lat/lon %.2f/%.2f: HTTP %d returned for "%s". Original: %s',
                $latitude,
                $longitude,
                $response->getStatusCode(),
                $response->getInfo('url') ?? $this->weatherApiBaseUrl,
                $e->getMessage()
            ), 0, $e);
        }
        // No generic \Throwable catch here
    }
}
