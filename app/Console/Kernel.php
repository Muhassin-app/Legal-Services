<?php

namespace App\Console;

use App\Models\Client;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CartReminder::class,
        Commands\AutoRejectOrders::class,
        // Commands\SetDummyDataForDemo::class,
        Commands\RejectOrderNotification::class,
        Commands\HubSpotSyncData::class,
        Commands\RecurringBooking::class,
        Commands\CloneDatabase::class,
        Commands\CopyVendorDataToolCommand::class
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('copy:catalog')->everyTwoMinutes();
        $schedule->command('cart:reminder')->hourly();
        $schedule->command('auto:reject_order')->everyMinute();
        $schedule->command('auto:reject_order_notifi')->everyMinute();
        // $schedule->command('set_default_dummy:data')->dailyAt('00:30');
        $schedule->command('auto:create_recurring_order_for_dispatcher')->dailyAt('00:30');
        // $schedule->command('set_default_dummy:data')->dailyAt('00:30');
        $schedule->command('auto:create_recurring_order_for_dispatcher')->hourly();
        $schedule->command('send_campaign:notification')->everyMinute();
        $schedule->command('service_area:active_for_vendor_slot')->everyMinute();
        $schedule->command('pickup:notify')->everyMinute();
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
