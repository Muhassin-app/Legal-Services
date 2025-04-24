<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductVariantSet extends Model
{
    protected $fillable = ['id', 'product_id','product_variant_id','variant_type_id','variant_option_id'];

	public function variantDetail() {
	    return $this->belongsTo('App\Models\Variant', 'variant_type_id', 'id')->orderBy('position');
	}
	
	public function options() {
	    return $this->hasMany('App\Models\VariantOption', 'variant_id', 'variant_type_id')
	    		->join('product_variant_sets as pvs', 'pvs.variant_option_id', 'variant_options.id')
				->join('product_variants as pv','pvs.product_variant_id','pv.id')
				->where('pv.status',1)
	    		->groupBy('pvs.variant_option_id')->orderBy('pvs.product_variant_id');
	}

	public function options1() {
	    return $this->hasOne('App\Models\ProductVariantSet', 'product_variant_id', 'product_variant_id')
    		->join('variant_options as pvs', 'product_variant_sets.variant_option_id', 'pvs.id')
    		->join('variant_option_translations as vt','vt.variant_option_id','pvs.id')
    		->join('product_variants as pv','product_variant_sets.product_variant_id','pv.id')
    		->select('pvs.hexacode', 'vt.title', 'product_variant_sets.product_id', 'product_variant_sets.variant_type_id', 'product_variant_sets.variant_option_id', 'product_variant_sets.product_variant_id','pv.quantity','pv.price','pv.status')
    		->where('pv.status',1)
			->groupBy('product_variant_sets.variant_option_id');
	}

	public function option2() {
	    return $this->hasMany('App\Models\ProductVariantSet', 'variant_type_id', 'variant_type_id')
    		->join('variant_options as pvs', 'product_variant_sets.variant_option_id', 'pvs.id')
    		->join('variant_option_translations as vt','vt.variant_option_id','pvs.id')
    		->join('product_variants as pv','product_variant_sets.product_variant_id','pv.id')
    		->select('pvs.hexacode', 'vt.title', 'product_variant_sets.id', 'product_variant_sets.product_id', 'product_variant_sets.variant_type_id', 'product_variant_sets.variant_option_id', 'product_variant_sets.product_variant_id','pv.quantity','pv.price','pv.status')
    		->where('pv.status',1)
			->groupBy('product_variant_sets.variant_option_id');
	}

	public function optionData() {
	    return $this->belongsTo('App\Models\VariantOption', 'variant_option_id', 'id');
	}

	public function productVariants(){
		return $this->hasMany('App\Models\ProductVariant', 'id', 'product_variant_id');
	}
}
