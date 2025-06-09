<?php

declare(strict_types=1);

namespace App\Dto\Weather;

readonly class ForecastWeatherDto
{
    /**
     * @param array<string, mixed> $data
     * @return self[]
     */
    public static function createFromArray(array $data): array
    {
        $result = [];
        if (!isset($data['time'])) {
            return $result;
        }
        $count = count($data['time']);
        for ($i = 0; $i < $count; $i++) {
            $result[] = new self(
                $data['time'][$i] ?? null,
                $data['weather_code'][$i] ?? null,
                $data['temperature_2m_max'][$i] ?? null,
                $data['temperature_2m_min'][$i] ?? null,
                $data['apparent_temperature_max'][$i] ?? null,
                $data['apparent_temperature_min'][$i] ?? null,
                $data['sunrise'][$i] ?? null,
                $data['sunset'][$i] ?? null,
                $data['daylight_duration'][$i] ?? null,
                $data['sunshine_duration'][$i] ?? null,
                $data['uv_index_max'][$i] ?? null,
                $data['uv_index_clear_sky_max'][$i] ?? null,
                $data['rain_sum'][$i] ?? null,
                $data['showers_sum'][$i] ?? null,
                $data['snowfall_sum'][$i] ?? null,
                $data['precipitation_sum'][$i] ?? null,
                $data['precipitation_hours'][$i] ?? null,
                $data['precipitation_probability_max'][$i] ?? null,
                $data['wind_speed_10m_max'][$i] ?? null,
                $data['wind_gusts_10m_max'][$i] ?? null,
                $data['wind_direction_10m_dominant'][$i] ?? null,
                $data['shortwave_radiation_sum'][$i] ?? null,
                $data['et0_fao_evapotranspiration'][$i] ?? null
            );
        }
        return $result;
    }

    public function __construct(
        public ?int $time,
        public ?int $weatherCode,
        public ?float $tempMax,
        public ?float $tempMin,
        public ?float $apparentTempMax,
        public ?float $apparentTempMin,
        public ?int $sunrise,
        public ?int $sunset,
        public ?int $daylightDuration,
        public ?int $sunshineDuration,
        public ?float $uvIndexMax,
        public ?float $uvIndexClearSkyMax,
        public ?float $rainSum,
        public ?float $showersSum,
        public ?float $snowfallSum,
        public ?float $precipitationSum,
        public ?float $precipitationHours,
        public ?float $precipitationProbabilityMax,
        public ?float $windSpeed10mMax,
        public ?float $windGusts10mMax,
        public ?int $windDirection10mDominant,
        public ?float $shortwaveRadiationSum,
        public ?float $et0FaoEvapotranspiration
    ) {}
}
