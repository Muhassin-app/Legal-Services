@php
$clientData = \App\Models\Client::select('id', 'logo', 'dark_logo')
->where('id', '>', 0)
->first();
if(Session::get('config_theme') == 'dark'){
    $urlImg = $clientData ? $clientData->dark_logo['original'] : ' ';
}else{
    $urlImg = $clientData ? $clientData->logo['original'] : ' ';
}
$compId = session()->get('company_id')??null;
if(!empty($compId) ||  @auth()->user()->company_id)
{
    $compId = (($compId)?base64_decode($compId):auth()->user()->company_id);
    $compdata =  \App\Models\Company::where('id',$compId)->first();
    $urlImg = get_file_path($compdata->logo,'FILL_URL');
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
<article class="site-header @if ($client_preference_detail->business_type == 'taxi') taxi-header @endif">
    @include('layouts.store/topbar-template-one')

    @if($client_preference_detail->business_type == 'taxi')
    <!-- Start Cab Booking Header From Here -->
    <div class="cab-booking-header" style="background: var(--top-header-color)">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-3 col-md-2">
                    <a class="navbar-brand mr-0"  href="{{ route('userHome') }}"><img id="theme-logo" class="logo-image" style="height:60px" alt="" src="{{ $urlImg }}"></a>
                </div>
                <div class="col-sm-9 col-md-10 top-header bg-transparent">
                    <ul class="header-dropdown d-flex align-items-center justify-content-md-end justify-content-center">
                        @if ($client_preference_detail->header_quick_link == 1)
                        <li class="onhover-dropdown quick-links quick-links">
                            <span class="quick-links ml-1 align-middle">{{ __('Quick Links') }}</span>
                            <ul class="onhover-show-div">


                                @foreach ($pages as $page)
                                @if (isset($page->primary->type_of_form) && $page->primary->type_of_form == 2)
                                @if (isset($last_mile_common_set) && $last_mile_common_set != false)
                                <li>
                                    <a href="{{ route('extrapage', ['slug' => $page->slug]) }}">
                                        @if (isset($page->translations) && $page->translations->first()->title != null)
                                        {{ __($page->translations->first()->title) ?? '' }}
                                        @else
                                        {{ __($page->primary->title) ?? '' }}
                                        @endif
                                    </a>
                                </li>
                                @endif
                                @else
                                <li>
                                    <a href="{{ route('extrapage', ['slug' => $page->slug]) }}" target="_blank">
                                        @if (isset($page->translations) && $page->translations->first()->title != null)
                                        {{ __($page->translations->first()->title) ?? '' }}
                                        @else
                                        {{ __($page->primary->title) ?? '' }}
                                        @endif
                                    </a>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </li>
                        @endif
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
                        <li class="onhover-dropdown change-currency">
                            <a href="javascript:void(0)">{{ session()->get('iso_code') }}
                                <span class="icon-ic_currency align-middle"></span>
                                <span class="currency ml-1 align-middle">{{ __('currency') }}</span>
                            </a>
                            <ul class="onhover-show-div">
                                @foreach ($currencyList as $key => $listc)
                                <li
                                    class="{{ session()->get('iso_code') == $listc->currency->iso_code ? 'active' : '' }}">
                                    <a href="javascript:void(0)" currId="{{ $listc->currency_id }}" class="customerCurr"
                                        currSymbol="{{ $listc->currency->symbol }}">
                                        {{ $listc->currency->iso_code }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        @if (Auth::guest())
                        <li class="onhover-dropdown mobile-account d-block">
                            <i class="fa fa-user mr-1" aria-hidden="true"></i>{{ __('Account') }}
                            <ul class="onhover-show-div">
                                <li>
                                    <a href="{{ route('customer.login') }}" data-lng="en">{{ __('Login') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('customer.register') }}" data-lng="es">{{ __('Register') }}</a>
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
    <!-- End Cab Booking Header From Here -->
{{-- P2p Template --}}
    @elseif(p2p_module_status() && Session::get('vendorType') == 'p2p')
    <div class="main-menu al_template_one_menu">
        <div class="container-fluid d-block ">
            <div class="container p-0 align-items-center justify-content-center position-initial">
                <div class="col-lg-12">
                  {{--  @include('layouts.store.topbar-template-nine')--}}
                    <div class="row mobile-header align-items-center justify-content-between">
                        <div class="logo">
                            <a class="navbar-brand mr-3 p-0 d-none d-sm-inline-flex align-items-center" style="height:60px" href="{{route('userHome')}}"><img alt="" src="{{$urlImg}}"></a>
                            <div class="radius-bar d-xl-inline al_custom_search mr-sm-2">
                                <div class="search_form d-flex align-items-start justify-content-start"> 
                                    {{--<button class="btn">
                                        <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 512.000000 512.000000"
                                        preserveAspectRatio="xMidYMid meet">
                                        <metadata>
                                        Created by potrace 1.16, written by Peter Selinger 2001-2019
                                        </metadata>
                                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                                        fill="#000000" stroke="none">
                                        <path d="M1810 5114 c-14 -2 -59 -9 -100 -15 -176 -25 -415 -101 -580 -184
                                        -587 -295 -982 -823 -1107 -1480 -24 -127 -29 -474 -9 -615 65 -450 264 -848
                                        583 -1166 485 -482 1170 -686 1841 -548 281 58 570 187 792 353 l65 49 740
                                        -738 c472 -470 753 -743 777 -753 54 -25 145 -22 199 6 68 36 103 93 107 176
                                        3 51 0 79 -13 107 -12 26 -257 279 -756 779 l-738 740 25 30 c49 60 140 202
                                        190 300 133 254 203 507 225 807 43 575 -171 1145 -585 1561 -315 316 -716
                                        518 -1157 582 -97 14 -425 19 -499 9z m473 -449 c512 -81 970 -430 1188 -903
                                        282 -612 156 -1316 -321 -1792 -626 -627 -1626 -626 -2251 2 -238 240 -389
                                        535 -446 870 -19 115 -21 375 -4 478 63 371 216 669 472 916 277 266 611 416
                                        999 448 74 6 269 -4 363 -19z"/>
                                        </g>
                                        </svg>
                                    </button>--}} 
                                    @php
                                    $searchPlaceholder=getNomenclatureName('Search', true);
                                    $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ?
                                    __('Search product, vendor, item') : $searchPlaceholder; @endphp <input
                                        class="form-control border-0 typeahead" type="search"
                                        placeholder="{{$searchPlaceholder}}" id="main_search_box"
                                        autocomplete="off"> </div>
                                    <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
                                </div>
                                @include('layouts.store.search_template')                        
                            </div>
                        
                        <div class="al_count_tabs my-1 d-none d-sm-block">
                            @if($mod_count > 1)
                            <ul class="nav nav-tabs navigation-tab nav-material tab-icons vendor_mods"
                                id="top-tab" role="tablist">
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
                                  
                                <div class="navigation-tab-overlay"></div>
                            </ul>
                            @endif
                        </div>

                        <div class=" ipad-view">
                            <div class="search_bar menu-right d-sm-flex d-block align-items-center justify-content-end w-100">
                               @if(Session::get('preferences') && (isset(Session::get('preferences')->is_hyperlocal)) && (Session::get('preferences')->is_hyperlocal==1) )
                                <div class="location-bar d-none align-items-center justify-content-start ml-md-2 my-2 my-lg-0 dropdown-toggle"
                                    href="#edit-address" data-toggle="modal">
                                    <div class="map-icon mr-md-1"><i class="fa fa-map-marker" aria-hidden="true"></i>
                                    </div>
                                    <div class="homepage-address text-left">
                                        <h2><span data-placement="top" data-toggle="tooltip"
                                                title="{{session('selectedAddress')}}">{{session('selectedAddress')}}</span>
                                        </h2>
                                    </div>
                                    <div class="down-icon"> <i class="fa fa-angle-down" aria-hidden="true"></i> </div>
                                </div>
                                @endif
                                
                                @if(auth()->user() && $client_preference_detail->show_wishlist==1)
                                    <div class="icon-nav  d-none d-sm-block"> 
                                        <a class="fav-button" href="{{route('user.wishlists')}}"> 
                                            <svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.000000 512.000000"  preserveAspectRatio="xMidYMid meet">
                                                <metadata>
                                                Created by potrace 1.16, written by Peter Selinger 2001-2019
                                                </metadata>
                                                <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                                                fill="#000000" stroke="none">
                                                <path d="M1325 4733 c-455 -57 -857 -339 -1062 -747 -44 -87 -106 -276 -124
                                                -376 -20 -109 -18 -384 4 -490 46 -230 141 -433 286 -611 33 -41 526 -540
                                                1095 -1109 l1036 -1035 1053 1055 c823 824 1064 1071 1103 1130 102 156 169
                                                319 211 510 25 119 25 401 0 520 -42 192 -110 356 -212 510 -65 98 -260 294
                                                -355 357 -318 212 -686 286 -1052 212 -249 -50 -473 -166 -666 -344 l-83 -77
                                                -112 108 c-62 60 -146 131 -187 159 -236 157 -502 237 -781 234 -68 -1 -137
                                                -4 -154 -6z m365 -388 c155 -23 294 -76 423 -162 42 -27 150 -124 260 -232
                                                l187 -185 178 175 c197 195 222 216 337 282 402 232 921 156 1247 -181 131
                                                -135 216 -286 265 -470 23 -85 26 -117 26 -252 0 -135 -3 -167 -26 -252 -31
                                                -119 -89 -243 -156 -340 -35 -51 -329 -351 -961 -983 l-910 -910 -910 910
                                                c-615 614 -926 933 -959 980 -294 425 -242 980 127 1337 239 231 550 332 872
                                                283z"/>
                                                </g>
                                            </svg>
                                            <span>Wishlist</span>
                                        </a> 
                                    </div>
                                @endif
                                <div class="icon-nav d-none d-sm-inline-block">
                                    <form name="filterData" id="filterData" action="{{route('changePrimaryData')}}">
                                        @csrf <input type="hidden" id="cliLang" name="cliLang"
                                            value="{{session('customerLanguage')}}"> <input type="hidden" id="cliCur"
                                            name="cliCur" value="{{session('customerCurrency')}}"> </form>
                                    <ul class="d-flex align-items-center m-0">
                                        <li class="mr-2 pl-0 d-ipad d-none"> 
                                            <span class="mobile-search-btn d-none">
                                                <i class="fa fa-search" aria-hidden="true"></i>
                                            </span> 
                                        </li>
                                    <li class="onhover-div pl-0 shake-effect">
                                            @if($client_preference_detail)
                                            @if($client_preference_detail->cart_enable==1)
                                            <a class="btn btn-solid_al px-0"
                                                href="{{route('showCart')}}">                                             
                                                <svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet">
                                                    <metadata> Created by potrace 1.16, written by Peter Selinger 2001-2019 </metadata>
                                                    <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                                                    fill="#000000" stroke="none">
                                                    <path d="M2360 4945 c-355 -79 -629 -336 -731 -686 -16 -55 -22 -112 -26 -221
                                                    l-5 -148 -172 0 c-209 0 -279 -11 -375 -55 -131 -62 -232 -192 -260 -335 -6
                                                    -30 -40 -696 -77 -1479 -63 -1354 -65 -1427 -50 -1488 43 -174 160 -303 323
                                                    -353 64 -20 91 -20 1573 -20 1482 0 1509 0 1573 20 121 37 232 132 286 243 12
                                                    24 28 72 37 109 16 63 14 123 -50 1489 -37 783 -71 1449 -77 1479 -32 166
                                                    -154 303 -320 361 -56 20 -89 23 -275 27 l-212 4 -5 147 c-3 108 -10 165 -26
                                                    220 -96 328 -343 576 -672 672 -97 28 -359 36 -459 14z m295 -320 c212 -23
                                                    419 -192 500 -408 23 -63 44 -198 45 -289 l0 -38 -640 0 -640 0 0 38 c1 91 22
                                                    226 45 289 67 176 203 312 379 378 66 25 185 45 236 39 14 -2 48 -6 75 -9z
                                                    m1264 -1080 c40 -20 79 -70 86 -108 3 -18 35 -659 70 -1426 l65 -1394 -20 -43
                                                    c-13 -26 -36 -53 -58 -66 l-37 -23 -1465 0 -1465 0 -37 23 c-22 13 -45 40 -58
                                                    66 l-21 43 66 1404 c61 1300 68 1407 86 1446 21 44 56 72 104 84 16 4 621 7
                                                    1343 8 1135 1 1317 -1 1341 -14z"/>
                                                    <path d="M1700 3208 c-62 -31 -94 -92 -86 -163 11 -95 78 -260 149 -365 45
                                                    -66 163 -186 232 -235 110 -78 248 -137 390 -166 83 -17 294 -14 380 5 362 80
                                                    640 341 736 691 32 119 8 194 -75 234 -54 26 -98 27 -152 1 -53 -26 -71 -53
                                                    -96 -143 -57 -207 -218 -380 -418 -448 -89 -30 -240 -37 -335 -15 -121 29
                                                    -209 79 -305 176 -92 92 -131 158 -169 287 -12 40 -34 86 -48 104 -52 61 -128
                                                    75 -203 37z"/>
                                                    </g>
                                                </svg>                                          
                                                <span>{{__('Cart')}}</span>
                                                <span id="cart_qty_span"></span>
                                            </a>
                                             @endif @endif
                                            <script type="text/template" id="header_cart_template">
                                                <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a>
                                            </script>
                                            <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                        </li>
                                        <li class="onhover-dropdown mobile-account"> 
                                            {{-- <i class="fa fa-user" aria-hidden="true"></i> --}}
                                          <svg width="16" height="16" viewBox="0 0 27 28" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <mask id="mask0_3_1830" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="27" height="28">
                                                <rect x="0.154297" y="0.271164" width="26.8457" height="26.8457" fill="url(#pattern0)"/>
                                                </mask>
                                                <g mask="url(#mask0_3_1830)">
                                                <rect x="0.154297" y="0.271164" width="28.6809" height="29.3101" fill="black"/>
                                                </g>
                                                <defs>
                                                <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                                                <use xlink:href="#image0_3_1830" transform="scale(0.00195312)"/>
                                                </pattern>
                                                <image id="image0_3_1830" width="512" height="512" xlink:href="data:image/png;base64,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"/>
                                                </defs>
                                            </svg>                                                                                                                                                                                            
                                            <span>{{__('My Account')}}</span>
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
                                     
                                        @if( Session::get('vendorType') == 'p2p' )
                                        <li class="add_post"><a href="{{route('posts.index', ['fullPage'=>1])}}" class="sell-btn"><span><i class="fa fa-plus" aria-hidden="true"></i>{{ __('Add Post') }}</span></a></li>
                                        @endif
                                        <li class="mobile-menu-btn d-none">
                                            <div class="toggle-nav p-0 d-inline-block"><i
                                                    class="fa fa-bars sidebar-bar"></i></div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="icon-nav d-sm-none d-none">
                                    <ul>
                                        <li class="onhover-div mobile-search">
                                            <a href="javascript:void(0);" id="mobile_search_box_btn"><i
                                                    class="ti-search"></i></a>
                                            <div id="search-overlay" class="search-overlay">
                                                <div>
                                                    <span class="closebtn" onclick="closeSearch()"
                                                        title="Close Overlay">×</span>
                                                    <div class="overlay-content">
                                                        <div class="container">
                                                            <div class="row">
                                                                <div class="col-xl-12">
                                                                    <form>
                                                                        <div class="form-group"> <input type="text"
                                                                                class="form-control"
                                                                                id="exampleInputPassword1"
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
                                            <div data-toggle="modal" data-target="#staticBackdrop"><i
                                                    class="ti-settings"></i></div>
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
                                                    <li><a class="theme-layout-version"
                                                            href="javascript:void(0)">Dark</a></li>
                                                </ul>
                                                @endif
                                            </div>

                                            <div class=" ipad-view order-lg-3">
                                                <div
                                                    class="search_bar menu-right d-sm-flex d-block align-items-center justify-content-end w-100">
                                                    @if (Session::get('preferences')) @if(
                                                    (isset(Session::get('preferences')->is_hyperlocal)) &&
                                                    (Session::get('preferences')->is_hyperlocal==1) )
                                                    <div class="location-bar d-none align-items-center justify-content-start ml-md-2 my-2 my-lg-0 dropdown-toggle"
                                                        href="#edit-address" data-toggle="modal">
                                                        <div class="map-icon mr-md-1"><i class="fa fa-map-marker"
                                                                aria-hidden="true"></i></div>
                                                        <div class="homepage-address text-left">
                                                            <h2><span data-placement="top" data-toggle="tooltip"
                                                                    title="{{ session('selectedAddress') }}">{{ session('selectedAddress') }}</span>
                                                            </h2>
                                                        </div>
                                                        <div class="down-icon"> <i class="fa fa-angle-down"
                                                                aria-hidden="true"></i> </div>
                                                    </div>
                                                    @endif
                                                    @endif
                                                    <div class="radius-bar d-xl-inline al_custom_search">
                                                        <div
                                                            class="search_form d-flex align-items-center justify-content-between">
                                                            <button class="btn"><i class="fa fa-search"
                                                                    aria-hidden="true"></i></button> @php
                                                            $searchPlaceholder = getNomenclatureName('Search product,
                                                            vendor, item', true);
                                                            $searchPlaceholder = $searchPlaceholder === 'Search product,
                                                            vendor, item' ? __('Search product, vendor, item') :
                                                            $searchPlaceholder;
                                                            @endphp <input class="form-control border-0 typeahead"
                                                                type="search" placeholder="{{ $searchPlaceholder }}"
                                                                id="main_search_box" autocomplete="off">
                                                        </div>
                                                        <div class="list-box style-4" style="display:none;"
                                                            id="search_box_main_div"> </div>
                                                    </div>
                                                    @include('layouts.store.search_template')
                                                    @if (auth()->user())
                                                    @if ($client_preference_detail->show_wishlist == 1)
                                                    <div class="icon-nav mx-2 d-none d-sm-block"> <a class="fav-button"
                                                            href="{{ route('user.wishlists') }}"> <i class="fa fa-heart"
                                                                aria-hidden="true"></i> </a> </div>
                                                    @endif
                                                    @endif
                                                    <div class="icon-nav d-none d-sm-inline-block">
                                                        <form name="filterData" id="filterData"
                                                            action="{{ route('changePrimaryData') }}"> @csrf <input
                                                                type="hidden" id="cliLang" name="cliLang"
                                                                value="{{ session('customerLanguage') }}"> <input
                                                                type="hidden" id="cliCur" name="cliCur"
                                                                value="{{ session('customerCurrency') }}"> </form>
                                                        <ul class="d-flex align-items-center">
                                                            <li class="mr-2 pl-0 d-ipad"> <span
                                                                    class="mobile-search-btn"><i class="fa fa-search"
                                                                        aria-hidden="true"></i></span> </li>
                                                            <li class="onhover-div pl-0 shake-effect">
                                                                @if($client_preference_detail)
                                                                @if($client_preference_detail->cart_enable==1)
                                                                <a class="btn btn-solid_al d-flex align-items-center px-0"
                                                                    href="{{route('showCart')}}">
                                                                    <span class="mr-1"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 19C15 20.1046 15.8954 21 17 21C18.1046 21 19 20.1046 19 19C19 17.8954 18.1046 17 17 17H7.36729C6.86964 17 6.44772 16.6341 6.37735 16.1414M18 14H6.07143L4.5 3H2M9 5H21L19 11M11 19C11 20.1046 10.1046 21 9 21C7.89543 21 7 20.1046 7 19C7 17.8954 7.89543 17 9 17C10.1046 17 11 17.8954 11 19Z" stroke="#001A72" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>

                                                                    <!-- <span>{{__('Cart')}}</span> -->
                                                                    <span id="cart_qty_span"></span>
                                                                </a>
                                                                @endif
                                                                @endif
                                                                <script type="text/template" id="header_cart_template">
                                                                    <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{ __('Subtotal') }}: <span id='totalCart'>{{ Session::get('currencySymbol') }}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{ __('View Cart') }}</a>
                                                                </script>
                                                                <ul class="show-div shopping-cart "
                                                                    id="header_cart_main_ul"></ul>
                                                            </li>
                                                            <li class="mobile-menu-btn d-none">
                                                                <div class="toggle-nav p-0 d-inline-block"><i
                                                                        class="fa fa-bars sidebar-bar"></i></div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="icon-nav d-sm-none d-none">
                                                        <ul>
                                                            <li class="onhover-div mobile-search">
                                                                <a href="javascript:void(0);"
                                                                    id="mobile_search_box_btn"><i
                                                                        class="ti-search"></i></a>
                                                                <div id="search-overlay" class="search-overlay">
                                                                    <div>
                                                                        <span class="closebtn" onclick="closeSearch()"
                                                                            title="Close Overlay">×</span>
                                                                        <div class="overlay-content">
                                                                            <div class="container">
                                                                                <div class="row">
                                                                                    <div class="col-xl-12">
                                                                                        <form>
                                                                                            <div class="form-group">
                                                                                                <input type="text"
                                                                                                    class="form-control"
                                                                                                    id="exampleInputPassword1"
                                                                                                    placeholder="Search a Product">
                                                                                            </div>
                                                                                            <button type="submit"
                                                                                                class="btn btn-primary"><i
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
                                                                <div data-toggle="modal" data-target="#staticBackdrop">
                                                                    <i class="ti-settings"></i>
                                                                </div>
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
                                                                        <li><a class="theme-layout-version"
                                                                                href="javascript:void(0)">Dark</a></li>
                                                                    </ul>
                                                                    @endif
                                                                </div>
                                                            </li>
                                                            <li class="onhover-div mobile-cart">
                                                                <a href="{{ route('showCart') }}"
                                                                    style="position: relative"> <i
                                                                        class="ti-shopping-cart"></i> <span
                                                                        class="cart_qty_cls"
                                                                        style="display:none"></span>
                                                                </a>{{-- <span class="cart_qty_cls" style="display:none"></span> --}}
                                                                <ul class="show-div shopping-cart"> </ul>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-lg-5 col-9 order-lg-2 order-1 position-initial"> </div>
                        </div>


                    </div>
                </div>
            </div>
            <div class="menu-navigation al">
                <div class="container d-sm-none d-block">
                    <div class="al_count_tabs my-1">
                            @if($mod_count > 1)
                            <ul class="nav nav-tabs navigation-tab nav-material tab-icons vendor_mods"
                                id="top-tab" role="tablist">
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
                                    {{-- @if($client_preference_detail->delivery_check==1) @php
                                    $Delivery=getNomenclatureName('Delivery', true);
                                    $Delivery=($Delivery==='Delivery') ?
                                    __('Delivery') : $Delivery; @endphp
                                    <li class="navigation-tab-item" role="presentation"> <a
                                            class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}"
                                            id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab"
                                            aria-controls="profile" aria-selected="false">{{$Delivery}}</a> </li>
                                    @endif @if($client_preference_detail->dinein_check==1) @php
                                    $Dine_In=getNomenclatureName('Dine-In', true);
                                    $Dine_In=($Dine_In==='Dine-In') ?
                                    __('Dine-In') : $Dine_In; @endphp
                                    <li class="navigation-tab-item" role="presentation"> <a
                                            class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}"
                                            id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab"
                                            aria-controls="dinein_tab" aria-selected="false">{{$Dine_In}}</a> </li>
                                    @endif @if($client_preference_detail->takeaway_check==1)
                                    <li class="navigation-tab-item" role="presentation"> @php
                                        $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway')
                                        ? __('Takeaway') : $Takeaway; @endphp <a
                                            class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}"
                                            id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab"
                                            aria-controls="takeaway_tab" aria-selected="false">{{$Takeaway}}</a> </li>
                                    @endif --}}
                                <div class="navigation-tab-overlay"></div>
                            </ul>
                            @endif
                        </div>
                </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                {{-- @include('frontend.home_page_1.sub_menu') --}}
                                <ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider">
                                    @if(@$navCategories)
                                    @foreach($navCategories as $cate)
                                    @if($cate['name'])
                                    <li class="al_main_category">

                                @if ($client_preference_detail->view_get_estimation_in_category == 1 && $client_preference_detail->business_type == "laundry")
                                    <a href="/get-estimation#{{$cate['slug']}}">
                                        @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='userHome' || \Request::route()->getName()=='homeTest'))
                                        <div class="nav-cate-img" > <img class="blur blurload" data-src="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" src="{{$cate['icon']['image_fit']}}20/20{{$cate['icon']['image_path']}}" alt=""> </div>
                                        @endif{{$cate['name']}}
                                    </a>
                                @else
                                    <a href="{{route('categoryDetail', $cate['slug'])}}">
                                        @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='userHome' || \Request::route()->getName()=='homeTest'))
                                        <div class="nav-cate-img" > <img class="blur blurload" data-src="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" src="{{$cate['icon']['image_fit']}}20/20{{$cate['icon']['image_path']}}" alt=""> </div>
                                        @endif{{$cate['name']}}
                                    </a>
                                @endif

                                @if(!empty($cate['children']))
                                <ul class="al_main_category_list">
                                    @foreach($cate['children'] as $childs)
                                    <li>
                                        <a href="{{route('categoryDetail', $childs['slug'])}}"><span
                                                class="new-tag">{{$childs['name']}}</span></a>
                                        @if(!empty($childs['children']))
                                        <ul class="al_main_category_sub_list">
                                            @foreach($childs['children'] as $chld)
                                            <li><a
                                                    href="{{route('categoryDetail', $chld['slug'])}}">{{$chld['name']}}</a>
                                            </li>
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
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
    </div>
    @else
    {{-- first Template --}}
    <div class="main-menu al_template_one_menu">
        <div class="container-fluid d-block">
            <div class="container p-0 align-items-center justify-content-center position-initial">
                <div class="col-lg-12">
                    <div class="row mobile-header align-items-center justify-content-between">
                        {{-- @include('frontend.home_page_1.main_menu') --}}
                        <div class="logo">
                            <a class="navbar-brand mr-3 p-0 d-none d-sm-inline-flex align-items-center" style="height:60px" href="{{route('userHome')}}"><img alt="" src="{{$urlImg}}"></a>
                        </div>
                        <div class="al_count_tabs my-1 d-none d-sm-block">
                            @if($mod_count > 1)
                            <ul class="nav nav-tabs navigation-tab nav-material tab-icons vendor_mods"
                                id="top-tab" role="tablist">
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
                                  
                                <div class="navigation-tab-overlay"></div>
                            </ul>
                            @endif
                        </div>

                        <div class=" ipad-view">
                            <div class="search_bar menu-right d-sm-flex d-block align-items-center justify-content-end w-100">
                               @if(Session::get('preferences') && (isset(Session::get('preferences')->is_hyperlocal)) && (Session::get('preferences')->is_hyperlocal==1) )
                                <div class="location-bar d-none align-items-center justify-content-start ml-md-2 my-2 my-lg-0 dropdown-toggle"
                                    href="#edit-address" data-toggle="modal">
                                    <div class="map-icon mr-md-1"><i class="fa fa-map-marker" aria-hidden="true"></i>
                                    </div>
                                    <div class="homepage-address text-left">
                                        <h2><span data-placement="top" data-toggle="tooltip"
                                                title="{{session('selectedAddress')}}">{{session('selectedAddress')}}</span>
                                        </h2>
                                    </div>
                                    <div class="down-icon"> <i class="fa fa-angle-down" aria-hidden="true"></i> </div>
                                </div>
                                @endif
                                <div class="radius-bar d-xl-inline al_custom_search mr-sm-2">
                                    <div class="search_form d-flex align-items-start justify-content-start">
                                        {{-- <button class="btn">
                                            <svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet"> <metadata>
                                            Created by potrace 1.16, written by Peter Selinger 2001-2019
                                            </metadata>
                                            <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                                            fill="#000000" stroke="none">
                                            <path d="M1810 5114 c-14 -2 -59 -9 -100 -15 -176 -25 -415 -101 -580 -184
                                            -587 -295 -982 -823 -1107 -1480 -24 -127 -29 -474 -9 -615 65 -450 264 -848
                                            583 -1166 485 -482 1170 -686 1841 -548 281 58 570 187 792 353 l65 49 740
                                            -738 c472 -470 753 -743 777 -753 54 -25 145 -22 199 6 68 36 103 93 107 176
                                            3 51 0 79 -13 107 -12 26 -257 279 -756 779 l-738 740 25 30 c49 60 140 202
                                            190 300 133 254 203 507 225 807 43 575 -171 1145 -585 1561 -315 316 -716
                                            518 -1157 582 -97 14 -425 19 -499 9z m473 -449 c512 -81 970 -430 1188 -903
                                            282 -612 156 -1316 -321 -1792 -626 -627 -1626 -626 -2251 2 -238 240 -389
                                            535 -446 870 -19 115 -21 375 -4 478 63 371 216 669 472 916 277 266 611 416
                                            999 448 74 6 269 -4 363 -19z"/>
                                            </g>
                                            </svg>
                                        </button> --}}
                                        @php
                                        $searchPlaceholder=getNomenclatureName('Search', true);
                                        $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ?
                                        __('Search product, vendor, item') : $searchPlaceholder; @endphp <input
                                            class="form-control border-0 typeahead" type="search"
                                            placeholder="{{$searchPlaceholder}}" id="main_search_box"
                                            autocomplete="off"> </div>
                                         <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
                                </div>
                                @include('layouts.store.search_template')
                                @if(auth()->user() && $client_preference_detail->show_wishlist==1)
                                <div class="icon-nav mr-0 d-none d-sm-block"> 
                                    <a class="fav-button" href="{{route('user.wishlists')}}"> 
                                    <svg version="1.0" xmlns="http://www.w3.org/2000/svg"   viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet">
                                    <metadata> Created by potrace 1.16, written by Peter Selinger 2001-2019 </metadata> <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                                        fill="#000000" stroke="none"><path d="M1325 4733 c-455 -57 -857 -339 -1062 -747 -44 -87 -106 -276 -124
                                            -376 -20 -109 -18 -384 4 -490 46 -230 141 -433 286 -611 33 -41 526 -540
                                            1095 -1109 l1036 -1035 1053 1055 c823 824 1064 1071 1103 1130 102 156 169
                                            319 211 510 25 119 25 401 0 520 -42 192 -110 356 -212 510 -65 98 -260 294
                                            -355 357 -318 212 -686 286 -1052 212 -249 -50 -473 -166 -666 -344 l-83 -77
                                            -112 108 c-62 60 -146 131 -187 159 -236 157 -502 237 -781 234 -68 -1 -137
                                            -4 -154 -6z m365 -388 c155 -23 294 -76 423 -162 42 -27 150 -124 260 -232
                                            l187 -185 178 175 c197 195 222 216 337 282 402 232 921 156 1247 -181 131
                                            -135 216 -286 265 -470 23 -85 26 -117 26 -252 0 -135 -3 -167 -26 -252 -31
                                            -119 -89 -243 -156 -340 -35 -51 -329 -351 -961 -983 l-910 -910 -910 910
                                            c-615 614 -926 933 -959 980 -294 425 -242 980 127 1337 239 231 550 332 872
                                            283z"/>
                                            </g>
                                            </svg>
                                    <span>Wishlist</span>
                                    </a> 
                                </div>
                                @endif
                                <div class="icon-nav d-none d-sm-inline-block">
                                    <form name="filterData" id="filterData" action="{{route('changePrimaryData')}}">
                                        @csrf <input type="hidden" id="cliLang" name="cliLang"
                                            value="{{session('customerLanguage')}}"> <input type="hidden" id="cliCur"
                                            name="cliCur" value="{{session('customerCurrency')}}"> </form>
                                    <ul class="d-flex align-items-center m-0">
                                        <li class="mr-0 pl-0 d-ipad d-none"> 
                                            <span class="mobile-search-btn ">
                                                <i class="fa fa-search" aria-hidden="true"></i>
                                            </span> 
                                            </li>
                                        <li class="onhover-div pl-0 shake-effect">
                                            @if($client_preference_detail)
                                            @if($client_preference_detail->cart_enable==1)
                                            <a class="btn btn-solid_al px-0"
                                                href="{{route('showCart')}}">

                                                <span class="mr-1">
                                                <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
  viewBox="0 0 512.000000 512.000000"
 preserveAspectRatio="xMidYMid meet">
