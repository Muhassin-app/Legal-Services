

 <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="_token" content="{{ csrf_token() }}">
    <title>{{__('Userede Payment Checkout')}}</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/font-awesome.min.css')}}">
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
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
        .btn-solid{
            padding: 13px 29px;
            color: #ffffff !important;
            letter-spacing: 0.05em;
            border: 2px solid var(--theme-deafult);
            background: {{getClientPreferenceDetail()->web_color}};
            -webkit-transition: background 300ms ease-in-out;
            transition: background 300ms ease-in-out;
        }
    </style>
</head>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="{{asset('js/card.js')}}"></script>
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        document.addEventListener('contextmenu', event => event.preventDefault());
      

        number = document.querySelector('#cc-number');
        cvc = document.querySelector('#cc-cvc');
        exp_month = document.querySelector('#cc-exp-month');
        exp_year = document.querySelector('#cc-exp-year');
        Payment.formatCardNumber(number);
        Payment.formatCardCVC(cvc);
        // Payment.formatCardExpiry(exp_month+"/"+exp_year);
       
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

<div class="container">
    @if(\Session::has('error'))
        <div class="alert alert-danger">
            <span>{!! \Session::get('error') !!}</span>
        </div>
    @endif
    <div class="row">
        <div class="offset-lg-3 col-lg-6">
            <form id="pagarme-payment-form" action="{{$return_url}}" method="POST">
            @csrf
                <div class="form-group">
                    <label>{{__('Card Holder Name')}}: </label>
                    <input class="form-control" id="cc-name" value ="{{ old('holder_name') }}" type="text" maxlength="20" autocomplete="off" name="holder_name" required autofocus />
               
                    @if($errors->first('holder_name'))
                    <span class="form-error">{{$errors->first('holder_name')}}</span>
                    @endif     
                </div>
                <div class="form-group">
                    <label>{{__('Card Number')}}: </label>
                    <input class="form-control" id="cc-number"  value ="{{ old('number') }}" type="text" maxlength="20" autocomplete="off" name="number" required autofocus />
                    @if($errors->first('number'))
                    <span class="form-error">{{$errors->first('number')}}</span>
                    @endif 
                </div>
                <div class="form-group">
                    <label>{{__('CVC')}}: </label>
                    <input class="form-control" id="cc-cvc" value ="{{ old('cvc') }}" type="text" maxlength="4" autocomplete="off" name="cvc" required />
                    @if($errors->first('cvc'))
                    <span class="form-error">{{$errors->first('cvc')}}</span>
                    @endif
                </div>
                <div class="form-group">
                    <label>{{__('Expiry Date')}}: </label>
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <select class="form-control" id="cc-exp-month" name="expMonth" required>
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
                            <select class="form-control" id="cc-exp-year" name="expYear" required>
                                @for($i=0; $i<10; $i++)
                                <option value="{{date('y') + $i}}">{{date('Y') + $i}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{__('Card Type')}}: </label>
                        <select class="form-control" id="cc-exp-month" name="cart_type" required>
                            <option value="creditCard">{{__('Credit Card')}}</option>
                            <option value="debitCard">{{__('Debit Card')}}</option>
                        </select>
                </div>
                @forelse($data as $key=>$value)
                <input type="hidden" name="{{$key}}" value="{{$value}}">
                @empty
                @endforelse
                @csrf
                <input type="hidden" name="card_id" id="cc-card_id">
                <button id="process-payment-btn" class="btn btn-solid w-100 mt-4" type="submit">{{__('Process Payment')}}</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>