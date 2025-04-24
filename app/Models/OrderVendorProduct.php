<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderVendorProduct extends Model
{
    use HasFactory;
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
      $values['image_fit'] = \Config::get('app.FIT_URl');
      return $values;
    }
    public function product(){
	    return $this->belongsTo('App\Models\Product', 'product_id', 'id');
	}
  public function statusDelievered(){
    $delievered = 6;
    return $this->hasOne('App\Models\VendorOrderStatus','order_id','order_id')->where('order_status_option_id', $delievered); 
  }
  
}
