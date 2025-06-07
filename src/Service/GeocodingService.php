<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Geocoding\GeocodingResultDto;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class GeocodingService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string              $geocodingApiBaseUrl
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function fetchCoordinatesForCity(string $cityName): ?GeocodingResultDto
    {
        try {
            $response = $this->httpClient->request('GET', $this->geocodingApiBaseUrl, [
                'query' => [
                    'name' => $cityName,
                    'count' => 1,
                    'language' => 'en',
                    'format' => 'json',
                ],
            ]);

            $data = $response->toArray();

            if (empty($data['results'])) {
                return null;
            }

            return GeocodingResultDto::createFromArray($data['results'][0]);

        } catch (ServerExceptionInterface $e) {
            $response = $e->getResponse();
            throw new RuntimeException(sprintf(
                'Geocoding API server error for city "%s": HTTP %d returned for "%s". Original: %s',
                $cityName,
                $response->getStatusCode(),
                $response->getInfo('url') ?? $this->geocodingApiBaseUrl,
                $e->getMessage()
            ), 0, $e);
        } catch (ClientExceptionInterface $e) {
            $response = $e->getResponse();
            throw new RuntimeException(sprintf(
                'Geocoding API client error for city "%s": HTTP %d returned for "%s". Original: %s',
                $cityName,
                $response->getStatusCode(),
                $response->getInfo('url') ?? $this->geocodingApiBaseUrl,
                $e->getMessage()
            ), 0, $e);
        } catch (DecodingExceptionInterface $e) {
            throw new RuntimeException(sprintf(
                'Failed to decode Geocoding API response for city "%s". Original: %s',
                $cityName,
                $e->getMessage()
            ), 0, $e);
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException(sprintf(
                'Transport error while fetching geocoding data for city "%s". Original: %s',
                $cityName,
                $e->getMessage()
            ), 0, $e);
        } catch (RedirectionExceptionInterface $e) {
            $response = $e->getResponse();
            throw new RuntimeException(sprintf(
                'Geocoding API redirection error for city "%s": HTTP %d returned for "%s". Original: %s',
                $cityName,
                $response->getStatusCode(),
                $response->getInfo('url') ?? $this->geocodingApiBaseUrl,
                $e->getMessage()
            ), 0, $e);
        }
    }
}
