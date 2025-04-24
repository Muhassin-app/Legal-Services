<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\{UserSubscriptionController, OrderController, WalletController, FrontController};
use Auth, Log, Redirect;
use App\Models\{PaymentOption, Client, ClientPreference, Order, OrderProduct, EmailTemplate, Cart, CartAddon, OrderProductPrescription, CartProduct, CartDeliveryFee, User, Product, OrderProductAddon, Payment, ClientCurrency, OrderVendor, UserAddress, Vendor, CartCoupon, CartProductPrescription, LoyaltyCard, NotificationTemplate, VendorOrderStatus,OrderTax, SubscriptionInvoicesUser, UserDevice, UserVendor, Transaction};

class UPayController extends FrontController
{
    use \App\Http\Traits\UPayPaymentManager;
	use \App\Http\Traits\ApiResponser;
	public function __construct()
  	{
		$this->upay_creds = PaymentOption::select('credentials','test_mode')->where('code', 'upay')->where('status', 1)->first();
        if(@$this->upay_creds && !empty($this->upay_creds->credentials)){
	    $this->creds_arr = json_decode($this->upay_creds->credentials);
	    $this->uidd = $this->creds_arr->uuid_key ?? '';
	    $this->aes_key = $this->creds_arr->aes_key ?? '';
	    $this->endpoint = $this->upay_creds->test_mode ? "https://ubotpsentry-tst1.outsystemsenterprise.com/UPAY" : "https://sith.unionbankph.com/UPAY";
        }
	}
	public function beforePayment(Request $request)
    {
    	$data = $request->all();
        $data['come_from'] = 'app';
        if($request->isMethod('post'))
        {
            $data['come_from'] = 'web';
        }else{
            $user = User::where('auth_token', $request->auth_token)->first();
            Auth::login($user);
        }
        $user = Auth::user();
        $data['amount'] = $this->getDollarCompareAmount($request->amount);
        $references = [];
        $ref_data = [];
        if($request->payment_from == 'cart'){
            
        }
        elseif($request->payment_from == 'wallet'){
            $ref_data['Id'] = $user->id.'';
            $ref_data['Name'] = 'Wallet Checkout';
            array_push($references, $ref_data);
        }
        elseif($request->payment_from == 'tip'){
            $ref_data['Name'] = 'Tip Checkout';
            if($request->has('order_number')){
                $ref_data['Id'] = $request->order_number;
            }
            array_push($references, $ref_data);
        }
        elseif($request->payment_from == 'subscription'){
            $ref_data['Name'] = 'Subscription Checkout';
            if($request->has('subscription_id')){
                $ref_data['Id'] = $request->subscription_id;
            }
            array_push($references, $ref_data);
        }
        $formData = [
          'Amt' => $data['amount'],
          'full_name' => 'Sujata Mehta',
          'Email' => $user->email??'',
          'Mobile' => $user->phone_number??'',
          'Redir' => $data['redirect_url']??'http://192.168.1.3:8060',
          'References' => $references
        ];
        $info = json_encode($formData);
        return view('frontend.payment_gatway.
            ')->with(['data' => $info,'key'=>$this->aes_key,'endpoint'=>$this->endpoint,'uidd'=>$this->uidd]);
    }
}
