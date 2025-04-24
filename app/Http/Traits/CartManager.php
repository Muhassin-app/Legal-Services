<?php
namespace App\Http\Traits;

use App\Http\Controllers\Front\{PromoCodeController,CartController, FrontController};
use App\Models\CaregoryKycDoc;
use App\Models\{Cart, Nomenclature, NomenclatureTranslation, ProcessorProduct, UserGiftCard};
use App\Models\CartDeliveryFee;
use App\Models\CartProduct;
use App\Models\CartProductPrescription;
use App\Models\CategoryKycDocuments;
use App\Models\ClientCurrency;
use App\Models\ClientPreference;
use App\Models\Country;
use App\Models\LoyaltyCard;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductFaq;
use App\Models\SubscriptionInvoicesUser;
use App\Models\TaxRate;
use App\Models\UserAddress;
use App\Models\Vendor;
use App\Models\VendorDineinTable;
use App\Models\VendorMinAmount;
use Auth, Log;
use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\{ProductTrait};
use App\Models\WebStylingOption;

trait cartManager{
  use ProductTrait;
  public function config()
  {
    $this->user = auth()->user();
    $countries = Country::get();
    $this->language = session()->get('customerLanguage')??'1';
    $this->currencyId = session()->get('customerCurrency');
    $this->customerCurrency = ClientCurrency::where('currency_id', $this->currencyId)->first();
    if($this->user)
    {
        $this->user_allAddresses = UserAddress::where('user_id', $this->user->id)->where('status',1)->orderBy('is_primary','Desc')->get();
    }
    $this->preferences = ClientPreference::with(['client_detail:id,code,country_id'])->first();
    $this->additionalPreferences = (object)getAdditionalPreference(['is_tax_price_inclusive']);
  }



  public function getCartProductsNew(Request $request)
    {
        $this->config();
        Session()->forget('vendorType');
        Session()->put('vendorType', $request->type);
        $cart_details = [];
        $user = $this->user;
        if ($user) {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
        } else {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
        }

        if ($cart) {
            $cart_details = $this->getCarts($cart);
        }

        if ($cart_details && !empty($cart_details)) {
              return json_encode([
                'data' => $cart_details,
            ]);
        }


        return json_encode([
            'message' => "No product found in cart",
            'data' => $cart_details,
        ]);
    }



  public function cartVendorOtherTaxes()
  {

  }

  public function getUserAddress($user,$address_id=0)
  {
     /*Getting User Address */
     if($user){
          if($address_id > 0){
              $address = UserAddress::where('user_id', $user)->where('id', $address_id)->first();
          }else{
              $address = UserAddress::where('user_id', $user)->where('is_primary', 1)->where('status',1)->first();
              //$address_id = ($address) ? $address->id : 0;
          }
          return $address;
      }
      return [];
  }

  public function productAddons()
  {

  }

  public function vendorDeliveryFees()
  {

  }

