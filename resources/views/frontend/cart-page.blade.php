<style>
    .alInfoIocn .tooltiptext {
        visibility: hidden;
        width: 200px;
        background-color: black;
        color: #fff;
        text-align: center;
        padding: 5px 0;
        border-radius: 6px;
        position: absolute;
        z-index: 1;
        margin-left: 5px;
        margin-top: 5px;
    }

    .alInfoIocn {
        position: absolute;
        top: 0;
        right: 0;
        cursor: pointer;
    }

    .alInfoIocn:hover .tooltiptext {
        visibility: visible;
    }

    .cross-sell .img-outer-box.position-relative img,
    .upsell-sell .img-outer-box.position-relative img {
        position: absolute;
        height: 100%;
        width: 100%;
        object-fit: cover;
    }

    .cross-sell .img-outer-box.position-relative,
    .upsell-sell .img-outer-box.position-relative {
        padding-bottom: 100%;
    }

    .cross-sell .media-body,
    .upsell-sell .media-body {
        padding: 0 10px;
    }

    .cross-sell .media-body .product-description,
    .upsell-sell .media-body .product-description {
        text-align: left;
        padding: 0;
    }

    .cross-sell .slick-slide>div {
        margin: 0 12px;
    }

    .order-user-name p {
        display: inline-block;
    }

    .order-user-name {
        background: #eeeeee;
        padding: 6px 6px;
        border-radius: 4px;
    }
    .cart-checkout_btn #order_placed_btn{padding: 10px 5px !important;display: inline-block;font-size: 14px !important;}

</style>

@php
    $serviceType = Session::get('vendorType');

    $additionalPreference = $getAdditionalPreference;
    $is_service_product_price_from_dispatch_forOnDemand = 0;
    $hidden_token = '';
    if ($additionalPreference['is_token_currency_enable'] == 1) {
        $hidden_token = 'd-none';
    }
    if(($additionalPreference['is_service_product_price_from_dispatch'] == 1) && ( Session::get('vendorType') == 'on_demand')){
        $is_service_product_price_from_dispatch_forOnDemand =1;
    }

    use Illuminate\Support\Facades\Storage;
@endphp



@if ($cart_details->totalQuantity <= 0)
    <div class="container">
        <div class="row mt-2 mb-4 mb-lg-5">
            <div class="col-12 text-center">
                <div class="cart_img_outer" style="height:200px;">
                    <img class="blur-up lazyload" data-src="{{ asset('front-assets/images/empty_cart.png') }}">
                </div>
                <h3>{{ __('Your Cart Is Empty!') }}</h3>
                <p>{{ __('Add items to it now.') }}</p>
                <a class="btn btn-solid" href="{{ url('/') }}">{{ __('Continue Shopping') }}</a>
            </div>
        </div>
    </div>
