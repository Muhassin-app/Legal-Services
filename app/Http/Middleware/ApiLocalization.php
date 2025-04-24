<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class ApiLocalization
{

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(Request $request, Closure $next)
    {
        // Check header request and determine localizaton
        $local = ($request->hasHeader('language')) ? $request->header('language') : 1;

        if (!array_key_exists($local, $this->app->config->get('app.supported_languages'))) {
            // return response()->json(['status' => 'Error', 'message' => 'Language not supported.'], 403);
            $local = 1;
        }
        // set laravel localization
        app()->setLocale($this->app->config->get('app.supported_languages.' . $local));

        return $next($request);
    }
}
