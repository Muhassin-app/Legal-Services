<?php

namespace App\Console\Commands;

use App\Jobs\CampaignSendNotificationJob;
use App\Models\Client;
use App\Mail\OrderSuccessEmail;
use App\Models\CampaignRoster;
use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\ClientPreference;
use Illuminate\Support\Facades\Log;

// use App\Models\Order;

class SendCampaignNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_campaign:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Campaign notifications at schedules time';

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
        $intervalTime = now();
        foreach ($clients as $client) {
            
            if($client->is_lumen_enabled == 1)
            {
                break;
            }
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
                $client_preferences = ClientPreference::first(['fcm_server_key', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'mail_host', 'mail_port', 'mail_driver', 'mail_from', 'favicon', 'mail_password', 'mail_encryption']);
                $from = $client_preferences->fcm_server_key ?? "";
                $headers = [
                    'Authorization: key=' . $from,
                    'Content-Type: application/json',
                ];
                $chunk_notifications = CampaignRoster::where('notification_time', '<=', $intervalTime)->where('status', 0)->with('campaign', 'user')->get();
                
                $chunk_notifications = $chunk_notifications->groupBy(function ($item) {
                    return $item->notofication_type;
                });
                if (count($chunk_notifications) > 0) {
                    CampaignSendNotificationJob::dispatch($chunk_notifications, $client_preferences, $headers);
                    DB::disconnect($database_name);
                }
            } else {
                DB::disconnect($database_name);
            }
        }
    }
}
