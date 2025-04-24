<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticDropoffLocation extends Model
{
    use HasFactory;

    protected $fillable = ['title','address','street','latitude', 'longitude','place_id'];
}
