<?php

namespace App\Http\Traits;

use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Front\PickupDeliveryController;
use App\Http\Controllers\Front\UserSubscriptionController;
use DB;
use Auth;
use HttpRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Client as CP;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Traits\{ValidatorTrait, ApiResponser, SquareInventoryManager,smsManager};
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\{CaregoryKycDoc, Order, ProductVariant, OrderVendor, VendorOrderCancelReturnPayment, ClientPreference, ProductBooking, User, UserAddress, Vendor, OrderProduct, OrderProductDispatchRoute, VendorOrderProductDispatcherStatus, Product, OrderLongTermServices, VendorOrderStatus, VendorOrderDispatcherStatus, OrderLongTermServiceSchedule, UserDevice, SmsTemplate, Cart, ClientCurrency, LuxuryOption, CartProduct, CartAddon, CartCoupon, OrderProductPrescription, CartProductPrescription, UserVendor, VendorOrderProductStatus,NotificationTemplate,EmailTemplate,OrderLocations};
use Illuminate\Support\Facades\Redirect;

trait OrderTrait
{
    use ValidatorTrait, ApiResponser, SquareInventoryManager,smsManager;


    public function ProductVariantStock($order_id, $request='')
    {
        $order = Order::with(['vendors.products.pvariant'])->find($order_id);
        if (isset($order->vendors)) {
            foreach ($order->vendors as $vendor) {
                foreach ($vendor->products as $product) {
                    $ProductVariant = ProductVariant::find($product->variant_id);

                    if ($ProductVariant) {

                        $update_quantity  = $ProductVariant->quantity - $product->quantity;
                        if ($update_quantity < 0)
                            $update_quantity  = 0;

                        $ProductVariant->quantity  = $update_quantity;
                        $ProductVariant->save();

                        if(isset($ProductVariant->square_variant_id) && !empty($ProductVariant->square_variant_id))
                        $this->inventoryAdjustmentInSquarePos($ProductVariant->square_variant_id, $ProductVariant->quantity, "PHYSICAL_COUNT", "IN_STOCK");
                    }
                    if(@$request && $request->order_luxury_option_id == 4){
                        $ProductVariant->increment('rented_product_count', $product->quantity);
                    }
                }
            }
        }
        return 1;
    }


    public function CheckProductStockLimit($order_id,$admin_product_limit){
        $vendors=[];
        $order = Order::with(['vendors.products.pvariant'])->find($order_id);
        if (isset($order->vendors)) {
            foreach ($order->vendors as $vendor) {
                foreach ($vendor->products as $product) {
                    $ProductVariant = ProductVariant::find($product->variant_id);

                    if ($ProductVariant) {
                        if ($ProductVariant->quantity < $admin_product_limit){
                            array_push($vendors,$vendor->vendor_id);

                        }
                    }
                }
            }
            return $vendors;
        }

    }

