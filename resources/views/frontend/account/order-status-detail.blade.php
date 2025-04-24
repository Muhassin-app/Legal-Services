@extends('layouts.store', ['title' => 'Order Status'])
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

    /* Order Status Tracker Styles */
    .order-status-tracker {
        position: relative;
        padding: 20px 0;
    }
    
    .status-step {
        display: flex;
        position: relative;
        margin-bottom: 30px;
    }
    
    .status-icon {
        width: 35px;
        height: 35px;
        border-radius: 21%;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        z-index: 2;
        border: 1px solid #ddd;
    }
    
    .status-step.completed .status-icon {
        background-color: #28a745;
        color: white;
        border-color: #28a745;
    }
    
    .status-step.active .status-icon {
        background-color: white;
        border: 2px solid #ff7f00;
        color: #333;
    }
    
    .status-line {
        position: absolute;
        top: 35px;
        left: 17px;
        width: 2px;
        height: calc(100% - 5px);
        background-color: #ddd;
        z-index: 1;
    }
    
    .status-step:last-child .status-line {
        display: none;
    }
    
    .status-content {
        padding-left: 20px;
        flex: 1;
    }
    
    .status-content h4 {
        margin: 0 0 5px;
        font-size: 18px;
    }
    
    .status-description {
        color: #666;
        margin: 5px 0;
        font-size: 14px;
    }
    
    .status-link {
        display: inline-block;
        margin-top: 5px;
        color: #007bff;
        text-decoration: none;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        margin-left: 10px;
    }
    
    .status-badge.completed {
        background-color: #28a745;
        color: white;
        float: right;
    }
    
    .status-badge.review {
        background-color: #ff7f00;
        color: white;
    }

    .doc_review {
        background-color: #ff7f00;
        float: right;
    }
</style>
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
@endsection
@section('content')

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
                            <h2>{{ __('Order Status') }}</h2>
                        </div>
                        <div class="card-box">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <div class="order-status-tracker">
                                        @foreach ($response['data'] as $status)
                                        <div class="status-step {{ $status['status'] == 'Completed' ? 'completed' : 'active' }}">
                                            <div class="status-icon">
                                                @if($status['status'] == 'Completed')
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </div>
                                            <div class="status-line"></div>
                                            <div class="status-content">
                                                <h4>{{$status['title']}}</h4>
                                                @if($status['status'] == 'Completed')
                                                    <span class="status-badge completed">Completed</span>
                                                @else
                                                    <span class="status-badge doc_review">Pending</span>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Business Information Section -->
                                    <div class="business-info-section mt-4 p-3 border rounded">
                                        <span class="status-badge1" style="color: #0f7c30;">ID : #000{{ $response['product'][0]['order_id'] }}</span>
                                        <h4 class="mb-3">{{$response['product'][0]['product_name']}}</h4>
                                        <p class="text-muted">Known for a strong commitment to justice and a client-centered approach.</p>
                                        {{-- <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="info-item mb-2">
                                                    <strong>Business Name:</strong> <span>{{$response['business_name'] ?? 'Not Available'}}</span>
                                                </div>
                                                <div class="info-item mb-2">
                                                    <strong>Contact:</strong> <span>{{$response['contact'] ?? 'Not Available'}}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-item mb-2">
                                                    <strong>Registration Date:</strong> <span>{{$response['registration_date'] ?? 'Not Available'}}</span>
                                                </div>
                                                <div class="info-item mb-2">
                                                    <strong>Status:</strong> <span class="badge badge-success">{{$response['status'] ?? 'Active'}}</span>
                                                </div>
                                            </div>
                                        </div> --}}
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

@endsection
