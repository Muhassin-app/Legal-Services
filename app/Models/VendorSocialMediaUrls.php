<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSocialMediaUrls extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_id','url','icon'];
}
