@php
$clientData = \App\Models\Client::select('id', 'logo','dark_logo')->where('id', '>', 0)->first();
if(Session::get('config_theme') == 'dark'){
    $urlImg = $clientData ? $clientData->dark_logo['original'] : ' ';
}else{
    $urlImg = $clientData ? $clientData->logo['original'] : ' ';
}
$languageList = \App\Models\ClientLanguage::with('language')->where('is_active', 1)->orderBy('is_primary', 'desc')->get();
$currencyList = \App\Models\ClientCurrency::with('currency')->orderBy('is_primary', 'desc')->get();
@endphp
<style>.cab-booking-header{display: none;}</style>
<header class="site-header">
   @include('layouts.store/topbar-template-two')
   <!-- Start Cab Booking Header From Here -->
   <div class="cab-booking-header"> 
      <div class="container-fluid">
         <div class="row d-flex justify-content-start align-items-center">
            <div class="col-6"> <a class="navbar-brand mr-0"  href="{{route('userHome')}}"><img alt="" style="height:60px;width:auto" class="logo-image" src="{{$urlImg}}"></a> </div>
            <div class="col-6 text-right top-header bg-transparent d-md-block d-none">
               <ul class="header-dropdown ml-auto">
                  @if(count($languageList) > 1)
                  <li class="onhover-dropdown change-language">
                     <a href="javascript:void(0)">{{session()->get('locale')}}<span class="icon-ic_lang align-middle"></span> <span class="language ml-1 align-middle">{{__('language')}}</span> </a>
                     <ul class="onhover-show-div">
                        @foreach($languageList as $key=> $listl)
                        <li class="{{session()->get('locale')==$listl->language->sort_code ? 'active' : ''}}"> <a href="javascript:void(0)" class="customerLang" langId="{{$listl->language_id}}">{{$listl->language->name}}</a> </li>
                        @endforeach
                     </ul>
                  </li>
                  @endif
                  @if(count($currencyList) > 1)
                  <li class="onhover-dropdown change-currency">
                     <a href="javascript:void(0)">{{session()->get('iso_code')}}<span class="icon-ic_currency align-middle"></span> <span class="currency ml-1 align-middle">{{('currency')}}</span> </a>
                     <ul class="onhover-show-div">
                        @foreach($currencyList as $key=> $listc)
                        <li class="{{session()->get('iso_code')==$listc->currency->iso_code ? 'active' : ''}}"> <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr" currSymbol="{{$listc->currency->symbol}}">{{$listc->currency->iso_code}}</a> </li>
                        @endforeach
                     </ul>
                  </li>
                  @endif
                  @if(Auth::guest())
                  <li class="onhover-dropdown mobile-account">
                     <i class="fa fa-user" aria-hidden="true"></i>{{__('Account')}}
                     <ul class="onhover-show-div">
                        <li> <a href="{{route('customer.login')}}" data-lng="en">{{__('Login')}}</a> </li>
                        <li> <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a> </li>
                     </ul>
                  </li>
                  @else
                  <li class="onhover-dropdown mobile-account">
                     <i class="fa fa-user" aria-hidden="true"></i>{{__('Account')}}
                     <ul class="onhover-show-div">
                        @if(Auth::user()->is_superadmin==1 || Auth::user()->is_admin==1)
                        <li> <a href="{{route('client.dashboard')}}" data-lng="en">{{getNomenclatureName('Control Panel', true)}}</a> </li>
                        @endif
                        <li> <a href="{{route('user.profile')}}" data-lng="en">{{__('Profile')}}</a> </li>
                        <li> <a href="{{route('user.logout')}}" data-lng="es">{{__('Logout')}}</a> </li>
                     </ul>
                  </li>
                  @endif
               </ul>
            </div>
            <div class="al_mobile_menu al_new_mobile_header">
               <a class="al_toggle-menu" href="#">
                    <i></i>
                    <i></i>
                    <i></i>
               </a>
               <div class="al_menu-drawer">
                  <ul class="header-dropdown">
                     @if(count($languageList) > 1)
                     <li class="onhover-dropdown change-language">
                        <a href="javascript:void(0)">{{session()->get('locale')}}<span class="icon-ic_lang align-middle"></span> <span class="language ml-1 align-middle">{{__('language')}}</span> </a>
                        <ul class="onhover-show-div">
                           @foreach($languageList as $key=> $listl)
                           <li class="{{session()->get('locale')==$listl->language->sort_code ? 'active' : ''}}"> <a href="javascript:void(0)" class="customerLang" langId="{{$listl->language_id}}">{{$listl->language->name}}</a> </li>
                           @endforeach
                        </ul>
                     </li>
                     @endif
                     @if(count($currencyList) > 1)
                     <li class="onhover-dropdown change-currency">
                        <a href="javascript:void(0)">{{session()->get('iso_code')}}<span class="icon-ic_currency align-middle"></span> <span class="currency ml-1 align-middle">{{('currency')}}</span> </a>
                        <ul class="onhover-show-div">
                           @foreach($currencyList as $key=> $listc)
                           <li class="{{session()->get('iso_code')==$listc->currency->iso_code ? 'active' : ''}}"> <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr" currSymbol="{{$listc->currency->symbol}}">{{$listc->currency->iso_code}}</a> </li>
                           @endforeach
                        </ul>
                     </li>
                     @endif
                     @if(Auth::guest())
                     <li class="onhover-dropdown mobile-account">
                        <i class="fa fa-user" aria-hidden="true"></i>{{__('Account')}}
                        <ul class="onhover-show-div">
                           <li> <a href="{{route('customer.login')}}" data-lng="en">{{__('Login')}}</a> </li>
                           <li> <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a> </li>
                        </ul>
                     </li>
                     @else
                     <li class="onhover-dropdown mobile-account">
                        <i class="fa fa-user" aria-hidden="true"></i>{{__('Account')}}
                        <ul class="onhover-show-div">
                           @if(Auth::user()->is_superadmin==1 || Auth::user()->is_admin==1)
                           <li> <a href="{{route('client.dashboard')}}" data-lng="en">{{getNomenclatureName('Control Panel', true)}}</a> </li>
                           @endif
                           <li> <a href="{{route('user.profile')}}" data-lng="en">{{__('Profile')}}</a> </li>
                           <li> <a href="{{route('user.logout')}}" data-lng="es">{{__('Logout')}}</a> </li>
                        </ul>
                     </li>
                     @endif
                  </ul>
               </div>
            </div>
         </div>
         <div class="al_mobile_menu al_new_mobile_header">
               <a class="al_toggle-menu" href="#">
                    <i></i>
                    <i></i>
                    <i></i>
               </a>
               <div class="al_menu-drawer">
                  <ul class="header-dropdown">
                     @if(count($languageList) > 1)
                     <li class="onhover-dropdown change-language">
                        <a href="javascript:void(0)">{{session()->get('locale')}}<span class="icon-ic_lang align-middle"></span> <span class="language ml-1 align-middle">{{__('language')}}</span> </a>
                        <ul class="onhover-show-div">
                           @foreach($languageList as $key=> $listl)
                           <li class="{{session()->get('locale')==$listl->language->sort_code ? 'active' : ''}}"> <a href="javascript:void(0)" class="customerLang" langId="{{$listl->language_id}}">{{$listl->language->name}}</a> </li>
                           @endforeach
                        </ul>
                     </li>
                     @endif
                     @if(count($currencyList) > 1)
                     <li class="onhover-dropdown change-currency">
                        <a href="javascript:void(0)">{{session()->get('iso_code')}}<span class="icon-ic_currency align-middle"></span> <span class="currency ml-1 align-middle">{{('currency')}}</span> </a>
                        <ul class="onhover-show-div">
                           @foreach($currencyList as $key=> $listc)
                           <li class="{{session()->get('iso_code')==$listc->currency->iso_code ? 'active' : ''}}"> <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr" currSymbol="{{$listc->currency->symbol}}">{{$listc->currency->iso_code}}</a> </li>
                           @endforeach
                        </ul>
                     </li>
                     @endif
                     @if(Auth::guest())
                     <li class="onhover-dropdown mobile-account">
                        <i class="fa fa-user" aria-hidden="true"></i>{{__('Account')}}
                        <ul class="onhover-show-div">
                           <li> <a href="{{route('customer.login')}}" data-lng="en">{{__('Login')}}</a> </li>
                           <li> <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a> </li>
                        </ul>
                     </li>
                     @else
                     <li class="onhover-dropdown mobile-account">
                        <i class="fa fa-user" aria-hidden="true"></i>{{__('Account')}}
                        <ul class="onhover-show-div">
                           @if(Auth::user()->is_superadmin==1 || Auth::user()->is_admin==1)
                           <li> <a href="{{route('client.dashboard')}}" data-lng="en">{{getNomenclatureName('Control Panel', true)}}</a> </li>
                           @endif
                           <li> <a href="{{route('user.profile')}}" data-lng="en">{{__('Profile')}}</a> </li>
                           <li> <a href="{{route('user.logout')}}" data-lng="es">{{__('Logout')}}</a> </li>
                        </ul>
                     </li>
                     @endif
                  </ul>
               </div>
            </div>
      </div>
   </div>
   <!-- End Cab Booking Header From Here -->

   <div class="container main-menu d-block ">
      <div class="row align-items-center py-md-0 position-initial py-0">
         <!-- @include('frontend.home_page_2.main_menu')  -->
         <div class="col-lg-2 col-5">
            <a class="navbar-brand mr-0 py-0"  href="{{route('userHome')}}">
               <img id="theme-logo" class="logo-image" alt="" style="height:70px;padding:5px 0" src="{{$urlImg}}">
            </a>
         </div>
         <div class="col-lg-7 main-menu order-lg-1 order-2 d-sm-block d-none">
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
                     <button class="btn">
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
                     </button>
                     <input class="form-control border-0 typeahead" type="search" placeholder="{{getNomenclatureName('Search', true)}}" id="main_search_box">
                  </div>
                  <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
               </div>

               @include('layouts.store.search_template')
               @if(auth()->user()) @if($client_preference_detail->show_wishlist==1) <div class="icon-nav mx-2 d-none d-sm-block">
                   <a href="{{route('user.wishlists')}}">
                   <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
  viewBox="0 0 512.000000 512.000000"
 preserveAspectRatio="xMidYMid meet">
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
                     </a> </div>@endif @endif <div class="icon-nav"> <form name="filterData" id="filterData" action="{{route('changePrimaryData')}}"> @csrf <input type="hidden" id="cliLang" name="cliLang" value="{{session('customerLanguage')}}"> <input type="hidden" id="cliCur" name="cliCur" value="{{session('customerCurrency')}}"> </form> <ul class="d-flex align-items-center">
                  <li class="onhover-div pl-0 shake-effect"> @if($client_preference_detail) @if($client_preference_detail->cart_enable==1) 
                     <a class="btn btn-solid_al " href="{{route('showCart')}}">
                  <span class="mr-1">
                  <svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet">
                       
                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
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
                        1343 8 1135 1 1317 -1 1341 -14z"></path>
                        <path d="M1700 3208 c-62 -31 -94 -92 -86 -163 11 -95 78 -260 149 -365 45
                        -66 163 -186 232 -235 110 -78 248 -137 390 -166 83 -17 294 -14 380 5 362 80
                        640 341 736 691 32 119 8 194 -75 234 -54 26 -98 27 -152 1 -53 -26 -71 -53
                        -96 -143 -57 -207 -218 -380 -418 -448 -89 -30 -240 -37 -335 -15 -121 29
                        -209 79 -305 176 -92 92 -131 158 -169 287 -12 40 -34 86 -48 104 -52 61 -128
                        75 -203 37z"></path>
                        </g>
                        </svg>
                  </span>

                      <span>Cart</span>
                      <span id="cart_qty_span"></span> </a> @endif @endif <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotals')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script> <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul> </li><li class=" d-none"><div class="toggle-nav p-0 d-inline-block"><i class="fa fa-bars sidebar-bar"></i></div></li></ul> </div><div class="icon-nav d-sm-none d-none"> <ul> <li class="onhover-div mobile-search"> <a href="javascript:void(0);" id="mobile_search_box_btn"><i class="ti-search"></i></a> <div id="search-overlay" class="search-overlay"> <div> <span class="closebtn" onclick="closeSearch()" title="Close Overlay">×</span> <div class="overlay-content"> <div class="container"> <div class="row"> <div class="col-xl-12"> <form> <div class="form-group"> <input type="text" class="form-control" id="exampleInputPassword1" placeholder={{__("Search a Product")}}> </div><button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button> </form> </div></div></div></div></div></div></li><li class="onhover-div mobile-setting"> <div data-toggle="modal" data-target="#staticBackdrop"><i class="ti-settings"></i></div><div class="show-div setting"> <h6>{{__('language')}}</h6> <ul> <li><a href="#">{{__("english")}}</a></li><li><a href="#">{{__("french")}}</a></li></ul> <h6>{{__("currency")}}</h6> <ul class="list-inline"> <li><a href="#">{{__("euro")}}</a></li><li><a href="#">{{__("rupees")}}</a></li><li><a href="#">{{__("pound")}}</a></li><li><a href="#">{{__("doller")}}</a></li></ul> <h6>{{__("Change Theme")}}</h6> @if($client_preference_detail->show_dark_mode==1) <ul class="list-inline"> <li><a class="theme-layout-version" href="javascript:void(0)">{{__("Dark")}}</a></li></ul> @endif </div></li><li class="onhover-div mobile-cart"> <a href="{{route('showCart')}}" style="position: relative"> <i class="ti-shopping-cart"></i> <span class="cart_qty_cls" style="display:none"></span> </a>{{--<span class="cart_qty_cls" style="display:none"></span>--}}<ul class="show-div shopping-cart"> </ul> </li></ul> </div></div>
         </div>
      </div>
   </div>

   <div class="menu-navigation al_template_two_menu"> 
                  <div class="container al_count_tabs menu-navigation al d-sm-none d-block">
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
                  </div>
   @if(@$navCategories && count($navCategories))
      <div class="container-fluid"> 
         <div class="row"> 
            <div class="col-12">
               {{-- @include('frontend.home_page_2.sub_menu') --}}
               <ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider">
                  @foreach ($navCategories as $cate)
                     @if ($cate['name'])
                        <li> <a href="{{ route('categoryDetail', $cate['slug']) }}">
                                 @if ($client_preference_detail->show_icons == 1 && (\Request::route()->getName() == 'userHome' || \Request::route()->getName() == 'homeTest'))
                                    <img class="blur-up lazyload"
                                          data-icon_two="{{ !is_null($cate['icon_two']) ? $cate['icon_two']['image_fit'] . '200/200' . $cate['icon_two']['image_path'] : $cate['icon']['image_fit'] . '200/200' . $cate['icon']['image_path'] }}"
                                          data-icon="{{ $cate['icon']['image_fit'] }}200/200{{ $cate['icon']['image_path'] }}"
                                          data-src="{{ $cate['icon']['image_fit'] }}200/200{{ $cate['icon']['image_path'] }}"
                                          alt="" onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'>
                                    @endif{{ $cate['name'] }}
                              </a>
                              @if (!empty($cate['children']))
                                 <ul>
                                    @foreach ($cate['children'] as $childs)
                                          <li> <a href="{{ route('categoryDetail', $childs['slug']) }}"><span
                                                      class="new-tag">{{ $childs['name'] }}</span></a>
                                             @if (!empty($childs['children']))
                                                <ul>
                                                      @foreach ($childs['children'] as $chld)
                                                         <li><a
                                                                  href="{{ route('categoryDetail', $chld['slug']) }}">{{ $chld['name'] }}</a>
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
               </ul>

            </div>
         </div>
      </div>
   @endif

   </div>

