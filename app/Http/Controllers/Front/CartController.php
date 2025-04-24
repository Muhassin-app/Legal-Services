<?php
namespace App\Http\Controllers\Front;
use DB;
use Log;
use Auth;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\EstimatedProduct;
use GuzzleHttp\Client as GCLIENT;
use App\Models\EstimatedProductCart;
use App\Models\EstimatedProductAddons;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\{ApiResponser, CartManager, KwikApi, BiddingCartTrait, CartManagerV2};
use App\Http\Controllers\Client\ShippoController;
use App\Http\Controllers\Client\BorzoeDeliveryController;
use App\Http\Controllers\{DunzoController, AhoyController, ShiprocketController,D4BDunzoController};
use App\Models\{AddonSet, BookingOption, Cart, CartAddon, CartProduct, CartCoupon, CartDeliveryFee, Nomenclature, NomenclatureTranslation, User, Product, ClientCurrency, ClientLanguage, CartProductPrescription, ProductVariantSet, Country, UserAddress, Client, ClientPreference, Vendor, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor, PaymentOption, OrderTax, LuxuryOption, UserWishlist, SubscriptionInvoicesUser, LoyaltyCard, CategoryKycDocuments, VendorDineinCategory, VendorDineinTable, VendorDineinCategoryTranslation, VendorDineinTableTranslation, VendorSlot, ProductFaq, CaregoryKycDoc, CartBookingOption, CartRentalProtection, ClientDocument, VerificationOption, VendorSlotDate, TaxRate, Page, WebStylingOption, ProductDeliveryFeeByRole, ProductRentalProtection, RentalProtection};
use Http\Message\Cookie;
use App\Http\Traits\Borzoe;
class CartController extends FrontController
{

    use ApiResponser, CartManager, KwikApi, BiddingCartTrait, CartManagerV2, Borzoe;
    private function randomString()
    {
        $random_string = substr(md5(microtime()), 0, 32);
        while (User::where('system_id', $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, 32);
        }
        return $random_string;
    }

