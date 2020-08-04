<?php

use App\Http\Controllers\KeywordCrapperController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('admin/keyword-crapper', "KeywordCrapperController")
    ->middleware('auth');

Route::get('admin/aida-generator', "AidaGeneratorController")
    ->middleware('auth');

Route::get('admin/keyword-ranking', "KeywordRankingController")
    ->middleware('auth');

Route::post('api/aida_posts/generate', "AidaGeneratorController@generate")
    ->middleware('auth');

/** 
 * Keywords and Aida_posts for ADMIN PANEL
 * You can found client oriented API routes in api.php
 */
Route::resource('api/keywords', "KeywordCrapperController");
Route::resource('api/aida_posts', "AidaPostsController");

Route::post('api/push_python_words', 'KeywordCrapperController@push_python_words');

Route::get('api/keywords', 'KeywordCrapperController@get_api_handler');

Route::get('api/send_get_related_keywords_to_local_server', 'KeywordCrapperController@send_get_related_keywords_to_local_server');

/** Only administrator managed routes */
Route::resource('admin/manage_tokens', 'AuthTokensController')->middleware('admin.user');