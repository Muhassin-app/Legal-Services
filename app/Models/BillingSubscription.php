<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingSubscription extends Model
{
    use HasFactory;

    public function paymenttransactions(){
        return $this->hasMany('App\Models\BillingPaymentTransation', 'billing_subscription_id');
    }

    public function client(){
        return $this->belongsTo('App\Models\Client', 'client_id');
    }

    public function billingpricing(){
        return $this->belongsTo('App\Models\BillingPricing', 'billing_price_id');
    }
}
