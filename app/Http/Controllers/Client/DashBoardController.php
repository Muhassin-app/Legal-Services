<?php

namespace App\Http\Controllers\Client;

use DB;
use Session;
use Log;
use \DateTimeZone;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseNotification;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Banner, Brand, Category, Country, Order, Product, Vendor, VendorOrderStatus, UserAddress, OrderVendor, OrderReturnRequest, User, ClientCurrency, OrderNotificationsLogs, OrderVendorProduct, ServiceArea, UserVendor};




class DashBoardController extends BaseController
{
    use ApiResponser;

    public $from_date;
    public $to_date;
    public $setWeekDate;
    public $roleId;

    function __construct()
    {
        $this->from_date = Carbon::now()->startOfDay()->subDays(7);
        $this->to_date = Carbon::now()->endOfDay();
        $this->setWeekDate =  $this->from_date->format('d M Y') . ' to '. $this->to_date->format('d M Y');
        $this->roleId = (@auth()->user()) ? getRoleId(@auth()->user()->getRoleNames()[0]) : null;
    }

    public function index(Request $request)
    {   
   
        $managers = User::whereHas('roles',function($q){
            $q->where('name','Manager');
       })->get();
       $setWeekDate = $this->setWeekDate;
       $role = Role::findById(7);
       $lawyer_count = $role->users->count();  
        return view('backend/dashboard',compact('managers','setWeekDate','lawyer_count'));
    }
    

  


    public function dashboard_old()
    {   
        return view('backend/dashboard_old');
    }

