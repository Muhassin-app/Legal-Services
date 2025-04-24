<?php

namespace App\Http\Controllers\Front;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\FrontController;
use App\Models\{User, Transaction, ClientCurrency, Payment, PaymentOption};
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Session;

class TokenController extends FrontController
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $langId = Session::get('customerLanguage');
        $currency_id = Session::get('customerCurrency');
        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
        $user = User::with('country')->find(Auth::user()->id);
        $navCategories = $this->categoryNav($langId);
        $auth_user = Auth::user();
        $user_transactions = Transaction::where('payable_id', $auth_user->id)->orderBy('id', 'desc')->paginate(10);
        $public_key_yoco=PaymentOption::where('code','yoco')->first();
        if($public_key_yoco){

            $public_key_yoco= $public_key_yoco->credentials??'';
            $public_key_yoco= json_decode($public_key_yoco);
            $public_key_yoco= $public_key_yoco->public_key??'';
        }

        $additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency']);
        return view('frontend/account/token',compact('public_key_yoco'))->with(['user'=>$user, 'navCategories'=>$navCategories, 'user_transactions'=>$user_transactions, 'clientCurrency'=>$clientCurrency, 'additionalPreference' => $additionalPreference]);
    }

    /**
     * wallet payment options
     *
     * @return \Illuminate\Http\Response
     */
    public function paymentOptions(Request $request, $domain = ''){
        $ex_codes = ['cod','offline_manual'];
        $payment_options = PaymentOption::select('id', 'code', 'title', 'credentials')->whereNotIn('code', $ex_codes)->where('status', 1)->get();
        foreach ($payment_options as $k => $payment_option) {
            if( (!empty($payment_option->credentials)) ){
                $payment_option->slug = strtolower(str_replace(' ', '_', $payment_option->title));
                if($payment_option->code == 'stripe'){
                    $payment_option->title = 'Credit/Debit Card (Stripe)';
                }elseif($payment_option->code == 'kongapay'){
                    $payment_option->title = 'Pay Now';
                }elseif($payment_option->code == 'mvodafone'){
                    $payment_option->title = 'Vodafone M-PAiSA';
                }elseif($payment_option->code == 'mobbex'){
                    $payment_option->title = __('Mobbex');
                }elseif($payment_option->code == 'offline_manual'){
                    $json = json_decode($payment_option->credentials);
                    $payment_option->title = $json->manule_payment_title;
                }elseif($payment_option->code == 'mycash'){
                    $payment_option->title = __('Digicel MyCash');
                }elseif($payment_option->code == 'windcave'){
                    $payment_option->title = __('Windcave (Debit/Credit card)');
                }elseif($payment_option->code == 'stripe_ideal'){
                    $payment_option->title = __('iDEAL');
                }elseif($payment_option->code == 'authorize_net'){
                    $payment_option->title = __('Credit/Debit Card');
                }elseif($payment_option->code == 'obo'){
                    $payment_option->title = __("Momo, Airtel Money by O'Pay");
                }
                $payment_option->title = __($payment_option->title);
                unset($payment_option->credentials);
            }
            else{
                unset($payment_options[$k]);
            }
        }
        return $this->successResponse($payment_options);
    }

    public function refreshWalletbalance(Request $request, $domain='', $id=''){
        if(!empty($id)){
            $user = User::find($id);
            if($user){
                if($user->wallet){
                    $user->wallet->refreshBalance();
                }
            }
        }

        echo '<pre>';
        echo 'Successfully Done';
        echo '</pre>';
    }
}
