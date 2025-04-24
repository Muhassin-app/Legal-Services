@extends('layouts.store', ['title' => __('Home')])
@section('css-links')
{{--<link href="{{asset('css/aos.css')}}" rel="stylesheet">--}}
<script>
	var featured_products_length = {{ isset($homePageData['featured_products']) ? count($homePageData['featured_products']) : ''}};
</script>
@endsection
@section('css')
<style>
	.logoArea_bar {
		height: 300px;
		margin: 0 0 30px;
	}

	.alMainMenuView {
		height: 100px;
		width: 100px;
		border-radius: 50%;
	}

	@media(max-width: 767px) {
		.logoArea_bar {
			height: 150px;
			margin: 0 0 20px;
		}
	}
</style>
@endsection
@section('content')
<!-- Shimmer Efferct Start -->
<section class="section-b-space_  p-0 ratio_asos banner_shimmer">
	<div class="container-fulid shimmer_effect  main_shimer topBar">
		<div class="row">
			<div class="col-12 cards">
				<div class="logoArea_bar loading"></div>
			</div>
		</div>
	</div>
	<div class="container shimmer_effect main_shimer">
		<div class="row">
			<div class="col-12 cards">
				<div class="cardbanner loading"></div>
			</div>
		</div>
	</div>
	<div class="container mb-md-5 shimmer_effect main_shimer">
		<div class="row">
			<div class="col-12 cards">
				<h2 class="h2-heading loading mb-3"></h2>
			</div>
		</div>
		<div class="row">

			<div class="col-sm-12">
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
		</div>

	</div>
	<div class="container mb-md-5 shimmer_effect main_shimer">
		<div class="row">
			<div class="col-12 cards">
				<h2 class="h2-heading loading mb-3"></h2>
			</div>
		</div>
		<div class="row">

			<div class="col-sm-12">
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
			<!-- <div class="col-sm-1 grid-row px-sm-3 p-0 d-sm-block d-none">
                <div class="card_image loading"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card_title loading"></div>
                    <div class="card_icon loading"></div>
                </div>
                <div class="card_content loading mt-0 w-75"></div>
                <div class="card_content loading mt-0 w-50"></div>
                <div class="card_line loading"></div>
                <div class="card_price loading"></div>
            </div> -->
		</div>

	</div>
	<div class="container mb-md-5 shimmer_effect main_shimer">
		<div class="row">
			<div class="col-12 cards">
				<h2 class="h2-heading loading mb-3"></h2>
			</div>
		</div>
		<div class="row">

			<div class="col-sm-12">
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
			<!-- <div class="col-sm-1 grid-row px-sm-3 p-0 d-sm-block d-none">
                <div class="card_image loading"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card_title loading"></div>
                    <div class="card_icon loading"></div>
                </div>
                <div class="card_content loading mt-0 w-75"></div>
                <div class="card_content loading mt-0 w-50"></div>
                <div class="card_line loading"></div>
                <div class="card_price loading"></div>
            </div> -->
		</div>

	</div>
</section>

<!-- Shimmer Efferct End -->

<!-- html code here -->
<button type="button" class="btn btn-primary d-none" data-toggle="modal" data-target="#login_modal"> Launch demo modal </button>
@if(count($banners))
<section class="home-slider-wrapper pt-md-0 pb-3">

	<div class="container-fluid p-0">
		<div id="myCarousel" class="carousel slide al_desktop_banner" data-ride="carousel">
			<div class="carousel-inner">
				@foreach($banners as $key => $banner)
				@php $url=''; if($banner->link=='category'){if(!empty($banner->category_slug)){$url=route('categoryDetail', $banner->category_slug);}}else if($banner->link=='vendor'){if(!empty($banner->vendor_slug)){$url=route('vendorDetail', $banner->vendor_slug);}}else if($banner->link=='url'){if($banner->link_url !=null){$url=$banner->link_url;}}@endphp
				<div class="carousel-item @if($key == 0) active @endif">
					<a class="banner-img-outer" href="{{$url??'#'}}"  target="_blank">
						<link rel="preload" as="image" href="{{ get_file_path($banner->image,'IMG_URL1','1170','500') }}" />
						<img alt="" title="" class="blur-up lazyload w-100" data-src="{{ get_file_path($banner->image,'IMG_URL1','1914','490') }}">
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
						<link rel="preload" as="image" href="{{ get_file_path($banner->image,'IMG_URL1','400','150') }}" />
						<img alt="" title="" class="blur-up lazyload w-100" data-src="{{ get_file_path($banner->image,'IMG_URL1','400','150') }}">
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



