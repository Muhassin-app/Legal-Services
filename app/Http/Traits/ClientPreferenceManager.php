<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\{ClientPreference,ClientPreferenceAdditional,Client, ProductDeliveryFeeByRole, Product};
use GuzzleHttp\Client as GCLIENT;
use Log;
use Storage;
use Illuminate\Support\Facades\Cache;
use App\Http\Traits\GuzzleHttpTrait;


trait ClientPreferenceManager{

  use GuzzleHttpTrait;

  public $client_preference_fillable_key = ['is_price_by_role','is_phone_signup', 'token_currency', 'is_token_currency_enable', 'hubspot_access_token', 'is_hubspot_enable', 'gtag_id', 'fpixel_id','is_long_term_service', 'is_free_delivery_by_roles', 'is_cab_pooling', 'is_attribute', 'is_gst_required_for_vendor_registration', 'is_baking_details_required_for_vendor_registration', 'is_advance_details_required_for_vendor_registration', 'is_vendor_category_required_for_vendor_registration', 'is_seller_module', 'is_same_day_delivery', 'is_next_day_delivery', 'is_hyper_local_delivery', 'is_cod_payment', 'is_prepaid_payment', 'is_partial_payment', 'add_to_cart_btn', 'chat_button', 'call_button', 'seller_sold_title','saller_platform_logo','is_tracking_url','is_tracking_sms_url', 'is_tax_price_inclusive', 'is_postpay_enable', 'is_order_edit_enable', 'order_edit_before_hours','is_gift_card', 'is_place_order_delivery_zero', 'is_cust_success_signup_email','is_influencer_refer_and_earn','is_bid_enable','advance_booking_amount','advance_booking_amount_percentage','update_order_product_price', 'is_bid_ride_enable', 'is_one_push_book_enable', 'bid_expire_time_limit_seconds',  'is_corporate_user', 'is_user_kyc_for_registration','is_service_product_price_from_dispatch','is_recurring_booking','is_file_cart_instructions','is_admin_vendor_rating', 'square_enable_status', 'square_credentials','is_show_vendor_on_subcription','is_enable_compare_product','is_service_price_selection','is_particular_driver', 'pickup_notification_before', 'pickup_notification_before_hours','pickup_notification_before2', 'pickup_notification_before2_hours','is_enable_curb_side','is_map_search_perticular_country','marg_access_token','marg_date_time', 'is_marg_enable', 'marg_company_code', 'marg_decrypt_key','stock_notification_before','stock_notification_qunatity','marg_company_url','is_share_ride_users','is_cache_enable_for_home','cache_reset_time_for_home','cache_radius_for_home','is_enable_allergic_items','is_enable_google_analytics','header_script','footer_script','is_vendor_marg_configuration','marg_cron_schedular_time','is_role_and_permission_enable','is_taxjar_enable','taxjar_testmode','taxjar_api_token','is_lumen_enabled','lumen_domain_url','lumen_access_token','is_rental_weekly_monthly_price','blockchain_route_formation','blockchain_api_domain','blockchain_address_id','is_car_rental_enable','is_gofrugal_enable','gofrugal_enable_status','gofrugal_credentials','is_sms_complete_order','is_sms_cancel_order','is_sms_booked_ride','is_product_measurement_in_cm_kg','enable_pwa','is_freelance_on_homepage','vendor_online_status','distance_matrix_app_status', 'cart_cms_page_status','document_report','product_measurment'];

