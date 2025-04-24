<?php

namespace App\Http\Controllers\Api\v1;
use DB;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{ProductVariantSet,ProductBooking,ProductVariant};
class RentalProductController extends BaseController
{
    use ApiResponser;
    public function checkProductAvailibility(Request $request)
    {
      try {
          $block_time = explode('-', $request->blocktime);
          $start_time = date("Y-m-d H:i:s",strtotime($request->selectedStartDate));
          $end_time = date("Y-m-d H:i:s",strtotime($request->selectedEndDate));
          $product_variant_data = array();
          $product_variant_id =  ProductVariantSet::where(['variant_option_id'=>$request->variant_option_id,'product_id'=>$request->product_id])->pluck('product_variant_id');
          $product_variant_id = $product_variant_id->toArray();
          $ProductBooking  = ProductBooking::whereIn('variant_id',$product_variant_id)->where('product_id',$request->product_id)
                              ->where(function ($query) use ($start_time , $end_time ){
                                  $query->where('start_date_time', '<=', $end_time)
                                        ->where('end_date_time', '>=', $start_time);
                              })->pluck('variant_id')->toArray();
                             
          $available_product_variant = array_values(array_diff($product_variant_id, $ProductBooking));
       
          if(isset($available_product_variant[0])){
            $product_variant_data =  ProductVariant::where('id',$available_product_variant[0])->with(['product','checkIfInCart'])->first();
          }
          $returnarr =  array();
          $returnarr['available_product_variant'] =  @$available_product_variant[0];
          $returnarr['product_variant_data'] = $product_variant_data;
          $returnarr['product_id'] =  $request->product_id;
          $returnarr['start_time'] =  $start_time;
          $returnarr['end_time'] =  $end_time;
          $returnarr['variant_option_id'] = $request->variant_option_id;
          if(empty($returnarr['product_variant_data'])){
            return $this->errorResponse('Product Not Found', 404);
          }
          return response()->json(array('success' => true, 'variant_data'=>$returnarr ,'message'=>'Available product data.'));
        } catch (Exception $e) {
          return response()->json(array('error' => false, 'message'=>'Something went wrong.'));
        }
     
    }
}
