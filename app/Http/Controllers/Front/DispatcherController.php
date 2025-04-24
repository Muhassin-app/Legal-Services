<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Requests\DispatchOrderStatusUpdateRequest;
use App\Http\Controllers\Front\FrontController;
use Carbon\Carbon;
use Auth;
use Session;
use DB;
use App\Http\Traits\{ApiResponser, OrderBlockchain, OrderTrait};
use App\Models\{Order, OrderProduct, OrderTax, OrderCancelRequest, Cart, CartAddon, CartProduct, CartProductPrescription, Product, OrderProductAddon, ClientPreference, ClientCurrency, OrderVendor, UserAddress, CartCoupon, VendorOrderStatus, VendorOrderDispatcherStatus, OrderStatusOption, Vendor, LoyaltyCard, NotificationTemplate, User, Payment, SubscriptionInvoicesUser, UserDevice, Client, ClientPreferenceAdditional, UserVendor, LuxuryOption, EmailTemplate, OrderQrcodeLinks, ProductVariantSet, QrcodeImport,OrderProductDispatchRoute,VendorOrderProductDispatcherStatus,OrderLongTermServiceSchedule,PickDropDriverBid,VendorOrderProductStatus,UserBidRideRequest};
use Illuminate\Support\Facades\Log;

class DispatcherController extends FrontController
{
    use ApiResponser,OrderTrait,OrderBlockchain;


