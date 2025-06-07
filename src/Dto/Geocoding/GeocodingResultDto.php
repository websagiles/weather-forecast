<?php

declare(strict_types=1);

namespace App\Dto\Geocoding;

readonly class GeocodingResultDto
{
    public function __construct(
        public int     $id,
        public string  $name,
        public float   $latitude,
        public float   $longitude,
        public ?string  $timezone = null,
        public ?string $country = null,
        public ?string $admin1 = null,
        public ?string $admin2 = null,
        public ?string $admin3 = null,
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            latitude: $data['latitude'],
            longitude: $data['longitude'],
            timezone: $data['timezone'] ?? null,
            country: $data['country'] ?? null,
            admin1: $data['admin1'] ?? null,
            admin2: $data['admin2'] ?? null,
            admin3: $data['admin3'] ?? null,
        );
    }
}
