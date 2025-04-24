

 <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="_token" content="{{ csrf_token() }}">
    <title>{{__('Payment Checkout')}}</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/font-awesome.min.css')}}">
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/payment.css')}}">
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
            background: '{{getClientPreferenceDetail()->web_color}}';
        }
        .btn-solid{
            padding: 13px 29px;
            color: #ffffff !important;
            letter-spacing: 0.05em;
            border: 2px solid var(--theme-deafult);
            background: '{{getClientPreferenceDetail()->web_color}}';
            -webkit-transition: background 300ms ease-in-out;
            transition: background 300ms ease-in-out;
        }
    </style>
</head>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://www.simplify.com/commerce/v1/simplify.js"></script>
<script type="text/javascript" src="{{asset('js/card.js')}}"></script>
<script type="text/javascript">
    function simplifyResponseHandler(data) {
        console.log(data);
        // return false;
        var $paymentForm = $("#simplify-payment-form");
        // Remove all previous errors
        $(".error").remove();
        // Check for errors
        if (data.error) {
            // Show any validation errors
            if (data.error.code == "validation") {
                var fieldErrors = data.error.fieldErrors,
                        fieldErrorsLength = fieldErrors.length,
                        errorList = "";
                for (var i = 0; i < fieldErrorsLength; i++) {
                    // errorList += "<div class='error'>Field: '" + fieldErrors[i].field +
                    //         "' is invalid - " + fieldErrors[i].message + "</div>";
                    errorList += "<div class='error'>"+ fieldErrors[i].message + "</div>";
                }
                // Display the errors
                $paymentForm.after(errorList);
            }
            // Re-enable the submit button
            $("#process-payment-btn").removeAttr("disabled");
        } else {
            // The token contains id, last4, and card type
            var token = data["id"];
            // Insert the token into the form so it gets submitted to the server
            $paymentForm.append("<input type='hidden' name='simplifyToken' value='" + token + "' />");
            // Submit the form to the server
            $paymentForm.get(0).submit();
        }
    }
    $(document).ready(function() {
        number = document.querySelector('#cc-number');
        cvc = document.querySelector('#cc-cvc');
        Payment.formatCardNumber(number);
        Payment.formatCardCVC(cvc);
        $("#simplify-payment-form").on("submit", function() {
            var number = $("#cc-number").val();
            // console.log(number.replace(/\s/g, ''));
            // Disable the submit button
            $("#process-payment-btn").attr("disabled", "disabled");
            // Generate a card token & handle the response
            SimplifyCommerce.generateToken({
                key: "{{$data['public_key']}}",
                card: {
                    number: number.replace(/\s/g, ''),
                    cvc: $("#cc-cvc").val(),
                    expMonth: $("#cc-exp-month").val(),
                    expYear: $("#cc-exp-year").val()
                }
            }, simplifyResponseHandler);
            // Prevent the form from submitting
            return false;
        });
    });
</script>
<body>

<div class="payment-top-haeder py-2 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <img src="{{ getClientDetail()->logo_image_url }}" alt="" height="50">
            </div>
        </div>
    </div>
</div>

<div class="container al_payment_gateway">
    <div class="row">
        <div class="offset-lg-3 col-lg-6">
            <form class="al_payment_gatewayForm" id="simplify-payment-form" action="{{route('payment.simplify.createPayment')}}" method="POST">
                <!-- The $10 amount is set on the server side -->
                <div class="form-group">
                    <label>{{__('Credit Card Number')}}: </label>
                    <input class="form-control" id="cc-number" type="text" maxlength="20" autocomplete="off" value="" autofocus />
                </div>
                <div class="form-group">
                    <label>{{__('CVC')}}: </label>
                    <input class="form-control" id="cc-cvc" type="text" maxlength="4" autocomplete="off" value=""/>
                </div>
                <div class="form-group">
                    <label>{{__('Expiry Date')}}: </label>
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <select class="form-control" id="cc-exp-month">
                                <option value="01">Jan</option>
                                <option value="02">Feb</option>
                                <option value="03">Mar</option>
                                <option value="04">Apr</option>
                                <option value="05">May</option>
                                <option value="06">Jun</option>
                                <option value="07">Jul</option>
                                <option value="08">Aug</option>
                                <option value="09">Sep</option>
                                <option value="10">Oct</option>
                                <option value="11">Nov</option>
                                <option value="12">Dec</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control" id="cc-exp-year">
                                @for($i=0; $i<10; $i++)
                                <option value="{{date('y') + $i}}">{{date('Y') + $i}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                @forelse($data as $key=>$value)
                <input type="hidden" name="{{$key}}" value="{{$value}}">
                @empty
                @endforelse
                @csrf
                <button id="process-payment-btn" class="btn btn-solid w-100 mt-3" type="submit">{{__('Process Payment')}}</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>