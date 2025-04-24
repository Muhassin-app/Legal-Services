<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartRentalProtection extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'rental_protection_id', 'cart_id', 'created_at', 'updated_at'];

    public function rentalProtection()
    {
        return $this->hasOne(RentalProtection::class, 'id', 'rental_protection_id');
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
