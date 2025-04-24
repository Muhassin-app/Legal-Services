@php
$checkSlot = findSlot('', $vendor->id, '');
@endphp
@extends('layouts.store', ['title' => $vendor->name])
@section('css')
<style type="text/css">
    .main-menu .brand-logo{display:inline-block;padding-top:20px;padding-bottom:20px}.productVariants .firstChild{min-width:150px;text-align:left!important;border-radius:0!important;margin-right:10px;cursor:default;border:none!important}.product-right .color-variant li,.productVariants .otherChild{height:35px;width:35px;border-radius:50%;margin-right:10px;cursor:pointer;border:1px solid #f7f7f7;text-align:center}.productVariants .otherSize{height:auto!important;width:auto!important;border:none!important;border-radius:0}.product-right .size-box ul li.active{background-color:inherit}.product-box .product-detail h4,.product-box .product-info h4{font-size:16px}select.changeVariant{color:#343a40;border:1px solid #bbb;border-radius:5px;font-size:14px}.counter-container{border:1px solid var(--theme-deafult);border-radius:5px;padding:2px}.switch{opacity:0;position:absolute;z-index:1;width:18px;height:18px;cursor:pointer}.switch+.lable{position:relative;display:inline-block;margin:0;line-height:20px;min-height:18px;min-width:18px;font-weight:400;cursor:pointer}.switch+.lable::before{cursor:pointer;font-family:fontAwesome;font-weight:400;font-size:12px;color:#32a3ce;content:"\a0";background-color:#fafafa;border:1px solid #c8c8c8;box-shadow:0 1px 2px rgba(0,0,0,.05);border-radius:0;display:inline-block;text-align:center;height:16px;line-height:14px;min-width:16px;margin-right:1px;position:relative;top:-1px}.switch:checked+.lable::before{display:inline-block;content:'\f00c';background-color:#f5f8fc;border-color:#adb8c0;box-shadow:0 1px 2px rgba(0,0,0,.05),inset 0 -15px 10px -12px rgba(0,0,0,.05),inset 15px 10px -12px rgba(255,255,255,.1)}.switch+.lable{margin:0 4px;min-height:24px}.switch+.lable::before{font-weight:400;font-size:11px;line-height:17px;height:20px;overflow:hidden;border-radius:12px;background-color:#f5f5f5;-webkit-box-shadow:inset 0 1px 1px 0 rgba(0,0,0,.15);box-shadow:inset 0 1px 1px 0 rgba(0,0,0,.15);border:1px solid #ccc;text-align:left;float:left;padding:0;width:52px;text-indent:-21px;margin-right:0;-webkit-transition:text-indent .3s ease;-o-transition:text-indent .3s ease;transition:text-indent .3s ease;top:auto}.switch.switch-bootstrap+.lable::before{font-family:FontAwesome;content:"\f00d";box-shadow:none;border-width:0;font-size:16px;background-color:#a9a9a9;color:#f2f2f2;width:52px;height:22px;line-height:21px;text-indent:32px;-webkit-transition:background .1s ease;-o-transition:background .1s ease;transition:background .1s ease}.switch.switch-bootstrap+.lable::after{content:'';position:absolute;top:2px;left:3px;border-radius:12px;box-shadow:0 -1px 0 rgba(0,0,0,.25);width:18px;height:18px;text-align:center;background-color:#f2f2f2;border:4px solid #f2f2f2;-webkit-transition:left .2s ease;-o-transition:left .2s ease;transition:left .2s ease}.switch.switch-bootstrap:checked+.lable::before{content:"\f00c";text-indent:6px;color:#fff;border-color:#b7d3e5}.switch-primary>.switch.switch-bootstrap:checked+.lable::before{background-color:#337ab7}.switch-success>.switch.switch-bootstrap:checked+.lable::before{background-color:#5cb85c}.switch-danger>.switch.switch-bootstrap:checked+.lable::before{background-color:#d9534f}.switch-info>.switch.switch-bootstrap:checked+.lable::before{background-color:#5bc0de}.switch-warning>.switch.switch-bootstrap:checked+.lable::before{background-color:#f0ad4e}.switch.switch-bootstrap:checked+.lable::after{left:32px;background-color:#fff;border:4px solid #fff;text-shadow:0 -1px 0 rgba(0,200,0,.25)}.switch-square{opacity:0;position:absolute;z-index:1;width:18px;height:18px;cursor:pointer}.switch-square+.lable{position:relative;display:inline-block;margin:0;line-height:20px;min-height:18px;min-width:18px;font-weight:400;cursor:pointer}.switch-square+.lable::before{cursor:pointer;font-family:fontAwesome;font-weight:400;font-size:12px;color:#32a3ce;content:"\a0";background-color:#fafafa;border:1px solid #c8c8c8;box-shadow:0 1px 2px rgba(0,0,0,.05);border-radius:0;display:inline-block;text-align:center;height:16px;line-height:14px;min-width:16px;margin-right:1px;position:relative;top:-1px}.switch-square:checked+.lable::before{display:inline-block;background-color:#f5f8fc;border-color:#adb8c0;box-shadow:0 1px 2px rgba(0,0,0,.05),inset 0 -15px 10px -12px rgba(0,0,0,.05),inset 15px 10px -12px rgba(255,255,255,.1)}.switch-square+.lable{margin:0 4px;min-height:24px}.switch.switch-bootstrap+.lable::before,.switch.switch-bootstrap:checked+.lable::before{content:"";width:40px;height:18px;line-height:21px}.switch.switch-bootstrap+.lable::after{width:14px;height:14px}.switch+.lable{line-height:14px}.switch.switch-bootstrap:checked+.lable::after{left:23px}.switch-square+.lable::before{font-weight:400;font-size:11px;line-height:17px;height:20px;overflow:hidden;border-radius:2px;background-color:#f5f5f5;-webkit-box-shadow:inset 0 1px 1px 0 rgba(0,0,0,.15);box-shadow:inset 0 1px 1px 0 rgba(0,0,0,.15);border:1px solid #ccc;text-align:left;float:left;padding:0;width:52px;text-indent:-21px;margin-right:0;-webkit-transition:text-indent .3s ease;-o-transition:text-indent .3s ease;transition:text-indent .3s ease;top:auto}.switch-square.switch-bootstrap+.lable::before{font-family:FontAwesome;box-shadow:none;border-width:0;font-size:16px;background-color:#a9a9a9;color:#f2f2f2;width:52px;height:22px;line-height:21px;text-indent:32px;-webkit-transition:background .1s ease;-o-transition:background .1s ease;transition:background .1s ease}.switch-square.switch-bootstrap+.lable::after{content:'';position:absolute;top:2px;left:3px;border-radius:12px;box-shadow:0 -1px 0 rgba(0,0,0,.25);width:18px;height:18px;text-align:center;background-color:#f2f2f2;border:4px solid #f2f2f2;-webkit-transition:left .2s ease;-o-transition:left .2s ease;transition:left .2s ease}.switch-square.switch-bootstrap:checked+.lable::before{text-indent:6px;color:#fff;border-color:#b7d3e5}.switch-primary>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#337ab7}.switch-success>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#5cb85c}.switch-danger>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#d9534f}.switch-info>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#5bc0de}.switch-warning>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#f0ad4e}.switch-square.switch-bootstrap:checked+.lable::after{left:32px;background-color:#fff;border:4px solid #fff;text-shadow:0 -1px 0 rgba(0,200,0,.25)}.switch-square.switch-bootstrap+.lable::after{border-radius:2px}
    .profile_address ul.vendor-info li.d-block.vendor-location a{position: absolute;right: 0px;top:0px;padding:0px 6px;
    border-radius: 4px;border: 1px dotted#938a8a;background-color: #f8f1f8;}.social-icon-list {
    width: 100%;max-width: 90%;}.social-icon-list .modal-body {text-align: center;}.social-icon-list .modal-body .text-center a img {width: 40px;}
.social-icon-list .modal-body .text-center {display: inline-block;margin: 0px 6px;}.profile_address ul.vendor-info li.d-block.vendor-location a span {font-size: 13px;}.profile_address ul.vendor-info li.d-block.vendor-location a img {width: 12px;}
/* .vendor-description .vendor-details-left .vender-icon .vendor-stories a img {width: 110px;height: 110px;} */
.vendor-description .profile_address h3 {font-size: 24px;text-transform: capitalize;margin: 10px 0px 6px 0px;}
.vendor-description .profile_address h4 {font-size: 16px; margin: 0px;color: #6c757d;}
/* .vendor-description .profile_address ul.vendor-info li {padding: 2px 8px 3px 0px;} */
.al_body_template_two .vendor-description .vendor-reviwes{padding: 0;position: absolute;right: auto;left:0px;top:5px;}
.al_body_template_six.homeHeader .product-bottom-bar{padding: 20px 10px;}
.vendor-description .vendor-info .d-block.vendor-location{padding-left:0px;}
.al_body_template_six.homeHeader .vendor-stories{background: transparent;}
.vendor-description .vendor-details-left .vendor-location a {position: inherit !important;}
.al_body_template_six .vendor-description .vendor-info .vendor-location{margin-bottom:10px;}
.line_diff_between_products{border-top: 1px dotted rgb(61, 60, 60)}
span.alPriceValue, span.alPriceValue i {
    display: inline-flex;
    align-items: baseline;
}
.vendor-products-wrapper .price_head h5{
    max-width:70%;
}
</style>
@endsection
@section('css-links')
<link rel="stylesheet" type="text/css" href="{{ asset('front-assets/css/price-range.css') }}">
@endsection
@php
$add_to_cart =  route('addToCart') ;
$is_service_product_price_from_dispatch_forOnDemand = 0;
$additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch','is_service_price_selection','is_enable_allergic_items']);
$getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
$category_type_idForNotShowshPlusMinus = ['12'];
if($getOnDemandPricingRule['is_price_from_freelancer'] ==1 ){
    $is_service_product_price_from_dispatch_forOnDemand =1;
    array_push($category_type_idForNotShowshPlusMinus,8);
}

@endphp
@section('content')
    <!-- section start -->
    <section class="section-b-space ratio_asos alProductCategories">
        <div class="collection-wrapper">
            <div class="container p-0">
                <div class="row">
                        <div class="col-12">
                            @include('frontend.vendor-category-topbar-banner')
                        </div>
                        @include('frontend.vendor-details-in-banner')

                </div>

                <div class="position-relative container">
                    <div class="categories-product-list mt-sm-4">

                        <a id="side_menu_toggle" class="d-md-none d-flex" href="javascript:void(0)">
                            <div class="manu-bars">
                                <span class="bar-line"></span>
                                <span class="bar-line"></span>
                                <span class="bar-line"></span>
                            </div>
                            <span>{{ __('Menu') }}</span>
                        </a>

                        <div class="row">
                            <div class="col-12">
                                <div class="col-sm-6 offset-sm-3">
                                    <div class="row  d-flex align-items-start justify-content-center m-0">
                                        <div class="col-7 vendor-search-bar mb-sm-0 mb-2 p-0">
                                            <div class="radius-bar w-100">
                                                <div class="search_form d-flex align-items-center">
                                                    <button class="btn"><i class="fa fa-search" aria-hidden="true"></i></button>
                                                    <input class="form-control border-0 typeahead" type="search"
                                                        placeholder="{{ __('Search') }}" id="vendor_search_box">
                                                </div>
                                                <div class="list-box style-4" style="display:none;" id="search_box_main_div">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-5 text-right pl-0 pr-0">
                                            <!-- <span class="d-lg-inline-block d-none"> {{ __('Sort By') }} :</span> -->
                                            <select name="order_type" id='order_type' class="product_tag_filter p-1">
                                                <option value="featured">{{ __('Featured') }}</option>
                                                <option value="a_to_z">{{ __('A to Z') }}</option>
                                                <option value="z_to_a">{{ __('Z to A') }}</option>
                                                <option value="low_to_high">{{ __('Cost : Low to High') }}</option>
                                                <option value="high_to_low">{{ __('Cost : High to Low') }}</option>
                                                <option value="rating">{{ __('Avg. Customer Review') }}</option>
                                                <option value="newly_added">{{ __('Newest Arrivals') }}</option>
                                                @if ($additionalPreference['is_enable_allergic_items'] == 1)
                                                    <option value="cal_asc">{{ __('Calories : Low to High') }}</option>
                                                    <option value="cal_desc">{{ __('Calories : High to Low') }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row vendor-products-wrapper">
                                    <div class="col-sm-4 col-lg-3 border-right al_white_bg_round">
                                        <nav class="scrollspy-menu ">
                                            <ul>
                                                @forelse($listData as $key => $data)
                                                    <li><a data-slug="{{ $data->category->slug??'#' }}" style="cursor: pointer;">{{ $data->category->translation[0]->name??'' }}({{$data->products->total() }})</a>
                                                    </li>
                                                @empty
                                                @endforelse
                                            </ul>
                                        </nav>
                                    </div>
                                    <div class="col-md-8 col-lg-6 alScrollspyProduct">

                                        <div class="col-12 d-sm-flex justify-content-start mb-2 p-0">
                                            @if (isset($tags) && !empty($tags))
                                                @foreach ($tags as $key => $tag)
                                                    <label class="label-switch switch-primary product_tag_filter mr-2 mb-0">
                                                        <input type="checkbox"
                                                            class="switch switch-bootstrap product_tag_filter status"
                                                            name="tag_id" id="product_tag_filter_{{ $key }}"
                                                            data-tag_id="{{ $tag->id }}" value="
                                                            {{ $tag->id }}">
                                                        <span class="lable">
                                                            @if (isset($tag->icon) && !empty($tag->icon))
                                                                <img class="ml-1"
                                                                    src="{{ $tag->icon['proxy_url'] . '100/100' . $tag->icon['image_path'] }}"
                                                                    alt="">
                                                            @endif <span
                                                                class="ml-1">{{ $tag->primary ? $tag->primary->name : '' }}</span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            @endif
                                        </div>
                                        @forelse($listData as $key => $data)
                                            <section class="scrolling_section " id="{{ $data->category->slug }}">
                                                @if (!empty($data->products))
                                                    <h2 class="category-head mt-0 mb-3">
                                                        {{ $data->category->translation_one->name??'' }}
                                                        ({{ $data->products->total() }})
                                                        @if($data->products->total() > 12)
                                                        :
                                                            <span class="font-12">
                                                                <a target="_blank" href="{{route('products',[$data->category_id,isset($data->vendor)?$data->vendor->id:0])}}">view all</a>
                                                            </span>
                                                        @endif
                                                    </h2>
                                                    @forelse($data->products as $prod)
                                                    @php
                                                        $product_url =  route('productDetail', [$prod->vendor->slug, $prod->url_slug]);
                                                    @endphp
                                                        <div class="row cart-box-outer al_white_bg_round product_row classes_wrapper no-gutters mb-2 pb-2 border-bottom"
                                                            data-p_sku="{{ $prod->sku }}"
                                                            data-slug="{{ $prod->url_slug }}">
                                                            <div class=" col-sm-2 col-4 mb-2">
                                                                <a target="_blank"
                                                                    href="{{ $product_url }}">
                                                                    <div class="class_img product_image">
                                                                        <img src="{{ $prod->product_image }}"
                                                                            alt="{{ $prod->translation_title }}">
                                                                    </div>
                                                                </a>

                                                            </div>
                                                            <div class="col-sm-10 col-8  pl-md-3 pl-2">
                                                                <div class="row price_head">
                                                                    <div class="col-sm-12">
                                                                        <div
                                                                            class="d-flex align-items-start justify-content-between">
                                                                            <h5 class="mt-0">
                                                                                {{ $prod->translation_title }}

                                                                            </h5>
                                                                            <div class="product_variant_quantity_wrapper">

                                                                                    @php
                                                                                        $data = $prod;
                                                                                        $productVariantInCart = 0;
                                                                                        $productVariantIdInCart = 0;
                                                                                        $productVariantInCartWithDifferentAddons = 0;
                                                                                        $cartProductId = 0;
                                                                                        $cart_id = 0;
                                                                                        $vendor_id = 0;
                                                                                        $product_id = $data->id;
                                                                                        $variant_id = ((isset($data->variant[0]))?$data->variant[0]->id : 0);
                                                                                        $variant_price = 0;
                                                                                        $variant_quantity = $prod->variant_quantity;
                                                                                        $isAddonExist = 0;
                                                                                        $minimum_order_count = $data->minimum_order_count == 0 ? 1 : $data->minimum_order_count;
                                                                                        $batch_count = $data->batch_count;
                                                                                        if (count($data->addOn) > 0) {
                                                                                            $isAddonExist = 1;
                                                                                        }
                                                                                    //pr($data->toArray());
                                                                                    @endphp
                                                                                @if($prod->category_type_id !=10)
                                                                                    @foreach ($data->variant as $var)
                                                                                        @if (isset($var->checkIfInCart) && count($var->checkIfInCart) > 0)
                                                                                            @php
                                                                                                //dd($var->_markup_price);
                                                                                                $productVariantInCart = 1;
                                                                                                $productVariantIdInCart = $var->checkIfInCart['0']['variant_id'];
                                                                                                $cartProductId = $var->checkIfInCart['0']['id'];
                                                                                                $cart_id = $var->checkIfInCart['0']['cart_id'];
                                                                                                // $variant_quantity = $var->checkIfInCart['0']['quantity'];
                                                                                                $variant_quantity = 0;
                                                                                                $vendor_id = $data->vendor_id;
                                                                                                $product_id = $data->id;
                                                                                                $batch_count = $data->batch_count;
                                                                                                $variant_price = decimal_format($var->price * $data->variant_multiplier);
                                                                                                if (count($var->checkIfInCart) > 1) {
                                                                                                    $productVariantInCartWithDifferentAddons = 1;
                                                                                                }
                                                                                                foreach ($var->checkIfInCart as $cartVar) {
                                                                                                    $variant_quantity = $variant_quantity + $cartVar['quantity'];
                                                                                                }
                                                                                            @endphp
                                                                                            @break;
                                                                                        @endif
                                                                                    @endforeach

                                                                                    @if ( ($is_service_product_price_from_dispatch_forOnDemand ==1) || ($vendor->is_vendor_closed == 0 || ($vendor->closed_store_order_scheduled != 0 && $checkSlot != 0) )  )
                                                                                        @php
                                                                                            $is_customizable = false;
                                                                                            if ($isAddonExist > 0 && ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1)) {
                                                                                                $is_customizable = true;
                                                                                            }
                                                                                        @endphp

                                                                                        @if ($productVariantInCart > 0)
                                                                                            @if( $is_service_product_price_from_dispatch_forOnDemand ==1)
                                                                                                <a class="btn btn-solid btn btn-solid view_on_demand_price"  style="display:none;" id="add_button_href{{$cartProductId}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                                            @else

                                                                                                {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                                                                                                <a class="add-cart-btn add_vendor_product as"
                                                                                                    style="display:none;"
                                                                                                    id="add_button_href{{ $cartProductId }}"
                                                                                                    data-variant_id="{{ $productVariantIdInCart }}"
                                                                                                    data-add_to_cart_url="{{ route('addToCart') }}"
                                                                                                    data-vendor_id="{{ $vendor_id }}"
                                                                                                    data-product_id="{{ $product_id }}"
                                                                                                    data-addon="{{ $isAddonExist }}"
                                                                                                    data-minimum_order_count="{{ $minimum_order_count }}"
                                                                                                    data-batch_count="{{ $batch_count }}"
                                                                                                    href="javascript:void(0)">{{ __('Add') }}
                                                                                                    @if ($minimum_order_count > 0)
                                                                                                        ({{ $minimum_order_count }})
                                                                                                    @endif
                                                                                                </a>
                                                                                            @endif

                                                                                            @if(isset($data->category_type_id) && (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)))
                                                                                                <div class="number"
                                                                                                    id="show_plus_minus{{ $cartProductId }}">
                                                                                                    <span
                                                                                                        class="minus qty-minus-product {{ $productVariantInCartWithDifferentAddons ? 'remove-customize' : '' }}"
                                                                                                        data-variant_id="{{ $productVariantIdInCart }}"
                                                                                                        data-parent_div_id="show_plus_minus{{ $cartProductId }}"
                                                                                                        data-id="{{ $cartProductId }}"
                                                                                                        data-base_price="{{ $variant_price }}"
                                                                                                        data-vendor_id="{{ $vendor_id }}"
                                                                                                        data-product_id="{{ $product_id }}"
                                                                                                        data-cart="{{ $cart_id }}"
                                                                                                        data-addon="{{ $isAddonExist }}"
                                                                                                        data-minimum_order_count="{{ $minimum_order_count }}"
                                                                                                        data-batch_count="{{ $batch_count }}">
                                                                                                        <i class="fa fa-minus"
                                                                                                            aria-hidden="true"></i>
                                                                                                    </span>
                                                                                                    <input
                                                                                                        style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                                                                        placeholder="1" type="text"
                                                                                                        value="{{ $variant_quantity }}"
                                                                                                        class="input-number"
                                                                                                        id="quantity_ondemand_{{ $cartProductId }}"
                                                                                                        readonly>
                                                                                                    <span
                                                                                                        class="plus qty-plus-product {{ $is_customizable ? 'repeat-customize' : '' }}"
                                                                                                        data-variant_id="{{ $productVariantIdInCart }}"
                                                                                                        data-id="{{ $cartProductId }}"
                                                                                                        data-base_price="{{ $variant_price }}"
                                                                                                        data-vendor_id="{{ $vendor_id }}"
                                                                                                        data-product_id="{{ $product_id }}"
                                                                                                        data-cart="{{ $cart_id }}"
                                                                                                        data-addon="{{ $isAddonExist }}"
                                                                                                        data-batch_count="{{ $batch_count }}">
                                                                                                        <i class="fa fa-plus"
                                                                                                            aria-hidden="true"></i>
                                                                                                    </span>
                                                                                                </div>
                                                                                            @else
                                                                                                    <a class="btn btn-solid " id="added_button_href{{$cartProductId}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                                            @endif

                                                                                        @else
                                                                                            @if ( (in_array($data->category_type_id,[12,8]))  || ($prod->has_inventory == 0 || ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1)))
                                                                                                @if(   $is_service_product_price_from_dispatch_forOnDemand ==1)
                                                                                                    <a class="btn btn-solid btn btn-solid view_on_demand_price"  id="add_button_href{{$data->id }}" data-variant_id = {{ isset($data->variant[0])?$data->variant[0]->id:0.00 }} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                                                @else
                                                                                                    {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                                                                                                    <a class="add-cart-btn add_vendor_product"
                                                                                                        id="aadd_button_href{{ $data->id }}"
                                                                                                        data-variant_id="{{ isset($data->variant[0])?$data->variant[0]->id:0.00 }}"
                                                                                                        data-add_to_cart_url="{{ route('addToCart') }}"
                                                                                                        data-vendor_id="{{ $data->vendor_id }}"
                                                                                                        data-product_id="{{ $data->id }}"
                                                                                                        data-addon="{{ $isAddonExist }}"
                                                                                                        data-batch_count="{{ $batch_count }}"
                                                                                                        data-minimum_order_count="{{ $minimum_order_count }}"
                                                                                                        href="javascript:void(0)">{{ __('Add') }}
                                                                                                        @if ($minimum_order_count > 1)
                                                                                                            ({{ $minimum_order_count }})
                                                                                                        @endif
                                                                                                    </a>
                                                                                                @endif
                                                                                                @if(isset($data->category_type_id) && (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)) )
                                                                                                    <div class="number"
                                                                                                        style="display:none;"
                                                                                                        id="ashow_plus_minus{{ $data->id }}">
                                                                                                        <span
                                                                                                            class="minus qty-minus-product"
                                                                                                            data-parent_div_id="show_plus_minus{{ $data->id }}"
                                                                                                            data-id="{{ $data->id }}"
                                                                                                            data-base_price="{{ decimal_format($data->variant_price * $data->variant_multiplier) }}"
                                                                                                            data-vendor_id="{{ $data->vendor_id }}"
                                                                                                            data-batch_count="{{ $batch_count }}"
                                                                                                            data-minimum_order_count="{{ $minimum_order_count }}">
                                                                                                            <i class="fa fa-minus"
                                                                                                                aria-hidden="true"></i>
                                                                                                        </span>
                                                                                                        <input
                                                                                                            style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                                                                            id="quantity_ondemand_d{{ $data->id }}"
                                                                                                            readonly
                                                                                                            placeholder="{{ $minimum_order_count }}"
                                                                                                            type="text"
                                                                                                            value="{{ $minimum_order_count }}"
                                                                                                            class="input-number input_qty"
                                                                                                            step="0.01">
                                                                                                        <span
                                                                                                            class="plus qty-plus-product"
                                                                                                            data-id=""
                                                                                                            data-base_price="{{ decimal_format($data->variant_price * $data->variant_multiplier) }}"
                                                                                                            data-vendor_id="{{ $data->vendor_id }}"
                                                                                                            data-batch_count="{{ $batch_count }}"
                                                                                                            data-minimum_order_count="{{ $minimum_order_count }}">
                                                                                                            <i class="fa fa-plus"
                                                                                                                aria-hidden="true"></i>
                                                                                                        </span>
                                                                                                    </div>
                                                                                                @else
                                                                                                    <a class="btn btn-solid "  style="display:none;" id="added_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                                                @endif
                                                                                            @else
                                                                                                <span
                                                                                                    class="text-danger">{{ __('Out of stock') }}</span>
                                                                                            @endif
                                                                                        @endif
                                                                                        @if ($is_customizable)
                                                                                            <div class="customizable-text">
                                                                                                {{ __('customizable') }}
                                                                                            </div>
                                                                                        @endif
                                                                                    @endif
                                                                                @else
                                                                                    <a class="btn btn-solid"  href="{{  $product_url }}">{{ __('View') }}</a>
                                                                                @endif
                                                                        </div>
                                                                    </div>
                                                                    @if ($prod->averageRating > 0 && $client_preference_detail->rating_check == 1)
                                                                        <div class="rating-text-box">
                                                                            <span>{{ number_format($prod->averageRating, 1, '.', '') }}
                                                                            </span>
                                                                            <i class="fa fa-star"
                                                                                aria-hidden="true"></i>
                                                                        </div>
                                                                    @endif

                                                                    @if ($prod->minimum_order_count > 0)
                                                                        {{-- <p class="mb-1 product_price">   {{__('Minimum Quantity') }} : {{ $prod->minimum_order_count }} </p>
                                                                        <p class="mb-1 product_price">   {{__('Batch') }} : {{ $prod->batch_count }} </p> --}}
                                                                    @endif

                                                                    <p class="mb-1 product_price ">
                                                                        @if($is_service_product_price_from_dispatch_forOnDemand !=1)
                                                                        {{-- price  not showing in vencor type in on demand and get price from dispatche--}}
                                                                            {{ Session::get('currencySymbol') . decimal_format($prod->variant_price * $prod->variant_multiplier,',') }}
                                                                            @if (@$prod->variant[0]->compare_at_price > 0)
                                                                                <span
                                                                                    class="org_price ml-1  font-14">{{ Session::get('currencySymbol') .decimal_format($prod->variant[0]->compare_at_price * $prod->variant_multiplier) }}</span>
                                                                            @endif
                                                                        @endif
                                                                    </p>
                                                                    <div class="member_no d-block mb-0">
                                                                        <span>{!! $prod->translation_description !!}</span>
                                                                    </div>
                                                                    <div id="product_variant_options_wrapper">
                                                                        @if (!empty($prod->variantSet))
                                                                            @php
                                                                                $selectedVariant = $productVariantIdInCart > 0 ? $productVariantIdInCart : $prod->variant_id;
                                                                            @endphp
                                                                            @foreach ($prod->variantSet as $key => $variant)
                                                                                @if ($variant->type == 1 || $variant->type == 2)
                                                                                    <?php $var_id = $variant->variant_type_id; ?>
                                                                                    <select
                                                                                        name="{{ 'var_' . $var_id }}"
                                                                                        vid="{{ $var_id }}"
                                                                                        class="changeVariant dataVar{{ $var_id }}">
                                                                                        <option value="" disabled>
                                                                                            {{ $variant->title }}
                                                                                        </option>
                                                                                        @foreach ($variant->option2 as $k => $optn)
                                                                                            <?php
                                                                                            $opt_id = $optn->variant_option_id;
                                                                                            $selected = $selectedVariant == $optn->product_variant_id ? 'selected' : '';
                                                                                            ?>
                                                                                            <option
                                                                                                value="{{ $opt_id }}"
                                                                                                {{ $selected }}>
                                                                                                {{ $optn->title }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                @else
                                                                                @endif
                                                                            @endforeach
                                                                        @endif
                                                                    </div>
                                                                    <div class="variant_response">
                                                                        <span
                                                                            class="text-danger mb-2 mt-2 font-14"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                @endforelse
                                            @else
                                                <h4 class="mt-3 mb-3 text-center">No product found</h4>
                                            @endif
                                        </section>
                                        @empty
                                            <h4 class="mt-3 mb-3 text-center">No product found</h4>
                                        @endforelse
                                    </div>
                                    <div class="col-12 col-lg-3 d-lg-inline-block d-none">
                                        <div class="card-box p-0 cart-main-box">
                                            <div class="p-2 d-flex align-items-center justify-content-between border-bottom">
                                                <h4 class="right-card-title">{{ __('Cart') }}</h4>
                                            </div>
                                            <div class="cart-main-box-inside d-flex align-items-center">
                                                <div class="spinner-box">
                                                    <div class="circle-border">
                                                        <div class="circle-core"></div>
                                                    </div>
                                                </div>
                                                <div class="show-div shopping-cart flex-fill w-100"
                                                    id="header_cart_main_ul_ondemand"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="d-none d-md-block">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/template" id="header_cart_template_ondemand">
        <ul class="pl-2 pr-2 pb-2 pt-0 ">
            <% _.each(cart_details.products, function(product, key){%>
            <li class="p-0">
                <h6 class="d-flex justify-content-center badge badge-light font-14"><b><%= product.vendor.name %></b></h6>
            </li>

            <% if( (product.isDeliverable != undefined) && (product.isDeliverable == 0) ) { %>
            <li class="border_0">
                <th colspan="7">
                    <div class="text-danger">
                        {{ __('Products for this vendor are not deliverable at your area. Please change address or remove product.') }}
                    </div>
                </th>
            </li>
            <% } %>
            <% _.each(product.vendor_products, function(vendor_product, vp){%>
            <li class="p-0" id="cart_product_<%= vendor_product.id %>" data-qty="<%= vendor_product.quantity %>">
                <div class='media-body'>
                    <h6 class="d-flex align-items-center justify-content-between">
                        <%
                            translationOneTitle = '';
                            count = 20;
                            if(vendor_product.product.translation_one != ''){
                                title = vendor_product.product.translation_one.title;
                                translationOneTitle = title.slice(0, count) + (title.length > count ? "..." : "");
                            }
                        %>

                        <span class="ellips"><%= vendor_product.quantity %>x <%=
                        vendor_product.product.translation_one ? translationOneTitle :  vendor_product.product.sku %></span>

                            <% if(cart_details.is_token_enable == 1) { %>
                                <span class="alPriceValue"><i class='fa fa-money mr-1' aria-hidden='true'></i><%=  Helper.formatPrice(vendor_product.quantity_price * cart_details.tokenAmount) %></span>
                                <% }else{ %>
                                <span>{{ Session::get('currencySymbol') }}<%=  Helper.formatPrice(vendor_product.quantity_price) %></span>
                            <% } %>

                        <a class="action-icon remove_product_via_cart text-danger" style="cursor: pointer;" data-product="<%= vendor_product.id %>" data-product_id="<%= vendor_product.product_id %>" data-vendor_id="<%= vendor_product.vendor_id %>">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </a>
                    </h6>
                </div>
            </li>
            <!--  -->
            <% if(vendor_product.addon.length != 0) { %>
                <hr class="my-2">
                <div class="row align-items-md-center">
                    <div class="col-12">
                        <h6 class="m-0 font-12"><b>{{ __('Add Ons') }}</b></h6>
                    </div>
                </div>
                <% _.each(vendor_product.addon, function(addon, ad){%>
                <div class="row mb-1">
                    <div class="col-md-6 col-sm-4 items-details text-left">
                        <p class="m-0 font-14 p-0"><%= vendor_product.quantity %>x <%= addon.option.title %></p>
                    </div>
                    <div class="col-md-3 col-sm-4 text-center">
                        <div class="extra-items-price font-14">
                            <% if(cart_details.is_token_enable == 1) { %>
                                    <i class='fa fa-money' aria-hidden='true'></i><%=  Helper.formatPrice(addon.option.price_in_cart * cart_details.tokenAmount) %>
                                <% }else{ %>
                                    {{ Session::get('currencySymbol') }}<%= Helper.formatPrice(addon.option.price_in_cart) %>
                            <% } %>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-4 text-right">
                        <div class="extra-items-price font-14 mr-xl-3">
                            <% if(cart_details.is_token_enable == 1) { %>
                                <i class='fa fa-money' aria-hidden='true'></i><%=  Helper.formatPrice(addon.option.quantity_price * cart_details.tokenAmount) %>
                            <% }else{ %>
                            {{ Session::get('currencySymbol') }}<%= Helper.formatPrice(addon.option.quantity_price) %>
                            <% } %>
                        </div>
                    </div>
                </div>
                <% }); %>
            <% } %>
            <hr class="my-2 mt-3 line_diff_between_products">
            <% }); %>
            <% if(cart_details.delivery_charges > 0) { %>
                {{-- <hr class="my-2"> --}}
                <div class="row justify-content-between">
                    <div class="col-md-6 col-sm-6 text-left">
                        <h6 class="m-0 font-14">{{ __('Delivery fee') }}</h6>
                    </div>
                    <div class="col-md-6 col-sm-6 text-right">
                        <div class="font-14 mr-xl-2">
                            <% if(cart_details.is_token_enable == 1) { %>
                                <span class="alPriceValue"><i class='fa fa-money mr-1' aria-hidden='true'></i><%=  Helper.formatPrice(cart_details.delivery_charges * cart_details.tokenAmount) %></span>
                            <% }else{ %>
                                {{ Session::get('currencySymbol') }}<%= Helper.formatPrice(cart_details.delivery_charges) %>
                            <% } %>
                            </div>
                    </div>
                </div>
            <% } %>

            <% }); %>

            <h5 class="d-flex align-items-center justify-content-between pb-2">{{ __('PRICE DETAILS') }} </h5>

            <% if(cart_details.total_service_fee > 0){ %>
            <li class="p-0 alSixCart">
                <div class='media-body'>
                    <h6 class="d-flex align-items-center justify-content-between">
                        <span class="ellips">{{ __('Service Fee') }}</span>
                        <% if(cart_details.is_token_enable == 1) { %>
                            <span class="alPriceValue"><i class='fa fa-money mr-1' aria-hidden='true'></i><%= Helper.formatPrice(cart_details.total_service_fee * cart_details.tokenAmount) %></span>
                            <% }else{ %>
                                <span >{{ Session::get('currencySymbol') }}<%= Helper.formatPrice(cart_details.total_service_fee) %></span>
                            <% } %>
                    </h6>
                </div>
            </li>
            <% } %>
            <li class="p-0 alSixCart">
                <div class='media-body'>
                    <h6 class="d-flex align-items-center justify-content-between">
                        <span class="ellips">{{ __('Total') }}</span>
                        <% if(cart_details.is_token_enable == 1) { %>
                            <span class="alPriceValue"><i class='fa fa-money mr-1' aria-hidden='true'></i><%= Helper.formatPrice(cart_details.gross_amount * cart_details.tokenAmount) %></span>
                            <% }else{ %>
                                <span >{{ Session::get('currencySymbol') }}<%= Helper.formatPrice(cart_details.gross_amount) %></span>
                            <% } %>
                    </h6>
                </div>
            </li>
            <% if((cart_details.total_taxable_amount != undefined) && (cart_details.total_taxable_amount > 0)) { %>
            <li class="p-0 alSixCart">
                <div class='media-body'>
                    <h6 class="d-flex align-items-center justify-content-between">
                        <span class="ellips">{{ __('Tax') }}</span>
                        <% if(cart_details.is_token_enable == 1) { %>
                            <span class="alPriceValue"><i class='fa fa-money mr-1' aria-hidden='true'></i><%= (cart_details.total_taxable_amount * cart_details.tokenAmount) %></span>
                            <% }else{ %>
                            <span>{{ Session::get('currencySymbol') }}<%= cart_details.total_taxable_amount %></span>
                        <% } %>
                    </h6>
                </div>
            </li>
            <% } %>

            <% if((cart_details.total_subscription_discount != undefined) && (cart_details.total_subscription_discount > 0)) { %>
                <li class="p-0 alSixCart">
                <div class='media-body'>
                    <h6 class="d-flex align-items-center justify-content-between">
                        <span class="ellips"> {{ __('Subscription Discount') }}</span>
                        <% if(cart_details.is_token_enable == 1) { %>
                            <span class="alPriceValue"><i class='fa fa-money mr-1' aria-hidden='true'></i><%= (cart_details.total_subscription_discount * cart_details.tokenAmount) %></span>
                            <% }else{ %>
                            <span>{{ Session::get('currencySymbol') }}<%= cart_details.total_subscription_discount %></span>
                            <% } %>
                    </h6>
                </div>
            </li>
            <% } %>

            <% if(cart_details.loyalty_amount > 0) { %>
            <li class="p-0 alSixCart">
                <div class='media-body'>
                    <h6 class="d-flex align-items-center justify-content-between">
                        <span class="ellips"> {{ __('Loyalty Amount') }} </span>
                        <% if(cart_details.is_token_enable == 1) { %>
                            <span class="alPriceValue"><i class='fa fa-money mr-1' aria-hidden='true'></i><%= (cart_details.loyalty_amount * cart_details.tokenAmount) %></span>
                            <% }else{ %>
                            <span>{{ Session::get('currencySymbol') }}<%= cart_details.loyalty_amount %></span>
                            <% } %>
                    </h6>
                </div>
            </li>
            <% } %>

            <% if(cart_details.wallet_amount_used > 0) { %>
            <li class="p-0 alSixCart">
                <div class='media-body'>
                    <h6 class="d-flex align-items-center justify-content-between">
                        <span class="ellips"> {{ __('Wallet Amount') }} </span>
                        <% if(cart_details.is_token_enable == 1) { %>
                            <span class="alPriceValue"><i class='fa fa-money mr-1' aria-hidden='true'></i><%= (cart_details.wallet_amount_used * cart_details.tokenAmount) %></span>
                            <% }else{ %>
                            <span>{{ '-'.Session::get('currencySymbol') }}<%= cart_details.wallet_amount_used %></span>
                            <% } %>
                    </h6>
                </div>
            </li>
            <% } %>
        </ul>
        <div class="cart-sub-total d-flex align-items-center justify-content-between">
            <span>{{ __('Total') }}</span>
            <% if(cart_details.is_token_enable == 1) { %>
                <span class="alPriceValue"><i class='fa fa-money' aria-hidden='true'></i> <%= Helper.formatPrice((cart_details.total_payable_amount * cart_details.tokenAmount)) %></span>
                <% }else{ %>
                <span>{{ Session::get('currencySymbol') }}<%= Helper.formatPrice(cart_details.total_payable_amount) %></span>
                <% } %>
        </div>
        <a class="checkout-btn text-center d-block" href="{{ route('showCart') }}">{{ __('Checkout') }}</a>
    </script>
    <script type="text/template" id="empty_cart_template">
        <div class="row">
            <div class="col-12 text-center pb-3">
                <img class="w-50 pt-3 pb-1" src="{{ asset('front-assets/images/ic_emptycart.svg') }}" alt="">
                <h5>{{ __('Your cart is empty') }}<br/>{{ __('Add an item to begin') }}</h5>
            </div>
        </div>
    </script>
    <script type="text/template" id="variant_image_template">
        <img src="<%= media.image_fit %>300/300<%= media.image_path %>" alt="">
                            </script>
    <script type="text/template" id="variant_template">
        <% if(variant.product.inquiry_only == 0) { %>
            <% if(is_token_enable == 1) { %>
                <i class='fa fa-money' aria-hidden='true'></i> <%= (variant.productPrice * tokenAmount)%>
                <% }else{ %>
                    {{ Session::get('currencySymbol') }}<%= variant.productPrice %>
                <% } %>
            <% if(variant.compare_at_price > 0 ) { %>
                <% if(is_token_enable == 1) { %>
                    <span class="org_price ml-1 font-14"><i class='fa fa-money' aria-hidden='true'></i> <%= (variant.compare_at_price* tokenAmount) %></span>
                    <% }else{ %>
                        <span class="org_price ml-1 font-14">{{ Session::get('currencySymbol') }}<%= variant.compare_at_price %></span>
                    <% } %>

            <% } %>
        <% } %>
    </script>
    <script type="text/template" id="variant_quantity_template">
        <% if(variant.quantity > 0){ %>
            <%
            var is_customizable = false;
            if(variant.isAddonExist > 0){
                is_customizable = true;
            }
            %>
            <% if(variant.check_if_in_cart != '') { %>
                {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                <a class="add-cart-btn add_vendor_product" style="display:none;" id="add_button_href<%= variant.check_if_in_cart.id %>" data-variant_id="<%= variant.id %>" data-add_to_cart_url="{{ route('addToCart') }}" data-vendor_id="<%= variant.check_if_in_cart.vendor_id %>" data-product_id="<%= variant.product_id %>" href="javascript:void(0)">{{ __('Add') }}</a>
                <div class="number" id="show_plus_minus<%= variant.check_if_in_cart.id %>">
                    <span class="minus qty-minus-product <% if(is_customizable){ %> remove-customize <% } %>"  data-parent_div_id="show_plus_minus<%= variant.check_if_in_cart.id %>" data-id="<%= variant.check_if_in_cart.id %>" data-base_price="<%= variant.price * variant.variant_multiplier %>" data-vendor_id="<%= variant.check_if_in_cart.vendor_id %>" data-product_id="<%= variant.product_id %>" data-cart="<%= variant.check_if_in_cart.cart_id %>">
                        <i class="fa fa-minus" aria-hidden="true"></i>
                    </span>
                    <input style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;" placeholder="1" type="text" value="<%= variant.check_if_in_cart.quantity %>" class="input-number" step="0.01" id="quantity_ondemand_<%= variant.check_if_in_cart.id %>" readonly>
                    <span class="plus qty-plus-product <% if(is_customizable){ %> repeat-customize <% } %>"  data-id="<%= variant.check_if_in_cart.id %>" data-base_price="<%= variant.price * variant.variant_multiplier %>" data-vendor_id="<%= variant.check_if_in_cart.vendor_id %>" data-product_id="<%= variant.product_id %>" data-cart="<%= variant.check_if_in_cart.cart_id %>">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </span>
                </div>
            <% }else{ %>
                {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                <a class="add-cart-btn add_vendor_product" id="aadd_button_href<%= variant.product_id %>" data-variant_id="<%= variant.id %>" data-add_to_cart_url="{{ route('addToCart') }}" data-vendor_id="<%= variant.product.vendor_id %>" data-product_id="<%= variant.product_id %>" data-addon="<%= variant.isAddonExist %>" href="javascript:void(0)">{{ __('Add') }}</a>
                <div class="number" style="display:none;" id="ashow_plus_minus<%= variant.product_id %>">
                    <span class="minus qty-minus-product"  data-parent_div_id="show_plus_minus<%= variant.product_id %>" readonly data-id="<%= variant.product_id %>" data-base_price="<%= variant.price * variant.variant_multiplier %>" data-vendor_id="<%= variant.product.vendor_id %>">
                        <i class="fa fa-minus" aria-hidden="true"></i>
                    </span>
                    <input style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;" id="quantity_ondemand_d<%= variant.product_id %>" readonly placeholder="1" type="text" value="2" class="input-number input_qty" step="0.01">
                    <span class="plus qty-plus-product"  data-id="" data-base_price="<%= variant.price * variant.variant_multiplier %>" data-vendor_id="<%= variant.product.vendor_id %>">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </span>
                </div>
            <% } %>
            <% if(is_customizable){ %>
                <div class="customizable-text">customizable</div>
            <% } %>
        <% }else{ %>
            <span class="text-danger">{{ __('Out of stock')}}</span>
        <% } %>
    </script>
    <script type="text/template" id="addon_template">
        <% if(addOnData != ''){ %>
        <% if(addOnData.product_image){ %>
            <div class="d-flex" style="height:200px">
                <img class="w-100" src="<%= addOnData.product_image %>" alt=""  style="object-fit:cover">
            </div>
        <% } %>
        <div class="modal-header">
            <div class="d-flex flex-column">
                <h5 class="modal-title" id="product_addonLabel"><%= addOnData.translation_title %></h5>
                <% if(addOnData.averageRating > 0){ %>
                <div class="rating-text-box justify-content-start" style="width: max-content;">
                    <span><%= addOnData.averageRating %></span>
                    <i class="fa fa-star" aria-hidden="true"></i>
                </div>
                <% } %>
                <span><small><%= addOnData.translation_description %></small></span>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-0">
            <% _.each(addOnData.add_on, function(addon, key1){ %>
                <div class="border-product border-top">
                    <div class="addon-product" style="padding: 16px;">
                        <h4 addon_id="<%= addon.addon_id %>" class="header-title productAddonSet mb-0"><%= addon.title %></h4>
                        <div class="addonSetMinMax mb-2">
                            <%
                                var min_select = '';
                                if(addon.min_select > 0){
                                    min_select = "{{ __('Minimum')}} " + addon.min_select;
                                }
                                var max_select = '';
                                if(addon.max_select > 0){
                                    max_select = "{{ __('Maximum')}} " + addon.max_select;
                                }
                                if( (min_select != '') && (max_select != '') ){
                                    min_select = min_select + " {{ __('and')}} ";
                                }
                            %>
                            <% if( (min_select != '') || (max_select != '') ) { %>
                                <small><%=min_select + max_select %> {{ __('Selections Allowed')}}</small>
                            <% } %>
                        </div>
                        <div class="productAddonSetOptions" data-min="<%= addon.min_select %>" data-max="<%= addon.max_select %>" data-addonset-title="<%= addon.title %>">
                            <% _.each(addon.setoptions, function(option, key2){ %>
                                <% if(key2 == '5')  { %>
                                    <div class="d-flex justify-content-end">
                                        <a class="show_subet_addeon" data-div_id_show="subOption<%= addon.addon_id  %>_<%= key2  %>"  href="javascript:void(0)">{{ __('Show more') }}</a>
                                    </div>
                                    <div class="more-subset d-none" id="subOption<%= addon.addon_id %>_<%= key2 %>" >
                                <% } %>
                                <div class="checkbox-success d-flex mb-1 " <%= key2  %> >
                                    <label class="pr-2 mb-0 flex-fill font-14" for="inlineCheckbox_<%= key1 %>_<%= key2 %>">
                                        <%= option.title %>
                                    </label>
                                    <div>
                                        <span class="addon_price mr-1 font-14">{{ Session::get('currencySymbol') }}<%= Helper.formatPrice(option.price) %></span>
                                        <input type="checkbox" id="inlineCheckbox_<%= key1 %>_<%= key2 %>" class="product_addon_option" name="addonData[<%= key1 %>][]" addonId="<%= addon.addon_id %>" addonOptId="<%= option.id %>" addonPrice="<%= option.price %>">
                                    </div>
                                </div>
                                <% if((key2 > 5) && (key2 == (_.size(addon.setoptions) - 1 )) ){ %>
                                </div>
                                <% } %>
                            <% }); %>
                        </div>
                    </div>
                </div>
            <% }); %>
            <div class="addon_response text-danger font-14 d-none" style="padding:0 16px"></div>
        </div>
        <div class="modal-footer flex-nowrap align-items-center">
            <div class="counter-container d-flex align-items-center">
                <span class="minus qty-action" >
                    <i class="fa fa-minus" aria-hidden="true"></i>
                </span>
                <input style="text-align:center; width:60px; height:24px; padding-bottom: 3px; border:none" placeholder="1" type="text" value="1" class="addon-input-number" step="1" readonly>
                <span class="plus qty-action" >
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </span>
            </div>
            <input type="hidden" id="addonVariantPriceVal" value="<%= addOnData.variant_price %>">
            <a class="btn btn-solid add-cart-btn flex-fill add_vendor_addon_product" id="add_vendor_addon_product" href="javascript:void(0)" data-variant_id="<%= addOnData.variant[0].id %>" data-add_to_cart_url="{{ route('addToCart') }}" data-vendor_id="<%= addOnData.vendor_id %>" data-product_id="<%= addOnData.id %>">{{ __('Add') }} {{ Session::get('currencySymbol') }}<span class="addon_variant_price"><%= addOnData.variant_price %></span></a>
        </div>
    <% } %>
</script>
    <div class="modal fade remove-item-modal" id="remove_item_modal" data-backdrop="static" data-keyboard="false"
        tabindex="-1" aria-labelledby="remove_itemLabel" aria-hidden="true"
        style="background-color: rgba(0,0,0,0.8); z-index: 1051">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header pb-0">
                    <h5 class="modal-title" id="remove_itemLabel">{{ __('Remove Item') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <input type="hidden" id="vendor_id" value="">
                    <input type="hidden" id="product_id" value="">
                    <input type="hidden" id="cartproduct_id" value="">
                    <h6 class="m-0 px-3">{{ __('Are You Sure You Want To Remove This Item?') }}</h6>
                </div>
                <div class="modal-footer flex-nowrap justify-content-center align-items-center">
                    <button type="button" class="btn btn-solid black-btn"
                        data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-solid" id="remove_product_button">{{ __('Remove') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade product-addon-modal" id="product_addon_modal" tabindex="-1" aria-labelledby="product_addonLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

            </div>
        </div>
    </div>
    <div class="modal fade repeat-item-modal" id="repeat_item_modal" data-backdrop="static" data-keyboard="false"
        tabindex="-1" aria-labelledby="repeat_itemLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header pb-0">
                    <h5 class="modal-title" id="repeat_itemLabel">{{ __('Repeat last used customization') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="last_cart_product_id" value="">
                    <input type="hidden" class="curr_variant_id" value="">
                    <input type="hidden" class="curr_vendor_id" value="">
                    <input type="hidden" class="curr_product_id" value="">
                    <input type="hidden" class="curr_product_has_addons" value="">
                    <input type="hidden" add_to_cart_url="cart" value="{{ route('addToCart') }}">
                </div>
                <div class="modal-footer flex-nowrap justify-content-center align-items-center">
                    <button type="button" class="btn btn-solid black-btn" id="repeat_item_with_new_addon_btn"
                        data-dismiss="modal">{{ __('Add new') }}</button>
                    <button type="button" class="btn btn-solid" id="repeat_item_btn">{{ __('Repeat last') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="social-media-links-modal" data-backdrop="static" data-keyboard="false"
        tabindex="-1" aria-labelledby="repeat_itemLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content social-icon-list">
                <div class="modal-header pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                   @if(!empty($socialMediaUrls))
                   @foreach($socialMediaUrls as $url)
                   <div class="text-center">
                        @php
                            if($url->icon == 'facebook'){
                                $iconUrl = asset('assets/images/social-media/facebook.png');
                            }else if($url->icon == 'github'){
                                $iconUrl = asset('assets/images/social-media/github.png');
                            }else if($url->icon == 'reddit'){
                                $iconUrl = asset('assets/images/social-media/reddit.png');
                            }else if($url->icon == 'whatsapp'){
                                $iconUrl = asset('assets/images/social-media/whatsapp-img.png');
                            }else if($url->icon == 'instagram'){
                                $iconUrl = asset('assets/images/social-media/instagram.png');
                            }else if($url->icon == 'tumblr'){
                                $iconUrl = asset('assets/images/social-media/tumblr.png');
                            }else if($url->icon == 'twitch'){
                                $iconUrl = asset('assets/images/social-media/twitch.png');
                            }else if($url->icon == 'twitter'){
                                $iconUrl = asset('assets/images/social-media/twitter.png');
                            }else if($url->icon == 'pinterest'){
                                $iconUrl = asset('assets/images/social-media/pinterest.png');
                            }else if($url->icon == 'youtube'){
                                $iconUrl = asset('assets/images/social-media/youtube.png');
                            }else if($url->icon == 'snapchat'){
                                $iconUrl = asset('assets/images/social-media/snapchat.png');
                            }else if($url->icon == 'linkedin'){
                                $iconUrl = asset('assets/images/social-media/linkedin.png');
                            }
                        @endphp
                        <a target="_blank" href="{{$url->url}}"><img src="{{$iconUrl}}" alt=""></a>
                    </div>
                   @endforeach
                   @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade customize-repeated-item-modal" id="customize_repeated_item_modal" data-backdrop="static"
        data-keyboard="false" tabindex="-1" aria-labelledby="customize_repeated_itemLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

            </div>
        </div>
    </div>
    <!-- vendorStories -->
    <!-- <div id="vendorStories" class="modal fade" tabindex="-1" aria-labelledby="vendorStoriesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
                <img class="modal-content" id="img01">
        </div>
    </div> -->

    @if($is_service_product_price_from_dispatch_forOnDemand ==1)
        @include('frontend.ondemand.productPriceModel');
    @endif
@endsection
@if($is_service_product_price_from_dispatch_forOnDemand ==1)

    @section('custom-js')
    <script src="{{ asset('js/onDemand/GetDispatcherPrice.js') }}"></script>
    @endsection
@endif
@section('script')

    <script src="{{ asset('front-assets/js/rangeSlider.min.js') }}"></script>
    <script src="{{ asset('front-assets/js/my-sliders.js') }}"></script>
    <script>
        @if(!empty($vendor->banner))
            $(document).ready(function() {
                $("body").addClass("homeHeader");
            });
        @endif

         //Get the modal vendorStories
        var modal = document.getElementById("vendorStories");

         //Get the image and insert it inside the modal - use its "alt" text as a caption
        var img = document.getElementById("vendorStoriesImg");
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");
            img.onclick = function(){
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        }

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

         //When the user clicks on <span> (x), close the modal
        span.onclick = function() {
        modal.style.display = "none";
        }
    </script>
    <script>
        var get_product_addon_url = "{{ route('vendorProductAddons') }}"

        // jQuery(window).scroll(function() {

        //     var scroll = jQuery(window).scrollTop();
        //     var categories_list_height = $('.vendor-products-wrapper').height() +400;

        //     if (scroll >= 600) {
        //         jQuery(".categories-product-list").addClass("fixed-bar");
        //     } else {
        //         jQuery(".categories-product-list").removeClass("fixed-bar");
        //     }
        //     if(scroll >= categories_list_height){
        //         jQuery(".categories-product-list").removeClass("fixed-bar");
        //     }
        // });

        var addonids = [];
        var addonoptids = [];
        var showChar = 136;
        var ellipsestext = "...";
        var moretext = "Read more";
        var lesstext = "Read less";

        function addReadMoreLink(){
            $('.price_head .member_no span').each(function() {
                var content = $(this).html();
                if (content.length > showChar) {

                    var firstContent = content.substr(0, showChar);
                    var lastContent = content.substr(showChar, content.length - showChar);
                    firstContent = firstContent.trim();
                    var html = firstContent + '<span class="moreellipses">' + ellipsestext +
                        '</span><span class="morecontent"><span style="display:none;">' + lastContent +
                        '</span><a href="" class="morelink">' + moretext + '</a></span>';

                    $(this).html(firstContent+lastContent);
                }

            });
        }
        addReadMoreLink();

        $(document).on('click', '.morelink', function() {
            if ($(this).hasClass("less")) {
                $(this).removeClass("less");
                $(this).html(moretext);
            } else {
                $(this).addClass("less");
                $(this).html(lesstext);
            }
            $(this).parent().prev().toggle();
            $(this).prev().toggle();
            return false;
        });

        $(document).ready(function(){
            vendorProductsSearchResults();
        });

        $(document).delegate(".product_tag_filter", "change", function() {
            vendorProductsSearchResults();
        });
    </script>
    <script>
        var base_url = "{{ url('/') }}";
        var place_order_url = "{{ route('user.placeorder') }}";
        var payment_stripe_url = "{{ route('payment.stripe') }}";
        var user_store_address_url = "{{ route('address.store') }}";
        var promo_code_remove_url = "{{ route('remove.promocode') }}";
        var payment_paypal_url = "{{ route('payment.paypalPurchase') }}";
        var update_qty_url = "{{ url('product/updateCartQuantity') }}";
        var promocode_list_url = "{{ route('verify.promocode.list') }}";
        var payment_option_list_url = "{{ route('payment.option.list') }}";
        var apply_promocode_coupon_url = "{{ route('verify.promocode') }}";
        var payment_success_paypal_url = "{{ route('payment.paypalCompletePurchase') }}";
        var getTimeSlotsForOndemand = "{{ route('getTimeSlotsForOndemand') }}";
        var update_cart_schedule = "{{ route('cart.updateSchedule') }}";
        var showCart = "{{ route('showCart') }}";
        var update_addons_in_cart = "{{ route('addToCartAddons') }}";
        var vendor_products_page_search_url = "{{ route('vendorProductsSearchResults') }}";
        var get_last_added_product_variant_url = "{{ route('getLastAddedProductVariant') }}";
        var get_product_variant_with_different_addons_url = "{{ route('getProductVariantWithDifferentAddons') }}"
        var addonids = [];
        var addonoptids = [];
        var ajaxCall = 'ToCancelPrevReq';


        $(document).on('click', '.open-social-medialinks', function(e) {
            $('#social-media-links-modal').modal({
                backdrop: 'static',
                keyboard: false
            });
        });
        $(document).on('click', '.show_subet_addeon', function(e) {
            e.preventDefault();
            var show_class = $(this).data("div_id_show");
            $(this).addClass("d-none");
            $("#" + show_class).removeClass("d-none");
        });

        $(document).delegate('.changeVariant', 'change', function() {
            var variants = [];
            var options = [];
            var product_variant_url = "{{ route('productVariant', ':sku') }}";
            var sku = $(this).parents('.product_row').attr('data-p_sku');
            var that = this;
            $(that).parents('.product_row').find('.changeVariant').each(function() {
                if (this.val != '') {
                    variants.push($(this).attr('vid'));
                    options.push($(this).val());
                }
            });
            ajaxCall = $.ajax({
                type: "post",
                dataType: "json",
                url: product_variant_url.replace(":sku", sku),
                data: {
                    "_token": "{{ csrf_token() }}",
                    "variants": variants,
                    "options": options,
                },
                beforeSend: function() {
                    if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                        ajaxCall.abort();
                    }
                },
                success: function(response) {
                    if (response.status == 'Success') {
                        response = response.data;
                        $(that).parents('.product_row').find(".variant_response span").html('');
                        if (response.variant != '') {

                            $(that).parents('.product_row').find(".add-cart-btn").attr(
                                'data-variant_id', response.variant.id);

                            $(that).parents('.product_row').find('.product_price').html('');
                            let variant_template = _.template($('#variant_template').html());
                            $(that).parents('.product_row').find('.product_price').append(
                                variant_template({
                                    variant: response.variant, tokenAmount: response.tokenAmount, is_token_enable: response.is_token_enable
                                }));

                            $(that).parents('.product_row').find('.product_variant_quantity_wrapper')
                                .html('');
                            let variant_quantity_template = _.template($('#variant_quantity_template')
                                .html());
                            $(that).parents('.product_row').find('.product_variant_quantity_wrapper')
                                .append(variant_quantity_template({
                                    variant: response.variant
                                }));

                            let variant_image_template = _.template($('#variant_image_template')
                                .html());

                            $(that).parents('.product_row').find('.product_image').html('');
                            $(that).parents('.product_row').find('.product_image').append(
                                variant_image_template({
                                    media: response.variant
                                }));
                        }
                    } else {
                        $(that).parents('.product_row').find(".variant_response span").html(response
                            .message);
                        $(that).parents('.product_row').find(".add-cart-btn").hide();
                        $(that).parents('.product_row').find(
                            ".product_variant_quantity_wrapper .text-danger").remove();
                    }
                },
                error: function(data) {

                },
            });
        });

        $(document).delegate("#vendor_search_box", "input", function() {
            let keyword = $(this).val();
            vendorProductsSearchResults();
        });

        function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).text()).select();
            document.execCommand("copy");
            $temp.remove();
            $("#show_copy_msg_on_click_copy").hide();
            $("#show_copy_msg_on_click_copied").show();
            setTimeout(function() {
                $("#show_copy_msg_on_click_copied").hide();
                $("#show_copy_msg_on_click_copy").show();
            }, 1000);
        }

        function vendorProductsSearchResults(id = '') {


            let keyword = $("#vendor_search_box").val();
            let order_type = $("#order_type").val();
            var checkboxesChecked = [];
            $("input:checkbox[name=tag_id]:checked").each(function() {
                checkboxesChecked.push($(this).val());
            });
            var checkedvalus = checkboxesChecked.length > 0 ? checkboxesChecked : null;
            // if (keyword.length > 2 || keyword.length == 0) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            ajaxCall = $.ajax({
                type: "post",
                dataType: 'json',
                url: vendor_products_page_search_url,
                data: {
                    tag_id: checkedvalus,
                    keyword: keyword,
                    order_type: order_type,
                    vendor: "{{ $vendor->id }}",
                    vendor_category: id ?? "{{ $vendor_category ?? '' }}"
                },
                beforeSend: function() {
                    if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                        ajaxCall.abort();
                    }
                },
                success: function(response) {
                    if (response.status == 'Success') {
                        var cart_html = $('.vendor-products-wrapper #header_cart_main_ul_ondemand').html();
                        $('.vendor-products-wrapper').html(response.html);
                        $('.vendor-products-wrapper #header_cart_main_ul_ondemand').html(cart_html);
                        addReadMoreLink();
                    }
                }
            });
            // }
        }
    </script>

@endsection
