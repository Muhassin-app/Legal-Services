<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSectionTranslation extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_section_id','title','description','language_id'];
}
