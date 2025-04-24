<?php

namespace App\Http\Controllers\Front;

use Log;
use DB;
use Auth;
use Rede;
use Session;
use Omnipay\Omnipay;
use Illuminate\Http\Request;
use Omnipay\Common\CreditCard;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\WalletController;
use App\Http\Controllers\Front\UserSubscriptionController;
use App\Http\Controllers\Front\PickupDeliveryController;
use App\Models\{User, UserVendor, CaregoryKycDoc,Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, CartDeliveryFee, Payment, PaymentOption, Client, ClientPreference, ClientCurrency, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, SubscriptionPlansUser, Transaction, UserAddress, UserSavedPaymentMethods, Webhook};
use Illuminate\Support\Facades\Crypt;
use function App\Notifications\via;
class UseRedePaymentController extends FrontController
{
    //https://github.com/DevelopersRede/erede-php   ###documentations
    use ApiResponser;

    public $REDE_PV;
    public $REDE_TOKEN;
    public $testmode;
    public $store;

    public function __construct()
    {
        $Rede = PaymentOption::select('credentials', 'test_mode')->where('code', 'userede')->where('status', 1)->first();
        if(@$Rede && !empty($Rede->credentials))
        {
            $creds_arr = json_decode($Rede->credentials);
            $this->REDE_PV = (isset($creds_arr->userede_Rede_PV)) ? $creds_arr->userede_Rede_PV : '';
            $this->REDE_TOKEN = (isset($creds_arr->userede_Rede_token)) ? $creds_arr->userede_Rede_token : '';
            $Environment = (isset($Rede->test_mode) && ($Rede->test_mode == '1')) ? \Rede\Environment::sandbox() : \Rede\Environment::production();
            $this->store = new \Rede\Store($this->REDE_PV, $this->REDE_TOKEN, $Environment );
        }
    }
    public function beforePayment(Request $request) 
    {
        unset($request['_token']);
        Session::put('userede_data',$request->all());
        $view_from =$request->view_from ?? '';
        $return_url = route('payment.userede.createPayment');
        if($view_from == 'app'){
            $return_url = route('payment.userede.createPaymentApp');
        }
      //  $request->merge(['amount_2'=>Crypt::encrypt($request->amount)]);
        return view('frontend.payment_gatway.userede_view')
                    ->with(['data' => $request->all(),
                    'return_url'=>$return_url
                ]);
    }

