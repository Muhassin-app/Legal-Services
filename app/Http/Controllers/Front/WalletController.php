<?php

namespace App\Http\Controllers\Front;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\FrontController;
use App\Models\{User, Transaction, ClientCurrency, Payment, PaymentOption};
use App\Http\Traits\{ApiResponser,PaymentTrait};
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\UserDataVault;

class WalletController extends FrontController
{
    use ApiResponser,PaymentTrait;
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

        $userCardExist =        UserDataVault::where(['user_id' => $auth_user->id])->count();

        return view('frontend/account/wallet',compact('public_key_yoco'))->with(['user'=>$user, 'navCategories'=>$navCategories, 'user_transactions'=>$user_transactions, 'clientCurrency'=>$clientCurrency,'userCardExist'=>$userCardExist]);
    }

    /**
     * Credit Money Into Wallet
     *
     * @return \Illuminate\Http\Response
     */
    public function creditWallet(Request $request, $domain = '')
    {
        if( (isset($request->user_id)) && (!empty($request->user_id)) ){
            $user = User::find($request->user_id);
        }elseif( (isset($request->auth_token)) && (!empty($request->auth_token)) ){
            $user = User::whereHas('device',function  ($qu) use ($request){
                $qu->where('access_token', $request->auth_token);
            })->first();
        }else{
            $user = Auth::user();
        }
        if($user){
            $credit_amount = $request->wallet_amount;
            $wallet = $user->wallet;
            if ($credit_amount > 0) {
                $saved_transaction = Transaction::where('meta', 'like', '%'.$request->transaction_id.'%')->first();
                if($saved_transaction){
                    return $this->errorResponse('Transaction has already been done', 400);
                }

                $wallet->depositFloat($credit_amount, [__("Wallet has been").' <b>Credited</b> by transaction reference <b>'.$request->transaction_id.'</b>']);
                $payment = Payment::where('transaction_id',$request->transaction_id)->first();
                if(!$payment){
                    $payment = new Payment();
                }
                $payment->date = date('Y-m-d');
                $payment->user_id = $user->id;
                $payment->transaction_id = $request->transaction_id;
                $payment->payment_option_id = $request->payment_option_id ?? null;
                $payment->balance_transaction = $credit_amount;
                $payment->type = 'wallet_topup';
                $payment->save();

                $transactions = Transaction::where('payable_id', $user->id)->get();
                $response['wallet_balance'] = $wallet->balanceFloat;
                $response['transactions'] = $transactions;
                $message = 'Wallet has been credited successfully';
                Session::put('success', $message);
                return $this->successResponse($response, $message, 200);
            }
            else{
                return $this->errorResponse('Amount is not sufficient', 400);
            }
        }
        else{
            return $this->errorResponse('Invalid User', 400);
        }
    }

    /**
     * Credit Money Into Wallet Through gateway redirection
     *
     * @return \Illuminate\Http\Response
     */
    public function postPaymentCreditWallet(Request $request, $domain = '')
    {
        if( (isset($request->auth_token)) && (!empty($request->auth_token)) ){
            return $this->creditWallet($request);
        }else{
            return $this->errorResponse('Invalid User', 402);
        }
    }

    /**
     * wallet payment options
     *
     * @return \Illuminate\Http\Response
     */
    public function paymentOptions(Request $request, $domain = ''){
        $ex_codes = ['offline_manual'];
        $code =  $this->paymentOptionArray('wallet');
        // $code = array('cod','stripe', 'dpo','azul', 'stripe_fpx', 'paystack','yoco', 'paylink', 'razorpay','simplify','square','ozow','pagarme', 'checkout','authorize_net','kongapay','ccavenue', 'cashfree','viva_wallet','easebuzz','vnpay','paytab','mvodafone','flutterwave','easypaisa','braintree','payphone','windcave','paytech','windcave','stripe_oxxo', 'mycash','stripe_ideal','userede','openpay','khalti','mtn_momo','plugnpay');

        $payment_options = PaymentOption::select('id', 'code', 'title', 'credentials')->whereIn('code', $code)->whereNotIn('code', $ex_codes)->where('status', 1)->get();
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

    /**
     * user verification for wallet transfer
     *
     * @return \Illuminate\Http\Response
     */
    public function walletTransferUserVerify(Request $request, $domain = ''){
        try{
            $user = Auth::user();
            $username = $request->username;
            $user_exists = User::select('image', 'name')->where(function($q) use($username){
                $q->where('email', $username)->orWhereRaw("CONCAT(`dial_code`, `phone_number`) = ?", $username);
            })
            ->where('status', 1)->where('id', '!=', $user->id)->first();
            if($user_exists){
                return $this->successResponse($user_exists, __('User is verified'), 201);
            }else{
                return $this->errorResponse('User does not exist', 422);
            }
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode);
        }
    }

    /**
     * transfer wallet balance to user
     *
     * @return \Illuminate\Http\Response
     */
    public function walletTransferConfirm(Request $request, $domain = ''){
        try{
            $first_user = Auth::user();
            $first_user_balance = $first_user->balanceFloat;
            $username = $request->username;
            $transfer_amount = $request->amount;

            if($transfer_amount < 0){
                return $this->errorResponse(__('Invalid Amount'), 422);
            }
            if($transfer_amount > $first_user_balance){
                return $this->errorResponse(__('Insufficient funds in wallet'), 422);
            }

            $transaction_reference = generateWalletTransactionReference();

            $second_user = User::where(function($q) use($username){
                $q->where('email', $username)->orWhereRaw("CONCAT(`dial_code`, `phone_number`) = ?", $username);
            })
            ->where('status', 1)->where('id', '!=', $first_user->id)->first();
            if($second_user){
                $first_user->transferFloat($second_user, $transfer_amount, ['Wallet has been transferred with reference <b>'.$transaction_reference.'</b>']);
                $message = __('Amount has been transferred successfully');
                Session::put('success', $message);
                return $this->successResponse('', $message, 201);
            }else{
                return $this->errorResponse('User does not exist', 422);
            }
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode);
        }
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
    //
    /**
     * this function is just for testing
     * addWalletAmount
     *
     * @return void
     */
    public function addWalletAmount(){
        $request = new Request(['wallet_amount' => 100, 'transaction_id' => rand()]);
        $this->creditWallet($request);
    }
}
