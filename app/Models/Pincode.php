<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pincode extends Model
{

    use HasFactory;
    protected $fillable = ['id', 'pincode', 'vendor_id', 'is_disabled'];

    public function deliveryOptions(){
        return $this->hasMany('App\Models\PincodeDeliveryOption');
    }
}
