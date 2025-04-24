<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPricing extends Model
{
    use HasFactory;

    public function billingplan(){
        return $this->belongsTo('App\Models\BillingPlan', 'billing_plan_id')->select(['id','title','status','plan_type']);
    }

    public function billingtimeframe(){
        return $this->belongsTo('App\Models\BillingTimeframe', 'billing_timeframe_id')->select('id', 'title', 'status', 'is_custom', 'is_lifetime', 'standard_buffer_period', 'validity', 'validity_type');
    }

    public function billingsubscriptions(){
        return $this->hasMany('App\Models\BillingSubscription', 'billing_price_id');
    }
}
