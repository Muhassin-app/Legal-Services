<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
            if (@$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                $route = $request->route()->getName();
                $notRestrict = ['user.verify', 'customer.login', 'user.verifyToken', 'email.send', 'userHome', 'orderlogs', 'extrapage', 'customer.loginViaUsername'];
                if (in_array($route, $notRestrict)) {
                    return $next($request);
                }

                return redirect()->route('customer.register'); // or any other redirect or error response you want
            }
            return $next($request);
        } catch (\Exception $ex) {
            return $next($request);
        }
    }
}
