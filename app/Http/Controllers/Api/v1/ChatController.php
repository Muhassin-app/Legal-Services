<?php

namespace App\Http\Controllers\Api\v1;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Traits\ChatTrait;
use App\Http\Traits\GlobalFunction;
use Illuminate\Support\Facades\Http;


use App\Models\{Client, Product, UserVendor, Vendor, User, OrderVendor, OrderVendorProduct};

class ChatController extends BaseController
{
    use GlobalFunction;
    use ChatTrait;
    /**
     * Display a listing of the country resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $client_data;
    public function __construct()
    {
        
        $this->middleware(function ($request, $next) {
            $this->id = Auth::user()->id;
            $data = Client::first();
            $this->client_data =  $data;
            if ($data->socket_url == null) {
                abort(404);
            }
    
            return $next($request);
        });
    }    
    /**
     * getChatRoom
     *
     * @param  mixed $vendor_id
     * @param  mixed $type
     * @param  mixed $sub_domain
     * @return void
     */
    public function getChatRoom($vendor_id,$type,$sub_domain){
        try {
            $clientData = $this->client_data;
            $server_name = $sub_domain;
            $response =   Http::post($clientData->socket_url.'/api/room/fetchRoomByVendor', [
                'vendor_id' => $vendor_id, 
                'sub_domain' =>$server_name,
                'type'=>$type,
                'db_name'=>$clientData->database_name,
                'client_id'=>$clientData->id
            ]);
            $statusCode = $response->getStatusCode();
            if($statusCode == 200) {
                $roomData = $response['roomData'];
                return ['status' => true, 'roomData' => $roomData , 'message' => __('Room list !!!')];
            } else {
    
                return ['status' => false, 'message' => __('Something went wrong!!!')];
            }
        } catch (\Throwable $th) {
            return ['status' => false, 'message' => __('Something went wrong!!!')];
        }
        

    }
    
    /**
     * getChatRoomForUser
     *
     * @param  mixed $order_user_id
     * @param  mixed $type
     * @param  mixed $sub_domain
     * @return void
     */
    public function getChatRoomForUser($order_user_id,$type,$sub_domain){
        try {
            $clientData = $this->client_data;
            $server_name = $sub_domain;
            $response =   Http::post($clientData->socket_url.'/api/room/fetchRoomByUserId', [
                'order_user_id' => $order_user_id, 
                'sub_domain' =>$server_name,
                'type'=>$type,
                'db_name'=>$clientData->database_name,
                'client_id'=>$clientData->id
            ]);
            
            $statusCode = $response->getStatusCode();
            if($statusCode == 200) {
                $roomData = $response['roomData'];
               
                return ['status' => true, 'roomData' => $roomData , 'message' => __('Room list !!!')];
            } else {
    
                return ['status' => false, 'message' => __('Something went wrong!!!')];
            }
        
        } catch (\Throwable $th) {
            return ['status' => false, 'message' => __('Something went wrong!!!')];
        }
    }    
    /**
     * vendorUserChatRoom
     *
     * @param  mixed $request
     * @return void
     */
    public function vendorUserChatRoom(Request $request){

        try {
            $user = Auth::user();
            $data = $request->all();
            $sub_domain = $data['sub_domain'];
            $vendor_id = $user->id;
            $this->client_data['vendor_id'] = $vendor_id;
            $roomData = $this->getChatRoom($vendor_id,'vendor_to_user',$sub_domain);
            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return response()->json([ 'chatrooms'=>$chatroom , 'status' => true, 'message' => __('list fetched!!!')]);
        } catch (\Throwable $th) {
            return response()->json([ 'chatrooms'=>[] , 'status' => true, 'message' => __('list fetched!!!')]);
        }
    }

    
    /**
     * userVendorChatRoom
     *
     * @param  mixed $request
     * @return void
     */
    public function userVendorChatRoom(Request $request){
        try {
            $user = Auth::user();
            $data = $request->all();
            $sub_domain = $data['sub_domain'];
            $roomData = $this->getChatRoomForUser($user->id,'vendor_to_user',$sub_domain);
            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return response()->json([ 'chatrooms'=>$chatroom , 'status' => true, 'message' => __('list fetched!!!')]);
        } catch (\Throwable $th) {
            return response()->json([ 'chatrooms'=>[] , 'status' => true, 'message' => __('list fetched!!!')]);
        }
        

    }

