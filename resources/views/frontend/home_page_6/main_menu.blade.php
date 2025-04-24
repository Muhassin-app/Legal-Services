<div class="al_count_tabs_new_design d-none d-sm-block"  >
    @if($mod_count > 1)
    <ul class="nav nav-tabs navigation-tab_al nav-material tab-icons mr-lg-3 vendor_mods" id="top-tab" role="tablist">
        @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
            @php
            $clientVendorTypes = $vendor_typ_key.'_check';
            $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
            $NomenclatureName = getNomenclatureName($vendor_typ_value, true);
            $iconFiledName = config('constants.VendorTypesIcon.'.$vendor_typ_key)
            @endphp

            @if($client_preference_detail->$clientVendorTypes == 1)
            <li class="navigation-tab-item pr-lg-3" role="presentation">
            <a class="nav-link px-0 al_delivery d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')==$VendorTypesName) || (Session::get('vendorType')=='')) ? 'active' : ''}}"
            id="{{$VendorTypesName}}_tab" VendorType="{{$VendorTypesName}}" data-toggle="tab" href="#{{$VendorTypesName}}_tab" role="tab"
            aria-controls="profile" aria-selected="false">
            <span class="al_tabsIcons">
            <img src="{{$client_preference_detail->$iconFiledName ? $client_preference_detail->$iconFiledName['proxy_url'].'36/26'.$client_preference_detail-> $iconFiledName['image_path'] : asset('images/al_custom3.png')}}" alt="{{$iconFiledName}}"></span>
            <span class="al_textTabsText">{{$NomenclatureName}} </span></a>
            </li>
            @endif
        @endforeach
    {{-- @if($client_preference_detail->delivery_check==1) @php $Delivery=getNomenclatureName('Delivery', true); $Delivery=($Delivery==='Delivery') ? __('Delivery') : $Delivery; @endphp
        <li class="navigation-tab-item pr-lg-3" role="presentation">
            <a class="nav-link px-0 al_delivery d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}" id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab" aria-controls="profile" aria-selected="false">
                <span class="al_tabsIcons"><img src="{{$client_preference_detail->deliveryicon ? $client_preference_detail->deliveryicon['proxy_url'].'36/26'.$client_preference_detail->deliveryicon['image_path'] : asset('images/al_custom3.png')}}" alt=""></span>
                <span class="al_textTabsText">{{$Delivery}} </span>
            </a>
        </li>
        @endif @if($client_preference_detail->dinein_check==1) @php $Dine_In=getNomenclatureName('Dine-In', true); $Dine_In=($Dine_In==='Dine-In') ? __('Dine-In') : $Dine_In; @endphp
        <li class="navigation-tab-item pr-lg-3 " role="presentation">
            <a class="nav-link px-0 al_dinein d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}" id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab" aria-controls="dinein_tab" aria-selected="false">
                <span class="al_tabsIcons"><img src="{{$client_preference_detail->dineinicon ? $client_preference_detail->dineinicon['proxy_url'].'36/26'.$client_preference_detail->dineinicon['image_path'] : asset('images/al_custom1.png')}}" alt=""></span>
               <span class="al_textTabsText"> {{$Dine_In}} </span>
            </a>
        </li>
        @endif @if($client_preference_detail->takeaway_check==1)
        <li class="navigation-tab-item  pr-lg-3" role="presentation">
            @php $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway') ? __('Takeaway') : $Takeaway; @endphp
            <a class="nav-link px-0 al_takeway d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}" id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab" aria-controls="takeaway_tab" aria-selected="false">
                <span class="al_tabsIcons"><img src="{{$client_preference_detail->takewayicon ? $client_preference_detail->takewayicon['proxy_url'].'36/26'.$client_preference_detail->takewayicon['image_path'] : asset('images/al_custom2.png')}}" alt=""></span>
                <span class="al_textTabsText">{{$Takeaway}} </span>
            </a>
        </li>
        @endif --}}

        <div class="navigation-tab-overlay_alnew_design"></div>
    </ul>
    @endif
