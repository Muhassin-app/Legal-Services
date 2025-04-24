@extends('layouts.vertical', ['demo' => 'Payout list', 'title' => 'Payout Requests - Vendors'])
@section('css')
{{-- <link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> --}}
@endsection
@section('content')
<div class="content">
    <div class="container-fluid alPayoutRequestsPage">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">{{ __("Payout Requests") }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card widget-inline">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup" id="total_earnings_by_vendors">{{$total_order_value}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Total Order Value') }}</p>
                                </div>
                            </div>

                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup" id="total_earnings_by_vendors">{{$total_available_value}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Total Available Funds') }}</p>
                                </div>
                            </div>

                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup" id="total_paid_payouts">{{$pending_payout_value}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0"> {{ __('Pending Payout Value') }}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <h3>
                                        <i class="fas fa-money-check-alt text-primary"></i>
                                        <span data-plugin="counterup" id="total_pending_payouts">{{$completed_payout_value}}</span>
                                    </h3>
                                    <p class="text-muted font-15 mb-0">{{ __('Completed Payout Value') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-12 tab-product pt-0">
                <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="pending-payouts" data-toggle="tab" href="#pending_payouts" role="tab" aria-selected="false" data-rel="pending_payouts_datatable" data-status="0">
                            <i class="icofont icofont-man-in-glasses"></i>{{ __('Pending') }}<sup class="total-items" id="pending_payouts_count">({{$pending_payout_count}})</sup>
                        </a>
                        <div class="material-border"></div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="completed-payouts" data-toggle="tab" href="#completed_payouts" role="tab" aria-selected="true" data-rel="completed_payouts_datatble" data-status="1">
                            <i class="icofont icofont-ui-home"></i>{{ __('Completed') }}<sup class="total-items">({{$completed_payout_count}})</sup>
                        </a>
                        <div class="material-border"></div>
                    </li>
                </ul>
                <div class="tab-content nav-material pt-0" id="top-tabContent">
                    <div class="tab-pane fade past-order show active" id="pending_payouts" role="tabpanel" aria-labelledby="pending-payouts">
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            {{-- <form name="saveOrder" id="saveOrder"> @csrf</form> --}}

                                            <table class="table table-centered table-nowrap table-striped" id="pending_payouts_datatable" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Vendor') }}</th>
                                                        <th>{{ __('Requested By') }}</th>
                                                        <th>{{ __('Amount') }}</th>
                                                        <th>{{ __('Payout Type') }}</th>
                                                        <th class="text-center">{{ __('Action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="pending_payouts_list"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="completed_payouts" role="tabpanel" aria-labelledby="completed-payouts">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            {{-- <form name="saveOrder" id="saveOrder"> @csrf</form> --}}
                                            <table class="table table-centered table-nowrap table-striped" id="completed_payouts_datatble" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Vendor') }}</th>
                                                        <th>{{ _('Requested By') }}</th>
                                                        <th>{{ __('Amount') }}</th>
                                                        <th>{{ __('Payout Type') }}</th>
                                                        <th class="text-center">{{ __('Status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="completed_payouts_list">

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="payout-confirm-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {{-- <div class="modal-header border-0">
                <h4 class="modal-title">Payout</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div> --}}
            <form id="payout_form_final" method="POST" action="{{url('client/account/vendor/payout/request/complete')}}">
                @csrf
                <div>
                    <input type="hidden" name="amount" id="payout_amount" value="">
                    <input type="hidden" name="payout_id" id="payout_id" value="">
                    <input type="hidden" name="payout_option_id" id="payout_method" value="">
                </div>
                <div class="modal-body px-3">
                    <div class="row">
                        <h4 class="modal-title">{{__('Are you sure you want to payout')}}
                            <span id="payout-vendor"></span> for
                            <span id="payout-amount-final"></span>?
                        </h4>
                    </div>
                </div>
                <div class="modal-footer flex-nowrap justify-content-center align-items-center">
                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__('Continue')}}</button>
                    <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">{{__('Cancel')}}</button>
                </div>
            </form>
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
        setTimeout(function(){$('#pending-payouts').trigger('click');}, 200);
        $(document).on("click",".nav-link",function() {
            let rel= $(this).data('rel');
            let status= $(this).data('status');
            initDataTable(rel, status);
        });

        // initDataTable();

        function initDataTable(table, status) {
            $('#'+table).DataTable({
                "destroy": true,
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "iDisplayLength": 20,
                "dom": '<"toolbar">Btrip',
                // language: {
                //     search: "",
                //     paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                //     searchPlaceholder: "Search By "+search_text+" Name"
                // },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                buttons: [],
                ajax: {
                  url: "{{route('account.vendor.payout.requests.filter')}}",
                  data: function (d) {
                    d.status = status;
                //     d.search = $('input[type="search"]').val();
                //     d.date_filter = $('#range-datepicker').val();
                //     d.payment_option = $('#payment_option_select_box option:selected').val();
                //     d.tax_type_filter = $('#tax_type_select_box option:selected').val();
                  }
                },
                columns: [
                    {data: 'date', name: 'date', orderable: false, searchable: false},
                    {data: 'vendorName', name: 'vendorName', orderable: false, searchable: false},
                    {data: 'requestedBy', name: 'requestedBy', orderable: false, searchable: false},
                    {data: 'amount', class:'text-center', name: 'amount', orderable: false, searchable: false},
                    {data: 'type', name: 'type', orderable: false, searchable: false},
                    {data: 'status', class:'text-center', name: 'status', orderable: false, searchable: false, "mRender":function(data, type, full){
                        if(full.status == 'Pending'){
                            return "<button class='btn btn-sm btn-info payout_btn' data-id='"+full.id+"' data-payout_method='"+full.payout_option_id+"' data-vendor='"+full.vendor_id+"'>Payout</button>";
                        }else{
                            return full.status;
                        }
                    }},
                ]
            });
        }

        $(document).delegate(".payout_btn", "click", function(){
            var vendor = $(this).closest('tr').find('td:nth-child(2)').text();
            var amount = $(this).closest('tr').find('td:nth-child(4)').text();
            var dataid = $(this).attr('data-id');
            var vendor_id = $(this).attr('data-vendor');
            var payout_method = $(this).attr('data-payout_method');
            $("#payout-confirm-modal #payout-vendor").html('<b>'+vendor+'</b>');
            $("#payout-confirm-modal #payout-amount-final").text('{{$currency_symbol}}' + amount);
            $("#payout-confirm-modal #payout_amount").val(amount);
            $("#payout-confirm-modal #payout_id").val(dataid);
            $("#payout-confirm-modal #payout_method").val(payout_method);
            $("#payout-confirm-modal").modal('show');
            // if(payout_method == 1){
            //     $("#payout_form_final").attr('action', "{{url('client/account/vendor/payout/request/process')}}");
            // }
        });

        // $(document).on('submit', '#payout_form_final', function(e){
        //     e.preventDefault();
        //     var amount = $("#payout-confirm-modal #payout_amount").val();
        //     var payout_method = $("#payout-confirm-modal #payout_method").val();
        //     if(payout_method == 1){
        //         $(this).trigger("submit");
        //     }
        //     else if(payout_method == 2){
        //         payoutViaStripe(amount, payout_method);
        //     }
        // });

        // function payoutViaStripe(amount, payment_option_id) {
        //     let ajaxData = {};
        //     ajaxData.amount = amount;
        //     ajaxData.payment_option_id = payment_option_id;
        //     $.ajax({
        //         type: "POST",
        //         dataType: 'json',
        //         url: "{{route('vendor.payout.stripe', 2)}}",
        //         data: ajaxData,
        //         success: function(resp) {
        //             if (resp.status == 'Success') {

        //             } else {
        //                 $("#payout_response .alert").html(resp.message).show();
        //                 setTimeout(function(){
        //                     $("#payout_response .alert").hide();
        //                 },5000);
        //                 return false;
        //             }
        //         },
        //         error: function(error) {
        //             var response = $.parseJSON(error.responseText);
        //             $("#payout_response .alert").html(response.message).show();
        //             setTimeout(function(){
        //                 $("#payout_response .alert").hide();
        //             },5000);
        //             return false;
        //         }
        //     });
        // }
    });
</script>
@endsection
@section('script')
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
@endsection
