<?php

namespace App\Console\Commands;

use App\Jobs\NotifyUsersPickupJob;
use App\Models\AutoRejectOrderCron;
use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\ClientPreferenceAdditional;
use App\Models\Order;
use Carbon\Carbon;

class NotifyUsersBeforePickup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pickup:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification To Customer before Pickup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $clients = Client::select('database_name', 'sub_domain')->get();
        foreach ($clients as $client) {
            $database_name = 'royo_' . $client->database_name;
            $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
            $db = DB::select($query, [$database_name]);
            if ($db) {
                $default = [
                    'prefix' => '',
                    'engine' => null,
                    'strict' => false,
                    'charset' => 'utf8mb4',
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'prefix_indexes' => true,
                    'database' => $database_name,
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                    'collation' => 'utf8mb4_unicode_ci',
                    'driver' => env('DB_CONNECTION', 'mysql'),
                ];
                Config::set("database.connections.$database_name", $default);
                DB::setDefaultConnection($database_name);

                $this->cron($database_name);

                DB::disconnect($database_name);
            } else {
                DB::disconnect($database_name);
            }
        }
    }

    public function cron($database_name)
    {
        $client_preferences_addional = ClientPreferenceAdditional::on($database_name)->pluck('key_value','key_name');

        if(isset($client_preferences_addional['pickup_notification_before']) && $client_preferences_addional['pickup_notification_before'] == 1)
        {
            $hour = $client_preferences_addional['pickup_notification_before_hours'];
            $hour2 = $client_preferences_addional['pickup_notification_before2_hours'];
            $user_ids = Order::on($database_name)->where('type',2)->where(function($q) use($hour, $hour2){
                $q->where('scheduled_date_time',Carbon::now()->addHours($hour)->format("Y-m-d H:i"));
                $q->orWhere('scheduled_date_time',Carbon::now()->addHours($hour2)->format("Y-m-d H:i"));
            })->pluck('user_id');
            Order::on($database_name)->where('type',2)->where('scheduled_date_time',Carbon::now()->addHours($hour)->format("Y-m-d H:i"))->chunk(100,function($orders) use ($database_name,$user_ids){
                NotifyUsersPickupJob::dispatch($database_name,$orders,$user_ids);
            });
        }
    }
}
