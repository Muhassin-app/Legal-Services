<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateAddonSet extends Model
{
	protected $fillable = ['title','min_select','max_select','position','status','is_core'];
	
    public function translation(){
	    return $this->hasMany('App\Models\EstimateAddonSetTranslation' , 'estimate_addon_id', 'id')
	    ->join('client_languages', 'estimate_addon_set_translations.language_id', 'client_languages.language_id')
	    ->select('estimate_addon_set_translations.title', 'estimate_addon_set_translations.estimate_addon_id', 'estimate_addon_set_translations.language_id')->where('client_languages.is_active', 1); 
	}

	  public function primary(){
	    return $this->hasOne('App\Models\EstimateAddonSetTranslation' , 'estimate_addon_id', 'id')->select('title', 'estimate_addon_id', 'language_id')->join('client_languages', 'estimate_addon_set_translations.language_id', 'client_languages.language_id')->where('client_languages.is_primary', 1);
	  }

	  public function option(){
	    return $this->hasMany('App\Models\EstimateAddonOption', 'estimate_addon_id', 'id')->select('id', 'title', 'estimate_addon_id', 'position', 'price'); 
	  }

	public function translation_one()
	{
		return $this->hasOne('App\Models\EstimateAddonSetTranslation', 'estimate_addon_id', 'id');
	}
	public function translation_many()
	{
		return $this->hasMany('App\Models\EstimateAddonSetTranslation', 'estimate_addon_id', 'id');
	}
	public function checkAddon($addOn)
	{
		return self::where([
			'title' => $addOn->title
		])->first();
	}

	public function estimate_product_addons(){
        return $this->hasMany('App\Models\EstimateProductAddon', 'estimate_addon_id' );
    }

	public function estimated_product_addons(){
        return $this->hasMany('App\Models\EstimatedProductAddons', 'estimated_addon_id' );
    }
}
