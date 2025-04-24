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
<div class="top-header site-topbar al_custom_head">
    <nav class="navbar navbar-expand-lg p-0 ">
        <div class="container ">
            <div class="row d-flex align-items-center justify-content-between w-100">
                <div class="col-lg-6 p-0 d-md-flex align-items-center justify-content-start"   >
                    <a class="navbar-brand mr-3"  href="{{ route('userHome') }}">
                    <img class="logo-image" style="height:auto;" alt="" src="{{$urlImg}}"></a>
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
                </div>

                <div class="col-lg-6 text-right ml-auto al_z_index p-0"  >
                    <ul class="header-dropdown ml-auto">
                    @if( p2p_module_status() && Session::get('vendorType') == 'p2p' )
                        <li><a href="{{route('posts.index', ['fullPage'=>1])}}" class="sell-btn"><span><i class="fa fa-plus" aria-hidden="true"></i>{{ __('Add Post') }}</span></a></li>
                    @endif
                    @if( $is_ondemand_multi_pricing ==1 )
                        @include('layouts.store.onDemandTopBarli')
                    @endif
                        @if($client_preference_detail->header_quick_link == 1)
                        <li class="onhover-dropdown quick-links quick-links">
                            <a href="javascript:void(0)">
                                <span class="icon-icLang align-middle">
                                    <svg version="1.0"width="18" height="18" fill="none" stroke="none" viewBox="0 0 456.000000 456.000000"
                                        preserveAspectRatio="xMidYMid meet">
                                        <g transform="translate(0.000000,456.000000) scale(0.100000,-0.100000)">
                                        <path d="M3472 4549 c-40 -5 -123 -25 -185 -46 -181 -60 -224 -94 -638 -508
                                        -197 -197 -359 -363 -359 -369 0 -6 49 -61 110 -121 l109 -109 22 20 c13 10
                                        181 177 374 369 327 327 356 353 425 387 119 58 181 73 300 72 115 0 186 -16
                                        279 -64 123 -63 195 -134 265 -261 99 -183 99 -377 0 -578 l-46 -94 -667 -666
                                        -666 -667 -95 -46 c-56 -28 -122 -52 -160 -58 -36 -7 -81 -16 -101 -22 -24 -7
                                        -43 -7 -55 0 -10 5 -44 12 -74 16 -72 10 -169 48 -249 99 l-64 42 -109 -115
                                        c-59 -63 -108 -119 -108 -125 2 -31 197 -147 315 -187 22 -7 42 -16 45 -19 12
                                        -15 179 -39 269 -39 96 0 246 22 280 40 9 5 53 24 99 41 160 60 187 83 892
                                        789 360 360 676 683 702 718 188 252 229 638 101 942 -53 124 -110 204 -220
                                        311 -145 139 -296 213 -503 244 -103 16 -175 17 -288 4z"/>
                                        <path d="M1955 3083 c-44 -9 -82 -19 -85 -22 -3 -4 -34 -18 -70 -30 -91 -33
                                        -145 -61 -219 -116 -35 -26 -360 -343 -720 -704 -753 -752 -749 -748 -816
                                        -972 -68 -225 -54 -475 37 -681 118 -269 364 -470 653 -534 117 -26 343 -24
                                        447 4 244 66 293 101 731 536 198 196 357 360 355 365 -5 16 -209 231 -219
                                        231 -4 0 -168 -160 -363 -355 -198 -197 -379 -370 -408 -388 -200 -128 -438
                                        -139 -637 -31 -92 51 -145 94 -195 160 -94 127 -130 231 -130 384 -1 143 41
                                        275 118 375 17 22 323 333 681 690 l651 651 94 46 c56 28 122 52 160 58 36 6
                                        80 16 98 22 23 7 42 7 60 0 15 -6 49 -13 75 -16 65 -8 168 -50 246 -99 l64
                                        -42 109 115 c59 63 108 119 108 125 -2 31 -197 147 -315 187 -22 7 -42 16 -45
                                        19 -3 3 -42 13 -88 22 -102 21 -268 21 -377 0z"/>
                                        </g>
                                    </svg>
                                </span>
                            </a>
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
                                <span class="icon-icLang align-middle">
                                <svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 456.000000 456.000000" preserveAspectRatio="xMidYMid meet">
