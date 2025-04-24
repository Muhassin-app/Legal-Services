@extends('layouts.store', ['title' => __('Home')])
@section('css-links')
@endsection
@section('cssnew')
<style>
.menu-slider .slick-slide{margin:0 10px;}
.alSpaCityBox {
    position: relative;
    padding-bottom: 75%;
    background-color: #f7f7f7;
    border-radius: 15px;
    box-shadow: 0 0 10px rgba(0,0,0,.2);
    overflow: hidden;
}
.alSpaCityBox img {
    position: absolute;
    height: 100%;
    width: 100%;
    object-fit: contain;
}
</style>
@endsection
@section('content')
<!-- shimmer_effect start -->
<section class="section-b-space_  p-0 ratio_asos alHomeServiceShimmer ">
	<div class="container_al mb-3 shimmer_effect main_shimer">
		<div class="row">
			<div class="col-12 cards">
				<div class="headerLoding loading"></div>
            <div class="cardbanner loading"></div>
			</div>
		</div>
	</div>
   <div class="container mb-3 shimmer_effect alcardCatagory main_shimer">
		<div class="row">
			<div class="col-2 cards">
				<div class="cardCatagory loading"></div>
			</div>
         <div class="col-2 cards">
				<div class="cardCatagory loading"></div>
			</div>
         <div class="col-2 cards">
				<div class="cardCatagory loading"></div>
			</div>
         <div class="col-2 cards">
				<div class="cardCatagory loading"></div>
			</div>
         <div class="col-2 cards">
				<div class="cardCatagory loading"></div>
			</div>
         <div class="col-2 cards">
				<div class="cardCatagory loading"></div>
			</div>

		</div>
	</div>

   <div class="container mb-3 shimmer_effect  main_shimer">
         <div class="row">
            <div class="col-12">
               <div class="card_title ml-4 mb-4 maintitle loading"></div>
            </div>
         </div>
         <div class="row">
            <div class="col-12 px-md-5 px-2">
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
         </div>
      </div>


	</div>
</section>
<!-- shimmer_effect end -->
<button type="button" class="btn btn-primary d-none" data-toggle="modal" data-target="#login_modal"> Launch demo modal </button>
@if(count($banners))
<section class="home-slider-wrapper">

	<div class="container-fulid">
		<div id="myCarousel" class="carousel carousel-fade slide al_desktop_banner" data-ride="carousel">
			<div class="carousel-inner">
				@foreach($banners as $key => $banner)
					@php $url=''; if($banner->link=='category'){if($banner->category !=null){$url=route('categoryDetail', $banner->category->slug);}}else if($banner->link=='vendor'){if($banner->vendor !=null){$url=route('vendorDetail', $banner->vendor->slug);}}@endphp
					<div class="carousel-item @if($key == 0) active @endif">
					 <a class="banner-img-outer" href="{{$url??'#'}}">
                  <link rel="preload" as="image" href="{{$banner->image['proxy_url'] . '1900/300' . $banner->image['image_path']}}" />
						<img alt="" title="" class="blur-up lazyload w-100" data-src="{{$banner->image['proxy_url'] . '1900/300' . $banner->image['image_path']}}">
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
<section class="home-slider-wrapper" style="min-height: 150px">
   <div class="container-fulid">
		<div id="myCarousel" class="carousel slide al_desktop_banner" data-ride="carousel"></div>
      <div id="myMobileCarousel" class="carousel slide al_mobile_banner mb-2" data-ride="carousel" style="display:none;"></div>
   </div>
