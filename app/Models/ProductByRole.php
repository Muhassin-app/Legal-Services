<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductByRole extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'role_id', 'minimum_order_count'];
}
