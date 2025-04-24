<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleOld extends Model
{
    protected $fillable = ['is_enable_pricing'];
    protected $table = 'roles';
}
