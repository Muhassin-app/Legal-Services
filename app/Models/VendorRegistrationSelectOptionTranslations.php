<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRegistrationSelectOptionTranslations extends Model
{
    use HasFactory;
    protected $fillable = ['name','language_id','vendor_registration_select_option_id'];
}
