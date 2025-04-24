<?php

namespace App\Http\Controllers\Api\v1;

use DB,Log;
use Config;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Requests\OrderProductRatingRequest;
use App\Models\{AddonOption, Category,ClientPreference,ClientCurrency,Vendor,ProductVariantSet,Product,SubscriptionInvoicesUser,LoyaltyCard,UserAddress,Order,OrderVendor,OrderProduct,VendorOrderStatus,Client, ClientPreferenceAdditional, Promocode,PromoCodeDetail,VendorOrderDispatcherStatus, Payment, Rider, OrderLocations, LuxuryOption, OrderDriverRating, OrderProductAddon, OrderVendorProduct, ProductFaq, ProductFaqSelectOption, UserBidRideRequest, PickDropDriverBid, TaxRate, UserDevice};
use App\Http\Traits\{ApiResponser,OrderTrait,GuzzleHttpTrait};
use GuzzleHttp\Client as GCLIENT;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as FacadesLog;

class PickupDeliveryController extends BaseController{

    use ApiResponser,OrderTrait,GuzzleHttpTrait;
    private $riderObj;
    public function __construct()
    {
        $this->riderObj = new Rider();
    }


    # get all vehicles category by vendor

    public function productsByVendorInPickupDelivery(Request $request, $vid = 0, $cid = 0){
        try {
            if($vid == 0){
                return response()->json(['error' => __('No record found.')], 404);
            }

            $preferences = ClientPreference::where('id', '>', 0)->first();
            $preferences->is_cab_pooling = getAdditionalPreference(['is_cab_pooling'])['is_cab_pooling'];

            $user = Auth::user();
            $userid = $user->id;
            $schedule_datetime_del = '';
            if (isset($request->schedule_date_delivery) && !empty($request->schedule_date_delivery)) {
                $schedule_datetime_del = Carbon::parse($request->schedule_date_delivery, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
            }else{
                $schedule_datetime_del = Carbon::now()->timezone($user->timezone)->format('Y-m-d H:i:s');
            }

            $paginate = $request->has('limit') ? $request->limit : 12;
            $clientCurrency = ClientCurrency::where('currency_id', $user->currency)->first();

            if(empty($clientCurrency))
            {
                $clientCurrency = ClientCurrency::first();

            }
            $langId = $user->language;
            $vendor = Vendor::select('id', 'name', 'desc', 'logo', 'banner', 'address', 'latitude', 'longitude',
                        'order_min_amount', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery')
                        ->where('id', $vid)->first();
            if(!$vendor){
                return response()->json(['error' => __('No record found.')], 200);
            }

            $products = Product::with(['category.categoryDetail', 'vendor', 'tollpass', 'travelmode', 'emissiontype', 'inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },
                        'media.image', 'translation' => function($q) use($langId){
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                        },
                        'variant' => function($q) use($langId){
                            $q->select('id','sku', 'product_id', 'quantity', 'price', 'barcode');
                            $q->groupBy('product_id');
                        },'ProductFaq.translations' => function ($qs) use($langId){
                            $qs->where('language_id',$langId);
                        },'ProductFaq.selection.translations' => function ($qs) use($langId){
                            $qs->where('language_id',$langId);
                        },
                        'ProductAttribute',
                        'ProductAttribute.attributeOption',
                        'addOn' => function ($q1) use ($langId) {
                            $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                            $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                            $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                            $q1->where('set.status', 1)->where('ast.language_id', $langId);
                        },
                        'addOn.setoptions' => function ($q2) use ($langId) {
                            $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                            $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                            $q2->where('apt.language_id', $langId);
                        }
                    ])->join('product_categories as pc', 'pc.product_id', 'products.id')
                    ->whereNotIn('pc.category_id', function($qr) use($vid){
                                $qr->select('category_id')->from('vendor_categories')
                                    ->where('vendor_id', $vid)->where('status', 0);
                    })
                    ->select('products.id','products.tax_category_id', 'products.sku', 'products.requires_shipping', 'products.sell_when_out_of_stock', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.Requires_last_mile', 'products.averageRating', 'pc.category_id','products.tags','products.seats_for_booking', 'products.available_for_pooling', 'products.is_toll_tax', 'products.travel_mode_id', 'products.toll_pass_id', 'products.emission_type_id')
                    ->where('products.vendor_id', $vid);
                    if($cid > 0){
                        $products = $products->where('products.category_id', $cid);
                    }

                    if(!empty($request->is_cab_pooling) && $request->is_cab_pooling == 1 && !empty($preferences) && $preferences->is_cab_pooling == 1)
                    {
                        $products = $products->where('products.available_for_pooling', 1);
                        if(isset($request->no_seats_for_pooling))
                        {
                            $products = $products->where('products.seats_for_booking', '>=', $request->no_seats_for_pooling);
                        }
                    }
                    $products = $products->where('products.is_live', 1)->distinct()->paginate($paginate);
                    $loyalty_amount_saved = 0;
                    $redeem_points_per_primary_currency = '';
                    $loyalty_card = LoyaltyCard::where('status', '0')->first();
                    if ($loyalty_card) {
                        $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
                    }
                    $loyalty_points_used = 0.0;
                    $order_loyalty_points_earned_detail = Order::where('user_id', $userid)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
                    if ($order_loyalty_points_earned_detail) {
                        $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                        if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                            $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                        }
                    }
                    $total_price = 0 ;
                    $payable_amount= 0;
                    $vendor_payable_amount=0;
                    $taxable_amount = 0;
                    $tax_amount = 0;
                    $response['tips'] = [];
                    $payable_amount= 0;
                    $vendor_payable_amount=0;
                    $taxable_amount = 0;
                    $tax_amount = 0;
            if(!empty($products)){
                foreach ($products as $key => $product) {
                    $total_price = 0 ;
                    $payable_amount= 0;
                    $vendor_payable_amount=0;
                    $taxable_amount = 0;
                    $tax_amount = 0;
                    $response['tips'] = [];
                    $payable_amount= 0;
                    $vendor_payable_amount=0;
                    $taxable_amount = 0;
                    $tax_amount = 0;
                    $tags_price = $this->getDeliveryFeeDispatcher($request, $product, $schedule_datetime_del);
                    $product->service_charge_amount  = 0.00;
                    if($product->vendor->fixed_service_charge)
                    {
                        $product->service_charge_amount  =  $product->vendor->service_charge_amount??0.00;
                    }else{
                        if($product->vendor->service_fee_percent>0){

                            $product->service_charge_amount  = $product->tags_price * $product->vendor->service_fee_percent/100;
                        }
                    }
                    $fields = [];
                    foreach ($product->ProductAttribute as $productAttribute) {
                        if ($productAttribute->attributeOption()->exists()) {
                            if(!empty($title = $productAttribute->attributeOption->title)){
                                $fields[$productAttribute->key_name] = $title;
                            }else{
                                $fields[$productAttribute->key_name] = $productAttribute->key_value;
                            }
                        }
                    }
                    $product->no_of_luggage = $fields['No of luggage'] ?? '';
                    $product->no_of_seats = $fields['Seats'] ?? '0' .' Seats';

                    $product->toll_fee   = $tags_price['toll_fee']??0;
                    $product->tags_price = $tags_price['delivery_fee']??0;
                    $total_price += $total_price + $product->tags_price;
                    $product->distance = decimal_format($tags_price['distance']??0);
                    $product->duration = decimal_format($tags_price['duration']??0);
                    $product->min_tags_price = decimal_format($tags_price['min_delivery_fee']??0);
                    $product->max_tags_price = decimal_format($tags_price['max_delivery_fee']??0);

                    $product->seats_for_booking = ($product->seats_for_booking > 0)?$product->seats_for_booking:1;
                    if(isset($request->is_cab_pooling) && $request->is_cab_pooling==1 && !empty($preferences) && $preferences->is_cab_pooling == 1)
                    {
                        if(isset($request->no_seats_for_pooling))
                        {
                            $no_seats_for_pooling = $request->no_seats_for_pooling;
                        }else{
                            $no_seats_for_pooling = 1;
                        }
                        $product->tags_price = decimal_format(($product->tags_price/$product->seats_for_booking)*$no_seats_for_pooling);
                        $product->toll_fee   = decimal_format(($product->toll_fee/$product->seats_for_booking)*$no_seats_for_pooling);
                    }else{
                        $product->tags_price = decimal_format($product->tags_price);
                        $product->toll_fee   = decimal_format($product->toll_fee);
                    }
                    $product->total_tags_price = $product->tags_price + $product->toll_fee + $product->service_charge_amount- $loyalty_amount_saved??0.00;
                    foreach ($product->variant as $k => $v) {
                        $product->variant[$k]->price = $product->tags_price;
                        $product->variant[$k]->toll_fee = $product->toll_fee;
                        $product->variant[$k]->multiplier = $clientCurrency->doller_compare ?? 1;
                    }
                    $now = Carbon::now()->toDateTimeString();
                    $subscriptionInvoiceUser = SubscriptionInvoicesUser::with('features')->whereUserId($userid)->where('end_date', '>', $now)
                    ->orderBy('end_date', 'desc')->first();
                    if($subscriptionInvoiceUser){
                        $percentValue = $subscriptionInvoiceUser->features[0]['percent_value'];
                        if(!empty($percentValue)){
                            $calulateSubscription = ($percentValue / 100)* $product->tags_price;
                            $subscriptionPercentage = $percentValue;
                            $subscriptionAmount = $calulateSubscription;
                            $totalTagPriceWithSubscription = $product->tags_price - $calulateSubscription;
                            $product->subscriptionPercentage = $percentValue;
                            $product->subscriptionAmount = decimal_format($calulateSubscription);
                            $product->total_tags_price = decimal_format($totalTagPriceWithSubscription)+ $product->service_charge_amount- $loyalty_amount_saved??0.00;
                        }
                    }

                    $divider = (empty($clientCurrency->doller_compare) || $clientCurrency->doller_compare < 0) ? 1 : $clientCurrency->doller_compare;
                    $divider = isset($divider) ? $divider : 1;
                    $price_in_currency = $product->tags_price / $divider;
                    $price_in_dollar_compare = $price_in_currency * $divider;
                    $quantity_price = $price_in_dollar_compare * 1;
                    $payable_amount = $payable_amount + $quantity_price;
                    $vendor_payable_amount = $vendor_payable_amount + $quantity_price;
                    $vendor_payable_amount = $vendor_payable_amount - $loyalty_amount_saved ?? 0;
                    if ($product['taxCategory']) {
                        foreach ($product['taxCategory']['taxRate'] as $tax_rate_detail) {
                            $rate                  = round($tax_rate_detail->tax_rate); // 2
                            $tax_amount            = ($price_in_dollar_compare * $rate) / 100;  // 20/100
                            $product_tax           = $payable_amount * $rate / 100;
                            $payable_amount        = $payable_amount + $product_tax;
                            $taxable_amount        = $taxable_amount + $product_tax;
                        }

                    }
                    // $product->vendor_payable_amount = $vendor_payable_amount;
                    // $product->rate = $rate;
                    // $product->price_in_currency = $price_in_currency;
                    // $product->price_in_dollar_compare = $price_in_dollar_compare;
                    $product->tax_rate =  $tax_amount;
                    $product->total_tags_price = decimal_format($product->total_tags_price + $taxable_amount);
                    $product->tags_price = decimal_format($product->tags_price);
                    // $product->payable_amount =  $payable_amount;
                    $product->taxable_amount =  $taxable_amount;
                    $product->wallet_amount_used = "0.00";
                    if($user){
                        if($user->balanceFloat > 0){
                            if($clientCurrency){
                                $wallet_amount_used = $user->balanceFloat * $clientCurrency->doller_compare;
                            }
                            if($wallet_amount_used > $product->total_tags_price ){
                                $wallet_amount_used = $product->total_tags_price ;
                            }
                            $product->wallet_amount_used = decimal_format($wallet_amount_used);
                        }
                        $product->remaining_amount =  decimal_format($product->total_tags_price - $product->wallet_amount_used);
                        $product->total_tags_price =  $product->remaining_amount;
                    }
                }
                if( $total_price > 0 && $preferences->tip_before_order == 1){
                $response['tips'] = array(
                    ['label' => '5%', 'value' => decimal_format(0.05 * $total_price)],
                    ['label' => '10%', 'value' => decimal_format(0.1 * $total_price)],
                    ['label' => '15%', 'value' => decimal_format(0.15 * $total_price)]
                    );
                }
            }
            $response['vendor'] = $vendor;
            $response['products'] = $products;
            $response['loyalty_amount_saved'] = $loyalty_amount_saved??0.00;
             return response()->json(['status','data' => $response]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLine(), 400);
        }
    }


    public function getTaxes()
    {
        /* Getting All Taxes available and making TaxRate array according to requirement */
        $taxes=TaxRate  ::all();
        $taxRates=array();
        foreach($taxes as $tax){
            $taxRates[$tax->id]=['tax_rate'=>$tax->tax_rate,'tax_amount'=>$tax->tax_amount];
        }
        return $taxRates;
    }


    public function postCabProductById(Request $request)
    {
        try
        {
            $user = Auth::user();
            $product_id = $request->product_id;
            $language_id = $user->language;
            $preferences = ClientPreference::where('id', '>', 0)->first();
            $preferences->is_cab_pooling = getAdditionalPreference(['is_cab_pooling'])['is_cab_pooling'];

            $taxRates = $this->getTaxes();
            $taxCharges = 0;
            $service_charge_tax = 0;
            $product_tax = 0;

            if(!empty($user)){
                $client_timezone = DB::table('clients')->first('timezone');
                $user->timezone = $client_timezone->timezone ?? $user->timezone;
            }
            $recurring = '';
            $recurringDays = 0;
            if($request->recurringformPost)
            {
            $recurring = recurringCalculationFunction($request);

            $recurringDays  = $recurring->daysCnt??1;
            }

            $schedule_datetime_del = '';
            if(isset($request->schedule_date_delivery) && !empty($request->schedule_date_delivery)) {
                $schedule_datetime_del = Carbon::parse($request->schedule_date_delivery, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
            }else{
                $schedule_datetime_del = Carbon::now()->timezone('UTC')->format('Y-m-d H:i:s');
            }

            $product = Product::with(['taxCategory','category.categoryDetail','media.image', 'vendor', 'tollpass', 'travelmode', 'emissiontype', 'translation' => function($q) use($language_id){
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $language_id);
                            },'variant' => function($q) use($language_id){
                                $q->select('id','sku', 'product_id', 'quantity', 'price', 'barcode');
                                $q->groupBy('product_id');
                            }])->select('products.id', 'products.sku', 'products.requires_shipping', 'products.sell_when_out_of_stock', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.Requires_last_mile', 'products.averageRating', 'products.category_id','products.tags', 'products.seats_for_booking', 'products.available_for_pooling', 'products.is_toll_tax', 'products.travel_mode_id', 'products.toll_pass_id', 'products.emission_type_id','products.tax_category_id')->where('products.id', $product_id)->where('products.is_live', 1)->first();
            $image_url = $product->media->first() ? $product->media->first()->image->path['image_fit'].'360/360'.$product->media->first()->image->path['image_path'] : '';
            $product->image_url = $image_url;
            $tags_price = $this->getDeliveryFeeDispatcher($request, $product, $schedule_datetime_del);
            // \Log::info(json_encode($tags_price));

            if($recurringDays)
            {
                $tags_price['delivery_fee'] = decimal_format($tags_price['delivery_fee'] * $recurringDays);
                $product->daysCnt = $recurringDays;
                $product->selectedCustomdates = $recurring->selectedCustomdates;
                $product->schedule_time = $recurring->schedule_time;
            }

            $product->service_charge_amount  = ($product->vendor->fixed_service_charge == 1)?$product->vendor->service_charge_amount:0.00;

            $product->original_tags_price = decimal_format($tags_price['delivery_fee']);
            $product->tags_price = decimal_format($tags_price['delivery_fee']);
            $product->toll_fee = decimal_format($tags_price['toll_fee']);

            $product->distance = decimal_format($tags_price['distance']);
            $product->duration = decimal_format($tags_price['duration']);
            $product->min_tags_price = decimal_format($tags_price['min_delivery_fee']);

            //for cab pooling
            $product->seats_for_booking = ($product->seats_for_booking > 0)?$product->seats_for_booking:1;
            $no_seats_for_pooling = isset($request->no_seats_for_pooling)?$request->no_seats_for_pooling:1;
            $product->no_seats_for_pooling = $no_seats_for_pooling;
            if(!empty($request->is_cab_pooling) && $request->is_cab_pooling == 1 && !empty($preferences) && $preferences->is_cab_pooling == 1)
            {
                $product->original_tags_price = decimal_format(($product->original_tags_price/$product->seats_for_booking)*$no_seats_for_pooling);
                $product->tags_price = decimal_format(($product->tags_price/$product->seats_for_booking)*$no_seats_for_pooling);
                $product->toll_fee = decimal_format(($product->toll_fee/$product->seats_for_booking)*$no_seats_for_pooling);
            }//------

            $product->service_charge_amount  = 0.00;
            if($product->vendor->fixed_service_charge)
            {
                $product->service_charge_amount  =  $product->vendor->service_charge_amount??0.00;
            }else{
                if($product->vendor->service_fee_percent>0){

                    $product->service_charge_amount  = $product->tags_price * $product->vendor->service_fee_percent/100;
                }
            }


            $product->total_tags_price = decimal_format($product->tags_price + $product->toll_fee + $product->service_charge_amount);



            $customerCurrency = ClientCurrency::where('is_primary', 1)->first();
            $price_in_doller_compare = $product->total_tags_price  * $customerCurrency->doller_compare;

            // dd($product->taxCategory);
            //Add Tax on product
            $taxData = array();
            if (!empty($product->taxCategory) && count($product->taxCategory->taxRate) > 0) {
                foreach ($product->taxCategory->taxRate as $tckey => $tax_value) {
                    $rate = $tax_value->tax_rate;
                    $product_tax = ($price_in_doller_compare * $rate) / 100;

                    $taxData[$tckey]['identifier'] = $tax_value->identifier;
                    $taxData[$tckey]['rate'] = $rate;
                    $taxData[$tckey]['product_tax'] = decimal_format($product_tax);
                    $payable_amount = $product->total_tags_price + $product_tax;
                    $product->product_tax = decimal_format($product_tax);
                    $product->product_tax_name = $tax_value->identifier .' '.$rate.'%';
                    $product->total_tags_price = $payable_amount;
                    $taxCharges = $taxCharges + $product_tax;
                }
            }
            // dd($price_in_doller_compare);


            $service_charges_tax_rate = 0;
                if($product->vendor->service_charges_tax_id!=null){
                    if(isset($taxRates[$product->vendor->service_charges_tax_id])){
                           $service_charges_tax_rate=$taxRates[$product->vendor->service_charges_tax_id]['tax_rate'];
                    }
                }

            if($product->service_charge_amount && $service_charges_tax_rate)
            {
                $service_charge_tax = ($product->service_charge_amount * $service_charges_tax_rate) /100;
                $taxCharges = $taxCharges + $service_charge_tax;
                $product->total_tags_price = $product->total_tags_price  + $service_charge_tax;
            }


            $other_taxes=$taxCharges;
            $other_taxes_string='service_charge_tax:'.($service_charge_tax??0).',product_tax_fee:'.($product_tax??0);
            $product->total_other_taxes = $other_taxes??0;
            $product->total_other_taxes_string = $other_taxes_string;



            $product->name = $product->translation->first() ? $product->translation->first()->title :'';
            $product->description = $product->translation->first() ? $product->translation->first()->body_html :'';
            $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
            $product->faqlist = count($product->ProductFaq);
            if(isset($request->rider_id) && $request->rider_id)
            {
                $rider = Rider::where('id',$request->rider_id)->first();
                $product->friend_name = $rider->first_name.(!is_null($rider->last_name) ? " ".$rider->last_name : "");
                // $product->friend_phone_name = "+".$rider->dial_code.$rider->phone_number;
                $product->friend_phone_name = $rider->phone_number;
            }
            foreach ($product->variant as $k => $v) {
                $product->variant[$k]->price = $product->total_tags_price;
                $product->variant[$k]->toll_fee = $product->toll_fee;
                $product->variant[$k]->multiplier = 1;
            }
            $loyalty_amount_saved = 0;
            $redeem_points_per_primary_currency = '';
            $loyalty_card = LoyaltyCard::where('status', '0')->first();
            if ($loyalty_card) {
                $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
            }
            $loyalty_points_used = 0;
            $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
            if ($order_loyalty_points_earned_detail) {
                $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                    $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                }
            }
            $product->loyalty_amount_saved = decimal_format((float)$loyalty_amount_saved ?? 0);

            if($product->loyalty_amount_saved > $product->total_tags_price)
            $product->loyalty_amount_saved = $product->total_tags_price;

            $subscription_features = array();
            $user_subscription = null;
            $user = Auth::user();


            $product->subscription_discount = 0;
            $product->subscription_percent_value = 0;
            if ($user) {
                $now = Carbon::now()->toDateTimeString();
                $user_subscription = SubscriptionInvoicesUser::with('features')
                    ->select('id', 'user_id', 'subscription_id')
                    ->where('user_id', $user->id)
                    ->where('end_date', '>', $now)
                    ->orderBy('end_date', 'desc')->first();
                if ($user_subscription) {
                    foreach ($user_subscription->features as $feature) {
                        if ($feature->feature_id == 2) {
                            $product->subscription_discount = $product->total_tags_price - ($feature->percent_value * $product->total_tags_price / 100);
                            $product->subscription_percent_value = $feature->percent_value;
                        }
                    }
                }
            }

            return $this->successResponse($product);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLine(), $e->getCode());
        }

    }



    /**
     * list of vehicles details
    */
     /**     * Get Company ShortCode     *     */
     public function getListOfVehicles(Request $request, $cid = 0){
        try{

            if($cid == 0){
                return response()->json(['error' => __('No record found.')], 404);
            }
            $userid = Auth::user()->id;
            $langId = Auth::user()->language;

            $user = Auth::user();
            $schedule_datetime_del = '';
            if (isset($request->schedule_date_delivery) && !empty($request->schedule_date_delivery)) {
                $schedule_datetime_del = Carbon::parse($request->schedule_date_delivery, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
            }else{
                $schedule_datetime_del = Carbon::now()->timezone($user->timezone)->format('Y-m-d H:i:s');
            }

            $category = Category::with(['tags','type'  => function($q){
                            $q->select('id', 'title as redirect_to');
                        },
                        'childs.translation'  => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                            ->where('category_translations.language_id', $langId);
                        },
                        'translation' => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                            ->where('category_translations.language_id', $langId);
                        }])
                        ->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products')
                        ->where('id', $cid)->first();


            if(!$category){
                return response()->json(['error' => 'No record found.'], 200);
            }
            $response['category'] = $category;
            $response['listData'] = $this->listData($langId, $cid, $category->type->redirect_to, $userid,$request, $schedule_datetime_del);
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }

    }

