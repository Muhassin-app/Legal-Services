@extends('layouts.vertical', ['demo' => 'Gift card Lising', 'title' => 'Gift card Lising'])
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
                    <h4 class="page-title">{{ __('Used gift card list') }}</h4>
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
                                    <div class="col-sm-3 mb-1 d-none">
                                        <input type="text" id="range-datepicker" class="form-control al_box_height flatpickr-input" placeholder="2018-10-03 to 2018-10-10" readonly="readonly">
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
                                    {{-- <th>{{ __("Vendor Name") }}</th> --}}
                                    <th>{{ __("Gift Card Used Amount") }}</th>
                                    <th>{{ __("Gift Card Name") }}</th>
                                    <th>{{ __("Gift Card Amount") }}</th>
                                    <th>{{ __("Is Delivery") }}</th>
                                    <th>{{ __("Address") }}</th>
                                    {{-- <th>{{ __("Gift Card Expiry Date") }}</th> --}}
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
                // buttons: [{
                //     className:'btn btn-success waves-effect waves-light',
                //     text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>{{__("Export CSV")}}',
                //     action: function ( e, dt, node, config ) {
                //         window.location.href = "{{ route('account.promo-code.export') }}";
                //     }
                // }],
                buttons: [
                   'csv'
                ],
                ajax: {
                  url: "{{route('gift.card.list.filter')}}",
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
                    {data: 'user_name', name: 'user_name', orderable: false, searchable: true},
                    // {data: 'vendor.name', name: 'vendor.name', orderable: false, searchable: false},
                    {data: 'gift_card_amount', name: 'gift_card_amount', orderable: false, searchable: false},
                    {data: 'gift_name', name: 'gift_name', orderable: false, searchable: true},
                    {data: 'gift_amount', name: 'gift_amount', orderable: false, searchable: false},
                    {data: 'is_delivery', name: 'is_delivery', orderable: false, searchable: false},
                    {data: 'delivery_address', name: 'delivery_address', orderable: false, searchable: false},
                    // {data: 'expiry_date', name: 'expiry_date', orderable: false, searchable: false},
                ]
            });
        }
    });
</script>
@endsection
@section('script')
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
@endsection
