<?php

namespace App\Providers;

use App\Repositories\CipherECB;
use Illuminate\Support\ServiceProvider;

class CipherECBServiceProvider extends ServiceProvider
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
        //
        $this->app->bind('CipherECB',CipherECB::class);
    }
}
