<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Razorpay Payment Gateway </title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>
    <div id="app">
        <main class="py-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 offset-3 col-md-offset-6">

                        @if($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <strong>Error!</strong> {{ $message }}
                        </div>
                        @endif

                        @if($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade {{ Session::has('success') ? 'show' : 'in' }}" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <strong>Success!</strong> {{ $message }}
                        </div>
                        @endif

                        <div class="card card-default">
                            <div class="card-header">
                                Razorpay Payment Gateway
                            </div>

                            <div class="card-body text-center">
                                <form action="{{ route('payment.razorpayCompletePurchase',[app('request')->input('amount'),app('request')->input('order')]) }}" method="POST" id="gateway">
                                    @csrf
                                    <script src="https://checkout.razorpay.com/v1/checkout.js" data-key="<?php echo app('request')->input('api_key'); ?>" data-amount="<?php echo app('request')->input('amount'); ?>" data-buttontext="Pay" data-name="Razorpay Payment gateway" data-description="Rozerpay" data-prefill.name="name" data-prefill.email="email" data-theme.color="#ff7529">
                                    </script>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>

<script>
    $(window).on('load', function() {
        jQuery('#gateway').submit();
    });
</script>