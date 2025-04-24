<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\OrderVendorProduct;
use App\Models\ShippingOption;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Vendor;
use App\Models\VendorOrderDispatcherStatus;
use App\Models\VendorOrderStatus;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Carbon\Carbon;
use App\Models\ClientPreference;
use App\Models\UserDevice;
use App\Http\Controllers\Front\FrontController;
use App\Http\Traits\OrderTrait;
class DunzoController extends FrontController
{
	
    use \App\Http\Traits\Dunzo,OrderTrait;

    private $api_key;
    private $app_url;
    private $base_price;
    private $distance;
    private $amount_per_km;
    public $status;

    public function __construct()
    {
        $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'dunzo')->where('status', 1)->first();
        if($simp_creds){
            $this->status = $simp_creds->status??'0';
            $creds_arr = json_decode($simp_creds->credentials);
            $this->api_key = $creds_arr->api_key??'';
            $this->app_url = (($simp_creds->test_mode=='1')?'https://dev.adloggs.com/aa':'https://app.adloggs.com/aa'); //Live url - https://app.adloggs.com/aa
            $this->base_price = $creds_arr->base_price ?? ''; 
            $this->distance = $creds_arr->distance ?? ''; 
            $this->amount_per_km = $creds_arr->amount_per_km ?? '';
        }else{
            return 0;
        }
    }

	public function configuration()
    {
        $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'dunzo')->where('status', 1)->first();
        if($simp_creds){
            $this->status = $simp_creds->status??'0';
            $creds_arr = json_decode($simp_creds->credentials);
            $this->api_key = $creds_arr->api_key??'';
            $this->app_url = (($simp_creds->test_mode=='1')?'https://dev.adloggs.com/aa':'https://app.adloggs.com/aa'); //Live url - https://app.adloggs.com/aa
            $this->base_price = $creds_arr->base_price ?? ''; 
            $this->distance = $creds_arr->distance ?? ''; 
            $this->amount_per_km = $creds_arr->amount_per_km ?? '';
        }else{
            return 0;
        }
      }


	public function getDistance($vendorId)
    {
		$this->configuration();
		if($this->status == 1){
			$customer = User::find(Auth::id());
			$cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
			$vendor_details = Vendor::find($vendorId);

			$latitude[] =  $vendor_details->latitude ?? 30.71728880;
			$latitude[] =  $cus_address->latitude ?? 30.717288800000;

			$longitude[] =  $vendor_details->longitude ?? 76.803508700000;
			$longitude[] =  $cus_address->longitude ?? 76.803508700000;

			$distance =  GoogleDistanceMatrix($latitude,$longitude);
			return $distance['distance'];
		}
		return false;
    }


		 # get delivery fee getDunzoBaseFee
		 public function getDunzoBaseFee($vendorId,$distance = null)
		 {	
			$fees = 0;
			$this->configuration();
			if($this->status == 1 && $this->base_price>0){
                    if(!$distance){
				      $distance = $this->getDistance($vendorId);
                    }
				if($distance){
					//Helper Function
					$fees =   getBaseprice($distance,'dunzo');
				}
			}
			return $fees;
		}


        # get delivery fee Dunzo Courier Service
		public function getQuotations($vendor_id,$address)
		{
			$this->configuration();
			$vendor_details = Vendor::find($vendor_id);
            $data =array(
                'pickup_lat'=>$vendor_details->latitude ?? '',
                'pickup_long'=>$vendor_details->longitude ?? '',
                'delivery_lat' => $address->latitude, //Required
				'delivery_long' => $address->longitude, //Required
            );
			$status =  $this->getfees($data);
			if($this->status){
				if(($this->base_price>0) && $status->status == true){
				return $this->getDunzoBaseFee($vendor_id,$status->data->distance);
			}else{
                    if($status->status){
                        return $status->data->estimated_price??0;
                    }
                    return 0; 
				}
			}
            return 0; 
		}


		# get delivery fee Courier Service
		public function getCourierService($vendorId)
		{
			$this->configuration();
            $vendor_details = Vendor::find($vendorId);
            $data =array(
                'pickup_lat'=>$vendor_details->latitude ?? '',
                'pickup_long'=>$vendor_details->longitude ?? ''
            );
			$status =  $this->checkAvilabilty($data);
			if($this->status == 1){
				if(($this->base_price>0) && $status->status == true){
				return $this->getDunzoBaseFee($vendorId);
			}else{
                    if($status->status){
                        return $this->getDunzoBaseFee($vendorId);
                    }
                    return 0; 
				}
			}
		}


	public function createOrderRequestDunzo($user_id,$orderVendor)
    { 

		$this->configuration();
		if($this->status)
		{

			$order = Order::find($orderVendor->order_id);
        	$customer = User::find($user_id);
			$vendor_details = Vendor::find($orderVendor->vendor_id);
			$cus_address = UserAddress::find($order->address_id);
			$orderProducts = OrderVendorProduct::where(['order_id'=>$orderVendor->order_id,'order_vendor_id'=>$orderVendor->id])->get();
            $scheduledAt = '';
            $preTime = ($vendor_details->order_pre_time >0) ? $vendor_details->order_pre_time : '10';
            if(isset($order->scheduled_date_time) && $order->scheduled_date_time){
                $date = date('Y-m-d H:i:s', strtotime('+'. $preTime.' minutes', strtotime($order->scheduled_date_time)));
                $date = Carbon::parse($date, 'UTC');

            }else{
                $date = date('Y-m-d H:i:s', strtotime('+'. $preTime.' minutes', strtotime($order->created_at)));
                $date = Carbon::parse($order->created_at, 'UTC');
            }
            $date->setTimezone($customer->timezone);
            $dateT = $date->isoFormat('YYYY-MM-DD HH:mm:ss');
			$data = array (
				'partner_order_id' => $orderVendor->id.'-'.$orderVendor->order_id.'-'.$orderVendor->vendor_id,
				'pickup_contact_name' => $vendor_details->name ?? '',  
				'pickup_contact_no' => $vendor_details->phone_no, 
				'pickup_contact_email' => $vendor_details->email ?? '', 
				'pickup_address' => $vendor_details->address ?? '',  
			    'pickup_date_time' => $dateT,
				'pickup_lat' => $vendor_details->latitude ?? '', //Required 
				'pickup_long' => $vendor_details->longitude ?? '', //Required 
				
                'delivery_contact_name' => $customer->name,
				'delivery_contact_no' => $customer->phone_number,
				'delivery_contact_email' => $customer->email,
                'delivery_address'=> $cus_address->address,
				'delivery_lat' => $cus_address->latitude, //Required
				'delivery_long' => $cus_address->longitude, //Required
				'order_description' => '',

                //(( INTEGER )) - Timezone difference with UTC in minutes for e.g. India IND 330 , Cuba CDT -240
				'utc_offset' => '330'
			  );
		}
    	$orderSuc = $this->createOrder($data);
		return $orderSuc;

		//Response Result
        // "status": true,
        // "code": 200,
        // "message": "Success",
        // "data": {
        //     "order_uuid": "f45d10c5-5edf-4ef4-aaaf-da4d96f1aebb",
        //     "trackUrl": "https://erranderz.in/dev/trackorder?key=f45d10c5-5edf-4ef4-aaaf-da4d96f1aebb",
        //     "partner_order_id": "100",
        //     "meta_data": "{}"
        // }

    }



    public function cancelOrderRequestDunzo($order_id)
    {
		$this->configuration();
		if($this->status){
            $data =array('order_uuid'=>$order_id,'update_type'=>'Cancel');
			return $cancel_order= $this->cancelOrder($data);
		}
    }


	public function dunzoWebhook(Request $request)
    {


        // "order_status_id": 4,
        // "order_uuid":"4ed83e5d-ec49-44ef-a7ea-eba3cfd91416",
        // "partner_order_id": "78954uigg",
        // "deliveryStaffDetails": {
        //     "name": "karthick",
        //     "phone": "99999999999",
        //     "currentLocation": {
        //         "lat": 10.452855555,
        //         "long": 11.55854455
        //     }
        // }
        $trackingId = '';
        $json = json_decode($request->getContent());

        Webhook::create(['tracking_order_id'=>'11111','response'=>$request->getContent()]);


        if($request && isset($json->order_uuid)){
            //is for Dunzo webhook 
            Webhook::create(['tracking_order_id'=>(($json->order_uuid)?$json->order_uuid:''),'response'=>$request->getContent()]);
            $awb = $json->order_uuid;
            $dispatcher_status_option_id = '';
            $order_status_option_id = '';
            $details = OrderVendor::where('web_hook_code',$awb)->first();
            if(isset($json->order_status_id) && $json->order_status_id == '3'){
                $dispatcher_status_option_id = '1';
            }elseif(isset($json->order_status_id) && $json->order_status_id == '4'){
                $dispatcher_status_option_id = '3';
                $order_status_option_id = '4';
            }elseif(isset($json->order_status_id) && $json->order_status_id == '8'){
                $dispatcher_status_option_id = '4';
                $order_status_option_id = '5';              
            }elseif(isset($json->order_status_id) && $json->order_status_id == '5'){
                $dispatcher_status_option_id = '5';
                $order_status_option_id = '6';
            }elseif(isset($json->order_status_id) && $json->order_status_id == '6'){
                $dispatcher_status_option_id = '6';
                $order_status_option_id = '3';
            }
            if(!empty($dispatcher_status_option_id) && !empty($details)){
                $data = array('order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>$dispatcher_status_option_id);
                if($dispatcher_status_option_id == 5){
                    $data = array_merge($data,array('type' => '2'));
                }
                $update = VendorOrderDispatcherStatus::Create($data);
                if($dispatcher_status_option_id == '1'){
                    $update = VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);                
                }
                $this->sendOrderNotification($update->id);
                if(isset($order_status_option_id) && !empty($order_status_option_id)){            
                    $checkif= VendorOrderStatus::where(['order_id' => $details->order_id,
                        'order_status_option_id' =>  $order_status_option_id,
                        'vendor_id' =>  $details->vendor_id,
                        'order_vendor_id' =>  $details->id])->count();
                    
                    if($checkif == 0){
                        $update_vendor = VendorOrderStatus::updateOrCreate([
                            'order_id' =>  $details->order_id,
                            'order_status_option_id' =>  $order_status_option_id,
                            'vendor_id' =>  $details->vendor_id,
                            'order_vendor_id' =>  $details->id ]);
                        
                        OrderVendor::where('vendor_id', $details->vendor_id)->where('order_id', $details->order_id)->update(['order_status_option_id' => $order_status_option_id]);
                        // if driver is reject order
                        if($order_status_option_id == 3 ){
                            $this->cancelOrderByDriver($details);
                        }
                    }
                }
                OrderVendor::where('vendor_id', $details->vendor_id)->where('order_id', $details->order_id)->update(['dispatcher_status_option_id' => $dispatcher_status_option_id]);          
            }
        }elseif(@$json->awb){
        //shiprocket webhook
        Webhook::create(['tracking_order_id'=>(($json->awb)?$json->awb:''),'response'=>$request->getContent()]);
        if(isset($json->shipment_status_id) && $json->shipment_status_id == '1')
        {
            $awb = $json->awb;
            $details = OrderVendor::where('ship_awb_id',$awb)->first();
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'1']);
        }elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '3')
        {
			$awb = $json->awb;
            $details = OrderVendor::where('ship_awb_id',$awb)->first();
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);
        }elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '19')
        {
			$awb = $json->awb;
            $details = OrderVendor::where('ship_awb_id',$awb)->first();
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);
        }elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '42')
        {
			$awb = $json->awb;
            $details = OrderVendor::where('ship_awb_id',$awb)->first();
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
        }elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '7')
        {
            $awb = $json->awb;
            $details = OrderVendor::where('ship_awb_id',$awb)->first();
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'5','type'=>'2']);
        }
    }
        return response([],200);

    }
    
    public function dunzoWebhookOld(Request $request)
    {
        $trackingId = '';
        $json = json_decode($request->getContent());
        if($request && isset($json->order_uuid)){
            //is for Dunzo webhook
            Webhook::create(['tracking_order_id'=>(($json->order_uuid)?$json->order_uuid:''),'response'=>$request->getContent()]);
            if(isset($json->order_status_id) && $json->order_status_id == '3')
            {
                $awb = $json->order_uuid;
                $details = OrderVendor::where('web_hook_code',$awb)->first();
                
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'1']);
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);
            }elseif(isset($json->order_status_id) && $json->order_status_id == '4')
            {
                $awb = $json->order_uuid;
                $details = OrderVendor::where('web_hook_code',$awb)->first();
                
                //Update in vendor status
                VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'4']);
                
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);
            }elseif(isset($json->order_status_id) && $json->order_status_id == '8')
            {
                $awb = $json->order_uuid;
                $details = OrderVendor::where('web_hook_code',$awb)->first();
                
                //Update in vendor status
                VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'5']);
                
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
            }elseif(isset($json->order_status_id) && $json->order_status_id == '5')
            {
                $awb = $json->order_uuid;
                $details = OrderVendor::where('web_hook_code',$awb)->first();
                
                //Update in vendor status
                VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'6']);
                
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'5','type'=>'2']);
            }
        }elseif(@$json->awb){
            
            //shiprocket webhook
            
            Webhook::create(['tracking_order_id'=>(($json->awb)?$json->awb:''),'response'=>$request->getContent()]);
            
            
            if(isset($json->shipment_status_id) && $json->shipment_status_id == '1')
            {
                $awb = $json->awb;
                $details = OrderVendor::where('ship_awb_id',$awb)->first();
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'1']);
            }elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '3')
            {
                $awb = $json->awb;
                $details = OrderVendor::where('ship_awb_id',$awb)->first();
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);
            }elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '19')
            {
                $awb = $json->awb;
                $details = OrderVendor::where('ship_awb_id',$awb)->first();
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);
            }elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '42')
            {
                $awb = $json->awb;
                $details = OrderVendor::where('ship_awb_id',$awb)->first();
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
            }elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '7')
            {
                $awb = $json->awb;
                $details = OrderVendor::where('ship_awb_id',$awb)->first();
                VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'5','type'=>'2']);
            }
            
        }
        
        return response([],200);
        
    }
    
    /******************    ---- send notification to user -----   ******************/
    public function sendOrderNotification($vendor_order_status_id)
    {  
        $OrderStatus = VendorOrderDispatcherStatus::select('*','dispatcher_status_option_id as status_data')->find($vendor_order_status_id);
        
        if($OrderStatus){
            $orderNumber = Order::where('id',$OrderStatus->order_id)->select('order_number','user_id')->first();
            
            $user_id = $orderNumber ? $orderNumber->user_id : '';
            // $checkuservendor = UserVendor::where('user_id',$user_id)->first();
            // $sound = ($checkuservendor)?"notification.wav":"default";
            $devices = UserDevice::whereNotNull('device_token')->where('user_id', $user_id)->pluck('device_token');
            
            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                $title = __('Order Status : #').($orderNumber ?  $orderNumber->order_number : '');
                $body =  $OrderStatus ? ($OrderStatus->status_data ? $OrderStatus->status_data['driver_status'] : '') : '';
                
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $title,
                        'body'  => $body,
                        'sound' => "default",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => route('order.index'),
                        "android_channel_id" => "default-channel-id"
                    ],
                    "data" => [
                        'title' => $title,
                        'body'  => $body,
                        'data' => '',
                        'type' => ""
                    ],
                    "priority" => "high"
                ];
                // Log::info(json_encode($data));
                $result = sendFcmCurlRequest($data);
            }
        }
    }
    /******************    ---- cancel order vendor product  -----   ******************/
    public function cancelOrderByDriver($order_vendor){
        if($order_vendor ){
            $order = Order::with(array(
                'vendors' => function ($query) use ($order_vendor) {
                $query->where('vendor_id', $order_vendor->vendor_id);
                }
                ))->find($order_vendor->order_id);
                $return_response =  $this->GetVendorReturnAmount([], $order);
                //return amount to user wallet
                if ($return_response['vendor_return_amount'] > 0) {
                    $user = User::find($order_vendor->user_id);
                    $wallet = $user->wallet;
                    $credit_amount = $return_response['vendor_return_amount'];//$currentOrderStatus->payable_amount;
                    $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $order_vendor->orderDetail->order_number . ' (' . $order_vendor->vendor->name . ')']);
                    $this->sendWalletNotification($user->id,  $order_vendor->orderDetail->order_number);
                }
        }
    }

}
