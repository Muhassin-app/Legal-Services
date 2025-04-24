<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDeliveryFeeByRole extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'role_id', 'is_free_delivery'];
}
