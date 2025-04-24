@extends('layouts.store', ['title' => $vendor->name])
@section('css')
<style type="text/css">
.main-menu .brand-logo{display:inline-block;padding-top:20px;padding-bottom:20px}.productVariants .firstChild{min-width:150px;text-align:left!important;border-radius:0!important;margin-right:10px;cursor:default;border:none!important}.product-right .color-variant li,.productVariants .otherChild{height:35px;width:35px;border-radius:50%;margin-right:10px;cursor:pointer;border:1px solid #f7f7f7;text-align:center}.productVariants .otherSize{height:auto!important;width:auto!important;border:none!important;border-radius:0}.product-right .size-box ul li.active{background-color:inherit}.product-box .product-detail h4,.product-box .product-info h4{font-size:16px}
.social-icon-list {width: 100%;max-width: 90%;}.social-icon-list .modal-body {text-align: center;}.social-icon-list .modal-body .text-center a img {width: 40px;}
.social-icon-list .modal-body .text-center {display: inline-block;margin: 0px 6px;}

.vendor-page-copy .name_location a.copy-board {padding: 0px 6px;border-radius: 4px;border: 1px dotted#938a8a;background-color: #f8f1f8;}.vendor-page-copy .name_location a.copy-board img {width: 12px;}
.vendor-page-copy .name_location a.copy-board span {font-size: 13px;}
</style>
<link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/price-range.css')}}">
@endsection
@section('content')
@php
$additionalPreference = getAdditionalPreference(['is_token_currency_enable','is_service_product_price_from_dispatch','is_service_price_selection']);
$is_service_product_price_from_dispatch_forOnDemand = 0;

$getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
$is_service_product_price_from_dispatch_forOnDemand =$getOnDemandPricingRule['is_price_from_freelancer'] ?? 0;

@endphp
<!-- get current page -->
@php
$currentPage = $_GET['page']??1;
@endphp

<!-- get current page end -->
<!-- section start -->
<section class="section-b-space ratio_asos al_vendor_product_page">
    <div class="collection-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    @include('frontend.vendor-category-topbar-banner')   
                </div>
                @include('frontend.vendor-details-in-banner')
            </div>

            
            @if(1)
            <div class="row mb-3 homepageSix mt-4">
               
            @if((!empty($variantSets) && count($variantSets) > 0) || ((!empty($brands) && count($brands) > 0)))
            <div class="collection-filter col-md-3 main-fillter">
                    <div class="collection-filter-block mb-3 bg-transparent p-0">
                        <aside class="side_fillter">
                        <div class="collection-mobile-back pt-0 border-0"><span class="filter-back d-lg-none d-inline-block"><i class="fa fa-angle-left" aria-hidden="true"></i>{{__('Back')}}</span></div>
                        @if(!empty($brands) && count($brands) > 0)
                        <div class="collection-collapse-block open mb-2">
                            <h3 class="collapse-block-title">brand</h3>
                            <div class="collection-collapse-block-content pb-0">
                                <div class="collection-brand-filter">
                                    @foreach($brands as $key => $val)
                                    <div class="custom-control custom-checkbox collection-filter-checkbox">
                                        <input type="checkbox" class="custom-control-input productFilter" fid="{{$val->brand_id}}" used="brands" id="brd{{$val->brand_id}}">
                                        @foreach($val->brand->translation as $k => $v)
                                        <label class="custom-control-label" for="brd{{$val->brand_id}}">{{$v->title}}</label>
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($variantSets) && count($variantSets) > 0)
                        @foreach($variantSets as $key => $sets)
                         @php
                            
                            $slug = '';
                            if(!empty($sets->variantDetail) && !empty($sets->variantDetail->varcategory) && !empty($sets->variantDetail->varcategory->cate) && !empty($sets->variantDetail->varcategory->cate->slug)) {
                                $slug = $sets->variantDetail->varcategory->cate->slug;
                            }
                            @endphp
                            @if($slug)
                        <div class="collection-collapse-block border-0 mb-2 open pt-2 border-0">
                           
                            <h3 class="collapse-block-title"> {{$slug . $sets->title}}</h3>
                            <div class="collection-collapse-block-content">
                                <div class="collection-brand-filter">
                                    @if($sets->type == 2)
                                        @foreach($sets->options as $ok => $opt)
                                        <div class="chiller_cb small_label d-inline-block color-selector mt-2">
                                            <?php $checkMark = ($key == 0) ? 'checked' : ''; ?>
                                            <input class="custom-control-input productFilter" type="checkbox" {{$checkMark}} id="Opt{{$key.'-'.$opt->id}}" fid="{{$sets->variant_type_id}}" used="variants" optid="{{$opt->id}}">
                                            <label for="Opt{{$key.'-'.$opt->id}}"></label>
                                            @if(strtoupper($opt->hexacode) == '#FFF' || strtoupper($opt->hexacode) == '#FFFFFF')
                                            <span style="background: #FFFFFF; border-color:#000;" class="check_icon white_check"></span>
                                            @else
                                            <span class="check_icon" style="background:{{$opt->hexacode}}; border-color: {{$opt->hexacode}};"></span>
                                            @endif
                                        </div>
                                        @endforeach
                                    @else
                                        @foreach($sets->options as $ok => $opt)
                                        <div class="custom-control custom-checkbox collection-filter-checkbox">
                                            <input type="checkbox" class="custom-control-input productFilter" id="Opt{{$key.'-'.$opt->id}}" fid="{{$sets->variant_type_id}}" type="variants" optid="{{$opt->id}}">
                                            <label class="custom-control-label" for="Opt{{$key.'-'.$opt->id}}">{{$opt->title}}</label>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                         @endif
                        @endforeach
                        @endif
                        @if($show_range == 1)
                        <div class="collection-collapse-block border-0 mb-2 open">
                            <h3 class="collapse-block-title">{{__('Price')}}</h3>
                            <div class="collection-collapse-block-content">
                                <div class="wrapper mt-3">
                                    <div class="range-slider">
                                        <input type="text" class="js-range-slider rangeSliderPrice" value="" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </aside>
                </div>
            {{-- </div> --}}
            @endif

                    @php $show_new_Products = 0; @endphp
                    @if($show_new_Products && !empty($newProducts) && count($newProducts) > 0)
                    <div class="theme-card custom-inner-card">
                        <h5 class="title-border d-flex align-items-center justify-content-between">
                            <span>{{__('New Product')}}</span>
                            <!-- <span class="filter-back d-lg-none d-inline-block">
                                <i class="fa fa-angle-left" aria-hidden="true"></i> {{__('Back')}}
                            </span> -->
                        </h5>
                            <div class="offer-slider al">

                                @foreach($newProducts as $newProds)

                                    @foreach($newProds as $new)
                                    <div class="col-md-12 p-0">
                                    <?php /*$imagePath = '';
                                    foreach ($new['media'] as $k => $v) {
                                        $imagePath = $v['image']['path']['image_fit'] . '300/300' . $v['image']['path']['image_path'];
                                    }*/ ?>

                                    <div class="common-product-box scale-effect mb-2">
                                        <a class="row w-100" href="{{route('productDetail', [$new['vendor']['slug'],$new['url_slug']])}}">
                                            <div class="col-4">
                                                <div class="img-outer-box position-relative">
                                                        <img class="blur-up lazyload" data-src="{{$new['image_url']}}" alt="">
                                                        <div class="pref-timing">
                                                            <!--<span>5-10 min</span>-->
                                                        </div>
                                                        {{-- <i class="fa fa-heart-o fav-heart" aria-hidden="true"></i> --}}
                                                    </div>
                                            </div>
                                            <div class="col-8">
                                                <div class="media-body align-self-center ">
                                                    <div class="inner_spacing px-0">
                                                        <div class="product-description">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <h6 class="card_title ellips">{{ $new['translation_title'] }}</h6>
                                                                <!--<span class="rating-number">2.0</span>-->
                                                            </div>
                                                            <!-- <h3 class="m-0">{{ $new['translation_title'] }}</h3> -->
                                                            <p>{{$new['vendor']['name']}}</p>
                                                            <p class="pb-1">In {{$new['category_name']}}</p>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <b>
                                                                    @if($is_service_product_price_from_dispatch_forOnDemand !=1)
                                                                        @if($new['inquiry_only'] == 0)
                                                                            <?php $multiply = $new['variant_multiplier']; ?>
                                                                            {{$additionalPreference ['is_token_currency_enable'] ? getInToken(decimal_format($new['variant_price'] * $multiply)) : Session::get('currencySymbol').' '.(decimal_format($new['variant_price'] * $multiply))}}
                                                                        @endif
                                                                    @endif
                                                                </b>

                                                                <!-- @if($client_preference_detail)
                                                                    @if($client_preference_detail->rating_check == 1)
                                                                        @if($new['averageRating'] > 0)
                                                                            <div class="rating-box">
                                                                                <i class="fa fa-star" aria-hidden="true"></i>
                                                                                <span>{{ $new['averageRating'] }}</span>
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                @endif   -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            @endforeach

                        </div>
                    </div>
                    @endif
                    <!-- side-bar banner end here -->
                </div>
                <div class="collection-content col-lg-9 outter-fillter-data">
                    <div class="page-main-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="collection-product-wrapper">
                                    <div class="product-top-filter">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="filter-main-btn"><span class="filter-btn btn btn-theme"><i class="fa fa-filter" aria-hidden="true"></i>{{__('Filter')}}</span></div>
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
                                                    {{--<div class="product-page-per-view">
                                                        <?php $pnum = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 8; ?>
                                                        <select class="customerPaginate">
                                                            <option value="8" @if($pnum==8) selected @endif>Show 8
                                                            </option>
                                                            <option value="12" @if($pnum==12) selected @endif>Show 12 </option>
                                                            <option value="24" @if($pnum==24) selected @endif>Show 24
                                                            </option>
                                                            <option value="48" @if($pnum==48) selected @endif>Show 48
                                                            </option>
                                                        </select>
                                                    </div>--}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="displayProducts px-0">
                                        <div class="col-12 custom_filtter mt-2 d-none">
                                            <select name="order_type" id='order_type' class="sortingFilter p-1">
                                                <option value="">{{__('Sort By')}}</option>
                                                <option value="newly_added" {{isset($input['order_type']) && $input['order_type'] == "newly_added" ? 'selected' : ''}}>{{__('Newest Arrivals')}}</option>
                                                <option value="featured" {{isset($input['order_type']) && $input['order_type'] == "featured" ? 'selected' : ''}}>{{__('Featured')}}</option>
                                                <option value="a_to_z" {{isset($input['order_type']) && $input['order_type'] == "a_to_z" ? 'selected' : ''}}>{{__('A to Z')}}</option>
                                                <option value="z_to_a" {{isset($input['order_type']) && $input['order_type'] == "z_to_a" ? 'selected' : ''}}>{{__('Z to A')}}</option>
                                                <option value="low_to_high" {{isset($input['order_type']) && $input['order_type'] == "low_to_high" ? 'selected' : ''}}>{{__('Cost : Low to High')}}</option>
                                                <option value="high_to_low" {{isset($input['order_type']) && $input['order_type'] == "high_to_low" ? 'selected' : ''}}>{{__('Cost : High to Low')}}</option>
                                                <option value="rating" {{isset($input['order_type']) && $input['order_type'] == "rating" ? 'selected' : ''}}>{{__('Avg. Customer Review')}}</option>
                                                

                                            </select>
                                            <!-- <ul>
                                                <li><span>Sort By:</span></li>
                                                <li><a href="javascript:void(0)" class="active">Featured</a></li>
                                                <li><a href="javascript:void(0)">A to Z</a></li>
                                                <li><a href="javascript:void(0)">Z to A</a></li>
                                                <li><a href="javascript:void(0)">Cost : Low to High</a></li>
                                                <li><a href="javascript:void(0)">Cost : High to Low</a></li>
                                                <li><a href="javascript:void(0)">Avg. Customer Review</a></li>
                                                <li><a href="javascript:void(0)">Newest Arrivals</a></li>
                                            </ul> -->
                                        </div>
                                        <div class="product-wrapper-grid alVender">
                                            <div class="row margin-res">
                                                @if($listData->isNotEmpty())
                                                    @foreach($listData as $key => $data)
                                                    <?php /*$imagePath = $imagePath2 = '';
                                                    $mediaCount = count($data->media);
                                                    for ($i = 0; $i < $mediaCount && $i < 2; $i++) {
                                                        if ($i == 0) {
                                                            $imagePath = $data->media[$i]->image->path['image_fit'] . '600/600' . $data->media[$i]->image->path['image_path'];
                                                        }
                                                        $imagePath2 = $data->media[$i]->image->path['image_fit'] . '600/600' . $data->media[$i]->image->path['image_path'];
                                                    }*/ ?>
                                                    <div class="col-md-3 col-6 col-grid-box mt-4">
                                                        <a href="{{route('productDetail', [$data->vendor->slug,$data->url_slug])}}" target="_blank" class="common-product-box scale-effect mt-0">
                                                            <div class="img-outer-box position-relative">
                                                                <img class="img-fluid blur-up lazyload" data-src="{{$data->image_url}}" alt="">
                                                                <div class="pref-timing">
                                                                    <!--<span>5-10 min</span>-->
                                                                </div>
                                                                {{-- <i class="fa fa-heart-o fav-heart" aria-hidden="true"></i> --}}
                                                            </div>
                                                            <div class="media-body align-self-center">
                                                                <div class="inner_spacing w-100">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <h6 class="card_title mb-1 ellips">{{ $data->translation_title }}</h6>
                                                                        @if($client_preference_detail)
                                                                            @if($client_preference_detail->rating_check == 1)
                                                                                @if($data->averageRating > 0)
                                                                                    <span class="rating-number"><i class="fa fa-star"></i> {{ number_format($data->averageRating, 1, '.', '') }}</span>
                                                                                @endif
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                    <!-- <h3>{{ $data->translation_title }}</h3> -->
                                                                    <p>{{$data->description}}</p>
                                                                    <p class="border-bottom pb-1 mb-1">In {{$data->category_name}}</p>


                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        @if($is_service_product_price_from_dispatch_forOnDemand !=1)
                                                                            @if($data['inquiry_only'] == 0)
                                                                                <h4 class="mt-0">{{$additionalPreference['is_token_currency_enable'] ? getInToken(decimal_format($data->variant_price * $data->variant_multiplier)) : Session::get('currencySymbol').(decimal_format($data->variant_price * $data->variant_multiplier))}}</h4>
                                                                            @endif
                                                                        @endif
                                                                      <!--   @if($client_preference_detail)
                                                                            @if($client_preference_detail->rating_check == 1)
                                                                                @if($data->averageRating > 0)
                                                                                    <span class="rating">{{ number_format($data->averageRating, 1, '.', '') }} <i class="fa fa-star text-white p-0"></i></span>
                                                                                @endif
                                                                            @endif
                                                                        @endif -->
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <div class="col-xl-12 col-12"><h5 class="text-center">{{__('No Product Found')}}</h5></div>
                                                @endif
                                            </div>
                                        </div>
                                        @if(count($listData))
                                        <div class="pagination pagination-rounded justify-content-end mb-0 mt-4">
                                            {{ $listData->links() }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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
</section>
@endsection
@section('script')
<script src="{{asset('front-assets/js/rangeSlider.min.js')}}"></script>
<script src="{{asset('front-assets/js/my-sliders.js')}}"></script>
<script>
    @if(!empty($vendor->banner))
    $(document).ready(function() {
        $("body").addClass("homeHeader");
    });
    @endif
    $('.js-range-slider').ionRangeSlider({
        type: 'double',
        grid: false,
        min: "{{floor($range_products->last() ? $range_products->last()->price * (!empty(Session::get('currencyMultiplier'))?Session::get('currencyMultiplier'):1) : 0)}}",
        max: "{{ceil($range_products->first() ? $range_products->first()->price * (!empty(Session::get('currencyMultiplier'))?Session::get('currencyMultiplier'):1) : 1000)}}",
        from: "{{floor($range_products->last() ? $range_products->last()->price * (!empty(Session::get('currencyMultiplier'))?Session::get('currencyMultiplier'):1) : 0)}}",
        to: "{{ceil($range_products->first() ? $range_products->first()->price * (!empty(Session::get('currencyMultiplier'))?Session::get('currencyMultiplier'):1) : 1000)}}",
        prefix: ""
    });

    var ajaxCall = 'ToCancelPrevReq';
    $('.js-range-slider').change(function() {
        filterProducts();
    });
    $('.productFilter').click(function() {
        filterProducts();
    });
    $(document).on('change','.sortingFilter',function(){
        filterProducts();
    });

    $(document).on('click', '.open-social-medialinks', function(e) {
        $('#social-media-links-modal').modal({
            backdrop: 'static',
            keyboard: false
        });
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

    function filterProducts() {
        var brands = [];
        var variants = [];
        var options = [];
        $('.productFilter').each(function() {
            var that = this;
            if (this.checked == true) {
                var forCheck = $(that).attr('used');
                if (forCheck == 'brands') {
                    brands.push($(that).attr('fid'));
                } else {
                    variants.push($(that).attr('fid'));
                    options.push($(that).attr('optid'));
                }
            }
        });
        var range = $('.rangeSliderPrice').val();
        var order_type = $('.sortingFilter').val();

        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('vendorDetail', $vendor->slug) }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "brands": brands,
                "variants": variants,
                "options": options,
                "range": range,
                "order_type" : order_type
            },
            beforeSend: function() {
                if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
            },
            success: function(response) {
                $('.displayProducts').html(response.html);
            },
            error: function(data) {
                //location.reload();
            },
        });
    }

    $(document).ready(function(){
        $('.sortingFilter').val('newly_added');
       // filterProducts();
        });


// $(document).ready(function(){
//         let currentPage = '{{$_GET["page"]??"1"}}';
//         if(currentPage){
//             $('.page-link').each(function(){
//                 if($(this).text()==currentPage){
//                     $(this).prev().addClass('active');
//                     break;
//                 }
//             })
//         }
// })
</script>



@endsection
