<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorMinAmount extends Model
{
    use HasFactory;
    protected $fillable=['vendor_id','role_id','order_min_amount','created_at','updated_at'];
}
