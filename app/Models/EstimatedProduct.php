<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimatedProduct extends Model
{
    use HasFactory;
    protected $table = 'estimated_product_new';


    public function estimated_product_cart(){
        return $this->belongsTo('App\Models\EstimatedProductCart', 'estimated_cart_id' );
    }

    public function estimated_product(){
        return $this->belongsTo('App\Models\EstimateProduct', 'product_id');
    }

    public function estimated_product_addons(){
        return $this->hasMany('App\Models\EstimatedProductAddons', 'estimated_product_id' );
    }

    public function category(){
        return $this->belongsTo('App\Models\Category', 'category_id' );
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
          return $values;
        }
        return $value;
  
      }
}
