<?php

namespace App\Http\Controllers\Front;

use Auth;
use Session;
use Timezonelist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Front\FrontController;
use App\Models\{User, UserRating, OrderVendor, OrderProductDispatchRoute, ClientCurrency, SubscriptionPlansUser, SubscriptionFeaturesListUser, SubscriptionInvoicesUser, SubscriptionInvoiceFeaturesUser, Payment, PaymentOption};

class UserRatingController extends FrontController
{
    use ApiResponser;
    public function userRatingWebhook(Request $request){
        $ratingArray['rating'] = $request->rating;
        $ratingArray['review'] = $request->review;
        $ratingArray['order_type']   = $request->type;
        $type = $request->type;
        $web_hook_code  = $request->web_hook_code;
        if(  in_array($type ,['dispatch-order-status-update','dispatch-pickup-delivery'])){
          $OrderVendor =   OrderVendor::where('web_hook_code',$web_hook_code)->first();
          if(  $OrderVendor){
            $check = UserRating::where(['order_id'=> $OrderVendor->order_id,'order_vendor_id'=>$OrderVendor->id])->first();
           
            $ratingArray['user_id']  =  $OrderVendor->user_id;
            $ratingArray['order_id']  =  $OrderVendor->order_id;
            $ratingArray['order_vendor_id']  =  $OrderVendor->id;
           
          }
        }
        else if($type =='dispatch-order-product-status-update'){
            $OrderProductDispatchRoute =   OrderProductDispatchRoute::where('web_hook_code',$web_hook_code)->with(['order'=> function($q){
                $q->select('id','user_id');
            }])->first();
          
            if( $OrderProductDispatchRoute){
              $ratingArray['user_id']  =  $OrderProductDispatchRoute->order->user_id;
              $ratingArray['order_id']  =  $OrderProductDispatchRoute->order_id;
              $ratingArray['order_vendor_id']  =  $OrderProductDispatchRoute->order_vendor_id;
              $ratingArray['order_vendor_product_id']  =  $OrderProductDispatchRoute->order_vendor_product_id;
            }
        }
      
      
        if(isset(  $ratingArray['order_id']) ){
            $checkUserRating = $this->checkRating($ratingArray);
            if($checkUserRating ){
               
                UserRating::where('id',$checkUserRating->id)->update($ratingArray);
            }else{
                UserRating::create($ratingArray);
            }
            
        }
        return response()->json([
            'status'=>'success',
            'message' => __('Rating submited successfully!'),
            'data' => []
        ]);
    }
    public function checkRating($ratingArray){
      
        $checkUserRating = UserRating::where(['order_id'=> $ratingArray['order_id'],'order_vendor_id'=>$ratingArray['order_vendor_id']]);
        if(isset($ratingArray['order_vendor_product_id'])){
            $checkUserRating = $checkUserRating->where(['order_vendor_product_id'=> $ratingArray['order_vendor_product_id']]);
        }
        return  $checkUserRating->first();
         
        
    }
    


}
