<html>

<head>
  <title>{{__('My Payment Flow')}}</title>
  <!-- link to the Square web payment SDK library -->
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/font-awesome.min.css')}}">
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/custom.css')}}">
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
            background: {{getClientPreferenceDetail()->web_color}}; 
        }
        button {
            background-color: {{getClientPreferenceDetail()->web_color}} ;
        }
    </style>
    <style type="text/css">
    .button {cursor: pointer;font-weight: 500;left: 3px;line-height: inherit;position: relative;text-decoration: none;text-align: center;border-style: solid;border-width: 1px;border-radius: 3px;-webkit-appearance: none;-moz-appearance: none;display: inline-block;}
    .button--small {padding: 10px 20px;font-size: 0.875rem;}
    .button--green {outline: none;background-color: #64d18a;border-color: #64d18a;color: white;transition: all 200ms ease;}
    .button--green:hover {background-color: #8bdda8;color: white;}
    </style>
</head>

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
  <div id="dropin-wrapper">
    <div id="checkout-message"></div>
    <div class="container text-center">
      <div id="dropin-container"></div>
      <button id="submit-button" class="button button--small button--green">Submit payment</button>
    </div>
  </div>
  <form class="al_payment_gatewayForm" id="braintree-payment-form" action="{{route('payment.braintree.createPayment')}}" method="POST">
    @forelse($data as $key=>$value)
    <input type="hidden" name="{{$key}}" value="{{$value}}">
    @empty
    @endforelse
    @csrf
    <input type="hidden" name="paymentMethodNonce" id="paymentMethodNonce">
    <input type="hidden" name="deviceData" id="deviceData">
  </form>

  <!-- includes the Braintree JS client SDK -->
  <script src="http://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
  <script src="https://js.braintreegateway.com/web/dropin/1.29.0/js/dropin.min.js"></script>
  <script src="https://js.braintreegateway.com/web/3.77.0/js/client.min.js"></script>
  <script src="https://js.braintreegateway.com/web/3.77.0/js/data-collector.min.js"></script>
  <script type="text/javascript">
    var button = document.querySelector('#submit-button');
    braintree.dropin.create({
      authorization: "{{$data['token']}}",
      selector: '#dropin-container'
    }, function (err, instance) {
      button.addEventListener('click', function () {
        instance.requestPaymentMethod(function (err, payload) {
          console.log(payload.nonce);
          $('#paymentMethodNonce').val(payload.nonce);
          $('#braintree-payment-form').submit();
        });
      })
    });

    braintree.client.create({
      authorization: "{{$data['token']}}"
    }).then(function (clientInstance) {
      // Creation of any other components...
      return braintree.dataCollector.create({
        client: clientInstance,
        paypal: true
      }).then(function (dataCollectorInstance) {
        // At this point, you should access the dataCollectorInstance.deviceData value and provide it
        // to your server, e.g. by injecting it into your form as a hidden input.
        var deviceData = dataCollectorInstance.deviceData;
        $('#deviceData').val(deviceData);
      });
    }).catch(function (err) {
      // Handle error in creation of components
    });
  </script>
</body>

</html>