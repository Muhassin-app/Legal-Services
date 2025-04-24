<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Client;
use App\Models\OrderVendor;
use App\Models\UserDevice;
use App\Models\ClientPreference;
use App\Models\VendorOrderStatus;
use App\Models\AutoRejectOrderCron;
use App\Models\NotificationTemplate;
use App\Models\VendorOrderCancelReturnPayment;

use Log;
use Config;
use Carbon\Carbon;

class AutoRejectOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:reject_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto reject order after a fixed interval of time';

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
        $intervalTime = Carbon::now();
        $databases = AutoRejectOrderCron::where('auto_reject_time', '<=', $intervalTime)->groupBy('database_name')->get();
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
                $notification_content = NotificationTemplate::on($database_name)->where(['id' => 6])->first();
                $selected_database_orders = AutoRejectOrderCron::where('auto_reject_time', '<=', $intervalTime)->where(['database_name' => $client->database_name])->get();
                foreach ($selected_database_orders as $order_key => $order_value) {


                    $orderVendorDetail = OrderVendor::on($database_name)->find($order_value->order_vendor_id);

                    if($orderVendorDetail)
                    {
                        
                        $order_status_check = VendorOrderStatus::on($database_name)->where('order_id',$orderVendorDetail->order_id)->where('vendor_id', $orderVendorDetail->vendor_id)->where('order_status_option_id', 3)->first();
                        
                        if(!$order_status_check){
                            $dataStatus = [
                                "order_id" => $orderVendorDetail->order_id,
                                "vendor_id" => $orderVendorDetail->vendor_id,
                                "order_vendor_id" => $order_value->order_vendor_id,
                                "order_status_option_id" => 3,
                                "created_at" => Carbon::now(),
                                "updated_at" => Carbon::now(),
                            ];
                            VendorOrderStatus::on($database_name)->insert($dataStatus);
                        }
                        $orderDetail = Order::on($database_name)->with(array(
                            'vendors' => function ($query) use ($orderVendorDetail) {
                                $query->where('vendor_id', $orderVendorDetail->vendor_id);
                            }
                        ))->find($orderVendorDetail->order_id);
                        AutoRejectOrderCron::where(['order_vendor_id' => $order_value->order_vendor_id, 'database_name' => $client->database_name])->delete();
                        // get vendo amount
                        $return_response =  $this->GetVendorReturnAmount($orderDetail,$database_name);
                        $orderVendorDetail->order_status_option_id = 3;
                        $orderVendorDetail->save();

                        if($return_response['vendor_return_amount'] > 0){
                            $user = User::on($database_name)->find($orderVendorDetail->user_id);
                            $wallet = $user->wallet;
                            $wallet_amount_used = $return_response['vendor_return_amount'] ; //$currentOrderStatus->payable_amount;
                            $wallet->depositFloat($wallet_amount_used, ['Wallet has been <b>Credited</b> for return #'. $orderVendorDetail->orderDetail->order_number.' ('.$orderVendorDetail->vendor->name.')']);
                        }

                        $orderDetail->loyalty_points_used    =  $orderDetail->loyalty_points_used - $return_response['vendor_loyalty_points'];
                        $orderDetail->loyalty_amount_saved   =  $orderDetail->loyalty_amount_saved - $return_response['vendor_loyalty_amount'];
                        $orderDetail->loyalty_points_earned  =  $orderDetail->loyalty_points_earned - $return_response['vendor_loyalty_points_earned'];
                        $orderDetail->save();
    
                        $vendor_return_payment_data = [
                            "order_id"                      => $orderDetail->id,
                            "order_vendor_id"               => $orderVendorDetail->id,
                            "wallet_amount"                 => $return_response['vendor_wallet_amount'],
                            "online_payment_amount"         => $return_response['vendor_online_payment_amount'],
                            "loyalty_amount"                => $return_response['vendor_loyalty_amount'],
                            "loyalty_points"                => $return_response['vendor_loyalty_points'],
                            "loyalty_points_earned"         => $return_response['vendor_loyalty_points_earned'],
                            "total_return_amount"           => $return_response['vendor_return_amount'],
                        ];
                         // save payment in table
                         $vendor_return_payment    = VendorOrderCancelReturnPayment::on($database_name)->insert($vendor_return_payment_data);

                        $devices = UserDevice::on($database_name)->whereNotNull('device_token')->where(['user_id' => $orderVendorDetail->user_id])->pluck('device_token')->toArray();
                        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                            $from = $client_preferences->fcm_server_key;
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

                }
                 DB::disconnect($database_name);
            } else {
            }
        }
    }

    public function GetVendorReturnAmount( $order,$database_name){
       
        $order_vendor_paybel_amount = OrderVendor::on($database_name)->where('order_id',$order->id)->where('order_status_option_id',"!=",'3')->select(DB::raw('sum(payable_amount) AS sum_of_order_payable_amount'))->first();
     
        $order_total_amount=  $order_vendor_paybel_amount->sum_of_order_payable_amount;
        
        $canceld_order_payments =VendorOrderCancelReturnPayment::on($database_name)->where('order_id',$order->id)->select(DB::raw('sum(wallet_amount) AS sum_of_wallet_amount'),DB::raw('sum(online_payment_amount) AS sum_of_online_payment_amount'))->first();
        
        $vendor_payble_amount = $order->vendors->first()->payable_amount;
        // vendor contribution in order
        $vendor_contribution_percentage = ($vendor_payble_amount / $order_total_amount) * 100;

        $vendor_loyalty_amount =  $vendor_loyalty_points = $vendor_wallet_amount = $vendor_loyalty_points_earned = $vendor_online_payment_amount = 0;   

        if($order->loyalty_points_used > 0){
            // get loyalty for vendor
            $total_loyalty_amount = $order->loyalty_amount_saved ;
            // get loyalty points as pr 1 rup (primery Currency)
            $redeem_points_per_primary_currency =  $order->loyalty_points_used /  $order->loyalty_amount_saved;
            
            // vendot loyalty amount in order
            $vendor_loyalty_amount =  ($total_loyalty_amount * $vendor_contribution_percentage ) / 100;

            // vendor loyalty points in order
            $vendor_loyalty_points  =  ($vendor_loyalty_amount * $redeem_points_per_primary_currency);
        }
        if($order->loyalty_points_earned > 0){
            $total_loyalty_points_earned = $order->loyalty_points_earned ;
            // get perticuler vendor loyalty point earnd 
            $vendor_loyalty_points_earned   =($total_loyalty_points_earned * $vendor_contribution_percentage ) / 100;
        }

        if($order->wallet_amount_used > 0){
            $order_total_wallet_amount =  $order->wallet_amount_used;
            // deduction  canceld order waller amount
            $order_total_wallet_amount = $order_total_wallet_amount -  $canceld_order_payments->sum_of_wallet_amount;

            $vendor_wallet_amount = ($order_total_wallet_amount * $vendor_contribution_percentage ) / 100;
        }
        if($order->payment_status == 1  ){
            $order_total_payable_amount =  $order->payable_amount;
            // deduction  canceld order online payment  amount
             $order_total_payable_amount = $order_total_payable_amount - $canceld_order_payments->sum_of_online_payment_amount;
             //vendo online payment contributuin in order
            $vendor_online_payment_amount = ($order_total_payable_amount * $vendor_contribution_percentage ) / 100;
       
        }
        
        $vendor_total_sum = $vendor_loyalty_amount +  $vendor_wallet_amount +  $vendor_online_payment_amount ;  

        $data['vendor_return_amount']           = $vendor_wallet_amount + $vendor_online_payment_amount;
        $data['vendor_loyalty_amount']          = $vendor_loyalty_amount;
        $data['vendor_wallet_amount']           = $vendor_wallet_amount;
        $data['vendor_online_payment_amount']   = $vendor_online_payment_amount;
        $data['vendor_total_sum']               = $vendor_total_sum;
        $data['vendor_contribution_percentage'] = $vendor_contribution_percentage;
        $data['vendor_loyalty_points']          = $vendor_loyalty_points;
        $data['vendor_loyalty_points_earned']   = $vendor_loyalty_points_earned;
       // pr($data);
        return  $data;

    }
}
