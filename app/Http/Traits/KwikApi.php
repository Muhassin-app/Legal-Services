<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\ShippingOption;
use Carbon\Carbon;
use Http\Message\Cookie;
use Illuminate\Http\Response;
use Log;

trait KwikApi{

  private $api_key;
  private $app_url;
  private $base_price;
  private $distance;
  private $amount_per_km;
  public $status;
  public $test;

/**
 * Dunzo integrations Details
 * Req Api Key
 * Req App URl
 */

//   public function __construct()
//   {
//     $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'kwikapi')->where('status', 1)->first();
//     if($simp_creds){
//         $this->status = $simp_creds->status??'0';
//         $creds_arr = json_decode($simp_creds->credentials);
//         $this->api_email = $creds_arr->api_email??'';
//         $this->api_pass = $creds_arr->api_pass??'';
//         $this->app_url = (($simp_creds->test_mode=='1')?'https://staging-api-test.kwik.delivery':'https://staging-api-test.kwik.delivery'); //Live url - 
//         $this->test = $simp_creds->test_mode; 
//         $this->base_price = $creds_arr->base_price ?? ''; 
//         $this->distance = $creds_arr->distance ?? ''; 
//         $this->amount_per_km = $creds_arr->amount_per_km ?? '';
//     }else{
//         return 0;
//     }
//   }

  public function configDetails()
  {
    $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'kwikapi')->where('status', 1)->first();
    if($simp_creds){
        $this->status = $simp_creds->status??'0';
        $creds_arr = json_decode($simp_creds->credentials);
        $this->api_email = $creds_arr->api_email??'';
        $this->api_domain = $creds_arr->api_domain??"staging-client-panel.kwik.delivery";
        $this->api_pass = $creds_arr->api_pass??'';
        $this->app_url = (($simp_creds->test_mode=='1')?'https://staging-api-test.kwik.delivery':'https://staging-api-test.kwik.delivery'); //Live url - 
        $this->test = $simp_creds->test_mode; 
        $this->base_price = $creds_arr->base_price ?? ''; 
        $this->distance = $creds_arr->distance ?? ''; 
        $this->amount_per_km = $creds_arr->amount_per_km ?? '';

        $this->authLoginVendor();
        return true;
    }else{
        return 0;
    }
  }


  //Vendor Auth Login Api
  public function authLoginVendor()
  {
     
    try{
        $end_url = '/vendor_login';
        $ch = curl_init();

        $data = ["domain_name"=> !empty($this->api_domain) ? $this->api_domain : '',
            "email"=> $this->api_email,
            "password"=> $this->api_pass,
            "api_login"=> 1];

        curl_setopt($ch, CURLOPT_URL, $this->app_url.$end_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json"
        ));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $httpCode =  curl_error($ch);
        }
        curl_close($ch);
        $val = json_decode($result);
        $res = [];
        if(isset($val) && $val->status == '200')
        {
            $this->access_token=$val->data->access_token;
            $this->vendor_id=$val->data->vendor_details->vendor_id;
        }
        return true;
    }catch (\Exception $e) {
        return array('code'=>400,'response'=>$e->getMessage());
    }
}