<g transform="translate(0.000000,456.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
<path d="M2055 4554 c-110 -12 -303 -50 -408 -80 -841 -242 -1461 -935 -1618
-1809 -21 -114 -24 -161 -24 -385 1 -222 4 -272 24 -385 174 -975 916 -1708
1896 -1872 172 -28 564 -26 740 6 965 172 1693 900 1866 1866 21 114 24 162
24 385 0 223 -3 271 -24 385 -86 484 -304 901 -649 1242 -327 324 -750 541
-1212 623 -99 18 -172 23 -350 25 -124 2 -243 1 -265 -1z m303 -268 c121 -32
247 -139 349 -294 82 -124 172 -310 222 -457 l41 -120 -48 -9 c-61 -11 -1153
-14 -1253 -3 l-77 8 14 47 c53 175 154 398 233 516 66 97 177 215 242 257 55
35 148 68 194 68 17 1 54 -6 83 -13z m-764 -202 c-110 -181 -206 -401 -260
-597 l-16 -59 -71 7 c-127 12 -410 45 -489 56 l-78 11 19 27 c37 52 158 181
231 247 192 173 417 309 650 395 41 15 76 26 78 24 1 -1 -27 -51 -64 -111z
m1431 69 c297 -115 596 -332 798 -578 l58 -70 -33 -7 c-36 -7 -418 -53 -534
-63 l-71 -7 -37 119 c-75 233 -176 453 -272 591 -51 73 -55 72 91 15z m-2165
-942 c129 -16 272 -32 318 -36 70 -6 82 -10 78 -24 -24 -80 -64 -411 -73 -598
l-6 -143 -454 0 -453 0 0 33 c0 54 28 220 55 325 31 125 75 251 126 361 62
133 58 130 121 120 29 -5 159 -22 288 -38z m3240 -65 c58 -123 102 -245 135
-378 27 -105 55 -271 55 -325 l0 -33 -455 0 -455 0 0 78 c0 111 -27 383 -54
542 -13 74 -23 136 -22 136 1 1 78 9 171 18 94 10 258 30 365 45 107 15 199
25 204 23 5 -3 30 -50 56 -106z m-2176 -17 c288 -10 441 -10 718 0 194 7 362
13 373 14 18 2 22 -11 42 -123 21 -124 46 -341 58 -517 l7 -93 -842 0 -841 0
6 98 c14 213 59 571 80 631 3 7 12 11 20 8 8 -4 178 -12 379 -18z m-744 -1057
c0 -141 50 -582 76 -663 4 -14 -8 -18 -78 -24 -91 -7 -522 -60 -606 -74 -63
-10 -59 -13 -121 120 -51 110 -95 236 -126 361 -27 105 -55 271 -55 326 l0 32
455 0 455 0 0 -78z m1935 -19 c-17 -245 -66 -618 -84 -634 -3 -3 -64 -1 -136
4 -169 12 -1062 12 -1230 0 -71 -5 -133 -7 -136 -4 -18 16 -67 389 -84 634
l-6 97 841 0 841 0 -6 -97z m1171 20 c-14 -161 -67 -373 -137 -548 -54 -135
-101 -221 -119 -218 -132 19 -432 56 -560 69 -91 9 -166 17 -167 18 0 0 5 35
13 76 25 130 54 390 61 538 l6 142 455 0 455 0 -7 -77z m-1360 -919 l41 -6
-14 -46 c-52 -175 -152 -397 -233 -518 -66 -98 -208 -241 -275 -276 -27 -14
-78 -32 -111 -39 -54 -11 -70 -10 -128 5 -177 48 -327 209 -474 509 -56 113
-147 356 -137 365 18 18 1205 24 1331 6z m-1571 -141 c73 -230 159 -419 260
-573 60 -91 65 -89 -80 -33 -237 92 -476 248 -667 435 -73 72 -191 209 -185
215 7 6 530 69 603 72 l31 1 38 -117z m2245 81 c118 -14 230 -28 248 -32 l33
-7 -54 -65 c-175 -212 -411 -397 -661 -519 -110 -54 -266 -116 -266 -107 0 2
30 53 66 113 109 180 206 402 260 596 l16 59 71 -7 c40 -3 169 -18 287 -31z"></path>
</g>
</svg>
                                </span>
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
                            <span class="icon-icCurrency align-middle">
                            <svg version="1.0"
 width="18" height="18" viewBox="0 0 456.000000 456.000000"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate(0.000000,456.000000) scale(0.100000,-0.100000)"
