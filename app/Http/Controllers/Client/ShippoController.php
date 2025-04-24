<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ToasterResponser;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Client, ClientPreference,ShippingOption};
use Auth, Storage, Validator;

class ShippoController extends BaseController
{
    use \App\Http\Traits\ShippoManager;

    use ToasterResponser;

    private $token;
    private $length;
    private $width;
    private $height;
    private $weight;
    private $api_url;
    private $status;
    private $base_price;

    
    public function __construct()
    {
        $ship_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'shippo')->where('status', 1)->first();
        if(isset($ship_creds) && !empty($ship_creds)){
            $creds_arr = json_decode($ship_creds->credentials);
            
            $this->token = $creds_arr->token;
            $this->api_url = 'https://api.goshippo.com/';
            $this->base_price = $creds_arr->base_price??'0';
            if($this->base_price>0)
            {
                $this->base_price = $creds_arr->base_price??'0';
                $this->distance = $creds_arr->distance??'0';
                $this->amount_per_km = $creds_arr->amount_per_km??'0';
            }
            $this->status = $ship_creds->status??'0';

            $this->length = $creds_arr->length??'1';
            $this->width = $creds_arr->width??'1';
            $this->height = $creds_arr->height??'1';
            $this->weight = $creds_arr->weight??'1';
        }else{
			$this->status =0;
		}
    }

    public function configuration()
    {
        $ship_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'shippo')->where('status', 1)->first();
       
        if(isset($ship_creds) && !empty($ship_creds)){
            $creds_arr = json_decode($ship_creds->credentials);
            
            $this->token = $creds_arr->token;
            $this->api_url = 'https://api.goshippo.com/';
            $this->base_price = $creds_arr->base_price??'0';
            if($this->base_price>0)
            {
                $this->base_price = $creds_arr->base_price??'0';
                $this->distance = $creds_arr->distance??'0';
                $this->amount_per_km = $creds_arr->amount_per_km??'0';
            }
            $this->status = $ship_creds->status??'0';

            $this->length = $creds_arr->length??'1';
            $this->width = $creds_arr->width??'1';
            $this->height = $creds_arr->height??'1';
            $this->weight = $creds_arr->weight??'1';
        }else{
			$this->status =0;
		}
    }


    	# get delivery fee Shiprocket Courier Service
		public function getServices($vendorId)
		{
			$this->configuration();
			if($this->status == 1){
				if($this->base_price>0){
				    return $this->getShiprocketBaseFee($vendorId);
			    }else{
					return $this->checkCourierService($vendorId);
				}
			}
		}

        public function createOrderRequestShippo($orderVendor){
            $data = ["rate" => $orderVendor->courier_id];
            $response=$this->createOrder($data);
            return $response;
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
					$fees =   getBaseprice($distance,'shippo');
					if($fees>0){
						$fee[] = array(
							'type'=>'SH',
							'courier_name' => 'Shippo',
							'rate' => number_format(round($fees), 2, '.', ''),
							'courier_company_id' => '',
							'etd' => 0,
							'etd_hours' => 0,
							'estimated_delivery_days' => 0,
							'code' => 'SH_0'
						);
					}
				}

			}
			
			return $fee;
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


    public function updateAll(Request $request, $domain = '')
    {
        $msg = 'Shipping options have been saved successfully!';
        $method_id_arr = $request->input('method_id');
        $method_name_arr = $request->input('method_name');
        $active_arr = $request->input('active');
        $base_active = $request->input('base_active');
        $test_mode_arr = $request->input('sandbox');

        foreach ($method_id_arr as $key => $id) {
            $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr[$id])) && ($active_arr[$id] == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr[$id])) && ($test_mode_arr[$id] == 'on')) {
                    $test_mode = 1;
                }

                if ((isset($method_name_arr[$key])) && (strtolower($method_name_arr[$key]) == 'shippo')) {
                    $validatedData = $request->validate([
                        'shippo_token'       => 'required',
                        // 'shippo_password'       => 'required',
                    ]);
                    $json_creds = array(
                        'token' => $request->shippo_token,
                        // 'password' => $request->shiprocket_password,
                    );

                    if ((isset($base_active)) && ($base_active == 'on')) {
                        $json_creds['base_price'] = $request->base_price;
                        $json_creds['distance'] = $request->distance;
                        $json_creds['amount_per_km'] = $request->amount_per_km;
                    }else{
                        $json_creds['base_price'] = '0';
                        $json_creds['distance'] = '0';
                        $json_creds['amount_per_km'] = '0';
                    }
                        $json_creds['height'] = ($request->height) ?? 0;
                        $json_creds['width'] = ($request->width) ?? 0;
                        $json_creds['weight'] = ($request->weight) ?? 0;

                    $json_creds = json_encode($json_creds);

                }
            }
            ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);
        }
        $toaster = $this->successToaster(__('Success'), $msg);
        return redirect()->back()->with('toaster', $toaster);
    }
}
