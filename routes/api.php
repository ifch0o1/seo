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

Route::resource('/client', 'API\ClientController');

Route::apiResource('/industry', "API\IndustryController");

Route::get('/client_keyword_href/{client}', "KeywordRankingController@indexClientKeywordHref");

Route::post('/client_keyword_href', "KeywordRankingController@storeClientKeywordHref");

Route::delete('/client_keyword_href', "KeywordRankingController@destroyClientKeywordHref");

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});