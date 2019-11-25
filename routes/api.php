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

Route::prefix('classification', function() {
    Route::post('new', 'newClassification@ClassificationController');
    Route::post('modify', 'modifyClassification@ClassificationController');
    Route::post('delete', 'deleteClassification@ClassificationController');
});

Route::prefix('word', function() {
    Route::post('new', 'newWord@WordController');
    Route::post('modify', 'modifyWord@WordController');
    Route::post('delete', 'deleteWord@WordController');
});
