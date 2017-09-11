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


/*Api公共接口定义*/
Route::Group(['namespace' => 'Api'], function () {
    Route::post('apply', 'IndexController@index')->name('apply');
    //外呼接口
    Route::post('callOut', 'CallOutController@callOut')->name('callOut');
    Route::post('createUser', 'IndexController@createUser')->name('createUser');
    Route::post('dropUser', 'IndexController@dropUser')->name('dropUser');
});
