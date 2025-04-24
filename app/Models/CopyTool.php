<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CopyTool extends Model
{
    use HasFactory;
    protected $fillable = [
        'copy_to', 'copy_from'
    ];
}
