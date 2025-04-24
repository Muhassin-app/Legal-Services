<?php
namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\{PaymentOption, UserAddress, Vendor};
use Illuminate\Support\Facades\Auth;

trait Vivawallet{

    private $merchant_key;
    private $merchant_id;
    private $client_key;
    private $client_id;
    private $url;
    private $tokenUrl;
    private $test_mode;


    public function __construct()
   {
        $viva = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'viva_wallet')->where('status', 1)->first();
        $json = json_decode($viva->credentials);
        $this->client_key = $json->client_key;
        $this->client_id = $json->client_id;
        $this->merchant_key = $json->merchant_key;
        $this->merchant_id = $json->merchant_id;
        $this->test_mode = $viva->test_mode;
   }

    public function credentials()
    {
         $viva = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'viva_wallet')->where('status', 1)->first();
         $json = json_decode($viva->credentials);
         $this->client_key = $json->client_key;
         $this->client_id = $json->client_id;
         $this->merchant_key = $json->merchant_key;
         $this->merchant_id = $json->merchant_id;
         $this->test_mode = $viva->test_mode;
    }
   
    public function getAuthTokenViva():object{
        $this->credentials();
            if($this->test_mode=='1'){
                $this->tokenUrl = 'https://demo-accounts.vivapayments.com/connect/token';            
                }else{
                $this->tokenUrl = 'https://accounts.vivapayments.com/connect/token';
            }
        $token = base64_encode($this->client_id.':'.$this->client_key);
        $response = $this->postCurlToken($token);
        return $response;
    }


    private function postCurlToken($token):object{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,"grant_type=client_credentials");
        $headers = array();
        $headers[] = 'Accept: */*';
        $headers[] = "Authorization: Basic ${token}";
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result); 
    }


    public function createOrderPaymentLink($data = null){
        $token = $this->getAuthTokenViva();
            if($this->test_mode=='1'){
                $this->payUrl = 'https://demo-api.vivapayments.com/checkout/v2/orders';            
                }else{
                $this->payUrl = 'https://api.vivapayments.com/checkout/v2/orders';
            }

        $response=$this->postCurl($this->payUrl,$data,$token->access_token);
        return $response;
    }

    public function getTransactionDetails($tid){
        $token = $this->getAuthTokenViva();
            // if($this->test_mode=='1'){
            //     $this->api_url = 'https://demo-api.vivapayments.com/checkout/v2/transactions/'.$tid;            
            //     }else{
            //     $this->api_url = 'https://api.vivapayments.com/checkout/v2/transactions/'.$tid;
            // }
         //dd($token);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://demo-api.vivapayments.com/checkout/v2/transactions/47825180",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                "Authorization: Bearer $token->access_token"
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        echo $response;
    }


    public function verificationWebhookKey():object{
        $this->credentials();
        $token = base64_encode($this->merchant_id.':'.$this->merchant_key);
            if($this->test_mode=='1'){
                $this->api_url = 'https://demo.vivapayments.com/api/messages/config/token';            
                }else{
                $this->api_url = 'https://vivapayments.com/api/messages/config/token';
            }
        $response=$this->getCurl('','',$token);
        return $response;
    }


    private function postCurl($endpoint,$data,$token=null):object{
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_ENCODING, '');
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
                curl_setopt($ch, CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
                $headers = array();
                $headers[] = 'Accept: */*';
                $headers[] = "Authorization: Bearer ${token}";
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
             dd($result); 
    }

   

}