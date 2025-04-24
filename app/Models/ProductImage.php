<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id','media_id','is_default'];

    public function variantImage(){
       return $this->hasMany('App\Models\ProductVariantImage')->select('product_variant_id', 'product_image_id'); 
    }

    public function image(){
        $img = 'default/default_image.png';
	   return $this->belongsTo('App\Models\VendorMedia','media_id','id')->select('id' ,'media_type', 'path')->withDefault([
                            "id"=> null,
                            "media_type"=> 1,
                            "path"=> [
                                "proxy_url"=> \Config::get('app.IMG_URL1'),
                                "image_path"=> \Config::get('app.IMG_URL2').'/'.$img,
                                "image_fit"=> \Config::get('app.FIT_URl'),
                                "original_image"=> \Storage::disk('s3')->url($img)
                            ]
        ]);
	}

    
}
