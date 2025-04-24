@extends('layouts.store', ['title' => 'My Subscriptions'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/azul.css')}}" rel="stylesheet" type="text/css" />

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
            <div class="col-lg-3 col-md-3">
                <div class="account-sidebar"><a class="popup-btn">my account</a></div>
                @include('layouts.store/profile-sidebar')
            </div>
            <div class="col-lg-9 col-md-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2>{{ __('My Subscriptions') }}</h2>
                        </div>

                        <div class="row">
                            @if(!empty($subscription))
                            <div class="col-12 mb-4">
                                <div class="card subscript-box">
                                    @if( (empty($subscription->cancelled_at)) || (!empty($subscription->cancelled_at)) && ($subscription->cancelled_at >= $now))
                                    <div class="row align-items-center mb-2">
                                        <div class="col-sm-3 text-center">
                                            <div class="gold-icon">
                                                <img src="{{$subscription->plan->image['proxy_url'].'100/100'.$subscription->plan->image['image_path']}}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-sm-9 mt-3 mt-sm-0">
                                            <div class="row align-items-end border-left-top pt-sm-0 pt-2">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h3 class="d-inline-block"><b>{{ $subscription->plan->title }}</b></h3>
                                                        <span class="plan-price">{{ Session::get('currencySymbol') . ($subscription->subscription_amount * $clientCurrency->doller_compare) }} / {{ $subscription->frequency }}</span>
                                                    </div>
                                                    <p>{{ $subscription->plan->description }}</p>
                                                    <?php /* ?><ul class="mb-3">
                                                        @foreach($subscription->features as $feature)
                                                            <li><i class="fa fa-check"></i> {{ $feature->feature->title }}</li>
                                                        @endforeach
                                                    </ul><?php */ ?>
                                                </div>

                                                <div class="col-sm-6 form-group mb-0">
                                                    <b class="mr-2">
                                                        @if(!empty($subscription->cancelled_at))
                                                            @if( $subscription->end_date >= $now )
                                                                {{ __('Cancels On') }}
                                                            @else
                                                                {{ __('Cancelled On') }}
                                                            @endif
                                                        @else
                                                            @if( $subscription->end_date >= $now )
                                                                {{ __('Upcoming Billing Date') }}
                                                            @else
                                                                {{ __('Expired On') }}
                                                            @endif
                                                        @endif
                                                    </b>
                                                    <span>{{ dateTimeInUserTimeZone($subscription->end_date, $timezone, true, false) }}</span>
                                                </div>
                                                <div class="col-sm-6 mb-0 text-center text-sm-right">
                                                    @if( $subscription->end_date >= $now )
                                                        @if($subscription->plan->status == 1)
                                                            <a class="btn btn-solid subscribe_btn" href="javascript:void(0)" data-toggle="modal" data-id="{{ $subscription->plan->slug }}">{{ __('Pay now') }} ({{ Session::get('currencySymbol') . ($subscription->plan->price * $clientCurrency->doller_compare) }})</a>
                                                        @endif
                                                        @if(empty($subscription->cancelled_at))
                                                            <a class="cancel-subscription-link btn btn-solid" href="#cancel-subscription" data-toggle="modal" data-id="{{ $subscription->slug }}">{{ __('Cancel') }}</a>
                                                        @endif
                                                    @else
                                                        @if($subscription->plan->status == 1)
                                                            <a class="btn btn-solid subscribe_btn" href="javascript:void(0)" data-toggle="modal" data-id="{{ $subscription->plan->slug }}">{{ __('Renew') }} ({{ Session::get('currencySymbol') . ($subscription->plan->price * $clientCurrency->doller_compare) }})</a>
                                                        @endif
                                                    @endif
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
							@php
							$subscription_plans_user = clone $subscription_plans;
							$subscription_plans_user = $subscription_plans_user->where('type_id', 1)->orwhere('type_id', null)->get();
							@endphp
							<div class="col-md-12 mb-4">
								<div class="card subscript-box">
								<h3>User Subscriptions</h3>
								<div class="row">
                                    @if($subscription_plans_user->isNotEmpty())
                                        @foreach($subscription_plans_user as $plan)
                                        <div class="col-md-3 col-sm-6 mb-3 mb-md-2">
                                            <div class="pricingtable">
                                                <div class="gold-icon position-relative">
                                                    <img src="{{ $plan->image['proxy_url'].'100/100'.$plan->image['image_path'] }}">
                                                    <div class="pricingtable-header position-absolute">
                                                        <div class="price-value"> <b>{{ Session::get('currencySymbol') . ($plan->price * $clientCurrency->doller_compare) }}</b> <span class="month">{{ $plan->frequency }}</span> </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-2">
                                                <h3 class="heading mt-0 mb-2"><b>{{ __($plan->title) }}</b></h3>
                                                <div class="pricing-content">
                                                    <p>{{ __($plan->description) }}</p>
                                                </div>
                                                <ul class="mb-3">
                                                    @foreach($plan->features as $feature)
                                                        <li><i class="fa fa-check"></i>{{ __($feature->percent_value ?? '') }} {{ __($feature->feature->title) }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="pricingtable-purchase">
                                                @if( (isset($subscription->plan->id)) && ($plan->id == $subscription->plan->id) )
                                                    <button class="btn btn-solid black-btn disabled w-100">{{ __('Subscribed') }}</button>
                                                @else
                                                    <button class="btn btn-solid w-100 subscribe_btn" data-id="{{ $plan->slug }}">{{ __('Subscribe') }}</button>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                    	<h5>No User subscription found</h5>
                                    @endif
                                    </div>
								</div>
                           </div>

                           	@php
                           	$subscription_plans_meal = clone $subscription_plans;
                           	$subscription_plans_meal = $subscription_plans_meal->where('type_id', '=' ,2)->get();
                           	@endphp
                           	<div class="col-md-12 mb-4">
                           		<div class="card subscript-box">
                           		<h3>Meal Subscriptions</h3>
                           		<div class="row p-2">
                                    @if($subscription_plans_meal->isNotEmpty())
                                        @foreach($subscription_plans_meal as $plan)
                                        <div class="col-md-3 col-sm-6 mb-3 mb-md-2">
                                            <div class="pricingtable">
                                                <div class="gold-icon position-relative">
                                                    <img src="{{ $plan->image['proxy_url'].'100/100'.$plan->image['image_path'] }}">
                                                    <div class="pricingtable-header position-absolute">
                                                        <div class="price-value"> <b>{{ Session::get('currencySymbol') . ($plan->price * $clientCurrency->doller_compare) }}</b> <span class="month">{{ $plan->frequency }}</span> </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-2">
                                                <h3 class="heading mt-0 mb-2"><b>{{ __($plan->title) }}</b></h3>
                                                <div class="pricing-content">
                                                    <p>{{ __($plan->description) }}</p>
                                                </div>
                                                <ul class="mb-3">
                                                    @foreach($plan->subscriptionCategory as $category)
                                                        <li><i class="fa fa-check"></i> {{ __($category->category->slug) }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="pricingtable-purchase">
                                                @if( (isset($subscription->plan->id)) && ($plan->id == $subscription->plan->id) )
                                                    <button class="btn btn-solid black-btn disabled w-100">{{ __('Subscribed') }}</button>
                                                @else
                                                    <button data-id="{{$plan->slug}}" class="btn btn-solid w-100 meal_subscribe_btn">{{ __('Subscribe') }}</button>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                    	<h5>No Meal subscription found</h5>
                                    @endif
                                    </div>
                                </div>
                            </div>
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

<div class="modal fade" id="subscription_payment" tabindex="-1" aria-labelledby="subscription_paymentLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title text-17 mb-0 mt-0" id="subscription_paymentLabel">{{ __('Subscription') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="" id="subscription_payment_form">
        @csrf
        @method('POST')
        <div>
            <input type="hidden" name="email" id="email" value="{{ Auth::user()->email }}">
            <input type="hidden" name="subscription_id" id="subscription_id" value="">
            <input type="hidden" name="subscription_amount" id="subscription_amount" value="">
            <input type="hidden" name="card_last_four_digit" id="card_last_four_digit" value="">
            <input type="hidden" name="card_expiry_month" id="card_expiry_month" value="">
            <input type="hidden" name="card_expiry_year" id="card_expiry_year" value="">
        </div>
        <div class="modal-body pb-0">
            <div class="form-group">
                <h5 class="text-17 mb-2" id="subscription_title"></h5>
                <div class="mb-2"><span id="subscription_price"></span> / <span id="subscription_frequency"></span></div>
            </div>
            <div class="form-group">
                <div class="text-17 mt-2">{{ __('Features Included') }}:
                    <div class="mt-2" id="features_list"></div>
                </div>
            </div>
            <hr class="mb-1" />
            <div class="payment_response">
                <div class="alert p-0 m-0" role="alert"></div>
            </div>
            <h5 class="text-17 mb-2">{{ __('Debit From') }}</h5>
            <div class="form-group" id="subscription_payment_methods">
            </div>
        </div>
        <div class="modal-footer d-block text-center">
            <div class="row">
                <div class="col-sm-12 p-0 d-flex justify-space-around">
                    <button type="button" class="btn btn-block btn-solid mr-1 mt-2 subscription_confirm_btn">{{ __('Pay') }}</button>
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
                    <input type="radio" name="subscription_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.slug %>" data-payment_option_id="<%= payment_option.id %>">
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

                <% if(payment_option.slug == 'plugnpay') { %>
                    <div class="col-md-12 mt-3 mb-3 plugnpay_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="plugnpay-card-element" placeholder="Enter card Number"  />
                            </div>
                            <div class="col-3">
                                <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="5"  id="plugnpay-date-element" placeholder="MM/YY"  />
                            </div>
                            <div class="col-3">
                                <input type="password" max="3" style=" border-left: none;"  class="form-control" id="plugnpay-cvv-element" placeholder="CVV"  />
                            </div>
                        </div>

                        <span class="error text-danger" id="plugnpay_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'azulpay') { %>
                    <div class="col-md-12 mt-3 mb-3 azulpay_element_wrapper option-wrapper d-none">

                         <div class="tab">
    <a class="tablinks active" onclick="clickHandle(event, 'Add-Card')" href="javascript:void(0);">Add Card</a>
    <a class="tablinks" onclick="clickHandle(event, 'Card-List')" href="javascript:void(0);">Card List</a>
  </div>

  <div id="Add-Card" class="tabcontent show" style="display:block">
     <div class="row no-gutters">
                            <div class="col-6">
                                <input type="text"  maxlength="16" style=" border-right: none;" class="form-control demoInputBox" id="azul-card-element" placeholder="Enter Card Number" />
                            </div>
                            <div class="col-3">
                                <input type="text" style=" border-left: none; border-right: none;" class="form-control demoInputBox" onkeyup="addSlashes(this)" maxlength=7  id="azul-date-element" placeholder="MM/YYYY" />
                            </div>
                            <div class="col-3">
                                <input type="password" max="4" style=" border-left: none;"  class="form-control demoInputBox" id="azul-cvv-element" placeholder="CVV" />
                            </div>
                        </div>
<div class="row">
<div class="col-md-4 save-card-custom">
                     <input type="checkbox" name="save_card" class="form-check-input" id="azul-save_card" value="1">
                                    <label for="azul-save_card" class="">{{ __('Save Card') }}</label>
            </div>
</div>
                        <span class="error text-danger" id="azul_card_error"></span>
  </div>
  <div id="Card-List" class="tabcontent">
  </div>
                   </div>
               <% } %>

               <% if(payment_option.slug == 'powertrans') { %>
                <div class="col-md-12 mt-3 mb-3 powertrans_element_wrapper option-wrapper d-none">
                    <div class="row no-gutters">
                        <div class="col-6">
                            <input type="number" min="16" maxlength="16" style=" border-right: none;" class="form-control" id="card-element-powertrans" placeholder="Enter card Number" required
                            oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-3">
                            <input type="number" style=" border-left: none; border-right: none;" class="form-control" maxLength="4"  id="date-element-powertrans" placeholder="YYMM" required
                            oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"/>
                        </div>
                        <div class="col-3">
                            <input type="password" maxLength="4" style=" border-left: none;"  class="form-control" id="cvv-element-powertrans" placeholder="CVV" required />
                        </div>
                    </div>

                    <span class="error text-danger" id="card_error_powertrans"></span>
                </div>
            <% } %>

            <% } %>
        <% }); %>
    <% } %>
</script>

@endsection

@section('script')
<script src="{{asset('js/credit-card-validator.js')}}"></script>
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
@if (in_array('mastercard', $client_payment_options))
    <script src="https://{{mastercardGateway()}}/static/checkout/checkout.min.js"></script>
@endif
<script type="text/javascript">
    var stripe_fpx = '';
    var fpxBank = '';
    var idealBank = {};
      var ajaxCall = 'ToCancelPrevReq';
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
    var create_mtn_momo_token = "{{route('mtn.momo.createToken')}}";
    var payment_plugnpay_url = "{{route('payment.plugnpay.beforePayment')}}";
	var payment_azulpay_url = "{{route('payment.azulpay.beforePayment')}}";
	var payment_mpesa_safari_url = "{{route('mpesasafari.pay')}}";
	var user_cards_url = "{{ route('payment.azulpay.getCards') }}";
    var payment_obo_url = "{{route('obo.pay')}}";
    var powertrans_payment_url = "{{ route('powertrans.payment') }}";
    var data_trans_url = "{{route('payment.payByDataTrans')}}";
    var pesapal_payment_url = "{{ route('pesapal.payment') }}";
    var livee_payment_url = "{{route('livee.pay')}}";
    var mastercard_create_session_url = "{{route('payment.mastercard.createSession')}}";
    var payment_hitpay_url="{{ route('make.hitpay.payment') }}";

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
@if(in_array('data_trans',$client_payment_options))
    <script src="{{ $data_trans_script_url }}"></script>
@endif
<script type="text/javascript" src="{{asset('js/developer.js')}}"></script>
<script type="text/javascript" src="{{asset('js/payment.js')}}"></script>
<script>
function addSlashes (element) {
    let ele = document.getElementById(element.id);
    ele = ele.value.split('/').join('');    // Remove slash (/) if mistakenly entered.
    if(ele.length < 4 && ele.length > 0){
        let finalVal = ele.match(/.{1,2}/g).join('/');

        document.getElementById(element.id).value = finalVal;
    }
}


$(document).delegate(".meal_subscribe_btn", "click", function (e) {
        e.preventDefault();
        var sub_id = $(this).attr('data-id');
        $.ajax({
            type: "get",
            dataType: "json",
            url: check_active_subscription_url.replace(":id", sub_id),
            success: function (response) {
                if (response.status == "Success") {
                   route = "{{route('user.mealSubscription',':id')}}"
                   window.location.href = route.replace(':id', sub_id)
                }
            },
            error: function (error) {
                var response = $.parseJSON(error.responseText);
                let error_messages = response.message;
                $("#error_response .message_body").html(error_messages);
                $("#error_response").modal("show");
            }
        });
    });
</script>

@endsection
