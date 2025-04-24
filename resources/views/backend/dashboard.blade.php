@extends('layouts.vertical', ['title' => 'Dashboard'])
@section('css')

<link href="{{asset('assets/dashboard/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/dashboard/css/new_dashboard.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
a.btn.btn-primary.alCartIcon {
    position: relative;
}
.alBadge {
    position: absolute;
    right: 0;
    top: -10px;
    background-color: red;
    height: 20px;
    width: 20px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}
span.nodatafound {font-size: 120% !important;border: 1px solid #FC0;background: #FFC;color: #384F34;display: block;font-weight: bold;margin: 2px auto 14px;padding: 15px !important;text-align: left;}
</style>
@endsection
@section('content')
@include('alert')
<!-- Dashboard start here -->
<div class="container-fluid py-md-2" id="alDashboardStyle">
    <div class="row d-flex align-items-center ">
        <div class="col-12">
            <!-- page title start here -->
            <div class="page-title-box mb-2">
                <!-- page title right side here -->
                <div class="col-md-6 float-right">
                    <form class="d-flex">

                        @if(auth()->user()->is_superadmin)
                          <div class="input-group mr-2  d-none">
                            <select name="app_managers" class="form-control select2-single mr-2" id="app_managers">
                            <option value="" >Select Manager</option>
                            @foreach($managers as $mng)
                                <option value="{{$mng->id}}" >{{$mng->name}}</option>
                            @endforeach
                            </select>
                        </div>
                        @endif

                        @if(@auth()->user()->roles[0]->name == 'Manager')
                        <div class="input-group mr-2  d-none">
                            <select name="type" id="reportType" class="form-control mr-2" >
                            <option value="" >Select Type</option>
                                <option value="Vendor" >Vendor</option>
                                <option value="Zone" >Zonal</option>
                                <option value="Both" >Both (Vendor and Zonal)</option>
                            </select>
                        </div>
                        @endif
                        <div class="input-group">
                            <input type="text" class="form-control form-control-light" id="range-datepicker" value="{{ $setWeekDate }}" placeholder="">
                            <span class="input-group-text bg-primary border-primary text-white">
                                <i class="mdi mdi-calendar-range font-13"></i>
                            </span>
                        </div>
                        <a href="javascript: void(0);" class="btn btn-primary mx-1" id="dashboard_refresh_btn">
                            <i class="mdi mdi-autorenew"></i>
                        </a>
                        @if(getRoleId(@auth()->user()->roles[0]->name) == 4)
                        <a href="{{route('noti.list')}}" class="btn btn-primary alCartIcon" ><span id="notification_counts" class="alBadge">0</span>
                            <i class="mdi mdi-cart-plus"></i>
                        </a>
                        @endif
                    </form>
                </div><!-- page title right side here -->
                <h4 class="page-title">{{ __('Dashboard') }}</h4>
            </div><!-- page title end here -->
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7 col-lg-7">
            <div class="row">
                @if(auth()->user()->is_superadmin)
                <div class="col-sm">
                    <!-- Customer box start here -->
                    <div class="card alDasBoxItems">
                        <div class="card-body">
                            <div class="float-right">
                                <i class="mdi mdi-account-multiple widget-icon"></i>
                            </div>
                            <h5 class="" title="Number of Customer">{{ __('Customer') }}</h5>
                            <h3 class="mt-3 mb-3" id="total_customers">0</h3>
                            <p class="mb-0" id="customers_change">
                            </p>
                        </div>
                    </div><!-- Customer box end here -->
                </div>
                @endif
                <div class="col-sm">
                    <!-- Orders box start here -->
                    <div class="card alDasBoxItems">
                        <div class="card-body">
                            <div class="float-right">
                                <!-- <i class="mdi mdi-cart-plus widget-icon"></i> -->
                            </div>
                            @php
                                $ordermenu = getNomenclatureName('Orders', true);
                                $ordermenulabel = ($ordermenu=="Orders")?__('Orders'):__($ordermenu);

                            @endphp
                            <h5 class="" title="Number of Orders">{{ __($ordermenulabel) }}</h5>
                            <h3 class="mt-3 mb-3" id="total_orders">0</h3>
                            <p class="mb-0" id="orders_change">
                            </p>
                        </div>
                    </div><!-- Orders box end here -->
                </div>

                <div class="col-sm">
                    @if(auth()->user()->is_superadmin)
                    <!-- Orders box start here -->
                    <div class="card alDasBoxItems">
                        <div class="card-body">
                            <div class="float-right">
                                <i class="mdi mdi-account-multiple widget-icon"></i>
                            </div>
                            @php
                                $vendormenu = getNomenclatureName('Vendors', true);
                                $vendormenulabel = ($vendormenu=="Vendors")?__('Vendors'):__($vendormenu);

                            @endphp
                            <h5 class="" title="Number of Vendors">{{ __($vendormenulabel) }}</h5>
                            <h3 class="mt-3 mb-3" id="total_vendors">0</h3>
                            <p class="mb-0" id="orders_vendor">
                            </p>
                        </div>
                    </div><!-- Orders box end here -->
                    @endif
                </div>

            </div>

            <div class="row">
                @if(auth()->user()->can('dashboard-totalRevenue') || auth()->user()->is_superadmin)
                <div class="col-sm">
                    <!-- Revenue box start here -->
                    <div class="card alDasBoxItems">
                        <div class="card-body">
                            <div class="float-right">
                                <!-- <i class="mdi mdi-currency-usd widget-icon"></i> -->
                            </div>
                            <h5 class="" title="Number of Revenue">{{ __('Total Revenue') }}</h5>
                            <h3 class="mt-3 mb-3" id="total_revenue">0</h3>
                            <p class="mb-0" id="revenue_change">
                            </p>
                        </div>
                    </div><!-- Revenue box end here -->
                </div>
                @endif

                @if(getRoleId(@auth()->user()->getRoleNames()[0])==4)
                <div class="col-sm">
                    <!-- Revenue box start here -->
                    <div class="card alDasBoxItems">
                        <div class="card-body">
                            <div class="float-right">
                                <i class="mdi mdi-currency-usd widget-icon"></i>
                            </div>
                            <h5 class="" title="Number of Revenue">{{ __('Total Sold Products') }}</h5>
                            <h3 class="mt-3 mb-3" id="total_sold_products">0</h3>
                            <p class="mb-0" id="total_sold_products">
                            </p>
                        </div>
                    </div><!-- Revenue box end here -->
                </div>
                @endif
                @if(auth()->user()->is_superadmin)
                <div class="col-sm">
                    <!-- Growth box start here -->
                    <div class="card alDasBoxItems">
                        <div class="card-body">
                            <div class="float-right">
                                <i class="mdi mdi-pulse widget-icon"></i>
                            </div>
                            @php
                                $productmenu = getNomenclatureName('Products', true);
                                $productmenulabel = ($productmenu=="Products")?__('Services'):__($productmenu);

                            @endphp
                            <h5 class="" title="Number of Growth">{{ __($productmenulabel) }}</h5>
                            <h3 class="mt-3 mb-3" id="total_products">+ 0</h3>
                            <p class="mb-0" id="products_change">
                            </p>
                        </div>
                    </div><!-- Growth box end here -->
                </div>
                @endif
                @if(auth()->user()->is_superadmin)
                <div class="col-sm">
                    <!-- Growth box start here -->
                    <div class="card alDasBoxItems">
                        <div class="card-body">
                            <div class="float-right">
                                <i class="mdi mdi-account-multiple widget-icon"></i>
                            </div>
                            <h5 class="" title="Number of Managers">{{ __('Lawyers') }}</h5>
                            <h3 class="mt-3 mb-3" id="total_managers">{{ $lawyer_count}}</h3>
                            <p class="mb-0" id="managers_change">
                            </p>
                        </div>  
                    </div><!-- Growth box end here -->
                </div>
                @endif
            </div>
        </div>
        @if(auth()->user()->can('dashboard-monthRevenue') || auth()->user()->is_superadmin)
        <div class="col-xl-5 col-lg-5">
            <div class="card card-h-100">
                <!-- month wise data shown start here  -->
                <div class="card-body alRevenueBox">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="header-title">{{ __('Revenue Monthly') }}</h4>

                    </div>
                    <div dir="ltr">
                        <div id="revenue-bar-chart" class="apex-charts" data-colors="#43bee1,#e3eaef"></div>
                    </div>
                </div><!-- month wise data shown end here  -->
            </div>
        </div>
        @endif

    </div>

    <div class="row">
        @if(auth()->user()->can('dashboard-weekRevenue') || auth()->user()->is_superadmin)
        <div class="col-lg-8">
            <!-- total revenue sec start here -->
            <div class="card alRevenueByLocation">
                <div class="card-body">
                    <!-- total revenue title start here -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="header-title">{{ __('Revenue Weekly') }}</h4>

                    </div><!-- total revenue title start here -->


                    <div class="chart-content-bg">
                        <div class="row text-center">
                            <!-- current week data start here -->
                            <div class="col-sm-6">
                                <p class="text-muted mb-0 mt-3">{{ __('Current Week') }}</p>
                                <h2 class="fw-normal mb-3">
                                    <small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1"></small>
                                    <span id="revenueCurrentWeek">0</span>
                                </h2>
                            </div><!-- current week data end here -->

                            <!-- Previous week data start here -->
                            <div class="col-sm-6">
                                <p class="text-muted mb-0 mt-3">{{ __('Previous Week') }}</p>
                                <h2 class="fw-normal mb-3">
                                    <small class="mdi mdi-checkbox-blank-circle text-success align-middle me-1"></small>
                                    <span id="revenueLastWeek">0</span>
                                </h2>
                            </div><!-- Previous week data end here -->
                        </div>
                    </div>


                    <div dir="ltr">
                        <div id="revenue-line-chart" class="apex-charts mt-3" data-colors="#43bee1,#0acf97" style="height: 364px"></div>
                    </div><!-- Total earning chat end here -->


                </div>
            </div>
        </div>
        @endif
        @if(auth()->user()->can('dashboard-locationRevenue') || auth()->user()->is_superadmin)
        <div class="col-lg-4">
            <div class="card alRevenueByLocation">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="header-title">{{ __('Revenue By Location') }}</h4>

                    </div>
                    <div class="mb-4 mt-3">
                        <div id="world-map" style="height: 224px"></div>
                    </div>

                    <div id="revenue_locations">

                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>


</div><!-- Dashboard end here -->



<script>
    @section('script')
    @endsection
    var Revenue_lng = "{{__('Revenue')}}";
    var Sales_lng = "{{__('Sales')}}";
    var Net_Revenue_lng = "{{__('Net Revenue')}}";
    var Number_of_Sales_lng = "{{__('Number of Sales')}}";
    var categoryInfo_url = "{{route('client.categoryInfo')}}";
    var yearlyInfo_url = "{{route('client.yearlySalesInfo')}}";
    var weeklyInfo_url = "{{route('client.weeklySalesInfo')}}";
    var monthlyInfo_url = "{{route('client.monthlySalesInfo')}}";
    var dashboard_filter_url = "{{ route('client.dashboard.filter_new') }}";
</script>
<script src="{{asset('assets/dashboard/js/new_dashboard.js')}}"></script>
<script src="{{asset('assets/dashboard/js/vendor.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/apexcharts.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/jquery-jvectormap-1.2.2.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/jquery-jvectormap-world-mill-en.js')}}"></script>
{{-- <script src="{{asset('assets/dashboard/js/dashboard.js')}}"></script> --}}

@endsection
