<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackEvent extends Model
{
    use HasFactory;

    protected $fillable = ['location','details'];
}
