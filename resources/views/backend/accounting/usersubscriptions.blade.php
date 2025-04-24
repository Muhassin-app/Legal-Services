@extends('layouts.vertical', ['demo' => 'Order list', 'title' => 'Accounting - Vendors'])
@section('css')
@php
$vendormenu = getNomenclatureName('Vendors', true);
$vendormenulabel = ($vendormenu=="Vendors")?__('Vendors'):__($vendormenu);

@endphp
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
                    <h4 class="page-title">{{ __("Subscriptions") }}</h4>
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
                                        <i class="fas fa-money-check-alt text-success"></i>
                                        <span data-plugin="counterup" id="total_subscription_discount">{{$admin_subs_discount + $vendor_subs_discount}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Total Subscription Discount") }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup" id="admin_subs_discount">{{$admin_subs_discount}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Subscription Discount On Admin's Account") }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-success"></i>
                                        <span data-plugin="counterup" id="vendor_subs_discount">{{$vendor_subs_discount}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Subscription Discount On ".$vendormenulabel."'s Account") }}</p>
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
                        </div>
                   </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="accounting_discount_tbody_list" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __("Order Number") }}</th>
                                    <th>{{ __("Customer") }}</th>
                                    <th>{{ __($vendormenulabel." Name") }}</th>
                                    <th>{{ __("Discount On Admin's Account") }}</th>
                                    <th>{{ __("Discount On ".$vendormenulabel."'s Account") }}</th>
                                    <th>{{ __("Total Subscription Discount") }}</th>
                                </tr>
                            </thead>
                            <tbody id="subscription_tbody">

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

        function initDataTable() {
            $('#accounting_discount_tbody_list').DataTable({
                "dom": '<"toolbar">Bfrtip',
                "destroy": true,
                "processing": true,

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
                buttons: [
                   'csv'
                   ,
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
                            }
                ],
                ajax: {
                  url: "{{route('subscription.list.filter')}}",
                  data: function (d) {
                    d.search = $('input[type="search"]').val();
                    d.date_filter = $('#range-datepicker').val();
                  }
                },
                columns: [
                    {data: 'order_number', name: 'order_number', orderable: false, searchable: false, "mRender": function ( data, type, full ) {
                      return "<a href='" + full.view_url + "' target='_blank'>"+full.order_number+"</a>";
                      }},
                    {data: 'customer', name: 'customer', orderable: false, searchable: false},
                    {data: 'vendor_name', name: 'vendor_name', orderable: true, searchable: false, "mRender": function ( data, type, full ) {
                      return "<a href='" + full.vendor_view_url + "' target='_blank'>"+full.vendor_name+"</a>";
                      }},
                    {data: 'admin_subscription_amount', name: 'admin_subscription_amount', orderable: false, searchable: false},
                    {data: 'vendor_subscription_amount', name: 'vendor_subscription_amount', orderable: false, searchable: false},
                    {data: 'total_subscription_amount', name: 'total_subscription_amount', orderable: false, searchable: false},
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
