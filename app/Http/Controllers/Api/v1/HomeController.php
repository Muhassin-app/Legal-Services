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
use App\Http\Traits\{ApiResponser,ProductActionTrait,VendorTrait,PaymentTrait};
use App\Http\Traits\HomePage\HomePageTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\UserRegistrationDocuments;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{UserVendorWishlist,User, MobileBanner, Category, Brand, Client, ClientPreference, Cms, Order, Banner, Vendor, VendorCategory, Category_translation, ClientLanguage, PaymentOption, Product, Country, Currency, ServiceArea, ClientCurrency, ProductCategory, BrandTranslation, Celebrity, UserVendor, AppStyling, Nomenclature, AppDynamicTutorial,ClientSlot, TempCart, VerificationOption, ShowSubscriptionPlanOnSignup, ClientCountries};
use DateTime;
use DateInterval;
use DateTimeZone;

class HomeController extends BaseController
{
    use ApiResponser,ProductActionTrait, HomePageTrait,VendorTrait,PaymentTrait;

    private $curLang = 0;
    private $field_status = 2;

    /** Return header data, client profile and configure data */
    public function headerContent(Request $request)
    {
        try {
            $homeData = array();
            $client_language = ClientLanguage::select('language_id')->where(['is_primary' => 1, 'is_active' => 1])->first();
            $clientPreferences = ClientPreference::first();

            $langId = ($request->hasHeader('language') && !empty($request->header('language'))) ? $request->header('language') : (($client_language) ? $client_language->language_id : 1);
            $homeData['profile'] = $preferences = Client::with(['preferences', 'country:id,name,code,phonecode'])->select('id','country_id', 'company_name', 'code', 'sub_domain','database_name', 'logo','dark_logo', 'company_address', 'phone_number', 'email','custom_domain','contact_phone_number','socket_url')->first();
            //dd(Client::with('getPreference')->first()->getPreference->auto_implement_5_percent_tip);
            $app_styling_detail = AppStyling::getSelectedData();
            \Session::put('customerLanguage',$langId);
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
                        $vendorData['name'] = getNomenclatureName($vendor_typ_value, true);
                        $iconFiledName = config('constants.VendorTypesIcon.'.$vendor_typ_key);
                        $vendorData["icon"] = $clientPreferences->$iconFiledName ? $clientPreferences->$iconFiledName : asset('images/al_custom3.png');
                        //$vendorData["name"] = $clientVendorTypes;
                        //$client_preference_detail->$iconFiledName['proxy_url'].'36/26'.$client_preference_detail-> $iconFiledName['image_path']
                        //$vendorData["name"] = $clientVendorTypes;
                        $vendorData["type"] = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;


                        $vendorMode[] = $vendorData;
                    }
            }
            $getAdditionalPreference = getAdditionalPreference(['advance_booking_amount', 'advance_booking_amount_percentage','update_order_product_price','is_one_push_book_enable', 'is_bid_ride_enable','is_service_product_price_from_dispatch','is_postpay_enable','is_order_edit_enable','is_bid_enable','is_file_cart_instructions','is_cab_pooling','chat_button','call_button','add_to_cart_btn','is_user_kyc_for_registration','seller_sold_title','seller_platform_logo','is_service_price_selection','is_particular_driver','is_enable_curb_side','is_enable_variant_set_v2','is_share_ride_users','is_recurring_booking','is_rental_weekly_monthly_price','is_enable_allergic_items','is_user_pre_signup','vendor_online_status','distance_matrix_app_status','fire_base_type']);

            //mohit sir branch code updated by sohail farm meat
            $homeData['profile']->preferences->vendorMode = $vendorMode;

            $homeData['profile']->preferences->is_cab_pooling = (int) $getAdditionalPreference['is_cab_pooling'];
            $homeData['profile']->preferences->distance_matrix_app_status = (int) $getAdditionalPreference['distance_matrix_app_status'] ?? "";
            $homeData['profile']->preferences->chat_button = (int) $getAdditionalPreference['chat_button'];
            $homeData['profile']->preferences->call_button = (int) $getAdditionalPreference['call_button'];
            $homeData['profile']->preferences->add_to_cart_btn = (int) $getAdditionalPreference['add_to_cart_btn'];
            $homeData['profile']->preferences->is_enable_curb_side = (int) $getAdditionalPreference['is_enable_curb_side'];
            $homeData['profile']->preferences->is_user_kyc_for_registration = (int) $getAdditionalPreference['is_user_kyc_for_registration'];
            $homeData['profile']->preferences->rating_check = $preferences->preferences->rating_check;

            $homeData['profile']->preferences->advance_booking_amount = 0;
            $homeData['profile']->preferences->advance_booking_amount_percentage = 0;
            if(!empty($getAdditionalPreference['advance_booking_amount']) && !empty($getAdditionalPreference['advance_booking_amount_percentage']) && ($getAdditionalPreference['advance_booking_amount_percentage'] > 0) && ($getAdditionalPreference['advance_booking_amount_percentage'] < 101) ){
                $homeData['profile']->preferences->advance_booking_amount = ($getAdditionalPreference['advance_booking_amount'] == 1)? true : false;
                $homeData['profile']->preferences->advance_booking_amount_percentage = $getAdditionalPreference['advance_booking_amount_percentage'];
            }
            //till here

