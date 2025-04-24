@extends('layouts.store', ['title' => __('Login')])
@php
$clientData = \App\Models\Client::select('id', 'logo')->where('id', '>', 0)->first();
$urlImg = @$clientData->logo['image_fit'].'150/60'.@$clientData->logo['image_path'];
$sign_image = (!empty(Session::get('preferences')) ? Session::get('preferences')->signup_image:'');
$sign_image_url = @$sign_image['image_fit'].'1920/1080'.@$sign_image['image_path'];
@endphp

@section('css-links')
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
@endsection
@section('css')
<style type="text/css">

</style>
@endsection

@section('content')
@php
$getAdditionalPreference = getAdditionalPreference(['is_phone_signup']);
@endphp
<article class="bgFourPage"><img class="LoginAreaBG abc" alt="" src="{{$sign_image_url}}"></article>
<section class="wrapper-main container-fluid">
    <article class="BGcenter">
        <div class="container">
            <div class=" col-xl-8 offset-xl-2 py-3" id="login-section">
                <img class="LoginAreaBG" alt="" src="{{$sign_image_url}}">
                <div class="row d-flex align-items-center h-100">
                    <div class="col-sm-6">
                        <div class="LoginLogoBG">
                            <img class="LoginLogo" style="height:80px" alt="" src="{{$urlImg}}">
                        </div>
                    </div>
                    <div class="col-sm-6 px-xl-5 px-3 LogoInArea">
                        <h3 class="mb-2 text-center">{{ __('Login') }}</h3>
                        <form id="login-form-new" class="arabic-language" action="" class="pl-2">
                            @csrf
                                <input type="hidden" name="device_type" value="web">
                                <input type="hidden" name="device_token" value="web">
                                <input type="hidden" id="dialCode" name="dialCode" value="{{ old('dialCode') ? old('dialCode') : Session::get('default_country_phonecode','1') }}">
                                <input type="hidden" id="countryData" name="countryData" value="{{ strtolower(Session::get('default_country_code','US')) }}">

                                <div class="login-with-username">
                                    <div class="form-group">
                                        <label for="">{{ ($getAdditionalPreference['is_phone_signup'] == 1) ?  __('Phone Number'):  __('Email or Phone Number') }}</label>
                                        <input type="text" class="form-control" id="username" placeholder="{{ ($getAdditionalPreference['is_phone_signup'] == 1) ?  __('Phone Number'):  __('Email or Phone Number') }}" required="" name="username" value="{{ old('username')}}">
                                    </div>
                                    <div class="form-group mb-3 {{ ($getAdditionalPreference['is_phone_signup'] == 1) ? 'd-none': '' }}" id="password-wrapper" style="position:relative">
                                        <label for="">Password</label>
                                        <input id="password-field" type="password" class="form-control pr-3" name="password" placeholder="{{ __('Password') }}">
                                        <span toggle="#password-field" class="fa fa-eye-slash toggle-password" aria-hidden="true"></span>
                                        <a class="font-14 pull-right" href="javascript:void(0)" id="send_password_reset_link" style="right:10px;">{{__('Forgot Password?')}}</a>
                                    </div>
                                    <div class="form-group m-0 alErrorSuccessMsg">
                                        <span id="error-msg" class="font-14 text-danger" style="display:none"></span>
                                        <span id="success-msg" class="font-14 text-success" style="display:none"></span>
                                    </div>
                                    <div class="form-group mt-4 text-center">
                                        <button class="btn btn-solid w-100 login_continue_btn my-2" type="submit">{{__('Continue')}}</button>
                                        @if($getAdditionalPreference['is_phone_signup'] != 1) <span class="registerLink">Don't Have An Account? <a href="{{route('customer.register')}}">Register</a> </span>@endif
                                    </div>
                                </div>
                        </form>

                        <div class="ALdivider_line"><span>Or Continue With</span></div>

                        @if(session('preferences'))
                        @if(@session('preferences')->fb_login == 1 || @session('preferences')->twitter_login == 1 || @session('preferences')->google_login == 1 || @session('preferences')->apple_login == 1)
                        <ul class="social-links text-center">
                            @if(@session('preferences')->google_login == 1)
                            <li>
                                <a href="{{url('auth/google')}}"><img src="{{asset('front-assets/images/google.svg')}}" alt=""></a>
                            </li>
                            @endif
                            @if(@session('preferences')->fb_login == 1)
                            <li>
                                <a href="{{url('auth/facebook')}}"><img src="{{asset('front-assets/images/facebook.svg')}}" alt=""></a>
                            </li>
                            @endif
                            @if(@session('preferences')->twitter_login)
                            <li>
                                <a href="{{url('auth/twitter')}}"><img src="{{asset('front-assets/images/twitter.svg')}}" alt=""></a>
                            </li>
                            @endif
                            @if(@session('preferences')->apple_login == 1)
                            <li>
                                <a href="javascript::void(0);"><img src="{{asset('front-assets/images/apple.svg')}}"></a>
                            </li>
                            @endif
                        </ul>

                        @endif
                        @endif
                        <div class="row mt-3 arabic-language">
                            <div class="offset-xl-2 col-xl-8 text-left">
                                {{-- <form name="login" id="login" action="{{route('customer.loginData')}}"  class="px-lg-4" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label for="">{{ __('Email') }}</label>
                                        <input type="email" class="form-control @if(isset($errors) && $errors->has('email')) is-invalid @endif" aria-describedby="" placeholder="{{ __('Email') }}" value="{{ old('email')}}" name="email">
                                        <input type="hidden" value="" id="access_token" name="access_token">
                                        @if($errors->first('email'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                        @if(\Session::has('err_email'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{!! \Session::get('err_email') !!}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="">{{ __('Password') }}</label>
                                        <input type="password" class="form-control @if(isset($errors) && $errors->has('password')) is-invalid @endif" name="password" placeholder="{{ __('Password') }}">
                                        @if($errors->first('password') || \Session::has('Error'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                        @if(\Session::has('err_password'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{!! \Session::get('err_password') !!}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <input type="hidden" name="device_type" value="web">
                                            <input type="hidden" name="device_token" value="web">
                                            <button type="submit" class="btn btn-solid submitLogin">{{ __('Login') }}</button>
                                        </div>
                                        <div class="col-md-6 text-md-right">
                                            <a class="forgot_btn" href="{{url('user/forgotPassword')}}">{{ __('Forgot Password?') }}</a>
                                        </div>
                                    </div>
                                </form> --}}



                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center" style="display:none">
                        <h3 class="mb-md-3 mb-2">{{ __('New Customer') }}</h3>
                        <div class="create_box">
                            {{-- <h6>{{ __('Create An Account') }}</h6> --}}
                            {{-- <p>{{ __('Sign up for a free account at our store. Registration is quick and easy. It allows you to be able to order from our shop. To start shopping click register.') }}</p> --}}
                            <h6>{{ __('Create a free account and join us!') }}</h6>
                            <a href="{{route('customer.register')}}" class="btn btn-solid mt-2">{{ __('Create An Account') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center" id="verify-phone-section" style="display:none">
                <div class="verify-login-code">
                    <form id="verify-otp-form" class="px-lg-4" action="">
                    <h3 class="mb-2 text-center">{{ __('Verify OTP') }}</h3>
                    <div method="get" class="digit-group otp_inputs d-flex justify-content-between" data-group-name="digits" data-autosubmit="false" autocomplete="off">
                        <input class="form-control" type="text" id="digit-1" name="digit-1" data-next="digit-2" onkeypress="return isNumberKey(event)"/>
                        <input class="form-control" type="text" id="digit-2" name="digit-2" data-next="digit-3" data-previous="digit-1" onkeypress="return isNumberKey(event)"/>
                        <input class="form-control" type="text" id="digit-3" name="digit-3" data-next="digit-4" data-previous="digit-2" onkeypress="return isNumberKey(event)"/>
                        <input class="form-control" type="text" id="digit-4" name="digit-4" data-next="digit-5" data-previous="digit-3" onkeypress="return isNumberKey(event)"/>
                        <input class="form-control" type="text" id="digit-5" name="digit-5" data-next="digit-6" data-previous="digit-4" onkeypress="return isNumberKey(event)"/>
                        <input class="form-control" type="text" id="digit-6" name="digit-6" data-next="digit-7" data-previous="digit-5" onkeypress="return isNumberKey(event)"/>
                    </div>
                    <span class="invalid_phone_otp_error invalid-feedback2 w-100 d-block text-center text-danger"></span>
                    <span id="phone_otp_success_msg" class="font-14 text-success text-center w-100 d-block" style="display:none"></span>
                    <div class="row text-center mt-2">
                        <div class="col-12 resend_txt">
                            <a href="javascript:void(0)" class="mb-2 back-login font-12">
                                Change Number
                            </a>
                            <p class="mb-1">{{__('If you didn’t receive a code?')}}</p>
                            <a class="verifyPhone" href="javascript:void(0)"><u>{{__('RESEND')}}</u></a>
                        </div>
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-solid" id="verify_phone_token">{{__('VERIFY')}}</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            </form>
        </div>
        <a class="alBtnGuestLink" href="{{route('userHome')}}">Skip As a Guest</a>

    </article>
</section>


@endsection
@section('script')
{{-- <script src="https://www.gstatic.com/firebasejs/5.5.9/firebase.js"></script> --}}
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script>
    // jQuery(window.document).ready(function () {
    //     jQuery("body").addClass("login_body");
    // });
    // jQuery(document).ready(function($) {
    //     setTimeout(function(){
    //         var footer_height = $('.footer-light').height();
    //         console.log(footer_height);
    //         $('article#content-wrap').css('padding-bottom',footer_height);
    //     }, 500);
    // });
    var login_via_username_url = "{{route('customer.loginViaUsername')}}";
    var forgot_password_url = "{{route('customer.forgotPass')}}";
    // var firebaseConfig = {
    //     apiKey: "AIzaSyBppfct1EwlyUSAT9QKbiuo4e6HiMvV4Fs",
    //     authDomain: "royo-apps-1624361718359.firebaseapp.com",
    //     databaseURL: "https://royo-apps-1624361718359-default-rtdb.firebaseio.com",
    //     projectId: "royo-apps-1624361718359",
    //     storageBucket: "royo-apps-1624361718359.appspot.com",
    //     messagingSenderId: "1030919748357",
    //     appId: "1:1030919748357:web:9c29df0aca70b4f508156c",
    //     measurementId: "G-EFBPR3ZDKE"
    // };
    // firebase.initializeApp(firebaseConfig);
    // const messaging = firebase.messaging();
    // messaging
    // .requestPermission()
    // .then(function () {
    //     return messaging.getToken()
    // })
    // .then(function(token) {
    //     $("#access_token").val(token);
    //     console.log(token);
    // })
    // .catch(function (err) {
    // });

    $('.email-btn').click(function(){
        $('.login-with-mail').show();
        $('.login-with-username').hide();
    });
    $('.back-login').click(function(){
        $('#login-section').show();
        $('#verify-phone-section').hide();
    });

    var reset = function() {
        var input = document.querySelector("#username"),
        errorMsg = document.querySelector("#error-msg");
        input.classList.remove("is-invalid");
        errorMsg.innerHTML = "";
        errorMsg.style.display = 'none';
        $("#password-wrapper").hide();
        $("#password-wrapper input").removeAttr("required");
        $("#password-wrapper input").val('');
    };

    // here, the index maps to the error code returned from getValidationError - see readme
    var errorMap = ["Invalid phone number", "Invalid country code", "Phone number too short", "Phone number too long", "Invalid phone number"];

    var iti = '';
    var phn_filter = /^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\./0-9]*$/;
    var email_filter = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    $(document).delegate('input[name="password"]', 'input', function() {
        $(this).parent('#password-wrapper').show();
        $("#error-msg").hide();
    });

    $(document).delegate("#username", "input", function(e){
        var uname = $.trim($(this).val());
        if(phn_filter.test(uname)){
            // get country flags when input is a number
            assignPhoneInput();
            $("#password-wrapper").hide();
            $("#password-wrapper input").removeAttr("required");
            $("#password-wrapper input").val('');
        }else{
            // destroy country flags when input is a string
            if(iti != ''){
                iti.destroy();
                iti = '';
                $(this).css('padding-left', '6px');
            }
        }
        $(this).focus();
        $(this).removeClass("is-invalid");
        $("#error-msg").hide();
    });

    function assignPhoneInput(){
        var input = document.querySelector("#username");
        var country = $('#countryData').val();
        if(iti != ''){
            iti.destroy();
            iti = '';
        }
        iti = intlTelInput(input, {
            initialCountry: country,
            separateDialCode: true,
            hiddenInput: "full_number",
            utilsScript: "{{asset('assets/js/utils.js')}}",
        });
        $("input[name='full_number']").val(iti.getNumber());
    }

    $(document).delegate(".verifyPhone", "click", function(){
        $("#login-form-new").submit();
    });

    $(document).delegate("#login-form-new", "submit", function(e){
    // $(document).delegate(".login_continue_btn, .verifyPhone", "click", function(e){
        e.preventDefault();
        var uname = $.trim($("#username").val());
        var error = 0;
        var phone = $("input[name='full_number']").val();
        if(uname != ''){
            if(phn_filter.test(uname)){
                reset();
                if (!iti.isValidNumber()) {
                    $("#username").addClass("is-invalid");
                    var errorCode = iti.getValidationError();
                    $("#error-msg").html(errorMap[errorCode]);
                    $("#error-msg").show();
                    error = 1;
                }else{
                    $("#username").removeClass("is-invalid");
                    $("#error-msg").hide();
                }
            }
            else{
                if(email_filter.test(uname)){
                    $("#username").removeClass("is-invalid");
                    $("#error-msg").hide();
                    if($("#password-wrapper").is(":visible")){
                        if($("#password-wrapper input").val() == ''){
                            error = 1;
                            $("#error-msg").show();
                            $("#error-msg").html('Password field is required');
                        }
                    }else{
                        error = 1;
                        $("#password-wrapper").show();
                        $("#password-wrapper input").attr("required", true);
                        $("#password-wrapper input").trigger("focus");
                    }
                }else{
                    error = 1;
                    $("#username").addClass("is-invalid");
                    $("#error-msg").show();
                    $("#error-msg").html('Invalid Email or Phone Number');
                }
            }
        }
        else{
            error = 1;
            $("#username").addClass("is-invalid");
            $("#error-msg").show();
            $("#error-msg").html('Email or Phone Number Required');
        }
        if(!error){
            var form_inputs = $("#login-form-new").serializeArray();
            $.each(form_inputs, function(i, input) {
                if(input.name == 'full_number'){
                    input.value = phone;
                }
            });
            $.ajax({
                data: form_inputs,
                type: "POST",
                dataType: 'json',
                url: login_via_username_url,
                success: function (response) {
                    if (response.status == "Success") {
                        var data = response.data;
                        if(data.is_phone != undefined && data.is_phone == 1){
                            $('#login-section').hide();
                            $('#verify-phone-section').show();
                            $('.otp_inputs input').val('');
                            $('#phone_otp_success_msg').html(response.message).show();
                            setTimeout(function(){
                                $('#phone_otp_success_msg').html('').hide();
                            }, 5000);
                        }
                        else if(data.is_email != undefined && data.is_email == 1){
                            window.location.href = response.data.redirect_to;
                        }else{
                            $("#error-msg").html('Something went wrong');
                            $("#error-msg").show();
                        }
                    }
                }, error: function (error) {
                    var response = $.parseJSON(error.responseText);
                    // let error_messages = response.message;

                    if((response.data != null) && (response.data.user_exists != undefined) && (response.data.user_exists == false)){
                        Swal.fire({
                            title: "{{__('User Not Found')}}",
                            text: response.message,
                            icon: 'info',
                            iconColor: '{{getClientPreferenceDetail()->web_color}}',
                            showCancelButton: true,
                            confirmButtonText: 'Signup',
                            confirmButtonColor: '{{getClientPreferenceDetail()->web_color}}'
                        }).then((result) => {
                            if(result.value)
                            {
                                window.location.href = "{{ route('customer.register') }}";
                            }
                        });
                    }
                    else{
                        $("#error-msg").html(response.message);
                        $("#error-msg").show();
                    }
                }
            });
        }
    });

    // $(document).ready(function() {
    //     $("#username").keypress(function(e) {
    //         if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
    //             return false;
    //         }
    //         return true;
    //     });
    // });

    $(document).delegate('.iti__country','click', function() {
        var code = $(this).attr('data-country-code');
        $('#countryData').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#dialCode').val(dial_code);
    });

    // $("#verify_phone_token").click(function(event) {
    $(document).delegate("#verify-otp-form", "submit", function(e){
        e.preventDefault();
        var verifyToken = '';
        $('.digit-group').find('input').each(function() {
            if($(this).val()){
               verifyToken +=  $(this).val();
            }
        });
        var form_inputs = $("#login-form-new").serializeArray();
        form_inputs.push({name : 'verifyToken', value : verifyToken});

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('customer.verifyPhoneLoginOtp') }}",
            data: form_inputs,
            success: function(response) {
                if(response.status == 'Success'){
                    window.location.href = response.data.redirect_to;
                }else{
                    $(".invalid_phone_otp_error").html(response.message);
                    setTimeout(function(){
                		$('.invalid_phone_otp_error').html('').hide();
                	}, 5000);
                }
            },
            error: function(data) {
                $(".invalid_phone_otp_error").html(data.responseJSON.message);
                setTimeout(function(){
                    $('.invalid_phone_otp_error').html('').hide();
                }, 5000);
            },
        });
    });

    $('.digit-group').find('input').each(function() {
        $(this).attr('maxlength', 1);
        $(this).on('keyup', function(e) {
            var parent = $($(this).parent());
            if(e.keyCode === 8 || e.keyCode === 37) {
                var prev = parent.find('input#' + $(this).data('previous'));
                if(prev.length) {
                    $(prev).select();
                }
            } else if((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || (e.keyCode >= 96 && e.keyCode <= 105) || e.keyCode === 39) {
                var next = parent.find('input#' + $(this).data('next'));
                if( (next.length) && ($(this).val() != '') ) {
                    $(next).select();
                } else {
                    if(parent.data('autosubmit')) {
                        parent.submit();
                    }
                }
            }
        });
    });

    $('#send_password_reset_link').click(function(){
        var that = $(this);
        var email = $('#username').val();
        $('.invalid-feedback').html('');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {"email": email},
            url: forgot_password_url,
            success: function(res) {
                if(res.status == "Success"){
                    $('#success-msg').html(res.message).show();
                	setTimeout(function(){
                		$('#success-msg').html('').hide();
                	}, 5000);
                }
            },
            error:function(error){
            	var response = $.parseJSON(error.responseText);
                let error_messages = response.errors;
                $.each(error_messages, function(key, error_message) {
                    $('#error-msg').html(error_message[0]).show();
                });
            }
        });
    });

    function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }
</script>
@endsection
