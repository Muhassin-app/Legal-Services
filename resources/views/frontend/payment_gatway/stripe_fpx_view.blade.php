<!DOCTYPE html>
<head>
  <title>{{__('Stripe FPX Payment')}}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
          background: #ffffff;
      }
      button {
          background-color: {{getClientPreferenceDetail()->web_color}} ;
      }
  </style>
</head>
<body>
    <div class="payment-top-haeder py-2 mb-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <img src="{{ getClientDetail()->logo_image_url }}" alt="" height="100"> 
                </div>
            </div>
        </div>
    </div>
    <div class="container">
      @if(\Session::has('error'))
          <div class="alert alert-danger">
              <span>{!! \Session::get('error') !!}</span>
          </div>
      @endif
      <div class="row">
        <div class="offset-lg-3 col-lg-6">
          <form class="payment-form" id="stripe-fpx-checkout" action="{{$payment_retrive_stripe_fpx_url}}">
              <div class="wrapper">
                <div class="col-md-12 mt-3 mb-3 stripe_fpx_element_wrapper option-wrapper">
                  <label for="fpx-bank-element">
                      FPX Bank
                  </label>
                  <div class="form-control">
                      <div id="fpx-bank-element">
                        <!-- A Stripe Element will be inserted here. -->
                      </div>
                  </div>
                  <span class="error text-danger" id="stripe_fpx_error"></span>
                </div>
                <div class="col-md-12">
                  <button class="btn btn-info" id="submit-button" type="button">{{__('Submit')}}</button>
                </div>
              </div>
              @forelse($data as $key=>$value)
              <input type="hidden" name="{{$key}}" value="{{$value}}">
              @empty
              @endforelse
              @csrf
          </form>
        </div>
      </div>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    <script>
      var payment_create_stripe_fpx_url = "{{url('payment/create/stripe_fpx')}}";
      var stripe_fpx_publishable_key = '{{ $stripe_fpx_publishable_key }}';
      var stripe_fpx = Stripe(stripe_fpx_publishable_key);
      var elements = stripe_fpx.elements();
      var style = {
          base: {
            // Add your base input styles here. For example:
            padding: '10px 12px',
            color: '#32325d',
            fontSize: '16px',
          },
      };
      var fpxBank = elements.create('fpxBank',
          {
            style: style,
            accountHolderType: 'individual',
          }
      );
      // Add an instance of the fpxBank Element into the container with id `fpx-bank-element`.
      fpxBank.mount('#fpx-bank-element');


      $(document).on('click', '#submit-button', function(){
        $("#stripe_fpx_error").text('');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_create_stripe_fpx_url,
            data: $("#stripe-fpx-checkout").serializeArray(),
            success: function(resp) {
                if (resp.status == 'Success') {
                    const clientSecret = resp.data;
                    const result = stripe_fpx.confirmFpxPayment(clientSecret, {
                        payment_method: {
                            fpx: fpxBank
                        },
                        // Return URL where the customer should be redirected after the authorization
                        return_url: $("#stripe-fpx-checkout").attr('action'),
                    });
                    if (result.error) {
                        // Inform the customer that there was an error.
                        $("#stripe_fpx_error").text(result.error.message);
                    }
                } else {
                    $("#stripe_fpx_error").text(resp.message);
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                $("#stripe_fpx_error").text(response.message);
            }
        });
      });
      
    </script>
</body>
</html>
