<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPlanType extends Model
{
    use HasFactory;

    public function billingplans(){
        return $this->hasMany('App\Models\BillingPlan', 'billing_plan_id');
    }
}