</header>
<div class="offset-top_al @if((\Request::route()->getName() != 'userHome') || ($client_preference_detail->show_icons == 0)) inner-pages-offset @else al_offset-top-home @endif @if($client_preference_detail->hide_nav_bar == 1) set-hide-nav-bar @endif"></div>
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
      <li> <a href="{{route('categoryDetail')}}/<%=category.slug %>"> @if($client_preference_detail->show_icons==1 && \Request::route()->getName()=='userHome') <img class="blur-up lazyload"  data-icon_two="<%=icon_two_url %>" data-src="<%=category.icon.image_fit %>200/200<%=category.icon.image_path %>" data-icon="<%=category.icon.image_fit %>200/200<%=category.icon.image_path %>" alt="" onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'> @endif <%=category.name %> </a> <% if(category.children){%> <ul> <% _.each(category.children, function(childs, key1){%> <li> <a href="{{route('categoryDetail')}}/<%=childs.slug %>"><span class="new-tag"><%=childs.name %></span></a> <% if(childs.children){%> <ul> <% _.each(childs.children, function(chld, key2){%> <li><a href="{{route('categoryDetail')}}/<%=chld.slug %>"><%=chld.name %></a></li><%}); %> </ul> <%}%> </li><%}); %> </ul> <%}%> </li>
   <% }); %>
