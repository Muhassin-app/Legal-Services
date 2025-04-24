<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Traits\D4BDunzo;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\OrderVendorProduct;
use App\Models\ShippingOption;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Vendor;
use App\Models\VendorOrderDispatcherStatus;
use App\Models\VendorOrderStatus;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
class D4BDunzoController extends Controller
{
    use D4BDunzo;

    public function quote($vendor_id)
    {

        try{
            $customer = User::find(Auth::id());
            $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
            if ($cus_address && $this->status==1){
                $vendor_details = Vendor::find($vendor_id);

            $locationData = [
                'pickup_details' => [
                    [
                        'lat' => floatval($vendor_details->latitude),
                        'lng' => floatval($vendor_details->longitude),
                        'reference_id' => 'pickup-ref-abcd123'.strtotime(now()),
                    ],
                ],
                'optimised_route' => true,
                'drop_details' => [
                    [
                        'lat' =>  floatval($cus_address->latitude),
                        'lng' => floatval($cus_address->longitude),
                        'reference_id' => 'drop-ref1-abcd887'.strtotime(now()),
                        // 'payment_data' => [
                        //     'payment_method' => 'COD',
                        //     'amount' => 101,
                        // ],
                    ]

                        ],
                // 'delivery_type' => 'SCHEDULED',
                // 'schedule_time' => Carbon::now()->timestamp,
                    ];

                \Log::info($locationData);


                $response = Http::withHeaders([
                    'client-id' => $this->client_id,
                    'Authorization' => $this->token,
                    'Accept-Language' => 'en_US',
                    'Content-Type' => 'application/json',
                ])->post($this->app_url.'/v2/quote', $locationData);
                \Log::info($response->json());

                if($response->successful()){
                    return $response->json();
                }else{
                    $response = 2;
                }
            }
        }catch(\Exception $e){
            return 0;
        }
    }

    public function createOrderRequestD4BDunzo($user_id,$orderVendor)
    {
        $order = Order::find($orderVendor->order_id);
        $customer = User::find($user_id);
        $vendor_details = Vendor::find($orderVendor->vendor_id);

        $scheduledAt = null;
        if(isset($order->scheduled_date_time) && $order->scheduled_date_time){
            $schTime = convertDateTimeInClientTimeZone($order->scheduled_date_time);
            $date = date('Y-m-d',strtotime($schTime));
            $time = date('H:i:s',strtotime($schTime));
            $scheduledAt = $date.'T'.$time;
            $schTime  = Carbon::parse($scheduledAt)->timestamp;
            $nowtime = Carbon::now()->addMinutes(35)->timestamp;

            if($schTime>$nowtime)
            {
                $scheduledAt  = $schTime;
            }
        }

        $cus_address = UserAddress::find($order->address_id);
        $orderProducts = OrderVendorProduct::where(['order_id'=>$orderVendor->order_id,'order_vendor_id'=>$orderVendor->id])->get();
        // create order
        return $this->createOrder($orderVendor,$vendor_details,$cus_address,$customer,$order,$scheduledAt);
    }

