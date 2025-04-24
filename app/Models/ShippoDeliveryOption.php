<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippoDeliveryOption extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_id','address_id','user_id','zipcode_from','zipcode_to','json'];

}
