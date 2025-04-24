<div class="tap-top top-cls">
    <div>
        <i class="fa fa-angle-double-up"></i>
    </div>
</div>
<div class="d-none" id ="nearmap">
</div>
  <div class="spinner-overlay">
    <div class="page-spinner">
        <div class="circle-border">
            <div class="circle-core"></div>
        </div>
    </div>
  </div>
@php
    $mapKey = 'AIzaSyD0edfD0pDgXVYBT65c8qczFdsx9j24PyY';
    $theme = Session::get('preferences');

    if($theme && !empty($theme->map_key)){
        $mapKey = $theme->map_key;
    }

    $webColor = '#ff4c3b';
    \Session::forget('success');
@endphp

<div class="modal age-restriction fade show-subscription-mdl" id="show-subscription-plan-mdl" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content p-2">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
            <div class="modal-body pb-0 text-center">
                <p class="mb-0">{{__('Avail some more offers !')}}</p>
            </div>
            <div class="modal-footer">
                <a href="{{route('user.subscription.plans')}}" class="btn btn-solid w-100">{{__('Subscribe Now')}}</a>
            </div>
        </div>
    </div>
</div>
@if($is_ondemand_multi_pricing ==1)
@include('layouts.store.ondemand_price_selection_model')
@endif
<!-- spinner Start -->

<div class="nb-spinner-main">

    <div class="nb-spinner"></div>

    </div>

    <!-- spinner End -->
@php
$showSubscriptionPlanPopUp = checkShowSubscriptionPlanOnSignup();
$is_map_search_perticular_country = getMapConfigrationPreference();
@endphp
<script>
    var setShowSubscriptionPlan = '';
    var showOndemandPricing = '';
    let is_map_search_perticular_country = '';

    @if($showSubscriptionPlanPopUp == 1)
        setShowSubscriptionPlan = "showed";
    @endif
    var is_ondemand_multi_pricing = '{{ $is_ondemand_multi_pricing }}';
     is_map_search_perticular_country = '{{ $is_map_search_perticular_country }}';
    var ondemand_selected_price = "{{ Session::get('onDemandPricingSelected')?? 'vendor' }}";
</script>
@yield('pre-custom-script')
<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
{{-- <script src="https://unpkg.com/axios/dist/axios.min.js"></script> --}}
<script type="text/javascript" src="{{asset('front-assets/js/axios.min.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/jquery-3.3.1.min.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/jquery.cookie.min.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/jquery-ui.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('assets/js/constants.js')}}"></script>
<script defer type="text/javascript"src="{{asset('front-assets/js/slick.js')}}"></script>

<script defer type="text/javascript" src="{{asset('front-assets/js/popper.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/menu.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/lazysizes.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/bootstrap.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/underscore.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/script.js')}}"></script>
<script src="{{asset('assets/libs/select2/select2.min.js')}}"></script>
@yield('home-page')
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{$mapKey}}&v=3.exp&libraries=places,drawing"></script>
<script type="text/javascript" src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('js/spinner.js')}}"></script>
<script defer type="text/javascript" src="{{asset('js/image_blur.js')}}"></script>

@yield('custom-js')
<script defer type="text/javascript" src="{{asset('js/custom.js')}}"></script>
<script defer type="text/javascript" src="{{asset('js/location.js')}}"></script>
{{--
<!-- All js merged -->
<script type="text/javascript" src="{{asset('front-assets/js/all-min.js')}}" defer></script>
<!-- shift to product detail page -->
<script type="text/javascript" src="{{asset('front-assets/js/jquery.elevatezoom.js')}}"></script>
<!-- duplicate script -->
<script type="text/javascript" src="{{asset('js/sweetalert2.min.js')}}"></script>
<!-- Extra javascript , which is not used -->
<script type="text/javascript" src="{{asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/libs/clockpicker/clockpicker.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/pages/form-pickers.init.js')}}"></script>


--}}

<!-- Waitme loader script -->
<script type="text/javascript" src="{{asset('js/waitMe.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/developer.js')}}"></script>

