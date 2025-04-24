@extends('layouts.store', [
    'title' => !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->title : '',
    'meta_title' => !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->meta_title : '',
    'meta_keyword' => !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->meta_keyword : '',
    'meta_description' => !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->meta_description : '',
])

@section('css')
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css"/>
            <link rel="stylesheet" href="{{ asset('front-assets/css/swiper.min.css') }}" />
            <link rel="stylesheet" href="{{ asset('front-assets/css/easyzoom.css') }}" />
            <link rel="stylesheet" href="{{ asset('front-assets/css/main.css') }}" /> -->

    <link rel="stylesheet" href="{{ asset('css/jquery.exzoom.css') }}">
    <style type="text/css">
        /* .main-menu .brand-logo{display:inline-block;padding-top:20px;padding-bottom:20px}.btn-disabled{opacity:.5;pointer-events:none}.fab{font:normal normal normal 14px/1 FontAwesome;font-size:inherit}
            #number{display:block}#exzoom{display:none}.exzoom .exzoom_btn a.exzoom_next_btn{right:-12px} .exzoom .exzoom_nav .exzoom_nav_inner{-webkit-transition:all .5s;-moz-transition:all .5s;transition:all .5s}

            @media screen and (max-width:768px){
                .exzoom .exzoom_zoom_outer{display:none}
                }
            */
        .border-product.al_disc ol,
        .border-product.al_disc ul {
            padding-left: 30px
        }

        .border-product.al_disc ol li,
        .border-product.al_disc ul li {
            display: list-item;
            padding-left: 0;
            padding-top: 8px;
            list-style-type: disc;
            font-size: 14px
        }

        .border-product.al_disc ol li {
            list-style-type: decimal
        }

        .productVariants .firstChild {
            min-width: 150px;
            text-align: left !important;
            border-radius: 0 !important;
            margin-right: 10px;
            cursor: default;
            border: none !important
        }

        .product-right .color-variant li,
        .productVariants .otherChild {
            height: 35px;
            width: 35px;
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer;
            border: 1px solid #f7f7f7;
            text-align: center
        }

        .productVariants .otherSize {
            height: auto !important;
            width: auto !important;
            border: none !important;
            border-radius: 0
        }

        .product-right .size-box ul li.active {
            background-color: inherit
        }

        .img-zoom-lens {
            position: absolute;
            border: 1px solid #d4d4d4;
            width: 150px;
            height: 150px;
            background-color: #fff;
            opacity: .2;
            display: block;
        }

        .img-zoom-result {
            border: 1px solid #d4d4d4;
            width: 100%;
            height: 500px;
            position: absolute;
            top: 0;
            right: -100%;
            z-index: 10;
            display: none;
            background-color: #fff;
        }

        body[dir="rtl"] .img-zoom-result {
            border: 1px solid #d4d4d4;
            width: 100%;
            height: 500px;
            position: absolute;
            top: 0;
            right: auto;
            left: -100%;
            z-index: 10;
            display: none;
        }

        .select2-results__option {
            width: 100%;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')

    @if (!empty($category))
        @include('frontend.included_files.products_breadcrumb')
    @endif
    @php
        $img = '';
    @endphp
    .<section class="section-b-space alSingleProducts">
        <div class="collection-wrapper al">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="text-sm-left">
                            @if (\Session::has('success'))
                                <div class="alert alert-success">
                                    <span>{!! \Session::get('success') !!}</span>
                                </div>
                            @endif
                            @if (\Session::has('error'))
                                <div class="alert alert-danger">
                                    <span>{!! \Session::get('error') !!}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-5 p-0 @php if(count($product->media) == 0){  echo 'd-none'; } @endphp ">
                                    <div class="exzoom hidden w-100">
                                        <div class="exzoom_img_box mb-2">
                                            <ul class='exzoom_img_ul'>
                                                @if (!empty($product->media))
                                                    @foreach ($product->media as $k => $image)
                                                        @php
                                                            if (isset($image->pimage)) {
                                                                $img = $image->pimage->image;
                                                            } else {
                                                                $img = $image->image;
                                                            }
                                                        @endphp
                                                    @endforeach
                                                    @if (!is_null($img))
                                                        <img id="main_image"
                                                            src="{{ @$img->path['image_fit'] . '1000/1000' . @$img->path['image_path'] }}" />
                                                    @endif
                                                @endif
                                            </ul>
                                        </div>
                                        @if (count($product->media) > 1)
                                            <div class="exzoom_nav">
                                                @if (!empty($product->media))
                                                    @foreach ($product->media as $k => $image)
                                                        @php
                                                            if (isset($image->pimage)) {
                                                                $img = $image->pimage->image;
                                                            } else {
                                                                $img = $image->image;
                                                            }
                                                        @endphp
                                                        @if (!is_null($img))
                                                            <span class="">
                                                                <img class="blur-up lazyloaded pro_imgs myimage1"
                                                                    data-src="{{ @$img->path['image_fit'] . '1000/1000' . @$img->path['image_path'] }}"
                                                                    width="60" height="60"
                                                                    src="{{ @$img->path['image_fit'] . '1000/1000' . @$img->path['image_path'] }}">
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                            <p class="exzoom_btn">
                                                <a href="javascript:void(0);" class="exzoom_prev_btn">
                                                    < </a> <a href="javascript:void(0);" class="exzoom_next_btn"> >
                                                        </a>
                                            </p>
                                        @endif
                                    </div>
                                    <div id="myresult" class="img-zoom-result"></div>
                                </div>

                                <div
                                    class="@php if(!empty($product->media) && count($product->media) > 0){ echo 'col-lg-7'; } else { echo 'offset-lg-4 col-lg-7'; } @endphp rtl-text p-0">
                                    <div class="product-right inner_spacing pl-sm-3 p-0">
                                        <h2 class="mb-0">
                                            {{ !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->title : '' }}
                                        </h2>
                                        <h6 class="sold-by">
                                            <b> <img class="blur-up lazyload"
                                                    data-src="{{ $product->vendor->logo['image_fit'] }}200/200{{ $product->vendor->logo['image_path'] }}"
                                                    alt="{{ $product->vendor->Name }}"></b> <a
                                                href="{{ route('vendorDetail', $product->vendor->slug) }}"><b>
                                                    {{ $product->vendor->name }} </b></a>
                                        </h6>
                                        @if ($client_preference_detail)
                                            @if ($client_preference_detail->rating_check == 1)
                                                @if ($product->averageRating > 0)
                                                    <span class="rating">{{ decimal_format($product->averageRating) }} <i
                                                            class="fa fa-star text-white p-0"></i></span>
                                                @endif
                                            @endif
                                        @endif
                                        <div class="description_txt mt-3">
                                            <p>{{ !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->meta_description : '' }}
                                            </p>
                                        </div>
                                        <input type="hidden" name="available_product_variant"
                                            id="available_product_variant" value="{{ $product->variant[0]->id }}">

                                        <div id="product_variant_wrapper">
                                            <input type="hidden" name="variant_id" id="prod_variant_id"
                                                value="{{ $product->variant[0]->id }}">
                                            @if ($product->inquiry_only == 0)
                                                <h3 id="productPriceValue" class="mb-md-3">
                                                    <b class="mr-1">{{ Session::get('currencySymbol') }}<span
                                                            class="product_fixed_price">{{ decimal_format($product->variant[0]->price * $product->variant[0]->multiplier) }}</span></b>
                                                    @if ($product->variant[0]->compare_at_price > 0)
                                                        <span class="org_price">{{ Session::get('currencySymbol') }}<span
                                                                class="product_original_price">{{ decimal_format($product->variant[0]->compare_at_price * $product->variant[0]->multiplier) }}</span></span>
                                                    @endif
                                                </h3>
                                            @endif
                                        </div>

                                        <div class='row service_products'>
                                            <div class="col-lg-2">
                                                <div class="class_img product_image">
                                                    @php
                                                        $img = $LongTermProducts->media->first() && !is_null($LongTermProducts->media->first()->image) ? $LongTermProducts->media->first()->image->path['image_fit'] . '100/100' . $LongTermProducts->media->first()->image->path['image_path'] : '';
                                                    @endphp
                                                    @if ($img)
                                                        <img src="{{ $img }}" alt="{{ $img }}">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-10">
                                                <div class="product">
                                                    <h5 class="mt-0">
                                                        {{ !empty($LongTermProducts->translation) && isset($LongTermProducts->translation[0]) ? $LongTermProducts->translation[0]->title : '' }}

                                                    </h5>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="product-title mt-0">{{ __('No. of Bookings') }}:
                                                            <span class="ml-3">
                                                                {{ !empty($LongTermProducts->long_term_product) ? $LongTermProducts->long_term_product->quantity : '' }}
                                                            </span>
                                                        </h6>

                                                        <div class="hsProductTimingDuration">
                                                            <h6 class="product-title mt-0">
                                                                {{ __('Service Duration') }}:
                                                                <span class="ml-2">
                                                                    {{ $product->service_duration . __(' Months') }}</span>
                                                            </h6>
                                                        </div>
                                                    </div>

                                                </div>
                                                @if ($LongTermProducts->long_term_product->addons->isNotEmpty())
                                                    <div class="border-product p-0">
                                                        <h6 class="product-title">{{ __('Addon') }}</h6>
                                                    </div>

                                                    <div class="row addon-product mb-2">
                                                        @foreach ($LongTermProducts->addOn as $row => $addon)
                                                            @if (array_key_exists($addon->addon_id, $LongTermProducts->product_addon))
                                                                <div
                                                                    class="col-md-12 d-flex justify-content-between align-items-center">
                                                                    <b addon_id="{{ $addon->addon_id }}"
                                                                        class="text-capitalize">{{ $addon->title }}:</b>
                                                                    @if ($addon->setoptions->isNotEmpty())
                                                                        <div class="productAddonSetOptions">
                                                                            <div class=" form-check-inline m-0">
                                                                                {{ $addon->setoptions->where('id', $LongTermProducts->product_addon[$addon->addon_id])->first() ? $addon->setoptions->where('id', $LongTermProducts->product_addon[$addon->addon_id])->first()->title : '' }}</label>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach

                                                    </div>
                                                @endif
                                                <input type="hidden" id="is_long_term_service" value="1">
                                                <div class="col-12 p-0">
                                                    <div class='select_timing row'>
                                                        <div class="hsProductTiming col-sm-4">
                                                            <label class="mt-0">{{ __('Service Time') }}:<br>
                                                            </label>
                                                            <select class="form-control selectize-select"
                                                                id="service_period" name="service_period">
                                                                @foreach (config('constants.Period') as $key => $value)
                                                                    @if (in_array($key, $product->ServicePeriods))
                                                                        <option value="{{ $key }}"
                                                                            {{ $product_in_cart ? ($product_in_cart->service_period == $key ? 'selected' : '') : '' }}>
                                                                            {{ __($value) }}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="service_date_div col-sm-4">
                                                            <label for="">{{ __('Date') }}</label>
                                                            <select class="form-control selectize-select"
                                                                id="service_date" name="date">
                                                                @for ($i = 1; $i <= 28; $i++)
                                                                    <option value="{{ $i }}"
                                                                        {{ $product_in_cart ? ($product_in_cart->service_date == $i ? 'selected' : '') : '' }}>
                                                                        {{ $i }}
                                                                    </option>
                                                                    @if ($i == 28)
                                                                        <option value="0"
                                                                            {{ $product_in_cart ? ($product_in_cart->service_date == 0 ? 'selected' : '') : '' }}>
                                                                            {{ __('Last day of month') }}
                                                                        </option>
                                                                    @endif
                                                                @endfor
                                                            </select>
                                                        </div>
                                                        <div class="service_day_div col-sm-4">
                                                            <label for="">{{ __('Day') }}</label>
                                                            <select class="form-control selectize-select" id="service_day"
                                                                name="day">
                                                                @foreach (config('constants.weekDay') as $dayKey => $day)
                                                                    <option value="{{ $dayKey }}"
                                                                        {{ $product_in_cart ? ($product_in_cart->service_day == $dayKey ? 'selected' : '') : '' }}>
                                                                        {{ __($day) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="service_time_div col-sm-4">
                                                            <label for="">{{ __('Time') }}</label>
                                                            <input type="time" id="service_start_time"
                                                                value="{{ $product_in_cart ? $product_in_cart->$product_in_cart : '' }}"
                                                                class="form-control">
                                                        </div>

                                                    </div>

                                                </div>


                                            </div>
                                            <div class="col-3">
                                                <label for=""></label>
                                                <a href="#" data-toggle="modal" data-target="#addtocart"
                                                    class="btn btn-solid  px-2 mt-3 py-1 w-100 {{ $product_in_cart ? 'btn-disabled' : 'addToCart' }}">{{ $product_in_cart ? __('Added') : __('Add To Cart') }}</a>
                                            </div>

                                        </div>

                                        @if (!empty($product->translation) && isset($product->translation[0]) && $product->translation[0]->body_html != '')
                                            <div class="border-product al_disc">
                                                <h6 class="product-title">{{ __('Service Details') }}</h6>
                                                <p></p>
                                                {!! !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->body_html : '' !!}
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($client_preference_detail && $client_preference_detail->rating_check == 1)
                            <section class="tab-product mb-3">
                                <div class="row">
                                    <div class="col-sm-12 col-lg-12">
                                        <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                                            <!-- <li class="nav-item"><a class="nav-link active" id="top-home-tab" data-toggle="tab" href="#top-home" role="tab" aria-selected="true"><i class="icofont icofont-ui-home"></i>{{ __('Description') }}</a>
                                                <div class="material-border"></div>
                                            </li> -->
                                            <!-- <li class="nav-item"><a class="nav-link" id="profile-top-tab" data-toggle="tab"
                                                    href="#top-profile" role="tab" aria-selected="false"><i
                                                        class="icofont icofont-man-in-glasses"></i>Details</a>
                                                <div class="material-border"></div>
                                            </li> -->
                                            @if ($client_preference_detail && $client_preference_detail->rating_check == 1 && count($rating_details) > 0)
                                                <li class="nav-item "><a class="nav-link active" id="review-top-tab"
                                                        data-toggle="tab" href="#top-review" role="tab"
                                                        aria-selected="false"><i
                                                            class="icofont icofont-contacts"></i>{{ __('Ratings & Reviews') }}</a>
                                                    <div class="material-border"></div>
                                                </li>
                                            @endif

                                            <li class="nav-item ml-3"><a
                                                    class="nav-link {{ count($rating_details) > 0 ? '' : 'active' }}"
                                                    id="compare-product-tab" data-toggle="tab" href="#compare-product"
                                                    role="tab" aria-selected="false"><i
                                                        class="icofont icofont-contacts"></i>{{ __('Compare products') }}</a>
                                                <div class="material-border"></div>
                                            </li>


                                        </ul>
                                        <div class="tab-content nav-material" id="top-tabContent">
                                            <div class="tab-pane fade" id="top-home" role="tabpanel"
                                                aria-labelledby="top-home-tab">
                                                <p>{!! !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->body_html : '' !!}</p>
                                            </div>
                                            <div class="tab-pane fade" id="top-profile" role="tabpanel"
                                                aria-labelledby="profile-top-tab">
                                                <p>{!! !empty($product->translation) && isset($product->translation[0]) ? $product->translation[0]->body_html : '' !!}</p>
                                            </div>
                                            <div class="tab-pane show {{ count($rating_details) > 0 ? 'active' : '' }}"
                                                id="top-review" role="tabpanel" aria-labelledby="review-top-tab">
                                                @forelse ($rating_details as $rating)
                                                    <div v-for="item in list"
                                                        class="w-100 d-flex justify-content-between mb-3">
                                                        <div class="review-box">

                                                            <div class="review-author mb-1">
                                                                <p><strong>{{ $rating->user->name ?? 'NA' }}</strong> - <i
                                                                        class="fa fa-star{{ $rating->rating >= 1 ? '' : '-o' }}"
                                                                        aria-hidden="true"></i>
                                                                    <i class="fa fa-star{{ $rating->rating >= 2 ? '' : '-o' }}"
                                                                        aria-hidden="true"></i>
                                                                    <i class="fa fa-star{{ $rating->rating >= 3 ? '' : '-o' }}"
                                                                        aria-hidden="true"></i>
                                                                    <i class="fa fa-star{{ $rating->rating >= 4 ? '' : '-o' }}"
                                                                        aria-hidden="true"></i>
                                                                    <i class="fa fa-star{{ $rating->rating >= 5 ? '' : '-o' }}"
                                                                        aria-hidden="true"></i>
                                                                </p>
                                                            </div>
                                                            <div class="review-comment">
                                                                <p>{{ $rating->review ?? '' }}</p>
                                                            </div>
                                                            <div class="row review-wrapper">
                                                                @if (isset($rating->reviewFiles))
                                                                    @foreach ($rating->reviewFiles as $files)
                                                                        <a target="_blank"
                                                                            href="{{ $files->file['image_fit'] . '900/900' . $files->file['image_path'] }}"
                                                                            class="col review-photo mt-2 lightBoxGallery"
                                                                            data-gallery="">
                                                                            <img class="blur-up lazyload"
                                                                                data-src="{{ $files->file['image_fit'] . '300/300' . $files->file['image_path'] }}">
                                                                        </a>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                            <div class="review-date mt-2">
                                                                <time>
                                                                    {{ $rating->time_zone_created_at->diffForHumans() }}
                                                                </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p>{{ __('No Result Found') }}</p>
                                                @endforelse
                                            </div>

                                            @include('frontend.compare-product-table')

                                        </div>
                                    </div>
                                </div>
                            </section>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/template" id="variant_image_template">
    <% if(variant.media != '') { %>
        <div class="swiper-container gallery-top">
            <div class="swiper-wrapper">
                <% _.each(variant.media, function(img, key){ %>
                    <div class="swiper-slide easyzoom easyzoom--overlay">
                        <a href="<%= img.pimage.image.path['image_fit'] %>600/600<%= img.pimage.image.path['image_path'] %>">
                        <img class="blur-up lazyload" data-src="<%= img.pimage.image.path['image_fit'] %>600/600<%= img.pimage.image.path['image_path'] %>" alt="">
                        </a>
                    </div>
                <% }); %>
            </div>
            <!-- Add Arrows -->
            <div class="swiper-button-next swiper-button-white"></div>
            <div class="swiper-button-prev swiper-button-white"></div>
        </div>
        <div class="swiper-container gallery-thumbs">
            <div class="swiper-wrapper">
                <% _.each(variant.media, function(img, key){ %>
                    <div class="swiper-slide">
                        <img class="blur-up lazyload" data-src="<%= img.pimage.image.path['image_fit'] %>300/300<%= img.pimage.image.path['image_path'] %>" alt="">
                    </div>
                <% }); %>
            </div>
        </div>
    <% }else{ %>
        <div class="swiper-container gallery-top">
            <div class="swiper-wrapper">
                <% _.each(variant.product.media, function(img, key){ %>
                    <% if(img.image != null) {%>
                        <div class="swiper-slide easyzoom easyzoom--overlay">
                            <a href="<%= img.image.path['image_fit'] %>600/600<%= img.image.path['image_path'] %>">
                            <img class="blur-up lazyload" data-src="<%= img.image.path['image_fit'] %>600/600<%= img.image.path['image_path'] %>" alt="">
                            </a>
                        </div>
                    <% }; %>
                <% }); %>
            </div>
            <!-- Add Arrows -->
            <div class="swiper-button-next swiper-button-white"></div>
            <div class="swiper-button-prev swiper-button-white"></div>
        </div>
        <div class="swiper-container gallery-thumbs">
            <div class="swiper-wrapper">
                <% _.each(variant.product.media, function(img, key){ %>
                    <% if(img.image != null) {%>
                        <div class="swiper-slide">
                            <img class="blur-up lazyload" data-src="<%= img.image.path['image_fit'] %>300/300<%= img.image.path['image_path'] %>" alt="">
                        </div>
                    <% }; %>
                <% }); %>
            </div>
        </div>
    <% } %>
</script>
    <script type="text/template" id="variant_template">
    <input type="hidden" name="variant_id" id="prod_variant_id" value="<%= variant.id %>">
    <% if(variant.product.inquiry_only == 0) { %>
        <h3 id="productPriceValue" class="mb-md-3">
            <b class="mr-1">{{Session::get('currencySymbol')}}<span class="product_fixed_price"><%= Helper.formatPrice(variant.productPrice) %></span></b>
            <% if(variant.compare_at_price > 0 ) { %>
                <span class="org_price">{{Session::get('currencySymbol')}}<span class="product_original_price"><%= Helper.formatPrice(variant.compare_at_price) %></span></span>
            <% } %>
        </h3>
    <% } %>
</script>
    <script type="text/template" id="variant_options_template">
    <% _.each(availableSets, function(type, key){ %>
        <% if(type.variant_detail.type == 1 || type.variant_detail.type == 2) { %>
            <div class="size-box">
                <ul class="productVariants">
                    <li class="firstChild"><%= type.variant_detail.title %></li>
                    <li class="otherSize">
                        <% _.each(type.option_data, function(opt, key){ %>
                        <label class="radio d-inline-block txt-14 mr-2"><%= opt.title %>
                            <input id="lineRadio-<%= opt.id %>" name="var_<%= opt.variant_id %>" vid="<%= opt.variant_id %>" optid="<%= opt.id %>" value="<%= opt.id %>" type="radio" class="changeVariant dataVar<%= opt.variant_id %>">
                            <span class="checkround"></span>
                        </label>
                        <% }); %>
                    </li>
                </ul>
            </div>
        <% } %>
    <% }); %>
</script>
    <script type="text/template" id="variant_quantity_template">
    <% if(variant.product.inquiry_only == 0) { %>
    <div class="product-description border-product pb-0">
        <h6 class="product-title mt-0">{{__('Quantity')}}:
            <% if(variant.product.has_inventory && !(variant.quantity > 0) && (variant.product.sell_when_out_of_stock != 1)){ %>
                <span id="outofstock" style="color: red;">{{__('Out of Stock')}}</span>
            <% }else{ %>
                <input type="hidden" id="instock" value="<%= variant.quantity %>">
            <% } %>
        </h6>
        <% if(!variant.product.has_inventory || (variant.quantity > 0) || (variant.product.sell_when_out_of_stock == 1)){ %>
        <div class="qty-box mb-3">
            <div class="input-group">
                <span class="input-group-prepend">
                    <button type="button" class="btn quantity-left-minus" data-type="minus" data-field="" data-batch_count="<%= variant.product.batch_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>"><i class="ti-angle-left"></i>
                    </button>
                </span>
                <input type="text" onkeypress="return event.charCode > 47 && event.charCode < 58;" pattern="[0-9]{5}" name="quantity" id="quantity" class="form-control input-qty-number quantity_count" value="<%= variant.product.minimum_order_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>">
                <span class="input-group-prepend quant-plus">
                    <button type="button" class="btn quantity-right-plus " data-type="plus" data-field="" data-batch_count="<%= variant.product.batch_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>">
                        <i class="ti-angle-right"></i>
                    </button>
                </span>
            </div>
        </div>
        <% } %>
    </div>
    <% } %>
</script>

    <div class="modal fade product-rating" id="product_rating" tabindex="-1" aria-labelledby="product_ratingLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div id="review-rating-form-modal"></div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="inquiry_form" tabindex="-1" aria-labelledby="inquiry_formLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="inquiry_formLabel">{{ __('Inquiry') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @php
                        $user = Auth::user();
                    @endphp
                    <form id="inquiry-form">
                        <div class="row">
                            <input type="hidden" name="vendor_id" value="{{ $product->vendor_id }}" />
                            <input type="hidden" name="product_id" value="{{ $product->id }}" />
                            <div class="col-md-6 form-group">
                                <label>{{ __('Name') }}</label>
                                <input class="form-control" name="name" id="name"
                                    value="{{ $user ? $user->name : '' }}" type="text"
                                    placeholder="{{ __('Name') }}">
                                <span class="text-danger error-text nameError"></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Email') }}</label>
                                <input class="form-control" name="email" id="email"
                                    value="{{ $user ? $user->email : '' }}" type="text"
                                    placeholder="{{ __('Email') }}">
                                <span class="text-danger error-text emailError"></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Phone Number') }}</label>
                                <input class="form-control" name="number1" id="number1"
                                    value="{{ $user ? $user->phone_number : '' }}" type="text"
                                    placeholder="{{ __('Phone Number') }}" style="display:inline-block;">
                                <span class="text-danger error-text numberError"></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Company Name') }}</label>
                                <input class="form-control" name="company_name" id="company_name" type="text"
                                    placeholder="{{ __('Company Name') }}">
                            </div>
                            <div class="col-12 form-group">
                                <label>{{ __('Message') }}</label>
                                <textarea class="form-control" name="message" id="message" cols="30" rows="8"
                                    placeholder="{{ __('Message') }}"></textarea>
                                <span class="text-danger error-texprapt messageError"></span>
                            </div>
                            <div class="col-12 form-group checkbox-input">
                                <input type="checkbox" id="agree" name="agree" required>
                                <label for="agree">{{ __('I accept the') }} <a
                                        href="{{ url('page/terms-conditions') }}"
                                        target="_blank">{{ __('Terms And Conditions') }}</a>
                                    {{ __('and have read the') }} <a href="{{ url('page/privacy-policy') }}"
                                        target="_blank"> {{ __('Privacy Policy') }}</a></label>
                                <span class="d-block text-danger error-text agreeError"></span>
                            </div>
                            <div class="col-12 mt-2">
                                <button type="button"
                                    class="btn btn-solid w-100 submitInquiryForm">{{ __('Submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js-script')
    <script type="text/javascript" src="{{ asset('front-assets/js/slick.js') }}"></script>
    <script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>
    <script type="text/javascript" src="{{ asset('front-assets/js/jquery.elevatezoom.js') }}"></script>
@endsection
@section('script')
    <script>
        var maximumquantitylert = "{{ __('Quantity is not available in stock') }}";
        var minimumquantitylert = "{{ __('Minimum Quantity count is') }}";
        $(document).on('click', '.submitInquiryForm', function(e) {
            e.preventDefault();
            var formData = new FormData(document.getElementById("inquiry-form"));
            formData.append("variant_id", $('#prod_variant_id').val());
            var submit_url = "{{ route('inquiryMode.store') }}";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "post",
                headers: {
                    Accept: "application/json"
                },
                url: submit_url,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#inquiry_form').modal('hide');
                },
                error: function(response) {
                    // console.log(response);
                    $('.messageError').html(response.responseJSON.errors.message[0]);
                    $('.agreeError').html(response.responseJSON.errors.agree[0]);
                    $('.numberError').html(response.responseJSON.errors.number[0]);
                    $('.emailError').html(response.responseJSON.errors.email[0]);
                    $('.nameError').html(response.responseJSON.errors.name[0]);
                },
                complete: function() {}
            });
        });


        var valueHover = 0;

        function calcSliderPos(e, maxV) {
            return (e.offsetX / e.target.clientWidth) * parseInt(maxV, 10);
        }

        $(".starrate").on("click", function() {
            $(this).data('val', valueHover);
            $(this).addClass('saved')
        });

        $(".starrate").on("mouseout", function() {
            upStars($(this).data('val'));
        });


        $(".starrate span.ctrl").on("mousemove", function(e) {
            var maxV = parseInt($(this).parent("div").data('max'))
            valueHover = Math.ceil(calcSliderPos(e, maxV) * 2) / 2;
            upStars(valueHover);
        });

        $("#service_period").on("change", function() {
            showTimeSelection();
        });


        function upStars(val) {
            var val = parseFloat(val);
            $("#test").html(val.toFixed(1));

            var full = Number.isInteger(val);
            val = parseInt(val);
            var stars = $("#starrate i");

            stars.slice(0, val).attr("class", "fa fa-star");
            if (!full) {
                stars.slice(val, val + 1).attr("class", "fa fa-star-half-o");
                val++
            }
            stars.slice(val, 5).attr("class", "fa fa-star-o");
        }

        $(document).ready(function() {
            showTimeSelection();
            $(".starrate span.ctrl").width($(".starrate span.cont").width());
            $(".starrate span.ctrl").height($(".starrate span.cont").height());
        });

        function showTimeSelection() {
            var selected_period = $('#service_period').val();
            if (selected_period == 'days') {
                $('.service_day_div').hide();
                $('.service_date_div').hide();
            } else if (selected_period == 'week') {
                $('.service_date_div').hide();
                $('.service_day_div').show();
            } else if (selected_period == 'months') {
                $('.service_date_div').show();
                $('.service_day_div').hide();
            } else {
                $('.service_date_div').hide();
                $('.service_day_div').hide();
            }
        }
    </script>

    <script type="text/javascript">
        var ajaxCall = 'ToCancelPrevReq';
        var vendor_id = "{{ $product->vendor_id }}";
        var product_id = "{{ $product->id }}";
        var add_to_cart_url = "{{ route('addToCart') }}";
        $('.changeVariant').click(function() {
            updatePrice();
        });

        function updatePrice() {
            var variants = [];
            var options = [];
            $('.changeVariant').each(function() {
                var that = this;
                if (this.checked == true) {
                    variants.push($(that).attr('vid'));
                    options.push($(that).attr('optid'));
                }
            });
            ajaxCall = $.ajax({
                type: "post",
                dataType: "json",
                url: "{{ route('productVariant', $product->sku) }}",
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
                success: function(resp) {
                    // console.log(resp);
                    if (resp.status == 'Success') {
                        $("#variant_response span").html('');
                        var response = resp.data;
                        if (response.variant != '') {
                            console.log('test');
                            if (vendor_type == 'rental') {
                                // $('.incremental_hrs').val(0);
                                // $('.base_hours_min').val();
                                $('.incremental_hrs').val(0);
                                $('#incremental_hrs_hidden').val(base_hours_min);
                                $('.incremental-left-minus').click();
                                //$('#blocktime, #blocktime2').change();
                            }

                            $('#product_variant_wrapper').html('');
                            let variant_template = _.template($('#variant_template').html());
                            response.variant.productPrice = (parseFloat(checkAddOnPrice()) + parseFloat(response
                                .variant.productPrice)).toFixed(digit_count);
                            console.log(response.variant.productPrice);
                            response.variant.compare_at_price = (parseFloat(checkAddOnPrice()) + parseFloat(
                                response.variant.compare_at_price)).toFixed(digit_count);
                            $("#product_variant_wrapper").append(variant_template({
                                Helper: NumberFormatHelper,
                                variant: response.variant
                            }));
                            $('#product_variant_quantity_wrapper').html('');
                            let variant_quantity_template = _.template($('#variant_quantity_template').html());
                            $("#product_variant_quantity_wrapper").append(variant_quantity_template({
                                variant: response.variant
                            }));
                            // console.log(response.variant.quantity);
                            if (!response.is_available) {
                                $(".addToCart, #addon-table").hide();
                            } else {
                                $(".addToCart, #addon-table").show();
                            }
                            let variant_image_template = _.template($('#variant_image_template').html());
                            $(".product__carousel .gallery-parent").html('');
                            $(".product__carousel .gallery-parent").append(variant_image_template({
                                variant: response.variant
                            }));
                            // easyZoomInitialize();
                            // $('.easyzoom').easyZoom();

                            if (response.variant.media != '') {
                                $(".product-slick").slick({
                                    slidesToShow: 1,
                                    slidesToScroll: 1,
                                    arrows: !0,
                                    fade: !0,
                                    asNavFor: ".slider-nav"
                                });
                                $(".slider-nav").slick({
                                    vertical: !1,
                                    slidesToShow: 3,
                                    slidesToScroll: 1,
                                    asNavFor: ".product-slick",
                                    arrows: !1,
                                    dots: !1,
                                    focusOnSelect: !0
                                });
                            }
                        }
                    } else {
                        $("#variant_response span").html(resp.message);
                        $(".addToCart, #addon-table").hide();
                    }
                },
                error: function(data) {

                },
            });
        }

        function checkAddOnPrice() {
            price = 0;
            $('.productDetailAddonOption').each(function() {
                if ($(this).prop('checked') == true) {
                    var cp = $(this).data('price');
                    price = price + parseFloat(cp);
                }
            });
            return price;
        }
    </script>
    <script>
        var addonids = [];
        var addonoptids = [];
        $(function() {
            $(".productDetailAddonOption").click(function(e) {
                var addon_elem = $(this).closest('tr');
                var addon_minlimit = addon_elem.data('min');
                var addon_maxlimit = addon_elem.data('max');
                if (addon_elem.find(".productDetailAddonOption:checked").length > addon_maxlimit) {
                    this.checked = false;
                } else {
                    var addonId = $(this).attr("addonId");
                    var addonOptId = $(this).attr("addonOptId");
                    if ($(this).is(":checked")) {
                        addonids.push(addonId);
                        addonoptids.push(addonOptId);
                    } else {
                        addonids.splice(addonids.indexOf(addonId), 1);
                        addonoptids.splice(addonoptids.indexOf(addonOptId), 1);
                    }
                    if ($('.changeVariant').length > 0) {
                        updatePrice();
                    } else {
                        addOnPrice = parseFloat(checkAddOnPrice());
                        org_price = parseFloat($(this).data('original_price')) + addOnPrice;
                        fixed_price = parseFloat($(this).data('fixed_price')) + addOnPrice;
                        $('.product_fixed_price').html(fixed_price.toFixed(digit_count));
                        $('.product_original_price').html(org_price.toFixed(digit_count));
                    }
                }
            });
        });
    </script>

    <!-----  rating product if delivered -->

    <script type="text/javascript">
        $(document).ready(function(e) {
            $('.rating-star-click').click(function() {
                $('.rating_files').show();
                $('.form-row').show();
                $('#product_rating').modal('show');
            });
            $('body').on('click', '.add_edit_review', function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                $.get('/rating/get-product-rating?id=' + id, function(markup) {
                    $('#product_rating').modal('show');
                    $('#review-rating-form-modal').html(markup);
                });
            });
        });
    </script>


    <script>
        var timeout = null;

        function imageZoom(imgID, resultID) {
            var img, lens, result, cx, cy;
            img = document.getElementById(imgID);
            result = document.getElementById(resultID);
            /*create lens:*/
            lens = document.createElement("DIV");
            lens.setAttribute("class", "img-zoom-lens");
            /*insert lens:*/
            img.parentElement.insertBefore(lens, img);
            /*calculate the ratio between result DIV and lens:*/
            cx = result.offsetWidth / lens.offsetWidth;
            cy = result.offsetHeight / lens.offsetHeight;
            console.log(cx + 4);
            /*set background properties for the result DIV:*/

            result.style.backgroundImage = "url('" + img.src + "')";
            result.style.backgroundSize = (img.width * cx) + "px " + (img.height * cy) + "px";
            /*execute a function when someone moves the cursor over the image, or the lens:*/
            lens.addEventListener("mousemove", moveLens);
            img.addEventListener("mousemove", moveLens);
            /*and also for touch screens:*/
            lens.addEventListener("touchmove", moveLens);
            img.addEventListener("touchmove", moveLens);

            function moveLens(e) {
                var pos, x, y;
                /*prevent any other actions that may occur when moving over the image:*/
                e.preventDefault();
                /*get the cursor's x and y positions:*/
                pos = getCursorPos(e);
                /*calculate the position of the lens:*/
                x = pos.x - (lens.offsetWidth / 2);
                y = pos.y - (lens.offsetHeight / 2);
                /*prevent the lens from being positioned outside the image:*/
                if (x > img.width - lens.offsetWidth) {
                    x = img.width - lens.offsetWidth;
                }
                if (x < 0) {
                    x = 0;
                }
                if (y > img.height - lens.offsetHeight) {
                    y = img.height - lens.offsetHeight;
                }
                if (y < 0) {
                    y = 0;
                }
                /*set the position of the lens:*/
                lens.style.left = x + "px";
                lens.style.top = y + "px";
                /*display what the lens "sees":*/
                //   console.log(x)
                //   console.log(y)
                result.style.backgroundPosition = "-" + (x * cx) + "px -" + (y * cy) + "px";
            }

            function getCursorPos(e) {
                var a, x = 0,
                    y = 0;
                e = e || window.event;
                /*get the x and y positions of the image:*/
                a = img.getBoundingClientRect();
                /*calculate the cursor's x and y coordinates, relative to the image:*/
                x = e.pageX - a.left;
                y = e.pageY - a.top;
                /*consider any page scrolling:*/
                x = x - window.pageXOffset;
                y = y - window.pageYOffset;
                return {
                    x: x,
                    y: y
                };
            }
        }
        $('#main_image').mouseover(function() {
            var imageId = this.id;
            $('.img-zoom-lens').remove();
            $('.img-zoom-result').show();
            imageZoom(imageId, "myresult");
        });

        $('.myimage1').click(function() {
            var new_image = $(this).attr('src');
            $('#main_image').attr('src', new_image);
        })
        $('.exzoom_img_ul').mouseleave(() => {
            $('.img-zoom-result').hide();
            $('.img-zoom-lens').remove();
        });
    </script>
@endsection
