 

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
@if($data['is_test'] == 1 && $data['is_test'] == '1')
<script type="text/javascript" src="https://jstest.authorize.net/v1/Accept.js"charset="utf-8"></script>
@else
<script type="text/javascript" src="https://js.authorize.net/v1/Accept.js"charset="utf-8"></script>
@endif
<script type="text/javascript" src="{{asset('js/card.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        number = document.querySelector('#cc-number');
        cvc = document.querySelector('#cc-cvc');
        Payment.formatCardNumber(number);
        Payment.formatCardCVC(cvc);
    });
    function sendPaymentDataToAnet() { 
    // Set up authorisation to access the gateway.
    var authData = {};
    authData.clientKey = "{{$data['client_key']}}";
    authData.apiLoginID = "{{$data['login_id']}}";

    // Capture the card details from the payment form.
    // The cardCode is the CVV.
    // You can include fullName and zip fields too, for added security.
    // You can pick up bank account fields in a similar way, if using
    // that payment method.
    var cardData = {};
    cardData.cardNumber = document.getElementById("cc-number").value.replace(/\s/g, '');
    cardData.month = document.getElementById("cc-exp-month").value;
    cardData.year = document.getElementById("cc-exp-year").value;
    cardData.cardCode = document.getElementById("cc-cvc").value;

    // Now send the card data to the gateway for tokenisation.
    // The responseHandler function will handle the response.
    var secureData = {};
    secureData.authData = authData;
    secureData.cardData = cardData;
    Accept.dispatchData(secureData, responseHandler);
}
function responseHandler(response) {
    $('.Error_message').html('');
    if (response.messages.resultCode === "Error") {
        var i = 0;
        while (i < response.messages.message.length) {
            console.log(
                response.messages.message[i].code + ": " +
                response.messages.message[i].text
            );
            $('.Error_message').append('<div class="alert alert-danger alert-dismissible fade show" role="alert">'+response.messages.message[i].text+'</div>');
            i = i + 1;
        }
    } else {
        paymentFormUpdate(response.opaqueData);
    }
}
function paymentFormUpdate(opaqueData) {
    document.getElementById("opaqueDataDescriptor").value = opaqueData.dataDescriptor;
    document.getElementById("opaqueDataValue").value = opaqueData.dataValue;
    document.getElementById("authorize-payment-form").submit();
}
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
            <form class="al_payment_gatewayForm" id="authorize-payment-form" action="{{route('payment.authorize.createPayment')}}" method="POST">

                <div class="form-group mb-1 Error_message">
        
                </div>

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
                <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" />
    			<input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" />
                @forelse($data as $key=>$value)
                <input type="hidden" name="{{$key}}" value="{{$value}}">
                @empty
                @endforelse
                @csrf
                <button id="process-payment-btn" class="btn btn-solid w-100 mt-4" type="button" onclick="sendPaymentDataToAnet()">{{__('Process Payment')}}</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>