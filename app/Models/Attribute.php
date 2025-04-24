<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Attribute extends Model
{
  protected $fillable = ['title', 'type', 'position', 'status'];

  protected $appends = ['actual_price'];


  public function translation(){
    return $this->hasMany('App\Models\AttributeTranslation')->join('languages', 'attribute_translations.language_id', 'languages.id')->select('attribute_translations.id', 'attribute_translations.title', 'attribute_translations.attribute_id', 'attribute_translations.language_id', 'languages.name');
  }

  public function translation_one(){
      $primary = ClientLanguage::orderBy('is_primary', 'desc')->first();
      if (isset($primary) && !empty($primary)) {
        $langset = $primary->language_id;
      } else {
        $langset = 1;
      }

      return $this->hasOne('App\Models\AttributeTranslation')->select('attribute_id', 'title')->where('language_id', $langset);
 }

  public function primary(){
    return $this->hasOne('App\Models\AttributeTranslation')->join('client_languages as cl', 'cl.language_id', 'attribute_translations.language_id')->where('cl.is_primary', 1); 
  }

  public function varcategory(){
    return $this->hasOne('App\Models\AttributeCategory'); 
  }

  public function option(){
    return $this->hasMany('App\Models\AttributeOption'); 
  }

  public function productAttribute(){
    return $this->hasMany('App\Models\ProductAttribute', 'attribute_id', 'id'); 
  }

  public function trans(){
       return $this->hasOne('App\Models\AttributeTranslation')->select('title', 'attribute_id'); 
  }

  public function category(){
    return $this->belongsToMany(Category::class, 'attribute_categories', 'attribute_id', 'category_id');
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

    public function getIconAttribute($value){
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

}
