<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDineinTable extends Model
{
    use HasFactory;

    public function getImageAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');
      $values['original'] = \Storage::disk('s3')->url($img);
      $values['logo_db_value'] = $value;

      return $values;
    }

    public function translations(){
      return $this->hasMany('App\Models\VendorDineinTableTranslation');
    }
    public function category(){
      return $this->hasOne('App\Models\VendorDineinCategory', 'id', 'vendor_dinein_category_id');
    }
    public function deleteByVendor($vendor_id){
      $ids = $this->where('vendor_id',$vendor_id)->pluck('id')->toArray();
      $delete_trans = VendorDineinTableTranslation::whereIn('vendor_dinein_table_id',$ids)->delete();
      return $this->where('vendor_id',$vendor_id)->delete();
  }
}
