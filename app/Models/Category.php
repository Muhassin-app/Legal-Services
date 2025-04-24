<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;
use Auth;
class Category extends Model
{
  use SoftDeletes;

    protected $fillable = ['slug','icon','icon_two', 'image', 'is_visible', 'status', 'position', 'is_core', 'can_add_products', 'parent_id', 'vendor_id', 'client_code', 'display_mode', 'type_id','warning_page_id', 'template_type_id', 'warning_page_design','is_p2p'];
    public $timestamps = true;

    public function translation(){
      return $this->hasMany('App\Models\Category_translation')->join('client_languages as cl', 'cl.language_id', 'category_translations.language_id')->join('languages', 'category_translations.language_id', 'languages.id')->select('category_translations.*', 'languages.id as langId', 'languages.name as langName', 'cl.is_primary')->where('cl.is_active', 1)->orderBy('cl.is_primary', 'desc');
    }
    
    public function translationLatest(){
        return $this->hasOne('App\Models\Category_translation')->join('client_languages as cl', 'cl.language_id', 'category_translations.language_id')->join('languages', 'category_translations.language_id', 'languages.id')->select('category_translations.*', 'languages.id as langId', 'languages.name as langName', 'cl.is_primary')->where('cl.is_active', 1)->orderBy('cl.is_primary', 'desc');       
    }
  
    public function translation_one(){

      $langset = Session::has('adminLanguage') ? Session::get('adminLanguage') : '';
      if(!$langset){
        $primary = ClientLanguage::orderBy('is_primary','desc')->first();
        if(isset($primary) && !empty($primary))
        {
          $langset = $primary->language_id ;
        }else{
          $langset = 1;
        }
      }
      return $this->hasOne('App\Models\Category_translation')->select('category_id', 'name')->where('language_id', $langset)->latest();
    }

    public function english(){
      $primary = ClientLanguage::orderBy('is_primary','desc')->first();
      if(isset($primary) && !empty($primary))
      {
        $langset = $primary->language_id ;
      }else{
        $langset = 1;
      }
       return $this->hasOne('App\Models\Category_translation')->select('category_id', 'name')->where('language_id', $langset);
    }

    public function primary(){

      $langData = $this->hasOne('App\Models\Category_translation')->latest()->join('client_languages as cl', 'cl.language_id', 'category_translations.language_id')->select('category_translations.created_at','category_translations.category_id', 'category_translations.name', 'category_translations.language_id', 'category_translations.meta_description', 'category_translations.language_id as langId', 'category_translations.meta_title','category_translations.meta_keywords')->where('cl.is_primary', 1);

      if(!$langData){
        $langData = $this->hasOne('App\Models\Category_translation')->join('client_languages as cl', 'cl.language_id', 'category_translations.language_id', 'category_translations.language_id as langId')->select('category_translations.category_id', 'category_translations.name', 'category_translations.language_id', 'category_translations.meta_title','category_translations.meta_keywords')->limit(1);
      }
      return $langData;
    }

    public function tags()
    {
        return $this->hasMany(CategoryTag::class)->select('category_id', 'tag');
    }

    public function brands()
    {
        return $this->hasMany(BrandCategory::class)->join('brands', 'brands.id', 'brand_categories.brand_id')
                ->select('brand_categories.category_id', 'brand_categories.brand_id', 'brands.id', 'brands.image');
    }

    public function cateBrands()
    {
      return $this->belongsToMany(Brand::class,'brand_categories','category_id','brand_id')
      ->select('brands.id', 'brands.image', 'brands.title');
    }

    public function childs()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id')->join('types', 'types.id', 'categories.type_id')
        ->select('categories.id', 'categories.slug', 'categories.parent_id', 'categories.icon', 'categories.icon_two','categories.image','type_id', 'types.title as redirect_to')->orderBy('position', 'ASC');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function productswithLimit()
    {
        return $this->hasMany(Product::class, 'category_id', 'id')->take(9);
    }
    public function type(){
      return $this->belongsTo('App\Models\Type')->select('id', 'title','service_type');
    }
    public function vendor(){
      return $this->belongsTo('App\Models\Vendor');
    }

    public function categoryTag()
    {
        return $this->hasOne(CategoryTag::class)->select('category_id', 'tag');
    }

    public function categoryRoleAssigned()
    {
      if(auth()->user() !=null){
        return $this->hasOne('App\Models\CategoryRole', 'category_id', 'id')->where('role_id', Auth::user()->role_id);
      }else{
          return $this->hasOne('App\Models\CategoryRole', 'category_id', 'id')->where('role_id', 1);
      }
    }

    public function getImageAttribute($value)
    {
      $values = array();
      $values['is_original'] = false; 
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
        $values['is_original'] = true; 
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');
      $values['image'] = $value;
      return $values;
    }

    public function getIconAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');
      $values['icon'] = $value;
      return $values;
    }

    public function getSubCatBannersAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $imgs = explode(',', $value);
        foreach($imgs as $img){
          $ex = checkImageExtension($img);
          $banner['proxy_url'] = \Config::get('app.IMG_URL1');
          $banner['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
          $banner['image_fit'] = \Config::get('app.FIT_URl');
          $banner['sub_cat_banners'] = $value;
          $values[] = $banner;
        }
      }
      return $values;
    }

    public function getIconTwoAttribute($value)
    {
      $values = array();
      if(!empty($value)){
        $img = $value;
        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');
        return $values;
      }
      return $value;
    }
    public function getIcon2Attribute($value)
    {
      $values = array();
      if(!empty($value)){
        $img = $value;
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img);
        $values['image_fit'] = \Config::get('app.FIT_URl');
        return $values;
      }
      return $value;
      
    }


    public function parent(){
      return $this->belongsTo('App\Models\Category','parent_id','id');
   }

  public function allParentsAccount()
  {
    return $this->parent()->select('id', 'slug', 'parent_id')->with('allParentsAccount');
  }



  public function translationSet(){
    return $this->hasMany('App\Models\Category_translation');
  }


  public function translationSetUnique(){
    return $this->hasMany('App\Models\Category_translation')->join('client_languages as cl', 'cl.language_id', 'category_translations.language_id')->join('languages', 'category_translations.language_id', 'languages.id')->select('category_translations.*', 'languages.id as langId', 'languages.name as langName', 'cl.is_primary')->where('cl.is_active', 1)->groupBy('category_translations.language_id')->orderBy('cl.is_primary', 'desc');
  }


  public function checkCategory($category,$vendor_id){
    return self::where('vendor_id',$vendor_id)->whereIn('slug',[$category->slug,$category->slug.'_'.$vendor_id])->first();
  }


  public function vendorCategory(){
    return $this->hasMany('App\Models\VendorCategory');
  }


  public function data()
  {
    return $this->hasMany(Product::class,'category_id', 'id');
  }
  
  public function scopeServiceType($query)
  {  
      $categoryTypesArray = getCategoryTypes();
      return $query->whereHas('type',function($q) use ($categoryTypesArray){ 
        $q->whereIn('service_type',$categoryTypesArray);
      });
  }

  public function categoryMobileBanner(){
    return $this->hasMany('App\Models\MobileBanner', 'redirect_category_id', 'id')->where('status', 1);
  }
}
