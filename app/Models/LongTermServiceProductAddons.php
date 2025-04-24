<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LongTermServiceProductAddons extends Model
{
    use HasFactory;

    public static function saveAddOn($request){
        LongTermServiceProductAddons::where('long_term_service_product_id',$request->long_term_service_product_id)->delete();
        if($request->has('add_on_id')){
            foreach ($request->add_on_id as $key => $add_on_id) {
                if($add_on_id && $request->add_on_set[$key]){
                    $ServiceAddon                                =   LongTermServiceProductAddons::where(['long_term_service_product_id'=>$request->long_term_service_product_id,'addon_id'=>$add_on_id])->first() ??  new LongTermServiceProductAddons();
                    $ServiceAddon->long_term_service_product_id  = $request->long_term_service_product_id;
                    $ServiceAddon->addon_id                      = $add_on_id;
                    $ServiceAddon->option_id                     = $request->add_on_set[$key];
                    $ServiceAddon->save();
                }
            }
        }
        return 1;
    }
}
