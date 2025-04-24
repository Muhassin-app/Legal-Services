@extends('layouts.store', [
'title' => $category->translation_name,
'meta_title'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_title:'',
'meta_keyword'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_keyword:'',
'meta_description'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_description:'',
])

@section('css')
<style type="text/css">
.main-menu .brand-logo {display: inline-block;padding-top: 20px;padding-bottom: 20px;}.slick-track{margin-left: 0px;}
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
            <div class="row">
                <div class="col-12">
                    <div class="top-banner-wrapper text-center">
                    @include('frontend.vendor-category-topbar-banner')   
                       

                        <div class="top-banner-content small-section">
                            <h4>{{ isset($category->translation_one)?$category->translation_one->name:$category->slug }}</h4>
                            <!-- deleted code -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-sm-5 homepageSix vendor_onetem">
                <div class="collection-filter col-lg-3 al d-none">
                @if( !empty($newProducts) && count($newProducts) > 0)
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
                                            <a class="row  w-100" href="{{route('productDetail', [$new['vendor']['slug'],$new['url_slug']])}}">
                                                <div class="col-4">
                                                    <div class="img-outer-box position-relative  p-0">
                                                        <img  class="blur-up lazyload w-100 p-0" data-src="{{$imagePath}}" alt="">
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
                    <!-- side-bar banner end here -->
                    @endif
                </div>
                <div class="collection-content col-lg-12">
                    <div class="page-main-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="collection-product-wrapper">
                                    <div class="product-top-filter mb-0">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="filter-main-btn">
                                                    <span class="filter-btn btn btn-theme">
                                                        </i> {{__('New Product')}} >
                                                    </span>
                                                </div>
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
                                    <div class="displayProducts px-0">

                                        <div class="product-wrapper-grid">
                                            <div class="row margin-res">
                                            @if($listData->isNotEmpty())
                                                @foreach($listData as $key => $data)
                                                    @php
                                                    $imagePath = $data->logo['proxy_url'] .'300/300'. $data->logo['image_path'];
                                                    $imagePath2 = $data->banner['proxy_url'] .'300/300'. $data->banner['image_path'];
                                                    if(empty($data->vendor_templete_id) || ($data->vendor_templete_id == 1)){
                                                        $vendor_url = route('categoryVendorProducts', [$category->slug, $data->slug]);
                                                    }elseif($data->vendor_templete_id == 5){
                                                        if(isset($data->slug) && isset($category->slug))
                                                        $vendor_url = route('vendorCategoryProducts', [$data->slug, $category->slug]);
                                                    }else{
                                                        $vendor_url = route('vendorDetail', $data->slug);
                                                    }
                                                    @endphp
                                                    <div class="col-xl-3 col-lg-3 col-6 col-grid-box mt-sm-3 mt-1" >
                                                        <div class="product-card-box position-relative alInnerBox">
                                                            <div class="add-to-fav">
                                                                <input id="fav_pro_one" type="checkbox">
                                                                {{-- <label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label> --}}
                                                            </div>
                                                            <a class="suppliers-box d-block" href="{{$vendor_url}}">
                                                                <div class="suppliers-img-outer">
                                                                    <img class="w-100 img-fluid mx-auto blur-up lazyload" data-src="{{$imagePath}}" alt="">
                                                                </div>
                                                                <div class="supplier-rating">
                                                                    <h6 class="mb-0 ellips">{{$data->name}}</h6>
                                                                    @if($client_preference_detail && $client_preference_detail->rating_check == 1 && $data->vendorRating > 0)
                                                                    <span class="rating-number">{{$data->vendorRating}} <i class="fa fa-star"></i> </span>
                                                                    <!-- <span class="Stars" style="--rating: {{$data->vendorRating}};" aria-label="Rating of this product is {{$data->vendorRating}} out of 5."></span> -->
                                                                    @endif
                                                                    <p title="{{$data->categoriesList}}" class="vendor-cate {{ (($client_preference_detail->rating_check ==1) || ($data->is_show_vendor_details == 1) ) ? 'border-bottom' : '' }} pb-0 mb-1 ellips">{{$data->categoriesList}}</p>
                                                                    <!-- <h6 class="mb-1">{{$data->name}}</h6> -->
                                                                    <div class="product-timing">
                                                                        @if ($data->is_show_vendor_details == 1)
                                                                        <small title="{{$data->address}}" class="ellips d-block"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         width="20px" height="20px" viewBox="0 0 368.666 368.666" style="enable-background:new 0 0 368.666 368.666;" xml:space="preserve">
                        <g id="XMLID_2_">
                            <g>
                                <g>
                                    <path d="M184.333,0C102.01,0,35.036,66.974,35.036,149.297c0,33.969,11.132,65.96,32.193,92.515
                                        c27.27,34.383,106.572,116.021,109.934,119.479l7.169,7.375l7.17-7.374c3.364-3.46,82.69-85.116,109.964-119.51
                                        c21.042-26.534,32.164-58.514,32.164-92.485C333.63,66.974,266.656,0,184.333,0z M285.795,229.355
                                        c-21.956,27.687-80.92,89.278-101.462,110.581c-20.54-21.302-79.483-82.875-101.434-110.552
                                        c-18.228-22.984-27.863-50.677-27.863-80.087C55.036,78.002,113.038,20,184.333,20c71.294,0,129.297,58.002,129.296,129.297
                                        C313.629,178.709,304.004,206.393,285.795,229.355z"/>
                                    <path d="M184.333,59.265c-48.73,0-88.374,39.644-88.374,88.374c0,48.73,39.645,88.374,88.374,88.374s88.374-39.645,88.374-88.374
                                        S233.063,59.265,184.333,59.265z M184.333,216.013c-37.702,0-68.374-30.673-68.374-68.374c0-37.702,30.673-68.374,68.374-68.374
                                        s68.373,30.673,68.374,68.374C252.707,185.341,222.035,216.013,184.333,216.013z"/>
                                </g>
                            </g>
                        </g>
                        </svg> {{$data->address}}</small>
                                                                        @endif
                                                                        @if(isset($data->timeofLineOfSightDistance))
                                                                            <ul class="timing-box mb-1">
                                                                                <li>
                                                                                    <small class="d-block">
                           <?xml version="1.0" encoding="iso-8859-1"?><svg style="height:16px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 368.666 368.666" style="enable-background:new 0 0 368.666 368.666;" xml:space="preserve"><g id="XMLID_2_"><g><g><path d="M184.333,0C102.01,0,35.036,66.974,35.036,149.297c0,33.969,11.132,65.96,32.193,92.515c27.27,34.383,106.572,116.021,109.934,119.479l7.169,7.375l7.17-7.374c3.364-3.46,82.69-85.116,109.964-119.51c21.042-26.534,32.164-58.514,32.164-92.485C333.63,66.974,266.656,0,184.333,0z M285.795,229.355c-21.956,27.687-80.92,89.278-101.462,110.581c-20.54-21.302-79.483-82.875-101.434-110.552c-18.228-22.984-27.863-50.677-27.863-80.087C55.036,78.002,113.038,20,184.333,20c71.294,0,129.297,58.002,129.296,129.297C313.629,178.709,304.004,206.393,285.795,229.355z"/><path d="M184.333,59.265c-48.73,0-88.374,39.644-88.374,88.374c0,48.73,39.645,88.374,88.374,88.374s88.374-39.645,88.374-88.374S233.063,59.265,184.333,59.265z M184.333,216.013c-37.702,0-68.374-30.673-68.374-68.374c0-37.702,30.673-68.374,68.374-68.374s68.373,30.673,68.374,68.374C252.707,185.341,222.035,216.013,184.333,216.013z"/></g></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></span> {{$data->lineOfSightDistance}}</small>
                                                                                </li>
                                                                                <li>
                                                                                    <small class="d-block mx-1"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                    width="16px" height="16px" viewBox="0 0 594.258 594.258" style="enable-background:new 0 0 594.258 594.258;"
                                    xml:space="preserve">
                                <g>
                                    <g>
                                        <path d="M506.877,87.381c-27.229-27.228-58.945-48.611-94.273-63.553C376.006,8.35,337.154,0.5,297.128,0.5
                                            c-40.025,0-78.877,7.85-115.475,23.329c-35.328,14.942-67.046,36.325-94.274,63.553c-27.228,27.228-48.61,58.946-63.552,94.273
                                            C8.349,218.252,0.5,257.103,0.5,297.129c0,40.025,7.849,78.877,23.328,115.475c14.942,35.328,36.325,67.047,63.553,94.273
                                            c27.228,27.229,58.946,48.611,94.273,63.553c36.598,15.48,75.449,23.328,115.475,23.328c40.025,0,78.877-7.848,115.475-23.328
                                            c35.328-14.941,67.047-36.324,94.273-63.553c27.229-27.227,48.611-58.945,63.553-94.273
                                            c15.48-36.598,23.328-75.449,23.328-115.475c0-40.026-7.848-78.877-23.328-115.475
                                            C555.486,146.328,534.105,114.609,506.877,87.381z M297.128,550.918c-140.163,0-253.789-113.625-253.789-253.789
                                            c0-140.164,113.626-253.789,253.789-253.789c140.163,0,253.79,113.626,253.79,253.789
                                            C550.918,437.293,437.291,550.918,297.128,550.918z"/>
                                        <path d="M297.129,594.258c-40.095,0-79.012-7.862-115.669-23.367c-35.386-14.967-67.158-36.385-94.432-63.66
                                            c-27.274-27.273-48.693-59.045-63.66-94.433C7.862,376.139,0,337.222,0,297.129c0-40.093,7.862-79.01,23.368-115.669
                                            c14.967-35.386,36.385-67.157,63.659-94.432c27.274-27.274,59.046-48.692,94.433-63.66C218.121,7.862,257.037,0,297.128,0
                                            s79.008,7.862,115.669,23.368c35.385,14.965,67.156,36.384,94.433,63.66c27.271,27.272,48.69,59.043,63.66,94.432
                                            c15.505,36.657,23.367,75.574,23.367,115.669c0,40.096-7.862,79.012-23.367,115.669c-14.967,35.388-36.385,67.159-63.66,94.433
                                            c-27.273,27.275-59.045,48.693-94.433,63.66C376.141,586.396,337.225,594.258,297.129,594.258z M297.128,1
                                            c-39.957,0-78.743,7.835-115.28,23.289c-35.268,14.917-66.933,36.263-94.115,63.446c-27.183,27.183-48.528,58.848-63.445,94.114
                                            C8.835,218.385,1,257.17,1,297.129c0,39.959,7.835,78.744,23.289,115.28c14.917,35.268,36.263,66.933,63.446,94.114
                                            c27.183,27.184,58.848,48.53,94.115,63.445c36.533,15.454,75.319,23.289,115.28,23.289s78.746-7.835,115.28-23.289
                                            c35.268-14.916,66.933-36.262,94.114-63.445c27.184-27.182,48.529-58.847,63.445-94.114
                                            c15.454-36.534,23.289-75.319,23.289-115.28s-7.835-78.747-23.289-115.28c-14.919-35.27-36.265-66.935-63.445-94.115
                                            c-27.185-27.185-58.85-48.531-94.114-63.446C375.871,8.835,337.085,1,297.128,1z M297.128,551.418
                                            c-67.923,0-131.78-26.45-179.809-74.479c-48.029-48.029-74.48-111.887-74.48-179.81s26.451-131.78,74.48-179.81
                                            C165.348,69.291,229.206,42.84,297.128,42.84c67.922,0,131.78,26.451,179.809,74.48c48.029,48.029,74.48,111.886,74.48,179.809
                                            s-26.451,131.78-74.48,179.81S365.051,551.418,297.128,551.418z M297.128,43.84c-67.656,0-131.262,26.347-179.102,74.187
                                            c-47.84,47.84-74.187,111.447-74.187,179.103c0,67.656,26.347,131.263,74.187,179.103c47.84,47.84,111.446,74.187,179.102,74.187
                                            s131.262-26.347,179.102-74.187c47.841-47.84,74.188-111.446,74.188-179.103c0-67.656-26.347-131.262-74.188-179.102
                                            C428.391,70.187,364.784,43.84,297.128,43.84z"/>
                                    </g>
                                    <g>
                                        <path d="M333.848,275.709c-8.436,0-15.299-6.863-15.299-15.3v-85.156c0-11.83-9.59-21.42-21.42-21.42
                                            c-11.83,0-21.42,9.59-21.42,21.42v85.156c0,32.058,26.081,58.14,58.139,58.14h129.328c11.83,0,21.42-9.59,21.42-21.42
                                            c0-11.83-9.59-21.42-21.42-21.42H333.848z"/>
                                        <path d="M463.176,319.049H333.848c-32.334,0-58.639-26.306-58.639-58.64v-85.156c0-12.087,9.833-21.92,21.92-21.92
                                            c12.087,0,21.92,9.833,21.92,21.92v85.156c0,8.161,6.639,14.8,14.799,14.8h129.328c12.087,0,21.92,9.833,21.92,21.92
                                            S475.263,319.049,463.176,319.049z M297.128,154.333c-11.535,0-20.92,9.385-20.92,20.92v85.156
                                            c0,31.783,25.857,57.64,57.639,57.64h129.328c11.535,0,20.92-9.385,20.92-20.92s-9.385-20.92-20.92-20.92H333.848
                                            c-8.712,0-15.799-7.088-15.799-15.8v-85.156C318.049,163.718,308.664,154.333,297.128,154.333z"/>
                                    </g>
                                </g>
                                </svg></span> {{$data->timeofLineOfSightDistance}}</small>
                                                                                </li>
                                                                            </ul>
                                    @if($client_preference_detail->max_safety_mod == 1)
                                    <div class="mt-2">
                                        <ul class="timing-box_al">
                                        <li><img height="30px" src="{{asset('images/max-safety.png')}}" alt=""></li>
                                        <li>Follows all Max Safety measures to ensure your food is safe</li>
                                        </ul>
                                    </div>
                                    @endif
                                                                        @endif
                                                                    </div>
                                                                   {{-- @if($client_preference_detail)
                                                                        @if($client_preference_detail->rating_check == 1)
                                                                            @if($data->vendorRating > 0)
                                                                                <ul class="custom-rating m-0 p-0">
                                                                                    <?php
                                                                                    for($i=0; $i < 5; $i++){
                                                                                        if($i <= $data->vendorRating){
                                                                                            $starFillClass = 'fa-star';
                                                                                        }else{
                                                                                            $starFillClass = 'fa-star-o';
                                                                                        }
                                                                                    ?>
                                                                                    <li><i class="fa {{ $starFillClass }}" aria-hidden="true"></i></li>
                                                                                    <?php } ?>
                                                                                </ul>
                                                                            @endif
                                                                        @endif
                                                                    @endif--}}
                                                                </div>
                                                            </a>
                                                        </div>

                                                        {{-- <div class="product-box">
                                                            <div class="img-wrapper">
                                                                <div class="front">
                                                                    <a href="{{$vendor_url}}"><img class="img-fluid blur-up lazyload" alt="" data-src="{{$imagePath}}" width="300" height="300"></a>
                                                                </div>
                                                            </div>
                                                            <div class="product-detail">
                                                                <div class="inner_spacing">
                                                                    <a href="{{$vendor_url}}">
                                                                        <h3>{{$data->name}}</h3>
                                                                    </a>
                                                                    @if($client_preference_detail)
                                                                        @if($client_preference_detail->rating_check == 1)
                                                                            @if($data->vendorRating > 0)
                                                                                <span class="rating">{{ number_format($data->vendorRating, 1, '.', '') }} <i class="fa fa-star text-white p-0"></i></span>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div> --}}
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
<script src="{{asset('front-assets/js/rangeSlider.min.js')}}"></script>
<script src="{{asset('front-assets/js/my-sliders.js')}}"></script>

<script>
    @if(!empty($category->image) && $category->image['is_original'])
    $(document).ready(function() {
        $("body").addClass("homeHeader");
    });
    @endif
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
        var range = $('.rangeSliderPrice').val();
        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('productFilters', $category->id) }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "brands": brands,
                "variants": variants,
                "options": options,
                "range": range
            },
            beforeSend : function() {
                if(ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
            },
            success: function(response) {
                $('.displayProducts').html(response.html);
            }
        });
    }

</script>



@endsection
