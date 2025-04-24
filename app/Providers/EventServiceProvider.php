<?php

namespace App\Providers;

use App\Jobs\SyncToDispatcher;
use App\Models\Order;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Observers\OrderVendorObserver;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Observers\OrderObserver;
use App\Observers\PaymentObserver;
use App\Observers\UserObserver;
use App\Observers\WalletObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            SyncToDispatcher::class
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\\Apple\\AppleExtendSocialite@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
        OrderVendor::observe(OrderVendorObserver::class);
        User::observe(UserObserver::class);
        Order::observe(OrderObserver::class);
        Payment::observe(PaymentObserver::class);
        Wallet::observe(WalletObserver::class);
    }
}
