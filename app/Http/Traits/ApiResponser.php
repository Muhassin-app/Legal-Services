<?php

namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;
use Illuminate\Support\Carbon;
use App\Models\LoyaltyCard;
use DB;
use App\Models\ClientCurrency;
use App\Models\CartProduct;
use App\Models\SubscriptionInvoicesUser;
use Auth;
use App\Models\Cart;
use App\Models\EmailTemplate;
use App\Models\UserAddress;
use App\Models\{Product, OrderProductRating, ClientPreference,UserDevice, NotificationTemplate};
use Twilio\Rest\Client as TwilioClient;
use App\Http\Traits\smsManager;
trait ApiResponser
{
	use  smsManager;

	protected function successResponse($data, $message = null, $code = 200)
	{
		return response()->json([
			'status' => 'Success',
			'message' => $message,
			'data' => $data
		], $code);
	}

	protected function successMail()
	{
		$data = ClientPreference::select(
			'sms_key',
			'sms_secret',
			'sms_from',
			'mail_type',
			'mail_driver',
			'mail_host',
			'mail_port',
			'mail_username',
			'sms_provider',
			'mail_password',
			'mail_encryption',
			'mail_from'
		)->where('id', '>', 0)->first();
		$confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);


		$mail_from = $data->mail_from;

