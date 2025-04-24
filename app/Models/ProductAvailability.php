<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAvailability extends Model
{
    use HasFactory;

    protected $table = 'product_availability';

    protected $fillable = ['product_id', 'date_time', 'not_available', 'created_at', 'updated_at'];


    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
