<?php

use App\Http\Controllers\FixturesController;
use App\Http\Controllers\LeaguesController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

// Get all leagues
Route::get('/leagues', [LeaguesController::class, 'leagues']);

// Get top 10 leagues fixtures: Live scores
Route::get('topLeaguesFixtures/{date}', [FixturesController::class, 'topLeaguesFixtures']);

// Get 5 news
Route::get('/news', [NewsController::class, 'news']);

// Get 5 league news
Route::get('/news/{id}', [NewsController::class, 'leagueNews']);

// Get League name and logo
Route::get('/league/{id}', [LeaguesController::class, 'league']);

// Get 10 fixtures of a leaugue: Calendar fixtures
Route::get('/league/{id}/fixtures/{date}', [FixturesController::class, 'todayFixture']);
Route::get('/league/{id}/nextfixtures/{date}', [FixturesController::class, 'fixture']);

// Get Table of a League: Competition Standings
Route::get('/league/{id}/table', [LeaguesController::class, 'table']);

//  Get Statictics of a leaugue (top rating / scoring / assists)
Route::get('/league/{id}/goals', [LeaguesController::class, 'goals']);
Route::get('/league/{id}/assists', [LeaguesController::class, 'assists']);
Route::get('/league/{id}/rating', [LeaguesController::class, 'rating']);

// Get Match details
Route::get('/match/{id}', [FixturesController::class, 'match']);

// Get match secondary details
Route::get('/matchdetails/{id}', [FixturesController::class, 'matchdetails']);

// head to head
Route::get('/lineups/{id}', [FixturesController::class, 'headtohead']);
