<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessorProduct extends Model
{
    use HasFactory;
    protected $fillable = ['is_processor_enable','product_id','name','date','address','longitude','latitude',];

}