<!-- no-store-wrapper start -->
<section class="no-store-wrapper mb-3 mt-3" style="display: none;">
	<div class="container">
		@if(count($for_no_product_found_html))
		@foreach($for_no_product_found_html as $key => $homePageLabel)
		@include('frontend.included_files.dynamic_page')
		@endforeach
		@else
		<div class="row">
			<div class="col-12 text-center"> <img class="no-store-image mt-2 mb-2 blur-up lazyload" data-src="{{getImageUrl(asset('images/no-stores.svg'),'250/250')}}" style="max-height: 250px;"> </div>
		</div>
		<div class="row">
			<div class="col-12 text-center mt-2">
				<h4>{{__('We are currently not operating in your location.')}}</h4>
			</div>
		</div>
		@endif
	</div>
</section><!-- no-store-wrapper end -->

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
				<img alt="" title="" class="blur-up lazyload w-100" data-src="<%= banner.image.proxy_url %>1370/300<%= banner.image.image_path %>">
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


<!-- vendors_template start -->
<script type="text/template" id="vendors_template">

	<% _.each(vendors, function(vendor, k){%>
		<div class="product-card-box position-relative text-center al_custom_vendors_sec"  >
			<a class="suppliers-box d-block" href="{{route('vendorDetail')}}/<%=vendor.slug %>">
				<div class="suppliers-img-outer position-relative ">
					<% if(vendor.is_vendor_closed==1){%>
						<img class="fluid-img mx-auto blur-up lazyload grayscale-image" data-src="<%=vendor.logo.image_fit %>200/200<%=vendor.logo['image_path'] %>" alt="" title="">
					<%}else{%>
						<img  class="fluid-img mx-auto blur-up lazyload" data-src="<%=vendor.logo.image_fit %>200/200<%=vendor.logo['image_path'] %>" alt="" title="">
					<%}%>

				</div>
				<div class="supplier-rating">
					<h6 class="mb-1 ellips"><%=vendor.name %></h6>
					{{--<p title="<%=vendor.categoriesList %>" class="vendor-cate mb-1 ellips d-none">
						<%=vendor.categoriesList %>
					</p>--}}
						<% if(vendor.timeofLineOfSightDistance !=undefined){%>
							<div class="pref-timing"> <span><%=vendor.timeofLineOfSightDistance %></span> </div>
						<%}%>
				</div>
				@if($client_preference_detail) @if($client_preference_detail->rating_check==1)
				<% if(vendor.vendorRating > 0){%> <span class="rating-number"><%=vendor.vendorRating %> </span>
				<%}%> @endif @endif
			</a>
		</div>
		<% }); %>
</script><!-- vendors_template end -->

<!-- banner_template start -->
<script type="text/template" id="banner_template">
	<% _.each(brands, function(brand, k){%>
		<div  >
			<a class="brand-box d-block black-box" href="<%=brand.redirect_url %>">
				<div class="brand-ing"> <img class="blur-up lazyload" data-src="<%=brand.image.image_fit %>260/260<%=brand.image.image_path %>" alt="" title=""> </div>
				<h6><%=brand.translation_title %></h6> </a>
		</div>
		<% }); %>
</script><!-- banner_template end -->

<!-- products_template start -->
<script type="text/template" id="products_template">
	<% _.each(products, function(product, k){ %>
		<div class="product-card-box position-relative al_box_third_template al"  >
			{{--<div class="add-to-fav 12">
				<input id="fav_pro_one" type="checkbox">
				<label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label>
			</div>--}}
			<a class="common-product-box text-center" href="<%=product.vendor.slug %>/product/<%=product.url_slug %>">
				<div class="img-outer-box position-relative"> <img class="blur-up lazyload" data-src="<%=product.image_url %>" alt="" title="">
					<div class="pref-timing"> </div>
				</div>
				<div class="media-body align-self-start">
					<div class="inner_spacing px-0">
						<div class="product-description">
							<div class="d-flex align-items-center justify-content-between">
								<h6 class="card_title ellips"><%=product.title %></h6> @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
								<% if(product.averageRating > 0){%> <span class="rating-number"><%=product.averageRating %></span>
									<%}%> @endif @endif </div>
							<div class="product-description_list border-bottom">
								<p>
									<%=product.vendor_name %>
								</p>
								<p class="al_product_category">
									<span>
								{{__('In')}}
									<%=product.category %></span>
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between al_clock pt-2">
								<b><% if(product.inquiry_only==0){%> <%=product.price %> <%}%></b>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
		<% }); %>
