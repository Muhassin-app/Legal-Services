<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRoster extends Model
{
    use HasFactory;
    protected $fillable = ['campaign_id','user_id','notification_time','notofication_type','device_type','device_token','status'];

    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }

    public function campaign(){
        return $this->hasOne('App\Models\Campaign','id','campaign_id');
    }
}
