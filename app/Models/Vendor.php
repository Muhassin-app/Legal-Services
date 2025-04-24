<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
//use Laravel\Scout\Searchable;
use DB;
use \App\Http\Traits\{VendorTrait};
use Illuminate\Support\Facades\Auth;

class Vendor extends Model implements Auditable{

  use \OwenIt\Auditing\Auditable;
  use VendorTrait;

  //use Searchable;
    protected $fillable = ['name','slug','desc','short_desc','logo','banner','address','email','website','phone_no','latitude','longitude','order_min_amount','order_pre_time','auto_reject_time','commission_percent','commission_fixed_per_order','commission_monthly','dine_in','takeaway','delivery','status','add_category','setting','show_slot','vendor_templete_id','auto_accept_order', 'service_fee_percent','order_amount_for_delivery_fee','delivery_fee_minimum','delivery_fee_maximum','slot_minutes','closed_store_order_scheduled','pincode','return_request','ahoy_location','city','state','country','fixed_fee','fixed_fee_amount','price_bifurcation','instagram_url','service_charges_tax','delivery_charges_tax','container_charges_tax','fixed_fee_tax','service_charges_tax_id','delivery_charges_tax_id','container_charges_tax_id','fixed_fee_tax_id', 'cron_for_service_area','markup_price_tax_id','razorpay_bank_json','razorpay_contact_json', 'is_seller', 'fixed_service_charge', 'service_charge_amount', 'is_vendor_instant_booking', 'is_online'];

    protected $appends = ['is_wishlist'];
    public function serviceArea(){
       return $this->hasMany('App\Models\ServiceArea')->select('vendor_id', 'geo_array', 'name');
    }

    public function products(){
      return $this->hasMany('App\Models\Product', 'vendor_id', 'id')->where('is_long_term_service',0);
      // return $this->hasMany('App\Models\Product', 'vendor_id', 'id');
    }

    public function long_term_products(){
        return $this->hasMany('App\Models\Product', 'vendor_id', 'id')->where('is_long_term_service',1);
    }
    public function productsLive(){
      return $this->hasMany('App\Models\Product', 'vendor_id', 'id')->where('is_live','1');
    }

    public function vendor_promo(){
      return $this->belongsToMany('App\Models\Promocode', 'promocode_details', 'refrence_id', 'promocode_id')->where('expiry_date','>=',Carbon::now()->format('Y-m-d'))->where('promo_type_id',1)->select('amount','title');
    }

    public function slot(){
      $client = Client::first();
      $mytime = Carbon::now()->setTimezone($client->timezone);
      $current_time = $mytime->toTimeString();
      return $this->hasMany('App\Models\VendorSlot', 'vendor_id', 'id')->has('day')->where('start_time', '<', $current_time)->where('end_time', '>', $current_time);
    }

    public function slots(){
      return $this->hasMany('App\Models\VendorSlot', 'vendor_id', 'id');
    }

    public function slotsForPickup(){
      return $this->hasMany('App\Models\VendorSlot', 'vendor_id', 'id')->where('slot_type', '1');
    }
    public function slotsForDropoff(){
      return $this->hasMany('App\Models\VendorSlot', 'vendor_id', 'id')->where('slot_type', '2');
    }


    public function slotDates(){
      return $this->hasMany('App\Models\VendorSlotDate', 'vendor_id', 'id');
    }
    public function dineinCategories(){
      return $this->hasMany('App\Models\VendorDineinCategory', 'vendor_id', 'id');
    }

    public function slotDate(){
      $client = Client::first();
      $mytime = Carbon::now()->setTimezone($client->timezone);
      $current_date = $mytime->toDateString();
      $current_time = $mytime->toTimeString();
      return $this->hasMany('App\Models\VendorSlotDate', 'vendor_id', 'id')->where('specific_date', '=', $current_date)->where('start_time', '<', $current_time)->where('end_time', '>', $current_time);
    }

    public function avgRating(){
      return $this->hasMany('App\Models\Product', 'vendor_id', 'id')->avg('averageRating');
    }
    public function getReviewsCountAttribute(){
      $reviews_count = OrderProductRating::join('products', 'products.id', '=', 'order_product_ratings.product_id')->join('products','	products.vendor_id', '=', 'vendors.id')->where('vendors.id',$this->id)->count();
     return $reviews_count;
    }

    public function getLogoAttribute($value){
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      if (substr($img, 0, 7) == "http://" || substr($img, 0, 8) == "https://"){
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.$img;
      } else {
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      }
      $values['image_fit'] = \Config::get('app.FIT_URl');

      $values['image_s3_url'] = \Storage::disk('s3')->url($img);
      return $values;
    }

