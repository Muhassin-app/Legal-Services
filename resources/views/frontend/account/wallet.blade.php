@extends('layouts.store', ['title' => 'My Wallet'])
@section('css')
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
</style>
@endsection
@section('content')
@php
$user = Auth::user();
$timezone = $user->timezone;
$user_wallet_balance = $user->balanceFloat ? ($user->balanceFloat * ($clientCurrency->doller_compare ?? 1) ) : 0;
$additionalPreference = getAdditionalPreference(['is_token_currency_enable','token_currency']);
@endphp
<link href="{{asset('assets/css/azul.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .productVariants .firstChild {
        min-width: 150px;
        text-align: left !important;
        border-radius: 0% !important;
        margin-right: 10px;
        cursor: default;
        border: none !important;
    }
    .product-right .color-variant li,
    .productVariants .otherChild {
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 10px;
        cursor: pointer;
        border: 1px solid #f7f7f7;
        text-align: center;
    }
    .productVariants .otherSize {
        height: auto !important;
        width: auto !important;
        border: none !important;
        border-radius: 0%;
    }
    .product-right .size-box ul li.active {
        background-color: inherit;
    }
    .login-page .theme-card .theme-form input {
        margin-bottom: 5px;
    }
    .invalid-feedback {
        display: block;
    }
    .box-info table tr:first-child td {
        padding-top: .85rem;
    }
    #wallet_transfer_error_msg{
        display: none;
    }
    .error-msg {
        color: #F00;
        background-color: #FFF;
    }
