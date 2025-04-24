@extends('layouts.vertical', ['title' => 'Orders'])
@section('content')
@php
$timezone = Auth::user()->timezone;
@endphp
<style type="text/css">
    .ellipsis{
        white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
</style>
    <div class="container-fluid alReturnRequestsPage">

      <!-- Return Page Tabbar start from here -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box mt-2 alReturnRequestsTitle d-md-flex align-items-center justify-content-between">
                    <h4 class="page-title"><button onclick="window.location='{{ route('order.index') }}'" class="back-button" style="border: none"><i class="fa fa-arrow-left" aria-hidden="true"></i></button> {{__('Return Requests')}}</h4>
                    <div class="float-right">
                        <div class="row ">
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
            <div class="col-sm-12 mb-2 d-flex justify-content-end">

            </div>
        </div>
        <div class="row mb-lg-5">
            <div class="col-sm-12 col-lg-12 tab-product pt-0">
                <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="awaiting_review-tab" data-toggle="tab" href="#awaiting-review" role="tab" aria-selected="true" data-rel="pending_request" data-status="Pending"><i
                                class="icofont icofont-ui-home"></i>{{__('Pending Requests')}}</a>
                        <div class="material-border"></div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="processed-tab" data-toggle="tab" href="#processed" role="tab" aria-selected="false" data-rel="accepted_request" data-status="Accepted"><i
                                class="icofont icofont-man-in-glasses"></i>{{__('Accepted Requests')}}</a>
                        <div class="material-border"></div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="rejected-tab" href="#rejected" data-toggle="tab" role="tab" aria-selected="false" data-rel="rejected_request" data-status="Rejected"><i
                                class="icofont icofont-man-in-glasses"></i>{{__('Rejected Requests')}}</a>
                        <div class="material-border"></div>
                    </li>
                </ul>
                <div class="tab-content nav-material" id="top-tabContent">
                    <div class="tab-pane fade show active " id="awaiting-review" role="tabpanel"
                        aria-labelledby="awaiting_review">
                        <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="w-100 common-table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{__('Order id')}}</th>
                                            <th>{{__('Vendor')}}</th>
                                            <th>{{__('Customer Name')}}</th>
                                            <th>{{__('Product')}}</th>
                                            <th>{{__('Product Price')}}</th>
                                            <th>{{__('Order Date & Time')}}</th>
                                            <th>{{__('Return Request Date & Time')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($orders['Pending']) && count($orders['Pending']))
                                        @foreach ($orders['Pending'] as $order)
                                        <tr data-id="{{ $order->id }}" class="show-return-product-modal"  data-status="Pending">
                                            <td>
                                                #{{$order->order->order_number??''}}
                                            </td>
                                            <td class="vendor-name">
                                                <a class="round_img_box mb-1" href="{{ route('vendor.show', $order->product->vendor_id) }}"><img class="rounded-circle" src="{{$order->product->vendor->logo['proxy_url'].'90/90'.$order->product->vendor->logo['image_path']}}" alt="{{$order->product->vendor->id}}"></a>
                                                <a href="{{ route('vendor.show', $order->product->vendor_id) }}">{{$order->product->vendor->name??''}}</a>
                                            </td>

                                            <td>
                                                <a href="#">{{$order->returnBy->name??''}}</a>
                                            </td>
                                           <td class="product-name">
                                                <img class="d-block mb-1" src="{{ $order->product->image['proxy_url'].'50/50'.$order->product->image['image_path']}}">
                                                <b class="text-capitalize">
                                                    {{$order->product->product_name??''}}
                                                </b>
                                            </td>
                                            <td class="">
                                                <b class="text-black">{{@$clientCurrency->currency->symbol}}{{number_format($order->product->price,2)??''}}</b>
                                            </td>
                                            <td>
                                                @if(@$order->order->created_at)
                                                {{ dateTimeInUserTimeZone($order->order->created_at, $timezone)}}
                                                @endif
                                            </td>
                                            <td>{{ dateTimeInUserTimeZone($order->created_at, $timezone)}}</td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr><td colspan="7">{{__('No Records Found')}}</td></tr>
                                        @endif

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="processed" role="tabpanel"
                        aria-labelledby="processed-tab">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="w-100 common-table table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{__('Order id')}}</th>
                                                <th>{{__('Vendor')}}</th>
                                                <th>{{__('Customer Name')}}</th>
                                                <th>{{__('Product')}}</th>
                                                <th>{{__('Product Price')}}</th>
                                                <th>{{__('Date & Time')}}</th>
                                                <th>{{__('Request Date & Time')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($orders['Accepted']) && count($orders['Accepted']))
                                            @foreach ($orders['Accepted'] as $order)
                                            <tr data-id="{{ $order->id }}" class="show-return-product-modal"  data-status="Accepted">
                                                <td>
                                                    #{{$order->order->order_number??''}}
                                                </td>
                                                <td class="vendor-name">
                                                    <a class="round_img_box mb-1" href="{{ route('vendor.show', $order->product->vendor_id) }}"><img class="rounded-circle" src="{{$order->product->vendor->logo['proxy_url'].'90/90'.$order->product->vendor->logo['image_path']}}" alt="{{$order->product->vendor->id}}"></a>
                                                    <a href="{{ route('vendor.show', $order->product->vendor_id) }}">{{$order->product->vendor->name??''}}</a>
                                                </td>

                                                <td>
                                                    <a href="#">{{$order->returnBy->name??''}}</a>
                                                </td>
                                               <td class="product-name">
                                                    <img class="d-block mb-1" src="{{ $order->product->image['proxy_url'].'50/50'.$order->product->image['image_path']}}">
                                                    <b class="text-capitalize">
                                                        {{$order->product->product_name??''}}
                                                    </b>
                                                </td>
                                                <td class="">
                                                    <b class="text-black">{{@$clientCurrency->currency->symbol}}{{number_format($order->product->price,2)??''}}</b>
                                                </td>
                                                <td>{{ dateTimeInUserTimeZone($order->order->created_at, $timezone)}}</td>
                                                <td>{{ dateTimeInUserTimeZone($order->created_at, $timezone)}}</td>
                                            </tr>
                                            @endforeach
                                        @else
                                        <tr><td colspan="7">{{__('No Records Found')}}</td></tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </div>
                    </div>
                    <div class="tab-pane fade " id="rejected" role="tabpanel"
                        aria-labelledby="rejected-tab">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="w-100 common-table table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{__('Order id')}}</th>
                                                <th>{{__('Vendor')}}</th>
                                               <th>{{__('Customer Name')}}</th>
                                                <th>{{__('Product')}}</th>
                                                <th>{{__('Product Price')}}</th>
                                                <th>{{__('Date & Time')}}</th>
                                                <th>{{__('Request Date & Time')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($orders['Rejected']) && count($orders['Rejected']))
                                            @foreach ($orders['Rejected'] as $order)
                                            <tr data-id="{{ $order->id }}" class="show-return-product-modal"  data-status="Rejected">
                                                <td>
                                                    #{{$order->order->order_number??''}}
                                                </td>
                                                <td class="vendor-name">
                                                    <a class="round_img_box mb-1" href="{{ route('vendor.show', $order->product->vendor_id) }}"><img class="rounded-circle" src="{{$order->product->vendor->logo['proxy_url'].'90/90'.$order->product->vendor->logo['image_path']}}" alt="{{$order->product->vendor->id}}"></a>
                                                    <a href="{{ route('vendor.show', $order->product->vendor_id) }}">{{$order->product->vendor->name??''}}</a>
                                                </td>

                                                <td>
                                                    <a href="#">{{$order->returnBy->name??''}}</a>
                                                </td>
                                               <td class="product-name">
                                                    <img class="d-block mb-1" src="{{ $order->product->image['proxy_url'].'50/50'.$order->product->image['image_path']}}">
                                                    <b class="text-capitalize">
                                                        {{$order->product->product_name??''}}
                                                    </b>
                                                </td>
                                                <td class="">
                                                    <b class="text-black">{{@$clientCurrency->currency->symbol}}{{$order->product->price??''}}</b>
                                                </td>
                                                <td>{{ dateTimeInUserTimeZone($order->order->created_at, $timezone)}}</td>
                                                <td>{{ dateTimeInUserTimeZone($order->created_at, $timezone)}}</td>
                                            </tr>
                                            @endforeach
                                        @else
                                        <tr><td colspan="7">{{__('No Records Found')}}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </div>
                    </div>
                </div>
                <div class="pagination pagination-rounded justify-content-end mb-0">
                    {{ $orders[$status]->links() }}
                </div>
            </div>
        </div>

    </div>
<!-- product return modal -->
<div class="modal fade return-order" id="return_order" tabindex="-1" aria-labelledby="return_orderLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
<!-- end product return modal -->
    @endsection

    @section('script')

<script type="text/javascript">

//// ************  return product details   *****************  //
    $('body').on('click', '.show-return-product-modal', function (event) {
        $(".vendor-name").click(function(e) { e.stopPropagation(); });
        event.preventDefault();
        var id = $(this).data('id');
        var status = $(this).data('status');
        var returnurl = "{{route('get-return-product-modal')}}";
        $.get(returnurl+'?id=' + id +'&status=' + status, function(markup){
            $('#return_order').modal('show');
            $('#return-order-form-modal').html(markup);
        });
    });

// New Code
    $(document).ready(function() {
        var table;
        var ajaxCall = 'ToCancelPrevReq';
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        //setTimeout(function(){$('#awaiting_review-tab').trigger('click');}, 200);

        $(document).on("click",".nav-link",function() {
            let rel= $(this).data('rel');
            let status= $(this).data('status');
            init1(rel, status);
        });
        $("#vendor_select_box").change(function() {
            intialize();
        });
        $("#range-datepicker").flatpickr({
            mode: "range",
            onClose: function(selectedDates, dateStr, instance) {
               intialize();
            }
        });
        $("#clear_filter_btn_icon").click(function() {
            $('#range-datepicker').val('');
            $('#vendor_select_box').val('');
            intialize();
        });
        $(document).on("input", "#search_via_keyword", function(e) {
            intialize();
        })
        function intialize()
        {
            let rel= $("a.nav-link.active").data('rel');
            let status= $("a.nav-link.active").data('status');
            init1(rel, status);
        }

        function init1(filter_order_status, status) {
            var date_filter = $('#range-datepicker').val();
            var vendor_id = $('#vendor_select_box option:selected').val();
            var search_keyword = $('#search_via_keyword').val();
            ajaxCall = $.ajax({
                url: "{{route('backend.order.returns.filter')}}",
                type: "POST",
                dataType: "JSON",
                beforeSend: function(){
                    if(ajaxCall !=  'ToCancelPrevReq' && ajaxCall.readyState < 4){
                        ajaxCall.abort();
                    }
                },
                data: {
                    status : status,
                    filter_order_status: filter_order_status,
                    search_keyword: search_keyword,
                    vendor_id: vendor_id,
                    date_filter: date_filter
                },
                success: function(response) {
                    if (response.status == 'Success') {
                        $("#processed").html(response.data.accepted_html);
                        $("#awaiting-review").html(response.data.pending_html);
                        $("#rejected").html(response.data.rejected_html);
                    }
                },
                error: function(data) {
                },
            });
        }

    });

</script>
@endsection
