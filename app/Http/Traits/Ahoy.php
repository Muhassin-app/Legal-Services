<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\ShippingOption;
use Log;

trait Ahoy{

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

  public function configDetails()
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


  //getLocations Function Api
  public function getLocations($data)
  {
        if($this->test==1){
            $end_url = 'https://ahoydev.azure-api.net/merchant/merchantlocations?SubscriptionKey=';
        }else{
            $end_url = 'https://ahoyapis.azure-api.net/merchant/merchantlocations?SubscriptionKey='.$this->api_key;
        }
        $this->configDetails();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $end_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $headers = array();
        $headers[] = 'Subscriptionkey: '.$this->api_key;
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Ocp-Apim-Subscription-Key: '.$this->api_key;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $httpCode =  curl_error($ch);
        }
    curl_close($ch);
  return array('code'=>$httpCode,'response'=>$result);
}

public function createPreOrder($data)
{
    $this->configDetails();
    if($this->test==1){
        $end_url = 'https://ahoydev.azure-api.net/delivery/deliveryservice?Subscriptionkey='.$this->api_key;
    }else{
        $end_url = 'https://ahoyapis.azure-api.net/delivery/deliveryservice?Subscriptionkey='.$this->api_key;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $end_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $headers = array();
    $headers[] = 'Subscriptionkey: '.$this->api_key;
    $headers[] = 'Cache-Control: no-cache';
    $headers[] = 'Ocp-Apim-Subscription-Key: '.$this->api_key;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $httpCode =  curl_error($ch);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return json_decode($result);
} 


public function confirmPreOrder($data)
{
    $this->configDetails();
    if($this->test==1){
        $end_url = 'https://ahoydev.azure-api.net/delivery/deliveryrequest?Subscriptionkey='.$this->api_key;
    }else{
        $end_url = 'https://ahoyapis.azure-api.net/DeliveryIntegrationAPIProd/CreateOrderFromPreOrderFunction?Subscriptionkey='.$this->api_key;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $end_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $headers = array();
    $headers[] = 'Subscriptionkey: '.$this->api_key;
    $headers[] = 'Cache-Control: no-cache';
    $headers[] = 'Ocp-Apim-Subscription-Key: '.$this->api_key;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        $httpCode =  curl_error($ch);
    }
    curl_close($ch);
    //\Log::info($result);
    return json_decode($result);
} 


  public function createNewLocation($data)
  {
    $this->configDetails();
    if($this->test==1){
        $end_url = 'https://ahoydev.azure-api.net/merchant/newLocaion?Subscriptionkey='.$this->api_key;
    }else{
        $end_url = 'https://ahoyapis.azure-api.net/merchant/newLocaion?Subscriptionkey='.$this->api_key;
    }   
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $end_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $headers = array();
    $headers[] = 'Subscriptionkey: '.$this->api_key;
    $headers[] = 'Cache-Control: no-cache';
    $headers[] = 'Ocp-Apim-Subscription-Key: '.$this->api_key;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        $return =  curl_error($ch);
    }
    curl_close($ch);
    return array('code'=>$httpCode,'response'=>json_decode($result));
  }


  public function cancelOrder($data)
  {
    $this->configDetails();
    $curl = curl_init();
    if($this->test==1){
        $end_url = 'https://ahoydev.azure-api.net/delivery/cancel?Subscriptionkey='.$this->api_key;
    }else{
        $end_url = 'https://ahoyapis.azure-api.net/delivery/cancel?Subscriptionkey='.$this->api_key;
    }  
    curl_setopt_array($curl, array(
    CURLOPT_URL => $end_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        "apikey: {$this->api_key}",
        "cache-control: no-cache",
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


  public function setWebhookUrl($url)
  {
    $this->configDetails();
    if($this->test==1){
        $end_url = 'https://ahoydev.azure-api.net/delivery/orderwebhook?Subscriptionkey='.$this->api_key;
    }else{
        $end_url = 'https://ahoyapis.azure-api.net/delivery/orderwebhook?Subscriptionkey='.$this->api_key;
    } 
    $curl = curl_init($end_url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_URL, $end_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    # Request headers
    $headers = array(
        'Content-Type: application/json',
        'Cache-Control: no-cache',
        'Ocp-Apim-Subscription-Key: '.$this->api_key,);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    # Request body
    $request_body = '{
        "endpoint": "'.$url.'",
    }';
    curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);
    
    $resp = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    //\Log::info($err);
    curl_close($curl);
    if($httpCode == '200'){
        return response()->json(['code'=>'200','msg'=>'Webhook url is set.']);
    }else{
        return response()->json(['code'=>'400','msg'=>'Error']);
    }
  }



}