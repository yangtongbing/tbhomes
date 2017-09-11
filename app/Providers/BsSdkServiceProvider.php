<?php

namespace App\Providers;

use App\Repositories\BsSdkRepository;
use function foo\func;
use Illuminate\Support\ServiceProvider;

class BsSdkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $obj = new BsSdkRepository(config('api.BsSdkId'), config('api.BsSdkKey'));
        //
        $this->app->instance('BsSDK',$obj);
    }
}
