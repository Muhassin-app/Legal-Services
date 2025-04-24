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
use PhpParser\Node\Stmt\TryCatch;
use Log,DB;

class LalaMovesController extends Controller
{
    use \App\Http\Traits\LalaMoves;
	use \App\Http\Traits\ApiResponser;

    private $base_price;
    private $lalamove_status;
    private $distance;
    private $amount_per_km;

    public function __construct()
    {
  
      $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'lalamove')->where('status', 1)->first();
      if($simp_creds && $simp_creds->credentials){
            $creds_arr = json_decode($simp_creds->credentials);
            $this->base_price = $creds_arr->base_price??'0';
            if($this->base_price>0)
            {
                $this->base_price = $creds_arr->base_price??'0';
                $this->distance = $creds_arr->distance??'0';
                $this->amount_per_km = $creds_arr->amount_per_km??'0';

            }
            $this->lalamove_status = $simp_creds->status??'';
        }
    }


    public function quotation(Request $request)
    {
    	$data = (object) array(
            "pick_lat"=> "3.115825684565",
            "pick_lng"=> "101.666775521484",
            "pick_address"=> "Malaysia",
            "vendor_name"=> "General Electric",
            "vendor_contact"=> "8965745236",
            "drop_lat"=> "3.229537972256",
            "drop_lng"=> "101.730552380616",
            "drop_address"=> "6PHJ+R6 Kuala Lumpur, Federal Territory of Kuala Lumpur, Malaysia",
            "user_name"=> "Xavier Ross",
            "user_phone"=> "+41767250736",
            "remarks"=> "Delivery vendor message remarks"
        );
        $quotation = $this->getQuotations($data);
        return $quotation;
    }


    public function getDeliveryFeeLalamove($vendor_id)
    {
        try{    

                $customer = User::find(Auth::id());
                $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
                if ($cus_address && $this->lalamove_status==1){

                    $vendor_details = Vendor::find($vendor_id);
                    $data = (object) array(
                        'pick_lat' => $vendor_details->latitude,
                        'pick_lng' => $vendor_details->longitude,
                        'pick_address' => $vendor_details->address,
                        'vendor_name' => $vendor_details->name,
                        // 'vendor_contact' => $vendor_details->phone_no,
                        'vendor_contact' => '3768865552',
                        'drop_lat' => $cus_address->latitude,
                        'drop_lng' => $cus_address->longitude,
                        'drop_address' => $cus_address->address,
                        'user_name' => $customer->name,
                        'user_phone' => $customer->phone_number,
                        'remarks' => 'Delivery vendor message remarks'
                    );
            
                    $quotation = $this->getQuotations($data);
                    $actualAmount=0;
                    if($quotation['code']!='409')
                    { 
                        $json = json_decode($quotation['response']);
                        $distance =  round($json->distance->value/1000);
                        if($this->base_price > 0)
                        {
                            $actualAmount = getBaseprice($distance);
                         }else{
                            $actualAmount = $json->totalFee;
                        }
                    }
                    //dd($actualAmount);
                    return $actualAmount;
                }
            
        }catch(\Exception $e)
        {
            return 0;
        }

    }
    public function placeOrderToLalamove($vendor_id,$user_id,$order_id)
    {
        $scheduledAt = null;
        $order = Order::find($order_id);
        $customer = User::find($user_id);
        if(isset($order->scheduled_date_time) && $order->scheduled_date_time){
            $schTime = convertDateTimeInClientTimeZone($order->scheduled_date_time);
            $date = date('Y-m-d',strtotime($schTime));
            $time = date('H:i:s',strtotime($schTime));
            $scheduledAt = $date.'T'.$time.'Z';
        }
        $cus_address = UserAddress::find($order->address_id);
                if ($cus_address && $this->lalamove_status==1){

                    $vendor_details = Vendor::find($vendor_id);
                    $data = (object) array(
                        'pick_lat' => $vendor_details->latitude,
                        'pick_lng' => $vendor_details->longitude,
                        'pick_address' => $vendor_details->address,
                        'vendor_name' => $vendor_details->name,
                        'vendor_contact' => $vendor_details->phone_no,
                        'drop_lat' => $cus_address->latitude,
                        'drop_lng' => $cus_address->longitude,
                        'drop_address' => $cus_address->address,
                        'user_name' => $customer->name,
                        'user_phone' => $customer->phone_number,
                        'remarks' => orderProductDetails($order_id),
                        'schedule_time' => $scheduledAt,
                    );
                   
                $quotation = $this->getQuotations($data);
                $response = json_decode($quotation['response']);
                if($quotation['code']=='200'){
                        $response = $this->placeOrders($data,$response);
                    //\Log::info(json_encode($response));
                        if($response['code']=='200'){
                            $response = json_decode($response['response']);
                        }else{
                            $response = 2;
                        }
                }else{
                    $response = 2;
                }
            }

        return $response;
    	
    }

    public function cancelOrderRequestlalamove($reffId)
    {
       $this->orderCancel($reffId);
    }

    //for Developer use only
    public function placeOrderToLalamoveDev($vendor_id,$user_id,$order_id)
    {
        $order = Order::find($order_id);
        $customer = User::find($user_id);
        $scheduledAt = null;
        if(isset($order->scheduled_date_time) && $order->scheduled_date_time){
            $schTime = convertDateTimeInClientTimeZone($order->scheduled_date_time);
            $date = date('Y-m-d',strtotime($schTime));
            $time = date('H:i:s',strtotime($schTime));
            $scheduledAt = $date.'T'.$time.'Z';
        }
        $noRef = '';
        $checkOrderRef = Webhook::where('tracking_order_id',$order_id)->first();
        if(isset($checkOrderRef) && $checkOrderRef->id){
            $noRef = json_decode($checkOrderRef->response)->orderRef;
            //dd(json_decode($checkOrderRef->response));
           return $response = json_decode($checkOrderRef->response);
           die;
        }
        if(empty($order->web_hook_code) && empty($noRef))
        {
        $cus_address = UserAddress::find($order->address_id);
                if ($cus_address && $this->lalamove_status==1){

                    $vendor_details = Vendor::find($vendor_id);
                    $data = (object) array(
                        'pick_lat' => $vendor_details->latitude,
                        'pick_lng' => $vendor_details->longitude,
                        'pick_address' => $vendor_details->address,
                        'vendor_name' => $vendor_details->name,
                        'vendor_contact' => $vendor_details->phone_no,
                        'drop_lat' => $cus_address->latitude,
                        'drop_lng' => $cus_address->longitude,
                        'drop_address' => $cus_address->address,
                        'user_name' => $customer->name,
                        'user_phone' => $customer->phone_number,
                        'remarks' => orderProductDetails($order_id),
                        'schedule_time' => $scheduledAt
                    );
        
                $quotation = $this->getQuotations($data);
                $response = json_decode($quotation['response']);
                if($quotation['code']=='200'){
                        $response = $this->placeOrders($data,$response,$order_id);
                        if(isset($response->orderRef)){
                            $response = $response;
                        }else{
                            $response = false;
                        }
                }else{
                    $response = false;
                }
            }
        }

        return $response;
    	
    }


    public function getDeriverDetails($order)
    {
        $order = $order;
        $response = $this->orderDriverDetail($order->web_hook_code,$order->driver_id);
        // dd($response);
        if($response['code'] == '200'){
            return $response['response'];
        }
        return false;
        // "{"name":"TestDriver 44111","phone":"+6011144111","plateNumber":"VP2381474","photo":""}
    }

    public function webhooks(Request $request)
    {
        try{
           $trackingId = '';
           $json = json_decode($request->getContent());
    
           $driverId = $json->data->order->driverId??'';
           if(isset($json->eventType) && $json->eventType == 'ORDER_STATUS_CHANGED' && $json->data->order->status == 'ASSIGNING_DRIVER')
           {
            $trackingId = $json->data->order->id;

            // ASSIGNING_DRIVER means Order is placed and assigning drivers
            OrderVendor::where('web_hook_code',$trackingId)
            ->update(['lalamove_tracking_url'=>$json->data->order->shareLink,'driver_id'=>$driverId]);
            $details = OrderVendor::where('web_hook_code',$trackingId)  ->first();

            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'1']);
            }elseif(isset($json->eventType) && $json->eventType == 'ORDER_STATUS_CHANGED' && $json->data->order->status == 'ON_GOING')
            {
            $trackingId = $json->data->order->id;
            // ON_GOING means driver assigned and start drive
            OrderVendor::where('web_hook_code',$trackingId)
            ->update(['lalamove_tracking_url'=>$json->data->order->shareLink,'driver_id'=>$driverId]);
            $details = OrderVendor::where('web_hook_code',$trackingId)->first();

            VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'4']);

            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);
            }elseif(isset($json->eventType) && $json->eventType == 'ORDER_STATUS_CHANGED' && $json->data->order->status == 'PICKED_UP')
            {
            $trackingId = $json->data->order->id;
            // PICKED_UP means driver picked order and out for delivery
            OrderVendor::where('web_hook_code',$trackingId)
            ->update(['lalamove_tracking_url'=>$json->data->order->shareLink,'driver_id'=>$driverId]);
            $details = OrderVendor::where('web_hook_code',$trackingId)->first();

            VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'5']);

            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
            }elseif(isset($json->eventType) && $json->eventType == 'ORDER_STATUS_CHANGED' && $json->data->order->status == 'COMPLETED')
            {
            $trackingId = $json->data->order->id;
            // COMPLETED means driver complete the delivery
            OrderVendor::where('web_hook_code',$trackingId)
            ->update(['lalamove_tracking_url'=>$json->data->order->shareLink,'driver_id'=>$driverId,'order_status_option_id'=>'6']);
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
