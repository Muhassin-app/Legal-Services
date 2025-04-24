<?php

namespace App\Console\Commands;

use App\Models\Client;
use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\ClientPreference;
use App\Models\OrderVendor;
use App\Models\UserVendor;
use App\Models\UserDevice;
use App\Models\User;
use App\Models\NotificationTemplate;
use App\Models\AutoRejectOrderCron;
use Log;
use Carbon\Carbon;
use App\Models\Order;

class RejectOrderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:reject_order_notifi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to vendor for reject order after a fixed interval of time';

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

        $intervalTime = Carbon::now()->addMinutes(5);
        $intervalTime2 = $intervalTime->addSeconds(40);
        $databases = AutoRejectOrderCron::whereBetween('auto_reject_time',[$intervalTime,$intervalTime2])->groupBy('database_name')->get();
        foreach ($databases as $client) {
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
                    'username' => $client->database_username,
                    'password' => $client->database_password,
                    'collation' => 'utf8mb4_unicode_ci',
                    'driver' => env('DB_CONNECTION', 'mysql'),
                ];
                Config::set("database.connections.$database_name", $default);
                // DB::setDefaultConnection($database_name);
                $client_preferences = ClientPreference::on($database_name)->first();
                $notification_content = NotificationTemplate::on($database_name)->where(['id' => 11])->first();
                $selected_database_orders = AutoRejectOrderCron::whereBetween('auto_reject_time',[$intervalTime,$intervalTime2])->where(['database_name' => $client->database_name])->get();
                foreach ($selected_database_orders as $order_key => $order_value) {
                    $orderVendorDetail = OrderVendor::on($database_name)->find($order_value->order_vendor_id);
                    $orderDetail = Order::on($database_name)->find($orderVendorDetail->order_id);

                    $user_vendors = UserVendor::on($database_name)->where(['vendor_id' => $orderVendorDetail->vendor_id])->pluck('user_id');

                    $devices = UserDevice::on($database_name)->whereNotNull('device_token')->whereIn('user_id', $user_vendors)->pluck('device_token')->toArray();

                    if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                        $body_content = str_ireplace("{order_id}", "#" . $orderDetail->order_number, $notification_content->content);
                        if ($body_content) {
                            $redirect_URL = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/user/orders";
                           
                            $data = [
                                "registration_ids" => $devices,
                                "notification" => [
                                    'title' => $notification_content->subject,
                                    'body'  => $body_content,
                                    'sound' => "default",
                                    "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                                    'click_action' => $redirect_URL,
                                    "android_channel_id" => "default-channel-id"
                                ],
                                "data" => [
                                    'title' => $notification_content->subject,
                                    'body'  => $body_content,
                                    'type' => "order_status_change"
                                ],
                                "priority" => "high"
                            ];
                            sendFcmCurlRequest($data);
                        }
                    }
                }
            } else {
            }
        }
    }
}
