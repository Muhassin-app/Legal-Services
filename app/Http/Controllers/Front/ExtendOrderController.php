<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ExtendOrderController extends Controller
{
    function getOrderProductDurationDatainModel(Request $request)
    {
        try {
            $product = Product::with('variant')->where('id', $request->vendor_product_id)->first();
            if (isset($product)) {
                if ($request->ajax()) {
                    return \Response::json(\View::make('frontend.modals.extend-product-order-rental', array('product' => $product, 'request' => $request->all()))->render());
                }
            }
            return $this->errorResponse('Invalid order', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
