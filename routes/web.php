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

Route::get('login', 'HomeController@login');
Route::get('wechatCallback', 'HomeController@wechatCallback');
Route::get('user/{id}', 'UserController@show');
Route::post('user/{id}', 'UserController@show');


//testing successful？？

//到底能不能成功了！！！

//难 难 难 