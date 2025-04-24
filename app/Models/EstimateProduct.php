<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateProduct extends Model
{
    use HasFactory;

    public function primary(){
      $langData = $this->hasOne('App\Models\EstimateProductTranslation')->whereHas('primary');
      return $langData;
    }


    public function translations(){
      $langData = $this->hasMany('App\Models\EstimateProductTranslation');
      return $langData;
    }


    public function getIconAttribute($value){
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
        $ex = checkImageExtension($img);
            $values['proxy_url'] = \Config::get('app.IMG_URL1');
            if (substr($img, 0, 7) == "http://" || substr($img, 0, 8) == "https://"){
                $values['image_path'] = \Config::get('app.IMG_URL2').'/'.$img;
            } else {
                $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
            }
            $values['image_fit'] = \Config::get('app.FIT_URl');
            $values['original'] = $img;
        return $values;
      }
      return $value;

    }


    public function estimate_product_addons(){
      return $this->hasMany('App\Models\EstimateProductAddon', 'estimate_product_id' );
    }

    public function category(){
      return $this->belongsTo('App\Models\Category', 'category_id' );
    }

    public function estimate_product_translation(){
        return $this->belongsTo('App\Models\EstimateProductTranslation', 'id', 'estimate_product_id' );
    }


}
