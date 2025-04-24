@extends('layouts.store', ['title' => __('Home')]) @section('content')
@section('css-links')
<link rel="stylesheet/less" type="text/css" href="{{ asset('front-assets/css/shimmer-less.less') }}">
{{--
<link rel="stylesheet"  href="{{asset('css/aos.css')}}">
--}}
@endsection

@php
$preference = $client_preference_detail;
@endphp
@section('css')
<style>
   .cardbanner {height:300px;}
   .shimmer_effect .grid-row .cards {margin-bottom: 20px;}
   .shimmer_effect .grid-row .card_icon{display:none;}
   .shimmer_effect .grid-row .card_image{border-radius:12px;height:200px !important;}
   .alOneTemplate{position: fixed !important;width: 100%; background: #fff;z-index: 999 !important;top: 0;}
   @media(max-width:767px){.cardbanner {height:120px;}}
</style>
@endsection
<!-- <div class="offset-top @if((\Request::route()->getName() != 'userHome') || ($client_preference_detail->show_icons == 0)) inner-pages-offset @endif @if($client_preference_detail->hide_nav_bar == 1) set-hide-nav-bar @endif"></div> -->
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary d-none" data-toggle="modal" data-target="#login_modal"> Launch demo modal </button>

<!-- shimmer_effect start -->
<section class="section-b-space_  p-0 ratio_asos alOneTemplate">
    <div class="container-fulid shimmer_effect main_shimer topBar">
        <div class="row">
            <div class="col-12 cards">
                <div class="top_bar loading"></div>
            </div>
        </div>
    </div>
    <div class="container-fulid shimmer_effect main_shimer topBar">
        <div class="row">
            <div class="col-12 cards">
                <div class="logoArea_bar loading"></div>
            </div>
        </div>
    </div>
   <div class="container mb-3 mt-3 shimmer_effect main_shimer ">
      <div class="row">
         <div class="col-12 cards">
            <div class="cardbanner loading"></div>
         </div>
      </div>
   </div>
   @switch($preference->business_type)
   @case('taxi')
   <div class="container_al mb-3 shimmer_effect main_shimer">
      <div class="row">
         <div class="col-12 cards">
            <div class="cardbanner loading"></div>
         </div>
      </div>
   </div>
   @break
   @default
   <div class="container mb-5 shimmer_effect main_shimer">
      <div class="row">
         <div class="col-12 cards">
            <h2 class="h2-heading loading mb-3"></h2>
         </div>
      </div>
      <div class="row">
         <div class="col-1 grid-row">
            <div class="card_image loading"></div>
            <div class="d-flex align-items-center justify-content-between">
               <div class="card_title loading"></div>
            </div>
            <div class="card_content loading mt-0 w-75"></div>
            <div class="card_content loading mt-0 w-50"></div>
            <div class="card_line loading"></div>
            <div class="card_price loading"></div>
         </div>
         <div class="col-10">
            <div class="grid-row grid-4-4">
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
            </div>
         </div>
         <div class="col-1 grid-row">
            <div class="card_image loading"></div>
            <div class="d-flex align-items-center justify-content-between">
               <div class="card_title loading"></div>
               <div class="card_icon loading"></div>
            </div>
            <div class="card_content loading mt-0 w-75"></div>
            <div class="card_content loading mt-0 w-50"></div>
            <div class="card_line loading"></div>
            <div class="card_price loading"></div>
         </div>
      </div>
      <div class="row">
         <div class="col-1 grid-row">
            <div class="card_image loading"></div>
            <div class="d-flex align-items-center justify-content-between">
               <div class="card_title loading"></div>
            </div>
            <div class="card_content loading mt-0 w-75"></div>
            <div class="card_content loading mt-0 w-50"></div>
            <div class="card_line loading"></div>
            <div class="card_price loading"></div>
         </div>
         <div class="col-10">
            <div class="grid-row grid-4-4">
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
            </div>
         </div>
         <div class="col-1 grid-row">
            <div class="card_image loading"></div>
            <div class="d-flex align-items-center justify-content-between">
               <div class="card_title loading"></div>
               <div class="card_icon loading"></div>
            </div>
            <div class="card_content loading mt-0 w-75"></div>
            <div class="card_content loading mt-0 w-50"></div>
            <div class="card_line loading"></div>
            <div class="card_price loading"></div>
         </div>
      </div>
      <div class="row">
         <div class="col-1 grid-row">
            <div class="card_image loading"></div>
            <div class="d-flex align-items-center justify-content-between">
               <div class="card_title loading"></div>
            </div>
            <div class="card_content loading mt-0 w-75"></div>
            <div class="card_content loading mt-0 w-50"></div>
            <div class="card_line loading"></div>
            <div class="card_price loading"></div>
         </div>
         <div class="col-10">
            <div class="grid-row grid-4-4">
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
               <div class="cards">
                  <div class="card_image loading"></div>
                  <div class="d-flex align-items-center justify-content-between">
                     <div class="card_title loading"></div>
                     <div class="card_icon loading"></div>
                  </div>
                  <div class="card_content loading mt-0 w-75"></div>
                  <div class="card_content loading mt-0 w-50"></div>
                  <div class="card_line loading"></div>
                  <div class="card_price loading"></div>
               </div>
            </div>
         </div>
         <div class="col-1 grid-row">
            <div class="card_image loading"></div>
            <div class="d-flex align-items-center justify-content-between">
               <div class="card_title loading"></div>
               <div class="card_icon loading"></div>
            </div>
            <div class="card_content loading mt-0 w-75"></div>
            <div class="card_content loading mt-0 w-50"></div>
            <div class="card_line loading"></div>
            <div class="card_price loading"></div>
         </div>
      </div>
   </div>
   @endswitch
</section>

@if(count($banners))

<section class="home-slider-wrapper py-sm-3 ">
   <div class="container">
      <div id="myCarousel" class="carousel slide al_desktop_banner" data-ride="carousel">
         <div class="carousel-inner">
            @foreach($banners as $key => $banner)
            @php $url=''; if($banner->link=='category'){if(!empty($banner->category_slug)){$url=route('categoryDetail', $banner->category_slug);}}else if($banner->link=='vendor'){if(!empty($banner->vendor_slug)){$url=route('vendorDetail', $banner->vendor_slug);}}else if($banner->link=='url'){if($banner->link_url !=null){$url=$banner->link_url;}}@endphp
            <div class="carousel-item @if($key == 0) active @endif">
               <a class="banner-img-outer" href="{{$url??'#'}}" target="_blank">
                  <link rel="preload" as="image" href="{{ get_file_path($banner->image,'IMG_URL1','1370','300') }}" />
                  <img alt="" title="" class="blur-up lazyload w-100" data-src="{{ get_file_path($banner->image,'IMG_URL1','1370','300') }}" >
               </a>
            </div>
            @endforeach
         </div>
         <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
         <span class="carousel-control-prev-icon" aria-hidden="true"></span>
         <span class="sr-only">{{__('Previous')}}</span>
         </a>
         <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
         <span class="carousel-control-next-icon" aria-hidden="true"></span>
         <span class="sr-only">{{__('Next')}}</span>
         </a>
      </div>
      <div id="myMobileCarousel" class="carousel slide al_mobile_banner mb-2" data-ride="carousel" style="display:none;">
         <div class="carousel-inner">
            @foreach($mobile_banners as $key => $banner)
            @php $url=''; if($banner->link=='category'){if(!empty($banner->category_slug)){$url=route('categoryDetail', $banner->category_slug);}}else if($banner->link=='vendor'){if(!empty($banner->vendor_slug)){$url=route('vendorDetail', $banner->vendor_slug);}}@endphp
            <div class="carousel-item @if($key == 0) active @endif">
               <a class="banner-img-outer" href="{{$url??'#'}}">
                  <link rel="preload" as="image" href="{{ get_file_path($banner->image,'IMG_URL1','1370','300') }}" />
                  <img alt="" title="" class="blur blurload w-100" data-src="{{ get_file_path($banner->image,'IMG_URL1','1370','300') }}" src="{{ get_file_path($banner->image,'IMG_URL1','137','30') }}">
               </a>
            </div>
            @endforeach
         </div>
         <a class="carousel-control-prev" href="#myMobileCarousel" role="button" data-slide="prev">
         <span class="carousel-control-prev-icon" aria-hidden="true"></span>
         <span class="sr-only">{{__('Previous')}}</span>
         </a>
         <a class="carousel-control-next" href="#myMobileCarousel" role="button" data-slide="next">
         <span class="carousel-control-next-icon" aria-hidden="true"></span>
         <span class="sr-only">{{__('Next')}}</span>
         </a>
      </div>
   </div>
</section>
@endif


@if($vendor_type!="car_rental")
<section class="section-b-space ratio_asos  pt-0 mt-0 pb-0 mt-0" id="our_vendor_main_div">
   <div class="vendors">
      @foreach($homePageLabels as $key => $homePageLabel)
         @if($homePageLabel->slug == 'pickup_delivery')
            @if(isset($homePageLabel->pickupCategories) && count($homePageLabel->pickupCategories) && $vendor_type=="pick_drop")
               @include('frontend.booking.cabbooking-single-module')
            @endif
         @elseif($homePageLabel->slug == 'dynamic_page')
            @include('frontend.included_files.dynamic_page')
         @elseif($homePageLabel->slug == 'best_sellers'  && (count($homePageData[$homePageLabel->slug]) > 0))
            <section class="suppliers-section">
               <div class="container"  >
                  <div class="row">
                     <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                        <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('Best Sellers', true)}}</h2>
                     </div>
                     <div class="col-12">
                        <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                           @foreach ($homePageData['best_sellers'] as $vendor )
                              @include('frontend.home_page_1.vendor')
                           @endforeach
                        </div>
                     </div>
                  </div>
               </div>
            </section>
         @elseif($homePageLabel->slug == 'brands' && (count($homePageData['brands']) > 0))
            <section class="popular-brands left-shape_ position-relative">
               <div class="container ">
                  <div class="row align-items-center">
                     <div class="col-lg-12 cw top-heading pr-0 text-center text-lg-left mb-3 mb-lg-0">
                        <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('brands', true)}}</h2>
                     </div>
                     <div class="col-lg-12 al_custom_brand">
                        <div class="brand-slider render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                           @foreach ($homePageData['brands'] as $brand )
                              @include('frontend.home_page_1.brands')
                           @endforeach
                        </div>
                     </div>
                  </div>
               </div>
               {{-- <div class="container "  >
                  <div class="al_top_heading col-md-12">
                     <div class="row d-flex justify-content-between">
                        <h2 class="h2-heading text-capitalize">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('brands', true)}}</h2> --}}
                        {{-- <a class="" href="">See All</a> --}}
                     {{-- </div>
                  </div>
                  <div class="row ">
                     <div class=" col-md-12 al_custom_brand">
                        <div class=" brand-slider render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
                     </div>
                  </div>
               </div> --}}
            </section>
         @elseif($homePageLabel->slug == 'cities' && (count($homePageData[$homePageLabel->slug]) > 0))
            <section class="suppliers-section container render_full_{{$homePageLabel->slug}}">
               <div class=" top-heading d-flex justify-content-between align-self-center">
                  <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : 'Cities'}}</h2>
               </div>
               <div class="col-12 p-0">
                  <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                     @foreach ($homePageData[$homePageLabel->slug] as $cities )
                     <div class="alSpaListSlider">
                        <div>
                           <div class="alSpaListBox">
                              <div class="alSpaCityBox">
                                 <a href="javascript:void(0);" class="cities updateLocationByCity" data-lat="{{$cities['latitude']}}" data-long="{{$cities['longitude']}}" data-place_id="{{$cities['place_id']}}" data-address="{{$cities['address']}}"><img class="w-100" src="{{$cities['image']['image_fit']}}260/260{{$cities['image']['image_path']}}"></a>
                              </div>
                              <p>{{$cities["title"]}} </p>
                           </div>
                        </div>
                     </div>
                     @endforeach
                  </div>
               </div>
            </section>
         @elseif($homePageLabel->slug == 'vendors' && (count($homePageData[$homePageLabel->slug]) > 0))
            <section class="suppliers-section">
               <div class="container"  >
                  <div class="row">
                     <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                        <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('vendors', true)}}</h2>
                        <a class="" href="{{route('vendor.all')}}">{{__("See all")}}</a>
                     </div>
                     <div class="col-12">
                        <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                           @foreach ($homePageData['vendors'] as $vendor )
                              @include('frontend.home_page_1.vendor')
                           @endforeach
                        </div>
                     </div>
                  </div>
               </div>
            </section>
         @elseif($homePageLabel->slug == 'recent_orders' && (count($homePageData['recent_orders']) > 0))
            <section class="container mb-0 render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"  >
               <div class="row" >
                  <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                     <h2 class="h2-heading"> @php
                           echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __("Your Recent Orders");
                     @endphp </h2>
                  </div>
               </div>
               <div class="row">
                  <div class="col-12">
                     <div class="recent-orders product-m no-arrow render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                        @foreach ($homePageData[$homePageLabel->slug] as $order )
                        @include('frontend.common_section.recent_order')
                        @endforeach
                     </div>
                  </div>
               </div>
            </section>
         @elseif($homePageLabel->slug == 'trending_vendors' && (count($homePageData[$homePageLabel->slug]) > 0))
            <section class="suppliers-section">
               <div class="container"  >
                  <div class="row">
                     <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                        <h2 class="h2-heading abc">{{$homePageLabel->slug=='trending_vendors' ? __('Trending')." ".getNomenclatureName('vendors', true) : __($homePageLabel->title)}}</h2>
                     </div>
                     <div class="col-12">
                        <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                           @foreach ($homePageData['trending_vendors'] as $vendor )
                           @include('frontend.home_page_1.vendor')
                           @endforeach
                        </div>
                     </div>
                  </div>
               </div>
            </section>
         @elseif($homePageLabel->slug == 'long_term_service' && (count($homePageData['long_term_service']) != 0))
            <section class="suppliers-section container" id="homepage_long_term_service_div">
                <div class=" top-heading ">
                    <h2 class="h2-heading">{{$homePageLabel->slug =='long_term_service' ? __('Long Term')." ".getNomenclatureName('service', true) : __($homePageLabel->title)}}</h2>
                </div>
                <div class="row">
                    <div class="col-12 p-0">
                   {{-- @php
                   pr($homePageData[$homePageLabel->slug]);
                   @endphp --}}
                        <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                            @foreach ($homePageData[$homePageLabel->slug] as $value )
                   
                            @include('frontend.home_page_1.long_term_service')
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
         @elseif($homePageLabel->slug == 'banner' && (count($homePageData['banners']) != 0))
            @if(!empty(@$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]))
               <section class="container mb-0 render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"  >
                  <div class="top-heading d-flex justify-content-between">
                     <h2 class="h2-heading"> @php
                        echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);
                     @endphp </h2>
                  </div>
   
                  <div class="custom_banner">
                     <div class="container">
                        <div class="text-center">
                           @php
                              $url = $homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]; // replace with your URL
                              $extension = pathinfo($url, PATHINFO_EXTENSION);
                              $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']; // list of image extensions
                              $video_extensions = ['mp4', 'avi', 'mov', 'wmv']; // list of video extensions
                           @endphp
                           @if(in_array($extension, $image_extensions))
                              <img alt="" title="" class="blur blurload w-100" data-src="{{$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]}}" src="{{$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]}}">	
                           @elseif (in_array($extension, $video_extensions))
                              <video id="video1" width="100%" controls autoplay muted>
                                 <source data-src="{{$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]}}" src="{{$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]}}" type="video/mp4">
                              </video>
                           @else
                           @endif
                        </div>
                     </div>
                  </div>
               </section>
            @endif
         @else
            @if(!empty($homePageData[$homePageLabel->slug]) && count($homePageData[$homePageLabel->slug]) != 0)
               <section class="container mb-0 render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"  >
                  <div class="row" >
                     <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                        <h2 class="h2-heading def"> @php
                     echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);@endphp </h2>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-12">

                        <div class="product-4-{{$homePageLabel->slug}} product-m no-arrow render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                           @foreach ($homePageData[$homePageLabel->slug] as $product )
                              @include('frontend.home_page_1.product')
                           @endforeach
                        </div>

                     </div>
                  </div>
               </section>
            @endif
         @endif
      @endforeach
   </div>
