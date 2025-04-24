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
  <link rel="stylesheet" type="text/css" href="{{asset('square/css/style.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('square/css/sq-payment.css')}}">

  <script type="text/javascript" src="{{$data['square_url']}}"></script>
  <script type="text/javascript">
    window.applicationId = "{{$data['application_id']}}";
    window.locationId = "{{$data['location_id']}}";
    window.currency = "{{$data['currency']}}";
    window.country = "{{$data['country']}}";
  </script>
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
    <form class="payment-form" id="fast-checkout" method="POST" action="{{route('payment.square.createPayment')}}">
        <div class="wrapper">
          <div id="apple-pay-button" alt="apple-pay" type="button"></div>
          <div id="google-pay-button" alt="google-pay" type="button"></div>
          <div class="border">
            <span>{{__('OR')}}</span>
          </div>
          <div id="ach-wrapper">
            <label for="ach-account-holder-name">{{__('Full Name')}}</label>
            <input id="ach-account-holder-name" type="text" placeholder="Jane Doe" name="ach-account-holder-name" autocomplete="name" /><span id="ach-message"></span><button id="ach-button" type="button">{{__('Pay with Bank Account')}}</button>

            <div class="border">
              <span>{{__('OR')}}</span>
            </div>
          </div>
          <div id="card-container"></div><button id="card-button" type="button">{{__('Pay with Card')}}</button>
          <span id="payment-flow-message"></span>
        </div>
        @forelse($data as $key=>$value)
        <input type="hidden" name="{{$key}}" value="{{$value}}">
        @empty
        @endforelse
        @csrf
        <input type="hidden" id="source_id" name="source_id" value="">
    </form>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="{{asset('square/js/sq-ach.js')}}"></script>
    <script type="text/javascript" src="{{asset('square/js/sq-apple-pay.js')}}"></script>
    <script type="text/javascript" src="{{asset('square/js/sq-card-pay.js')}}"></script>
    <script type="text/javascript" src="{{asset('square/js/sq-google-pay.js')}}"></script>
    <script type="text/javascript" src="{{asset('square/js/sq-payment-flow.js')}}"></script>
</body>

</html>
