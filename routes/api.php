<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Testing by ovi


$prefix = 'v1';
$prefix_v = 'v2';
require_once $prefix."/auth.php";
require_once $prefix."/guest.php";
require_once $prefix_v."/auth.php";
require_once $prefix_v."/guest.php";
