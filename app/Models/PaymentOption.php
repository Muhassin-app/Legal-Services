<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOption extends Model
{

    protected $fillable = [
        'code',
        'path',
        'title',
        'credentials',
        'status'
    ];

    use HasFactory;

    protected $appends = [
        'title_lng'
    ];

    public function getTitleLngAttribute()
    {
        return __($this->title);
    }

    public function getCredentials($code)
    {
        return self::select('credentials', 'test_mode')->where('code', $code)
            ->where('status', 1)
            ->first();
    }

    public function getPath($file)
    {
        return  \Storage::path($file);
    }


}
