<!DOCTYPE html>
<head>
  <title>{{__('MyCash Payment')}}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- link to the Square web payment SDK library -->
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/font-awesome.min.css')}}">
  <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
  <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/custom.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
  <style>
      .spinner-overlay .page-spinner .circle-border {
          background: linear-gradient(0deg, rgba(0, 0, 0, 0.5) 33%, rgba(255, 255, 255, 1) 100%);
      }
      @keyframes spin {
          from {
              transform:rotate(0deg);
          }
          to {
              transform:rotate(360deg);
          }
      }
      .payment-top-haeder{
          background: #ffffff;
      }
      button {
          background-color: {{getClientPreferenceDetail()->web_color}} ;
      }
  </style>
</head>
<body>
<section class="wrapper-main mb-5 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center offset-lg-0" id="verify_phone_main_div">
                <img src="{{asset('front-assets/images/phone-otp.svg')}}">
                <h3 class="mb-2">{{__('Verify OTP')}}</h3>
                <p>{{__('Enter the code we just sent you on your phone number')}}</p>
                <form id="otp_verification_form">
                    @csrf
                    <div class="row mt-3">
                        <div class="offset-xl-2 col-xl-8 text-left">
                            <div>
                                @forelse($data as $key=>$value)
                                    <input type="hidden" name="{{$key}}" value="{{$value}}">
                                @empty
                                @endforelse
                            </div>
                            <div method="get" class="digit-group otp_inputs d-flex justify-content-between" data-group-name="digits" data-autosubmit="false" autocomplete="off">
                                <input class="form-control" type="text" id="digit-1" name="digit-1" data-next="digit-2" onkeypress="return isNumberKey(event)"/>
                                <input class="form-control" type="text" id="digit-2" name="digit-2" data-next="digit-3" data-previous="digit-1" onkeypress="return isNumberKey(event)"/>
                                <input class="form-control" type="text" id="digit-3" name="digit-3" data-next="digit-4" data-previous="digit-2" onkeypress="return isNumberKey(event)"/>
                                <input class="form-control" type="text" id="digit-4" name="digit-4" data-next="digit-5" data-previous="digit-3" onkeypress="return isNumberKey(event)"/>
                                <input class="form-control" type="text" id="digit-5" name="digit-5" data-next="digit-6" data-previous="digit-4" onkeypress="return isNumberKey(event)"/>
                                <input class="form-control" type="text" id="digit-6" name="digit-6" data-next="digit-7" data-previous="digit-5" onkeypress="return isNumberKey(event)"/>
                            </div>
                            <span class="invalid_phone_otp_error invalid-feedback2 w-100 d-block text-center text-danger"></span>
                            <span class="valid-feedback d-block text-center" role="alert">
                                <strong class="edit_phone_feedback"></strong>
                            </span>
                            <div class="row text-center mt-2">
                                <div class="col-12 resend_txt">
                                    <p class="mb-1">{{__('If you didn’t receive a code?')}}</p>
                                    <div class="phonecountdown text-danger"></div>
                                    <a class="verifyPhone" href="javascript:void(0)"><u>{{__('RESEND')}}</u></a>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <button type="button" class="btn btn-solid btn-primary" id="verify_phone_token">{{__('VERIFY')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script type="text/javascript">
    var order_success_url = "{{ route('order.success', ':id') }}";
    var resend_text = "{{__('RESEND')}}";
    var sending_text = "{{__('SENDING...')}}";

    function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    var ajaxCall = 'ToCancelPrevReq';
    
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
                if(next.length) {
                    $(next).select();
                } else {
                    if(parent.data('autosubmit')) {
                        parent.submit();
                    }
                }
            }
        });
    });
    $("#verify_phone_token").click(function(event) {
        var verifyToken = '';
        $('.digit-group').find('input').each(function() {
            if($(this).val()){
               verifyToken +=  $(this).val();
            }
        });
        var formData = $("#otp_verification_form").serializeArray();
        formData.push({name: 'otp', value: verifyToken});
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('verify.payment.otp.submit', 'mycash') }}",
            data: formData,
            success: function(response) {
                $(".digit-group input").val('');
                if(response.status == 'Success'){
                    window.location.href = response.data;
                }
                else{
                    $(".invalid_phone_otp_error").html(response.message);
                }
            },
            error: function(data) {
                // console.log(data.responseJSON);
                $(".invalid_phone_otp_error").html(data.responseJSON.message);
            },
        });
    });
    
    $('.verifyPhone').click(function() {
        resendOtp();
    });
    function resendOtp($type = 'email') {
        $('.verifyPhone').addClass('disabled').html(sending_text);
        var formData = $("#otp_verification_form").serializeArray();
        formData.push({name: 'resend', value: true});
        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('send.payment.otp', 'mycash') }}",
            data: formData,
            success: function(response) {
                if(response.status == 'Success'){
                    $('.verifyPhone').removeClass('disabled').html(resend_text);
                    $('.otp_inputs input').val('');
                    $('.verifyPhone').css('display','none');
                    $('.phonecountdown, .invalid_phone_otp_error').html('');
                    $('.phonecountdown').css('display','');
                    startPhoneTimer();
                    setTimeout( function() {
                        $('.verifyPhone').css('display','');
                        $('.phonecountdown').css('display','none');
                        $('.phonecountdown').html('');
                    }, 61000 );
                
                    $('.edit_phone_feedback').html(response.message);
                    
                    setTimeout(function(){
                        $('.edit_phone_feedback').html('');
                    }, 5000);
                }
                else{
                    $(".invalid_phone_otp_error").html(response.message);
                }
            },
            error: function(data) {
                $(".invalid_phone_otp_error").html(data.responseJSON.message);
            },
        });
    }

    function startPhoneTimer()
    {
        var timer2 = "1:01";
        var interval = setInterval(function() {


        var timer = timer2.split(':');
        //by parsing integer, I avoid all extra string processing
        var minutes = parseInt(timer[0], 10);
        var seconds = parseInt(timer[1], 10);
        --seconds;
        minutes = (seconds < 0) ? --minutes : minutes;
        if (minutes < 0) clearInterval(interval);
        seconds = (seconds < 0) ? 59 : seconds;
        seconds = (seconds < 10) ? '0' + seconds : seconds;
        //minutes = (minutes < 10) ?  minutes : minutes;
        $('.phonecountdown').html(minutes + ':' + seconds);
        timer2 = minutes + ':' + seconds;
        }, 1000);
    }

    
                                            
                                        
</script>
</body>
</html>