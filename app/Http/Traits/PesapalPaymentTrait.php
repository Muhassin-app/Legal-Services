<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\ClientCurrency;
use App\Models\Currency;
use App\Models\PaymentOption;
use Illuminate\Support\Facades\Log;

trait PesapalPaymentTrait
{
    use ApiResponser;

    public $consumer_secret;
    public $consumer_key;
    public $test_mode;

    public function __construct()
    {
        $this->creds = PaymentOption::where('code', 'pesapal')->where('status', 1)->first();
        $this->creds_arr = json_decode($this->creds->credentials);
        $this->test_mode = $this->creds->test_mode;
        $this->consumer_key = $this->creds_arr->pesapal_consumer_key;
        $this->consumer_secret = $this->creds_arr->pesapal_consumer_secret;
    }

    public function token()
    {
        $url = $this->test_mode ? 'https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken' : 'https://pay.pesapal.com/v3/api/Auth/RequestToken';

        return Http::post($url,[
            "consumer_key" =>  $this->consumer_key,
            "consumer_secret" =>  $this->consumer_secret
        ]);
    }

    public function pinId($token)
    {
        $url = $this->test_mode ? 'https://cybqa.pesapal.com/pesapalv3/api/URLSetup/RegisterIPN' : 'https://pay.pesapal.com/v3/api/URLSetup/RegisterIPN';

        return Http::withHeaders([
            'Content-Type' =>'application/json',
            'Authorization' => 'Bearer '.$token
        ])->post($url,[
            "url" => url(''),
            "ipn_notification_type" => "GET"
        ]);
    }

    public function pinList()
    {
        $url = $this->test_mode ? 'https://cybqa.pesapal.com/pesapalv3/api/URLSetup/GetIpnList' : 'https://pay.pesapal.com/v3/api/URLSetup/GetIpnList';

        return Http::withHeaders([
            'Content-Type' =>'application/json',
            'Authorization' => 'Bearer '.$this->token()['token']
        ])->get($url);
    }

    public function pesaPalTransApi(Request $request,$description)
    {
        $token = $this->token()['token'];

        $redirct_url = $request->action ? url('/success/pesapal/?id='.auth()->id().'&come_from=app&status=200') : route('payment.pesapal.success');
        $request->action ? $request->request->add(['total_amount' => $request->amount]) : $request->total_amount;

        $url = $this->test_mode ? 'https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest' : 'https://pay.pesapal.com/v3/api/Transactions/SubmitOrderRequest';
        $name = explode(' ',auth()->user()->name);

        $currency_id = $request->action ? $request->header('currency') : session()->get('customerCurrency');
        $customerCurrency = Currency::find($currency_id);
        $currCode = $customerCurrency->iso_code;

        if(!$customerCurrency){
            $customerCurrency = ClientCurrency::with('currency')->where('is_primary', '1')->first();
            $currCode = $customerCurrency->currency->iso_code;
        }
        
        $notification_id = $this->pinId($token)['ipn_id'];
        
        if($currCode == 'UGX' && $request->total_amount < 20){
            return ['status' => 201, 'message' => 'Amount should not be less than 20'];
        }

        return Http::withHeaders([
            'Content-Type' =>'application/json',
            'Authorization' => 'Bearer '.$token
        ])->post($url,[
            "id" => $request->order_number,
            "currency" => $currCode,
            "amount" => number_format($request->total_amount,2),
            "description" => $description,
            "callback_url" => $redirct_url,
            "notification_id" => $notification_id,
            "billing_address" => [
                "email_address" => auth()->user()->email ?? '',
                "phone_number" => auth()->user()->phone_number ?? '',
                "country_code" => substr($currCode, 0, 2),
                "first_name" => $name[0] ?? '',
                "middle_name" => "",
                "last_name" => $name[1] ?? '',
                "line_1" => "",
                "line_2" => "",
                "city" => "",
                "state" => "",
                "postal_code" => "",
                "zip_code" => ""
            ]
        ])->json();
    }

    public function transactionStatus()
    {
        $url = $this->test_mode ? 'https://cybqa.pesapal.com/pesapalv3/api/Transactions/GetTransactionStatus' : 'https://pay.pesapal.com/v3/api/Transactions/GetTransactionStatus';

        return Http::withHeaders([
            'Content-Type' =>'application/json',
            'Authorization' => 'Bearer '.$this->token()['token']
        ])->get($url,[
            "orderTrackingId" => "6bc85e7b-cea7-4088-b643-deaeea5f2505"
        ]);
    }

    public function PesapalPayment(Request $request)
    {
        if ($request->payment_from == 'cart') {
            $description = "Oder-".$request->order_number;
          
        } elseif ($request->payment_from == 'pickup_delivery') {
            $description = "Pickup Delivery";
          
        } elseif ($request->payment_from == 'wallet') {
            $description = "Wallet-Credit";
            $request->merge(['order_number' => time() ]);

        } elseif ($request->payment_from == 'tip') {
            $description = "Tip Amount";
            $request->merge(['order_number' => $request->order_number.'-'.time() ]);
  
        } elseif ($request->payment_from == 'subscription') {
            $description = "subscription";    
            $request->subscription_id ? $request->merge(['order_number' => $request->subscription_id.'-'.time() ]) : $request->merge(['order_number' => $request->order_number.'-'.time() ]);
        }

       return $this->pesaPalTransApi($request,$description);
    }
}