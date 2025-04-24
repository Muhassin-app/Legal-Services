@extends('layouts.vertical', ['demo' => 'Orders', 'title' => 'Accounting - Orders'])
@section('css')
{{-- <link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> --}}
<style>
.dataTables_filter,.toolbar,.dt-buttons.btn-group.flex-wrap {position: absolute;height:40px;}.dataTables_filter{right:0;top: -50px;}
.dataTables_filter label{margin:0;height:40px;}.dataTables_filter label input{margin:0;height:40px;}.dt-buttons.btn-group.flex-wrap{right: 200px;top: -50px;}
.table-responsive{position: relative;overflow:visible;margin-top:10px;}table.dataTable{margin-top:0 !important;}
div.dataTables_wrapper div.dataTables_filter input {width: 285px;}
.dt-buttons.btn-group.flex-wrap {right: 310px;top: -50px;}
</style>

@endsection
@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    @php
                        $ordermenu = getNomenclatureName('Orders', true);
                        $ordermenulabel = ($ordermenu=="Orders")?__('Orders'):__($ordermenu);
                    @endphp
                    <h4 class="page-title">{{ __($ordermenulabel) }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card widget-inline">
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="mdi mdi-cash-multiple text-primary mdi-24px"></i>
                                        <span data-plugin="counterup" id="total_earnings_by_vendors">{{$total_earnings_by_vendors}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Total Order Value') }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="mdi mdi-cart-arrow-up text-primary mdi-24px"></i>
                                        <span data-plugin="counterup" id="total_order_count">{{$total_order_count}}</span>
                                    </h3>
                                    @php
                                        $ordermenu = getNomenclatureName('Orders', true);
                                        $ordermenulabel = ($ordermenu=="Orders")?__('Orders'):__($ordermenu);

                                    @endphp
                                    <p class="text-muted font-15 mb-0">{{ __('Total '. $ordermenulabel) }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup" id="total_cash_to_collected">{{$total_cash_to_collected}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Total Cash To Be Collected') }}</p>
                                </div>
                            </div>
                            <!-- <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup" id="total_delivery_fees">{{$total_delivery_fees}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Total Delivery Fees') }}</p>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body position-relative al">
                    <div class="top-input position-absoluteal">
                        <div class="row">
                            <div class="col-md-12">
                                <form  action="{{route('account.order.export')}}" id="export-form" method="GET" >
                                <div class="row">
                                    <div class="col-sm-3 mb-1">
                                        <input type="text" name="date_range" id="range-datepicker" class="form-control al_box_height flatpickr-input" placeholder="2018-10-03 to 2018-10-10" readonly="readonly">
                                    </div>
                                    <div class="col-sm-3 mb-1">
                                        <select class="form-control al_box_height " id="vendor_select_box" name="vendor">
                                            <option value="">{{ __('Select Vendor') }}</option>
                                            @forelse($vendors as $vendor)
                                                <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="col-sm-3 mb-1">
                                        <select class="form-control al_box_height" id="order_status_option_select_box" name="order_status" >
                                            <option value="">{{ __('Select Order Status') }}</option>
                                            @forelse($order_status_options as $order_status_option)
                                                <option value="{{$order_status_option->id}}">{{$order_status_option->title}}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="col-sm-3 mb-1">
                                        <select class="form-control al_box_height" id="company_option_select_box" name="company_id" >
                                            <option value="">{{ __('Select Company') }}</option>
                                            @forelse($companies as $company)
                                                <option value="{{$company->id}}">{{$company->name}}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="col-sm-3 mb-1">
                                        <button type="button" class="btn btn-danger al_box_height waves-effect waves-light" id="clear_filter_btn_icon">
                                            <i class="mdi mdi-close"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                   </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="accounting_vendor_datatable" width="100%">
                            <thead>
                                <tr>
                                    <!-- <th>{{ __('Order ID') }}</th> -->
                                    <th>{{ __('Order Number') }}</th>
                                    <th>{{ __('Date & Time') }}</th>
                                    <th>{{ __('Customer Name') }}</th>
                                    <th>{{ __('Vendor') }}</th>
                                    <th>{{ __('Lawyer Earning') }}</th>
                                    <th>{{ __('Subtotal Amount') }}</th>
                                    @if(auth()->user()->is_superadmin ==1)
                                        <th>{{ __('Markup Price') }}({{ __("Visible For Admin") }})</th>
                                    @endif
                                    <th>{{ __('Promo Code Discount') }}</th>
                                     <th>{{ __('Tax') }}</th>
                                    <th>{{ __('Delivery Fee') }}</th>
                                    <th>{{ __('Service Fee') }}</th>
                                    <th>{{ __('Fixed Fee') }}</th>
                                    <th>{{ __('Tip Amount') }}</th>
                                    <!-- <th>{{ __('Admin Commission') }} [{{ __("Fixed") }}]</th> -->
                                    <th>{{ __('Admin Commission') }} [%{{ __("Age") }}]
                                    <a href="javascript:void(0);" onclick="alert('First, it shows the total admin commission of the sub total amount then it shows the total percentage value.');" rel="noopener noreferrer"> <i class="fa fa-info-circle"></i> </a>
                                    </th>
                                    <th>{{ __('Final Amount') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Order Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="accounting_vendor_tbody_list">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var table;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
       function getPercentageAmount(percent,amount){
            // var totalPercent = (percent/amount * 100);
           if(amount == 0.00){
            	return amount;
            }else{
            	var totalPercent = (percent * 100) / amount; // Added by ovi
            	return parseFloat(totalPercent).toFixed(2);
            }
            
        }


        function numberWithCommas(x) {
        // x=x.toFixed(2)
            if(x > 0){
                var digit_count = "{{$client_preference_detail->digit_after_decimal}}";
                if(digit_count)
                {
                    x = parseFloat(x).toFixed(digit_count);
                }
                var parts = x.split(".");
                return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ((parts[1] !== undefined) ? "." + parts[1] : "");
            }else{
                return 0;
            }
        }
        getOrderList();
        function getOrderList() {
            $(document).ready(function() {
                initDataTable();
                $("#range-datepicker").flatpickr({
                    mode: "range",
                    onClose: function(selectedDates, dateStr, instance) {
                        initDataTable();
                        getOrderCalculations();
                    }
                });
                $("#clear_filter_btn_icon").click(function() {
                    $('#range-datepicker').val('');
                    $('#vendor_select_box').val('');
                    $('#order_status_option_select_box').val('');
                    initDataTable();
                    getOrderCalculations();
                    
                });
                $("#vendor_select_box, #order_status_option_select_box").change(function() {
                    initDataTable();
                    getOrderCalculations();
                });
                $("#vendor_select_box, #company_option_select_box").change(function() {
                    initDataTable();
                    getOrderCalculations();
                });
                function initDataTable() {
                    $('#accounting_vendor_datatable').DataTable({
                        "dom": '<"toolbar">Bfrtip',
                        "scrollX": true,
                        "destroy": true,
                        "processing": true,
                        "serverSide": true,
                        "iDisplayLength": 50,
                        language: {
                            search: "",
                            info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                            paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                            searchPlaceholder: '{{__("Search By Order No.,Vendor,Customer Name")}}'
                        },
                        drawCallback: function () {
                            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                        },
                        buttons: [{
                                className:'btn btn-success waves-effect Export_btn waves-light',
                                id:'exp-btn',
                                text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>{{__("Export CSV")}}',
                                action: function ( e, dt, node, config ) {
                                    //window.location.href = "{{ route('account.order.export') }}";
                                    $('#export-form').trigger('submit');
                                },
                                
                            },
                            {
                                extend: 'pdf',
                                text: 'Export to PDF',
                                className:'btn btn-success waves-effect Export_btn waves-light ml-2',
                                id:'exp-btn',
                                text: '<span class="btn-label"><i class="mdi mdi-file-pdf-box"></i></span>Export PDF',
                                orientation: 'landscape',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                customize: function (doc) {
                                doc.pageOrientation = 'landscape';
                                doc.pageSize = 'A3'; // Set the custom page size
                            }
                            }],
                        ajax: {
                          url: "{{route('account.order.filter')}}",
                          data: function (d) {
                            d.search = $('input[type="search"]').val();
                            d.date_filter = $('#range-datepicker').val();
                            d.vendor_id = $('#vendor_select_box option:selected').val();
                            d.status_filter = $('#order_status_option_select_box option:selected').val();
                            d.company_filter = $('#company_option_select_box option:selected').val();
                          }
                        },
                        columns: [
                            {data: 'order_number', name: 'order_number',orderable: false, searchable: false},
                            {data: 'created_date', name: 'name',orderable: false, searchable: false},
                            {data: 'user_name', name: 'Customer Name',orderable: false, searchable: false},
                            {data: 'vendor_name', name: 'vendor_name', orderable: false, searchable: false},
                             {data: 'vendor_amount', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},
                            {data: 'subtotal_amount', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},
                            @if(auth()->user()->is_superadmin ==1)
                            {data: 'markup_price', name: 'action', orderable: false, searchable: false},
                            @endif

                            {data: 'discount_amount', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},

                            {data: 'taxable_amount', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},
                            {data: 'delivery_fee', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},

                            {data: 'service_fee_percentage_amount', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},
							{data: 'fixed_fee', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},
                            {data: 'tip_amount', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},
                            // {data: 'admin_commission_fixed_amount', name: 'action', orderable: false, searchable: false,
                            // "mRender": function(data, type, full) {
                            //     return numberWithCommas(data);
                            // }},

                            {data: 'admin_commission', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                            	var discount = 0.00;
                            	var amount = full.subtotal_amount;
                            	if(full.coupon_paid_by == 0){
                            		discount = full.discount_amount;
                            	}
                            	amount = amount - discount;
                                return data+" ("+getPercentageAmount(data,amount)+"%)";
                                // return numberWithCommas(data)+" ("+getPercentageAmount(data,full.subtotal_amount)+"%)";
                            }},
                            {data: 'total_price', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return numberWithCommas(data);
                            }},
                            {data: 'order_detail.payment_option.title', name: 'action', orderable: false, searchable: false,
                            "mRender": function(data, type, full) {
                                return full.payment_option_title;
                            }
                        },
                            {data: 'order_status', name: 'order_status', orderable: false, searchable: false, "mRender": function ( data, type, full ) {
                                var color = "";
                                if(full.order_status == 'Placed'){
                                    color = "secondary";
                                }else if(full.order_status == "Accepted"){
                                    color = "warning";
                                }else if(full.order_status == "Processing"){
                                    color = "blue";
                                }else if(full.order_status == "Rejected"){
                                    color = "danger";
                                }else if(full.order_status == "Out For Delivery"){
                                    color = "info";
                                }else if(full.order_status == "Delivered"){
                                    color = "success";
                                }
                              return "<h5><span class='badge badge-"+color+"'>"+full.order_status+"</span></h5>";
                            }},
                        ]
                    });
                }

				function getOrderCalculations(){
					 $.ajax({
                         method:"GET",
                          url: "{{route('account.order.calculations')}}",
                          data:{
                            search: $('input[type="search"]').val(),
                            date_filter: $('#range-datepicker').val(),
                            vendor_id :$('#vendor_select_box option:selected').val(),
                            status_filter : $('#order_status_option_select_box option:selected').val()
                          },
                          success:function(response){
                          	$("#total_earnings_by_vendors").html(response.total_earnings_by_vendors);
                          	$("#total_cash_to_collected").html(response.total_cash_to_collected);
                          	$("#total_delivery_fees").html(response.total_delivery_fees);
                          	$("#total_order_count").html(response.total_order_count);
                          }
                        });
				
				}
            });
        }
    });
    $('.Export_btn').on('click',function(){
        alert('hi');
        return false;
    });
</script>
@endsection
@section('script')
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
@include('backend.export_pdf')
@endsection
