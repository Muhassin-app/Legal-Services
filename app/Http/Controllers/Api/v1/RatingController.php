<?php

namespace App\Http\Controllers\Api\v1;

use DB, Log;
use Config;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Requests\OrderProductRatingRequest;
use App\Http\Requests\OrderDriverRatingRequest;
use App\Models\{Order,OrderProductRating,VendorOrderStatus,OrderProduct,OrderProductRatingFile,Client,OrderVendor,OrderDriverRating,ClientPreference};
use App\Http\Traits\{OrderTrait,Dispatcher,ApiResponser};

use GuzzleHttp\Client as GCLIENT;
use App\Http\Requests\Web\CheckImageRequest;

class RatingController extends BaseController{

    use ApiResponser, OrderTrait ,Dispatcher;
    /**
     * update order product rating

     */
    public function updateProductRating(OrderProductRatingRequest $request){
        try {
            $user = Auth::user();
            $order_deliver = 0;
            $order_details = OrderProduct::where('id',$request->order_vendor_product_id)->whereHas('order',function($q){$q->where('user_id',Auth::id());})->first();
            if($order_details)
            // $order_deliver = VendorOrderStatus::where(['order_id' => $request->order_id,'vendor_id' => $order_details->vendor_id,'order_status_option_id' => 6])->count();
            // Log::info(['$order_deliver' => $order_deliver]);
            // if($order_deliver > 0){
                $ratings = OrderProductRating::updateOrCreate(['order_vendor_product_id' => $request->order_vendor_product_id,
                'order_id' => $request->order_id,
                'product_id' => $request->product_id,
                'user_id' => Auth::id()],['rating' => $request->rating,'review' => $request->review??'']);
                if ($image = $request->file('files')) {
                    foreach ($image as $files) {
                    $file =  substr(md5(microtime()), 0, 15).'_'.$files->getClientOriginalName();
                    $code = Client::orderBy('id','asc')->value('code');
                    $storage = Storage::disk('s3')->put($code.'/review', $files, 'public');
                    $img = new OrderProductRatingFile();
                    $img->order_product_rating_id = $ratings->id;
                    $img->file = $storage;
                    $img->save();

                    }
                // }
                $this->updateaverageRating($request->product_id);

                // update vendor rating 
                $this->updateVendorRating($order_details->vendor_id);

                if(isset($request->remove_files) && is_array($request->remove_files))    # send index array of deleted images
                $removefiles = OrderProductRatingFile::where('order_product_rating_id',$ratings->id)->whereIn('id',$request->remove_files)->delete();

                if(isset($request->rating_for_dispatch) && !empty($request->rating_for_dispatch))
                {
                    $staus = $this->setRatingOnDispatch($request);
                }

            }
            if(isset($ratings)) {
                return $this->successResponse($ratings,'Rating Submitted.');
            }
            return $this->errorResponse('Invalid order', 404);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * update driver rating

     */
    public function updateDriverRating(OrderDriverRatingRequest $request){
        try {

            if($request->has('dispatch_traking_url') && !empty($request->dispatch_traking_url)){
              
                $postdata = $request->postdata;
                
               
                $this->setDriverRatingDispatcher($postdata , $request->dispatch_traking_url);
                $ratings = OrderDriverRating::updateOrCreate([
                    'order_id' => $request->order_id,
                    'user_id' => Auth::id()],['rating' => $request->Average_rating,'review' => $request->review]);
               return $this->successResponse([],'Rating Submitted.');
            }
            
            //return $request->all();
            $user = Auth::user();
            $checkdriverdetail = OrderVendor::where('order_id',$request->order_id)->first();
            if(isset($checkdriverdetail->dispatch_traking_url) && $checkdriverdetail->dispatch_traking_url!=NULL)
            {
                $ratings = OrderDriverRating::updateOrCreate([
                    'order_id' => $request->order_id,
                    'user_id' => Auth::id()],['rating' => $request->rating,'review' => $request->review]);
                
                $split_trcking_url = explode('/',$checkdriverdetail->dispatch_traking_url);
                $driverclientcode = $split_trcking_url[count($split_trcking_url)-2];
                $unique_order_code = $split_trcking_url[count($split_trcking_url)-1];
                $request->client_id = $driverclientcode;
                $request->order_unique_id = $unique_order_code;
                $staus = $this->setDriverRatingOnDispatch($request); 
                
                if(isset($ratings)) {
                    return $this->successResponse($ratings,'Rating Submitted.');
                }else{
                    return $this->errorResponse('There is some issue. Try again later', 401);
                }
            }else{
                return $this->errorResponse('Invalid order for driver rating', 401);
            }           

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * ratings details
    */
    public function getAllProductRating(Request $request){
        try {
            $ratings = OrderProductRating::with(['user', 'reviewFiles'])->where('product_id', $request->product_id)->get();
            
            $totalRatings = $ratings->count();
            $totalReviews = $ratings->where('review', '!=', null)->count();
            $averageRating = $ratings->avg('rating') ?? 0;
            $totalReviewFiles = $ratings->sum(function($rating) {
                return $rating->reviewFiles->count();
            });

            // Calculate review counts for each rating value (1-5)
            $reviewCounts = [
                1 => $ratings->where('rating', 1)->count(),
                2 => $ratings->where('rating', 2)->count(),
                3 => $ratings->where('rating', 3)->count(),
                4 => $ratings->where('rating', 4)->count(),
                5 => $ratings->where('rating', 5)->count()
            ];
            
            // Add review files count to each rating
            $ratings->transform(function ($rating) {
                $rating->review_files_count = $rating->reviewFiles->count();
                return $rating;
            });
            
            $data = [
                'ratings' => $ratings,
                'total_ratings' => $totalRatings,
                'total_reviews' => $totalReviews,
                'total_review_files' => $totalReviewFiles,
                'average_rating' => round($averageRating, 2),
                'review_counts' => $reviewCounts
            ];
            
            return $this->successResponse($data, 'Product Ratings.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getProductRating(Request $request){
        try {
            $ratings = '';
            $ratings = OrderProductRating::where('id',$request->id)->with('reviewFiles')->first();
            if(isset($ratings))
            return $this->successResponse($ratings,'Rating Details.');
            return $this->errorResponse('Invalid rating', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

     /**
     * update image

     */
    public function uploadFile(CheckImageRequest $request){
        try {
                  $code = Client::orderBy('id','asc')->value('code');
                  $files_set = [];
                  $folder = '/'.$code.$request->folder ??'';
                  if ($image = $request->file('images')) {
                       foreach ($image as $key => $files) {
                       $file =  substr(md5(microtime()), 0, 15).'_'.$files->getClientOriginalName();
                       $storage = Storage::disk('s3')->put($folder, $files, 'public');
                       $files_set[$key]['name'] = $storage;
                       $files_set[$key]['ids'] = uniqid();
                       $proxy_url = env('IMG_URL1');
                       $ex = checkImageExtension($storage);
                       $image_path = env('IMG_URL2').'/'.\Storage::disk('s3')->url($storage);
                       $files_set[$key]['img_path'] = $proxy_url.'300/300'.$image_path.$ex;
                       }
                   }

                if(isset($files_set)) {
                   return $this->successResponse($files_set,'Files Submitted.');
               }
               return $this->errorResponse('Invalid data', 200);

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
            if ($dispatch_domain && $dispatch_domain != false) {
                // $all_location = array();
                // $postdata =  [ 'order_id' => $request->rating_for_dispatch??'',
                //                 'client_id' =>$request->client_id,
                //                 'order_unique_id' => $request->order_unique_id,
                //                 'rating' => $request->rating??'',
                //                 'review' => $request->review??''];
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->delivery_service_key_url,'shortcode' => $dispatch_domain->delivery_service_key_code,'content-type' => 'application/json']]);
                $url = $dispatch_domain->delivery_service_key_url;                
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
     * Rating api for driver when order is delivered to user 
     */
    public function driverAgentRating(Request $request) {
        
        try {
            if( !empty($request->order_vendor_product_id) ) {
                $order_product = OrderProduct::where('id',$request->order_vendor_product_id)->first();
                if($order_product) {
                    $order = Order::where('id', $order_product->order_id)->first();
                    if( !empty($order) ) {
                        $order->driver_rating = $request->rating;
                        $order->save();
                        return $this->successResponse('', 'Thanks for rating.', 200);
                    }
                }
            }
            return $this->errorResponse('Order not found !', 400);
        }
        catch(\Exception $e) {
            return $this->errorResponse('Something went wrong !', 400);
        }

    }


    /**
     * driver ratings details
    */
    public function getDriverRating(Request $request){
        try {
            if($request->has('dispatch_traking_url') && !empty($request->dispatch_traking_url)){
                $rating_response = $this->getRatingQuestingDispatcher($request->dispatch_traking_url); 
                $rating_details = OrderDriverRating::where('id',$request->id)->first();
                $rating_response['rating_details'] =   $rating_details;
                return $this->successResponse($rating_response,'Rating Details.');
            }
        
            if(isset($rating_details)){
                return $this->successResponse($rating_details,'Rating Details.');
            }
            return $this->errorResponse('Invalid rating', 404);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getAgentRatingQues(Request $request) {
        $traking_url = 'http://192.168.102.65:8001/order/tracking/745e3f/YUCmbs';
        $rating_response = $this->getRatingQuestingDispatcher($traking_url);
       pr($rating_response);
    }

}