<script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
@if(isset($set_template)  && $set_template->template_id == 1)
<script defer type="text/javascript" src="{{asset('front-assets/js/custom-template-one.js')}}"></script>
@endif

@yield('js-script')
<script>
    var Alltranslations = {!! \Cache::get('translations') !!};
 </script>

@if (Auth::check() && Session::has('preferences') && !empty(Session::get('preferences')['fcm_api_key']))
<script  type="text/javascript" src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
<script  type="text/javascript" src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>


<script>
    var firebaseCredentials = {!!json_encode(Session::get('preferences')) !!};
    var firebaseConfig = {
        apiKey: firebaseCredentials.fcm_api_key,
        authDomain: firebaseCredentials.fcm_auth_domain,
        projectId: firebaseCredentials.fcm_project_id,
        storageBucket: firebaseCredentials.fcm_storage_bucket,
        messagingSenderId: firebaseCredentials.fcm_messaging_sender_id,
        appId: firebaseCredentials.fcm_app_id,
        measurementId: firebaseCredentials.fcm_measurement_id
    };
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    const messaging = firebase.messaging();

    function initFirebaseMessagingRegistration() {
        messaging.requestPermission().then(function() {
            return messaging.getToken()
        }).then(function(token) {
            $.ajax({
                url: "{{ route('user.save_fcm') }}",
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    fcm_token: token,
                },
                success: function(response) {

                },
            });
                        console.log("token");

            console.log(token);

        }).catch(function(err) {
            console.log(`Token Error :: ${err}`);
        });
    }
    @if(empty(Session::get('current_fcm_token')))
    initFirebaseMessagingRegistration();
    @endif
    messaging.onMessage(async function(payload) {
        console.log(payload);
        if (!("Notification" in window)) {
            console.log("This browser does not support system notifications.");
        } else if (Notification.permission === "granted") {
            if (payload && payload.data && payload.data.type && (payload.data.type == "order_status_change" || payload.data.type == "reminder_notification")) {
                var notificationTitle = payload.notification.title;
                var notificationOptions = {
                    body: payload.notification.body,
                    icon: payload.notification.icon
                };
                var push_notification = new Notification(
                    notificationTitle,
                    notificationOptions
                );
                push_notification.onclick = function(event) {
                    event.preventDefault();
                    window.open(payload.notification.click_action, "_blank");
                    push_notification.close();
                };
            }  else {
                   // alert();
                    var notificationTitle = payload.notification.title;
                    var notificationOptions = {
                        body: payload.notification.body,
                        icon: payload.notification.icon
                    };
                    var push_notification = new Notification(
                        notificationTitle,
                        notificationOptions
                    );
                    push_notification.onclick = function(event) {
                        event.preventDefault();
                        // window.open(payload.notification.click_action, "_blank");
                        // push_notification.close();
                    };
            }
        }
    });
</script>
@endif
<script src="{{asset('assets/libs/moment/moment.min.js')}}"></script>
<script src="{{asset('assets/libs/datetimepicker/daterangepicker.min.js')}}" ></script>
<script src="{{ asset('js/storage/OrderStorage.js') }}"></script>
<script src="{{ asset('assets/js/alert/alert.js') }}"></script>
<script src="{{ asset('assets\js\backend\backend_common.js') }}"></script>
@if((!empty($socket_url)))
<!-- /** socket_accept */ -->
<script src="{{$socket_url}}/socket.io/socket.io.js"></script>

@endif
@if((!empty(Auth::user())))

@endif
<!-- /**socket_accept end */ -->

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-5LPF1QP3Y3"></script>
@if (isset($set_template)  && $set_template->template_id == 6)
<script async src="{{asset('frontend/template_six/homepage/spa_slider_custom.js')}}"></script>
@endif
<script type="text/javascript">
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'G-5LPF1QP3Y3');

@if(isset($analytics['gtag_id']))
    gtag('config', "{{$analytics['gtag_id'] ?? ''}}");
@endif

@if(!isset($_COOKIE['show-subscription-plan']) && ($showSubscriptionPlanPopUp == 1) && (Route::current()->getName() != 'userHome'))
    $(document).ready(function() {
        $("#show-subscription-plan-mdl").modal("show");
    });
