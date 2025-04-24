<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class OrderPanelController extends Controller
{
    
    public function getVendors(Request $request)
    {

        $vendors_list = Vendor::where('status',1)->select('id','name','logo');
        if(@$request->not_vendor_ids){
            $vendors_list->whereNotIn('id', $request->not_vendor_ids);
        }
        if(@$request->vendor_id){
            $vendors_list->where('id', $request->vendor_id);
        }
        $vendors_list = $vendors_list->get();

        return response()->json([
            'status' => 200,
            'data' => json_encode($vendors_list),
            'message' => 'success']);
    }

    public function getVendorCategoryId(Request $request)
    {
        $category_ids = [];
        if(@$request->vendor_id && !is_null($request->vendor_id) ) {
            $category_ids = Product::select('category_id')->distinct()->where('vendor_id', $request->vendor_id)->get();
        }

        return response()->json([
            'status' => 200,
            'data' => json_encode($category_ids),
            'message' => 'success']);
    }

    public function getOrderCategories()
    {
        
        $categories =  Category::where('id', '>', '1')->where('is_core', 1)->orderBy('parent_id', 'asc')->orderBy('position', 'asc')->where('deleted_at', NULL)->where('status', 1)->get();
        return response()->json([
            'status' => 200,
            'data' => json_encode($categories),
            'message' => 'success']);
    
    }
    
}
