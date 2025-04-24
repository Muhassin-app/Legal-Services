<?php

namespace App\Http\Controllers\Front;

use DB;
use Config;
use Session;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Requests\OrderProductRatingRequest;
use App\Models\{AddonOption, Category,ClientPreference,ClientCurrency,Vendor,ProductVariantSet,Product,SubscriptionInvoicesUser,LoyaltyCard,UserAddress,Order,OrderVendor,OrderProduct,VendorOrderStatus,Client,Promocode,PromoCodeDetail,VendorOrderDispatcherStatus, Payment, Rider, OrderLocations, LuxuryOption, OrderDriverRating, ProductFaq, ProductFaqSelectOption, User, VendorCategory,ClientLanguage, ClientPreferenceAdditional, OrderProductAddon, PaymentOption, PickDropDriverBid, TaxRate, UserBidRideRequest, UserDevice,EmailTemplate};
use App\Http\Traits\{ApiResponser, GuzzleHttpTrait, OrderTrait, PaymentTrait};
use GuzzleHttp\Client as GCLIENT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Log,DateTime,DateTimeZone;

class PickupDeliveryController extends FrontController{

    use ApiResponser,PaymentTrait,OrderTrait,GuzzleHttpTrait;

    public function getPaymentOptions(Request $request, $domain = '')
    {
        $code = $this->paymentOptionArray('pickup_delivery');
        $payment_options = PaymentOption::whereIn('code', $code)->where('status', 1)->get(['id', 'code','credentials' ,'title', 'off_site']);
        foreach($payment_options as $option){
            if($option->code == 'stripe'){
                $option->title = __('Credit/Debit Card (Stripe)');
            }elseif($option->code == 'kongapay'){
                $option->title = 'Pay Now';
            }elseif($option->code == 'mvodafone'){
                $option->title = 'Vodafone M-PAiSA';
            }
            elseif($option->code == 'mobbex'){
                $option->title = __('Mobbex');
            }
            elseif($option->code == 'authorize_net'){
                $option->title = __('Credit/Debit Card');
            }
            elseif($option->code == 'offline_manual'){
                $json = json_decode($option->credentials);
                $option->title = $json->manule_payment_title;
            }elseif($option->code == 'obo'){
                $option->title = __("Momo, Airtel Money by O'Pay");
            }
            elseif($option->code == 'livee'){
                $option->title = __("Livees");
            }
            $option->title = __($option->title);
            $option->slug = strtolower(str_replace(' ', '_', $option->title));
        }
        return $this->successResponse($payment_options, '', 201);
    }

    public function getOrderTrackingDetails(Request $request, $domain = ''){

        $order = OrderVendor::with('orderDetail')->where('order_id',$request->order_id)->select('*','dispatcher_status_option_id as dispatcher_status')->first()->toArray();
       $response = Http::get($request->new_dispatch_traking_url);

        if(count($order) > 0) {
            if($response->status() == 200){
                if(($order['dispatcher_status'] === __('Hold on! We are looking for drivers nearby!'))){
                    if($order['order_detail']['scheduled_date_time']){ //  show scheduled ride
                        $user = Auth::user();
                        if(empty($user->timezone))
                        {
                            $client_timezone = DB::table('clients')->first('timezone');
                            $user->timezone = $client_timezone->timezone ?? $user->timezone;
                        }
                        $date = Carbon::parse($order['order_detail']['scheduled_date_time'], 'UTC');
                        $date->setTimezone( $user->timezone);
                        $schudelDate =  $date->format('d M,Y | h:i A'); //$date->isoFormat('d.m.Y, H:i A');
                       //date("F j, Y, g:i a"); //dateTimeInUserTimeZone($order['order_detail']['scheduled_date_time'], $user->timezone)
                        $order['dispatcher_status'] = __('You have successfully scheduled your ride for:') . $schudelDate   ;
                    }
                    if ($response['agent_location'] != ''){
                        $order['dispatcher_status'] = __('Your driver has been assigned!');
                    }
                }
                $type = VendorOrderDispatcherStatus::where(['order_id' =>  $order['order_id'] ,'vendor_id' =>$order['vendor_id'] ])->latest()->first();
                $order_driver_rating = OrderDriverRating::where('order_id', $request->order_id)->first();
                $order['dispatcher_status_type']=  $type ?  $type->type :1;
                $response = $response->json();
                $response['order_details'] = $order;
                $response['order_driver_rating'] = $order_driver_rating;
                return $this->successResponse($response);
            } else {

                $response = [];
                $response['order_details'] = [];
                $response['status'] = $response->status();
                return $this->successResponse($response);

            }
        } else {
            if($response->status() == 200){
                $response = $response->json();
                $response['order_details'] = [];
                return $this->successResponse($response);
            } else {
                $response = [];
                $response['order_details'] = [];
                $response['status'] = $response->status();
                return $this->successResponse($response);
            }
        }

    }

    public function postVendorListByCategoryId(Request $request, $domain = '',$category_id = 0){
        $vendor_type = Session::get('vendorType');
        $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'pickup_delivery_service_area')->where('id', '>', 0)->first();
        $preferences->is_cab_pooling = getAdditionalPreference(['is_cab_pooling'])['is_cab_pooling'];
        $vendor_ids = [];
        $pickup_latitude = '';
        $pickup_longitude = '';
        $locations = $request->has('locations') ? json_decode($request->get('locations')) : [];

