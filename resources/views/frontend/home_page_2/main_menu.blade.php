<div class="col-lg-2 col-5">
    <a class="navbar-brand mr-0" style="height:60px" href="{{route('userHome')}}">
       <img alt="" src="{{$urlImg}}">
    </a>
 </div>
 <div class="col-lg-7 main-menu d-block order-lg-1 order-2 ">
    <div class="d-md-flex mr-auto">
       {{-- @if( (Session::get('preferences'))) @if( (isset(Session::get('preferences')->is_hyperlocal)) && (Session::get('preferences')->is_hyperlocal==1) )
       <div class="location-bar d-none d-lg-flex align-items-center justify-content-start ml-md-2 my-2 my-lg-0 dropdown-toggle order-1" href="#edit-address" data-toggle="modal">
          <div class="map-icon mr-1">
          <svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.848633 6.15122C0.848633 2.7594 3.60803 0 6.99985 0C10.3917 0 13.1511 2.7594 13.1511 6.15122C13.1511 8.18227 12.1614 9.98621 10.6392 11.107L7.46151 15.7563C7.3573 15.9088 7.18455 16 6.99985 16C6.81516 16 6.64237 15.9088 6.5382 15.7563L3.36047 11.107C1.8383 9.98621 0.848633 8.18227 0.848633 6.15122ZM6.99981 10.4225C7.23979 10.4225 7.47461 10.4072 7.70177 10.3806C9.73302 10.0446 11.2871 8.27613 11.287 6.15122C11.287 3.78725 9.36375 1.86402 6.99977 1.86402C4.6358 1.86402 2.71257 3.78725 2.71257 6.15122C2.71257 8.27613 4.26665 10.0446 6.29786 10.3806C6.52498 10.4072 6.75984 10.4225 6.99981 10.4225ZM9.23683 6.15089C9.23683 7.38626 8.23537 8.38772 7.00001 8.38772C5.76464 8.38772 4.76318 7.38626 4.76318 6.15089C4.76318 4.91552 5.76464 3.91406 7.00001 3.91406C8.23537 3.91406 9.23683 4.91552 9.23683 6.15089Z" fill="white"/></svg>
          </div>
          <div class="homepage-address text-left">
             <h2>
                <span data-placement="top" data-toggle="tooltip" title="{{session('selectedAddress')}}">{{session('selectedAddress')}}</span>
             </h2>
          </div>
          <div class="down-icon">
             <i class="fa fa-angle-down" aria-hidden="true"></i>
          </div>
       </div>
       @endif @endif --}}
       @if($mod_count > 1)
       <ul class="nav nav-tabs navigation-tab nav-material tab-icons mx-auto order-0 mb-2 mb-lg-0 vendor_mods" id="top-tab" role="tablist">
          @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
             @php
             $clientVendorTypes = $vendor_typ_key.'_check';
             $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
             $NomenclatureName = getNomenclatureName($vendor_typ_value, true);
             @endphp

             @if($client_preference_detail->$clientVendorTypes == 1)
             <li class="navigation-tab-item" role="presentation"> <a
             class="nav-link {{($mod_count==1 || (Session::get('vendorType')==$VendorTypesName) || (Session::get('vendorType')=='')) ? 'active' : ''}}"
             id="{{$VendorTypesName}}_tab" VendorType="{{$VendorTypesName}}" data-toggle="tab" href="#{{$VendorTypesName}}_tab" role="tab"
             aria-controls="profile" aria-selected="false">{{$NomenclatureName}}</a> </li>
             @endif
          @endforeach

          {{-- @if($client_preference_detail->delivery_check==1) @php $Delivery=getNomenclatureName('Delivery', true); $Delivery=($Delivery==='Delivery') ? __('Delivery') : $Delivery; @endphp
          <li class="navigation-tab-item" role="presentation">
             <a class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='delivery')) ? 'active' : ''}}" id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab" aria-controls="profile" aria-selected="false">
                {{$Delivery}}</a>
          </li>
          @endif @if($client_preference_detail->dinein_check==1) @php $Dine_In=getNomenclatureName('Dine-In', true); $Dine_In=($Dine_In==='Dine-In') ? __('Dine-In') : $Dine_In; @endphp
          <li class="navigation-tab-item" role="presentation">
             <a class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}" id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab" aria-controls="dinein_tab" aria-selected="false">{{$Dine_In}}</a>
          </li>
          @endif @if($client_preference_detail->takeaway_check==1)
          <li class="navigation-tab-item" role="presentation">
             @php $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway') ? __('Takeaway') : $Takeaway; @endphp
             <a class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}" id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab" aria-controls="takeaway_tab" aria-selected="false">{{$Takeaway}}</a>
          </li>
          @endif --}}
          <div class="navigation-tab-overlay"></div>
       </ul>
       @endif
    </div>
 </div>
 <div class="col-lg-3 col-7 order-lg-2 order-1 position-initial">
    <div class="search_bar menu-right d-flex align-items-center justify-content-end justify-content-lg-end w-100 ">

       <div class="radius-bar d-lg-inline">
          <div class="search_form d-flex align-items-center justify-content-between">
             <button class="btn"><i class="fa fa-search" aria-hidden="true"></i></button>
             <input class="form-control border-0 typeahead" type="search" placeholder="{{getNomenclatureName('Search', true)}}" id="main_search_box">
          </div>
          <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
       </div>
       @include('layouts.store.search_template')
        @if(auth()->user()) @if($client_preference_detail->show_wishlist==1) <div class="icon-nav mx-2 d-none d-sm-block"> <a href="{{route('user.wishlists')}}"> <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i> </a> </div>@endif @endif <div class="icon-nav"> <form name="filterData" id="filterData" action="{{route('changePrimaryData')}}"> @csrf <input type="hidden" id="cliLang" name="cliLang" value="{{session('customerLanguage')}}"> <input type="hidden" id="cliCur" name="cliCur" value="{{session('customerCurrency')}}"> </form> <ul class="d-flex align-items-center">

          <li class="onhover-div pl-0 shake-effect"> @if($client_preference_detail) @if($client_preference_detail->cart_enable==1) <a class="btn btn-solid_al " href="{{route('showCart')}}"> <i class="fa fa-shopping-cart mr-1 " aria-hidden="true"></i> <span id="cart_qty_span"></span> </a> @endif @endif <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotals')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script> <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul> </li><li class=" d-none"><div class="toggle-nav p-0 d-inline-block"><i class="fa fa-bars sidebar-bar"></i></div></li></ul> </div><div class="icon-nav d-sm-none d-none"> <ul> <li class="onhover-div mobile-search"> <a href="javascript:void(0);" id="mobile_search_box_btn"><i class="ti-search"></i></a> <div id="search-overlay" class="search-overlay"> <div> <span class="closebtn" onclick="closeSearch()" title="Close Overlay">×</span> <div class="overlay-content"> <div class="container"> <div class="row"> <div class="col-xl-12"> <form> <div class="form-group"> <input type="text" class="form-control" id="exampleInputPassword1" placeholder={{__("Search a Product")}}> </div><button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button> </form> </div></div></div></div></div></div></li><li class="onhover-div mobile-setting"> <div data-toggle="modal" data-target="#staticBackdrop"><i class="ti-settings"></i></div><div class="show-div setting"> <h6>{{__('language')}}</h6> <ul> <li><a href="#">{{__("english")}}</a></li><li><a href="#">{{__("french")}}</a></li></ul> <h6>{{__("currency")}}</h6> <ul class="list-inline"> <li><a href="#">{{__("euro")}}</a></li><li><a href="#">{{__("rupees")}}</a></li><li><a href="#">{{__("pound")}}</a></li><li><a href="#">{{__("doller")}}</a></li></ul> <h6>{{__("Change Theme")}}</h6> @if($client_preference_detail->show_dark_mode==1) <ul class="list-inline"> <li><a class="theme-layout-version" href="javascript:void(0)">{{__("Dark")}}</a></li></ul> @endif </div></li><li class="onhover-div mobile-cart"> <a href="{{route('showCart')}}" style="position: relative"> <i class="ti-shopping-cart"></i> <span class="cart_qty_cls" style="display:none"></span> </a>{{--<span class="cart_qty_cls" style="display:none"></span>--}}<ul class="show-div shopping-cart"> </ul> </li></ul> </div></div>
 </div>