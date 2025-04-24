@switch($client_preference_detail->business_type)
@case('taxi')
<?php $ordertitle = 'Rides';
 $hidereturn = 1;  ?>
@break
@default
<?php $ordertitle = 'Orders';
 $hidereturn = 0;
?>

@endswitch
@php
$orderTitles = [
    'Active' => "Active ",
    'Past' => "Past ",
    'Rejected/Cancel' => "Rejected/Cancel "
];

$clientData = \App\Models\Client::select('socket_url')->first();

if($is_service_product_price_from_dispatch_forOnDemand == 1){
    $hidereturn = 1;
    $orderTitles = [
        'Active' => "Confirmed ",
        'Past'   =>  "Done ",
        'Rejected/Cancel' => " Declined "
    ];
}
@endphp
@extends('layouts.store', ['title' => __('My '.getNomenclatureName($ordertitle, true))])
@section('css')
<link href="{{asset('assets/css/azul.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }

    input:invalid,
    input:out-of-range {
        border-color: hsl(0, 50%, 50%);
        background: hsl(0, 50%, 90%);
    }

    .error {
        font-size: 10px;
        color: red;
    }

    .btn.btn-solid {
        padding: 6px 19px;
        margin: 2px;
    }

    li.bg-txt i {
        font-size: 15px;
    }

    label.rating-star.cancel_order, .rating-star.request_cancel_order, .extend-order {
        position: relative;
        left: 0px;
        top: 4px;
        background: #a22c7f;
        color: #fff;
        font-weight: 600;
        font-size: 10px;
        padding: 5px 10px 4px 10px;
        text-transform: uppercase;
    }
    .single-cancel-order {
        left: 0px !important;
        top: 0px !important;
    }

    .schedule_slot {
        left: 0px !important;
        top: 0px !important;
    }

    .rental_return, .rental_stop {
        position: relative;
        left: 0px;
        top: 4px;
        background: #a22c7f;
        color: #fff;
        font-weight: 600;
        font-size: 10px;
        padding: 5px 10px 4px 10px;
        text-transform: uppercase;

    }

    .single-cancel-order {
        left: 0px !important;
        top: 0px !important;
    }
    .service_product h6 {
    max-width: 120px;
    display: inline-block;
    width: 100%;
    }


    .order_vender_product{
        position: relative;
    }
    .order_vender_product li label {
    padding-left: 80px!important;
    display: block!important;
    width: 170px!important;
    text-align: left;
}
.order_vender_product li:first-child {
    position: absolute!important;
    top: 0!important;
    left: 0!important;
}
.order_vender_product li .schedule_slot span {
    font-size: 10px;
    line-height: 1;
    color: var(--theme-deafult);
}

</style>
@endsection
@section('content')
@php
$timezone = Auth::user()->timezone;
@endphp

<style type="text/css">
    .productVariants .firstChild {
        min-width: 150px;
        text-align: left !important;
        border-radius: 0 !important;
        margin-right: 10px;
        cursor: default;
        border: none !important
    }

    .product-right .color-variant li,
    .productVariants .otherChild {
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 10px;
        cursor: pointer;
        border: 1px solid #f7f7f7;
        text-align: center
    }

    .productVariants .otherSize {
        height: auto !important;
        width: auto !important;
        border: none !important;
        border-radius: 0
    }

    .product-right .size-box ul li.active {
        background-color: inherit
    }

    .login-page .theme-card .theme-form input {
        margin-bottom: 5px
    }

    .invalid-feedback {
        display: block
    }

    .al_body_template_one .order_popop .modal-body {
        padding: 5px 15px 15px;
        background: #89898905;
        box-shadow: 4px 10px 6px #838282
    }

    .al_body_template_one .order_popop p {
        font-size: 13px;
        line-height: 19px
    }

    .al_body_template_one .order_popop .modal-body textarea {
        border: 1px solid#d9d3d3
    }

    .al_body_template_one .order_popop .modal-body textarea::placeholder {
        padding: 5px 10px
    }

    .al_body_template_one .order_popop .modal-body button.close {
        position: absolute;
        right: 5px;
        top: 0;
        padding: 0;
        margin: 0
    }

    .al_body_template_one .order_popop .modal-body label {
        display: inline-block;
        font-size: 18px !important;
        font-weight: 400;
    }
    .status_box li a {
	color: #6180cc !important;
    font-size:14px !important;
}

