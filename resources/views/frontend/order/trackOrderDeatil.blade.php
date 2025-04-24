@extends('layouts.store', ['title' => 'Order Detail'])
@section('script')
<script>
    $(function(){
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
    });
    </script>
    @endsection
@section('css')
<style>
ul.timeline-3 {
  list-style-type: none;
  position: relative;
}
iframe section.location_wrapper.py-xl-5.position-relative.d-lg-flex.align-items-lg-center {
    padding: 0 !important;
    min-height: 100% !important;
}
iframe body{margin: 0 !important;}
.dispatcher-section iframe{border: none !important; overflow: hidden !important;}
ul.timeline-3:before {
  content: " ";
  background: #d4d9df;
  display: inline-block;
  position: absolute;
  left:0px;
  width: 2px;
  height: 100%;
  z-index: 1;
  margin-top: 6px;
}
ul.timeline-3 > li {
  margin: 20px 0;
  padding-left: 20px;
}
ul.timeline-3 > li:before {
  content: " ";
  background: white;
  display: inline-block;
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgb(133, 126, 126);
    left: -4px;
    width: 10px;
    height: 10px;
    z-index: 1;
    margin-top: 6px;
}

.track-ordr .order-heading h4 {
    font-size: 22px;
    font-weight: 600;
}
.track-ordr .timeline-3 li a:first-child {
    color: #685e5e;
    font-size: 16px;
}
.track-ordr .timeline-3 li a {
    font-size: 12px;
}
.no_order_found_track .card-body p {
    font-size: 20px;
}

/*  */
ul.timeline-3:before {
    content: " ";
    background: #d4d9df;
    display: inline-block;
    position: absolute;
    left: 0px;
    width: 100%;
    height: 2px;
    z-index: 1;
    margin-top: 6px;
    top: 0;
}
ul.timeline-3 > li {
    margin: 20px 0;
    padding-left: 0;
    padding-right: 10px;
    position: relative;
}
ul.timeline-3 > li:before {
    content: " ";
    background: white;
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgb(133, 126, 126);
    left: 0;
    width: 10px;
    height: 10px;
    z-index: 1;
    margin-top: 0;
    top: -18px;
}
@media(max-width: 767px){
ul.timeline-3 li a{display: none !important;}
ul.timeline-3 li.last-active a{display: block !important;}
ul.timeline-3 > li:before {
    top: -38px;
}
ul.timeline-3 li.last-active::before{top: -18px;}
}
  </style>
@endsection
@section('content')

