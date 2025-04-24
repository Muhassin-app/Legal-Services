<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDriverRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id','dispatcher_driver_id','user_id','rating','review',
     ];
}