            $homeData['profile']->preferences->update_order_product_price = (!empty($getAdditionalPreference['update_order_product_price']) && $getAdditionalPreference['update_order_product_price'] == 1)? true : false;
            $homeData['profile']->preferences->is_one_push_book_enable    = (int) $getAdditionalPreference['is_one_push_book_enable'];
            $homeData['profile']->preferences->is_bid_ride_enable         = (int) $getAdditionalPreference['is_bid_ride_enable'];
            $homeData['profile']->preferences->is_particular_driver       = (int) $getAdditionalPreference['is_particular_driver'];
            $homeData['profile']->preferences->is_enable_allergic_items       = (int) $getAdditionalPreference['is_enable_allergic_items'];
            $homeData['profile']->preferences->vendor_online_status       = (($getAdditionalPreference['vendor_online_status'])?1:0);

            // on demand service is_share_ride_users
            $homeData['profile']->preferences->is_share_ride_users       = (int) $getAdditionalPreference['is_share_ride_users'];
            $homeData['profile']->preferences->is_recurring_booking       = (int) $getAdditionalPreference['is_recurring_booking'];
            $homeData['profile']->preferences->is_user_pre_signup       = (int) $getAdditionalPreference['is_user_pre_signup'];
            // on demand service pricing
            $homeData['profile']->preferences->is_service_product_price_from_dispatch   = (int) $getAdditionalPreference['is_service_product_price_from_dispatch'];
            $homeData['profile']->preferences->is_service_price_selection               = (int) $getAdditionalPreference['is_service_price_selection'];

            if($homeData['profile']->preferences->is_one_push_book_enable == 1){
                $homeData['profile']->preferences->pick_drop_instant_booking_vendor = Vendor::select('id', 'slug', 'name', 'is_vendor_instant_booking', 'status')
                                                                                      ->with(['products' => function ($v) {
                                                                                        $v->where('is_product_instant_booking', 1)->first();
                                                                                    }])->where('status', 1)->where('is_vendor_instant_booking', 1)->first();
            }

            $delivery_nomenclature = $this->getNomenclatureName('Delivery', $langId, false);
            $dinein_nomenclature = $this->getNomenclatureName('Dine-In', $langId, false);
            $takeaway_nomenclature = $this->getNomenclatureName('Takeaway', $langId, false);
            $search_nomenclature = $this->getNomenclatureName('Search', $langId, false);
            $vendors_nomenclature = $this->getNomenclatureName('Vendors', $langId, false);
            $sellers_nomenclature = $this->getNomenclatureName('sellers', $langId, false);

            $account_name = $this->getNomenclatureName('Account Name', $langId, false);
            $bank_name = $this->getNomenclatureName('Bank Name', $langId, false);
            $account_number = $this->getNomenclatureName('Account Number', $langId, false);
            $ifsc_code = $this->getNomenclatureName('IFSC Code', $langId, false);
            $aadhaar_front = $this->getNomenclatureName('Aadhaar Front', $langId, false);
            $aadhaar_back = $this->getNomenclatureName('Aadhaar Back', $langId, false);
            $aadhaar_number = $this->getNomenclatureName('Aadhaar Number', $langId, false);
            $upi_id = $this->getNomenclatureName('UPI Id', $langId, false);

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
            $homeData['profile']->preferences->sellers_nomenclature = $sellers_nomenclature;
            $homeData['profile']->preferences->fixed_fee_nomenclature = $fixed_fee_nomenclature;
            $homeData['profile']->preferences->want_to_tip_nomenclature = $want_to_tip;
            $homeData['profile']->preferences->referral_code = $referral_code;

            $homeData['profile']->preferences->seller_sold_title = (int) $getAdditionalPreference['seller_sold_title'];
            $homeData['profile']->preferences->seller_platform_logo = (int) $getAdditionalPreference['seller_platform_logo'];
            $homeData['profile']->preferences->account_name = $account_name;
            $homeData['profile']->preferences->bank_name = $bank_name;
            $homeData['profile']->preferences->account_number = $account_number;
            $homeData['profile']->preferences->ifsc_code = $ifsc_code;
            $homeData['profile']->preferences->aadhaar_front = $aadhaar_front;
            $homeData['profile']->preferences->aadhaar_back = $aadhaar_back;
            $homeData['profile']->preferences->aadhaar_number = $aadhaar_number;
            $homeData['profile']->preferences->upi_id = $upi_id;
            $homeData['profile']->preferences->is_hourly_pickup_rental = $clientPreferences->is_hourly_pickup_rental;

            if(!is_null($passbase))
            {
                $homeData['profile']->preferences->passbase_check = 1;
                $passbase_creds = json_decode($passbase->credentials);
                $homeData['profile']->preferences->passbase_api_key = $passbase_creds->publish_key;
            }else{
                $homeData['profile']->preferences->passbase_check = 0;
            }

