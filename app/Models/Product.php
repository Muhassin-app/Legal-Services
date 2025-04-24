<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Auth, DB;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\{ProductVariant, CartProduct, UserWishlist};

class Product extends Model implements Auditable
{
  use SoftDeletes;
  use \OwenIt\Auditing\Auditable;


  protected $fillable = ['sku', 'title', 'url_slug', 'description', 'body_html', 'vendor_id', 'category_id', 'type_id', 'country_origin_id', 'is_new', 'is_featured', 'is_live', 'is_physical', 'weight', 'weight_unit', 'has_inventory', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'publish_at', 'inquiry_only', 'has_variant', 'averageRating', 'tags', 'pharmacy_check', 'deleted_at', 'celebrity_id', 'brand_id', 'tax_category_id', 'need_price_from_dispatcher', 'mode_of_service', 'delay_order_hrs', 'delay_order_min', 'pickup_delay_order_hrs', 'pickup_delay_order_min', 'dropoff_delay_order_hrs', 'dropoff_delay_order_min', 'minimum_order_count', 'batch_count', 'service_charges_tax', 'delivery_charges_tax', 'container_charges_tax', 'fixed_fee_tax', 'service_charges_tax_id', 'delivery_charges_tax_id', 'container_charges_tax_id', 'fixed_fee_tax_id', 'global_product_id', 'import_from_inventory', 'markup_price', 'seats', 'seats_for_booking', 'available_for_pooling', 'same_day_delivery', 'next_day_delivery', 'hyper_local_delivery', 'sync_from_inventory', 'sync_inventory_side_cat', 'store_id','is_recurring_booking','latitude','longitude','per_hour_price','km_included'];
  protected $appends = ['available_seats'];

  public function addOn()
  {
    return $this->hasMany('App\Models\ProductAddon', 'product_id', 'id')->select('product_id', 'addon_id');
  }

  public function sets()
  {
    return $this->hasMany('App\Models\ProductAddon')->join('addon_set_translations as ast', 'ast.addon_id', 'product_addons.addon_id')->select('product_addons.product_id', 'ast.title', 'product_addons.addon_id');
  }

  public function orderVendorProduct()
  {
      return $this->hasOne('App\Models\OrderVendorProduct', 'product_id', 'id')->select('*');
  }
  public function measurements()
    {
        return $this->belongsToMany(
            Measurements::class,
            'product_measurement',
            'product_id',
            'key_id'
        )->withPivot('product_variant_id', 'key_value')
        ->with(['productVariants' => function($query) {
            $query->select('product_variants.id', 'product_variants.title');
        }])
        ;
    }



    public function brand()
  {
    return $this->belongsTo('App\Models\Brand')->select('id', 'title', 'image');
  }

  public function vendor()
  {
    if (checkColumnExists('vendors', 'need_sync_with_order') && checkColumnExists('vendors', 'is_seller')) {
      return $this->belongsTo('App\Models\Vendor')->select('id', 'slug', 'name', 'desc', 'logo', 'address','show_slot', 'status', 'closed_store_order_scheduled', 'need_container_charges', 'fixed_fee', 'fixed_fee_amount', 'price_bifurcation', 'fixed_fee_tax_id', 'add_markup_price', 'latitude', 'longitude', 'need_sync_with_order', 'is_seller', 'fixed_service_charge', 'service_charge_amount', 'pick_drop', 'return_request', 'phone_no', 'dial_code', 'same_day_delivery', 'next_day_delivery', 'hyper_local_delivery', 'cutOff_time','service_fee_percent','service_charges_tax_id','service_charges_tax','orders_per_slot')->where('status',1);
    }

    return $this->belongsTo('App\Models\Vendor')->select('id', 'slug', 'name', 'desc', 'logo', 'show_slot', 'status', 'closed_store_order_scheduled', 'need_container_charges', 'fixed_fee', 'fixed_fee_amount', 'price_bifurcation', 'fixed_fee_tax_id', 'add_markup_price', 'latitude', 'longitude', 'fixed_service_charge', 'service_charge_amount', 'pick_drop', 'return_request', 'phone_no', 'dial_code', 'same_day_delivery', 'next_day_delivery', 'hyper_local_delivery', 'cutOff_time','service_fee_percent','service_charges_tax_id','service_charges_tax','orders_per_slot');
  }

