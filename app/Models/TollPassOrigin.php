<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TollPassOrigin extends Model
{
    use HasFactory;
    protected $table = "toll_pass_origin";

    public function getTollPassNameAttribute()
    {
        return "{$this->toll_pass} - {$this->desc}";
    }

    public function products(){
        return $this->hasMany('App\Models\Product', 'toll_pass_id', 'id');
    }
}
