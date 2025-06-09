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
    /**
     * Obtiene datos completos de Open-Meteo (current, daily, hourly) para la app.
     * Devuelve un array con las claves: current, forecast, timezone, timezone_abbreviation
     */
    public function fetchCurrentWeather(float $latitude, float $longitude): ?array
    {
        try {
            $response = $this->httpClient->request('GET', $this->weatherApiBaseUrl, [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,apparent_temperature_max,apparent_temperature_min,sunrise,sunset,daylight_duration,sunshine_duration,uv_index_max,uv_index_clear_sky_max,rain_sum,showers_sum,snowfall_sum,precipitation_sum,precipitation_hours,precipitation_probability_max,wind_speed_10m_max,wind_gusts_10m_max,wind_direction_10m_dominant,shortwave_radiation_sum,et0_fao_evapotranspiration',
                    'hourly' => 'temperature_2m',
                    'current' => 'temperature_2m,relative_humidity_2m,precipitation,rain,is_day,cloud_cover,weather_code,apparent_temperature,showers,snowfall,pressure_msl,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'timezone' => 'auto',
                    'past_days' => 5,
                    'timeformat' => 'unixtime',
                    'precipitation_unit' => 'inch',
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['current']) || !isset($data['daily'])) {
                throw new RuntimeException('Weather data not found in API response payload.');
            }

            return [
                'current' => $data['current'],
                'forecast' => $data['daily'],
                'timezone' => $data['timezone'] ?? null,
                'timezone_abbreviation' => $data['timezone_abbreviation'] ?? null,
            ];

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
