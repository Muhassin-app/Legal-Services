<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarDetails extends Model
{
    use HasFactory;

    public static function saveCarDetail($request){
         
        $newCarDetails = CarDetails::where('car_id',$request->car_id)->first() ?? new CarDetails();
        $newCarDetails->car_id =  $request->car_id;
        $newCarDetails->save();
        return $newCarDetails;
    }
}
