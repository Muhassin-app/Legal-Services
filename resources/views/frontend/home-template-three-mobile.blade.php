@extends('layouts.store', ['title' => __('Home')]) @section('content')
@php
$clientData = \App\Models\Client::select('id', 'logo')->where('id', '>', 0)->first();
$urlImg = $clientData->logo['image_fit'].'150/60'.$clientData->logo['image_path'];
$languageList = \App\Models\ClientLanguage::with('language')->where('is_active', 1)->orderBy('is_primary', 'desc')->get();
$currencyList = \App\Models\ClientCurrency::with('currency')->orderBy('is_primary', 'desc')->get();
$pages = \App\Models\Page::with(['translations' => function($q) {$q->where('language_id', session()->get('customerLanguage') ??1);}])->whereHas('translations', function($q) {$q->where(['is_published' => 1, 'language_id' => session()->get('customerLanguage') ??1]);})->orderBy('order_by','ASC')->get();
$preference = $client_preference_detail;
@endphp
@php
$applocale = 'en';
if(session()->has('applocale')){
    $applocale = session()->get('applocale');
}
@endphp

<style>
.onhover-show-div li{display: block;padding: 10px ;}
.icon-ic_currency:before {color: #777;}
.dark .icon-ic_currency:before {color: #fff;}
.section-b-space_al_shimer{background-color: #fff;z-index: 99999;}
.al_menu-drawer {width: 0;display: none;background-color: #fff;height: 100vh;position: absolute;right: -350px;top: 0;z-index: 9999;transition: right linear .2s;}
.al_menu-drawer.open {padding: 50px 10px;width: 250px;display: block;right: 0;-webkit-transition: right linear .2s;transition: right linear .2s;box-shadow: 0 0 5px rgb(0 0 0 / 50%);}
.al_only_mobile_wrapper svg{fill: #FA1C0A;height:20px;}
.al_toggle-menu.active i:nth-child(1) {top: 25px;-webkit-transform: rotateZ(45deg);transform: rotateZ(45deg);}
.al_toggle-menu i:nth-child(1) {top: 16px;}.al_toggle-menu i {margin: 0 auto;right: 0;position: absolute;display: block;height: 2px;background: #777;width: 24px;left: 0;-webkit-transition: all .3s;transition: all .3s;}
.al_mobile_search_thrid_template {background-color: #fff;border: 1px solid #eee;border-radius: 15px;box-shadow: 0 0 3px rgb(0 0 0 / 20%);overflow: hidden;}
.al_toggle-menu {width: 50px;height: 50px;display: inline-block;position: relative;top: 0;z-index: 99999;}
.al_mobile_main_category li.al_main_category {text-align: center;vertical-align: text-top;}
.nav-cate-img img {width: 100%;height: 100%;position: absolute;left: 0;right: 0;bottom: 0;top: 0;margin: auto;}
.al_mobile_main_category li.al_main_category .nav-cate-img {border-radius: 50%;overflow: hidden;height: 70px;width: 70px;position: relative;margin: 0 auto 20px;}
.al_only_mobile_wrapper a{color: #777;}
.al_more_see_more{cursor: pointer; background-color: #fff;border: 1px solid #eee;border-radius: 15px;box-shadow: 0 0 3px rgb(0 0 0 / 20%);overflow: hidden;}
.fixed-top {top: -40px;transform: translateY(40px);transition: transform .3s;}
.al_count_tabs_new_design.al_tab_mobile {bottom: 0;background-color: #fff;box-shadow: 0 0 10px rgb(0 0 0 / 20%);width: 100%;left: 0;z-index: 99;}
.al_count_tabs_new_design.al_tab_mobile li a.nav-link span {margin-bottom: 5px; display: block;margin: 0 auto;width: 40px;height: 40px;line-height: 40px;padding: 0;}
</style>

<script type="text/javascript">

</script>
<section class="al_only_mobile_wrapper">

    <!-- shimmer_effect start -->
    <article class="section-b-space_al_shimer position-absolute  p-0 ratio_asos">
        <div class="container-fulid mb-5 shimmer_effect main_shimer">
            <div class="row ">
                <div class="col-12 cards d-flex justify-content-between">
                        <h2 class="h2-heading loading mb-3"></h2>
                        <span class="p-5 loading"></span>
                </div>
            </div>
            <div class="row">
                <div class="col-12 cards">
                    <h2 class="h2-heading loading mb-3"></h2> </div>
            </div>
            <div class="row">
                <div class="col-12 cards">
                <h2 class="h2-heading loading mb-3" style="height:80px"></h2>
                </div>
            </div>
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

    </article>
    <!-- shimmer_effect end -->

    <article class="al_mobile_third_template_header">
        <div class="container-fluid">
            <div class="row d-md-flex align-items-center justify-content-between al_mobile_third_header">
                @if(isset($preference))
                @if(($preference->is_hyperlocal) && ($preference->is_hyperlocal == 1))
                <div class="location-bar d-flex justify-content-around position-relative pl-2" href="#edit-address" data-toggle="modal">
                   <svg version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 368.666 368.666" style="enable-background:new 0 0 368.666 368.666;" ><g id="XMLID_2_"><g><g><path d="M184.333,0C102.01,0,35.036,66.974,35.036,149.297c0,33.969,11.132,65.96,32.193,92.515c27.27,34.383,106.572,116.021,109.934,119.479l7.169,7.375l7.17-7.374c3.364-3.46,82.69-85.116,109.964-119.51c21.042-26.534,32.164-58.514,32.164-92.485C333.63,66.974,266.656,0,184.333,0z M285.795,229.355c-21.956,27.687-80.92,89.278-101.462,110.581c-20.54-21.302-79.483-82.875-101.434-110.552c-18.228-22.984-27.863-50.677-27.863-80.087C55.036,78.002,113.038,20,184.333,20c71.294,0,129.297,58.002,129.296,129.297C313.629,178.709,304.004,206.393,285.795,229.355z"/><path d="M184.333,59.265c-48.73,0-88.374,39.644-88.374,88.374c0,48.73,39.645,88.374,88.374,88.374s88.374-39.645,88.374-88.374S233.063,59.265,184.333,59.265z M184.333,216.013c-37.702,0-68.374-30.673-68.374-68.374c0-37.702,30.673-68.374,68.374-68.374s68.373,30.673,68.374,68.374C252.707,185.341,222.035,216.013,184.333,216.013z"/></g></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
                    <p class="homepage-address m-0"><span data-placement="top" data-toggle="tooltip" title="{{session('selectedAddress')}}">{{session('selectedAddress')}}</span></p>
                </div>
                @endif
                @endif
                <div class="al_mobile_menu_new al_new_mobile_header">
                    <a class="al_toggle-menu" href="#">
                        <i></i>
                        <i></i>
                        <i></i>
                    </a>
                    <div class="al_menu-drawer" id="navbarsfoodTemplate">
                        <a class="navbar-brand m-0" style="max-width:100px;" href="{{ route('userHome') }}"><img style="width:100%;height:auto" alt="" src="{{$urlImg}}"></a>
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
                                    <li>
                                        <a href="{{route('customer.register')}}" data-lng="es">{{__('Register')}}</a>
                                    </li>
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
                </div>
            </div>
        </div>
    </article>

    <article class="al_mobile_third_template_search_area sticky-top mb-2">
        <div class="container-fluid">
            <div class="row">
                <div class=" position-relative col-12">
                    <div class="al_mobile_search_thrid_template d-flex justify-content-around w-100">
                        <button class="btn pr-0"><svg version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 487.95 487.95" style="enable-background:new 0 0 487.95 487.95;"><g><g><path d="M481.8,453l-140-140.1c27.6-33.1,44.2-75.4,44.2-121.6C386,85.9,299.5,0.2,193.1,0.2S0,86,0,191.4s86.5,191.1,192.9,191.1c45.2,0,86.8-15.5,119.8-41.4l140.5,140.5c8.2,8.2,20.4,8.2,28.6,0C490,473.4,490,461.2,481.8,453z M41,191.4c0-82.8,68.2-150.1,151.9-150.1s151.9,67.3,151.9,150.1s-68.2,150.1-151.9,150.1S41,274.1,41,191.4z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></button>
                        @php $searchPlaceholder=getNomenclatureName('Search', true); $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ? __('Search product, vendor, item') : $searchPlaceholder; @endphp
                        <input class="form-control border-0 typeahead" type="search" placeholder="{{$searchPlaceholder}}" id="main_search_box" autocomplete="off">
                        <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <article class="al_mobile_third_template_banner_area mb-2">
        <div class="container-fluid">
            <div id="myCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">

                    @foreach($banners as $key => $banner)
                        @php $url=''; if($banner->link=='category'){if($banner->category !=null){$url=route('categoryDetail', $banner->category->slug);}}else if($banner->link=='vendor'){if($banner->vendor !=null){$url=route('vendorDetail', $banner->vendor->slug);}}@endphp
                        <div class="carousel-item @if($key == 0) active @endif">
                        <a class="banner-img-outer" href="{{$url??'#'}}">
                            <img style="" alt="" title="" class="blur-up lazyload w-100" data-src="{{$banner->image['proxy_url'] . '1370/300' . $banner->image['image_path']}}">
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
        </div>
    </article>

    <article class="al_mobile_main_category mb-2">
        <div class="container-fluid">
            <h4 class="al_new_mobile_heading_template">Category</h4>
            <ul id="main-menu_" class="row d-flex align-items-stretch justify-content-around">

                  @foreach($navCategories as $key => $cate)

                    @if($cate['name'])
                        <li class="al_main_category col-sm-3 col-3 my-1 {{$key >7 ? 'd-none' : '' }}" >
                            <a href="{{route('categoryDetail', $cate['slug'])}}">
                                @if($client_preference_detail->show_icons==1 && \Request::route()->getName()=='userHome')
                                <div class="nav-cate-img"> <img  class="blur-up lazyload" data-src="{{$cate['icon']['image_fit']}}80/80{{$cate['icon']['image_path']}}" alt=""> </div>
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
                  @if(count($navCategories)>8)
                    <div class="col-sm-12 mt-3">
                        <a class="w-100 al_more_see_more d-block text-center p-2">See more</a>
                    </div>
                    @endif

                </ul>

        </div>
    </article>

    <article class="al_count_tabs_new_design al_tab_mobile position-fixed">
        <div class="container-fluid">

            @if($mod_count > 1)
            <ul class="row nav nav-tabs navigation-tab_al nav-material tab-icons mr-lg-3 vendor_mods d-flex justify-content-around" id="top-tab" role="tablist">
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
                        <span><img src="{{$client_preference_detail->deliveryicon ? $client_preference_detail->deliveryicon['proxy_url'].'36/26'.$client_preference_detail->deliveryicon['image_path'] : asset('images/al_custom3.png')}}" alt=""></span>
                        {{$NomenclatureName}}
                    </a>
                </li>
                
                @endif
            @endforeach 
            <!-- @if($client_preference_detail->delivery_check==1) @php $Delivery=getNomenclatureName('Delivery', true); $Delivery=($Delivery==='Delivery') ? __('Delivery') : $Delivery; @endphp
                <li class="col navigation-tab-item text-center" role="presentation">
                    <a class="nav-link al_delivery {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}" id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab" aria-controls="profile" aria-selected="false">
                        <span><img src="{{asset('images/al_custom3.png')}}" alt=""></span>
                        {{$Delivery}}
                    </a>
                </li>
                @endif @if($client_preference_detail->dinein_check==1) @php $Dine_In=getNomenclatureName('Dine-In', true); $Dine_In=($Dine_In==='Dine-In') ? __('Dine-In') : $Dine_In; @endphp
                <li class="col navigation-tab-item text-center " role="presentation">
                    <a class="nav-link al_dinein {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}" id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab" aria-controls="dinein_tab" aria-selected="false">
                        <span><img src="{{asset('images/al_custom1.png')}}" alt=""></span>
                        {{$Dine_In}}
                    </a>
                </li>
                @endif @if($client_preference_detail->takeaway_check==1)
                <li class="col navigation-tab-item  text-center" role="presentation">
                    @php $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway') ? __('Takeaway') : $Takeaway; @endphp
                    <a class="nav-link al_takeway {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}" id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab" aria-controls="takeaway_tab" aria-selected="false">
                        <span><img src="{{asset('images/al_custom2.png')}}" alt=""></span>
                        {{$Takeaway}}
                    </a>
                </li>
                @endif -->
            </ul>
            @endif

        </div>


    </article>


    <!-- no-store-wrapper start -->
    <section class="no-store-wrapper mb-3 d-none" >
        <div class="container"> @if(count($for_no_product_found_html)) @foreach($for_no_product_found_html as $key => $homePageLabel) @include('frontend.included_files.dynamic_page') @endforeach @else
            <div class="row">
                <div class="col-12 text-center"> <img class="no-store-image mt-2 mb-2 blur-up lazyload" data-src="{{getImageUrl(asset('images/no-stores.svg'),'250/250')}}" style="max-height: 250px;"> </div>
            </div>
            <div class="row">
                <div class="col-12 text-center mt-2">
                    <h4>{{__('There are no stores available in your area currently.')}}</h4> </div>
            </div> @endif </div>
    </section><!-- no-store-wrapper end -->
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
                    <% if(vendor.vendorRating > 0){%> <span class="rating-number"><%=vendor.vendorRating %> </span>
                    <%}%> @endif @endif
                </a>
            </div>
            <% }); %>
    </script><!-- vendors_template end -->

    <!-- banner_template start -->
    <script type="text/template" id="banner_template" >
        <% _.each(brands, function(brand, k){%>
            <div  >
                <a class="brand-box d-block black-box" href="<%=brand.redirect_url %>">
                    <div class="brand-ing"> <img class="blur-up lazyload" data-src="<%=brand.image.image_fit %>260/260<%=brand.image.image_path %>" alt="" title=""> </div>
                    <h6><%=brand.translation_title %></h6> </a>
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
                    <div class="img-outer-box position-relative"> <img class="blur-up lazyload" data-src="<%=product.image_url %>" alt="" title="">
                        <div class="pref-timing"> </div>
                    </div>
                    <div class="media-body align-self-start">
                        <div class="inner_spacing px-0">
                            <div class="product-description">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="card_title ellips"><%=product.title %></h6> @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
                                    <% if(product.averageRating > 0){%> <span class="rating-number"><%=product.averageRating %></span>
                                        <%}%> @endif @endif </div>
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
                            <span class="rating-number"><%=vendor.vendorRating %> </span>
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
                                                        <% if(vendor.dispatch_traking_url){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/order-icon.svg')}}" alt="" title=""> <a href="{{route('front.booking.details')}}/<%=order.order_number %>" target="_blank">{{__('Details')}}</a>
                                                            <%}%>
                                                                <% if(vendor.dineInTable){%>
                                                                    <li>
                                                                        <h5 class="mb-1">{{__('Dine-in')}}</h5>
                                                                        <h6 class="m-0"><%=vendor.dineInTableName %></h6>
                                                                        <h6 class="m-0">Category : <%=vendor.dineInTableCategory %></h6>
                                                                        <h6 class="m-0">Capacity : <%=vendor.dineInTableCapacity %></h6> </li>
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
    <section class="section-b-space ratio_asos d-none pt-0 mt-0 pb-0" id="our_vendor_main_div" >
        <div class="vendors"> @foreach($homePageLabels as $key => $homePageLabel) @if($homePageLabel->slug == 'pickup_delivery') @if(isset($homePageLabel->pickupCategories) && count($homePageLabel->pickupCategories)) @include('frontend.booking.cabbooking-single-module') @endif @elseif($homePageLabel->slug == 'dynamic_page') @include('frontend.included_files.dynamic_page') @elseif($homePageLabel->slug == 'brands')
            <section class="popular-brands left-shape_ position-relative">
                <div class="container "  >
                    <div class="al_top_heading col-md-12">
                        <div class="row d-flex justify-content-between">
                            <h2 class="h2-heading text-capitalize">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('brands', true)}}</h2>
                            {{-- <a class="" href="">See All</a> --}}
                        </div>
                    </div>
                    <div class="row ">
                        <div class=" col-12 al_custom_brand p-0">
                        <div class=" brand-slider render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
                        </div>
                    </div>
                </div>
            </section> @elseif($homePageLabel->slug == 'vendors')
            <section class="suppliers-section">
                <div class="container mb-0"  >
                        <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                            <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : getNomenclatureName('vendors', true)}}</h2> <a class="" href="{{route('vendor.all')}}">{{__("See all")}}</a> </div>
                        <div class="row">
                            <div class="col-12 p-0">
                            <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
                        </div>
                    </div>
                </div>
            @elseif($homePageLabel->slug == 'cities')
                <section class="suppliers-section render_full_{{$homePageLabel->slug}} d-none ">
                    <div class="container mb-0"  >
                        <div class=" top-heading d-flex justify-content
                        -between align-self-center">
                            <h2 class="h2-heading">{{(!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : 'Cities'}}</h2>
                        </div>
                        <div class="col-12 p-0">
                            <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}">
                            </div>
                        </div>
                    </div>
                </section>
            </section> @elseif($homePageLabel->slug == 'trending_vendors')
            <section class="suppliers-section">
                <div class="container"  >

                        <div class="col-12 top-heading d-flex align-items-center justify-content-between">
                            <h2 class="h2-heading">{{$homePageLabel->slug=='trending_vendors' ? __('Trending')." ".getNomenclatureName('vendors', true) : __($homePageLabel->title)}}</h2> </div>
                        <div class="row">
                            <div class="col-12 p-0">
                            <div class="suppliers-slider-{{$homePageLabel->slug}} product-m render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"> </div>
                        </div>
                    </div>
                </div>
            </section> @else
            <section class="container mb-0 render_full_{{$homePageLabel->slug}} d-none" id="{{$homePageLabel->slug.$key}}"  >

                    <div class="col-md-12 top-heading d-flex align-items-center justify-content-between">
                    <h2 class="h2-heading"> @php if($homePageLabel->slug=='vendors'){echo getNomenclatureName('vendors', true);}elseif($homePageLabel->slug=='recent_orders'){echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __("Your Recent Orders");}else{echo (!empty($homePageLabel->translations->first()->title)) ? $homePageLabel->translations->first()->title : __($homePageLabel->title);}@endphp </h2> @if($homePageLabel->slug=='vendors') <a class="" href="{{route('vendor.all')}}">{{__('View More')}}</a> @endif </div>

                <div class="row">
                    <div class="col-12"> @if($homePageLabel->slug=='vendors' || $homePageLabel->slug=='trending_vendors')
                        <div class="product-5 product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>@elseif($homePageLabel->slug=='recent_orders')
                        <div class="recent-orders product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>@else
                        <div class="product-4-{{$homePageLabel->slug}} product-m  render_{{$homePageLabel->slug}}" id="{{$homePageLabel->slug.$key}}"></div>@endif </div>
                </div>
            </section> @endif @endforeach </div>
    </section><!-- our_vendor_main_div end -->



    <!-- age-restriction star -->
    <div class="modal age-restriction fade" id="age_restriction" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center"> <img style="height: 150px;" class="blur-up lazyload" data-src="{{getImageUrl(asset('assets/images/age-img.svg'),'150/150')}}" alt="" title="">
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
    </div><!-- age-restriction end -->








</section>
@endsection

<!-- footer code in layouts.store/footercontent-template-two -->

@section('script')
<script src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script src="{{asset('front-assets/js/fly-cart.js')}}"></script>
<script>
    $('.al_more_see_more').on('click',function(){
        $('.al_main_category').removeClass('d-none');
        $(this).addClass('al_more_see_less');
        $(this).removeClass('al_more_see_more');
        $(this).html('See Less');
    });
</script>
@endsection