</section>
@endif
<section class="no-store-wrapper mb-3" style="display: none;"  >
   <div class="container">
      @if(count($for_no_product_found_html)) @foreach($for_no_product_found_html as $key => $homePageLabel) @include('frontend.included_files.dynamic_page') @endforeach @else
      <div class="row">
         <div class="col-12 text-center"> <img class="no-store-image mt-2 mb-2 blur blurload" data-src="{{getImageUrl(asset('images/no-stores.svg'),'250/250')}}" style="max-height: 250px;"> </div>
      </div>
      <div class="row">
         <div class="col-12 text-center mt-2">
            <h4>{{__('There are no stores available in your area currently.')}}</h4>
         </div>
      </div>
      @endif
   </div>
</section>
<div class="modal age-restriction fade" id="age_restriction" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-body text-center">
            <img style="height: 150px;" class="blur blurload" data-src="{{getImageUrl(asset('assets/images/age-img.svg'),'150/150')}}" alt="" title="">
            <p class="mb-0 mt-3">{{$client_preference_detail ? $client_preference_detail->age_restriction_title : __('Are you 18 or older?')}}</p>
            <p class="mb-0">{{__('Are you sure you want to continue?')}}</p>
         </div>
         <div class="modal-footer d-block">
            <div class="row no-gutters">
               <div class="col-6 pr-1">
                  <button type="button" class="btn btn-solid w-100 age_restriction_yes" data-dismiss="modal">{{__('Yes')}}</button>
               </div>
               <div class="col-6 pl-1">
                  <button type="button" class="btn btn-solid w-100 age_restriction_no" data-dismiss="modal">{{__('No')}}</button>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@if($vendor_type=="p2p")
