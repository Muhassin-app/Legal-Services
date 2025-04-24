@extends('layouts.store', ['title' => __('Register')])
@php
$clientData = \App\Models\Client::select('id', 'logo')->where('id', '>', 0)->first();
$urlImg = $clientData->logo['image_fit'].'150/60'.$clientData->logo['image_path'];
$sign_image = (!empty(Session::get('preferences')) ? Session::get('preferences')->signup_image:'');
$sign_image_url = $sign_image['image_fit'].'1920/1080'.$sign_image['image_path'];
@endphp
@section('css-links')
<link rel="stylesheet" href="{{ asset('assets/css/intlTelInput.css') }}">
@endsection
@section('css')
<style type="text/css">
.file>label,
.file.upload-new>label {width: 100%;border: 1px solid #ddd;padding: 30px 0;height: 216px;}
.file .update_pic img,
.file.upload-new img {height: 130px;width: auto;}
.update_pic,
.file.upload-new .update_pic {width: 100%;height: auto;margin: auto;text-align: center;border: 0;border-radius: 0;}
.file--upload>label {margin-bottom: 0;}
/* .bgFourPage{background-image : url({{getImageUrl(asset('assets/images/bannerFour.jpg'),'1920/1200')}});}  */

</style>
@endsection
@section('content')
<article class="bgFourPage"><img class="LoginAreaBG" alt="" src="{{$sign_image_url}}"></article>
<section class="wrapper-main pt-lg-3 alSectionTop d-flex align-items-center">
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
                    <div class="col-sm-6 text-center px-xl-5 px-3 LogoInArea">
                        <h3 class="mb-2">{{ __('Register') }}</h3>

                        <div class="row">
                            <div class="col-sm-12 text-left ">
                                <form name="register" id="register" enctype="multipart/form-data" action="{{ route('customer.register') }}"
                                    class="" method="post"> @csrf
                                    <div class="form-group mb-0">
                                        <div class="col-12 p-0">
                                            <label for="" class="m-0">{{ __('Full Name') }}</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                placeholder="{{ __('Full Name') }}" name="name" value="{{ old('name') }}">
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-1 p-0">
                                            <label for="" class="m-0">{{ __('Phone No.') }}</label>
                                            <input type="tel"
                                                class="form-control @error('phone_number') is-invalid @enderror"
                                                id="phone" placeholder="{{ __('Phone No.') }}" name="phone_number"
                                                value="{{ old('full_number') }}">

                                            <input type="hidden" id="dialCode" name="dialCode"
                                                value="{{ old('dialCode') ? old('dialCode') : Session::get('default_country_phonecode', '1') }}">
                                            <input type="hidden" id="countryData" name="countryData"
                                                value="{{ old('countryData') ? old('countryData') : Session::get('default_country_code', 'US') }}">
                                                @error('phone_number')
                                                <span class="invalid-feedback" role="alert" style="display:block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <div class="col-12 mb-1 p-0">
                                            <label for="" class="m-0">{{ __('Email') }}</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                placeholder="{{ __('Email') }}" name="email" value="{{ old('email') }}">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-1 p-0">
                                            <label for="" class="m-0">{{ __('Password') }}</label>
                                            <div class="position-relative">
                                                <input type="password" id="password-field"
                                                    class="form-control @error('password') is-invalid @enderror" id="review"
                                                    placeholder="{{ __('Enter Your Password') }}" name="password">
                                                <!-- <input id="password-field" type="password" class="form-control pr-3" name="password" placeholder="{{ __('Password') }}"> -->
                                                <span toggle="#password-field" class="fa fa-eye-slash toggle-password"
                                                    style="right:20px"></span>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('password') }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" id="alDocumentsSection">

                                        @if (count($user_registration_documents) > 0)
                                            <div class="user-info d-block w-100">
                                                <h2 class="py-1">User Document</h2>
                                            </div>
                                        @endif
                                        <div class="row alDocumentsSection">
                                            @foreach ($user_registration_documents as $vendor_registration_document)
                                                @if (isset($vendor_registration_document->primary->slug) && !empty($vendor_registration_document->primary->slug))
                                                    @if (strtolower($vendor_registration_document->file_type) == 'selector')
                                                    <div class="col-6 mb-1"
                                                        id="{{ $vendor_registration_document->primary->slug ?? '' }}Input">
                                                        <label
                                                            for="" class="m-0">{{ $vendor_registration_document->primary ? $vendor_registration_document->primary->name : '' }}</label>
                                                        <select
                                                            class="form-control {{ !empty($vendor_registration_document->is_required) ? 'required' : '' }}"
                                                            name="{{ $vendor_registration_document->primary->slug }}"
                                                            id="input_file_selector_{{ $vendor_registration_document->id }}">
                                                            <option value="">
                                                                {{ __('Please Select ') .($vendor_registration_document->primary ? $vendor_registration_document->primary->name : '') }}
                                                            </option>
                                                            @foreach ($vendor_registration_document->options as $key => $value)
                                                                <option value="{{ $value->id }}">
                                                                    {{ $value->translation ? $value->translation->name : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <span class="invalid-feedback"
                                                            id="{{ $vendor_registration_document->primary->slug }}_error"><strong></strong></span>
                                                    </div>
                                                    @else
                                                    <div class="col-6 mb-1"
                                                        id="{{ $vendor_registration_document->primary->slug ?? '' }}Input">
                                                        <label class="m-0"
                                                            for="">{{ $vendor_registration_document->primary ? $vendor_registration_document->primary->name : '' }}</label>
                                                        @if (strtolower($vendor_registration_document->file_type) == 'text')
                                                            <input id="input_file_logo_{{ $vendor_registration_document->id }}"
                                                                type="text"
                                                                name="{{ $vendor_registration_document->primary->slug }}"
                                                                class="form-control {{ !empty($vendor_registration_document->is_required) ? 'required' : '' }}">

                                                            @error($vendor_registration_document->primary->slug)
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first($vendor_registration_document->primary->slug) }}</strong>
                                                                </span>
                                                            @enderror
                                                        @else
                                                            <div class="file file--upload">
                                                                <label class="m-0"
                                                                    for="input_file_logo_{{ $vendor_registration_document->id }}">
                                                                    <span class="update_pic pdf-icon">
                                                                        <img src=""
                                                                            id="upload_logo_preview_{{ $vendor_registration_document->id }}">
                                                                    </span>
                                                                    <span class="plus_icon"
                                                                        id="plus_icon_{{ $vendor_registration_document->id }}">
                                                                        <i class="fa fa-plus"></i>
                                                                    </span>
                                                                </label>
                                                                @if (strtolower($vendor_registration_document->file_type) == 'image')
                                                                    <input
                                                                        class="{{ !empty($vendor_registration_document->is_required) ? 'required' : '' }}"
                                                                        id="input_file_logo_{{ $vendor_registration_document->id }}"
                                                                        type="file"
                                                                        name="{{ $vendor_registration_document->primary->slug }}"
                                                                        accept="image/*"
                                                                        data-rel="{{ $vendor_registration_document->id }}">
                                                                @else
                                                                    <input
                                                                        class="{{ !empty($vendor_registration_document->is_required) ? 'required' : '' }}"
                                                                        id="input_file_logo_{{ $vendor_registration_document->id }}"
                                                                        type="file"
                                                                        name="{{ $vendor_registration_document->primary->slug }}"
                                                                        accept=".pdf"
                                                                        data-rel="{{ $vendor_registration_document->id }}">
                                                                @endif

                                                                @error($vendor_registration_document->primary->slug)
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $errors->first($vendor_registration_document->primary->slug) }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-group mb-0 align-items-center">
                                        <div class="col-12 p-0 checkbox-input">
                                            <input type="checkbox" id="html" name="term_and_condition"
                                                class="form-control @error('term_and_condition') is-invalid @enderror">



                                            <label for="html">{{ __('I accept the') }}
                                                <a href="{{ $terms ? route('extrapage', $terms->slug) : '#' }}"
                                                    target="_blank">{{ __('Terms And Conditions') }} </a>
                                                {{ __('and have read the') }}
                                                <a href="{{ $privacy ? route('extrapage', $privacy->slug) : '#' }}"
                                                    target="_blank">
                                                    {{ __('Privacy Policy') }}.
                                                </a>
                                            </label>
                                            @if ($errors->first('term_and_condition'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('term_and_condition') }}</strong>
                                                </span>
                                            @endif



                                        </div>
                                        <div class="col-12 hide position-absolute">
                                            <label for="">Referral Code</label>
                                            <input type="text" class="form-control" id="refferal_code"
                                                placeholder="Refferal Code" name="refferal_code"
                                                value="{{ old('refferal_code', $code ?? '') }}">
                                            @if ($errors->first('refferal_code'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('refferal_code') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="col-12 p-0">
                                            <input type="hidden" name="device_type" value="web">
                                            <input type="hidden" name="device_token" value="web">
                                            <button type="submit" class="btn btn-solid submitLogin w-100">{{ __('Create An Account') }}</button>
                                            <span class="registerLink">Already Have An Account? <a href="{{route('customer.login')}}">Login</a> </span>
                                        </div>
                                    </div>
                                </form>
                                <div class="ALdivider_line"><span>{{ __('Or Continue With') }}</span></div>
                                @if (session('preferences'))
                                @if (session('preferences')->fb_login == 1 || session('preferences')->twitter_login == 1 || session('preferences')->google_login == 1 || session('preferences')->apple_login == 1)
                                <ul class="social-links text-center">
                                    @if (session('preferences')->google_login == 1)
                                    <li>
                                        <a href="{{ url('auth/google') }}"><img src="{{ asset('front-assets/images/google.svg') }}"></a>
                                    </li>
                                    @endif
                                    @if (session('preferences')->fb_login == 1)
                                    <li>
                                        <a href="{{ url('auth/facebook') }}"><img src="{{ asset('front-assets/images/facebook.svg') }}"></a>
                                    </li>
                                    @endif
                                    @if (session('preferences')->twitter_login)
                                    <li>
                                        <a href="{{ url('auth/twitter') }}"><img src="{{ asset('front-assets/images/twitter.svg') }}"></a>
                                    </li>
                                    @endif
                                    @if (session('preferences')->apple_login == 1)
                                    <li>
                                        <a href="javascript::void(0);"><img src="{{ asset('front-assets/images/apple.svg') }}"></a>
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
        </div>
        <a class="alBtnGuestLink" href="{{route('userHome')}}">Skip As a Guest</a>
    </article>

</section>

@endsection
@section('script')
    <script src="{{ asset('assets/js/intlTelInput.js') }}"></script>
    <script src="{{asset('js/phone_number_validation.js')}}"></script>
    <script>
        jQuery(window.document).ready(function () {
            jQuery("body").addClass("register_body");

            $("#register").submit(function() {
                if($("#phone").hasClass("is-invalid")){
                    $("#phone").focus();
                    return false;
                }
            });
        });
        jQuery(document).ready(function($) {
            setTimeout(function(){
                var footer_height = $('.footer-light').height();
                console.log(footer_height);
                $('article#content-wrap').css('padding-bottom',footer_height);
            }, 500);
        });
        var input = document.querySelector("#phone");
        var iti = window.intlTelInput(input, {
            separateDialCode: true,
            hiddenInput: "full_number",
            utilsScript: "{{ asset('assets/js/utils.js') }}",
            initialCountry: "{{ Session::get('default_country_code', 'US') }}",
        });

        phoneNumbervalidation(iti, input);

        $(document).ready(function() {
            $("#phone").keypress(function(e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
                return true;
            });
        });
        $('.iti__country').click(function() {
            var code = $(this).attr('data-country-code');
            $('#countryData').val(code);
            var dial_code = $(this).attr('data-dial-code');
            $('#dialCode').val(dial_code);
        });
        $(document).on('change', '[id^=input_file_logo_]', function(event) {
            var rel = $(this).data('rel');
            // $('#plus_icon_'+rel).hide();
            readURL(this, '#upload_logo_preview_' + rel);
        });

        function getExtension(filename) {
            return filename.split('.').pop().toLowerCase();
        }

        function readURL(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                var extension = getExtension(input.files[0].name);
                reader.onload = function(e) {
                    if (extension == 'pdf') {
                        $(previewId).attr('src', "{{ asset('assets/images/pdf-icon-png-2072.png') }}");
                    } else if (extension == 'csv') {
                        $(previewId).attr('src', text_image);
                    } else if (extension == 'txt') {
                        $(previewId).attr('src', text_image);
                    } else if (extension == 'xls') {
                        $(previewId).attr('src', text_image);
                    } else if (extension == 'xlsx') {
                        $(previewId).attr('src', text_image);
                    } else {
                        $(previewId).attr('src', e.target.result);
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
