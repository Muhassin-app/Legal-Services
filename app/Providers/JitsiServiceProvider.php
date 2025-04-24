<?php

namespace App\Providers;

use App\Services\JitsiService;
use Illuminate\Support\ServiceProvider;

class JitsiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(JitsiService::class, function () {
            return new JitsiService();
        });

        $this->app->bind('jitsi', function () {
            return app(JitsiService::class);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
