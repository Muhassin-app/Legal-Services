<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Variant extends Model
{
  protected $fillable = ['title', 'type', 'position', 'status'];

  protected $appends = ['actual_price'];


  public function translation(){
    return $this->hasMany('App\Models\VariantTranslation')->join('languages', 'variant_translations.language_id', 'languages.id')->select('variant_translations.id', 'variant_translations.title', 'variant_translations.variant_id', 'variant_translations.language_id', 'languages.name');
  }

  public function translation_one(){
      $primary = ClientLanguage::orderBy('is_primary', 'desc')->first();
      if (isset($primary) && !empty($primary)) {
        $langset = $primary->language_id;
      } else {
        $langset = 1;
      }

      return $this->hasOne('App\Models\VariantTranslation')->select('variant_id', 'title')->where('language_id', $langset);
 }

  public function primary(){
    return $this->hasOne('App\Models\VariantTranslation')->join('client_languages as cl', 'cl.language_id', 'variant_translations.language_id')->where('cl.is_primary', 1); 
  }

  public function varcategory(){
    return $this->hasOne('App\Models\VariantCategory'); 
  }

  public function option(){
    return $this->hasMany('App\Models\VariantOption'); 
  }
  public function trans(){
       return $this->hasOne('App\Models\VariantTranslation')->select('title', 'variant_id'); 
  }

  public function category(){
    return $this->belongsToMany(Category::class, 'variant_categories', 'variant_id', 'category_id');
  }
  public function product(){
    return $this->belongsTo(Product::class, 'id', 'product_id');
  }


  public function getActualPriceAttribute()
    {
       // if vendor actual price = price - markup price
        if(auth()->user() !=null && !auth()->user()->is_admin == 1){
                return $this->price - $this->markup_price??0;
        }
                return $this->price;
    }

    public function getPriceAttribute($value)
    {
        $checkMarkup = 0;
        $vendor = Product::where('id', $this->product_id)->value('vendor_id');
        $checkMarkup = Vendor::where('id',$vendor)->value('add_markup_price');
        //if vendor price add with markup price
           if(auth()->user() !=null && auth()->user()->is_admin == 1){
            $userVendor = UserVendor::where('user_id', auth()->id())->where('vendor_id', $vendor)->first();
            if($userVendor){
                return $value;
            }
        }
           if($checkMarkup){
                return $value + $this->markup_price??0;
            }
        
            return $value;  
           
    }

    public function getMarkupPriceAttribute($value)
    {
        $checkMarkup = 0;
        $vendor = Product::where('id', $this->product_id)->value('vendor_id');
        $checkMarkup = Vendor::where('id',$vendor)->value('add_markup_price');
        //if vendor price add with markup price
           if($checkMarkup){
                return $value;
            }
        
            return 0;  
           
    }

}
