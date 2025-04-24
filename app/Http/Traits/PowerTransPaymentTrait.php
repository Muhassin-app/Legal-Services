<?php
namespace App\Http\Traits;

use App\Http\Controllers\Front\PowerTransPaymentController;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\PaymentOption;
use Illuminate\Support\Str;

trait PowerTransPaymentTrait
{
    use ApiResponser;

    public $powertrans_id;
    public $powertrans_password;
    public $test_mode;

    public function __construct()
    {
        $this->creds = PaymentOption::where('code', 'powertrans')->where('status', 1)->first();
        $this->creds_arr = json_decode($this->creds->credentials);
        $this->test_mode = $this->creds->test_mode;
        $this->powertrans_id = $this->creds_arr->powertrans_id;
        $this->powertrans_password = $this->creds_arr->powertrans_password;
    }

    public function powerTransApi(Request $request,$description)
    {
        $redirect_url = $request->action ? url('/success/powertrans?id='.auth()->id().'&come_from=app') : route('payment.powertrans.success');
        $request->action ? $request->request->add([
            'total_amount' => $request->amount,
            'card_number' => $request->cno,
            'cvv' => $request->cv,
            'exp_date' => $request->dt
        ]) : '';
        
        $url = $this->test_mode ? 'https://staging.ptranz.com/api/auth' : 'https://tbd.ptranz.com/api/auth';
        $name = explode(' ',auth()->user()->name);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'PowerTranz-PowerTranzId' => $this->powertrans_id, 
            'PowerTranz-PowerTranzPassword' => $this->powertrans_password
        ])->post($url,[
            "TransactionIdentifier" => Str::uuid(),
            "TotalAmount" => $request->total_amount,
            "CurrencyCode" => "840",
            "ThreeDSecure" => false,
            "Source" => [
                "CardPan" => $request->card_number,
                "CardCvv" => $request->cvv,
                "CardExpiration" => $request->exp_date,
                "CardholderName" => auth()->user()->name
            ],
            "OrderIdentifier" => Str::uuid(),
            "BillingAddress" => [
                "FirstName" => $name[0] ?? '',
                "LastName" => $name[1] ?? '',
                "Line1" => "",
                "Line2" => "",
                "City" => "",
                "State" => "",
                "PostalCode" => "",
                "CountryCode" => "840",
                "EmailAddress" => auth()->user()->email ?? '',
                "PhoneNumber" => auth()->user()->phone_number ?? ''
            ],
            "AddressMatch"=> false
        ])->json();
        
        $request->action ? $response['redirect_url'] = $redirect_url.'&TransactionIdentifier='.$response['TransactionIdentifier'] : $response['redirect_url'] = $redirect_url;
        return $response;
    }

    public function powerTransPayment(Request $request)
    {
        $request->payment_from ?? $request->request->add([
            'payment_from' => $request->action,
        ]);

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
            $request->action ? $request->merge(['order_number' => $request->subscription_id ]) : '';
        }

       return $this->powerTransApi($request,$description);
    }
}