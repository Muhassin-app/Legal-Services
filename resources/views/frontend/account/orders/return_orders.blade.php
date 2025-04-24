<div class="tab-pane fade return-order {{ Request::query('pageType') == 'returnOrders' ? 'active show' : '' }}"
                                                id="return_order" role="tabpanel" aria-labelledby="return_order-tab">
    <div class="row">
        @if ($returnOrders->isNotEmpty())
            @foreach ($returnOrders as $key => $order)
                @if ($order->orderStatusVendor->isNotEmpty())
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
                                <div class="col-md-3">
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
                                        <span class="left_arrow pulse"></span>
                                        <div class="row">
                                            <div class="col-5 col-sm-3">
                                                <h5 class="m-0"></h5>
                                                <ul class="status_box mt-1 pl-0">
                                                    @if ($vendor->dineInTable)
                                                        <li>
                                                            <h5
                                                                class="mb-1">
                                                                {{ __('Dine-in') }}
                                                            </h5>
                                                            <h6
                                                                class="m-0">
                                                                {{ $vendor->dineInTableName }}
                                                            </h6>
                                                            <h6
                                                                class="m-0">
                                                                Category :
                                                                {{ $vendor->dineInTableCategory }}
                                                            </h6>
                                                            <h6
                                                                class="m-0">
                                                                Capacity :
                                                                {{ $vendor->dineInTableCapacity }}
                                                            </h6>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="col-7 col-sm-4">
                                                <ul class="product_list p-0 m-0 text-center">
                                                    @foreach ($vendor->products as $product)
                                                        @if ($vendor->vendor_id == $product->vendor_id)
                                                            @php
                                                                $pro_rating = $product->productRating->rating ?? 0;
                                                            @endphp
                                                            <li class="text-center mb-0 alOrderImg">
                                                                <img src="{{ $product->image_url }}" alt="">
                                                                <span class="item_no position-absolute">x{{ $product->quantity }}</span>
                                                            </li>
                                                            <li>
                                                                <label class="items_price">
                                                            {{$product->product_title}}
                                                            ({{ $additionalPreference["is_token_currency_enable"] ? getInToken(decimal_format($product->price * $clientCurrency->doller_compare)) : Session::get('currencySymbol').decimal_format($product->price * $clientCurrency->doller_compare) }})
                                                        </label>                                                                <label
                                                                    class="rating-star add_edit_review"
                                                                    data-id="{{ $product->productRating->id ?? 0 }}"
                                                                    data-order_vendor_product_id="{{ $product->id ?? 0 }}">
                                                                    <i
                                                                        class="fa fa-star{{ $pro_rating >= 1 ? '' : '-o' }}"></i>
                                                                    <i
                                                                        class="fa fa-star{{ $pro_rating >= 2 ? '' : '-o' }}"></i>
                                                                    <i
                                                                        class="fa fa-star{{ $pro_rating >= 3 ? '' : '-o' }}"></i>
                                                                    <i
                                                                        class="fa fa-star{{ $pro_rating >= 4 ? '' : '-o' }}"></i>
                                                                    <i
                                                                        class="fa fa-star{{ $pro_rating >= 5 ? '' : '-o' }}"></i>
                                                                </label>
                                                                {{ __($product->productReturn->status ?? '') }}
                                                            </li>
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
                                                <ul
                                                    class="price_box_bottom m-0 p-0">
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
                                                            $total_order_price += $product_subtotal_amount + $total_tax_order_price;
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
                                                class="m-0">{{ 'Sub Total' }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->total_amount
                                                + $order->total_delivery_fee *
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
                                            <span>{{ Session::get('currencySymbol') }}{{decimal_format($order->payable_amount
                                                *
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
                @endif
            @endforeach
        @else
            <div class="col-12">
                <div class="no-gutters order_head">
                    <h4 class="text-center">{{ __('No Return Requests') }}
                    </h4>
                </div>
            </div>
        @endif
    </div>
    {{ $returnOrders->appends(['pageType' => 'returnOrders'])->links() }}
    </div>