<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPlan extends Model
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
        $values['original'] = $value;

        return $values;
    }

    public function billingpricings(){
        return $this->hasMany('App\Models\BillingPricing', 'billing_plan_id');
    }

    public function plantype(){
      return $this->belongsTo('App\Models\BillingPlanType', 'plan_type');
    }

    public function billingtimeframes()
    {
        return $this->belongsToMany('App\Models\BillingTimeframe', 'billing_pricings');
    }
}