@if(!empty($navCategories) && count($navCategories))
<section class="p2p-categories">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center mb-4">
				<h2>Categories</h2>
			</div>
		</div>
		<div class="categories_slider" >
			{{-- @dump($navCategories) --}}
			@foreach($navCategories as $cate)
				@if($cate['name'])
					<div class="item">
						<div class="cate-item text-center">
							<a href="{{route('categoryDetail', $cate['slug'])}}">
								<img
									class="blur-up lazyload"
									data-icon_two="{{!is_null($cate['icon_two']) ? $cate['icon_two']['image_fit'].'200/200'.$cate['icon_two']['image_path'] : $cate['icon']['image_fit'].'200/200'.$cate['icon']['image_path']}}"
									data-icon="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}"
									data-src="{{$cate['icon']['image_fit']}}150/150{{$cate['icon']['image_path']}}"
									alt=""
									onmouseover='changeImage(this,1)'
									onmouseout='changeImage(this,0)'
								>
								<h3>{{$cate['name']}}</h3>
							</a>
						</div>
					</div>
				@endif
			@endforeach
		</div>
	</div>
</section>
@endif
@endif

@if($vendor_type=="car_rental")
   @include('frontend.yacht.rental');
@endif

<script type="text/template" id="desktop_banners_template">
	<div class="carousel-inner">
	   <% _.each(banners, function(banner, k){%>
		  <%
		  var url='#';
		  if(banner.link == 'category'){
			 if(banner.category != null){
				url = "{{route('categoryDetail')}}" + "/" + banner.category.slug;
			 }
		  }
          else if(banner.link == 'vendor'){
			 if(banner.vendor != null){
				url = "{{route('vendorDetail')}}" + "/" + banner.vendor.slug;
			 }
		  }
		  %>
		  <div class="carousel-item <% if(k == 0) { %> active <% } %>">
			 <a class="banner-img-outer" href="<%= url %>">
				<link rel="preload" as="image" href="<%= banner.image.proxy_url %>1370/300<%= banner.image.image_path %>" />
				<img alt="" title="" class="blur blurload w-100" data-src="<%= banner.image.proxy_url %>1370/300<%= banner.image.image_path %>">
			 </a>
		  </div>
	   <% }); %>
	</div>
	<a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only">{{__('Previous')}}</span>
	</a>
	<a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only">{{__('Next')}}</span>
	</a>
