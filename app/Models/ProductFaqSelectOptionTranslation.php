<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFaqSelectOptionTranslation extends Model
{
    use HasFactory;
    protected $fillable = ['name','language_id','product_faq_select_option_id'];

}
