<?php

namespace App\Http\Controllers\Api\v1\v2;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\{ServiceArea, Language, Currency};
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\HomePage\HomePageTrait;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Traits\{OrderTrait, ProductActionTrait, VendorTrait, RedisCacheTrait};
use App\Models\{Banner, Brand, CabBookingLayout, CabBookingLayoutTranslation, Category, Client, ClientPreference, Vendor, VendorCategory, Product, ClientCurrency, HomePageLabel, HomeProduct, MobileBanner, OnboardSetting, Order, ProductCategory, SubscriptionInvoicesVendor, UserVendor, VendorCities, VendorOrderStatus, WebStylingOption, UserAddress};
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client as GClient;

/**
 * HomeController
 */
class HomeController extends BaseController
{
    use ApiResponser, HomePageTrait, OrderTrait, ProductActionTrait, VendorTrait, RedisCacheTrait;
    public $cities = [];
    private $curLang = 0;
    private $field_status = 2;
    public $additionalPreference =[];
    public $client_preferences = [];
    public $venderFilterOpenClose = null;
    public $venderFilterbest = null;
    public $loc_key = 'geo_fence:locations:mobile';


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

    public function config()
    {
        $this->additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency','is_long_term_service','is_admin_vendor_rating', 'is_service_product_price_from_dispatch','is_service_price_selection','is_cache_enable_for_home','cache_reset_time_for_home','cache_radius_for_home']);
        $this->cache_minutes =  ($this->additionalPreference['cache_reset_time_for_home']!='') ? $this->additionalPreference['cache_reset_time_for_home'] :  $this->cache_minutes;
        $this->radius =  ($this->additionalPreference['cache_radius_for_home']!='') ? $this->additionalPreference['cache_radius_for_home'] :  $this->radius;
    }



