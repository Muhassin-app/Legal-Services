@php
$favicon = asset('assets/images/favicon.png');
$mapKey = '1234';
    $theme = \App\Models\ClientPreference::where(['id' => 1])->first();
    if($theme && !empty($theme->map_key)){
    $mapKey = $theme->map_key;
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @include('layouts.store.car-rental-meta')
    @include('layouts.store.car-rental-head', ["demo" => "creative"])
</head>
@php

$dark_mode = '';
if($client_preference_detail->show_dark_mode == 1){
    $dark_mode = 'dark';
  }else if($client_preference_detail->show_dark_mode == 2){
    if(session()->has('config_theme')){
    $dark_mode = session()->get('config_theme');
  }
}

$left_sidebar = 'layouts.store/top-car-rental-header';

@endphp
    @include('layouts.language')
<body class="{{$dark_mode}}{{ Request::is('category/cabservice') ? 'cab-booking-body' : '' }}" dir="{{session()->get('locale') == 'ar' ? 'rtl' : ''}}">
    <header>
        @include($left_sidebar)
    </header>
    @include('layouts.store.remove_cart_model')
    @yield('content')
    @yield('script')
    @yield('script-bottom-js')
    @include('layouts.store/car-rental-footer')
    <script src="{{ asset('js/car-rental.js') }}"></script>
</body>
</html>
