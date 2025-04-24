@extends('layouts.store', ['title' => __('Home')])
@section('css-links')
{{--<link href="{{asset('css/aos.css')}}" rel="stylesheet">--}}
@endsection
@section('css')
<style type="text/css">
.main-menu .brand-logo{display:inline-block;padding-top:20px;padding-bottom:20px}
.shimmer_effect{overflow:hidden}
.grid-row.grid-4-4{display:grid;grid-template-columns:repeat(4,1fr);grid-gap:20px}
.shimmer_effect .card_image{width:100%;height:100%}
.shimmer_effect .card_image.loading{width:100%;height:180px}
.shimmer_effect .card_title.loading{width:50%;height:1rem;margin:1rem 0;border-radius:3px;position:relative}
.shimmer_effect .card_description{padding:8px;font-size:16px}
.shimmer_effect .card_description.loading{height:1rem;margin:1rem 0;border-radius:3px}
.shimmer_effect .loading{position:relative;background:#cccccc86}.shimmer_effect .loading:after{content:"";display:block;position:absolute;top:0;width:100%;height:100%;transform:translateX(-100px);background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);animation:loading .8s infinite}.no-store-wrapper{display:none}@keyframes loading{100%{transform:translateX(100%)}}
.cardbanner {border-radius:0;height:550px;}
.shimmer_effect .grid-row .cards {margin-bottom: 40px;}
.shimmer_effect .grid-row .card_icon{display:none;}
.alTemplateTwoShimnerEffect .alTemplateTwoShimnerEffectBanner{width: 100%;max-width: 100%;}
.shimmer_effect .grid-row .card_image{border-radius:0;height:200px !important;}
.container_al{width: 100%;}
.alTwoHomeShimmer{position: fixed !important; background-color:#fff;width: 100%;top:0;z-index: 999 !important;}
.top_bar{height:50px;}
.logoArea_bar{height:54px;margin:20px 0 10px;}
.al_body_template_two section.section-b-space_.p-0.ratio_asos .container_al.shimmer_effect{width:100%; max-width: 100%;}
.alTabsView{border-radius:50px;}
@media(max-width:767px){.cardbanner {border-radius:0;height:250px;}}
@media (max-width: 991px){
.al_body_template_two #alTaxiBookingWrapper .cab-booking {
height: auto;
}}
</style>

@endsection
@section('content')

{{-- <div class="offset-top @if((\Request::route()->getName() != 'userHome') || ($client_preference_detail->show_icons == 0)) inner-pages-offset @endif @if($client_preference_detail->hide_nav_bar == 1) set-hide-nav-bar @endif"></div> --}}
<!-- shimmer_effect start -->
<section class="section-b-space_  p-0 ratio_asos alTwoHomeShimmer">
    <div class="container-fulid shimmer_effect  main_shimer topBar">
        <div class="row">
            <div class="col-12 cards">
                <div class="top_bar loading"></div>
            </div>
        </div>
    </div>
    <div class="container shimmer_effect main_shimer topBar">
        <div class="row">
            <div class="col-2 cards">
                <div class="logoArea_bar loading"></div>
            </div>
            <div class="col-1 cards">
                <div class="logoArea_bar"></div>
            </div>
            <div class="col-7 cards">
                <div class="logoArea_bar alTabsView loading"></div>
            </div>
            <div class="col-2 cards">
                <div class="logoArea_bar loading"></div>
            </div>
        </div>
    </div>
    <div class="container-fulid mt-1 mb-1 shimmer_effect main_shimer topBar">
        <div class="row">
            <div class="col-12 cards">
                <div class="top_bar loading"></div>
            </div>
        </div>
    </div>
	<div class="container_al mb-3 shimmer_effect main_shimer">
		<div class="row">
			<div class="col-12 cards">
				<div class="cardbanner loading"></div>
			</div>
		</div>
	</div>
	<div class="container mb-5 shimmer_effect main_shimer">
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
		<div class="row mt-3">
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


	</div>
</section>
<!-- shimmer_effect end -->
@if(count($banners))
<section class="home-slider-wrapper p-0">
	<div class="container-fluid">
		<div id="myCarousel" class="carousel slide al_desktop_banner" data-ride="carousel">
			<div class="carousel-inner">
				@foreach($banners as $key => $banner)
					@php $url=''; if($banner->link=='category'){if($banner->category !=null){$url=route('categoryDetail', $banner->category->slug);}}else if($banner->link=='vendor'){if($banner->vendor !=null){$url=route('vendorDetail', $banner->vendor->slug);}}@endphp
					<div class="carousel-item @if($key == 0) active @endif">
					 <a class="banner-img-outer" href="{{$url??'#'}}">
                        <link rel="preload" as="image" href="{{$banner->image['proxy_url'] . '1920/500' . $banner->image['image_path']}}" />
						<img alt="" title="" class="blur-up lazyload w-100" data-src="{{$banner->image['proxy_url'] . '1920/500' . $banner->image['image_path']}}">
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
					@php $url=''; if($banner->link=='category'){if($banner->category !=null){$url=route('categoryDetail', $banner->category->slug);}}else if($banner->link=='vendor'){if($banner->vendor !=null){$url=route('vendorDetail', $banner->vendor->slug);}}@endphp
					<div class="carousel-item @if($key == 0) active @endif">
					 <a class="banner-img-outer" href="{{$url??'#'}}">
                        <link rel="preload" as="image" href="{{$banner->image['proxy_url'] . '400/150' . $banner->image['image_path']}}" />
						<img alt="" title="" class="blur-up lazyload w-100" data-src="{{$banner->image['proxy_url'] . '400/150' . $banner->image['image_path']}}">
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
@else
<section class="home-slider-wrapper">
    <div class="container-fulid">
        <div id="myCarousel" class="carousel slide al_desktop_banner" data-ride="carousel"></div>
        <div id="myMobileCarousel" class="carousel slide al_mobile_banner mb-2" data-ride="carousel" style="display:none;"></div>
    </div>
</section>
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
				<link rel="preload" as="image" href="<%= banner.image.proxy_url %>1920/500<%= banner.image.image_path %>" />
				<img alt="" title="" class="blur-up lazyload w-100" data-src="<%= banner.image.proxy_url %>1920/500<%= banner.image.image_path %>">
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
				<link rel="preload" as="image" href="<%= banner.image.proxy_url %>400/150<%= banner.image.image_path %>" />
				<img alt="" title="" class="blur-up lazyload w-100" data-src="<%= banner.image.proxy_url %>400/150<%= banner.image.image_path %>">
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
        <div class="product-box scale-effect">
            <div class="img-wrapper">
                <div class="front">
                    <a href="{{route('vendorDetail')}}/<%= vendor.slug %>">
                        <% if(vendor.is_vendor_closed == 1){%>
                            <img class="img-fluid blur-up lazyload m-auto bg-img grayscale-image" alt="xx" src="<%= vendor.logo.proxy_url %>200/200<%= vendor.logo['image_path'] %>">
                            <% }else { %>
                                <img class="img-fluid blur-up lazyload m-auto bg-img" alt="xx" src="<%= vendor.logo.proxy_url %>200/200<%= vendor.logo['image_path'] %>">
                            <%  } %>

                    </a>
                </div>
            </div>
            <div class="product-detail inner_spacing text-center m-0 w-100">
                <a href="{{route('vendorDetail')}}/<%= vendor.slug %>">
                    <h3 class="d-flex justify-content-between p-0">
                        <span><%= vendor.name %></span>
                        @if($client_preference_detail)
                            @if($client_preference_detail->rating_check == 1)
                                <% if(vendor.vendorRating > 0){%>
                                    <span class="rating m-0"><%= vendor.vendorRating %> <i class="fa fa-star text-white p-0"></i></span>
                                <% } %>
                            @endif
                        @endif
                    </h3>
                </a>
                <% if(vendor.timeofLineOfSightDistance != undefined){ %>
                    <h6 class="d-flex justify-content-between">
                        <small><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.848633 6.15122C0.848633 2.7594 3.60803 0 6.99985 0C10.3917 0 13.1511 2.7594 13.1511 6.15122C13.1511 8.18227 12.1614 9.98621 10.6392 11.107L7.46151 15.7563C7.3573 15.9088 7.18455 16 6.99985 16C6.81516 16 6.64237 15.9088 6.5382 15.7563L3.36047 11.107C1.8383 9.98621 0.848633 8.18227 0.848633 6.15122ZM6.99981 10.4225C7.23979 10.4225 7.47461 10.4072 7.70177 10.3806C9.73302 10.0446 11.2871 8.27613 11.287 6.15122C11.287 3.78725 9.36375 1.86402 6.99977 1.86402C4.6358 1.86402 2.71257 3.78725 2.71257 6.15122C2.71257 8.27613 4.26665 10.0446 6.29786 10.3806C6.52498 10.4072 6.75984 10.4225 6.99981 10.4225ZM9.23683 6.15089C9.23683 7.38626 8.23537 8.38772 7.00001 8.38772C5.76464 8.38772 4.76318 7.38626 4.76318 6.15089C4.76318 4.91552 5.76464 3.91406 7.00001 3.91406C8.23537 3.91406 9.23683 4.91552 9.23683 6.15089Z" fill="black"/></svg> <%= vendor.lineOfSightDistance %></small>
                        <small><i class="fa fa-clock"></i> <%= vendor.timeofLineOfSightDistance %></small>
                    </h6>
                <% } %>
            </div>
        </div>
    <% }); %>
