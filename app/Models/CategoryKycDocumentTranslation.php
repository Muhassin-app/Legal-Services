<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryKycDocumentTranslation extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug', 'name','language_id','category_kyc_document_id'
    ];
}
