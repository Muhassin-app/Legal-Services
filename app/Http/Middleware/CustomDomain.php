<?php

namespace App\Http\Middleware;

use Cache;
use Config;
use Closure;
use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Models\{Client, ClientPreference, Language, ClientLanguage, Currency, ClientCurrency, Product, Country};


class CustomDomain
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
    $path = $request->path();
    $domain = $request->getHost();
    $domain = str_replace(array('http://', '.test.com/login'), '', $domain);
    $subDomain = explode('.', $domain);
    $existRedis = Redis::get($domain);
    if (!$existRedis) {
      $client = Client::select('name', 'email', 'phone_number', 'is_deleted', 'is_blocked', 'logo', 'company_name', 'company_address', 'status', 'code', 'database_name', 'database_host', 'database_port', 'database_username', 'database_password', 'custom_domain', 'sub_domain')
        ->where(function ($q) use ($domain, $subDomain) {
          $q->where('custom_domain', $domain)
            ->orWhere('sub_domain', $subDomain[0]);
        })->firstOrFail();
      Redis::set($domain, json_encode($client->toArray()), 'EX', 36000);
      $existRedis = Redis::get($domain);
    }
    $callback = '';
    $redisData = json_decode($existRedis);
    if ($redisData) {
      $database_name = 'royo_' . $redisData->database_name;
      $database_host = !empty($redisData->database_host) ? $redisData->database_host : env('DB_HOST', '127.0.0.1');
      $database_port = !empty($redisData->database_port) ? $redisData->database_port : env('DB_PORT', '3306');
      $database_username = !empty($redisData->database_username) ? $redisData->database_username : env('DB_USERNAME', 'royoorders');
      $database_password = !empty($redisData->database_password) ? $redisData->database_password : env('DB_PASSWORD', '');
      $default = [
        'driver' => env('DB_CONNECTION', 'mysql'),
        'host' => $database_host,
        'port' => $database_port,
        'database' => $database_name,
        'username' => $database_username,
        'password' => $database_password,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => false,
        'engine' => null
      ];
      Config::set("database.connections.$database_name", $default);
      Config::set("client_id", 1);
      Config::set("client_connected", true);
      Config::set("client_data", $redisData);
      DB::setDefaultConnection($database_name);
      DB::purge($database_name);
      if (!empty($redisData->custom_domain)) {
        $domain = rtrim($redisData->custom_domain, "/");
        $domain = ltrim($domain, "https://");
        $callback = "https://" . $domain . '/auth/facebook/callback';
      } else {
        $sub_domain = rtrim($redisData->sub_domain, "/");
        $sub_domain = ltrim($sub_domain, "https://");
        $callback = "https://" . $sub_domain . ".royoorders.com/auth/facebook/callback";
      }
      $clientPreference = ClientPreference::where('client_code', $redisData->code)->first();
      if ($clientPreference) {
        Config::set('FACEBOOK_CLIENT_ID', $clientPreference->fb_client_id);
        Config::set('FACEBOOK_CLIENT_SECRET', $clientPreference->fb_client_secret);
        Config::set('FACEBOOK_CALLBACK_URL', $callback);
      }
      Session::put('client_config', $redisData);
      Session::put('login_user_type', 'client');

      // Set language
      $primeLang = ClientLanguage::select('language_id', 'is_primary')->where('is_primary', 1)->first();
      if (!Session::has('customerLanguage') || empty(Session::get('customerLanguage'))) {
        if ($primeLang) {
          Session::put('customerLanguage', $primeLang->language_id);
        }
      }
      if (!Session::has('customerLanguage') || empty(Session::get('customerLanguage'))) {
        $primeLang = Language::where('id', 1)->first();
        Session::put('customerLanguage', 1);
      }
      $lang_detail = Language::where('id', Session::get('customerLanguage'))->first();
      App::setLocale($lang_detail->sort_code);
      Session::put('applocale', $lang_detail->sort_code);

      // Set Currency                   
      $primeCurcy = ClientCurrency::join('currencies as cu', 'cu.id', 'client_currencies.currency_id')->where('client_currencies.is_primary', 1)->first();
      Session::put('client_primary_currency', $primeCurcy->iso_code);
      if (!Session::has('customerCurrency') || empty(Session::get('customerCurrency'))) {
        if ($primeCurcy) {
          Session::put('customerCurrency', $primeCurcy->currency_id);
          Session::put('currencySymbol', $primeCurcy->symbol);
          Session::put('currencyMultiplier', $primeCurcy->doller_compare);
        }
      }
      if (!Session::has('customerCurrency') || empty(Session::get('customerCurrency'))) {
        $primeCurcy = Currency::where('id', 147)->first();
        Session::put('customerCurrency', 147);
        Session::put('currencySymbol', $primeCurcy->symbol);
        Session::put('currencyMultiplier', 1);
      }
      $currency_detail = Currency::where('id', Session::get('customerCurrency'))->first();
      Session::put('iso_code', $currency_detail->iso_code);

      // Client preferences
      $preferData = array();
      if (isset($clientPreference)) {
        $preferData = $clientPreference;
      }

      $cl = Client::first();
      $getAdminCurrentCountry = Country::where('id', '=', $cl->country_id)->get()->first();
      if (!empty($getAdminCurrentCountry)) {
        $countryCode = $getAdminCurrentCountry->code;
        $phoneCode = $getAdminCurrentCountry->phonecode;
      } else {
        $countryCode = '';
        $phoneCode = '';
      }

      $vendor_mode_count = 0;
      $single_vendor_type = "delivery";
      $enabled_vendor_types = [];

      if ($clientPreference) {
        foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
          $clientVendorTypes = $vendor_typ_key . '_check';

          if ($clientPreference->$clientVendorTypes == 1) {
            if ($vendor_mode_count == 0) {
              $single_vendor_type   = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
            }
            $enabled_vendor_types[] = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
            $vendor_mode_count++;
          }
        }
      }
      // pr(Session::get('latitude'));
      if (empty(Session::get('vendorType'))) {
        Session::put('vendorType', $single_vendor_type);
      }
      if (!in_array(Session::get('vendorType'), $enabled_vendor_types)) {
        Session::put('vendorType', $single_vendor_type);
      }
      if (empty(Session::get('selectedAddress'))) {
        Session::put('selectedAddress', @$clientPreference->Default_location_name);
      }

      if ($vendor_mode_count == 1) {
        Session::forget('vendorType');
        Session::put('vendorType', $single_vendor_type);
      }

      Session::put('default_country_code', $countryCode);
      Session::put('default_country_phonecode', $phoneCode);

      Session::put('preferences', $preferData);
      $cl->logo_image_url = $cl ? $cl->logo['original'] : ' ';
      Session::put('clientdata', $cl);
    } else {
      return redirect()->route('error_404');
    }
    return $next($request);
  }
}
