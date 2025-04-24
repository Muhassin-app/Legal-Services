@extends('layouts.store', ['title' => 'Celebrity'])
@section('css')
<style type="text/css">
.main-menu .brand-logo {display: inline-block;padding-top: 20px;padding-bottom: 20px;}.slick-track{margin-left: 0px;}
</style>
<link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/price-range.css')}}">
@endsection
@section('content')

<section class="section-b-space ratio_asos">
    <div class="collection-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="top-banner-wrapper text-center">
                       
                        @include('frontend.vendor-category-topbar-banner')   

                        <div class="top-banner-content small-section">
                            <h4>{{ $celebrity->name }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-5 homepageSix">
                <div class="collection-filter col-lg-3 main-fillter">
                    <div class="collection-filter-block bg-transparent p-0">
                        <!-- <div class="collection-mobile-back">
                            <span class="filter-back d-lg-none d-inline-block">
                                <i class="fa fa-angle-left" aria-hidden="true"></i>{{__('Back')}}
                            </span>
                        </div> -->
                         <!--- Left Sidebar filters -->
                         @include('frontend.category-left-sidebar')
                    <!---End Left Sidebar filters -->
                    
                    </div>
                 
                </div>
                <div class="collection-content col-lg-9 outter-fillter-data">
                    <div class="page-main-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="collection-product-wrapper">
                                    <div class="product-top-filter">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="filter-main-btn">
                                                    <span class="filter-btn btn btn-theme">
                                                        <i class="fa fa-filter" aria-hidden="true"></i> {{__('Filter')}}</span>
                                                    </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="product-filter-content border-left">
                                                    <!-- <div class="collection-view">
                                                        <ul>
                                                            <li><i class="fa fa-th grid-layout-view"></i></li>
                                                            <li><i class="fa fa-list-ul list-layout-view"></i></li>
                                                        </ul>
                                                    </div> -->
                                                    {{-- <div class="collection-grid-view">
                                                        <ul>
                                                            <li><img class="blur-up lazyload" data-src="{{asset('front-assets/images/icon/2.png')}}" alt="" class="product-2-layout-view"></li>
                                                            <li><img class="blur-up lazyload" data-src="{{asset('front-assets/images/icon/3.png')}}" alt="" class="product-3-layout-view"></li>
                                                            <li><img class="blur-up lazyload" data-src="{{asset('front-assets/images/icon/4.png')}}" alt="" class="product-4-layout-view"></li>
                                                            <li><img class="blur-up lazyload" data-src="{{asset('front-assets/images/icon/6.png')}}" alt="" class="product-6-layout-view"></li>
                                                        </ul>
                                                    </div> --}}
                                                    {{-- <div class="product-page-per-view">
                                                        <?php $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 8; ?>
                                                        <select class="customerPaginate">
                                                            <option value="8" @if($pagiNate == 8) selected @endif>Show 8
                                                            </option>
                                                            <option value="12" @if($pagiNate == 12) selected @endif>Show 12
                                                            </option>
                                                            <option value="24" @if($pagiNate == 24) selected @endif>Show 24
                                                            </option>
                                                            <option value="48" @if($pagiNate == 48) selected @endif>Show 48
                                                            </option>
                                                        </select>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="displayProducts">
                                        <div class="product-wrapper-grid">
                                            <div class="row margin-res">
                                              @if($celebrity->products->isNotEmpty())
                                                @foreach($celebrity->products as $key => $data)
                                                <?php
                                                /*$imagePath = $imagePath2 = '';
                                                $mediaCount = count($data->media);
                                                for ($i = 0; $i < $mediaCount && $i < 2; $i++) {
                                                    if($i == 0){
                                                        $imagePath = $data->media[$i]->image->path['image_fit'].'300/300'.$data->media[$i]->image->path['image_path'];
                                                    }
                                                    $imagePath2 = $data->media[$i]->image->path['image_fit'].'300/300'.$data->media[$i]->image->path['image_path'];
                                                }*/ ?>
                                                <div class="col-md-3 col-6 col-grid-box mt-3">
                                                    <div class="product-box scale-effect">
                                                        <div class="img-wrapper">
                                                            <div class="front">
                                                                <a href="{{route('productDetail', [$data->vendor->slug,$data->url_slug])}}" target="_blank"><img class="img-fluid blur-up lazyload" data-src="{{$data->image_url}}" alt=""></a>
                                                            </div>
                                                            <div class="cart-info cart-wrap">
                                                                <button data-toggle="modal" data-target="#addtocart" title="Add to cart"><i class="ti-shopping-cart"></i></button>
                                                                <a href="javascript:void(0)" title="Add to Wishlist" class="addWishList" proSku="{{$data->sku}}"><i class="ti-heart" aria-hidden="true"></i></a>
                                                            </div>
                                                        </div>
                                                        <div class="media-body align-self-center">
                                                            <div class="inner_spacing">
                                                                <a href="{{route('productDetail', [$data->vendor->slug,$data->url_slug])}}">
                                                                    <h3>{{ $data->translation_title }}</h3>
                                                                </a>
                                                                <h6 class="mt-0"><b>{{$data->vendor->name}}</b></h6>
                                                                <h4 class="mt-1">{{Session::get('currencySymbol').' '.(decimal_format($data->variant_price * $data->variant_multiplier))}}</h4>
                                                                @if($client_preference_detail)
                                                                    @if($client_preference_detail->rating_check == 1)
                                                                        @if($data->averageRating > 0)
                                                                            <span class="rating">{{ number_format($data->averageRating, 1, '.', '') }} <i class="fa fa-star text-white p-0"></i></span>
                                                                        @endif
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                              @else
                                                <div class="col-xl-12 col-12 mt-4"><h5 class="text-center">{{ __("No Product Found") }}</h5></div>
                                              @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script src="{{asset('front-assets/js/rangeSlider.min.js')}}"></script>
<script src="{{asset('front-assets/js/my-sliders.js')}}"></script>
<script>

    $('.js-range-slider').ionRangeSlider({
        type: 'double',
        grid: false,
        min: 0,
        max: 50000,
        from: 0,
        to: 50000,
        prefix: " "
    });

    var ajaxCall = 'ToCancelPrevReq';
    $('.js-range-slider').change(function(){
        filterProducts();
    });

    $('.productFilter').click(function(){
        filterProducts();
    });

    function filterProducts(){
        var brands = [];
        var variants = [];
        var options = [];
        $('.productFilter').each(function () {
            var that = this;
            if(this.checked == true){
                var forCheck = $(that).attr('used');
                if(forCheck == 'brands'){
                    brands.push($(that).attr('fid'));
                }else{
                    variants.push($(that).attr('fid'));
                    options.push($(that).attr('optid'));
                }
            }
        });

    }

</script>


@endsection
