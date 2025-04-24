<?php

namespace App\Http\Middleware;

use App\Models\PaymentOption;
use Closure;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class VerifyStripeWebhook
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
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');
            $stripe_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'stripe')->where('status', 1)->first();
            if(!empty($stripe_creds)){
                $creds_arr = json_decode($stripe_creds->credentials);
            }
            $webhook_secret = (isset($creds_arr->webhook_signature)) ? $creds_arr->webhook_signature : config('services.stripe.webhook_secret');
            $event = Webhook::constructEvent($payload, $sigHeader, $webhook_secret);
            $request->attributes->add(['stripe_event' => $event]);
        } catch (SignatureVerificationException $e) {
            return response('Invalid webhook signature.', 401);
        }

        return $next($request);
    }
}
