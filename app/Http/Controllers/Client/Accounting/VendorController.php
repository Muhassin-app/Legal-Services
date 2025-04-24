<?php

namespace App\Http\Controllers\Client\Accounting;
use DB;
use DataTables;
use App\Models\User;
use App\Models\Vendor;
use App\Models\OrderVendor;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderVendorListExport;

class VendorController extends Controller{
    use ApiResponser;

    public function index(Request $request){
        return view('backend.accounting.vendor')->with($this->getOrderVendorCalculations($request,true));
    }
    
    public function getOrderVendorCalculations(Request $request,$flag = false){
        $from_date = "";
        $to_date = "";
        $vendors = OrderVendor::with('orderDetail')->whereHas('vendor',function($q) {
            $q->where('status', '!=', '2')->where('is_seller', 0);
        })->orderBy('id','desc');
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
            $from_date = $date_date_filter[0];
        }
        $vendors = $vendors->whereHas('orderDetail', function ($query) use($from_date,$to_date) {
            if((!empty($from_date)) && (!empty($to_date))){
                $query->between($from_date." 00:00:00", $to_date." 23:59:59");
            }
        });
            $data['total_order_value'] = decimal_format($vendors->where('order_status_option_id', '!=', 3)->sum('payable_amount'));
            $data['total_delivery_fees'] = decimal_format($vendors->where('order_status_option_id', '!=', 3)->sum('delivery_fee'));
            $admin_commission_percentage_amount =  decimal_format($vendors->where('order_status_option_id', '!=', 3)->sum('admin_commission_percentage_amount'));
            $admin_commission_fixed_amount =  decimal_format($vendors->where('order_status_option_id', '!=', 3)->sum('admin_commission_fixed_amount'));
            $data['total_admin_commissions'] = $admin_commission_fixed_amount + $admin_commission_percentage_amount;           
        if($flag){
            return $data;
        }
        return response()->json(['data' => $data]);
    }
    
    
    public function getVendors($request){
        $from_date = "";
        $to_date = "";
        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
            $from_date = $date_date_filter[0];
        }
        $vendors = Vendor::with(['orders' => function($query) use($from_date,$to_date) {
            if((!empty($from_date)) && (!empty($to_date))){
                $query->between($from_date." 00:00:00", $to_date." 23:59:59");
            }
             $query->wherehas('orderDetail',function ($q) {
                $q->where('payment_status', 1);
                 });
        }])->where('status', '!=', '2')->where('is_seller', 0)->orderBy('id', 'desc');
        
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        return $vendors;
        
    }

    public function filter(Request $request){
        // $month_number = '';
        // $month_picker_filter = $request->month_picker_filter;
        // if($month_picker_filter){
        //     $temp_arr = explode(' ', $month_picker_filter);
        //     $month_number =  getMonthNumber($temp_arr[0]);
        // }

        $vendors = $this->getVendors($request);
 
        // foreach ($vendors as $vendor) {

        //     $vendor->total_paid = 0.00;
        //     $vendor->url = route('vendor.show', $vendor->id);
        //     $vendor->view_url = route('vendor.show', $vendor->id);
        //     $vendor->delivery_fee = decimal_format($vendor->orders->sum('delivery_fee'));
        //     $vendor->order_value = decimal_format($vendor->orders->sum('payable_amount'));
        //     $vendor->payment_method = decimal_format($vendor->orders->whereIn('payment_option_id', [2,3, 4])->sum('payable_amount'));
        //     $vendor->promo_admin_amount = decimal_format($vendor->orders->where('coupon_paid_by', 1)->sum('discount_amount'));
        //     $vendor->promo_vendor_amount = decimal_format($vendor->orders->where('coupon_paid_by', 0)->sum('discount_amount'));
        //     $vendor->service_fee = decimal_format($vendor->orders->sum('service_fee_percentage_amount'));
        //     $vendor->cash_collected_amount = decimal_format($vendor->orders->where('payment_option_id', 1)->sum('payable_amount'));
        //     $vendor->admin_commission_amount = decimal_format($vendor->orders->sum('admin_commission_percentage_amount') +  $vendor->orders->sum('admin_commission_fixed_amount'));
        //     $vendor->taxable_amount = decimal_format($vendor->orders->sum('taxable_amount'));
        //     $vendor->vendor_earning = decimal_format(($vendor->orders->sum('payable_amount') - $vendor->promo_vendor_amount - $vendor->promo_admin_amount - $vendor->admin_commission_amount - $vendor->delivery_fee ));
        // }
        return Datatables::of($vendors)
            ->addColumn('total_paid', function($vendors) {
                return 0.00;
            })
            ->addColumn('url', function($vendors) {
                return route('vendor.show', $vendors->id);
            })
            ->addColumn('view_url', function($vendors){
                return route('vendor.show', $vendors->id);
            })
            ->addColumn('delivery_fee', function($vendors) {
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('delivery_fee'));
            })
            ->addColumn('order_value', function($vendors) {
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('payable_amount'));
            })
            ->addColumn('payment_method', function($vendors){
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->whereNotIn('payment_option_id', [1,2])->sum('payable_amount'));
            })
            ->addColumn('promo_admin_amount', function($vendors) {
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->where('coupon_paid_by', 1)->sum('discount_amount'));
            })
            ->addColumn('promo_vendor_amount', function($vendors) {
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->where('coupon_paid_by', 0)->sum('discount_amount'));
            })
            ->addColumn('service_fee', function($vendors){
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('service_fee_percentage_amount'));
            })
            ->addColumn('fixed_fee', function($vendors){
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('fixed_fee'));
            })
            ->addColumn('cash_collected_amount', function($vendors) {
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->where('payment_option_id', 1)->sum('payable_amount') + $vendors->orders->where('order_status_option_id', '!=', 3)->sum('taxable_amount') + $vendors->orders->where('order_status_option_id', '!=', 3)->sum('service_fee_percentage_amount'));
            })
            ->addColumn('admin_commission_amount', function($vendors) {
                $admin_commission_fixed_amount = decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('admin_commission_fixed_amount'));
                $admin_commission_percentage_amount = decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('admin_commission_percentage_amount'));
                return decimal_format($admin_commission_fixed_amount +  $admin_commission_percentage_amount);
            })
            ->addColumn('taxable_amount', function($vendors){
                return decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('taxable_amount'));
            })
            ->addColumn('vendor_earning', function($vendors) {
                $order_value = decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('payable_amount'));
                $delivery_fee = decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('delivery_fee'));
                $promo_vendor_amount = decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->where('coupon_paid_by', 0)->sum('discount_amount'));
                $promo_admin_amount = decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->where('coupon_paid_by', 1)->sum('discount_amount'));
                $admin_commission_fixed_amount = decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('admin_commission_fixed_amount'));
                $admin_commission_percentage_amount = decimal_format($vendors->orders->where('order_status_option_id', '!=', 3)->sum('admin_commission_percentage_amount'));
                //$promo_admin_amount
                return decimal_format($order_value - $promo_vendor_amount  - $admin_commission_fixed_amount - $admin_commission_percentage_amount - $delivery_fee);
            })

            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $search = $request->get('search');
                    $instance->where(function($query) use($search){
                        $query->where('name', 'LIKE', '%'.$search.'%');
                    });
                }
            })->make(true);
    }

    public function filterOld(Request $request){
        // $month_number = '';
        // $month_picker_filter = $request->month_picker_filter;
        // if($month_picker_filter){
        //     $temp_arr = explode(' ', $month_picker_filter);
        //     $month_number =  getMonthNumber($temp_arr[0]);
        // }
        $from_date = "";
        $to_date = "";
        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
            $from_date = $date_date_filter[0];
        }
        $vendors = Vendor::with(['orders' => function($query) use($from_date,$to_date) {
            if((!empty($from_date)) && (!empty($to_date))){
                $query->between($from_date." 00:00:00", $to_date." 23:59:59");
            }
        }])->where('status', '!=', '2')->orderBy('id', 'desc');

        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }

        $vendors = $vendors;
        return Datatables::of($vendors)

            ->addColumn('total_paid', function($vendors) {
                return 0.00;
            })
            ->addColumn('url', function($vendors) {
                return route('vendor.show', $vendors->id);
            })
            ->addColumn('view_url', function($vendors){
                    return route('vendor.show', $vendors->id);
            })

            ->addColumn('delivery_fee', function($vendors) {
                return decimal_format($vendors->orders->sum('delivery_fee'));
            })
            ->addColumn('order_value', function($vendors) {
                return decimal_format($vendors->orders->sum('payable_amount'));
            })
            ->addColumn('payment_method', function($vendors){
                    return decimal_format($vendors->orders->whereIn('payment_option_id', [2,3, 4])->sum('payable_amount'));
            })

            ->addColumn('promo_admin_amount', function($vendors) {
                return decimal_format($vendors->orders->where('coupon_paid_by', 1)->sum('discount_amount'));
            })
            ->addColumn('promo_vendor_amount', function($vendors) {
                return decimal_format($vendors->orders->where('coupon_paid_by', 0)->sum('discount_amount'));
            })
            ->addColumn('service_fee', function($vendors){
                    return decimal_format($vendors->orders->sum('service_fee_percentage_amount'));
            })

            ->addColumn('cash_collected_amount', function($vendors) {
                return decimal_format($vendors->orders->where('payment_option_id', 1)->sum('payable_amount'));
            })
            ->addColumn('admin_commission_amount', function($vendors) {
                return decimal_format($vendors->orders->sum('admin_commission_percentage_amount') +  $vendors->orders->sum('admin_commission_fixed_amount'));
            })
            ->addColumn('taxable_amount', function($vendors){
                    return decimal_format($vendors->orders->sum('taxable_amount'));
            })
            ->addColumn('vendor_earning', function($vendors) {
                return decimal_format(($vendors->orders->sum('payable_amount') - $vendors->promo_vendor_amount - $vendors->promo_admin_amount - $vendors->admin_commission_amount - $vendors->delivery_fee ));
            })

            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $search = $request->get('search');
                    $instance->where('name', 'LIKE', '%'.$search.'%');
                }
            })->make(true);
    }

    public function export() {
        return Excel::download(new OrderVendorListExport, 'vendor_list.xlsx');
    }
}