        if(count($locations) > 0){
            $pickup_latitude = $locations[0] ? $locations[0]->latitude : '';
            $pickup_longitude = $locations[0] ? $locations[0]->longitude : '';
            $dropoff_latitude = $locations[1] ? $locations[1]->latitude : '';
            $dropoff_longitude = $locations[1] ? $locations[1]->longitude : '';

        }
        $vendor_categories = VendorCategory::where('category_id', $category_id)->where('status', 1)->get();
        foreach ($vendor_categories as $vendor_category) {
           if(!in_array($vendor_category->vendor_id, $vendor_ids)){
                $vendor_ids[] = $vendor_category->vendor_id;
           }
        }
        $vendors = Vendor::select('id', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id')
        ->with('slot')->withAvg('product', 'averageRating');



        if(isset($preferences->pickup_delivery_service_area) && ($preferences->pickup_delivery_service_area == 1)){

            if (!empty($pickup_latitude) && !empty($pickup_longitude) && !empty($dropoff_latitude) && !empty($dropoff_longitude)) {
                $vendors = $vendors->whereHas('serviceArea', function ($query) use ($pickup_latitude, $pickup_longitude,$dropoff_latitude,$dropoff_longitude) {
                    $query->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(".$pickup_latitude." ".$pickup_longitude.")'))")->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(".$dropoff_latitude." ".$dropoff_longitude.")'))");
                });
            }
        }

        $vendors = $vendors->whereIn('id', $vendor_ids)
        ->where($vendor_type, 1)
        ->where('status', 1)
        ->get();


        foreach ($vendors as $vendor) {
            $vendor->is_show_category = ($vendor->vendor_templete_id == 1) ? 0 : 1;
            unset($vendor->products);
        }
        return $this->successResponse($vendors);
    }

    public function getTaxes()
    {
        /* Getting All Taxes available and making TaxRate array according to requirement */
        $taxes=TaxRate::all();
        $taxRates=array();
        foreach($taxes as $tax){
            $taxRates[$tax->id]=['tax_rate'=>$tax->tax_rate,'tax_amount'=>$tax->tax_amount];
        }
        return $taxRates;
    }

    public function postCabProductById(Request $request, $domain = '',$product_id = 0){
        $user = Auth::user();
        $taxRates = $this->getTaxes();
        $language_id = Session::get('customerLanguage');
        $preferences = ClientPreference::where('id', '>', 0)->first();
        $preferences->is_cab_pooling = getAdditionalPreference(['is_cab_pooling'])['is_cab_pooling'];
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
                        },
                        'addOn.addOnName.option'
                        ])->select('products.id', 'products.sku', 'products.requires_shipping', 'products.sell_when_out_of_stock', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.Requires_last_mile', 'products.averageRating', 'products.category_id','products.tags', 'products.seats_for_booking', 'products.available_for_pooling', 'products.is_toll_tax', 'products.travel_mode_id', 'products.toll_pass_id', 'products.emission_type_id','seats','products.per_hour_price','products.km_included')->where('products.id', $product_id)->where('products.is_live', 1)->first();
        $image_url = $product->media->first() ? $product->media->first()->image->path['image_fit'].'360/360'.$product->media->first()->image->path['image_path'] : '';
        $product->image_url = $image_url;

        if($preferences->is_hourly_pickup_rental == 1)
        {
        $tags_price = $this->getDeliveryFeeDispatcher($request, $product, $schedule_datetime_del,1);


        }
        else{
            $tags_price = $this->getDeliveryFeeDispatcher($request, $product, $schedule_datetime_del);

        }
        // $product->service_charge_amount  = ($product->vendor->fixed_service_charge == 1)?$product->vendor->service_charge_amount:0.00;

        $product->original_tags_price = decimal_format($tags_price['delivery_fee']);
        $product->tags_price = decimal_format($tags_price['delivery_fee']);
        if(!empty($request->rental_hour))
        {
        $product->tags_price = decimal_format($request->rental_hour * $product->per_hour_price);
        $product->distance =  $product->km_included;
        }
        else{
            $product->distance = decimal_format($tags_price['distance']);
        }
        $product->toll_fee = decimal_format($tags_price['toll_fee']);
        $product->duration = decimal_format($tags_price['duration']);
        $product->min_tags_price = decimal_format($tags_price['min_delivery_fee']);

        //for cab pooling

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
        $product->seats_for_booking = ($product->seats_for_booking > 0)?$product->seats_for_booking:1;
        $no_seats_for_pooling = isset($request->no_seats_for_pooling)?$request->no_seats_for_pooling:1;
        $product->no_seats_for_pooling = $no_seats_for_pooling;
        if(!empty($request->is_cab_pooling) && $request->is_cab_pooling == 1 && !empty($preferences) && $preferences->is_cab_pooling == 1)
        {
            $product->original_tags_price = decimal_format(($product->original_tags_price/$product->seats_for_booking)*$no_seats_for_pooling);
            $product->tags_price = decimal_format(($product->tags_price/$product->seats_for_booking)*$no_seats_for_pooling);
            $product->toll_fee = decimal_format(($product->toll_fee/$product->seats_for_booking)*$no_seats_for_pooling);
        }//------

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

        //Check for fixed service fee and service fee percent
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

        $curId = Session::get('customerCurrency');
        $customerCurrency = ClientCurrency::where('currency_id', $curId)->first();
        if (!$customerCurrency) {
            $customerCurrency = ClientCurrency::where('is_primary', 1)->get()->first()->currency;
        }
        $price_in_doller_compare = $product->total_tags_price  * $customerCurrency->doller_compare;
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
        $product->taxable_amount =  $product_tax;
        $product->total_tags_price = $product->total_tags_price - $loyalty_amount_saved??0.0;

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

        //wallet amount used
        $product->wallet_amount_used = 0;
        if($user->balanceFloat > 0){
            if($customerCurrency){
                $wallet_amount_used = $user->balanceFloat * $customerCurrency->doller_compare;
            }
            if($wallet_amount_used >  $product->total_tags_price){
                $wallet_amount_used = $product->total_tags_price;
            }
            $product->wallet_amount_used = decimal_format($wallet_amount_used);
        }
        $product->total_tags_price = $product->total_tags_price - $product->subscription_discount??0.0;
        $product->total_tags_price =  decimal_format($product->total_tags_price - $product->wallet_amount_used);

        if(isset($request->yacht_id)){
            $yacht = Product::with(['pimage','variant'])->select('id','title')->find($request->yacht_id);
            $product->yacht = $yacht;
            $image_url = $yacht->media->first() ? $yacht->media->first()->image->path['image_fit'].'360/360'.$yacht->media->first()->image->path['image_path'] : '';
            $product->yacht->image_url = $image_url;
        }
        return $this->successResponse($product);
    }
    # get all vehicles category by vendor

    public function productsByVendorInPickupDelivery(Request $request, $domain = '',$vid = 0, $cid = 0){
        try {
            if($vid == 0){
                return response()->json(['error' => 'No record found.'], 404);
            }

            $preferences = ClientPreference::where('id', '>', 0)->first();
            $preferences->is_cab_pooling = getAdditionalPreference(['is_cab_pooling'])['is_cab_pooling'];
            $user = Auth::user();
            $userid = $user->id;
            if(empty($user->timezone)){
                $client_timezone = DB::table('clients')->first('timezone');
                $user->timezone = $client_timezone->timezone ?? $user->timezone;
            }

            $schedule_datetime_del = '';
            if (isset($request->schedule_date_delivery) && !empty($request->schedule_date_delivery)) {
                $schedule_datetime_del = Carbon::parse($request->schedule_date_delivery, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
            }else{
                $schedule_datetime_del = Carbon::now()->timezone('UTC')->format('Y-m-d H:i:s');
            }

            $paginate = $request->has('limit') ? $request->limit : 12;
            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
            $language_id = Session::get('customerLanguage');
            $vendor = Vendor::select('id', 'name', 'desc', 'logo', 'banner', 'address', 'latitude', 'longitude',
                        'order_min_amount', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery', 'fixed_service_charge', 'service_charge_amount')
                        ->where('id', $vid)->first();
            if(!$vendor){
                return response()->json(['error' => 'No record found.'], 200);
            }
            $products = Product::with(['category.categoryDetail', 'tollpass', 'travelmode', 'emissiontype', 'inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },'media.image', 'translation' => function($q) use($language_id){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $language_id);
                        },'variant' => function($q) use($language_id){
                            $q->select('id','sku', 'product_id', 'quantity', 'price', 'barcode');
                            $q->groupBy('product_id');
                        },
                    ])->join('product_categories as pc', 'pc.product_id', 'products.id')
                    ->whereNotIn('pc.category_id', function($qr) use($vid){
                                $qr->select('category_id')->from('vendor_categories')
                                    ->where('vendor_id', $vid)->where('status', 0);
                    })
                    ->whereHas('category.categoryDetail' ,function($qryd) {
                        $qryd->where('type_id', 7);   # check only products get of pickup
                    })
                    ->select('products.id', 'products.sku', 'products.requires_shipping', 'products.sell_when_out_of_stock', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.Requires_last_mile', 'products.averageRating', 'pc.category_id','products.tags','products.seats_for_booking', 'products.available_for_pooling', 'products.is_toll_tax', 'products.travel_mode_id', 'products.toll_pass_id', 'products.emission_type_id')
                    ->where('products.vendor_id', $vid);
                    if($cid > 0){
                        $products = $products->where('products.category_id', $cid);
                    }

                    if(!empty($request->is_cab_pooling) && $request->is_cab_pooling == 1 && !empty($preferences) && $preferences->is_cab_pooling == 1)
                    {
                        $products = $products->where('products.available_for_pooling', 1);
                    }
                    $products = $products->where('products.is_live', 1)->distinct()->get();

             if(!empty($products)){
                foreach ($products as $key => $product) {
                    $tags_price = $this->getDeliveryFeeDispatcher($request, $product, $schedule_datetime_del);
                    $image_url = $product->media->first() ? $product->media->first()->image->path['image_fit'].'93/93'.$product->media->first()->image->path['image_path'] : '';
                    $product->image_url = $image_url;
                    $product->service_charge_amount  = ($vendor->fixed_service_charge == 1)?$vendor->service_charge_amount:0.00;
                    $product->name = $product->translation->first() ? $product->translation->first()->title :'';
                    $product->description = $product->translation->first() ? $product->translation->first()->meta_description :'';

                    $product->seats_for_booking = ($product->seats_for_booking > 0)?$product->seats_for_booking:1;
                    if(isset($request->is_cab_pooling) && $request->is_cab_pooling==1 && !empty($preferences) && $preferences->is_cab_pooling == 1){
                        $product->tags_price = decimal_format(($tags_price['delivery_fee'] + $tags_price['toll_fee'])/$product->seats_for_booking);
                    }else{
                        $product->tags_price = decimal_format($tags_price['delivery_fee'] + $tags_price['toll_fee']);
                    }
                    $product->original_tags_price = $product->tags_price + $product->service_charge_amount;

                    $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                    foreach ($product->variant as $k => $v) {
                        $product->variant[$k]->price = $product->tags_price;
                        $product->variant[$k]->multiplier = 1;
                    }
                }
            }
            $loyalty_amount_saved = 0;
            $redeem_points_per_primary_currency = '';
            $loyalty_card = LoyaltyCard::where('status', '0')->first();
            if ($loyalty_card) {
                $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
            }
            $loyalty_points_used = 0;
            $order_loyalty_points_earned_detail = Order::where('user_id', $userid)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
            if ($order_loyalty_points_earned_detail) {
                $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                    $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                }
            }

            $response['vendor'] = $vendor;
            $response['products'] = $products;
            $response['loyalty_amount_saved'] = decimal_format((float)$loyalty_amount_saved ?? 0 ) ;
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLineNo(), $e->getCode());
        }
    }
    public function productsByRentalVendorInPickupDelivery(Request $request, $domain = '',$cid = 0){
        try {


            $cid = $request->category_id;


            $preferences = ClientPreference::where('id', '>', 0)->first();
            $user = Auth::user();
            $userid = $user->id;
            if(empty($user->timezone)){
                $client_timezone = DB::table('clients')->first('timezone');
                $user->timezone = $client_timezone->timezone ?? $user->timezone;
            }

            $vendor_category = VendorCategory::where('category_id',$cid)->where('status',1)->first();


            $vid = $vendor_category->vendor_id;
            $schedule_datetime_del = '';
            if (isset($request->schedule_date_delivery) && !empty($request->schedule_date_delivery)) {
                $schedule_datetime_del = Carbon::parse($request->schedule_date_delivery, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
            }else{
                $schedule_datetime_del = Carbon::now()->timezone('UTC')->format('Y-m-d H:i:s');
            }


            $paginate = $request->has('limit') ? $request->limit : 12;
            $clientCurrency = ClientCurrency::where('currency_id', Auth::user()->currency)->first();
            $language_id = Session::get('customerLanguage');
            $vendor = Vendor::select('id', 'name', 'desc', 'logo', 'banner', 'address', 'latitude', 'longitude',
                        'order_min_amount', 'order_pre_time', 'auto_reject_time', 'dine_in', 'takeaway', 'delivery', 'fixed_service_charge', 'service_charge_amount')
                        ->where('id', $vid)->first();
            if(!$vendor){
                return response()->json(['error' => 'No record found.'], 200);
            }

            $products = Product::with(['category.categoryDetail', 'tollpass', 'travelmode', 'emissiontype', 'inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },'media.image', 'translation' => function($q) use($language_id){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $language_id);
                        },'variant' => function($q) use($language_id){
                            $q->select('id','sku', 'product_id', 'quantity', 'price', 'barcode');
                            $q->groupBy('product_id');
                        },
                    ])->join('product_categories as pc', 'pc.product_id', 'products.id')
                    ->whereNotIn('pc.category_id', function($qr) use($vid){
                                $qr->select('category_id')->from('vendor_categories')
                                    ->where('vendor_id', $vid)->where('status', 0);
                    })
                    ->whereHas('category.categoryDetail' ,function($qryd) {
                        $qryd->where('type_id', 7);   # check only products get of pickup
                    })
                    ->select('products.id', 'products.sku', 'products.requires_shipping', 'products.sell_when_out_of_stock', 'products.url_slug', 'products.weight_unit', 'products.weight', 'products.vendor_id', 'products.has_variant', 'products.has_inventory', 'products.Requires_last_mile', 'products.averageRating', 'pc.category_id','products.tags','products.seats_for_booking', 'products.available_for_pooling', 'products.is_toll_tax', 'products.travel_mode_id', 'products.toll_pass_id', 'products.emission_type_id','products.per_hour_price','products.km_included')
                    ->where('products.vendor_id', $vid);
                    if($cid > 0){
                        $products = $products->where('products.category_id', $cid);
                    }

                    if(!empty($request->is_cab_pooling) && $request->is_cab_pooling == 1 && !empty($preferences) && $preferences->is_cab_pooling == 1)
                    {
                        $products = $products->where('products.available_for_pooling', 1);
                    }
                    $products = $products->where('products.is_live', 1)->distinct()->get();

             if(!empty($products)){
                foreach ($products as $key => $product) {
                    $tags_price = $this->getDeliveryFeeDispatcher($request, $product, $schedule_datetime_del,1);


                    $image_url = $product->media->first() ? $product->media->first()->image->path['image_fit'].'93/93'.$product->media->first()->image->path['image_path'] : '';
                    $product->image_url = $image_url;
                    $product->service_charge_amount  = ($vendor->fixed_service_charge == 1)?$vendor->service_charge_amount:0.00;
                    $product->name = $product->translation->first() ? $product->translation->first()->title :'';
                    $product->description = $product->translation->first() ? $product->translation->first()->meta_description :'';

                    $product->seats_for_booking = ($product->seats_for_booking > 0)?$product->seats_for_booking:1;
                    if(isset($request->is_cab_pooling) && $request->is_cab_pooling==1 && !empty($preferences) && $preferences->is_cab_pooling == 1){
                        $product->tags_price = decimal_format(($tags_price['delivery_fee'] + $tags_price['toll_fee'])/$product->seats_for_booking);
                    }else{
                        if($preferences->is_hourly_pickup_rental == 1){

                            $product->tags_price = decimal_format($request->rental_hours * $product->per_hour_price);

                        }else {
                            $product->tags_price = decimal_format($tags_price['delivery_fee'] + $tags_price['toll_fee'] );

                        }
                    }
                    $product->original_tags_price = $product->tags_price + $product->service_charge_amount;

                    $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
                    foreach ($product->variant as $k => $v) {
                        $product->variant[$k]->price = $product->tags_price;
                        $product->variant[$k]->multiplier = 1;
                    }
                }
            }
            $loyalty_amount_saved = 0;
            $redeem_points_per_primary_currency = '';
            $loyalty_card = LoyaltyCard::where('status', '0')->first();
            if ($loyalty_card) {
                $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
            }
            $loyalty_points_used = 0;
            $order_loyalty_points_earned_detail = Order::where('user_id', $userid)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
            if ($order_loyalty_points_earned_detail) {
                $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                    $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                }
            }

            $response['vendor'] = $vendor;
            $response['products'] = $products;
            $response['loyalty_amount_saved'] = decimal_format((float)$loyalty_amount_saved ?? 0 ) ;
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage().''.$e->getLineNo(), $e->getCode());
        }
    }
    /**
     * list of vehicles details
    */
     /**     * Get Company ShortCode     *     */
     public function getListOfVehicles(Request $request, $cid = 0){
        try{
            if($cid == 0){
                return response()->json(['error' => 'No record found.'], 404);
            }
            $userid = Auth::user()->id;
            $langId = Auth::user()->language;

            $user   = Auth::user();
            if(empty($user->timezone)){
                $client_timezone = DB::table('clients')->first('timezone');
                $user->timezone = $client_timezone->timezone ?? $user->timezone;
            }

            $schedule_datetime_del = '';
            if(isset($request->schedule_date_delivery) && !empty($request->schedule_date_delivery)) {
                $schedule_datetime_del = Carbon::parse($request->schedule_date_delivery, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
            }else{
                $schedule_datetime_del = Carbon::now()->timezone($user->timezone)->format('Y-m-d H:i:s');
            }


            $category = Category::with(['tags','type'  => function($q){
                            $q->select('id', 'title as redirect_to');
                        },'childs.translation'  => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')->where('category_translations.language_id', $langId);
                        },'translation' => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')->where('category_translations.language_id', $langId);
                        }])->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products')
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

    public function listData($langId, $category_id, $type = '', $userid, $request, $schedule_datetime_del=''){
        if ($type == 'Pickup/Delivery') {
            $category_details = [];
            $deliver_charge = $this->getDeliveryFeeDispatcher($request, null, $schedule_datetime_del);
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
        }else{
            $arr = array();
            return $arr;
        }
    }


     # get delivery fee from dispatcher
     public function getDeliveryFeeDispatcher($request, $product=null, $schedule_datetime_del = '',$is_rental = 0){
        try {
            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $all_location = array();
                $postdata =  ['locations' => $request->locations,'agent_tag' => $product->tags??'', 'schedule_datetime_del' => $schedule_datetime_del, 'toll_passes' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->tollpass)?$product->tollpass->toll_pass:'IN_FASTAG':'IN_FASTAG'), 'VehicleEmissionType' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->emissiontype)?$product->emissiontype->emission_type:'GASOLINE':'GASOLINE'), 'travelMode' => ((!empty($product) && $product->is_toll_tax == 1)?isset($product->travelmode)?$product->travelmode->travelmode:'TAXI':'TAXI')];
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,'content-type' => 'application/json']]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;

                if($is_rental)
                {

                    $res = $client->post($url.'/api/get-delivery-fee-rental',
                    ['form_params' => ($postdata)]
                );
                }else
                {
                    $res = $client->post($url.'/api/get-delivery-fee',
                    ['form_params' => ($postdata)]
                );
                }

                $response = json_decode($res->getBody(), true);
                if($response && $response['message'] == 'success'){
                    return array('delivery_fee' => $response['total'], 'toll_fee' => isset($response['toll_fee'])?((!empty($product) && $product->is_toll_tax == 1)?$response['toll_fee']:0.00):0.00, 'distance' => isset($response['total_distance']) ? $response['total_distance'] : 0, 'duration' => isset($response['total_duration']) ? $response['total_duration'] :0, 'min_delivery_fee' => isset($response['total_minimum']) ? $response['total_minimum'] : 0);
                }else{
                    return array('delivery_fee' => 0, 'toll_fee' => 0, 'distance' => 0, 'duration' => 0, 'min_delivery_fee' => 0);
                }
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
    # check if last mile delivery on
    public function checkIfPickupDeliveryOn(){
        $preference = ClientPreference::first();
        if($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url)){
            return $preference;}
        else
            return false;
    }
    /**
     * create order for booking
    */
     public function createOrder(Request $request){



        try {
            DB::beginTransaction();
            if(isset($request->schedule_datetime) && !empty($request->schedule_datetime))
            {
                $timezone = $request->time_zone;
                $given = new DateTime($request->schedule_datetime, new DateTimeZone($timezone));
                $given->setTimezone(new DateTimeZone("UTC"));
                $request->merge(['schedule_time' => $given->format("Y-m-d H:i:s")]);
            }

            $product = Product::find($request->product_id);
            if($product && $product->available_seats < $request->seats){
                return response()->json(['status' => 203, 'message' => $request->seats.' Seats not Availeble']);
            }

            if($product->extra_time)
            {
                $beforeTime = ($product->extra_time + ($request->duration_time * 2));
                $scheduleDatetime = Carbon::parse($product->pickup_time)->subMinute($beforeTime);
                $timezone = $request->time_zone;
                $given = new DateTime($scheduleDatetime, new DateTimeZone($timezone));
                $given->setTimezone(new DateTimeZone("UTC"));
                $request->merge(['schedule_time' => $given->format("Y-m-d H:i:s")]);
            }

           // pr($request->all());
            $user = Auth::user();
            $order_place = $this->orderPlaceForPickupDelivery($request);

            if($order_place['data']['recurring_booking_time'])
            {
                DB::commit();
                return response()->json([
                    'status' => '200',
                    'redirect' => route('user.orders'),
                    'message' => 'Recurring Order placed successfully.'
                ]);
            }

            if( ( $order_place && $order_place['status'] == 200 && ($request->payment_option_id == 1) ) || (( $request->has('transaction_id') ) && (!empty($request->transaction_id))) ){
                $data = [];
                $order = $order_place['data'];
                $request_to_dispatch = $this->placeRequestToDispatch($request, $order, $request->vendor_id);
                if($request_to_dispatch && isset($request_to_dispatch['task_id']) && $request_to_dispatch['task_id'] > 0){
                    $order_place['data']['dispatch_traking_url'] = $request_to_dispatch['dispatch_traking_url'];
                    // $order_place['data']['invalid_agent'] = $request_to_dispatch['invalid_agent'];
                    $order_place['data']['user_name'] = $user->email;
                    $order_place['data']['phone_number'] = '+'.$user->dial_code.''.$user->phone_number;


                    // return  $order_place;
                }
                else{
                    DB::rollback();
                    return $request_to_dispatch;
                }
            }else if($order_place && $order_place['status'] == 200 && ($request->payment_option_id == 48 || $request->payment_option_id == 59 ) ){
                $data = [];
                $order = $order_place['data'];
                $request_to_dispatch = $this->placeRequestToDispatch($request, $order, $request->vendor_id);
                if($request_to_dispatch && isset($request_to_dispatch['task_id']) && $request_to_dispatch['task_id'] > 0){

                    $order_place['data']['dispatch_traking_url'] = $request_to_dispatch['dispatch_traking_url'];
                    $order_place['data']['user_name'] = $user->email;
                    $order_place['data']['phone_number'] = '+'.$user->dial_code.''.$user->phone_number;


                }
                else{
                    DB::rollback();
                    return $request_to_dispatch;
                }
            }

            DB::commit();
            //Send message if ride is booked for friend
            if(@$request->share_ride_users && count($request->share_ride_users)>0)
            {
                $share_ride_users = Rider::whereIn('id',$request->share_ride_users)->get();
                foreach($share_ride_users as $share_ride_users)
                {

                    $share_ride_users = (object)$share_ride_users;
                    $dialCode = empty($share_ride_users->dial_code) ? '+91' : null;
                    $phone = $dialCode.$share_ride_users->phone_number;
                    $msg = "Hi ".($share_ride_users->first_name??'User').", ".$user->name." has booked a ride. Tracking url is ".$request_to_dispatch['dispatch_traking_url']??null;
                    $send = $this->sendSms('', '', '', '', $phone, $msg);
                }
            }


             //Send sendNotificationToCustomer
             if (isset($request->schedule_time) && !empty($request->schedule_time))
             {
                 $order_number = $order_place['data']->order_number??$order_place['data']['order_number'];
                 $device_token = UserDevice::whereUserId($user->id)->orderBy('id','desc')->value('device_token');
                 sendNotificationToCustomer($device_token,$order_number);
             }

             return  $order_place;


        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // order update for pickup delivery
    public function orderUpdateAfterPaymentPickupDelivery($request){

          try {
            $order_number =  isset($request->order_number) ? $request->order_number : ($request->order_id ?? "");

            $order = Order::where('order_number',$order_number)->with('orderLocation')->first();

           if($order && $order->orderLocation){
            if (($request->has('transaction_id')) && (!empty($request->transaction_id))) {
                $order->payment_status = 1;
            }
            $order->save();
            $request->merge([
                "product_id" => $order->orderLocation->product_id,
                "vendor_id" => $order->orderLocation->vendor_id,
                "phone_number" => $order->orderLocation->phone_number,
                "email" => $order->orderLocation->email,
                "tasks" => $order->orderLocation->tasks ? json_decode($order->orderLocation->tasks,true) : "",
            ]);


            if (($request->payment_option_id != 1) && ($request->payment_option_id != 38) && ($request->payment_option_id != 2) && ($request->has('transaction_id')) && (!empty($request->transaction_id))) {

                $payment = Payment::where('transaction_id',$request->transaction_id)->first();
                if(!$payment){
                    $payment = new Payment();
                }
                $payment->date = date('Y-m-d');
                $payment->order_id = $order->id;
                $payment->user_id = $request->user_id;
                $payment->transaction_id = $request->transaction_id;
                $payment->balance_transaction = $order->payable_amount;
                $payment->payment_option_id = $request->payment_option_id;
                $payment->type = 'pickup_delivery';
                $payment->save();
            }
            $request_to_dispatch = $this->placeRequestToDispatch($request,$order,$request->vendor_id);

            if($request_to_dispatch && isset($request_to_dispatch['task_id']) && $request_to_dispatch['task_id'] > 0){
                $user = User::find($order->user_id);
                $order_place['data']['dispatch_traking_url'] = $request_to_dispatch['dispatch_traking_url'];
                $order_place['data']['user_name'] = $user->email;
                $order_place['data']['phone_number'] = '+'.$user->dial_code.''.$user->phone_number;
                return  $order_place;
            }else{
                return $request_to_dispatch;
            }

        }
        }catch(\Exception $e){
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
        $user = Auth::user();
        $loyalty_points_used = 0;
        $loyalty_points_earned = 0;
        $currency_id = Session::get('customerCurrency');
        $action = 'pick_drop';
        $luxury_option = LuxuryOption::where('title', $action)->first();
        $request->address_id = $request->address_id ??null;
        $request->payment_option_id = $request->payment_option_id ??1;
        if ($user) {
            $loyalty_amount_saved = 0;
            $total_service_fee = 0;
            $total_toll_amount = 0;
            $redeem_points_per_primary_currency = '';
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

                $loyalty_points_used = 0;
                $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
                if ($order_loyalty_points_earned_detail) {
                    $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                    if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                        $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                    }
                }
                if($request->payment_option_id == 2){
                    $payment_option = 1;
                }
                else{
                    $payment_option = $request->payment_option_id;
                }
                $order                      = new Order;
                $order->user_id             = $user->id;
                $order->order_number        = generateOrderNo();
                $order->address_id          = $request->address_id;
                $order->payment_option_id   = $payment_option;
                $order->is_postpay          = ($request->postpay_enable)?$request->postpay_enable:0;
                $order->total_other_taxes   = ($request->total_other_taxes_string)?$request->total_other_taxes_string:'';
                $schedule_datetime_del      = NULL;
                if (isset($request->schedule_time) && !empty($request->schedule_time)) {
                    $schedule_datetime_del  =$request->schedule_time ;// Carbon::parse($request->schedule_time, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                }

                if(@$request->payment_option_id == '60')
                {
                    $order->company_id          = auth()->user()->company_id;
                }

                $recurringformPost = '';
                if(isset($request->recurringformPost) && !empty($request->recurringformPost))
                {
                    //This Function Return objected array of recurring data
                    $recurringformPost = recurringCalculationFunction($request);

                     //Check if recurring_booking_type,recurring_week_day,recurring_week_type,recurring_day_data,recurring_booking_time coulmn exists in table
                    $order->recurring_booking_type  =@$recurringformPost->action??null;
                    $order->recurring_week_day      =@$recurringformPost->weekTypes??null;
                    $order->recurring_week_type     =@$recurringformPost->weekTypes??null;
                    $order->recurring_day_data      =@$recurringformPost->selectedCustomdates??null;
                    $order->recurring_booking_time  =@$recurringformPost->schedule_time??null;
                    $order->scheduled_date_time     = Null;

                }else{

                    $order->scheduled_date_time = $schedule_datetime_del;

                }

               $returnBookingTime = null;
                /*book for a friend*/
                $order->type                = $request->type;
                $order->friend_name         = $request->friendName;
                $order->friend_phone_number = $request->friendPhoneNumber;
                $order->luxury_option_id    = $luxury_option->id;

                $order->specific_instructions = $request->task_description ?? '';

                if ($client_preference->is_hourly_pickup_rental != 1) {

                $order->recurring_booking_time = $returnBookingTime ;
                $order->recurring_week_type = $returnBookingTime ? 2 : null; //once
                }
                $order->flight_no = $request->flight_number ?? '';
                $order->adults = $request->number_of_adult ?? 0;
                $order->name_sign_board = $request->name_sign_board ?? '';
                $order->rental_hours = $request->rental_hours ?? 0;
                $order->save();


                // save pickup delivery task
                $order_location               = new OrderLocations();
                $order_location->order_id     = $order->id;
                $order_location->product_id   = $request->product_id;
                $order_location->vendor_id    = $request->vendor_id;
                $order_location->phone_number = $request->phone_number ?? null;
                $order_location->email        = $request->email ?? null;
                $order_location->tasks        = json_encode($request->tasks );
                $order_location->save();


                $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
                $vendor = Vendor::whereHas('product', function ($q) use ($request) {
                    $q->where('id', $request->product_id);
                })->select('*','id as vendor_id')->orderBy('created_at', 'asc')->first();
                $vendor_id                = $vendor->id;
                $product                  = Product::where('id',$request->product_id)->with('pimage', 'variants', 'taxCategory.taxRate', 'addon')->first();
                $total_delivery_fee       = 0;
                $delivery_fee             = 0;
                $vendor_payable_amount    = 0;
                $vendor_discount_amount   = 0;
                $order_vendor             = new OrderVendor;
                $order_vendor->status     = 0;
                $order_vendor->user_id    = $user->id;
                $order_vendor->order_id   = $order->id;
                $order_vendor->vendor_id  = $vendor->id;
                $order_vendor->save();
                $variant                  = $product->variants->where('product_id', $request->product_id)->first();
                $variant->price           = $request->amount;
                $variant->toll_price      = $request->tollamount;
                $quantity_price           = 0;
                $divider                  = (empty($clientCurrency->doller_compare) || $clientCurrency->doller_compare < 0) ? 1 : $clientCurrency->doller_compare;
                $divider                  = isset($divider) ? $divider : 1;
                $price_in_currency        = $request->amount / $divider;
                $price_in_dollar_compare  = $price_in_currency * $divider;
                $quantity_price           = $price_in_dollar_compare * 1;
                $payable_amount           = $payable_amount + $quantity_price;
                $vendor_payable_amount    = $vendor_payable_amount + $quantity_price;
                $product_taxable_amount   = 0;
                $product_payable_amount   = 0;
                $vendor_taxable_amount    = 0;

                if ($request->total_other_taxes) {
                    $payable_amount = $payable_amount + $request->total_other_taxes;
                }

                $vendor_taxable_amount              += $request->total_other_taxes;
                $total_amount                       += $variant->price;
                $order_product                       = new OrderProduct;
                $order_product->order_vendor_id      = $order_vendor->id;
                $order_product->order_id             = $order->id;
                $order_product->price                = $variant->price;
                $order_product->toll_price           = $variant->toll_price;
                $order_product->quantity             = 1;
                $order_product->vendor_id            = $vendor->id;
                $order_product->product_id           = $product->id;
                $order_product->created_by           = null;
                $order_product->variant_id           = $variant->id;
                $order_product->product_name         = $product->sku;
                $order_product->no_seats_for_pooling = (isset($request->is_cab_pooling) && $request->is_cab_pooling== 1 && isset($request->no_seats_for_pooling))?$request->no_seats_for_pooling:0;
                $order_product->is_cab_pooling       = isset($request->is_cab_pooling)?$request->is_cab_pooling:0;
                $order_product->booking_seats        = $request->seats ?? 0;

                if(isset($request->user_product_order_form) && !empty($request->user_product_order_form))
                $user_product_order_form             = json_encode($request->user_product_order_form);
                else
                $user_product_order_form             = null;

                $order_product->user_product_order_form = $user_product_order_form;
                if ($product->pimage) {
                    $order_product->image            = $product->pimage->first() ? $product->pimage->first()->path : '';
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

                $coupon_id     = null;
                $coupon_name   = null;
                $actual_amount = $vendor_payable_amount;
                if ($request->coupon_id) {
                    $coupon      = Promocode::find($request->coupon_id);
                    $coupon_id   = $coupon->id;
                    $coupon_name = $coupon->name;
                    if ($coupon->promo_type_id == 2) {
                        $coupon_discount_amount  = $coupon->amount;
                        $total_discount         += $coupon_discount_amount;
                        $vendor_payable_amount  -= $coupon_discount_amount;
                        $vendor_discount_amount +=$coupon_discount_amount;
                    } else {
                        $coupon_discount_amount = ($quantity_price * $coupon->amount / 100);
                        $final_coupon_discount_amount = $coupon_discount_amount * $clientCurrency->doller_compare;
                        $total_discount += $final_coupon_discount_amount;
                        $vendor_payable_amount -=$final_coupon_discount_amount;
                        $vendor_discount_amount +=$final_coupon_discount_amount;
                    }
                }
                $total_toll_amount     +=(isset($request->tollamount))?$request->tollamount:0.00;
                $total_service_fee     +=(isset($request->servicechargeamount))?$request->servicechargeamount:0.00;

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
                $order_vendor->taxable_amount = $taxable_amount;
                $order_vendor->save();
                $order_status = new VendorOrderStatus();
                $order_status->order_id = $order->id;
                $order_status->vendor_id = $vendor_id;
                $order_status->order_status_option_id = 1;
                $order_status->order_vendor_id = $order_vendor->id;
                $order_status->save();

                // $loyalty_points_earned = LoyaltyCard::getLoyaltyPoint($loyalty_points_used, $payable_amount);
                $order->total_amount = $total_amount;
                $order->total_discount = $total_discount;
                $order->taxable_amount = $taxable_amount;
                if ($loyalty_amount_saved > 0) {
                    if ($payable_amount < $loyalty_amount_saved) {
                        $loyalty_amount_saved =  $payable_amount;
                        $loyalty_points_used = $payable_amount * $redeem_points_per_primary_currency;
                    }
                }
                $order->total_delivery_fee   = $total_delivery_fee;
                $order->loyalty_points_used  = $loyalty_points_used;
                $order->loyalty_amount_saved = $loyalty_amount_saved;
                $order->total_toll_amount    = $total_toll_amount;
                $order->total_service_fee    = $total_service_fee;
                $finalAmount                 = $delivery_fee + $payable_amount - $total_discount - $loyalty_amount_saved + $total_toll_amount + $request->servicechargeamount;
                if ($user) {
                    $now = Carbon::now()->toDateTimeString();
                    $user_subscription = SubscriptionInvoicesUser::with('features')
                        ->select('id', 'user_id', 'subscription_id')
                        ->where('user_id', $user->id)
                        ->where('end_date', '>', $now)
                        ->orderBy('end_date', 'desc')->first();
                    if (!empty($user_subscription)) {
                        foreach ($user_subscription->features as $feature) {
                            if ($feature->feature_id == 2) {
                                $finalAmount = $finalAmount - ($feature->percent_value * $finalAmount / 100);
                            }
                        }
                    }
                }

              //  $order->payable_amount = $finalAmount;

                if(!empty($request->subscription_payable_amount)){
                    $order->subscription_discount = $request->amount - $request->subscription_payable_amount;
                }

                $order->loyalty_points_earned = $loyalty_points_earned['per_order_points'] ?? 0;
                $order->loyalty_membership_id = $loyalty_points_earned['loyalty_card_id'] ?? 0;
                if (($request->has('transaction_id')) && (!empty($request->transaction_id))) {
                    $order->payment_status = 1;
                }
                $wallet_amount_used = 0;
                $ex_gateways_wallet = [4,36,40,41,22]; // stripe,mycash,userede,openpay,ccavenue
                if ($user->balanceFloat > 0) {
                    $wallet = $user->wallet;
                    $wallet_amount_used = $user->balanceFloat;
                    if ($wallet_amount_used > $finalAmount) {
                        $wallet_amount_used = $finalAmount;
                    }
                    $order->wallet_amount_used = $wallet_amount_used;
                    // Deduct wallet amount if payable amount is successfully done on gateway
                    if (($wallet_amount_used > 0) && (! in_array($request->payment_option_id, $ex_gateways_wallet))) {
                        $wallet->withdrawFloat($order->wallet_amount_used, [
                            'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>'
                        ]);
                    }
                }
                $order->payable_amount = $finalAmount - $wallet_amount_used;
                $order->save();


                 /** for Recurring Service */
                 if(!empty($order->recurring_booking_time) && !empty($request->recurringformPost)){
                    $this->saveOrderLongTermServiceSchedule($order,$order_product->id);
                }

                if (($request->payment_option_id != 1) && ($request->payment_option_id != 2) && ($request->has('transaction_id')) && (!empty($request->transaction_id))) {
                    $payment = new Payment();
                    $payment->date = date('Y-m-d');
                    $payment->order_id = $order->id;
                    $payment->transaction_id = $request->transaction_id;
                    $payment->balance_transaction = $order->payable_amount;
                    $payment->type = 'pickup/delivery';
                    $payment->save();
                }
            }
            // DB::commit();

            $order['route'] = route('front.booking.details',$order->order_number);
            $data = [];
            $data['status'] = 200;
            $data['recurring_booking_time'] = @$order->recurring_booking_time??null;
            $data['message'] =  'Order Placed';
            $data['data'] = $order;
            return $data;
        }
    }

    // place Request To Dispatch
    public function placeRequestToDispatch($request,$order,$vendor){
        try {
            $meta_data = '';
            $tasks = array();
            $dispatch_domain = $this->checkIfPickupDeliveryOn();
            $customer = User::find($order->user_id);
            $wallet = $customer->wallet;

            if ($dispatch_domain && $dispatch_domain != false) {
                if ($request->payment_option_id == 1) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order->payable_amount;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;

                }
                $unique = $customer->code;
                $team_tag = $unique."_".$vendor;
                $dynamic = uniqid($order->id.$vendor);
                $product = Product::find($request->product_id);
                $order_agent_tag = $product->tags??'';
                $client_do = Client::where('code',$unique)->first();
                $domain = '';
                if(!empty($client_do->custom_domain)){
                    $domain = $client_do->custom_domain;
                }else{
                    $domain = $client_do->sub_domain.env('SUBMAINDOMAIN');
                }
                $call_back_url = "https://".$domain."/dispatch-pickup-delivery/".$dynamic;

                $type=$request->type??0;
                $friendName=$request->friendName?? null;
                $friendPhoneNumber=$request->friendPhoneNumber?? null;
                if(empty($friendPhoneNumber)){
                    $type=0;
                }

                $task_type = 'now';
                if(!empty($request->task_type)){
                    $task_type = $request->task_type;
                }elseif(!empty($order->scheduled_date_time)){
                    $task_type = 'schedule';
                }
                // dd($request->task_type);
                if ($customer->dial_code == "971") {
                    // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                    $customerno = "0" . $customer->phone_number;
                } else {
                    // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                    $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
                }
                $order_vendor = OrderVendor::where(['order_id' => $order->id,'vendor_id' => $vendor])->first();
                $client = Client::orderBy('id', 'asc')->first();

                $user = Auth::user();
                if(@$user && empty($user->timezone))
                {
                    $client_timezone = DB::table('clients')->first('timezone');
                    $user->timezone = $client_timezone->timezone ?? $user->timezone;
                }
                $schedule_datetime_del = NULL;
                if (isset($request->schedule_time) && !empty($request->schedule_time)) {
                    $schedule_datetime_del = $request->schedule_time;//Carbon::parse($request->schedule_time, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                }

                $client_preferences_addional = ClientPreferenceAdditional::pluck('key_value','key_name');

                if(isset($client_preferences_addional['pickup_notification_before']) && $client_preferences_addional['pickup_notification_before'] == 1)
                {
                    $notify_hour = $client_preferences_addional['pickup_notification_before_hours'] ?? 1;
                    $reminder_hour = $client_preferences_addional['pickup_notification_before2_hours'] ?? 1;
                }
                $allocation_type = 'a';
                if(isset($request->unique_id) || isset($request->driver_id)){
                    $allocation_type = 'm';
                }
                $postdata =  [
                    'order_number' =>  $order->order_number,
                    //'order_type' =>  $order->type,
                    // 'order_friend_name' =>  $order->friend_name,
                    // 'order_number' =>  $order->friend_phone_number,
                    'notify_all' => $request->send_to_all ?1: 0,
                    'barcode' => '',
                    'allocation_type' => @$request->unique_id ? 'notify' : 'a',
                    'task' => $request->tasks??null,
                    'order_team_tag' => $team_tag,
                    'task_type' => $task_type,
                    'order_agent_tag' => $order_agent_tag,
                    'call_back_url' => $call_back_url??null,
                    'customer_email' => $customer->email ?? '',
                    'cash_to_be_collected' => $payable_amount??0.00,
                    'schedule_time' => $schedule_datetime_del ?? null,
                    'task_description' => null,
                    'order_number' =>  $order->order_number,
                    'order_time_zone' => $request->order_time_zone ??null,
                    'customer_name' => $customer->name ?? 'Dummy Customer',
                    'recipient_email' => $request->email ?? $customer->email,
                    'recipient_phone' => $request->phone_number ?? $customerno,
                    'customer_phone_number' => $customerno ?? rand(111111,11111),
                    'customer_dial_code' => $customer->dial_code ?? null,
                    'type'=>$type,
                    'friend_name'=>$friendName,
                    'friend_phone_number'=>$friendPhoneNumber,
                    'vendor_id' => $vendor,
                    'order_vendor_id' => $order_vendor->id,
                    'dbname' => $client->database_name,
                    'order_id' => $order->id,
                    'customer_id' => $order->user_id,
                    'user_icon' => $customer->image,
                    'toll_passes' => 'IN_FASTAG',
                    'VehicleEmissionType' => 'GASOLINE',
                    'travelMode' => 'TAXI',
                    'no_seats_for_pooling' => (isset($request->is_cab_pooling) && $request->is_cab_pooling== 1 && isset($request->no_seats_for_pooling))?$request->no_seats_for_pooling:0,
                    'is_cab_pooling' => isset($request->is_cab_pooling)?$request->is_cab_pooling:0,
                    'available_seats' => $product->seats_for_booking,
                    'driver_id' => $request->driver_id ?? null,
                    'driver_unique_id' => $request->unique_id ?? null,
                    'notify_hour' => $notify_hour ?? 0,
                    'reminder_hour' => $reminder_hour ?? 0,
                    'app_call' => 0,
                    'call_notification' => 0
                ];

                if(isset($request->bid_task_type) && !empty($request->bid_task_type)){
                    $postdata['bid_task_type']    = $request->bid_task_type;
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
        }catch(\Exception $e){
                $data = [];
                $data['status'] = 400;
                $data['message'] =  $e->getMessage().'- line-'.$e->getLine();
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
            //$total_minimum_spend = 0;
            // foreach ($cart_products as $cart_product) {
            //     $total_minimum_spend += $cart_product->variant->first() ? $cart_product->variant->first()->price * 1 : 0;
            // }
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
                return response()->json(['error' => 'Invalid vendor id.'], 404);
            }




            $cart_detail = Promocode::where('id', $request->coupon_id)->first();
            if(!$cart_detail){
                return $this->errorResponse('Invalid Promocode Id', 422);
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
                $cart_detail['currency_symbol'] = Session::get('currencySymbol');
            }
            if($cart_detail->promo_type_id == 1){
                $cart_detail['new_amount'] = ($request->amount * ($cart_detail->amount/100));
                if($cart_detail['new_amount'] < 0)
                $cart_detail['new_amount'] = 0.00;
                $cart_detail['currency_symbol'] = Session::get('currencySymbol');
            }
            return $this->successResponse($cart_detail, 'Promotion Code Used Successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function postRemovePromoCode(Request $request){
        try {
            $response = ['currency_symbol' => Session::get('currencySymbol')];
            return $this->successResponse($response, 'Promotion Code Removed Successfully.', 201);
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


    /**
     * ratings details
    */
    public function getProductOrderForm(Request $request){
        try {
            $langId = Auth::user()->language;
            if(empty($langId))
            $langId = ClientLanguage::orderBy('is_primary','desc')->value('language_id');

            $product_faqs = ProductFaq::where('product_id',$request->product_id)->with(['translations' => function ($qs) use($langId){
                $qs->where('language_id',$langId);
            }])->get();

            if(isset($product_faqs)){

                if ($request->ajax()) {
                 return \Response::json(\View::make('frontend.modals.product-order-form', array('product_faqs'=>  $product_faqs))->render());
                }

            }
            return \Response::json(\View::make('frontend.modals.product-order-form', array('product_faqs'=>  $product_faqs))->render());

            return $this->errorResponse('Invalid product form ', 404);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function createBidRideRequest(Request $request)
    {
        {
            DB::beginTransaction();
            try
            {
                $vendor = Vendor::where('id', $request->vendor_id)->first();
                $product = Product::where('id', $request->product_id)->first();

                if(!$vendor || !$product){
                    return response()->json(['status' => 201, 'message' => __('No record found.')], 404);
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
                    return response()->json(['status' => 200,'data' => $UserBidRideRequest, 'message' => "Request created, Please wait a while till someone respond to your request."], 200);
                }else{
                    DB::rollback();
                    return response()->json(['status' => 201,'message' => "Error, Something went wrong."], 400);
                }
            }
            catch(\Exception $e)
            {
                DB::rollback();
                return response()->json(['status' => 201,'message' => "Error, Something went wrong. ".$e->getMessage() ], 400);
            }
        }
    }

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

    public function getBidsRelatedToOrderRide(Request $request)
    {
        try
        {
            $getAdditionalPreference = getAdditionalPreference(['bid_expire_time_limit_seconds']);
            $order_bid_id = $request->order_id;
            $task_type    = $request->task_type;
            $biddata      = PickDropDriverBid::where('order_bid_id', $order_bid_id)->where(function($q) use ($task_type){
                if(isset($task_type)){
                    $q->where('task_type', $task_type);
                }
            })->where('expired_at', '>', now()->format('Y-m-d H:i:s'))->where('status', 0)->get();
            return $this->successResponse(['biddata' => $biddata, 'bid_expire_time_limit_seconds' => $getAdditionalPreference['bid_expire_time_limit_seconds']], 200);
        }
        catch (\Exception $e) {
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }

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
