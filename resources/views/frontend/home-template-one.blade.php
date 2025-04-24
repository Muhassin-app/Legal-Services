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
               <a class="banner-img-outer" href="{{$url??'#'}}">
               <link rel="preload" as="image" href="{{ get_file_path($banner->image,'IMG_URL1','1370','300') }}" />
                  <img alt="" title="" class="blur blurload w-100" data-src="{{ get_file_path($banner->image,'IMG_URL1','1370','300') }}" src="{{ get_file_path($banner->image,'IMG_URL1','137','30') }}">
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
   				<div class="order_detail order_detail_data align-items-top pb-3 card-box no-gutters mb-2">
   					<% if((vendor.delivery_fee > 0) || (order.scheduled_date_time)){%>
   						<div class="progress-order font-12">
   							<% if(order.scheduled_slot==null){%>
   								<% if(order.scheduled_date_time){%> <span class="badge badge-success ml-2">Scheduled</span> <span class="ml-2">{{__('Your order will arrive by ')}}<%=order.converted_scheduled_date_time %></span>
   									<%}else{%> <span class="ml-2">{{__('Your order will arrive by ')}}<%=vendor.ETA %></span>
   										<%}%>
   											<%}else{%> <span class="badge badge-success ml-2">Scheduled</span> <span class="ml-2">{{__('Your order will arrive by ')}}<%=order.converted_scheduled_date_time %>, Slot : <%=order.scheduled_slot %></span>
   												<%}%>
   						</div>
   						<%}%> <span class="left_arrow pulse"></span>
   							<div class="row">
   								<div class="col-5 col-sm-3">
   									<h5 class="m-0">{{__('Order Status')}}</h5>
   									<ul class="status_box mt-1 pl-0">
   										<% if(vendor.order_status){%>
   											<li>
   												<% if(vendor.order_status=='placed'){%> <img class="blur blurload" data-src="{{asset('assets/images/order-icon.svg')}}" alt="" title="">
   													<%}else if(vendor.order_status=='accepted'){%> <img class="blur blurload" data-src="{{asset('assets/images/payment_icon.svg')}}" alt="" title="">
   														<%}else if(vendor.order_status=='processing'){%> <img class="blur blurload" data-src="{{asset('assets/images/customize_icon.svg')}}" alt="" title="">
   															<%}else if(vendor.order_status=='out for delivery'){%> <img class="blur blurload" data-src="{{asset('assets/images/driver_icon.svg')}}" alt="" title="">
   																<%}%>
   																	<label class="m-0 in-progress">
   																		<%=(vendor.order_status).charAt(0).toUpperCase() + (vendor.order_status).slice(1) %>
   																	</label>
   											</li>
   											<%}%>
   												<% if(vendor.dispatch_traking_url){%> <img class="blur blurload" data-src="{{asset('assets/images/order-icon.svg')}}" alt="" title=""> <a href="{{route('front.booking.details')}}/<%=order.order_number %>" target="_blank">{{__('Details')}}</a>
   													<%}%>
   														<% if(vendor.dineInTable){%>
   															<li>
   																<h5 class="mb-1">{{__('Dine-in')}}</h5>
   																<h6 class="m-0"><%=vendor.dineInTableName %></h6>
   																<h6 class="m-0">Category : <%=vendor.dineInTableCategory %></h6>
   																<h6 class="m-0">Capacity : <%=vendor.dineInTableCapacity %></h6> </li>
   															<%}%>
   									</ul>
   								</div>
   								<div class="col-7 col-sm-4">
   									<ul class="product_list d-flex align-items-center p-0 flex-wrap m-0">
   										<% _.each(vendor.products, function(product, k){%>
   											<% if(vendor.vendor_id==product.vendor_id){%>
   												<li class="text-center"> <img class="blur blurload" data-src="<%=product.image_url %>" alt="" title=""> <span class="item_no position-absolute">x <%=product.quantity %></span>
   													<label class="items_price">{{Session::get('currencySymbol')}}
   														<%= Helper.formatPrice(product.price * product.pricedoller_compare) %>
   													</label>
   												</li>
   												<% product_total_price=product.price * product.doller_compare; product_total_count +=product.quantity * product_total_price; product_taxable_amount +=product.taxable_amount; total_tax_order_price +=product.taxable_amount; %>
   													<%}%>
   														<%}); %>
   									</ul>
   								</div>
   								<div class="col-md-5 mt-md-0 mt-sm-2">
   									<ul class="price_box_bottom m-0 p-0">
   										<li class="d-flex align-items-center justify-content-between">
   											<label class="m-0">{{__('Product Total')}}</label> <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor.subtotal_amount) %></span> </li>
   										<li class="d-flex align-items-center justify-content-between">
   											<label class="m-0">{{__('Coupon Discount')}}</label> <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor.discount_amount) %></span> </li>
   										<li class="d-flex align-items-center justify-content-between">
   											<label class="m-0">{{__('Delivery Fee')}}</label> <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor.delivery_fee) %></span> </li>
   										<li class="grand_total d-flex align-items-center justify-content-between">
   											<label class="m-0">{{__('Amount')}}</label>
   											<% product_subtotal_amount=product_total_count - vendor.discount_amount + vendor.delivery_fee; subtotal_order_price +=product_subtotal_amount; %> <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor.payable_amount) %></span> </li>
   									</ul>
   								</div>
   							</div>
   				</div>
   				<% }); %>
   					<% }); %>
