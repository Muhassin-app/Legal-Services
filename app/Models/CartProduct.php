<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;
class CartProduct extends Model{

    use HasFactory;

    protected $fillable = ['cart_id','product_id', 'vendor_id', 'vendor_dinein_table_id', 'quantity', 'status', 'variant_id', 'is_tax_applied', 'tax_rate_id', 'currency_id', 'tax_category_id', 'luxury_option_id','schedule_type','scheduled_date_time','scheduled_slot','start_date_time','end_date_time','additional_increments_hrs_min','total_booking_time','service_day','service_date','service_period','service_start_date','bid_number','bid_discount', 'slot_id', 'delivery_date', 'slot_price','dispatch_agent_price','dispatch_agent_id','schedule_slot','recurring_booking_type','recurring_week_day','recurring_week_type','recurring_day_data','recurring_booking_time', 'is_cart_checked'];

    protected $touches = ['cart'];

    public function cart(){
        return $this->belongsTo('App\Models\Cart','cart_id','id');
    }

    public function addon(){
       return $this->hasMany('App\Models\CartAddon', 'cart_product_id', 'id')->select('cart_product_id', 'addon_id', 'option_id');
    }


 	public function product(){
    $selectColuman = ['id','title', 'sku', 'url_slug','description', 'is_live', 'weight', 'weight_unit', 'averageRating', 'brand_id', 'tax_category_id','Requires_last_mile','pharmacy_check','tags','mode_of_service','delay_order_hrs','delay_order_min','delay_order_hrs_for_dine_in','delay_order_min_for_dine_in','delay_order_hrs_for_takeway','delay_order_min_for_takeway','pickup_delay_order_hrs','pickup_delay_order_min','dropoff_delay_order_hrs','dropoff_delay_order_min','id as delay_hrs_min','id as pickup_delay_hrs_min','id as dropoff_delay_hrs_min','id as delay_order_time','sell_when_out_of_stock','minimum_order_count','batch_count','has_inventory','category_id','service_charges_tax','delivery_charges_tax','container_charges_tax','fixed_fee_tax','service_charges_tax_id','delivery_charges_tax_id','container_charges_tax_id','fixed_fee_tax_id','age_restriction','markup_price','additional_increments','additional_increments_min','minimum_duration_min','minimum_duration','is_slot_from_dispatch','is_show_dispatcher_agent','individual_delivery_fee','is_long_term_service','service_duration','same_day_delivery','next_day_delivery','hyper_local_delivery','is_recurring_booking', 'security_amount', 'validate_pharmacy_check', 'address','cashback'];
    
      return $this->belongsTo('App\Models\Product')->select($selectColuman);
  }


  public function cartProduct(){
    $selectColuman = ['id','title', 'sku', 'url_slug','description', 'is_live', 'weight', 'weight_unit', 'averageRating', 'brand_id', 'tax_category_id','Requires_last_mile','pharmacy_check','tags','mode_of_service','delay_order_hrs','delay_order_min','delay_order_hrs_for_dine_in','delay_order_min_for_dine_in','delay_order_hrs_for_takeway','delay_order_min_for_takeway','pickup_delay_order_hrs','pickup_delay_order_min','dropoff_delay_order_hrs','dropoff_delay_order_min','id as delay_hrs_min','id as pickup_delay_hrs_min','id as dropoff_delay_hrs_min','id as delay_order_time','sell_when_out_of_stock','minimum_order_count','batch_count','has_inventory','category_id','service_charges_tax','delivery_charges_tax','container_charges_tax','fixed_fee_tax','service_charges_tax_id','delivery_charges_tax_id','container_charges_tax_id','fixed_fee_tax_id','age_restriction','markup_price','additional_increments','additional_increments_min','minimum_duration_min','minimum_duration','is_slot_from_dispatch','is_show_dispatcher_agent','individual_delivery_fee','is_long_term_service','service_duration','same_day_delivery','next_day_delivery','hyper_local_delivery','is_recurring_booking', 'security_amount', 'validate_pharmacy_check'];
    
      return $this->belongsTo('App\Models\Product','product_id')->select($selectColuman);
  }