    public function showCart(Request $request, $domain = '')
    {
        if (($request->has('gateway')) && (($request->gateway == 'mobbex') || ($request->gateway == 'yoco'))) {
            if ($request->has('order')) {
                $order = Order::where('order_number', $request->order)->first();
                if ($order) {
                    if ($request->status == 0) {
                        // $order_products = OrderProduct::select('id')->where('order_id', $order->id)->get();
                        // foreach($order_products as $order_prod){
                        //     OrderProductAddon::where('order_product_id', $order_prod->id)->delete();
                        // }
                        // OrderProduct::where('order_id', $order->id)->delete();
                        // OrderProductPrescription::where('order_id', $order->id)->delete();
                        // VendorOrderStatus::where('order_id', $order->id)->delete();
                        // OrderVendor::where('order_id', $order->id)->delete();
                        // OrderTax::where('order_id', $order->id)->delete();
                        // $order->delete();
                        return redirect()->route('showCart')->with('error', 'Your order has been cancelled');
                    } elseif ($request->status == 200) {
                        return redirect()->route('order.success', $order->id);
                    }
                }
            }
            return redirect()->route('showCart');
        }

        $cartData = [];
        $user = Auth::user();
        $countries = Country::get();
        $langId = Session::get('customerLanguage');
        $fixedFee = $this->fixedFee($langId);
        $guest_user = true;
        if ($user) {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'comment_for_pickup_driver', 'comment_for_dropoff_driver', 'comment_for_vendor', 'specific_instructions')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
            $addresses = UserAddress::where('user_id', $user->id)->where('status', 1)->get();
            $guest_user = false;
        } else {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'comment_for_pickup_driver', 'comment_for_dropoff_driver', 'comment_for_vendor', 'specific_instructions')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
            $addresses = collect();
        }
        if ($cart) {
            $cartData = CartProduct::where('status', [0, 1])->where('cart_id', $cart->id)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();
        }

        $navCategories = $this->categoryNav($langId);

        $subscription_features = array();
        $user_subscription = null;
        if ($user) {
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
        $action = (Session::has('vendorType')) ? Session::get('vendorType') : 'delivery';
        // $vendorSlotDate=VendorSlotDate::where('specific_date',date('Y-m-d'))->get();
        $vendorWeeklySlotDay = array();
        $vendorId = 0;
        if (!empty($cartData[0])) {
            $vendorId = $cartData[0]->vendor_id;
            $vendorWeeklySlotDay = VendorSlot::select('start_time', 'end_time', 'day')->join('slot_days', 'slot_days.slot_id', '=', 'vendor_slots.id')->where(['vendor_slots.vendor_id' => $vendorId])->get()->toArray();
        }
        $data = array(
            'navCategories' => $navCategories,
            'cartData' => $cartData,
            'vendorId' => $vendorId,
            'vendorWeeklySlotDay' => $vendorWeeklySlotDay,
            'addresses' => $addresses,
            'countries' => $countries,
            'subscription_features' => $subscription_features,
            'guest_user' => $guest_user,
            'action' => $action,
            'fixedFee' => $fixedFee
        );

        $client_preference_detail = ClientPreference::first();
        if (!empty($client_preference_detail)) {
            $client_preference_detail->is_postpay_enable = getAdditionalPreference(['is_postpay_enable'])['is_postpay_enable'];
        }

        $client_detail = Client::first();
        // dd($client_detail);
        $public_key_yoco = PaymentOption::where('code', 'yoco')->first();
        if ($public_key_yoco) {

            $public_key_yoco = $public_key_yoco->credentials ?? '';
            $public_key_yoco = json_decode($public_key_yoco);
            $public_key_yoco = $public_key_yoco->public_key ?? '';
        }

        $privacy = Page::with(['translations' => function ($q) use ($langId) {
            $q->where('language_id', $langId)->where('type_of_form', [4]);   # get privacy & terms url
        }])->whereHas('translations', function ($q) use ($langId) {
            $q->where('language_id', $langId)->where('type_of_form', [4]);   # get privacy & terms url
        })->first();

        $terms = Page::with(['translations' => function ($q) use ($langId) {
            $q->where('language_id', $langId)->where('type_of_form', [5]);   # get privacy & terms url
        }])->whereHas('translations', function ($q) use ($langId) {
            $q->where('language_id', $langId)->where('type_of_form', [5]);   # get privacy & terms url
        })->first();

        $ageVerify = VerificationOption::where('code', 'yoti')->first();

        $nomenclature = Nomenclature::where('label', 'Product Order Form')->first();
        $nomenclatureProductOrderForm = "Product Order Form";
        if (!empty($nomenclature)) {
            $nomenclatureTranslation = NomenclatureTranslation::where(['nomenclature_id' => $nomenclature->id, 'language_id' => $langId])->first();
            if ($nomenclatureTranslation) {
                $nomenclatureProductOrderForm = $nomenclatureTranslation->name ?? null;
            }
        }
        $template = WebStylingOption::where('is_selected', '1')->first();

        if ($action == "car_rental") {
            return view('frontend.yacht.summary', compact('public_key_yoco', 'cart', 'client_detail', 'data', 'ageVerify', 'terms', 'privacy', 'client_preference_detail', 'nomenclatureProductOrderForm'))->with($data, $nomenclatureProductOrderForm, $client_preference_detail, $client_detail);
        } else {
            return view('frontend.cartnew', compact('public_key_yoco', 'cart', 'client_detail', 'data', 'ageVerify', 'terms', 'privacy', 'client_preference_detail', 'nomenclatureProductOrderForm'))->with($data, $nomenclatureProductOrderForm, $client_preference_detail, $client_detail,$action);


        }
        //
        // return view('frontend.cartnew',compact('public_key_yoco','cart','client_detail'))->with($data,$client_preference_detail,$client_detail);
        // return view('frontend.cartnew')->with(['navCategories' => $navCategories, 'cartData' => $cartData, 'addresses' => $addresses, 'countries' => $countries, 'subscription_features' => $subscription_features, 'guest_user'=>$guest_user]);
    }

    public function postCartRequestFromEstimation(Request $request)
    {
        $js = json_decode($request->addonoptID);
        $addonAr = array();
        $addonsoptAr = array();
        foreach ($js as $add) {
            $addonAr[$add->pid] = $add->addonAr;
            $addonsoptAr[$add->pid] = $add->optAr;
        }

        // dd($addonsoptAr);

        $product_ids = explode(',', $request->product_id);
        $user = Auth::user();
        $vendor_id = $request->vendor_id;
        $variant_id = array();
        $minimum_order_count = array();
        $addon_id = array();
        $option_id = array();
        foreach ($product_ids as $product_id) {
            $product = Product::find($product_id);

            $request->merge([
                "product_id" => $product->id,
                "variant_id" => $product->variant[0]->id,
                "quantity" => $request->quantity,
                "minimum_order_count" => $product->minimum_order_count,
                "from_estimation" => true
            ]);

            $addon_price = 0;

            // foreach($product->sets as $set){
            //     array_push($addon_id, strval($set->addon_id));
            //     $addon_price = $set->setoptions->sum('price');

            //     foreach($set->setoptions as $key => $option){
            //         array_push($option_id, strval($option->id) );
            //     }
            // }

            $request->merge([
                "addonID" => $addonAr[$product->id]
            ]);

            $request->merge([
                "addonoptID" => $addonsoptAr[$product->id]
            ]);


            $result = $this->postAddToCart($request);
            // echo $result;
        }


        // Remove Estimation Cart
        if ($user) {
            $estimatedProductCart = EstimatedProductCart::where('user_id', $user->id)->first();
        } else {
            $user_token = session()->get('_token');
            $estimatedProductCart = EstimatedProductCart::where('unique_identifier', $user_token)->first();
        }
        $estimatedProduct = EstimatedProduct::where('estimated_cart_id', $estimatedProductCart->id)->first();
        $estimatedProductAddons = EstimatedProductAddons::where('estimated_product_id', $estimatedProduct->id)->delete();
        $estimatedProduct->delete();
        $estimatedProductCart->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Product Added Successfully!'
        ]);
    }


    // Added By Ovi
    // Check if the order slots is full
    public function checkSlotOrders(Request $request)
    {
        // Get Logged in user
        $user = Auth::user();

        $client_timezone = DB::table('clients')->first('timezone');
        $timezone = (!empty($user->timezone)) ? $user->timezone : $client_timezone->timezone;

        $schedule_datetime = $request->schedule_datetime;
        $schedule_slot     = $request->schedule_slot;
        $vendor_id         = $request->vendor_id;

        // Get current vendor
        $vendor = Vendor::find($vendor_id);
        $orders_per_slot = $vendor->orders_per_slot ?? 0;
        $orderCount = 0;
        // Get Vendor orders
        $orderVendors = OrderVendor::where('vendor_id', $vendor_id)->get();
        // dd($orderVendors);
        foreach ($orderVendors as $orderVendor) {
            // Get orders of current vendor where scheduled_slot and schedule_pickup_datetime is same as received from frontend.
            $order = Order::where('id', $orderVendor->order_id)->where('scheduled_slot', $schedule_slot)->first();
            // dd($order);
            $if_order_scheduled = 0;
            if ($order) {
                $schedule_pickup = Carbon::parse($order->scheduled_date_time);
                $schedule_pickup_final = convertDateTimeInTimeZone($schedule_pickup, $timezone, 'Y-m-d');
                // dump($schedule_pickup_final);
                // dd($schedule_datetime);
                if ($schedule_pickup_final == $schedule_datetime) {
                    // Increment orderCount and return this count to front end for validation
                    $orderCount++;
                    $if_order_scheduled = 1;
                }
            }

            if ($orderVendor->schedule_slot == $schedule_slot && $if_order_scheduled == 0) {
                $schedule_pickup = Carbon::parse($orderVendor->scheduled_date_time);
                $schedule_pickup_final = convertDateTimeInTimeZone($schedule_pickup, $timezone, 'Y-m-d');

                if ($schedule_pickup_final == $schedule_datetime) {
                    // Increment orderCount and return this count to front end for validation
                    $orderCount++;
                }
            }
        }

        // Return JSON Response
        return response()->json([
            'orderCount' => $orderCount,
            'orders_per_slot' => $orders_per_slot,
        ], 200);
    }



    public function postAddToCart(Request $request, $domain = '')
    {
        $preference = ClientPreference::first();
        $luxury_option = LuxuryOption::where('title', Session::get('vendorType'))->first();
        $vendor = Vendor::find($request->vendor_id);
        try {
            $cart_detail = [];
            $user = Auth::user();
            // $addon_ids = $request->addonID;
            // $addon_options_ids = $request->addonoptID;
            $langId = Session::get('customerLanguage') ?? '1';
            $new_session_token = session()->get('_token');
            $client_currency = ClientCurrency::where('is_primary', '=', 1)->first();
            $user_id = $user ? $user->id : '';
            $variant_id = $request->variant_id;
            $client_timezone = DB::table('clients')->first('timezone');
            $timezone = $user ? $user->timezone : ($client_timezone->timezone ??  'Asia/Kolkata');
            if ($user) {
                $cart_detail['user_id'] = $user_id;
                $cart_detail['created_by'] = $user_id;
            }
            $cart_detail = [
                'is_gift' => 0,
                'status' => '0',
                'item_count' => 0,
                'currency_id' => $client_currency->currency_id,
                'unique_identifier' => !$user ? $new_session_token : '',
            ];
            if ($user) {
                $cart_detail = Cart::updateOrCreate(['user_id' => $user->id], $cart_detail);
                $already_added_product_in_cart = CartProduct::where(["product_id" => $request->product_id, 'cart_id' => $cart_detail->id])->first();
            } else {
                $cart_detail = Cart::updateOrCreate(['unique_identifier' => $new_session_token], $cart_detail);
                $already_added_product_in_cart = CartProduct::where(["product_id" => $request->product_id, 'cart_id' => $cart_detail->id])->first();
            }
            $productDetail = Product::with([
                'variant' => function ($sel) use ($variant_id) {
                    $sel->where('id', $variant_id);
                    $sel->groupBy('product_id');
                }
            ])->find($request->product_id);

            //items already ordered in case order is being edit in cart
            $order_edit_qty = (!empty($already_added_product_in_cart) && !empty($already_added_product_in_cart->order_quantity)) ? $already_added_product_in_cart->order_quantity : 0;
            /** if product is not lonf term */
            if ($productDetail->is_long_term_service != 1) {
                /** if product type is not equal to on demand and appointment
                 **/
                $message= 'Only '.$productDetail->variant[0]->quantity.' is available for this product';

                if( ( !in_array($productDetail->category->categoryDetail->type_id,[8,12])) && ($productDetail->has_inventory == 1)  && ($productDetail->sell_when_out_of_stock == 0)){
                    if(!empty($already_added_product_in_cart)){
                        if(($productDetail->variant[0]->quantity + $order_edit_qty) <= $already_added_product_in_cart->quantity){
                            return response()->json(['status' => 'error', 'message' =>$message]);
                        }
                        if (($productDetail->variant[0]->quantity + $order_edit_qty) <= ($already_added_product_in_cart->quantity + $request->quantity)) {
                            $request->quantity = $productDetail->variant[0]->quantity + $order_edit_qty - $already_added_product_in_cart->quantity;
                        }
                    }
                    if (($productDetail->variant[0]->quantity + $order_edit_qty) < $request->quantity) {
                        if ($productDetail->variant[0]->quantity == 0) {
                            $productDetail->variant[0]->quantity = 1;
                        }
                        $request->quantity = $productDetail->variant[0]->quantity + $order_edit_qty;
                    }
                }
            }


            $addonSets = $addon_ids = $addon_options = array();

            if ($request->has('addonID')) {
                $addon_ids = $request->addonID;
            }

            if ($request->has('addonoptID')) {
                $addon_options = $request->addonoptID;
            }
            foreach ($addon_options as $key => $opt) {
                if (isset($addon_ids[$key])) {
                    $addonSets[$addon_ids[$key]][] = $opt;
                }
            }


            foreach ($addonSets as $key => $value) {
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                    ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                    ->where('ast.language_id', $langId)
                    ->where('addon_sets.status', '!=', '2')
                    ->where('addon_sets.id', $key)->first();
                if (!$addon) {
                    return response()->json(["status" => "error", 'message' => 'Invalid addon or delete by admin. Try again with remove some.'], 404);
                }
                if ($addon->min_select > count($value)) {
                    return response()->json([
                        "status" => "error",
                        'message' => 'Select minimum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 400);
                }
                if ($addon->max_select < count($value)) {
                    return response()->json([
                        "status" => "error",
                        'message' => 'You can select maximum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 400);
                }
            }
            // total booking time for rental case
            $total_booking_time = $request->has('total_booking_time') ? $request->total_booking_time : null;


            // total booking time as single service duration time as per service for get totel service time multiply by quantity
            if (@$luxury_option->id && in_array($luxury_option->id, [6, 8])) {
                $total_booking_time = $productDetail->minimum_duration_min;
            }
            $oldquantity = $isnew = 0;
            $service_start_date   = '';
            $start_date  = $request->has('start_date') ? $request->start_date : null;
            $isLongTermProduct = 0;
            if ($request->has('service_start_time') && !empty($request->service_start_time)) {


                $isLongTermProduct  = 1;

                $time = '2022-10-27 ' . $request->service_start_time;
                /**only need time */
                $service_start_time = Carbon::parse($time, $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                $start_date = $service_start_time;
                /** we user start_date_time for long term order timing */
                $service_start_date = carbon::now()->setTimezone('UTC')->format('Y-m-d H:i:s');
            }


            $cart_product_detail = [
                'status'            => '0',
                'is_tax_applied'    => '1',
                'created_by'        => $user_id,
                'cart_id'           => $cart_detail->id,
                'quantity'          => $request->quantity ?? 1,
                'vendor_id'         => $request->vendor_id,
                'product_id'          => $request->product_id,
                'variant_id'          => $request->variant_id,
                'currency_id'         => $client_currency->currency_id,
                'luxury_option_id'    => ($luxury_option) ? $luxury_option->id : 0,
                'start_date_time'     => $request->start_date ?? $request->start_date_time,
                'end_date_time'       => $request->end_date ?? $request->end_date_time,
                'additional_increments_hrs_min' => $request->has('incremental_hrs') ? $request->incremental_hrs : null,
                'total_booking_time'  => $total_booking_time,
                'service_day'         => $request->has('service_day') ? $request->service_day : null,
                'service_date'        => $request->has('service_date') ? $request->service_date : null,
                'service_period'      => $request->has('service_period') ? $request->service_period : null,
                'service_start_date'  => @$service_start_date,

                'slot_id'  => $request->has('sele_slot_id') ? $request->sele_slot_id : null,
                'delivery_date'  => $request->has('delivery_date') ? $request->delivery_date : null,
                'slot_price'  => $request->has('sele_slot_price') ? $request->sele_slot_price : null
            ];

            //Check if

            if ($request->has('dispatcherAgentData') && !empty($request->dispatcherAgentData)) {


                $dataTime = Carbon::parse($request->dispatcherAgentData['onDemandBookingdate'], $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                $slot    = $request->dispatcherAgentData['slot'] ?? Carbon::parse($request->dispatcherAgentData['onDemandBookingdate'], $timezone)->setTimezone('UTC')->format('H:i:s');
                $cart_product_detail['schedule_type'] = 'schedule';
                $cart_product_detail['scheduled_date_time'] = @$dataTime;
                $cart_product_detail['schedule_slot'] = @$slot ?? null;
                $cart_product_detail['dispatch_agent_price'] = @$request->dispatcherAgentData['agent_price'] ?? null;
                $cart_product_detail['dispatch_agent_id'] = @$request->dispatcherAgentData['agent_id'] ?? null;
                $cart_detail = Cart::updateOrCreate(['user_id' => $user->id], ['address_id' => @$request->dispatcherAgentData['address_id']]);
            }

            //Check if BidId and bid dicount coulmn exists in table

            $cart_product_detail['bid_number'] = @$request->bid_number ?? null;
            $cart_product_detail['bid_discount'] = @$request->bid_discount ?? null;

            $recurringformPost = '';
            if (isset($request->recurringformPost) && !empty($request->recurringformPost)) {
                //This Function Return objected array of recurring data
                $recurringformPost = $this->recurringCalculationFunction($request);

                //Check if recurring_booking_type,recurring_week_day,recurring_week_type,recurring_day_data,recurring_booking_time coulmn exists in table
                $cart_product_detail['recurring_booking_type']  = @$recurringformPost->action ?? null;
                $cart_product_detail['recurring_week_day']      = @$recurringformPost->weekTypes ?? null;
                $cart_product_detail['recurring_week_type']     = @$recurringformPost->weekTypes ?? null;
                $cart_product_detail['recurring_day_data']      = @$recurringformPost->selectedCustomdates ?? null;
                $cart_product_detail['recurring_booking_time']  = @$recurringformPost->schedule_time ?? null;
            }



            $checkVendorId = CartProduct::where('cart_id', $cart_detail->id)->where('vendor_id', '!=', $request->vendor_id)->first();
            /** check is long term is added to cart */
            $checkLongTermService = CartProduct::where('cart_id', $cart_detail->id)->with('product')->first();
            $isLongTermService  = 0;

            if ($checkLongTermService && isset($checkLongTermService->product)) {
                $isLongTermService = $checkLongTermService->product->is_long_term_service;
            }

            if (@$luxury_option && $luxury_option) {
                $checkCartLuxuryOption = CartProduct::where('luxury_option_id', '!=', $luxury_option->id)->where('cart_id', $cart_detail->id)->first();
                if ($checkCartLuxuryOption) {
                    CartProduct::where('cart_id', $cart_detail->id)->delete();
                }
                if (@$luxury_option->id && ($luxury_option->id == 2 || $luxury_option->id == 3)) {
                    if ($checkVendorId) {
                        CartProduct::where('cart_id', $cart_detail->id)->delete();
                    } else {
                        $checkVendorTableAdded = CartProduct::where('cart_id', $cart_detail->id)->where('vendor_id', $request->vendor_id)->whereNotNull('vendor_dinein_table_id')->first();
                        $cart_product_detail['vendor_dinein_table_id'] = ($checkVendorTableAdded) ? $checkVendorTableAdded->vendor_dinein_table_id : NULL;
                    }
                }
            }
            if (((isset($preference->isolate_single_vendor_order)) && ($preference->isolate_single_vendor_order == 1)) || (@$luxury_option->id && $luxury_option->id == 4)) {
                if ($checkVendorId) {
                    CartProduct::where('cart_id', $cart_detail->id)->delete();
                }
            }
            /** delete is long term is added from cart */
            if ($isLongTermService || ($isLongTermProduct == 1)) {
                CartProduct::where('cart_id', $cart_detail->id)->delete();
            }


            $cartProduct = CartProduct::where('product_id', $request->product_id)->where('variant_id', $request->variant_id)->where('cart_id', $cart_detail->id)->first();
            if (!$cartProduct) {
                $isnew = 1;
            } else {
                $checkaddonCount = CartAddon::where('cart_product_id', $cartProduct->id)->count();
                if (count($addon_ids) != $checkaddonCount) {
                    $isnew = 1;
                } else {
                    foreach ($addon_options as $key => $opts) {
                        $cart_addon = CartAddon::where('cart_product_id', $cartProduct->id)
                            ->where('addon_id', $addon_ids[$key])
                            ->where('option_id', $opts)->first();

                        if (!$cart_addon) {
                            $isnew = 1;
                        }
                    }
                }
            }

            if ($isnew == 1) {
                $cartProduct = CartProduct::create($cart_product_detail);

                if (!empty($addon_ids) && !empty($addon_options)) {
                    $saveAddons = array();
                    foreach ($addon_options as $key => $opts) {
                        if (isset($addon_ids[$key])) {
                            $saveAddons[] = [
                                'option_id' => $opts,
                                'cart_id' => $cart_detail->id,
                                'addon_id' => $addon_ids[$key],
                                'cart_product_id' => $cartProduct->id,
                            ];
                        }
                    }
                    if (!empty($saveAddons)) {
                        CartAddon::insert($saveAddons);
                    }
                }
            } else {
                $cartProduct->quantity = $cartProduct->quantity + $request->quantity;

                //Check if BidId and bid dicount coulmn exists in table
                $cartProduct->bid_number = @$request->bid_number ?? null;
                $cartProduct->bid_discount = @$request->bid_discount ?? null;

                $cartProduct->save();
            }
            $quantityCart = CartProduct::where('cart_id', $cart_detail->id)->sum('quantity');

            return response()->json(['status' => 'success', 'message' => 'Product Added Successfully!', 'cart_product_id' => $cartProduct->id, 'cart_quantity' => $quantityCart ?? 0, 'product_id' => $cartProduct->product_id, 'vendor' => $vendor]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    /**
     * add product to cart
     *
     * @return \Illuminate\Http\Response
     */
    public function addToCart(Request $request, $domain = '')
    {
        $langId = Session::get('customerLanguage') ?? '1';
        if ($request->has('addonID') && $request->has('addonoptID')) {
            $addon_ids = $request->addonID;
            $addon_options = $request->addonoptID;
            $addonSets = array();
            foreach ($addon_options as $key => $opt) {
                $addonSets[$addon_ids[$key]][] = $opt;
            }
            foreach ($addonSets as $key => $value) {
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                    ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                    ->where('ast.language_id', $langId)
                    ->where('addon_sets.status', '!=', '2')
                    ->where('addon_sets.id', $key)->first();
                if (!$addon) {
                    return response()->json(['error' => 'Invalid addon or delete by admin. Try again with remove some.'], 404);
                }
                if ($addon->min_select > count($value)) {
                    return response()->json([
                        'error' => 'Select minimum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
                if ($addon->max_select < count($value)) {
                    return response()->json([
                        'error' => 'You can select maximum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
            }
        }
        $user_id = ' ';
        $cartInfo = ' ';
        $user = Auth::user();
        $currency = ClientCurrency::where('is_primary', '=', 1)->first();
        if ($user) {
            $user_id = $user->id;
            $userFind = Cart::where('user_id', $user_id)->first();
            if (!$userFind) {
                $cart = new Cart;
                $cart->status = '0';
                $cart->is_gift = '1';
                $cart->item_count = '1';
                $cart->user_id = $user_id;
                $cart->created_by = $user_id;
                $cart->currency_id = $currency->currency->id;
                $cart->unique_identifier = $user->system_id;
                $cart->save();
                $cartInfo = $cart;
            } else {
                $cartInfo = $userFind;
            }
            $checkIfExist = CartProduct::where('product_id', $request->product_id)->where('variant_id', $request->variant_id)->where('cart_id', $cartInfo->id)->first();
            if ($checkIfExist) {
                $checkIfExist->quantity = (int)$checkIfExist->quantity + 1;
                $cartInfo->cartProducts()->save($checkIfExist);
                return response()->json(['status' => 'success', 'message' => 'Product Added Successfully!']);
            } else {
            }
        } else {
            $cart_detail = Cart::where('unique_identifier', session()->get('_token'))->first();
            if (!$cart_detail) {
                $cart = new Cart;
                $cart->status = '0';
                $cart->is_gift = '1';
                $cart->item_count = '1';
                $cart->currency_id = $currency->currency->id;
                $cart->unique_identifier = session()->get('_token');
                $cart->save();
            }
            $productForVendor = Product::where('id', $request->product_id)->first();
            $cartProduct = new CartProduct;
            $cartProduct->status  = '0';
            $cartProduct->is_tax_applied  = '1';
            $cartProduct->created_by  = $user_id;
            $cartProduct->cart_id  = $cart_detail->id;
            $cartProduct->quantity  = $request->quantity;
            $cartProduct->product_id = $request->product_id;
            $cartProduct->variant_id  = $request->variant_id;
            $cartProduct->currency_id = $cart_detail->currency_id;
            $cartProduct->vendor_id  = $productForVendor->vendor_id;
            $cartProduct->save();
            if ($request->has('addonID') && $request->has('addonID')) {
                foreach ($addon_ids as $key => $value) {
                    $aa = $addon_ids[$key];
                    $bb = $addon_options[$key];
                    $cartAddOn = new CartAddon;
                    $cartAddOn->addon_id = $aa;
                    $cartAddOn->option_id = $bb;
                    $cartAddOn->cart_id = $cart_detail->id;
                    $cartAddOn->cart_product_id = $cartProduct->id;
                    $cartAddOn->save();
                }
            }
            return response()->json(['status' => 'success', 'message' => 'Product Added Successfully!', 'cart_product_id' => $cartProduct->id]);
        }
    }

    /**
     * add wishlist products to cart
     *
     * @return \Illuminate\Http\Response
     */
    public function addWishlistToCart(Request $request, $domain = '')
    {
        try {
            $cart_detail = [];
            $user = Auth::user();
            $new_session_token = session()->get('_token');
            $client_currency = ClientCurrency::where('is_primary', '=', 1)->first();
            $user_id = $user ? $user->id : '';
            if ($user) {
                $cart_detail['user_id'] = $user_id;
                $cart_detail['created_by'] = $user_id;
                $cart_detail = [
                    'is_gift' => 1,
                    'status' => '0',
                    'item_count' => 0,
                    'currency_id' => $client_currency->currency_id,
                    'unique_identifier' => !$user ? $new_session_token : '',
                ];
                $cart_detail = Cart::updateOrCreate(['user_id' => $user->id], $cart_detail);
                foreach ($request->wishlistProducts as $product) {
                    $checkIfExist = CartProduct::where('product_id', $product['product_id'])->where('variant_id', $product['variant_id'])->where('cart_id', $cart_detail->id)->first();
                    if ($checkIfExist) {
                        $checkIfExist->quantity = (int)$checkIfExist->quantity + 1;
                        $cart_detail->cartProducts()->save($checkIfExist);
                    } else {
                        $productVendor = Product::where('id', $product['product_id'])->first();
                        $cart_product_detail = [
                            'status'  => '0',
                            'is_tax_applied'  => '1',
                            'created_by'  => $user_id,
                            'cart_id'  => $cart_detail->id,
                            'quantity'  => 1,
                            'vendor_id'  => $productVendor->vendor_id,
                            'product_id' => $product['product_id'],
                            'variant_id'  => $product['variant_id'],
                            'currency_id' => $client_currency->currency_id,
                        ];
                        $cart_product = CartProduct::updateOrCreate(['cart_id' =>  $cart_detail->id, 'product_id' => $product['product_id']], $cart_product_detail);
                    }
                    $exist = UserWishlist::where('user_id', Auth::user()->id)->where('product_id', $product['product_id'])->where('product_variant_id', $product['variant_id'])->first();
                    if ($exist) {
                        $exist->delete();
                    }
                }
            }
            return response()->json(['status' => 'success', 'message' => 'Products Has Been Added to Cart Successfully!']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->message()]);
        }
    }

    //initialize bidding cart
    public function initCart(Request $request)
    {
        $this->biddingCart($request->id);

        return redirect()->route('showCart')->with('success', 'Product added successfully');
    }
    /**
     * get products from cart
     *
     * @return \Illuminate\Http\Response
     */
    public function getCartProducts(Request $request, $domain = '')
    {
        $cart_details = [];
        $user = Auth::user();
        $curId = Session::get('customerCurrency');
        $langId = Session::get('customerLanguage');
        if ($user) {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
        } else {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
        }
        if ($cart) {
            $cart_details = $this->getCart($cart);
        }
        if ($cart_details && !empty($cart_details)) {
            return response()->json([
                'data' => $cart_details,
            ]);
        }


        return response()->json([
            'message' => "No product found in cart",
            'data' => $cart_details,
        ]);
    }

    public function getProductPrescription(Request $request)
    {
        if (!empty($request->prescriptionId) && $request->requestType == 'delete_prescription') {
            CartProductPrescription::where('id', $request->prescriptionId)->delete();
            return response()->json(['status' => 'success', 'message' => "Prescription remove Successfully"]);
        }
        $productPrescription = CartProductPrescription::where('cart_id', $request->cart)->where('product_id', $request->product)->get()->toArray();
        return response()->json($productPrescription);
    }

    /**
     * Get Cart Items
     *
     */
    public function getCart($cart, $address_id = 0, $code = 'D', $schedule_datetime_del = '')
    {
        $address = [];
        $category_array = [];
        $cart_id = $cart->id;
        $user = Auth::user();
        $langId = Session::has('customerLanguage') ? Session::get('customerLanguage') : 1;
        $curId = Session::get('customerCurrency');
        $action = (Session::has('vendorType')) ? Session::get('vendorType') : 'delivery';
        $preferences = ClientPreference::with(['client_detail:id,code,country_id'])->first();
        $countries = Country::get();
        $cart->pharmacy_check = $preferences->pharmacy_check;
        $customerCurrency = ClientCurrency::where('currency_id', $curId)->first();
        $nowdate = Carbon::now()->toDateTimeString();
        $nowdate = convertDateTimeInClientTimeZone($nowdate);
        $latitude = '';
        $longitude = '';
        $user_allAddresses = collect();
        $upSell_products = collect();
        $crossSell_products = collect();
        $couponGetAmount = 0;
        /*Getting User Address */
        $guest_user = true;
        if ($user) {
            $user_allAddresses = UserAddress::where('user_id', $user->id)->where('status', 1)->orderBy('is_primary', 'Desc')->get();
            if ($address_id > 0) {
                $address = UserAddress::where('user_id', $user->id)->where('id', $address_id)->first();
            } else {
                $address = UserAddress::where('user_id', $user->id)->where('is_primary', 1)->where('status', 1)->first();
                $address_id = ($address) ? $address->id : 0;
            }
            $guest_user = false;
        }

        /* Getting User Lat Long */
        if ($action != 'delivery') {
            $latitude = Session::get('latitude') ?? '';
            $longitude = Session::get('longitude') ?? '';
        } else {
            $latitude = ($address) ? $address->latitude : '';
            $longitude = ($address) ? $address->longitude : '';
        }

        /* Delete Cart product if dont exists*/
        $delifproductnotexist = CartProduct::where('cart_id', $cart_id)->doesntHave('product')->delete();

        /* Getting All Cart Data */
        $cartData = CartProduct::with([
            'vendor', 'vendor.slots', 'vendor.slot.day', 'vendor.slot.geos.serviceArea', 'vendor.slotDate.geos.serviceArea', 'vendor.slotsForPickup', 'vendor.slotsForDropoff', 'vendor.slotDate', 'coupon' => function ($qry) use ($cart_id) {
                $qry->where('cart_id', $cart_id);
            }, 'vendorProducts.pvariant.media.pimage.image', 'vendorProducts.product.media.image',
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
                $qry->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                $qry->select('addon_options.id', 'addon_options.price', 'apt.title', 'addon_options.addon_id', 'apt.language_id');
                $qry->where('apt.language_id', $langId)->groupBy(['addon_options.id', 'apt.language_id']);
                // $qry->where('language_id', $langId);
            }, 'vendorProducts.product.taxCategory.taxRate'
        ])->select('vendor_id', 'luxury_option_id', 'vendor_dinein_table_id', 'id as cart_product_id', 'schedule_type', 'scheduled_date_time', 'schedule_slot')->where('status', [0, 1])->where('cart_id', $cart_id)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();

        //dd($cartData->toArray());
        /* Getting All Taxes available and making TaxRate array according to requirement */
        $taxes = TaxRate::all();
        $taxRates = array();
        foreach ($taxes as $tax) {
            $taxRates[$tax->id] = ['tax_rate' => $tax->tax_rate, 'tax_amount' => $tax->tax_amount];
        }


        $loyalty_amount_saved = 0;
        $redeem_points_per_primary_currency = '';
        $loyalty_card = LoyaltyCard::where('status', '0')->first();
        if ($loyalty_card) {
            $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
        }

        /* Getting All User Subscription plans */
        $subscription_features = array();
        $user_subscription = null;
        if ($user) {
            $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
            if ($order_loyalty_points_earned_detail) {
                $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                    $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                    if (($customerCurrency) && ($customerCurrency->is_primary != 1)) {
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
            // if ($user_subscription) {
            //     foreach ($user_subscription->features as $feature) {
            //         $subscription_features[] = $feature->feature_id;
            //     }
            // }

            $cart->scheduled_date_time = convertDateTimeInTimeZone($cart->scheduled_date_time, $user->timezone, 'Y-m-d\TH:i');
        }
        $total_payable_amount = $total_subscription_discount = $total_discount_amount = $total_discount_percent = $total_taxable_amount = $deliver_charges_lalmove = 0.00;
        /* If cart have data then getting total and other variable set */
        if ($cartData) {
            $addon_price = 0;
            $cart_dinein_table_id = NULL;
            $vendor_details = [];
            $delivery_status = 1;
            $is_vendor_closed = 0;
            $closed_store_order_scheduled = 0;
            $deliver_charge = 0;
            $deliveryCharges = 0;
            $delay_date = 0;
            $pickup_delay_date = 0;
            $dropoff_delay_date = 0;
            $sub_total = 0;
            $total_service_fee = 0;
            $product_out_of_stock = 0;
            $PromoDelete = 0;
            $d = 0;
            $total_container_charges = 0;
            $all_vendor_deliver_charges = 0;
            $all_vendor_markup_charges = 0;
            $total_quantity = 0;
            if (!empty($user)) {
                $client_timezone = DB::table('clients')->first('timezone');
                $user->timezone = $client_timezone->timezone ?? $user->timezone;
            }
            // $sub_total+=$opt_price_in_currency;
            /* Getting in vendor loop */
            foreach ($cartData as $ven_key => $vendorData) {

                $opt_quantity_price_new = 0.00;
                $addon_price = 0;
                $is_promo_code_available = 0;
                $vendor_products_total_amount = $payable_amount = $taxable_amount = $subscription_discount = $discount_amount = $discount_percent = $deliver_charge = $delivery_fee_charges = $delivery_fee_charges_static =  $deliver_charges_lalmove = 0.00;
                $delivery_count = 0;
                $delivery_count_lm = 0;
                $coupon_amount_used = 0;
                $coupon_apply_price = 0;
                $slotsCnt = 0;
                $PromoFreeDeliver = 0;

                if (!empty($user)) {
                    $scheduledDateTime = dateTimeInUserTimeZone($vendorData->scheduled_date_time, $user->timezone);
                    $vendorData->scheduled_date_time = date('Y-m-d', strtotime($scheduledDateTime));
                }


                $slots = (object)showSlot($vendorData->scheduled_date_time, $vendorData->vendor_id, 'delivery');
                if ($cartData->count() > 1) {
                    $vendorData->selected_slot = $vendorData->schedule_slot;
                }
                $vendorData->slots = $slots;
                $vendorData->slotsCnt = count((array)$slots);
                $vendorData->delay_date = date('Y-m-d');

                if (Session::has('vendorTable')) {
                    if ((Session::has('vendorTableVendorId')) && (Session::get('vendorTableVendorId') == $vendorData->vendor_id)) {
                        $cart_dinein_table_id = Session::get('vendorTable');
                    }
                    Session::forget(['vendorTable', 'vendorTableVendorId']);
                } else {
                    $cart_dinein_table_id = $vendorData->vendor_dinein_table_id;
                }

                /* Getting vendor details */
                if ($action != 'delivery') {
                    $vendor_details['vendor_address'] = $vendorData->vendor->select('id', 'latitude', 'longitude', 'address')->where('id', $vendorData->vendor_id)->first();
                    if ($action == 'dine_in') {
                        $vendor_tables = VendorDineinTable::where('vendor_id', $vendorData->vendor_id)->with('category')->get();
                        foreach ($vendor_tables as $vendor_table) {
                            $vendor_table->qr_url = url('/vendor/' . $vendorData->vendor->slug . '/?id=' . $vendorData->vendor_id . '&name=' . $vendorData->vendor->name . '&table=' . $vendor_table->id);
                        }
                        $vendor_details['vendor_tables'] = $vendor_tables;
                    }
                } else {

                    if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)) {
                        if ($address_id > 0) {

                            if (!empty($latitude) && !empty($longitude)) {
                                $serviceArea = $vendorData->vendor->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                                    $query->select('vendor_id')
                                        ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                                })->where('id', $vendorData->vendor_id)->get();
                            }
                        }
                    }
                }

                if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {
                    if (!empty($latitude) && !empty($longitude)) {
                        if (($preferences->slots_with_service_area == 1) && ($vendorData->vendor->show_slot == 0)) {
                            $serviceArea = $vendorData->vendor->where(function ($query) use ($latitude, $longitude) {
                                $query->whereHas('slot.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                    $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                })
                                    ->orWhereHas('slotDate.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                        $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                    });
                            })->where('id', $vendorData->vendor_id)->get();
                        }
                    }
                }

                Session()->put('vid', '');
                //get Coupon Discount for product case
                $coupon_product_ids = [];
                $coupon_vendor_ids = [];
                $coupon_product_discount = 0;
                $in_or_not = 0;
                if (isset($vendorData->coupon) && !empty($vendorData->coupon) && isset($vendorData->coupon->promo) && !empty($vendorData->coupon->promo)) {
                    if ($vendorData->coupon->promo->restriction_on == 0) {
                        $coupon_product_ids = $vendorData->coupon->promo->details->pluck('refrence_id')->toArray();
                        $in_or_not = $vendorData->coupon->promo->restriction_type;
                    } elseif ($vendorData->coupon->promo->restriction_on == 1) {
                        $coupon_vendor_ids = $vendorData->coupon->promo->details->pluck('refrence_id')->toArray();
                        $in_or_not = $vendorData->coupon->promo->restriction_type;
                    }
                }
                $cart_product_ids = [];
                $totalMarkup = 0;
                /* Getting in Vendor product loop and setting product values*/
                foreach ($vendorData->vendorProducts as $ven_key => $prod) {

                    if ($prod->pvariant) {

                        $cart_product_ids[] = $prod->product_id;
                        /* Setting Out of Stock if requied quanitity is not available */
                        if ($prod->product->sell_when_out_of_stock == 0 && $prod->product->has_inventory == 1) {
                            $quantity_check = productvariantQuantity($prod->variant_id);
                            if ($quantity_check < $prod->quantity) {
                                $delivery_status = 0;
                                $product_out_of_stock = 1;
                            }
                        }

                        $cart_product_prescription = CartProductPrescription::where('cart_id', $cart->id)->where('product_id', $prod->product_id)->count();
                        $vendorData->vendorProducts[$ven_key]->cart_product_prescription = $cart_product_prescription;

                        if ($cart_dinein_table_id > 0) {
                            $prod->update(['vendor_dinein_table_id' => $cart_dinein_table_id]);
                        }

                        $prod->product_out_of_stock =  $product_out_of_stock;
                        $prod->faq_count = 0;
                        if ($preferences->product_order_form == 1 && $user) {
                            $prod->faq_count =  ProductFaq::where('product_id', $prod->product->id)->count();
                        }
                        $prod->category_id = $prod->product->category_id;
                        $prod->category_kyc_count = 0;
                        if ($preferences->category_kyc_documents == 1) {
                            if (!in_array($prod->product->category_id, $category_array)) {
                                $category_array[] = $prod->product->category_id;
                            }
                        }

                        $quantity_price = 0;
                        $divider = (empty($prod->doller_compare) || $prod->doller_compare < 0) ? 1 : $prod->doller_compare;
                        //need change here
                        //dd($prod->pvariant->price);
                        $price_in_currency = $prod->pvariant->price ?? 0;
                        $totalMarkup += $prod->pvariant->markup_price ?? 0;
                        $price_in_doller_compare = $prod->pvariant->price ?? 0;
                        $container_charges_in_currency = $prod->pvariant->container_charges ?? 0;
                        $coupon_apply_price += $price_in_currency;
                        $container_charges_in_doller_compare = $prod->pvariant->container_charges ?? 0;
                        if ($customerCurrency && $prod->pvariant) {
                            $price_in_currency = $prod->pvariant->price / $divider;
                            $price_in_doller_compare = $price_in_currency * $customerCurrency->doller_compare;

                            $container_charges_in_currency = $prod->pvariant->container_charges / $divider;
                            $container_charges_in_doller_compare = $container_charges_in_currency * $customerCurrency->doller_compare;
                        }
                        // dd($price_in_currency);

                        $quantity_price = $price_in_doller_compare * $prod->quantity;
                        $sub_total += $quantity_price + $container_charges_in_currency;
                        $quantity_container_charges = $container_charges_in_doller_compare * $prod->quantity;
                        $prod->pvariant->price_in_cart = $prod->pvariant->price ?? 0;
                        $total_quantity += $prod->quantity;
                        // $prod->pvariant->price = decimal_format($price_in_currency);
                        //dd($prod->pvariant->price);
                        $prod->pvariant->container_charges = decimal_format($container_charges_in_currency);
                        $prod->image_url = $this->loadDefaultImage();
                        $prod->pvariant->media_one = isset($prod->pvariant->media) ? $prod->pvariant->media->first() : [];
                        $prod->pvariant->media_second = isset($prod->product->media) ? $prod->product->media->first() : [];
                        $prod->pvariant->multiplier = ($customerCurrency) ? $customerCurrency->doller_compare : 1;
                        $prod->quantity_price = decimal_format($quantity_price);
                        $prod->quantity_container_charges = decimal_format($quantity_container_charges);
                        //echo "index 1: quantity_price. ",$quantity_price." quantity_container_charges:".$quantity_container_charges;

                        $payable_amount = $payable_amount + $quantity_price + $quantity_container_charges;
                        $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price;
                        $total_container_charges = $total_container_charges + $quantity_container_charges;
                        if (
                            ($in_or_not == 0 && in_array($prod->product_id, $coupon_product_ids))
                            || ($in_or_not == 1 && !in_array($prod->product_id, $coupon_product_ids))
                            || ($in_or_not == 0 && in_array($vendorData->vendor_id, $coupon_vendor_ids))
                            || ($in_or_not == 1 && !in_array($vendorData->vendor_id, $coupon_vendor_ids))
                        ) {
                            $coupon_product_discount = $coupon_product_discount + $quantity_price + $quantity_container_charges;
                        }
                        /* Getting Add On info */
                        if ($prod->addon->isNotEmpty()) {
                            foreach ($prod->addon as $ck => $addons) {
                                if (isset($addons->option)) {
                                    $opt_price_in_currency = $addons->option->price;
                                    $opt_price_in_doller_compare = $addons->option->price;
                                    if ($customerCurrency) {
                                        $opt_price_in_currency = $addons->option->price / $divider;
                                        $opt_price_in_doller_compare = $opt_price_in_currency * $customerCurrency->doller_compare;
                                    }
                                    $sub_total += ($opt_price_in_currency * $prod->quantity);
                                    $coupon_apply_price += $opt_price_in_currency;

                                    $opt_quantity_price = decimal_format($opt_price_in_doller_compare * $prod->quantity);
                                    $addons->option->price_in_cart = $addons->option->price;
                                    $addon_price = $addons->option->price;
                                    $addons->option->price = decimal_format($opt_price_in_currency);
                                    $addons->option->multiplier = ($customerCurrency) ? $customerCurrency->doller_compare : 1;
                                    $addons->option->quantity_price = $opt_quantity_price;
                                    $opt_quantity_price_new += $opt_quantity_price;
                                    $payable_amount = $payable_amount + $opt_quantity_price;
                                    $quantity_price = $quantity_price + $opt_quantity_price;
                                    if (
                                        ($in_or_not == 0 && in_array($prod->product_id, $coupon_product_ids))
                                        || ($in_or_not == 1 && !in_array($prod->product_id, $coupon_product_ids))
                                        || ($in_or_not == 0 && in_array($vendorData->vendor_id, $coupon_vendor_ids))
                                        || ($in_or_not == 1 && !in_array($vendorData->vendor_id, $coupon_vendor_ids))
                                    ) {
                                        $coupon_product_discount = $coupon_product_discount + $opt_quantity_price;
                                    }
                                }
                            }
                        }
                        //echo "index 1: quantity_price. ",$quantity_price." quantity_container_charges:".$quantity_container_charges;
                        /* Getting taxes info */

                        $taxData = array();
                        if (!empty($prod->product->taxCategory) && count($prod->product->taxCategory->taxRate) > 0) {
                            foreach ($prod->product->taxCategory->taxRate as $tckey => $tax_value) {
                                $rate = $tax_value->tax_rate;
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

                        if (isset($prod->pvariant->image->imagedata) && !empty($prod->pvariant->image->imagedata)) {
                            $prod->cartImg = $prod->pvariant->image->imagedata;
                        } else {
                            $prod->cartImg = (isset($prod->product->media[0]) && !empty($prod->product->media[0])) ? $prod->product->media[0]->image : '';
                        }

                        if ($prod->product->delay_hrs_min != 0) {
                            if ($prod->product->delay_hrs_min > $delay_date)
                                $delay_date = $prod->product->delay_hrs_min;
                        }
                        if ($prod->product->pickup_delay_hrs_min != 0) {
                            if ($prod->product->pickup_delay_hrs_min > $delay_date)
                                $pickup_delay_date = $prod->product->pickup_delay_hrs_min;
                        }

                        if ($prod->product->dropoff_delay_hrs_min != 0) {
                            if ($prod->product->dropoff_delay_hrs_min > $delay_date)
                                $dropoff_delay_date = $prod->product->dropoff_delay_hrs_min;
                        }

                        $select = '';

                        //if ($action == 'delivery' || $action =='appointment' ) {
                        if (in_array($action, ['delivery', 'appointment', 'on_demand'])) {


                            $delivery_fee_charges = 0;
                            $deliver_charges_lalmove = 0;
                            $deliveryCharges = 0;
                            $code = (($code) ? $code : $cart->shipping_delivery_type);
                            $prod->product->Requires_last_mile = 1;
                            if (!empty($prod->product->Requires_last_mile) && ($prod->product->Requires_last_mile == 1)) {
                                $deliveries = $this->getDeliveryOptions($vendorData, $preferences, $payable_amount, $address, $schedule_datetime_del);
                                if (isset($deliveries[0])) {
                                    $select .= '<select name="vendorDeliveryFee" class="form-control delivery-fee select">';
                                    if (count($deliveries) > 1) {
                                        foreach ($deliveries as $k => $opt) {
                                            $select .= '<option value="' . $opt['code'] . '" ' . (($opt['code'] == $code) ? 'selected' : '') . '  >' . __($opt['courier_name']) . ', ' . __('Rate') . ' : ' . decimal_format($opt['rate']) . '</option>';
                                            //$select .= '<option value="'.$opt['code'].'" '.(($opt['code']==$code)?'selected':'').'  >'.$opt['rate'].'</option>';
                                        }

                                    } else {
                                        foreach ($deliveries as $k => $opt) {
                                            //$select .= '<option value="'.$opt['code'].'" '.(($opt['code']==$code)?'selected':'').'  >'.__($opt['courier_name']).', '.__('Rate').' : '.$opt['rate'].'</option>';
                                            $select .= '<option value="' . $opt['code'] . '" ' . (($opt['code'] == $code) ? 'selected' : '') . '  >' . decimal_format($opt['rate']) . '</option>';
                                        }
                                    }
                                    $select .= '</select>';
                                    if ($code) {
                                        $new = array_filter($deliveries, function ($var) use ($code) {
                                            return ($var['code'] == $code);
                                        });
                                        foreach ($new as $rate) {
                                            $deliveryCharges = $rate['rate'];
                                            $deliveryDuration = $rate['duration'];
                                        }
                                        if ($deliveryCharges) {
                                            $deliveryCharges = $rate['rate'];
                                            $deliveryDuration = $rate['duration'];
                                        } else {
                                            $deliveryCharges = $deliveries[0]['rate'];
                                            $deliveryDuration = $deliveries[0]['duration'];
                                            $code = $deliveries[0]['code'];
                                        }
                                    } else {
                                        $deliveryCharges  = $deliveries[0]['rate'];
                                        $deliveryDuration = $deliveries[0]['duration'];
                                        $code = $deliveries[0]['code'];
                                    }
                                }

                                if (isset($deliveryCharges) && !empty($deliveryCharges)) {
                                    $dtype = explode('_', $code);
                                    CartDeliveryFee::updateOrCreate(['cart_id' => $cart->id, 'vendor_id' => $vendorData->vendor->id], ['delivery_fee' => $deliveryCharges, 'delivery_duration' => $deliveryDuration, 'shipping_delivery_type' => $dtype[0] ?? 'D', 'courier_id' => $dtype[1] ?? '0']);
                                }
                            } //End Check last time stone
                        }
                    }

                    $product = Product::with([
                        'variant' => function ($sel) {
                            $sel->groupBy('product_id');
                        },
                        'variant.media.pimage.image', 'upSell', 'crossSell', 'vendor', 'media.image', 'translation' => function ($q) use ($langId) {
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                            $q->where('language_id', $langId);
                        }
                    ])->select('id', 'sku', 'inquiry_only', 'url_slug', 'weight', 'weight_unit', 'vendor_id', 'has_variant', 'has_inventory', 'averageRating', 'minimum_order_count', 'batch_count', 'service_charges_tax', 'delivery_charges_tax', 'container_charges_tax', 'fixed_fee_tax', 'service_charges_tax_id', 'delivery_charges_tax_id', 'container_charges_tax_id', 'fixed_fee_tax_id')
                        ->where('url_slug', $prod->product->url_slug)
                        ->where('is_live', 1)
                        ->first();

                    $doller_compare = ($customerCurrency) ? $customerCurrency->doller_compare : 1;
                    $up_prods = $this->metaProduct($langId, $doller_compare, 'upSell', ($product->upSell ?? ''));
                    if ($up_prods) {
                        $upSell_products->push($up_prods);
                    }
                    $cross_prods = $this->metaProduct($langId, $doller_compare, 'crossSell', ($product->crossSell ?? ''));
                    if ($cross_prods) {
                        $crossSell_products->push($cross_prods);
                    }
                }

                // $couponGetAmount = $payable_amount ;

                if (isset($vendorData->coupon) && !empty($vendorData->coupon)) {
                    if (isset($vendorData->coupon->promo) && !empty($vendorData->coupon->promo)) {
                        if ($vendorData->coupon->promo->restriction_on == 0 || $vendorData->coupon->promo->restriction_on == 1) {
                            $couponGetAmount = $coupon_product_discount;
                        }
                        if ($vendorData->coupon->promo->first_order_only == 1) {
                            if (Auth::user()) {
                                $userOrder = auth()->user()->orders->first();
                                if ($userOrder) {
                                    $cart->coupon()->delete();
                                    $vendorData->coupon()->delete();
                                    unset($vendorData->coupon);
                                    $PromoDelete = 1;
                                }
                            }
                        }
                        if ($PromoDelete != 1) {
                            if (!($vendorData->coupon->promo->expiry_date >= $nowdate)) {
                                $cart->coupon()->delete();
                                $vendorData->coupon()->delete();
                                unset($vendorData->coupon);
                                $PromoDelete = 1;
                            }
                        }
                        if ($PromoDelete != 1) {

                            $minimum_spend = 0;
                            if (isset($vendorData->coupon->promo->minimum_spend)) {
                                $minimum_spend = $vendorData->coupon->promo->minimum_spend * $doller_compare;
                            }

                            $maximum_spend = 0;
                            if (isset($vendorData->coupon->promo->maximum_spend)) {
                                $maximum_spend = $vendorData->coupon->promo->maximum_spend * $doller_compare;
                            }

                            if (($minimum_spend <= $couponGetAmount) && ($maximum_spend >= $couponGetAmount)) {
                                if ($vendorData->coupon->promo->promo_type_id == 2) {
                                    $total_discount_percent = $vendorData->coupon->promo->amount;

                                    $payable_amount -= $total_discount_percent;
                                    $coupon_amount_used = $total_discount_percent;
                                } else {
                                    $gross_amount = decimal_format($payable_amount - $taxable_amount);
                                    $percentage_amount = ($coupon_apply_price * $vendorData->coupon->promo->amount / 100);
                                    $payable_amount -= $percentage_amount;
                                    $coupon_amount_used = $percentage_amount;
                                }
                            } else {

                                $cart->coupon()->delete();
                                $vendorData->coupon()->delete();
                                unset($vendorData->coupon);
                                $PromoDelete = 1;
                            }
                        }
                        if ($PromoDelete != 1) {
                            if ($vendorData->coupon->promo->allow_free_delivery == 1) {
                                $PromoFreeDeliver = 1;
                                // $coupon_amount_used = $coupon_amount_used ;
                                $coupon_amount_used = $coupon_amount_used +  $deliveryCharges;
                                $payable_amount = $payable_amount - $deliveryCharges;
                            }
                        }
                    }
                }
                $promoCodeController = new PromoCodeController();
                $promoCodeRequest = new Request();
                $promoCodeRequest->setMethod('POST');
                $promoCodeRequest->request->add(['vendor_id' => $vendorData->vendor_id, 'cart_id' => $cart_id, 'amount' => $couponGetAmount, 'is_cart' => 1, 'cart_product_ids' => $cart_product_ids]);
                $promoCodeResponse = $promoCodeController->postPromoCodeList($promoCodeRequest)->getData();
                if ($promoCodeResponse->status == 'Success') {
                    if (!empty($promoCodeResponse->data)) {
                        $is_promo_code_available = 1;
                    }
                }

                // calculate subscription discount
                if ($user_subscription) {
                    foreach ($user_subscription->features as $feature) {
                        if ($feature->feature_id == 1) {
                            $subscription_discount = $subscription_discount + $deliveryCharges;
                        } elseif ($feature->feature_id == 2) {
                            $off_percentage_discount = ($feature->percent_value * $payable_amount / 100);
                            $subscription_discount = $subscription_discount + $off_percentage_discount;
                        }
                    }
                }

                // pr($PromoFreeDeliver);
                // add total delivery fee
                if ($vendorData->vendor->delivery_charges_tax_id)
                    $all_vendor_deliver_charges +=  $deliveryCharges;

                if ($vendorData->vendor->add_markup_price)
                    $all_vendor_markup_charges +=  $totalMarkup;

                $subtotal_amount = $payable_amount;
                // if($PromoFreeDeliver != 1){
                $payable_amount = $payable_amount + $deliveryCharges;
                //}
                //$payable_amount = $payable_amount + $deliver_charge;
                //Start applying service fee on vendor products total

                $slotsDate = findSlot('', $vendorData->vendor->id, '');
                $vendorData->delaySlot = (($slotsDate) ? $slotsDate : '');

                $vendor_service_fee_percentage_amount = 0;
                if ($vendorData->vendor->service_fee_percent > 0) {
                    $amount_for_service = $opt_quantity_price_new + $vendor_products_total_amount;
                    $vendor_service_fee_percentage_amount = (($amount_for_service) * $vendorData->vendor->service_fee_percent) / 100;
                    $payable_amount = $payable_amount + $vendor_service_fee_percentage_amount;
                }
                if ($vendorData->vendor->service_charge_amount > 0) {
                    $amount_for_service = $opt_quantity_price_new + $vendor_products_total_amount;
                    $vendor_service_fee_percentage_amount = $vendorData->vendor->service_charge_amount;
                    $payable_amount = $payable_amount + $vendor_service_fee_percentage_amount;
                }


                //end applying service fee on vendor products total
                $total_service_fee = $total_service_fee + $vendor_service_fee_percentage_amount;
                $vendorData->coupon_amount_used = decimal_format($coupon_amount_used);
                $vendorData->service_fee_percentage_amount = decimal_format($vendor_service_fee_percentage_amount);
                $vendorData->delivery_fee_charges = decimal_format($delivery_fee_charges);
                //$vendorData->delivery_fee_charges_static = decimal_format($delivery_fee_charges_static);;

                $vendorData->payable_amount = decimal_format($payable_amount);
                $vendorData->discount_amount = decimal_format($discount_amount);
                $vendorData->discount_percent = decimal_format($discount_percent);
                $vendorData->taxable_amount = decimal_format($taxable_amount);
                $vendorData->product_total_amount = decimal_format($payable_amount - $taxable_amount);
                $vendorData->product_sub_total_amount = decimal_format($subtotal_amount);
                $vendorData->isDeliverable = 1;
                $vendorData->promo_free_deliver = $PromoFreeDeliver;
                $vendorData->is_vendor_closed = $is_vendor_closed;
                $slotsDate = findSlot('', $vendorData->vendor->id, '');
                $vendorData->delaySlot = (($slotsDate) ? $slotsDate : '');
                $vendorData->closed_store_order_scheduled = (($slotsDate) ? $product->vendor->closed_store_order_scheduled : 0);
                $vendorData->delOptions = $select;

                if (isset($serviceArea)) {
                    if ($serviceArea->isEmpty()) {
                        $vendorData->isDeliverable = 0;
                        $delivery_status = 0;
                    }
                }
                if ($vendorData->vendor->show_slot == 0) {
                    if (($vendorData->vendor->slotDate->isEmpty()) && ($vendorData->vendor->slot->isEmpty())) {
                        $vendorData->is_vendor_closed = 1;
                        if ($delivery_status != 0) {
                            $delivery_status = 0;
                        }
                    } else {
                        $vendorData->is_vendor_closed = 0;
                    }
                }
                if ($vendorData->vendor->$action == 0) {
                    $vendorData->is_vendor_closed = 1;
                    $delivery_status = 0;
                }

                // if ($loyalty_amount_saved > 0) {
                // dd($payable_amount+(float)($cartData[0]->vendor->fixed_fee_amount)-(float)($loyalty_amount_saved)); //36.81
                // }
                if ((float)($vendorData->vendor->order_min_amount) > $payable_amount + (float)($vendorData->vendor->fixed_fee_amount) - (float)($loyalty_amount_saved)) {  # if any vendor total amount of order is less then minimum order amount
                    $delivery_status = 0;
                }

                $total_payable_amount = $total_payable_amount + $payable_amount;
                $total_taxable_amount = $total_taxable_amount + $taxable_amount;
                $total_discount_amount = $total_discount_amount + $discount_amount;
                $total_discount_percent = $total_discount_percent + $discount_percent;
                $total_subscription_discount = $total_subscription_discount + $subscription_discount;

                $vendorData->is_promo_code_available = $is_promo_code_available;
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
                if ($customerCurrency) {
                    $amount_value = $amount_value * $customerCurrency->doller_compare;
                }
                $total_discount_amount = $total_discount_amount + $amount_value;
            }
            if ($total_subscription_discount > 0) {
                $total_discount_amount = $total_discount_amount + $total_subscription_discount;
            }
            $cart->total_subscription_discount = decimal_format($total_subscription_discount ?? 0);

            $fixedFeeAmount = 0.00;
            if (isset($vendorData->vendor->fixed_fee_amount)) {
                $fixedFeeAmount = $vendorData->vendor->fixed_fee_amount;
            }
            $total_payable_amount = $total_payable_amount - $total_discount_amount + $fixedFeeAmount;
            if ($loyalty_amount_saved > 0) {
                if ($loyalty_amount_saved > $total_payable_amount) {
                    $loyalty_amount_saved =  $total_payable_amount;
                }
                $total_payable_amount = $total_payable_amount - $loyalty_amount_saved;
            }
            $wallet_amount_available = 0;
            $wallet_amount_used = 0;
            if ($user) {

                if ($user->balanceFloat > 0) {
                    $wallet_amount_available = $user->balanceFloat;
                    if ($customerCurrency) {
                        $wallet_amount_used = $user->balanceFloat * $customerCurrency->doller_compare;
                    }
                    if ($wallet_amount_used > $total_payable_amount) {
                        $wallet_amount_used = $total_payable_amount;
                    }
                    $total_payable_amount = $total_payable_amount - $wallet_amount_used;
                }
            }
            $cart->wallet_amount_used = decimal_format($wallet_amount_used);


            $scheduled = (object)array(
                'scheduled_date_time' => (($cart->scheduled_slot) ? date('Y-m-d', strtotime($cart->scheduled_date_time)) : $cart->scheduled_date_time), 'slot' => $cart->scheduled_slot,
            );
            $cart->deliver_status = $delivery_status;
            $cart->vendorCnt = $cartData->count();
            $cart->scheduled = $scheduled;
            $cart->schedule_type =  $cart->schedule_type;
            $cart->closed_store_order_scheduled =  0;
            $myDate = date('Y-m-d');
            if ($cart->vendorCnt == 1) {
                $vendorId = $cartData[0]->vendor_id;

                $cart->scheduled->scheduled_date_time = $cartData[0]->scheduled_date_time;
                $cart->scheduled->slot = $cartData[0]->schedule_slot;

                //type must be a : delivery , takeaway,dine_in
                $duration = Vendor::where('id', $vendorId)->select('slot_minutes', 'closed_store_order_scheduled')->first();
                $closed_store_order_scheduled = (($slotsDate) ? $duration->closed_store_order_scheduled : 0);
                if ($cart->deliver_status == 0 && $closed_store_order_scheduled == 1) {
                    $cart->deliver_status = $duration->closed_store_order_scheduled;
                    $cart->closed_store_order_scheduled = $duration->closed_store_order_scheduled;
                    $myDate = date('Y-m-d', strtotime($cart->scheduled_date_time));
                    $sttime =  strtotime($myDate);
                    $todaytime =  strtotime(date('Y-m-d'));
                    if ($todaytime == $sttime) {
                        $sttime =  strtotime('+1 day', $sttime);
                    }
                    $myDate = (($myDate) ? date('Y-m-d', $sttime) : date('Y-m-d', strtotime('+1 day')));
                    $cart->schedule_type =  'schedule';
                    //$cart->closed_store_order_scheduled =  1;
                } else {
                    $cart->closed_store_order_scheduled = $duration->closed_store_order_scheduled;
                }
                if ($preferences->scheduling_with_slots != 1 && $preferences->business_type != 'laundry') {
                    $myDate = $cartData[0]->scheduled_date_time;
                    $slots = (object)showSlot($myDate, $vendorId, 'delivery', $duration->slot_minutes, 0);
                    if (count((array)$slots) == 0) {
                        $myDate  = date('Y-m-d', strtotime('+1 day'));
                        $slots = (object)showSlot($myDate, $vendorId, 'delivery', $duration->slot_minutes, 0);
                    }
                    if (count((array)$slots) == 0) {
                        $myDate  = date('Y-m-d', strtotime('+2 day'));
                        $slots = (object)showSlot($myDate, $vendorId, 'delivery', $duration->slot_minutes, 0);
                    }

                    if (count((array)$slots) == 0) {
                        $myDate  = date('Y-m-d', strtotime('+3 day'));
                        $slots = (object)showSlot($myDate, $vendorId, 'delivery', $duration->slot_minutes, 0);
                    }
                    $cart->slots = $slots;
                    $cart->vendor_id =  $vendorId;
                } else {
                    $cart->slots = [];
                    $cart->vendor_id =  $vendorId;
                    $slots = [];
                }

                $pickupSlots = [];
                $dropoffSlots = [];
                // get slots for laundry category
                if ($preferences->scheduling_with_slots == 1 && $preferences->business_type == 'laundry') {
                    // For Pickup
                    $pickupSlots = (object)showSlot($myDate, $vendorId, 'delivery', $duration->slot_minutes, 1);
                    if (count((array)$pickupSlots) == 0) {
                        $myDate  = date('Y-m-d', strtotime('+1 day'));
                        $pickupSlots = (object)showSlot($myDate, $vendorId, 'delivery', $duration->slot_minutes, 1);
                    }
                    if (count((array)$pickupSlots) == 0) {
                        $myDate  = date('Y-m-d', strtotime('+2 day'));
                        $pickupSlots = (object)showSlot($myDate, $vendorId, 'delivery', $duration->slot_minutes, 1);
                    }
                    if (count((array)$pickupSlots) == 0) {
                        $myDate  = date('Y-m-d', strtotime('+3 day'));
                        $pickupSlots = (object)showSlot($myDate, $vendorId, 'delivery', $duration->slot_minutes, 1);
                    }

                    // For Dropoff
                    $myDropoffDate = date('Y-m-d');
                    $dropoffSlots = (object)showSlot($myDropoffDate, $vendorId, 'delivery', $duration->slot_minutes, 2);
                    if (count((array)$dropoffSlots) == 0) {
                        $myDropoffDate  = date('Y-m-d', strtotime('+1 day'));
                        $dropoffSlots = (object)showSlot($myDropoffDate, $vendorId, 'delivery', $duration->slot_minutes, 2);
                    }
                    if (count((array)$dropoffSlots) == 0) {
                        $myDropoffDate  = date('Y-m-d', strtotime('+2 day'));
                        $dropoffSlots = (object)showSlot($myDropoffDate, $vendorId, 'delivery', $duration->slot_minutes, 2);
                    }
                    if (count((array)$dropoffSlots) == 0) {
                        $myDropoffDate  = date('Y-m-d', strtotime('+3 day'));
                        $dropoffSlots = (object)showSlot($myDropoffDate, $vendorId, 'delivery', $duration->slot_minutes, 2);
                    }

                    $cart->slotsForPickup = $pickupSlots;
                    $cart->slotsForDropoff  = $dropoffSlots;
                    $cart->vendor_id = $vendorId;
                }
            } else {
                $slots = [];
                $cart->slots = [];
                $cart->slotsForPickup = [];
                $cart->slotsForDropoff = [];
                $cart->vendor_id =  0;
                $pickupSlots = [];
                $dropoffSlots = [];
            }
            $cart->without_category_kyc = 0;

            if ($preferences->category_kyc_documents == 1 && $user) {

                $category_query =  CategoryKycDocuments::whereHas('categoryMapping', function ($q) use ($category_array) {
                    $q->whereIn('category_id', $category_array);
                });

                $category_kyc_document_ids =  $category_query->pluck('id');
                //     $category_kyc_category_ids =  $category_query->categoryMapping->pluck('category_id');
                // pr( $category_query->first()->toArray());
                $category_kyc_document_ids = $category_kyc_document_ids->isNotEmpty() ? $category_kyc_document_ids->toArray() : [];


                $category_kyc_count =  $category_query->count();

                $is_alrady_submit = CaregoryKycDoc::whereIn('category_kyc_document_id', $category_kyc_document_ids)->where('cart_id', $cart_id)->count();
                //     echo  $is_alrady_submit." <br>";
                //    pr($category_kyc_document_ids);
                $cart->category_kyc_count = 0;
                if ($category_kyc_count  > 0 && ($is_alrady_submit  !=  $category_kyc_count)) {
                    $cart->category_kyc_count = $category_kyc_count;
                    $cart->category_rendem_id = rand(9, 10);
                    $cart->category_ids = implode(',', $category_array);
                }

                $ALLcategory_kyc_documents = CategoryKycDocuments::whereHas('categoryMapping', function ($q) use ($category_array) {
                    $q->whereIn('category_id', $category_array);
                })->with('primary')->get();
                foreach ($ALLcategory_kyc_documents as $vendor_registration_document) {
                    if ($vendor_registration_document->is_required == 1) {

                        $check = CaregoryKycDoc::where(['cart_id' => $cart_id, 'category_kyc_document_id' => $vendor_registration_document->id])->first();
                        if ($check) {
                            $cart->without_category_kyc = 1;
                        }
                    }
                }
            } else {
                $cart->without_category_kyc = 1;
            }


            // echo "Total_payable_amount: ".$total_payable_amount."total_discount_amount: ". $total_discount_amount."loyalty_amount_saved". $loyalty_amount_saved ."wallet_amount_used".$wallet_amount_used."total_taxable_amount".$total_taxable_amount;
            // Total_payable_amount: 695.6total_discount_amount: 97.1loyalty_amount_saved83.4wallet_amount_used0total_taxable_amount102
            //pr($total_payable_amount);
            $cart->slotsCnt = count((array)$slots);
            $cart->pickupSlotsCnt = count((array)$pickupSlots);
            $cart->dropoffSlotsCnt = count((array)$dropoffSlots);
            $cart->total_service_fee = decimal_format($total_service_fee);
            $cart->loyalty_amount = decimal_format($loyalty_amount_saved);
            $cart->gross_amount = decimal_format($total_payable_amount + $total_discount_amount + $loyalty_amount_saved + $wallet_amount_used - $total_taxable_amount);
            $cart->new_gross_amount = decimal_format($total_payable_amount + $total_discount_amount);
            $cart->total_payable_amount = decimal_format($total_payable_amount);
            $cart->delivery_charges = decimal_format($deliveryCharges);


            $cart->all_vendor_deliver_charges = decimal_format($all_vendor_deliver_charges);
            $cart->all_vendor_markup_charges = decimal_format($all_vendor_markup_charges);
            $cart->total_discount_amount = decimal_format($total_discount_amount);
            $cart->total_taxable_amount = decimal_format($total_taxable_amount);
            $total_payable_amount_calc_tip = $total_payable_amount - $total_taxable_amount;
            $cart->tip_5_percent = decimal_format(0.05 * $total_payable_amount_calc_tip);
            $cart->tip_10_percent = decimal_format(0.10 * $total_payable_amount_calc_tip);
            $cart->tip_15_percent = decimal_format(0.15 * $total_payable_amount_calc_tip);
            $cart->total_container_charges = decimal_format($total_container_charges);
            $cart->wallet_amount_available = decimal_format($wallet_amount_available);
            $cart->taxRates = $taxRates;
            $cart->action = $action;
            $cart->totalQuantity = $total_quantity;
            $cart->user_allAddresses = $user_allAddresses ?? [];
            $cart->guest_user = $guest_user ?? 0;
            $cart->left_section = view('frontend.cartnew-left')->with(['action' => $action,  'vendor_details' => $vendor_details, 'addresses' => $user_allAddresses, 'countries' => $countries, 'cart_dinein_table_id' => $cart_dinein_table_id, 'preferences' => $preferences])->render();
            $cart->upSell_products = ($upSell_products) ? $upSell_products->first() : collect();
            $cart->crossSell_products = ($crossSell_products) ? $crossSell_products->first() : collect();
            $cart->scheduled_date_time = $myDate;

            if ($preferences->scheduling_with_slots == 1 && $preferences->business_type == 'laundry') {
                if ($cart->pickupSlotsCnt == 0) {
                    $mdate = (object)findSlotNew('', $cart->vendor_id, 1);
                    $cart->delay_date =  $mdate->mydate;
                } else {
                    $cart->delay_date =  $myDate ?? $delay_date;
                }

                if ($cart->dropoffSlotsCnt == 0) {
                    $mdate = (object)findSlotNew('', $cart->vendor_id, 2);
                    $cart->my_dropoff_delay_date =  $mdate->mydate;
                } else {
                    $cart->my_dropoff_delay_date =  $myDropoffDate ?? $delay_date;
                }
            } else {
                if ($cart->slotsCnt == 0) {
                    $mdate = (object)findSlotNew('', $cart->vendor_id, 0);
                    $cart->delay_date =  $mdate->mydate;
                } else {
                    $cart->delay_date =  $myDate ?? $delay_date;
                }
            }

            if ($preferences->same_day_delivery_for_schedule == 0) {
                if ($cart->my_dropoff_delay_date == date('Y-m-d')) {
                    $cart->my_dropoff_delay_date = date('Y-m-d', strtotime('+1 day'));
                }

                if ($cart->delay_date == date('Y-m-d')) {
                    $cart->delay_date = date('Y-m-d', strtotime('+1 day'));
                }
            }

            $cart->pickup_delay_date =  $pickup_delay_date ?? 0;
            $cart->dropoff_delay_date =  $dropoff_delay_date ?? 0;
            $cart->delivery_type =  $code ?? 'D';
            $cart->sub_total =  $sub_total ?? 0;

            $cart->products = $cartData->toArray();
        }

           
        return $cart;
    }

    public function checkScheduleSlots(Request $request)
    {
        $message = '';
        $status = 'Success';
        $vendorId = $request->vendor_id;
        $delivery = $request->delivery ?? 'delivery';
        $option = "";
        //type must be a : delivery , takeaway,dine_in
        $duration = Vendor::where('id', $vendorId)->select('slot_minutes')->first();
        $slots = (object)showSlot($request->date, $vendorId, $delivery, $duration->slot_minutes, 0);
        $option = "<option value=''>" . __("Select Slot") . "</option>";
        if (count((array)$slots) <= 0) {
            $message = 'Slot not found.';
            $status = 'error';
        } else {
            foreach ($slots as $opt) {
                $option .= "<option value='" . $opt['value'] . "'>" . $opt['name'] . "</option>";
            }
        }
        $data = array('status' => $status, 'data' => $option, 'message' => $message);
        return response()->json($data);
    }

    public function checkPickupScheduleSlots(Request $request)
    {
        $message = '';
        $status = 'Success';
        $vendorId = $request->vendor_id ?? 0;
        $option = "";
        //type must be a : delivery , takeaway,dine_in
        $duration = Vendor::where('id', $vendorId)->select('slot_minutes')->first();
        $slots = (object)showSlot($request->date, $vendorId, 'delivery', $duration->slot_minutes, 1);
        $option = "<option value=''>" . __("Select Slot") . "</option>";
        if (count((array)$slots) <= 0) {
            $message = 'Slot not found.';
            $status = 'error';
        } else {
            foreach ($slots as $opt) {
                $option .= "<option value='" . $opt['value'] . "'>" . $opt['name'] . "</option>";
            }
        }
        $data = array('status' => $status, 'data' => $option, 'message' => $message);
        return response()->json($data);
    }

    public function checkDropoffScheduleSlots(Request $request)
    {
        $message = '';
        $status = 'Success';
        $vendorId = $request->vendor_id ?? 0;
        $option = "";
        //type must be a : delivery , takeaway,dine_in
        $duration = Vendor::where('id', $vendorId)->select('slot_minutes')->first();

        $slots = (object)showSlot($request->date, $vendorId, 'delivery', $duration->slot_minutes, 2);
        $option = "<option value=''>" . __("Select Slot") . "</option>";
        if (count((array)$slots) <= 0) {
            $message = 'Slot not found.';
            $status = 'error';
        } else {
            foreach ($slots as $opt) {
                $option .= "<option value='" . $opt['value'] . "'>" . $opt['name'] . "</option>";
            }
        }
        $data = array('status' => $status, 'data' => $option, 'message' => $message);
        return response()->json($data);
    }

    /**
     * Show Main Cart
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Get Last added product variant
     *
     * @return \Illuminate\Http\Response
     */
    public function getLastAddedProductVariant(Request $request, $domain = '')
    {
        try {
            $cartProduct = CartProduct::with('addon')
                ->where('cart_id', $request->cart_id)
                ->where('product_id', $request->product_id)
                ->orderByDesc('created_at')->first();

            return $this->successResponse($cartProduct, '', 200);
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Get current product variants with different addons
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductVariantWithDifferentAddons(Request $request, $domain = '')
    {
        try {
            $langId = Session::get('customerLanguage');
            $cur_ids = Session::get('customerCurrency');
            if (isset($cur_ids) && !empty($cur_ids)) {
                $clientCurrency = ClientCurrency::where('currency_id', '=', $cur_ids)->first();
            } else {
                $clientCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
            }

            $cartProducts = CartProduct::with([
                'product.translation' => function ($qry) use ($langId) {
                    $qry->where('language_id', $langId)->groupBy('product_translations.language_id');
                },
                //  'addon.option.translation' => function ($qry) use ($langId) {
                //     $qry->where('language_id', $langId)->groupBy('addon_option_translations.language_id');
                // }, 'addon.set' => function ($qry) use ($langId) {
                //     $qry->where('language_id', $langId)->groupBy('addon_sets.id');
                // },
                'product.media.image',
                'pvariant.media.pimage.image',
                'vendor.slot.day', 'vendor.slotDate',
            ])
                ->where('cart_id', $request->cart_id)
                ->where('product_id', $request->product_id)
                ->select('*', 'id as add_on_set_and_option')->orderByDesc('created_at')->get();

            $multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
            foreach ($cartProducts as $key => $cart) {
                $cart->is_vendor_closed = 0;
                $cart->variant_multiplier = $multiplier;
                $variant_price = ($cart->pvariant) ? ($cart->pvariant->price * $multiplier) : 0;

                $product = $cart->product;
                $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;

                if ($cart->pvariant && $cart->pvariant->media->isNotEmpty()) {
                    $image_fit = $cart->pvariant->media->first()->pimage->image->path['image_fit'];
                    $image_path = $cart->pvariant->media->first()->pimage->image->path['image_path'];
                    $product->product_image = $image_fit . '300/300' . $image_path;
                } elseif ($product->media->isNotEmpty()) {
                    $image_fit = $product->media->first()->image->path['image_fit'];
                    $image_path = $product->media->first()->image->path['image_path'];
                    $product->product_image = $image_fit . '300/300' . $image_path;
                } else {
                    $product->product_image = $this->loadDefaultImage();
                }

                $addon_set = $cart->add_on_set_and_option;
                foreach ($addon_set as $skey => $set) {
                    $set->addon_set_translation_title = ($set->translation->isNotEmpty()) ? $set->translation->first()->title : $set->title;
                    foreach ($set->options as $okey => $option) {
                        $option->option_translation_title = ($option->translation->isNotEmpty()) ? $option->translation->first()->title : $option->title;
                        $opt_price_in_doller_compare = $option->price * $multiplier;
                        $variant_price = $variant_price + $opt_price_in_doller_compare;
                    }
                }
                $cart->variant_price = $variant_price;
                $cart->addon_set = $addon_set;
                $cart->total_variant_price = decimal_format($cart->quantity * $variant_price);

                if ($cart->vendor->show_slot == 0) {
                    if (($cart->vendor->slotDate->isEmpty()) && ($cart->vendor->slot->isEmpty())) {
                        $cart->is_vendor_closed = 1;
                    } else {
                        $cart->is_vendor_closed = 0;
                    }
                }
                unset($cartProducts[$key]->add_on_set_and_option);
            }

            $returnHTML = view('frontend.product-with-different-addons-modal')->with(['cartProducts' => $cartProducts])->render();
            return $this->successResponse($returnHTML, '', 200);
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Update Quantityt
     *
     * @return \Illuminate\Http\Response
     */
    public function updateQuantity($domain = '', Request $request)
    {
        $cartProduct = CartProduct::find($request->cartproduct_id);
        $variant_id = $cartProduct->variant_id;
        $productDetail = Product::with([
            'variant' => function ($sel) use ($variant_id) {
                $sel->where('id', $variant_id);
                $sel->groupBy('product_id');
            }
        ])->find($cartProduct->product_id);
        $message= 'Only '.$productDetail->variant[0]->quantity.' is available for this product';
        if( ($productDetail->category->categoryDetail->type_id != 8) && ($productDetail->has_inventory == 1)  && ($productDetail->sell_when_out_of_stock == 0) ){
            if($productDetail->variant[0]->quantity < $request->quantity){
                return response()->json(['status' => 'error', 'quantity' => $productDetail->variant[0]->quantity, 'message' => $message]);
            }
        }

        $cartProduct->quantity = $request->quantity;
        $cartProduct->save();

        return response()->json("Successfully Updated");
    }

    /**
     * Update Cart Product Checked Status
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCartProductStatus($domain = '', Request $request)
    {
        $cartProduct = CartProduct::find($request->cartproduct_id);
        $cartProduct->is_cart_checked = $request->is_cart_checked;
        $cartProduct->save();
        return response()->json("Successfully Updated");
    }

    /**
     * Delete Cart Product
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteCartProduct($domain = '', Request $request)
    {
       
        $cartProd =  CartProduct::where('id', $request->cartproduct_id)->select('cart_id', 'vendor_id', 'bid_number')->first();
     
       
        if ($cartProd->bid_number) {
            CartProduct::where('vendor_id', $cartProd->vendor_id)->update(['bid_number' => null, 'bid_discount' => null]);
        }
        CartProduct::where('id', $request->cartproduct_id)->delete();
        CartCoupon::where('vendor_id', $request->vendor_id)->delete();
        CartAddon::where('cart_product_id', $request->cartproduct_id)->delete();
        CartRentalProtection::where('cart_id', $cartProd->cart_id)->delete();
        CartBookingOption::where('cart_id', $cartProd->cart_id)->delete();
        CartDeliveryFee::where('cart_id',$cartProd->cart_id)->where('vendor_id',$cartProd->vendor_id)->delete();
      
       
        if (!empty($cartProd)) {

            $cartpro_count = CartProduct::where('cart_id', $cartProd->cart_id)->count();
            if ($cartpro_count == 0) {
                Cart::where('id', $cartProd->cart_id)->update([
                    'schedule_type' => null, 'scheduled_date_time' => null,
                    'comment_for_pickup_driver' => null, 'comment_for_dropoff_driver' => null, 'comment_for_vendor' => null, 'schedule_pickup' => null, 'schedule_dropoff' => null, 'specific_instructions' => null, 'order_id' => NULL
                ]);
            }
        }

        return response()->json(['status' => 'success', 'message' => __('Product removed from cart successfully.')]);
    }

    /**
     * Empty Cart
     *
     * @return \Illuminate\Http\Response
     */
    public function emptyCartData($domain = '', Request $request)
    {
        $cart_id = $request->cart_id;
        if (($cart_id != '') && ($cart_id > 0)) {
            // Cart::where('id', $cart_id)->delete();
            CartProduct::where('cart_id', $cart_id)->delete();
            CartCoupon::where('cart_id', $cart_id)->delete();
            CartAddon::where('cart_id', $cart_id)->delete();


            return response()->json(['status' => 'success', 'message' => 'Cart has been deleted successfully.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Cart cannot be deleted.']);
        }
    }


    public function repeatOrder($domain = '', Request $request)
    {

        $order_vendor_id = $request->order_vendor_id;
        $cart_id = $request->cart_id;
        $getallproduct = OrderProduct::where('order_vendor_id', $order_vendor_id)->get();

        if (isset($cart_id) && !empty($cart_id)) {
            CartProduct::where('cart_id', $cart_id)->delete();
            CartCoupon::where('cart_id', $cart_id)->delete();
            CartAddon::where('cart_id', $cart_id)->delete();
        }




        foreach ($getallproduct as $data) {
            $request->vendor_id = $data->vendor_id;
            $request->product_id = $data->product_id;
            $request->quantity = $data->quantity;
            $request->variant_id = $data->variant_id;

            $addonID = OrderProductAddon::where('order_product_id', $data->id)->pluck('addon_id');
            $addonoptID = OrderProductAddon::where('order_product_id', $data->id)->pluck('option_id');

            if (count($addonID))
                $request->request->add(['addonID' => $addonID->toArray()]);

            if (count($addonoptID))
                $request->request->add(['addonoptID' => $addonoptID->toArray()]);

            $this->postAddToCart($request);
        }

        return response()->json(['status' => 'success', 'message' => 'Order added to cart.', 'cart_url' => route('showCart')]);
    }

    /**
     * Delete Cart Product
     *
     * @return \Illuminate\Http\Response
     */
    public function getCartData($domain = '', Request $request)
    {
        try
        {
        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role', 'order_edit_before_hours', 'is_gift_card', 'is_token_currency_enable', 'is_service_product_price_from_dispatch', 'token_currency', 'advance_booking_amount', 'advance_booking_amount_percentage', 'is_file_cart_instructions', 'is_service_price_selection', 'is_rental_weekly_monthly_price', 'cart_cms_page_status']);

        $wishListCount = 0;
        $cart_details = null;
        $user = Auth::user();
        $curId = Session::get('customerCurrency');
        $langId = Session::get('customerLanguage') ?? 1;
        $client_timezone = DB::table('clients')->first('timezone');
        $timezone = $client_timezone->timezone ?? ($user ?  ($user->timezone ?? 'Asia/Kolkata') : 'Asia/Kolkata');
        $address_id = 0;
        $schedule_datetime_del = '';
        if ($user) {
            $cart = Cart::where('status', '0')->where('user_id', $user->id);
            if ($getAdditionalPreference['is_gift_card'] == 1) {

                $cart =  $cart->select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time', 'schedule_pickup', 'schedule_dropoff', 'scheduled_slot', 'shipping_delivery_type', 'gift_card_id', 'order_id', 'address_id')->with('giftCard');
            } else {

                $cart = $cart->select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time', 'schedule_pickup', 'schedule_dropoff', 'scheduled_slot', 'shipping_delivery_type', 'order_id', 'address_id');
            }

            $cart = $cart->with(['coupon.promo', 'editingOrder'])->first();

            // pr($cart->toArray());
            $wishListCount =  UserWishlist::where('user_id', $user->id)->count('id');
          
            $uploaded_documents = ClientDocument::where('client_id',Auth::user()->id)->whereNull('order_id')->where('cart_id',$cart->id)->get();
        } else {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time', 'schedule_pickup', 'schedule_dropoff', 'scheduled_slot', 'shipping_delivery_type', 'order_id', 'address_id')->with(['coupon.promo', 'editingOrder'])->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
        }
        if ($cart && !empty($cart)) {
            $cart_product_removed =    CartProduct::where('cart_id', $cart->id)->whereHas('product', function ($q) {
                $q->whereIn('is_live', [0, 2]);
            })->pluck('id');


        if (count($cart_product_removed)) {
            CartProduct::whereIn('id', $cart_product_removed)->delete();
            if (CartProduct::where('cart_id', $cart->id)->count() == 0) {
                Cart::find($cart->id)->delete();
            }
        }
        }
        if($cart && !empty($cart)){
        $cart_product_removed =    CartProduct::where('cart_id',$cart->id)->whereHas('product',function($q){
            $q->whereIn('is_live',[0,2]);
        })->pluck('id');

        if(count($cart_product_removed)){
            CartProduct::whereIn('id',$cart_product_removed)->delete();
        if(CartProduct::where('cart_id',$cart->id)->count() == 0){
        Cart::find($cart->id)->delete();
        }
    }


        }
        $address_id = $request->has("address_id") ? $request->address_id : (  @$cart->address_id ?? '') ;
        if (isset( $address_id) && !empty( $address_id) && !empty($user)) {
           // $address_id $address_id = $request->address_id;
            $address = UserAddress::where('user_id', $user->id)->update(['is_primary' => 0]);
            $address = UserAddress::where('user_id', $user->id)->where('id', $address_id)->update(['is_primary' => 1]);
        }else if($user){
            $address_id = UserAddress::where('user_id', $user->id)->where('is_primary', 1)->value('id')??null;
            // $address_id = $address->id??null;
        }

        if (isset($cart->editingOrder) && !empty($cart->editingOrder)) {
            $schedule_date_delivery_edit = Carbon::parse($cart->editingOrder->scheduled_date_time)->timezone($timezone)->format('Y-m-d H:i:s');
            $schedule_slots_edit = $cart->editingOrder->scheduled_slot;
            $editlimit_datetime = Carbon::now()->toDateTimeString();
            $order_edit_before_hours = $getAdditionalPreference['order_edit_before_hours'];
            $editlimit_datetime = Carbon::now()->addHours($order_edit_before_hours)->toDateTimeString();
            $error_message = '';
            if ((strtotime($cart->editingOrder->scheduled_date_time) - strtotime($editlimit_datetime)) < 0) {
                $error_message = __("Order can only be edited before Time limit of " . $order_edit_before_hours . " Hours from Scheduled date. Please discard order editing.");
            }
            $VendorOrderStatus = VendorOrderStatus::where('order_id', $cart->editingOrder->id)->whereNotIn('order_status_option_id', [1, 2])->count();
            if ($VendorOrderStatus > 0) {
                $error_message = __("You can not edit this order. Either order is in processed or in processing. Please discard order editing.");
            }
        } else {
            $schedule_date_delivery_edit = '';
            $schedule_slots_edit = '';
            $error_message = '';
        }



        if (isset($request->schedule_date_delivery) && !empty($request->schedule_date_delivery)) {
            $schedule_datetime_del = Carbon::parse($request->schedule_date_delivery)->format('Y-m-d H:i:s');
        } else {
            $schedule_datetime_del = Carbon::now()->timezone($timezone)->format('Y-m-d H:i:s');
        }


        if ($cart) {
            //$cart_details = $this->getCartsNew($cart, $address_id, $request->code, $schedule_datetime_del);
            //v2 trait
            $obj = [
                'cart' => $cart,
                'code' => $request->code,
                'address_id' => $address_id,
                'currency' => $curId,
                'schedule_datetime_del' => $schedule_datetime_del,
                'requestType' => 1,
                'type' => $request->type,
                'language' => $request->language
            ];
            $cart_details = $this->getCartsNewV2($obj, $request);
        }
        if(!empty($cart_details->error_message)){
            $error_message = $cart_details->error_message;
        }
        // pr($cart_details);

        $client_preference_detail = ClientPreference::first();
        $client_preference_detail  = $this->hideSecretKeys($client_preference_detail);

        $expected_vendors = [];
        //    $expected_vendors = $this->searchProductExpection($cart_details);
        $expected_vendor_html = '';
        // if(count($expected_vendors))
        // {
        //     $clientCurrency = ClientCurrency::where('currency_id', $curId)->first();

        //     $expected_vendor_html = view('frontend.modals.expected_vendor_pricing')->with(['expected_vendors'=>$expected_vendors,'clientCurrency' => $clientCurrency])->render();
        // }
        if ($cart_details) {
            $nomenclature = Nomenclature::where('label', 'Product Order Form')->first();
            $nomenclatureProductOrderForm = "Product Order Form";
            if (!empty($nomenclature)) {
                $nomenclatureTranslation = NomenclatureTranslation::where(['nomenclature_id' => $nomenclature->id, 'language_id' => $langId])->first();
                if ($nomenclatureTranslation) {
                    $nomenclatureProductOrderForm = $nomenclatureTranslation->name ?? null;
                }
            }

            $currency_code = "USD";
            $conversion_rate = 0;
            if (!empty(ClientCurrency::where('currency_id', 147)->first()->doller_compare)) {
                $conversion_rate = (float)ClientCurrency::where('currency_id', 147)->first()->doller_compare;
            }
            if (!empty(ClientCurrency::where('currency_id', $curId)->first()->doller_compare)) {
                $conversion_rate = (float)ClientCurrency::where('currency_id', $curId)->first()->doller_compare;
            }
            $cart_details->conversion_rate = $conversion_rate;
            
            $cmsPages = Page::with(['translation' => function ($q) use ($langId) {
                $q->where('language_id', $langId);
            }])->whereIn('slug', ['terms-conditions', 'refund-policy'])->get();

            $currency = ClientCurrency::with('currency')->where('is_primary', 1)->first();
            if (!empty($currency->currency->iso_code)) {
                $currency_code = $currency->currency->iso_code;
            }
            $cart_details->currency_code = $currency_code;
          
            $action = (Session::has('vendorType')) ? Session::get('vendorType') : 'delivery';
            if ($action == 'car_rental') {
                $addon = AddonSet::with('option', 'translation')->where('vendor_id', $cart_details->vendor_id)->where('status', 1)->get();

                $mycartView = view('frontend.yacht.cart-page')->with(['cart_details' => (($cart_details) ? json_decode($cart_details) : []), 'nomenclatureProductOrderForm' => $nomenclatureProductOrderForm, 'getAdditionalPreference' => $getAdditionalPreference, 'edit_order_schedule_datetime' => $schedule_date_delivery_edit, 'schedule_slots_edit' => $schedule_slots_edit, 'cart_error_message' => $error_message, 'addons' => $addon, 'cmsPages' => $cmsPages])->render();
            } else {

                
                $mycartView = view('frontend.cart-page')->with(['cart_details' => (($cart_details) ? json_decode($cart_details) : []), 'nomenclatureProductOrderForm' => $nomenclatureProductOrderForm, 'getAdditionalPreference' => $getAdditionalPreference, 'edit_order_schedule_datetime' => $schedule_date_delivery_edit, 'schedule_slots_edit' => $schedule_slots_edit, 'cart_error_message' => $error_message, 'cmsPages' => $cmsPages,'uploaded_documents'=>$uploaded_documents ?? null])->render();
            }

        }


        $tokenAmount = 1;
        $is_token_enable = @$getAdditionalPreference['is_token_currency_enable'];
        if ($is_token_enable && $cart_details) {
            $tokenAmount = getJsToken();
            $cart_details->is_token_enable = $is_token_enable;
            $cart_details->tokenAmount = $tokenAmount;
        }


        // till here
        return response()->json(['loggedIn' => Auth::check() ? 'true' : 'false', 'status' => 'success', 'schedule_datetime' => $request->schedule_date_delivery, 'cart_details' => $cart_details, 'expected_vendor_html' => $expected_vendor_html, 'expected_vendors' => $expected_vendors, 'client_preference_detail' => $client_preference_detail, 'mycart' => $mycartView ?? '', 'cart_error_message' => $error_message, 'wishListCount' => $wishListCount]); //'token_val' => $tokenAmount , 'is_token_enable' => $is_token_enable

    }catch(\Exception $e)
    {
        \Log::info($e->getLine().'--'.$e->getMessage());
        return response()->json([]);
    }
    }


    public function searchProductExpection($cart_details)
    {



        $langId = Session::get('customerLanguage');

        $all_vendors = array();
        $keywords = array();
        foreach ($cart_details->products as $product) {
            foreach ($product['vendor_products'] as $vendor_product) {
                $keywords[] = isset($vendor_product['product']['translation_one']) ? $vendor_product['product']['translation_one']['title'] :  $vendor_product['product']['sku'];
            }
        }

        $all_vendors = Vendor::OrderBy('id', 'desc')->with(['products' => function ($q) use ($langId, $keywords) {
            $q->whereHas(
                'translation',
                function ($q) use ($langId, $keywords) {
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId)->whereIn('title', $keywords);
                }
            )->with('media.image', 'variant');
        }])->whereHas(
            'products.translation',
            function ($q) use ($langId, $keywords) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId)->whereIn('title', $keywords);
            }
        )->where('status', 1)->get();


        return $all_vendors;
    }

    /***
     * Fetch all delivery fee option
     * totalRoute = number to total route witch we have send to dispatcher
     */


    public function getDeliveryOptions($vendorData, $preferences, $payable_amount, $address, $schedule_datetime_del = '', $dispatcher_tags = '', $totalRoute = '1')
    {
        $option = array();
        $delivery_count = 0;
        try {
            if ($vendorData->vendor_id) {

                Session()->put('vid', $vendorData->vendor_id);

                $getAdditionalPreference = getAdditionalPreference(['is_free_delivery_by_roles']);
                $skip_delivery_fees = false;
                if ($getAdditionalPreference['is_free_delivery_by_roles'] == 1) {
                    $product_id = $vendorData->vendorProducts[0]['product_id'];
                    $result = ProductDeliveryFeeByRole::where('product_id', $product_id)->where('role_id', Auth::user()->role_id)
                        ->where('is_free_delivery', 1)->first();
                    if ($result != null) {
                        $skip_delivery_fees = true;
                    }
                }
                if ($skip_delivery_fees == true) {
                    // skip
                } else if ($preferences->static_delivey_fee != 1) {

                    //Dispatcher Delivery changes and estimated delivery duration code
                    $deliver_response_array = $this->getDeliveryFeeDispatcher($vendorData->vendor_id, $schedule_datetime_del, $dispatcher_tags);
                    if (!empty($deliver_response_array[0])) {
                        $deliver_charge = (!empty($deliver_response_array[0]['delivery_fee'])) ? number_format(($deliver_response_array[0]['delivery_fee'] * $totalRoute), 2, '.', '') : '0.00';
                        $delivery_duration = (!empty($deliver_response_array[0]['total_duration'])) ? number_format($deliver_response_array[0]['total_duration'], 0, '.', '') : '0.00';
                        if ($deliver_charge > 0) {
                            $option[] = array(
                            'type'=>'D',
                            'courier_name'=>__('Dispatcher'),
                            'rate' => $deliver_charge,
                            'courier_company_id' => 0,
                            'etd' => 0,
                            'duration' => $delivery_duration,
                            'etd_hours' => 0,
                            'estimated_delivery_days' => 0,
                            'code' => 'D_0'
                        );
                    }
                    }


                     //Borzoe Delivery changes code
                     $borzoe_deliver_fee = $this->borzoeDelivery($vendorData->vendor_id);
                     if(!empty($borzoe_deliver_fee)){
                     $deliverFee = json_decode($borzoe_deliver_fee);
                     
                     $borzoe_deliver_fee = $deliverFee->order->payment_amount;
                     if ($borzoe_deliver_fee > 0) {
                         $borzoe_deliver_fee = decimal_format($borzoe_deliver_fee);
                         $optionBorzoeApi[] = array(
                             'type' => 'B',
                             'courier_name' => __('Borzoe'),
                             'rate' => $borzoe_deliver_fee,
 
                             'courier_company_id' => 0,
                             'etd' => 0,
                             'etd_hours' => 0,
                             'duration' => 0,
                             'estimated_delivery_days' => 0,
                             'code' => 'B_0'
                         );
                         $option = array_merge($option, $optionBorzoeApi);
                     }
                    }
                     //End Borzoe Delivery changes code


                    //Kwik Delivery changes code
                    $kwick = new QuickApiController();
                    $deliver_fee = $kwick->getDeliveryFeeKwikApi($vendorData->vendor_id);
                    if ($deliver_fee > 0) {
                        $deliver_fee = decimal_format($deliver_fee);

                        $optionKwikApi[] = array(
                            'type' => 'K',
                            'courier_name' => __('KwikApi'),
                            'rate' => $deliver_fee,

                            'courier_company_id' => 0,
                            'etd' => 0,
                            'etd_hours' => 0,
                            'duration' => 0,
                            'estimated_delivery_days' => 0,
                            'code' => 'K_0'
                        );
                        $option = array_merge($option, $optionKwikApi);
                    }
                    //End Kwik Delivery changes code

                    //Lalamove Delivery changes code
                    $lalamove = new LalaMovesController();
                    $deliver_lalmove_fee = $lalamove->getDeliveryFeeLalamove($vendorData->vendor_id);
                    if ($deliver_lalmove_fee > 0) {
                        $deliver_charge_lalamove = decimal_format($deliver_lalmove_fee);

                        $optionLala[] = array(
                            'type' => 'L',
                            'courier_name' => __('Lalamove'),
                            'rate' => $deliver_charge_lalamove,

                            'courier_company_id' => 0,
                            'etd' => 0,
                            'etd_hours' => 0,
                            'duration' => 0,
                            'estimated_delivery_days' => 0,
                            'code' => 'L_0'
                        );
                        $option = array_merge($option, $optionLala);
                    }

                    $d4bdunzo = new D4BDunzoController();
                    // dd($vendorData->vendor_id);
                    $deliver_d4bdunzo_data= $d4bdunzo->quote($vendorData->vendor_id);
                    // dd($deliver_d4bdunzo_data);
                    if($deliver_d4bdunzo_data['estimated_price']>0)
                    {
                        $deliver_charge_d4bdunzo = decimal_format($deliver_d4bdunzo_data['estimated_price']);
                        $optionD4Dunzo[] = array(
                            'type'=>'D4',
                            'courier_name'=>__('D4B Dunzo'),
                            'rate' => $deliver_charge_d4bdunzo,
                            'duration' => $deliver_d4bdunzo_data['eta']['pickup'] +  $deliver_d4bdunzo_data['eta']['dropoff'],
                            'courier_company_id' => 0,
                            'etd' => 0,
                            'etd_hours' => 0,
                            'estimated_delivery_days' => 0,
                            'code' => 'D4_0'
                        );
                        $option = array_merge($option,$optionD4Dunzo);
                    }
                    //End Lalamove Delivery changes code

                    if ($vendorData->vendor->pincode) {
                        //get Shippo Services Delivery changes code
                        $shipo = new ShippoController();
                        $deliver_shipo_fee = $shipo->getServices($vendorData->vendor_id);
                        if ($deliver_shipo_fee) {
                            $option = array_merge($option, $deliver_shipo_fee);
                        }
                    }


                    if ($vendorData->vendor->shiprocket_pickup_name) {
                        //getShiprocketFee Delivery changes code
                        $ship = new ShiprocketController();
                        $deliver_ship_fee = $ship->getCourierService($vendorData->vendor_id);
                        if ($deliver_ship_fee) {
                            $option = array_merge($option, $deliver_ship_fee);
                        }
                    }

                    //getDunzo Delivery fee changes code
                    $dunzo = new DunzoController();
                    if ($dunzo->status) {
                        $deliver_dunzo_fee = $dunzo->getQuotations($vendorData->vendor_id, $address);
                        if ($deliver_dunzo_fee > 0) {
                            $deliver_charge_dunzo = decimal_format($deliver_dunzo_fee);
                            $optionDunzo[] = array(
                                'type' => 'DU',
                                'courier_name' => __('Dunzo'),
                                'rate' => $deliver_charge_dunzo,

                                'courier_company_id' => 0,
                                'etd' => 0,
                                'etd_hours' => 0,
                                'duration' => 0,
                                'estimated_delivery_days' => 0,
                                'code' => 'DU_0'
                            );
                            $option = array_merge($option, $optionDunzo);
                        }
                    }

                    //Roadie Delivery changes code
                    $roadie = new RoadieController();
                    if ($roadie->roadie_status) {
                        $deliver_roadie_fee = $roadie->getEstimate($vendorData, $address);
                        if ($deliver_roadie_fee['price'] > 0) {
                            $deliver_charge_roadie = decimal_format($deliver_roadie_fee['price']);
                            $optionRoadie[] = array(
                                'type' => 'RO',
                                'courier_name' => __('Roadie'),
                                'rate' => $deliver_charge_roadie,
                                'courier_company_id' => 0,
                                'etd' => 0,
                                'etd_hours' => 0,
                                'duration' => 0,
                                'estimated_delivery_days' => 0,
                                'code' => 'RO_0'
                            );
                            $option = array_merge($option, $optionRoadie);
                        }
                    }


                    if (isset($vendorData->vendor->ahoy_location)) {
                        //getAhoy (Masa) Delivery fee changes code
                        $ahoy = new AhoyController();
                        if ($ahoy->status) {
                            $deliver_ahoy_fee = $ahoy->getPreOrderFee($vendorData->vendor_id, $address);
                            if ($deliver_ahoy_fee > 0) {
                                $deliver_charge_ahoy = decimal_format($deliver_ahoy_fee);
                                $optionAhoy[] = array(
                                    'type' => 'M',
                                    'courier_name' => __('Ahoy'),
                                    'rate' => $deliver_charge_ahoy,
                                    'courier_company_id' => 0,
                                    'etd' => 0,
                                    'etd_hours' => 0,
                                    'duration' => 0,
                                    'estimated_delivery_days' => 0,
                                    'code' => 'M_0'
                                );
                                $option = array_merge($option, $optionAhoy);
                            }
                        }
                    }


                    //ShipEngine Delivery fee changes code
                    if (isset($vendorData->vendor)) {
                        $shipEngine = new ShipEngineController();
                        if ($shipEngine->status) {
                            $deliver_fee = $shipEngine->getEstimateFee($vendorData);
                            if ($deliver_fee > 0) {
                                $optionAhoy[] = array(
                                    'type' => 'SE',
                                    'courier_name' => __('ShipEngine'),
                                    'rate' => decimal_format($deliver_fee),
                                    'courier_company_id' => 0,
                                    'etd' => 0,
                                    'etd_hours' => 0,
                                    'duration' => 0,
                                    'estimated_delivery_days' => 0,
                                    'code' => 'SE_0'
                                );
                                $option = array_merge($option, $optionAhoy);
                            }
                        }
                    }
                } elseif ($preferences->static_delivey_fee == 1 &&  $vendorData->vendor->order_amount_for_delivery_fee != 0) {
                    # for static fees
                    if ($payable_amount >= (float)($vendorData->vendor->order_amount_for_delivery_fee)) {

                        $deliveryCharges = decimal_format($vendorData->vendor->delivery_fee_maximum);
                    } elseif ($payable_amount < (float)($vendorData->vendor->order_amount_for_delivery_fee)) {
                        $deliveryCharges = decimal_format($vendorData->vendor->delivery_fee_minimum);
                    }

                    $option[] = array(
                        'type' => 'D',
                        'courier_name' => __('Static'),
                        'rate' => $deliveryCharges,
                        'courier_company_id' => 0,
                        'etd' => 0,
                        'etd_hours' => 0,
                        'duration' => 0,
                        'estimated_delivery_days' => 0,
                        'code' => 'D_0'
                    );
                } //End statis fe code

            }
        } catch (\Exception $e) {
        }
        return $option;
    }




    # get delivery fee from dispatcher
    public function getDeliveryFeeDispatcher($vendor_id, $schedule_datetime_del = '', $dispatcher_tags = '')
    {

        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            //pr($dispatch_domain);
            if ($dispatch_domain && $dispatch_domain != false) {
                $customer = User::find(Auth::id());
                $cus_address = UserAddress::where('user_id', Auth::id())->where('status', 1)->orderBy('is_primary', 'desc')->first();
                if ($cus_address) {
                    $tasks = array();
                    $vendor_details = Vendor::find($vendor_id);

                    $location[] = array(
                        'latitude' => $vendor_details->latitude ?? 30.71728880,
                        'longitude' => $vendor_details->longitude ?? 76.80350870
                    );
                    $location[] = array(
                        'latitude' => $cus_address->latitude ?? 30.717288800000,
                        'longitude' => $cus_address->longitude ?? 76.803508700000
                    );

                    $postdata =  ['locations' => $location, 'schedule_datetime_del' => $schedule_datetime_del, 'agent_tag' => (!empty($dispatcher_tags) ? $dispatcher_tags : '')];

                    $vendorType =  (Session::has('vendorType')) ? Session::get('vendorType') : 'delivery';
                    if ($vendorType == 'appointment') {
                        $client = new GClient([
                            'headers' => [
                                'personaltoken' => $dispatch_domain->appointment_service_key,
                                'shortcode' => $dispatch_domain->appointment_service_key_code,
                                'content-type' => 'application/json'
                            ]
                        ]);

                        $url = $dispatch_domain->appointment_service_key_url;
                        $res = $client->post(
                            $url . '/api/get-delivery-fee',
                            ['form_params' => ($postdata)]
                        );
                        $response = json_decode($res->getBody(), true);
                    } elseif ($vendorType == 'on_demand') {
                        $client = new GClient([
                            'headers' => [
                                'personaltoken' => $dispatch_domain->dispacher_home_other_service_key,
                                'shortcode' => $dispatch_domain->dispacher_home_other_service_key_code,
                                'content-type' => 'application/json'
                            ]
                        ]);

                        $url = $dispatch_domain->dispacher_home_other_service_key_url;
                        $res = $client->post(
                            $url . '/api/get-delivery-fee',
                            ['form_params' => ($postdata)]
                        );
                        $response = json_decode($res->getBody(), true);
                    } else {
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
                    }

                    if ($response && $response['message'] == 'success') {
                        $response_array[] = array('delivery_fee' => $response['total'], 'total_duration' => $response['total_duration']);
                        return $response_array;
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
    # check if last mile delivery on
    public function checkIfLastMileOn()
    {
        $vendorType =  (Session::has('vendorType')) ? Session::get('vendorType') : 'delivery';
        $preference = ClientPreference::first();
        switch ($vendorType) {
            case 'appointment':
                if (($preference->need_appointment_service == 1) && !empty($preference->appointment_service_key) && !empty($preference->appointment_service_key_code) && !empty($preference->appointment_service_key_url)) {
                    return false;
                }
                break;
            case 'on_demand':
                if (($preference->need_dispacher_home_other_service == 1) && !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_code) && !empty($preference->dispacher_home_other_service_key_url)) {
                    return $preference;
                }
                break;
            default:
                if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url)) {
                    return $preference;
                }
                // code block
        }
        // if(( $vendorType == 'appointment') && ( ($preference->need_appointment_service == 1) && !empty($preference->appointment_service_key) && !empty($preference->appointment_service_key_code) && !empty($preference->appointment_service_key_url)) ){
        //     //
        // }
        // elseif ( ( $vendorType != 'appointment') && $preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
        //     return $preference;
        // else
        //     return false;
    }

    public function uploadPrescription(Request $request, $domain = '')
    {
        $user = Auth::user();
        if ($user) {
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->first();
            foreach ($request->prescriptions as $prescription) {
                $cart_product_prescription = new CartProductPrescription();
                $cart_product_prescription->cart_id = $cart->id;
                $cart_product_prescription->vendor_id = $request->vendor_idd;
                $cart_product_prescription->product_id = $request->product_id;
                $cart_product_prescription->prescription = Storage::disk('s3')->put('prescription', $prescription, 'public');
                $cart_product_prescription->save();
            }
        }
        return response()->json(['status' => 'success', 'message' => "Uploaded Successfully"]);
    }


    public function addVendorTableToCart(Request $request, $domain = '')
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if ($user) {
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->firstOrFail();
                $cartData = CartProduct::where('cart_id', $cart->id)->where('vendor_id', $request->vendor)->update(['vendor_dinein_table_id' => $request->table]);
                DB::commit();
                return response()->json(['status' => 'Success', 'message' => 'Table has been selected']);
            } else {
                return response()->json(['status' => 'Error', 'message' => 'Invalid user']);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['status' => 'Error', 'message' => $ex->getMessage()]);
        }
    }

    public function updateSchedule(Request $request, $domain = '')
    {
        // dd($request->all());
        DB::beginTransaction();
        try {

            $user = Auth::user();
            $client_timezone = DB::table('clients')->first('timezone');
            $user->timezone = $client_timezone->timezone ?? $user->timezone;
            $new_session_token = session()->get('_token');
            $langId = Session::get('customerLanguage') ?? '1';

            if ($user) {
                $cart_detail = Cart::where('user_id', $user->id)->first();
            } else {
                $cart_detail = Cart::where('unique_identifier', $new_session_token)->first();
            }

            $productIds = CartProduct::where('cart_id', $cart_detail->id)->whereHas('cartProduct', function ($q) {
                $q->where('pharmacy_check', 1);
            })->pluck('product_id');


            if (count($productIds) > 0) {

                $presciptionProducts = [];
                foreach ($productIds as $product_id) {

                    $cartProductPrescription =  CartProductPrescription::where(['cart_id' => $cart_detail->id, 'product_id' => $product_id])->first('product_id');
                    if (!isset($cartProductPrescription)) {
                        array_push($presciptionProducts, $product_id);
                    }
                }

                if (count($presciptionProducts)) {
                    return response()->json(['status' => 'error_prescription', 'presciptionProducts' => $presciptionProducts]);
                }
            }

            $addon_ids = [];
            if ($request->has('addonID')) {
                $addon_ids = $request->addonID;
            }
            $addon_options = [];
            if ($request->has('addonoptID')) {
                $addon_options = $request->addonoptID;
            }
            $addonSets = [];
            foreach ($addon_options as $key => $opt) {
                if (isset($addon_ids[$key])) {
                    $addonSets[$addon_ids[$key]][] = $opt;
                }
            }




            foreach ($addonSets as $key => $value) {
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                    ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                    ->where('ast.language_id', $langId)
                    ->where('addon_sets.status', '!=', '2')
                    ->where('addon_sets.id', $key)->first();
                if (!$addon) {
                    return response()->json(["status" => "error", 'message' => 'Invalid addon or delete by admin. Try again with remove some.'], 404);
                }
            }
            $cartProduct = CartProduct::where('cart_id', $cart_detail->id)->first();

            $isnew = 0;
            if (!$cartProduct) {
                $isnew = 1;
            } else {
                $checkaddonCount = CartAddon::where('cart_product_id', $cartProduct->id)->count();
                if (count($addon_ids) != $checkaddonCount) {
                    $isnew = 1;
                } else {
                    foreach ($addon_options as $key => $opts) {
                        $cart_addon = CartAddon::where('cart_product_id', $cartProduct->id)
                            ->where('addon_id', $addon_ids[$key])
                            ->where('option_id', $opts)->first();

                        if (!$cart_addon) {
                            $isnew = 1;
                        }
                    }
                }
            }

            if ($isnew) {
                if (!empty($addon_ids) && !empty($addon_options)) {
                    $saveAddons = array();
                    foreach ($addon_options as $key => $opts) {
                        if (isset($addon_ids[$key])) {
                            $saveAddons[] = [
                                'option_id' => $opts,
                                'cart_id' => $cart_detail->id,
                                'addon_id' => $addon_ids[$key],
                                'cart_product_id' => $cartProduct->id,
                            ];
                        }
                    }
                    if (!empty($saveAddons)) {
                        CartAddon::insert($saveAddons);
                    }
                }
            }

            if ($request->has('rentalProtectionId')) {
                $saveProtections = [];
                foreach ($request->rentalProtectionId as $protectionId) {
                    $rentalProtection = RentalProtection::find($protectionId);
                    if ($rentalProtection) {
                        $saveProtections[] = [
                            'cart_id' => $cart_detail->id,
                            'rental_protection_id' => $rentalProtection->id,
                            'product_id' => $cartProduct->id,
                        ];
                    }
                }
                if (!empty($saveProtections)) {
                    CartRentalProtection::insert($saveProtections);
                }
            }

            if ($request->has('bookingOptionId')) {
                $saveBooking = [];
                foreach ($request->bookingOptionId as $bookingId) {
                    $bookingOption = BookingOption::find($bookingId);
                    if ($bookingOption) {
                        $saveBooking[] = [
                            'cart_id' => $cart_detail->id,
                            'booking_option_id' => $bookingOption->id,
                            'product_id' => $cartProduct->id,
                        ];
                    }
                }
                if (!empty($saveBooking)) {
                    CartBookingOption::insert($saveBooking);
                }
            }

            if ($user || $new_session_token) {
                if ($request->task_type == 'now') {
                    $time = Carbon::now()->format('Y-m-d H:i:s');
                } else {
                    if ($request->schedule_dt) {
                        if (isset($request->slot)) {
                            //->setTimezone('UTC') (in case slot is comming then schedule_dt coming only date and we no need to convart date to ny UTC time  )
                            $time = Carbon::parse($request->schedule_dt, $user->timezone)->format('Y-m-d H:i:s');
                            $slot = $request->slot;
                        } else {

                            if (isset($request->schedule_dt) && !empty($request->schedule_dt))
                                $time = Carbon::parse($request->schedule_dt, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                        }
                    }
                }



                if (isset($request->schedule_pickup) && !empty($request->schedule_pickup) &&  $request->schedule_pickup != 'undefined undefined')    # for pickup laundry
                    $request->schedule_pickup = Carbon::parse($request->schedule_pickup, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');

                if (isset($request->schedule_dropoff) && !empty($request->schedule_dropoff) &&  $request->schedule_dropoff != 'undefined undefined')  # for pickup laundry
                    $request->schedule_dropoff = Carbon::parse($request->schedule_dropoff, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');

                if (isset($request->dropoff_scheduled_slot)) {
                    $dropSlot = $request->dropoff_scheduled_slot;
                }



                //  pr($time);
                $cart_update = $cart_detail->update([
                    'specific_instructions' => $request->specific_instructions ?? null,
                    'schedule_type' => $request->task_type,
                    'address_id' => $request->address,
                    'scheduled_date_time' => $time ?? null,
                    'scheduled_slot' => $slot ?? null,
                    'dropoff_scheduled_slot' => $dropSlot ?? null,
                    'shipping_delivery_type' => $request->delivery_type ?? 'D',
                    'comment_for_pickup_driver' => $request->comment_for_pickup_driver ?? null,
                    'comment_for_dropoff_driver' => $request->comment_for_dropoff_driver ?? null,
                    'comment_for_vendor' => $request->comment_for_vendor ?? null,
                    'schedule_pickup' => $request->schedule_pickup ?? null,
                    'schedule_dropoff' => $request->schedule_dropoff ?? null,
                    'payable_amount' => $request->payable_amount ?? 0
                ]);

                CartProduct::where('id', $request->productid)->update(['specific_instruction' => $request->specific_instructions]);

                DB::commit();
                if ($user) {
                    $checkpreference = ClientPreference::select('verify_email', 'verify_phone', 'third_party_accounting')->first();
                    $age_restriction = CartProduct::where('cart_id', $cart_detail->id)->whereHas('product', function ($q) {
                        $q->where('age_restriction', 1);
                    })->count();
                    $passbase_check = VerificationOption::where(['code' => 'passbase', 'status' => 1])->first();
                    if ($passbase_check && $age_restriction) {
                        if (is_null($user->passbase_verification)) {
                            return response()->json(['status' => 'passbase_pending', 'message' => 'The cart contains Alcohol/Tobacco contents. It is mandatory to provide the verification documents to proceed']);
                        } elseif ($user->passbase_verification->status == 'pending' || $user->passbase_verification->status == 'processing') {
                            return response()->json(['status' => 'passbase_submitted', 'message' => 'We have received your request for verification. Check back soon and order OR remove Alcohol/Tobacco items']);
                        } elseif ($user->passbase_verification->status == 'declined') {
                            return response()->json(['status' => 'passbase_rejected', 'message' => 'According to our Terms and Conditions and Company\'s Policies, your verification documents were not found upto the mark .Please upload them again OR remove Alcohol/Tobacco items.']);
                        }
                    }

                    // if ($checkpreference->verify_email == 1 || $checkpreference->verify_phone == 1) {
                    //     if ($checkpreference->verify_email == 1) {

                    //         if ($user->is_email_verified == 0) {
                    //             return response()->json(['status' => 'Pending', 'message' => 'Verify your account first']);
                    //         }
                    //     }
                    //     if ($checkpreference->verify_phone == 1) {
                    //         if ($user->is_phone_verified == 0) {
                    //             return response()->json(['status' => 'Pending', 'message' => 'Verify your account first']);
                    //         }
                    //     }
                    // }
                }
                return response()->json(['status' => 'Success', 'message' => 'Cart has been scheduled']);
            } else {
                return response()->json(['status' => 'Error', 'message' => 'Invalid user']);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['status' => 'Error', 'message' => $ex->getMessage()]);
        }
    }

    # update schedule for home services basis on services
    public function updateProductSchedule(Request $request, $domain = '')
    {
        //pr($request->all());
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $cartProduct = CartProduct::where('id', $request->cart_product_id)->first();

            if ($user) {
                if ($request->task_type == 'now') {
                    $request->schedule_dt = Carbon::now()->format('Y-m-d H:i:s');
                } else {
                    if (!empty($request->schedule_time)) {
                        $slot_time = explode("-", $request->schedule_time);
                        $start_time = $slot_time[0];
                        $end_time = !empty($slot_time[1]) ? $slot_time[1] : $slot_time[0];
                        $request->schedule_dt = date('d-m-Y H:i:s', strtotime(date('Y-m-d', strtotime($request->schedule_dt)) . " " . $start_time));
                    } else {
                        if (isset($cartProduct->schedule_slot)) {
                            $start_scheduled_time = date('H:i', strtotime($request->schedule_dt));
                            $interval = isset($cartProduct->vendor) &&  $cartProduct->vendor->slot_minutes > 0 ?  $cartProduct->vendor->slot_minutes : 30;
                            $request->schedule_time = $start_scheduled_time . " - " . date('H:i', strtotime($start_scheduled_time . ' + ' . $interval . ' minute'));
                        }
                    }

                    $request->schedule_dt = Carbon::parse($request->schedule_dt, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                }
                $cartProduct->update(['schedule_type' => $request->task_type, 'scheduled_date_time' => $request->schedule_dt, 'schedule_slot' => $request->schedule_time, 'dispatch_agent_id' => $request->dispatch_agent_id]);


                // $cartProductDetails = CartProduct::where('id', $request->cart_product_id)->get()->first();
                // CartProduct::where('cart_id', $cartProductDetails->cart_id )->where('vendor_id', $cartProductDetails->vendor_id  )->update(['schedule_type' => $request->task_type, 'scheduled_date_time' => $request->schedule_dt,'schedule_slot' => $request->schedule_time]);

                DB::commit();
                return response()->json(['status' => 'Success', 'message' => 'Cart has been scheduled']);
            } else {
                return response()->json(['status' => 'Error', 'message' => 'Invalid user']);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['status' => 'Error', 'message' => $ex->getMessage()]);
        }
    }

    # update dispatch agent id  home services basis on services
    public function updateDispatcherAgent(Request $request, $domain = '')
    {
        //pr($request->all());
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if ($user) {

                CartProduct::where('id', $request->cart_product_id)->update(['dispatch_agent_id' => $request->dispatch_agent_id]);

                DB::commit();
                return response()->json(['status' => 'Success', 'message' => 'Cart has been scheduled']);
            } else {
                return response()->json(['status' => 'Error', 'message' => 'Invalid user']);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['status' => 'Error', 'message' => $ex->getMessage()]);
        }
    }

    // add ones add in cart for ondemand

    public function postAddToCartAddons(Request $request, $domain = '')
    {
        try {

            $user = Auth::user();
            $addon_ids = $request->addonID;
            $addon_options_ids = $request->addonoptID;
            $langId = Session::get('customerLanguage');

            $addonSets = $addon_ids = $addon_options = array();
            if ($request->has('addonID')) {
                $addon_ids = $request->addonID;
            }
            if ($request->has('addonoptID')) {
                $addon_options = $request->addonoptID;
            }
            foreach ($addon_options as $key => $opt) {
                $addonSets[$addon_ids[$key]][] = $opt;
            }

            if ($request->has('addonoptID')) {
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                    ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                    ->where('ast.language_id', $langId)
                    ->where('addon_sets.status', '!=', '2')
                    ->where('addon_sets.id', $request->addonID[0])->first();
                if (!$addon) {
                    return response()->json(['error' => 'Invalid addon or delete by admin. Try again with remove some.'], 404);
                }
                if ($addon->min_select > count($request->addonID)) {
                    return response()->json([
                        'error' => 'Select minimum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
                if ($addon->max_select < count($request->addonID)) {
                    return response()->json([
                        'error' => 'You can select maximum ' . $addon->max_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
            }

            if (isset($addon_ids) && !empty($addon_ids[0]))
                CartAddon::where('cart_id', $request->cart_id)->where('cart_product_id', $request->cart_product_id)->where('addon_id', $addon_ids[0])->delete();
            else
                CartAddon::where('cart_id', $request->cart_id)->where('cart_product_id', $request->cart_product_id)->delete();

            if (count($addon_options) > 0) {
                $saveAddons = array();
                foreach ($addon_options as $key => $opts) {
                    $saveAddons[] = [
                        'option_id' => $opts,
                        'cart_id' => $request->cart_id,
                        'addon_id' => $addon_ids[$key],
                        'cart_product_id' => $request->cart_product_id,
                    ];
                }
                CartAddon::insert($saveAddons);
            }





            return response()->json(['status' => 'success', 'message' => 'Addons Added Successfully!']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function checkIsolateSingleVendor(Request $request, $domain = '')
    {
        
        $preference = ClientPreference::first();
        $user = Auth::user();
        $new_session_token = session()->get('_token');
        if ($user) {
            $cart_detail = Cart::where('user_id', $user->id)->first();
        } else {
            $cart_detail = Cart::where('unique_identifier', $new_session_token)->first();
        }
        if ((isset($preference->isolate_single_vendor_order)) && ($preference->isolate_single_vendor_order == 1) && (!empty($cart_detail))) {
            $checkVendorId = CartProduct::where('vendor_id', '!=', $request->vendor_id)->where('cart_id', $cart_detail->id)->first();
            return response()->json(['status' => 'Success', 'otherVendorExists' => ($checkVendorId ? 1 : 0), 'isSingleVendorEnabled' => 1]);
        } else {
            return response()->json(['status' => 'Success', 'otherVendorExists' => 0, 'isSingleVendorEnabled' => 0]);
        }
    }


    public function updateCartSlot(Request $request)
    {
        $checkVendorProd = CartProduct::where('vendor_id', $request->vid)->update(['schedule_type' => $request->slot, 'scheduled_date_time' => $request->date, 'specific_instruction' => $request->specific_instructions]);
        return true;
    }



    // public function updateCartProductFaq(Request $request, $domain = '')
    // {

    //     $user = Auth::user();
    //     $new_session_token = session()->get('_token');
    //     if ($user) {
    //         $cart = Cart::where('user_id', $user->id)->first();
    //     } else {
    //         $cart = Cart::where('unique_identifier', $new_session_token)->first();
    //     }
    //     $user_product_order_form = null;

    //     $cartData_id = CartProduct::where('cart_id', $cart->id)->where('product_id', $request->product_id)->pluck('id');

    //     if (isset($request->user_product_order_form) && !empty($request->user_product_order_form))
    //         $user_product_order_form = json_encode($request->user_product_order_form);

    //     CartProduct::whereIn('id', $cartData_id)->update(['user_product_order_form' => $user_product_order_form]);

    //     return response()->json(['status' => 'Success', 'message' => __('Product form Submit successfully.')]);
    // }
    
    public function updateCartProductFaq(Request $request, $domain = '')
    {
        $user = Auth::user();
        $new_session_token = session()->get('_token');

        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
        } else {
            $cart = Cart::where('unique_identifier', $new_session_token)->first();
        }

        $user_product_order_form = [];

        $cartData_id = CartProduct::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->pluck('id');

        if ($request->has('user_product_order_form')) {
            foreach ($request->user_product_order_form as $index => $faq) {
                if (isset($faq['answer']) && $faq['answer'] instanceof \Illuminate\Http\UploadedFile) {
                    $filename = time() . "_" . $faq['answer']->getClientOriginalName();
                    
                    $filePath = 'productfaq/' . $filename;
                    Storage::disk('s3')->put($filePath, file_get_contents($faq['answer']), 'public');
                    $faq['answer'] = Storage::disk('s3')->url($filePath);
                    ClientDocument::create([
                        'cart_id' =>  $cart ?  $cart->id : null,
                        'client_id' => $user->id,
                        'file_path' => $filePath,
                        'product_id' => $request->product_id,
                        'file_name' => $filename,
                    ]);
                }
                $user_product_order_form[] = $faq;
            }
        }

        $user_product_order_form_json = json_encode($user_product_order_form);

        CartProduct::whereIn('id', $cartData_id)->update(['user_product_order_form' => $user_product_order_form_json]);

        return response()->json(['status' => 'Success', 'message' => __('Product form Submit successfully.')]);
    }

    public function updateCartCategoryKyc(Request $request)
    {
        $category_ids = explode(",", $request->category_id);
        $category_kyc_documents = CategoryKycDocuments::whereHas('categoryMapping', function ($q) use ($category_ids) {
            $q->whereIn('category_id', $category_ids);
        })->with('primary')->get();
        foreach ($category_kyc_documents as $vendor_registration_document) {
            if ($vendor_registration_document->is_required == 1) {
                if (isset($vendor_registration_document->primary) && !empty($vendor_registration_document->primary)) {
                    $rules[$vendor_registration_document->primary->slug] = 'required';
                }
            }
        }
        $validation  = Validator::make($request->all(), $rules)->validate();

        $user = Auth::user();
        $new_session_token = session()->get('_token');
        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
        } else {
            $cart = Cart::where('unique_identifier', $new_session_token)->first();
        }

        //pr($category_ids);
        $user_product_order_form = null;

        foreach ($category_ids as $category_id) {
            $category_kyc_documents = CategoryKycDocuments::whereHas('categoryMapping', function ($q) use ($category_id) {
                $q->where('category_id', $category_id);
            })->with('primary')->get();

            //  pr($category_kyc_documents->first());

            if ($category_kyc_documents->count() > 0) {
                foreach ($category_kyc_documents as $vendor_registration_document) {
                    $doc_name = str_replace(" ", "_", $vendor_registration_document->primary->slug);
                    if ($vendor_registration_document->file_type != "Text" && $vendor_registration_document->file_type != "selector") {
                        $check = CaregoryKycDoc::where(['cart_id' => $cart->id, 'category_kyc_document_id' => $vendor_registration_document->id])->first();
                        if ($request->hasFile($doc_name) && !$check) {

                            $vendor_docs =  new CaregoryKycDoc();
                            $vendor_docs->user_id = $user->id;
                            $vendor_docs->category_kyc_document_id = $vendor_registration_document->id;
                            $filePath = 'category_kyc_document' . '/' . Str::random(40);
                            $file = $request->file($doc_name);
                            $vendor_docs->file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                            $vendor_docs->cart_id = $cart->id;
                            $vendor_docs->save();
                        }
                    }
                    //else {
                    //     if (!empty($request->$doc_name)) {
                    //         $vendor_docs =  new CaregoryKycDoc();
                    //         $vendor_docs->user_id = $user->id;
                    //         $vendor_docs->category_kyc_document_id = $vendor_registration_document->id;
                    //         $vendor_docs->file_name = $request->$doc_name;
                    //         $vendor_docs->cart_id = $cart->id;
                    //         $vendor_docs->save();
                    //     }
                    //}
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'document submit Successfully!',
        ]);
        //return redirect()->back()->with('status','Submit successfully.');
        // $res = CaregoryKycDoc::where('user_id',$user->id)->get();
        // pr($res);
        // $cartData_id = CartProduct::where('cart_id', $cart->id)->where('product_id', $request->product_id)->pluck('id');

        // if(isset($request->user_product_order_form) && !empty($request->user_product_order_form))
        // $user_product_order_form = json_encode($request->user_product_order_form);

        // CartProduct::whereIn('id', $cartData_id)->update(['user_product_order_form'=> $user_product_order_form]);

        //return response()->json(['status'=>'Success', 'message'=>__('Product form Submit successfully.')]);
    }

    function ajaxGetScheduleDateDetails(Request $request)
    {
        $vendorWeeklySlotDay = VendorSlot::select('start_time', 'end_time', 'day')->join('slot_days', 'slot_days.slot_id', '=', 'vendor_slots.id')->where(['vendor_slots.vendor_id' => $request->vendorId])->get()->toArray();
        $today = ['start_time' => "00:00", 'end_time' => "00:00"];
        $dt = new \DateTime($request->date);
        foreach ($vendorWeeklySlotDay as $row) {

            if (($row['day'] - 1) == (int)$dt->format('w')) {
                $today = ['start_time' => convertDateTimeInTimeZone(date('Y-M-d') . " " . $row['start_time'], Auth()->user()->timezone, 'H:i'), 'end_time' => substr($row['end_time'], 0, -3)];
            }
        }
        return json_encode($today);
    }


    public function VendorTimeSlot(Request $request)
    {
        $user       = Auth::user();
        $dates      = $request->dates;
        $dates      = explode(",", $dates);
        $userdates  = [];
        $dayArr     = ['sunday' => 1, 'monday' => 2, 'tuesday' => 3, 'wednesday' => 4, 'thursday' => 5, 'friday' => 6, 'saturday' => 7];
        if ($dates) {
            foreach ($dates as $date) {
                $day = GetDayFromDate($date);
                $day = $dayArr[$day];
                array_push($userdates, $day);
            }
        }
        if ($user) {
            $cart       = Cart::select('id', 'is_gift', 'item_count', 'comment_for_pickup_driver', 'comment_for_dropoff_driver', 'comment_for_vendor', 'specific_instructions')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
            $addresses  = UserAddress::where('user_id', $user->id)->where('status', 1)->get();
            $guest_user = false;
        } else {
            $cart       = Cart::select('id', 'is_gift', 'item_count', 'comment_for_pickup_driver', 'comment_for_dropoff_driver', 'comment_for_vendor', 'specific_instructions')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
            $addresses  = collect();
        }
        if ($cart) {
            $cartData   = CartProduct::where('status', [0, 1])->where('cart_id', $cart->id)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();
        }

        if ($cartData) {
            foreach ($cartData as $key => $data) {
                $vendorWeeklySlotDay = VendorSlot::select('start_time', 'end_time', 'day')->join('slot_days', 'slot_days.slot_id', '=', 'vendor_slots.id')->where(['vendor_slots.vendor_id' => $data->vendor_id])->get()->toArray();
                $checkAvailableSlots = $this->RecurringBookingAvailableSlots($vendorWeeklySlotDay, $userdates);
                //pr($checkAvailableSlots);
            }
        }
    }

    public function RecurringBookingAvailableSlots($vendorWeeklySlotDay, $userdates)
    {
        $AvailableSlots = [];
        if ($vendorWeeklySlotDay) {
            foreach ($vendorWeeklySlotDay as $slot) {
                if (in_array($slot['day'], $userdates)) {
                    $AvailableSlots[] = ['start_time' => convertDateTimeInTimeZone(date('Y-M-d') . " " . $slot['start_time'], Auth()->user()->timezone, 'H:i'), 'end_time' => substr($slot['end_time'], 0, -3)];
                }
            }
            return $AvailableSlots;
        }
    }

    public function recurringCalculationFunction($request)
    {
        $recurringformPost = (object)$request->recurringformPost;
        $weekTypes = '';
        $daysCnt = '';
        if (!empty($recurringformPost->weekDay)) {
            $weekTypes = implode(',', $recurringformPost->weekDay);
        }

        $startDate = $recurringformPost->startDate;
        $endDate = $recurringformPost->endDate;

        $selectedCustomdates = [];


        if ($recurringformPost->action == '2' || $recurringformPost->action == '1') {
            $startDate = $recurringformPost->startDate;
            $endDate = $recurringformPost->endDate;

            if ($recurringformPost->action == '1') {
                $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate, $endDate);
            } else {
                $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate, $endDate, $recurringformPost->weekDay);
            }

            $daysCnt = count($selectedCustomdates);
            $selectedCustomdates = implode(',', $selectedCustomdates);
        } elseif ($recurringformPost->action == '3') {
            $startDate = Carbon::now()->addDays(1);
            $endDate = Carbon::now()->addDays(1);
            $endDate = $endDate->addMonths($recurringformPost->month_number);
            $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate, $endDate);
            $daysCnt = count($selectedCustomdates);
            $selectedCustomdates = implode(',', $selectedCustomdates);
        } elseif ($recurringformPost->action == '4') {
            if (!empty($recurringformPost->selectedCustomdates)) {
                $daysCnt = count($recurringformPost->selectedCustomdates);
                $selectedCustomdates = implode(',', $recurringformPost->selectedCustomdates);
            }
            if (!empty($recurringformPost->selected_custom_dates)) {
                $daysCnt = count($recurringformPost->selected_custom_dates);
                $selectedCustomdates = implode(',', $recurringformPost->selected_custom_dates);
            }
        } elseif ($recurringformPost->action == '6') {
            $startDate = $recurringformPost->startDate;
            $endDate = $recurringformPost->endDate;
            if ($recurringformPost->action == '1') {
                $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate, $endDate);
            } else {
                $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate, $endDate, $recurringformPost->weekDay, 'A');
            }

            $daysCnt = count($selectedCustomdates);
            $selectedCustomdates = implode(',', $selectedCustomdates);
        }


        if (empty($daysCnt)) {
            $days = getDaysArrayBetweenTwoDates($startDate, $endDate);
            $daysCnt = count($days);
        }

        return (object)[
            'weekTypes' => @$weekTypes,
            'selectedCustomdates' => @$selectedCustomdates,
            'startDate' => @$startDate,
            'endDate' => @$endDate,
            'action'  => @$recurringformPost->action,
            'schedule_time' => @$recurringformPost->schedule_time ?? '10:00',
            'daysCnt' => @$daysCnt ?? '1'
        ];
    }
}