    public function listData($langId, $category_id, $type = '', $userid,$request, $schedule_datetime_del=''){
        if ($type == 'Pickup/Delivery') {
            $category_details = [];
            $delivercharge = $this->getDeliveryFeeDispatcher($request, null, $schedule_datetime_del);
            $deliver_charge = $delivercharge['delivery_fee']??0.00;
            $toll_charge = $delivercharge['toll_fee']??0.00;
            $category_list = Category::where('parent_id', $category_id)->get();
            foreach ($category_list as $category) {
                $category_details[] = array(
                    'id' => $category->id,
                    'name' => $category->slug,
                    'icon' => $category->icon,
                    'image' => $category->image,
                    'price' => $deliver_charge + $toll_charge,
                    'toll_price' => $toll_charge,
                );
            }
            return $category_details;
        }
        else{
            $arr = array();
            return $arr;
        }
    }


     # get delivery fee from dispatcher
     public function getDeliveryFeeDispatcher($request, $product=null, $schedule_datetime_del=''){
        try {
                $dispatch_domain = $this->checkIfPickupDeliveryOn();
                if ($dispatch_domain && $dispatch_domain != false)
                {
                    $all_location = array();
                    $postdata =  ['locations' => $request->locations,'agent_tag' => $product->tags??'', 'schedule_datetime_del' => $schedule_datetime_del, 'toll_passes' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->tollpass)?$product->tollpass->toll_pass:'IN_FASTAG':'IN_FASTAG'), 'VehicleEmissionType' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->emissiontype)?$product->emissiontype->emission_type:'GASOLINE':'GASOLINE'), 'travelMode' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->travelmode)?$product->travelmode->travelmode:'TAXI':'TAXI')];
                    $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                                                'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                                                'content-type' => 'application/json']
                                                    ]);
                    $url = $dispatch_domain->pickup_delivery_service_key_url;
                    $res = $client->post($url.'/api/get-delivery-fee',
                        ['form_params' => ($postdata)]
                    );
                    $response = json_decode($res->getBody(), true);
                    if($response && $response['message'] == 'success'){
                        return array('delivery_fee' => $response['total'], 'toll_fee' => isset($response['toll_fee'])?((!empty($product) && $product->is_toll_tax == 1)?$response['toll_fee']:0.00):0.00, 'distance' => isset($response['total_distance']) ? $response['total_distance'] : 0, 'duration' => isset($response['total_duration']) ? $response['total_duration'] :0, 'min_delivery_fee' => isset($response['total_minimum']) ? $response['total_minimum'] : 0, 'max_delivery_fee' => isset($response['total_maximum']) ? $response['total_maximum'] : 0);
                    }else{
                        return array('delivery_fee' => 0, 'toll_fee' => 0, 'distance' => 0, 'duration' => 0, 'min_delivery_fee' => 0, 'max_delivery_fee' => 0);
                    }

                }
            }
            catch(\Exception $e){

            }
    }
    # check if last mile delivery on
    public function checkIfPickupDeliveryOn(){
        $preference = ClientPreference::first();
        if($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url))
            return $preference;
        else
            return false;
    }




    /**
     * create order for booking
    */
     public function createOrder(Request $request){

        // \Log::info('request data');
        // \Log::info($request->all());
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $order_place = $this->orderPlaceForPickupDelivery($request);

            if($order_place['data']['recurring_booking_time'])
            {
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Recurring Order placed successfully.'
                ]);
            }



            if($order_place && $order_place['status'] == 200){
                if (($request->payment_option_id == 1) || ($request->payment_option_id == 42) || (( $request->has('transaction_id') ) && (!empty($request->transaction_id))) || (( $request->has('is_postpay')) && ($request->is_postpay==1))){
                    $data = [];
                    $order = $order_place['data'];
                    $request_to_dispatch = $this->placeRequestToDispatch($request, $order, $request->vendor_id);
                    if($request_to_dispatch && isset($request_to_dispatch['task_id']) && $request_to_dispatch['task_id'] > 0){
                        // DB::commit();
                        $order_place['data']['dispatch_traking_url'] = $request_to_dispatch['dispatch_traking_url'];


                    }else{
                        DB::rollback();
                        return $request_to_dispatch;
                    }
                }else if($request->payment_option_id == 48){
                    $order = $order_place['data'];
                    $request_to_dispatch = $this->placeRequestToDispatch($request, $order, $request->vendor_id);
                    if($request_to_dispatch && isset($request_to_dispatch['task_id']) && $request_to_dispatch['task_id'] > 0){
                        // DB::commit();
                        $order_place['data']['dispatch_traking_url'] = $request_to_dispatch['dispatch_traking_url'];
                        $order_place['data']['user_name'] = $user->email;
                        $order_place['data']['phone_number'] = '+'.$user->dial_code.''.$user->phone_number;


                        // return  $order_place;
                    }
                    else{
                        DB::rollback();
                        return $request_to_dispatch;
                    }
                }
            }
            else{
                DB::rollback();
                return $order_place;
            }

            DB::commit();
            // if(@$order_place['data']['recurring_booking_time']!=null)
            // {
                if(@$request->share_ride_users && count($request->share_ride_users)>0)
                {
                    // $share_ride_users = Rider::whereIn('id',$request->share_ride_users)->get();

                    foreach($request->share_ride_users as $share_ride_users)
                    {
                        $share_ride_users = (object)$share_ride_users;
                        $dialCode = empty($share_ride_users->dial_code) ? '+91' : null;
                        $phone = $dialCode.$share_ride_users->phone_number;
                        $msg = "Hi ".($share_ride_users->first_name??'User').", ".$user->name." has booked a ride. Tracking url is ".$request_to_dispatch['dispatch_traking_url']??null;
                        $send = $this->sendSms('', '', '', '', $phone, $msg);
                    }
                }
            // }

               //Send sendNotificationToCustomer
               if (isset($request->schedule_time) && !empty($request->schedule_time))
               {
                   $order_number = $order_place['data']->order_number??$order_place['data']['order_number'];
                   $device_token = UserDevice::whereUserId($user->id)->orderBy('id','desc')->value('device_token');
                   sendNotificationToCustomer($device_token,$order_number);
               }

               return  $order_place;

        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

    }


    //Call Notification to driver api for after create order
    public function createOrderNotification(Request $request){

        try {

            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            if ($dispatch_domain && $dispatch_domain != false) {

                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                'content-type' => 'application/json']
                                    ]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->post(
                    $url.'/api/task/callNotification',
                            ['form_params' => (
                                    [   'order_id'=>$request->order_id,
                                        'call_notification'=>1
                                    ]
                            )]
                        );
                $response = json_decode($res->getBody(), true);

                return $response;
            }

        }catch(\Exception $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }


    }


    // order place for pickup delivery

    public function orderPlaceForPickupDelivery($request){
        $total_amount = 0;
        $total_discount = 0;
        $taxable_amount = 0;
        $payable_amount = 0;
        $order = '';
        $user = Auth::user();
        $action = 'pick_drop';
        $luxury_option = LuxuryOption::where('title', $action)->first();
        $request->address_id = $request->address_id ??null;
        $request->payment_option_id = $request->payment_option_id ??1;
        if ($user) {
            $loyalty_amount_saved = 0;
            $total_service_fee = 0;
            $total_toll_amount = 0;
            $redeem_points_per_primary_currency = '';
            $loyalty_points_used = 0;
            $loyalty_card = LoyaltyCard::where('status', '0')->first();
            if ($loyalty_card) {
                $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
            }
            $client_preference = ClientPreference::first();
            if ($client_preference->verify_email == 1) {
                if ($user->is_email_verified == 0) {
                    $data = [];
                    $data['status'] = 404;
                    $data['message'] =  'Your account is not verified.';
                    return $data;
                }
            }
            if ($client_preference->verify_phone == 1) {
                if ($user->is_phone_verified == 0) {
                    $data = [];
                    $data['status'] = 404;
                    $data['message'] =  'Your phone is not verified.';
                    return $data;
                }
            }
            $cart = Product::where('id', $request->product_id)->first();
            if ($cart) {
                $loyalty_points_used = 0;
                $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
                if ($order_loyalty_points_earned_detail) {
                    $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                    if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                        $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                    }
                }
                $addons = null;
                if(!empty($request->addons_ids) && is_array($request->addons_ids)){
                    $addons = AddonOption::whereIN('id', $request->addons_ids)->get();
                }
                // $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
                // if ($order_loyalty_points_earned_detail) {
                //     $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                //     if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                //         $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                //     }
                // }
                $payment_option = $request->payment_option_id;
                if($request->payment_option_id == 2){
                    $payment_option = 1;
                }
                $order = new Order;
                $order->user_id = $user->id;
                $order->order_number = generateOrderNo();
                $order->address_id = $request->address_id;
                $order->payment_option_id = $payment_option;
                /*book for a friend*/
                $order->type = $request->bookingType ?? 0;
                $order->friend_name = $request->friendName;
                $order->friend_phone_number = $request->friendPhoneNumber;
                $order->luxury_option_id = $luxury_option->id;
                $order->rental_hours = $request->rental_hours ?? 0;

                $order->is_postpay = (isset($request->is_postpay))?$request->is_postpay:0;
                $order->total_other_taxes   = ($request->other_taxes_string)?$request->other_taxes_string:'';

                $schedule_datetime_del = NULL;
                if (isset($request->schedule_time) && !empty($request->schedule_time)) {
                    $schedule_datetime_del = Carbon::parse($request->schedule_time, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                }
                $recurringformPost = '';
                if(isset($request->recurringformPost) && !empty($request->recurringformPost))
                {
                    //This Function Return objected array of recurring data
                    $recurringformPost = recurringCalculationFunction($request);
                     //Check if recurring_booking_type,recurring_week_day,recurring_week_type,recurring_day_data,recurring_booking_time coulmn exists in table
                    $order->recurring_booking_type  =$recurringformPost->action??null;
                    $order->recurring_week_day      =$recurringformPost->weekTypes??null;
                    $order->recurring_week_type     =$recurringformPost->weekTypes??null;
                    $order->recurring_day_data      =$recurringformPost->selectedCustomdates??null;
                    $order->recurring_booking_time  =$recurringformPost->schedule_time??null;
                    $order->scheduled_date_time     =Null;

                }else{

                    $order->scheduled_date_time = $schedule_datetime_del;

                }

                $returnBookingTime = null;
                if (!empty($request->return_booking_time)) {
                    $returnBookingTime = Carbon::parse($request->return_booking_time, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                }
                $order->scheduled_date_time = $schedule_datetime_del??NULL;
                $order->specific_instructions = $request->task_description;
                $order->recurring_booking_time = $returnBookingTime;
                $order->recurring_week_type = $returnBookingTime ? 2 : null; //once
                $order->flight_no = $request->flight_number;
                $order->adults = $request->number_of_adult;
                $order->name_sign_board = $request->name_sign_board;
                $order->save();

                // save pickup delivery task
                $order_location = new OrderLocations();
                $order_location->order_id = $order->id;
                $order_location->product_id = $request->product_id;
                $order_location->vendor_id = $request->vendor_id;
                $order_location->phone_number = $request->phone_number ?? null;
                $order_location->email = $request->email ?? null;
                $order_location->tasks = json_encode($request->tasks);
                $order_location->save();

                $customerCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
                $clientCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
                $vendor = Vendor::whereHas('product', function ($q) use ($request) {
                    $q->where('id', $request->product_id);
                })->select('*','id as vendor_id')->orderBy('created_at', 'asc')->first();
                $vendor_id = $vendor->id;
                $product = Product::where('id',$request->product_id)->with('pimage', 'variants', 'taxCategory.taxRate', 'addon')->first();
                $total_delivery_fee = 0;
                $delivery_fee = 0;
                $vendor_payable_amount = 0;
                $vendor_discount_amount = 0;
                $order_vendor = new OrderVendor;
                $order_vendor->status = 0;
                $order_vendor->user_id= $user->id;
                $order_vendor->order_id= $order->id;
                $order_vendor->vendor_id= $vendor->id;
                $order_vendor->save();
                $variant = $product->variants->where('product_id', $request->product_id)->first();
                $variant->price = $request->tags_amount;
                $variant->toll_price = $request->tollamount;
                $quantity_price = 0;
                $divider = (empty($clientCurrency->doller_compare) || $clientCurrency->doller_compare < 0) ? 1 : $clientCurrency->doller_compare;
                $divider = isset($divider) ? $divider : 1;
                $price_in_currency = $request->tags_amount / $divider;
                $price_in_dollar_compare = $price_in_currency * $divider;
                $quantity_price = $price_in_dollar_compare * 1;
                $payable_amount = $payable_amount + $quantity_price;
                $vendor_payable_amount = $vendor_payable_amount + $quantity_price;
                $product_taxable_amount = 0;
                $product_payable_amount = 0;
                $vendor_taxable_amount = 0;
                // if ($product['tax_category']) {
                //     foreach ($product['tax_category']['tax_rate'] as $tax_rate_detail) {
                //         $rate = round($tax_rate_detail->tax_rate);
                //         $tax_amount = ($price_in_dollar_compare * $rate) / 100;
                //         $product_tax = $quantity_price * $rate / 100;
                //         $taxable_amount = $taxable_amount + $product_tax;
                //         $payable_amount = $payable_amount + $product_tax;
                //         $vendor_payable_amount = $vendor_payable_amount;
                //     }
                // }

                if ($request->other_taxes) {
                    $payable_amount = $payable_amount + $request->other_taxes;
                }

                $vendor_taxable_amount += $request->other_taxes;
                $total_amount += $variant->price;
                $order_product = new OrderProduct;
                $order_product->order_vendor_id = $order_vendor->id;
                $order_product->order_id = $order->id;
                $order_product->price = $variant->price;
                $order_product->toll_price = $variant->toll_price;
                $order_product->quantity = 1;
                $order_product->vendor_id = $vendor->id;
                $order_product->product_id = $product->id;
                $order_product->created_by = null;
                $order_product->variant_id = $variant->id;
                $order_product->product_name = $product->sku;
                $order_product->no_seats_for_pooling = (isset($request->is_cab_pooling) && $request->is_cab_pooling== 1 && isset($request->no_seats_for_pooling))?$request->no_seats_for_pooling:0;
                $order_product->is_cab_pooling = isset($request->is_cab_pooling)?$request->is_cab_pooling:0;
                $order_product->is_one_push_booking = isset($request->is_one_push_booking)?$request->is_one_push_booking:0;

                if(isset($request->user_product_order_form) && !empty($request->user_product_order_form)){
                    $user_product_order_form = json_encode($request->user_product_order_form);
                }
                else{
                    $user_product_order_form = null;
                }

                $order_product->user_product_order_form = $user_product_order_form;
                if ($product->pimage) {
                    $order_product->image = $product->pimage->first() ? $product->pimage->first()->path : '';
                }
                $order_product->save();

                if(!empty($addons)){
                    foreach($addons as $addon){
                        $orderAddon = new OrderProductAddon();
                        $orderAddon->addon_id = $addon->addon_id;
                        $orderAddon->option_id = $addon->id;
                        $orderAddon->order_product_id = $order_product->id;
                        $orderAddon->save();
                        $payable_amount += $addon->price;
                    }
                }



                $coupon_id = null;
                $coupon_name = null;
                $actual_amount = $vendor_payable_amount;
                if ($request->coupon_id) {
                    $coupon = Promocode::find($request->coupon_id);
                    $coupon_id = $coupon->id;
                    $coupon_name = $coupon->name;
                    if ($coupon->promo_type_id == 2) {
                        $coupon_discount_amount = $coupon->amount;
                        $total_discount += $coupon_discount_amount;
                        $vendor_payable_amount -= $coupon_discount_amount;
                        $vendor_discount_amount +=$coupon_discount_amount;
                    } else {
                        $coupon_discount_amount = ($quantity_price * $coupon->amount / 100);
                        $final_coupon_discount_amount = $coupon_discount_amount * $clientCurrency->doller_compare;
                        $total_discount += $final_coupon_discount_amount;
                        $vendor_payable_amount -=$final_coupon_discount_amount;
                        $vendor_discount_amount +=$final_coupon_discount_amount;
                    }
                }
                $total_toll_amount +=(isset($request->tollamount))?$request->tollamount:0.00;
                $total_service_fee +=(isset($request->servicechargeamount))?$request->servicechargeamount:0.00;

                $vendor_payable_amount +=(isset($request->tollamount))?$request->tollamount:0.00;
                $vendor_payable_amount +=(isset($request->servicechargeamount))?$request->servicechargeamount:0.00;
                $order_vendor->coupon_id = $coupon_id;
                $order_vendor->coupon_code = $coupon_name;
                $order_vendor->order_status_option_id = 1;
                $order_vendor->subtotal_amount = $actual_amount;
                $order_vendor->payable_amount = $vendor_payable_amount;
                $order_vendor->taxable_amount = $vendor_taxable_amount;
                $order_vendor->discount_amount= $vendor_discount_amount;
                $order_vendor->payment_option_id = $request->payment_option_id;
                $order_vendor->toll_amount = (isset($request->tollamount))?$request->tollamount:0.00;
                $order_vendor->service_fee_percentage_amount = (isset($request->servicechargeamount))?$request->servicechargeamount:0.00;
                $vendor_info = Vendor::where('id', $vendor_id)->first();
                if ($vendor_info) {
                    if (($vendor_info->commission_percent) != null && $vendor_payable_amount > 0) {
                        $order_vendor->admin_commission_percentage_amount = round($vendor_info->commission_percent * ($vendor_payable_amount / 100), 2);
                    }
                    if (($vendor_info->commission_fixed_per_order) != null && $vendor_payable_amount > 0) {
                        $order_vendor->admin_commission_fixed_amount = $vendor_info->commission_fixed_per_order;
                    }
                }

                $order_status = new VendorOrderStatus();
                $order_status->order_id = $order->id;
                $order_status->vendor_id = $vendor_id;
                $order_status->order_status_option_id = 1;
                $order_status->order_vendor_id = $order_vendor->id;
                $order_status->save();

                $loyalty_points_earned = LoyaltyCard::getLoyaltyPoint($loyalty_points_used, $payable_amount);


                $order->total_amount = $total_amount;
                $order->total_discount = $total_discount;
                $order->taxable_amount = $taxable_amount;
                if ($loyalty_amount_saved > 0) {
                    if ($payable_amount < $loyalty_amount_saved) {
                        $loyalty_amount_saved =  $payable_amount;
                        $loyalty_points_used = $payable_amount * $redeem_points_per_primary_currency;
                    }
                }

                $order->total_delivery_fee = $total_delivery_fee;
                $order->loyalty_points_used = $loyalty_points_used;
                $order->loyalty_amount_saved = $loyalty_amount_saved;
                $order->total_toll_amount    = $total_toll_amount;
                $order->total_service_fee    = $total_service_fee;

                $vendor_payable_amount = $vendor_payable_amount - $loyalty_amount_saved ?? 0;
                $order_vendor->payable_amount = $vendor_payable_amount;
                if ($product['taxCategory']) {
                    foreach ($product['taxCategory']['taxRate'] as $tax_rate_detail) {
                        $rate                  = round($tax_rate_detail->tax_rate);
                        $tax_amount            = ($price_in_dollar_compare * $rate) / 100;
                        $product_tax           = $payable_amount * $rate / 100;
                        $payable_amount        = $payable_amount + $product_tax;
                        $taxable_amount        = $taxable_amount + $product_tax;
                    }
                }
                $order_vendor->taxable_amount = $taxable_amount ?? 0;
                $order_vendor->save();

                $now = Carbon::now()->toDateTimeString();
                $user_subscription = SubscriptionInvoicesUser::with('features')
                ->select('id', 'user_id', 'subscription_id')
                ->where('user_id', $user->id)
                ->where('end_date', '>', $now)
                ->orderBy('end_date', 'desc')->first();

                if ($user_subscription) {
                    foreach ($user_subscription->features as $feature) {
                        if ($feature->feature_id == 2) {
                            $subscriptionAmount = $request->tags_amount - ($feature->percent_value * $request->tags_amount / 100);
                            $order->subscription_discount = $request->tags_amount - $subscriptionAmount;
                            $order->payable_amount = $subscriptionAmount + $total_toll_amount + $total_service_fee;
                        }
                    }
                }else{
                    $order->payable_amount = $delivery_fee + $payable_amount - $total_discount - $loyalty_amount_saved + $total_toll_amount + $total_service_fee;
                }

                // $order->payable_amount = $request->remaing_amount;
                $order->loyalty_points_earned = $loyalty_points_earned['per_order_points'];
                $order->loyalty_membership_id = $loyalty_points_earned['loyalty_card_id'];
                if (isset($request->transaction_id) && (!empty($request->transaction_id))) {
                    $order->payment_status = 1;
                }

                $wallet_amount_used = 0;
                $ex_gateways_wallet = [4,36,40,41,22]; // stripe,mycash,userede,openpay,ccavenue
                if ($user->balanceFloat > 0) {
                    $wallet = $user->wallet;
                    $wallet_amount_used = $user->balanceFloat;
                    if ($wallet_amount_used > $order->payable_amount) {
                        $wallet_amount_used = $order->payable_amount;
                    }
                    $order->wallet_amount_used = $wallet_amount_used;
                    // Deduct wallet amount if payable amount is successfully done on gateway
                    if (($wallet_amount_used > 0) && (! in_array($request->payment_option_id, $ex_gateways_wallet))) {
                        $wallet->withdrawFloat($order->wallet_amount_used, [
                            'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>'
                        ]);
                    }
                }
                $order->payable_amount = $order->payable_amount - $wallet_amount_used;
                if ((isset($request->tip)) && ($request->tip != '') && ($request->tip > 0)) {
                    $tip_amount = $request->tip;
                    $tip_amount = ($tip_amount / $customerCurrency->doller_compare) * $clientCurrency->doller_compare;
                    $order->tip_amount = decimal_format($tip_amount);
                }
                if(isset($request->bid_task_type) && ($request->bid_task_type == 'bid_ride_request'))
                {
                    $order->total_amount  = $request->amount;
                    $order->payable_amount  = $request->amount;
                }
                $order->save();

                 /** for Recurring Service */
                 if(!empty($order->recurring_booking_time) && !empty($request->recurringformPost)){
                    DB::commit();
                    $this->saveOrderLongTermServiceSchedule($order,$order_product->id);
                }

                if (($request->payment_option_id != 1) && ($request->payment_option_id != 2) && (!empty($request->transaction_id))) {
                    $payment = new Payment();
                    $payment->date = date('Y-m-d');
                    $payment->order_id = $order->id;
                    $payment->transaction_id = $request->transaction_id;
                    $payment->balance_transaction = $order->payable_amount;
                    $payment->type = 'pickup/delivery';
                    $payment->save();
                }
            }
            $data = [];
            $data['status'] = 200;
            $data['message'] =  __('Order Placed');
            $data['recurring_booking_time'] = @$order->recurring_booking_time??null;
            $data['data'] = $order;
            return $data;
        }
    }

     // order update for pickup delivery
     public function orderUpdateAfterPaymentPickupDelivery($request){
            $order = Order::where('order_number', $request->order_number)->first();
            $vendorId = OrderVendor::where('order_id',$order->id)->first();
            $vendor_id = $vendorId->vendor_id;
            $productId = OrderVendorProduct::where('order_vendor_id',$vendorId->id)->select('product_id')->first();
            $tasks = OrderLocations::where('order_id',$order->id)->select('tasks')->first();
            $request->request->add(['product_id', $productId->product_id]);
            $request->request->add(['tasks', json_decode($tasks->tasks)]);
            if (!empty($request->transaction_id)) {
                $order->payment_status = 1;
            }
            $order->save();
            if ($request->payment_option_id != 1 && $request->payment_option_id != 2 && $request->has('transaction_id') && !empty($request->transaction_id)) {
                $payment = Payment::where('transaction_id',$request->transaction_id)->first();
                if(!$payment){
                    $payment = new Payment();
                }
                $payment->date = date('Y-m-d');
                $payment->order_id = $order->id;
                $payment->transaction_id = $request->transaction_id;
                $payment->balance_transaction = !empty($order->payable_amount)?$order->payable_amount:$request->amount;
                $payment->type = 'pickup/delivery';
                $payment->save();

            }

            if($request->payment_option_id = 49){
                $data['product_id']         =   $productId->product_id;
                $data['tasks']              =   json_decode($tasks->tasks);
                $request                    =   new \Illuminate\Http\Request($data);
            }
            if($request->payment_option_id = 48){
                $data['product_id']         =   $productId->product_id;
                $data['tasks']              =   json_decode($tasks->tasks);
                $request                    =   new \Illuminate\Http\Request($data);
            }
            $request_to_dispatch = $this->placeRequestToDispatch($request,$order,$vendor_id);
            if($request_to_dispatch && isset($request_to_dispatch['task_id']) && $request_to_dispatch['task_id'] > 0){
                $user = Auth::user();
                $order_place['data']['dispatch_traking_url'] = $request_to_dispatch['dispatch_traking_url'];
                $order_place['data']['user_name'] = $user->email;
                $order_place['data']['phone_number'] = '+'.$user->dial_code.''.$user->phone_number;
                return  $order_place;
            }else{
                return $request_to_dispatch;
            }



    }

    // place Request To Dispatch
    public function placeRequestToDispatch($request,$order,$vendor){
        try {
            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            $customer = Auth::user();
            $wallet = $customer->wallet;
            if ($dispatch_domain && $dispatch_domain != false) {
                $tasks = array();

                $schedule_datetime_del = NULL;
                if (isset($request->schedule_time) && !empty($request->schedule_time)) {
                    $schedule_datetime_del = $request->schedule_time;
                }
                if(empty($request->task_type) && !empty($request->schedule_time)){
                    $task_type = 'schedule';
                }else{
                    $task_type = 'now';
                }
                $vendor_details = Vendor::where('id', $vendor)->select('order_pre_time')->first();
                $order_vendor = OrderVendor::where(['order_id' => $order->id,'vendor_id' => $vendor])->first();
                $dynamic = (!empty($order_vendor->web_hook_code)) ? $order_vendor->web_hook_code : uniqid($order->id.$vendor);
                $unique = Auth::user()->code;
                $client_do = Client::where('code',$unique)->first();

                if ($request->payment_option_id == 1 && $order->payable_amount >0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order_vendor->payable_amount + $order_vendor->taxable_amount - $order->wallet_amount_used;
                } else {
                    if(checkColumnExists('orders', 'is_postpay'))
                    {
                        $cash_to_be_collected = 'Yes';
                        $payable_amount = $order_vendor->payable_amount + $order_vendor->taxable_amount - $order->wallet_amount_used;
                    }else{
                        $cash_to_be_collected = 'No';
                        $payable_amount = 0.00;
                    }
                }

                if(!empty($client_do->custom_domain)){
                    $domain = $client_do->custom_domain;
                }else{
                    $domain = $client_do->sub_domain.env('SUBMAINDOMAIN');
                }
                $call_back_url = "https://".$domain."/dispatch-pickup-delivery/".$dynamic;
                $tasks = array();
                $meta_data = '';
                $team_tag = $unique."_".$vendor;
                $product = Product::find($request->product_id);
                $order_agent_tag = $product->tags??'';
                $type = $request->bookingType ?? 0;
                $friendName=$request->friendName?? null;
                $friendPhoneNumber=$request->friendPhoneNumber?? null;
                if(empty($friendPhoneNumber)){
                    $type=0;
                }


                if ($customer->dial_code == "971") {
                    // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                    $customerno = "0" . $customer->phone_number;
                } else {
                    // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                    $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
                }

                $client_preferences_addional = ClientPreferenceAdditional::pluck('key_value','key_name');
                // FacadesLog::warning(['postdata' => $client_preferences_addional]);
                if(isset($client_preferences_addional['pickup_notification_before']) && $client_preferences_addional['pickup_notification_before'] == 1)
                {
                    $notify_hour = $client_preferences_addional['pickup_notification_before_hours'] ?? 1;
                    $reminder_hour = $client_preferences_addional['pickup_notification_before2_hours'] ?? 1;
                }
                $allocation_type = 'a';
                if(isset($request->unique_id) || isset($request->agent_id)){
                    $allocation_type = 'm';
                }
                if(isset($request->bid_task_type) && ($request->bid_task_type == 'bid_ride_request'))
                {
                    $payable_amount  = $request->amount;
                }
                $postdata =  [
                            'notify_all' => $request->send_to_all ?1: 0,
                            'order_number' =>  $order->order_number,
                            'customer_name' => $customer->name ?? 'Dummy Customer',
                            'customer_phone_number' => $customerno??rand(111111,11111),
                            'customer_dial_code' => $customer->dial_code ?? null,
                            'customer_email' => $customer->email ?? '',
                            'recipient_phone' => $request->phone_number ?? $customerno,
                            'recipient_email' => $request->email ?? $customer->email,
                            'task_description' => $request->task_description??null,
                            'allocation_type' => @$request->unique_id ? 'notify' : 'a',
                            'task_type' => $task_type,
                            'schedule_time' => $schedule_datetime_del ?? null,
                            'cash_to_be_collected' => $payable_amount??0.00,
                            'barcode' => '',
                            'call_back_url' => $call_back_url??null,
                            'order_team_tag' => $team_tag,
                            'order_agent_tag' => $order_agent_tag,
                            'task' => $request->tasks,
                            'order_time_zone' => $request->order_time_zone??null,
                            'images_array' => $request->images_array??null,
                            'type'=>$type,
                            'friend_name'=>$friendName,
                            'friend_phone_number'=>$friendPhoneNumber,
                            'vendor_id' => $vendor,
                            'order_vendor_id' => $order_vendor->id,
                            'dbname' => $client_do->database_name,
                            'order_id' => $order->id,
                            'customer_id' => $order->user_id,
                            'user_icon' => $customer->image,
                            'toll_passes' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->tollpass)?$product->tollpass->toll_pass:'IN_FASTAG':'IN_FASTAG'),
                            'VehicleEmissionType' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->emissiontype)?$product->emissiontype->emission_type:'GASOLINE':'GASOLINE'),
                            'travelMode' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->travelmode)?$product->travelmode->travelmode:'TAXI':'TAXI'),
                            'no_seats_for_pooling' =>(isset($request->is_cab_pooling) && $request->is_cab_pooling== 1 && isset($request->no_seats_for_pooling))?$request->no_seats_for_pooling:0,
                            'is_cab_pooling' => isset($request->is_cab_pooling)?$request->is_cab_pooling:0,
                            'is_one_push_booking' => isset($request->is_one_push_booking)?$request->is_one_push_booking:0,
                            'available_seats' =>isset($product)?$product->seats_for_booking:0,
                            'agent' => $request->agent_id ?? null,
                            'driver_id' => $request->driver_id ?? null,
                            'order_pre_time'=>$vendor_details->order_pre_time,
                            'driver_unique_id' => $request->unique_id ?? null,
                            'notify_hour' => $notify_hour ?? 0,
                            'reminder_hour' => $reminder_hour ?? 0,
                            'app_call' => 1,
                            'call_notification' => $request->call_notification??0
                        ];


                if($request->has('bid_task_type')){
                    $postdata['bid_task_type']    = $request->bid_task_type;
                    $postdata['accept_bid_price'] = $order->payable_amount;
                }

                //use Guzzle for send request at other panel
                $endPoints = '/api/task/create';
                $response = $this->guzzlePost($endPoints,$dispatch_domain,$postdata);

                if ($response && isset($response['task_id']) && $response['task_id'] > 0) {
                    $dispatch_traking_url = $response['dispatch_traking_url']??'';
                    $up_web_hook_code = OrderVendor::where(['order_id' => $order->id,'vendor_id' => $vendor])
                                    ->update(['web_hook_code' => $dynamic,'dispatch_traking_url' => $dispatch_traking_url]);
                    $response['dispatch_traking_url'] = $dispatch_traking_url;


                    $or_ids = OrderVendor::where(['order_id' => $order->id,'vendor_id' => $vendor])->with(['vendor'])->first();


                        $update_vendor = VendorOrderStatus::updateOrCreate([
                            'order_id' =>  $order->id,
                            'order_status_option_id' => 2,
                            'vendor_id' =>  $vendor,
                            'order_vendor_id' =>  $or_ids->id]);

                        OrderVendor::where('vendor_id', $vendor)->where('order_id', $order->id)->update(['order_status_option_id' => 2,'dispatcher_status_option_id' => 1]);

                    $update = VendorOrderDispatcherStatus::updateOrCreate(['dispatcher_id' => null,
                    'order_id' =>  $order->id,
                    'dispatcher_status_option_id' =>  1,
                    'vendor_id' =>  $vendor]);
                    $ex_gateways_wallet = [4,36,40,41,22]; // stripe,mycash,userede,openpay,ccavenue
                    if (in_array($order->payment_option_id, $ex_gateways_wallet )){
                        $wal =   $wallet->forceWithdrawFloat($order->wallet_amount_used, ['Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>']);
                    }
                }
                return $response;
            }
            }catch(\Exception $e)
            {
                $data = [];
                $data['status'] = 400;
                $data['message'] =  $e->getMessage();
                return $data;
            }
    }

      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAgents(Request $request){

        try{
            $validator = Validator::make(request()->all(), [
                'latitude' => 'required',
                'longitude' => 'required',
            ]);

            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }

            $postdata = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ];

            if(!empty($request->tag)){
                $postdata['tag'] = $request->tag;
            }

            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            $header = ['headers' => ['personaltoken' => !empty($dispatch_domain->pickup_delivery_service_key)? $dispatch_domain->pickup_delivery_service_key : "",
                'shortcode' => !empty($dispatch_domain->delivery_service_key_code)? $dispatch_domain->delivery_service_key_code : $dispatch_domain->pickup_delivery_service_key_code,
                'content-type' => 'application/json']
            ];

            $client = new GClient($header);
            $url    = !empty($dispatch_domain->delivery_service_key_url)? $dispatch_domain->delivery_service_key_url.'/api/get/agents' : $dispatch_domain->pickup_delivery_service_key_url.'/api/get/agents';
            $res = $client->post(
                $url,
                ['form_params' => (
                        $postdata
                    )]
            );
            $response = json_decode($res->getBody(), true);
            return $response;

        }catch(\Exception $e){
            $data = [];
            $data['status'] = 400;
            $data['message'] =  $e->getMessage();
            return $data;
        }

    }


      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function postPromoCodeList(Request $request){
        try {
            $promo_codes = new \Illuminate\Database\Eloquent\Collection;
            $vendor_id = $request->vendor_id;
            $validator = $this->validatePromoCodeList();
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            if(!$vendor){
                return response()->json(['error' => 'Invalid vendor id.'], 404);
            }
            $now = Carbon::now()->toDateTimeString();
            $product_ids = Product::where('vendor_id', $request->vendor_id)->where('id', $request->product_id)->pluck("id");
            $cart_products = Product::with(['variant' => function($q){
                            $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
                        }])->where('vendor_id', $request->vendor_id)->where('id', $request->product_id)->get();

            $total_minimum_spend = $request->amount??0;
            if($product_ids){
                $promo_code_details = PromoCodeDetail::whereIn('refrence_id', $product_ids->toArray())->pluck('promocode_id');
                if($promo_code_details->count() > 0){
                    $result1 = Promocode::whereIn('id', $promo_code_details->toArray())->whereDate('expiry_date', '>=', $now)->where('minimum_spend','<=',$total_minimum_spend)->where('maximum_spend','>=',$total_minimum_spend)->where('restriction_on', 0)->where('restriction_type', 0)->where('is_deleted', 0)->get();
                    $promo_codes = $promo_codes->merge($result1);
                }

                $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->where('refrence_id', $vendor_id)->pluck('promocode_id');
                $result2 = Promocode::whereIn('id', $vendor_promo_code_details->toArray())->where('restriction_on', 1)->whereHas('details', function($q) use($vendor_id){
                    $q->where('refrence_id', $vendor_id);
                })->where('restriction_on', 1)->where('is_deleted', 0)->where('minimum_spend','<=',$total_minimum_spend)->where('maximum_spend','>=',$total_minimum_spend)->whereDate('expiry_date', '>=', $now)->get();
                $promo_codes = $promo_codes->merge($result2);

            }
            return $this->successResponse($promo_codes, '', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function postVerifyPromoCode(Request $request){
        try {
            $user = Auth::user();
            $validator = $this->validatePromoCode();
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            if(!$vendor){
                return response()->json(['error' => __('Invalid vendor id.')], 404);
            }
            $cart_detail = Promocode::where('id', $request->coupon_id)->first();
            if(!$cart_detail){
                return $this->errorResponse(__('Invalid Promocode Id'), 422);
            }

            if($cart_detail->first_order_only == 1){
                $orders_count = Order::where('user_id', $user->id)->count();
                if($orders_count > 0){
                    return $this->errorResponse('Coupon Code apply only first order.', 422);
                }
            }
            $order_vendor_user_promo_count = OrderVendor::where(['coupon_id' => $request->coupon_id])->count();
            if($order_vendor_user_promo_count >= $cart_detail->limit_total){
                return $this->errorResponse(__('Coupon Code limit has been reached.'), 422);
            }
            $order_vendor_user_promo_count = OrderVendor::where(['user_id' => $user->id, 'coupon_id' => $request->coupon_id])->count();
            if($order_vendor_user_promo_count >= $cart_detail->limit_per_user){
                return $this->errorResponse(__('Coupon Code already applied.'), 422);
            }

            if($cart_detail->promo_type_id == 2){
                $cart_detail['new_amount'] = $cart_detail->amount;
                if($cart_detail['new_amount'] < 0)
                $cart_detail['new_amount'] = 0.00;
            }
            if($cart_detail->promo_type_id == 1){
                $cart_detail['new_amount'] = ($request->amount * ($cart_detail->amount/100));
                if($cart_detail['new_amount'] < 0)
                $cart_detail['new_amount'] = 0.00;
            }
            return $this->successResponse($cart_detail, __('Promotion Code Used Successfully.'), 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function postRemovePromoCode(Request $request){
        try {
            $validator = $this->validatePromoCode();
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $cart_detail = Cart::where('id', $request->cart_id)->first();
            if(!$cart_detail){
                return $this->errorResponse(__('Invalid Cart Id'), 422);
            }
            $cart_detail = Promocode::where('id', $request->coupon_id)->first();
            if(!$cart_detail){
                return $this->errorResponse(__('Invalid Promocode Id'), 422);
            }

            return $this->successResponse(null, __('Promotion Code Removed Successfully.'), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function validatePromoCodeList(){
        return Validator::make(request()->all(), [
            'vendor_id' => 'required',
        ]);
    }

    public function validatePromoCode(){
        return Validator::make(request()->all(), [
            'vendor_id' => 'required',
            'coupon_id' => 'required',
            'amount' => 'required'
        ]);
    }


    public function getOrderTrackingDetails(Request $request){
        $user = Auth::user();
        $langId = $user->language ?? 1;
        $preferences = ClientPreference::where('id', '>', 0)->first();
        $order = OrderVendor::with('orderDetail','orderDetail.orderLocation')->where('order_id',$request->order_id)
        ->with(['products.productRating.reviewFiles','products.product.translation', 'products.product.category.categoryDetail.translation' => function($q) use($langId){
            $q->where('category_translations.language_id', $langId);
        }])
        ->select('*','dispatcher_status_option_id as dispatcher_status')->first();
        $order->subtotal_amount = $order->subtotal_amount;
        $order->payable_amount = $order->payable_amount;
        $dispatch_traking_url = ($request->has('new_dispatch_traking_url') && !empty($request->new_dispatch_traking_url)) ? $request->new_dispatch_traking_url : $order->dispatch_traking_url;
        $dispatch_traking_url = str_replace('/order/', '/order-details/', $dispatch_traking_url);
        $response = Http::get($dispatch_traking_url, [
            'headers' => [
                'timezone' => $user->timezone
            ]
        ]);
        $product_id = $order->products[0]['product_id'];
        $productData = Product::with(['category.categoryDetail','taxCategory.taxRate'])->whereId($product_id)->first();

        $loyalty_amount_saved = 0;
        $total_service_fee = 0;
        $total_toll_amount = 0;
        $redeem_points_per_primary_currency = '';
        $loyalty_card = LoyaltyCard::where('status', '0')->first();
        if ($loyalty_card) {
            $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
        }

        $loyalty_points_used = 0;
        $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
        if ($order_loyalty_points_earned_detail) {
            $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
            if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
            }
        }

        $clientCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
        $payable_amount= 0;
        $vendor_payable_amount=0;
        $taxable_amount = 0;
        $tax_amount = 0;

        $divider = (empty($clientCurrency->doller_compare) || $clientCurrency->doller_compare < 0) ? 1 : $clientCurrency->doller_compare;
        $divider = isset($divider) ? $divider : 1;
        $price_in_currency = $order->subtotal_amount / $divider;
        $price_in_dollar_compare = $price_in_currency * $divider;
        $quantity_price = $price_in_dollar_compare * 1;
        $payable_amount = $payable_amount + $quantity_price;
        $vendor_payable_amount = $vendor_payable_amount + $quantity_price;
        $vendor_payable_amount = $vendor_payable_amount - $loyalty_amount_saved ?? 0;

        if ($productData['taxCategory']) {
            foreach ($productData['taxCategory']['taxRate'] as $tax_rate_detail) {
                $rate                  = round($tax_rate_detail->tax_rate); // 2
                $tax_amount            = ($price_in_dollar_compare * $rate) / 100;  // 20/100
                $product_tax           = $payable_amount * $rate / 100;
                $payable_amount        = $payable_amount + $product_tax;
                $taxable_amount        = $taxable_amount + $product_tax;
            }
        }

        $order->tax_rate =  $tax_amount;
        // $order->subtotal_amount = $order->subtotal_amount + $tax_amount;
        $order->loyalty_amount_saved = $loyalty_amount_saved ?? 0;
        $order->payable_amount =  $order->payable_amount + $tax_amount - $order->orderDetail['subscription_discount'];
        // $order->total_tags_price = decimal_format($product->total_tags_price + $taxable_amount);

        // $product->payable_amount =  $payable_amount;

        // $product->taxable_amount =  $taxable_amount;
        $now = Carbon::now()->toDateTimeString();
        $userid = Auth::user()->id;
        $subscriptionInvoiceUser = SubscriptionInvoicesUser::with('features')->whereUserId($userid)->where('end_date', '>', $now)
        ->orderBy('end_date', 'desc')->first();
        if($subscriptionInvoiceUser){
            $percentValue = $subscriptionInvoiceUser->features[0]['percent_value'];
            if(!empty($percentValue)){
                $calulateSubscription = ($percentValue / 100)* $response['order']['base_price'];
                $subscriptionPercentage = $percentValue;
                $subscriptionAmount = $calulateSubscription;
                $totalTagPriceWithSubscription = $response['order']['base_price'] - $calulateSubscription;
                $order->subscriptionPercentage = $percentValue;
                $order->subscriptionAmount = decimal_format($calulateSubscription);
                $order->payable_amount = decimal_format($totalTagPriceWithSubscription)+ $order->service_fee_percentage_amount- $loyalty_amount_saved??0.00;
            }
        }
        $order->wallet_amount_used = 0.00;
        if(isset($order->orderDetail->wallet_amount_used)){
            $order->wallet_amount_used = isset($order->orderDetail)?decimal_format($order->orderDetail->wallet_amount_used):0.00;
        }
        /*if(isset($order->orderDetail->scheduled_date_time)){*/
        /*    $order->orderDetail->scheduled_date_time = dateTimeInUserTimeZone($order->orderDetail->scheduled_date_time, $user->timezone);*/
        /*}*/

        $order->payable_amount = decimal_format($order->payable_amount - $order->wallet_amount_used);
        if($response->status() == 200){
            $type = VendorOrderDispatcherStatus::where(['order_id' =>  $order->order_id ,'vendor_id' =>$order->vendor_id ])->latest()->first();
            // OrderProductRating::where('order_id', $order->order_id)
            $order_driver_rating = OrderDriverRating::where('order_id', $request->order_id)->first();
            $order->dispatcher_status_type=  $type ?  $type->type :1;
            $response = $response->json();
            $response['tips'] = [];
            /*if(isset($response) && isset( $response['order'] ) &&  !empty($response['order']['scheduled_date_time'])){*/
            /*    $response['order']['scheduled_date_time'] = dateTimeInUserTimeZone($response['order']['scheduled_date_time'], $user->timezone);*/
            /*}*/
            if($order->orderDetail->total_amount > 0 && isset($preferences) && $preferences->tip_before_order == 1){
                $response['tips'] = array(
                    ['label' => '5%', 'value' => decimal_format(0.05 * $order->orderDetail->total_amount)],
                    ['label' => '10%', 'value' => decimal_format(0.1 * $order->orderDetail->total_amount)],
                    ['label' => '15%', 'value' => decimal_format(0.15 * $order->orderDetail->total_amount)]
                );
            }
            $response['order_details'] = $order->toArray();
            $response['order_driver_rating'] = $order_driver_rating;
            return $this->successResponse($response);
        }else{
            return $this->errorResponse('', 400, $response);
        }
    }



    # upload image in pickup & delivery
    public function uploadImagePickup(Request $request)
    {

            $validator = Validator::make($request->all(), [
                'upload_photo' => 'required|image'
            ]);
           // dd('1');
           try {
             $dispatch_domain = $this->checkIfPickupDeliveryOnCommon();
            if ($dispatch_domain && $dispatch_domain != false) {
                $files = [];
                // $dispatch_domain->pickup_delivery_service_key_code ='745e3f';
                // $dispatch_domain->pickup_delivery_service_key = 'icDerSAVT4Fd795DgPsPfONXahhTOA';
                // $dispatch_domain->pickup_delivery_service_key_url ='http://192.168.96.20:8010';
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key, 'shortcode' => $dispatch_domain->pickup_delivery_service_key_code]]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;

                $profile_photo = [];
                if ($request->hasFile('upload_photo')) {
                    $profile_photo =
                        [
                            'Content-type' => 'multipart/form-data',
                            'name' => 'upload_photo',
                            'filename' => $request->upload_photo->getClientOriginalName(),
                            'Mime-Type' => $request->upload_photo->getMimeType('image'),
                            'contents' =>  fopen($request->upload_photo, 'r'),
                        ];
                }
                if ($profile_photo == null) {
                    $profile_photo = ['name' => 'profile_photo[]', 'contents' => 'abc'];
                }
                $res = $client->post($url . '/api/upload-image-for-task', [
                    'multipart' => [
                        $profile_photo
                    ]
                ]);

                $response = json_decode($res->getBody(), true);
                return $response;
            }else{
                $data = [];
               $data['status'] = 400;
               $data['message'] =  __('Error in pickup & delivery configuration');
               return $data;
            }
        } catch (\Exception $e) {
            $data = [];
            $data['status'] = 400;
            $data['message'] =  $e->getMessage();
            return $data;
        }

    }




     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function postPromoCodeListOpen(Request $request){
        try {
            $promo_codes = new \Illuminate\Database\Eloquent\Collection;

            $now = Carbon::now()->toDateTimeString();
            $promo_code_details = PromoCodeDetail::pluck('promocode_id');
                if($promo_code_details->count() > 0){
                    $promo_codes = Promocode::whereIn('id', $promo_code_details->toArray())->whereDate('expiry_date', '>=', $now)->where('is_deleted', 0)->get();

                }


            return $this->successResponse($promo_codes, '', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function getAllRiders(Request $request)
    {
        $data = $request->all();

        $data['user_id'] = Auth::user()->id;
        if($request->isMethod('post')){
            $add = $this->riderObj->createRider($data);
        }
        $all_riders = $this->riderObj->getAllByUserId($data['user_id']);
        return response()->json(['riders' => $all_riders],200);
    }

    public function updatePickupDeliveryOrderByCustomer(Request $request){
        try {
            DB::beginTransaction();
            $user                   = Auth::user();
            $order                  = Order::where('order_number', $request->order_number)->first();
            $order_vendor           = OrderVendor::where('order_id', $order->id)->first();
            $vendor_id              = $order_vendor->vendor_id;
            $order_product          = OrderProduct::where('order_vendor_id', $order_vendor->id)->select('product_id')->first();
            $product                = Product::where('id',$order_product->product_id)->with('pimage', 'variants', 'taxCategory.taxRate', 'addon')->first();
            $tasks                  = OrderLocations::where('order_id', $order->id)->select('tasks')->first();
            $clientCurrency         = ClientCurrency::where('currency_id', $user->currency)->first();

            $redeem_points_per_primary_currency = '';
            $loyalty_card = LoyaltyCard::where('status', '0')->first();
            if ($loyalty_card) {
                $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
            }

            if(isset($request->tasks))
            {
                if(!empty($tasks))
                {
                    OrderLocations::where('order_id', $order->id)->update(['tasks' => $request->tasks]);
                }else{
                    OrderLocations::insert(['tasks' => $request->tasks, 'order_id' => $order->id, 'vendor_id' => $vendor_id, 'product_id' => $order_product->product_id]);
                }
            }

            $schedule_datetime_del = (!empty($order->scheduled_date_time)) ? $order->scheduled_date_time : NULL;
            $tags_price            = $this->getDeliveryFeeDispatcher($request, $product, $schedule_datetime_del);
            $product_tags_price    = $tags_price['delivery_fee']??0;
            $product_toll_fee      = $tags_price['toll_fee']??0;

            $product->seats_for_booking = ($product->seats_for_booking > 0)?$product->seats_for_booking:1;
            $no_seats_for_pooling = 1;
            if(isset($order->is_cab_pooling) && $order->is_cab_pooling==1)
            {
                if($product->no_seats_for_pooling > 0)
                {
                    $no_seats_for_pooling = $order_product->no_seats_for_pooling;
                }else{
                    $no_seats_for_pooling = 1;
                }
                $product_tags_price = decimal_format(($product_tags_price/$product->seats_for_booking)*$no_seats_for_pooling);
                $product_toll_fee   = decimal_format(($product_toll_fee/$product->seats_for_booking)*$no_seats_for_pooling);
            }

            $request->request->add(['product_id' => $order_product->product_id]);
            $request->request->add(['tags_amount' => $product_tags_price]);
            $request->request->add(['tollamount' => $product_toll_fee]);
            $request->request->add(['schedule_time' => $order->scheduled_date_time]);
            $request->request->add(['payment_option_id' => $order->payment_option_id]);
            $request->request->add(['task_type' => $order->task_type]);
            $request->request->add(['order_time' => $order->task_type]);
            $request->request->add(['no_seats_for_pooling' => $order_product->no_seats_for_pooling]);
            $request->request->add(['is_cab_pooling' => $order_product->is_cab_pooling]);


            $total_delivery_fee = 0;
            $delivery_fee = 0;
            $vendor_payable_amount = 0;
            $vendor_discount_amount = 0;
            $total_amount = 0;
            $total_discount = 0;
            $taxable_amount = 0;
            $payable_amount = 0;
            $loyalty_amount_saved = 0;
            $total_service_fee = 0;
            $total_toll_amount = 0;

            $loyalty_points_used;
            $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->where('id', '!=', $order->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
            if ($order_loyalty_points_earned_detail) {
                $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                    $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                }
            }

            $variant = $product->variants->where('product_id', $product->id)->first();
            $variant->price = $request->tags_amount;
            $variant->toll_price = $request->tollamount;
            $quantity_price = 0;
            $divider = (empty($clientCurrency->doller_compare) || $clientCurrency->doller_compare < 0) ? 1 : $clientCurrency->doller_compare;
            $divider = isset($divider) ? $divider : 1;
            $price_in_currency = $request->tags_amount / $divider;
            $price_in_dollar_compare = $price_in_currency * $divider;
            $quantity_price = $price_in_dollar_compare * 1;
            $payable_amount = $payable_amount + $quantity_price;
            $vendor_payable_amount = $vendor_payable_amount + $quantity_price;
            $product_taxable_amount = 0;
            $product_payable_amount = 0;
            $vendor_taxable_amount = 0;
            if ($product['tax_category']) {
                foreach ($product['tax_category']['tax_rate'] as $tax_rate_detail) {
                    $rate = round($tax_rate_detail->tax_rate);
                    $tax_amount = ($price_in_dollar_compare * $rate) / 100;
                    $product_tax = $quantity_price * $rate / 100;
                    $taxable_amount = $taxable_amount + $product_tax;
                    $payable_amount = $payable_amount + $product_tax;
                    $vendor_payable_amount = $vendor_payable_amount;
                }
            }
            $vendor_taxable_amount += $taxable_amount;
            $total_amount += $variant->price;

            $order_product->price = $variant->price;
            $order_product->toll_price = $variant->toll_price;
            $order_product->save();
            $coupon_id = null;
            $coupon_name = null;
            $actual_amount = $vendor_payable_amount;
            if (!empty($order_vendor->coupon_id)) {
                $coupon = Promocode::find($order_vendor->coupon_id);
                $coupon_id = $coupon->id;
                $coupon_name = $coupon->name;
                if ($coupon->promo_type_id == 2) {
                    $coupon_discount_amount = $coupon->amount;
                    $total_discount += $coupon_discount_amount;
                    $vendor_payable_amount -= $coupon_discount_amount;
                    $vendor_discount_amount +=$coupon_discount_amount;
                } else {
                    $coupon_discount_amount = ($quantity_price * $coupon->amount / 100);
                    $final_coupon_discount_amount = $coupon_discount_amount * $clientCurrency->doller_compare;
                    $total_discount += $final_coupon_discount_amount;
                    $vendor_payable_amount -=$final_coupon_discount_amount;
                    $vendor_discount_amount +=$final_coupon_discount_amount;
                }
            }
            $total_toll_amount +=(isset($request->tollamount))?$request->tollamount:0.00;
            $total_service_fee +=$order_vendor->service_fee_percentage_amount;

            $vendor_payable_amount +=(isset($request->tollamount))?$request->tollamount:0.00;
            $vendor_payable_amount +=$order_vendor->service_fee_percentage_amount;

            $order_vendor->subtotal_amount = $actual_amount;
            $order_vendor->payable_amount = $vendor_payable_amount;
            $order_vendor->taxable_amount = $vendor_taxable_amount;
            $order_vendor->discount_amount= $vendor_discount_amount;
            $order_vendor->toll_amount = (isset($request->tollamount))?$request->tollamount:0.00;

            $vendor_info = Vendor::where('id', $vendor_id)->first();
            if ($vendor_info) {
                if (($vendor_info->commission_percent) != null && $vendor_payable_amount > 0) {
                    $order_vendor->admin_commission_percentage_amount = round($vendor_info->commission_percent * ($vendor_payable_amount / 100), 2);
                }
                if (($vendor_info->commission_fixed_per_order) != null && $vendor_payable_amount > 0) {
                    $order_vendor->admin_commission_fixed_amount = $vendor_info->commission_fixed_per_order;
                }
            }
            $order_vendor->save();

            $loyalty_points_earned = LoyaltyCard::getLoyaltyPoint($loyalty_points_used, $payable_amount);
            $order->total_amount = $total_amount;
            $order->total_discount = $total_discount;
            $order->taxable_amount = $taxable_amount;
            if ($loyalty_amount_saved > 0) {
                if ($payable_amount < $loyalty_amount_saved) {
                    $loyalty_amount_saved =  $payable_amount;
                    $loyalty_points_used = $payable_amount * $redeem_points_per_primary_currency;
                }
            }

            $order->total_delivery_fee = $total_delivery_fee;
            $order->loyalty_points_used = $loyalty_points_used;
            $order->loyalty_amount_saved = $loyalty_amount_saved;
            $order->total_toll_amount    = $total_toll_amount;
            $order->total_service_fee    = $total_service_fee;
            $order->is_edited = 1;

            $now = Carbon::now()->toDateTimeString();
            $user_subscription = SubscriptionInvoicesUser::with('features')
                ->select('id', 'user_id', 'subscription_id')
                ->where('user_id', $user->id)
                ->where('end_date', '>', $now)
                ->orderBy('end_date', 'desc')->first();
            if ($user_subscription) {
                foreach ($user_subscription->features as $feature) {
                    if ($feature->feature_id == 2) {
                        $subscriptionAmount = $request->tags_amount - ($feature->percent_value * $request->tags_amount / 100);
                        $order->subscription_discount = $request->tags_amount - $subscriptionAmount;
                        $order->payable_amount = $subscriptionAmount + $total_toll_amount + $total_service_fee;
                    }
                }
            }else{
                $order->payable_amount = $delivery_fee + $payable_amount - $total_discount - $loyalty_amount_saved + $total_toll_amount + $total_service_fee;
            }



            $order->loyalty_points_earned = $loyalty_points_earned['per_order_points'];
            $order->loyalty_membership_id = $loyalty_points_earned['loyalty_card_id'];

            $order->save();



            if(!empty($order_vendor->web_hook_code)){
                $request_to_dispatch = $this->updateOrderRequestToDispatch($request, $order, $vendor_id);
            }
            DB::commit();
            return $this->successResponse([], __('Drop Off Location Updated.'), 200);

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    // place edit dropoff Request To Dispatch
    public function updateOrderRequestToDispatch($request,$order,$vendor){
        try
        {
            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            $customer = Auth::user();
            $wallet = $customer->wallet;
            if($dispatch_domain && $dispatch_domain != false)
            {
                $tasks = array();
                if ($request->payment_option_id == 1) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order->payable_amount;
                } else {
                    if($order->is_postpay==1)
                    {
                        $cash_to_be_collected = 'Yes';
                        $payable_amount = $order->payable_amount;
                    }else{
                        $cash_to_be_collected = 'No';
                        $payable_amount = 0.00;
                    }
                }

                $schedule_datetime_del = NULL;
                if (isset($request->schedule_time) && !empty($request->schedule_time)) {
                    $schedule_datetime_del = Carbon::parse($request->schedule_time, $customer->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                }

                if(isset($request->task_type) && !empty($request->task_type))
                {
                    $request->task_type = $request->task_type;
                    $schedule_datetime_del = null;
                    $request->order_time = $schedule_datetime_del;
                }else{
                    $request->task_type = 'schedule';
                    $request->scheduled_date_time = $schedule_datetime_del;
                    $request->order_time = $schedule_datetime_del;
                }
                $order_vendor = OrderVendor::where(['order_id' => $order->id,'vendor_id' => $vendor])->first();

                $unique = Auth::user()->code;
                $client_do = Client::where('code',$unique)->first();
                if(!empty($client_do->custom_domain)){
                    $domain = $client_do->custom_domain;
                }else{
                    $domain = $client_do->sub_domain.env('SUBMAINDOMAIN');
                }
                $call_back_url = "https://".$domain."/dispatch-pickup-delivery/".$order_vendor->web_hook_code;
                $tasks = array();

                $product = Product::find($request->product_id);
                $order_agent_tag = $product->tags??'';
                $type = $request->bookingType ?? 0;

                $postdata =  [
                            'task_type' => $request->task_type,
                            'schedule_time' => $schedule_datetime_del ?? null,
                            'cash_to_be_collected' => $payable_amount??0.00,
                            'call_back_url' => $call_back_url??null,
                            'order_agent_tag' => $order_agent_tag,
                            'task' => $request->tasks_dropoff,
                            'allocation_type' => 'a',
                            'toll_passes' => ((!empty($product) && $product->is_toll_tax == 1)?$product->tollpass->toll_pass:'IN_FASTAG'),
                            'VehicleEmissionType' => ((!empty($product) && $product->is_toll_tax == 1)?$product->emissiontype->emission_type:'GASOLINE'),
                            'travelMode' => ((!empty($product) && $product->is_toll_tax == 1)?$product->travelmode->travelmode:'TAXI'),
                            'no_seats_for_pooling' =>(isset($request->is_cab_pooling) && $request->is_cab_pooling== 1 && isset($request->no_seats_for_pooling))?$request->no_seats_for_pooling:0,
                            'is_cab_pooling' => isset($request->is_cab_pooling)?$request->is_cab_pooling:0,
                            'available_seats' => $product->seats_for_booking,
                        ];


                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                        'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                        'content-type' => 'application/json']
                            ]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->post(
                    $url.'/api/task/update',
                    ['form_params' => (
                            $postdata
                        )]
                );
                $response = json_decode($res->getBody(), true);

                return $response;
            }
        }catch(\Exception $e)
        {
            $data = [];
            $data['status'] = 400;
            $data['message'] =  $e->getMessage();
            return $data;
        }
    }


    //-----function to accept bids related to ride request/instant booking-----
    public function acceptBidsRelatedToOrderRide(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $user              = Auth::user();
            $order_bid_id      = $request->order_id;
            $bid_id            = $request->bid_id;
            $task_type         = $request->task_type;

            $biddata           = PickDropDriverBid::where('id', $bid_id)->where('order_bid_id', $order_bid_id)->first();
            $order             = Order::where('id', $order_bid_id)->first();
            $order_vendor      = OrderVendor::where('order_id', $order->id)->first();
            $vendor_id         = $order_vendor->vendor_id;
            $order_product     = OrderProduct::where('order_vendor_id', $order_vendor->id)->select('product_id')->first();
            $product           = Product::where('id',$order_product->product_id)->with('pimage', 'variants', 'taxCategory.taxRate', 'addon')->first();
            $clientCurrency    = ClientCurrency::where('currency_id', $user->currency)->first();

            if(!empty($biddata) && !empty($order) && !empty($order_vendor) && !empty($order_product)){

                $tasks         = OrderLocations::where('order_id', $order->id)->select('tasks')->first();
                if(isset($biddata->tasks))
                {
                    if(!empty($tasks))
                    {
                        OrderLocations::where('order_id', $order->id)->update(['tasks' => $biddata->tasks]);
                    }else{
                        OrderLocations::insert(['tasks' => $biddata->tasks, 'order_id' => $order->id, 'vendor_id' => $order_product->vendor_id, 'product_id' => $order_product->product_id]);
                    }
                }

                //--------------------------------------------------------------------------------------------------------------
                $request->request->add(['product_id' => $order_product->product_id]);
                $request->request->add(['tags_amount' => $biddata->bid_price]);
                $request->request->add(['tasks' => json_decode($biddata->tasks)]);
                $request->request->add(['tollamount' => 0]);
                $request->request->add(['agent_id' => $biddata->driver_id]);

                $variant                            = $product->variants->where('product_id', $product->id)->first();
                $variant->price                     = $request->tags_amount;
                $variant->toll_price                = 0;

                $divider                            = (empty($clientCurrency->doller_compare) || $clientCurrency->doller_compare < 0) ? 1 : $clientCurrency->doller_compare;
                $divider                            = isset($divider) ? $divider : 1;
                $price_in_currency                  = $request->tags_amount / $divider;
                $price_in_dollar_compare            = $price_in_currency * $divider;
                $quantity_price                     = $price_in_dollar_compare * 1;
                $payable_amount                     = $quantity_price;
                $vendor_payable_amount              = $quantity_price;

                $total_amount                       = $variant->price;

                $order_product->price               = $variant->price;
                $order_product->toll_price          = $variant->toll_price;
                $order_product->save();
                $coupon_id                          = null;
                $coupon_name                        = null;
                $actual_amount                      = $vendor_payable_amount;

                $order_vendor->service_fee_percentage_amount = 0;
                $order_vendor->subtotal_amount               =  $biddata->bid_price ?? $actual_amount;
                $order_vendor->payable_amount                =  $biddata->bid_price ?? $vendor_payable_amount;
                $order_vendor->taxable_amount                = 0;
                $order_vendor->discount_amount               = 0;
                $order_vendor->toll_amount                   = 0;

                $vendor_info                                 = Vendor::where('id', $vendor_id)->first();
                if ($vendor_info) {
                    if (($vendor_info->commission_percent) != null && $vendor_payable_amount > 0) {
                        $order_vendor->admin_commission_percentage_amount = round($vendor_info->commission_percent * ($vendor_payable_amount / 100), 2);
                    }
                    if (($vendor_info->commission_fixed_per_order) != null && $vendor_payable_amount > 0) {
                        $order_vendor->admin_commission_fixed_amount = $vendor_info->commission_fixed_per_order;
                    }
                }
                $order_vendor->save();


                $order->total_delivery_fee      = 0;
                $order->loyalty_points_used     = 0;
                $order->loyalty_amount_saved    = 0;
                $order->total_toll_amount       = 0;
                $order->total_service_fee       = 0;

                $order->total_amount            = $payable_amount;
                $order->payable_amount          = $payable_amount;

                $order->loyalty_points_earned   = 0;
                $order->loyalty_membership_id   = 0;

                $order->save();
                //-------------------------------------------------------------------------------------------------------------


                $request_to_dispatch = $this->placeInstantOrderBidAcceptRequestToDispatch($request, $order, $vendor_id);
                if($request_to_dispatch && isset($request_to_dispatch['task_id']) && $request_to_dispatch['task_id'] > 0){
                    PickDropDriverBid::where('id', $bid_id)->where('order_bid_id', $order_bid_id)->where('task_type', '=', $task_type)->update(['status' => 1]);
                    PickDropDriverBid::where('id', '!=', $bid_id)->where('order_bid_id', $order_bid_id)->where('task_type', '=', $task_type)->update(['status' => 2]);
                    DB::commit();
                    return $this->successResponse($order, null, 200);
                }else{
                    DB::rollback();
                    return $request_to_dispatch;
                }
            }else{
                DB::rollback();
                $message = "Something went wrong, Please try again.";
                return $this->errorResponse($message, 400);
            }
        }
        catch (\Exception $e) {
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }


    // place Request To Dispatch
    public function placeInstantOrderBidAcceptRequestToDispatch($request, $order, $vendor){
        try {
            $meta_data = '';
            $tasks = array();
            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            $customer = Auth::user();
            $wallet = $customer->wallet;

            if ($dispatch_domain && $dispatch_domain != false) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order->payable_amount;

                $unique = $customer->code;
                $team_tag = $unique."_".$vendor;
                $order_vendor = OrderVendor::where(['order_id' => $order->id,'vendor_id' => $vendor])->first();
                $dynamic = (!empty($order_vendor->web_hook_code)) ? $order_vendor->web_hook_code : uniqid($order->id.$vendor);
                $product = Product::find($request->product_id);
                $order_agent_tag = $product->tags??'';
                $client_do = Client::where('code', $unique)->first();
                $domain = '';
                if(!empty($client_do->custom_domain)){
                    $domain = $client_do->custom_domain;
                }else{
                    $domain = $client_do->sub_domain.env('SUBMAINDOMAIN');
                }
                $call_back_url = "https://".$domain."/dispatch-pickup-delivery/".$dynamic;

                $client = Client::orderBy('id', 'asc')->first();

                $postdata =  [
                    'order_number' =>  $order->order_number,
                    'allocation_type' => 'm',
                    'task' => $request->tasks,
                    'order_agent_tag' => $order_agent_tag,
                    'call_back_url' => $call_back_url??null,
                    'cash_to_be_collected' => $payable_amount??0.00,
                    'customer_id' => $order->user_id,
                    'task_type'   => $request->task_type,
                    'agent_id'    => $request->agent_id,
                ];
                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                                                    'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                                                    'content-type' => 'application/json']
                                                        ]);

                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->post($url.'/api/task/updateBidRide',['form_params' => ($postdata)]);
                $response = json_decode($res->getBody(), true);
                if ($response && isset($response['task_id']) && $response['task_id'] > 0) {

                    if($response['status'] == 'assigned'){
                        $update_vendor = VendorOrderStatus::updateOrCreate([
                            'order_id' =>  $order->id,
                            'order_status_option_id' => 2,
                            'vendor_id' =>  $vendor,
                            'order_vendor_id' =>  $order_vendor->id]);

                        OrderVendor::where('vendor_id', $vendor)->where('order_id', $order->id)->update(['order_status_option_id' => 2,'dispatcher_status_option_id' => 2]);

                        $update = VendorOrderDispatcherStatus::updateOrCreate(['dispatcher_id' => null,
                        'order_id' =>  $order->id,
                        'dispatcher_status_option_id' =>  2,
                        'vendor_id' =>  $vendor]);
                    }

                    return $response;
                }
                return $response;
            }
        }catch(\Exception $e){
                $data = [];
                $data['status'] = 400;
                $data['message'] =  $e->getMessage();
                return $data;
            }
    }


    //-----function to decline bids related to bid & ride/instant booking-----
    public function declineBidsRelatedToOrderRide(Request $request)
    {
        try
        {
            $bid_id       = $request->bid_id;
            $bid       = PickDropDriverBid::where('id', $bid_id)->first();
            $update       = PickDropDriverBid::where('id', $bid_id)->update(['status' => 2]);
            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $postdata =  [
                    'driver_id'                   => $bid->driver_id,
                    'status'                      => 2
                ];

                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                    'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                    'content-type' => 'application/json']
                ]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->post(
                    $url.'/api/bidRide/notification',
                    ['form_params' => (
                        $postdata
                        )]
                    );
                $response = json_decode($res->getBody(), true);
              //  return $response;
            }

            return $this->successResponse($update, "Request declined successfully", 200);
        }
        catch (\Exception $e) {
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }





    // bid & Ride request from user
    public function createBidRideRequest(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            $product = Product::where('id', $request->product_id)->first();
            if(!$vendor || !$product){
                return response()->json(['error' => __('No record found.')], 404);
            }

            $getAdditionalPreference = getAdditionalPreference(['bid_expire_time_limit_seconds']);
            $expiryseconds = ($getAdditionalPreference['bid_expire_time_limit_seconds'] > 0) ? $getAdditionalPreference['bid_expire_time_limit_seconds'] : 30;


            $UserBidRideRequest                         = new UserBidRideRequest();
            $UserBidRideRequest->user_id                = Auth::user()->id;
            $UserBidRideRequest->product_id             = $request->product_id;
            $UserBidRideRequest->vendor_id              = $request->vendor_id;
            $UserBidRideRequest->tasks                  = json_encode($request->tasks);
            $UserBidRideRequest->requested_price        = $request->requested_price;
            $UserBidRideRequest->web_hook_code          = uniqid(Auth::user()->id.$request->vendor_id);
            $UserBidRideRequest->expired_at             = Carbon::now()->addSeconds($expiryseconds)->format('Y-m-d H:i:s');
            $UserBidRideRequest->save();

            $request_to_dispatch = $this->placeRequestForDriverBidsToDispatch($request, $product, $UserBidRideRequest);
            if($UserBidRideRequest){
                DB::commit();
                return response()->json(['data' => $UserBidRideRequest, 'message' => "Request created, Please wait a while till someone respond to your request."], 200);
            }else{
                DB::rollback();
                return response()->json(['message' => "Error, Something went wrong."], 400);
            }
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json(['message' => "Error, Something went wrong."], 400);
        }
    }


    // place Request To Dispatch
    public function placeRequestForDriverBidsToDispatch($request, $product, $UserBidRideRequest){
        try
        {
            $getAdditionalPreference = getAdditionalPreference(['bid_expire_time_limit_seconds']);
            $expiryseconds = ($getAdditionalPreference['bid_expire_time_limit_seconds'] > 0) ? $getAdditionalPreference['bid_expire_time_limit_seconds'] : 30;

            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            $customer = Auth::user();
            if($dispatch_domain && $dispatch_domain != false && !empty($UserBidRideRequest))
            {
                $unique = Auth::user()->code;
                $client_do = Client::orderBy('id', 'asc')->first();

                if(!empty($client_do->custom_domain)){
                    $domain = $client_do->custom_domain;
                }else{
                    $domain = $client_do->sub_domain.env('SUBMAINDOMAIN');
                }

                $call_back_url = "https://".$domain."/dispatch/driver/bids/update/".$UserBidRideRequest->web_hook_code;

                $postdata =  [
                            'tasks'                   => $request->tasks,
                            'call_back_url'           => $call_back_url??null,
                            'agent_tag'               => $product->tags ?? '',
                            'bid_id'                  => $UserBidRideRequest->id,
                            'db_name'                 => $client_do->database_name,
                            'client_code'             => $client_do->code,
                            'requested_price'         => $UserBidRideRequest->requested_price,
                            'expired_at'              => $UserBidRideRequest->expired_at,
                            'expire_seconds'          => $expiryseconds,
                            'customer_name'           => $customer->name,
                            'customer_image'          => $customer->image['proxy_url'].'100/100'.$customer->image['image_path'],
                            'minimum_requested_price' => $request->min_requested_price,
                            'maximum_requested_price' => $request->max_requested_price,
                        ];


                $client = new GClient(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                                                    'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                                                    'content-type' => 'application/json']
                                                        ]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->post(
                    $url.'/api/bidriderequest/notifications',
                    ['form_params' => (
                            $postdata
                        )]
                );
                $response = json_decode($res->getBody(), true);
                return $response;
            }
        }
        catch(\Exception $e)
        {
            $data = [];
            $data['status'] = 400;
            $data['message'] =  $e->getMessage();
            return $data;
        }
    }

    //-----function to get bids related to ride request/instant booking-----
    public function getBidsRelatedToOrderRide(Request $request)
    {
        try
        {
            $getAdditionalPreference = getAdditionalPreference(['bid_expire_time_limit_seconds']);
            $order_bid_id = $request->order_id;
            $task_type    = $request->task_type;
            $biddata      = PickDropDriverBid::where('order_bid_id', $order_bid_id)->where('task_type', $task_type)->where('expired_at', '>', now()->format('Y-m-d H:i:s'))->where('status', 0)->get();
            return $this->successResponse(['biddata' => $biddata, 'bid_expire_time_limit_seconds' => $getAdditionalPreference['bid_expire_time_limit_seconds']], 200);
        }
        catch (\Exception $e) {
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }

    //-----function to accept bids related to bid & ride/instant booking-----
    public function acceptBidsRelatedToBidRideOrderRide(Request $request)
    {
        try
        {
            $bid_id       = $request->bid_id;
            $update       = PickDropDriverBid::where('id', $bid_id)->update(['status' => 1]);
            return $this->successResponse($update, "Request accepted successfully", 200);
        }
        catch (\Exception $e) {
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }

}