  # get last mile teams
  public function getLastMileTeams(){
    try {
      $dispatch_domain = $this->checkIfLastMileOn();
      if ($dispatch_domain && $dispatch_domain != false) {

        $unique = Auth::user()->code;
        //use Guzzle for send request at other panel
        $endPoints = '/api/get-all-teams';
        $response = $this->guzzleGet($endPoints,$dispatch_domain,);

        if($response && $response['message'] == 'success'){
            return $response['teams'];
        }
      }
    }
    catch(\Exception $e){
    }
  }
  # check if last mile delivery on
  public function checkIfLastMileOn(){
    $preference = ClientPreference::first();
    if($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
      return $preference;
    else
      return false;
  }

  /**
   * updatePreferenceAdditional
   *
   * @param  mixed $$request
   * @return void
   * harbans :)
   *
   */
  public function updatePreferenceAdditional($request){
    $validated_keys = $request->only($this->client_preference_fillable_key);
    $client = Client::first();
    $cacheKey = 'client_preferences_additional_'.json_encode($this->client_preference_fillable_key);
    // dd($cacheKey);

    Cache::forget($cacheKey);
    foreach($validated_keys as $key => $value){
      if ($key == 'saller_platform_logo') {
        if ($request->hasFile('saller_platform_logo')) { /* upload logo file */
          $file = $request->file('saller_platform_logo');
          $value = $this->uploadFile($file);
        }
      }
      // $value = "1";
      if($value === 0){
        $value = "0";
      }
      if($value == "on"){
        $value = "1";
      }

    Cache::forget('client_preferences_additional_["'.$key.'"]');

    $id =  ClientPreferenceAdditional::updateOrCreate(
          ['key_name' => $key, 'client_code' => $client->code],
          ['key_name' => $key, 'key_value' => $value,'client_code' => $client->code,'client_id'=> $client->id]);
    }

    if($request->has('enable_pwa')){
      if($request->has('enable_pwa_switch'))
      {

        ClientPreferenceAdditional::updateOrCreate(
          ['key_name' => 'enable_pwa', 'client_code' => $client->code],
          ['key_name' => 'enable_pwa', 'key_value' => $request->has('enable_pwa') ? 1 : 0,'client_code' => $client->code,'client_id'=> $client->id]);

      }
       else{

        ClientPreferenceAdditional::updateOrCreate(
          ['key_name' => 'enable_pwa', 'client_code' => $client->code],
          ['key_name' => 'enable_pwa', 'key_value' => 0,'client_code' => $client->code,'client_id'=> $client->id]);
       }
      }


    if($request->has('is_blockchain_route')){
    if($request->has('blockchain_route_formation_switch'))
    {

      ClientPreferenceAdditional::updateOrCreate(
        ['key_name' => 'blockchain_route_formation', 'client_code' => $client->code],
        ['key_name' => 'blockchain_route_formation', 'key_value' => $request->has('blockchain_route_formation_switch') ? 1 : 0,'client_code' => $client->code,'client_id'=> $client->id]);
      ClientPreferenceAdditional::updateOrCreate(
          ['key_name' => 'blockchain_api_domain', 'client_code' => $client->code],
          ['key_name' => 'blockchain_api_domain', 'key_value' =>$request->input('blockchain_api_domain') ?? null,'client_code' => $client->code,'client_id'=> $client->id]);
      ClientPreferenceAdditional::updateOrCreate(
            ['key_name' => 'blockchain_address_id', 'client_code' => $client->code],
            ['key_name' => 'blockchain_address_id', 'key_value' => $request->input('blockchain_address_id') ?? null,'client_code' => $client->code,'client_id'=> $client->id]);

    }
     else{

      ClientPreferenceAdditional::updateOrCreate(
        ['key_name' => 'blockchain_route_formation', 'client_code' => $client->code],
        ['key_name' => 'blockchain_route_formation', 'key_value' => 0,'client_code' => $client->code,'client_id'=> $client->id]);
     }
    }

    return 1;
  }

  public function updateFreeDeliveryForRoles($apply_free_del_arr){

    if(isset($apply_free_del_arr) && count($apply_free_del_arr) > 0){
      $products = Product::where('is_live', 1)->get();

      $save_data = [];
      ProductDeliveryFeeByRole::where('is_free_delivery', 1)->delete();
      foreach($apply_free_del_arr as $apply_free_del){


        foreach($products as $product){
          array_push($save_data,[ 'product_id' => $product->id, 'role_id' => $apply_free_del, 'is_free_delivery' => 1 ] );
        }

      }

      ProductDeliveryFeeByRole::insert($save_data);

    }

    return 1;
  }
  public function uploadFile($file){
      return Storage::disk('s3')->put('/vendor', $file, 'public');
  }

  function getAdditionalPreference($key=array()){
    setUserCode();
    $return = [];
    $dbreturn= [];
    if(sizeof($key)){
        $result = ClientPreferenceAdditional::select('key_name','key_value')->whereIn('key_name',$key)->get();
        $return = array_column($result->toArray(), 'key_value', 'key_name');
        if (sizeof($result)) {
            $dbreturn = array_column($result->toArray(), 'key_value', 'key_name');
        }
        $emp = array_diff($key, array_keys($dbreturn));
        $emptyArr = array_fill_keys($emp, 0);
        $return = array_merge($emptyArr, $dbreturn);
    }
    return $return;
}
}
