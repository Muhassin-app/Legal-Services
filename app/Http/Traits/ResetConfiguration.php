<?php

namespace App\Http\Traits;

use App\Models\AppStyling;
use App\Models\AppStylingOption;
use App\Models\Client;
use App\Models\ClientPreference;
use App\Models\ClientPreferenceAdditional;
use App\Models\WebStylingOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ResetConfiguration
{
    public $additional_preference;

    public function  updateStylingsAndPreferences($web_styling_id, $app_styling_id)
    {



        $web_font = WebStylingOption::where('template_id', $web_styling_id)->where('is_template', 1)->first();
        WebStylingOption::where('id', '!=', $web_font->id)->update(['is_selected' => 0]);
        $web_font = $web_font->fresh();

        $web_font->is_selected = 1;
        $web_font->save();


        $app_font = AppStylingOption::where('template_id', $app_styling_id)->where('is_template', 1)->first();

        AppStylingOption::where('id', '!=', $app_font->id)->update(['is_selected' => 0]);
       
        $app_font = $app_font->fresh();

        $app_font->is_selected = 1;
        $app_font->save();

        $tab_style = AppStyling::where('name', 'Tab Bar Style')->first();
        if ($tab_style) {
            $tab_style_options = AppStylingOption::where('app_styling_id', $tab_style->id)->where('name','Tab 1')->first();
            $tab_style_options->is_selected = 1;
            $tab_style_options->save();
        }
    }

    public function initialize()
    {

        $client_preference = ClientPreference::first();
        $client_preference->hide_order_address = 1;
        $client_preference->celebrity_check = 0;
        $client_preference->is_hyperlocal = 0;


        $vendor_type =  ["dinein_check", "takeaway_check", "delivery_check", "rental_check", "pick_drop_check", "on_demand_check", "laundry_check", "appointment_check", "p2p_check"];

        foreach ($vendor_type as $type) {
            $client_preference->$type = 0;
        }

        $client_preference->save();

        $additional_preference = getAdditionalPreference([
            'is_price_by_role',
            'is_phone_signup',
            'token_currency',
            'is_token_currency_enable',
            'hubspot_access_token',
            'is_hubspot_enable',
            'gtag_id',
            'fpixel_id',
            'is_long_term_service',
            'is_free_delivery_by_roles',
            'is_cab_pooling',
            'is_attribute',
            'is_gst_required_for_vendor_registration',
            'is_baking_details_required_for_vendor_registration',
            'is_advance_details_required_for_vendor_registration',
            'is_vendor_category_required_for_vendor_registration',
            'is_seller_module',
            'is_same_day_delivery',
            'is_next_day_delivery',
            'is_hyper_local_delivery',
            'is_cod_payment',
            'is_prepaid_payment',
            'is_partial_payment',
            'add_to_cart_btn',
            'chat_button',
            'call_button',
            'seller_sold_title',
            'saller_platform_logo',
            'is_tracking_url',
            'is_tracking_sms_url',
            'is_tax_price_inclusive',
            'is_postpay_enable',
            'is_order_edit_enable',
            'order_edit_before_hours',
            'is_gift_card',
            'is_place_order_delivery_zero',
            'is_cust_success_signup_email',
            'is_influencer_refer_and_earn',
            'is_bid_enable',
            'advance_booking_amount',
            'advance_booking_amount_percentage',
            'update_order_product_price',
            'is_bid_ride_enable',
            'is_one_push_book_enable',
            'bid_expire_time_limit_seconds',
            'is_corporate_user',
            'is_user_kyc_for_registration',
            'is_service_product_price_from_dispatch',
            'is_recurring_booking',
            'is_file_cart_instructions',
            'is_admin_vendor_rating',
            'square_enable_status',
            'square_credentials',
            'is_show_vendor_on_subcription',
            'is_enable_compare_product',
            'is_service_price_selection',
            'is_particular_driver',
            'pickup_notification_before',
            'pickup_notification_before_hours',
            'pickup_notification_before2',
            'pickup_notification_before2_hours',
            'is_enable_curb_side',
            'is_map_search_perticular_country',
            'marg_access_token',
            'marg_date_time',
            'is_marg_enable',
            'marg_company_code',
            'marg_decrypt_key',
            'stock_notification_before',
            'stock_notification_qunatity',
            'marg_company_url',
            'is_share_ride_users',
            'is_cache_enable_for_home',
            'cache_reset_time_for_home',
            'cache_radius_for_home',
            'is_enable_allergic_items',
            'is_enable_google_analytics',
            'header_script',
            'footer_script',
            'is_vendor_marg_configuration',
            'marg_cron_schedular_time',
            'is_role_and_permission_enable',
            'is_taxjar_enable',
            'taxjar_testmode',
            'taxjar_api_token',
            'is_lumen_enabled',
            'lumen_domain_url',
            'lumen_access_token',
            'is_rental_weekly_monthly_price',
            'blockchain_route_formation',
            'blockchain_api_domain',
            'blockchain_address_id',
            'is_car_rental_enable',
            'is_gofrugal_enable',
            'gofrugal_enable_status',
            'gofrugal_credentials',
            'is_sms_complete_order',
            'is_sms_cancel_order',
            'is_sms_booked_ride',
            'is_hourly_pickup_rental',
            'add_to_cart_btn',
            'is_product_measurement_in_cm_kg'
        ]);


        $client = Client::first();


        foreach ($additional_preference as $key => $value) {
            $preferenceValue = 0; // Default value

            // Set values based on conditions
            switch ($key) {
                case 'is_cab_pooling':
                    if (in_array($client_preference->business_type, ['taxi', 'super_app'])) {
                        $preferenceValue = 1;
                    }
                    break;
                case 'is_attribute':
                    if (in_array($client_preference->business_type, ['emart', 'super_app', 'p2p', 'rental'])) {
                        $preferenceValue = 1;
                    }
                    break;
                case 'is_tracking_url':
                    if (in_array($client_preference->business_type, ['taxi', 'emart', 'super_app', 'rental', 'food_grocery_ecommerce', 'home_service'])) {
                        $preferenceValue = 1;
                    }
                    break;
                case 'is_long_term_service':
                    if (in_array($client_preference->business_type, ['home_service'])) {
                        $preferenceValue = 1;
                    }
                    break;

                case 'is_tracking_sms_url':
                    if (in_array($client_preference->business_type, ['taxi', 'emart', 'super_app', 'rental', 'food_grocery_ecommerce'])) {
                        $preferenceValue = 1;
                    }
                    break;
                case 'is_place_order_delivery_zero':
                    if (in_array($client_preference->business_type, ['taxi', 'emart', 'super_app', 'rental', 'food_grocery_ecommerce'])) {
                        $preferenceValue = 1;
                    }
                    break;

                case 'is_enable_compare_product':
                    if (in_array($client_preference->business_type, ['emart', 'super_app'])) {
                        $preferenceValue = 1;
                    }
                    break;
                case 'is_free_delivery_by_roles':
                    if (in_array($client_preference->business_type, ['p2p', 'super_app'])) {
                        $preferenceValue = 1;
                    }
                    break;
                case 'add_to_cart_btn':
                    if (in_array($client_preference->business_type, ['p2p', 'super_app'])) {
                        $preferenceValue = 1;
                    }
                    break;

                default:
                    break;
            }

            // Update or create the ClientPreferenceAdditional record
            ClientPreferenceAdditional::updateOrCreate(
                ['key_name' => $key, 'client_code' => $client->code],
                ['key_name' => $key, 'key_value' => $preferenceValue, 'client_code' => $client->code, 'client_id' => $client->id]
            );
        }
    }

    public function  resetDeliveryConfiguration($type)
    {
        $this->initialize();


        $this->updateStylingsAndPreferences(3, 6);

        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        $preference->$type = 1;
        $preference->save();


        $data = [
            'pharmacy_check' => 0,
            'enquire_mode' => 0,
            'isolate_single_vendor_order' => 1,
            'subscription_mode' => 1,
            'subscription_tab_taxi' => 0,
            'tip_before_order' => 1,
            'tip_after_order' => 0,
            'auto_implement_5_percent_tip' => 0,
            'product_order_form' => 0,
            'gifting' => 0,
            'pickup_delivery_service_area' => 0,
            'minimum_order_batch' => 0,
            'static_delivey_fee' => 0,
            'get_estimations' => 0,
            'view_get_estimation_in_category' => 0,
            'max_safety_mod' => 0,
            'hide_order_address' => 0,
            'category_kyc_documents' => 0,
            'vendor_return_request' => 0,
            'hide_order_prepare_time' => 0,
            'is_cancel_order_user' => 1,
            'book_for_friend' => 0,
            'is_static_dropoff' => 0,
            'is_scan_qrcode_bag' => 0,
            'is_vendor_tags' => 0,
            'is_service_area_for_banners' => 0,
            'stop_order_acceptance_for_users' => 0,
            'map_on_search_screen' => 0,
            'slots_with_service_area' => 0,
        ];

        $preference->update($data);

        \Illuminate\Support\Facades\Redis::flushall();
    }

    public function  resetRentalConfiguration($type)
    {
        $this->initialize();
        $this->updateStylingsAndPreferences(1, 9);

        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        $preference->$type = 1;
        $preference->save();

        $data = [
            'enquire_mode' => 0,
            'pharmacy_check' => 0,
            'isolate_single_vendor_order' => 1,
            'subscription_mode' => 1,
            'subscription_tab_taxi' => 0,
            'tip_before_order' => 0,
            'tip_after_order' => 0,
            'auto_implement_5_percent_tip' => 0,
            'product_order_form' => 0,
            'gifting' => 0,
            'pickup_delivery_service_area' => 0,
            'minimum_order_batch' => 0,
            'static_delivey_fee' => 0,
            'get_estimations' => 0,
            'view_get_estimation_in_category' => 0,
            'max_safety_mod' => 0,
            'hide_order_address' => 0,
            'category_kyc_documents' => 0,
            'vendor_return_request' => 0,
            'hide_order_prepare_time' => 0,
            'is_cancel_order_user' => 1,
            'book_for_friend' => 0,
            'is_static_dropoff' => 0,
            'is_scan_qrcode_bag' => 0,
            'is_vendor_tags' => 0,
            'is_service_area_for_banners' => 0,
            'stop_order_acceptance_for_users' => 0,
            'map_on_search_screen' => 0,
            'slots_with_service_area' => 0,
        ];

        $preference->update($data);

        \Illuminate\Support\Facades\Redis::flushall();
    }
    public function  resetPickDropConfiguration($type)
    {
        $this->initialize();
        $this->updateStylingsAndPreferences(3, 4);

        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        $preference->$type = 1;
        $preference->save();

        $data = [
            'enquire_mode' => 0,
            'pharmacy_check' => 0,
            'isolate_single_vendor_order' => 1,
            'subscription_mode' => 1,
            'subscription_tab_taxi' => 0,
            'tip_before_order' => 0,
            'tip_after_order' => 0,
            'auto_implement_5_percent_tip' => 0,
            'product_order_form' => 0,
            'gifting' => 0,
            'pickup_delivery_service_area' => 0,
            'minimum_order_batch' => 0,
            'static_delivey_fee' => 0,
            'get_estimations' => 0,
            'view_get_estimation_in_category' => 0,
            'max_safety_mod' => 0,
            'hide_order_address' => 0,
            'category_kyc_documents' => 0,
            'vendor_return_request' => 0,
            'hide_order_prepare_time' => 0,
            'is_cancel_order_user' => 0,
            'book_for_friend' => 1,
            'is_static_dropoff' => 0,
            'is_scan_qrcode_bag' => 0,
            'is_vendor_tags' => 0,
            'is_service_area_for_banners' => 0,
            'stop_order_acceptance_for_users' => 0,
            'map_on_search_screen' => 0,
            'slots_with_service_area' => 0,
        ];

        $preference->update($data);

        \Illuminate\Support\Facades\Redis::flushall();
    }
    public function  resetOnDemandConfiguration($type)
    {
        $this->initialize();
        $this->updateStylingsAndPreferences(6, 9);

        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        $preference->$type = 1;
        $preference->save();

        $data = [
            'enquire_mode' => 0,
            'pharmacy_check' => 0,
            'isolate_single_vendor_order' => 1,
            'subscription_mode' => 1,
            'subscription_tab_taxi' => 0,
            'tip_before_order' => 0,
            'tip_after_order' => 0,
            'auto_implement_5_percent_tip' => 0,
            'product_order_form' => 0,
            'gifting' => 0,
            'pickup_delivery_service_area' => 0,
            'minimum_order_batch' => 0,
            'static_delivey_fee' => 0,
            'get_estimations' => 0,
            'view_get_estimation_in_category' => 0,
            'max_safety_mod' => 0,
            'hide_order_address' => 0,
            'category_kyc_documents' => 0,
            'vendor_return_request' => 0,
            'hide_order_prepare_time' => 0,
            'is_cancel_order_user' => 0,
            'book_for_friend' => 0,
            'is_static_dropoff' => 0,
            'is_scan_qrcode_bag' => 0,
            'is_vendor_tags' => 0,
            'is_service_area_for_banners' => 0,
            'stop_order_acceptance_for_users' => 0,
            'map_on_search_screen' => 0,
            'slots_with_service_area' => 0,
        ];

        $preference->update($data);

        \Illuminate\Support\Facades\Redis::flushall();
    }
    public function  resetLaundryConfiguration($type)
    {
        $this->initialize();
        $this->updateStylingsAndPreferences(6, 9);

        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        $preference->$type = 1;
        $preference->save();

        $data = [
            'enquire_mode' => 0,
            'pharmacy_check' => 0,
            'isolate_single_vendor_order' => 1,
            'subscription_mode' => 0,
            'subscription_tab_taxi' => 0,
            'tip_before_order' => 0,
            'tip_after_order' => 0,
            'auto_implement_5_percent_tip' => 0,
            'product_order_form' => 0,
            'gifting' => 0,
            'pickup_delivery_service_area' => 0,
            'minimum_order_batch' => 0,
            'static_delivey_fee' => 0,
            'get_estimations' => 0,
            'view_get_estimation_in_category' => 0,
            'max_safety_mod' => 0,
            'hide_order_address' => 0,
            'category_kyc_documents' => 0,
            'vendor_return_request' => 0,
            'hide_order_prepare_time' => 0,
            'is_cancel_order_user' => 0,
            'book_for_friend' => 0,
            'is_static_dropoff' => 0,
            'is_scan_qrcode_bag' => 0,
            'is_vendor_tags' => 0,
            'is_service_area_for_banners' => 0,
            'stop_order_acceptance_for_users' => 0,
            'map_on_search_screen' => 0,
            'slots_with_service_area' => 0,
        ];

        $preference->update($data);

        \Illuminate\Support\Facades\Redis::flushall();
    }

    public function  resetP2PConfiguration($type)
    {
        $this->initialize();
        $this->updateStylingsAndPreferences(9, 8);

        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        $preference->$type = 1;
        $preference->save();

        $data = [
            'enquire_mode' => 0,
            'pharmacy_check' => 0,
            'isolate_single_vendor_order' => 1,
            'subscription_mode' => 1,
            'subscription_tab_taxi' => 0,
            'tip_before_order' => 0,
            'tip_after_order' => 0,
            'auto_implement_5_percent_tip' => 0,
            'product_order_form' => 0,
            'gifting' => 0,
            'pickup_delivery_service_area' => 1,
            'minimum_order_batch' => 0,
            'static_delivey_fee' => 0,
            'get_estimations' => 0,
            'view_get_estimation_in_category' => 0,
            'max_safety_mod' => 0,
            'hide_order_address' => 0,
            'category_kyc_documents' => 0,
            'vendor_return_request' => 0,
            'hide_order_prepare_time' => 0,
            'is_cancel_order_user' => 1,
            'book_for_friend' => 0,
            'is_static_dropoff' => 0,
            'is_scan_qrcode_bag' => 0,
            'is_vendor_tags' => 0,
            'is_service_area_for_banners' => 0,
            'stop_order_acceptance_for_users' => 0,
            'map_on_search_screen' => 0,
            'slots_with_service_area' => 0,
        ];

        $preference->update($data);

        \Illuminate\Support\Facades\Redis::flushall();
    }
    public function  resetEmartConfiguration($type)
    {
        $this->initialize();
        $this->updateStylingsAndPreferences(8, 10);

        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        $preference->$type = 1;
        $preference->save();


        $data = [
            'enquire_mode' => 0,
            'pharmacy_check' => 0,
            'isolate_single_vendor_order' => 0,
            'subscription_mode' => 1,
            'subscription_tab_taxi' => 0,
            'tip_before_order' => 1,
            'tip_after_order' => 0,
            'auto_implement_5_percent_tip' => 0,
            'product_order_form' => 0,
            'gifting' => 0,
            'pickup_delivery_service_area' => 0,
            'minimum_order_batch' => 0,
            'static_delivey_fee' => 0,
            'get_estimations' => 0,
            'view_get_estimation_in_category' => 0,
            'max_safety_mod' => 0,
            'hide_order_address' => 0,
            'category_kyc_documents' => 0,
            'vendor_return_request' => 1,
            'hide_order_prepare_time' => 0,
            'is_cancel_order_user' => 1,
            'book_for_friend' => 0,
            'is_static_dropoff' => 0,
            'is_scan_qrcode_bag' => 0,
            'is_vendor_tags' => 0,
            'is_service_area_for_banners' => 0,
            'stop_order_acceptance_for_users' => 0,
            'map_on_search_screen' => 0,
            'slots_with_service_area' => 0,
        ];

        $preference->update($data);

        \Illuminate\Support\Facades\Redis::flushall();
    }
    public function  resetSuperAppConfiguration($type)
    {


        $this->initialize();
        $this->updateStylingsAndPreferences(3, 3);

        $preference = ClientPreference::where('client_code', Auth::user()->code)->first();
        $preference->$type = 1;
        $preference->save();
        $data = [
            'enquire_mode' => 0,
            'pharmacy_check' => 0,
            'isolate_single_vendor_order' => 1,
            'subscription_mode' => 1,
            'subscription_tab_taxi' => 0,
            'tip_before_order' => 1,
            'tip_after_order' => 0,
            'auto_implement_5_percent_tip' => 0,
            'product_order_form' => 0,
            'gifting' => 0,
            'pickup_delivery_service_area' => 0,
            'minimum_order_batch' => 0,
            'static_delivey_fee' => 0,
            'get_estimations' => 0,
            'view_get_estimation_in_category' => 0,
            'max_safety_mod' => 0,
            'hide_order_address' => 0,
            'category_kyc_documents' => 0,
            'vendor_return_request' => 1,
            'hide_order_prepare_time' => 0,
            'is_cancel_order_user' => 1,
            'book_for_friend' => 1,
            'is_static_dropoff' => 0,
            'is_scan_qrcode_bag' => 0,
            'is_vendor_tags' => 0,
            'is_service_area_for_banners' => 0,
            'stop_order_acceptance_for_users' => 0,
            'map_on_search_screen' => 0,
            'slots_with_service_area' => 0,
        ];

        $preference->update($data);

        \Illuminate\Support\Facades\Redis::flushall();
    }
}
