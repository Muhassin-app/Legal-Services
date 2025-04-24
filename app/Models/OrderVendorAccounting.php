<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderVendorAccounting extends Model
{
    use HasFactory;
    protected $table = "order_vendor_accounting";
    protected $fillable = ['order_vendor_id','third_party_accounting_id','invoice_id'];

    public function addInvoice($data)
    {
    	return self::updateOrCreate([
    		'order_vendor_id' => $data['order_vendor_id'],
    		'third_party_accounting_id' => $data['third_party_accounting_id']
    	],[
    		'invoice_id' => $data['invoice_id']
    	]);
    }
}
