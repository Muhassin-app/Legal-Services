@extends('layouts.vertical', ['title' => 'Dashboard'])
@section('css')
<link href="{{asset('assets/libs/admin-resources/admin-resources.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
span.nodatafound {font-size:120% !important;border: 1px solid #FC0;background: #FFC;color: #384F34;display: block;font-weight: bold;margin: 2px auto 14px;padding: 15px !important;text-align: left;}
</style>
@endsection
@section('content')

    <div class="content dashboard-boxes">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <div class="d-flex align-items-center mb-3">
                                <input type="text" id="range-datepicker" class="form-control flatpickr-input active alCustomDateInput" placeholder="2018-10-03 to 2018-10-10" readonly="">
                                <a href="javascript: void(0);" class="btn btn-blue ml-2" id="dashboard_refresh_btn">
                                    <i class="mdi mdi-autorenew"></i>
                                </a>
                            </div>
                        </div>
                        <h4 class="page-title">{{ __('Dashboard') }}</h4>
                    </div>
                </div>
            </div>
            <div class="row custom-cols">
                <div class="col col-md-4 col-lg-3 col-xl">
                    <div class="widget-rounded-circle card al_color_box color_f">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Pending Orders') }}</p>
                                        <h3 class=" mt-1 mb-0"><span class="counter" data-plugin="counterup" id="total_pending_order"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4 text-md-right">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fe-heart_ font-22 avatar-title"><svg style="height:24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M32 432C32 458.5 53.49 480 80 480h352c26.51 0 48-21.49 48-48V160H32V432zM160 236C160 229.4 165.4 224 172 224h168C346.6 224 352 229.4 352 236v8C352 250.6 346.6 256 340 256h-168C165.4 256 160 250.6 160 244V236zM480 32H32C14.31 32 0 46.31 0 64v48C0 120.8 7.188 128 16 128h480C504.8 128 512 120.8 512 112V64C512 46.31 497.7 32 480 32z"/></svg></i>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_b">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                            <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Active Orders') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="total_active_order"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fe-shopping-cart font-22 avatar-title"></i>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_c">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Delivered Orders') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="total_delivered_order"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fe-bar-chart-line font-22 avatar-title"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_j">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Cancelled Orders') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="total_rejected_order"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fe-ey_ font-22 avatar-title"><svg style="height:24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M64 480C28.65 480 0 451.3 0 416V352C0 316.7 28.65 288 64 288H448C483.3 288 512 316.7 512 352V416C512 451.3 483.3 480 448 480H64zM448 416V352H64V416H448zM288 160C288 195.3 259.3 224 224 224H64C28.65 224 0 195.3 0 160V96C0 60.65 28.65 32 64 32H368C412.2 32 448 67.82 448 112V128H486.1C507.4 128 518.1 153.9 503 168.1L432.1 239C423.6 248.4 408.4 248.4 399 239L328.1 168.1C313.9 153.9 324.6 128 345.9 128H384V112C384 103.2 376.8 96 368 96H288V160z"/></svg></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_e">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Vendor') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="total_vendor"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fe-eye_ font-22 avatar-title"><svg  style="height:24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M224 256c70.7 0 128-57.31 128-128S294.7 0 224 0C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3c-95.73 0-173.3 77.6-173.3 173.3C0 496.5 15.52 512 34.66 512H413.3C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304zM479.1 320h-73.85C451.2 357.7 480 414.1 480 477.3C480 490.1 476.2 501.9 470 512h138C625.7 512 640 497.6 640 479.1C640 391.6 568.4 320 479.1 320zM432 256C493.9 256 544 205.9 544 144S493.9 32 432 32c-25.11 0-48.04 8.555-66.72 22.51C376.8 76.63 384 101.4 384 128c0 35.52-11.93 68.14-31.59 94.71C372.7 243.2 400.8 256 432 256z"/></svg></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_h">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Categories') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="total_categories"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4 text-md-right">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fa fa-list-alt font-22 avatar-title" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_d">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                            <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Products') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="total_products"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fa-solid fa-barcode-read font-22 avatar-title"><svg style="height:24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M40 32C53.25 32 64 42.75 64 56V456C64 469.3 53.25 480 40 480H24C10.75 480 0 469.3 0 456V56C0 42.75 10.75 32 24 32H40zM128 48V464C128 472.8 120.8 480 112 480C103.2 480 96 472.8 96 464V48C96 39.16 103.2 32 112 32C120.8 32 128 39.16 128 48zM200 32C213.3 32 224 42.75 224 56V456C224 469.3 213.3 480 200 480H184C170.7 480 160 469.3 160 456V56C160 42.75 170.7 32 184 32H200zM296 32C309.3 32 320 42.75 320 56V456C320 469.3 309.3 480 296 480H280C266.7 480 256 469.3 256 456V56C256 42.75 266.7 32 280 32H296zM448 56C448 42.75 458.7 32 472 32H488C501.3 32 512 42.75 512 56V456C512 469.3 501.3 480 488 480H472C458.7 480 448 469.3 448 456V56zM384 48C384 39.16 391.2 32 400 32C408.8 32 416 39.16 416 48V464C416 472.8 408.8 480 400 480C391.2 480 384 472.8 384 464V48z"/></svg></i>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_g">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Banner Promotions') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="total_banners"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fa fa-bullhorn  font-22 avatar-title" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_i">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Brands') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="total_brands"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                    <i class=" font-22 avatar-title" aria-hidden="true"><svg style="height:24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM199.2 312.6c14.94 15.06 34.8 23.38 55.89 23.38c.0313 0 0 0 0 0c21.06 0 40.92-8.312 55.83-23.38c9.375-9.375 24.53-9.469 33.97-.1562c9.406 9.344 9.469 24.53 .1562 33.97c-24 24.22-55.95 37.56-89.95 37.56c0 0 .0313 0 0 0c-33.97 0-65.95-13.34-89.95-37.56c-49.44-49.88-49.44-131 0-180.9c24-24.22 55.98-37.56 89.95-37.56c.0313 0 0 0 0 0c34 0 65.95 13.34 89.95 37.56c9.312 9.438 9.25 24.62-.1562 33.97c-9.438 9.344-24.59 9.188-33.97-.1562c-14.91-15.06-34.77-23.38-55.83-23.38c0 0 .0313 0 0 0c-21.09 0-40.95 8.312-55.89 23.38C168.3 230.6 168.3 281.4 199.2 312.6z"/></svg></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="widget-rounded-circle card al_color_box color_a">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="text-end">
                                        <p class="text-muted mb-1 text-truncate">{{ __('Return Request') }}</p>
                                        <h3 class=" mt-1 mb-0"><span data-plugin="counterup" id="return_requests"></span></h3>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="avatar-lg rounded-circle ml-auto">
                                        <i class="fe-eye_ font-22 avatar-title"><svg style="height:24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M480 256c-17.67 0-32 14.31-32 32c0 52.94-43.06 96-96 96H192L192 344c0-9.469-5.578-18.06-14.23-21.94C169.1 318.3 159 319.8 151.9 326.2l-80 72C66.89 402.7 64 409.2 64 416s2.891 13.28 7.938 17.84l80 72C156.4 509.9 162.2 512 168 512c3.312 0 6.615-.6875 9.756-2.062C186.4 506.1 192 497.5 192 488L192 448h160c88.22 0 160-71.78 160-160C512 270.3 497.7 256 480 256zM160 128h159.1L320 168c0 9.469 5.578 18.06 14.23 21.94C337.4 191.3 340.7 192 343.1 192c5.812 0 11.57-2.125 16.07-6.156l80-72C445.1 109.3 448 102.8 448 95.1s-2.891-13.28-7.938-17.84l-80-72c-7.047-6.312-17.19-7.875-25.83-4.094C325.6 5.938 319.1 14.53 319.1 24L320 64H160C71.78 64 0 135.8 0 224c0 17.69 14.33 32 32 32s32-14.31 32-32C64 171.1 107.1 128 160 128z"/></svg></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row ">
                <div class="col-xl-6 col-lg-12 mb-3">
                    <div class="card mb-0 h-100">
                        <div class="card-body align-items-center">
                            <div id="cardCollpase2" class="collapse show mt-3 " dir="ltr" style="position: relative;">
                                <div class="card-widgets d-block align-items-top select-date">
                                    <div class="btn-group mb-0 mx-2">
                                        <button type="button" class="btn btn-xs btn-secondary yearSales">{{ __('Year') }}</button>
                                        <button type="button" class="btn btn-xs btn-light weeklySales">{{ __('Week') }}</button>
                                        <button type="button" class="btn btn-xs btn-light monthlySales">{{ __('Month') }}</button>
                                    </div>
                                </div>
                                <h4 class="header-title mb-0">{{ __('Sales Analytics') }}</h4>
                                <div id="sales-analytics" class="mt-4" data-colors="#1abc9c,#4a81d4" style="min-height: 393px;">
                                </div>
                                <div class="resize-triggers">
                                    <div class="expand-trigger">
                                        <div style="width: 974px; height: 394px;"></div>
                                    </div>
                                    <div class="contract-trigger"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-8 mb-3">
                    <div class="card h-100">
                        <div class="card-body align-items-center">
                            <div id="cardCollpase3" class="collapse pt-3 show">
                                <h4 class="header-title mb-0">{{ __('Revenue By Location') }}</h4>
                                <div id="world-map-markers" style="height: 433px"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body align-items-center">
                            <div id="cardCollpase4" class="collapse show pt-3">
                                <h4 class="header-title mb-0 pb-2">{{ __("Orders (Top Categories)") }}</h4>
                                <div class="gray-placeholder-img text-center py-5 my-2 hide" id="empty_card_collpase4">
                                    <img class="img-fluid" src="{{asset('assets/images/Dashboard _ Royo.png')}}" alt="">
                                </div>
                                <div id="apexchartsfwg700r2" class="apexcharts-canvas apexchartsfwg700r2 apexcharts-theme-light mt-5"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



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
<script src="{{asset('js/admin_dashboard.js')}}"></script>
<script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>
<script src="{{asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script src="{{asset('assets/libs/admin-resources/admin-resources.min.js')}}"></script>
@endsection
