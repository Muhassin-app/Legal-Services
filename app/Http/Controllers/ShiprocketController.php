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

class ShiprocketController extends Controller
{
	
	use \App\Http\Traits\ShiprocketManager;

	private $base_price;
    private $status;
    private $distance;
    private $amount_per_km;
    private $weight;

    public function __construct()
    {
  
      $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'shiprocket')->where('status', 1)->first();
      if(isset($simp_creds) && !empty($simp_creds)){
            $creds_arr = json_decode($simp_creds->credentials);
            $this->base_price = $creds_arr->base_price??'0';
            if($this->base_price>0)
            {
                $this->base_price = $creds_arr->base_price??'0';
                $this->distance = $creds_arr->distance??'0';
                $this->amount_per_km = $creds_arr->amount_per_km??'0';
				$this->weight = $creds_arr->weight;
            }
            $this->status = $simp_creds->status??'0';
        }else{
			$this->status =0;
		}
    }


	public function configuration()
    {
  
      $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'shiprocket')->where('status', 1)->first();
	  if(isset($simp_creds) && !empty($simp_creds)){
            $creds_arr = json_decode($simp_creds->credentials);
            $this->base_price = $creds_arr->base_price??'0';
            if($this->base_price>0)
            {
                $this->base_price = $creds_arr->base_price??'0';
                $this->distance = $creds_arr->distance??'0';
                $this->amount_per_km = $creds_arr->amount_per_km??'0';
				$this->weight = $creds_arr->weight;
            }
            $this->status = $simp_creds->status??'0';
        }else{
			$this->status = 0;
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


	    # get delivery fee Shiprocket
		public function getShiprocketFee($vendorId)
		 {	
			$fee = 0;
			$this->configuration();
			if($this->status == 1){
				$token = $this->getAuthToken();
				$distance = $this->getDistance($vendorId);
				if($distance){
					//Helper Function
					$fee =   getBaseprice($distance,'shiprocket');
				}
			}
			return $fee;
		}

		 # get delivery fee getShiprocketBaseFee
		 public function getShiprocketBaseFee($vendorId)
		 {	
			$fee = array();
			$fees = 0;
			$this->configuration();
			if($this->status == 1 && $this->base_price>0){
				$distance = $this->getDistance($vendorId);
				if($distance){
					//Helper Function
					$fees =   getBaseprice($distance,'shiprocket');
					if($fees>0){
						$fee[] = array(
							'type'=>'SR',
							'courier_name' => 'Shiprocket',
							'rate' => number_format(round($fees), 2, '.', ''),
							'courier_company_id' => '',
							'etd' => 0,
							'etd_hours' => 0,
							'estimated_delivery_days' => 0,
							'code' => 'SR_0'
						);
					}
				}

			}
			
			return $fee;
		}


		# get delivery fee Shiprocket Courier Service
		public function getCourierService($vendorId)
		{
			$this->configuration();
			if($this->status == 1){
				if($this->base_price>0){
				return $this->getShiprocketBaseFee($vendorId);
			}else{
					$token = $this->getAuthToken();
					return $this->checkCourierService($token->token,$vendorId);
				}
			}
		}

		public function trackingShipmentId($shipId)
		{
			$this->configuration();
			if($this->status == 1){
				$token = $this->getAuthToken();
				return $this->trackingThroughShipmentId($token->token,$shipId);
			}
			return 0;
		}

		public function trackingAWB($awbId)
		{
			$this->configuration();
			if($this->status == 1){
				$token = $this->getAuthToken();
				return $this->trackingThroughAWB($token->token,$awbId);
			}
			return 0;
		}


    public function checkShiprocket()
    {
		$this->configuration();
    	$token = $this->getAuthToken();
    	$order = $this->createOrder($token->token,[]);
    	$response = $this->generateAWBForShipment($token->token,["shipment_id"=>$order->shipment_id,"courier_id"=> "","status"=> ""]); // need kyc
    	$cancel_order= $this->cancelOrder($token->token,[$order->order_id]); // not working
    	$add_address = $this->addAddress($token->token,[]);
    	$tracking_shipping = $this->trackingThroughShipmentId($token->token,$order->shipment_id);
    	//dd($token->token,$order,$response,$order->order_id,$cancel_order,$add_address,$tracking_shipping);
    }

	public function addShiprocketPickup($vendor,$name)
    {
		$this->configuration();
    	$token = $this->getAuthToken();
		
		if(isset($token->token)){
			$address = $this->addAddress($token->token,$vendor,$name);
			//dd($address);
			return $address; 
		}
		return 0;
	}

	public function createOrderRequestShiprocket($user_id,$orderVendor)
    { 
		$this->configuration();
		$token = $this->getAuthToken();
		if($token->token)
		{
			$order = Order::find($orderVendor->order_id);
        	$customer = User::find($user_id);
			$vendor_details = Vendor::find($orderVendor->vendor_id);
			$cus_address = UserAddress::find($order->address_id);
			$orderProducts = OrderVendorProduct::where(['order_id'=>$orderVendor->order_id,'order_vendor_id'=>$orderVendor->id])->get();
			$itemsArray = array();
			$weight = array();
			foreach($orderProducts as $items)
			{
				$itemsArray[] = array (
					'name' => $items->product_name, //Required
					'sku' => $items->product->sku ?? $items->id, //Required
					'units' => $items->quantity,
					'selling_price' => decimal_format($items->price),
					'discount' => '',
					'tax' => decimal_format($items->taxable_amount),
					'hsn' => '',
					);
					$weight[] = $items->product->weight;
			}
			$weightSum = array_sum($weight);
			$data = array (
				'order_id' => $orderVendor->id.'-'.$orderVendor->order_id.'-'.$orderVendor->vendor_id,
				'order_date' => $order->scheduled_date_time ?? $order->created_at,
				'pickup_location' => $vendor_details->shiprocket_pickup_name ?? '',
				'channel_id' => '',
				'comment' => '',
				'billing_customer_name' => $customer->name ?? '',//Required  
				'billing_last_name' => '',
				'billing_address' => $cus_address->address ?? '', //Required 
				'billing_address_2' => '',
				'billing_city' => $cus_address->city ?? '', //Required 
				'billing_pincode' => $cus_address->pincode ?? '', //Required 
				'billing_state' => $cus_address->state ?? '', //Required 
				'billing_country' => $cus_address->country ?? '', //Required 
				'billing_email' => $customer->email ?? '', //Required
				'billing_phone' => $customer->phone_number, //Required
				'shipping_is_billing' => true,
				'shipping_customer_name' => '',
				'shipping_last_name' => '',
				'shipping_address' => '',
				'shipping_address_2' => '',
				'shipping_city' => '',
				'shipping_pincode' => '',
				'shipping_country' => '',
				'shipping_state' => '',
				'shipping_email' => '',
				'shipping_phone' => '',
				'order_items' => $itemsArray,
				'payment_method' => (($order->payment_option_id==1)?'COD':'Prepaid'),
				'shipping_charges' => 0,
				'giftwrap_charges' => 0,
				'transaction_charges' => 0,
				'total_discount' => 0,
				'sub_total' => $order->total_amount,
				'length' => '.5',
				'breadth' => '.5',
				'height' => '.5',
				'weight' => ($weightSum>0)? $weightSum : $this->weight,
			  );
		}
		$orderSuc = $this->createOrder($token->token,$data);
		if($orderSuc->status_code == 1)
		{
		  $awb = $this->AWBForShipment($orderSuc->shipment_id,$orderVendor->courier_id,$token->token);
			\Log::info('awb');
			\Log::info([$awb]);
		// return $awb;
		}
		return (($awb==0)?$orderSuc:$awb);
		//Response Result
		//"order_id": 181022136
  		//"shipment_id": 180553979
  		//"status": "NEW"
  		//"status_code": 1
  		//"onboarding_completed_now": 0
  		//"awb_code": ""
  		//"courier_company_id": ""
  		//"courier_name": ""

    }


	public function AWBForShipment($shipment_id,$courierId,$token)
    {
		$data['shipment_id'] = $shipment_id;
		if($courierId){$data['courier_id'] = $courierId;}
		$awb_order= $this->generateAWBForShipment($token,$data);
		if($awb_order->awb_assign_status == 1)
		{
			return $awb_order->response->data;
		}
		return 0;
    }


    public function cancelOrderRequestShiprocket($order_id)
    {
		$this->configuration();
		$token = $this->getAuthToken();
		if(isset($token->token)){
			return $cancel_order= $this->cancelOrder($token->token,[$order_id]);
		}
    }


	public function shiprocketWebhook(Request $request)
    {
		//1-AWB Assigned
		//2-Label Generated
		//3-Pickup Scheduled/Generated
		//19-Out For Pickup 
		//42-Picked Up 
		//6-Shipped 
		//7-Delivered 
		//8-Cancelled 
		//11-Pending 
		//17-Out For Delivery 
		//18-In Transit 
		//38-Reached Destination Hub 

        try{
			$trackingId = '';
			$json = json_decode($request->getContent());
	
			if($request){
				Webhook::create(['tracking_order_id'=>(($json->awb)?$json->awb:''),'response'=>$request->getContent()]);
			   }
			   
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
				//Update in vendor status
				VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'4']);
				
				VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);
			}elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '42')
			{
				$awb = $json->awb;
				$details = OrderVendor::where('ship_awb_id',$awb)->first();
				//Update in vendor status
				VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'5']);
	
				VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
			}elseif(isset($json->shipment_status_id) && $json->shipment_status_id == '7')
			{
				$awb = $json->awb;
				$details = OrderVendor::where('ship_awb_id',$awb)->first();
			   
				//Update in vendor status
				VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'6']);
	
				VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'5','type'=>'2']);
			}
	
			if($request && isset($json)){
			 Webhook::create(['tracking_order_id'=>(($json->awb)?$json->awb:''),'response'=>$request->getContent()]);
			}
				
			}catch(\Exception $e){
				\Log::info($e->getMessage());
				return response([],200);
			}
			return response([],200);

    }


}