</script>

<script type="text/template" id="banner_template">
    <% _.each(brands, function(brand, k){%>
        <a class="barnd-img-outer" href="<%= brand.redirect_url %>">
            <img class="blur-up lazyloaded" src="<%= brand.image.image_fit %>500/500<%= brand.image.image_path %>" alt="">
        </a>
    <% }); %>
</script>

<script type="text/template" id="products_template">
    <% _.each(products, function(product, k){ %>
        <div>
            <a class="card scale-effect text-center" href="<%= product.vendor.slug %>/product/<%= product.url_slug %>">
                <label class="product-tag"><% if(product.tag_title != 0) { %><%= product.tag_title %><% } else { %><%= type %><% } %></label>
                <div class="product-image">
                    <img class="blur-up lazyloaded" src="<%= product.image_url %>" alt="">
                </div>
                <div class="media-body align-self-center">
                    <div class="inner_spacing px-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="m-0"><%= product.title %></h3>
                            @if($client_preference_detail)
                                @if($client_preference_detail->rating_check == 1)
                                    <% if(product.averageRating > 0){%>
                                        <span class="rating"><%= product.averageRating %> <i class="fa fa-star text-white p-0"></i></span>
                                    <% } %>
                                @endif
                            @endif
                        </div>
                        <p><%= product.vendor_name %></p>
                        <h4>
                            <% if(product.inquiry_only == 0) { %>
                                <%= product.price %>
                            <% } %>
                        </h4>
                    </div>
                </div>
            </a>
        </div>
    <% }); %>
