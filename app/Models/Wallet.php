<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use OwenIt\Auditing\Contracts\Auditable;


class Wallet extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['holder_type','holder_id','name','slug','description','meta','balance','decimal_places'];
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
