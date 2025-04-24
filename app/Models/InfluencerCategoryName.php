<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerCategoryName extends Model
{
    protected $table = 'influencer_categories_name';
    protected $fillable = [
        'name', 'is_active'
    ];
}
