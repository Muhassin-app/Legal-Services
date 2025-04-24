<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

class AhoyController extends Controller
{
	
	use \App\Http\Traits\Ahoy;

    private $api_key;
    private $app_url;
    private $base_price;
    private $distance;
    private $amount_per_km;
    public $status;

    public function __construct()
    {
        $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'ahoy')->where('status', 1)->first();
        if($simp_creds){
            $this->status = $simp_creds->status??'0';
            $creds_arr = json_decode($simp_creds->credentials);
            $this->api_key = $creds_arr->api_key??'';
            $this->app_url = (($simp_creds->test_mode=='1')?'https://ahoydev.azure-api.net':'https://ahoyapis.azure-api.net'); //Live url - https://ahoydev.azure-api.net
            $this->test = $simp_creds->test_mode; 
            $this->base_price = $creds_arr->base_price ?? ''; 
            $this->distance = $creds_arr->distance ?? ''; 
            $this->amount_per_km = $creds_arr->amount_per_km ?? '';
        }else{
            return 0;
        }
    }

	public function configuration()
    {
        $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'ahoy')->where('status', 1)->first();
            if($simp_creds){
                $this->status = $simp_creds->status??'0';
                $creds_arr = json_decode($simp_creds->credentials);
                $this->api_key = $creds_arr->api_key??'';
                $this->app_url = (($simp_creds->test_mode=='1')?'https://ahoydev.azure-api.net':'https://ahoyapis.azure-api.net'); //Live url - https://ahoydev.azure-api.net
                $this->test = $simp_creds->test_mode; 
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


		 # get delivery fee getAhoyBaseFee
		 public function getAhoyBaseFee($vendorId)
		 {	
			$fees = 0;
			$this->configuration();
			if($this->status == 1 && $this->base_price>0){
				$distance = $this->getDistance($vendorId);
				if($distance){
					//Helper Function
					$fees =   getBaseprice($distance,'ahoy');
				}
			}
			return $fees;
		}


	public function createPreOrderRequestAhoy($user_id,$orderVendor)
    { 
		$this->configuration();
		if($this->status)
		{
			$order = Order::find($orderVendor->order_id);
        	$customer = User::find($user_id);
			$vendor_details = Vendor::find($orderVendor->vendor_id);
			$cus_address = UserAddress::find($order->address_id);
			//$orderProducts = OrderVendorProduct::where(['order_id'=>$orderVendor->order_id,'order_vendor_id'=>$orderVendor->id])->get();
            $scheduledAt = '';
            if(isset($order->scheduled_date_time) && $order->scheduled_date_time){
                $scheduledAt = strtotime($order->scheduled_date_time);
            }

             # Request body
             $data = array (
                'pickupLocationId' =>  $vendor_details->ahoy_location ? json_decode($vendor_details->ahoy_location)->id : '',   //Required 
                "companyOrderTrackId"=> $orderVendor->id.'-'.$orderVendor->order_id.'-'.$orderVendor->vendor_id,
                "orderLargeBoxQuantity"=> '0',
                "orderMidBoxQuantity"=> '1',
                "orderSmallBoxQuantity"=> '0',
                'customerName' => $customer->name,  //+971 code is only for dubai and it's required
				//'customerPhone' => '971566134856', 
				'customerPhone' => ($customer->dial_code??'+971').$customer->phone_number,
				'customerEmail' => $customer->email,
                'customerAddress'=> $cus_address->address,
				'customerLatitude' => $cus_address->latitude, //Required
				'customerLongitude' => $cus_address->longitude, //Required
                "isCashPayment"=> ($order->payment_option_id==1)?true:false,
                "isCardPayment"=> false,
                "paymentAmount"=> ($order->payment_option_id==1)?$order->total_amount : 0,
                "customerAddressTypeId"=> '2',
                "customerAddressNote"=> null,
                "area"=> null,
                "building"=> null,
                "floor"=> null,
                "unit"=> null,
                "temperatureTypeId"=> 0
            );

    	    $orderSuc = $this->createPreOrder($data);
            if(isset($orderSuc->preOrderId) && !empty($orderSuc->preOrderId)){
                return $this->confirmOrderPreRequestAhoy($orderSuc);
            }else{
                return 0;
            }

            //     • CustomerAddressTypeId
            //     ◦ 1 => Tower, (either office or apartment)
            //     ◦ 2 => Building (villa, police station. etc)
            //     ◦ 3 => Commercial (warehouse)
            // • Area => Area
            // • Building => “Building Name or Number”
            // • Floor => “Floor Number”
            // • Unit => “Apartment Number”.
            // • TemperatureTypeId 
            //     ◦ 0 => Normal
            //     ◦ 1 => Cold
            //     ◦ 2 => Warm

			  
		}
		//Response Result
        // "preOrderId": 220,
        // "expiryTime": 1600352489947,
        // "services": [{
        //     "serviceName": "Bike",
        //     "serviceDetails": "ETA 6:25 PM",
        //     "numberOfVehicles": 1,
        //     "unitPrice": 20.00,
        //     "currency": "AED",
        //     "serviceId": 5,
        //     "serviceImageUrl": "https://ahoydelivery.blob.core.windows.net/icons/expressbike.png"
        // }]

    }

    public function confirmOrderPreRequestAhoy($responseData)
    {
		$this->configuration();
		if($this->status){ 
            $typeId = $responseData->onDemand->deliveryServiceId;
            $data =array('preOrderId'=>$responseData->preOrderId,'deliveryServiceTypeId'=>$typeId);
			return $order= $this->confirmPreOrder($data);
		}
    }



    public function getPreOrderFee($vendor_id,$cus_address)
    {
        $this->configuration();
		if($this->status)
		{
            $user_id = auth()->id();
        	$customer = User::find($user_id);
			$vendor_details = Vendor::find($vendor_id);
			//$cus_address = UserAddress::where(['is_primary'=>'1','user_id'=>$user_id])->first();
           
            # Request body
            $data = array (
                'pickupLocationId' => $vendor_details->ahoy_location ? json_decode($vendor_details->ahoy_location)->id : '',   //Required 
                "companyOrderTrackId"=> '',
                "orderLargeBoxQuantity"=> '0',
                "orderMidBoxQuantity"=> '1',
                "orderSmallBoxQuantity"=> '0',
                'customerName' => $customer->name,
				'customerPhone' =>($customer->dial_code??'+971').$customer->phone_number,
				//'customerPhone' => '971566134856', 
				'customerEmail' => $customer->email,
                'customerAddress'=> $cus_address->address,
				'customerLatitude' => $cus_address->latitude, //Required
				'customerLongitude' => $cus_address->longitude, //Required
                "isCashPayment"=> true,
                "isCardPayment"=> false,
                "paymentAmount"=> 0,
                "customerAddressTypeId"=> '2',
                "customerAddressNote"=> null,
                "area"=> null,
                "building"=> null,
                "floor"=> null,
                "unit"=> null,
                "temperatureTypeId"=> 0
            );
            $orderSuc = $this->createPreOrder($data);
            if($orderSuc->preOrderId != ''){
               // $this->confirmOrderPreRequestAhoy($orderSuc);
                return $orderSuc->onDemand->price;
            }else{
                return 0;
            }
        }


    }


    # get delivery fee createLocation Courier Service
		public function createLocation($vendor_details,$request)
		{
			$this->configuration();
            $data =array(
                'locationName'=>$request->location_name ?? '',
                'Address'=>$vendor_details->address ?? '',
                'latitude'=>$vendor_details->latitude ?? '',
                'longitude'=>$vendor_details->longitude ?? '',
                'locationType'=>$request->location_type??1,
                'PhoneNumber'=>($vendor_details->dial_code??'+971').$vendor_details->phone_no ?? '',
                'Email'=>$vendor_details->email ?? ''
            );
            //dd($data);
            if($this->status){
                return $this->createNewLocation($data);
            }
            return false; 
		}


    public function cancelOrderRequestAhoy($order_id)
    {
		$this->configuration();
		if($this->status){
            $data =array('order_uuid'=>$order_id,'update_type'=>'Cancel');
			return $cancel_order= $this->cancelOrder($data);
		}
    }


	public function ahoyWebhook(Request $request)
    {
        // {
        //     "orderId": 87231,
        //     "statusId": 12,
        //     "orderStatusName": "Assigned to job",
        //     "companyOrderTrackId": "1165-1103-40",
        //     "updateTime": 1649400020000
        //   }

        // {
        //     "orderId": 87231,
        //     "statusId": 22,
        //     "orderStatusName": "Out For Delivery",
        //     "companyOrderTrackId": "1165-1103-40",
        //     "updateTime": 1649400425000
        //   }

        // {
        //     "orderId": 87231,
        //     "statusId": 3,
        //     "orderStatusName": "Delivered",
        //     "companyOrderTrackId": "1165-1103-40",
        //     "updateTime": 1649400466000
        //   }

    try{
        $trackingId = '';
        $json = json_decode($request->getContent());

        if($request){
            Webhook::create(['tracking_order_id'=>(($json->orderId)?$json->orderId:''),'response'=>$request->getContent()]);
           }
           
        if(isset($json->statusId) && $json->statusId == '12')
        {
            $awb = $json->orderId;
            $details = OrderVendor::where('web_hook_code',$awb)->first();
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'1']);
       
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);
        }elseif(isset($json->statusId) && $json->statusId == '22')
        {
			$awb = $json->orderId;
            $details = OrderVendor::where('web_hook_code',$awb)->first();
            //Update in vendor status
            VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'4']);
            
            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);
        }elseif(isset($json->statusId) && $json->statusId == '3')
        {
			$awb = $json->orderId;
            $details = OrderVendor::where('web_hook_code',$awb)->first();
            //Update in vendor status
            VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'5']);

            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
     
           
            //Update in vendor status
            VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'6']);

            VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'5','type'=>'2']);
        }

        if($request && isset($json)){
         Webhook::create(['tracking_order_id'=>(($json->orderId)?$json->orderId:''),'response'=>$request->getContent()]);
        }
            
        }catch(\Exception $e){
            return response([],200);
        }
        return response([],200);

    }

    public function setWebhook(Request $request)
    {
        $this->configDetails();
        if($this->test==1){
            return $this->setWebhookUrl($request->url);
        }else {
            return 'Webhook url not set.';
        }
    }


}