    /******************    ---- order status update from dispatch (Need to dispatcher_status_option_id ) -----   ******************/
    public function dispatchOrderStatusUpdate(DispatchOrderStatusUpdateRequest $request, $domain = '', $web_hook_code)
    {
        try {
            DB::beginTransaction();
            $checkiftokenExist = OrderVendor::where('web_hook_code',$web_hook_code)->first();

            if($checkiftokenExist){

                 //Checking Bag QrCode imported in order panel only if qrcheck parameter is came from dispatcher
                 if(isset($request->check_qr) && isset($request->qr_code))
                 {
                     $code = QrcodeImport::with('vendorDetail')->where('code',$request->qr_code)->first();
                     if(!isset($code->code))
                     {
                        return response()->json([
                                'status' => '0',
                                'message' => 'Not Found'
                            ]);
                     }
                 }

                 if($request->check_qr=='5' && isset($request->qr_code))
                 {
                    $order = Order::where('order_number',$request->order_number)->first();
                    $code = QrcodeImport::with('vendorDetail')->where('code',$request->qr_code)->first();
                    if($code){
                        $qrcodes = OrderQrcodeLinks::updateOrCreate(['order_id' => $order->id,'qrcode_id'=>$code->id], ['order_id'=>$order->id,'order_number'=>$order->order_number,'qrcode_id'=>$code->id,'code'=>$code->code]);
                    }
                 }


                $update = VendorOrderDispatcherStatus::updateOrCreate(['dispatcher_id' => null,
                    'order_id' =>  $checkiftokenExist->order_id,
                    'dispatcher_status_option_id' =>  $request->dispatcher_status_option_id,
                    'vendor_id' =>  $checkiftokenExist->vendor_id,
                    'type' =>  $request->task_type??1]);
                    $this->sendOrderNotification($update->id);
                    $type = $request->task_type??1;
                   $dispatch_status = $request->dispatcher_status_option_id;

                    switch ($dispatch_status) {
                        case 2:
                            $request->status_option_id = 2;
                            break;
                      case 3:
                        $request->status_option_id = 4;
                        break;
                      case 4:
                        $request->status_option_id = 5;
                        break;
                      case 5:
                        $request->status_option_id = 6;
                        break;
                      case 6: //order rejected by driver
                          $request->status_option_id = 3;
                          break;
                      default:
                       $request->status_option_id = null;
                    }

                    # vendor status update

                    if(isset($request->status_option_id) && !empty($request->status_option_id) && (in_array($request->status_option_id ,[6,3,4])) && $type == 2){

                        $checkif= VendorOrderStatus::where(['order_id' =>  $checkiftokenExist->order_id,
                        'order_status_option_id' =>  $request->status_option_id,
                        'vendor_id' =>  $checkiftokenExist->vendor_id,
                        'order_vendor_id' =>  $checkiftokenExist->id])->count();

                        if($checkif == 0){
                            $update_vendor = VendorOrderStatus::updateOrCreate([
                                'order_id' =>  $checkiftokenExist->order_id,
                                'order_status_option_id' =>  $request->status_option_id,
                                'vendor_id' =>  $checkiftokenExist->vendor_id,
                                'order_vendor_id' =>  $checkiftokenExist->id ]);

                                OrderVendor::where('vendor_id', $checkiftokenExist->vendor_id)->where('order_id', $checkiftokenExist->order_id)->update(['order_status_option_id' => $request->status_option_id]);
                                // if driver is reject order
                                if($request->status_option_id == 3 ){
                                    $this->cancelOrderByDriver($checkiftokenExist);
                                }
                        }


                    }


                if($request->waiting_price && $request->waiting_price>0)
                {
                    OrderVendor::where('vendor_id', $checkiftokenExist->vendor_id)->where('order_id', $checkiftokenExist->order_id)->update(['waiting_price' => $request->waiting_price??0,'waiting_time'=>$request->waiting_time]);

                    $orderVendDetail = OrderVendor::where('order_id', $checkiftokenExist->order_id);
                    $total_waiting_price = $orderVendDetail->sum('waiting_price');
                    $total_waiting_time = $orderVendDetail->sum('waiting_time');

                    $payable_amount =  Order::where('id', $checkiftokenExist->order_id)->value('payable_amount');
                    $old_payable_amount =  Order::where('id', $checkiftokenExist->order_id)->value('old_payable_amount');
                    $payable_amount = (($old_payable_amount>0)?$old_payable_amount:$payable_amount);
                    Order::where('id', $checkiftokenExist->order_id)->update(['total_waiting_price' => $total_waiting_price??0 ,'total_waiting_time'=>$total_waiting_time,'payable_amount'=>$payable_amount+$total_waiting_price,'old_payable_amount'=>$payable_amount]);
                }


            if(isset($request->dispatch_traking_url) && !empty($request->dispatch_traking_url))
            {
                $update_tr = OrderVendor::where('web_hook_code',$web_hook_code)->update(['dispatch_traking_url' =>  $request->dispatch_traking_url]);
            }
         
                OrderVendor::where('vendor_id', $checkiftokenExist->vendor_id)->where('order_id', $checkiftokenExist->order_id)->update(['dispatcher_status_option_id' => $request->dispatcher_status_option_id]);
             $data = ['order'=>$update,'vendor_detail'=>$code->vendorDetail??[]];
             $orderData = Order::find($checkiftokenExist->order_id);

              DB::commit();

                $blockchain_route = ClientPreferenceAdditional::where('key_name','blockchain_route_formation')->first();
    
                if(isset($blockchain_route) && ($blockchain_route->key_value == 1))
                { 
                    @$this->moveOrderToWarehouse($orderData,$request ?? null);

                }
                    $message = "Order status updated.";
                    return $this->successResponse($data??[], $message);

            }else{
                DB::rollback();
                $message = "Invalid Order Token";
                return $this->errorResponse($message, 400);
               }

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());

        }
    }

    /******************    ---- order status update from dispatch for single product base (Need to dispatcher_status_option_id ) -----   ******************/
    public function dispatchOrderSingleProductStatusUpdate(DispatchOrderStatusUpdateRequest $request, $domain = '', $web_hook_code)
    {

        try {
            DB::beginTransaction();
            $checkiftokenExist = OrderProductDispatchRoute::where('web_hook_code',$web_hook_code)->first();

            if($checkiftokenExist){

                //Checking Bag QrCode imported in order panel only if qrcheck parameter is came from dispatcher
                if(isset($request->check_qr) && isset($request->qr_code))
                {
                    $code = QrcodeImport::with('vendorDetail')->where('code',$request->qr_code)->first();
                    if(!isset($code->code))
                    {
                        return response()->json([
                                'status' => '0',
                                'message' => 'Not Found'
                            ]);
                    }
                }


                if($request->check_qr=='5' && isset($request->qr_code))
                {
                    $order = Order::where('order_number',$request->order_number)->first();
                    $code = QrcodeImport::with('vendorDetail')->where('code',$request->qr_code)->first();
                    if($code){
                        $qrcodes = OrderQrcodeLinks::updateOrCreate(['order_id' => $order->id,'qrcode_id'=>$code->id], ['order_id'=>$order->id,'order_number'=>$order->order_number,'qrcode_id'=>$code->id,'code'=>$code->code]);
                    }
                }


                $update = VendorOrderProductDispatcherStatus::updateOrCreate([
                                                                                'dispatcher_id' => null,
                                                                                'order_id' =>  $checkiftokenExist->order_id,
                                                                                'dispatcher_status_option_id' =>  $request->dispatcher_status_option_id,
                                                                                'vendor_id' =>  $checkiftokenExist->vendor_id,
                                                                                'order_product_route_id' =>  $checkiftokenExist->id,
                                                                                'type' =>  $request->task_type??1
                                                                            ]);
                //$this->sendOrderProductNotification($update->id);
                $type = $request->task_type??1;
                $dispatch_status = $request->dispatcher_status_option_id;

                switch ($dispatch_status) {
                    case 2:
                        $request->status_option_id = 2;
                        break;
                    case 3:
                    $request->status_option_id = 4;
                    break;
                    case 4:
                    $request->status_option_id = 5;
                    break;
                    case 5:
                    $request->status_option_id = 6;
                    break;
                    case 6: //order rejected by driver
                    $request->status_option_id = 3;
                    break;
                    default:
                    $request->status_option_id = null;
                }

                    # vendor status update

                if(isset($request->status_option_id) && !empty($request->status_option_id) && (in_array($request->status_option_id ,[6,3])) && $type == 2){

                        $checkif= VendorOrderProductDispatcherStatus::where([
                        'order_id' =>  $checkiftokenExist->order_id,
                        'order_status_option_id' =>  $request->status_option_id,
                        'order_product_route_id' => $checkiftokenExist->id
                        ])->count();


                    if($checkif == 0){
                        $update_vendor = VendorOrderProductDispatcherStatus::updateOrCreate([
                                                            'order_id' =>  $checkiftokenExist->order_id,
                                                            'order_product_route_id' =>$checkiftokenExist->id,
                                                            'order_status_option_id' => $request->status_option_id,
                                                            'dispatcher_status_option_id' =>  $request->dispatcher_status_option_id,
                                                            'vendor_id'          =>  $checkiftokenExist->vendor_id,
                                                            'type'              =>  $request->task_type??1
                                                        ]);
                        OrderProductDispatchRoute::where('id', $checkiftokenExist->id)->update(['order_status_option_id' => $request->status_option_id]);
                        // if driver is reject order
                        if($request->status_option_id == 3 ){
                            $this->cancelVendorOrderProduct($checkiftokenExist->id);
                        }

                    }
                    // get total rout count of order vendor
                    $total_route_query = OrderProductDispatchRoute::where('order_vendor_id', $checkiftokenExist->order_vendor_id);
                    $total_route = $total_route_query->count();
                    $total_complet_route = $total_route_query->where('dispatcher_status_option_id', '5')->count(); // dispatch complet task

                    // update order status
                    if($total_route == ($total_complet_route +1 )){
                        $OrderVendor = OrderVendor::where('id', $checkiftokenExist->order_vendor_id)->select('vendor_id','id','order_status_option_id')->first();

                        if( $OrderVendor ){
                            $checkifVendor= VendorOrderStatus::where([
                                'order_id' =>  $checkiftokenExist->order_id,
                                'order_status_option_id' =>  $request->status_option_id,
                                'vendor_id' =>  $OrderVendor->vendor_id,
                                'order_vendor_id' =>  $OrderVendor->id
                                ])->count();

                            if($checkifVendor == 0){
                                $update_vendor = VendorOrderStatus::updateOrCreate([
                                    'order_id' =>  $checkiftokenExist->order_id,
                                    'order_status_option_id' =>  $request->status_option_id,
                                    'vendor_id' =>  $OrderVendor->vendor_id,
                                    'order_vendor_id' =>  $OrderVendor->id
                                ]);
                                $res  =   OrderVendor::where('id', $checkiftokenExist->order_vendor_id)->update(['order_status_option_id' => $request->status_option_id]);
                            }
                        }
                    }
                }
                if(isset($request->dispatch_traking_url) && !empty($request->dispatch_traking_url))
                {
                    $update_tr = OrderProductDispatchRoute::where('web_hook_code',$web_hook_code)->update(['dispatch_traking_url' =>  $request->dispatch_traking_url]);
                }
                OrderProductDispatchRoute::where('id', $checkiftokenExist->id)->where('order_id', $checkiftokenExist->order_id)->update(['dispatcher_status_option_id' => $request->dispatcher_status_option_id]);


                $update = VendorOrderProductStatus::updateOrCreate([
                    'order_id' =>  $checkiftokenExist->order_id,
                    'dispatcher_status_option_id' =>  $request->dispatcher_status_option_id,
                    'order_status_option_id' =>  $request->status_option_id,
                    'order_vendor_id' =>  $checkiftokenExist->order_vendor_id,
                    'order_vendor_product_id' =>  $checkiftokenExist->order_vendor_product_id,
                ]);

                OrderProduct::where('id',$checkiftokenExist->order_vendor_product_id)->update(['dispatcher_status_option_id'=>$request->dispatcher_status_option_id,'order_status_option_id'=>$request->status_option_id]);

                $data = ['order'=>$update,'vendor_detail'=>$code->vendorDetail??[]];
                DB::commit();
                    $message = "Order status updated.";
                    return $this->successResponse($data??[], $message);

            }else{
                DB::rollback();
                $message = "Invalid Order Token";
                return $this->errorResponse($message, 400);
                }

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /******************    ---- order status update from dispatch for Long service product base (Need to dispatcher_status_option_id ) -----   ******************/
    public function dispatchOrderServiceProductStatusUpdate(DispatchOrderStatusUpdateRequest $request, $domain = '', $web_hook_code)
    {

        try {
            DB::beginTransaction();
            $checkiftokenExist = OrderLongTermServiceSchedule::with('OrderService.orderProduct')->where('web_hook_code',$web_hook_code)->first();

          //  pr(  $checkiftokenExist->toArray() );
            if($checkiftokenExist){

                //Checking Bag QrCode imported in order panel only if qrcheck parameter is came from dispatcher
                if(isset($request->check_qr) && isset($request->qr_code))
                {
                    $code = QrcodeImport::with('vendorDetail')->where('code',$request->qr_code)->first();
                    if(!isset($code->code))
                    {
                        return response()->json([
                                'status' => '0',
                                'message' => 'Not Found'
                            ]);
                    }
                }


                if($request->check_qr=='5' && isset($request->qr_code))
                {
                    $order = Order::where('order_number',$request->order_number)->first();
                    $code = QrcodeImport::with('vendorDetail')->where('code',$request->qr_code)->first();
                    if($code){
                        $qrcodes = OrderQrcodeLinks::updateOrCreate(['order_id' => $order->id,'qrcode_id'=>$code->id], ['order_id'=>$order->id,'order_number'=>$order->order_number,'qrcode_id'=>$code->id,'code'=>$code->code]);
                    }
                }


                $update = VendorOrderProductDispatcherStatus::updateOrCreate([
                                                                            'dispatcher_id' => null,
                                                                            'order_id' =>  @$checkiftokenExist->OrderService->orderProduct->order_id,
                                                                            'dispatcher_status_option_id' =>  $request->dispatcher_status_option_id,
                                                                            'vendor_id' =>  @$checkiftokenExist->OrderService->orderProduct->vendor_id,
                                                                            'long_term_schedule_id' =>  $checkiftokenExist->id,
                                                                            'type' =>  $request->task_type??1
                                                                            ]);
                //$this->sendOrderProductNotification($update->id);
                $type = $request->task_type??1;
                $dispatch_status = $request->dispatcher_status_option_id;

                switch ($dispatch_status) {
                    case 2:
                        $request->status_option_id = 2;
                        break;
                    case 3:
                    $request->status_option_id = 4;
                    break;
                    case 4:
                    $request->status_option_id = 5;
                    break;
                    case 5:
                    $request->status_option_id = 6;
                    break;
                    default:
                    $request->status_option_id = null;
                }

                    # vendor status update

                if(isset($request->status_option_id) && !empty($request->status_option_id) && $request->status_option_id == 6 && $type == 2){

                        $checkif= VendorOrderProductDispatcherStatus::where([
                        'order_id' =>   @$checkiftokenExist->OrderService->orderProduct->order_id,
                        'order_status_option_id' =>  $request->status_option_id,
                        'long_term_schedule_id'  => $checkiftokenExist->id
                        ])->count();


                    if($checkif == 0){
                        $update_vendor = VendorOrderProductDispatcherStatus::updateOrCreate([
                                                            'order_id' =>  @$checkiftokenExist->OrderService->orderProduct->order_id,
                                                            'long_term_schedule_id' =>$checkiftokenExist->id,
                                                            'order_status_option_id' => $request->status_option_id,
                                                            'dispatcher_status_option_id' =>  $request->dispatcher_status_option_id,
                                                            'vendor_id'          =>  @$checkiftokenExist->OrderService->orderProduct->vendor_id,
                                                            'type'              =>  $request->task_type??1
                                                        ]);
                            OrderLongTermServiceSchedule::where('id', $checkiftokenExist->id)->update(['order_status_option_id' => $request->status_option_id,'status'=>1]);

                    }
                    // get total rout count of order vendor
                    $total_service_query = OrderLongTermServiceSchedule::where('order_long_term_services_id', $checkiftokenExist->order_long_term_services_id);
                    $total_route = $total_service_query->count();
                    $total_complet_route = $total_service_query->where('dispatcher_status_option_id', '5')->count(); // dispatch complet task

                    // update order status
                    if($total_route == ($total_complet_route +1 )){

                        $OrderVendor = OrderVendor::where('id', @$checkiftokenExist->OrderService->orderProduct->order_vendor_id)->select('vendor_id','id','order_status_option_id')->first();

                        if( $OrderVendor ){
                            $checkifVendor= VendorOrderStatus::where([
                                'order_id' =>  $OrderVendor->order_id,
                                'order_status_option_id' =>  $request->status_option_id,
                                'vendor_id' =>  $OrderVendor->vendor_id,
                                'order_vendor_id' =>  $OrderVendor->id
                                ])->count();

                            if($checkifVendor == 0){
                                $update_vendor = VendorOrderStatus::updateOrCreate([
                                    'order_id' =>  $OrderVendor->order_id,
                                    'order_status_option_id' =>  $request->status_option_id,
                                    'vendor_id' =>  $OrderVendor->vendor_id,
                                    'order_vendor_id' =>  $OrderVendor->id
                                ]);
                                $res  =   OrderVendor::where('id', $checkiftokenExist->order_vendor_id)->update(['order_status_option_id' => $request->status_option_id]);
                           }
                        }
                    }
                }
                if(isset($request->dispatch_traking_url) && !empty($request->dispatch_traking_url))
                {
                    $update_tr = OrderProductDispatchRoute::where('web_hook_code',$web_hook_code)->update(['dispatch_traking_url' =>  $request->dispatch_traking_url]);
                }
                OrderLongTermServiceSchedule::where('id', $checkiftokenExist->id)->update(['dispatcher_status_option_id' => $request->dispatcher_status_option_id]);

                $data = ['order'=>$update,'vendor_detail'=>$code->vendorDetail??[]];
                DB::commit();
                    $message = "Order status updated.";
                    return $this->successResponse($data??[], $message);

            }else{
                DB::rollback();
                $message = "Invalid Order Token";
                return $this->errorResponse($message, 400);
                }

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /******************    ---- pickup delivery status update (Need to dispatcher_status_option_id ) -----   ******************/
    public function dispatchPickupDeliveryUpdate(Request $request, $domain = '', $web_hook_code)
    {
   
        try {
            DB::beginTransaction();
            $checkiftokenExist = OrderVendor::where('web_hook_code',$web_hook_code)->first();
            $type = $request->task_type??1;

            if($checkiftokenExist){
                $dispatch_status = $request->dispatcher_status_option_id;
                switch ($dispatch_status) {
                  case 2:
                        // $request->status_option_id = 2;
                        $request->request->add(['status_option_id'=> '2']);
                        break;
                  case 3:
                    // $request->status_option_id = 4;
                    $request->request->add(['status_option_id'=> '4']);
                    break;
                  case 4:
                    // $request->status_option_id = 5;
                    $request->request->add(['status_option_id'=> '5']);
                    break;
                  case 5:
                    // $request->status_option_id = 6;
                    $request->request->add(['status_option_id'=> '6']);
                    break;
                  case 6: //order rejected by driver
                      $request->request->add(['status_option_id'=> '3']);
                      break;
                  default:
                   $request->status_option_id = null;
                }
                $orderUserInfo= User::where('id',$checkiftokenExist->user_id)->first();
                if(isset($request->status_option_id) && !empty($request->status_option_id) &&  (in_array($request->status_option_id ,[6,3])) && $type == 2){
                    $checkif= VendorOrderStatus::where(['order_id' =>  $checkiftokenExist->order_id,
                    'order_status_option_id' =>  $request->status_option_id,
                    'vendor_id' =>  $checkiftokenExist->vendor_id,
                    'order_vendor_id' =>  $checkiftokenExist->id])->count();
                    if($checkif == 0){
                        $update_vendor = VendorOrderStatus::updateOrCreate([
                            'order_id' =>  $checkiftokenExist->order_id,
                            'order_status_option_id' =>  $request->status_option_id,
                            'vendor_id' =>  $checkiftokenExist->vendor_id,
                            'order_vendor_id' =>  $checkiftokenExist->id ]);

                            OrderVendor::where('vendor_id', $checkiftokenExist->vendor_id)->where('order_id', $checkiftokenExist->order_id)->update(['order_status_option_id' => $request->status_option_id]);
                            // if driver is reject order
                            if($request->status_option_id == 3 ){
                                $this->cancelOrderByDriver($checkiftokenExist);
                            }
                    }
                }

                // AAA
                    $to = "";
                    $username = "";
                    
                    $to = '+' . $orderUserInfo->dial_code . $orderUserInfo->phone_number;
                    $username = $orderUserInfo->name;

                    $arr = ['is_sms_complete_order','is_sms_booked_ride'];
                    $config = ClientPreferenceAdditional::whereIn('key_name',$arr)->pluck('key_value','key_name');
                    $data = ClientPreference::select('sms_credentials','sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();

                    if(!empty($data->sms_provider)  && !empty($data->sms_key) && !empty($data->sms_secret) && !empty($data->sms_from)) {

                        if(isset($config['is_sms_booked_ride']) && $config['is_sms_booked_ride'] == 1 && $dispatch_status == 2)
                        {
                            $provider = $data->sms_provider;
                            $keyData = ['{user_name}'=>$username??''];
                            $body = sendSmsTemplate('ride-booked',$keyData);
                            $this->sendSmsNew($provider, $data->sms_key, $data->sms_secret, $data->sms_from, $to, $body);
                        }

                        if(isset($config['is_sms_complete_order']) && $config['is_sms_complete_order'] == 1 && $dispatch_status == 5)
                        {
                            $provider = $data->sms_provider;
                            $keyData = ['{user_name}'=>$username??''];
                            $body = sendSmsTemplate('order-completed',$keyData);
                            $this->sendSmsNew($provider, $data->sms_key, $data->sms_secret, $data->sms_from, $to, $body);
                        }
                    }

                // END

                $update = VendorOrderDispatcherStatus::updateOrCreate(['dispatcher_id' => null,
                    'order_id' =>  $checkiftokenExist->order_id,
                    'dispatcher_status_option_id' =>  $request->dispatcher_status_option_id,
                    'vendor_id' =>  $checkiftokenExist->vendor_id,
                    'type' =>  $request->task_type??1]);

                $this->sendOrderNotification($update->id);

            if(isset($request->dispatch_traking_url) && !empty($request->dispatch_traking_url))
            {
                $update_tr = OrderVendor::where('web_hook_code',$web_hook_code)->update(['dispatch_traking_url' =>  $request->dispatch_traking_url]);
            }

            OrderVendor::where('vendor_id', $checkiftokenExist->vendor_id)->where('order_id', $checkiftokenExist->order_id)->update(['dispatcher_status_option_id' => $request->dispatcher_status_option_id]);

            if($request->waiting_price && $request->waiting_price>0)
                {
                    OrderVendor::where('vendor_id', $checkiftokenExist->vendor_id)->where('order_id', $checkiftokenExist->order_id)->update(['waiting_price' => $request->waiting_price??0,'waiting_time'=>$request->waiting_time]);

                    $orderVendDetail = OrderVendor::where('order_id', $checkiftokenExist->order_id);
                    $total_waiting_price = $orderVendDetail->sum('waiting_price');
                    $total_waiting_time = $orderVendDetail->sum('waiting_time');

                   // \Log::info('total_waiting_price : '.$total_waiting_price.' -- total_waiting_time ='.$total_waiting_time);
                    $payable_amount =  Order::where('id', $checkiftokenExist->order_id)->value('payable_amount');
                    $old_payable_amount =  Order::where('id', $checkiftokenExist->order_id)->value('old_payable_amount');
                    $payable_amount = (($old_payable_amount>0)?$old_payable_amount:$payable_amount);
                    Order::where('id', $checkiftokenExist->order_id)->update(['total_waiting_price' => $total_waiting_price??0 ,'total_waiting_time'=>$total_waiting_time,'payable_amount'=>$payable_amount+$total_waiting_price,'old_payable_amount'=>$payable_amount]);
                }


              DB::commit();
                    $message = "Order status updated.";
                    return $this->successResponse($update, $message);

            }else{
                DB::rollback();
                $message = "Invalid Order Token";
                return $this->errorResponse($message, 400);
               }

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());

        }
    }

    /******************    ---- share all details of order for dispatcher -----   ******************/
    public function dispatchOrderDetails(Request $request, $domain = '', $web_hook_code)
    {
        try {
            $user = Auth::user();
            $order_item_count = 0;
            $order_vendor = OrderVendor::where('web_hook_code',$web_hook_code)->first();
            if(isset($order_vendor) && !empty($order_vendor)){
                $order = Order::where('id',$order_vendor->order_id)->first();
                $user = User::where('id',$order->user_id)->first();
                $language_id = $user->language;
                $order_id = $order_vendor->order_id;
                $vendor_id = $order_vendor->vendor_id;
                if ($vendor_id) {
                    $order = Order::with([
                        'vendors' => function ($q) use ($vendor_id) {
                            $q->where('vendor_id', $vendor_id);
                        },
                        'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                            $qry->where('language_id', $language_id);
                        }, 'vendors.dineInTable.category',
                        'vendors.products' => function ($q) use ($vendor_id) {
                            $q->where('vendor_id', $vendor_id);
                        },
                        'vendors.products.translation' => function ($q) use ($language_id) {
                            $q->select('id', 'product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                            $q->where('language_id', $language_id);
                        },
                        'vendors.products.pvariant.vset.optionData.trans', 'vendors.products.addon', 'vendors.coupon', 'address', 'vendors.products.productRating', 'vendors.allStatus',
                        'vendors.cancel_request',
                        'user','user.passbase_verification','user.passbase_verification.resources'
                    ])
                    ->where(function ($q1) {
                        $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1]);
                        $q1->orWhere(function ($q2) {
                            $q2->where('payment_option_id', 1);
                        });
                    })
                    ->where('id', $order_id)->select('*','id as total_discount_calculate')->first();
                } else {
                    $order = Order::with(
                        [
                            'vendors.vendor',
                            'vendors.products.translation' => function ($q) use ($language_id) {
                                $q->select('id', 'product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                                $q->where('language_id', $language_id);
                            },
                            'vendors.products.pvariant.vset.optionData.trans', 'vendors.products.addon', 'vendors.coupon', 'address', 'vendors.products.productRating',
                            'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                                $qry->where('language_id', $language_id);
                            },
                            'vendors.dineInTable.category',
                            'vendors.cancel_request'
                        ]
                    )
                    ->where(function ($q1) {
                        $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1]);
                        $q1->orWhere(function ($q2) {
                            $q2->where('payment_option_id', 1);
                        });
                    })
                    ->where('user_id', $user->id)->where('id', $order_id)->select('*','id as total_discount_calculate')->first();
                }
                $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
                if ($order) {
                    $order->user_name = $order->user->name;
                    $order->user_image = $order->user->image;
                    $order->payment_option_title = __($order->paymentOption->title);
                    $order->created_date = dateTimeInUserTimeZone($order->created_at, $user->timezone);
                    $order->tip_amount = $order->tip_amount;
                    $order->tip = array(
                        ['label' => '5%', 'value' => decimal_format(0.05 * ($order->payable_amount - $order->total_discount_calculate))],
                        ['label' => '10%', 'value' => decimal_format(0.1 * ($order->payable_amount - $order->total_discount_calculate))],
                        ['label' => '15%', 'value' => decimal_format(0.15 * ($order->payable_amount - $order->total_discount_calculate))]
                    );
                    foreach ($order->vendors as $vendor) {
                        $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order_id)->where('vendor_id', $vendor->vendor->id)->orderBy('id', 'DESC')->first();
                        if ($vendor_order_status) {
                            $vendor->order_status =  ['current_status' => ['id' => $vendor_order_status->OrderStatusOption->id, 'title' => __($vendor_order_status->OrderStatusOption->title)]];
                        } else {
                            $vendor->current_status = null;
                        }
                        $couponData = [];
                        $payable_amount = 0;
                        $discount_amount = 0;
                        $product_addons = [];
                        $vendor->vendor_name = $vendor->vendor->name;
                        foreach ($vendor->products as  $product) {
                            $product_addons = [];
                            $variant_options = [];
                            $order_item_count += $product->quantity;
                            $product->product_name = isset($product->product)?isset($product->product->translation_one)?$product->product->translation_one->title:$product->title:$product->title;
                            $product->image_path = $product->media->first() ? $product->media->first()->image->path : $product->image;
                            if ($product->pvariant) {
                                foreach ($product->pvariant->vset as $variant_set_option) {
                                    $variant_options[] = array(
                                        'option' => $variant_set_option->optionData->trans->title,
                                        'title' => $variant_set_option->variantDetail->trans->title,
                                    );
                                }
                            }
                            $product->variant_options = $variant_options;
                            if (!empty($product->addon)) {
                                foreach ($product->addon as $k => $addon) {
                                    // $product_addons[] = array(
                                    //     'addon_id' =>  $addon->addon_id,
                                    //     'addon_title' =>  $addon->set->title,
                                    //     'option_title' =>  $addon->option->title,
                                    // );
                                    $opt_quantity_price = 0;
                                    $opt_price_in_currency = $addon->option ? $addon->option->price : 0;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                    $opt_quantity_price = $opt_price_in_doller_compare * $product->quantity;
                                    $product_addons[$k]['quantity'] = $product->quantity;
                                    $product_addons[$k]['addon_id'] = $addon->addon_id;
                                    $product_addons[$k]['option_id'] = $addon->option_id;
                                    $product_addons[$k]['price'] = $opt_price_in_currency;
                                    $product_addons[$k]['addon_title'] = $addon->set->title;
                                    $product_addons[$k]['quantity_price'] = $opt_quantity_price;
                                    $product_addons[$k]['option_title'] = $addon->option ? $addon->option->title : 0;
                                    // $product_addons[$k]['multiplier'] = $clientCurrency->doller_compare;
                                }
                            }
                            $product->product_addons = $product_addons;
                        }
                        if($vendor->delivery_fee > 0){
                            $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                            $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                            $ETA = $order_pre_time + $user_to_vendor_time;
                            $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time,$user) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                        }
                        if($vendor->dineInTable){
                            $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                            $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                            $vendor->dineInTableCategory = $vendor->dineInTable->category->title; //$vendor->dineInTable->category->first() ? $vendor->dineInTable->category->first()->title : '';
                        }
                    }
                    if(!empty($order->scheduled_date_time)){
                        $order->scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
                    }
                    $luxury_option_name = '';
                    if($order->luxury_option_id > 0){
                        $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
                        if($luxury_option->title == 'takeaway'){
                            $luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
                        }elseif($luxury_option->title == 'dine_in'){
                            $luxury_option_name = 'Dine-In';
                        }else{
                            $luxury_option_name = 'Delivery';
                        }
                    }
                    $order->luxury_option_name = $luxury_option_name;
                    $order->order_item_count = $order_item_count;
                }
                //$data=$order;

                //$data->payable_amount=$data->payable_amount+$data->fixed_fee_amount;
                //dd($data->payable_amount);
                return $this->successResponse($order, null, 201);
            }

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /******************    ---- share all details of order for dispatcher -----   ******************/
    public function dispatchOrderProductDetails(Request $request, $domain = '', $web_hook_code)
    {
        try {
            $user = Auth::user();
            $order_item_count = 0;
            $order_vendor = OrderProductDispatchRoute::where('web_hook_code',$web_hook_code)->first();

            if(isset($order_vendor) && !empty($order_vendor)){
                $order = Order::where('id',$order_vendor->order_id)->first();
                $user = User::where('id',$order->user_id)->first();
                $language_id = $user->language;
                $order_id = $order_vendor->order_id;
                $vendor_id = $order_vendor->order_vendor_id;
                $order_vendor_product_id = $order_vendor->order_vendor_product_id;

                $order = Order::with([
                    'vendors' => function ($q) use ($vendor_id) {
                        $q->where('id', $vendor_id);
                    },
                    'vendors.dineInTable.translations' => function ($qry) use ($language_id) {
                        $qry->where('language_id', $language_id);
                    }, 'vendors.dineInTable.category',
                    'vendors.products' => function ($q) use ($vendor_id, $order_vendor_product_id) {
                        $q->where('id',$order_vendor_product_id);
                    },
                    'vendors.products.translation' => function ($q) use ($language_id) {
                        $q->select('id', 'product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                        $q->where('language_id', $language_id);
                    },
                    'vendors.products.pvariant.vset.optionData.trans', 'vendors.products.addon', 'vendors.coupon', 'address', 'vendors.products.productRating', 'vendors.allStatus',
                    'vendors.cancel_request',
                    'user','user.passbase_verification','user.passbase_verification.resources'
                ])
                // ->where(function ($q1) {
                //     $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1]);
                //     $q1->orWhere(function ($q2) {
                //         $q2->where('payment_option_id', 1);
                //     });
                // })
                ->where('id', $order_id)->select('*','id as total_discount_calculate')->first();

                $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
                if ($order) {
                    $order->user_name = $order->user->name;
                    $order->user_image = $order->user->image;
                    $order->payment_option_title = __($order->paymentOption->title);
                    $order->created_date = dateTimeInUserTimeZone($order->created_at, $user->timezone);
                    $order->tip_amount = $order->tip_amount;
                    $order->tip = array(
                        ['label' => '5%', 'value' => decimal_format(0.05 * ($order->payable_amount - $order->total_discount_calculate))],
                        ['label' => '10%', 'value' => decimal_format(0.1 * ($order->payable_amount - $order->total_discount_calculate))],
                        ['label' => '15%', 'value' => decimal_format(0.15 * ($order->payable_amount - $order->total_discount_calculate))]
                    );
                    foreach ($order->vendors as $vendor) {
                        $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order_id)->where('vendor_id', $vendor->vendor->id)->orderBy('id', 'DESC')->first();
                        if ($vendor_order_status) {
                            $vendor->order_status =  ['current_status' => ['id' => $vendor_order_status->OrderStatusOption->id, 'title' => __($vendor_order_status->OrderStatusOption->title)]];
                        } else {
                            $vendor->current_status = null;
                        }
                        $couponData = [];
                        $payable_amount = 0;
                        $discount_amount = 0;
                        $product_addons = [];
                        $vendor->vendor_name = $vendor->vendor->name;
                        foreach ($vendor->products as  $product) {
                            $product_addons = [];
                            $variant_options = [];
                            $order_item_count += $product->quantity;
                            $product->image_path = $product->media->first() ? $product->media->first()->image->path : $product->image;
                            if ($product->pvariant) {
                                foreach ($product->pvariant->vset as $variant_set_option) {
                                    $variant_options[] = array(
                                        'option' => $variant_set_option->optionData->trans->title,
                                        'title' => $variant_set_option->variantDetail->trans->title,
                                    );
                                }
                            }
                            $product->variant_options = $variant_options;
                            if (!empty($product->addon)) {
                                foreach ($product->addon as $k => $addon) {

                                    $opt_quantity_price = 0;
                                    $opt_price_in_currency = $addon->option ? $addon->option->price : 0;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                    $opt_quantity_price = $opt_price_in_doller_compare * $product->quantity;
                                    $product_addons[$k]['quantity'] = $product->quantity;
                                    $product_addons[$k]['addon_id'] = $addon->addon_id;
                                    $product_addons[$k]['option_id'] = $addon->option_id;
                                    $product_addons[$k]['price'] = $opt_price_in_currency;
                                    $product_addons[$k]['addon_title'] = $addon->set->title;
                                    $product_addons[$k]['quantity_price'] = $opt_quantity_price;
                                    $product_addons[$k]['option_title'] = $addon->option ? $addon->option->title : 0;
                                    // $product_addons[$k]['multiplier'] = $clientCurrency->doller_compare;
                                }
                            }
                            $product->product_addons = $product_addons;
                        }
                        if($vendor->delivery_fee > 0){
                            $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                            $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                            $ETA = $order_pre_time + $user_to_vendor_time;
                            $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time,$user) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                        }
                        if($vendor->dineInTable){
                            $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                            $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                            $vendor->dineInTableCategory = $vendor->dineInTable->category->title; //$vendor->dineInTable->category->first() ? $vendor->dineInTable->category->first()->title : '';
                        }
                    }
                    if(!empty($order->scheduled_date_time)){
                        $order->scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
                    }
                    $luxury_option_name = '';
                    if($order->luxury_option_id > 0){
                        $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
                        if($luxury_option->title == 'takeaway'){
                            $luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
                        }elseif($luxury_option->title == 'dine_in'){
                            $luxury_option_name = 'Dine-In';
                        }else{
                            $luxury_option_name = 'Delivery';
                        }
                    }
                    $order->luxury_option_name = $luxury_option_name;
                    $order->order_item_count = $order_item_count;
                }

                $order['DatabaseName'] = DB::connection()->getDatabaseName().'_';
                return $this->successResponse($order, null, 201);
            }

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function test(Request $request){
        $devices[] = $request->token;

        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        $orderData = Order::latest()->first();

        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {

               $title = "test notification";
                $body =  "test";

                //pr($title);
                //pr($body);
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $title,
                        'body'  => $body,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => route('order.index'),
                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $title,
                        'body'  => $body,
                        'data' => $orderData,
                        'type' => "order_created"
                    ],
                    "priority" => "high"
                ];
                sendFcmCurlRequest($data);
        }
    }
    /******************    ---- send notification to user -----   ******************/
    public function sendOrderNotification( $vendor_order_status_id )
    {

        $OrderStatus = VendorOrderDispatcherStatus::select('*','dispatcher_status_option_id as status_data')->find($vendor_order_status_id);

        if($OrderStatus){
            $orderNumber = Order::where('id',$OrderStatus->order_id)->select('order_number','user_id')->first();

            $user_id = $orderNumber ? $orderNumber->user_id : '';
            // $checkuservendor = UserVendor::where('user_id',$user_id)->first();
            // $sound = ($checkuservendor)?"notification.wav":"default";
            $devices = UserDevice::whereNotNull('device_token')->where('user_id', $user_id)->pluck('device_token');

            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                    $title = __('Order Status : #').($orderNumber ?  $orderNumber->order_number : '');
                    $notification_content = NotificationTemplate::where('slug', 'order-cancelled')->first();
                    $body_content =  $OrderStatus ? ($OrderStatus->status_data ? $OrderStatus->status_data['driver_status'] : '') : '';
                    if($OrderStatus->status_data['driver_status'] == ''){
                        $body_content = str_ireplace("{order_id}", "#" . $orderNumber->order_number, $notification_content->content ?? "");
                    }

                    //pr($title);
                    //pr($body);
                    $data = [
                        "registration_ids" => $devices,
                        "notification" => [
                            'title' => $title,
                            'body'  => $body_content,
                            'sound' => "default",
                            "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                            'click_action' => route('order.index'),
                            "android_channel_id" => "default-channel-id"
                        ],
                        "data" => [
                            'title' => $title,
                            'body'  => $body_content,
                            'data' => '',
                            'type' => ""
                        ],
                        "priority" => "high"
                    ];
                $result = sendFcmCurlRequest($data);
            }
        }

    }

     /******************    ---- send notification to user as par vendorProduct -----   ******************/
     public function sendOrderProductNotification( $vendor_order_product_status_id )
     {
          //pr($vendor_order_status_id);

         $OrderStatus = VendorOrderProductDispatcherStatus::select('*','dispatcher_status_option_id as status_data')->find($vendor_order_product_status_id);

         if($OrderStatus){
             $orderNumber = Order::where('id',$OrderStatus->order_id)->select('order_number','user_id')->first();

             $user_id = $orderNumber ? $orderNumber->user_id : '';
             // $checkuservendor = UserVendor::where('user_id',$user_id)->first();
             // $sound = ($checkuservendor)?"notification.wav":"default";
             $devices = UserDevice::whereNotNull('device_token')->where('user_id', $user_id)->pluck('device_token');

             $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();

             if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {


                     $title = __('Order Status : #').($orderNumber ?  $orderNumber->order_number : '');
                     $body =  $OrderStatus ? ($OrderStatus->status_data ? $OrderStatus->status_data['driver_status'] : '') : '';

                     //pr($title);
                     //pr($body);
                     $data = [
                         "registration_ids" => $devices,
                         "notification" => [
                             'title' => $title,
                             'body'  => $body,
                             'sound' => "default",
                             "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                             'click_action' => route('order.index'),
                             "android_channel_id" => "sound-channel-id"
                         ],
                         "data" => [
                             'title' => $title,
                             'body'  => $body,
                             'data' => '',
                             'type' => ""
                         ],
                         "priority" => "high"
                     ];
                     sendFcmCurlRequest($data);

             }
         }

     }
    /******************    ---- create order cancel request from dispatch (Need to dispatcher_status_option_id ) -----   ******************/
    public function dispatchOrderCancelRequest(Request $request, $domain = '', $web_hook_code){
        try{
            DB::beginTransaction();
            $checkiftokenExist = OrderVendor::with('cancel_request')->where('web_hook_code', $web_hook_code)->first();

            if($checkiftokenExist){
                $user = Auth::user();

                if($checkiftokenExist->dispatcher_status_option_id == 5){
                    return response()->json(['status' => 'Error', 'message' => __('Order has already been delivered')]);
                }
                if($checkiftokenExist->order_status_option_id == 3){
                    return response()->json(['status' => 'Error', 'message' => __('Order has already been rejected by vendor')]);
                }
                if($checkiftokenExist->cancel_request && $checkiftokenExist->cancel_request->status == 0){
                    return response()->json(['status' => 'Error', 'message' => __('Cancel request has already been submitted')]);
                }
                if($checkiftokenExist->cancel_request && $checkiftokenExist->cancel_request->status == 1){
                    return response()->json(['status' => 'Error', 'message' => __('Cancel request has already been processed')]);
                }

                $reject_reason = urldecode($request->reject_reason);

                $order_cancel_request = new OrderCancelRequest();
                $order_cancel_request->order_id = $checkiftokenExist->order_id;
                $order_cancel_request->order_vendor_id = $checkiftokenExist->id;
                $order_cancel_request->vendor_id = $checkiftokenExist->vendor_id;
                $order_cancel_request->reject_reason = $reject_reason;
                $order_cancel_request->status = 0;
                $order_cancel_request->save();
                DB::commit();

                $order = Order::select('id', 'order_number', 'payable_amount', 'payment_option_id', 'user_id', 'address_id', 'loyalty_amount_saved', 'total_discount', 'total_delivery_fee', 'total_amount', 'taxable_amount', 'created_at')->find($checkiftokenExist->order_id);
                $super_admin = User::where('is_superadmin', 1)->pluck('id');
                // $user_vendors = UserVendor::where(['vendor_id' => $checkiftokenExist->vendor_id])->pluck('user_id');
                $this->sendOrderCancelRequestNotification($super_admin, $order);

                // AAA
                $to = "";
                $username = "";
                $orderUserInfo= User::where('id',$checkiftokenExist->user_id)->first();
                $to = '+' . $orderUserInfo->dial_code . $orderUserInfo->phone_number;
                $username = $orderUserInfo->name;

                $config = ClientPreferenceAdditional::where('key_name','is_sms_cancel_order')->first();
                $data = ClientPreference::select('sms_credentials','sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
                if(isset($config) && $config->is_sms_cancel_order == 1 && !empty($data->sms_provider) && !empty($data->sms_key) && !empty($data->sms_secret) && !empty($data->sms_from))
                { 
                    $provider = $data->sms_provider;
                    $keyData = ['{user_name}'=>$username??''];
                    $body = sendSmsTemplate('order-canceled',$keyData);
                    $this->sendSmsNew($provider, $data->sms_key, $data->sms_secret, $data->sms_from, $to, $body);
                } 
                //END

                return $this->successResponse('', __('Request for order cancellation has been submitted'));
            }
            else{
                DB::rollback();
                return response()->json(['status' => 'Error', 'message' => __('Invalid Order Token')]);
            }
        }
        catch(\Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    public function sendOrderCancelRequestNotification($user_ids, $orderData)
    {
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            $notification_content = NotificationTemplate::where('id', 13)->first();
            if ($notification_content) {

                $body =  str_replace('{order_id}', $orderData->order_number, $notification_content->content);
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => route('cancel-order.requests'),
                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $body,
                        'data' => $orderData,
                        'type' => "order_cancellation_request"
                    ],
                    "priority" => "high"
                ];
                sendFcmCurlRequest($data);
            }
        }
    }

    /******************----Send--To--Customer--Push--Notification--Per--Distance---From---Dispatcher-----******************/
    public function dispatchCustomerDetails(Request $request, $domain = '', $web_hook_code)
    {
        $checkiftokenExist = OrderVendor::where('web_hook_code',$web_hook_code)->first();

        if(!empty($checkiftokenExist)){
            $devices = UserDevice::whereNotNull('device_token')->where('user_id', $checkiftokenExist->user_id)->pluck('device_token');
            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                   $title = $request->notificationTitle;
                    $body  =  $request->notificationDiscription;
                    $data = [
                        "registration_ids" => $devices,
                        "notification" => [
                            'title'              => $title,
                            'body'               => $body,
                            'sound'              => "default",
                            "icon"               => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                            'click_action'       => route('order.index'),
                            "android_channel_id" => "sound-channel-id"
                        ],
                        "data" => [
                            'title' => $title,
                            'body'  => $body,
                            'data'  => '',
                            'type'  => ""
                        ],
                        "priority" => "high"
                    ];



                    //CURL request to route notification to FCM connection server (provided by Google)
                    $result=sendFcmCurlRequest($data);

            }
        }
    }

    /******************    ---- pickup delivery Driver Bid/pricing update -----   ******************/
    public function dispatchDriverBidUpdate(Request $request, $domain = '', $web_hook_code)
    {
        try {
            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
            DB::beginTransaction();
            $order_bid_id = 0;
            if($request->task_type == 'Instant_Booking'){
                $checkiftokenExist = OrderVendor::where('web_hook_code', $web_hook_code)->first();
                $order_bid_id = !empty($checkiftokenExist) ? $checkiftokenExist->order_id : 0;
            }else{
                $checkiftokenExist = UserBidRideRequest::where('web_hook_code', $web_hook_code)->first();
                $order_bid_id = !empty($checkiftokenExist) ? $checkiftokenExist->id : 0;
            }

            $getAdditionalPreference = getAdditionalPreference(['bid_expire_time_limit_seconds']);
            $expiryseconds = ($getAdditionalPreference['bid_expire_time_limit_seconds'] > 0) ? $getAdditionalPreference['bid_expire_time_limit_seconds'] : 30;

            if($order_bid_id > 0){
                $ifbidexists = PickDropDriverBid::where('order_bid_id', $order_bid_id)->where('driver_id', $request->driver_id)->count();
                if($ifbidexists == 0){
                    $PickDropDriverBid = [
                        'order_bid_id'                    => $order_bid_id,
                        'status'                          => 0,
                        'tasks'                           => isset($request->tasks) ? json_encode($request->tasks) : '',
                        'driver_id'                       => $request->driver_id,
                        'driver_name'                     => $request->driver_name,
                        'driver_image'                    => $request->driver_image,
                        'bid_price'                       => isset($request->bid_price) ? $request->bid_price : 0,
                        'task_type'                       => $request->task_type,
                        'expired_at'                      => Carbon::now()->addSeconds($expiryseconds)->format('Y-m-d H:i:s')
                    ];

                    $PickDropDriverBid = PickDropDriverBid::create($PickDropDriverBid);

                    //-------------driver bid received notification
                    $title     =  __('Driver\'s Bid Request');
                    $body      = "";
                    $devices   = UserDevice::whereNotNull('device_token')->where('user_id', $checkiftokenExist->user_id)->pluck('device_token');
                    $data      = [
                        "registration_ids" => $devices,
                        "notification" => [
                            'title'              => $title,
                            'body'               => $body,
                            'sound'              => "default",
                            "icon"               => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                            'click_action'       => '',
                            "android_channel_id" => "sound-channel-id"
                        ],
                        "data" => [
                            'title' => $title,
                            'body'  => $body,
                            'data'  => '',
                            'type'  => ""
                        ],
                        "priority" => "high"
                    ];

                    $result=sendFcmCurlRequest($data);

                    DB::commit();
                    return $this->successResponse($PickDropDriverBid, __('Request Placed, You will be notified once the customer respond.'), 200);
                }else{
                    return $this->successResponse([], __('Duplicate Entry, Request has already been placed. You will be notified once the customer respond.'), 200);
                }

            }else{
                DB::rollback();
                $message = "Invalid Token";
                return $this->errorResponse($message, 400);
               }

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());

        }
    }

    /******************    ---- pickup delivery Driver Bid/pricing status -----   ******************/
    public function dispatchDriverBidStatus(Request $request, $domain = '', $web_hook_code)
    {
        try {
            $order_bid_id = 0;
            if($request->task_type == 'Instant_Booking'){
                $checkiftokenExist = OrderVendor::where('web_hook_code', $web_hook_code)->first();
                $order_bid_id = !empty($checkiftokenExist) ? $checkiftokenExist->order_id : 0;
            }else{
                $checkiftokenExist = UserBidRideRequest::where('web_hook_code', $web_hook_code)->first();
                $order_bid_id = !empty($checkiftokenExist) ? $checkiftokenExist->id : 0;
            }

            if($order_bid_id > 0){

                $noofbids          = PickDropDriverBid::where('driver_id', '=', $request->driver_id)->where('order_bid_id', $order_bid_id)->orderBy('created_at', 'DESC')->count();
                $PickDropDriverBid = PickDropDriverBid::where('driver_id', '=', $request->driver_id)->where('order_bid_id', $order_bid_id)->orderBy('created_at', 'DESC')->first();

                $statusText = '';
                if(!empty($PickDropDriverBid)){
                    if($PickDropDriverBid->status == 0){
                        $statusText = "Pending";
                    }
                    if($PickDropDriverBid->status == 1){
                        $statusText = "Accepted";
                    }
                    if($PickDropDriverBid->status == 2){
                        $statusText = "Declined";
                    }
                }
                return $this->successResponse(['noofbid'=> $noofbids, 'lastBidStatus' => $statusText], 200);

            }else{
                $message = "Invalid Token";
                return $this->errorResponse($message, 400);
               }

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());

        }
    }
    /******************    ---- cancel order vendor product  -----   ******************/
    public function cancelVendorOrderProduct($product_dispatch_route_id){
        $OrderProductDispatchRoute = OrderProductDispatchRoute::find($product_dispatch_route_id);

        if($OrderProductDispatchRoute ){

            $order = Order::with(array(
                'vendors' => function ($query) use ($OrderProductDispatchRoute) {
                    $query->where('id', $OrderProductDispatchRoute->order_vendor_id);
                }
            ))->find($OrderProductDispatchRoute->order_id);

            $return_response =  $this->GetVendorReturnAmount([], $order);
           // pr(  $return_response);
            //return amount to user wallet
            if ($return_response['vendor_return_amount'] > 0) {
               $OrderProduct = OrderProduct::find($OrderProductDispatchRoute->order_vendor_product_id);
                if($OrderProduct && ($OrderProduct->price > 0)){
                    $return_amount = ($return_response['vendor_return_amount'] <=  $OrderProduct->price) ? $return_response['vendor_return_amount'] : $OrderProduct->price ;
                    $user = User::find($currentOrderStatus->user_id);
                    $wallet = $user->wallet;
                    $credit_amount = $return_response['vendor_return_amount']; //$currentOrderStatus->payable_amount;
                    $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $currentOrderStatus->orderDetail->order_number . ' (' . $currentOrderStatus->vendor->name . ')']);
                }
            }

            // diarise loyalty in order table
            // $order->loyalty_points_used    =  $order->loyalty_points_used - $return_response['vendor_loyalty_points'];
            // $order->loyalty_amount_saved   =  $order->loyalty_amount_saved - $return_response['vendor_loyalty_amount'];
            // $order->loyalty_points_earned  =  $order->loyalty_points_earned - $return_response['vendor_loyalty_points_earned'];
            // $order->save();

        }
    }

    /******************    ---- cancel order vendor product  -----   ******************/
    public function cancelOrderByDriver($order_vendor){
        if($order_vendor ){
            $order = Order::with(array(
                'vendors' => function ($query) use ($order_vendor) {
                $query->where('vendor_id', $order_vendor->vendor_id);
                }
                ))->find($order_vendor->order_id);
                $return_response =  $this->GetVendorReturnAmount([], $order);
                //return amount to user wallet
                if ($return_response['vendor_return_amount'] > 0) {
                    $user = User::find($order_vendor->user_id);
                    $wallet = $user->wallet;
                    $credit_amount = $return_response['vendor_return_amount'];//$currentOrderStatus->payable_amount;
                    $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $order_vendor->orderDetail->order_number . ' (' . $order_vendor->vendor->name . ')']);
                    $this->sendWalletNotification($user->id,  $order_vendor->orderDetail->order_number);
                }
        }
    }

}