</script>
@if( $client_preference_detail)
    @if( $client_preference_detail->is_hyperlocal == 1 )
<div class="modal fade edit_address" id="edit-address" tabindex="-1" aria-labelledby="edit-addressLabel" aria-hidden="true"> <div class="modal-dialog modal-dialog-centered"> <div class="modal-content"> <div class="modal-body p-0"> <div id="address-map-container"> <div id="address-map"></div></div><div class="delivery_address p-2 mb-2 position-relative"> <button type="button" class="close edit-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> <div class="form-group"> <label class="delivery-head mb-2">{{__('SELECT YOUR LOCATION')}}</label> <div class="address-input-field d-flex align-items-center justify-content-between"> <svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.848633 6.15122C0.848633 2.7594 3.60803 0 6.99985 0C10.3917 0 13.1511 2.7594 13.1511 6.15122C13.1511 8.18227 12.1614 9.98621 10.6392 11.107L7.46151 15.7563C7.3573 15.9088 7.18455 16 6.99985 16C6.81516 16 6.64237 15.9088 6.5382 15.7563L3.36047 11.107C1.8383 9.98621 0.848633 8.18227 0.848633 6.15122ZM6.99981 10.4225C7.23979 10.4225 7.47461 10.4072 7.70177 10.3806C9.73302 10.0446 11.2871 8.27613 11.287 6.15122C11.287 3.78725 9.36375 1.86402 6.99977 1.86402C4.6358 1.86402 2.71257 3.78725 2.71257 6.15122C2.71257 8.27613 4.26665 10.0446 6.29786 10.3806C6.52498 10.4072 6.75984 10.4225 6.99981 10.4225ZM9.23683 6.15089C9.23683 7.38626 8.23537 8.38772 7.00001 8.38772C5.76464 8.38772 4.76318 7.38626 4.76318 6.15089C4.76318 4.91552 5.76464 3.91406 7.00001 3.91406C8.23537 3.91406 9.23683 4.91552 9.23683 6.15089Z" fill="white"/></svg> <input class="form-control border-0 map-input" type="text" name="address-input" id="address-input" value="{{session('selectedAddress')}}"> <input type="hidden" name="address_latitude" id="address-latitude" value="{{session('latitude')}}"/> <input type="hidden" name="address_longitude" id="address-longitude" value="{{session('longitude')}}"/> <input type="hidden" name="address_place_id" id="address-place-id" value="{{session('selectedPlaceId')}}"/> </div></div><div class="text-center"> <button type="button" class="btn btn-solid ml-auto confirm_address_btn w-100">{{__('Confirm And Proceed')}}</button> </div></div></div></div></div></div>
@endif
@endif
@include('layouts.store.remove_cart_model')
