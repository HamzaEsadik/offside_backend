<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\JsonResponse;

class FixturesController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    //list of top leagues fixtures
    public function topLeaguesFixtures($date): JsonResponse
{
    $leagues_ids = [47, 42, 87, 54, 73, 53, 55, 536];
    $results = [];

    try {
        $data = $this->apiService->fetchSomething('football-get-matches-by-date', [
            'date' => $date,
        ]);

        $matches = $data['response']['matches'];

        // Group matches by league
        $groupedMatches = [];
        foreach ($matches as $match) {
            $leagueId = $match['leagueId'];
            if (!isset($groupedMatches[$leagueId])) {
                $groupedMatches[$leagueId] = [];
            }
            $groupedMatches[$leagueId][] = [
                'id' => $match['id'],
                'time' => $match['time'],
                'home' => [
                    'id' => $match['home']['id'],
                    'score' => $match['home']['score'],
                    'name' => $match['home']['name'],
                ],
                'away' => [
                    'id' => $match['away']['id'],
                    'score' => $match['away']['score'],
                    'name' => $match['away']['name'],
                ],
                'started' => $match['status']['started'],
                'finished' => $match['status']['finished'],
                'cancelled' => $match['status']['cancelled'],
            ];
        }

        // Arrange results with leagues_ids first, followed by other leagues
        foreach ($leagues_ids as $leagueId) {
            if (isset($groupedMatches[$leagueId])) {
                $results[] = [
                    'leagueid' => $leagueId,
                    'data' => $groupedMatches[$leagueId],
                ];
                unset($groupedMatches[$leagueId]);
            }
        }

        // Add remaining leagues
        foreach ($groupedMatches as $leagueId => $matches) {
            $results[] = [
                'leagueid' => $leagueId,
                'data' => $matches,
            ];
        }

        return response()->json($results, 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Unable to fetch data',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    //return the today fixture of a league
    public function todayFixture($id, $date): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('football-get-matches-by-date', [
                'date' => $date,
            ]);

            $filteredMatches = array_filter($data['response']['matches'], function ($match) use ($id) {
                return $match['leagueId'] == $id;
            });
            
            $formattedMatches = array_map(function ($match) {
                return [
                    'id' => $match['id'],
                    'time' => $match['time'],
                    'home' => [
                        'id' => $match['home']['id'],
                        'score' => $match['home']['score'],
                        'name' => $match['home']['name'],
                    ],
                    'away' => [
                        'id' => $match['away']['id'],
                        'score' => $match['away']['score'],
                        'name' => $match['away']['name'],
                    ],
                    'started' => $match['status']['started'],
                    'finished' => $match['status']['finished'],
                    'cancelled' => $match['status']['cancelled'],
                ];
            }, array_values($filteredMatches));

            return response()->json($formattedMatches, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //return the fixture of a league
    public function fixture($id, $date): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('football-get-all-matches-by-league', [
                'leagueid' => $id,
            ]);

            $matches = $data['response']['matches'];

            $now = new \DateTime($date);
            $filteredMatches = array_filter($matches, function ($match) use ($now) {
                $matchTime = new \DateTime($match['status']['utcTime']);
                return $matchTime >= $now;
            });

            usort($filteredMatches, function ($a, $b) {
                return strtotime($a['status']['utcTime']) - strtotime($b['status']['utcTime']);
            });

            $nextTenMatches = array_slice($filteredMatches, 0, 10);

            $formattedMatches = array_map(function ($match) {
                return [
                    'id' => $match['id'],
                    'time' => $match['status']['utcTime'],
                    'home' => [
                        'id' => $match['home']['id'],
                        'score' => $match['home']['score'],
                        'name' => $match['home']['name'],
                    ],
                    'away' => [
                        'id' => $match['away']['id'],
                        'score' => $match['away']['score'],
                        'name' => $match['away']['name'],
                    ],
                    'started' => $match['status']['started'],
                    'finished' => $match['status']['finished'],
                    'cancelled' => $match['status']['cancelled'],
                ];
            }, $nextTenMatches);

            return response()->json($formattedMatches, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //return match details
    public function match($id): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('football-get-match-detail', [
                'eventid' => $id,
            ]);

            $scores = $this->apiService->fetchSomething('football-get-match-score', [
                'eventid' => $id,
            ]);

            $detail = $data['response']['detail'];
            $scoreData = $scores['response']['scores'];
            $homeScore = null;
            $awayScore = null;

            foreach ($scoreData as $team) {
                if ($team['id'] == $detail['homeTeam']['id']) {
                    $homeScore = $team['score'];
                } elseif ($team['id'] == $detail['awayTeam']['id']) {
                    $awayScore = $team['score'];
                }
            }

            $result = [
                'leagueId' => $detail['leagueId'],
                'matchId' => $detail['matchId'],
                'homeTeam' => [
                    'name' => $detail['homeTeam']['name'],
                    'id' => $detail['homeTeam']['id'],
                    'score' => $homeScore,
                ],
                'awayTeam' => [
                    'name' => $detail['awayTeam']['name'],
                    'id' => $detail['awayTeam']['id'],
                    'score' => $awayScore,
                ],
                'date' => $detail['matchTimeUTCDate'],
                'started' => $detail['started'],
                'finished' => $detail['finished'],
            ];

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //return match secondary details
    public function matchdetails($id): JsonResponse
    {
        try {
            $data = $this->apiService->fetchSomething('football-get-match-detail', [
                'eventid' => $id,
            ]);

            $location = $this->apiService->fetchSomething('football-get-match-location', [
                'eventid' => $id,
            ]);

            $refereedata = $this->apiService->fetchSomething('football-get-match-referee', [
                'eventid' => $id,
            ]); 

            $detail = $data['response']['detail'];
            $stadium = $location['response']['location']['name'];
            $referee = $refereedata['response']['referee']['text'];

            $result = [
                'matchId' => $detail['matchId'],
                'round' => $detail['leagueRoundName'],
                'stadium' => $stadium,
                'date' => $detail['matchTimeUTCDate'],
                'referee' => $referee,
            ];

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //return line ups
    public function headtohead($id): JsonResponse
    {
        try {
            $hometeam = $this->apiService->fetchSomething('football-get-hometeam-lineup', [
                'eventid' => $id,
            ]);

            $awayteam = $this->apiService->fetchSomething('football-get-hometeam-lineup', [
                'eventid' => $id,
            ]);

            $hometeamStarters = array_map(function ($starter) {
                return [
                    'id' => $starter['id'] ?? null,
                    'name' => $starter['name'] ?? null,
                    'countryName' => $starter['countryName'] ?? null,
                ];
            }, $hometeam['response']['lineup']['starters']);
    
            $awayteamStarters = array_map(function ($starter) {
                return [
                    'id' => $starter['id'] ?? null,
                    'name' => $starter['name'] ?? null,
                    'countryName' => $starter['countryName'] ?? null,
                ];
            }, $awayteam['response']['lineup']['starters']);

            return response()->json([
                'hometeam' => $hometeamStarters,
                'awayteam' => $awayteamStarters,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
