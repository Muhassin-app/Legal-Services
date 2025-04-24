<?php
namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\{ShippingOption, UserAddress, Vendor};
use Illuminate\Support\Facades\Auth;

trait ShiprocketManager{

    private $email;
    private $password;
    private $api_url;
    public function __construct()
    {
        $ship_creds = ShippingOption::select('credentials', 'test_mode')->where('code', 'shiprocket')->where('status', 1)->first();
        $creds_arr = json_decode($ship_creds->credentials);
        $this->email = $creds_arr->username;
        $this->password = $creds_arr->password;
        $this->api_url = 'https://apiv2.shiprocket.in/v1/external';
        $this->weight = $creds_arr->weight;
    }

    public function credentials()
    {
        $ship_creds = ShippingOption::select('credentials', 'test_mode')->where('code', 'shiprocket')->where('status', 1)->first();
        if(isset($ship_creds) && !empty($ship_creds)){
            $creds_arr = json_decode($ship_creds->credentials);
            $this->email = $creds_arr->username;
            $this->password = $creds_arr->password;
            $this->api_url = 'https://apiv2.shiprocket.in/v1/external';
            $this->weight = $creds_arr->weight;
        }else{
            return false;
        }
    }
   
    public function getAuthToken():object{
        $this->credentials();
        $endpoint='/auth/login';
        $data=[
           'email'=>$this->email,
           'password'=>$this->password
        ];
        $response=$this->postCurl($endpoint,$data);
        return $response;
    }

    public function createOrder($token,$data){
        $endpoint="/orders/create/adhoc";
        $response=$this->postCurl($endpoint,$data,trim($token));

        return $response;
    }

    public function generateAWBForShipment($token,$data){
        $endpoint="/courier/assign/awb";
        $response=$this->postCurl($endpoint,$data,$token);
        return $response;
    }

    public function returnOrder($token,$data){
        $endpoint='/orders/create/return';
        $response=$this->postCurl($endpoint,$data,trim($token));
        return $response;
        
    }

    public function cancelOrder($token,$ids){
        $endpoint="orders/cancel";
        $data = [
            'ids' => $ids
        ];
        // dd($data);
        $response=$this->postCurl($endpoint,$data,$token);
        return $response;
    }

    public function trackingThroughAWB($token,$awbCode){
        $endpoint="/courier/track/awb/$awbCode";
        $response=$this->getCurl($endpoint,'',$token);
        return $response;
    }

    public function trackingThroughShipmentId($token,$shipmentId){
        $endpoint="/shipments/$shipmentId";
        $response=$this->getCurl($endpoint,'',$token);
        return $response;
    }

    

    public function updateOrderPickupAddress($token,$data)
    {
        $endpoint="orders/address/pickup";
        $response=$this->patchCurl($endpoint,$data,$token);
        return $response;
    }

    public function updateOrderDeliveryAddress($token,$data){
        $endpoint="orders/address/update";
        $response=$this->postCurl($endpoint,$data,$token);
        return $response;
    }


    public function addAddress($token,$vid,$name){
        $endpoint='/settings/company/addpickup';
        $data = array (
              'pickup_location' => $name,
              'name' => $vid->name,
              'email' => $vid->email,
              'phone' => $vid->phone_no,
              'address' => $vid->address,
              'address_2' => '',
              'city' => $vid->city,
              'state' => $vid->state,
              'country' => $vid->country,
              'pin_code' => $vid->pincode,
            );
        $response=$this->postCurl($endpoint,$data,trim($token));
        return $response;
    //   "success": true
    //   "address": {
    //   "company_id": 2001023
    //   "pickup_code": "Inderjit_1642589053"
    //      }

    }

    public function checkCourierService($token,$vid,$weight = null)
    {
        $vendors = array();
        $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
        $vendor_details = Vendor::find($vid);
        if($cus_address->pincode!='')
        {
        $this->credentials();
        $endpoint='/courier/serviceability';
        $data = array (
          'pickup_postcode' => ($vendor_details->pincode) ?? null,
          'delivery_postcode' => ($cus_address->pincode)?? null,
          'cod' => 0,
          'weight' => (($weight)?$weight:$this->weight),
          //'length' => 15,
          //'breadth' => 10,
          //'height' => 5,
          //'declared_value' => 50,
        );
        $result = $this->getCurl($endpoint,$data,trim($token));
        //courier_name , rate, courier_company_id , etd , etd_hours , estimated_delivery_days
        if($result->status == '200'){
          $result = $result->data->available_courier_companies;
          foreach($result as $key => $data)
          {
              $vendors[] = array(
                'type'=>'SR',
                'courier_name' => $data->courier_name,
                'rate' => number_format(round($data->rate), 2, '.', ''),
                'courier_company_id' => $data->courier_company_id,
                'etd' => $data->etd,
                'etd_hours' => $data->etd_hours,
                'estimated_delivery_days' => $data->estimated_delivery_days,
                'code' => 'SR_'.$data->courier_company_id
            );
          }
        }
        }
        return $vendors;
    }



    private function postCurl($endpoint,$data,$token=null):object{
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->api_url.''.$endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
                $headers = array();
                $headers[] = 'Accept: */*';
                if(!is_null($token)){

                   $headers[] = "Authorization: Bearer $token";
                    // dd( $headers);
                }
              $headers[] = 'Content-Type: application/json';
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
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
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            return json_decode($result); 
    }

    private function patchCurl($endpoint,$data,$token=null):object{
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->api_url.''.$endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        $headers = array();
        $headers[] = 'Accept: */*';
        if(!is_null($token)){
            $headers[] = "Authorization: Bearer $token";
        }
        $headers[] = 'Content-Type: application/json';
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result); 

    }

}