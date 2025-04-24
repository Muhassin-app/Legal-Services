@php
$clientData = \App\Models\Client::select('id', 'logo')->where('id', '>', 0)->first();
$urlImg = $clientData ? $clientData->logo['original'] : ' ';
$languageList = \App\Models\ClientLanguage::with('language')->where('is_active', 1)->orderBy('is_primary', 'desc')->get();
$currencyList = \App\Models\ClientCurrency::with('currency')->orderBy('is_primary', 'desc')->get();
$pages = \App\Models\Page::with(['translations' => function($q) {$q->where('language_id', session()->get('customerLanguage') ??1);}])->whereHas('translations', function($q) {$q->where(['is_published' => 1, 'language_id' => session()->get('customerLanguage') ??1]);})->orderBy('order_by','ASC')->get();
$preference = $client_preference_detail;
@endphp

<style>
.modal-backdrop {z-index: 1;}
.cardbanner {height:300px;}
.shimmer_effect .grid-row .cards {margin-bottom: 20px;}
.shimmer_effect .grid-row .card_icon{display:none;}
.shimmer_effect .grid-row .card_image{border-radius:12px;height:200px !important;}
.alOneTemplate{position: fixed !important; background-color:#fff;width: 100%;top:0;z-index: 999 !important;}
.top_bar{height:50px;}
.logoArea_bar{height:164px;margin:5px 0;}
@media(max-width:767px){.cardbanner {height:120px;}}
</style>

<!-- shimmer_effect end -->
<div class="top-header site-topbar al_template_one border-bottom">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-6">
                <div class="d-flex align-items-center justify-content-lg-start">
                    <a class="navbar-brand mr-sm-3 d-block d-sm-none" style="height:60px" href="{{ route('userHome') }}"><img alt="" src="{{$urlImg}}" height="60" ></a>
                    @if(isset($preference))
                    @if(($preference->is_hyperlocal) && ($preference->is_hyperlocal == 1))
                            <div class="location-bar d-flex align-items-center justify-content-start m-0 p-0 dropdown-toggle order-1 ellips" href="#edit-address" data-toggle="modal">
                                <div class="map-icon mr-1"><span class="yl-text">{{__('Delivering to')}}</span> <svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.848633 6.15122C0.848633 2.7594 3.60803 0 6.99985 0C10.3917 0 13.1511 2.7594 13.1511 6.15122C13.1511 8.18227 12.1614 9.98621 10.6392 11.107L7.46151 15.7563C7.3573 15.9088 7.18455 16 6.99985 16C6.81516 16 6.64237 15.9088 6.5382 15.7563L3.36047 11.107C1.8383 9.98621 0.848633 8.18227 0.848633 6.15122ZM6.99981 10.4225C7.23979 10.4225 7.47461 10.4072 7.70177 10.3806C9.73302 10.0446 11.2871 8.27613 11.287 6.15122C11.287 3.78725 9.36375 1.86402 6.99977 1.86402C4.6358 1.86402 2.71257 3.78725 2.71257 6.15122C2.71257 8.27613 4.26665 10.0446 6.29786 10.3806C6.52498 10.4072 6.75984 10.4225 6.99981 10.4225ZM9.23683 6.15089C9.23683 7.38626 8.23537 8.38772 7.00001 8.38772C5.76464 8.38772 4.76318 7.38626 4.76318 6.15089C4.76318 4.91552 5.76464 3.91406 7.00001 3.91406C8.23537 3.91406 9.23683 4.91552 9.23683 6.15089Z" fill="white"/></svg></div>
                                <div class="homepage-address text-left">
                                    <h2><span data-placement="top">{{session('selectedAddress')}}</span></h2>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="col-sm-6 text-right">
                <div class="d-inline d-sm-none">
                    @if( (Session::get('preferences')))
                        @if( (isset(Session::get('preferences')->is_hyperlocal)) && (Session::get('preferences')->is_hyperlocal == 1) )
                            <div class="location-bar d-none d-sm-flex align-items-center justify-content-start" href="#edit-address" data-toggle="modal">
                                <div class="map-icon mr-1"><i class="fa fa-map-marker" aria-hidden="true"></i></div>
                                <div class="homepage-address text-left">
                                    <h2><span data-placement="top">{{session('selectedAddress')}}</span></h2>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                @php
                $applocale = 'en';
                if(session()->has('applocale')){
                    $applocale = session()->get('applocale');
                }
                @endphp
                <ul class="header-dropdown d-none d-sm-inline">
                
                    @if( $is_ondemand_multi_pricing ==1 )
                        @include('layouts.store.onDemandTopBarli')
                    @endif
                    @if($client_preference_detail->header_quick_link == 1)
                    
                    <li class="onhover-dropdown quick-links quick-links">

                        <span class="quick-links ml-1 align-middle">{{ __('Quick Links') }}</span>
                        </a>
                        <ul class="onhover-show-div">


                            @foreach($pages as $page)
                                @if(isset($page->primary->type_of_form) && ($page->primary->type_of_form == 2))
                                @if(isset($last_mile_common_set) && $last_mile_common_set != false)
                                <li>
                                    <a href="{{route('extrapage',['slug' => $page->slug])}}">
                                        @if(isset($page->translations) && $page->translations->first()->title != null)
                                        {{ __($page->translations->first()->title) ?? ''}}
                                        @else
                                        {{ __($page->primary->title) ?? ''}}
                                        @endif
                                    </a>
                                </li>
                                @endif
                                @else
                                <li>
                                    <a href="{{route('extrapage',['slug' => $page->slug])}}" target="_blank">
                                        @if(isset($page->translations) && $page->translations->first()->title != null)
                                        {{ __($page->translations->first()->title) ?? ''}}
                                        @else
                                        {{ __($page->primary->title) ?? ''}}
                                        @endif
                                    </a>
                                </li>
                                @endif
                                @endforeach
                        </ul>
                    </li>
                    @endif
                    @if(count($languageList) > 1)
                    <li class="onhover-dropdown change-language slected-language">
                        <a href="javascript:void(0)">{{$applocale}}
                        <span class="icon-ic_lang align-middle"></span>
                        <span class="language ml-1">{{ __('language') }}</span>
                        </a>
                        <ul class="onhover-show-div">
                            @foreach($languageList as $key => $listl)
                                <li class="{{$applocale ==  $listl->language->sort_code ?  'active' : ''}}">
                                    <a href="javascript:void(0)" class="customerLang" langId="{{$listl->language_id}}">{{$listl->language->name}}
                                        @if($listl->language->id != 1)
                                            ({{$listl->language->nativeName}})
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif
                    @if(count($currencyList) > 1)
                    <li class="onhover-dropdown change-currency slected-language">
                        <a href="#">{{session()->get('iso_code')}} <span class="icon-ic_currency align-middle"></span>
                        <span class="currency ml-1">{{ __('currency') }}</span> </a>
                        <ul class="onhover-show-div">
                            @foreach($currencyList as $key => $listc)
                                <li class="{{session()->get('iso_code') ==  $listc->currency->iso_code ?  'active' : ''}}">
                                    <a href="javascript:void(0)" currId="{{$listc->currency_id}}" class="customerCurr " currSymbol="{{$listc->currency->symbol}}">{{$listc->currency->iso_code}}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif
                    @if( Session::get('vendorType') != 'p2p')
                    <li class="onhover-dropdown mobile-account"> <i class="fa fa-user" aria-hidden="true"></i>
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
                    @endif
                </ul>
            </div>

        </div>
    </div>

    <div class="mobile-menu main-menu position-fixed d-block d-sm-none">
        <div class="menu-right_ oneTemplateMobile">
            <ul class="header-dropdown icon-nav d-flex justify-content-around">
                <li class="onhover-div mobile-setting al_iconsMb">
                    <div data-toggle="modal" data-target="#setting_modal"><i class="ti-settings"></i></div>
                </li>

                <li class="onhover-dropdown_al mobile-account  d-inline d-sm-none al_iconsMb">
                    <i class="fa fa-user" aria-hidden="true"></i>

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
                
                @if( Session::get('vendorType') == 'p2p' )
                    <li class="add_post"><a href="{{route('posts.index', ['fullPage'=>1])}}" class="sell-btn">
                        <span>
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            {{ __('') }}</span>
                        </a></li>
                @endif

                @if($client_preference_detail->show_wishlist == 1)
                <li class="mobile-wishlist d-inline d-sm-none al_iconsMb">
                    <a href="{{route('user.wishlists')}}">
                        <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i>
                    </a>
                </li>
                @endif
                <li class="onhover-div al_mobile-search al_iconsMb">
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
                <li class="onhover-div mobile-cart al_iconsMb">
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
    </div>


</div>


