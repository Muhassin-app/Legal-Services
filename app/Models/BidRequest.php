<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidRequest extends Model
{
    use HasFactory;
    //protected $appends = array('image');
    protected $fillable = ['user_id', 'prescription','description','status'];

    public function user(){
        return $this->belongsTo(User::class, 'id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class, 'bid_req_id');
    }

    public function bid()
    {
        return $this->hasOne(Bid::class, 'bid_req_id','id');
    }

    public function bidCounts()
    {
        return $this->hasMany(Bid::class, 'bid_req_id')->where('status',0);
    }

    // public function getImageAttribute()
    // {
    //   $values = array();
    //   $img = 'default/default_image.png';
    //   if(!empty($this->prescription)){
    //     $img = $this->prescription;
    //   }
    //   $ex = checkImageExtension($img);
    //   $values['proxy_url'] = \Config::get('app.IMG_URL1');
    //   $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
    //   $values['image_fit'] = \Config::get('app.FIT_URl');
    //   $values['original'] = $this->prescription;

    //   return $values;
    // }
}
