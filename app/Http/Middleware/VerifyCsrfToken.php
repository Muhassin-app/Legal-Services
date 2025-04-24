<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'payment/payfast/notify',
        'payment/payfast/notify/app',
        'payment/paypal/notify',
        'payment/mobbex/notify',
        'webhook/*',
        'passbase/webhook',
        'easebuzz_respont',
        'payment/easebuzz/notify',
        'payment/easebuzz/api',
        'payment/userede/respons',

        'ccavenue/success',
        'vnpay_respont',
        'payment/vnpay/api',
        'payment/vnpay/notify',

        'payment/cashfree/notify',
        'verify/payment/otp/app/*',
        'payment/webhook/stripe_ideal',
        /** routes for edit order **/
        'edit-order/*',
        'payment/*',

        'payment/paytab/return',
        'payment/paytab/callback',
        'sendNotificationToUserByDispatcher',
        'dispatch/customer/distance/notification/*',
        'dispatch/driver/bids/update/*',
        'dispatch/driver/bids/status/*',
        'square/inventory/event/update',
        'skipcash/webhook',
        'cybersource/process-payment',
        'success-orangepay',
        'success/pesapal',
        'payment/hitpay/webhook'
    ];
}