@endif
</script>
<!-- End googletagmanager -->
    @if(isset($analytics['fpixel_id']))
    <!-- Meta Pixel Code -->
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', "{{$analytics['fpixel_id']}}");
        fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{$analytics['fpixel_id']}}&ev=PageView&noscript=1"/></noscript>
    <!-- End Meta Pixel Code -->
    @endif

@php
if($showSubscriptionPlanPopUp == 1){
    setcookie('show-subscription-plan','showed',0);
}
@endphp

<script type="text/javascript">
    var currencySymbol = "{{ Session::get('currencySymbol') }}";
    var is_hyperlocal = 0;
    var selected_address = 0;
    var vendor_type = "delivery";
    var currentRouteName = "{{Route::currentRouteName()}}";
    var is_service_product_price_from_dispatch_forOnDemand = 0;
    @if(Session::has('vendorType') && (Session::get('vendorType') != '') )
        vendor_type = "{{Session::get('vendorType')}}";
    @endif
    @php
        $additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch','is_service_price_selection']);
        $getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
        $is_service_product_price_from_dispatch_forOnDemand =$getOnDemandPricingRule['is_price_from_freelancer'] ?? 0;
    @endphp

     is_service_product_price_from_dispatch_forOnDemand ="{{ $is_service_product_price_from_dispatch_forOnDemand  }}";
    var autocomplete_url = "{{ route('autocomplete') }}";
    let stripe_publishable_key = '{{ $stripe_publishable_key }}';
    let stripe_fpx_publishable_key = '{{ $stripe_fpx_publishable_key }}';
    let stripe_ideal_publishable_key = '{{ $stripe_ideal_publishable_key }}';
    let checkout_public_key = '{{ $checkout_public_key }}';
    let yoco_public_key = '{{ $yoco_public_key }}';
    var login_url = "{{ route('customer.login') }}";
    if(currentRouteName == 'indexTemplateOne')
    var home_page_url = "{{ route('indexTemplateOne') }}";
    else
    var home_page_url = "{{ route('userHome') }}";

    var category_page_url = "{{ route('categoryDetail', ':id') }}";
    var home_page_url_template_one = "{{ route('indexTemplateOne') }}";
    let home_page_url2 = home_page_url.concat("/");
    var add_to_whishlist_url = "{{ route('addWishlist') }}";
    var show_cart_url = "{{ route('showCart') }}";
    var home_page_data_url = "{{ route('homePageData') }}";
    var home_page_data_url_new = "{{ route('homePageDataNew') }}";
    var postHomePageDataSingle = "{{ route('postHomePageDataSingle') }}";
    var home_page_banners_url = "{{ route('postHomePageDataBanners') }}";
    var home_page_data_url_category_menu = "{{ route('homePageDataCategoryMenu') }}";
    var client_preferences_url = "{{ route('getClientPreferences') }}";
    var check_isolate_single_vendor_url = "{{ route('checkIsolateSingleVendor') }}";
    let empty_cart_url = "{{route('emptyCartData')}}";
    var cart_details_url = "{{ route('cartDetails') }}";
    var session_vendor_type = "{{Session::get('vendorType')}}";
    var delete_cart_url = "{{ route('emptyCartData') }}";
    var user_checkout_url= "{{ route('user.checkout') }}";
    var cart_product_url= "{{ route('getCartProducts') }}";
    var delete_cart_product_url= "{{ route('deleteCartProduct') }}";
    var change_primary_data_url = "{{ route('changePrimaryData') }}";
    var url1 = "{{ route('config.update') }}";
    var url2 = "{{ route('config.get') }}";
    var razorpay_complete_payment_url = "{{ route('payment.razorpayCompletePurchase') }}";
    var payment_razorpay_url = "{{route('payment.razorpayPurchase')}}";
    var pyment_totalpay_url= "{{ route('make.payment') }}";
    var payment_thawani_url= "{{ route('pay-by-thawanipg') }}";
    var featured_product_language = "{{ __('Featured Product') }}";
    var new_product_language = "{{ __('New Product') }}";
    var on_sale_product_language = "{{ __('On Sale') }}";
    var best_seller_product_language = "{{ __('Best Seller') }}";
    var vendor_language = "{{ __('Vendors') }}";
    var brand_language = "{{ __('Brands') }}";
