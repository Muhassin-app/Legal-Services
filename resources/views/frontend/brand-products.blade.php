@extends('layouts.store', ['title' => $brand->translation_title ])

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/price-range.css')}}">
@endsection
@php
$additionalPreference = getAdditionalPreference(['is_token_currency_enable','is_service_product_price_from_dispatch','is_service_price_selection']);
$is_service_product_price_from_dispatch_forOnDemand = 0;

$getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
$is_service_product_price_from_dispatch_forOnDemand =$getOnDemandPricingRule['is_price_from_freelancer'] ?? 0;
@endphp
@section('content')
<style type="text/css">
    .main-menu .brand-logo {display: inline-block;padding-top: 20px;padding-bottom: 20px;}.productVariants .firstChild{min-width:150px;text-align:left!important;border-radius:0!important;margin-right:10px;cursor:default;border:none!important}.product-right .color-variant li,.productVariants .otherChild{height:35px;width:35px;border-radius:50%;margin-right:10px;cursor:pointer;border:1px solid #f7f7f7;text-align:center}.productVariants .otherSize{height:auto!important;width:auto!important;border:none!important;border-radius:0}.product-right .size-box ul li.active{background-color:inherit}
</style>
<!-- section start -->
<section class="section-b-space ratio_asos outer-categories">
    <div class="collection-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="top-banner-wrapper text-center">
                            @include('frontend.vendor-category-topbar-banner')
                        <div class="top-banner-content small-section">
                            <h4>{{ $brand->translation_title }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4 homepageSix">
                <div class="collection-filter col-lg-3 main-fillter filter_brand">
                        <div class="collection-filter-block bg-transparent p-0">
                            <div class="collection-mobile-back">
                                <span class="filter-back d-lg-none d-inline-block">
                                    <i class="fa fa-angle-left" aria-hidden="true"></i>{{__('Back')}}
                                </span>
                            </div>

                            @if($products->isNotEmpty())
                             <!--- Left Sidebar filters -->
                                  @include('frontend.category-left-sidebar')
                            <!---End Left Sidebar filters -->
                            @endif
                           
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
                                                <div class="filter-main-btn"><span class="filter-btn btn btn-theme"><i class="fa fa-filter" aria-hidden="true"></i> Filter</span></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 custom_filtter mt-2">
                                            
                                                <div class="product-filter-content">
                                               
                                                </div>
                                            </div>
                                        </div>
                                    </div>
        
                                     <div class="displayProducts">
                                        <div class="product-wrapper-grid">
                                            <div class="row margin-res brand_one">
                                              @if($products->isNotEmpty())
                                                @foreach($products as $key => $data)
                                                <?php /*$imagePath = $imagePath2 = '';
                                                $mediaCount = count($data->media);
                                                for ($i = 0; $i < $mediaCount && $i < 2; $i++) {
                                                    if($i == 0){
                                                        $imagePath = $data->media[$i]->image->path['image_fit'].'300/300'.$data->media[$i]->image->path['image_path'];
                                                    }
                                                    $imagePath2 = $data->media[$i]->image->path['image_fit'].'300/300'.$data->media[$i]->image->path['image_path'];
                                                }*/ ?>
                                                <div class="col-md-3 col-6 col-grid-box mt-3">
                                                        <a href="{{route('productDetail', [$data->vendor->slug,$data->url_slug])}}" target="_blank" class="product-box scale-effect mt-0 product-card-box position-relative al_box_third_template al">
                                                            <div class="product-image">
                                                                <img class="img-fluid blur-up lazyload" data-src="{{$data->image_url}}" alt="">
                                                            </div>
                                                            <div class="media-body align-self-center card-text">
                                                                <div class="inner_spacing w-100">
                                                                @if($dicountPercentage = productDiscountPercentage($data->variant_price, $data->variant_compare_at_price))
                                                                        <span class="flag-discount">{{$dicountPercentage}}% Off</span>
                                                                    @endif
                                                                    <h3 class="d-flex align-items-center justify-content-between text-left">
                                                                        <label class="mb-0 mt-0"><b>{{ $data->translation_title }}</b></label>
                                                                        @if($client_preference_detail)
                                                                            @if($client_preference_detail->rating_check == 1)
                                                                                @if($data->averageRating > 0)
                                                                                    <span class="rating">{{ number_format($data->averageRating, 1, '.', '') }} <i class="fa fa-star text-white p-0"></i></span>
                                                                                @endif
                                                                            @endif
                                                                        @endif
                                                                    </h3>
                                                                    <h6 class="mt-0 mb-1"><b>{{$data->vendor->name}}</b></h6>
                                                                    @if (strlen($data->translation_description) >= 65)
                                                                        <p title="{{$data->translation_description}}">{{ substr($data->translation_description, 0, 64)." ..." }}</p>
                                                                    @else
                                                                        <p>{{ $data->translation_description }}</p>
                                                                    @endif
                                                                    @if($is_service_product_price_from_dispatch_forOnDemand!=1) 
                                                                        @if($data->inquiry_only == 0)
                                                                            @if ($additionalPreference ['is_token_currency_enable'])
                                                                            <h4 class="mt-1"> <i class='fa fa-money' aria-hidden='true'></i> {{(getInToken($data->variant_price * $data->variant_multiplier))}}</h4>
                                                                            @else
                                                                            <h4 class="mt-1">{{Session::get('currencySymbol').' '.(decimal_format($data->variant_price * $data->variant_multiplier))}}</h4>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </a>
                                                </div>
                                                @endforeach
                                              @else
                                                <div class="col-xl-12 col-12 mt-4"><h5 class="text-center">{{ __('No Product Found') }}</h5></div>
                                              @endif
                                            </div>
                                        </div>
                                        <div class="pagination pagination-rounded justify-content-end mb-0">
                                            @if(!empty($products))
                                                {{ $products->links() }}
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
</section>
@endsection
@section('script')
<script src="{{asset('front-assets/js/rangeSlider.min.js')}}"></script>
<script src="{{asset('front-assets/js/my-sliders.js')}}"></script>
<script>
    @if(!empty($brand->image_banner))
    $(document).ready(function() {
        $("body").addClass("homeHeader");
    });
    @endif
    $('.js-range-slider').ionRangeSlider({
        type: 'double',
        grid: false,
        min: "{{$range_products->last() ? $range_products->last()->price : 0}}",
        max: "{{$range_products->first() ? $range_products->first()->price : 1000}}",
        from: "{{$range_products->last() ? $range_products->last()->price : 0}}",
        to: "{{$range_products->first() ? $range_products->first()->price : 1000}}",
        prefix: " "
    });

    var ajaxCall = 'ToCancelPrevReq';
    $('.js-range-slider').change(function(){
        filterProducts();
    });

    $('.productFilter').click(function(){
        filterProducts();
    });
    $(document).on('change','.sortingFilter',function(){
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
        var range = $('.rangeSliderPrice').val();
        var order_type = $('.sortingFilter').val();
        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('brandProductFilters', $brand->id) }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "brands": brands,
                "variants": variants,
                "options": options,
                "range": range,
                "order_type" : order_type,
            },
            beforeSend : function() {
                if(ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
            },
            success: function(response) {
                $('.displayProducts').html(response.html);
            },
            error: function (data) {
                //location.reload();
            },
        });
    }
</script>

@endsection