</style>
<section class="section-b-space order-page">
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
                <div class="col-lg-3">
                    <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                        <div class="dashboard-left mb-3">
                            <div class="collection-mobile-back">
                                <span class="filter-back d-lg-none d-inline-block">
                                    <i class="fa fa-angle-left" aria-hidden="true"></i>{{ __('Back') }}
                                </span>
                            </div>
                            @include('layouts.store/profile-sidebar')
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="dashboard-right">
                        <div class="dashboard">
                            <div class="page-title">
                                <h2>{{ __(getNomenclatureName($ordertitle, true)) }}</h2>
                            </div>
                            <div class="order_response mt-3 mb-3 d-none">
                                <div class="alert p-0" role="alert"></div>
                            </div>
                            <div class="welcome-msg">
                                <h5>{{ __('Here Are All Your Previous ' . getNomenclatureName($ordertitle, true)) }}</h5>
                            </div>
                            <div class="col-md-12">
                                <div class="row" id="orders_wrapper">
                                    <div class="col-sm-12 col-lg-12 tab-product al_custom_ordertabs mt-md-3 p-0">
                                        <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                                            @if($is_service_product_price_from_dispatch_forOnDemand == 1)
                                            <li class="nav-item">
                                                <a class="nav-link {{ Request::query('pageType') == 'pendingOrders' ? 'active show' : '' }} " id="pending-orders-tab" data-toggle="tab" href="#pending-orders" role="tab"
                                                    aria-selected="true"><i
                                                        class="icofont icofont-ui-home"></i>{{ __('Pending ' . getNomenclatureName($ordertitle, true)) }}</a>
                                                <div class="material-border"></div>
                                            </li>
                                            @endif
                                            <li class="nav-item">
                                                <a class="nav-link {{ Request::query('pageType') === null || Request::query('pageType') == 'activeOrders' ? 'active show' : '' }}"
                                                    id="active-orders-tab" data-toggle="tab" href="#active-orders" role="tab"
                                                    aria-selected="true"><i
                                                        class="icofont icofont-ui-home"></i>{{ __($orderTitles['Active'] . getNomenclatureName($ordertitle, true)) }}</a>
                                                <div class="material-border"></div>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link {{ Request::query('pageType') == 'pastOrders' ? 'active show' : '' }}"
                                                    id="past_order-tab" data-toggle="tab" href="#past_order" role="tab"
                                                    aria-selected="false"><i
                                                        class="icofont icofont-man-in-glasses"></i>{{ __($orderTitles['Past']  . getNomenclatureName($ordertitle, true)) }}</a>
                                                <div class="material-border"></div>
                                            </li>
                                            <!-- @if (isset($hidereturn) && $hidereturn != 1)
                                                <li class="nav-item">
                                                    <a class="nav-link {{ Request::query('pageType') == 'returnOrders' ? 'active show' : '' }}"
                                                        id="return_order-tab" data-toggle="tab" href="#return_order" role="tab"
                                                        aria-selected="false"><i
                                                            class="icofont icofont-man-in-glasses"></i>{{ __('Return Requests') }}</a>
                                                    <div class="material-border"></div>
                                                </li>
                                            @endif -->
                                            <li class="nav-item">
                                                <a class="nav-link {{ Request::query('pageType') == 'rejectedOrders' ? 'active show' : '' }}"
                                                    id="return_order-tab" data-toggle="tab" href="#rejected_order" role="tab"
                                                    aria-selected="false"><i
                                                        class="icofont icofont-man-in-glasses"></i>{{ getNomenclatureName($ordertitle, true). __(' Rejected/Cancel ')  }}</a>
                                                <div class="material-border"></div>
                                            </li>
                                            @if($show_long_term ==1)
                                                <li class="nav-item">
                                                    <a class="nav-link {{ Request::query('pageType') == 'LongTermOrders' ? 'active show' : '' }}"
                                                        id="long_term_order-tab" data-toggle="tab" href="#long_term_order" role="tab"
                                                        aria-selected="false"><i
                                                            class="icofont icofont-man-in-glasses"></i>{{ __('Long Term Serivces') }}</a>
                                                    <div class="material-border"></div>
                                            </li>
                                            @endif
                                        </ul>
                                        <div class="tab-content nav-material al" id="top-tabContent">
                                            @if($is_service_product_price_from_dispatch_forOnDemand == 1)
                                                @include('frontend.account.orders.pending_orders')
                                            @endif
                                            @include('frontend.account.orders.active_orders')
                                            @include('frontend.account.orders.past_orders')
                                            @include('frontend.account.orders.return_orders')
                                            @include('frontend.account.orders.rejected_orders')

                                            @if($show_long_term ==1)
                                                @include('frontend.account.longTermOrderTab')
                                            @endif

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-account box-info">
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>

