<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabBookingLayoutBanner extends Model
{
    protected $fillable = ['cab_booking_layout_id', 'banner_image_url', 'banner_url', 'type'];
    use HasFactory;
}
