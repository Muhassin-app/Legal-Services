<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class CsvProductImport extends Model
{
    use HasFactory;
    protected $appends = ['storage_url'];
    /*public function getStorageUrlAttribute($value){
        return Storage::url('csv_products/'.$this->name);
    }*/

    public function getStorageUrlAttribute($value){
        return storage_path('app/public/csv_products/') .$this->name;
    }
}
