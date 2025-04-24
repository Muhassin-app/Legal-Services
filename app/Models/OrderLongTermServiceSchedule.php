<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLongTermServiceSchedule extends Model
{
    use HasFactory;
    protected $fillable = ['order_long_term_services_id', 'schedule_date','type','order_vendor_product_id'];

    public function OrderService(){
        return $this->hasOne('App\Models\OrderLongTermServices', 'id', 'order_long_term_services_id');
    }
    public function DispatchStatus(){
        return $this->hasMany('App\Models\VendorOrderProductDispatcherStatus', 'long_term_schedule_id', 'id')->orderBy('id','DESC');
    }
}
