<?php
namespace App\Http\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException; 
use Log;
trait TelrPaymentManager{

  public function createPaymentpage($data) 
  {
    $cart_id =  uniqid();
    $after_url = $data['payment_from']??''."/".$data['come_from'].'/'.$data['amount']."/".($data["order_number"]??0);
    $name = explode(' ', $data['customer_name'], 2);

    $formParams = [
        "ivp_method" => "create",
        "ivp_store" => (int)$this->merchant_id,
        "ivp_authkey" => $this->api_key,
        "ivp_cart" => $cart_id,
        "ivp_test" => $this->is_test,
        "ivp_amount" => $data['amount'],
        "ivp_currency" => "AED",
        "ivp_desc" => "DESCRIPTION ...", 
        "ivp_lang" => "en",
        "return_auth" => $this->url."/success/".$after_url."?cart_id=".$cart_id,
        "return_can" => $this->url."/cancel/".$after_url."?cart_id=".$cart_id,
        "return_decl" => $this->url."/declined/".$after_url."?cart_id=".$cart_id,
        "bill_fname" => $name[0]??'',
        "bill_sname" => $name[1]??'',
        "bill_addr1" => $data['billing_address']->address??"",
        "bill_addr2" => $data['billing_address']->street??"",
        "bill_city" => $data['billing_address']->city??"",
        "bill_region" => $data['billing_address']->state??"",
        "bill_zip" => $data['billing_address']->pincode??"",
        "bill_country" => $data['billing_address']->country_code??'',
        "bill_email" => $data['customer_email'],
        "bill_phone" => $data['customer_phone']
    ];
    $endPoint = "https://secure.telr.com/gateway/order.json";
    $client = new Client();
    $result = $client->post($endPoint, ['form_params' => $formParams]);
    if ($result->getStatusCode() == 200) {
        return \GuzzleHttp\json_decode($result->getBody()->getContents());
    }
  }
}
