@php
$clientData = \App\Models\Client::select('id', 'logo')->where('id', '>', 0)->first();
$urlImg = $clientData->logo['image_fit'].'300/100'.$clientData->logo['image_path'];

$languageList = \App\Models\ClientLanguage::with('language')->where('is_active', 1)->orderBy('is_primary', 'desc')->get();
$currencyList = \App\Models\ClientCurrency::with('currency')->orderBy('is_primary', 'desc')->get();
$pages = \App\Models\Page::with(['translations' => function($q) {$q->where('language_id', session()->get('customerLanguage') ??1);}])->whereHas('translations', function($q) {$q->where(['is_published' => 1, 'language_id' => session()->get('customerLanguage') ??1]);})->orderBy('order_by','ASC')->get();
$preference = $client_preference_detail;
$applocale = 'en';
if(session()->has('applocale')){
    $applocale = session()->get('applocale');
}
@endphp
<div class="top-header site-topbar al_custom_head mobileHeader d-none">
    <nav class="navbar navbar-expand-lg p-0 ">
        <div class="container-fluid ">
            <div class="row d-flex align-items-center justify-content-between w-100">
                <div class="col-lg-5 p-0 d-md-flex align-items-center justify-content-start"   >
                    <a class="navbar-brand mr-3"  href="{{ route('userHome') }}">
                    <img class="logo-image" style="height:50px;" alt="" src="{{$urlImg}}"></a>
                    <div class="al_custom_head_map_box px-2 py-1 d-md-inline-flex  d-flex align-items-center justify-content-start">
                        @if(isset($preference))
                        @if(($preference->is_hyperlocal) && ($preference->is_hyperlocal == 1))
                        <div class=" col-md-4 location-bar d-inline-flex align-items-center position-relative p-0 mr-2" href="#edit-address" data-toggle="modal">
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                            <h2 class="homepage-address"><span data-placement="top" data-toggle="tooltip" title="{{session('selectedAddress')}}">{{session('selectedAddress')}}</span></h2>
                        </div>
                            @endif
                        @endif
                        <div class="col d-inline-flex align-items-center justify-content-start p-0 position-relative">
                            <button class="btn"><i class="fa fa-search" aria-hidden="true"></i></button>
                                @php $searchPlaceholder=getNomenclatureName('Search', true); $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ? __('Search product, vendor, item') : $searchPlaceholder; @endphp
                            <input class="form-control border-0 typeahead" type="search" placeholder="{{$searchPlaceholder}}" id="main_search_box" autocomplete="off">
                            <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
                        </div>                       
                    </div>
                    @if(Auth::user())
                   @if( p2p_module_status() )
                    <li><a href="{{route('posts.index', ['fullPage'=>1])}}" class="sell-btn"><span><i class="fa fa-plus" aria-hidden="true"></i>{{ __('Add Post') }}</span></a></li>
                    @endif 
                    @endif
                    <div class="al_toggle-menu">
                            <span></span><span></span><span></span>
                        </div>
                </div>
                
                <div class="col-lg-7 text-right ml-auto al_z_index p-0"  >
                    <ul class="header-dropdown ml-auto">
                        @if(Auth::user())
                        <li class="search-b">
                            <a href="{{route('user.notification')}}" > <img  class="img-fluid img-white-s" src="{{asset('images/g4.png')}}"> <img  class="img-fluid img-black-s" src="{{asset('images/g4-white.png')}}">Notifications </a>
                        </li>
                        <li class="search-b">
                            <a href="{{route('userChat.UserToUserChat')}}" > <img  class="img-fluid img-white-s" src="{{asset('images/g3.png')}}"><img  class="img-fluid img-black-s" src="{{asset('images/g3-white.png')}}"> Chats </a>
                        </li>
                        @if($client_preference_detail->show_wishlist==1)
                        <li class="search-b">
                            <a href="{{route('user.wishlists')}}" > <img  class="img-fluid img-white-s" src="{{asset('images/g2.png')}}"> <img  class="img-fluid img-black-s" src="{{asset('images/g2-white.png')}}">Favourite </a>
                        </li>
                        @endif
                        <li class="search-b">
                            <a href="{{route('user.productList')}}"> <img  class="img-fluid img-white-s" src="{{asset('images/g5.png')}}"> <img  class="img-fluid img-black-s" src="{{asset('images/g5-white.png')}}">My Ads</a>
                        </li>
                        {{-- <li class="search-b">
                            <a href="{{route('user.searches')}}"> <img  class="img-fluid img-white-s" src="{{asset('images/g1.png')}}"><img  class="img-fluid img-black-s" src="{{asset('images/g1-white.png')}}"> My Searches </a>
                        </li> --}}
                        @endif
                        @if($client_preference_detail->header_quick_link == 1)
                        @if( $is_ondemand_multi_pricing ==1 )
                            @include('layouts.store.onDemandTopBarli')
                        @endif
                        <li class="onhover-dropdown quick-links quick-links">
                            <span class="quick-links ml-1 align-middle">{{ __('Quick Links') }}</span>
                            <ul class="onhover-show-div">
                                @foreach($pages as $page)
                                @if(isset($page->primary->type_of_form) && ($page->primary->type_of_form == 2))
                                @if(isset($last_mile_common_set) && $last_mile_common_set != false)
                                <li>
                                    <a href="{{route('extrapage',['slug' => $page->slug])}}">
                                        @if(isset($page->translations) && $page->translations->first()->title != null)
                                        {{ $page->translations->first()->title ?? ''}}
                                        @else
                                        {{ $page->primary->title ?? ''}}
                                        @endif
                                    </a>
                                </li>
                                @endif
                                @else
                                <li>
                                    <a href="{{route('extrapage',['slug' => $page->slug])}}" target="_blank">
                                        @if(isset($page->translations) && $page->translations->first()->title != null)
                                        {{ $page->translations->first()->title ?? ''}}
                                        @else
                                        {{ $page->primary->title ?? ''}}
                                        @endif
                                    </a>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </li>
                        @endif
                        {{-- @if(count($languageList) > 1) --}}
                        <li class="onhover-dropdown change-language">
                            <a href="javascript:void(0)">
                                <!-- <span class="alLanguageSign">{{$applocale}}</span> -->
                                <span class="icon-icLang align-middle"><svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.59803 0H15.3954C16.3301 0 17.0449 0.714786 17.0449 1.64951V7.6977C17.0449 8.63242 16.3301 9.3472 15.3954 9.3472H9.3472V13.1961H5.66331L2.19934 16.0002V13.1961H1.64951C0.714786 13.1961 0 12.4813 0 11.5465V5.49836C0 4.56364 0.714786 3.84885 1.64951 3.84885H8.79737V2.74918H6.59803V0ZM5.66331 10.062L5.93822 10.9417H7.25783L5.44337 6.04819H4.12377L2.30931 10.9417H3.62891L3.95882 10.062H5.66331ZM12.1514 7.14786C12.8112 7.47776 13.5809 7.6977 14.2957 7.6977V6.59803C13.9658 6.59803 13.6359 6.54304 13.251 6.43308C14.0758 5.60832 14.5157 4.45367 14.4607 3.29901L14.4057 2.74918H12.5912V1.64951H11.4916V2.74918H9.84206V3.84885H13.1411C13.0861 4.6736 12.7012 5.38839 12.0964 5.88324C11.7115 5.55334 11.3816 5.16845 11.2166 4.6736H10.062C10.2269 5.33341 10.5568 5.93822 11.0517 6.43308C10.6668 6.54304 10.2819 6.59803 9.89704 6.59803L9.95202 7.6977C10.7218 7.64271 11.4916 7.47776 12.1514 7.14786ZM4.23384 9.12727L4.78368 7.42278L5.33351 9.12727H4.23384Z" fill="#777777"/></svg></span>
                                <span class="language ml-1">{{ __("Language") }}</span>
                            </a>
                            <ul class="onhover-show-div">
                                @foreach($languageList as $key => $listl)
                                    <li class="{{$applocale ==  $listl->language->sort_code ?  'active' : ''}}">
                                        <a href="javascript:void(0)" class="customerLang" langId="{{$listl->language_id}}">{{$listl->language->name}}@if($listl->language->id != 1)
                                            ({{$listl->language->nativeName}})
                                            @endif </a>
                                    </li>

                                @endforeach
                            </ul>
                        </li>
                        {{-- @endif --}}

                        @if(count($currencyList) > 1)
                        <li class="onhover-dropdown change-currency">
                            <a href="javascript:void(0)">
                            <span class="icon-icCurrency align-middle"><svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.39724 0.142578H1.69458C1.26547 0.142578 0.917597 0.490456 0.917597 0.919564V2.05797C0.917597 2.48705 1.26547 2.83496 1.69458 2.83496H9.39724C9.82635 2.83496 10.1742 2.48708 10.1742 2.05797V0.919564C10.1742 0.490456 9.82638 0.142578 9.39724 0.142578ZM1.08326 4.57899H8.78588C9.21502 4.57899 9.56287 4.92687 9.5629 5.35598V5.94463C8.76654 6.24743 8.08369 6.70273 7.51822 7.2682L7.51514 7.27137H1.08326C0.654151 7.27137 0.306273 6.92349 0.306273 6.49439V5.35598C0.306273 4.92687 0.654151 4.57899 1.08326 4.57899ZM6.31719 9.0156H2.18655C1.75744 9.0156 1.40956 9.36347 1.40956 9.79258V10.931C1.40956 11.3601 1.75744 11.708 2.18655 11.708H5.8268C5.77784 10.8364 5.91763 9.95693 6.2739 9.11453C6.28796 9.08133 6.30256 9.04848 6.31719 9.0156ZM6.20036 13.452H0.776986C0.347878 13.452 0 13.7999 0 14.229V15.3674C0 15.7965 0.347878 16.1444 0.776986 16.1444H8.3093C7.38347 15.4994 6.63238 14.5788 6.20036 13.452ZM6.85635 11.3741C6.85635 8.74051 8.99127 6.60557 11.6249 6.60557C14.2584 6.60557 16.3933 8.74048 16.3934 11.3741C16.3934 14.0077 14.2585 16.1426 11.6249 16.1426C8.99127 16.1426 6.85635 14.0076 6.85635 11.3741Z" fill="#777777"/></svg></span>
                            <span class="currency ml-1 align-middle">{{ __("currency") }}</span>
                            </a>
                            <ul class="onhover-show-div">
                                @foreach($currencyList as $key => $listc)
                                <li class="{{session()->get('iso_code') ==  $listc->currency->iso_code ?  'active' : ''}}">
                                    <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr" currSymbol="{{$listc->currency->symbol}}">
                                        {{$listc->currency->iso_code}}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        @endif

                        <li class="onhover-dropdown mobile-account">
                            <!-- <i class="fa fa-user" aria-hidden="true"></i> -->
                            <span class="alAccount">{{__('My Account')}}</span>
                            <ul class="onhover-show-div">
                                @if(Auth::user())
                                @if(@auth()->user()->can('dashboard-view') || Auth::user()->is_superadmin == 1 || Auth::user()->is_admin == 1)
                                    <li>
                                        <a href="{{route('client.dashboard')}}" data-lng="en">{{getNomenclatureName('Control Panel', true)}}</a>
                                    </li>
                                    @endif
                                    <li>
                                        <a href="{{route('user.profile')}}" data-lng="en">{{__('Profile')}}</a>
                                    </li>
                                    <li>
                                        <a href="{{route('user.logout')}}" data-lng="es">{{__('Logout')}}</a>
                                    </li>
                                @else
                                  @php
                                $getAdditionalPreference = getAdditionalPreference(['is_user_pre_signup']);
                                @endphp
                                @if(isset($getAdditionalPreference) && ($getAdditionalPreference['is_user_pre_signup'] == 1))
                                
                                 <li>
                                    <a href="{{route('customer.register')}}" data-lng="es">{{__('Pre Signup')}}</a>
                                </li>
                               @else
                                  
                                <li>
                                    <a href="{{route('customer.login')}}" data-lng="en">{{__('Login')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a>
                                </li>
                                @endif
                                @endif
                            </ul>
                        </li>
                        <li class="p-0">
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
                                    {{-- @if(auth()->user()) @if($client_preference_detail->show_wishlist==1)
                                    <div class="icon-nav mr-2 d-none d-lg-block mr-0"> <a class="fav-button mr-0" href="{{route('user.wishlists')}}">
                                        <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i>
                                    </a> </div>
                                    @endif @endif --}}
                                    {{-- <div class="icon-nav d-none d-lg-inline-block">
                                        <form name="filterData" id="filterData" action="{{route('changePrimaryData')}}"> @csrf <input type="hidden" id="cliLang" name="cliLang" value="{{session('customerLanguage')}}"> <input type="hidden" id="cliCur" name="cliCur" value="{{session('customerCurrency')}}"> </form>
                                        <ul class="d-flex align-items-center m-0">

                                            <li class="onhover-div pl-0 shake-effect">
                                                @if($client_preference_detail) @if($client_preference_detail->cart_enable==1)
                                                <a class="btn btn-solid d-flex align-items-center p-0" href="{{route('showCart')}}">
                                                    <span class="mr-1"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 19C15 20.1046 15.8954 21 17 21C18.1046 21 19 20.1046 19 19C19 17.8954 18.1046 17 17 17H7.36729C6.86964 17 6.44772 16.6341 6.37735 16.1414M18 14H6.07143L4.5 3H2M9 5H21L19 11M11 19C11 20.1046 10.1046 21 9 21C7.89543 21 7 20.1046 7 19C7 17.8954 7.89543 17 9 17C10.1046 17 11 17.8954 11 19Z" stroke="#001A72" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>


                                                    <span id="cart_qty_span"></span>
                                                </a> @endif @endif
                                                <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script>
                                                <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                            </li>
                                            <li class="mobile-menu-btn d-none">
                                                <div class="toggle-nav p-0 d-inline-block"><i class="fa fa-bars sidebar-bar"></i></div>
                                            </li>
                                        </ul>
                                    </div> --}}
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
                                            <div class="search_bar menu-right d-sm-flex d-block align-items-center justify-content-center w-100">
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
                                                                    <span class="mr-1"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M15 19C15 20.1046 15.8954 21 17 21C18.1046 21 19 20.1046 19 19C19 17.8954 18.1046 17 17 17H7.36729C6.86964 17 6.44772 16.6341 6.37735 16.1414M18 14H6.07143L4.5 3H2M9 5H21L19 11M11 19C11 20.1046 10.1046 21 9 21C7.89543 21 7 20.1046 7 19C7 17.8954 7.89543 17 9 17C10.1046 17 11 17.8954 11 19Z" stroke="#001A72" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                    </span>

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
                        </li>
                        <li class="onhover-div pl-0 shake-effect">
                                                @if($client_preference_detail) @if($client_preference_detail->cart_enable==1 && $client_preference_detail->p2p_check !=1)
                                                <a class="btn btn-solid d-flex align-items-center p-0" href="{{route('showCart')}}">
                                                    <span class="mr-1"><i class="fa fa-shopping-cart" aria-hidden="true"></i></span>

                                                    <!-- <span>{{__('Cart')}}•</span> -->
                                                    <span id="cart_qty_span">Cart</span>
                                                </a> @endif @endif
                                                <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script>
                                                <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                            </li>
                    </ul>
                </div>

                <!-- mobile-header  -->
                <div class="mobile_header  d-none">
                <div class="mobile-list second col-12">
                        <ul class="header-dropdown ml-auto">
                                <div>
                                    @if(Auth::user())
                                    <li class="search-b">
                                        <a href="{{route('user.notification')}}" > 
                                            <img  class="img-fluid img-white-s" src="{{asset('images/g4.png')}}">
                                            <img  class="img-fluid img-black-s" src="{{asset('images/g4-white.png')}}">
                                            <!-- <i class="fa fa-bell"></i> -->
                                                Notifications
                                            </a>
                                    </li>
                                    <li class="search-b">
                                        <a href="{{route('userChat.UserToUserChat')}}" >
                                                <img  class="img-fluid img-white-s" src="{{asset('images/g3.png')}}"><img  class="img-fluid img-black-s" src="{{asset('images/g3-white.png')}}"> 
                                                <!-- <i class="fa fa-commenting"></i> -->
                                                Chats
                                            </a>
                                    </li>
                                    @if($client_preference_detail->show_wishlist==1)
                                    <li class="search-b">
                                        <a href="{{route('user.wishlists')}}" >
                                                <img  class="img-fluid img-white-s" src="{{asset('images/g2.png')}}"> <img  class="img-fluid img-black-s" src="{{asset('images/g2-white.png')}}">
                                                <!-- <i class="fa fa-heart-o wishListCount"></i> -->
                                                Favourite
                                        </a>
                                    </li> 
                                    @endif
                                    <li class="search-b">
                                        <a href="{{route('user.productList')}}"> 
                                            <img  class="img-fluid img-white-s" src="{{asset('images/g5.png')}}">
                                                <img  class="img-fluid img-black-s" src="{{asset('images/g5-white.png')}}">
                                                My Ads
                                        </a>
                                    </li>
                                    {{-- <li class="search-b">
                                        <a href="{{route('user.searches')}}">
                                                <img  class="img-fluid img-white-s" src="{{asset('images/g1.png')}}">
                                                <img  class="img-fluid img-black-s" src="{{asset('images/g1-white.png')}}">
                                                My Searches 
                                            </a>
                                    </li> --}}                        
                                    @endif                  
                                </div>
                            </ul>                            
                        </div>
                        <div class="mobile-list">
                            <ul class="header-dropdown ml-auto">                    
                                @if($client_preference_detail->header_quick_link == 1)
                               
                                <li class="onhover-dropdown quick-links quick-links">
                                    <span class="quick-links ml-1 align-middle">{{ __('Quick Links') }}</span>
                                    <ul class="onhover-show-div">
                                        @foreach($pages as $page)
                                        @if(isset($page->primary->type_of_form) && ($page->primary->type_of_form == 2))
                                        @if(isset($last_mile_common_set) && $last_mile_common_set != false)
                                        <li>
                                            <a href="{{route('extrapage',['slug' => $page->slug])}}">
                                                @if(isset($page->translations) && $page->translations->first()->title != null)
                                                {{ $page->translations->first()->title ?? ''}}
                                                @else
                                                {{ $page->primary->title ?? ''}}
                                                @endif
                                            </a>
                                        </li>
                                        @endif
                                        @else
                                        <li>
                                            <a href="{{route('extrapage',['slug' => $page->slug])}}" target="_blank">
                                                @if(isset($page->translations) && $page->translations->first()->title != null)
                                                {{ $page->translations->first()->title ?? ''}}
                                                @else
                                                {{ $page->primary->title ?? ''}}
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
                                    <a href="javascript:void(0)">
                                        <!-- <span class="alLanguageSign">{{$applocale}}</span> -->
                                        <span class="icon-icLang align-middle"><svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.59803 0H15.3954C16.3301 0 17.0449 0.714786 17.0449 1.64951V7.6977C17.0449 8.63242 16.3301 9.3472 15.3954 9.3472H9.3472V13.1961H5.66331L2.19934 16.0002V13.1961H1.64951C0.714786 13.1961 0 12.4813 0 11.5465V5.49836C0 4.56364 0.714786 3.84885 1.64951 3.84885H8.79737V2.74918H6.59803V0ZM5.66331 10.062L5.93822 10.9417H7.25783L5.44337 6.04819H4.12377L2.30931 10.9417H3.62891L3.95882 10.062H5.66331ZM12.1514 7.14786C12.8112 7.47776 13.5809 7.6977 14.2957 7.6977V6.59803C13.9658 6.59803 13.6359 6.54304 13.251 6.43308C14.0758 5.60832 14.5157 4.45367 14.4607 3.29901L14.4057 2.74918H12.5912V1.64951H11.4916V2.74918H9.84206V3.84885H13.1411C13.0861 4.6736 12.7012 5.38839 12.0964 5.88324C11.7115 5.55334 11.3816 5.16845 11.2166 4.6736H10.062C10.2269 5.33341 10.5568 5.93822 11.0517 6.43308C10.6668 6.54304 10.2819 6.59803 9.89704 6.59803L9.95202 7.6977C10.7218 7.64271 11.4916 7.47776 12.1514 7.14786ZM4.23384 9.12727L4.78368 7.42278L5.33351 9.12727H4.23384Z" fill="#777777"/></svg></span>
                                        <span class="language ml-1">{{ __("Language") }}</span>
                                    </a>
                                    <ul class="onhover-show-div">
                                        @foreach($languageList as $key => $listl)
                                            <li class="{{$applocale ==  $listl->language->sort_code ?  'active' : ''}}">
                                                <a href="javascript:void(0)" class="customerLang" langId="{{$listl->language_id}}">{{$listl->language->name}}@if($listl->language->id != 1)
                                                    ({{$listl->language->nativeName}})
                                                    @endif </a>
                                            </li>

                                        @endforeach
                                    </ul>
                                </li>
                                @endif

                                @if(count($currencyList) > 1)
                                <li class="onhover-dropdown change-currency">
                                    <a href="javascript:void(0)">
                                    <span class="icon-icCurrency align-middle"><svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.39724 0.142578H1.69458C1.26547 0.142578 0.917597 0.490456 0.917597 0.919564V2.05797C0.917597 2.48705 1.26547 2.83496 1.69458 2.83496H9.39724C9.82635 2.83496 10.1742 2.48708 10.1742 2.05797V0.919564C10.1742 0.490456 9.82638 0.142578 9.39724 0.142578ZM1.08326 4.57899H8.78588C9.21502 4.57899 9.56287 4.92687 9.5629 5.35598V5.94463C8.76654 6.24743 8.08369 6.70273 7.51822 7.2682L7.51514 7.27137H1.08326C0.654151 7.27137 0.306273 6.92349 0.306273 6.49439V5.35598C0.306273 4.92687 0.654151 4.57899 1.08326 4.57899ZM6.31719 9.0156H2.18655C1.75744 9.0156 1.40956 9.36347 1.40956 9.79258V10.931C1.40956 11.3601 1.75744 11.708 2.18655 11.708H5.8268C5.77784 10.8364 5.91763 9.95693 6.2739 9.11453C6.28796 9.08133 6.30256 9.04848 6.31719 9.0156ZM6.20036 13.452H0.776986C0.347878 13.452 0 13.7999 0 14.229V15.3674C0 15.7965 0.347878 16.1444 0.776986 16.1444H8.3093C7.38347 15.4994 6.63238 14.5788 6.20036 13.452ZM6.85635 11.3741C6.85635 8.74051 8.99127 6.60557 11.6249 6.60557C14.2584 6.60557 16.3933 8.74048 16.3934 11.3741C16.3934 14.0077 14.2585 16.1426 11.6249 16.1426C8.99127 16.1426 6.85635 14.0076 6.85635 11.3741Z" fill="#777777"/></svg></span>
                                    <span class="currency ml-1 align-middle">{{ __("currency") }}</span>
                                    </a>
                                    <ul class="onhover-show-div">
                                        @foreach($currencyList as $key => $listc)
                                        <li class="{{session()->get('iso_code') ==  $listc->currency->iso_code ?  'active' : ''}}">
                                            <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr" currSymbol="{{$listc->currency->symbol}}">
                                                {{$listc->currency->iso_code}}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </li>
                                @endif

                                <li class="onhover-dropdown mobile-account">
                                    <!-- <i class="fa fa-user" aria-hidden="true"></i> -->
                                    <span class="alAccount">{{__('My Account')}}</span>
                                    <ul class="onhover-show-div">
                                        @if(Auth::user())
                                        @if(@auth()->user()->can('dashboard-view') || Auth::user()->is_superadmin == 1 || Auth::user()->is_admin == 1)
                                            <li>
                                                <a href="{{route('client.dashboard')}}" data-lng="en">{{getNomenclatureName('Control Panel', true)}}</a>
                                            </li>
                                            @endif
                                            <li>
                                                <a href="{{route('user.profile')}}" data-lng="en">{{__('Profile')}}</a>
                                            </li>
                                            <li>
                                                <a href="{{route('user.logout')}}" data-lng="es">{{__('Logout')}}</a>
                                            </li>
                                        @else
                                        <li>
                                            <a href="{{route('customer.login')}}" data-lng="en">{{__('Login')}}</a>
                                        </li>
                                        <li>
                                            <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                                <li class="p-0">
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
                                            {{-- @if(auth()->user()) @if($client_preference_detail->show_wishlist==1)
                                            <div class="icon-nav mr-2 d-none d-lg-block mr-0"> <a class="fav-button mr-0" href="{{route('user.wishlists')}}">
                                                <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i>
                                            </a> </div>
                                            @endif @endif --}}
                                            {{-- <div class="icon-nav d-none d-lg-inline-block">
                                                <form name="filterData" id="filterData" action="{{route('changePrimaryData')}}"> @csrf <input type="hidden" id="cliLang" name="cliLang" value="{{session('customerLanguage')}}"> <input type="hidden" id="cliCur" name="cliCur" value="{{session('customerCurrency')}}"> </form>
                                                <ul class="d-flex align-items-center m-0">

                                                    <li class="onhover-div pl-0 shake-effect">
                                                        @if($client_preference_detail) @if($client_preference_detail->cart_enable==1)
                                                        <a class="btn btn-solid d-flex align-items-center p-0" href="{{route('showCart')}}">
                                                            <span class="mr-1"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 19C15 20.1046 15.8954 21 17 21C18.1046 21 19 20.1046 19 19C19 17.8954 18.1046 17 17 17H7.36729C6.86964 17 6.44772 16.6341 6.37735 16.1414M18 14H6.07143L4.5 3H2M9 5H21L19 11M11 19C11 20.1046 10.1046 21 9 21C7.89543 21 7 20.1046 7 19C7 17.8954 7.89543 17 9 17C10.1046 17 11 17.8954 11 19Z" stroke="#001A72" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>


                                                            <span id="cart_qty_span"></span>
                                                        </a> @endif @endif
                                                        <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script>
                                                        <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                                    </li>
                                                    <li class="mobile-menu-btn d-none">
                                                        <div class="toggle-nav p-0 d-inline-block"><i class="fa fa-bars sidebar-bar"></i></div>
                                                    </li>
                                                </ul>
                                            </div> --}}
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
                                                    <div class="search_bar menu-right d-sm-flex d-block align-items-center justify-content-center w-100">
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
                                                                            <span class="mr-1"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M15 19C15 20.1046 15.8954 21 17 21C18.1046 21 19 20.1046 19 19C19 17.8954 18.1046 17 17 17H7.36729C6.86964 17 6.44772 16.6341 6.37735 16.1414M18 14H6.07143L4.5 3H2M9 5H21L19 11M11 19C11 20.1046 10.1046 21 9 21C7.89543 21 7 20.1046 7 19C7 17.8954 7.89543 17 9 17C10.1046 17 11 17.8954 11 19Z" stroke="#001A72" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            </svg>
                                                                            </span>

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
                                </li>
                                <li class="onhover-div pl-0 shake-effect">
                                    @if($client_preference_detail) @if($client_preference_detail->cart_enable==1 && $client_preference_detail->p2p_check !=1)
                                    <a class="btn btn-solid d-flex align-items-center p-0" href="{{route('showCart')}}">
                                        <span class="mr-1"><i class="fa fa-shopping-cart" aria-hidden="true"></i></span>

                                        <!-- <span>{{__('Cart')}}•</span> -->
                                        <span id="cart_qty_span">Cart</span>
                                    </a> @endif @endif
                                    <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script>
                                    <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                </li>
                            </ul>


                        </div>
                        
            </div>
                <!-- mobile header -->



            </div>
        </div>
    </nav>


    {{--<div class="mobile-menu main-menu position-fixed d-none">
        <div class="menu-right_">
            <ul class="header-dropdown icon-nav d-flex justify-content-around">
                <li class="onhover-div mobile-setting">
                    <div data-toggle="modal" data-target="#setting_modal"><i class="ti-settings"></i></div>
                </li>

                <li class="onhover-dropdown mobile-account  d-inline d-sm-none"> <i class="fa fa-user" aria-hidden="true"></i>
                    <span class="alAccount">{{__('My Account')}}</span>
                    <ul class="onhover-show-div">
                        @if(Auth::user())
                            @if(Auth::user()->is_superadmin == 1 || Auth::user()->is_admin == 1)
                                <li>
                                    <a href="{{route('client.dashboard')}}" data-lng="en">{{__('Control Panel')}}</a>
                                </li>
                            @endif
                            <li>
                                <a href="{{route('user.profile')}}" data-lng="en">{{__('Profile')}}</a>
                            </li>
                            <li>
                                <a href="{{route('user.logout')}}" data-lng="es">{{__('Logout')}}</a>
                            </li>
                        @else
                        <li>
                            <a href="{{route('customer.login')}}" data-lng="en">{{__('Login')}}</a>
                        </li>
                        <li>
                            <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @if($client_preference_detail->show_wishlist == 1)
                <li class="mobile-wishlist d-inline d-sm-none">
                    <a href="{{route('user.wishlists')}}">
                        <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i>
                    </a>
                </li>
                @endif
                <li class="onhover-div al_mobile-search">
                    <a href="javascript:void(0);" id="mobile_search_box_btn" onClick="$('.search-overlay').css('display','block');"><i class="ti-search"></i></a>
                    <div id="search-overlay" class="search-overlay">
                        <div> <span class="closebtn" onclick="closeSearch()" title="Close Overlay">×</span>
                        <div class="overlay-content w-100">
                            <form>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Search a Product">
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                        </div>
                    </div>
                </li>

                @if($client_preference_detail->cart_enable == 1)
                <li class="onhover-div mobile-cart">
                    <a href="{{route('showCart')}}" style="position: relative">
                        <i class="ti-shopping-cart"></i>
                        <span class="cart_qty_cls" style="display:none"></span>
                    </a>
                    <ul class="show-div shopping-cart">
                    </ul>
                </li>
                @endif
            </ul>
        </div>
    </div>--}}
</div>
<div class="al_mobile_menu al_new_mobile_header d-none">
                <div class="al_new_cart">
                    @if($client_preference_detail->cart_enable == 1)
                    <div class="onhover-dropdown_al onhover-div mobile-cart">
                        <a href="{{route('showCart')}}" style="position: relative">
                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                            <span class="cart_qty_cls" style="display:none"></span>
                        </a>
                        <ul class="show-div shopping-cart"></ul>
                    </div>
                    @endif
                </div>
                <a class="al_toggle-menu" href="javascript:void(0)">
                    <i></i>
                    <i></i>
                    <i></i>
                </a>
                <div class="al_menu-drawer" id="navbarsfoodTemplate">
                    <ul class="header-dropdown ml-auto">
                        <li class="onhover-dropdown_al mobile-account_al">
                            <ul class="onhover-show-div">
                                @if(Auth::user())
                                    @if(Auth::user()->is_superadmin == 1 || Auth::user()->is_admin == 1)
                                    <li>
                                        <a href="{{route('client.dashboard')}}" data-lng="en">{{getNomenclatureName('Control Panel', true)}}</a>
                                    </li>
                                    @endif
                                    <li>
                                        <a href="{{route('user.profile')}}" data-lng="en">{{__('Profile')}}</a>
                                    </li>
                                    <li>
                                        <a href="{{route('user.logout')}}" data-lng="es">{{__('Logout')}}</a>
                                    </li>
                                @else
                                <li>
                                    <a href="{{route('customer.login')}}" data-lng="en">{{__('Login')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @if($client_preference_detail->show_wishlist == 1)
                        <li class="onhover-dropdown_al mobile-wishlist_al">
                            <a href="{{route('user.wishlists')}}">
                            {{__('Wishlists')}}
                            </a>
                        </li>
                        @endif

                        @if($client_preference_detail->cart_enable == 1)
                        <li class="onhover-dropdown_al onhover-div mobile-cart">
                            <a href="{{route('showCart')}}" style="position: relative">
                            {{__('Viewcart')}}
                                <span class="cart_qty_cls" style="display:none"></span>
                            </a>
                            <ul class="show-div shopping-cart"></ul>
                        </li>
                        @endif

                        @if($client_preference_detail->header_quick_link == 1)
                        @foreach($pages as $page)
                        @if(isset($page->primary->type_of_form) && ($page->primary->type_of_form == 2))
                        @if(isset($last_mile_common_set) && $last_mile_common_set != false)
                        <li class="onhover-dropdown_al">
                            <a href="{{route('extrapage',['slug' => $page->slug])}}">
                                @if(isset($page->translations) && $page->translations->first()->title != null)
                                {{ $page->translations->first()->title ?? ''}}
                                @else
                                {{ $page->primary->title ?? ''}}
                                @endif
                            </a>
                        </li>
                        @endif
                        @else
                        <li class="onhover-dropdown_al">
                            <a href="{{route('extrapage',['slug' => $page->slug])}}" target="_blank">
                                @if(isset($page->translations) && $page->translations->first()->title != null)
                                {{ $page->translations->first()->title ?? ''}}
                                @else
                                {{ $page->primary->title ?? ''}}
                                @endif
                            </a>
                        </li>
                        @endif
                        @endforeach

                        @endif
                        <li class="onhover-dropdown change-language">
                            <a href="javascript:void(0)">
                                <!-- <span class="alLanguageSign">{{$applocale}}</span> -->
                                <span class="icon-icLang align-middle"><svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.59803 0H15.3954C16.3301 0 17.0449 0.714786 17.0449 1.64951V7.6977C17.0449 8.63242 16.3301 9.3472 15.3954 9.3472H9.3472V13.1961H5.66331L2.19934 16.0002V13.1961H1.64951C0.714786 13.1961 0 12.4813 0 11.5465V5.49836C0 4.56364 0.714786 3.84885 1.64951 3.84885H8.79737V2.74918H6.59803V0ZM5.66331 10.062L5.93822 10.9417H7.25783L5.44337 6.04819H4.12377L2.30931 10.9417H3.62891L3.95882 10.062H5.66331ZM12.1514 7.14786C12.8112 7.47776 13.5809 7.6977 14.2957 7.6977V6.59803C13.9658 6.59803 13.6359 6.54304 13.251 6.43308C14.0758 5.60832 14.5157 4.45367 14.4607 3.29901L14.4057 2.74918H12.5912V1.64951H11.4916V2.74918H9.84206V3.84885H13.1411C13.0861 4.6736 12.7012 5.38839 12.0964 5.88324C11.7115 5.55334 11.3816 5.16845 11.2166 4.6736H10.062C10.2269 5.33341 10.5568 5.93822 11.0517 6.43308C10.6668 6.54304 10.2819 6.59803 9.89704 6.59803L9.95202 7.6977C10.7218 7.64271 11.4916 7.47776 12.1514 7.14786ZM4.23384 9.12727L4.78368 7.42278L5.33351 9.12727H4.23384Z" fill="#777777"/></svg></span>
                                <span class="language ml-1 align-middle">{{ __("language") }}</span>
                            </a>
                            <ul class="onhover-show-div">
                                @foreach($languageList as $key => $listl)
                                    <li class="{{$applocale ==  $listl->language->sort_code ?  'active' : ''}}">
                                        <a href="javascript:void(0)" class="customerLang" langId="{{$listl->language_id}}">{{$listl->language->name}}
                                            @if($listl->language->id != 1)
                                            ({{$listl->language->nativeName}})
                                            @endif </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>

                        <li class="onhover-dropdown change-currency">
                            <a href="javascript:void(0)">
                            <span class="icon-icCurrency align-middle"><svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.39724 0.142578H1.69458C1.26547 0.142578 0.917597 0.490456 0.917597 0.919564V2.05797C0.917597 2.48705 1.26547 2.83496 1.69458 2.83496H9.39724C9.82635 2.83496 10.1742 2.48708 10.1742 2.05797V0.919564C10.1742 0.490456 9.82638 0.142578 9.39724 0.142578ZM1.08326 4.57899H8.78588C9.21502 4.57899 9.56287 4.92687 9.5629 5.35598V5.94463C8.76654 6.24743 8.08369 6.70273 7.51822 7.2682L7.51514 7.27137H1.08326C0.654151 7.27137 0.306273 6.92349 0.306273 6.49439V5.35598C0.306273 4.92687 0.654151 4.57899 1.08326 4.57899ZM6.31719 9.0156H2.18655C1.75744 9.0156 1.40956 9.36347 1.40956 9.79258V10.931C1.40956 11.3601 1.75744 11.708 2.18655 11.708H5.8268C5.77784 10.8364 5.91763 9.95693 6.2739 9.11453C6.28796 9.08133 6.30256 9.04848 6.31719 9.0156ZM6.20036 13.452H0.776986C0.347878 13.452 0 13.7999 0 14.229V15.3674C0 15.7965 0.347878 16.1444 0.776986 16.1444H8.3093C7.38347 15.4994 6.63238 14.5788 6.20036 13.452ZM6.85635 11.3741C6.85635 8.74051 8.99127 6.60557 11.6249 6.60557C14.2584 6.60557 16.3933 8.74048 16.3934 11.3741C16.3934 14.0077 14.2585 16.1426 11.6249 16.1426C8.99127 16.1426 6.85635 14.0076 6.85635 11.3741Z" fill="#777777"/></svg></span>
                            <span class="currency ml-1 align-middle">{{ __("currency") }}</span>
                            </a>
                            <ul class="onhover-show-div">
                                @foreach($currencyList as $key => $listc)
                                <li class="{{session()->get('iso_code') ==  $listc->currency->iso_code ?  'active' : ''}}">
                                    <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr" currSymbol="{{$listc->currency->symbol}}">
                                        {{$listc->currency->iso_code}}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>


