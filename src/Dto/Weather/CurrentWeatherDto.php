<?php

declare(strict_types=1);

namespace App\Dto\Weather;

readonly class CurrentWeatherDto
{
    public function __construct(
        public string $time,
        public float  $temperature,
        public int    $relativeHumidity,
        public float  $precipitation,
        public float  $rain
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            time: $data['time'],
            temperature: $data['temperature_2m'],
            relativeHumidity: $data['relative_humidity_2m'],
            precipitation: $data['precipitation'],
            rain: $data['rain']
        );
    }
}
