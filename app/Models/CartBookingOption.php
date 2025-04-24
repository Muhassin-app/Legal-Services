<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartBookingOption extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'booking_option_id', 'cart_id', 'created_at', 'updated_at'];

    public function bookingOption()
    {
        return $this->hasOne(BookingOption::class, 'id', 'booking_option_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class, 'id', 'cart_id');
    }
}