  public function related()
  {
    return $this->hasMany('App\Models\ProductRelated')->select('product_id', 'related_product_id');
  }

  public function categories()
  {
    return $this->hasOne('App\Models\Category','category_id','id');
  }

  public function celebrities()
  {
    return $this->hasMany('App\Models\ProductCelebrity')->select('product_id', 'celebrity_id');
  }

  public function upSell()
  {
    return $this->hasMany('App\Models\ProductUpSell')->select('product_id', 'upsell_product_id');
  }

  public function crossSell()
  {
    return $this->hasMany('App\Models\ProductCrossSell')->select('product_id', 'cross_product_id');
  }


  public function variant()
  {
    return $this->hasMany('App\Models\ProductVariant')->select('id', 'sku', 'product_id', 'title', 'quantity', 'price', 'position', 'compare_at_price', 'barcode', 'cost_price', 'currency_id', 'tax_category_id', 'container_charges', 'markup_price', 'incremental_price', 'incremental_price_per_min', 'minimum_duration','week_price','month_price')->where('status', 1)->orderBy('id', 'asc');
  }



  public function translation($langId = 0)
  {
    if ($langId > 0) {
      return $this->hasMany('App\Models\ProductTranslation')->where('language_id', $langId);
    } else {
      return $this->hasMany('App\Models\ProductTranslation');
    }
  }
  public function translation_one($langId = 0)
  {
    return $this->hasOne('App\Models\ProductTranslation');
  }
  public function primary()
  {

    $langData = $this->hasOne('App\Models\ProductTranslation')->join('client_languages as cl', 'cl.language_id', 'product_translations.language_id')->select('product_translations.product_id', 'product_translations.title', 'product_translations.language_id', 'product_translations.body_html', 'product_translations.meta_title', 'product_translations.meta_keyword', 'product_translations.meta_description')->where('cl.is_primary', 1);

    return $langData;

  }

  public function category()
  {
    return $this->hasOne('App\Models\ProductCategory')->select('product_id', 'category_id');
  }

  public function categoryName()
  {
    return $this->hasOne('App\Models\CategoryTranslation', 'category_id', 'category_id')->select('id', 'name', 'category_id');
  }

  public function variantSet()
  {
    return $this->hasMany('App\Models\ProductVariantSet')->select('id','product_id', 'product_variant_id', 'variant_type_id', 'variant_option_id')->groupBy('variant_type_id')->orderBy('product_variant_id');
  }

  public function variantSetNew()
  {
    return $this->hasOne('App\Models\ProductVariantSet')->select('product_id', 'product_variant_id', 'variant_type_id', 'variant_option_id')->groupBy('variant_type_id')->orderBy('product_variant_id');
  }

  public function vatoptions()
  {
    return $this->hasMany('App\Models\ProductVariantSet')->select('product_id', 'product_variant_id', 'variant_option_id')->groupBy('variant_option_id');
  }

  public function variantSets()
  {
    return $this->hasMany('App\Models\ProductVariantSet');
  }

  public function media()
  {
    return $this->hasMany('App\Models\ProductImage')->select('id', 'product_id', 'media_id', 'is_default');
  }

  public function pimage()
  {
    return $this->hasMany('App\Models\ProductImage')->select('product_images.product_id', 'product_images.media_id', 'product_images.is_default', 'vendor_media.media_type', 'vendor_media.path')->join('vendor_media', 'vendor_media.id', 'product_images.media_id')->limit(1);
  }

  public function baseprice()
  {
    return $this->hasMany('App\Models\ProductVariant')->select('id', 'product_id', 'price')->groupBy('product_id');
  }

  /* for app */

  public function variants()
  {
    return $this->hasMany('App\Models\ProductVariant')->select('id', 'sku', 'product_id', 'quantity', 'price', 'barcode', 'container_charges', 'markup_price', 'incremental_price', 'incremental_price_per_min');
  }

  public function reviews()
  {
    return $this->hasMany('App\Models\OrderProductRating', 'product_id', 'id');
  }

  public function productVariantByRoles()
  {
    return $this->hasMany('App\Models\ProductVariantByRole')->select('id', 'role_id', 'product_id', 'product_variant_id', 'amount','quantity');
  }

