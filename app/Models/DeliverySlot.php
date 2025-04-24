<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySlot extends Model
{
    use HasFactory;
    protected $fillable =  ['title', 'start_time', 'end_time', 'price', 'status', 'slot_interval','parent_id', 'cutOff_time'];
}