<metadata>
Created by potrace 1.16, written by Peter Selinger 2001-2019
</metadata>
<g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
fill="#000000" stroke="none">
<path d="M2360 4945 c-355 -79 -629 -336 -731 -686 -16 -55 -22 -112 -26 -221
l-5 -148 -172 0 c-209 0 -279 -11 -375 -55 -131 -62 -232 -192 -260 -335 -6
-30 -40 -696 -77 -1479 -63 -1354 -65 -1427 -50 -1488 43 -174 160 -303 323
-353 64 -20 91 -20 1573 -20 1482 0 1509 0 1573 20 121 37 232 132 286 243 12
24 28 72 37 109 16 63 14 123 -50 1489 -37 783 -71 1449 -77 1479 -32 166
-154 303 -320 361 -56 20 -89 23 -275 27 l-212 4 -5 147 c-3 108 -10 165 -26
220 -96 328 -343 576 -672 672 -97 28 -359 36 -459 14z m295 -320 c212 -23
419 -192 500 -408 23 -63 44 -198 45 -289 l0 -38 -640 0 -640 0 0 38 c1 91 22
226 45 289 67 176 203 312 379 378 66 25 185 45 236 39 14 -2 48 -6 75 -9z
m1264 -1080 c40 -20 79 -70 86 -108 3 -18 35 -659 70 -1426 l65 -1394 -20 -43
c-13 -26 -36 -53 -58 -66 l-37 -23 -1465 0 -1465 0 -37 23 c-22 13 -45 40 -58
66 l-21 43 66 1404 c61 1300 68 1407 86 1446 21 44 56 72 104 84 16 4 621 7
1343 8 1135 1 1317 -1 1341 -14z"/>
<path d="M1700 3208 c-62 -31 -94 -92 -86 -163 11 -95 78 -260 149 -365 45
-66 163 -186 232 -235 110 -78 248 -137 390 -166 83 -17 294 -14 380 5 362 80
640 341 736 691 32 119 8 194 -75 234 -54 26 -98 27 -152 1 -53 -26 -71 -53
-96 -143 -57 -207 -218 -380 -418 -448 -89 -30 -240 -37 -335 -15 -121 29
-209 79 -305 176 -92 92 -131 158 -169 287 -12 40 -34 86 -48 104 -52 61 -128
75 -203 37z"/>
</g>
</svg>

                                                </span>
                                                <span>{{__('Cart')}}</span>
                                                <span id="cart_qty_span"></span>
                                            </a> @endif @endif
                                            <script type="text/template" id="header_cart_template">
                                                <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a>
                                            </script>
                                            <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                        </li>
                                        <li class="mobile-menu-btn d-none">
                                            <div class="toggle-nav p-0 d-inline-block"><i
                                                    class="fa fa-bars sidebar-bar"></i></div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="icon-nav d-sm-none d-none">
                                    <ul>
                                        <li class="onhover-div mobile-search">
                                            <a href="javascript:void(0);" id="mobile_search_box_btn"><i
                                                    class="ti-search"></i></a>
                                            <div id="search-overlay" class="search-overlay">
                                                <div>
                                                    <span class="closebtn" onclick="closeSearch()"
                                                        title="Close Overlay">×</span>
                                                    <div class="overlay-content">
                                                        <div class="container">
                                                            <div class="row">
                                                                <div class="col-xl-12">
                                                                    <form>
                                                                        <div class="form-group"> <input type="text"
                                                                                class="form-control"
                                                                                id="exampleInputPassword1"
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
                                            <div data-toggle="modal" data-target="#staticBackdrop"><i
                                                    class="ti-settings"></i></div>
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
                                                    <li><a class="theme-layout-version"
                                                            href="javascript:void(0)">Dark</a></li>
                                                </ul>
                                                @endif
                                            </div>

                                            <div class=" ipad-view order-lg-3">
                                                <div
                                                    class="search_bar menu-right d-sm-flex d-block align-items-center justify-content-end w-100">
                                                    @if (Session::get('preferences')) @if(
                                                    (isset(Session::get('preferences')->is_hyperlocal)) &&
                                                    (Session::get('preferences')->is_hyperlocal==1) )
                                                    <div class="location-bar d-none align-items-center justify-content-start ml-md-2 my-2 my-lg-0 dropdown-toggle"
                                                        href="#edit-address" data-toggle="modal">
                                                        <div class="map-icon mr-md-1"><i class="fa fa-map-marker"
                                                                aria-hidden="true"></i></div>
                                                        <div class="homepage-address text-left">
                                                            <h2><span data-placement="top" data-toggle="tooltip"
                                                                    title="{{ session('selectedAddress') }}">{{ session('selectedAddress') }}</span>
                                                            </h2>
                                                        </div>
                                                        <div class="down-icon"> <i class="fa fa-angle-down"
                                                                aria-hidden="true"></i> </div>
                                                    </div>
                                                    @endif
                                                    @endif
                                                    <div class="radius-bar d-xl-inline al_custom_search">
                                                        <div
                                                            class="search_form d-flex align-items-center justify-content-between">
                                                            <button class="btn"><i class="fa fa-search"
                                                                    aria-hidden="true"></i></button> @php
                                                            $searchPlaceholder = getNomenclatureName('Search product,
                                                            vendor, item', true);
                                                            $searchPlaceholder = $searchPlaceholder === 'Search product,
                                                            vendor, item' ? __('Search product, vendor, item') :
                                                            $searchPlaceholder;
                                                            @endphp <input class="form-control border-0 typeahead"
                                                                type="search" placeholder="{{ $searchPlaceholder }}"
                                                                id="main_search_box" autocomplete="off">
                                                        </div>
                                                        <div class="list-box style-4" style="display:none;"
                                                            id="search_box_main_div"> </div>
                                                    </div>
                                                    @include('layouts.store.search_template')
                                                    @if (auth()->user())
                                                    @if ($client_preference_detail->show_wishlist == 1)
                                                    <div class="icon-nav mx-2 d-none d-sm-block"> <a class="fav-button"
                                                            href="{{ route('user.wishlists') }}"> <i class="fa fa-heart-o wishListCount"
                                                                aria-hidden="true"></i> </a> </div>
                                                    @endif
                                                    @endif
                                                    <div class="icon-nav d-none d-sm-inline-block">
                                                        <form name="filterData" id="filterData"
                                                            action="{{ route('changePrimaryData') }}"> @csrf <input
                                                                type="hidden" id="cliLang" name="cliLang"
                                                                value="{{ session('customerLanguage') }}"> <input
                                                                type="hidden" id="cliCur" name="cliCur"
                                                                value="{{ session('customerCurrency') }}"> </form>
                                                        <ul class="d-flex align-items-center">
                                                            <li class="mr-2 pl-0 d-ipad"> <span
                                                                    class="mobile-search-btn"><i class="fa fa-search"
                                                                        aria-hidden="true"></i></span> </li>
                                                            <li class="onhover-div pl-0 shake-effect">
                                                                @if($client_preference_detail)
                                                                @if($client_preference_detail->cart_enable==1)
                                                                <a class="btn btn-solid_al d-flex align-items-center px-0"
                                                                    href="{{route('showCart')}}">
                                                                    <span class="mr-1"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 19C15 20.1046 15.8954 21 17 21C18.1046 21 19 20.1046 19 19C19 17.8954 18.1046 17 17 17H7.36729C6.86964 17 6.44772 16.6341 6.37735 16.1414M18 14H6.07143L4.5 3H2M9 5H21L19 11M11 19C11 20.1046 10.1046 21 9 21C7.89543 21 7 20.1046 7 19C7 17.8954 7.89543 17 9 17C10.1046 17 11 17.8954 11 19Z" stroke="#001A72" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>

                                                                    <!-- <span>{{__('Cart')}}</span> -->
                                                                    <span id="cart_qty_span"></span>
                                                                </a>
                                                                @endif
                                                                @endif
                                                                <script type="text/template" id="header_cart_template">
                                                                    <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{ __('Subtotal') }}: <span id='totalCart'>{{ Session::get('currencySymbol') }}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{ __('View Cart') }}</a>
                                                                </script>
                                                                <ul class="show-div shopping-cart "
                                                                    id="header_cart_main_ul"></ul>
                                                            </li>
                                                            <li class="mobile-menu-btn d-none">
                                                                <div class="toggle-nav p-0 d-inline-block"><i
                                                                        class="fa fa-bars sidebar-bar"></i></div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="icon-nav d-sm-none d-none">
                                                        <ul>
                                                            <li class="onhover-div mobile-search">
                                                                <a href="javascript:void(0);"
                                                                    id="mobile_search_box_btn"><i
                                                                        class="ti-search"></i></a>
                                                                <div id="search-overlay" class="search-overlay">
                                                                    <div>
                                                                        <span class="closebtn" onclick="closeSearch()"
                                                                            title="Close Overlay">×</span>
                                                                        <div class="overlay-content">
                                                                            <div class="container">
                                                                                <div class="row">
                                                                                    <div class="col-xl-12">
                                                                                        <form>
                                                                                            <div class="form-group">
                                                                                                <input type="text"
                                                                                                    class="form-control"
                                                                                                    id="exampleInputPassword1"
                                                                                                    placeholder="Search a Product">
                                                                                            </div>
                                                                                            <button type="submit"
                                                                                                class="btn btn-primary"><i
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
                                                                <div data-toggle="modal" data-target="#staticBackdrop">
                                                                    <i class="ti-settings"></i>
                                                                </div>
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
                                                                        <li><a class="theme-layout-version"
                                                                                href="javascript:void(0)">Dark</a></li>
                                                                    </ul>
                                                                    @endif
                                                                </div>
                                                            </li>
                                                            <li class="onhover-div mobile-cart">
                                                                <a href="{{ route('showCart') }}"
                                                                    style="position: relative"> <i
                                                                        class="ti-shopping-cart"></i> <span
                                                                        class="cart_qty_cls"
                                                                        style="display:none"></span>
                                                                </a>{{-- <span class="cart_qty_cls" style="display:none"></span> --}}
                                                                <ul class="show-div shopping-cart"> </ul>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-lg-5 col-9 order-lg-2 order-1 position-initial"> </div>
                        </div>


                    </div>
                </div>
            </div>
            <div class="menu-navigation al">
                <div class="container d-sm-none d-block">
                    <div class="al_count_tabs my-1">
                            @if($mod_count > 1)
                            <ul class="nav nav-tabs navigation-tab nav-material tab-icons vendor_mods"
                                id="top-tab" role="tablist">
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
                                    {{-- @if($client_preference_detail->delivery_check==1) @php
                                    $Delivery=getNomenclatureName('Delivery', true);
                                    $Delivery=($Delivery==='Delivery') ?
                                    __('Delivery') : $Delivery; @endphp
                                    <li class="navigation-tab-item" role="presentation"> <a
                                            class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}"
                                            id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab"
                                            aria-controls="profile" aria-selected="false">{{$Delivery}}</a> </li>
                                    @endif @if($client_preference_detail->dinein_check==1) @php
                                    $Dine_In=getNomenclatureName('Dine-In', true);
                                    $Dine_In=($Dine_In==='Dine-In') ?
                                    __('Dine-In') : $Dine_In; @endphp
                                    <li class="navigation-tab-item" role="presentation"> <a
                                            class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}"
                                            id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab"
                                            aria-controls="dinein_tab" aria-selected="false">{{$Dine_In}}</a> </li>
                                    @endif @if($client_preference_detail->takeaway_check==1)
                                    <li class="navigation-tab-item" role="presentation"> @php
                                        $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway')
                                        ? __('Takeaway') : $Takeaway; @endphp <a
                                            class="nav-link {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}"
                                            id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab"
                                            aria-controls="takeaway_tab" aria-selected="false">{{$Takeaway}}</a> </li>
                                    @endif --}}
                                <div class="navigation-tab-overlay"></div>
                            </ul>
                            @endif
                        </div>
                </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                {{-- @include('frontend.home_page_1.sub_menu') --}}
                                <ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider">
                                    @if(@$navCategories)
                                    @foreach($navCategories as $cate)
                                    @if($cate['name'])
                                    <li class="al_main_category ">

                                @if ($client_preference_detail->view_get_estimation_in_category == 1 && $client_preference_detail->business_type == "laundry")
                                    <a href="/get-estimation#{{$cate['slug']}}">
                                        @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='userHome' || \Request::route()->getName()=='homeTest'))
                                            <div class="nav-cate-img" >
                                                 <img class="blur blurload" data-src="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" src="{{$cate['icon']['image_fit']}}20/20{{$cate['icon']['image_path']}}" alt="">                                                  
                                                </div>
                                        @endif
                                        <div class="" style="overflow: hidden; max-width:60px;">
                                            {{-- <span class="slide_text">{{$cate['name']}}</span> --}}
                                            @if(strlen($cate['name']) > 5)
                                            <marquee behavior="scroll" direction="left" scrollamount="3">
                                              {{$cate['name']}}
                                            </marquee>
                                          @else
                                            <span>{{$cate['name']}}</span>
                                          @endif 
                                        </div>
                                       
                                    </a>
                                @else
                                    <a href="{{route('categoryDetail', $cate['slug'])}}">
                                        @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='userHome' || \Request::route()->getName()=='homeTest'))
                                        <div class="nav-cate-img" > <img class="blur blurload" data-src="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" src="{{$cate['icon']['image_fit']}}20/20{{$cate['icon']['image_path']}}" alt=""> </div>
                                        @endif
                                        <div class="categories_menu" style="overflow: hidden; max-width:60px;">
                                            {{-- <span class="slide_text">{{$cate['name']}}</span> --}}
                                            @if(strlen($cate['name']) > 15)
                                            <marquee behavior="scroll" direction="left" scrollamount="3">
                                              {{$cate['name']}}
                                            </marquee>
                                          @else
                                            <span>{{$cate['name']}}</span>
                                          @endif 
                                        </div>
                                    </a>
                                @endif

                                @if(!empty($cate['children']))
                                <ul class="al_main_category_list">
                                    @foreach($cate['children'] as $childs)
                                    <li>
                                        <a href="{{route('categoryDetail', $childs['slug'])}}"><span
                                                class="new-tag">{{$childs['name']}}</span></a>
                                        @if(!empty($childs['children']))
                                        <ul class="al_main_category_sub_list">
                                            @foreach($childs['children'] as $chld)
                                            <li><a
                                                    href="{{route('categoryDetail', $chld['slug'])}}">{{$chld['name']}}</a>
                                            </li>
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
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
    </div>
    @endif
