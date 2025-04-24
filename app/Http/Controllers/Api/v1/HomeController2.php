<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use App;
use Config;
use Log;
use Validation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use ConvertCurrency;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\UserRegistrationDocuments;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{User, MobileBanner, Category, Brand, Client, ClientPreference, Cms, Order, Banner, Vendor, VendorCategory, Category_translation, ClientLanguage, PaymentOption, Product, Country, Currency, ServiceArea, ClientCurrency, ProductCategory, BrandTranslation, Celebrity, UserVendor, AppStyling, Nomenclature, AppDynamicTutorial,ClientSlot, TempCart, VerificationOption, ShowSubscriptionPlanOnSignup};
use DateTime;
use DateInterval;
use DateTimeZone;

class HomeController2 extends BaseController
{
    use ApiResponser;

    private $curLang = 0;
    private $field_status = 2;

    /** Return header data, client profile and configure data */
    public function headerContent(Request $request)
    {
        try {
            $homeData = array();
            $client_language = ClientLanguage::select('language_id')->where(['is_primary' => 1, 'is_active' => 1])->first();
            
            $langId = ($request->hasHeader('language') && !empty($request->header('language'))) ? $request->header('language') : (($client_language) ? $client_language->language_id : 1);
            $homeData['profile'] = $preferences = Client::with(['preferences', 'preferences.additional_preferences', 'country:id,name,code,phonecode'])->select('id','country_id', 'company_name', 'code', 'sub_domain','database_name', 'logo','dark_logo', 'company_address', 'phone_number', 'email','custom_domain','contact_phone_number','socket_url')->first();
            //dd(Client::with('getPreference')->first()->getPreference->auto_implement_5_percent_tip);
            $app_styling_detail = AppStyling::getSelectedData();
            foreach ($app_styling_detail as $app_styling) {
                $key = $app_styling['key'];
                $homeData['profile']->preferences->$key = __($app_styling['value']);
            }
            $vendorMode = [];
            foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
                $clientVendorTypes = $vendor_typ_key.'_check';
                $nomenclature =  $vendor_typ_key.'_nomenclature';
                $vendorData = [];
                    if($preferences->preferences->$clientVendorTypes == 1){
                        $vendorData['name'] =  $this->getNomenclatureName($vendor_typ_value, $langId, false);
                        $vendorData["icon"] = config('constants.VendorTypesIcon.'.$vendor_typ_key);
                        //$vendorData["name"] = $clientVendorTypes;
                        $vendorData["type"] = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
                        
                        $vendorMode[] = $vendorData;
                    }
            }
            //pr($vendorMode);
            $homeData['profile']->preferences->vendorMode = $vendorMode;
            //dd($homeData['profile']);
            $delivery_nomenclature = $this->getNomenclatureName('Delivery', $langId, false);
            $dinein_nomenclature = $this->getNomenclatureName('Dine-In', $langId, false);
            $takeaway_nomenclature = $this->getNomenclatureName('Takeaway', $langId, false);
            $search_nomenclature = $this->getNomenclatureName('Search', $langId, false);
            $vendors_nomenclature = $this->getNomenclatureName('Vendors', $langId, false);
            $fixed_fee_nomenclature = $this->getNomenclatureName('fixed_fee', $langId, false);
            $referral_code = $this->getNomenclatureName('Referral Code', $langId, false);
            $want_to_tip = $this->getNomenclatureName('want_to_tip', $langId, false);
            $fixed_fee_nomenclature=ucwords(str_replace("_"," ",$fixed_fee_nomenclature));
            $want_to_tip=ucwords(str_replace("_"," ",$want_to_tip));
            $passbase = VerificationOption::where(['code' => 'passbase','status' => 1])->first();

            $homeData['profile']->preferences->delivery_nomenclature = $delivery_nomenclature;
            $homeData['profile']->preferences->dinein_nomenclature = $dinein_nomenclature;
            $homeData['profile']->preferences->takeaway_nomenclature = $takeaway_nomenclature;
            $homeData['profile']->preferences->search_nomenclature = $search_nomenclature;
            $homeData['profile']->preferences->vendors_nomenclature = $vendors_nomenclature;
            $homeData['profile']->preferences->fixed_fee_nomenclature = $fixed_fee_nomenclature;
            $homeData['profile']->preferences->want_to_tip_nomenclature = $want_to_tip;
            $homeData['profile']->preferences->referral_code = $referral_code;
            if(!is_null($passbase))
            {
                $homeData['profile']->preferences->passbase_check = 1; 
                $passbase_creds = json_decode($passbase->credentials);
                $homeData['profile']->preferences->passbase_api_key = $passbase_creds->publish_key;
            }else{
                $homeData['profile']->preferences->passbase_check = 0;
            }
            


            $homeData['languages'] = ClientLanguage::with('language')->select('language_id', 'is_primary')->where('is_active', 1)->orderBy('is_primary', 'desc')->get();
            $banners = Banner::select("id", "name", "description", "image", "image_mobile", "link", 'redirect_category_id', 'redirect_vendor_id')
                ->where('status', 1)->where('validity_on', 1)
                ->where(function ($q) {
                    $q->whereNull('start_date_time')->orWhere(function ($q2) {
                        $q2->whereDate('start_date_time', '<=', Carbon::now())
                            ->whereDate('end_date_time', '>=', Carbon::now());
                    });
                })->orderBy('sorting', 'asc')->get();
            if ($banners) {
                foreach ($banners as $key => $value) {
                    $bannerLink = '';
                    $is_show_category = null;
                    $vendor_name = null;
                    if (!empty($value->link) && $value->link == 'category') {
                        $bannerLink = $value->redirect_category_id;
                        if ($bannerLink) {
                            $categoryData = Category::where('status', '!=', $this->field_status)->where('id', $value->redirect_category_id)->with('translation_one')->first();
                            $value->redirect_name = (($categoryData) && ($categoryData->translation_one)) ? $categoryData->translation_one->name : '';
                        }
                    }
                    if (!empty($value->link) && $value->link == 'vendor') {
                        $bannerLink = $value->redirect_vendor_id;
                        if ($bannerLink) {
                            $vendorData = Vendor::select('id', 'slug', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'latitude', 'longitude')->where('status', 1)->where('id', $value->redirect_vendor_id)->first();
                            if ($vendorData) {
                                $vendorData->is_show_category = ($vendorData->vendor_templete_id == 2 || $vendorData->vendor_templete_id == 4) ? 1 : 0;
                            }
                            $is_show_category = (($vendorData) && ($vendorData->vendor_templete_id == 1)) ? 0 : 1;
                            $value->is_show_category = $is_show_category;
                            $value->redirect_name = $vendorData->name ?? '';
                            $value->vendor = $vendorData;
                        }
                    }
                    $value->redirect_to = ucwords($value->link);
                    $value->redirect_id = $bannerLink;
                    unset($value->redirect_category_id);
                    unset($value->redirect_vendor_id);
                }
            }
            $mobile_banners = MobileBanner::select("id", "name", "description", "image", "link", 'redirect_category_id', 'redirect_vendor_id')
                ->where('status', 1)->where('validity_on', 1)
                ->with(['category:id,type_id', 'category.type', 'vendor'])
                ->where(function ($q) {
                    $q->whereNull('start_date_time')->orWhere(function ($q2) {
                        $q2->whereDate('start_date_time', '<=', Carbon::now())
                            ->whereDate('end_date_time', '>=', Carbon::now());
                    });
                })->orderBy('sorting', 'asc')->get();
            if ($mobile_banners) {
                foreach ($mobile_banners as $key => $value) {
                    $bannerLink = '';
                    $is_show_category = null;
                    $vendor_name = null;
                    if (!empty($value->link) && $value->link == 'category') {
                        $bannerLink = $value->redirect_category_id;
                        if ($bannerLink) {
                            $categoryData = Category::where('status', '!=', $this->field_status)->where('id', $value->redirect_category_id)->with('translation_one')->first();
                            $value->redirect_name = (($categoryData) && ($categoryData->translation_one)) ? $categoryData->translation_one->name : '';
                        }
                    }
                    if (!empty($value->link) && $value->link == 'vendor') {
                        $bannerLink = $value->redirect_vendor_id;
                        if ($bannerLink) {
                            $vendorData = Vendor::select('id', 'slug', 'name', 'banner', 'show_slot', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'latitude', 'longitude')->where('status', 1)->where('id', $value->redirect_vendor_id)->first();
                            if ($vendorData) {
                                $vendorData->is_show_category = ($vendorData->vendor_templete_id == 2 || $vendorData->vendor_templete_id == 4) ? 1 : 0;
                            }
                            $is_show_category = (($vendorData) && ($vendorData->vendor_templete_id == 1)) ? 0 : 1;
                            $value->is_show_category = $is_show_category;
                            $value->redirect_name = $vendorData->name ?? '';
                            $value->vendor = $vendorData;
                        }
                    }
                    $value->redirect_to = ucwords($value->link);
                    $value->redirect_id = $bannerLink;
                    unset($value->redirect_category_id);
                    unset($value->redirect_vendor_id);
                }
            }
            $homeData['banners'] = $banners;
            $homeData['mobile_banners'] = $mobile_banners;
            $homeData['currencies'] = ClientCurrency::with('currency')->select('currency_id', 'is_primary', 'doller_compare')->orderBy('is_primary', 'desc')->get();
            $homeData['dynamic_tutorial'] = AppDynamicTutorial::orderBy('sort')->get();

            $payment_codes = ['stripe', 'stripe_fpx', 'stripe_oxxo','stripe_ideal','razorpay', 'checkout', 'paytab','flutterwave', 'khalti'];
            $payment_creds = PaymentOption::select('code', 'credentials')->whereIn('code', $payment_codes)->where('status', 1)->get();
            if ($payment_creds) {
                foreach ($payment_creds as $creds) {
                    $creds_arr = json_decode($creds->credentials);
                    if ($creds->code == 'stripe') {
                        $homeData['profile']->preferences->stripe_publishable_key = (isset($creds_arr->publishable_key) && (!empty($creds_arr->publishable_key))) ? $creds_arr->publishable_key : '';
                    }
                    if ($creds->code == 'stripe_fpx') {
                        $homeData['profile']->preferences->stripe_fpx_publishable_key = (isset($creds_arr->publishable_key) && (!empty($creds_arr->publishable_key))) ? $creds_arr->publishable_key : '';
                    }
                    if ($creds->code == 'stripe_oxxo') {
                        $homeData['profile']->preferences->stripe_oxxo_publishable_key = (isset($creds_arr->publishable_key) && (!empty($creds_arr->publishable_key))) ? $creds_arr->publishable_key : '';
                    }
                    if ($creds->code == 'stripe_ideal') {
                        $homeData['profile']->preferences->stripe_ideal_publishable_key = (isset($creds_arr->publishable_key) && (!empty($creds_arr->publishable_key))) ? $creds_arr->publishable_key : '';
                    }
                    if ($creds->code == 'razorpay') {
                        $homeData['profile']->preferences->razorpay_api_key = (isset($creds_arr->api_key) && (!empty($creds_arr->api_key))) ? $creds_arr->api_key : '';
                    }
                    if ($creds->code == 'checkout') {
                        $homeData['profile']->preferences->checkout_public_key = (isset($creds_arr->public_key) && (!empty($creds_arr->public_key))) ? $creds_arr->public_key : '';
                    }
                    if ($creds->code == 'paytab') {
                        $homeData['profile']->preferences->paytab_profile_id = (isset($creds_arr->profile_id) && (!empty($creds_arr->profile_id))) ? $creds_arr->profile_id : '';
                        $homeData['profile']->preferences->paytab_server_key = (isset($creds_arr->mobile_server_key) && (!empty($creds_arr->mobile_server_key))) ? $creds_arr->mobile_server_key : '';
                        $homeData['profile']->preferences->paytab_client_key = (isset($creds_arr->mobile_client_key) && (!empty($creds_arr->mobile_client_key))) ? $creds_arr->mobile_client_key : '';
                    }
                    if ($creds->code == 'flutterwave') {
                        $homeData['profile']->preferences->flutterwave_public_key = (isset($creds_arr->client_id) && (!empty($creds_arr->client_id))) ? $creds_arr->client_id : '';
                    }
                    if ($creds->code == 'khalti') {
                        $homeData['profile']->preferences->khalti_api_key = (isset($creds_arr->api_key) && (!empty($creds_arr->api_key))) ? $creds_arr->api_key : '';
                    }

                    $homeData['profile']->preferences->show_subscription_plan_popup = 0;
                    $showSubscriptionPlan = ShowSubscriptionPlanOnSignup::find(1);
                    if(@$showSubscriptionPlan->show_plan_customer == 1 && @$showSubscriptionPlan->every_app_open == 1){
                        $homeData['profile']->preferences->show_subscription_plan_popup = 1;
                    }

                    $homeData['profile']->preferences->concise_signup = ClientPreference::first()->concise_signup;
                }
            }

            if (isset($homeData['profile']->custom_domain) && !empty($homeData['profile']->custom_domain) && $homeData['profile']->custom_domain != $homeData['profile']->sub_domain)
                $domain_link = "https://" . $homeData['profile']->custom_domain;
            else
                $domain_link = "https://" . $homeData['profile']->sub_domain . env('SUBMAINDOMAIN');
            $homeData['domain_link'] = $domain_link;

            return $this->successResponse($homeData);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /** return dashboard content like categories, vendors, brands, products     */
    public function homepage(Request $request)
    {
        try {
            $vends = [];
            $venderIds = [];
            $homeData = [];
            $user = Auth::user();
            $langId = $user->language;
            $currency_id = $user->currency;
            $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'is_service_area_for_banners')->first();
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $paginate = $request->has('limit') ? $request->limit : 12;
            //filter
            $venderFilterClose   = $request->has('close_vendor') && $request->close_vendor ? $request->close_vendor : null;
            $venderFilterOpen   = $request->has('open_vendor') && $request->open_vendor ? $request->open_vendor : null;
            $venderFilterbest   = $request->has('best_vendor') && $request->best_vendor ? $request->best_vendor : null;
            $venderFilternear   = $request->has('near_me') && $request->near_me ? $request->near_me : null;

            $type = $request->has('type') ? $request->type : 'delivery';

            if (empty($type))
            $type = 'delivery';


            $categoryTypes = getServiceTypesCategory($type);
            
           
            $vendorData = Vendor::whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                $q->whereIn('type_id',$categoryTypes);
            })->select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude', 'closed_store_order_scheduled')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($type, 1);


