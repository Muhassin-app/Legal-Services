<?php

namespace App\Http\Controllers\Front; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{ThirdPartyAccounting, OrderVendor,OrderVendorAccounting};
use Session, Auth;

class XeroController extends Controller
{
	use \App\Http\Traits\XeroManager;
    private $client_id;
    private $secret_id;
    private $accountingObj;
    public function __construct(OrderVendorAccounting $accounting)
    {
        $xero_creds = ThirdPartyAccounting::where('code','xero')->first();
        $creds_arr = json_decode($xero_creds->credentials);
        $this->client_id = $creds_arr->client_id??'';
        $this->secret_id = $creds_arr->secret_id??'';

        $this->accountingObj = $accounting;
    }


    public function index(Request $request)
    {
    	$response = $this->authorization();
    }
    public function xero_callback(Request $request)
    {
    	$this->callback();
        $order_vendors = OrderVendor::has('accounting', '<', 1)->where('order_status_option_id',6)->with('user','products','products.addon','products.addon.option','products.pvariant')->take(10)->get();
        foreach($order_vendors as $order_vendor)
        {
            $invoice = $this->createInvoice($order_vendor);
            $addInvoice = $this->accountingObj->addInvoice([
                'order_vendor_id' => $order_vendor->id,
                'third_party_accounting_id' => 1,
                'invoice_id' => $invoice['invoice_id']
            ]);

        } 
        return redirect()->route('order.index');
    }
}
