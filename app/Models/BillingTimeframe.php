<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingTimeframe extends Model
{
    use HasFactory;

    public function billingpricings(){
        return $this->hasMany('App\Models\BillingPricing', 'billing_timeframe_id');
    }

    public function billingplans()
    {
        return $this->belongsToMany('App\Models\BillingPlan', 'billing_pricings');
    }
}
