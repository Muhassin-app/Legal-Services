<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id', 'discount', 'bid_req_id','bid_total', 'final_amount','bid_order_number'];

    public function bidRequests()
    {
        return $this->belongsTo(BidRequest::class, 'bid_req_id','id');
    }

    public function bidProducts()
    {
        return $this->hasMany(BidProduct::class, 'bid_id')->with('product');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
