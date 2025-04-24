<?php
namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\{PaymentOption, UserAddress, Vendor, VerificationOption};
use Illuminate\Support\Facades\Auth;

trait AgeVerificationYoti{

    private $yoti_sdk_id;
    private $yoti_auth_key;
    private $url;
    private $test_mode;


    public function __construct()
   {
        $ageVerify= VerificationOption::where('code','yoti')->where('status', 1)->first();
        $json = json_decode($ageVerify->credentials);
        $this->yoti_sdk_id = $json->yoti_sdk_id;
        $this->yoti_auth_key = $json->yoti_auth_key;
        $this->test_mode = $ageVerify->test_mode;
   }

    public function credentials()
    {
        $ageVerify= VerificationOption::where('code','yoti')->where('status', 1)->first();
        $json = json_decode($ageVerify->credentials);
        $this->yoti_sdk_id = $json->yoti_sdk_id;
        $this->yoti_auth_key = $json->yoti_auth_key;
        $this->test_mode = $ageVerify->test_mode;
    }
   
    public function createSessionApi($data)
    {

        $this->credentials();           

            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://age.yoti.com/api/v1/sessions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "authorization: bearer ".$this->yoti_auth_key,
                "cache-control: no-cache",
                "content-type: application/json",
                "yoti-sdk-id: ".$this->yoti_sdk_id
            ),
            ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
                if ($err) {
                    //\Log::info($err);
                    return false;
                } else {
                    return $response;
                }
                
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


    

   

}