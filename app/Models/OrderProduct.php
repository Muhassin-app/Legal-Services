<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderProduct extends Model{
    use HasFactory;

    protected $table = 'order_vendor_products';
    protected $casts = ['price' => 'double'];
    protected $appends = ['image_base64'];
    public static $withoutAppends = false;

    public function scopeWithoutAppends($query){
        self::$withoutAppends = true;
        return $query;
    }

    protected function getArrayableAppends(){
        if (self::$withoutAppends){
            return [];
        }
        return parent::getArrayableAppends();
    }

    public function order_product_status()
    {
        return $this->hasOne('App\Models\VendorOrderProductStatus', 'order_vendor_product_id')->orderBy('id','DESC')->limit(1);
    }
    public function orderProductStatus()
    {
        return $this->hasMany('App\Models\VendorOrderProductStatus', 'order_vendor_product_id');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id')->select('id', 'name', 'desc', 'logo', 'banner', 'order_pre_time', 'auto_reject_time', 'order_min_amount');
    }
    public function categories()
    {
      return $this->hasOne('App\Models\Category','category_id','id');
    }
  
    public function coupon(){
      return $this->hasOne('App\Models\CartCoupon', 'vendor_id', 'vendor_id')->select("cart_id", "coupon_id", 'vendor_id');
    }
    public function addon(){
       return $this->hasMany('App\Models\OrderProductAddon', 'order_product_id', 'id');
    }
    public function product(){
      return $this->belongsTo('App\Models\Product')->select('id', 'sku', 'url_slug', 'is_live', 'weight', 'weight_unit', 'averageRating', 'brand_id', 'tax_category_id', 'category_id','minimum_duration_min','is_long_term_service', 'returnable', 'replaceable', 'return_days', 'sync_from_inventory', 'height', 'breadth', 'length', 'description', 'latitude', 'longitude', 'address','title')->withTrashed();
    }
     public function variant(){
      return $this->hasMany('App\Models\ProductVariant','product_id', 'product_id')->select('id', 'sku', 'product_id', 'title', 'quantity', 'price', 'position', 'compare_at_price', 'barcode', 'cost_price', 'currency_id', 'tax_category_id')->where('status', 1);
    }
    public function pvariant(){
      return $this->belongsTo('App\Models\ProductVariant', 'variant_id', 'id')->select('id', 'sku', 'product_id', 'title', 'price', 'tax_category_id', 'barcode','price','week_price','month_price');
    }
    public function media(){
        return $this->hasMany('App\Models\ProductImage', 'product_id', 'product_id')->select('product_id', 'media_id', 'is_default');
    }
    public function reqCancelOrder()
    {
        return $this->hasOne('App\Models\OrderCancelRequest', 'order_vendor_product_id'); //, 'order_id', 'id'
    }
    public function pimage(){
        return $this->hasMany('App\Models\ProductImage', 'order_product_id', 'order_product_id')->select('product_images.product_id', 'product_images.media_id', 'product_images.is_default', 'vendor_media.media_type', 'vendor_media.path')->join('vendor_media', 'vendor_media.id', 'product_images.media_id')->limit(1);
    }
    public function vendorProducts(){
      return $this->hasMany(OrderProduct::class, 'vendor_id', 'vendor_id')->orderBy('order_vendor_products.created_at', 'asc')->orderBy('order_vendor_products.vendor_id', 'asc');
    }
    public function translation(){
      return $this->hasOne('App\Models\ProductTranslation','product_id', 'product_id');
    }
    public function prescription(){
	    return $this->hasMany('App\Models\OrderProductPrescription' , 'product_id', 'product_id');
	  }
    public function productRating(){
      return $this->hasOne('App\Models\OrderProductRating', 'order_vendor_product_id', 'id');
    }
    public function order(){
      return $this->belongsTo('App\Models\Order', 'order_id', 'id')->select('id', 'order_number', 'user_id');
    }
    public function productReturn(){
      return $this->hasOne('App\Models\OrderReturnRequest', 'order_vendor_product_id', 'id');
    }
    public function getImageAttribute($value){
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');
      return $values;
    }
    public function getImageBase64Attribute()
    {
      if(!empty($this->attributes['image']))
      $img = $this->attributes['image'];
      else
      $img = 'default/default_image.png';

      $image = $this->getImageAttribute($img);
      $image_url = $image['proxy_url'].'100/100'.$image['image_path'];
      try{
        if(isset($image_url) && !empty($image_url))
        $base64 = base64_encode(@file_get_contents($image_url));
        else
        $base64 = '';
        return $base64;
     }
     catch(Exception $e) {
      $base64 = '';
      return $base64;
      }
    }


    public function getActualPriceAttribute()
    {
        //if vendor actual price = price - markup price
        if(auth()->user() !=null && auth()->user()->is_admin == 1){
                return $this->price - $this->markup_price??0;
        }
                return $this->price;
    }


    // public function getMarkupPriceAttribute($value)
    // {
    //   if(auth()->user() !=null && auth()->user()->is_admin == 1){
    //             return 0;
    //   }
    //   return $value;
    // }

    public function getPriceAttribute($value)
    {
        // //if vendor price add with markup price
        //    if(auth()->user() !=null && auth()->user()->is_admin == 1){
        //     $vendor = Product::where('id', $this->product_id)->value('vendor_id');
        //     $userVendor = UserVendor::where('user_id', auth()->id())->where('vendor_id', $vendor)->first();
        //     if($userVendor){
        //         return $value;
        //     }
        //    }
        //        // return $value + $this->markup_price??0;
                return $value??0;

    }
    // get dispatch route for single product in case of on demand and appointmenet by harbans :)
    public function Routes(){
      return $this->hasMany('App\Models\OrderProductDispatchRoute', 'order_vendor_product_id', 'id')->with('DispatchStatus');
    }
    public function LongTermService(){
      return $this->hasOne('App\Models\OrderLongTermServices', 'order_product_id', 'id');
    }
    public function RecurringService(){
      return $this->hasMany('App\Models\OrderLongTermServiceSchedule', 'order_vendor_product_id', 'id');
    }

    public function order_vendor(){
      return $this->belongsTo('App\Models\OrderVendor', 'order_vendor_id', 'id');
  }
}