    public function getBannerAttribute($value){
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      if (substr($img, 0, 7) == "http://" || substr($img, 0, 8) == "https://"){
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.$img;
      } else {
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      }
      $values['image_fit'] = \Config::get('app.FIT_URl');

      $values['image_s3_url'] = \Storage::disk('s3')->url($img);
      return $values;
    }
    public static function getNameById($vendor_id){
      $result = Vendor::where('id', $vendor_id)->first();
      return $result->name;
    }

    public function orders(){
       return $this->hasMany('App\Models\OrderVendor', 'vendor_id', 'id');
    }

    public function activeOrders(){
       return $this->hasMany('App\Models\OrderVendor', 'vendor_id', 'id')->select('id', 'vendor_id')
              ->where('status', '!=', 3);
    }

    public function permissionToUser(){
      return $this->hasMany('App\Models\UserVendor');
    }


    public function product(){
      return $this->hasMany('App\Models\Product', 'vendor_id', 'id');
    }

    public function currentlyWorkingOrders(){
      return $this->hasMany('App\Models\OrderVendor', 'vendor_id', 'id')->select('id', 'vendor_id')
             ->whereIn('order_status_option_id',[2,4,5]);
    }
    public function CompletedOrders(){
      return $this->hasMany('App\Models\OrderVendor', 'vendor_id', 'id')->select('id', 'vendor_id')
             ->where('order_status_option_id',5);
    }


  public function getAllCategory(){
    return $this->hasMany('App\Models\VendorCategory');
  }
  public function getCustomCategory(){
    return $this->hasMany('App\Models\Category','vendor_id','id');
  }



  public function getById($id){
    return self::where('id',$id)->first();
  }
  public function Facilty(){
     // return $this->hasMany('App\Models\VendorFacilty', 'vendor_id', 'id');
      return $this->belongsToMany(\App\Models\Facilty::class, 'vendor_facilties', 'vendor_id', 'facilty_id');
  }
    public function getIsVendorCloseAttribute()
    {
     $value = 0;
     if($this->show_slot == 1){
        $value = 0 ;
     } else {
            if (($this->slotDate->isEmpty()) && ($this->slot->isEmpty())) {
              $value = 1;
            } else {
              $value = 0;
              if ($this->slotDate->isNotEmpty()) {
                  $this->opening_time = Carbon::parse($this->slotDate->first()->start_time)->format('g:i A');
                  $this->closing_time = Carbon::parse($this->slotDate->first()->end_time)->format('g:i A');
              } elseif ($this->slot->isNotEmpty()) {
                  $this->opening_time = Carbon::parse($this->slot->first()->start_time)->format('g:i A');
                  $this->closing_time = Carbon::parse($this->slot->first()->end_time)->format('g:i A');
              }
          }
     }
     return $value;

    }

    public function getVendorContactJsonAttribute()
    {
        return json_decode($this->razorpay_contact_json)->id;
    }

    public function getVendorBankJsonAttribute()
    {
          return json_decode($this->razorpay_bank_json);
    }

    public function VendorAdditionalInfo(){
      return $this->hasOne(\App\Models\VendorAdditionalInfo::class);
    }

    public function bids()
    {
      return $this->hasMany(Bid::class, 'vendor_id');
    }

    public function scopeByVendorSubscriptionRule($query,$preferences)
    {
      if($preferences->subscription_mode ==1){
        if(@getAdditionalPreference(['is_show_vendor_on_subcription'])['is_show_vendor_on_subcription'] == 1){
            $query =   $query->whereIn('id',$this->getSubscriptionVendorId());
        }
      }
      return $query;
    }

    public function scopeVendorOnline($query)
    {
        if(@getAdditionalPreference(['vendor_online_status'])['vendor_online_status'] == 1){
          return $query->where('is_online', 1);
        }
    }

    public function orderProducts()
    {
      return $this->belongsToMany(Product::class,'order_vendor_products','vendor_id','product_id');
    }

    public function vendorCategories()
    {
      return $this->belongsToMany(Category::class,'vendor_categories','category_id','vendor_id');
    }

    public function wishlistByUsers()
    {
      return $this->belongsToMany(User::class,'user_vendor_wishlists','vendor_id','user_id');
    }

    public function getIsWishlistAttribute()
    {
      return $this->wishlistByUsers()->where('user_id', Auth::id())->first() ? 1 : 0;
    }

    public function minimumPromo()
    {
      return $this->vendor_promo->min('amount');
    }
    public function userVendor()
    {
        return $this->hasOne(UserVendor::class, 'vendor_id', 'id');
    }
    public function categories()
    {
      return $this->belongsToMany(Category::class, 'vendor_categories');
    }

    public function scopeDistanceInMeters($query, $latitude = 0, $longitude = 0)
    {
        if ($latitude && $longitude) {
            return $query->selectRaw("vendors.*, (6371000 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))) AS distance_in_meter");
        }
        return $query->selectRaw("0 as distance_in_meter");
    }


}
