<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CabBookingLayout;
use App\Models\CabBookingLayoutTranslation;
use App\Models\Category;
use App\Models\OnboardSetting;
use App\Models\Order;
use App\Models\SubscriptionInvoicesVendor;
use App\Models\VendorOrderStatus;
use App\Models\WebStylingOption;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Http\Traits\ApiResponser;
use App\Models\ClientPreference;
use App\Http\Controllers\Front\FrontController;
use App\Models\VendorCities;
use App\Http\Traits\HomePage\HomePageTrait;
use App\Http\Traits\OrderTrait;
use App\Http\Traits\ProductActionTrait;
use App\Http\Traits\VendorTrait;
use App\Http\Traits\YachtTrait;

class YachtController extends FrontController
{
    use ApiResponser, OrderTrait,ProductActionTrait, HomePageTrait,VendorTrait, YachtTrait;
    private $field_status = 2;
    public $cities = [];
    public $additionalPreference =[];
    public $client_preferences = [];
    
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            if (Session::has('preferences') && !empty(Session::get('preferences'))) {
                $this->client_preferences = Session::get('preferences');
                return $next($request);
            }else{
                $this->client_preferences = ClientPreference::first();
                return $next($request);
            }
            abort(403);
        });
        
    }

    public function yacht(Request $request)
    {
        // try {
            $home = array();
            $vendor_ids = array();
            if ($request->has('ref')) {
                session(['referrer' => $request->query('ref')]);
            }
            $additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency','is_long_term_service','is_admin_vendor_rating', 'is_service_product_price_from_dispatch','is_service_price_selection']);
            $latitude = Session::get('latitude') ?? null;
            $longitude = Session::get('longitude') ?? null;
            $curId = Session::get('customerCurrency');
            $langId = Session::get('customerLanguage');
            $client_config = Session::get('client_config');
            $selectedAddress = Session::get('selectedAddress');
            $client_preferences = $this->client_preferences;
            $_REQUEST['request_from'] = 1;

            $navCategories = $this->categoryNav($langId);
            Session::put('navCategories', $navCategories);
            $vendor_type = $request->has('type') ? $request->type : Session::get('vendorType');


            $count = 0;
            if ($client_preferences) {
                foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                    $clientVendorTypes = $vendor_typ_key.'_check';
                    if($client_preferences->$clientVendorTypes == 1){
                        $count++;
                    }
                }

                if(empty($latitude) && empty($longitude)){
                    $latitude = $client_preferences->Default_latitude;
                    $longitude = $client_preferences->Default_longitude;
                }

            }
            if(count($navCategories) > 0 && ($vendor_type =='pick_drop') &&  ($count!=1) ){
                $categoriesSlug = $navCategories[0]->slug;
                return redirect()->route('categoryDetail',$categoriesSlug);
            }

            $banners = $this->getBannersForHomePage($client_preferences, 'banners', $latitude, $longitude);


            $mobile_banners = $this->getBannersForHomePage($client_preferences, 'mobile_banners', $latitude, $longitude);


            $home_page_labels = CabBookingLayout::where('is_active', 1)->web()->where('for_no_product_found_html',0)->orderBy('order_by');


            if (isset($langId) && !empty($langId))
                $home_page_labels = $home_page_labels->with(['translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }]);

            $home_page_labels = $home_page_labels->get();
           
            //     $home_page_labels = HomePageLabel::with('translations')->where('is_active', 1)->orderBy('order_by')->get();
            $request->request->add(['type'=>Session::get('vendorType')??'delivery','noTinJson'=>1] );
            $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();

            $CabBookingLayout = CabBookingLayout::web()->where('is_active', 1);
            $home_page_pickup_labels   = clone $CabBookingLayout;
            $for_no_product_found_html = clone $CabBookingLayout;
            $enable_layout             = clone $CabBookingLayout;
            $enable_layout = $enable_layout->orderBy('order_by','asc')->pluck('slug')->toArray();
            $homePageData = $this->rentalProducts($request, $set_template, $enable_layout, $additionalPreference);

            $home_page_labels = $home_page_labels->map(function($da) use ($homePageData, $navCategories) {
                if($da->slug!='pickup_delivery' && $da->slug!='dynamic_page' ){
                    $da[$da->slug] = $homePageData[$da->slug] ?? '';
                }
                if( $da->slug == 'nav_categories'  ){
                    $da['nav_categories'] = $navCategories ?? '';
                }
                return $da;
            });
            
            $only_cab_booking = OnboardSetting::where('key_value', 'home_page_cab_booking')->count();
            if ($only_cab_booking == 1)
                return Redirect::route('categoryDetail', 'cabservice');

            $home_page_pickup_labels  = $home_page_pickup_labels->with('translations')->where('for_no_product_found_html',0)->orderBy('order_by')->get();

            $for_no_product_found_html = $for_no_product_found_html->with('translations')->where('for_no_product_found_html',1)->orderBy('order_by')->get();
            
            $categories = [];
            if(isset($set_template)  && ($set_template->template_id == 8 || $set_template->template_id == 9)){
                $categories = Category::with('translation_one')->select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
                ->where('id', '>', '1')
                ->whereIn('type_id', [7, 10])
                ->where(function ($q) {
                    $q->whereNull('vendor_id');
                })->orderBy('position', 'asc')
                ->orderBy('id', 'asc')
                ->where('status', 1)
                ->orderBy('parent_id', 'asc')->get();
            }

            $is_service_product_price_from_dispatch_forOnDemand = 0;
          
            $getOnDemandPricingRule = getOnDemandPricingRule($vendor_type, Session::get('onDemandPricingSelected'),$additionalPreference);
         
            $is_service_product_price_from_dispatch_forOnDemand =$getOnDemandPricingRule['is_price_from_freelancer'];
            // if(($additionalPreference['is_service_product_price_from_dispatch'] == 1) && ( Session::get('vendorType') == 'on_demand')){
            //     $is_service_product_price_from_dispatch_forOnDemand =1;
            // }
           
            $homeData = ['categories' => $categories,'home' => $home,  'count' => $count, 'for_no_product_found_html' => $for_no_product_found_html,'homePagePickupLabels' => $home_page_pickup_labels, 'homePageLabels' => $home_page_labels, 'clientPreferences' => $client_preferences, 'banners' => $banners,'mobile_banners'=>$mobile_banners, 'navCategories' => $navCategories, 'selectedAddress' => $selectedAddress, 'latitude' => $latitude, 'longitude' => $longitude,'enable_layout'=>$enable_layout,'homePageData'=>$homePageData ,'is_service_product_price_from_dispatch_forOnDemand'=> $is_service_product_price_from_dispatch_forOnDemand];
            return view('frontend.yacht.index')->with($homeData);
            // $view = view('frontend.yacht.index')->with($homeData)->render();
                
        // } catch (\Exception $e) {
        //     pr($e->getCode());
        //     die;
        // }
    }

    public function rentalProducts(Request $request,$set_template,$enable_layout,$additionalPreference)
    {
        $vendor_ids = $vendors = [];
        $new_products = [];
        $feature_products = [];
        $banners = [];
        
        $p_dim = '260/260';
        if (isset($set_template)  && $set_template->template_id == 3){
            $p_dim = '300/300';
        }elseif(isset($set_template)  && $set_template->template_id == 2){
            $p_dim = '260/260';
        }
        $latitude = Session::get('latitude');
        $longitude = Session::get('longitude');
        $clientdata = Session::get('clientdata');
        $preferences = ClientPreference::first() ;
      

        if( (empty($latitude)) && (empty($longitude)) ){
            $latitude = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_latitude) : 0;
            $longitude = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_longitude) : 0;
        }
        
        //pr($latitude);
        if($request->has('latitude') ){
            $latitude = $request->latitude;
            Session::put('latitude', $latitude);
        }
        if ($request->has('longitude')) {
            $longitude = $request->longitude;
            Session::put('longitude', $longitude);
        }
       
        $selectedAddress = ($request->has('selectedAddress')) ? Session::put('selectedAddress', $request->selectedAddress) : Session::get('selectedAddress');
       
        $currency_id = Session::get('customerCurrency');
        $language_id = Session::get('customerLanguage');

        $currency_id = $this->setCurrencyInSesion();

        $featured_products_title = $vendors_title = $new_products_title = $on_sale_title = $brands_title = $best_sellers_title = $recent_orders_title = $banner_title = $selected_products_title = $trending_vendors_title = '';

        $slugs = array("featured_products", "vendors", "new_products", "on_sale", "brands", "best_sellers", "recent_orders", "banner", "selected_products", "trending");
        $CabBookingLayoutTranslation = CabBookingLayoutTranslation::where('language_id', $language_id)->with('layout')
                                       ->whereHas('layout', function($q) use ($slugs){
                                            $q->whereIn('slug', $slugs);
                                       })->whereNotNull('title')->select('title', 'cab_booking_layout_id')->get();

        foreach($CabBookingLayoutTranslation as $translation)
        {
            switch ($translation->layout->slug) {
                case "featured_products":
                    $featured_products_title = $translation->title;
                    break;
      
                default:
                    break;
            }
        }
        $vendor_ids = $this->getRandomVendorIdsForHomePage($preferences, $request->type, $additionalPreference['is_admin_vendor_rating'], $latitude, $longitude);

        Session::forget('vendorType');
        Session::put('vendorType', $request->type);
      
        if ($preferences) {
            // check vendor Subscription0
           
            if ((empty($latitude)) && (empty($longitude)) && (empty($selectedAddress))) {
                $selectedAddress = $preferences->Default_location_name;
                $latitude = $preferences->Default_latitude??null;
                $longitude = $preferences->Default_longitude??null;
                Session::put('latitude', $latitude);
                Session::put('longitude', $longitude);
                Session::put('selectedAddress', $selectedAddress);
            } else {
                if ($preferences && ($latitude == $preferences->Default_latitude) && ($longitude == $preferences->Default_longitude)) {
                    Session::put('selectedAddress', $preferences->Default_location_name);
                }
            }
        }

        
        if(count($vendor_ids) > 0){
            $vendors = $this->getVendorForHomePage($preferences, "random_or_admin_rating", $clientdata->timezone, $additionalPreference['is_admin_vendor_rating'], $request->type, $language_id, $latitude, $longitude, $vendor_ids);
        }
       
            if (in_array('trending_vendors', $enable_layout)) {  # if enable trending_vendors section in 
            $now = Carbon::now()->toDateTimeString();
            $trending_vendors = SubscriptionInvoicesVendor::whereHas('features', function ($query) {
                $query->where(['subscription_invoice_features_vendor.feature_id' => 1]);
            })
            ->select('id', 'vendor_id', 'subscription_id')
            ->where('end_date', '>=', $now)
            ->pluck('vendor_id')->toArray();

        } 
        
        if (($latitude) && ($longitude)) {
            Session::put('vendors', $vendor_ids);
        }
        
        //get Most Selling Vendors
        $mostSellingVendors = []; //best_sellers
        if (in_array('best_sellers', $enable_layout)) {
            if(!empty($vendors)){
                $mostSellingVendors = collect($vendors);
                $mostSellingVendors = $mostSellingVendors->sortByDesc('selling_count');
            }else{
                if(count($vendor_ids) > 0){
                    $mostSellingVendors = $this->getVendorForHomePage($preferences, "best_sellers", $clientdata->timezone, 0, $request->type, $language_id, $latitude, $longitude, $vendor_ids);
                }
            }
        }
      
        if (in_array('featured_products', $enable_layout)) {  # if enable featured_products section in
            array_push($vendor_ids,57);
            $feature_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'is_featured', $request->type, $featured_products_title,$p_dim);
        } 

        if (in_array('banner', $enable_layout)) {  # if enable banner section in
            $cab_booking_layouts = CabBookingLayout::with('banner_image')->where('slug','banner')->get();
            
            foreach($cab_booking_layouts as $bkey => $bval){
                if(count($bval->banner_image) > 0)
                $banners[$bval->banner_image[0]->cab_booking_layout_id] = $bval->banner_image[0]->banner_image_url;
            }
        } 
      
        /**  Recent order */
        $activeOrders = [];
        if (in_array('recent_orders', $enable_layout)) {  # if enable recent_orders section in 
            $user = Auth::user();

            if ($user) {
                    $activeOrders = Order::with([
                        'vendors' => function ($q) {
                            $q->where('order_status_option_id', '!=', 6);
                        },
                        'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        }, 'vendors.dineInTable.category', 'vendors.products', 'vendors.products.media.image', 'vendors.products.pvariant.media.pimage.image', 'user', 'address'
                    ])->whereHas('vendors', function ($q) {
                        $q->where('order_status_option_id', '!=', 6);
                    })
                        ->where('orders.user_id', $user->id)->take(10)
                        ->orderBy('orders.id', 'DESC')->get();
                        foreach ($activeOrders as $order) {
                            foreach ($order->vendors as $vendor) {
                                $vendor->tag_title = $vendor_title??'0';
                                $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)->where('vendor_id', $vendor->vendor_id)->orderBy('id', 'DESC')->first();
                                $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                                foreach ($vendor->products as $product) {
                                    if (isset($product->pvariant) && $product->pvariant->media->isNotEmpty()) {
                                        $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                                    } elseif ($product->media->isNotEmpty() && isset($product->media->first()->image)) {
                                        $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                                    } else {
                                        $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                                    }
                                    $product->pricedoller_compare = 1;
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
                                    $vendor->dineInTableCategory = $vendor->dineInTable->category->first() ? $vendor->dineInTable->category->first()->title : '';
                                }
                            }
                            $order->converted_scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
                        }
            }
        }
        /**  Recent order end */

        /**  Get cities */
        if (in_array('cities', $enable_layout)) {   # if enable recent_orders section in 
            if($preferences->is_hyperlocal==1){
                $this->getCities($language_id);
            }
        }

      
        $data = [
            'feature_products' => $feature_products
        ];
       
        if($request->has('noTinJson') && $request->noTinJson == 1){
            $data = [
                'featured_products' => $feature_products,
            ];
            return $data ;
        }

        return $this->successResponse($data);
    }



    public function getCities($language_id){
        $this->cities =  VendorCities::with(['translations'=> function ($q) use($language_id) {
                            $q->where('language_id', $language_id);
                        }])->where(function ($q)  {
                            $q->where('latitude','!=', null);
                            $q->where('longitude','!=', null);
                        })->get();

        $this->cities = $this->cities->map(function($da) {
            $da->title = $da->translations->first() ? $da->translations->first()->name : $da->slug ;
            unset($da->translations);
            return $da;
         });
         return $this->cities;
    }

    public function productsSearchResult(Request $request)
    {
        $data = [];
        $pickup_time = null;
        $drop_time = null;
        if($request->has('pick_drop_time')){
            $time = explode('to',$request->pick_drop_time);
            $pickup_time = date('Y-m-d H:i',strtotime($time[0]));
            $drop_time = date('Y-m-d H:i',strtotime($time[1]));
        }
        $pickup = (object) [
            'time' => $pickup_time,
            'latitude' =>  $request->pickup_latitude,
            'longitude' =>  $request->pickup_longitude,
            'address' => $request->pickup_location
        ];

        $dropOff = (object) [
            'time' => $drop_time,
            'latitude' =>  $request->drop_latitude,
            'longitude' =>  $request->drop_longitude,
            'address' => $request->drop_location
        ];
        
        $data = $this->productSearch($request, $pickup, $dropOff);
        Session::put('serviceType', $data['service']);
        return view('frontend.yacht.car-rental',$data);
    }
}
