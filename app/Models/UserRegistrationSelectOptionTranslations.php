<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegistrationSelectOptionTranslations extends Model
{
    use HasFactory;
    protected $fillable = ['name','language_id','user_registration_select_option_id'];
}
