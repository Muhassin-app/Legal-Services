<!DOCTYPE html>
    <html lang="en">

    <head>
        @include('layouts.shared.title-meta', ['title' => $title])
        @include('layouts.shared.head-content', ["demo" => "creative"])

        @yield('css')

        <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />


        <script src="{{asset('assets/js/jquery-3.1.1.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery-ui.min.js')}}" ></script>

        <script src="{{asset('assets/js/vendor.min.js')}}"></script>



       <style type="text/css">
.loader_box {position: fixed;width: 100%;height: 100%;background: #00000075;top: 0;z-index:99999;left: 0;}
.spinner-border{color: <?php echo getClientPreferenceDetail()->web_color; ?> !important; position: absolute;top: 50%;left: 0;right: 0;margin: 0 auto !important;display: block;}
.full-width-area {margin-left: 0;width: 100%}
       </style>

    </head>
    @php
        $classBody1 = 'light';
        $Default_latitude = '30.7187';
        $Default_longitude = '76.8106';
        $theme1 = \App\Models\ClientPreference::where(['id' => 1])->first('theme_admin','Default_latitude','Default_longitude');
        if($theme1 && ($theme1->theme_admin == 'dark' || $theme1->theme_admin == 'Dark')){
            $classBody1 = 'dark';
        }
        if($theme1){
            $Default_latitude = $theme1->Default_latitude ? $theme1->Default_latitude : '30.7187' ;
            $Default_longitude = $theme1->Default_longitude ? $theme1->Default_longitude : '76.8106' ;
        }

        $ll = session()->get('applocale_admin');
        @endphp

    <body class="{{$classBody1}}" @yield('body-extra') @if( isset($ll) && $ll=='ar' ) dir="rtl" @endif>
        <!-- Begin page -->
        <div id="wrapper">
            @include('layouts.shared/topbar')
            
            
            @include('layouts.shared/left-sidebar')
            

        <!-- Start Page Content here -->

        <div class="content-page {{(is_p2p_vendor()) ? '' : ''}}">
            <div class="content">
               <!-- @php
                    $style = "";
                    if(session('preferences.twilio_status') != 'invalid_key'){
                        $style = "display:none;";
                    }
                @endphp -->

                <div class="row displaySettingsError" style="{{$style}}">
                    <div class="col-12">
                        <div class="alert alert-danger excetion_keys" role="alert">
                           <!-- @if(session('preferences.twilio_status') == 'invalid_key')
                            <span><i class="mdi mdi-block-helper mr-2"></i> <strong>Twilio</strong> key is not valid</span> <br/>
                            @endif -->
                        </div>
                    </div>
                </div>
                @yield('content')
            </div>

        @include('layouts.shared/footer')

        </div>

        <!-- End Page content -->
    </div>
<script>
    var Default_latitude  =  {{ $Default_latitude }};
    var Default_longitude =  {{ $Default_longitude }};

    var NumberFormatHelper = { formatPrice: function(x,format=1){
        if(x){
            var digit_count = "{{$client_preference_detail->digit_after_decimal}}";
            if(digit_count)
            {
                x = parseFloat(x).toFixed(digit_count);
            }
            if(format == 1)
            {
                var parts = x.split(".");
                return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ((parts[1] !== undefined) ? "." + parts[1] : "");
                // return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        }
        return x;
        }
    };
</script>
    @include('layouts.shared/right-sidebar')
    @include('layouts.shared/footer-script')

    @yield('script')
    <script src="{{asset('assets/js/app.min.js')}}"></script>
    <script>

        $(".remove-modal-open").click(function (e) {
               $('body').addClass('modal-opensag');
       });
    </script>
    </body>
</html>
