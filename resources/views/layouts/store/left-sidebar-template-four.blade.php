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
@php
    $applocale = 'en';
    if(session()->has('applocale')){
        $applocale = session()->get('applocale');
    }
    @endphp
@if((\Request::route()->getName() != 'customer.login') && (\Request::route()->getName() != 'customer.register') && (\Request::route()->getName() != 'user.verify'))
<header id="al_four_design" class="site-header @if ($client_preference_detail->business_type == 'taxi') taxi-header @endif">

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
                                <span class="icon-ic_lang align-middle"></span>
                                <span class="language ml-1 align-middle">{{ __('language') }}</span>
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
                                <span class="icon-ic_currency align-middle"></span>
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

    <div class="al_four_custom_head">
        <nav class="navbar navbar-expand-lg p-0 ">
            <div class="container-fluid d-flex justify-content-center">
                <div class="col-xl-10 col-lg-12">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-lg-2">
                            <div class="logo_area d-none d-lg-block">
                                <a class="navbar-brand alFourThLogo m-0" href="{{ route('userHome') }}"><img alt="" class="logo-image" style="height:50px;" src="{{$urlImg}}"></a>
                            </div>
                            <div class="col-12 d-block d-lg-none p-0">
                                <div class="row no-gutters">
                                    <div class="col-2">
                                        <div class="al_mobile_menu al_new_mobile_header">
                                            <a class="al_toggle-menu" href="#">
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
                                                        <a href="javascript:void(0)">{{$applocale}}
                                                        <span class="icon-ic_lang align-middle"></span>
                                                        <span class="language ml-1 align-middle">{{ __("language") }}</span>
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

                                                    <li class="onhover-dropdown change-currency">
                                                        <a href="javascript:void(0)">{{session()->get('iso_code')}}
                                                        <span class="icon-ic_currency align-middle"></span>
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
                                    </div>
                                    <div class="col-8">
                                        <a class="navbar-brand m-0" href="{{ route('userHome') }}"><img alt="" class="logo-image w-100" style="height:60px;" src="{{$urlImg}}"></a>
                                    </div>

                                    <div class="col-2 d-flex justify-content-end align-items-center">
                                        <div class="onhover-div pl-0 ml-xl-3 ml-lg-1 shake-effect d-block d-md-none">
                                            <div class="d-flex">
                                                @if( Session::get('vendorType') == 'p2p' )
                                                <li class="add_post pr-2"><a href="{{route('posts.index', ['fullPage'=>1])}}" class="sell-btn">
                                                    <span>
                                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                                        {{ __('') }}</span>
                                                    </a></li>
                                            @endif
                                            @if($client_preference_detail) @if($client_preference_detail->cart_enable==1)
                                            <a class="addToCardBtn d-flex align-items-center " href="{{route('showCart')}}">
                                                <i class="fa fa-shopping-cart mr-1 " aria-hidden="true"></i>
                                                <span id="cart_qty_span"></span>
                                            </a> @endif @endif
                                            <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script>
                                            <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-10">
                            <div class="row">
                                <div class="col-sm-12 p-lg-0 d-flex align-items-center justify-content-md-around justify-content-between  pr-lg-3 pl-lg-0">
                                    @if(isset($client_preference_detail))
                                    @if(($client_preference_detail->is_hyperlocal) && ($client_preference_detail->is_hyperlocal == 1))
                                    <div class="location-bar d-inline-flex align-items-center position-relative mr-xl-3 mr-lg-1" href="#edit-address" data-toggle="modal">
                                        <span class="al_icons_mapPin mr-lg-2"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_544_2)"><path d="M6.83602 0C3.70982 0 1.1665 2.54332 1.1665 5.66952C1.1665 6.95948 1.58924 8.17433 2.38902 9.18275C3.4246 10.4884 6.43607 13.5886 6.56374 13.7199L6.83598 14L7.10826 13.72C7.23601 13.5886 10.2484 10.4877 11.2841 9.18161C12.0832 8.17399 12.5055 6.95956 12.5055 5.66952C12.5055 2.54332 9.96222 0 6.83602 0ZM10.689 8.7097C9.85524 9.76111 7.6161 12.1 6.83602 12.909C6.05602 12.1 3.81767 9.76183 2.98409 8.7108C2.29189 7.83799 1.926 6.78635 1.926 5.66952C1.926 2.96211 4.12861 0.759495 6.83602 0.759495C9.54339 0.759495 11.746 2.96211 11.746 5.66952C11.746 6.78643 11.3805 7.83772 10.689 8.7097Z" fill=""/><path d="M6.83645 2.25098C4.98594 2.25098 3.48047 3.75645 3.48047 5.60696C3.48047 7.45747 4.98598 8.96294 6.83645 8.96294C8.68692 8.96294 10.1924 7.45743 10.1924 5.60696C10.1924 3.75649 8.68696 2.25098 6.83645 2.25098ZM6.83645 8.20344C5.40473 8.20344 4.23996 7.03864 4.23996 5.60696C4.23996 4.17523 5.40476 3.01047 6.83645 3.01047C8.26814 3.01047 9.4329 4.17527 9.43294 5.60696C9.43294 7.03868 8.26817 8.20344 6.83645 8.20344Z" fill=""/></g><defs><clipPath id="clip0_544_2"><rect width="14" height="14" fill="white"/></clipPath></defs></svg></span>
                                        <h2 class="homepage-address"><span data-placement="top" data-toggle="tooltip" title="{{session('selectedAddress')}}">{{session('selectedAddress')}}</span></h2>
                                    </div>
                                    @endif
                                    @endif
                                    <div class="d-inline-flex al_searchType border align-items-center justify-content-start px-xl-2 px-lg-1 position-relative">
                                        <button class="btn px-lg-0"><i class="fa fa-search" aria-hidden="true"></i></button>
                                            @php $searchPlaceholder=getNomenclatureName('Search', true); $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ? __('Search product, vendor, item') : $searchPlaceholder; @endphp
                                        <input class="form-control border-0 typeahead" type="search" placeholder="{{$searchPlaceholder}}" id="main_search_box" autocomplete="off" >
                                        <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
                                    </div>
                                    @include('layouts.store.search_template')
                                    <ul class="d-lg-flex align-items-center m-0 al_addCart daad d-md-block d-none">
                                        <li class="onhover-div pl-0 ml-xl-3 ml-lg-1 shake-effect">
                                            @if($client_preference_detail) @if($client_preference_detail->cart_enable==1)
                                            <a class="btn btn-solid d-flex align-items-center " href="{{route('showCart')}}">
                                                <i class="mr-1"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.20853 17.8104L3.46191 17.7393L4.20853 17.8104ZM19.7915 17.8104L20.5381 17.7393L19.7915 17.8104ZM17.8005 19.25C17.3863 19.25 17.0505 19.5858 17.0505 20C17.0505 20.4142 17.3863 20.75 17.8005 20.75V19.25ZM19.0296 9.81038L18.2829 9.88149L19.0296 9.81038ZM4.97043 9.81038L5.71705 9.88149L4.97043 9.81038ZM7.24999 11C7.24999 11.4142 7.58578 11.75 7.99999 11.75C8.41421 11.75 8.74999 11.4142 8.74999 11H7.24999ZM15.25 11C15.25 11.4142 15.5858 11.75 16 11.75C16.4142 11.75 16.75 11.4142 16.75 11H15.25ZM7.99999 8.75C8.41421 8.75 8.74999 8.41421 8.74999 8C8.74999 7.58579 8.41421 7.25 7.99999 7.25V8.75ZM12 7.25C11.5858 7.25 11.25 7.58579 11.25 8C11.25 8.41421 11.5858 8.75 12 8.75V7.25ZM13 20.75C13.4142 20.75 13.75 20.4142 13.75 20C13.75 19.5858 13.4142 19.25 13 19.25V20.75ZM18.2829 9.88149L19.0448 17.8815L20.5381 17.7393L19.7762 9.73928L18.2829 9.88149ZM4.95515 17.8815L5.71705 9.88149L4.22381 9.73928L3.46191 17.7393L4.95515 17.8815ZM6.19952 19.25C5.46234 19.25 4.88526 18.6153 4.95515 17.8815L3.46191 17.7393C3.30815 19.3538 4.57773 20.75 6.19952 20.75V19.25ZM19.0448 17.8815C19.1147 18.6153 18.5376 19.25 17.8005 19.25V20.75C19.4223 20.75 20.6918 19.3538 20.5381 17.7393L19.0448 17.8815ZM17.0386 8.75C17.683 8.75 18.2218 9.23994 18.2829 9.88149L19.7762 9.73928C19.6418 8.32788 18.4563 7.25 17.0386 7.25V8.75ZM6.96142 7.25C5.54364 7.25 4.35823 8.32788 4.22381 9.73928L5.71705 9.88149C5.77815 9.23994 6.31698 8.75 6.96142 8.75V7.25ZM8.74999 7C8.74999 5.20507 10.2051 3.75 12 3.75V2.25C9.37664 2.25 7.24999 4.37665 7.24999 7H8.74999ZM12 3.75C13.7949 3.75 15.25 5.20507 15.25 7H16.75C16.75 4.37665 14.6233 2.25 12 2.25V3.75ZM7.24999 7V11H8.74999V7H7.24999ZM15.25 7V11H16.75V7H15.25ZM6.96142 8.75H7.99999V7.25H6.96142V8.75ZM12 8.75H17.0386V7.25H12V8.75ZM13 19.25H6.19952V20.75H13V19.25Z" fill="#001A72"/></svg></i>
                                                <span>{{__('Cart')}}</span>
                                                <span id="cart_qty_span"></span>
                                            </a> @endif @endif
                                            <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script>
                                            <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                        </li>
                                    </ul>

                                    <ul class="header-dropdown ml-auto d-lg-block d-none">
                                    @if( p2p_module_status() && Session::get('vendorType') == 'p2p' )
                                        <li><a href="{{route('posts.index', ['fullPage'=>1])}}" class="sell-btn"><span><i class="fa fa-plus" aria-hidden="true"></i>{{ __('Add Post') }}</span></a></li>
                                    @endif
                                    @if( $is_ondemand_multi_pricing ==1 )
                                        @include('layouts.store.onDemandTopBarli')
                                    @endif
                                    @if (auth()->user())
                                        @if ($client_preference_detail->show_wishlist == 1)
                                            <li class="icon-nav mx-2 "> 
                                                <a class="fav-button" href="{{ route('user.wishlists') }}">
                                                    <i class="fa fa-heart" aria-hidden="true"></i> 
                                                </a> 
                                            </li>
                                        @endif
                                    @endif
                                        @if($client_preference_detail->header_quick_link == 1)

                                        <li class="onhover-dropdown quick-links quick-links pr-2 mr-2">

                                            <span class="quick-links mr-1 align-middle">{{ __('Quick Links') }}</span>
                                            </a>
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
                                        @if(count($currencyList) > 1)
                                        <li class="onhover-dropdown change-currency pr-2 mr-2">
                                            <a href="javascript:void(0)">{{session()->get('iso_code')}}
                                            <span class="icon-ic_currency align-middle"></span>
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
                                        <!-- @if(count($languageList) > 1)
                                        <li class="onhover-dropdown change-language pr-2 mr-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend al_BGcolor">
                                                    <label class="input-group-text" for="inputGroupSelect01"><i class="fa fa-globe" aria-hidden="true"></i></label>
                                                </div>
                                                <select class="custom-select change_language_selector" id="inputGroupSelect01">
                                                    <option>Language</option>
                                                     @foreach ($languageList as $key => $listl)

                                                    <option class="customerLang" value="{{ $listl->language_id }}" {{ session()->get('locale') == $listl->language->sort_code ? 'selected' : '' }}>{{ $listl->language->name }}</option>
                                                   @endforeach
                                                </select>
                                            </div>
                                        </li>
                                        @endif -->

                                   <!--      @foreach ($languageList as $key => $listl)
                                    <li
                                        class="{{ session()->get('locale') == $listl->language->sort_code ? 'active' : '' }}">
                                        <a href="javascript:void(0)" class="customerLang"
                                            langId="{{ $listl->language_id }}">{{ $listl->language->name }}</a>
                                    </li>
                                @endforeach -->


                                        @if(count($languageList) > 1)
                                        <li class="onhover-dropdown mobile-account pr-2">
                                            <span class="al_BGcolor"><i class="fa fa-globe" aria-hidden="true"></i></span>
                                            {{__('Language')}}

                                            <ul class="onhover-show-div"> @foreach ($languageList as $key => $list)
                                                <li class="{{ session()->get('locale') == $list->language->sort_code ? 'active' : '' }}">
                                                    <a href="javascript:void(0)" class="customerLang" langId="{{ $list->language_id }}">{{ $list->language->name }}</a>
                                                </li> @endforeach
                                            </ul>

                                        </li>
                                        @endif

                                        <li class="onhover-dropdown mobile-account pr-2">
                                            <span class="al_BGcolor"><i class="fa fa-user" aria-hidden="true"></i></span>
                                            {{__('My Account')}}
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
                                    </ul>



                                </div>
                                @if(@$navCategories && count($navCategories))
                                    <div class="col-sm-12 p-0 d-flex align-items-center alOverflow">
                                        <div class="menu_navigation_al_four mt-lg-3">
                                            <ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider d-flex justify-content-center" >
                                                @foreach($navCategories as $cate)
                                                @if($cate['name'])
                                                <li class="alMainMenu @if(!empty($cate['children'])) has-children @endif ">
                                                    <a class="alMainLink" href="{{route('categoryDetail', $cate['slug'])}}">

                                                        {{$cate['name']}}
                                                    </a>
                                                    @if(!empty($cate['children']))
                                                    <ul class="alInnerMenu">
                                                        @foreach($cate['children'] as $childs)
                                                        <li class="alInnerLink">
                                                            <a href="{{route('categoryDetail', $childs['slug'])}}"><span class="new-tag">{{$childs['name']}}</span></a>
                                                            @if(!empty($childs['children']))
                                                            <ul class="alInnerSubMenu">
                                                                @foreach($childs['children'] as $chld)
                                                                <li class="alInnerSublink"><a href="{{route('categoryDetail', $chld['slug'])}}">{{$chld['name']}}</a></li>
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
                                @endif
                            </div>


                        </div>
                    </div>
                </div>

            </div>
        </nav>
    </div>


    {{-- @endif --}}
</header>
@endif

<div class=" @if((\Request::route()->getName() != 'userHome') || ($client_preference_detail->show_icons == 0)) inner-pages-offset al_offset-top @else al_offset-top-home @endif @if($client_preference_detail->hide_nav_bar == 1) set-hide-nav-bar @endif"></div>
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
    <li class="alMainMenu  <% if(category.children){%> has-children <%}%> "  >
        <a class="alMainLink" href="{{route('categoryDetail')}}/<%=category.slug %>">
            <%=category.name %>
        </a>
        <% if(category.children){%>
        <ul class="alInnerMenu">
            <% _.each(category.children, function(childs, key1){%>
            <li class="alInnerLink">
                <a href="{{route('categoryDetail')}}/<%=childs.slug %>">
                    <span class="new-tag"><%=childs.name %></span>
                </a>
                <% if(childs.children){%>
                <ul class="alInnerSubMenu">
                    <% _.each(childs.children, function(chld, key2){%>
                    <li class="alInnerSublink">
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