  public function variant_list()
  {
    return $this->hasMany('App\Models\ProductVariantSet')
      ->join('variants as pv', 'pv.id', 'product_variant_sets.variant_type_id')
      ->select('product_id', 'title', 'type', 'position', 'status')
      ->groupBy('product_variant_sets.variant_type_id')
      ->orderBy('pv.position', 'asc');
  }

  public function variant1()
  {
    return $this->hasMany('App\Models\ProductVariant', 'product_id', 'pro_id')->select('id', 'sku', 'product_id');
  }

  public function inwishlist()
  {
    return $this->hasOne('App\Models\UserWishlist')->select('product_id', 'user_id');
  }

  public function taxCategory()
  {
    return $this->belongsTo('App\Models\TaxCategory', 'tax_category_id', 'id')->select('id', 'title', 'code');
  }

  public function tags()
  {
    return $this->hasMany('App\Models\ProductTag', 'product_id', 'id');
  }
  public function all_tags()
  {
    return $this->hasMany('App\Models\ProductTag', 'product_id', 'id');
  }

  public function getDelayOrderTimeAttribute()
  {
    $data = [];
    $type = ((session()->get('vendorType')) ? session()->get('vendorType') : 'delivery');
    if ($type == 'dine_in') {
      $data['delay_order_hrs'] = $this->attributes['delay_order_hrs_for_dine_in'];
      $data['delay_order_min'] = $this->attributes['delay_order_min_for_dine_in'];
    } elseif ($type == 'takeaway') {
      $data['delay_order_hrs'] = $this->attributes['delay_order_hrs_for_takeway'];
      $data['delay_order_min'] = $this->attributes['delay_order_min_for_takeway'];
    } else {
      $data['delay_order_hrs'] = $this->attributes['delay_order_hrs'];
      $data['delay_order_min'] = $this->attributes['delay_order_min'];
    }
    return $data;
  }


  public function getDelayHrsMinAttribute()
  {
    $data = $this->getDelayOrderTimeAttribute();
    $delay_order_hrs = $data['delay_order_hrs'];
    $delay_order_min = $data['delay_order_min'];

    if (@$delay_order_hrs > 0 || @$delay_order_min > 0) {
      $total_minutues = ($delay_order_hrs * 60) + $delay_order_min;

      $date = Carbon::now()
        ->addMinutes($total_minutues)
        ->format('Y-m-d\TH:i');
      if (Auth::user()) {
        $timezone = Auth::user()->timezone;
        $date = convertDateTimeInTimeZone($date, $timezone, 'Y-m-d\TH:i');
      }
      return $date;
    }
    return 0;

  }


  public function getPickupDelayHrsMinAttribute()
  {
    $delay_order_hrs = $this->attributes['pickup_delay_order_hrs'];
    $delay_order_min = $this->attributes['pickup_delay_order_min'];

    if ($delay_order_hrs > 0 || $delay_order_min > 0) {
      $total_minutues = ($delay_order_hrs * 60) + $delay_order_min;

      $date = Carbon::now()
        ->addMinutes($total_minutues)
        ->format('Y-m-d\TH:i');
      if (Auth::user()) {
        $timezone = Auth::user()->timezone;
        $date = convertDateTimeInTimeZone($date, $timezone, 'Y-m-d\TH:i');
      }
      return $date;
    }
    return 0;

  }

  public function getDropoffDelayHrsMinAttribute()
  {
    $delay_order_hrs = $this->attributes['dropoff_delay_order_hrs'];
    $delay_order_min = $this->attributes['dropoff_delay_order_min'];

    $delay_order_hrs_pick = $this->attributes['pickup_delay_order_hrs'];
    $delay_order_min_pick = $this->attributes['pickup_delay_order_min'];

    if ($delay_order_hrs > 0 || $delay_order_min > 0) {
      $total_minutues = (($delay_order_hrs + $delay_order_hrs_pick) * 60) + ($delay_order_min + $delay_order_min_pick);

      $date = Carbon::now()
        ->addMinutes($total_minutues)
        ->format('Y-m-d\TH:i');
      if (Auth::user()) {
        $timezone = Auth::user()->timezone;
        $date = convertDateTimeInTimeZone($date, $timezone, 'Y-m-d\TH:i');
      }
      return $date;
    }
    return 0;

  }