</script>

<script type="text/template" id="mobile_banners_template">
	<div class="carousel-inner">
	   <% _.each(banners, function(banner, k){%>
		  <%
		  var url='#';
		  if(banner.link == 'category'){
			 if(banner.category != null){
				url = "{{route('categoryDetail')}}" + "/" + banner.category.slug;
			 }
		  }
          else if(banner.link == 'vendor'){
			 if(banner.vendor != null){
				url = "{{route('vendorDetail')}}" + "/" + banner.vendor.slug;
			 }
		  }
		  %> 
		  <div class="carousel-item <% if(k == 0) { %> active <% } %>">
			 <a class="banner-img-outer" href="<%= url %>">
				<link rel="preload" as="image" href="<%= banner.image.proxy_url %>1370/300<%= banner.image.image_path %>" />
				<img alt="" title="" class="blur blurload w-100" data-src="<%= banner.image.proxy_url %>1370/300<%= banner.image.image_path %>">
			 </a>
		  </div>
	   <% }); %>
	</div>
	<a class="carousel-control-prev" href="#myMobileCarousel" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only">{{__('Previous')}}</span>
	</a>
	<a class="carousel-control-next" href="#myMobileCarousel" role="button" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only">{{__('Next')}}</span>
	</a>
</script>

<script type="text/template" id="vendors_template">
   <% _.each(vendors, function(vendor, k){%>
   	<div class="product-card-box position-relative ">
   		<a class="suppliers-box d-block" href="{{route('vendorDetail')}}/<%=vendor.slug %>">
   			<div class="suppliers-img-outer position-relative">
   				<% if(vendor.is_vendor_closed==1){%> <img class="fluid-img mx-auto blur blurload grayscale-image" data-src="<%=vendor.logo.image_fit %>200/200<%=vendor.logo['image_path'] %>" alt="" title="">
   					<%}else{%> <img class="fluid-img mx-auto blur blurload" data-src="<%=vendor.logo.image_fit %>200/200<%=vendor.logo['image_path'] %>" alt="" title="">
   						<%}%>
   							<% if(vendor.timeofLineOfSightDistance !=undefined){%>
   								<div class="pref-timing"> <span><%=vendor.timeofLineOfSightDistance %></span> </div>
   								<%}%>
   			</div>
   			<div class="supplier-rating">
   				<div class="d-flex align-items-center justify-content-between">
   					<h6 class="mb-1 ellips"><%=vendor.name %></h6> @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
   					<% if(vendor.vendorRating > 0){%> <span class="rating-number"><%=vendor.vendorRating %></span>
   						<%}%> @endif @endif </div>
   				<p title="<%=vendor.categoriesList %>" class="vendor-cate mb-1 ellips">
   					<%=vendor.categoriesList %>
   				</p>
   			</div>
   		</a>
   	</div>
   	<% }); %>
