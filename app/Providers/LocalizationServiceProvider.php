<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cache,App,File;

class LocalizationServiceProvider extends ServiceProvider
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
        $this->langPath = resource_path( 'lang/'. App::getLocale() );
            Cache::rememberForever( 'translations', function () {
            return collect( File::allFiles( $this->langPath ) )->flatMap( function ( $file ) {
                return [
                    $translation = $file->getBasename( '.php' ) => trans( $translation ),
                ];
            } )->toJson();
            } );
    }
}