    public function categoriesAll(Request $request, $domain='')
    {

        try {
            $user = Auth::user();
            $langId = $user->language;

            $latitude = Session::get('latitude') ?? null;
            $longitude = Session::get('longitude') ?? null;
            $type = $request->has('type') ? $request->type : 'delivery';

            if (empty($type))
            $type = 'delivery';

            $categoryTypes = getServiceTypesCategory($type);
            $preferences = Session::get('preferences');
            $vendorData = Vendor::whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                $q->whereIn('type_id',$categoryTypes);
            })->select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude','id as is_vendor_closed' ,'closed_store_order_scheduled')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($type, 1);




            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
                $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendorData = $vendorData->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->withAvg('product', 'averageRating');
                $ses_vendors = $this->getServiceAreaVendors($latitude, $longitude, $type);
                $vendorData = $vendorData->whereIn('id', $ses_vendors);
                //if($venderFilternear && ($venderFilternear == 1) ){
                    //->orderBy('vendorToUserDistance', 'ASC')
                    $vendorData =   $vendorData->orderBy('vendorToUserDistance', 'ASC');
                //}
            }


            $venderIds  = $vendorData->where('status', 1)->pluck('id');

            $navCategories = $this->categoryNav($langId, $venderIds, $type , $request);
            $homeData['navCategories'] = $navCategories;
            return $this->successResponse($homeData);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }

    }
    public function homepage(Request $request, $domain = '')
    {
        try {
            $this->config();
            $home = array();
            $vendor_ids = array();
            if ($request->has('ref')) {
                session(['referrer' => $request->query('ref')]);
            }
            $latitude = Session::get('latitude') ?? $request->latitude;
            $longitude = Session::get('longitude') ?? $request->longitude;
            $curId = Session::get('customerCurrency');
            $preferences = Session::get('preferences');
            $user = Auth::user();
            $langId = $user->language;
            $currency_id = $user->currency;
            $client_config = Session::get('client_config');
            $selectedAddress = Session::get('selectedAddress');
            $_REQUEST['request_from'] = 1;

            $type = $request->has('type') ? $request->type : 'delivery';
              Session::put('vendorType',$type);

            //   print_r($type);exit;

            $categoryTypes = getServiceTypesCategory($type);

            $this->venderFilterOpenClose   = isset($request->open_close_vendor) ? $request->open_close_vendor : null;
            $this->venderFilterbest   = $request->has('best_vendor') && $request->best_vendor ? $request->best_vendor : null;
            $clientPreferences = ClientPreference::first();


            $count = 0;
            if ($clientPreferences) {
                foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
                    $clientVendorTypes = $vendor_typ_key . '_check';
                    if ($clientPreferences->$clientVendorTypes == 1) {
                        $count++;
                    }
                }

                if (empty($latitude) && empty($longitude)) {
                    $latitude = $clientPreferences->Default_latitude;
                    $longitude = $clientPreferences->Default_longitude;
                }
            }

            $clientPreferences = ClientPreference::first();

            $count = 0;
            if ($clientPreferences) {
                foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
                    $clientVendorTypes = $vendor_typ_key . '_check';
                    if ($clientPreferences->$clientVendorTypes == 1) {
                        $count++;
                    }
                }

                if (empty($latitude) && empty($longitude)) {
                    $latitude = $clientPreferences->Default_latitude;
                    $longitude = $clientPreferences->Default_longitude;
                }
            }

            if($clientPreferences->is_hyperlocal == 1) {

                $this->loc_key = $this->loc_key.":hyperlocal:".$type.":".$clientPreferences->client_code;
                $cacheKey = $this->loc_key.":{$latitude}:{$longitude}";

                $find_key = $this->isPointInRadius($latitude, $longitude, $this->radius, $this->loc_key);


            } else {
                $this->loc_key = $this->loc_key.':'.$type.':'.$clientPreferences->client_code;
                $cacheKey = $this->loc_key;
                $cachedResult = Redis::get($this->loc_key);
                //$cachedResult['cacheKey'] = $cacheKey??'';
                if ($cachedResult) {
                    $find_key['data'] = json_decode($cachedResult);
                }

            }

            if ($this->additionalPreference['is_cache_enable_for_home'] == 1 && @$find_key['data']) {
                $homeData = $find_key['data'];
            } else {


            $mobile_banners = MobileBanner::with(['category','category.type', 'vendor'])->where('status', 1)->where('validity_on', 1)
                ->where(function ($q) {
                    $q->whereNull('start_date_time')->orWhere(function ($q2) {
                        $q2->whereDate('start_date_time', '<=', Carbon::now())
                            ->whereDate('end_date_time', '>=', Carbon::now());
                    });
                });
            if (isset($clientPreferences->is_service_area_for_banners) && ($clientPreferences->is_service_area_for_banners == 1) && ($clientPreferences->is_hyperlocal == 1)) {
                if (!empty($latitude) && !empty($longitude)) {
                    $mobile_banners = $mobile_banners->whereHas('geos.serviceArea', function ($query) use ($latitude, $longitude) {
                        $query->select('id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                    });
                }
            }
            $mobile_banners = $mobile_banners->orderBy('sorting', 'asc')->get();
            if (count($mobile_banners)) {
                foreach ($mobile_banners as $key => $value) {
                    $bannerLink = '';
                    $is_show_category = null;
                    $vendor_name = null;
                    if (!empty($value->link) && $value->link == 'category') {
                        $bannerLink = $value->redirect_category_id;
                        if ($bannerLink) {
                            $categoryData = Category::where('status', 1)->where('id', $value->redirect_category_id)->with('translation_one')->first();
                            $value->redirect_name = (($categoryData) && ($categoryData->translation_one)) ? $categoryData->translation_one->name : '';
                        }
                    }
                    if (!empty($value->link) && $value->link == 'vendor') {
                        $bannerLink = $value->redirect_vendor_id;
                        if ($bannerLink) {
                            $vendorDataSingle = Vendor::select('id', 'slug', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'latitude', 'longitude')->where('status', 1)->where('id', $value->redirect_vendor_id)->first();
                            if ($vendorDataSingle) {
                                $vendorDataSingle->is_show_category = ($vendorDataSingle->vendor_templete_id == 2 || $vendorDataSingle->vendor_templete_id == 4) ? 1 : 0;
                            }
                            $is_show_category = (($vendorDataSingle) && ($vendorDataSingle->vendor_templete_id == 1)) ? 0 : 1;
                            $value->is_show_category = $is_show_category;
                            $value->redirect_name = $vendorDataSingle->name ?? '';
                            $value->vendor = $vendorDataSingle;
                        }
                    }
                    $value->redirect_to = ucwords($value->link);
                    $value->redirect_id = $bannerLink;
                    unset($value->redirect_category_id);
                    unset($value->redirect_vendor_id);
                }
            }

            $home_page_labels = CabBookingLayout::where('is_active', 1)->app()->where('for_no_product_found_html', 0)->orderBy('order_by');



            if (isset($langId) && !empty($langId))
                $home_page_labels = $home_page_labels->with(['translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }]);


            $home_page_labels = $home_page_labels->get();


            if (count($home_page_labels) == 0)
                $home_page_labels = HomePageLabel::with('translations')->where('is_active', 1)->orderBy('order_by')->get();
            $request->request->add(['noTinJson' => 1]);
            /***start new  */

            $additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency','is_long_term_service','is_admin_vendor_rating', 'is_service_product_price_from_dispatch','is_freelance_on_homepage']);

            $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();
            $enable_layout = CabBookingLayout::where('is_active', 1)->app();

                $enable_layout = $enable_layout->orderBy('order_by', 'asc')->pluck('slug')->toArray();

            if($request->action=='2'){
                $homePageData = $this->postHomePageDataV2($request, $set_template, $enable_layout, $additionalPreference,$user);
            } else {
                $homePageData = $this->postHomePageData($request,$additionalPreference);
            }

            if($type == 'p2p')
            {

            $vendorData = Vendor::whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                $q->whereIn('type_id',$categoryTypes);
            })->select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude','id as is_vendor_closed' ,'closed_store_order_scheduled','delivery_fee_minimum','delivery_fee_maximum')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($type, 1);

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {

                $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
                $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendorData = $vendorData->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->withAvg('product', 'averageRating');

                $ses_vendors = $this->getServiceAreaVendors($latitude, $longitude, $type);
                $Service_area = $this->getServiceArea($latitude, $longitude, $type);

                $vendorData = $vendorData->whereIn('id', $ses_vendors);
                    $vendorData =   $vendorData->orderBy('vendorToUserDistance', 'ASC');
            }

            $venderIds  = $vendorData->where('status', 1)->pluck('id');
            $navCategories = $this->categoryNav($langId, $venderIds, $type , $request);
            } else{
                $navCategories = $this->categoryNav($langId, @$homePageData['vendor_ids'], $type);
            }

            Session::put('navCategories', $navCategories);
            /***end new  */
            $home_page_labels = $home_page_labels->map(function ($da) use ($homePageData, $navCategories,$mobile_banners) {
                if ($da->slug != 'pickup_delivery' && $da->slug != 'dynamic_page' && $da->slug != 'nav_categories' && $da->slug != 'banner') {

                    $da['data'] = @$homePageData[@$da->slug];
                }
                if ($da->slug == 'banner') {
                    $da['banner_image'] = $mobile_banners;
                }
                if ($da->slug == 'nav_categories') {
                    $da['data'] = $navCategories;
                }
                return $da;
            });

            $user = Auth::user();
            $s3_url = '';
            if(\Config::get('filesystems.disks.s3.driver') == 's3') {
                $s3_url = 'https://'.\Config::get('filesystems.disks.s3.driver').'.'.\Config::get('filesystems.disks.s3.region').'.amazonaws.com'.'/'.\Config::get('filesystems.disks.s3.bucket').'/';
            }
            $image_const_arr = [
                'image_fit' => \Config::get('app.FIT_URL'),
                'image_fill' => \Config::get('app.FILL_URL'),
                's3_url' => $s3_url,
                'proxy_url'=> \Config::get('app.IMG_URL2')
            ];


            $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
            $homeData = ['homePageLabels' => $home_page_labels, 'reqData' => $request->all(), 'selectedAddress' => $selectedAddress, 'latitude' => $latitude, 'longitude' => $longitude, 'enable_layout' => $enable_layout,'image_prefix' => $image_const_arr];
            $homeData['is_admin'] = $user_vendor_count > 0 ? 1 : 0;
            $homeData['mobile_banners'] = $mobile_banners??[];
            //$homeData['banners'] = $banners??[];
            $homeData['banner_image'] = $banners??[];
            //$homeData['categories'] = $categories;
            $homeData['cacheKey'] = $cacheKey??'';
            $locations = [
                [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'key' => $cacheKey,
                    //'data' => json_encode($homeData)
                ]
            ];

             $get_preference = $this->checkIfLastMileOn();
            if(!empty($get_preference) && @$additionalPreference['is_freelance_on_homepage']==1){
                $homeData['freelance_details'] = $this->getAllAgentDetailFromDispatcher($get_preference);
            }
            //pr($cacheKey);
            // return $this->successResponse($homeData);

            if($clientPreferences->is_hyperlocal == 1) {
                $this->storeLocations($locations,$homeData,$this->loc_key);

            } else {
                Redis::set($this->loc_key, json_encode($homeData));
                Redis::expire($this->loc_key, $this->cache_minutes);
            }
        }
            $distance_unit=$clientPreferences->distance_unit_for_time;
            $homeData['distance_unit'] = $distance_unit;
            return $this->successResponse($homeData);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getSubcategoryVendor(Request $request, $domain = '')
    {
        try {
            $home = array();
            $vendor_ids = array();
            if ($request->has('ref')) {
                session(['referrer' => $request->query('ref')]);
            }
            $latitude = Session::get('latitude') ?? null;
            $longitude = Session::get('longitude') ?? null;
            $curId = Session::get('customerCurrency');
            $preferences = Session::get('preferences');
            $user = Auth::user();
            $langId = $user->language;
            $currency_id = $user->currency;
            $client_config = Session::get('client_config');
            $selectedAddress = Session::get('selectedAddress');
            $_REQUEST['request_from'] = 1;

            $type = $request->has('type') ? $request->type : 'delivery';

            if (empty($type))
                $type = 'delivery';

            $categoryTypes = getServiceTypesCategory($type);

            $clientPreferences = ClientPreference::first();
            $vendor_type = $request->has('type') ? $request->type : Session::get('vendorType');


            $count = 0;
            if ($clientPreferences) {
                foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
                    $clientVendorTypes = $vendor_typ_key . '_check';
                    if ($clientPreferences->$clientVendorTypes == 1) {
                        $count++;
                    }
                }

                if (empty($latitude) && empty($longitude)) {
                    $latitude = $clientPreferences->Default_latitude;
                    $longitude = $clientPreferences->Default_longitude;
                }
            }

            $cid = $request->category_id;
            $getSubCatIds = Category::where('parent_id', $cid)->pluck('id')->toArray();

            $mobile_banners = MobileBanner::with(['category', 'vendor'])->where('status', 1)->where('validity_on', 1)
                ->where(function ($q) {
                    $q->whereNull('start_date_time')->orWhere(function ($q2) {
                        $q2->whereDate('start_date_time', '<=', Carbon::now())
                            ->whereDate('end_date_time', '>=', Carbon::now());
                    });
                });
            if (isset($clientPreferences->is_service_area_for_banners) && ($clientPreferences->is_service_area_for_banners == 1) && ($clientPreferences->is_hyperlocal == 1)) {
                if (!empty($latitude) && !empty($longitude)) {
                    $mobile_banners = $mobile_banners->whereHas('geos.serviceArea', function ($query) use ($latitude, $longitude) {
                        $query->select('id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                    });
                }
            }
            $mobile_banners = $mobile_banners->whereIn('redirect_category_id', $getSubCatIds)->orderBy('sorting', 'asc')->get();


            $home_page_labels = CabBookingLayout::where('is_active', 1)->app()->where('for_no_product_found_html', 0)->orderBy('order_by');



            if (isset($langId) && !empty($langId))
                $home_page_labels = $home_page_labels->with(['banner_image', 'translations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }]);


            $home_page_labels = $home_page_labels->get();


            if (count($home_page_labels) == 0)
                $home_page_labels = HomePageLabel::with('translations')->where('is_active', 1)->orderBy('order_by')->get();
            $request->request->add(['type' => Session::get('vendorType') ?? 'delivery', 'noTinJson' => 1]);
            /***start new  */

            $additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency','is_long_term_service','is_admin_vendor_rating', 'is_service_product_price_from_dispatch']);

            $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();
            $enable_layout = CabBookingLayout::where('is_active', 1)->app();

            $enable_layout = $enable_layout->orderBy('order_by', 'asc')->pluck('slug')->toArray();
            //$homePageData = $this->postHomePageData($request);

            $homePageData = $this->postHomePageDataV2($request, $set_template, $enable_layout, $additionalPreference,$user, $getSubCatIds);

            $navCategories = $this->subCategoryNav($langId, @$homePageData['vendor_ids'], $type, $cid);
            Session::put('navCategories', $navCategories);

            /***end new  */

            // dd($navCategories);
            $home_page_labels = $home_page_labels->map(function ($da) use ($homePageData, $navCategories) {
                if ($da->slug != 'pickup_delivery' && $da->slug != 'dynamic_page' && $da->slug != 'nav_categories' && $da->slug != 'banner') {

                    $da['data'] = @$homePageData[@$da->slug];
                }
                if ($da->slug == 'nav_categories') {
                    // dd($da->slug);
                    $da['data'] = $navCategories;
                    // dd($da[$da->slug]);
                }

                return $da;
            });

            $user = Auth::user();
            $s3_url = '';
            if(\Config::get('filesystems.disks.s3.driver') == 's3') {
                $s3_url = 'https://'.\Config::get('filesystems.disks.s3.driver').'.'.\Config::get('filesystems.disks.s3.region').'.amazonaws.com'.'/'.\Config::get('filesystems.disks.s3.bucket').'/';
            }
            $image_const_arr = [
                'image_fit' => \Config::get('app.FIT_URL'),
                'image_fill' => \Config::get('app.FILL_URL'),
                's3_url' => $s3_url,
                'proxy_url'=> \Config::get('app.IMG_URL2')
            ];

            $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
            $homeData = ['homePageLabels' => $home_page_labels, 'reqData' => $request->all(), 'selectedAddress' => $selectedAddress, 'latitude' => $latitude, 'longitude' => $longitude, 'enable_layout' => $enable_layout,'image_prefix' => $image_const_arr];
            $homeData['is_admin'] = $user_vendor_count > 0 ? 1 : 0;
            $homeData['mobile_banners'] = $mobile_banners??[];
            //$homeData['banners'] = $banners??[];
            $homeData['banner_image'] = $banners??[];
            //$homeData['categories'] = $categories;
            return $this->successResponse($homeData);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /** return dashboard content like categories, vendors, brands, products     */
    /**
     * homepage
     *
     * @param  mixed $request
     * @return void
     */
    public function postHomePageData(Request $request,$additionalPreference=null)
    {
        $vendor_ids = [];
        $new_products = [];
        $feature_products = [];
        $on_sale_products = [];
        $recently_viewed = [];
        $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();
        $p_dim = '260/100';
        if (isset($set_template)  && $set_template->template_id == 3) {
            $p_dim = '300/350';
        } elseif (isset($set_template)  && $set_template->template_id == 2) {
            $p_dim = '260/180';
        }
        $latitude = Session::get('latitude');
        $longitude = Session::get('longitude');

        //pr($latitude);
        if ($request->has('latitude')) {
            $latitude = $request->latitude;
            Session::put('latitude', $latitude);
        }
        if ($request->has('longitude')) {
            $longitude = $request->longitude;
            Session::put('longitude', $longitude);
        }
        $selectedAddress = ($request->has('selectedAddress')) ? Session::put('selectedAddress', $request->selectedAddress) : Session::get('selectedAddress');
        $selectedPlaceId = ($request->has('selectedPlaceId')) ? Session::put('selectedPlaceId', $request->selectedPlaceId) : Session::get('selectedPlaceId');
        $preferences = !empty(Session::get('preferences')) ? (object)Session::get('preferences') : ClientPreference::first();
        $currency_id = Session::get('customerCurrency');
        $language_id = Session::get('customerLanguage');
        if (is_null($language_id)) {
            $local = ($request->hasHeader('language')) ? $request->header('language') : 1;

            $language_id = $local;
        }

        $currency_id = $this->setCurrencyInSesion();

        $featured_products_title = CabBookingLayoutTranslation::where('language_id', $language_id)->whereHas('layout', function ($q) {
            $q->where('slug', 'featured_products');
        })->value('title');

        $vendors_title = CabBookingLayoutTranslation::where('language_id', $language_id)->whereHas('layout', function ($q) {
            $q->where('slug', 'vendors');
        })->value('title');

        $new_products_title = CabBookingLayoutTranslation::where('language_id', $language_id)->whereHas('layout', function ($q) {
            $q->where('slug', 'new_products');
        })->value('title');

        $on_sale_title = CabBookingLayoutTranslation::where('language_id', $language_id)->whereHas('layout', function ($q) {
            $q->where('slug', 'on_sale');
        })->value('title');

        $brands_title = CabBookingLayoutTranslation::where('language_id', $language_id)->whereHas('layout', function ($q) {
            $q->where('slug', 'brands');
        })->value('title');

        $best_sellers_title = CabBookingLayoutTranslation::where('language_id', $language_id)->whereHas('layout', function ($q) {
            $q->where('slug', 'best_sellers');
        })->value('title');

        $trending_vendors_title = CabBookingLayoutTranslation::where('language_id', $language_id)->whereHas('layout', function ($q) {
            $q->where('slug', 'trending');
        })->value('title');

        $recent_orders_title = CabBookingLayoutTranslation::where('language_id', $language_id)->whereHas('layout', function ($q) {
            $q->where('slug', 'recent_orders');
        })->value('title');

        $enable_layout = CabBookingLayout::where('is_active', 1)->app();
        $enable_layout = $enable_layout->pluck('slug')->toArray();
        $home_page_labels = HomePageLabel::with('translations')->get();
        if (in_array('brands', $enable_layout)) {     # if enable brands section in
            $brands = Brand::select('id', 'image', 'title')->with(['translation' => function ($q) use ($language_id) {
                $q->where('language_id', $language_id);
            }])->where('status', '!=', $this->field_status)->orderBy('position', 'asc')->get();
            foreach ($brands as $brand) {
                $brand->redirect_url = route('brandDetail', $brand->id);
                $brand->translation_title = $brand->translation->first() ? $brand->translation->first()->title : $brand->title;
            }
        } else {
            $brands = [];
        }

        Session::forget('vendorType');
        Session::put('vendorType', $request->type);
        $vendors = Vendor::with('products')->with('slot.day', 'slotDate')->select('id', 'name', 'banner', 'address', 'order_pre_time', 'order_min_amount', 'logo', 'slug', 'latitude', 'longitude', 'show_slot');
        if ($preferences) {
            if ((empty($latitude)) && (empty($longitude)) && (empty($selectedAddress))) {
                $selectedAddress = $preferences->Default_location_name;
                $latitude = $preferences->Default_latitude ?? null;
                $longitude = $preferences->Default_longitude ?? null;
                Session::put('latitude', $latitude);
                Session::put('longitude', $longitude);
                Session::put('selectedAddress', $selectedAddress);
            } else {
                if ($preferences && ($latitude == $preferences->Default_latitude) && ($longitude == $preferences->Default_longitude)) {
                    Session::put('selectedAddress', $preferences->Default_location_name);
                }
            }


            if (($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {

                if (!empty($latitude) && !empty($longitude)) {
                    $vendors = $vendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                        $query->select('vendor_id')
                            ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                    });
                }
            }
        }

        /**
         * put a limit to get vendors.
         */
        $long_term_vendors = $vendors->pluck('id')->toArray();
        if(isset($additionalPreference['is_admin_vendor_rating']) && ($additionalPreference['is_admin_vendor_rating'] == 1)){
            $vendors = $vendors->orderBy('admin_rating', 'DESC');
        }else{
            $vendors = $vendors->inRandomOrder();
        }
        $vendors = $vendors->where('status', 1)->where($request->type, 1)
            ->limit(10)->get();
        foreach ($vendors as $key => $value) {
            $vendor_ids[] = $value->id;
            // $value->vendorRating = $this->vendorRating($value->products);

            // get or update rating
            $value->vendorRating = $this->getVendorRating($value->id);

            // $value->name = Str::limit($value->name, 15, '..');
            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $value = $this->getVendorDistanceWithTime($latitude, $longitude, $value, $preferences);
            }
            $vendorCategories = VendorCategory::with('category.translation_one')->where('vendor_id', $value->id)->where('status', 1)->get();
            $categoriesList = '';
            foreach ($vendorCategories as $key => $category) {
                if ($category->category) {
                    $categoriesList = $categoriesList . @$category->category->translation_one->name ?? '';
                    if ($key !=  $vendorCategories->count() - 1) {
                        $categoriesList = $categoriesList . ', ';
                    }
                }
            }
            $value->categoriesList = $categoriesList;
            $value->type_title = $categoriesList;

            $value->is_vendor_closed = 0;
            if ($value->show_slot == 0) {
                if (($value->slotDate->isEmpty()) && ($value->slot->isEmpty())) {
                    $value->is_vendor_closed = 1;
                } else {
                    $value->is_vendor_closed = 0;
                    if ($value->slotDate->isNotEmpty()) {
                        if ($value->slotDate->first()->start_time != '' && $value->slotDate->first()->end_time != '') {
                            $value->opening_time  = date('g:i A', strtotime($value->slotDate->first()->start_time));
                            $value->closing_time = date('g:i A', strtotime($value->slotDate->first()->end_time));
                        }
                    } elseif ($value->slot->isNotEmpty()) {

                        if ($value->slot->first()->start_time && $value->slot->first()->end_time) {
                            $value->opening_time = date('g:i A', strtotime($value->slot->first()->start_time));
                            $value->closing_time = date('g:i A', strtotime($value->slot->first()->end_time));
                        }
                    }
                }
            }
        }
        if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            $vendors = $vendors->sortBy('lineOfSightDistance')->values()->all();
        }
        $now = Carbon::now()->toDateTimeString();
        $subscribed_vendors_for_trending = SubscriptionInvoicesVendor::with('features')->whereHas('features', function ($query) {
            $query->where(['subscription_invoice_features_vendor.feature_id' => 1]);
        })
            ->select('id', 'vendor_id', 'subscription_id')
            ->where('end_date', '>=', $now)
            ->pluck('vendor_id')->toArray();

        if (($latitude) && ($longitude)) {

            Session::put('vendors', $vendor_ids);
        }

        $trendingVendors = Vendor::with('slot.day', 'slotDate')->whereIn('id', $subscribed_vendors_for_trending)->where('status', 1)->inRandomOrder();

        // add hyperlocal check to get vendors
        if (($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {

            if (!empty($latitude) && !empty($longitude)) {
                $trendingVendors = $trendingVendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                    $query->select('vendor_id')
                        ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                });
            }
        }

        $trendingVendors = $trendingVendors->get();

        if ((!empty($trendingVendors) && count($trendingVendors) > 0)) {
            foreach ($trendingVendors as $key => $value) {
                $value->tag_title = $trending_vendors_title ?? '0';
                $value->vendorRating = $this->vendorRatings($value->products);
                // $value->name = Str::limit($value->name, 15, '..');
                if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                    $value = $this->getVendorDistanceWithTime($latitude, $longitude, $value, $preferences);
                }
                $vendorCategories = VendorCategory::with('category.translation_one')->where('vendor_id', $value->id)->where('status', 1)->get();
                $categoriesList = '';
                foreach ($vendorCategories as $key => $category) {
                    if ($category->category) {
                        $categoriesList = $categoriesList . @$category->category->translation_one->name;
                        if ($key !=  $vendorCategories->count() - 1) {
                            $categoriesList = $categoriesList . ', ';
                        }
                    }
                }
                $value->categoriesList = $categoriesList;
                $value->is_vendor_closed = 0;
                if ($value->show_slot == 0) {
                    if (($value->slotDate->isEmpty()) && ($value->slot->isEmpty())) {
                        $value->is_vendor_closed = 1;
                    } else {
                        $value->is_vendor_closed = 0;
                        if ($value->slotDate->isNotEmpty()) {
                            $value->opening_time = Carbon::parse($value->slotDate->first()->start_time)->format('g:i A');
                            $value->closing_time = Carbon::parse($value->slotDate->first()->end_time)->format('g:i A');
                        } elseif ($value->slot->isNotEmpty()) {
                            $value->opening_time = Carbon::parse($value->slot->first()->start_time)->format('g:i A');
                            $value->closing_time = Carbon::parse($value->slot->first()->end_time)->format('g:i A');
                        }
                    }
                }
            }
        }
        if (($preferences) && ($preferences->is_hyperlocal == 1)) {
            $trendingVendors = $trendingVendors->sortBy('lineOfSightDistance')->values()->all();
        }

        //get Most Selling Vendors
        $mostSellingVendors = $this->getMostSellingVendors($preferences, $vendor_ids);

        $on_sale_product_details = $this->vendorProducts_v2($vendor_ids, $language_id, 'USD', 'on_sale', $request->type);
        $new_product_details = $this->vendorProducts_v2($vendor_ids, $language_id, $currency_id, 'is_new', $request->type);
        $feature_product_details = $this->vendorProducts_v2($vendor_ids, $language_id, $currency_id, 'is_featured', $request->type);

        foreach ($new_product_details as  $new_product_detail) {
            $multiply = $new_product_detail->variant->first()->multiplier ?? 1;
            $title = $new_product_detail->translation->first() ? $new_product_detail->translation->first()->title : $new_product_detail->sku;
            $image_url = $new_product_detail->media->first() ? $new_product_detail->media->first()->image->path['proxy_url'] . $p_dim . $new_product_detail->media->first()->image->path['image_path'] : $this->loadDefaultImage();
            $is_p2p = 0;
            if (@$new_product_detail->category->categoryDetail->type_id && @$new_product_detail->category->categoryDetail->type_id == 13) {
                $is_p2p = 1;
            }
            $new_products[] = array(
                'id' => $new_product_detail->id,
                'tag_title' => $new_products_title ?? 0,
                'image_url' => $image_url,
                'media' => $new_product_detail->media,
                'variant' => $new_product_detail->variant,
                'sku' => $new_product_detail->sku,
                'title' => Str::limit($title, 18, '..'),
                'url_slug' => $new_product_detail->url_slug,
                'averageRating' => number_format($new_product_detail->averageRating, 1, '.', ''),
                'inquiry_only' => $new_product_detail->inquiry_only,
                'vendor_name' => $new_product_detail->vendor ? $new_product_detail->vendor->name : '',
                'vendor' => $new_product_detail->vendor,
                'price' => Session::get('currencySymbol') . ' ' . (decimal_format(@$new_product_detail->variant->first()->price ?? 0 * $multiply, ',')),
                'category' => (@$new_product_detail->category->categoryDetail->translation) ? @$new_product_detail->category->categoryDetail->translation->first()->name : @$new_product_detail->category->categoryDetail->slug,
                'translation' => $new_product_detail->translation,
                'is_p2p' => $is_p2p
            );
        }
        foreach ($feature_product_details as  $feature_product_detail) {
            $multiply = $feature_product_detail->variant->first()->multiplier ?? 1;
            $title = $feature_product_detail->translation->first() ? $feature_product_detail->translation->first()->title : $feature_product_detail->sku;
            $image_url = $feature_product_detail->media->first() ? $feature_product_detail->media->first()->image->path['proxy_url'] . $p_dim . $feature_product_detail->media->first()->image->path['image_path'] : $this->loadDefaultImage();
            $is_p2p = 0;
            if (@$feature_product_detail->category->categoryDetail->type_id && @$feature_product_detail->category->categoryDetail->type_id == 13) {
                $is_p2p = 1;
            }
            $feature_products[] = array(
                'id' => $feature_product_detail->id,
                'tag_title' => $featured_products_title ?? '0',
                'image_url' => $image_url,
                'media' => $feature_product_detail->media,
                'variant' => $feature_product_detail->variant,
                'sku' => $feature_product_detail->sku,
                'title' => Str::limit($title, 18, '..'),
                'url_slug' => $feature_product_detail->url_slug,
                'averageRating' => number_format($feature_product_detail->averageRating, 1, '.', ''),
                'inquiry_only' => $feature_product_detail->inquiry_only,
                'vendor_name' => $feature_product_detail->vendor ? $feature_product_detail->vendor->name : '',
                'vendor' => $feature_product_detail->vendor,
                'price' => Session::get('currencySymbol') . ' ' . (decimal_format(@$feature_product_detail->variant->first()->price * $multiply, ',')),
                'category' => (@$feature_product_detail->category->categoryDetail->translation) ? @$feature_product_detail->category->categoryDetail->translation->first()->name : @$feature_product_detail->category->categoryDetail->slug,
                'translation' => $feature_product_detail->translation,
                'is_p2p' => $is_p2p
            );
        }
        foreach ($on_sale_product_details as  $on_sale_product_detail) {
            $multiply = $on_sale_product_detail->variant->first()->multiplier ?? 1;
            $title = $on_sale_product_detail->translation->first() ? $on_sale_product_detail->translation->first()->title : $on_sale_product_detail->sku;
            $image_url = $on_sale_product_detail->media->first() ? $on_sale_product_detail->media->first()->image->path['proxy_url'] . $p_dim . $on_sale_product_detail->media->first()->image->path['image_path'] : $this->loadDefaultImage();
            $cat_name = '';
            if (@$on_sale_product_detail->category->categoryDetail->translation) {
                $cat_name =  $on_sale_product_detail->category->categoryDetail->translation->first()->name ?? $on_sale_product_detail->category->categoryDetail->slug;
            }
            $is_p2p = 0;
            if (@$on_sale_product_detail->category->categoryDetail->type_id && @$on_sale_product_detail->category->categoryDetail->type_id == 13) {
                $is_p2p = 1;
            }
            $on_sale_products[] = array(
                'id' => $on_sale_product_detail->id,
                'tag_title' => $on_sale_title ?? '0',
                'image_url' => $image_url,
                'media' => $on_sale_product_detail->media,
                'variant' => $on_sale_product_detail->variant,
                'sku' => $on_sale_product_detail->sku,
                'title' => Str::limit($title, 18, '..'),
                'url_slug' => $on_sale_product_detail->url_slug,
                'averageRating' => number_format($on_sale_product_detail->averageRating, 1, '.', ''),
                'inquiry_only' => $on_sale_product_detail->inquiry_only,
                'vendor_name' => $on_sale_product_detail->vendor ? $on_sale_product_detail->vendor->name : '',
                'vendor' => $on_sale_product_detail->vendor,
                'price' => Session::get('currencySymbol') . ' ' . (decimal_format(@$on_sale_product_detail->variant->first()->price ?? 0 * $multiply, ',')),
                'category' => $cat_name,
                'translation' => $on_sale_product_detail->translation,
                'is_p2p' => $is_p2p
            );
        }

        $top_rated_products = $popular_products = $selected_products = $single_category_products = $spot_light_products =  [];

        //get long term service
        $long_term_service_products = [];
        $additionalPreference = getAdditionalPreference(['is_long_term_service', 'is_token_currency_enable', 'token_currency']);
        if (@$additionalPreference['is_long_term_service'] == 1) {
            $long_term_service_products = $this->longTermServiceProducts($long_term_vendors, $additionalPreference, $language_id, $currency_id, '', $request->type, $p_dim);
        }
        $ordered_products = [];
        if ($this->checkTemplateForAction(8)) {

            $recently_viewed = $this->productvendorProducts($vendor_ids, $language_id, $currency_id, '', $request->type, $p_dim);
            $spot_light_products = $this->getSpotLight($preferences, $vendor_ids, $language_id, $currency_id, $p_dim); // get spotlight product i.e. max discounted products

            $single_category_product_ids = $this->getSingleCategoryProducts(); // get single selected category's products
            $single_category_products = $this->getProducts($preferences, $vendor_ids, $language_id, $currency_id, $p_dim, $single_category_product_ids);

            $selected_product_ids = $this->getSelectedProducts(); // get single selected category's products
            $selected_products = $this->getProducts($preferences, $vendor_ids, $language_id, $currency_id, $p_dim, $selected_product_ids);

            $popular_product_ids = $this->getMostPopularProducts();  // get selected products to display
            $popular_products = $this->getProducts($preferences, $vendor_ids, $language_id, $currency_id, $p_dim, $popular_product_ids);

            $top_rated_products_ids = $this->getTopRatedProducts();  // get selected products to display
            $top_rated_products = $this->getProducts($preferences, $vendor_ids, $language_id, $currency_id, $p_dim, $top_rated_products_ids);

            $ordered_products = $this->getProducts($preferences, $vendor_ids, $language_id, $currency_id, $p_dim, $this->getLastProductOrdered(), 10);



        }
        /**  Recent order */
        $activeOrders = [];
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
                    // dd($vendor->toArray());
                    $vendor->tag_title = $vendor_title ?? '0';
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
        /**  Recent order end */

        /**  Get cities */
        if ($preferences->is_hyperlocal == 1) {
            $this->getCities_v2($language_id);
        }
        /**  Get cities end */

        /** Respose data */
        $data = [
            'vendor_ids' =>$vendor_ids,
            'brands' => $brands,
            'vendors' => $vendors,
            'new_products' => $new_products,
            'homePageLabels' => $home_page_labels,
            'feature_products' => $feature_products,
            'on_sale_products' => $on_sale_products,
            'trending_vendors' => (!empty($trendingVendors) && count($trendingVendors) > 0) ? $trendingVendors : $mostSellingVendors,
            'active_orders' => $activeOrders,

        ];

        if ($request->has('noTinJson') && $request->noTinJson == 1) {
            $data = [
                'vendor_ids' =>$vendor_ids,
                'brands' => $brands,
                'vendors' => $vendors,
                'new_products' => $new_products,
                'top_rated'       => $top_rated_products,
                'ordered_products'       => $ordered_products,
                'recently_viewed' => $recently_viewed,
                'homePageLabels' => $home_page_labels,
                'featured_products' => $feature_products,
                'on_sale' => $on_sale_products,
                'cities' => $this->cities,
                'long_term_service' => $long_term_service_products,
                'trending_vendors' => (!empty($trendingVendors) && count($trendingVendors) > 0) ? $trendingVendors : [],
                'best_sellers'     => (!empty($mostSellingVendors) && count($mostSellingVendors) > 0) ? $mostSellingVendors : [],
                'spotlight_deals'  => (!empty($spot_light_products) && count($spot_light_products) > 0) ? $spot_light_products : [],
                'single_category_products'  => (!empty($single_category_products) && count($single_category_products) > 0) ? $single_category_products : [],
                'selected_products'  => (!empty($selected_products) && count($selected_products) > 0) ? $selected_products : [],
                'most_popular_products'  => (!empty($popular_products) && count($popular_products) > 0) ? $popular_products : [],
                'recent_orders' => $activeOrders,
            ];
            return $data;
        }
        return $this->successResponse($data);
    }

    /**
     * getCities
     *
     * @param  mixed $language_id
     * @return $cities
     */
    public function getCities_v2($language_id)
    {
        $this->cities =  VendorCities::with(['translations' => function ($q) use ($language_id) {
            $q->where('language_id', $language_id);
        }])->where(function ($q) {
            $q->where('latitude', '!=', null);
            $q->where('longitude', '!=', null);
        })->get();

        $this->cities = $this->cities->map(function ($da) {
            $da->title = $da->translations->first() ? $da->translations->first()->name : $da->slug;
            unset($da->translations);
            return $da;
        });
        return $this->cities;
    }


    public function get_spotlight_deals_selected_producst(Request $request)
    {
        try {
            if (!empty($request->layout_id)) {
                $selected_products = HomeProduct::with(['products.variants', 'products.media.image'])->where('layout_id', $request->layout_id)->paginate(15);
            } else {
                $selected_products = Product::with([
                    'variants', 'media.image'
                ])->select('id', 'sku', 'title', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only', 'spotlight_deals')->where('spotlight_deals', 1)->paginate(15);
            }
            return $this->successResponse($selected_products);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function searchCategories($langId, $keyword, $limit, $page)
    {

        // $orderBy = "";
        // foreach ($keyword as $key=>$word) {
        //     $orderBy .= " WHEN cts.name LIKE '$keyword%' THEN ".$key."  ";
        // }
        $categories = Category::join('category_translations as cts', 'categories.id', 'cts.category_id')
            ->leftjoin('types', 'types.id', 'categories.type_id')
            ->select('categories.id', 'categories.icon', 'categories.image', 'categories.slug', 'categories.parent_id', 'cts.name', 'categories.warning_page_id', 'categories.template_type_id', 'types.title as redirect_to')
            ->where('categories.id', '>', '1')
            ->where('categories.is_visible', 1)
            ->where('categories.status', '!=', 2)
            ->where('categories.is_core', 1)
            ->where('cts.language_id', $langId)
            ->where(function ($q) use ($keyword) {
                // foreach ($keyword as $word) {
                //     $q->orwhere('cts.name', 'LIKE', $word . '%')
                //         ->orWhere('categories.slug', 'LIKE', $word . '%')
                //         ->orWhere('cts.trans-slug', 'LIKE', $word . '%');
                // }
                $q->where('cts.name', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('categories.slug', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('cts.trans-slug', 'LIKE', '%' . $keyword . '%');
            });
            // if(@$orderBy){
            //     $categories = $categories->orderByRaw("CASE ".$orderBy." ELSE 10 END, cts.name");
            // }
            $categories = $categories->orderBy('categories.parent_id', 'asc')
            ->orderBy('categories.position', 'asc')
            ->groupBy('cts.category_id')
            // ->limit(5)->get();
            ->paginate($limit, $page);
        $category_results = [];
        foreach ($categories as $category) {
            $category->response_type = 'category';
            $category->image_url = $category->image['proxy_url'] . '80/80' . $category->image['image_path'];
            $category_results[] = $category;
        }
        // dd($categories);
        if (@$category_results && $page == 1) {
            return  $response[] = [
                'id' => 1, 'title' => __('Category'), 'result' => $category_results, 'lastPage' => $categories->lastPage(),
                'currentPage' =>  $categories->currentPage(),
                'total' =>  $categories->total(),
            ];
        }
        return 0;
    }


    public function  searchVendors($langId, $keyword, $limit, $page, $action='delivery', $latitude, $longitude)
    {
        // $orderBy = "";
        // foreach ($keyword as $key=>$word) {
        //     $orderBy .= " WHEN name LIKE '$word%' THEN ".$key."  ";
        // }
        $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'slots_with_service_area')->first();
        $categoryTypes = getServiceTypesCategory($action);
        $vendors = Vendor::whereHas('getAllCategory.category', function ($q) use ($categoryTypes) {
            $q->whereIn('type_id', $categoryTypes);
        })
        ->select('id', 'name', 'name  as dataname', 'logo', 'address',  'slug', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude')
        ->withAvg('product', 'averageRating','closed_store_order_scheduled')
        ->where($action, 1);
        if (($preferences) && ($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {

            if (!empty($latitude) && !empty($longitude)) {
                $vendors = $vendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                    $query->select('vendor_id')
                        ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                });

                if (isset($preferences->slots_with_service_area) && ($preferences->slots_with_service_area == 1)) {
                    $slot_vendors = clone $vendors;
                    $data = $slot_vendors->get();
                    foreach ($data as $key => $value) {
                        $vendors = $vendors->when(($value->show_slot == 0), function ($query) use ($latitude, $longitude) {
                            return $query->where(function ($query1) use ($latitude, $longitude) {
                                $query1->whereHas('slot.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                    $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                })
                                    ->orWhereHas('slotDate.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                        $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                    });
                            });
                        });
                    }
                }
            }
        }

        $vendors = $vendors->where(function ($q) use ($keyword) {
            // foreach ($keyword as $word) {
            //     $q->orwhere('name', 'LIKE', '%'.$word . '%')->orWhere('address', 'LIKE', '%'.$word . '%');
            // }
            $q->where('name', 'LIKE', '%'. $keyword .'%')->orWhere('address', 'LIKE', '%' . $keyword . '%');
        })->where('status', 1);
        // if(@$orderBy){
        //     $vendors = $vendors->orderByRaw("CASE ".$orderBy." ELSE 10 END, name");
        // }
        // ->limit(5)->get();
        $vendors = $vendors->paginate($limit, $page);

        $vendor_results = [];
        foreach ($vendors as $vendor) {
            $vendor->is_vendor_closed = 0;
            if($vendor->show_slot == 0){
                if(empty($vendor->slotdate_start_end_time) && empty($vendor->slot_start_end_time)){
                    $vendor->is_vendor_closed = 1;
                }else{
                    $vendor->is_vendor_closed = 0;
                    if(!empty($vendor->slotdate_start_end_time)){
                        $slotdate_start_end_time = explode('##', $vendor->slotdate_start_end_time);
                        if($slotdate_start_end_time[0]!='' && $slotdate_start_end_time[1]!=''){
                            $vendor->opening_time  = date('g:i A',strtotime($slotdate_start_end_time[0]));
                            $vendor->closing_time = date('g:i A',strtotime($slotdate_start_end_time[1]));
                        }
                    }elseif(!empty($vendor->slot_start_end_time)){
                        $slot_start_end_time = explode('##', $vendor->slot_start_end_time);
                        if($slot_start_end_time[0]!='' && $slot_start_end_time[1]!=''){
                            $vendor->opening_time  = date('g:i A',strtotime($slot_start_end_time[0]));
                            $vendor->closing_time = date('g:i A',strtotime($slot_start_end_time[1]));
                        }
                    }
                }
            }
            $vendor->response_type = 'vendor';
            $vendor->image_url = $vendor->logo['proxy_url'] . '80/80' . $vendor->logo['image_path'];
            $vendor->is_vendor_closed = 0;
            if ($vendor->show_slot == 0) {
                if (($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty())) {
                    $vendor->is_vendor_closed = 1;
                } else {
                    $vendor->is_vendor_closed = 0;
                    if ($vendor->slotDate->isNotEmpty()) {
                        if ($vendor->slotDate->first()->start_time != '' && $vendor->slotDate->first()->end_time != '') {
                            $vendor->opening_time  = date('g:i A', strtotime($vendor->slotDate->first()->start_time));
                            $vendor->closing_time = date('g:i A', strtotime($vendor->slotDate->first()->end_time));
                        }
                    } elseif ($vendor->slot->isNotEmpty()) {

                        if ($vendor->slot->first()->start_time && $vendor->slot->first()->end_time) {
                            $vendor->opening_time = date('g:i A', strtotime($vendor->slot->first()->start_time));
                            $vendor->closing_time = date('g:i A', strtotime($vendor->slot->first()->end_time));
                        }
                    }
                }
            }
            $vendor_results[] = $vendor;
        }

        if (@$vendor_results && $page == 1) {
          return  $response[] = [
                'id' => 3, 'title' => __('Vendor'), 'result' => $vendor_results, 'lastPage' => $vendors->lastPage(),
                'currentPage' =>  $vendors->currentPage(),
                'total' =>  $vendors->total(),
            ];
        }

        return 0;
    }

    public function searchBrand($langId, $keyword, $limit, $page)
    {
        // $orderBy = "";
        // foreach ($keyword as $key=>$word) {
        //     $orderBy .= " WHEN bt.title LIKE '$word%' THEN ".$key."  ";
        // }
        $brands = Brand::join('brand_translations as bt', 'bt.brand_id', 'brands.id')
            ->select('brands.id', 'bt.title  as dataname', 'image')
            // ->where(function ($q) use ($keyword) {
            //     foreach ($keyword as $word) {
            //         $q->orWhere('bt.title', 'LIKE', $word . '%');
            //     }
            // })
            ->where('bt.title', 'LIKE', '%' . $keyword . '%')
            ->where('brands.status', '!=', '2')
            ->where('bt.language_id', $langId)
        // if(@$orderBy){
        //     $brands = $brands->orderByRaw("CASE ".$orderBy." ELSE 10 END, bt.title");
        // }

            ->orderBy('brands.position', 'asc');
        // ->limit(5)->get();
        $brands = $brands->paginate($limit, $page);
        $brand_results = [];
        foreach ($brands as $brand) {
            $brand->response_type = 'brand';
            $brand->image_url = $brand->image['proxy_url'] . '80/80' . $brand->image['image_path'];
            $brand_results[] = $brand;
        }

        if (@$brand_results && $page == 1) {
            return $response[] = [
                'id' => 2, 'title' => __('brand'), 'result' => $brand_results, 'lastPage' => $brands->lastPage(),
                'currentPage' =>  $brands->currentPage(),
                'total' =>  $brands->total(),
            ];
        }
        return 0;
    }

    public function searchProduct($langId, $keyword, $limit, $page, $action, $latitude, $longitude)
    {
        // $orderBy = "";
        // foreach ($keyword as $key=>$word) {
        //     $orderBy .= " WHEN pt.title LIKE '$word%' THEN ".$key."  ";
        // }
        $allowed_vendors = $this->getServiceAreaVendors($latitude, $longitude, $action);
        $products = Product::byProductCategoryServiceType($action)->with(['category.categoryDetail.translation' => function ($q) use ($langId) {
            $q->where('category_translations.language_id', $langId);
        }, 'media', 'media.image','vendor', 'translation', 'variant',])->join('product_translations as pt', 'pt.product_id', 'products.id')
            ->select('products.id', 'products.sku', 'pt.title  as dataname', 'pt.body_html', 'pt.meta_title', 'pt.meta_keyword', 'pt.meta_description')
            ->where('pt.language_id', $langId)
            ->whereHas('vendor', function ($query) use ($action) {
                $query->where($action, 1);
            })

            ->where(function ($q) use ($keyword) {
                // foreach ($keyword as $word) {
                //     $q->orwhere('products.sku', ' LIKE', '%'.$word . '%')->orWhere('products.url_slug', 'LIKE', '%'. $word . '%')->orWhere('pt.title', 'LIKE', '%'. $word . '%');
                // }
                $q->where('products.sku', ' LIKE', '%' . $keyword . '%')->orWhere('products.url_slug', 'LIKE', '%' . $keyword . '%')->orWhere('pt.title', 'LIKE', '%' . $keyword . '%');
            })->where('products.is_live', 1)->whereNull('deleted_at')->groupBy('products.id')
            ->whereIn('vendor_id', $allowed_vendors);
            // if(@$orderBy){
            //     $products = $products->orderByRaw("CASE ".$orderBy." ELSE 10 END, pt.title");
            // }
        $products = $products->paginate($limit, $page);
        $product_results = [];
        $user = Auth::user();
        $clientCurrency = ClientCurrency::where('currency_id', $user->currency)->first();

        foreach ($products as $product) {
            $product->response_type = 'product';
            $product->image_url = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
            foreach ($product->variant as $key => $value) {
                $product->variant[$key]->multiplier = $clientCurrency->doller_compare;
                $product->variant[$key]->price *= $product->variant[$key]->multiplier;
                $product->variant[$key]->compare_at_price *= $product->variant[$key]->multiplier;
            }
            $product_results[] = $product;
        }
        if (@$product_results) {
            return  $response[] = ['id' => 4, 'title' => __('Product'), 'result' => $product_results, 'lastPage' => $products->lastPage(),
            'currentPage' =>  $products->currentPage(),
            'total' =>  $products->total(),
        ];
        }
        return 0;
    }

    private  function createSearchKeywords($request){
        $searchQuery = $request->keyword;
        $results = [];
        $results = $keyword = explode(' ', $searchQuery);

        for ($i = 0; $i < count($keyword); $i++) {
            for ($j = $i + 1; $j < count($keyword); $j++) {
                $result = $keyword[$i] . ' ' . $keyword[$j];
                array_push($results, $result);
            }
        }

        // Add three-word combinations to the results
        for ($i = 0; $i < count($keyword); $i++) {
            for ($j = $i + 1; $j < count($keyword); $j++) {
                for ($k = $j + 1; $k < count($keyword); $k++) {
                    $result = $keyword[$i] . ' ' . $keyword[$j] . ' ' . $keyword[$k];
                    array_push($results, $result);
                }
            }
        }

       return array_reverse($results);
    }

    public function globalSearch(Request $request, $for = 'all', $dataId = 0)
    {
        try {
            $for = $request->view_type ?? 'all';
            // $keyword = $this->createSearchKeywords($request);
            $keyword = $request->keyword;
            // Display the results
            $langId = Auth::user()->language;
            $curId = Auth::user()->language;
            $limit = $request->has('limit') ? $request->limit : 10;
            $page = $request->has('page') ? $request->page : 1;
            $action = $request->has('type') && $request->type ? $request->type : 'delivery';
            // $types = ['delivery', "dine_in", "takeaway"];

            $latitude = $request->latitude;
            $longitude = $request->longitude;

            $response = array();
            if ($for == 'all') {
                $cats = $this->searchCategories($langId, $keyword, $limit, $page);
                if (@$cats) {
                    $response[] = $cats;
                }
                $searchedBrands = $this->searchBrand($langId, $keyword, $limit, $page);
                if (@$searchedBrands) {
                    $response[] = $searchedBrands;
                }

                $searchedVendors = $this->searchVendors($langId, $keyword, $limit, $page, $action, $latitude, $longitude);
                if (@$searchedVendors) {
                    $response[] = $searchedVendors;
                }


                $searchedProducts = $this->searchProduct($langId, $keyword, $limit, $page, $action, $latitude, $longitude);
                if (@$searchedProducts) {
                    $response[] = $searchedProducts;
                }

                return $this->successResponse($response);
            } elseif ($for == 'product') {
                $searchedProducts = $this->searchProduct($langId, $keyword, $limit, $page, $action, $latitude, $longitude);
                if (@$searchedProducts) {
                    $response[] = $searchedProducts;
                }
            } elseif ($for == 'category') {
                $cats = $this->searchCategories($langId, $keyword, $limit, $page);
                if (@$cats) {
                    $response[] = $cats;
                }
            } elseif ($for == 'vendor') {
                $searchedVendors = $this->searchVendors($langId, $keyword, $limit, $page, $action, $latitude, $longitude);
                if (@$searchedVendors) {
                    $response[] = $searchedVendors;
                }
            } elseif ($for == 'brand') {
                $searchedBrands = $this->searchBrand($langId, $keyword, $limit, $page);
                if (@$searchedBrands) {
                    $response[] = $searchedBrands;
                }
            } else {
                $allowed_vendors = $this->getServiceAreaVendors($latitude, $longitude, $action);
                $products = Product::byProductCategoryServiceType($action)->join('product_translations as pt', 'pt.product_id', 'products.id')
                    ->select('products.id', 'products.sku', 'pt.title', 'pt.body_html', 'pt.meta_title', 'pt.meta_keyword', 'pt.meta_description')
                    ->where('pt.language_id', $langId)
                    ->whereHas('vendor', function ($query) use ($action) {
                        $query->where($action, 1);
                    })
                    ->where(function ($q) use ($keyword) {
                        // foreach ($keyword as $word) {
                        //     $q->orwhere('products.sku', ' LIKE', $word . '%')
                        //         ->orWhere('products.url_slug', 'LIKE', $word . '%')
                        //         ->orWhere('pt.title', 'LIKE', $word . '%');
                        // }
                        $q->where('products.sku', ' LIKE', $keyword . '%')
                        ->orWhere('products.url_slug', 'LIKE', $keyword . '%')
                        ->orWhere('pt.title', 'LIKE', $keyword . '%');
                    });
                if ($for == 'category') {
                    $prodIds = array();
                    $productCategory = ProductCategory::select('product_id')->where('category_id', $dataId)->distinct()->get();
                    if ($productCategory) {
                        foreach ($productCategory as $key => $value) {
                            $prodIds[] = $value->product_id;
                        }
                    }
                    $products = $products->whereIn('products.id', $prodIds);
                }
                if ($for == 'vendor') {
                    $products = $products->where('products.vendor_id', $dataId);
                }
                if ($for == 'brand') {
                    $products = $products->where('products.brand_id', $dataId);
                }
                $products = $products->where('products.is_live', 1)
                    ->whereIn('vendor_id', $allowed_vendors)
                    ->whereNull('deleted_at')->groupBy('products.id')
                    ->paginate($limit, $page);
                $product_results = [];
                foreach ($products as $product) {

                    $product->response_type = 'product';
                    $product_results[] = $product;
                }
                if (@$product_results) {
                    $response[] = ['id' => 4, 'title' => __('Product'), 'result' => $product_results, 'lastPage' => $products->lastPage()];
                }
            }
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
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
    public function postHomePageDataV2(Request $request,$set_template,$enable_layout,$additionalPreference,$user, $getSubCatIds='')
    {
        $client_timezone = DB::table('clients')->first('timezone');

        $timezone        = $user->timezone ? $user->timezone :  ($client_timezone->timezone ?? 'Asia/Kolkata' );
       // $additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency','is_long_term_service','is_admin_vendor_rating','is_show_vendor_on_subcription']);
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
        if (is_null($language_id)) {
            $local = ($request->hasHeader('language')) ? $request->header('language') : 1;

            $language_id = $local;
        }

        $currency_id = $this->setCurrencyInSesion();


        //sd

        $featured_products_title = $vendors_title = $new_products_title = $on_sale_title = $brands_title = $best_sellers_title = $recent_orders_title = $banner_title = $selected_products_title = $trending_vendors_title = '';

        $slugs = array("featured_products", "vendors", "new_products", "on_sale", "brands", "best_sellers", "recent_orders", "banner", "selected_products", "trending");

        $results = DB::select("
            SELECT
                cab_booking_layout_transaltions.title,
                cab_booking_layout_transaltions.cab_booking_layout_id,
                cab_booking_layouts.slug
            FROM
                cab_booking_layout_transaltions
                INNER JOIN 	cab_booking_layouts ON cab_booking_layout_transaltions.cab_booking_layout_id = 	cab_booking_layouts.id
            WHERE
                cab_booking_layout_transaltions.language_id = ?
                AND cab_booking_layout_transaltions.title IS NOT NULL
                AND 	cab_booking_layouts.slug IN (" . implode(',', array_fill(0, count($slugs), '?')) . ")
        ", array_merge([$language_id], $slugs));
       // pr($results);

        $titles = [];
        foreach ($results as $result) {
            switch ($result->slug) {
                case "featured_products":
                    $titles['featured_products_title'] = $result->title;
                    break;
                case "vendors":
                    $titles['vendors_title'] = $result->title;
                    break;
                case "new_products":
                    $titles['new_products_title'] = $result->title;
                    break;
                case "on_sale":
                    $titles['on_sale_title'] = $result->title;
                    break;
                case "brands":
                    $titles['brands_title'] = $result->title;
                    break;
                case "best_sellers":
                    $titles['best_sellers_title'] = $result->title;
                    break;
                case "recent_orders":
                    $titles['recent_orders_title'] = $result->title;
                    break;
                case "banner":
                    $titles['banner_title'] = $result->title;
                    break;
                case "selected_products":
                    $titles['selected_products_title'] = $result->title;
                    break;
                case "trending":
                    $titles['trending_vendors_title'] = $result->title;
                    break;
                default:
                    break;
            }
        }
        //extract($titles);

        $featured_products_title = $titles['featured_products_title'] ?? null;
        $vendors_title = $titles['vendors_title'] ?? null;
        $new_products_title = $titles['new_products_title'] ?? null;
        $on_sale_title = $titles['on_sale_title'] ?? null;
        $brands_title = $titles['brands_title'] ?? null;
        $best_sellers_title = $titles['best_sellers_title'] ?? null;
        $recent_orders_title = $titles['recent_orders_title'] ?? null;
        $selected_products_title = $titles['selected_products_title'] ?? null;
        $trending_vendors_title = $titles['trending_vendors_title'] ?? null;

        $vendor_ids = $this->getRandomVendorIdsForHomePage($preferences, $request->type, $preferences['is_admin_vendor_rating'], $latitude, $longitude,@$request->momo);
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

        if ($request->rating) {
            $preferences->rating = $request->rating;
        }

        if(count($vendor_ids) > 0){
            $vendors = $this->getVendorForHomePage($preferences, "random_or_admin_rating", $timezone, $additionalPreference['is_admin_vendor_rating'], $request->type, $language_id, $latitude, $longitude, $vendor_ids,null,$this->venderFilterOpenClose,$this->venderFilterbest,$request->nearest_vendor);
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
                $trendingVendors = $this->getVendorForHomePage($preferences, "trending_vendors", $timezone, 0, $request->type, $language_id, $latitude, $longitude, $trending_vendors);
            }
        }

        if (($latitude) && ($longitude)) {
            Session::put('vendors', $vendor_ids);

        }

        //get Most Selling Vendors
        $mostSellingVendors = []; //best_sellers
        if (in_array('best_sellers', $enable_layout)) {
            if(count($vendor_ids) > 0){
                $dataMo = $this->getVendorForHomePage($preferences, "best_sellers", $timezone, 0, $request->type, $language_id, $latitude, $longitude, $vendor_ids);
                if(sizeof($dataMo)){
                    $mostSellingVendors = $dataMo;
                }
            }
        }
        $on_sale_product_details =$on_sale_products = [];
        if (in_array('on_sale', $enable_layout)) {  # if enable new_products section in
            $on_sale_products = $on_sale_product_details = $this->vendorProducts($vendor_ids, $language_id, 'USD', 'on_sale', $request->type,$on_sale_title, $p_dim, $getSubCatIds);
        }
        $new_product_details =$new_products = [];
        if (in_array('new_products', $enable_layout)) {  # if enable new_products section in
            $new_products = $new_product_details = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'is_new', $request->type,$new_products_title,$p_dim, $getSubCatIds);
        }
        $feature_product_details = $feature_products = [];
        if (in_array('featured_products', $enable_layout)) {  # if enable featured_products section in
            $feature_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'is_featured', $request->type, $featured_products_title,$p_dim, $getSubCatIds);
            foreach ($feature_products as $product) {
                $product->body_html = strip_tags($product->body_html);
            }
        }

        if (in_array('banner', $enable_layout)) {  # if enable banner section in
            $cab_booking_layouts = CabBookingLayout::with('banner_image')->where('slug','banner')->get();

            foreach($cab_booking_layouts as $bkey => $bval){
                if(count($bval->banner_image) > 0)
                $banners[$bval->banner_image[0]->cab_booking_layout_id] = $bval->banner_image[0]->banner_image_url;
            }
        }

        $top_rated_products = '';
         //get long term service
        $long_term_service_products =[];
        if( in_array('long_term_service', $enable_layout) && @$additionalPreference['is_long_term_service'] == 1 && count($vendor_ids) > 0){ # if enable long_term_service section in
            $long_term_service_products = $this->longTermServiceProducts($vendor_ids, $additionalPreference, $language_id, $currency_id,'', $request->type,$p_dim);
        }
        //pr($set_template->template_id);
        //if($set_template->template_id ==10){
            $recently_viewed = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'recent_viewed', $request->type, $featured_products_title,$p_dim, $getSubCatIds);
            //$spot_light_products = $this->getSpotLight($preferences, $vendor_ids, $language_id, $currency_id, $p_dim); // get spotlight product i.e. max discounted products
            $spot_light_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'spotlight_deals', $request->type, $featured_products_title,$p_dim, $getSubCatIds);
            // pr($spot_light_products);
            //$single_category_product_ids = $this->getSingleCategoryProducts(); // get single selected category's products
            $single_category_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'single_category_products', $request->type, $featured_products_title,$p_dim, $getSubCatIds);
            // dd($single_category_products);
            //$selected_product_ids = $this->getSelectedProducts(); // get single selected category's products
            $selected_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'selected_products', $request->type, $featured_products_title,$p_dim, $getSubCatIds);

            //$popular_product_ids = $this->getMostPopularProducts();  // get selected products to display
            $popular_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'popular_products', $request->type, $featured_products_title,$p_dim, $getSubCatIds);

            //$top_rated_products_ids = $this->getTopRatedProducts();  // get selected products to display
            $top_rated_products = $this->vendorProducts($vendor_ids, $language_id, $currency_id, 'top_rated_products', $request->type, $featured_products_title,$p_dim, $getSubCatIds);

           // $ordered_products = $this->vendorProducts($preferences, $vendor_ids, $language_id, $currency_id, $p_dim, $this->getLastProductOrdered(), 10);

            //pr($top_rated_products);
        //}
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
                $this->getCities_v2($language_id);
            }
        }
        /**  Get cities end */


        /** Respose data */

            $data = [
                'vendor_ids'=>$vendor_ids,
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
                //'recent_orders' => $activeOrders,
                'banners' => $banners,
                'additionalPreference' => $additionalPreference,
            ];
            return $data ;
    }
  # check if last mile delivery on
  public function checkIfLastMileOn()
  {
      $preference = ClientPreference::first();
      if ( !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_code) && !empty($preference->dispacher_home_other_service_key_url))
          return $preference;
      else
          return false;
  }

    public function getAllAgentDetailFromDispatcher($data)
    {

        try {
                $client = new GClient([
                    'headers' => [
                        'personaltoken' => $data['dispacher_home_other_service_key'],
                        'shortcode'     => $data['dispacher_home_other_service_key_code'],
                        'content-type'  => 'application/json'
                    ]
                ]);

                $url = $data['dispacher_home_other_service_key_url']. '/api/get/all_agent_detail';
                $res = $client->get($url );
                $response = json_decode($res->getBody(), true);

                if ($response && $response['status'] === 200) {
                    return $response['data'];
                }
                return [];

        } catch (\Exception $e) {
           // Log::info($e->getMessage());
            return [];
        }
    }
}
