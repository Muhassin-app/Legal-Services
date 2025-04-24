<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerAttribute extends Model
{
    protected $table = 'influ_attributes';
    protected $guarded = [];
    


    public function translation(){
        return $this->hasMany('App\Models\InfluencerAttributeTranslation', 'attribute_id', 'id')->join('languages', 'influ_attr_trans.language_id', 'languages.id')->select('influ_attr_trans.id', 'influ_attr_trans.title', 'influ_attr_trans.attribute_id', 'influ_attr_trans.language_id', 'languages.name');
      }
    
      public function translation_one(){
          $primary = ClientLanguage::orderBy('is_primary', 'desc')->first();
          if (isset($primary) && !empty($primary)) {
            $langset = $primary->language_id;
          } else {
            $langset = 1;
          }
    
          return $this->hasOne('App\Models\InfluencerAttributeTranslation', 'attribute_id', 'id')->select('attribute_id', 'title')->where('language_id', $langset);
     }
    
      public function primary(){
        return $this->hasOne('App\Models\InfluencerAttributeTranslation')->join('client_languages as cl', 'cl.language_id', 'attribute_translations.language_id')->where('cl.is_primary', 1); 
      }
    
      public function varcategory(){
        return $this->hasOne('App\Models\InfluencerAttributeCategory'); 
      }
    
      public function option(){
        return $this->hasMany('App\Models\InfluencerAttributeOption', 'attribute_id', 'id'); 
      }
    
    //   public function productAttribute(){
    //     return $this->hasMany('App\Models\ProductAttribute', 'attribute_id', 'id'); 
    //   }
    
      public function trans(){
           return $this->hasOne('App\Models\InfluencerAttributeTranslation')->select('title', 'attribute_id'); 
      }
    
      public function category(){
        return $this->belongsToMany(Category::class, 'attribute_categories', 'attribute_id', 'category_id');
      }

      public function influencerCategory(){
        return $this->belongsToMany(InfluencerCategory::class, 'influ_attr_cat', 'attribute_id', 'category_id');
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
