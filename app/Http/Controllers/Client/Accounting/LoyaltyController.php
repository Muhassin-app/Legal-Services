<?php

namespace App\Http\Controllers\Client\Accounting;
use DataTables;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\LoyaltyCard;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaymentOption;
use App\Exports\OrderLoyaltyExport;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class LoyaltyController extends Controller{

    public function index(Request $request){
        $loyalty_card_details = LoyaltyCard::get();

        // total_loyalty_spent
        $total_loyalty_spent = Order::orderBy('id','desc')->where(function ($query){
            $query->where('payment_status', 1)->whereNotIn('payment_option_id', [1,38]);
            $query->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [1,38]);
            });
        }); 
        if (Auth::user()->is_superadmin == 0) {
            $total_loyalty_spent = $total_loyalty_spent->whereHas('vendors.vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_loyalty_spent =$total_loyalty_spent->sum('loyalty_points_used');


        // total_loyalty_earned
        $total_loyalty_earned = Order::orderBy('id','desc');
        if (Auth::user()->is_superadmin == 0) {
            $total_loyalty_earned = $total_loyalty_earned->whereHas('vendors.vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_loyalty_earned =$total_loyalty_earned->sum('loyalty_points_earned');



        $payment_options = PaymentOption::where('status', 1)->get();

         // type_of_loyality_applied_count
         $type_of_loyality_applied_count = Order::orderBy('id','desc');
         if (Auth::user()->is_superadmin == 0) {
             $type_of_loyality_applied_count = $type_of_loyality_applied_count->whereHas('vendors.vendor.permissionToUser', function ($query) {
                 $query->where('user_id', Auth::user()->id);
             });
         }
         $type_of_loyality_applied_count =$type_of_loyality_applied_count->distinct('loyalty_membership_id')->count('loyalty_membership_id');



        return view('backend.accounting.loyality',compact('loyalty_card_details', 'total_loyalty_earned','total_loyalty_spent','type_of_loyality_applied_count', 'payment_options'));
    }

    public function filter(Request $request){
        $month_number = '';
        $user = Auth::user();
        $search_value = $request->get('search');
        $timezone = $user->timezone ? $user->timezone : 'Asia/Kolkata';
        $month_picker_filter = $request->month_picker_filter;
        if($month_picker_filter){
            $temp_arr = explode(' ', $month_picker_filter);
            $month_number =  getMonthNumber($temp_arr[0]);
        }
        $orders_query = Order::with('user','paymentOption','loyaltyCard')->where(function ($query){
            $query->where('payment_status', 1)->whereNotIn('payment_option_id', [1,38]);
            $query->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [1,38]);
            });
        }); 
        if (Auth::user()->is_superadmin == 0) {
            $orders_query = $orders_query->whereHas('vendors.vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }

        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
            $from_date = $date_date_filter[0];
            $orders_query->between($from_date." 00:00:00", $to_date." 23:59:59");
        }
        if (!empty($request->get('payment_option'))) {
            $orders_query->where('payment_option_id',$request->get('payment_option'));
        }
        if (!empty($request->get('loyalty'))) {
            $orders_query->where('loyalty_membership_id',$request->get('loyalty'));
        }
        $orders = $orders_query->orderBy('id', 'desc');
        return Datatables::of($orders)
                ->addColumn('order_number', function($orders) {
                    return $orders->order_number ?? '';
                })
                ->addColumn('created_at', function($orders) {
                    return $orders->created_at ??'N/A';
                })
                ->addColumn('user_name', function($orders) {
                    return $orders->user->name ?? 'N/A';
                })
                ->addColumn('payable_amount', function($orders) {
                    return $orders->payable_amount ?? '0.00';
                })
                ->addColumn('loyalty_membership', function($orders) {
                    return $orders->loyaltyCard->name ?? 'N/A';
                })
                ->addColumn('loyalty_points_used', function($orders) {
                    return $orders->loyalty_points_used ? $orders->loyalty_points_used : '0.00';
                })
                ->addColumn('created_date', function($orders) use($timezone) {
                        return dateTimeInUserTimeZone($orders->created_at, $timezone);
                })
                ->addColumn('loyalty_points_earned',function($orders){
                    return $orders->loyalty_points_earned ? $orders->loyalty_points_earned : '0.00';
                })
                ->addColumn('payment_option_title',function($orders){
                    return __($orders->paymentOption->title??'N/A');
                })
                ->addColumn('payable_amount',function($orders){
                    return decimal_format($orders->payable_amount,",");
                })
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $search = $request->get('search');
                    $instance->where(function($query) use($search) {
                        $query->whereHas('user', function($q) use($search){
                            $q->where('name', 'LIKE', '%'.$search.'%');
                        });
                    })->orWhere('order_number', 'LIKE', '%'.$search.'%');
                }
            })->make(true);
    }
    public function export() {
        return Excel::download(new OrderLoyaltyExport, 'loyality.xlsx');
    }
}
