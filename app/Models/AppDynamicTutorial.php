<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppDynamicTutorial extends Model
{

   public function getFileNameAttribute($value){
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      if (substr($img, 0, 7) == "http://" || substr($img, 0, 8) == "https://"){
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.$img.$ex;
      } else {
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      }
      $values['image_fit'] = \Config::get('app.FIT_URl');
      return $values;
    }

}