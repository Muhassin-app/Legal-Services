<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartDeliveryFee extends Model
{
    use HasFactory;
    

    protected $table = 'cart_vendor_delivery_fee';

    protected $fillable = ['cart_id', 'vendor_id', 'delivery_fee', 'shipping_delivery_type','courier_id', 'delivery_duration', 'delivery_distance'];


    
}