@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Campaign'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<style type="text/css">
    .iti__flag-container li,
    .flag-container li {
        display: block;
    }

    .iti.iti--allow-dropdown,
    .allow-dropdown {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .iti.iti--allow-dropdown .phone,
    .flag-container .phone {
        padding: 17px 0 17px 100px !important;
    }

    .mdi-icons {
        color: #43bee1;
        font-size: 26px;
        vertical-align: middle;
    }
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Campaign") }}</h4>
            </div>
        </div>
        
       
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card-box set-height">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="text-sm-left">
                            @if (\Session::has('success'))
                            <div class="alert alert-success">
                                <span>{!! \Session::get('success') !!}</span>
                            </div>
                            @endif
                            @if (\Session::has('error_delete'))
                            <div class="alert alert-danger">
                                <span>{!! \Session::get('error_delete') !!}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <form id="send_notification_form" method="POST" class="p-3" action="{{ route('send.notification') }}">
                    @csrf
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="form-group" id="customerCheck">
                                <input type="checkbox" name="all_customer" id="all_customers" value="1">All customers
                            </div>
                        </div>
                    </div>
    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="form-group" id="nameInput">
                                <label for="title" class="control-label">Title</label>
                                <input class="form-control" placeholder="Title" name="title" type="text">
                                <span class="invalid-feedback" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6" id="slugInput">
                            <div class="form-group" id="addressInput">
                                <label for="title" class="control-label">Description</label>
                                <!-- <input class="form-control" name="address" type="text"> -->
                                <textarea class="form-control" rows="3" name="description"></textarea>
                                <span class="invalid-feedback" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
    
                    </div>
                    <button type="submit" class="btn btn-info waves-effect waves-light submitAddForm">Send</button>                    
                </form>
                
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var table;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        
        $(document).on("click", ".delete-vendor", function() {
            var destroy_url = $(this).data('destroy_url');
            var id = $(this).data('rel');
            if (confirm('Are you sure?')) {
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: destroy_url,
                    data: {
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "Success") {
                            $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });

</script>
@endsection
@section('script')
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script type="text/javascript">
    var mobile_number = '';
    // $('#add-agent-modal .xyz').val(mobile_number.getSelectedCountryData().dialCode);
    $('#add-agent-modal .xyz').change(function() {
        var phonevalue = $('.xyz').val();
        $("#countryCode").val(mobile_number.getSelectedCountryData().dialCode);
    });

    function phoneInput() {
        console.log('phone working');
        var input = document.querySelector(".xyz");

        var mobile_number_input = document.querySelector(".xyz");
        mobile_number = window.intlTelInput(mobile_number_input, {
            separateDialCode: true,
            hiddenInput: "full_number",
            utilsScript: "{{ asset('telinput/js/utils.js') }}",
        });
    }
    var input = document.querySelector("#phone");
    window.intlTelInput(input, {
        separateDialCode: true,
        hiddenInput: "contact",
        utilsScript: "{{asset('assets/js/utils.js')}}",
        initialCountry: "{{ Session::get('default_country_code','US') }}",
    });
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
    // $('.iti__country').click(function() {
    //     var code = $(this).attr('data-country-code');
    //     document.getElementById('addCountryData').value = code;
    // })
</script>
@include('backend.users.pagescript')
@endsection
