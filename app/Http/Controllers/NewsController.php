<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function news(): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('/football-get-trendingnews');
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
