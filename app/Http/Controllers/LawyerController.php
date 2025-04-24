<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusTime;
use App\Models\ClientDocument;
use App\Models\ClientPreference;
use App\Models\LawyerDocument;
use App\Models\NotificationTemplate;
use App\Models\Order as ModelsOrder;
use App\Models\OrderVendor;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserDocument;
use App\Models\UserRefferal;
use App\Models\VendorDocument;
use App\Models\VendorOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class LawyerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(){
    $role = Role::findById(7);
    $users = $role->users()->orderBy('id', 'desc')->get();
        return view('backend.lawyer.index',compact('users'));
   }

   public function toggleStatus($domain, $userId)
   {
        $user = User::findOrFail($userId);
        
        $user->status = !$user->status;
        $user->save();
    
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
   }
   public function reassignLawyer(Request $request)
   {
        $order = ModelsOrder::find($request->orderId);
        
        if ($order) {
            $order->update(['lawyer_id' => null]);
            return response()->json([
                'success' => true,
                'message' => 'order rejected successfully'
            ]);
        }
    }
    public function orderRejectByLawyer(Request $request)
   {
        $order = ModelsOrder::find($request->orderId);
            if ($order) {
            $order->ordervendor->update([
                'order_status_option_id' => 3
            ]);
            return response()->json([
                'success' => true,
                'message' => 'order rejected successfully'
            ]);
        }
    }
   public function assignLawyer($domain, $lawyerId, $orderId)
    {
        $order = ModelsOrder::find($orderId);
        if ($order) {
            if (in_array($order->ordervendor->order_status_option_id, [2,3, 4, 5, 6])) {
                return response()->json(['message' => 'Lawyer already accepted this order, you can’t reassign it.']);
            }
            
            $order->update(['lawyer_id' => $lawyerId]);
            // $order->ordervendor->update(['order_status_option_id' => 9 ]);
                
            //for notification to lawyer 
            $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', [$lawyerId])->where('is_vendor_app', 1)->pluck('device_token')->toArray();
            Log::info(['devices' => $devices]);
            // push notification
            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from')->first();
            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                $notification_content = NotificationTemplate::where('id', 4)->first();
                if ($notification_content) {
                    $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                    $redirect_URL['type'] = 4;
                    $data = [
                        "registration_ids" => $devices,
                        "notification" => [
                            'title' => $notification_content->subject,
                            'body'  => $body_content,
                            'sound' => "default",
                            "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                            'click_action' => '',
                            "android_channel_id" => "default-channel-id",
                            "redirect_type" => $redirect_URL['type']
                        ],
                        "data" => [
                            'title' => $notification_content->subject,
                            'body'  => $body_content,
                            "type" => "order_status_change",
                            "order_id" => $order->order_number,
                            "vendor_id" => $vendorId ?? '',
                            "order_status" => 3,
                            "redirect_type" => $redirect_URL['type']
                        ],
                        "priority" => "high"
                    ];
                    Log::info(['data' => $data]);
                    sendFcmCurlRequest($data, '', 1);
                }
            }
             //for notification to client 
             $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', [$order->user_id])->where('is_vendor_app', 0)->pluck('device_token')->toArray();
                  
             if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                 $notification_content = NotificationTemplate::where('id', 26)->first();
                 if ($notification_content) {
                     $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                     $redirect_URL['type'] = 4;
                     $data = [
                         "registration_ids" => $devices,
                         "notification" => [
                             'title' => $notification_content->subject,
                             'body'  => $body_content,
                             'sound' => "default",
                             "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                             'click_action' => '',
                             "android_channel_id" => "default-channel-id",
                             "redirect_type" => $redirect_URL['type']
                         ],
                         "data" => [
                             'title' => $notification_content->subject,
                             'body'  => $body_content,
                             "type" => "order_status_change",
                             "order_id" => $order->order_number,
                             "vendor_id" => $vendorId ?? '',
                             "order_status" => 3,
                             "redirect_type" => $redirect_URL['type']
                         ],
                         "priority" => "high"
                     ];
                     Log::info(['data' => $data]);
                     sendFcmCurlRequest($data);
                 }
             }  
            return response()->json(['message' => 'Lawyer assigned successfully']);
        }
        return response()->json(['message' => 'Order not found'], 404);
     
    }
    // public function generateCode($domain,$id){
    //     $user = User::findOrFail($id); 
    //     $random_string = substr(md5(microtime()), 0, 6);
    //     while(\DB::table('users')->where('refferal_code', $random_string)->exists()){
    //         $random_string = substr(md5(microtime()), 0, 6);
    //     }
    //      $user->update(['refferal_code' => $random_string]);
    //      UserRefferal::create(['refferal_code'=>$random_string ,'user_id' => $id]);
    //      return back()->with('success', 'Referral Code Generated Successfully');
        
    // }
    public function  documentStatus(Request $request){
        $request->validate([
            'order_id' => 'required',
            'status' => 'required|in:1,2'
        ]);
        if ($request->status == 2) {
                LawyerDocument::where('order_id', $request->order_id)->whereNull('reject_reason')
                    ->update(['reject_reason' => $request->reject_reason,
                'status' => $request->status]);
                OrderVendor::where('order_id',$request->order_id)->update(['order_status_option_id' => 4]);
                $lawyerData= LawyerDocument::where('order_id', $request->order_id)->first();
                //for notification  
                $order= Order::where('id',$request->order_id)->first();
                $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', [$lawyerData->lawyer_id])->where('is_vendor_app', 1)->pluck('device_token')->toArray();
                Log::info(['devices' => $devices]);
                // push notification
                $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from')->first();
                if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                    $notification_content = NotificationTemplate::where('id', 23)->first();
                    if ($notification_content) {
                        $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                        $redirect_URL['type'] = 4;
                        $data = [
                            "registration_ids" => $devices,
                            "notification" => [
                                'title' => $notification_content->subject,
                                'body'  => $body_content,
                                'sound' => "default",
                                "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                                'click_action' => '',
                                "android_channel_id" => "default-channel-id",
                                "redirect_type" => $redirect_URL['type']
                            ],
                            "data" => [
                                'title' => $notification_content->subject,
                                'body'  => $body_content,
                                "type" => "order_status_change",
                                "order_id" => $order->order_number,
                                "vendor_id" => $vendorId ?? '',
                                "order_status" => 3,
                                "redirect_type" => $redirect_URL['type']
                            ],
                            "priority" => "high"
                        ];
                        Log::info(['data' => $data]);
                        sendFcmCurlRequest($data, '', 1);
                    }
                }
                return response()->json(['message'=>'Lawyer document rejected successfully']);  
        }
        if($request->status ==1){
            OrderVendor::where('order_id',$request->order_id)->update(['order_status_option_id' => 6]);
            $ordervendor = OrderVendor::where('order_id', $request->order_id)->first();
            LawyerDocument::where('order_id', $request->order_id)->where('status', 0)
            ->update(['status' => $request->status]);
            $lawyerData= LawyerDocument::where('order_id', $request->order_id)->first();
            VendorOrderStatus::create([
                'order_id' => $request->order_id,
                'order_status_option_id' => 6,
                'order_vendor_id' =>   $ordervendor->id,
                'vendor_id' => 1
            ]);
              //for notification  
              $order= Order::where('id',$request->order_id)->first();
              $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', [$lawyerData->lawyer_id])->where('is_vendor_app', 1)->pluck('device_token')->toArray();
             
              // push notification
              $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from')->first();
              if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                  $notification_content = NotificationTemplate::where('id', 22)->first();
                  if ($notification_content) {
                      $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                      $redirect_URL['type'] = 4;
                      $data = [
                          "registration_ids" => $devices,
                          "notification" => [
                              'title' => $notification_content->subject,
                              'body'  => $body_content,
                              'sound' => "default",
                              "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                              'click_action' => '',
                              "android_channel_id" => "default-channel-id",
                              "redirect_type" => $redirect_URL['type']
                          ],
                          "data" => [
                              'title' => $notification_content->subject,
                              'body'  => $body_content,
                              "type" => "order_status_change",
                              "order_id" => $order->order_number,
                              "vendor_id" => $vendorId ?? '',
                              "order_status" => 3,
                              "redirect_type" => $redirect_URL['type']
                          ],
                          "priority" => "high"
                      ];
                      sendFcmCurlRequest($data, '', 1);
                  }
                  //for session complete notification to lawyer
                  $notification_content = NotificationTemplate::where('id', 24)->first();
                  if ($notification_content) {
                      $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                      $redirect_URL['type'] = 4;
                      $data = [
                          "registration_ids" => $devices,
                          "notification" => [
                              'title' => $notification_content->subject,
                              'body'  => $body_content,
                              'sound' => "default",
                              "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                              'click_action' => '',
                              "android_channel_id" => "default-channel-id",
                              "redirect_type" => $redirect_URL['type']
                          ],
                          "data" => [
                              'title' => $notification_content->subject,
                              'body'  => $body_content,
                              "type" => "order_status_change",
                              "order_id" => $order->order_number,
                              "vendor_id" => $vendorId ?? '',
                              "order_status" => 3,
                              "redirect_type" => $redirect_URL['type']
                          ],
                          "priority" => "high"
                      ];
                      sendFcmCurlRequest($data, '', 1);
                  }
              }

              //for session complete notification to client 
              $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', [$order->user_id])->where('is_vendor_app', 0)->pluck('device_token')->toArray();
              if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                $notification_content = NotificationTemplate::where('id', 24)->first();
                if ($notification_content) {
                    $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                    $redirect_URL['type'] = 4;
                    $data = [
                        "registration_ids" => $devices,
                        "notification" => [
                            'title' => $notification_content->subject,
                            'body'  => $body_content,
                            'sound' => "default",
                            "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                            'click_action' => '',
                            "android_channel_id" => "default-channel-id",
                            "redirect_type" => $redirect_URL['type']
                        ],
                        "data" => [
                            'title' => $notification_content->subject,
                            'body'  => $body_content,
                            "type" => "order_status_change",
                            "order_id" => $order->order_number,
                            "vendor_id" => $vendorId ?? '',
                            "order_status" => 3,
                            "redirect_type" => $redirect_URL['type']
                        ],
                        "priority" => "high"
                    ];
                    sendFcmCurlRequest($data);
                }
            }
            return response()->json(['message'=>'Your order completed successfully']);
        }
    }
    public function documentReviewdByUser(Request $request)
    {
        $lawyer_document = LawyerDocument::where('order_id',$request->order_id);
        if($lawyer_document){
            if($request->status == 1){
                $lawyer_document->status=1;
                $lawyer_document->ordervendor->save();
                return  response()->json(['status',200, 'message' => 'document accepted successfully']);
            }elseif($request->status == 2){
                $lawyer_document->status =2;
                $lawyer_document->ordervendor->save();
                return  response()->json(['status',200, 'message' => 'document rejected successfully']);
            }else{
                return  response(['status',400, 'message' => 'Invalid Request']);
            }
        }
        return response()->json(['status' => 404, 'message' => 'No document present with respect to this order.']);
    }
    public function upload(Request $request)
    {
        $request->validate([
            'documents' => 'required|array', // Ensure it's an array
            'documents.*' => 'required|file|mimes:pdf,jpg,png|max:2048', // 2MB max size
            'order_id' => 'required|integer',
            'order_vendor_id' => 'required|integer',
        ]);
        $uploadedFiles = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = Storage::disk('s3')->put('lawyerDocument', $file, 'public');
                $uploadedFiles[] = [
                    'lawyer_id' =>auth()->id(),
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'order_id' => $request->order_id,
                    'status' => 0,
                ];
            }

            LawyerDocument::insert($uploadedFiles);
        }
        OrderVendor::where('id',$request->order_vendor_id)->update(['order_status_option_id' => 5]);
        OrderStatusTime::create([
            'order_id' => $request->order_id,
            'order_status' => 5,
        ]);
        VendorOrderStatus::firstOrCreate([
            'order_id' => $request->order_id,
            'order_status_option_id' => 5,
            'order_vendor_id' => $request->order_vendor_id,
            'vendor_id' => 1
        ]);
        return response()->json([
            'status' => 200,
            'message' => 'Documents uploaded successfully!',
            'files' => $uploadedFiles
        ]);
    }
   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.lawyer.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:3',
            'last_name' => 'required|string|max:255|min:3',
            'mobile' => 'required|string|max:15||min:6',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'image' => 'required|image|mimes:jpg,jpeg,png',
            'document' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);
        
        

        $user= User::create([
            'image' =>  Storage::disk('s3')->put('/user/document',  $request->file('image'), 'public'),
            'name' => $request->name,
            'last_name' => $request->last_name,
            'phone_number' => $request->mobile,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'is_admin' => 1,
            'enrollment_no' => rand(100000, 999999),
            'document' =>  Storage::disk('s3')->put('/user/document',  $request->file('document'), 'public'),

        ]);
        $user->assignRole(7);
    
        return redirect()->route('lawyer.index')->with('success', 'Lawyer created successfully.');
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Lawyer not found.');
        }
        $user->forceDelete(); 
        return redirect()->back()->with('success', 'Lawyer deleted successfully.');
    }

    public function uploadClientDocuments(Request $request)
    { 
        $request->validate([
            'documents' => 'required|file|mimes:pdf', 
        ]);
        $file = $request->file('documents');
        $fileName = $file->getClientOriginalName();
            $path = Storage::disk('s3')->put('clientDocument', $request->documents, 'public');
            $url = Storage::disk('s3')->url($path);
            // Save file path in the database
            ClientDocument::create([
                'cart_id' => @$request->cart_id,
                'client_id' => Auth::user()->id,
                'file_path' => $path,
                'product_id' => $request->product_id,
                'file_name' => $fileName,
            ]);
    
        return response()->json([
            'message' => 'Documents uploaded successfully',
            'paths' => $url,
        ]);
    }
    public function viewClientDocument(Request $request)
    {
        $id = $request->id;
        $document = ClientDocument::where('order_id',$id)->first();
        $fileContent = Storage::disk('s3')->get($document->file_path);
        $mimeType = Storage::disk('s3')->mimeType($document->file_path);
    
        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($document->file_path) . '"');
       
    }
    public function viewLawyerDocument(Request $request)
    {
        $id = $request->id;
        $document = LawyerDocument::where('order_id',$id)->first();
        $fileContent = Storage::disk('s3')->get($document->file_path);
        $mimeType = Storage::disk('s3')->mimeType($document->file_path);
    
        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($document->file_path) . '"');
       
    }

    public function downloadClientDocument(Request $request)
    {
        if($request->cart_id){
            $document = ClientDocument::where('cart_id',$request->cart_id)->where('id',$request->client_document_id)->first();
            return Storage::disk('s3')->download($document->file_path);
        }
        $id = $request->id;
        $document = ClientDocument::where('order_id',$id)->where('id',$request->client_document_id)->first();
        return Storage::disk('s3')->download($document->file_path);
    }
    public function downloadLawyerDocument(Request $request)
    {
        $document = LawyerDocument::where('order_id',$request->id)->where('id',$request->lawyer_document_id)->first();
        return Storage::disk('s3')->download($document->file_path);
       
    }
    public function uploadLawyerDocuments(Request $request)
    {
        $request->validate([
            'documents' => 'required|file|mimes:pdf',
            'order_id' => 'required'
        ]);
        $file = $request->file('documents');
        $fileName = $file->getClientOriginalName();
        $path = Storage::disk('s3')->put('lawyerDocument', $request->documents,'public');
        LawyerDocument::create([
            'lawyer_id' => Auth()->user()->id,
            'file_path' => $path,
            'order_id' => $request->order_id,
            'status' => 0,
            'file_name' => $fileName,
        ]);
        //for notification 
        $order = Order::where('id', $request->order_id)->first();
        $devices = UserDevice::whereNotNull('device_token')
            ->whereIn('user_id', [$order->user_id]) // Ensure user_id is in an array for whereIn
            ->pluck('device_token')
            ->toArray();

        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            $notification_content = NotificationTemplate::where('id', 21)->first();
            Log::info(['notification_content' => $notification_content]);
            if ($notification_content) {
                $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                $redirect_URL['type'] = 4;
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "default",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => '',
                        "android_channel_id" => "default-channel-id",
                        "redirect_type" => $redirect_URL['type']
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        "type" => "order_status_change",
                        "order_id" => $order->id,
                        "vendor_id" => $orderData->ordervendor->vendor_id ?? '',
                        "redirect_type" => $redirect_URL['type']
                    ],
                    "priority" => "high"
                ];
                Log::info(['data' => $data]);
                sendFcmCurlRequest($data);
            }
        }
        $data=[];
        $lawyer_documents = LawyerDocument::where('order_id',$request->order_id)->where('status','!=',2)->get();
        $data['lawyer_documents'] =$lawyer_documents;
        return response()->json([
            'message' => 'Documents uploaded successfully',
            'data' => $data,
        ]);
    }
    public function deleteClientDocument(Request $request)
    {
        $document = ClientDocument::where('id', $request->id)->first();
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }
    public function deleteLawyerDocument(Request $request){
        $document = LawyerDocument::where('id', $request->id)->first();
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }
}
