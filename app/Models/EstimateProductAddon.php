<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateProductAddon extends Model
{
    use HasFactory;

    public function product_sets(){
        return $this->belongsTo('App\Models\EstimateProduct', 'estimate_product_id' );
    }

    public function estimate_addon_set(){
        return $this->belongsTo('App\Models\EstimateAddonSet', 'estimate_addon_id' );
    }

    public function estimate_product_addon_option(){
        return $this->belongsTo('App\Models\EstimateAddonOption', 'estimate_addon_id' );
    }

    public function setoptions(){
        return $this->hasMany('App\Models\EstimateAddonOption', 'estimate_addon_id', 'id')->select('id', 'estimate_addon_id', 'title', 'price', 'position')->orderBy('id', 'asc'); 
     }
}
