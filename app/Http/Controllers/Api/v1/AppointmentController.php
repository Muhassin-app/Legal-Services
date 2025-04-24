<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use Config;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,DispatcherSlot};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{User, Client, StaticDropoffLocation, UserAddress};

class AppointmentController extends BaseController{
	use ApiResponser,DispatcherSlot;

    // get slot fron dispatcher
    public function getSlotFromDispatchDemand(Request $request)
    {
       
        $product = $this->productDetail($request->product_id);
        $dispatchAgents = [];
        $cateTypeId = $product ? ($product->productcategory ? $product->productcategory->type_id : '') : '';
        $is_slot_from_dispatch =  ($product ? $product->is_slot_from_dispatch  : '') ?? '';
        $show_dispatcher_agent = ($product ? $product->is_show_dispatcher_agent  : '') ?? ' ';
        $last_mile_check       = $product ? $product->Requires_last_mile  : '';
        $vendorStartDate       = $vendorStartTime  = '';
        $html = "";
        if(($cateTypeId ==  12) && ($is_slot_from_dispatch == 1) && ( $last_mile_check ==1) ){ 
            
            $Dispatch =  $this->getDispatchAppointmentDomain();
            
            
            if($Dispatch){
                
                $vendor_latitude =  $product->vendor ? $product->vendor->latitude : 30.71728880;
                $vendor_longitude =  $product->vendor ? $product->vendor->longitude : 76.80350870;
                $location[] = array(
                    'latitude' =>   $vendor_longitude,
                    'longitude' =>  $vendor_longitude
                );
                $dispatchData=[
                    'service_key'      => $Dispatch->appointment_service_key,
                    'service_key_code' => $Dispatch->appointment_service_key_code,
                    'service_key_url'  => $Dispatch->appointment_service_key_url,
                    'service_type'     => 'appointment',
                    'tags'             => $product->tags,
                    'latitude'         => $vendor_latitude,
                    'longitude'        => $vendor_longitude,
                    'service_time'     => $product->minimum_duration_min,
                    'schedule_date'    => $request->cur_date,
                    'slot_start_time'  => $vendorStartTime
                ];
              
                $dispatchAgents = $this->getSlotFeeDispatcher($dispatchData);
               
            }
        
        }
        return response()->json(['status' => 'Success',
        'dispatchAgents' => $dispatchAgents,
        ], 200);
    }

}
