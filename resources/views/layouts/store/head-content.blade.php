
<link defer type="text/css" rel="stylesheet" media="all" href="{{asset('front-assets/css/icons-style.css')}}">
<link defer type="text/css" href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" id="bs-default-stylesheet" />
<link defer type="text/css" rel="stylesheet" media="all" href="{{asset('front-assets/css/color1-style.css')}}">


@if(@getAdditionalPreference(['enable_pwa'])['enable_pwa'] == 1)
	<!-- PWA  -->
<meta name="theme-color" content="#6777ef"/>
<link rel="apple-touch-icon" href="{{ asset('logo.PNG') }}">


<link rel="manifest" href="{{ url('/manifest')}}">
@endif
@if(Route::currentRouteName() != "userHome")

<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/common/common.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/common/spinner.css')}}">
<link href="{{ asset('assets/libs/datetimepicker/daterangepicker.css') }}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
@endif

@if((isset($set_template)  && $set_template->template_id == 1) || empty($set_template))
<link defer type="text/css" rel="stylesheet" type="text/css"  media="all" href="{{asset('front-assets/css/custom.css')}}">
{{--<link defer type="text/css" rel="stylesheet" href="{{asset('front-assets/css/color1-style.css')}}">--}}
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_one/header/header.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_one/homepage/homepage.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_one/footer/footer.css')}}">

@elseif(isset($set_template)  && $set_template->template_id == 2)

<link defer type="text/css" rel="stylesheet" type="text/css"  media="all" href="{{asset('front-assets/css/custom.css')}}">
{{--<link defer type="text/css" rel="stylesheet" href="{{asset('front-assets/css/color1-style.css')}}">--}}
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_two/header/header.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_two/footer/footer.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_two/homepage/homepage.css')}}">
@elseif(isset($set_template)  && $set_template->template_id == 3)
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_three/header/header.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_three/footer/footer.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_three/homepage/inner_page.css')}}">
	@if(Route::currentRouteName() == "userHome")
	<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_three/homepage/homepage.css')}}">
	@else

	@endif
	@if(Route::currentRouteName() == "homeTest")
	<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_three/homepage/homepage.css')}}">
	@else

	<link defer type="text/css" rel="stylesheet" href="{{asset('assets/css/thiredtemplate.css')}}">
	@endif
@elseif(isset($set_template)  && $set_template->template_id == 4)

<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_four/header/header.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_four/footer/footer.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_four/homepage/homepage.css')}}">
@elseif(isset($set_template)  && $set_template->template_id == 5)
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_five/homepage.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_five/header.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_five/footer.css')}}">

{{-- variables-constant-js --}}
@include('layouts.shared.variables-constant-js')
@elseif(isset($set_template)  && $set_template->template_id == 6)
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_six/css/slick-theme.min.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_six/css/slick.min.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_six/header/header.css')}}">
	@if(Route::currentRouteName() == "userHome" || Route::currentRouteName() == "homeTest")
	<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_six/homepage/homepage.css')}}">
	@else
	<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_six/homepage/inner_page.css')}}">
	@endif
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_six/homepage/style-rtl.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_six/footer/footer.css')}}">

@elseif(isset($set_template)  && $set_template->template_id == 8)
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_eight/header/header.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_eight/footer/footer.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_eight/eighttemplate.css')}}">
	@if(Route::currentRouteName() == "userHome")
	<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_eight/homepage/homepage.css')}}">
	@else
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_eight/homepage/inner_page.css')}}">
	@endif
	@if(Route::currentRouteName() == "homeTest")
	<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_eight/homepage/homepage.css')}}">
	@else

	<link defer type="text/css" rel="stylesheet" href="{{asset('assets/css/thiredtemplate.css')}}">
	@endif
@elseif(isset($set_template)  && $set_template->template_id == 9)
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/common/common.css')}}">
<link defer type="text/css" rel="stylesheet" type="text/css"  media="all" href="{{asset('front-assets/css/custom.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_nine/header/header.css')}}">
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_nine/footer/footer.css')}}">
<!-- p2p css -->
<link rel="stylesheet" href="{{asset('frontend/common/p2p.css')}}">

	@if(Route::currentRouteName() == "userHome" || Route::currentRouteName() == "homeTest")
	<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_nine/homepage/homepage.css')}}">
	@else
	<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/template_nine/homepage/inner_page.css')}}">
	@endif