</script><!-- products_template end -->

<!-- trending_vendors_template start -->
<script type="text/template" id="trending_vendors_template">
	<% _.each(trending_vendors, function(vendor, k){%>
		<div class="product-card-box position-relative text-center al_custom_vendors_sec"  >
			<a class="suppliers-box al_vendors_template2 d-block" href="{{route('vendorDetail')}}/<%=vendor.slug %>">
				<div class="suppliers-img-outer position-relative ">
					<% if(vendor.is_vendor_closed==1){%> <img class="fluid-img mx-auto blur-up lazyload grayscale-image" data-src="<%=vendor.logo.image_fit %>200/200<%=vendor.logo['image_path'] %>" alt="" title="">
						<%}else{%> <img class="fluid-img mx-auto blur-up lazyload w-100" data-src="<%=vendor.logo.image_fit %>200/200<%=vendor.logo['image_path'] %>" alt="" title="">
							<%}%>
				</div>
				<div class="supplier-rating">
					<h6 class="mb-1 ellips"><%=vendor.name %></h6>
					<p title="<%=vendor.categoriesList %>" class="vendor-cate mb-1 ellips d-none">
						<%=vendor.categoriesList %>
					</p>

						<% if(vendor.timeofLineOfSightDistance !=undefined){%>
									<div class="pref-timing"> <span><%=vendor.timeofLineOfSightDistance %></span> </div>
									<%}%>
				</div>
				@if($client_preference_detail) @if($client_preference_detail->rating_check==1)
						<% if(vendor.vendorRating > 0){%>
						<span class="rating-number"><%=vendor.vendorRating %> </span>
						<%}%> @endif @endif
			</a>
		</div>
		<% }); %>
</script><!-- trending_vendors_template end -->

<!-- recent_orders_template start -->
<script type="text/template" id="recent_orders_template">
	<% _.each(recent_orders, function(order, k){ %>
		<% subtotal_order_price = total_order_price = total_tax_order_price = 0; %>
			<% _.each(order.vendors, function(vendor, k){ %>
				<%   product_total_count = product_subtotal_amount = product_taxable_amount = 0; %>
				@include('frontend.common_section.recent_order_j')
					<% }); %>
						<% }); %>
</script><!-- recent_orders_template end -->

<script type="text/template" id="cities_template">
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

