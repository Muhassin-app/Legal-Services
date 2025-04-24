<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaregoryKycDoc extends Model
{
    use HasFactory;

    protected $appends = ['image_file'];

    //protected $fillable = ['user_id','category_kyc_document_id','file_name','file_original_name'];

    public function getimageFileAttribute($value){
        $values = array();
        if (!empty($this->file_name)) {
          $img = $this->file_name;
          $ex = checkImageExtension($img);
          $values['proxy_url'] = \Config::get('app.IMG_URL1');
          $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img).$ex;
          $values['image_fit'] = \Config::get('app.FIT_URl');
          $values['storage_url'] = \Storage::disk('s3')->url($img);
        }
        return $values;
      }
  
      public function category_document(){
          return $this->hasOne('App\Models\CategoryKycDocuments', 'id', 'category_kyc_document_id');
      }
}
