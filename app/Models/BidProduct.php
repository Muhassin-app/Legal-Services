<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidProduct extends Model
{
    use HasFactory;

    protected $fillable = ['bid_id', 'product_id', 'quantity', 'price', 'total'];

    public function bids()
    {
        return $this->hasOne(Bid::class, 'id','bid_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->with('variant');
    }
}
