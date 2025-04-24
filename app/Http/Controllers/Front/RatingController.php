<?php

namespace App\Http\Controllers\Front;

use DB;
use Config;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Requests\Web\OrderProductRatingRequest;
use App\Http\Requests\Web\OrderDriverRatingRequest;
use App\Http\Requests\Web\CheckImageRequest;
use App\Models\{Order,OrderProductRating,VendorOrderStatus,OrderProduct,OrderProductRatingFile,OrderDriverRating,OrderVendor,ClientPreference};
use App\Http\Traits\{ApiResponser,Dispatcher,OrderTrait};

use GuzzleHttp\Client as GCLIENT;
use Illuminate\Support\Facades\Http;
class RatingController extends FrontController{

    use ApiResponser, OrderTrait,Dispatcher;
    /**
     * update order product rating

     */
    public function updateProductRating(OrderProductRatingRequest $request){
       try {
            $user = Auth::user();
            $order_deliver = 0;
            $order_details = OrderProduct::where('id',$request->order_vendor_product_id)->whereHas('order',function($q){$q->where('user_id',Auth::id());})->first();
            if($order_details)
            $order_deliver = VendorOrderStatus::where(['order_id' => $order_details->order_id,'vendor_id' => $order_details->vendor_id,'order_status_option_id' => 6])->count();

            if($order_deliver > 0){
                $ratings = OrderProductRating::updateOrCreate(['order_vendor_product_id' => $request->order_vendor_product_id,
                'order_id' => $order_details->order_id,
                'product_id' => $order_details->product_id,
                'user_id' => Auth::id()],['rating' => $request->rating,'review' => $request->review??$request->hidden_review]);

                   if(isset($request->add_files) && is_array($request->add_files))    # send  array of insert images
                    {
                        foreach ($request->add_files as $storage) {
                            OrderProductRatingFile::updateOrCreate(['order_product_rating_id' => $ratings->id,
                            'file' => $storage]);
                        }
                    }

                $this->updateaverageRating($order_details->product_id);

                // update vendor rating 
                $this->updateVendorRating($order_details->vendor_id);

              if(isset($request->remove_files) && is_array($request->remove_files))    #send index array of deleted images
                $removefiles = OrderProductRatingFile::where('order_product_rating_id',$ratings->id)->whereIn('id',$request->remove_files)->delete();

            }

            if(isset($request->rating_for_dispatch) && !empty($request->rating_for_dispatch))
            {
                $staus = $this->setRatingOnDispatch($request);

            }

            if(isset($ratings)) {
                return $this->successResponse($ratings,'Rating Submitted.');
            }
            return $this->errorResponse('Invalid order', 200);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function updateDriverRating(Request $request){ //OrderDriverRatingRequest
      
        try {
            // $this->validate($request, [
            //     'rating' => 'required_without_all:rating_types,rating_types_Coming',
             
            //   ]);
            if($request->has('dispatch_traking_url') && !empty($request->dispatch_traking_url)){
              
                $attribute = [];
                $rating_type = [];
                $rating['rating'] = $request->rating;
                $rating['review'] = $request->review;
                if(isset($request->question_id ) && count($request->question_id )){
                    foreach($request->question_id as $key => $value){
                        $option_name = 'option_id_'.$value;
                        $attribute[$key]['question_id']=$value;
                        $attribute[$key]['option_id']=$request->$option_name;
                    }
                }
                $max = $n = 0;
                if(isset($request->rating_type_id ) &&  count($request->rating_type_id )){

                foreach($request->rating_type_id as $key => $value){
                    $rating_name = $value.'_rating';
                    $rating_type[$key]['rating_type_id']= $value;
                    $rating_type[$key]['rating']       = $request->$rating_name;
                    $max = $max+$request->$rating_name;
                    $n++;
                }
            }
                $Average_rating =0;
                if($n != 0 && $max != 0 ){
                    $Average_rating = $max / $n;
                }
                if(@$request->question_id){
                    $rating['rating'] =  $Average_rating;
                }
                $postdata['attribute']  = $attribute;
                $postdata['Rating_types']  =$rating_type;
                $postdata['Rating']  = $rating;
               
                $this->setDriverRatingDispatcher($postdata , $request->dispatch_traking_url);
                $order_details = OrderProduct::where('id',$request->order_vendor_product_id)->whereHas('order',function($q){$q->where('user_id',Auth::id());})->first();
                if($order_details){
                    $Average_rating = $Average_rating > 0 ? $Average_rating : ($request->rating ?? 0);
                    $ratings = OrderDriverRating::updateOrCreate([
                        'order_id' => $order_details->order_id,                 
                        'user_id' => Auth::id()],['rating' => $request->rating,'review' => $request->review??$request->hidden_review]);
                      
                }
             
                return $this->successResponse([],'Rating Submitted.');
            }
            // if dispatch_traking_url not commig then dispatch_traking_url
             $user = Auth::user();
             $order_deliver = 0;
             $order_details = OrderProduct::where('id',$request->order_vendor_product_id)->whereHas('order',function($q){$q->where('user_id',Auth::id());})->first();
             if($order_details)
             $order_deliver = VendorOrderStatus::where(['order_id' => $order_details->order_id,'vendor_id' => $order_details->vendor_id,'order_status_option_id' => 6])->count();

             if($order_deliver > 0){
                 $checkdriverdetail = OrderVendor::where('order_id',$order_details->order_id)->first();
                 if(isset($checkdriverdetail->dispatch_traking_url) && $checkdriverdetail->dispatch_traking_url!=NULL)
                 {
                    // dd($checkdriverdetail->dispatch_traking_url);
                    $dispacther = Http::get($checkdriverdetail->dispatch_traking_url);
                    $ratings = OrderDriverRating::updateOrCreate([
                        'order_id' => $order_details->order_id,                 
                        'user_id' => Auth::id()],['rating' => $request->rating,'review' => $request->review??$request->hidden_review]);
                    $split_trcking_url = explode('/',$checkdriverdetail->dispatch_traking_url);
                    $driverclientcode = $split_trcking_url[count($split_trcking_url)-2];
                    $unique_order_code = $split_trcking_url[count($split_trcking_url)-1];
                    $request->client_id = $driverclientcode;
                    $request->order_unique_id = $unique_order_code;
                    $staus = $this->setDriverRatingOnDispatch($request); 
                 }                 
             }          
 
             if(isset($ratings)) {
                 return $this->successResponse($ratings,'Rating Submitted.');
             }
             return $this->errorResponse('Invalid order', 200);
 
         } catch (Exception $e) {
             return $this->errorResponse($e->getMessage(), 400);
         }
     }


    # set rating at dispatch panel
    public function setRatingOnDispatch($request)
    {
        try {
            $dispatch_domain = $this->checkIfPickupDeliveryOnCommon();
            if ($dispatch_domain && $dispatch_domain != false) {
                $all_location = array();
                $postdata =  [ 'order_id' => $request->rating_for_dispatch??'',
                                'rating' => $request->rating??'',
                                'review' => $request->review??''];
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key,'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,'content-type' => 'application/json']]);
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $res = $client->post($url.'/api/update-order-feedback',
                    ['form_params' => ($postdata)]
                );

                $response = json_decode($res->getBody(), true);
                if($response && $response['message'] == 'success'){

                }
            }
        }catch(\Exception $e){
              return $e->getMessage();
        }
    }

    

    # set Driver rating at dispatch panel
    public function setDriverRatingOnDispatch($request)
    {
        try {
            $dispatch_domain = ClientPreference::select('id', 'delivery_check','delivery_service_key_url','delivery_service_key_code','need_dispacher_ride', 'pickup_delivery_service_key', 'pickup_delivery_service_key_code', 'pickup_delivery_service_key_url')->first();
            //$dispatch_domain = $this->checkIfPickupDeliveryOnCommon();
            if ($dispatch_domain && $dispatch_domain != false) {
                $all_location = array();
                $postdata =  [ 'order_id' => $request->rating_for_dispatch??'',
                                'client_id' =>$request->client_id,
                                'order_unique_id' => $request->order_unique_id,
                                'rating' => $request->rating??'',
                                'review' => $request->review??''];
                                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->delivery_service_key_url,'shortcode' => $dispatch_domain->delivery_service_key_code,'content-type' => 'application/json']]);
                $url = $dispatch_domain->delivery_service_key_url;
                // $res = $client->post($url.'/api/update-driver-rating',                
                //     ['form_params' => ($postdata)]
                // ); 
                //$url = "http://127.0.0.1:8002";
                $res = $client->get($url.'/order/driver-rating/'.$request->client_id.'/'.$request->order_unique_id.'?review='.$request->review.'&rating='.$request->rating);

                $response = json_decode($res->getBody(), true);
                if($response && $response['message'] == 'success'){

                }
            }
        }catch(\Exception $e){
              return $e->getMessage();
        }
    }

    /**
     * ratings details
    */
    public function getProductRating(Request $request){
        try {
            $rating_details = OrderProductRating::where('id',$request->id)->with('reviewFiles')->first();
            if(isset($rating_details)){

                if ($request->ajax()) {
                 return \Response::json(\View::make('frontend.modals.update-review-rating', array('rating'=>  $rating_details->rating,'order_vendor_product_id' => $request->order_vendor_product_id ,'rating_details' => $rating_details))->render());
                }

                return $this->successResponse($rating_details,'Rating Details.');
            }
            return \Response::json(\View::make('frontend.modals.update-review-rating', array('rating'=> 0 ,'order_vendor_product_id' => $request->order_vendor_product_id ,'rating_details' => $rating_details))->render());

            return $this->errorResponse('Invalid rating', 404);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * driver ratings details
    */
    public function getDriverRating(Request $request){
        try {
            // dd($request->all());
            $dispatch_rating_ques = [];
            $dispatch_rating_types = [];
            $rating_details = OrderDriverRating::where('id',$request->id)->first();
            // dd($rating_details);
            if($request->has('dispatch_traking_url') && !empty($request->dispatch_traking_url)){
               // $traking_url = 'http://192.168.102.65:8001/order/tracking/745e3f/YUCmbs';
                $rating_response = $this->getRatingQuestingDispatcher($request->dispatch_traking_url); //$request->dispatch_traking_url / change dynamic url
                $dispatch_rating_ques =  @$rating_response['attribute'] ?? [];
                $dispatch_rating_types =  @$rating_response['ratingType'] ?? [];
            }
            //pr($dispatch_rating_types);
            $withArray = array(
                            'rating'=> 0 ,
                            'order_vendor_product_id' => $request->order_vendor_product_id ,
                            'rating_details'    => $rating_details,
                            'dispatch_rating_ques' => $dispatch_rating_ques,
                            'dispatch_rating_types' => $dispatch_rating_types,
                            'dispatch_traking_url' => $request->dispatch_traking_url 
                            );

            if(isset($rating_details)){
                $withArray['rating'] = $rating_details->rating;
                // dd($withArray['rating']);
                if ($request->ajax()) {
                 return \Response::json(\View::make('frontend.modals.update-driver-rating',  $withArray)->render());
                }

                return $this->successResponse($rating_details,'Rating Details.');
            }
            return \Response::json(\View::make('frontend.modals.update-driver-rating', $withArray)->render());

            return $this->errorResponse('Invalid rating', 404);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    /**
     * update order product rating

     */
    public function uploadFile(CheckImageRequest $request){
     try {
               $files_set = [];
               $folder =$request->folder ??'';
               if ($image = $request->file('images')) {
                    foreach ($image as $key => $files) {
                    $file =  substr(md5(microtime()), 0, 15).'_'.$files->getClientOriginalName();
                    $storage = Storage::disk('s3')->put($folder, $files, 'public');
                    $files_set[$key]['name'] = $storage;
                    $files_set[$key]['ids'] = uniqid();
                    $proxy_url = env('IMG_URL1');
                    $ex = checkImageExtension($storage);
                    $image_path = env('IMG_URL2').'/'.\Storage::disk('s3')->url($storage).$ex;
                    $files_set[$key]['img_path'] = $proxy_url.'300/300'.$image_path;
                    }
                }

             if(isset($files_set)) {
                return $this->successResponse($files_set,'Files Submitted.');
            }
            return $this->errorResponse('Invalid order', 200);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Rating api for driver when order is delivered to user 
     */
    public function driverAgentRating(Request $request) {
        
        try {
            if( !empty($request->order_id) ) {
                $order = Order::where('id', $request->order_id)->first();
                if( !empty($order) ) {
                    $order->driver_rating = $request->driver_rating;
                    $order->save();
                    return $this->successResponse('', 'Thanks for rating.', 200);
                }
                else {
                    return $this->errorResponse('Order not found !', 400);
                }
            }
        }
        catch(\Exception $e) {
            return $this->errorResponse('Something went wrong !', 400);
        }

    }

    
    public function getAgentRatingQues(Request $request) {
        $traking_url = 'http://192.168.102.65:8001/order/tracking/745e3f/YUCmbs';
        $rating_questing = $this->getRatingQuestingDispatcher($traking_url);
        pr($rating_questing);
    }
}
