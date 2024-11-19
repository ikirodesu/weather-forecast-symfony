<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class UserPreferenceService
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function storeRecentSearch(string $city): void
    {
        $city = trim($city);
        $recentSearches = $this->session->get('recentSearches', []);

        if (!in_array($city, $recentSearches, true)) {
            array_unshift($recentSearches, $city);
            if (count($recentSearches) > 5) {
                array_pop($recentSearches);
            }
            $this->session->set('recentSearches', $recentSearches);
        }
    }

    public function toggleUnits(): void
    {
        $currentUnit = $this->session->get('unit', 'metric');
        $newUnit = $currentUnit === 'metric' ? 'imperial' : 'metric';
        $this->session->set('unit', $newUnit);
    }
}