    /**
     * start Chat.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function startChat(Request $request){
        try {
            $data = $request->all();
            
            $vendor_id = $data['vendor_id'];
         
            $order_number = $data['order_number'] ?? null;
            $vendor_order_id = $data['order_vendor_id'] ?? '';
           
            $order_id = $data['order_id'] ?? '';
            $isRaiseIssue = $data['isRaiseIssue'] ?? 0 ;
            $server_name = $_SERVER['SERVER_NAME'];
            $product_id = $data['product_id'] ?? null;
            $agent_db = '';
            $agent_id = '';
            $socket_url = $this->client_data->socket_url;
            $c_type = $data['type'] ?? null;
            $p2p_id = null;
            $vendor_name = null;
            $product_name = null;
            $product_price = null;
            $user_id = Auth::id();

            $product = [];
            $vendorImage = [];
           
            // dd(is_null($order_id));
            // check order_vendor_id and order_id is empty then it is called for p2p chat
            if( $c_type == 'user_to_user' ) {

           
              
                $room_name = $room_id = ($order_number != null) ? 'p2p-productId-'.$product_id.'-orderNumber-'.$order_number :'p2p-productId-'.$product_id."-userId-".$user_id;
                $orderby_user_id = Auth::id();
                $p2p_id = $vendor_id;
                $vendor = User::where('id', $vendor_id)->first();
                if(@$vendor){
                    // $user_vendor = UserVendor::where('vendor_id', $vendor_id)->first();
                    // if(@$user_vendor->user_id){
                        $vendorImage = User::where('id', $vendor->id)->first();
                    // }
                }
                $vendor_name = $vendor->name;
                $product = Product::with('variant')->where('id', $product_id)->first();
                $product_name = $product->title??'';
                $product_price = $product->variant[0]->price??0;
            }
            else {

                $order = $this->OrderVendorDetail($request);
                if(@$order){
                    $room_id = $order->order_number;
                    if(isset($data['agent_id'])) {
                        $room_name = 'OrderNo-'.$order->order_number.'-orderId-'.$order->id.'-oderVendor-'.$vendor_id.'-agentId-'.$data['agent_id'];
                        $agent_db = $data['agent_db'];
                        $agent_id = $data['agent_id'];
                    } else {

                        $room_name = 'OrderNo-'.$order->order_number.'-orderId-'.$order->id.'-oderVendor-'.$vendor_id;
                        $agent_db = '';
                        $agent_id = '';
                    }
                    $orderby_user_id = $order->user_id;

                } else {

                    return response()->json(['status' => false, 'message' => __('Something went wrong!!!')]);
                }
            }
 

            $url = $socket_url.'/api/room/createRoom';
            $params =  [
                'room_id' => $room_id,
                'room_name' => $room_name,
                'order_vendor_id'=> $vendor_order_id,
                'order_id'=>$order_number,
                'vendor_id'=>$vendor_id,
                'sub_domain' =>$server_name,
                'vendor_user_id' =>$data['user_id'],
                'order_user_id' =>$orderby_user_id,
                'type'=>$data['type'],
                'db_name'=>$this->client_data->database_name,
                'client_id'=>$this->client_data->id,
                'p2p_id'=>$p2p_id,
                'product_id'=>$product_id,
                'vendor_name' => $vendor_name,
                'product_name' => $product_name,
                'product_price' => $product_price,
                'agent_id'=>$agent_id,
                'agent_db'=>$agent_db,
                'isRaiseIssue'=> $isRaiseIssue
            ];
          

            $response =   Http::post($url,$params);


            $statusCode = $response->getStatusCode();

            if($statusCode == 200) {
                $roomData = $response['roomData'];
                return response()->json(['status' => true, 'roomData' => $roomData ,'product' => $product,'vendorImage' => $vendorImage, 'message' => __('Room created successfully !!!')]);
            } else {
              
                return response()->json(['status' => false, 'message' => __('Something went wrong!!!')]);
            }
        } catch (\Throwable $th) {
         
            return response()->json(['status' => false, 'message' => __('Something went wrong!!!')]);
        }
    }

    
    /**
     * fetchOrderDetail
     *
     * @param  mixed $request
     * @return void
     */
    public function fetchOrderDetail(Request $request){
        try {
            $vendorImage = [];
            $order_vendor = [];
            if(@$request->product_id){
                $order_vendor_id = OrderVendorProduct::select('order_vendor_id')->where('product_id', $request->product_id)->orderBy('id', 'Desc')->first();
                  
                if($order_vendor_id)
                {
                $order_vendor = OrderVendor::select('order_status_option_id', 'vendor_id', 'user_id', 'id')->where('id',  $order_vendor_id->order_vendor_id)->first();
                
                $user_vendor = UserVendor::where('vendor_id', $order_vendor->vendor_id)->first();
                $order_vendor->vendor_user_id = $user_vendor->user_id ?? 0;

                }
            }



            
            if($request->product_id != 'undefined' && $request->product_id != ''){
                $orderData = $this->ProductDetail($request);
                
                if(@$orderData){
                    $user_vendor = UserVendor::where('vendor_id', $orderData->vendor_id)->first();
                    
                    if(@$user_vendor->user_id){
                        $vendorImage = User::where('id', $user_vendor->user_id)->first();
                    }
                   
                }
            }else{
                $orderData = $this->OrderVendorDetail($request);
            }
            return response()->json(['status' => true, 'orderData' => $orderData ,'vendorData' => $vendorImage ,'order_vendor'=>  $order_vendor, 'message' => __('Data fetched !!!')]);
            
            //code...
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'orderData' => [] , 'message' => __('No Data found !!!')]);
        }
            
    }

    
    /**
     * userAgentChatRoom
     *
     * @param  mixed $request
     * @return void
     */
    public function userAgentChatRoom(Request $request){
        try {
            $user = Auth::user();
            $data = $request->all();
            $sub_domain = $data['sub_domain'];
            $roomData = $this->getChatRoomForUser($user->id,'agent_to_user',$sub_domain);
            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return response()->json([ 'chatrooms'=>$chatroom , 'status' => true, 'message' => __('list fetched!!!')]);
        } catch (\Throwable $th) {
            return response()->json([ 'chatrooms'=>[] , 'status' => true, 'message' => __('list fetched!!!')]);
        }
        

    }
    
    /**
     * sendNotificationToUser
     *
     * @param  mixed $request
     * @return void
     */
    public function sendNotificationToUser(Request $request){
        try {
            $notiFY = $this->sendNotification($request);
            return response()->json([ 'notiFY'=>$notiFY , 'status' => true, 'message' => __('sent!!!')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'notiFY' => [] , 'message' => __('No Data found !!!')]);
        }

    }

}

