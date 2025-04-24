<?php

namespace App\Http\Controllers\Client;

use Auth;
use Session;
use Redirect;
use Carbon\Carbon;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use Omnipay\Common\CreditCard;
use App\Models\{PaymentOption, Client, ClientCurrency, SubscriptionPlansVendor, PayoutOption, UserVendor, Vendor, VendorConnectedAccount};
use App\Http\Traits\ApiResponser;
use App\Http\Traits\ToasterResponser;
use App\Http\Controllers\Client\BaseController;
use Illuminate\Support\Facades\Validator;
class StripeGatewayController extends BaseController{

    use ApiResponser;
    use ToasterResponser;
    public $gateway;
    public $currency;
    public $currency_id;
    public $payout_secret_key;
    public $payout_client_id;

    public function __construct()
    {
        $stripe_creds = PaymentOption::select('credentials')->where('code', 'stripe')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $this->gateway = Omnipay::create('Stripe');
        $this->gateway->setApiKey($api_key);
        $this->gateway->setTestMode(true); //set it to 'false' when go live

        $payout_creds = PayoutOption::select('credentials')->where('code', 'stripe')->where('status', 1)->first();
        if($payout_creds){
            $payout_creds_arr = json_decode($payout_creds->credentials);
            $this->payout_secret_key = (isset($payout_creds_arr->secret_key)) ? $payout_creds_arr->secret_key : '';
            $this->payout_client_id = (isset($payout_creds_arr->client_id)) ? $payout_creds_arr->client_id : '';
        }

        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : '';
        $this->currency_id = (isset($primaryCurrency->currency_id)) ? $primaryCurrency->currency_id : '';
    }