    public function vendor(){
      return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id')->select('id', 'name', 'desc', 'logo', 'banner', 'latitude', 'longitude', 'order_pre_time', 'auto_reject_time', 'order_min_amount', 'show_slot', 'dine_in', 'delivery', 'takeaway', 'service_fee_percent','address','order_amount_for_delivery_fee','delivery_fee_minimum','delivery_fee_maximum','closed_store_order_scheduled','shiprocket_pickup_name','ahoy_location','fixed_fee','fixed_fee_amount','price_bifurcation','service_charges_tax','delivery_charges_tax','container_charges_tax','service_charges_tax_id'	,'delivery_charges_tax_id'	,'container_charges_tax_id'	, 'fixed_fee_tax','fixed_fee_tax_id','pincode','rental','pick_drop','on_demand','laundry','appointment','markup_price_tax_id','add_markup_price','slug', 'subscription_discount_percent', 'fixed_service_charge', 'service_charge_amount','city','state','country','slot_minutes','state_code','country_code','phone_no');
    }

    public function slotCounts(){
        return $this->hasMany('App\Models\VendorSlot', 'vendor_id', 'id');
     }

    public function pvariant(){
    	return $this->belongsTo('App\Models\ProductVariant', 'variant_id', 'id')->select('id', 'sku', 'product_id', 'title', 'price','markup_price', 'tax_category_id', 'barcode','container_charges','incremental_price','incremental_price_per_min', 'month_price', 'week_price','quantity');
    }

    public function coupon(){
      return $this->hasOne('App\Models\CartCoupon', 'cart_id', 'cart_id')->select("cart_id", "coupon_id", 'vendor_id');
    }

    public function currency(){
      return $this->hasOne('App\Models\Currency', 'vendor_id', 'vendor_id');
    }

    public function productDeliverySlot(){
      return $this->belongsTo('App\Models\DeliverySlot', 'slot_id', 'id');
    }

    public function vendorProducts(){
      return $this->hasMany(CartProduct::class, 'vendor_id', 'vendor_id')->leftjoin('client_currencies as cc', 'cc.currency_id', 'cart_products.currency_id')->select('cart_products.id', 'cart_products.cart_id', 'cart_products.product_id', 'cart_products.quantity', 'cart_products.variant_id', 'cart_products.is_tax_applied', 'cart_products.tax_category_id', 'cart_products.currency_id', 'cc.doller_compare', 'cart_products.vendor_id', 'cart_products.scheduled_date_time','cart_products.user_product_order_form','cart_products.start_date_time','cart_products.end_date_time','cart_products.additional_increments_hrs_min','cart_products.total_booking_time','cart_products.schedule_slot','cart_products.total_booking_time','cart_products.luxury_option_id','cart_products.service_day','cart_products.service_date','cart_products.service_period','cart_products.bid_number as bid_number','cart_products.bid_discount as bid_discount', 'cart_products.slot_id', 'cart_products.delivery_date', 'cart_products.slot_price','cart_products.dispatch_agent_id','cart_products.dispatch_agent_price','cart_products.recurring_booking_type','cart_products.recurring_week_day','cart_products.recurring_week_type','cart_products.recurring_day_data','cart_products.recurring_booking_time',  'cart_products.is_cart_checked')->orderBy('cart_products.created_at', 'asc')->distinct()->orderBy('cart_products.vendor_id', 'asc');
    }

    public function vendorProductSum(){
      return $this->hasMany(CartProduct::class, 'vendor_id', 'vendor_id')->sum('price');
    }


    public function getAddOnSetAndOptionAttribute()
    {
       $cart_product_id = $this->attributes['id'];
       $langId = Session::has('customerLanguage') ? Session::get('customerLanguage') : 1;
        $cart_addons = \App\Models\CartAddon::where('cart_product_id',$cart_product_id)->pluck('addon_id');
        $addoset = [];
        if($cart_addons){
          $cart_options = \App\Models\CartAddon::where('cart_product_id',$cart_product_id)->pluck('option_id');
          $addoset =  \App\Models\AddonSet::with(['translation' => function ($qry) use ($langId) {
                $qry->where('addon_set_translations.language_id', $langId);
            }])->whereIn('id',$cart_addons)->get();
          foreach($addoset as $key => $setsection)
          {
              $add_options = \App\Models\AddonOption::with(['translation' => function ($qry) use ($langId) {
                    $qry->where('addon_option_translations.language_id', $langId);
                }])->where('addon_id',$setsection->id)->whereIn('id',$cart_options)->get();
              $setsection->options = $add_options;
          }
        }
        return $addoset;
    }
    public function LongTermProducts(){
      return $this->hasOne('App\Models\LongTermServiceProducts','long_term_service_id','product_id');
    }

    public function productVariantByRoles(){
       return $this->hasMany('App\Models\ProductVariantByRole','product_id','product_id');
    }
}