</section>
@endif
<section class="alSixMainMenu p-0">
   @if(count($navCategories))
   <div class="menu-navigation_al test">
   <div class="container-fulid">
      <div class="row">
         <div class="col-12">
            <ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider" >
               @foreach($navCategories as $cate)
               @if($cate['name'])
               <li class="al_main_category">
                  <a href="{{route('categoryDetail', $cate['slug'])}}" class="{{isset($category) && $category->slug == $cate['slug'] ? 'current_category' : ''}}">
                     @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='userHome' || \Request::route()->getName()=='categoryDetail'))
                     <div class="nav-cate-img {{ \Request::route()->getName()=='userHome' ? '' : 'activ_nav'}} " >
                        <img class="blur-up lazyload" data-icon_two="{{!is_null($cate['icon_two']) ? $cate['icon_two']['image_fit'].'200/200'.$cate['icon_two']['image_path'] : $cate['icon']['image_fit'].'200/200'.$cate['icon']['image_path']}}" data-icon="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" data-src="{{$cate['icon']['image_fit']}}150/150{{$cate['icon']['image_path']}}" alt="" onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'>
                     </div>
                     @endif
                     <span class="alCategoryName">{{$cate['name']}}</span>
                  </a>
                  @if(!empty($cate['children']))
                  <ul class="al_main_category_list">
                     @foreach($cate['children'] as $childs)
                     <li>
                        <a href="{{route('categoryDetail', $childs['slug'])}}"><span class="new-tag">{{$childs['name']}}</span></a>
                        @if(!empty($childs['children']))
                        <ul class="al_main_category_sub_list">
                           @foreach($childs['children'] as $chld)
                           <li><a href="{{route('categoryDetail', $chld['slug'])}}">{{$chld['name']}}</a></li>
                           @endforeach
                        </ul>
                        @endif
                     </li>
                     @endforeach
                  </ul>
                  @endif
               </li>
               @endif
               @endforeach
            </ul>
         </div>
      </div>
   </div>
   @endif
</section>

<!-- no-store-wrapper start -->
<section class="no-store-wrapper mb-3 mt-5 pt-5" style="display: none;">
   <div class="container">
      @if(count($for_no_product_found_html))
      @foreach($for_no_product_found_html as $key => $homePageLabel)
      @include('frontend.included_files.dynamic_page')
      @endforeach
      @else
      <div class="row">
         <div class="col-12 text-center">
            <img class="no-store-image mt-2 mb-2 blur-up lazyload" data-src="{{getImageUrl(asset('images/no-stores.svg'),'250/250')}}" style="max-height: 250px;">
         </div>
      </div>
      <div class="row">
         <div class="col-12 text-center mt-2">
            <h4>{{__('We are currently not operating in your location.')}}</h4>
         </div>
      </div>
      @endif
   </div>
</section>
<!-- no-store-wrapper end -->
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
				<link rel="preload" as="image" href="<%= banner.image.proxy_url %>sw/300<%= banner.image.image_path %>" />
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
<script type="text/template" id="vendors_template" >
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
   			<% if(vendor.vendorRating > 0){%> <span class="rating-number"><i class="fa fa-star"></i> <%=vendor.vendorRating %> </span>
   			<%}%> @endif @endif
   		</a>
   	</div>
   	<% }); %>
</script><!-- vendors_template end -->
<!-- banner_template start -->
<script type="text/template" id="banner_template" >
   <% _.each(brands, function(brand, k){%>
   	<div>
   		<a class="brand-box d-block black-box" href="<%=brand.redirect_url %>">
   			<div class="brand-ing">
                      <img class="blur-up lazyload" data-src="<%=brand.image.image_fit %>260/260<%=brand.image.image_path %>" alt="" title="">
                  </div>
   			<h6><%=brand.translation_title %></h6>
              </a>
   	</div>
   	<% }); %>
</script><!-- banner_template end -->
<!-- products_template start -->
<script type="text/template" id="products_template" >
   <% _.each(products, function(product, k){ %>
   	<div class="product-card-box position-relative al_box_third_template al"  >
   		{{--<div class="add-to-fav 12">
   			<input id="fav_pro_one" type="checkbox">
   			<label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label>
   		</div>--}}
   		<a class="common-product-box text-center" href="<%=product.vendor.slug %>/product/<%=product.url_slug %>">
   			<div class="img-outer-box position-relative">
                      <img class="blur-up lazyload" data-src="<%=product.image_url %>" alt="" title="">
   				<div class="pref-timing"> </div>
   			</div>
   			<div class="media-body align-self-start">
   				<div class="inner_spacing px-0">
   					<div class="product-description">
   						<div class="d-flex align-items-center justify-content-between">
   							<h6 class="card_title ellips"><%=product.title %></h6> @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
   							<% if(product.averageRating > 0){%> <span class="rating-number"><i class="fa fa-star"></i> <%=product.averageRating %></span>
   								<%}%> @endif @endif
                              </div>
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
<script type="text/template" id="trending_vendors_template" >
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
   					<span class="rating-number"><i class="fa fa-star"></i> <%=vendor.vendorRating %> </span>
   					<%}%> @endif @endif
   		</a>
   	</div>
   	<% }); %>
