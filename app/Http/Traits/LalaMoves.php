<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\ShippingOption;
use App\Models\Webhook;


trait LalaMoves{

  private $api_key;
  private $secret_key;
  private $base_url;
  private $region;
  private $locale_key;
  private $startTime;
  private $service_type;


/**
 * LalaMoves integrations Details
 * Req Api Key
 * Req Secret Key
 * Country Region Keys : Where we are using our Service
 * Country Locale Keys : Local Language type en_MY, ms_MY
 */

  // public function __construct()
  // {
  //   $simp_creds = ShippingOption::select('credentials', 'test_mode')->where('code', 'lalamove')->where('status', 1)->first();
  //   $creds_arr = json_decode($simp_creds->credentials);
  //   $this->api_key = $creds_arr->api_key??'';
  //   $this->secret_key = $creds_arr->secret_key ?? '';
  //   $this->base_url = (($simp_creds->test_mode=='1')?'https://rest.sandbox.lalamove.com':'https://rest.lalamove.com'); //Live url - https://rest.lalamove.com
  //   $this->region = $creds_arr->country_region ?? ''; // Malaysia regions ----  MY_JHB, MY_KUL, MY_NTL
  //   $this->locale_key = $creds_arr->locale_key ?? ''; // Malaysia region locale type en_MY, ms_MY
  //   $this->service_type = $creds_arr->service_type ?? ''; // Malaysia region ServiceType MOTORCYCLE, WALKER , VAN , 4x4 , TRUCK330, TRUCK550 
  // }

  public function configDetails()
  {
    $simp_creds = ShippingOption::select('credentials', 'test_mode','status')->where('code', 'lalamove')->where('status', 1)->first();
    if(@$simp_creds->status){
        $creds_arr = json_decode($simp_creds->credentials);
        $this->api_key = $creds_arr->api_key??'';
        $this->secret_key = $creds_arr->secret_key ?? '';
        $this->base_url = (($simp_creds->test_mode=='1')?'https://rest.sandbox.lalamove.com':'https://rest.lalamove.com'); //Live url - https://rest.lalamove.com
        $this->region = $creds_arr->country_region ?? ''; // Malaysia regions ----  MY_JHB, MY_KUL, MY_NTL
        $this->locale_key = $creds_arr->locale_key ?? ''; // Malaysia region locale type en_MY, ms_MY
        $this->service_type = $creds_arr->service_type ?? ''; // Malaysia region ServiceType MOTORCYCLE, WALKER , VAN , 4x4 , TRUCK330, TRUCK550 
    }
  }




  //Token Array function
  public function token($method = NULL,$path = NULL,$body = NULL)
  {
    $time = time() * 1000;
    $rawSignature = "{$time}\r\n{$method}\r\n{$path}\r\n\r\n{$body}";
    $signature = hash_hmac("sha256", $rawSignature, $this->secret_key);
    $token = $this->api_key.':'.$time.':'.$signature;
    $this->startTime = microtime(true);
    return $token;
  }





  //Quotation Body Array
  public function getQuotationBody($data)
  {
    //Contact person at pick up point 0 eg - Vendor Details 
    $requesters =array(
      "name"=> $data->vendor_name,
      "phone" => $data->vendor_contact
    );

    
  //Pickup address
    $regionDetail =array(
      "displayString"=> $data->pick_address,
      "market" => $this->region
    );
    

 //Pickup Location
    $location =array(
      "lat"=> $data->pick_lat,
      "lng" => $data->pick_lng,
    );
 //Pickup Stop
    $addresses =array(
       $this->locale_key => $regionDetail,
    );

    //Pickup Stop
    $stops[0] =array(
      "location"=> $location,
      "addresses" => $addresses
    );




    //Drop off Location
    $location_to =array(
      "lat"=> $data->drop_lat,
      "lng" => $data->drop_lng,
    );

    //Drop off address
    $regionDetail_to =array(
      "displayString"=> $data->drop_address,
      "market" => $this->region
    );

    $addresses_to =array(
      $this->locale_key => $regionDetail_to,
   );

    //Dropoff Stop
    $stops[1] =array(
      "location"=> $location_to,
      "addresses" => $addresses_to
    );


    //The name of the contact person -- user Deails
    $toContact =array(
      "name"=> $data->user_name,
      "phone" => $data->user_phone,
    );


    $deliveries[] = array(
      'toStop'=>1,
      'toContact'=>$toContact,
      'remarks' => $data->remarks
    );


    $bodyArr = array(
      'serviceType' => $this->service_type,
      'specialRequests' => [ ], //optional parameters "COD", "HELP_BUY", "LALABAG"
      'requesterContact' => $requesters,
      'stops' => $stops,
      'deliveries' =>$deliveries
    );
    if(isset($data->schedule_time)){
      $bodyArr['scheduleAt'] = $data->schedule_time; 
    }
    //Quotation json array build 
    return $body = json_encode($bodyArr);
  }





  //Quotation Function Api
  public function getQuotations($data)
  {
    $this->configDetails();
    
    $method = 'POST';
    $path = '/v2/quotations';
    $body = $this->getQuotationBody($data);
    $token = $this->token($method,$path,$body);
  
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->base_url.$path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 3,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HEADER => false, // Enable this option if you want to see what headers Lalamove API returning in response
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $body,
      CURLOPT_HTTPHEADER => array(
          "Content-type: application/json; charset=utf-8",
          "Authorization: hmac ".$token, // A unique Signature Hash has to be generated for EVERY API call at the time of making such call.
          "Accept: application/json",
          "X-LLM-Market: {$this->region}" // Please note to which city are you trying to make API call
      ),
  ));
  
  $response = curl_exec($curl);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  return array('code'=>$httpCode,'response'=>$response);
}

