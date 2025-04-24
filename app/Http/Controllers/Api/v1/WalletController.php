<?php

namespace App\Http\Controllers\Api\v1;
use App\Models\VendorConnectedAccount;
use Auth;
use Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\{User, Transaction, Payment, PayoutOption, Vendor};
use App\Http\Controllers\Controller;

class WalletController extends Controller{
    use ApiResponser;

    # get my wallet details
    public function getFindMyWalletDetails(Request $request){
    	$user = Auth::user();
        $user = User::with('country')->find($user->id);
        $paginate = $request->has('limit') ? $request->limit : 12;
        $transactions = Transaction::where('payable_id', $user->id)->orderBy('id', 'desc')->paginate($paginate);
        foreach($transactions as $trans){
            $trans->meta = json_decode($trans->meta);
            $trans->amount = sprintf("%.2f", $trans->amount / 100);
        }
        $data = ['wallet_amount' => $user->balanceFloat, 'transactions' => $transactions];
        return $this->successResponse($data, '', 200);
    }


    # credit wallet set
    public function creditMyWallet(Request $request)
    {
        if($request->has('auth_token')){
            $user = User::whereHas('device',function  ($qu) use ($request){
                $qu->where('access_token', $request->auth_token);
            })->first();
        }
        else{
            $user = Auth::user();
        }


        if($user){
            $credit_amount = $request->amount;
            $wallet = $user->wallet;

            if ($credit_amount > 0) {
                $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> by transaction reference <b>'.$request->transaction_id.'</b>']);

                $payment = new Payment();
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
                return $this->successResponse($response, $message, 201);
            }
            else{
                return $this->errorResponse('Amount is not sufficient', 402);
            }
        }
        else{
            return $this->errorResponse('Invalid User', 402);
        }
    }

    /**
     * user verification for wallet transfer
     *
     * @return \Illuminate\Http\Response
     */
    public function walletTransferUserVerify(Request $request){
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
    public function walletTransferConfirm(Request $request){
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
                return $this->successResponse('', $message, 201);
            }else{
                return $this->errorResponse('User does not exist', 422);
            }
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode);
        }
    }

    public function payoutConnectDetails(Request $request)
    {
        
        $client = Client::with('country')->orderBy('id','asc')->first();
   
        if(isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain){
            $server_url =  "https://" . $client->custom_domain . '/';
        }else{
            $server_url =  "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . '/';
        }

        //stripe connected account details
        $codes = $this->paymentOptionArray('payout');
       
        $payout_creds = PayoutOption::whereIn('code', $codes)->where('status', 1)->get();
      
        if ($payout_creds) {
            foreach ($payout_creds as $creds) {
                $creds_arr = json_decode($creds->credentials);
                if($creds->code != 'cash'){
                    if ($creds->code == 'stripe') {
                        $creds->stripe_connect_url = '';
                        if( (isset($creds_arr->client_id)) && !empty($creds_arr->client_id) ){
                            $stripe_redirect_url = $server_url."client/verify/oauth/token/stripe";
                            $creds->stripe_connect_url = 'https://connect.stripe.com/oauth/v2/authorize?response_type=code&state='.$request->vendor.'&client_id='.$creds_arr->client_id.'&scope=read_write&redirect_uri='.$stripe_redirect_url;
                        }

                        // Check if vendor has connected account
                        $checkIfStripeAccountExists = VendorConnectedAccount::where(['vendor_id' => $request->vendor, 'payment_option_id' => $creds->id])->first();
                        if($checkIfStripeAccountExists && (!empty($checkIfStripeAccountExists->account_id))){
                            $creds->is_connected = 1;
                        }else{
                            $creds->is_connected = 0;
                        }
                        
                    }else if($creds->code == 'razorpay'){
                        $creds->is_connected = 0;
                        $vendors = Vendor::find($request->vendor);
                        if(@$vendors->vendor_bank_json->id)
                        {
                            $creds->is_connected = 1;
                        }
                    }

                    
                }
            }
            // dd($payout_creds->toArray());
        }

        // $ex_countries = ['INDIA'];

        // if((!empty($payout_creds->credentials)) && ($client_id != '') && (!in_array($client->country->name, $ex_countries))){
        //     $stripe_redirect_url = 'http://local.myorder.com/client/verify/oauth/token/stripe'; //$server_url."client/verify/oauth/token/stripe";
        //     $stripe_connect_url = 'https://connect.stripe.com/oauth/v2/authorize?response_type=code&state='.$id.'&client_id='.$client_id.'&scope=read_write&redirect_uri='.$stripe_redirect_url;
        // }else{
        //     $stripe_connect_url = route('create.custom.connected-account.stripe', $id);
        // }

        return $payout_creds;
    }
}
