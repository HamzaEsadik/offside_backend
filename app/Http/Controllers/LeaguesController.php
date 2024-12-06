<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\JsonResponse;

class LeaguesController extends Controller
{

    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    //return list of leagues
    public function leagues(): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('football-get-all-leagues-with-countries');
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //return league name and logo
    public function league($id): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('football-get-league-detail', [
                'leagueid' => $id,
            ]);
            $league = $data['response']['leagues'];
            $formattedData = [
                'id' => $league['id'],
                'name' => $league['name'],
            ];
            return response()->json($formattedData, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //return league table
    public function table($id): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('football-get-standing-all', [
                'leagueid' => $id,
            ]);

            $formattedData = array_map(function ($item) {
                return [
                    'id' => $item['id'],
                    'rank' => $item['idx'],
                    'team_name' => $item['name'],
                    'points' => $item['pts'],
                    'matches' => $item['played'],
                    'win' => $item['wins'],
                    'draw' => $item['draws'],
                    'lose' => $item['losses'],
                    'goals' => $item['goalConDiff'],
                ];
            }, $data['response']['standing']);

            return response()->json($formattedData, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //return league statisctics
    public function statistics($id): JsonResponse
    {
        try {
            $goals = $this->apiService->fetchSomething('football-get-top-players-by-goals', [
                'leagueid' => $id,
            ]);
            $assists = $this->apiService->fetchSomething('football-get-top-players-by-assists', [
                'leagueid' => $id,
            ]);
            $rating = $this->apiService->fetchSomething('football-get-top-players-by-rating', [
                'leagueid' => $id,
            ]);

            $formattedAssists = array_map(function ($player) {
                return [
                    'id' => $player['id'],
                    'name' => $player['name'],
                    'teamId' => $player['teamId'],
                    'teamName' => $player['teamName'],
                    'assists' => $player['assists'],
                ];
            }, $assists['response']['players'] ?? []);
    
            $formattedGoals = array_map(function ($player) {
                return [
                    'id' => $player['id'],
                    'name' => $player['name'],
                    'teamId' => $player['teamId'],
                    'teamName' => $player['teamName'],
                    'goals' => $player['goals'],
                ];
            }, $goals['response']['players'] ?? []);
    
            $formattedRating = array_map(function ($player) {
                return [
                    'id' => $player['id'],
                    'name' => $player['name'],
                    'teamId' => $player['teamId'],
                    'teamName' => $player['teamName'],
                    'rating' => $player['rating'],
                ];
            }, $rating['response']['players'] ?? []);
    
            $response = [
                'assists' => $formattedAssists,
                'goals' => $formattedGoals,
                'rating' => $formattedRating,
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
