@extends('layouts.store', ['title' => 'Checkout'])
@section('content')
@php
//$total_amount = $order->payable_amount+$order->total_other_taxes_amount;
$total_amount = $order->payable_amount;
// dd($total);
$total_bid_discount = @$order->bid_discount;
$serviceType =  Session::get('vendorType');

if($serviceType == 'rental')
{

    $total=$order->total_amount+$order->fixed_fee_amount+$order->total_delivery_fee+$order->total_service_fee+$order->total_container_charges+$order->rental_protection_amount+$order->booking_option_price - $order->wallet_amount_used +$order->total_other_taxes_amount;
}else{

    $total = $order->total_other_taxes_amount+$order->total_service_fee+$order->fixed_fee_amount+$order->total_container_charges+$order->total_delivery_fee+$order->tip_amount+$order->subscription_discount+$order->total_amount - $order->wallet_amount_used ;
}

$additional_price=0;
$vendor_total_discount = 0;

$additionalPreference = getAdditionalPreference(['is_token_currency_enable']);
$timezone = Auth::user()->timezone;
$order_is_long_term = $order->is_long_term;
@endphp
@section('customcss')
<style>
    .total_booking_time span {
    display: inline-block;
    font-size: 12px;
    font-style: italic;
    color: #777;
    border-left: 1px solid #000;
    padding-left: 5px;
}
.spa_order_detail_slot .sch_slot h6 {display: inline-block;}
.product_slot{    color: var(--theme-deafult); font-weight: 400;margin-left: 15px;};
</style>
@endsection
<section class="section-b-space light-layout_alFour">
    <div class="container">
        <div class="row">
            <div class="col-md-12 my-1">
                <div class="success-text al">
                	<i class="fa fa-check-circle" aria-hidden="true"></i>
                    <h2>{{__('Thank You')}}</h2>
                    {{-- <p>{{__('Payment is successfully processsed and your order is on the way')}}</p> --}}
                    <p>{{__("Your order has been placed")}}</p>
                    @if(($order->payment_method != 1) && ($order->payment_method != 2))
                    	<p>{{__('Transaction ID')}}: {{$order->payment ? $order->payment->transaction_id : ''}}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
