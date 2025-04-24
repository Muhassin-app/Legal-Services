<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferEarnDetail extends Model
{
    use HasFactory;
    protected $table = 'refer_and_earn_details';

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
