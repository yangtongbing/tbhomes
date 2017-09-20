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
/*web页面*/
Route::Group(['middleware' => 'verify', 'namespace' => 'Web'], function () {
    Route::get("/", ['as' => 'web.index.account', 'uses' => 'IndexController@account']);
    Route::get('web/mycustomer', 'IndexController@mycustomer')->name('web.Index.mycustomer');
    Route::get('web/account', 'IndexController@account')->name('web.Index.account');
    Route::get('web/integral', 'IndexController@integral')->name('web.Index.integral');
    Route::any('web/uploadFile', 'IndexController@uploadFile')->name('web.Index.uploadFile');
    Route::post('web/orderEdit', 'IndexController@orderEdit')->name('web.Index.orderEdit');
    Route::post('web/doEdit', 'IndexController@doEdit')->name('web.Index.doEdit');
    Route::post('web/callOut', 'IndexController@callOut')->name('web.Index.callOut');
    Route::post('web/deleteImg', 'IndexController@deleteImg')->name('web.Index.deleteImg');
});

Route::Group(['prefix' => 'web', 'namespace' => 'Web'], function () {
    //首页
    Route::get('login', 'IndexController@login')->name('web.Index.login');
    Route::post('imgCode', 'IndexController@imgCode')->name('web.Index.imgCode');
});


###url配置(需要登录)
Route::Group(['prefix' => 'atlas', 'middleware' => 'atlasverify', 'namespace' => 'Atlas'], function () {
    Route::get("/", 'IndexController@login');
    Route::get("account", 'IndexController@account');
    Route::post('imgCode', 'IndexController@imgCode')->name('atlas.Index.imgCode');
    Route::post('treemap', 'IndexController@treemap')->name('atlas.Index.treemap');
    Route::post('addUser', 'IndexController@addUser')->name('atlas.Index.addUser');
});

###url配置(不需要登录)
Route::Group(['prefix' => 'atlas', 'namespace' => 'Atlas'], function () {
    //首页
    Route::get('login', 'IndexController@login')->name('atlas.Index.login');
    Route::post('imgCode', 'IndexController@imgCode')->name('atlas.Index.imgCode');
    Route::post('doLogin', 'IndexController@doLogin')->name('atlas.Index.doLogin');
});
