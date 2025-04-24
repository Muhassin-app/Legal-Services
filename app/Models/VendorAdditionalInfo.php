<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAdditionalInfo extends Model
{
    use HasFactory;

    protected $table = "vendor_additional_info";

    protected $fillable = ['vendor_id', 'company_name', 'gst_number', 'account_name', 'bank_name', 'account_number', 'ifsc_code','compare_categories'];

    public function getCompareCategoryAttribute()
    {
        return explode(',',$this->compare_categories);
    }
}
