<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.api.base_url');
    }

    public function fetchSomething($endpoint, $params = [])
    {

        $response = Http::withHeaders([
            'x-rapidapi-key' => config('services.api.api_key'),
            'x-rapidapi-host' => config('services.api.api_host'),
        ])
        ->get($this->baseUrl . $endpoint, $params);

        return $response->json();
    }
}
