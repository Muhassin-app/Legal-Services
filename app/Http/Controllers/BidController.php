<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Traits\{ApiResponser,BiddingCartTrait};
use App\Models\Bid;
use App\Models\BidProduct;
use App\Models\BidRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ClientPreference;
use App\Models\NotificationTemplate;
use App\Models\Product;
use App\Models\UserDevice;
use App\Models\UserVendor;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Str;

class BidController extends FrontController
{
    use ApiResponser,BiddingCartTrait;
    public function index(Request $request)
    {
        $user = Auth::user();
        $prescriptions = BidRequest::where('user_id', $user->id)->where('status' , '=' , 0)
        ->withCount('bidCounts')->orderBy('id', 'DESC')->get();
        return view('frontend.bidding_module.biddingRequest', compact('prescriptions'));
    }


    public function bidDetails(Request $request,$domain = '',$id=null)
    {
        $activeOrders = Bid::where('bid_req_id', $id);
        $activeOrders = $activeOrders->orderBy('id', 'DESC')->paginate(10);
        return view('frontend.bidding_module.bid-lists', compact('activeOrders'));

    }

    public function create()
    {
        $prescriptions = BidRequest::where('status' , '=' , 0)->get();
        //dd($prescriptions->toArray());
        return view('frontend.bidding_module.create', compact('prescriptions'));
    }

    public function bidAccept(Request $request,$domain ="",$id = null,$vid = null)
    {
        $accepted = Bid::with('bidRequests')->whereHas('bidRequests',function($q)use($id)
        {
           return $q->where(['id'=>$id,'user_id'=>auth()->id()]);
        })->where(['id'=> $vid])->where('status','==',0)->first();

        if($accepted)
        {
            $is_bid_enable = @getAdditionalPreference(['is_bid_enable'])['is_bid_enable']??0;
            $bid_products = BidProduct::where('bid_id', $vid)->with('product.variant')->get();
            $CartController  = new CartController();
            foreach($bid_products as $product) {
                $newRequest = new Request();
                $newRequest->merge(['product_id'=> $product->product_id, 'quantity'=>$product->quantity, 'variant_id'=>$product->product->variant[0]->id, 'vendor_id'=>$product->product->vendor_id,'bid_number'=>(($is_bid_enable)?$vid:null),'bid_discount'=>(($is_bid_enable)?$product->bids->discount:null)]);

                $data = $CartController->postAddToCart($newRequest);
            }

            //Send Notification to vendor bid accepted
            $this->sendBidPushNotificationVendors($accepted->vendor_id,$accepted->bidRequests);

            return redirect('viewcart');

        }else{
            return redirect()->back()->withError('Already Accepted Bid Or Not your bid.');
        }

    }


    public function bidReject(Request $request,$domain ="",$id = null,$vid = null)
    {
        // $bid_products = BidProduct::where('bid_id', $vid)->with('product.variant')->get();
        $accept = Bid::where('id', $vid)->update(['status'=>2]);
        return redirect()->back()->with('success','Bid updated successfuly.');
    }

    public function store(Request $request)
    {
       $user = Auth::user();
       $vendors = Vendor::where('status','1');
       if (Auth::user()->is_superadmin == 0) {
           $vendors = $vendors->whereHas('permissionToUser', function ($query) {
               $query->where('user_id', Auth::user()->id);
           });
       }
       $prod_vendor =  $vendors->first();
       if(!$prod_vendor){
            Session::flash('error', 'Somthing went wrong!');
            return $this->successResponse(__('Somthing went wrong!'),'400');
       }
       $vendor_id = $prod_vendor->id;
       
       $products    = json_decode($request->data,true);
       $discount = $products[0]['discount'];
       $prescription_id = $products[0]['prescription_id'];

       if($products){
        $total = 0;
        foreach ($products as $key => $bidTotal) {
            $total += $bidTotal['qty'] * $bidTotal['price'];
        }
        $amountPayable = $total - ($total * ($discount/ 100));
        $data = [
            'bid_req_id' => (int) $prescription_id,
            'vendor_id'    => $prod_vendor,
            'discount'     => $discount,
            'bid_total'    => $total,
            'final_amount' => $amountPayable,
            'bid_order_number'   => time()
        ];

        $vendor_bids = Bid::create($data);
        $total = 0;
        foreach ($products as $key => $data) {
            $total = $data['qty'] * $data['price'];
           $bids = BidProduct::create([
              'bid_id'       =>  $vendor_bids->id,
              'product_id'   =>  $data['id'],
              'quantity'     =>  $data['qty'],
              'price'        =>  $data['price'],
              'total'        =>  $total,
            ]);
        }

       }

    //    Session::flash('success', 'Bid Placed Successfully');
       return redirect()->back()->with('success','Bid Placed Successfully');
    }


