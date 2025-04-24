<?php

namespace App\Http\Controllers\Client;

use App\Models\ProductDeliveryFeeByRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Client, ClientPreference, ClientPreferenceAdditional, MapProvider, SmsProvider, NomenclatureTranslation, Template, Currency, Language, ClientLanguage, ClientCurrency, Nomenclature, ReferAndEarn, SocialMedia, VendorRegistrationDocument, PageTranslation, BrandTranslation, VariantTranslation, ProductTranslation, Category_translation, AddonOptionTranslation, ClientSlot, DriverRegistrationDocument, VariantOptionTranslation, Tag, UserRegistrationDocuments, UserRegistrationDocumentTranslation, ThirdPartyAccounting, CategoryKycDocuments, VerificationOption, StaticDropoffLocation, Facilty, RoleOld, User, Country, ClientCountries, VendorMargConfig};
use GuzzleHttp\Client as GCLIENT;
use DB;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\ResetConfiguration;
use App\Http\Traits\ValidatorTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Redis;
use Session;

class ClientPreferenceController extends BaseController
{
    use \App\Http\Traits\ClientPreferenceManager;
    use ApiResponser, ResetConfiguration;

    // client_preference_fillable_key this variables define in ClientPreferenceManager

    public function index()
    {

        $client = Auth::user();
        $mapTypes = MapProvider::where('status', '1')->get();
        $smsTypes = SmsProvider::where('status', '1')->get();
        $ClientPreference = ClientPreference::where('client_code', $client->code)->first();
        $preference = $ClientPreference ? $ClientPreference : new ClientPreference();
        $client_languages = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
            ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
            ->where('client_languages.client_code', Auth::user()->code)
            ->where('client_languages.is_active', 1)
            ->orderBy('client_languages.is_primary', 'desc')->get();
        $file_types = ['image/*' => 'Image', '.pdf' => 'Pdf'];
        $file_types_driver = ['image/*' => 'Image', '.pdf' => 'Pdf', '.txt' => 'Text'];
        $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();
        $driver_registration_documents = DriverRegistrationDocument::with('primary')->get();


        $last_mile_teams = [];
        $laundry_teams = [];
        # if last mile on
        if (isset($preference) && $preference->need_delivery_service == '1') {
            $last_mile_teams = $this->getLastMileTeams();
        }
        # if laundry on
        if (isset($preference) && $preference->need_laundry_service == '1') {
            $laundry_teams = $this->getLaundryTeams();
        }

        $tags = Tag::with('primary')->get();
        $slots = ClientSlot::get();

        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;

        $nomenclature = Nomenclature::where('label', 'Product Order Form')->first();
        $nomenclatureProductOrderForm = "Product Order Form";
        if (!empty($nomenclature)) {
            $nomenclatureTranslation = NomenclatureTranslation::where(['nomenclature_id' => $nomenclature->id, 'language_id' => $langId])->first();
            if ($nomenclatureTranslation) {
                $nomenclatureProductOrderForm = $nomenclatureTranslation->name ?? null;
            }
        }



        $accounting     = ThirdPartyAccounting::where('code', 'xero')->first();

        $productDeliveryFeeByRole = ProductDeliveryFeeByRole::groupBy('role_id')->get()->pluck('role_id')->toArray();

        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role', 'is_phone_signup', 'token_currency', 'is_token_currency_enable', 'hubspot_access_token', 'is_hubspot_enable', 'gtag_id', 'fpixel_id', 'is_long_term_service', 'is_free_delivery_by_roles', 'is_cab_pooling', 'is_attribute', 'is_gst_required_for_vendor_registration', 'is_baking_details_required_for_vendor_registration', 'is_advance_details_required_for_vendor_registration', 'is_vendor_category_required_for_vendor_registration', 'is_seller_module', 'is_same_day_delivery', 'is_next_day_delivery', 'is_hyper_local_delivery', 'is_cod_payment', 'is_prepaid_payment', 'is_partial_payment', 'add_to_cart_btn', 'chat_button', 'call_button', 'seller_sold_title', 'saller_platform_logo', 'is_tracking_url', 'is_tracking_sms_url', 'is_tax_price_inclusive', 'is_postpay_enable', 'is_order_edit_enable', 'order_edit_before_hours', 'is_gift_card', 'is_place_order_delivery_zero', 'is_cust_success_signup_email', 'is_influencer_refer_and_earn', 'is_bid_enable', 'advance_booking_amount', 'advance_booking_amount_percentage', 'update_order_product_price', 'is_bid_ride_enable', 'is_one_push_book_enable', 'bid_expire_time_limit_seconds',  'is_corporate_user', 'is_user_kyc_for_registration', 'is_service_product_price_from_dispatch', 'is_recurring_booking', 'is_file_cart_instructions', 'is_admin_vendor_rating', 'square_enable_status', 'square_credentials', 'is_show_vendor_on_subcription', 'is_enable_compare_product', 'is_service_price_selection', 'is_particular_driver', 'pickup_notification_before', 'pickup_notification_before_hours', 'pickup_notification_before2', 'pickup_notification_before2_hours', 'is_enable_curb_side', 'is_map_search_perticular_country', 'marg_access_token', 'marg_date_time', 'is_marg_enable', 'marg_company_code', 'marg_decrypt_key', 'stock_notification_before', 'stock_notification_qunatity', 'marg_company_url', 'is_share_ride_users', 'is_cache_enable_for_home', 'cache_reset_time_for_home', 'cache_radius_for_home', 'is_enable_allergic_items', 'is_enable_google_analytics', 'header_script', 'footer_script', 'is_vendor_marg_configuration', 'marg_cron_schedular_time', 'is_role_and_permission_enable', 'is_taxjar_enable', 'taxjar_testmode', 'taxjar_api_token', 'is_lumen_enabled', 'lumen_domain_url', 'lumen_access_token', 'is_rental_weekly_monthly_price', 'blockchain_route_formation', 'blockchain_api_domain', 'blockchain_address_id', 'is_car_rental_enable', 'is_gofrugal_enable', 'gofrugal_enable_status', 'gofrugal_credentials', 'is_sms_complete_order', 'is_sms_cancel_order', 'is_sms_booked_ride', 'is_hourly_pickup_rental', 'is_product_measurement_in_cm_kg', 'enable_pwa', 'is_freelance_on_homepage', 'vendor_online_status', 'distance_matrix_app_status', 'cart_cms_page_status', 'document_report', 'fcm_vendor_project_id', 'product_measurment']);

        $client_detail = Client::first();
        return view('backend/setting/config')->with([
            'tags' => $tags,
            'slots' => $slots,
            'laundry_teams' => $laundry_teams,
            'last_mile_teams' => $last_mile_teams,
            'client' => $client,
            'preference' => $preference,
            'mapTypes' => $mapTypes,
            'smsTypes' => $smsTypes,
            'client_languages' => $client_languages,
            'file_types' => $file_types,
            'vendor_registration_documents' => $vendor_registration_documents,
            'driver_registration_documents' => $driver_registration_documents,
            'file_types_driver' => $file_types_driver,
            'nomenclatureProductOrderForm' => $nomenclatureProductOrderForm,
            'productDeliveryFeeByRole' => $productDeliveryFeeByRole,
            'accounting' => $accounting,
            'getAdditionalPreference' => $getAdditionalPreference,
            'client_detail' => $client_detail
        ]);
    }


