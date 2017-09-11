<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('company', CompanyController::class);
    $router->resource('reconciliation', ReconciliationController::class);
    $router->resource('order', OrderController::class);
    $router->resource('record', RecordController::class);
    $router->resource('account', AccountController::class);
    $router->post('record/getDetail', 'RecordController@getDetail');




//    $router->get('info', 'InfoController@index');
//    $router->resource('user', UserController::class);
//    $router->resource('news', NewsController::class);
//    $router->resource('shop', ShopController::class);


});


