<?php

namespace App\Http\Controllers\Client\Accounting;
use DataTables;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Vendor;
use App\Models\OrderVendor;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\OrderStatusOption;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderPromoCodeExport;
use App\Models\DispatcherStatusOption;

class PromoCodeController extends Controller{

    public function index(Request $request){
        $order_status_options = OrderStatusOption::where('type', 1)->get();

        //  promo_code_uses_count
        $promo_code_uses_count = OrderVendor::distinct('coupon_code');
        if (Auth::user()->is_superadmin == 0) {
            $promo_code_uses_count = $promo_code_uses_count->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $promo_code_uses_count = $promo_code_uses_count->count('coupon_code');

        /// unique_users_to_use_promo_code_count
        $unique_users_to_use_promo_code_count = OrderVendor::whereNotNull('coupon_id')->distinct('user_id');
        if (Auth::user()->is_superadmin == 0) {
            $unique_users_to_use_promo_code_count = $unique_users_to_use_promo_code_count->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $unique_users_to_use_promo_code_count = $unique_users_to_use_promo_code_count->count('user_id');

        /// admin_paid_total_amt
        $admin_paid_total_amt = OrderVendor::where('coupon_paid_by', 1);
        if (Auth::user()->is_superadmin == 0) {
            $admin_paid_total_amt = $admin_paid_total_amt->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $admin_paid_total_amt = $admin_paid_total_amt->sum('discount_amount');

    /// vendor_paid_total_amt
        $vendor_paid_total_amt = OrderVendor::where('coupon_paid_by', 0);
        if (Auth::user()->is_superadmin == 0) {
            $vendor_paid_total_amt = $vendor_paid_total_amt->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendor_paid_total_amt = $vendor_paid_total_amt->sum('discount_amount');

        /// promo_code_options
        $promo_code_options = OrderVendor::whereNotNull('coupon_id')->distinct('coupon_id');

        if (Auth::user()->is_superadmin == 0) {
            $promo_code_options = $promo_code_options->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $promo_code_options = $promo_code_options->get();



        return view('backend/accounting/promocode', compact('vendor_paid_total_amt','admin_paid_total_amt','promo_code_uses_count','unique_users_to_use_promo_code_count','order_status_options','promo_code_options'));
    }

    public function filter(Request $request){
        try {
            $user = Auth::user();
            $search_value = $request->get('search');
            $timezone = $user->timezone ? $user->timezone : 'Asia/Kolkata';
            $vendor_orders_query = OrderVendor::with(['orderDetail.paymentOption', 'user','vendor','payment','orderstatus'])->whereHas('orderDetail',function ($query){
                $query->where('payment_status', 1)->whereNotIn('payment_option_id', [1,38]);
                $query->orWhere(function ($q2) {
                    $q2->whereIn('payment_option_id', [1,38]);
                });
            });
            if (Auth::user()->is_superadmin == 0) {
                $vendor_orders_query = $vendor_orders_query->whereHas('vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            if (!empty($request->get('date_filter'))) {
                $date_date_filter = explode(' to ', $request->get('date_filter'));
                $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
                $from_date = $date_date_filter[0];
                $vendor_orders_query = $vendor_orders_query->between($from_date." 00:00:00", $to_date." 23:59:59");
            }
            if (!empty($request->get('promo_code_filter'))) {
                $promo_code_filter = $request->get('promo_code_filter');
                $vendor_orders_query = $vendor_orders_query->where('coupon_id', $promo_code_filter);
            }
            if (!empty($request->get('status_filter'))) {
                $status_filter = $request->get('status_filter');
                $vendor_orders_query = $vendor_orders_query->where('order_status_option_id', $status_filter);
               
            }
            $vendor_orders = $vendor_orders_query->orderBy('id', 'desc');
            return Datatables::of($vendor_orders)
                ->addColumn('subtotal_amount', function($vendor_orders) {
                    return decimal_format($vendor_orders->subtotal_amount);
                })
                ->addColumn('payable_amount', function($vendor_orders) {
                    return decimal_format($vendor_orders->payable_amount);
                })
                ->addColumn('order_number', function($vendor_orders) {
                    return $vendor_orders->orderDetail ? $vendor_orders->orderDetail->order_number : '';
                })
                ->addColumn('view_url', function($vendor_orders) {
                    if(!empty($vendor_orders->order_id) && !empty($vendor_orders->vendor_id)){
                        return route('order.show.detail', [$vendor_orders->order_id, $vendor_orders->vendor_id]);
                    }else{
                        return '#';
                    }
                })
                ->addColumn('vendor_paid_promo', function($vendor_orders){
                    if($vendor_orders->coupon_paid_by == 0){
                        return decimal_format($vendor_orders->discount_amount ?? 0);
                    }else{
                        return '0.00';
                    }
                })
                ->addColumn('admin_paid_promo', function($vendor_orders){
                    if($vendor_orders->coupon_paid_by == 1){
                        return decimal_format($vendor_orders->discount_amount ?? 0) ;
                    }else{
                        return '0.00';
                    }
                })

                ->addColumn('created_date', function($vendor_orders) use($timezone) {
                    return dateTimeInUserTimeZone($vendor_orders->created_at, $timezone);
                })

                ->addColumn('user_name', function($vendor_orders) {
                    return $vendor_orders->user ? $vendor_orders->user->name : '';
                })
                ->addColumn('order_status', function($vendor_orders) {
                    if ($vendor_orders->OrderStatusOption) {
                        return $vendor_orders->OrderStatusOption->title;
                    }else{
                        return '';
                    }
                })
                ->addColumn('vendor_name',function($vendor_orders){
                    return $vendor_orders->vendor ? __($vendor_orders->vendor->name) : '';
                })
                ->addColumn('payment_option_title',function($vendor_orders){
                    return __($vendor_orders->orderDetail->paymentOption->title);
                })
                ->addIndexColumn()
                ->filter(function ($instance) use ($request) {
                    if (!empty($request->get('search'))) {
                        $search = $request->get('search');
                        $instance->where(function($query) use($search){
                            $query->whereHas('orderDetail', function($q) use($search){
                                $q->where('order_number', 'LIKE', '%'.$search.'%');
                            })
                            ->orWhereHas('user', function($q) use($search){
                                $q->where('name', 'LIKE', '%'.$search.'%');
                            })
                            ->orWhereHas('vendor', function($q) use($search){
                                $q->where('name', 'LIKE', '%'.$search.'%');
                            });
                        });
                    }
                })->make(true);
        } catch (Exception $e) {

        }
    }
    public function export() {
        return Excel::download(new OrderPromoCodeExport, 'promocode.xlsx');
    }
}
