<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Aws\Crypto\AesEncryptingStream;
use Illuminate\Support\Facades\Crypt;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use iluminate\Http\JsonResponse;
use \Firebase\JWT\JWT;
use App\Http\Traits\{ApiResponser, OrderTrait};
use App\Models\{Order, Payment, PaymentOption, User};
use Illuminate\Support\Facades\{Auth, Http};
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


class TotalpayController extends Controller
{

    public function __construct()
    {
        $payOption = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'totalpay')->where('status', 1)->first();
        if (@$payOption && !empty($payOption->credentials)) {
            $credentials = json_decode($payOption->credentials);
            $this->merchantKey = $credentials->totalpay_MerchantId;
            $this->password = $credentials->totalpay_password;
        }
    }

    public function makePayment(Request $request)
    {
      $orderNumber = strval($this->orderNumber($request));
      $merchantKey=strval($this->merchantKey);
      $password=strval($this->password);

      $amount = number_format($request->amount, 2, '.', '');

      $calculateHash = function ($requestData, $password) {
          $dataToHash = md5(
              strtoupper(
                  $requestData['order']['number'] .
                  $requestData['order']['amount'] .
                  $requestData['order']['currency'] .
                  $requestData['order']['description'] .
                  $password
              )
          );
          return sha1($dataToHash);
      };

      $sessionHash = $calculateHash([
          'order' => [
              'number' => $orderNumber,
              'amount' => $amount,
              'currency' => 'AED',
              'description' => 'Important gift',
          ],
      ], $password);
      $response = Http::post('https://checkout.totalpay.global/api/v1/session', [
          'merchant_key' =>  $merchantKey,
          'operation' => 'purchase',
          'methods' => [
              'card'
          ],
          'session_expiry' => 05,
          'order' => [
              'number' => $orderNumber,
              'amount' => $amount,
              'currency' => 'AED',
              'description' => 'Important gift',
          ],
          'success_url' => url('/success-totalpay'),
          'expiry_url' => 'https://example.domain.com/expiry',
          'hash' => $sessionHash,
      ], [
          'Content-Type' => 'application/json',
      ]);
      $url = $response->json()['redirect_url'];
      return response()->json([
        'status' => 'Success',
        'payment_url' =>  $url
    ]);
    }

    public function paymentSuccessTotalpay(Request $request)
    {
        try{
                        $transactionId = $request['order_id'];
                        $payment = Payment::where('transaction_id', $transactionId)->first();
                    if ($payment) {
                        $payment->viva_order_id = $transactionId;
                        $payment->payment_option_id = 65;
                        $payment->save();
                    }
                        if ($payment->type== 'cart') {
                            $order = Order::where('order_number', $transactionId)->first();
                            if ($order) {
                                $order->payment_status = '1';
                                $order->save();

                                $this->orderSuccessCartDetail($order);
                                if ($payment->payment_from == 'web') {
                                    return redirect()->route('order.success', $order->id);
                                } else {
                                    $returnUrl = route('payment.gateway.return.response') . '/?gateway=totalpay' . '&status=200&order=' . $order->id;
                                    return redirect($returnUrl);
                                }
                            }
                        } elseif ($payment->type == 'wallet') {
                                if ($payment->payment_from == 'app') {
                                    $user = User::findOrFail($payment->user_id);
                                    Auth::login($user);
                                    $returnUrl = route('payment.gateway.return.response') . '/?gateway=totalpay' . '&status=200&transaction_id=' . $payment->transaction_id . '&action=wallet';
                                }
                                else {
                                    $user= auth()->user();
                                    $returnUrl = route('user.wallet');
                                }
                                $wallet  = $user->wallet;
                                $wallet->depositFloat($payment->balance_transaction, ['Wallet has been <b>credited</b> for order number <b>' . $payment->transaction_id . '</b>']);
                                return redirect($returnUrl);

                     } elseif ($payment->type == 'subscription') {
                                $data['transaction_id'] = $payment->transaction_id;
                                $data['payment_option_id'] =65;
                                $data['subsid'] = $payment->transaction_id;
                                $data['subscription_id'] = $payment->transaction_id;
                                $data['amount'] = $payment->amount;
                                $request = new Request($data);
                                $subscriptionController = new UserSubscriptionController();
                                $subscriptionController->purchaseSubscriptionPlan($request, $request->subscription_id);
                                if ($payment->payment_from == 'web') {
                                    return redirect()->route('user.subscription.plans','1');
                                } else {
                                    $returnUrl = route('payment.gateway.return.response') . '/?gateway=totalpay' . '&status=200&transaction_id=' . $payment->transaction_id . '&action=subscription';
                                    return redirect($returnUrl);
                                }
                    } elseif ($payment->type == 'pickup_delivery') {

                                $data['payment_option_id'] = 65;
                                $data['transaction_id'] = $transactionId;
                                $data['amount'] = $payment->amount;
                                $data['order_number'] = $transactionId;
                                $data['reload_route'] = $payment->reload_route;

                                $request = new Request($data);

                                $plaseOrderForPickup = new PickupDeliveryController();
                                $res = $plaseOrderForPickup->orderUpdateAfterPaymentPickupDelivery($request);
                                if ($payment->payment_from == 'web') {
                                    return redirect()->route('front.booking.details', $transactionId);
                                } else {
                                    $returnUrl = route('payment.gateway.return.response') . '/?gateway=totalpay' . '&status=200&order=' . $transactionId;
                                    return redirect($returnUrl);
                                }
                    } elseif ($payment->type == 'tip') {

                            $data['tip_amount'] = $payment->amount;
                            $data['order_number'] = $payment->order_number;
                            $data['transaction_id'] = $transactionId;
                            $request = new Request($data);
                            $orderController = new OrderController();
                            $orderController->tipAfterOrder($request);
                            if ($payment->payment_from == 'web') {
                                return redirect()->route('user.orders');
                            } else {
                                $returnUrl = route('payment.gateway.return.response') . '/?gateway=totalpay' . '&status=200&order=' . $transactionId . '&action=tip';
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
    public function orderSuccessCartDetail($order)
    {
        try {
            $orderController = new OrderController();
            $orderController->autoAcceptOrderIfOn($order->id);

            $cart = Cart::where('user_id', $order->user_id)->select('id')->first();
            $cartid = $cart->id;

            Cart::where('id', $cartid)->update([
                'schedule_type' => null,
                'scheduled_date_time' => null,
                'comment_for_pickup_driver' => null,
                'comment_for_dropoff_driver' => null,
                'comment_for_vendor' => null,
                'schedule_pickup' => null,
                'schedule_dropoff' => null,
                'specific_instructions' => null
            ]);

            CaregoryKycDoc::where('cart_id', $cartid)->update([
                'ordre_id' => $order->id,
                'cart_id' => ''
            ]);
            CartAddon::where('cart_id', $cartid)->delete();
            CartCoupon::where('cart_id', $cartid)->delete();
            CartProduct::where('cart_id', $cartid)->delete();
            CartProductPrescription::where('cart_id', $cartid)->delete();

            if (!empty($order->vendors)) {
                foreach ($order->vendors as $vendor_value) {
                    $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                    $user_vendors = UserVendor::where([
                        'vendor_id' => $vendor_value->vendor_id
                    ])->pluck('user_id');
                    $orderController->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail);
                }
            }

            $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order->id);
            $super_admin = User::where('is_superadmin', 1)->pluck('id');
            $orderController->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);

            // send sms
            $this->sendOrderSuccessSMS($order);
        } catch (\Exception $e) {
            return true;
        }
        return true;
    }
    public function orderNumber($request)
            {
                try{
                    $time= isset($request->transaction_id) ? $request->transaction_id : time();
                    $user_id = auth()->id();
                    $amount  = $request->amount?$request->amount:$request->amt;
                    if(isset($request->action)){
                        $request->request->add(['payment_from' => $request->action,'come_from'=>'app']);
                    }
                    if ($request->payment_from == 'cart') {
                         $time = $request->order_number;
                      Payment::create([
                            'amount' => $amount,
                            'transaction_id' => $time,
                            'balance_transaction' => $amount,
                            'type' => 'cart',
                            'date' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'payment_from' => $request->come_from ?? 'web',
                        ]);
                    } elseif ($request->payment_from == 'wallet') {
                        Payment::create([
                            'amount' => $amount,
                            'transaction_id' => $time,
                            'balance_transaction' => $amount,
                            'type' => 'wallet',
                            'date' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'payment_from' => $request->come_from ?? 'web',
                        ]);
                    } elseif ($request->payment_from == 'subscription') {
                        $time = $request->subscription_id ?$request->subscription_id : time();
                       $payment= Payment::create([
                            'amount' => 0,
                            'transaction_id' => $request->subscription_id . '_' . $time,
                            'balance_transaction' => round($amount, 2),
                            'type' => 'subscription',
                            'date' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'payment_from' => $request->come_from ?? 'web',
                        ]);
                    } elseif ($request->payment_from == 'tip') {
                        $time = time();
                       $res=  Payment::create([
                            'amount' => 0,
                            'transaction_id' =>  $time,
                            'balance_transaction' => $request->amount,
                            'type' => 'tip',
                            'date' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'payment_from' => $request->come_from ?? 'web',
                        ]);
                    } else if ($request->payment_from == 'pickup_delivery') {
                        $time = $request->order_number;
                        Payment::create([
                            'amount' => 0,
                            'transaction_id' => $time,
                            'balance_transaction' => $request->amt,
                            'type' => 'pickup_delivery',
                            'date' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'payment_from' => $request->come_from ?? 'web',
                        ]);
                    }
                    return $time;
                }catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
}
