@php
$clientData = \App\Models\Client::select('id', 'logo','dark_logo')
    ->where('id', '>', 0)
    ->first();
if(Session::get('config_theme') == 'dark'){
    $urlImg = $clientData ? $clientData->dark_logo['original'] : ' ';
}else{
    $urlImg = $clientData ? $clientData->logo['original'] : ' ';
}
$languageList = \App\Models\ClientLanguage::with('language')
    ->where('is_active', 1)
    ->orderBy('is_primary', 'desc')
    ->get();
$currencyList = \App\Models\ClientCurrency::with('currency')
    ->orderBy('is_primary', 'desc')
    ->get();
$pages = \App\Models\Page::with([
    'translations' => function ($q) {
        $q->where('language_id', session()->get('customerLanguage') ?? 1);
    },
])
    ->whereHas('translations', function ($q) {
        $q->where(['is_published' => 1, 'language_id' => session()->get('customerLanguage') ?? 1]);
    })
    ->orderBy('order_by', 'ASC')
    ->get();
@endphp
@section('css')

@endsection

<header id="al_new_design" class="site-header @if ($client_preference_detail->business_type == 'taxi') taxi-header @endif">
    @include('layouts.store/topbar-template-five')
    @if($client_preference_detail->business_type == 'taxi')
    <!-- Start Cab Booking Header From Here -->
    <div class="cab-booking-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-sm-9 col-md-10 top-header bg-transparent">
                    <ul class="header-dropdown d-flex align-items-center justify-content-md-end justify-content-center">
                        @if ($client_preference_detail->header_quick_link == 1)
                            <li class="onhover-dropdown quick-links quick-links">

                                <span class="quick-links ml-1 align-middle">{{ __('Quick Links') }}</span>
                                </a>
                                <ul class="onhover-show-div">
                                    @foreach ($pages as $page)
                                        @if (isset($page->primary->type_of_form) && $page->primary->type_of_form == 2)
                                            @if (isset($last_mile_common_set) && $last_mile_common_set != false)
                                                <li>
                                                    <a href="{{ route('extrapage', ['slug' => $page->slug]) }}">
                                                        @if (isset($page->translations) && $page->translations->first()->title != null)
                                                            {{ $page->translations->first()->title ?? '' }}
                                                        @else
                                                            {{ $page->primary->title ?? '' }}
                                                        @endif
                                                    </a>
                                                </li>
                                            @endif
                                        @else
                                            <li>
                                                <a href="{{ route('extrapage', ['slug' => $page->slug]) }}"
                                                    target="_blank">
                                                    @if (isset($page->translations) && $page->translations->first()->title != null)
                                                        {{ $page->translations->first()->title ?? '' }}
                                                    @else
                                                        {{ $page->primary->title ?? '' }}
                                                    @endif
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                        @if(count($languageList) > 1)
                        <li class="onhover-dropdown change-language">
                            <a href="javascript:void(0)">{{ session()->get('locale') }}
                                <span class="icon-icLang align-middle"><svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.59803 0H15.3954C16.3301 0 17.0449 0.714786 17.0449 1.64951V7.6977C17.0449 8.63242 16.3301 9.3472 15.3954 9.3472H9.3472V13.1961H5.66331L2.19934 16.0002V13.1961H1.64951C0.714786 13.1961 0 12.4813 0 11.5465V5.49836C0 4.56364 0.714786 3.84885 1.64951 3.84885H8.79737V2.74918H6.59803V0ZM5.66331 10.062L5.93822 10.9417H7.25783L5.44337 6.04819H4.12377L2.30931 10.9417H3.62891L3.95882 10.062H5.66331ZM12.1514 7.14786C12.8112 7.47776 13.5809 7.6977 14.2957 7.6977V6.59803C13.9658 6.59803 13.6359 6.54304 13.251 6.43308C14.0758 5.60832 14.5157 4.45367 14.4607 3.29901L14.4057 2.74918H12.5912V1.64951H11.4916V2.74918H9.84206V3.84885H13.1411C13.0861 4.6736 12.7012 5.38839 12.0964 5.88324C11.7115 5.55334 11.3816 5.16845 11.2166 4.6736H10.062C10.2269 5.33341 10.5568 5.93822 11.0517 6.43308C10.6668 6.54304 10.2819 6.59803 9.89704 6.59803L9.95202 7.6977C10.7218 7.64271 11.4916 7.47776 12.1514 7.14786ZM4.23384 9.12727L4.78368 7.42278L5.33351 9.12727H4.23384Z" fill="#777777"/></svg></span>

                                <span class="language ml-1">{{ __('language') }}</span>
                            </a>
                            <ul class="onhover-show-div">
                                @foreach ($languageList as $key => $listl)
                                    <li
                                        class="{{ session()->get('locale') == $listl->language->sort_code ? 'active' : '' }}">
                                        <a href="javascript:void(0)" class="customerLang"
                                            langId="{{ $listl->language_id }}">{{ $listl->language->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        @endif
                        @if(count($currencyList) > 1)
                        <li class="onhover-dropdown change-currency">
                            <a href="javascript:void(0)">{{ session()->get('iso_code') }}
                            <span class="icon-icCurrency align-middle"><svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.39724 0.142578H1.69458C1.26547 0.142578 0.917597 0.490456 0.917597 0.919564V2.05797C0.917597 2.48705 1.26547 2.83496 1.69458 2.83496H9.39724C9.82635 2.83496 10.1742 2.48708 10.1742 2.05797V0.919564C10.1742 0.490456 9.82638 0.142578 9.39724 0.142578ZM1.08326 4.57899H8.78588C9.21502 4.57899 9.56287 4.92687 9.5629 5.35598V5.94463C8.76654 6.24743 8.08369 6.70273 7.51822 7.2682L7.51514 7.27137H1.08326C0.654151 7.27137 0.306273 6.92349 0.306273 6.49439V5.35598C0.306273 4.92687 0.654151 4.57899 1.08326 4.57899ZM6.31719 9.0156H2.18655C1.75744 9.0156 1.40956 9.36347 1.40956 9.79258V10.931C1.40956 11.3601 1.75744 11.708 2.18655 11.708H5.8268C5.77784 10.8364 5.91763 9.95693 6.2739 9.11453C6.28796 9.08133 6.30256 9.04848 6.31719 9.0156ZM6.20036 13.452H0.776986C0.347878 13.452 0 13.7999 0 14.229V15.3674C0 15.7965 0.347878 16.1444 0.776986 16.1444H8.3093C7.38347 15.4994 6.63238 14.5788 6.20036 13.452ZM6.85635 11.3741C6.85635 8.74051 8.99127 6.60557 11.6249 6.60557C14.2584 6.60557 16.3933 8.74048 16.3934 11.3741C16.3934 14.0077 14.2585 16.1426 11.6249 16.1426C8.99127 16.1426 6.85635 14.0076 6.85635 11.3741Z" fill="#777777"/></svg></span>
                                <span class="currency ml-1 align-middle">{{ __('currency') }}</span>
                            </a>
                            <ul class="onhover-show-div">
                                @foreach ($currencyList as $key => $listc)
                                    <li
                                        class="{{ session()->get('iso_code') == $listc->currency->iso_code ? 'active' : '' }}">
                                        <a href="javascript:void(0)" currId="{{ $listc->currency_id }}"
                                            class="customerCurr" currSymbol="{{ $listc->currency->symbol }}">
                                            {{ $listc->currency->iso_code }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        @endif
                        @if (Auth::guest())
                            <li class="onhover-dropdown mobile-account d-block">
                                <i class="fa fa-user mr-1" aria-hidden="true"></i>{{ __('Account') }}
                                <ul class="onhover-show-div">
                                    <li>
                                        <a href="{{ route('customer.login') }}" data-lng="en">{{ __('Login') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('customer.register') }}"
                                            data-lng="es">{{ __('Register') }}</a>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="onhover-dropdown mobile-account d-block">
                                <i class="fa fa-user mr-1" aria-hidden="true"></i>{{ __('Account') }}
                                <ul class="onhover-show-div">
                                    @if (Auth::user()->is_superadmin == 1 || Auth::user()->is_admin == 1)
                                        <li>
                                            <a href="{{ route('client.dashboard') }}"
                                                data-lng="en">{{getNomenclatureName('Control Panel', true)}}</a>
                                        </li>
                                    @endif
                                    <li>
                                        <a href="{{ route('user.profile') }}" data-lng="en">{{ __('Profile') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('user.logout') }}" data-lng="es">{{ __('Logout') }}</a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
         </div>
      </div>
   </div>
   <!-- End Cab Booking Header From Here -->
   @else
    <div class="main-menu @if((\Request::route()->getName() != 'userHome')) no-category-image @endif">
        <div class="container_fluid_al d-block" >
            <div class="row align-items-center justify-content-center position-initial">
                <div class="col-lg-12">
                    <div class="container al_mobile-header d-flex align-items-center justify-content-between position-relative">
                        <div class="al_count_tabs_new_design d-none d-sm-block"  >
                            @if($mod_count > 1)
                            <ul class="nav nav-tabs navigation-tab_al nav-material tab-icons mr-lg-3 vendor_mods" id="top-tab" role="tablist">
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
                                aria-controls="profile" aria-selected="false">
                                        <span class="al_tabsIcons"><img src="{{$client_preference_detail->deliveryicon ? $client_preference_detail->deliveryicon['proxy_url'].'36/26'.$client_preference_detail->deliveryicon['image_path'] : asset('images/al_custom3.png')}}" alt=""></span>
                                        <span class="al_textTabsText">{{$NomenclatureName}}</span>
                                    </a>
                                </li>

                                @endif
                            @endforeach
                            <!-- @if($client_preference_detail->delivery_check==1) @php $Delivery=getNomenclatureName('Delivery', true); $Delivery=($Delivery==='Delivery') ? __('Delivery') : $Delivery; @endphp
                                <li class="navigation-tab-item pr-lg-3" role="presentation">
                                    <a class="nav-link al_delivery d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}" id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab" aria-controls="profile" aria-selected="false">
                                        <span class="al_tabsIcons"><img src="{{$client_preference_detail->deliveryicon ? $client_preference_detail->deliveryicon['proxy_url'].'36/26'.$client_preference_detail->deliveryicon['image_path'] : asset('images/al_custom3.png')}}" alt=""></span>
                                        <span class="al_textTabsText">{{$Delivery}} </span>
                                    </a>
                                </li>
                                @endif @if($client_preference_detail->dinein_check==1) @php $Dine_In=getNomenclatureName('Dine-In', true); $Dine_In=($Dine_In==='Dine-In') ? __('Dine-In') : $Dine_In; @endphp
                                <li class="navigation-tab-item pr-lg-3 " role="presentation">
                                    <a class="nav-link al_dinein d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}" id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab" aria-controls="dinein_tab" aria-selected="false">
                                        <span class="al_tabsIcons"><img src="{{$client_preference_detail->dineinicon ? $client_preference_detail->dineinicon['proxy_url'].'36/26'.$client_preference_detail->dineinicon['image_path'] : asset('images/al_custom1.png')}}" alt=""></span>
                                       <span class="al_textTabsText"> {{$Dine_In}} </span>
                                    </a>
                                </li>
                                @endif @if($client_preference_detail->takeaway_check==1)
                                <li class="navigation-tab-item  pr-lg-3" role="presentation">
                                    @php $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway') ? __('Takeaway') : $Takeaway; @endphp
                                    <a class="nav-link al_takeway d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}" id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab" aria-controls="takeaway_tab" aria-selected="false">
                                        <span class="al_tabsIcons"><img src="{{$client_preference_detail->takewayicon ? $client_preference_detail->takewayicon['proxy_url'].'36/26'.$client_preference_detail->takewayicon['image_path'] : asset('images/al_custom2.png')}}" alt=""></span>
                                        <span class="al_textTabsText">{{$Takeaway}} </span>
                                    </a>
                                </li>
                                @endif -->
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
                                @endphp

                                @if($client_preference_detail->$clientVendorTypes == 1)
                                <li class="navigation-tab-item" role="presentation"> <a
                                class="nav-link {{($mod_count==1 || (Session::get('vendorType')==$VendorTypesName) || (Session::get('vendorType')=='')) ? 'active' : ''}}"
                                id="{{$VendorTypesName}}_tab" VendorType="{{$VendorTypesName}}" data-toggle="tab" href="#{{$VendorTypesName}}_tab" role="tab"
                                aria-controls="profile" aria-selected="false">
                                        <span class="al_tabsIcons"><img src="{{$client_preference_detail->deliveryicon ? $client_preference_detail->deliveryicon['proxy_url'].'36/26'.$client_preference_detail->deliveryicon['image_path'] : asset('images/al_custom3.png')}}" alt=""></span>
                                        <span class="al_textTabsText">{{$NomenclatureName}}</span>
                                    </a>
                                </li>

                                @endif
                            @endforeach
                            <!-- @if($client_preference_detail->delivery_check==1) @php $Delivery=getNomenclatureName('Delivery', true); $Delivery=($Delivery==='Delivery') ? __('Delivery') : $Delivery; @endphp
                                <li class="navigation-tab-item pr-lg-3" role="presentation">
                                    <a class="nav-link al_delivery d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}" id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab" aria-controls="profile" aria-selected="false">
                                        <span class="al_tabsIcons"><img src="{{$client_preference_detail->deliveryicon ? $client_preference_detail->deliveryicon['proxy_url'].'36/26'.$client_preference_detail->deliveryicon['image_path'] : asset('images/al_custom3.png')}}" alt=""></span>
                                        <span class="al_textTabsText">{{$Delivery}} </span>
                                    </a>
                                </li>
                                @endif @if($client_preference_detail->dinein_check==1) @php $Dine_In=getNomenclatureName('Dine-In', true); $Dine_In=($Dine_In==='Dine-In') ? __('Dine-In') : $Dine_In; @endphp
                                <li class="navigation-tab-item pr-lg-3 " role="presentation">
                                    <a class="nav-link al_dinein d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}" id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab" aria-controls="dinein_tab" aria-selected="false">
                                        <span class="al_tabsIcons"><img src="{{$client_preference_detail->dineinicon ? $client_preference_detail->dineinicon['proxy_url'].'36/26'.$client_preference_detail->dineinicon['image_path'] : asset('images/al_custom1.png')}}" alt=""></span>
                                       <span class="al_textTabsText"> {{$Dine_In}} </span>
                                    </a>
                                </li>
                                @endif @if($client_preference_detail->takeaway_check==1)
                                <li class="navigation-tab-item  pr-lg-3" role="presentation">
                                    @php $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway') ? __('Takeaway') : $Takeaway; @endphp
                                    <a class="nav-link al_takeway d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}" id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab" aria-controls="takeaway_tab" aria-selected="false">
                                        <span class="al_tabsIcons"><img src="{{$client_preference_detail->takewayicon ? $client_preference_detail->takewayicon['proxy_url'].'36/26'.$client_preference_detail->takewayicon['image_path'] : asset('images/al_custom2.png')}}" alt=""></span>
                                        <span class="al_textTabsText">{{$Takeaway}} </span>
                                    </a>
                                </li>
                                @endif -->
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
                                <div class="icon-nav mr-2 d-none d-sm-block"> <a class="fav-button" href="{{route('user.wishlists')}}">
                                    <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i>
                                </a> </div>
                                @endif @endif
                                <div class="icon-nav d-none d-sm-inline-block">
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
        </div>
    </div>
    
   @endif

   @if(count($navCategories))
    <div class="menu-navigation al">
      <div class="container-fluid">
         <div class="row">
            <div class="col-12">

               <ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider d-flex justify-content-center" >
                  @foreach($navCategories as $cate)
                  @if($cate['name'])
                  <li class="al_main_category"  >
                     <a href="{{route('categoryDetail', $cate['slug'])}}" class="{{isset($category) && $category->slug == $cate['slug'] ? 'current_category' : ''}}">
                        @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='userHome' || \Request::route()->getName()=='categoryDetail'))
                        <div class="nav-cate-img {{ \Request::route()->getName()=='userHome' ? '' : 'activ_nav'}} " > <img style="height:100px;width:100px;" class="blur-up lazyload" data-icon_two="{{!is_null($cate['icon_two']) ? $cate['icon_two']['image_fit'].'200/200'.$cate['icon_two']['image_path'] : $cate['icon']['image_fit'].'200/200'.$cate['icon']['image_path']}}" data-icon="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" data-src="{{$cate['icon']['image_fit']}}150/150{{$cate['icon']['image_path']}}" alt="" onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'> </div>
                        @endif
                        {{$cate['name']}}
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
</header>

<div class=" @if((\Request::route()->getName() != 'userHome') || ($client_preference_detail->show_icons == 0)) inner-pages-offset al_offset-top @else al_offset-top-home_five @endif @if($client_preference_detail->hide_nav_bar == 1) set-hide-nav-bar @endif"></div>
<script type="text/template" id="nav_categories_template">
    <!-- <li>
       <div class="mobile-back text-end">Back<i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
   </li> -->
    <% _.each(nav_categories, function(category, key){ %>
    <% var icon_two_url = null;
      if(category.icon_two != null){
        icon_two_url =  category.icon_two.image_fit + '200/200' + category.icon_two.image_path;
      }else{
        icon_two_url =  category.icon.image_fit + '200/200' + category.icon.image_path;
      }
    %>

    <li class="al_main_category"  >
        <a href="{{route('categoryDetail')}}/<%=category.slug %>" class="{{isset($category[0]) && $category->slug == $cate[0]['slug'] ? 'current_category' : ''}}">
            @if($client_preference_detail->show_icons==1)
            <div class="nav-cate-img {{ \Request::route()->getName()=='userHome' ? '' : 'activ_nav'}}">
                <img style="height:100px;width:100px;" class="blur-up lazyload" data-icon_two="<%=icon_two_url %>" data-icon="<%=category.icon.image_fit %>200/200<%=category.icon.image_path %>" data-src="<%=category.icon.image_fit %>200/200<%=category.icon.image_path %>" alt=""  onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'>
            </div>
            @endif
            <%=category.name %>
        </a>
        <% if(category.children){%>
        <ul class="al_main_category_list">
            <% _.each(category.children, function(childs, key1){%>
            <li>
                <a href="{{route('categoryDetail')}}/<%=childs.slug %>">
                    <span class="new-tag"><%=childs.name %></span>
                </a>
                <% if(childs.children){%>
                <ul class="al_main_category_sub_list">
                    <% _.each(childs.children, function(chld, key2){%>
                    <li>
                        <a href="{{route('categoryDetail')}}/<%=chld.slug %>">
                            <%=chld.name %>
                        </a>
                    </li>
                    <%}); %>
                </ul>
                <%}%>
            </li>
            <%}); %>
        </ul>
        <%}%>
    </li>
    <% }); %>
</script>
@if($client_preference_detail)
    @if($client_preference_detail->is_hyperlocal == 1 )
        <div class="modal fade edit_address" id="edit-address" tabindex="-1" aria-labelledby="edit-addressLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div id="address-map-container">
                        <div id="address-map"></div>
                        </div>
                        <div class="delivery_address p-2 mb-2 position-relative">
                        <button type="button" class="close edit-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group address-input-group">
                            <label class="delivery-head mb-2">{{__('SELECT YOUR LOCATION')}}</label>
                            <div class="address-input-field d-flex align-items-center justify-content-between"> <i class="fa fa-map-marker" aria-hidden="true"></i> <input class="form-control border-0 map-input" type="text" name="address-input" id="address-input" value="{{session('selectedAddress')}}"> <input type="hidden" name="address_latitude" id="address-latitude" value="{{session('latitude')}}"/> <input type="hidden" name="address_longitude" id="address-longitude" value="{{session('longitude')}}"/> <input type="hidden" name="address_place_id" id="address-place-id" value="{{session('selectedPlaceId')}}"/> </div>
                        </div>
                        <div class="text-center"> <button type="button" class="btn btn-solid ml-auto confirm_address_btn w-100">{{__('Confirm And Proceed')}}</button> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
@include('layouts.store.remove_cart_model')