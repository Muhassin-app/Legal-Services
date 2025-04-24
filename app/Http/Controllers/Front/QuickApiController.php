<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\VendorOrderStatus;
use App\Models\ShippingOption;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Vendor;
use App\Models\VendorOrderDispatcherStatus;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log,DB;
use App\Http\Traits\{ApiResponser,KwikApi};


class QuickApiController extends Controller
{
    use KwikApi,ApiResponser;

    public function getDeliveryFeeKwikApi($vendor_id)
    {
         try{    
            $customer = User::find(Auth::id());
            $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
            if ($cus_address){
                $vendor_details = Vendor::find($vendor_id);
                $data = (object)[      
                        "address"=> $cus_address->address,
                        "name"=> $customer->name,
                        "latitude"=> $cus_address->latitude,
                        "longitude"=> $cus_address->longitude,
                        "phone"=> $customer->phone_number,
                        "p_address"=> $vendor_details->address,
                        "p_name"=> $vendor_details->name,
                        "p_latitude"=> $vendor_details->latitude,
                        "p_longitude"=> $vendor_details->longitude,
                        "p_phone"=> $vendor_details->phone_no,
                        "p_email"=> $vendor_details->email
                  ];
                $quotation = $this->getPriceEstimation($data);
                $actualAmount=0;
                if($quotation->status=='200')
                { 
                    return $quotation->data->per_task_cost;
                }else{
                    //\Log::info(json_encode($quotation));
                }
                return $actualAmount;
            }
        
        }catch(\Exception $e)
        {
            \Log::info($e->getMessage());
            return 0;
        }
    }



    public function placeOrderToKwikApi($vendor_id,$order_id)
    {
        $scheduledAt = null;
        $order = Order::find($order_id);
        $customer = User::find(auth()->id());
        // if(isset($order->scheduled_date_time) && $order->scheduled_date_time){
        //     $schTime = convertDateTimeInClientTimeZone($order->scheduled_date_time);
        //     $date = date('Y-m-d',strtotime($schTime));
        //     $time = date('H:i:s',strtotime($schTime));
        //     $scheduledAt = $date.'T'.$time.'Z';
        // }
        // //\Log::info('vendor_id --'.$vendor_id.'--');
        // //\Log::info(json_encode($order->ordervendor->where('vendor_id',$vendor_id)->first()));
        $amountPay = $order->ordervendor->where('vendor_id',$vendor_id)->value('payable_amount')??0;
    
        $cus_address = UserAddress::find($order->address_id);
                if ($cus_address){
                    $vendor_details = Vendor::find($vendor_id);
                    $data = (object)[      
                        "address"=> $cus_address->address,
                        "name"=> $customer->name,
                        "latitude"=> $cus_address->latitude,
                        "longitude"=> $cus_address->longitude,
                        "phone"=> $customer->phone_number,
                        "p_address"=> $vendor_details->address,
                        "p_name"=> $vendor_details->name,
                        "p_latitude"=> $vendor_details->latitude,
                        "p_longitude"=> $vendor_details->longitude,
                        "p_phone"=> $vendor_details->phone_no,
                        "p_email"=> $vendor_details->email,
                        "amount" => $amountPay,
                  ];
                   
                $quotation = $this->getPriceEstimation($data);
                if($quotation->status=='200')
                {
                //  //\Log::info('vendor_id --'.$quotation->data->per_task_cost.'--');

                    $data->delivery_charge = $quotation->data->per_task_cost;
                    $response = $this->createKwikOrder($data);
                        if($response->status=='200'){
                            $response = $response->data;
                        }
                }else{
                    $response = false;
                }
            }

        return $response;
    	
    }

    public function cancelOrderRequestKwikApi($order_id,$vendor_id)
    {
        $order = Order::find($order_id);
        $reffId = $order->ordervendor->where('id',$vendor_id)->first()->delivery_response;
        return $this->cancelOrder($reffId);
    }

    public function webhooks(Request $request)
    {
        try{
           $trackingId = '';
           $json = json_decode($request->getContent());
           if(isset($json->pickup_job_status) && $json->pickup_job_status == '0')
           {
            $trackingId = $json->unique_order_id;

            // ASSIGNING_DRIVER means Order is placed and assigning drivers
            $details = OrderVendor::where('web_hook_code',$trackingId)->first();
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'1']);
            }elseif(isset($json->pickup_job_status) && $json->pickup_job_status == '1')
            {
            $trackingId = $json->unique_order_id;
            // ON_GOING means driver assigned and start drive
            $details = OrderVendor::where('web_hook_code',$trackingId)->first();
            VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'4']);

            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);
            }elseif(isset($json->pickup_job_status) && $json->pickup_job_status == '7')
            {
            $trackingId = $json->unique_order_id;
            // PICKED_UP means driver picked order and out for delivery
            $details = OrderVendor::where('web_hook_code',$trackingId)->first();

            VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'5']);

            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
            }elseif(isset($json->pickup_job_status) && $json->pickup_job_status == '2')
            {
            $trackingId = $json->unique_order_id;
            // COMPLETED means driver complete the delivery
            OrderVendor::where('web_hook_code',$trackingId)
            ->update(['order_status_option_id'=>'6']);
            $details = OrderVendor::where('web_hook_code',$trackingId)->first();


            VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'6']);

            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'5','type'=>'2']);
            }


        if($request && isset($json->data)){
         Webhook::create(['tracking_order_id'=>(($trackingId)?$trackingId:''),'response'=>$request->getContent()]);
        }
        
        }catch(\Exception $e){
            \Log::info($e->getMessage());
            return response([],200);
        }

        return response([],200);

    }

    
}