</script>

<script type="text/template" id="trending_vendors_template">
    <% _.each(trending_vendors, function(vendor, k){%>
        <div class="product-box scale-effect">
            <div class="img-wrapper">
                <div class="front">
                    <a href="{{route('vendorDetail')}}/<%= vendor.slug %>">
                        <% if(vendor.is_vendor_closed == 1){%>
                        <img class="img-fluid blur-up lazyload m-auto bg-img grayscale-image" alt="" src="<%= vendor.logo.proxy_url %>200/200<%= vendor.logo['image_path'] %>">
                        <% }else { %>
                            <img class="img-fluid blur-up lazyload m-auto bg-img" alt="" src="<%= vendor.logo.proxy_url %>200/200<%= vendor.logo['image_path'] %>">
                        <%  } %>
                    </a>
                </div>
            </div>
            <div class="product-detail inner_spacing text-center m-0 w-100">
                <a href="{{route('vendorDetail')}}/<%= vendor.slug %>">
                    <h3 class="d-flex justify-content-between p-0">
                        <span><%= vendor.name %></span>
                        @if($client_preference_detail)
                            @if($client_preference_detail->rating_check == 1)
                                <% if(vendor.vendorRating > 0){%>
                                    <span class="rating m-0"><%= vendor.vendorRating %> <i class="fa fa-star text-white p-0"></i></span>
                                <% } %>
                            @endif
                        @endif
                    </h3>
                </a>
                <% if(vendor.timeofLineOfSightDistance != undefined){ %>
                    <h6 class="d-flex justify-content-between">
                        <small><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.848633 6.15122C0.848633 2.7594 3.60803 0 6.99985 0C10.3917 0 13.1511 2.7594 13.1511 6.15122C13.1511 8.18227 12.1614 9.98621 10.6392 11.107L7.46151 15.7563C7.3573 15.9088 7.18455 16 6.99985 16C6.81516 16 6.64237 15.9088 6.5382 15.7563L3.36047 11.107C1.8383 9.98621 0.848633 8.18227 0.848633 6.15122ZM6.99981 10.4225C7.23979 10.4225 7.47461 10.4072 7.70177 10.3806C9.73302 10.0446 11.2871 8.27613 11.287 6.15122C11.287 3.78725 9.36375 1.86402 6.99977 1.86402C4.6358 1.86402 2.71257 3.78725 2.71257 6.15122C2.71257 8.27613 4.26665 10.0446 6.29786 10.3806C6.52498 10.4072 6.75984 10.4225 6.99981 10.4225ZM9.23683 6.15089C9.23683 7.38626 8.23537 8.38772 7.00001 8.38772C5.76464 8.38772 4.76318 7.38626 4.76318 6.15089C4.76318 4.91552 5.76464 3.91406 7.00001 3.91406C8.23537 3.91406 9.23683 4.91552 9.23683 6.15089Z" fill="black"/></svg> <%= vendor.lineOfSightDistance %>km</small>
                        <small><i class="fa fa-clock"></i> <%= vendor.timeofLineOfSightDistance %></small>
                    </h6>
                <% } %>
            </div>
        </div>
    <% }); %>
