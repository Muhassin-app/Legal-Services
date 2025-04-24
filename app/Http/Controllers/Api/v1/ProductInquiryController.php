<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\cartManager;
use App\Models\ProductInquiry;
use Illuminate\Http\Request;

class ProductInquiryController extends Controller
{
    use ApiResponser;

    public function store(Request $request, $domain = '')
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'phone_number' => 'required',
                'message' => 'required',
            ], [
                'name.required' => __('The name field is required.'),
                'email.required' => __('The email field is required.'),
                'phone_number.required' => __('The phone number field is required.'),
                'message.required' => __('The message field is required.'),
            ]);
        $productEnquiry = ProductInquiry::create(['name' => $request->name, 'email' => $request->email, 'phone_number' => $request->phone_number, 'company_name' => $request->company_name, 'message' => $request->message, 'product_id' => $request->product_id, 'vendor_id' => $request->vendor_id, 'product_variant_id' => $request->variant_id]);
            return $this->successResponse($productEnquiry);
        } catch (Exception $e) {
            return $this->errorResponse( __('Something Went Wrong !'),500);

        }
    }
}
