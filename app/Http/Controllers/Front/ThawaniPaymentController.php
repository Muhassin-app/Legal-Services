<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Aws\Crypto\AesEncryptingStream;
use iluminate\Http\JsonResponse;
use App\Http\Traits\{ApiResponser, OrderTrait};
use App\Models\{Order, Payment, PaymentOption, User};
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\v1\UserSubscriptionController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Api\v1\PickupDeliveryController;
use App\Models\CaregoryKycDoc;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\ClientCurrency;
use App\Models\UserVendor;
use App\Models\UserAddress;
use App\Http\Traits\ThawanipaymentManager;


class ThawaniPaymentController extends Controller
{
    use ThawanipaymentManager;

    public function __construct() {

        $creds =self::getDetails();
        $this->checkoutUrl =$creds['checkout_url'];
        $this->payurl =  $creds['payurl'];
        $this->thawani_Apikey = $creds['thawani_Apikey'];
        $this->thawani_publishKey = $creds['thawani_publishKey'];
        $this->currency = $creds['currency'];
    }
    public function paybythawanipg(Request $request)
    {
         if($this->currency ){
                $transaction_id = self::orderNumber($request);
                $payment        = Payment::where('transaction_id', $transaction_id)->first();
                $user           = auth()->user();
                $amount         = intval($payment->balance_transaction);
                $sessionUrl     = $this->checkoutUrl;
                $successUrl     = url('after-payment/'.$transaction_id);
                $cancelurl      = url(isset($request->returnUrl) ? $request->returnUrl : 'payment/gateway/returnResponse?status=0&gateway=thawani');



                $response = Http::withHeaders(['Accept' => 'application/json','Content-Type' => 'application/json','thawani-api-key' => $this->thawani_Apikey,
                                             ])->post($sessionUrl, ['client_reference_id' => $user->id,'mode' => 'payment','products' => [[
                                                'name' => 1,'quantity' => 1,'unit_amount' =>$amount*1000,]],
                                                'success_url' => $successUrl,'cancel_url' =>  $cancelurl,'metadata' => ['Customer name' => $user->name,'order id' => $transaction_id],]);


                if ($response->successful()) {
                        $responseData = $response->json();
                        $sessionId = $responseData['data']['session_id'];

                        $payurl= "{$this->payurl}/pay/{$sessionId}?key={$this->thawani_publishKey}";
                        return response()->json([
                            'status'      => 'Success',
                            'payment_url' => $payurl
                        ]);
                } else {
                    Log::error('Thawani API request failed', ['status' => $response->status()]);
                    return response()->json(['error' => 'Thawani API request failed'], 500);
                }

        }else{
            return response()->json(['message' => 'Payment Failed currency OMR accepted'], 500);
        }
    }
    public function afterpayment($domain='',$transaction_id){

           try{
                $transactionId = $transaction_id;
                $payment       = Payment::where('transaction_id', $transactionId)->first();
                if ($payment) {
                    $payment->viva_order_id     = $transactionId;
                    $payment->payment_option_id = 67;
                    $payment->save();
                }
                if ($payment->type == 'cart') {
                    $order = Order::where('order_number', $transactionId)->first();
                    if ($order) {
                        $order->payment_status = '1';
                        $order->save();

                        $this->orderSuccessCartDetail($order);
                        if ($payment->payment_from == 'web') {
                            return redirect()->route('order.success', $order->id);
                        } else {
                            $returnUrl = route('payment.gateway.return.response') . '/?gateway=thawani' . '&status=200&order=' . $order->id;
                            return redirect($returnUrl);
                        }
                    }
                } elseif ($payment->type == 'wallet') {
                        if ($payment->payment_from == 'app') {
                            $user = User::findOrFail($payment->user_id);
                            Auth::login($user);
                            $returnUrl = route('payment.gateway.return.response') . '/?gateway=thawani' . '&status=200&transaction_id=' . $payment->transaction_id . '&action=wallet';
                        }
                        else {
                            $user= auth()->user();
                            $returnUrl = route('user.wallet');
                        }
                        $wallet  = $user->wallet;
                        $wallet->depositFloat($payment->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $payment->transaction_id . '</b>']);
                        return redirect($returnUrl);

                } elseif ($payment->type == 'subscription') {
                        $subcription_idtime = $payment->transaction_id;
                        $subs_id            = strstr($subcription_idtime, '_', true);
                        $data['transaction_id']    = $payment->transaction_id;
                        $data['payment_option_id'] = 67;
                        $data['subsid']            = $payment->transaction_id;
                        $data['subscription_id']   = $subs_id;
                        $data['amount']            = $payment->amount;
                        $request = new Request($data);
                        $subscriptionController = new UserSubscriptionController();
                        $subscriptionController->purchaseSubscriptionPlan($request, $subs_id);
                        if ($payment->payment_from == 'web') {
                            return redirect()->route('user.subscription.plans','1');
                        } else {
                            $returnUrl = route('payment.gateway.return.response') . '/?gateway=thawani' . '&status=200&transaction_id=' . $payment->transaction_id . '&action=subscription';
                            return redirect($returnUrl);
                        }
                } elseif ($payment->type == 'pickup_delivery') {

                        $data['payment_option_id'] = 67;
                        $data['transaction_id']    = $transactionId;
                        $data['amount']            = $payment->amount;
                        $data['order_number']      = $transactionId;
                        $data['reload_route']      = $payment->reload_route;

                        $request = new Request($data);

                        $plaseOrderForPickup = new PickupDeliveryController();
                        $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
                        if ($payment->payment_from == 'web') {
                            return redirect()->route('front.booking.details', $transactionId);
                        } else {
                            $returnUrl = route('payment.gateway.return.response') . '/?gateway=thawani' . '&status=200&order=' . $transactionId;
                            return redirect($returnUrl);
                        }
                } elseif ($payment->type == 'tip') {

                    $data['tip_amount']     = $payment->amount;
                    $data['order_number']   = $payment->order_number;
                    $data['transaction_id'] = $transactionId;
                    $request = new Request($data);
                    $orderController = new OrderController();
                    $orderController->tipAfterOrder($request);
                    if ($payment->payment_from == 'web') {
                        return redirect()->route('user.orders');
                    } else {
                        $returnUrl = route('payment.gateway.return.response') . '/?gateway=thawani' . '&status=200&order=' . $transactionId . '&action=tip';
                        return redirect($returnUrl);
                    }
                }
                else {
                    return redirect()->back();
                }
            }catch (\Exception $e) {
                    return $e->getMessage();
            }
    }

}
