<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'slug', 'tags', 'label', 'content', 'subject','template_id'];
}
