<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductDispatchReturnRoute extends Model
{
    use HasFactory;

    //relation with order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    //order_product
    public function orderProduct()
    {
        return $this->belongsTo(OrderProduct::class, 'order_vendor_product_id' );
    }
    
}
