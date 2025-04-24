<?php

namespace App\Http\Controllers\Api\v1;

use Config, DB;
use App\Http\Controllers\Controller;
use App\Jobs\SyncToDispatcher;
use App\Models\Category;
use App\Models\ClientPreference;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Traits\InventoryTrait;

class InventoryController extends Controller
{

    use InventoryTrait;

    public function getUnAssignedOrderCategory(Request $request)
    {
       try{
            if(isset($request->assigned_order_side_vendor_id) && is_array($request->assigned_order_side_vendor_id)){
                $unAssignedOrderCategory = Vendor::select('id', 'name', 'logo')->where('status', 1)->get(); //->whereNotIn('id', $request->assigned_order_side_vendor_id)

                return response()->json([
                    'status' => 200,
                    'message' => 'fetched succesfully',
                    'data' => $unAssignedOrderCategory
                ]);
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
            
       }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
                'data' => []
            ]);
       }
           
    }

    public function getSyncStoreOrderProductIds(Request $request)
    {
       try{
            if(isset($request->order_vendor_id) && isset($request->inv_store_id)){
                $syncCatIds = Product::where('store_id', $request->inv_store_id)->where('vendor_id', $request->order_vendor_id)->distinct('sync_inventory_side_product_id')->pluck('sync_inventory_side_product_id')->toArray();

                return response()->json([
                    'status' => 200,
                    'message' => 'fetched succesfully',
                    'data' => $syncCatIds
                ]);
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
            
       }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
                'data' => []
            ]);
       }
           
    }

    public function getOrderVendorById(Request $request)
    {
        try{
            if(@$request->vendor_id){
                $vendorIds = $request->vendor_id;
                $vendors = Vendor::select('id', 'name', 'logo')->whereIn('id', $vendorIds)->get();

                return response()->json([
                    'status' => 200,
                    'message' => 'fetched succesfully',
                    'data' => $vendors
                ]);
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function getOrderVendors(Request $request)
    {
        try{
           
                $vendors = Vendor::vendorOnline()->select('id', 'name')->where('status', 1)->get();;
                return response()->json([
                    'status' => 200,
                    'message' => 'fetched succesfully',
                    'data' => $vendors
                ]);
            
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function getOrderCategories(Request $request)
    {
        try{
           
                $categories = Category::where('id', '>', '1')->where('is_core', 1)->orderBy('parent_id', 'asc')->orderBy('position', 'asc')->where('deleted_at', NULL)->where('status', 1)->get();
                return response()->json([
                    'status' => 200,
                    'message' => 'fetched succesfully',
                    'data' => $categories
                ]);
            
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function getOrderVendorCategories(Request $request)
    {
        try{
            if(@$request->vendor_id){
           
                $order_categories = \DB::table('categories')
                    ->select('categories.slug', 'categories.id')
                    ->leftjoin('vendor_categories', 'categories.id', '=', 'vendor_categories.category_id')
                    ->where('categories.id', '>', '1')
                    ->where('categories.is_core', 1)->orderBy('categories.parent_id', 'asc')
                    ->orderBy('categories.position', 'asc')
                    ->where('categories.deleted_at', NULL)
                    ->where('categories.status', 1)
                    ->where('vendor_categories.status', 1)
                    ->where('vendor_categories.vendor_id',$request->vendor_id)
                    ->get();
                return response()->json([
                    'status' => 200,
                    'message' => 'fetched succesfully',
                    'data' => $order_categories
                ]);
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }
    

    public function syncVendorCategoryProducts(Request $request)
    {
        try{

            if(@$request->products && is_array($request->products)){
                DB::beginTransaction();
                $order_vendor_id = $request->order_vendor_id;
                $synced_product = [];
                foreach($request->products as $i_product){
                    //save brand and get return brand id
                    $brand_id = $this->saveBrand($i_product, $request);

                    //save tax and return tax_categories id
                    $order_product_tax_categories_id = $this->saveTax($i_product, $request);

                    $i_product['brand_id'] =  $brand_id;
                    $i_product['tax_category_id'] =  $order_product_tax_categories_id;

                    $product_details = \DB::table('products')->where('sku', $i_product['sku'])->first();
                    if( !empty($product_details) ) {
                        $order_cat = $i_product['tax_category_id'] =  $product_details->tax_category_id;
                    }

                    //save product and return product id
                    $product_id  = $this->saveProduct($i_product);
                    $this->saveProductTranslation($i_product['product_translations'], $product_id);

                    if(@$i_product['variants']){
                        $this->saveVariant($i_product['variants'], $product_id, $request);
                    }

                    if(@$i_product['product_varaint_set']){
                        $this->saveVariantSet($i_product['product_varaint_set'], $i_product, $request);
                    }

                    if(@$i_product['addon_sets']){
                        $this->productApiAddons($i_product['addon_sets'], $product_id);
                    }

                    if(@$i_product['media']){
                        $this->saveProductMedia($i_product['media'], $product_id, $order_vendor_id);
                    }

                    if(@$i_product['variantData']){
                        $this->saveProductVariant($i_product['variantData'], $product_id, $order_vendor_id);
                    }

                    if(@$i_product['variantData']){
                        $this->saveProductCategory($request['order_cat'], $product_id);
                    }
                   
                    $synced_product[] = [
                        'product_id' => $i_product['i_id'],
                        'order_product_id' => $product_id
                    ];


                }
                
                DB::commit();
                
                return response()->json([
                    'status' => 200,
                    'message' => 'sync succesfull',
                    'data' => $synced_product
                ]);
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
                
        }catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    
    public function updateRoyoProductQuantity(Request $request)
    {
        try{
            if(@$request->sku && @$request->quantity){
                if(!empty($request->sku)){
                    $productSku = $request->sku;
                    $quantity = $request->quantity;
                  
                    $check_variant = DB::table('product_variants')->where('sku', $productSku)->get();
                    if(!empty($check_variant)){
                        DB::table('product_variants')->where('sku', $productSku)->increment('quantity', $quantity);
                    }
                }
               
                return response()->json([
                    'status' => 200,
                    'message' => 'updated succesfully',
                   
                ]);
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getOrderProductBySku(Request $request)
    {
        try{
            if(@$request->sku && !empty($request->sku)){
               
                $product = DB::table('products')->where('sku', $request->sku)->first();

                return response()->json([
                    'status' => 200,
                    'message' => 'updated succesfully',
                   'data' => $product
                ]);
          
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteOrderProductBySku(Request $request)
    {
        try{
            if(@$request->sku && !empty($request->sku)){

                $order_side_product = DB::table('products')->where('sku', $request->sku)->first();
                if(!empty($order_side_product)){
                    DB::table('products')->where('sku', $request->sku)->delete();
                    
                    DB::table('product_variants')->where('product_id', $order_side_product->id)->delete(); // Delete Order Side Product Variants
                    
                    DB::table('product_categories')->where('product_id', $order_side_product->id)->delete(); // Delete Order Side Product Category
                }

                return response()->json([
                    'status' => 200,
                    'message' => 'deleted succesfully'
                ]);
          
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function needSyncWithOrder(Request $request)
    {
        try{
            if(@$request->vendor_id && !empty($request->vendor_id) && isset($request->need_sync_with_order)){
                DB::table('vendors')->where('id', $request->vendor_id)->update(['need_sync_with_order' => $request->need_sync_with_order]);
               

                return response()->json([
                    'status' => 200,
                    'message' => 'updated succesfully'
                ]);
          
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function getOrderCategoryById(Request $request)
    {
        try{
            if(@$request->category_id && !empty($request->category_id)){
                $data['category'] = DB::table('categories')->where('id', $request->category_id)->first();
                $data['category_translation'] = DB::table('category_translations')->where('category_id',$category->id )->first();

                return response()->json([
                    'status' => 200,
                    'message' => 'updated succesfully',
                    'data' => $data
                ]);
          
            }else{
                throw new \ErrorException('parameter missing', 400);
            }
                
        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

}
