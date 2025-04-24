<div class="tab-pane fade rejected-order {{ Request::query('pageType') == 'rejectedOrders' ? 'active show' : '' }}" id="rejected_order" role="tabpanel" aria-labelledby="rejected_order-tab">
    <div class="row">
        @if ($rejectedOrders->isNotEmpty())
            @foreach ($rejectedOrders as $key => $order)
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
                            <h4>{{ __('Vendor Name') }}</h4>
                            <span><a class="text-capitalize">{{ @$order->vendors[0]->vendor->name }}</a></span>
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
                            <div class="col-md-3">
                                <span class="ellipsis" data-toggle="tooltip"
                                    data-placement="top" title="">
                                    @if ($order->address)
                                        {{ $order->address->address }},
                                        {{ $order->address->street }},
                                        {{ $order->address->city }},
                                        {{ $order->address->state }},
                                        {{ $order->address->country }}
                                        {{ $order->address->pincode }}
                                    @else
                                        NA
                                    @endif
                                </span>
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
                                <div class="order_detail order_detail_data align-items-top pb-3 card-box no-gutters mb-0">
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

                                                        //$luxury_option_name = 'Delivery';
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
                                                    <span
                                                        class="align-middle">This
                                                        is a gift.</span>
                                                </div>
                                            @endif
                                            <button class="chat-icon btn btn-solid" style="font-size:10px; padding: 0 5px; float: right; margin-top: 5px;" >{{__('Chat')}}</button>
                                        </div>
                                    @endif
                                    <span class="left_arrow pulse"></span>
                                    <div class="row">
                                        <div class="col-5 col-sm-3">
                                            <h5 class="m-0">
                                                {{ __('Order Status') }} </h5>
                                            <ul class="status_box mt-1 pl-0">
                                                @if (!empty($vendor->order_status) && $vendor->order_status == "accepted")
                                                    <li>
                                                        <label class="m-0 in-progress">{{ __(ucfirst('cancelled')) }} </label>
                                                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="{{$vendor->reject_reason}}" aria-hidden="true"></i>
                                                    </li>
                                                @else
                                                    <li>
                                                        <label class="m-0 in-progress">
                                                        @if(@$is_exchanged_order)
                                                            {{__('Exchange Order')}}
                                                        @endif
                                                        @if($vendor->cancelled_by == Auth::id())
                                                            {{ __(ucfirst('cancelled')) }} </label>
                                                        @else
                                                            {{ __(ucfirst($vendor->order_status)) }} </label>
                                                        @endif
                                                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="{{$vendor->reject_reason}}" aria-hidden="true"></i>
                                                    </li>
                                                @endif

                                            </ul>

                                        </div>
                                        <div class="col-7 col-sm-4">
                                            <ul
                                                class="product_list p-0 m-0 text-center">
                                                @foreach ($vendor->products as $product)
                                                    @if ($vendor->vendor_id == $product->vendor_id)
                                                        <li class="text-center mb-0 alOrderImg">
                                                            <img src="{{ $product->image_url }}" alt="">
                                                            <span class="item_no position-absolute">x{{ $product->quantity }}</span>
                                                        </li>
                                                        <li>
                                                        <label class="items_price">
                                                            {{$product->product_title}}
                                                            ({{ $additionalPreference["is_token_currency_enable"] ? getInToken(decimal_format($product->price * $clientCurrency->doller_compare)) : Session::get('currencySymbol').decimal_format($product->price * $clientCurrency->doller_compare) }})
                                                        </label>
                                                 		</li>
                                                        @if($product->schedule_slot && $product->schedule_slot_name )
                                                        <li>
                                                            <label class="schedule_slot"><span>Slots: {{ $product->schedule_slot_name }}</span></label>
                                                        </li>
                                                        @endif
                                                        @php
                                                            $product_total_price = $product->price * $clientCurrency->doller_compare;
                                                            $product_total_count += $product->quantity * $product_total_price;
                                                            $product_taxable_amount += $product->taxable_amount;
                                                            $total_tax_order_price += $product->taxable_amount;
                                                        @endphp
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-5 mt-md-0 mt-sm-2">
                                            <ul class="price_box_bottom m-0 p-0">
                                                <li
                                                    class="d-flex align-items-center justify-content-between">
                                                    <label
                                                        class="m-0">{{ __('Product Total') }}</label>
                                                    <span>{{ Session::get('currencySymbol') }}{{decimal_format($vendor->subtotal_amount
                                                        *
                                                        $clientCurrency->doller_compare)}}</span>
                                                </li>
                                                @if ($vendor->discount_amount > 0)
                                                    <li
                                                        class="d-flex align-items-center justify-content-between">
                                                        <label
                                                            class="m-0">{{ __('Coupon Discount') }}</label>
                                                        <span>{{ Session::get('currencySymbol') }}{{decimal_format($vendor->discount_amount
                                                            *
                                                            $clientCurrency->doller_compare)}}</span>
                                                    </li>
                                                @endif
                                                @if ($vendor->delivery_fee > 0)
                                                    <li
                                                        class="d-flex align-items-center justify-content-between">
                                                        <label
                                                            class="m-0">{{ __('Delivery Fee') }}</label>
                                                        <span>{{ Session::get('currencySymbol') }}{{decimal_format($vendor->delivery_fee
                                                            *
                                                            $clientCurrency->doller_compare)}}</span>
                                                    </li>
                                                @endif


                                                <li
                                                    class="grand_total d-flex align-items-center justify-content-between">
                                                    <label
                                                        class="m-0">{{ __('Amount') }}</label>
                                                    @php
	                        $product_subtotal_amount = $vendor->subtotal_amount - $vendor->discount_amount + $vendor->total_container_charges +
                                                                                                 $vendor->taxable_amount + $vendor->service_fee_percentage_amount + $vendor->fixed_fee +
                                                                                                 $vendor->delivery_fee + $vendor->additional_price + $vendor->toll_amount-$order->wallet_amount_used;
                                                        $subtotal_order_price += $product_subtotal_amount;
                                                    @endphp
                                                    <span>{{ Session::get('currencySymbol') }}{{decimal_format($product_subtotal_amount
                                                        *
                                                        $clientCurrency->doller_compare)}}</span>
                                                </li>

                                            </ul>
                                        </div>
                                    </div>
                                    @include('frontend.account.recurringItems')
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-3 mb-3 pl-lg-0">
                            <div class="card-box p-2 mb-0 h-100">
                                <ul class="price_box_bottom m-0 pl-0 pt-1">
                                    <li
                                        class="d-flex align-items-center justify-content-between">
                                        <label
                                            class="m-0">{{ __('Sub Total') }}</label>
                                        <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->total_amount
                                            *
                                            $clientCurrency->doller_compare)}}</span>
                                    </li>
                                    @if ($order->wallet_amount_used > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Wallet') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->wallet_amount_used
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    @if ($order->loyalty_amount_saved > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Loyalty Used') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->loyalty_amount_saved
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    @if ($order->taxable_amount > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Tax') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->taxable_amount
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    @if ($order->total_service_fee > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Service Fee') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->total_service_fee
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    @if ($order->tip_amount > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Tip Amount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->tip_amount
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    @if ($order->subscription_discount > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Subscription Discount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->subscription_discount
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    @if ($order->total_discount_calculate > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Discount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->total_discount_calculate
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    @if ($order->total_delivery_fee > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Delivery Fee') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->total_delivery_fee
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    @if ($order->gift_card_amount > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Gift Card Amount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->gift_card_amount
                                                *
                                                $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif
                                    <li
                                        class="grand_total d-flex align-items-center justify-content-between">
                                        <label
                                            class="m-0">{{ __('Total Payable') }}</label>
                                        <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->payable_amount *
                                            $clientCurrency->doller_compare)}}</span>
                                    </li>
                                    {{-- mohit sir branch code added by sohail --}}
                                    @if (@$order->advance_amount > 0)
                                        <li
                                            class="grand_total d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Advance Paid') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format(@$order->advance_amount) }}</span>
                                        </li>
                                        <li
                                            class="grand_total d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Pending Amount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->payable_amount) - decimal_format(@$order->advance_amount) }}</span>
                                        </li>
                                    @endif
                                    {{-- till here --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="no-gutters order_head">
                    <h4 class="text-center">{{ __('No Rejected/Cancel Order Found') }}
                    </h4>
                </div>
            </div>
        @endif
    </div>
    {{ $rejectedOrders->appends(['pageType' => 'rejectedOrders'])->links() }}
</div>