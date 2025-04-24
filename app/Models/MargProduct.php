<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MargProduct extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'rid', 'catcode', 'code', 'name', 'stock', 'remark', 'company', 'shopcode', 'MRP', 'Rate', 'Deal', 'Free', 'PRate', 'Is_Deleted', 'curbatch', 'exp', 'gcode', 'MargCode', 'Conversion', 'Salt', 'ENCODE', 'remarks', 'Gcode6', 'ProductCode', 'created_at', 'updated_at'];
}
