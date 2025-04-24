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
$getAdditionalPreference = getAdditionalPreference(['is_phone_signup']);
@endphp


<!-- alSpaMenuCard start -->
<section class="alSpaMenuCard">
   <div class="container-fluid d-flex align-items-center justify-content-center h-100">
      <button class="alMenuClose">×</button>
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
               @else
               <li>
                  <a href="{{route('customer.login')}}" data-lng="en">{{__('Login')}}</a>
               </li>
               @if($getAdditionalPreference['is_phone_signup'] != 1)
               <li>
                  <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a>
               </li>
               @endif
               @endif
               @if( $is_ondemand_multi_pricing ==1 )
                     @include('layouts.store.onDemandTopBarli')
               @endif
               @if($client_preference_detail->show_wishlist == 1)
               <li class="onhover-dropdown_al mobile-wishlist_al">
                  <a href="{{route('user.wishlists')}}">
                  {{__('Wishlist')}}
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
                  <span class="alLanguageSign">{{$applocale}}</span>
                  <span class="lnr-earth align-middle"></span>
                  <span class="language ml-1">{{ __("Language") }}</span>
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
                  <a href="javascript:void(0)">{{session()->get('iso_code')}}
                  <span class="icon-ic_currency align-middle"></span>
                  <span class="currency ml-1 align-middle">{{ __("currency") }}</span>
                  </a>
                  <ul class="onhover-show-div">
                     @foreach($currencyList as $key => $listc)
                     <li class="{{session()->get('iso_code') ==  $listc->currency->iso_code ?  'active' : ''}}">
                        <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr" currSymbol="{{$listc->currency->symbol}}">
                        {{$listc->Currency->iso_code}}
                        </a>
                     </li>
                     @endforeach
                  </ul>
               </li>
               @if(Auth::user())
               <li>
                  <a href="{{route('user.logout')}}" data-lng="es">{{__('Logout')}}</a>
               </li>
               @endif
            </ul>
         </li>
      </ul>
   </div>