    public function sendProductStockOutPushNotificationVendors($user_ids, $orderData)
    {


        $devices = UserDevice::where('is_vendor_app', 0)->whereNotNull('device_token')
            ->whereIn('user_id', $user_ids)
            ->pluck('device_token')
            ->toArray();



        $from = '';
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();
        if (! empty($devices) && ! empty($client_preferences->fcm_server_key)) {
            $from = $client_preferences->fcm_server_key;
        }
        $notification_content = NotificationTemplate::where('slug', 'product-stock-vendor')->first();
        if ($notification_content) {
            $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
            $data = [
                "registration_ids" => $devices,
                "notification" => [
                    'title' => $notification_content->subject,
                    'body' => $notification_content->content,
                    'sound' => "notification.wav",
                    "icon" => (! empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                    // 'click_action' => route('order.index'),
                    "android_channel_id" => "sound-channel-id"
                ],
                "data" => [
                    'title' => $notification_content->subject,
                    'body' => $notification_content->content,
                    'data' => $orderData,
                    'order_id' =>  $orderData->id,
                    'type' => "order_created"
                ],
                "priority" => "high"
            ];
            if (! empty($from)) {
                // helper function
                sendFcmCurlRequest($data);
            }

            // Individual Vendor App User Token
            $vendorAppUserDevices = UserDevice::where('is_vendor_app', 1)->whereNotNull('device_token')
                ->whereIn('user_id', $user_ids)
                ->pluck('device_token')
                ->toArray();

            if (! empty($vendorAppUserDevices) && ! empty($client_preferences->vendor_fcm_server_key)) {

                $from = $client_preferences->vendor_fcm_server_key;
                $data['registration_ids'] = $vendorAppUserDevices;

                $result = sendFcmCurlRequest($data);
            }
        }
    }

    public function ProductVariantStockIncrease($product)
    {
        $ProductVariant = ProductVariant::find($product->variant_id);
        if ($ProductVariant) {
            $update_quantity  = $ProductVariant->quantity + $product->quantity;
            if ($update_quantity < 0)
                $update_quantity  = 0;
            $ProductVariant->quantity  = $update_quantity;
            $ProductVariant->save();

            if(isset($ProductVariant->square_variant_id) && !empty($ProductVariant->square_variant_id))
            $this->inventoryAdjustmentInSquarePos($ProductVariant->square_variant_id, $ProductVariant->quantity, "PHYSICAL_COUNT", "IN_STOCK");
        }

        return 1;
    }

    public function inventryProductQuantityIncrease($order_id){

        $order = Order::with(['vendors.products.pvariant'])->find($order_id);
        if( isset($order->vendors )){
            foreach ($order->vendors as $vendor) {
                foreach ($vendor->products as $product) {
                    $client_preferences = ClientPreference::first();
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'shortcode' => $client_preferences->inventory_service_key_code,
                'content-type' => 'application/json'
            ]
        ]);
        $url = $client_preferences->inventory_service_key_url;

        $request = $client->post($url . '/api/v1/product-qunatity-increase', [
            'json' => ['product_variant' =>$product->variant_id ,'product_quantity'=>$product->quantity]
        ]);

        $response = json_decode($request->getBody());

                }
            }
        }
        return 1 ;
    }

    public function ProductVariantStockIncreaseByOrderId($order_id, $rental = '')
    {
        $order = Order::with(['vendors.products.pvariant'])->find($order_id);
        if( isset($order->vendors )){
            foreach ($order->vendors as $vendor) {
                foreach ($vendor->products as $product) {
                    $ProductVariant = ProductVariant::find($product->variant_id);
                    if ($ProductVariant) {
                        $update_quantity  = $ProductVariant->quantity + $product->quantity;
                        if($update_quantity < 0)
                        $update_quantity  = 0;
                        $ProductVariant->quantity  = $update_quantity;
                        $ProductVariant->save();

                        if(isset($ProductVariant->square_variant_id) && !empty($ProductVariant->square_variant_id))
                        $this->inventoryAdjustmentInSquarePos($ProductVariant->square_variant_id, $ProductVariant->quantity, "PHYSICAL_COUNT", "IN_STOCK");
                    }
                    if(@$rental && $rental == 'rental'){
                        $ProductVariant->decrement('rented_product_count', $product->quantity);
                    }
                }
            }
        }
        return 1;
    }

    public function GetVendorReturnAmount($request, $order)
    {

        $order_vendor_paybel_amount = OrderVendor::where('order_id', $order->id)->where('order_status_option_id', "!=", '3')->select(DB::raw('sum(payable_amount) AS sum_of_order_payable_amount'))->first();
        $order_total_amount =  $order_vendor_paybel_amount->sum_of_order_payable_amount;

        $canceld_order_payments = VendorOrderCancelReturnPayment::where('order_id', $order->id)->select(DB::raw('sum(wallet_amount) AS sum_of_wallet_amount'), DB::raw('sum(online_payment_amount) AS sum_of_online_payment_amount'))->first();
        $vendor_payble_amount = $order->vendors->first()->payable_amount;
        // vendor contribution in order
        $vendor_contribution_percentage = 0;
        if ($order_total_amount > 0) {
            $vendor_contribution_percentage = ($vendor_payble_amount / $order_total_amount) * 100;
        }

        $vendor_loyalty_amount =  $vendor_loyalty_points = $vendor_wallet_amount = $vendor_loyalty_points_earned = $vendor_online_payment_amount = 0;

        if ($order->loyalty_points_used > 0) {
            // get loyalty for vendor
            $total_loyalty_amount = $order->loyalty_amount_saved;

            // get loyalty points as pr 1 rup (primery Currency)
            $redeem_points_per_primary_currency = 0;
            if ($order->loyalty_amount_saved > 0) {
                $redeem_points_per_primary_currency =  $order->loyalty_points_used /  $order->loyalty_amount_saved;
            }

            // vendot loyalty amount in order
            $vendor_loyalty_amount =  ($total_loyalty_amount * $vendor_contribution_percentage) / 100;

            // vendor loyalty points in order
            $vendor_loyalty_points  =  ($vendor_loyalty_amount * $redeem_points_per_primary_currency);
        }
        if ($order->loyalty_points_earned > 0) {
            $total_loyalty_points_earned = $order->loyalty_points_earned;
            // get perticuler vendor loyalty point earnd
            $vendor_loyalty_points_earned   = ($total_loyalty_points_earned * $vendor_contribution_percentage) / 100;
        }

        if ($order->wallet_amount_used > 0) {
            $order_total_wallet_amount =  $order->wallet_amount_used;
            // deduction  canceld order waller amount
            $order_total_wallet_amount = $order_total_wallet_amount -  $canceld_order_payments->sum_of_wallet_amount;

            $vendor_wallet_amount = ($order_total_wallet_amount * $vendor_contribution_percentage) / 100;
        }
        if ($order->payment_status == 1) {
            $order_total_payable_amount =  $order->payable_amount;
            // deduction  canceld order online payment  amount
            $order_total_payable_amount = $order_total_payable_amount - $canceld_order_payments->sum_of_online_payment_amount;
            //vendo online payment contributuin in order
            $vendor_online_payment_amount = ($order_total_payable_amount * $vendor_contribution_percentage) / 100;
        }

        $vendor_total_sum = $vendor_loyalty_amount +  $vendor_wallet_amount +  $vendor_online_payment_amount;

        $vendor_return_amount = $vendor_wallet_amount + $vendor_online_payment_amount;

        // get what time order placed according to current time
        $orderPlacedTime = (strtotime(now()) - strtotime($order->created_at)) / 60; // in minutes

        // check admin cancellation charges
        $client_preference_detail = ClientPreference::first();
        if (($client_preference_detail->order_cancellation_time > 0) && ($orderPlacedTime > $client_preference_detail->order_cancellation_time)) {

            //online payment
            if ($vendor_return_amount > 0) {
                $vendor_return_amount = $vendor_return_amount - ($client_preference_detail->cancellation_percentage * $vendor_return_amount / 100);
            }
            //COD cancellation charges deduct from user wallet
            else {
                $order_total_payable_amount =  $order->payable_amount;

                // deduction canceld order online payment  amount
                $order_total_payable_amount = $order_total_payable_amount - $canceld_order_payments->sum_of_online_payment_amount;

                //vendo online payment contributuin in order
                $vendor_payment_amount = ($order_total_payable_amount * $vendor_contribution_percentage) / 100;

                $cancellation_charges = $client_preference_detail->cancellation_percentage * $vendor_payment_amount / 100;

                // Debite order cancellation charges
                if ($cancellation_charges > 0) {
                    $user   = auth()->user();
                    $wallet = $user->wallet;
                    $wallet->forceWithdrawFloat($cancellation_charges, ['Wallet has been <b>debited</b> cancellation charges for order number ' . $order->order_number]);
                }
            }
        }

        $data['vendor_return_amount']           = $vendor_return_amount;
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


    public function GetVendorProductReturnAmount($request, $order, $cancelledProductPrice)
    {

        $order_vendor_paybel_amount = OrderVendor::where('order_id', $order->id)->where('order_status_option_id', "!=", '3')->select(DB::raw('sum(payable_amount) AS sum_of_order_payable_amount'))->first();
        $order_total_amount =  $order_vendor_paybel_amount->sum_of_order_payable_amount;

        $canceld_order_payments = VendorOrderCancelReturnPayment::where('order_id', $order->id)->select(DB::raw('sum(wallet_amount) AS sum_of_wallet_amount'), DB::raw('sum(online_payment_amount) AS sum_of_online_payment_amount'))->first();

        $vendor_payble_amount = $cancelledProductPrice;
        // vendor contribution in order
        $vendor_contribution_percentage = 0;
        if ($order_total_amount > 0) {
            $vendor_contribution_percentage = ($vendor_payble_amount / $order_total_amount) * 100;
        }

        $vendor_loyalty_amount =  $vendor_loyalty_points = $vendor_wallet_amount = $vendor_loyalty_points_earned = $vendor_online_payment_amount = 0;

        if ($order->loyalty_points_used > 0) {
            // get loyalty for vendor
            $total_loyalty_amount = $order->loyalty_amount_saved;

            // get loyalty points as pr 1 rup (primery Currency)
            $redeem_points_per_primary_currency = 0;
            if ($order->loyalty_amount_saved > 0) {
                $redeem_points_per_primary_currency =  $order->loyalty_points_used /  $order->loyalty_amount_saved;
            }

            // vendot loyalty amount in order
            $vendor_loyalty_amount =  ($total_loyalty_amount * $vendor_contribution_percentage) / 100;

            // vendor loyalty points in order
            $vendor_loyalty_points  =  ($vendor_loyalty_amount * $redeem_points_per_primary_currency);
        }
        if ($order->loyalty_points_earned > 0) {
            $total_loyalty_points_earned = $order->loyalty_points_earned;
            // get perticuler vendor loyalty point earnd
            $vendor_loyalty_points_earned   = ($total_loyalty_points_earned * $vendor_contribution_percentage) / 100;
        }

        if ($order->wallet_amount_used > 0) {
            $order_total_wallet_amount =  $order->wallet_amount_used;
            // deduction  canceld order waller amount
            $order_total_wallet_amount = $order_total_wallet_amount -  $canceld_order_payments->sum_of_wallet_amount;

            $vendor_wallet_amount = ($order_total_wallet_amount * $vendor_contribution_percentage) / 100;
        }
        if ($order->payment_status == 1) {
            $order_total_payable_amount =  $order->payable_amount;
            // deduction  canceld order online payment  amount
            $order_total_payable_amount = $order_total_payable_amount - $canceld_order_payments->sum_of_online_payment_amount;
            //vendo online payment contributuin in order
            $vendor_online_payment_amount = ($order_total_payable_amount * $vendor_contribution_percentage) / 100;
        }

        $vendor_total_sum = $vendor_loyalty_amount +  $vendor_wallet_amount +  $vendor_online_payment_amount;

        $vendor_return_amount = $vendor_wallet_amount + $vendor_online_payment_amount;

        // get what time order placed according to current time
        $orderPlacedTime = (strtotime(now()) - strtotime($order->created_at)) / 60; // in minutes

        // check admin cancellation charges
        $client_preference_detail = ClientPreference::first();
        if (($client_preference_detail->order_cancellation_time > 0) && ($orderPlacedTime > $client_preference_detail->order_cancellation_time)) {

            //online payment
            if ($vendor_return_amount > 0) {
                $vendor_return_amount = $vendor_return_amount - ($client_preference_detail->cancellation_percentage * $vendor_return_amount / 100);
            }
            //COD cancellation charges deduct from user wallet
            else {
                $order_total_payable_amount =  $order->payable_amount;

                // deduction canceld order online payment  amount
                $order_total_payable_amount = $order_total_payable_amount - $canceld_order_payments->sum_of_online_payment_amount;

                //vendo online payment contributuin in order
                $vendor_payment_amount = ($order_total_payable_amount * $vendor_contribution_percentage) / 100;

                $cancellation_charges = $client_preference_detail->cancellation_percentage * $vendor_payment_amount / 100;

                // Debite order cancellation charges
                if ($cancellation_charges > 0) {
                    $user   = auth()->user();
                    $wallet = $user->wallet;
                    $wallet->forceWithdrawFloat($cancellation_charges, ['Wallet has been <b>debited</b> cancellation charges for order number ' . $order->order_number]);
                }
            }
        }

        $data['vendor_return_amount']           = $vendor_return_amount;
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


    // place Request To Dispatch for Appointment , OnDemand
    public function placeRequestToDispatchSingleProduct($order, $vendor, $dispatch_domain, $request)
    {
        try {
            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();
            $task_type = 'now';
            $schedule_time = '';
            $return_response = 2;
            $paymentSentAlready = 0;
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'phone_no', 'email', 'latitude', 'longitude', 'address','order_pre_time')->first();

            $order_vendor = OrderVendor::with(['products.product.categoryName', 'products.order_product_status'])->where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();

            foreach( $order_vendor->products as $product){
                \Log::info($product->dispatcher_status_option_id);
                if( $product->dispatcher_status_option_id < 2) {
                    \Log::info('log 11');

                    $allocation_type = 'a';
                    $agent = '';
                    if ($order->payment_option_id == 1 && ($order->payable_amount >0)) {
                        $cash_to_be_collected = 'Yes';
                        $payable_amount = $order_vendor->payable_amount + $order_vendor->taxable_amount;
                    } else {
                        $cash_to_be_collected = 'No';
                        $payable_amount = 0.00;
                    }

                    $tasks = array();
                    $meta_data = '';

                    $unique = Auth::user()->code;
                    $team_tag = $unique . "_" . $vendor;
                    if (!empty($product->scheduled_date_time) && $product->scheduled_date_time > 0) {
                        $task_type = 'schedule';
                        $user = Auth::user();
                        $selectedDate = dateTimeInUserTimeZone($product->scheduled_date_time, $user->timezone);
                        $slot = trim(explode("-", $product->schedule_slot)[0]);

                        $slotTime = date('H:i:s', strtotime("$slot"));
                        $selectedDate = date('Y-m-d', strtotime($selectedDate));
                        $scheduleDateTime = $selectedDate . ' ' . $slotTime;
                        $schedule_time =  $scheduleDateTime ?? null;
                    }
                    $rejectable_order = isset($dispatch_domain['rejectable_order'])? $dispatch_domain['rejectable_order'] : 0;

                    $task_type_id = $dispatch_domain['service_type'] == 'appointment' ?  3 : 1;
                    $service_time = $product->product->first() ? $product->product->minimum_duration_min : 0;

                    if( $rejectable_order ==1){
                        $service_time = '60';
                    }
                    if ($product->dispatch_agent_id) {
                        $allocation_type = 'm';
                        $agent = $product->dispatch_agent_id;
                    }
                    $is_assign_warehouse = $dispatch_domain['service_type'] == 'rental' ? 1 : 0;
                    $order__product_status_option_id = 1;
                    if(!empty($product->order_product_status) && $product->order_product_status->order_status_option_id == 3){
                        $order__product_status_option_id = 0;
                    }
                    if ($order__product_status_option_id > 0 ) {
                        $tasks[] = array(
                            'task_type_id' => $task_type_id,
                            'latitude' => $vendor_details->latitude ?? '',
                            'longitude' => $vendor_details->longitude ?? '',
                            'short_name' => '',
                            'address' => $vendor_details->address ?? '',
                            'post_code' => '',
                            'barcode' => '',
                            'flat_no' => null,
                            'email' => $vendor_details->email ?? null,
                            'phone_number' => $vendor_details->phone_no ?? null,
                            'appointment_duration' => $dispatch_domain['service_type'] == 'appointment' ? $service_time : null,
                        );

                        if($product->dispatch_agent_id){
                            $allocation_type = 'm';
                            $agent = $product->dispatch_agent_id;
                        }
                        $service_types = ['on_demand', 'rental'];
                        if(@in_array($dispatch_domain['service_type'], $service_types))
                        {
                            $tasks[] = array(
                                'task_type_id' => 2,
                                'latitude' => $cus_address->latitude ?? '',
                                'longitude' => $cus_address->longitude ?? '',
                                'short_name' => '',
                                'address' => $cus_address->address ?? '',
                                'post_code' => $cus_address->pincode ?? '',
                                'barcode' => '',
                                'flat_no' => $cus_address->house_number ?? null,
                                'email' => $customer->email ?? null,
                                'phone_number' => ($customer->dial_code . $customer->phone_number) ?? null,
                            );
                        }

                        if ($customer->dial_code == "971") {
                            // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                            $customerno = "0" . $customer->phone_number;
                        } else {
                            // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                            $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
                        }
                        $orderfromName =  $vendor_details->name;
                        if($rejectable_order ==1){
                            $orderfromName =  $customer->name ?? $vendor_details->name;
                        }
                        $category_name = isset($product->product->categoryName) ? @$product->product->categoryName->name : 'na' ;
                        $driverCost  = 0;
                        $driverCost  =($product->is_price_buy_driver ==1) ?  $product->price :0;
                        $specific_instruction = (isset($product->specific_instruction) && ($product->specific_instruction !='')) ? $product->specific_instruction : $order->specific_instruction;
                        $client = CP::orderBy('id', 'asc')->first();
                        for ($x = 1; $x <= $product->quantity; $x++) {
                            //  send all payment to fist order
                            if( $paymentSentAlready == 0){
                                $paymentSentAlready =1;
                            }else{
                                $cash_to_be_collected = 'No';
                                $payable_amount = 0.00;
                            }
                            $dynamic = uniqid($order->id . $vendor . $product->product_id.$x);


                            if (isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain)
                                $call_back_url = "https://" . $client->custom_domain . "/dispatch-order-product-status-update/" . $dynamic;
                            else
                                $call_back_url = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/dispatch-order-product-status-update/" . $dynamic;

                            $postdata =  [
                                'order_number'  =>  $order->order_number,
                                'customer_name' => $customer->name ?? 'Dummy Customer',
                                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                                'customer_dial_code' => $customer->dial_code ?? null,
                                'customer_email' => $customer->email ?? null,
                                'recipient_phone' => $customerno ?? rand(111111, 11111),
                                'recipient_email' => $customer->email ?? null,
                                'task_description' => "Order From: " . $orderfromName,
                                'allocation_type' => $allocation_type,
                                'task_type' => $task_type,
                                'schedule_time' => $schedule_time ?? null,
                                'cash_to_be_collected' => $payable_amount ?? 0.00,
                                'barcode' => '',
                                'order_team_tag' => $team_tag,
                                'call_back_url' => $call_back_url ?? null,
                                'task' => $tasks,
                                'is_restricted' => $order_vendor->is_restricted,
                                'vendor_id' => $vendor_details->id,
                                'order_vendor_id' => @$order_vendor->id,
                                'dbname' => @$client->database_name,
                                'order_id' => @$order->id,
                                'customer_id' => @$order->user_id,
                                'user_icon' => $customer->image,
                                'agent'     => $agent,
                                'task_type_id' =>$task_type_id, //  for add agent booking in case of appointment
                                'service_time' =>  $service_time,
                                'is_assign_warehouse' => $is_assign_warehouse,
                                'rejectable_order' =>  $rejectable_order,
                                'category_name' =>  $category_name,
                                'specific_instruction' =>  $specific_instruction,
                                'driverCost' =>  $driverCost,
                                'order_pre_time'=>$vendor_details->order_pre_time,
                                'tip_amount'=>$order->tip_amount??0


                            ];

                            if($order_vendor->is_restricted == 1)
                            {
                                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
                            }


                            $client = new Client([
                                'headers' => [
                                    'personaltoken' => $dispatch_domain['service_key'],
                                    'shortcode'     => $dispatch_domain['service_key_code'],
                                    'content-type'  => 'application/json'
                                ]
                            ]);

                            $url = $dispatch_domain['service_key_url'];
                            \Log::info($url);
                            \Log::info($postdata);
                            $res = $client->post(
                                $url . '/api/task/create',
                                ['form_params' => ($postdata)]
                            );
                            $response = json_decode($res->getBody(), true);
                            if ($response && $response['task_id'] > 0) {
                                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';

                                $dispatch_route                                 = new OrderProductDispatchRoute();
                                $dispatch_route->order_id                       = $request->order_id ;
                                $dispatch_route->order_vendor_id                = $product->order_vendor_id ;
                                $dispatch_route->order_vendor_product_id        = $product->id ;
                                $dispatch_route->web_hook_code                  = $dynamic;
                                $dispatch_route->dispatch_traking_url           = $dispatch_traking_url ;
                                $dispatch_route->dispatcher_status_option_id    = 1 ;
                                $dispatch_route->order_status_option_id         = 1 ;
                                $dispatch_route->save();

                                $update = VendorOrderProductDispatcherStatus::updateOrCreate([
                                    'dispatcher_id' => null,
                                    'order_id' =>  $request->order_id,
                                    'dispatcher_status_option_id' => 1,
                                    'vendor_id' =>  $request->vendor_id,
                                    'order_product_route_id' => $dispatch_route->id
                                ]);

                                $return_response = 1;
                            }

                        }
                        $update = VendorOrderProductStatus::updateOrCreate([
                            'order_id' =>    $order_vendor->order_id,
                            'dispatcher_status_option_id' => 1,
                            'order_status_option_id' =>  2,
                            'order_vendor_id' =>    $order_vendor->id,
                            'order_vendor_product_id' =>  $product->id,
                        ]);
                        OrderProduct::where('id',$product->id)->update(['dispatcher_status_option_id'=>1,'order_status_option_id'=>2]);

                    }
                }

            }
            return $return_response;
        } catch (\Exception $e) {
            // dd('ssfsd');
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }


    public function bookingSlot($request, $order_product_id=null, $order_id=null)
    {
        $request = (object) $request;

        try {
            DB::beginTransaction(); //Initiate transaction

            $start_time = date("Y-m-d H:i:s", strtotime($request->start_date));
            $end_time = date("Y-m-d H:i:s", strtotime($request->end_date));


            $start_end_block_time = $start_time;

            $status = ProductBooking::Create([
                'memo' => $request->memo,
                'variant_id' => $request->variant_id,
                'product_id' => $request->product_id,
                'order_vendor_id' => $request->order_vendor_id,
                'start_date_time' => $start_time,
                'booking_type' => 'new_booking',
                'end_date_time' => $end_time,
                'order_user_id' => $request->order_user_id,
                'booking_start_end' => $start_end_block_time,
                'order_vendor_product_id' => $order_product_id,
                'order_id' => $order_id
            ]);
            DB::commit(); //Commit transaction after all the operations
            return 1;
        } catch (Exception $e) {
            DB::rollBack();
            return 0;
        }
    }

    /** update vendor rating
     * @author sudhanshu sharma
     */
    public function updateVendorRating($vendor_id)
    {
        $vendor_rating = 0;

        if ($vendor_id != null & $vendor_id > 0) {
            $vendor_rating = Product::where('vendor_id', $vendor_id)
                ->avg('averageRating');
        }

        Vendor::where('id', $vendor_id)->update(['rating' => $vendor_rating]);
        return $vendor_rating;

    }

    /**
     * get or update vendor rating if rating is null
     * @author sudhanshu sharma
     */
    public function getVendorRating($vendor_id)
    {
        $vendor_rating = 0;

        $vendor = Vendor::select('id', 'rating')->where('id', $vendor_id)->first();

        if ($vendor && $vendor->rating == null) {

            $vendor_rating = $this->updateVendorRating($vendor_id);
            return number_format($vendor_rating, 1);
        } else if ($vendor) {

            return number_format($vendor->rating, 1);
        } else {

            return number_format($vendor_rating, 1);
        }
    }
    public function getUserLongTermService($user, $langId, $currency_id)
    {
        $longTermOrders = Order::with([
            'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            }, 'vendors.dineInTable.category', 'vendors.products', 'vendors.products.media.image', 'vendors.products.pvariant.media.pimage.image', 'user', 'address'
        ], 'vendors.products.product')

            ->where('is_long_term', 1)
            ->where(function ($q1) {

                $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1]);
                $q1->orWhere(function ($q2) {
                    $q2->where('payment_option_id', 1);
                });
            })
            ->where('orders.user_id', $user->id)
            ->orderBy('orders.id', 'DESC')->select('*', 'id as total_discount_calculate')->paginate(10);
        foreach ($longTermOrders as $order) {

            $orderStatus = '';
            foreach ($order->vendors as $vendor) {

                $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)->where('vendor_id', $vendor->vendor_id)->orderBy('id', 'DESC')->first();

                $vendor->order_status = ucfirst($vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '');
                foreach ($vendor->products as $product) {

                    $product->longTermSchedule =  OrderLongTermServices::with(['schedule', 'product.primary', 'addon.set', 'addon.option', 'addon.option.translation' => function ($q) use ($langId) {
                        $q->select('addon_option_translations.id', 'addon_option_translations.addon_opt_id', 'addon_option_translations.title', 'addon_option_translations.language_id');
                        $q->where('addon_option_translations.language_id', $langId);
                        $q->groupBy('addon_option_translations.addon_opt_id', 'addon_option_translations.language_id');
                    }])->where('order_product_id', $product->id)->first();
                    if (isset($product->longTermSchedule->addon) && !empty($product->longTermSchedule->addon)) {

                        foreach ($product->longTermSchedule->addon as $ck => $addons) {
                            $addons->option->translation_title = ($addons->option->translation->isNotEmpty()) ? $addons->option->translation->first()->title : '';
                        }
                    }
                    if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                        $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                    } elseif ($product->media->isNotEmpty() && !is_null($product->media->first()->image)) {
                        $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                    } else {
                        $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                    }
                }
                if ($vendor->delivery_fee > 0) {
                    $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                    $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                    $ETA = $order_pre_time + $user_to_vendor_time;
                    $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                }
                if ($vendor->dineInTable) {
                    $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                    $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                    $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                }

                $vendor->vendor_dispatcher_status = VendorOrderDispatcherStatus::whereNotIn('dispatcher_status_option_id', [2])
                    ->select('*', 'dispatcher_status_option_id as status_data')
                    ->where('order_id', $order->id);
                if (isset($vendor->vendor->id))
                    $vendor->vendor_dispatcher_status = $vendor->vendor_dispatcher_status->where('vendor_id', $vendor->vendor->id);

                $vendor->vendor_dispatcher_status = $vendor->vendor_dispatcher_status->get();
                $vendor->vendor_dispatcher_status_count = 6;
                $vendor->dispatcher_status_icons = [asset('assets/icons/driver_1_1.png'), asset('assets/icons/driver_2_1.png'), asset('assets/icons/driver_4_1.png'), asset('assets/icons/driver_3_1.png'), asset('assets/icons/driver_4_2.png'), asset('assets/icons/driver_5_1.png')];
            }
        }
        return $longTermOrders;
    }

    public function checkIfanyServiceProductLastMileon($request)
    {

        $order_dispatchs = 2;
        $checkdeliveryFeeAdded = OrderVendor::with('LuxuryOption')->where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $luxury_option_id = $checkdeliveryFeeAdded->LuxuryOption ? $checkdeliveryFeeAdded->LuxuryOption->luxury_option_id : 1;
        $totalSchudelCount = @$checkdeliveryFeeAdded->products->first()->LongTermService->service_quentity;
        $serviceProductLastMile = @$checkdeliveryFeeAdded->products->first()->LongTermService->product->Requires_last_mile;
        $product_dispatcher_tag  = @$checkdeliveryFeeAdded->products->first()->LongTermService->product->tags;
        $product_category_type_id  = @$checkdeliveryFeeAdded->products->first()->LongTermService->product->category->categoryDetail->type_id ?? 0;
        $preference = ClientPreference::first();

        /// luxury option 8 ( static ) for appointment you can check it on luxuryOptionSeeder
        if ($luxury_option_id == 8) { // only for appointment type

            if ($preference->need_appointment_service == 1 && !empty($preference->appointment_service_key_code) && !empty($preference->appointment_service_key) && !empty($preference->appointment_service_key_url))
                $Appointment = 0;

            /**if its not long_term_service */

            if (isset($product_dispatcher_tag) && !empty($product_dispatcher_tag)) {
                if ($Appointment == 0  && $checkdeliveryFeeAdded->delivery_fee <= 0) {

                    $dispatch_domain = [
                        'service_key'      => $preference->appointment_service_key,
                        'service_key_code' => $preference->appointment_service_key_code,
                        'service_key_url'  => $preference->appointment_service_key_url,
                        'service_type'     => 'appointment'
                    ];

                    $order_dispatchs = $this->placeRequestToDispatchServiceProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                    if ($order_dispatchs && $order_dispatchs == 1) {
                        $Appointment = 1;
                        return 1;
                    }
                }
            }
        }

        if ($luxury_option_id == 6) { // only for on_demand type
            if ($preference->need_dispacher_home_other_service == 1 && !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_code) && !empty($preference->dispacher_home_other_service_key_url)) {

                $OnDemand = 0;

                if (isset($product_dispatcher_tag) && !empty($product_dispatcher_tag) && $product_category_type_id == 8) {
                    if ($checkdeliveryFeeAdded->delivery_fee > 0) {

                        $dispatch_domain = [
                            'service_key'      => $preference->dispacher_home_other_service_key,
                            'service_key_code' => $preference->dispacher_home_other_service_key_code,
                            'service_key_url'  => $preference->dispacher_home_other_service_key_url,
                            'service_type'     => 'on_demand'
                        ];

                        $order_dispatchs = $this->placeRequestToDispatchServiceProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                        if ($order_dispatchs && $order_dispatchs == 1) {
                            $OnDemand = 1;
                            return 1;
                        }
                    }
                }
            }
        }

        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url)) {

            $dispatch_domain = [
                'service_key'      => $preference->delivery_service_key,
                'service_key_code' => $preference->delivery_service_key_code,
                'service_key_url'  => $preference->delivery_service_key_url,
                'service_type'     => 'delivery'
            ];
            if ($checkdeliveryFeeAdded && $checkdeliveryFeeAdded->delivery_fee > 0.00) {
                $order_dispatchs = $this->placeRequestToDispatchServiceProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
            }


            if ($order_dispatchs && $order_dispatchs == 1)
                return 1;
        }
        return 2;
    }

    /**
     * placeRequestToDispatchServiceProduct
     *
     * @param  mixed $request
     * @return void
     * place Request To Dispatch for LongTerm Service
     */
    // place Request To Dispatch for LongTerm Service
    public function placeRequestToDispatchServiceProduct($order, $vendor, $dispatch_domain, $request)
    {

        try {

            $order = Order::find($order);

            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();
            $task_type = 'schedule';
            $schedule_time = '';
            $return_response = 2;
            $paymentSentAlready = 0;
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'phone_no', 'email', 'latitude', 'longitude', 'address','order_pre_time')->first();

            $order_vendor = OrderVendor::with('products.product', 'products.LongTermService.schedule')->where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
            $product = $order_vendor->products->first();
            $schedules = $order_vendor->products->first()->LongTermService->schedule;


            $allocation_type = 'a';
            $agent = '';
            if ($order->payment_option_id == 1) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order_vendor->payable_amount + $order_vendor->taxable_amount;
            } else {
                $cash_to_be_collected = 'No';
                $payable_amount = 0.00;
            }
            $tasks = array();
            $meta_data = '';

            $unique = Auth::user()->code;
            $team_tag = $unique . "_" . $vendor;


            $task_type_id = $dispatch_domain['service_type'] == 'appointment' ?  3 : 1;
            $service_time = $product->product->first() ? $product->product->minimum_duration_min : 0;

            $tasks[] = array(
                'task_type_id' => $task_type_id,
                'latitude'     => $vendor_details->latitude ?? '',
                'longitude'    => $vendor_details->longitude ?? '',
                'short_name'   => '',
                'address'      => $vendor_details->address ?? '',
                'post_code'    => '',
                'barcode'      => '',
                'flat_no'     => null,
                'email'       => $vendor_details->email ?? null,
                'phone_number' => $vendor_details->phone_no ?? null,
                'appointment_duration' =>  $dispatch_domain['service_type'] == 'appointment' ?  $service_time  : null,
            );
            if ($product->dispatch_agent_id) {
                $allocation_type = 'm';
                $agent = $product->dispatch_agent_id;
            }
            if ($dispatch_domain['service_type'] != 'appointment') {
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
            }
            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }
            $client = CP::orderBy('id', 'asc')->first();

            //  send all payment to fist order

            $postdata =  [
                'order_number'  =>  $order->order_number,
                'customer_name' => $customer->name ?? 'Dummy Customer',
                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                'customer_dial_code' => $customer->dial_code ?? null,
                'customer_email' => $customer->email ?? null,
                'recipient_phone' => $customerno ?? rand(111111, 11111),
                'recipient_email' => $customer->email ?? null,
                'task_description' => "Order From :" . $vendor_details->name,
                'allocation_type' => $allocation_type,
                'task_type' => $task_type,
                'schedule_time' => $schedule_time ?? null,
                'cash_to_be_collected' => $payable_amount ?? 0.00,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'is_restricted' => $order_vendor->is_restricted,
                'vendor_id' => $vendor_details->id,
                'order_vendor_id' => @$order_vendor->id,
                'dbname' => @$client->database_name,
                'order_id' => @$order->id,
                'customer_id' => @$order->user_id,
                'user_icon' => $customer->image,
                'agent'     => $agent,
                'task_type_id' => $task_type_id, //  for add agent booking in case of appointment
                'service_time' =>  $service_time,
                'order_pre_time'=>$vendor_details->order_pre_time,
                'tip_amount'=>$order->tip_amount??0

            ];


            if ($order_vendor->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }
            $paymentSentAlready = 0;
            foreach ($schedules as $key => $schedule) {

                if ($paymentSentAlready == 0) {
                    $paymentSentAlready = 1;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
                $dynamic = uniqid($order->id . $vendor . $product->product_id . ($key = 1));

                $call_back_url = route('dispatch-order-service-status-update', $dynamic);
                $postdata['call_back_url'] = $call_back_url;
                $postdata['schedule_time'] = $schedule->schedule_date;
                $postdata['cash_to_be_collected'] = $payable_amount;


                $client = new Client([
                    'headers' => [
                        'personaltoken' => $dispatch_domain['service_key'],
                        'shortcode'     => $dispatch_domain['service_key_code'],
                        'content-type'  => 'application/json'
                    ]
                ]);

                $url = $dispatch_domain['service_key_url'];
                $res = $client->post(
                    $url . '/api/task/create',
                    ['form_params' => ($postdata)]
                );
                $response = json_decode($res->getBody(), true);
                if ($response && $response['task_id'] > 0) {
                    $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';


                    $schedule->web_hook_code                  = $dynamic;
                    $schedule->dispatch_traking_url           = $dispatch_traking_url;
                    $schedule->dispatcher_status_option_id    = 1;
                    $schedule->order_status_option_id         = 1;
                    $schedule->save();

                    $update = VendorOrderProductDispatcherStatus::updateOrCreate([
                        'dispatcher_id' => null,
                        'order_id' =>  $request->order_id,
                        'dispatcher_status_option_id' => 1,
                        'vendor_id' =>  $request->vendor_id,
                        'long_term_schedule_id' => $schedule->id
                    ]);
                }
            }

            return 1;
        } catch (\Exception $e) {
            //return 2;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * updateBooking
     *
     * @param  mixed $request
     * @return void
     * update long term order booking  update
     */
    public function updateLongTermBooking($request)
    {

        try {
            $BookingSchedule  = OrderLongTermServiceSchedule::with('OrderService.orderProduct.order')->find($request->service_id);
            if ($BookingSchedule) {
                // cancel order to dispatcher
                $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $BookingSchedule->dispatch_traking_url);
                $response = Http::get($dispatch_traking_url);
                //order cancelled
                $orderUserId[]              =  @$BookingSchedule->OrderService->orderProduct->order->user_id ?? 0;
                $order_number               =  @$BookingSchedule->OrderService->orderProduct->order->order_number ?? '';
                $BookingSchedule->status    = 1;
                $BookingSchedule->save();
                $this->sendOrderBookingNotification($orderUserId, $order_number);
            }
            return $BookingSchedule;
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
    public function sendOrderBookingNotification($user_ids, $order_number, $NotificationTemplateId = '1')
    {
        $devices = UserDevice::where('is_vendor_app', 0)->whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();


        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {

            $body_content = "Your Long term Booking no:({order_id}) has been Completed";
            $body_content = str_ireplace("{order_id}", "#" . $order_number, $body_content);
            $subject = 'Long term booking Completed';
            if ($body_content) {

                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $subject,
                        'body'  => $body_content,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',

                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $subject,
                        'body'  => $body_content,
                    ],
                    "priority" => "high"
                ];
                sendFcmCurlRequest($data);
            }
        }
    }

    public function sendTrackingUrlSMS($order,$order_id='', $vendor_id = '')
    {
        $user = User::find($order['user_id']);
        $prefer = ClientPreference::select('sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'currency_id')->first();
        if ($user['dial_code'] == "971") {
            $to = '+' . $user['dial_code'] . "0" . $user['phone_number'];
        } else {
            $to = '+' . $user['dial_code'] . $user['phone_number'];
        }
        $provider = $prefer['sms_provider'];
        $order    = Order::where('id',$order_id)->with('orderStatusVendor', 'ordervendor')->first();

        if (isset($order->orderStatusVendor)) {
            $order_status = '';
            foreach ($order->orderStatusVendor as $key => $status) {
                if ($status->order_status_option_id  == 1) {
                    $order_status = "Placed";
                }
                if ($status->order_status_option_id  == 2) {
                    $order_status = "Accepted";
                }
                if ($status->order_status_option_id  == 4) {
                    $order_status = "Processing";
                }
                if ($status->order_status_option_id  == 5) {
                    $order_status = "Out For Delivery";
                }
                if ($status->order_status_option_id  == 6) {
                    $order_status = "Delivered";
                }
            }
        }

        $tracking_url = $order->ordervendor->dispatch_traking_url;

        $tracking_url = get_tiny_url($tracking_url);

        $keyData = ['{user_name}' => $user['name'] ?? '', '{order_number}' => $order['order_number'] ?? '', '{track_url}' => $tracking_url ?? '', '{order_status}' => $order_status ?? ''];

        $checkSeeder = SmsTemplate::where('slug', 'order-tracking-url')->count();
        if ($checkSeeder > 0) {
            $body = sendSmsTemplate('order-tracking-url', $keyData);

            if (!empty($prefer['sms_provider'])) {

                $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
            }
        }
    }

    public function sendAccessTrackingUrlSMS($user, $order, $vendor_id = '')
    {
        $prefer = ClientPreference::select('sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'currency_id')->first();
        if ($user['dial_code'] == "971") {
            $to = '+' . $user['dial_code'] . "0" . $user['phone_number'];
        } else {
            $to = '+' . $user['dial_code'] . $user['phone_number'];
        }
        $provider = $prefer['sms_provider'];

        $phoneCode = mt_rand(100000, 999999);
        $sendTime  = Carbon::now()->addMinutes(10)->toDateTimeString();

        $user                                       = User::find($user['id']);
        $user->track_order_phone_token              = $phoneCode;
        $user->track_order_phone_token_valid_till   = $sendTime;
        $user->save();



        $keyData = ['{otp_code}' => $phoneCode ?? ''];

        $checkSeeder = SmsTemplate::where('slug', 'otp-sms-tracking-url')->count();
        if ($checkSeeder > 0) {
            $body = sendSmsTemplate('otp-sms-tracking-url', $keyData);

            if (!empty($prefer['sms_provider'])) {

                $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
            }
        }
    }

    /**
     * check order days of return / replace
     */
    public function checkOrderDaysForReturn($order, $days)
    {
        if ($days == 0) {
            return false;
        }
        $date = Carbon::parse($order->created_at)->addDays($days); // enddate for return
        $today = $dt = Carbon::now();

        if ($date >= $today) {
            return true;
        }
        return false;
    }

    public function editOrderInCart($orderid)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $langId = Session::get('customerLanguage') ?? '1';
            $new_session_token = session()->get('_token');
            $client_currency = ClientCurrency::where('is_primary', '=', 1)->first();

            $orderdata = Order::where('id', $orderid)->with(['vendors.products.addon', 'vendors.products.LongTermService.addon', 'editingInCart'])->first();

            if (!empty($orderdata)) :

                $cart = NULL;
                if ($user) :
                    $cart = Cart::where('user_id', $user->id)->first();
                else :
                    $cart = Cart::where('unique_identifier', session()->get('_token'))->first();
                endif;

                if (!empty($cart)) :
                    CartProduct::where('cart_id', $cart->id)->delete();
                    Cart::where('id', $cart->id)->delete();
                    CartProductPrescription::where('cart_id', $cart->id)->delete();
                endif;

                $cart_detail = [
                    'is_gift' => $orderdata->is_gift,
                    'status' => '0',
                    'item_count' => 0,
                    'currency_id' => $client_currency->currency_id,
                    'unique_identifier' => !$user ? $new_session_token : '',
                    'schedule_type' => (!empty($orderdata->scheduled_date_time)) ? "schedule" : '',
                    'scheduled_date_time' => (!empty($orderdata->scheduled_date_time)) ? $orderdata->scheduled_date_time : NULL,
                    'order_id' => $orderdata->id,
                    'scheduled_slot' => (!empty($orderdata->scheduled_slot)) ? $orderdata->scheduled_slot : NULL,
                    'address_id' => (!empty($orderdata->address_id)) ? $orderdata->address_id : NULL,
                    'comment_for_pickup_driver' => (!empty($orderdata->comment_for_pickup_driver)) ? $orderdata->comment_for_pickup_driver : NULL,
                    'comment_for_dropoff_driver' => (!empty($orderdata->comment_for_dropoff_driver)) ? $orderdata->comment_for_dropoff_driver : NULL,
                    'comment_for_vendor' => (!empty($orderdata->comment_for_vendor)) ? $orderdata->comment_for_vendor : NULL,
                    'schedule_pickup' => (!empty($orderdata->schedule_pickup)) ? $orderdata->schedule_pickup : NULL,
                    'schedule_dropoff' => (!empty($orderdata->schedule_dropoff)) ? $orderdata->schedule_dropoff : NULL,
                    'specific_instructions' => (!empty($orderdata->specific_instructions)) ? $orderdata->specific_instructions : NULL,
                ];

                if (Session::has('vendorType')) :
                    Session::forget('vendorType');
                endif;
                $luxury_option = LuxuryOption::where('id', $orderdata->luxury_option_id)->first();
                Session::put('vendorType', $luxury_option->title);
                //Orders-----------------
                //id, created_by, order_number, scheduled_date_time, payment_option_id, user_id, address_id, is_deleted, currency_id, loyalty_membership_id, luxury_option_id, loyalty_points_used, loyalty_amount_saved, loyalty_points_earned, paid_via_wallet, paid_via_loyalty, total_amount, wallet_amount_used, subscription_discount, total_discount, total_delivery_fee, taxable_amount, tip_amount, payable_amount, tax_category_id, created_at, updated_at, payment_method, payment_status, comment_for_pickup_driver, comment_for_dropoff_driver, comment_for_vendor, schedule_pickup, schedule_dropoff, specific_instructions, is_gift, total_service_fee, shipping_delivery_type, scheduled_slot, total_container_charges, viva_order_id, fixed_fee_amount, type, friend_name, friend_phone_number, total_other_taxes, dropoff_scheduled_slot, user_latitude, user_longitude, additional_price, total_toll_amount, is_postpay, is_long_term
                $cart_data = Cart::updateOrCreate(['user_id' => $user->id], $cart_detail);

                $OrderProductPrescription = OrderProductPrescription::where('order_id', $orderdata->id)->get();
                foreach ($OrderProductPrescription as $prescription) :
                    $CartProductPrescription = new CartProductPrescription();
                    $CartProductPrescription->cart_id = $cart_data->id;
                    $CartProductPrescription->vendor_id = $prescription->vendor_id;
                    $CartProductPrescription->product_id = $prescription->product_id;
                    $CartProductPrescription->prescription = $prescription->getRawOriginal('prescription');
                    $CartProductPrescription->save();
                endforeach;

                if (!empty($orderdata->address_id)) :
                    UserAddress::where('user_id', $user->id)->update(['is_primary' => 0]);
                    UserAddress::where('id', $orderdata->address_id)->where('user_id', $user->id)->update(['is_primary' => 1]);
                endif;
                foreach ($orderdata->vendors as $ordervendorproducts) :
                    //Order_vendors---------------
                    foreach ($ordervendorproducts->products as $orderproduct) :
                        //Order_vendor_products-------------
                        $cart_product_detail = [
                            'status'                        => '0',
                            'is_tax_applied'                => '1',
                            'created_by'                    => $user->id,
                            'cart_id'                       => $cart_data->id,
                            'quantity'                      => $orderproduct->quantity ?? 1,
                            'order_quantity'                => $orderproduct->quantity ?? 1,
                            'vendor_id'                     => $ordervendorproducts->vendor_id,
                            'product_id'                    => $orderproduct->product_id,
                            'variant_id'                    => $orderproduct->variant_id,
                            'user_product_order_form'       => $orderproduct->user_product_order_form,
                            'currency_id'                   => $client_currency->currency_id,
                            'luxury_option_id'              => ($orderdata->luxury_option_id) ? $orderdata->luxury_option_id : 0,
                            'start_date_time'               => ($orderproduct->start_date_time) ? $orderproduct->start_date_time : NULL,
                            'end_date_time'                 => ($orderproduct->end_date_time) ? $orderproduct->end_date_time : NULL,
                            'additional_increments_hrs_min' => ($orderproduct->additional_increments_hrs_min) ? $orderproduct->additional_increments_hrs_min : NULL,
                            'total_booking_time'            => $orderproduct->total_booking_time,
                            'service_day'                   => (!empty($orderproduct->LongTermService)) ? $orderproduct->LongTermService->service_day : null,
                            'service_date'                  => (!empty($orderproduct->LongTermService)) ? $orderproduct->LongTermService->service_date : null,
                            'service_period'                => (!empty($orderproduct->LongTermService)) ? $orderproduct->LongTermService->service_period : null,
                            'service_start_date'            => (!empty($orderproduct->LongTermService)) ? $orderproduct->LongTermService->service_start_date : null,
                            'vendor_dinein_table_id'        => ($ordervendorproducts->vendor_dinein_table_id) ? $ordervendorproducts->vendor_dinein_table_id : NULL,
                            'scheduled_date_time'           => ($orderproduct->scheduled_date_time) ? $orderproduct->scheduled_date_time : NULL,
                            'schedule_slot'                 => ($orderproduct->schedule_slot) ? $orderproduct->schedule_slot : NULL,
                            'schedule_type'                 => ($orderproduct->schedule_type) ? $orderproduct->schedule_type : NULL,
                        ];

                        $cartProduct = CartProduct::create($cart_product_detail);

                        foreach ($orderproduct->addon as $addon) :
                            $saveAddons = [
                                'option_id' => $addon->option_id,
                                'cart_id' => $cart_data->id,
                                'addon_id' => $addon->addon_id,
                                'cart_product_id' => $cartProduct->id,
                            ];
                            CartAddon::insert($saveAddons);
                        endforeach;

                    endforeach;
                endforeach;
                DB::commit();
                return $this->successResponse([], __('Items has been added to Cart.'), 200);
            else :
                return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
            endif;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error($e->getMessage());
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }

    public function discardEditOrder($orderid)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $new_session_token = session()->get('_token');

            $cart = NULL;
            if ($user) :
                $cart = Cart::where('user_id', $user->id)->where('order_id', $orderid)->first();
            else :
                $cart = Cart::where('unique_identifier', session()->get('_token'))->where('order_id', $orderid)->first();
            endif;
            if (!empty($cart)) :

                CartProduct::where('cart_id', $cart->id)->delete();
                CartProductPrescription::where('cart_id', $cart->id)->delete();
                Cart::where('id', $cart->id)->delete();
                DB::commit();
                return $this->successResponse([], __('Order editing discarded successfully.'), 200);
            else :
                return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
            endif;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error($e->getMessage());
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }

    public function sendSuccessNotification($id, $vendorId)
    {
        $super_admin = User::where('is_superadmin', 1)->pluck('id');
        $user_vendors = UserVendor::where('vendor_id', $vendorId)->pluck('user_id');
        $devices = UserDevice::whereNotNull('device_token')->where('user_id', $id)->pluck('device_token');
        foreach ($devices as $device) {
            $token[] = $device;
        }
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_vendors)->pluck('device_token');
        foreach ($devices as $device) {
            $token[] = $device;
        }
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $super_admin)->pluck('device_token');
        foreach ($devices as $device) {
            $token[] = $device;
        }
        $notification_content = NotificationTemplate::where('id', 2)->first();
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if ($notification_content && ! empty($token) && ! empty($client_preferences->fcm_server_key)) {

            $data = [
                "registration_ids" => $token,
                "notification" => [
                    'title' => $notification_content->label,
                    'body' => $notification_content->content,
                    'sound' => 'default',
                ]
            ];
            $dataString = $data;

            sendFcmCurlRequest($data);
        }
    }

     // place Request To Dispatch for Appointment , OnDemand
     public function placeRequestToDispatchSingleProductUpdate($order, $vendor, $dispatch_domain,$vendorProduct ,$is_restricted ,$request)
     {
           try {
             $order = Order::find($order);
             $customer = User::find($order->user_id);
             $cus_address = UserAddress::find($order->address_id);
             $tasks = array();
             $task_type = 'now';
             $schedule_time = '';
             $return_response = 2;
             $paymentSentAlready = 0;
             $is_order_amount_send_to_dispatcher = 0;
             $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'phone_no', 'email', 'latitude', 'longitude', 'address')->first();

                $product = $vendorProduct;
                $allocation_type = 'a';
                $agent = '';

                 if ($order->payment_option_id == 1 && ($order->is_order_amount_send_to_dispatcher ==0)) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order->payable_amount;
                    $is_order_amount_send_to_dispatcher = 1;

                } else {

                    if($order->is_postpay==1 && ($order->payment_status == 0)  && ($order->is_order_amount_send_to_dispatcher ==0))
                    {
                        $cash_to_be_collected = 'Yes';
                        $payable_amount = $order->payable_amount;
                        $is_order_amount_send_to_dispatcher = 1;
                    }else{
                        $cash_to_be_collected = 'No';
                        $payable_amount = 0.00;
                    }
                }

                 $tasks = array();
                 $meta_data = '';

                 $unique = Auth::user()->code;
                 $team_tag = $unique . "_" . $vendor;
                 if (!empty($product->scheduled_date_time) && $product->scheduled_date_time > 0) {
                     $task_type = 'schedule';
                     $user = Auth::user();
                     $selectedDate = dateTimeInUserTimeZone($product->scheduled_date_time, $user->timezone);
                     $slot = trim(explode("-", $product->schedule_slot)[0]);

                     $slotTime = date('H:i:s', strtotime("$slot"));
                     $selectedDate = date('Y-m-d', strtotime($selectedDate));
                     $scheduleDateTime = $selectedDate . ' ' . $slotTime;
                     $schedule_time =  $scheduleDateTime ?? null;
                 }

                 $task_type_id = $dispatch_domain['service_type'] == 'appointment' ?  3 : 1;
                 $service_time = $product->product->first() ? $product->product->minimum_duration_min : 0;

                 $rejectable_order = isset($dispatch_domain['rejectable_order'])? $dispatch_domain['rejectable_order'] : 0;


                 $tasks[] = array(
                     'task_type_id' => $task_type_id,
                     'latitude'     => $vendor_details->latitude ?? '',
                     'longitude'    => $vendor_details->longitude ?? '',
                     'short_name'   => '',
                     'address'      => $vendor_details->address ?? '',
                     'post_code'    => '',
                     'barcode'      => '',
                     'flat_no'     => null,
                     'email'       => $vendor_details->email ?? null,
                     'phone_number' => $vendor_details->phone_no ?? null,
                     'appointment_duration' =>  $dispatch_domain['service_type'] == 'appointment' ?  $service_time  : null,
                 );

                 if ($product->dispatch_agent_id) {
                     $allocation_type = 'm';
                     $agent = $product->dispatch_agent_id;
                 }
                 if ($dispatch_domain['service_type'] == 'on_demand') {
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
                 }

                 if ($customer->dial_code == "971") {
                     // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                     $customerno = "0" . $customer->phone_number;
                 } else {
                     // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                     $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
                 }

                 $client = CP::orderBy('id', 'asc')->first();

                     //  send all payment to fist order
                     if ($paymentSentAlready == 0) {
                         $paymentSentAlready = 1;
                     } else {
                         $cash_to_be_collected = 'No';
                         $payable_amount = 0.00;
                     }
                     $dynamic = uniqid($order->id . $vendor . $product->product_id );

                     $call_back_url = route('dispatch-order-product-status-update', $dynamic);
                     $postdata =  [
                         'order_number'  =>  $order->order_number,
                         'customer_name' => $customer->name ?? 'Dummy Customer',
                         'customer_phone_number' => $customerno ?? rand(111111, 11111),
                         'customer_dial_code' => $customer->dial_code ?? null,
                         'customer_email' => $customer->email ?? null,
                         'recipient_phone' => $customerno ?? rand(111111, 11111),
                         'recipient_email' => $customer->email ?? null,
                         'task_description' => "Order From :" . $vendor_details->name,
                         'allocation_type' => $allocation_type,
                         'task_type' => $task_type,
                         'schedule_time' => $schedule_time ?? null,
                         'cash_to_be_collected' => $payable_amount ?? 0.00,
                         'barcode' => '',
                         'order_team_tag' => $team_tag,
                         'call_back_url' => $call_back_url ?? null,
                         'task' => $tasks,
                         'is_restricted' => $is_restricted,
                         'vendor_id' => $vendor_details->id,
                         'order_vendor_id' => @$order_vendor->id,
                         'dbname' => @$client->database_name,
                         'order_id' => @$order->id,
                         'customer_id' => @$order->user_id,
                         'user_icon' => $customer->image,
                         'agent'     => $agent,
                         'task_type_id' => $task_type_id, //  for add agent booking in case of appointment
                         'service_time' =>  $service_time,
                         'rejectable_order' =>  $rejectable_order,
                         'tip_amount'=>$order->tip_amount??0

                     ];

                     if ($is_restricted == 1) {
                         $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                         $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
                     }

                     $client = new Client([
                         'headers' => [
                             'personaltoken' => $dispatch_domain['service_key'],
                             'shortcode'     => $dispatch_domain['service_key_code'],
                             'content-type'  => 'application/json'
                         ]
                     ]);

                     $url = $dispatch_domain['service_key_url'];
                     $res = $client->post(
                         $url . '/api/task/create',
                         ['form_params' => ($postdata)]
                     );
                     $response = json_decode($res->getBody(), true);
                     if ($response && $response['task_id'] > 0) {
                        if( $is_order_amount_send_to_dispatcher ==1){
                            //$order->update(['is_order_amount_send_to_dispatcher'=>$is_order_amount_send_to_dispatcher]);
                            $order->is_order_amount_send_to_dispatcher = $is_order_amount_send_to_dispatcher;
                            $order->save();
                        }
                         $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';

                         $dispatch_route                                 = new OrderProductDispatchRoute();
                         $dispatch_route->order_id                       = $request->order_id;
                         $dispatch_route->order_vendor_id                = $product->order_vendor_id;
                         $dispatch_route->order_vendor_product_id        = $product->id;
                         $dispatch_route->web_hook_code                  = $dynamic;
                         $dispatch_route->dispatch_traking_url           = $dispatch_traking_url;
                         $dispatch_route->dispatcher_status_option_id    = 1;
                         $dispatch_route->order_status_option_id         = 1;
                         $dispatch_route->save();

                         $update = VendorOrderProductDispatcherStatus::updateOrCreate([
                             'dispatcher_id' => null,
                             'order_id' =>  $request->order_id,
                             'dispatcher_status_option_id' => 1,
                             'vendor_id' =>  $request->vendor_id,
                             'order_product_route_id' => $dispatch_route->id
                         ]);

                         $return_response = 1;
                     }


             return $return_response;
         } catch (\Exception $e) {
             //return 2;
             return response()->json([
                 'status' => 'error',
                 'message' => $e->getMessage()
             ]);
         }
     }


     public function orderSuccessCartDetail($order)
    {
        try {
            // Auto accept order
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);

            $cart = Cart::where('user_id',$order->user_id)->select('id')->first();
            $cartid = $cart->id;
            Cart::where('id', $cartid)->update([
                'schedule_type' => null,
                'scheduled_date_time' => null,
                'comment_for_pickup_driver' => null,
                'comment_for_dropoff_driver' => null,
                'comment_for_vendor' => null,
                'schedule_pickup' => null,
                'schedule_dropoff' => null,
                'specific_instructions' => null
            ]);
            CaregoryKycDoc::where('cart_id', $cartid)->update([
                'ordre_id' => $order->id,
                'cart_id' => ''
            ]);
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();
            // Send Notification
            if (! empty($order->vendors)) {
                foreach ($order->vendors as $vendor_value) {
                    $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                    $user_vendors = UserVendor::where([
                        'vendor_id' => $vendor_value->vendor_id
                    ])->pluck('user_id');
                    $orderController->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail);
                }
            }
            $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id);
            $super_admin = User::where('is_superadmin', 1)->pluck('id');
            $orderController->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);
                // send sms
            $this->sendOrderSuccessSMS($order);
        }catch(\Exception $e){
            \Log::info('sendSuccessSMS error :-'.$e->getMessage());
            return true;
        }
        return true;
    }

    public function sendOrderSuccessSMS($order)
    {
        try {
            $prefer = ClientPreference::select('sms_provider', 'sms_key', 'sms_secret', 'sms_from','digit_after_decimal')->first();
            $customerCurrency = ClientCurrency::with('currency')->where('is_primary', '1')->first();
            $currSymbol =$customerCurrency->currency->symbol;
            $user = User::where('id', $order->user_id)->first();
            if ($user) {
                if ($user->dial_code == "971") {
                    $to = '+' . $user->dial_code . "0" . $user->phone_number;
                } else {
                    $to = '+' . $user->dial_code . $user->phone_number;
                }

                $provider = $prefer->sms_provider;
                $order->payable_amount = number_format((float)$order->payable_amount, $prefer->digit_after_decimal, '.', '');

                $smsTemplates =  SmsTemplate::where('slug', 'order-place-Successfully')->first()->content;
                if(!empty($smsTemplates)){
                    $smsTemplates = str_replace("{user_name}", $user->name, $smsTemplates);
                    $smsTemplates = str_replace("{amount}", $currSymbol . $order->payable_amount, $smsTemplates);
                    $body = str_replace("{order_number}", $order->order_number, $smsTemplates);
                }else{
                    $body = __("Hi ") . $user->name . __(", Your order of amount ") . $currSymbol . $order->payable_amount . __(" for order number ") . $order->order_number . __(" has been placed successfully.");
                }
                if (!empty($prefer->sms_provider)) {
                    $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
                }
            }
        }catch(\Exception $e)
        {
            return true;
        }
        return true;

    }

    public function failedOrderWalletRefund($order)
    {
        try{
                if (isset($order->wallet_amount_used))
                {
                    $user = auth()->user();
                    $wallet = $user->wallet;
                        $wallet->depositFloat($order->wallet_amount_used, [
                            'Wallet has been <b>refunded</b> for cancellation of order #' . $order->order_number
                        ]);
                }

            }catch(\Exception $e)
            {
                \Log::info('failedOrderWalletRefund error :-'.$e->getMessage());
                return true;
            }
            return true;

    }

    public function addBufferTime($request){
        $postdata= [
            'order_id'=>$request->order_id,
            'vendor_id'=>$request->vendor_id,
        ];
        $order=  OrderVendor::where($postdata)->first();
        $order->extra_time = $request->time;
        $order->save();
        $postdata['time'] = $request->time;
        $postdata['tracking_id'] = $request->tracking_id;
        $dispatch_domain = $this->getDispatchDomain();
        $client = new Client([
            'headers' => [
                'personaltoken' => $dispatch_domain->delivery_service_key,
                'shortcode' => $dispatch_domain->delivery_service_key_code,
                'content-type' => 'application/json'
            ]
        ]);
        $url = $dispatch_domain->delivery_service_key_url;
               $res = $client->post(
            $url . '/api/task/update_order_prepration_time',
            ['form_params' => ($postdata)]
        );
        $response = json_decode($res->getBody(), true);
        $response['order_id'] =$order->order_id;
        return $response;
     }

     public function sendDelayPushNotification($user_id, $order,$request){
        $devices = UserDevice::whereNotNull('device_token')->where('user_id', $user_id)->pluck('device_token')->toArray();
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();

        $data = [
            "registration_ids" => $devices,
            "notification" => [
                'title' => "Order Delayed",
                'body'  => "Your order has been delayed by ".$request->time." minutes",
                'sound' => "default",
                "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                'click_action' => route('user.orders'),
                "android_channel_id" => "default-channel-id"
            ],
            "data" => [
                'title' => "Order Delayed",
                'body'  => "Your order has been delayed by ".$request->time." minutes",
                "type" => "order_delayed"
            ],
            "priority" => "high"
        ];
        sendFcmCurlRequest($data);
     }



     public function saveOrderLongTermServiceSchedule($order,$productId)
     {
         $user = auth()->user();
         $client_timezone = DB::table('clients')->first('timezone');
         if($user){
             $timezone = $user->timezone ??  $client_timezone->timezone;
         }else{
             $timezone = $client_timezone->timezone ?? ( $user ? $user->timezone : 'Asia/Kolkata' );
         }

         $user_timezone          =   $timezone;
         $recurring_booking_time =   convertDateTimeInTimeZone($order->recurring_booking_time, $user_timezone, 'H:i');

             $RecurringServiceSchedule = array();

                 // No Nee other action
                 if(@$order->recurring_booking_type){
                 $Recurring_quantity     = $order->quantity;
                 $recurring_day_data     = $order->recurring_day_data;
                 $recurring_day_data     = explode(",",$recurring_day_data);

                 $ndate                  = convertDateTimeInClientTimeZone(Carbon::now());
                 $recurring_booking_time = convertDateTimeInTimeZone($order->recurring_booking_time, $user_timezone, 'H:i');
                 for ($x = 0; $x < count($recurring_day_data); $x++) {
                     $date           = $recurring_day_data[$x];
                     $newDate        = $date.' '. $recurring_booking_time;
                     $RecurringServiceSchedule [] = [
                         'order_vendor_product_id' => $productId,
                         'schedule_date'           => $newDate,
                         'type'                    => 4, // Pickup and drop
                         'order_number'            => $order->order_number
                     ];
                 }
             }

             if (!empty($RecurringServiceSchedule)) {
                     OrderLongTermServiceSchedule::insert($RecurringServiceSchedule);
                 }
     }

     public function sendPickupeliverySuccessEmail($request, $order, $vendor_id = '')
     {
         $user = Auth::user();

         $client = CP::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
         $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from', 'admin_email')->where('id', '>', 0)->first();
         $message = __('An otp has been sent to your email. Please check.');
         $otp = mt_rand(100000, 999999);

         if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
             $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
             if ($vendor_id == "") {
                 $sendto =  $user->email;
             } else {
                 $vendor = Vendor::where('id', $vendor_id)->first();
                 if ($vendor) {
                     $sendto =  $vendor->email;
                 }
             }

             $customerCurrency = ClientCurrency::join('currencies as cu', 'cu.id', 'client_currencies.currency_id')->where('client_currencies.currency_id', $user->currency)->first();
             $currSymbol = $customerCurrency->symbol;
             $client_name = 'Sales';
             $mail_from = $data->mail_from;

             try {
                 $email_template_content = '';
                 $email_template = EmailTemplate::where('id', 10)->first();
                 $address = UserAddress::where('id', $request->address_id)->first();
                 if ($email_template) {

                     $email_template_content = $email_template->content;
                     $orderLocations = OrderLocations::where('order_id',$order->id)->first();
                     $locations = json_decode($orderLocations->tasks);
                     $returnHTML = view('email.newPickupRideAddress')->with(['user'=>$user,'order' => $order,'locations' => $locations])->render();
                     $email_template_content = str_ireplace("{description}",'', $email_template_content);
                     $email_template_content = str_ireplace("{customer_name}", ucwords($user->name), $email_template_content);
                     $email_template_content = str_ireplace("{order_id}", $order->order_number, $email_template_content);
                     $email_template_content = str_ireplace("{products}", $returnHTML, $email_template_content);
                     if(!empty($address)){
                         $email_template_content = str_ireplace("{address}", $address->address . ', ' . $address->state . ', ' . $address->country . ', ' . $address->pincode, $email_template_content);
                     }
                 }
                 $email_data = [
                     'code' => $otp,
                     'link' => "link",
                     'email' => $sendto,//"harbans.sayonakh@gmail.com",//  $sendto,//
                     'mail_from' => $mail_from,
                     'client_name' => $client_name,
                     'logo' => $client->logo['original'],
                     'subject' => $email_template->subject,
                     'customer_name' => ucwords($user->name),
                     'email_template_content' => $email_template_content,
                     'cartData' => [],
                     'user_address' => $address,
                 ];
                 // $res = $this->testOrderMail($email_data);
                 // dd($res);
                 dispatch(new \App\Jobs\SendOrderSuccessEmailJob($email_data))->onQueue('verify_email');
                 $notified = 1;
             } catch (\Exception $e) {
             }
         }
     }





}
