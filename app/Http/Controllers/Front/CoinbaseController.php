<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\{UserSubscriptionController, OrderController, WalletController, FrontController};
use Auth, Log, Redirect;
use App\Models\{PaymentOption, Cart, SubscriptionPlansUser, Order, Payment, CartAddon, CartCoupon, CartProduct, CartProductPrescription, UserVendor, User,OrderProduct, OrderProductAddon}; 

class CoinbaseController extends Controller 
{
	use \App\Http\Traits\CoinbasePaymentManager;
	use \App\Http\Traits\ApiResponser;

	private $application_id;
	private $access_token;

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
        $response = $this->createCheckout1($data,$user);
        if(!is_null($response) && isset($response->id))
        {
        	return redirect('https://public.sandbox.gdax.com/checkout/'.$response->id);
        }


        if(!is_null($response) && $response->gettargetUrl() != null)
        {
            return redirect($response->gettargetUrl());
        }
        return redirect()->back()->with('error','Something went wrong, Please try again later.');
    }
}