    public function d4bdunzoWebhook(Request $request)
    {

        // 1. runner_accepted  -- d,1
        // 2. reached_for_pickup  -- d,2
        // 3. pickup_complete  -- o,4 - d,3
        // 4. started_for_delivery -- o,5 - d,4
        // 5. delivered -- 6--5

        try {
            $jsonData = json_decode($request->getContent());
            $taskId = $jsonData->task_id;
            $details =OrderVendor::where('web_hook_code',$taskId)->first();
            // $trackingStatus = $this->getTrackInfo($taskId);
            switch ($jsonData->state) {
                case 'queued':
                    VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'1']);
                    break;
                case 'runner_accepted':
                    VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'2']);
                    break;
                case 'pickup_complete':
                     //Update in vendor status
				    VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'4']);
				
				    VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'3']);

                    $details->dispatch_traking_url = $jsonData->tracking_url;
                    $details->save();
                    break;
                case 'started_for_delivery':
                    //Update in vendor status
                    VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'5']);
	
                    VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'4']);
                    break;
                case 'delivered':
                   
                    //Update in vendor status
                    VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'6']);
        
                    VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'5','type'=>'2']);

                    break;
                case 'cancelled':
                case 'location_cancelled':
                    //Update in vendor status
                    VendorOrderStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'order_status_option_id'=>'3']);
        
                    VendorOrderDispatcherStatus::Create(['order_id'=>$details->order_id,'vendor_id'=>$details->vendor_id,'dispatcher_status_option_id'=>'6','type'=>'2']);
                    break;
                // Add more cases as needed
                // Default case if the state doesn't match any known values
                default:
                    // Handle unknown state if necessary
            }

           
            return response([], 200);
        } catch (\Exception $e) {
            // Handle exceptions here
            \Log::info('webhook error dunzod4--'.$e->getMessage().$e->getLine());
            Webhook::create(['tracking_order_id'=>'2222','response'=>$request->getContent()]);
            return response([],200);
        }
    }

    public function getTrackInfo($task_id)
    {
        $traking_res = Http::withHeaders([
            'client-id' => $this->client_id,
            'Authorization' => $this->token,
            'Accept-Language' => 'en_US',
            'Content-Type' => 'application/json',
        ])
        ->get( $this->app_url.'/v1/tasks/'.$task_id.'/status');
            $res = $traking_res->json();
            if(property_exists($res,'tracking_url'))
            {
                return $res->tracking_url;
            }
        return null;
    }

	public function getDistance($vendorId)
    {
		$this->configuration();
		if($this->status == 1){
			$customer = User::find(Auth::id());
			$cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
			$vendor_details = Vendor::find($vendorId);
			$latitude[] =  $vendor_details->latitude ?? 30.71728880;
			$latitude[] =  $cus_address->latitude ?? 30.717288800000;
			$longitude[] =  $vendor_details->longitude ?? 76.803508700000;
			$longitude[] =  $cus_address->longitude ?? 76.803508700000;
			$distance =  GoogleDistanceMatrix($latitude,$longitude);
			return $distance['distance'];
		}
		return false;
    }
    # get delivery fee getDunzoBaseFee
    public function getDunzoBaseFee($vendorId,$distance = null)
    {
        $fees = 0;
        $this->configuration();
        if($this->status == 1 && $this->base_price>0){
                if(!$distance){
                    $distance = $this->getDistance($vendorId);
                }
            if($distance){
                //Helper Function
                $fees =   getBaseprice($distance,'dunzo');
            }
        }
        return $fees;
    }

    # get delivery fee Dunzo Courier Service
    public function getQuotations($vendor_id,$address)
    {
        $this->configuration();
        $vendor_details = Vendor::find($vendor_id);
        $data =array(
            'pickup_lat'=>$vendor_details->latitude ?? '',
            'pickup_long'=>$vendor_details->longitude ?? '',
            'delivery_lat' => $address->latitude, //Required
            'delivery_long' => $address->longitude, //Required
        );
        $status =  $this->getfees($data);
        if($this->status){
            if(($this->base_price>0) && $status->status == true){
            return $this->getDunzoBaseFee($vendor_id,$status->data->distance);
        }else{
                if($status->status){
                    return $status->data->estimated_price??0;
                }
                return 0;
            }
        }
        return 0;
    }

    # get delivery fee Courier Service
    public function getCourierService($vendorId)
    {
        $this->configuration();
        $vendor_details = Vendor::find($vendorId);
        $data =array(
            'pickup_lat'=>$vendor_details->latitude ?? '',
            'pickup_long'=>$vendor_details->longitude ?? ''
        );
        $status =  $this->checkAvilabilty($data);
        if($this->status == 1){
            if(($this->base_price>0) && $status->status == true){
            return $this->getDunzoBaseFee($vendorId);
        }else{
                if($status->status){
                    return $this->getDunzoBaseFee($vendorId);
                }
                return 0;
            }
        }
    }

    public function cancelOrderRequestD4BDunzo($task_id,$reason)
    {
		if($this->status){
            $data =array('cancellation_reason'=>$reason??'No Need');
			return $cancel_order= $this->cancelOrder($data,$task_id);
		}
    }

}