fill="#000000" stroke="none">
<path d="M1165 4546 c-174 -39 -382 -122 -490 -195 -25 -17 -49 -31 -52 -31
-9 0 -165 -130 -208 -174 -121 -122 -206 -243 -284 -406 -101 -213 -131 -350
-131 -615 0 -217 8 -269 70 -462 79 -242 256 -493 465 -659 172 -136 392 -240
620 -291 83 -18 453 -22 523 -5 39 9 43 8 37 -6 -13 -34 -25 -250 -19 -357 8
-149 19 -209 66 -354 85 -261 238 -489 446 -661 166 -138 319 -219 532 -283
l135 -40 213 -5 c241 -5 285 1 464 57 130 40 255 97 331 150 25 17 49 31 52
31 10 0 164 127 210 174 202 204 339 461 400 751 21 98 20 429 0 531 -122 589
-544 1015 -1140 1151 -81 18 -452 23 -521 6 l-41 -10 9 46 c14 67 13 414 -2
491 -19 104 -88 306 -135 395 -199 376 -499 622 -905 742 l-125 37 -230 2
c-167 3 -247 0 -290 -10z m480 -286 c146 -32 195 -48 285 -92 301 -148 516
-406 609 -733 58 -204 62 -388 11 -575 -11 -41 -23 -87 -26 -101 -4 -19 -18
-32 -48 -45 -140 -63 -367 -250 -475 -392 -55 -71 -135 -195 -154 -237 -17
-38 -22 -41 -95 -63 -231 -67 -404 -68 -639 -1 -184 53 -359 156 -498 296
-214 213 -335 503 -335 803 0 189 36 351 116 512 196 401 585 645 1029 647 94
1 155 -5 220 -19z m1606 -1680 c327 -37 626 -214 817 -482 102 -145 158 -281
198 -483 26 -127 15 -338 -25 -483 -59 -211 -163 -386 -323 -540 -341 -329
-871 -407 -1300 -192 -232 116 -420 307 -529 537 -97 205 -137 477 -100 673
44 232 134 419 282 590 45 52 112 112 204 182 64 48 222 123 325 153 172 51
295 63 451 45z"/>
<path d="M1365 4028 c-55 -32 -75 -76 -75 -165 l0 -73 -35 0 c-55 0 -171 -45
-220 -86 -56 -46 -110 -119 -131 -175 -10 -28 -15 -79 -15 -142 0 -89 3 -105
30 -163 38 -80 117 -161 198 -200 57 -28 61 -29 278 -34 160 -4 225 -9 240
-19 83 -55 76 -171 -12 -207 -28 -12 -74 -15 -217 -12 -174 3 -183 4 -238 31
-68 32 -88 33 -144 7 -48 -24 -74 -67 -74 -125 0 -71 45 -121 146 -163 40 -17
150 -41 185 -42 4 0 8 -38 11 -85 4 -73 8 -89 29 -111 67 -72 151 -72 219 1
22 23 25 37 29 111 1 46 4 84 5 84 1 0 29 5 61 11 179 33 307 159 335 327 30
181 -53 343 -220 432 l-65 34 -205 4 c-113 1 -216 5 -228 8 -13 3 -37 19 -53
37 -35 38 -39 95 -10 143 26 42 78 54 244 54 157 0 210 -13 267 -64 36 -33 45
-36 99 -36 44 0 65 5 85 21 57 45 73 126 36 186 -34 56 -127 121 -213 148 -43
14 -91 25 -108 25 l-29 0 0 78 c0 59 -5 85 -20 109 -41 68 -118 89 -185 51z"/>
<path d="M3138 2160 c-176 -44 -313 -183 -348 -355 -12 -55 -8 -172 7 -232 5
-22 2 -23 -56 -23 -71 0 -110 -17 -140 -59 -31 -43 -36 -85 -17 -136 24 -60
65 -85 144 -85 l60 0 -19 -83 c-19 -87 -30 -117 -86 -237 -46 -100 -49 -119
-25 -174 16 -36 30 -50 66 -68 l46 -22 92 22 c218 51 406 51 608 0 l85 -21 48
21 c38 17 52 30 67 64 24 53 24 67 4 118 -27 67 -99 96 -319 130 -38 6 -129
11 -201 11 l-132 0 18 62 c10 34 24 88 30 120 l11 57 97 0 c113 0 154 17 178
75 27 66 14 124 -41 175 -24 23 -36 25 -125 28 -113 4 -105 -5 -115 127 -6 71
-4 85 17 124 28 54 75 81 141 81 59 0 103 -18 141 -59 77 -81 217 -29 233 87
11 80 -74 178 -197 228 -72 30 -203 41 -272 24z"/>
<path d="M3275 4403 c-49 -12 -85 -70 -85 -136 0 -37 6 -51 38 -86 36 -42 38
-42 127 -51 133 -14 225 -51 322 -131 113 -92 171 -224 180 -408 l6 -114 -41
33 c-22 18 -43 38 -46 46 -7 19 -81 54 -115 54 -94 0 -163 -79 -146 -168 6
-31 40 -70 206 -238 294 -296 271 -295 557 -11 220 220 233 239 212 315 -6 20
-16 44 -23 53 -32 42 -122 62 -170 38 -12 -6 -50 -39 -84 -72 l-63 -61 0 101
c0 223 -83 440 -227 594 -107 114 -305 221 -445 239 -76 10 -169 11 -203 3z"/>
<path d="M500 1569 c-14 -5 -114 -99 -223 -207 -215 -214 -228 -234 -207 -309
6 -21 16 -45 23 -54 32 -42 122 -62 170 -38 12 6 50 39 85 72 l62 61 0 -101
c0 -287 129 -543 350 -697 139 -97 286 -145 448 -146 105 0 162 50 162 143 0
41 -5 53 -37 88 -37 39 -41 40 -128 49 -97 10 -142 23 -221 63 -181 92 -288
280 -282 496 l3 93 51 -50 c59 -58 101 -82 143 -82 94 0 163 79 146 168 -6 31
-39 70 -203 235 -225 227 -259 248 -342 216z"/>
</g>
</svg>
                            </span>
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
                            <span class="icon-icLang align-middle">
                                <svg version="1.0" width="18" height="18" viewBox="0 0 456.000000 456.000000" preserveAspectRatio="xMidYMid meet">
                                    <g transform="translate(0.000000,456.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
                                    <path d="M1935 4544 c-350 -61 -688 -195 -951 -380 -136 -94 -194 -144 -320
                                    -269 -126 -126 -172 -179 -262 -309 -196 -280 -330 -615 -387 -966 -21 -132
                                    -21 -548 0 -680 57 -351 191 -686 387 -966 90 -130 136 -183 262 -309 353
                                    -352 772 -565 1280 -650 123 -21 549 -21 672 0 228 38 432 101 638 197 251
                                    117 436 248 642 453 126 126 172 179 262 309 196 280 330 615 387 966 21 132
                                    21 548 0 680 -57 351 -191 686 -387 966 -90 130 -136 183 -262 309 -353 352
                                    -772 565 -1280 650 -118 20 -566 19 -681 -1z m529 -269 c103 -8 341 -57 436
                                    -91 41 -14 98 -34 125 -44 89 -31 319 -154 395 -212 219 -165 366 -312 497
                                    -493 67 -94 200 -334 218 -397 4 -13 22 -63 40 -113 32 -85 71 -255 95 -410
                                    14 -90 14 -368 0 -465 -58 -403 -187 -713 -420 -1012 l-44 -57 -36 107 c-134
                                    401 -424 730 -809 916 -52 25 -101 46 -108 46 -25 0 -13 18 36 53 72 52 190
                                    192 238 284 99 185 143 417 113 593 -34 206 -117 380 -247 519 -65 70 -181
                                    171 -197 171 -2 0 -31 16 -63 34 -113 68 -303 116 -453 116 -150 0 -340 -48
                                    -453 -116 -32 -18 -61 -34 -63 -34 -16 0 -132 -101 -197 -171 -173 -186 -269
                                    -449 -253 -694 11 -177 57 -317 157 -485 33 -54 149 -180 202 -218 26 -19 47
                                    -38 47 -43 0 -5 -12 -12 -27 -15 -44 -11 -201 -93 -288 -152 -294 -199 -509
                                    -480 -618 -808 l-33 -102 -44 57 c-232 297 -362 609 -420 1011 -14 97 -14 375
                                    0 465 24 155 63 325 95 410 18 50 36 100 40 113 18 63 151 303 218 397 131
                                    181 278 328 497 493 76 58 306 181 395 212 27 10 84 30 125 44 106 38 354 87
                                    484 96 109 7 162 6 320 -5z m-108 -745 c286 -37 515 -239 584 -515 44 -176 11
                                    -384 -86 -530 -102 -156 -224 -244 -401 -290 -125 -33 -221 -33 -346 0 -97 25
                                    -132 41 -222 101 -220 146 -332 451 -265 719 75 298 329 503 650 523 8 1 47
                                    -3 86 -8z m226 -1689 c209 -61 313 -110 453 -212 100 -73 146 -116 222 -204
                                    147 -171 248 -384 294 -616 l12 -63 -39 -34 c-112 -97 -374 -247 -529 -302
                                    -256 -93 -467 -131 -715 -131 -248 0 -459 38 -715 131 -153 55 -395 192 -523
                                    297 -43 35 -44 37 -37 80 4 25 22 96 40 159 63 215 163 383 325 544 58 58 132
                                    124 165 147 129 90 312 171 460 206 106 25 115 26 325 22 157 -3 208 -8 262
                                    -24z"></path>
                                    </g>
                                </svg>
                            </span>
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
                        <div class="al_new_ipad_view ipad-view cutom_add_wishlist"  >
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
                                  <span class="icon-icLang align-middle">
                                  <svg version="1.0"
                                    width="18" height="18" fill="none" stroke="none" viewBox="0 0 538.000000 456.000000"
                                    preserveAspectRatio="xMidYMid meet">
                                    <g transform="translate(0.000000,456.000000) scale(0.100000,-0.100000)">
                                    <path d="M1215 4545 c-49 -6 -130 -22 -180 -35 -99 -25 -301 -108 -343 -140
                                    -15 -12 -61 -43 -102 -70 -41 -26 -119 -92 -172 -147 -112 -113 -121 -124
                                    -178 -217 -23 -38 -48 -75 -55 -82 -25 -25 -107 -237 -135 -348 -43 -170 -53
                                    -280 -47 -499 6 -214 26 -327 91 -517 52 -153 204 -445 304 -584 26 -36 67
                                    -93 92 -128 131 -182 389 -462 629 -681 178 -163 434 -377 516 -432 28 -18 65
                                    -46 83 -63 18 -16 85 -65 149 -108 64 -44 121 -84 127 -89 21 -20 272 -185
                                    282 -185 5 0 24 -13 42 -29 19 -15 95 -64 170 -107 133 -77 139 -79 202 -79
                                    63 0 69 2 203 80 76 44 147 87 157 97 11 10 48 35 82 55 65 38 239 152 248
                                    163 3 4 60 44 128 90 67 46 137 97 155 113 17 16 49 40 69 53 32 20 136 102
                                    218 171 14 11 57 47 95 79 334 277 807 776 898 949 9 17 28 44 42 60 69 77
                                    249 422 301 575 65 190 85 303 91 517 6 219 -4 329 -47 499 -28 112 -110 323
                                    -136 348 -7 7 -34 49 -60 92 -60 101 -270 313 -364 369 -36 21 -74 46 -85 56
                                    -48 41 -273 126 -420 158 -264 57 -602 33 -880 -62 -90 -31 -218 -86 -225 -96
                                    -3 -5 -37 -24 -76 -44 -87 -44 -181 -107 -301 -204 l-93 -74 -92 74 c-121 97
                                    -215 160 -302 204 -39 20 -73 39 -76 44 -7 10 -128 62 -225 95 -237 83 -536
                                    113 -780 79z m398 -350 c92 -14 246 -53 257 -65 3 -3 32 -16 65 -29 93 -37
                                    214 -99 285 -146 87 -58 285 -218 324 -263 81 -92 197 -94 286 -4 59 60 248
                                    212 330 267 72 48 218 122 295 151 28 10 52 21 55 25 3 4 50 19 105 33 302 77
                                    573 55 808 -65 139 -70 194 -108 276 -189 96 -95 141 -157 201 -280 28 -58 55
                                    -113 60 -122 6 -10 22 -62 36 -115 32 -122 44 -386 24 -510 -14 -88 -48 -223
                                    -59 -238 -5 -5 -21 -44 -36 -85 -31 -84 -149 -323 -170 -344 -7 -7 -35 -46
                                    -61 -87 -83 -130 -262 -351 -414 -513 -187 -199 -603 -562 -814 -710 -31 -22
                                    -66 -49 -78 -60 -38 -36 -420 -294 -536 -363 -42 -25 -79 -48 -82 -52 -3 -4
                                    -22 -17 -42 -30 l-38 -23 -37 23 c-21 13 -40 26 -43 29 -3 3 -23 17 -45 30
                                    -95 56 -238 150 -346 227 -65 46 -120 83 -124 83 -7 0 -78 53 -103 77 -8 7
                                    -44 34 -80 60 -125 88 -362 283 -539 445 -270 244 -538 547 -680 767 -30 46
                                    -60 90 -68 97 -18 18 -135 252 -165 329 -13 33 -27 65 -31 70 -12 17 -47 146
                                    -65 240 -13 71 -15 126 -11 265 7 217 31 320 115 490 112 229 237 357 460 475
                                    216 113 433 148 685 110z"/>
                                    </g>
                                    </svg>
                                  </span>
                                  <span>Wish list</span>
                              </a> </div>
                              @endif @endif
                              <div class="icon-nav d-none d-lg-inline-block ">
                                  <form name="filterData" id="filterData" action="{{route('changePrimaryData')}}"> @csrf <input type="hidden" id="cliLang" name="cliLang" value="{{session('customerLanguage')}}"> <input type="hidden" id="cliCur" name="cliCur" value="{{session('customerCurrency')}}"> </form>
                                  <ul class="d-flex align-items-center m-0 ">

                                      <li class="onhover-div pl-0 shake-effect d-none">
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

                        <li class="onhover-div pl-0 shake-effect">
                                                @if($client_preference_detail) @if($client_preference_detail->cart_enable==1)
                                                <a class="btn btn-solid d-flex align-items-center p-0" href="{{route('showCart')}}">
                                                    <span class="mr-1 icon-icLang align-middle">
                                                    <svg version="1.0"
 width="18" height="18" viewBox="0 0 519.000000 456.000000"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate(0.000000,456.000000) scale(0.100000,-0.100000)">
