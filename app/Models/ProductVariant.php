<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Session;
use App\Models\UserVendor;
use Illuminate\Support\Facades\Cache;

class ProductVariant extends Model
{
	protected $fillable = ['sku','product_id','title','quantity','price','position','compare_at_price','cost_price','barcode','currency_id','tax_category_id','inventory_policy','fulfillment_service','inventory_management','status', 'container_charges','markup_price','incremental_price','incremental_price_per_min','role_id', 'square_variant_id', 'square_variant_version', 'minimum_duration'];

    protected $appends = ['actual_price', 'new_price'];

	public function getImageAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
    //   $values['image_fit'] = \Config::get('app.FIT_URl');
      $values['image_fit'] = \Config::get('app.FILL_URL');
      return $values;
    }

    public function set(){
	    return $this->hasMany('App\Models\ProductVariantSet')
	    		->join('variant_options as opt', 'opt.id', 'product_variant_sets.variant_option_id')
	    		->join('variants as vari', 'vari.id', 'opt.variant_id')
	    		->select('product_variant_sets.product_variant_id', 'product_variant_sets.variant_option_id', 'opt.title', 'opt.hexacode', 'vari.type', 'vari.id');
	}

	public function vimageall(){
	    return $this->hasOne('App\Models\ProductVariantImage', 'product_variant_id', 'id')
	    		->select('product_variant_id', 'product_image_id')->groupBy('product_variant_id');
	}

	public function vimage(){
		return $this->hasOne('App\Models\ProductVariantImage', 'product_variant_id', 'id')
	    		->select('product_variant_id', 'product_image_id')->groupBy('product_variant_id');
	}
    public function category(){
        return $this->belongsToMany('App\Models\ProductCategory', 'products','category_id','id');
    }

	public function media(){
		return $this->hasMany('App\Models\ProductVariantImage', 'product_variant_id', 'id')->select('product_variant_id', 'product_image_id');
	}

	public function vset(){
	    return $this->hasMany('App\Models\ProductVariantSet')->select('product_variant_id','variant_option_id','product_id','variant_type_id');
	}

	public function translation($langId = 0){
        return $this->hasMany('App\Models\ProductTranslation', 'product_id', 'product_id');
    }
	public function translation_one($langId = 0){
        return $this->hasOne('App\Models\ProductTranslation', 'product_id', 'product_id');
    }
    public function optionData() {
	    return $this->belongsTo('App\Models\VariantOption', 'variant_option_id', 'id');
	}
    public function tax()
    {
        return $this->belongsTo('App\Models\TaxCategory', 'tax_category_id', 'id')->select('id', 'title', 'code');
    }
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id')->select('id', 'sku', 'title', 'averageRating', 'inquiry_only', 'vendor_id', 'has_inventory', 'sell_when_out_of_stock', 'batch_count', 'minimum_order_count','markup_price','minimum_duration_min','minimum_duration','additional_increments','buffer_time_duration','is_fix_check_in_time','check_in_time','additional_increments_min','buffer_time_duration_min', 'replaceable', 'return_days', 'returnable');
    }
    public function wishlist(){
       return $this->hasOne('App\Models\UserWishlist', 'product_id', 'product_id')->select('product_id', 'user_id');
    }

    public function productVariantByRole(){
        if(auth()->user() !=null){

            return $this->hasOne('App\Models\ProductVariantByRole', 'product_variant_id', 'id')->where('role_id', Auth::user()->role_id);
        }else{
            return $this->hasOne('App\Models\ProductVariantByRole', 'product_variant_id', 'id')->where('role_id', 1);
        }

	}

    public function checkIfInCart()
    {
        $user = Auth::user();
        if ($user) {
            $column = 'user_id';
            $value = $user->id;
        } else {
            $column = 'unique_identifier';
            $value = session()->get('_token');
        }

        return $this->hasMany('App\Models\CartProduct', 'variant_id', 'id')->whereHas('cart',function($qset)use($column,$value){
            $qset->where($column,$value);
        });
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

        return $this->hasMany('App\Models\CartProduct', 'variant_id', 'id')->whereHas('cart',function($qset)use($column,$value){
            $qset->where($column,$value);
        });
    }


    public function getActualPriceAttribute()
    {
       // if vendor actual price = price - markup price
        if(auth()->user() !=null && !auth()->user()->is_admin == 1){
            return $this->price - $this->markup_price??0;
        }

        //  price based on role
        if(auth()->user() !=null){

            $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);
            if($getAdditionalPreference['is_price_by_role'] == 1 && $this->productVariantByRole){
                return $this->productVariantByRole->amount;
            }
        }

        return $this->price;
    }

    // do not use this. price based on role
    public function getNewPriceAttribute()
    {

        if(auth()->user() !=null){
            $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);
            if($getAdditionalPreference['is_price_by_role'] == 1 && $this->productVariantByRole){
                return $this->productVariantByRole->amount;
            }
            return $this->price;
        }
        return $this->price;
    }

    public function getPriceAttribute($value)
    {
        $checkMarkup = 0;
        $cacheKey = 'Product_'.$this->product_id;
        $vendor = Cache::remember($cacheKey, 60, function () {
            return Product::where('id', $this->product_id)->select('vendor_id','tax_category_id')->first();
        });
        if(!empty($vendor)){
            $cacheKey = 'markup_price_'.$vendor->vendor_id;
            $checkMarkup = Cache::remember($cacheKey, 60, function () use($vendor) {
                return Vendor::where('id',$vendor->vendor_id)->value('add_markup_price');
            });

            //if vendor price add with markup price
            $user = auth()->user();
            if($user !=null && $user->is_admin == 1 ){
                $cacheKey = 'user_vendor_'.$user->id;
                $userVendor = Cache::remember($cacheKey, 60, function () use($vendor, $user) {
                    return UserVendor::where('user_id', $user->id)->where('vendor_id', $vendor->vendor_id)->first();
                });
                if($userVendor){
                    return decimal_format($value);
                }
            }
        }
        if($checkMarkup){
           return decimal_format($value + $this->markup_price??0);
        }

       //  price based on role
       if(auth()->user() !=null){
         $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);
         if($getAdditionalPreference['is_price_by_role'] == 1 && $this->productVariantByRole){
            return decimal_format($this->productVariantByRole->amount);
         }
       }
       return decimal_format($value);
    }

    public function getCompareAtPriceAttribute($value)
    {
        return decimal_format($value);
    }

    public function getMarkupPriceAttribute($value)
    {
        $checkMarkup = 0;
        $cacheKey = 'Product_'.$this->product_id;
        $vendor = Cache::remember($cacheKey, 60, function () {
            return Product::where('id', $this->product_id)->select('vendor_id')->first();
        });
        if(!empty($vendor)){
            $cacheKey = 'markup_price_'.$vendor->vendor_id;
            $checkMarkup = Cache::remember($cacheKey, 60, function () use($vendor) {
                return Vendor::where('id', $vendor->vendor_id)->value('add_markup_price');
            });
        }

        //if vendor price add with markup price
        if($checkMarkup){
            return decimal_format($value);
        }
        return 0;
    }
    public function measurements()
    {
        return $this->belongsToMany(
            Measurements::class,
            'product_measurement',
            'product_variant_id',
            'key_id'
        )->withPivot('product_id', 'key_value');
    }
}