<?php

namespace App\Http\Middleware;

use Auth;
use Config;
use Request;
use Closure;
use JWT\Token;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\{BlockedToken, User, ClientLanguage, ClientCurrency, Currency, UserDevice};

class AppAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $header = $request->header();
        $token = $header['authorization'][0] ?? null;
        if ($token != null) {
            if (!Token::check($token, 'royoorders-jwt')) {
                return response()->json(['error' => 'Invalid Token', 'message' => 'Session Expired'], 401);
                abort(404);
            }
        } else {
            return response()->json(['error' => 'Invalid Token', 'message' => 'Session Expired'], 401);
            abort(404);
        }

        $tokenBlock = BlockedToken::where('token', $token)->first();
        if ($tokenBlock) {
            return response()->json(['error' => 'Invalid Session', 'message' => 'Session Expired'], 401);
            abort(404);
        }


        $user = User::whereHas('device', function ($qu) use ($token) {
            $qu->where('access_token', $token);
        })->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid Session', 'message' => 'Invalid Token or session has been expired.'], 401);
            abort(404);
        }

        if (isset($user) && $user->status != 1 && !$user->hasRole('Lawyer')) {
            $blockToken = new BlockedToken();
            $header = $request->header();
            $blockToken->token = $header['authorization'][0];
            $blockToken->expired = '1';
            $blockToken->save();

            $del_token = UserDevice::where('access_token', $header['authorization'][0])->delete();

            return response()->json([
                'message' => __('Successfully logged out')
            ]);
        }

        $timezone = $user->timezone;
        $languages = ClientLanguage::where('is_primary', 1)->first();
        $primary_cur = ClientCurrency::where('is_primary', 1)->first();
        $language_id = $languages->language_id;
        $currency_id = $primary_cur->currency_id;
        if (isset($header['language'][0]) && !empty($header['language'][0])) {
            $checkLang = ClientLanguage::where('language_id', $header['language'][0])->first();
            if ($checkLang) {
                $language_id = $checkLang->language_id;
            }
        }
        if (isset($header['currency'][0]) && !empty($header['currency'][0])) {
            $checkCur = ClientCurrency::where('currency_id', $header['currency'][0])->first();
            if ($checkCur) {
                $currency_id = $checkCur->currency_id;
            } else {
                $currency_id = Currency::where('id', 147)->first()->id;
            }
        }
        if (isset($header['timezone'][0]) && !empty($header['timezone'][0])) {
            $timezone = $header['timezone'][0];
        }
        if (isset($header['latitude'][0]) && !empty($header['latitude'][0])) {
            $user->latitude = $header['latitude'][0];
        }
        if (isset($header['longitude'][0]) && !empty($header['longitude'][0])) {
            $user->longitude = $header['longitude'][0];
        }
        if (isset($header['type'][0]) && !empty($header['type'][0])) {
            $user->vendorType = $header['type'][0];
        }
        $user->language = $language_id;
        $user->currency = $currency_id;
        $user->timezone = $timezone;
        Auth::login($user);
        return $next($request);
    }
}
