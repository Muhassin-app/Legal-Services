@extends('layouts.vertical', ['demo' => 'Promo Codes', 'title' => 'Accounting - Promo Codes'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<style>
.dataTables_filter,.toolbar,.dt-buttons.btn-group.flex-wrap {position: absolute;height:40px;}.dataTables_filter{right:0;top: -50px;}
.dataTables_filter label{margin:0;height:40px;}.dataTables_filter label input{margin:0;height:40px;}.dt-buttons.btn-group.flex-wrap{right: 200px;top: -50px;}
.table-responsive{position: relative;overflow:visible;margin-top:10px;}table.dataTable{margin-top:0 !important;}
</style>
@endsection
@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                    </div>
                    <h4 class="page-title">{{ __('Promo Codes') }}</h4>
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
                                        <i class="fas fa-money-check-alt text-success"></i>
                                        <span data-plugin="counterup">{{decimal_format($admin_paid_total_amt)}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Admin Paid Total') }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-success"></i>
                                        <span data-plugin="counterup">{{decimal_format($vendor_paid_total_amt)}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Vendor Paid Total') }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3><i class="mdi mdi-account-group text-danger mdi-24px"></i> <span data-plugin="counterup"> {{$promo_code_uses_count}}</span></h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Promo Code Uses") }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="mdi mdi-eye-outline text-blue mdi-24px"></i>
                                        <span data-plugin="counterup">{{$unique_users_to_use_promo_code_count}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Unique Users To Use Promo Code') }}</p>
                                </div>
                            </div>
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
                                <div class="row">
                                    <div class="col-sm-3 mb-1">
                                        <input type="text" id="range-datepicker" class="form-control al_box_height flatpickr-input" placeholder="2018-10-03 to 2018-10-10" readonly="readonly">
                                    </div>
                                    <div class="col-sm-3 mb-1">
                                        <select class="form-control al_box_height" id="order_status_option_select_box">
                                            <option value="">{{ __("Select Order Status") }}</option>
                                            @forelse($order_status_options as $order_status_option)
                                                <option value="{{$order_status_option->id}}">{{$order_status_option->title}}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="col-sm-3 mb-1">
                                        <select class="form-control al_box_height" name="" id="promo_code_option_select_box">
                                            <option value="">{{ __("Select Coupon Code") }}</option>
                                            @forelse($promo_code_options as $promo_code_option)
                                                <option value="{{$promo_code_option->coupon_id}}">{{$promo_code_option->coupon_code}}</option>
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
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="accounting_vendor_datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __("Order ID") }}</th>
                                    <th>{{ __("Date & Time") }}</th>
                                    <th>{{ __("Customer Name") }}</th>
                                    <th>{{ __("Vendor Name") }}</th>
                                    <th>{{ __("Subtotal Amount") }}</th>
                                    <th>{{ __("Promo Code Discount") }} [{{ __("Vendor Paid Promos") }}]</th>
                                    <th>{{ __("Promo Code Discount") }} [{{ __("Admin Paid Promos") }}]</th>
                                    <th>{{ __("Final Amount") }}</th>
                                    <th>{{ __("Payment Method") }}</th>
                                    <th>{{ __("Order Status") }}</th>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        initDataTable();
        $("#range-datepicker").flatpickr({
            mode: "range",
            onClose: function(selectedDates, dateStr, instance) {
                initDataTable();
            }
        });
        $("#clear_filter_btn_icon").click(function() {
            $('#range-datepicker').val('');
            $('#promo_code_option_select_box').val('');
            $('#order_status_option_select_box').val('');
            initDataTable();
        });
        $("#promo_code_option_select_box, #order_status_option_select_box").change(function() {
            initDataTable();
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
                    className:'btn btn-success waves-effect waves-light',
                    text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>{{__("Export CSV")}}',
                    action: function ( e, dt, node, config ) {
                        window.location.href = "{{ route('account.promo-code.export') }}";
                    }
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
                  url: "{{route('account.promo-code.filter')}}",
                  data: function (d) {
                    d.search = $('input[type="search"]').val();
                    d.date_filter = $('#range-datepicker').val();
                    d.status_filter = $('#order_status_option_select_box option:selected').val();
                    d.promo_code_filter = $('#promo_code_option_select_box option:selected').val();
                  }
                },
                columns: [
                    {data: 'order_number', name: 'order_number', orderable: true, searchable: false, "mRender": function ( data, type, full ) {
                      return "<a href='" + full.view_url + "' target='_blank'>"+full.order_detail.order_number+"</a>";
                      }},
                    {data: 'created_date', name: 'created_date', orderable: false, searchable: false},
                    {data: 'user_name', name: 'user_name', orderable: false, searchable: false},
                    {data: 'vendor.name', name: 'vendor.name', orderable: false, searchable: false},
                    {data: 'subtotal_amount', name: 'subtotal_amount', orderable: false, searchable: false},
                    {data: 'vendor_paid_promo', name: 'vendor_paid_promo', orderable: false, searchable: false},
                    {data: 'admin_paid_promo', name: 'admin_paid_promo', orderable: false, searchable: false},
                    {data: 'payable_amount', name: 'payable_amount', orderable: false, searchable: false},
                    {data: 'payment_option_title', name: 'payment_method', orderable: false, searchable: false},
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
    });
</script>
@endsection
@section('script')
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
@include('backend.export_pdf')
@endsection
