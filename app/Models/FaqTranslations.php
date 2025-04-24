<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqTranslations extends Model
{
    use HasFactory;
    protected $fillable = ['page_id','language_id','question','answer'];

}
