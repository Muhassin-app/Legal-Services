<?php

namespace App\Http\Controllers\Front;

use DB;
use Config;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Requests\Web\OrderProductRatingRequest;
use App\Http\Requests\Web\OrderProductReturnRequest;
use App\Models\{Client, ClientPreference, EmailTemplate, ExchangeReason, NotificationTemplate, Order, OrderProductRating, VendorOrderStatus, OrderProduct, OrderProductRatingFile, ReturnReason, OrderReturnRequest, OrderReturnRequestFile, OrderVendor, OrderVendorProduct, Product, ProductVariantSet, User, UserAddress, UserDevice, UserVendor, OrderCancelRequest};

use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Session;
use App\Models\Client as CP;
use App\Models\Transaction;
use App\Models\AutoRejectOrderCron;

use App\Http\Traits\{OrderTrait, ReturnExchangeTrait};
use App\Models\{LoyaltyCard, ClientCurrency, VendorOrderCancelReturnPayment};

class ReturnOrderController extends FrontController
{

    use ApiResponser,OrderTrait, ReturnExchangeTrait;
    /**
     * order details in modal
     */
    public function getOrderDatainModel(Request $request)
    {
        try {
            $order_details = Order::with([
                'vendors.products.productReturn', 'products.productRating', 'user', 'address',
                'vendors' => function ($qw) use ($request) {
                    $qw->where('vendor_id', $request->vendor_id)->where('order_id', $request->id);
                }, 'vendors.products' => function ($qw) use ($request) {
                    $qw->with(['pvariant.product', 'pvariant.media.pimage.image']);
                    $qw->where('vendor_id', $request->vendor_id)->where('order_id', $request->id);
                }, 'vendors.products.pvariant.media.pimage.image',
                'products' => function ($qw) use ($request) {
                    $qw->where('vendor_id', $request->vendor_id)->where('order_id', $request->id);
                }
            ])->whereHas('vendors', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id)->where('order_id', $request->id);
            })
                ->where('orders.user_id', Auth::user()->id)->where('orders.id', $request->id)->orderBy('orders.id', 'DESC')->first();

