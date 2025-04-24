@extends('layouts.store', ['title' => 'My Profile'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }

</style>
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
@endsection
@section('content')

<style type="text/css">
    .productVariants .firstChild {
        min-width: 150px;
        text-align: left !important;
        border-radius: 0% !important;
        margin-right: 10px;
        cursor: default;
        border: none !important;
    }

    .product-right .color-variant li,
    .productVariants .otherChild {
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 10px;
        cursor: pointer;
        border: 1px solid #f7f7f7;
        text-align: center;
    }

    .productVariants .otherSize {
        height: auto !important;
        width: auto !important;
        border: none !important;
        border-radius: 0%;
    }

    .product-right .size-box ul li.active {
        background-color: inherit;
    }

    .login-page .theme-card .theme-form input {
        margin-bottom: 5px;
    }
    .errors {
        color: #F00;
        background-color: #FFF;
    }
    .invalid-feedback {
        display: block;
    }
</style>

<section class="section-b-space">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="text-sm-left">
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <span>{!! \Session::get('success') !!}</span>
                        </div>
                    @endif
                    @if ( ($errors) && (count($errors) > 0) )
                        <div class="alert alert-danger">
                            <ul class="m-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row my-md-3 mt-5 pt-4">
            <div class="col-lg-3">
                <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                @include('layouts.store/profile-sidebar')
            </div>
            <div class="col-lg-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2>{{ __('My Profile') }}</h2>
                        </div>
                        <div class="card-box">
                            <div class="row align-items-center">
                                <div class="col-sm-6 d-flex align-items-center">
                                    <div class="file file--upload">
                                        <label>
                                            <span class="update_pic">
                                            <img src="{{$user->image['proxy_url'].'1000/1000'.$user->image['image_path']}}" alt="">
                                            </span>
                                        </label>
                                    </div>
                                    <div class="name_location">
                                        <h5 class="mt-0 mb-1">{{$user->name}}</h5>
                                    </div>
                                </div>
                                <div class="col-sm-6 text-right mt-0 mt-md-0">
                                    <button type="button" class="btn btn-solid openProfileModal">{{ __('Edit Profile') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-box">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="m-0">{{ __('About Me') }}</h6>
                            </div>
                            <div class="text-16">
                                <p class="m-0">{{$user->description}}</p>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="row welcome-msg justify-content-between">
                            <div class="col-12">
                                <h4 class="d-inline-block m-0">
                                    <span>{{ __('Your ') }} {{__(getNomenclatureName('Referral Code'))}}: {{(isset($userRefferal['refferal_code'])) ? $userRefferal['refferal_code'] : ''}}</span>
                                </h4>
                                <sup class="position-relative">
                                    <a class="copy-icon ml-2" id="copy_icon" data-url="{{url('/'.'?ref=')}}{{(isset($userRefferal['refferal_code'])) ? $userRefferal['refferal_code'] : ''}}" style="cursor:pointer;">
                                        <i class="fa fa-copy"></i>
                                    </a>
                                    <p id="copy_message" class="copy-message"></p>
                                </sup>
                            </div>
                        </div>
                        <div class="row mt-1 profile-page">
                            <div class="col-lg-6">
                                <div class="card-box">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="info-text mb-2">
                                                <label class="m-0">{{ __('Name :') }}</label>
                                                <p>{{$user->name}}</p>
                                            </div>

                                            <div class="info-text mb-2">
                                                <label class="m-0">{{ __('Email :') }}</label>
                                                <p>{{$user->email}}</p>
                                            </div>

                                            <div class="info-text mb-2">
                                                <label class="m-0">{{ __('Phone Number :') }}</label>
                                                <p>{{ '+'.$user->dial_code.$user->phone_number}}</p>
                                            </div>

                                            <div class="info-text mb-2">
                                                <form method="post" action="{{ route('user.updateTimezone') }}" id="user_timezone_form">
                                                    @csrf
                                                    <label class="mb-1">{{ __('Time Zone') }}</label>
                                                    {!! $timezone_list !!}
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade edit_profile_modal" id="profile-modal" tabindex="-1" aria-labelledby="profile-modalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="profile-modalLabel">{{ __('Edit Profile') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editProfileForm" method="post" action="{{route('user.updateAccount')}}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" id="editProfileBox">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-solid w-100">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')

<script src="{{asset('assets/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/libs/dropify/dropify.min.js')}}"></script>
<script src="{{asset('assets/js/pages/form-fileuploads.init.js')}}"></script>
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.js"></script>
<script type="text/javascript">
    var ajaxCall = 'ToCancelPrevReq';
    $('.verifyEmail').click(function() {
        verifyUser('email');
    });
    $('.verifyPhone').click(function() {
        verifyUser('phone');
    });
    function verifyUser($type = 'email') {
        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('verifyInformation', Auth::user()->id) }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "type": $type,
            },
            beforeSend: function() {
                if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
            },
            success: function(response) {
                var res = response.result;
            },
            error: function(data) {},
        });
    }

    $(document).ready(function() {
        jQuery.validator.addMethod("alphanumeric", function(value, element) {
                return this.optional(element) || /^[a-zA-Z0-9 ]+$/i.test(value);
            }, "Name should contains alphanumeric data.");
            $("#editProfileForm").validate({
                errorClass: 'errors',
                rules: {
                    name : {
                        required: true,
                        minlength: 3,
                        alphanumeric: true
                    },
                    phone_number: {
                        required: true,
                        number: true,
                        minlength: 7,
                        maxlength: 15,
                        regex: /^[1-9][0-9]*$/
                    },
                    email: {
                        required: true,
                        email: true
                    }
                },
                onfocusout: function(element) {
                    this.element(element); // triggers validation
                },
                onkeyup: function(element, event) {
                    this.element(element); // triggers validation
                },
                messages : {
                    name: {
                        required:"{{ __('Please enter your name')}}",
                        minlength:"{{__('The name must be at least 3 characters.')}}",
                        alphanumeric:"{{ __('Name should contains alphanumeric data')}}"
                    },
                    phone_number: {
                        required: "{{ __('Please enter your phone')}}",
                        number: "{{ __('Please enter a numerical value')}}",
                        minlength:"{{ __('minimum 7 digits allowed')}}",
                        maxlength:"{{ __('maximum 15 digits required')}}"
                    },
                    email: "{{ __('The email should be in the format:')}} abc@domain.tld",
                }
            });

            $("#editProfileForm").submit(function() {
                if($("#phone").hasClass("is-invalid")){
                    $("#phone").focus();
                    return false;
                }
            });
        });


    $(".openProfileModal").click(function (e) {
        e.preventDefault();
        var uri = "{{route('user.editAccount')}}";
        $.ajax({
            type: "get",
            url: uri,
            data: '',
            dataType: 'json',
            success: function (data) {
                $('#editProfileForm #editProfileBox').html(data.html);
                $('#profile-modal').modal('show');
                var input = document.querySelector("#phone");
                window.intlTelInput(input, {
                    separateDialCode: true,
                    hiddenInput: "full_number",
                    utilsScript: "{{asset('assets/js/utils.js')}}",
                    initialCountry: "{{ Session::get('default_country_code','US') }}",
                });
                $('.dropify').dropify();
            },
            error: function (data) {
            }
        });
    });

    $("#timezone").change(function(){
        $("#user_timezone_form").submit();
    });
    $("#copy_icon").click(function(){
        var temp = $("<input>");
        var url = $(this).data('url');
        $("body").append(temp);
        temp.val(url).select();
        document.execCommand("copy");
        temp.remove();
        $("#copy_message").text("{{ __('URL Copied!') }}").show();
        setTimeout(function(){
            $("#copy_message").text('').hide();
        }, 3000);
    });
</script>
<script>
    var loadFile = function(event) {
        var output = document.getElementById('output');
        output.src = URL.createObjectURL(event.target.files[0]);
    };
</script>
<script>
    $(document).ready(function() {
        $("#phone").keypress(function(e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            return true;
        });
    });
    $(document).delegate('.iti__country', 'click', function() {
        var code = $(this).attr('data-country-code');
        $('#countryData').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#dialCode').val(dial_code);
    });
</script>
@endsection