</div>
<div class="al_count_tabs_new_design al_tab_mobile position-fixed d-block d-sm-none">
    @if($mod_count > 1)
    <ul class="nav nav-tabs navigation-tab_al nav-material tab-icons mr-lg-3 vendor_mods d-flex justify-content-around" id="top-tab" role="tablist">
        @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
            @php
            $clientVendorTypes = $vendor_typ_key.'_check';
            $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
            $NomenclatureName = getNomenclatureName($vendor_typ_value, true);
            $iconFiledName = config('constants.VendorTypesIcon.'.$vendor_typ_key)
            @endphp

            @if($client_preference_detail->$clientVendorTypes == 1)
            <li class="navigation-tab-item pr-lg-3" role="presentation">
            <a class="nav-link px-0 al_delivery d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')==$VendorTypesName) || (Session::get('vendorType')=='')) ? 'active' : ''}}"
            id="{{$VendorTypesName}}_tab" VendorType="{{$VendorTypesName}}" data-toggle="tab" href="#{{$VendorTypesName}}_tab" role="tab"
            aria-controls="profile" aria-selected="false">
            <span class="al_tabsIcons">
            <img src="{{$client_preference_detail->$iconFiledName ? $client_preference_detail->$iconFiledName['proxy_url'].'36/26'.$client_preference_detail-> $iconFiledName['image_path'] : asset('images/al_custom3.png')}}" alt="{{$iconFiledName}}"></span>
            <span class="al_textTabsText">{{$NomenclatureName}} </span></a>
            </li>
            @endif
        @endforeach
        {{-- @if($client_preference_detail->delivery_check==1) @php $Delivery=getNomenclatureName('Delivery', true); $Delivery=($Delivery==='Delivery') ? __('Delivery') : $Delivery; @endphp
        <li class="navigation-tab-item pr-lg-3" role="presentation">
            <a class="nav-link px-0 al_delivery d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}" id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab" aria-controls="profile" aria-selected="false">
                <span class="al_tabsIcons"><img src="{{$client_preference_detail->deliveryicon ? $client_preference_detail->deliveryicon['proxy_url'].'36/26'.$client_preference_detail->deliveryicon['image_path'] : asset('images/al_custom3.png')}}" alt=""></span>
                <span class="al_textTabsText">{{$Delivery}} </span>
            </a>
        </li>
        @endif @if($client_preference_detail->dinein_check==1) @php $Dine_In=getNomenclatureName('Dine-In', true); $Dine_In=($Dine_In==='Dine-In') ? __('Dine-In') : $Dine_In; @endphp
        <li class="navigation-tab-item pr-lg-3 " role="presentation">
            <a class="nav-link px-0 al_dinein d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}" id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab" aria-controls="dinein_tab" aria-selected="false">
                <span class="al_tabsIcons"><img src="{{$client_preference_detail->dineinicon ? $client_preference_detail->dineinicon['proxy_url'].'36/26'.$client_preference_detail->dineinicon['image_path'] : asset('images/al_custom1.png')}}" alt=""></span>
               <span class="al_textTabsText"> {{$Dine_In}} </span>
            </a>
        </li>
        @endif @if($client_preference_detail->takeaway_check==1)
        <li class="navigation-tab-item  pr-lg-3" role="presentation">
            @php $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway') ? __('Takeaway') : $Takeaway; @endphp
            <a class="nav-link px-0 al_takeway d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}" id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab" aria-controls="takeaway_tab" aria-selected="false">
                <span class="al_tabsIcons"><img src="{{$client_preference_detail->takewayicon ? $client_preference_detail->takewayicon['proxy_url'].'36/26'.$client_preference_detail->takewayicon['image_path'] : asset('images/al_custom2.png')}}" alt=""></span>
                <span class="al_textTabsText">{{$Takeaway}} </span>
            </a>
        </li>
        @endif --}}
    </ul>
    @endif
</div>

