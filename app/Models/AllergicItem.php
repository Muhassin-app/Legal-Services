<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AllergicItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['is_selected'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_allergic_items', 'allergic_item_id','user_id');
    }

    public function getIsSelectedAttribute()
    {
        if (UserAllergicItem::where(['user_id' => Auth::id(), 'allergic_item_id' => $this->id])->exists()) {
            return 1;
        }
        return 0;
    }
}
