<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\CityFormType;
use App\Service\GeocodingService;
use App\Service\WeatherService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WeatherController extends AbstractController
{
    public function __construct(
        private readonly GeocodingService $geocodingService,
        private readonly WeatherService   $weatherService
    )
    {
    }

    #[Route('/', name: 'app_weather_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(CityFormType::class);
        $form->handleRequest($request);

        $weatherData = null;
        $geocodingResult = null;
        $error = null;
        $cityName = null;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{city: string} $data */
            $data = $form->getData();
            $cityName = $data['city'];

            try {
                $geocodingResult = $this->geocodingService->fetchCoordinatesForCity($cityName);

                if ($geocodingResult) {
                    $weatherData = $this->weatherService->fetchCurrentWeather(
                        $geocodingResult->latitude,
                        $geocodingResult->longitude
                    );
                } else {
                    $this->addFlash('warning', sprintf('City "%s" not found.', $cityName));
                }
            } catch (RuntimeException $e) {
                // Log the exception with a PSR logger
                $this->addFlash('danger', 'Could not retrieve weather data at this time. Please try again later.');
                $error = $e->getMessage(); // For debugging, not usually for prod display
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger', '');
                $error = $e->getMessage();
            }
        }

        return $this->render('weather/index.html.twig', [
            'form' => $form->createView(),
            'weatherData' => $weatherData,
            'geocodingResult' => $geocodingResult,
            'cityName' => $cityName, // To display even if not found
            'error' => $error, // For development context, or more generic message in prod
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/api/geocode', name: 'api_geocode', methods: ['GET'])]
    public function apiGeocode(Request $request): Response
    {
        $city = $request->query->get('city');
        if (!$city) {
            return $this->json(['found' => false]);
        }

        $result = $this->geocodingService->fetchCoordinatesForCity($city);

        return $this->json(['found' => $result !== null]);
    }

    public function getWeatherInfo(int $code, string $size = 'w-20 h-20'): Response
    {
        switch ($code) {
            case 0:
                $description = 'Clear sky';
                $icon = 'sun';
                $class = "$size text-yellow-500 bg-linear-to-t from-blue-400 to-blue-600";
                break;
            case 1:
                $description = 'Mainly clear';
                $icon = 'sun';
                $class = "$size text-yellow-400 bg-linear-to-t from-blue-400 to-blue-600";
                break;
            case 2:
                $description = 'Partly cloudy';
                $icon = 'cloud';
                $class = "$size text-gray-400 bg-linear-to-t from-blue-400 to-gray-500";
                break;
            case 3:
                $description = 'Overcast';
                $icon = 'cloud';
                $class = "$size text-gray-500 bg-linear-to-t from-gray-400 to-gray-600";
                break;
            case 45:
                $description = 'Fog';
                $icon = 'eye';
                $class = "$size text-gray-400 bg-linear-to-t from-gray-300 to-gray-500";
                break;
            case 48:
                $description = 'Depositing rime fog';
                $icon = 'eye';
                $class = "$size text-gray-500 bg-linear-to-t from-gray-300 to-gray-500";
                break;
            case 51:
                $description = 'Light drizzle';
                $icon = 'cloud-drizzle';
                $class = "$size text-blue-400 bg-linear-to-t from-gray-400 to-blue-500";
                break;
            case 53:
                $description = 'Moderate drizzle';
                $icon = 'cloud-drizzle';
                $class = "$size text-blue-500 bg-linear-to-t from-gray-400 to-blue-500";
                break;
            case 55:
                $description = 'Dense drizzle';
                $icon = 'cloud-drizzle';
                $class = "$size text-blue-600 bg-linear-to-t from-gray-400 to-blue-600";
                break;
            case 61:
                $description = 'Slight rain';
                $icon = 'cloud-rain';
                $class = "$size text-blue-500 bg-linear-to-t from-gray-500 to-blue-600";
                break;
            case 63:
                $description = 'Moderate rain';
                $icon = 'cloud-rain';
                $class = "$size text-blue-600 bg-linear-to-t from-gray-500 to-blue-700";
                break;
            case 65:
                $description = 'Heavy rain';
                $icon = 'cloud-rain';
                $class = "$size text-blue-700 bg-linear-to-t from-gray-600 to-blue-800";
                break;
            case 71:
                $description = 'Slight snow';
                $icon = 'cloud-snow';
                $class = "$size text-blue-200 bg-linear-to-t from-gray-300 to-blue-400";
                break;
            case 73:
                $description = 'Moderate snow';
                $icon = 'cloud-snow';
                $class = "$size text-blue-300 bg-linear-to-t from-gray-400 to-blue-500";
                break;
            case 75:
                $description = 'Heavy snow';
                $icon = 'snowFlake';
                $class = "$size text-blue-400 bg-linear-to-t from-gray-500 to-blue-600";
                break;
            case 95:
                $description = 'Thunderstorm';
                $icon = 'cloud-lightning';
                $class = "$size text-yellow-400 bg-linear-to-t from-gray-700 to-purple-800";
                break;
            case 96:
                $description = 'Thunderstorm with hail';
                $icon = 'zap';
                $class = "$size text-yellow-300 bg-linear-to-t from-gray-700 to-purple-900";
                break;
            case 99:
                $description = 'Thunderstorm with heavy hail';
                $icon = 'zap';
                $class = "$size text-yellow-200 bg-linear-to-t from-gray-800 to-purple-900";
                break;
            default:
                $description = 'Clear sky';
                $icon = 'sun';
                $class = "$size text-yellow-500 bg-linear-to-t from-blue-400 to-blue-600";
        }

        return $this->render('weather/info.html.twig', [
            'description' => $description,
            'icon' => $icon,
            'class' => $class
        ]);
    }
}
