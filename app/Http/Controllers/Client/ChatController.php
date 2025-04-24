<?php

namespace App\Http\Controllers\Client;

use DB;
use Auth;
use Session;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Http\Traits\ChatTrait;
use App\Http\Traits\GlobalFunction;
use Illuminate\Support\Facades\Http;


use App\Models\{Client, Order, UserVendor, ClientPreference, LoyaltyCard,OrderProductRating, Product, User, Vendor};

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
        $this->middleware('auth');
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
    public function getAllChatRoom($type){
        $clientData = $this->client_data;
        $server_name = $_SERVER['SERVER_NAME'];
        $response =   Http::post($clientData->socket_url.'/api/room/fetchAllRoom', [
            //'vendor_id' => $vendor_id,
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

    }
    public function getChatRoom($vendor_id,$type){
        $clientData = $this->client_data;
        $server_name = $_SERVER['SERVER_NAME'];
        $response =   Http::post($clientData->socket_url.'/api/room/fetchRoomByVendor', [
            'vendor_id' => $vendor_id,
            'sub_domain' =>$server_name,
            'type'=>$type,
            'db_name'=>$clientData->database_name,
            'client_id'=>$clientData->id
        ]);
        // echo "<pre>";
        // print_r($response['roomData']);
        // die;
        $statusCode = $response->getStatusCode();
        if($statusCode == 200) {
            $roomData = $response['roomData'];
            return ['status' => true, 'roomData' => $roomData , 'message' => __('Room list !!!')];
        } else {

            return ['status' => false, 'message' => __('Something went wrong!!!')];
        }

    }

    public function getChatRoomForUser($order_user_id,$type){
        $clientData = $this->client_data;
        $server_name = $_SERVER['SERVER_NAME'];
        $response =   Http::post($clientData->socket_url.'/api/room/fetchRoomByUserId', [
            'order_user_id' => $order_user_id,
            'sub_domain' =>$server_name,
            'type'=>$type,
            'db_name'=>$clientData->database_name,
            'client_id'=>$clientData->id
        ]);

        // echo "<pre>";
        // print_r($response['roomData']);
        // die;
        $statusCode = $response->getStatusCode();
        if($statusCode == 200) {
            $roomData = $response['roomData'];

            return ['status' => true, 'roomData' => $roomData , 'message' => __('Room list !!!')];
        } else {

            return ['status' => false, 'message' => __('Something went wrong!!!')];
        }


    }

    public function index(Request $request){
        try {
            $user = Auth::user();
            if ($user->is_superadmin != 1) {
                abort(404);
            }

            //$roomData = $this->getAllChatRoom('vendor_to_user');
            $roomData['status'] = false;
            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return view('backend.chat.index',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>$chatroom]);
        } catch (\Throwable $th) {
            return view('backend.chat.index',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>[]]);
        }


    }

    public function userAgentChatRoom(Request $request){
        try {
            $user = Auth::user();
            if ($user->is_superadmin != 1) {
                abort(404);
            }


            //$roomData = $this->getAllChatRoom('agent_to_user');
            $roomData['status'] = false;
            $view = "AgentUserChat";
           if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return view('backend.chat.'.$view,$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>$chatroom]);
        } catch (\Throwable $th) {
            return view('backend.chat.AgentUserChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>[]]);
        }


    }

    public function userUserChat(Request $request){
        $user = Auth::user();

        $roomData['status'] = false;
        $view = "userindex";

        try {
            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return view('backend.chat.'.$view,$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>$chatroom]);
        } catch (\Throwable $th) {
            return view('backend.chat.'.$view,$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>[]]);
        }
    }

    public function VendorUserChat(Request $request){
        $user = Auth::user();
        if($user->is_superadmin == 1){
            //$roomData = $this->getAllChatRoom('vendor_to_user');
            $roomData['status'] = false;
            $view = "index";
        } else {
            $vendor_id = UserVendor::where('user_id',$user->id)->pluck('vendor_id');
            $this->client_data['vendor_id'] = $vendor_id;
            //$roomData = $this->getChatRoom($vendor_id,'vendor_to_user');
            $roomData['status'] = false;
            $view = "VendorUserChat";
        }
        try {

            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return view('backend.chat.'.$view,$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>$chatroom]);
        } catch (\Throwable $th) {
            return view('backend.chat.'.$view,$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>[]]);
        }


    }


    public function UserVendorChat(Request $request){
        try {
            $user = Auth::user();

            //$roomData = $this->getChatRoomForUser($user->id,'vendor_to_user');
            $roomData['status'] = false;
            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return view('backend.chat.UserVenorChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>$chatroom]);
        } catch (\Throwable $th) {
            return view('backend.chat.UserVenorChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>[]]);
        }


    }


    /**
     * start Chat.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function startChat(Request $request)
    {
        //try {
            $data = $request->all();
            $vendor_id = $data['vendor_id'];
            $vendor_order_id = $data['order_vendor_id'] ?? null;
            $order_id = $data['order_id'] ?? null;
            $server_name = $_SERVER['SERVER_NAME'];
            $product_id = $data['product_id'] ?? null;
            $socket_url = $this->client_data->socket_url;
            $c_type = $data['type'] ?? null;
            $p2p_id = null;
            $vendor_name = null;
            $product_name = null;
            $product_price = null;
            // dd(is_null($order_id));
            // check order_vendor_id and order_id is empty then it is called for p2p chat
            if( $c_type == 'user_to_user' ) {
                $room_name = $room_id = 'p2p-productId-'.$product_id.'-vendorId-'.$vendor_id.'-currentUser-'.Auth::id();
                $orderby_user_id = Auth::id();
                $p2p_id = $vendor_id;
                $vendor = User::where('id', $vendor_id)->first();
                $vendor_name = $vendor->name;
                $product = Product::with('variant')->where('id', $product_id)->first();
                $product_name = $product->title ?? '';
                $product_price = $product->variant[0]->price ?? 0.00;
            }
            else {

                $order = $this->OrderVendorDetail($request);
                if(@$order){
                    $room_id = $order->order_number;
                    $room_name = 'OrderNo-'.$order->order_number.'-orderId-'.$order->id.'-oderVendor-'.$vendor_id;
                    $orderby_user_id = $order->user_id;

                } else {
                    return response()->json(['status' => false, 'message' => __('Something went wrong!!!')]);
                }
            }
            $request_data = [
                'room_id' => $room_id,
                'room_name' => $room_name,
                'order_vendor_id'=> $vendor_order_id,
                'order_id'=>$order_id,
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
                'product_price' => $product_price
            ];

            $response =   Http::post($socket_url.'/api/room/createRoom', $request_data );
            $statusCode = $response->getStatusCode();
            if($statusCode == 200) {
                $roomData = $response['roomData'];
                // dd($roomData);
                return response()->json(['status' => true, 'roomData' => $roomData , 'message' => __('Room created successfully !!!')]);
            } else {
               
                return response()->json(['status' => false, 'message' => __('Something went wrong!!!')]);
            }
        // } catch (\Throwable $th) {
        //     return response()->json(['status' => false, 'message' => __('Something went wrong!!!')]);
        // }


    }

    public function fetchOrderDetail(Request $request){
        try {
            if($request->product_id != 'undefined' && $request->product_id != ''){
                $orderData = $this->ProductDetail($request);
            }else{
                $orderData = $this->OrderVendorDetail($request);
            }
            return response()->json(['status' => true, 'orderData' => $orderData , 'message' => __('Data fetched !!!')]);

            //code...
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'orderData' => [] , 'message' => __('No Data found !!!')]);
        }

    }

    public function sendNotificationToUser(Request $request){
        try {
            $notiFY = $this->sendNotification($request);
            return response()->json([ 'notiFY'=>$notiFY , 'status' => true, 'message' => __('sent!!!')]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'notiFY' => [] , 'message' => __('No Data found !!!')]);
        }

    }
}