    public function subscriptionPaymentViaStripe(request $request)
    {
        try{
            $user = Auth::user();
            $token = $request->stripe_token;
            $amount = getDollarCompareAmount($request->amount, $this->currency_id);
            $plan = SubscriptionPlansVendor::where('slug',$request->subscription_id)->firstOrFail();
            $request->request->add(['user_id' => $user->id]); //add request
            $saved_payment_method = $this->getSavedVendorPaymentMethod($request);
            if(!$saved_payment_method){
                $customerResponse = $this->gateway->createCustomer(array(
                    'description' => 'Creating Customer for subscription',
                    'email' => $request->email,
                    'source' => $token
                ))->send();
                $customer_id = $customerResponse->getCustomerReference();
                if($customer_id){
                    $request->request->set('customerReference', $customer_id);
                    $save_payment_method_response = $this->saveVendorPaymentMethod($request);
                }
            }
            else{
                $customer_id = $saved_payment_method->customerReference;
            }

            // $subscriptionResponse = $this->gateway->createSubscription(array(
            //     "customerReference" => $customer_id,
            //     'plan' => 'Basic Plan',
            // ))->send();
            $authorizeResponse = $this->gateway->authorize([
                'amount' => $amount,
                'currency' => $this->currency,
                'description' => 'This is a subscription purchase transaction.',
                'customerReference' => $customer_id
            ])->send();
            if ($authorizeResponse->isSuccessful()) {
                $purchaseResponse = $this->gateway->purchase([
                    'currency' => $this->currency,
                    'amount' => $amount,
                    'metadata' => ['user_id'=>$user->id, 'vendor_id' => $request->vendor_id, 'plan_id' => $plan->id],
                    'description' => 'This is a subscription purchase transaction.',
                    'customerReference' => $customer_id
                ])->send();
                if ($purchaseResponse->isSuccessful()) {
                    return $this->successResponse($purchaseResponse->getData());
                }
                else {
                    return $this->errorResponse($purchaseResponse->getMessage(), 400);
                }
            }
            else {
                return $this->errorResponse($authorizeResponse->getMessage(), 400);
            }
        }catch(\Exception $ex){
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function createCustomConnectedAccount(request $request, $domain='', $vid=0)
    {
        try{
            $user = Auth::user();
            if($vid > 0){
                $checkIfExists = VendorConnectedAccount::where('vendor_id', $vid)->first();
                if($checkIfExists){
                    $msg = __('You are already connected to stripe');
                    $toaster = $this->errorToaster('Error', $msg);
                }
                else{
                    $client = Client::with('country')->orderBy('id','asc')->first();
                    $vendor = Vendor::where("id", $vid)->where('status', 1)->first();
                    $stripe = new \Stripe\StripeClient($this->payout_secret_key);
                    $response = $stripe->accounts->create([
                        'country' => $client->country->code,
                        'type' => 'custom',
                        'email' => $vendor->email,
                        'capabilities' => [
                            'card_payments' => ['requested' => true],
                            'transfers' => ['requested' => true],
                        ],
                        // 'tos_acceptance' => [
                        //     'date' => Carbon::now()->timestamp,
                        //     'ip' => getUserIP()
                        // ]
                    ]);

                    $connectedAccountRetrieve = $stripe->accounts->retrieve(
                        $response->id,
                        []
                    );

                    $stripe->accounts->update(
                        $response->id,
                        [
                            'tos_acceptance' => [
                                'date' => Carbon::now()->timestamp,
                                'ip' => getUserIP()
                            ],
                            'business_type' => 'individual',
                            'business_profile' => [
                                // 'mcc' => null,
                                'name' => $vendor->name,
                                // 'support_address' => null,
                                'support_email' => $vendor->email,
                                'support_phone' => $vendor->phone_no,
                                'support_url' => $vendor->website,
                            ],
                            'individual'=> [
                                'first_name'=> $user->name,
                                'last_name'=> $user->name,
                                'dob' => [
                                  'day'=> 1,
                                  'month'=> 10,
                                  'year'=> 1990
                                ],
                                'email'=> $user->email,
                                'phone'=> $user->phone_number,
                                'address' => [
                                   'city'=> 'city',
                                   'country'=> 'IN',
                                   'line1'=> '1',
                                   'line2'=> 'Street Rd',
                                   'postal_code'=> 'XXX XXX'
                                ]       
                            ],
                        ]
                    );

                    // $stripe->accounts->update(
                    //     $response->id,
                    //     [
                    //         'tos_acceptance' => [
                    //             'date' => Carbon::now()->timestamp,
                    //             'ip' => getUserIP()
                    //         ],
                    //         'business_type' => 'company',
                    //         'business_profile' => [
                    //             'mcc' => 5814,
                    //             'name' => $vendor->name,
                    //             // 'support_address' => null,
                    //             'support_email' => $vendor->email,
                    //             'support_phone' => $vendor->phone_no,
                    //             'support_url' => $vendor->website,
                    //         ],
                    //         'company'=> [
                    //             'name'=> $vendor->name,
                    //             'phone'=> $vendor->phone_no,
                    //             'address' => [
                    //                'city'=> 'city',
                    //                'country'=> 'IN',
                    //                'line1'=> '1',
                    //                'line2'=> 'Street Rd',
                    //                'postal_code'=> 'XXX XXX'
                    //             ]       
                    //         ],
                    //     ]
                    // );

                    // $account_link = $stripe->accountLinks->create([
                    //     'account' => $response->id,
                    //     'refresh_url' => url('client/vendor/payout/'.$vid),
                    //     'return_url' => url('client/vendor/payout/'.$vid),
                    //     'type' => 'custom_account_verification',
                    //     'collect' => 'eventually_due'
                    // ]);

                    // Access the connected account id in the response
                    $connected_account_id = $response->id;
                    $connectdAccount = new VendorConnectedAccount();
                    $connectdAccount->user_id = $user->id;
                    $connectdAccount->vendor_id = $vid;
                    $connectdAccount->account_id = $connected_account_id;
                    $connectdAccount->payment_option_id = 2;
                    $connectdAccount->status = 1;
                    $connectdAccount->save();

                    $msg = __('Stripe connect has been enabled successfully');
                    $toaster = $this->successToaster(__('Success'), $msg);
                }
            }else{
                $msg = __('Invalid Data');
                $toaster = $this->errorToaster(__('Errors'), $msg);
            }
        }
        catch(Exception $ex){
            $toaster = $this->errorToaster(__('Errors'), $ex->getMessage());
        }

        return Redirect::To(route('vendor.payout', $vid))->with('toaster', $toaster);
    }

    public function verifyOAuthToken(request $request)
    {
        try{
            $user = Auth::user();
            $vendor = $request->state;
            if($request->has('code')){
                $code = $request->code;
                $checkIfExists = VendorConnectedAccount::where('vendor_id', $vendor)->first();
                if($vendor > 0){
                    if($checkIfExists){
                        $msg = __('You are already connected to stripe');
                        $toaster = $this->errorToaster('Error', $msg);
                    }
                    else{
                        // Complete the connection and get the account ID
                        \Stripe\Stripe::setApiKey($this->payout_secret_key);
                        $response = \Stripe\OAuth::token([
                            'grant_type' => 'authorization_code',
                            'code' => $code,
                        ]);

                        // Access the connected account id in the response
                        $connected_account_id = $response->stripe_user_id;

                        $connectdAccount = new VendorConnectedAccount();
                        $connectdAccount->user_id = $user->id;
                        $connectdAccount->vendor_id = $vendor;
                        $connectdAccount->account_id = $connected_account_id;
                        $connectdAccount->payment_option_id = 2;
                        $connectdAccount->status = 1;
                        $connectdAccount->save();

                        $msg = __('Stripe connect has been enabled successfully');
                        $toaster = $this->successToaster(__('Success'), $msg);
                    }
                }else{
                    $msg = __('Invalid Data');
                    $toaster = $this->errorToaster(__('Errors'), $msg);
                }
            }
            else{
                $msg = __('Stripe connect has been declined');
                $toaster = $this->errorToaster(__('Errors'), $msg);
            }
        }
        catch(Exception $ex){
            $toaster = $this->errorToaster(__('Errors'), $ex->getMessage());
        }

        return Redirect::To(route('vendor.payout', $vendor))->with('toaster', $toaster);
    }

    public function vendorPayoutViaStripe(request $request, $domain='')
    {
        try{
            $user = Auth::user();
            $connected_account = VendorConnectedAccount::where('vendor_id', $request->vendor_id)->first();
            if($connected_account && (!empty($connected_account->account_id))){

                // $stripe = new \Stripe\StripeClient($this->payout_secret_key);
                // $payment_intent = $stripe->paymentIntents->create([
                //     'payment_method_types' => ['card'],
                //     'amount' => $request->amount * 100,
                //     'currency' => 'INR',
                //     'transfer_data' => [
                //       'destination' => $connected_account->account_id,
                //     ],
                // ]);
                // $charge_id = $payment_intent->id;

                // $response = $stripe->transfers->create([
                //     'amount' => $request->amount * 100,
                //     'currency' => 'USD', //$this->currency
                //     // 'source_transaction' => $charge_id,
                //     'destination' => $connected_account->account_id,
                //     // 'transfer_group' => $charge_id,
                // ]);
                
                $amount = getDollarCompareAmount($request->amount, $this->currency_id);
                \Stripe\Stripe::setApiKey($this->payout_secret_key);

                // Create a PaymentIntent:
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amount * 100,
                    'currency' => $this->currency,
                    'payment_method_types' => ['card'],
                    'on_behalf_of' => $connected_account->account_id,
                    'transfer_group' => 'vendor_payout',
                ]);
                
                // Create a Transfer to a connected account (later):
                $transfer = \Stripe\Transfer::create([
                    'amount' => $amount * 100,
                    'currency' => $this->currency,
                    'destination' => $connected_account->account_id,
                    'transfer_group' => 'vendor_payout',
                ]);
                $transactionReference = $transfer->balance_transaction;
                return $this->successResponse($transactionReference, 'Payout is completed successfully', 200);

            }else{
                return $this->errorResponse('You are not connected to stripe', 400);
            }
        }catch(\Exception $ex){
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }
}
