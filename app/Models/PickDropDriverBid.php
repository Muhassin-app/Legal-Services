<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickDropDriverBid extends Model
{
    use HasFactory;
    protected $fillable = ['order_bid_id', 'status', 'tasks', 'driver_id', 'driver_name', 'driver_image', 'bid_price', 'bid_price', 'task_type', 'expired_at'];
}
