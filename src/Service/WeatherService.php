<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey = '')
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function fetchWeatherData(string $city, string $unit): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API ключ не предоставлен');
        }

        $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
            'query' => [
                'q' => $city,
                'units' => $unit,
                'appid' => $this->apiKey,
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            $data = $response->toArray(false);
            $message = $data['message'] ?? 'Неизвестная ошибка';
            throw new \Exception($message);
        }

        return $response->toArray();
    }
}