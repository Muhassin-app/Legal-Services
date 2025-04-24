<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderNotificationsLogs extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','vendor_id','order_vendor_id','order_number','user_id','message','is_seen','order_id'];
}
