<?php
namespace App\Http\Traits;

use App\Models\VendorOrderDispatcherStatus;
use App\Models\VendorOrderStatus;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{ShippingOption,User, UserAddress, Vendor, OrderVendor, Order};
use Auth;
trait Borzoe{

    private $api_url;
    private $api_key;

    public function brozoConfig()
    {
        $shippingOption = ShippingOption::where('code', 'borzo')->first();
        $cred = json_decode($shippingOption->credentials);
        if ($shippingOption->test_mode) {
            $url = 'https://robotapitest-in.borzodelivery.com/api/business/1.4/';
        }else{
            $url = 'https://robot-in.borzodelivery.com/api/business/1.4/';
        }
        $this->api_url = $url;
        $this->api_key = $cred->api_key;
    }

    public function borzoeDelivery($vendor_id){
        try {
            $this->brozoConfig();
            $customer = User::find(Auth::id());
            $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
            $vendor_details = Vendor::find($vendor_id);
            $url = $this->api_url.'calculate-order';

            $data = [
                'matter' => 'Food',
                'points' => [
                    [
                        'address' => $vendor_details->address,
                        'contact_person' => [
                            'phone' => $vendor_details->phone_no,
                        ],
                        'latitude' => $vendor_details->latitude??'',
                        'longitude' =>$vendor_details->longitude??''
                    ],
                    [
                        'address' => $cus_address->address,
                        'contact_person' => [
                            'phone' => $customer->phone_number,
                        ],
                        'latitude' => $cus_address->latitude??'',
                        'longitude' =>$cus_address->longitude??''
                    ],
                ],
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-DV-Auth-Token: '.$this->api_key.'']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function placeOrderToBorzoApi($order_vendor, $vendor_id, $order_id){
        $this->brozoConfig();
        $order = Order::find($order_id);
        $customer = User::findOrFail($order->user_id);
        $cus_address = UserAddress::where('id', $order->address_id)->orderBy('is_primary', 'desc')->first();
        $note = '';
        if(!empty($cus_address)){
            $note .= $cus_address->house_number.', '.$cus_address->street.', '.$cus_address->pincode.", ".$cus_address->extra_instruction;
        }

        $amountPay = $order_vendor->payable_amount??0;
        $vendor_details = Vendor::findOrFail($vendor_id);
        $is_cod_cash_voucher_required = false;
        $taking_amount = '';
        if ($order->payment_option_id == 1) {
            $payable_amount = $amountPay - $order->loyalty_amount_saved - $order->wallet_amount_used;
            $is_cod_cash_voucher_required = true; // It is mandatory for Cash-On-Delivery Orders
            $taking_amount = $payable_amount; // Required only if payment_type is COD
        } else {
            if ($order->is_postpay == 1 && $order->payment_status == 0) {
                $payable_amount = $amountPay - $order->loyalty_amount_saved - $order->wallet_amount_used;
                $is_cod_cash_voucher_required = true; // It is mandatory for Cash-On-Delivery Orders
                $taking_amount = $payable_amount; // Required only if payment_type is COD
            }
        }
        if($payable_amount <= 0){
            $is_cod_cash_voucher_required = false; // It is mandatory for Cash-On-Delivery Orders
            $taking_amount = 0.00;
        }
        $url = $this->api_url.'create-order';
        $data = [
            'matter' => 'Food',
            'points' => [
                [
                    'address' => $vendor_details->address,
                    'contact_person' => [
                        'phone' => $vendor_details->phone_no,
                    ],
                    'latitude' => $vendor_details->latitude??'',
                    'longitude' =>$vendor_details->longitude??''
                ],
                [
                    'address' => $cus_address->address,
                    'contact_person' => [
                        'phone' => $customer->phone_number,
                    ],
                    'latitude' => $cus_address->latitude??'',
                    'longitude' =>$cus_address->longitude??'',
                    'is_cod_cash_voucher_required' => $is_cod_cash_voucher_required,
                    'taking_amount' => $taking_amount,
                    'note' => $note
                ]
            ],
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-DV-Auth-Token: '.$this->api_key.'']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function cancleOrderToBorzoApi($vendor_id, $order_id){
        $this->brozoConfig();
        $order = Order::find($order_id);
        $borzo_order_id = OrderVendor::where('order_id', $order->id)->where('vendor_id', $vendor_id)->pluck('borzoe_order_id')->toArray();
        $url = $this->api_url.'cancel-order';

        $data = [
            'order_id' => $borzo_order_id[0]
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-DV-Auth-Token: '.$this->api_key.'']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

	public function Webhook(Request $request)
    {
        $this->brozoConfig();
		//1-Created
		//2-planned
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
				Webhook::create(['tracking_order_id'=>(($json->awb)?$json->delivery->order_id:''),'response'=>$request->getContent()]);
            }

			if(isset($json->shipment_status_id) && $json->event_type == 'delivery_changed' && $json->status == 'courier_assigned')
			{
				$order_id = $json->delivery->order_id;
				$details = OrderVendor::where('ship_awb_id',$order_id)->first();
				VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'1']);
			}elseif(isset($json->shipment_status_id) && $json->event_type == 'delivery_changed' && $json->status == 'courier_departed')
			{
				$order_id = $json->delivery->order_id;
				$details = OrderVendor::where('ship_awb_id',$order_id)->first();
				VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);
			}elseif(isset($json->shipment_status_id) && $json->event_type == 'delivery_changed' && $json->status == 'courier_at_pickup')
			{
				$order_id = $json->delivery->order_id;
				$details = OrderVendor::where('ship_awb_id',$order_id)->first();
				//Update in vendor status
				VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'4']);

				VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);
			}elseif(isset($json->shipment_status_id) && $json->event_type == 'delivery_changed' && $json->status == 'parcel_picked_up')
			{
				$order_id = $json->delivery->order_id;
				$details = OrderVendor::where('ship_awb_id',$order_id)->first();
				//Update in vendor status
				VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'5']);

				VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
			}elseif(isset($json->shipment_status_id) && $json->event_type == 'delivery_changed' && $json->status == 'courier_arrived')
			{
				$order_id = $json->delivery->order_id;
				$details = OrderVendor::where('ship_awb_id',$order_id)->first();

				//Update in vendor status
				VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'6']);

				VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'5','type'=>'2']);
			}

			if($request && isset($json)){
			 Webhook::create(['tracking_order_id'=>(($json->delivery->order_id)?$json->delivery->order_id:''),'response'=>$request->getContent()]);
			}
			}catch(\Exception $e){
				\Log::info($e->getMessage());
				return response([],200);
			}
			return response([],200);

    }



}
