<?php

namespace App\Http\Controllers\Front;
use Auth;
use Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\FrontController;
use App\Http\Traits\GlobalFunction;
use Illuminate\Support\Facades\Http;
use App\Http\Traits\ChatTrait;



use App\Models\{Client, Order, UserVendor};

class ChatController extends FrontController
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
    
   

    public function getChatRoomForUser($order_user_id,$type){
        try {
            $clientData = $this->client_data;
            $server_name = $_SERVER['SERVER_NAME'];
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

    public function index(Request $request){
        return view('frontend.chat.index',$this->client_data);

    }
    public function UserAgentChat(Request $request){
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        try {
            $user = Auth::user();
            
            //$roomData = $this->getChatRoomForUser($user->id,'agent_to_user');
            $roomData['status'] = false;
            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return view('frontend.chat.UserAgentChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>$chatroom,'navCategories' => $navCategories]);
        } catch (\Throwable $th) {
            //throw $th;
            return view('frontend.chat.UserAgentChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>[],'navCategories' => $navCategories]);
        }
        

    }


    public function UservendorChat(Request $request){
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        try {
            $user = Auth::user();
            //$roomData = $this->getChatRoomForUser($user->id,'vendor_to_user');
            $roomData['status'] = false;
            if($roomData['status']){
                $chatroom = $roomData['roomData'];
            } else {
                $chatroom = [];
            }
            return view('frontend.chat.UserVenorChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>$chatroom,
            'navCategories' => $navCategories]);
        } catch (\Throwable $th) {
            return view('frontend.chat.UserVenorChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>[],
            'navCategories' => $navCategories]);
        }
        

    }

    public function UserToUserChat(Request $request, $domain, $room_id =''){
        
        $user = Auth::user();
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        if($user->is_superadmin == 1){
            
            $roomData['status'] = false;
            $view = "index";
        } else {
            $vendor_id = UserVendor::where('user_id',$user->id)->select('vendor_id')->first();
            // dd($vendor_id);
            $this->client_data['vendor_id'] = $vendor_id->vendor_id ?? "";
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
            return view('frontend.chat.UserToUserChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>$chatroom,
            'navCategories' => $navCategories, 'room_id' => $room_id]);
            
        } catch (\Throwable $th) {
            return view('frontend.chat.UserToUserChat',$this->client_data)->with([ 'data' => $this->client_data,'chatrooms'=>[],
            'navCategories' => $navCategories, 'room_id' => $room_id]);
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
        try {
            $data = $request->all();
            $vendor_id = $data['vendor_id'];
            $vendor_order_id = $data['order_vendor_id'];
            $order_id = $data['order_id'];
            $server_name = $_SERVER['SERVER_NAME'];
            $order = $this->OrderVendorDetail($request);
            if($order){
                $socket_url = $this->client_data->socket_url;
                $room_id = $order->order_number;
                $room_name = 'OrderNo-'.$order->order_number.'-orderId-'.$order->id.'-oderVendor-'.$vendor_id;
                $order_vendor_id = $vendor_order_id;
                $order_id = $order->id;
                $vendor_id = $vendor_id;
                $orderby_user_id = $order->user_id;

                $request_data = [
                    'room_id' => $room_id, 
                    'room_name' => $room_name,
                    'order_vendor_id'=>$order_vendor_id,
                    'order_id'=>$order_id,
                    'vendor_id'=>$vendor_id,
                    'sub_domain' =>$server_name,
                    'vendor_user_id' =>$data['user_id'],
                    'order_user_id' =>$orderby_user_id,
                    'agent_id'=>$data['agent_id'],
                    'type'=>$data['type'],
                    'db_name'=>$this->client_data->database_name,
                    'client_id'=>$this->client_data->id
                ];
                $response =   Http::post($socket_url.'/api/room/createRoom', $request_data);
                $statusCode = $response->getStatusCode();
                if($statusCode == 200) {
                    $roomData = $response['roomData'];
                    return response()->json(['status' => true, 'roomData' => $roomData , 'message' => __('Room created successfully !!!')]);
                } else {
    
                    return response()->json(['status' => false, 'message' => __('Something went wrong!!!')]);
                }
            
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => __('Something went wrong!!!')]);
        }
       

        
    }

    public function fetchOrderDetail(Request $request){
        try {
            $orderData = $this->OrderVendorDetail($request);
            return response()->json(['status' => true, 'orderData' => $orderData , 'message' => __('Data fetched !!!')]);
            
            //code...
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'orderData' => [] , 'message' => __('No Data found !!!')]);
        }
            
    }



}

