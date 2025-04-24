<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="_token" content="{{ csrf_token() }}">
    <title>Payment Checkout</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/font-awesome.min.css')}}">
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/custom.css')}}">
    <link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
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
        .err_message{
            display: none;
        }
    </style>
</head>
<body>
<section class="section-b-space">
    <div class="container bg-light" style="margin:0 auto;margin-top:50px;">
        <div class="col-md-12 text-center">
            <div class="d-flex justify-content-center">
                <h2>Debit / Credit Card</h2>
            </div>
        </div>
        <div class="col-md-12 text-center">
            <div class="d-flex justify-content-center">
                <form id="payment-form" method="POST" style="border:1px solid black;padding:10px;">
                    @csrf
                    <div class="one-liner">
                        <div id="card-frame">
                            <!-- Yoco Inline form will be added here -->
                        </div>
                    </div>
                    <br>
                    <div class="err_message text-left text-danger"></div>
                    <br>
                    <button class="btn btn-info" type="submit" id="pay-button">Pay</button>
                    <button class="btn btn-danger" type="button" id="cancel-button">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</section>
<p class="success-payment-message" />
<div class="spinner-overlay">
    <div class="page-spinner">
        <div class="circle-border">
            <div class="circle-core"></div>
        </div>
    </div>
</div>
<script src="{{asset('front-assets/js/jquery-3.3.1.min.js')}}"></script>
<script src="https://js.yoco.com/sdk/v1/yoco-sdk-web.js"></script>
<script src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
<script>
    let payment_success_url = "{{route('payment.getCheckoutSuccess', ':id')}}";
    let payment_return_url = "{{route('payment.gateway.return.response')}}/?gateway=yoco";
    let queryString = window.location.search;
    let path = window.location.pathname;
    let urlParams = new URLSearchParams(queryString);
    let yoco_public_key, order_number = '';
    let yoco_amount_payable = 0;
    let auth_token = '';
    let action = '';
    if (urlParams.get('public_key_yoco') != '') {
        yoco_public_key = urlParams.get('public_key_yoco');
        yoco_amount_payable = urlParams.get('amount');
        if(urlParams.has('order')){
            order_number = urlParams.get('order');
            payment_return_url = payment_return_url + '&order=' + order_number;
        }
        auth_token = urlParams.get('auth_token');
        action = urlParams.get('action');
        payment_return_url = payment_return_url + '&action=' + action;

        var sdk = new window.YocoSDK({
            publicKey: yoco_public_key
        });

        // Create a new dropin form instance
        var inline = sdk.inline({
            layout: 'basic',
            amountInCents: yoco_amount_payable * 100,
            currency: 'ZAR'
        });
        // this ID matches the id of the element we created earlier.
        inline.mount('#card-frame');
        var payment_yoco_url = "{{route('payment.yocoPurchaseApp')}}";
        var form = document.getElementById('payment-form');
        var submitButton = document.getElementById('pay-button');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            $('.spinner-overlay').show();
            // Disable the button to prevent multiple clicks while processing
            submitButton.disabled = true;
            // This is the inline object we created earlier with the sdk
            inline.createToken().then(function(result) {
                if (result.error) {
                    const errorMessage = result.error.message;
                    errorMessage && alert("error occured: " + errorMessage);
                } else {
                    const token = result;
                    paymentViaYoco(auth_token, order_number, token.id, yoco_amount_payable, action);
                }

            }).catch(function(error) {
                // Re-enable button now that request is complete
                $('.spinner-overlay').show();
                submitButton.disabled = false;
                Swal.fire({
                   // title: "Warning!",
                   text: "error occured: " + error,
                   icon : "error",
                   button: "OK",
                });
                //alert("error occured: " + error);
            });
        });

    }else{
        alert('Invalid data');
    }

    // Any additional form data you want to submit to your backend should be done here, or in another event listener
    window.paymentViaYoco = function paymentViaYoco(auth_token='', order_number='', token='', amount, action='') {
        var ajaxData = {};
        ajaxData.auth_token = auth_token;
        ajaxData.payment_form = action;
        ajaxData.token = token;
        ajaxData.order_number = order_number;
        ajaxData.amount = amount;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_yoco_url,
            data: ajaxData,
            success: function(response) {
                if (response.status == "Success") {
                    payment_return_url = payment_return_url + '&status=200&transaction_id=' + response.data.id;
                    if(action == 'cart'){
                        // payment_success_url = payment_success_url.replace(':id', response.data.id);
                    }
                } else {
                    payment_return_url = payment_return_url + '&status=0';
                    // success_error_alert('error', response.message, ".success-payment-message");

                }
                location.href = payment_return_url;
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                $('.err_message').text(response.message).show();
                // success_error_alert('error', response.message, ".success-payment-message");
            },
            complete: function(data) {
                // Re-enable button now that request is complete
                // (i.e. on success, on error and when auth is cancelled)
                $("#pay-button").attr('disabled', false);
                $('.spinner-overlay').hide();
            }
        });
    }

    $(document).delegate("#cancel-button", "click", function(){
        location.href = payment_return_url + '&status=0';
    });
</script>

</body>
</html>