    public function getCustomizePage(ClientPreference $clientPreference)
    {
        $curArray = [];
        $cli_langs = [];
        $cli_countries = [];
        $reffer_by = "";
        $reffer_to = "";
        $cli_currs = [];
        $laundry_teams = [];
        $client = Auth::user();
        $social_media_details = SocialMedia::get();
        $webTemplates = Template::where('for', '1')->get();
        $appTemplates = Template::where('for', '2')->get();
        $languages = Language::where('id', '>', '0')->get();
        $currencies = Currency::where('id', '>', '0')->get();
        $countries = Country::where('id', '>', '0')->get();
        $curtableData = array_chunk($currencies->toArray(), 2);
        $primaryCurrency = ClientCurrency::where('is_primary', 1)->first();
        $primaryCountry = ClientCountries::where('is_primary', 1)->first();
        $nomenclatureAllToGet = Nomenclature::get();
        $want_to_tip_nomenclature = $nomenclatureAllToGet->where('label', 'Want To Tip')->first();
        $include_gift_nomenclature = $nomenclatureAllToGet->where('label', 'Include Gift')->first();
        $control_panel_nomenclature = $nomenclatureAllToGet->where('label', 'Control Panel')->first();
        $fixed_fee = $nomenclatureAllToGet->where('label', 'Fixed Fee')->first();

        // $want_to_tip_nomenclature=Nomenclature::where('label','Want To Tip')->first();
        // $fixed_fee=Nomenclature::where('label','Fixed Fee')->first();
        $ClientPreference = ClientPreference::where('client_code', $client->code)
            ->first();
        if (isset($ClientPreference) && $ClientPreference->need_laundry_service == '1') {
            $laundry_teams = $this->getLaundryTeams();
        }

        $preference = $ClientPreference ? $ClientPreference : new ClientPreference();

        $nomenclature_value = $nomenclatureAllToGet->first();
        foreach ($preference->currency as $value) {
            $cli_currs[] = $value->currency_id;
        }
        foreach ($preference->language as $value) {
            $cli_langs[] = $value->language_id;
        }
        foreach ($preference->countries as $value) {
            $cli_countries[] = $value->country_id;
        }
        $tags = Tag::with('primary')->get();
        $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();

        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        $category_kyc_documents = CategoryKycDocuments::with(['primary', 'categoryMapping.category.translation_one'])->whereHas('categoryMapping')->get();

        if ($preference->reffered_by_amount == null) {
            $reffer_by = 0;
        } else {
            $reffer_by = $preference->reffered_by_amount;
        }
        if ($preference->reffered_to_amount == null) {
            $reffer_to = 0;
        } else {
            $reffer_to = $preference->reffered_to_amount;
        }
        $verify_codes   = array('passbase');
        $verify_options = VerificationOption::whereIn('code', $verify_codes)->get();
        $accounting     = ThirdPartyAccounting::where('code', 'xero')->first();
        $staticDropoff  = StaticDropoffLocation::get();


        $client_languages = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
            ->select('lang.id as langId', 'lang.name as langName', 'lang.sort_code', 'client_languages.client_code', 'client_languages.is_primary')
            ->where('client_languages.client_code', Auth::user()->code)
            ->where('client_languages.is_active', 1)
            ->orderBy('client_languages.is_primary', 'desc')->get();
        $roles = [];

        $roles = RoleOld::where('status', 1)->get();
        return view('backend.setting.customize', compact('client', 'nomenclature_value', 'want_to_tip_nomenclature', 'user_registration_documents', 'cli_langs', 'languages', 'currencies', 'preference', 'cli_currs', 'curtableData', 'webTemplates', 'appTemplates', 'primaryCurrency', 'social_media_details', 'client_languages', 'tags', 'vendor_registration_documents', 'reffer_by', 'reffer_to', 'category_kyc_documents', 'fixed_fee', 'verify_options', 'accounting', 'staticDropoff', 'laundry_teams', 'roles', 'countries', 'primaryCountry', 'cli_countries', 'include_gift_nomenclature', 'control_panel_nomenclature'));
    }

    public function referandearnUpdate(Request $request, $code)
    {
        $cp = new ClientPreference();
        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        if ($preference) {
            $preference->reffered_to_amount = $request->reffered_to_amount;
            $preference->reffered_by_amount = $request->reffered_by_amount;
            $preference->save();
            return redirect()->route('configure.customize')->with('success', 'Client Customization updated successfully!');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientPreferenceAdditional  $PreferenceAdditional
     * @return \Illuminate\Http\Response
     */

    public function additionalupdate(Request $request)
    {

        $rules = array(
            'token_currency' => 'required_if:is_token_currency_enable,1'
        );

        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }

        try {


            $this->updatePreferenceAdditional($request);

            if ($request->has('token_currency')) {
                $client = Client::first();
                $tokenCurrency = getAdditionalPreference(['token_currency'])['token_currency'];
                Redis::set($client->code, json_encode($tokenCurrency), 'EX', 36000);
                Redis::set("tCurrency_" . session()->get('userCode'), json_encode($tokenCurrency), 'EX', 36000);
                Redis::set("ifTCurrency_" . session()->get('userCode'), $request->is_token_currency_enable ?? 0, 'EX', 36000);
            }
            // $validated_keys = $request->only($this->client_preference_fillable_key);
            // $client = Client::first();

            // foreach($validated_keys as $key => $value){

            //     ClientPreferenceAdditional::updateOrCreate(
            //         ['key_name' => $key, 'client_code' => $client->code],
            //         ['key_name' => $key, 'key_value' => $value,'client_code' => $client->code,'client_id'=> $client->id]);
            //  }
            // try {
            //     $this->updatePreferenceAdditional($request);
            ProductDeliveryFeeByRole::where('is_free_delivery', 1)->delete();
            // if($request->has('apply_free_del')){
            // dd($request->all());
            $this->updateFreeDeliveryForRoles($request->apply_free_del);
            // }
            return redirect()->back()->with('success', 'Client settings updated successfully!');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong!!');
        }
    }

    // Have Issue need to fix in case of multipal role_id's
    // enable/disable price key in Role table (START)
    public function updateIsPriceEnable(Request $request)
    {
        $rules = array(
            'role' => 'required'
        );

        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }

        try {
            if ($request->has('role_id')) {
                foreach ($request->role_id as $key => $_role) {
                    $userRole                    = RoleOld::where('id', $_role)->first();
                    if (!$userRole) {
                        $userRole                = new RoleOld();
                    }
                    $userRole->is_enable_pricing = ($request->has('is_enable_pricing') && isset($request->is_enable_pricing[$_role])) ? ((($request->is_enable_pricing[$_role] == 1) || ($request->is_enable_pricing[$_role] == 'on')) ? 1 : 0) : 0;
                    $userRole->role              = $request->has('role') ? $request->role[$_role] : $userRole->role;
                    $userRole->save();
                }
                return redirect()->back()->with('success', 'Client settings updated successfully!');
            }
            return redirect()->back()->with('error', 'Something went wrong!!');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong!!');
        }
    }
    // enable/disable price key in Role table (END)

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientPreference  $clientPreference
     * @return \Illuminate\Http\Response
     */


