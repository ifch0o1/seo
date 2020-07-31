<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/custom_python_test', 'KeywordCrapperController@custom')->middleware('cors');

Route::get('/get_bottom_keywords/{keyword}', 'KeywordCrapperController@getBottomKeywords');

Route::get('/crap_bottom_keywords/{keyword}', 'KeywordCrapperController@crapBottomKeywords')->middleware('cors');

Route::resource('/client', 'API\ClientController');

Route::apiResource('/industry', "API\IndustryController");

Route::apiResource('/aida_sentences', "API\AidaSentenceController");

Route::get('/keyword-ranking-words', 'KeywordRankingController@keywordRankingWords')->middleware('cors');

Route::post('/keyword-ranking-words', 'KeywordRankingController@store')->middleware('cors');

Route::get('/client_keyword_href/{client}', "KeywordRankingController@indexClientKeywordHref");

Route::get('/client_keywords_ranking/{client}', 'KeywordRankingController@indexRanking');

Route::post('/client_keyword_href', "KeywordRankingController@storeClientKeywordHref");

Route::delete('/client_keyword_href', "KeywordRankingController@destroyClientKeywordHref");

Route::get('/get_related_keywords', 'KeywordCrapperController@get_related_keywords')->middleware('cors');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});