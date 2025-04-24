<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class AddonSet extends Model
{
	use SoftDeletes;

	protected $fillable = ['title','min_select','max_select','position','status','is_core','vendor_id', 'square_modifier_id'];

    public function translation(){
	    return $this->hasMany('App\Models\AddonSetTranslation' , 'addon_id', 'id')
	    ->join('client_languages', 'addon_set_translations.language_id', 'client_languages.language_id')
	    ->select('addon_set_translations.title', 'addon_set_translations.addon_id', 'addon_set_translations.language_id')->where('client_languages.is_active', 1);
	}

	  public function primary(){
	    return $this->hasOne('App\Models\AddonSetTranslation' , 'addon_id', 'id')->select('title', 'addon_id', 'addon_set_translations.language_id')->join('client_languages', 'addon_set_translations.language_id', 'client_languages.language_id')->where('client_languages.is_primary', 1);
	  }

	  public function option(){
	    return $this->hasMany('App\Models\AddonOption', 'addon_id', 'id')->select('id', 'title', 'addon_id', 'position', 'price', 'square_modifier_option_id');
	  }

	public function translation_one()
	{
		if (request()->segment(1) == 'client') {
			$sessionLang = \Session::get('adminLanguage');
		}else{
			$sessionLang = \Session::get('customerLanguage');
		}
		return $this->hasOne('App\Models\AddonSetTranslation', 'addon_id', 'id')->where('language_id',$sessionLang ?? 1);
	}
	public function translation_many()
	{
		return $this->hasMany('App\Models\AddonSetTranslation', 'addon_id', 'id');
	}
	public function checkAddon($addOn,$vendor_id)
	{
		return self::where([
			'vendor_id' => $vendor_id,
			'title' => $addOn->title
		])->where('status', 1)->first();
	}
}
