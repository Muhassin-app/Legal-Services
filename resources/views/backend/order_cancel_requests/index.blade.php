@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Order Cancel Requests'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<style type="text/css">
@media(min-width: 1440px){.content{min-height: calc(100vh - 100px);}.dataTables_scrollBody {height: calc(100vh - 500px);}}
.dd-list .dd3-item {list-style: none;}
</style>
<style type="text/css">
    .pac-container,.pac-container .pac-item{z-index:99999!important}.fc-v-event{border-color:#43bee1;background-color:#43bee1}.dd-list .dd3-content{position:relative}span.inner-div{top:50%;-webkit-transform:translateY(-50%);-moz-transform:translateY(-50%);transform:translateY(-50%)}.button{position:relative;padding:8px 16px;background:#009579;border:none;outline:0;border-radius:50px;cursor:pointer}.button:active{background:#007a63}.button__text{font:bold 20px Quicksand,san-serif;color:#fff;transition:all .2s}.button--loading .button__text{visibility:hidden;opacity:0}.button--loading::after{content:"";position:absolute;width:16px;height:16px;top:0;left:0;right:0;bottom:0;margin:auto;border:4px solid transparent;border-top-color:#fff;border-radius:50%;animation:button-loading-spinner 1s ease infinite}@keyframes button-loading-spinner{from{transform:rotate(0turn)}to{transform:rotate(1turn)}}

    .dataTables_filter input[type="search"]{
        height: 30px;
    }
</style>
@endsection
@section('content')
<div class="container-fluid alCancelOrderRequestsPage">
    <div class="row align-items-center">
        <div class="col-sm-12">
            <div class="page-title-box alCancelOrderRequestsPageDashboard align-items-center d-md-flex justify-content-between my-2">
                <h4 class="page-title"><button onclick="window.location='{{ route('order.index') }}'" class="back-button" style="border: none"><i class="fa fa-arrow-left" aria-hidden="true"></i></button> {{ __('Cancel Order Requests') }}</h4>
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
        <div class="col-sm-12 col-lg-12 tab-product vendor-products pt-2 invisible">
            <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="approved-requests" data-toggle="tab" href="#approved_requests" role="tab" aria-selected="false" data-rel="approved_requests_datatable" data-status="1">
                        <i class="icofont icofont-man-in-glasses"></i>{{ __('Approved') }}<sup class="total-items" id="approved_requests_count">({{$approved_requests_count}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pending-requests" data-toggle="tab" href="#pending_requests" role="tab" aria-selected="true" data-rel="pending_requests_datatable" data-status="0">
                        <i class="icofont icofont-ui-home"></i>{{ __('Pending') }}<sup class="total-items">({{$pending_requests_count}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="rejected-requests" data-toggle="tab" href="#rejected_requests" role="tab" aria-selected="false" data-rel="rejected_requests_datatable" data-status="2">
                        <i class="icofont icofont-man-in-glasses"></i>{{ __('Rejected') }}<sup class="total-items">({{$rejected_requests_count}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>
            </ul>
            <div class="tab-content nav-material pt-0" id="top-tabContent">
                <div class="tab-pane fade past-order show active" id="approved_requests" role="tabpanel" aria-labelledby="approved-requests">
                    <div class="row">
                        <div class="col-12">

                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-striped" id="approved_requests_datatable" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Order ID') }}</th>
                                                    <th>{{ __('Vendor') }}</th>
                                                    <th>{{ __('Cancel Reason') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Requested At') }}</th>
                                                    <th>{{ __('Updated At') }}</th>
                                                    <th>{{ __('Updated By') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="approved_body_list"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pending_requests" role="tabpanel" aria-labelledby="awaiting-vendor">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <form name="saveOrder" id="saveOrder"> @csrf</form>
                                        <table class="table table-centered table-nowrap table-striped" id="pending_requests_datatable" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Order ID') }}</th>
                                                    <th>{{ __('Vendor') }}</th>
                                                    <th>{{ __('Cancel Reason') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Requested At') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="pending_body_list"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade past-order" id="rejected_requests" role="tabpanel" aria-labelledby="block-vendor">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-striped" id="rejected_requests_datatable" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Order ID') }}</th>
                                                    <th>{{ __('Vendor') }}</th>
                                                    <th>{{ __('Cancel Reason') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Requested At') }}</th>
                                                    <th>{{ __('Updated At') }}</th>
                                                    <th>{{ __('Updated By') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="rejected_body_list"></tbody>
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
<!-- end vendor request reject order -->

<script type="text/template" id="user_id_section">
    <li class="d-flex justify-content-start align-items-center position-relative" id ="user_selected_<%= id %>" data-section_number="<%= id %>">
        <p class="al_checkbox m-0 py-2 ">
            <input type="hidden" class="user_hidden_ids" name="userIDs[]" value="<%= user_id %>" class="mt-2 mr-1">
            <img class="user_img mr-2" src="<%= image %>" alt="">
        </p>
        <p class="al_username m-0 py-2">
            <span> <%= name %> </span>
            <small><%= email %></small>
        </p>
        <sup class="">&#128473;</sup>
    </li>
</script>
@endsection
@section('script')
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
<script type="text/javascript">
    var cancel_request_update_url = "{{ route('cancel-order.request.status.update') }}";
</script>
@include('backend.order_cancel_requests.pagescript')
<script type="text/javascript">
    var search_text = "{{__('Search By Order ID, '). getNomenclatureName('vendors', false) . __('Name')}}";
    var table_info = '{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}';
</script>
@endsection
