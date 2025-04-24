<!doctype html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <script type="text/javascript" 
    src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" 
    src="https://openpay.s3.amazonaws.com/openpay.v1.min.js"></script>
    <script type='text/javascript' 
    src="https://openpay.s3.amazonaws.com/openpay-data.v1.min.js"></script>
    <script type="text/javascript" src="{{asset('js/card.js')}}"></script>
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
   
<script type="text/javascript">
        $(document).ready(function() {
            number = document.querySelector('#card_number');
            Payment.formatCardNumber(number);
            //cvc = document.querySelector('#cvc');
            //Payment.formatCardCVC(cvc);
            
            OpenPay.setId('{{$openpay_merchant_id}}');
            OpenPay.setApiKey('{{$openpay_private_key}}');
            OpenPay.setSandboxMode(true);
            //Se genera el id de dispositivo
            var deviceSessionId = OpenPay.deviceData.setup("payment-form", "deviceIdHiddenFieldName");
            console.log(deviceSessionId);
           
           
            $('#pay-button').on('click', function(event) {
                event.preventDefault();
                document.getElementById("pay-button").disabled = true;
                $('#device_session_id').value =deviceSessionId;
                $('#payment-form').submit();
               // OpenPay.token.extractFormAndCreate('payment-form', sucess_callbak, error_callbak);                
            });

            var sucess_callbak = function(response) {
              var token_id = response.data.id;
              $('#token_id').val(token_id);
              $('#payment-form').submit();
            };

            var error_callbak = function(response) {
                var desc = response.data.description != undefined ? response.data.description : response.message;
                alert("ERROR [" + response.status + "] " + desc);
                $("#pay-button").prop("disabled", false);
            };

        });
    </script>

<style>
@charset "US-ASCII";
@import "http://fonts.googleapis.com/css?family=Lato:300,400,700";
* {
    color: #444;
    font-family: Lato;
    font-size: 16px;
    font-weight: 300;
}::-webkit-input-placeholder {font-style: italic;}
:-moz-placeholder {font-style: italic;}
::-moz-placeholder {font-style: italic;}
:-ms-input-placeholder {font-style: italic;}