@endif
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/common/commonall.css')}}">
<!-- rental p2p css -->
<link rel="stylesheet" href="{{asset('frontend/common/rental_p2p.css')}}">

<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
<link href="{{asset('assets/libs/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css" />
<script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>


@yield('css-links')
<style type="text/css">
    body{font-size:16px;position:initial}.site-header{width:100%;top:0;left:0;background:#fff;position:fixed;-webkit-transition:all .5s ease-in-out;-moz-transition:all .5s ease-in-out;-ms-transition:all .5s ease-in-out;-o-transition:all .5s ease-in-out;transition:all .5s ease-in-out;z-index:11}.top-header.site-topbar{background:var(--top-header-color);padding:5px 0;display:list-item}

    .top-header .header-dropdown li.onhover-dropdown{padding:0 15px}.onhover-dropdown .onhover-show-div{display:none}.shimmer_effect{overflow:hidden}.menu-slider.items-center .slick-track{justify-content:space-between}

	body .menu-slider .slick-track{display:flex;justify-content:center;margin:0 auto!important; gap: 20px;}

	.al_body_template_three .menu-slider .slick-slide {
		width: 100% !important;
	}
	@media(max-width:767px){
		body .menu-slider .slick-track {
			justify-content: flex-start;
		}
	}

	.pixelstrap li a,.product-card-box{position:relative;-webkit-transform:scale(.95);transform:scale(.95);-webkit-transition:.3s ease-in-out;transition:.3s ease-in-out}.pixelstrap a,.pixelstrap a:active,.pixelstrap a:hover,.pixelstrap li>a{padding:0 7px 10px;text-align:center;width:100px;white-space:normal}
    :root {
		--theme-deafult: <?= ($client_preference_detail) ? $client_preference_detail->web_color : '#ff4c3b' ?>;
		--top-header-color: <?= ($client_preference_detail) ? $client_preference_detail->site_top_header_color : '#4c4c4c' ?>;
		--dashboard_theme_color: <?= ($client_preference_detail) ? $client_preference_detail->dashboard_theme_color : '#4c4c4c' ?>;
}
    a {color: <?= ($client_preference_detail) ? $client_preference_detail->web_color : '#ff4c3b' ?>;}
	body.al_body_template_nine.p2p-module .top-header.site-topbar.al_custom_head a.sell-btn{
		background: linear-gradient(to right, <?= ($client_preference_detail) ? $client_preference_detail->web_color : '#ff4c3b' ?>, #ffce32, <?= ($client_preference_detail) ? $client_preference_detail->site_top_header_color : '#3a77ff' ?>);
	}
    @if($set_common_business_type == 'taxi')
      .cabbooking-loader {width: 30px;height: 30px;animation: loading 1s infinite ease-out;margin: auto;border-radius: 50%;background-color: red;}
      @keyframes loading {0% {transform: scale(1);}100% {transform: scale(8);opacity: 0;}}
      .site-topbar,.main-menu.d-block{display: none !important;}
      .cab-booking-header img.img-fluid {height: 50px;}
      .cab-booking-header{display: block !important;}
      .container .main-menu .d-block{display: none;}
      @media(max-width: 991px){
        .cab-booking-header img.img-fluid {height: auto !important;}
      }
      @media(max-width:767px){.cab-booking-header a.navbar-brand.mr-0 {margin: 10px auto 0;text-align: center;display: block;}}
    @else
       .cab-booking-header{display: none;}
    @endif
</style>
  @yield('css')
@php
if(@getAdditionalPreference(['is_enable_google_analytics'])['is_enable_google_analytics'] == 1){ 
$header_script = getAdditionalPreference(['header_script'])['header_script'];

@endphp
{!! $header_script !!}

  
 @php } @endphp
