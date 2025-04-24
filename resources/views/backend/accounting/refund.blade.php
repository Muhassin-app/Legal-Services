@extends('layouts.vertical', ['demo' => 'Payout list', 'title' => 'All Refunds'])
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
                    <h4 class="page-title">{{ __("All Refunds") }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    {{-- <form name="saveOrder" id="saveOrder"> @csrf</form> --}}

                    <table class="table table-centered table-nowrap table-striped" id="pending_payouts_datatable" width="100%">
                        <thead>
                            <tr>
                                <th>{{ __('Customer')}} | {{ __('Email')}}  | {{ __('Phone')}}</th>
                                <th>{{ __('Order Number') }}</th>
                                <th>{{ __('Transaction Id') }}</th>
                                <th>{{ __('Refund Id') }}</th>
                                <th>{{ __('Refund Amount') }}</th>
                                <th>{{ __('Destination') }}</th>
                            </tr>
                        </thead>
                        <tbody id="refund_list"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#pending_payouts_datatable').DataTable({
            "destroy": true,
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "searching": false,
            "iDisplayLength": 20,
            "dom": '<"toolbar">Btrip',
            "ajax": "{{route('backend.order.refund.filter')}}",
            'buttons': [],
            "columns": [{
                    "data": "user"
                },
                {
                    data: 'order_detail.order_number',
                    name: 'order_number',
                    orderable: false,
                    searchable: false,
                    "mRender": function(data, type, full) {
                        return "<a href='" + full.view_url + "'>" + full.orderNumber + "</a>";
                    }
                },
                {
                    "data": "transactionId"
                },
                {
                    "data": "Refund_id"
                },
                {
                    "data": "amount"
                },
                {
                    "data": "paid_to_wallet"
                }
            ]
        });
    });
</script>
@endsection
@section('script')
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
@endsection
