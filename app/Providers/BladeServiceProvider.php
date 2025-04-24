<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('money', function ($money) {
            return "<?php echo number_format($money, 2); ?>";
        });

        Blade::directive('debug', function ($content) {
            return "<?php debug($content); ?>";
        });

        Blade::directive('use', function ($import) {
            return "<?php use $import; ?>";
        });
    }
}