<div class="modal fade product-rating" id="product_rating" tabindex="-1" aria-labelledby="product_ratingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="review-rating-form-modal">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade driver-rating" id="driver_rating" tabindex="-1" aria-labelledby="driver_ratingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="driver-review-rating-form-modal">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade return-order" id="return_order_model" tabindex="-1" aria-labelledby="return_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div id="return-order-form-modal"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade replace-order" id="replace_order_model" tabindex="-1" aria-labelledby="return_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div id="replace-order-form-modal"></div>
            </div>
        </div>
    </div>
</div>

<!-- start cancel order -->
{{-- <div class="modal fade vendor-order-cancel order_popop" id="cancel_order" tabindex="-1" aria-labelledby="cancel_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <div id="review-rating-form-modal">
                </div>
            </div>
        </div>
    </div>
</div> --}}

<div class="modal fade driver-rating" id="driver_rating" tabindex="-1" aria-labelledby="driver_ratingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="driver-review-rating-form-modal">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade return-order" id="return_order_model" tabindex="-1" aria-labelledby="return_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div id="return-order-form-modal"></div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade return-rental-order" id="return_rental_model" tabindex="-1" aria-labelledby="return_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div id="return-rental-order-form-modal"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade extend-order-rental" id="extend_order_rental" tabindex="-1" aria-labelledby="return_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Extend Order Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="extend-rental-order-form-modal"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="extend-btn">Extend</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade replace-order" id="replace_order_model" tabindex="-1" aria-labelledby="return_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div id="replace-order-form-modal"></div>
            </div>
        </div>
    </div>
</div>


<!-- start cancel order -->
<div class="modal fade vendor-order-cancel order_popop" id="cancel_order" tabindex="-1" aria-labelledby="cancel_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="cancel-order-form-modal">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end cancel order -->

