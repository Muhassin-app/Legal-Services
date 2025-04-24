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
.al_toggle-menu {width: 50px;height: 50px;display: inline-block;position: relative;top: 10px;z-index: 1000;}
.al_only_mobile_wrapper svg{fill: #FA1C0A;height:20px;}
.al_toggle-menu.active i:nth-child(1) {top: 25px;-webkit-transform: rotateZ(45deg);transform: rotateZ(45deg);}
.al_toggle-menu i:nth-child(1) {top: 16px;}.al_toggle-menu i {margin: 0 auto;right: 0;position: absolute;display: block;height: 2px;background: #777;width: 24px;left: 0;-webkit-transition: all .3s;transition: all .3s;}
</style>
<section class="al_only_mobile_wrapper">
    <article class="al_mobile_third_template_header">
        <div class="container-fluid">
            <div class="row d-md-flex align-items-center justify-content-between al_mobile_third_header">
                @if(isset($preference))
                @if(($preference->is_hyperlocal) && ($preference->is_hyperlocal == 1))
                <div class="location-bar d-flex justify-content-around position-relative p-0 mr-2" href="#edit-address" data-toggle="modal">
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
                        <a class="navbar-brand " style="height:60px;" href="{{ route('userHome') }}"><img alt="" src="{{$urlImg}}"></a>
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
                                    @if(count($languageList) > 1)
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
                                    @endif
                                    @if(count($currencyList) > 1)
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
                                    @endif


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

    <article class="al_mobile_third_template_search_area">
        <div class="container-fluid">
            <div class="row">
                <div class="d-flex justify-content-around position-relative al_mobile_search_thrid_template">
                    <button class="btn"><svg version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 487.95 487.95" style="enable-background:new 0 0 487.95 487.95;"><g><g><path d="M481.8,453l-140-140.1c27.6-33.1,44.2-75.4,44.2-121.6C386,85.9,299.5,0.2,193.1,0.2S0,86,0,191.4s86.5,191.1,192.9,191.1c45.2,0,86.8-15.5,119.8-41.4l140.5,140.5c8.2,8.2,20.4,8.2,28.6,0C490,473.4,490,461.2,481.8,453z M41,191.4c0-82.8,68.2-150.1,151.9-150.1s151.9,67.3,151.9,150.1s-68.2,150.1-151.9,150.1S41,274.1,41,191.4z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></button>
                    @php $searchPlaceholder=getNomenclatureName('Search', true); $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ? __('Search product, vendor, item') : $searchPlaceholder; @endphp
                    <input class="form-control border-0 typeahead" type="search" placeholder="{{$searchPlaceholder}}" id="main_search_box" autocomplete="off">
                    <div class="list-box style-4" style="display:none;" id="search_box_main_div"> </div>
                </div>
            </div>
        </div>
    </article>

    <article class="al_mobile_third_template_banner_area">
        <div class="container-fluid">
            <div id="myCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">

                    @foreach($banners as $key => $banner)
                        @php $url=''; if($banner->link=='category'){if($banner->category !=null){$url=route('categoryDetail', $banner->category->slug);}}else if($banner->link=='vendor'){if($banner->vendor !=null){$url=route('vendorDetail', $banner->vendor->slug);}}@endphp
                        <div class="carousel-item @if($key == 0) active @endif">
                        <a class="banner-img-outer" href="{{$url??'#'}}">
                            <img alt="" title="" class="blur-up lazyload w-100" data-src="{{$banner->image['proxy_url'] . '1370/300' . $banner->image['image_path']}}">
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
                <li class="col navigation-tab-item pr-lg-3" role="presentation"> <a
                class=" nav-link {{($mod_count==1 || (Session::get('vendorType')==$VendorTypesName) || (Session::get('vendorType')=='')) ? 'active' : ''}}"
                id="{{$VendorTypesName}}_tab" VendorType="{{$VendorTypesName}}" data-toggle="tab" href="#{{$VendorTypesName}}_tab" role="tab"
                aria-controls="profile" aria-selected="false">
                        <span><img src="{{$client_preference_detail->deliveryicon ? $client_preference_detail->deliveryicon['proxy_url'].'36/26'.$client_preference_detail->deliveryicon['image_path'] : asset('images/al_custom3.png')}}" alt=""></span>
                        {{$NomenclatureName}}
                    </a>
                </li>
                
                @endif
            @endforeach   
            <!-- @if($client_preference_detail->delivery_check==1) @php $Delivery=getNomenclatureName('Delivery', true); $Delivery=($Delivery==='Delivery') ? __('Delivery') : $Delivery; @endphp
                <li class="col navigation-tab-item pr-lg-3" role="presentation">
                    <a class="nav-link al_delivery {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}" id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab" aria-controls="profile" aria-selected="false">
                        <span><img src="{{asset('images/al_custom3.png')}}" alt=""></span>
                        {{$Delivery}}
                    </a>
                </li>
                @endif @if($client_preference_detail->dinein_check==1) @php $Dine_In=getNomenclatureName('Dine-In', true); $Dine_In=($Dine_In==='Dine-In') ? __('Dine-In') : $Dine_In; @endphp
                <li class="col navigation-tab-item pr-lg-3 " role="presentation">
                    <a class="nav-link al_dinein {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}" id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab" aria-controls="dinein_tab" aria-selected="false">
                        <span><img src="{{asset('images/al_custom1.png')}}" alt=""></span>
                        {{$Dine_In}}
                    </a>
                </li>
                @endif @if($client_preference_detail->takeaway_check==1)
                <li class="col navigation-tab-item  pr-lg-3" role="presentation">
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
</section>