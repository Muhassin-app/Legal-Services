<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanUserCategory extends Model
{
    use HasFactory;

    protected $table = 'subscription_plan_user_category';

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product', 'category_id', 'category_id');
    }

    public function subscription()
    {
        return $this->hasOne('App\Models\SubscriptionPlansUser', 'id', 'subscription_id');
    }
}
