<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id'];

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }
    public function CarDetails()
    {
        return $this->hasOne('App\Models\CarDetails');
    }
    public function images()
    {
        return $this->hasOne('App\Models\CarImages')->select('id' ,'image'); ;
    }

    public function getFullAddressAttribute(){
        return (($this->address)?$this->address:$this->house_number.', '.$this->city.', '.$this->state);
    }
}
