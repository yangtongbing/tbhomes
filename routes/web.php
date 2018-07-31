<?php
Route::get('/', 'HomeController@index');
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
    Route::any('callback', 'IndexController@callback')->name('wechat.Index.callback');
    Route::get('getAccessToken', 'IndexController@getAccessToken')->name('wechat.Index.getAccessToken');
    Route::get('pullData', 'IndexController@pullData')->name('wechat.Index.pullData');
    Route::get('ocrCeshi', 'IndexController@ocrCeshi')->name('wechat.Index.ocrCeshi');
    Route::get('excel', 'IndexController@excel')->name('wechat.Index.excel');
    Route::get('getRelation', 'IndexController@getRelation')->name('wechat.Index.getRelation');
});