            if (isset($order_details)) {

                foreach($order_details->vendors as $vendor){
                    foreach($vendor->products as $key=>$products){
                        if((@$products->pvariant->product->returnable == 1 && @$products->pvariant->product->return_days && $this->checkOrderDaysForReturn($vendor, $products->pvariant->product->return_days)) ){
                            continue;
                        }else{
                            $vendor->products->forget($key);
                        }
                    }
                }

                if ($request->ajax()) {
                    return \Response::json(\View::make('frontend.modals.return-product-order', array('order' => $order_details))->render());
                }
            }
            return $this->errorResponse('Invalid order', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function getReplaceOrderDatailModel(Request $request)
    {
        try {
            $order_details = Order::with([
                'vendors.products.productReturn', 'products.productRating', 'user', 'address',
                'vendors' => function ($qw) use ($request) {
                    $qw->where('vendor_id', $request->vendor_id)->where('order_id', $request->id);
                }, 'vendors.products' => function ($qw) use ($request) {
                    $qw->with(['pvariant.product', 'pvariant.media.pimage.image']);
                    $qw->where('vendor_id', $request->vendor_id)->where('order_id', $request->id);
                }, 
                'products' => function ($qw) use ($request) {
                    $qw->where('vendor_id', $request->vendor_id)->where('order_id', $request->id);
                }
            ])->whereHas('vendors', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id)->where('order_id', $request->id);
            })
                ->where('orders.user_id', Auth::user()->id)->where('orders.id', $request->id)->orderBy('orders.id', 'DESC')->first();

            // dd($order_details->vendors[0]->products);
            if (isset($order_details)) {
                
                    foreach($order_details->vendors as $vendor){
                        foreach($vendor->products as $key=>$products){
                            if((@$products->pvariant->product->replaceable == 1 && @$products->pvariant->product->return_days && $this->checkOrderDaysForReturn($vendor, $products->pvariant->product->return_days)) ){
                                continue;
                            }else{
                                $vendor->products->forget($key);
                            }
                        }
                    }
                

                if ($request->ajax()) {
                    return \Response::json(\View::make('frontend.modals.replace-product-order', array('order' => $order_details))->render());
                }
            }
            return $this->errorResponse('Invalid order', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    /**
     * order details in for return order
     */
    public function getReturnProducts(Request $request, $domain = '')
    {
        try {

            $langId = Session::get('customerLanguage');
            $navCategories = $this->categoryNav($langId);
            $reasons = ReturnReason::where('status', 'Active')->where('type', 1)->orderBy('order', 'asc')->get();
            $order_details = Order::with([
                'vendors.products' => function ($q1) use ($request) {
                    $q1->where('id', $request->return_ids);
                }, 'vendors.products.media.image', 'vendors.products.pvariant.media.pimage.image',
                'products' => function ($q1) use ($request) {
                    $q1->where('id', $request->return_ids);
                }, 'products.productRating', 'user', 'address'
            ])
                ->whereHas('vendors.products', function ($q) use ($request) {
                    $q->where('id', $request->return_ids);
                })->where('orders.user_id', Auth::user()->id)->where('id', $request->order_id)->orderBy('orders.id', 'DESC')->first();

            if (isset($order_details)) {
                foreach ($order_details->vendors as $key => $vendor) {
                    foreach ($vendor->products as $product) {
                        if ($product->pvariant->media->isNotEmpty()) {
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                        } elseif ($product->media->isNotEmpty()) {
                            $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                        } else {
                            $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                        }
                    }
                }
                return view('frontend.account.return-order')->with(['order' => $order_details, 'navCategories' => $navCategories, 'reasons' => $reasons]);
            }
            return $this->errorResponse('Invalid order', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * order details in for return order
    */
    public function getReplaceProducts(Request $request, $domain = ''){
      try {
            $langId = Session::get('customerLanguage');
            $navCategories = $this->categoryNav($langId);
            $reasons = ReturnReason::where('status','Active')->where('type', 2)->orderBy('order','asc')->get();
            $order_details = Order::with(['vendors.products' => function ($q1)use($request){
                $q1->where('id', $request->replace_id);
            }, 'vendors.products.media.image', 'vendors.products.pvariant.media.pimage.image',
            'products' => function ($q1)use($request){
                $q1->where('product_id', $request->replace_ids);
            },'products.productRating', 'user', 'address','orderVendorProduct' =>function($q1) use($request){
                $q1->where('product_id', $request->replace_ids);
            }])
            ->whereHas('vendors.products',function($q)use($request){
                $q->where('id', $request->replace_id);
            })->where('orders.user_id', Auth::user()->id)->where('id', $request->order_id)->orderBy('orders.id', 'DESC')->first();

            $vendor_id = 0;
            if(isset($order_details)){
                foreach($order_details->vendors as $key => $vendor){
                    $vendor_id = $vendor->vendor_id;
                    foreach($vendor->products as $product){
                        if(@$product->pvariant->media && $product->pvariant->media->isNotEmpty()){
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'].'74/100'.$product->pvariant->media->first()->pimage->image->path['image_path'];
                        }elseif($product->media->isNotEmpty()){
                            $product->image_url = $product->media->first()->image->path['image_fit'].'74/100'.$product->media->first()->image->path['image_path'];
                        }else{
                            $product->image_url = ($product->image) ? $product->image['image_fit'].'74/100'.$product->image['image_path'] : '';
                        }
                    }
                }
                $vendor_id = !empty($order_details->orderVendorProduct)?$order_details->orderVendorProduct->vendor_id:$vendor_id;
                $user = Auth::user();
                $p_id = $request->replace_ids;
                $product = Product::with([
                    'variant' => function ($sel) {
                        $sel->groupBy('product_id');
                    },
                    'variant.set' => function ($sel) {
                        $sel->select('product_variant_id', 'variant_option_id');
                    },
                    'variant.media.pimage.image', 'related', 'upSell', 'crossSell', 'vendor', 'media.image', 'translation' => function ($q) use ($langId) {
                        $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                        $q->where('language_id', $langId);
                    },
                    'addOn' => function ($q1) use ($langId) {
                        $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                        $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                        $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                        $q1->where('set.status', 1)->where('ast.language_id', $langId);
                    },
                    'variantSet' => function ($z) use ($langId, $p_id) {
                        $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                        $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                        $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                        $z->where('vt.language_id', $langId);
                        $z->where('product_variant_sets.product_id', $p_id);
                        $z->where('vr.status', 1);
                    },
                    'variantSet.option2' => function ($zx) use ($langId, $p_id) {
                        $zx->where('vt.language_id', $langId)
                            ->where('product_variant_sets.product_id', $p_id);
                    },
                    'addOn.setoptions' => function ($q2) use ($langId) {
                        $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                        $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                        $q2->where('apt.language_id', $langId);
                    },
                    'category.categoryDetail.allParentsAccount'
                ])->where('id', $p_id);

                $product = $product->whereHas('vendor', function ($q) use ($vendor_id) {
                    $q->where('id', $vendor_id);
                })
                    ->where('is_live', 1)
                    ->firstOrFail();
                $clientCurrency = ClientCurrency::where('currency_id', Session::get('customerCurrency'))->first();
                if ($clientCurrency) {
                    $doller_compare = $clientCurrency->doller_compare;
                } else {
                    $clientCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
                    $doller_compare = $clientCurrency->doller_compare ?? 1;
                }
                foreach ($product->variant as $key => $value) {
                    if (isset($product->variant[$key])) {
                        $product->variant[$key]->multiplier = $clientCurrency ? $clientCurrency->doller_compare : '1.00';
                    }
                }
                foreach ($product->addOn as $key => $value) {
                    foreach ($value->setoptions as $k => $v) {
                        $v->multiplier = $clientCurrency->doller_compare;
                    }
                }
                // dd($product);
                $preferences = Session::get('preferences');
                $is_available = true;
                if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)) {
                    if ($product) {
                        $productVendorId = $product->vendor_id;
                        $vendors = $this->getServiceAreaVendors();
                        if (!in_array($productVendorId, $vendors)) {
                            $is_available = false;
                        }
                    }
                }

                    $addresses = UserAddress::where('user_id', $user->id)->where('status',1)->orderBy('is_primary','Desc')->get();

                return view('frontend.account.replace-order')->with(['addresses' => $addresses, 'product' => $product,'is_available'=>$is_available,'order' => $order_details,'navCategories' => $navCategories,'reasons' => $reasons]);
            }
            return $this->errorResponse('Invalid order', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
    /**
     * return  order product
     */
    public function updateProductReturn(OrderProductReturnRequest $request)
    {
        try {
            $user = Auth::user();
            $order_deliver = 0;
            $order_details = OrderProduct::where('id', $request->order_vendor_product_id)->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id());
            })->first();

            $this->markAsReturnPending($order_details);
            $type = 1; // 1 = return
            $returns = $this->saveReturnExchangeRequest($request, $order_details, $type);
            if (@$returns) {
                $this->sendSuccessNotification($user->id, $order_details->vendor_id);
                $this->sendSuccessEmail($request);
                return $this->successResponse($returns, 'Return Submitted.');
            }

            return $this->errorResponse('Invalid order', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }


    /**
     * return  order product
     */
    public function updateProductReplace(Request $request)
    {
        try {
            DB::beginTransaction();

            $orderVendorProductOld = OrderProduct::with('addon', 'addon.option', 'variant')->where('id', $request->order_vendor_product_id)->first(); // get exchanged order product

            $orderVendorOld = OrderVendor::with('products')->where('id', $orderVendorProductOld->order_vendor_id)
                ->whereHas('products', function ($q) use ($orderVendorProductOld) {
                    $q->where('product_id', $orderVendorProductOld->product_id);
                })->first();



            if (round($this->checkreplaceProduct($request, $orderVendorProductOld))  != round($request->product_a_price)) {
                // if(round($orderVendorOld->subtotal_amount/$orderVendorOld->products[0]->quantity)  != round($request->product_a_price)){
                return $this->errorResponse('Please select product with same price', 200);
            }

            $type = 2; // type = exchange
            $this->saveReturnExchangeRequest($request, $orderVendorProductOld, $type);
            /****** create new exchange order Start ******************/
            $order =  $this->saveOrder($request, $orderVendorOld);
            $order_vender =  $this->saveOrderVendor($order, $orderVendorProductOld);

            $this->saveOrderVendorProduct($request, $order, $orderVendorProductOld, $order_vender);
            $this->saveVendorOrderStatus($order, $orderVendorProductOld, $order_vender);

            /****** start Task in dispatcher to return exchange product ********/
            $dispatch_domain = $this->getDispatchDomain();
            $this->placeReturnRequestToDispatch($order->id, $orderVendorProductOld->vendor_id, $dispatch_domain);

            /**** mark previous product as exchanged/replace pending **********/
            $this->markAsExchangePending($orderVendorProductOld);

            DB::commit();
            $this->sendSuccessSMS($request, $order);

            return $this->successResponse($order);
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), 400);
        }
    }


    
    // public function sendSuccessNotification($id, $vendorId){
    //     $super_admin = User::where('is_superadmin', 1)->pluck('id');
    //     $user_vendors = UserVendor::where('vendor_id', $vendorId)->pluck('user_id');
    //     $devices = UserDevice::whereNotNull('device_token')->where('user_id', $id)->pluck('device_token');
    //     foreach($devices as $device){
    //         $token[] = $device;
    //     }
    //     $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_vendors)->pluck('device_token');
    //     foreach($devices as $device){
    //         $token[] = $device;
    //     }
    //     $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $super_admin)->pluck('device_token');
    //     foreach($devices as $device){
    //         $token[] = $device;
    //     }
    //     //$token[] = "d4SQZU1QTMyMaENeZXL3r6:APA91bHoHsQ-rnxsFaidTq5fPse0k78qOTo7ZiPTASiH69eodqxGoMnRu2x5xnX44WfRhrVJSQg2FIjdfhwCyfpnZKL2bHb5doCiIxxpaduAUp4MUVIj8Q43SB3dvvvBkM1Qc1ThGtEM";
    //     // dd($token);

    //     //$from = env('FIREBASE_SERVER_KEY');

    //     $notification_content = NotificationTemplate::where('id', 3)->first();
    //     $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
    //     if ($notification_content && !empty($token) && !empty($client_preferences->fcm_server_key)) {

    //         $data = [
    //             "registration_ids" => $token,
    //             "notification" => [
    //                 'title' => $notification_content->label,
    //                 'body'  => $notification_content->content,
    //             ]
    //         ];

    //         sendFcmCurlRequest($data);
    //     }
    // }

    public function sendSuccessEmail($request)
    {
        if ((isset($request->auth_token)) && (!empty($request->auth_token))) {
            $user = User::where('auth_token', $request->auth_token)->first();
        } else {
            $user = Auth::user();
        }
        $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
        $message = __('An otp has been sent to your email. Please check.');
        if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
            $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
            $sendto =  $user->email;
            $client_name = 'Sales';
            $mail_from = $data->mail_from;
            try {
                $order_vendor_product = OrderVendorProduct::where('id', $request->order_vendor_product_id)->first();
                $email_template_content = '';
                $email_template = EmailTemplate::where('id', 4)->first();
                if ($email_template) {
                    //for changeing the value upto 2 decimal
                    $order_vendor_product->price = number_format((float)$order_vendor_product->price, 2, '.', '') ?? $order_vendor_product->price;

                    $email_template_content = $email_template->content;
                    $email_template_content = str_ireplace("{product_image}", $order_vendor_product->image['image_fit'] . '200/200' . $order_vendor_product->image['image_path'], $email_template_content);
                    $email_template_content = str_ireplace("{product_name}", $order_vendor_product->product->title, $email_template_content);
                    $email_template_content = str_ireplace("{price}", $order_vendor_product->price, $email_template_content);
                }
                $data = [
                    'link' => "link",
                    'email' => $sendto,
                    'mail_from' => $mail_from,
                    'client_name' => $client_name,
                    'logo' => $client->logo['original'],
                    'subject' => $email_template->subject,
                    'customer_name' => ucwords($user->name),
                    'email_template_content' => $email_template_content,
                ];
                dispatch(new \App\Jobs\SendOrderSuccessEmailJob($data))->onQueue('verify_email');
                $notified = 1;
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * vendor  details
     */
    public function getVendorOrderForCancel(Request $request)
    {

        $client_preferences = ClientPreference::first();
        $user = Auth::user();
        // For Deduction of cancelation charges for laundry
        if ($client_preferences->business_type == 'laundry') {
            $pickup_cancelling_charges  = substr($request->pickup_cancelling_charges, 1);
            $order_id                   = $request->order_id;
            $order_number               = $request->order_number;
            $pickup_order_date          = $request->pickup_order_date;
        }

        try {


            $order_vendor = OrderVendor::where('id', $request->id)->first();
            $cancellation_reason = ReturnReason::where(['status' => 'Active', 'type' => 3])->get();
           

            $orderCancellationPercentage = 0;
            if (($client_preferences->order_cancellation_time > 0)) {
                $orderData = Order::find($order_vendor->order_id);

                // get what time order placed according to current time
                $orderPlacedTime = (strtotime(now()) - strtotime($orderData->created_at)) / 60; // in minutes
                if ($orderPlacedTime > $client_preferences->order_cancellation_time) {
                    $orderCancellationPercentage = $client_preferences->cancellation_percentage;
                }
            }


            if ($client_preferences->business_type == 'laundry') {
                return \Response::json(\View::make('frontend.modals.vendor-cancel-order')->with([
                    'order_vendor' => $order_vendor,
                    'pickup_cancelling_charges' => $pickup_cancelling_charges,
                    'pickup_order_date' => $pickup_order_date,
                    'order_number'  => $order_number,
                    'order_id'  => $order_id,
                    'orderCancellationPercentage' => $orderCancellationPercentage
                ])->render());
            } else {
                if (isset($order_vendor)) {
                    if ($request->ajax()) {
                        return \Response::json(\View::make('frontend.modals.vendor-cancel-order', array('order_vendor' =>  $order_vendor, 'orderCancellationPercentage' => $orderCancellationPercentage, 'cancellation_reason' => $cancellation_reason))->render());
                    }
                }
                return \Response::json(\View::make('frontend.modals.vendor-cancel-order', array('order_vendor' =>  $order_vendor, 'orderCancellationPercentage' => $orderCancellationPercentage))->render());
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Change the status of order
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function vendorOrderForCancel(Request $request, $domain = '')
    {
        DB::beginTransaction();
        $client_preferences = ClientPreference::first();
        // try {
        $vendor_id = $request->vendor_id;
        $orderData = Order::with(array(
            'luxury_option',
            'vendors' => function ($query) use ($vendor_id) {
                $query->where('vendor_id', $vendor_id);
            }
        ))->find($request->order_id);
        $today = date('Y-m-d');
        $user = Auth::user();

        if ($client_preferences->business_type == 'laundry') {
            if ($request->pickup_order_date == $today) {
                if ($user->balanceFloat >= $request->pickup_cancelling_charges) {
                    if ($user) {
                        $wallet_amount_used = $user->balanceFloat;
                        if ($wallet_amount_used >= $request->pickup_cancelling_charges) {
                            if ($wallet_amount_used > 0) {
                                $wallet->withdrawFloat($request->pickup_cancelling_charges, ['Wallet has been <b>debited</b> for cancelling the order on pickup day under order number <b>#' . $request->order_number . '</b>']);
                            }
                        }
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => __('Insufficient wallet balance, required cancelling charges are ' . $request->pickup_cancelling_charges . '. Please recharge your wallet.')
                    ]);
                }
            }
        }


        $timezone = Auth::user()->timezone;
        $request->status_option_id = 3;
        $vendor_order_status_check = VendorOrderStatus::where('order_id', $request->order_id)->where('vendor_id', $request->vendor_id)->where('order_status_option_id', $request->status_option_id)->first();
        $currentOrderStatus = OrderVendor::where(['vendor_id' => $request->vendor_id, 'order_id' => $request->order_id])->first();

        if ($currentOrderStatus->order_status_option_id == 3 && $request->status_option_id == 3) { //$request->status_option_id == 2){
            return response()->json(['status' => 'error', 'message' => __('Order has already been rejected!!!')]);
        }
        if ($currentOrderStatus->order_status_option_id >= 2) { //$request->status_option_id == 2){
            return response()->json(['status' => 'error', 'message' => __('Order is accepted, you can not reject this order !!!')]);
        }

        // get vendor return amount from order
        $return_response =  $this->GetVendorReturnAmount($request, $orderData);

        if (!$vendor_order_status_check) {
            $vendor_order_status = new VendorOrderStatus();
            $vendor_order_status->order_id = $request->order_id;
            $vendor_order_status->vendor_id = $request->vendor_id;
            $vendor_order_status->order_vendor_id = $request->order_vendor_id;
            $vendor_order_status->order_status_option_id = $request->status_option_id;
            $vendor_order_status->save();
            if ($request->status_option_id == 2 || $request->status_option_id == 3) {
                $clientDetail = CP::on('mysql')->where(['code' => $client_preferences->client_code])->first();
                AutoRejectOrderCron::on('mysql')->where(['database_name' => $clientDetail->database_name, 'order_vendor_id' => $currentOrderStatus->id])->delete();
            }

            OrderVendor::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->update([
                'order_status_option_id' => $request->status_option_id,
                'reject_reason' => $request->reject_reason,  'cancelled_by' => Auth::id(), 'return_reason_id' => $request->return_reason_id,
            ]);


            if (!empty($currentOrderStatus->dispatch_traking_url) && ($request->status_option_id == 3)) {
                if (isset($orderData->luxury_option->title) && $orderData->luxury_option->title == "pick_drop") {
                    $new_dispatch_traking_url = str_replace('/order/', '/order-details/', $currentOrderStatus->dispatch_traking_url);
                    $tracking_response = Http::get($new_dispatch_traking_url);
                    if ($tracking_response->status() == 200) {
                        if (!empty($tracking_response['tasks'])) {
                            foreach ($tracking_response['tasks'] as $order_tasks) {
                                if ($order_tasks['task_status'] > 0 && $order_tasks['task_status'] < 5) {
                                    return response()->json(['status' => 'error', 'message' => __('Order initiated, you can not cancel this order !!!')]);
                                }
                            }
                        }
                    }
                }
                $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $currentOrderStatus->dispatch_traking_url);
                $response = Http::get($dispatch_traking_url);
            }

            // if($currentOrderStatus->payment_option_id != 1){

            // $user = User::find(Auth::id());
            // $wallet = $user->wallet;
            // $credit_amount = $currentOrderStatus->payable_amount;
            // $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #'. $currentOrderStatus->orderDetail->order_number.' ('.$currentOrderStatus->vendor->name.')']);

            // }
            if ($return_response['vendor_return_amount'] > 0) {
                $user = User::find(Auth::id());
                $wallet = $user->wallet;
                $credit_amount = $return_response['vendor_return_amount']; //$currentOrderStatus->payable_amount;
                $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $currentOrderStatus->orderDetail->order_number . ' (' . $currentOrderStatus->vendor->name . ')']);
            }
            // diarise loyalty
            $orderData->loyalty_points_used    =  $orderData->loyalty_points_used - $return_response['vendor_loyalty_points'];
            $orderData->loyalty_amount_saved   =  $orderData->loyalty_amount_saved - $return_response['vendor_loyalty_amount'];
            $orderData->loyalty_points_earned  =  $orderData->loyalty_points_earned - $return_response['vendor_loyalty_points_earned'];
            $orderData->save();
            $vendor_return_payment                          = new VendorOrderCancelReturnPayment();
            $vendor_return_payment->order_id                = $orderData->id;
            $vendor_return_payment->order_vendor_id         = $currentOrderStatus->id;
            $vendor_return_payment->wallet_amount           = $return_response['vendor_wallet_amount'];
            $vendor_return_payment->online_payment_amount   = $return_response['vendor_online_payment_amount'];
            $vendor_return_payment->loyalty_amount          = $return_response['vendor_loyalty_amount'];
            $vendor_return_payment->loyalty_points          = $return_response['vendor_loyalty_points'];
            $vendor_return_payment->loyalty_points_earned   = $return_response['vendor_loyalty_points_earned'];
            $vendor_return_payment->total_return_amount     = $return_response['vendor_return_amount'];
            $vendor_return_payment->save();

            DB::commit();
            //  $this->sendStatusChangePushNotificationCustomer([$currentOrderStatus->user_id], $orderData, $request->status_option_id);
            return response()->json([
                'status' => 'success',
                'message' => __('Order Cancelled Successfully.')
            ]);
        }
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => $e->getMessage()
        //     ]);
        // }
    }

    /**
     * Change the status of order
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function vendorOrderForCancelSingleProduct(Request $request, $domain = '')
    {
        DB::beginTransaction();
        $client_preferences = ClientPreference::first();
        try {
            $vendor_id = $request->vendor_id;
            $product_id = $request->product_id;
            $orderData = Order::with(array(
                'luxury_option',
                'vendors' => function ($query) use ($vendor_id) {
                    $query->where('vendor_id', $vendor_id);
                }
            ))->find($request->order_id);
            $today = date('Y-m-d');
            $user = Auth::user();


            $currentOrderStatus = OrderVendor::with('products')->where(['vendor_id' => $request->vendor_id, 'order_id' => $request->order_id])->first();

            $orderVendorProduct = OrderProduct::with('addon', 'addon.option', 'variant')->where('order_vendor_id', $currentOrderStatus->id)->where('product_id', $product_id)->first();
            $cancelledProductPrice = $this->checkreplaceProduct($request, $orderVendorProduct) * $orderVendorProduct->quantity;

            if ($client_preferences->business_type == 'laundry') {
                if ($request->pickup_order_date == $today) {
                    if ($user->balanceFloat >= $request->pickup_cancelling_charges) {
                        if ($user) {
                            $wallet_amount_used = $user->balanceFloat;
                            if ($wallet_amount_used >= $request->pickup_cancelling_charges) {
                                if ($wallet_amount_used > 0) {
                                    $wallet->withdrawFloat($request->pickup_cancelling_charges, ['Wallet has been <b>debited</b> for cancelling the order on pickup day under order number <b>#' . $request->order_number . '</b>']);
                                }
                            }
                        }
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => __('Insufficient wallet balance, required cancelling charges are ' . $request->pickup_cancelling_charges . '. Please recharge your wallet.')
                        ]);
                    }
                }
            }


            $timezone = Auth::user()->timezone;
            $request->status_option_id = 3;

            $vendor_order_status_check = VendorOrderStatus::where('order_id', $request->order_id)->where('vendor_id', $request->vendor_id)->where('order_status_option_id', $request->status_option_id)->first();

            if ($currentOrderStatus->order_status_option_id == 3 && $request->status_option_id == 3) { //$request->status_option_id == 2){
                return response()->json(['status' => 'error', 'message' => __('Order has already been rejected!!!')]);
            }
            if ($currentOrderStatus->order_status_option_id >= 2) { //$request->status_option_id == 2){
                return response()->json(['status' => 'error', 'message' => __('Order is accepted, you can not reject this order !!!')]);
            }

            //check if $currentOrderStatus->products more than 1
            if (count($currentOrderStatus->products) == 1) {

                // get vendor return amount from order
                $return_response =  $this->GetVendorReturnAmount($request, $orderData);

                if (!$vendor_order_status_check) {
                    $vendor_order_status = new VendorOrderStatus();
                    $vendor_order_status->order_id = $request->order_id;
                    $vendor_order_status->vendor_id = $request->vendor_id;
                    $vendor_order_status->order_vendor_id = $request->order_vendor_id;
                    // $vendor_order_status->order_vendor_product_id = $orderVendorProduct->id;
                    $vendor_order_status->order_status_option_id = $request->status_option_id;
                    $vendor_order_status->save();
                    if ($request->status_option_id == 2 || $request->status_option_id == 3) {
                        $clientDetail = CP::on('mysql')->where(['code' => $client_preferences->client_code])->first();
                        AutoRejectOrderCron::on('mysql')->where(['database_name' => $clientDetail->database_name, 'order_vendor_id' => $currentOrderStatus->id])->delete();
                    }

                    OrderVendor::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->update([
                        'order_status_option_id' => $request->status_option_id,
                        'reject_reason' => $request->reject_reason,  'cancelled_by' => Auth::id(), 'return_reason_id' => $request->return_reason_id,
                    ]);


                    if (!empty($currentOrderStatus->dispatch_traking_url) && ($request->status_option_id == 3)) {
                        if (isset($orderData->luxury_option->title) && $orderData->luxury_option->title == "pick_drop") {
                            $new_dispatch_traking_url = str_replace('/order/', '/order-details/', $currentOrderStatus->dispatch_traking_url);
                            $tracking_response = Http::get($new_dispatch_traking_url);
                            if ($tracking_response->status() == 200) {
                                if (!empty($tracking_response['tasks'])) {
                                    foreach ($tracking_response['tasks'] as $order_tasks) {
                                        if ($order_tasks['task_status'] > 0 && $order_tasks['task_status'] < 5) {
                                            return response()->json(['status' => 'error', 'message' => __('Order initiated, you can not cancel this order !!!')]);
                                        }
                                    }
                                }
                            }
                        }
                        $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $currentOrderStatus->dispatch_traking_url);
                        $response = Http::get($dispatch_traking_url);
                    }



                    //  $this->sendStatusChangePushNotificationCustomer([$currentOrderStatus->user_id], $orderData, $request->status_option_id);

                }
            } else {


                //remove product from order
                $orderProduct = OrderProduct::where('order_vendor_id', $currentOrderStatus->id)->where('product_id', $product_id)->delete();
                $return_response =  $this->GetVendorProductReturnAmount($request, $orderData, $cancelledProductPrice);

                /***************save cancel reason  start   ********************/
                \Log::info(('OrderCancelRequest'));

                $orderProductCancelReason = new OrderCancelRequest();
                $orderProductCancelReason->order_id = $request->order_id;
                $orderProductCancelReason->vendor_id = $request->vendor_id;
                $orderProductCancelReason->order_vendor_id = $request->order_vendor_id;
                // $orderProductCancelReason->order_vendor_product_id = '';
                $orderProductCancelReason->return_reason_id = $request->return_reason_id;
                $orderProductCancelReason->status = 1;
                $orderProductCancelReason->reject_reason = $request->reject_reason;
                $orderProductCancelReason->save();

                $currentOrderStatus->payable_amount = $currentOrderStatus->payable_amount - $cancelledProductPrice;
                $currentOrderStatus->subtotal_amount = $currentOrderStatus->subtotal_amount - $cancelledProductPrice;
                $currentOrderStatus->save();
                // order amount update
                $orderData->total_amount = $orderData->total_amount - $cancelledProductPrice;
                $orderData->payable_amount = $orderData->payable_amount - $cancelledProductPrice;
                $orderData->save();

                /***************cancel product from dispatcher  start  total_amount ********************/
                //code--------
                /***************cancel product from dispatcher  end   ********************/
            }
            if ($return_response['vendor_return_amount'] > 0) {
                $user = User::find(Auth::id());
                $wallet = $user->wallet;
                $credit_amount = $return_response['vendor_return_amount']; //$currentOrderStatus->payable_amount;
                $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $currentOrderStatus->orderDetail->order_number . ' (' . $currentOrderStatus->vendor->name . ')']);
                $this->sendWalletNotification($user->id, $currentOrderStatus->orderDetail->order_number);
            }
            // diarise loyalty
            $orderData->loyalty_points_used    =  $orderData->loyalty_points_used - $return_response['vendor_loyalty_points'];
            $orderData->loyalty_amount_saved   =  $orderData->loyalty_amount_saved - $return_response['vendor_loyalty_amount'];
            $orderData->loyalty_points_earned  =  $orderData->loyalty_points_earned - $return_response['vendor_loyalty_points_earned'];
            $orderData->save();
            $vendor_return_payment                          = new VendorOrderCancelReturnPayment();
            $vendor_return_payment->order_id                = $orderData->id;
            $vendor_return_payment->order_vendor_id         = $currentOrderStatus->id;
            $vendor_return_payment->wallet_amount           = $return_response['vendor_wallet_amount'];
            $vendor_return_payment->online_payment_amount   = $return_response['vendor_online_payment_amount'];
            $vendor_return_payment->loyalty_amount          = $return_response['vendor_loyalty_amount'];
            $vendor_return_payment->loyalty_points          = $return_response['vendor_loyalty_points'];
            $vendor_return_payment->loyalty_points_earned   = $return_response['vendor_loyalty_points_earned'];
            $vendor_return_payment->total_return_amount     = $return_response['vendor_return_amount'];
            $vendor_return_payment->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => __('Order Cancelled Successfully.')
            ]);




        } catch (\Exception $e) {
            DB::rollback();
            \Log::info(('$e->getMessage()'));
            \Log::info(($e->getMessage()));

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function vendorOrderForCancelReq(Request $request)
    {

        $checkReqIfExist = OrderCancelRequest::where(['order_id' => $request->order_id, 'order_vendor_product_id' => $request->order_vendor_product_id, 'order_vendor_id' => $request->order_vendor_id, 'vendor_id' =>  $request->vendor_id])->first();

        if (empty($checkReqIfExist)) {
            $reject_reason = urldecode($request->reject_reason);

            $order_cancel_request = new OrderCancelRequest();
            $order_cancel_request->order_id = $request->order_id;
            $order_cancel_request->order_vendor_product_id = $request->order_vendor_product_id;
            $order_cancel_request->order_vendor_id = $request->order_vendor_id;
            $order_cancel_request->vendor_id = $request->vendor_id;
            $order_cancel_request->reject_reason = $reject_reason;
            $order_cancel_request->return_reason_id = $request->return_reason_id;
            $order_cancel_request->status = 0;
            if ($order_cancel_request->save()) {
                return response()->json(['status' => 'success', 'message' => __('Order cancel request send successfully.')]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('Something went wrong.')]);
            }
        } elseif ($checkReqIfExist->status == 'Pending') {
            return response()->json(['status' => 'error', 'message' => __('Order cancel request already send. Please wait for admin approval.')]);
        } elseif ($checkReqIfExist->status == 'Approved') {
            return response()->json(['status' => 'error', 'message' => __('Order cancel request approved by admin.')]);
        } elseif ($checkReqIfExist->status == 'Rejected') {
            return response()->json(['status' => 'error', 'message' => __('Order cancel request rejected by admin.')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('Something went wrong. Please try again later.')]);
        }
    }

    /**
     * order rental product details in modal
     */
    public function getOrderRentalDatainModel(Request $request)
    {
        try {
            $product = OrderProduct::whereHas('order', function($q){
                $q->where('user_id', Auth::user()->id);
            })
                ->where('id', $request->order_vendor_product_id)->first();

            if (isset($product)) {

                if ($request->ajax()) {
                    $type = $request->has('type') && $request->type != null ? $request->type: '';
                    return \Response::json(\View::make('frontend.modals.return-rental-product-order', array('product' => $product,'type'=>$type))->render());
                }
            }
            return $this->errorResponse('Invalid order', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    /**
     * return rental order product
     */
    public function updateRentalProductReturn(Request $request)
    {
        try {
            $user = Auth::user();
            $order_deliver = 0;
            $order_details = OrderProduct::where('id', $request->order_vendor_product_id)->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id());
            })->first();

            $this->markAsReturnPending($order_details);
            $type = $request->has('request_type') && $request->request_type != null ? $request->request_type: 1;
            $returns = $this->saveReturnExchangeRequest($request, $order_details, $type);
            if (@$returns) {
                $this->sendSuccessNotification($user->id, $order_details->vendor_id);
                $this->sendSuccessEmail($request);
                return redirect()->back();
                // return $this->successResponse($returns, 'Return Submitted.');
            }
            return redirect()->back();
           
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}
