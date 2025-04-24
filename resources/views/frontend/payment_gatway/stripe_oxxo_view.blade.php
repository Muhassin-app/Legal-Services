<!DOCTYPE html>
<head>
  <title>{{__('Stripe OXXO Payment')}}</title>
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
            <div style="display: block;margin:15% 20%"><img src="{{asset('loader.webp')}}"></div>
          <form class="payment-form" id="stripe-oxxo-checkout" action="{{$payment_retrive_stripe_oxxo_url}}">
              <div class="wrapper">
                <div class="col-md-12 mt-3 mb-3 stripe_oxxo_element_wrapper option-wrapper">
                  <span class="error text-danger" id="stripe_oxxo_error"></span>
                </div>
              </div>
              @forelse($data as $key=>$value)
              <input type="hidden" name="{{$key}}" id="{{$key}}" value="{{$value}}">
              @empty
              @endforelse
              @csrf
          </form>
        </div>
      </div>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    <script>
      var payment_create_stripe_oxxo_url = "{{url('payment/create/stripe_oxxo')}}";
      var cart_clear_stripe_oxxo_url = "{{url('payment/stripe_oxxo/clear')}}";
      var stripe_oxxo_publishable_key = '{{ $stripe_oxxo_publishable_key }}';
      var stripe_oxxo = Stripe(stripe_oxxo_publishable_key);
      var payment_from = $('#payment_form').val();
      var order_number = $('#order_number').val();
      var come_from = $('#come_from').val();

      //var elements = stripe_oxxo.elements();
      var style = {
          base: {
            // Add your base input styles here. For example:
            padding: '10px 12px',
            color: '#32325d',
            fontSize: '16px',
          },
      };
    //   var oxxoBank = elements.create('oxxoBank',
    //       {
    //         style: style,
    //         accountHolderType: 'individual',
    //       }
    //   );
    //   // Add an instance of the oxxoBank Element into the container with id `oxxo-bank-element`.
    //   oxxoBank.mount('#oxxo-bank-element');


    // $(document).on('click', '#submit-button', function(){
    $( document ).ready(function() {
        $("#stripe_oxxo_error").text('');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_create_stripe_oxxo_url,
            data: $("#stripe-oxxo-checkout").serializeArray(),
            success: function(resp) {
                if (resp.status == 'Success') {
                    const clientSecret = resp.data.client_secret;
                    const result = stripe_oxxo.confirmOxxoPayment(clientSecret, {
                          payment_method: {
                            billing_details: {
                                name: resp.data.shipping.name,
                                email: resp.data.receipt_email,
                            },
                          },
                        })
                        .then(function(result) {

                            if (result.error) {
                                // Inform the customer that there was an error.
                                $("#stripe_oxxo_error").text(result.error.message);
                            }
                        // Return URL where the customer should be redirected after the authorization
                      
                        window.location.href=cart_clear_stripe_oxxo_url+'?no='+order_number+'&payment_from='+payment_from+'&come_from='+come_from;
                          
                    });
                   
                } else {
                    $("#stripe_oxxo_error").text(resp.message);
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                $("#stripe_oxxo_error").text(response.message);
            }
        });
      });
      
    </script>
</body>
</html>
