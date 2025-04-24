<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class JitsiMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_name',
        'order_id',
    ];

    protected $casts = [
        'unique_id'  => \App\Casts\Uuid::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            /** @var static $model */

            $model->unique_id ??= Uuid::uuid4();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function invitation(): HasMany
    {
        return $this->hasMany(JitsiInvitation::class);
    }
}
