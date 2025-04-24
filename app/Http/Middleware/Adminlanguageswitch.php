<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\{ ClientLanguage,Language};
use Session;
class Adminlanguageswitch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if(session()->has('applocale_admin')){
            app()->setlocale(session()->get("applocale_admin"));
        }else{
            $primeLang = ClientLanguage::select('language_id', 'is_primary')->where('is_primary', 1)->first();

                if($primeLang){
                    $lang_detail = Language::where('id', $primeLang->language_id)->first();
                    Session::put('applocale_admin', $lang_detail->sort_code);
                    app()->setlocale(session()->get("applocale_admin"));
                }
                if(!session()->has('applocale_admin')){
                    app()->setlocale('en');
                }

        }
        return $next($request);
    }
}
