<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderQrcodeLinks extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','order_number','qrcode_id','code']; 
}
