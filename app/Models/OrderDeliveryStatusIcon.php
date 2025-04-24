<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryStatusIcon extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'image', 'image_url'];
    protected $table = 'order_delivery_status_icon';

    public function getImageUrlAttribute($value)
    {
      $values = array();
      $img = '';
      if(!empty($value)){
        $img = $value;
      
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');
      }else{
        $values['proxy_url'] = '';
        $values['image_path'] = '';
        $values['image_fit'] = '';
      }

      return $values;
    }

}
