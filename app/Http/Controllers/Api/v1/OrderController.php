<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\AhoyController;
use DB;
use Carbon\{Carbon, CarbonPeriod};
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser, Borzoe, OrderTrait, CartManager, DispatcherSlot, MargTrait, VendorTrait};
use GuzzleHttp\Client as GCLIENT;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Client\ShippoController;
use App\Http\Controllers\D4BDunzoController;
use App\Http\Controllers\DunzoController;
use App\Http\Controllers\Front\LalaMovesController;
use App\Http\Controllers\Front\QuickApiController;
use App\Http\Controllers\Front\TempCartController;
use App\Http\Controllers\ShiprocketController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\OrderStoreRequest;
use Illuminate\Support\Facades\Validator;
use Log;
use App\Http\Controllers\Client\BorzoeDeliveryController;
use App\Models\{Order, OrderProduct, UserDocs, SmsTemplate, UserRegistrationDocuments, OrderTax, Cart, CartAddon, CartProduct, CartProductPrescription, TempCart, TempCartProduct, TempCartAddon, Product, OrderProductAddon, ClientPreference, ClientCurrency, ClientLanguage, OrderVendor, OrderProductPrescription, UserAddress, CartCoupon, CartDeliveryFee, VendorOrderStatus, VendorOrderDispatcherStatus, OrderStatusOption, Vendor, LoyaltyCard, NotificationTemplate, User, Payment, SubscriptionInvoicesUser, UserDevice, Client, UserVendor, LuxuryOption, EmailTemplate, ProductVariantSet, CaregoryKycDoc, CategoryKycDocuments, VerificationOption, OrderLongTermServices, OrderLongTermServicesAddon, OrderLongTermServiceSchedule, WebStylingOption, Bid, CartBookingOption, CartRentalProtection, ClientDocument, InappNotification, JitsiMeeting, LawyerDocument, Notification, OrderNotificationsLogs, ProcessorProduct, OrderFiles, OrderStatusTime, OrderVendorProduct, ProductAvailability, VendorMargConfig};

use App\Models\AutoRejectOrderCron;

use App\Models\{VendorOrderCancelReturnPayment, Wallet};
use Illuminate\Support\Facades\Log as FacadesLog;