</article>
<div class=" @if((\Request::route()->getName() != 'userHome') || ($client_preference_detail->show_icons == 0)) inner-pages-offset @else al_offset-top-home @endif @if($client_preference_detail->hide_nav_bar == 1) set-hide-nav-bar @endif">
</div>
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
   @if ($client_preference_detail->view_get_estimation_in_category == 1 && $client_preference_detail->business_type == "laundry")
   <li class="al_main_category"> <a href="/get-estimation#<%=category.slug %>"> @if($client_preference_detail->show_icons==1 && \Request::route()->getName()=='userHome') <div class="nav-cate-img"> <img class="blur-up lazyload" data-src="<%=category.icon.image_fit %>200/200<%=category.icon.image_path %>" alt=""> </div>@endif <%=category.name %> </a> <% if(category.children){%> <ul class="al_main_category_list"> <% _.each(category.children, function(childs, key1){%> <li> <a href="/get-estimation#<%=category.slug %>"><span class="new-tag"><%=childs.name %></span></a> <% if(childs.children){%> <ul class="al_main_category_sub_list"> <% _.each(childs.children, function(chld, key2){%> <li><a href="/get-estimation#<%=category.slug %>"><%=chld.name %></a></li><%}); %> </ul> <%}%> </li><%}); %> </ul> <%}%> </li>
   @else
    <li class="al_main_category"> <a href="{{route('categoryDetail')}}/<%=category.slug %>" >
            @if($client_preference_detail->show_icons==1 && \Request::route()->getName()=='userHome') <div
                class="nav-cate-img"> <img class="blur-up lazyload" data-icon_two="<%=icon_two_url %>" data-icon="<%=category.icon.image_fit %>200/200<%=category.icon.image_path %>"
                    data-src="<%=category.icon.image_fit %>200/200<%=category.icon.image_path %>" alt="" onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'> </div>@endif
            <%=category.name %> </a> <% if(category.children){%> <ul class="al_main_category_list">
            <% _.each(category.children, function(childs, key1){%> <li> <a
                    href="{{route('categoryDetail')}}/<%=childs.slug %>"><span
                        class="new-tag"><%=childs.name %></span></a> <% if(childs.children){%> <ul
                    class="al_main_category_sub_list"> <% _.each(childs.children, function(chld, key2){%> <li><a
                            href="{{route('categoryDetail')}}/<%=chld.slug %>"><%=chld.name %></a></li><%}); %> </ul>
                <%}%> </li><%}); %> </ul> <%}%> </li>
    @endif
        <% }); %>
