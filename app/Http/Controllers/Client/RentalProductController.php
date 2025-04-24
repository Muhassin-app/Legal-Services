<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\{ ProductVariant,ProductBooking,Variant ,ProductVariantSet};
use App\Http\Traits\ApiResponser;
use App\Http\Traits\ToasterResponser;
use Exception;

class RentalProductController extends BaseController
{
    use ApiResponser;
    use ToasterResponser;

    public function getRow(Request $request)
    {
        try {
            $celeb_ids = $related_ids = $upSell_ids = $crossSell_ids = $existOptions = $addOn_ids = array();
            $sku  = $request->sku;
            $ids  = $request->variant_ids ?? [];
            $proSku = $sku . '-' . implode('*', $ids);
            $product_id = $request->pid;
            $product_category_id = $request->category_id;
            $proVariantCount = ProductVariant::where('product_id', $product_id)->count();
            $proVariant = ProductVariant::where('sku', $proSku)->first();
            if (!$proVariant) {
                $proVariant = new ProductVariant();
                $proVariant->sku = $proSku;
                $proVariant->title = $sku . '-' .$request->vid;
                $proVariant->product_id = $product_id;
                $proVariant->barcode = $this->generateBarcodeNumber();
                $proVariant->save();
            }
            $productVariants = Variant::with('option', 'varcategory.cate.primary')
            ->select('variants.*')
            ->join('variant_categories', 'variant_categories.variant_id', 'variants.id')
            ->where('variant_categories.category_id', $product_category_id)
            ->where('variants.status', '!=', 2)
            ->orderBy('position', 'asc')->get();

            $returnHTML = view('backend.product.part.addRows')->with(['varnt' => $proVariant,'show'=>true,'product_id'=>$product_id,'productVariants'=>$productVariants,'existOptions'=>$existOptions,'product_category_id'=>$product_category_id])->render();
            return response()->json(array('success' => true, 'htmlData' => $returnHTML));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(array('success' => false, 'message'=>'Something went wrong.'));
      }
     
     }
    public function updateProductVariantSet(Request $request)
    {
        try {
            $product_id = $request->product_id;
            $variant_id = $request->variant_id;
            $p_variant_option_id = $request->p_variant_option_id;
            $p_variant_id = $request->p_variant_id;
            DB::beginTransaction();
                ProductVariantSet::updateOrCreate(
                    ['product_id' => $product_id, 'product_variant_id' => $p_variant_id,'variant_type_id' => $variant_id],
                    ['product_id' => $product_id, 'product_variant_id' => $p_variant_id,'variant_option_id' => $p_variant_option_id,'variant_type_id' => $variant_id]
                );
            DB::commit(); //Commit transaction after all the operations
            return response()->json(array('success' => true, 'message'=>'Set updated successfully.'));
      } catch (Exception $e) {
            DB::rollBack();
            return response()->json(array('success' => false, 'message'=>'Something went wrong.'));
      }
      
    }
    private function generateBarcodeNumber()
    {
        $random_string = substr(md5(microtime()), 0, 14);
        while (ProductVariant::where('barcode', $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, 14);
        }
        return $random_string;
    }

    public function getScheduleTableData(Request $request)
    {
        $booking =  ProductBooking::whereHas('user')->with('user')->where(['variant_id'=>$request->variant_id,'product_id'=>$request->product_id]);//->get();

        return Datatables::of($booking)
            ->addColumn('user_name', function($row){
                $user_name = $row->user ? ( $row->user->name ?? $row->user->email  ) : 'Block by Admin';
                return  $user_name;
            })
            ->addIndexColumn()
            
            ->rawColumns(['user_name'])
            ->make(true);
     }

     public function getScheduleTableBlockedData(Request $request)
     {
         $ProductBlockedBooking  = ProductBooking::where(['order_user_id'=>null ,'product_id'=>$request->product_id,'variant_id'=>$request->variant_id,'booking_type'=>'blocked'])->get();
         return response()->json(array('success' => true, 'data' => $ProductBlockedBooking));
      
      }
     

}
