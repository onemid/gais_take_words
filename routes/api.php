<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'classification'], function() {
    Route::post('new', 'ClassificationController@newClassification');
    Route::post('modify', 'ClassificationController@modifyClassification');
    Route::post('delete', 'ClassificationController@deleteClassification');

    Route::get('get_classification/{field_name}/{rid}', 'ClassificationController@getClassification');
    Route::get('search_classification/{field_name}/{query}', 'ClassificationController@searchClassification');
});

Route::group(['prefix' => 'word'], function() {
    Route::post('new', 'WordController@newWord');
    Route::post('modify', 'WordController@modifyWord');
    Route::post('delete', 'WordController@deleteWord');
});
