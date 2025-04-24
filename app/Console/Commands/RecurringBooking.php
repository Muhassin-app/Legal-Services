<?php

namespace App\Console\Commands;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\UserVendor;
use App\Models\UserDevice;
use App\Models\User;
use App\Models\NotificationTemplate;
use GuzzleHttp\Client;
use App\Models\Client as CP;
use App\Models\{ ClientPreference, OrderLongTermServiceSchedule,UserAddress,Product,Vendor,OrderVendor};
use Log;
use Carbon\Carbon;
use App\Models\Order;

class RecurringBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:create_recurring_order_for_dispatcher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send recurring order to dispatcher';

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
        /**
         * Recurring Booking Order send to dispather
         */

         try {
            $clients = CP::where('status', 1)->get();

            $dispatch_domain = '';


            foreach ($clients as $key => $client) {
                //Connect client connection
                $database_name  = 'royo_' . $client->database_name;
                $header         = $client->database_name;

                $default = [
                    'driver' => env('DB_CONNECTION', 'mysql'),
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'database' => $database_name,
                    'username' => $client->database_username,
                    'password' => $client->database_password,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => false,
                    'engine' => null
                ];
                Config::set("database.connections.$database_name", $default);
                DB::setDefaultConnection($database_name);

                $preference = ClientPreference::first();


                if (isset($preference) && @$preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url)){
                    $dispatch_domain = $preference;
                }else{
                    return false;
                }


                $today              = Carbon::now();
                $recurring_booking  = OrderLongTermServiceSchedule::whereDate('schedule_date', '=', $today )->where('type',2)->whereNull('dispatch_traking_url')->get();
                if($recurring_booking){
                    foreach($recurring_booking as $booking){
                        $recurring_data = OrderLongTermServiceSchedule::find($booking->id);
                        $order          = Order::where('order_number',$booking->order_number)->first();
                        $customer       = User::find($order->user_id);
                        $cus_address    = UserAddress::find($order->address_id);
                        $product        = Product::find($booking->order_vendor_product_id);
                        $vendor         = $product->vendor_id;

                        $tasks          = array();


                        $dynamic = uniqid($order->id . $vendor);
                        $vendor_details = Vendor::where('id', $vendor)->select('id', 'phone_no', 'email', 'name', 'latitude', 'longitude', 'address')->first();
                        $orderVendorDetails = OrderVendor::where('vendor_id', $vendor)->where('order_id', $order->id)->get()->first();
                        if(!empty($orderVendorDetails->web_hook_code))
                        {
                            $dynamic = $orderVendorDetails->web_hook_code;
                        }
                        $call_back_url = route('dispatch-order-update', $dynamic);

                        $tasks = array();
                        $meta_data = '';

                        $unique = $customer->code;
                        $team_tag = $unique . "_" . $vendor;

                        if (isset($order->scheduled_date_time) && !empty($order->scheduled_date_time)) {
                            $task_type = 'schedule';
                            $schedule_time = $order->scheduled_date_time ?? null;
                        }
                        else {
                            $task_type = 'now';
                        }

                        if (!empty($orderVendorDetails->scheduled_date_time) && $orderVendorDetails->scheduled_date_time > 0) {
                            $task_type = 'schedule';
                            $user = $customer;
                            $selectedDate = dateTimeInUserTimeZone($orderVendorDetails->scheduled_date_time, $user->timezone);
                            $slot = trim(explode("-", $orderVendorDetails->schedule_slot)[0]);

                            $slotTime = date('H:i:s', strtotime("$slot"));
                            $selectedDate = date('Y-m-d', strtotime($selectedDate));
                            $scheduleDateTime = $selectedDate . ' ' . $slotTime;
                            $schedule_time =  $scheduleDateTime ?? null;
                        }

                        if(checkColumnExists('orders', 'recurring_booking_type'))
                        {


                            $schedule_time = $booking->schedule_date;
                            $tasks[] = array(
                                            'task_type_id' => 1,
                                            'latitude' => $vendor_details->latitude ?? '',
                                            'longitude' => $vendor_details->longitude ?? '',
                                            'short_name' => '',
                                            'address' => $vendor_details->address ?? '',
                                            'post_code' => '',
                                            'barcode' => '',
                                            'flat_no'     => null,
                                            'email'       => $vendor_details->email ?? null,
                                            'phone_number' => $vendor_details->phone_no ?? null,
                                        );

                            $tasks[] = array(
                                            'task_type_id' => 2,
                                            'latitude' => $cus_address->latitude ?? '',
                                            'longitude' => $cus_address->longitude ?? '',
                                            'short_name' => '',
                                            'address' => $cus_address->address ?? '',
                                            'post_code' => $cus_address->pincode ?? '',
                                            'barcode' => '',
                                            'flat_no'     => $cus_address->house_number ?? null,
                                            'email'       => $customer->email ?? null,
                                            'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
                                        );

                            if ($customer->dial_code == "971") {
                                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                                $customerno = "0" . $customer->phone_number;
                            } else {
                                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
                            }
                            $client = CP::orderBy('id', 'asc')->first();
                            $postdata =  [
                                'order_number' =>  $order->order_number,
                                'customer_name' => $customer->name ?? 'Dummy Customer',
                                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                                'customer_dial_code' => $customer->dial_code ?? null,
                                'customer_email' => $customer->email ?? null,
                                'recipient_phone' => $customerno ?? rand(111111, 11111),
                                'recipient_email' => $customer->email ?? null,
                                'task_description' => "Order From :" . $vendor_details->name,
                                'allocation_type' => 'a',
                                'task_type' => $task_type,
                                'schedule_time' => $schedule_time ?? null,
                                'cash_to_be_collected' => $payable_amount ?? 0.00,
                                'order_number' => $order->order_number,
                                'barcode' => '',
                                'order_team_tag' => $team_tag,
                                'call_back_url' => $call_back_url ?? null,
                                'task' => $tasks,
                                'is_restricted' => $orderVendorDetails->is_restricted,
                                'vendor_id' => $vendor_details->id,
                                'order_vendor_id' => $orderVendorDetails->id,
                                'dbname' => $client->database_name,
                                'order_id' => $order->id,
                                'customer_id' => $order->user_id,
                                'user_icon' => $customer->image,
                                'vendor_name' => $vendor_details->name ?? null,
                                'tip_amount' => $order->tip_amount,
                                'payment_method' => $order->payment_method,
                            ];
                            //pr($postdata);
                            if ($orderVendorDetails->is_restricted == 1) {
                                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
                            }

                            $client = new Client([
                                'headers' => [
                                    'personaltoken' => $dispatch_domain->delivery_service_key,
                                    'shortcode' => $dispatch_domain->delivery_service_key_code,
                                    'content-type' => 'application/json'
                                ]
                            ]);

                            $url = $dispatch_domain->delivery_service_key_url;

                            $res = $client->post(
                                $url . '/api/task/create',
                                ['form_params' => ($postdata)]
                            );

                        $response = json_decode($res->getBody(), true);
                        if ($response && $response['task_id'] > 0) {
                            $dispatch_traking_url                   = $response['dispatch_traking_url'] ?? '';
                            $recurring_data->web_hook_code          = $dynamic;
                            $recurring_data->dispatch_traking_url   = $dispatch_traking_url;
                            $recurring_data->save();
                        }
                    }

                }

            }

                \DB::disconnect($database_name);
            }
        }catch (Exception $ex) {
            return $ex->getMessage();
        }
        return 0;


    }
}