  public function ProductFaq()
  {
    return $this->hasMany('App\Models\ProductFaq', 'product_id', 'id');
  }

  public function checkIfInCartApp()
  {
    $user = Auth::user();
    if ($user->id && $user->id > 0) {
      $column = 'user_id';
      $value = $user->id;
    } else {
      $column = 'unique_identifier';
      $value = $user->system_user;
    }

    return $this->hasMany('App\Models\CartProduct', 'product_id', 'id')->whereHas('cart', function ($qset) use ($column, $value) {
      $qset->where($column, $value);
    });
  }
  public function getByVendorId($vendor_id)
  {
    return self::where('vendor_id', $vendor_id)->with('addOn', 'category', 'celebrities', 'crossSell', 'media', 'related', 'all_tags', 'translation', 'upSell', 'variant', 'variantSets')->get();
  }
  public function getProductBySku($sku)
  {
    return self::where('sku', $sku)->first();
  }
  public function getProductByCategory($category_id)
  {
    return self::where('category_id', $category_id)->get();
  }



  public function variantPrice()
  {
    return $this->hasOne('App\Models\ProductVariant')->select('*', 'price as variant_price')->first();
  }

  public function variantSingle()
  {
    return $this->hasOne('App\Models\ProductVariant');
  }



  public function OrderProduct()
  {
    return $this->hasMany('App\Models\OrderProduct')->where(function ($q) {
      $q->groupBy('order_id');
    });
  }

  public function UserWishlist()
  {
    return $this->hasMany('App\Models\UserWishlist')->where(function ($q) {
      $q->groupBy('product_id');
    });

  }


  public function productTranslation()
  {
    return $this->hasMany('App\Models\ProductTranslation');
  }

  public function OrderReturnRequest()
  {
    return $this->hasManyThrough('App\Models\OrderReturnRequest', 'App\Models\OrderProduct', 'product_id', 'order_vendor_product_id', 'id', 'id');
  }
  public function productcategory()
  {
    return $this->hasOne('App\Models\Category', 'id', 'category_id');
  }
  public function scopeByProductCategoryServiceType($query, $type)
  {
    $categoryTypesArray = getServiceTypesCategory($type);
    return $query->whereHas('productcategory', function ($q) use ($categoryTypesArray) {
      $q->whereIn('type_id', $categoryTypesArray);
    });
  }

  public function scopeByLongTermProductCategoryServiceType($query, $type)
  {
    $categoryTypesArray = getServiceTypesCategory($type);
    return $query->whereHas('LongTermProducts.product.productcategory', function ($q) use ($categoryTypesArray) {
      $q->whereIn('type_id', $categoryTypesArray);
    });
  }
  // check product validate
  public function scopeByProductWhereCheck($query)
  {
    $query = $query->where(['is_live' => 1]);
    if (checkColumnExists('products', 'is_long_term_service')) {
      $query = $query->where('is_long_term_service', 0);
    }
    return $query;
  }
  // check product validate
  public function scopeByProductLongTerm($query)
  {
    $query = $query->where(['is_live' => 1]);
    if (checkColumnExists('products', 'is_long_term_service')) {
      $query = $query->where('is_long_term_service', 1);
    }
    return $query;
  }

  public function getActualPriceAttribute()
  {
    // if vendor actual price = price - markup price
    if (auth()->user() != null && !auth()->user()->is_admin == 1) {
      return $this->price - $this->markup_price ?? 0;
    }
    return $this->price;
  }

  public function getPriceAttribute($value)
  {
    $checkMarkup = 0;
    $vendor = Product::where('id', $this->product_id)->value('vendor_id');
    $checkMarkup = Vendor::where('id', $vendor)->value('add_markup_price');
    //if vendor price add with markup price
    if (auth()->user() != null && auth()->user()->is_admin == 1) {
      $userVendor = UserVendor::where('user_id', auth()->id())->where('vendor_id', $vendor)->first();
      if ($userVendor) {
        return $value;
      }
    }
    if ($checkMarkup) {
      return $value + $this->markup_price ?? 0;
    }

    return $value;

  }

  public function getMarkupPriceAttribute($value)
  {
    $checkMarkup = 0;
    $vendor = Product::where('id', $this->product_id)->value('vendor_id');
    $checkMarkup = Vendor::where('id', $vendor)->value('add_markup_price');
    //if vendor price add with markup price
    if ($checkMarkup) {
      return $value;
    }

    return 0;

  }