            $homeData['parent_category'] = Category::with('translation_one','type')->where('id', '>', '1')->where('is_core', 1)->where('parent_id', 1)->where('is_visible', 1)->orderBy('position', 'asc')->where('deleted_at', NULL)->where('status', 1)->pluck('id', 'slug')->toArray();

            $homeData['countries'] = ClientCountries::with('country')->where('is_active', 1)->orderBy('is_primary', 'desc')->get()->map(function ($item) {
                return [
                    'country_id' => $item->country_id,
                    'is_primary' => $item->is_primary,
                    'country' => $item->country->only(['id', 'code', 'nicename', 'iso3'])
                    + ['flag' => 'https://flagcdn.com/56x42/' . strtolower($item->country->code) . '.png'],
                ];
            });

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
            $mobile_banners = MobileBanner::select("id", "name", "description", "image", "link", 'redirect_category_id', 'redirect_vendor_id', 'link_url')
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

            $payment_codes = $this->paymentOptionArray('homepage');
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

            // Send Primary Language And Primary Currency By Lattitude and Longitude
            $primary_currencies = new \stdClass();
            $primary_language = new \stdClass();
            $primary_country = new \stdClass();
            if ($request->has(['latitude', 'longitude'])) {
                $service_area = ServiceArea::select('service_areas.primary_language', 'service_areas.country_code', 'service_areas.primary_currency', 'languages.name as language_name', 'languages.sort_code', 'languages.nativeName', 'currencies.name as currency_name', 'currencies.id as country_id', 'currencies.symbol', 'currencies.iso_code', 'countries.name', 'countries.nicename', 'countries.iso3')
                ->join('languages', 'service_areas.primary_language', '=', 'languages.id')
                ->join('currencies', 'service_areas.primary_currency', '=', 'currencies.id')
                ->join('countries', 'service_areas.country_code', '=', 'countries.code')
                ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $request->latitude . " " . $request->longitude . ")'))")
                ->first();

