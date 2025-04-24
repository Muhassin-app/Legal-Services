<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $appends = ['image_url'];
    public function getimageUrlAttribute($value){
        $values = array();
        $image_path = '';
        $staticImage = ['visa','discover','american-express','master','mobile-money','mtn','airtel-money', 'vodafone', 'airteltigo', 'mtn-mobile', 'telecel-cash'];
        if ( in_array($this->slug, $staticImage) ) {
            $image_path = asset('assets/images/cards/'.$this->slug.'.png');
        }else{
            $img = $this->image;

            $ex = checkImageExtension($img);
            $values['proxy_url'] = \Config::get('app.IMG_URL1');
            $values['image_path'] = \Config::get('app.IMG_URL2') . '/' . \Storage::disk('s3')->url($img).$ex;
            $values['image_fit'] = \Config::get('app.FIT_URl');
            $values['storage_url'] = \Storage::disk('s3')->url($img);
        }
        return $image_path;
    }
}
