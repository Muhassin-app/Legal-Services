<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CsvQrcodeImport extends Model
{
    use HasFactory;
    protected $appends = ['storage_url'];
    public function getStorageUrlAttribute($value){
        return Storage::url('csv_qrcodes/'.$this->name);
    }

}
