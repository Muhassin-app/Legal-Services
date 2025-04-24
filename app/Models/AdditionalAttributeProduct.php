<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalAttributeProduct extends Model
{
    use HasFactory;

    protected $table = 'additional_attribute_products';

    public function additionalAttribute()
    {
        return $this->hasOne('App\Models\AdditionalAttribute', 'id', 'additional_attribute_id');
    }
}