class OrderController extends BaseController
{
    use ApiResponser, CartManager, OrderTrait, DispatcherSlot, VendorTrait, MargTrait, Borzoe;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDeliveryFeeDispatcher($vendor_id)
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $customer = User::find(Auth::id());
                $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
                if ($cus_address) {
                    $tasks = array();
                    $vendor_details = Vendor::find($vendor_id);
                    $location[] = array(
                        'latitude' => $vendor_details->latitude ?? '',
                        'longitude' => $vendor_details->longitude ?? ''
                    );
                    $location[] = array(
                        'latitude' => $cus_address->latitude ?? '',
                        'longitude' => $cus_address->longitude ?? ''
                    );
                    $postdata =  ['locations' => $location];
                    $client = new GClient([
                        'headers' => [
                            'personaltoken' => $dispatch_domain->delivery_service_key,
                            'shortcode' => $dispatch_domain->delivery_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);
                    $url = $dispatch_domain->delivery_service_key_url;
                    $res = $client->post(
                        $url . '/api/get-delivery-fee',
                        ['form_params' => ($postdata)]
                    );
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['message'] == 'success') {
                        return $response['total'];
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
    # check if last mile delivery on
    public function checkIfLastMileOn()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }
    public function postPlaceOrder(Request $request)
    {

        try {
            $action = ($request->has('type')) ? $request->type : 'delivery';
            $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();
            if (isset($set_template)  && $set_template->template_id == 9  && $action != 'p2p') {
                $action = 'delivery';
            }

            if ($request->has('type') && $request->type != 'delivery') {
                $rules = [
                    'payment_option_id' => 'required'
                ];
                $validator = Validator::make($request->all(), $rules, [
                    'payment_option_id.required' => __('Payment Option is required')
                ]);
            } else {
                $rules = [
                    'address_id'        => 'required:exists:user_addresses,id',
                    'payment_option_id' => 'required'
                ];
                $validator = Validator::make($request->all(), $rules, [
                    'address_id.required' => __('address is required'),
                    'payment_option_id.required' => __('Payment Option is required')
                ]);
            }

            if ($validator->fails()) {
                foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                    $errors['error'] = __($error_value[0]);
                    return response()->json($errors, 422);
                }
            }
            $rate = 0;
            $total_amount = 0;
            $total_discount = 0;
            $taxable_amount = 0;
            $payable_amount = 0;
            $new_vendor_taxable_amount = 0;
            $additional_price = 0;
            $tax_category_ids = [];
            $user = Auth::user();
            $language_id = $user->language ?? 1;
            $latitude = '';
            $longitude = '';
            $Order_bid_discount = 0;
            $daysCnt = '';
            $totalFreeDeliveryCharges = 0;
            if ($user) {
                DB::beginTransaction();

                $client_timezone = DB::table('clients')->first('timezone');

                if ($user) {
                    $timezone = $user->timezone ??  $client_timezone->timezone;
                } else {
                    $timezone = $client_timezone->timezone ?? ($user ? $user->timezone : 'Asia/Kolkata');
                }

                if ($action == 'takeaway' || $action == 'dine_in') {
                    $latitude = $user->latitude ?? '';
                    $longitude = $user->longitude ?? '';
                }

                $subscription_features = array();
                $now = Carbon::now()->toDateTimeString();
                $user_subscription = SubscriptionInvoicesUser::with('features')
                    ->select('id', 'user_id', 'subscription_id')
                    ->where('user_id', $user->id)
                    ->where('end_date', '>', $now)
                    ->orderBy('end_date', 'desc')->first();
                // if ($user_subscription) {
                //     foreach ($user_subscription->features as $feature) {
                //         $subscription_features[] = $feature->feature_id;
                //     }
                // }
                $loyalty_amount_saved = 0;
                $redeem_points_per_primary_currency = '';
                $loyalty_card = LoyaltyCard::where('status', '0')->first();
                if ($loyalty_card) {
                    $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
                }
                $client_preference = ClientPreference::first();

                $editlimit_datetime = Carbon::now()->toDateTimeString();
                $additionalPreferences = getAdditionalPreference(['is_tax_price_inclusive', 'is_gift_card', 'is_service_product_price_from_dispatch', 'order_edit_before_hours', 'is_show_vendor_on_subcription', 'is_service_price_selection']);
                $is_service_product_price_from_dispatch = 0;
                if (($action == 'on_demand') && ($additionalPreferences['is_service_product_price_from_dispatch'] == 1)) {
                    $on_demand_price_selection_type = ($request->has('on_demand_price_selection_type')) ? $request->on_demand_price_selection_type : 'vendor';
                    $getOnDemandPricingRule = getOnDemandPricingRule($action, $on_demand_price_selection_type, $additionalPreferences);
                    $is_service_product_price_from_dispatch = $getOnDemandPricingRule['is_price_from_freelancer'];
                }
                $additionalPreferences = (object) $additionalPreferences;

                $order_edit_before_hours =  @$additionalPreferences->order_edit_before_hours;
                $editlimit_datetime = Carbon::now()->addHours($order_edit_before_hours)->toDateTimeString();
                // if ($client_preference->verify_email == 1) {
                //     if ($user->is_email_verified == 0) {
                //         return response()->json(['error' => 'Your account is not verified.'], 404);
                //     }
                // }
                if ($client_preference->verify_phone == 1) {
                    if ($user->is_phone_verified == 0 && $user->phone_number) {
                        return response()->json(['error' => 'Your phone is not verified.'], 404);
                    }
                }
                if ($request->has('type') && $request->type == 'delivery') {
                    $user_address = UserAddress::where('id', $request->address_id)->first();
                    if (!$user_address) {
                        return response()->json(['error' => 'Invalid address id.'], 404);
                    }
                }
                if (isset($client_preference->stop_order_acceptance_for_users) && ($client_preference->stop_order_acceptance_for_users == 1)) {
                    return $this->errorResponse(__('Sorry! We are not accepting orders right now.'), 400);
                }

                $luxury_option = LuxuryOption::where('title', $action)->first();
                $cart = Cart::where('user_id', $user->id)->with(['editingOrder.orderStatusVendor', 'cartvendor'])->first();

                FacadesLog::info([' $luxury_option ' => $luxury_option]);
                if ($cart) {

                    // $loyalty_points_used=0;
                    // $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
                    // if ($order_loyalty_points_earned_detail) {
                    //     $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                    //     if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                    //         $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                    //     }
                    // }


                    $customerCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
                    $clientCurrency = ClientCurrency::where('is_primary', '=', 1)->first();

                    $cart_products = CartProduct::with(['product.pimage', 'product.variants', 'product.taxCategory.taxRate', 'coupon' => function ($query) use ($cart) {
                        $query->where('cart_id', $cart->id);
                    }, 'coupon.promo', 'product.addon', 'vendorProducts.productVariantByRoles'])->where('cart_id', $cart->id)->where('is_cart_checked', 1)->where('status', [0, 1])->orderBy('created_at', 'asc')->whereHas('product', function ($q) {
                        $q->where('is_live', 1);
                    })->get();
                    //for cashback flow
                    $cashback_amount = 0;
                    if (!empty($cart_products[0])) {
                        foreach ($cart_products as $cart_product) {
                            $cashback = Product::where('id', $cart_product->product_id)->first()->cashback;
                            $cashback_amount += $cart_product->quantity * $cashback;
                        }
                    }
                    $total_subscription_discount = $total_delivery_fee = $total_service_fee = 0;
                    $total_subscription_discount = 0;

                    if (!empty($cart_products[0]) && $cart_products[0]->luxury_option_id == "4") {
                        $additional_price = isset($cart_products[0]['product']['variants'][0])  && $cart_products[0]['product']['variants'][0]->incremental_price_per_min > 0 ? ($cart_products[0]->additional_increments_hrs_min / $cart_products[0]['product']['variants'][0]->incremental_price_per_min) : 0;
                    }
                    /* calculate total fixed fee amount */
                    // pr($cart_products[0]->additional_increments_hrs_min);
                    //    pr($additional_price);
                    $loyaltyCheck = $this->getOrderLoyalityAmount($user, '');
                    $loyalty_amount_saved = $loyaltyCheck->loyalty_amount_saved;
                    $loyalty_points_used =  $loyaltyCheck->loyalty_points_used;



                    if (isset($cart->editingOrder) && !empty($cart->editingOrder)) {
                        $order = Order::where('id', $cart->editingOrder->id)->first();
                        if ((strtotime($order->scheduled_date_time) - strtotime($editlimit_datetime)) < 0) {
                            return $this->errorResponse(__("Order can only be edited before Time limit of " . $order_edit_before_hours . " Hours from Scheduled date."), 400);
                        }
                        $VendorOrderStatus = VendorOrderStatus::where('order_id', $order->id)->whereNotIn('order_status_option_id', [1, 2])->count();
                        $order_vendor_status_error = 0;
                        foreach ($cart->editingOrder->orderStatusVendor as $key => $status) {
                            if ($status->order_status_option_id  > 2) {
                                $order_vendor_status_error = 1;
                            }
                        }

                        if ($VendorOrderStatus > 0 || $order_vendor_status_error == 1) {
                            return $this->errorResponse(__("You can not edit this order. Either order is in processed or in processing. Please discard order editing."), 400);
                        }
                        OrderProduct::where('order_id', $order->id)->delete();
                        OrderProductPrescription::where('order_id', $order->id)->delete();
                        OrderTax::where('order_id', $order->id)->delete();
                        VendorOrderStatus::where('order_id', $order->id)->delete();

                        if (!empty($cart->cartvendor)) {
                            $array_cart_vendors = array();
                            foreach ($cart->cartvendor as $cartvendor) {
                                $array_cart_vendors[] = $cartvendor->vendor_id;
                            }
                            if (count($array_cart_vendors) > 0) {
                                $noincartVendors = OrderVendor::where('order_id', $cart->editingOrder->id)->whereNotIn('vendor_id', $array_cart_vendors)->get();
                                foreach ($noincartVendors as $noincartVendor) {
                                    OrderVendor::where('order_id', $cart->editingOrder->id)->where('vendor_id', $noincartVendor->vendor_id)->delete();
                                    if ($noincartVendor->dispatch_traking_url != '' && $noincartVendor->dispatch_traking_url != NULL) {
                                        $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $noincartVendor->dispatch_traking_url);
                                        $response = Http::get($dispatch_traking_url);
                                    }
                                }
                            }
                        }

                        $order->is_edited = 1;
                    } else {
                        $order = new Order;
                        $order->order_number = generateOrderNo();
                    }

                    $order->user_id = $user->id;
                    $order->address_id = $request->address_id;
                    $order->total_other_taxes = $cart->total_other_taxes;
                    $order->payment_option_id = $request->payment_option_id;
                    $order->specific_instructions = $request->specific_instructions;
                    $order->comment_for_pickup_driver = $cart->comment_for_pickup_driver ?? null;
                    $order->comment_for_dropoff_driver = $cart->comment_for_dropoff_driver ?? null;
                    $order->comment_for_vendor = $cart->comment_for_vendor ?? null;
                    $order->schedule_pickup = $cart->schedule_pickup ?? null;
                    $order->schedule_dropoff = $cart->schedule_dropoff ?? null;
                    $order->additional_price = $additional_price ?? null;
                    // $order->specific_instructions = $cart->specific_instructions ?? null;
                    $order->specific_instructions = $request->specific_instructions ?? null;

                    $order->is_gift = $request->is_gift ?? 0;
                    $order->user_latitude = $latitude ? $latitude : null;
                    $order->user_longitude = $longitude ? $longitude : null;

                    $total_taxes = 0;
                    if ($cart->total_other_taxes != '') {
                        foreach (explode(",", $cart->total_other_taxes) as $row) {
                            $row1 = explode(":", $row);
                            $total_taxes += (float)$row1[1];
                        }
                    }
                    $order->taxable_amount =  decimal_format($total_taxes);
                    $order->is_postpay = (isset($request->is_postpay)) ? $request->is_postpay : 0;
                    // $order->platform_fee = (isset($request->platform_fee))?$request->platform_fee:0;
                    $order->pick_drop_order_number = $request->pick_drop_order_number ?? null;
                    //here
                    $order->cashback_recieved = $cashback_amount;
                    $order->save();
                    //add order id in client document in case of cod
                    if($order->payment_option_id == 1){
                        ClientDocument::where('client_id', Auth::user()->id)
                        ->whereNull('order_id')
                        ->update(['order_id' => $order->id]);
                    }
                    $is_long_term_order = 0;

                    /* Updating order prescription if any */
                    $cart_prescriptions = CartProductPrescription::where('cart_id', $cart->id)->get();
                    foreach ($cart_prescriptions as $cart_prescription) {
                        $order_prescription = new OrderProductPrescription();
                        $order_prescription->order_id = $order->id;
                        $order_prescription->vendor_id = $cart_prescription->vendor_id;
                        $order_prescription->product_id = $cart_prescription->product_id;
                        $order_prescription->prescription = $cart_prescription->getRawOriginal('prescription');
                        $order_prescription->save();
                    }

                    $total_fixed_fee_amount = 0.00;
                    $pro_vendors = array();
                    foreach ($cart_products as $row) {
                        if (!in_array($row->vendor_id, $pro_vendors)) {
                            $pro_vendors[] = $row->vendor_id;
                            $total_fixed_fee_amount += Vendor::find($row->vendor_id)->fixed_fee_amount;
                        }
                    }
                    $opt_quantity_price = 0;
                    $total_container_charges = 0;
                    $fixed_fee_amount = 0.00;
                    $vendor_total_container_charges = 0;
                    $security_amount = 0.00;

                    $slot_based_price = 0;
                    $deliveryfeeOnCoupon = 0;
                    $rentalProtectionPrice = 0;
                    $bookingOptionPrice = 0;
                    foreach ($cart_products->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                        $delivery_fee = 0;
                        $deliver_charge = $delivery_fee_charges = 0.00;
                        $delivery_count = 0;
                        $product_taxable_amount = 0;
                        $vendor_products_total_amount = 0;
                        $vendor_payable_amount = 0;
                        $only_products_amount = 0;
                        $vendor_markup_amount = 0;
                        $vendor_discount_amount = 0;
                        $is_restricted = 0;
                        $bid_vendor_discount = 0;
                        $deliveryfeeOnCoupon = 0;
                        $vendor_service_fee_percentage_amount = 0;

                        $passbase_check = VerificationOption::where(['code' => 'passbase', 'status' => 1])->first();
                        if (isset($cart->editingOrder) && !empty($cart->editingOrder)) {
                            $order_vendor = OrderVendor::where('order_id', $cart->editingOrder->id)->where('vendor_id', $vendor_id)->first();
                            if (!empty($order_vendor)) {
                                $order_vendor->web_hook_code = $order_vendor->web_hook_code;
                            } else {
                                $order_vendor = new OrderVendor();
                            }
                        } else {
                            $order_vendor = new OrderVendor();
                        }
                        $vendor_subcription_lnvoices_id = '';

                        if ($client_preference->subscription_mode == '1' &&  $additionalPreferences->is_show_vendor_on_subcription == 1) {
                            $vendor_on_subcription = $this->getVendorActiveSubscription($vendor_id);
                            if ($vendor_on_subcription) {
                                $vendor_subcription_lnvoices_id =   $vendor_on_subcription->id;
                            }
                        }

                        $order_vendor->status = 0;
                        $order_vendor->user_id = $user->id;
                        $order_vendor->order_id = $order->id;
                        $order_vendor->vendor_id = $vendor_id;
                        $order_vendor->subscription_invoices_vendor_id = $vendor_subcription_lnvoices_id;
                        $order_vendor->vendor_dinein_table_id = $vendor_cart_products->unique('vendor_dinein_table_id')->first()->vendor_dinein_table_id;
                        $order_vendor->save();
                        foreach ($vendor_cart_products as $vendor_cart_product) {
                            // @dd($vendor_cart_product->productVariantByRoles);
                            if (!empty($vendor_cart_product->slot_price)) {
                                $slot_based_price += $vendor_cart_product->slot_price;
                            }
                            if ((isset($client_preference->is_hyperlocal)) && ($client_preference->is_hyperlocal == 1) && !empty($latitude) && !empty($longitude)) {
                                $serviceArea =  $order_vendor->vendor->where('id', $order_vendor->vendor_id)->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                                    $query->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                                })->first();

                                if (!isset($serviceArea)) {
                                    DB::rollback();
                                    return $this->errorResponse(__('Products for this vendor are not deliverable at your area. Please change address or remove product.'), 400);
                                }

                                if (($client_preference->slots_with_service_area == 1) && ($vendor_cart_product->vendor->show_slot == 0)) {
                                    $serviceArea = $vendor_cart_product->vendor->where(function ($query) use ($latitude, $longitude) {
                                        $query->whereHas('slot.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                            $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                        })
                                            ->orWhereHas('slotDate.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                                $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                            });
                                    })->where('id', $vendor_id)->get();

                                    if ($serviceArea->isEmpty()) {
                                        DB::rollback();
                                        return $this->errorResponse(__('Products for this vendor are not deliverable at your area. Please change address or remove product.'), 400);
                                    }
                                }
                            }

                            if ($is_restricted == 0 && $passbase_check && isset($vendor_cart_product->product) && $vendor_cart_product->product->age_restriction == 1) {
                                $is_restricted = 1;
                            }
                            $variant = $vendor_cart_product->product->variants->where('id', $vendor_cart_product->variant_id)->first();
                            $quantity_price = 0;
                            $divider = (empty($vendor_cart_product->doller_compare) || $vendor_cart_product->doller_compare < 0) ? 1 : $vendor_cart_product->doller_compare;
                            $price_in_currency = $variant->price / $divider;
                            // change product price when is_service_product_price_from_dispatch on

                            if (($action == 'on_demand') && $is_service_product_price_from_dispatch == 1) {
                                $price_in_currency = $vendor_cart_product->dispatch_agent_price / $divider;
                            }
                            $container_charges_in_currency = $variant->container_charges / $divider;
                            $price_container_charges = $variant->container_charges;
                            $price_in_dollar_compare = $price_in_currency * $clientCurrency->doller_compare;
                            $container_charges_in_dollar_compare = $container_charges_in_currency * $clientCurrency->doller_compare;


                            $daysCountRecurring       = 1;

                            if ($vendor_cart_product->recurring_day_data && !empty($vendor_cart_product->recurring_day_data)) {
                                $date       = count(explode(",", $vendor_cart_product->recurring_day_data));
                                $daysCountRecurring =  $date;
                            }


                            if (($luxury_option->id == 4) || ($luxury_option->id == 9)) {
                                $security_amount += $vendor_cart_product->product->security_amount;
                            }


                            $quantity_price = ($price_in_dollar_compare * $vendor_cart_product->quantity) * $daysCountRecurring;
                            $quantity_container_charges = $container_charges_in_dollar_compare * $vendor_cart_product->quantity;

                            $total_container_charges = $total_container_charges + $quantity_container_charges;

                            $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price + $price_container_charges;
                            $vendor_markup_amount = $vendor_markup_amount + $variant->markup_price;
                            $vendor_payable_amount = $vendor_payable_amount + $quantity_price + $quantity_container_charges;
                            $only_products_amount += $quantity_price;
                            $vendor_total_container_charges = $vendor_total_container_charges + $quantity_container_charges;

                            $payable_amount = $payable_amount + $quantity_price + $quantity_container_charges;
                            $productAddon_price = 0;
                            if (!empty($vendor_cart_product->addon)) {
                                foreach ($vendor_cart_product->addon as $ck => $addon) {
                                    $opt_quantity_price = 0;
                                    $opt_price_in_currency = $addon->option->price;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                    $opt_quantity_price = $opt_price_in_doller_compare *  $vendor_cart_product->quantity;
                                    $total_amount = $total_amount + $opt_quantity_price;
                                    $productAddon_price = $productAddon_price + $opt_quantity_price;
                                    $payable_amount = $payable_amount + $opt_quantity_price;
                                    $vendor_payable_amount = $vendor_payable_amount + $opt_quantity_price;
                                    $vendor_products_total_amount = $vendor_products_total_amount + $opt_quantity_price;
                                }
                            }

                            if (!empty($cart->rentalProtection)) {
                                foreach ($cart->rentalProtection as $protection) {
                                    $protection_price_in_currency = $protection->rentalProtection->price ?? 0;
                                    $rentalProtectionPrice = $protection_price_in_currency * $clientCurrency->doller_compare;
                                    $total_amount += $rentalProtectionPrice;
                                    $productAddon_price += $rentalProtectionPrice;
                                    $payable_amount += $rentalProtectionPrice;
                                    $vendor_payable_amount += $rentalProtectionPrice;
                                    $vendor_products_total_amount += $rentalProtectionPrice;
                                }
                            }
                            if (!empty($cart->bookingOption)) {
                                foreach ($cart->bookingOption as $option) {
                                    $option_price_in_currency = $option->bookingOption->price ?? 0;
                                    $bookingOptionPrice = $option_price_in_currency * $clientCurrency->doller_compare;
                                    $total_amount += $bookingOptionPrice;
                                    $productAddon_price += $bookingOptionPrice;
                                    $payable_amount += $bookingOptionPrice;
                                    $vendor_payable_amount += $bookingOptionPrice;
                                    $vendor_products_total_amount += $bookingOptionPrice;
                                }
                            }

                            $vendor_taxable_amount = 0;
                            if (isset($vendor_cart_product->product->taxCategory)) {
                                foreach ($vendor_cart_product->product->taxCategory->taxRate as $tax_rate_detail) {
                                    if (!in_array($tax_rate_detail->id, $tax_category_ids)) {
                                        $tax_category_ids[] = $tax_rate_detail->id;
                                    }
                                    $rate = $tax_rate_detail->tax_rate;
                                    $tax_amount = ($price_in_dollar_compare * $rate) / 100;
                                    $product_tax = ($quantity_price + $productAddon_price) * $rate / 100;
                                    $taxable_amount = $taxable_amount + $product_tax;
                                    $product_taxable_amount += $product_tax;
                                    //$payable_amount = $payable_amount + $product_tax;

                                }
                            }
                            if ($action == 'delivery' || $action == 'on_demand') {
                                $deliver_fee_data = CartDeliveryFee::where('cart_id', $vendor_cart_product->cart_id)->where('vendor_id', $vendor_cart_product->vendor_id)->first();
                                if ((!empty($vendor_cart_product->product->Requires_last_mile)) && ($vendor_cart_product->product->Requires_last_mile == 1) || isset($deliver_fee_data)) {
                                    $order_vendor->shipping_delivery_type = $deliver_fee_data->shipping_delivery_type ?? 'D';
                                    $order_vendor->courier_id = $deliver_fee_data->courier_id ?? 0;

                                    if ($deliver_fee_data):
                                        $delivery_fee  = $deliver_fee_data->delivery_fee ?? 0.00;
                                        $delivery_duration  = $deliver_fee_data->delivery_duration ?? 0;
                                        $delivery_distance  = $deliver_fee_data->delivery_distance ?? 0.00;
                                    endif;

                                    if (!empty($delivery_fee) && $delivery_count == 0) {
                                        $delivery_count = 1;
                                        $vendor_cart_product->delivery_fee = decimal_format($delivery_fee);
                                        // $payable_amount = $payable_amount + $delivery_fee;

                                        $delivery_fee_charges = $delivery_fee;
                                        $latitude = $request->header('latitude');
                                        $longitude = $request->header('longitude');
                                        $vendor_cart_product->vendor = $this->getVendorDistanceWithTime($latitude, $longitude, $vendor_cart_product->vendor, $client_preference);
                                        $order_vendor->order_pre_time = ($vendor_cart_product->vendor->order_pre_time > 0) ? $vendor_cart_product->vendor->order_pre_time : 0;

                                        if ($delivery_duration > 0) {
                                            $order_vendor->user_to_vendor_time = intval($delivery_duration);
                                        } else if ($vendor_cart_product->vendor->timeofLineOfSightDistance > 0) {

                                            //$OrderVendor->order_pre_time = ($vendor_cart_product->vendor->order_pre_time > 0) ? $vendor_cart_product->vendor->order_pre_time : 0;
                                            if ($order_vendor->order_pre_time)
                                                $order_vendor->user_to_vendor_time = $vendor_cart_product->vendor->timeofLineOfSightDistance - $order_vendor->order_pre_time;
                                        }
                                    }
                                }
                            }


                            // $taxable_amount += $product_taxable_amount;
                            $vendor_taxable_amount +=  decimal_format($taxable_amount);
                            //$total_amount += ($vendor_cart_product->quantity * $variant->price) + ($vendor_cart_product->quantity * $variant->container_charges);
                            $variant_price = $variant->price;

                            if (@$luxury_option->id == 9 && @$variant->month_price) {
                                $schedule_days = $prod->additional_increments_hrs_min / 24;
                                if ($schedule_days >= 7 && $schedule_days < 30) {
                                    $variant_price = $variant->week_price * ($vendor_cart_product->additional_increments_hrs_min / (60 * 24));
                                } elseif ($schedule_days >= 30) {
                                    $variant_price = $variant->month_price * ($vendor_cart_product->additional_increments_hrs_min / (60 * 24));
                                } else {
                                    $variant_price = $variant->price * ($vendor_cart_product->additional_increments_hrs_min / (60 * 24));
                                }
                            }

                            $is_price_buy_driver = 0;
                            if (($action == 'on_demand') && ($is_service_product_price_from_dispatch == 1)) {
                                $variant_price = $vendor_cart_product->dispatch_agent_price;
                                $is_price_buy_driver = 1;
                            }
                            $total_amount += ($vendor_cart_product->quantity * $variant_price);
                            $order_product = new OrderProduct;
                            $order_product->order_vendor_id = $order_vendor->id;
                            $order_product->order_id = $order->id;

                            //Multiply by Recurring product item days
                            $order_product->price = $variant->price * $daysCountRecurring;

                            if ($action == 'p2p') {
                                $order_product->price = $request->amount ?? 0;
                            } else {
                                $order_product->price = $variant->price * $daysCountRecurring;
                            }

                            $order_product->bid_number = @$vendor_cart_product->bid_number ?? null;
                            $order_product->bid_discount = @$vendor_cart_product->bid_discount ?? null;
                            $order_product->additional_increments_hrs_min = @$vendor_cart_product->additional_increments_hrs_min;
                            $order_product->start_date_time = $vendor_cart_product->start_date_time;
                            $order_product->end_date_time = $vendor_cart_product->end_date_time;
                            $order_product->total_booking_time = @$vendor_cart_product->total_booking_time;
                            $order_product->markup_price = $variant->markup_price;
                            $order_product->container_charges = $variant->container_charges;
                            $order_product->taxable_amount = $product_taxable_amount;
                            $order_product->quantity = $vendor_cart_product->quantity;
                            $order_product->vendor_id = $vendor_cart_product->vendor_id;
                            $order_product->product_id = $vendor_cart_product->product_id;
                            $order_product->created_by = $vendor_cart_product->created_by;
                            $order_product->user_product_order_form = $vendor_cart_product->user_product_order_form;
                            $order_product->variant_id = $vendor_cart_product->variant_id;
                            $order_product->dispatcher_status_option_id = 1;
                            $order_product->order_status_option_id = 1;
                            $order_product->product_delivery_fee = isset($vendor_cart_product->product_delivery_fee) ? $vendor_cart_product->product_delivery_fee : 0;
                            $product_variant_sets = '';


                            $order_product->is_price_buy_driver = $is_price_buy_driver;


                            $order_product->specific_instruction = $vendor_cart_product->specific_instruction;

                            $order_product->schedule_type = $vendor_cart_product->schedule_type ?? null;
                            $order_product->scheduled_date_time = $vendor_cart_product->schedule_type == 'schedule' ? $vendor_cart_product->scheduled_date_time : null;
                            $order_product->schedule_slot = !empty($vendor_cart_product->schedule_slot) ? $vendor_cart_product->schedule_slot : '';
                            $order_product->dispatch_agent_id = !empty($vendor_cart_product->dispatch_agent_id) ? $vendor_cart_product->dispatch_agent_id : null;

                            if (@$vendor_cart_product->bid_number) {
                                Bid::where('id', $vendor_cart_product->bid_number)->update(['status' => 1]);
                                $bid_vendor_discount += ((($order_product->price * $vendor_cart_product->quantity) * $vendor_cart_product->bid_discount) / 100);
                            }

                            if (isset($vendor_cart_product->variant_id) && !empty($vendor_cart_product->variant_id)) {
                                $var_sets = ProductVariantSet::where('product_variant_id', $vendor_cart_product->variant_id)->where('product_id', $vendor_cart_product->product->id)
                                    ->with([
                                        'variantDetail.trans' => function ($qry) use ($language_id) {
                                            $qry->where('language_id', $language_id);
                                        },
                                        'optionData.trans' => function ($qry) use ($language_id) {
                                            $qry->where('language_id', $language_id);
                                        }
                                    ])->get();
                                if (count($var_sets)) {
                                    foreach ($var_sets as $set) {
                                        if (isset($set->variantDetail) && !empty($set->variantDetail)) {
                                            $product_variant_set = @$set->variantDetail->trans->title . ":" . @$set->optionData->trans->title . ", ";
                                            $product_variant_sets .= $product_variant_set;
                                        }
                                    }
                                }
                            }

                            $order_product->product_variant_sets = $product_variant_sets;
                            if (!empty($vendor_cart_product->product->title))
                                $vendor_cart_product->product->title = $vendor_cart_product->product->title;
                            elseif (empty($vendor_cart_product->product->title)  && !empty($vendor_cart_product->product->translation))
                                $vendor_cart_product->product->title = $vendor_cart_product->product->translation[0]->title;
                            else
                                $vendor_cart_product->product->title = $vendor_cart_product->product->sku;

                            $order_product->product_name = $vendor_cart_product->product->title ?? $vendor_cart_product->product->sku;
                            $order_product->product_dispatcher_tag = $vendor_cart_product->product->tags;
                            if ($vendor_cart_product->product->pimage) {
                                $order_product->image = $vendor_cart_product->product->pimage->first() ? $vendor_cart_product->product->pimage->first()->path : '';
                            }


                            $order_product->slot_id = !empty($vendor_cart_product->slot_id) ? $vendor_cart_product->slot_id : null;



                            $order_product->delivery_date = !empty($vendor_cart_product->delivery_date) ? $vendor_cart_product->delivery_date : null;



                            $order_product->slot_price = !empty($vendor_cart_product->slot_price) ? $vendor_cart_product->slot_price : null;


                            $order_product->dispatch_agent_id = ! empty($vendor_cart_product->dispatch_agent_id) ? $vendor_cart_product->dispatch_agent_id : null;

                            $order_product->schedule_type = $vendor_cart_product->schedule_type ?? null;
                            $order_product->schedule_slot = ! empty($vendor_cart_product->schedule_slot) ? $vendor_cart_product->schedule_slot : '';
                            $order_product->scheduled_date_time = $vendor_cart_product->schedule_type == 'schedule' ? $vendor_cart_product->scheduled_date_time : null;

                            if (($luxury_option->id == 4) || ($luxury_option->id == 9)) {
                                $order_product->security_amount = $vendor_cart_product->product->security_amount;
                            }

                            $order_product->save();

                            // Assuming $vendor_cart_product holds the relevant data

                            $startDateTime = date('Y-m-d', strtotime($vendor_cart_product->start_date_time));
                            $endDateTime = date('Y-m-d', strtotime($vendor_cart_product->end_date_time));
                            if (($luxury_option->id == 4) || ($luxury_option->id == 9)) {

                                $data = [
                                    'memo' => __('Booked for order #') . $order->order_number,
                                    'variant_id' => $order_product->variant_id,
                                    'product_id' => $order_product->product_id,
                                    'start_date' => $order_product->start_date_time,
                                    'order_user_id' => $order->user_id,
                                    'order_vendor_id' => $order_product->vendor_id,
                                    'end_date' => $order_product->end_date_time
                                ];
                                $res =  $this->bookingSlot($data, $order_product->id, $order->id);
                            }

                            ProductAvailability::where('product_id', $vendor_cart_product->product_id)
                                ->whereBetween('date_time', [$startDateTime, $endDateTime])->update(['not_available' => 1]);

                            // Recurring Booking Functionity

                            if (!empty($vendor_cart_product->recurring_booking_time)) {

                                $user_timezone          =   $timezone;
                                $recurring_booking_time =   convertDateTimeInTimeZone($vendor_cart_product->recurring_booking_time, $user_timezone, 'H:i');

                                $RecurringServiceSchedule = array();
                                // No Nee other action
                                if (@$vendor_cart_product->recurring_booking_type) {
                                    $Recurring_quantity     = $vendor_cart_product->quantity;
                                    $recurring_day_data     = $vendor_cart_product->recurring_day_data;
                                    $recurring_day_data     = explode(",", $recurring_day_data);
                                    $ndate                  = convertDateTimeInClientTimeZone(Carbon::now());
                                    $recurring_booking_time = convertDateTimeInTimeZone($vendor_cart_product->recurring_booking_time, $user_timezone, 'H:i');
                                    for ($x = 0; $x < count($recurring_day_data); $x++) {
                                        $date           = $recurring_day_data[$x];
                                        $newDate        = $date . ' ' . $recurring_booking_time;
                                        $RecurringServiceSchedule[] = [
                                            'order_vendor_product_id' => $order_product->id,
                                            'schedule_date'           => $newDate,
                                            'type'                    => 2,
                                            'order_number'            => $order->order_number
                                        ];
                                    }
                                }
                                if (!empty($RecurringServiceSchedule)) {
                                    OrderLongTermServiceSchedule::insert($RecurringServiceSchedule);
                                }
                            }



                            if ($vendor_cart_product->product->is_long_term_service && $vendor_cart_product->LongTermProducts) {
                                $is_long_term_order = 1;
                                $service_start_date =  $vendor_cart_product->service_start_date ??   Carbon::now()->format('Y-m-d H:i:s');
                                $service_end_date = Carbon::parse($service_start_date)->addMonths($vendor_cart_product->product->service_duration)->setTimezone('UTC')->format('Y-m-d H:i:s');
                                $LongTermSericeData = [
                                    'order_product_id'  => $order_product->id,
                                    'user_id'           => $user->id,
                                    'service_quentity'  => $vendor_cart_product->LongTermProducts->quantity ?? 1,
                                    'service_day'       => $vendor_cart_product->service_day,
                                    'service_date'      => $vendor_cart_product->service_date,
                                    'service_start_date' => $service_start_date,
                                    'service_period'    => $vendor_cart_product->service_period,
                                    'service_end_date'  => $service_end_date,
                                    'service_product_id'         => $vendor_cart_product->LongTermProducts->product_id,
                                    'service_product_variant_id' => $vendor_cart_product->LongTermProducts->product_variant,
                                    'status'                => 0,
                                ];
                                $OrderLongTermServices  = OrderLongTermServices::create($LongTermSericeData);
                                if ($vendor_cart_product->LongTermProducts->addons->isNotEmpty()) {
                                    foreach ($vendor_cart_product->LongTermProducts->addons as $SAddon) {
                                        $LongTermSericeAddonData = [
                                            'order_long_term_services_id' => $OrderLongTermServices->id,
                                            'addon_id'                    => $SAddon->addon_id,
                                            'option_id'                   => $SAddon->option_id
                                        ];
                                        OrderLongTermServicesAddon::create($LongTermSericeAddonData);
                                    }
                                }
                                /** save long term service schedule */
                                $OrderLongTermServiceSchedule = array();;
                                $Service_quantity = $vendor_cart_product->LongTermProducts->quantity;
                                $start_service_date = Carbon::parse($vendor_cart_product->service_start_date)->format('Y-m-d');
                                $end_service_date   = Carbon::parse($vendor_cart_product->service_start_date)->addMonths($vendor_cart_product->product->service_duration);

                                if ($vendor_cart_product->service_period == 'days') {

                                    $end_service_date = Carbon::parse($vendor_cart_product->service_start_date)->addDays(($vendor_cart_product->LongTermProducts->quantity + 1));
                                    $period   = CarbonPeriod::create($start_service_date, $end_service_date);
                                    $entery = 1;
                                    foreach ($period as $key => $date) {
                                        if ($entery <= $Service_quantity) {
                                            $OrderLongTermServiceSchedule[] = [
                                                'order_long_term_services_id' => $OrderLongTermServices->id,
                                                'schedule_date'               => $date->format('Y-m-d') . ' ' . Carbon::parse($vendor_cart_product->start_date_time)->format('H:i:s'), //
                                            ];
                                            $entery++;
                                        }
                                    }
                                } elseif ($vendor_cart_product->service_period == 'week') {
                                    $end_service_date = Carbon::parse($vendor_cart_product->service_start_date)->addWeeks(($vendor_cart_product->LongTermProducts->quantity + 1));
                                    $period   = CarbonPeriod::create($start_service_date, $end_service_date);
                                    $entery = 1;
                                    foreach ($period as $key => $date) {
                                        $dayNumber = $date->dayOfWeek + 1; // get day number
                                        if ($vendor_cart_product->service_day == $dayNumber) {
                                            if ($entery <= $Service_quantity) {
                                                $OrderLongTermServiceSchedule[] = [
                                                    'order_long_term_services_id' => $OrderLongTermServices->id,
                                                    'schedule_date'               => $date->format('Y-m-d') . ' ' . Carbon::parse($vendor_cart_product->start_date_time)->format('H:i:s'), //
                                                ];
                                                $entery++;
                                            }
                                        }
                                    }
                                } elseif ($vendor_cart_product->service_period == 'months') {

                                    $end_service_date = Carbon::parse($vendor_cart_product->service_start_date)->addMonths(($vendor_cart_product->LongTermProducts->quantity + 1));

                                    if ($vendor_cart_product->service_date == 0) {

                                        $startdate =  Carbon::now()->endOfMonth()->format('Y-m-d');

                                        if (strtotime($startdate) < strtotime($start_service_date))
                                            $startdate = Carbon::now()->addMonths(1);

                                        $arrayDate = explode("-", $startdate);
                                        $newDate =  $arrayDate[0] . '-' . $arrayDate[1] . '-01';

                                        for ($i = 0; $i < $Service_quantity; $i++) {

                                            $OrderLongTermServiceSchedule[] = [
                                                'order_long_term_services_id' => $OrderLongTermServices->id,
                                                'schedule_date'               => Carbon::parse($newDate)->addMonths($i)->endOfMonth()->format('Y-m-d') . ' ' . Carbon::parse($vendor_cart_product->start_date_time)->format('H:i:s'), //
                                            ];
                                        }
                                    } else {

                                        $todayDate =  Carbon::now()->format('Y-m-d');
                                        $arrayDate = explode("-", $todayDate);
                                        $newDate =  $arrayDate[0] . '-' . $arrayDate[1] . '-' . $vendor_cart_product->service_date;
                                        $startdate = Carbon::parse($newDate)->format('Y-m-d');
                                        if (strtotime($startdate) < strtotime($start_service_date))
                                            $startdate = Carbon::parse($startdate)->addMonth();


                                        // $selected_date = $startdate->subMonth();

                                        for ($i = 0; $i < $Service_quantity; $i++) {

                                            $OrderLongTermServiceSchedule[] = [
                                                'order_long_term_services_id' => $OrderLongTermServices->id,
                                                'schedule_date'               => Carbon::parse($startdate)->addMonths($i)->format('Y-m-d') . ' ' . Carbon::parse($vendor_cart_product->start_date_time)->format('H:i:s'), //
                                            ];
                                        }
                                    }
                                }

                                if (!empty($OrderLongTermServiceSchedule)) {
                                    OrderLongTermServiceSchedule::insert($OrderLongTermServiceSchedule);
                                }
                            }

                            $cart_addons = CartAddon::where('cart_product_id', $vendor_cart_product->id)->get();
                            if ($cart_addons) {
                                foreach ($cart_addons as $cart_addon) {
                                    $orderAddon = new OrderProductAddon;
                                    $orderAddon->addon_id = $cart_addon->addon_id;
                                    $orderAddon->option_id = $cart_addon->option_id;
                                    $orderAddon->order_product_id = $order_product->id;
                                    $orderAddon->save();
                                }
                                if (($request->payment_option_id != 7) && ($request->payment_option_id != 6)) { // if not mobbex, payfast
                                    //        CartAddon::where('cart_product_id', $vendor_cart_product->id)->delete();
                                }
                            }
                        }
                        $coupon_id = null;
                        $coupon_name = null;



                        $actual_amount = $vendor_payable_amount;

                        if ($vendor_cart_product->coupon && !empty($vendor_cart_product->coupon->promo)) {
                            $coupon_id = $vendor_cart_product->coupon->promo->id;

                            if ($vendor_cart_product->coupon->promo->paid_by_vendor_admin == 0) {
                                $coupon_paid_by = 0;
                            }

                            $coupon_name = $vendor_cart_product->coupon->promo->name;

                            /* if ($vendor_cart_product->coupon->promo->allow_free_delivery) {
                                $total_discount += $delivery_fee;
                                $vendor_payable_amount -= $delivery_fee;
                                $vendor_discount_amount += $delivery_fee;
                            } */
                            //-------------Coupon Related discount calculations start here----------------------
                            //----fixed amount----------
                            if ($vendor_cart_product->coupon->promo->promo_type_id == 2) {
                                $coupon_discount_amount = $vendor_cart_product->coupon->promo->amount;
                                $total_discount += $coupon_discount_amount;
                                $vendor_payable_amount -= $coupon_discount_amount;
                                $vendor_discount_amount += $coupon_discount_amount;
                            } else {
                                //----Percent amount----------
                                $coupon_discount_amount = ($actual_amount * $vendor_cart_product->coupon->promo->amount / 100);
                                $final_coupon_discount_amount = $coupon_discount_amount * $clientCurrency->doller_compare;
                                $total_discount += $final_coupon_discount_amount;
                                $vendor_payable_amount -= $final_coupon_discount_amount;
                                $vendor_discount_amount += $final_coupon_discount_amount;
                            }
                            // add delivery fee in coupon if coupon has free delicery
                            if ($vendor_cart_product->coupon->promo->allow_free_delivery == 1) {
                                $vendor_discount_amount = $vendor_discount_amount +  $delivery_fee;
                                $vendor_payable_amount = $vendor_payable_amount - $delivery_fee;
                                $total_discount += $delivery_fee;
                                // $totalFreeDeliveryCharges += $delivery_fee;
                                $deliveryfeeOnCoupon = 1;
                            }

                            // if(isset($rate) && $total_discount > 0 ){
                            //    $discount = ($total_discount*$rate) / 100;
                            //    $vendor_taxable_amount -= $discount;
                            // }
                            //-------------Coupon Related discount calculations Ends here----------------------
                        }
                        //Start applying service fee on vendor products total
                        $service_fee_percentage_amount = 0;
                        if ($vendor_cart_product->vendor->service_fee_percent > 0) {
                            $service_fee_percentage_amount = (($actual_amount - $total_container_charges) * $vendor_cart_product->vendor->service_fee_percent) / 100;
                            $vendor_service_fee_percentage_amount = $vendor_service_fee_percentage_amount + $service_fee_percentage_amount;
                            $vendor_payable_amount += $service_fee_percentage_amount;
                            $payable_amount += $service_fee_percentage_amount;
                        }

                        if ($vendor_cart_product->vendor->fixed_service_charge > 0) {
                            // $vendor_service_fee_percentage_amount = ($vendor_payable_amount * $vendor_cart_product->vendor->service_fee_percent) / 100; // wrong percentage_amount
                            $service_fee_percentage_amount        = $vendor_cart_product->vendor->service_charge_amount;
                            $vendor_service_fee_percentage_amount = $vendor_service_fee_percentage_amount + $service_fee_percentage_amount;
                            $payable_amount += $service_fee_percentage_amount;
                            // $total_service_fee = $total_service_fee + $service_fee_percentage_amount;
                            $vendor_payable_amount += $service_fee_percentage_amount;
                        }

                        //End applying service fee on vendor products total
                        $total_service_fee = $total_service_fee + $service_fee_percentage_amount;
                        $order_vendor->service_fee_percentage_amount = $service_fee_percentage_amount;

                        $total_delivery_fee += $delivery_fee;
                        $vendor_payable_amount += $delivery_fee;
                        $vendor_payable_amount += $vendor_taxable_amount;

                        $order_vendor->coupon_id = $coupon_id;
                        $order_vendor->coupon_paid_by = $coupon_paid_by ?? 1;
                        $order_vendor->coupon_code = $coupon_name;
                        $order_vendor->order_status_option_id = 1;
                        $order_vendor->delivery_fee = $delivery_fee;
                        $order_vendor->discount_amount = $vendor_discount_amount;

                        if ($deliveryfeeOnCoupon)
                            $vendor_discount_amount =  $vendor_discount_amount - $delivery_fee;
                        // check if is_tax_price_inclusive is on than no tax
                        if (! $additionalPreferences->is_tax_price_inclusive) {
                            $new_vendor_taxable_amount = number_format((($actual_amount - $vendor_discount_amount) * $rate) / 100, 2);
                        } else {
                            $new_vendor_taxable_amount = number_format((($actual_amount - $vendor_discount_amount) * $rate) / (100 + $rate), 2);
                        }

                        $new_vendor_taxable_amount = str_replace(',', '', $new_vendor_taxable_amount);
                        $new_vendor_taxable_amount = floatval($new_vendor_taxable_amount);

                        $order_vendor->subtotal_amount = $actual_amount;
                        $order_vendor->payable_amount = $vendor_payable_amount + $total_fixed_fee_amount;
                        $order_vendor->total_markup_price = $vendor_markup_amount;
                        $order_vendor->taxable_amount = $new_vendor_taxable_amount;
                        $order_vendor->payment_option_id = $request->payment_option_id;
                        $order_vendor->total_container_charges = $vendor_total_container_charges;

                        $vendor_subs_disc_percent       = isset($vendor_cart_product->vendor->subscription_discount_percent) ? $vendor_cart_product->vendor->subscription_discount_percent : 0;
                        $deliveryfee_ifnot_discounted   = ($deliveryfeeOnCoupon == 0) ? $delivery_fee : 0;
                        $subs_discount_arr              = $this->calCulateSubscriptionDiscount($user->id, $deliveryfee_ifnot_discounted, ($vendor_payable_amount - $deliveryfee_ifnot_discounted), $vendor_subs_disc_percent);
                        $subs_discount_admin            = $subs_discount_arr['admin'] + $subs_discount_arr['delivery_discount'];
                        $subs_discount_vendor           = $subs_discount_arr['vendor'];


                        $order_vendor->subscription_discount_admin  = $subs_discount_admin;
                        $order_vendor->subscription_discount_vendor = $subs_discount_vendor;

                        $total_subscription_discount = $total_subscription_discount + $subs_discount_admin + $subs_discount_vendor;

                        $order_vendor->is_restricted = $is_restricted;
                        $order_vendor->bid_discount = $bid_vendor_discount ?? 0;
                        $Order_bid_discount += $bid_vendor_discount ?? 0;
                        $vendor_info = Vendor::where('id', $vendor_id)->first();
                        if ($vendor_info) {
                            if (isset($coupon_paid_by)) {
                                $actual_amount = $actual_amount - $vendor_discount_amount;
                            }
                            if (($vendor_info->commission_percent) != null && $actual_amount > 0) {
                                $actual_amountComm = $actual_amount - $vendor_markup_amount;
                                $order_vendor->admin_commission_percentage_amount = round($vendor_info->commission_percent * ($actual_amountComm / 100), 2);
                            }
                            if (($vendor_info->commission_fixed_per_order) != null && $actual_amount > 0) {
                                $order_vendor->admin_commission_fixed_amount = $vendor_info->commission_fixed_per_order;
                            }
                            if ($vendor_info->fixed_fee_amount > 0) {
                                $fixed_fee_amount = $fixed_fee_amount + $vendor_info->fixed_fee_amount;
                                $order_vendor->fixed_fee =  $vendor_info->fixed_fee_amount;
                            }
                        }
                        $order_vendor->save();
                        $order_status = new VendorOrderStatus();
                        $order_status->order_id = $order->id;
                        $order_status->vendor_id = $vendor_id;
                        $order_status->order_status_option_id = 1;
                        $order_status->order_vendor_id = $order_vendor->id;
                        $order_status->save();
                    }


                    $payable_amount = $payable_amount + $total_taxes + $additional_price + $slot_based_price;

                    $loyalty_points_earned = LoyaltyCard::getLoyaltyPoint($loyalty_points_used, $payable_amount);

                    // calculate subscription discount
                    if ($user_subscription) {
                        foreach ($user_subscription->features as $feature) {
                            if ($feature->feature_id == 1) {
                                $total_subscription_discount = $total_subscription_discount + $total_delivery_fee;
                            } elseif ($feature->feature_id == 2) {
                                $off_percentage_discount = ($feature->percent_value * $payable_amount / 100);
                                $total_subscription_discount = $total_subscription_discount + $off_percentage_discount;
                            }
                        }
                    }



                    if (@$vendor_cart_product->recurring_day_data && !empty($vendor_cart_product->recurring_day_data)) {
                        $date       = explode(",", $vendor_cart_product->recurring_day_data);
                        if ($vendor_cart_product->recurring_booking_type == 1 || $vendor_cart_product->recurring_booking_type == 2 || $vendor_cart_product->recurring_booking_type == 3 || $vendor_cart_product->recurring_booking_type == 4) {
                            $days_count     =  count($date);
                            $total_amount   =  decimal_format($total_amount * $days_count);
                        }
                    }


                    $total_discount = $total_discount + $total_subscription_discount;
                    // $order->total_amount = ($total_amount + $total_container_charges) - $Order_bid_discount??0;


                    $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
                    if ($action == 'p2p' && @$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                        $total_amount = $request->amount ?? 0;
                        $order->total_amount =  $total_amount;
                    } else {
                        $order->total_amount = ($total_amount + $total_container_charges) - $Order_bid_discount ?? 0;
                    }
                    $order->total_discount = $total_discount;
                    $payable_amount = $payable_amount + $total_delivery_fee - $total_discount - $totalFreeDeliveryCharges;

                    if ($loyalty_amount_saved > 0) {
                        if ($loyalty_amount_saved > $payable_amount) {
                            $loyalty_amount_saved = $payable_amount;
                            $loyalty_points_used = $payable_amount * $redeem_points_per_primary_currency;
                        }
                    }
                    $payable_amount = ($payable_amount + $fixed_fee_amount) - $loyalty_amount_saved;
                    $ex_gateways_wallet = [4, 36, 40, 41, 22]; // stripe,mycash,userede,openpay
                    $wallet_amount_used = 0;

                    if ($user->balanceFloat > 0) {
                        $wallet = $user->wallet;
                        $wallet_amount_used = $user->balanceFloat;
                        if ($wallet_amount_used > $payable_amount) {
                            $wallet_amount_used = $payable_amount;
                        }
                        $order->wallet_amount_used = $wallet_amount_used;
                        // Deduct wallet amount if payable amount is successfully done on gateway
                        if (($wallet_amount_used > 0) && (!in_array($request->payment_option_id, $ex_gateways_wallet))) {
                            $wallet->withdrawFloat($order->wallet_amount_used, ['Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>']);
                        }
                    }

                    $user_wallet = Wallet::where('holder_id', $user->id)->first();
                    try {
                        DB::beginTransaction();

                        if (isset($request->wallet_type) && $request->wallet_type == 1) {
                            $payable_amount = (float) $payable_amount;
                            $wallet_balance = (float) $user_wallet->balance;

                            if ($payable_amount > $wallet_balance) {
                                $payable_amount -= $wallet_balance;
                                $user_wallet->balance = 0.00;
                            } else {
                                $user_wallet->balance -= $payable_amount;
                                $payable_amount = 0.00;
                            }
                            $user_wallet->save();
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return response()->json(['error' => 'Transaction failed. Please try again.'], 500);
                    }

                    $tip_amount = 0;
                    if ((isset($request->tip)) && ($request->tip != '') && ($request->tip > 0)) {
                        $tip_amount = $request->tip;
                        $tip_amount = ($tip_amount / $customerCurrency->doller_compare) * $clientCurrency->doller_compare;
                        $order->tip_amount = decimal_format($tip_amount);
                    }

                    $client_timezone = DB::table('clients')->first('timezone');

                    if ($user) {
                        $timezone = $user->timezone ??  $client_timezone->timezone;
                    } else {
                        $timezone = $client_timezone->timezone ?? ($user ? $user->timezone : 'Asia/Kolkata');
                    }

                    $payable_amount = $payable_amount + $tip_amount + $security_amount;
                    $payable_amount = $payable_amount - $wallet_amount_used;


                    $order->total_service_fee = $total_service_fee;
                    $order->total_delivery_fee = $total_delivery_fee;
                    $order->loyalty_points_used = $loyalty_points_used;
                    $order->loyalty_amount_saved = $loyalty_amount_saved;

                    if ($action == 'p2p') {

                        // $order->loyalty_points_earned = $loyalty_points_earned['per_order_points'];
                        $order->loyalty_points_earned = NULL;
                        $order->loyalty_points_earned_order = $loyalty_card->per_order_points ?? 0;
                    } else {
                        $order->loyalty_points_earned = $loyalty_points_earned['per_order_points'] ?? 0;
                    }
                    $order->loyalty_membership_id = $loyalty_points_earned['loyalty_card_id'];
                    $order->scheduled_date_time = $cart->schedule_type == 'schedule' ? $cart->scheduled_date_time : null;
                    $order->scheduled_slot = $cart->scheduled_slot ?? null;
                    $order->dropoff_scheduled_slot = (($cart->dropoff_scheduled_slot) ? $cart->dropoff_scheduled_slot : null);
                    $order->subscription_discount = $total_subscription_discount;
                    $order->luxury_option_id = $luxury_option->id;
                    $order->rental_protection_amount = $rentalProtectionPrice;
                    $order->booking_option_price = $bookingOptionPrice;
                    $payable_amount = $payable_amount - $Order_bid_discount ?? 0;
                    if ($order->scheduled_slot) {
                        $scheduled_time =    explode("-", $order->scheduled_slot);
                        $schedule_dt =  date('Y-m-d', strtotime($order->scheduled_date_time));
                        $schedule_dt = date('Y-m-d H:i:s', strtotime($schedule_dt . " " . $scheduled_time[0]));
                        $order->scheduled_date_time = Carbon::parse($schedule_dt, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                    }
                    if (!$additionalPreferences->is_tax_price_inclusive) {
                        $order->payable_amount = $payable_amount;
                    } else {
                        $order->payable_amount = $payable_amount - $order->taxable_amount;
                    }

                    if ($action == 'p2p' && @$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                        $payable_amount = $request->amount ?? 0;
                        $order->payable_amount = $payable_amount;
                    }

                    // Advance Book Token Amount by mohit added by shiekh sohail farm meat
                    $getAdditionalPreference = getAdditionalPreference(['advance_booking_amount', 'advance_booking_amount_percentage']);
                    if (!empty($getAdditionalPreference['advance_booking_amount']) && !empty($getAdditionalPreference['advance_booking_amount_percentage']) && ($getAdditionalPreference['advance_booking_amount_percentage'] > 0) && ($getAdditionalPreference['advance_booking_amount_percentage'] < 101)) {
                        $advanceAmount = $payable_amount * $getAdditionalPreference['advance_booking_amount_percentage'] / 100;
                        $order->advance_amount = number_format($advanceAmount, 2);
                    }
                    // till here

                    $order->fixed_fee_amount = $fixed_fee_amount;
                    $order->total_container_charges = $total_container_charges;
                    if (($payable_amount == 0) || (($request->has('transaction_id')) && (!empty($request->transaction_id)))) {
                        $order->payment_status = 1;
                    }

                    $order->is_long_term            = $is_long_term_order;

                    $order->bid_discount  = $Order_bid_discount ?? 0;


                    if (!empty($vendor_cart_product->recurring_booking_time)) {
                        $user_timezone          =   $timezone;
                        $recurring_booking_time =   convertDateTimeInTimeZone($vendor_cart_product->recurring_booking_time, $user_timezone, 'H:i');

                        $order->recurring_booking_type  = $vendor_cart_product->recurring_booking_type;
                        $order->recurring_week_day      = json_encode($vendor_cart_product->recurring_week_day);
                        $order->recurring_week_type     = $vendor_cart_product->recurring_week_type;
                        $order->recurring_day_data      = $vendor_cart_product->recurring_day_data;
                        $order->recurring_booking_time  = $recurring_booking_time;
                    }


                    $order->save();

                    OrderFiles::where('cart_id', $cart->id)->update(['order_id' => $order->id, 'cart_id' => '']);



                    // pr($res);
                    // exit();
                    // $ex_gateways = [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 17, 18, 19, 24,25,28]; // if Stripe, paystack, mobbex, payfast, yoco, razorpay, gcash, simplify, square, checkout, authorise.net, stripe_fpx, cashfree,easebuzz,vnpay
                    // need to add weebhook for razorpay (10) and remove from ex_gateways
                    $ex_gateways = [1, 2, 3, 14, 15, 16, 10, 20, 21, 22, 23, 26, 38, 42, 30];
                    //Delete cart if payment is done from these gateways
                    if (in_array($request->payment_option_id, $ex_gateways)) {

                        //Send Email to customer
                        $res = $this->sendSuccessEmail($request, $order);
                        //Send Email to Vendor
                        foreach ($cart_products->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                            $this->sendSuccessEmail($request, $order, $vendor_id);
                        }

                        CaregoryKycDoc::where('cart_id', $cart->id)->update(['ordre_id' => $order->id, 'cart_id' => '']);

                        Cart::where('id', $cart->id)->update(['schedule_type' => NULL, 'scheduled_date_time' => NULL, 'order_id' => NULL]);

                        CartCoupon::where('cart_id', $cart->id)->delete();
                        // CartProduct::where('cart_id', $cart->id)->delete();
                        $cart_product_ids = $cart_products->pluck('id');
                        CartProduct::query()->whereIn('id', $cart_product_ids)->delete();
                        CartProductPrescription::where('cart_id', $cart->id)->delete();
                        CartDeliveryFee::where('cart_id', $cart->id)->delete();
                        CartRentalProtection::where('cart_id', $cart->id)->delete();
                        CartBookingOption::where('cart_id', $cart->id)->delete();
                    }
                    if (count($tax_category_ids)) {
                        foreach ($tax_category_ids as $tax_category_id) {
                            $order_tax = new OrderTax();
                            $order_tax->order_id = $order->id;
                            $order_tax->tax_category_id = $tax_category_id;
                            $order_tax->save();
                        }
                    }
                    if (($request->payment_option_id != 1) && ($request->payment_option_id != 2) && ($request->has('transaction_id')) && (!empty($request->transaction_id))) {
                        $payment = new Payment();
                        $payment->date = date('Y-m-d');
                        $payment->order_id = $order->id;
                        $payment->transaction_id = $request->transaction_id;
                        $payment->balance_transaction = $order->payable_amount;
                        $payment->type = 'cart';
                        $payment->save();
                    }
                    $order = $order->with(['vendors:id,order_id,dispatch_traking_url,vendor_id', 'user_vendor', 'vendors.vendor'])->where('order_number', $order->order_number)->first();
                    if (in_array($request->payment_option_id, $ex_gateways)) {
                        $code = $request->header('code');
                        if (!empty($order->vendors)) {
                            foreach ($order->vendors as $vendor_value) {
                                $vendor_order_detail = $this->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                                $user_vendors = UserVendor::where(['vendor_id' => $vendor_value->vendor_id])->pluck('user_id');
                                $vendorDetail = $vendor_value->vendor;
                                if ($vendorDetail->auto_accept_order == 0 && $vendorDetail->auto_reject_time > 0) {
                                    $clientDetail = Client::on('mysql')->where(['code' => $client_preference->client_code])->first();
                                    AutoRejectOrderCron::on('mysql')->create(['database_host' => $clientDetail->database_path, 'database_name' => $clientDetail->database_name, 'database_username' => $clientDetail->database_username, 'database_password' => $clientDetail->database_password, 'order_vendor_id' => $vendor_value->id, 'auto_reject_time' => Carbon::now()->addMinute($vendorDetail->auto_reject_time)]);
                                }
                                $this->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail, $code);
                            }
                            $vendor_order_detail = $this->minimize_orderDetails_for_notification($order->id);
                            $super_admin = User::where('is_superadmin', 1)->pluck('id');
                            $this->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail, $code);
                        } else {
                            $vendor_order_detail = $this->minimize_orderDetails_for_notification($order->id);

                            $getAllVendorAdmin = Order::join('order_vendors as ov', 'ov.order_id', 'orders.id')
                                ->leftjoin('user_vendors as uv', 'uv.vendor_id', 'ov.vendor_id')
                                ->where('order_number', $order->order_number)
                                ->pluck('uv.user_id');

                            $super_admin = User::where('is_superadmin', 1)->pluck('id');

                            if (!empty($getAllVendorAdmin)) {
                                $admins = $super_admin->merge($getAllVendorAdmin);
                                $super_admin = $admins->all();
                            }

                            $this->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);
                        }
                        $this->sendSuccessSMS($request, $order);
                    }
                    DB::commit();

                    # if payment type cash on delivery or payment status is 'Paid'
                    if ((($order->payment_option_id == 1 || $order->payment_option_id == 38)) || (($order->payment_option_id != 1) && ($order->payment_status == 1))) {
                        # if vendor selected auto accept

                        $autoaccept = $this->autoAcceptOrderIfOn($order->id);
                    }
                    // $hub_key = @getAdditionalPreference(['marg_access_token','is_marg_enable','marg_decrypt_key', 'marg_company_code','marg_date_time']);
                    // $hub_key = VendorMargConfig::where('vendor_id',$order->ordervendor->vendor_id ?? 0)->first();
                    // if(isset($hub_key) && $hub_key->is_marg_enable == 1){

                    //     //Create an order at margApi side also
                    //     $this->makeInsertOrderMargApi($order);
                    // }

                    return $this->successResponse($order, __('Order placed successfully.'), 201);
                }
            } else {
                return $this->errorResponse(['error' => __('Empty cart.')], 404);
            }
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    # if vendor selected auto accepted order
    /**
     * autoAcceptOrderIfOn
     *
     * @param  mixed $order_id
     * @param  mixed $uncheck_vendor_status in case order update by driver
     * @return void
     */
    public function autoAcceptOrderIfOn($order_id, $uncheck_vendor_status = 0)
    {
        $order_vendors = OrderVendor::where('order_id', $order_id)->whereHas('vendor', function ($q) {
            $q->where('auto_accept_order', 1);
        })->get();

        foreach ($order_vendors as $ov) {
            $request = $ov;

            DB::beginTransaction();
            try {

                $request->order_id = $ov->order_id;
                $request->vendor_id = $ov->vendor_id;
                $request->order_vendor_id = $ov->id;
                $request->status_option_id = 2;
                $timezone = Auth::user()->timezone;
                $vendor_order_status_check = VendorOrderStatus::where('order_id', $request->order_id)->where('vendor_id', $request->vendor_id)->where('order_status_option_id', $request->status_option_id)->first();

                if (!$vendor_order_status_check || ($uncheck_vendor_status == 1)) {

                    $vendor_order_status = new VendorOrderStatus();
                    $vendor_order_status->order_id = $request->order_id;
                    $vendor_order_status->vendor_id = $request->vendor_id;
                    $vendor_order_status->order_vendor_id = $request->order_vendor_id;
                    $vendor_order_status->order_status_option_id = $request->status_option_id;
                    $vendor_order_status->save();

                    // if ($request->status_option_id == 2) {
                    //     $order_dispatch = $this->checkIfanyProductLastMileon($request);
                    //     if ($order_dispatch && $order_dispatch == 1)
                    //         $stats = $this->insertInVendorOrderDispatchStatus($request);
                    // }

                    if ($request->status_option_id == 2) {

                        if ($request->shipping_delivery_type == 'D') {
                            $order_dispatch = $this->checkIfanyProductLastMileon($request);
                            if ($order_dispatch && $order_dispatch == 1) {
                                $stats = $this->insertInVendorOrderDispatchStatus($request);
                            }
                        } elseif ($request->shipping_delivery_type == 'L') {
                            //Create Shipping place order request for Lalamove
                            $order_lalamove = $this->placeOrderRequestlalamove($request);
                        } elseif ($request->shipping_delivery_type == 'SR') {
                            //Create Shipping place order request for Shiprocket
                            $order_ship = $this->placeOrderRequestShiprocket($request);
                        } elseif ($request->shipping_delivery_type == 'DU') {
                            //Create Shipping place order request for Shiprocket
                            $order_ship = $this->placeOrderRequestDunzo($request);
                        } elseif ($request->shipping_delivery_type == 'M') {
                            //Create Shipping place order request for Shiprocket
                            $order_ship = $this->placeOrderRequestAhoy($request);
                        } elseif ($request->shipping_delivery_type == 'D4') {
                            $order_ship = $this->placeOrderRequestD4B($request);
                        }
                    }

                    OrderVendor::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->update(['order_status_option_id' => $request->status_option_id]);
                    $this->ProductVariantStock($order_id);
                    DB::commit();
                    $this->sendSuccessNotification(Auth::user()->id, $request->vendor_id);
                }
            } catch (\Exception $e) {
                DB::rollback();
            }
        }
    }


    /// ******************  check If any D4b Mile on   ************************ ///////////////
    public function placeOrderRequestD4B($request)
    {
        $ship = new D4BDunzoController();
        //Create Shipping place order request for Shiprocket
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && $checkdeliveryFeeAdded->delivery_fee > 0.00) {
            $order_d4dunzo = $ship->createOrderRequestD4BDunzo($checkOrder->user_id, $checkdeliveryFeeAdded);
        }
        if ($order_d4dunzo['state'] == 'created') {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'web_hook_code' => $order_d4dunzo['task_id'],
                ]);
            return 1;
        }
        return 2;
    }

    /// ******************  check If any Product Last Mile on   ************************ ///////////////

    public function placeOrderRequestShippo($request)
    {
        $ship = new ShippoController();
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Shipping place order request for Shiprocket

        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_ship = $ship->createOrderRequestShippo($checkdeliveryFeeAdded);
        }
        if ($order_ship->object_id) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])->update([
                'ship_order_id' => $order_ship->object_id,
                'ship_shipment_id' => $order_ship->rate,
                'ship_awb_id' => $order_ship->parcel
            ]);
            return 1;
        }

        return 2;
    }
    public function placeOrderRequestShiprocket($request)
    {
        $ship = new ShiprocketController();
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Shipping place order request for Shiprocket
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_ship = $ship->createOrderRequestShiprocket($checkOrder->user_id, $checkdeliveryFeeAdded);
        }
        if ($order_ship->order_id) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'ship_order_id' => $order_ship->order_id,
                    'ship_shipment_id' => $order_ship->shipment_id,
                    'ship_awb_id' => $order_ship->awb_code
                ]);
            return 1;
        }

        return 2;
    }

    public function placeOrderRequestAhoy($request)
    {
        $data = new AhoyController();
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Ahoy place order request for Ahoy
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_det = $data->createPreOrderRequestAhoy($checkOrder->user_id, $checkdeliveryFeeAdded);
        }

        if (isset($order_det->orderId)) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'web_hook_code' => $order_det->orderId
                ]);

            return 1;
        }

        return 2;
    }

    public function placeOrderRequestDunzo($request)
    {

        $data = new DunzoController();
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Shipping place order request for Dunzo
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_lalamove = $data->createOrderRequestDunzo($checkOrder->user_id, $checkdeliveryFeeAdded);
        }

        if ($order_lalamove->status) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'web_hook_code' => $order_lalamove->data->order_uuid,
                    'lalamove_tracking_url' => $order_lalamove->data->trackUrl
                ]);

            return 1;
        }

        return 2;
    }

    public function placeOrderRequestlalamove($request)
    {
        $lala = new LalaMovesController();
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Shipping place order request for Lalamove
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_lalamove = $lala->placeOrderToLalamoveDev($request->vendor_id, $checkOrder->user_id, $checkOrder->id);
        }
        if (isset($order_lalamove->orderRef)) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update(['web_hook_code' => $order_lalamove->orderRef]);
        }
        return 1;
    }

    public function placeOrderRequestKwikApi($request)
    {
        $kwik = new QuickApiController();
        //Create Shipping place order request for KwikApi
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && $checkdeliveryFeeAdded->delivery_fee > 0.00) {
            $order_ship = $kwik->placeOrderToKwikApi($request->vendor_id, $request->order_id);
        }
        if ($order_ship) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'delivery_response' => json_encode($order_ship),
                    'dispatch_traking_url' => $order_ship->pickups[0]->result_tracking_link,
                    'web_hook_code' => $order_ship->unique_order_id
                ]);
            return 1;
        }

        return false;
    }

    public function checkIfanyProductLastMileon($request)
    {

        $order_dispatchs = 2;
        $AdditionalPreference = getAdditionalPreference(['is_place_order_delivery_zero']);
        $is_place_order_delivery_zero =  $AdditionalPreference['is_place_order_delivery_zero'];

        $checkdeliveryFeeAdded = OrderVendor::with('LuxuryOption', 'products')->where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $luxury_option_id      = $checkdeliveryFeeAdded->LuxuryOption ? $checkdeliveryFeeAdded->LuxuryOption->luxury_option_id : 1;
        $is_restricted         = $checkdeliveryFeeAdded->is_restricted;

        if ($luxury_option_id == 6) { // only for on_demand type

            $dispatch_domain_OnDemand = $this->getDispatchOnDemandDomain();

            if ($dispatch_domain_OnDemand && $dispatch_domain_OnDemand != false) {

                $OnDemand = 0;
                foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
                    $dispatch_domain = [
                        'service_key'      => $dispatch_domain_OnDemand->dispacher_home_other_service_key,
                        'service_key_code' => $dispatch_domain_OnDemand->dispacher_home_other_service_key_code,
                        'service_key_url'  => $dispatch_domain_OnDemand->dispacher_home_other_service_key_url,
                        'service_type'     => 'on_demand'
                    ];

                    if (($prod->is_price_buy_driver == 1)  && ($prod->product->category->categoryDetail->type_id == 8)) {

                        $dispatch_domain['rejectable_order'] = 1;

                        $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                        if ($order_dispatchs && $order_dispatchs == 1) {
                            $OnDemand = 1;
                            return 1;
                        }
                    } else if (isset($prod->product_dispatcher_tag) && !empty($prod->product_dispatcher_tag) && $prod->product->category->categoryDetail->type_id == 8) {



                        if ($dispatch_domain_OnDemand && $dispatch_domain_OnDemand != false && $OnDemand == 0  && $checkdeliveryFeeAdded->delivery_fee > 0) {


                            $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                            if ($order_dispatchs && $order_dispatchs == 1) {
                                $OnDemand = 1;
                                return 1;
                            }
                        }
                    } else { //for long term service

                    }
                }
            }
        }
        if ($luxury_option_id == 8) { // only for appointment type
            $dispatch_domain_Appointment = $this->checkIfAppointmentOnCommon();
            if ($dispatch_domain_Appointment && $dispatch_domain_Appointment != false) {
                $Appointment = 0;
                foreach ($checkdeliveryFeeAdded->products as $key => $prod) {


                    if (isset($prod->product_dispatcher_tag) && !empty($prod->product_dispatcher_tag) && $prod->product->category->categoryDetail->type_id == 12) {
                        $dispatch_domain_Appointment = $this->checkIfAppointmentOnCommon();
                        //echo $Appointment . 'app';
                        //echo $checkdeliveryFeeAdded->delivery_fee . '$checkdeliveryFeeAdded->delivery_fee';

                        if ($dispatch_domain_Appointment && $dispatch_domain_Appointment != false && $Appointment == 0  && $checkdeliveryFeeAdded->delivery_fee <= 0) {

                            $dispatch_domain = [
                                'service_key'      => $dispatch_domain_Appointment->appointment_service_key,
                                'service_key_code' => $dispatch_domain_Appointment->appointment_service_key_code,
                                'service_key_url'  => $dispatch_domain_Appointment->appointment_service_key_url,
                                'service_type'     => 'appointment'
                            ];
                            //pr($checkdeliveryFeeAdded);
                            $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                            if ($order_dispatchs && $order_dispatchs == 1) {
                                $Appointment = 1;
                                return 1;
                            }
                        }
                    }
                }
            }
        }

        $dispatch_domain = $this->getDispatchDomain();
        if ($dispatch_domain && $dispatch_domain != false) {
            if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1))
                $order_dispatchs = $this->placeRequestToDispatch($request->order_id, $request->vendor_id, $dispatch_domain);


            if ($order_dispatchs && $order_dispatchs == 1)
                return 1;
        }


        // $dispatch_domain_ondemand = $this->getDispatchOnDemandDomain();
        // if ($dispatch_domain_ondemand && $dispatch_domain_ondemand != false) {

        //     $ondemand = 0;

        //     foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
        //         if (isset($prod->product_dispatcher_tag) && !empty($prod->product_dispatcher_tag) && $prod->product->category->categoryDetail->type_id == 8) {
        //             $dispatch_domain_ondemand = $this->getDispatchOnDemandDomain();
        //             if ($dispatch_domain_ondemand && $dispatch_domain_ondemand != false && $ondemand == 0  && $checkdeliveryFeeAdded->delivery_fee <= 0.00) {
        //                 $order_dispatchs = $this->placeRequestToDispatchOnDemand($request->order_id, $request->vendor_id, $dispatch_domain_ondemand);
        //                 if ($order_dispatchs && $order_dispatchs == 1) {
        //                     $ondemand = 1;
        //                     return 1;
        //                 }
        //             }
        //         }
        //     }
        // }


        /////////////// **************** for laundry accept order *************** ////////////////
        $dispatch_domain_laundry = $this->getDispatchLaundryDomain();

        if ($dispatch_domain_laundry && $dispatch_domain_laundry != false) {
            $laundry = 0;

            foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
                if ($prod->product->category->categoryDetail->type_id == 9) {     ///////// if product from laundry
                    $dispatch_domain_laundry = $this->getDispatchLaundryDomain();
                    if ($dispatch_domain_laundry && $dispatch_domain_laundry != false && $laundry == 0) {
                        for ($x = 1; $x <= 2; $x++) {
                            if ($x == 1) {
                                $team_tag = $dispatch_domain_laundry->laundry_pickup_team ?? null;
                                $colm = $x;
                            }

                            if ($x == 2) {
                                $team_tag = $dispatch_domain_laundry->laundry_dropoff_team ?? null;
                                $colm = $x;
                            }

                            $order_dispatchs = $this->placeRequestToDispatchLaundry($request->order_id, $request->vendor_id, $dispatch_domain_laundry, $team_tag, $colm);
                        }

                        if ($order_dispatchs && $order_dispatchs == 1) {
                            $laundry = 1;
                            return 1;
                        }
                    }
                }
            }
        }


        return 2;
    }


    // place Request To Dispatch
    public function placeRequestToDispatch($order, $vendor, $dispatch_domain)
    {
        try {

            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();

            $dynamic = uniqid($order->id . $vendor);
            $client = Client::orderBy('id', 'asc')->first();
            if (isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain)
                $call_back_url = "https://" . $client->custom_domain . "/dispatch-order-status-update/" . $dynamic;
            else
                $call_back_url = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/dispatch-order-status-update/" . $dynamic;
            //   $call_back_url = route('dispatch-order-update', $dynamic);
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'phone_no', 'email', 'latitude', 'longitude', 'address', 'order_pre_time')->first();
            $order_vendor = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])->first();
            $tasks = array();
            $meta_data = '';

            if ($order->payment_option_id == 1 && ($order->payable_amount > 0)) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order_vendor->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used + $order->tip_amount;
            } else {

                if ($order->is_postpay == 1 && $order->payment_status == 0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order_vendor->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used + $order->tip_amount;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
            }

            $vendorProduct = OrderVendorProduct::where('order_id', $order->id)->first();
            $tags = isset($vendorProduct->product) ? $vendorProduct->product->tags : '';

            $team_tag = null;
            if (!empty($dispatch_domain->last_mile_team))
                $team_tag = $dispatch_domain->last_mile_team;

            if (isset($order->scheduled_date_time) && !empty($order->scheduled_date_time)) {
                $task_type = 'schedule';
                $schedule_time = $order->scheduled_date_time ?? null;
            } else {
                $task_type = 'now';
            }

            $tasks[] = array(
                'task_type_id' => 1,
                'latitude'    => $vendor_details->latitude ?? '',
                'longitude'   => $vendor_details->longitude ?? '',
                'short_name'  => '',
                'address'     => $vendor_details->address ?? '',
                'post_code'   => '',
                'barcode'     => '',
                'flat_no'     => null,
                'email'       => $vendor_details->email ?? null,
                'phone_number' => $vendor_details->phone_no ?? null,
            );

            $tasks[] = array(
                'task_type_id' => 2,
                'latitude'    => $cus_address->latitude ?? '',
                'longitude'   => $cus_address->longitude ?? '',
                'short_name'  => '',
                'address'     => $cus_address->address ?? '',
                'post_code'   => $cus_address->pincode ?? '',
                'barcode'     => '',
                'flat_no'     => $cus_address->house_number ?? null,
                'email'       => $customer->email ?? null,
                'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
            );

            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }

            $postdata =  [
                'order_number' =>  $order->order_number,
                'customer_name' => $customer->name ?? 'Customer',
                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                'customer_dial_code' => $customer->dial_code ?? null,
                'customer_email' => $customer->email ?? null,
                'recipient_phone' => $customerno ?? rand(111111, 11111),
                'recipient_email' => $customer->email ?? null,
                'task_description' => "Order From :" . $vendor_details->name,
                'allocation_type' => 'a',
                'task_type' => $task_type,
                'schedule_time' => $schedule_time ?? null,
                'cash_to_be_collected' => $payable_amount ?? 0.00,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'order_agent_tag' => $tags,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'is_restricted' => $order_vendor->is_restricted,
                'vendor_id' => $vendor_details->id,
                'order_vendor_id' => $order_vendor->id,
                'dbname' => $client->database_name,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image,
                'order_pre_time' => $vendor_details->order_pre_time,
                'app_call' => 1,
                'tip_amount' => $order->tip_amount ?? 0
            ];
            if ($order_vendor->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }

            $client = new GCLIENT([
                'headers' => [
                    'personaltoken' => $dispatch_domain->delivery_service_key,
                    'shortcode' => $dispatch_domain->delivery_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->delivery_service_key_url;
            $res = $client->post(
                $url . '/api/task/create',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);
            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                    ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);

                return 1;
            }
            return 2;
        } catch (\Exception $e) {
            return 2;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }



    // place Request To Dispatch for On Demand
    public function placeRequestToDispatchOnDemand($order, $vendor, $dispatch_domain)
    {
        try {

            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();
            if ($order->payment_option_id == 1) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used;
            } else {

                if ($order->is_postpay == 1 && $order->payment_status == 0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
            }
            $dynamic = uniqid($order->id . $vendor);
            $client = Client::orderBy('id', 'asc')->first();
            if (isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain)
                $call_back_url = "https://" . $client->custom_domain . "/dispatch-order-status-update/" . $dynamic;
            else
                $call_back_url = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/dispatch-order-status-update/" . $dynamic;
            // $call_back_url = route('dispatch-order-update', $dynamic);

            $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'phone_no', 'email', 'latitude', 'longitude', 'address')->first();
            $order_vendor = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])->first();
            $tasks = array();
            $meta_data = '';

            $unique = Auth::user()->code;
            $team_tag = $unique . "_" . $vendor;

            $tasks[] = array(
                'task_type_id' => 1,
                'latitude' => $vendor_details->latitude ?? '',
                'longitude' => $vendor_details->longitude ?? '',
                'short_name' => '',
                'address' => $vendor_details->address ?? '',
                'post_code' => '',
                'barcode' => '',
                'flat_no'     => null,
                'email'       => $vendor_details->email ?? null,
                'phone_number' => $vendor_details->phone_no ?? null,
            );

            $tasks[] = array(
                'task_type_id' => 2,
                'latitude' => $cus_address->latitude ?? '',
                'longitude' => $cus_address->longitude ?? '',
                'short_name' => '',
                'address' => $cus_address->address ?? '',
                'post_code' => $cus_address->pincode ?? '',
                'barcode' => '',
                'flat_no'     => $cus_address->house_number ?? null,
                'email'       => $customer->email ?? null,
                'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
            );

            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }

            $postdata =  [
                'order_number' =>  $order->order_number,
                'customer_name' => $customer->name ?? 'Dummy Customer',
                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                'customer_dial_code' => $customer->dial_code ?? null,
                'customer_email' => $customer->email ?? null,
                'recipient_phone' => $customerno ?? rand(111111, 11111),
                'recipient_email' => $customer->email ?? null,
                'task_description' => "Order From :" . $vendor_details->name,
                'allocation_type' => 'a',
                'task_type' => 'now',
                'cash_to_be_collected' => $payable_amount ?? 0.00,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'is_restricted' => $order_vendor->is_restricted,
                'vendor_id' => $vendor_details->id,
                'order_vendor_id' => $order_vendor->id,
                'dbname' => $client->database_name,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image,
                'tip_amount' => $order->tip_amount ?? 0

            ];
            if ($order_vendor->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }


            $client = new GClient([
                'headers' => [
                    'personaltoken' => $dispatch_domain->dispacher_home_other_service_key,
                    'shortcode' => $dispatch_domain->dispacher_home_other_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->dispacher_home_other_service_key_url;
            $res = $client->post(
                $url . '/api/task/create',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);

            if ($response && $response['task_id'] > 0) {

                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                    ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);


                return 1;
            }

            return 2;
        } catch (\Exception $e) {

            return 2;
        }
    }

    // place Request To Dispatch for Laundry
    public function placeRequestToDispatchLaundry($order, $vendor, $dispatch_domain, $team_tag, $colm)
    {
        try {
            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();
            if ($order->payment_option_id == 1) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used;
            } else {

                if ($order->is_postpay == 1 && $order->payment_status == 0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
            }


            $dynamic = uniqid($order->id . $vendor);
            $call_back_url = route('dispatch-order-update', $dynamic);
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'latitude', 'phone_no', 'email', 'longitude', 'address')->first();
            $order_vendor = OrderVendor::where(['order_id' => $order, 'vendor_id' => $vendor])->first();
            $tasks = array();
            $meta_data = '';
            $rtype = 'P';
            $unique = Auth::user()->code;
            if ($colm == 1) {
                $rtype = 'P';    # 1 for pickup from customer drop to vendor
                $desc = $order->comment_for_pickup_driver ?? null;
                $tasks[] = array(
                    'task_type_id' => 1,
                    'latitude'    => $cus_address->latitude ?? '',
                    'longitude'   => $cus_address->longitude ?? '',
                    'short_name'  => '',
                    'address'     => $cus_address->address ?? '',
                    'post_code'   => $cus_address->pincode ?? '',
                    'barcode'     => '',
                    'flat_no'     => $cus_address->house_number ?? '',
                    'email'       => $customer->email ?? '',
                    'phone_number' => $customer->dial_code . $customer->phone_number  ?? ''

                );
                $tasks[] = array(
                    'task_type_id' => 2,
                    'latitude'    => $vendor_details->latitude ?? '',
                    'longitude'   => $vendor_details->longitude ?? '',
                    'short_name'  => '',
                    'address'     => $vendor_details->address ?? '',
                    'post_code'   => '',
                    'barcode'     => '',
                    'flat_no'     => null,
                    'email'       => $vendor_details->email ?? null,
                    'phone_number' => $vendor_details->phone_no ?? null
                );

                if (isset($order->schedule_pickup) && !empty($order->schedule_pickup)) {
                    $task_type = 'schedule';
                    $schedule_time = $order->schedule_pickup ?? null;
                } else {
                    $task_type = 'now';
                }
            }


            if ($colm == 2) { # 1 for pickup from vendor drop to customer
                $rtype = 'D';
                $desc = $order->comment_for_dropoff_driver ?? null;
                $tasks[] = array(
                    'task_type_id' => 1,
                    'latitude'    => $vendor_details->latitude ?? '',
                    'longitude'   => $vendor_details->longitude ?? '',
                    'short_name'  => '',
                    'address'     => $vendor_details->address ?? '',
                    'post_code'   => '',
                    'barcode'     => '',
                    'flat_no'     => null,
                    'email'       => $vendor_details->email ?? null,
                    'phone_number' => $vendor_details->phone_no ?? null,
                );


                $tasks[] = array(
                    'task_type_id' => 2,
                    'latitude'    => $cus_address->latitude ?? '',
                    'longitude'   => $cus_address->longitude ?? '',
                    'short_name'  => '',
                    'address'     => $cus_address->address ?? '',
                    'post_code'   => $cus_address->pincode ?? '',
                    'barcode'     => '',
                    'flat_no'     => $cus_address->house_number ?? null,
                    'email'       => $customer->email ?? null,
                    'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
                );


                if (isset($order->schedule_dropoff) && !empty($order->schedule_dropoff)) {
                    $task_type = 'schedule';
                    $schedule_time = $order->schedule_dropoff ?? null;
                } else {
                    $task_type = 'now';
                }
            }

            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }

            $client = Client::orderBy('id', 'asc')->first();
            $postdata =  [
                'order_number' =>  $order->order_number,
                'customer_name' => $customer->name ?? 'Dummy Customer',
                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                'customer_dial_code' => $customer->dial_code ?? null,
                'customer_email' => $customer->email ?? null,
                'recipient_phone' => $customerno ?? rand(111111, 11111),
                'recipient_email' => $customer->email ?? null,
                'task_description' => $desc ?? null,
                'allocation_type' => 'a',
                'task_type' => $task_type,
                'cash_to_be_collected' => $payable_amount ?? 0.00,
                'schedule_time' => $schedule_time ?? null,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'request_type' => $rtype,
                'is_restricted' => $order_vendor->is_restricted,
                'vendor_id' => $vendor_details->id,
                'order_vendor_id' => $order_vendor->id,
                'dbname' => $client->database_name,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image,
                'tip_amount' => $order->tip_amount ?? 0

            ];
            if ($order_vendor->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }


            $client = new GCLIENT([
                'headers' => [
                    'personaltoken' => $dispatch_domain->laundry_service_key,
                    'shortcode' => $dispatch_domain->laundry_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->laundry_service_key_url;
            $res = $client->post(
                $url . '/api/task/create',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);

            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                    ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);

                return 1;
            }
            return 2;
        } catch (\Exception $e) {
            return 2;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }


    # get prefereance if last mile on or off and all details updated in config
    public function getDispatchDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }


    # get prefereance if on demand on in config
    public function getDispatchOnDemandDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_dispacher_home_other_service == 1 && !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_code) && !empty($preference->dispacher_home_other_service_key_url))
            return $preference;
        else
            return false;
    }

    # get prefereance if laundry in config
    public function getDispatchLaundryDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_laundry_service == 1 && !empty($preference->laundry_service_key) && !empty($preference->laundry_service_key_code) && !empty($preference->laundry_service_key_url)) {
            return $preference;
        } else {
            return false;
        }
    }

    /// ******************   insert In Vendor Order Dispatch Status   ************************ ///////////////
    public function insertInVendorOrderDispatchStatus($request)
    {
        $update = VendorOrderDispatcherStatus::updateOrCreate([
            'dispatcher_id' => null,
            'order_id' =>  $request->order_id,
            'dispatcher_status_option_id' => 1,
            'vendor_id' =>  $request->vendor_id
        ]);
    }
    public function sendSuccessEmail($request, $order, $vendor_id = '')
    {

        $user = Auth::user();

        $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from', 'admin_email')->where('id', '>', 0)->first();
        $message = __('An otp has been sent to your email. Please check.');
        $otp = mt_rand(100000, 999999);

        if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
            $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
            if ($vendor_id == "") {
                $sendto =  $user->email;
            } else {
                $vendor = Vendor::where('id', $vendor_id)->first();
                if ($vendor) {
                    $sendto =  $vendor->email;
                }
            }

            $customerCurrency = ClientCurrency::join('currencies as cu', 'cu.id', 'client_currencies.currency_id')->where('client_currencies.currency_id', $user->currency)->first();
            if (!$customerCurrency) {
                $customerCurrency = ClientCurrency::where('is_primary', 1)->get()->first()->currency;
            }
            $currSymbol = $customerCurrency->symbol;
            $client_name = 'Sales';
            $mail_from = $data->mail_from;

            try {
                $email_template_content = '';
                $email_template = EmailTemplate::where('id', 5)->first();

                $address = UserAddress::where('id', $request->address_id)->first();
                if ($user) {
                    $cart = Cart::select('id', 'is_gift', 'item_count')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
                } else {
                    $cart = Cart::select('id', 'is_gift', 'item_count')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
                }

                if ($cart) {
                    $cartDetails = $this->getCart($cart);
                }
                //pr( $cartDetails->toArray());
                $luxuryOptionTitle = !empty($order->luxury_option) ? $order->luxury_option->title : 'delivery';
                if ($email_template) {

                    $email_template_content = $email_template->content;
                    // if ($vendor_id == "") {
                    $returnHTML = view('email.newOrderProducts')->with(['user' => $user, 'cartData' => $cartDetails, 'order' => $order, 'currencySymbol' => $currSymbol, 'luxuryOptionTitle' => $luxuryOptionTitle])->render();
                    // } else {
                    //     $returnHTML = view('email.newOrderVendorProducts')->with(['user'=>$user,'cartData' => $cartDetails, 'order' => $order, 'id' => $vendor_id, 'currencySymbol' => $currSymbol, 'luxuryOptionTitle' => $luxuryOptionTitle])->render();
                    // }

                    $email_template_content = str_ireplace("{description}", '', $email_template_content);
                    $email_template_content = str_ireplace("{customer_name}", ucwords($user->name), $email_template_content);
                    $email_template_content = str_ireplace("{order_id}", $order->order_number, $email_template_content);
                    $email_template_content = str_ireplace("{products}", $returnHTML, $email_template_content);
                    if (!empty($address)) {
                        $email_template_content = str_ireplace("{address}", $address->address . ', ' . $address->state . ', ' . $address->country . ', ' . $address->pincode, $email_template_content);
                    }
                }
                $email_data = [
                    'code' => $otp,
                    'link' => "link",
                    'email' => $sendto, //"harbans.sayonakh@gmail.com",//  $sendto,//
                    'mail_from' => $mail_from,
                    'client_name' => $client_name,
                    'logo' => $client->logo['original'],
                    'subject' => $email_template->subject,
                    'customer_name' => ucwords($user->name),
                    'email_template_content' => $email_template_content,
                    'cartData' => $cartDetails,
                    'user_address' => $address,
                ];
                if (!empty($data['admin_email'])) {
                    $email_data['admin_email'] = $data['admin_email'];
                }
                if ($vendor_id == "") {
                    $email_data['send_to_cc'] = 1;
                } else {
                    $email_data['send_to_cc'] = 0;
                }
                // $res = $this->testOrderMail($email_data);
                // dd($res);
                dispatch(new \App\Jobs\SendOrderSuccessEmailJob($email_data))->onQueue('verify_email');
                $notified = 1;
            } catch (\Exception $e) {
            }
        }
    }



    public function sendSuccessSMS($request, $order, $vendor_id = '')
    {
        try {
            $prefer = ClientPreference::select('sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'digit_after_decimal')->first();

            $user = Auth::user();
            if ($user) {
                $customerCurrency = ClientCurrency::join('currencies as cu', 'cu.id', 'client_currencies.currency_id')->where('client_currencies.currency_id', $user->currency)->first();
                $currSymbol = $customerCurrency->symbol;
                if ($user->dial_code == "971") {
                    $to = '+' . $user->dial_code . "0" . $user->phone_number;
                } else {
                    $to = '+' . $user->dial_code . $user->phone_number;
                }
                $provider = $prefer->sms_provider;
                $order->payable_amount = number_format((float) $order->payable_amount, $prefer->digit_after_decimal, '.', '');
                $keyData = ['{user_name}' => $user->name ?? '', '{amount}' => $currSymbol . $order->payable_amount, '{order_number}' => $order->order_number ?? ''];
                $body = sendSmsTemplate('order-place-Successfully', $keyData);

                if (!empty($prefer->sms_provider)) {
                    $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
                }
            }
        } catch (\Exception $ex) {
        }
    }
    public function sendOrderNotification($id)
    {
        $token = UserDevice::whereNotNull('device_token')->pluck('device_token')->where('user_id', $id)->toArray();
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        //$from = env('FIREBASE_SERVER_KEY');
        $notification_content = NotificationTemplate::where('id', 1)->first();
        if ($notification_content && !empty($token) && !empty($client_preferences->fcm_server_key)) {

            $data = [
                "registration_ids" => $token,
                "notification" => [
                    'title' => $notification_content->label,
                    'body'  => $notification_content->content,
                ]
            ];
            sendFcmCurlRequest($data);
        }
    }
    public function getOrdersList(Request $request)
    {
        $user = Auth::user();
        $order_status_options = [];
        $paginate = $request->has('limit') ? $request->limit : 12;
        $type = $request->has('type') ? $request->type : 'active';
        $orders = OrderVendor::where('user_id', $user->id)->with('products')->orderBy('id', 'DESC');

        $additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch']);
        switch ($type) {
            case 'pending': // which order not assign yet in driver

                $orders->whereHas('products', function ($q1) {
                    $q1->where('dispatcher_status_option_id', 1);
                });

                break;
            case 'active':
                $orders->whereNotIn('order_status_option_id', [6, 3]);
                // $orders->whereHas('products', function ($q) use ($additionalPreference) {
                //      if($additionalPreference['is_service_product_price_from_dispatch'] ==1){
                //         $q->whereNotIn('dispatcher_status_option_id',[1,5,6]); //1=pending,5= complete,6 reject
                //      }
                // });

                break;
            case 'past':
                $orders->whereIn('order_status_option_id', [6, 3]);
                // if($additionalPreference['is_service_product_price_from_dispatch'] ==1){
                //     $orders->whereHas('products', function ($q) {
                //         $q->where('dispatcher_status_option_id',5); //1=pending,5= complete,6 reject
                //     });
                // }
                break;
            case 'schedule':
                $order_status_options = [10];
                $orders->whereHas('status', function ($query) use ($order_status_options) {
                    $query->whereIn('order_status_option_id', $order_status_options);
                });
                break;
        }

        $orders = $orders->with([
            'order:id,scheduled_date_time',  // Just load it normally
            'order.orderVendorProduct',
            'orderDetail.editingInCart',
            'products.product.translation',
            'vendor:id,name,logo,banner,return_request,cancel_order_in_processing',
            'products.productReturn',
            'exchanged_of_order.orderDetail',
            'exchanged_to_order.orderDetail',
            'cancel_request',
            'products.Routes',
            'products.order_product_status',
            'products.product.category.categoryDetail' => function ($q) {
                $q->select('id', 'type_id');
            }
        ])
        ->whereHas('orderDetail', function ($q1) {
            $q1->where('orders.payment_status', 1)->whereNotIn('orders.payment_option_id', [1, 38]);
            $q1->orWhere(function ($q2) {
                $q2->whereIn('orders.payment_option_id', [1, 38, 9]) //for paylink orders
                    ->orWhere(function ($q3) {
                        $q3->where('orders.is_postpay', 1) //1 for order is post paid
                            ->whereNotIn('orders.payment_option_id', [1, 38]);
                    });
            });
        })
        ->orderBy('id', 'Desc')
        ->paginate($paginate);
        $orders->getCollection()->transform(function ($order) {
            $order->formatted_schedule_date = optional($order->order)->order_schedule_date; // Use the accessor
            $order->formatted_schedule_slot = optional(optional($order->order)->orderVendorProduct)->schedule_slot;
            $order->is_appointment = ($order->formatted_schedule_date && $order->formatted_schedule_slot) ? 1 : 0;
            $order->formatted_schedule_date =  $order->is_appointment ? $order->formatted_schedule_date : null; // Use the accessor
            return $order;
        });

        $orders =    $this->orderlistLoop($orders, $user, $request, 'borrower');

        return $this->successResponse($orders, '', 201);
    }

    public function orderlistLoop($orders,   $user, $request, $type = null)
    {
        $additionalPreferences   =  @getAdditionalPreference(['is_postpay_enable', 'is_order_edit_enable', 'order_edit_before_hours']);
        $is_postpay_enable       =  $additionalPreferences['is_postpay_enable'];
        $is_order_edit_enable    =  $additionalPreferences['is_order_edit_enable'];
        $order_edit_before_hours =  $additionalPreferences['order_edit_before_hours'];
        $editlimit_datetime = Carbon::now()->addHours($order_edit_before_hours)->toDateTimeString();
        $dispatch_domain_OnDemand = $this->getDispatchOnDemandDomain();
        $dispatch_domain  = [];
        if ($dispatch_domain_OnDemand && $dispatch_domain_OnDemand != false) {
            $dispatch_domain = [
                'service_key'      => $dispatch_domain_OnDemand->dispacher_home_other_service_key,
                'service_key_code' => $dispatch_domain_OnDemand->dispacher_home_other_service_key_code,
                'service_key_url'  => $dispatch_domain_OnDemand->dispacher_home_other_service_key_url,
                'service_type'     => 'on_demand'
            ];
        }
        foreach ($orders as $order) {
            if (@$order->order_id) {
                $order_item_count = 0;
                $order->user_name = $user->name;
                $order->user_image = $user->image;
                $total_total_payable = $order->orderDetail->total_amount + $order->orderDetail->wallet_amount_used + $order->orderDetail->loyalty_amount_saved + $order->orderDetail->taxable_amount + $order->orderDetail->total_delivery_fee + $order->orderDetail->tip_amount + $order->orderDetail->total_service_fee - $order->orderDetail->total_discount;
                $order->date_time = dateTimeInUserTimeZone($order->orderDetail->created_at, $user->timezone);
                $order->payment_option_title = ($order->orderDetail->wallet_amount_used >= ceil($total_total_payable)) ? __("Wallet") : __($order->orderDetail->paymentOption->title ?? '');
                $order->order_number = $order->orderDetail->order_number;
                $order->schedule_pickup = date('d/m/Y', strtotime($order->orderDetail->schedule_pickup));
                $order->scheduled_slot  = $order->orderDetail->scheduled_slot;
                $order->schedule_dropoff = date('d/m/Y', strtotime($order->orderDetail->schedule_dropoff));
                $order->dropoff_scheduled_slot  = $order->orderDetail->dropoff_scheduled_slot;

                $order->payable_amount = decimal_format($order->orderDetail->payable_amount);
                if (checkColumnExists('orders', 'is_postpay')) {
                    $order->is_postpay = (isset($request->is_postpay)) ? $request->is_postpay : 0;
                }
                $order->type = $type;
                if (checkColumnExists('orders', 'is_edited')) {
                    $order->is_edited   = (isset($order->orderDetail->is_edited)) ? $order->orderDetail->is_edited : 0;
                }
                if (!empty($order->orderDetail->scheduled_date_time) && $is_order_edit_enable == 1 && $order_edit_before_hours > 0 && ($order->orderDetail->payment_option_id == 1 || $order->orderDetail->payment_status != 1)) {
                    if ((strtotime($order->orderDetail->scheduled_date_time) - strtotime($editlimit_datetime)) > 0) {
                        $order->is_editable  = 1;
                    } else {
                        $order->is_editable  = 0;
                    }
                } else {
                    $order->is_editable  = 0;
                }

                if (!empty($order->orderDetail->editingInCart)) {
                    $order->is_editable  = 2;
                }

                $product_details = [];
                $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->orderDetail->id)->where('vendor_id', $order->vendor_id)->orderBy('id', 'DESC')->first();
                if ($vendor_order_status) {
                    $order_sts = OrderStatusOption::where('id', $order->order_status_option_id)->first();
                    // $order->order_status =  ['current_status' => ['id' => $vendor_order_status->OrderStatusOption->id, 'title' => __($vendor_order_status->OrderStatusOption->title)]];
                    if (@$order->exchanged_to_order->order_status_option_id && $order->exchanged_to_order->order_status_option_id == 6) {
                        $order->order_status =  ['current_status' => ['id' => 6, 'title' => __("Replaced")]];
                        // $order->order_status->current_status->title = "Replaced";
                    } else {
                        $order->order_status =  ['current_status' => ['id' => @$order_sts->id ?? '', 'title' => __(@$order_sts->title)]];
                    }
                } else {
                    $order->current_status = null;
                }
                $return_request_status = 0;
                $returnable = 0;
                $replaceable = 0;

                foreach ($order->products as $product) {
                    $dispatch_agent_id = $product->dispatch_agent_id;
                    $dispatcher_traking_url = $product->routes->isNotEmpty() ? ($product->routes->first() ? $product->routes->first()->dispatch_traking_url : '') : '';
                    $dispatcher_agent = [];
                    $category_type_id = @$product->product->category->categoryDetail->type_id ?? '';
                    if (($category_type_id == 8 && !empty($dispatch_domain))  && ($dispatch_agent_id && $dispatcher_traking_url)) {
                        $dispatch_domain['driver_id'] = $dispatch_agent_id;
                        $dispatcher_agent = $this->getAgentDetailFromDispatcher($dispatch_domain);
                    }

                    if ($this->checkOrderDaysForReturn($order, @$product->product->return_days) && $order->is_exchanged_or_returned == 0) {


                        if (@$product->product->replaceable && $product->product->replaceable == 1) {
                            $replaceable = $product->product->replaceable;
                        }

                        if (@$product->product->returnable && $order->vendor->return_request == 1 && $product->product->returnable == 1) {
                            $returnable = $product->product->returnable;
                        }
                    }
                    // dd($product->productReturn->status);
                    if (@$product->productReturn &&  $return_request_status == 0 && $order->is_exchanged_or_returned != 1) {
                        if ($product->productReturn->status == 'Accepted') {
                            $return_request_status = 1;
                        }
                        if ($product->productReturn->status == 'Rejected') {
                            $return_request_status = 2;
                        }
                        if ($product->productReturn->status == 'Pending') {
                            $return_request_status = 3;
                        }
                    }
                    $order_item_count += $product->quantity;

                    $product_details[] = array(
                        'image_path' => $product->media->first() ? $product->media->first()->image->path : $product->image,
                        'price' => $product->price,
                        'qty' => $product->quantity,
                        'category_type' => $product->product->category->categoryDetail->type->title ?? '',
                        'translation' => $product->product->translation ?? '',
                        'product_id' => $product->product_id,
                        'title' => $product->product_name,
                        'routes' => $product->routes,
                        'dispatcher_agent' => $dispatcher_agent,
                        'scheduled_date_time' => dateTimeInUserTimeZone($product->scheduled_date_time, $user->timezone),
                        'schedule_slot' => $product->schedule_slot
                    );
                }

                $luxury_option_name = '';
                if ($order->orderDetail->luxury_option_id > 0) {
                    $luxury_option = LuxuryOption::where('id', $order->orderDetail->luxury_option_id)->first();

                    switch ($luxury_option->title) {
                        case "takeaway":
                            $luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
                            break;
                        case "dine_in":
                            $luxury_option_name = __('Dine-In');
                            break;
                        case "on_demand":
                            $luxury_option_name = $this->getNomenclatureName('Services', $user->language, false);
                            break;
                        default:
                            $luxury_option_name = getNomenclatureName($luxury_option->title);
                            break;
                    }
                }
            }
            $order->is_long_term  = 0;

            $order->is_long_term  = $order->orderDetail->is_long_term;

            $order->luxury_option_name = $luxury_option_name;
            $order->luxury_option_name = $luxury_option_name;
            $order->product_details = $product_details;
            $order->item_count = $order_item_count;
            $order->return_request_status = $return_request_status;


            //product returnable and replaceble

            $order->returnable = $returnable;
            $order->replaceable = $replaceable;

            // unset($order->user);
            // unset($order->products);
            unset($order->paymentOption);
            unset($order->payment_option_id);
            unset($order->orderDetail);
        }
        return $orders;
    }



    public function getRejectedOrdersList(Request $request)
    {
        $user = Auth::user();
        //  pr($user);

        $paginate = $request->has('limit') ? $request->limit : 12;
        $type = $request->has('type') ? $request->type : 'active';
        $orders = OrderVendor::where('user_id', $user->id)->orderBy('id', 'DESC');


        $orders = $orders->with([
            'orderDetail.editingInCart',
            'vendor:id,name,logo,banner,return_request,cancel_order_in_processing',
            'products.productReturn',
            'exchanged_of_order.orderDetail',
            'exchanged_to_order.orderDetail',
            'cancel_request',
            'products.orderProductStatus' => function ($q) {
                $q->where('order_status_option_id', 3); // cancel order product
            },
            'products.product.category.categoryDetail' => function ($q) {
                $q->select('id', 'type_id');
            }
        ])
            ->whereHas('orderDetail', function ($q1) {
                $q1->where('orders.payment_status', 1)->whereNotIn('orders.payment_option_id', [1, 38]);
                $q1->orWhere(function ($q2) {
                    $q2->whereIn('orders.payment_option_id', [1, 38])
                        ->orWhere(function ($q3) {
                            $q3->where('orders.is_postpay', 1) //1 for order is post paid
                                ->whereNotIn('orders.payment_option_id', [1, 38]);
                        });
                });
            })
            ->whereHas('products.orderProductStatus', function ($q1) {
                $q1->where('order_status_option_id', 3); // cancel order product
            })
            ->paginate($paginate);
        $orders =    $this->orderlistLoop($orders, $user, $request);
        return $this->successResponse($orders, '', 201);
    }

    public function postOrderDetail(Request $request)
    {

        try {
            $user = Auth::user();
            $order_item_count = 0;
            $language_id = $user->language;
            $order_id = $request->order_id;
            $vendor_id = $request->vendor_id ?? '';
            $preferences = ClientPreference::first();

            if ($vendor_id) {
                $order = Order::with([
                    'orderVendorProduct',
                    'driver_rating',
                    'vendors.products.Routes',
                    'reports',
                    // 'vendors' => function ($q) use ($vendor_id) {
                    //     $q->where('vendor_id', $vendor_id);

                    // },
                    'vendors' => function ($q) use ($vendor_id) {
                        $q->where('vendor_id', $vendor_id)
                            ->addSelect([
                                'order_vendors.*',
                                \DB::raw('(SELECT dispatch_traking_url FROM order_product_dispatch_routes WHERE order_product_dispatch_routes.order_id = order_vendors.order_id LIMIT 1) as dispatch_traking_url')
                            ]);
                    },
                    'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    },
                    'vendors.dineInTable.category',
                    'vendors.products' => function ($q) use ($vendor_id) {
                        $q->where('vendor_id', $vendor_id);
                    },
                    'vendors.products.translation' => function ($q) use ($language_id) {
                        $q->select('id', 'product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                        $q->where('language_id', $language_id);
                    },
                    'vendors.products.pvariant.vset.optionData.trans',
                    'vendors.products.addon',
                    'vendors.coupon',
                    'address',
                    'vendors.products.productRating',
                    'vendors.allStatus',
                    'vendors.tempCart' => function ($q) {
                        $q->where('is_submitted', 1)->where('is_approved', 0);
                    },
                    'vendors.tempCart.cartProducts.product.media.image',
                    'vendors.tempCart.cartProducts.pvariant.media.pimage.image',
                    'vendors.tempCart.cartProducts.product.translation' => function ($q) use ($language_id) {
                        $q->where('language_id', $language_id)->groupBy('product_id');
                    },
                    'vendors.tempCart.cartProducts.addon.set' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    },
                    'vendors.tempCart.cartProducts.addon.option' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    },
                    'user.allergicItems'
                ]);
                $order = $order->with(['OrderFiles']);

                $order = $order->where(function ($q1) {
                    $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
                    $q1->orWhere(function ($q2) {
                        $q2->whereIn('payment_option_id', [1, 38]);
                    });
                })
                    ->where('id', $order_id)->select('*', 'id as total_discount_calculate')->first();
            } else {
                $order = Order::with(
                    [
                        'driver_rating',
                        'reports',
                        'vendors.vendor',
                        'vendors.products.Routes',
                        'vendors.products.translation' => function ($q) use ($language_id) {
                            $q->select('id', 'product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                            $q->where('language_id', $language_id);
                        },
                        'vendors.products.pvariant.vset.optionData.trans',
                        'vendors.products.addon',
                        'vendors.coupon',
                        'address',
                        'vendors.products.productRating',
                        'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        },
                        'vendors.dineInTable.category',
                        'vendors.tempCart' => function ($q) {
                            $q->where('is_submitted', 1)->where('is_approved', 0);
                        },
                        'vendors.tempCart.cartProducts.product.media.image',
                        'vendors.tempCart.cartProducts.pvariant.media.pimage.image',
                        'vendors.tempCart.cartProducts.product.translation' => function ($q) use ($language_id) {
                            $q->where('language_id', $language_id)->groupBy('product_id');
                        },
                        'vendors.tempCart.cartProducts.addon.set' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        },
                        'vendors.tempCart.cartProducts.addon.option' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        },
                        'user.allergicItems'
                    ]
                );

                $order = $order->with(['OrderFiles']);

                $order = $order->where(function ($q1) {
                    $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
                    $q1->orWhere(function ($q2) {
                        $q2->whereIn('payment_option_id', [1, 38]);
                    });
                });
                if (!$user->is_admin) {
                    $order = $order->where('user_id', $user->id);
                }
                $order = $order->where('id', $order_id)->select('*', 'id as total_discount_calculate')
                    ->first();
            }

            $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
            if ($order) {
                // set payment option dynamic name
                if ($order->paymentOption->code == 'stripe') {
                    $order->paymentOption->title = __('Credit/Debit Card (Stripe)');
                } elseif ($order->paymentOption->code == 'kongapay') {
                    $order->paymentOption->title = 'Pay Now';
                } elseif ($order->paymentOption->code == 'mvodafone') {
                    $order->paymentOption->title = 'Vodafone M-PAiSA';
                } elseif ($order->paymentOption->code == 'mobbex') {
                    $order->paymentOption->title = __('Mobbex');
                } elseif ($order->paymentOption->code == 'offline_manual') {
                    $json = json_decode($order->paymentOption->credentials);
                    $order->paymentOption->title = $json->manule_payment_title;
                }
                $order->paymentOption->title = __($order->paymentOption->title);

                $order->user_name = $order->user->name;
                $order->user_image = $order->user->image;
                $order->payment_option_title = __($order->paymentOption->title);
                $order->created_date = dateTimeInUserTimeZone($order->created_at, $user->timezone);
                $order->tip_amount = $order->tip_amount;
                $order->tip = array(
                    ['label' => '5%', 'value' => decimal_format(0.05 * ($order->payable_amount - $order->total_discount_calculate))],
                    ['label' => '10%', 'value' => decimal_format(0.1 * ($order->payable_amount - $order->total_discount_calculate))],
                    ['label' => '15%', 'value' => decimal_format(0.15 * ($order->payable_amount - $order->total_discount_calculate))]
                );
                $total_markup_Price = 0;
                $slot_based_Price = 0;
                foreach ($order->vendors as $vendor) {
                    $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order_id)->where('vendor_id', $vendor->vendor->id)->orderBy('id', 'DESC')->first();
                    if ($vendor_order_status) {
                        $vendor->order_status =  ['current_status' => ['id' => $vendor_order_status->OrderStatusOption->id, 'title' => __($vendor_order_status->OrderStatusOption->title)]];
                    } else {
                        $vendor->current_status = null;
                    }
                    $couponData = [];
                    $payable_amount = 0;
                    $total_container_charges = 0;
                    $discount_amount = 0;
                    $opt_quantity_price = 0;
                    $product_addons = [];
                    $vendor->vendor_name = $vendor->vendor->name;
                    foreach ($vendor->products as  $product) {

                        $product->longTermSchedule = array();
                        $product->recurring_date_count = 1;
                        if ($product->product->is_long_term_service == 1) {
                            $product->longTermSchedule =  OrderLongTermServices::with(['schedule', 'product.primary', 'addon.set', 'addon.option', 'addon.option.translation' => function ($q) use ($language_id) {
                                $q->select('addon_option_translations.id', 'addon_option_translations.addon_opt_id', 'addon_option_translations.title', 'addon_option_translations.language_id');
                                $q->where('addon_option_translations.language_id', $language_id);
                                $q->groupBy('addon_option_translations.addon_opt_id', 'addon_option_translations.language_id');
                            }])->where('order_product_id', $product->id)->first();
                            foreach ($product->longTermSchedule->addon as $ck => $addons) {
                                $opt_price_in_currency = $addons->option->price ?? 0;
                                $opt_price_in_doller_compare = $addons->option->price ?? 0;
                                if ($clientCurrency) {
                                    $opt_price_in_currency = $addons->option->price ?? 0 / $divider;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                }
                                $opt_quantity_price = decimal_format($opt_price_in_doller_compare * $product->quantity);
                                $addons->option->translation_title = ($addons->option->translation->isNotEmpty()) ? $addons->option->translation->first()->title : '';
                                $addons->option->price_in_cart = $addons->option->price;
                                $addons->option->price = decimal_format($opt_price_in_currency);
                                $addons->option->multiplier = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
                            }
                        }

                        //Mohit sir branch code by sohail
                        if ($order->luxury_option_id == 3) {
                            $processorProduct = ProcessorProduct::where('product_id', $product->product_id)->first();
                            $product->is_processor_enable = (isset($processorProduct->is_processor_enable) && $processorProduct->is_processor_enable == 1) ? true : false;
                            $product->processor_name = !empty($processorProduct->name) ? $processorProduct->name : '';
                            $product->processor_date = !empty($processorProduct->date) ? $processorProduct->date : '';
                            $product->address = !empty($processorProduct->address) ? $processorProduct->address : '';
                        } else {
                            $product->is_processor_enable = false;
                            $product->processor_name = '';
                            $product->processor_date = '';
                            $product->address = '';
                        }
                        $product->product_name = isset($product->translation) ? $product->translation->title : $product->product_name;
                        //till here
                        $product->scheduled_date_time = (($product->scheduled_date_time != null) ? dateTimeInUserTimeZone($product->scheduled_date_time, $user->timezone) : null);
                        $product_addons = [];
                        $variant_options = [];
                        $vendor_total_container_charges = 0;
                        $order_item_count += $product->quantity;
                        $product->image_path = $product->media->first() ? $product->media->first()->image->path : $product->image;
                        if ($product->pvariant) {
                            foreach ($product->pvariant->vset as $variant_set_option) {
                                $variant_options[] = array(
                                    'option' => $variant_set_option->optionData->trans->title,
                                    'title' => $variant_set_option->variantDetail->trans->title,
                                );
                            }
                        }
                        if ($product->user_product_order_form)
                            $product->user_product_order_form = json_decode($product->user_product_order_form);

                        $product->variant_options = $variant_options;
                        if (!empty($product->addon)) {
                            foreach ($product->addon as $k => $addon) {
                                // $product_addons[] = array(
                                //     'addon_id' =>  $addon->addon_id,
                                //     'addon_title' =>  $addon->set->title,
                                //     'option_title' =>  $addon->option->title,
                                // );
                                $opt_quantity_price = 0;
                                $opt_price_in_currency = $addon->option ? $addon->option->price : 0;
                                $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                $opt_quantity_price = $opt_price_in_doller_compare * $product->quantity;
                                $product_addons[$k]['quantity'] = $product->quantity;
                                $product_addons[$k]['addon_id'] = $addon->addon_id;
                                $product_addons[$k]['option_id'] = $addon->option_id;
                                $product_addons[$k]['price'] = $opt_price_in_currency;
                                $product_addons[$k]['addon_title'] = $addon->set->title;
                                $product_addons[$k]['quantity_price'] = $opt_quantity_price;
                                $product_addons[$k]['option_title'] = $addon->option ? $addon->option->title : 0;
                                // $product_addons[$k]['multiplier'] = $clientCurrency->doller_compare;
                            }
                        }

                        $product->product_addons = $product_addons;
                        if (auth()->user()->is_admin) {
                            $product->price = $product->price - $product->markup_price;
                        } else {
                            $product->price = $product->price;
                        }

                        $total_markup_Price += $product->markup_price;
                        if ($product->slot_id != '' && $product->delivery_date != '' && $product->slot_price != '') {
                            $slot_based_Price += $product->slot_price;
                        }
                    }
                    if ($vendor->delivery_fee > 0) {
                        $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                        $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                        $ETA = $order_pre_time + $user_to_vendor_time;
                        $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                    }
                    if ($vendor->dineInTable) {
                        $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                        $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                        $vendor->dineInTableCategory = $vendor->dineInTable->category->title; //$vendor->dineInTable->category->first() ? $vendor->dineInTable->category->first()->title : '';
                    }

                    $vendorId = $vendor->vendor->id;
                    //type must be a : delivery , takeaway,dine_in
                    $duration = Vendor::where('id', $vendorId)->select('slot_minutes', 'closed_store_order_scheduled')->first();
                    $slotsDate = findSlot('', $vendorId, '', 'api');
                    $slots = showSlot($slotsDate, $vendorId, 'delivery', $duration->slot_minutes, 1);
                    $vendor->slots = $slots;
                    if ($preferences->business_type == 'laundry') {
                        $dropoff_slots = showSlot($slotsDate, $vendorId, 'delivery', $duration->slot_minutes, 2);
                        $vendor->dropoff_slots = $dropoff_slots;
                    } else {
                        $vendor->dropoff_slots = [];
                    }
                    if (count($slots) > 0) {
                        $vendor->closed_store_order_scheduled = $duration->closed_store_order_scheduled ?? 0;
                    } else {
                        $vendor->closed_store_order_scheduled = 0;
                    }
                    $slotsDate = findSlot('', $vendorId, '', 'api');
                    $vendor->delaySlot = $slotsDate;
                    $vendor->same_day_orders_for_rescheduling = $preferences->same_day_orders_for_rescheduing ?? 0;

                    // dispatch status
                    $vendor->vendor_dispatcher_status = VendorOrderDispatcherStatus::whereNotIn('dispatcher_status_option_id', [2])
                        ->select('*', 'dispatcher_status_option_id as status_data')->where('order_id', $order_id)
                        ->where('vendor_id', $vendor->vendor->id)
                        ->get();
                    $vendor->vendor_dispatcher_status_count = 6;
                    $vendor->dispatcher_status_icons = [asset('assets/icons/driver_1_1.png'), asset('assets/icons/driver_2_1.png'), asset('assets/icons/driver_3_1.png'), asset('assets/icons/driver_4_1.png'), asset('assets/icons/driver_4_2.png'), asset('assets/icons/driver_5_1.png')];

                    // Start temp cart calculations
                    if ($vendor->tempCart) {
                        $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
                        $currId = ClientCurrency::where(['is_primary' => 1])->value('currency_id');
                        $tempCartController = new TempCartController();
                        $vendor->tempCart = $tempCartController->getCartForApproval($vendor->tempCart, $order, $langId, $currId, '');
                    } else {
                        $vendor->tempCart = null;
                    }
                }
                if (!empty($order->scheduled_date_time)) {
                    $order->scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
                }
                if (!empty($order->scheduled_slot)) {
                    $slot_time = explode("-", $order->scheduled_slot);
                    $start_time = $slot_time[0];
                    $end_time = !empty($slot_time[1]) ? $slot_time[1] : $slot_time[0];
                    $order->schedule_slot = date('d-m-Y h:i A', strtotime(date('Y-m-d', strtotime($order->scheduled_date_time)) . " " . $start_time)) . ' - ' . date('h:i A', strtotime($end_time));
                }
                $luxury_option_name = '';
                $order->luxury_option = [];
                if ($order->luxury_option_id > 0) {
                    $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
                    $order->luxury_option = $luxury_option;
                    if ($luxury_option->title == 'takeaway') {
                        $luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
                    } elseif ($luxury_option->title == 'dine_in') {
                        $luxury_option_name = $this->getNomenclatureName('Dine-In', $user->language, false);
                    } elseif ($luxury_option->title == 'on_demand') {
                        $luxury_option_name = $this->getNomenclatureName('Services', $user->language, false);
                    } else {
                        //$luxury_option_name = 'Delivery';
                        $luxury_option_name = getNomenclatureName($luxury_option->title);
                    }
                }
                $order->luxury_option_name = $luxury_option_name;
                $order->order_item_count = $order_item_count;
            }
            // 12345
            if (isset($request->new_dispatch_traking_url) && !empty($request->new_dispatch_traking_url)) {
                try {
                    $new_dispatch_traking_url = str_replace('/order/', '/order-details/', $request->new_dispatch_traking_url);

                    $response = Http::get($new_dispatch_traking_url);
                } catch (\Exception $ex) {
                }


                if (isset($response) && $response->status() == 200) {
                    $response = $response->json();

                    $order['order_data'] = $response;
                }
            }
            $user_id = $order->user_id ?? '';

            //$user_docs = UserDocs::where('user_id', $order->user_id)->get();
            $user_registration_documents = UserRegistrationDocuments::with('user_document', 'primary')
                ->whereHas('user_document', function ($q) use ($user_id) {
                    $q->where('user_id', $user_id);
                })->get();

            if (isset($order)) {
                $category_KYC_document =  CaregoryKycDoc::where('ordre_id', $order->id)->with('category_document.primary')->groupBy('category_kyc_document_id')->get();
            }
            // $category_KYC_document = CategoryKycDocuments::with('category_doc','primary')
            // ->whereHas('category_doc', function($q) use($order){
            //     $q->where('ordre_id',$order->id);
            // })->get();

            // $order['user_document_value'] =  $user_docs;

            if (auth()->user()->is_admin) {
                $order['total_amount'] = $order->total_amount  - $total_markup_Price;
                $order['payable_amount'] = $order->payable_amount  - $total_markup_Price;
            } else {
                $order['total_amount'] = $order->total_amount;
                $order['payable_amount'] = decimal_format($order->payable_amount);
            }
            //mohit sir branch code added by sohail
            $advancePayableAmount = 0;
            $pendingAmount = 0;
            $getAdditionalPreference = getAdditionalPreference(['advance_booking_amount', 'advance_booking_amount_percentage']);
            if (!empty($order->advance_amount) && !empty($getAdditionalPreference['advance_booking_amount']) && !empty($getAdditionalPreference['advance_booking_amount_percentage']) && ($getAdditionalPreference['advance_booking_amount_percentage'] > 0) && ($getAdditionalPreference['advance_booking_amount_percentage'] < 101)) {
                $advancePayableAmount = $order->advance_amount;
                $pendingAmount = $order['payable_amount'] - $order->advance_amount;
            }
            $order['advance_paid_amount'] = number_format((float)$advancePayableAmount, 2, '.', '');
            $order['pending_amount'] = number_format((float)$pendingAmount, 2, '.', '');
            //till here

            /* Check if other taxes available like: Tax on service fee, container charges, delivery fee and fixed fee .etc */
            $total_other_taxes = 0;
            if ($order->total_other_taxes != '') {
                foreach (explode(",", $order->total_other_taxes) as $row) {
                    $row1 = explode(":", $row);
                    $total_other_taxes += (float)$row1[1];
                }
            }

            // $order['user_document_value'] =  $user_docs;
            $order->taxable_amount =  decimal_format($total_other_taxes ?? 0);
            $order->total_other_taxes =  decimal_format($total_other_taxes ?? 0);
            $order['user_document_list'] =  $user_registration_documents;
            $order['category_KYC_document'] = $category_KYC_document ?? null;
            $order->slot_based_Price =  $slot_based_Price ?? 0;
            return $this->successResponse($order, null, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    //mohit sir brach code added by sohail
    public function orderUpdate(Request $request)
    {
        try {
            $roles = [
                'order_vendor_product_id'   => 'required',
                'order_product_old_price' => 'required',
                'new_product_price'   => 'required',
                'update_price_reason' => 'required|string'
            ];
            $validator = Validator::make($request->all(), $roles);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => __('Price & Reason both fields are required')]);
            }

            DB::beginTransaction();
            $orderProduct = OrderProduct::find($request->order_vendor_product_id);
            $orderProduct->price = decimal_format(isset($request->new_product_price) ? $request->new_product_price : 0);
            $orderProduct->old_price = isset($request->order_product_old_price) ? ($request->order_product_old_price) : 0;
            $orderProduct->updated_price_reason = isset($request->update_price_reason) ? ($request->update_price_reason) : 0;
            $orderProduct->save();
            $orderData = Order::find($orderProduct->order_id);
            $orderVendorData = OrderVendor::where('order_id', $orderProduct->order_id)->first();

            $newPayableAmount = 0;
            $newTotalAmount   = 0;
            $tax_category_ids = [];
            $productAddon_price = 0;
            $clientCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            if (!empty($orderProduct->addon)) {
                foreach ($orderProduct->addon as $ck => $addon) {
                    $opt_quantity_price = 0;
                    $opt_price_in_currency = $addon->option->price;
                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                    $opt_quantity_price = $opt_price_in_doller_compare *  $orderProduct->quantity;
                    $productAddon_price = $productAddon_price + $opt_quantity_price;
                }
            }
            $product_taxable_amount = 0;
            $taxable_amount = 0;
            $payable_amount = 0;
            $total_other_taxes = 0;
            $total_fixed_fee_tax = 0;
            $total_service_fee_tax = 0;
            $deliver_fee_charges_tax = 0;
            $total_markup_fee_tax = 0;
            $total_taxable_amount = 0;
            $container_charges_tax = 0;


            if ($orderData->total_other_taxes != '') {
                foreach (explode(",", $orderData->total_other_taxes) as $row) {
                    $row1 = explode(":", $row);
                    $check_row  = $row1[0];
                    if ($check_row == "product_tax_fee") {
                        $product_taxable_amount = $row1[1];
                    } else  if ($check_row == "tax_fixed_fee") {
                        $total_fixed_fee_tax = $row1[1];
                    } else  if ($check_row == "tax_service_charges") {
                        $total_service_fee_tax = $row1[1];
                    } else  if ($check_row == "tax_delivery_charges") {
                        $deliver_fee_charges_tax = $row1[1];
                    } else  if ($check_row == "tax_markup_fee") {
                        $total_markup_fee_tax = $row1[1];
                    } else  if ($check_row == "container_charges_tax") {
                        $container_charges_tax = $row1[1];
                    }
                }
            }

            $quantity_price = ($orderProduct->price * $orderProduct->quantity);
            if (isset($orderProduct->product->taxCategory)) {
                foreach ($orderProduct->product->taxCategory->taxRate as $tax_rate_detail) {
                    if (!in_array($tax_rate_detail->id, $tax_category_ids)) {
                        $tax_category_ids[] = $tax_rate_detail->id;
                    }
                    $rate = $tax_rate_detail->tax_rate;
                    $product_tax = ($quantity_price + $productAddon_price) * $rate / 100;
                    $product_taxable_amount = $taxable_amount + $product_tax;
                    $payable_amount = $payable_amount + $product_tax;
                }
            }
            $orderData->taxable_amount = $product_taxable_amount;
            $other_taxes_string = 'tax_fixed_fee:' . $total_fixed_fee_tax . ',tax_service_charges:' . $total_service_fee_tax . ',tax_delivery_charges:' . $deliver_fee_charges_tax . ',tax_markup_fee:' . $total_markup_fee_tax . ',product_tax_fee:' . $product_taxable_amount . ',container_charges_tax:' . $container_charges_tax;
            $orderData->total_other_taxes = $other_taxes_string;
            if ($orderProduct->old_price < $orderProduct->price) {
                $newPayableAmount =   ($orderProduct->price - $orderProduct->old_price) + ($product_taxable_amount - $orderData->taxable_amount) + $orderData->payable_amount;
                $newTotalAmount   = ($orderProduct->price - $orderProduct->old_price) + $orderData->total_amount;
                $orderVendorData->payable_amount =   $orderVendorData->payable_amount + ($product_taxable_amount - $orderData->taxable_amount);
            } else if ($orderProduct->old_price > $orderProduct->price) {
                $newPayableAmount =   $orderData->payable_amount - ($request->order_product_old_price - $request->new_product_price) + ($orderData->taxable_amount - $product_taxable_amount);
                $newTotalAmount   = $orderData->total_amount - ($request->order_product_old_price - $request->new_product_price);
                $orderVendorData->payable_amount =   $orderVendorData->payable_amount + ($orderData->taxable_amount - $product_taxable_amount);
            }
            if (!empty($newPayableAmount) && !empty($newTotalAmount)) {
                $orderData->total_amount   = decimal_format($newTotalAmount);
                $orderData->payable_amount = decimal_format($newPayableAmount);
                $orderData->save();
                $orderVendorData->subtotal_amount =   $newTotalAmount;
                $orderVendorData->taxable_amount =   $product_taxable_amount;
                $orderVendorData->save();
                DB::commit();
                return response()->json(['status' => 'success', 'message' => __('Product price updated Successfully.')]);
            } else {
                DB::rollback();
            }
            return response()->json(['status' => 'error', 'message' => __('Product price are same.')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    //till here


    public function submitEditedOrder(Request $request)
    {


        try {
            $rules = [
                'cart_id' => 'required',
                'address_id' => 'required:exists:user_addresses,id',
                'order_vendor_id' => 'required',
                'total_payable_amount' => 'required',
                'status'    => 'required'
                // 'payment_option_id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules, [
                'cart_id.required' => __('Cart data is required'),
                'address_id.required' => __('Address is required'),
                'order_vendor_id.required' => __('Order data is required'),
                'total_payable_amount.required' => __('Invalid Amount')
                // 'payment_option_id.required' => __('Payment Option is required')
            ]);
            if ($validator->fails()) {
                foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                    return $this->errorResponse(__($error_value[0]), 422);
                }
            }
            $status = $request->status;
            $cart_id = $request->cart_id;
            $order_vendor_id = $request->order_vendor_id;
            $new_payable_amount = $request->total_payable_amount;
            $action = ($request->has('type')) ? $request->type : 'delivery';
            $additionalPreference =  getAdditionalPreference(['is_service_product_price_from_dispatch']);
            $is_service_product_price_from_dispatch = 0;
            if (($additionalPreference['is_service_product_price_from_dispatch'] == 1) && ($action == 'on_demand')) { // luxury_option_id for ondemand
                $is_service_product_price_from_dispatch = $additionalPreference['is_service_product_price_from_dispatch'];
            }

            DB::beginTransaction();
            if ($status == 1) {
                ////// Edited Order accepted functionality /////
                $total_amount = 0;
                $total_discount = 0;
                $taxable_amount = 0;
                $payable_amount = 0;
                $tax_category_ids = [];
                $user = Auth::user();
                $language_id = $user->language ?? 1;

                if ($user) {
                    $user_wallet_balance = $user->balanceFloat;

                    // if($user_wallet_balance < $new_payable_amount){
                    //     return $this->errorResponse(__('Insufficient balance in your wallet'), 422);
                    // }

                    $subscription_features = array();
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
                    $loyalty_amount_saved = 0;
                    $redeem_points_per_primary_currency = '';
                    $loyalty_card = LoyaltyCard::where('status', '0')->first();
                    if ($loyalty_card) {
                        $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
                    }
                    $client_preference = ClientPreference::first();
                    // if ($client_preference->verify_email == 1) {
                    //     if ($user->is_email_verified == 0) {
                    //         return response()->json(['error' => 'Your account is not verified.'], 404);
                    //     }
                    // }
                    if ($client_preference->verify_phone == 1) {
                        if ($user->is_phone_verified == 0) {
                            return $this->errorResponse(__('Your phone is not verified.'), 404);
                        }
                    }
                    $user_address = UserAddress::where('id', $request->address_id)->first();
                    if (!$user_address) {
                        return $this->errorResponse(__('Invalid address id.'), 404);
                    }

                    $luxury_option = LuxuryOption::where('title', $action)->first();
                    $cart = TempCart::where('status', '0')->where('id', $cart_id)->where('order_vendor_id', $order_vendor_id)->where('is_submitted', 1)->where('is_approved', 0)->first(); //

                    if ($cart) {
                        $loyalty_points_used = 0;
                        // $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
                        // if ($order_loyalty_points_earned_detail) {
                        //     $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                        //     if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                        //         $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                        //     }
                        // }
                        $order = Order::whereHas('vendors', function ($q) use ($order_vendor_id) {
                            $q->where('id', $order_vendor_id);
                        })->first();

                        // $order_vendor = $order->vendors->first();

                        //Previous Order Total
                        $payment_status =  @$order->payment_status ?? 0;
                        // if($order->total_amount > 0){
                        $previous_order_total = $order->total_amount;
                        // }else{
                        // $previous_order_total = $order->total_amount + $order->wallet_amount_used + $order->loyalty_amount_saved + $order->taxable_amount + $order->total_delivery_fee + $order->tip_amount + $order->total_service_fee - $order->total_discount;
                        // }

                        if ($payment_status == 1) {
                            $order->advance_amount = $previous_order_total;
                            $order->is_postpay = 1;
                        }

                        // $order->user_id = $user->id;
                        // $order->order_number = generateOrderNo();
                        $order->address_id = $request->address_id;
                        // $order->payment_option_id = $request->payment_option_id;
                        // $order->comment_for_pickup_driver = $cart->comment_for_pickup_driver ?? null;
                        // $order->comment_for_dropoff_driver = $cart->comment_for_dropoff_driver ?? null;
                        // $order->comment_for_vendor = $cart->comment_for_vendor ?? null;
                        // $order->schedule_pickup = $cart->schedule_pickup ?? null;
                        // $order->schedule_dropoff = $cart->schedule_dropoff ?? null;
                        // $order->specific_instructions = $cart->specific_instructions ?? null;
                        // $order->is_gift = $request->is_gift ?? 0;
                        $order->update();


                        $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
                        foreach ($order_products as $order_prod) {
                            OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
                        }
                        //  OrderProduct::where('order_id', $order->id)->delete();
                        OrderProductPrescription::where('order_id', $order->id)->delete();
                        OrderTax::where('order_id', $order->id)->delete();

                        $customerCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
                        $clientCurrency = ClientCurrency::where('is_primary', '=', 1)->first();

                        $cart_products = TempCartProduct::with('product.pimage', 'product.variants', 'product.taxCategory.taxRate', 'coupon', 'product.addon')->where('cart_id', $cart->id)->where('status', [0, 1])->where('cart_id', $cart->id)->orderBy('created_at', 'asc')->get();

                        $total_subscription_discount = $total_delivery_fee = $total_service_fee = 0;

                        foreach ($cart_products->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {

                            $delivery_fee = 0;
                            $deliver_charge = $delivery_fee_charges = 0.00;
                            $delivery_count = 0;
                            $product_taxable_amount = 0;
                            $vendor_products_total_amount = 0;
                            $vendor_payable_amount = 0;
                            $vendor_discount_amount = 0;

                            $order_vendor = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor_id])->first();

                            if (!$order_vendor) {
                                $order_vendor = new OrderVendor();
                                $order_vendor->status = 0;
                                $order_vendor->user_id = $user->id;
                                $order_vendor->order_id = $order->id;
                                $order_vendor->vendor_id = $vendor_id;
                                $order_vendor->save();
                            }

                            foreach ($vendor_cart_products as $vendor_cart_product) {

                                $variant = $vendor_cart_product->product->variants->where('id', $vendor_cart_product->variant_id)->first();
                                $quantity_price = 0;
                                $divider = (empty($vendor_cart_product->doller_compare) || $vendor_cart_product->doller_compare < 0) ? 1 : $vendor_cart_product->doller_compare;
                                $price_in_currency = $variant->price / $divider;
                                $price_in_dollar_compare = $price_in_currency * $clientCurrency->doller_compare;
                                if ($is_service_product_price_from_dispatch  == 1) {
                                    $price_in_dollar_compare = $vendor_cart_product->dispatch_agent_price;
                                }
                                $quantity_price = $price_in_dollar_compare * $vendor_cart_product->quantity;
                                $payable_amount = $payable_amount + $quantity_price;
                                $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price;
                                $vendor_payable_amount = $vendor_payable_amount + $quantity_price;
                                $product_payable_amount = 0;
                                $vendor_taxable_amount = 0;

                                if (isset($vendor_cart_product->product->taxCategory)) {
                                    foreach ($vendor_cart_product->product->taxCategory->taxRate as $tax_rate_detail) {
                                        if (!in_array($tax_rate_detail->id, $tax_category_ids)) {
                                            $tax_category_ids[] = $tax_rate_detail->id;
                                        }
                                        $rate = round($tax_rate_detail->tax_rate);
                                        $tax_amount = ($price_in_dollar_compare * $rate) / 100;
                                        $product_tax = $quantity_price * $rate / 100;
                                        // $taxable_amount = $taxable_amount + $product_tax;
                                        $product_taxable_amount += $product_tax;
                                        $payable_amount = $payable_amount + $product_tax;
                                    }
                                }

                                if (($action == 'delivery' || $action == 'on_demand') && ($is_service_product_price_from_dispatch != 1)) {
                                    if ((!empty($vendor_cart_product->product->Requires_last_mile)) && ($vendor_cart_product->product->Requires_last_mile == 1)) {
                                        $delivery_fee = $this->getDeliveryFeeDispatcher($vendor_cart_product->vendor_id, $user->id);

                                        if (!empty($delivery_fee) && $delivery_count == 0) {
                                            $delivery_count = 1;
                                            $vendor_cart_product->delivery_fee = decimal_format($delivery_fee);
                                            // $payable_amount = $payable_amount + $delivery_fee;
                                            $delivery_fee_charges = $delivery_fee;
                                            $latitude = $request->header('latitude');
                                            $longitude = $request->header('longitude');
                                            $vendor_cart_product->vendor = $this->getVendorDistanceWithTime($latitude, $longitude, $vendor_cart_product->vendor, $client_preference);
                                            $order_vendor->order_pre_time = ($vendor_cart_product->vendor->order_pre_time > 0) ? $vendor_cart_product->vendor->order_pre_time : 0;
                                            if ($vendor_cart_product->vendor->timeofLineOfSightDistance > 0) {
                                                $order_vendor->user_to_vendor_time = $vendor_cart_product->vendor->timeofLineOfSightDistance - $order_vendor->order_pre_time;
                                            }
                                        }
                                    }
                                }
                                $taxable_amount += $product_taxable_amount;
                                $vendor_taxable_amount += $taxable_amount;
                                $total_amount += $vendor_cart_product->quantity * $variant->price;
                                //need to sub markup price

                                $order_product = OrderProduct::where(['order_id' => $order->id, 'order_vendor_id' => $order_vendor->id, 'product_id' => $vendor_cart_product->product_id])->first() ??  new OrderProduct;
                                $order_product->order_vendor_id = $order_vendor->id;
                                $order_product->order_id = $order->id;
                                $order_product->price = $variant->price;
                                if ($is_service_product_price_from_dispatch  == 1) {
                                    $order_product->dispatch_agent_id = $vendor_cart_product->dispatch_agent_id;
                                    $order_product->is_price_buy_driver = 1;
                                    $order_product->price = $vendor_cart_product->dispatch_agent_price;
                                }
                                $order_product->taxable_amount = $product_taxable_amount;
                                $order_product->quantity = $vendor_cart_product->quantity;
                                $order_product->vendor_id = $vendor_cart_product->vendor_id;
                                $order_product->product_id = $vendor_cart_product->product_id;
                                $order_product->created_by = $vendor_cart_product->created_by;
                                $order_product->variant_id = $vendor_cart_product->variant_id;

                                $product_variant_sets = '';
                                if (isset($vendor_cart_product->variant_id) && !empty($vendor_cart_product->variant_id)) {
                                    $var_sets = ProductVariantSet::where('product_variant_id', $vendor_cart_product->variant_id)->where('product_id', $vendor_cart_product->product->id)
                                        ->with([
                                            'variantDetail.trans' => function ($qry) use ($language_id) {
                                                $qry->where('language_id', $language_id);
                                            },
                                            'optionData.trans' => function ($qry) use ($language_id) {
                                                $qry->where('language_id', $language_id);
                                            }
                                        ])->get();
                                    if (count($var_sets)) {
                                        foreach ($var_sets as $set) {
                                            if (isset($set->variantDetail) && !empty($set->variantDetail)) {
                                                $product_variant_set = @$set->variantDetail->trans->title . ":" . @$set->optionData->trans->title . ", ";
                                                $product_variant_sets .= $product_variant_set;
                                            }
                                        }
                                    }
                                }

                                $order_product->product_variant_sets = $product_variant_sets;
                                if (!empty($vendor_cart_product->product->title))
                                    $vendor_cart_product->product->title = $vendor_cart_product->product->title;
                                elseif (empty($vendor_cart_product->product->title)  && !empty($vendor_cart_product->product->translation))
                                    $vendor_cart_product->product->title = $vendor_cart_product->product->translation[0]->title;
                                else
                                    $vendor_cart_product->product->title = $vendor_cart_product->product->sku;

                                $order_product->product_name = $vendor_cart_product->product->title ?? $vendor_cart_product->product->sku;
                                $order_product->product_dispatcher_tag = $vendor_cart_product->product->tags;
                                if ($vendor_cart_product->product->pimage) {
                                    $order_product->image = $vendor_cart_product->product->pimage->first() ? $vendor_cart_product->product->pimage->first()->path : '';
                                }
                                $order_product->save();
                                if (!empty($vendor_cart_product->addon)) {
                                    foreach ($vendor_cart_product->addon as $ck => $addon) {
                                        $opt_quantity_price = 0;
                                        $opt_price_in_currency = $addon->option->price;
                                        $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                        $opt_quantity_price = $opt_price_in_doller_compare * $order_product->quantity;
                                        $total_amount = $total_amount + $opt_quantity_price;
                                        $payable_amount = $payable_amount + $opt_quantity_price;
                                        $vendor_payable_amount = $vendor_payable_amount + $opt_quantity_price;
                                    }
                                }
                                $cart_addons = TempCartAddon::where('cart_product_id', $vendor_cart_product->id)->get();
                                if ($cart_addons) {
                                    foreach ($cart_addons as $cart_addon) {
                                        $orderAddon = new OrderProductAddon;
                                        $orderAddon->addon_id = $cart_addon->addon_id;
                                        $orderAddon->option_id = $cart_addon->option_id;
                                        $orderAddon->order_product_id = $order_product->id;
                                        $orderAddon->save();
                                    }
                                    if (($request->payment_option_id != 7) && ($request->payment_option_id != 6)) { // if not mobbex, payfast
                                        //        CartAddon::where('cart_product_id', $vendor_cart_product->id)->delete();
                                    }
                                }
                            }

                            $coupon_id = null;
                            $coupon_name = null;
                            $actual_amount = $vendor_payable_amount;
                            if ($vendor_cart_product->coupon && !empty($vendor_cart_product->coupon->promo)) {
                                $coupon_id = $vendor_cart_product->coupon->promo->id;

                                if ($vendor_cart_product->coupon->promo->paid_by_vendor_admin == 0) {
                                    $coupon_paid_by = 0;
                                }

                                $coupon_name = $vendor_cart_product->coupon->promo->name;
                                if ($vendor_cart_product->coupon->promo->promo_type_id == 2) {
                                    $coupon_discount_amount = $vendor_cart_product->coupon->promo->amount;
                                    $total_discount += $coupon_discount_amount;
                                    $vendor_payable_amount -= $coupon_discount_amount;
                                    $vendor_discount_amount += $coupon_discount_amount;
                                } else {
                                    $coupon_discount_amount = ($quantity_price * $vendor_cart_product->coupon->promo->amount / 100);
                                    $final_coupon_discount_amount = $coupon_discount_amount * $clientCurrency->doller_compare;
                                    $total_discount += $final_coupon_discount_amount;
                                    $vendor_payable_amount -= $final_coupon_discount_amount;
                                    $vendor_discount_amount += $final_coupon_discount_amount;
                                }
                            }
                            //Start applying service fee on vendor products total
                            $vendor_service_fee_percentage_amount = 0;
                            if ($vendor_cart_product->vendor->service_fee_percent > 0) {
                                $vendor_service_fee_percentage_amount = ($vendor_products_total_amount * $vendor_cart_product->vendor->service_fee_percent) / 100;
                                $vendor_payable_amount += $vendor_service_fee_percentage_amount;
                                $payable_amount += $vendor_service_fee_percentage_amount;
                            }
                            //End applying service fee on vendor products total
                            $total_service_fee = $total_service_fee + $vendor_service_fee_percentage_amount;
                            $order_vendor->service_fee_percentage_amount = $vendor_service_fee_percentage_amount;

                            $total_delivery_fee += $delivery_fee;
                            $vendor_payable_amount += $delivery_fee;
                            $vendor_payable_amount += $vendor_taxable_amount;

                            $order_vendor->coupon_id = $coupon_id;
                            $order_vendor->coupon_paid_by = $coupon_paid_by ?? 1;
                            $order_vendor->coupon_code = $coupon_name;
                            $order_vendor->order_status_option_id = 1;
                            $order_vendor->delivery_fee = $delivery_fee;
                            $order_vendor->subtotal_amount = $actual_amount;
                            $order_vendor->payable_amount = $vendor_payable_amount;
                            $order_vendor->taxable_amount = $vendor_taxable_amount;
                            $order_vendor->discount_amount = $vendor_discount_amount;
                            // $order_vendor->payment_option_id = $request->payment_option_id;
                            $vendor_info = Vendor::where('id', $vendor_id)->first();
                            if ($vendor_info) {
                                if (($vendor_info->commission_percent) != null && $vendor_payable_amount > 0) {
                                    $order_vendor->admin_commission_percentage_amount = round($vendor_info->commission_percent * ($vendor_payable_amount / 100), 2);
                                }
                                if (($vendor_info->commission_fixed_per_order) != null && $vendor_payable_amount > 0) {
                                    $order_vendor->admin_commission_fixed_amount = $vendor_info->commission_fixed_per_order;
                                }
                            }
                            $order_vendor->update();

                            $order_status = VendorOrderStatus::where('order_id', $order->id)->where('order_vendor_id', $order_vendor_id)->first();
                            if (!$order_status) {
                                $order_status = new VendorOrderStatus();
                                $order_status->order_id = $order->id;
                                $order_status->vendor_id = $vendor_id;
                                $order_status->order_status_option_id = 1;
                                $order_status->order_vendor_id = $order_vendor->id;
                                $order_status->save();
                            }
                        }
                        // $loyalty_points_earned = LoyaltyCard::getLoyaltyPoint($loyalty_points_used, $payable_amount);
                        if (in_array(1, $subscription_features)) {
                            $total_subscription_discount = $total_subscription_discount + $total_delivery_fee;
                        }
                        $total_discount = $total_discount + $total_subscription_discount;
                        $order->total_amount = $total_amount;
                        $order->total_discount = $total_discount;
                        $order->taxable_amount = $taxable_amount;
                        $payable_amount = $payable_amount + $total_delivery_fee - $total_discount;
                        // if ($loyalty_amount_saved > 0) {
                        //     if ($loyalty_amount_saved > $payable_amount) {
                        //         $loyalty_amount_saved = $payable_amount;
                        //         $loyalty_points_used = $payable_amount * $redeem_points_per_primary_currency;
                        //     }
                        // }
                        // $payable_amount = $payable_amount - $loyalty_amount_saved;

                        $difference_to_be_paid = $payable_amount - $previous_order_total;

                        // dd($difference_to_be_paid);

                        // If new amount is greater than previous amount then deduct from wallet
                        if ($difference_to_be_paid > 0) {
                            // deduct if payment method is not cash on delivery and offline mathod method
                            if ($request->payment_option_id != 1 && $request->payment_option_id != 38) {
                                $wallet_amount_used = 0;
                                if ($user->balanceFloat > 0) {
                                    $wallet = $user->wallet;
                                    $wallet_amount_used = $user->balanceFloat;

                                    if ($difference_to_be_paid > $wallet_amount_used) {
                                        return $this->errorResponse(__('Insufficient balance in your wallet'), 422);
                                    }

                                    // if ($wallet_amount_used > $payable_amount) {
                                    //     $wallet_amount_used = $payable_amount;
                                    // }
                                    $order->wallet_amount_used = $wallet_amount_used;
                                    if ($wallet_amount_used > 0) {
                                        $wallet->withdrawFloat($order->wallet_amount_used, ['Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>']);
                                    }
                                }
                                // $payable_amount = $payable_amount - $wallet_amount_used;
                            }
                        } else {
                            if ($difference_to_be_paid < 0) {
                                $wallet = $user->wallet;
                                $difference_to_be_paid = abs($difference_to_be_paid);
                                $wallet->depositFloat($difference_to_be_paid, ['Wallet has been <b>credited</b> for order number <b>' . $order->order_number . '</b>']);
                            }
                        }


                        // $tip_amount = 0;
                        // if ((isset($request->tip)) && ($request->tip != '') && ($request->tip > 0)) {
                        //     $tip_amount = $request->tip;
                        //     $tip_amount = ($tip_amount / $customerCurrency->doller_compare) * $clientCurrency->doller_compare;
                        //     $order->tip_amount = number_format($tip_amount, 2);
                        // }
                        // $payable_amount = $payable_amount + $tip_amount;
                        $order->tip_amount = 0;
                        $order->total_service_fee = $total_service_fee;
                        $order->total_delivery_fee = $total_delivery_fee;
                        $order->loyalty_points_used = 0;
                        $order->loyalty_amount_saved = 0; //$loyalty_amount_saved;
                        $order->loyalty_points_earned = $loyalty_points_earned['per_order_points'] ?? 0;
                        $order->loyalty_membership_id = $loyalty_points_earned['loyalty_card_id'] ?? 0;
                        $order->scheduled_date_time = $cart->schedule_type == 'schedule' ? $cart->scheduled_date_time : null;
                        $order->subscription_discount = $total_subscription_discount;
                        $order->luxury_option_id = $luxury_option->id;
                        $order->payable_amount = $payable_amount;
                        $order->payment_status = 1;

                        // if (($payable_amount == 0) || (($request->has('transaction_id')) && (!empty($request->transaction_id)))) {
                        //     $order->payment_status = 1;
                        // }
                        $order->update();
                        foreach ($cart_products->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                            $this->sendSuccessEmail($request, $order, $vendor_id);
                        }
                        $res = $this->sendSuccessEmail($request, $order);

                        // $ex_gateways = [5, 6, 7, 8, 9, 10, 11, 12, 13, 17]; // if paystack, mobbex, payfast, yoco, razorpay, gcash, simplify, square, checkout
                        $ex_gateways = [
                            4,
                            5,
                            7,
                            8,
                            9,
                            10,
                            12,
                            13,
                            15,
                            17,
                            18,
                            19,
                            20,
                            21,
                            23,
                            24,
                            25,
                            26,
                            28,
                            29,
                            30,
                            31,
                            32,
                            34,
                            35,
                            36,
                            37,
                            39,
                            40,
                            41,
                            42,
                            43,
                            44,
                            45,
                            47
                        ]; // stripe, mobbex,yoco,pointcheckout,razorpay,simplified,square,pagarme, checkout,Authourize, stripe_fpx,KongaPay, cashfree,easubuzz,vnpay, payu,mycash,Stipre_oxxo,stripe_ideal
                        // if (!in_array($request->payment_option_id, $ex_gateways)) {
                        //     Cart::where('id', $cart->id)->update(['schedule_type' => NULL, 'scheduled_date_time' => NULL]);
                        //     CartCoupon::where('cart_id', $cart->id)->delete();
                        //     CartProduct::where('cart_id', $cart->id)->delete();
                        //     CartProductPrescription::where('cart_id', $cart->id)->delete();
                        // }

                        if (count($tax_category_ids)) {
                            foreach ($tax_category_ids as $tax_category_id) {
                                $order_tax = new OrderTax();
                                $order_tax->order_id = $order->id;
                                $order_tax->tax_category_id = $tax_category_id;
                                $order_tax->save();
                            }
                        }
                        $cart->is_approved = 1;
                        $cart->update();

                        // if (($request->payment_option_id != 1) && ($request->payment_option_id != 2) && ($request->has('transaction_id')) && (!empty($request->transaction_id))) {
                        $order_payment = Payment::where('order_id', $order->id)->first();

                        if ($order_payment) {
                            $order_payment->date = date('Y-m-d');
                            $order_payment->balance_transaction = $order->payable_amount;
                            $order_payment->update();
                        }
                        // }
                        $order = $order->with(['vendors:id,order_id,dispatch_traking_url,vendor_id', 'user_vendor', 'vendors.vendor'])->where('order_number', $order->order_number)->first();

                        if (! in_array($request->payment_option_id, $ex_gateways) || (isset($request->is_postpay) && $request->is_postpay == 1)) {
                            $code = $request->header('code');

                            if (!empty($order->vendors)) {
                                foreach ($order->vendors as $vendor_value) {
                                    $vendor_order_detail = $this->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                                    $user_vendors = UserVendor::where(['vendor_id' => $vendor_value->vendor_id])->pluck('user_id');
                                    $vendorDetail = $vendor_value->vendor;

                                    if ($vendorDetail->auto_accept_order == 0 && $vendorDetail->auto_reject_time > 0) {
                                        $clientDetail = Client::on('mysql')->where(['code' => $client_preference->client_code])->first();
                                        AutoRejectOrderCron::on('mysql')->create(['database_host' => $clientDetail->database_path, 'database_name' => $clientDetail->database_name, 'database_username' => $clientDetail->database_username, 'database_password' => $clientDetail->database_password, 'order_vendor_id' => $vendor_value->id, 'auto_reject_time' => Carbon::now()->addMinute($vendorDetail->auto_reject_time)]);
                                    }
                                    $this->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail, $code);
                                }
                            }
                            $vendor_order_detail = $this->minimize_orderDetails_for_notification($order->id);
                            $super_admin = User::where('is_superadmin', 1)->pluck('id');
                            $this->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail, $code);
                        }

                        DB::commit();

                        // Send push notification to order specific driver
                        $this->sendEditOrderApprovalStatusNotification($order_vendor, 1);

                        # if payment type cash on delivery or payment status is 'Paid'
                        if (($order->payment_option_id == 1) || (($order->payment_option_id != 1) && ($order->payment_status == 1))) {
                            # if vendor selected auto accept
                            # uncheck_vendor_statusin case of order edit by driver
                            $uncheck_vendor_status  = 1;
                            $autoaccept = $this->autoAcceptOrderIfOn($order->id, $uncheck_vendor_status); //
                        }

                        $this->sendSuccessSMS($request, $order);

                        return $this->successResponse($order, __('Order accepted successfully.'), 201);
                    } else {
                        return $this->errorResponse(__('Order already submitted'), 422);
                    }
                } else {
                    return $this->errorResponse(__('Invalid data'), 422);
                }
            } else {
                ////// Edited Order rejected functionality /////
                $cart = TempCart::where('status', '0')->where('id', $cart_id)->where('order_vendor_id', $order_vendor_id)->where('is_submitted', 1)->where('is_approved', 0)->first();
                if ($cart) {
                    $cart->is_approved = 2;
                    $cart->update();

                    // Send push notification to order specific driver
                    $order_vendor = OrderVendor::select('web_hook_code')->where('id', $order_vendor_id)->first();
                    $this->sendEditOrderApprovalStatusNotification($order_vendor, 2);
                }
                DB::commit();
                return $this->successResponse($cart, __('Order rejected successfully.'), 201);
            }
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function sendEditOrderApprovalStatusNotification($order_vendor, $status)
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $postdata =  ['web_hook_code' => $order_vendor->web_hook_code, 'status' => $status];
                $client = new GCLIENT([
                    'headers' => [
                        'personaltoken' => $dispatch_domain->delivery_service_key,
                        'shortcode' => $dispatch_domain->delivery_service_key_code,
                        'content-type' => 'application/json'
                    ]
                ]);
                $url = $dispatch_domain->delivery_service_key_url;
                $res = $client->post(
                    $url . '/api/edit-order/driver/notify',
                    ['form_params' => ($postdata)]
                );
                $response = json_decode($res->getBody(), true);
                // if ($response && $response['message'] == 'success') {
                //     return $response;
                // }
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function postVendorOrderStatusUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            $order_id = $request->order_id;
            $vendor_id = $request->vendor_id;
            $reject_reason = $request->reject_reason;
            $order_vendor_id = $request->order_vendor_id;
            $order_status_option_id = $request->order_status_option_id;
            $client_preference = ClientPreference::first();
            if ($order_status_option_id == 7) {
                $order_status_option_id = 2;
                $request->order_status_option_id = 2;
            } else if ($order_status_option_id == 8) {
                $order_status_option_id = 3;
                $request->order_status_option_id = 3;
            }


            // $vendor_order_status = VendorOrderStatus::where('order_id', $order_id)->where('vendor_id', $vendor_id)->first();
            $currentOrderStatus = OrderVendor::where(['vendor_id' => $request->vendor_id, 'order_id' => $request->order_id])->first();

            if ($currentOrderStatus->order_status_option_id == 3) {
                //$request->status_option_id == 2){

                return response()->json(['status' => 'error', 'message' => __('This Order has been rejected.')]);
            }
            $vendor_order_status_detail = VendorOrderStatus::where('order_id', $order_id)->where('vendor_id', $vendor_id)->where('order_status_option_id', $order_status_option_id)->first();

            if (!$vendor_order_status_detail) {
                // $vendor_order_status = new VendorOrderStatus();
                // $vendor_order_status->order_id = $order_id;
                // $vendor_order_status->vendor_id = $vendor_id;
                // $vendor_order_status->order_status_option_id = $order_status_option_id;
                // $vendor_order_status->order_vendor_id = $vendor_order_status->order_vendor_id;
                // $vendor_order_status->save();



                if ($order_status_option_id == 2 || $order_status_option_id == 3) {
                    $clientDetail = Client::on('mysql')->where(['code' => $client_preference->client_code])->first();
                    AutoRejectOrderCron::on('mysql')->where(['database_name' => $clientDetail->database_name, 'order_vendor_id' => $currentOrderStatus->id])->delete();
                }
                $current_status = OrderStatusOption::select('id', 'title')->find($order_status_option_id);

                if ($order_status_option_id == 2) {
                    $upcoming_status = OrderStatusOption::select('id', 'title')->where('id', '>', 3)->first();
                } elseif ($order_status_option_id == 3) {
                    $upcoming_status = null;
                } elseif ($order_status_option_id == 6) {
                    $upcoming_status = null;
                } else {
                    $upcoming_status = OrderStatusOption::select('id', 'title')->where('id', '>', $order_status_option_id)->first();
                }
                $order_status = [
                    'current_status' => $current_status,
                    'upcoming_status' => $upcoming_status,
                ];

                $orderPlaced = true;
                $orderData = OrderVendor::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->first();
                if ($request->order_status_option_id == 2) {
                    //Check Order delivery type
                    if ($orderData->shipping_delivery_type == 'D') {
                        //Create Shipping request for dispatcher
                        $order_dispatch = $this->checkIfanyProductLastMileon($request);
                        if ($order_dispatch && $order_dispatch == 1) {
                            $stats = $this->insertInVendorOrderDispatchStatus($request);
                            $orderPlaced = true;
                        }
                    } elseif ($orderData->shipping_delivery_type == 'L') {
                        //Create Shipping place order request for Lalamove
                        //$orderPlaced = $this->placeOrderRequestlalamove($request);
                    } elseif ($orderData->shipping_delivery_type == 'B') {
                        $orderPlaced = $this->placeOrderRequestBorzoeApi($request);
                    } elseif ($orderData->shipping_delivery_type == 'K') {
                        //Create Shipping place order request for Kwik
                        $orderPlaced = $this->placeOrderRequestKwikApi($request);
                    } elseif ($orderData->shipping_delivery_type == 'SR') {
                        //Create Shipping place order request for Shiprocket
                        $orderPlaced = $this->placeOrderRequestShiprocket($request);
                    } elseif ($orderData->shipping_delivery_type == 'DU') {
                        //Create Shipping place order request for Dunzo
                        $orderPlaced = $this->placeOrderRequestDunzo($request);
                    } elseif ($orderData->shipping_delivery_type == 'M') {
                        //Create Shipping place order request for Ahoy Masa
                        $orderPlaced = $this->placeOrderRequestAhoy($request);
                    } elseif ($orderData->shipping_delivery_type == 'SH') {
                        //Create Shipping place order request for Shippo Masa
                        $orderPlaced = $this->placeOrderRequestShippo($request);
                    }
                    $orderData->accepted_by = auth()->id();
                    $orderData->save();
                }

                if ($request->order_status_option_id == 4 && $orderData->shipping_delivery_type == 'L') {
                    //Create Shipping place order request for Lalamove
                    $orderPlaced = $this->placeOrderRequestlalamove($request);
                }

                if ($orderPlaced) {
                    $vendor_order_status = new VendorOrderStatus();
                    $vendor_order_status->order_id = $request->order_id;
                    $vendor_order_status->vendor_id = $request->vendor_id;
                    $vendor_order_status->order_vendor_id = $request->order_vendor_id;
                    $vendor_order_status->order_status_option_id = $request->order_status_option_id;
                    $vendor_order_status->save();



                    if ($order_status_option_id == 3) {
                        if ($orderData->shipping_delivery_type == 'D' && !empty($currentOrderStatus->dispatch_traking_url)) {
                            $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $currentOrderStatus->dispatch_traking_url);
                            $response = Http::get($dispatch_traking_url);
                        } elseif ($orderData->shipping_delivery_type == 'L') {
                            //Cancel Shipping place order request for Lalamove
                            $lala = new LalaMovesController();
                            $order_lalamove = $lala->cancelOrderRequestlalamove($currentOrderStatus->web_hook_code);
                        } elseif ($orderData->shipping_delivery_type == 'K') {
                            //Cancel Shipping place order request for KwikApi
                            $lala = new QuickApiController();
                            $order_lalamove = $lala->cancelOrderRequestKwikApi($request->order_id, $request->vendor_id);
                        } elseif ($orderData->shipping_delivery_type == 'B') {
                            //Cancel Shipping place order request for Borzoe
                            // $borzoe = new BorzoeDeliveryController();
                            $order_lalamove = $this->cancleOrderToBorzoApi($request->vendor_id, $request->order_id);
                        } elseif ($orderData->shipping_delivery_type == 'SR') {
                            //Cancel Shipping place order request for Shiprocket
                            $ship = new ShiprocketController();
                            $order_ship = $ship->cancelOrderRequestShiprocket($currentOrderStatus->ship_order_id);
                        } elseif ($orderData->shipping_delivery_type == 'DU') {
                            //Cancel Dunzo place order request for Dunzo
                            $ship = new DunzoController();
                            $order_ship = $ship->cancelOrderRequestDunzo($currentOrderStatus->web_hook_code);
                        } elseif ($orderData->shipping_delivery_type == 'M') {
                            //Create Shipping place order request for Ahoy
                            $ship = new AhoyController();
                            $order_ship = $ship->cancelOrderRequestAhoy($currentOrderStatus->web_hook_code);
                        }

                        $vendor_id = $request->vendor_id;

                        $order = Order::with(array(
                            'vendors' => function ($query) use ($vendor_id) {
                                $query->where('vendor_id', $vendor_id);
                            }
                        ))->find($request->order_id);
                        // get vendor return amount from order
                        $return_response =  $this->GetVendorReturnAmount($request, $order);

                        // return amount to user wallet
                        if ($return_response['vendor_return_amount'] > 0) {
                            $user = User::find($currentOrderStatus->user_id);
                            $wallet = $user->wallet;
                            $credit_amount = $return_response['vendor_return_amount']; //$currentOrderStatus->payable_amount;
                            $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $currentOrderStatus->orderDetail->order_number . ' (' . $currentOrderStatus->vendor->name . ')']);
                            $this->sendWalletNotification($user->id, $currentOrderStatus->orderDetail->order_number);
                        }

                        // diarise loyalty in order table
                        $order->loyalty_points_used    =  $order->loyalty_points_used - $return_response['vendor_loyalty_points'];
                        $order->loyalty_amount_saved   =  $order->loyalty_amount_saved - $return_response['vendor_loyalty_amount'];
                        $order->loyalty_points_earned  =  $order->loyalty_points_earned - $return_response['vendor_loyalty_points_earned'];
                        $order->save();
                        // save payment in table
                        $vendor_return_payment                          = new VendorOrderCancelReturnPayment();
                        $vendor_return_payment->order_id                = $order->id;
                        $vendor_return_payment->order_vendor_id         = $currentOrderStatus->id;
                        $vendor_return_payment->wallet_amount           = $return_response['vendor_wallet_amount'];
                        $vendor_return_payment->online_payment_amount   = $return_response['vendor_online_payment_amount'];
                        $vendor_return_payment->loyalty_amount          = $return_response['vendor_loyalty_amount'];
                        $vendor_return_payment->loyalty_points          = $return_response['vendor_loyalty_points'];
                        $vendor_return_payment->loyalty_points_earned   = $return_response['vendor_loyalty_points_earned'];
                        $vendor_return_payment->total_return_amount     = $return_response['vendor_return_amount'];
                        $vendor_return_payment->save();
                    }

                    OrderVendor::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->update(['order_status_option_id' => $request->order_status_option_id, 'reject_reason' => $request->reject_reason ?? null, 'cancelled_by' => $request->cancelled_by ?? null]);
                }


                // if (!empty($currentOrderStatus->dispatch_traking_url) && ($request->order_status_option_id == 3)) {
                //     $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $currentOrderStatus->dispatch_traking_url);
                //     $response = Http::get($dispatch_traking_url);
                // }



                $orderData = Order::find($order_id);

                DB::commit();
                // $this->sendSuccessNotification(Auth::user()->id, $request->vendor_id);
                $code = $request->header('code');
                $this->sendStatusChangePushNotificationCustomer([$orderData->user_id], $orderData, $order_status_option_id, $code);
                return response()->json([
                    'status' => 'success',
                    'order_status' => $order_status,
                    'message' => 'Order Status Updated Successfully.'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function sendOrderPushNotificationVendors($user_ids, $orderData, $header_code = '')
    {

        $devices = UserDevice::where('is_vendor_app', 0)->whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();
        $from = '';
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            $from = $client_preferences->fcm_server_key;
        }
        $notification_content = NotificationTemplate::where('id', 4)->first();
        $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
        if ($notification_content) {
            if ($header_code == '') {
                $header_code = Client::orderBy('id', 'asc')->first()->code;
            }
            $code = $header_code;
            $client = Client::where('code', $code)->first();
            $redirect_URL = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/client/order";

            //Order Notifications Logs
            OrderNotificationsLogs::updateOrCreate([
                'order_vendor_id' => $orderData->vendors[0]->id
            ], [
                'user_id' => auth()->id(),
                'order_number' => $orderData->order_number,
                'vendor_id' => $orderData->vendors[0]->vendor_id,
                'order_vendor_id' => $orderData->vendors[0]->id,
                'order_id' => $orderData->id,
                'message' => $body_content . ', <a href="' . $redirect_URL . '">#' . $orderData->order_number . '</a>'
            ]);

            $data = [
                "registration_ids" => $devices,
                "notification" => [
                    'title' => $notification_content->subject,
                    'body'  => $body_content,
                    'sound' => "notification.wav",
                    "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                    // 'click_action' => $redirect_URL,
                    "android_channel_id" => "sound-channel-id"
                ],
                "data" => [
                    'title' => $notification_content->subject,
                    'body'  => $notification_content->content,
                    'data' => $orderData,
                    'order_id' => $orderData->id,
                    'type' => "order_created"
                ],
                "priority" => "high"
            ];
            if (!empty($from)) {
                sendFcmCurlRequest($data);
            }

            $vendorAppUserDevices = UserDevice::where('is_vendor_app', 1)->whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();
            if (!empty($vendorAppUserDevices) && !empty($client_preferences->vendor_fcm_server_key)) {
                $from = $client_preferences->vendor_fcm_server_key;
                $data['registration_ids'] = $vendorAppUserDevices;
                $result = sendFcmCurlRequest($data, $from, 1);
            }
        }
    }

    public function sendStatusChangePushNotificationCustomer($user_ids, $orderData, $order_status_id, $header_code)
    {
        $devices = UserDevice::where('is_vendor_app', 0)->whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();
        $vendorAppDevices = UserDevice::where('is_vendor_app', 1)->whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();

        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {

            if ($order_status_id == 2 || $order_status_id == 7) {
                $notification_content = NotificationTemplate::where('id', 5)->first();
            } elseif ($order_status_id == 3 || $order_status_id == 8) {
                $notification_content = NotificationTemplate::where('id', 6)->first();
            } elseif ($order_status_id == 4) {
                $notification_content = NotificationTemplate::where('id', 7)->first();
            } elseif ($order_status_id == 5) {

                //Check for order is takeaway
                if (@$orderData->luxury_option_id == 3) {
                    $notification_content = NotificationTemplate::where('slug', 'order-out-for-takeaway-delivery')->first();
                } else {
                    $notification_content = NotificationTemplate::where('id', 8)->first();
                }
            } elseif ($order_status_id == 6) {
                $notification_content = NotificationTemplate::where('id', 9)->first();
            }
            if ($notification_content) {
                $code = $header_code;
                $client = Client::where('code', $code)->first();
                $redirect_URL = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/user/orders";

                $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "default",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => $redirect_URL,
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

    public function minimize_orderDetails_for_notification($order_id, $vendor_id = "")
    {
        $order = Order::with(['vendors.vendor:id,name,auto_accept_order'])->select('id', 'order_number', 'payable_amount', 'payment_option_id', 'user_id', 'address_id', 'loyalty_amount_saved', 'total_discount', 'total_delivery_fee', 'total_amount', 'taxable_amount', 'created_at');
        $order = $order->whereHas('vendors', function ($query) use ($vendor_id) {
            if (!empty($vendor_id)) {
                $query->where('vendor_id', $vendor_id);
            }
        })->with('vendors', function ($query) use ($vendor_id) {
            $query->select('id', 'order_id', 'vendor_id');
            if (!empty($vendor_id)) {
                $query->where('vendor_id', $vendor_id);
            }
        });
        $order = $order->find($order_id);
        return $order;
    }

    public function orderDetails_for_notification($order_id, $vendor_id = "")
    {

        $user = Auth::user();
        if ($user->is_superadmin != 1) {
            $userVendorPermissions = UserVendor::where(['user_id' => $user->id])->pluck('vendor_id')->toArray();
            $vendor_id = OrderVendor::where(['order_id' => $order_id])->whereIn('vendor_id', $userVendorPermissions)->pluck('vendor_id')->first();
            if (!$vendor_id) {
                return response()->json(['error' => __('No order found')], 404);
            }
        }
        $language_id = (!empty($user->language)) ? $user->language : 1;
        $order = Order::with([
            'vendors.products:id,product_name,product_id,order_id,order_vendor_id,variant_id,quantity,price,image',
            'vendors.vendor:id,name,auto_accept_order,logo',
            'vendors.products.addon:id,order_product_id,addon_id,option_id',
            'vendors.products.pvariant:id,sku,product_id,title,quantity',
            'user:id,name,timezone,dial_code,phone_number',
            'address:id,user_id,address',
            'vendors.products.addon.option:addon_options.id,addon_options.title,addon_id,price',
            'vendors.products.addon.set:addon_sets.id,addon_sets.title',
            'luxury_option',
            'vendors.products.translation' => function ($q) use ($language_id) {
                $q->select('id', 'product_id', 'title');
                $q->where('language_id', $language_id);
            },
            'vendors.products.addon.option.translation_one' => function ($q) use ($language_id) {
                $q->select('id', 'addon_opt_id', 'title');
                $q->where('language_id', $language_id);
            },
            'vendors.products.addon.set.translation_one' => function ($q) use ($language_id) {
                $q->select('id', 'addon_id', 'title');
                $q->where('language_id', $language_id);
            }
        ])->select('id', 'order_number', 'payable_amount', 'payment_option_id', 'user_id', 'address_id', 'loyalty_amount_saved', 'total_discount', 'total_delivery_fee', 'total_amount', 'taxable_amount', 'wallet_amount_used', 'scheduled_date_time', 'payment_method', 'payment_status', 'luxury_option_id', 'created_at');
        $order = $order->whereHas('vendors', function ($query) use ($vendor_id) {
            if (!empty($vendor_id)) {
                $query->where('vendor_id', $vendor_id);
            }
        })->with('vendors', function ($query) use ($vendor_id) {
            $query->select('id', 'order_id', 'vendor_id');
            if (!empty($vendor_id)) {
                $query->where('vendor_id', $vendor_id);
            }
        });
        $order = $order->find($order_id);
        $order->admin_profile = Client::select('company_name', 'code', 'sub_domain', 'logo')->first();
        $order_item_count = 0;
        $order->payment_option_title = $order->paymentOption->title;
        $order->item_count = $order_item_count;
        //$order->created_at = dateTimeInUserTimeZone($order->created_at, $user->timezone);
        // $order->date_time = dateTimeInUserTimeZone($order->created_at, $user->timezone);
        $order->created = dateTimeInUserTimeZone($order->created_at, $user->timezone);
        $order->scheduled_date_time = !empty($order->scheduled_date_time) ? dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone) : null;
        $luxury_option_name = '';
        if ($order->luxury_option_id > 0) {
            $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
            if ($luxury_option->title == 'takeaway') {
                $luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
            } elseif ($luxury_option->title == 'dine_in') {
                $luxury_option_name = $this->getNomenclatureName('Dine-In', $user->language, false);
            } elseif ($luxury_option->title == 'on_demand') {
                $luxury_option_name = $this->getNomenclatureName('Services', $user->language, false);
            } else {
                //$luxury_option_name = 'Delivery';
                $luxury_option_name = getNomenclatureName($luxury_option->title);
            }
        }
        $order->luxury_option_name = $luxury_option_name;
        foreach ($order->products as $product) {
            $order_item_count += $product->quantity;
        }
        $order->item_count = $order_item_count;
        unset($order->products);
        unset($order->paymentOption);
        return $this->successResponse($order, __('Order detail.'), 201);
    }


    /**
     * Credit Money Into order tip
     *
     * @return \Illuminate\Http\Response
     */
    public function tipAfterOrder(Request $request)
    {

        $user = Auth::user();
        if ($user) {
            $order_number = $request->order_number;
            if ($order_number > 0) {
                $order = Order::select('id', 'tip_amount')->where('order_number', $order_number)->first();
                if (($order->tip_amount == 0) || empty($order->tip_amount)) {
                    $tip = Order::where('order_number', $order_number)->update(['tip_amount' => $request->tip_amount]);
                    Payment::insert([
                        'date' => date('Y-m-d'),
                        'order_id' => $order->id,
                        'transaction_id' => $request->transaction_id,
                        'balance_transaction' => $request->tip_amount,
                        'type' => 'tip'
                    ]);
                }
                $message = 'Tip has been submitted successfully';
                $response['tip_amount'] = $request->tip_amount;
                return $this->successResponse($response, $message, 200);
            } else {
                return $this->errorResponse('Amount is not sufficient', 400);
            }
        } else {
            return $this->errorResponse('Invalid User', 400);
        }
    }

    /**
     * get tracking order detail
     *
     * @return \Illuminate\Http\Response
     */
    public function OrderTracking(Request $request)
    {
        try {

            $order      = Order::with('ordervendor', 'orderStatusVendor', 'address', 'orderLocation')->where('order_number', $request->order_number)->first();


            if (empty($order)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Order Not Found',
                    'data' => null
                ], 400);
            }
            $customer   = User::find($order->user_id);
            if (isset($order->ordervendor->dispatch_traking_url) && !empty($order->ordervendor->dispatch_traking_url)) {
                try {
                    $response = Http::get($order->ordervendor->dispatch_traking_url);
                } catch (\Exception $ex) {
                }

                if (isset($response) && $response->status() == 200) {
                    $response               = $response->json();
                    $order['order_data']    = $response;
                }

                $order['dispatch_traking_url'] = str_replace("/order/", "/order-details/", $order->ordervendor->dispatch_traking_url);
                $response = Http::get($order['dispatch_traking_url']);
                $tasks = array();
                $agent_location = '';
                if ($response->status() == 200) {
                    $response = $response->json();
                    $order['dispatch_order'] = $response;
                    $tasks = $response['tasks'];
                    $agent_location = $response['agent_location'];
                    $order['agent_location']  = $agent_location;
                }
            }

            $order['user']  = $customer;
            return $this->successResponse($order, null, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function editOrderByUser(Request $request)
    {
        try {
            $orderid = $request->orderid;
            $response = $this->editOrderInCart($orderid);
            return $response;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }

    public function discardEditOrderByUser(Request $request)
    {
        try {
            $orderid = $request->orderid;
            $response = $this->discardEditOrder($orderid);
            return $response;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }


    public function sendVendorReachedLocation($domain = '', Request $request)
    {
        try {
            $user_vendors = UserVendor::where(['vendor_id' => $request->vendor_id])->pluck('user_id');
            $orderData = $this->minimize_orderDetails_for_notification($request->order_id, $request->vendor_id);
            $header_code = $request->code ?? '';

            // if(@$request->vehicle_name && @$request->number_plate){
            //     $user_id = $request->user_id ?? Auth::user()->id;
            //     $user_vehicle = UserVehicle::updateOrCreate([
            //         'plate' =>  $request->number_plate,
            //         'user_id' =>  $user_id
            //     ], [
            //         'name' => $request->vehicle_name,
            //         'description' =>  $request->description ?? null
            //     ]);
            //     $comment = $request->vehicle_name." - ". $request->number_plate ;
            //     if(@$request->description){
            //         $comment .=  " - ".$request->description;
            //     }
            //     Order::where('id', $request->order_id)->update(['comment_for_vendor' => $comment]);
            // }

            $send = $this->sendOrderPushNotificationVendorReached($user_vendors, $orderData, $header_code);

            return response()->json(['status' => 1, 'message' => 'Notification sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'error' => $e->getMessage()]);
        }
    }

    public function sendOrderPushNotificationVendorReached($user_ids, $orderData, $header_code = '')
    {

        $devices = UserDevice::where('is_vendor_app', 0)->whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();

        //$vendorAppDevices = UserDevice::where('is_vendor_app', 1)->whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();

        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            $notification_content = NotificationTemplate::where('slug', 'reached-vendor-location')->first();
            $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
            if ($notification_content) {
                if ($header_code == '') {
                    $header_code = Client::orderBy('id', 'asc')->first()->code;
                }
                $code = $header_code;
                $client = Client::where('code', $code)->first();
                $redirect_URL = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/client/order";

                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $notification_content->content,
                        'data' => $orderData,
                        'order_id' => $orderData->id,
                        'type' => "reached_location"
                    ],
                    "priority" => "high"
                ];
                return sendFcmCurlRequest($data);
            }
        }

        // Individual Vendor App User Token
        $vendorAppUserDevices = UserDevice::where('is_vendor_app', 1)->whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();
        if (!empty($vendorAppUserDevices) && !empty($client_preferences->vendor_fcm_server_key)) {
            $from = $client_preferences->vendor_fcm_server_key;
            $data['registration_ids'] = $vendorAppUserDevices;
            return sendFcmCurlRequest($data, $from, 1);
        }
    }

    public function orderVenderStatusUpdate(Request $request)
    {
        $code = $request->header('code') ?? '';
        try {
            $response = OrderVendor::where('id', $request->order_vendor_id)->update(['order_status_option_id' => $request->order_status_option_id]);
            $response = OrderVendor::with('vendor.userVendor', 'user')->where('id', $request->order_vendor_id)->first();
            $order_data = $response->orderDetail;
            $order_user = $response->user;
            $userVendor = $response->vendor->userVendor;
            $credit_amount = $response->payable_amount;

            $user = User::find($userVendor->user_id);
            $wallet = $user->wallet;
            if ($order_data->payment_option_id == 1 && $request->order_status_option_id == 4) {
                $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for order number <b>' . $order_data->order_number . '</b>']);

                $order_data->payment_status = 1;
                $order_data->save();
            }
            if ($order_data->payment_intent_id && $request->order_status_option_id == 4) {
                $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for order number <b>' . $order_data->order_number . '</b>']);

                $secret_key = stripePaymentCredentials()->secret_key;
                \Stripe\Stripe::setApiKey($secret_key);
                $paymentIntent = \Stripe\PaymentIntent::retrieve($order_data->payment_intent_id);
                $paymentIntent->capture();
            }

            if ($request->order_status_option_id == 6) {
                $order = Order::select('id', 'loyalty_points_earned_order')->find($response->order_id);

                if ($order) {
                    $data = ["loyalty_points_earned" => $order->loyalty_points_earned_order];
                    // Add this line to check the $data variable
                    Order::where('id', $order->id)->update($data);
                }
            }

            if ($request->order_status_option_id == 6 || $request->order_status_option_id == 4) {
                $this->sendOrderStatusChangeNotification($request, $order_data);
            }

            $vendor_id = [$response->vendor_id];
            $user_id = OrderVendor::where(['id' => $request->order_vendor_id])->pluck('user_id');
            VendorOrderStatus::updateOrCreate(['order_id' => $request->order_id, 'order_vendor_id' => $request->order_vendor_id, 'order_status_option_id' => $request->order_status_option_id, 'vendor_id' => $response->vendor_id]);
            // $this->sendOrderStatusChangePushNotificationCustomer($user_id,$order_data,$request->order_status_option_id,$code);
            // $this->sendOrderStatusChangePushNotificationCustomer($vendor_id,$order_data,$request->order_status_option_id,$code,1);

            if ($request->order_status_option_id == 6) { /// if completed rental
                $orderVendorProduct = OrderVendorProduct::where('order_id', $response->order_id)->first();
                if ($orderVendorProduct) {

                    $carbonDate1 = \Carbon\Carbon::parse(date('Y-m-d'));
                    $carbonDate2 = \Carbon\Carbon::parse(date('Y-m-d', strtotime($orderVendorProduct->end_date_time)));

                    if ($carbonDate1->lessThan($carbonDate2)) {
                        $datesInRange = [];
                        while ($carbonDate1->lessThanOrEqualTo($carbonDate2)) {
                            $datesInRange[] = $carbonDate1->toDateString();
                            $carbonDate1->addDay();
                        }

                        if ($datesInRange) {
                            $datesInRange = array_reverse($datesInRange);
                            ProductAvailability::where('product_id', $orderVendorProduct->product_id)->whereIn(\DB::raw('DATE(date_time)'), $datesInRange)->update(['not_available' => 0]);
                        }
                    }
                }
            }
            return $response;
        } catch (\Exception $e) {

            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }


    public function sendOrderStatusChangeNotification($request, $order_data)
    {
        // Fetch user devices with non-null device tokens
        $devices = UserDevice::whereNotNull('device_token')
            ->where('user_id', $order_data->user_id)
            ->pluck('device_token')
            ->toArray();

        // Check if there are no devices, return true (or handle accordingly)
        if (empty($devices)) {
            return true;
        }
        // Get client preferences
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();
        // Set the 'from' value based on client preferences
        $from = (!empty($client_preferences->fcm_server_key)) ? $client_preferences->fcm_server_key : '';
        // Fetch notification content for the given ID (23)
        $notification_content = NotificationTemplate::where('id', 23)->first();
        // Set the title for the notification, defaulting to "Order Status Changed"
        $title = $notification_content ? $notification_content->subject : "Order Status Changed";
        // Determine the status based on the 'order_status_option_id'
        $status = ($request->order_status_option_id == 4) ? "Pickup Complete" : "DropOff Complete";
        // Replace placeholders in the notification content
        $body_content = str_ireplace(["{order_number}", "{status}"], ["#" . $order_data->order_number, $status], $notification_content->content);
        // Call the function to send the notification
        $data = [
            "registration_ids" => $devices,
            "notification" => [
                'title' => $title,
                'body'  => $body_content,
                'sound' => "notification.wav",
                "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                "android_channel_id" => "sound-channel-id"
            ],
            "data" => [
                'title' => $title,
                'body'  => $body_content,
                'type' => "order_status"
            ],
            "priority" => "high"
        ];
        if (!empty($from)) {
            // helper function
            sendFcmCurlRequest($data);
        }
    }

    public function getOrdersListLenderBorrower(Request $request)
    {
        $user = Auth::user();
        $order_status_options = [];
        $paginate = $request->has('limit') ? $request->limit : 12;
        $type = $request->has('type') ? $request->type : 'all';

        $user_type = $request->has('user_type') ? $request->user_type : 'borrower';
        $product_type = $request->has('productType') ? $request->productType : '';
        $orders = OrderVendor::with('products')->orderBy('id', 'DESC');
        $additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch']);
        $vendorUser =  UserVendor::select('vendor_id')->where('user_id', $user->id)->first();
        $orders->where('user_id', $user->id);
        if (!empty($vendorUser)) {
            if ($user_type == 'borrower') {
                $orders->where('user_id', $user->id);
            } elseif ($user_type == 'lender') {
                $orders->where('vendor_id',  $vendorUser->vendor_id);
            } else {
                $orders->where(function ($q) use ($vendorUser, $user) {
                    $q->where('vendor_id',  $vendorUser->vendor_id)->orWhere('user_id', $user->id);
                });
            }
        }

        switch ($type) {
            case 'all': // which order not assign yet indriver

                $orders->whereHas('products');
                break;
            case 'upcoming': // which order not assign yet indriver

                $orders->whereHas('products');
                $orders->whereIn('order_status_option_id', [1, 2]);
                break;
            case 'ongoing': // which order not assign yet indriver

                $orders->whereHas('products');
                $orders->whereIn('order_status_option_id', [4]);
                break;
            case 'pending': // which order not assign yet indriver

                $orders->whereHas('products', function ($q1) {
                    $q1->where('dispatcher_status_option_id', 1);
                });
                break;
            case 'active':
                $orders->whereNotIn('order_status_option_id', [6, 9]);
                $orders->whereHas('products', function ($q) use ($additionalPreference) {
                    if ($additionalPreference['is_service_product_price_from_dispatch'] == 1) {
                        $q->whereNotIn('dispatcher_status_option_id', [1, 5, 6]); //1=pending,5= complete,6 reject
                    }
                });
                break;
            case 'past':
                $orders->whereIn('order_status_option_id', [6]);
                if ($additionalPreference['is_service_product_price_from_dispatch'] == 1) {
                    $orders->whereHas('products', function ($q) {
                        $q->where('dispatcher_status_option_id', 5); //1=pending,5= complete,6 reject
                    });
                }
                break;
            case 'cancel':
                $orders->where('order_status_option_id', 3);
                break;
            case 'schedule':
                $order_status_options = [10];
                $orders->whereHas('status', function ($query) use ($order_status_options) {
                    $query->whereIn('order_status_option_id', $order_status_options);
                });
                break;
        }

        $orders = $orders->with([
            'orderDetail.editingInCart',
            'vendor:id,name,logo,banner,return_request,cancel_order_in_processing',
            'user' => function ($qq) {
                $qq->select('id', 'name');
            },
            'products.productReturn',
            'cancelledBy.userVendor',
            'exchanged_of_order.orderDetail',
            'exchanged_to_order.orderDetail',
            'cancel_request',
            'products.Routes',
            'products.order_product_status',
            'products.product.category.categoryDetail' => function ($q) {
                $q->select('id', 'type_id');
            },
            'products.product.translation'
        ])
            ->whereHas('products.product.category.categoryDetail', function ($qq) use ($product_type) {
                if ($product_type == "rent") {
                    $qq->where('type_id', 10);
                } else {
                    $qq->where('type_id', 13);
                }
            })
            // ->whereHas('orderDetail', function ($q1) {
            // $q1->where('orders.payment_status', 1)->whereNotIn('orders.payment_option_id', [1,38]);
            // $q1->orWhere(function ($q2) {
            //     $q2->whereIn('orders.payment_option_id', [1,38])
            //     ->orWhere(function($q3) {
            //         $q3->where('orders.is_postpay', 1) //1 for order is post paid
            //             ->whereNotIn('orders.payment_option_id', [1, 38]);
            //     });

            // });
            // })
            ->paginate($paginate);


        $orders = $this->orderlistLoop($orders, $user, $request);
        return $this->successResponse($orders, '', 201);
    }

    public function getOrdersLenderBorrower(Request $request)
    {

        $user = Auth::user();
        $order_status_options = [];
        $paginate = $request->has('limit') ? $request->limit : 2;
        $type = $request->has('type') ? $request->type : 'all';
        $product_type = $request->has('productType') ? $request->productType : '';
        $user_type = $request->has('user_type') ? $request->user_type : '';
        $additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch']);

        $vendorUser =  UserVendor::select('vendor_id')->where('user_id', $user->id)->first();
        $orders = OrderVendor::with('products')->orderBy('id', 'DESC');
        $orders->where('user_id', $user->id); //borrower

        switch ($type) {

            case 'all': // which order not assign yet indriver
                $orders->whereHas('products');
                break;
            case 'upcoming': // which order not assign yet indriver

                $orders->whereHas('products');
                $orders->whereIn('order_status_option_id', [1, 2]);
                break;
            case 'ongoing': // which order not assign yet indriver

                $orders->whereHas('products');
                $orders->whereIn('order_status_option_id', [4]);
                break;
            case 'pending': // which order not assign yet indriver

                $orders->whereHas('products', function ($q1) {
                    $q1->where('dispatcher_status_option_id', 1);
                });
                break;

            case 'past':

                $orders->where('order_status_option_id', 6);
                // dd($orders->get());
                if ($additionalPreference['is_service_product_price_from_dispatch'] == 1) {
                    $orders->whereHas('products', function ($q) {
                        $q->where('dispatcher_status_option_id', 5); //1=pending,5= complete,6 reject
                    });
                }
                break;
            case 'schedule':
                $order_status_options = [10];
                $orders->whereHas('status', function ($query) use ($order_status_options) {
                    $query->whereIn('order_status_option_id', $order_status_options);
                });
                break;
        }

        $orders = $orders->with([
            'orderDetail.editingInCart',
            'vendor:id,name,logo,banner,return_request,cancel_order_in_processing',
            'user' => function ($qq) {
                $qq->select('id', 'name');
            },
            'products.productReturn',
            'exchanged_of_order.orderDetail',
            'exchanged_to_order.orderDetail',
            'cancel_request',
            'products.Routes',
            'products.order_product_status',
            'products.product.category.categoryDetail' => function ($q) {
                $q->select('id', 'type_id');
            },
            'products.product.translation'

        ])
            ->whereHas('products.product.category.categoryDetail', function ($qq) use ($product_type) {
                if ($product_type == "rent") {
                    $qq->where('type_id', 10);
                } else {
                    $qq->where('type_id', 13);
                }
            })
            ->orderBy('id', 'Desc')
            ->take($paginate)->get();


        if (@$vendorUser->vendor_id) {
            $lender = OrderVendor::with('products')->orderBy('id', 'DESC');
            $lender->where('vendor_id',  $vendorUser->vendor_id); //lender
            switch ($type) {
                case 'all': // which order not assign yet indriver
                    $lender->whereHas('products');
                    break;
                case 'upcoming': // which order not assign yet indriver

                    $lender->whereHas('products');
                    $lender->whereIn('order_status_option_id', [1, 2]);
                    break;
                case 'ongoing': // which order not assign yet indriver

                    $lender->whereHas('products');
                    $lender->whereIn('order_status_option_id', [4]);
                    break;
                case 'past':
                    $lender->where('order_status_option_id', 6);
                    // dd($orders->get());
                    if ($additionalPreference['is_service_product_price_from_dispatch'] == 1) {
                        $lender->whereHas('products', function ($q) {
                            $q->where('dispatcher_status_option_id', 5); //1=pending,5= complete,6 reject
                        });
                    }
                    break;
                case 'pending': // which order not assign yet indriver
                    $lender->whereHas('products', function ($q1) {
                        $q1->where('dispatcher_status_option_id', 1);
                    });
                    break;
                case 'schedule':
                    $order_status_options = [10];
                    $lender->whereHas('status', function ($query) use ($order_status_options) {
                        $query->whereIn('order_status_option_id', $order_status_options);
                    });
                    break;
            }
            $lender = $lender->with([
                'orderDetail.editingInCart',
                'vendor:id,name,logo,banner,return_request,cancel_order_in_processing',
                'user' => function ($qq) {
                    $qq->select('id', 'name');
                },
                'products.productReturn',
                'exchanged_of_order.orderDetail',
                'exchanged_to_order.orderDetail',
                'cancel_request',
                'products.Routes',
                'products.order_product_status',
                'products.product.category.categoryDetail' => function ($q) {
                    $q->select('id', 'type_id');
                },
                'products.product.translation'
            ])
                ->whereHas('products.product.category.categoryDetail', function ($qq) use ($product_type) {
                    if ($product_type == "rent") {
                        $qq->where('type_id', 10);
                    } else {
                        $qq->where('type_id', 13);
                    }
                })
                ->orderBy('id', 'Desc')
                ->take($paginate)->get();
            $orderdata['lender'] = $this->orderlistLoop($lender, $user, $request, 'lender');
        } else {
            $orderdata['lender'] = [];
        }

        $orderdata['borrower'] = $this->orderlistLoop($orders, $user, $request, 'borrower');

        return $this->successResponse($orderdata, '', 201);
    }

    public function postOrderDetailP2p(Request $request)
    {

        try {
            $user = Auth::user();
            $order_item_count = 0;
            $language_id = $user->language;
            $order_id = $request->order_id;
            $vendor_id = $request->vendor_id ?? '';
            $preferences = ClientPreference::first();

            if ($vendor_id) {
                $order = Order::with([
                    'driver_rating',
                    'vendors.products.Routes',
                    'reports',
                    'vendors' => function ($q) use ($vendor_id) {
                        $q->where('vendor_id', $vendor_id);
                    },
                    'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    },
                    'vendors.dineInTable.category',
                    'vendors.products' => function ($q) use ($vendor_id) {
                        $q->where('vendor_id', $vendor_id);
                    },
                    'vendors.products.translation' => function ($q) use ($language_id) {
                        $q->select('id', 'product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                        $q->where('language_id', $language_id);
                    },
                    'vendors.products.pvariant.vset.optionData.trans',
                    'vendors.products.addon',
                    'vendors.coupon',
                    'address',
                    'vendors.products.productRating',
                    'vendors.allStatus',
                    'vendors.tempCart' => function ($q) {
                        $q->where('is_submitted', 1)->where('is_approved', 0);
                    },
                    'vendors.tempCart.cartProducts.product.media.image',
                    'vendors.tempCart.cartProducts.pvariant.media.pimage.image',
                    'vendors.tempCart.cartProducts.product.translation' => function ($q) use ($language_id) {
                        $q->where('language_id', $language_id)->groupBy('product_id');
                    },
                    'vendors.tempCart.cartProducts.addon.set' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    },
                    'vendors.tempCart.cartProducts.addon.option' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    }
                ]);

                $order = $order->with(['OrderFiles']);

                $order = $order->where(function ($q1) {
                    $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
                    $q1->orWhere(function ($q2) {
                        $q2->whereIn('payment_option_id', [1, 38]);
                    });
                })
                    ->where('id', $order_id)->select('*', 'id as total_discount_calculate')->first();
            } else {
                $order = Order::with(
                    [
                        'driver_rating',
                        'reports',
                        'vendors.vendor',
                        'vendors.products.Routes',
                        'vendors.products.product',
                        'vendors.products.product.category.categoryDetail',
                        'vendors.products.translation' => function ($q) use ($language_id) {
                            $q->select('id', 'product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                            $q->where('language_id', $language_id);
                        },
                        'vendors.products.pvariant.vset.optionData.trans',
                        'vendors.products.addon',
                        'vendors.coupon',
                        'address',
                        'vendors.products.productRating',
                        'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        },
                        'vendors.dineInTable.category',
                        'vendors.tempCart' => function ($q) {
                            $q->where('is_submitted', 1)->where('is_approved', 0);
                        },
                        'vendors.tempCart.cartProducts.product.media.image',
                        'vendors.tempCart.cartProducts.pvariant.media.pimage.image',
                        'vendors.tempCart.cartProducts.product.translation' => function ($q) use ($language_id) {
                            $q->where('language_id', $language_id)->groupBy('product_id');
                        },
                        'vendors.tempCart.cartProducts.addon.set' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        },
                        'vendors.tempCart.cartProducts.addon.option' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        }
                    ]
                );

                $order = $order->with(['OrderFiles']);

                $order = $order->where(function ($q1) {
                    $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
                    $q1->orWhere(function ($q2) {
                        $q2->whereIn('payment_option_id', [1, 38]);
                    });
                });

                if (!$user->is_admin) {
                    $order = $order->where(function ($q1) use ($user) {
                        $q1->where('user_id', $user->id);
                        $q1->orWhere(function ($q2) use ($user) {
                            $q2->whereHas('orderVendorProduct.product', function ($q) use ($user) {
                                $q->where('vendor_id', @$user->userVendor->vendor_id);
                            });
                        });
                    });
                }
                $order = $order->where('id', $order_id)->select('*', 'id as total_discount_calculate')
                    ->first();
            }

            $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
            if ($order) {
                // set payment option dynamic name
                if ($order->paymentOption->code == 'stripe') {
                    $order->paymentOption->title = __('Credit/Debit Card (Stripe)');
                } elseif ($order->paymentOption->code == 'kongapay') {
                    $order->paymentOption->title = 'Pay Now';
                } elseif ($order->paymentOption->code == 'mvodafone') {
                    $order->paymentOption->title = 'Vodafone M-PAiSA';
                } elseif ($order->paymentOption->code == 'mobbex') {
                    $order->paymentOption->title = __('Mobbex');
                } elseif ($order->paymentOption->code == 'offline_manual') {
                    $json = json_decode($order->paymentOption->credentials);
                    $order->paymentOption->title = $json->manule_payment_title;
                }
                $order->paymentOption->title = __($order->paymentOption->title);

                $order->user_name = $order->user->name;
                $order->user_image = $order->user->image;
                $order->payment_option_title = __($order->paymentOption->title);
                $order->created_date = dateTimeInUserTimeZone($order->created_at, $user->timezone);
                $order->tip_amount = $order->tip_amount;
                $order->tip = array(
                    ['label' => '5%', 'value' => decimal_format(0.05 * ($order->payable_amount - $order->total_discount_calculate))],
                    ['label' => '10%', 'value' => decimal_format(0.1 * ($order->payable_amount - $order->total_discount_calculate))],
                    ['label' => '15%', 'value' => decimal_format(0.15 * ($order->payable_amount - $order->total_discount_calculate))]
                );
                $total_markup_Price = 0;
                $slot_based_Price = 0;
                foreach ($order->vendors as $vendor) {
                    $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order_id)->where('vendor_id', $vendor->vendor->id)->orderBy('id', 'DESC')->first();
                    if ($vendor_order_status) {
                        $vendor->order_status =  ['current_status' => ['id' => $vendor_order_status->OrderStatusOption->id, 'title' => __($vendor_order_status->OrderStatusOption->title)]];
                    } else {
                        $vendor->current_status = null;
                    }




                    $couponData = [];
                    $payable_amount = 0;
                    $total_container_charges = 0;
                    $discount_amount = 0;
                    $opt_quantity_price = 0;
                    $product_addons = [];
                    $vendor->vendor_name = $vendor->vendor->name;
                    foreach ($vendor->products as  $product) {

                        $start_date_time  = new Carbon($product->start_date_time);
                        $end_date_time  = new Carbon($product->end_date_time);
                        $product->days = $start_date_time->diff($end_date_time)->days + 1;


                        $rental_price = $product->pvariant ? $product->pvariant->price : 0;
                        if (@$product->pvariant->month_price && @$product->pvariant->week_price) {

                            if ($product->days >= 7 && $product->days < 30) {
                                $rental_price = $product->pvariant->week_price;
                            } elseif ($product->days >= 30) {
                                $rental_price = $product->pvariant->month_price;
                            }
                        }
                        $product->rental_price = $rental_price;
                        $order['payable_amount'] = $product->price;

                        $product->longTermSchedule = array();
                        $product->recurring_date_count = 1;
                        if ($product->product->is_long_term_service == 1) {
                            $product->longTermSchedule =  OrderLongTermServices::with(['schedule', 'product.primary', 'addon.set', 'addon.option', 'addon.option.translation' => function ($q) use ($language_id) {
                                $q->select('addon_option_translations.id', 'addon_option_translations.addon_opt_id', 'addon_option_translations.title', 'addon_option_translations.language_id');
                                $q->where('addon_option_translations.language_id', $language_id);
                                $q->groupBy('addon_option_translations.addon_opt_id', 'addon_option_translations.language_id');
                            }])->where('order_product_id', $product->id)->first();
                            foreach ($product->longTermSchedule->addon as $ck => $addons) {
                                $opt_price_in_currency = $addons->option->price ?? 0;
                                $opt_price_in_doller_compare = $addons->option->price ?? 0;
                                if ($clientCurrency) {
                                    $opt_price_in_currency = $addons->option->price ?? 0 / $divider;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                }
                                $opt_quantity_price = decimal_format($opt_price_in_doller_compare * $product->quantity);
                                $addons->option->translation_title = ($addons->option->translation->isNotEmpty()) ? $addons->option->translation->first()->title : '';
                                $addons->option->price_in_cart = $addons->option->price;
                                $addons->option->price = decimal_format($opt_price_in_currency);
                                $addons->option->multiplier = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
                            }
                        }

                        //Mohit sir branch code by sohail
                        if ($order->luxury_option_id == 3) {
                            $processorProduct = ProcessorProduct::where('product_id', $product->product_id)->first();
                            $product->is_processor_enable = (isset($processorProduct->is_processor_enable) && $processorProduct->is_processor_enable == 1) ? true : false;
                            $product->processor_name = !empty($processorProduct->name) ? $processorProduct->name : '';
                            $product->processor_date = !empty($processorProduct->date) ? $processorProduct->date : '';
                            $product->address = !empty($processorProduct->address) ? $processorProduct->address : '';
                        } else {
                            $product->is_processor_enable = false;
                            $product->processor_name = '';
                            $product->processor_date = '';
                            $product->address = '';
                        }
                        //till here

                        $product_addons = [];
                        $variant_options = [];
                        $vendor_total_container_charges = 0;
                        $order_item_count += $product->quantity;
                        $product->image_path = $product->media->first() ? $product->media->first()->image->path : $product->image;
                        if ($product->pvariant) {
                            foreach ($product->pvariant->vset as $variant_set_option) {
                                $variant_options[] = array(
                                    'option' => $variant_set_option->optionData->trans->title,
                                    'title' => $variant_set_option->variantDetail->trans->title,
                                );
                            }
                        }
                        if ($product->user_product_order_form)
                            $product->user_product_order_form = json_decode($product->user_product_order_form);

                        $product->variant_options = $variant_options;
                        if (!empty($product->addon)) {
                            foreach ($product->addon as $k => $addon) {
                                // $product_addons[] = array(
                                //     'addon_id' =>  $addon->addon_id,
                                //     'addon_title' =>  $addon->set->title,
                                //     'option_title' =>  $addon->option->title,
                                // );
                                $opt_quantity_price = 0;
                                $opt_price_in_currency = $addon->option ? $addon->option->price : 0;
                                $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                $opt_quantity_price = $opt_price_in_doller_compare * $product->quantity;
                                $product_addons[$k]['quantity'] = $product->quantity;
                                $product_addons[$k]['addon_id'] = $addon->addon_id;
                                $product_addons[$k]['option_id'] = $addon->option_id;
                                $product_addons[$k]['price'] = $opt_price_in_currency;
                                $product_addons[$k]['addon_title'] = $addon->set->title;
                                $product_addons[$k]['quantity_price'] = $opt_quantity_price;
                                $product_addons[$k]['option_title'] = $addon->option ? $addon->option->title : 0;
                                // $product_addons[$k]['multiplier'] = $clientCurrency->doller_compare;
                            }
                        }

                        $product->product_addons = $product_addons;
                        if (auth()->user()->is_admin) {
                            $product->price = $product->price - $product->markup_price;
                        } else {
                            $product->price = $product->price;
                        }

                        $total_markup_Price += $product->markup_price;
                        if ($product->slot_id != '' && $product->delivery_date != '' && $product->slot_price != '') {
                            $slot_based_Price += $product->slot_price;
                        }
                    }
                    if ($vendor->delivery_fee > 0) {
                        $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                        $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                        $ETA = $order_pre_time + $user_to_vendor_time;
                        $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                    }
                    if ($vendor->dineInTable) {
                        $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                        $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                        $vendor->dineInTableCategory = $vendor->dineInTable->category->title; //$vendor->dineInTable->category->first() ? $vendor->dineInTable->category->first()->title : '';
                    }

                    $vendorId = $vendor->vendor->id;
                    //type must be a : delivery , takeaway,dine_in
                    $duration = Vendor::where('id', $vendorId)->select('slot_minutes', 'closed_store_order_scheduled')->first();
                    $slotsDate = findSlot('', $vendorId, '', 'api');
                    $slots = showSlot($slotsDate, $vendorId, 'delivery', $duration->slot_minutes, 1);
                    $vendor->slots = $slots;
                    if ($preferences->business_type == 'laundry') {
                        $dropoff_slots = showSlot($slotsDate, $vendorId, 'delivery', $duration->slot_minutes, 2);
                        $vendor->dropoff_slots = $dropoff_slots;
                    } else {
                        $vendor->dropoff_slots = [];
                    }
                    if (count($slots) > 0) {
                        $vendor->closed_store_order_scheduled = $duration->closed_store_order_scheduled ?? 0;
                    } else {
                        $vendor->closed_store_order_scheduled = 0;
                    }
                    $slotsDate = findSlot('', $vendorId, '', 'api');
                    $vendor->delaySlot = $slotsDate;
                    $vendor->same_day_orders_for_rescheduling = $preferences->same_day_orders_for_rescheduing ?? 0;

                    // dispatch status
                    $vendor->vendor_dispatcher_status = VendorOrderDispatcherStatus::whereNotIn('dispatcher_status_option_id', [2])
                        ->select('*', 'dispatcher_status_option_id as status_data')->where('order_id', $order_id)
                        ->where('vendor_id', $vendor->vendor->id)
                        ->get();
                    $vendor->vendor_dispatcher_status_count = 6;
                    $vendor->dispatcher_status_icons = [asset('assets/icons/driver_1_1.png'), asset('assets/icons/driver_2_1.png'), asset('assets/icons/driver_3_1.png'), asset('assets/icons/driver_4_1.png'), asset('assets/icons/driver_4_2.png'), asset('assets/icons/driver_5_1.png')];

                    // Start temp cart calculations
                    if ($vendor->tempCart) {
                        $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
                        $currId = ClientCurrency::where(['is_primary' => 1])->value('currency_id');
                        $tempCartController = new TempCartController();
                        $vendor->tempCart = $tempCartController->getCartForApproval($vendor->tempCart, $order, $langId, $currId, '');
                    } else {
                        $vendor->tempCart = null;
                    }
                }
                if (!empty($order->scheduled_date_time)) {
                    $order->scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
                }
                $luxury_option_name = '';
                $order->luxury_option = [];
                if ($order->luxury_option_id > 0) {
                    $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
                    $order->luxury_option = $luxury_option;
                    if ($luxury_option->title == 'takeaway') {
                        $luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
                    } elseif ($luxury_option->title == 'dine_in') {
                        $luxury_option_name = $this->getNomenclatureName('Dine-In', $user->language, false);
                    } elseif ($luxury_option->title == 'on_demand') {
                        $luxury_option_name = $this->getNomenclatureName('Services', $user->language, false);
                    } else {
                        //$luxury_option_name = 'Delivery';
                        $luxury_option_name = getNomenclatureName($luxury_option->title);
                    }
                }
                $order->luxury_option_name = $luxury_option_name;
                $order->order_item_count = $order_item_count;
                if (auth()->user()->is_admin) {
                    $order['total_amount'] = $order->total_amount  - $total_markup_Price;
                    $order['payable_amount'] = $order->payable_amount  - $total_markup_Price;
                } else {
                    $order['total_amount'] = $order->total_amount;
                    $order['payable_amount'] = decimal_format($order->payable_amount);
                }
                $user_id = $order->user_id ?? '';

                //$user_docs = UserDocs::where('user_id', $order->user_id)->get();
                $user_registration_documents = UserRegistrationDocuments::with('user_document', 'primary')
                    ->whereHas('user_document', function ($q) use ($user_id) {
                        $q->where('user_id', $user_id);
                    })->get();

                $total_other_taxes = 0;
                if ($order->total_other_taxes != '') {
                    foreach (explode(",", $order->total_other_taxes) as $row) {
                        $row1 = explode(":", $row);
                        $total_other_taxes += (float)$row1[1];
                    }
                }

                // $order['user_document_value'] =  $user_docs;
                $order->taxable_amount =  decimal_format($total_other_taxes ?? 0);
                $order->total_other_taxes =  decimal_format($total_other_taxes ?? 0);
                $order['user_document_list'] =  $user_registration_documents;
                $order['category_KYC_document'] = $category_KYC_document ?? null;

                $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)->where('vendor_id', $order->ordervendor->vendor_id)->orderBy('id', 'DESC')->first();
                if ($vendor_order_status) {
                    $order_sts = OrderStatusOption::where('id', $order->ordervendor->order_status_option_id)->first();
                    if (@$order->ordervendor->exchanged_to_order->order_status_option_id && $order->ordervendor->exchanged_to_order->order_status_option_id == 6) {
                        $order['order_status'] =  ['current_status' => ['id' => 6, 'title' => __("Replaced")]];
                    } else {
                        $order['order_status'] =  ['current_status' => ['id' => @$order_sts->id ?? '', 'title' => __(@$order_sts->title)]];
                    }
                } else {
                    $order->current_status = null;
                }
                $order->slot_based_Price =  $slot_based_Price ?? 0;
            }
            // 12345
            if (isset($request->new_dispatch_traking_url) && !empty($request->new_dispatch_traking_url)) {
                try {
                    $new_dispatch_traking_url = str_replace('/order/', '/order-details/', $request->new_dispatch_traking_url);

                    $response = Http::get($new_dispatch_traking_url);
                } catch (\Exception $ex) {
                }


                if (isset($response) && $response->status() == 200) {
                    $response = $response->json();

                    $order['order_data'] = $response;
                }
            }


            if (isset($order)) {
                $category_KYC_document =  CaregoryKycDoc::where('ordre_id', $order->id)->with('category_document.primary')->groupBy('category_kyc_document_id')->get();
            }
            // $category_KYC_document = CategoryKycDocuments::with('category_doc','primary')
            // ->whereHas('category_doc', function($q) use($order){
            //     $q->where('ordre_id',$order->id);
            // })->get();

            // $order['user_document_value'] =  $user_docs;


            //   // Log::info('order'.json_encode($order));

            //mohit sir branch code added by sohail
            // $advancePayableAmount = 0;
            // $pendingAmount = 0;
            // $getAdditionalPreference = getAdditionalPreference(['advance_booking_amount', 'advance_booking_amount_percentage']);
            // if(!empty($order->advance_amount) && !empty($getAdditionalPreference['advance_booking_amount']) && !empty($getAdditionalPreference['advance_booking_amount_percentage']) && ($getAdditionalPreference['advance_booking_amount_percentage'] > 0) && ($getAdditionalPreference['advance_booking_amount_percentage'] < 101) )
            // {
            //     $advancePayableAmount = $order->advance_amount;
            //     $pendingAmount = $order['payable_amount'] - $order->advance_amount;
            // }
            // $order['advance_paid_amount'] = number_format((float)$advancePayableAmount, 2, '.', '');
            // $order['pending_amount'] = number_format((float)$pendingAmount, 2, '.', '');
            //till here

            /* Check if other taxes available like: Tax on service fee, container charges, delivery fee and fixed fee .etc */


            return $this->successResponse($order, null, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function inappNotificationList(Request $request)
    {
        try {
            if (!Auth::check()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Please login to continue',
            ], 401); // Return a proper 401 response
        }
            $userId = Auth::id();

            $perPage = 10;

            if ($request->limit) {
                $perPage = $request->limit;
            }
            $notifications = InappNotification::where('user_id', $userId)->orderBy('id', 'desc')->paginate($perPage);
            $totalNotifications = $notifications->total();
            return response()->json([
                'status' => 'Success',
                'message' => null,
                'data' => $notifications,
                'notification_count' =>  $totalNotifications
            ], 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function inappNotificationDelete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:inapp_notifications,id',
        ]);
        try {
            $notification = InappNotification::find($request->id);
            if ($notification) {
                $notification->delete();
                return response()->json(['status' => 200, 'msg' => 'Notification deleted successfully']);
            } else {
                return response()->json(['status' => 404, 'msg' => 'Notification not found']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'msg' => $e]);
        }
    }

    public function notificationList(Request $request)
    {
        try {
            $userId = Auth::id();

            $perPage = 10;

            if ($request->limit) {
                $perPage = $request->limit;
            }
            $notifications = Notification::where('user_id', $userId)->orderBy('id', 'desc')
                ->paginate($perPage);

            return $this->successResponse($notifications, null, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function deleteNotification(Request $request)
    {
        try {
            $userId = Auth::id();

            $notifications = Notification::where('user_id', $userId);

            if ($request->id) {
                $notifications->where('id', $request->id);
            }

            $deletedCount = $notifications->delete();

            return $this->successResponse(null, 'Notification(s) Deleted Successfully.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * placeOrderRequestBorzoeApi
     *
     * @param  mixed $request
     * @return void
     */
    public function placeOrderRequestBorzoeApi($request)
    {
        $borzoe = new BorzoeDeliveryController();
        //Create Shipping place order request for Borzoe delivery
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && $checkdeliveryFeeAdded->delivery_fee > 0.00) {
            $order_ship = $this->placeOrderToBorzoApi($checkdeliveryFeeAdded, $request->vendor_id, $request->order_id);
        }
        $orderDetails = json_decode($order_ship);
        if ($order_ship) {
            OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'borzoe_order_id' => $orderDetails->order->order_id,
                    'borzoe_order_name' => $orderDetails->order->order_name,
                    'dispatch_traking_url' => $orderDetails->order->points[1]->tracking_url ?? null,
                ]);
            return 1;
        }
        return false;
    }
    public function OrderStatus(Request $request)
    {
        $is_arabic =  ($request->hasHeader('language')) ? $request->header('language') : 1;
        $is_arabic = $is_arabic == 8 ? 1 : 0;
        $order = Order::findOrFail($request->order_id);
        $product_form = json_decode($order->orderVendorProduct->user_product_order_form, true);
        $data = [];
        $imagePath = [
            'request_received' => asset('assets/icons/requ_re.png'),
            'document_reviewed' => asset('assets/icons/document_review.png'),
            'approved_request' => asset('assets/icons/approved_req.png'),
            'meeting_scheduled' => asset('assets/icons/meeting.png'),
            'document_submitted' => asset('assets/icons/doc_sub.png'),
            'session_completed' => asset('assets/icons/session_com.png'),
            'icReqNew' => asset('assets/icons/icReqNew.png'),
        ];

        // Define labels in both English and Arabic
        $labels = [
            'request_received' => $is_arabic ? 'تم استلام الطلب' : 'Request Received',
            'document_review' => $is_arabic ? 'مراجعة المستندات' : 'Document Review',
            'approved_request' => $is_arabic ? 'تمت الموافقة على الطلب' : 'Approved Request',
            'meeting_scheduled' => $is_arabic ? 'تم تحديد موعد الاجتماع' : 'Meeting Scheduled',
            'documents_submitted' => $is_arabic ? 'تم تقديم المستندات' : 'Documents Submitted',
            'session_complete' => $is_arabic ? 'اكتمل الاجتماع' : 'Session Complete'
        ];

        $statuses = [
            'Active' => $is_arabic ? 'نشط' : 'Active',
            'Pending' => $is_arabic ? 'قيد الانتظار' : 'Pending',
            'Completed' => $is_arabic ? 'مكتمل' : 'Completed',
            'Rejected' => $is_arabic ? 'مرفوض' : 'Rejected',
            'Submitted' => $is_arabic ? 'تم التقديم' : 'Submitted',
            'Ongoing' => $is_arabic ? 'جاري' : 'Ongoing',
            'Reviewed' => $is_arabic ? 'تمت المراجعة' : 'Reviewed'
        ];

        if ($order->ordervendor->order_status_option_id == 1) {
            $data[] = [
                'id' => 1,
                'label' => $labels['request_received'],
                'image' =>   $imagePath['request_received'] ?? null,
                'time' => null,
                'status' => $statuses['Active'],
                'isComplete' => false,
                'key' => 'request_received',
                'current_status' => $order->ordervendor->order_status_option_id
            ];
            $data[] = [
                'id' => 2,
                'label' => $labels['document_review'],
                'image' => $imagePath['document_reviewed'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'document_reviewed',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 3,
                'label' => $labels['approved_request'],
                'image' => $imagePath['approved_request'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'approved_request',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 4,
                'label' => $labels['meeting_scheduled'],
                'image' => $imagePath['meeting_scheduled'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'meeting_scheduled',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 5,
                'label' => $labels['documents_submitted'],
                'image' => $imagePath['document_submitted'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'document_submitted',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 6,
                'label' => $labels['session_complete'],
                'image' => $imagePath['session_completed'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'session_completed',
                'current_status' => -1
            ];
        } elseif ($order->ordervendor->order_status_option_id == 9) {
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [9])
                ->first();
            $data[] = [
                'id' => 1,
                'label' => $labels['request_received'],
                'image' =>  $imagePath['request_received'] ?? null,
                'time' => Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'request_received',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 2,
                'label' => $labels['document_review'],
                'image' => $imagePath['document_reviewed'] ?? null,
                'time' => null,
                'status' => $statuses['Active'],
                'isComplete' => false,
                'key' => 'document_reviewed',
                'current_status' => $order->ordervendor->order_status_option_id
            ];
            $data[] = [
                'id' => 3,
                'label' => $labels['approved_request'],
                'image' => $imagePath['approved_request'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'approved_request',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 4,
                'label' => $labels['meeting_scheduled'],
                'image' => $imagePath['meeting_scheduled'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'meeting_scheduled',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 5,
                'label' => $labels['documents_submitted'],
                'image' => $imagePath['document_submitted'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'document_submitted',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 6,
                'label' => $labels['session_complete'],
                'image' =>  $imagePath['session_completed'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'session_completed',
                'current_status' => -1
            ];
        } elseif ($order->ordervendor->order_status_option_id == 10) {
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [9])
                ->first();
            $data[] = [
                'id' => 1,
                'label' => $labels['request_received'],
                'image' =>  $imagePath['request_received'] ?? null,
                'time' =>  Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'request_received',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [10])
                ->first();
            $data[] = [
                'id' => 2,
                'label' => $labels['document_review'],
                'image' => $imagePath['document_reviewed'] ?? null,
                'time' =>  Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'document_reviewed',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 3,
                'label' => $labels['approved_request'],
                'image' => $imagePath['approved_request'] ?? null,
                'time' =>  null,
                'status' => $statuses['Active'],
                'isComplete' => false,
                'key' => 'approved_request',
                'current_status' => $order->ordervendor->order_status_option_id
            ];
            $data[] = [
                'id' => 4,
                'label' => $labels['meeting_scheduled'],
                'image' => $imagePath['meeting_scheduled'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'meeting_scheduled',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 5,
                'label' => $labels['documents_submitted'],
                'image' => $imagePath['document_submitted'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'document_submitted',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 6,
                'label' => $labels['session_complete'],
                'image' =>  $imagePath['session_completed'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'session_completed',
                'current_status' => -1
            ];
        } elseif ($order->ordervendor->order_status_option_id == 2) {
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [9])
                ->first();
            $data[] = [
                'id' => 1,
                'label' => $labels['request_received'],
                'image' =>  $imagePath['request_received'] ?? null,
                'time' =>  Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'request_received',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 2,
                'label' => $labels['document_review'],
                'image' => $imagePath['document_reviewed'] ?? null,
                'time' =>  Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'document_reviewed',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->where('order_status', 2)->first();
            $data[] = [
                'id' => 3,
                'label' => $labels['approved_request'],
                'image' => $imagePath['approved_request'] ?? null,
                'time' =>  Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'approved_request',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 4,
                'label' => $labels['meeting_scheduled'],
                'image' => $imagePath['meeting_scheduled'] ?? null,
                'time' => null,
                'status' => $statuses['Active'],
                'isComplete' => false,
                'key' => 'meeting_scheduled',
                'current_status' => $order->ordervendor->order_status_option_id
            ];
            $data[] = [
                'id' => 5,
                'label' => $labels['documents_submitted'],
                'image' => $imagePath['document_submitted'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'document_submitted',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 6,
                'label' => $labels['session_complete'],
                'image' =>  $imagePath['session_completed'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'session_completed',
                'current_status' => -1
            ];
        } elseif ($order->ordervendor->order_status_option_id == 3) {
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [9])
                ->first();
            $data[] = [
                'id' => 1,
                'label' => $labels['request_received'],
                'image' =>  $imagePath['request_received'] ?? null,
                'time' =>  Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'request_received',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [10])
                ->first();
            $data[] = [
                'id' => 2,
                'label' => $labels['document_review'],
                'image' => $imagePath['document_reviewed'] ?? null,
                'time' => Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'document_reviewed',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->where('order_status', 3)->first();
            $data[] = [
                'id' => 3,
                'label' => $labels['approved_request'],
                'image' => $imagePath['approved_request'] ?? null,
                'time' => Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Rejected'],
                'isComplete' => true,
                'key' => 'approved_request',
                'current_status' => $order->ordervendor->order_status_option_id
            ];
            $data[] = [
                'id' => 4,
                'label' => $labels['meeting_scheduled'],
                'image' => $imagePath['meeting_scheduled'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'meeting_scheduled',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 5,
                'label' => $labels['documents_submitted'],
                'image' => $imagePath['document_submitted'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'document_submitted',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 6,
                'label' => $labels['session_complete'],
                'image' =>  $imagePath['session_completed'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'session_completed',
                'current_status' => -1
            ];
        } elseif ($order->ordervendor->order_status_option_id == 4) {
            $rejected_lawyer_doc = 0;
            $recentLawyerDocument = LawyerDocument::where('order_id', $request->order_id)
                ->orderBy('created_at', 'desc')
                ->first();
            if (@$recentLawyerDocument->status == 2) {
                $rejected_lawyer_doc = 1;
                $rejected_lawyer_doc_reason = $recentLawyerDocument->reject_reason;
            }
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [9])
                ->first();
            $data[] = [
                'id' => 1,
                'label' => $labels['request_received'],
                'image' =>  $imagePath['request_received'] ?? null,
                'time' => Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'request_received',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [10])
                ->first();
            $data[] = [
                'id' => 2,
                'label' => $labels['document_review'],
                'image' => $imagePath['document_reviewed'] ?? null,
                'time' => Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'document_reviewed',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->where('order_status', 2)->first();
            $data[] = [
                'id' => 3,
                'label' => $labels['approved_request'],
                'image' => $imagePath['approved_request'] ?? null,
                'time' => Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'approved_request',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->where('order_status', 4)->first();
            $data[] = [
                'id' => 4,
                'label' => $labels['meeting_scheduled'],
                'image' => $imagePath['meeting_scheduled'] ?? null,
                'time' => Carbon::parse(@$order_time->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'meeting_scheduled',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 5,
                'label' => $labels['documents_submitted'],
                'image' => $imagePath['document_submitted'] ?? null,
                'time' => null,
                'status' => $statuses['Active'],
                'isComplete' => false,
                'key' => 'document_submitted',
                'current_status' => $order->ordervendor->order_status_option_id,
                'rejected_lawyer_doc_reason' => $rejected_lawyer_doc_reason ?? null
            ];
            $data[] = [
                'id' => 6,
                'label' => $labels['session_complete'],
                'image' =>  $imagePath['session_completed'] ?? null,
                'time' => null,
                'status' => $statuses['Pending'],
                'isComplete' => false,
                'key' => 'session_completed',
                'current_status' => -1
            ];
        } elseif ($order->ordervendor->order_status_option_id == 5) {
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [9])
                ->first();
            $data[] = [
                'id' => 1,
                'label' => $labels['request_received'],
                'image' =>  $imagePath['request_received'] ?? null,
                'time' => optional($order_time)->created_at ? Carbon::parse($order_time->created_at)->format('h:i A · d M, Y') : null,
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'request_received',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->whereRaw('ROUND(order_status, 2) = ?', [10])
                ->first();
            $data[] = [
                'id' => 2,
                'label' => $labels['document_review'],
                'image' => $imagePath['document_reviewed'] ?? null,
                'time' => optional($order_time)->created_at ? Carbon::parse($order_time->created_at)->format('h:i A · d M, Y') : null,
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'document_reviewed',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->where('order_status', 2)->first();
            $data[] = [
                'id' => 3,
                'label' => $labels['approved_request'],
                'image' => $imagePath['approved_request'] ?? null,
                'time' => optional($order_time)->created_at ? Carbon::parse($order_time->created_at)->format('h:i A · d M, Y') : null,
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'approved_request',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->where('order_status', 4)->first();
            $data[] = [
                'id' => 4,
                'label' => $labels['meeting_scheduled'],
                'image' => $imagePath['meeting_scheduled'] ?? null,
                'time' => optional($order_time)->created_at ? Carbon::parse($order_time->created_at)->format('h:i A · d M, Y') : null,
                'status' => $statuses['Submitted'],
                'isComplete' => true,
                'key' => 'meeting_scheduled',
                'current_status' => -1
            ];
            $order_time = OrderStatusTime::where('order_id', $request->order_id)->where('order_status', 5)->first();
            $data[] = [
                'id' => 5,
                'label' => $labels['documents_submitted'],
                'image' => $imagePath['document_submitted'] ?? null,
                'time' => optional($order_time)->created_at ? Carbon::parse($order_time->created_at)->format('h:i A · d M, Y') : null,
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'document_submitted',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 6,
                'label' => $labels['session_complete'],
                'image' => $imagePath['session_completed'] ?? null,
                'time' => null,
                'status' => $statuses['Active'],
                'isComplete' => false,
                'key' => 'session_completed',
                'current_status' => $order->ordervendor->order_status_option_id
            ];
        } elseif ($order->ordervendor->order_status_option_id == 6) {
            $session_completed = 1;
            $order_time = OrderStatusTime::where('order_id', $request->order_id);
            $data[] = [
                'id' => 1,
                'label' => $labels['request_received'],
                'image' =>  $imagePath['request_received'] ?? null,
                'time' => Carbon::parse(@$order_time->where('order_status', 9)->first()->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'request_received',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 2,
                'label' => $labels['document_review'],
                'image' => $imagePath['document_reviewed'] ?? null,
                'time' => Carbon::parse(@$order_time->where('order_status', 10)->first()->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'document_reviewed',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 3,
                'label' => $labels['approved_request'],
                'image' => $imagePath['approved_request'] ?? null,
                'time' => Carbon::parse(@$order_time->where('order_status', 2)->first()->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'approved_request',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 4,
                'label' => $labels['meeting_scheduled'],
                'image' => $imagePath['meeting_scheduled'] ?? null,
                'time' => Carbon::parse(@$order_time->where('order_status', 4)->first()->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'meeting_scheduled',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 5,
                'label' => $labels['documents_submitted'],
                'image' => $imagePath['document_submitted'] ?? null,
                'time' => Carbon::parse(@$order_time->where('order_status', 5)->first()->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'document_submitted',
                'current_status' => -1
            ];
            $data[] = [
                'id' => 6,
                'label' => $labels['session_complete'],
                'image' =>   $imagePath['session_completed'] ?? null,
                'time' => Carbon::parse(@$order_time->where('order_status', 6)->first()->created_at)->format('h:i A · d M, Y'),
                'status' => $statuses['Completed'],
                'isComplete' => true,
                'key' => 'session_completed',
                'current_status' => -1
            ];
        }
        $lawyer_document = LawyerDocument::where('order_id',$request->order_id)->where('status','!=',2)->get();
        $meeting = JitsiMeeting::query()->where('order_id', $request->order_id);

        $client_documents = ClientDocument::where('order_id', $request->order_id)->get();
        return response()->json([
            'status' => 200,
            'data' => $data,
            'client_documents' => $client_documents,
            'rejected_lawyer_doc' => $rejected_lawyer_doc ?? 0,
            'rejected_lawyer_doc_reason' => $rejected_lawyer_doc_reason ?? null,
            'session_completed' =>  $session_completed ?? 0,
            'product_form' => $product_form,
            'lawyer_document' => $lawyer_document ?? null,
            'has_ongoing_meeting' => (int) ((clone $meeting)->exists() && ! is_null($meeting->value('completed_at')))
        ]);
    }
    //for order app
    public function getOrderStatus(Request $request)
    {
        $is_arabic = ($request->hasHeader('language')) ? $request->header('language') : 1;
        $is_arabic = $is_arabic == 8 ? 1 : 0;

        $order = Order::findOrFail($request->order_id);
        if($order->lawyer_id){
            $lawyer_details = User::where('id',$order->lawyer_id)->first();
        }
        $product_form = json_decode($order->orderVendorProduct->user_product_order_form, true);
        if($order->ordervendor->order_status_option_id == 5 || $order->ordervendor->order_status_option_id == 6){
            $lawyer_document = LawyerDocument::where('order_id',$order->id)->where('status','!=',2)->get();
        }
        $pending_lawyer_document = LawyerDocument::where('order_id',$order->id)
        ->where('status',0)
        ->first();
        $data = [];
        $cancelOrder = 0;

        // Define labels in both English and Arabic
        $labels = [
            'request_sent' => $is_arabic ? 'تم إرسال الطلب' : 'Request Sent',
            'request_received' => $is_arabic ? 'تم استلام الطلب' : 'Request Received',
            'document_reviewed' => $is_arabic ? 'تمت مراجعة المستندات' : 'Document Reviewed',
            'approved_request' => $is_arabic ? 'تمت الموافقة على الطلب' : 'Approved Request',
            'meeting_scheduled' => $is_arabic ? 'تم تحديد موعد الاجتماع' : 'Meeting Scheduled',
            'document_submitted' => $is_arabic ? 'تم تقديم المستندات' : 'Document Submitted',
            'session_completed' => $is_arabic ? 'اكتملت الجلسة' : 'Session Completed',
            'request_new' => $is_arabic ? 'طلب جديد' : 'New Request'
        ];
  // Define status translations
        $statuses = [
            'Completed' =>  'Completed',
            'Active' =>  'Active',
            'pending' =>  'Pending',
            'Ongoing' =>  'Ongoing'
        ];

        // Define tag translations
        $tags = [
            'Completed' => 'Completed',
            'Reviewed' =>  'Reviewed'
        ];
        if ($order->ordervendor->order_status_option_id == 9 || $order->ordervendor->order_status_option_id == 1) {
            $cancelOrder = 1;
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $is_arabic ? 'مراجعة المستندات' : 'Document Review',
                'status' => $statuses['Active'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => '',
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => null,
            ];
        }

        if ($order->ordervendor->order_status_option_id == 10) {
            $cancelOrder = 1;
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Active'],
                'description' => null,
                'tag' => null,
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['Active'],
                'description' => null,
                'tag' => null,
            ];
        }
        if ($order->ordervendor->order_status_option_id == 2) {
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => 'Ongoing',
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
        }
        if ($order->ordervendor->order_status_option_id == 4) {
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['Active'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                 'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
        }
        if ($order->ordervendor->order_status_option_id == 5) {
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['Active'],
                'description' => null,
                'tag' => '',
            ];
        }
        if ($order->ordervendor->order_status_option_id == 6) {
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
        }
        $pending_document = 0;
        if ($pending_lawyer_document) {
            $pending_document = 1;
        }
        $client_documents = '';
        if (Auth::check() && $request->has('order_id')) {
            $client_documents = ClientDocument::where('client_id', Auth::id())
                ->where('order_id', $request->order_id)
                ->get();
        }
        if ($client_documents) {
            $clientDocuments = $client_documents;
        }
        if ($order->ordervendor->order_status_option_id == 3) {
            $isRejected = 1;
        }
        
        $recentLawyerDocument = LawyerDocument::where('order_id', $request->order_id)
        ->orderBy('created_at', 'desc')
        ->first();
        if (isset($recentLawyerDocument) && $recentLawyerDocument->status == 2) {
            $rejected_lawyer_doc_reason = $recentLawyerDocument->reject_reason;
        }

        $response = [
            'status' => 200,
            'data' => $data,
            'pending_document' => $pending_document,
            'lawyer_document' => $lawyer_document ?? null,
            'rejected_lawyer_doc_reason' => $rejected_lawyer_doc_reason ?? null,
            'client_documents' => $clientDocuments,
            'reorder' => $order->ordervendor->order_status_option_id == 6 ? 1 : 0,
            'product_form' => $product_form,
            'cancelOrder' => $cancelOrder,
            'has_ongoing_meeting' => (int) JitsiMeeting::query()->where('order_id', $request->order_id)
                ->whereRelation('invitation', 'user_id', $order->lawyer_id)
                ->exists(),
                'lawyer_details' => $lawyer_details ?? null
        ];

        // Add `isRejected` only if it is true
        if ($order->ordervendor->order_status_option_id == 3) {
            $response['isRejected'] = 1;
        }

        return response()->json($response);
    }
    public function changeOrderStatus(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $pending_lawyer_document = LawyerDocument::where('order_id', $order->id)->first();
        if ($request->status == "request_received") {
            $order->ordervendor->order_status_option_id = 9;
            OrderStatusTime::create([
                'order_id' => $request->order_id,
                'order_status' => 9,
            ]);
            VendorOrderStatus::create([
                'order_id' => $request->order_id,
                'order_status_option_id' => 9,
                'order_vendor_id' =>   $order->ordervendor->id,
                'vendor_id' => 1
            ]);
        } elseif ($request->status == "document_reviewed") {
            $order->ordervendor->order_status_option_id = 10;
            OrderStatusTime::create([
                'order_id' => $request->order_id,
                'order_status' => 10,
            ]);
            VendorOrderStatus::create([
                'order_id' => $request->order_id,
                'order_status_option_id' => 10,
                'order_vendor_id' =>   $order->ordervendor->id,
                'vendor_id' => 1
            ]);
        } elseif ($request->status == "approved_request") {
            $order->ordervendor->order_status_option_id = 2;
            OrderStatusTime::create([
                'order_id' => $request->order_id,
                'order_status' => 2,
            ]);
            VendorOrderStatus::create([
                'order_id' => $request->order_id,
                'order_status_option_id' => 2,
                'order_vendor_id' =>   $order->ordervendor->id,
                'vendor_id' => 1
            ]);
        } elseif ($request->status == "reject_request") {
            $order->ordervendor->order_status_option_id = 3;
            OrderStatusTime::create([
                'order_id' => $request->order_id,
                'order_status' => 3,
            ]);
            VendorOrderStatus::create([
                'order_id' => $request->order_id,
                'order_status_option_id' => 3,
                'order_vendor_id' =>   $order->ordervendor->id,
                'vendor_id' => 1
            ]);
        } elseif ($request->status == "meeting_scheduled") {
            $order->ordervendor->order_status_option_id = 4;
            OrderStatusTime::create([
                'order_id' => $request->order_id,
                'order_status' => 4,
            ]);
            VendorOrderStatus::create([
                'order_id' => $request->order_id,
                'order_status_option_id' => 4,
                'order_vendor_id' =>   $order->ordervendor->id,
                'vendor_id' => 1
            ]);
        } elseif ($request->status == "document_submitted") {
            //upload document pending
            $order->ordervendor->order_status_option_id = 5;
            OrderStatusTime::updateOrCreate([
                'order_id' => $request->order_id,
                'order_status' => 5,
            ],  [
                'created_at' => now() 
            ]);
            VendorOrderStatus::firstOrCreate([
                'order_id' => $request->order_id,
                'order_status_option_id' => 5,
                'order_vendor_id' =>   $order->ordervendor->id,
                'vendor_id' => 1
            ]);
        } elseif ($request->status == "session_completed") {
            ClientDocument::where(
                'client_id',
                Auth::user()->id
            )->whereNull('order_id')->update([
                'order_id',
                $request->order_id
            ]);
            $order->ordervendor->order_status_option_id = 6;
            OrderStatusTime::create([
                'order_id' => $request->order_id,
                'order_status' => 6,
            ]);
            VendorOrderStatus::create([
                'order_id' => $request->order_id,
                'order_status_option_id' => 6,
                'order_vendor_id' =>   $order->ordervendor->id,
                'vendor_id' => 1
            ]);
        } else {
            return  response(['status' => 400, 'message' => 'Invalid Request']);
        }
        $order->ordervendor->save();
        if (
            $order->ordervendor->order_status_option_id == 2 || $order->ordervendor->order_status_option_id == 10 || $order->ordervendor->order_status_option_id == 3 ||
            $order->ordervendor->order_status_option_id == 4
        ) {
            $this->sendStatusChangePushNotificationCustomerNew([$order->user_id], $order, $order->ordervendor->order_status_option_id);
        }

        $response = [
            'status' => 200,
            'message' => 'order status changed successfully',
            'current_status' => $order->ordervendor->order_status_option_id, // Send the updated status
        ];
        if ($request->status == "document_submitted" && $pending_lawyer_document) {
            $response['pending_lawyer_document'] = $pending_lawyer_document->status;
        }
        return response()->json($response);
    }
    public function sendStatusChangePushNotificationCustomerNew($user_ids, $orderData, $order_status_id)
    {
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            if ($order_status_id == 2) {
                $notification_content = NotificationTemplate::where('id', 5)->first();
            } elseif ($order_status_id == 10) {
                $notification_content = NotificationTemplate::where('id', 20)->first();
            } elseif ($order_status_id == 3) {
                $notification_content = NotificationTemplate::where('id', 6)->first();
            } elseif ($order_status_id == 4) {
                $notification_content = NotificationTemplate::where('id', 7)->first();
            }
            if ($notification_content) {
                $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
                $redirect_URL['type'] = 4;
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "default",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => '',
                        "android_channel_id" => "default-channel-id",
                        "redirect_type" => $redirect_URL['type']
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        "type" => "order_status_change",
                        "order_id" => $orderData->id,
                        "vendor_id" => 1,
                        "order_status" => $order_status_id,
                        "redirect_type" => $redirect_URL['type']
                    ],
                    "priority" => "high"
                ];
                //data added to database - inapp_notification
                InappNotification::create([
                    'title' =>  $notification_content->subject,
                    'description' => $body_content,
                    'order_id' => $orderData->id,
                    'user_id' =>  $user_ids[0],
                    'is_seen' => 0,
                    'meta' => json_encode($data)
                ]);
                sendFcmCurlRequest($data);
            }
        }
    }
}
