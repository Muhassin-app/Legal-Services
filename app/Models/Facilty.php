<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facilty extends Model
{
    use HasFactory;

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
      return $values;
    }

    public function primary(){
        $langData = $this->hasOne('App\Models\FaciltyTranslation','facilties_id','id')->join('client_languages as cl', 'cl.language_id', 'facilty_translations.language_id')->where('cl.is_primary', 1);
        return $langData;
    }
    public function translations(){
        $langData = $this->hasMany('App\Models\FaciltyTranslation','facilties_id','id');
        return $langData;
    }
}
