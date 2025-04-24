<style>
table.wp-table.w-100 tr:nth-child(even){
    background-color: #fff;
}
table.wp-table.w-100 tr td, table.wp-table.w-100 tr th {
padding: 7px 15px;
}
.alOrderImg img {
    width: 100%;
    border-radius: 15px;
    margin-bottom: 10px;
}
.status_box li{margin-right: 10px;}
.status_box li label{margin:0;}
</style>
<div class="tab-pane fade {{ Request::query('pageType') == 'LongTermOrders' ? 'active show' : '' }}"
    id="long_term_order" role="tabpanel" aria-labelledby="long_term_order-tab">
    <div class="row">
        @if ($longTermOrder->isNotEmpty())
            @foreach ($longTermOrder as $key => $order)
                @php

                    $total_other_taxes = 0.0;
                    foreach (explode(':', $order->total_other_taxes) as $row) {
                        $total_other_taxes += (float) $row;
                    }

                @endphp
                <div class="col-12">
                    <div class="row no-gutters order_head">
                        <div class="col-md-3 alOrderStatus">
                            <h4>{{ __('Order Number') }}</h4>
                            <span>#{{ $order->order_number }}</span>
                        </div>
                        <div class="col-md-3 alOrderStatus">
                            <h4>{{ __('Date & Time') }}</h4>
                            <span>{{ dateTimeInUserTimeZone($order->created_at, $timezone) }}</span>
                        </div>
                        <div class="col-md-3 alOrderStatus">
                            <h4>{{  __('Customer Name') }}</h4>
                            <span><a class="text-capitalize">{{ $order->user->name }}</a></span>
                        </div>
                        @if ($client_preference_detail->business_type != 'taxi')
                            <div class="col-md-3 ellipsis">
                                <h4>{{ __('Address') }}</h4>
                                <div class="alOrderAddressBox">
                                    @include('frontend.account.orders.order_address')
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="row no-gutters order_data d-none">
                        <div class="col-md-3">#{{ $order->order_number }}
                        </div>
                        {{-- <div class="col-md-3">{{convertDateTimeInTimeZone($order->created_at, $timezone, 'l, F d, Y, h:i A')}}</div> --}}
                        <div class="col-md-3">
                            {{ dateTimeInUserTimeZone($order->created_at, $timezone) }}
                        </div>
                        <div class="col-md-3">
                            <a class="text-capitalize">{{ $order->user->name }}</a>
                        </div>
                        @if ($client_preference_detail->business_type != 'taxi')
                            <div class="col-md-3 ellipsis  asdas">
                                <h4>{{ __('Address') }}</h4>
                                <div class="alOrderAddressBox">
                                    @include('frontend.account.orders.order_address')
                                </div>

                            </div>
                        @endif
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-9 mb-3">
                            @php
                                $subtotal_order_price = $total_order_price = $total_tax_order_price = 0;
                            @endphp
                            @foreach ($order->vendors as $key => $vendor)
                                @php
                                    $product_total_count = $product_subtotal_amount = $product_taxable_amount = 0;
                                @endphp
                                <div
                                    class="order_detail order_detail_data align-items-top pb-3 card-box no-gutters mb-0">
                                    @if ($vendor->delivery_fee > 0 || !empty($order->scheduled_date_time) || $order->luxury_option_id > 0)
                                        <div
                                            class="progress-order font-12  d-flex align-items-center justify-content-between pr-2">
                                            @if ($order->luxury_option_id > 0)
                                                @php
                                                    $luxury_option = \App\Models\LuxuryOption::where('id', $order->luxury_option_id)->first();
                                                    if ($luxury_option->title == 'takeaway') {
                                                        $luxury_option_name = getNomenclatureName('Takeaway', Session::get('customerLanguage'), false);
                                                    } elseif ($luxury_option->title == 'dine_in') {
                                                        $luxury_option_name = 'Dine-In';
                                                    } else {
                                                        //  $luxury_option_name = 'Delivery';
                                                        $luxury_option_name = getNomenclatureName($luxury_option->title);
                                                    }
                                                @endphp
                                                <span
                                                    class="badge badge-info ml-2 my-1">{{ __($luxury_option_name) }}</span>
                                            @endif

                                            @if ($order->is_gift == '1')
                                                <div class="gifted-icon">
                                                    <img class="p-1 align-middle"
                                                        src="{{ asset('assets/images/gifts_icon.png') }}"
                                                        alt="">
                                                    <span class="align-middle">{{__('This
                                                        is a gift.')}}</span>
                                                </div>
                                            @endif
                                            @if ($clientData->socket_url != '')
                                                <a class="start_chat chat-icon btn btn-solid"
                                                    data-vendor_order_id="{{ $vendor->id }}"
                                                    data-vendor_id="{{ $vendor->vendor_id }}" data-orderid=""
                                                    data-order_id="{{ $order->id }}">{{ __('Chat') }}</a>
                                                @if (isset($vendor->driver_chat) && $vendor->driver_chat == 1 && $vendor->dispatch_traking_url != '')
                                                    <a class="start_chat_driver chat-icon btn btn-solid"
                                                        data-driver_details_api="{{ $vendor->dispatch_traking_url }}"
                                                        data-vendor_order_id="{{ $vendor->id }}"
                                                        data-vendor_id="{{ $vendor->vendor_id }}" data-orderid=""
                                                        data-order_id="{{ $order->id }}">{{ __('Driver Chat') }}</a>
                                                @endif
                                            @endif
                                            {{-- @if ($vendor->order_status )
                                                <div class="gifted-icon">
                                                    <span class="align-middle text-Capitalize">{{$vendor->order_status}}</span>
                                                </div>
                                            @endif --}}
                                        </div>
                                    @endif
                                    <span class="left_arrow pulse"></span>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <h5 class="m-0">
                                                {{ __('Order Status') }}</h5>
                                            <ul class="status_box d-flex align-items-center">
                                                @if (!empty($vendor->order_status))
                                                    <li>
                                                        @if ($vendor->order_status == 'placed')
                                                            <img src="{{ asset('assets/images/order-icon.svg') }}"
                                                                alt="">
                                                        @elseif($vendor->order_status == 'accepted')
                                                            <img src="{{ asset('assets/images/payment_icon.svg') }}"
                                                                alt="">
                                                        @elseif($vendor->order_status == 'processing')
                                                            <img src="{{ asset('assets/images/customize_icon.svg') }}"
                                                                alt="">
                                                        @elseif($vendor->order_status == 'out for delivery')
                                                            <img src="{{ asset('assets/images/driver_icon.svg') }}"
                                                                alt="">
                                                        @endif
                                                        <label
                                                            class="m-0 in-progress">{{ __(ucfirst($vendor->order_status)) }}</label>
                                                    </li>
                                                @endif

                                                @if (!empty($vendor->dispatch_traking_url))
                                                    <li>
                                                        <img src="{{ asset('assets/images/order-icon.svg') }}"
                                                            alt="">
                                                        <a class="alOrderDetailsLink"
                                                            href="{{ route('front.booking.details', $order->order_number) }}"
                                                            target="_blank">{{ __('Details') }}</a>
                                                    </li>
                                                @endif
                                                @if ($vendor->order_status_option_id == 1 && $client_preference_detail->is_cancel_order_user == 1)
                                                    <?php
                                                    if ($clientPreference->business_type == 'laundry') {
                                                        $pickup_cancelling_charges = $clientCurrency->currency->symbol . $vendor->vendor->pickup_cancelling_charges;
                                                    }
                                                    ?>

                                                    <li>
                                                        @if ($clientPreference->business_type == 'laundry')
                                                            <label class="rating-star cancel_order"
                                                                id="cancel_order_{{ $order->order_number }}"
                                                                data-pickup_order="{{ date('Y-m-d', strtotime(dateTimeInUserTimeZone($order->schedule_pickup, $timezone))) }}"
                                                                data-order_id="{{ $order->id }}"
                                                                data-pickup_cancelling_charges="{{ $pickup_cancelling_charges }}"
                                                                data-order_number="{{ $order->order_number }}"
                                                                data-order_vendor_id="{{ $vendor->vendor_id ?? 0 }}"
                                                                data-id="{{ $vendor->id ?? 0 }}">
                                                                {{ __('Cancel Order') }}
                                                            </label>
                                                        @else
                                                            <label class="rating-star cancel_order"
                                                                data-order_vendor_id="{{ $vendor->vendor_id ?? 0 }}"
                                                                data-id="{{ $vendor->id ?? 0 }}">
                                                                {{ __('Cancel Order') }}
                                                            </label>
                                                        @endif
                                                        </li>
                                                @endif
                                                @if ($vendor->dineInTable)
                                                    <li>
                                                        <h5 class="mb-1">
                                                            {{ __('Dine-in') }}
                                                        </h5>
                                                        <h6 class="m-0">
                                                            {{ $vendor->dineInTableName }}
                                                        </h6>
                                                        <h6 class="m-0">
                                                            Category :
                                                            {{ $vendor->dineInTableCategory }}
                                                        </h6>
                                                        <h6 class="m-0">
                                                            Capacity :
                                                            {{ $vendor->dineInTableCapacity }}
                                                        </h6>
                                                    </li>
                                                @endif

                                            </ul>
                                        </div>
                                        <div class="col-md-5">
                                            <ul class="price_box_bottom m-0 p-0">
                                                <li class="d-flex align-items-center justify-content-between mb-2">
                                                    <label class="m-0">{{ __('Product Total') }}</label>
                                                    <span>{{ Session::get('currencySymbol') }}{{ decimal_format($vendor->subtotal_amount * $clientCurrency->doller_compare) }}</span>
                                                </li>
                                                @if ($vendor->discount_amount > 0)
                                                    <li class="d-flex align-items-center justify-content-between  mb-2">
                                                        <label class="m-0">{{ __('Coupon Discount') }}</label>
                                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($vendor->discount_amount * $clientCurrency->doller_compare) }}</span>
                                                    </li>
                                                @endif
                                                @if ($order->fixed_fee_amount > 0)
                                                    <li class="d-flex align-items-center justify-content-between  mb-2">
                                                        <label class="m-0">{{ __($fixedFee) }}</label>
                                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->fixed_fee_amount * $clientCurrency->doller_compare) }}</span>
                                                    </li>
                                                @endif
                                                @if ($vendor->delivery_fee > 0)
                                                    <li class="d-flex align-items-center justify-content-between  mb-2">
                                                        <label class="m-0">{{ __('Delivery Fee') }}</label>
                                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($vendor->delivery_fee * $clientCurrency->doller_compare) }}</span>
                                                    </li>
                                                @endif
                                                <li class="grand_total d-flex align-items-center justify-content-between mb-2">
                                                    <label class="m-0">{{ __('Amount') }}</label>
                                                    @php
                                                        $product_subtotal_amount = $product_total_count - $vendor->discount_amount + $vendor->delivery_fee;
                                                        $subtotal_order_price += $product_subtotal_amount;
                                                    @endphp
                                                    <span>{{ Session::get('currencySymbol') }}{{ decimal_format($vendor->payable_amount + $order->fixed_fee_amount * $clientCurrency->doller_compare) }}</span>
                                                </li>
                                                {{-- Check if order is created only --}}

                                            </ul>
                                        </div>
                                        <?php
                                        $pkup = json_encode(date('Y-m-d', strtotime(dateTimeInUserTimeZone($order->schedule_pickup, $timezone))));
                                        $dpoff = json_encode(date('Y-m-d', strtotime(dateTimeInUserTimeZone($order->schedule_dropoff, $timezone))));
                                        $rescheduling_charges = json_encode($clientCurrency->currency->symbol . $vendor->vendor->rescheduling_charges);
                                        $pickup_cancelling_charges = json_encode($clientCurrency->currency->symbol . $vendor->vendor->pickup_cancelling_charges);
                                        $newPickupClass = json_encode('pickup_' . $order->order_number);
                                        $newDropoffClass = json_encode('dropoff_' . $order->order_number);
                                        ?>
                                        @if ($clientPreference->business_type == 'laundry')
                                            {{-- Model to get Slots --}}
                                            <form
                                                onsubmit='checkDates({{ $pkup }}, {{ $dpoff }}, {{ $rescheduling_charges }}, {{ $pickup_cancelling_charges }}, {{ $newPickupClass }}, {{ $newDropoffClass }})'
                                                action="{{ route('user.rescheduleOrder') }}" class="rescheduleOrder"
                                                id="rescheduleOrder" name="rescheduleOrder" method="post">
                                                @csrf
                                                <div class="modal fade" id="orderModel{{ $order->id }}"
                                                    tabindex="-1" role="dialog"
                                                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg"
                                                        role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header"
                                                                style="border-bottom: 1px solid #e5e8eb;">
                                                                <h5 class="modal-title text-capitalize"
                                                                    id="exampleModalLongTitle">Order Number:
                                                                    #{{ $order->order_number }}</h5>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="order_id"
                                                                    value="{{ Crypt::encrypt($order->id) }}">
                                                                @include('frontend.modals.rescheduling_modal',
                                                                    [
                                                                        'newPickupClass' =>
                                                                            'pickup_' . $order->order_number,
                                                                        'newDropoffClass' =>
                                                                            'dropoff_' . $order->order_number,
                                                                    ])
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit"
                                                                    style="font-size:10px; padding: 0 5px; float: right; margin-top: 5px;"
                                                                    class="btn btn-solid reschedule_now_btn">Reschedule
                                                                    Now</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            {{-- Model to get Slots --}}
                                        @endif

                                    </div>
                                    <div class="product_details row">
                                        <hr class="mt-0 mb-3 w-100">
                                        @foreach ($vendor->products as $product)
                                        @if ($vendor->vendor_id == $product->vendor_id)
                                            <div class="text-center col-3 mb-0 alOrderImg">
                                                <img src="{{ $product->image_url }}" alt="">
                                                <span class="item_no d-block">{{ $product->product_name }}</span>
                                            </div>
                                            <div class="col-2 d-none">
                                                <label
                                                    class="items_price">{{ Session::get('currencySymbol') }}{{ decimal_format($product->price * $clientCurrency->doller_compare) }}</label>
                                            </div>
                                            @php
                                                $product_total_price = $product->price * $clientCurrency->doller_compare;
                                                $product_total_count += $product->quantity * $product_total_price;
                                                $product_taxable_amount += $product->taxable_amount;
                                                $total_tax_order_price += $product->taxable_amount;
                                               // pr($product->toArray());
                                            @endphp
                                        @endif
                                        @if (isset($product->longTermSchedule) && count($product->longTermSchedule->schedule) > 0)
                                            <div class="outer_div col-9 mb-2">
                                                <h6 class="mt-0"> <b>{{ __('Product Detail') }} </b></h6>
                                                <hr class="my-2">
                                                <div class="service_product">
                                                    @php
                                                        $Service_product_url = isset($product->longTermSchedule->product) ? route('product.edit', @$product->longTermSchedule->product->id) : '#';
                                                    @endphp
                                                    <div class="d-flex justify-content-start">
                                                        <h6 class="m-0 pr-2 text-left">{{ __('Product Name') }}:</h6>
                                                        <a href="{{ $Service_product_url }}" target="_blank"> {{ $product->longTermSchedule->product->primary->title  ?? ''}}</a>
                                                    </div>
                                                    <div class="d-flex justify-content-start">
                                                        <h6 class="m-0 pr-2 text-left">{{ __('No. of Bookings') }}:</h6>
                                                        <span>{{ $product->longTermSchedule->service_quentity }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-start">
                                                        <h6 class="m-0 pr-2 text-left">{{ __('Service Time') }}:</h6>
                                                        <span>{{ __(config('constants.Period.' . $product->longTermSchedule->service_period)) }}</span>
                                                    </div>

                                                    @if ($product->longTermSchedule->addon && count($product->longTermSchedule->addon))
                                                        <hr class="my-2">
                                                        <h6 class="m-0 pl-0"><b>{{ __('Add Ons') }}</b></h6>
                                                        @foreach ($product->longTermSchedule->addon as $addon)
                                                            <div class="longTermAddon d-flex justify-content-start">
                                                                <h6 class="p-0 m-0">
                                                                    {{ $addon->set->title }} :</h6>
                                                                <span class="p-0 m-0">{{ $addon->option->translation_title }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="outer_divLongTermBox w-100">
                                                <h6>{{ __('Long Term Service Schedule') }}</h6>
                                                <div class="col-12">

                                                    <table class="wp-table w-100">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>{{ __('Scheduled date time') }}
                                                            </th>
                                                            <th>{{ __('Status') }}</th>
                                                        </tr>
                                                        @foreach ($product->longTermSchedule->schedule as $key => $schedule)
                                                            <tr>
                                                                <td>{{ $key + 1 }}</td>
                                                                <td><a
                                                                        href="javascript:void(0)">{{ date('d M Y h:i A', strtotime(dateTimeInUserTimeZone($schedule->schedule_date, $timezone))) }}</a>
                                                                </td>
                                                                <td> <span
                                                                        class="badge {{ $schedule->status == 0 ? 'badge-info' : 'badge-success' }}  mr-2">{{ $schedule->status == 0 ? __('Pending') : __('Complete') }}</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    </div>
                                    @if (count($vendor['vendor_dispatcher_status']))
                                        <div class="step-indicator step-indicator-order">

                                            @foreach ($vendor->dispatcher_status_icons as $key => $icons)
                                                @if (isset($vendor['vendor_dispatcher_status'][$key]) && !empty($vendor['vendor_dispatcher_status'][$key]))
                                                    <div class="step step{{ $key + 1 }} active">
                                                        <div class="step-icon-order step-icon-order-fill"><img
                                                                src="{{ @$vendor->vendor_dispatcher_status[$key]->status_data['icon'] }}">
                                                        </div>

                                                    </div>


                                                    <div class="indicator-line active"></div>

                                                    @if (count($vendor['vendor_dispatcher_status']) == $key + 1)
                                                        <p>{{ @$vendor->vendor_dispatcher_status[$key]->status_data['driver_status'] }}
                                                        </p>
                                                    @endif
                                                    @if ($key < count($vendor->dispatcher_status_icons) - 1)
                                                        <div class="indicator-line active"></div>
                                                    @endif
                                                @else
                                                    <div
                                                        class="step step{{ $key + 1 }} @if (app('request')->input('step') >= '1' || empty(app('request')->input('step'))) active @endif">
                                                        <div class="step-icon-order"><img src="{{ $icons }}">
                                                        </div>
                                                        <p></p>
                                                    </div>
                                                    @if ($key < count($vendor->dispatcher_status_icons) - 1)
                                                        <div class="indicator-line"></div>
                                                    @endif
                                                @endif
                                            @endforeach

                                        </div>
                                    @endif


                                </div>
                            @endforeach

                        </div>
                        <div class="col-md-3 mb-3 pl-lg-0">
                            <div class="card-box p-2 mb-0 h-100">
                                <ul class="price_box_bottom m-0 pl-0 pt-1">
                                    <li class="d-flex align-items-center justify-content-between">
                                        <label class="m-0">{{ __('Sub Total') }}</label>
                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->total_amount * $clientCurrency->doller_compare) }}</span>
                                    </li>
                                    @if ($order->wallet_amount_used > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Wallet') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->wallet_amount_used * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->loyalty_amount_saved > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Loyalty Used') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->loyalty_amount_saved * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->taxable_amount > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Tax') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->taxable_amount * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->taxable_amount > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Container Charges') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($vendor->total_container_charges * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->total_service_fee > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Service Fee') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->total_service_fee * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->fixed_fee_amount > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __($fixedFee) }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->fixed_fee_amount * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->tip_amount > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Tip Amount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->tip_amount * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->subscription_discount > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Subscription Discount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->subscription_discount * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->total_discount_calculate > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Discount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->total_discount_calculate * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->total_delivery_fee > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Delivery Fee') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->total_delivery_fee * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    <li class="grand_total d-flex align-items-center justify-content-between">
                                        <label class="m-0">{{ __('Total Payable') }}</label>
                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->payable_amount + $order->fixed_fee_amount) }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="no-gutters order_head">
                    <h4 class="text-center">{{ __('No Active Order Found') }}
                    </h4>
                </div>
            </div>
        @endif
    </div>
    {{ $longTermOrder->appends(['pageType' => 'activeOrders'])->links() }}
</div>
