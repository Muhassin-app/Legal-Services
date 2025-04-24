<?php

namespace App\Http\Controllers\Front\Booking;
use App\Http\Controllers\Front\FrontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\{ProductBooking, ProductVariant, ProductVariantSet,Product};
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\ToasterResponser;
use App\Http\Requests\{AddManualTimeRequest};
use Exception;
class ProductBookingController extends FrontController
{
    use ApiResponser;
    use ToasterResponser;
    

    /**
     * Check Prodcut availibility
     *
     * @param Request $request
     * @param mixed $name
     * @return void
     */
    public function checkProductAvailibility(Request $request)
    {
      try {
        $block_time = explode('-', $request->blocktime);
        $start_time = date("Y-m-d H:i:s",strtotime($request->selectedStartDate));
        $end_time = date("Y-m-d H:i:s",strtotime($request->selectedEndDate));
        $product_variant_data = array();
        $available_product_variant = [];
        $variant_product_quantity = 0;

        if($request->has('variant_option_id') && ($request->variant_option_id !='')){
          $product_variant_id =  ProductVariantSet::where(['variant_option_id'=>$request->variant_option_id,'product_id'=>$request->product_id])->pluck('product_variant_id');
          $product_variant_id = $product_variant_id->toArray();
        }else{
          $product_variant_id[]=$request->variant_id;
        }

        $ProductBooking = ProductBooking::whereIn('variant_id',$product_variant_id)->where('product_id',$request->product_id)
                            ->where(function ($query) use ($start_time , $end_time ){
                                $query->where('start_date_time', '<=', $end_time)
                                      ->where('end_date_time', '>=', $start_time);
                            })->pluck('variant_id')->toArray();

        $available_product_variant = array_values(array_diff($product_variant_id, $ProductBooking));

        $variant_product_detail = ProductVariant::select('product_id', 'quantity')->whereIn('id', $product_variant_id)->first();
       
        $variant_product_quantity = $variant_product_detail->quantity;
        if($variant_product_detail->quantity > count($ProductBooking)){
          $available_product_variant[] = $product_variant_id;
          $product_variant_data =  ProductVariant::where('id',$available_product_variant[0])->with(['product','checkIfInCart'])->first();
        }

        $returnarr =  array();
        $returnarr['available_product_variant'] =  @$available_product_variant[0];
        $returnarr['product_variant_data'] = $product_variant_data;
        $returnarr['product_id'] =  $request->product_id;
        $returnarr['start_time'] =  $start_time;
        $returnarr['end_time'] =  $end_time;
        $returnarr['variant_option_id'] = $request->variant_option_id;
        $returnarr['variant_product_quantity'] = $variant_product_quantity;
        return response()->json(array('success' => true, 'variant_data'=>$returnarr ,'message'=>'Available product data.'));
    } catch (Exception $e) {
          return response()->json(array('error' => false, 'message'=>'Something went wrong.'));
        }
     
    }
    

}