                if ($service_area) {
                    $primary_language = (object) [
                        'language_id' => $service_area->primary_language,
                        'is_primary' => 0,
                        'language' => (object) [
                            'id' => $service_area->primary_language,
                            'name' => $service_area->language_name,
                            'sort_code' => $service_area->sort_code,
                            'nativeName' => $service_area->nativeName,
                            'country_code' => $service_area->country_code,
                        ],
                    ];

                    $primary_currencies = (object) [
                        'currency_id' => $service_area->primary_currency,
                        'is_primary' => 0,
                        'currency' => (object) [
                            'id' => $service_area->primary_currency,
                            'name' => $service_area->currency_name,
                            'iso_code' => $service_area->iso_code,
                            'symbol' => $service_area->symbol,
                        ],
                    ];

                    $primary_country = (object) [
                        'country_id' => $service_area->country_id,
                        'is_primary' => 0,
                        'country' => (object) [
                            'id' => $service_area->country_id,
                            'name' => $service_area->nicename,
                            'iso3' => $service_area->iso3,
                            'symbol' => $service_area->country_code,
                            'flag' => 'https://flagcdn.com/56x42/' . strtolower($service_area->country_code) . '.png',
                        ],
                    ];
                }
                $homeData['primary_currencies'] = $primary_currencies;
                $homeData['primary_language'] = $primary_language;
                $homeData['primary_country'] = $primary_country;
            }
            if(empty((array)$primary_currencies)){
                $homeData['primary_currencies'] = ClientCurrency::with('currency')->select('currency_id', 'is_primary', 'doller_compare')->where('is_primary',1)->orderBy('is_primary', 'desc')->first();
            }

            if (isset($homeData['profile']->custom_domain) && !empty($homeData['profile']->custom_domain) && $homeData['profile']->custom_domain != $homeData['profile']->sub_domain)
                $domain_link = "https://" . $homeData['profile']->custom_domain;
            else
                $domain_link = "https://" . $homeData['profile']->sub_domain . env('SUBMAINDOMAIN');
            $homeData['domain_link'] = $domain_link;

            $homeData['profile']->preferences->static_otp =  (getUserToken($preferences->preferences)['status'])?false:true;
            $homeData['profile']->preferences->is_postpay_enable    = (int) $getAdditionalPreference['is_postpay_enable'];
            $homeData['profile']->preferences->is_order_edit_enable = (int) $getAdditionalPreference['is_order_edit_enable'];
            $homeData['profile']->preferences->is_bid_enable        = (int) $getAdditionalPreference['is_bid_enable'];
            $homeData['profile']->preferences->is_file_cart_instructions = (int) $getAdditionalPreference['is_file_cart_instructions'];
            $homeData['profile']->preferences->is_rental_weekly_monthly_price = (int) $getAdditionalPreference['is_rental_weekly_monthly_price'];
            $homeData['profile']->preferences->fire_base_type = $getAdditionalPreference['fire_base_type'] ?? "";

            return $this->successResponse($homeData);
        } catch (\Exception $e) {
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
            $spotlight_products=[];
            $user = Auth::user();
            $langId = $user->language;
            $currency_id = $user->currency;
            $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'is_service_area_for_banners','subscription_mode')->first();
            $latitude = !empty($request->latitude) ? ($request->latitude ?? $user->latitude ) :  $preferences->Default_latitude ;
            $longitude =!empty($request->longitude) ? ($request->longitude ?? $user->longitude ) :  $preferences->Default_longitude ;
            $paginate = $request->has('limit') ? $request->limit : 12;
            $distance_to_time_multiplier = $preferences->distance_to_time_multiplier??2;
            //filter
            $venderFilterClose   = $request->has('close_vendor') && $request->close_vendor ? $request->close_vendor : null;
            $venderFilterOpen   = $request->has('open_vendor') && $request->open_vendor ? $request->open_vendor : null;
            $venderFilterbest   = $request->has('best_vendor') && $request->best_vendor ? $request->best_vendor : null;
            $venderFilternear   = $request->has('near_me') && $request->near_me ? $request->near_me : null;
            $spotlight= $request->has('is_spotlight') && $request->is_spotlight ? $request->is_spotlight : null;

            $type = $request->has('type') ? $request->type : 'delivery';

            if (empty($type))
            $type = 'delivery';


            $categoryTypes = getServiceTypesCategory($type);


            $vendorData = Vendor::byVendorSubscriptionRule($preferences)->whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                $q->whereIn('type_id',$categoryTypes);
            })->select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude', 'closed_store_order_scheduled')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($type, 1);



            $ses_vendors = $this->getServiceAreaVendors($latitude, $longitude, $type);

            $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
            $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {

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

            $featuredVendor= clone $vendorData;
            $featuredVendor = $featuredVendor->where('is_featured',1)->get();
            $popularVendor = clone $vendorData;
            $popularVendor = $popularVendor->withCount('orderProducts')->orderBy('order_products_count','desc')->get();

            $allVendorData = clone $vendorData;
            $vendorData = $vendorData->with('slot', 'slotDate')->where('status', 1)->limit(100)->get();
            $venderIds  = $allVendorData->with('slot', 'slotDate')->where('status', 1)->pluck('id');


            $timezone = $user->timezone ?? 'Asia/Kolkata';
            $start_date = new DateTime("now", new  DateTimeZone($timezone) );
            $start_date =  $start_date->format('Y-m-d');
            $end_date = Date('Y-m-d', strtotime('+13 days'));


            foreach ($vendorData as $vendor) {
                $vendor->vendorrating = $this->vendorRatings($vendor->products);
                $vendor->vendorNoOfRatings = $this->vendorNoOfRatings($vendor->products);
                $vendor->promocodes = $this->getVendorWisePromoCodes($vendor->id);
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
                    $slotsDate = findSlot('',$vendor->id,$type );
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

                $vendorCategories = VendorCategory::with('category.translation_one')
                ->where('vendor_id', $vendor->id)
                ->where('status', 1)
                ->groupBy('category_id')
                ->get();
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


            $on_sale_product_details = $this->vendorProducts($vends, $langId, $clientCurrency, '', $type,$latitude,$longitude,$preferences);
            $new_product_details    = $this->vendorProducts($vends, $langId, $clientCurrency, 'is_new', $type,$latitude,$longitude,$preferences);
            $feature_product_details = $this->vendorProducts($vends, $langId, $clientCurrency, 'is_featured', $type,$latitude,$longitude,$preferences);
            if($spotlight && ($spotlight == 1) ){
                $spotlight_products=$this->getSpotlightProducts();
            }

            $isVendorArea = 0;

            // Start Mobile Banners
            $mobile_banners = MobileBanner::select("id", "name", "description", "image", "link", 'redirect_category_id', 'redirect_vendor_id', 'link_url')
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
            $homeData['mobile_banners'] = $mobile_banners;
            $homeData['on_sale_products'] = $on_sale_product_details;
            $homeData['new_products'] = $new_product_details;
            $homeData['featured_products'] = $feature_product_details;
            $homeData['spotlight_deals']=$spotlight_products;

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
            // long term service
            $long_term_service_products =[];
            $additionalPreference = getAdditionalPreference(['is_long_term_service', 'is_token_currency_enable']);
            if($additionalPreference['is_long_term_service'] == 1){
                $requestFrom='app';
                $long_term_service_products = $this->longTermServiceProducts($ses_vendors, $additionalPreference, $langId, $clientCurrency,'', $type,'', $requestFrom);
            }
            $homeData['long_term_service'] = $long_term_service_products;
            $homeData['popular_restaurents'] = $popularVendor;
            $homeData['featured_restaurents'] = $featuredVendor;

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
            $spotlight_products=[];
            $user = Auth::user();
            $langId = $user->language;
            $currency_id = $user->currency;
            $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'is_service_area_for_banners','subscription_mode')->first();
            $latitude = !empty($request->latitude) ? ($request->latitude ?? $user->latitude ) :  $preferences->Default_latitude ;
            $longitude =!empty($request->longitude) ? ($request->longitude ?? $user->longitude ) :  $preferences->Default_longitude ;
            $paginate = $request->has('limit') ? $request->limit : 12;
            //filter
            $venderFilterClose   = $request->has('close_vendor') && $request->close_vendor ? $request->close_vendor : null;
            $venderFilterOpen   = $request->has('open_vendor') && $request->open_vendor ? $request->open_vendor : null;
            $venderFilterbest   = $request->has('best_vendor') && $request->best_vendor ? $request->best_vendor : null;
            $venderFilternear   = $request->has('near_me') && $request->near_me ? $request->near_me : null;
            $spotlight= $request->has('is_spotlight') && $request->is_spotlight ? $request->is_spotlight : null;


            $type = $request->has('type') ? $request->type : 'delivery';
            $cid = $request->category_id;
            if (empty($type))
            $type = 'delivery';

            $categoryTypes = getServiceTypesCategory($type);

            $vendorData = Vendor::byVendorSubscriptionRule($preferences)->whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                $q->whereIn('type_id',$categoryTypes);
            })->select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude', 'closed_store_order_scheduled')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($type, 1);

            $ses_vendors = $this->getServiceAreaVendors($latitude, $longitude, $type);

            $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
            $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {

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
                $vendorData = $vendorData->orderBy('product_avg_average_rating', 'desc');
            }

            $allVendorData = clone $vendorData;
            $vendorData = $vendorData->with('slot', 'slotDate')->where('status', 1);

            $timezone = $user->timezone ?? 'Asia/Kolkata';
            $start_date = new DateTime("now", new  DateTimeZone($timezone) );
            $start_date =  $start_date->format('Y-m-d');
            $end_date = Date('Y-m-d', strtotime('+13 days'));


            foreach ($vendorData as $vendor) {
                $vendor->vendorrating = $this->vendorRatings($vendor->products);
                $vendor->vendorNoOfRatings = $this->vendorNoOfRatings($vendor->products);
                $vendor->promocodes = $this->getVendorWisePromoCodes($vendor->id);
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
                    $slotsDate = findSlot('',$vendor->id,$type );
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
            ->where('id', $cid)->first();

            $childCatIds = $categories->childs->pluck('id');
            $brands = Brand::with(['bc.categoryDetail', 'bc.categoryDetail.translation' =>  function ($q) use ($langId) {
                $q->select('category_translations.name', 'category_translations.category_id', 'category_translations.language_id')->where('category_translations.language_id', $langId);
            }, 'translation' => function ($q) use ($langId) {
                $q->select('title', 'brand_id', 'language_id')->where('language_id', $langId);
            }])
                ->whereHas('bc.categoryDetail', function ($q) use ($childCatIds) {
                    $q->where('categories.status', 1)->whereIn('categories.id', $childCatIds);
                })
                ->select('id', 'image', 'image_banner')->where('status', 1)->orderBy('position', 'asc')->get();

            $on_sale_product_details = $this->vendorProducts($vends, $langId, $clientCurrency, '', $type,$latitude,$longitude,$preferences, $childCatIds);
            $new_product_details    = $this->vendorProducts($vends, $langId, $clientCurrency, 'is_new', $type,$latitude,$longitude,$preferences, $childCatIds);
            $feature_product_details = $this->vendorProducts($vends, $langId, $clientCurrency, 'is_featured', $type,$latitude,$longitude,$preferences, $childCatIds);

            // Start Mobile Banners
            $mobile_banners = MobileBanner::select("id", "name", "description", "image", "link", 'redirect_category_id', 'redirect_vendor_id', 'link_url')
            ->where('status', 1)->where('validity_on', 1)
            ->with(['category:id,type_id', 'category.type', 'vendor'])
            ->where(function ($q) {
                $q->whereNull('start_date_time')->orWhere(function ($q2) {
                    $q2->whereDate('start_date_time', '<=', Carbon::now())
                        ->whereDate('end_date_time', '>=', Carbon::now());
                });
            })
            ->whereIn('redirect_category_id', $childCatIds);

            if(isset($preferences->is_service_area_for_banners) && ($preferences->is_service_area_for_banners == 1) && ($preferences->is_hyperlocal == 1)){
                if(!empty($latitude) && !empty($longitude)){
                    $mobile_banners = $mobile_banners->whereHas('geos.serviceArea', function($query) use ($latitude, $longitude) {
                        $query->select('id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                    });
                }
            }
            $mobile_banners = $mobile_banners->orderBy('sorting', 'asc')->get();
            // dd($mobile_banners);
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

            $homeData['brands'] = $brands;
            $homeData['vendors'] = $vendorData;

            $homeData['on_sale_products'] = $on_sale_product_details;
            $homeData['new_products'] = $new_product_details;
            $homeData['featured_products'] = $feature_product_details;

            $homeData['categories'] = $categories->childs;
            $homeData['mobile_banners'] = $mobile_banners;
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
            $user_registration_documents = UserRegistrationDocuments::with(['options.translations','translations' => function ($q) use ($langId) {
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

    public function vendorProducts($venderIds, $langId, $currency = '', $where = '', $type,$latitude='',$longitude='',$preferences,$category_id=null,$keyword=null)
    {
        $distance_to_time_multiplier = $preferences->distance_to_time_multiplier??2;
        $user = Auth::user();
        $userid = !empty($user) ? $user->id : 0;
        $products = Product::byProductCategoryServiceType($type)->byProductWhereCheck()->with([
            'category.categoryDetail.translation' => function ($q) use ($langId) {
                $q->where('category_translations.language_id', $langId);
            },
            'vendor' => function ($q) use ($type,$latitude,$longitude,$distance_to_time_multiplier) {
                $q->where($type, 1);
                $q->select('*',DB::Raw("6371 * acos(cos(radians(" . $latitude . "))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians(" . $longitude . "))
                + sin(radians(" .$latitude. "))
                * sin(radians(latitude))) AS dropoffdistance "),
                DB::Raw("6371 * acos(cos(radians(" . $latitude . "))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians(" . $longitude . "))
                + sin(radians(" .$latitude. "))
                * sin(radians(latitude))) * ".$distance_to_time_multiplier." as timeTaken"));
            },
            'inwishlist' => function($qry) use($userid){
                $qry->where('user_id', $userid);
            },
            'media' => function ($q) {
                $q->groupBy('product_id');
            }, 'media.image',
            'translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
            },
            'variant' => function ($q) use ($langId) {
                $q->select('sku', 'product_id', 'quantity', 'price','markup_price', 'barcode','compare_at_price');
                $q->groupBy('product_id');
            },
        ])
            ->whereHas('category.categoryDetail', function ($q) {
                $q->whereNull('categories.deleted_at');
            })
            ->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating', 'inquiry_only','category_id','title','calories','per_hour_price','km_included');
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
        $products = $products->whereNotNull('category_id')->take(10)->inRandomOrder()
        ->where(function($q) use ($category_id,$keyword){
            if(isset($category_id)){
                $q->where('category_id', $category_id);
            }
            if(isset($keyword)){
                $q->where('title', 'like' ,"%$keyword%");
            }
        })
        ->get(); //->where('is_live', 1) set in byProductWhereCheck
        if (!empty($products)) {
            foreach ($products as $key => $value) {
                foreach ($value->variant as $k => $v) {
                    $value->variant[$k]->multiplier = $currency ? $currency->doller_compare : 1;
                }
                if(isset($value->variant) && @$value->variant->first()->compare_at_price > 0){
                    $value->offers = ($value->variant->first()->compare_at_price - $value->variant->first()->price) / $value->variant->first()->compare_at_price * 100;
                }else{
                    $value->offers = 0;
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
                $q->select('sku', 'product_id', 'quantity', 'price', 'markup_price','barcode','compare_at_price');
                $q->groupBy('product_id');
            },
        ])->select('id', 'sku', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating','calories')
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
                if($value->variant->first()->compare_at_price>0){
                    $value->offers = ($value->variant->first()->compare_at_price - $value->variant->first()->price) / $value->variant->first()->compare_at_price * 100;
                }else{
                    $value->offers = 0;
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
            $action = $request->has('type') && $request->type ? $request->type : 'delivery';
           // $types = ['delivery', "dine_in", "takeaway"];
            $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'slots_with_service_area','subscription_mode')->first();
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
                $vendors = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->whereHas('getAllCategory.category',function($q)use ($categoryTypes){
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
                $products = Product::byProductCategoryServiceType($action)->with(['vendor'=>function($q) {
                    $q->select('id','name');
                },'variantSingle' => function ($q) {
                    $q->select('id','title', 'product_id', 'quantity', 'price','markup_price', 'barcode','compare_at_price');
                },'category.categoryDetail.translation' => function ($q) use ($langId) {
                    $q->where('category_translations.language_id', $langId);
                }, 'media'])->join('product_translations as pt', 'pt.product_id', 'products.id')
                    ->select('products.id','products.vendor_id','products.sku', 'pt.title  as dataname', 'pt.body_html', 'pt.meta_title', 'pt.meta_keyword', 'pt.meta_description')
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

                if(($request->search_type)){
                    $response = collect($response)->filter(function ($data) use ($request){
                        return $data->response_type == $request->search_type;
                    })->values();
                }

                return $this->successResponse($response);
            } else {
                $products = Product::byProductCategoryServiceType($action)->with(['vendor'=>function($q) {
                    $q->select('id','name');
                },'variantSingle'=> function ($q) use ($langId) {
                    $q->select('id','title', 'product_id', 'quantity', 'price','markup_price', 'barcode','compare_at_price');
                }])->join('product_translations as pt', 'pt.product_id', 'products.id')
                    ->select('products.id', 'products.sku','products.vendor_id', 'pt.title', 'pt.body_html', 'pt.meta_title', 'pt.meta_keyword', 'pt.meta_description')
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
                    return $this->errorResponse('SMTP not configured.', 400);
                }
                $mail_from = $request->email;
                $sender = $data->mail_from;
                $receiver = $client->contact_email ? $client->contact_email : $superAdmin->email;
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
                    function ($message) use ($receiver, $customer_name, $sender) {
                        $message->from($sender, $customer_name);
                        $message->to($receiver)->subject('Customer Request for Contact');
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

    public function homeRestaurents(Request $request)
    {
        $user = Auth::user();
        $langId = $user->language;
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;

        $preferences = ClientPreference::first();;
        $vendorData = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->with('products')->select('vendors.id', 'name', 'banner','is_show_vendor_details' ,'address', 'order_pre_time', 'order_min_amount', 'logo', 'slug', 'latitude', 'longitude', 'vendor_templete_id');

        $category = Category::with(['tags', 'brands.translation' => function($q) use($langId){
            $q->where('brand_translations.language_id', $langId);
        },
        'type'  => function($q){
            $q->select('id', 'title as redirect_to' ,'service_type' );
        },
        'childs.translation'  => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        },
        'translation' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
            ->where('category_translations.language_id', $langId);
        },
        'allParentsAccount'])
        ->select('id', 'icon', 'image', 'slug', 'type_id', 'can_add_products', 'parent_id', 'sub_cat_banners')
        ->where('slug', 'Restaurant')->firstOrFail();

        $vendorData = $vendorData->withCount('orderProducts')->orderBy('order_products_count');

        $vendors= $this->getServiceAreaVendors();
        $vendorData= $vendorData->whereIn('vendors.id', $vendors);

        $vendorData = $vendorData->whereHas('getAllCategory' , function ($q) use($category){
            $q->where('status', 1)
            ->where('category_id', $category->id);
        });
        if(!empty($vendorType)){
            $vendorData= $vendorData->where($vendorType, 1);
        }
        $vendorData = $vendorData->where('vendors.status', 1)->paginate($limit,$page);

        foreach ($vendorData as $key => $value) {
            $value = $this->getLineOfSightDistanceAndTime($value, $preferences);
            $value->vendorRating = $this->vendorRating($value->products);
            $vendorCategories = VendorCategory::with(['category.translation' => function($q) use($langId){
                $q->where('category_translations.language_id', $langId);
            }])->where('vendor_id', $value->id)->where('status', 1)->get();
            $categoriesList = '';
            foreach ($vendorCategories as $key => $category) {
                if ($category->category) {
                    $categoryName = $category->category->translation->first() ? $category->category->translation->first()->name : '';
                    $categoriesList = $categoriesList . $categoryName;
                    if ($key !=  $vendorCategories->count() - 1) {
                        $categoriesList = $categoriesList . ', ';
                    }
                }
            }
            $value->categoriesList = $categoriesList;
        }

        $homeData['popular_restaurants'] = $vendorData;
        $homeData['all_vendors'] = Vendor::vendorOnline()->get();
        $homeData['featured_restaurants'] = Vendor::vendorOnline()->where('is_featured',1)->get();

        return response()->json([
            'status' => 200,
            'message' => 'Restaurent List',
            'data' => $homeData
        ]);
    }

    public function vendorRating($vendorProducts)
    {
        $vendor_rating = 0;
        if($vendorProducts->isNotEmpty()){
            $product_rating = 0;
            $product_count = 0;
            foreach($vendorProducts as $product){
                if($product->averageRating > 0){
                    $product_rating = $product_rating + $product->averageRating;
                    $product_count++;
                }
            }
            if($product_count > 0){
                $vendor_rating = $product_rating / $product_count;
            }
        }
        return number_format($vendor_rating, 1, '.', '');
    }

    public function categoryRestaurent(Request $request,$category_id)
    {
        $vendors = Vendor::vendorOnline()->whereHas('vendorCategories', function($q) use ($category_id){
            $q->where('category_id', $category_id);
        })->with('vendorCategories')->where(function($q) use ($request){
            if(isset($request->keyword)){
                $q->where('name', $request->keyword);
            }
        })->orWhereHas('products',function($q) use ($request){
            if(isset($request->keyword)){
                $q->where('title', $request->keyword);
            }
        })->get();

        return response()->json([
            'status' => 200,
            'message' => __('Restaurents by category'),
            'data' => $vendors
        ]);
    }

    public function categoryRestaurents(Request $request, $category_id)
    {
        try {
            $vends = [];
            $venderIds = [];
            $homeData = [];
            $user = Auth::user();
            $langId = $user->language;
            $currency_id = $user->currency;
            $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'is_service_area_for_banners','subscription_mode')->first();
            $latitude = !empty($request->latitude) ? ($request->latitude ?? $user->latitude ) :  $preferences->Default_latitude ;
            $longitude =!empty($request->longitude) ? ($request->longitude ?? $user->longitude ) :  $preferences->Default_longitude ;

            $venderFilterClose   = $request->has('close_vendor') && $request->close_vendor ? $request->close_vendor : null;
            $venderFilterOpen   = $request->has('open_vendor') && $request->open_vendor ? $request->open_vendor : null;
            $venderFilterbest   = $request->has('best_vendor') && $request->best_vendor ? $request->best_vendor : null;

            $type = $request->has('type') ? $request->type : 'delivery';

            if (empty($type))
            $type = 'delivery';


            $categoryTypes = getServiceTypesCategory($type);


            $vendorData = Vendor::vendorOnline()->byVendorSubscriptionRule($preferences)->whereHas('getAllCategory.category',function($q)use ($categoryTypes){
                $q->whereIn('type_id',$categoryTypes);
            })->select('id', 'slug', 'name', 'desc', 'banner', 'order_pre_time', 'order_min_amount', 'vendor_templete_id', 'show_slot', 'latitude', 'longitude', 'closed_store_order_scheduled')->withAvg('product', 'averageRating','closed_store_order_scheduled')->where($type, 1);

            $vendorData = $vendorData->whereHas('vendorCategories',function($q) use ($category_id, $request){
                $q->where('category_id', $category_id);
                if(isset($request->keyword)){
                    $q->where('name', 'Like', "%$request->keyword%");
                }
            })->orWhereHas('products',function($q) use ($request){
                if(isset($request->keyword)){
                    $q->where('title', 'Like', "%$request->keyword%");
                }
            });

            $ses_vendors = $this->getServiceAreaVendors($latitude, $longitude, $type);

            $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
            $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;

            if (($preferences) && ($preferences->is_hyperlocal == 1)) {

                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                $vendorData = $vendorData->select('*', DB::raw(' ( ' .$calc_value. ' * acos( cos( radians(' . $latitude . ') ) *
                        cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) +
                        sin( radians(' . $latitude . ') ) *
                        sin( radians( latitude ) ) ) )  AS vendorToUserDistance'))->withAvg('product', 'averageRating');
                $vendorData = $vendorData->whereIn('id', $ses_vendors);

                $vendorData =   $vendorData->orderBy('vendorToUserDistance', 'ASC');
            }

            //filter on ratings
            if($venderFilterbest && ($venderFilterbest == 1) ){
                $vendorData =   $vendorData->orderBy('product_avg_average_rating', 'desc');
            }

            $featuredVendor= clone $vendorData;
            $featuredVendor = $featuredVendor->where('is_featured',1)->get();
            $popularVendor = clone $vendorData;
            $popularVendor = $popularVendor->withCount('orderProducts')->orderBy('order_products_count','desc')->get();

            $allVendorData = clone $vendorData;
            $vendorData = $vendorData->with('slot', 'slotDate')->where('status', 1)->limit(100)->get();
            $venderIds  = $allVendorData->with('slot', 'slotDate')->where('status', 1)->pluck('id');

            $timezone = $user->timezone ?? 'Asia/Kolkata';
            $start_date = new DateTime("now", new  DateTimeZone($timezone) );
            $start_date =  $start_date->format('Y-m-d');
            $end_date = Date('Y-m-d', strtotime('+13 days'));


            foreach ($vendorData as $vendor) {
                $vendor->vendorrating = $this->vendorRatings($vendor->products);
                $vendor->vendorNoOfRatings = $this->vendorNoOfRatings($vendor->products);
                $vendor->promocodes = $this->getVendorWisePromoCodes($vendor->id);
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
                    $slotsDate = findSlot('',$vendor->id,$type );
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

            $products = $this->vendorProducts($vends, $langId, $clientCurrency, '', $type,$latitude,$longitude,$preferences,$category_id,$request->keyword);

            // Start Mobile Banners
            $mobile_banners = MobileBanner::select("id", "name", "description", "image", "link", 'redirect_category_id', 'redirect_vendor_id', 'link_url')
            ->where('status', 1)->where('validity_on', 1)
            ->with(['category:id,type_id', 'category.type', 'vendor'])
            ->where(function ($q) {
                $q->whereNull('start_date_time')->orWhere(function ($q2) {
                    $q2->whereDate('start_date_time', 'app/Http/Controllers/Api/v1/OrderController.php=', Carbon::now())
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

            $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
            $homeData['is_admin'] = $user_vendor_count > 0 ? 1 : 0;

            $homeData['popular_restaurents'] = $popularVendor;
            $homeData['featured_restaurents'] = $featuredVendor;
            $homeData['products'] = $products;

            return $this->successResponse($homeData);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function addVendorWishList(Request $request) {
        try {
            $user = Auth::user();
            $wishlistData = [
                'user_id' => $user->id,
                'vendor_id' => $request->input('vendor_id'),
            ];
            UserVendorWishlist::updateOrCreate($wishlistData, $wishlistData);
            return response()->json([
                'status' => 'success',
                'message' => 'Vendor added to your wish list successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getLine());
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function viewVendorWishList(Request $request) {
        try {
            $user = Auth::user();
            $userVendorWishlist = UserVendorWishlist::with('vendor')->where('user_id', $user->id)->get();
            return $this->successResponse($userVendorWishlist);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getLine());
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function removeVendorWishList(Request $request) {
        try {
            $user = Auth::user();
            $wishlistData = [
                'user_id' => $user->id,
                'vendor_id' => $request->input('vendor_id'),
            ];
            UserVendorWishlist::where($wishlistData)->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Vendor removed from your wish list successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getLine());
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
