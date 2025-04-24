<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlansUser extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "subscription_plans_user";

    const SUBSCRIPTION_USER = 1; 
    
    const SUBSCRIPTION_MEAL = 2;
    
    public $weekdays = [
        "monday" => "Monday",
        "tuesday" => "Tuesday",
        "wednesday" => "Wednesday",
        "thursday" => "Thursday",
        "friday" => "Friday",
        "saturday" => "Saturday",
        "sunday" => "Sunday"
    ];

    public function features(){
        return $this->hasMany('App\Models\SubscriptionPlanFeaturesUser', 'subscription_plan_id', 'id');
    }

    public function subFeatures(){
        return $this->belongsToMany('App\Models\SubscriptionFeaturesListUser', 'subscription_plan_features_user', 'subscription_plan_id', 'feature_id');
    }

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

    public static function subscriptionTypes(){
        return [
            self::SUBSCRIPTION_USER => 'User Subscription',
            self::SUBSCRIPTION_MEAL => 'Meal Subscription',
        ];
    }
    
    public function subscriptionTypeName($id){
        $types = self::subscriptionTypes();
        return isset($types[$id]) ? $types[$id] : null;
    }
    
    public function subscriptionCategory()
    {
        return $this->hasMany('App\Models\SubscriptionPlanUserCategory', 'subscription_id', 'id');
    }

    public function days($day = null){
        $days = $this->weekdays;
        if($day == null){
            return $days;
        }
        return isset($days[$day]) ? $days[$day] : 'Not Defined' ;
    }
}
