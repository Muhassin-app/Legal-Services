<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerUser extends Model
{
    use HasFactory;

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function tier() {
        return $this->belongsTo('App\Models\InfluencerTier', 'influencer_tier_id');
    }

    public function ReferEarnDetail() {
        return $this->hasMany('App\Models\ReferEarnDetail', 'influencer_user_id');
    }

    public function promo(){
        return $this->belongsTo('App\Models\Promocode', 'reffered_code', 'name');
    }
    public function kyc(){
        return $this->belongsTo('App\Models\InfluencerKyc', 'user_id', 'user_id');
    }
}