</script>

<script type="text/template" id="recent_orders_template">
    <% _.each(recent_orders, function(order, k){ %>
        <% subtotal_order_price = total_order_price = total_tax_order_price = 0; %>
        <% _.each(order.vendors, function(vendor, k){ %>
        <%   product_total_count = product_subtotal_amount = product_taxable_amount = 0; %>
        <div class="order_detail order_detail_data align-items-top pb-3 card-box no-gutters mb-0">
            <% if((vendor.delivery_fee > 0) || (order.scheduled_date_time)){ %>
                <div class="progress-order font-12">
                <% if(order.scheduled_date_time){ %>
                        <span class="badge badge-success ml-2">Scheduled</span>
                        <span class="ml-2">Your order will arrive by <%= order.converted_scheduled_date_time %></span>
                    <% } else { %>
                        <span class="ml-2">Your order will arrive by <%= vendor.ETA %></span>
                    <% } %>
                </div>
            <% } %>
            <span class="left_arrow pulse"></span>
            <div class="row">
                <div class="col-5 col-sm-3">
                    <h5 class="m-0">{{__('Order Status')}}</h5>
                    <ul class="status_box mt-1 pl-0">
                    <% if(vendor.order_status){ %>
                        <li>
                        <% if(vendor.order_status == 'placed'){ %>
                                <img src="{{ asset('assets/images/order-icon.svg') }}" alt="">
                        <% }else if(vendor.order_status == 'accepted'){ %>
                                <img src="{{ asset('assets/images/payment_icon.svg') }}" alt="">
                        <% } else if(vendor.order_status == 'processing'){ %>
                                <img src="{{ asset('assets/images/customize_icon.svg') }}" alt="">
                        <% } else if(vendor.order_status == 'out for delivery'){ %>
                                <img src="{{ asset('assets/images/driver_icon.svg') }}" alt="">
                        <% } %>
                            <label class="m-0 in-progress"><%= (vendor.order_status).charAt(0).toUpperCase() + (vendor.order_status).slice(1) %></label>
                        </li>
                    <% } %>

                    <% if(vendor.dispatch_traking_url){ %>
                        <img src="{{ asset('assets/images/order-icon.svg') }}" alt="">
                        <a href="{{route('front.booking.details')}}/<%= order.order_number %>" target="_blank">{{ __('Details') }}</a>
                    <% } %>

                    <% if(vendor.dineInTable){ %>
                        <li>
                            <h5 class="mb-1">{{ __('Dine-in') }}</h5>
                            <h6 class="m-0"><%= vendor.dineInTableName %></h6>
                            <h6 class="m-0">Category : <%=  vendor.dineInTableCategory %></h6>
                            <h6 class="m-0">Capacity : <%= vendor.dineInTableCapacity %></h6>
                        </li>
                    <% } %>

                    </ul>
                </div>
                <div class="col-7 col-sm-4">
                    <ul class="product_list d-flex align-items-center p-0 flex-wrap m-0">
                    <% _.each(vendor.products, function(product, k){ %>
                            <% if(vendor.vendor_id == product.vendor_id){ %>
                                <li class="text-center">
                                    <img src="<%= product.image_url %>" alt="">
                                    <span class="item_no position-absolute">x <%= product.quantity %></span>
                                    <label class="items_price">{{Session::get('currencySymbol')}}<%= product.price  * product.pricedoller_compare %></label>
                                </li>
                                <%
                                    product_total_price = product.price * product.doller_compare;
                                    product_total_count += product.quantity * product_total_price;
                                    product_taxable_amount += product.taxable_amount;
                                    total_tax_order_price += product.taxable_amount;
                                %>
                            <% } %>
                        <% }); %>
                    </ul>
                </div>
                <div class="col-md-5 mt-md-0 mt-sm-2">
                    <ul class="price_box_bottom m-0 p-0">
                        <li class="d-flex align-items-center justify-content-between">
                            <label class="m-0">{{__('Product Total')}}</label>
                            <span>{{Session::get('currencySymbol')}} <%=Helper.formatPrice(vendor.subtotal_amount)%></span>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <label class="m-0">{{__('Coupon Discount')}}</label>
                            <span>{{Session::get('currencySymbol')}} <%=Helper.formatPrice(vendor.discount_amount)%></span>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <label class="m-0">{{__('Delivery Fee')}}</label>
                            <span>{{Session::get('currencySymbol')}} <%= Helper.formatPrice(vendor.delivery_fee)%></span>
                        </li>
                        <li class="grand_total d-flex align-items-center justify-content-between">
                            <label class="m-0">{{__('Amount')}}</label>
                            <%
                                product_subtotal_amount = product_total_count - vendor.discount_amount + vendor.delivery_fee;
                                subtotal_order_price += product_subtotal_amount;
                            %>
                            <span>{{Session::get('currencySymbol')}} <%=Helper.formatPrice(vendor.payable_amount)%></span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <% }); %>
    <% }); %>
