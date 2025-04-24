<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegistrationDocumentTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name','language_id','user_registration_document_id'
    ];

}