  public function productByRole()
  {
    if (auth()->user() != null) {
      return $this->hasOne('App\Models\ProductByRole', 'product_id', 'id')->where('role_id', Auth::user()->role_id);
    } else {
      return $this->hasOne('App\Models\ProductByRole', 'product_id', 'id')->where('role_id', 1);
    }
  }

  public function productByRoleForAdmin()
  {
    return $this->hasMany('App\Models\ProductByRole', 'product_id', 'id');
  }

  public function getMinimumOrderCountAttribute($value)
  {
    //  price based on role
    if (auth()->user() != null) {
      $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);
      if ($getAdditionalPreference['is_price_by_role'] == 1 && $this->productByRole) {
        return $this->productByRole->minimum_order_count;
      }
    }
    return $value;
  }
  // in long term service
  public function LongTermProducts()
  {
    $langData = $this->hasOne('App\Models\LongTermServiceProducts', 'long_term_service_id', 'id');
    return $langData;
  }

  public static function productDelete($id)
  {
    try {
      DB::beginTransaction();
      $product = Product::find($id);
      $dynamic = time();

      Product::where('id', $id)->update(['sku' => $product->sku . $dynamic, 'url_slug' => $product->url_slug . $dynamic]);

      $tot_var = ProductVariant::where('product_id', $id)->get();
      foreach ($tot_var as $varr) {
        $dynamic = time() . substr(md5(mt_rand()), 0, 7);
        ProductVariant::where('id', $varr->id)->update(['sku' => $product->sku . $dynamic]);
      }

      Product::where('id', $id)->delete();

      CartProduct::where('product_id', $id)->delete();
      UserWishlist::where('product_id', $id)->delete();
      DB::commit();
      return 1;
    } catch (\Exception $ex) {
      DB::rollback();
      pr($ex->getMessage());
      return 2;
    }
  }
  public function LongTermProduct()
  {
    return $this->belongsToMany(\App\Models\Product::class, "long_term_service_products", "long_term_service_id", "product_id");
    //$langData = $this->morphMany('App\Models\LongTermServiceProducts','long_term_service_id','id');

  }

  public function ServicePeriod()
  {
    return $this->hasMany('App\Models\LongTermServicePeriod');
  }

  public function ProductAttribute()
  {
      return $this->hasMany('App\Models\ProductAttribute', 'product_id', 'id');
  }

  public function tollpass()
  {
    return $this->belongsTo('App\Models\TollPassOrigin', 'toll_pass_id', 'id')->select('id', 'toll_pass', 'desc');
  }

  public function travelmode()
  {
    return $this->belongsTo('App\Models\TravelMode', 'travel_mode_id', 'id')->select('id', 'travelmode', 'desc');
  }

  public function emissiontype()
  {
    return $this->belongsTo('App\Models\VehicleEmissionType', 'emission_type_id', 'id')->select('id', 'emission_type', 'desc');
  }

  public function syncProductDeliverySlot(){
    return $this->belongsToMany('App\Models\DeliverySlot', 'delivery_slots_product', 'product_id', 'delivery_slot_id')->withTimestamps();
  }

  public function product_availability()
  {
    return $this->hasMany('App\Models\ProductAvailability');
  }
  public function getAvailableSeatsAttribute()
  {
    $booking_seats = OrderProduct::where('product_id',$this->id)->sum('booking_seats');
    $available_seats = $this->seats - $booking_seats;
    if($available_seats > 0){
      return $available_seats;
    }
    return 0;
  }

  public function bookingOptions(){
    return $this->hasMany('App\Models\ProductBookingOption', 'product_id', 'id');
  }

  public function rentalProtections(){
    return $this->hasMany('App\Models\ProductRentalProtection', 'product_id', 'id');
  }

  public function cartBookingOptions(){
    return $this->hasOne('App\Models\CartBookingOption', 'product_id', 'id');
  }

  public function cartRentalProtections(){
    return $this->hasOne('App\Models\CartRentalProtection', 'product_id', 'id');
  }

  public function productBooked(){
    return $this->hasMany('App\Models\ProductBooking', 'product_id', 'id');
  }
}