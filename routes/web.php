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

Route::get('/', function () {
    return view('welcome');
});

Route::get('admin/keyword-crapper', "KeywordCrapperController")
    ->middleware('auth');

Route::get('admin/aida-generator', "AidaGeneratorController")
    ->middleware('auth');

Route::post('api/aida_posts/generate', "AidaGeneratorController@generate")
    ->middleware('auth');

Route::post('api/custom_python_test', 'KeywordCrapperController@custom');

Route::resource('api/keywords', "KeywordCrapperController");

Route::apiResource('api/industry', "API\IndustryController")
    ->middleware('auth');

Route::post('api/push_python_words', 'KeywordCrapperController@push_python_words');

Route::get('api/keywords', 'KeywordCrapperController@get_api_handler');

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});