  public function getVendorServiceArea($address_id,$vendorData)
  {
    $serviceArea = null;
    if($address_id > 0){
    $address = UserAddress::where('user_id', $this->user->id)->where('id', $address_id)->first();
            $latitude = $address->latitude;
            $longitude = $address->longitude;
            if (!empty($latitude) && !empty($longitude)) {

                $serviceArea = $vendorData->vendor->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                    $query->select('vendor_id')
                ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(".$latitude." ".$longitude.")'))");
                })->where('id', $vendorData->vendor_id)->get();
            }
        }
        return $serviceArea;

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


  public function userSubscription($userId)
  {
    $now = Carbon::now()->toDateTimeString();
    $user_subscription = SubscriptionInvoicesUser::with('features')
        ->select('id', 'user_id', 'subscription_id')
        ->where('user_id', $userId)
        ->where('end_date', '>', $now)
        ->orderBy('end_date', 'desc')->first();
    return $user_subscription;
  }

  public function calCulateSubscriptionDiscount($userId, $deliveryCharges=0, $payable_amount=0, $vendor_subs_disc_percent)
  {
    $user_subscription = $this->userSubscription($userId);
    $off_percent_discount_vendor_total = $off_percent_discount_admin_total = $delivery_discount_total = 0;
    if ($user_subscription) {
        foreach ($user_subscription->features as $feature) {
            if ($feature->feature_id == 1) {
                $delivery_discount_total = $delivery_discount_total + $deliveryCharges;
            }
            elseif ($feature->feature_id == 2) {
                if($vendor_subs_disc_percent > 0){
                    $off_percent_discount_vendor_total = $off_percent_discount_vendor_total + ($vendor_subs_disc_percent * $payable_amount / 100);
                }
                $off_percent_discount_admin = ($feature->percent_value * $payable_amount / 100);
                $off_percent_discount_admin_total = $off_percent_discount_admin_total + $off_percent_discount_admin;
            }
        }
        if($off_percent_discount_vendor_total >= $off_percent_discount_admin_total){
            $off_percent_discount_admin_total = 0;
        }else{
            $off_percent_discount_admin_total = $off_percent_discount_admin_total - $off_percent_discount_vendor_total;
        }
    }
    $subscription_discount_arr = array('admin' => $off_percent_discount_admin_total, 'vendor' => $off_percent_discount_vendor_total, 'delivery_discount' => $delivery_discount_total);
    return $subscription_discount_arr;
  }


  public function getOrderLoyalityAmount($user,$customerCurrency = '')
  {
    $customerCurrency = $customerCurrency??$this->customerCurrency;
    $loyalty_amount_saved = 0;
    $loyalty_points_used = 0;
    $redeem_points_per_primary_currency = '';
    $loyalty_card = LoyaltyCard::where('status', '=', '0')->first();
    if ($loyalty_card) {
        $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
    }

    $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
    $balanced_points = ($order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used);

    $result = LoyaltyCard::where('minimum_points','<=', $balanced_points)->where('status', '=', '0')->orderBy('minimum_points', 'desc')->value('minimum_points');
    /* Getting All User Subscription plans */
    $subscription_features = array();
    $user_subscription = null;

    if ($order_loyalty_points_earned_detail && $result) {
        $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
        if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
            $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
            if( ($customerCurrency) && ($customerCurrency->is_primary != 1) ){
                $loyalty_amount_saved = $loyalty_amount_saved * $customerCurrency->doller_compare;
            }
        }
    }

    return (object)array('loyalty_amount_saved'=>$loyalty_amount_saved??0,'loyalty_points_used'=>$loyalty_points_used??0);

  }

  public function getAllOthertaxes($vendorData,$taxChargeable,$taxCharges)
    {
        $taxRates = $this->getTaxes();
        if(!empty($taxRates)){
            $delivery_charges_tax_rate = 0;
            if($vendorData->vendor->delivery_charges_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->delivery_charges_tax_id])){
                     $delivery_charges_tax_rate=$taxRates[$vendorData->vendor->delivery_charges_tax_id]['tax_rate'];
                }
            }

            $container_charges_tax_rate = 0;
            if($vendorData->vendor->container_charges_tax_id!=null){
                    $container_charges_tax_rate=$taxRates[$vendorData->vendor->container_charges_tax_id]['tax_rate'];
            }

            $fixed_fee_tax_rate = 0;
            if($vendorData->vendor->fixed_fee_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->fixed_fee_tax_id])){
                     $fixed_fee_tax_rate=$taxRates[$vendorData->vendor->fixed_fee_tax_id]['tax_rate'];
                }
            }

            // //\Log::info($vendorData);
            $service_charges_tax_rate = 0;
            if($vendorData->vendor->service_charges_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->service_charges_tax_id])){
                       $service_charges_tax_rate=$taxRates[$vendorData->vendor->service_charges_tax_id]['tax_rate'];
                }
            }

            $markup_price_tax_rate = 0;
            if($vendorData->vendor->markup_price_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->markup_price_tax_id])){
                    $markup_price_tax_rate=$taxRates[$vendorData->vendor->markup_price_tax_id]['tax_rate'];
                }
            }

            $deliveryCharges =  $taxChargeable['deliveryCharges'];
            $vendor_service_fee_percentage_amount =  $taxChargeable['vendor_service_fee_percentage_amount'];
            $total_fixed_fee_amount =  $taxChargeable['total_fixed_fee_amount'];
            $total_markup_charges =  $taxChargeable['total_markup_charges'];
            $total_container_charges =  $taxChargeable['total_container_charges'];


            if(!$this->additionalPreferences->is_tax_price_inclusive)
            {
                if($vendorData->vendor->delivery_charges_tax)
                $taxCharges['deliver_fee_charges'] =  $deliveryCharges * $delivery_charges_tax_rate/100;

                if($vendorData->vendor->container_charges_tax)
                $taxCharges['container_charges_tax'] =  $total_container_charges * $container_charges_tax_rate/100;

                if($vendorData->vendor->service_charges_tax)
                $taxCharges['total_service_fee'] =  $vendor_service_fee_percentage_amount * $service_charges_tax_rate/100;

                if($vendorData->vendor->fixed_fee_tax)
                $taxCharges['total_fixed_fee_tax'] =  $total_fixed_fee_amount * $fixed_fee_tax_rate/100;

                if($vendorData->vendor->add_markup_price)
                $taxCharges['total_markup_fee_tax'] =  $total_markup_charges * $markup_price_tax_rate/100;
            }else{

                if($vendorData->vendor->delivery_charges_tax)
                $taxCharges['deliver_fee_charges'] =  ($deliveryCharges * $delivery_charges_tax_rate)/(100 + $delivery_charges_tax_rate);

                if($vendorData->vendor->container_charges_tax)
                $taxCharges['container_charges_tax'] =  ($total_container_charges * $container_charges_tax_rate)/(100 + $container_charges_tax_rate);

                if($vendorData->vendor->service_charges_tax)
                $taxCharges['total_service_fee'] =  ($vendor_service_fee_percentage_amount * $service_charges_tax_rate)/(100 + $service_charges_tax_rate);

                if($vendorData->vendor->fixed_fee_tax)
                $taxCharges['total_fixed_fee_tax'] =  ($total_fixed_fee_amount * $fixed_fee_tax_rate)/(100 + $fixed_fee_tax_rate);

                if($vendorData->vendor->add_markup_price)
                $taxCharges['total_markup_fee_tax'] =  ($total_markup_charges * $markup_price_tax_rate)/(100 + $markup_price_tax_rate);

            }


            return (object)$taxCharges;


        }

        return [];



    }


      /**
       * Get Cart Items
       *
       */
      public function getCartsNew($cart, $address_id=0 , $code = 'D',$schedule_datetime_del='')
      {
        $processorProduct = [];
        $this->config();
        $address = [];
        $category_array = [];
        $cart_id = $cart->id;
        $user = $this->user;
        $langId = $this->language;
        $curId = $this->currencyId;
        $preferences = $this->preferences;
        $countries = Country::get();
        $cart->pharmacy_check = $preferences->pharmacy_check;
        $customerCurrency = $this->customerCurrency;
        $nowdate = Carbon::now()->toDateTimeString();
        $latitude = '';
        $longitude = '';
        $user_allAddresses = collect();
        $upSell_products = collect();
        $crossSell_products = collect();
        $couponGetAmount=0;
        $loyalty_amount_saved = 0;
        $additionalPreference = getAdditionalPreference(['is_token_currency_enable','token_currency','is_price_by_role','is_service_product_price_from_dispatch','is_service_price_selection']);
        $client_timezone = DB::table('clients')->first('timezone');
        $user_timezone = $client_timezone->timezone ?? 'Asia/Kolkata';
        
        $giftCardUsed = 0;
        $giftCardAmount = 0;
        $security_amount = 0.00;

        $is_recurring_booking = 0;
        $action = (session()->has('vendorType')) ? session()->get('vendorType') : 'delivery';
        $is_service_product_price_from_dispatch = 0;
        $getOnDemandPricingRule = getOnDemandPricingRule($action, (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
        if($getOnDemandPricingRule['is_price_from_freelancer']==1){
            $is_service_product_price_from_dispatch =1;
        }

        if($user){
            $user_timezone =  $user->timezone ?? $user_timezone  ;
            //Get User Address Details
            $address = $this->getUserAddress($user->id,$address_id);
        }
        /* Getting User Lat Long */
        $latitude = ($address) ? $address->latitude : '';
        $longitude = ($address) ? $address->longitude : '';

        /* Delete Cart product if dont exists*/
        $delifproductnotexist = CartProduct::where('cart_id', $cart_id)->doesntHave('product')->delete();

        /* Getting All Cart Data */
        $cartData = CartProduct::with([
            'vendor','vendor.slots','vendor.slot.day', 'vendor.slotsForPickup', 'vendor.slotsForDropoff', 'vendor.slotDate', 'coupon' => function ($qry) use ($cart_id) {
                $qry->where('cart_id', $cart_id);
            }, 'vendorProducts.pvariant.media.pimage.image', 'vendorProducts.product.media.image','vendorProducts.productDeliverySlot', 'vendorProducts.productVariantByRoles','vendorProducts.pvariant.vset.variantDetail.trans' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.pvariant.vset.optionData.trans' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.product.translation_one' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                $q->where('language_id', $langId);
            },
            'vendorProducts' => function ($qry) use ($cart_id) {
                $qry->where('cart_id', $cart_id);
            },
            'vendorProducts.addon.set' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.product.categoryName' => function ($q) use ($langId) {
                $q->select('category_id', 'name');
                $q->where('language_id', $langId);
            },'vendorProducts.product.productcategory'=> function ($q1)  {
                $q1->select('id', 'type_id');
            },

            'vendorProducts.addon.option' => function ($qry) use ($langId) {
                $qry->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                $qry->select('addon_options.id', 'addon_options.price', 'apt.title', 'addon_options.addon_id', 'apt.language_id');
                $qry->where('apt.language_id', $langId)->groupBy(['addon_options.id', 'apt.language_id']);
                // $qry->where('language_id', $langId);
            }, 'vendorProducts.product.taxCategory.taxRate',
        ]);
        
       // $cartData = $cartData->select('vendor_id', 'luxury_option_id', 'vendor_dinein_table_id', 'id as cart_product_id', 'schedule_type', 'scheduled_date_time', 'schedule_slot','total_booking_time','product_id','cart_id','delivery_date','slot_price','slot_id')->where('status', [0, 1])->where('cart_id', $cart_id)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();
        // dd($cartData);
       //Get All Taxes    


        $cartData = $cartData->select('vendor_id', 'luxury_option_id', 'vendor_dinein_table_id', 'id as cart_product_id', 'schedule_type', 'scheduled_date_time', 'schedule_slot','total_booking_time','product_id','cart_id','recurring_booking_type','recurring_week_day','recurring_week_type','recurring_day_data','recurring_booking_time','delivery_date', 'slot_price', 'slot_id','dispatch_agent_id')->where('status', [0, 1])->where('cart_id', $cart_id)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();


       //Get All Taxes
       $taxRates = $this->getTaxes();

        $taxCharges = array();
        $taxCharges['deliver_fee_charges'] = 0;
        $taxCharges['total_service_fee'] = 0;
        $taxCharges['total_fixed_fee_tax'] = 0;
        $taxCharges['total_markup_fee_tax'] = 0;

        /* Getting All User Subscription plans */
        $subscription_features = array();
        $user_subscription = null;
        if($user){
          //Get earn and used loyalty amount
          $loyaltyCheck = $this->getOrderLoyalityAmount($user);
          $loyalty_amount_saved = $loyaltyCheck->loyalty_amount_saved;
          //d Get user subscription
          $user_subscription = $this->userSubscription($user->id);
          $cart->scheduled_date_time = convertDateTimeInTimeZone($cart->scheduled_date_time, $user->timezone, 'Y-m-d\TH:i');
        }
        $total_payable_amount = $total_subscription_discount_admin = $total_subscription_discount_vendor = $total_subscription_discount_delivery = $total_discount_amount = $total_discount_percent = $total_taxable_amount = $deliver_charges_lalmove = $total_fixed_fee_amount = 0.00;
        /* If cart have data then getting total and other variable set */
        if ($cartData) {
            $addon_price=0;
            $cart_dinein_table_id = NULL;
          
            $vendor_details = [];
            $delivery_status = 1;
            $is_vendor_closed = 0;
            $closed_store_order_scheduled = 0;
            $deliver_charge = 0;
            $delivery_status_message = '';
            $deliveryCharges = 0;
            $is_recurring_cart = 0;
            $totalMarkup = 0;
            $delay_date = 0;
            $pickup_delay_date = 0;
            $dropoff_delay_date = 0;
            $sub_total = 0;
            $total_service_fee = 0;
            $product_out_of_stock = 0;
            $PromoDelete = 0;
            $d = 0;
            $total_container_charges = 0 ;
            $total_deliver_charges = 0 ;
            $total_markup_charges = 0;
            $total_quantity = 0;
            $bid_total_discount = 0;
            $deliveryCharges_real = 0;

            $delivery_slot_amount = 0;
            $is_long_term_service = 0;
            $container_charges_tax = 0;

            //multivendor delivery charges
            $delivery_fees = 0;
            
            $deliver_fee_charges = 0;
            $total_fixed_fee_tax = 0;
            $total_service_fee = 0;
            $total_markup_fee_tax = 0;
            $container_charges_tax = 0;
            $processorProduct     = array();

            // if(!empty($user)){
            //     $client_timezone = DB::table('clients')->first('timezone');
            //     $user->timezone = $user->timezone ?? $client_timezone->timezone;
            //     $user_timezone = $user->timezone;
            // }
          // $sub_total+=$opt_price_in_currency;
            /* Getting in vendor loop */
            $quantity_role_price = [];
            foreach ($cartData as $ven_key => $vendorData) {
                $opt_quantity_price_new = 0.00;
                $addon_price=0;
                $total_fixed_fee_amount =$total_fixed_fee_amount+ $vendorData->vendor->fixed_fee_amount;
                $is_promo_code_available = 0;
                $vendor_products_total_amount = $payable_amount = $taxable_amount = $subscription_discount = $discount_amount = $discount_percent = $deliver_charge = $delivery_fee_charges = $delivery_fee_charges_static =  $deliver_charges_lalmove = 0.00;
                $delivery_count = 0;
                $delivery_count_lm = 0;
                $coupon_amount_used = 0;
                $coupon_apply_price=0;
                $slotsCnt = 0;
                $PromoFreeDeliver = 0;
                $subscription_discount_admin     = 0;
                $subscription_discount_delivery  = 0;
                $subscription_discount_vendor    = 0;
               // if(!empty($user)){
                    $scheduledDateTime = dateTimeInUserTimeZone($vendorData->scheduled_date_time, $user_timezone);
                    $vendorData->scheduled_date_time = date('Y-m-d',strtotime($scheduledDateTime)) ;
                // }else{
                //     $vendorData->scheduled_date_time = date('Y-m-d',strtotime($vendorData->scheduled_date_time)) ;
                // }
                    $slotsRes = getShowSlot($vendorData->scheduled_date_time,$vendorData->vendor_id,'delivery',"60",0,'',$cart_id);

                $slots = (object)$slotsRes['slots'];
                // this variable for get slot from dispatc
                $slotsdate = $slotsRes['date'];
                $slotcount =count((array)$slots);

                $vendor_latitude = $vendorData->vendor->latitude ?? 30.71728880;
                $vendor_longitude =  $vendorData->vendor->longitude ?? 76.80350870;


                if($cartData->count() > 1 || in_array($action,['appointment','on_demand']) ){
                    $vendorData->selected_slot = $vendorData->schedule_slot;
                }
              
             
                $vendorData->slotsdate = $slotsdate;
                $vendorData->slots = $slots;
                $vendorData->slotsCnt =  $slotcount ;
                $vendorData->delay_date = date('Y-m-d');

                if(session()->has('vendorTable')) {
                    if((session()->has('vendorTableVendorId')) && (session()->get('vendorTableVendorId') == $vendorData->vendor_id)){
                        $cart_dinein_table_id = session()->get('vendorTable');
                    }
                    session()->forget(['vendorTable', 'vendorTableVendorId']);
                } else {
                    $cart_dinein_table_id = $vendorData->vendor_dinein_table_id;
                }

                /* Getting vendor details */
                if($action != 'delivery') {
                    $vendor_details['vendor_address'] = $vendorData->vendor->select('id','latitude','longitude','address')->where('id', $vendorData->vendor_id)->first();
                    if($action == 'dine_in') {
                        $vendor_tables = VendorDineinTable::where('vendor_id', $vendorData->vendor_id)->with('category')->get();
                        foreach ($vendor_tables as $vendor_table) {
                            $vendor_table->qr_url = url('/vendor/'.$vendorData->vendor->slug.'/?id='.$vendorData->vendor_id.'&name='.$vendorData->vendor->name.'&table='.$vendor_table->id);
                        }
                        $vendor_details['vendor_tables'] = $vendor_tables;
                    }
                }
                else {
                    if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) ){
                        //Get VendorService Area
                        $serviceArea = $this->getVendorServiceArea($address_id,$vendorData);
                    }
                }
                //dd($serviceArea);
                Session()->put('vid','');
                //get Coupon Discount for product case
                $coupon_product_ids = [];
                $coupon_vendor_ids = [];
                $coupon_product_discount = 0;
                $in_or_not = 0;
                if (isset($vendorData->coupon) && !empty($vendorData->coupon) && isset($vendorData->coupon->promo) && !empty($vendorData->coupon->promo)){
                    if($vendorData->coupon->promo->restriction_on == 0)
                    {
                        $coupon_product_ids = $vendorData->coupon->promo->details->pluck('refrence_id')->toArray();
                        $in_or_not = $vendorData->coupon->promo->restriction_type;

                    }
                    elseif($vendorData->coupon->promo->restriction_on == 1){
                        $coupon_vendor_ids = $vendorData->coupon->promo->details->pluck('refrence_id')->toArray();
                        $in_or_not = $vendorData->coupon->promo->restriction_type;
                    }
                }
                $cart_product_ids = [];

                $deliver_fee_charges = 0;
                $total_fixed_fee_tax = 0;
                // $total_service_fee = 0;
                $total_markup_fee_tax = 0;
                $bid_vendor_discount = 0;

                /* Getting in Vendor product loop and setting product values*/
                
                $previousdeliveryfee = 0;

                $security_amount = 0.00;
                $if_previousdeliveryfee_added = 0;
                $vendorTotalDeliveryFee = 0;

                $deliveryfeeOnCoupon = 0;
                foreach ($vendorData->vendorProducts as $ven_key => $prod) {
                    $prod->product->ServicePeriods = [];
                    $prod->service_start_time = '';
                    $prod->is_long_term_service = 0;

                    $prod->is_recurring_booking = 0;
                    $prod->schedule_slot_name = $prod->schedule_slot;
                    //if we required any additional price * multiply (Right now its for reccuring)
                    $prod->recurring_date_count = 1;

                    if($prod->product->is_recurring_booking ==1){
                        $prod->is_recurring_booking   = 1;
                        $prod->recurring_booking_time = convertDateTimeInTimeZone($prod->recurring_booking_time, $user_timezone, 'H:i');
                        $cnt = @count(explode(",",$prod->recurring_day_data));
                        $prod->recurring_date_count = $cnt != 0 ? $cnt : 1;
                        $is_recurring_booking         = 1;
                    }
                    if($prod->product->is_long_term_service ==1){

                        $vendorData->is_long_term_service = 1;
                        $is_long_term_service = 1;
                        $LongTermProducts = $prod->product->LongTermProducts;
                        if($prod->product->ServicePeriod){
                            $prod->product->ServicePeriods = $prod->product->ServicePeriod->pluck('service_period')->toArray();
                        }
                        if($prod->start_date_time !=''){
                            $prod->service_start_time = convertDateTimeInTimeZone($prod->start_date_time, $user_timezone, 'H:i');
                        }
                        $product_id = $LongTermProducts->product_id;
                        $url_slug   = $LongTermProducts->product->url_slug;
                        $vendor_slug=  $vendorData->vendor->slug;
                        unset($LongTermProducts->product);
                        $LongTermProducts->product   = $this->getProduct($product_id,$vendor_slug,$url_slug,$user,$langId);

                        $prod->long_term_products=$LongTermProducts;
                    }

                  $slotsDate = findSlot('',$vendorData->vendor->id,'','webFormet',$cart_id);

                  $vendorData->delaySlot = (($slotsDate)? ( $slotsDate['datetime']?  $slotsDate['datetime'] : '' ):'');
                  $vendorStartDate =  (($slotsDate)? ( $slotsDate['date'] ?  $slotsDate['date'] : '' ):'');
                  $vendorStartTime =  (($slotsDate)? ( $slotsDate['time'] ?  $slotsDate['time'] : '' ):'');
                if($prod->pvariant)   {

                    $cart_product_ids[] = $prod->product_id;
                    /* Setting Out of Stock if requied quanitity is not available */
                    if($prod->product->sell_when_out_of_stock == 0 && $prod->product->has_inventory == 1){
                        $quantity_check = productvariantQuantity($prod->variant_id);
                        if($quantity_check < $prod->quantity ){
                            $vendorData->product_quantity_less = 1;
                            $delivery_status = 0;
                            $product_out_of_stock = 1;
                        }
                    }
                        $cart_product_prescription = CartProductPrescription::where('cart_id', $cart->id)->where('product_id', $prod->product_id)->count();
                        $vendorData->vendorProducts[$ven_key]->cart_product_prescription = $cart_product_prescription;

                        if($cart_dinein_table_id > 0){
                            CartProduct::where('id', $prod->id)->update(['vendor_dinein_table_id' => $cart_dinein_table_id]);
                            //$prod->update(['vendor_dinein_table_id' => $cart_dinein_table_id]);
                        }

                        $prod->product_out_of_stock =  $product_out_of_stock;
                        $prod->faq_count = 0;
                        if( $preferences->product_order_form ==1 && $user ){
                            $prod->faq_count =  ProductFaq::where('product_id',$prod->product->id)->count();
                        }
                        $prod->category_id = $prod->product->category_id;
                        $prod->category_kyc_count = 0;
                        if( $preferences->category_kyc_documents ==1 ){
                            if(  !in_array( $prod->product->category_id, $category_array)){
                                $category_array[] = $prod->product->category_id;
                            }
                        }

                        $quantity_price = 0;
                        $divider = (empty($prod->doller_compare) || $prod->doller_compare < 0) ? 1 : $prod->doller_compare;
                        $price_in_currency = ($additionalPreference['is_price_by_role'] == 1 ) ? $prod->pvariant->new_price : $prod->pvariant->price??0;
                        if($cartData[0]->luxury_option_id == 4 ){ // for rental case
                            if(($prod->pvariant->incremental_price_per_min!='' && $prod->pvariant->incremental_price_per_min > 0)){
                                $prod->additional_price = ($prod->additional_increments_hrs_min / $prod->pvariant->incremental_price_per_min);
                            } else {
                                $prod->additional_price = 0.00;
                            }

                            if($prod->product->security_amount != '' && $prod->product->security_amount > 0){
                                $security_amount += $prod->product->security_amount;
                            }

                            //$payable_amount =  $price_in_currency + $prod->additional_price;
                            $sub_total += $prod->additional_price;
                        }
                        // dd($security_amount);
                   // } ///// Notable
                    //  GET PRICE from driver
                    $price_in_currency = isset($prod->dispatch_agent_price) ? $prod->dispatch_agent_price : 0 ;
                    
                    $totalMarkup += $prod->pvariant->markup_price * $prod->quantity??0;
                    $price_in_doller_compare = $prod->pvariant->price??0;
                    $container_charges_in_currency = $prod->pvariant->container_charges??0;

                    //Check product promo code is valid for this product
                    $checkProductPromoCodeController = new PromoCodeController();
                    $productPromoRequest = new Request();
                    $productPromoRequest->setMethod('POST');
                    $productPromoRequest->request->add(['cart_id' => $cart_id, 'product_id' => $prod->product_id]);
                    $productPromoCodeResponse = $checkProductPromoCodeController->postProductPromoCodeCheck($productPromoRequest)->getData();
                    if($productPromoCodeResponse->status == 'Success'){
                        $coupon_apply_price+=$price_in_currency * $prod->quantity;
                    }

                        //  $coupon_apply_price+=$price_in_currency;
                        $container_charges_in_doller_compare = $prod->pvariant->container_charges??0;
                        if($customerCurrency && $prod->pvariant){
                            // $price_in_currency = $prod->pvariant->price / $divider;
                            $price_in_doller_compare = $price_in_currency * $customerCurrency->doller_compare;

                        $container_charges_in_currency = $prod->pvariant->container_charges / $divider;
                        $container_charges_in_doller_compare = $container_charges_in_currency * $customerCurrency->doller_compare;
                    }
                    $quantity_price = $price_in_doller_compare * $prod->quantity;

                    

                    // if ((@auth()->user()->role_id == 3)) {
                        $quantity_role_price = $this->calculatePrice($prod->productVariantByRoles, $prod->quantity);
                    // }

                    if(@$quantity_role_price['quantity_price'] != 0 ) {
                            $quantity_price = $quantity_role_price['quantity_price'];
                    } else {
                        $quantity_price = $price_in_doller_compare * $prod->quantity;    
                    }
                    $quantity_price =  (($quantity_price)*($prod->recurring_date_count));
                   
                    $total_container_charges = $container_charges_in_currency * $prod->quantity;
                    $quantity_container_charges = $container_charges_in_doller_compare * $prod->quantity;

                    $sub_total+=$quantity_price+$quantity_container_charges;

                    $prod->pvariant->price_in_cart = $prod->pvariant->price??0;
                   
                    $total_quantity += $prod->quantity;
                    $prod->pvariant->price = decimal_format($price_in_currency);

                    $prod->pvariant->container_charges = decimal_format($container_charges_in_currency);
                    $prod->image_url = $this->loadDefaultImage();
                    // $prod->bid_discount = $pro;
                    $prod->pvariant->media_one = isset($prod->pvariant->media) ? $prod->pvariant->media->first() : [];
                    $prod->pvariant->media_second = isset($prod->product->media) ? $prod->product->media->first() : [];
                    $prod->pvariant->multiplier = ($customerCurrency) ? $customerCurrency->doller_compare : 1;
                    if(@$prod->bid_discount){
                        $bid_vendor_discount += (($quantity_price * $prod->bid_discount)/100);
                    }
                        $prod->quantity_price = decimal_format($quantity_price);


                    $prod->quantity_container_charges = decimal_format($quantity_container_charges);
                    //echo "index 1: quantity_price. ",$quantity_price." quantity_container_charges:".$quantity_container_charges;
                    $prod->quantity_role_price = $quantity_role_price;

                   $payable_amount = $payable_amount + $prod->additional_price + $quantity_price + $quantity_container_charges;


                    $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price;
                    $total_container_charges = $total_container_charges + $quantity_container_charges;
                    if(
                        ($in_or_not == 0 && in_array($prod->product_id,$coupon_product_ids))
                        || ($in_or_not == 1 && !in_array($prod->product_id,$coupon_product_ids))
                        || ($in_or_not == 0 && in_array($vendorData->vendor_id, $coupon_vendor_ids))
                        || ($in_or_not == 1 && !in_array($vendorData->vendor_id, $coupon_vendor_ids))
                    ){
                        $coupon_product_discount = $coupon_product_discount + $quantity_price + $quantity_container_charges;
                    }
                  /* Getting Add On info */
                    if($prod->addon->isNotEmpty()){
                        foreach ($prod->addon as $ck => $addons) {
                            if(isset($addons->option)){
                                $opt_price_in_currency = $addons->option->price;
                                $opt_price_in_doller_compare = $addons->option->price;
                                if($customerCurrency){
                                    $opt_price_in_currency = $addons->option->price / $divider;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $customerCurrency->doller_compare;
                                }

                                if($prod->recurring_day_data && !empty($prod->recurring_day_data)){
                                    

                                }else{
                                    $sub_total+=($opt_price_in_currency * $prod->quantity);
                                }
                                $coupon_apply_price+=$opt_price_in_currency;
                                $opt_quantity_price = decimal_format($opt_price_in_doller_compare * $prod->quantity);
                                $addons->option->price_in_cart = $addons->option->price;

                                

                                $addon_price=$addons->option->price;
                                $addons->option->price = decimal_format($opt_price_in_currency);

                                $addons->option->multiplier = ($customerCurrency) ? $customerCurrency->doller_compare : 1;


                                // Recurring Booking Enabled
                                if($prod->recurring_day_data && !empty($prod->recurring_day_data)){

                                    
                                }else{
                                    $addons->option->quantity_price = $opt_quantity_price;
                                }

                                $opt_quantity_price_new += $opt_quantity_price;


                                // Recurring Booking Enabled
                                if($prod->recurring_day_data && !empty($prod->recurring_day_data)){

                                    
                                }else{
                                    $payable_amount = $payable_amount + $opt_quantity_price;

                                }
                                $quantity_price = $quantity_price + $opt_quantity_price;
                                if(
                                    ($in_or_not == 0 && in_array($prod->product_id,$coupon_product_ids))
                                    || ($in_or_not == 1 && !in_array($prod->product_id,$coupon_product_ids))
                                    || ($in_or_not == 0 && in_array($vendorData->vendor_id, $coupon_vendor_ids))
                                    || ($in_or_not == 1 && !in_array($vendorData->vendor_id, $coupon_vendor_ids))
                                ){
                                    $coupon_product_discount = $coupon_product_discount + $opt_quantity_price;
                                }
                            }
                        }
                    }
                    //echo "index 1: quantity_price. ",$quantity_price." quantity_container_charges:".$quantity_container_charges;
                    /* Getting taxes info */
                    $select = '';
                    $taxData = array();
                    if (!empty($prod->product->taxCategory) && count($prod->product->taxCategory->taxRate) > 0) {
                        foreach ($prod->product->taxCategory->taxRate as $tckey => $tax_value) {
                            $rate = $tax_value->tax_rate;
                            $tax_amount = ($price_in_doller_compare * $rate) / 100;
                            if(!$this->additionalPreferences->is_tax_price_inclusive){
                                $product_tax = $quantity_price * $rate / 100;
                            }else{
                                $product_tax = ($quantity_price * $rate) / (100 + $rate);
                            }
                            // dd($product_tax);

                            $taxData[$tckey]['identifier'] = $tax_value->identifier;
                            $taxData[$tckey]['rate'] = $rate;
                            $taxData[$tckey]['tax_amount'] = decimal_format($tax_amount);
                            $taxData[$tckey]['product_tax'] = decimal_format($product_tax);
                            $taxable_amount = $taxable_amount + $product_tax;
                            $payable_amount = $payable_amount + $product_tax;
                        }
                    }
                        // dd($prod->product->taxCategory->toArray());
                        $prod->taxdata = $taxData;

                        if (isset($prod->pvariant->image->imagedata) && !empty($prod->pvariant->image->imagedata)) {
                            $prod->cartImg = $prod->pvariant->image->imagedata;
                        } else {
                            $prod->cartImg = (isset($prod->product->media[0]) && !empty($prod->product->media[0])) ? $prod->product->media[0]->image : '';
                        }

                        if($prod->product->delay_hrs_min != 0){
                            if($prod->product->delay_hrs_min > $delay_date)
                            $delay_date = $prod->product->delay_hrs_min;
                        }
                        if($prod->product->pickup_delay_hrs_min != 0){
                            if($prod->product->pickup_delay_hrs_min > $delay_date)
                            $pickup_delay_date = $prod->product->pickup_delay_hrs_min;
                        }

                        if($prod->product->dropoff_delay_hrs_min != 0){
                            if($prod->product->dropoff_delay_hrs_min > $delay_date)
                            $dropoff_delay_date = $prod->product->dropoff_delay_hrs_min;
                        }



                        $scheduled_date_time = $prod->scheduled_date_time !=''? $prod->scheduled_date_time : $slotsdate;

                        if(!empty($user)){
                            $scheduledDateTime = dateTimeInUserTimeZone($scheduled_date_time, $user->timezone);
                            //pr($scheduledDateTime);
                            $prod->scheduled_date_time = date('Y-m-d',strtotime($scheduledDateTime)) ;
                            $prod->manual_scheduled_date_time = convertDateTimeInTimeZone($prod->scheduled_date_time, $user->timezone, 'Y-m-d\TH:i');
                           // pr(  $prod->manual_scheduled_date_time );
                        }else{
                            $prod->scheduled_date_time = date('Y-m-d',strtotime($scheduled_date_time)) ;
                            $prod->manual_scheduled_date_time =  date('Y-m-d\TH:i',strtotime($scheduled_date_time)) ;
                        }

                        //if ($action == 'delivery' || $action == 'appointment') {
                        if ( (in_array($action,['delivery','appointment','on_demand']) ) && ( $is_service_product_price_from_dispatch !=1 ) ) {
                            $delivery_fee_charges = 0;
                            $deliver_charges_lalmove =0;
                            $deliveryCharges = 0;
                            $code = (($code)?$code:$cart->shipping_delivery_type.'_0');
                            $checkLastMile = 0;
                            $lastMileDate['tags'] = '';
                            $NumberOfroutes= 1;
                            //if recurring product
                            $NumberOfroutes = ($prod->recurring_date_count);
                            /** check product last mile  */
                            if (!empty($prod->product->Requires_last_mile) && ($prod->product->Requires_last_mile == 1) ) {
                                $checkLastMile = 1;
                                $lastMileDate['tags'] = $prod->product->tags;
                            } /** check lont term product product last mile  */
                            else if(($prod->product->is_long_term_service ==1) && !empty($prod->product->LongTermProduct) && $prod->product->LongTermProduct->first()->Requires_last_mile ==1){

                                $checkLastMile = 1;
                                $lastMileDate['tags'] = $prod->product->LongTermProduct->first()->tags;
                                $NumberOfroutes = $prod->LongTermProducts ? $prod->LongTermProducts->quantity : 1;
                            }
                            
                            //pr($NumberOfroutes);
                           // if ((!empty($prod->product->Requires_last_mile) && ($prod->product->Requires_last_mile == 1))  ) {
                            if($checkLastMile ==1){
                                $deliveriesNew = new CartController();
                                $deliveries = $deliveriesNew->getDeliveryOptions($vendorData, $preferences, $payable_amount, $address, $schedule_datetime_del, $lastMileDate['tags'],$NumberOfroutes);


                                if (isset($deliveries[0])) {
                                    $select .= '<select name="vendorDeliveryFee" class="form-control delivery-fee select">';
                                    if (count($deliveries)>1) {
                                        foreach ($deliveries as $k=> $opt) {
                                            if($prod->product->individual_delivery_fee == 1) {
                                                $select .= '<option value="'.$opt['code'].'" '.(($opt['code']==$code)?'selected':'').'  >'.__($opt['courier_name']).', '.__('Rate').' : '.($additionalPreference ['is_token_currency_enable'] ? getInToken(decimal_format($vendorTotalDeliveryFee + $opt['rate']*$prod->quantity)):decimal_format($vendorTotalDeliveryFee + $opt['rate']*$prod->quantity)).'</option>';
                                            }else{
                                                if($if_previousdeliveryfee_added == 0 && $opt['rate'] > 0){
                                                    $delivery_to_add = $opt['rate'];
                                                }else{$delivery_to_add = 0;}
                                                $select .= '<option value="'.$opt['code'].'" '.(($opt['code']==$code)?'selected':'').'  >'.__($opt['courier_name']).', '.__('Rate').' : '.($additionalPreference ['is_token_currency_enable'] ? getInToken(($vendorTotalDeliveryFee + $delivery_to_add)):decimal_format($vendorTotalDeliveryFee + $delivery_to_add)).'</option>';
                                            }
                                        }
                                    } else {
                                        foreach ($deliveries as $k=> $opt) {
                                            if($prod->product->individual_delivery_fee == 1) {
                                                $select .= '<option value="'.$opt['code'].'" '.(($opt['code']==$code)?'selected':'').'  >'.($additionalPreference ['is_token_currency_enable'] ? getInToken(($vendorTotalDeliveryFee + $opt['rate']*$prod->quantity)):($vendorTotalDeliveryFee + $opt['rate']*$prod->quantity)).'</option>';
                                            }else{
                                                if($if_previousdeliveryfee_added == 0 && $opt['rate'] > 0){
                                                    $delivery_to_add = $opt['rate'];
                                                }else{$delivery_to_add = 0;}
                                                $select .= '<option value="'.$opt['code'].'" '.(($opt['code']==$code)?'selected':'').'  >'.($additionalPreference ['is_token_currency_enable'] ? getInToken(decimal_format($vendorTotalDeliveryFee + $delivery_to_add)):decimal_format($vendorTotalDeliveryFee + $delivery_to_add)).'</option>';
                                            }
                                        }
                                    }
                                    $select .= '</select>';

                                    if($code) {
                                        $new = array_filter($deliveries, function ($var) use ($code) {
                                            return ($var['code'] == $code);
                                        });
                                        foreach ($new as $rate) {
                                            $deliveryCharges = $rate['rate'];
                                        }
                                        if ($deliveryCharges) {
                                            $deliveryCharges = $rate['rate'];
                                        } else {
                                            $deliveryCharges = $deliveries[0]['rate'];
                                            $code = $deliveries[0]['code'];
                                        }
                                    } else {
                                        $deliveryCharges = $deliveries[0]['rate'];
                                        $code = $deliveries[0]['code'];
                                    }
                                }

                            if($prod->product->individual_delivery_fee == 1) {
                                $quantity_deliveryCharges = $deliveryCharges*$prod->quantity;
                                $vendorTotalDeliveryFee = $vendorTotalDeliveryFee + $quantity_deliveryCharges;
                                CartProduct::where('id', $prod->id)->update(['product_delivery_fee'=>$quantity_deliveryCharges]);
                                $prod->product_delivery_fee = $quantity_deliveryCharges;
                            }else{
                                if($if_previousdeliveryfee_added == 0 && $deliveryCharges > 0){
                                    $vendorTotalDeliveryFee = $vendorTotalDeliveryFee + $deliveryCharges;
                                    $if_previousdeliveryfee_added = 1;
                                }
                            }
                           
                            $deliveryCharges_real = $deliveryCharges_real + $vendorTotalDeliveryFee;

                            if (isset($vendorTotalDeliveryFee) && !empty($vendorTotalDeliveryFee)) {
                                $dtype = explode('_', $code);
                                CartDeliveryFee::updateOrCreate(['cart_id' => $cart->id, 'vendor_id' => $vendorData->vendor->id], ['delivery_fee' => $vendorTotalDeliveryFee,'shipping_delivery_type' => $dtype[0]??'D','courier_id'=>$dtype[1]??'0']);
                            }
                        }//End Check last time stone
                    }
                }

                $is_slot_from_dispatch =  $prod->product->is_slot_from_dispatch;
                $show_dispatcher_agent =  $prod->product->is_show_dispatcher_agent;
                $last_mile_check       = $prod->product->Requires_last_mile  ;
                $cateTypeId = $prod->product->productcategory ? $prod->product->productcategory->type_id : '';

                $getSlotingDate = $prod->scheduled_date_time ;
                if( ($prod->scheduled_date_time =='') || ( strtotime($prod->scheduled_date_time) < strtotime($vendorStartDate) ) ){
                    $prod->scheduled_date_time = $getSlotingDate = $vendorStartDate ;
                }
               
                if(  $is_service_product_price_from_dispatch == 1){
                 
                    $selected_dispatcher_time = Carbon::parse($prod->scheduled_date_time, 'UTC')->setTimezone( $user_timezone)->format('Y-m-d');
                    $scheduled_date_time = Carbon::parse($prod->scheduled_date_time)->format('Y-m-d');
                    $nowDate             = Carbon::now()->format('Y-m-d');
                    if(  $scheduled_date_time <  $nowDate   ){
                        $delivery_status_message  = __('Scheduled Date Time Invalid!');
                        $delivery_status = 0;
                    }
                    
                    if($prod->schedule_slot!=''){
                        $D_slot =    explode('-', $prod->schedule_slot);
                        $start_time =  $nowDate.' ' .( @$D_slot[0] ?? '00:00');
                        $end_time   =   $nowDate.' ' .(@$D_slot[1] ?? '01:00');
                       
                        $prod->schedule_slot_name =  date('h:i A',strtotime($start_time)).' - '.date('h:i A', strtotime($end_time));
                    }
                  
                   
                    $prod->selected_dispatcher_time =$selected_dispatcher_time;
                }
                $vendorData->delivery_status_message = $delivery_status_message;
                $prod->dispatchAgents = [];
                if(($cateTypeId ==  12) && ($is_slot_from_dispatch == 1) && ( $last_mile_check ==1) ){
                    $Dispatch =  $this->getDispatchAppointmentDomain();
                    $dispatchAgents = [];

                    if($Dispatch){
                        $location[] = array(
                            'latitude' =>   $vendor_longitude,
                            'longitude' =>  $vendor_longitude
                        );
                        $dispatchData=[
                            'service_key'      => $Dispatch->appointment_service_key,
                            'service_key_code' => $Dispatch->appointment_service_key_code,
                            'service_key_url'  => $Dispatch->appointment_service_key_url,
                            'service_type'     => 'appointment',
                            'tags'             => $prod->product->tags,
                            'latitude'         => $vendor_latitude,
                            'longitude'        => $vendor_longitude,
                            'service_time'     => $prod->product->minimum_duration_min,
                            'schedule_date'    => $getSlotingDate,
                            'slot_start_time'  => $vendorStartTime
                        ];
                        //pr($dispatchData);
                        $dispatchAgents = $this->getSlotFeeDispatcher($dispatchData);
                    }
                    $prod->dispatchAgents =  $dispatchAgents;
                    $prod->vendorStartDate = $vendorStartDate;
                }
                    $product = Product::with([
                        'variant' => function ($sel) {
                            $sel->groupBy('product_id');
                        },
                        'variant.media.pimage.image', 'upSell', 'crossSell', 'vendor', 'media.image', 'translation' => function ($q) use ($langId) {
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                            $q->where('language_id', $langId);
                        }])->select('id', 'sku', 'inquiry_only', 'url_slug', 'weight', 'weight_unit', 'vendor_id', 'has_variant', 'has_inventory', 'averageRating','minimum_order_count','batch_count','service_charges_tax','delivery_charges_tax','container_charges_tax','fixed_fee_tax','service_charges_tax_id','delivery_charges_tax_id','container_charges_tax_id','fixed_fee_tax_id')
                        ->where('url_slug', $prod->product->url_slug)
                        ->where('is_live', 1)
                        ->first();

                    $doller_compare = ($customerCurrency) ? $customerCurrency->doller_compare : 1;
                    $upsell = new FrontController();
                    $up_prods = $upsell->metaProduct($langId, $doller_compare, 'upSell', ($product->upSell ?? ''));
                    if($up_prods){
                        $upSell_products->push($up_prods);
                    }
                    $cross_prods = $upsell->metaProduct($langId, $doller_compare, 'crossSell', ($product->crossSell ?? ''));
                    if($cross_prods){
                        $crossSell_products->push($cross_prods);
                    }

                    // Add Delivery Slot Price In total amount
                    if($prod->delivery_date != '' && $prod->slot_price != '' && $prod->slot_id != ''){
                        $payable_amount = $payable_amount + decimal_format($prod->slot_price);
                    }
                    
                    // Add Delivery Slot Price In total amount

                    if($prod->delivery_date != '' && $prod->slot_price != '' && $prod->slot_id != ''){
                        $delivery_slot_amount += decimal_format($prod->slot_price);                        
                    }
                }
                // dd($security_amount);
                // $couponGetAmount = $payable_amount ;

                if (isset($vendorData->coupon) && !empty($vendorData->coupon) ) {
                    if (isset($vendorData->coupon->promo) && !empty($vendorData->coupon->promo)) {
                        if($vendorData->coupon->promo->restriction_on == 0 || $vendorData->coupon->promo->restriction_on == 1)
                        {
                            $couponGetAmount = $coupon_product_discount;
                        }
                        if($vendorData->coupon->promo->first_order_only==1){
                            if(auth()->user()){
                                $userOrder = auth()->user()->orders->first();
                                if($userOrder){
                                    $cart->coupon()->delete();
                                    $vendorData->coupon()->delete();
                                    unset($vendorData->coupon);
                                    $PromoDelete =1;
                                }
                            }
                        }
                        if ($PromoDelete !=1) {
                            if(!($vendorData->coupon->promo->expiry_date >= $nowdate) ){
                                $cart->coupon()->delete();
                                $vendorData->coupon()->delete();
                                unset($vendorData->coupon);
                                $PromoDelete =1;
                            }
                        }
                        if ( $PromoDelete !=1) {

                            $minimum_spend = 0;
                            if (isset($vendorData->coupon->promo->minimum_spend)) {
                                $minimum_spend = $vendorData->coupon->promo->minimum_spend * $doller_compare;
                            }

                            $maximum_spend = 0;
                            if (isset($vendorData->coupon->promo->maximum_spend)) {
                                $maximum_spend = $vendorData->coupon->promo->maximum_spend * $doller_compare;
                            }

                            if( ($minimum_spend <= $couponGetAmount ) && ($maximum_spend >= $couponGetAmount))
                            {
                                if ($vendorData->coupon->promo->promo_type_id == 2) {
                                    $total_discount_percent = $vendorData->coupon->promo->amount;

                                    $payable_amount -= $total_discount_percent;
                                    $coupon_amount_used = $total_discount_percent;
                                } else {
                                    $gross_amount = decimal_format($payable_amount - $taxable_amount-$total_container_charges);
                                    $percentage_amount = ($gross_amount * $vendorData->coupon->promo->amount / 100);
                                    $payable_amount -= $percentage_amount;
                                    $coupon_amount_used = $percentage_amount;
                                }
                            }
                            else{

                                $cart->coupon()->delete();
                                $vendorData->coupon()->delete();
                                unset($vendorData->coupon);
                                $PromoDelete =1;
                            }
                        }
                        if ( $PromoDelete !=1) {
                            if($vendorData->coupon->promo->allow_free_delivery ==1   ){
                                $PromoFreeDeliver = 1;
                              // $coupon_amount_used = $coupon_amount_used ;
                                $coupon_amount_used = $coupon_amount_used +  $vendorTotalDeliveryFee;
                                $payable_amount = $payable_amount;
                                $deliveryfeeOnCoupon = 1;
                            }
                        }
                    }
                }

                $promoCodeController = new PromoCodeController();
                $promoCodeRequest = new Request();
                $promoCodeRequest->setMethod('POST');
                $promoCodeRequest->request->add(['vendor_id' => $vendorData->vendor_id, 'cart_id' => $cart_id, 'amount' => $couponGetAmount,'is_cart' => 1, 'cart_product_ids' => $cart_product_ids]);
                $promoCodeResponse = $promoCodeController->postPromoCodeList($promoCodeRequest)->getData();
                if($promoCodeResponse->status == 'Success'){
                    if(!empty($promoCodeResponse->data)){
                        $is_promo_code_available = 1;
                    }
                }

                $deliveryfee_ifnot_discounted = ($deliveryfeeOnCoupon == 0) ? $vendorTotalDeliveryFee : 0;
                if($user){
                    // calculate subscription discount On admin and vendor
                    $vendor_subs_disc_percent       = isset($vendorData->vendor->subscription_discount_percent) ? $vendorData->vendor->subscription_discount_percent : 0;
                    $subscription_discount_arr      = $this->calCulateSubscriptionDiscount($user->id, $deliveryfee_ifnot_discounted, $payable_amount  +  $deliveryfee_ifnot_discounted, $vendor_subs_disc_percent);
                    $subscription_discount_admin    = $subscription_discount_arr['admin'];
                    $subscription_discount_vendor   = $subscription_discount_arr['vendor'];
                    $subscription_discount_delivery = $subscription_discount_arr['delivery_discount'];
                }
                // add total delivery fee
                if($vendorData->vendor->delivery_charges_tax_id)
                $total_deliver_charges +=  $deliveryfee_ifnot_discounted;

                $delivery_fees = $delivery_fees + $deliveryfee_ifnot_discounted;

                if($vendorData->vendor->add_markup_price)
                $total_markup_charges +=  $totalMarkup;



                $subtotal_amount = $payable_amount;

                // if($PromoFreeDeliver != 1){
                $payable_amount = $payable_amount + $deliveryfee_ifnot_discounted + $security_amount;
                //}
                //$payable_amount = $payable_amount + $deliver_charge;
                //Start applying service fee on vendor products total



                $vendor_service_fee_percentage_amount = 0;
                if($vendorData->vendor->service_fee_percent > 0){
                    $amount_for_service = $opt_quantity_price_new + $vendor_products_total_amount;
                    $vendor_service_fee_percentage_amount = (($amount_for_service) * $vendorData->vendor->service_fee_percent) / 100 ;
                    $payable_amount = $payable_amount + $vendor_service_fee_percentage_amount;
                }
                
                if($vendorData->vendor->service_charge_amount > 0){
                    $vendor_service_fee_percentage_amount =  $vendorData->vendor->service_charge_amount ;
                    $payable_amount = $payable_amount + $vendor_service_fee_percentage_amount;
                }

                //end applying service fee on vendor products total
                $total_service_fee = $total_service_fee + $vendor_service_fee_percentage_amount;

                $vendorData->coupon_amount_used = decimal_format($coupon_amount_used);
                $vendorData->service_fee_percentage_amount = decimal_format($vendor_service_fee_percentage_amount);
                $vendorData->delivery_fee_charges = decimal_format($delivery_fee_charges);
                //$vendorData->delivery_fee_charges_static = decimal_format($delivery_fee_charges_static);;

                $vendorData->payable_amount = decimal_format($payable_amount);
                $vendorData->discount_amount = decimal_format($discount_amount);
                $vendorData->discount_percent = decimal_format($discount_percent);
                $vendorData->taxable_amount = decimal_format($taxable_amount);

                $vendorData->product_total_amount = decimal_format($payable_amount - $taxable_amount);



                $vendorData->bid_vendor_discount = decimal_format($bid_vendor_discount);

                $bid_total_discount += $vendorData->bid_vendor_discount;


                $vendorData->product_sub_total_amount = decimal_format($subtotal_amount);
                $vendorData->isDeliverable = 1;
                $vendorData->promo_free_deliver = $PromoFreeDeliver;
                $vendorData->is_vendor_closed = $is_vendor_closed;
                // $slotsDate = findSlot('',$vendorData->vendor->id,'');
                // $vendorData->delaySlot = (($slotsDate)?$slotsDate:'');
                $vendorData->closed_store_order_scheduled = (($slotsDate)?$product->vendor->closed_store_order_scheduled:0);

                $vendorData->delOptions = $select;

                //mohit sir branch code added by sohail
                $processorProduct = [];
                if(checkTableExists('processor_products')){
                    $processorProduct = ProcessorProduct::where(['product_id' => $prod->product_id])->first();
                }
                if(!empty($processorProduct) && $processorProduct->is_processor_enable == 1){
                    $vendorData->processor_product = $processorProduct;
                }else{

                    $vendorData->processor_product = '';
                }
                //till here

                if(isset($serviceArea)){
                    if($serviceArea->isEmpty()){
                        $vendorData->service_area_empty = 1;
                        $vendorData->isDeliverable = 0;
                        $delivery_status = 0;
                    }
                }
                if($is_service_product_price_from_dispatch !=1){ // no need to check slot and web styling 

                    if(($vendorData->vendor->show_slot == 0) ){
                        if( ($vendorData->vendor->slotDate->isEmpty()) && ($vendorData->vendor->slot->isEmpty()) ){
                            $vendorData->is_vendor_closed = 1;
                            if($delivery_status != 0){
                                $delivery_status = 0;
                            }
                        }else{
                            $vendorData->is_vendor_closed = 0;
                        }
                    }
                 
                    $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();
                    if((isset($set_template)  && $set_template->template_id != 9) ){
                        if($vendorData->vendor->$action == 0){
                            $vendorData->vendot_type_not_active = 1;
                            $vendorData->is_vendor_closed = 1;
                            $delivery_status = 0;
                        }
                    }
                }
                $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);

                if($getAdditionalPreference['is_price_by_role'] == 1){
                    $role_id = (Auth::user() != null) ? Auth::user()->role_id : 1;
                    $vendor_min_amount_data = VendorMinAmount::where('vendor_id',$vendorData->vendor->id)
                    ->where('role_id', $role_id)->first();
                    $vendorData->vendor->order_min_amount = empty($vendor_min_amount_data)?0:$vendor_min_amount_data->order_min_amount;
                }

                if((float)($vendorData->vendor->order_min_amount) > $payable_amount+(float)($vendorData->vendor->fixed_fee_amount)-(float)($loyalty_amount_saved)){  # if any vendor total amount of order is less then minimum order amount
                    $vendorData->les_order_min_amount = 1;
                    $delivery_status = 0;
                }

                $total_payable_amount = $total_payable_amount + $payable_amount + $vendorData->vendor->fixed_fee_amount;
                $total_taxable_amount = $total_taxable_amount + $taxable_amount;
                $total_discount_amount = $total_discount_amount + $discount_amount;
                $total_discount_percent = $total_discount_percent + $discount_percent;
                $total_subscription_discount_admin     = $total_subscription_discount_admin + $subscription_discount_admin;
                $total_subscription_discount_vendor    = $total_subscription_discount_vendor + $subscription_discount_vendor;
                $total_subscription_discount_delivery  = $total_subscription_discount_delivery + $subscription_discount_delivery;
                $vendorData->is_promo_code_available = $is_promo_code_available;


                $taxChargeable['deliveryCharges'] = $total_deliver_charges;
                $taxChargeable['vendor_service_fee_percentage_amount'] = $total_service_fee;
                $taxChargeable['total_fixed_fee_amount'] = $total_fixed_fee_amount;
                $taxChargeable['total_markup_charges'] = $total_markup_charges;
                $taxChargeable['total_container_charges'] = $total_container_charges;

                $getalltaxes = $this->getAllOthertaxes($vendorData,$taxChargeable,$taxCharges);

                $taxCharges['deliver_fee_charges'] = $getalltaxes->deliver_fee_charges??0;
                $taxCharges['total_service_fee'] = $getalltaxes->total_service_fee??0;
                $taxCharges['total_fixed_fee_tax'] = $getalltaxes->total_fixed_fee_tax??0;
                $taxCharges['total_markup_fee_tax'] = $getalltaxes->total_markup_fee_tax??0;
                $container_charges_tax = $getalltaxes->container_charges_tax??0;

            }//End vendor loop
            //pr(  $cartData->toArray());
            $is_percent = 0;
            $amount_value = 0;
            if ($cart->coupon) {
                foreach ($cart->coupon as $ck => $coupon) {
                    if (isset($coupon->promo)) {
                        if ($coupon->promo->promo_type_id == 1) {
                            $is_percent = 1;
                            $total_discount_percent = $total_discount_percent + round($coupon->promo->amount);
                        }
                    }
                }
            }
            if ($is_percent == 1) {
                $total_discount_percent = ($total_discount_percent > 100) ? 100 : $total_discount_percent;
                $total_discount_amount = $total_discount_amount + ($total_payable_amount * $total_discount_percent) / 100;
            }
            if ($amount_value > 0) {
                if($customerCurrency){
                    $amount_value = $amount_value * $customerCurrency->doller_compare;
                }
                $total_discount_amount = $total_discount_amount + $amount_value;
            }

            $total_discount_amount = $total_discount_amount + $total_subscription_discount_admin + $total_subscription_discount_vendor + $total_subscription_discount_delivery;

            $cart->total_subscription_discount = decimal_format(($total_subscription_discount_admin + $total_subscription_discount_vendor + $total_subscription_discount_delivery)??0);

            $total_payable_amount = $total_payable_amount - $total_discount_amount;
            if ($loyalty_amount_saved > 0) {
                if ($loyalty_amount_saved > $total_payable_amount) {
                    $loyalty_amount_saved =  $total_payable_amount;
                }
                $total_payable_amount = $total_payable_amount - $loyalty_amount_saved;
            }
            $wallet_amount_available = 0;
            $wallet_amount_used = 0;
            //pr($cart->toArray());
            if($user){

                if($user->balanceFloat > 0){
                    $wallet_amount_available=$user->balanceFloat;
                    if($customerCurrency){
                        $wallet_amount_used = $user->balanceFloat * $customerCurrency->doller_compare;
                    }
                    if($wallet_amount_used > $total_payable_amount){
                        $wallet_amount_used = $total_payable_amount;
                    }
                    $total_payable_amount = $total_payable_amount - $wallet_amount_used;
                }
                if(isset($cart->giftCard) && !empty($cart->giftCard)){
                    $giftCardDelete =0;
                    $giftCardCode = $cart->giftCard->name;
                    $giftcard = UserGiftCard::with('giftCard')->whereHas('giftCard',function ($query) use ($nowdate,$giftCardCode){
                        return  $query->whereDate('expiry_date', '>=', $nowdate)->where('name',$giftCardCode);
                    })->where(['is_used'=>'0','user_id'=>$user->id])->first();
                    if($giftcard){
                        $giftCardAmount = $cart->giftCard->amount;
                    }else{
                        Cart::where('id', $cart->id)->update(['gift_card_id'=> null]);
                        unset($cart->giftCard);
                    }
                }
            }
            $cart->wallet_amount_used = decimal_format($wallet_amount_used);



            $scheduled = (object)array(
                'scheduled_date_time'=>(($cart->scheduled_slot)?date('Y-m-d',strtotime($cart->scheduled_date_time)):$cart->scheduled_date_time),'slot'=>$cart->scheduled_slot,
            );
            $cart->deliver_status = $delivery_status;
            $cart->vendorCnt = $cartData->count();
            $cart->scheduled = $scheduled;
            $cart->is_recurring_booking = $is_recurring_booking;
            $cart->schedule_type =  $cart->schedule_type;
            $cart->closed_store_order_scheduled =  0;
            $myDate = date('Y-m-d');
            if($cart->vendorCnt==1){
                $vendorId = $cartData[0]->vendor_id;
                $cart->scheduled->scheduled_date_time = $cartData[0]->scheduled_date_time;
                $cart->scheduled->slot = $cartData[0]->schedule_slot;

                //type must be a : delivery , takeaway,dine_in
                $duration = Vendor::where('id',$vendorId)->select('slot_minutes','closed_store_order_scheduled')->first();
                $closed_store_order_scheduled = (($slotsDate)?$duration->closed_store_order_scheduled:0);
                if($cart->deliver_status == 0 && $closed_store_order_scheduled == 1)
                {
                    $cart->deliver_status = $duration->closed_store_order_scheduled;
                    $cart->closed_store_order_scheduled = $duration->closed_store_order_scheduled;
                    $myDate = date('Y-m-d',strtotime($cart->scheduled_date_time));
                    $sttime =  strtotime($myDate);
                    $todaytime =  strtotime(date('Y-m-d'));
                    if($todaytime == $sttime){$sttime =  strtotime('+1 day',$sttime);}
                    $myDate = (($myDate)?date('Y-m-d',$sttime):date('Y-m-d',strtotime('+1 day')));
                    $cart->schedule_type =  'schedule';
                    //$cart->closed_store_order_scheduled =  1;
                }else{
                    $cart->closed_store_order_scheduled = $duration->closed_store_order_scheduled;
                }
                if($preferences->scheduling_with_slots != 1 && $preferences->business_type != 'laundry'){
                    $myDate = $cartData[0]->scheduled_date_time;
                    $slotsRes = getShowSlot($myDate,$vendorId,'delivery',$duration->slot_minutes, 0,$cart_id);
                    $slots = (object)$slotsRes['slots'];
                    $slotsdate = $slotsRes['date'];
                    $cart->slotsdate = $slotsdate;
                    $cart->slots = $slots;
                    $cart->vendor_id =  $vendorId;
                }else{
                    $cart->slots = [];
                    $cart->vendor_id =  $vendorId;
                    $slots = [];
                }

                $pickupSlots = [];
                $dropoffSlots = [];
                // get slots for laundry category
                if($preferences->scheduling_with_slots == 1 && $preferences->business_type == 'laundry'){
                    // For Pickup
                    //$pickupSlots = (object)getShowSlot($myDate,$vendorId,'delivery',$duration->slot_minutes, 1);
                    $slotsRes = getShowSlot($myDate,$vendorId,'delivery',$duration->slot_minutes, 1,'',$cart_id);
                    $pickupSlots = (object)$slotsRes['slots'];
                    $pickupslotsdate = $slotsRes['date'];
                    $cart->slotsForPickupdate= $pickupslotsdate;


                    // For Dropoff
                    $myDropoffDate = date('Y-m-d');
                    $slotsRes = getShowSlot($myDropoffDate,$vendorId,'delivery',$duration->slot_minutes, 2,'',$cart_id);
                    $dropoffSlots = (object)$slotsRes['slots'];
                    $dropoffSlotsdate = $slotsRes['date'];
                    $cart->slotsForDropoffDate = $dropoffSlotsdate;

                    $cart->slotsForPickup = $pickupSlots;

                    $cart->slotsForDropoff  = $dropoffSlots;
                    $cart->vendor_id = $vendorId;
                }

            }else{
                $slots = [];
                $cart->slots = [];
                $cart->slotsForPickup = [];
                $cart->slotsForDropoff = [];
                $cart->vendor_id =  0;
                $pickupSlots = [];
                $dropoffSlots = [];
            }
            $cart->without_category_kyc = 0;

            if( $preferences->category_kyc_documents ==1 && $user ){

                $category_query =  CategoryKycDocuments::whereHas('categoryMapping',function($q) use($category_array){
                    $q->whereIn('category_id',$category_array);
                });

                $category_kyc_document_ids =  $category_query->pluck('id');
                //  $category_kyc_category_ids =  $category_query->categoryMapping->pluck('category_id');
                 // pr( $category_query->first()->toArray());
                $category_kyc_document_ids = $category_kyc_document_ids->isNotEmpty() ? $category_kyc_document_ids->toArray() : [];


                $category_kyc_count =  $category_query->count();

                $is_alrady_submit = CaregoryKycDoc::whereIn('category_kyc_document_id', $category_kyc_document_ids)->where('cart_id',$cart_id)->count();
                // echo  $is_alrady_submit." <br>";
                // pr($category_kyc_document_ids);
                if( $category_kyc_count  > 0 && ($is_alrady_submit  !=  $category_kyc_count )){
                    $cart->category_kyc_count = $category_kyc_count;
                    $cart->category_rendem_id = rand(9,10);
                    $cart->category_ids = implode( ',',$category_array);
                }

                $ALLcategory_kyc_documents =CategoryKycDocuments::whereHas('categoryMapping',function($q) use($category_array){
                    $q->whereIn('category_id',$category_array);
                })->with('primary')->get();
                foreach ($ALLcategory_kyc_documents as $vendor_registration_document) {
                    if($vendor_registration_document->is_required == 1){

                        $check = CaregoryKycDoc::where(['cart_id'=>$cart_id,'category_kyc_document_id'=>$vendor_registration_document->id])->first();
                        if($check)
                        {
                            $cart->without_category_kyc = 1;
                        }
                    }
                }
            }else{
                $cart->without_category_kyc = 1;
            }

            $other_taxes=array_sum($taxCharges);
            $other_taxes_string='tax_fixed_fee:'.$taxCharges['total_fixed_fee_tax'].',tax_service_charges:'.$taxCharges['total_service_fee'].',tax_delivery_charges:'.$taxCharges['deliver_fee_charges'].',tax_markup_fee:'.$taxCharges['total_markup_fee_tax'].',product_tax_fee:'.$total_taxable_amount;



            $cart->bid_total_discount = $bid_total_discount??0;
            $gross_amount = decimal_format($total_payable_amount + $total_discount_amount + $loyalty_amount_saved + $wallet_amount_used - $total_taxable_amount);
            $cart->other_taxes = $other_taxes;
            $cart->other_taxes_string = $other_taxes_string;
            $cart->container_charges_tax =  decimal_format($container_charges_tax);
            $cart->slotsCnt = count((array)$slots);
            $cart->pickupSlotsCnt = count((array)$pickupSlots);
            $cart->dropoffSlotsCnt = count((array)$dropoffSlots);
            $cart->total_service_fee = decimal_format($total_service_fee);
            $cart->loyalty_amount = decimal_format($loyalty_amount_saved);
            $cart->gross_amount = ($gross_amount < 0) ? decimal_format(0) : decimal_format($gross_amount);
            $cart->new_gross_amount = decimal_format($total_payable_amount + $total_discount_amount);
            $cart->is_long_term_service = $is_long_term_service ;
            if(!$this->additionalPreferences->is_tax_price_inclusive){
                $cartTotalPay = decimal_format($total_payable_amount);
               // pr( $cartTotalPay);
                // gift card calculation
                if($giftCardAmount >0 && $cartTotalPay >0){
                    $calCulateGiftCard = $this->calCulateGiftCard($cartTotalPay,$giftCardAmount);
                    $cartTotalPay  = @$calCulateGiftCard['totalPaybel'];
                    $giftCardUsed  = @$calCulateGiftCard['used_GiftCardAmount'];
                }
                //end  gift card calculation

                $cart->total_payable_amount =  $cartTotalPay ;
            }else{
                $cartTotalPay = decimal_format($total_payable_amount - $total_taxable_amount - $other_taxes);
                // gift card calculation
                if($giftCardAmount >0){
                    $calCulateGiftCard = $this->calCulateGiftCard($cartTotalPay,$giftCardAmount);
                    $cartTotalPay  = @$calCulateGiftCard['totalPaybel'];
                    $giftCardUsed  = @$calCulateGiftCard['used_GiftCardAmount'];
                }
                //end  gift card calculation
                $cart->total_payable_amount =  $cartTotalPay;
                $cart->payy = decimal_format(($total_payable_amount - $total_taxable_amount - $other_taxes) + $cart->other_taxes);
            }

            $cart->delivery_slot_amount = $delivery_slot_amount;

            // $cart->total_payable_amount = decimal_format($total_payable_amount);
            //$cart->delivery_charges = decimal_format($deliveryCharges);
            //$cart->total_payable_amount = decimal_format($total_payable_amount);
            $cart->delivery_charges = decimal_format($delivery_fees);
            $cart->total_deliver_charges = decimal_format($total_deliver_charges);
            $cart->total_markup_charges = decimal_format($total_markup_charges);
            $cart->total_discount_amount = decimal_format($total_discount_amount);
            $cart->total_taxable_amount = decimal_format($total_taxable_amount);
            $total_payable_amount_calc_tip = $total_payable_amount - $total_taxable_amount;
            $cart->tip_5_percent = decimal_format(0.05 * $total_payable_amount_calc_tip);
            $cart->tip_10_percent = decimal_format(0.10 * $total_payable_amount_calc_tip);
            $cart->tip_15_percent = decimal_format(0.15 * $total_payable_amount_calc_tip);
            $cart->total_container_charges = decimal_format($total_container_charges);
            $cart->wallet_amount_available = decimal_format($wallet_amount_available);
            $cart->taxRates=$taxRates;
            $cart->action = $action;
            $cart->totalQuantity = $total_quantity;
            $cart->user_allAddresses = $user_allAddresses??[];
            $cart->guest_user = $guest_user??0;
            $cart->left_section = view('frontend.cartnew-left')->with(['action' => $action,  'vendor_details' => $vendor_details, 'addresses'=> $this->user_allAddresses??[], 'countries'=> $countries, 'cart_dinein_table_id'=> $cart_dinein_table_id, 'processorProduct' => $processorProduct, 'preferences' => $preferences])->render();
            $cart->upSell_products = ($upSell_products) ? $upSell_products->first() : collect();
            $cart->crossSell_products = ($crossSell_products) ? $crossSell_products->first() : collect();
            $cart->scheduled_date_time = $myDate;
            $cart->giftCardUsedAmount = $giftCardUsed;
            $cart->security_amount = $security_amount;

            if($preferences->scheduling_with_slots == 1 && $preferences->business_type == 'laundry'){
                if($cart->pickupSlotsCnt==0){
                    $mdate = (object)findSlotNew('',$cart->vendor_id,1);
                    $cart->delay_date =  $mdate->mydate;
                }else{
                    $cart->delay_date =  $myDate??$delay_date;
                }

                if($cart->dropoffSlotsCnt==0){
                    $mdate = (object)findSlotNew('',$cart->vendor_id,2);
                    $cart->my_dropoff_delay_date =  $mdate->mydate;
                }else{
                    $cart->my_dropoff_delay_date =  $myDropoffDate??$delay_date;
                }

            }else{
                if($cart->slotsCnt==0){
                    $mdate = (object)findSlotNew('',$cart->vendor_id,0);
                    $cart->delay_date =  $mdate->mydate;
                }else{
                    $cart->delay_date =  $myDate??$delay_date;
                }
            }

            if($preferences->same_day_delivery_for_schedule == 0){
                if($cart->my_dropoff_delay_date == date('Y-m-d') ){
                    $cart->my_dropoff_delay_date = date('Y-m-d',strtotime('+1 day'));
                }

                if($cart->delay_date == date('Y-m-d') ){
                    $cart->delay_date = date('Y-m-d',strtotime('+1 day'));
                }

            }


            $cart->pickup_delay_date =  $pickup_delay_date??0;
            $cart->dropoff_delay_date =  $dropoff_delay_date??0;
            $cart->delivery_type =  $code??'D';
            $cart->sub_total =  $sub_total??0;
            $cart->sub_total_inc_tax =  decimal_format($cart->sub_total + $total_taxable_amount);
            $cart->is_token =  $additionalPreference['is_token_currency_enable'] ? 1 : 0;
            $cart->token_value = $additionalPreference['token_currency'] ?? 0;
            // pr($cart->toArray());
            $cart->products = $cartData->toArray();
        }
        return $cart;
      }

    public function hideSecretKeys($res){
        return $res->makeHidden(['map_key','map_secret', 'mail_password', 'mail_host', 'mail_username', 'sms_secret',
        'sms_key', 'sms_from', 'sms_credentials' ]);
    }

    /**
     * calCulateGiftCard
     *
     * @param  mixed $TotalPaybel
     * @param  mixed $GiftCardAmount
     * @return void
     */
    public function calCulateGiftCard($TotalPaybel,$GiftCardAmount)
    {
        $balance_coupon   =0 ;
        $used_GiftCardAmount = $GiftCardAmount;
        $cartTotalAfterGiftCardPay = ($TotalPaybel) - ($GiftCardAmount);
        if($cartTotalAfterGiftCardPay <=0){
            $balance_coupon   = abs($cartTotalAfterGiftCardPay);
            $used_GiftCardAmount = $GiftCardAmount - $balance_coupon;
            $cartTotalAfterGiftCardPay = decimal_format(0);
        }
        $TotalPaybel =$cartTotalAfterGiftCardPay;
        return ['totalPaybel'=>$TotalPaybel,'used_GiftCardAmount'=>$used_GiftCardAmount];

    }

    function calculatePrice($productVariantByRoles, $prodQuantity) {
        $quantity_price = 0;
        $current_price = 0;
        
        if(getAdditionalPreference(['is_corporate_user'])['is_corporate_user'] == 1 && !empty($productVariantByRoles))  {
            $amount = 0;
            $quantity = 0;
            foreach($productVariantByRoles->reverse() as $inn_key => $inn_val) {
                // if($inn_val->role_id == Auth::user()->role_id ) {
                    if($quantity < $inn_val->quantity && $inn_val->quantity <= $prodQuantity) {
                        $quantity = $inn_val->quantity;
                        $amount = $inn_val->amount;
                    }
                // }
                // break;
            }
            $quantity_price = $amount * $prodQuantity;
        }
        return [
            'quantity_price' => $quantity_price,
            'amount' => $amount
        ]; 
    }

    public function fixedFee($lang_id){
        if(Nomenclature::where('label','Fixed Fee')->exists()){
            $nomenclatures_translation_id=Nomenclature::where('label','Fixed Fee')->first()->id;
            return NomenclatureTranslation::where(['nomenclature_id'=>$nomenclatures_translation_id,'language_id'=>$lang_id])->exists() ? NomenclatureTranslation::where(['nomenclature_id'=>$nomenclatures_translation_id,'language_id'=>$lang_id])->first()->name : "Fixed Fee";
        }else{
            return "Fixed Fee";
        }
    }
}
