@extends('layouts.vertical', ['title' => 'Orders'])
@section('content')
@php

    $clientData = \App\Models\Client::select('socket_url')->first();

@endphp
<style type="text/css">
.ellipsis {white-space: nowrap;overflow: hidden;text-overflow: ellipsis;}body {font-size: 0.75rem;}.order_data>div,.order_head h4 {padding: 0 !important;
}.order-page .card-box {padding: 20px 20px 5px !important;}.progress-order {width: calc(100% + 48px);margin: -24px 0 20px;background: #00000012;
color: var(--theme-deafult);position: relative;left: -24px;font-weight: 600;border-top-left-radius: 15px;border-top-right-radius: 15px;padding: 5px 0;
}
.error-msg {
    font-size: 20px;
    position: absolute;
    width: 100%;
    top: 50%;
    -webkit-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
}
.accounting_upload .btn.btn-info{
    border-radius : 10px!important;
}
.alBtnsOnOrders{position: absolute;left: 10px;padding: 0;
    bottom: 10px;
    margin: 0;}
.alBtnsOnOrders li{list-style: none;margin-right: 3px;}
.alBtnsOnOrders .start_chat {
    color: #43bee1;
    height: 30px;
    width: 30px;
    display: inline-block;
    min-width: auto;
    line-height: 26px;
    z-index: 999;
    background-color: transparent;
    border: 1px solid;
}


.rental_filter_tab {
    position: absolute;
    left: 0;
    width: 100%;
}
.rental_filter_tab li.nav-item {
    width: auto;
    display: inline-block;
}
</style>