    public function search(Request $request)
    {
        $response = [];
        $user = Auth::user();
        $keyword = $request->input('keyword');
        $language_id = Session::get('customerLanguage');
        $allowed_vendors = $this->getServiceAreaVendors();
        $vendors = Vendor::where('status','1');
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendor_ids =  $vendors->pluck('id');
        
        $response  = $this->searchProduct($language_id,$keyword,$vendor_ids);

        return $this->successResponse($response);
    }

    public function uploadPrescription(Request $request, $domain = '')
    {
        // dd($request->description);
        $user = Auth::user();
        $doc_name = 'prescription';
        $folderName = 'prescriptions';
        $description = $request->description??null;
        if ($user) {
            if ($request->hasFile($doc_name)) {
                $filePath = $folderName . '/' . Str::random(40);
                $file = $request->file($doc_name);
                $orignal_name = $request->file($doc_name)->getClientOriginalName();
                $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                $url = Storage::disk('s3')->url($file_name);
                $prescriptionData = BidRequest::Create(
                    [   'user_id' => $user->id,
                        'prescription' => $url,
                        'description'=>$description,
                        'bid_number'=>time()
                    ]
                );
            }

        }
        $this->sendBidPushNotificationVendors('',$prescriptionData);
        // $previousUrl = url('/index');
        // return redirect()->to($previousUrl.'?'. http_build_query(['success'=>'done']));
        return redirect()->back()->with(['success' => "Uploaded Successfully"]);
    }

    public function getPrescription(Request $request){
        if(!empty($request->prescriptionId) && $request->requestType == 'delete_prescription'){
            BidRequest::where('id', $request->prescriptionId)->delete();
            return response()->json(['status' => 'success', 'message' => "Prescription remove Successfully"]);
        }
        $bidPrescription = bidRequest::get()->toArray();
        return response()->json($bidPrescription);
    }

    public function sendBidPushNotificationVendors($vendorIds='',$prescriptionData)
    {
        if(empty($vendorIds)){
            $userIds = UserVendor::pluck('user_id')->toArray();
        }else{
             $userIds= $vendorIds;
        }

        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', [$userIds])->pluck('device_token')->toArray();
        if (!empty($devices)) 
        {
            $from = '';
            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();
            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                $from = $client_preferences->fcm_server_key;
            }

            $notification_content = NotificationTemplate::where('id', 11)->first();
            if ($notification_content && empty($vendorIds)) {
                $body_content = str_ireplace("{prescription}", "#" . $prescriptionData->id, $notification_content->content);
                $title = $notification_content->subject;
            }else{
                $title = "Bid accepted by User";
                $body_content = "Bid accepted";
            }
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $title,
                        'body'  => $body_content,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        //'click_action' => route('order.index'),
                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $title,
                        'body'  => $notification_content->content??$title,
                        'data' => $prescriptionData,
                        'prescription_id' => $prescriptionData->id,
                        'type' => "bid_request_created"
                    ],
                    "priority" => "high"
                ];
                if(!empty($from)){
                    // helper function
                    sendFcmCurlRequest($data);
                }
        }else{
            return true;
        }
    }

    public function sendBidPushNotificationUser($userIds)
    {
   
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', [$userIds])->pluck('device_token')->toArray();

        if (!empty($devices)) 
        {
            $from = '';
            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();
            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                $from = $client_preferences->fcm_server_key;
            }

                $title = "Vendor place a bid.";
                $body_content = "Vendor make a bid please check it.";
            
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $title,
                        'body'  => $body_content,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        //'click_action' => route('order.index'),
                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $title,
                        'body'  => $title,
                        'data' => $title,
                        'type' => "bid_request_created"
                    ],
                    "priority" => "high"
                ];
                if(!empty($from)){
                    // helper function
                    sendFcmCurlRequest($data);
                }
        }else{
            return true;
        }
    }

}
