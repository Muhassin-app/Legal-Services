<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderRefund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable=['user_id','order_id','payment_id','payment_option_id','transaction_id','amount','webhook_payload','paid_to_wallet'];

    public function user()
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    
}