</section>
<!-- alSpaMenuCard end -->
<div class="top-header  al_custom_head">
   <!-- site-topbar -->

   <nav class="navbar navbar-expand-lg fixed-top px-0">
      <div class="container-fluid ">
         <div class="col-12 px-3">
            <div class="row d-flex align-items-center justify-content-between">
               <!-- leftHead start -->
               <div class="leftHead d-flex align-items-center">
                  <!-- logo start -->
                  <div class="logo">
                     <a class="navbar-brand position-relative" href="{{ route('userHome') }}">
                     <img class="logo-image alspalogo lightLogo" style="height:50px;" alt="" src="{{$urlImg}}"></a>
                     </a>
                  </div>
                  <!-- logo end -->
                  <!-- alFindSec start -->
                  <div class="alFindSec  d-lg-block">
                     <ul class="p-0 m-0 d-flex align-items-center justify-content-between">
                        @if(isset($preference))
                        @if(($preference->is_hyperlocal) && ($preference->is_hyperlocal == 1))
                        <li class="alLocationArea mr-2">

                           <div class="alLocation homepage-address" href="#edit-address" data-toggle="modal" >
                              <i class="ti-location-pin"></i>
                              <!-- data-toggle="modal" data-target="#googleMapModal" -->
                              <span data-placement="top" data-toggle="tooltip" title="{{session('selectedAddress')}}">{{session('selectedAddress')}}</span>
                           </div>
                        </li>
                        @endif
                        @endif
                        <li class="pr-3">
                           <div class="d-inline-flex al_searchType align-items-center justify-content-start px-2 position-relative">
                              <button class="btn px-0"><i class="fa fa-search" aria-hidden="true"></i></button>
                                 @php $searchPlaceholder=getNomenclatureName('Search', true); $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ? __('Search product, vendor, item') : $searchPlaceholder; @endphp
                              <input class="form-control border-0 typeahead" type="search" placeholder="{{$searchPlaceholder}}" id="main_search_box" autocomplete="off">
                              <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
                           </div>
                        </li>

                        {{-- <li class="pr-3">
                           <div class="alChooseDate">
                              <input type="input" class="form-control" value="{{session('selectedDate') ?? ''}}"
                               id="inputDate" placeholder="{{session('selectedDate') ? session('selectedDate') : __('Choose Date') }} ">
                           </div>
                        </li> 
                        <li class="pr-0">
                           <div class="alFindGo">
                              <button class="btn">{{__('Go')}}</button>
                           </div>
                        </li>--}}
                     </ul>
                  </div>
                  <!-- alFindSec end -->
               </div>
               <!-- leftHead end -->
               <!-- rightHead start -->
               <div class="rightHead d-flex align-items-end">
                  <ul class="p-0 m-0 d-flex align-items-center ml-auto">
                     <!-- alUserIcon start -->
                     @if( Session::get('vendorType') == 'p2p' )
                        <li class="add_post pr-3"><a href="{{route('posts.index', ['fullPage'=>1])}}" class="sell-btn"><span> {{__('Add Post')}}</span></a></li>
                    @endif

                     <li class="alUserIcon onhover-dropdown">
                        <a href="#">
                           <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M13.0151 9.27363C14.2948 8.34031 15.1282 6.82984 15.1282 5.1282C15.1282 2.30051 12.8277 0 9.99998 0C7.17228 0 4.87177 2.30051 4.87177 5.1282C4.87177 6.82984 5.70509 8.34031 6.98486 9.27363C3.80361 10.491 1.53845 13.5754 1.53845 17.1795C1.53845 18.7347 2.80373 20 4.35896 20H15.641C17.1962 20 18.4615 18.7347 18.4615 17.1795C18.4615 13.5754 16.1963 10.491 13.0151 9.27363ZM6.41025 5.1282C6.41025 3.14883 8.0206 1.53848 9.99998 1.53848C11.9794 1.53848 13.5897 3.14883 13.5897 5.1282C13.5897 7.10758 11.9794 8.71797 9.99998 8.71797C8.0206 8.71797 6.41025 7.10758 6.41025 5.1282ZM15.641 18.4615H4.35896C3.65205 18.4615 3.07693 17.8864 3.07693 17.1795C3.07693 13.362 6.18255 10.2564 10 10.2564C13.8175 10.2564 16.9231 13.362 16.9231 17.1795C16.9231 17.8864 16.3479 18.4615 15.641 18.4615Z" fill="white"/>
                           </svg>
                        </a>
                        <ul class="onhover-show-div p-2">
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
                     <!-- alUserIcon end -->
                     <!-- alShoppingBag start -->
                     @if($client_preference_detail)
                     @if($client_preference_detail->cart_enable==1)
                     <li class="alShoppingBag mx-md-4 mx-2 dropdown">
                        <a class="alShopIcon" href="{{route('showCart')}}">
                           <span class="navbar-tool-label" id="cart_qty_span"> </span>
                           <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clip-path="url(#clip0_160_410)">
                                 <path d="M16 4H14C14 1.79 12.21 0 10 0C7.79 0 6 1.79 6 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H16C17.1 20 18 19.1 18 18V6C18 4.9 17.1 4 16 4ZM10 2C11.1 2 12 2.9 12 4H8C8 2.9 8.9 2 10 2ZM16 18H4V6H6V8C6 8.55 6.45 9 7 9C7.55 9 8 8.55 8 8V6H12V8C12 8.55 12.45 9 13 9C13.55 9 14 8.55 14 8V6H16V18Z" fill="white"/>
                              </g>
                              <defs>
                                 <clipPath id="clip0_160_410">
                                    <rect width="20" height="20" fill="white"/>
                                 </clipPath>
                              </defs>
                           </svg>
                        </a>
                        <script type="text/template" id="header_cart_template">
                           <div class='alShoppingList'>
                              <div class='widget widget-cart px-3 pt-2 pb-0' style="width: 20rem">
                                 <div class='simplebar-wrapper' style="">
                                       <div class='simplebar-content' style="padding: 0;">
                                          <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%>
                                          <li class='widget-cart-item pb-2 border-bottom' id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>">
                                             <div class='close-circle btn-close'>
                                                   <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product text-danger'> <span aria-hidden="true">×</span></a>
                                             </div>
                                             <div class="d-flex align-items-center">
                                                   <a class='media flex-shrink-0' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%>
                                                      <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>60/60<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}
                              else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%>
                                                      <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>60/60<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%>
                                                      <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%>
                                                      <div class='media-body ps-2'>
                                                         <h4 class='widget-product-title'><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4>
                                                         <h4 class='widget-product-meta'> <span class='text-muted'><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4>
                                                      </div>
                                                   </a>
                                             </div>
                                          </li>
                                          <%}); %> <%}); %>
                                       </div>
                                 </div>
                                 <div class='d-flex flex-wrap justify-content-between align-items-center pt-3'>
                                       <div class='total'>
                                          <h5>{{__('Subtotal')}}: <span class='text-muted' id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5>
                                       </div>
                                       <div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a></div>
                                 </div>
                              </div>
                           </div>
                        </script>
                        <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                     </li>
                     <!-- alShoppingBag end -->
                     @endif @endif
                     <!-- alMenuIcon start -->
                     <li class="alMenuIcon">
                        <a href="#" class="alHamBurgerIcon">
                           <svg width="34" height="16" viewBox="0 0 34 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M34 1C34 1.55228 33.5523 2 33 2H1C0.447715 2 0 1.55228 0 1C0 0.447715 0.447716 0 1 0H33C33.5523 0 34 0.447715 34 1Z" fill="white"/>
                              <path class="alSmMenu" fill-rule="evenodd" clip-rule="evenodd" d="M27.8109 8C27.8109 8.55228 27.3632 9 26.8109 9H7.18921C6.63692 9 6.18921 8.55228 6.18921 8C6.18921 7.44772 6.63692 7 7.18921 7H26.8109C27.3632 7 27.8109 7.44772 27.8109 8Z" fill="white"/>
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M34 15C34 15.5523 33.5523 16 33 16H1C0.447715 16 0 15.5523 0 15C0 14.4477 0.447716 14 1 14H33C33.5523 14 34 14.4477 34 15Z" fill="white"/>
                           </svg>
                        </a>
                     </li>
                     <!-- alMenuIcon end -->
                  </ul>
               </div>
               <!-- rightHead end -->
               @if($mod_count > 1)
               <div class="vendor_mods_section">
                  <div class="al_count_tabs_new_design "  >
                     <ul class="nav nav-tabs navigation-tab_al nav-material tab-icons vendor_mods" id="top-tab" role="tablist">
                        @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
                           @php
                           $clientVendorTypes = $vendor_typ_key.'_check';
                           $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                           $NomenclatureName = getNomenclatureName($vendor_typ_value, true);
                           $iconFiledName = config('constants.VendorTypesIcon.'.$vendor_typ_key)
                           @endphp

                           @if($client_preference_detail->$clientVendorTypes == 1)
                           <li class="navigation-tab-item" role="presentation">
                              <a class="nav-link al_delivery d-flex align-items-center {{($mod_count==1 || (Session::get('vendorType')==$VendorTypesName) || (Session::get('vendorType')=='')) ? 'active' : ''}}"
                                 id="{{$VendorTypesName}}_tab" VendorType="{{$VendorTypesName}}" data-toggle="tab" href="#{{$VendorTypesName}}_tab" role="tab"
                                 aria-controls="profile" aria-selected="false">
                                 <span class="al_tabsIcons">
                                    {{-- <img src="{{$client_preference_detail->$iconFiledName ? $client_preference_detail->$iconFiledName['proxy_url'].'36/26'.$client_preference_detail-> $iconFiledName['image_path'] : asset('images/al_custom3.png')}}" alt="{{$iconFiledName}}"></span> --}}
                                    <span class="al_textTabsText">{{$NomenclatureName}} </span>
                              </a>
                           </li>
                        @endif
                        @endforeach
                        <div class="navigation-tab-overlay_alnew_design"></div>
                     </ul>
                  </div>
               </div>
               @endif
            </div>

         </div>
      </div>
   </nav>
</div>