</script>
<section class="section-b-space ratio_asos d-none pt-0 mt-0 pb-0 mt-0" id="our_vendor_main_div">
   <div class="vendors">
      @foreach($homePageLabels as $key => $homePageLabel) @if($homePageLabel->slug == 'pickup_delivery')
      @if(isset($homePageLabel->pickupCategories) && count($homePageLabel->pickupCategories))
      @include('frontend.booking.cabbooking-single-module') @endif
      @elseif($homePageLabel->slug == 'dynamic_page') @include('frontend.included_files.dynamic_page')
      @elseif($homePageLabel->slug == 'brands')
      <section class="popular-brands left-shape_ position-relative">
         <!-- <div class="container ">
            <div class="row align-items-center">
            	<div class="col-lg-2 cw top-heading pr-0 text-center text-lg-left mb-3 mb-lg-0">
            		<h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('brands', true)}}</h2> </div>
            	<div class="col-lg-10 al_custom_brand">
            		<div class="brand-slider render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
            	</div>
            </div>
            </div> -->
         <div class="container "  >
            <div class="al_top_heading col-md-12">
               <div class="row d-flex justify-content-between">
                  <h2 class="h2-heading text-capitalize">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('brands', true)}}</h2>
                  {{-- <a class="" href="">See All</a> --}}
               </div>
            </div>
            <div class="row ">
               <div class=" col-md-12 al_custom_brand">
                  <div class=" brand-slider render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
               </div>
            </div>
         </div>
      </section>
      @elseif($homePageLabel->slug == 'vendors')
      <section class="suppliers-section">
         <div class="container"  >
            <div class="row">
               <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                  <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('vendors', true)}}</h2>
                  <a class="" href="{{route('vendor.all')}}">{{__("See all")}}</a>
               </div>
               <div class="col-12">
                  <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
               </div>
            </div>
         </div>
      </section>
      @elseif($homePageLabel->slug == 'cities')
      <section class="suppliers-section container  render_full_{{$homePageLabel->slug}} d-none ">
         <div class=" top-heading d-flex justify-content-between align-self-center">
            <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : 'Cities'}}</h2>
         </div>
         <div class="col-12 p-0">
            <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
            </div>
         </div>
      </section>
      @elseif($homePageLabel->slug == 'trending_vendors')
      <section class="suppliers-section">
         <div class="container"  >
            <div class="row">
               <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                  <h2 class="h2-heading">{{$homePageLabel->slug=='trending_vendors' ? __('Trending')." ".getNomenclatureName('vendors', true) : __($homePageLabel->title)}}</h2>
               </div>
               <div class="col-12">
                  <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
               </div>
            </div>
         </div>
      </section>
      @else
      <section class="container mb-0 render_full_{{$homePageLabel->slug}} d-none" id="{{$homePageLabel->slug.$key}}"  >
         <div class="row" >
            <div class="col-12 top-heading d-flex align-items-center justify-content-between">
               <h2 class="h2-heading"> @php if($homePageLabel->slug=='vendors'){echo getNomenclatureName('vendors', true);}elseif($homePageLabel->slug=='recent_orders'){echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __("Your Recent Orders");}else{echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);}@endphp </h2>
               @if($homePageLabel->slug=='vendors') <a class="" href="{{route('vendor.all')}}">{{__('View More')}}</a> @endif
            </div>
         </div>
         <div class="row">
            <div class="col-12">
               @if($homePageLabel->slug=='vendors' || $homePageLabel->slug=='trending_vendors')
               <div class="product-5 product-m no-arrow render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
               @elseif($homePageLabel->slug=='recent_orders')
               <div class="recent-orders product-m no-arrow render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
               @else
               <div class="product-4-{{$homePageLabel->slug}} product-m no-arrow render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
               @endif
            </div>
         </div>
      </section>
      @endif @endforeach
   </div>
</section>
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
@endsection
@section('js-script')
<script type="text/javascript" src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/fly-cart.js')}}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/less@4"></script>
<script>
	var featured_products_length = {{ isset($homePageData['featured_products']) ? count($homePageData['featured_products']) : ''}};
</script>
{{--<script type="text/javascript" src="{{asset('js/aos.js')}}"></script>--}}
@endsection
@section('script')
<script type="text/javascript">
   // AOS.init();
   // 	$('.center').slick({
   //   centerMode: true,
   //   centerPadding: '60px',
   //   slidesToShow: 2,
   //   responsive: [
   //     {breakpoint: 768, settings: { arrows: false, centerMode: true, centerPadding: '40px', slidesToShow: 2}},
   //     {breakpoint: 480, settings: { arrows: false, centerMode: true, centerPadding: '40px', slidesToShow: 1}}
   //   ]
   // });
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