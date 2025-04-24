<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\ShippingOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

trait D4BDunzo{

  private $api_key;
  private $app_url;
  private $base_price;
  private $distance;
  private $amount_per_km;
  public $status;
  public $client_id;
  public $client_secret;
  public $token;
/**
 * Dunzo integrations Details
 * Req Api Key
 * Req App URl
 */

 public function __construct()
 {

    $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'd4b_dunzo')->where('status', 1)->first();

    if($simp_creds){
         $this->status = $simp_creds->status??'0';
         $creds_arr = json_decode($simp_creds->credentials);

         $this->client_id = $creds_arr->client_id??'';
         $this->client_secret = $creds_arr->client_secret??'';
         $this->client_id = $creds_arr->client_id??'';
         $this->app_url = (($simp_creds->test_mode=='1')?'https://apis-staging.dunzo.in/api':'https://api.dunzo.in/api');
         $this->base_price = $creds_arr->base_price ?? '';
         $this->distance = $creds_arr->distance ?? '';
         $this->amount_per_km = $creds_arr->amount_per_km ?? '';
         $response = Http::withHeaders([
             'client-id' => $this->client_id,
             'client-secret' => $this->client_secret,
             'Accept-Language' => 'en_US',
             'Content-Type' => 'application/json',
         ])->get($this->app_url."/v1/token");
         // Work with the response as needed
         $status = $response->status();
         $content = $response->json(); // Assuming the response is in JSON format
         $this->token = $content['token'];
     }else{
         return 0;
     }
 }

  public function configDetails()
  {
    $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'd4b_dunzo')->where('status', 1)->first();

    if($simp_creds){
         $this->status = $simp_creds->status??'0';
         $creds_arr = json_decode($simp_creds->credentials);

         $this->client_id = $creds_arr->client_id??'';
         $this->client_secret = $creds_arr->client_secret??'';
         $this->client_id = $creds_arr->client_id??'';
         $this->app_url = (($simp_creds->test_mode=='1')?'https://apis-staging.dunzo.in/api':'https://api.dunzo.in/api'); //
         $this->base_price = $creds_arr->base_price ?? '';
         $this->distance = $creds_arr->distance ?? '';
         $this->amount_per_km = $creds_arr->amount_per_km ?? '';
         $response = Http::withHeaders([
             'client-id' => $this->client_id,
             'client-secret' => $this->client_secret,
             'Accept-Language' => 'en_US',
             'Content-Type' => 'application/json',
         ])->get($this->app_url."/v1/token");


         // Work with the response as needed
         $status = $response->status();
         $content = $response->json(); // Assuming the response is in JSON format
         $this->token = $content['token'];
     }else{
         return 0;
     }
  }


  //Quotation Function Api
  public function getfees($data)
  {
    $this->configDetails();
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $this->app_url."/oporder/service/availability",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "apikey: {$this->api_key}"
    ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    if ($err) {
        return  $err;
    } else {
        return json_decode($response);
    }

}


public function createOrder($orderVendor,$vendor_details,$cus_address,$customer,$order,$scheduledAt = null)
{
    $this->configDetails();

    $postdata = [
        'request_id' =>  $orderVendor->id.'-'.$orderVendor->order_id.'-'.$orderVendor->vendor_id.strtotime(now()),
        // 'reference_id' => '9357d296-c366-4409-872d-2e0898f27f80'.strtotime(now()),
        'pickup_details' => [
            [
                'reference_id' => 'pick_ref_1'.$orderVendor->id.'-'.$orderVendor->order_id.'-'.$orderVendor->vendor_id.strtotime(now()),
                'special_instructions' => $order->specific_instructions??'fragile items, handle with great care',
                'address' => [
                    // 'apartment_address' => '004',
                    'street_address_1' => $vendor_details->address,
                    // 'street_address_2' => 'LB Shastri nagar',
                    // 'landmark' => 'Iblur lake',
                    // 'city' =>  $vendor_details->city??null,
                    // 'state' => $vendor_details->state??null,
                    // 'pincode' => $vendor_details->pincode??null,
                    'country' =>  $vendor_details->country??null,
                    'lat' => (float) $vendor_details->latitude,
                    'lng' => (float) $vendor_details->longitude,
                    'contact_details' => [
                        'name' => $vendor_details->name,
                        'phone_number' => $vendor_details->phone_no,
                    ],
                ],
                'otp_required' => false,
            ],
        ],
        // 'optimised_route' => true,
        'drop_details' => [
            [
                'reference_id' => 'drop_ref_1'.$orderVendor->id.'-'.$orderVendor->order_id.'-'.$orderVendor->vendor_id.strtotime(now()),
                'special_instructions' => $order->specific_instructions??'leave at door step and ring the bell',
                'address' => [
                    // 'apartment_address' => '204 Block 4',
                    'street_address_1' => 'Suncity Apartments',
                    // 'street_address_2' => 'Bellandur',
                    // 'landmark' => 'Iblur lake',
                    // 'city' =>  $cus_address->city ?? null,
                    // 'state' => $cus_address->state ?? null,
                    // 'pincode' => $cus_address->pincode  ?? null,
                    'lat' => (float) $cus_address->latitude,
                    'lng' => (float) $cus_address->longitude,
                    'country' =>$cus_address->country ?? null,
                    'contact_details' => [
                        'name' => $customer->name,
                        'phone_number' => $customer->phone_number,
                    ],

                ],

                'otp_required' => false,
                'payment_data' => [
                    'payment_method' => (($order->payment_option_id==1)?'COD':'Prepaid'),
                    'amount' =>  $order->total_amount,
                ],
            ]
        ],
        'payment_method' => 'DUNZO_CREDIT',
        'delivery_type' => (($scheduledAt)?'SCHEDULED':null),
        'schedule_time' => (($scheduledAt)?$scheduledAt:null),
    ];
    \Log::info($postdata);


    $response_d4b_dunzo = Http::withHeaders([
        'client-id' => $this->client_id,
        'Authorization' => $this->token,
        'Accept-Language' => 'en_US',
        'Content-Type' => 'application/json',
    ])
    ->post($this->app_url.'/v2/tasks', $postdata);

    \Log::info($response_d4b_dunzo->json());

    return $response_d4b_dunzo->json();
}


  public function orderDetail($id)
  {
    $data = array('order_uuid'=>$id);
    $this->configDetails();
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $this->app_url."/aa/oporder/getbyid",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "apikey: {$this->api_key}"
    ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {
    echo $response;
    }

  }


  public function checkAvilabilty($data)
  {
        $this->configDetails();
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->app_url."/oporder/dscheck",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "apikey: ".$this->api_key
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return (object)json_decode($response,true);
        }

        // "status": true,
        // "code": 200,
        // "message": "Riders available",
        // "data": {}

  }


    public function cancelOrder($data,$task_id)
    {
        $this->configDetails();
        $response = Http::withHeaders([
            'client-id' => $this->client_id,
            'Authorization' => $this->token,
            'Accept-Language' => 'en_US',
            'Content-Type' => 'application/json',
        ])->post($this->app_url.'/v2/tasks/'.$task_id."/cancel", $data);
        if($response->successful()){
            return $response->json();
        }else{
            $response = 2;
        }
    }
}