    public function paymentInit(Request $request, $domain='')
    {
        $validatedData = $request->validate([
            'number'        => 'required|min:16|max:20',
            'cvc'           => 'required',
            'holder_name'   => 'required',
            
        ], [
            'number.required'   => __('The Card Number is required.'),
            'cvc.required'      => __('Address Type is required'),
            'holder_name.required' => __('The Card Holder Name is required.'),
            'number.number' => __('Incorrect Card Number.'),
            
        ]);
       
        
        $amount      = $request->amount;
   
        $amount      = $this->getDollarCompareAmount($amount);
        $cart_number =  str_replace(' ', '', $request->number);

        try {
            if($request->cart_type == 'creditCard'){
                 // for creditCard 
                $transaction = (new \Rede\Transaction($amount , 'pedido' . time()))->creditCard(
                    $cart_number,// '5448280000000007',
                    $request->cvc,// '123',
                    $request->expMonth,// '06',
                    $request->expYear,// '2028',
                    $request->holder_name,// 'John Snow'
                );
            }else{
                // for debitCard 
                $transaction = (new \Rede\Transaction($amount , 'pedido' . time()))->debitCard(
                    $cart_number,// '5448280000000007',
                    $request->cvc,// '123',
                    $request->expMonth,// '06',
                    $request->expYear,// '2028',
                    $request->holder_name,// 'John Snow'
                );  
            }
            
      
            //Transaction that will be authorized
            $transaction = (new  \Rede\eRede($this->store))->create($transaction);
        } catch (\Exception $e) {
            $data = Session::get('userede_data');
            unset($data['_token']);
           // Log::info($e->getMessage());
           
            return Redirect::to(route('payment.userede.beforePayment',$data))->with('error','Invalid Card Detail');
        }
        //Successfully authorized transaction;
        
        if ($transaction->getReturnCode() == '00') {
            Session::forget('userede_data');
            
           // printf("Transação autorizada com sucesso; tid=%s\n", $transaction->getTid());
            $payment_form  = $request->payment_from;
            $transactionId = $transaction->getTid();
            $amount        = $request->amount;
            $user_id       = Auth::user()->id;
            $order_number  = $request->order_number;
            if($payment_form == 'cart'){
                $cart_id  = $request->cart_id;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if ($order) {
                    $order->payment_status = 1;
                    $order->save();
                    $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                    if (!$payment_exists) {
                        $payment = new Payment();
                        $payment->date = date('Y-m-d');
                        $payment->order_id = $order->id;
                        $payment->transaction_id = $transactionId;
                        $payment->balance_transaction = $amount;
                        $payment->type = 'cart';
                        $payment->save();

                        // Auto accept order
                        $orderController = new OrderController();
                        $orderController->autoAcceptOrderIfOn($order->id);

                        // Remove cart
                        CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                        Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                        CartAddon::where('cart_id', $cart_id)->delete();
                        CartCoupon::where('cart_id', $cart_id)->delete();
                        CartProduct::where('cart_id', $cart_id)->delete();
                        CartProductPrescription::where('cart_id', $cart_id)->delete();
                        // send success sms
                        $this->sendSuccessSMS($request, $order);
                        // Send Notification
                        if (!empty($order->vendors)) {
                            foreach ($order->vendors as $vendor_value) {
                                $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                                $user_vendors = UserVendor::where(['vendor_id' => $vendor_value->vendor_id])->pluck('user_id');
                                $orderController->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail);
                            }
                        }
                        $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id);
                        $super_admin = User::where('is_superadmin', 1)->pluck('id');
                        $orderController->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);
                    }

                    // Send Email
                    //   $this->successMail();
                }
                $returnUrlParams = '';
                $returnUrl = route('order.success', $order->id);
                return Redirect::to(url($returnUrl . $returnUrlParams))->with('success', 'Transaction has been completed successfully');
    
            } elseif($payment_form == 'wallet'){
                $request->request->add(['user_id' => $user_id, 'wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                $walletController = new WalletController();
                $walletController->creditWallet($request);
                $returnUrl = route('user.wallet');
                return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
               
            }
            elseif($payment_form == 'tip'){
                $request->request->add(['user_id' => $user_id, 'order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                $orderController = new OrderController();
                $orderController->tipAfterOrder($request);
                $returnUrl = route('user.orders');
                return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
             
            }
            elseif($payment_form == 'subscription'){
                $subscription_id = $request->subscription_id;
                $request->request->add(['user_id' => $user_id, 'payment_option_id' => 25, 'amount' => $amount, 'transaction_id' => $transactionId]);
                $subscriptionController = new UserSubscriptionController();
                $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription_id);
                $returnUrl = route('user.subscription.plans');
                return Redirect::to(url($returnUrl))->with('success', 'Transaction has been completed successfully');
         
            }
        }
       

      
       
    }

    public function paymentInitApp(Request $request)
    {
        $validatedData = $request->validate([
            'number'        => 'required|min:16|max:20',
            'cvc'           => 'required',
            'holder_name'   => 'required',
            
        ], [
            'number.required'   => __('The Card Number is required.'),
            'cvc.required'      => __('Address Type is required'),
            'holder_name.required' => __('The Card Holder Name is required.'),
            'number.number' => __('Incorrect Card Number.'),
            
        ]);
       
        
        $amount      = $request->amount;
   
        $amount      = $this->getDollarCompareAmount($amount);
        $cart_number =  str_replace(' ', '', $request->number);

        try {
            if($request->cart_type == 'creditCard'){
                 // for creditCard 
                $transaction = (new \Rede\Transaction($amount , 'pedido' . time()))->creditCard(
                    $cart_number,// '5448280000000007',
                    $request->cvc,// '123',
                    $request->expMonth,// '06',
                    $request->expYear,// '2028',
                    $request->holder_name,// 'John Snow'
                );
            }else{
                // for debitCard 
                $transaction = (new \Rede\Transaction($amount , 'pedido' . time()))->debitCard(
                    $cart_number,// '5448280000000007',
                    $request->cvc,// '123',
                    $request->expMonth,// '06',
                    $request->expYear,// '2028',
                    $request->holder_name,// 'John Snow'
                );  
            }
            
      
            //Transaction that will be authorized
            $transaction = (new  \Rede\eRede($this->store))->create($transaction);
        } catch (\Exception $e) {
            $data = Session::get('userede_data');
            unset($data['_token']);
           // Log::info($e->getMessage());
            return Redirect::to(route('payment.userede.beforePayment',$data))->with('error','Invalid Card Detail');
        }
        //Successfully authorized transaction;
        $Getuser = User::where('auth_token', $request->auth_token)->first();
        
        Auth::loginUsingId($Getuser->id);
        $user = Auth::user();
        if ($transaction->getReturnCode() == '00') {
            Session::forget('userede_data');
            $payment_from  = $request->payment_from;
            $transactionId = $transaction->getTid();
            $amount        = $request->amount;
            $user_id       = $user->id;
            $order_number  = $request->order_number;
            $returnUrl = url('payment/gateway/returnResponse');
            $returnUrlParams = '?status=200&gateway=userede&action=' . $payment_from;
            if($payment_from == 'cart'){
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user_id)->first();
                $cart_id  = $cart->id;
                $order = Order::with(['paymentOption', 'user_vendor', 'vendors:id,order_id,vendor_id'])->where('order_number', $order_number)->first();
                if ($order) {
                    $order->payment_status = 1;
                    $order->save();
                    $payment_exists = Payment::where('transaction_id', $transactionId)->first();
                    if (!$payment_exists) {
                        $payment = new Payment();
                        $payment->date = date('Y-m-d');
                        $payment->order_id = $order->id;
                        $payment->transaction_id = $transactionId;
                        $payment->balance_transaction = $amount;
                        $payment->type = 'cart';
                        $payment->save();

                        // Auto accept order
                        $orderController = new OrderController();
                        $orderController->autoAcceptOrderIfOn($order->id);

                        // Remove cart
                        CaregoryKycDoc::where('cart_id',$cart_id)->update(['ordre_id'=> $order->id,'cart_id'=>'' ]);
                        Cart::where('id', $cart_id)->update(['schedule_type' => null, 'scheduled_date_time' => null]);
                        CartAddon::where('cart_id', $cart_id)->delete();
                        CartCoupon::where('cart_id', $cart_id)->delete();
                        CartProduct::where('cart_id', $cart_id)->delete();
                        CartProductPrescription::where('cart_id', $cart_id)->delete();
                        // send success sms
                        $this->sendSuccessSMS($request, $order);
                        // Send Notification
                        if (!empty($order->vendors)) {
                            foreach ($order->vendors as $vendor_value) {
                                $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                                $user_vendors = UserVendor::where(['vendor_id' => $vendor_value->vendor_id])->pluck('user_id');
                                $orderController->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail);
                            }
                        }
                        $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id);
                        $super_admin = User::where('is_superadmin', 1)->pluck('id');
                        $orderController->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);
                    }

                    // Send Email
                    //   $this->successMail();
                }
                $returnUrlParams = $returnUrlParams . '&order=' .  $order_number;
            } elseif($payment_from == 'wallet'){
                $request->request->add(['user_id' => $user_id, 'wallet_amount' => $amount, 'transaction_id' => $transactionId]);
                $walletController = new WalletController();
                $walletController->creditWallet($request);
            }
            elseif($payment_from == 'tip'){
                $request->request->add(['user_id' => $user_id, 'order_number' => $order_number, 'tip_amount' => $amount, 'transaction_id' => $transactionId]);
                $orderController = new OrderController();
                $orderController->tipAfterOrder($request);
                $returnUrlParams = $returnUrlParams . '&order=' .  $order_number;
            }
            elseif($payment_from == 'subscription'){
                $subscription_id = $request->subscription_id;
                $request->request->add(['user_id' => $user_id, 'payment_option_id' => 25, 'amount' => $amount, 'transaction_id' => $transactionId]);
                $subscriptionController = new UserSubscriptionController();
                $subscriptionController->purchaseSubscriptionPlan($request, '', $subscription_id);
            }
            DB::commit(); //Commit transaction after all the operations
            return Redirect::to(url($returnUrl . $returnUrlParams));
        }
    }
}
