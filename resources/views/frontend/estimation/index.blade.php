@extends('layouts.store', ['title' => 'Get Estimation'])
@section('css')
    <style type="text/css">
        .main-menu .brand-logo {
            display: inline-block;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .modal-backdrop
        {
            opacity:0.5 !important;
        }

    </style>
    <link rel="stylesheet" type="text/css" href="{{ asset('front-assets/css/price-range.css') }}">
@endsection
@section('content')
    <header>
        <div class="mobile-fix-option"></div>
        @if (isset($set_template) && $set_template->template_id == 1)
            @include('layouts.store/left-sidebar-template-one')
        @elseif(isset($set_template) && $set_template->template_id == 2)
            @include('layouts.store/left-sidebar')
        @else
            @include('layouts.store/left-sidebar-template-one')
        @endif
    </header>
    <style type="text/css">
        .productVariants .firstChild {
            min-width: 150px;
            text-align: left !important;
            border-radius: 0% !important;
            margin-right: 10px;
            cursor: default;
            border: none !important;
        }

        .product-right .color-variant li,
        .productVariants .otherChild {
            height: 35px;
            width: 35px;
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer;
            border: 1px solid #f7f7f7;
            text-align: center;
        }

        .productVariants .otherSize {
            height: auto !important;
            width: auto !important;
            border: none !important;
            border-radius: 0%;
        }

        .product-right .size-box ul li.active {
            background-color: inherit;
        }

        .product-box .product-detail h4,
        .product-box .product-info h4 {
            font-size: 16px;
        }

        select.changeVariant {
            color: #343a40;
            border: 1px solid #bbb;
            border-radius: 5px;
            font-size: 14px;
        }

        .counter-container {
            border: 1px solid var(--theme-deafult);
            border-radius: 5px;
            padding: 2px;
        }

        .switch {
            opacity: 0;
            position: absolute;
            z-index: 1;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .switch+.lable {
            position: relative;
            display: inline-block;
            margin: 0;
            line-height: 20px;
            min-height: 18px;
            min-width: 18px;
            font-weight: normal;
            cursor: pointer;
        }

        .switch+.lable::before {
            cursor: pointer;
            font-family: fontAwesome;
            font-weight: normal;
            font-size: 12px;
            color: #32a3ce;
            content: "\a0";
            background-color: #FAFAFA;
            border: 1px solid #c8c8c8;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border-radius: 0;
            display: inline-block;
            text-align: center;
            height: 16px;
            line-height: 14px;
            min-width: 16px;
            margin-right: 1px;
            position: relative;
            top: -1px;
        }

        .switch:checked+.lable::before {
            display: inline-block;
            content: '\f00c';
            background-color: #F5F8FC;
            border-color: #adb8c0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05), inset 15px 10px -12px rgba(255, 255, 255, 0.1);
        }

        /* CSS3 on/off switches */
        .switch+.lable {
            margin: 0 4px;
            min-height: 24px;
        }

        .switch+.lable::before {
            font-weight: normal;
            font-size: 11px;
            line-height: 17px;
            height: 20px;
            overflow: hidden;
            border-radius: 12px;
            background-color: #F5F5F5;
            -webkit-box-shadow: inset 0 1px 1px 0 rgba(0, 0, 0, 0.15);
            box-shadow: inset 0 1px 1px 0 rgba(0, 0, 0, 0.15);
            border: 1px solid #CCC;
            text-align: left;
            float: left;
            padding: 0;
            width: 52px;
            text-indent: -21px;
            margin-right: 0;
            -webkit-transition: text-indent .3s ease;
            -o-transition: text-indent .3s ease;
            transition: text-indent .3s ease;
            top: auto;
        }

        .switch.switch-bootstrap+.lable::before {
            font-family: FontAwesome;
            content: "\f00d";
            box-shadow: none;
            border-width: 0;
            font-size: 16px;
            background-color: #a9a9a9;
            color: #F2F2F2;
            width: 52px;
            height: 22px;
            line-height: 21px;
            text-indent: 32px;
            -webkit-transition: background 0.1s ease;
            -o-transition: background 0.1s ease;
            transition: background 0.1s ease;
        }

        .switch.switch-bootstrap+.lable::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 3px;
            border-radius: 12px;
            box-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
            width: 18px;
            height: 18px;
            text-align: center;
            background-color: #F2F2F2;
            border: 4px solid #F2F2F2;
            -webkit-transition: left 0.2s ease;
            -o-transition: left 0.2s ease;
            transition: left 0.2s ease;
        }

        .switch.switch-bootstrap:checked+.lable::before {
            content: "\f00c";
            text-indent: 6px;
            color: #FFF;
            border-color: #b7d3e5;

        }

        .switch-primary>.switch.switch-bootstrap:checked+.lable::before {
            background-color: #337ab7;
        }

        .switch-success>.switch.switch-bootstrap:checked+.lable::before {
            background-color: #5cb85c;
        }

        .switch-danger>.switch.switch-bootstrap:checked+.lable::before {
            background-color: #d9534f;
        }

        .switch-info>.switch.switch-bootstrap:checked+.lable::before {
            background-color: #5bc0de;
        }

        .switch-warning>.switch.switch-bootstrap:checked+.lable::before {
            background-color: #f0ad4e;
        }

        .switch.switch-bootstrap:checked+.lable::after {
            left: 32px;
            background-color: #FFF;
            border: 4px solid #FFF;
            text-shadow: 0 -1px 0 rgba(0, 200, 0, 0.25);
        }

        /* square */
        .switch-square {
            opacity: 0;
            position: absolute;
            z-index: 1;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .switch-square+.lable {
            position: relative;
            display: inline-block;
            margin: 0;
            line-height: 20px;
            min-height: 18px;
            min-width: 18px;
            font-weight: normal;
            cursor: pointer;
        }

        .switch-square+.lable::before {
            cursor: pointer;
            font-family: fontAwesome;
            font-weight: normal;
            font-size: 12px;
            color: #32a3ce;
            content: "\a0";
            background-color: #FAFAFA;
            border: 1px solid #c8c8c8;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border-radius: 0;
            display: inline-block;
            text-align: center;
            height: 16px;
            line-height: 14px;
            min-width: 16px;
            margin-right: 1px;
            position: relative;
            top: -1px;
        }

        .switch-square:checked+.lable::before {
            display: inline-block;
            /* content: '\f00c'; */
            background-color: #F5F8FC;
            border-color: #adb8c0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05), inset 15px 10px -12px rgba(255, 255, 255, 0.1);
        }

        /* CSS3 on/off switches */
        .switch-square+.lable {
            margin: 0 4px;
            min-height: 24px;
        }

        .switch.switch-bootstrap:checked+.lable::before,
        .switch.switch-bootstrap+.lable::before {
            content: "";
            width: 40px;
            height: 18px;
            line-height: 21px;
        }

        .switch.switch-bootstrap+.lable::after {
            width: 14px;
            height: 14px;
        }

        .switch+.lable {
            line-height: 14px;
        }

        .switch.switch-bootstrap:checked+.lable::after {
            left: 23px;
        }

        .switch-square+.lable::before {
            font-weight: normal;
            font-size: 11px;
            line-height: 17px;
            height: 20px;
            overflow: hidden;
            border-radius: 2px;
            background-color: #F5F5F5;
            -webkit-box-shadow: inset 0 1px 1px 0 rgba(0, 0, 0, 0.15);
            box-shadow: inset 0 1px 1px 0 rgba(0, 0, 0, 0.15);
            border: 1px solid #CCC;
            text-align: left;
            float: left;
            padding: 0;
            width: 52px;
            text-indent: -21px;
            margin-right: 0;
            -webkit-transition: text-indent .3s ease;
            -o-transition: text-indent .3s ease;
            transition: text-indent .3s ease;
            top: auto;
        }

        .switch-square.switch-bootstrap+.lable::before {
            font-family: FontAwesome;
            /* content: "\f00d"; */
            box-shadow: none;
            border-width: 0;
            font-size: 16px;
            background-color: #a9a9a9;
            color: #F2F2F2;
            width: 52px;
            height: 22px;
            line-height: 21px;
            text-indent: 32px;
            -webkit-transition: background 0.1s ease;
            -o-transition: background 0.1s ease;
            transition: background 0.1s ease;
        }

        .switch-square.switch-bootstrap+.lable::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 3px;
            border-radius: 12px;
            box-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
            width: 18px;
            height: 18px;
            text-align: center;
            background-color: #F2F2F2;
            border: 4px solid #F2F2F2;
            -webkit-transition: left 0.2s ease;
            -o-transition: left 0.2s ease;
            transition: left 0.2s ease;
        }

        .switch-square.switch-bootstrap:checked+.lable::before {
            /* content: "\f00c";*/
            text-indent: 6px;
            color: #FFF;
            border-color: #b7d3e5;

        }

        .switch-primary>.switch-square.switch-bootstrap:checked+.lable::before {
            background-color: #337ab7;
        }

        .switch-success>.switch-square.switch-bootstrap:checked+.lable::before {
            background-color: #5cb85c;
        }

        .switch-danger>.switch-square.switch-bootstrap:checked+.lable::before {
            background-color: #d9534f;
        }

        .switch-info>.switch-square.switch-bootstrap:checked+.lable::before {
            background-color: #5bc0de;
        }

        .switch-warning>.switch-square.switch-bootstrap:checked+.lable::before {
            background-color: #f0ad4e;
        }

        .switch-square.switch-bootstrap:checked+.lable::after {
            left: 32px;
            background-color: #FFF;
            border: 4px solid #FFF;
            text-shadow: 0 -1px 0 rgba(0, 200, 0, 0.25);
        }

        .switch-square.switch-bootstrap+.lable::after {
            border-radius: 2px;
        }

    </style>
    <!-- section start -->
    <section class="section-b-space ratio_asos composite">
        <div class="bb grid-width-100"></div>
        <div class="collection-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="product-bottom-bar pt-5">
                            <div class="row pt-5">
                                <div class="col-md-10 col-lg-5 order-0">
                                    <div class="card-box vendor-details-left px-2 py-3">
                                        <div class="d-sm-flex">
                                            <div class="ml-sm-1">
                                                <h3>Get Estimation</h3>
                                                    <h6  style="line-height: 24px">
                                                        An estimate is an educated guess at what a job may cost.
                                                    </h6>
                                                 <ul class="vendor-info">
                                                    <span class="badge badge-danger">Choose the best option available.</span>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="col-lg-4 col-xl-5 order-lg-1 order-2">
                                    <div class="vendor-search-bar">
                                        <div class="radius-bar w-100">
                                            <div class="search_form d-flex align-items-center justify-content-between">
                                                <button class="btn"><i class="fa fa-search"
                                                        aria-hidden="true"></i></button>
                                                <input class="form-control border-0 typeahead" type="search"
                                                    placeholder="{{ __('Search') }}" id="vendor_search_box">
                                            </div>
                                            <div class="list-box style-4" style="display:none;" id="search_box_main_div">
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="position-relative">
                    <div class="categories-product-list">

                        <a id="side_menu_toggle" class="d-md-none d-flex" href="javascript:void(0)">
                            <div class="manu-bars">
                                <span class="bar-line"></span>
                                <span class="bar-line"></span>
                                <span class="bar-line"></span>
                            </div>
                            <span>{{ _('Menu') }}</span>
                        </a>

                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-8"></div>
                            <div class="col-12">
                                <hr>
                                <div class="row vendor-products-wrapper">
                                    <div class="col-sm-4 col-lg-3 border-right">
                                        <nav class="scrollspy-menu">
                                            <ul>
                                                @foreach ($products as $data)
                                                    <li>
                                                        <a href="#{{ $data->category->slug }}">{{ $data->category->primary->name }}
                                                            <i style="font-size: 13px;" class="fa fa-arrow-right"
                                                                aria-hidden="true"></i>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </nav>
                                    </div>
                                    <div class="col-md-8 col-lg-6">
                                        @foreach ($products as $parent)
                                            <section class="scrolling_section" id="{{ $parent->category->slug }}">
                                                <?php $productDetails = \App\Models\EstimateProduct::where('category_id', $parent->category_id)->get(); ?>
                                                <h2 class="category-head mt-0 mb-3" style="position:initial;">
                                                    {{ $parent->category->translation_one->name }}
                                                    ({{ $productDetails->count() }}) </h2>

                                                @foreach ($productDetails as $product)
                                                    <div class="row cart-box-outer product_row classes_wrapper no-gutters mb-3"
                                                        data-p_sku="{{ $product->sku }}"
                                                        data-slug="{{ $product->id }}">
                                                        <div class="col-2">
                                                            <a target="_blank" href="#">
                                                                <div class="class_img product_image">
                                                                    <img src="{{ $product->icon['image_fit'] }}300/300{{ $product->icon['image_path'] }}"
                                                                        alt="{{ $product->estimate_product_translation->name }}">
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="col-10">
                                                            <div class="row price_head pl-3 pl-sm-2">
                                                                <div class="col-sm-12 pl-2">
                                                                    <div
                                                                        class="d-flex align-items-start justify-content-between">
                                                                        <h5 class="mt-0">
                                                                            {{ $product->estimate_product_translation->name }}
                                                                        </h5>
                                                                        <div class="product_variant_quantity_wrapper">

                                                                            <a class="add-cart-btn add_vendor_product_btn"
                                                                                id="aadd_button_href233"
                                                                                data-variant_id="317"
                                                                                data-add_to_cart_url="{{ route('addToEstimateCart') }}"
                                                                                data-vendor_id="23" data-product_id="233"
                                                                                data-addon="1"
                                                                                href="javascript:void(0)">Add</a>
                                                                            <div class="number"
                                                                                style="display:none;"
                                                                                id="ashow_plus_minus233">
                                                                                <span class="minus qty-minus-product"
                                                                                    data-parent_div_id="show_plus_minus233"
                                                                                    data-id="233" data-base_price="1023834"
                                                                                    data-vendor_id="23">
                                                                                    <i class="fa fa-minus"
                                                                                        aria-hidden="true"></i>
                                                                                </span>
                                                                                <input
                                                                                    style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                                                    id="quantity_ondemand_d233" readonly=""
                                                                                    placeholder="1" type="text" value="1"
                                                                                    class="input-number input_qty"
                                                                                    step="0.01">
                                                                                <span class="plus qty-plus-product"
                                                                                    data-id="" data-base_price="1023834"
                                                                                    data-vendor_id="23">
                                                                                    <i class="fa fa-plus"
                                                                                        aria-hidden="true"></i>
                                                                                </span>
                                                                            </div>
                                                                            <div class="customizable-text">{{ _('customizable')}}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="member_no d-block mb-0">
                                                                        <span></span>
                                                                    </div>
                                                                    <div id="product_variant_options_wrapper">
                                                                    </div>
                                                                    <div class="variant_response">
                                                                        <span class="text-danger mb-2 mt-2 font-14"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </section>
                                        @endforeach
                                    </div>
                                    <div class="col-12 col-lg-3 d-lg-inline-block d-none">
                                        <div class="card-box p-0 cart-main-box">
                                            <div
                                                class="p-2 d-flex align-items-center justify-content-between border-bottom">
                                                <h4 class="right-card-title">Estimation Cart</h4>
                                            </div>
                                            <div class="cart-main-box-inside">
                                                <div class="spinner-box" style="display:none">
                                                    <div class="circle-border">
                                                        <div class="circle-core"></div>
                                                    </div>
                                                </div>
                                                <div class="show-div">
                                                    <div class="row estimate_empty_cart_icon">
                                                        @if (empty($estimatedProducts))
                                                            <div class="col-12 text-center pb-3">
                                                                <img class="w-50 pt-3 pb-1"
                                                                    src="http://localhost:8000/front-assets/images/ic_emptycart.svg"
                                                                    alt="">
                                                                <h5>Your estimation cart is empty<br>Add an item to begin</h5>
                                                            </div>
                                                        @else
                                                            <div class="col-12 text-left">
                                                                <ul class="pl-2 pr-2 pb-2 pt-0 ">
                                                                        @foreach ($estimatedProducts as $estimatedProduct)
                                                                            <li class="p-0 pt-2" id="cart_product_1587" data-qty="1" >
                                                                                <div class="media-body">
                                                                                    <h6 class="d-flex align-items-center justify-content-between m-0">
                                                                                        <span class="ellips font-16">{{$estimatedProduct->estimated_product->primary->name}} &nbsp; </span>
                                                                                        <a data-value="{{$estimatedProduct->product_id}}" data-estimated_cart_id="{{$estimatedProduct->estimated_cart_id}}" class="action-icon remove_product_via_cart text-danger remove_estimate_cart_product" href="javascript:void(0);">
                                                                                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                                                        </a>
                                                                                    </h6>
                                                                                </div>
                                                                            </li>

                                                                                <div class="row align-items-md-center">
                                                                                    <div class="col-12">
                                                                                        <h6 class="m-0 font-12"><b>Add Ons</b></h6>
                                                                                    </div>
                                                                                </div>

                                                                            @foreach ($estimatedProduct->estimated_product_addons as $addon)
                                                                                    <div class="row mb-1">
                                                                                        <div class="col-md-12 col-sm-4 items-details text-left">
                                                                                            <p class="m-0 font-14 p-0">{{$addon->estimated_product_addon_option->title}}</p>
                                                                                        </div>
                                                                                    </div>
                                                                            @endforeach

                                                                            <hr class="my-2">

                                                                        @endforeach
                                                                </ul>

                                                                <div class="row">
                                                                    <div class="col-12 text-center">
                                                                        <a href="javascript:void(0)" class="get_estimation_btn" id="get_estimation_btn">
                                                                            <div class="cart-sub-total text-center">
                                                                                <span class="get_estimation_btn_text">Get Estimation
                                                                                </span>
                                                                                <img id="get_estimation_btn_loader" style="width:15%; display:none;" src="{{asset('assets/images/loading_new.gif')}}" alt="">
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif


                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/template" id="estimate_addon_template">

            <% if(estimateAddOnData != ''){ %>
                        <% if(estimateAddOnData.product_image){ %>

                            <div class="d-flex" style="max-height:200px">
                                <img class="w-100" src="<%= estimateAddOnData.product_image %>" alt=""  style="object-fit:cover">
                            </div>
                        <% } %>
                        <div class="modal-header">
                            <div class="d-flex flex-column">
                                <h5 class="modal-title" id="product_addonLabel"><%= estimateAddOnData.translation_title %></h5>
                                <% if(estimateAddOnData.averageRating > 0){ %>
                                <div class="rating-text-box justify-content-start" style="width: max-content;">
                                    <span><%= estimateAddOnData.averageRating %></span>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                </div>
                                <% } %>
                                <span><small><%= estimateAddOnData.translation_description %></small></span>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <% _.each(estimateAddOnData.estimate_product_addons, function(estimate_product_addon, key1){ %>
                                <div class="border-product border-top">
                                    <div class="addon-product" style="padding: 16px;">

                                        <% _.each(estimate_product_addon, function(estimate_addon_set, key2){ %>
                                            <h4 addon_id="<%= estimate_addon_set.id %>" class="header-title productAddonSet mb-0"><%= estimate_addon_set.title %></h4>
                                                <div class="addonSetMinMax mb-2">
                                                    <%
                                                        var min_select = '';
                                                        if(estimate_addon_set.min_select > 0){
                                                            min_select = "{{ __('Minimum')}} " + estimate_addon_set.min_select;
                                                        }
                                                        var max_select = '';
                                                        if(estimate_addon_set.max_select > 0){
                                                            max_select = "{{ __('Maximum')}} " + estimate_addon_set.max_select;
                                                        }
                                                        if( (min_select != '') && (max_select != '') ){
                                                            min_select = min_select + " {{ __('and')}} ";
                                                        }
                                                    %>
                                                    <% if( (min_select != '') || (max_select != '') ) { %>
                                                        <small><%=min_select + max_select %> {{ __('Selections Allowed')}}</small>
                                                    <% } %>
                                                </div>

                                                <div class="estimateProductAddonSetOptions" data-min="<%= estimate_addon_set.min_select %>" data-max="<%= estimate_addon_set.max_select %>" data-addonset-title="<%= estimate_addon_set.title %>" >

                                            <% count = _.size(estimate_addon_set.option); %>
                                            <% if(count <= 4){ %>

                                                    <% _.each(estimate_addon_set.option, function(option, key2){ %>
                                                        <div class="checkbox-success d-flex mb-1">
                                                            <label class="pr-2 mb-0 flex-fill font-14" for="inlineCheckbox_<%= key1 %>_<%= key2 %>">
                                                                <%= option.title %>
                                                            </label>
                                                            <div>
                                                                <input type="hidden" id="fake_product_id" name="fake_product_id" value="<%= estimateAddOnData.id %>">

                                                                <input type="checkbox" value="<%= option.id %>" id="inlineCheckbox_<%= key1 %>_<%= key2 %>" class="estimate_product_addon_option" name="addonOptionData[<%= key1 %>][]" addonId="<%= option.estimate_addon_id %>" addonOptId="<%= option.id %>" addonPrice="<%= option.price %>">
                                                            </div>
                                                        </div>
                                                    <% }); %>

                                                <% }else{ %>

                                                    <div class="checkbox-success d-flex mb-1">
                                                        <input type="hidden" id="fake_product_id" name="fake_product_id" value="<%= estimateAddOnData.id %>">
                                                        <select class="pr-2 mb-0 flex-fill font-14 estimate_product_addon_option form-control" name="addonOptionData[<%= key1 %>][]" required>
                                                            <option value="">--Please Select--</option>
                                                            <% _.each(estimate_addon_set.option, function(option, key2){ %>
                                                            <option value="<%= option.id %>"><%= option.title %></option>
                                                            <% }); %>
                                                        </select>
                                                        <div>


                                                <% } %>
                                            </div>
                                        <% }); %>
                                    </div>
                                </div>
                            <% }); %>
                            <div class="estimate_addon_response text-danger font-14 d-none" style="padding:0 16px"></div>
                        </div>
                        <div class="modal-footer flex-nowrap align-items-center">



                            <a class="btn btn-solid add-cart-btn flex-fill add_estimate_addon_product" id="add_estimate_addon_product" href="javascript:void(0)" data-variant_id="2" data-add_to_cart_url="{{ route('addToEstimateCart') }}" data-product_id="<%= estimateAddOnData.id %>">{{ __('Add') }}</a>
                        </div>
                    <% } %>
                </script>

        {{-- <script type="text/template" id="empty_cart_template">
            <div class="row">
                <div class="col-12 text-center pb-3">
                    <img class="w-50 pt-3 pb-1" src="{{ asset('front-assets/images/ic_emptycart.svg') }}" alt="">
                    <h5>Your cart is empty<br/>{{ __('Add an item to begin') }}</h5>
                </div>
            </div>
        </script> --}}

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
                    <button type="button" class="btn btn-solid remove_estimate_product_button" id="remove_estimate_product_button">{{ __('Remove') }}</button>
                </div>
            </div>
        </div>
    </div>



        <div class="modal fade product-addon-modal" id="estimate_product_addon_modal" tabindex="-1"
            aria-labelledby="product_addonLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                </div>
            </div>
        </div>

        <div class="modal fade product-addon-modal-new" id="estimated_product_addon_modal" tabindex="-1"
        aria-labelledby="product_addonLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

            </div>
        </div>
    </div>

    </section>


@endsection
@section('script')
    <script src="{{ asset('front-assets/js/rangeSlider.min.js') }}"></script>
    <script src="{{ asset('front-assets/js/my-sliders.js') }}"></script>
    <script>
        var get_estimate_product_addon_url = "{{ route('estimateProductAddons') }}"

        // jQuery(window).scroll(function() {
        //     var scroll = jQuery(window).scrollTop();
        //     if (scroll >= 900) {
        //         jQuery(".categories-product-list").addClass("fixed-bar");
        //     } else {
        //         jQuery(".categories-product-list").removeClass("fixed-bar");
        //     }
        // });

        var addonids = [];
        var addonoptids = [];
        var showChar = 140;
        var ellipsestext = "...";
        var moretext = "Read more";
        var lesstext = "Read less";
        $('.price_head .member_no span').each(function() {
            var content = $(this).html();
            if (content.length > showChar) {

                var firstContent = content.substr(0, showChar);
                var lastContent = content.substr(showChar, content.length - showChar);

                var html = firstContent + '<span class="moreellipses">' + ellipsestext +
                    '&nbsp;</span><span class="morecontent"><span>' + lastContent +
                    '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';

                $(this).html(html);
            }

        });

        $(".morelink").click(function() {
            if ($(this).hasClass("less")) {
                // $(this).removeClass("less");
                $(this).html(moretext);
            } else {
                $(this).addClass("less");
                $(this).html(lesstext);
            }
            $(this).parent().prev().toggle();
            $(this).prev().toggle();
            return true;
        });

        $(document).delegate(".product_tag_filter", "change", function() {
            vendorProductsSearchResults();
        });

        $(document).on('click', '.add_estimate_addon_product', function() {
            var check = false;

            $(".estimateProductAddonSetOptions").each(function(index) {
                if($(this).find(".estimate_product_addon_option").length){
                    var min_select = $(this).attr("data-min");
                    var max_select = $(this).attr("data-max");
                    var addon_set_title = $(this).attr("data-addonset-title");
                    var addon_set_id = $(this).attr("data-addonset-id");

                    var elementType = $($(this).find(".estimate_product_addon_option"))[0].tagName.toLowerCase();
                    // console.log(($(this).find(".estimate_product_addon_option option:selected").length < min_select), 'condition');
                    // console.log($(this).find(".estimate_product_addon_option option:selected").length, 'length');
                    // console.log(elementType);
                    // return false;

                    if ((min_select > 0) &&
                    (((elementType=='input') && ($(this).find(".estimate_product_addon_option:checked").length < min_select)) ||
                    ((elementType=='select') && ($(this).find(".estimate_product_addon_option option:not(:first-child):selected").length < min_select))) ) {
                                success_error_alert('error', "Minimum " + min_select + " " + addon_set_title + " required", ".estimate_addon_response");
                                check = true;
                                return false;
                    }

                    if ((max_select > 0) &&
                    (((elementType=='input') && ($(this).find(".estimate_product_addon_option:checked").length > max_select)) ||
                    ((elementType=='select') && ($(this).find(".estimate_product_addon_option option:not(:first-child):selected").length > max_select))) ) {
                            success_error_alert('error', "You can select maximum " + max_select + " " + addon_set_title, ".estimate_addon_response");
                            check = true;
                            return false;
                    }
                }

             });

            if(check === false){
                var url = "{{ route('addToEstimateCart') }}";
                var estimate_product_id = $('#fake_product_id').val();
                var quantity = $('.addon-input-number').val();
                var options = [];
                $('input.estimate_product_addon_option:checked').each(function() {
                    options.push($(this).val());
                });
                $('select.estimate_product_addon_option option:selected').each(function() {
                    options.push($(this).val());
                });

                var estimate_option_id = options;
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        "estimate_product_id": estimate_product_id,
                        "quantity": quantity,
                        "estimate_option_id": estimate_option_id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(output) {
                        console.log(output);
                        window.location.reload();
                    },
                    error: function(output) {
                        console.log(output);
                        window.location.reload();
                    },
                });
            }
        });

        $(document).on('click', '.remove_estimate_cart_product', function(e){
                e.stopPropagation();
                var url = "{{ route('removeEstimateCartProduct') }}";
                var estimated_cart_id = $(this).data("estimated_cart_id");
                var product_id = $(this).data('value');
                $('#remove_item_modal').modal('show');
                $(document).on('click','.remove_estimate_product_button', function(e) {
                    $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        "estimated_cart_id": estimated_cart_id,
                        "product_id": product_id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(output) {
                        console.log(output);
                        window.location.reload();
                    },
                    error: function(output) {
                        console.log(output);
                        window.location.reload();
                    },
                });
            });
        });

        $(document).on('click', '.get_estimation_btn', function(e){
            $('.get_estimation_btn_text').text('Estimating...');
            $('#get_estimation_btn_loader').show();
            var estimation_list_url = "{{ route('estimationList') }}";
            $.ajax({
                    type: "GET",
                    url: estimation_list_url,
                    data: {
                        "test": 'test',
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(output) {
                        $("#estimated_product_addon_modal .modal-content").html('');
                        $("#estimated_product_addon_modal .modal-content").append(output.html);
                        $("#estimated_product_addon_modal").modal('show');
                        $('.get_estimation_btn_text').text('Get Estimation');
                        $('#get_estimation_btn_loader').hide();

                        // window.location.reload();
                    },
                    error: function(output) {
                        console.log(output);
                        // window.location.reload();
                    },
            });

        });

        $(document).on('click', '.add_real_cart', function(){
                $('.add_to_real_cart_loader').show();
                var url        = "{{ route('postCartRequestFromEstimation') }}";
                var vendor_id  = $(this).attr("data-vendor_id");
                var product_id = $(this).attr("data-product_id");
                var addon_id   = $(this).attr("data-addonId");
                var option_id  = $(this).attr("data-option_id");
                var quantity   = 1;
                var data = {
                            "vendor_id":       vendor_id,
                            "product_id":      product_id,
                            "quantity":        quantity,
                            "addonID":         addon_id,
                            "addonoptID":      option_id,
                            "from_estimation": true,
                        };
                        // alert('ih');
                        //  console.log(data);
                        //  return false;
                $.ajax({
                    type: "POST",
                    url: url,
                        data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(output) {
                        $('.add_to_real_cart_loader').hide();
                        window.location.href = "{{route('showCart')}}";
                        // window.location.reload();
                    },
                    error: function(output) {
                        $('.add_to_real_cart_loader').hide();
                        // console.log(output);
                        window.location.reload();
                    },
                });
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
        var search_estimated_products_url = "{{ route('searchEstimatedProducts') }}";
        var get_last_added_product_variant_url = "{{ route('getLastAddedProductVariant') }}";
        var get_product_variant_with_different_addons_url = "{{ route('getProductVariantWithDifferentAddons') }}"
        var addonids = [];
        var addonoptids = [];
        var ajaxCall = 'ToCancelPrevReq';

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
            // console.log(variants);
            // console.log(options);
            // return 0;
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
                        $(that).parents('.product_row').find(".variant_response span").html('');
                        if (response.variant != '') {

                            $(that).parents('.product_row').find(".add-cart-btn").attr(
                                'data-variant_id', response.variant.id);

                            $(that).parents('.product_row').find('.product_price').html('');
                            let variant_template = _.template($('#variant_template').html());
                            $(that).parents('.product_row').find('.product_price').append(
                                variant_template({
                                    variant: response.variant
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
            searchEstimatedProducts();
        });

        function searchEstimatedProducts() {
            let keyword = $("#vendor_search_box").val();
            var checkboxesChecked = [];
            $("input:checkbox[name=tag_id]:checked").each(function() {
                checkboxesChecked.push($(this).val());
            });
            var checkedvalus = checkboxesChecked.length > 0 ? checkboxesChecked : null;
            ajaxCall = $.ajax({
                type: "post",
                dataType: 'json',
                url: search_estimated_products_url,
                data: {
                    tag_id: checkedvalus,
                    keyword: keyword,
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
                    }
                }
            });
        }

    </script>
@endsection
