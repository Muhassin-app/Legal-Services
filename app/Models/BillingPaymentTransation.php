<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPaymentTransation extends Model
{
    use HasFactory;

    public function getReceiptAttribute($value)
    {
        $values = array();
        $img = '';
        if(!empty($value)){
          $img = $value;
        }
        if($img!=''):
          $ex = checkImageExtension($img);
          $values['proxy_url'] = \Config::get('app.IMG_URL1');
          $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
          $values['image_fit'] = \Config::get('app.FIT_URl');
          $values['original'] = $value;
          return $values;
        else:
          return '';
        endif;
    }

    public function billingsubscriptions(){
       return $this->belongsTo('App\Models\BillingSubscription', 'billing_subscription_id');
    }

    
}
