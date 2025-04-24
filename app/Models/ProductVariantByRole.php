<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantByRole extends Model
{
    // save role id 1 price in product variant table and in this table also. save all other roles pricing in this table.
    protected $fillable = ['product_id', 'product_variant_id', 'role_id', 'amount','created_at','deleted_at', 'quantity'];
}