</script><!-- trending_vendors_template end -->
<!-- recent_orders_template start -->
<script type="text/template" id="recent_orders_template"  >
   <% _.each(recent_orders, function(order, k){ %>
   	<% subtotal_order_price = total_order_price = total_tax_order_price = 0; %>
   		<% _.each(order.vendors, function(vendor, k){ %>
   			<%   product_total_count = product_subtotal_amount = product_taxable_amount = 0; %>
   				<div class="order_detail order_detail_data align-items-top pb-3 card-box no-gutters mb-0"  >
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
                                       <% if(vendor.order_status=='placed'){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/order-icon.svg')}}" alt="" title="">
                                       <%}else if(vendor.order_status=='accepted'){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/payment_icon.svg')}}" alt="" title="">
                                       <%}else if(vendor.order_status=='processing'){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/customize_icon.svg')}}" alt="" title="">
                                       <%}else if(vendor.order_status=='out for delivery'){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/driver_icon.svg')}}" alt="" title="">
                                       <%}%>
                                       <label class="m-0 in-progress">
                                       <%=(vendor.order_status).charAt(0).toUpperCase() + (vendor.order_status).slice(1) %>
                                       </label>
   											</li>
   											<%}%>
                                    <li>
                                    <% if(vendor.dispatch_traking_url){%>
                                       <img class="blur-up lazyload" data-src="{{asset('assets/images/order-icon.svg')}}" alt="" title="">
                                       <a class="alOrderDetailsLink" href="{{route('front.booking.details')}}/<%=order.order_number %>" target="_blank">{{__('Details')}}</a>
                                    <%}%>
                                    </li>
                                    <% if(vendor.dineInTable){%>
                                    <li>
                                       <h5 class="mb-1">{{__('Dine-in')}}</h5>
                                       <h6 class="m-0"><%=vendor.dineInTableName %></h6>
                                       <h6 class="m-0">Category : <%=vendor.dineInTableCategory %></h6>
                                       <h6 class="m-0">Capacity : <%=vendor.dineInTableCapacity %></h6>
                                    </li>
                                    <%}%>
   									</ul>
   								</div>
   								<div class="col-7 col-sm-4">
   									<ul class="product_list d-flex align-items-center p-0 flex-wrap m-0">
   										<% _.each(vendor.products, function(product, k){%>
   											<% if(vendor.vendor_id==product.vendor_id){%>
   												<li class="text-center"> <img class="blur-up lazyload" data-src="<%=product.image_url %>" alt="" title=""> <span class="item_no position-absolute">x <%=product.quantity %></span>
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
</script><!-- recent_orders_template end -->
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
<!-- our_vendor_main_div start -->
<section class="section-b-space ratio_asos d-none pt-0 mt-0 pb-0 {{isset($client_preference_detail) && $client_preference_detail->business_type == 'taxi' ? 'taxi' : ''}}" id="our_vendor_main_div" >
   <div class="vendors">
   @foreach ($enable_layout as $enabled)
      <div class="container mb-3 shimmer_effect  shimmer_effect_{{ $enabled  }}">
         <div class="row">
            <div class="col-12">
               <div class="card_title ml-4 mb-4 maintitle loading"></div>
            </div>
         </div>
         <div class="row">
            <div class="col-12 px-md-5 px-2">
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
         </div>
      </div>
   @endforeach
      @foreach($homePageLabels as $key => $homePageLabel) @if($homePageLabel->slug == 'pickup_delivery') @if(isset($homePageLabel->pickupCategories) && count($homePageLabel->pickupCategories)) @include('frontend.booking.cabbooking-single-module') @endif @elseif($homePageLabel->slug == 'dynamic_page') @include('frontend.included_files.dynamic_page') @elseif($homePageLabel->slug == 'brands')
      <section class="container popular-brands left-shape_ position-relative "  >
         <div class="al_top_heading d-flex justify-content-between">
            <h2 class="h2-heading text-capitalize">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('brands', true)}}</h2>
            {{-- <a class="" href="">See All</a> --}}
         </div>
         <div class="row">
            <div class=" col-12 al_custom_brand p-0">
               <div class=" brand-slider render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
            </div>
         </div>
      </section>
      @elseif($homePageLabel->slug == 'vendors')
      <section class="suppliers-section container  mt-4">
         <div class=" top-heading d-flex justify-content-between align-self-center">
            <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('vendors', true)}}</h2>
            <a class="" href="{{route('vendor.all')}}">{{__("See all")}}</a>
         </div>
         <div class="col-12 p-0">
            <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
            </div>
         </div>
      </section>
      @elseif($homePageLabel->slug == 'cities')
      <section class="suppliers-section container render_full_{{$homePageLabel->slug}} d-none">
         <div class=" top-heading d-flex justify-content-between align-self-center">
            <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : 'Cities'}}</h2>
         </div>
         <div class="col-12 p-0">
            <div class="suppliers-slider-{{$homePageLabel->slug}} d-none product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
            </div>
         </div>
      </section>
      @elseif($homePageLabel->slug == 'trending_vendors')
      <section class="suppliers-section container" id="homepage_trending_vendors_div">
         <div class=" top-heading ">
            <h2 class="h2-heading">{{$homePageLabel->slug=='trending_vendors' ? __('Trending')." ".getNomenclatureName('vendors', true) : __($homePageLabel->title)}}</h2>
         </div>
         <div class="row">
            <div class="col-12 p-0">
               <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
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
                        <img alt="" title="" class="blur-up lazyload w-100" src="{{$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]}}" >	
                     @elseif (in_array($extension, $video_extensions))
                        <video id="video1" width="100%" controls autoplay muted>
                           <source src="{{$homePageData['banners'][$homePageLabel->translations->first()->cab_booking_layout_id]}}" type="video/mp4">
                        </video>
                     @else
                     @endif
                  </div>
               </div>
            </div>
         </section>
      @endif
      @else
      <section class="container mb-0 render_full_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"  >
         <div class="top-heading d-flex justify-content-between">
            <h2 class="h2-heading"> @php if($homePageLabel->slug=='vendors'){echo getNomenclatureName('vendors', true);}elseif($homePageLabel->slug=='recent_orders'){echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __("Your Recent Orders");}else{echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);}@endphp </h2>
            @if($homePageLabel->slug=='vendors') <a class="" href="{{route('vendor.all')}}">{{__('View More')}}</a>
            @endif
         </div>
         <div class="row">
            <div class="col-12">
               @if($homePageLabel->slug=='vendors' || $homePageLabel->slug=='trending_vendors')
               <div class="product-5 product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
               @elseif($homePageLabel->slug=='recent_orders')
               <div class="recent-orders product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
               @else
               <div class="product-4-{{$homePageLabel->slug}} product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>
               @endif
            </div>
         </div>
      </section>
      @endif @endforeach
   </div>
</section>
<!-- our_vendor_main_div end -->




<!-- age-restriction star -->
<div class="modal age-restriction fade" id="age_restriction" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-body text-center">
            <img style="height: 150px;" class="blur-up lazyload" data-src="{{getImageUrl(asset('assets/images/age-img.svg'),'150/150')}}" alt="" title="">
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
<!-- age-restriction end -->
<!-- footer code in layouts.store/footercontent-template-two -->
@endsection
@section('js-script')
{{--<script type="text/javascript" src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/fly-cart.js')}}"></script>
<script type="text/javascript" src="{{asset('js/aos.js')}}"></script>--}}
@endsection
@section('script')
<script type="text/javascript">
    @if(count($banners))
    $(document).ready(function() {
        $("body").addClass("homeHeader");
    });
    @endif
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

<script>
   $(window).scroll(function() {
       var scroll = $(window).scrollTop();

       if (scroll >= 100) {
           $(".header").addClass("darkHeader");
       } else {
           $(".header").removeClass("darkHeader");
       }
   });
</script>
@endsection