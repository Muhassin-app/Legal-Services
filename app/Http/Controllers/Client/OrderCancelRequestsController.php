<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\AhoyController;
use DB;
use Log;
use Auth;
use Session;
use DataTables;
use Carbon\Carbon;
use GuzzleHttp\Client as GCLIENT;
use App\Models\Client as CP;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,OrderTrait};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\BaseController;
use App\Http\Controllers\DunzoController;
use App\Http\Controllers\Front\LalaMovesController;
use App\Http\Controllers\Front\QuickApiController;
use App\Http\Controllers\ShiprocketController;
use App\Models\{AutoRejectOrderCron, Order, OrderStatusOption, OrderCancelRequest, DispatcherStatusOption, VendorOrderStatus, ClientPreference, NotificationTemplate, OrderProduct, OrderVendor, UserAddress, Vendor, OrderReturnRequest, UserDevice, UserVendor, LuxuryOption, ClientCurrency, VendorOrderDispatcherStatus, Tax, Transaction, User};
use App\Http\Traits\ReturnExchangeTrait;

class OrderCancelRequestsController extends BaseController
{
    use ApiResponser,OrderTrait, ReturnExchangeTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $order_requests = OrderCancelRequest::orderBy('id', 'desc');

        $pending_requests_count = OrderCancelRequest::where('status', 0);
        if ($user->is_superadmin == 0) {
            $pending_requests_count = $pending_requests_count->whereHas('order.vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $pending_requests_count = $pending_requests_count->count();

        $approved_requests_count = OrderCancelRequest::where('status', 1);
        if ($user->is_superadmin == 0) {
            $approved_requests_count = $approved_requests_count->whereHas('order.vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $approved_requests_count = $approved_requests_count->count();

        $rejected_requests_count = OrderCancelRequest::where('status', 2);
        if ($user->is_superadmin == 0) {
            $rejected_requests_count = $rejected_requests_count->whereHas('order.vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $rejected_requests_count = $rejected_requests_count->count();
        // all vendors
        $vendors = Vendor::where('status', '!=', '2')->orderBy('id', 'desc');
        if ($user->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $vendors = $vendors->get();

        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        return view('backend.order_cancel_requests.index', compact('pending_requests_count', 'approved_requests_count', 'rejected_requests_count', 'clientCurrency', 'vendors'));
    }

    public function filter(Request $request, $domain = '')
    {
        $user = Auth::user();
        $timezone = $user->timezone ? $user->timezone : 'Asia/Kolkata';
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;

        $req = OrderCancelRequest::with(['order', 'vendor', 'order_vendor', 'updated_by_user', 'reason'])->where('status', $request->status);
        if ($user->is_superadmin == 0) {
            $req = $req->whereHas('order_vendor.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        if (!empty($request->search_keyword)) {
            $req->whereHas('order', function ($query)  use ($request) {
                $query->whereHas('address', function ($q) use ($request) {
                    $q->where('house_number', 'like', '%' . $request->search_keyword . '%')
                        ->orWhere('address', 'like', '%' . $request->search_keyword . '%')
                        ->orWhere('street', 'like', '%' . $request->search_keyword . '%')
                        ->orWhere('city', 'like', '%' . $request->search_keyword . '%')
                        ->orWhere('state', 'like', '%' . $request->search_keyword . '%')
                        ->orWhere('pincode', 'like', '%' . $request->search_keyword . '%')
                        ->orWhere('country', 'like', '%' . $request->search_keyword . '%');
                })->orWhere('order_number', 'like', '%' . $request->search_keyword . '%');
            });
        }
        //get by vendor
        if (!empty($request->get('vendor_id'))) {
            $req->where('vendor_id', $request->get('vendor_id'));
        }
        //filer bitween date
        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1])) ? $date_date_filter[1] : $date_date_filter[0];
            $from_date = $date_date_filter[0];

            $req->whereBetween('created_at', [$from_date . " 00:00:00", $to_date . " 23:59:59"]);
        }
        $req = $req->orderBy('id', 'desc');

        return Datatables::of($req)
            ->addColumn('order_number', function ($req) {
                return $req->order ? $req->order->order_number : '';
            })
            ->addColumn('show_vendor_url', function ($req) {
                if ($req->vendor_id) {
                    return route('vendor.catalogs', $req->vendor_id);
                } else {
                    return '';
                }
            })
            ->addColumn('order_detail_url', function ($req) {
                if ($req->vendor_id) {
                    return route('order.show.detail', [$req->order_id, $req->vendor_id]);
                } else {
                    return '';
                }
            })
            ->addColumn('vendor', function ($req) {
                return isset($req->order_vendor->vendor) ? $req->order_vendor->vendor->name : '';
            })
            ->addColumn('reject_reason', function ($req) {
                if (!empty($req->return_reason_id) && $req->reason->title == "Other") {
                    return $req->reject_reason;
                } elseif (!empty($req->return_reason_id) && $req->reason->title != "Other") {
                    return $req->reason->title;
                } else {
                    return $req->reject_reason;
                }
            })
            ->editColumn('updated_by', function ($req) {
                return $req->updated_by_user ? $req->updated_by_user->name : '';
            })
            ->addColumn('requested_date', function ($req) use ($timezone) {
                return dateTimeInUserTimeZone($req->created_at, $timezone);
            })
            ->addColumn('updated_date', function ($req) use ($timezone) {
                return dateTimeInUserTimeZone($req->updated_at, $timezone);
            })
            ->addColumn('action', function ($req) use ($request) {
                if ($request->status == 0) {
                    return "<div class='form-ul'>
                        <div class='inner-div d-inline-block'>
                            <a title='Approve' class='action-icon text-success complete_request_btn' href='#' data-status='1' data-id='" . $req->id . "'><i class='fa fa-check'></i></a>
                            <a title='Reject' class='action-icon text-danger ml-2 complete_request_btn' href='#' data-status='2' data-id='" . $req->id . "'><i class='fa fa-times'></i></a>
                        </div>
                    </div>";
                } else {
                    return '';
                }
            })
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $search = $request->get('search');
                    $instance->where(function ($query) use ($search) {
                        $query->where('reject_reason', 'LIKE', '%' . $search . '%')
                            ->orWhereHas('order', function ($q) use ($search) {
                                $q->where('order_number', 'LIKE', '%' . $search . '%');
                            })
                            ->orWhereHas('order_vendor.vendor', function ($q) use ($search) {
                                $q->where('name', 'LIKE', '%' . $search . '%');
                            });
                    });
                }
            }, true)
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $id = $request->id;
            $status = $request->status;
            $vendor_reject_reason = $request->vendor_reject_reason;
            $cancel_req = OrderCancelRequest::where('id', $id)->first();
            if (!$cancel_req) {
                return $this->errorResponse('Invalid Data', 422);
            }
            if ($cancel_req->status == 1) {
                return $this->errorResponse('Request has already been approved', 422);
            }
            if ($cancel_req->status == 2) {
                return $this->errorResponse('Request has already been rejected', 422);
            }
            $order_id = $cancel_req->order_id;
            $vendor_id = $cancel_req->vendor_id;
            $order_vendor_id = $cancel_req->order_vendor_id;
            $order_vendor_product_id = $cancel_req->order_vendor_product_id;
            $client_preferences = ClientPreference::first();
            $orderData = Order::with(array(
                'luxury_option',
                'vendors' => function ($query) use ($vendor_id) {
                    $query->where('vendor_id', $vendor_id);
                }
            ))->find($order_id);
            
            $currentOrderStatus = OrderVendor::with('orderDetail', 'vendor', 'products')->where(['id' => $order_vendor_id, 'vendor_id' => $vendor_id, 'order_id' => $order_id])->first();
            $orderVendorProduct = OrderProduct::with('addon', 'addon.option', 'variant')->where('order_vendor_id', $currentOrderStatus->id)->where('id', $order_vendor_product_id)->first();
            

            $cancelledProductPrice = $this->checkreplaceProduct($request, $orderVendorProduct) * $orderVendorProduct->quantity;

            if ($currentOrderStatus->order_status_option_id == 2 && $status == 1) {
                $this->ProductVariantStockIncrease($orderVendorProduct);
            }

            // If cancel order request has been approved
            if ($status == 1) {
                if ($currentOrderStatus->order_status_option_id == 3) {
                    return $this->errorResponse(__('Order has already been rejected'), 422);
                }
                if (count($currentOrderStatus->products) == 1) {
                    $return_response =  $this->GetVendorReturnAmount($request, $orderData);

                    if (!empty($currentOrderStatus->dispatch_traking_url)) {
                        $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $currentOrderStatus->dispatch_traking_url);
                        
                        $response = Http::get($dispatch_traking_url . '?reject_reason=' . $cancel_req->reject_reason);
                        $response = json_decode($response->getBody(), true);

                        if ($response['status'] != 'Success') {
                            return $this->errorResponse($response['message'], 400);
                        }
                    }

                    $currentOrderStatus->order_status_option_id = 3;
                    $currentOrderStatus->reject_reason = $cancel_req->reject_reason;
                    $currentOrderStatus->cancelled_by = $user->id;
                    $currentOrderStatus->update();

                    // if ($currentOrderStatus->payment_option_id != 1) {
                    //     $order_user = User::find($currentOrderStatus->user_id);
                    //     $wallet = $order_user->wallet;
                    //     $credit_amount = $currentOrderStatus->orderDetail->payable_amount;
                    //     $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $currentOrderStatus->orderDetail->order_number . ' (' . $currentOrderStatus->vendor->name . ')']);
                    // }
                } else {
                    $orderProduct = OrderProduct::where('order_vendor_id', $currentOrderStatus->id)->where('id', $order_vendor_product_id)->delete();
                    $return_response =  $this->GetVendorProductReturnAmount($request, $orderData, $cancelledProductPrice);
                    $currentOrderStatus->payable_amount = $currentOrderStatus->payable_amount - $cancelledProductPrice;
                    $currentOrderStatus->subtotal_amount = $currentOrderStatus->subtotal_amount - $cancelledProductPrice;
                    $currentOrderStatus->save();
                    // order amount update
                    $orderData->total_amount = $orderData->total_amount - $cancelledProductPrice;
                    $orderData->payable_amount = $orderData->payable_amount - $cancelledProductPrice;
                    $orderData->save();
                }



                if ($return_response['vendor_return_amount'] > 0) {
                    $user = User::find($currentOrderStatus->user_id);
                    $wallet = $user->wallet;
                    $credit_amount = $return_response['vendor_return_amount']; //$currentOrderStatus->payable_amount;
                    $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $currentOrderStatus->orderDetail->order_number . ' (' . $currentOrderStatus->vendor->name . ')']);
                }
                $msg = 'approved';
            } elseif ($status == 2) {
                //
                $msg = 'rejected';
            } else {
                return $this->errorResponse(__('Invalid status code'), 400);
            }

            $cancel_req->status = $status;
            $cancel_req->updated_by = $user->id;
            $cancel_req->vendor_reject_reason = $vendor_reject_reason;
            $cancel_req->update();

            //Need to cancel order from delivery panel
            if ($currentOrderStatus->shipping_delivery_type == 'DU') {
                //Cancel Dunzo place order request for Dunzo
                $ship = new DunzoController();
               $res = $ship->cancelOrderRequestDunzo($currentOrderStatus->web_hook_code);
        }elseif($currentOrderStatus->shipping_delivery_type=='L'){
            //Cancel Shipping place order request for Lalamove
            $lala = new LalaMovesController();
            $order_lalamove = $lala->cancelOrderRequestlalamove($currentOrderStatus->web_hook_code);
        }elseif ($currentOrderStatus->shipping_delivery_type == 'K') {
            //Cancel Shipping place order request for KwikApi
            $lala = new QuickApiController();
            $order_lalamove = $lala->cancelOrderRequestKwikApi($request->order_id,$request->vendor_id);
        }elseif($currentOrderStatus->shipping_delivery_type=='SR'){
            //Cancel Shipping place order request for Shiprocket
            $ship = new ShiprocketController();
            $order_ship = $ship->cancelOrderRequestShiprocket($currentOrderStatus->ship_order_id);
        }elseif($currentOrderStatus->shipping_delivery_type=='M'){
            //Create Shipping place order request for Ahoy
            $ship = new AhoyController();
            $order_ship = $ship->cancelOrderRequestAhoy($currentOrderStatus->web_hook_code);
        }

            DB::commit();
            $this->sendCancelOrderRequestStatusNotification($currentOrderStatus, $status);
            return $this->successResponse(__('Request has been '.$msg.' Successfully.'),200);
        }
        catch(\Exception $ex){
            DB::rollback();
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }

    public function sendCancelOrderRequestStatusNotification($order_vendor, $status)
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $postdata =  ['web_hook_code' => $order_vendor->web_hook_code, 'status' => $status];
                $client = new GCLIENT([
                    'headers' => [
                        'personaltoken' => $dispatch_domain->delivery_service_key,
                        'shortcode' => $dispatch_domain->delivery_service_key_code,
                        'content-type' => 'application/json'
                    ]
                ]);
                $url = $dispatch_domain->delivery_service_key_url;
                $res = $client->post(
                    $url . '/api/cancel-order-request-status/driver/notify',
                    ['form_params' => ($postdata)]
                );
                $response = json_decode($res->getBody(), true);
                // if ($response && $response['message'] == 'success') {
                //     return $response;
                // }
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