/* body {float: left;margin: 0;padding: 0;width: 100%;} */
strong {font-weight: 700;}
a {cursor: pointer;display: block;text-decoration: none;}
a.button {
    border-radius: 5px 5px 5px 5px;
    -webkit-border-radius: 5px 5px 5px 5px;
    -moz-border-radius: 5px 5px 5px 5px;
    text-align: center;
    font-size: 21px;
    font-weight: 400;
    padding: 12px 0;
    width: 100%;
    display: table;
    background: #E51F04;
    background: -moz-linear-gradient(top,  #E51F04 0%, #A60000 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#E51F04), color-stop(100%,#A60000));
    background: -webkit-linear-gradient(top,  #E51F04 0%,#A60000 100%);
    background: -o-linear-gradient(top,  #E51F04 0%,#A60000 100%);
    background: -ms-linear-gradient(top,  #E51F04 0%,#A60000 100%);
    background: linear-gradient(top,  #E51F04 0%,#A60000 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#E51F04', endColorstr='#A60000',GradientType=0 );
}
a.button i {
    margin-right: 10px;
}
a.button.disabled {
    background: none repeat scroll 0 0 #ccc;
    cursor: default;
}
/* .bkng-tb-cntnt {
    float: left;
    width: 800px;
} */
.bkng-tb-cntnt a.button {
    color: #fff;
    float: right;
    font-size: 18px;
    padding: 5px 20px;
    width: auto;
}
.bkng-tb-cntnt a.button.o {
    background: none repeat scroll 0 0 rgba(0, 0, 0, 0);
    color: #e51f04;
    border: 1px solid #e51f04;
}
.bkng-tb-cntnt a.button i {
    color: #fff;
}
.bkng-tb-cntnt a.button.o i {
    color: #e51f04;
}
.bkng-tb-cntnt a.button.right i {
    float: right;
    margin: 2px 0 0 10px;
}
.bkng-tb-cntnt a.button.left {
    float: left;
}
.bkng-tb-cntnt a.button.disabled.o {
    border-color: #ccc;
    color: #ccc;
}
.bkng-tb-cntnt a.button.disabled.o i {
    color: #ccc;
}
/* .pymnts {
    float: left;
    width: 800px;
} */
.pymnts * {
    float: left;
}

.sctn-row {
    margin-bottom: 35px;
    width: 800px;
}
/* .sctn-col {
    width: 375px;
} */
.sctn-col.l {
    width: 425px;
}
.sctn-col input {
    border: 1px solid #ccc;
    font-size: 18px;
    line-height: 24px;
    padding: 10px 12px;
    width: 333px;
}
.sctn-col label {
    font-size: 24px;
    line-height: 24px;
    margin-bottom: 10px;
    width: 100%;
}
.sctn-col.x3 {
    width: 300px;
}
.sctn-col.x3.last {
    width: 200px;
}
.sctn-col.x3 input {
    width: 210px;
}
.sctn-col.x3 a {
    float: right;
}
.pymnts-sctn {
    width: 800px;
}
.pymnt-itm {
    margin: 0 0 3px;
    width: 100%;
}
.pymnt-itm h2 {
    background-color: #e9e9e9;
    font-size: 24px;
    line-height: 24px;
    margin: 0;
    padding: 28px 0 28px 20px;
    /* width: 780px; */
}
.pymnt-itm.active h2 {
    background-color: #e51f04;
    color: #fff;
    cursor: default;
}
.pymnt-itm div.pymnt-cntnt {
    display: none;
}
.pymnt-itm.active div.pymnt-cntnt {
    background-color: #f7f7f7;
    display: block;
    padding: 0 0 30px;
    width: 100%;
}

.pymnt-cntnt div.sctn-row {
    margin: 10px 10px;
    width: 100%;
}
.pymnt-cntnt div.sctn-row div.sctn-col.l {
    width: 45%;
}
.pymnt-cntnt div.sctn-row div.sctn-col.half {
    width: 155px;
}
.pymnt-cntnt div.sctn-row div.sctn-col.half.l {
    float: left;
    width: 190px;
}
.pymnt-cntnt div.sctn-row div.sctn-col.half input {
    width: 113px;
}
.pymnt-cntnt div.sctn-row div.sctn-col.cvv {
    background-image: url("../../openpayImages/cvv.png");
    background-position: 156px center;
    background-repeat: no-repeat;
    padding-bottom: 30px;
}
.pymnt-cntnt div.sctn-row div.sctn-col.cvv div.sctn-col.half input {
    width: 110px;
}
/* .openpay {
    float: right;
    height: 60px;
    margin: 10px 30px 0 0;
    width: 435px;
} */
.openpay div.logo {
    background-image: url("../../openpayImages/openpay.png");
    background-position: left bottom;
    background-repeat: no-repeat;
    border-right: 1px solid #ccc;
    font-size: 12px;
    font-weight: 400;
    height: 45px;
    padding: 0px 20px 0 0;
}
.openpay div.shield {
    background-image: url("../../openpayImages/security.png");
    background-position: left bottom;
    background-repeat: no-repeat;
    font-size: 12px;
    font-weight: 400;
    margin-left: 20px;
    padding: 20px 0 0 40px;
    width:auto;
}
.card-expl {
    height: 80px;
    margin: 20px 0;
    width: 100%;
}
.card-expl div {
    background-position: left 45px;
    background-repeat: no-repeat;
    height: 70px;
    padding-top: 10px;
}
.card-expl div.debit {
    background-image: url("../../openpayImages/cards2.png");
}
.card-expl div.credit {
    background-image: url("../../openpayImages/cards1.png");
    border-right: 1px solid #ccc;
}
.card-expl h4 {
    font-weight: 400;
    margin: 0;
}



@media (max-width:576px){
    .pymnt-cntnt div.sctn-row div.sctn-col.l{
        width:100%;
    }
    .card-expl{
        height:auto;
    }
.sctn-col label {
    font-size: 17px;
    line-height: 24px;
    margin: 10px 0px;
}
.card-expl div.debit{
    background-size:contain;
}
.pymnt-cntnt div.sctn-row div.sctn-col.half.l{
    width:150px;
    display:inline-block;
}
}
</style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="bkng-tb-cntnt">
                <div class="pymnts">
                @if(\Session::has('error'))
                    <div class="alert alert-danger">
                        <span>{!! \Session::get('error') !!}</span>
                    </div>
                @endif
                    <form action="{{$return_url}}" method="POST" id="payment-form">
                        <input type="hidden" name="token_id" id="token_id">
                        @csrf
                        <div class="pymnt-itm card active">
                            <h2>{{__('Credit or debit card')}} </h2>
                            <div class="pymnt-cntnt">
                                <div class="card-expl">
                                    <div class="col-md-6 credit"><h4>{{__('Credit cards')}}Tarjetas de cr�dito</h4></div>
                                    <div class="col-md-6 debit"><h4>{{__('Debit cards')}}</h4></div>
                                </div>
                                <div class="sctn-row top">
                                    <div class="col-md-6 sctn-col l">
                                        <label>{{__('Name of owner')}}</label><input type="text" placeholder="{{__('Name of owner')}}" name="holder_name" autocomplete="off" data-openpay-card="holder_name">
                                    </div>
                                    <div class="col-md-6 sctn-col">
                                        <label>{{__('Card number')}}</label><input type="text" autocomplete="off" name="number" id="card_number" data-openpay-card="card_number"></div>
                                    </div>
                                    <div class="sctn-row">
                                        <div class="col-md-6 sctn-col l">
                                            <label>{{__('Expiration date')}}</label>
                                            <div class="sctn-col half l"><input type="text" placeholder="{{__('Month')}}" name="expMonth" data-openpay-card="expiration_month"></div>
                                            <div class="sctn-col half l"><input type="text" placeholder="{{__('Year')}}" name="expYear" data-openpay-card="expiration_year"></div>
                                        </div>
                                        <div class="col-md-6 sctn-col cvv"><label>{{__('Security code')}}</label>
                                            <div class="sctn-col half l"><input type="text" placeholder="{{__('3 digits')}}" name="cvc" id="cvc" autocomplete="off" data-openpay-card="cvv2"></div>
                                        </div>
                                    </div>
                                    <div class="open_pay"><div class="col-md-12 openpay my-3"><div class="logo">{{__('Transactions made via:')}}</div>
                                    <div class="shield">{{__('Your payments are made securely with 256-bit encryption')}}</div></div>
                                </div>
                                @forelse($data as $key=>$value)
                                    <input type="hidden" name="{{$key}}" value="{{$value}}">
                                @empty
                                @endforelse
                                <input type="hidden" name="device_session_id" id="device_session_id">
                                <div class="row sctn-row">
                                        <!-- <a class="button rght" id="pay-button">Pagar</a> -->
                                        <button type="button" class="btn btn-danger rght" id="pay-button">{{__('')}}Pagar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
    </div>
    </div>
</body>
</html>