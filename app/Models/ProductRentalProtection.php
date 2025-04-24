<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRentalProtection extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'rental_proctection_id', 'type_id'];

    public function rentalProtection()
    {
        return $this->hasOne(RentalProtection::class, 'id', 'rental_proctection_id');
    }
}
