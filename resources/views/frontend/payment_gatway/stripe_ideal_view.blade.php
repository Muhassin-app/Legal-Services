<!DOCTYPE html>
<head>
  <title>{{__('Stripe Ideal Payment')}}</title>
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
          <form class="payment-form" id="stripe-ideal-checkout" action="{{$payment_retrive_stripe_ideal_url}}">
              <div class="wrapper">
                <div class="col-md-12 mt-3 mb-3 stripe_ideal_element_wrapper option-wrapper">
                  <label for="ideal-bank-element">
                      iDeal Bank
                  </label>
                  <div class="form-control">
                      <div id="ideal-bank-element">
                        <!-- A Stripe Element will be inserted here. -->
                      </div>
                  </div>
                  <span class="error text-danger" id="stripe_ideal_error"></span>
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
      var payment_create_stripe_ideal_url = "{{url('payment/create/stripe_ideal')}}";
      var stripe_ideal_publishable_key = '{{ $stripe_ideal_publishable_key }}';
      stripe_ideal = Stripe(stripe_ideal_publishable_key);
        var elements = stripe_ideal.elements();
        var options = {
            // Custom styling can be passed to options when creating an Element
            style: {
              base: {
                padding: '10px 12px',
                color: '#32325d',
                fontSize: '16px',
                '::placeholder': {
                  color: '#aab7c4'
                },
              },
            },
          };
          
          // Create an instance of the idealBank Element
          var idealBank = elements.create('idealBank', options);
          // Add an instance of the idealBank Element into
          // the `ideal-bank-element` <div>
          idealBank.mount('#ideal-bank-element'); 


      $(document).on('click', '#submit-button', function(){
        $("#stripe_ideal_error").text('');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_create_stripe_ideal_url,
            data: $("#stripe-ideal-checkout").serializeArray(),
            success: function(resp) {
                if (resp.status == 'Success') {
                    const clientSecret = resp.data.client_secret;
                    const result = stripe_ideal.confirmIdealPayment(clientSecret, {
                        payment_method: {
                            ideal: idealBank,
                            billing_details: {
                                name: resp.data.shipping.name,
                            },
                        },
                        // Return URL where the customer should be redirected after the authorization
                        return_url: $("#stripe-ideal-checkout").attr('action'),
                    });
                    if (result.error) {
                        // Inform the customer that there was an error.
                        $("#stripe_ideal_error").text(result.error.message);
                    }
                } else {
                    $("#stripe_ideal_error").text(resp.message);
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                $("#stripe_ideal_error").text(response.message);
            }
        });
      });
      
    </script>
</body>
</html>
