@extends('layouts.vertical', ['demo' => 'Order list', 'title' => 'Accounting - Vendors'])
@section('css')
{{-- <link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> --}}
@endsection
@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">{{ __("Vendor Payouts") }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card widget-inline">
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup">{{$total_order_value}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Total Order Value") }}</p>
                                </div>
                            </div>
                            {{-- <div class="col-sm-6 col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-success"></i>
                                        <span data-plugin="counterup" id="total_delivery_fees">{{$total_delivery_fees}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __("Total Delivery Fees") }}</p>
                                </div>
                            </div> --}}
                            <div class="col-sm-6 col-md-4 mb-3 mb-md-0">
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
                    {{-- <div class="top-input position-absoluteal">
                        <div class="row">
                            <div class="col-md-3">
                                 <input type="text" class="form-control al_box_height flatpickr-input" id="range-datepicker" placeholder="2018-10-03 to 2018-10-10" readonly="readonly">
                            </div>
                        </div>
                   </div> --}}
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="accounting_vendor_datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __("Vendor Name") }}</th>
                                    {{-- <th>{{ __("Stripe Account") }}</th> --}}
                                    <th >{{ __("Order Value") }} <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Order Value Without Delivery Fee."></i></th>
                                    {{-- <th>{{ __("Delivery Fees") }}</th> --}}
                                    <th>{{ __("Admin Commissions") }}</th>
                                    {{-- <th>{{ __("Promo [Vendor]") }}</th> --}}
                                    {{-- <th>{{ __("Promo [Admin]") }}</th> --}}
                                    <th>{{ __("Vendor Earning") }}</th>
                                    <th>Stripe Status</th>
                                    <th>Payout</th>
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

        // $("#range-datepicker").flatpickr({
        //     mode: "range",
        //     onClose: function(selectedDates, dateStr, instance) {
        //         initDataTable();
        //     }
        // });

        function initDataTable() {
            $('#accounting_vendor_datatable').DataTable({
                "dom": '<"toolbar">frtip', //'<"toolbar">Bfrtip',
                "destroy": true,
                "processing": true,
                "serverSide": true,
                "iDisplayLength": 50,
                language: {
                    search: "",
                    info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                    paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                    searchPlaceholder: "Search By Vendor Name"
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                // buttons:[{
                //         className:'btn btn-success waves-effect waves-light',
                //         text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>Export CSV',
                //         action: function ( e, dt, node, config ) {
                //             window.location.href = "{{ route('account.vendor.export') }}";
                //         }
                // }],
                ajax: {
                  url: "{{route('account.vendor.payout.filter')}}",
                  data: function (d) {
                    d.search = $('input[type="search"]').val();
                    d.date_filter = $('#range-datepicker').val();
                  }
                },
                columns: [
                    {data: 'name', name: 'name', orderable: true, searchable: false, "mRender": function ( data, type, full ) {
                        return "<a href='" + full.view_url + "' target='_blank'>"+full.name+"</a>";
                      }
                    },
                    // {"mRender": function ( data, type, full ) {
                    //     var html = "<a class='stripe_account_setup_link' href=''>Stripe Connect</a>";
                    //     return html;
                    //   }
                    // },
                    {data: 'order_value', name: 'order_amt', orderable: false, searchable: false},
                    // {data: 'delivery_fee', name: 'delivery_fee', orderable: false, searchable: false},
                    {data: 'admin_commission_amount', name: 'admin_commission_amount', orderable: false, searchable: false},
                    // {data: 'promo_vendor_amount', name: 'promo_admin_amount', orderable: false, searchable: false},
                    // {data: 'promo_admin_amount', name: 'promo_admin_amount', orderable: false, searchable: false},
                    {data: 'vendor_earning', name: 'vendor_earning', orderable: false, searchable: false},
                    {"mRender": function ( data, type, full ) {
                        if(full.is_stripe_connected == 1){
                            var html = "<span class='text-success'>Connected</span>";
                        }else{
                            var html = "<span class='text-danger'>Not Connected</span>";
                        }
                        return html;
                      }
                    },
                    {"mRender": function ( data, type, full ) {
                        var html = "<a class='btn btn-sm btn-info waves-effect waves-light manual_payout_btn' href='javascript:void(0)'>Manual</a>";
                        return html;
                      }
                    }
                ]
            });

        }
    });
</script>
@endsection
@section('script')
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
@endsection
