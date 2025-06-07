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
}
