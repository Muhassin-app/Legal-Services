<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSlotServiceArea extends Model
{
    use HasFactory;

    public function serviceArea(){
        return $this->hasOne('App\Models\ServiceArea', 'id', 'service_area_id')->select('id', 'vendor_id', 'geo_array', 'name');
    }

}
