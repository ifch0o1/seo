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

Route::get('admin/keyword-crapper', "KeywordCrapperController");

Route::post('api/custom_python_test', 'KeywordCrapperController@custom');

Route::post('api/push_python_words', 'KeywordCrapperController@push_python_words');

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});