</script>
@if($client_preference_detail)
@if($client_preference_detail->is_hyperlocal == 1 )
<div class="modal fade edit_address" id="edit-address" tabindex="-1" aria-labelledby="edit-addressLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div id="address-map-container">
                    <div id="address-map"></div>
                </div>
                <div class="delivery_address p-2 mb-2 position-relative">
                    <button type="button" class="close edit-close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <div class="form-group address-input-group">
                        <label class="delivery-head mb-2">{{__('SELECT YOUR LOCATION')}}</label>
                        <div class="address-input-field d-flex align-items-center justify-content-between"> <i
                                class="fa fa-map-marker" aria-hidden="true"></i> <input
                                class="form-control border-0 map-input" type="text" name="address-input"
                                id="address-input" value="{{session('selectedAddress')}}"> <input type="hidden"
                                name="address_latitude" id="address-latitude" value="{{session('latitude')}}" /> <input
                                type="hidden" name="address_longitude" id="address-longitude"
                                value="{{session('longitude')}}" /> <input type="hidden" name="address_place_id"
                                id="address-place-id" value="{{session('selectedPlaceId')}}" /> </div>
                    </div>
                    <div class="text-center"> <button type="button"
                            class="btn btn-solid ml-auto confirm_address_btn w-100">{{__('Confirm And Proceed')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endif
@include('layouts.store.remove_cart_model')
@php
                $applocale = 'en';
                if(session()->has('applocale')){
                    $applocale = session()->get('applocale');
                }
                @endphp
<!-- Modal -->
<div class="modal fade mobile-setting" id="setting_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="setting-modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title" id="setting-modalLabel">Language & Currency</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-0">
        <div class="show-div setting">
            <h6 class="mb-1">{{ __("language") }}</h6>
            <ul>
                @foreach($languageList as $key => $listl)
                    <li class="{{$applocale ==  $listl->language->sort_code ?  'active' : ''}}">
                        <a href="javascript:void(0)" class="customerLang" langId="{{$listl->language_id}}">{{$listl->language->name}}@if($listl->language->id != 1)
                            ({{$listl->language->nativeName}})
                            @endif </a>
                    </li>
                @endforeach
            </ul>
            <h6 class="mb-1">{{ __("currency") }}</h6>
            <ul class="list-inline">
                @foreach($currencyList as $key => $listc)
                    <li class="{{session()->get('iso_code') ==  $listc->currency->iso_code ?  'active' : ''}}">
                        <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr " currSymbol="{{$listc->currency->symbol}}">{{$listc->currency->iso_code}}</a>
                    </li>
                @endforeach
            </ul>
        </div>
      </div>
    </div>
  </div>
</div>