</style>
<section class="section-b-space">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="text-sm-left" id="wallet_response">
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <span>{!! \Session::get('success') !!}</span>
                        </div>
                        @php
                            \Session::forget('success');
                        @endphp
                    @endif
                    @if (\Session::has('error'))
                        <div class="alert alert-danger">
                            <span>{!! \Session::get('error') !!}</span>
                        </div>
                        @php
                            \Session::forget('error');
                        @endphp
                    @endif
                    <div class="message d-none">
                        <div class="alert p-0"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row my-md-3">
            <div class="col-lg-3 profile-sidebar">
                <div class="account-sidebar"><a class="popup-btn">{{__('My Account')}}</a></div>
                <div class="dashboard-left mb-3">
                    <div class="collection-mobile-back">
                        <span class="filter-back d-lg-none d-inline-block">
                            <i class="fa fa-angle-left" aria-hidden="true"></i>{{__('Back')}}
                        </span>
                        </div>
                    @include('layouts.store/profile-sidebar')
                </div>
            </div>
            <div class="col-lg-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2 class="">{{__('My Wallet')}}</h2>
                        </div>
                        <div class="box-account box-info">
                            <div class="card-box mb-0">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-md-left text-center mb-md-0 mb-4">
                                        <h5 class="text-17 mb-2 mt-0">{{__('Available Balance')}}</h5>
                                        <div class="text-36">{{Session::get('currencySymbol')}}<span class="wallet_balance">{{decimal_format(Auth::user()->balanceFloat * ( $clientCurrency->doller_compare ?? 1))}}</span></div>
                                    </div>
                                    <div class="col-md-4 text-md-left text-center">
                                        @if( $additionalPreference["is_token_currency_enable"])
                                        <h5 class="text-17 mb-2 mt-0">{{__('Token Balance')}}</h5>
                                        <div class="text-36 mb-md-0 mb-4"><span class="wallet_balance">{!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{getInToken(decimal_format(Auth::user()->balanceFloat * ( $clientCurrency->doller_compare ?? 1)))}}</span></div>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-md-right text-center">
                                        <button type="button" class="btn btn-solid" id="topup_wallet_btn" data-toggle="modal" data-target="#topup_wallet">{{__('Topup Wallet')}}</button>
                                        <button type="button" class="btn btn-solid" id="transfer_wallet_btn" data-toggle="modal" data-target="#transfer_wallet">{{__('Transfer Funds')}}</button>
                                    </div>
                                </div>
                            </div>
                            <h6>{{__('Transaction History')}}</h6>
                            <div class="card-box" id="wallet_transactions_history">
                                <div class="table-responsive table-responsive-xs">
                                  <table class="table wallet-transactions border">
                                    <thead>
                                        <tr class="table-head">
                                            <th>{{__('Date')}}</th>
                                            <th>{{__('Description')}}</th>
                                            <th class="text-right" style="white-space:nowrap"><span class="text-success">{{__('Credit')}}</span> / <span class="text-danger">{{__('Debit')}}</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($user_transactions as $ut)
                                    @php
                                    $reason = json_decode($ut->meta);
                                    $amount = ($ut->amount / 100) * ( $clientCurrency->doller_compare ?? 1);
                                    @endphp
                                    <tr>
                                        <td> {{dateTimeInUserTimeZone($ut->created_at, $timezone)}}</td>
                                        <td  class="name_">{!! $reason->description ?? $reason[0]!!}</td>
                                        <td class="text-right {{ ($ut->type == 'deposit') ? 'text-success' : (($ut->type == 'withdraw') ? 'text-danger' : '') }}">
                                            @if($ut->type == 'deposit')
                                            {{ Session::get('currencySymbol').decimal_format($amount)}}
                                            @else
                                            <b>{{$additionalPreference["is_token_currency_enable"] ? getInToken(decimal_format($amount)) : Session::get('currencySymbol').decimal_format($amount)}}</b>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td align="center" colspan="4">{{__('No Transaction History Exists')}}</td></tr>
                                    @endforelse
                                    </tbody>
                                  </table>
                                </div>
                                <div class="pagination pagination-rounded justify-content-end mb-0">
                                    @if(!empty($user_transactions))
                                        {{ $user_transactions->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="LiveesModal" tabindex="-1" aria-labelledby="LiveesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title text-center" id="exampleModalLabel">Livees User Details</h3>
          <button type="button" class="btn livees-btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times-circle-o fa-2x" aria-hidden="true"></i></button>
        </div>
        <div class="livees-modal-body">
          ...
        </div>
        <div class="modal-footer">

        </div>
      </div>
    </div>
  </div>

<div class="modal fade wallet_money" id="add-money" tabindex="-1" aria-labelledby="add-moneyLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title" id="add-moneyLabel">{{__('Pay-Out')}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="">
            <div class="form-group">
                <label for="">{{__('Account Number')}}</label>
                <input class="form-control" type="text" placeholder="Account Number">
            </div>
            <div class="form-group">
                <label for="">{{__('Account Name')}}</label>
                <input class="form-control" type="text" placeholder="Account Name">
            </div>
            <div class="form-group">
                <label for="">{{__('Bank Name')}}</label>
                <input class="form-control" type="text" placeholder="Bank Name">
            </div>
            <div class="form-group">
                <label for="">{{getNomenclatureName('IFSC Code', true)}}</label>
                <input class="form-control" type="text" placeholder="{{getNomenclatureName('IFSC Code', true)}}">
            </div>
            <button type="button" class="btn btn-solid w-100 mt-2" data-dismiss="modal">{{__('Close')}}</button>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="topup_wallet" tabindex="-1" aria-labelledby="topup_walletLabel" >
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title text-17 mb-0 mt-0" id="topup_walletLabel">{{__('Available Balance')}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="" id="wallet_topup_form">
        @csrf
        @method('POST')
        <div class="modal-body pb-0">
            <div class="form-group">
                <div class="text-36">{{Session::get('currencySymbol')}}<span class="wallet_balance">{{decimal_format(Auth::user()->balanceFloat * ( $clientCurrency->doller_compare ?? 1))}}</span></div>
            </div>
            <div class="form-group">
                <h5 class="text-17 mb-2">{{__('Topup Wallet')}}</h5>
            </div>
            <div class="form-group">
                <label for="wallet_amount">{{__('Amount')}}</label>
                <input class="form-control" name="wallet_amount" id="wallet_amount" type="text" placeholder="{{__('Enter Amount')}}">
                <span class="error-msg" id="wallet_amount_error"></span>
            </div>
            <div class="form-group">
                <div><label for="custom_amount">{{__('Recommended')}}</label></div>
                <button type="button" class="btn btn-solid mb-2 custom_amount">+10</button>
                <button type="button" class="btn btn-solid mb-2 custom_amount">+20</button>
                <button type="button" class="btn btn-solid mb-2 custom_amount">+50</button>
            </div>
            <hr class="mt-0 mb-1" />
            <div class="payment_response">
                <div class="alert p-0 m-0" role="alert"></div>
            </div>
            <h5 class="text-17 mb-2">{{__('Debit From')}}</h5>
            <div class="form-group" id="wallet_payment_methods">
            </div>
            <span class="error-msg" id="wallet_payment_methods_error"></span>
        </div>
        <div class="modal-footer d-block text-center">
            <div class="row">
                <div class="col-sm-12 p-0 d-flex justify-space-around">
                    <button type="button" class="btn btn-block btn-solid mr-1 mt-2 topup_wallet_confirm">{{__('Topup Wallet')}}</button>
                    <button type="button" class="btn btn-block btn-solid ml-1 mt-2" data-dismiss="modal">{{__('Cancel')}}</button>
                </div>
            </div>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="transfer_wallet" tabindex="-1" aria-labelledby="transfer_walletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header border-bottom">
          <h5 class="modal-title text-17 mb-0 mt-0" id="transfer_walletLabel">{{__('Transfer Funds')}}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="" id="wallet_transfer_form">
          @csrf
          @method('POST')
          <div class="modal-body pb-0">
              <div class="form-group">
                <h5 class="text-17 mb-2">{{__('Available Balance')}}</h5>
              </div>
              <div class="form-group">
                  <div class="text-36">{{Session::get('currencySymbol')}}<span class="wallet_balance">{{decimal_format($user_wallet_balance)}}</span></div>
              </div>

              @if($user_wallet_balance <= 0)
                <div class="alert alert-danger">
                    <span>{{ __('Insufficient funds in wallet') }}</span>
                </div>
              @else
              <div id="error_dev"></div>
              <div class="form-group" id="wallet_transfer_amountInput">
                <label for="wallet_transfer_amount">{{__('Amount to transfer')}}</label>
                <input class="form-control" name="wallet_transfer_amount" id="wallet_transfer_amount" type="text" placeholder="{{__('Enter Amount')}}">
                <span class="invalid-feedback" role="alert">
                    <strong></strong>
                </span>
              </div>
              <div class="form-group" id="wallet_transfer_userInput">
                <label for="wallet_transfer_user">{{__('Transfer to')}}</label>
                <input class="form-control" name="wallet_transfer_user" id="wallet_transfer_user" type="text" placeholder="{{__('Enter Email or Phone Number with Country Code')}}">
                <span class="invalid-feedback" role="alert">
                    <strong></strong>
                </span>
                <span class="valid-feedback" role="alert">
                    <strong></strong>
                </span>
              </div>
              <div class="form-group" id="user_profile">

              </div>
              <span class="error-msg pl-0" id="wallet_transfer_error_msg"></span>
              @endif
          </div>
          <div class="modal-footer d-block text-center">
              <div class="row">
                  <div class="col-sm-12 p-0 d-flex justify-space-around">
                    @if($user_wallet_balance > 0)
                      <button type="button" class="btn btn-block btn-solid mr-1 mt-2 transfer_wallet_confirm">{{__('Confirm')}}</button>
                      <button type="button" class="btn btn-block btn-solid ml-1 mt-2" data-dismiss="modal">{{__('Cancel')}}</button>
                    @endif
                  </div>
              </div>
          </div>
        </form>
      </div>
    </div>
</div>
<script type="text/template" id="user_profile_template">
    <% if(profile != '') { %>
        <label>
            <span class="">
                <img class="rounded-circle" src="<%= profile.image['image_fit'] %>100/100<%= profile.image['image_path'] %>" alt="" width="40" height="40">
            </span>
            <span class="ml-1"><b><%= profile.name %></b></span>
        </label>
    <% } %>
</script>
<script type="text/template" id="payment_method_template">
    <% if(payment_options == '') { %>
        <h6>{{__('Payment Options Not Avaialable')}}</h6>
    <% }else{ %>
        <% _.each(payment_options, function(payment_option, k){%>
            <% if( (payment_option.slug != 'cash_on_delivery') && (payment_option.slug != 'loyalty_points') ) { %>
                <label class="radio mt-2">
                    <%= payment_option.title %>
                    <input type="radio" name="wallet_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.slug %>" data-payment_option_id="<%= payment_option.id %>">
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
                <% if(payment_option.slug == 'totalpay') { %>
                    <div class="col-md-12 mt-3 mb-3 totalpay_element_wrapper option-wrapper d-none">
                        <form action=""></form>
                        <span class="error text-danger" id="totalpay__error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'yoco') { %>
                    <div class="col-md-12 mt-3 mb-3 yoco_element_wrapper option-wrapper d-none">
                        <div class="form-control">
                            <label class="pb-1 mb-0">
                            <div id="yoco-card-frame">
                                    <!-- Yoco Inline form will be added here -->
                                    </div>
                            </label>
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
                        <div class="row no-gutters mb-2">
                                    <div class="col-12">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-name-element" placeholder="Enter card holder name" />
                                    </div>
                                    <div class="col-6">
                                        <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="plugnpay-card-element" placeholder="Enter card Number" />
                                    </div>
                                    <div class="col-3">
                                        <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="5"  id="plugnpay-date-element" placeholder="MM/YY" />
                                    </div>
                                    <div class="col-3">
                                        <input type="password" max="3" style=" border-left: none;"  class="form-control" id="plugnpay-cvv-element" placeholder="CVV" />
                                    </div>
                                     <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-addr1-element" placeholder="Enter address"/>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-addr2-element" placeholder="Enter alternate address (optional)" />
                                    </div>
                                    <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-zip-element" placeholder="Enter zip code"/>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-city-element" placeholder="Enter city name"/>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-state-element" placeholder="Enter state code e.g. NY"/>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-country-element" placeholder="Enter country code e.g. US"/>
                                    </div>
                                </div>

                        <span class="error text-danger" id="plugnpay_card_error"></span>
                    </div>
                <% } %>

                <% if(payment_option.slug == 'nmi') { %>
                    <div class="col-md-12 mt-3 mb-3 nmi_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                            <input type="text"  maxlength="16" style=" border-right: none;" class="form-control demoInputBox" id="card-element-nmi" placeholder="Enter Card Number" />
                            </div>
                            <div class="col-3">
                            <input type="text" style=" border-left: none; border-right: none;" class="form-control demoInputBox" onkeyup="addSlashes(this)" maxlength=7  id="date-element-nmi" placeholder="MM/YYYY" />
                            </div>
                            <div class="col-3">
                            <input type="password" max="4" style=" border-left: none;"  class="form-control demoInputBox" id="cvv-element-nmi" placeholder="CVV" />
                            </div>
                            <span class="error text-danger" id="card_error_nmi"></span>
                        </div>
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

                {{-- <% if(payment_option.slug =='livee'){ %>
                    <div class="col-md-12 mt-3 mb-3 powertrans_element_wrapper option-wrapper d-none">
                    <div class="d-flex justify-content-center mt-5">
        <form  action="https://www.livees.net/Checkout/api4" method="POST"  class="d-flex flex-column gap-3">

            <input type="hidden" name="_" value="sa4b4km6c0l9eq7y6od88cnjp62efvr6ix59u5taz2ghw0193" class="form-control">
            <input type="hidden" name="__" value="bj65bih1kzo740snwbru2q9px3v5503fetfdaaegmc64yle58" class="form-control">
            <input type="hidden" name=" postURL" value="{{url('/livee/success')}}" class="form-control">
            <input type="hidden" name=" amt2" value="100" class="form-control">
            <input type="hidden" name="currency" value="BOB" class="form-control">
            <input type="hidden" name="invno" value="   " class="form-control">
            <input type="text" name="name" placeholder="Enter First Name" class="form-control">
            <input type="text" name=" lastname" placeholder="Enter lastname" class="form-control">
            <input type="email" name="email" value="{{Auth::user()->email}}" class="form-control">
            <select name="pais" class="form-control">
            <option value="BO">Bolivia</option>
            <option value="US">Estados Unidos</option>
            </select>
            <input type="text" name="ciudad" value="Santa Cruz de la Sierra" class="form-control">
            <select name="estado_lbl" class="form-select">
            <option value="La Paz">La Paz</option>
            <option value="Santa Cruz">Santa Cruz</option>
                <input type="submit" class="btn btn-primary" value="submit">
            </form>
    </div>
</div>
               <% } %> --}}


            <% } %>
        <% }); %>
    <% } %>
</script>
@endsection
@section('script')
<script src="{{asset('js/credit-card-validator.js')}}"></script>
@include('frontend.account.paymentUrls')
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
<script src="https://cdn.checkout.com/js/framesv2.min.js"></script>
<script type="text/javascript">
    var sdk = new window.YocoSDK({
        publicKey: yoco_public_key
    });
</script>
@endif
@if(in_array('payphone',$client_payment_options))
<script src="https://pay.payphonetodoesposible.com/api/button/js?appId={{$payphone_id}}"></script>
@endif
@if(in_array('khalti',$client_payment_options))
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
@endif
<script type="text/javascript">
    var inline='';
    var powertrans_payment_url = "{{ route('powertrans.payment') }}";
    var pesapal_payment_url = "{{ route('pesapal.payment') }}";
    var payment_mpesa_safari_url = "{{route('mpesasafari.pay')}}";
    var livee_payment_url = "{{route('livee.pay')}}"
    var payment_orangepay_url =  "{{ route('orangepay.initiate.payment') }}";
    var payment_cybersource_url =  "{{ route('cybersource.initiate.payment') }}";
    // var livee_email={{auth()->user()->email}};

    $('#wallet_amount').keypress(function(event) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });
    $(".livees-btn-close").on('click',function(){
        $("#LiveesModal").modal('hide');
    })
    $('.verifyEmail').click(function() {
        verifyUser('email');
    });
    $('.verifyPhone').click(function() {
        verifyUser('phone');
    });
    function verifyUser($type = 'email') {
        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('verifyInformation', Auth::user()->id) }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "type": $type,
            },
            beforeSend: function() {
                if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
            },
            success: function(response) {
                var res = response.result;
            },
            error: function(data) {},
        });
    }
    $(document).delegate(".custom_amount", "click", function(){
        let wallet_amount = $("#wallet_amount").val();
        let amount = $(this).text();
        if(wallet_amount == ''){ wallet_amount = 0; }
        let new_amount = parseInt(amount) + parseInt(wallet_amount);
        $("#wallet_amount").val(new_amount);
    });

    $(document).on('change', '#wallet_payment_methods input[name="wallet_payment_method"]', function() {
        $('#wallet_payment_methods_error').html('');
        var method = $(this).val();
        var code = method.replace('radio-', '');
    	console.log(code)
        if (code != '') {
            $("#wallet_payment_methods .option-wrapper").addClass('d-none');
            $("#wallet_payment_methods ."+code+"_element_wrapper").removeClass('d-none');
        } else {
            $("#wallet_payment_methods .option-wrapper").addClass('d-none');
        }

        if (code == 'yoco') {
            // $("#wallet_payment_methods .yoco_element_wrapper").removeClass('d-none');
            // Create a new dropin form instance

            var yoco_amount_payable = $("input[name='wallet_amount']").val();

            inline = sdk.inline({
                layout: 'field',
                amountInCents:  yoco_amount_payable * 100,
                currency: 'ZAR'
            });
            // this ID matches the id of the element we created earlier.
            inline.mount('#yoco-card-frame');
        }
        // else {
        //     $("#wallet_payment_methods .yoco_element_wrapper").addClass('d-none');
        // }

        if (code == 'checkout') {
            // $("#wallet_payment_methods .checkout_element_wrapper").removeClass('d-none');
            Frames.init(checkout_public_key);
        }
        // else {
        //     $("#wallet_payment_methods .checkout_element_wrapper").addClass('d-none');
        // }
    });

    $(document).on('blur', '#wallet_transfer_user', function() {
        var username = $(this).val();
        if(username != ''){
            ajaxCall = $.ajax({
                type: "post",
                dataType: "json",
                url: "{{ route('wallet.transfer.user.verify') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "username": username,
                },
                beforeSend: function() {
                    if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                        ajaxCall.abort();
                    }
                },
                success: function(response) {
                    if(response.status == 'Success'){
                        $("#wallet_transfer_userInput input").removeClass("is-invalid").addClass('valid');
                        $("#wallet_transfer_userInput span.invalid-feedback").children("strong").text('');
                        // $("#wallet_transfer_userInput span.valid-feedback").children("strong").text(response.message);
                        $("#wallet_transfer_userInput span.invalid-feedback").hide();
                        // $("#wallet_transfer_userInput span.valid-feedback").show();

                        $("#user_profile").html('');
                        let user_profile_template = _.template($('#user_profile_template').html());
                        $("#user_profile").append(user_profile_template({ profile: response.data}));
                    }
                },
                error: function(response) {
                    let error = response.responseJSON;
                    if (response.status === 422) {
                        $("#user_profile").html('');
                        $("#wallet_transfer_userInput input").removeClass("valid").addClass("is-invalid");
                        $("#wallet_transfer_userInput span.invalid-feedback").children("strong").text(error.message);
                        $("#wallet_transfer_userInput span.invalid-feedback").show();
                        $("#wallet_transfer_userInput span.valid-feedback").hide();
                    }
                },
            });
        }
    });

    $(document).on('blur', '#wallet_transfer_amount', function() {
        var amount = $(this).val();
        console.log("amount is : "+amount);
        if((amount <= 0) || (amount > user_wallet_balance)){
            if(amount <= 0){
                var msg = 'Invalid amount';
            }else{
                var msg = wallet_balance_insufficient_msg;
            }
            $("#wallet_transfer_amountInput input").removeClass("valid").addClass("is-invalid");
            $("#wallet_transfer_amountInput span.invalid-feedback").children("strong").text(msg);
            $("#wallet_transfer_amountInput span.invalid-feedback").show();
        }else{
            $("#wallet_transfer_amountInput input").removeClass("is-invalid").addClass('valid');
            $("#wallet_transfer_amountInput span.invalid-feedback").children("strong").text('');
            $("#wallet_transfer_amountInput span.invalid-feedback").hide();
        }
    });

    $(document).on('focus', '#wallet_transfer_form input', function(){
        $("#wallet_transfer_error_msg").text('').hide();
    });



    $(document).on('click', '.transfer_wallet_confirm', function() {
        var _that = $(this);
        var amount = $("#wallet_transfer_amount").val();
        var username = $("#wallet_transfer_user").val();
        if((amount != '') && (username != '')){
            $("#error_dev").hide();
            var is_valid = true;
            $('#wallet_transfer_form input').each(function(index, el) {
                if($(el).hasClass("is-invalid")){
                    $(el).trigger('focus');
                    is_valid = false;
                    return false;
                }
            });
            if(!is_valid){
                return false;
            }

            ajaxCall = $.ajax({
                type: "post",
                dataType: "json",
                url: "{{ route('wallet.transfer.confirm') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "username": username,
                    "amount": amount
                },
                beforeSend: function() {
                    if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                        ajaxCall.abort();
                    }
                },
                success: function(response) {
                    if(response.status == 'Success'){
                        $("#wallet_transfer_error_msg").text('').hide();
                        window.location.reload();
                    }
                },
                error: function(response) {
                    let error = response.responseJSON;
                    if (response.status === 422) {
                        $("#wallet_transfer_error_msg").text(error.message).show();
                    }
                },
            });
        }else{
            var html ='<div class="alert alert-danger"><span>{{ __("All fields are required") }}</span></div>'
            $("#error_dev").html(html).show();
        }
    });

</script>
<script>
    var loadFile = function(event) {
        var output = document.getElementById('output');
        output.src = URL.createObjectURL(event.target.files[0]);
    };
</script>
@if(in_array('kongapay',$client_payment_options))
<script src="https://kongapay-pg.kongapay.com/js/v1/production/pg.js"></script>
@endif
@if(in_array('flutterwave',$client_payment_options))
<script src="https://checkout.flutterwave.com/v3.js"></script>
@endif
@if(in_array('data_trans',$client_payment_options))
    <script src="{{ $data_trans_script_url }}"></script>
@endif
@if (in_array('mastercard', $client_payment_options))
    <script src="https://{{mastercardGateway()}}/static/checkout/checkout.min.js"></script>
@endif
<script type="text/javascript" src="{{asset('js/developer.js')}}"></script>
<script src="{{asset('js/payment.js')}}"></script>

<script>

function addSlashes (element) {

    let ele = document.getElementById(element.id);
    ele = ele.value.split('/').join('');    // Remove slash (/) if mistakenly entered.
    if(ele.length < 4 && ele.length > 0){
        let finalVal = ele.match(/.{1,2}/g).join('/');

        document.getElementById(element.id).value = finalVal;
    }
}
</script>

@endsection
