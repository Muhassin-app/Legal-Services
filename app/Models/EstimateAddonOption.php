<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateAddonOption extends Model
{
    protected $fillable = ['title','estimate_addon_id','position','price'];

    public function translation(){
       return $this->hasMany('App\Models\EstimateAddonOptionTranslation', 'estimate_addon_opt_id', 'id')->join('languages', 'estimate_addon_option_translations.language_id', 'languages.id')->select('estimate_addon_option_translations.id', 'estimate_addon_option_translations.title', 'estimate_addon_option_translations.estimate_addon_opt_id', 'estimate_addon_option_translations.language_id', 'languages.sort_code', 'languages.name'); 
    }

    public function translation_one()
    {
        return $this->hasOne('App\Models\EstimateAddonOptionTranslation', 'estimate_addon_opt_id', 'id');
    }
    public function translation_many()
    {
        return $this->hasMany('App\Models\EstimateAddonOptionTranslation', 'estimate_addon_opt_id', 'id');
    }

    public function estimated_product_addon_option(){
        return $this->hasMany('App\Models\EstimatedProductAddons', 'estimated_addon_option_id' );
    }
}