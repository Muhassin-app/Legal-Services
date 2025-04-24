<!-- <footer class="site_footer">
    <div class="container">
        <div class="footer_row">
            <div class="item">
                <div class="footer_logo">
                    <div class="image">
                        <img src="/yacht-images/footer-logo.png">
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="text">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="" title="">Rent a car</a></li>
                        <li><a href="" title="">Airport Pickup and Drop</a></li>
                    </ul>
                </div>
            </div>
            <div class="item">
                <div class="text">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="" title="">About Us</a></li>
                        <li><a href="" title="">FAQs</a></li>
                        <li><a href="" title="">Privacy Policy</a></li>
                        <li><a href="" title="">Terms & Conditions</a></li>
                    </ul>
                </div>
            </div>
            <div class="item">
                <div class="text">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><a href="" title="">+644 6655 654</a></li>
                        <li><a href="" title="">example@gmail.com</a></li>
                    </ul>
                </div>
            </div>
            <div class="item">
                <div class="text social_icon">
                    <ul class="d-flex">
                        <li><a href="" title=""><i class="fa fa-facebook"></i></a></li>
                        <li><a href="" title=""><i class="fa fa-google"></i></a></li>
                        <li><a href="" title=""><i class="fa fa-apple"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer_bar">
            <ul class="d-flex justify-content-center">
                <li>© Atlantic</li>
                <li>Privacy Policy</li>
                <li>Site Credits</li>
            </ul>
        </div>
    </div>
    <div class="d-none" id ="nearmap">
</footer> -->
 @section('script')
{{-- <script src="{{asset('js/custom.js')}}"></script>
<script src="{{asset('js/location.js')}}"></script>
<script src="{{asset('assets/libs/moment/moment.min.js')}}"></script>
<script src="{{asset('assets/libs/datetimepicker/daterangepicker.min.js')}}" ></script>
<script src="{{ asset('js/storage/OrderStorage.js') }}"></script>
<script src="{{ asset('assets/js/alert/alert.js') }}"></script>
<script src="{{ asset('assets\js\backend\backend_common.js') }}"></script>
<script src="{{asset('front-assets/js/underscore.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/popper.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/menu.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/lazysizes.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/bootstrap.js')}}"></script>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script> --}}
<script type="text/javascript"src="{{asset('front-assets/js/slick.js')}}"></script>
{{-- <script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>
<script type="text/javascript" src="{{asset('front-assets/js/jquery.elevatezoom.js')}}"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{asset('assets/libs/select2/select2.min.js')}}"></script>
<script defer type="text/javascript" src="{{asset('front-assets/js/script.js')}}"></script>
{{-- <script>
    var cart_product_url= "{{ route('getCartProducts') }}";
    var delete_cart_product_url= "{{ route('deleteCartProduct') }}";
    var digit_count = "{{$client_preference_detail->digit_after_decimal}}";
    var show_cart_url = "{{ route('showCart') }}";
    var vendor_type = "delivery";
    var currentRouteName = "{{Route::currentRouteName()}}";
    var is_service_product_price_from_dispatch_forOnDemand = 0;
    var url2 = "{{ route('config.get') }}";
    var featured_products_length = '';
    let is_map_search_perticular_country = '';
    var check_isolate_single_vendor_url = "{{ route('checkIsolateSingleVendor') }}";
    let stripe_publishable_key = '{{ $stripe_publishable_key }}';
    let stripe_fpx_publishable_key = '{{ $stripe_fpx_publishable_key }}';
    let stripe_ideal_publishable_key = '{{ $stripe_ideal_publishable_key }}';
    @if(Session::has('vendorType') && (Session::get('vendorType') != '') )
        vendor_type = "{{Session::get('vendorType')}}";
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
</script> --}}