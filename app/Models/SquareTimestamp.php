<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SquareTimestamp extends Model
{
    use HasFactory;

    protected $table = "square_timestamp";

    protected $fillable = ['created_at', 'updated_at'];
}
