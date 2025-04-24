<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LongTermServiceProducts extends Model
{
    use HasFactory;
    public function product(){
        $langData = $this->hasOne('App\Models\Product','id','product_id');
        return $langData;
    }

    public function addons(){
        $langData = $this->hasMany('App\Models\LongTermServiceProductAddons','long_term_service_product_id','id');
        return $langData;
    }
    
    public static function saveProducts($request){
        $longTermProduct =  LongTermServiceProducts::where('long_term_service_id',$request->long_term_service_id)->first() ?? new LongTermServiceProducts();
        $longTermProduct->long_term_service_id = $request->long_term_service_id ;
        $longTermProduct->product_id           = $request->service_product_id ;
        $longTermProduct->product_variant      = $request->service_product_variant_id ;
        $longTermProduct->quantity             = $request->product_quantity ;
        $longTermProduct->save();
        return $longTermProduct->id;
    }
}
