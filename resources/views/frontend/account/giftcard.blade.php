@extends('layouts.store', ['title' => 'My gift Card'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')
@php
    $timezone = Auth::user()->timezone;
    $now = \Carbon\Carbon::now()->toDateString();
    $after7days = \Carbon\Carbon::now()->addDays(7)->toDateString();
@endphp

<style type="text/css">
.invalid-feedback {display: block;}
ul li {margin: 0 0 10px;color: #6c757d;}
.main-menu .brand-logo {display: inline-block;padding-top: 20px;padding-bottom: 20px;}
form#giftCard_payment_form input.form-control {
    width: 100%;
    margin: 0px;
}
.gift_card label {
    display: inline-block;
}

.gift_card 
 input#check_is_delivery {
    width: 3% !important;
    display: inline-block;
    height: auto;
}
</style>

<section class="section-b-space">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="text-sm-left">
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <button type="button" class="close p-0" data-dismiss="alert">x</button>
                            <span>{!! \Session::get('success') !!}</span>
                        </div>
                        @php
                            \Session::forget('success');
                        @endphp
                    @endif
                    @if (\Session::has('error'))
                        <div class="alert alert-danger">
                            <button type="button" class="close p-0" data-dismiss="alert">x</button>
                            <span>{!! \Session::get('error') !!}</span>
                        </div>
                        @php
                            \Session::forget('error');
                        @endphp
                    @endif
                    @if ( ($errors) && (count($errors) > 0) )
                        <div class="alert alert-danger">
                            <button type="button" class="close p-0" data-dismiss="alert">x</button>
                            <ul class="m-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row my-md-3 mt-5 pt-4">
            <div class="col-lg-3">
                <div class="account-sidebar"><a class="popup-btn">my account</a></div>
                @include('layouts.store/profile-sidebar')
            </div>
            <div class="col-lg-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2>{{ __('My Gift Card') }}</h2>
                        </div>

                        <div class="row">
                          
                            @if(!empty($active_giftcards))
                            @forelse ($active_giftcards  as $active_giftcard)
                            
                            <div class="col-12 mb-4">
                                <div class="card subscript-box">
                                    
                                    <div class="row align-items-center mb-2">
                                        <div class="col-sm-3 text-center">
                                            <div class="gold-icon">
                                                <img src="{{$active_giftcard->giftCard->image['proxy_url'].'100/100'.$active_giftcard->giftCard->image['image_path']}}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-sm-9 mt-3 mt-sm-0">
                                            <div class="row align-items-end border-left-top pt-sm-0 pt-2">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h3 class="d-inline-block"><b>{{ $active_giftcard->giftCard->title }}</b></h3>
                                                        <span class="plan-price">{{ Session::get('currencySymbol') . ($active_giftcard->giftCard->amount * $clientCurrency->doller_compare) }}</span>
                                                    </div>
                                                    <p>{{ $active_giftcard->giftCard->short_desc }}</p>
                                                   
                                                </div>

                                                <div class="col-sm-6 form-group mb-0">
                                                    <b class="mr-2">{{ __('Expired On') }}</b>
                                                    <span>{{ dateTimeInUserTimeZone($active_giftcard->giftCard->expiry_date, $timezone, true, false) }}</span>
                                                </div>
                                                
                                                <div class="col-sm-6 mb-0 text-center text-sm-right">
                                                        <a class="btn btn-solid" href="javascript:void(0)">{{ __('Total') }} ({{ $active_giftcard->total }})</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            @empty
                            <div class="no_gift text-center py-3 col-12">
                                <h4>{{ __('You dont have Gift Card') }}</h4>
                            </div>
                            @endforelse
                           
                            @endif
                            <div class="page-title col-12">
                                <h2>{{ __('Gift Card List') }}</h2>
                            </div>
                            @if($GiftCard->isNotEmpty())
                                @foreach($GiftCard as $GCard)
                                <div class="col-md-3 col-sm-6 mb-3 mb-md-2">
                                    <div class="pricingtable">
                                        <div class="gold-icon position-relative">
                                            <img src="{{ $GCard->image['proxy_url'].'100/100'.$GCard->image['image_path'] }}">
                                            <div class="pricingtable-header position-absolute">
                                                <div class="price-value"> <b>{{ Session::get('currencySymbol') . ($GCard->amount * $clientCurrency->doller_compare) }}</b> <span class="month">{{ $GCard->frequency }}</span> </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        <h3 class="heading mt-0 mb-2"><b>{{ __($GCard->title) }}</b></h3>
                                        <div class="pricing-content">
                                            <p>{{ __($GCard->short_desc) }}</p>
                                        </div>
                                       
                                    </div>
                                    <div class="pricingtable-purchase">
                                            <button class="btn btn-solid w-100  giftCardBuy_btn" data-id="{{ $GCard->id }}">{{ __('Buy') }}</button>
                                    </div>
                                </div>
                                @endforeach
                            @else
                            <div class="no_gift text-center py-3 col-12">
                                <h4>{{ __('Empty Gift Card') }}</h4>
                            </div>    
                            @endif
                        </div>
                    </div>

                </div>



            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="cancel-subscription" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="cancel_subscriptionLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-0">
        <h5 class="modal-title" id="cancel_subscriptionLabel">{{ __('Unsubscribe') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <form id="cancel-subscription-form" method="POST" action="">
        @csrf
        <div class="modal-body">
            <h6 class="m-0">{{ __('Do you really want to cancel this subscription ?') }}</h6>
        </div>
        <div class="modal-footer flex-nowrap justify-content-center align-items-center">
            <button type="submit" class="btn btn-solid">{{ __('Continue') }}</a>
            <button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{ __('Cancel') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="error_response" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="error_responseLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-0">
        <h5 class="modal-title" id="error_responseLabel">{{ __('Error') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <h6 class="message_body">{{ __('Unknown error occurs') }}</h6>
      </div>
      <div class="modal-footer flex-nowrap justify-content-center align-items-center">
        <button type="button" class="btn btn-solid" data-dismiss="modal">{{ __('Ok') }}</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="GiftCard_payment" tabindex="-1" aria-labelledby="subscription_paymentLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title text-17 mb-0 mt-0" id="subscription_paymentLabel">{{ __('Gift Card') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="" id="giftCard_payment_form">
        @csrf
        @method('POST')
        <div>
            <input type="hidden" name="email" id="email" value="{{ Auth::user()->email }}">
            <input type="hidden" name="giftCard_id" id="giftCard_id" value="">
            <input type="hidden" name="giftCard_amount" id="giftCard_amount" value="">
            <input type="hidden" name="card_last_four_digit" id="card_last_four_digit" value="">
            <input type="hidden" name="card_expiry_month" id="card_expiry_month" value="">
            <input type="hidden" name="card_expiry_year" id="card_expiry_year" value="">
        </div>
        <div class="modal-body pb-0">
            <div class="form-group">
                <div class="position-relative">
                    <div class="row">
                        <div class="col-5" id="gift_card_image" >
                         
                        </div>
                        <div class="col-7 pl-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="text-17 m-0" id="giftCard__title">gold</h5>
                                <span id="giftCard_price"></span>
                            </div>
                            <p id="giftCard_disc"></p>
                        </div>
                    </div>

                        
                  
                </div>
            
               
            </div>
            <hr class="mb-1" />
            <div  class="row" >
                <div class="form-group col-12">
                <p class="modal-title text-17 mb-0 mt-2" >{{ __('Send Gift Card') }}</p>
                </div>
                <div class="form-group col-6">
                    {!! Form::text('send_card_to_name','', ['class'=>'form-control', 'placeholder' => 'Name', 'required' => 'required']) !!}
                </div>
                <div class="form-group col-6">
                    {!! Form::text('send_card_to_mobile', '', ['class'=>'form-control', 'placeholder' => 'Mobile', 'required' => 'required']) !!}
                </div>
                <div class="form-group col-12">
                    {!! Form::text('send_card_to_email','', ['class'=>'form-control', 'placeholder' => 'E-mail', 'required' => 'required']) !!}
                </div>
                <div class="form-group col-12">
                    {!! Form::text('send_card_to_address','', ['class'=>'form-control', 'placeholder' => 'Address', 'required' => 'required']) !!}
                </div>
                <div class="form-group col-12 gift_card d-flex align-items-center">
                   
                      
                       
                        <input data-plugin="switchery" id="check_is_delivery" class="form-control checkbox_change" data-color="#43bee1" data-className="send_card_is_delivery" type="checkbox" >
                        <input type="hidden"  name="send_card_is_delivery" value="0" id="send_card_is_delivery"/>
                        <label for="check_is_delivery" class="ml-1">{{ __("Is Deliverable") }}</label>
                </div>
            </div>
            <hr class="mb-1" />
            <div class="payment_response">
                <div class="alert p-0 m-0" role="alert"></div>
            </div>
            <h5 class="text-17 mb-2">{{ __('Debit From') }}</h5>
            <div class="form-group" id="giftCard_payment_methods">
            </div>
        </div>
        <div class="modal-footer d-block text-center">
            <div class="row">
                <div class="col-sm-12 p-0 d-flex justify-space-around">
                    <button type="button" class="btn btn-block btn-solid mr-1 mt-2 giftCard_confirm_btn">{{ __('Pay') }}</button>
                    <button type="button" class="btn btn-block btn-solid ml-1 mt-2" data-dismiss="modal">{{ __('Cancel') }}</button>
                </div>
            </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/template" id="payment_method_template">
    <% if(payment_options == '') { %>
        <h6>{{ __('Payment Options Not Avaialable') }}</h6>
    <% }else{ %>
        <% _.each(payment_options, function(payment_option, k){%>
            <% if( (payment_option.slug != 'cash_on_delivery') && (payment_option.slug != 'loyalty_points') ) { %>
                <label class="radio mt-2">
                    <%= payment_option.title %>
                    <input type="radio" name="GiftCard_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.slug %>" data-payment_option_id="<%= payment_option.id %>">
                    <span class="checkround"></span>
                </label>
                <% if(payment_option.slug == 'stripe') { %>
                    <div class="col-md-12 mt-3 mb-3 stripe_element_wrapper option-wrapper d-none">
                        <div class="form-control">
                            <label class="pb-1 mb-0">
                                <div id="stripe-card-element"></div>
                            </label>
                        </div>
                        <span class="error text-danger" id="stripe_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'stripe_fpx') { %>
                    <div class="col-md-12 mt-3 mb-3 stripe_fpx_element_wrapper option-wrapper d-none">
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
                <% } %>
                <% if(payment_option.slug == 'stripe_ideal' ) { %>
                    <div class="col-md-12 mt-3 mb-3 stripe_ideal_element_wrapper option-wrapper d-none">
                        <label for="ideal-bank-element">
                            iDEAL Bank
                        </label>
                        <div class="form-control">
                            <div id="ideal-bank-element">
                              <!-- A Stripe Element will be inserted here. -->
                            </div>
                        </div>

                        <span class="error text-danger"id="error-message"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'yoco') { %>
                    <div class="col-md-12 mt-3 mb-3 yoco_element_wrapper option-wrapper d-none">
                        <div class="form-control">
                            <div id="yoco-card-frame">
                            <!-- Yoco Inline form will be added here -->
                            </div>
                        </div>
                        <span class="error text-danger" id="yoco_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'checkout') { %>
                    <div class="col-md-12 mt-3 mb-3 checkout_element_wrapper option-wrapper d-none">
                        <div class="form-control card-frame">
                            <!-- form will be added here -->
                        </div>
                        <span class="error text-danger" id="checkout_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'payphone') { %>
                    <div id="pp-button"></div>
                <% } %>
            <% } %>
        <% }); %>
    <% } %>
</script>

@endsection

@section('script-bottom-js')
<script defer type="text/javascript"  src="{{ asset('js/giftCard/giftcardFrontend.js') }}"></script>
@endsection
@section('script')

@if(in_array('razorpay',$client_payment_options))
<script type="text/javascript" src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif
@if(in_array('stripe',$client_payment_options) || in_array('stripe_fpx',$client_payment_options) || in_array('stripe_oxxo',$client_payment_options)  || in_array('stripe_ideal',$client_payment_options))
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endif
@if(in_array('stripe_oxxo',$client_payment_options))
<script>
var stripe_oxxo_publishable_key = '{{ $stripe_oxxo_publishable_key }}';
</script>
@endif
@if(in_array('stripe_ideal',$client_payment_options))
<script>
var stripe_ideal_publishable_key = '{{ $stripe_ideal_publishable_key }}';
</script>
@endif
@if(in_array('yoco',$client_payment_options))
<script src="https://js.yoco.com/sdk/v1/yoco-sdk-web.js"></script>
<script type="text/javascript">
    var sdk = new window.YocoSDK({
        publicKey: yoco_public_key
    });
    var inline='';
</script>
@endif
@if(in_array('checkout',$client_payment_options))
<script src="https://cdn.checkout.com/js/framesv2.min.js"></script>
@endif
@if(in_array('payphone',$client_payment_options))
<script src="https://pay.payphonetodoesposible.com/api/button/js?appId={{$payphone_id}}"></script>
@endif
@if(in_array('khalti',$client_payment_options))
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
@endif
<script type="text/javascript">
    var stripe_fpx = '';
    var fpxBank = '';
    var idealBank = {};
    var create_viva_wallet_pay_url = "{{route('vivawallet.pay')}}";
    var create_mvodafone_pay_url = "{{route('mvodafone.pay')}}";
    var create_konga_hash_url = "{{route('kongapay.createHash')}}";
    var create_dpo_subscription = "{{route('dpo.subscription')}}";
    var create_payphone_url = "{{route('payphone.createHash')}}";
    var create_easypaisa_hash_url = "{{route('easypaisa.createHash')}}";
    var create_windcave_hash_url = "{{route('windcave.createHash')}}";
    var create_paytech_hash_url = "{{route('paytech.createHash')}}";
    var create_flutterwave_url = "{{route('flutterwave.createHash')}}";
    var create_ccavenue_url = "{{route('ccavenue.pay')}}";
    var post_payment_via_gateway_url = "{{route('payment.gateway.postPayment', ':gateway')}}";
    var subscription_payment_options_url = "{{route('user.subscription.plan.select', ':id')}}";
    var user_subscription_purchase_url = "{{route('user.subscription.plan.purchase', ':id')}}";
    var user_subscription_cancel_url = "{{route('user.subscription.plan.cancel', ':id')}}";
    var payment_stripe_url = "{{route('payment.stripe')}}";
    var payment_retrive_stripe_fpx_url = "{{url('payment/retrieve/stripe_fpx')}}";
    var payment_create_stripe_fpx_url = "{{url('payment/create/stripe_fpx')}}";
    var payment_create_stripe_oxxo_url = "{{url('payment/create/stripe_oxxo')}}";
    var payment_retrive_stripe_ideal_url = "{{url('payment/retrieve/stripe_ideal')}}";
    var payment_create_stripe_ideal_url = "{{url('payment/create/stripe_ideal')}}";
    var payment_paystack_url = "{{route('payment.paystackPurchase')}}";
    var payment_yoco_url = "{{route('payment.yocoPurchase')}}";
    var payment_paylink_url = "{{route('payment.paylinkPurchase')}}";
    var payment_checkout_url = "{{route('payment.checkoutPurchase')}}";
    var payment_khalti_url = "{{route('payment.khaltiVerification')}}";
    var payment_khalti_complete_purchase = "{{route('payment.khaltiCompletePurchase')}}";
    var check_active_subscription_url = "{{route('user.subscription.plan.checkActive', ':id')}}";

    $(document).on('change', '#subscription_payment_methods input[name="subscription_payment_method"]', function() {
        var method = $(this).val();
        var code = method.replace('radio-', '');

        if (code != '') {
            $("#subscription_payment_methods .option-wrapper").addClass('d-none');
            $("#subscription_payment_methods ."+code+"_element_wrapper").removeClass('d-none');
        } else {
            $("#subscription_payment_methods .option-wrapper").addClass('d-none');
        }

        if (code == 'yoco') {
            // $("#subscription_payment_methods .yoco_element_wrapper").removeClass('d-none');
            // Create a new dropin form instance

            var yoco_amount_payable = $("input[name='subscription_amount']").val();
            inline = sdk.inline({
                layout: 'field',
                amountInCents:  yoco_amount_payable * 100,
                currency: 'ZAR'
            });
            // this ID matches the id of the element we created earlier.
            inline.mount('#yoco-card-frame');
        }
        // else {
        //     $("#subscription_payment_methods .yoco_element_wrapper").addClass('d-none');
        // }

        if (code == 'checkout') {
            // $("#subscription_payment_methods .checkout_element_wrapper").removeClass('d-none');
            Frames.init(checkout_public_key);
        }
        // else {
        //     $("#subscription_payment_methods .checkout_element_wrapper").addClass('d-none');
        // }
    });

    $(document).on('click', '.cancel-subscription-link', function(){
        var id = $(this).attr('data-id');
        $('#cancel-subscription-form').attr('action', user_subscription_cancel_url.replace(":id", id));
    });
</script>
@if(in_array('kongapay',$client_payment_options))
<script src="https://kongapay-pg.kongapay.com/js/v1/production/pg.js"></script>
@endif
@if(in_array('flutterwave',$client_payment_options))
<script type="text/javascript" src="https://checkout.flutterwave.com/v3.js"></script>
@endif
<script type="text/javascript" src="{{asset('js/developer.js')}}"></script>
<script type="text/javascript" src="{{asset('js/payment.js')}}"></script>



@endsection