<!-- our_vendor_main_div start -->
<section class="section-b-space ratio_asos {{isset($client_preference_detail) && $client_preference_detail->business_type == 'taxi' ? 'taxi' : ''}}" id="our_vendor_main_div">

	<div class="vendors">
		@foreach($homePageLabels as $key => $homePageLabel)
		@if($homePageLabel->slug == 'pickup_delivery')
		@if(isset($homePageLabel->pickupCategories) && count($homePageLabel->pickupCategories)) @include('frontend.booking.cabbooking-single-module') @endif
		@elseif($homePageLabel->slug == 'dynamic_page') @include('frontend.included_files.dynamic_page')
		@elseif($homePageLabel->slug == 'brands' && (count($homePageData['brands']) != 0))
		<section class="p2p-full-width popular-brands left-shape_ position-relative section-space">
			<div class="container">
				<div class="top-heading al_top_heading text-center d-flex align-items-center">
					<h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('brands', true)}}</h2>
					<a class="" href="{{route('brand.all')}}">See All  <i class="fa fa-angle-right" aria-hidden="true"></i> </a>
				</div>
				<div class="">
					<div class="al_custom_brand">
						<div class="alNewSliderTopBrand render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
							@foreach ($homePageData[$homePageLabel->slug] as $brand )
							@include('frontend.home_page_8.brands')
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</section>
		@elseif($homePageLabel->slug == 'cities' && (count($homePageData['cities']) != 0))
		<section class="suppliers-section container render_full_{{$homePageLabel->slug}} section-space">
			<div class=" top-heading d-flex justify-content-between align-self-center">
				<h2 class="col h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : 'Cities'}}</h2>
			</div>
			<div class="col-12">
				<div class="suppliers-slider-{{$homePageLabel->slug}} product-m renderqw_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
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
		@elseif($homePageLabel->slug == 'vendors' && (count($homePageData['vendors']) != 0))
		<section class="suppliers-section  vender_slider section-space">
			<div class="container">
				<!-- <div class=" top-heading d-flex justify-content-between align-self-center">
					<h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('vendors', true)}}</h2>
					<a class="" href="{{route('vendor.all')}}">{{__("See all")}} <img class="btn-arrow" src="{{asset('images/template-8/arrow.png')}}" alt="" title=""></a>
				</div> -->
				<div class=" top-heading">
					<h2 class="text-left h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('Recommended Vendor', true)}}</h2>
					<a class="" href="{{route('vendor.all')}}">{{__("See All")}} <i class="fa fa-angle-right" aria-hidden="true"></i></a>
				</div>
				<div class="row eccomerce-temp">
					<div class="col-12">
						<div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
							@foreach ($homePageData[$homePageLabel->slug] as $vendor )
							@include('frontend.home_page_8.vendor')
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</section>
		@elseif($homePageLabel->slug == 'trending_vendors' && (count($homePageData['trending_vendors']) != 0))
		<section class="suppliers-section container section-space" id="homepage_trending_vendors_div">
			<div class=" top-heading ">
				<h2 class="h2-heading">{{$homePageLabel->slug=='trending_vendors' ? __('Trending')." ".getNomenclatureName('vendors', true) : __($homePageLabel->title)}}</h2>
			</div>
			<div class="row">
				<div class="col-12 p-0">
					<div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
						@foreach ($homePageData[$homePageLabel->slug] as $vendor )
						@include('frontend.home_page_8.vendor')
						@endforeach
					</div>
				</div>
			</div>
		</section>
		@elseif($homePageLabel->slug == 'recent_orders' && count($homePageData['recent_orders']) != 0 )
		<section class="best-seller-full render_full_{{$homePageLabel->slug}} section-space" id="{{$homePageLabel->slug.$key}} ">
            <div class="container">
                <div class="top-heading d-flex justify-content-between">
                    <h2 class="h2-heading"> @php
                        echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __("Your Recent Orders");
                        @endphp </h2>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="recent-orders-8 product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                            @foreach ($homePageData[$homePageLabel->slug] as $order )
                            @include('frontend.common_section.recent_order')
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
		</section>
        @elseif($homePageLabel->slug == 'recently_viewed' && (count($homePageData['recently_viewed']) != 0))
		<section class="main-pro-slider p2p-full-width suppliers-section section-space" id="homepage_recently_viewed">
			<div class="container">
				<div class=" top-heading pt-5">
					<h2 class="h2-heading">{{$homePageLabel->slug=='recently_viewed' ? __('Recently')." ".getNomenclatureName('Viewed', true) : __($homePageLabel->title)}}</h2>
					{{-- <a class="" href="">See All  <i class="fa fa-angle-right" aria-hidden="true"></i> </a> --}}
				</div>
				<div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
					@foreach ($homePageData[$homePageLabel->slug] as $product )
					@include('frontend.home_page_8.recently_viewed')
					@endforeach
				</div>
			</div>
		</section>
		@elseif($homePageLabel->slug == 'top_rated' && (count($homePageData['top_rated']) != 0))
		<section class="main-pro-slider suppliers-section section-space" id="homepage_top_rated">
			<div class="container p2p-full-width">
				<div class="row ">
					<div class="col-md-12">
						<div class=" top-heading text-center d-flex justify-content-between">
							<h2 class="col h2-heading">{{$homePageLabel->slug=='top_rated' ? __('Top')." ".getNomenclatureName('Rated', true) : __($homePageLabel->title)}}</h2>
							{{-- <a class="" href="">See All  <i class="fa fa-angle-right" aria-hidden="true"></i> </a> --}}
						</div>
					</div>
				</div>
				<!-- suppliers-slider-{{$homePageLabel->slug}} -->
					<div class="product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
						<div class="row top_rated_slider">
						@foreach ($homePageData[$homePageLabel->slug] as $product )
							@include('frontend.home_page_8.top_rated')
						@endforeach
						</div>
					</div>
				</div>
		</section>
		{{--  && (count($homePageData['nav_categories']) != 0) --}}
		@elseif(!empty($homePageLabel->nav_categories) && $homePageLabel->slug == 'nav_categories' && (count($homePageLabel->nav_categories) != 0))
		<section class="popular-brands left-shape_ position-relative bg-light-gray section-space">
			<div class="container-fluid">
				<div class="al_top_heading text-center d-flex align-items-center">
					<h2 class="col h2-heading text-center">{{$homePageLabel->slug=='nav_categories' ? __('Categories') : __($homePageLabel->title)}}</h2>
				</div>
				<div class="row">
					<div class=" col-12 al_custom_categories">
						<div class="render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
							<div class="col-12 categories_sliderEight">
								@foreach ( $homePageLabel->nav_categories as $category )
									@include('frontend.home_page_8.category')
								@endforeach
							<!-- </div> -->
						</div>
					</div>
				</div>
			</div>
		</section>
        @elseif($homePageLabel->slug == 'spotlight_deals' && (count($homePageData['spotlight_deals']) != 0))

		<section class="mb-0 render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
			<div class="container full_width_eccomerce">
				<div class="top-heading d-flex justify-content-between">
					<h2 class="col h2-heading"> @php
						echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);
						@endphp </h2>
						<!-- <a class="" href="">See All  <i class="fa fa-angle-right" aria-hidden="true"></i> </a>  -->
				</div>
				<div class="product-4-{{$homePageLabel->slug}} product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
					<div class="row spotlight_deals-eight">
							@foreach ($homePageData[$homePageLabel->slug] as $product )
							@include('frontend.home_page_8.spotlight_deals')
							@endforeach
						</div>
				</div>
			</div>
		</section>
		@elseif($homePageLabel->slug == 'best_sellers' && (count($homePageData['best_sellers']) != 0))
		<section class=" best-seller-eccomerce render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}} section-space" >
			<div class="container">
				<div class="top-heading d-flex justify-content-between">
					<h2 class=" h2-heading"> @php
						echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);
						@endphp </h2>
				</div>
				<div class="">
					<div class="">
						<div class="product-4-{{$homePageLabel->slug}} product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
							@foreach ($homePageData[$homePageLabel->slug] as $vendor )
							@include('frontend.home_page_8.best_sellers')
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</section>
		@elseif($homePageLabel->slug == 'single_category_products' && (count($homePageData[$homePageLabel->slug]) != 0))
		<section class="main-product single_category_products section-space render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
			<div class="container p2p-full-width">
			<div class="top-heading d-flex justify-content-between">
			<h2 class="col h2-heading mb-3"> @php
				echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);
				@endphp </h2>
					<!-- <a class="" href="">See All  <i class="fa fa-angle-right" aria-hidden="true"></i> </a>  -->
			</div>
			<div class="row">
				<div class="col-12">
					<div class="Product-4-{{$homePageLabel->slug}} product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
						<div class="row p2p_single_slider">
							@foreach ($homePageData[$homePageLabel->slug] as $product )
							@include('frontend.home_page_8.single_category_products')
							@endforeach
						</div>
					</div>
				</div>
			</div>
			</div>
		</section>
		</section>
		@elseif(@$homePageLabel->slug == 'selected_products' && (@count(@$homePageData[@$homePageLabel->slug]) != 0))
		<section class="main-product single_category_products section-space render_full_{{$homePageLabel->slug}} bg-light-gray" id="{{$homePageLabel->slug.$key}}">
			<div class="container p2p-full-width">
				<div class="top-heading d-flex justify-content-between">
					<h2 class="col h2-heading mt-3"> @php
						echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);
						@endphp </h2>
						{{-- <a class="mt-3" href="">See All  <i class="fa fa-angle-right" aria-hidden="true"></i> </a> --}}
				</div>
				<div class="row">
					<div class="col-12">
						<div class="product-4-{{$homePageLabel->slug}} product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
					</div>
				</div>
				<div class="row selected_products-slider">
					@foreach ($homePageData[$homePageLabel->slug] as $product )
					@include('frontend.home_page_8.selected_products')
					@endforeach
				</div>
			</div>
		</section>
		@elseif($homePageLabel->slug == 'most_popular_products' && (count($homePageData[$homePageLabel->slug]) != 0))
		{{-- <section class="main-product single_category_products mb-0 render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
			<div class="container p2p-full-width">
				<div class="top-heading d-flex justify-content-between">
					<h2 class="col pl-0 h2-heading mb-3"> @php
						echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);
						@endphp </h2>
						<!-- <a class="" href="">See All  <i class="fa fa-angle-right" aria-hidden="true"></i></a>  -->
				</div>
				<div class="product-4-{{$homePageLabel->slug}} product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
					<div class=" selling_product_eight">
						@foreach ($homePageData[$homePageLabel->slug] as $product )
						@include('frontend.home_page_8.most_popular_products')
						@endforeach
					</div>
				</div>
			</div>
		</section> --}}
		@elseif($homePageLabel->slug == 'banner' && (count($homePageData['banners']) != 0))
		@if(!empty(@$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]))
			<section class="section-space render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"  >
				<div class="container p2p-full-width">
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
									@php
										if(!empty($homePageData['banners']['url_'.optional($homePageLabel->translations->first())->cab_booking_layout_id])){
											$url = $homePageData['banners']['url_'.optional($homePageLabel->translations->first())->cab_booking_layout_id];
										}else{
											$url = 'javascript:void(0)';
										}
									@endphp
									<a href="{{$url}}"><img alt="" title="" class="blur-up lazyload w-100" src="{{$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]}}"></a>
								@elseif (in_array($extension, $video_extensions))
									<video id="video1" width="100%" controls autoplay muted>
										<source src="{{$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]}}" type="video/mp4">
									</video>
								@else
								@endif
							</div>
						</div>
					</div>
				</div>
			</section>
		@endif
		@else
		@if(!empty($homePageData[$homePageLabel->slug]) && count($homePageData[$homePageLabel->slug]) != 0)
		<!-- <section class="home-headings">
			<div class="container">
				<div class="row">
					<div class="col-md-12 text-center">
						<div class="top-heading d-flex justify-content-between">
							<h2 class="h2-heading"> @php
								echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);
								@endphp </h2>
								<a class="" href="">See All  <img class="" src="{{asset('images/template-8/arrow.png')}}" alt="" title=""> </a>
						</div>
					</div>
				</div>
			</div>
		</section> -->
		<section class="main-product render_full_{{$homePageLabel->slug}}  section-space" id="{{$homePageLabel->slug.$key}}">
			<div class="container p2p-full-width">
				<div class="row ">
					<div class="col-md-12 text-center">
						<div class="top-heading d-flex justify-content-between">
							<h2 class="mt-0 h2-heading"> @php
								echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);
								@endphp </h2>
								<!-- <a class="" href="">See All  <i class="fa fa-angle-right" aria-hidden="true"></i> </a>  -->
						</div>
					</div>
				</div>
			<div class="product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
				<div class="  {{(($homePageLabel->slug =='featured_products')?'p2p_eccomerce_slider10':'p2p_eccomerce_slider')}}">
							@foreach ($homePageData[$homePageLabel->slug] as $product )
							@include('frontend.home_page_8.product')
							@endforeach
					</div>
				</div>
			</div>
		</section>
		@endif
		@endif @endforeach


	</div>
</section><!-- our_vendor_main_div end -->



<!-- age-restriction star -->
<div class="modal age-restriction fade" id="age_restriction" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body text-center"> <img style="height: 150px;" class="blur-up lazyload" data-src="{{getImageUrl(asset('assets/images/age-img.svg'),'150/150')}}" alt="" title="">
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
</div><!-- age-restriction end -->

<!-- footer code in layouts.store/footercontent-template-two -->
@section('home-page')
{{-- <script type="text/javascript" src="{{asset('front-assets/js/homepage-three.js')}}"></script> --}}
<script type="text/javascript" src="{{asset('assets/js/template/commonFunction.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/template/template-eight/templateFunction.js')}}"></script>
@endsection
@endsection
@section('js-script')
{{--<script type="text/javascript" src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/fly-cart.js')}}"></script>
<script type="text/javascript" src="{{asset('js/aos.js')}}"></script>--}}
@endsection
@section('script')
@endsection
