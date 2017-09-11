<?php

namespace App\Providers;

use App\Repositories\CallOutRepository;
use function foo\func;
use Illuminate\Support\ServiceProvider;

class CallOutServiceProvider extends ServiceProvider
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
        $obj = new CallOutRepository();
        //
        $this->app->instance('CallOut',$obj);
    }
}
