<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VendorDineinCategoryTranslation;

class VendorDineinCategory extends Model
{
    use HasFactory;
    
    public function translations(){
        return $this->hasMany('App\Models\VendorDineinCategoryTranslation', 'category_id', 'id');
    }
    public function dineinTable(){
    	return $this->hasMany('App\Models\VendorDineinTable','vendor_dinein_category_id','id');
    }

    public function deleteByVendor($vendor_id){
    	$ids = $this->where('vendor_id',$vendor_id)->pluck('id')->toArray();
    	$delete_trans = VendorDineinCategoryTranslation::where('category_id',$ids)->delete();
		return $this->where('vendor_id',$vendor_id)->delete();
	}
}