    public function postFilterData(Request $request){
        try {
            $type = $request->type;
            $date_filter = $request->date_filter;
            if($date_filter){
                $date_explode = explode('to', $date_filter);
                $from_date = $date_explode[0].' 00:00:00';
                $end_date = $date_explode[1].' 23:59:59';
            }else{
                $from_date = $this->from_date;
                $end_date = $this->to_date;
            }
            $total_brands = Brand::where('status', 1);
            if($date_filter){
                $total_brands->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_brands = $total_brands->count();
            /// Vendors count 
            $total_vendor = Vendor::orderBy('id','desc');
            if (Auth::user()->is_superadmin == 0) {
                $total_vendor = $total_vendor->whereHas('permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            if($date_filter){
                $total_vendor->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_vendor = $total_vendor->where('status', 1)->count();
            $total_banners = Banner::where('status', 1);
            if($date_filter){
                $total_banners->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_banners = $total_banners->count();
            $total_products = Product::orderBy('id','desc');
            $total_products = $total_products->whereHas('vendor', function ($query){
                $query->where(['vendors.status' => 1]);
            });
            if (Auth::user()->is_superadmin == 0) {
                $total_products = $total_products->whereHas('vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            if($date_filter){
                $total_products->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_products = $total_products->where('deleted_at', NULL)->count();
            $total_categories = Category::whereHas('parent')->where('status', 1);
            if($date_filter){
                $total_categories->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_categories = $total_categories->where('id', '>', '1')->where('deleted_at', NULL)->count();
            $total_revenue = Order::orderBy('id','desc');
            if (Auth::user()->is_superadmin == 0) {
                $total_revenue = $total_revenue->whereHas('vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            $total_revenue = $total_revenue->sum('payable_amount');
            $today_sales = Order::whereDay('created_at', now()->day);
            if (Auth::user()->is_superadmin == 0) {
                $today_sales = $today_sales->whereHas('vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            $today_sales = $today_sales->sum('payable_amount');
            #all pending orders 
            $total_pending_order = OrderVendor::where('order_status_option_id',1);
            if (Auth::user()->is_superadmin == 0) {
                $total_pending_order = $total_pending_order->whereHas('vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            if($date_filter){
                $total_pending_order->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_pending_order = $total_pending_order->count();
            #total_rejected_order
            $total_rejected_order = OrderVendor::where('order_status_option_id',3);
            if (Auth::user()->is_superadmin == 0) {
                 $total_rejected_order = $total_rejected_order->whereHas('vendor.permissionToUser', function ($query) {
                     $query->where('user_id', Auth::user()->id);
                 });
            }
            if($date_filter){
                $total_rejected_order->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_rejected_order = $total_rejected_order->count();
              #total_delivered_order
            $total_delivered_order = OrderVendor::where('order_status_option_id',6);
            if (Auth::user()->is_superadmin == 0) {
                  $total_delivered_order = $total_delivered_order->whereHas('vendor.permissionToUser', function ($query) {
                      $query->where('user_id', Auth::user()->id);
                  });
            }
            if($date_filter){
                $total_delivered_order->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_delivered_order = $total_delivered_order->count();
            $dates = $sales = $labels = $series = $categories = $revenue = $address_ids = $markers =[];
             #total_active_order
            $total_active_order = OrderVendor::whereIn('order_status_option_id',[2,4,5]);
            if (Auth::user()->is_superadmin == 0) {
                $total_active_order = $total_active_order->whereHas('vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            if($date_filter){
                $total_active_order->whereBetween('created_at', [$from_date, $end_date]);
            }
            $total_active_order = $total_active_order->count();


            // Graph Data

            $orders_data = Order::where('id','<>',0);
            if (Auth::user()->is_superadmin == 0) {
                $orders_data = $orders_data->whereHas('vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            $orders_query = clone $orders_data; $monthly_sales_query = clone $orders_data; $address_order_query = clone $orders_data;

            $orders_query = $orders_query->with(array('products' => function ($query) {
                    $query->select('order_id', 'category_id');
                }));
            $monthly_sales_query = $monthly_sales_query->select(\DB::raw('sum(payable_amount) as y'), \DB::raw('count(*) as z'), \DB::raw('date(created_at) as x'), \DB::raw("month(created_at) as month"),\DB::raw("day(created_at) as day"),'address_id');

            $address_order_query = $address_order_query->whereNotNull('address_id')->select('address_id','created_at');



            if($date_filter){
                $monthly_sales_query->whereBetween('created_at', [$from_date, $end_date])->groupBy('x');
                $orders = $orders_query->whereBetween('created_at', [$from_date, $end_date])->select('id')->get();
                $address_ids = $address_order_query->whereBetween('created_at', [$from_date, $end_date])->groupBy('address_id')->pluck('address_id')->toArray();
            }else{
                switch ($type) {
                    case 'monthly':
                        $monthly_sales_query->whereRaw('MONTH(created_at) = ?', [date('m')])->groupBy('x');
                        $orders = $orders_query->whereRaw('MONTH(created_at) = ?', [date('m')])->select('id')->get();
                        $address_ids = $address_order_query->whereRaw('MONTH(created_at) = ?', [date('m')])->groupBy('address_id')->pluck('address_id')->toArray();
                    break;
                    case 'weekly':
                        Carbon::setWeekStartsAt(Carbon::SUNDAY);
                        $monthly_sales_query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->groupBy('x');
                        $orders = $orders_query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->select('id')->get(); 
                        $address_ids = $address_order_query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->groupBy('address_id')->pluck('address_id')->toArray();
                    break;
                    case 'yearly':
                        $monthly_sales_query->whereRaw('YEAR(created_at) = ?', [date('Y')])->groupBy('month')->orderByRaw('month');
                        $orders = $orders_query->whereRaw('YEAR(created_at) = ?', [date('Y')])->select('id')->get();
                        $address_ids = $address_order_query->whereRaw('YEAR(created_at) = ?', [date('Y')])->groupBy('address_id')->pluck('address_id')->toArray(); 
                    break;
                    default:
                        $monthly_sales_query->whereRaw('MONTH(created_at) = ?', [date('m')])->groupBy('x');
                        $orders = $orders_query->whereRaw('MONTH(created_at) = ?', [date('m')])->select('id')->get();
                        $address_ids = $address_order_query->whereRaw('MONTH(created_at) = ?', [date('m')])->groupBy('address_id')->pluck('address_id')->toArray();
                    break;
                }
            }

            $temp_array = [];
            foreach ($orders as $order) {
                foreach ($order->products as $product) {
                    $category = Category::with('english')->where('id', $product->category_id)->first();
                    if ($category) {
                        if($category->english){
                            if (in_array($category->slug, $temp_array)) {
                                $categories[$category->english->name] += 1;
                                $slugs[] = $category->slug;
                            } else {
                                $temp_array[] = $category->slug;
                                $categories[$category->english->name] = 1;
                            }
                        }
                    }
                }
            }
            foreach ($categories as $key => $value) {
                $labels[] = $key;
                $series[] = $value;
            }


            $monthlysales = $monthly_sales_query->get();
            if($type == 'yearly')
            { 
                foreach ($monthlysales as $monthly) {
                    $dates[$monthly->month-1] = config('constants.MONTHS')[$monthly->month];
                    $sales[$monthly->month-1] = $monthly->z;
                    $revenue[$monthly->month-1] = decimal_format($monthly->y); 
                }

                foreach(config('constants.MONTHS') as $k=>$mon){
                    if(!isset($dates[$k-1]))
                    {
                        $dates[$k-1] = $mon;
                        $sales[$k-1] = 0;
                        $revenue[$k-1] = decimal_format(0); 
                    }
                }
            }elseif($type == 'monthly'){
                $current_month = date('M');
                foreach ($monthlysales as $monthly) {
                    $dates[$monthly->day-1] = $monthly->day.' '.$current_month;
                    $sales[$monthly->day-1] = $monthly->z;
                    $revenue[$monthly->day-1] = decimal_format($monthly->y); 
                }
                for($i=0; $i<date('t'); $i++)
                {
                    if(!isset($dates[$i]))
                    {
                        $dates[$i] = ($i+1)." ".$current_month;
                        $sales[$i] = 0;
                        $revenue[$i] = decimal_format(0); 
                    }
                }
            }elseif($type == 'weekly'){
                $first_date = Carbon::now()->startOfWeek()->format('d M');
                $last_date = Carbon::now()->endOfWeek()->format('d M');
                foreach ($monthlysales as $monthly) {
                    $dates[date('w', strtotime($monthly->day.' '.config('constants.MONTHS')[$monthly->month]))] = $monthly->day.' '.config('constants.MONTHS')[$monthly->month];
                    $sales[date('w', strtotime($monthly->day.' '.config('constants.MONTHS')[$monthly->month]))] = $monthly->z;
                    $revenue[date('w', strtotime($monthly->day.' '.config('constants.MONTHS')[$monthly->month]))] = decimal_format($monthly->y); 
                }
                for($i=0; $i<7; $i++)
                {
                    if(!isset($dates[$i]))
                    {
                        $dates[$i] = date('d M', strtotime("+".$i." day", strtotime($first_date)));
                        $sales[$i] = 0;
                        $revenue[$i] = decimal_format(0); 
                    }
                }
            }
            ksort($dates); ksort($sales); ksort($revenue);


            $address_details = UserAddress::whereIn('id', $address_ids)->get();
            foreach ($address_details as $address_detail) {
                if(!$address_detail->latitude){
                    continue;
                }
                $markers[]= array(
                    'name' => $address_detail->city,
                    'latLng' => [$address_detail->latitude , $address_detail->longitude],
                );
            }
            $return_requests = OrderReturnRequest::where('status', 'Pending');
            if (Auth::user()->is_superadmin == 0) {
                $return_requests = $return_requests->whereHas('order.vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            if($date_filter){
                $return_requests->whereBetween('created_at', [$from_date, $end_date]);
            }
            $return_requests = $return_requests->count();
            $response = [
                'dates' => $dates,
                'sales' => $sales,
                'labels' => $labels,
                'series' => $series,
                'markers' => $markers,
                'revenue' => ($revenue),
                'today_sales' => $today_sales, 
                'total_vendor' => $total_vendor, 
                'total_brands' => $total_brands, 
                'total_banners' => $total_banners, 
                'total_revenue' => $total_revenue, 
                'total_products' => $total_products, 
                'return_requests' => $return_requests,
                'total_categories' => $total_categories,
                'total_active_order' => $total_active_order, 
                'total_pending_order' => $total_pending_order, 
                'total_rejected_order' => $total_rejected_order, 
                'total_delivered_order' => $total_delivered_order, 
            ];
            return $this->successResponse($response);
        } catch (Exception $e) {
            
        }
    }

    public function thousandsCurrencyFormat($num)
    {
        if ($num > 1000) {
            $x = round($num);
            $x_number_format = number_format($x);
            $x_array = explode(',', $x_number_format);
            $x_parts = array('k', 'm', 'b', 't');
            $x_count_parts = count($x_array) - 1;
            $x_display = $x;
            $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
            $x_display .= $x_parts[$x_count_parts - 1];
            return $x_display;
        }
        return $num;
    }

    # Filter for new admin dashboard
    public function postFilterDataNew(Request $request)
    {
        try {
            $vendorIds = [];
            $managerId = (($request->manager_id)?$request->manager_id:auth()->id());
            $vendors = Vendor::latest();

            if($this->roleId == 4 && $request->manager_id)
            {
                $vendors = $vendors->where('refference_id',$managerId);
                $vendorIds = $vendors->pluck('id')->toArray();
            }elseif($this->roleId == 4)
            {
                $managerId = UserVendor::where('user_id',$managerId)->get();
                $vendorIds = $managerId->pluck('vendor_id')->toArray();
                $vendors = $vendors->whereIn('id',$vendorIds);
            }
            if(($request->reportType !='Vendor' && !empty($request->reportType)) && isset(auth()->user()->geo_ids))
            {
                $areaVendors = ServiceArea::whereIn('id',explode(',',auth()->user()->geo_ids))->pluck('vendor_id')->toArray();          
                if(count($areaVendors)>0 && ($request->reportType =='Both')){
                    $vendorIds = array_unique(array_merge($areaVendors,$vendorIds));
                }elseif(count($areaVendors)>0 && ($request->reportType =='Zone')){               
                    $vendorIds = $areaVendors;
                }
            }

            $vendorCounts = $vendors->count();
            // $managersCount = User::whereHas('roles',function($q){
            //     $q->where('name','Manager');
            // })->count();
            $role = Role::findById(7);
            $managersCount = $role->users->count();  
            $date_filter = $request->date_filter;
            if($date_filter){

                $date_date_filter = explode(' to ', $request->get('date_filter'));
                $to_date = (!empty($date_date_filter[1])) ? $date_date_filter[1] : $date_date_filter[0];
                $from_date = date("Y-m-d", strtotime($date_date_filter[0]));  
                $to_date = date("Y-m-d", strtotime($to_date));  
                $from_date = $from_date.' 00:00:00';
                $end_date = $to_date.' 23:59:59';
            }else{
                $date_filter = '1';
                $from_date = $this->from_date;
                $end_date = $this->to_date;
            }
            # Products count
            $products = new Product;
            
            $total_products = $products->whereHas('vendor', function ($query){
                $query->where(['vendors.status' => 1]);
            });
            if (Auth::user()->is_superadmin == 0 || $request->manager_id) {
                $total_products = $total_products->whereHas('vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });

                if(count($vendorIds)>0)
                {
                    $total_products = $total_products->whereIn('vendor_id',$vendorIds);
                }
            }

            if($date_filter)
            $total_products->whereBetween('created_at', [$from_date, $end_date]);
            
            
            $total_products = $total_products->where('deleted_at', NULL)->count();

            # Revenue sum
            $orders = new Order;

            $order_revenue = $total_revenue = clone $orders;
            if (Auth::user()->is_superadmin == 0) {
                $orders = $orders->whereHas('vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }   
            $month_revenue =clone $orders;
            if (Auth::user()->is_superadmin == 0) {
                $total_revenue = $orders->whereHas('vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });

                if(count($vendorIds)>0)
                {
                    $total_revenue = $orders->whereHas('vendors.vendor', function ($query) use($vendorIds) {
                        $query->whereIn('vendor_id', $vendorIds);
                    });
                    
                    $order_revenue = clone $total_revenue;
                }
            }
            
            if($date_filter)
            $total_revenue = $total_revenue->whereBetween('created_at', [$from_date, $end_date])->where('payment_status', 1);

            $total_revenue = $total_revenue->sum('payable_amount');
            //pr($total_revenue);
            # Customers count
            $users = new User;
            $total_customers = $users->where(['status' => 1, 'is_superadmin' => 0]);
            
            if($date_filter)
            $total_customers = $total_customers->whereBetween('created_at', [$from_date, $end_date]);
            
            $total_customers = $total_customers->count();

          
            
            if (Auth::user()->is_superadmin == 0 || $request->manager_id) {
             # Orders count
            $vendor_orders = OrderVendor::with(['user','vendor']);

            if($date_filter)
            $vendor_orders->whereBetween('created_at', [$from_date, $end_date]);


                 $vendor_orders = $vendor_orders->whereHas('vendor.permissionToUser', function ($query) {
                        $query->where('user_id', Auth::user()->id);
                    });

             if(count($vendorIds)>0)
                {
                    $vendor_orders = $vendor_orders->whereIn('vendor_id',$vendorIds);
                }

            $total_orders = $vendor_orders->count();


            }else{
                  # Orders count
            if($date_filter)
               $orders =  $orders->whereBetween('created_at', [$from_date, $end_date])->where('payment_status', 1);
            
            $total_orders = $orders->count();

            }
            

            // $total_orders = $vendor_orders->count();
            // pr($total_orders);

            if($this->roleId==4){
                $total_sold_products = OrderVendorProduct::where('order_vendor_id',$vendorIds);

                if($date_filter)
                $total_sold_products = $total_sold_products->whereBetween('created_at', [$from_date, $end_date]);

                $total_sold_products =$total_sold_products->sum('quantity');
            }
            
            $revenueCurrentWeek = clone $order_revenue;
            $revenueLastWeek = clone $order_revenue;
            $orders_currentmonth = clone $order_revenue;
            $orders_lastmonth = clone $order_revenue;
            $revenue_currentmonth = clone $order_revenue;
            $revenue_lastmonth = clone $order_revenue;

            $sale = clone $order_revenue;
            $data = clone $order_revenue;
            $data1 = clone $order_revenue;
            $locationwise_revenue = clone $order_revenue;
            
            $currentyear_ordercount = clone $order_revenue;

            # Current week revenue sum
            $revenueCurrentWeek = $revenueCurrentWeek->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('payable_amount');
            # Previous week revenue sum
            $revenueLastWeek = $revenueLastWeek->whereBetween('created_at', [Carbon::now()->startOfWeek()->subWeek(), Carbon::now()->endOfWeek()->subWeek()])->sum('payable_amount');
        
            $currentmonth_start = Carbon::now()->startOfMonth();
            $currentmonth_end = Carbon::now()->endOfMonth();
            $previousmonth_start = Carbon::now()->startOfMonth()->subMonth();
            $previousmonth_end = Carbon::now()->endOfMonth()->subMonth();

            #Get customers percentage since last month
            $customers_currentmonth = $users->whereBetween('created_at', [$currentmonth_start, $currentmonth_end])->where(['status' => 1, 'is_superadmin' => 0])->count();
            $customers_lastmonth = $users->whereBetween('created_at', [$previousmonth_start, $previousmonth_end])->where(['status' => 1, 'is_superadmin' => 0])->count();
            $customers_increase = '';
            $customers_decrease = '';
            if ($customers_lastmonth < $customers_currentmonth) {
                if ($customers_lastmonth > 0) {
                    $percent_from = $customers_currentmonth - $customers_lastmonth;
                    $customers_increase = $percent_from / $customers_lastmonth * 100; //increase percent
                } else {
                    $customers_increase = 100; //increase percent
                }
            } else {
                if ($customers_currentmonth > 0) {
                    $percent_from = $customers_lastmonth - $customers_currentmonth;
                    $customers_decrease = $percent_from / $customers_lastmonth * 100; //decrease percent
                } else {
                    $customers_decrease = 0;
                }
            }
            if ($customers_increase != '') {
                $customers_increase = round($customers_increase, 2);
            }
            if ($customers_decrease != '') {
                $customers_decrease = round($customers_decrease, 2);
            }

            #Get orders percentage since last month
            $orders_currentmonth = $orders_currentmonth->whereBetween('created_at', [$currentmonth_start, $currentmonth_end])->count();
            $orders_lastmonth = $orders_lastmonth->whereBetween('created_at', [$previousmonth_start, $previousmonth_end])->count();
            $orders_increase = '';
            $orders_decrease = '';
            if ($orders_lastmonth < $orders_currentmonth) {
                if ($orders_lastmonth > 0) {
                    $percent_from = $orders_currentmonth - $orders_lastmonth;
                    $orders_increase = $percent_from / $orders_lastmonth * 100; //increase percent
                } else {
                    $orders_increase = 100; //increase percent
                }
            } else {
                if ($orders_currentmonth > 0) {
                    $percent_from = $orders_lastmonth - $orders_currentmonth;
                    $orders_decrease = $percent_from / $orders_lastmonth * 100; //decrease percent
                } else {
                    $orders_decrease = 0;
                }
            }
            if ($orders_increase != '') {
                $orders_increase = round($orders_increase, 2);
            }
            if ($orders_decrease != '') {
                $orders_decrease = round($orders_decrease, 2);
            }

            #Get revenue percentage since last month
            $revenue_currentmonth = $revenue_currentmonth->whereBetween('created_at', [$currentmonth_start, $currentmonth_end])->sum('payable_amount');
            $revenue_lastmonth = $revenue_lastmonth->whereBetween('created_at', [$previousmonth_start, $previousmonth_end])->sum('payable_amount');
            $revenue_increase = '';
            $revenue_decrease = '';
            if ($revenue_lastmonth < $revenue_currentmonth) {
                if ($revenue_lastmonth > 0) {
                    $percent_from = $revenue_currentmonth - $revenue_lastmonth;
                    $revenue_increase = $percent_from / $revenue_lastmonth * 100; //increase percent
                } else {
                    $revenue_increase = 100; //increase percent
                }
            } else {
                if ($revenue_currentmonth > 0) {
                    $percent_from = $revenue_lastmonth - $revenue_currentmonth;
                    $revenue_decrease = $percent_from / $revenue_lastmonth * 100; //decrease percent
                } else {
                    $revenue_decrease = 'NULL';
                }
            }
            if ($revenue_increase != '') {
                $revenue_increase = round($revenue_increase, 2);
            }
            if ($revenue_decrease != '') {
                $revenue_decrease = round($revenue_decrease, 2);
            }

            #Get products percentage since last month
            $products_currentmonth = $products->whereBetween('created_at', [$currentmonth_start, $currentmonth_end])->where('deleted_at', NULL)->count();
            $products_lastmonth = $products->whereBetween('created_at', [$previousmonth_start, $previousmonth_end])->where('deleted_at', NULL)->count();
            $products_increase = '';
            $products_decrease = '';
            if ($products_lastmonth < $products_currentmonth) {
                if ($products_lastmonth > 0) {
                    $percent_from = $products_currentmonth - $products_lastmonth;
                    $products_increase = $percent_from / $products_lastmonth * 100; //increase percent
                } else {
                    $products_increase = 100; //increase percent
                }
            } else {
                if ($products_currentmonth > 0) {
                    $percent_from = $products_lastmonth - $products_currentmonth;
                    $products_decrease = $percent_from / $products_lastmonth * 100; //decrease percent
                } else {
                    $products_decrease = 0;
                }
            }
            if ($products_increase != '') {
                $products_increase = round($products_increase, 2);
            }
            if ($products_decrease != '') {
                $products_decrease = round($products_decrease, 2);
            }
            $range = range(1,12,1); 
            # Month wise revenue total
            $month_revenue = $month_revenue->select(DB::raw('SUM(payable_amount) as total_amount, MONTH( created_at ) as month'))->whereYear('created_at', date('Y'))
            ->whereIn(DB::raw('MONTH(created_at)'),$range)->groupBy(DB::raw('MONTH(created_at)'))->orderBy(DB::raw('MONTH(created_at)'),'ASC')->get();
            $monthwise_revenue = [];
            $monthData = $month_revenue->mapWithKeys(function($item) {
                return [$item['month'] => $item['total_amount']];
            });
            foreach($range as $value) {
                $monthwise_revenue[] = isset($monthData[$value])?round($monthData[$value]):0;
            }
            # Previous week day wise revenue total
            $previousweek_startdate = Carbon::now()->startOfWeek()->subWeek()->format('Y-m-d');
            $previousweek_revenue_daywise = [];
            for ($i = 0; $i < 7; $i++) {
                $dataSum = $data->where(\DB::raw("DATE(created_at)"), date('Y-m-d', strtotime($previousweek_startdate . '+' . $i . ' day')))->sum('payable_amount');
                $previousweek_revenue_daywise[] = round($dataSum);
            }

            # Current week day wise revenue total
            $currentweek_startdate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $currentweek_revenue_daywise = [];
            for ($i = 0; $i < 7; $i++) {
                $dataSum2 = $data1->where(\DB::raw("DATE(created_at)"), date('Y-m-d', strtotime($currentweek_startdate . '+' . $i . ' day')))->sum('payable_amount');
                $currentweek_revenue_daywise[] = round($dataSum2);
            }

            # Revenue location wise
            // dd($orders->with('address:id,city')->get());  
            // pr($locationwise_revenue->get()->toArray());
            $locationwise_revenueNew = $locationwise_revenue->with('address:id,city')->groupBy('address_id')->selectRaw('address_id, sum(payable_amount) as sum, COUNT(address_id) as addressCount');
            // ->whereYear('created_at', date('Y'));
            // dd($locationwise_revenueNew->get());

            $currentyear_orderCount = $currentyear_ordercount->whereYear('created_at', date('Y'))->count();
            
            if($date_filter)
            {
                $locationwise_revenueNew->whereBetween('created_at', [$from_date, $end_date]);
                $currentyear_orderCount = $currentyear_ordercount->whereBetween('created_at', [$from_date, $end_date])->count();
            }

            if (Auth::user()->is_superadmin == 0) {
                $locationwise_revenueNew = $locationwise_revenueNew->whereHas('vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            
            // dd(currentyear_ordercountNew);
            $address_ids = $locationwise_revenueNew->pluck('address_id')->toArray();

            $locationwise_revenue = $locationwise_revenueNew->get();
            $address_details = UserAddress::whereIn('id', $address_ids)->get();

            # Locations latitude and longitude for map marking
            $markers = [];
            foreach ($address_details as $address_detail) {
                if(!$address_detail->latitude){
                    continue;
                }
                $markers[]= array(
                    'name' => $address_detail->city,
                    'latLng' => [$address_detail->latitude , $address_detail->longitude],
                );
            }

            # Currency symbol
            $clientCurrency = ClientCurrency::with('currency')->where('is_primary', 1)->first();
            $currencySymbol = $clientCurrency->currency->symbol;
            $orderNotificationCnt = OrderNotificationsLogs::whereIn('vendor_id',$vendorIds)->count();

            $orderLocations = [];
            $address_ids  = [];
             
            if(sizeof($locationwise_revenue) > 0) {
                foreach($locationwise_revenue as $key => $orderAdd)
                {
                
                    if($orderAdd->address && !empty(@$orderAdd->address->city)){
                            $sum = isset($orderLocations[$orderAdd->address->city]['sum']) ? $orderLocations[$orderAdd->address->city]['sum'] : 0;
                            $addresscount = isset($orderLocations[$orderAdd->address->city]['addressCount']) ? $orderLocations[$orderAdd->address->city]['addressCount'] : 0;
                            $address_ids[]=$orderAdd->id;
                            //$loc[$orderAdd->address->city]= array(
                                $orderLocations[$orderAdd->address->city]['addressCount']=($addresscount) +$orderAdd->addressCount;
                                $orderLocations[$orderAdd->address->city]['address_id']= $orderAdd->address_id;
                                $orderLocations[$orderAdd->address->city]['city'] = $orderAdd->address->city;
                                $orderLocations[$orderAdd->address->city]['sum'] = ($sum) + ($orderAdd->sum);
                            //);
                    
                    } else {
                        $city = 'Others';
                        $sum = isset($orderLocations[$city]['sum']) ? $orderLocations[$city]['sum'] : 0;
                        $addresscount = isset($orderLocations[$city]['addressCount']) ? $orderLocations[$city]['addressCount'] : 0;
                        $address_ids[]=$orderAdd->id;
                        //$loc[$orderAdd->address->city]= array(
                            $orderLocations[$city]['addressCount']=($addresscount) +1;
                            $orderLocations[$city]['address_id']= $orderAdd->address_id;
                            $orderLocations[$city]['city'] = $city;
                            $orderLocations[$city]['sum'] = ($sum) + ($orderAdd->sum);
                    }
                }
                
            //pr($orderLocations);
            }
            if(Auth::user()->hasRole('Lawyer')){
                // Base query
                $baseQuery = Order::where('lawyer_id', Auth::user()->id)
                                 ->where('payment_status', 1);
                
                // Previous week day wise revenue total for lawyer
                $previousweek_startdate = Carbon::now()->startOfWeek()->subWeek()->format('Y-m-d');
                $previousweek_revenue_daywise = [];
                for ($i = 0; $i < 7; $i++) {
                    $dayDate = date('Y-m-d', strtotime($previousweek_startdate . '+' . $i . ' day'));
                    $dataSum = (clone $baseQuery)
                        ->whereDate('created_at', $dayDate)
                        ->sum('payable_amount');
                    $previousweek_revenue_daywise[] = round($dataSum);
                }

                # Current week day wise revenue total for lawyer
                $currentweek_startdate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $currentweek_revenue_daywise = [];
                for ($i = 0; $i < 7; $i++) {
                    $dayDate = date('Y-m-d', strtotime($currentweek_startdate . '+' . $i . ' day'));
                    $dataSum2 = (clone $baseQuery)
                        ->whereDate('created_at', $dayDate)
                        ->sum('payable_amount');
                    $currentweek_revenue_daywise[] = round($dataSum2);
                }

                // Clone queries for different purposes
                $total_orders = (clone $baseQuery)->count();
                $total_revenue = (clone $baseQuery)->sum('payable_amount');
                // Current week revenue
                $revenueCurrentWeek = (clone $baseQuery)
                    ->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(), 
                        Carbon::now()->endOfWeek()
                    ])
                    ->sum('payable_amount');
                
                // Previous week revenue
                $revenueLastWeek = (clone $baseQuery)
                    ->whereBetween('created_at', [
                        Carbon::now()->startOfWeek()->subWeek(), 
                        Carbon::now()->endOfWeek()->subWeek()
                    ])
                    ->sum('payable_amount');

                if($date_filter){
                    $total_orders = (clone $baseQuery)
                        ->whereBetween('created_at', [$from_date, $end_date])
                        ->count();
                        
                    $total_revenue = (clone $baseQuery)
                        ->whereBetween('created_at', [$from_date, $end_date])
                        ->sum('payable_amount');
                }
           }
            $response = [
                'markers' => $markers,
                'total_revenue' => round($total_revenue),
                'total_products' => $total_products,
                'total_customers' => $total_customers,
                'total_orders' => $total_orders,
                'revenueCurrentWeek' => round($revenueCurrentWeek),
                'revenueLastWeek' => round($revenueLastWeek),
                'customers_increase' => $customers_increase,
                'customers_decrease' => $customers_decrease,
                'orders_increase' => $orders_increase,
                'orders_decrease' => $orders_decrease,
                'revenue_increase' => $revenue_increase,
                'revenue_decrease' => $revenue_decrease,
                'products_increase' => $products_increase,
                'products_decrease' => $products_decrease,
                'monthwise_revenue' => $monthwise_revenue,
                'previousweek_revenue_daywise' => $previousweek_revenue_daywise,
                'currentweek_revenue_daywise' => $currentweek_revenue_daywise,
                'locationwise_revenue' => $orderLocations,
                'currentyear_ordercount' => $currentyear_orderCount,
                'currencySymbol' => $currencySymbol,
                'total_vendors' => $vendorCounts,
                'managersCount' => $managersCount??0,
                'total_sold_products' => $total_sold_products??0,
                'orderNotificationCnt' => $orderNotificationCnt??0
            ];
            return $this->successResponse($response);
        } catch (Exception $e) {
        }
    }

        # Filter for new admin dashboard
        public function notificationList(Request $request)
        {

            $vendorIds = [];
        
            $managerId = auth()->id();
            $vendors = Vendor::latest();
            $vendorIds = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
            $vendorIds =$vendorIds->pluck('id');

            $notifications = OrderNotificationsLogs::whereIn('vendor_id',$vendorIds)->orderBy('id','desc')->get();
            return  view('backend.vendor.notifications',compact('notifications'));
        }
}
