<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimatedProductAddons extends Model
{
    protected $table = 'estimated_product_addon_new';
    use HasFactory;

    public function estimated_products(){
        return $this->belongsTo('App\Models\EstimatedProduct', 'estimated_product_id' );
    }

    public function estimated_product_addon(){
        return $this->belongsTo('App\Models\EstimateAddonSet', 'estimated_addon_id' );
    }

    public function estimated_product_addon_option(){
        return $this->belongsTo('App\Models\EstimateAddonOption', 'estimated_addon_option_id' );
    }
}