<div class="container-fluid order-page">
    <div class="row ">
        <div class="col-md-12">
            <div class="page-title-box dashboard_order_title mt-2 d-md-flex align-items-center justify-content-between">
                @php
                    $ordermenu = getNomenclatureName('Orders', true);
                    $ordermenulabel = ($ordermenu=="Orders")?__('Orders'):__($ordermenu);
                @endphp
                <h4 class="page-title">{{ __($ordermenulabel) }}</h4>
                <div class="float-right">
                    <div class="row d-flex justify-content-between">
                        <div class="col-sm-4 mb-1">
                            <input type="text" id="range-datepicker" class="form-control flatpickr-input" placeholder="2018-10-03 to 2018-10-10" readonly="readonly" value="{{$setWeekDate}}">
                        </div>
                        <div class="col-sm-4 mb-1">
                            <select class="form-control" id="vendor_select_box">
                                <option value="">{{ __('Select Vendor') }}</option>
                                @forelse($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                        {{-- <div class="col">
                            <select class="form-control" id="sort_order">
                                <option value="">{{ __('Change Sort') }}</option>
                                <option value="distance">{{ __('Distance') }}</option>
                                <option value="newest_slot">{{ __('Latest Slot') }}</option>
                            </select>
                        </div> --}}
                        <div class="col-sm-4 mb-1">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-danger waves-effect waves-light mr-3" id="clear_filter_btn_icon">
                                    <i class="mdi mdi-close"></i>
                                </button>
                                <input type="search" class="form-control" placeholder="{{ __('Search') }}..." id="search_via_keyword">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        @if($client_preference_detail->third_party_accounting)
        @foreach($accounting as $accounting)
        <div class="pull-right accounting_upload">
        @if($accounting->code == 'xero')
        <a class="btn btn-info" href="{{route('xero_auth')}}">{{ __("Upload to Xero") }} ({{$del_order_count}})</a>
        @endif
        </div>
        @endforeach
        @endif


        <div class="col-sm-12 mb-2">
            <div class="row align-items-center ">
                <div class="col">

                </div>

            </div>
        </div>
        <!-- <div class="col-md-3 col-lg-2 mb-3">
            <input type="search" class="form-control form-control-sm" placeholder="{{ __('Search') }}..." id="search_via_keyword">
        </div> -->
    </div>
</div>
@php
        $ordersNom = getNomenclatureName('Orders', true);
        $ordersNom = ($ordersNom=="Orders")?__('Orders'):__($ordersNom);
@endphp
<script type="text/template" id="no_order_template">
    <div class="error-msg mt-3">
        <img class="mb-2" src="{{asset('images/no-order.svg')}}">
        <p>{{ __("You don't have ".$ordersNom." right now.") }}</p>
    </div>
    </script>

<div class="col-12">
    <div class="row order-list-spinner">
        <div class="tab-product pl-2 pr-2 flex-grow-1">
            <div class="item">
            <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pending_order-tab" data-toggle="tab" href="#pending_orders" role="tab" aria-selected="false" data-rel="pending_orders">
                        <i class="icofont icofont-man-in-glasses"></i>{{ __('Pending '. $ordersNom) }} <sup class="total-items" id="pending-orders" data-count="{{$pending_order_count}}">({{$pending_order_count}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>


                @if ($client_preferences->business_type == 'rental')
                    <li class="nav-item">
                        <a class="nav-link" id="rental_pending_delivery-tab" data-toggle="tab" href="#rental_pending_delivery" role="tab" aria-selected="true" data-rel="rental_pending_delivery">
                            <i class="icofont icofont-ui-home"></i>{{ __('Active '. $ordersNom) }} <sup class="total-items" id="active-orders" data-count="{{$active_order_count}}">({{$active_order_count}})</sup>
                        </a>
                        <div class="material-border"></div>
                        <ul class="nav nav-tabs nav-material rental_filter_tab" id="top-tab" role="tablist" style="display:none;">
                            <li class="nav-item">
                                <a class="nav-link active" id="rental_pending_delivery-tab" data-toggle="tab" href="#rental_pending_delivery" role="tab" aria-selected="false" data-rel="rental_pending_delivery">
                                    <i class="icofont icofont-man-in-glasses"></i>{{ __('Rental Pending Delivery') }} <sup class="total-items" id="rental-pending-delivery"></sup>
                                </a>
                                <div class="material-border"></div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="rental_running_product-tab" data-toggle="tab" href="#rental_running_product" role="tab" aria-selected="false" data-rel="rental_running_product">
                                    <i class="icofont icofont-man-in-glasses"></i>{{ __('Running Product') }} <sup class="total-items" id="rental-running-product"></sup>
                                </a>
                                <div class="material-border"></div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="rental_pending_return-tab" data-toggle="tab" href="#rental_pending_return" role="tab" aria-selected="true" data-rel="rental_pending_return">
                                    <i class="icofont icofont-ui-home"></i>{{ __('Rental Pending Returns') }} <sup class="total-items" id="rental-pending-return"></sup>
                                </a>
                                <div class="material-border"></div>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" id="active_orders_tab" data-toggle="tab" href="#active_orders" role="tab" aria-selected="true" data-rel="active_orders">
                            <i class="icofont icofont-ui-home"></i>{{ __('Active '. $ordersNom) }} <sup class="total-items" id="active-orders" data-count="{{$active_order_count}}">({{$active_order_count}})</sup>
                        </a>
                        <div class="material-border"></div>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" id="orders_history_tab" data-toggle="tab" href="#orders_history" role="tab" aria-selected="false" data-rel="orders_history">
                        <i class="icofont icofont-man-in-glasses"></i>{{ __($ordersNom.' History') }} <sup class="total-items" id="history-orders" data-count="{{$past_order_count}}">({{$past_order_count}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>

            </ul>
            <div class="page-title-box page-title-box text-left ">
                <a class="return-btn mr-1" href="{{route('backend.order.returns',['Pending'])}}">
                    <b>{{ __("Return Request") }}
                        <span class="total-items">({{$return_requests}})</span>
                        {{-- <i class="fa fa-arrow-circle-right" aria-hidden="true"></i> --}}
                    </b>
                </a>
                <a class="mr-2" href="{{route('cancel-order.requests')}}">
                    <b>{{ __("Cancel ".$ordersNom." Request") }}
                        <span class="total-items">({{$cancel_order_requests}})</span>
                        {{-- <i class="fa fa-arrow-circle-right" aria-hidden="true"></i> --}}
                    </b>
                </a>
                @if ($client_preferences->business_type == 'laundry')
                <a class="return-btn" href="{{route('rescheduled.orders')}}">
                    <b>{{ __("Rescheduled Orders") }}
                        <span class="total-items">({{$rescheduleOrderCount}})</span>
                        {{-- <i class="fa fa-arrow-circle-right ml-1" aria-hidden="true"></i> --}}
                    </b>
                </a>
                @endif
                @if ($client_preferences->business_type == 'rental' || $client_preferences->business_type == 'super_app')
                <a class="return-btn" href="{{route('return.dispatcher.form')}}">
                    <b>{{ __("Rental Return Order Form") }}
                        <span class="total-items">({{$returnFormRequestCount}})</span>
                        {{-- <i class="fa fa-arrow-circle-right ml-1" aria-hidden="true"></i> --}}
                    </b>
                </a>
                @endif
            </div>
        </div>
        </div>

        <div class="col-12 pl-2 pr-2">
            <div class="tabs_radio_controls">
                @php
                    $index = 1;
                @endphp
                <input type="radio" class="tabs_radio" id="all_tab" name="select" value="" checked>
                <label class="tabs_label" for="all_tab">
                    <h5 class="m-0">{{ __('All') }}</h5>
                </label>
                @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
                    @php
                        $clientVendorTypes = $vendor_typ_key.'_check';
                        $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                        $NomenclatureName = getNomenclatureName($vendor_typ_value, true);
                        $vendorTypeOrders = $VendorTypesName.'_orders';
                    @endphp

                    @if($client_preference_detail->$clientVendorTypes == 1)
                        <input type="radio" class="tabs_radio" id="{{$VendorTypesName}}_tab" name="select" value="{{$VendorTypesName}}">
                        <label class="tabs_label" for="{{$VendorTypesName}}_tab">
                            <h5 class="m-0">{{ __($NomenclatureName)}}</h5>
                            {{-- <p class="m-0">5%</p> --}}
                            <span class="ml-1" id="{{$VendorTypesName}}-orders">({{ $OrderFilterData[$vendorTypeOrders] ?? 0 }})</span>
                        </label>
                    @endif
                    @php
                        $index++;
                    @endphp
                @endforeach
            </div>
        </div>

    </div>
    <div class="tab-content nav-material  order_data_box scroll-style" id="top-tabContent">
          <div class="spinner-order-loader">
            <div class="nb-spinner-order"></div>
    </div>
        <div class="tab-pane fade past-order show active position-relative h-100" id="pending_orders" role="tabpanel" aria-labelledby="pending_order-tab">
            <div id="pending_orders_row" class="row">
                {!! $OrderFilterData['html'] !!}
            </div>
            <div class="row mt-4 mb-4" id="pending_orders_pagination">
                {!! $OrderFilterData['pagination'] !!}
            </div>
        </div>
        <div class="tab-pane fade position-relative h-100" id="active_orders" role="tabpanel" aria-labelledby="active_orders_tab">
            <div id="active_orders_row" class="row"></div>
            <div class="row mt-4 mb-4" id="active_orders_pagination"></div>
        </div>
        <div class="tab-pane fade past-order position-relative h-100" id="orders_history" role="tabpanel" aria-labelledby="orders_history_tab">
            <div id="orders_history_row" class="row"></div>
            <div class="row mt-4 mb-4" id="orders_history_pagination"></div>

        </div>
        @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
            @php
                $clientVendorTypes = $vendor_typ_key.'_check';
                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                $NomenclatureName = getNomenclatureName($vendor_typ_value, true);
            @endphp

            @if($client_preference_detail->$clientVendorTypes == 1)
                <div class="tab-pane fade past-order position-relative h-100" id="{{$VendorTypesName}}_orders" role="tabpanel" aria-labelledby="{{$VendorTypesName}}_tab">
                    <div class="error-msg mt-3">
                        <img class="mb-2" src="{{asset('images/no-order.svg')}}">
                        <p>{{ __("You don't have ".$ordersNom." right now.") }}</p>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>


<div id="addRejectmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Reject Reason") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="addRejectForm" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" id="AddRejectBox">
                    <p id="error-case" style="color:red;"></p>
                    <label style="font-size:medium;">{{ __("Enter reason for rejecting the order.") }}</label>
                    <textarea class="reject_reason w-100" data-name="reject_reason" name="reject_reason" id="" cols="107" rows="10"></textarea>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light addrejectSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Document Upload Modal -->
<div id="orderDocumentUploadModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="orderDocumentUploadLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Upload Documents") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="orderDocumentUploadForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <input type="file" name="documents[]" id="orderDocuments" class="form-control" required multiple>
                        <input type="hidden" name="order_id" id="orderUploadId">
                        <input type="hidden" name="order_vendor_id" id="orderVendorId">
                    </div>
                    <div id="orderUploadResult"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> {{ __("Upload") }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script> -->

<!-- <script src="https://cdn.socket.io/4.1.2/socket.io.min.js" integrity="sha384-toS6mmwu70G0fw54EGlWWeA4z3dyJ+dlXBtSURSKN4vyRFOcxd3Bzjj/AoOwY+Rg" crossorigin="anonymous"></script> -->

@endsection

@section('script-bottom')
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="{{asset('assets/js/chat/vendor_chat.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
@section('script')
<script type="text/javascript">
    var ajaxCall = 'ToCancelPrevReq';
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
    $("#range-datepicker").flatpickr({
        mode: "range",
        dateFormat: "d M Y", //change format also

        onClose: function(selectedDates, dateStr, instance) {
            //initDataTable();
            var typ=  $("a.nav-link.active").data('rel');
            init(typ, "{{ route('orders.filter') }}", '', false);
        }
    });
    $("#vendor_select_box").change(function() {
      var typ=  $("a.nav-link.active").data('rel');
     //   alert(typ);

        init(typ, "{{ route('orders.filter') }}", '', false);
    });
    $("#sort_order, .tabs_radio").change(function() {
        var typ=  $("a.nav-link.active").data('rel');
        init(typ, "{{ route('orders.filter') }}", '', false);
    });
    $("#clear_filter_btn_icon").click(function() {
        $('#range-datepicker').val('');
        $('#vendor_select_box').val('');
        init("pending_orders", "{{ route('orders.filter') }}", '', false);
    });

    function autoloaddashboad(){
        //console.log('dasd');
        var type =  $("a.nav-link.active").data('rel');
        var search = $("#search_via_keyword").val();

        init(type, "{{ route('orders.filter') }}", search, false);
    }

    function init(filter_order_status, url, search_keyword = "", isOnload = false,type=0) {
    var date_filter = $('#range-datepicker').val();
    var vendor_id = $('#vendor_select_box option:selected').val();
    // var sort_order = $('#sort_order option:selected').val();
    var order_type = $('.tabs_radio:checked').val();
    
        ajaxCall = $.ajax({
            url: url,
            type: "POST",
            dataType: "JSON",
            // async: false,
            beforeSend: function(){
                spinnerJS.showSpinner();
                if(ajaxCall !=  'ToCancelPrevReq' && ajaxCall.readyState < 4){
                    ajaxCall.abort();
                    spinnerJS.hideSpinner();
                }
            },
            data: {
                filter_order_status: filter_order_status,
                search_keyword: search_keyword,
                order_type : order_type,
                vendor_id: vendor_id,
                date_filter: date_filter,
                // sort_order: sort_order
            },
            success: async function(response) {
                // reload after 10 sec
                 $('.order_data_box').removeClass('show-nb-spinner-main');
                //$('#order_list_order').hide();
                //$('.order_data').html(response);
               // console.log(filter_order_status);
               $("#active-orders").html("(" + response.data.active_orders + ")");
                    $("#pending-orders").html("(" + response.data.pending_orders + ")");
                    $("#history-orders").html("(" + response.data.orders_history + ")");
                    if(response.data.delivery_orders !== undefined){
                        $("#delivery-orders").html("(" + response.data.delivery_orders + ")");
                    }
                    if(response.data.dine_in_orders !== undefined){
                        $("#dine_in-orders").html("(" + response.data.dine_in_orders + ")");
                    }
                    if(response.data.takeaway_orders !== undefined){
                        $("#takeaway-orders").html("(" + response.data.takeaway_orders + ")");
                    }
                    if(response.data.rental_orders !== undefined){
                        $("#rental-orders").html("(" + response.data.rental_orders + ")");
                    }
                    if(response.data.pick_drop_orders !== undefined){
                        $("#pick_drop-orders").html("(" + response.data.pick_drop_orders + ")");
                    }
                    if(response.data.on_demand_orders !== undefined){
                        $("#on_demand-orders").html("(" + response.data.on_demand_orders + ")");
                    }
                    if(response.data.laundry_orders !== undefined){
                        $("#laundry-orders").html("(" + response.data.laundry_orders + ")");
                    }
                    if(response.data.appointment_orders !== undefined){
                        $("#appointment-orders").html("(" + response.data.appointment_orders + ")");
                    }
                    (response.data.p2p_orders != undefined) ? $("#p2p-orders").html("(" + response.data.p2p_orders + ")") : '';

                    if(response.data.count_resp == 0)
                    {
                        $(`#${filter_order_status}_row`).html('');
                        // $(`#${filter_order_status}_pagination`).html('');
                    }
                //await $(`#${filter_order_status}_row`).html('');
                await $(`#${filter_order_status}_pagination`).html('');
                if (response.status == 'Success') {
                    if(type==1){
                         await $(`#${filter_order_status}_row`).append(response.data.html);
                    }else{
                        await $(`#${filter_order_status}_row`).html(response.data.html);
                    }
                        if(response.data.pagination!=null && response.data.pagination != ''){
                            await $(`#${filter_order_status}_pagination`).html('');

                            await $(`#${filter_order_status}_pagination`).html(response.data.pagination);
                        } else{
                            await $(`#${filter_order_status}_pagination`).html('');
                        }



                }
                await spinnerJS.hideSpinner();

            },
            error: function(data) {
                console.log(data);
            },
        });
    }
    $(document).ready(function() {

        // setTimeout(function() {
        //     $("#pending_order-tab").trigger('click');
        // }, 1500);

        setInterval(function() {
            // autoloaddashboad();
        }, 17000);

        $(document).on("click", ".load-more-btn", function() {
            $('#order_list_order').show();
            var url = $(this).data('url');
            var rel = $(this).data('rel');
            $("#search_via_keyword").val("");
            init(rel, url, '', true,1);
            $(this).remove();
        });
        $(".nav-link").click(function() {
            $('#order_list_order').show();
            var rel = $(this).data('rel');
            var url = "{{ route('orders.filter') }}";
            $("#search_via_keyword").val("");
            // $(".tab-pane").html('');
            init(rel, url, '', false);

        });
        // $(function() {
        //     var url = window.location.href;
        //     var arr = url.split("/");
        //     var result = arr[2];
        //     let ip_address = result;
        //     let socket_port = "3100";
        //     let socket = io(ip_address + ':' + socket_port);
        //     socket.on('sendChatToClient', (message) => {
        //         $('#order_list_order').show();
        //         var rel = "pending_orders";
        //         var url = "{{ route('orders.filter') }}";
        //         // $(".tab-pane").html('');
        //         init(rel, url, '',false);
        //     });
        // });

        $(document).on("input", "#search_via_keyword", function(e) {
            $('#order_list_order').show();
            var rel = $("#top-tab li a.active").data('rel');
            var url = "{{ route('orders.filter') }}";
            var search_keyword = $(this).val();
            // $(".tab-pane").html('');
            init(rel, url, search_keyword, false);
        })


        function openRejectModal(order_id, vendor_id, status_option_id, order_vendor_id, order_luxury_option_id) {
            var cancelled_by = "{{Auth::user()->id}}";
            // var that = document.getElementById('reject');
            //     var count = that.data("count");
            //     var full_div = that.data("full_div");
            //     var single_div =that.data("single_div");
            //     var status_option_id = that.data("status_option_id");
            //     var status_option_id_next = status_option_id + 1;
            //     var order_vendor_id = that.data("order_vendor_id");
            //     var order_id = that.data("order_id");
            //     var vendor_id = that.data("vendor_id");

            //     var count = that.data("count");
             
            // if (!confirm("Are you sure you want to reject this order?")) {
            //     return; // Exit if user clicks Cancel
            // }
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to reject this order!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            // cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reject it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
            
            $('#addRejectmodal').modal({
                backdrop: 'static',
                keyboard: false,

            });
            $('.addrejectSubmit').on('click', function(e) {
                e.preventDefault();
                var reject_reason = $('#addRejectForm #AddRejectBox .reject_reason').val();

                // var that = document.getElementById('reject');
                var count = $("#reject").data("count");
                var full_div = $("#reject").data("full_div");
                var single_div =$("#reject").data("single_div");
                $.ajax({
                    url: "{{ route('order.changeStatus') }}",
                    type: "POST",
                    data: {
                        order_id: order_id,
                        vendor_id: vendor_id,
                        reject_reason: reject_reason,
                        "_token": "{{ csrf_token() }}",
                        status_option_id: status_option_id,
                        order_vendor_id: order_vendor_id,
                        cancelled_by: cancelled_by,
                        order_luxury_option_id: order_luxury_option_id,
                    },

                    success: function(response) {
                        if (response.status == 'success') {
                            $(".modal .close").click();
                            $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                            //location.reload();
                        } else if (response.status == 'error') {
                            $('#error-case').empty();
                            $('#error-case').append(response.message);
                        }
                        if (count == 0) {
                            $(full_div).slideUp(1000, function() {
                                $(this).remove();
                            });
                            if (response.status == 'success') {
                                // $(".modal .close").click();
                                location.reload();
                            }
                        } else {
                            $(single_div).slideUp(1000, function() {
                                $(this).remove();
                            });
                            if (response.status == 'success') {
                                //   $(".modal .close").click();
                                location.reload();
                            }
                        }
                    },
                    error: function(response) {
                        if (response.status == 'error') {
                            $('#error-case').empty();
                            $('#error-case').append(response.message);
                        }
                    }

                });


            });
        }
    });
        }

        // update status
        // $(document).on("click", ".update-status-ar", function() {

        //     let that = $(this);
        //     that.prop("disabled",true);
        //     var count = that.data("count");
        //     var full_div = that.data("full_div");
        //     var single_div = that.data("single_div");
        //     var status_option_id = that.data("status_option_id");
        //     var luxury_option = that.data("order_luxury_option");
        //     var status_option_id_next = status_option_id + 1;
        //     var order_vendor_id = that.data("order_vendor_id");
        //     var order_id = that.data("order_id");
        //     var vendor_id = that.data("vendor_id");
        //     var count = that.data("count");
        //     var order_luxury_option_id = that.data("order_luxury_option");
        //     var alertMessage = "";
        //     var productIds = [];
        //     var title = "";
        //     var totalCount = $(full_div + ' [data-count]').length;
        //     if(status_option_id == 9){
        //          title = "{{__('Proceed with Accepting Order')}}";
        //     }else if(status_option_id == 10){
        //         title = "{{__('Document Reviewed')}}";
        //     }else if(status_option_id == 2){
        //         title = "{{__('Accepting the Order')}}";
        //     }else if(status_option_id == 4){
        //          title = "{{__('Meeting Scheduled')}}";
        //     }else if(status_option_id == 5){
        //          title = "{{__('Document Submitted')}}";
        //     }else{
        //          title = "{{__('Complete the Session?')}}";
        //     }

        //     $('.productIdsCheck_'+order_id+':checked').each(function(i){
        //         productIds[i] = $(this).val();
        //     });
        //     order_vendor_product_id = [];
        //     $('.productIdsCheck_'+order_id+':checked').each(function(i){
        //         order_vendor_product_id[i] = $(this).data('order_vendor_product_id');
        //     });
        //     if(status_option_id == 2 && that.data('is_alert'))
        //     {
        //         alertMessage = that.data('alert_message');
        //     }
        //     if (status_option_id == 3) {
        //         return openRejectModal(order_id, vendor_id, status_option_id, order_vendor_id, order_luxury_option_id);
        //     } else {
        //         if(productIds.length === 0 && order_luxury_option_id == 4 && status_option_id == 2){
        //             Swal.fire({
        //             title: "{{__('Error')}}",
        //             icon: 'warning',
        //             text: "Please select atleast one product",
        //             showCancelButton: true,
        //             confirmButtonText: 'Ok',
        //             });
        //             that.prop("disabled",false);
        //         }else{
        //             Swal.fire({
        //             title: title,
        //             // icon: 'info',
        //             text: alertMessage,
        //             showCancelButton: true,
        //             confirmButtonText: 'Yes',
        //             }).then((result) => {
        //                 if (result.value) {
        //                     $.ajax({
        //                         url: "{{ route('order.changeStatus') }}",
        //                         type: "POST",
        //                         data: {
        //                             order_id: order_id,
        //                             vendor_id: vendor_id,
        //                             "_token": "{{ csrf_token() }}",
        //                             status_option_id: status_option_id,
        //                             order_vendor_id: order_vendor_id,
        //                             productIds: productIds,
        //                             order_vendor_product_id: order_vendor_product_id,
        //                             order_luxury_option_id: order_luxury_option_id
        //                         },
        //                         success: function(response) {
        //                             if($('#received_new_orders').hasClass('show')){
        //                                 $("#received_new_orders").modal('hide');
        //                             }
        //                             if(response.status=='error'){
        //                                 if (count == 0) {
        //                                     $(full_div).slideUp(1000, function() {
        //                                         $(this).remove();
        //                                     });

        //                                 } else {
        //                                     $(single_div).slideUp(1000, function() {
        //                                         $(this).remove();
        //                                     });

        //                                 }
        //                                 that.prop("disabled",false);
        //                                 $.NotificationApp.send('{{__("Error")}}', response.message, "top-right", "#ff0808", "error");
        //                                 return 0;
        //                             }

        //                             if (status_option_id == 4 || status_option_id == 5) {
        //                                 if (status_option_id == 4){
        //                                     if((luxury_option == 2) || (luxury_option == 3)){
        //                                         var next_status = "{{ __('Order Prepared') }}";
        //                                     }else{
        //                                         var next_status = "{{ __('Document Submitted') }}";
        //                                     }
        //                                 }else{
        //                                     var next_status = "{{ __('Session Completed') }}";
        //                                 }
        //                                 that.prop("disabled",false);
        //                                 that.replaceWith("<button class='update-status-ar btn-warning' data-full_div='" + full_div + "' data-single_div='" + single_div + "'  data-count='" + count + "'  data-order_id='" + order_id + "'  data-vendor_id='" + vendor_id + "'  data-status_option_id='" + status_option_id_next + "' data-order_vendor_id=" + order_vendor_id + ">" + next_status + "</button>");
        //                                 return false;
        //                             } else {

        //                                 if (count == 0) {
        //                                     if(totalCount>2){

        //                                         $(single_div).slideUp(1000, function() {
        //                                         $(this).remove();
        //                                     });
        //                                     }else{

        //                                     $(full_div).slideUp(1000, function() {
        //                                         $(this).remove();
        //                                     });
        //                                 }

        //                                 } else {
        //                                     if(totalCount>2){
        //                                         $(single_div).slideUp(1000, function() {
        //                                         $(this).remove();
        //                                     });
        //                                     }else{
        //                                     $(full_div).slideUp(1000, function() {
        //                                         $(this).remove();
        //                                     });
        //                                 }

        //                                 }
        //                                 that.prop("disabled",false);
        //                             }
        //                             if (status_option_id == 2){
        //                                 that.prop("disabled",false);
        //                             	getOrderCount("pending-orders","active-orders");
        //                                 $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
        //                             }
        //                             if (status_option_id == 6){
        //                                 that.prop("disabled",false);
        //                             	getOrderCount("active-orders","history-orders");
        //                             	$.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
        //                             }
        //                         },
        //                     });
        //                 }else{
        //                 	that.prop("disabled",false);
        //                 }
        //             });
        //         }
        //     }
        // });
// $(document).on("click", ".update-status-ar", function() {
//     let that = $(this);
//     that.prop("disabled", true);
//     var count = that.data("count");
//     var full_div = that.data("full_div");
//     var single_div = that.data("single_div");
//     var status_option_id = that.data("status_option_id");
//     var luxury_option = that.data("order_luxury_option");
//     var status_option_id_next = status_option_id + 1;
//     var order_vendor_id = that.data("order_vendor_id");
//     var order_id = that.data("order_id");
//     var vendor_id = that.data("vendor_id");
//     var count = that.data("count");
//     var order_luxury_option_id = that.data("order_luxury_option");
//     var alertMessage = "";
//     var productIds = [];
//     var title = "";
//     var totalCount = $(full_div + ' [data-count]').length;
  
//     if (status_option_id == 9) {
//         title = "{{__('Proceed with Accepting Order')}}";
//     } else if (status_option_id == 10) {
//         title = "{{__('Document Reviewed')}}";
//     } else if (status_option_id == 2) {
//         title = "{{__('Accepting the Order')}}";
//     } else if (status_option_id == 4) {
//         title = "{{__('Meeting Scheduled')}}";
//     } else if (status_option_id == 5) {
//         title = "{{__('Document Submitted')}}";
//     } else {
//         title = "{{__('Complete the Session?')}}";
//     }

//     $('.productIdsCheck_' + order_id + ':checked').each(function(i) {
//         productIds[i] = $(this).val();
//     });
//     order_vendor_product_id = [];
//     $('.productIdsCheck_' + order_id + ':checked').each(function(i) {
//         order_vendor_product_id[i] = $(this).data('order_vendor_product_id');
//     });
//     if (status_option_id == 2 && that.data('is_alert')) {
//         alertMessage = that.data('alert_message');
//     }
//     if (status_option_id == 3) {
//         return openRejectModal(order_id, vendor_id, status_option_id, order_vendor_id, order_luxury_option_id);
//     } else {
//         if (productIds.length === 0 && order_luxury_option_id == 4 && status_option_id == 2) {
//             Swal.fire({
//                 title: "{{__('Error')}}",
//                 icon: 'warning',
//                 text: "Please select at least one product",
//                 showCancelButton: true,
//                 confirmButtonText: 'Ok',
//             });
//             that.prop("disabled", false);
//         } else {
//             Swal.fire({
//                 title: title,
//                 text: alertMessage,
//                 showCancelButton: true,
//                 confirmButtonText: 'Yes',
//             }).then((result) => {
//                 if (result.value) {
//                     $.ajax({
//                         url: "{{ route('order.changeStatus') }}",
//                         type: "POST",
//                         data: {
//                             order_id: order_id,
//                             vendor_id: vendor_id,
//                             "_token": "{{ csrf_token() }}",
//                             status_option_id: status_option_id,
//                             order_vendor_id: order_vendor_id,
//                             productIds: productIds,
//                             order_vendor_product_id: order_vendor_product_id,
//                             order_luxury_option_id: order_luxury_option_id
//                         },
//                         success: function(response) {
//                             if ($('#received_new_orders').hasClass('show')) {
//                                 $("#received_new_orders").modal('hide');
//                             }
//                             if (response.status == 'error') {
//                                 if (count == 0) {
//                                     $(full_div).slideUp(1000, function() {
//                                         $(this).remove();
//                                     });
//                                 } else {
//                                     $(single_div).slideUp(1000, function() {
//                                         $(this).remove();
//                                     });
//                                 }
//                                 that.prop("disabled", false);
//                                 $.NotificationApp.send('{{__("Error")}}', response.message, "top-right", "#ff0808", "error");
//                                 return 0;
//                             }

//                             if (status_option_id == 4 || status_option_id == 5 ||status_option_id==10 || status_option_id==2) {
//                                 if (status_option_id == 4) {
//                                     if ((luxury_option == 2) || (luxury_option == 3)) {
//                                         var next_status = "{{ __('Order Prepared') }}";
//                                     } else {
//                                         var next_status = "{{ __('Document Submitted') }}";
//                                     }
//                                 } else if(status_option_id == 10){
//                                     var next_status = "{{ __('Approved Request') }}";
//                                 }else if(status_option_id == 2){
//                                     var next_status = "{{ __('Meeting Scheduled') }}";
//                                 }else if(status_option_id == 5){
//                                     var next_status = "{{ __('Session Completed') }}";
//                                 }
//                                 that.prop("disabled", false);
//                                 that.replaceWith("<button class='update-status-ar btn-warning' data-full_div='" + full_div + "' data-single_div='" + single_div + "'  data-count='" + count + "'  data-order_id='" + order_id + "'  data-vendor_id='" + vendor_id + "'  data-status_option_id='" + status_option_id_next + "' data-order_vendor_id=" + order_vendor_id + ">" + next_status + "</button>");
//                                 $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
//                                 return false;
//                             } else {
//                                 if (count == 0) {
//                                     if (totalCount > 2) {
//                                         $(single_div).slideUp(1000, function() {
//                                             $(this).remove();
//                                         });
//                                     } else {
//                                         $(full_div).slideUp(1000, function() {
//                                             $(this).remove();
//                                         });
//                                     }
//                                 }
//                                 that.prop("disabled", false);
//                             }
//                             if (status_option_id == 2) {
//                                 that.prop("disabled", false);
//                                 getOrderCount("pending-orders", "active-orders");
//                                 $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
//                             } else {
//                                 $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
//                             }
//                             if (status_option_id == 6) {
//                                 that.prop("disabled", false);
//                                 getOrderCount("active-orders", "history-orders");
//                                 $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
//                             }
//                         },
//                         error: function(xhr, status, error) {
//                             console.error("AJAX Error: ", status, error);
//                             that.prop("disabled", false);
//                             $.NotificationApp.send('{{__("Error")}}', "An error occurred while processing your request.", "top-right", "#ff0808", "error");
//                         }
//                     });
//                 } else {
//                     that.prop("disabled", false);
//                 }
//             });
//         }
//     }
// });
        $(document).on("click", ".update-status-ar", function() {
            let that = $(this);
            that.prop("disabled", true);
            var count = that.data("count");
            var full_div = that.data("full_div");
            var single_div = that.data("single_div");
            var status_option_id = that.data("status_option_id");
            var luxury_option = that.data("order_luxury_option");
            var status_option_id_next = status_option_id + 1;
            var order_vendor_id = that.data("order_vendor_id");
            var order_id = that.data("order_id");
            var vendor_id = that.data("vendor_id");
            var count = that.data("count");
            var order_luxury_option_id = that.data("order_luxury_option");
            var alertMessage = "";
            var productIds = [];
            var title = "";
            var totalCount = $(full_div + ' [data-count]').length;

            if (status_option_id == 9) {
                title = "{{__('Proceed with Accepting Order')}}";
            } else if (status_option_id == 10 ) {
                title = "{{__('Review Document')}}";
            } else if (status_option_id == 2 || status_option_id == 11) {
                title = "{{__('Accepting the Order')}}";
            } else if (status_option_id == 4 || status_option_id == 12) {
                title = "{{__('Schedule Meeting')}}";
            } else if (status_option_id == 5 || status_option_id == 13) {
                title = "{{__('Submit Document')}}";
            } else {
                title = "{{__('Complete the Session?')}}";
            }

            $('.productIdsCheck_' + order_id + ':checked').each(function(i) {
                productIds[i] = $(this).val();
            });
            order_vendor_product_id = [];
            $('.productIdsCheck_' + order_id + ':checked').each(function(i) {
                order_vendor_product_id[i] = $(this).data('order_vendor_product_id');
            });
            if (status_option_id == 2 && that.data('is_alert')) {
                alertMessage = that.data('alert_message');
            }
            if (status_option_id == 3) {
                return openRejectModal(order_id, vendor_id, status_option_id, order_vendor_id, order_luxury_option_id);
            } else if (status_option_id == 5 || status_option_id == 13) {
                // Show document upload modal
                $('#orderUploadId').val(order_id);
                $('#orderVendorId').val(order_vendor_id);
                $('#orderDocumentUploadModal').modal('show');
                that.prop("disabled", false);
                return;
            } else {
                if (productIds.length === 0 && order_luxury_option_id == 4 && status_option_id == 2) {
                    Swal.fire({
                        title: "{{__('Error')}}",
                        icon: 'warning',
                        text: "Please select at least one product",
                        showCancelButton: true,
                        confirmButtonText: 'Ok',
                    });
                    that.prop("disabled", false);
                } else {
                    Swal.fire({
                        title: title,
                        text: alertMessage,
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                    }).then((result) => {
                        if (result.value) {
                            $.ajax({
                                url: "{{ route('order.changeStatus') }}",
                                type: "POST",
                                data: {
                                    order_id: order_id,
                                    vendor_id: vendor_id,
                                    "_token": "{{ csrf_token() }}",
                                    status_option_id: status_option_id,
                                    order_vendor_id: order_vendor_id,
                                    productIds: productIds,
                                    order_vendor_product_id: order_vendor_product_id,
                                    order_luxury_option_id: order_luxury_option_id
                                },
                                success: function(response) {
                                    if ($('#received_new_orders').hasClass('show')) {
                                        $("#received_new_orders").modal('hide');
                                    }
                                    if (response.status == 'error') {
                                        if(status_option_id != 5 || status_option_id == 13){

                                            if (count == 0) {
                                                $(full_div).slideUp(1000, function() {
                                                    $(this).remove();
                                                });
                                            } else {
                                                $(single_div).slideUp(1000, function() {
                                                    $(this).remove();
                                                });
                                            }
                                        }
                                        that.prop("disabled", false);
                                        $.NotificationApp.send('{{__("Error")}}', response.message, "top-right", "#ff0808", "error");
                                        return 0;
                                    }

                                    if (status_option_id == 4 || status_option_id == 5 || status_option_id == 10 || status_option_id == 2 ||status_option_id == 11 ||status_option_id == 12 || status_option_id == 13) {
                                        if (status_option_id == 4 || status_option_id == 12) {
                                            if ((luxury_option == 2) || (luxury_option == 3)) {
                                                var next_status = "{{ __('Order Prepared') }}";
                                            } else {
                                                var next_status = "{{ __('Submit Document') }}";
                                            }
                                        } else if (status_option_id == 10) {
                                            var next_status = "{{ __('Approve Request') }}";
                                        } else if (status_option_id == 2 ||status_option_id == 11) {
                                            var next_status = "{{ __('Schedule Meeting') }}";
                                        } else if (status_option_id == 5 || status_option_id == 13) {
                                            // var next_status = "{{ __('Session Completed') }}";
                                        }
                                        that.prop("disabled", false);
                                        that.replaceWith("<button class='update-status-ar btn-warning' data-full_div='" + full_div + "' data-single_div='" + single_div + "'  data-count='" + count + "'  data-order_id='" + order_id + "'  data-vendor_id='" + vendor_id + "'  data-status_option_id='" + status_option_id_next + "' data-order_vendor_id=" + order_vendor_id + ">" + next_status + "</button>");
                                        $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                                        return false;
                                    } else {
                                    
                                        if (status_option_id == 9 ||status_option_id == 6 ||status_option_id == 14) {
                                            if (count == 0) {
                                                if (totalCount > 2) {
                                                    $(single_div).slideUp(1000, function() {
                                                        $(this).remove();
                                                    });
                                                } else {
                                                    $(full_div).slideUp(1000, function() {
                                                        $(this).remove();
                                                    });
                                                }
                                            } else {
                                                if (totalCount > 2) {
                                                    $(single_div).slideUp(1000, function() {
                                                        $(this).remove();
                                                    });
                                                } else {
                                                    $(full_div).slideUp(1000, function() {
                                                        $(this).remove();
                                                    });
                                                }
                                            }
                                        }
                                        that.prop("disabled", false);
                                    }
                                    if (status_option_id == 2) {
                                        that.prop("disabled", false);
                                        getOrderCount("pending-orders", "active-orders");
                                        $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                                    } else {
                                        $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                                    }
                                    if (status_option_id == 6) {
                                        that.prop("disabled", false);
                                        getOrderCount("active-orders", "history-orders");
                                        $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX Error: ", status, error);
                                    that.prop("disabled", false);
                                    $.NotificationApp.send('{{__("Error")}}', "An error occurred while processing your request.", "top-right", "#ff0808", "error");
                                }
                            });
                        } else {
                            that.prop("disabled", false);
                        }
                    });
                }
            }
        });
		function getOrderCount(id_1,id_2){
			var pending_count = parseInt($("#"+id_1).attr("data-count"));
        	var active_count = parseInt($("#"+id_2).attr("data-count"));
        	pending_count = pending_count-1;
        	$("#"+id_1).attr("data-count",pending_count);
        	$("#"+id_1).html("("+pending_count+")");
        	active_count = active_count+1;
        	$("#"+id_2).attr("data-count",active_count)
        	$("#"+id_2).html("("+active_count+")");
		}

        // update vendor Product status
        $(document).on("click", ".updateVendorProdStatus", function(e) {
            e.preventDefault()
            let that = $(this);
            var count = that.data("count");
            var status_option_id = that.data("status_option_id");
            var luxury_option = that.data("order_luxury_option");
            var order_vendor_id = that.data("order_vendor_id");
            var order_id = that.data("order_id");
            var vendor_id = that.data("vendor_id");
            var count = that.data("count");
            var order_product_id = that.data("vendor_product_id");
            var order_vendor_product_id = that.data("order_vendor_product_id");
            var alertMessage = "";

            Swal.fire({
                title: "{{__('Proceed with Accepting Order')}}",
                // icon: 'info',
                text: alertMessage,
                showCancelButton: true,
                confirmButtonText: 'Yes',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: "{{ route('order.changeVendorProductStatus') }}",
                        type: "POST",
                        data: {
                            order_id: order_id,
                            vendor_id: vendor_id,
                            vendor_id: vendor_id,
                            "_token": "{{ csrf_token() }}",
                            status_option_id: status_option_id,
                            order_vendor_id: order_vendor_id,
                            order_product_id: order_product_id,
                            order_vendor_product_id: order_vendor_product_id,
                        },
                        success: function(response) {
                            if(response.status=='error'){
                                Swal.fire({
                                    icon: 'warning',
                                    text: response.message,
                                    showCancelButton: false,
                                    confirmButtonText: 'Ok',
                                })
                            }else if(response.status=='success'){
                                Swal.fire({
                                    icon: 'success',
                                    text: response.message,
                                    showCancelButton: false,
                                    confirmButtonText: 'Ok',
                                })
                            }
                        },
                    });
                }
            });
        });

        // Update document upload handler with new IDs
        $(document).on('submit', '#orderDocumentUploadForm', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('documents.upload') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('#orderUploadResult').html('<p>Uploading...</p>');
                },
                success: function (response) {
                    if (response.status === 200) {
                        $('#orderUploadResult').html('<p class="text-success">' + response.message + '</p>');
                        $('#orderDocumentUploadForm')[0].reset();
                        $('#orderDocuments').val('');
                        // Get active tab's filter type
                        var activeType = $("a.nav-link.active").data('rel');
                        
                        setTimeout(function() {
                            $('#orderDocumentUploadModal').modal('hide');
                            // Update only the current view instead of reloading the page
                            init(activeType, "{{ route('orders.filter') }}", '', false);
                        }, 2000);
                    } else {
                        $('#orderUploadResult').html('<p class="text-danger">' + response.message + '</p>');
                    }
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON;
                    $('#orderUploadResult').html('<p class="text-danger">' + (errors.message || 'Upload failed') + '</p>');
                }
            });
        });
    });
</script>


@endsection
