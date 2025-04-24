<?php
namespace App\Http\Traits;

use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http as FacadesHttp;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Log;

trait DataTransTrait
{
    use ApiResponser;

    public $merchant_id;
    public $password;

    public function __construct()
    {
        $this->creds = PaymentOption::where('code', 'data_trans')->where('status', 1)->first();
        if(@$this->creds->status){
            $this->creds_arr = json_decode($this->creds->credentials);
            $this->merchant_id = $this->creds_arr->merchant_id;
            $this->password = $this->creds_arr->password;
            if($this->creds->test_mode)
            {
                $this->url = 'https://api.sandbox.datatrans.com/v1/transactions';
            }else{
                $this->url = 'https://api.datatrans.com/v1/transactions';
            }
        }
    }

    public function dataTransApi(Request $request)
    {

        if(empty($this->merchant_id))
        return false;

        $redirect = route('order.dataTransuccessPage');
        $cancel_redirect = route('order.dataTransCancel');
        
        if ($request->payment_from == 'cart') {
            $refNo = "Oder-".$request->order_number;
          
        } elseif ($request->payment_from == 'pickup_delivery') {
            $refNo = "Pickup Delivery";
          
        } elseif ($request->payment_from == 'wallet') {
            $refNo = "Wallet-Credit";

        } elseif ($request->payment_from == 'tip') {
            $refNo = "Tip Amount";
  
        } elseif ($request->payment_from == 'subscription') {
            $refNo = "subscription";                
        }

        return FacadesHttp::withHeaders([
            'Authorization' => 'Basic '. base64_encode($this->merchant_id.':'.$this->password),
            'Content-Type' =>'application/json' 
        ])->post($this->url,[
            "currency" => "CHF",
            "refno" => $refNo,
            "amount" => $request->total_amount * 100,
            "redirect" => [
                "successUrl" => $redirect,
                "cancelUrl" => $cancel_redirect,
                "errorUrl" => $redirect
            ],
            "autoSettle" => true,
            "option" =>[
                "createAlias" => true
            ]
        ]);
    }
}