//Price estimation Api
public function getPriceEstimation($dataRec)
{
    $check = $this->configDetails();
    if($check){
        $end_url = '/send_payment_for_task';
      $data = [      
        "custom_field_template"=> "pricing-template",
        "access_token"=> !empty($this->access_token) ? $this->access_token : '',
        "domain_name"=> !empty($this->api_domain) ? $this->api_domain : '',
        "timezone"=> -330,  //For IST : -330
        "vendor_id"=> !empty($this->vendor_id) ? $this->vendor_id : '',
        "sareaId"=> 3,
        "is_multiple_tasks"=> 1,
        "layout_type"=> 0,
        "pickup_custom_field_template"=> "pricing-template",
            "deliveries"=> [[
                "address"=> $dataRec->address,
                "name"=> $dataRec->name,
                "latitude"=> $dataRec->latitude,
                "longitude"=> $dataRec->longitude,
                "time"=> date('Y-m-d h:i:s'),
                "phone"=> $dataRec->phone
            ]],
        "has_pickup"=> 1,
        "has_delivery"=> 1,
        "auto_assignment"=> 1,
        "user_id"=> 1,
            "pickups"=> [[
                "address"=> $dataRec->p_address,
                "name"=> $dataRec->p_name,
                "latitude"=> $dataRec->p_latitude,
                "longitude"=> $dataRec->p_longitude,
                "time"=> date('Y-m-d h:i:s'),
                "phone"=> $dataRec->p_phone,
                "email"=> $dataRec->p_email
            ]],
        "payment_method"=> 262144, //8: Cash on pickup , 32: Card payment , 262144 : Cash on delivery ,131072 : Paga 
        //"form_id"=> 2,
        //"vehicle_id"=> 4,
        //"delivery_instruction"=> "Hey,Please deliver the parcel with safety.Thanks in advance",
        //"delivery_images"=> "https://s3.ap-south-1.amazonaws.com/kwik-project/task_images/kjjX1603884709732-stripeconnect.png",
        // "is_loader_required"=> 1,
        // "loaders_amount"=> 40,
        // "loaders_count"=> 4,
        // // "is_cod_job"=> 0,
        // "parcel_amount"=> 1000
      ];
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->app_url.$end_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Content-Type: application/json"
      ));

      $result = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if (curl_errno($ch)) {
          $httpCode =  curl_error($ch);
      }
      //\Log::info('Kwick Response--');
      //\Log::info($result);
      curl_close($ch);
      $result = json_decode($result);
      return $result;
    }
    $result = (object) array('status' => 400);
    return $result;
}


public function createKwikOrder($dataRec)
{
    $end_url = '/create_task_via_vendor';
    $data = [
        "domain_name"=> !empty($this->api_domain) ? $this->api_domain : '',
        "access_token"=> !empty($this->access_token) ? $this->access_token : '',
        "vendor_id"=> !empty($this->vendor_id) ? $this->vendor_id : '',
        "sareaId"=> 3,
        "is_multiple_tasks"=> 1,
        "sareaId"=> 3,
        "timezone"=> 60,
        "has_pickup"=> 1,
        "has_delivery"=> 1,
        "pickup_delivery_relationship"=> 0,
        "layout_type"=> 0,
        "auto_assignment"=> 1,
        //"is_schedule_task"=> "1", ///For Schedule task
        "pickups"=> [[
            "address"=> $dataRec->p_address,
            "name"=> $dataRec->p_name,
            "latitude"=> $dataRec->p_latitude,
            "longitude"=> $dataRec->p_longitude,
            "time"=> Carbon::createFromFormat('Y-m-d H:i:s',Carbon::now()->addMinutes(10)),
            "phone"=> $dataRec->p_phone,
            "email"=> $dataRec->p_email
        ]],
        "deliveries"=> [[
            "address"=> $dataRec->address,
            "name"=> $dataRec->name,
            "latitude"=> $dataRec->latitude,
            "longitude"=> $dataRec->longitude,
            "time"=> Carbon::createFromFormat('Y-m-d H:i:s',Carbon::now()->addMinutes(60)),
            "phone"=> $dataRec->phone
        ]],
        "payment_method"=> 8,
        "amount"=> $dataRec->amount,
        "delivery_charge_by_buyer"=> 1,
        "delivery_charge"=> $dataRec->delivery_charge,
        "delivery_instruction"=> "Hey,Please handover parcel with safety.\nThanks",
        // "delivery_images"=> "https://s3.ap-south-1.amazonaws.com/kwik-project/task_images/wPqj1603886372690-stripeconnect.png",
        //"vehicle_id"=> 4
        ];
    // //\Log::info(json_encode($data));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->app_url.$end_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json"
        ));

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $httpCode =  curl_error($ch);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    //\Log::info('Kwick create order Response--');
    //\Log::info($result);
    return json_decode($result);
}

  public function cancelOrder($dataRec)
  {
    $this->configDetails();
    $curl = curl_init();

    $end_url = '/cancel_vendor_task';
    
    $data = [      
        "domain_name"=> !empty($this->api_domain) ? $this->api_domain : '',
        "access_token"=> !empty($this->access_token) ? $this->access_token : '',
        "vendor_id"=> !empty($this->vendor_id) ? $this->vendor_id : '',
        "job_id"=> $dataRec->pickups[0]->job_id, //fetch this id from order cancel
        "job_status"=> 9 // for cancel order
        ];
        
    curl_setopt_array($curl, array(
    CURLOPT_URL => $this->app_url.$end_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json"
        ),
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return $err;
    } else {
        return $response;
    }

  }


}