@else
    <div class="container mt-3 mb-5">

        <div class="row">
            <div class="col-12">
                <div class="row mb-md-1 alFourTemplateCartButtons mt-2 pt-2">
                    <div
                        class="col-sm-6 col-lg-4 mb-2 mb-sm-0 d-lg-flex align-items-lg-center justify-content-lg-between">
                        <a class="btn shoping" href="{{ url('/') }}"><i class="fa fa-arrow-left"
                                aria-hidden="true"></i>
                            {{ __('Continue Shopping') }}</a>
                    </div>
                    <div class="col-md-6">
                        @if (!empty($cart_details->editing_order))
                            <span class="shoping">{{ __('Order') }} {{ $cart_details->editing_order->order_number }}
                                {{ __('being edited') }} <a class="btn shoping discard_editing_order"
                                    href="javascript:void(0)" data-orderid="{{ $cart_details->editing_order->id }}"><i
                                        class="fa fa-trash-o"></i> {{ __('Discard') }}</a></span>
                        @endif
                    </div>
                </div>
                <!-- <div class="page-title-box">
                    <h3 class="page-title text-uppercase mt-lg-4">{{ __('Cart') }}</h3>
                </div> -->
                <div class="cart_response mt-3 mb-3 d-none">
                    <div class="alert p-0" role="alert"></div>
                </div>
                @if (\Session::has('error'))
                    <div class="alert alert-danger">
                        <span>{!! \Session::get('error') !!}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">

            <div class="col-lg-8" id="cart_template">
                <div class="shoping_cart px-3 py-2">
                    <div class="row border-bottom">
                        <div class="col-6">
                            <div class="single_cart_heading">
                                <h3>{{ __('Shopping Cart') }} </h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="item-show-cart text-right">
                                <h4>{{ $cart_details->totalQuantity }} {{ __('Items') }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row border-bottom product_title_add py-1 no-gutters">
                        <div class="col-md-4 col">
                            <span>{{ __('Product Details') }}</span>
                        </div>

                        <div class="col-md-2 col text-center">
                            <span>{{ __('Price') }}</span>
                        </div>
                        @if ($serviceType == 'rental' || $serviceType == 'p2p')
                            <div class="col-md-2 col text-center">
                                @if($serviceType == 'rental' && Session::get('vendorType') == 'p2p')
                                    <span>Duration By(Days)</span>
                                @elseif($serviceType == 'rental')
                                    <span>Duration By(min)</span>
                                @endif
                            </div>
                        @else
                            <div class="col-md-2 col text-center">
                                <span>{{ __('Quantity') }}</span>
                            </div>
                        @endif
                        <div class="col-md-4 col text-center">
                            <span>Total</span>
                        </div>

                    </div>
                    @php
                        $fixed_fee = 0;
                        $fixed_fee_amount = 0;
                        $total_fixed_fee_amount = 0;
                        $price_bifurcation = 0;
                        $total_wallet_amount_used = !empty($cart_details->wallet_amount_used) ? $cart_details->wallet_amount_used : 0;
                        $closed_store = 0;

                        /* Getting other taxes */
                        $tax_fixed_fee_percentage = 0;
                        $tax_container_charges_percentage = 0;
                        $tax_markup_charges_percentage = 0;
                        $tax_service_charges_percentage = 0;
                        $tax_delivery_charges_percentage = 0;
                        $incTax = 0;
                        $product_container_charges_tax_amount = 0;

                        $tax_container_charges_percentage = $cart_details->container_charges_tax;
                        $other_taxes = $cart_details->other_taxes;
                        $other_taxes_string = $cart_details->other_taxes_string;
                    @endphp
                    @foreach ($cart_details->products as $product)
                        <div id="thead_{{ $product->vendor->id }}" class="mt-2 px-0">
                            <div class="row">

                                <div class="col-12">
                                    <div class="countdownholder alert-danger"
                                        id="min_order_validation_error_{{ $product->vendor->id }}"
                                        style="display:none;">Your cart will be expired in </div>
                                </div>
                                @if ($product->is_vendor_closed == 1 && $product->closed_store_order_scheduled == 0)
                                    {{-- {{ $closed_store = 1; }} --}}
                                    <div class="col-12">
                                        <div class="text-danger">
                                            <i
                                                class="fa fa-exclamation-circle"></i>{{ getNomenclatureName('Vendors', true) . __(' is not accepting orders right now.') }}
                                        </div>
                                    </div>
                                @elseif($product->is_vendor_closed == 1 && $product->closed_store_order_scheduled == 1)
                                    <div class="col-12">
                                        <div class="text-danger">
                                            <i class="fa fa-exclamation-circle"></i>
                                            {{ __('We are not accepting orders right now. You can schedule this for ') }}{{ @$product->delaySlot }}
                                        </div>
                                    </div>
                                 @elseif($product->delivery_status_message != '')
                                    <div class="col-12">
                                        <div class="text-danger">
                                            <i class="fa fa-exclamation-circle"></i>
                                          {{ $product->delivery_status_message }}
                                        </div>
                                    </div>
                                @endif


                                @if ($product->vendor->order_min_amount > 0 &&
                                    $product->product_total_amount + $product->vendor->fixed_fee_amount < $product->vendor->order_min_amount)
                                    <div class="col-12" id="MOV_Notification">
                                        <div class="text-danger">
                                            <i class="fa fa-exclamation-circle"></i>
                                            {{ __('We are not accepting orders less then') }}
                                            {{ Session::get('currencySymbol') . decimal_format($product->vendor->order_min_amount) }}
                                        </div>
                                    </div>
                                @endif
                                <div id="mov" style="display:none;">{{ $product->vendor->order_min_amount }}
                                </div>
                                @if ($product->isDeliverable != '' && $product->isDeliverable == 0)
                                <div class="col-12">
                                    <div class="text-danger">
                                        <i class="fa fa-exclamation-circle"></i>
                                        {{ __('Products for this vendor are not deliverable at your area. Please change address or remove product.') }}
                                    </div>
                                </div>
                            @endif

                            </div>
                        </div>

                        <div class="col-12 cart-heading mt-2 px-0">
                            <h5 class="my-1"><b>{{ $product->vendor ? @$product->vendor->name : '' }}</b></h5>
                            <input type="hidden" name="category_name" id="category_name" value="{{ $product->vendor ? @$product->vendor->name : '' }}" />
                        </div>


                        {{-- Product Detail Loop --}}

                        <div id="tbody_{{ $product->vendor->id }}">


                            @foreach ($product->vendor_products as $vendor_product)
                                <div class="row al align-items-md-center vendor_products_tr alFourTemplateCartPage" id="tr_vendor_products_{{ $vendor_product->id }}">
                                    <div class="col-3 col-md-2">
                                        <div class="product_img_grid">
                                            <div class="product-img">
                                                <div class="product-img w-auto  ">
                                                    <input type="checkbox" name="checked_cart_product" class="checked-cart-product" id="checked_cart_product" value="{{$vendor_product->id}}" {{ @$vendor_product->is_cart_checked ? 'checked' : '' }} >
                                                    <i class="fa fa-spinner fa-pulse d-none" id="fa_spinner_{{$vendor_product->id}}" aria-hidden="true" style="color: var(--theme-deafult)"></i>
                                                </div>
                                            </div>
                                            <div class="product-img">
                                                @if (!empty($vendor_product->pvariant->media_one))
                                                    <img class='blur-up lazyload w-100'
                                                        data-src="{{ $vendor_product->pvariant->media_one->pimage->image->path->proxy_url . '200/200' . $vendor_product->pvariant->media_one->pimage->image->path->image_path }}">
                                                @elseif(!empty($vendor_product->pvariant->media_second) && !empty($vendor_product->pvariant->media_second->image))
                                                    <img class='blur-up lazyload w-100'
                                                        data-src="{{ $vendor_product->pvariant->media_second->image->path->proxy_url . '200/200' . $vendor_product->pvariant->media_second->image->path->image_path }}">
                                                @elseif(!empty($vendor_product->image_ur))
                                                    <img class='blur-up lazyload w-100'
                                                        data-src="{{ $vendor_product->image_url }}">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-9 col-md-10">
                                        <div class="row align-items-md-center">
                                            <div class="col-md-3 order-md-1">
                                                <h4 class="cart_product_name">
                                                    {{ $vendor_product->product->category_name ? @$vendor_product->product->category_name->name : ''}}</h4>
                                                <h4 class="mt-0 mb-1" style="word-wrap: break-word; line-height:20px">
                                                    <strong>{{ @$vendor_product->product->translation_one ? @$vendor_product->product->translation_one->title : @$vendor_product->product->sku }}</strong>
                                                </h4>
                                                <input type="hidden" name="hidden_product_name"
                                                    id="hidden_product_name"
                                                    value="{{ $vendor_product->product->translation_one ? $vendor_product->product->translation_one->title : $vendor_product->product->sku }}" />
                                                @if (isset($vendor_product->pvariant->vset))
                                                    @foreach ($vendor_product->pvariant->vset as $vset)
                                                        @if ($vset->variant_detail->trans)
                                                            <label><span><b>{{ @$vset->variant_detail->trans->title }}:</b></span>
                                                                {{ @$vset->option_data->trans->title }}</label>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>

                                            @if (isset($vendor_product->pvariant->actual_price))

                                            @php


                                            @endphp
                                                <div class="col-6 col-md-2 mb-1 mb-md-0 order-md-2 p-0">
                                                    <div class="items-price">
                                                        @if ($additionalPreference['is_token_currency_enable'])
                                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($vendor_product->price??0 * (@$vendor_product->days ?? 1))) }}
                                                        @else
                                                            {{ Session::get('currencySymbol') . decimal_format($vendor_product->pvariant->price*$cart_details->conversion_rate ?? 0 * (@$vendor_product->days ?? 1)) }}
                                                        @endif
                                                        @if (in_array($serviceType, ['appointment', 'on_demand']))
                                                            <span class="">
                                                                {{ $vendor_product->total_booking_time > 0 ? $vendor_product->total_booking_time : 0 }}
                                                                {{ __(' min') }} </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            @if (!empty(@$vendor_product->quantity_price))
                                                <div class="col-6 col-md-2 text-left order-md-4">
                                                    @if ($serviceType == 'p2p')

                                                        @php


                                                            $additionalPrice = 0;
                                                            if ($vendor_product->pvariant->incremental_price_per_min > 0) {
                                                                $additionalPrice = ($vendor_product->additional_increments_hrs_min/(60*24)) * $vendor_product->quantity_price;
                                                            }
                                                            $price = $vendor_product->pvariant->price;



                                                            if(@$vendor_product->days  <= 7){
                                                                $price = $vendor_product->pvariant->price;
                                                            }elseif(@$vendor_product->days >= 7 && @$vendor_product->days < 30){
                                                                $price = $vendor_product->pvariant->week_price;
                                                            }else{
                                                                $price = $vendor_product->pvariant->month_price;
                                                            }



                                                        @endphp

                                                        <div class="items-price">
                                                            @if ($additionalPreference['is_token_currency_enable'])
                                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($price * (@$vendor_product->days ?? 0))) }}
                                                            @else
                                                                {{ Session::get('currencySymbol') . decimal_format($price * (@$vendor_product->days ?? 1)) }}
                                                            @endif
                                                        </div>
                                                    @elseif ($serviceType == 'rental')
                                                        @php
                                                            $additionalPrice = 0;
                                                            if ($vendor_product->pvariant->incremental_price_per_min > 0) {
                                                                $additionalPrice = $vendor_product->additional_increments_hrs_min / $vendor_product->pvariant->incremental_price_per_min;
                                                            }
                                                        @endphp
                                                        <div class="items-price">
                                                            @if ($additionalPreference['is_token_currency_enable'])
                                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format((@$vendor_product->quantity_price) + $additionalPrice)) }}
                                                            @else
                                                                {{ Session::get('currencySymbol') . decimal_format((@$vendor_product->quantity_price) + $additionalPrice) }}
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="items-price">
                                                            @if ($additionalPreference['is_token_currency_enable'])
                                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format(@$vendor_product->quantity_price)) }}
                                                            @else
                                                                {{ Session::get('currencySymbol') . decimal_format(@$vendor_product->quantity_price) }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif


                                            @if ($serviceType == 'rental' || $serviceType == 'p2p')
                                                <div class="col-10 col-md-4 text-md-center order-md-3">
                                                    <div class="number d-flex justify-content-md-center border-0">
                                                        <div style="display: none !important;"
                                                            class="counter-container d-flex align-items-center">
                                                            <input placeholder="1" type="number" min="0"
                                                                data-minimum_order_count="{{ $vendor_product->product->minimum_order_count }}"
                                                                data-batch_count="{{ $vendor_product->product->batch_count }}"
                                                                value="{{ $vendor_product->quantity }}"
                                                                class="input-number" step="0.01"
                                                                id="quantity_{{ $vendor_product->id }}" readonly>

                                                        </div>
                                                        <div class="qty-box alCartInput">
                                                            <div class="input-group">
                                                                @php
                                                                    if($serviceType == 'p2p'){
                                                                        $dura = getDaysBetweenTwoDates($vendor_product->start_date_time, $vendor_product->end_date_time);
                                                                    }else{
                                                                        $dura = getHoursMinutes($vendor_product->total_booking_time);
                                                                    }
                                                                @endphp
                                                                <p class="mb-0">{{ $dura }}</p>

                                                            </div>
                                                        </div>

                                                    </div>
                                                    @if ($cart_details->pharmacy_check == 1)
                                                    @php
                                                        $class = '';
                                                        if($vendor_product->product->validate_pharmacy_check == 1){
                                                            $class = 'validate_prescription';
                                                        }
                                                    @endphp

                                                    @if ($vendor_product->product->pharmacy_check == 1)
                                                        <button type="button"
                                                            class="float-left btn btn-solid prescription_btn mt-2 {{$class}}"
                                                            data-cart="{{ $vendor_product->cart_id }}"
                                                            data-product="{{ $vendor_product->product->id }}"
                                                            data-vendor_id="{{ $vendor_product->vendor_id }}" data-cart_product_prescription="{{ $vendor_product->cart_product_prescription??0 }}">{{ __('Add Prescription') }}</button>
                                                        @if ($vendor_product->cart_product_prescription > 0)
                                                            <h4 class="mt-0 mb-1"
                                                                style="word-wrap: break-word; line-height:20px">
                                                                <strong>{{ $vendor_product->cart_product_prescription }}
                                                                    {{ __('Prescription Added') }}</strong></h4>
                                                        @endif
                                                    @endif
                                                @endif
                                                </div>
                                            @elseif(($serviceType == 'appointment') || ($is_service_product_price_from_dispatch_forOnDemand ==1))
                                                <div class="col-10 col-md-4 text-md-center order-md-3">
                                                    1
                                                </div>
                                            @elseif($vendor_product->product->is_long_term_service == 1)
                                                <div class="col-10 col-md-4 text-md-center order-md-3">
                                                    <span class="">1</span>
                                                </div>
                                            @else
                                                <div class="col-10 col-md-4 text-md-center order-md-3">
                                                    <div class="number d-flex justify-content-md-center">
                                                        <div class="counter-container d-flex align-items-center">
                                                            <span class="minus qty-minus"
                                                                data-minimum_order_count="{{ $vendor_product->product->minimum_order_count }}"
                                                                data-batch_count="{{ $vendor_product->product->batch_count }}"
                                                                data-id="{{ $vendor_product->id }}"
                                                                data-base_price="{{ !empty($vendor_product->pvariant->price) ? $vendor_product->pvariant->price : '' }}"
                                                                data-vendor_id="{{ $vendor_product->vendor_id }}">
                                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                                            </span>
                                                            <input placeholder="1" type="text"
                                                                data-minimum_order_count="{{ $vendor_product->product->minimum_order_count }}"
                                                                data-batch_count="{{ $vendor_product->product->batch_count }}"
                                                                value="{{ $vendor_product->quantity }}"
                                                                class="input-number" step="0.01"
                                                                id="quantity_{{ $vendor_product->id }}" readonly>
                                                            <span class="plus qty-plus"
                                                                data-minimum_order_count="{{ $vendor_product->product->minimum_order_count }}"
                                                                data-batch_count="{{ $vendor_product->product->batch_count }}"
                                                                data-id="{{ $vendor_product->id }}"
                                                                data-base_price="{{ !empty($vendor_product->pvariant->price) ? $vendor_product->pvariant->price : '' }}">
                                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    @if ($cart_details->pharmacy_check == 1)

                                                        @if ($vendor_product->product->pharmacy_check == 1)
                                                            <button type="button"
                                                                class="float-left btn btn-solid prescription_btn mt-2"
                                                                data-cart="{{ $vendor_product->cart_id }}"
                                                                data-product="{{ $vendor_product->product->id }}"
                                                                data-vendor_id="{{ $vendor_product->vendor_id }}">{{ __('Add Prescription') }}</button>
                                                            @if ($vendor_product->cart_product_prescription > 0)
                                                                <h4 class="mt-0 mb-1"
                                                                    style="word-wrap: break-word; line-height:20px;float: left;">
                                                                    <strong>{{ $vendor_product->cart_product_prescription }}
                                                                        {{ __('Prescription Added') }}</strong></h4>
                                                            @endif
                                                        @endif
                                                    @endif

                                                    @if (isset($vendor_product->product_delivery_fee) && $vendor_product->product_delivery_fee > 0)
                                                        <div class="float-left mt-2">Delivery Fee : <span
                                                                style="color: #000;font-size: 14px;font-weight: 500;">{{ Session::get('currencySymbol') }}{{ $vendor_product->product_delivery_fee  }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="col-2 col-md-1 text-right text-md-center p-in order-md-5">
                                                <a class="action-icon d-block remove_product_via_cart"
                                                    style="cursor: pointer;" data-product="{{ $vendor_product->id }}"
                                                    data-vendor_id="{{ $vendor_product->vendor_id }}">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                </a>
                                            </div>

                                        </div>

                                        @if($serviceType == 'rental')
                                            <hr class="my-2">
                                            <div class="row align-items-md-center alRentalStartDate">
                                                   <div class="col-3">
                                                    <h6 class="m-0 pl-0">{{ __('Start Date') }}</h6>
                                                    <p>{{ date('m/d/Y g:i A', strtotime($vendor_product->start_date_time)) }}
                                                    </p>
                                                </div>
                                                <div class="col-3">
                                                    <h6 class="m-0 pl-0">{{ __('End Date') }}</h6>
                                                    <p>{{ date('m/d/Y g:i A', strtotime($vendor_product->end_date_time)) }}
                                                    </p>
                                                </div>

                                                @if($vendor_product->product->security_amount > 0)
                                                <div class="col-3">
                                                    <h6 class="m-0 pl-0" style="font-weight: 600;">{{ __('Security Amount') }}</h6>
                                                    <p>{{ Session::get('currencySymbol') . decimal_format($vendor_product->product->security_amount) }}</p>
                                                </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if (count($vendor_product->addon) != 0)
                                            <hr class="my-2">
                                            <div class="row align-items-md-center add_head">
                                                <div class="col-12">
                                                    <h6 class="m-0 pl-0"><b>{{ __('Add Ons') }}</b></h6>
                                                </div>
                                            </div>

                                            @foreach ($vendor_product->addon as $ad => $addon)
                                                @if ($addon->option)
                                                    <div class="row">
                                                        <div class="col-md-3 col col-sm-4 items-details">
                                                            <p class="p-0 m-0">{{ $addon->option->title }}</p>
                                                        </div>
                                                        <div class="col-md-6 col col-sm-4">
                                                            <div class="extra-items-price">
                                                                @if ($additionalPreference['is_token_currency_enable'])
                                                                    {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format(@$addon->option->price_in_cart *(@$addon->option->multiplier ?? 1))) }}
                                                                @else
                                                                    {{ Session::get('currencySymbol') . decimal_format(@$addon->option->price_in_cart * (@$addon->option->multiplier ?? 1)) }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col col-sm-4">
                                                            <div class="extra-items-price">
                                                                @if ($additionalPreference['is_token_currency_enable'])
                                                                    {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format((@$addon->option->quantity_price ?? 0))) }}
                                                                @else
                                                                    {{ Session::get('currencySymbol') . decimal_format((@$addon->option->quantity_price??0)) }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if (!empty($vendor_product->pvariant->container_charges) && $vendor_product->pvariant->container_charges > 0)
                                            <div class="row">
                                                <div class="col-md-3 col-sm-4 items-details text-left">
                                                    <p class="p-0 m-0 alert-danger">{{ __('Container Charges') }} *
                                                    </p>
                                                </div>
                                                <div class="col-md-2 col-sm-4 text-center">
                                                    <div class="extra-items-price">
                                                        @if ($additionalPreference['is_token_currency_enable'])
                                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($vendor_product->pvariant->container_charges)) }}
                                                        @else
                                                            {{ Session::get('currencySymbol') . decimal_format($vendor_product->pvariant->container_charges) }}
                                                        @endif


                                                        {{-- /* --- Vendor Tax Get Percentage ---- */ --}}
                                                        @php
                                                            foreach ($cart_details->taxRates as $index => $tax) {
                                                                if ($vendor_product->product->container_charges_tax_id != null) {
                                                                    if ($vendor_product->product->container_charges_tax_id == $index) {
                                                                        $product_container_charges_tax_amount += ($vendor_product->quantity_container_charges * $tax->tax_rate) / 100;
                                                                        $incTax = 1;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                    </div>
                                                </div>
                                                <div class="col-md-7 col-sm-4 text-right">
                                                    <div class="extra-items-price">
                                                        @if ($additionalPreference['is_token_currency_enable'])
                                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($vendor_product->quantity_container_charges)) }}
                                                        @else
                                                            {{ Session::get('currencySymbol') . decimal_format($vendor_product->quantity_container_charges) }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif


                                        {{-- Home Service Schedual code Start at down --}}

                                        @if (
                                            ($cart_details->closed_store_order_scheduled == 1 || $client_preference_detail->off_scheduling_at_cart != 1)
                                            && ((in_array($serviceType, ['appointment', 'on_demand'])  )
                                            && ( ($vendor_product->product->mode_of_service == 'schedule') || ($is_service_product_price_from_dispatch_forOnDemand ==1))
                                         ))
                                            <hr class="my-1">
                                            @if ($client_preference_detail->business_type != 'laundry')
                                                @if (@$vendor_product->product->is_slot_from_dispatch != 1 || $vendor_product->product->Requires_last_mile != 1)
                                                @if($cart_details->schedule_type == 'schedule')
                                                    <div class="row mb-1 d-flex align-items-center vendor_product_schedule_datetime"
                                                        style="{{ ((($cart_details->schedule_type == 'schedule' || $vendor_product->product->mode_of_service=='schedule')) ||  ($is_service_product_price_from_dispatch_forOnDemand ==1)) ? '' : 'display:none!important' }}">
                                                        <div class="col-{{( $is_service_product_price_from_dispatch_forOnDemand ==1 ? '9 d-flex ' : '5') }} offset-3 text-left">
                                                            <p class="text-dark">{{ __('Scheduled Slot') }} :</p>
                                                             @if($is_service_product_price_from_dispatch_forOnDemand ==1)
                                                                <p class="m-0 mx-2">{{ $vendor_product->selected_dispatcher_time   }}  </p>

                                                                <p class="m-0" > {{$vendor_product->schedule_slot_name}}</p>

                                                            @endif
                                                        </div>

                                                        @if($is_service_product_price_from_dispatch_forOnDemand !=1)
                                                            <div class="col-4 vendor_slot_cart">
                                                                <input type="hidden" class="custom-control-input vendor_product_schedule_datetime check"
                                                                    id="tasknow" name="task_type" value='schedule'>

                                                                        @if ($product->slotsCnt != 0)
                                                                        <input type="date"
                                                                            class="form-control vendor_schedule_datetime"
                                                                            placeholder="Inline calendar"
                                                                            data-schedule_type="date"
                                                                            data-vendor_id="{{ $product->vendor_id }}"
                                                                            data-cart_product_id="{{ $product->cart_product_id }}"
                                                                            value="{{ !empty($vendor_product->scheduled_date_time) ? \Carbon\Carbon::parse($vendor_product->scheduled_date_time)->format('Y-m-d') : \Carbon\Carbon::parse($product->delay_date)->format('Y-m-d') }}"
                                                                            min="{{ $product->delay_date != '0' ? \Carbon\Carbon::parse($product->delay_date)->format('Y-m-d') : '' }}">

                                                                            <select
                                                                                class="form-control vendor_product_schedule_slot vendor_schedule_slot "
                                                                                id="vendor_schedule_slot_{{ $product->vendor_id }}"
                                                                                data-schedule_type="time"
                                                                                data-vendor_id="{{ $product->vendor_id }}"
                                                                                data-cart_product_id="{{ $product->cart_product_id }}">
                                                                                <option value="">{{ __('Select Slot') }}
                                                                                </option>
                                                                                @foreach ($product->slots as $slot)
                                                                                    <option value="{{ $slot->value }}"
                                                                                        {{ $slot->value == $product->schedule_slot ? 'selected' : '' }}>
                                                                                        {{ @$slot->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        @else
                                                                            @if ($cart_details->delay_date != 0)
                                                                                <input type="datetime-local"
                                                                                    id="vendor_schedule_slot_{{ $product->vendor_id }}"
                                                                                    data-schedule_type="ProductDateTime"
                                                                                    data-vendor_id="{{ $product->vendor_id }}"
                                                                                    data-cart_product_id="{{ $product->cart_product_id }}"
                                                                                    class="form-control vendor_schedule_datetime"
                                                                                    placeholder="Inline calendar"
                                                                                    value="{{ $vendor_product->manual_scheduled_date_time != '' ? $vendor_product->manual_scheduled_date_time : $product->delay_date }}"
                                                                                    min="{{ $cart_details->delay_date != '0' ? $cart_details->delay_date : '' }}"
                                                                                    data-cart_product_id="{{ $vendor_product->id }}">
                                                                            @else
                                                                                <input type="datetime-local"
                                                                                    id="vendor_schedule_slot_{{ $product->vendor_id }}"
                                                                                    data-schedule_type="ProductDateTime"
                                                                                    data-vendor_id="{{ $product->vendor_id }}"
                                                                                    data-cart_product_id="{{ $product->cart_product_id }}"
                                                                                    class="form-control vendor_schedule_datetime"
                                                                                    placeholder="Inline calendar"
                                                                                    value="{{ $vendor_product->manual_scheduled_date_time != '' ? $vendor_product->manual_scheduled_date_time : $product->delay_date }} "
                                                                                    min="{{ $cart_details->delay_date != '0' ? $cart_details->delay_date : '' }}"
                                                                                    data-cart_product_id="{{ $vendor_product->id }}">
                                                                            @endif
                                                                        @endif

                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                                @else
                                                    {{-- Dispatch sloat shot --}}
                                                    @include('frontend.cart.dispatchSlots')
                                                @endif
                                            @endif
                                        @endif
                                        @if ($vendor_product->product->is_long_term_service == 1)
                                            @include('frontend.cart.longTermTimeSelection')
                                        @endif
                                        @if( $vendor_product->product->same_day_delivery ==  1 && $vendor_product->product->next_day_delivery ==  1)
                                            @include('frontend.cart.deliverySlotSelection')
                                        @endif

                                        @if ($vendor_product->product->is_long_term_service == 1)
                                            @include('frontend.cart.longTermTimeSelection')
                                        @endif

                                    </div>

                                    @if ($vendor_product->product->delay_order_time->delay_order_hrs != '' &&
                                        $vendor_product->product->delay_order_time->delay_order_min != '' &&
                                        ($vendor_product->product->delay_order_time->delay_order_hrs != 0 ||
                                            $vendor_product->product->delay_order_time->delay_order_hrs != 0))
                                        <div class="col-12">
                                            <div class="text-danger" style="font-size:12px;">
                                                <i class="fa fa-exclamation-circle"></i>Preparation Time is
                                                @if ($vendor_product->product->delay_order_time->delay_order_hrs > 0)
                                                    {{ $vendor_product->product->delay_order_time->delay_order_hrs }}
                                                    Hrs
                                                @endif
                                                @if ($vendor_product->product->delay_order_time->delay_order_min > 0)
                                                    {{ $vendor_product->product->delay_order_time->delay_order_min }}
                                                    Minutes
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    @if (!empty($vendor_product->product_out_of_stock) && $vendor_product->product_out_of_stock == 1)
                                        <div class="col-12">
                                            <div class="text-danger" style="font-size:12px;">
                                                <i
                                                    class="fa fa-exclamation-circle"></i>{{ __('This Product is out of stock') }}
                                            </div>
                                        </div>
                                    @endif
                                    @if ($client_preference_detail->product_order_form == 1)
                                        @if ($vendor_product->faq_count > 0 &&
                                            ($vendor_product->user_product_order_form == '' || $vendor_product->user_product_order_form == null))
                                            <div class=" col-3 {{ $vendor_product->faq_count }}  "
                                                id="product_faq_dev_{{ $vendor_product->product_id }}">
                                                <input type="hidden" name="product_faq_ids"
                                                    value="{{ $vendor_product->product_id }}">
                                                <div class="text-center my-3 btn-product-order-form-div">
                                                    <button class="clproduct_cart_order_form btn btn-solid w-100"
                                                        id="add__cart_product_form"
                                                        data-dev_remove_id="product_faq_dev_{{ $vendor_product->product_id }}"
                                                        data-product_id="{{ $vendor_product->product_id }}"
                                                        data-vendor_id="{{ $vendor_product->vendor_id }}">Fill Form</button>
                                                </div>
                                            </div>
                                        @endif
                                    @endif


                                </div>
                                <input type="hidden" name="cart_product_ids[]"
                                    value="{{ $vendor_product->product_id }}">
                                <hr class="my-1">
                            @endforeach

                            {{-- End Product Detail Loop --}}

                            <div class="row my-2">
                                @if (!$cart_details->guest_user)
                                    <div class="col-lg-6">
                                        @if ($product->is_promo_code_available > 0)
                                            <div class="coupon_box w-100 d-flex align-content-center">
                                                <img class="blur-up lazyload"
                                                    data-src="{{ asset('assets/images/discount_icon.svg') }}">
                                                <label class="mb-0 ml-2">
                                                    @if ($product->coupon && $product->coupon->promo )
                                                        {{  @$product->coupon->promo->name ?? '' }}
                                                    @else
                                                        <a href="javascript:void(0)" class="promo_code_list_btn ml-1"
                                                            data-vendor_id="{{ $product->vendor->id }}"
                                                            data-cart_id="{{ $cart_details->id }}"
                                                            data-amount="{{ $product->product_sub_total_amount }}">{{ __('Select a promo code') }}</a>
                                                    @endif
                                                </label>
                                            </div>
                                            @if ($product->coupon && $product->coupon->promo )
                                                <label class="p-1 m-0"><a href="javascript:void(0)"
                                                        class="remove_promo_code_btn ml-1"
                                                        data-coupon_id="{{ @$product->coupon->promo->id ?? '' }}"
                                                        data-cart_id="{{ $cart_details->id }}">Remove</a></label>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                                <div class="col-lg-6">
                                    @if ($product->delOptions)
                                        <div
                                            class="row mb-1 d-flex align-items-center  @if ($product->promo_free_deliver == 1) {{ $product->promo_free_deliver }} org_price @endif ">
                                            <div class="col-5 text-lg-right">
                                                <label class="m-0 radio">
                                                    {{ __('Delivery Fee') }} :</label>
                                            </div>
                                            <div class="col-7 text-right">
                                                {!! $product->delOptions !!}
                                            </div>
                                        </div>
                                    @endif
                                    @if (!empty($product->processor_product) && $product->processor_product->is_processor_enable == 1)
                                        <div class="row mb-1 d-flex align-items-center">
                                            <div class="col-5 text-lg-right">
                                                <label class="m-0 radio">
                                                    {{ __('Processor Name') }} :</label>
                                            </div>
                                            <div class="col-7">
                                                {!! ($product->processor_product ? $product->processor_product->name : '') !!}
                                            </div>
                                        </div>
                                        <div class="row mb-1 d-flex align-items-center">
                                            <div class="col-5 text-lg-right">
                                                <label class="m-0 radio">
                                                    {{ __('Processor Date') }} :</label>
                                            </div>
                                            <div class="col-7">
                                                {!! $product->processor_product->date !!}
                                            </div>
                                        </div>
                                        {{-- <div class="row mb-1 d-flex align-items-center">
                                <div class="col-5 text-lg-right">
                                    <label class="m-0 radio">
                                        {{ __('Processor Address') }} :</label>
                                </div>
                                <div class="col-7">
                                    {!! $product->processor_product->address !!}
                                </div>
                            </div> --}}
                                    @endif
                                    @if ($product->vendor->fixed_fee_amount > 0)
                                        <div class="row mb-1 d-flex align-items-center">
                                            <div class="col-5 text-lg-right">
                                                <label class="m-0 radio">
                                                    {{getNomenclatureName('Fixed Fee', true)}} :</label>
                                            </div>
                                            <div class="col-7">
                                                @if ($additionalPreference['is_token_currency_enable'])
                                                    {{ getInToken($product->vendor->fixed_fee_amount) }}@else{{ $product->vendor->fixed_fee_amount }}
                                                @endif
                                            </div>

                                        </div>
                                    @endif

                                    {{-- Home Service Schedual code Start at down --}}
                                    @if (($cart_details->closed_store_order_scheduled == 1 ||
                                        $client_preference_detail->off_scheduling_at_cart != 1) &&
                                        ($cart_details->vendorCnt > 1 && !in_array($serviceType, ['appointment', 'on_demand'])))
                                        @if ($client_preference_detail->business_type != 'laundry')
                                            <div class="row mb-1 d-flex align-items-center"
                                                style="{{ $product->schedule_type == 'schedule' ? '' : 'display:none!important' }}">
                                                <div class="col-5 text-lg-right">
                                                    <label class="m-0 radio">
                                                        {{ __('Scheduled Slot') }} :</label>
                                                </div>
                                                <div class="col-7 vendor_slot_cart">

                                                    @if ($product->slotsCnt != 0)
                                                        <input type="hidden" class="custom-control-input check"
                                                            id="tasknow" name="task_type" value='schedule'>
                                                        <input type="date"
                                                            class="form-control vendor_schedule_datetime"
                                                            placeholder="Inline calendar" data-schedule_type="date"
                                                            data-vendor_id="{{ $product->vendor_id }}"
                                                            data-cart_product_id="{{ $product->cart_product_id }}"
                                                            value="{{ $product->scheduled_date_time != '' ? $product->scheduled_date_time : $product->delay_date }}"
                                                            min="{{ $product->delay_date != '0' ? $product->delay_date : '' }}">
                                                        <select class="form-control vendor_schedule_slot"
                                                            id="vendor_schedule_slot_{{ $product->vendor_id }}"
                                                            data-schedule_type="time"
                                                            data-vendor_id="{{ $product->vendor_id }}"
                                                            data-cart_product_id="{{ $product->cart_product_id }}">
                                                            <option value="">{{ __('Select Slot') }} </option>
                                                            @foreach ($product->slots as $slot)
                                                                <option value="{{ $slot->value }}"
                                                                    {{ $slot->value == $product->selected_slot ? 'selected' : '' }}>
                                                                    {{ @$slot->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        {{-- onchange="checkSlotAvailability(this);" --}}
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif


                                    {{-- Home Service Schedual code end at down --}}

                                    <div class="row mb-1">
                                        <div class="col-5 text-lg-right">
                                            @if ($product->coupon_amount_used > 0)
                                                <label class="m-0 radio">{{ __('Coupon Discount') }} :</label>
                                            @endif
                                        </div>
                                        <div class="col-7 text-right">
                                            @if ($product->coupon_amount_used > 0)
                                                <p class="total_amt m-0">
                                                    @if ($additionalPreference['is_token_currency_enable'])
                                                        {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($product->coupon_amount_used)) }}@else{{ Session::get('currencySymbol') . decimal_format($product->coupon_amount_used) }}
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($product->bid_vendor_discount > 0)
                                        <div class="row">
                                            <div class="col-5 text-lg-right">
                                                <label class="m-0 radio">{{ __('Bid Discount') }} :</label>
                                            </div>
                                            <div class="col-7 text-right">
                                                <p class="total_amt m-0">{{ Session::get('currencySymbol') }}
                                                    {{ decimal_format($product->bid_vendor_discount) }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if (@$product->vendor->service_charge_amount > 0)
                                        <div class="row">
                                            <div class="col-5 text-lg-right">
                                                <label class="m-0 radio">{{ __('Service Fee') }} :</label>
                                            </div>
                                            <div class="col-7 text-right">
                                                <p class="total_amt m-0">
                                                    {{ showPriceWithCurrency($product->vendor->service_charge_amount) }}</p>
                                            </div>
                                        </div>
                                    @endif


                                    <div class="row">
                                        {{-- @if ($cart_details->vendorCnt > 1) --}}
                                        <div class="col-5 text-lg-right">
                                            <label class="m-0 radio">{{ __('Sub Total') }} :</label>
                                        </div>
                                        <div class="col-7 text-right">
                                            {{-- <p class="total_amt m-0">{{Session::get('currencySymbol')}} {{decimal_format($product->product_total_amount + $product->vendor->fixed_fee_amount - $product->bid_vendor_discount??0)}}</p> --}}


                                            <p class="total_amt m-0">

                                                @if ($additionalPreference['is_token_currency_enable'])

                                                    {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($product->product_total_amount)) }}
                                                    @else
                                                    @if($serviceType == 'rental')
                                                    {{ Session::get('currencySymbol') . decimal_format($product->product_sub_total_amount + $product->vendor->fixed_fee_amount +$additionalPrice - $product->bid_vendor_discount ?? 0) }}
                                                    @else
                                                    {{ Session::get('currencySymbol') . decimal_format($product->product_sub_total_amount + $product->vendor->fixed_fee_amount + $vendor_product->pvariant->container_charges - $product->bid_vendor_discount ?? 0  ) }}
                                                    @endif
                                                @endif
                                            </p>
                                        </div>
                                        {{-- @endif --}}
                                    </div>



                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if(checkColumnExists('cart_products','recurring_booking_type'))
                        @if ($vendor_product->recurring_booking_type == 1 || $vendor_product->recurring_booking_type == 2 || $vendor_product->recurring_booking_type == 3 || $vendor_product->recurring_booking_type == 4)
                            @include('frontend.cart.recurrentBooking')
                        @endif
                    @endif
                </div>
                <div class="row m-0">
                    <div class="col-lg-12 left_box new_cart mt-4 p-3" id="">
                       <h5 class="mb-3">Uploaded Documents</h5>
                    
                       @if($uploaded_documents && count($uploaded_documents) > 0)
                           <div class="uploaded-documents-list">
                               @foreach($uploaded_documents as $document)
                                   <div class="document-item d-flex align-items-center justify-content-between p-2 mb-2 border rounded cursor-pointer" 
                                        onclick="window.open('{{ Storage::disk('s3')->url($document->file_path) }}', '_blank')"
                                        style="cursor: pointer; transition: all 0.3s ease;">
                                       <div class="document-info d-flex align-items-center">
                                           <i class="fa fa-file me-2"></i>
                                           <span class="document-name">{{ $document->file_name ?? basename($document->file_path) }}</span>
                                       </div>
                                       <div class="document-actions">
                                           <i class="fa fa-external-link" aria-hidden="true"></i>
                                       </div>
                                   </div>
                               @endforeach
                           </div>
                       @else
                           <div style="color: #dc3545;">No documents uploaded yet.</div>
                       @endif
                    </div>

                    <style>
                        .document-item:hover {
                            background-color: #f8f9fa;
                            transform: translateY(-2px);
                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                        }
                        .document-name {
                            margin-left: 8px;
                            color: #333;
                        }
                        .document-actions {
                            color: #007bff;
                        }
                    </style>
                </div>
                <!-- <div class="row m-0">
                    <div class="col-lg-12 left_box new_cart mt-4 p-3" id="left_address">
                        {!! $cart_details->left_section !!}
                    </div>
                </div> -->

                @if ($cart_details->guest_user)
                    <div class="col-lg-12 left_box new_cart mt-4 p-3">

                    </div>
                @endif
            </div>
            {{-- End Left Section --}}

            {{-- Start Right Section --}}
            <div class="col-lg-4">

                <div class="row m-0">
                    <div class="cart-summary p-2 pb-4">
                        <div class="col-12 mb-2">
                            <h5 class="order_text">{{ __('Order Summary') }}</h5>
                            @if (array_key_exists('gift_card_id', (array) $cart_details) && empty($cart_details->gift_card))
                                <a id='open_gift_card' href="javascript:void(0)"
                                    class="btn btn-solid w-100">{{ __('open gift Card') }}</a>
                            @endif
                        </div>
                        <input type="hidden" name="without_category_kyc"
                            value="{{ $cart_details->without_category_kyc }}">

                            @if(checkColumnExists('cart_products','recurring_booking_type'))
                                @if ($vendor_product->recurring_booking_type == 1)
                                    <input type="hidden" id="is_recurring_booking" value="{{ $vendor_product->recurring_booking_type }}" />
                                @endif
                            @else
                                <input type="hidden" id="is_recurring_booking" value="0" />
                            @endif


                        @if ($client_preference_detail->category_kyc_documents == 1)
                            @if (@$cart_details->category_kyc_count > 0)
                                <div class=" col-3 {{ $cart_details->category_kyc_count }}"
                                    id="category_kyc_dev_{{ $cart_details->category_rendem_id }}">
                                    <input type="hidden" name="category_kyc_ids"
                                        value="{{ $cart_details->category_rendem_id }}">
                                    <div class="text-center my-3 btn-category_kyc-div">
                                        <button class="cl_category_kyc_form btn btn-solid w-100"
                                            id="add__category_kyc_form"
                                            data-dev_remove_id="category_kyc_dev_{{ $cart_details->category_rendem_id }}"
                                            data-category_id="{{ $cart_details->category_ids }}">{{ __('Order Documents') }}</button>
                                    </div>
                                </div>
                            @endif
                        @endif
                        <div class="col-12">
                            @if (isset($cart_details) && !empty($cart_details) && $client_preference_detail->business_type == 'laundry')
                                <div class="row">
                                    <div class="col-4"><span>{{ __('Comment for Pickup Driver ') }}</span></div>
                                    <div class="col-8"><input class="form-control" type="text"
                                            placeholder="{{ __('Eg. Please reach before time if possible') }}"
                                            id="comment_for_pickup_driver"
                                            value="{{ $cart_details->comment_for_pickup_driver ?? '' }}"
                                            name="comment_for_pickup_driver"></div>
                                </div>
                                <hr class="my-2">
                                <div class="row">
                                    <div class="col-4">{{ __('Comment for Dropoff Driver ') }}</div>
                                    <div class="col-8"><input class="form-control" type="text"
                                            placeholder="{{ __('Eg. Do call me before drop off') }}"
                                            id="comment_for_dropoff_driver"
                                            value="{{ $cart_details->comment_for_dropoff_driver ?? '' }}"
                                            name="comment_for_dropoff_driver"></div>
                                </div>
                                <hr class="my-2">
                                <div class="row">
                                    <div class="col-4">{{ __('Comment for Vendor ') }}</div>
                                    <div class="col-8"><input class="form-control" type="text"
                                            placeholder="{{ __('Eg. Please do the whites separately') }}"
                                            id="comment_for_vendor"
                                            value="{{ $cart_details->comment_for_vendor ?? '' }}"
                                            name="comment_for_vendor"></div>
                                </div>

                                <hr class="my-2">
                                @if ($client_preference_detail->scheduling_with_slots == 1 &&
                                    $client_preference_detail->off_scheduling_at_cart == 0)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">{{ __('Schedule Pickup ') }}</label> <span
                                                class="loaderforjs"><img class="img-fluid" style="display:none;"
                                                    id="loaderforjs"
                                                    src="{{ asset('front-assets/images/loading.gif') }}"
                                                    alt=""></span>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <input type="hidden" class="custom-control-input check"
                                                        id="vendor_id" name="vendor_id"
                                                        value="{{ $cart_details->vendor_id }}">
                                                    @if ($client_preference_detail->same_day_delivery_for_schedule == 0)
                                                        <input type="date" id="pickup_schedule_datetime"
                                                            class="form-control pickup_schedule_datetime"
                                                            placeholder="Inline calendar"
                                                            min="{{ $cart_details->delay_date }}">
                                                    @else
                                                        <input type="date" id="pickup_schedule_datetime"
                                                            class="form-control pickup_schedule_datetime"
                                                            placeholder="Inline calendar"
                                                            value="{{ $cart_details->scheduled_date_time != '' ? $cart_details->scheduled_date_time : $cart_details->delay_date }}"
                                                            min="{{ $cart_details->delay_date }}">
                                                    @endif
                                                    <input type="hidden" id="checkPickUpSlot" value="1">
                                                </div>
                                                <div class="col-md-6 schedule_pickup_slot">
                                                    <select name="schedule_pickup_slot" id="schedule_pickup_slot"
                                                        class="form-control"
                                                        @if ($client_preference_detail->isolate_single_vendor_order == 0) onchange="checkSlotOrders();" @endif>
                                                        <option value="" selected>{{ __('Select Slot') }}
                                                        </option>
                                                        @if ($client_preference_detail->same_day_delivery_for_schedule == 1)
                                                            @foreach ($cart_details->slotsForPickup as $slot)
                                                                <option value="{{ $slot->value }}"
                                                                    {{ $slot->value == $cart_details->scheduled->slot ? 'selected' : '' }}>
                                                                    {{ @$slot->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label for="">{{ __('Schedule Dropoff ') }} </label> <span
                                                class="loaderfordrop"><img class="img-fluid" style="display:none;"
                                                    id="loaderfordrop"
                                                    src="{{ asset('front-assets/images/loading.gif') }}"
                                                    alt=""></span>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <input type="date" id="dropoff_schedule_datetime"
                                                        class="form-control dropoff_schedule_datetime"
                                                        placeholder="Inline calendar"
                                                        value="{{ @$cart_details->dropoff_scheduled_date_time ? @$cart_details->dropoff_scheduled_date_time : @$cart_details->my_dropoff_delay_date }}"
                                                        min="{{ @$cart_details->my_dropoff_delay_date }}">
                                                    <input type="hidden" id="checkDropoffSlot" value="1">
                                                </div>
                                                <div class="col-md-6 schedule_dropoff_slot">
                                                    <select name="schedule_dropoff_slot" id="schedule_dropoff_slot"
                                                        class="form-control">
                                                        <option value="" selected>{{ __('Select Slot') }}
                                                        </option>
                                                        @if ($client_preference_detail->same_day_delivery_for_schedule == 1)
                                                            @foreach ($cart_details->slotsForDropoff as $slot)
                                                                <option value="{{ $slot->value }}"
                                                                    {{ $slot->value == $cart_details->scheduled->slot ? 'selected' : '' }}>
                                                                    {{ @$slot->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="row">

                                    <div class="col-12 alFourSpecificInstructions">
                                        <span class="pb-1"> {{ __('Specific instructions') }}</span>
                                        <input class="form-control" type="text"
                                            placeholder="{{ __('Do you want to add any instructions?') }}"
                                            id="specific_instructions"
                                            value="{{ $cart_details->specific_instructions ?? '' }}"
                                            name="specific_instructions">
                                             {{-- @if($getAdditionalPreference['is_file_cart_instructions'])
                                                <div class="Instructions_file">
                                                        <label>{{ __('Instructions file') }}</label>
                                                        <div class="instructions_image">
                                                            <input data-default-file="" accept="image/*" type="file" data-plugins="dropify" name="instructionsFile[]" class="dropify instructions_image" multiple />
                                                        </div>
                                                        <label class="logo-size text-right w-100">{{ __("image") }} 1000X1000</label>
                                                </div>
                                            @endif --}}
                                    </div>
                                </div>
                                @if (isset($cart_details->gift_card_id) && (isset($cart_details->gift_card) && !empty($cart_details->gift_card)))
                                    <div class="row">
                                        <div class="col-12 alFourSpecificInstructions mt-2">
                                            <span class="pb-1"> {{ __('Gift Card') }}</span>
                                            {{-- http://local.myorder.com/assets/images/discount_icon.svg --}}
                                            <div class="order-user-name">
                                                <p class="mb-0"><img class="blur-up lazyloaded"
                                                        data-src="{{ asset('assets/images/discount_icon.svg') }}"
                                                        src="{{ asset('assets/images/discount_icon.svg') }}">
                                                    {{ $cart_details->gift_card->title }}</p>
                                                <a href="javascript:void(0);"
                                                    data-giftcard_id='{{ $cart_details->gift_card->id }}'
                                                    class="float-right remove_giftCard"> <i class="fa fa-times"
                                                        aria-hidden="true"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            @endif {{-- //isset($cart) && !empty($cart) && $client_preference_detail->business_type == 'laundry' --}}


                        </div>


                        <div class="col-lg-12 mt-3 cart-price">

                            @if ($cart_details->sub_total > 0)
                                <div class="row">
                                    <div class="col-6">{{ __('Total') }}</div>
                                    {{-- <div class="col-6 text-right"><b> {{Session::get('currencySymbol')}}{{decimal_format($cart_details->sub_total - $cart_details->bid_total_discount)}}</b></div> --}}
                                    <div class="col-6 text-right"><b>
                                            @if ($additionalPreference['is_token_currency_enable'])

                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($cart_details->total_gross_amount )) }}@else{{ Session::get('currencySymbol') . decimal_format($cart_details->total_gross_amount   +@$additionalPrice) }}

                                            @endif
                                        </b>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif
                            @if ($price_bifurcation != 1)
                                <!-- <hr class="my-2"> -->
                                {{-- <div class="row">
                                    <div class="col-6">{{ __('Sub Total') }}</div>
                                    <div class="col-6 text-right"><b>
                                            @if ($additionalPreference['is_token_currency_enable'])
                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                            @else
                                                {{ Session::get('currencySymbol') }}
                                            @endif
                                            <span
                                                id="gross_amount">{{ $additionalPreference['is_token_currency_enable'] ? getInToken(decimal_format($cart_details->gross_amount - $cart_details->bid_total_discount - $cart_details->total_service_fee )) : decimal_format($cart_details->gross_amount - $cart_details->bid_total_discount - $cart_details->total_service_fee ) }}
                                        </b></span>
                                        <span id="other_taxes" style="display:none;">{{ $other_taxes }}</span>
                                    </div>
                                </div> --}}
                                <hr class="my-2">
                            @endif


                            @if ($serviceType == 'rental' || $serviceType == 'p2p')
                                <div class="row">
                                    <div class="col-6">{{ __('Security Amount') }}</div>
                                    <div class="col-6 text-right">
                                        <b>{{ Session::get('currencySymbol') . decimal_format($cart_details->security_amount) }}</b>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif
                            @if ($serviceType == 'p2p' && @$cart_details->plateform_fee > 0)
                                <div class="row">
                                    <div class="col-6">{{ __('Platform Fee') }}</div>
                                    <div class="col-6 text-right">
                                        <b>{{ Session::get('currencySymbol') . decimal_format($cart_details->plateform_fee) }}</b>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif

                            @if($product->slot_price != '' && $product->delivery_date != ''&& $product->slot_id != '')
                                <div class="row">

                                    <div class="col-6">{{__('Delivery Slot Fees')}}</div>
                                    <div class="col-6 text-right"><b> {{Session::get('currencySymbol')}}{{decimal_format($cart_details->delivery_slot_amount)}}</b></div>
                            @endif
                            @if ($cart_details->total_service_fee > 0 && $price_bifurcation != 1)
                                <div class="row">
                                    <div class="col-6">{{ __('Service Fee') }}</div>
                                    <div class="col-6 text-right"><b>
                                            @if ($additionalPreference['is_token_currency_enable'])
                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($cart_details->total_service_fee)) }}@else{{ Session::get('currencySymbol') . decimal_format($cart_details->total_service_fee) }}
                                            @endif
                                        </b>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif

                            @if ($total_fixed_fee_amount > 0 && $price_bifurcation != 1)
                                <div class="row">
                                    <div class="col-6">{{ __($fixedFee) }}</div>
                                    <div class="col-6 text-right"><b>
                                            @if ($additionalPreference['is_token_currency_enable'])
                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($total_fixed_fee_amount)) }}@else{{ Session::get('currencySymbol') . decimal_format($total_fixed_fee_amount) }}
                                            @endif
                                        </b></div>
                                    <input type="hidden" name="total_fixed_fee_amount"
                                        data-curr="{{ Session::get('currencySymbol') }}"
                                        value="{{ $total_fixed_fee_amount }}">
                                </div>
                            @endif
                            @php
                                if ($product_container_charges_tax_amount > 0) {
                                    $other_taxes = $other_taxes + $product_container_charges_tax_amount;
                                    $other_taxes_string = $other_taxes_string . ',tax_product_container_charges:' . $product_container_charges_tax_amount;
                                    $incTax = 1;
                                } elseif ($tax_container_charges_percentage > 0) {
                                    $other_taxes = $other_taxes + $tax_container_charges_percentage;
                                    $other_taxes_string = $other_taxes_string . ',tax_vendor_container_charges:' . $tax_container_charges_percentage;
                                    $incTax = 1;
                                }
                            @endphp
                            <input type="hidden" id="other_taxes_string" value="{{ $other_taxes_string }}">
                            @if ($serviceType == 'rental' || $serviceType == 'p2p')

                            @endif



                            @if ($cart_details->total_taxable_amount + $other_taxes > 0)
                                <div class="row">
                                    <div class="col-6">{{ __('Tax') }}</div>
                                    <div class="col-6 text-right"><b>
                                            @if ($additionalPreference['is_token_currency_enable'])
                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                            @else
                                                {{ Session::get('currencySymbol') }}
                                            @endif

                                            <span
                                                id="total_taxable_amount">{{ $additionalPreference['is_token_currency_enable'] ? getInToken(decimal_format($cart_details->total_taxable_amount)) : decimal_format($cart_details->total_taxable_amount) }}</span>
                                        </b>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif
                            @if ($cart_details->total_subscription_discount > 0)

                                <div class="row">
                                    <div class="col-6">{{ __('Subscription Discount') }}</div>
                                    <div class="col-6 text-right"><b> - @if ($additionalPreference['is_token_currency_enable'])
                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                            @else
                                                {{ Session::get('currencySymbol') }}
                                            @endif
                                            <span id="total_subscription_discount">
                                                {{ $additionalPreference['is_token_currency_enable'] ? getInToken(decimal_format($cart_details->total_subscription_discount)) : decimal_format($cart_details->total_subscription_discount) }}<span></b>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif
                            @if (isset($cart_details->gift_card_id) && (isset($cart_details->gift_card) && !empty($cart_details->gift_card)))
                                <div class="row">
                                    <div class="col-6">{{ __('Gift Card Used Amount') }}</div>
                                    <div class="col-6 text-right"><b> - {{ Session::get('currencySymbol') }}<span
                                                id="loyalty_amount">{{ decimal_format($cart_details->giftCardUsedAmount) }}</span></b>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif
                            @if ($cart_details->loyalty_amount > 0 && $price_bifurcation != 1)

                                <div class="row">
                                    <div class="col-6">{{ __('Loyalty Amount') }}</div>
                                    <div class="col-6 text-right"><b> - @if ($additionalPreference['is_token_currency_enable'])
                                                {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                            @else
                                                {{ Session::get('currencySymbol') }}
                                            @endif
                                            <span id="loyalty_amount">
                                                {{ $additionalPreference['is_token_currency_enable'] ? getInToken(decimal_format($cart_details->loyalty_amount)) : decimal_format($cart_details->loyalty_amount) }}</span></b>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif
                            @if ($cart_details->wallet_amount_used > 0)
                                <div class="row">
                                    <div class="col-6">
                                        {{ $additionalPreference['is_token_currency_enable'] ? __('Used Token') : __('Wallet Amount') }}
                                    </div>
                                    <div class="col-6 text-right" id="wallet_amount_used"> - @if ($additionalPreference['is_token_currency_enable'])
                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                            {{ getInToken(decimal_format($cart_details->wallet_amount_used )) }}
                                            @else{{ Session::get('currencySymbol') . decimal_format($cart_details->wallet_amount_used ) }}
                                        @endif
                                    </div>
                                    <div class="col-6 text-right" id="wallet_amount_used_fixed" style="display:none">
                                        {{ $cart_details->wallet_amount_used }}</div>
                                    <div class="col-6 text-right" id="wallet_amount_available" style="display:none">
                                        {{ $cart_details->wallet_amount_available }}</div>
                                    <div class="col-6 text-right" id="token_currency" style="display:none">
                                        {{ $additionalPreference['is_token_currency_enable'] ? $additionalPreference['token_currency'] : 1 }}
                                    </div>
                                </div>
                                <hr class="my-2">
                            @else
                                <div class="col-6 text-right" id="wallet_amount_used" style="display:none">0</div>
                            @endif

                            @if ($cart_details->total_deliver_charges > 0 )
                                <div class="row">
                                    <div class="col-6">{{ __('Total Delivery Fee') }}</div>
                                    <div class="col-6 text-right" >  @if ($additionalPreference['is_token_currency_enable'])
                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                            {{ getInToken(decimal_format($cart_details->total_deliver_charges )) }}
                                            @else{{ Session::get('currencySymbol') . decimal_format($cart_details->total_deliver_charges ) }}
                                        @endif
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif

                            @if ($cart_details->free_delivery_amount > 0 )
                            <div class="row">
                                    <div class="col-6">
                                        {{  __('Free Delivery Discount') }}
                                    </div>
                                    <div class="col-6 text-right" > - @if ($additionalPreference['is_token_currency_enable'])
                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                            {{ getInToken(decimal_format($cart_details->free_delivery_amount )) }}
                                            @else{{ Session::get('currencySymbol') . decimal_format($cart_details->free_delivery_amount ) }}
                                        @endif
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif

                            @if ($client_preference_detail->tip_before_order == 1)
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
                                            @if ($cart_details->total_payable_amount > 0)
                                                {{-- <input type="radio" class="tip_radio" id="control_01"
                                                    name="select"
                                                    value="{{ $additionalPreference['is_token_currency_enable'] ? getInToken($cart_details->tip_5_percent) : $cart_details->tip_5_percent }}"
                                                    @if ($client_preference_detail->auto_implement_5_percent_tip == 1) checked @endif>
                                                <label class="tip_label" for="control_01">
                                                    <h5 class="m-0" id="tip_5">
                                                        @if ($additionalPreference['is_token_currency_enable'])
                                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                                            {{ getInToken(decimal_format($cart_details->tip_5_percent)) }}
                                                        @else
                                                            {{ Session::get('currencySymbol') . decimal_format($cart_details->tip_5_percent) }}
                                                        @endif
                                                    </h5>
                                                    <p class="m-0">5%</p>
                                                </label>

                                                <input type="radio" class="tip_radio" id="control_02"
                                                    name="select"
                                                    value="{{ $additionalPreference['is_token_currency_enable'] ? getInToken($cart_details->tip_10_percent) : $cart_details->tip_10_percent }}">
                                                <label class="tip_label" for="control_02">
                                                    <h5 class="m-0" id="tip_10">
                                                        @if ($additionalPreference['is_token_currency_enable'])
                                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                                            {{ getInToken(decimal_format($cart_details->tip_10_percent)) }}
                                                        @else
                                                            {{ Session::get('currencySymbol') . decimal_format($cart_details->tip_10_percent) }}
                                                        @endif
                                                    </h5>
                                                    <p class="m-0">10%</p>
                                                </label>

                                                <input type="radio" class="tip_radio" id="control_03"
                                                    name="select"
                                                    value="{{ $additionalPreference['is_token_currency_enable'] ? getInToken($cart_details->tip_15_percent) : $cart_details->tip_15_percent }}">
                                                <label class="tip_label" for="control_03">
                                                    <h5 class="m-0" id="tip_15">
                                                        @if ($additionalPreference['is_token_currency_enable'])
                                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                                            {{ getInToken(decimal_format($cart_details->tip_15_percent)) }}
                                                        @else
                                                            {{ Session::get('currencySymbol') . decimal_format($cart_details->tip_15_percent) }}
                                                        @endif
                                                    </h5>
                                                    <p class="m-0">15%</p>
                                                </label> --}}

                                                <input type="radio" class="tip_radio" id="custom_control"
                                                    name="select" value="custom">
                                                <label class="tip_label" for="custom_control">
                                                    <h5 class="m-0">{{ __('Custom') }}<br>{{ __('Amount') }}
                                                    </h5>
                                                </label>
                                            @endif
                                        </div>
                                        <div class="custom_tip my-1 @if ($cart_details->total_payable_amount > 0) d-none @endif">
                                            <input class="input-number form-control" name="custom_tip_amount"
                                                id="custom_tip_amount" placeholder="{{ __('Enter Custom Amount') }}"
                                                type="number" value="" step="0.1">
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-2">

                            @endif
                            @if ($client_preference_detail->gifting == 1)
                                <div class="row">
                                    <div class="col-12">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input"
                                                style="margin-left: 10px;" id="is_gift" name="is_gift"
                                                value="1">

                                            <label class="custom-control-label" for="is_gift"><img
                                                    class="pr-1 align-middle blur-up lazyload"
                                                    data-src="{{ asset('assets/images/gifts_icon.png') }}"
                                                    alt=""> <span class="align-middle pt-1">{{getNomenclatureName('Include Gift', true)}}</span></label>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-2">
                            @endif
                            <div class="row {{ $hidden_token }}">
                                <div class="col-6 d-flex">
                                    <p class="total_amt m-0">{{ __('Amount Payable') }}
                                        @if ($other_taxes)
                                            <small>({{ __('incl. tax') }})</small>
                                        @endif
                                    </p>
                                    @if ($cart_details->conversion_rate > 0 && $cart_details->currency_code == 'MXN')
                                        <div class="ml-2 alInfoIocn position-relative">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="tooltiptext">Equivalent to
                                                {{ (decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent)) * $cart_details->conversion_rate }}
                                                USD</span>
                                        </div>
                                    @endif
                                </div>


                                <div class="col-6 text-right">


                                    @if ($client_preference_detail->auto_implement_5_percent_tip == 1)
                                        @if (decimal_format($cart_details->wallet_amount_used) > 0)
                                            <p class="total_amt m-0" id="cart_total_payable_amount"
                                                data-cart_id="{{ $cart_details->id }}">
                                                @if ($additionalPreference['is_token_currency_enable'])
                                                    {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                                    {{ getInToken(decimal_format(decimal_format($cart_details->total_payable_amount ?? 0) + decimal_format($cart_details->tip_5_percent))) }}
                                                @else
                                                    {{ Session::get('currencySymbol') . decimal_format(decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent)) }}
                                                @endif
                                            </p>
                                        @else
                                            <p class="total_amt m-0" id="cart_total_payable_amount"
                                                data-cart_id="{{ $cart_details->id }}">
                                                @if ($additionalPreference['is_token_currency_enable'])
                                                    {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}
                                                    {{ getInToken(decimal_format(decimal_format($cart_details->total_payable_amount ?? 0) + decimal_format($cart_details->tip_5_percent) + decimal_format($other_taxes))) }}
                                                @else
                                                    {{ Session::get('currencySymbol') . decimal_format(decimal_format($cart_details->total_payable_amount) + decimal_format($cart_details->tip_5_percent) ) }}
                                                @endif
                                            </p>
                                        @endif
                                        <input type="hidden" name="cart_tip_amount" id="cart_tip_amount"
                                            value="{{ decimal_format($cart_details->tip_5_percent) }}">
                                        <input type="hidden" name="cart_total_payable_amount "
                                            value="{{ decimal_format($cart_details->product_total_amount  ?? 0) + decimal_format($cart_details->tip_5_percent)  }}">
                                    @else

                                        <p class="total_amt m-0" id="cart_total_payable_amount"
                                            data-cart_id="{{ $cart_details->id }}">

                                            {{ Session::get('currencySymbol') }}

                                            {{ decimal_format($cart_details->total_payable_amount  ?? 0 - $cart_details->bid_total_discount) }}

                                        </p>

                                        <input type="hidden" name="cart_tip_amount" id="cart_tip_amount"
                                            value="0">
                                        <input type="hidden" name="cart_total_payable_amount"
                                            value="{{ $additionalPreference['is_token_currency_enable']
                                                ? ''
                                                : decimal_format($cart_details->total_payable_amount  ?? 0)  }}">
                                    @endif
                                    <div>
                                        <input type="hidden" name="cart_payable_amount_original"
                                            id="cart_payable_amount_original"
                                            data-curr="{{ Session::get('currencySymbol') }}"
                                            value="{{ decimal_format($cart_details->total_payable_amount  ?? 0 - $cart_details->bid_total_discount)  }}">
                                    </div>


                                </div>
                            </div>
                            {{-- mohit sir branch code added by sohail  --}}
                            @if ($serviceType == 'takeaway' &&
                                !empty($getAdditionalPreference['advance_booking_amount']) &&
                                !empty($getAdditionalPreference['advance_booking_amount_percentage']) &&
                                $getAdditionalPreference['advance_booking_amount_percentage'] > 0 &&
                                $getAdditionalPreference['advance_booking_amount_percentage'] < 101)
                                <hr class="my-2">
                                <div class="row">
                                    <div class="col-6">
                                        <p class="total_amt m-0"> {{ __('Deposit Required') }}</p>
                                    </div>
                                    <div class="col-6 text-right">
                                        @if ($client_preference_detail->auto_implement_5_percent_tip == 1)
                                            @if (decimal_format($cart_details->wallet_amount_used) > 0)
                                                <p class="total_amt m-0 11" id="advance_cart_total_payable_amount"
                                                    data-cart_id="{{ $cart_details->id }}">
                                                    {{ Session::get('currencySymbol') }}{{ decimal_format(((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}
                                                </p>
                                            @else
                                                <p class="total_amt m-0 22" id="advance_cart_total_payable_amount"
                                                    data-cart_id="{{ $cart_details->id }}">
                                                    {{ Session::get('currencySymbol') }}{{ decimal_format(((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent) + decimal_format($other_taxes)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}
                                                </p>
                                            @endif
                                            <input type="hidden" name="cart_tip_amount" id="cart_tip_amount"
                                                value="{{ decimal_format($cart_details->tip_5_percent) }}">
                                            <input type="hidden" name="cart_total_payable_amount"
                                                value="{{ decimal_format(((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent) + decimal_format($other_taxes)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}">
                                        @else
                                            @if (decimal_format($cart_details->wallet_amount_used) > 0)
                                                <p class="total_amt m-0 33" id="advance_cart_total_payable_amount"
                                                    data-cart_id="{{ $cart_details->id }}">
                                                    {{ Session::get('currencySymbol') }}{{ decimal_format(((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($other_taxes)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}
                                                </p>
                                            @else
                                                <p class="total_amt m-0 44" id="advance_cart_total_payable_amount"
                                                    data-cart_id="{{ $cart_details->id }}">
                                                    {{ Session::get('currencySymbol') }}{{ decimal_format(((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($other_taxes)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}
                                                </p>
                                            @endif
                                            <input type="hidden" name="cart_tip_amount" id="cart_tip_amount"
                                                value="0">
                                            <input type="hidden" name="cart_total_payable_amount "
                                                value="{{ decimal_format(((decimal_format($cart_details->total_payable_amount ?? 0) + decimal_format($other_taxes)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}">
                                        @endif
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row">
                                    <div class="col-6">
                                        <p class="total_amt m-0"> {{ __('Outstanding Amount') }}</p>
                                    </div>
                                    <div class="col-6 text-right">
                                        @if ($client_preference_detail->auto_implement_5_percent_tip == 1)
                                            @if (decimal_format($cart_details->wallet_amount_used) > 0)
                                                <p class="total_amt m-0 11" id="pending_cart_total_payable_amount"
                                                    data-cart_id="{{ $cart_details->id }}">
                                                    {{ Session::get('currencySymbol') }}{{ decimal_format(decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent) - ((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}
                                                </p>
                                            @else
                                                <p class="total_amt m-0 22" id="pending_cart_total_payable_amount "
                                                    data-cart_id="{{ $cart_details->id }}">
                                                    {{ Session::get('currencySymbol') }}{{ decimal_format(decimal_format($cart_details->total_payable_amount ?? 0) + decimal_format($cart_details->tip_5_percent) + decimal_format($other_taxes) - ((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent) + decimal_format($other_taxes)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}
                                                </p>
                                            @endif
                                        @else
                                            @if (decimal_format($cart_details->wallet_amount_used) > 0)
                                                <p class="total_amt m-0 33" id="pending_cart_total_payable_amount"
                                                    data-cart_id="{{ $cart_details->id }}">
                                                    {{ Session::get('currencySymbol') }}{{ decimal_format(decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($other_taxes) - ((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($other_taxes)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}
                                                </p>
                                            @else
                                                <p class="total_amt m-0 44" id="pending_cart_total_payable_amount"
                                                    data-cart_id="{{ $cart_details->id }}">
                                                    {{ Session::get('currencySymbol') }}{{ decimal_format(decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($other_taxes) - ((decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($other_taxes)) * $getAdditionalPreference['advance_booking_amount_percentage']) / 100) }}
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @else
                                @if ($client_preference_detail->auto_implement_5_percent_tip == 1)
                                    <input type="hidden" name="cart_tip_amount" id="cart_tip_amount"
                                        value="{{ decimal_format($cart_details->tip_5_percent) }}">
                                    <input type="hidden" name="cart_total_payable_amount"
                                        value="{{ decimal_format($cart_details->total_payable_amount  ?? 0) + decimal_format($cart_details->tip_5_percent) }}">
                                @else
                                    <input type="hidden" name="cart_tip_amount" id="cart_tip_amount"
                                        value="0">
                                    <input type="hidden" name="cart_total_payable_amount"
                                        value="{{ decimal_format($cart_details->total_payable_amount  ?? 0) }}">
                                @endif
                            @endif
                            {{-- till date --}}
                            <hr class="my-2">

                        </div>

                        {{-- Schedual code Start at down --}}
                        @if (!(
                            $product->vendor->order_min_amount > 0 &&
                            $product->product_total_amount + $product->vendor->fixed_fee_amount < $product->vendor->order_min_amount
                        ))
                        @if($cart_details->is_recurring_booking != 1 && $serviceType != 'rental')
                              @include('frontend.cart.scheduleSlot')
                        @endif

                            <div class="col-sm-6 col-lg-12 mt-2 text-sm-right cart-checkout_btn">
                                @if (isset($ageVerify->status) && $ageVerify->status == 1)
                                    {{-- <button id="verify_your_age" class="btn btn-solid " type="button" >{{__('Verify Your Age')}}</button> --}}
                                @endif
                                @if (!empty($cart_details->editing_order) &&
                                    !empty($cart_details->editing_order->scheduled_date_time) &&
                                    !empty($edit_order_schedule_datetime))
                                    <input type="hidden" id="edit_order_schedule_datetime"
                                        value="{{ $edit_order_schedule_datetime }}">
                                    <input type="hidden" id="edit_order_schedule_slot"
                                        value="{{ $schedule_slots_edit }}">
                                @endif
                                @if (@$additionalPreference['cart_cms_page_status'] == 1)
                                    <div class="text-sm-left mb-2">
                                            <input type="checkbox" name="refund_term_check" id="refund_term_check" value="" disabled> <a href="javascript:void(0);" class="refund_term_policy">I accept the refund Terms & policy</a>
                                    </div>
                                @endif
                                @if ($serviceType == 'rental' || @$additionalPreference['cart_cms_page_status'] == 1)
                                    <div class="text-sm-left mb-2">
                                        <input type="checkbox" name="agree_term_check" id="agree_term_check" value="" disabled> <a href="javascript:void(0);" class="agree_term_btn">I accept the Terms & Conditions</a>
                                    </div>
                                @endif
                                @php
                                    $disablePlaceBtn = '';
                                    if(count($cart_details->user_allAddresses) == 0 || $serviceType == 'rental' || $serviceType == 'p2p'){
                                        $disablePlaceBtn = 'disabled';
                                    }
                                @endphp
                                @if ($additionalPreference['is_token_currency_enable'] == 1)

                                    @if ($cart_details->wallet_amount_used > 0)
                                        @if ($cart_error_message == '')
                                          <button id="order_placed_btn" class="btn btn-solid d-none" type="button"
                                                {{ $disablePlaceBtn}}>{{ __('Place Order') }}</button>
                                        @else
                                            <div class="alert p-0" role="alert">
                                                <div class="alert-danger p-1">{{ $cart_error_message }}</div>
                                            </div>
                                        @endif
                                    @else
                                        <a class="btn shoping btn-danger"
                                            href="{{ route('user.wallet') }}">{{ __('Need Topup Wallet') }}</a>
                                    @endif
                                @else
                                    @if ($cart_error_message == '')
                                    <button id="order_placed_btn" class="btn btn-solid d-none" type="button"
                                    {{ $disablePlaceBtn}}>{{ __('Place Order') }}</button>
                                    @else
                                        <div class="alert p-0" role="alert">
                                            <div class="alert-danger p-1">{{ $cart_error_message }}</div>
                                        </div>
                                    @endif
                                @endif

                            </div>


                            </div>

                        </div>

                    </div>
                </div>
            @endif
        {{-- -- End Right Section ---- --}}
        </div>
    </div>



    <div class="container">

        @if (count($cart_details->upSell_products) > 0)
            <h3 class="mb-2 mt-4">{{ __('Frequently bought together') }}</h3>
            <div class="row">
                <div class="col-12 p-0">
                    <div class="product-4 product-m upsell-sell">
                        @foreach ($cart_details->upSell_products as $product)
                            <a class="common-product-box scale-effect text-center"
                                href="{{ $product->vendor->slug . '/product/' . $product->url_slug }}">
                                <div class="img-outer-box position-relative">
                                    <img class="blur-up lazyload" data-src="{{ $product->image_url }}"
                                        alt="">
                                    <div class="pref-timing">
                                        <!--<span>5-10 min</span>-->
                                    </div>
                                    <i class="fa fa-heart-o fav-heart position-absolute" aria-hidden="true"></i>
                                </div>
                                <div class="media-body align-self-center">
                                    <div class="inner_spacing px-0">
                                        <div class="product-description">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="card_title ellips">{{ $product->translation_title }}</h6>
                                                <!--<span class="rating-number">2.0</span>-->
                                            </div>
                                            <p>{{ $product->vendor_name }}</p>
                                            <p class="border-bottom pb-1">In {{ $product->category_name }}</p>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <b>
                                                    @if ($product->inquiry_only == 0)
                                                        {{ Session::get('currencySymbol') }}{{ decimal_format($product->variant_price) }}
                                                    @endif
                                                </b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if (count($cart_details->crossSell_products) > 0)
            <h3 class="mb-2 mt-3">{{ __('You might be interested in') }}</h3>
            <div class="row">
                <div class="col-12 p-0">
                    <div class="product-4 product-m cross-sell">
                        @foreach ($cart_details->crossSell_products as $product)
                            <a class="common-product-box scale-effect text-center"
                                href="{{ $product->vendor->slug . '/product/' . $product->url_slug }}">
                                <div class="img-outer-box position-relative">
                                    <img class="blur-up lazyload" data-src="{{ $product->image_url }}"
                                        alt="">
                                    <div class="pref-timing">
                                    </div>
                                    <i class="fa fa-heart-o fav-heart position-absolute" aria-hidden="true"></i>
                                </div>
                                <div class="media-body align-self-center">
                                    <div class="inner_spacing px-0">
                                        <div class="product-description">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="card_title ellips">{{ $product->translation_title }}</h6>
                                            </div>
                                            <p>{{ $product->vendor_name }}</p>
                                            <p class="border-bottom pb-1">In {{ $product->category_name }}</p>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <b>
                                                    @if ($product->inquiry_only == 0)
                                                        {{ Session::get('currencySymbol') }}{{ decimal_format($product->variant_price) }}
                                                    @endif
                                                </b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

@endif
@php
    $termsPage = $cmsPages->filter(function($page) {
            return $page->slug == 'terms-conditions';
    })->first();
    $refundPolicy = $cmsPages->filter(function($page) {
            return $page->slug == 'refund-policy';
    })->first();
@endphp

@if ($serviceType == "rental" || $serviceType == 'p2p' || @$additionalPreference['cart_cms_page_status'] == 1)
    @include('frontend.cart.rentalConsentFormModal', ['page' => $termsPage])
@endif

@if (@$additionalPreference['cart_cms_page_status'] == 1)
    @include('frontend.cart.refundPolicyFormModal', ['page' => $refundPolicy])
@endif

<script>
    $(document).ready(function() {
         $('.dropify').dropify({
            messages: {
                'default': "Drag and drop a file here or click",
                'replace': "Drag and drop or click to replace",
                'remove':  "Remove",
                'error':   "Ooops, something wrong happended."
            }
        });

        $('.dropify-clear').click(function(e){
            e.preventDefault();
            $(".instructions_image").empty();

        });
        @if(!empty($r_schedule_datetime))
            var schedule_datetime = "{{ $r_schedule_datetime }}";
            $("#schedule_datetime").val(schedule_datetime);
            $("#schedule_datetime").prop('disabled',true);
            $(".schedule_btn").find('.close-window').hide();
        @else
            $("#schedule_datetime").prop('disabled',false);
            $(".schedule_btn").find('.close-window').show();
        @endif
        $(".upsell-sell").slick({
            arrows: true,
            dots: false,
            infinite: true,
            slidesToShow: 5,
            slidesToScroll: 1,
            responsive: [{
                breakpoint: 1200,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 2
                }
            }]
        });

        $(".cross-sell").slick({
            arrows: true,
            dots: false,
            infinite: true,
            slidesToShow: 5,
            slidesToScroll: 1,
            responsive: [{
                breakpoint: 1200,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 2
                }
            }]
        });
        var serviceType = "{{$serviceType}}";
        if(serviceType == "rental"){
            $("#order_placed_btn").attr('disabled', true);
        }
    });

    $(document).on('click', '.agree_term_btn', function(){
        $('#consent_form_rental').modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    $(document).on('click', '.refund_term_policy', function(){
        $('#refund_form_rental').modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    $(document).on('click', '#refund_agree_btn', function(){
        $('#refund_term_check').prop('checked', true);
        $('#refund_term_check').attr('disabled', false);
        $("#order_placed_btn").attr('disabled', false);
        $('#refund_form_rental').modal('hide');
    });

    $(document).on('click', '#agree_btn', function(){
        $('#agree_term_check').prop('checked', true);
        $('#agree_term_check').attr('disabled', false);
        $("#order_placed_btn").attr('disabled', false);
        $('#consent_form_rental').modal('hide');
    });


</script>

@section('script-bottom-js')
    <script defer type="text/javascript" src="{{ asset('js/giftCard/cartGiftCard.js') }}"></script>

@endsection
