<?php

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

Auth::routes();

Route::get('/home', 'CurrenciesController@index');

Route::get('/', 'CurrenciesController@index');

//Route::post('/get-dates-interval-iso-data', 'CurrenciesController@getFiltertDatesIsoData');

Route::get('/get-dates-interval-iso-data', 'CurrenciesController@getFiltertDatesIsoData');




