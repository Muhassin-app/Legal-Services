@extends('layouts.vertical', ['title' => 'Order Edit'])
@section('css')
<!-- <style>
td { white-space:pre-line; word-break:break-all}
</style> -->
@endsection
@section('content')
@php
$timezone = Auth::user()->timezone;
@endphp
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ __("Order Edit") }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card mb-0 h-100">

                </div>
            </div>
            <div class="col-lg-8 mb-3">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <h4 class="header-title mb-3">

                            <div class='form-ul'> {{ $vendor_data->name }}

                            </div>



                            @if($order->luxury_option_name != '')
                                <span class="badge badge-info mr-2">{{$order->luxury_option_name}}</span>
                            @endif
                            {{ __("Items from Order") }} #{{$order->order_number}}
                            <a href="{{ route('order.show.detail',[$order->id,$order->vendors->first()->vendor_id])}}">{{__('Show Order')}}</a>
                        </h4>
                        @if($order->luxury_option_id == 2)
                            @foreach($order->vendors as $vendor)
                                <p>{{ $vendor->dineInTableName }} | {{__('Category')}} : {{ $vendor->dineInTableCategory }} | {{__('Capacity')}} : {{ $vendor->dineInTableCapacity }}</p>
                            @endforeach
                        @endif
                        <div class="table-responsive">
                            <table class="table table-bordered table-centered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __("Product Name") }}</th>
                                        <th>{{ __("Product") }}</th>
                                        <th>{{ __("Quantity") }}</th>
                                        <th>{{ __("Price") }}</th>

                                        <th>{{ __("Total") }}</th>
                                    </tr>
                                </thead>
                                @foreach($order->vendors as $vendor)
                                <tbody>
                                    @php
                                    $sub_total = 0;
                                    $taxable_amount = 0;
                                    @endphp
                                    @foreach($vendor->products as $product)
                                    @if($product->order_id == $order->id)
                                    @php
                                    // $taxable_amount += $product->taxable_amount;
                                    // $sub_total += $product->quantity * $product->price;
                                    $taxable_amount = $vendor->taxable_amount;
                                    $vendor_service_fee = $vendor->service_fee_percentage_amount;
                                    $sub_total += $product->total_amount;
                                    @endphp
                                    <tr>
                                        <th scope="row">{{$product->product_name}}
                                            <p class="p-0 m-0">
                                                @if(isset($product->scheduled_date_time)) {{dateTimeInUserTimeZone($product->scheduled_date_time, $timezone)}} @endif
                                            </p>
                                                @foreach($product->prescription as $pres)
                                                <br><a target="_blank" href="{{ ($pres) ? @$pres->prescription['proxy_url'].'74/100'.@$pres->prescription['image_path'] : ''}}">{{($product->prescription) ? 'Prescription' : ''}}</a>
                                                @endforeach

                                                <p class="p-0 m-0">{{ substr($product->product_variant_sets, 0, -2) }}</p>
                                            @if($product->addon && count($product->addon))
                                                <hr class="my-2">
                                                <h6 class="m-0 pl-0"><b>{{__('Add Ons')}}</b></h6>
                                                @foreach($product->addon as $addon)
                                                    <p class="p-0 m-0">{{ $addon->option->translation_title }}</p>
                                                @endforeach
                                            @endif
                                        </th>
                                        <td>
                                            <img src="{{@$product->image_path['proxy_url'].'32/32'.@$product->image_path['image_path']}}" alt="product-img" height="32">
                                        </td>
                                        <td>
                                            <div class="number d-flex justify-content-md-center">
                                                <div class="counter-container d-flex align-items-center">
                                                    <div id="field1">
                                                        <button type="button" id="sub" class="sub">-</button>
                                                        <input type="number" id="1" value="{{ $product->quantity }}" min="1" max="500" />
                                                        <button type="button" id="add" class="add">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{$clientCurrency->currency->symbol}}{{decimal_format($product->price)}}
                                            @if($product->addon->isNotEmpty())
                                                <hr class="my-2">
                                                @foreach($product->addon as $addon)
                                                    <p class="p-0 m-0">{{$clientCurrency->currency->symbol}}{{ decimal_format($addon->option->price_in_cart) }}</p>
                                                    {{-- <p class="p-0 m-0">${{ $addon->option->quantity_price }}</p> --}}
                                                @endforeach
                                            @endif
                                        </td>

                                        <td>{{$clientCurrency->currency->symbol}}{{decimal_format($product->total_amount)}}</td>
                                    </tr>
                                    @endif
                                    @endforeach
                                    <tr>
                                        <th scope="row" colspan="4" class="text-end">{{__('Delivery Fee')}} :</th>
                                        <td>{{$clientCurrency->currency->symbol}}{{decimal_format($vendor->delivery_fee)}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" colspan="4" class="text-end">{{ __("Sub Total") }} :</th>
                                        <td>
                                            <div class="fw-bold">{{$clientCurrency->currency->symbol}}{{decimal_format($sub_total)}}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" colspan="4" class="text-end">{{__('Total Discount')}} :</th>
                                        <td>{{$clientCurrency->currency->symbol}}{{decimal_format($vendor->discount_amount)}}</td>
                                    </tr>

                                    <tr>
                                        <th scope="row" colspan="4" class="text-end">{{ __("Estimated Tax") }} :</th>
                                        <td>{{$clientCurrency->currency->symbol}}{{decimal_format($taxable_amount)}}</td>
                                    </tr>
                                    @if($vendor_service_fee > 0)
                                        <tr>
                                            <th scope="row" colspan="4" class="text-end">{{ __("Service Fee") }} :</th>
                                            <td>{{$clientCurrency->currency->symbol}}{{decimal_format($vendor_service_fee)}}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th scope="row" colspan="4" class="text-end">{{ __("Reject Reason") }} :</th>
                                        <td style="width:200px;">{{$vendor->reject_reason}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" colspan="4" class="text-end">{{ __("Total") }} :</th>
                                        <td>
                                            <div class="fw-bold">{{$clientCurrency->currency->symbol}}{{decimal_format($vendor->payable_amount * $clientCurrency->doller_compare)}}</div>
                                        </td>
                                    </tr>
                                </tbody>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            @if($order->address)
            <div class="col-lg-6 mb-3">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <h4 class="header-title mb-3">{{ __("Delivery Information") }}</h4>
                        <h5 class="font-family-primary fw-semibold">{{$order->user->name}}</h5>
                        <p class="mb-2"><span class="fw-semibold me-2">{{ __("Email") }}:</span> {{ $order->user->email ? $order->user->email : ''}}</p>
                        <p class="mb-2"><span class="fw-semibold me-2">{{ __('Phone')}}:</span> {{'+'.$order->user->dial_code.$order->user->phone_number}}</p>
                        <p class="mb-2"><span class="fw-semibold me-2">{{ __("Address") }}:</span> {{ $order->address->house_number ? $order->address->house_number."," : ''}} {{ $order->address ? $order->address->address : ''}}</p>
                        @if(isset($order->address) && !empty($order->address->street))
                        <p class="mb-2"><span class="fw-semibold me-2">{{__('Street')}}:</span> {{ $order->address ? $order->address->street : ''}}</p>
                        @endif
                        <p class="mb-2"><span class="fw-semibold me-2">{{__('City')}}:</span> {{ $order->address ? $order->address->city : ''}}</p>
                        <p class="mb-2"><span class="fw-semibold me-2">{{ __("State") }}:</span> {{ $order->address ? $order->address->state : ''}}</p>
                        <p class="mb-0"><span class="fw-semibold me-2">{{ getNomenclatureName('Zip Code', true) }}:</span>  {{ $order->address ? $order->address->pincode : ''}}</p>
                    </div>
                </div>
            </div>
            @elseif( ($order->luxury_option_id == 2) || ($order->luxury_option_id == 3) )
            <div class="col-lg-6 mb-3">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <h4 class="header-title mb-3">{{ __("User Information") }}</h4>
                        <h5 class="font-family-primary fw-semibold">{{$order->user->name}}</h5>
                        <p class="mb-2"><span class="fw-semibold me-2">{{ __("Address") }}:</span> {{ $order->user->address->first() ? $order->user->address->first()->address : __('Not Available')}}</p>
                        <p class="mb-0"><span class="fw-semibold me-2">{{ __("Mobile") }}:</span> {{$order->user->phone_number ? $order->user->phone_number : __('Not Available')}}</p>
                        @if(isset($order->address) && !empty($order->address->street))
                        <p class="mb-2"><span class="fw-semibold me-2">{{__('Street')}}:</span> {{ $order->address ? $order->address->street : ''}}</p>
                        @endif
                        <p class="mb-2"><span class="fw-semibold me-2">{{__('City')}}:</span> {{ $order->address ? $order->address->city : ''}}</p>
                        <p class="mb-2"><span class="fw-semibold me-2">{{ __("State") }}:</span> {{ $order->address ? $order->address->state : ''}}</p>
                        <p class="mb-0"><span class="fw-semibold me-2">{{ getNomenclatureName('Zip Code', true) }}:</span>  {{ $order->address ? $order->address->pincode : ''}}</p>

                    </div>
                </div>
            </div>
            @endif

            <div class="col-lg-6 mb-3">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <h4 class="header-title mb-3">{{ __('Payment Information') }}</h4>
                        <p class="mb-2"><span class="fw-semibold me-2">{{ __('Payment By') }} :</span> {{ $order->paymentOption  ? $order->paymentOption->title : ''}}</p>
                        @if($order->payment)
                        <p class="mb-2"><span class="fw-semibold me-2">{{ __('Transaction Id') }} :</span> {{ $order->payment  ? $order->payment->transaction_id : ''}}</p>
                        @endif
                    </div>


                    <div class="card-body">
                        <h4 class="header-title mb-3">{{ __('Comment/Schedule Information') }}</h4>
                        @if($order->comment_for_pickup_driver)
                          <p class="mb-2"><span class="fw-semibold me-2">{{ __('Comment for Pickup Driver') }} :</span> {{ $order->comment_for_pickup_driver ?? ''}}</p>
                        @endif

                        @if($order->comment_for_dropoff_driver)
                          <p class="mb-2"><span class="fw-semibold me-2">{{ __('Comment for Dropoff Driver') }} :</span> {{ $order->comment_for_dropoff_driver ?? ''}}</p>
                        @endif

                        @if($order->comment_for_vendor)
                          <p class="mb-2"><span class="fw-semibold me-2">{{ __('Comment for Vendor') }} :</span> {{ $order->comment_for_vendor ?? ''}}</p>
                        @endif

                        @if($order->schedule_pickup)
                          <p class="mb-2"><span class="fw-semibold me-2">{{ __('Schedule Pickup') }} :</span> {{dateTimeInUserTimeZone($order->schedule_pickup, $timezone)}} </p>
                        @endif

                        @if($order->schedule_dropoff)
                          <p class="mb-2"><span class="fw-semibold me-2">{{ __('Schedule Dropoff') }} :</span> {{dateTimeInUserTimeZone($order->schedule_dropoff, $timezone)}} </p>
                        @endif

                        @if($order->specific_instructions)
                          <p class="mb-2"><span class="fw-semibold me-2">{{ __('Specific instructions') }} :</span> {{ $order->specific_instructions ?? ''}}</p>
                        @endif

                    </div>


                </div>
            </div>


        </div>



    </div>
</div>
<div id="delivery_info_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Delivery Info") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="AddCardBox">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info waves-effect waves-light submitAddForm">{{ __("Submit") }}</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    // quantity plus minus
    $('.add').click(function () {
		if ($(this).prev().val() < 500) {
    	$(this).prev().val(+$(this).prev().val() + 1);
		}
    });

    $('.sub').click(function () {
            if ($(this).next().val() > 0) {
            if ($(this).next().val() > 0) $(this).next().val(+$(this).next().val() - 1);
            }
    });

    $("#order_statuses li").click(function() {
        if (confirm("Are you Sure?")) {
            let that = $(this);
            var status_option_id = that.data("status_option_id");
            var order_vendor_id = that.data("order_vendor_id");
            $.ajax({
                url: "{{ route('order.changeStatus') }}",
                type: "POST",
                data: {
                    order_id: "{{$order->id}}",
                    vendor_id: "{{$vendor_id}}",
                    "_token": "{{ csrf_token() }}",
                    status_option_id: status_option_id,
                    order_vendor_id: order_vendor_id,
                },
                success: function(response) {
                    that.addClass("completed");
                    if (status_option_id == 2) {
                        that.next('li').remove();
                    }
                    if (status_option_id == 3) {
                        that.prev('li').remove();
                        that.nextAll('li').remove();
                    }
                    $('#text_muted_' + status_option_id).html('<small class="text-muted">' + response.created_date + '</small>');
                    if (status_option_id == 2)
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                    location.reload();
                },
            });
        }
    });


    $("#create_dispatch_request").click(function() {
        if (confirm("Are you Sure?")) {
            let that = $(this);
            var order_vendor_id = that.data("order_vendor_id");
            $.ajax({
                url: "{{ route('create.dispatch.request') }}",
                type: "POST",
                data: {
                    order_id: "{{$order->id}}",
                    vendor_id: "{{$vendor_id}}",
                    "_token": "{{ csrf_token() }}",
                    order_vendor_id: order_vendor_id,
                },
                success: function(response) {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", response.status);
                    location.reload();
                },
                error: function(error) {
                var response = $.parseJSON(error.responseText);
                let error_messages = response.message;
                // Swal.fire({
                //    title: "Error!",
                //    text: response.message,
                //    icon: "error",
                //    button: "OK",
                // });
                alert(error_messages);
                location.reload();
                }
            });
        }
    });
</script>
@endsection