</script>

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

<section class="section-b-space ratio_asos d-none pb-0 pt-0 mt-0 al_template_two_content" id="our_vendor_main_div">
    <div class="vendors">
        @foreach($homePageLabels as $key => $homePageLabel)
        @if($homePageLabel->slug == 'pickup_delivery')
            @if(isset($homePageLabel->pickupCategories) && count($homePageLabel->pickupCategories))
                @include('frontend.booking.cabbooking-single-module')
            @endif
        @elseif($homePageLabel->slug == 'dynamic_page')
        
            @include('frontend.included_files.dynamic_page')
        @else
        <div class="container render_full_{{$homePageLabel->slug}} d-none" id="{{$homePageLabel->slug.$key}}">
            <div class="row">
                <div class="col-12"   >
                    @if($homePageLabel->slug == 'vendors')
                    <div class="product-5 product-m no-arrow render_{{$homePageLabel->slug}} suppliers-slider-{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}" ></div>
                    @elseif($homePageLabel->slug == 'trending_vendors')
                    <div class="product-5 product-m no-arrow render_{{$homePageLabel->slug}} suppliers-slider-{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}" ></div>
                    @elseif($homePageLabel->slug == 'cities')
                    <div class="product-5 product-m no-arrow render_{{$homePageLabel->slug}} suppliers-slider-{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}" ></div>
                    @elseif($homePageLabel->slug == 'recent_orders')
                    <div class="recent-orders product-m no-arrow render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
                    @elseif($homePageLabel->slug == 'brands')
                    <div class="brand-slider product-m no-arrow render_{{$homePageLabel->slug }}" id="{{$homePageLabel->slug.$key}}" ></div>
                    @else
                    <div class="product-4-{{$homePageLabel->slug}} product-m no-arrow render_{{$homePageLabel->slug }}" id="{{$homePageLabel->slug.$key}}"></div>
                    @endif
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</section>
<section class="no-store-wrapper mb-3">
    <div class="container">
        @if(count($for_no_product_found_html))
        @foreach($for_no_product_found_html as $key => $homePageLabel)
            @include('frontend.included_files.dynamic_page')
        @endforeach
       @else
        <div class="row">
            <div class="col-12 text-center">
                <img class="no-store-image mt-2 mb-2" src="{{ getImageUrl(asset('images/no-stores.svg'),'250/250') }}" style="max-height: 250px;">
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-center mt-2">
                <h4>{{__('There are no stores available in your area currently.')}}</h4>
            </div>
        </div>
        @endif
    </div>
</section>
<div class="modal fade" id="age_restriction" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img style="height:150px" class="img-fluid blur-up lazyload" data-src="{{asset('assets/images/18.png')}}" alt="">
                <p class="mb-0 mt-3">{{ $client_preference_detail ? $client_preference_detail->age_restriction_title : __('Are you 18 or older?') }}</p>
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
@endsection
@section('js-script')
<script type="text/javascript" src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/fly-cart.js')}}"></script>
{{--<script type="text/javascript" src="{{asset('js/aos.js')}}"></script>--}}
@endsection
@section('script')
<script type="text/javascript">
    // AOS.init();
    function changeImage(image, check) {
       var  icon = $(image).attr('data-icon');
       var  icon_two = $(image).attr('data-icon_two');
       if(check == 1)
       {
        setTimeout(function () {
            $(image).attr('data-src',icon_two);
            $(image).attr('src',icon_two);
        },200);
       }else if(check == 0){
            setTimeout(function () {
                $(image).attr('data-src',icon);
                $(image).attr('src',icon);
            },200);

       }
    }
</script>
@endsection