<!-- start request cancel order -->
<div class="modal fade vendor-order-cancel order_popop" id="cancel_request_order" tabindex="-1" aria-labelledby="cancel_orderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="cancel-order-form-modal">
                    <form id="addRejectReqForm" method="post" class="text-center" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="reason">Select Reason</label>
                            <select class="form-control" id="return_reason_id" name="return_reason_id">
                                @foreach ($cancellation_reason as $reason)
                                <option value="{{$reason->id}}">{{$reason->title}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="order_id" id="req_order_id" />
                        <input type="hidden" name="order_vendor_id" id="req_order_vendor_id" />
                        <input type="hidden" name="order_vendor_product_id" id="req_order_vendor_product_id" />
                        <input type="hidden" name="req_vendor_id" id="req_vendor_id" />
                        <p id="error-case" style="color:red;"></p>
                        <label style="font-size:medium;">Enter reason for cancel the order. <small>(Optional)</small> </label>
                        <textarea class="reject_reason w-100" data-name="reject_reason" name="reject_reason" id="reject_reason" cols="50" rows="5"></textarea>
                        <button type="button" class="btn btn-info waves-effect waves-light addrejectReqSubmit">{{ __("Submit") }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end request cancel order -->

<!-- tip after order complete -->
@include('frontend.modals.tip_after_order')
@include('frontend.modals.pending-amount')



<!-- end tip order after complete -->
<!-- repeat order modal -->
<div class="modal fade remove-cart-modal" id="repeat_cart_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_cartLabel" style="background-color: rgba(0,0,0,0.8);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="remove_cartLabel">{{__('Repeat Order')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <h6 class="m-0 px-3">{{__('This change will remove all your cart products. Do you really want to continue ?')}}</h6>
            </div>
            <div class="modal-footer flex-nowrap justify-content-center align-items-center">
                <button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{__('Cancel')}}</button>
                <button type="button" class="btn btn-solid" id="repeat_cart_button" data-cart_id="">{{__('Remove')}}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="proceed_to_pay_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="pay-billLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="pay-billLabel">{{__('Total Amount')}}: <span id="total_amt"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="v_pills_tabContent_pending"></div>
        </div>
    </div>
</div>

<div class="modal fade remove-cart-modal" id="repeat_cart_modal1" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_cartLabel" style="background-color: rgba(0,0,0,0.8);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="remove_cartLabel">{{__('Repeat Order')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <h6 class="m-0 px-3">{{__('Are you sure you want to repeat same order')}}</h6>
            </div>
            <div class="modal-footer flex-nowrap justify-content-center align-items-center">
                <button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{__('Cancel')}}</button>
                <button type="button" class="btn btn-solid" id="repeat_cart_button" data-cart_id="">{{__('Yes')}}</button>
            </div>
        </div>
    </div>
</div>
<!-- end repat order modal -->
@include('frontend.modals.modal_recurring')

@endsection
@section('script')

<!-- tip after order complete -->
@include('frontend.modals.extend_order_payment')

<script src="{{asset('js/credit-card-validator.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.all.min.js"></script>
@if(in_array('razorpay',$client_payment_options))
<script type="text/javascript" src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif
@if(in_array('stripe',$client_payment_options) || in_array('stripe_fpx',$client_payment_options) || in_array('stripe_oxxo',$client_payment_options) || in_array('stripe_ideal',$client_payment_options))
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
<script>
    // Replace the supplied `publicKey` with your own.
    // Ensure that in production you use a production public_key.
    var sdk = new window.YocoSDK({
        publicKey: yoco_public_key
    });
</script>
@endif
@if(in_array('checkout',$client_payment_options))
<script src="https://cdn.checkout.com/js/framesv2.min.js"></script>
@endif
<script src="{{ asset('js/tip_after_order.js') }}"></script>
<script src="{{ asset('js/pending_payment.js') }}"></script>

@if(in_array('kongapay',$client_payment_options))
<script src="https://kongapay-pg.kongapay.com/js/v1/production/pg.js"></script>
@endif
@if(in_array('flutterwave',$client_payment_options))
<script src="https://checkout.flutterwave.com/v3.js"></script>
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
<script type="text/javascript" src="{{ asset('js/payment.js') }}"></script>
<script type="text/javascript" src="{{asset('js/developer.js')}}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>

<script type="text/javascript">
    $(document).delegate(".topup_wallet_btn_tip", "click", function() {
        $('#topup_wallet').modal('show');
        var payable_amount = $(this).attr('data-payableamount');
        //  if(payable_amount > 0)
        //  {
        //     $('#topup_wallet').modal('show');
        //  }
        var order_number = $(this).attr('data-order_number');
        var input_name = "select" + order_number;
        var custom_tip_amount = "custom_tip_amount" + order_number;

        var select_tip = $('input[name="' + input_name + '"]:checked').val();

        if (select_tip != 'custom' && select_tip != undefined) {
            $('.wallet_balance').html(select_tip);
            var tip_amount = select_tip;
        } else {
            $('.wallet_balance').html($('input[name="' + custom_tip_amount + '"]').val());
            var tip_amount = $('input[name="' + custom_tip_amount + '"]').val();
        }
        $("#wallet_amount").val(tip_amount);
        $("#cart_tip_amount").val(tip_amount);
        $("#order_number").val(order_number);
    });
    var ajaxCall = 'ToCancelPrevReq';
    var payment_obo_url = "{{route('obo.pay')}}";
    var livee_payment_url="{{route('livee.pay')}}"
    var credit_tip_url = "{{ route('user.tip_after_order') }}";
    var payment_azulpay_url = "{{route('payment.azulpay.beforePayment')}}";
    var payment_mpesa_safari_url = "{{route('mpesasafari.pay')}}";
    var payment_stripe_url = "{{ route('payment.stripe') }}";
    var create_konga_hash_url = "{{route('kongapay.createHash')}}";
    var create_payphone_url = "{{route('payphone.createHash')}}";
    var update_qty_url = "{{ url('product/updateCartQuantity') }}";

    var create_easypaisa_hash_url = "{{route('easypaisa.createHash')}}";
    var create_dpo_tocken = "{{route('dpo.createTocken')}}";
    var create_windcave_hash_url = "{{route('windcave.createHash')}}";
    var create_paytech_hash_url = "{{route('paytech.createHash')}}";
    var create_flutterwave_url = "{{route('flutterwave.createHash')}}";
    var create_viva_wallet_pay_url = "{{route('vivawallet.pay')}}";
    var create_mvodafone_pay_url = "{{route('mvodafone.pay')}}";
    var create_ccavenue_url = "{{route('ccavenue.pay')}}";
    var post_payment_via_gateway_url = "{{route('payment.gateway.postPayment', ':gateway')}}";
    var payment_retrive_stripe_fpx_url = "{{url('payment/retrieve/stripe_fpx')}}";
    var payment_create_stripe_fpx_url = "{{url('payment/create/stripe_fpx')}}";
    var payment_create_stripe_oxxo_url = "{{url('payment/create/stripe_oxxo')}}";
    var payment_create_stripe_ideal_url = "{{url('payment/create/stripe_ideal')}}";
    var payment_retrive_stripe_ideal_url = "{{url('payment/retrieve/stripe_ideal')}}";
    var payment_paypal_url = "{{ route('payment.paypalPurchase') }}";
    var payment_option_list_url = "{{route('payment.option.list')}}";
    var payment_yoco_url = "{{ route('payment.yocoPurchase') }}";
    var payment_checkout_url = "{{route('payment.checkoutPurchase')}}";
    var payment_paylink_url = "{{ route('payment.paylinkPurchase') }}";
    var wallet_payment_options_url = "{{ route('wallet.payment.option.list') }}";
    var payment_success_paypal_url = "{{ route('payment.paypalCompletePurchase') }}";
    var payment_paystack_url = "{{ route('payment.paystackPurchase') }}";
    var payment_success_paystack_url = "{{ route('payment.paystackCompletePurchase') }}";
    var payment_payfast_url = "{{ route('payment.payfastPurchase') }}";
    var payment_khalti_url = "{{route('payment.khaltiVerification')}}";
    var payment_khalti_complete_purchase = "{{route('payment.khaltiCompletePurchase')}}";
    var amount_required_error_msg = "{{ __('Please enter amount.') }}";
    var payment_method_required_error_msg = "{{ __('Please select payment method.') }}";
    var check_pickup_schedule_slots = "{{route('cart.check_pickup_schedule_slots')}}";
    var check_dropoff_schedule_slots = "{{route('cart.check_dropoff_schedule_slots')}}";
    var edit_order_by_user_url = "{{route('user.editorder')}}";
    var confirm_edit_order_title = "{{__('Are you sure?')}}";
    var confirm_edit_order_desc = "{{__('You want to edit this Order.')}}";
    var showcart_redirect = "{{route('showCart')}}";
    var discard_order_editing_url = "{{route('user.discardeditorder')}}";
    var confirm_discard_edit_order_title = "{{__('Are you sure?')}}";
    var confirm_discard_edit_order_desc = "{{__('You want to discard editing Order.')}}";
    var success_error_container = ".order_response";
    var payment_option_list_url = "{{route('payment.option.list')}}";
    var user_cards_url = "{{ route('payment.azulpay.getCards') }}";
    var powertrans_payment_url = "{{ route('powertrans.payment') }}";
    var data_trans_url = "{{route('payment.payByDataTrans')}}";
    var create_mtn_momo_token = "{{route('mtn.momo.createToken')}}";
    var mastercard_create_session_url = "{{ route('payment.mastercard.createSession') }}";
    var payment_hitpay_url="{{ route('make.hitpay.payment') }}";

     @if(!empty($client_preference_detail->is_postpay_enable))
        var post_pay_edit_order = "{{$client_preference_detail->is_postpay_enable}}";
    @else
        var post_pay_edit_order = 0;
    @endif
    var pesapal_payment_url = "{{ route('pesapal.payment') }}";

</script>
<script type="text/javascript">
    localStorage.removeItem('check_pk_date_check');
    localStorage.removeItem('check_date_check');
    localStorage.removeItem('check_pickup_order_date');
    var ajaxCall = 'ToCancelPrevReq';
    $('.verifyEmail').click(function() {
        verifyUser('email');
    });
    $('.verifyPhone').click(function() {
        verifyUser('phone');
    });
    // Added by Ovi
    function checkDates(prevPickup, prevDropoff, reschedulingCharges, pickupCancellingCharges, newPickupClass, newDropoffClass) {
        // var pickup_schedule_datetime = $('.'+newPickupClass).val();
        // var dropoff_schedule_datetime = $('.'+newDropoffClass).val();
        var pickup_schedule_datetime = "{{date('Y-m-d')}}";
        var dropoff_schedule_datetime = "{{date('Y-m-d')}}";

        if (Date.parse(pickup_schedule_datetime) == Date.parse(prevPickup)) {
            if (localStorage.getItem('check_pk_date_check') == null) {
                localStorage.setItem("check_pk_date_check", true);
                Swal.fire({
                    icon: 'info',
                    text: 'You are trying to reschedule the order on the day of pickup, additional ' + pickupCancellingCharges + ' will be debited from your wallet.',
                    confirmButtonText: 'Ok',
                });
                return false;
            }
        }

        if (Date.parse(dropoff_schedule_datetime) == Date.parse(prevDropoff)) {
            if (localStorage.getItem('check_date_check') == null) {
                localStorage.setItem("check_date_check", true);
                Swal.fire({
                    icon: 'info',
                    text: 'You are trying to reschedule the order on the day of delivery, additional ' + reschedulingCharges + ' will be debited from your wallet.',
                    confirmButtonText: 'Ok',
                });
                return false;
            }
            return true;
        }
    }

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
            error: function(data) {

            },
        });
    }
    $('body').on('click', '.add_edit_driver_review', function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var order_vendor_product_id = $(this).data('order_vendor_product_id');
        var dispatch_traking_url = $(this).data('dispatch_traking_url');
        $.get(`/rating/get-driver-rating?id=${id}&order_vendor_product_id=${order_vendor_product_id}&dispatch_traking_url=${dispatch_traking_url}`,
            function(markup) {
                $('#driver_rating').modal('show');
                $('#driver-review-rating-form-modal').html(markup);
            });
    });
    $('body').on('click', '.add_edit_review', function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var order_vendor_product_id = $(this).data('order_vendor_product_id');
        $.get('/rating/get-product-rating?id=' + id + '&order_vendor_product_id=' + order_vendor_product_id,
            function(markup) {
                $('#product_rating').modal('show');
                $('#review-rating-form-modal').html(markup);
            });
    });
    $('body').on('click', '.return-order-product', function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var vendor_id = $(this).data('vendor_id');
        $.get('/return-order/get-order-data-in-model?id=' + id + '&vendor_id=' + vendor_id, function(markup) {
            $('#return_order_model').modal('show');
            $('#return-order-form-modal').html(markup);
        });
    });

    $('body').on('click', '.rental_return', function(event) {
        event.preventDefault();
        var order_vendor_product_id = $(this).data('order_vendor_product_id');
        var attr = $(this).data('type');
        var type = '';
        if (typeof attr !== 'undefined' && attr !== false) {
            var type = attr;
        }
        $.get('/return-order/get-order-rental-data-in-model?order_vendor_product_id=' + order_vendor_product_id +'&type=' + type, function(markup) {
            $('#return_rental_model').modal('show');
            $('#return-rental-order-form-modal').html(markup);
        });
    });

    $('body').on('click', '.extend-order', function(event) {
        event.preventDefault();
        var order_vendor_product_id = $(this).data('order_vendor_product_id');
        var vendor_product_id = $(this).data('vendor_product_id');vendor_end_date_time
        var vendor_end_date_time = $(this).data('vendor_end_date_time');
        $.get('/extend-durartion/get-order-vendor-product-duration-data-in-model?order_vendor_product_id=' + order_vendor_product_id +'&vendor_product_id=' + vendor_product_id +'&vendor_end_date_time=' + vendor_end_date_time, function(markup) {
            $('#extend_order_rental').modal('show');
            $('#extend-rental-order-form-modal').html(markup);
        });
    });

    $('body').on('click', '.replace-order-product', function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var vendor_id = $(this).data('vendor_id');
        $.get('/return-order/get-replace-order-data-in-model?id=' + id + '&vendor_id=' + vendor_id, function(markup) {
            $('#replace_order_model').modal('show');
            $('#replace-order-form-modal').html(markup);
        });
    });

    $(document).delegate(".repeat-order-product", "click", function() {
        var order_vendor_id = $(this).data('order_vendor_id');
        $.ajax({
            type: "get",
            dataType: 'json',
            url: cart_details_url,
            success: function(response) {
                if (response.data != "") {
                    let cartProducts = response.data.products;


                    if (cartProducts != "") {
                        $("#repeat_cart_modal").modal('show');
                        $("#repeat_cart_modal #repeat_cart_button").attr("data-cart_id", response.data.id);
                        $("#repeat_cart_modal #repeat_cart_button").attr("data-order_vendor_id", order_vendor_id);

                    } else {
                        $("#repeat_cart_modal1").modal('show');
                        $("#repeat_cart_modal1 #repeat_cart_button").attr("data-cart_id", response.data.id);
                        $("#repeat_cart_modal1 #repeat_cart_button").attr("data-order_vendor_id", order_vendor_id);
                    }


                }
            }
        });
    });

    $(document).delegate("#repeat_cart_button", "click", function() {

        let cart_id = $(this).attr("data-cart_id");
        let order_vendor_id = $(this).attr("data-order_vendor_id");

        $.ajax({
            type: "post",
            dataType: 'json',
            url: "{{route('web.repeatOrder')}}",
            data: {
                'cart_id': cart_id,
                'order_vendor_id': order_vendor_id
            },
            success: function(response) {
                if (response.status == 'success') {
                    window.location.href = response.cart_url;
                }
            }
        });

    });

    $(document).delegate("#orders_wrapper .nav-tabs .nav-link", "click", function() {
        let id = $(this).attr('id');
        const params = window.location.search;
        if (params != '') {
            if (id == 'active-orders-tab') {
                window.location.href = window.location.pathname + '?pageType=activeOrders';
            } else if (id == 'past_order-tab') {
                window.location.href = window.location.pathname + '?pageType=pastOrders';
            }
        }
    });

    ///// cancel order start
    $('body').on('click', '.cancel_order', function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var order_vendor_id = $(this).data('order_vendor_id');
        var order_product_id = $(this).data('order_product_id');
        var pickup_cancelling_charges = $(this).data('pickup_cancelling_charges');
        if (typeof pickup_cancelling_charges !== 'undefined' && pickup_cancelling_charges !== false) {
            var order_id = $(this).data('order_id');
            var pickup_order_date = $(this).data('pickup_order');
            var order_number = $(this).data('order_number');
            var today = "{{date('Y-m-d')}}";
            if (Date.parse(pickup_order_date) == Date.parse(today)) {
                if (localStorage.getItem('check_pickup_order_date') == null) {
                    localStorage.setItem("check_pickup_order_date", true);
                    Swal.fire({
                        icon: 'info',
                        text: 'You are trying to cancel the order on the day of pickup, additional ' + pickup_cancelling_charges + ' will be debited from your wallet.',
                        confirmButtonText: 'Ok',
                    });

                    $.get('/return-order/get-vendor-order-for-cancel?id=' + id + '&order_vendor_id=' + order_vendor_id + '&order_product_id=' + order_product_id + '&pickup_cancelling_charges=' + pickup_cancelling_charges + '&order_id=' + order_id + '&pickup_order_date=' + pickup_order_date + '&order_number=' + order_number, function(markup) {
                        $('#cancel_order').modal('show');
                        $('#cancel-order-form-modal').html(markup);
                    });
                }
            } else {
                $.get('/return-order/get-vendor-order-for-cancel?id=' + id + '&order_vendor_id=' + order_vendor_id + '&order_product_id=' + order_product_id, function(markup) {
                    $('#cancel_order').modal('show');
                    $('#cancel-order-form-modal').html(markup);
                });
            }
        } else {
            $.get('/return-order/get-vendor-order-for-cancel?id=' + id + '&order_vendor_id=' + order_vendor_id + '&order_product_id=' + order_product_id, function(markup) {
                $('#cancel_order').modal('show');
                $('#cancel-order-form-modal').html(markup);
            });
        }
    });
    ////////// cancel order end

    $('body').on('click', '.request_cancel_order', function(event) {
        event.preventDefault();
        var order_vendor_id = $(this).data('id');
        var id = $(this).data('order_vendor_id');
        var vendor_id = $(this).data('vendor_id');
        var order_vendor_product_id = $(this).data('order_vendor_product_id');
        $('#cancel_request_order').modal('show');
        $('#req_order_id').attr('value', id);
        $('#req_order_vendor_id').attr('value', order_vendor_id);
        $('#req_vendor_id').attr('value', vendor_id);
        $('#req_order_vendor_product_id').attr('value', order_vendor_product_id);
        /* $('#cancel-order-form-modal').html(markup); */
    });

    // Added by Ovi
    // Check Slot Availability
    $(document).on("change", ".schedule_pickup_slot_select", function() {
        var url = "{{route('checkSlotOrders')}}"
        var schedule_pickup_datetime = $('#pickup_schedule_datetime_re').val();
        var vendor_id = $('#vendor_id').val();
        // get schedule_pickup_slot_select class parent id
        var parentRowID = this.parentNode.id;
        // get parentRowID child first's value
        var schedule_pickup_slot = document.getElementById(parentRowID).childNodes[1].value;
        $.ajax({
            type: "GET",
            data: {
                "schedule_pickup_datetime": schedule_pickup_datetime,
                "schedule_pickup_slot": schedule_pickup_slot,
                "vendor_id": vendor_id,
            },
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(output) {
                // Check if orderCount is greaten equal to orders_per_slot
                if (output.orderCount >= output.orders_per_slot) {
                    success_error_alert('error', 'All slots are full for the selected date & slot please choose another date or slot.', ".cart_response");
                    // Disable the place order button
                    $('.reschedule_now_btn').attr("disabled", true);
                } else {
                    // Enable the place order button
                    $('.reschedule_now_btn').attr("disabled", false);
                }
            },
            error: function(output) {
                // console.log(output);
            },
        });
    });

    $('.addrejectReqSubmit').on('click', function(e) {
        e.preventDefault();
        var return_reason_id = $('#return_reason_id').val();
        var reject_reason = $('#reject_reason').val();
        var order_vendor_product_id = $('#req_order_vendor_product_id').val();
        var order_id = $('#req_order_id').attr("value");
        var vendor_id = $('#req_vendor_id').attr("value");
        var order_vendor_id = $('#req_order_vendor_id').attr("value");
        $.ajax({
            url: "{{ route('order.cancel.req.customer') }}",
            type: "POST",
            data: {
                vendor_id: vendor_id,
                order_id: order_id,
                order_vendor_product_id: order_vendor_product_id,
                reject_reason: reject_reason,
                "_token": "{{ csrf_token() }}",
                order_vendor_id: order_vendor_id,
                return_reason_id: return_reason_id
            },
            success: function(response) {
                if (response.status == 'success') {
                    $("#cancel_request_order #reject_reason").val('');
                    $("#cancel_request_order .close").click();
                    Swal.fire({
                        icon: 'success',
                        text: response.message,
                        confirmButtonText: 'Ok',
                    });
                } else if (response.status == 'error') {
                    $("#cancel_request_order #reject_reason").val('');
                    $("#cancel_request_order .close").click();
                    Swal.fire({
                        icon: 'warning',
                        text: response.message,
                        confirmButtonText: 'Ok',
                    });
                }
            },
            error: function(response) {
                if (response.status == 'error') {
                    $('#error-case').empty();
                    $('#error-case').append(response.message);
                }
            }
        });
    });



