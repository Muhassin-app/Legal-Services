<?php

namespace App\Http\Controllers\Client\Booking;
use App\Http\Controllers\Client\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\{ProductBooking};
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\ToasterResponser;
use App\Http\Requests\{AddManualTimeRequest};
use Exception;
class ProductBookingController extends BaseController
{
    use ApiResponser;
    use ToasterResponser;
    

    /**
     * Add Block slot for rental
     *
     * @param Request $request
     * @param mixed $name
     * @return void
     */
    public function addBlockSlot(Request $request)
    {
     
      try {
        DB::beginTransaction(); //Initiate transaction
          $block_time = explode('-', $request->blocktime);
          $start_time = date("Y-m-d H:i:s",strtotime($block_time[0]));
          $end_time = date("Y-m-d H:i:s",strtotime($block_time[1]));
          
          $start_end_block_time = $request->blocktime;

          $ProductBooking  = ProductBooking::where(['variant_id'=>$request->variant_id,'product_id'=>$request->product_id])
                                            ->where(function ($query) use ($start_time , $end_time ){
                                                $query->where('start_date_time', '<=', $start_time)
                                                      ->where('end_date_time', '>=', $end_time);
                                            })->first();
          
          if (!$ProductBooking) {
            $status = ProductBooking::Create(['memo'=>$request->memo,'variant_id'=>$request->variant_id,'product_id'=>$request->product_id,'start_date_time'=>$start_time,'end_date_time'=>$end_time,'booking_start_end'=>$start_end_block_time]);
          } else {
            return response()->json(array('success' => false, 'message'=>'This slot is already booked, Please try other.'));
          }
         
        DB::commit(); //Commit transaction after all the operations
        return response()->json(array('success' => true, 'message'=>'Manual time added sucessfully.'));
      } catch (Exception $e) {
          DB::rollBack();
          return response()->json(array('success' => false, 'message'=>'Something went wrong.'));
      }
     
    }
    /**
     * Delete slot
     *
     * @param Request $request
     * @param mixed $name
     * @return void
     */
    public function deleteSlot($domen = '' , $id)
    {
      try {
          DB::beginTransaction(); //Initiate transaction
             // ProductBooking::where('id',$id)->delete();
          DB::commit(); //Commit transaction after all the operations
          return response()->json(array('success' => true, 'message'=>'Deleted sucessfully.'));
      } catch (Exception $e) {
          DB::rollBack();
          return response()->json(array('success' => false, 'message'=>'Something went wrong.'));
      }
     
    }
    /**
     *  UpdateBlockSlot
     *
     * @param Request $request
     * @param mixed $name
     * @return void
     */
    public function updateBlockSlot(Request $request)
    {
   
      try {
          DB::beginTransaction(); //Initiate transaction
          $block_time = explode('-', $request->blocktime);
          $start_time = date("Y-m-d H:i:s",strtotime($block_time[0]));
          $end_time = date("Y-m-d H:i:s",strtotime($block_time[1]));
          
          $start_end_block_time = $request->blocktime;

          $ProductBooking  = ProductBooking::where('id','!=',$request->booking_id)
                                            ->where(function ($query) use ($start_time , $end_time ){
                                                $query->where('start_date_time', '<=', $start_time)
                                                      ->where('end_date_time', '>=', $end_time);
                                            })->first();
  
          if (!$ProductBooking) {
            $status = ProductBooking::where('id',$request->booking_id)->update(['memo'=>$request->memo,'start_date_time'=>$start_time,'end_date_time'=>$end_time,'booking_start_end'=>$start_end_block_time]);
          } else {
            return response()->json(array('success' => false, 'message'=>'This slot is already booked, Please try other.'));
          }
         
        DB::commit(); //Commit transaction after all the operations
        return response()->json(array('success' => true, 'message'=>'Manual time update sucessfully.'));
      } catch (Exception $e) {
          DB::rollBack();
          return response()->json(array('success' => false, 'message'=>'Something went wrong.'));
      }
     
    }
    

}
