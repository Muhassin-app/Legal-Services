<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use AfricasTalking\SDK\AfricasTalking;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as FacadesLog;
use Log;
use Unifonic;
trait smsManager{

    public function mTalkz_sms($to,$message,$crendentials,$templates_id = '')
    {
            $api_url = "http://msg.mtalkz.com/V2/http-api.php";
            $to_number = substr($to, 1);
            $endpoint = $api_url.'?apikey='.$crendentials->api_key.'&senderid='.$crendentials->sender_id.'&number='.$to_number.'&message='.$message.'&format=json&template_id='.$templates_id;
            $response=$this->getGuzzle($endpoint);
            return $response;
    }



    public function mazinhost_sms($to,$message,$crendentials)
    {
        $curl = curl_init();
        $from = $crendentials->sender_id;
        $to = substr($to, 1);
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://mazinhost.com/smsv1/sms/api",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "action=send-sms&api_key=$crendentials->api_key&to=$to&from=$from&sms=$message",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

        // $api_url = " https://mazinhost.com/smsv1/sms/api";
        // $to_number = substr($to, 1);
        // $endpoint = $api_url.'?action=send-sms&api_key='.$crendentials->api_key.'&to='.$to_number.'&from='.$crendentials->sender_id.'&sms='.$message;
        // $response=$this->getGuzzle($endpoint);
        //return $endpoint;
    }

    public function unifonic($recipient,$message,$crendentials)
    {   try{

            $crendential = [
                'app_id' =>$crendentials->unifonic_app_id,
                'account_email' => $crendentials->unifonic_account_email,
                'account_password' => $crendentials->unifonic_account_password
            ];
            config(['services.unifonic' => $crendential]);
            $to_number = substr($recipient, 1);
            $respont = Unifonic::send( $to_number,  $message, $senderID = null);
            return 1;
        }catch(Exception $e) {
            return $e->getMessage();
        }

    }
    public function arkesel_sms($to,$message,$crendentials)
    {
        $to_number = substr($to, 1);
        $api_url = "https://sms.arkesel.com/sms/api?action=send-sms&";
        $endpoint = $api_url.'api_key='.$crendentials->api_key.'&to='.$to_number.'&from='.$crendentials->sender_id.'&sms='.urlencode($message);


        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);

    }
    private function postCurl($data,$token=null):object{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
        $headers = array();
        $headers[] = 'Accept: */*';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result);
    }
    private function getCurl($endpoint):object{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $headers = array();
        $headers[] = 'Accept: */*';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
        return json_decode($result);

        $curl = curl_init();

    }
    public function getGuzzle($endpoint)
    {
       // pr($endpoint);
        try{
            $client = new \GuzzleHttp\Client();
            $res = $client->get($endpoint);
            return $res;
            return $res->getStatusCode(); // 200
        }catch(Exception $e) {
            dd($e);
        }
    }

    public function africasTalking_sms($to,$message,$crendentials)
    {
        try{
            $AT       = new AfricasTalking($crendentials->sender_id, $crendentials->api_key);
            $sms      = $AT->sms();
            $result   = $sms->send([
                'to'      => $to,
                'message' => $message
            ]);
            return $result;
        }catch(\Exception $e) {
            return response()->json(['data' => $e->getMessage()]);
        }
    }

    public function vonage_sms($to, $message, $crendentials)
    {
        try{
         
            $basic  = new \Vonage\Client\Credentials\Basic($crendentials->api_key, $crendentials->secret_key);
            $client = new \Vonage\Client($basic);
            $response = $client->sms()->send(
                new \Vonage\SMS\Message\SMS($to, BRAND_NAME, $message)
            );
            FacadesLog::info([$response]);
            $resmessage = $response->current();

            if ($resmessage->getStatus() == 0) {
                return "The message was sent successfully\n";
            } else {
                return "The message failed with status: " . $resmessage->getStatus() . "\n";
            }
        }catch(\Exception $e) {
            return response()->json(['data' => $e->getMessage()]);
        }
    }


    public function sms_partner_gateway($to, $message, $crendentials)
    {
        try{
            $fields = array(
                "apiKey"=>$crendentials->api_key,
                "phoneNumbers"=>$to,
                "message"=>$message,
                "sender" => $crendentials->sender_id,
                'gamme' => 1
            );

            $api_url = "http://api.smspartner.fr/v1/send";
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            if (!empty($fields))
            {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
            }

            $result = curl_exec($curl);
            if ($result === false)
            return curl_error($curl);
            else
                curl_close($curl);

            return $result;


        }catch(\Exception $e) {
            return response()->json(['data' => $e->getMessage()]);
        }
    }


    public function ethiopia($to, $message, $crendentials){
        $to_number = substr($to, 1);
        try{
            $apiurl = 'http://197.156.70.196:9095/api/send_sms';
            $rawData = json_encode([
                    "username" => $crendentials->sms_username,
                    "password" => $crendentials->sms_password,
                    "to"=> $to_number,
                    "text"=> $message,
            ]);
            $response = Http::withBody($rawData, 'application/json')->post($apiurl);
            return $response;
        }catch(\Exception $e) {
            return response()->json(['data' => $e->getMessage()]);
        }
    }

    public function sms_country($to, $message, $crendentials)
    {
        try {
            return Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($crendentials->sms_auth_key.':'.$crendentials->sms_auth_token)
            ])->post('https://restapi.smscountry.com/v0.1/Accounts/'.$crendentials->sms_auth_key.'/SMSes', [
                "Text"=> $message,
                "Number"=> str_replace("+","",$to),
                "SenderId"=> $crendentials->sms_sender_id,
                "DRNotifyUrl"=> "https://www.domainname.com/notifyurl",
                "DRNotifyHttpMethod"=> "POST",
                "Tool"=> "API"
            ]);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage()]);
        }
        
    }

}
