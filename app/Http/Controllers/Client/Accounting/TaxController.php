<?php

namespace App\Http\Controllers\Client\Accounting;
use DataTables;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderTax;
use App\Models\LoyaltyCard;
use Illuminate\Support\Str; 
use App\Models\TaxCategory;
use Illuminate\Http\Request;
use App\Models\PaymentOption;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderVendorTaxExport;

class TaxController extends Controller{

    public function index(Request $request){
        $tax_category_options = TaxCategory::get();

        // total_tax_collected 
        $total_tax_collected = Order::orderBy('id','desc')->where(function ($query){
            $query->where('payment_status', 1)->whereNotIn('payment_option_id', [1,38]);
            $query->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [1,38]);
            });
        }); 
        if (Auth::user()->is_superadmin == 0) {
            $total_tax_collected = $total_tax_collected->whereHas('vendors.vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_tax_collected =decimal_format($total_tax_collected->sum('taxable_amount'));


        // type_of_taxes_applied_count
        $type_of_taxes_applied_count = OrderTax::distinct('tax_category_id');
        if (Auth::user()->is_superadmin == 0) {
            $type_of_taxes_applied_count = $type_of_taxes_applied_count->whereHas('order.vendors.vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $type_of_taxes_applied_count =$type_of_taxes_applied_count->count('tax_category_id');

        $payment_options = PaymentOption::where('status', 1)->get();

        return view('backend.accounting.tax', compact('total_tax_collected','payment_options','tax_category_options', 'type_of_taxes_applied_count'));
    }

    public function filter(Request $request){
        $user = Auth::user();
        $timezone = $user->timezone ? $user->timezone : 'Asia/Kolkata';
        $orders_query = Order::with('user','paymentOption','taxes','ordervendor')->where(function ($query){
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
        if (!empty($request->get('tax_type_filter'))) {
            $tax_type_filter = $request->get('tax_type_filter');
            $orders_query->whereHas('taxes', function($q) use($tax_type_filter){ 
                if($tax_type_filter){
                    $q->where('tax_category_id', $tax_type_filter);
                }
            });
        }
        if (!empty($request->get('payment_option'))) {
            $orders_query->where('payment_option_id',$request->get('payment_option'));
        }
        $orders = $orders_query->orderBy('id', 'desc'); 
        return Datatables::of($orders)
        ->addColumn('payable_amount', function($orders) {
            return decimal_format($orders->payable_amount);
        })
        ->addColumn('taxable_amount', function($orders) {
            if(isset($orders->ordervendor->taxable_amount)){
                return decimal_format($orders->ordervendor->taxable_amount);
            }else{
                return 0;
            }
            
        })
        ->addColumn('payment_method', function($orders) {
            return $orders->paymentOption ? $orders->paymentOption->title : '';
        })
        ->addColumn('customer_name', function($orders) {
            return $orders->user ? $orders->user->name : '-';
        })
        ->addColumn('created_date', function($orders) use($timezone) {
                return dateTimeInUserTimeZone($orders->created_at, $timezone);
        })
        // ->addColumn('tax_types', function($orders){
        //     $tax_types = [];
        //     foreach ($orders->taxes as $tax) {
        //         if($tax && !is_null($tax->category)){
        //             $tax_types[]= $tax->category->title??'';
        //         }
        //     }
        //     return implode(', ',$tax_types);
        // })
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
        return Excel::download(new OrderVendorTaxExport, 'tax.xlsx');
    }
}
