<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabBookingLayout extends Model
{
    use HasFactory;

    protected $fillable = ['title','slug','order_by','is_active','image','for_no_product_found_html'];


    public function translations(){
        $langData = $this->hasMany('App\Models\CabBookingLayoutTranslation');
        return $langData;
    }

    public function translation_one($langId = 0){
        return $this->hasOne('App\Models\CabBookingLayoutTranslation');
    }

    public function translation(){
      return $this->belongsTo('App\Models\CabBookingLayoutTranslation', 'id', 'cab_booking_layout_id' );
    }

    public function banner_image(){
      return $this->hasMany('App\Models\CabBookingLayoutBanner', 'cab_booking_layout_id' );
    }

    public function pickupCategories(){
        return $this->hasMany('App\Models\CabBookingLayoutCategory')->whereHas('categoryDetail',function($q){$q->where('deleted_at',null);});

    }

    public function getImageAttribute($value)
    {
      $values = array();
      $img = 'images/CabBANNER.jpg';
      if(!empty($value)){
        $img = $value;
      }else{
        return $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');

      return $values;
    }

    public function scopeApp($query)
    {
      return $query->where('type', 2);
    }

    public function scopeWeb($query)
    {
      return $query->where('type', 1);
    }
}
