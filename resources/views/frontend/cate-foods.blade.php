@extends('layouts.store', [
'title' => $category->translation_name,
'meta_title'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_title:'',
'meta_keyword'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_keyword:'',
'meta_description'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_description:'',
])
@section('css')
<style type="text/css">
.main-menu .brand-logo {display: inline-block;padding-top: 20px;padding-bottom: 20px;}.slick-track{margin-left: 0px;}
a.banner-img-outer{height:300px;overflow:hidden;display: block;}
</style>
<link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/price-range.css')}}">
@endsection
@section('content')
@if(!empty($category))
@include('frontend.included_files.categories_breadcrumb')
@endif
<section class="section-b-space ratio_asos">
    <div class="collection-wrapper">
        <div class="container">
            <div id="myCarousel" class="carousel slide mb-4" data-ride="carousel">
                @php
                $key = 0;
                @endphp
                <div class="carousel-inner">
                        @if(!empty($category->sub_cat_banners))
                        @foreach($category->sub_cat_banners as $subCatBanners)
                        <div class="carousel-item @if($key == 0) active @endif">
                            <a class="banner-img-outer" href="{{$url??'#'}}">
                                <img alt="" title="" class="blur-up w-100 lazyloaded" src="{{$subCatBanners['proxy_url'] . '1370/300' . $subCatBanners['image_path']}}">
                            </a>
                        </div>
                        @php
                        $key++;
                        @endphp
                        @endforeach
                        @endif
                    <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">{{__('Previous')}}</span>
                    </a>
                    <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">{{__('Next')}}</span>
                    </a>
                </div>
            </div>
            <div class="row mb-5">
                <div class="collection-filter col-lg-3 al">
                @php $show_new_Products = 0; @endphp
                @if($show_new_Products && !empty($newProducts) && count($newProducts) > 0)
                    <div class="theme-card">
                        <h5 class="title-border d-flex align-items-center justify-content-between">
                            <span>{{__('New Product')}}</span>
                            <span class="filter-back d-lg-none d-inline-block">
                                <i class="fa fa-angle-left" aria-hidden="true"></i> {{__('Back')}}
                            </span>
                        </h5>
                        <div class="offer-slider al">
                            
                                @foreach($newProducts as $newProds)
                                    <div class="col-12 p-0">
                                        @foreach($newProds as $new)
                                        <?php $imagePath = '';
                                        foreach ($new['media'] as $k => $v) {
                                            if(!is_null($v['image']))
                                            $imagePath = $v['image']['path']['proxy_url'].'300/300'.$v['image']['path']['image_path'];
                                        } ?>
                                        <div class="common-product-box scale-effect mb-2">
                                            <a class="row  w-100"  href="{{route('productDetail', [$new['vendor']['slug'],$new['url_slug']])}}">
                                                <div class="col-4">
                                                    <div class=" img-outer-box position-relative">
                                                        <img class="blur-up lazyload w-100" data-src="{{$imagePath}}" alt="">
                                                        <div class="pref-timing">
                                                            <!--<span>5-10 min</span>-->
                                                        </div>
                                                        {{--<i class="fa fa-heart-o fav-heart" aria-hidden="true"></i>--}}
                                                    </div>
                                                </div>
                                                <div class="col-8">
                                                    <div class="media-body align-self-center">
                                                        <div class="inner_spacing px-0">
                                                            <div class="product-description">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <h6 class="card_title ellips">{{ $new['translation_title'] }}</h6>
                                                                    <!--<span class="rating-number">2.0</span>-->
                                                                </div>
                                                                <!-- <h3 class="mb-0 mt-2">{{ $new['translation_title'] }}</h3> -->
                                                                <p>{{$new['vendor']['name']}}</p>
                                                                <p class="pb-1">{{__('In')}} {{$new['category_name']}}</p>
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <b>
                                                                        @if($new['inquiry_only'] == 0)
                                                                            <?php $multiply = $new['variant_multiplier']; ?>
                                                                            {{ Session::get('currencySymbol').' '.(decimal_format($new['variant_price'] * $multiply))}}
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

                                    @endforeach
                                    </div>
                                @endforeach
                            
                        </div>
                    </div>
                    @endif
                </div>
                <div class="collection-content col-lg-9">
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
                                            <div class="col-12">
                                                <div class="product-filter-content">
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
                                                @if(!empty($category->childs) && count($category->childs) > 0)
                                                    @foreach($category->childs->toArray() as $cate)
                                                    <div class="col-md-3 col-6 col-grid-box">
                                                        <a href="{{route('categoryDetail', $cate['slug'])}}"  class="product-box scale-effect m-0" onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'>
                                                            <div class="product-image"><img width="100%" alt="" class="blur-up lazyload" data-icon_two="{{isset($cate['icon_two']) && !is_null($cate['icon_two']) ? $cate['icon_two']['image_fit'].'500/500'.$cate['icon_two']['image_path'] : $cate['icon']['image_fit'].'500/500'.$cate['icon']['image_path']}}" data-icon="{{$cate['icon']['image_fit']}}500/500{{$cate['icon']['image_path']}}" data-src="{{$cate['icon']['proxy_url'] . '500/500' . $cate['icon']['image_path']}}" ></div>
                                                            <div class="media-body align-self-center">
                                                                <div class="inner_spacing w-100">
                                                                    <h3 class="d-flex align-items-center justify-content-between">
                                                                        <label class="mb-0">{{$cate['translation_name']}}</label>
                                                                    </h3>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <div class="col-xl-12 col-12 mt-4"><h5 class="text-center">{{__('Details Not Available')}}</h5></div>
                                                @endif
                                            </div>
                                        </div>
                                        @if(count($listData))
                                        <div class="pagination pagination-rounded justify-content-end mb-0">
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
        </div>
    </div>
</section>
@endsection
@section('script')
<script type="text/javascript">
	function changeImage(image2, check) {
       var image = $(image2).children('.product-image').children("img");
       var  icon = image.attr('data-icon');
       var  icon_two = image.attr('data-icon_two');
       if(check == 1)
       {
	        setTimeout(function () {
	            image.attr('data-src',icon_two);
	            image.attr('src',icon_two);
	        },200);
       }else if(check == 0){
            setTimeout(function () {
                image.attr('data-src',icon);
                image.attr('src',icon);
            },200);

       }
    }
</script>
@endsection
