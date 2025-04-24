<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderDocument extends Model{
    use HasFactory;

    protected $table = 'order_documents';

    protected $fillable = ['order_vendor_product_id', 'document', 'file_name'];

}
