<?php
namespace App\Http\Traits;

use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http as FacadesHttp;
use App\Http\Traits\ApiResponser;

trait IciciPaymentTrait
{
    use ApiResponser;
    public function __construct()
    {
        $this->creds = PaymentOption::where('code', 'icici')->where('status', 1)->first();
        $this->creds_arr = json_decode($this->creds->credentials);
        $this->merchant_id = $this->creds_arr->icici_merchant_id;
        $this->sub_merchant_id = $this->creds_arr->icici_sub_merchant_id;
        $this->merchant_name = $this->creds_arr->icici_merchant_name;
        $this->sub_merchant_name = $this->creds_arr->icici_sub_merchant_name;
        $this->pem_file = $this->creds_arr->icici_merchant_encryption_file;
        $this->key_file = $this->creds_arr->icici_merchant_key_file;

        if($this->creds->test_mode)
        {
            $this->url = 'https://apibankingonesandbox.icicibank.com/api/MerchantAPI/UPI/v0/CollectPay2/'.$this->merchant_id;
            $this->tStatus_url = 'https://apibankingonesandbox.icicibank.com/api/MerchantAPI/UPI/v0/TransactionStatus1/'.$this->merchant_id;
        }else{
            $this->url = 'https://apibankingone.icicibank.com/api/MerchantAPI/UPI/v0/CollectPay2/'.$this->merchant_id;
            $this->tStatus_url = 'https://apibankingone.icicibank.com/api/MerchantAPI/UPI/v0/TransactionStatus3/'.$this->merchant_id;
        }
    }

    public function checkWhitelistedIp()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ipify.org/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 500,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "postman-token: fb7cc67c-1980-883b-b928-0308e571b1cc"
            ),
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    
        if ($err) {
            \Log::info('error '.$err);
        } else {
            \Log::info('res for ip '.$response);
        }
    }

    public function IciciPaymentApi(Request $request)
    {
        // $this->checkWhitelistedIp();
        if ($request->payment_from == 'cart') {
            $refNo = "Oder-".$request->order_number;
          
        } elseif ($request->payment_from == 'pickup_delivery') {
            $refNo = "Pickup-Delivery";
          
        } elseif ($request->payment_from == 'wallet') {
            $refNo = "Wallet-Credit";

        } elseif ($request->payment_from == 'tip') {
            $refNo = "Tip-Amount";
  
        } elseif ($request->payment_from == 'subscription') {
            $refNo = "Subscription";                
        }

       return $this->collectPayApi($request,$refNo);
    }

    public function collectPayApi(Request $request,$refNo)
    {    
        $params = [
            "payerVa" => $request->upiId,
            "amount" => number_format($request->total_amount,2),
            "note" => $refNo,
            "collectByDate" => date('d/m/Y H:i A',strtotime('+1 day')),
            "merchantId" => $this->merchant_id,
            "subMerchantId" => $this->sub_merchant_id,
            "subMerchantName" => $this->merchant_name,
            "merchantName" => $this->sub_merchant_name,
            "terminalId" => '6012',
            "merchantTranId" => date('YmdHis'),
            "billNumber" => date('YmdHis'),
        ]; 
        $fp=fopen($this->pem_file,"r");
        $pub_key_string=fread($fp,8192);
        fclose($fp);
        openssl_get_publickey($pub_key_string);
        openssl_public_encrypt(json_encode($params),$crypttext,$pub_key_string);
        // \Log::info(['crypttext' => $crypttext]);
    
        $encrypt = json_encode(base64_encode($crypttext));
        \Log::info(['encrypt' => $encrypt]);
    
        $header = [
            'Content-type:text/plain'
        ];
    
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $encrypt,
            CURLOPT_HTTPHEADER => $header
        ));
    
        $raw_response = curl_exec($curl);
    
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // \Log::info('httpcode '.$httpcode);
        curl_close($curl);
    
        $fp= fopen($this->key_file,"r");
        $priv_key=fread($fp,8192);
        fclose($fp);
        $res = openssl_get_privatekey($priv_key, "");
        \Log::info('res '.$res);

        openssl_private_decrypt(base64_decode($raw_response), $newsource, $res);
        \Log::info(['newsource' => $newsource]);
        $output = json_decode($newsource, true);
    
        return $output;
    
    }
    
    public function iciciTransactionStatusApi(Request $request)
    {
        $params = [
            "merchantId" =>  $this->merchant_id,
            "subMerchantId" =>  $this->sub_merchant_id,
            "terminalId" => "5411",
            "merchantTranId" => $request->merchantTranId,
        ];
        
        // $fp=fopen("rsa_apikey.cer","r");
        $fp=fopen($this->pem_file,"r");
        $pub_key_string=fread($fp,8192);
        fclose($fp);
        openssl_get_publickey($pub_key_string);
        openssl_public_encrypt(json_encode($params),$crypttext,$pub_key_string);
        
        $encrypt = json_encode(base64_encode($crypttext));
    
        $header = [
            'Content-type:text/plain'
        ];
            
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->tStatus_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $encrypt,
            CURLOPT_HTTPHEADER => $header
        ));
        
        $raw_response = curl_exec($curl);
    
        curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
        curl_close($curl);
        $fp= fopen($this->key_file,"r");
        $priv_key=fread($fp,8192);
        fclose($fp);
        $res = openssl_get_privatekey($priv_key, "");
        openssl_private_decrypt(base64_decode($raw_response), $newsource, $res);
    
        $output = json_decode($newsource, true);
        return $output;
    
    }
    
    public function callbackUrl()
    {
        $data = file_get_contents('php://input');
        $fp= fopen($this->key_file,"r");
        $priv_key=fread($fp,8192);
        fclose($fp);
        $res = openssl_get_privatekey($priv_key, "");
        openssl_private_decrypt(base64_decode($data), $newsource, $res);
        
        $output = json_decode($newsource, true);  
        return $output;
    }
}