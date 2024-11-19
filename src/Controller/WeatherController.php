<?php

namespace App\Controller;

namespace App\Controller;

use App\Service\WeatherService;
use App\Service\UserPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\WeatherSearchType;
use App\Exception\WeatherServiceException;

class WeatherController extends AbstractController
{
    private $weatherService;
    private $userPreferenceService;

    public function __construct(WeatherService $weatherService, UserPreferenceService $userPreferenceService)
    {
        $this->weatherService = $weatherService;
        $this->userPreferenceService = $userPreferenceService;
    }

    #[Route('/', name: 'weather_index', methods: ['GET'])]
    public function index(SessionInterface $session)
    {
        $recentSearches = $session->get('recentSearches', []);
        $unit = $session->get('unit', 'metric');

        $form = $this->createForm(WeatherSearchType::class);

        return $this->render('weather/index.html.twig', [
            'recentSearches' => $recentSearches,
            'unit' => $unit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/search', name: 'weather_search', methods: ['POST'])]
    public function search(Request $request, SessionInterface $session)
    {
        $form = $this->createForm(WeatherSearchType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Неверные данные формы',
            ]);
        }

        $city = $request->request->get('city');
        $unit = $session->get('unit', 'metric');

        try {
            $weatherData = $this->weatherService->fetchWeatherData($city, $unit);

            $this->userPreferenceService->storeRecentSearch($city);

            return new JsonResponse([
                'success' => true,
                'weatherData' => $weatherData,
                'unit' => $unit,
                'recentSearches' => $session->get('recentSearches', [])
            ]);
        } catch (WeatherServiceException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Произошла ошибка'
            ]);
        }
    }

    #[Route('/toggle-units', name: 'toggle_units', methods: ['POST'])]
    public function toggleUnits(SessionInterface $session)
    {
        $this->userPreferenceService->toggleUnits($session);

        return $this->redirectToRoute('weather_index');
    }
}