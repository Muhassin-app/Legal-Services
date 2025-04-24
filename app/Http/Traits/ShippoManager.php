<?php
namespace App\Http\Traits;

use App\Models\{ShippingOption, ShippoDeliveryOption, User, UserAddress, Vendor};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Log;
trait ShippoManager{

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
       $ship_creds = ShippingOption::select('credentials', 'test_mode')->where('code', 'shippo')->where('status', 1)->first();
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
    public function credentials()
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
            $this->width  = $creds_arr->width??'1';
            $this->height = $creds_arr->height??'1';
            $this->weight = $creds_arr->weight??'1';
        }else{
			$this->status =0;
		}
    }


    public function checkCourierService($vid)
    {
        $vendors = array();
        $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
        $vendor_details = Vendor::find($vid);
        if($cus_address->pincode!='')
        {
            $this->credentials();
            $endpoint='shipments/';
            $data = array (
                "address_to"=> [
                    "name"=> auth()->user()->name??null,
                    "street1"=> ($cus_address->address)?? null,
                    "city"=> ($cus_address->city)?? null,
                    "state"=> ($cus_address->state)?? null,
                    "zip"=> ($cus_address->pincode)?? null,
                    "country"=> ($cus_address->country)?? null,
                    "phone"=> auth()->user()->phone_number??null,
                    "email"=> auth()->user()->email??null
                ],
                "address_from"=> [
                    "name"=> $vendor_details->name??null,
                    "street1"=> ($vendor_details->address)?? null,
                    "city"=> ($vendor_details->city)?? null,
                    "state"=> ($vendor_details->state)?? null,
                    "zip"=> ($vendor_details->pincode)?? null,
                    "country"=> ($vendor_details->country)?? null,
                    "phone"=> $vendor_details->phone_number??null,
                    "email"=> $vendor_details->email??null
                ],
                "parcels"=> [
                    "length"=> $this->length,
                    "width"=> $this->width,
                    "height"=> $this->height,
                    "distance_unit"=> "in",
                    "weight"=> $this->weight,
                    "mass_unit"=> "lb"
                ],
                "async"=> false

            );
            $json =  ShippoDeliveryOption::where(['user_id'=>auth()->id(),'zipcode_from'=>$vendor_details->pincode,'zipcode_to'=>$cus_address->pincode])->whereDate('created_at',date('Y-m-d'))->first();
           // dd($json);
            if(!isset($json->json))
            {
                $result = $this->postCurl($endpoint,json_encode($data),$this->token);
                if($result->rates){
                    ShippoDeliveryOption::create(['user_id'=>auth()->id(),'zipcode_from'=>$vendor_details->pincode,'zipcode_to'=>$cus_address->pincode,'created_at'=>date('Y-m-d'),'vendor_id'=>$vid,'address_id'=>$cus_address->id,'json'=>json_encode($result)]);
                }
            }else{
                $result = json_decode($json->json);
                //dd($result);
            }
            //courier_name , rate, courier_company_id , etd , etd_hours , estimated_delivery_days
                if($result->rates)
                {
                    $result = $result->rates;
                    foreach($result as $key => $data)
                    {
                        $vendors[] = array(
                            'type'=>'SH',
                            'courier_name' => $data->provider.' - '.$data->servicelevel->name,
                            'rate' => number_format(round($data->amount), 2, '.', ''),
                            'courier_company_id' => $data->object_id,
                            'etd' => $data->estimated_days,
                            'etd_hours' => $data->arrives_by,
                            'estimated_delivery_days' => $data->estimated_days,
                            'code' => 'SH_'.$data->object_id
                        );
                    }
                }
        }
        return $vendors;
    }

    public function createOrder($data){
        $endpoint="/transactions";
        $response=$this->postCurl($endpoint,$data,$this->token);
        return $response;
    }


    private function postCurl($endpoint,$data,$token=null):object{
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->api_url.''.$endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
                $headers = array();
                //$headers[] = 'Accept: */*';
                if(!is_null($token)){
                    $headers[] = "authorization: ShippoToken ${token}";
                }
                    $headers[] = "content-type: application/json";
                     //dd( $headers);

                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                }
                curl_close($ch);
                
                return json_decode($result); 
    }

    private function getCurl($endpoint,$data,$token=null):object{

        $curl = curl_init();
          
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->api_url.''.$endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            if($data)
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );


            $headers = array();
            $headers[] = 'Accept: */*';
            if(!is_null($token)){
                $headers[] = "Authorization: Bearer $token";
            }
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
            }
            curl_close($ch);
            return json_decode($result); 
    }


}