<section class="section-b-space light-layout">
    <div class="container">
        <div class="row {{ $showPage }}" id="track-section">
            <div class="col-md-12  mb-3 track-ordr">
                <div class="order-heading text-center pt-3">
                    <h4>Order Status</h4>
                </div>
                @if($order)
                    @if(isset($order->orderStatusVendor))
                        <ul class="timeline-3 d-flex align-items-center justify-content-around">
                            @php $count = count($order->orderStatusVendor); $num = 0; @endphp

                            @foreach ($order->orderStatusVendor as $key =>$status)
                            @if($status->order_status_option_id  == 1)
                                    @php
                                        $title = "Order Placed";
                                    @endphp
                            @endif
                            @if($status->order_status_option_id  == 2)
                                    @php
                                        $title = "Order Accepted";
                                    @endphp
                            @endif
                            @if($status->order_status_option_id  == 4)
                                    @php
                                        $title = "Order Processing";
                                    @endphp
                            @endif
                            @if($status->order_status_option_id  == 5)
                                    @php
                                        $title = "Order Out For Delivery";
                                    @endphp
                            @endif
                            @if($status->order_status_option_id  == 6)
                                    @php
                                        $title = "Delivered";
                                    @endphp
                                @endif

                                <li <?php if($num == $count-1){ ?> class="last-active" <?php } ?>>
                                    <a href="#!">{{ $title }}</a>
                                    <a href="#!" class="d-block">{{ convertDateTimeInClientTimeZone($status->updated_at) }}</a>
                                </li>
                                @php  $num++;  @endphp
                            @endforeach
                        </ul>
                    @endif
                  @else
                    <div class="no_order_found_track">
                        <div class="card">
                            <div class="card-body">
                                <p>{{__('Result not found')}}</p>
                            </div>
                        </div>
                    </div>
                  @endif
            </div>
            <div class="col-md-12">
                @if(isset($order->ordervendor))
                <div class="dispatcher-section">
                    @if(!empty($order->ordervendor->dispatch_traking_url))
                        <iframe id="tracking-frm" src="{{ $order->ordervendor->dispatch_traking_url }}" width="100%" height="800"></iframe>
                    @endif
                </div>
            @endif
            </div>
        </div>

        <div class="row justify-content-center {{ $verifyPage }}" id="verify-phone-section">
            <div class="verify-login-code p-3">
                <form id="verify-otp-form" class="px-lg-4" method="post" >
                <h3 class="mb-2 text-center">{{ __('Verify OTP') }}</h3>
                <div class="digit-group otp_inputs d-flex justify-content-between" data-group-name="digits" data-autosubmit="false" autocomplete="off">
                    <input class="form-control" type="text" id="digit-1" name="digit-1" data-next="digit-2" max="9" Min="0" onkeypress="return isNumberKey(event)"/ required>
                    <input class="form-control" type="text" id="digit-2" name="digit-2" data-next="digit-3" max="9" Min="0" data-previous="digit-1" required onkeypress="return isNumberKey(event)"/>
                    <input class="form-control" type="text" id="digit-3" name="digit-3" data-next="digit-4" max="9" Min="0" data-previous="digit-2" required onkeypress="return isNumberKey(event)"/>
                    <input class="form-control" type="text" id="digit-4" name="digit-4" data-next="digit-5" max="9" Min="0" data-previous="digit-3" required onkeypress="return isNumberKey(event)"/>
                    <input class="form-control" type="text" id="digit-5" name="digit-5" data-next="digit-6" max="9" Min="0" data-previous="digit-4" required onkeypress="return isNumberKey(event)"/>
                    <input class="form-control" type="text" id="digit-6" name="digit-6" data-next="digit-7" max="9" Min="0" data-previous="digit-5" required onkeypress="return isNumberKey(event)"/>
                </div>
                <span class="invalid_phone_otp_error invalid-feedback2 w-100 d-block text-center text-danger" id="invalid_phone_otp_error"></span>
                <span id="phone_otp_success_msg" class="font-14 text-success text-center w-100 d-none"></span>
                <div class="row text-center mt-2">
                    <div class="col-12 resend_txt">
                        <p class="mb-1">{{__('If you didn’t receive a code?')}}</p>
                        <a class="verifyPhone resendOtp" id="resendOtp" href="javascript:void(0)"><u>{{__('RESEND')}}</u></a>
                    </div>
                    <div class="col-md-12 mt-3">
                        <button type="button" class="btn btn-solid" id="verify_phone_token">{{__('VERIFY')}}</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</section>
@php
 $url = url('order/track/'.$order->user_id.'/'.$order->order_number.'?verified=1');
@endphp
@endsection
<script src="https://code.jquery.com/jquery-3.6.1.js"></script>
<script>
     function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    $( document ).ready(function() {

        $("#verify_phone_token").click(function(event) {

            $("#invalid_phone_otp_error").empty();
            if($("#digit-1").val() == '' || $("#digit-2").val() == '' || $("#digit-3").val() == '' || $("#digit-4").val() == '' || $("#digit-5").val() == '' || $("#digit-6").val() == ''){
                $("#invalid_phone_otp_error").html('Please enter otp');
            }else{
                var verifyToken = '';
                $('.digit-group').find('input').each(function() {
                    if($(this).val()){
                        verifyToken +=  $(this).val();
                    }
                });


                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ route('track.order.token.verify') }}",
                    data: {'verifyToken':verifyToken,data:'{{ $order->user_id }}',order_number:'{{ $order->order_number }}'},
                    success: function(response) {
                        $("#phone_otp_success_msg").removeClass('d-none').addClass('d-block');
                        $("#phone_otp_success_msg").html(response.success);
                        $(".invalid_phone_otp_error").empty();
                        var redirectUrl = "{{ $url }}";


                       setTimeout(function(){window.location.href = redirectUrl }, 2000);
                    },
                    error: function(data) {
                        $("#phone_otp_success_msg").empty();
                        $("#phone_otp_success_msg").removeClass('d-block').addClass('d-none');
                        $(".invalid_phone_otp_error").html(data.responseJSON.error);
                    },
                });
            }
        });


        /*** Resend Otp***/
        $("#resendOtp").click(function(event) {
            event.preventDefault();
            $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ route('track.order.otp.resend') }}",
                    data: {'order_id':'{{ $order->order_number }}',data:'{{ $order->user_id }}'},
                    success: function(response) {
                        $("#phone_otp_success_msg").removeClass('d-none').addClass('d-block');
                        $("#phone_otp_success_msg").html(response.success);
                        $(".invalid_phone_otp_error").empty();
                    },
                    error: function(data) {
                        $("#phone_otp_success_msg").empty();
                        $("#phone_otp_success_msg").removeClass('d-block').addClass('d-none');
                        $(".invalid_phone_otp_error").html(data.responseJSON.error);
                    },
                });

        });

    });



</script>