//Order Array Body
public function getOrderBody($data)
{
  $quotedFee = array(
        'amount' => $data->totalFee,
        'currency' => $data->totalFeeCurrency
    );
  
  $quotedTotalFee = array(
    'quotedTotalFee' => $quotedFee,
    'sms' => true, //Send delivery updates via SMS to THE recipient, or the recipient of the LAST STOP for multi-stop orders once the order has been picked-up by the driver. default 'true'
    'pod' => false, //Request driver to carry out "Proof Of Delivery" for all stops in the order. Default to false. See Proof Of Delivery for details.
  );

  return json_encode($quotedTotalFee);

  }


public function placeOrders($data,$quotation,$order_id = '')
{
    $this->configDetails();
    $method = 'POST';
    $path = '/v2/orders';
    $bodyQut = $this->getQuotationBody($data);
    $bodyOrder = $this->getOrderBody($quotation);
    $body=json_encode(array_merge(json_decode($bodyQut, true),json_decode($bodyOrder, true)));
    $token = $this->token($method,$path,$body);

  
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $this->base_url.$path,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 3,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => false, // Enable this option if you want to see what headers Lalamove API returning in response
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => array(
        "Content-type: application/json; charset=utf-8",
        "Authorization: hmac ".$token, // A unique Signature Hash has to be generated for EVERY API call at the time of making such call.
        //"Accept: application/json",
        "X-LLM-Market: $this->region" // Please note to which city are you trying to make API call
    ),
));

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// echo "Total elapsed http request/response time in milliseconds: ".floor((microtime(true) - $this->startTime)*1000)."\r\n";
//\Log::info('Place Order response Mail');
//\Log::info('orderRef = '.json_decode($response)->orderRef);
//\Log::info($response);
//\Log::info('End Place Order response Mail');
$resp = json_decode($response);
Webhook::create(['tracking_order_id'=>$order_id,'response'=>$response]);
if($resp->orderRef){
  return $resp;
 }
 return false;




// Response
// {
//   "orderRef": "193400800238",
//   "totalFee": "43.60",
//   "totalFeeCurrency": "MYR",
//   "distance": {
//       "text": "51.2 km",
//       "value": 51222
//   }
// }
  
} 

public function testing()
{

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://rest.sandbox.lalamove.com/v2/orders',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{"serviceType":"MOTORCYCLE","specialRequests":[],"requesterContact":{"name":"General Electric","phone":"8965745236"},"stops":[{"location":{"lat":"3.115825684565","lng":"101.666775521484"},"addresses":{"en_MY":{"displayString":"Malaysia","market":"MY_KUL"}}},{"location":{"lat":"3.144354220271","lng":"101.710811322266"},"addresses":{"en_MY":{"displayString":"g107, Jln Bukit Bintang, Bukit Bintang, 55100 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur, Malaysia","market":"MY_KUL"}}}],"deliveries":[{"toStop":1,"toContact":{"name":"pankaj","phone":"7520822619"},"remarks":"Delivery vendor message remarks"}],"quotedTotalFee":{"amount":"11.00","currency":"MYR"},"sms":false,"pod":false}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: hmac pk_test_11c917c792586a46bef122660d6e04b9:1640684549815:5aa5d38a69dad05defa0943d4308559d30f8fdc20651bf85f98e9acaa60db64e',
    'X-LLM-market: MY_KUL'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
return $response;
}


public function orderDetails($orderReff)
  {
  $this->configDetails();
  $method = 'GET';
  $path = '/v2/orders/'.$orderReff;
  $token = $this->token($method,$path);
  $curl = curl_init();
  curl_setopt_array($curl, array(
  CURLOPT_URL => $this->base_url.$path,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => $method,
  CURLOPT_HTTPHEADER => array(
    "Content-type: application/json; charset=utf-8",
    "Authorization: hmac ".$token,
    "X-LLM-Market: {$this->region}"
    ),
  ));

  $response = curl_exec($curl);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);

  return array('code'=>$httpCode,'response'=>$response);

// Response
//   {
//     "driverId": "",
//     "shareLink": "https://share.sandbox.lalamove.com?MY100211216170231071410010085148835&lang=en_MY&sign=dd4390429b25b1bd6e24eaf8c1bef13b&source=api_wrapper",
//     "status": "ASSIGNING_DRIVER",
//     "pod": null,
//     "price": {
//         "amount": "43.60",
//         "currency": "MYR"
//     },
//     "distance": {
//         "text": "51.2 km",
//         "value": 51222
//     }
// }


  }

  public function orderDriverDetail($orderid,$driverId)
  {

    $this->configDetails();

    $method = 'GET';
    $path = '/v2/orders/'.$orderid.'/drivers/'.$driverId;
    $token = $this->token($method,$path);
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->base_url.$path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => array(
        "Content-type: application/json; charset=utf-8",
        "Authorization: hmac ".$token,
        "X-LLM-Market: {$this->region}"
      ),
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
   return array('code'=>$httpCode,'response'=>$response);


  }





  public function orderCancel($orderReff)
  {
    $this->configDetails();

    $method = 'GET';
    $path = '/v2/orders/'.$orderReff.'/cancel';
    $token = $this->token($method,$path);

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->base_url.$path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => array(
        "Content-type: application/json; charset=utf-8",
        "Authorization: hmac ".$token,
        "X-LLM-Market: {$this->region}"
      ),
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return array('code'=>$httpCode,'response'=>$response);


  }






}