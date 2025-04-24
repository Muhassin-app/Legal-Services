<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPreference extends Model
{
    protected $fillable = ['client_code', 'theme_admin', 'distance_unit', 'currency_id', 'language_id', 'date_format', 'time_format', 'fb_client_id', 'fb_client_secret', 'fb_client_url', 'twitter_client_id', 'twitter_client_secret', 'twitter_client_url', 'google_client_id', 'google_client_secret', 'google_client_url', 'apple_client_id', 'apple_client_secret', 'apple_client_url', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'map_provider', 'map_key', 'map_secret', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'verify_email', 'verify_phone', 'pharmacy_check', 'web_template_id', 'app_template_id', 'is_hyperlocal', 'need_delivery_service', 'dispatcher_key_1', 'dispatcher_key_2', 'need_dispacher_home_other_service', 'dispacher_home_other_service_key', 'dispacher_home_other_service_key_url', 'dispacher_home_other_service_key_code', 'last_mile_team', 'tip_before_order', 'tip_after_order', 'off_scheduling_at_cart', 'distance_unit_for_time', 'distance_to_time_multiplier', 'business_type', 'need_laundry_service', 'laundry_service_key', 'laundry_service_key_url', 'laundry_service_key_code', 'laundry_pickup_team', 'laundry_dropoff_team', 'delay_order', 'product_order_form', 'sms_credentials', 'is_edit_order_admin', 'is_edit_order_vendor', 'is_edit_order_driver', 'tools_mode', 'map_key_for_app', 'map_key_for_ios_app', 'vendor_fcm_server_key', 'concise_signup', 'slots_with_service_area', 'is_service_area_for_banners', 'stop_order_acceptance_for_users', 'map_on_search_screen', 'is_tax_price_inclusive', 'rating_check', 'dashboard_theme_color', 'enquire_mode', 'pharmacy_check', 'isolate_single_vendor_order', 'subscription_mode', 'subscription_tab_taxi', 'auto_implement_5_percent_tip', 'gifting', 'pickup_delivery_service_area', 'minimum_order_batch', 'static_delivey_fee', 'max_safety_mod', 'hide_order_address', 'category_kyc_documents', 'vendor_return_request', 'hide_order_prepare_time', 'is_cancel_order_user', 'book_for_friend', 'is_static_dropoff', 'is_scan_qrcode_bag', 'is_vendor_tags', "dinein_check", "takeaway_check", "delivery_check", "rental_check", "pick_drop_check", "on_demand_check", "laundry_check", "appointment_check", "p2p_check"];

    protected $casts = [
        'jitsi_preferences' => 'array',
    ];

    public function filling()
    {
        $filling = ['theme_admin', 'distance_unit', 'currency_id', 'date_format', 'time_format', 'fb_client_id', 'fb_client_secret', 'fb_client_url', 'twitter_client_id', 'twitter_client_secret', 'twitter_client_url', 'google_client_id', 'google_client_secret', 'google_client_url', 'apple_client_id', 'apple_client_secret', 'apple_client_url', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'map_provider', 'map_key', 'map_secret', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'verify_email', 'verify_phone', 'web_template_id', 'app_template_id', 'pharmacy_check', 'map_on_search_screen', 'rating_check'];
        return $filling;
    }

    public function language()
    {
        return $this->hasMany('App\Models\ClientLanguage', 'client_code', 'client_code')->select('client_code', 'language_id', 'is_primary')->where('is_active', 1);
    }

    public function primarylang()
    {
        return $this->hasOne('App\Models\ClientLanguage', 'client_code', 'client_code')->select('client_code', 'language_id')->where('is_primary', 1);
    }

    public function currency()
    {
        return $this->hasMany('App\Models\ClientCurrency', 'client_code', 'client_code')->select('client_code', 'currency_id', 'doller_compare')->where('is_primary', 0);
    }


    public function primary()
    {
        return $this->hasone('App\Models\ClientCurrency', 'client_code', 'client_code')->select('client_code', 'currency_id')->where('is_primary', 1);
    }

    public function countries()
    {
        return $this->hasMany('App\Models\ClientCountries', 'client_code', 'client_code')->select('client_code', 'country_id', 'is_primary')->where('is_primary', 0);
    }


    public function primary_country()
    {
        return $this->hasone('App\Models\ClientCountries', 'client_code', 'client_code')->select('client_code', 'country_id')->where('is_primary', 1);
    }


    public function domain()
    {
        return $this->belongsTo('App\Models\Client', 'client_code', 'code')->select('id', 'code', 'custom_domain');
    }

    public function getFaviconAttribute($value)
    {
        $values = array();
        $img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }
    public function getSignupImageAttribute($value)
    {
        $values = array();
        $img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }

    public function getDeliveryiconAttribute($value)
    {
        $values = array();
        $img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }

        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }

    public function getDineiniconAttribute($value)
    {
        $values = array();
        //$img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }

    public function getTakewayiconAttribute($value)
    {
        $values = array();
        //$img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }

    public function getRentaliconAttribute($value)
    {
        $values = array();
        //$img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }
    public function getPickDropiconAttribute($value)
    {
        $values = array();
        //$img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }
    public function getOnDemandiconAttribute($value)
    {
        $values = array();
        //$img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }
    public function getLaundryiconAttribute($value)
    {
        $values = array();
        //$img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }

    public function getP2piconAttribute($value)
    {
        $values = array();
        //$img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }

    public function getappointmenticonAttribute($value)
    {
        $values = array();
        //$img = 'default/default_image.png';
        if (!empty($value)) {
            $img = $value;
        } else {
            return '';
        }
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img) . $ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');

        //$values['small'] = url('showImage/small/' . $img);
        return $values;
    }

    public function client_detail()
    {
        return $this->belongsTo('App\Models\Client', 'client_code', 'code');
    }

    public function client_preferences_additional()
    {
        return $this->hasMany('App\Models\ClientPreferenceAdditional', 'client_code', 'client_code');
    }

    public function additional_preferences()
    {
        return $this->hasMany('App\Models\ClientPreferenceAdditional', 'client_code', 'client_code')->select('id', 'key_name', 'key_value', 'client_code');
    }
}

