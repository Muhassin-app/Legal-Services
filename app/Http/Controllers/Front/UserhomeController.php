<?php

namespace App\Http\Controllers\Front;

use Session;
use Carbon\Carbon;
use GuzzleHttp\Client as GCLIENT;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Front\FrontController;
use Illuminate\Contracts\Session\Session as SessionSession;
use App\Models\{Currency, Banner, MobileBanner, FaqTranslations, Category, Brand, Product, ClientLanguage, Vendor, VendorCategory, ClientCurrency,Client, ClientPreference, DriverRegistrationDocument, HomePageLabel, Page, VendorRegistrationDocument, Language, OnboardSetting, CabBookingLayout, WebStylingOption, SubscriptionInvoicesVendor, Order, VendorOrderStatus,CabBookingLayoutTranslation,ShowSubscriptionPlanOnSignup, TaxCategory, VendorCities, UserWishlist};
use Illuminate\Contracts\View\View;
use Illuminate\View\View as ViewView;
use Redirect;
use DB;
use Illuminate\Http\Response;
use Cookie;
use App\Http\Traits\{OrderTrait,ProductActionTrait,VendorTrait, RedisCacheTrait};
use App\Http\Traits\HomePage\{HomePageTrait};

class UserhomeController extends FrontController
{
    use ApiResponser, OrderTrait,ProductActionTrait, HomePageTrait,VendorTrait, RedisCacheTrait;
    private $field_status = 2;
    public $cities = [];
    public $additionalPreference =[];
    public $client_preferences = [];
    public $loc_key = 'geo_fence:locations';