</script>
<script type="text/template" id="banner_template">
   <% _.each(brands, function(brand, k){%>
   	<div>
   		<a class="brand-box d-block black-box" href="<%=brand.redirect_url %>">
   			<div class="brand-ing">
   				<img class="blur blurload" data-src="<%=brand.image.image_fit %>260/260<%=brand.image.image_path %>" alt="" title="">
   			</div>
   			<h6><%=brand.translation_title %></h6>
   		</a>
   	</div>
   	<% }); %>
</script>
<!-- cities start -->
<script type="text/template" id="cities_template" >
   <% _.each(cities, function(city, k){%>
      <div class="alSpaListSlider">
         <div>
            <div class="alSpaListBox">
               <div class="alSpaCityBox">
                  <a href="/cities/<%=city.slug %>"><img class="w-100" src="<%=city.image.image_fit %>260/260<%=city.image.image_path %>"></a>
               </div>
               <p><%=city.title %></p>
            </div>
         </div>
      </div>
   	<% });
   %>
</script><!-- cities cities end -->
<script type="text/template" id="products_template">
   <% _.each(products, function(product, k){ %>
   	<div class="product-card-box al_box_third_template position-relative al">
   		<div class="add-to-fav 12">
   			<input id="fav_pro_one" type="checkbox">
   			<label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label>
   		</div>
   		<a class="common-product-box text-center" href="<%=product.vendor.slug %>/product/<%=product.url_slug %>">
   			<div class="img-outer-box position-relative"> <img class="blur blurload" data-src="<%=product.image_url %>" alt="" title="">
   				<div class="pref-timing"> </div>
   			</div>
   			<div class="media-body align-self-center">
   				<div class="inner_spacing px-0">
   					<div class="product-description">
   						<div class="d-flex align-items-center justify-content-between">
   							<h6 class="card_title ellips"><%=product.title %></h6> @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
   							<% if(product.averageRating > 0){%> <span class="rating-number"><%=product.averageRating %></span>
   								<%}%> @endif @endif
   						</div>
   						<p class="al_productText ellips">
   							<%=product.vendor_name %>
   						</p>
   						<p class="border-bottom pb-1 d-none">
   							<span>
   						{{__('In')}}
   							<%=product.category %></span>
   						</p>
   						<div class="d-flex align-items-center justify-content-between al_clock"> <b><% if(product.inquiry_only==0){%> <%=product.price %> <%}%></b>
   							<!-- <p><i class="fa fa-clock-o"></i> 30-40 min</p>  -->
   						</div>
   					</div>
   				</div>
   			</div>
   		</a>
   	</div>
   	<% }); %>