<section class="section-b-space_al p-0 mt-2">
    <div class="container position-relative alFourTemplateOrderSucces">
        <div class="card">
            <div class="card-body">

                <div class="row">
                    <div class="col-lg-6">
                        <div class="product-order py-3 pro-scroller">
                            <h3>{{__('Your Order Details')}}</h3>
                            @php
                                $security_amount = 0.00;
                            @endphp
                            @foreach($order->vendors as $vendor)
                              <div class="row product-order-detail">
                                    <div class="col-12">
                                            <h4>{{$vendor->vendor->name}}</h4>
                                    </div>

                                @php
                                    $bid_vendor_discount = 0;
                                 @endphp

                                @foreach($vendor->products as $product)
                                    @php
                                        $productCategory = $product->product->productcategory->slug;
                                        $image = count($product->media) ? @$product->media->first()->image['path']['proxy_url'].'74/100'.@$product->media->first()->image['path']['image_path']:@$product->image['proxy_url'].'74/100'.@$product->image['image_path'];
                                        $additional_price+= $product->incremental_price;
                                        $security_amount+=$product->security_amount;
                                    @endphp



                                        <div class="col-2">
                                            <img src="{{ $image }}" class="img-fluid blur-up lazyloaded">
                                        </div>
                                        <div class="col-10">
                                            <div class="row">
                                                <div class="col-4 order_detail">
                                                    <div>
                                                        <h4> {{ ($order_is_long_term ==1)? __('Long term service Name') : __('Product Name')}}</h4>
                                                        <h5>{{ (!empty($product->pvariant->translation) && isset($product->pvariant->translation[0])) ? $product->pvariant->translation[0]->title : ''}}</h5>
                                                        @foreach($product->pvariant->vset as $vset)
                                                            <label><span>{{$vset->optionData->trans->title}}:</span>{{$vset->variantDetail->trans->title}}</label>
                                                        @endforeach

                                                    </div>

                                                </div>
                                                <div class="col-4 order_detail">
                                                    @if($order_is_long_term ==0)
                                                    <div>
                                                        @if($serviceType=='rental')
                                                            <h4>{{__('Duration')}}</h4>
                                                            @php
                                                            if($serviceType == 'rental'){
                                                                $dura = getHoursMinutes($product->total_booking_time);
                                                            }elseif($productCategory == 'yacht'){
                                                                $dura = $product->pvariant->vset[0]->options1->title;
                                                            }
                                                            @endphp
                                                            <h5>{{$dura ?? ''}}</h5>
                                                        @else
                                                            <h4>{{__('Quantity')}}</h4>

                                                            <h5>{{$product->quantity}}</h5>
                                                        @endif
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="col-4 order_detail">
                                                    <div>
                                                        <h4>{{__('Price')}}</h4>
                                                        <h5 class="total_booking_time" >@if( $additionalPreference["is_token_currency_enable"])
                                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($product->price * @$clientCurrency->doller_compare))}}@else{{Session::get('currencySymbol').decimal_format($product->price * @$clientCurrency->doller_compare)}}@endif @if(in_array($serviceType , ['appointment','on_demand']))
                                                            <span > {{ $product->total_booking_time > 0 ? $product->total_booking_time : 0 }} {{  __(' min') }}  </span>@endif</h5>
                                                        @if($product->container_charges>0)
                                                        <h4>{{__('Container Charges')}}</h4>
                                                        <p>{{$additionalPreference ['is_token_currency_enable'] ? getInToken(decimal_format($product->container_charges)) : decimal_format($product->container_charges)}}</p>
                                                        @endif
                                                        @if($serviceType=='rental' && $product->incremental_price>0)
                                                            <h4>{{__('Additional Price')}}</h4>
                                                            <p>{{decimal_format($product->incremental_price)}}</p>
                                                        @endif
                                                    </div>
                                                </div>

                                            </div>
                                            @if(count($product->addon) != 0)
                                                <hr class="my-2" style="width:100%">
                                                <div class="col-12">
                                                    <div class="row align-items-md-center">
                                                        <h6 class="m-0 pl-0"><b>{{__('Add Ons')}}</b></h6>
                                                    </div>
                                                </div>
                                                @foreach($product->addon as $addon)
                                                    @if($addon->option)
                                                    <div class="col-12">
                                                        <div class="row">
                                                            <div class="col-md-4 col col-sm-4 items-details text-left">
                                                                <p class="p-0 m-0">{{ $addon->option->title }}</p>
                                                            </div>
                                                            <div class="col-md-3 col col-sm-4 text-center">

                                                            </div>
                                                            <div class="col-md-5 col col-sm-4 text-right">
                                                                <div class="extra-items-price">@if( $additionalPreference["is_token_currency_enable"])
                                                                    {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($addon->option->price )) }}@else{{Session::get('currencySymbol').decimal_format($addon->option->price )}}@endif</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endforeach

                                            @endif

                                            @if(isset($product->scheduled_date_time))
                                                <hr class="my-2" style="width:100%;  display: block !important;">
                                                <div class="spa_order_detail_slot">
                                                    <div class="sch_slot py-1">
                                                        <h6 class="m-0 pl-0">{{__('Scheduled slot')}}</h6>
                                                        <p class="p-0 m-0 float-right product_slot"><span>
                                                            {{ date('F d, Y',strtotime(dateTimeInUserTimeZone($product->scheduled_date_time, $timezone))) }}
                                                        @if($product->schedule_slot!='')  {{' ; '. $product->schedule_slot }}  @endif
                                                        </span></p>
                                                    </div>
                                                </div>

                                            @endif
                                            @if($order_is_long_term ==1)
                                                @include('frontend.order.longTermDetails')
                                            @endif
                                        </div>

                                    @endforeach {{--  End vendor Products loop --}}

                                    @if(@$total_bid_discount && $total_bid_discount>0)
                                        <div class="col-12 offset-6 border-top pt-1 mt-1">
                                            <div class="row mr-0">
                                                <div class="col-md-3">
                                                    {{__('Bid Discount')}}
                                                </div>
                                                <div class="col-md-3 text-right"><b style="color:#000">
                                                    {{Session::get('currencySymbol')}}{{decimal_format(($total_bid_discount) * @$clientCurrency->doller_compare)}}
                                                </b></div>
                                        </div>
                                    </div>
                                    @endif

                                </div>

                            @endforeach {{--  End vendor loop --}}

                            <div class="total-sec row">
                                <ul class="col-sm-6 offset-sm-6">
                                        <li>{{__('Sub Total')}} <span>@if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format(($order->total_amount+$order->total_container_charges) * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format(($order->total_amount+$order->total_container_charges) * @$clientCurrency->doller_compare)}}@endif</span></li>
                                    @if($order->total_service_fee > 0)
                                        <li>{{__('Service Fee')}} <span>@if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->total_service_fee * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format($order->total_service_fee * @$clientCurrency->doller_compare)}}@endif</span></li>
                                    @endif
                                    @if(!empty($order->fixed_fee_amount) && $order->fixed_fee_amount > 0)
                                        <li>{{__($fixedFeeNomenclatures)}} <span>@if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->fixed_fee_amount)) }}@else{{Session::get('currencySymbol').decimal_format($order->fixed_fee_amount)}}@endif</span></li>
                                    @endif
                                    {{-- @if($order->total_container_charges > 0)
                                        <li>{{__('Container Charges')}} <span>{{$additionalPreference ['is_token_currency_enable'] ? getInToken() }}@else{{Session::get('currencySymbol')}}@money($order->total_container_charges * @$clientCurrency->doller_compare)</span></li>
                                    @endif --}}
                                    @if($order->total_delivery_fee > 0)
                                        <li>{{__('Delivery Fee')}} <span>@if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->total_delivery_fee * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format($order->total_delivery_fee * @$clientCurrency->doller_compare)}}@endif</span></li>
                                    @endif
                                    @if($order->total_discount > 0)
                                    	@php
                                    		$total -= (decimal_format($order->total_discount * @$clientCurrency->doller_compare));
                                    	@endphp
                                        <li>{{__('Total Discount')}} <span> - @if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->total_discount * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format($order->total_discount * @$clientCurrency->doller_compare)}}@endif</span></li>
                                    @endif

                                    @if($order->taxable_amount > 0 || $order->total_other_taxes_amount> 0 )
                                    @php
                                    // $total += decimal_format($order->total_other_taxes_amount * @$clientCurrency->doller_compare);
                                    // dd($total);
                                    @endphp
				                         <li>{{__('Tax')}} <span>@if( $additionalPreference["is_token_currency_enable"])
                                        {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->total_other_taxes_amount * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format($order->total_other_taxes_amount * @$clientCurrency->doller_compare)}}@endif</span></li>
                                   @endif

                                    @if($order->luxury_option_id == 4)
                                        <li>{{__('Security Amount')}}<span>{{Session::get('currencySymbol').decimal_format($security_amount)}}</span></li>
                                    @endif

                                    @if($order->rental_protection_amount > 0)
                                        <li>{{__('Rental Protection Amount')}}<span>{{Session::get('currencySymbol').decimal_format($order->rental_protection_amount)}}</span></li>
                                    @endif

                                    @if($order->booking_option_price > 0)
                                        <li>{{__('Booking Option Amount')}}<span>{{Session::get('currencySymbol').decimal_format($order->booking_option_price)}}</span></li>
                                    @endif

                                    @if($product->slot_id != '' && $product->delivery_date != '' && $product->slot_price != '')

                                        <li>{{__('Slot Delivery Fees')}} <span>{{Session::get('currencySymbol')}} {{$order->slot_delivery_fees??'0'}}</span></li>

                                    @endif

                                    @if($order->subscription_discount > 0)
                                        <li>{{__('Subscription Discount')}} <span> - @if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->subscription_discount * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format($order->subscription_discount * @$clientCurrency->doller_compare)}}@endif</span></li>
                                    @endif
                                    @if($order->loyalty_amount_saved > 0)
                                        <li>{{__('Loyalty Amount')}} <span> - @if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->loyalty_amount_saved * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format($order->loyalty_amount_saved * @$clientCurrency->doller_compare)}}@endif</span></li>
                                    @endif
                                    @if($order->wallet_amount_used > 0)
                                        <li>{{$additionalPreference ['is_token_currency_enable'] ? __('Used Token') : __('Wallet Amount')}} <span> @if( $additionalPreference["is_token_currency_enable"])
                                            - {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->wallet_amount_used * @$clientCurrency->doller_compare)) }}@else - {{Session::get('currencySymbol').decimal_format($order->wallet_amount_used * @$clientCurrency->doller_compare)}}@endif</span></li>
                                    @endif
                                    @if($order->gift_card_amount > 0)
                                        <li>{{__('Gift Card Amount')}} <span> {{Session::get('currencySymbol')}}{{decimal_format($order->gift_card_amount * @$clientCurrency->doller_compare)}}</span></li>
                                    @endif
                                    @if($order->tip_amount > 0)
                                        <li>{{__('Tip Amount')}} <span>@if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format($order->tip_amount * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format($order->tip_amount * @$clientCurrency->doller_compare)}}@endif</span></li>
                                    @endif
                                        {{-- <li>{{__('Total')}}<span>@if( $additionalPreference["is_token_currency_enable"])
                                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format(($total+$additional_price) * @$clientCurrency->doller_compare)) }}@else{{Session::get('currencySymbol').decimal_format(($total+$additional_price) * @$clientCurrency->doller_compare)}}@endif</span></li> --}}


                        </ul>
                    </div>

                    <div class="final-total">
                        @php
                            //$total = $order->taxable_amount+$order->total_service_fee+$order->fixed_fee_amount+$order->total_container_charges+$order->total_delivery_fee+$order->tip_amount+$order->subscription_discount+$order->total_amount

                        @endphp
                        @if($serviceType == 'rental')
                        <h3>{{__('Total')}} <span>@if( $additionalPreference["is_token_currency_enable"])
                            {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format(($total) * @$clientCurrency->doller_compare))}}@else{{Session::get('currencySymbol').decimal_format(($total) * @$clientCurrency->doller_compare)}}@endif</span></h3>
                    </div>
                 @else
                <h3>{{__('Total')}} <span>@if( $additionalPreference["is_token_currency_enable"])
                    {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{ getInToken(decimal_format(($total) * @$clientCurrency->doller_compare))}}@else{{Session::get('currencySymbol').decimal_format(($total) * @$clientCurrency->doller_compare)}}@endif</span></h3>
            </div>
                @endif
                    {{-- mohit code added by sohail --}}
                    @if(!empty(@$order->advance_amount) && $order->advance_amount > 0)
                    <div class="total-sec final-total">
                        <h3>{{__('Advance Paid')}} <span>{{Session::get('currencySymbol')}}{{decimal_format(@$order->advance_amount)}}</span></h3>
                    </div>
                    <div class="final-total">
                        <h3>{{__('Pending Amount')}} <span>{{Session::get('currencySymbol')}}{{(decimal_format(($total + $additional_price) * @$clientCurrency->doller_compare)) - decimal_format(@$order->advance_amount)}}</span></h3>
                    </div>
                    @endif
                    {{-- till here --}}
                </div></div>
                        <div class="col-lg-6">
                        <div class="row order-success-sec">
                            <h3 class="col-12">{{__('Summary')}}</h3>
                            <div class="col-sm-12">
                                <ul class="order-detail row">
                                    <li class="col-md-4"><span>{{__('Order ID')}}:</span> <span> {{$order->order_number}}</span></li>
                                    <li class="col-md-8"><span>{{__('Order Date')}}:</span><span> {{ date('F d, Y', strtotime($order->created_at)) }} {{ convertDateTimeInTimeZone($order->created_at, $timezone, 'H:i')}}</span></li>
                                    @if (!empty($order->scheduled_date_time))
                                    <li class="col-md-8"><span>{{__('Scheduled Date')}}:</span><span> {{ date('F d, Y', strtotime($order->scheduled_date_time)) }} {{ convertDateTimeInTimeZone($order->scheduled_date_time, $timezone, 'H:i')}}</span></li>
                                    {{-- <li class="col-8"><span>{{__('Scheduled Date')}}:</span><span> {{ date('F d, Y', strtotime($order->scheduled_date_time)) }} {{ date("H:i",strtotime($order->scheduled_date_time ))}}</span></li> --}}
                                    @endif
                                </ul>
                                <ul class="order-detail row">

                                    <li class="col-12 col-md-4"><span>{{__('Order Total')}}:</span><span>
                                        @if( $additionalPreference["is_token_currency_enable"])

                                        {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{getInToken(decimal_format($total + $additional_price))}}
                                        @else
                                        {{Session::get('currencySymbol').decimal_format($total)}}
                                        @endif

                                        @if($order->payment_option_id != 1 && $order->payment_status!=1 && $order->is_postpay==1)
                                        <span style="color:var(--theme-deafult);">Unpaid</span>
                                        @endif
                                    </span></li>
                                    <li class="Shipping col-8 d-block">
                                        @if($order->luxury_option_id == 1 || $order->luxury_option_id == 6 || $order->luxury_option_id == 4)
                                           <span> {{__('Delivery Address')}}:</span>
                                        <span>
                                        {{ ($order->address->house_number ?? false) ? $order->address->house_number."," : '' }} {{ $order->address ? $order->address->address : ''}}{{$order->address ? ($order->address->pincode ? ", ".$order->address->pincode : '') : ''}}
                                        </span>
                                        @elseif($order->luxury_option_id == 3)
                                        {{getNomenclatureName('Takeaway', true) .' '. __('Address')}}
                                            <span>
                                            {{ $order->vendors->first() ? ($order->vendors->first()->vendor ? ($order->vendors->first()->vendor->address) : __('NA') ) : __('NA') }}
                                            </span>
                                        @endif
                                    </li>
                                </ul>
                                <ul class="order-detail row">
                                    <li class="col-12 payment-mode"><span>{{__('Payment Method')}}:</span>
                                        <span>{{__($order->paymentOption->title)}}</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<!-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script> -->

    <script src="https://cdn.socket.io/4.1.2/socket.io.min.js" integrity="sha384-toS6mmwu70G0fw54EGlWWeA4z3dyJ+dlXBtSURSKN4vyRFOcxd3Bzjj/AoOwY+Rg" crossorigin="anonymous"></script>

    <script>
        var url = window.location.href;
        var arr = url.split("/");
        var result = arr[2];
        $(function(){
            let ip_address = result;
            let socket_port = "3100";
            let socket = io(ip_address + ':' + socket_port);
            let message = "jhlh";
            socket.emit('sendChatToServer', message);
        });
    </script>
@endsection