    public function updateTaxInclusivePrice(Request $request)
    {
        $this->updatePreferenceAdditional($request);
        return true;
    }
    public function update(Request $request, $code)
    {

        $cp = new ClientPreference();
        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        if (!$preference) {
            $z = new ClientPreference();
            $preference->client_code = $code;
        }

        $keyShould = array('show_dark_mode', 'show_payment_icons', 'loyalty_check',   'theme_admin', 'distance_unit',   'date_format', 'time_format', 'nafath_client_id', 'nafath_client_secret', 'fb_client_id', 'fb_client_secret', 'fb_client_url', 'twitter_client_id', 'twitter_client_secret', 'twitter_client_url', 'google_client_id', 'google_client_secret', 'google_client_url',  'apple_client_id', 'apple_client_secret', 'apple_client_url',    'map_provider', 'map_key', 'map_secret', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'verify_email', 'verify_phone', 'web_template_id', 'app_template_id', 'personal_access_token_v1', 'personal_access_token_v2', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from',   'primary_color', 'secondary_color',  'dispatcher_key', 'web_color',   'fcm_server_key', 'fcm_api_key', 'fcm_auth_domain', 'fcm_project_id', 'fcm_storage_bucket', 'fcm_messaging_sender_id', 'fcm_app_id', 'fcm_measurement_id',  'android_app_link', 'ios_link', 'single_vendor', 'stripe_connect',   'customer_support', 'customer_support_key', 'customer_support_application_id', 'shipping_mode',  'tools_mode',  'digit_after_decimal', 'need_xero',  'db_audit_logs',  'map_key_for_app', 'map_key_for_ios_app', 'user_order_history', 'order_cancellation_time', 'cancellation_percentage',  'vendor_fcm_server_key', 'estimation_matching_logic',   'signup_image',  'is_tax_price_inclusive');
        $keyShouldNot = array('is_user_pre_signup', 'last_mile_team', 'hide_order_address', 'unifonic_app_id', 'unifonic_account_email', 'unifonic_account_password', 'laundry_pickup_team', 'laundry_dropoff_team', 'laundry_service_key_url', 'laundry_service_key_code', 'laundry_service_key', 'laundry_submit_btn', 'need_dispacher_ride_submit_btn', 'need_dispacher_home_other_service_submit_btn', 'need_inventory_service_submit_btn', 'last_mile_submit_btn', 'dispacher_home_other_service_key_url', 'dispacher_home_other_service_key_code', 'dispacher_home_other_service_key', 'pickup_delivery_service_key_url', 'pickup_delivery_service_key_code', 'pickup_delivery_service_key', 'delivery_service_key_url', 'delivery_service_key_code', 'delivery_service_key', 'need_delivery_service', 'need_dispacher_home_other_service', 'need_dispacher_ride', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'is_hyperlocal', '_token', 'social_login', 'send_to', 'languages', 'hyperlocals', 'currency_data', 'multiply_by', 'cuid', 'primary_language', 'primary_currency', 'currency_data', 'verify_config', 'verify_vendor_type', 'custom_mods_config', 'distance_to_time_calc_config', 'delay_order', 'gifting', 'product_order_form', 'mtalkz_api_key', 'mtalkz_sender_id', 'mazinhost_api_key', 'mazinhost_sender_id', 'minimum_order_batch', 'edit_order_modes', 'cancel_order_modes', 'category_kyc_documents', 'xero_submit', 'xero_status', 'xero_client_id', 'xero_secret_id', 'method_id', 'method_name', 'active', 'passbase_publish_key', 'passbase_secret_key', 'arkesel_api_key', 'arkesel_sender_id', 'subscription_tab_taxi', "sos", "sos_police_contact", 'sos_ambulance_contact', 'sos_enable', 'is_static_dropoff', 'is_vendor_tags', 'slotting_and_scheduling', 'appointment_submit_btn', 'need_appointment_service', 'appointment_service_key_url', 'appointment_service_key_code', 'appointment_service_key', 'is_long_term_service', 'is_long_term_service_switch', 'is_long_term_service', 'is_long_term_service_switch', 'is_phone_signup_switch', 'is_phone_signup', 'is_tax_price_inclusive', 'is_price_by_role_switch', 'is_price_by_role', 'is_gst_required_for_vendor_registration', 'is_gst_required_for_vendor_registration_switch', 'is_baking_details_required_for_vendor_registration_switch', 'is_baking_details_required_for_vendor_registration', 'is_advance_details_required_for_vendor_registration_switch', 'is_advance_details_required_for_vendor_registration', 'is_vendor_category_required_for_vendor_registration_switch', 'is_vendor_category_required_for_vendor_registration', 'is_seller_module_switch', 'is_seller_module', 'is_cab_pooling_switch', 'is_cab_pooling', 'is_same_day_delivery_switch', 'is_same_day_delivery', 'is_next_day_delivery_switch', 'is_next_day_delivery', 'is_hyper_local_delivery_switch', 'is_hyper_local_delivery', 'is_cod_payment_switch', 'is_cod_payment', 'is_prepaid_payment_switch', 'is_prepaid_payment', 'is_partial_payment_switch', 'is_partial_payment', 'is_cab_pooling_switch', 'is_cab_pooling', 'is_attribute_switch', 'is_attribute', 'add_to_cart_btn_switch', 'add_to_cart_btn', 'chat_button', 'chat_button_switch', 'call_button_switch', 'call_button', 'seller_sold_title', 'saller_platform_logo', 'is_tracking_url_switch', 'is_tracking_url', 'is_tracking_url_sms_switch', 'is_tracking_sms_url', 'is_postpay_enable_switch', 'is_postpay_enable', 'is_order_edit_enable_switch', 'is_order_edit_enable', 'order_edit_before_hours', 'is_gift_card', 'is_gift_card_switch', 'is_place_order_delivery_zero_switch', 'is_place_order_delivery_zero', 'is_cust_success_signup_email_switch', 'is_cust_success_signup_email', 'is_influencer_refer_and_earn_switch', 'is_influencer_refer_and_earn', 'afrTalk_api_key', 'afrTalk_sender_id', 'is_order_bid_switch', 'is_bid_enable', 'update_order_product_price', 'update_order_product_price_switch', 'is_bid_ride_enable', 'is_bid_ride_enable_switch', 'is_one_push_book_enable', 'is_one_push_book_enable_switch', 'bid_expire_time_limit_seconds', 'is_corporate_user_switch', 'is_corporate_user', 'is_user_kyc_for_registration_switch', 'is_user_kyc_for_registration', 'is_service_product_price_from_dispatch_switch', 'is_service_product_price_from_dispatch', 'is_recurring_booking_switch', 'is_recurring_booking', 'third_party_accounting_config', 'is_file_cart_instructions_switch', 'is_file_cart_instructions', 'vonage_api_key', 'vonage_secret_key', 'sms_partner_api_key', 'sms_partner_sender_id', 'is_admin_vendor_rating_switch', 'is_admin_vendor_rating', 'square_enable_status_switch', 'square_enable_status', 'square_sandbox_enable_status_switch', 'square_sandbox_enable_status', 'square_application_id', 'square_access_token', 'square_pos_integration', 'square_location_id', 'static_otp', 'is_enable_compare_product', 'is_rental_weekly_monthly_price', 'influencer_mode', 'is_enable_google_analytics', 'is_freelance_on_homepage', 'vendor_online_status', 'document_report', 'product_measurment');

        foreach ($request->all() as $key => $value) {
            if (in_array($key, $keyShould)) {
                $preference->{$key} = $value;
            }
        }

        if ($request->has('jitsi_domain_url')) {
            $request->validate([
                'jitsi_domain_url'  => 'required|string',
                'jitsi_api_key'     => 'required|string',
                'jitsi_api_secret'  => 'required|string',
                'jitsi_private_key' => 'file',
            ]);

            if ($request->hasFile('jitsi_private_key')) {
                $file = $request->file('jitsi_private_key')->store('public', 's3');

                if (! $file) {
                    return back()->with('error', 'Could not save jitsi private key');
                }

                $preference->jitsi_preferences = array_merge($preference->jitsi_preferences, [
                    'private_key' => $file,
                ]);
            }

            $preference->jitsi_preferences = array_merge($preference->jitsi_preferences, [
                'domain_url'  => $request->input('jitsi_domain_url'),
                'api_key'     => $request->input('jitsi_api_key'),
                'api_secret'  => $request->input('jitsi_api_secret'),
            ]);

            $preference->save();

            return back()->with('success', 'Jitsi preferences updated successfully');
        };

        // square pos integration configurations
        if ($request->has('square_pos_integration') && $request->square_pos_integration == '1') {

            $square_enable_status = ($request->has('square_enable_status')) ? $request->square_enable_status : 0;
            $square_sandbox_enable_status = ($request->has('square_sandbox_enable_status')) ? $request->square_sandbox_enable_status : 0;

            if ($square_enable_status == 1) {
                $json_creds = json_encode(array(
                    'application_id'        => $request->square_application_id,
                    'access_token'          => $request->square_access_token,
                    'sandbox_enable_status' => $square_sandbox_enable_status,
                    'location_id' => $request->square_location_id
                ));
            } else {
                $json_creds = json_encode(array(
                    'application_id'        => '',
                    'access_token'          => '',
                    'sandbox_enable_status' => '',
                    'location_id'           => ''
                ));
            }
            $request->request->add(['square_credentials' => $json_creds]);
        }
        if ($request->has('influencer_mode')) {
            $preference->celebrity_check = ($request->has('celebrity_check') && $request->celebrity_check == 'on') ? 1 : 0;
        }
        //gofrugal pos integration
        if ($request->has('gofrugal_pos_integration') && $request->gofrugal_pos_integration == '1') {

            $gofrugal_enable_status = ($request->has('gofrugal_enable_status')) ? $request->gofrugal_enable_status : 0;
            $gofrugal_sandbox_enable_status = ($request->has('gofrugal_sandbox_enable_status')) ? $request->gofrugal_sandbox_enable_status : 0;

            if ($gofrugal_enable_status == 1) {
                $json_creds = json_encode(array(
                    'api_key'        => $request->gofrugal_api_key,
                    'sandbox_enable_status' => $gofrugal_sandbox_enable_status,
                    'domain_url' => $request->gofrugal_domain_url
                ));
            } else {
                $json_creds = json_encode(array(
                    'api_key'        => '',
                    'sandbox_enable_status' => '',
                    'domain_url' => ''
                ));
            }
            $request->request->add(['gofrugal_credentials' => $json_creds]);
        }
        // update Client Preference Additional column
        $this->updatePreferenceAdditional($request);
        if ($request->has('sms_provider')) {
            if ($request->sms_provider == 1) //for twillio
            {
                $sms_credentials = [
                    'sms_from' => $request->sms_from,
                    'sms_key' => $request->sms_key,
                    'sms_secret' => $request->sms_secret,
                ];
            } elseif ($request->sms_provider == 2) // for mTalkz
            {
                $sms_credentials = [
                    'api_key' => $request->mtalkz_api_key,
                    'sender_id' => $request->mtalkz_sender_id,
                ];
            } elseif ($request->sms_provider == 3) // for mazinhost
            {
                $sms_credentials = [
                    'api_key' => $request->mazinhost_api_key,
                    'sender_id' => $request->mazinhost_sender_id,
                ];
            } elseif ($request->sms_provider == 4) // for unifonic
            {
                $sms_credentials = [
                    'unifonic_app_id' => $request->unifonic_app_id,
                    'unifonic_account_email' => $request->unifonic_account_email,
                    'unifonic_account_password' => $request->unifonic_account_password,
                ];
            } elseif ($request->sms_provider == 5) // for unifonic
            {
                $sms_credentials = [
                    'api_key' => $request->arkesel_api_key,
                    'sender_id' => $request->arkesel_sender_id,
                ];
            } elseif ($request->sms_provider == 6) // for unifonic
            {
                $sms_credentials = [
                    'api_key' => $request->afrTalk_api_key,
                    'sender_id' => $request->afrTalk_sender_id,
                ];
            } elseif ($request->sms_provider == 7) // for vonage
            {
                $sms_credentials = [
                    'api_key' => $request->vonage_api_key,
                    'secret_key' => $request->vonage_secret_key,
                ];
            } elseif ($request->sms_provider == 8) // for vonage
            {
                $sms_credentials = [
                    'api_key' => $request->sms_partner_api_key,
                    'sender_id' => $request->sms_partner_sender_id,
                ];
            } elseif ($request->sms_provider == 9) // for ethiopia
            {
                $sms_credentials = [
                    'sms_username' => $request->sms_username,
                    'sms_password' => $request->sms_password,
                ];
            } elseif ($request->sms_provider == 10) // Sms Country
            {
                $sms_credentials = [
                    'sms_sender_id' => $request->sms_sender_id,
                    'sms_auth_key' => $request->sms_auth_key,
                    'sms_auth_token' => $request->sms_auth_token,
                ];
            }
            //for static otp
            $sms_credentials['static_otp'] = ($request->has('static_otp') && $request->static_otp == 'on') ? 1 : 0;
            $preference->sms_credentials = json_encode($sms_credentials);
        }


        /* SOS update */
        if ($request->has('sos_enable') && $request->sos_enable == '1') {
            $preference->sos = ($request->has('sos') && $request->sos == 'on') ? 1 : 0;
            if ($request->has('sos') && $request->sos == 'on') {
                $preference->sos_police_contact = $request->sos_police_contact;
                $preference->sos_ambulance_contact = $request->sos_ambulance_contact;
            }
        }

        /* social login update */
        if ($request->has('social_login') && $request->social_login == '1') {
            $preference->fb_login = ($request->has('fb_login') && $request->fb_login == 'on') ? 1 : 0;
            $preference->twitter_login = ($request->has('twitter_login') && $request->twitter_login == 'on') ? 1 : 0;
            $preference->google_login = ($request->has('google_login') && $request->google_login == 'on') ? 1 : 0;
            $preference->apple_login = ($request->has('apple_login') && $request->apple_login == 'on') ? 1 : 0;
            $preference->nafath_login = ($request->has('nafath_login') && $request->nafath_login == 'on') ? 1 : 0;
        }
        if ($request->has('verify_config') && $request->verify_config == '1') {
            $preference->verify_email = ($request->has('verify_email') && $request->verify_email == 'on') ? 1 : 0;
            $preference->verify_phone = ($request->has('verify_phone') && $request->verify_phone == 'on') ? 1 : 0;
            $preference->concise_signup = (!empty($request->concise_signup) && $request->concise_signup == 'on') ? 1 : 0;
            $preference->age_restriction_on_product_mode = ($request->has('age_restriction_on_product_mode') && $request->age_restriction_on_product_mode == 'on') ? 1 : 0;
            $this->updateVerificationOption([
                'method_id' => $request->method_id,
                'method_name' => $request->method_name,
                'active' => $request->active,
                'passbase_publish_key' => $request->passbase_publish_key,
                'passbase_secret_key' => $request->passbase_secret_key
            ]);
        }
        if ($request->has('verify_vendor_type') && $request->verify_vendor_type == '1') {
            $roles = [
                'dinein_check'   => 'required_without_all:takeaway_check,delivery_check,rental_check,pick_drop_check,on_demand_check,laundry_check,appointment_check,p2p_check',
                'takeaway_check' => 'required_without_all:dinein_check,delivery_check,rental_check,pick_drop_check,on_demand_check,laundry_check,appointment_check,p2p_check',
                'delivery_check' => 'required_without_all:dinein_check,takeaway_check,rental_check,pick_drop_check,on_demand_check,laundry_check,appointment_check,p2p_check',
                'rental_check'   => 'required_without_all:dinein_check,takeaway_check,delivery_check,pick_drop_check,on_demand_check,laundry_check,appointment_check,p2p_check',
                'pick_drop_check' => 'required_without_all:dinein_check,takeaway_check,delivery_check,rental_check,on_demand_check,laundry_check,appointment_check,p2p_check',
                'on_demand_check' => 'required_without_all:dinein_check,takeaway_check,delivery_check,pick_drop_check,rental_check,laundry_check,appointment_check,p2p_check',
                'laundry_check'  => 'required_without_all:dinein_check,takeaway_check,delivery_check,pick_drop_check,on_demand_check,rental_check,appointment_check,p2p_check',
                'appointment_check'  => 'required_without_all:dinein_check,takeaway_check,delivery_check,pick_drop_check,on_demand_check,laundry_check,rental_check,p2p_check',
                'p2p_check'  => 'required_without_all:dinein_check,takeaway_check,delivery_check,rental_check,pick_drop_check,on_demand_check,laundry_check,appointment_check'

            ];
            // atleast one is required
            $validator = Validator::make($request->all(), $roles);
            if ($validator->fails()) {
                return redirect()->route('configure.customize')->with('error', __('Atleast one vendor type will be active'));
            }
            // save vendor mode in client preference table
            foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
                $vendor_typ_name = $vendor_typ_key . "_check";
                $preference->$vendor_typ_name = ($request->has($vendor_typ_name) && $request->$vendor_typ_name == 'on') ? 1 : 0;
                if ($vendor_typ_key == 'p2p') {
                    $request->request->add(['is_attribute' => $preference->$vendor_typ_name]);
                    $this->updatePreferenceAdditional($request);
                    $request->request->remove('is_attribute');
                }
            }
            // if((!$request->has('dinein_check') && !$request->dinein_check == 'on') && (!$request->has('takeaway_check') && !$request->dinein_check == 'on') && (!$request->has('delivery_check') && !$request->dinein_check == 'on')){
            //     return redirect()->route('configure.customize')->with('error', 'One Option must be acitve');
            // }

            // $preference->dinein_check = ($request->has('dinein_check') && $request->dinein_check == 'on') ? 1 : 0;
            // $preference->takeaway_check = ($request->has('takeaway_check') && $request->takeaway_check == 'on') ? 1 : 0;
            // $preference->delivery_check = ($request->has('delivery_check') && $request->delivery_check == 'on') ? 1 : 0;
        }


        if ($request->has('custom_mods_config') && $request->custom_mods_config == '1') {
            $preference->enquire_mode = ($request->has('enquire_mode') && $request->enquire_mode == 'on') ? 1 : 0;
            $preference->pharmacy_check = ($request->has('pharmacy_check') && $request->pharmacy_check == 'on') ? 1 : 0;

            $preference->subscription_mode = ($request->has('subscription_mode') && $request->subscription_mode == 'on') ? 1 : 0;
            $preference->tip_before_order = ($request->has('tip_before_order') && $request->tip_before_order == 'on') ? 1 : 0;
            $preference->tip_after_order = ($request->has('tip_after_order') && $request->tip_after_order == 'on') ? 1 : 0;
            $preference->product_order_form = ($request->has('product_order_form') && $request->product_order_form == 'on') ? 1 : 0;
            $preference->isolate_single_vendor_order = ($request->has('isolate_single_vendor_order') && $request->isolate_single_vendor_order == 'on') ? 1 : 0;
            $preference->gifting = ($request->has('gifting') && $request->gifting == 'on') ? 1 : 0;
            $preference->pickup_delivery_service_area = ($request->has('pickup_delivery_service_area') && $request->pickup_delivery_service_area == 'on') ? 1 : 0;
            $preference->minimum_order_batch = ($request->has('minimum_order_batch') && $request->minimum_order_batch == 'on') ? 1 : 0;
            $preference->static_delivey_fee = ($request->has('static_delivey_fee') && $request->static_delivey_fee == 'on') ? 1 : 0;
            $preference->get_estimations = ($request->has('get_estimations') && $request->get_estimations == 'on') ? 1 : 0;
            $preference->is_scan_qrcode_bag = ($request->has('is_scan_qrcode_bag') && $request->is_scan_qrcode_bag == 'on') ? 1 : 0;
            $preference->view_get_estimation_in_category = ($request->has('view_get_estimation_in_category') && $request->view_get_estimation_in_category == 'on') ? 1 : 0; //Added by ovi
            $preference->max_safety_mod = ($request->has('max_safety_mod') && $request->max_safety_mod == 'on') ? 1 : 0;

            $preference->hide_order_address = ($request->has('hide_order_address') && $request->hide_order_address == 'on') ? 1 : 0;
            $preference->auto_implement_5_percent_tip = ($request->has('auto_implement_5_percent_tip') && $request->auto_implement_5_percent_tip == 'on') ? 1 : 0;
            $preference->subscription_tab_taxi = ($request->has('subscription_tab_taxi') && $request->subscription_tab_taxi == 'on') ? 1 : 0;
            $preference->category_kyc_documents = ($request->has('category_kyc_documents') && $request->category_kyc_documents == 'on') ? 1 : 0;
            $preference->vendor_return_request = ($request->has('vendor_return_request') && $request->vendor_return_request == 'on') ? 1 : 0;
            $preference->hide_order_prepare_time = ($request->has('hide_order_prepare_time') && $request->hide_order_prepare_time == 'on') ? 1 : 0;
            $preference->is_cancel_order_user = ($request->has('is_cancel_order_user') && $request->is_cancel_order_user == 'on') ? 1 : 0;
            $preference->enable_inventory_service = ($request->has('enable_inventory_service') && $request->enable_inventory_service == 'on') ? 1 : 0;
            $preference->book_for_friend = ($request->has('book_for_friend') && $request->book_for_friend == 'on') ? 1 : 0;
            $preference->is_static_dropoff = ($request->has('is_static_dropoff') && $request->is_static_dropoff == 'on') ? 1 : 0;
            $preference->is_vendor_tags = ($request->has('is_vendor_tags') && $request->is_vendor_tags == 'on') ? 1 : 0;
            $preference->is_service_area_for_banners = ($request->has('is_service_area_for_banners') && $request->is_service_area_for_banners == 'on') ? 1 : 0;
            $preference->stop_order_acceptance_for_users = ($request->has('stop_order_acceptance_for_users') && $request->stop_order_acceptance_for_users == 'on') ? 1 : 0;
            $preference->map_on_search_screen = ($request->has('map_on_search_screen') && $request->map_on_search_screen == 'on') ? 1 : 0;
            $preference->slots_with_service_area = ($request->has('slots_with_service_area') && $request->slots_with_service_area == 'on') ? 1 : 0;
            $preference->is_hourly_pickup_rental = ($request->has('is_hourly_pickup_rental_switch') && $request->is_hourly_pickup_rental_switch == 'on') ? 1 : 0;
        }




        if ($request->has('edit_order_modes') && $request->edit_order_modes == '1') {
            $preference->is_edit_order_admin = ($request->has('is_edit_order_admin') && $request->is_edit_order_admin == 'on') ? 1 : 0;
            $preference->is_edit_order_vendor = ($request->has('is_edit_order_vendor') && $request->is_edit_order_vendor == 'on') ? 1 : 0;
            $preference->is_edit_order_driver = ($request->has('is_edit_order_driver') && $request->is_edit_order_driver == 'on') ? 1 : 0;
        }
        if ($request->has('distance_to_time_calc_config') && $request->distance_to_time_calc_config == '1') {
            $preference->distance_unit_for_time = (($request->has('distance_unit_for_time')) && ($request->distance_unit_for_time != '')) ? $request->distance_unit_for_time : 'kilometer';
            $preference->distance_to_time_multiplier = (($request->has('distance_to_time_multiplier')) && ($request->distance_to_time_multiplier != '')) ? $request->distance_to_time_multiplier : 2;
        }

        // third party accounting and xero configurations
        if ($request->has('third_party_accounting_config') && $request->third_party_accounting_config == '1') {

            $preference->third_party_accounting = ($request->has('third_party_accounting') && $request->third_party_accounting == 'on') ? 1 : 0;

            if ($request->has('xero_status') && $request->xero_status == 'on') {
                if (((!$request->has('xero_client_id')) || ($request->xero_client_id == '')) || ((!$request->has('xero_secret_id')) || ($request->xero_secret_id == ''))) {
                    return redirect()->route('configure.index')->with('error', 'Invalid Xero Configuration Data');
                }
            }
            $json_creds = json_encode(array(
                'client_id' => $request->xero_client_id,
                'secret_id' => $request->xero_secret_id,
            ));

            $update = ThirdPartyAccounting::where('code', 'xero')->update([
                'status' => ($request->has('xero_status') && $request->xero_status == 'on') ? 1 : 0,
                'credentials' => $json_creds
            ]);
        }

        if ($request->has('primary_language')) {
            $deactivate_language = ClientLanguage::where('client_code', Auth::user()->code)->where('is_primary', 1)->first();
            if ($deactivate_language) {
                $deactivate_language->is_active = '0';
                $deactivate_language->is_primary = '0';
                $deactivate_language->save();
            }
            $primary_change = ClientLanguage::where('client_code', Auth::user()->code)->where('language_id', $request->primary_language)->update(['is_active' => 1, 'is_primary' => 1]);
            if (!$primary_change) {
                ClientLanguage::insert([
                    'is_active' => 1,
                    'is_primary' => 1,
                    'client_code' => Auth::user()->code,
                    'language_id' => $request->primary_language,
                ]);
            }
            PageTranslation::where('language_id', $deactivate_language->language_id)->update(['language_id' => $request->primary_language]);
            BrandTranslation::where('language_id', $deactivate_language->language_id)->update(['language_id' => $request->primary_language]);
            VariantTranslation::where('language_id', $deactivate_language->language_id)->update(['language_id' => $request->primary_language]);
            ProductTranslation::where('language_id', $deactivate_language->language_id)->update(['language_id' => $request->primary_language]);
            Category_translation::where('language_id', $deactivate_language->language_id)->update(['language_id' => $request->primary_language]);
            AddonOptionTranslation::where('language_id', $deactivate_language->language_id)->update(['language_id' => $request->primary_language]);
            VariantOptionTranslation::where('language_id', $deactivate_language->language_id)->update(['language_id' => $request->primary_language]);
            $exist_language_id = array();
            if ($request->has('languages')) {
                foreach ($request->languages as $lan) {
                    if ($lan != $request->primary_language) {
                        $client_language = ClientLanguage::where('client_code', Auth::user()->code)->where('language_id', $lan)->first();
                        if (!$client_language) {
                            $client_language = new ClientLanguage();
                            $client_language->client_code = Auth::user()->code;
                        }
                        $client_language->is_primary = 0;
                        $client_language->language_id = $lan;
                        $client_language->is_active = 1;
                        $client_language->save();
                        $exist_language_id[] = $client_language->language_id;
                    }
                }
            }
            $deactivateLanguages = ClientLanguage::where('client_code', Auth::user()->code)->whereNotIn('language_id', $exist_language_id)->where('is_primary', 0)->update(['is_active' => 0]);
        }

        if ($request->has('primary_currency')) {
            $oldAdditional = ClientCurrency::where('currency_id', $request->primary_currency)
                ->where('is_primary', 0)->delete();
            $primaryCur = ClientCurrency::where('is_primary', 1)->update(['currency_id' => $request->primary_currency, 'doller_compare' => 1]);
        }
        if ($request->has('primary_currency') && !$request->has('currency_data')) {
            $delete = ClientCurrency::where('client_code', Auth::user()->code)->where('is_primary', 0)->delete();
        }
        // Create Or Update Primary Country And Additional Country
        if ($request->filled('primary_country')) {
            $primaryCountryData = [
                'is_active' => 1,
                'is_primary' => 1,
                'client_code' => Auth::user()->code,
                'country_id' => $request->primary_country,
            ];

            ClientCountries::where('client_code', Auth::user()->code)->where('is_primary', 0)->delete();

            ClientCountries::updateOrCreate(['is_primary' => 1], $primaryCountryData);
        }

        if ($request->filled('countries')) {
            $existingCountryIds = [];
            foreach ($request->countries as $country) {
                if ($country != $request->primary_country) {
                    $existingCountryIds[] = $country;
                }
            }
            ClientCountries::where('client_code', Auth::user()->code)->where('is_primary', 0)->delete();
            $clientCountriesData = [];
            foreach ($existingCountryIds as $country) {
                $clientCountriesData[] = [
                    'is_primary' => 0,
                    'is_active' => 1,
                    'client_code' => Auth::user()->code,
                    'country_id' => $country,
                ];
            }
            ClientCountries::insert($clientCountriesData);
        }
        //  End

        if ($request->has('currency_data') && $request->has('multiply_by')) {
            $cur_multi = $exist_cid = array();
            foreach ($request->currency_data as $key => $value) {
                $exist_cid[] = $value;
                $curr = ClientCurrency::where('currency_id', $value)->where('client_code', Auth::user()->code)->first();
                $multiplier = $request->multiply_by[$value] ?? 1;
                if (!$curr) {
                    $cur_multi[] = [
                        'currency_id' => $value,
                        'client_code' => Auth::user()->code,
                        'is_primary' => 0,
                        'doller_compare' => $multiplier
                    ];
                } else {
                    $curr->doller_compare =  $multiplier;
                    $curr->save();

                    // $res = ClientCurrency::where('currency_id', $value)->where('client_code',Auth::user()->code)
                    //             ->update(['doller_compare' => $multiplier]);
                    // pr($res);
                }
            }
            ClientCurrency::insert($cur_multi);
            $delete = ClientCurrency::where('client_code', Auth::user()->code)->where('is_primary', 0)
                ->whereNotIn('currency_id', $exist_cid)->delete();
        }

        if ($request->has('admin_email')) {
            $preference->admin_email = $request->admin_email;
        }


        //Adding Code by inder bcz ididn't found this code here
        if (isset($request->hyperlocals) && !empty($request->hyperlocals)) {
            if (isset($request->is_hyperlocal) && !empty($request->is_hyperlocal)) {
                $preference->is_hyperlocal = ($request->has('is_hyperlocal') && $request->is_hyperlocal == 'on') ? 1 : 0;
                $preference->Default_location_name = $request->Default_location_name;
                $preference->Default_latitude = $request->Default_latitude;
                $preference->Default_longitude = $request->Default_longitude;
            } else {
                $preference->is_hyperlocal = ($request->has('is_hyperlocal') && $request->is_hyperlocal == 'on') ? 1 : 0;
            }
        }

        // Check if the request is coming from customize page
        if ($request->has('slotting_and_scheduling') && $request->slotting_and_scheduling == 1) {
            $preference->delay_order = ($request->has('delay_order') && $request->delay_order == 'on') ? 1 : 0; // Moved by ovi
            $preference->off_scheduling_at_cart = ($request->has('off_scheduling_at_cart') && $request->off_scheduling_at_cart == 'on') ? 1 : 0; // Moved by ovi
            $preference->scheduling_with_slots = ($request->has('scheduling_with_slots') && $request->scheduling_with_slots == 'on') ? 1 : 0; //Added by ovi
            $preference->same_day_delivery_for_schedule = ($request->has('same_day_delivery_for_schedule') && $request->same_day_delivery_for_schedule == 'on') ? 1 : 0;  //Added by ovi
            $preference->same_day_orders_for_rescheduing = ($request->has('same_day_orders_for_rescheduing') && $request->same_day_orders_for_rescheduing == 'on') ? 1 : 0; //Added by ovi
        }

        $preference->save();

        $preferenceset = ClientPreference::where('client_code', Auth::user()->code)->first();
        if (isset($request->last_mile_submit_btn) && !empty($request->last_mile_submit_btn)) {
            if (isset($request->need_delivery_service) && !empty($request->need_delivery_service)) {
                try {
                    $client = new GClient(['headers' => ['personaltoken' => $request->delivery_service_key, 'shortcode' => $request->delivery_service_key_code, 'content-type' => 'application/json']]);
                    $url = $request->delivery_service_key_url;
                    $res = $client->post($url . '/api/check-dispatcher-keys');
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 400) {
                        return redirect()->back()->with('error', 'Last Mile Delivery Keys incorrect !');
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Invalid Last Mile Delivery Dispatcher URL !');
                }
                $preferenceset->need_delivery_service = ($request->has('need_delivery_service') && $request->need_delivery_service == 'on') ? 1 : 0;
                $preferenceset->delivery_service_key_url = $request->delivery_service_key_url;
                $preferenceset->delivery_service_key_code = $request->delivery_service_key_code;
                $preferenceset->delivery_service_key = $request->delivery_service_key;
                $preferenceset->last_mile_team = $request->last_mile_team;
            } else {
                $preferenceset->need_delivery_service =  ($request->has('need_delivery_service') && $request->need_delivery_service == 'on') ? 1 : 0;
            }
        }

        if (isset($request->laundry_submit_btn) && !empty($request->laundry_submit_btn)) {
            if (isset($request->need_laundry_service) && !empty($request->need_laundry_service)) {
                try {
                    $client = new GClient(['headers' => ['personaltoken' => $request->laundry_service_key, 'shortcode' => $request->laundry_service_key_code, 'content-type' => 'application/json']]);
                    $url = $request->laundry_service_key_url;
                    $res = $client->post($url . '/api/check-dispatcher-keys');
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 400) {
                        if ($request->has('send_to') && $request->send_to == 'customize') {
                            return redirect()->route('configure.customize')->with('error', 'laundry Keys incorrect !');
                        } else {
                            return redirect()->route('configure.customize')->with('error', 'laundry Keys incorrect !');
                        }
                    }
                } catch (\Exception $e) {
                    if ($request->has('send_to') && $request->send_to == 'customize') {
                        return redirect()->route('configure.customize')->with('error', 'Invalid laundry Dispatcher URL !');
                    } else {
                        return redirect()->route('configure.customize')->with('error', 'Invalid laundry Dispatcher URL !');
                    }
                }
                $preferenceset->need_laundry_service = ($request->has('need_laundry_service') && $request->need_laundry_service == 'on') ? 1 : 0;
                $preferenceset->laundry_service_key_url = $request->laundry_service_key_url;
                $preferenceset->laundry_service_key_code = $request->laundry_service_key_code;
                $preferenceset->laundry_service_key = $request->laundry_service_key;
                $preferenceset->laundry_pickup_team = $request->laundry_pickup_team;
                $preferenceset->laundry_dropoff_team = $request->laundry_dropoff_team;
            } else {
                $preferenceset->need_laundry_service =  ($request->has('need_laundry_service') && $request->need_laundry_service == 'on') ? 1 : 0;
            }
        }