/////GCash Payment Routes
    var gcash_before_payment = "{{route('payment.gcash.beforePayment')}}";

///////////////Simplify Payment Routes
    var simplify_before_payment = "{{route('payment.simplify.beforePayment')}}";
    var simplify_create_payment = "{{route('payment.simplify.createPayment')}}";

//////////////Square payment Routes
    var square_before_payment = "{{route('payment.square.beforePayment')}}";
    var square_create_payment = "{{route('payment.square.createPayment')}}";

//////////////Braintree payment Routes
    var braintree_before_payment = "{{route('payment.braintree.beforePayment')}}";
    var braintree_create_payment = "{{route('payment.braintree.createPayment')}}";
//////////////UPay payment Routes
    var upay_before_payment = "{{route('payment.upay.beforePayment')}}";
//////////////Conekta payment Routes
    var conekta_before_payment = "{{route('payment.conekta.beforePayment')}}";
//////////////Telr payment Routes
    var telr_before_payment = "{{route('payment.telr.beforePayment')}}";

//////////////Ozow payment Routes
    var ozow_before_payment = "{{route('payment.ozow.beforePayment')}}";
    var ozow_create_payment = "{{route('payment.ozow.createPayment')}}";

/////////////Pagarme Payment Routes
    var pagarme_before_payment = "{{route('payment.pagarme.beforePayment')}}";
    var pagarme_create_payment = "{{route('payment.pagarme.createPayment')}}";

////////////Paytab payment Routes
    var paytab_before_payment = "{{route('payment.paytab.beforePayment')}}";

/////////////Authorize Payment Routes
    var authorize_before_payment = "{{route('payment.authorize.beforePayment')}}";
    var authorize_create_payment = "{{route('payment.authorize.createPayment')}}";
/////////////Pagarme Payment Routes
    var userede_before_payment = "{{route('payment.userede.beforePayment')}}";
    var userede_create_payment = "{{route('payment.userede.createPayment')}}";

    /////////////openpay Payment Routes
    var openpay_before_payment = "{{route('payment.opnepay.beforePayment')}}";
    var opnepay_create_payment = "{{route('payment.opnepay.createPayment')}}";

    var client_primary_currency = "{{ session()->get('client_primary_currency') }}";
    var default_country_code = "{{ session()->get('default_country_code') }}";

// Logged In User Detail
    var logged_in_user_name = "{{Auth::user()->name??''}}";
    var logged_in_user_email = "{{Auth::user()->email??''}}";
    var logged_in_user_phone = "{{Auth::user()->phone_number??''}}";
    var logged_in_user_dial_code = "{{Auth::user()->dial_code??'91'}}";
// Payment Gateway Key Detail
    var razorpay_api_key = "{{getRazorPayApiKey()??''}}";

// Khalti Payment Gateway Key Detail
    var khalti_api_key = "{{getKhaltiPayApiKey()??''}}";

// Client Perference  Detail
    var client_preference_web_color = "{{Session::get('preferences')->web_color ?? ''  }}";
    var client_preference_web_rgb_color = "{{Session::get('preferences')->wb_color_rgb  ?? ''}}";
    var stop_accepting_orders = "{{Session::get('preferences')->stop_order_acceptance_for_users ?? 0}}";

// Client Detail
    var client_company_name = "{{Session::get('clientdata')->company_name}}";
    var client_logo_url = "{{Session::get('clientdata')->logo_image_url}}";
    var digit_count = "{{$client_preference_detail->digit_after_decimal}}";

//////////////Telr payment Routes
    var skipcash = "{{route('payment.skipcash')}}";