</script>
<script type="text/template" id="trending_vendors_template">
   <% _.each(trending_vendors, function(vendor, k){%>
   	<div class="product-card-box position-relative">
   		<a class="suppliers-box d-block" href="{{route('vendorDetail')}}/<%=vendor.slug %>">
   			<div class="suppliers-img-outer position-relative">
   				<% if(vendor.is_vendor_closed==1){%> <img class="fluid-img mx-auto blur blurload grayscale-image" data-src="<%=vendor.logo.image_fit %>200/200<%=vendor.logo['image_path'] %>" alt="" title="">
   					<%}else{%> <img class="fluid-img mx-auto blur blurload" data-src="<%=vendor.logo.image_fit %>200/200<%=vendor.logo['image_path'] %>" alt="" title="">
   						<%}%>
   							<% if(vendor.timeofLineOfSightDistance !=undefined){%>
   								<div class="pref-timing"> <span><%=vendor.timeofLineOfSightDistance %></span> </div>
   								<%}%>
   			</div>
   			<div class="supplier-rating">
   				<div class="d-flex align-items-center justify-content-between">
   					<h6 class="mb-1 ellips"><%=vendor.name %></h6> @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
   					<% if(vendor.vendorRating > 0){%> <span class="rating-number"><%=vendor.vendorRating %></span>
   						<%}%> @endif @endif </div>
   				<p title="<%=vendor.categoriesList %>" class="vendor-cate mb-1 ellips">
   					<%=vendor.categoriesList %>
   				</p>
   			</div>
   		</a>
   	</div>
   	<% }); %>
</script>
<script type="text/template" id="recent_orders_template">
   <% _.each(recent_orders, function(order, k){ %>
   	<% subtotal_order_price = total_order_price = total_tax_order_price = 0; %>
   		<% _.each(order.vendors, function(vendor, k){ %>
   			<%   product_total_count = product_subtotal_amount = product_taxable_amount = 0; %>
            @include('frontend.common_section.recent_order_j')
   				<% }); %>
   					<% }); %>
</script>

@endsection
@section('home-page')
{{-- <script type="text/javascript" src="{{asset('front-assets/js/homepage.js')}}"></script> --}}
<script type="text/javascript" src="{{asset('assets/js/template/commonFunction.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/template/template-one/templateFunction.js')}}"></script>
@endsection
@section('js-script')
<script type="text/javascript" src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/fly-cart.js')}}"></script>
<script>
	var featured_products_length = {{ isset($homePageData['featured_products']) ? count($homePageData['featured_products']) : ''}};
</script>
{{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/less@4"></script> --}}
{{--<script type="text/javascript" src="{{asset('js/aos.js')}}"></script>--}}
@endsection
@section('script')
@endsection
@section('script-bottom')
@endsection