<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRating extends Model
{
    use HasFactory;
   
    protected $fillable = [ 'user_id', 'order_id','order_vendor_id','order_vendor_product_id','order_type','rating','review' ];
}