<path d="M43 4515 l-45 -45 4 -70 c3 -66 6 -73 41 -108 l37 -37 428 -3 427 -3
59 -267 c32 -147 109 -499 171 -782 63 -283 155 -702 204 -930 50 -228 96
-423 101 -432 16 -29 12 -44 -16 -57 -46 -20 -116 -84 -161 -144 -101 -138
-112 -349 -26 -503 33 -59 112 -138 166 -165 94 -49 144 -59 281 -59 l130 0
-69 -34 c-105 -50 -195 -154 -236 -274 -26 -76 -26 -218 0 -294 47 -138 151
-242 288 -289 77 -26 219 -26 295 0 122 42 228 134 279 244 33 69 34 76 34
192 0 115 -1 123 -33 190 -45 95 -134 186 -227 231 l-69 34 944 0 944 0 -69
-34 c-50 -24 -88 -52 -132 -99 -97 -102 -133 -189 -133 -322 0 -163 68 -288
208 -381 80 -54 150 -74 257 -74 174 0 305 73 399 223 58 93 75 269 37 379
-41 120 -131 224 -236 274 l-69 34 114 0 c96 0 120 3 144 19 48 32 66 69 66
136 0 65 -17 102 -61 133 -22 16 -130 17 -1449 22 l-1425 5 -41 23 c-50 29
-74 68 -74 120 0 48 32 98 80 126 35 21 43 21 1456 24 l1421 2 40 34 c31 27
44 48 58 98 11 35 22 65 25 68 4 3 23 66 44 140 21 74 98 347 171 605 349
1229 365 1288 365 1337 0 55 -15 91 -55 128 l-27 25 -1888 3 c-1786 2 -1889 3
-1894 20 -3 9 -28 119 -55 243 -28 124 -55 237 -60 251 -5 14 -27 39 -48 57
l-38 31 -519 0 -519 0 -44 -45z m4777 -880 c0 -3 -29 -108 -64 -233 -36 -125
-103 -364 -151 -532 -47 -168 -90 -314 -95 -325 -6 -11 -54 -175 -108 -365
l-99 -345 -1249 -3 -1249 -2 -114 517 c-62 285 -117 523 -121 528 -4 6 -22 78
-40 160 -17 83 -54 250 -81 372 -27 123 -49 225 -49 228 0 3 770 5 1710 5 941
0 1710 -2 1710 -5z m-2749 -3084 c35 -34 39 -44 39 -87 0 -86 -55 -144 -135
-144 -75 0 -135 59 -135 133 0 82 58 137 144 137 43 0 53 -4 87 -39z m2130 16
c42 -28 59 -60 59 -112 0 -76 -59 -135 -135 -135 -80 0 -135 58 -135 144 0 43
4 53 39 87 35 35 44 39 88 39 36 0 60 -7 84 -23z"/>
</g>
</svg>
                                                    </span>

                                                    <span>{{__('Cart')}}</span>
                                                    <span id="cart_qty_span">{{__('0')}}</span>
                                                </a> @endif @endif
                                                <script type="text/template" id="header_cart_template"> <% _.each(cart_details.products, function(product, key){%> <% _.each(product.vendor_products, function(vendor_product, vp){%> <li id="cart_product_<%=vendor_product.id %>" data-qty="<%=vendor_product.quantity %>"> <a class='media' href='<%=show_cart_url %>'> <% if(vendor_product.pvariant.media_one){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_one.pimage.image.path.image_path %>"> <%}else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%=vendor_product.pvariant.media_second.image.path.image_path %>"> <%}else{%> <img class='mr-2 blur-up lazyload' data-src="<%=vendor_product.image_url %>"> <%}%> <div class='media-body'> <h4><%=vendor_product.product.translation_one ? vendor_product.product.translation_one.title : vendor_product.product.sku %></h4> <h4> <span><%=vendor_product.quantity %> x <%=Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></span> </h4> </div></a> <div class='close-circle'> <a href="javascript::void(0);" data-product="<%=vendor_product.id %>" class='remove-product'> <i class='fa fa-times' aria-hidden='true'></i> </a> </div></li><%}); %> <%}); %> <li><div class='total'><h5>{{__('Subtotal')}}: <span id='totalCart'>{{Session::get('currencySymbol')}}<%=Helper.formatPrice(cart_details.gross_amount) %></span></h5></div></li><li><div class='buttons'><a href="<%=show_cart_url %>" class='view-cart'>{{__('View Cart')}}</a> </script>
                                                <ul class="show-div shopping-cart " id="header_cart_main_ul"></ul>
                                            </li>
                    </ul>
                </div>



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
<div class="al_mobile_menu al_new_mobile_header">
                <div class="d-flex">
                    @if( Session::get('vendorType') == 'p2p' )
                    <li class="add_post pr-2"><a href="{{route('posts.index', ['fullPage'=>1])}}" class="sell-btn">
                        <span>
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            {{ __('Add Post') }}</span>
                        </a></li>
                @endif

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
            </div>
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


