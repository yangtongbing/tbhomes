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


###家谱url配置(需要登录)
Route::Group(['prefix' => 'atlas', 'middleware' => 'atlasverify', 'namespace' => 'Atlas'], function () {
    Route::get("/", 'AtlasController@login');
    Route::get("account", 'AtlasController@account');//我的账户信息
    Route::get('myTreeMap', 'AtlasController@myTreeMap')->name('atlas.Index.myTreeMap');//我的家谱信息
    Route::get('treeMapList', 'AtlasController@treeMapList')->name('atlas.Index.treeMapList');//家谱成员列表
    Route::get('addUser', 'AtlasController@addUser')->name('atlas.Index.addUser');//添加用户页面
    Route::get('editUser', 'AtlasController@editUser')->name('atlas.Index.editUser');//编辑用户页面

    Route::post('treemap', 'AtlasController@treemap')->name('atlas.Index.treemap');//获取家谱树
    Route::post('doAddUser', 'AtlasController@doAddUser')->name('atlas.Index.doAddUser');//添加用户
    Route::post('delUser', 'AtlasController@delUser')->name('atlas.Index.delUser');//删除用户
    Route::post('doEditUser', 'AtlasController@doEditUser')->name('atlas.Index.doEditUser');//执行编辑操作
    Route::post('resetPass', 'AtlasController@resetPass')->name('atlas.Index.resetPass');//重置密码
});

###家谱url配置(不需要登录)
Route::Group(['prefix' => 'atlas', 'namespace' => 'Atlas'], function () {
    //首页
    Route::get('login', 'AtlasController@login')->name('atlas.Index.login'); //登录页面
    Route::post('imgCode', 'AtlasController@imgCode')->name('atlas.Index.imgCode');//图形验证码
    Route::post('doLogin', 'AtlasController@doLogin')->name('atlas.Index.doLogin');//执行登录
});

##公众号
Route::Group(['prefix' => 'wechat', 'namespace' => 'Wechat'], function(){
    //验证接口
    Route::get('checkStatus', 'IndexController@checkStatus')->name('wechat.Index.checkStatus');

});
