<div class="tab-pane fade past-order {{ Request::query('pageType') == 'pastOrders' ? 'active show' : '' }}"
    id="past_order" role="tabpanel" aria-labelledby="past_order-tab">
    <div class="row">
        @if ($pastOrders->isNotEmpty())
            @foreach ($pastOrders as $key => $order)

            @php

            $total_other_taxes=0.00;
             if(!empty($order->total_other_taxes)){
                $total_other_taxes  =   (float) array_sum(explode(":", $order->total_other_taxes));
            }else{
                $total_other_taxes = $order->taxable_amount;
            }

                if(count($order->vendors)==0)
                    {
                        continue;
                    }
                @endphp

                <div class="col-12">
                    <div class="row no-gutters order_head">
                        <div class="col-md-3 alOrderStatus">
                            <h4>{{ __('Order Number') }}</h4>
                            <span>#{{ $order->order_number }}</span>
                            <?php $is_exchanged_order = 0; ?>

                            @if (@$order->vendors[0]->exchanged_to_order)
                                <h4>{{ __('Exchanged To') }}</h4>
                                <span>#{{ $order->vendors[0]->exchanged_to_order->orderDetail->order_number }}</span>
                            @endIf
                            @if (@$order->vendors[0]->exchanged_of_order)
                                <?php $is_exchanged_order = 1; ?>
                                <h4>{{ __('Exchange Of') }}</h4>
                                <span># {{ $order->vendors[0]->exchanged_of_order->orderDetail->order_number }}</span>
                            @endIf
                        </div>
                        <div class="col-md-3 alOrderStatus">
                            <h4>{{ __('Date & Time') }}</h4>
                            <span>{{ dateTimeInUserTimeZone($order->created_at, $timezone) }}</span>
                        </div>
                        <div class="col-md-3 alOrderStatus">
                            <h4>{{ __(getNomenclatureName('Vendor Name', true)) }}</h4>
                            <span><a class="text-capitalize">{{ @$order->vendors[0]['vendor']->name }}</a></span>
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
                        <div class="col-md-3">
                            {{ dateTimeInUserTimeZone($order->created_at, $timezone) }}
                        </div>
                        <div class="col-md-3">
                            <a class="text-capitalize">{{ $order->user->name }}</a>
                        </div>
                        @if ($client_preference_detail->business_type != 'taxi')
                            <div class="col-md-3" {{ $order->luxury_option_id }}>

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
                                $luxury_option_id = $order->luxury_option_id;
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
                                            <h5 class="m-0">
                                                {{ __('Order Status') }}</h5>
                                            <ul class="status_box mt-1 pl-0">
                                                @if (!empty($vendor->order_status))
                                                    <li>
                                                        <img src="{{ asset('assets/images/driver_icon.svg') }}"
                                                            alt="">
                                                        <label class="m-0 in-progress">
                                                            @if (@$is_exchanged_order)
                                                                {{ __('Exchange Order') }}
                                                            @endif
                                                            {{ __(ucfirst($vendor->order_status)) }}
                                                        </label>
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
                                        <div class="col-7 col-sm-4 row">
                                            <div class="col-6 col-sm-6">
                                                <ul class="product_list p-0 m-0 text-center">
                                                    @php
                                                        $returnable = 0;
                                                        $replaceable = 0;
                                                    @endphp
                                                    @foreach ($vendor->products as $product)
                                                        @php

                                                            if (@$product->product->returnable && $product->product->returnable == 1 && @$vendor->is_order_days_for_return) {
                                                                $returnable = 1;
                                                            }

                                                            if (@$product->product->replaceable && $product->product->replaceable == 1 && @$vendor->is_order_days_for_return) {
                                                                $replaceable = 1;
                                                            }
                                                        @endphp



                                                        @if ($vendor->vendor_id == $product->vendor_id)
                                                            @php
                                                                $pro_rating = $product->productRating->rating ?? 0;
                                                            @endphp
                                                            <li class="text-center mb-0 alOrderImg">
                                                                <img src="{{ $product->image_url }}" alt="">
                                                                <span
                                                                    class="item_no position-absolute">x{{ $product->quantity }}</span>
                                                            </li>
                                                            <li>
                                                            <label class="items_price">
                                                            {{$product->product_title}}
                                                            ({{ $additionalPreference["is_token_currency_enable"] ? getInToken(decimal_format($product->price * $clientCurrency->doller_compare)) : Session::get('currencySymbol').decimal_format($product->price * $clientCurrency->doller_compare) }})
                                                            </label>
                                                                <label class="rating-star add_edit_review"
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
                                                                @php
                                                                    $product_total_price = $product->price * $clientCurrency->doller_compare;
                                                                    $product_total_count += $product->quantity * $product_total_price;
                                                                    $product_taxable_amount += $product->taxable_amount;
                                                                    $total_tax_order_price += $product->taxable_amount;
                                                                @endphp
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <div class="col-6 col-sm-6">
                                                @if ($order->vendors[0]->dispatch_traking_url != null && $order->vendors[0]->dispatch_traking_url != '')
                                                    <ul class="product_list p-0 m-0 text-center">
                                                        @php
                                                            $driverrating = $order->driver_rating->rating ?? 0;
                                                        @endphp
                                                        <li class="text-center alOrderTaxi">
                                                            {{-- <img src="#" alt=""> --}}
                                                            <label
                                                                class="items_price">{{ __('Rate Your Driver') }}</label>
                                                            <label class="rating-star add_edit_driver_review"
                                                                data-id="{{ $order->driver_rating->id ?? 0 }}"
                                                                data-order_vendor_product_id="{{ $product->id ?? 0 }}"
                                                                data-dispatch_traking_url="{{ $order->vendors[0]->dispatch_traking_url  }}">
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 1 ? '' : '-o' }}"></i>
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 2 ? '' : '-o' }}"></i>
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 3 ? '' : '-o' }}"></i>
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 4 ? '' : '-o' }}"></i>
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 5 ? '' : '-o' }}"></i>
                                                            </label>
                                                        </li>

                                                    </ul>

                                                @elseif($product->routes->first() && ($product->routes->first()->dispatch_traking_url !=''))
                                                    <ul class="product_list p-0 m-0 text-center">
                                                        @php
                                                            $driverrating = $order->driver_rating->rating ?? 0;
                                                        @endphp
                                                        <li class="text-center alOrderTaxi">
                                                            {{-- <img src="#" alt=""> --}}
                                                            <label
                                                                class="items_price">{{ __('Rate Your Driver') }}</label>
                                                            <label class="rating-star add_edit_driver_review"
                                                                data-id="{{ $order->driver_rating->id ?? 0 }}"
                                                                data-order_vendor_product_id="{{ $product->id ?? 0 }}"
                                                                data-dispatch_traking_url="{{$product->routes->first()->dispatch_traking_url  }}">
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 1 ? '' : '-o' }}"></i>
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 2 ? '' : '-o' }}"></i>
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 3 ? '' : '-o' }}"></i>
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 4 ? '' : '-o' }}"></i>
                                                                <i
                                                                    class="fa fa-star{{ $driverrating >= 5 ? '' : '-o' }}"></i>
                                                            </label>
                                                        </li>

                                                    </ul>
                                                @endif

                                                @if ($order->reports != null)
                                                    <div class="order-past-report text-center">
                                                        <a target="_blank"
                                                            href="{{ $order->reports->report['original'] }}"
                                                            download><i class="fa fa-download" aria-hidden="true"></i>
                                                            Report</a>
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                        <div class="col-md-5 mt-md-0 mt-sm-2">
                                            <ul class="price_box_bottom m-0 p-0">
                                                <li class="d-flex align-items-center justify-content-between">
                                                    <label class="m-0">{{ __('Product Total') }}</label>
                                                    <span>{{ Session::get('currencySymbol') }}{{ decimal_format($product_total_count * $clientCurrency->doller_compare) }}</span>
                                                </li>
                                                @if ($vendor->discount_amount > 0)
                                                    <li class="d-flex align-items-center justify-content-between">
                                                        <label class="m-0">{{ __('Coupon Discount') }}</label>
                                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($vendor->discount_amount * $clientCurrency->doller_compare) }}</span>
                                                    </li>
                                                @endif

                                                @if ($vendor->taxable_amount  > 0)
                                                <li
                                                    class="d-flex align-items-center justify-content-between">
                                                    <label
                                                        class="m-0">{{ __('Tax') }}</label>
                                                    <span>{{ $additionalPreference["is_token_currency_enable"] ? getInToken(decimal_format(($vendor->taxable_amount) * $clientCurrency->doller_compare)) : Session::get('currencySymbol') .decimal_format(($vendor->taxable_amount)
                                                        *
                                                        $clientCurrency->doller_compare)}}</span>
                                                </li>
                                            @endif

                                                @if ($vendor->delivery_fee > 0)
                                                    <li class="d-flex align-items-center justify-content-between">
                                                        <label class="m-0">{{ __('Delivery Fee') }}</label>
                                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($vendor->delivery_fee * $clientCurrency->doller_compare) }}</span>
                                                    </li>

                                                @endif
                                            @if ($vendor->waiting_price > 0)
                                                <li class="d-flex align-items-center justify-content-between">
                                                    <label class="m-0">{{ __('Waiting Time').(($order->total_waiting_time)?'('.$order->total_waiting_time.'Min)':'') }}</label>
                                                    <span>{{$additionalPreference["is_token_currency_enable"] ? getInToken(decimal_format($vendor->waiting_price
                                                    *
                                                    $clientCurrency->doller_compare)) : Session::get('currencySymbol').decimal_format($vendor->waiting_price
                                                    *
                                                    $clientCurrency->doller_compare)}}</span>
                                                    </li>
                                            @endif
                                                <li
                                                    class="grand_total d-flex align-items-center justify-content-between">
                                                    <label class="m-0">{{ __('Amount') }}</label>
                                                    @php
                                                        // $product_subtotal_amount = $product_total_count - $vendor->discount_amount + $vendor->delivery_fee + $vendor->waiting_price;
                                                        // $subtotal_order_price += $product_subtotal_amount;


                                                        $product_subtotal_amount = $vendor->subtotal_amount - $vendor->discount_amount + $vendor->total_container_charges +
                                                        $vendor->taxable_amount + $vendor->service_fee_percentage_amount + $vendor->fixed_fee +
                                                        $vendor->delivery_fee + $vendor->additional_price + $vendor->toll_amount-$order->wallet_amount_used;
                                                        $subtotal_order_price += $product_subtotal_amount;

                                                    @endphp

                                                    <span>{{ Session::get('currencySymbol') }}{{ decimal_format($product_subtotal_amount * $clientCurrency->doller_compare) }}</span>
                                                </li>
                                                <li>
                                                    @php
                                                        $docs = $vendor->orderDocument;
                                                    @endphp
                                                    @if(count($docs) > 0 && getAdditionalPreference(['document_report'])['document_report'] == 1)
                                                        <button type="button" class="btn btn-primary docButtons" data-toggle="modal" data-target="#exampleModal" style="color: white!important">View Reports</button>
                                                    @endif
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                          <div class="modal-content">
                                            <div class="modal-header">
                                              <h4 class="header-title mb-3" id="exampleModalLabel">{{ __('Reports') }}</h4>
                                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                              </button>
                                            </div>
                                            <div class="modal-body">
                                                @if($docs && count($docs) > 0)
                                                    @foreach($docs as $file)
                                                        @php
                                                            $files = Storage::disk('s3')->url($file['document']);
                                                        @endphp
                                                        <div class="mb-2 d-flex ">
                                                            <div class="col-12">
                                                                <img  src="{{url('file-download' . '/pdf.png')}}"    ><a target="_blank" href="{{$files}}"> {{$file['file_name']}}   </a>
                                                            </div>

                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="card-body">

                                            </div>

                                            <div class="modal-footer">

                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justifiy-content-end alListBtnGroups">
                                                @if (@$vendor->is_exchanged_or_returned && $vendor->is_exchanged_or_returned == 1)
                                                    @if (@$vendor->exchanged_to_order->order_status_option_id == 6)
                                                        <button class="btn btn-solid"> {{ __('Replaced') }}</button>
                                                    @else($vendor->order_status_option_id == 9)
                                                        <button class="btn btn-solid"> {{ __('Replacement Pending') }}
                                                        </button>
                                                    @endif
                                                @elseif($vendor->is_exchanged_or_returned && $vendor->is_exchanged_or_returned == 2)
                                                    <button class="btn btn-solid"> {{ __('Return Pending') }} </button>
                                                @else
                                                    @if (isset($hidereturn) && $hidereturn != 1 && isset($vendor->vendor->return_request) && $vendor->vendor->return_request)
                                                        @if (@$returnable && $order->vendors[0]->exchanged_of_order == null)
                                                            <button class="return-order-product btn btn-solid"
                                                                data-id="{{ $order->id ?? 0 }}"
                                                                data-vendor_id="{{ $vendor->vendor_id ?? 0 }}">
                                                                {{ __('Return') }}
                                                            </button>
                                                        @endif
                                                    @endif

                                                    @if (@$replaceable && $order->vendors[0]->exchanged_of_order == null)
                                                        <button class="replace-order-product btn btn-solid"
                                                            data-id="{{ $order->id ?? 0 }}"
                                                            data-vendor_id="{{ $vendor->vendor_id ?? 0 }}">

                                                            {{ __('Replace') }}
                                                        </button>
                                                    @endif
                                                    {{--  luxury_option_id = 6 on_demand   --}}
                                                    @if(($luxury_option_id !=6) && ($is_service_product_price_from_dispatch_forOnDemand != 1))
                                                    @if(!Auth::user()->hasRole('Lawyer'))
                                                    <button class="repeat-order-product btn btn-solid mr-2"
                                                        data-id="{{ $order->id ?? 0 }}"
                                                        data-order_vendor_id="{{ $vendor->id ?? 0 }}"
                                                        data-vendor_id="{{ $vendor->vendor_id ?? 0 }}">
                                                        <td class="text-center" colspan="3">
                                                            {{ __('Repeat Order') }}
                                                    </button>
                                                    @endif
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
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

                                    @if ($order->total_container_charges > 0)
                                        <li
                                            class="d-flex align-items-center justify-content-between">
                                            <label
                                                class="m-0">{{ __('Container Charges') }}</label>
                                            <span>{{ $additionalPreference["is_token_currency_enable"] ? getInToken(decimal_format(($vendor->total_container_charges) * $clientCurrency->doller_compare)) : Session::get('currencySymbol') .decimal_format(($vendor->total_container_charges) * $clientCurrency->doller_compare)}}</span>
                                        </li>
                                    @endif

                                    @if ($order->total_service_fee > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Service Fee') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->total_service_fee * $clientCurrency->doller_compare) }}</span>
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
                                    @if ($order->total_delivery_fee > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Delivery Fee') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->total_delivery_fee * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    @if ($order->total_discount_calculate > 0)
                                    <li class="d-flex align-items-center justify-content-between">
                                        <label class="m-0">{{ __('Discount') }}</label>
                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->total_discount_calculate * $clientCurrency->doller_compare) }}</span>
                                    </li>
                                @endif
                                    @if ($order->total_waiting_price > 0)
                                    <li class="d-flex align-items-center justify-content-between">
                                        <label class="m-0">{{ __('Waiting Time ').(($order->total_waiting_time)?'('.$order->total_waiting_time.'Min)':'') }}</label>
                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->total_waiting_price * $clientCurrency->doller_compare) }}</span>
                                    </li>
                                @endif
                                    @if ($order->gift_card_amount > 0)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Gift Card Amount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->gift_card_amount * $clientCurrency->doller_compare) }}</span>
                                        </li>
                                    @endif
                                    <li class="grand_total d-flex align-items-center justify-content-between">
                                        <label class="m-0">{{ __('Total Payable') }}</label>
                                        <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->payable_amount * $clientCurrency->doller_compare) }}</span>
                                    </li>
                                    {{-- mohit sir branch code added by sohail --}}
                                    @if (@$order->advance_amount > 0)
                                        <li class="grand_total d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Advance Paid') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format(@$order->advance_amount) }}</span>
                                        </li>
                                        <li class="grand_total d-flex align-items-center justify-content-between">
                                            <label class="m-0">{{ __('Pending Amount') }}</label>
                                            <span>{{ Session::get('currencySymbol') }}{{ decimal_format($order->payable_amount) - decimal_format(@$order->advance_amount) }}</span>
                                        </li>
                                    @endif
                                    {{-- till here --}}
                                </ul>

                                @if ($client_preference_detail->tip_after_order == 1 && $order->tip_amount <= 0 && $payments > 0)
                                    <hr>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-2">
                                                @if (getNomenclatureName('Want To Tip', true) != 'Want To Tip')
                                                    {{ getNomenclatureName('Want To Tip', true) }}
                                                @else
                                                    {{ __('Do you want to give a tip?') }}
                                                @endif
                                            </div>
                                            <div class="tip_radio_controls">
                                                @if ($order->payable_amount > 0)
                                                    <input type="radio" class="tip_radio" id="control_01"
                                                        name="select{{ $order->order_number }}"
                                                        value="{{ round($order->payable_amount * 0.05, 2) }}">
                                                    <label class="tip_label" for="control_01">
                                                        <h5 class="m-0" id="tip_5">
                                                            {{ Session::get('currencySymbol') }}{{ decimal_format($order->payable_amount * 0.05) }}
                                                        </h5>
                                                        <p class="m-0">
                                                            5%</p>
                                                    </label>

                                                    <input type="radio" class="tip_radio" id="control_02"
                                                        name="select{{ $order->order_number }}"
                                                        value="{{ round($order->payable_amount * 0.1, 2) }}">
                                                    <label class="tip_label" for="control_02">
                                                        <h5 class="m-0" id="tip_10">
                                                            {{ Session::get('currencySymbol') }}{{ decimal_format($order->payable_amount * 0.1) }}
                                                        </h5>
                                                        <p class="m-0">
                                                            10%</p>
                                                    </label>

                                                    <input type="radio" class="tip_radio" id="control_03"
                                                        name="select{{ $order->order_number }}"
                                                        value="{{ round($order->payable_amount * 0.15, 2) }}">
                                                    <label class="tip_label" for="control_03">
                                                        <h5 class="m-0" id="tip_15">
                                                            {{ Session::get('currencySymbol') }}{{ decimal_format($order->payable_amount * 0.15) }}
                                                        </h5>
                                                        <p class="m-0">
                                                            15%</p>
                                                    </label>

                                                    <input type="radio" class="tip_radio"
                                                        id="custom_control{{ $order->order_number }}"
                                                        name="select{{ $order->order_number }}" value="custom">
                                                    <label class="tip_label"
                                                        for="custom_control{{ $order->order_number }}">
                                                        <h5 class="m-0">
                                                            {{ __('Custom') }}<br>{{ __('Amount') }}
                                                        </h5>
                                                    </label>
                                                @else
                                                    <input type="hidden" class="tip_radio"
                                                        id="custom_control{{ $order->order_number }}"
                                                        name="select{{ $order->order_number }}" value="custom"
                                                        checked>
                                                @endif
                                            </div>
                                            <div
                                                class="custom_tip mb-1 @if ($order->payable_amount > 0) d-none @endif">
                                                <input class="input-number form-control"
                                                    name="custom_tip_amount{{ $order->order_number }}"
                                                    id="custom_tip_amount{{ $order->order_number }}"
                                                    placeholder="{{ __('Enter Custom Amount') }}" type="number"
                                                    value="" min="0.01" step="0.01">
                                            </div>
                                            <div class="col-md-6 text-md-right text-center">
                                                <button type="button"
                                                    class="btn btn-solid topup_wallet_btn_tip topup_wallet_btn_for_tip"
                                                    data-order_number={{ $order->order_number }}
                                                    data-payableamount={{ $order->payable_amount }}>{{ __('Submit') }}</button>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-2">
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="no-gutters order_head">
                    <h4 class="text-center">{{ __('No Past Order Found') }}
                    </h4>
                </div>
            </div>
        @endif
    </div>
    {{ $pastOrders->appends(['pageType' => 'pastOrders'])->links() }}
</div>
<style>
    .docButtons{
        padding: 5px 10px!important;
        font-size: 10px!important;
        letter-spacing: 2px;
        font-weight: 500;
        text-shadow: none;
        border-radius: 4px;
        border-width: 1px;
        width: auto !important;
        max-width: max-content;
        text: white;
    }
</style>