        if (isset($request->need_dispacher_ride_submit_btn) && !empty($request->need_dispacher_ride_submit_btn)) {
            if (isset($request->need_dispacher_ride) && !empty($request->need_dispacher_ride)) {
                try {
                    $client = new GClient(['headers' => ['personaltoken' => $request->pickup_delivery_service_key, 'shortcode' => $request->pickup_delivery_service_key_code, 'content-type' => 'application/json']]);
                    $url = $request->pickup_delivery_service_key_url;
                    $res = $client->post($url . '/api/check-dispatcher-keys');
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 400) {
                        if ($request->has('send_to') && $request->send_to == 'customize') {
                            return redirect()->route('configure.customize')->with('error', 'Pickup & Delivery Keys incorrect !');
                        } else {
                            return redirect()->route('configure.customize')->with('error', 'Pickup & Delivery Keys incorrect !');
                        }
                    }
                } catch (\Exception $e) {
                    if ($request->has('send_to') && $request->send_to == 'customize') {
                        return redirect()->route('configure.customize')->with('error', 'Invalid Pickup & Delivery Dispatcher URL !');
                    } else {
                        return redirect()->route('configure.customize')->with('error', 'Invalid Pickup & Delivery Dispatcher URL !');
                    }
                }
                $preferenceset->need_dispacher_ride = ($request->has('need_dispacher_ride') && $request->need_dispacher_ride == 'on') ? 1 : 0;
                $preferenceset->pickup_delivery_service_key_url = $request->pickup_delivery_service_key_url;
                $preferenceset->pickup_delivery_service_key_code = $request->pickup_delivery_service_key_code;
                $preferenceset->pickup_delivery_service_key = $request->pickup_delivery_service_key;
            } else {
                $preferenceset->need_dispacher_ride = ($request->has('need_dispacher_ride') && $request->need_dispacher_ride == 'on') ? 1 : 0;
            }
        }

        if (isset($request->need_dispacher_home_other_service_submit_btn) && !empty($request->need_dispacher_home_other_service_submit_btn)) {

            if (isset($request->need_dispacher_home_other_service) && !empty($request->need_dispacher_home_other_service)) {
                try {
                    $client = new GClient([
                        'headers' => [
                            'personaltoken' => $request->dispacher_home_other_service_key,
                            'shortcode' => $request->dispacher_home_other_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);
                    $url = $request->dispacher_home_other_service_key_url;
                    $res = $client->post($url . '/api/check-dispatcher-keys');
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 400) {
                        if ($request->has('send_to') && $request->send_to == 'customize') {
                            return redirect()->route('configure.customize')->with('error', 'On Demand Services Keys incorrect!');
                        } else {
                            return redirect()->route('configure.customize')->with('error', 'On Demand Services Keys incorrect!');
                        }
                    }
                } catch (\Exception $e) {
                    if ($request->has('send_to') && $request->send_to == 'customize') {
                        return redirect()->route('configure.customize')->with('error', 'Invalid On Demand Services Dispatcher URL !');
                    } else {
                        return redirect()->route('configure.customize')->with('error', 'Invalid On Demand Services Dispatcher URL !');
                    }
                }
                $preferenceset->need_dispacher_home_other_service = ($request->has('need_dispacher_home_other_service') && $request->need_dispacher_home_other_service == 'on') ? 1 : 0;
                $preferenceset->dispacher_home_other_service_key_url = $request->dispacher_home_other_service_key_url;
                $preferenceset->dispacher_home_other_service_key_code = $request->dispacher_home_other_service_key_code;
                $preferenceset->dispacher_home_other_service_key = $request->dispacher_home_other_service_key;
            } else {
                $preferenceset->need_dispacher_home_other_service = ($request->has('need_dispacher_home_other_service') && $request->need_dispacher_home_other_service == 'on') ? 1 : 0;
            }
        }


        # inventory service
        if (isset($request->need_inventory_service_submit_btn) && !empty($request->need_inventory_service_submit_btn)) {

            if (isset($request->need_inventory_service) && !empty($request->need_inventory_service)) {
                try {
                    $client = new GClient([
                        'headers' => [
                            'shortcode' => $request->inventory_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);
                    $url = $request->inventory_service_key_url;
                    $res = $client->post($url . '/api/v1/check-inventory-keys');
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 400) {
                        return redirect()->route('configure.index')->with('error', 'Inventory Services Keys incorrect !');
                    }
                } catch (\Exception $e) {
                    return redirect()->route('configure.index')->with('error', 'Invalid Inventory Services Dispatcher URL !');
                }
                $preferenceset->need_inventory_service = ($request->has('need_inventory_service') && $request->need_inventory_service == 'on') ? 1 : 0;
                $preferenceset->inventory_service_key_url = $request->inventory_service_key_url;
                $preferenceset->inventory_service_key_code = $request->inventory_service_key_code;
                $preferenceset->inventory_service_key = $request->inventory_service_key ?? null;
            } else {
                $preferenceset->need_inventory_service = ($request->has('need_inventory_service') && $request->need_inventory_service == 'on') ? 1 : 0;
            }
        }

        if (isset($request->appointment_submit_btn) && !empty($request->appointment_submit_btn)) {
            if (isset($request->need_appointment_service) && !empty($request->need_appointment_service)) {
                try {
                    $client = new GClient(['headers' => ['personaltoken' => $request->appointment_service_key, 'shortcode' => $request->appointment_service_key_code, 'content-type' => 'application/json']]);
                    $url = $request->appointment_service_key_url;
                    $res = $client->post($url . '/api/check-dispatcher-keys');
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 400) {
                        if ($request->has('send_to') && $request->send_to == 'customize') {
                            return redirect()->route('configure.customize')->with('error', 'Appointment Keys incorrect!');
                        } else {
                            return redirect()->route('configure.customize')->with('error', 'Appointment Keys incorrect!');
                        }
                    }
                } catch (\Exception $e) {
                    if ($request->has('send_to') && $request->send_to == 'customize') {
                        return redirect()->route('configure.customize')->with('error', 'Invalid Appointment Dispatcher URL !');
                    } else {
                        return redirect()->route('configure.customize')->with('error', 'Invalid Appointment Dispatcher URL !');
                    }
                }
                $preferenceset->need_appointment_service = ($request->has('need_appointment_service') && $request->need_appointment_service == 'on') ? 1 : 0;
                $preferenceset->appointment_service_key_url = $request->appointment_service_key_url;
                $preferenceset->appointment_service_key_code = $request->appointment_service_key_code;
                $preferenceset->appointment_service_key = $request->appointment_service_key;
            } else {
                $preferenceset->need_appointment_service = ($request->has('need_appointment_service') && $request->need_appointment_service == 'on') ? 1 : 0;
            }
        }
        $preferenceset->save();
        $client = Client::first();




        // if ($request->hasFile($doc_name)) {
        //     $filePath = $this->folderName . '/' . Str::random(40);
        //     $file = $request->file($doc_name);
        //     $orignal_name = $request->file($doc_name)->getClientOriginalName();
        //     $file_name = Storage::disk('s3')->put($filePath, $file, 'public');

        //     UserDocs::updateOrCreate(

        //         ['user_id' => $user->id, 'user_registration_document_id' => $user_registration_document->id]
        //         ,
        //         ['file_name' => $file_name,'file_original_name'=>$orignal_name]);
        // }


        ClientPreferenceAdditional::updateOrCreate(
            ['key_name' => 'is_user_pre_signup', 'client_code' => $client->code],
            ['key_name' => 'is_user_pre_signup', 'key_value' => $request->input('is_user_pre_signup') ? $request->input('is_user_pre_signup') : 0, 'client_code' => $client->code, 'client_id' => $client->id]
        );


        if ($request->has('firebase_account_json_file')) {

            $file = Storage::disk('s3')->put('prods', $request->firebase_account_json_file, 'public');
            ClientPreferenceAdditional::updateOrCreate(
                ['key_name' => 'firebase_account_json_file', 'client_code' => $client->code],
                ['key_name' => 'firebase_account_json_file', 'key_value' => $file ?? "", 'client_code' => $client->code, 'client_id' => $client->id]
            );
        }

        if ($request->has('firebase_vendor_account_json_file')) {

            $file = Storage::disk('s3')->put('prods', $request->firebase_vendor_account_json_file, 'public');
            ClientPreferenceAdditional::updateOrCreate(
                ['key_name' => 'firebase_vendor_account_json_file', 'client_code' => $client->code],
                ['key_name' => 'firebase_vendor_account_json_file', 'key_value' => $file ?? "", 'client_code' => $client->code, 'client_id' => $client->id]
            );
        }

        if ($request->has('fcm_vendor_project_id')) {

            $value = $request->fcm_vendor_project_id;
            ClientPreferenceAdditional::updateOrCreate(
                ['key_name' => 'fcm_vendor_project_id', 'client_code' => $client->code],
                ['key_name' => 'fcm_vendor_project_id', 'key_value' => $value ?? "", 'client_code' => $client->code, 'client_id' => $client->id]
            );
        }


        if ($request->has('send_to') && $request->send_to == 'customize') {
            return redirect()->route('configure.customize')->with('success', 'Client customizations updated successfully!');
        }
        return redirect()->route('configure.index')->with('success', 'Client configurations updated successfully!');
    }


    # get laundry teams
    public function getLaundryTeams()
    {
        try {
            $dispatch_domain = $this->checkIfLaundryOnCommon();
            if ($dispatch_domain && $dispatch_domain != false) {

                $unique = Auth::user()->code;

                $client = new GCLIENT([
                    'headers' => [
                        'personaltoken' => $dispatch_domain->laundry_service_key,
                        'shortcode' => $dispatch_domain->laundry_service_key_code,
                        'content-type' => 'application/json'
                    ]
                ]);
                $url = $dispatch_domain->laundry_service_key_url;
                $res = $client->get($url . '/api/get-all-teams');
                $response = json_decode($res->getBody(), true);
                if ($response && $response['message'] == 'success') {
                    return $response['teams'];
                }
            }
        } catch (\Exception $e) {
        }
    }


    public function postUpdateDomain(Request $request, $id)
    {
        $rules = array('custom_domain' => 'required|max:150');
        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $client = Client::where('code', Auth::user()->code)->first();
        // $client->custom_domain = $request->custom_domain;
        // $client->save();
        $id = Auth::user()->code;
        # if submit custom domain by client
        if ($request->custom_domain && $request->custom_domain != $client->custom_domain) {
            try {
                $my_url =   $request->custom_domain;

                $data1 = [
                    'domain' => $my_url
                ];

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "localhost:3000/add_subdomain",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30000,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($data1),
                    CURLOPT_HTTPHEADER => array(
                        "content-type: application/json",
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                $res = json_decode($response);
                if (isset($res->error) && $res->error->statusCode == 400) {
                    $error = isset($res->error->customMessage) ? $res->error->customMessage : 'ERROR';
                    return redirect()->back()->withInput()->withErrors(new \Illuminate\Support\MessageBag(['custom_domain' => $error]));
                }

                $exists = Client::on('god')->where('code', $id)->where('custom_domain', $request->custom_domain)->count();
                if ($exists) {
                    return redirect()->back()->withInput()->withErrors(new \Illuminate\Support\MessageBag(['custom_domain' => 'Domain name "' . $request->custom_domain . '" is not available. Please select a different domain']));
                } else {
                    Client::on('god')->where('code', $id)->update(['custom_domain' => $request->custom_domain]);
                    $dbname = DB::connection()->getDatabaseName();
                    if ($dbname != env('DB_DATABASE')) {
                        Client::where('id', '!=', 0)->update(['custom_domain' => $request->custom_domain]);
                    }
                }
                return redirect()->route('configure.customize')->with('success', 'Client customize data updated successfully!');
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->withErrors(new \Illuminate\Support\MessageBag(['custom_domain' => $e->getMessage()]));
            }
        } else {
            return redirect()->back()->withInput()->withErrors(new \Illuminate\Support\MessageBag(['custom_domain' => 'Domain name "' . $request->custom_domain . '" is already pointed. Please select a different domain']));
        }
    }

    public function updateVerificationOption($data)
    {
        $method_id_arr = $data['method_id'];
        $method_name_arr = $data['method_name'];
        $active_arr = $data['active'];
        if (!empty($method_id_arr)) {
            foreach ($method_id_arr as $key => $id) {
                $saved_creds = VerificationOption::select('credentials')->where('id', $id)->first();
                if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                    $json_creds = $saved_creds->credentials;
                } else {
                    $json_creds = NULL;
                }

                $status = 0;
                $test_mode = 0;
                if ((isset($active_arr[$id])) && ($active_arr[$id] == 'on')) {
                    $status = 1;

                    if ((isset($method_name_arr[$key])) && (strtolower($method_name_arr[$key]) == 'passbase')) {
                        $json_creds = json_encode(array(
                            'publish_key' => $data['passbase_publish_key'],
                            'secret_key'  => $data['passbase_secret_key'],
                        ));
                    }
                }
                VerificationOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);
            }
        }
    }

    public function toggleDatabase(Request $request)
    {

        $default = [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => 'royoorders',
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null
        ];
        Config::set("database.connections.royoorders", $default);
        FacadesDB::setDefaultConnection('royoorders');


        $client = Client::first();


        if ($request->has('db_toggle')) {

            if ($request->db_toggle == '245bae') {
                $client->database_name = 'salesdemo';
            } elseif ($request->db_toggle == '2d98b5') {
                $client->database_name = 'ace';
            }
            $client->save();
            \Illuminate\Support\Facades\Redis::flushall();

            return redirect()->back()->with('success', 'Client settings updated successfully!');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientPreferenceAdditional  $PreferenceAdditional
     * @return \Illuminate\Http\Response
     */
    public function updateAdditional(Request $request)
    {

        $request->merge(['is_cache_enable_for_home' => ($request->has('is_cache_enable_for_home') && $request->is_cache_enable_for_home == 'on') ? 1 : 0]);
        $ret = $this->updatePreferenceAdditional($request);
        if ($ret) {
            return redirect()->back()->with('success', 'Cache updated successfully!');
        }
        // return redirect()->route('configure.index')->with('success', 'Client configurations updated successfully!');
    }

    public function vendorMargConfig($domain, $vendor_id)
    {
        $data['vendorMargConfig'] = VendorMargConfig::where('vendor_id', $vendor_id)->first();
        $data['vendor_id'] = $vendor_id;
        return view('backend.vendor.marg_config', $data);
    }

    public function vendorMargConfigUpdate($domain, Request $request, $vendor_id)
    {
        VendorMargConfig::updateOrCreate([
            'vendor_id' => $vendor_id
        ], [
            'is_marg_enable' => $request->is_marg_enable,
            'marg_company_url' => $request->marg_company_url,
            'marg_company_code' => $request->marg_company_code,
            'marg_access_token' => $request->marg_access_token,
            'marg_decrypt_key' => $request->marg_decrypt_key,
            'marg_date_time' => $request->marg_date_time ?? ''
        ]);

        return back();
    }
    public function resetToDefault(Request $request)
    {
        $client_preference = ClientPreference::select('business_type')->first();

        switch ($client_preference->business_type) {

            case 'taxi':
                $this->resetPickDropConfiguration("pick_drop_check");
                break;

            case 'food_grocery_ecommerce':
                $this->resetDeliveryConfiguration("delivery_check");
                break;

            case 'home_service':
                $this->resetOnDemandConfiguration("on_demand_check");
                break;

            case 'laundry':
                $this->resetLaundryConfiguration("laundry_check");
                break;

            case 'rental':
                $this->resetRentalConfiguration("rental_check");
                break;

            case 'p2p':
                $this->resetP2PConfiguration("p2p_check");
                break;

            case 'emart':
                $this->resetEmartConfiguration("delivery_check");
                break;
            case 'super_app':
                $this->resetSuperAppConfiguration("delivery_check");
                break;

            default:
                break;
        }

        return redirect()->back()->with('success', 'Configuration resetted successfully!');
    }
}
