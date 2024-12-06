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
            $data = $this->apiService->fetchSomething('football-get-trendingnews');
            $news = $data['response']['news'];
            $formattedNews = array_map(function ($new) {
                return [
                    'imageUrl' => $new['imageUrl'],
                    'title' => $new['title'],
                    'time' => $new['gmtTime'],
                    'src' => $new['sourceStr'],
                    'url' => $new['page']['url'],
                ];
            }, $news);

            return response()->json($formattedNews, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function leagueNews($id): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('football-get-league-news', [
                'leagueid' => $id,
            ]);
            $news = $data['response']['news'];
            $formattedNews = array_map(function ($new) {
                return [
                    'imageUrl' => $new['imageUrl'],
                    'title' => $new['title'],
                    'time' => $new['gmtTime'],
                    'src' => $new['sourceStr'],
                    'url' => $new['page']['url'],
                ];
            }, $news);

            return response()->json($formattedNews, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
