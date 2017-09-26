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
    Route::get("/", 'AtlasController@login');
    Route::get("account", 'AtlasController@account');
    Route::post('imgCode', 'AtlasController@imgCode')->name('atlas.Index.imgCode');
    Route::post('treemap', 'AtlasController@treemap')->name('atlas.Index.treemap');
    Route::post('addUser', 'AtlasController@addUser')->name('atlas.Index.addUser');
    Route::post('delUser', 'AtlasController@delUser')->name('atlas.Index.delUser');
    Route::post('editUser', 'AtlasController@editUser')->name('atlas.Index.editUser');
    Route::post('resetPass', 'AtlasController@resetPass')->name('atlas.Index.resetPass');
    Route::get('myTreeMap', 'AtlasController@myTreeMap')->name('atlas.Index.myTreeMap');
    Route::get('treeMapList', 'AtlasController@treeMapList')->name('atlas.Index.treeMapList');
});

###url配置(不需要登录)
Route::Group(['prefix' => 'atlas', 'namespace' => 'Atlas'], function () {
    //首页
    Route::get('login', 'AtlasController@login')->name('atlas.Index.login');
    Route::post('imgCode', 'AtlasController@imgCode')->name('atlas.Index.imgCode');
    Route::post('doLogin', 'AtlasController@doLogin')->name('atlas.Index.doLogin');
});
