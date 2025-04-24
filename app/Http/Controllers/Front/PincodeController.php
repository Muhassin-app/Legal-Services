<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pincode;

class PincodeController extends Controller
{
    public function checkVendorPincode(Request $request){

        if($request->ajax()){
            $vendor_id = $request->vendor_id;
            $pincode = $request->pincode;
            $checkVendorPincode = Pincode::with('deliveryOptions')->whereHas('deliveryOptions',function ($q) {
                $q->where('delivery_option_type', '=', 3);
            })->where(['pincode' => $pincode, 'vendor_id' => $vendor_id])->first();
            if(!empty($checkVendorPincode)){
                return response()->json([
                    "success" => true
                ]);
            }else{
                return response()->json([
                    "success" => false
                ]);
            }
        }
    }

    public function getShippingMethod(Request $request){
        
        if ($request->ajax()) {
            return view('frontend.shipping-method-option-ajax');
        }
    }
}