		$email_template_content = '';
		$email_template = EmailTemplate::where('id', 6)->first();
		$address = UserAddress::where('user_id', Auth::user()->id)->first();
		if (Auth::user()) {
			$cart = Cart::select('id', 'is_gift', 'item_count')->with('coupon.promo')->where('status', '0')->where('user_id', Auth::user()->id)->first();
		} else {
			$cart = Cart::select('id', 'is_gift', 'item_count')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
		}
		if ($cart) {
			$cartDetails = $this->getCart($cart);
		}
		if ($email_template) {
			$email_template_content = $email_template->content;

			$returnHTML = view('email.orderProducts')->with(['cartData' => $cartDetails])->render();

			$email_template_content = $email_template->content;
			$email_template_content = str_ireplace("{name}", Auth::user()->name, $email_template_content);
			$email_template_content = str_ireplace("{products}", $returnHTML, $email_template_content);
			$email_template_content = str_ireplace("{address}", $address->address . ', ' . $address->state . ', ' . $address->country . ', ' . $address->pincode, $email_template_content);
		}
		Mail::send('frontend.successmail', compact('email_template_content'), function ($message) use ($mail_from) {
			$message->from($mail_from);
			$message->to(Auth::user()->email);
			$message->subject('Payment Succesful Notification');
		});
	}

	protected function failMail()
	{
		try{
			$data = ClientPreference::select(
				'sms_key',
				'sms_secret',
				'sms_from',
				'mail_type',
				'mail_driver',
				'mail_host',
				'mail_port',
				'mail_username',
				'sms_provider',
				'mail_password',
				'mail_encryption',
				'mail_from'
			)->where('id', '>', 0)->first();
			$confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);


			$mail_from = $data->mail_from;
			$email_template_content = '';
			$email_template = EmailTemplate::where('id', 7)->first();
			if (Auth::user()) {
				$cart = Cart::select('id', 'is_gift', 'item_count')->with('coupon.promo')->where('status', '0')->where('user_id', Auth::user()->id)->first();
			} else {
				$cart = Cart::select('id', 'is_gift', 'item_count')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
			}
			if ($cart) {
				$cartDetails = $this->getCart($cart);
			}
			if ($email_template) {
				$email_template_content = $email_template->content;
				$returnHTML = view('email.orderProducts')->with(['cartData' => $cartDetails])->render();

				$email_template_content = str_ireplace("{name}", Auth::user()->name, $email_template_content);
				$email_template_content = str_ireplace("{products}", $returnHTML, $email_template_content);
			}
			Mail::send('frontend.failmail', compact('email_template_content'), function ($message) use ($mail_from) {
				$message->from($mail_from);
				$message->to(Auth::user()->email);
				$message->subject('Payment Failure Notification');
			});
		}catch (\Exception $ex){

		}

	}



	protected function errorResponse($message = null, $code, $data = null)
	{
		$validCodes = range(100, 599);

		if (!is_int($code) || !in_array($code, $validCodes)) {
			$code = 500;
		}
		return response()->json([
			'status' => 'Error',
			'message' => $message,
			'data' => $data,
			'code' => $code
		], $code);
	}


	protected function updateaverageRating($product_id, $message = null, $code = 200)
	{
		$ava_rating = OrderProductRating::where(['status' => '1', 'product_id' => $product_id])->avg('rating');
		$up_rat = Product::where('id', $product_id)->update(['averageRating' => $ava_rating]);
		return response()->json([
			'status' => 'Success',
			'message' => $message,
			'data' => $up_rat
		], $code);
	}



	# check if last mile delivery on
	public function checkIfPickupDeliveryOnCommon()
	{
		$preference = ClientPreference::select('id', 'need_dispacher_ride', 'pickup_delivery_service_key', 'pickup_delivery_service_key_code', 'pickup_delivery_service_key_url')->first();
		if ($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url))
			return $preference;
		else
			return false;
	}

	# check if laundry on
	public function checkIfLaundryOnCommon()
	{
		$preference = ClientPreference::select('id', 'need_laundry_service', 'laundry_service_key', 'laundry_service_key_code', 'laundry_service_key_url')->first();
		if ($preference->need_laundry_service == 1 && !empty($preference->laundry_service_key) && !empty($preference->laundry_service_key_code) && !empty($preference->laundry_service_key_url))
			return $preference;
		else
			return false;
	}

	# check if on demand service  on
	public function checkIfOnDemandOnCommon()
	{
		$preference = ClientPreference::select('id', 'need_dispacher_home_other_service', 'dispacher_home_other_service_key', 'dispacher_home_other_service_key_code', 'dispacher_home_other_service_key_url')->first();
		if ($preference->need_dispacher_home_other_service == 1 && !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_code) && !empty($preference->dispacher_home_other_service_key_url))
			return $preference;
		else
			return false;
	}

	# check if on demand service  on
	public function checkIfAppointmentOnCommon()
	{
		$preference = ClientPreference::select('id', 'need_appointment_service', 'appointment_service_key_code', 'appointment_service_key', 'appointment_service_key_url')->first();
		if ($preference->need_appointment_service == 1 && !empty($preference->appointment_service_key_code) && !empty($preference->appointment_service_key) && !empty($preference->appointment_service_key_url))
			return $preference;
		else
			return false;
	}



	# set currency in session
	public function setCurrencyInSesion(){
		$currency_id = Session::get('customerCurrency');
		if(isset($currency_id) && !empty($currency_id)){
            $all = ClientCurrency::orderBy('is_primary','desc')->where('currency_id',$currency_id)->first();
            if(empty($all)){
                $primaryCurrency = ClientCurrency::where('is_primary','=', 1)->first();
                Session::put('customerCurrency',$primaryCurrency->currency_id);
				Session::put('currencySymbol',$primaryCurrency->currency->symbol);
                $currency_id = $primaryCurrency->currency_id ;
            }else{
                $currency_id = (int)$currency_id;
                Session::put('customerCurrency',$currency_id);
            }
        }
        else{
            $primaryCurrency = ClientCurrency::where('is_primary','=', 1)->first();
            Session::put('customerCurrency',$primaryCurrency->currency_id);
			Session::put('currencySymbol',$primaryCurrency->currency->symbol);
            $currency_id = $primaryCurrency->currency_id ;
        }

		return  $currency_id;
	}

	public function getCart($cart, $address_id = 0)
	{
		$cart_id = $cart->id;
		$user = Auth::user();
		$langId = $user->language;
        $curId = $user->currency;
		$pharmacy = ClientPreference::first();
		$cart->pharmacy_check = $pharmacy->pharmacy_check;
		$customerCurrency = ClientCurrency::where('currency_id', $curId)->first();
		$latitude = '';
		$longitude = '';
		if ($address_id > 0) {
			$address = UserAddress::where('user_id', $user->id)->where('id', $address_id)->first();
		} else {
			$address = UserAddress::where('user_id', $user->id)->where('is_primary', 1)->first();
			$address_id = ($address) ? $address->id : 0;
		}
		$latitude = ($address) ? $address->latitude : '';
		$longitude = ($address) ? $address->longitude : '';
		$cartData = CartProduct::with([
			'vendor', 'coupon' => function ($qry) use ($cart_id) {
				$qry->where('cart_id', $cart_id);
			}, 'vendorProducts.pvariant.media.image', 'vendorProducts.product.media.image',
			'vendorProducts.pvariant.vset.variantDetail.trans' => function ($qry) use ($langId) {
				$qry->where('language_id', $langId);
			},
			'vendorProducts.pvariant.vset.optionData.trans' => function ($qry) use ($langId) {
				$qry->where('language_id', $langId);
			},
			'vendorProducts.product.translation_one' => function ($q) use ($langId) {
				$q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
				$q->where('language_id', $langId);
			},
			'vendorProducts' => function ($qry) use ($cart_id) {
				$qry->where('cart_id', $cart_id);
			},
			'vendorProducts.addon.set' => function ($qry) use ($langId) {
				$qry->where('language_id', $langId);
			},
            'vendorProducts.product.categoryName' => function ($q) use ($langId) {
                $q->select('category_id', 'name');
                $q->where('language_id', $langId);
            },
			'vendorProducts.addon.option' => function ($qry) use ($langId) {
				$qry->where('language_id', $langId);
			}, 'vendorProducts.product.taxCategory.taxRate',
		])->select('vendor_id', 'luxury_option_id')->where('status', [0, 1])->where('cart_id', $cart_id)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();
		$loyalty_amount_saved = 0;
		$redeem_points_per_primary_currency = '';
		$loyalty_card = LoyaltyCard::where('status', '0')->first();
		if ($loyalty_card) {
			$redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
		}
		$subscription_features = array();
		if ($user) {
			$order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
			if ($order_loyalty_points_earned_detail) {
				$loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
				if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
					$loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
					if ($customerCurrency->is_primary != 1) {
						$loyalty_amount_saved = $loyalty_amount_saved * $customerCurrency->doller_compare;
					}
				}
			}
			$now = Carbon::now()->toDateTimeString();
			$user_subscription = SubscriptionInvoicesUser::with('features')
				->select('id', 'user_id', 'subscription_id')
				->where('user_id', $user->id)
				->where('end_date', '>', $now)
				->orderBy('end_date', 'desc')->first();
			if ($user_subscription) {
				foreach ($user_subscription->features as $feature) {
					$subscription_features[] = $feature->feature_id;
				}
			}
		}
		$total_payable_amount = $total_subscription_discount = $total_discount_amount = $total_discount_percent = $total_taxable_amount = 0.00;
		if ($cartData) {
			$delivery_status = 1;
			foreach ($cartData as $ven_key => $vendorData) {
				$payable_amount = $taxable_amount = $subscription_discount = $discount_amount = $discount_percent = $deliver_charge = $delivery_fee_charges = 0.00;
				$delivery_count = 0;
				foreach ($vendorData->vendorProducts as $ven_key => $prod) {
					$quantity_price = 0;
					$divider = (empty($prod->doller_compare) || $prod->doller_compare < 0) ? 1 : $prod->doller_compare;
					$price_in_currency = $prod->pvariant->price / $divider;
					$price_in_doller_compare = $price_in_currency * $customerCurrency->doller_compare;
					$quantity_price = $price_in_doller_compare * $prod->quantity;
					$prod->pvariant->price_in_cart = $prod->pvariant->price;
					$prod->pvariant->price = $price_in_currency;
					$prod->pvariant->media_one = $prod->pvariant->media ? $prod->pvariant->media->first() : [];
					$prod->pvariant->media_second = $prod->product->media ? $prod->product->media->first() : [];
					$prod->pvariant->multiplier = $customerCurrency->doller_compare;
					$prod->pvariant->quantity_price = decimal_format($quantity_price);
					$payable_amount = $payable_amount + $quantity_price;
					$taxData = array();
					if (!empty($prod->product->taxCategory) && count($prod->product->taxCategory->taxRate) > 0) {
						foreach ($prod->product->taxCategory->taxRate as $tckey => $tax_value) {
							$rate = round($tax_value->tax_rate);
							$tax_amount = ($price_in_doller_compare * $rate) / 100;
							$product_tax = $quantity_price * $rate / 100;
							$taxData[$tckey]['identifier'] = $tax_value->identifier;
							$taxData[$tckey]['rate'] = $rate;
							$taxData[$tckey]['tax_amount'] = decimal_format($tax_amount);
							$taxData[$tckey]['product_tax'] = decimal_format($product_tax);
							$taxable_amount = $taxable_amount + $product_tax;
							$payable_amount = $payable_amount + $product_tax;
						}
						unset($prod->product->taxCategory);
					}
					$prod->taxdata = $taxData;
					foreach ($prod->addon as $ck => $addons) {
						$opt_price_in_currency = $addons->option->price / $divider;
						$opt_price_in_doller_compare = $opt_price_in_currency * $customerCurrency->doller_compare;
						$opt_quantity_price = decimal_format($opt_price_in_doller_compare * $prod->quantity);
						$addons->option->price_in_cart = $addons->option->price;
						$addons->option->price = $opt_price_in_currency;
						$addons->option->multiplier = $customerCurrency->doller_compare;
						$addons->option->quantity_price = $opt_quantity_price;
						$payable_amount = $payable_amount + $opt_quantity_price;
					}
					if (isset($prod->pvariant->image->imagedata) && !empty($prod->pvariant->image->imagedata)) {
						$prod->cartImg = $prod->pvariant->image->imagedata;
					} else {
						$prod->cartImg = (isset($prod->product->media[0]) && !empty($prod->product->media[0])) ? $prod->product->media[0]->image : '';
					}
					if (!empty($prod->product->Requires_last_mile) && ($prod->product->Requires_last_mile == 1)) {
						$deliver_charge = $this->getDeliveryFeeDispatcher($vendorData->vendor_id, $user->id);
						if (!empty($deliver_charge) && $delivery_count == 0) {
							$delivery_count = 1;
							$prod->deliver_charge = decimal_format($deliver_charge);
							$payable_amount = $payable_amount + $deliver_charge;
							$delivery_fee_charges = $deliver_charge;
						}
					}
				}
				if ($vendorData->coupon) {
					if ($vendorData->coupon->promo->promo_type_id == 2) {
						$total_discount_percent = $vendorData->coupon->promo->amount;
						$payable_amount -= $total_discount_percent;
					} else {
						$gross_amount = decimal_format(($payable_amount - $taxable_amount));
						$percentage_amount = ($gross_amount * $vendorData->coupon->promo->amount / 100);
						$payable_amount -= $percentage_amount;
					}
				}
				if (in_array(1, $subscription_features)) {
					$subscription_discount = $subscription_discount + $delivery_fee_charges;
				}
				if (isset($serviceArea)) {
					if ($serviceArea->isEmpty()) {
						$vendorData->isDeliverable = 0;
						$delivery_status = 0;
					} else {
						$vendorData->isDeliverable = 1;
					}
				}
				$vendorData->delivery_fee_charges = decimal_format($delivery_fee_charges);
				$vendorData->payable_amount = decimal_format($payable_amount);
				$vendorData->discount_amount = decimal_format($discount_amount);
				$vendorData->discount_percent = decimal_format($discount_percent);
				$vendorData->taxable_amount = decimal_format($taxable_amount);
				$vendorData->product_total_amount = decimal_format(($payable_amount - $taxable_amount));
				if (!empty($subscription_features)) {
					$vendorData->product_total_amount = decimal_format(($payable_amount - $taxable_amount - $subscription_discount));
				}
				$total_payable_amount = $total_payable_amount + $payable_amount;
				$total_taxable_amount = $total_taxable_amount + $taxable_amount;
				$total_discount_amount = $total_discount_amount + $discount_amount;
				$total_discount_percent = $total_discount_percent + $discount_percent;
				$total_subscription_discount = $total_subscription_discount + $subscription_discount;
			}
			$is_percent = 0;
			$amount_value = 0;
			if ($cart->coupon) {
				foreach ($cart->coupon as $ck => $coupon) {
					if (isset($coupon->promo)) {
						if ($coupon->promo->promo_type_id == 1) {
							$is_percent = 1;
							$total_discount_percent = $total_discount_percent + round($coupon->promo->amount);
						}
					}
				}
			}
			if ($is_percent == 1) {
				$total_discount_percent = ($total_discount_percent > 100) ? 100 : $total_discount_percent;
				$total_discount_amount = $total_discount_amount + ($total_payable_amount * $total_discount_percent) / 100;
			}
			if ($amount_value > 0) {
				$amount_value = $amount_value * $customerCurrency->doller_compare;
				$total_discount_amount = $total_discount_amount + $amount_value;
			}
			if (!empty($subscription_features)) {
				$total_discount_amount = $total_discount_amount + $total_subscription_discount;
				$cart->total_subscription_discount = decimal_format($total_subscription_discount);
			}
			$total_payable_amount = $total_payable_amount - $total_discount_amount;
			if ($loyalty_amount_saved > 0) {
				if ($loyalty_amount_saved > $total_payable_amount) {
					$loyalty_amount_saved =  $total_payable_amount;
				}
				$total_payable_amount = $total_payable_amount - $loyalty_amount_saved;
			}
			$cart->loyalty_amount = decimal_format($loyalty_amount_saved);
			$cart->gross_amount = decimal_format(($total_payable_amount + $total_discount_amount + $loyalty_amount_saved - $total_taxable_amount));
			$cart->new_gross_amount = decimal_format(($total_payable_amount + $total_discount_amount));
			$cart->total_payable_amount = decimal_format($total_payable_amount);
			$cart->total_discount_amount = decimal_format($total_discount_amount);
			$cart->total_taxable_amount = decimal_format($total_taxable_amount);
			$cart->tip_5_percent = decimal_format(0.05 * $total_payable_amount);
			$cart->tip_10_percent = decimal_format(0.1 * $total_payable_amount);
			$cart->tip_15_percent = decimal_format(0.15 * $total_payable_amount);
			$cart->deliver_status = $delivery_status;
			$cart->products = $cartData->toArray();
		}
		return $cart;
	}



	public function sendStatusChangePushNotificationCustomer($user_ids, $orderData, $order_status_id)
    {
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();

        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
           if ($order_status_id == 2) {
                $notification_content = NotificationTemplate::where('id', 5)->first();
            } elseif ($order_status_id == 3) {
                $notification_content = NotificationTemplate::where('id', 6)->first();
            } elseif ($order_status_id == 4) {
                $notification_content = NotificationTemplate::where('id', 7)->first();
            } elseif ($order_status_id == 5) {
                //Check for order is takeaway
                if(@$orderData->luxury_option_id == 3)
                {
                    $notification_content = NotificationTemplate::where('slug', 'order-out-for-takeaway-delivery')->first();
                }else{
                    $notification_content = NotificationTemplate::where('id', 8)->first();
                }
            } elseif ($order_status_id == 6) {
                $notification_content = NotificationTemplate::where('id', 9)->first();
            }
            if ($notification_content) {

                $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "default",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => url('user/orders'),
                        "android_channel_id" => "default-channel-id"
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        "type" => "order_status_change"
                    ],
                    "priority" => "high"
                ];
                sendFcmCurlRequest($data);
            }
        }
    }


	protected function sendSmsNew($provider, $sms_key, $sms_secret, $sms_from, $to, $body){
        try{
            $template_id = $body['template_id']??''; //sms Template_id
            $body = $body['body']??'';
            $client_preference =  getClientPreferenceDetail();
            if($client_preference->sms_provider == 1)
            {
                $client = new TwilioClient($sms_key, $sms_secret);
                $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
            }elseif($client_preference->sms_provider == 2) //for mtalkz gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->mTalkz_sms($to,$body,$crendentials,$template_id);
            }elseif($client_preference->sms_provider == 3) //for mazinhost gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->mazinhost_sms($to,$body,$crendentials);
            }elseif($client_preference->sms_provider == 4) //for unifonic gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->unifonic($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 5) //for arkesel_sms gateway
            {
                $crendentials = json_decode($client_preference->sms_credentials);
                $send = $this->arkesel_sms($to,$body,$crendentials);
                if( isset($send->code) && $send->code != 'ok'){
                    return '2';
                }
            }
			elseif($client_preference->sms_provider == 6) //for AfricasTalking gateway
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->africasTalking_sms($to,$body,$crendentials);
            }
			elseif($client_preference->sms_provider == 7) //for Vonage
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->vonage_sms($to,$body,$crendentials);
            }
            elseif($client_preference->sms_provider == 9) //for Ethiopia
            {
            $crendentials = json_decode($client_preference->sms_credentials);
            $send = $this->ethiopia($to,$body,$crendentials);
            }
			elseif($client_preference->sms_provider == 10) //sms country
    		{
    		$crendentials = json_decode($client_preference->sms_credentials);
    		$send = $this->sms_country($to,$body,$crendentials);
    		}
			else{
                $client = new TwilioClient($sms_key, $sms_secret);
                $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
            }
        }
        catch(\Exception $e){
            \Log::info(['err' => $e->getMessage()]);
            return '2';
        }
        return '1';
	}





}
