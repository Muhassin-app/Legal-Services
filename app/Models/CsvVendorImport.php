<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CsvVendorImport extends Model
{
    use HasFactory;

    protected $appends = ['storage_url'];
    
    public function getStorageUrlAttribute($value){
        return Storage::url('csv_vendors/'.$this->name);
    }
}
