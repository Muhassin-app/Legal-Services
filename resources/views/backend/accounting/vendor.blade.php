@extends('layouts.vertical', ['demo' => 'Order list', 'title' => 'Accounting - Vendors'])
@section('css')
{{-- <link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> --}}
<style>
.dataTables_filter,.toolbar,.dt-buttons.btn-group.flex-wrap {position: absolute;height:40px;}.dataTables_filter{right:0;top: -50px;}
.dataTables_filter label{margin:0;height:40px;}.dataTables_filter label input{margin:0;height:40px;}.dt-buttons.btn-group.flex-wrap{right: 200px;top: -50px;}
.table-responsive{position: relative;overflow:visible;margin-top:10px;}table.dataTable{margin-top:0 !important;}
</style>
@endsection
@section('content')
<div class="content">
    <div class="container-fluid alVendorsPages">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">{{ __("Vendors") }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card widget-inline">
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-6 col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup" id="total_order_value">{{$total_order_value}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Total Order Value") }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-success"></i>
                                        <span data-plugin="counterup" id="total_delivery_fees">{{$total_delivery_fees}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Total Delivery Fees") }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-success"></i>
                                        <span data-plugin="counterup" id="total_admin_commissions">{{$total_admin_commissions}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Total Admin Commissions") }}</p>
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
                            <div class="col-md-3">
                                 <input type="text" class="form-control al_box_height flatpickr-input" id="range-datepicker" placeholder="2018-10-03 to 2018-10-10" readonly="readonly">
                            </div>
                            <div class="col-sm-3 mb-1">
                                <button type="button" class="btn btn-danger al_box_height waves-effect waves-light" id="clear_filter_btn_icon">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </div>
                        </div>
                   </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="accounting_vendor_datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __("Vendor Name") }}</th>
                                    <th >{{ __("Order Value") }} <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Order Value Without Delivery Fee."></i></th>
                                    <th>{{ __("Delivery Fees") }}</th>
                                    <th>{{ __("Admin Commissions") }}</th>
                                    <th>{{ __("Promo [Vendor]") }}</th>
                                    <th>{{ __("Promo [Admin]") }}</th>
                                    <th>{{ __("Service Fee") }}</th>
                                    <th>{{ __("Fixed Fee") }}</th>
                                    <th>{{ __("Cash Collected") }}</th>
                                    <th>{{ __("Payment Gateway") }}</th>
                                    <th>{{ __("Vendor Earning") }}</th>
                                    <th>{{ __("Tax") }}</th>
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
                getOrderCalculations();
            }
        });

        function initDataTable() {
            $('#accounting_vendor_datatable').DataTable({
                "dom": '<"toolbar">Bfrtip',
                "destroy": true,
                "processing": true,
                "searching": true,
                "responsive": true,
                "serverSide": true,
                "iDisplayLength": 50,
                language: {
                    search: "",
                    info: '{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                    paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                    searchPlaceholder: '{{__("Search By Vendor Name")}}'
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                buttons:[{
                        className:'btn btn-success waves-effect waves-light',
                        text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>{{__("Export CSV")}}',
                        action: function ( e, dt, node, config ) {
                            window.location.href = "{{ route('account.vendor.export') }}";
                        }
                },
                        {
                                extend: 'pdf',
                                text: 'Export to PDF',
                                className:'btn btn-success waves-effect Export_btn waves-light ml-2',
                                id:'exp-btn',
                                text: '<span class="btn-label"><i class="mdi mdi-file-pdf"></i></span>Export PDF',
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
                  url: "{{route('account.vendor.filter')}}",
                  data: function (d) {
                    d.search = $('input[type="search"]').val();
                    d.date_filter = $('#range-datepicker').val();
                  }
                },
                columns: [
                    {data: 'name', name: 'name', orderable: true, searchable: false, "mRender": function ( data, type, full ) {
                      return "<a href='" + full.view_url + "' target='_blank'>"+full.name+"</a>";
                      }},
                    {data: 'order_value', name: 'order_amt', orderable: false, searchable: false},
                    {data: 'delivery_fee', name: 'delivery_fee', orderable: false, searchable: false},
                    {data: 'admin_commission_amount', name: 'admin_commission_amount', orderable: false, searchable: false},
                    {data: 'promo_vendor_amount', name: 'promo_vendor_amount', orderable: false, searchable: false},
                    {data: 'promo_admin_amount', name: 'promo_admin_amount', orderable: false, searchable: false},
                    {data: 'service_fee', name: 'service_fee', orderable: false, searchable: false},
                    {data: 'fixed_fee', name: 'fixed_fee', orderable: false, searchable: false},
                    {data: 'cash_collected_amount', name: 'cash_collected_amount', orderable: false, searchable: false},
                    {data: 'payment_method', name: 'payment_method', orderable: false, searchable: false},
                    {data: 'vendor_earning', name: 'vendor_earning', orderable: false, searchable: false},
                    {data: 'taxable_amount', name: 'taxable_amount', orderable: false, searchable: false},
                ]
            });

        }
        
		function getOrderCalculations(){
			 $.ajax({
                 method:"GET",
                  url: "{{route('account.vendor.calculations')}}",
                  data:{
                    date_filter: $('#range-datepicker').val(),
                  },
                  success:function(response){
                  	$("#total_order_value").html(response.total_order_value);
                  	$("#total_delivery_fees").html(response.total_delivery_fees);
                  	$("#total_admin_commissions").html(response.total_admin_commissions);
                  }
            });
		}
		$("#clear_filter_btn_icon").click(function() {
            $('#range-datepicker').val('');
            initDataTable();
            getOrderCalculations();
            
        });
    });
</script>
@endsection
@section('script')
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
@include('backend.export_pdf')
@endsection