// is restricted
    var is_age_restricted ="{{$client_preference_detail->age_restriction}}";
    //user lat long
    // check vendor slot urkl
    var checkSlotOrdersUrl = "{{route('checkSlotOrders')}}";

    var userLatitude = "{{ session()->has('latitude') ? session()->get('latitude') : 0 }}";
    var userLongitude = "{{ session()->has('longitude') ? session()->get('longitude') : 0 }}";

    if(!userLatitude || userLongitude ==0 || userLongitude==''){
        @if(!empty($client_preference_detail->Default_latitude))
            userLatitude = "{{$client_preference_detail->Default_latitude}}";
        @endif
    }
    if(!userLatitude ){
        userLatitude = "30.7333";
    }

    if(!userLongitude || userLongitude ==0 || userLongitude==''){
        @if(!empty($client_preference_detail->Default_longitude))
             userLongitude = "{{$client_preference_detail->Default_longitude}}";
        @endif
    }
    if(!userLongitude ){
        userLongitude = "76.7794";
    }

    @if(Session::has('selectedAddress'))
        selected_address = 1;
    @endif

    @if($client_preference_detail->is_hyperlocal == 1)
        is_hyperlocal = 1;
        var defaultLatitude = "{{$client_preference_detail->Default_latitude}}";
        var defaultLongitude = "{{$client_preference_detail->Default_longitude}}";
        var defaultLocationName = "{{$client_preference_detail->Default_location_name}}";
    @endif

    var NumberFormatHelper = { formatPrice: function(x,format=1){
        if(x){
            if(digit_count)
            {
                x = parseFloat(x).toFixed(digit_count);
            }
            if(format == 1)
            {
                var parts = x.split(".");
                return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ((parts[1] !== undefined) ? "." + parts[1] : "");
            }
        }
        return x;
        }
    };

    var bindLatlng, bindmapProp, bindMap = '';
    function bindLatestCoords(userLatitude, userLongitude){
        bindLatlng = new google.maps.LatLng(userLatitude, userLongitude);
        bindmapProp = {
            center:bindLatlng,
            zoom:13,
            mapTypeId:google.maps.MapTypeId.ROADMAP
        };
        bindMap=new google.maps.Map(document.getElementById("nearmap"), bindmapProp);
    }
    bindLatestCoords(userLatitude, userLongitude);

    // || $set_common_business_type == 'taxi'
    @if($client_preference_detail->hide_nav_bar == 1)
      $('.main-menu').addClass('d-none').removeClass('d-block');
      $('.menu-navigation').addClass('d-none').removeClass('d-block');
    @endif
    $(function() {
        $(".al_toggle-menu").click(function() {
            $(this).toggleClass("active");
            $('.al_menu-drawer').toggleClass("open");
            $('#page-container').toggleClass("al_fixed");
        });
    });
    //jQuery(document).ready(function($) {
    //    setTimeout(function(){
    //        var footer_height = $('.footer-light').height();
    //        console.log(footer_height);
    //        $('article#content-wrap').css('padding-bottom',footer_height);
    //    }, 500);
    //});
    @if(isset($set_template)  && $set_template->template_id ==3)
        function changeImage(image, check) {
            var  icon = $(image).attr('data-icon');
            var  icon_two = $(image).attr('data-icon_two');
            if(check == 1)
            {
                setTimeout(function () {
                    $(image).attr('data-src',icon_two);
                    $(image).attr('src',icon_two);
                },200);
            }else if(check == 0){
                setTimeout(function () {
                    $(image).attr('data-src',icon);
                    $(image).attr('src',icon);
                },200);
            }
        }
    @endif

    if((stop_accepting_orders == 1) && ((window.location.pathname == '/') || (window.location.pathname == '/viewcart'))){
        swal.fire({
            // title: "{{__('Sorry')}}",
            text:"{{__('There is an extremely high demand right now. Please return later!')}}",
            imageUrl: "{{ URL::asset('/images/order_waiting.gif') }}",
            imageWidth: '40%',
            imageHeight: '10%',
            imageAlt: "Image",
            // icon: 'warning',
            showCancelButton: false,
            confirmButtonText: 'OK',
            // timer: 5000
        }).then((result) => {
            return false;
        });
    }
</script>

@yield('script')
@yield('script-bottom-js')