            $ses_vendors = $this->getServiceAreaVendors($latitude, $longitude, $type);

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
                $vendorData = $vendorData->whereIn('id', $ses_vendors);
                //if($venderFilternear && ($venderFilternear == 1) ){
                    //->orderBy('vendorToUserDistance', 'ASC')
                    $vendorData =   $vendorData->orderBy('vendorToUserDistance', 'ASC');
                //}
            }
           
            //filter on ratings
            if($venderFilterbest && ($venderFilterbest == 1) ){
                $vendorData =   $vendorData->orderBy('product_avg_average_rating', 'desc');
            }
            $allVendorData = clone $vendorData;
            $vendorData = $vendorData->with('slot', 'slotDate')->where('status', 1)->limit(100)->get();
            $venderIds  = $allVendorData->with('slot', 'slotDate')->where('status', 1)->pluck('id');


            $timezone = $user->timezone ?? 'Asia/Kolkata';
            $start_date = new DateTime("now", new  DateTimeZone($timezone) );
            $start_date =  $start_date->format('Y-m-d');
            $end_date = Date('Y-m-d', strtotime('+13 days'));
            

            foreach ($vendorData as $vendor) {
                unset($vendor->products);

                $vendor->is_vendor_closed = 0;
                if ($vendor->show_slot == 0) {
                    if (($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty())) {
                        $vendor->is_vendor_closed = 1;
                    } else {
                        $vendor->is_vendor_closed = 0;
                        if ($vendor->slotDate->isNotEmpty()) {
                            $vendor->opening_time = Carbon::parse($vendor->slotDate->first()->start_time)->format('g:i A');
                            $vendor->closing_time = Carbon::parse($vendor->slotDate->first()->end_time)->format('g:i A');
                        } elseif ($vendor->slot->isNotEmpty()) {
                            $vendor->opening_time = Carbon::parse($vendor->slot->first()->start_time)->format('g:i A');
                            $vendor->closing_time = Carbon::parse($vendor->slot->first()->end_time)->format('g:i A');
                        }
                    }
                }

                $slotsDate = 0;
                $vendor->date_with_slots = [];
                if($vendor->closed_store_order_scheduled == 1){
                    $slotsDate = findSlot('',$vendor->id,'');
                    $vendor->delaySlot = $slotsDate;
                    $vendor->closed_store_order_scheduled = (($slotsDate)?$vendor->closed_store_order_scheduled:0);

                    if(!empty($slotsDate)){
                        $period = CarbonPeriod::create($start_date, $end_date);
                        $slotWithDate = [];
                        foreach($period as $key => $date){
                            $slotDate = trim(date('Y-m-d', strtotime($date)));
                            $slots = showSlot($slotDate,$vendor->id,'delivery');
                            if(!empty($slots)){
                                $slotData['date']  =  $slotDate;
                                $slotData['slots'] = $slots;
                                $slotWithDate[] = $slotData;
                            }
                        }
                        $vendor->date_with_slots = $slotWithDate;
                    }
                }else{
                    $vendor->delaySlot = 0;
                    $vendor->closed_store_order_scheduled = 0;
                }


                $vendor->is_show_category = ($vendor->vendor_templete_id == 2 || $vendor->vendor_templete_id == 4) ? 1 : 0;

                $vendorCategories = VendorCategory::with('category.translation_one')->where('vendor_id', $vendor->id)->where('status', 1)->get();
                $categoriesList = '';
                foreach ($vendorCategories as $key => $category) {
                    if ($category->category) {
                        $cat_name = isset($category->category->translation_one) ? $category->category->translation_one->name : $category->category->slug;
                        $categoriesList = $categoriesList . $cat_name ?? '';
                        if ($key !=  $vendorCategories->count() - 1) {
                            $categoriesList = $categoriesList . ', ';
                        }
                    }
                }
                $vendor->categoriesList = $categoriesList;

                $vends[] = $vendor->id;
                if (($preferences) && ($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {
                    $vendor = $this->getVendorDistanceWithTime($latitude, $longitude, $vendor, $preferences, $type);
                }

            }
            //filter vendor
            if($venderFilterClose && ($venderFilterClose == 1) ){
                $vendorData =   $vendorData->where('is_vendor_closed',1)->values();
            }
            if($venderFilterOpen && ($venderFilterOpen == 1) ){
                $vendorData =   $vendorData->where('is_vendor_closed',0)->values();
            }
           
            $vendorData =   $vendorData->take(5);

            // if (($preferences) && ($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {
            //     $vendorData = $vendorData->sortBy('lineOfSightDistance')->values()->all();
            // }

            $on_sale_product_details = $this->vendorProducts($vends, $langId, $clientCurrency, '', $type);
            $new_product_details    = $this->vendorProducts($vends, $langId, $clientCurrency, 'is_new', $type);
            $feature_product_details = $this->vendorProducts($vends, $langId, $clientCurrency, 'is_featured', $type);
            // foreach ($new_product_details as  $new_product_detail) {
            //     $multiply = $new_product_detail->variant->first() ? $new_product_detail->variant->first()->multiplier : 1;
            //     $title = $new_product_detail->translation->first() ? $new_product_detail->translation->first()->title : $new_product_detail->sku;
            //     $image_url = $new_product_detail->media->first() && !is_null($new_product_detail->media->first()->image) ? $new_product_detail->media->first()->image->path['image_fit'] . '600/600' . $new_product_detail->media->first()->image->path['image_path'] : '';
            //     $vprice1 = (isset($new_product_detail->variant->first()->price)?$new_product_detail->variant->first()->price * $multiply:0);
            //     $new_products[] = array(
            //         'image_url' => $image_url,
            //         'sku' => $new_product_detail->sku,
            //         'title' => $title,
            //         'url_slug' => $new_product_detail->url_slug,
            //         'averageRating' => number_format($new_product_detail->averageRating, 1, '.', ''),
            //         'inquiry_only' => $new_product_detail->inquiry_only,
            //         'vendor_name' => $new_product_detail->vendor ? $new_product_detail->vendor->name : '',
            //         'price' => decimal_format($vprice1),
            //         'category' => ($new_product_detail->category->categoryDetail->translation->first()) ? $new_product_detail->category->categoryDetail->translation->first()->name : $new_product_detail->category->categoryDetail->slug
            //     );
            // }
            // foreach ($feature_product_details as  $feature_product_detail) {
            //     $multiply = $feature_product_detail->variant->first() ? $feature_product_detail->variant->first()->multiplier : 1;
            //     $title = $feature_product_detail->translation->first() ? $feature_product_detail->translation->first()->title : $feature_product_detail->sku;
            //     $image_url = $feature_product_detail->media->first() &&  !is_null($feature_product_detail->media->first()->image)? $feature_product_detail->media->first()->image->path['image_fit'] . '600/600' . $feature_product_detail->media->first()->image->path['image_path'] : '';
            //     $vprice = (isset($feature_product_detail->variant->first()->price)?$feature_product_detail->variant->first()->price * $multiply:0);
            //     $feature_products[] = array(
            //         'image_url' => $image_url,
            //         'sku' => $feature_product_detail->sku,
            //         'title' => $title,
            //         'url_slug' => $feature_product_detail->url_slug,
            //         'averageRating' => number_format($feature_product_detail->averageRating, 1, '.', ''),
            //         'inquiry_only' => $feature_product_detail->inquiry_only,
            //         'vendor_name' => $feature_product_detail->vendor ? $feature_product_detail->vendor->name : '',
            //         'price' => decimal_format($vprice),
            //         'category' => ($feature_product_detail->category->categoryDetail->translation->first()) ? $feature_product_detail->category->categoryDetail->translation->first()->name : $feature_product_detail->category->categoryDetail->slug
            //     );
            // }
            // foreach ($on_sale_product_details as  $on_sale_product_detail) {
            //     $multiply = $on_sale_product_detail->variant->first() ? $on_sale_product_detail->variant->first()->multiplier : 1;
            //     $title = $on_sale_product_detail->translation->first() ? $on_sale_product_detail->translation->first()->title : $on_sale_product_detail->sku;
            //     $image_url = $on_sale_product_detail->media->first() && !is_null($on_sale_product_detail->media->first()->image) ? $on_sale_product_detail->media->first()->image->path['image_fit'] . '600/600' . $on_sale_product_detail->media->first()->image->path['image_path'] : '';
            //     $vprice2 = (isset($on_sale_product_detail->variant->first()->price)?$on_sale_product_detail->variant->first()->price * $multiply:0);
            //     $on_sale_products[] = array(
            //         'image_url' => $image_url,
            //         'sku' => $on_sale_product_detail->sku,
            //         'title' => $title,
            //         'url_slug' => $on_sale_product_detail->url_slug,
            //         'averageRating' => number_format($on_sale_product_detail->averageRating, 1, '.', ''),
            //         'inquiry_only' => $on_sale_product_detail->inquiry_only,
            //         'vendor_name' => $on_sale_product_detail->vendor ? $on_sale_product_detail->vendor->name : '',
            //         'price' => decimal_format($vprice2),
            //         'category' => ($on_sale_product_detail->category->categoryDetail->translation->first()) ? $on_sale_product_detail->category->categoryDetail->translation->first()->name : $on_sale_product_detail->category->categoryDetail->slug
            //     );
            // }
           

            $isVendorArea = 0;
            
            // Start Mobile Banners
            $mobile_banners = MobileBanner::select("id", "name", "description", "image", "link", 'redirect_category_id', 'redirect_vendor_id')
            ->where('status', 1)->where('validity_on', 1)
            ->with(['category:id,type_id', 'category.type', 'vendor'])
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
            if ($mobile_banners) {
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
           

            // End Mobile Banners
            $categories = $this->categoryNav($langId,  $venderIds,$type);
            $homeData['vendors'] = $vendorData;
            $homeData['categories'] = $categories;
            $homeData['reqData'] = $request->all();
            //$homeData['mobile_banners'] = $mobile_banners;
            $homeData['on_sale_products'] = $on_sale_product_details;
            $homeData['new_products'] = $new_product_details;
            $homeData['featured_products'] = $feature_product_details;

            $brands = Brand::with(['bc.categoryDetail', 'bc.categoryDetail.translation' =>  function ($q) use ($langId) {
                $q->select('category_translations.name', 'category_translations.category_id', 'category_translations.language_id')->where('category_translations.language_id', $langId);
            }, 'translation' => function ($q) use ($langId) {
                $q->select('title', 'brand_id', 'language_id')->where('language_id', $langId);
            }])
                ->whereHas('bc.categoryDetail', function ($q) {
                    $q->where('categories.status', 1);
                })
                ->select('id', 'image', 'image_banner')->where('status', 1)->orderBy('position', 'asc')->get();

            $homeData['brands'] = $brands;
            $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
            $homeData['is_admin'] = $user_vendor_count > 0 ? 1 : 0;
            return $this->successResponse($homeData);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /** return get subcategory content like categories, vendors, brands, products     */
    public function getSubcategoryVendor(Request $request)
    {
        try {
            $vends = [];
            $venderIds = [];
            $homeData = [];
            $user = Auth::user();
            $langId = $user->language;
            $currency_id = $user->currency;
            $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude')->first();
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $paginate = $request->has('limit') ? $request->limit : 12;
            //filter
            $venderFilterClose   = $request->has('close_vendor') && $request->close_vendor ? $request->close_vendor : null;
            $venderFilterOpen   = $request->has('open_vendor') && $request->open_vendor ? $request->open_vendor : null;
            $venderFilterbest   = $request->has('best_vendor') && $request->best_vendor ? $request->best_vendor : null;
            $venderFilternear   = $request->has('near_me') && $request->near_me ? $request->near_me : null;

            $type = $request->has('type') ? $request->type : 'delivery';
            $cid = $request->category_id;
            if (empty($type))
            $type = 'delivery';

            $vendor_ids = [];
            $vendor_categories = VendorCategory::where('category_id', $cid)->where('status', 1)->get();
            foreach ($vendor_categories as $vendor_category) {
                if (!in_array($vendor_category->vendor_id, $vendor_ids)) {
                    $vendor_ids[] = $vendor_category->vendor_id;
                }
            }

            $vendorData = Vendor::select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude', 'closed_store_order_scheduled')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($type, 1);

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
                $vendorData = $vendorData->whereIn('id', $vendor_ids);
                //if($venderFilternear && ($venderFilternear == 1) ){
                    //->orderBy('vendorToUserDistance', 'ASC')
                    $vendorData =   $vendorData->orderBy('vendorToUserDistance', 'ASC');
                //}
            }

            //filter on ratings
            if($venderFilterbest && ($venderFilterbest == 1) ){
                $vendorData =   $vendorData->orderBy('product_avg_average_rating', 'desc');
            }
            $allVendorData = clone $vendorData;
            $vendorData = $vendorData->with('slot', 'slotDate')->where('status', 1)->take(5)->get();
            $venderIds = $allVendorData->with('slot', 'slotDate')->where('status', 1)->pluck('id');

            $timezone = $user->timezone ?? 'Asia/Kolkata';
            $start_date = new DateTime("now", new  DateTimeZone($timezone) );
            $start_date =  $start_date->format('Y-m-d');
            $end_date = Date('Y-m-d', strtotime('+13 days'));
            

            foreach ($vendorData as $vendor) {
                unset($vendor->products);

                $vendor->is_vendor_closed = 0;
                if ($vendor->show_slot == 0) {
                    if (($vendor->slotDate->isEmpty()) && ($vendor->slot->isEmpty())) {
                        $vendor->is_vendor_closed = 1;
                    } else {
                        $vendor->is_vendor_closed = 0;
                        if ($vendor->slotDate->isNotEmpty()) {
                            $vendor->opening_time = Carbon::parse($vendor->slotDate->first()->start_time)->format('g:i A');
                            $vendor->closing_time = Carbon::parse($vendor->slotDate->first()->end_time)->format('g:i A');
                        } elseif ($vendor->slot->isNotEmpty()) {
                            $vendor->opening_time = Carbon::parse($vendor->slot->first()->start_time)->format('g:i A');
                            $vendor->closing_time = Carbon::parse($vendor->slot->first()->end_time)->format('g:i A');
                        }
                    }
                }

                $slotsDate = 0;
                $vendor->date_with_slots = [];
                if($vendor->closed_store_order_scheduled == 1){
                    $slotsDate = findSlot('',$vendor->id,'');
                    $vendor->delaySlot = $slotsDate;
                    $vendor->closed_store_order_scheduled = (($slotsDate)?$vendor->closed_store_order_scheduled:0);

                    if(!empty($slotsDate)){
                        $period = CarbonPeriod::create($start_date, $end_date);
                        $slotWithDate = [];
                        foreach($period as $key => $date){
                            $slotDate = trim(date('Y-m-d', strtotime($date)));
                            $slots = showSlot($slotDate,$vendor->id,'delivery');
                            if(!empty($slots)){
                                $slotData['date']  =  $slotDate;
                                $slotData['slots'] = $slots;
                                $slotWithDate[] = $slotData;
                            }
                        }
                        $vendor->date_with_slots = $slotWithDate;
                    }
                }else{
                    $vendor->delaySlot = 0;
                    $vendor->closed_store_order_scheduled = 0;
                }


                $vendor->is_show_category = ($vendor->vendor_templete_id == 2 || $vendor->vendor_templete_id == 4) ? 1 : 0;

                $vendorCategories = VendorCategory::with('category.translation_one')->where('vendor_id', $vendor->id)->where('status', 1)->get();
                $categoriesList = '';
                foreach ($vendorCategories as $key => $category) {
                    if ($category->category) {
                        $cat_name = isset($category->category->translation_one) ? $category->category->translation_one->name : $category->category->slug;
                        $categoriesList = $categoriesList . $cat_name ?? '';
                        if ($key !=  $vendorCategories->count() - 1) {
                            $categoriesList = $categoriesList . ', ';
                        }
                    }
                }
                $vendor->categoriesList = $categoriesList;

                $vends[] = $vendor->id;
                if (($preferences) && ($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {
                    $vendor = $this->getVendorDistanceWithTime($latitude, $longitude, $vendor, $preferences, $type);
                }

            }
            //filter vendor
            if($venderFilterClose && ($venderFilterClose == 1) ){
                $vendorData =   $vendorData->where('is_vendor_closed',1)->values();
            }
            if($venderFilterOpen && ($venderFilterOpen == 1) ){
                $vendorData =   $vendorData->where('is_vendor_closed',0)->values();
            }

            $cid = $request->category_id;
            $categories = Category::with([
                'tags', 'type'  => function ($q) {
                    $q->select('id', 'title as redirect_to');
                },
                'childs.translation'  => function ($q) use ($langId) {
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                        ->where('category_translations.language_id', $langId);
                },
                'translation' => function ($q) use ($langId) {
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                        ->where('category_translations.language_id', $langId);
                }
            ])
            ->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products','sub_cat_banners')
            ->where('id', $cid)->first();//->toArray();
            
            // print_r($categories);die;
            $homeData['vendors'] = $vendorData;
            $homeData['categories'] = $categories->childs;
            $homeData['mobile_banners'] = $categories->sub_cat_banners;
            $homeData['reqData'] = $request->all();

            $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
            $homeData['is_admin'] = $user_vendor_count > 0 ? 1 : 0;
            return $this->successResponse($homeData);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    //git user registration document
     public function UserRegistrationDocument(){
        $user = Auth::user();
        $langId = $user->language;
        //$user_registration_documents = UserRegistrationDocuments::with(['primary'])->get();
        if( $langId){
            $user_registration_documents = UserRegistrationDocuments::with(['translations' => function ($q) use ($langId) {
                $q->where('language_id', $langId);
            }])->get();

        }
        return $this->successResponse($user_registration_documents);
     }

    public function getEditedOrders(Request $request){
        // Get user Edited Orders from Temp Cart
        $user = Auth::user();
        $temp_orders = array();
        if($user){
            $temp_order_vendors = TempCart::where('status', '0')->where('user_id', $user->id)->where('is_submitted', 1)->where('is_approved', 0)->pluck('order_vendor_id');
            $temp_orders = Order::with(['vendors'=> function($q){
                $q->select('order_id','vendor_id', 'dispatch_traking_url');
            }])->whereHas('vendors', function($q) use($temp_order_vendors){
                $q->whereIn('id', $temp_order_vendors);
            })
            ->select('id','order_number')
            ->get();
        }
        

        return $this->successResponse($temp_orders, '', 200);
    }

    public function vendorProducts($venderIds, $langId, $currency = '', $where = '', $type)
    {
        $products = Product::byProductCategoryServiceType($type)->with([
            'category.categoryDetail.translation' => function ($q) use ($langId) {
                $q->where('category_translations.language_id', $langId);
            },
            'vendor' => function ($q) use ($type) {
                $q->where($type, 1);
            },
            'media' => function ($q) {
                $q->groupBy('product_id');
            }, 'media.image',
            'translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
            },
            'variant' => function ($q) use ($langId) {
                $q->select('sku', 'product_id', 'quantity', 'price','markup_price', 'barcode');
                $q->groupBy('product_id');
            },
        ])
            ->whereHas('category.categoryDetail', function ($q) {
                $q->whereNull('categories.deleted_at');
            })
            ->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only');
        if ($where !== '') {
            $products = $products->where($where, 1);
        }
        $pndCategories = Category::where('type_id', 7)->pluck('id');
        if (is_array($venderIds)) {
            $products = $products->whereIn('vendor_id', $venderIds);
        }
        if ($pndCategories) {
            $products = $products->whereNotIn('category_id', $pndCategories);
        }
        $products = $products->whereNotNull('category_id')->where('is_live', 1)->take(10)->inRandomOrder()->get();
        if (!empty($products)) {
            foreach ($products as $key => $value) {
                foreach ($value->variant as $k => $v) {
                    $value->variant[$k]->multiplier = $currency ? $currency->doller_compare : 1;
                }
            }
        }
        return $products;
    }

    /** return product meta data for new products, featured products, onsale products     */
    public function productList($venderIds, $langId = 1, $currency = 147, $where = '')
    {
        $clientCurrency = ClientCurrency::where('currency_id', $currency)->first();
        $products = Product::with([
            'media' => function ($q) {
                $q->groupBy('product_id');
            }, 'media.image',
            'translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
            },
            'variant' => function ($q) use ($langId) {
                $q->select('sku', 'product_id', 'quantity', 'price', 'markup_price','barcode');
                $q->groupBy('product_id');
            },
        ])->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating')
            ->where('is_live', 1);
        if ($where !== '') {
            $products = $products->where($where, 1);
        }
        if (is_array($venderIds) && count($venderIds) > 0) {
            $products = $products->whereIn('vendor_id', $venderIds);
        }
        $products = $products->get();
        if (!empty($products)) {
            foreach ($products as $key => $value) {
                foreach ($value->variant as $k => $v) {
                    $value->variant[$k]->multiplier = $clientCurrency->doller_compare;
                }
            }
        }
        return $products;
    }

    public function globalSearch(Request $request, $for = 'all', $dataId = 0)
    {
       // return 1;
        try {
            $keyword = $request->keyword;
            $langId = Auth::user()->language;
            $curId = Auth::user()->language;
            $limit = $request->has('limit') ? $request->limit : 10;
            $page = $request->has('page') ? $request->page : 1;
            $action = $request->has('type') && $request->type ? $request->type : null;
           // $types = ['delivery', "dine_in", "takeaway"];
            $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'slots_with_service_area')->first();
            $latitude = $request->latitude;
            $longitude = $request->longitude;


            // if (!in_array($action, $types)) {
            //     return response()->json(['error' => 'Type is incorrect.'], 404);
            // }
            $allowed_vendors = $this->getServiceAreaVendors($latitude, $longitude, $action);

            $response = array();
            if ($for == 'all') {
                $categories = Category::join('category_translations as cts', 'categories.id', 'cts.category_id')
                    ->leftjoin('types', 'types.id', 'categories.type_id')
                    ->select('categories.id', 'categories.icon', 'categories.image', 'categories.slug', 'categories.parent_id', 'cts.name', 'categories.warning_page_id', 'categories.template_type_id', 'types.title as redirect_to')
                    ->where('categories.id', '>', '1')
                    ->where('categories.is_visible', 1)
                    ->where('categories.status', '!=', 2)
                    ->where('categories.is_core', 1)
                    ->where('cts.language_id', $langId)
                    ->where(function ($q) use ($keyword) {
                        $q->where('cts.name', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('categories.slug', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('cts.trans-slug', 'LIKE', '%' . $keyword . '%');
                    })->orderBy('categories.parent_id', 'asc')
                    ->orderBy('categories.position', 'asc')
                    ->groupBy('cts.category_id')->paginate($limit, $page);
                foreach ($categories as $category) {
                    $category->response_type = 'category';
                    $category->image_url = $category->image['proxy_url'] . '80/80' . $category->image['image_path'];
                    $response[] = $category;
                }

                $brands = Brand::join('brand_translations as bt', 'bt.brand_id', 'brands.id')
                    ->select('brands.id', 'bt.title  as dataname', 'image')
                    ->where('bt.title', 'LIKE', '%' . $keyword . '%')
                    ->where('brands.status', '!=', '2')
                    ->where('bt.language_id', $langId)
                    ->orderBy('brands.position', 'asc')->paginate($limit, $page);
                foreach ($brands as $brand) {
                    $brand->response_type = 'brand';
                    $brand->image_url = $brand->image['proxy_url'] . '80/80' . $brand->image['image_path'];
                    $response[] = $brand;
                }
                $categoryTypes = getServiceTypesCategory($action);
                $vendors = Vendor::vendorOnline()->whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                    $q->whereIn('type_id',$categoryTypes);
                })->select('id', 'name  as dataname', 'logo', 'slug', 'address', 'show_slot')->where($action, 1);
                if (($preferences) && ($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {

                    if (!empty($latitude) && !empty($longitude)) {
                        $vendors = $vendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                            $query->select('vendor_id')
                        ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(".$latitude." ".$longitude.")'))");
                        });

                        if (isset($preferences->slots_with_service_area) && ($preferences->slots_with_service_area == 1)) {
                            $slot_vendors = clone $vendors;
                            $data = $slot_vendors->get();
                            foreach ($data as $key => $value) {
                                $vendors = $vendors->when(($value->show_slot == 0), function($query) use ($latitude, $longitude) {
                                    return $query->where(function($query1) use ($latitude, $longitude) {
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
                    $q->where('name', 'LIKE', '%'. $keyword .'%')->orWhere('address', 'LIKE', '%' . $keyword . '%');
                })->where('status', 1)->paginate($limit, $page);


                foreach ($vendors as $vendor) {
                    $vendor->response_type = 'vendor';
                    $vendor->image_url = $vendor->logo['proxy_url'] . '80/80' . $vendor->logo['image_path'];
                    $response[] = $vendor;
                }
                // $vendors  = Vendor::select('id', 'name  as dataname', 'address')->where(function ($q) use ($keyword) {
                //         $q->where('name', ' LIKE', '%' . $keyword . '%')->orWhere('address', 'LIKE', '%' . $keyword . '%');
                //     })->where('vendors.status', '!=', '2')->get();
                // foreach ($vendors as $vendor) {
                //     $vendor->response_type = 'vendor';
                //     // $response[] = $vendor;
                // }
               // pr($vendorids);
                $products = Product::byProductCategoryServiceType($action)->with(['category.categoryDetail.translation' => function ($q) use ($langId) {
                    $q->where('category_translations.language_id', $langId);
                }, 'media'])->join('product_translations as pt', 'pt.product_id', 'products.id')
                    ->select('products.id', 'products.sku', 'pt.title  as dataname', 'pt.body_html', 'pt.meta_title', 'pt.meta_keyword', 'pt.meta_description')
                    ->where('pt.language_id', $langId)
                    ->whereHas('vendor', function ($query) use ($action) {
                        $query->where($action, 1);
                    })

                    ->where(function ($q) use ($keyword) {
                        $q->where('products.sku', ' LIKE', '%' . $keyword . '%')->orWhere('products.url_slug', 'LIKE', '%' . $keyword . '%')->orWhere('pt.title', 'LIKE', '%' . $keyword . '%');
                    })->where('products.is_live', 1)->whereNull('deleted_at')->groupBy('products.id')
                    ->whereIn('vendor_id', $allowed_vendors)
                    ->paginate($limit, $page);
                foreach ($products as $product) {
                    $product->response_type = 'product';
                    $product->image_url = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                    $response[] = $product;
                }
                return $this->successResponse($response);
            } else {
                $products = Product::byProductCategoryServiceType($action)->join('product_translations as pt', 'pt.product_id', 'products.id')
                    ->select('products.id', 'products.sku', 'pt.title', 'pt.body_html', 'pt.meta_title', 'pt.meta_keyword', 'pt.meta_description')
                    ->where('pt.language_id', $langId)
                    ->whereHas('vendor', function ($query) use ($action) {
                        $query->where($action, 1);
                    })
                    ->where(function ($q) use ($keyword) {
                        $q->where('products.sku', ' LIKE', '%' . $keyword . '%')
                            ->orWhere('products.url_slug', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('pt.title', 'LIKE', '%' . $keyword . '%');
                            // ->orWhere('pt.body_html', 'LIKE', '%' . $keyword . '%')
                            // ->orWhere('pt.meta_title', 'LIKE', '%' . $keyword . '%')
                            // ->orWhere('pt.meta_keyword', 'LIKE', '%' . $keyword . '%')
                            // ->orWhere('pt.meta_description', 'LIKE', '%' . $keyword . '%');
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
                foreach ($products as $product) {
                    $product->response_type = 'product';
                    $response[] = $product;
                }
            }
            return $this->successResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function contactUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required',
            'email' => 'required',
            'message' => 'required'
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                return $this->errorResponse($error_value[0], 400);
            }
        }
        $client = Client::select('id', 'name', 'email', 'phone_number','contact_email', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
        $superAdmin = User::where('is_superadmin', 1)->first();
        if ($superAdmin) {
            try {
                if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
                    $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
                } else {
                    return $this->errorResponse('We are sorry for inconvenience. Please contact us later', 400);
                }
                $mail_from = $request->email;
                $sendto = $client->contact_email ? $client->contact_email : $superAdmin->email;
                $customer_name = $request->name;
                $data = [
                    'logo' => $client->logo['original'],
                    'superadmin_name' => $superAdmin->name,
                    'customer_name' => $customer_name,
                    'customer_email' => $mail_from,
                    'customer_phone_number' => $request->phone_number,
                    'customer_message' => $request->message,
                ];
                Mail::send(
                    'email.contactUs',
                    ['mailData' => $data],
                    function ($message) use ($sendto, $customer_name, $mail_from) {
                        $message->from($mail_from, $customer_name);
                        $message->to($sendto)->subject('Customer Request for Contact');
                    }
                );
                return $this->successResponse('', 'Thank you for contacting us. We will get to you shortly');
            } catch (\Exception $e) {
                return $this->errorResponse($e->getMessage(), $e->getCode());
            }
        } else {
            return $this->errorResponse('We are sorry for inconvenience. Please contact us later', 400);
        }
    }
    public function setMailDetail($mail_driver, $mail_host, $mail_port, $mail_username, $mail_password, $mail_encryption)
    {
        $config = array(
            'driver' => $mail_driver,
            'host' => $mail_host,
            'port' => $mail_port,
            'encryption' => $mail_encryption,
            'username' => $mail_username,
            'password' => $mail_password,
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
        );

        Config::set('mail', $config);
        $app = App::getInstance();
        $app->register('Illuminate\Mail\MailServiceProvider');
        return '1';

        // return '2';
    }
}
