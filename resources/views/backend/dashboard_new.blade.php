@extends('layouts.vertical', ['title' => 'Dashboard'])
@section('css')
<link href="{{asset('assets/assets/dashboard/css/jquery-jvectormap-1.2.2.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/dashboard/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/dashboard/css/new_app.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/dashboard/css/new_dashboard.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/dashboard/css/arabic_dashboard.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
span.nodatafound {font-size:120% !important;border: 1px solid #FC0;background: #FFC;color: #384F34;display: block;font-weight: bold;margin: 2px auto 14px;padding: 15px !important;text-align: left;}
</style>
@endsection
@section('content')

<!-- Dashboard start here -->
<div class="container-fluid">
        <div class="row d-flex align-items-center">
            <div class="col-12">
                <!-- page title start here -->
                <div class="page-title-box mb-2">
                    <!-- page title right side here -->
                    <div class="page-title-right">
                        <form class="d-flex">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-light" id="dash-daterange">
                                <span class="input-group-text bg-primary border-primary text-white">
                                    <i class="mdi mdi-calendar-range font-13"></i>
                                </span>
                            </div>
                            <a href="javascript: void(0);" class="btn btn-primary ms-2">
                                <i class="mdi mdi-autorenew"></i>
                            </a>
                            <a href="javascript: void(0);" class="btn btn-primary ms-1">
                                <i class="mdi mdi-filter-variant"></i>
                            </a>
                        </form>
                    </div><!-- page title right side here -->
                    <h4 class="page-title">Dashboard</h4>
                </div><!-- page title end here -->
            </div>
        </div>

        <div class="row">
            <div class="col-xl-5 col-lg-6">
                <div class="row">
                    <div class="col-sm-6">
                        <!-- Customer box start here -->
                        <div class="card">
                            <div class="card-body">
                                <div class="float-right">
                                    <i class="mdi mdi-account-multiple widget-icon"></i>
                                </div>
                                <h5 class="" title="Number of Customer">Customer</h5>
                                <h3 class="mt-3 mb-3">36,254</h3>
                                <p class="mb-0">
                                    <span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i> 5.27%</span>
                                    <span class="text-nowrap">{{ __('Since last month') }}</span>
                                </p>
                            </div>
                        </div><!-- Customer box end here -->
                    </div>
                    <div class="col-sm-6">
                        <!-- Orders box start here -->
                        <div class="card">
                            <div class="card-body">
                                <div class="float-right">
                                    <i class="mdi mdi-cart-plus widget-icon"></i>
                                </div>
                                <h5 class="" title="Number of Orders">{{ __('Orders') }}</h5>
                                <h3 class="mt-3 mb-3">36,254</h3>
                                <p class="mb-0">
                                    <span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i> 1.08%</span>
                                    <span class="text-nowrap">{{ __('Since last month') }}</span>
                                </p>
                            </div>
                        </div><!-- Orders box end here -->
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <!-- Revenue box start here -->
                        <div class="card">
                            <div class="card-body">
                                <div class="float-right">
                                    <i class="mdi mdi-currency-usd widget-icon"></i>
                                </div>
                                <h5 class="" title="Number of Revenue">Revenue</h5>
                                <h3 class="mt-3 mb-3">$6,254</h3>
                                <p class="mb-0">
                                    <span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i> 5.27%</span>
                                    <span class="text-nowrap">{{ __('Since last month') }}</span>
                                </p>
                            </div>
                        </div><!-- Revenue box end here -->
                    </div>
                    <div class="col-sm-6">
                        <!-- Growth box start here -->
                        <div class="card">
                            <div class="card-body">
                                <div class="float-right">
                                    <i class="mdi mdi-pulse widget-icon"></i>
                                </div>
                                <h5 class="" title="Number of Growth">{{ __('Products') }}</h5>
                                <h3 class="mt-3 mb-3">+ 3056</h3>
                                <p class="mb-0">
                                    <span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i> 4.08%</span>
                                    <span class="text-nowrap">{{ __('Since last month') }}</span>
                                </p>
                            </div>
                        </div><!-- Growth box end here -->
                    </div>
                </div>
            </div>

            <div class="col-xl-7 col-lg-6">
                <div class="card card-h-100">
                    <!-- month wise data shown start here  -->
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="header-title">Projections Vs Actuals</h4>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" data-popper-placement="bottom-end" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate(0px, 31px);">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Sales Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Profit</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                </div>
                            </div>
                        </div>
                        <div dir="ltr">
                            <div id="high-performing-product" class="apex-charts" data-colors="#727cf5,#e3eaef"></div>
                        </div>
                    </div><!-- month wise data shown end here  -->
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-lg-8">
                <!-- total revenue sec start here -->
                <div class="card">
                    <div class="card-body">
                        <!-- total revenue title start here -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="header-title">REVENUE</h4>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle arrow-none card-drop " data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end " data-popper-placement="bottom-end" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate(0px, 31px);">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Sales Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Profit</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                </div>
                            </div>
                        </div><!-- total revenue title start here -->


                        <div class="chart-content-bg">
                            <div class="row text-center">
                                <!-- current week data start here -->
                                <div class="col-sm-6">
                                    <p class="text-muted mb-0 mt-3">{{ __('Current Week') }}</p>
                                    <h2 class="fw-normal mb-3">
                                        <small class="mdi mdi-checkbox-blank-circle text-primary align-middle me-1"></small>
                                        <span>$58,254</span>
                                    </h2>
                                </div><!-- current week data end here -->

                                <!-- Previous week data start here -->
                                <div class="col-sm-6">
                                    <p class="text-muted mb-0 mt-3">{{ __('Previous Week') }}</p>
                                    <h2 class="fw-normal mb-3">
                                        <small class="mdi mdi-checkbox-blank-circle text-success align-middle me-1"></small>
                                        <span>$69,524</span>
                                    </h2>
                                </div><!-- Previous week data end here -->
                            </div>
                        </div>

                        <!-- Total earning sec start here -->
                        <div class="dash-item-overlay d-none d-md-block" dir="ltr">
                            <h5>Today's Earning: $2,562.30</h5>
                            <p class="text-muted font-13 mb-3 mt-2">Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui.
                                Etiam rhoncus...</p>
                            <a href="javascript: void(0);" class="btn btn-outline-primary">View Statements
                                <i class="mdi mdi-arrow-right ms-2"></i>
                            </a>
                        </div><!-- Total earning sec end here -->

                        <!-- Total earning chat start here -->
                        <div dir="ltr">
                            <div id="revenue-chart" class="apex-charts mt-3" data-colors="#727cf5,#0acf97"></div>
                        </div><!-- Total earning chat end here -->


                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="header-title">{{ __('Revenue By Location') }}</h4>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Sales Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Profit</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4 mt-3">
                            <div id="world-map-markers" style="height: 224px"></div>
                        </div>

                        <h5 class="mb-1 mt-0 fw-normal">New York</h5>
                        <div class="progress-w-percent">
                            <span class="progress-value fw-bold">72k </span>
                            <div class="progress progress-sm">
                                <div class="progress-bar" role="progressbar" style="width: 72%;" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <h5 class="mb-1 mt-0 fw-normal">San Francisco</h5>
                        <div class="progress-w-percent">
                            <span class="progress-value fw-bold">39k </span>
                            <div class="progress progress-sm">
                                <div class="progress-bar" role="progressbar" style="width: 39%;" aria-valuenow="39" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <h5 class="mb-1 mt-0 fw-normal">Sydney</h5>
                        <div class="progress-w-percent">
                            <span class="progress-value fw-bold">25k </span>
                            <div class="progress progress-sm">
                                <div class="progress-bar" role="progressbar" style="width: 39%;" aria-valuenow="39" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <h5 class="mb-1 mt-0 fw-normal">Singapore</h5>
                        <div class="progress-w-percent mb-0">
                            <span class="progress-value fw-bold">61k </span>
                            <div class="progress progress-sm">
                                <div class="progress-bar" role="progressbar" style="width: 61%;" aria-valuenow="61" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-12 order-lg-2 order-xl-1">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="header-title">Top Selling Products</h4>
                            <a href="javascript:void(0);" class="btn btn-sm btn-link">Export <i class="mdi mdi-download ms-1"></i></a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-hover mb-0">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">ASOS Ridley High Waist</h5>
                                            <span class="text-muted font-13">07 April 2018</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$79.49</h5>
                                            <span class="text-muted font-13">{{ __('Price') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">82</h5>
                                            <span class="text-muted font-13">{{ __('Quantity') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$6,518.18</h5>
                                            <span class="text-muted font-13">Amount</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">Marco Lightweight Shirt</h5>
                                            <span class="text-muted font-13">25 March 2018</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$128.50</h5>
                                            <span class="text-muted font-13">{{ __('Price') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">37</h5>
                                            <span class="text-muted font-13">{{ __('Quantity') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$4,754.50</h5>
                                            <span class="text-muted font-13">Amount</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">Half Sleeve Shirt</h5>
                                            <span class="text-muted font-13">17 March 2018</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$39.99</h5>
                                            <span class="text-muted font-13">{{ __('Price') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">64</h5>
                                            <span class="text-muted font-13">{{ __('Quantity') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$2,559.36</h5>
                                            <span class="text-muted font-13">Amount</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">Lightweight Jacket</h5>
                                            <span class="text-muted font-13">12 March 2018</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$20.00</h5>
                                            <span class="text-muted font-13">{{ __('Price') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">184</h5>
                                            <span class="text-muted font-13">{{ __('Quantity') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$3,680.00</h5>
                                            <span class="text-muted font-13">Amount</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">Marco Shoes</h5>
                                            <span class="text-muted font-13">05 March 2018</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$28.49</h5>
                                            <span class="text-muted font-13">{{ __('Price') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">69</h5>
                                            <span class="text-muted font-13">{{ __('Quantity') }}</span>
                                        </td>
                                        <td>
                                            <h5 class="font-14 my-1 fw-normal">$1,965.81</h5>
                                            <span class="text-muted font-13">Amount</span>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div> <!-- end table-responsive-->
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 order-lg-1">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="header-title">Total Sales</h4>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Sales Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Profit</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                </div>
                            </div>
                        </div>

                        <div id="average-sales" class="apex-charts mb-4 mt-3" data-colors="#727cf5,#0acf97,#fa5c7c,#ffbc00"></div>


                        <div class="chart-widget-list">
                            <p>
                                <i class="mdi mdi-square text-primary"></i> Direct
                                <span class="float-end">$300.56</span>
                            </p>
                            <p>
                                <i class="mdi mdi-square text-danger"></i> Affilliate
                                <span class="float-end">$135.18</span>
                            </p>
                            <p>
                                <i class="mdi mdi-square text-success"></i> Sponsored
                                <span class="float-end">$48.96</span>
                            </p>
                            <p class="mb-0">
                                <i class="mdi mdi-square text-warning"></i> E-mail
                                <span class="float-end">$154.02</span>
                            </p>
                        </div>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div>
            <div class="col-xl-3 col-lg-6 order-lg-1">
                <div class="card">
                    <div class="card-body pb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="header-title">Recent Activity</h4>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Sales Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Profit</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body py-0" data-simplebar="init" style="max-height: 405px;">
                        <div class="simplebar-wrapper" style="margin: 0px -24px;">
                            <div class="simplebar-height-auto-observer-wrapper">
                                <div class="simplebar-height-auto-observer"></div>
                            </div>
                            <div class="simplebar-mask">
                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                    <div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content" style="height: auto; overflow: hidden scroll;">
                                        <div class="simplebar-content" style="padding: 0px 24px;">
                                            <div class="timeline-alt py-0">
                                                <div class="timeline-item">
                                                    <i class="mdi mdi-upload bg-info-lighten text-info timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <a href="javascript:void(0);" class="text-info fw-bold mb-1 d-block">You sold an item</a>
                                                        <small>Paul Burgess just purchased “Hyper - Admin Dashboard”!</small>
                                                        <p class="mb-0 pb-2">
                                                            <small class="text-muted">5 minutes ago</small>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="timeline-item">
                                                    <i class="mdi mdi-airplane bg-primary-lighten text-primary timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <a href="javascript:void(0);" class="text-primary fw-bold mb-1 d-block">Product on the Bootstrap Market</a>
                                                        <small>Dave Gamache added
                                                            <span class="fw-bold">Admin Dashboard</span>
                                                        </small>
                                                        <p class="mb-0 pb-2">
                                                            <small class="text-muted">30 minutes ago</small>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="timeline-item">
                                                    <i class="mdi mdi-microphone bg-info-lighten text-info timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <a href="javascript:void(0);" class="text-info fw-bold mb-1 d-block">Robert Delaney</a>
                                                        <small>Send you message
                                                            <span class="fw-bold">"Are you there?"</span>
                                                        </small>
                                                        <p class="mb-0 pb-2">
                                                            <small class="text-muted">2 hours ago</small>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="timeline-item">
                                                    <i class="mdi mdi-upload bg-primary-lighten text-primary timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <a href="javascript:void(0);" class="text-primary fw-bold mb-1 d-block">Audrey Tobey</a>
                                                        <small>Uploaded a photo
                                                            <span class="fw-bold">"Error.jpg"</span>
                                                        </small>
                                                        <p class="mb-0 pb-2">
                                                            <small class="text-muted">14 hours ago</small>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="timeline-item">
                                                    <i class="mdi mdi-upload bg-info-lighten text-info timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <a href="javascript:void(0);" class="text-info fw-bold mb-1 d-block">You sold an item</a>
                                                        <small>Paul Burgess just purchased “Hyper - Admin Dashboard”!</small>
                                                        <p class="mb-0 pb-2">
                                                            <small class="text-muted">16 hours ago</small>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="timeline-item">
                                                    <i class="mdi mdi-airplane bg-primary-lighten text-primary timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <a href="javascript:void(0);" class="text-primary fw-bold mb-1 d-block">Product on the Bootstrap Market</a>
                                                        <small>Dave Gamache added
                                                            <span class="fw-bold">Admin Dashboard</span>
                                                        </small>
                                                        <p class="mb-0 pb-2">
                                                            <small class="text-muted">22 hours ago</small>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="timeline-item">
                                                    <i class="mdi mdi-microphone bg-info-lighten text-info timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <a href="javascript:void(0);" class="text-info fw-bold mb-1 d-block">Robert Delaney</a>
                                                        <small>Send you message
                                                            <span class="fw-bold">"Are you there?"</span>
                                                        </small>
                                                        <p class="mb-0 pb-2">
                                                            <small class="text-muted">2 days ago</small>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end timeline -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="simplebar-placeholder" style="width: auto; height: 623px;"></div>
                        </div>
                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                        </div>
                        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                            <div class="simplebar-scrollbar" style="height: 263px; transform: translate3d(0px, 0px, 0px); display: block;"></div>
                        </div>
                    </div> <!-- end slimscroll -->
                </div>
                <!-- end card-->
            </div>
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
    var dashboard_filter_url = "{{ route('client.dashboard.filter') }}";
</script>
<script src="{{asset('assets/dashboard/js/vendor.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/app.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/apexcharts.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/jquery-jvectormap-1.2.2.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/jquery-jvectormap-world-mill-en.js')}}"></script>
<script src="{{asset('assets/dashboard/js/dashboard.js')}}"></script>


@endsection
