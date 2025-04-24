<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorOrderProductStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id','order_vendor_id', 'order_status_option_id', 'vendor_id', 'product_id', 'order_vendor_product_id','dispatcher_status_option_id'
    ];

    protected $appends = ['status']; //,'dispatch_status'
    public function OrderStatusOption(){
        return $this->hasOne('App\Models\OrderStatusOption', 'id', 'order_status_option_id'); 
    }

    public function getStatusAttribute(){
        return OrderStatusOption::where('id',$this->order_status_option_id)->first();
    }
    // public function getdispatchStatusAttribute(){
    //     return OrderStatusOption::where('id',$this->dispatcher_status_option_id)->first();
    // }
}
