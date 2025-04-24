<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LongTermServicePeriod extends Model
{
    use HasFactory;

    public static function saveServicePeriod($request){
       
        LongTermServicePeriod::where('product_id',$request->long_term_service_id)->delete();
        if($request->has('service_period')){
            foreach ($request->service_period as $key => $service_period) {
                    $period                                =  new LongTermServicePeriod();
                    $period->product_id                    = $request->long_term_service_id;
                    $period->service_period                = $service_period;
                    $period->save();
            }
        }
        return 1;
    }
}
