<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutOption extends Model
{
    use HasFactory;

    public function getCredentials($code)
    {
    	return self::select('credentials', 'test_mode')->where('code', $code)->where('status', 1)->first();
    }

}
