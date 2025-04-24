@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Order Cancel Requests'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<style type="text/css">
    @media(min-width: 1440px) {
        .content {
            min-height: calc(100vh - 100px);
        }

        .dataTables_scrollBody {
            height: calc(100vh - 500px);
        }
    }

    .dd-list .dd3-item {
        list-style: none;
    }
</style>
<style type="text/css">
    .pac-container,
    .pac-container .pac-item {
        z-index: 99999 !important
    }

    .fc-v-event {
        border-color: #43bee1;
        background-color: #43bee1
    }

    .dd-list .dd3-content {
        position: relative
    }

    span.inner-div {
        top: 50%;
        -webkit-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
        transform: translateY(-50%)
    }

    .button {
        position: relative;
        padding: 8px 16px;
        background: #009579;
        border: none;
        outline: 0;
        border-radius: 50px;
        cursor: pointer
    }

    .button:active {
        background: #007a63
    }

    .button__text {
        font: bold 20px Quicksand, san-serif;
        color: #fff;
        transition: all .2s
    }

    .button--loading .button__text {
        visibility: hidden;
        opacity: 0
    }

    .button--loading::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        border: 4px solid transparent;
        border-top-color: #fff;
        border-radius: 50%;
        animation: button-loading-spinner 1s ease infinite
    }

    @keyframes button-loading-spinner {
        from {
            transform: rotate(0turn)
        }

        to {
            transform: rotate(1turn)
        }
    }

    .dataTables_filter input[type="search"] {
        height: 30px;
    }
</style>
@endsection
@section('content')
<div class="container-fluid alCancelOrderRequestsPage">
    <div class="row align-items-center">
        <div class="col-sm-12">
            <div class="page-title-box alCancelOrderRequestsPageDashboard align-items-center d-md-flex justify-content-between my-2">
                <h4 class="page-title">{{ __('Rental Return Order Form') }}</h4>
                <div class="float-right">
                    <div class="row align-items-center ">
                        <div class="col-sm-4 mb-1">
                            <input type="text" id="range-datepicker" class="form-control flatpickr-input" placeholder="2018-10-03 to 2018-10-10" readonly="readonly">
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
    </div>
    <div class="row">
        <div class="col-sm-12 col-lg-12 tab-product vendor-products pt-2">

            <div class="tab-content nav-material pt-0" id="top-tabContent">

                <div class="tab-pane fade show active" id="pending_requests" role="tabpanel" aria-labelledby="block-vendor">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-striped" id="pending_requests_datatable" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Order ID') }}</th>
                                                    <th>{{ __('Vendor') }}</th>
                                                    <th>{{ __('Product Name') }}</th>
                                                    <th>{{ __('Track') }}</th>

                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($return_form_requests as $return_form_request)
                                                <tr  >
                                                    <td >
                                                        <a href="javascript:;" class="text-body font-weight-bold">
                                                            {{ $return_form_request->order->order_number??'' }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:;" class="text-body font-weight-bold">
                                                            {{ $return_form_request->orderProduct->vendor->name??'' }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:;" class="text-body font-weight-bold">
                                                            {{ $return_form_request->orderProduct->pvariant->title ?? '' }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{str_replace('/tracking/', '/form-attribute/', $return_form_request->dispatch_traking_url)}}" target="_blank" class="text-body font-weight-bold">
                                                            View
                                                        </a>
                                                    <td data-id="{{ $return_form_request->id }}" class="show-return-product-modal"> 
                                                        {{ __('Action') }}
                                                        {{-- <div class="dropdown">
                                                            <a href="#" class="dropdown-toggle arrow-none" data-toggle="dropdown" aria-expanded="false">
                                                                <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="javascript:void(0);" onclick="approveCancelRequest({{ $return_form_request->id }})">{{ __('Approve') }}</a>
                                                                <a class="dropdown-item" href="javascript:void(0);" onclick="rejectCancelRequest({{ $return_form_request->id }})">{{ __('Reject') }}</a>
                                                            </div> --}}

                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">
                                                        {{ __('No Pending Rental Return Order Product Found') }}
                                                    </td>
                                                </tr>
                                                @endforelse
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

    <!-- start vendor request reject order -->
    <div class="modal fade vendor-order-reject order_popop" id="vendor_order_reject" tabindex="-1" aria-labelledby="cancel_orderLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-3">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div id="cancel-order-form-modal">
                        <form id="addRejectReqForm" method="post" class="text-center" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" id="id" value="">
                            <input type="hidden" name="status" id="status" value="">
                            <input type="hidden" name="title" id="title" value="">
                            <p id="error-case" style="color:red;"></p>
                            <label style="font-size:medium;">Enter reason for reject the order. <small>(Optional)</small> </label>
                            <textarea class="reject_reason w-100" data-name="reject_reason" name="reject_reason" id="reject_reason" cols="50" rows="5"></textarea>
                            <button type="button" class="btn btn-info waves-effect waves-light vendorrejectReqSubmit mt-2">{{ __("Submit") }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- product return modal -->
<div class="modal fade return-order" id="return_order" tabindex="-1" aria-labelledby="return_orderLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                    </button>
                <div id="return-order-form-modal">

                </div>


            </div>
        </div>
    </div>
</div>
    @endsection
    @section('script')
    <script src="{{asset('assets/js/intlTelInput.js')}}"></script>
    <script src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
    <script type="text/javascript">
        var cancel_request_update_url = "{{ route('cancel-order.request.status.update') }}";
    </script>

    <script type="text/javascript">
        var search_text = "{{__('Search By Order ID, '). getNomenclatureName('vendors', false) . __('Name')}}";
        var table_info = '{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}';


        $('body').on('click', '.show-return-product-modal', function (event) {
        $(".vendor-name").click(function(e) { e.stopPropagation(); });
        event.preventDefault();
        var id = $(this).data('id');
        // var status = $(this).data('status');
        var returnurl = "{{route('get-rental-return-product-modal')}}";
        $.get(returnurl+'?id=' + id , function(markup){
            $('#return_order').modal('show');
            $('#return-order-form-modal').html(markup);
        });
    });
    </script>
    @endsection