@extends('layouts.store', ['title' => 'Register'])
@section('css')
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
    .productVariants .firstChild{
        min-width: 150px;
        text-align: left !important;
        border-radius: 0% !important;
        margin-right: 10px;
        cursor: default;
        border: none !important;
    }
    .product-right .color-variant li, .productVariants .otherChild{
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 10px;
        cursor: pointer;
        border: 1px solid #f7f7f7;
        text-align: center;
    }
    .productVariants .otherSize{
        height: auto !important;
        width: auto !important;
        border: none !important;
        border-radius: 0%;
    }
    .product-right .size-box ul li.active {
        background-color: inherit;
    }
    .iti__flag-container li, .flag-container li{
        display: block;
    }
    .iti.iti--allow-dropdown, .allow-dropdown {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    .iti.iti--allow-dropdown .phone, .flag-container .phone {
        padding: 17px 0 17px 100px !important;
    }
    .social-logins{
        text-align: center;
    }
    .social-logins img{
        width: 100px;
        height: 100px;
        border-radius: 100%;
        margin-right: 20px;
    }
    .register-page .theme-card .theme-form input {
        margin-bottom: 5px;
    }
    .invalid-feedback{
        display: block;
    }
</style>
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
@endsection
@section('content')

<section class="register-page section-b-space main-signup-page mt-5 pt-5 pb-0 recet-pw">
    <div class="container">
        <div class="row bg_inner">
                <div class="col-md-6 p-0">
                    <div class="login_img">
                        <img src="{{asset('images/template-8/login-img.png')}}" class="img-fluid">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="alert alert-success" role="alert" style="display:none;"></div>
                        <!-- <div class="card-header">{{__('Reset Password')}}</div> -->
                        <h3 class="mt-3 mb-2 pl-3">{{__('Reset Password')}}</h3>
                            <div class="card-body pt-0 pb-0">
                                <form method="POST" id="reset_password_form">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <div class="form-group row">
                                    <label for="password" class="col-md-12 col-form-label">{{__('Password')}}</label>
                                    <div class="col-md-12">
                                        <input id="password" placeholder="Password" type="password" class="form-control" name="password" autocomplete="new-password" id="password">
                                        <span class="invalid-feedback" role="alert" id="password_err"></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="password-confirm" class="col-md-12 col-form-label">{{__('Confirm Password')}}</label>
                                    <div class="col-md-12">
                                        <input type="password" placeholder="Confirm Password" class="form-control" name="password_confirmation" autocomplete="new-password" id="password_confirmation">
                                        <span class="invalid-feedback" role="alert" id="password_confirmation_err"></span>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                <div class="col-md-12">
                                        <button type="button" class="btn btn-solid w-100 login_continue_btn mt-3" id="reset_password_btn">{{__('Reset Password')}}</button>
                                    </div>
                                </div>
                            </form>
                            @if (session('preferences'))
                        @if (session('preferences')->fb_login == 1 || session('preferences')->twitter_login == 1 || session('preferences')->google_login == 1 || session('preferences')->apple_login == 1)
                            <div class="divider_line mt-3">
                                <span>{{ __('OR') }}</span>
                            </div>    
                            <ul class="social-media-links d-flex align-items-center justify-content-center mb-4 mt-3">
                                @if (session('preferences')->google_login == 1)
                                    <li>
                                        <a href="{{ url('auth/google') }}">
                                            <img src="{{ asset('front-assets/images/google.svg') }}">
                                        </a>
                                    </li>
                                @endif
                                @if (session('preferences')->fb_login == 1)
                                    <li>
                                        <a href="{{ url('auth/facebook') }}">
                                            <img src="{{ asset('front-assets/images/facebook.svg') }}">
                                        </a>
                                    </li>
                                @endif
                                @if (session('preferences')->twitter_login)
                                    <li>
                                        <a href="{{ url('auth/twitter') }}">
                                            <img src="{{ asset('front-assets/images/twitter.svg') }}">
                                        </a>
                                    </li>
                                @endif
                                @if (session('preferences')->apple_login == 1)
                                    <li>
                                        <a href="javascript::void(0);">
                                            <img src="{{ asset('front-assets/images/apple.svg') }}">
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    @endif
                        </div>
                    </div>
                </div>
    </div>
</div>
</section>
@endsection
@section('script')
<script type="text/javascript">
    var login_url = "{{url('user/login')}}";
    var reset_password_url = "{{route('reset-password')}}";
</script>
<script src="{{asset('js/forgot_password.js')}}"></script>
@endsection