    public function __construct(Request $request)
    {
        $this->additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency','is_long_term_service','is_admin_vendor_rating', 'is_service_product_price_from_dispatch','is_service_price_selection','is_cache_enable_for_home','cache_reset_time_for_home','cache_radius_for_home']);
        $this->cache_minutes =  ($this->additionalPreference['cache_reset_time_for_home']!='') ? $this->additionalPreference['cache_reset_time_for_home'] :  $this->cache_minutes;
        $this->radius =  ($this->additionalPreference['cache_radius_for_home']!='') ? $this->additionalPreference['cache_radius_for_home'] :  $this->radius;

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

    public function setTheme(Request $request)
    {
        $clientData = Client::select('id', 'logo','dark_logo','socket_url')->where('id', '>', 0)->first();
        if ($request->theme_color == "dark") {
            Session::put('config_theme', $request->theme_color);
            return response()->json(['success' => true, 'logo' => $clientData->dark_logo['original']]);
        } else {
            Session::forget('config_theme');
            return response()->json(['success' => true, 'logo' => $clientData->logo['original']]);
        }
    }
    public function getConfig()
    {
        $client_preferences = $this->client_preferences;
        $client_preferences = $client_preferences->makeHidden(['customer_support_key','delivery_service_key','fcm_server_key','fcm_api_key','mail_username','mail_password','sms_key','sms_secret','sms_credentials','fb_client_secret','fcm_storage_bucket','customer_support_application_id','pickup_delivery_service_key']);
        return response()->json(['success' => true, 'client_preferences' => $client_preferences]);
    }

    public function getLastMileTeams()
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $unique = Auth::user()->code;
                $client = new GCLIENT([
                    'headers' => [
                        'personaltoken' => $dispatch_domain->delivery_service_key,
                        'shortcode' => $dispatch_domain->delivery_service_key_code,
                        'content-type' => 'application/json'
                    ]
                ]);
                $url = $dispatch_domain->delivery_service_key_url;
                $res = $client->get($url . '/api/get-all-teams');
                $response = json_decode($res->getBody(), true);
                if ($response && $response['message'] == 'success') {
                    return $response['teams'];
                }
            }
        } catch (\Exception $e) {
        }
    }

    public function getAgentTags()
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $unique = Auth::user()->code;
                $client = new GCLIENT([
                    'headers' => [
                        'personaltoken' => $dispatch_domain->delivery_service_key,
                        'shortcode' => $dispatch_domain->delivery_service_key_code,
                        'content-type' => 'application/json'
                    ]
                ]);
                $url = $dispatch_domain->delivery_service_key_url;
                $res = $client->get($url . '/api/get-all-teams');
                $response = json_decode($res->getBody(), true);
                if ($response && $response['message'] == 'success') {
                    return $response['teams'];
                }
            }
        } catch (\Exception $e) {
        }
    }

    public function checkIfLastMileDeliveryOn()
    {
        $preference = $this->client_preferences;

        if($preference->business_type == 'taxi'){
            if ($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url))
                return $preference;
            else
                return false;
        }elseif($preference->business_type == 'laundry'){
            if ($preference->need_laundry_service == 1 && !empty($preference->laundry_service_key) && !empty($preference->laundry_service_key_code) && !empty($preference->laundry_service_key_url))
                return $preference;
            else
                return false;
        } else{
            if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
                return $preference;
            else
                return false;
        }

    }

    public function driverDocuments()
    {
        try {
            $dispatch_domain = $this->checkIfLastMileDeliveryOn();
             if($dispatch_domain->business_type == 'taxi'){
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $endpoint =$url . "/api/send-documents";
                 $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key, 'shortcode' => $dispatch_domain->pickup_delivery_service_key_code]]);

                $response = $client->post($endpoint);
                $response = json_decode($response->getBody(), true);
                return json_encode($response['data'],true);
            } elseif($dispatch_domain->business_type == 'laundry'){
                    $url = $dispatch_domain->laundry_service_key_url;
                    $endpoint =$url . "/api/send-documents";
                    $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->laundry_service_key, 'shortcode' => $dispatch_domain->laundry_service_key_code]]);

                    $response = $client->post($endpoint);
                    $response = json_decode($response->getBody(), true);
                    return json_encode($response['data'],true);
            } else{

                $url = $dispatch_domain->delivery_service_key_url;
                $endpoint =$url . "/api/send-documents";
                 $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->delivery_service_key, 'shortcode' => $dispatch_domain->delivery_service_key_code]]);

                $response = $client->post($endpoint);

                $response = json_decode($response->getBody(), true);
                // \Log::info('response2');
                // \Log::info(json_encode($response));

                return json_encode($response['data']);
            }

        } catch (\Exception $e) {
            $data = [];
            $data['status'] = 400;
            $data['message'] =  $e->getMessage().'--'.$e->getLine();
            \Log::info('catch error');
            \Log::info([$data]);
            return [];
        }
    }

    public function driverSignup()
    {
        $user = Auth::user();
        $language_id = Session::get('customerLanguage');
        $client_preferences = $this->client_preferences;
        $navCategories = $this->categoryNav($language_id);
        $client = Auth::user();
        $page_detail = Page::with(['translations' => function ($q) {
            $q->where('language_id', session()->get('customerLanguage'));
        }])->where('slug', 'driver-registration')->firstOrFail();
        $last_mile_teams = [];

        $tag = [];

        $showTag = implode(',', $tag);
        $driver_registration_documents = json_decode($this->driverDocuments());
        return view('frontend.driver-registration', compact('page_detail', 'navCategories', 'user', 'showTag', 'driver_registration_documents'));
    }

    public function checkIfLastMileOn()
    {
        $preference = $this->client_preferences;
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getExtraPage(Request $request)
    {
        $user = Auth::user();
        $language_id = Session::get('customerLanguage');
        $client_preferences = $this->client_preferences;
        $navCategories = $this->categoryNav($language_id);
        $page_detail = Page::with(['translations' => function ($q) {
            $q->where('language_id', session()->get('customerLanguage'));
        }])->where('slug', $request->slug)->firstOrFail();
        if ($page_detail->primary->type_of_form != 2) {
            if($page_detail->primary->type_of_form == 3){
             $faq =   FaqTranslations::where('page_id',$page_detail->id)->where('language_id', $language_id)->get();
             $page_detail->faqs_details = $faq;
            }
            $vendor_registration_documents = VendorRegistrationDocument::with(['primary','options','options.translation' => function($query) use($language_id) {
                $query->where('language_id', session()->get('customerLanguage'));
            }])->get();
            $builds = array();
            $categories = Category::with('translation_one')->select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
                                    ->where('id', '>', '1')
                                    // ->where('is_core', 1)
                                    ->whereNotIn('type_id', [4, 5])
                                    ->where(function ($q) {
                                        $q->whereNull('vendor_id');
                                    })->orderBy('position', 'asc')
                                    ->orderBy('id', 'asc')
                                    ->where('status', 1)
                                    ->orderBy('parent_id', 'asc')->get();
                if ($categories) {
                    $builds = $this->buildTree($categories->toArray());
                }

                $VendorCategory =array();

                $templetes  = \DB::table('vendor_templetes')->where('status', 1)->get();
                $server = env('APP_ENV', 'development');
                $langId = session()->get('customerLanguage');
                $privacy = Page::with(['translations' => function ($q) use($langId) {
                    $q->where('language_id', $langId)->where('type_of_form',[4]);   # get privacy & terms url
                }])->whereHas('translations', function ($q) use($langId) {
                    $q->where('language_id', $langId)->where('type_of_form',[4]);   # get privacy & terms url
                })->first();

                $terms = Page::with(['translations' => function ($q) use($langId) {
                    $q->where('language_id', $langId)->where('type_of_form',[5]);   # get privacy & terms url
                }])->whereHas('translations', function ($q) use($langId) {
                    $q->where('language_id', $langId)->where('type_of_form',[5]);   # get privacy & terms url
                })->first();
                return view('frontend.extrapageNew', compact('page_detail','templetes','VendorCategory','builds','navCategories', 'client_preferences', 'user', 'vendor_registration_documents','terms','privacy'));

        }else {
            $tag = [];
            $showTag = implode(',', $tag);
            $client = Client::with('country')->first();
            $docs = $this->driverDocuments();

            $driver_registration_documents = [];

            // Check if $docs is a non-empty string
            if (isset($docs) && is_string($docs) && !empty($docs)) {
                // Decode the JSON string as an object
                $driverDocs = json_decode($docs);

                // Check if 'documents' property exists in the decoded object
                if (isset($driverDocs->documents) && is_array($driverDocs->documents)) {
                    $driver_registration_documents = $driverDocs->documents;

                    // Loop through each document
                    foreach ($driver_registration_documents as $key => $doc) {
                        // Check if $doc is an object and has 'name' property
                        if (is_object($doc) && isset($doc->name)) {
                            $name = str_replace(" ", "_", $doc->name);
                            $doc->slug = $name;
                        }
                    }
                }
            }

            $teams = @$driverDocs->all_teams??[];
            $tags = @$driverDocs->agent_tags??[];
            return view('frontend.driver-registration', compact('page_detail', 'navCategories', 'user', 'showTag', 'driver_registration_documents','client', 'teams', 'tags'));
        }
    }

    // public function index(Request $request, $domain='')
    // {
    //    // pr(Session::get('onDemandPricingSelected'));
    //     try {
    //         $home = array();
    //         $vendor_ids = array();
    //         if ($request->has('ref')) {
    //             session(['referrer' => $request->query('ref')]);
    //         }
    //         $additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency','is_long_term_service','is_admin_vendor_rating', 'is_service_product_price_from_dispatch','is_service_price_selection']);
    //         $latitude = Session::get('latitude') ?? null;
    //         $longitude = Session::get('longitude') ?? null;
    //         $curId = Session::get('customerCurrency');
    //         $langId = Session::get('customerLanguage');
    //         $client_config = Session::get('client_config');
    //         $selectedAddress = Session::get('selectedAddress');
    //         $client_preferences = $this->client_preferences;
    //         $_REQUEST['request_from'] = 1;

    //         $navCategories = $this->categoryNav($langId);
    //         Session::put('navCategories', $navCategories);
    //         $vendor_type = $request->has('type') ? $request->type : Session::get('vendorType');


    //         $count = 0;
    //         if ($client_preferences) {
    //             foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
    //                 $clientVendorTypes = $vendor_typ_key.'_check';
    //                 if($client_preferences->$clientVendorTypes == 1){
    //                     $count++;
    //                 }
    //             }

    //             if(empty($latitude) && empty($longitude)){
    //                 $latitude = $client_preferences->Default_latitude;
    //                 $longitude = $client_preferences->Default_longitude;
    //             }

    //         }
    //         if(count($navCategories) > 0 && ($vendor_type =='pick_drop') &&  ($count!=1) ){
    //             $categoriesSlug = $navCategories[0]->slug;
    //             return redirect()->route('categoryDetail',$categoriesSlug);
    //         }

    //         $carbon_now = Carbon::now();

    //         $banners = $this->getBannersForHomePage($client_preferences, 'banners', $latitude, $longitude);


    //         $mobile_banners = $this->getBannersForHomePage($client_preferences, 'mobile_banners', $latitude, $longitude);


    //         $home_page_labels = CabBookingLayout::where('is_active', 1)->web()->where('for_no_product_found_html',0)->orderBy('order_by');


    //         if (isset($langId) && !empty($langId))
    //             $home_page_labels = $home_page_labels->with(['translations' => function ($q) use ($langId) {
    //                 $q->where('language_id', $langId);
    //             }]);

    //         $home_page_labels = $home_page_labels->get();
    //         // if nothing in enblead for home page then show all

    //         //     $home_page_labels = HomePageLabel::with('translations')->where('is_active', 1)->orderBy('order_by')->get();
    //         $request->request->add(['type'=>Session::get('vendorType')??'delivery','noTinJson'=>1] );
    //         $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();

    //         $CabBookingLayout = CabBookingLayout::web()->where('is_active', 1);
    //         $home_page_pickup_labels   = clone $CabBookingLayout;
    //         $for_no_product_found_html = clone $CabBookingLayout;
    //         $enable_layout             = clone $CabBookingLayout;
    //         $enable_layout = $enable_layout->orderBy('order_by','asc')->pluck('slug')->toArray();
    //         $homePageData = $this->postHomePageData($request, $set_template, $enable_layout, $additionalPreference);

    //         $home_page_labels = $home_page_labels->map(function($da) use ($homePageData, $navCategories) {
    //             if($da->slug!='pickup_delivery' && $da->slug!='dynamic_page' ){
    //                 $da[$da->slug] = $homePageData[$da->slug] ?? '';
    //             }
    //             if( $da->slug == 'nav_categories'  ){
    //                 $da['nav_categories'] = $navCategories ?? '';
    //             }
    //             return $da;
    //         });

    //         $only_cab_booking = OnboardSetting::where('key_value', 'home_page_cab_booking')->count();
    //         if ($only_cab_booking == 1)
    //             return Redirect::route('categoryDetail', 'cabservice');



    //         $home_page_pickup_labels  = $home_page_pickup_labels->with('translations')->where('for_no_product_found_html',0)->orderBy('order_by')->get();



    //         $for_no_product_found_html = $for_no_product_found_html->with('translations')->where('for_no_product_found_html',1)->orderBy('order_by')->get();

    //         $categories = [];
    //         if(isset($set_template)  && ($set_template->template_id == 8 || $set_template->template_id == 9)){
    //             $categories = Category::with('translation_one')->select('id', 'icon', 'slug', 'type_id', 'is_visible', 'status', 'is_core', 'vendor_id', 'can_add_products', 'parent_id')
    //             ->where('id', '>', '1')

    //             ->whereNotIn('type_id', [4, 5])
    //             ->where(function ($q) {
    //                 $q->whereNull('vendor_id');
    //             })->orderBy('position', 'asc')
    //             ->orderBy('id', 'asc')
    //             ->where('status', 1)
    //             ->orderBy('parent_id', 'asc')->get();
    //         }



    //         $view_page ="home-template-one";
    //         if (isset($set_template)  && $set_template->template_id == 1){
    //             // $view_page = 'home-template-one';
    //             $view_page = 'home-template-test-one';
    //         }elseif(isset($set_template)  && $set_template->template_id == 2){
    //             // $view_page = "home-template-two";
    //             $view_page = 'home-template-test-two';
    //         }elseif(isset($set_template)  && $set_template->template_id == 3){
    //             // $view_page = "home-template-three";
    //             $view_page = 'home-template-test-three';
    //         }elseif(isset($set_template)  && $set_template->template_id == 4){
    //             // $view_page = "home-template-four";
    //             $view_page = 'home-template-test-four';
    //         }elseif(isset($set_template)  && $set_template->template_id == 5){
    //             $view_page = "home-template-five";
    //         }elseif(isset($set_template)  && $set_template->template_id == 6){
    //             // $view_page = "home-template-six";
    //             $view_page = "home-template-test-six";
    //         }
    //         elseif(isset($set_template)  && $set_template->template_id == 8){
    //             // $view_page = "home-template-six";
    //             $view_page = "home-template-test-eight";
    //         }
    //         elseif(isset($set_template)  && $set_template->template_id == 9){
    //             $view_page = "home-template-test-nine";
    //         }

    //         $is_service_product_price_from_dispatch_forOnDemand = 0;

    //         $getOnDemandPricingRule = getOnDemandPricingRule($vendor_type, Session::get('onDemandPricingSelected'),$additionalPreference);

    //         $is_service_product_price_from_dispatch_forOnDemand =$getOnDemandPricingRule['is_price_from_freelancer'];
    //         // if(($additionalPreference['is_service_product_price_from_dispatch'] == 1) && ( Session::get('vendorType') == 'on_demand')){
    //         //     $is_service_product_price_from_dispatch_forOnDemand =1;
    //         // }

    //         $homeData = ['categories' => $categories,'home' => $home,  'count' => $count, 'for_no_product_found_html' => $for_no_product_found_html,'homePagePickupLabels' => $home_page_pickup_labels, 'homePageLabels' => $home_page_labels, 'clientPreferences' => $client_preferences, 'banners' => $banners,'mobile_banners'=>$mobile_banners, 'navCategories' => $navCategories, 'selectedAddress' => $selectedAddress, 'latitude' => $latitude, 'longitude' => $longitude,'enable_layout'=>$enable_layout,'homePageData'=>$homePageData ,'is_service_product_price_from_dispatch_forOnDemand'=> $is_service_product_price_from_dispatch_forOnDemand];
    //         return view('frontend.'.$view_page)->with($homeData);

    //     } catch (Exception $e) {
    //         pr($e->getCode());
    //         die;
    //     }
    // }

    public function index(Request $request, $domain='')
    {
       // pr(Session::get('onDemandPricingSelected'));
        try {

            $startTime = microtime(true); // Start time in seconds with microseconds
            $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
            $client = Client::first();
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
            $vendor_type = Session::get('vendorType') ?? "delivery";


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


            if($client_preferences->is_hyperlocal == 1) {

                $this->loc_key = $this->loc_key.":hyperlocal:".$vendor_type.":".$client_preferences->client_code;
                $banners = $this->getBannersForHomePage($client_preferences, 'banners', $latitude, $longitude);
                $cacheKey = $this->loc_key.":{$latitude}:{$longitude}";

                $find_key = $this->isPointInRadius($latitude, $longitude, $this->radius, $this->loc_key);
                $mobile_banners = $this->getBannersForHomePage($client_preferences, 'mobile_banners', $latitude, $longitude);
            } else {
                $this->loc_key = $this->loc_key.':'.$vendor_type.':'.$client_preferences->client_code;
                $cacheKey = $this->loc_key;
                $cachedResult = Redis::get($this->loc_key);
                //$cachedResult['cacheKey'] = $cacheKey??'';
                if ($cachedResult) {
                    $find_key['data'] = json_decode($cachedResult);
                }
            }

            if ($this->additionalPreference['is_cache_enable_for_home'] == 1 && @$find_key['data']) {
                $homeData = $find_key['data'];
                // Logging the retrieved data

                echo $homeData;
                exit;
            } else {

                $carbon_now = Carbon::now();

                $banners = $this->getBannersForHomePage($client_preferences, 'banners', $latitude, $longitude);


                $mobile_banners = $this->getBannersForHomePage($client_preferences, 'mobile_banners', $latitude, $longitude);


                $home_page_labels = CabBookingLayout::where('is_active', 1)->web()->where('for_no_product_found_html',0)->orderBy('order_by');


                if (isset($langId) && !empty($langId))
                    $home_page_labels = $home_page_labels->with(['translations' => function ($q) use ($langId) {
                        $q->where('language_id', $langId);
                    }]);

                $home_page_labels = $home_page_labels->get();
                // if nothing in enblead for home page then show all

                //     $home_page_labels = HomePageLabel::with('translations')->where('is_active', 1)->orderBy('order_by')->get();
                $request->request->add(['type'=>Session::get('vendorType')??'delivery','noTinJson'=>1] );
                $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();

                $CabBookingLayout = CabBookingLayout::web()->where('is_active', 1);
                $home_page_pickup_labels   = clone $CabBookingLayout;
                $for_no_product_found_html = clone $CabBookingLayout;
                $enable_layout             = clone $CabBookingLayout;
                $enable_layout = $enable_layout->orderBy('order_by','asc')->pluck('slug')->toArray();
                $homePageData = $this->postHomePageData($request, $set_template, $enable_layout, $additionalPreference);

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

                    ->whereNotIn('type_id', [4, 5])
                    ->where(function ($q) {
                        $q->whereNull('vendor_id');
                    })->orderBy('position', 'asc')
                    ->orderBy('id', 'asc')
                    ->where('status', 1)
                    ->orderBy('parent_id', 'asc')->get();
                }



                $view_page ="home-template-one";
                if (isset($set_template)  && $set_template->template_id == 1){
                    // $view_page = 'home-template-one';
                    $view_page = 'home-template-test-one';
                }elseif(isset($set_template)  && $set_template->template_id == 2){
                    // $view_page = "home-template-two";
                    $view_page = 'home-template-test-two';
                }elseif(isset($set_template)  && $set_template->template_id == 3){
                    // $view_page = "home-template-three";
                    $view_page = 'home-template-test-three';
                }elseif(isset($set_template)  && $set_template->template_id == 4){
                    // $view_page = "home-template-four";
                    $view_page = 'home-template-test-four';
                }elseif(isset($set_template)  && $set_template->template_id == 5){
                    $view_page = "home-template-five";
                }elseif(isset($set_template)  && $set_template->template_id == 6){
                    // $view_page = "home-template-six";
                    $view_page = "home-template-test-six";
                }
                elseif(isset($set_template)  && $set_template->template_id == 8){
                    // $view_page = "home-template-six";
                    $view_page = "home-template-test-eight";
                }
                elseif(isset($set_template)  && $set_template->template_id == 9){

                    $view_page = "home-template-test-nine";
                }

                elseif(isset($set_template) && $set_template->template_id == 10){
                    $view_page = "yacht.index";
                }

                $is_service_product_price_from_dispatch_forOnDemand = 0;

                $getOnDemandPricingRule = getOnDemandPricingRule($vendor_type, Session::get('onDemandPricingSelected'),$additionalPreference);

                $is_service_product_price_from_dispatch_forOnDemand =$getOnDemandPricingRule['is_price_from_freelancer'];
                // if(($additionalPreference['is_service_product_price_from_dispatch'] == 1) && ( Session::get('vendorType') == 'on_demand')){
                //     $is_service_product_price_from_dispatch_forOnDemand =1;
                // }


                $homeData = ['categories' => $categories,'home' => $home,  'count' => $count, 'for_no_product_found_html' => $for_no_product_found_html,'homePagePickupLabels' => $home_page_pickup_labels, 'homePageLabels' => $home_page_labels, 'clientPreferences' => $client_preferences, 'banners' => $banners,'mobile_banners'=>$mobile_banners, 'navCategories' => $navCategories, 'selectedAddress' => $selectedAddress, 'latitude' => $latitude, 'longitude' => $longitude,'enable_layout'=>$enable_layout,'homePageData'=>$homePageData ,'is_service_product_price_from_dispatch_forOnDemand'=> $is_service_product_price_from_dispatch_forOnDemand,'vendor_type'=>$vendor_type];


                $locations = [
                    [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'key' => $cacheKey,
                        //'data' => json_encode($homeData)
                    ]
                ];

                $html = view('frontend.'.$view_page)->with($homeData)->render();
                if($client_preferences->is_hyperlocal == 1) {
                    $this->storeLocations($locations,$html,$this->loc_key);
                }else{
                    Redis::set($this->loc_key, json_encode($html));

                    Redis::expire($this->loc_key, $this->cache_minutes);
                }

            // Your code to be measured goes here

            $endTime = microtime(true); // End time in seconds with microseconds
            $executionTime = $endTime - $startTime; // Calculate execution time in seconds

            // \Log::info('Execution time homepage:'.$client->database_name.':' . $executionTime . ' seconds');

                return view('frontend.'.$view_page)->with($homeData);
            }

        } catch (Exception $e) {
            pr($e->getCode());
            die;
        }
    }
    /**
     * setHyperlocalAddress
     *
     * @param  mixed $lat
     * @param  mixed $long
     * @param  mixed $address
     * @return void
     */
    public function setHyperlocalAddress(Request $request)
    {
        $latitude        =  $request->latitude;
        $longitude       =  $request->longitude;
        $selectedAddress =  $request->address;
        $selectedPlaceId =  $request->place_id;
        //if ((!empty($latitude)) && (!empty($longitude)) && (!empty($selectedAddress))) {
            Session::put('latitude', $latitude);
            Session::put('longitude', $longitude);
            if($selectedAddress)
            Session::put('selectedAddress', $selectedAddress);
            if($selectedPlaceId)
            Session::put('selectedPlaceId', $selectedPlaceId);
       // }
        return redirect()->route('userHome');
    }
    public function setondemandPricingSession(Request $request)
    {
        $type  =  $request->type ?? 'vendor';
        Session::put('onDemandPricingSelected',  $type );
        ///pr(Session::get('onDemandPricingSelected'));
        return redirect()->route('userHome');
    }
    /**
     * postHomePageData
     *
     * @param  mixed $request
     * @return void
     */
    public function postHomePageData(Request $request,$set_template,$enable_layout)
    {
        $vendor_ids = $vendors = [];
        $new_products = [];
        $feature_products = [];
        $on_sale_products = [];
        $long_term_service_products = [];
        $recently_viewed = [];
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

        if( (empty($latitude)) && (empty($longitude)) ){
            $latitude = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_latitude) : 0;
            $longitude = (!empty($preferences->Default_latitude)) ? floatval($preferences->Default_longitude) : 0;
        }
        if($request->has('latitude') ){
            $latitude = $request->latitude;
            Session::put('latitude', $latitude);
        }
        if ($request->has('longitude')) {
            $longitude = $request->longitude;
            Session::put('longitude', $longitude);
        }
        $selectedAddress = ($request->has('selectedAddress')) ? Session::put('selectedAddress', $request->selectedAddress) : Session::get('selectedAddress');
        $selectedPlaceId = ($request->has('selectedPlaceId')) ? Session::put('selectedPlaceId', $request->selectedPlaceId) : Session::get('selectedPlaceId');
        $preferences = $this->client_preferences;
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
                case "vendors":
                    $vendors_title = $translation->title;
                    break;
                case "new_products":
                    $new_products_title = $translation->title;
                    break;
                case "on_sale":
                    $on_sale_title = $translation->title;
                    break;
                case "brands":
                    $brands_title = $translation->title;
                    break;
                case "best_sellers":
                    $best_sellers_title = $translation->title;
                    break;
                case "recent_orders":
                    $recent_orders_title = $translation->title;
                    break;
                case "banner":
                    $on_sale_title = $translation->title;
                    break;
                case "selected_products":
                    $selected_products_title = $translation->title;
                    break;
                case "trending":
                    $trending_vendors_title = $translation->title;
                    break;
                default:
                    break;
            }
        }
        $vendor_ids = $this->getRandomVendorIdsForHomePage($preferences, $request->type, $this->additionalPreference['is_admin_vendor_rating'], $latitude, $longitude);
        $home_page_labels = HomePageLabel::with('translations')->get();
        if (in_array('brands', $enable_layout)) {     # if enable brands section in
            $brands = $this->getBrandsForHomePage($language_id, $this->field_status);
        }else{
            $brands = [];
        }

        Session::forget('vendorType');
        Session::put('vendorType', $request->type);

        if ($preferences) {
            // check vendor Subscription0
            if ((empty($latitude)) || (empty($longitude)) || (empty($selectedAddress))) {
                $selectedAddress = $preferences->Default_location_name;
                $latitude = $preferences->Default_latitude??null;
                $longitude = $preferences->Default_longitude??null;
            } else {
                if ($preferences && ($latitude == $preferences->Default_latitude) && ($longitude == $preferences->Default_longitude)) {
                  $selectedAddress =  $preferences->Default_location_name;
                }

            }
            Session::put('latitude', $latitude);
            Session::put('longitude', $longitude);
            Session::put('selectedAddress', $selectedAddress);
        }

        if(count($vendor_ids) > 0){
            $vendors = $this->getVendorForHomePage($preferences, "random_or_admin_rating", $clientdata->timezone, $this->additionalPreference['is_admin_vendor_rating'], $request->type, $language_id, $latitude, $longitude, $vendor_ids);
        }
        $trendingVendors = [];
        if (in_array('trending_vendors', $enable_layout)) {  # if enable trending_vendors section in
            $now = Carbon::now()->toDateTimeString();
            $trending_vendors = SubscriptionInvoicesVendor::whereHas('features', function ($query) {
                $query->where(['subscription_invoice_features_vendor.feature_id' => 1]);
            })
            ->select('id', 'vendor_id', 'subscription_id')
            ->where('end_date', '>=', $now)
            ->pluck('vendor_id')->toArray();
            if(count($trending_vendors) > 0){
                $trendingVendors = $this->getVendorForHomePage($preferences, "trending_vendors", $clientdata->timezone, 0, $request->type, $language_id, $latitude, $longitude, $trending_vendors);
            }
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
        $on_sale_product_details =$on_sale_products = [];
        if (in_array('on_sale', $enable_layout)) {  # if enable new_products section in
            $on_sale_products = $on_sale_product_details = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'on_sale', $request->type,$on_sale_title, $p_dim);
        }
        $new_product_details =$new_products = [];
        if (in_array('new_products', $enable_layout)) {  # if enable new_products section in
            $new_products = $new_product_details = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'is_new', $request->type,$new_products_title,$p_dim);
        }
        $feature_product_details = $feature_products = [];

        if (in_array('featured_products', $enable_layout)) {  # if enable featured_products section in
            $feature_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'is_featured', $request->type, $featured_products_title,$p_dim);
        }

        if (in_array('banner', $enable_layout)) {  # if enable banner section in
            $cab_booking_layouts = CabBookingLayout::with('banner_image')->where('slug','banner')->get();
            // dd($cab_booking_layouts);
            foreach($cab_booking_layouts as $bkey => $bval){
                if(count($bval->banner_image) > 0){
                    $banners[$bval->banner_image[0]->cab_booking_layout_id] = $bval->banner_image[0]->banner_image_url;
                    $banners['url_'.$bval->banner_image[0]->cab_booking_layout_id] = $bval->banner_image[0]->banner_url;
                }
            }
        }

        $top_rated_products = '';
         //get long term service
        $long_term_service_products =[];
        if( in_array('long_term_service', $enable_layout) && @$this->additionalPreference['is_long_term_service'] == 1 && count($vendor_ids) > 0){ # if enable long_term_service section in
            $long_term_service_products = $this->longTermServiceProducts($vendor_ids, $this->additionalPreference, $language_id, $currency_id,'', $request->type,$p_dim);
        }
        if($this->checkTemplateForAction(8) || $this->checkTemplateForAction(1)){
            $recently_viewed = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'recent_viewed', $request->type, $featured_products_title,$p_dim);
            //$spot_light_products = $this->getSpotLight($preferences, $vendor_ids, $language_id, $currency_id, $p_dim); // get spotlight product i.e. max discounted products
            $spot_light_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'spotlight_deals', $request->type, $featured_products_title,$p_dim);
            // pr($spot_light_products);
            //$single_category_product_ids = $this->getSingleCategoryProducts(); // get single selected category's products
            $single_category_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'single_category_products', $request->type, $featured_products_title,$p_dim);
            // dd($single_category_products);
            //$selected_product_ids = $this->getSelectedProducts(); // get single selected category's products
            $selected_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'selected_products', $request->type, $featured_products_title,$p_dim);

            //$popular_product_ids = $this->getMostPopularProducts();  // get selected products to display
            $popular_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'popular_products', $request->type, $featured_products_title,$p_dim);

            //$top_rated_products_ids = $this->getTopRatedProducts();  // get selected products to display
            $top_rated_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'top_rated_products', $request->type, $featured_products_title,$p_dim);
            //pr($top_rated_products);
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
        /**  Get cities end */


        /** Respose data */

        $data = [
            'brands' => $brands,
            'vendors' => $vendors,
            'new_products' => $new_products,
            'homePageLabels' => $home_page_labels,
            'feature_products' => $feature_products,
            'on_sale_products' => $on_sale_products,
            'trending_vendors' => (!empty($trendingVendors) && count($trendingVendors) > 0)?$trendingVendors:$mostSellingVendors,
            'active_orders' => $activeOrders,
            'long_term_service' => $long_term_service_products,
            'additionalPreference' => $this->additionalPreference,

        ];

        if($request->has('noTinJson') && $request->noTinJson == 1){
            $data = [
                'brands' => $brands,
                'vendors' => $vendors,
                'new_products' => $new_products,
                'top_rated'       => $top_rated_products ?? '',
                'recently_viewed' => $recently_viewed,
                'homePageLabels' => $home_page_labels,
                'featured_products' => $feature_products,
                'on_sale' => $on_sale_products,
                'cities' => $this->cities,
                'long_term_service' => $long_term_service_products,
                'trending_vendors' => (!empty($trendingVendors) && count($trendingVendors) > 0)?$trendingVendors:[],
                'best_sellers'     => (!empty($mostSellingVendors) && count($mostSellingVendors) > 0)?$mostSellingVendors:[],
                'spotlight_deals'  => (!empty($spot_light_products) && count($spot_light_products) > 0)?$spot_light_products:[],
                'single_category_products'  => (!empty($single_category_products) && count($single_category_products) > 0)?$single_category_products:[],
                'selected_products'  => (!empty($selected_products) && count($selected_products) > 0)?$selected_products:[],
                'most_popular_products'  => (!empty($popular_products) && count($popular_products) > 0)?$popular_products:[],
                'recent_orders' => $activeOrders,
                'banners' => $banners,
                'additionalPreference' => $this->additionalPreference,
            ];
            //pr( $data);
            return $data ;
        }
        // if(count($dashboardProductsData)>0){
        //     $data =  array_merge($data,$dashboardProductsData);
        // }
        return $this->successResponse($data);
    }


    /**
     * getCities
     *
     * @param  mixed $language_id
     * @return $cities
     */
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

    // public function vendorProducts($venderIds, $langId, $currency = 'USD', $where = '', $type,$Products_title, $p_dim)
    // {

    //     $this->additionalPreference = getAdditionalPreference(['is_token_currency_enable']);
    //     // $products = $products->whereHas('vendor', function($q) use ($type,$venderIds){
    //     //             $q->where('status',1);
    //     //             $q->whereIn('id',$venderIds);
    //     //             $q->where($type, 1);
    //     //         });
    //     //         if ($where == 'is_featured') {
    //     //                  $products = $products->take(20);
    //     //             }else{
    //     //                 $products = $products->take(10);
    //     //             }
    //     //         $products = $products->inRandomOrder()->get();
    //     $vendorWhereIN = '';
    //     if(!empty($venderIds)){
    //         $venid = implode(',',$venderIds);
    //         $vendorWhereIN = 'AND `vendors`.`id` IN ('.$venid.')';

    //     }




    //     $raw_query = "SELECT
    //         `products`.`id`,
    //         `products`.`sku`,
    //         `products`.`url_slug`,
    //         `products`.`weight_unit`,
    //         `products`.`weight`,
    //         `products`.`vendor_id`,
    //         `products`.`has_variant`,
    //         `products`.`has_inventory`,
    //         `products`.`sell_when_out_of_stock`,
    //         `products`.`requires_shipping`,
    //         `products`.`Requires_last_mile`,
    //         `products`.`averageRating`,
    //         `products`.`inquiry_only`,
    //         `products`.`updated_at`,
    //         `products`.`is_featured`,
    //         `products`.`is_new`,
    //         `products`.`category_id`,
    //         -- `products`.`inwishlist` as `is_inwishlist_btn`,
    //         `categories`.`id` as `category_id` ,
    //         `categories`.`type_id`,
    //         `product_images`.`media_id`,
    //         `vendor_media`.`path`,
    //         `product_translation`.`title`,
    //         `product_translation`.`meta_title`,
    //         `product_translation`.`meta_keyword`,
    //         `product_translation`.`meta_description`,
    //         `product_translation`.`language_id`,
    //         `product_variant`.`compare_at_price` as `compare_price_numeric`,
    //         `product_variant`.`price` as `price_numeric`,
    //         `category_translation`.`name` as `category_name` ,
    //         `category_translation`.`meta_title` as `category_meta_title` ,
    //         `category_translation`.`meta_keywords` as `category_meta_keyword` ,
    //         `category_translation`.`meta_description` as `category_meta_description`,
    //         `vendors`.`name` as `vendor_name`,
    //         `vendors`.`slug` as `vendor_slug`,

    //         IFNULL(`products`.`is_long_term_service`, 0) AS `is_long_term_service`
    //         FROM
    //             `products` LEFT JOIN   `categories` as `categories` ON `products`.`category_id` = `categories`.`id`  AND `categories`.`type_id` != 7
    //              LEFT JOIN   `product_images` as `product_images` ON `product_images`.`product_id` = `products`.`id`
    //              LEFT JOIN   `vendors` as `vendors` ON `vendors`.`id` = `products`.`vendor_id` AND `vendors`.`status` = 1 $vendorWhereIN
    //              LEFT JOIN   `vendor_media` as `vendor_media` ON `vendor_media`.`id` = `product_images`.`media_id`
    //              LEFT JOIN   `product_translations` as `product_translation` ON `product_translation`.`product_id` = `products`.`id`
    //              LEFT JOIN   `product_variants` as `product_variant` ON `product_variant`.`product_id` = `products`.`id`
    //              LEFT JOIN   `category_translations` as `category_translation` ON `category_translation`.`category_id` = `products`.`category_id`

    //         WHERE
    //             `products`.`deleted_at` IS NULL
    //                 AND `vendors`.`status` = 1
    //                 AND `products`.`is_live` = 1

    //                 $vendorWhereIN -- replace with actual vendor IDs

    //                 GROUP BY `products`.`id`

    //                 ORDER BY
    //                     RAND()

    //                 LIMIT
    //                     10";

    //    $products = DB::select( DB::raw($raw_query));
    //    $returnArray = $products;
    //    return $returnArray;
    // }



    public function changePrimaryData(Request $request)
    {
        if ($request->has('type') && $request->type == 'language') {
            $clientLanguage = ClientLanguage::where('language_id', $request->value1)->first();
            if ($clientLanguage) {
                $lang_detail = Language::where('id', $request->value1)->first();
                App::setLocale($lang_detail->sort_code);
                session()->put('locale', $lang_detail->sort_code);
                Session::put('customerLanguage', $request->value1);
            }
        }
        if ($request->has('type') && $request->type == 'currency') {
            $clientCurrency = ClientCurrency::where('currency_id', $request->value1)->first();
            if ($clientCurrency) {
                $currency_detail = Currency::where('id', $request->value1)->first();
                Session::put('currencySymbol', $request->value2);
                Session::put('customerCurrency', $request->value1);
                Session::put('iso_code', $currency_detail->iso_code);
                Session::put('currencyMultiplier', $clientCurrency->doller_compare);
            }
        }
        $data['customerLanguage'] = Session::get('customerLanguage');
        $data['customerCurrency'] = Session::get('customerCurrency');
        $data['currencySymbol'] = Session::get('currencySymbol');
        return response()->json(['status' => 'success', 'message' => 'Saved Successfully!', 'data' => $data]);
    }

    public function changePaginate(Request $request)
    {
        $perPage = 12;
        if ($request->has('itemPerPage')) {
            $perPage = $request->itemPerPage;
        }
        Session::put('cus_paginate', $perPage);
        return response()->json(['status' => 'success', 'message' => 'Saved Successfully!', 'data' => $perPage]);
    }

    public function getClientPreferences(Request $request)
    {
        $clientPreferences = $this->client_preferences;
        if ($clientPreferences) {
            $dinein_check = $clientPreferences->dinein_check;
            $delivery_check = $clientPreferences->delivery_check;
            $takeaway_check = $clientPreferences->takeaway_check;
            $age_restriction = $clientPreferences->age_restriction;
            return response()->json(["age_restriction" => $age_restriction, "dinein_check" => $dinein_check, "delivery_check" => $delivery_check, "takeaway_check" => $takeaway_check]);
        }
    }


    /////    new home page
    public function indexTemplateOne(Request $request)
    {
        try {
            $home = array();
            $vendor_ids = array();
            if ($request->has('ref')) {
                session(['referrer' => $request->query('ref')]);
            }
            $latitude = Session::get('latitude');
            $longitude = Session::get('longitude');
            $curId = Session::get('customerCurrency');
            $preferences = Session::get('preferences');
            $langId = Session::get('customerLanguage');
            $client_config = Session::get('client_config');
            $selectedAddress = Session::get('selectedAddress');
            $navCategories = $this->categoryNav($langId);
            Session::put('navCategories', $navCategories);
            $clientPreferences = Session::get('preferences');
            $count = 0;
            if ($clientPreferences) {
                if ($clientPreferences->dinein_check == 1) {
                    $count++;
                }
                if ($clientPreferences->takeaway_check == 1) {
                    $count++;
                }
                if ($clientPreferences->delivery_check == 1) {
                    $count++;
                }
            }

            $banners = Banner::where('status', 1)->where('validity_on', 1)
                ->where(function ($q) {
                    $q->whereNull('start_date_time')->orWhere(function ($q2) {
                        $q2->whereDate('start_date_time', '<=', Carbon::now())
                            ->whereDate('end_date_time', '>=', Carbon::now());
                    });
                })->orderBy('sorting', 'asc')->with('category')->with('vendor')->get();
            $home_page_labels = HomePageLabel::with('translations')->where('is_active', 1)->orderBy('order_by')->get();

            $only_cab_booking = OnboardSetting::where('key_value', 'home_page_cab_booking')->count();
            if ($only_cab_booking == 1)
                return Redirect::route('categoryDetail', 'cabservice');
            $home_page_pickup_labels = CabBookingLayout::with(['translations' => function ($q) use ($langId) {
                $q->where('language_id', $langId);
            }])->where('is_active', 1)->orderBy('order_by')->where('for_no_product_found_html',0)->get();
            $for_no_product_found_html = CabBookingLayout::with('translations')->where('is_active', 1)->where('for_no_product_found_html',1)->orderBy('order_by')->get();

            return view('frontend.home-template-one')->with(['home' => $home, 'count' => $count, 'for_no_product_found_html' => $for_no_product_found_html,'homePagePickupLabels' => $home_page_pickup_labels, 'homePageLabels' => $home_page_labels, 'clientPreferences' => $clientPreferences, 'banners' => $banners, 'navCategories' => $navCategories, 'selectedAddress' => $selectedAddress, 'latitude' => $latitude, 'longitude' => $longitude]);
        } catch (Exception $e) {
            pr($e->getCode());
            die;
        }
    }

    # category menu

    public function homePageDataCategoryMenu(Request $request)
    {
        if ($request->has('latitude')) {
            $latitude = $request->latitude;
            Session::put('latitude', $latitude);
        } else {
            $latitude = Session::get('latitude');
        }
        if ($request->has('longitude')) {
            $longitude = $request->longitude;
            Session::put('longitude', $longitude);
        } else {
            $longitude = Session::get('longitude');
        }
        $selectedAddress = ($request->has('selectedAddress')) ? Session::put('selectedAddress', $request->selectedAddress) : Session::get('selectedAddress');
        $selectedPlaceId = ($request->has('selectedPlaceId')) ? Session::put('selectedPlaceId', $request->selectedPlaceId) : Session::get('selectedPlaceId');
        $preferences = Session::get('preferences');
        $currency_id = Session::get('customerCurrency');
        $language_id = Session::get('customerLanguage');

        $currency_id = $this->setCurrencyInSesion();


        Session::forget('vendorType');
        Session::put('vendorType', $request->type);

        $now = Carbon::now()->toDateTimeString();


        $navCategories = $this->categoryNav($language_id);
        Session::put('navCategories', $navCategories);

        $user = Auth::user();

        $data = [
           'navCategories' => $navCategories,
        ];
        return $this->successResponse($data);
    }


     #post Home Page Data Single


    public function postHomePageDataBanners(Request $request)
    {
        $preferences = Session::get('preferences');
        $latitude = $request->has('latitude') ? $request->get('latitude') : null;
        $longitude = $request->has('longitude') ? $request->get('longitude') : null;

        if(empty($latitude) && empty($longitude)){
            $latitude = $preferences->Default_latitude;
            $longitude = $preferences->Default_longitude;
        }

        // Start Web Banners
        $banners = Banner::with(['category', 'vendor'])->where('status', 1)->where('validity_on', 1)
        ->where(function ($q) {
            $q->whereNull('start_date_time')->orWhere(function ($q2) {
                $q2->whereDate('start_date_time', '<=', Carbon::now())
                    ->whereDate('end_date_time', '>=', Carbon::now());
            });
        });
        if(isset($preferences->is_service_area_for_banners) && ($preferences->is_service_area_for_banners == 1) && ($preferences->is_hyperlocal == 1)){
            if(!empty($latitude) && !empty($longitude)){
                $banners = $banners->whereHas('geos.serviceArea', function($query) use ($latitude, $longitude) {
                    $query->select('id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                });
            }
        }
        $banners = $banners->orderBy('sorting', 'asc')->get();
        // End Web Banners

        // Start Mobile Banners
        $mobile_banners = MobileBanner::with(['category','vendor'])->where('status', 1)->where('validity_on', 1)
        ->where(function ($q) {
            $q->whereNull('start_date_time')->orWhere(function ($q2) {
                $q2->whereDate('start_date_time', '<=', Carbon::now())
                    ->whereDate('end_date_time', '>=', Carbon::now());
            });
        });
        if(isset($preferences->is_service_area_for_banners) && ($preferences->is_service_area_for_banners == 1) && ($preferences->is_hyperlocal == 1)){
            if(!empty($latitude) && !empty($longitude)){
                $mobile_banners = $mobile_banners->whereHas('geos.serviceArea', function($query) use ($latitude, $longitude) {
                    $query->select('id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                });
            }
        }
        $mobile_banners = $mobile_banners->orderBy('sorting', 'asc')->get();
        // End Mobile Banners

        $data = [
            'banners' => $banners,
            'mobile_banners' => $mobile_banners
        ];

        return $this->successResponse($data);
    }

    public function confirmation(){
        return view('confirmatin');
    }

    public function setSessionIndex(Request $request, $domain='')
    {

        Session::forget('vendorType');
        Session::put('vendorType', $request->type);

        return response()->json(["status" => true]);
    }


    public function homePageSection()
    {
        $vendors = Vendor::where('status', 1)->select('id', 'name', 'slug');
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendors = $vendors->get();
        $taxCategory = TaxCategory::all();

        $p_categories = Category::with(['parent', 'translation_one'])
            ->whereIn('type_id', ['1', '3', '7', '8', '9'])
            ->where('id', '>', '1')
            ->where('deleted_at', NULL)
            ->where('status', 1)
            ->orderBy('parent_id', 'asc')
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('backend.tools.index')->with(['vendors' => $vendors, 'taxCategory' => $taxCategory, 'categories' => $p_categories]);
    }

    public function manifest()
    {
        $client = Client::first();
        // $logo = $client->logo['image_fit'].'72/72' . $client->logo['image_path'];
        $logo = $client->logo['image_fit'].'72/72' . str_replace('@webp','',$client->logo['image_path']);


        $manifest = [
            'name' => env('APP_NAME', 'royo'),
            'short_name' => 'ro2',
            'start_url' => '/',
            'background_color' => '#6777ef',
            'description' => 'Royo Orders',
            'display' => 'fullscreen',
            'theme_color' => '#6777ef',
            'icons' => [
                [
                    'src' => '',
                    'sizes' => '72x72',
                    'type' => 'image/webp',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        $jsonResponse  = json_encode($manifest);

        $jsonResponse = stripslashes($jsonResponse);

         return response($jsonResponse, 200)
             ->header('Content-Type', 'application/json');
    }
}
