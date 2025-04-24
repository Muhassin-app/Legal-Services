<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerTier extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'target', 'commision_type', 'commision', 'status', 'created_at', 'updated_at'];
}
