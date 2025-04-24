<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLongTermServices extends Model
{
    use HasFactory;
    protected $fillable = ['order_product_id', 'user_id', 'service_quentity', 'service_day', 'service_date', 'service_period', 'service_end_date', 'service_product_id', 'service_product_variant_id', 'service_start_date','status'];
    
    public function schedule(){
        return $this->hasMany('App\Models\OrderLongTermServiceSchedule', 'order_long_term_services_id', 'id');
    }

    public function addon(){
        return $this->hasMany('App\Models\OrderLongTermServicesAddon', 'order_long_term_services_id', 'id');
     }

    public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'service_product_id');
    }
    public function orderProduct(){
        return $this->belongsTo('App\Models\OrderProduct', 'order_product_id', 'id');
    }
}
