<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBookingOption extends Model
{
    use HasFactory;

    protected $fillable = ['product_id','booking_option_id'];

    public function bookingOption(){
        return $this->hasOne(BookingOption::class, 'id', 'booking_option_id');
    }
}