$(document).delegate(".order_placed_btn_pending", "click", function() {
        var dataId = $(this).attr("data-id");
        var amount = $(this).attr("data-amount");
        var order_number = $(this).attr("data-order");
        $("#order_number").val(order_number);
        $('#pending_amount').val(amount);
        $('#pending_amount_modal').modal('show');
        var payable_amount = $(this).attr('data-payableamount');
        var input_name = "select" + order_number;
        $('.wallet_balance').html(amount);
        $("#pending_amount").val(amount);
        $("#amount_pending").val(amount);
        $("#order_number").val(order_number);
});




    $(document).on('click', '#extend-btn', function(){
        $.ajax({
            data: {},
            type: "POST",
            dataType: 'json',
            url: payment_option_list_url,
            success: function (response) {
                console.log(response);
                if (response.status == "Success") {
                    // console.log(response.data);
                    $('#v_pills_tab').html('');
                    $('#v_pills_tabContent').html('');
                    let payment_method_template = _.template($('#payment_method_template').html());
                    $("#v_pills_tab").append(payment_method_template({ payment_options: response.data }));
                    let payment_method_tab_pane_template = _.template($('#payment_method_tab_pane_template').html());

                    $("#v_pills_tabContent").append(payment_method_tab_pane_template({ payment_options: response.data }));
                    $('#extend_order_rental').modal('hide');
                    $('#proceed_to_pay_modal').modal('show');

                    //mohit sir branch code added by sohail
                    var advanceCartTotalPayableAmount = $('#advance_cart_total_payable_amount').length;
                    if(advanceCartTotalPayableAmount == 1){
                        var amtHTML = 'Advanced Token Amount: <span id="total_amt">'+$('#advance_cart_total_payable_amount').html()+'</span>';
                        $('#proceed_to_pay_modal #pay-billLabel').html(amtHTML);
                    }else{
                        $('#proceed_to_pay_modal #total_amt').html($('#cart_total_payable_amount').html());
                    }
                    //till here
                    if(stripe_publishable_key != ''){
                        stripeInitialize();
                    }
                    if(stripe_fpx_publishable_key != ''){
                        stripeFPXInitialize();
                    }
                    if(stripe_ideal_publishable_key != ''){
                        stripeIdealInitialize();
                    }
                }
            },
            error: function (error) {
                var response = $.parseJSON(error.responseText);
                let error_messages = response.message;
                $.each(error_messages, function (key, error_message) {
                    $('#min_order_validation_error_' + error_message.vendor_id).html(error_message.message).show();
                });
            }
        });
        // $('#proceed_to_pay_modal').modal();
    });

    function addSlashes (element) {

    let ele = document.getElementById(element.id);
    ele = ele.value.split('/').join('');    // Remove slash (/) if mistakenly entered.
    if(ele.length < 4 && ele.length > 0){
        let finalVal = ele.match(/.{1,2}/g).join('/');

        document.getElementById(element.id).value = finalVal;
    }
}

        $('.recurringBtn').click(function()
        {
            var date = $(this).attr('data-recurring_day_data');
            var slot = $(this).attr('data-recurring_slot');

            const dateDate = date.split(",");
            var days = dateDate.length;

            $(".recurring-modal").modal();
            $('#days-recurring').html(days);
            $('#slot-recurring').html(slot);
            $('#date-recurring').html(date);
        });

</script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/additional-methods.min.js"></script>
<script src="{{asset('front-assets/js/reschedule_order.js')}}"></script>
<script src="{{asset('front-assets/js/user_edit_order.js')}}"></script>
@if(in_array('data_trans',$client_payment_options))
    <script src="{{ $data_trans_script_url }}"></script>
@endif
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="{{asset('assets/js/chat/user_vendor_chat.js')}}"></script>
@endsection
