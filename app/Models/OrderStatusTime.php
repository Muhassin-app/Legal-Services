<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusTime extends Model
{
    use HasFactory;
    protected $fillable =['order_id', 'order_status'];
    protected $table ='order_status_time';
}
