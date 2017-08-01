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
    Route::get("/", ['as' => 'api.index.index', 'uses' => 'IndexController@index']);
});


Route::Group(['middleware' => 'pass', 'prefix' => 'pc', 'namespace' => 'Api\PC'], function () {
    //demo
    Route::get('/', 'IndexController@index')->name('pc.index.index');
    Route::post('smscode', 'IndexController@smscode')->name('pc.smscode');
    Route::post('register', 'IndexController@register')->name('pc.register');
    Route::post('message', 'IndexController@message')->name('pc.message');
    Route::post('collect', 'IndexController@collect')->name('pc.collect');
});

Route::Group(['middleware' => 'pass', 'prefix' => 'h5', 'namespace' => 'Api\H5'], function () {
    //demo
    Route::get('/', 'IndexController@index')->name('h5.index.index');
    Route::post('company/list/{uid}', 'CompanyController@companyList')->name('h5.company.list');
    //发票
    Route::post('invoice/create', 'InvoiceController@create')->name('h5.Invoice.create');
    //公司
    Route::post('company/index','CompanyController@index')->name('h5.Company.index');
    Route::post('company/create','CompanyController@create')->name('h5.Company.create');
    Route::post('company/update','CompanyController@update')->name('h5.Company.update');
    Route::post('company/delete','CompanyController@delete')->name('h5.Company.delete');
    //商户
    Route::post('shop/index','ShopController@index')->name('h5.Shop.index');
});