<div class="al_new_ipad_view ipad-view"  >
    <div class="search_bar menu-right d-sm-flex d-block align-items-center justify-content-end w-100">
        @if( (Session::get('preferences')))
        @if( (isset(Session::get('preferences')->is_hyperlocal)) && (Session::get('preferences')->is_hyperlocal==1) )
        <div class="location-bar d-none align-items-center justify-content-start ml-md-2 my-2 my-lg-0 dropdown-toggle" href="#edit-address" data-toggle="modal">
            <div class="map-icon mr-md-1"><i class="fa fa-map-marker" aria-hidden="true"></i></div>
            <div class="homepage-address text-left">
                <h2><span data-placement="top" data-toggle="tooltip" title="{{session('selectedAddress')}}">{{session('selectedAddress')}}</span></h2>
            </div>
            <div class="down-icon"> <i class="fa fa-angle-down" aria-hidden="true"></i> </div>
        </div>
        @endif
        @endif
        @include('layouts.store.search_template')
        @if(auth()->user()) @if($client_preference_detail->show_wishlist==1)
        <div class="icon-nav mr-2 d-none d-lg-block"> <a class="fav-button" href="{{route('user.wishlists')}}">
            <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i>
        </a> </div>
        @endif @endif
        <div class="icon-nav d-none d-lg-inline-block">
            <form name="filterData" id="filterData" action="{{route('changePrimaryData')}}"> @csrf <input type="hidden" id="cliLang" name="cliLang" value="{{session('customerLanguage')}}"> <input type="hidden" id="cliCur" name="cliCur" value="{{session('customerCurrency')}}"> </form>
            <ul class="d-flex align-items-center m-0">
                <!-- <li class="mr-2 pl-0 d-ipad"> <span class="mobile-search-btn">
                    <i class="fa fa-search" aria-hidden="true"></i>
                </span> </li> -->
                <li class="onhover-div pl-0 shake-effect">
                    @if($client_preference_detail) @if($client_preference_detail->cart_enable==1)
                    <a class="btn btn-solid d-flex align-items-center " href="{{route('showCart')}}">
                        <i class="fa fa-shopping-cart mr-1 " aria-hidden="true"></i>

                        <!-- <span>{{__('Cart')}}•</span> -->
                        <span id="cart_qty_span"></span>
                    </a> @endif @endif
                    <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script>
                    <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                </li>
                <li class="mobile-menu-btn d-none">
                    <div class="toggle-nav p-0 d-inline-block"><i class="fa fa-bars sidebar-bar"></i></div>
                </li>
            </ul>
        </div>
        <div class="icon-nav d-sm-none d-none">
            <ul>
                <li class="onhover-div mobile-search">
                    <a href="javascript:void(0);" id="mobile_search_box_btn"><i class="ti-search"></i></a>
                    <div id="search-overlay" class="search-overlay">
                        <div>
                        <span class="closebtn" onclick="closeSearch()" title="Close Overlay">×</span>
                        <div class="overlay-content">
                            <div class="container">
                                <div class="row">
                                    <div class="col-xl-12">
                                    <form>
                                        <div class="form-group"> <input type="text" class="form-control" placeholder="Search a Product"> </div>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </li>
                <li class="onhover-div mobile-setting">
                    <div data-toggle="modal" data-target="#staticBackdrop"><i class="ti-settings"></i></div>
                    <div class="show-div setting">
                        <h6>language</h6>
                        <ul>
                        <li><a href="#">english</a></li>
                        <li><a href="#">french</a></li>
                        </ul>
                        <h6>currency</h6>
                        <ul class="list-inline">
                        <li><a href="#">euro</a></li>
                        <li><a href="#">rupees</a></li>
                        <li><a href="#">pound</a></li>
                        <li><a href="#">doller</a></li>
                        </ul>
                        <h6>Change Theme</h6>
                        @if($client_preference_detail->show_dark_mode==1)
                        <ul class="list-inline">
                        <li><a class="theme-layout-version" href="javascript:void(0)">Dark</a></li>
                        </ul>
                        @endif
                    </div>
                </li>
            </ul>

            <div class="ipad-view order-lg-3">
                <div class="search_bar menu-right d-sm-flex d-block align-items-center justify-content-end w-100">
                    @if (Session::get('preferences')) @if(
                        (isset(Session::get('preferences')->is_hyperlocal)) &&
                        (Session::get('preferences')->is_hyperlocal==1) )
                        <div class="location-bar d-none align-items-center justify-content-start ml-md-2 my-2 my-lg-0 dropdown-toggle" href="#edit-address" data-toggle="modal">
                            <div class="map-icon mr-md-1"><i class="fa fa-map-marker" aria-hidden="true"></i></div>
                            <div class="homepage-address text-left">
                                <h2><span data-placement="top" data-toggle="tooltip"
                                        title="{{ session('selectedAddress') }}">{{ session('selectedAddress') }}</span>
                                </h2>
                            </div>
                            <div class="down-icon"> <i class="fa fa-angle-down" aria-hidden="true"></i> </div>
                        </div>
                    @endif
                    @endif
                    <div class="radius-bar d-xl-inline al_custom_search">
                        <div class="search_form d-flex align-items-center justify-content-between"> <button class="btn"><i
                                    class="fa fa-search" aria-hidden="true"></i></button> @php
                                        $searchPlaceholder = getNomenclatureName('Search product, vendor, item', true);
                                        $searchPlaceholder = $searchPlaceholder === 'Search product, vendor, item' ? __('Search product, vendor, item') : $searchPlaceholder;
                                    @endphp <input
                                class="form-control border-0 typeahead" type="search" placeholder="{{ $searchPlaceholder }}"
                                id="main_search_box" autocomplete="off"> </div>
                        <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
                    </div>
                    @include('layouts.store.search_template')
                    @if (auth()->user())
                    @if ($client_preference_detail->show_wishlist == 1)
                        <div class="icon-nav mx-2 d-none d-sm-block"> <a class="fav-button"
                                href="{{ route('user.wishlists') }}"> <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i> </a> </div>
                        @endif
                    @endif
                    <div class="icon-nav d-none d-sm-inline-block">
                        <form name="filterData" id="filterData" action="{{ route('changePrimaryData') }}"> @csrf <input type="hidden"
                                id="cliLang" name="cliLang" value="{{ session('customerLanguage') }}"> <input type="hidden" id="cliCur"
                                name="cliCur" value="{{ session('customerCurrency') }}"> </form>
                        <ul class="d-flex align-items-center">
                            <li class="mr-2 pl-0 d-ipad"> <span class="mobile-search-btn"><i class="fa fa-search"
                                        aria-hidden="true"></i></span> </li>
                            <li class="onhover-div pl-0 shake-effect">
                                @if($client_preference_detail)
                                    @if($client_preference_detail->cart_enable==1)
                                    <a class="btn btn-solid d-flex align-items-center " href="{{route('showCart')}}">
                                        <i class="fa fa-shopping-cart mr-1 " aria-hidden="true"></i>
                                        <!-- <span>{{__('Cart')}}•</span> -->
                                        <span id="cart_qty_span">
                                        </span>
                                    </a>
                                    @endif
                                @endif
                                <script type="text/template" id="header_cart_template">
                                    <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{ __('Subtotal') }}: <span id='totalCart'>{{ Session::get('currencySymbol') }}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{ __('View Cart') }}</a>
                                </script>
                                <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                            </li>
                            <li class="mobile-menu-btn d-none">
                                <div class="toggle-nav p-0 d-inline-block"><i class="fa fa-bars sidebar-bar"></i></div>
                            </li>
                        </ul>
                    </div>
                    <div class="icon-nav d-sm-none d-none">
                        <ul>
                            <li class="onhover-div mobile-search">
                                <a href="javascript:void(0);" id="mobile_search_box_btn"><i class="ti-search"></i></a>
                                <div id="search-overlay" class="search-overlay">
                                    <div>
                                        <span class="closebtn" onclick="closeSearch()" title="Close Overlay">×</span>
                                        <div class="overlay-content">
                                            <div class="container">
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <form>
                                                            <div class="form-group"> <input type="text" class="form-control"
                                                                     placeholder="Search a Product"> </div>
                                                            <button type="submit" class="btn btn-primary"><i
                                                                    class="fa fa-search"></i></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="onhover-div mobile-setting">
                                <div data-toggle="modal" data-target="#staticBackdrop"><i class="ti-settings"></i></div>
                                <div class="show-div setting">
                                    <h6>language</h6>
                                    <ul>
                                        <li><a href="#">english</a></li>
                                        <li><a href="#">french</a></li>
                                    </ul>
                                    <h6>currency</h6>
                                    <ul class="list-inline">
                                        <li><a href="#">euro</a></li>
                                        <li><a href="#">rupees</a></li>
                                        <li><a href="#">pound</a></li>
                                        <li><a href="#">doller</a></li>
                                    </ul>
                                    <h6>Change Theme</h6>
                                    @if ($client_preference_detail->show_dark_mode == 1)
                                        <ul class="list-inline">
                                            <li><a class="theme-layout-version" href="javascript:void(0)">Dark</a></li>
                                        </ul>
                                    @endif
                                </div>
                            </li>
                            <li class="onhover-div mobile-cart">
                                <a href="{{ route('showCart') }}" style="position: relative"> <i class="ti-shopping-cart"></i> <span
                                        class="cart_qty_cls" style="display:none"></span> </a>{{-- <span class="cart_qty_cls" style="display:none"></span> --}}
                                <ul class="show-div shopping-cart"> </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>