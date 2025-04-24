@extends('layouts.store', ['title' => __('Cart')])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/azul.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">

<style type="text/css">
.swal2-title {margin: 0px;font-size: 26px;font-weight: 400;margin-bottom: 28px;}
.discard_price {text-decoration: line-through;color: #6c757d;}
#category_kyc_form_in_cart .file--upload>label {width: 100%;height: 200px;border: 1px solid #eee;border-radius: 15px;}
#category_kyc_form_in_cart .file .update_pic {width: 100%;height: auto;margin: auto;text-align: center;border: 0;border-radius: 0;}
#category_kyc_form_in_cart .file .update_pic img {height: 130px;width: auto;}
.time {display: inline-block;font-size: 26px;padding: 5px;text-align: center;width: 94px;margin-top: 5px;}
#applepay-btn {width: 100%;height: 50px;display: none;border-radius: 5px;margin-left: auto;margin-right: auto;margin-top: 20px;background-image: -webkit-named-image(apple-pay-logo-white);background-position: 50% 50%;background-color: black;background-size: 60%;background-repeat: no-repeat;color: #FFF !important;}
#save_prescription_form .modal-footer {display: block;}
.al_body_template_two .show-prescription-doc {overflow: auto;white-space: nowrap;overflow-y: hidden;width: 100%;}
.show-prescription-close {position: relative;display: inline-block;}
.show-prescription-close i {position: absolute;right: -2px;top: 0px;font-size: 11px;background: #eee;padding: 1px 2px;border-radius: 10px;cursor: pointer;}
.al_body_template_two .show-prescription-doc img{margin:2px;}
.al_body_template_one .vendor_slot_cart input {display: inline-block;width: 52%;}
.al_body_template_one .vendor_slot_cart select {display: inline-block;width: 45%;}
.grn_popop-total_amt label{  font-size: 12px !important;text-align:left;}
.vendor_cart-check label {display: inline-block;}
/*-------cart page design css  start here---------*/
.items-details p{font-size: 12px;}
.dark .new_cart,
.dark .cart-summary,
.dark .new_cart .add-address i,
.dark .shoping_cart {background-color: #232323;box-shadow: 10px 16px 14px 10px #0000001c;border: 1px solid #4645458c;}
.dark .item-show-cart h4,
.dark .cart-summary h5.order_text,
.dark .new_cart .page-title,
.dark .cart-summary .alFourSpecificInstructions span,
.dark .shoping_cart .order-md-4 .items-price,
.dark .cart-summary .cart-price .text-right b,
.dark .single_cart_heading h3{color: #cfd4da;}
.dark .alFourSpecificInstructions input{background-color: #c9c3c3;}
.dark .delivery_box.cart_delivery{border-color: #4645458c; }
.single_cart_heading h3 {font-weight: 600;color: #000;}
.cart-summary h5.order_text{font-size: 22px;font-weight: 600;color:#000;}
.item-show-cart h4 {font-size: 20px !important;font-weight: 600;color: #000;}
.shoping_cart {background: #fff;box-shadow: 10px 16px 14px 10px #eee9;border: 1px solid #eeeeee8c;}
.dark #specific_instructions::-webkit-input-placeholder,
.dark #specific_instructions::-moz-input-placeholder { color:#fff; }
.alFourSpecificInstructions input#specific_instructions{background-color: #eee;}
.dark .new_cart .add-address i ,
.dark .cart_delivery,
.dark .alert-danger {background: #242424;border: 1px solid #242424;box-shadow: 5px 6px 4px #0000001a;}
.dark .cart-summary,
.dark .new_cart,
.dark .shoping_cart{background-color: #0c0c0c;box-shadow: none;border: 0px;}
.dark .item-show-cart h4,
.dark .new_cart .page-title,
.dark .cart-summary .cart-price .mb-2,
.dark .cart-summary h5.order_text,
.dark .cart-summary .alFourSpecificInstructions span,
.dark .cart-summary .cart-price .text-right b,
.dark .shoping_cart .order-md-4 .items-price,
.dark .single_cart_heading h3 {color: #fff;}
.dark .list-box{background-color: #242424;}
.dark .tip_label{background: transparent;}
.dark .tip_radio:checked+.tip_label p{color: #fff !important;}
.dark .tip_radio:checked+.tip_label{box-shadow:none;}
.cart-design .card-box {padding: 0px;}
.cart-summary{background-color: #f5f5f6;border: 1px solid #efefef;box-shadow: 6px 13px 30px #1615152b;border-radius: 4px;width: 100%;}
.cart-summary .alFourSpecificInstructions span {font-size: 18px;color: #000;display: block;}
.cart-summary .alFourSpecificInstructions input::placeholder {color: #000;font-size: 14px;}
.cart-summary .cart-price .text-right b {color: #000;font-size: 14px;}
.cart-summary .cart-price .mb-2{font-size: 18px;color: #000;}
.cart-page-layout .shoping_cart .product-img img {border-radius: 10px;}
.shoping_cart .order-md-4 .items-price {color: #000;font-size: 14px;font-weight: 500;}
.shoping_cart .cart_product_name{font-size: 16px;}
.shoping_cart .vendor_products_tr h4 strong{font-size: 15px;}
.shoping_cart .cart-heading h5{font-size: 18px;font-weight: 400;}
.new_cart {background: #fff;box-shadow: 10px 16px 14px 10px #eee9;border: 1px solid#eeeeee8c;}
.cart_edit-addre{border-top: 1px solid#eee;}
.new_cart .page-title {font-size:20px;font-weight: 600;color:#000;}.delivery_box label { color: #666363; font-size: 16px; line-height: 28px;display: block;width:100%;}
.alFourTemplateCartButtons a.shoping{color:#FA1C0A;}
.cart_delivery{border-radius: 5px;background-color: #fff;box-shadow: 1px 4px 4px #eee;height:100%;
display: flex;align-items: center;justify-content: center;border: 1px solid#eee;overflow: hidden;}
.alFourTemplateCartButtons a.shoping i {font-size: 18px;vertical-align: bottom;padding-right: 5px;}
.cart_all_address a {text-align: center;width: 100%;font-size: 14px;}
.cart_delivery a {position: absolute;right: 10px;top: 10px;}
.cart_delivery a.deleteAddress{position: absolute;right: -50px;top: 50%;color:red;-webkit-transform: translateY(-50%);transform: translateY(-50%);}
.cart_delivery:hover a.deleteAddress{right: 10px;}
.new_cart .add-address i {background: #fff;border: 1px solid#eee; padding: 16px;border-radius: 100%;height: 40px;width: 40px;display: flex;align-items: center;
justify-content: center;font-size: 20px;box-shadow: 5px 6px 4px #eee;color: #ff3f3f;}
.cart-checkout_btn button{width:100%;}
.cart-checkout_btn #order_placed_btn{padding: 10px 5px !important;display: inline-block;font-size: 14px !important;}
.cart_delivery a i {font-weight: 600;font-size: 16px;}
.schedule_btn ul li label.taskschedulebtn {padding: 6px 10px !important;font-size: 10px !important;}
.cart-page-layout .alFourTemplateCartPage .add_head h6{color:#000;font-size: 14px;}
.cart-page-layout .alFourTemplateCartPage .items-details p{font-size: 14px;}
.cart-page-layout .alFourTemplateCartPage .extra-items-price {color: #000;font-size: 14px;}
.product_title_add span {text-transform: uppercase;font-size: 14px;}

/* .al_body_template_two .cart-summary {background: #fff;} */
.shoping_cart .number{width: fit-content;}
.al_body_template_three .cart-summary .tip_radio_controls label{width:auto;margin:0px;}
.al_body_template_four .shoping_cart .alFourTemplateCartButtons .btn.shoping i{position: inherit;box-shadow:none;}
.al_body_template_four .new_cart a.add-address i{position:inherit;}
.al_body_template_four .new_cart a.add-address{padding-right:0px;}
.al_body_template_four .cart-summary .cart-checkout_btn .btn-solid{border-radius: 4px;}
.al_body_template_three .cart-summary .cart-checkout_btn .btn-solid{border-radius: 4px;}
.al_body_template_four .shoping_cart .prescription_btn{padding: 5px 10px;font-size: 10px;}

/* 2 template cart checkbox css */
.tip_radio:checked+.tip_label {background: var(--theme-deafult);box-shadow: 0 0 5px var(--theme-deafult);}
.tip_radio:checked+.tip_label h5, .tip_radio:checked+.tip_label p {color: #fff;}
.al_body_template_four .text-danger {font-size: 16px;}
.al_body_template_four .clproduct_cart_order_form.btn.btn-solid {padding: 8px 0px;font-size: 12px;}
.al_body_template_two .shoping_cart .alFourTemplateCartPage #product_faq_dev_42 .btn-product-order-form-div button {
font-size: 12px;padding: 6.7px 10px;}
.add_address_btn .btn-solid{font-size: 12px;padding: 6.7px 10px;}
#add_new_address_form_modal button.close{position: absolute;right: 20px;}
.cart-design .product-img img{border-radius: 15px;overflow: hidden;}
.dark .coupon_box img.blur-up.lazyloaded{background-color: #fff;opacity: 1;padding: 0 4px;border-radius: 25px;}
.login-form #schedule_div input{display:block;width: 100% !important;}
.login-form #schedule_div input::-webkit-calendar-picker-indicator{color: rgba(0, 0, 0, 0);opacity: 1}
.cart_login_popop .modal-header h5 {font-size: 18px;font-weight: 600;text-transform: capitalize;}
.cart_login_popop input{border-radius: 3px;}
.cart_login_popop .new-user{text-align: center;padding: 30px 0px 0px 0px;}
.cart_login_popop .login_continue_btn{border-radius: 3px !important;}
.cart_login_popop  .login-button{font-size: 16px;}
.cart_login_popop .login_continue_btn{font-size: 16px;}


/*------cart page css end here------ */
@media (max-width:576px){
.al_body_template_two .show-prescription-doc {width:100%;}
.item-show-cart h4 {font-size:14px !important;}
.product_title_add span {font-size: 12px;}
.single_cart_heading h3 {font-size: 18px;}
#cart_template .col-5.text-lg-right label.radio {padding-left: 0px;}
.cart-design .alFourTemplateCartButtons a.shoping i{transform: translate(0px, -2px);}
.al_body_template_one .cart-summary .tip_label {padding: 5px 8px;}

}

/*cart page responsive css */
@media only screen and (min-width:1367px) and (max-width:1429px){
.cart-design .alFourTemplateCartButtons a.shoping{font-size:13px;height:auto;}
}
@media only screen and (max-width:1366px){
.cart-design .alFourTemplateCartButtons a.shoping i{font-size:17px;vertical-align: middle;}
.cart-design .alFourTemplateCartButtons a.shoping{font-size:13px;vertical-align: middle;text-align:left;height:auto;}
}

@media (min-width:991px) and (max-width:1200px){
.cart-design .alFourTemplateCartButtons a.btn.shoping {font-size:10px;display: block;width: 100%;text-align: left;height:auto;}
.cart-design .alFourTemplateCartButtons a.shoping i{font-size:10px;vertical-align: middle;}
}

</style>

@endsection
@php $today=['start_time'=>"00:00",'end_time'=>"00:00"]; @endphp
@foreach($vendorWeeklySlotDay as $row)
@if(($row['day']-1)==(int)date('w'))
@php $today=['start_time'=>$row['start_time'],'end_time'=>$row['end_time']]; @endphp
@endif
@endforeach
@section('content')
@php
$now = \Carbon\Carbon::now()->format('Y-m-d\TH:i');
if(Auth::user()){
$timezone = Auth::user()->timezone;
$now = convertDateTimeInTimeZone($now, $timezone, 'H:i');
}
$clientData = \App\Models\Client::select('id', 'logo')->where('id', '>', 0)->first();
$urlImg = $clientData ? $clientData->logo['original'] : ' ';
$languageList = \App\Models\ClientLanguage::with('language')->where('is_active', 1)->orderBy('is_primary', 'desc')->get();
$currencyList = \App\Models\ClientCurrency::with('currency')->orderBy('is_primary', 'desc')->get();
$client_preferences = \App\Models\ClientPreference::first();
@endphp



<script>
    var action_type = "{{$action}}";
</script>

<script type="text/template" id="promo_code_template">
    <% _.each(promo_codes, function(promo_code, key){%>
        <div class="col-lg-12 mt-3">
            <div class="coupon-code mt-0">
                <div class="p-2">
                    <img class="blur-up lazyload p-1" data-src="<%= promo_code.image.proxy_url %>100/70<%= promo_code.image.image_path %>" alt="">
                    <h6 class="mt-0"><%= promo_code.title %></h6>
                </div>
                <hr class="m-0">
                <div class="code-outer p-2 text-uppercase row align-items-center justify-content-between">
                    <div class="col-9">
                        <label class="m-0"><%= promo_code.name %></label>
                    </div>
                    <div class="col-3 text-left">
                        <a class="btn btn-solid apply_promo_code_btn" data-vendor_id="<%= vendor_id %>" data-cart_id="<%= cart_id %>" data-coupon_id="<%= promo_code.id %>" data-amount="<%= amount %>" style="cursor: pointer;">{{__('Apply')}}</a>
                    </div>
                </div>
                <hr class="m-0">
                <div class="offer-text p-2">
                    <p class="m-0"><%= promo_code.short_desc %></p>
                </div>
            </div>
        </div>
    <% }); %>
</script>

<script type="text/template" id="no_promo_code_template">
    <div class="col-12 no-more-coupon text-center">
        <p>{{__('No Other Coupons Available.')}}</p>
    </div>
</script>

<div class="cart-design">
    {{-- {{dump($client_preference_detail)}} --}}
    <div id="mycart"></div>
    <div class="container">
        @if($cartData)

        <input type="hidden" id='cart_id' value="{{ isset($cartData['0']) ?  $cartData['0']->cart_id : '' }}">

        <form method="post" action="" id="placeorder_form">
            @csrf
            <div class="card-box bg-transparent">
                <!-- <div class="row d-flex justify-space-around"> -->


                    <div class="spinner-box">
                        <div class="circle-border">
                            <div class="circle-core"></div>
                        </div>
                    </div>

                    <div class="row cart-page-layout" id="cart_table"></div>

                <!-- </div> -->
                <!-- <div class="row mb-md-3 alFourTemplateCartButtons mt-4">
                    <div class="col-sm-6 col-lg-4 mb-2 mb-sm-0 d-lg-flex align-items-lg-center justify-content-lg-between">
                        <a class="btn btn-solid" href="{{ url('/') }}">{{__('Continue Shopping')}}</a>
                        @if(!empty(Auth::user()))
                        <a href="{{route('user.addressBook')}}"><i class="fa fa-pencil" aria-hidden="true"></i> <span>{{ __('Edit Address') }}</span> </a>
                        @endif
                    </div>




                </div> -->
            </div>

        </form>
        @else
        <div class="row mt-5 mb-4 pt-5">
            <div class="col-12 text-center">
                <div class="cart_img_outer" style="height:200px;">
                    <img class="blur-up lazyload" data-src="{{asset('front-assets/images/empty_cart.png')}}">
                </div>
                <h3>{{__('Your Cart Is Empty!')}}</h3>
                <p>{{__('Add items to it now.')}}</p>
                <a class="btn btn-solid" href="{{url('/')}}">{{__('Continue Shopping')}}</a>
            </div>
        </div>
        @endif
    </div>

    <div class="other_cart_products"></div>

</div>


{{-- <div id="expected_vendors" class="mb-4">
</div> --}}


<script type="text/template" id="other_cart_products_template">
    <div class="container mt-3 mb-5">
        <% if(cart_details.upSell_products != ''){ %>
            <h3 class="mb-2 mt-4">{{__('Frequently bought together')}}</h3>
            <div class="row">
                <div class="col-12 p-0">
                    <div class="product-4 product-m">
                        <% _.each(cart_details.upSell_products, function(product, key){%>

                            <a class="common-product-box scale-effect text-center" href="<%= product.vendor.slug %>/product/<%= product.url_slug %>">
                                <div class="img-outer-box position-relative">
                                    <img class="blur-up lazyload" data-src="<%= product.image_url %>" alt="">
                                    <div class="pref-timing">
                                        <!--<span>5-10 min</span>-->
                                    </div>
                                    <i class="fa fa-heart-o fav-heart position-absolute" aria-hidden="true"></i>
                                </div>
                                <div class="media-body align-self-center">
                                    <div class="inner_spacing px-0">
                                        <div class="product-description">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="card_title ellips"><%= product.translation_title %></h6>
                                                <!--<span class="rating-number">2.0</span>-->
                                            </div>
                                            <p><%= product.vendor_name %></p>
                                            <p class="border-bottom pb-1 d-none">In <%= product.category_name %></p>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <b><% if(product.inquiry_only == 0) { %>
                                                    {{ Session::get('currencySymbol') }}<%= Helper.formatPrice(product.variant_price) %>
                                                <% } %></b>

                                                <!-- @if($client_preference_detail)
                                                    @if($client_preference_detail->rating_check == 1)
                                                        <% if(product.averageRating > 0){%>
                                                            <div class="rating-box">
                                                                <i class="fa fa-star" aria-hidden="true"></i>
                                                                <span><%= product.averageRating %></span>
                                                            </div>
                                                        <% } %>
                                                    @endif
                                                @endif -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>

                        <% }); %>
                    </div>
                </div>
            </div>
        <% } %>

        <% if(cart_details.crossSell_products != ''){ %>
            <h3 class="mb-2 mt-3">{{__('You might be interested in')}}</h3>
            <div class="row">
                <div class="col-12 p-0">
                    <div class="product-4 product-m">
                        <% _.each(cart_details.crossSell_products, function(product, key){%>

                            <a class="common-product-box scale-effect text-center" href="<%= product.vendor.slug %>/product/<%= product.url_slug %>">
                                <div class="img-outer-box position-relative">
                                    <img class="blur-up lazyload" data-src="<%= product.image_url %>" alt="">
                                        <div class="pref-timing">
                                            <!--<span>5-10 min</span>-->
                                        </div>
                                        <i class="fa fa-heart-o fav-heart position-absolute" aria-hidden="true"></i>
                                </div>
                                <div class="media-body align-self-center">
                                    <div class="inner_spacing px-0">
                                        <div class="product-description">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="card_title ellips"><%= product.translation_title %></h6>
                                                <!--<span class="rating-number">2.0</span>-->
                                            </div>
                                            <!-- <h3 class="m-0"><%= product.translation_title %></h3> -->
                                            <p><%= product.vendor_name %></p>
                                            <p class="border-bottom pb-1 d-none">In <%= product.category_name %></p>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <b><% if(product.inquiry_only == 0) { %>
                                                    {{ Session::get('currencySymbol') }}<%= Helper.formatPrice(product.variant_price) %>
                                                <% } %></b>

                                                <!-- @if($client_preference_detail)
                                                    @if($client_preference_detail->rating_check == 1)
                                                        <% if(product.averageRating > 0){%>
                                                            <div class="rating-box">
                                                                <i class="fa fa-star" aria-hidden="true"></i>
                                                                <span><%= product.averageRating %></span>
                                                            </div>
                                                        <% } %>
                                                    @endif
                                                @endif -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>

                        <% }); %>
                    </div>
                </div>
            </div>
        <% } %>
    </div>
</script>
<div class="modal fade refferal_modal" id="refferal-modal" tabindex="-1" aria-labelledby="refferal-modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="refferal-modalLabel">{{__('Apply Coupon Code')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body mt-0 pb-0 pt-4">
                <div class="row validate_promo_div">
                    <div class="col-9">
                        <div class="form-group" >
                            <input class="form-control manual_promocode_input" name="name" type="text" placeholder="{{ __('Enter a promocode')}}" >
                            <button class="btn btn-solid apply_promo_code_btn" data-vendor_id="" data-cart_id=""
                            data-coupon_id="" data-amount="" style="display:none">Apply</button>

                        </div>
                        <span class="invalid-feedback manual_promocode" role="alert">
                        </span>
                    </div>
                    <div class="col-3 p-0">
                        <button class="btn btn-solid validate_promo_code_btn" data-vendor_id="" data-cart_id=""
                            data-coupon_id="" data-amount="" style="cursor: pointer;">Apply</button>
                    </div>
                </div>
                <div class="coupon-box">
                    <div class="row mb-3" id="promo_code_list_main_div">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade giftCard_modal" id="giftCard-modal" tabindex="-1" aria-labelledby="giftCard-modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="giftCard-modalLabel">{{__('Apply Gift Card Code')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body mt-0 pb-0 ">
                <div class="row validate_giftCard_div border-bottom">
                    <div class="col-9">
                        <div class="form-group" >
                            <input class="form-control manual_giftCard_input" name="name" type="text" placeholder="{{ __('Enter a Gift Cardcode')}}" >
                            <button class="btn btn-solid apply_giftCard_code__btn" data-user_id="" data-cart_id=""
                            data-giftCard_id="" data-amount="" style="display:none">Apply</button>
                            <span class="invalid-feedback manual_giftCard" role="alert">

                            </span>
                        </div>
                    </div>
                    <div class="col-3 pl-0">
                        <button class="btn btn-solid w-100 validate_giftCard_code_btn" data-cart_id=""
                            data-giftCard_id="" data-amount="" style="cursor: pointer;">Apply</button>
                    </div>
                </div>
                <div class="coupon-box">
                    <div class="row mb-0" id="giftCard_code_list_main_div">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade remove-item-modal" id="remove_item_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_itemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="remove_itemLabel">{{__('Remove Item')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="vendor_id" value="">
                <input type="hidden" id="cartproduct_id" value="">
                <h6 class="m-0">{{__('Are You Sure You Want To Remove This Item?')}}</h6>
            </div>
            <div class="modal-footer flex-nowrap justify-content-center align-items-center">
                <button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{__('Cancel')}}</button>
                <button type="button" class="btn btn-solid" id="remove_product_button">{{__('Remove')}}</button>
            </div>
        </div>
    </div>
</div>
<script type="text/template" id="payment_method_template">
    <% _.each(payment_options, function(payment_option, k){%>
        <a class="nav-link <%= payment_option.slug == 'cash_on_delivery' ? 'active': ''%>" id="v-pills-<%= payment_option.slug %>-tab" data-toggle="pill" href="#v-pills-<%= payment_option.slug %>" role="tab" aria-controls="v-pills-wallet" aria-selected="true" data-payment_option_id="<%= payment_option.id %>"><%= payment_option.title %></a>
    <% }); %>
</script>
<script type="text/template" id="payment_method_tab_pane_template">
    <% if(payment_options == '') { %>
        <h6>{{__('Payment Options Not Avaialable')}}</h6>
    <% }else{ %>
        <div class="modal-body pb-0">
            <h5 class="text-17 mb-2">{{__('Debit From')}}</h5>
            <form method="POST" id="cart_payment_form">
                @csrf
                @method('POST')
                <% _.each(payment_options, function(payment_option, k){%>
                    <div class="" id="" role="tabpanel">
                        <label class="radio mt-2">
                            <%= payment_option.title %>
                            <input type="radio" name="cart_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.id %>" data-payment_option_id="<%= payment_option.id %>">
                            <span class="checkround"></span>
                        </label>
                        <% if(payment_option.slug == 'stripe') { %>
                            <div class="col-md-12 mt-3 mb-3 stripe_element_wrapper option-wrapper d-none">
                                <div class="form-control">
                                    <label class="mb-0">
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
                            <div class="col-md-12 mt-3 mb-3">
                                <div id="pp-button"></div>
                            </div>
                        <% } %>

                        <% if(payment_option.slug == 'plugnpay') { %>
                            <div class="col-md-12 mt-3 mb-3 plugnpay_element_wrapper option-wrapper d-none">
                                <div class="row no-gutters">
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

                    </div>
                <% }); %>
                {{-- <div class="" id="" role="tabpanel">
                    <label class="radio mt-2">
                        Apple Pay
                        <input type="radio" name="cart_payment_method" id="radio-paytab_apple_pay" value="100" data-payment_option_id="100">
                        <span class="checkround"></span>
                    </label>
                    <div class="col-md-12 mt-3 mb-3 paytab_apple_pay_element_wrapper option-wrapper d-none">
                        <button type="button" id="applepay-btn">Pay Now</button>
                        <span class="error text-danger" id="paytab_apple_pay_error"></span>
                    </div>
                </div> --}}
                <div class="payment_response">
                    <div class="alert p-0 m-0" role="alert"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer d-block text-center pt-0">
            <div class="row">
                <div class="col-12 grn_popop-total_amt">
                    <label>{{ __('By placing this order I accept the') }}
                        <a href="{{ $terms ? route('extrapage', $terms->slug) : '#' }}"
                            target="_blank">{{ __('Terms And Conditions') }} </a>
                        {{ __('and have read the') }}
                        <a href="{{ $privacy ? route('extrapage', $privacy->slug) : '#' }}"
                            target="_blank">
                            {{ __('Privacy Policy') }}.
                        </a>
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 p-0 d-flex flex-fill">
                    <button type="button" style="width:100%;" class="btn btn-solid ml-1 proceed_to_pay">{{__('Place Order')}}
                        <img style="width:5%; display:none;" id="proceed_to_pay_loader" src="{{asset('assets/images/loader.gif')}}"/>
                    </button>
                </div>
            </div>
        </div>
    <% } %>
</script>
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
<div class="modal fade" id="proceed_to_pay_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="pay-billLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="pay-billLabel">{{__('Total Amount')}}: <span id="total_amt"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="v_pills_tabContent"></div>
        </div>
    </div>
</div>
<!-- <script type="text/template" id="payment_method_tab_pane_template">
    <% _.each(payment_options, function(payment_option, k){%>
        <div class="tab-pane fade <%= payment_option.slug == 'cash_on_delivery' ? 'active show': ''%>" id="v-pills-<%= payment_option.slug %>" role="tabpanel" aria-labelledby="v-pills-<%= payment_option.slug %>-tab">
            <form method="POST" id="<%= payment_option.slug %>-payment-form">
            @csrf
            @method('POST')
                <div class="payment_response mb-3">
                    <div class="alert p-0" role="alert"></div>
                </div>
                <div class="form_fields">
                    <div class="row">
                        <div class="col-md-12">
                            <% if(payment_option.slug == 'stripe') { %>
                                <div class="form-control">
                                    <label class="d-flex flex-row pt-1 pb-1 mb-0">
                                        <div id="stripe-card-element"></div>
                                    </label>
                                </div>
                                <span class="error text-danger" id="stripe_card_error"></span>
                            <% } %>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-md-right">
                            <button type="button" class="btn btn-solid" data-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="button" class="btn btn-solid ml-1 proceed_to_pay">{{__('Place Order')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <% }); %>
</script>
<div class="modal fade" id="proceed_to_pay_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="pay-billLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="row no-gutters">
                    <div class="col-4">
                        <div class="nav flex-column nav-pills" id="v_pills_tab" role="tablist" aria-orientation="vertical"></div>
                    </div>
                    <div class="col-8">
                        <div class="tab-content-box px-3">
                            <div class="d-flex align-items-center justify-content-between pt-3">
                                <h5 class="modal-title" id="pay-billLabel">{{__('Total Amount')}}: <span id="total_amt"></span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="tab-content h-100" id="v_pills_tabContent">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- Modal -->
<div class="modal fade login-modal" id="login_modal" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <form id="login-form-new" action="">
                    @csrf
                    <input type="hidden" name="device_type" value="web">
                    <input type="hidden" name="device_token" value="web">
                    <input type="hidden" id="dialCode" name="dialCode" value="{{ old('dialCode') ? old('dialCode') : Session::get('default_country_phonecode','1') }}">
                    <input type="hidden" id="countryData" name="countryData" value="{{ strtolower(Session::get('default_country_code','US')) }}">

                    <div class="login-with-username cart_login_popop">
                        <div class="modal-header px-0 pt-0">
                            <h5 class="modal-title">{{ __('Log in') }}</h5>
                            <button type="button" class="close m-0 p-0" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="username" placeholder="{{ __('Email or Phone Number') }}" required="" name="username" value="{{ old('username')}}">
                        </div>
                        <div class="form-group" id="password-wrapper" style="display:none; position:relative">
                            <input type="password" class="form-control pr-3" name="password" placeholder="{{ __('Password') }}">
                            <a class="font-14" href="javascript:void(0)" id="send_password_reset_link" style="position:absolute; right:10px; top:7px;">Forgot?</a>
                        </div>
                        <div class="form-group">
                            <span id="error-msg" class="font-14 text-danger" style="display:none"></span>
                            <span id="success-msg" class="font-14 text-success" style="display:none"></span>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-solid w-100 login_continue_btn" type="button">Continue</button>
                        </div>

                        <div class="divider-line alOR"><span>or</span></div>
                        {{-- <button class="login-button email-btn">
                        <i class="fa fa-envelope" aria-hidden="true"></i>
                        <span>Continue with Email</span>
                    </button> --}}

                        @if(@session('preferences'))
                        @if(@session('preferences')->fb_login == 1 || @session('preferences')->twitter_login == 1 ||
                        @session('preferences')->google_login == 1 || @session('preferences')->apple_login == 1)
                        @if(@session('preferences')->google_login == 1)
                        <a class="login-button" href="{{url('auth/google')}}">
                            <i class="fa fa-google" aria-hidden="true"></i>
                            <span>Continue with gmail</span>
                        </a>
                        @endif
                        @if(@session('preferences')->fb_login == 1)
                        <a class="login-button" href="{{url('auth/facebook')}}">
                            <i class="fa fa-facebook" aria-hidden="true"></i>
                            <span>Continue with facebook</span>
                        </a>
                        @endif
                        @if(@session('preferences')->twitter_login)
                        <a class="login-button" href="{{url('auth/twitter')}}">
                            <i class="fa fa-twitter" aria-hidden="true"></i>
                            <span>Continue with twitter</span>
                        </a>
                        @endif
                        @if(@session('preferences')->apple_login == 1)
                        <a class="login-button" href="javascript::void(0);">
                            <i class="fa fa-apple" aria-hidden="true"></i>
                            <span>Continue with apple</span>
                        </a>
                        @endif
                        @endif
                        @endif

                        {{-- <div class="divider-line mb-2"></div> --}}
                        <p class="new-user mb-0"><a href="{{route('customer.register')}}">Create an
                                account</a></p>
                    </div>
                    {{-- <div class="login-with-mail">
                  <div class="modal-header px-0 pt-0">
                      <button type="button" class="close m-0 p-0 back-login">
                          <i class="fa fa-arrow-left" aria-hidden="true"></i>
                      </button>
                      <h5 class="modal-title">Log in</h5>
                      <button type="button" class="close m-0 p-0" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                      </button>
                  </div>
                  <form id="email-login-form" action="">
                      <div class="mail-icon text-center">
                          <img alt="image" class="blur-up lazyload img-fluid" data-src="https://b.zmtcdn.com/Zwebmolecules/73b3ee9d469601551f2a0952581510831595917292.png">
                      </div>
                      <div class="form-group">
                          <input class="from-control" type="text" placeholder="Email">
                          <a class="forgot_btn font-14" href="{{url('user/forgotPassword')}}">{{ __('Forgot Password?') }}</a>
            </div>
            <div class="form-group">
                <button class="btn btn-solid w-100" type="submit">Login</button>
            </div>
            </form>
        </div> --}}
        <div class="verify-login-code" style="display:none">
            <div class="modal-header px-0 pt-0">
                <button type="button" class="close m-0 p-0 back-login">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                </button>
                <h5 class="modal-title">Verify OTP</h5>
                <button type="button" class="close m-0 p-0" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div method="get" class="digit-group otp_inputs d-flex justify-content-between" data-group-name="digits" data-autosubmit="false" autocomplete="off">
                <input class="form-control" type="text" id="digit-1" name="digit-1" data-next="digit-2" onkeypress="return isNumberKey(event)" />
                <input class="form-control" type="text" id="digit-2" name="digit-2" data-next="digit-3" data-previous="digit-1" onkeypress="return isNumberKey(event)" />
                <input class="form-control" type="text" id="digit-3" name="digit-3" data-next="digit-4" data-previous="digit-2" onkeypress="return isNumberKey(event)" />
                <input class="form-control" type="text" id="digit-4" name="digit-4" data-next="digit-5" data-previous="digit-3" onkeypress="return isNumberKey(event)" />
                <input class="form-control" type="text" id="digit-5" name="digit-5" data-next="digit-6" data-previous="digit-4" onkeypress="return isNumberKey(event)" />
                <input class="form-control" type="text" id="digit-6" name="digit-6" data-next="digit-7" data-previous="digit-5" onkeypress="return isNumberKey(event)" />
            </div>
            <span class="invalid_phone_otp_error invalid-feedback2 w-100 d-block text-center text-danger"></span>
            <span id="phone_otp_success_msg" class="font-14 text-success text-center w-100 d-block" style="display:none"></span>
            <div class="row text-center mt-2">
                <div class="col-12 resend_txt">
                    <p class="mb-1">{{__('If you didn’t receive a code?')}}</p>
                    <a class="verifyPhone" href="javascript:void(0)"><u>{{__('RESEND')}}</u></a>
                </div>
                <div class="col-md-12 mt-3">
                    <button type="button" class="btn btn-solid" id="verify_phone_token">{{__('VERIFY')}}</button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
</div>
</div>

<div id="prescription_form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{__('Add Prescription')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_prescription_form" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" id="AddCardBox">
                    <div class="row">
                        <div class="col-sm-12 position-relative" id="imageInput">
                            <input type="hidden" id="vendor_idd" name="vendor_idd" value="" />
                            <input type="hidden" id="product_id" name="product_id" value="" />
                            <input type="hidden" id="uploaded_pres_count" name="uploaded_pres_count" value="" />
                            <input data-default-file="" accept="image/*" type="file" data-plugins="dropify" name="prescriptions[]" id="prescription_file" class="dropify uploaded-prescription-img" multiple />
                            <!-- <img id="uploaded-prescription" style="margin-top: 9px;display:none;" src="#"/> -->
                            <div class="uploaded-prescription"></div>
                            <p class="text-muted text-center mt-2 mb-0">{{__('Uploaded Prescription(s)')}}</p>
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                            <span class="validate-file-error text-danger"></span>
                        </div>

                    </div>
                        <div class="show-prescription-doc">
                             </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light submitPrescriptionForm">{{__('Submit')}}</button>
                </div>
            </form>
        </div>

    </div>
</div>
<div class="modal fade pick-address" id="pick_address" tabindex="-1" aria-labelledby="pick-addressLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="pick-addressLabel">{{ __('Select Location') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row">
                    <div class="col-md-12">
                        <div id="address-map-container" class="w-100" style="height: 500px; min-width: 500px;">
                            <div id="pick-address-map" class="h-100"></div>
                        </div>
                        <div class="pick_address p-2 mb-2 position-relative">
                            <div class="text-center">
                                <button type="button" class="btn btn-solid ml-auto pick_address_confirm w-100" data-dismiss="modal">{{ __('Ok') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- modal for product order form -->
<div class="modal fade product-order-form
" id="cart_product_order_form" tabindex="-1" aria-labelledby="cart_product_order_form" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <div id="cart_product-order-form-modal" style="padding-top: 30px;">
          </div>
        </div>
      </div>
    </div>
  </div>


@endsection

@section('script')
<script type="text/javascript" src="{{asset('assets/libs/jquery-clock-timepicker/jquery-clock-timepicker.js')}}"></script>
<script type="text/javascript" src="{{asset('js/cart_custom.js')}}"></script>
<script src="{{asset('js/credit-card-validator.js')}}"></script>
<script type="text/javascript">
    function handler(e) {
        $('.standard').clockTimePicker();
        $('.required').clockTimePicker({
            required: true
        });

        var CSRF_TOKEN = $("input[name=_token]").val();
        var DATE = e.target.value;
        var vendorId = "{{$vendorId}}";
        $.ajax({
                type: "post",
                url: "{{route('ajaxGetScheduleDateDetails')}}",
                data: {_token: CSRF_TOKEN, date: DATE, vendorId: vendorId},
                success: function(resp) {
                    var data=JSON.parse(resp);
                   $('.durationMinMaxPickup').clockTimePicker({
                        duration: true,
                        minimum: data.start_time,
                        maximum: data.end_time
                    });
                    $('.durationMinMaxDropoff').clockTimePicker({
                        duration: true,
                        minimum: data.start_time,
                        maximum:data.end_time

                    });
                    $("#schedule_datetime_dropoff_time").val(data.start_time);
                    $("#schedule_datetime_pickup_time").val(data.start_time);
                },
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                error: function(response) {

                        $(".show_all_error.invalid-feedback").show();
                        $(".show_all_error.invalid-feedback").text(
                            'Something went wrong, Please try Again.');

                    return response;
                }
            });


       $('.time').removeClass("d-none");
    }

</script>
<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js"></script>
<script src="https://cdn.socket.io/4.1.2/socket.io.min.js" integrity="sha384-toS6mmwu70G0fw54EGlWWeA4z3dyJ+dlXBtSURSKN4vyRFOcxd3Bzjj/AoOwY+Rg" crossorigin="anonymous">
</script>
@if(in_array('payphone',$client_payment_options))
<script src="https://pay.payphonetodoesposible.com/api/button/js?appId={{$payphone_id}}"></script>
@endif
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
<script type="text/javascript" src="https://js.yoco.com/sdk/v1/yoco-sdk-web.js"></script>
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
@if(in_array('paytabs',$client_payment_options))
<script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js"></script>
@endif
@if(in_array('khalti',$client_payment_options))
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
@endif
@if (in_array('mastercard', $client_payment_options))
    <script src="https://{{mastercardGateway()}}/static/checkout/checkout.min.js"></script>
@endif
<script src="{{ asset('assets/libs/dropzone/dropzone.min.js') }}"></script>
<script src="{{ asset('assets/libs/dropify/dropify.min.js') }}"></script>
<script type="text/javascript" src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script type="text/javascript">
    var business_type = "<?= $client_preferences->business_type; ?>";
    var scheduling_with_slots = "<?= $client_preferences->scheduling_with_slots; ?>";
    var off_scheduling_at_cart = "<?= $client_preferences->off_scheduling_at_cart; ?>";
    var payment_azulpay_url = "{{route('payment.azulpay.beforePayment')}}";
    var payment_plugnpay_url = "{{route('payment.plugnpay.beforePayment')}}";
    var payment_mpesa_safari_url = "{{route('mpesasafari.pay')}}";
</script>
<script type="text/javascript" src="{{asset('js/developer.js')}}"></script>
<script type="text/javascript" src="{{asset('js/payment.js')}}"></script>
<script type="text/javascript" src="{{asset('js/apple_pay.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.dropify').dropify({
            messages: {
                'default': "{{ __('Drag and drop a file here or click')}}",
                'replace': "{{ __('Drag and drop or click to replace')}}",
                'remove':  "{{ __('Remove')}}",
                'error':   "{{ __('Ooops, something wrong happended.')}}"
            }
        });

        $('.dropify-clear').click(function(e){
            e.preventDefault();
            $(".uploaded-prescription").empty();

        });
    });

    var stripe_fpx = '';
    var fpxBank = '';
    var idealBank = {};
    var guest_cart = {{ $guest_user ? 1 : 0 }};
        var ajaxCall = 'ToCancelPrevReq';
    var base_url = "{{url('/')}}";
    var place_order_url = "{{route('user.placeorder')}}";
    var create_konga_hash_url = "{{route('kongapay.createHash')}}";
    var create_payphone_url = "{{route('payphone.createHash')}}";
    var payphone_refund_wallet = "{{route('payphone.refund')}}";
    var create_easypaisa_hash_url = "{{route('easypaisa.createHash')}}";
    var create_windcave_hash_url = "{{route('windcave.createHash')}}";
    var create_dpo_tocken_url = "{{route('dpo.createTocken')}}";
    var create_paytech_hash_url = "{{route('paytech.createHash')}}";
    var create_flutterwave_url = "{{route('flutterwave.createHash')}}";
    var create_viva_wallet_pay_url = "{{route('vivawallet.pay')}}";
    var create_mvodafone_pay_url = "{{route('mvodafone.pay')}}";
    var create_ccavenue_url = "{{route('ccavenue.pay')}}";
    var payment_nmi_url = "{{route('nmi.pay')}}";
    var payment_obo_url = "{{route('obo.pay')}}";
    var post_payment_via_gateway_url = "{{route('payment.gateway.postPayment', ':gateway')}}";
    var payment_stripe_url = "{{route('payment.stripe')}}";
    var payment_retrive_stripe_fpx_url = "{{url('payment/retrieve/stripe_fpx')}}";
    var payment_create_stripe_fpx_url = "{{url('payment/create/stripe_fpx')}}";
    var payment_create_stripe_oxxo_url = "{{url('payment/create/stripe_oxxo')}}";
    var payment_create_stripe_ideal_url = "{{url('payment/create/stripe_ideal')}}";
    var payment_retrive_stripe_ideal_url = "{{url('payment/retrieve/stripe_ideal')}}";
    var get_product_prescription = "{{url('get/product/prescription')}}";
    var cart_clear_stripe_oxxo_url = "{{url('payment/stripe_oxxo/clear')}}";
    var user_store_address_url = "{{route('address.store')}}";
    var product_faq_update_url = "{{ route('cart.productfaq') }}";
    var promo_code_remove_url = "{{ route('remove.promocode') }}";
    var payment_paypal_url = "{{route('payment.paypalPurchase')}}";
    var payment_success_paypal_url = "{{ route('payment.paypalCompletePurchase') }}";
    var payment_paystack_url = "{{route('payment.paystackPurchase')}}";
   // var payment_success_paystack_url = "{{route('payment.paystackCompletePurchase')}}";
    var payment_payfast_url = "{{route('payment.payfastPurchase')}}";
    var payment_mobbex_url = "{{route('payment.mobbexPurchase')}}";
    var payment_yoco_url = "{{route('payment.yocoPurchase')}}";
    var payment_paylink_url = "{{route('payment.paylinkPurchase')}}";
    var payment_razorpay_url = "{{route('payment.razorpayPurchase')}}";
    var pyment_totalpay_url= "{{ route('make.payment') }}";
    var payment_thawani_url= "{{ route('pay-by-thawanipg') }}";
    var payment_checkout_url = "{{route('payment.checkoutPurchase')}}";
    var payment_khalti_url = "{{route('payment.khaltiVerification')}}";
    var payment_khalti_complete_purchase = "{{route('payment.khaltiCompletePurchase')}}";
    var update_qty_url = "{{ url('product/updateCartQuantity') }}";
    var update_cart_product_status = "{{ url('product/updateCartProductStatus') }}";
	var user_cards_url = "{{ route('payment.azulpay.getCards') }}";

    var promocode_list_url = "{{ route('verify.promocode.list') }}";
    var payment_option_list_url = "{{route('payment.option.list')}}";
    var update_cart_slot = "{{ route('updateCartSlot') }}";
    var apply_promocode_coupon_url = "{{ route('verify.promocode') }}";
    var update_cart_schedule = "{{route('cart.updateSchedule')}}";
    var verifyaccounturl = "{{route('user.verify')}}";
    var check_schedule_slots = "{{route('cart.check_schedule_slots')}}";
    var check_pickup_schedule_slots = "{{route('cart.check_pickup_schedule_slots')}}";
    var check_dropoff_schedule_slots = "{{route('cart.check_dropoff_schedule_slots')}}";
    var login_via_username_url = "{{route('customer.loginViaUsername')}}";
    var forgot_password_url = "{{route('customer.forgotPass')}}";
    var order_success_return_url = "{{route('order.return.success')}}";
    var my_orders_url = "{{route('user.orders')}}";
    var validate_promocode_coupon_url = "{{ route('verify.promocode.validate_code') }}";
    var update_cart_product_schedule = "{{route('cart.updateProductSchedule')}}";
    var post_toyyibpay_via_gateway_url = "{{route('payment.toyyibpay.index')}}";

    var latitude = "{{ session()->has('latitude') ? session()->get('latitude') : 0 }}";
    var longitude = "{{ session()->has('longitude') ? session()->get('longitude') : 0 }}";

    var get_product_faq = "{{ url('product/faq') }}";

    var get_category_kyc_document = "{{ url('category_kycDocument') }}";
    var post_category_kyc_document = "{{ route('updateCartCategoryKyc') }}";

    var passbase_page = "{{route('passbase.page')}}";
    var get_dispatch_slot = "{{ route('getSlotFromDispatchDemand') }}";
    var product_order_form_element_data = [];
    var error_Slot_is_required = "{{__('Slot is required')}}";
    var error_Schedule_date_is_required = "{{__('Schedule date time is required')}}";
    var error_Invalid_Schedule_date = "{{__('Invalid schedule date time')}}";
    var create_mtn_momo_token = "{{route('mtn.momo.createToken')}}";
    var error_unchanged_schedule_date = "{{__('Schedule date can not be changed, Because order being edited is scheduled order. In case of multi vendor, order can not be edited.')}}";
    var discard_order_editing_url = "{{route('user.discardeditorder')}}";
    var confirm_discard_edit_order_title = "{{__('Are you sure?')}}";
    var confirm_discard_edit_order_desc = "{{__('You want to discard editing Order.')}}";
    var success_error_container = ".cart_response";
    var data_trans_url = "{{route('payment.payByDataTrans')}}";
    var payment_hitpay_url="{{ route('make.hitpay.payment') }}";
    var pesapal_payment_url = "{{ route('pesapal.payment') }}";
    var powertrans_payment_url = "{{ route('powertrans.payment') }}";
    var livee_payment_url="{{route('livee.pay')}}";
    var payment_orangepay_url =  "{{ route('orangepay.initiate.payment') }}";
    var payment_cybersource_url =  "{{ route('cybersource.initiate.payment') }}";
    var mastercard_create_session_url = "{{route('payment.mastercard.createSession')}}";

    @if(!empty($client_preference_detail->is_postpay_enable))
        var post_pay_edit_order = "{{$client_preference_detail->is_postpay_enable}}";
    @else
        var post_pay_edit_order = 0;
    @endif

    if(!latitude){
        @if(!empty($client_preference_detail->Default_latitude))
            latitude = "{{$client_preference_detail->Default_latitude}}";
        @endif
    }

    if(!longitude){
        @if(!empty($client_preference_detail->Default_longitude))
            longitude = "{{$client_preference_detail->Default_longitude}}";
        @endif
    }

    $(document).on('click', '.showMapHeader', function() {
        var lats = document.getElementById('latitude').value;
        var lngs = document.getElementById('longitude').value;
        if(lats==''){
            lats=latitude;
        }
        if(lngs==''){
            lngs=longitude;
        }
        var infowindow = new google.maps.InfoWindow();
        var geocoder = new google.maps.Geocoder();
        var myLatlng = new google.maps.LatLng(lats, lngs);
        var mapProp = {
            center: myLatlng,
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.ROADMAP

        };
        //address
        var map = new google.maps.Map(document.getElementById("pick-address-map"), mapProp);
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            draggable: true
        });
        // marker drag event
        google.maps.event.addListener(marker, 'dragend', function() {
            geocoder.geocode({
            'latLng': marker.getPosition()
            }, function(results, status) {

            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                        document.getElementById('latitude').value = marker.getPosition().lat();
                        document.getElementById('longitude').value = marker.getPosition().lng();
                        document.getElementById('address').value= results[0].formatted_address;

                    infowindow.setContent(results[0].formatted_address);

                    infowindow.open(map, marker);
                }
            }
            });
        });
        // google.maps.event.addListener(marker, 'drag', function(event) {
        //     document.getElementById('latitude').value = event.latLng.lat();
        //     document.getElementById('longitude').value = event.latLng.lng();
        // });
        // //marker drag event end
        // google.maps.event.addListener(marker, 'dragend', function(event) {
        //     document.getElementById('latitude').value = event.latLng.lat();
        //     document.getElementById('longitude').value = event.latLng.lng();
        // });
        $('#pick_address').modal('show');
    });

    $(document).delegate("#vendor_table", "change", function() {
        var table = $(this).val();
        var vendor = $(this).attr('data-id');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "{{ route('addVendorTableToCart') }}",
            data: {
                table: table,
                vendor: vendor
            },
            success: function(response) {
                // console.log(response);
                if (response.status == "Success") {

                } else {
                    Swal.fire({
                    // title: "Warning!",
                    text: response.message,
                    icon : "error",
                    button: "OK",
                    });
                   // alert(response.message);
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                success_error_alert('error', response.message, ".payment_response");
            }
        });
    });


    $(document).delegate('#submit_productfaq', 'click', function() {
    var product_id = $(this).attr('data-product_id');
    var form_class = $(this).attr('data-form_class');
    var remove_div_id = $(this).attr('data-dev_remove_id');
    var product_order_form_element = getFormData('.'+form_class);
    if(product_order_form_element == 0){
        return false;
    }
    var formData = new FormData();
    formData.append("product_id", product_id);

    // Append each form field
    product_order_form_element.forEach((item, index) => {
        if (item.answer instanceof File) {
            formData.append(`user_product_order_form[${index}][answer]`, item.answer); // Append file
        } else {
            formData.append(`user_product_order_form[${index}][answer]`, item.answer); // Append text
        }
        formData.append(`user_product_order_form[${index}][question]`, item.question);
        formData.append(`user_product_order_form[${index}][product_faq_id]`, item.product_faq_id);
    });

    // Send via AJAX
    $.ajax({
        type: "POST",
        dataType: "json",
        url: product_faq_update_url,
        data: formData,
        contentType: false, // Prevents jQuery from setting incorrect content type
        processData: false, // Prevents jQuery from processing FormData
        success: function(response) {
            $("#" + remove_div_id).remove();
            window.location.reload();
        },
        error: function(data) {
            $(".product_order_form_error").html(data.responseJSON.message);
            setTimeout(function() {
                $('.product_order_form_error').html('').hide();
            }, 5000);
        }
    });

    $('#cart_product_order_form').modal('hide');

    // $.ajax({
    //     type: "POST",
    //     dataType: "json",
    //     url: product_faq_update_url,
    //     data: {product_id:product_id,user_product_order_form:product_order_form_element},
    //     success: function(response) {
    //         $("#"+remove_div_id).remove();
    //         //window.location.reload();
    //     },
    //     error: function(data) {
    //         $(".product_order_form_error").html(data.responseJSON.message);
    //         setTimeout(function() {
    //             $('.product_order_form_error').html('').hide();
    //         }, 5000);
    //     },
    // });
    //     $('#cart_product_order_form').modal('hide');
    });


    // function getFormData(dom_query){
    //     var product_order_form_element_data = [];
    //     var out = {};
    //     var s_data = $(dom_query).serializeArray();

    //     document.querySelectorAll('.form-control').forEach(function(inp) {
    //     });
    //         //transform into simple data/value object
    //         for(var i = 0; i < s_data.length; i++){
    //             var record = s_data[i];
    //             out[record.name] = record.value;
    //             var select_faq_id = $(dom_query+' select[name="'+record.name+'"]').attr('data-product_faq_id');
    //             var is_required = $(dom_query+' select[name="'+record.name+'"]').attr('data-required');

    //             if(!select_faq_id){
    //                 var product_faq_id = $(dom_query+' input[name="'+record.name+'"]').attr('data-product_faq_id');
    //                  var is_required = $(dom_query+' input[name="'+record.name+'"]').attr('data-required');
    //             }

    //             if((is_required)==1 && (record.value =='' )){
    //                 var errorMsg ="The "+ record.name +" field is required.";
    //                 $('.product_order_form_error').html(errorMsg);
    //                 return 0;
    //             } else {
    //                 $('.product_order_form_error').html('');
    //             }
    //             product_order_form_element_data.push({'question':record.name,'answer':record.value,'product_faq_id':product_faq_id})
    //         }
    //     return product_order_form_element_data;
    // }

    function getFormData(dom_query) {
    var product_order_form_element_data = [];
    var out = {};
    var s_data = $(dom_query).serializeArray();

    // Process text inputs
    for (var i = 0; i < s_data.length; i++) {
        var record = s_data[i];
        out[record.name] = record.value;
        var select_faq_id = $(dom_query + ' select[name="' + record.name + '"]').attr('data-product_faq_id');
        var is_required = $(dom_query + ' select[name="' + record.name + '"]').attr('data-required');

        if (!select_faq_id) {
            var product_faq_id = $(dom_query + ' input[name="' + record.name + '"]').attr('data-product_faq_id');
            var is_required = $(dom_query + ' input[name="' + record.name + '"]').attr('data-required');
        }

        if ((is_required) == 1 && (record.value == '')) {
            var errorMsg = "The " + record.name + " field is required.";
            $('.product_order_form_error').html(errorMsg);
            return 0;
        } else {
            $('.product_order_form_error').html('');
        }

        product_order_form_element_data.push({
            'question': record.name,
            'answer': record.value,
            'product_faq_id': product_faq_id
        });
    }

    // Process file inputs
    $(dom_query + ' input[type="file"]').each(function() {
        var fileInput = $(this)[0];

        if (fileInput.files.length > 0) {
            var file = fileInput.files[0]; // Get the first selected file
            var file_faq_id = $(this).attr('data-product_faq_id');

            product_order_form_element_data.push({
                'question': fileInput.name,
                'answer': file,  // Store file object
                'product_faq_id': file_faq_id
            });
        }
    });

    console.log(product_order_form_element_data);
    return product_order_form_element_data;
}


    $(document).delegate('#category_kyc_form_submit', 'click', function() {
    var product_id = $(this).attr('data-product_id');
    var form_class = $(this).attr('data-form_class');
    var remove_div_id = $(this).attr('data-dev_remove_id');
    var product_order_form_element = getFormDataImage('.'+form_class);
    if(product_order_form_element == 0){
        return false;
    }

    // $.ajax({
    //     type: "POST",
    //     dataType: "json",
    //     url: product_faq_update_url,
    //     data: {product_id:product_id,user_product_order_form:product_order_form_element},
    //     success: function(response) {
    //         $("#"+remove_div_id).remove();
    //         //window.location.reload();
    //     },
    //     error: function(data) {
    //         $(".product_order_form_error").html(data.responseJSON.message);
    //         setTimeout(function() {
    //             $('.product_order_form_error').html('').hide();
    //         }, 5000);
    //     },
    // });
        $('#cart_product_order_form').modal('hide');
    });


    function getFormDataImage(dom_query){
        var product_order_form_element_data = [];
        var out = {};
        var s_data = $(dom_query).serializeArray();

        // console.log(s_data);

            //transform into simple data/value object
            for(var i = 0; i < s_data.length; i++){
                var record = s_data[i];
                // console.log(record);
                out[record.name] = record.value;
                var product_faq_id = $(dom_query+' input[name="'+record.name+'"]').attr('data-product_faq_id');
                var is_required = $(dom_query+' input[name="'+record.name+'"]').attr('data-required');
                // console.log(is_required);

                if((is_required)==1 && (record.value =='' )){
                    var errorMsg ="The "+ record.name +" field is required.";
                    $('.product_order_form_error').html(errorMsg);
                    // errorMsg = document.querySelector(".product_order_form_error");
                    // errorMsg.html = "error msg";
                    // errorMsg.style.display = 'none';
                    // alert("hello");
                    return 0;
                } else {
                    $('.product_order_form_error').html('');
                }
                product_order_form_element_data.push({'question':record.name,'answer':record.value,'product_faq_id':product_faq_id})
            }
        return product_order_form_element_data;
    }

    $(document).delegate('#cart_payment_form input[name="cart_payment_method"]', 'change', function() {
        var method = $(this).attr('id');
        var code = method.replace('radio-', '');

        if (code != '' && post_pay_edit_order == 0) {
            $("#cart_payment_form .option-wrapper").addClass('d-none');
            $("#cart_payment_form ."+code+"_element_wrapper").removeClass('d-none');
        } else {
            $("#cart_payment_form .option-wrapper").addClass('d-none');
        }

        if (code == 'yoco' && post_pay_edit_order == 0) {
            // $("#cart_payment_form .yoco_element_wrapper").removeClass('d-none');
            // Create a new dropin form instance

            var yoco_amount_payable = $("input[name='cart_total_payable_amount']").val();
            inline = sdk.inline({
                layout: 'field',
                amountInCents:  yoco_amount_payable * 100,
                currency: 'ZAR'
            });
            // this ID matches the id of the element we created earlier.
            inline.mount('#yoco-card-frame');
        }
        // else {
        //     $("#cart_payment_form .yoco_element_wrapper").addClass('d-none');
        // }

        if (code == 'checkout' && post_pay_edit_order == 0) {
            // $("#cart_payment_form .checkout_element_wrapper").removeClass('d-none');
            Frames.init(checkout_public_key);
        }
        // else {
        //     $("#cart_payment_form .checkout_element_wrapper").addClass('d-none');
        // }
    });

    $(document).delegate('#login_modal', 'shown.bs.modal', function() {
        $('.login-with-mail').hide();
        $('.verify-login-code').hide();
    });

    $('.email-btn').click(function() {
        $('.login-with-mail').show();
        $('.login-with-username').hide();
    });
    $('.back-login').click(function() {
        $('.login-with-mail').hide();
        $('.verify-login-code').hide();
        $('.login-with-username').show();
    });

    var reset = function() {
        var input = document.querySelector("#username"),
            errorMsg = document.querySelector("#error-msg");
        input.classList.remove("is-invalid");
        errorMsg.innerHTML = "";
        errorMsg.style.display = 'none';
        $("#password-wrapper").hide();
        $("#password-wrapper input").removeAttr("required");
        $("#password-wrapper input").val('');
    };

    // here, the index maps to the error code returned from getValidationError - see readme
    var errorMap = ["Invalid phone number", "Invalid country code", "Phone number too short", "Phone number too long",
        "Invalid phone number"
    ];

    var iti = '';
    var phn_filter = /^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\./0-9]*$/;
    var email_filter =
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    $(document).delegate('input[name="password"]', 'input', function() {
        $(this).parent('#password-wrapper').show();
        $("#error-msg").hide();
    });

    $(document).delegate("#username", "input", function(e) {
        var uname = $.trim($(this).val());
        if (phn_filter.test(uname)) {
            // get country flags when input is a number
            assignPhoneInput();
            $("#password-wrapper").hide();
            $("#password-wrapper input").removeAttr("required");
            $("#password-wrapper input").val('');
        } else {
            // destroy country flags when input is a string
            if (iti != '') {
                iti.destroy();
                iti = '';
                $(this).css('padding-left', '6px');
            }
        }
        $(this).focus();
        $(this).removeClass("is-invalid");
        $("#error-msg").hide();
    });

    // function readPrescriptionURL(input) {
    //     if (input.files && input.files[0]) {
    //         var reader = new FileReader();
    //         $("#uploaded-prescription").css("display", "block");
    //         reader.onload = function (e) {
    //             $('#uploaded-prescription').attr('src', e.target.result).width(120).height(87);
    //         };

    //         reader.readAsDataURL(input.files[0]);
    //     }
    // }

    $(function() {
        // Multiple images preview in browser
        var imagesPreview = function(input, placeToInsertImagePreview) {

            if (input.files) {
                var filesAmount = input.files.length;

                for (i = 0; i < filesAmount; i++) {
                    var reader = new FileReader();

                    reader.onload = function(event) {
                        $($.parseHTML('<img>')).attr('src', event.target.result).width(120).height(87).css("margin", '2px').appendTo(placeToInsertImagePreview);
                    }

                    reader.readAsDataURL(input.files[i]);
                }
            }

        };

        $('.uploaded-prescription-img').on('change', function() {
            imagesPreview(this, 'div.uploaded-prescription');
        });
    });

    function assignPhoneInput() {
        var input = document.querySelector("#username");
        var country = $('#countryData').val();
        if (iti != '') {
            iti.destroy();
            iti = '';
        }
        iti = intlTelInput(input, {
            initialCountry: country,
            separateDialCode: true,
            hiddenInput: "full_number",
            utilsScript: "{{asset('assets/js/utils.js')}}",
        });
        $("input[name='full_number']").val(iti.getNumber());
    }

    $(document).delegate(".login_continue_btn, .verifyPhone", "click", function(e) {
        var uname = $.trim($("#username").val());
        var error = 0;
        var phone = $("input[name='full_number']").val();
        if (uname != '') {
            if (phn_filter.test(uname)) {
                reset();
                if (!iti.isValidNumber()) {
                    $("#username").addClass("is-invalid");
                    var errorCode = iti.getValidationError();
                    $("#error-msg").html(errorMap[errorCode]);
                    $("#error-msg").show();
                    error = 1;
                } else {
                    $("#username").removeClass("is-invalid");
                    $("#error-msg").hide();
                }
            } else {
                if (email_filter.test(uname)) {
                    $("#username").removeClass("is-invalid");
                    $("#error-msg").hide();
                    $("#password-wrapper").show();
                    $("#password-wrapper input").attr("required", true);
                    if ($("#password-wrapper input").val() == '') {
                        error = 1;
                        $("#error-msg").show();
                        $("#error-msg").html('Password field is required');
                    }
                } else {
                    error = 1;
                    $("#username").addClass("is-invalid");
                    $("#error-msg").show();
                    $("#error-msg").html('Invalid Email or Phone Number');
                }
            }
        } else {
            error = 1;
            $("#username").addClass("is-invalid");
            $("#error-msg").show();
            $("#error-msg").html('Email or Phone Number Required');
        }
        if (!error) {
            var form_inputs = $("#login-form-new").serializeArray();
            $.each(form_inputs, function(i, input) {
                if (input.name == 'full_number') {
                    input.value = phone;
                }
            });
            $.ajax({
                data: form_inputs,
                type: "POST",
                dataType: 'json',
                url: login_via_username_url,
                success: function(response) {
                    if (response.status == "Success") {
                        var data = response.data;
                        if (data.is_phone != undefined && data.is_phone == 1) {
                            $('.login-with-username').hide();
                            $('.login-with-mail').hide();
                            $('.verify-login-code').show();
                            $('.otp_inputs input').val('');
                            $('#phone_otp_success_msg').html(response.message).show();
                            setTimeout(function() {
                                $('#phone_otp_success_msg').html('').hide();
                            }, 5000);
                        } else if (data.is_email != undefined && data.is_email == 1) {
                            window.location.reload();
                        } else {
                            $("#error-msg").html('Something went wrong');
                            $("#error-msg").show();
                        }
                    }
                },
                error: function(error) {
                    var response = $.parseJSON(error.responseText);
                    // let error_messages = response.message;
                    $("#error-msg").html(response.message);
                    $("#error-msg").show();
                }
            });
        }
    });

    // $(document).ready(function() {
    //     $("#username").keypress(function(e) {
    //         if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
    //             return false;
    //         }
    //         return true;
    //     });
    // });

    $(document).delegate('.iti__country', 'click', function() {
        var code = $(this).attr('data-country-code');
        $('#countryData').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#dialCode').val(dial_code);
    });

    $("#verify_phone_token").click(function(event) {
        var verifyToken = '';
        $('.digit-group').find('input').each(function() {
            if ($(this).val()) {
                verifyToken += $(this).val();
            }
        });
        var form_inputs = $("#login-form-new").serializeArray();
        form_inputs.push({
            name: 'verifyToken',
            value: verifyToken
        });

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('customer.verifyPhoneLoginOtp') }}",
            data: form_inputs,
            success: function(response) {
                if (response.status == 'Success') {
                    window.location.reload();
                } else {
                    $(".invalid_phone_otp_error").html(response.message);
                    setTimeout(function() {
                        $('.invalid_phone_otp_error').html('').hide();
                    }, 5000);
                }
            },
            error: function(data) {
                $(".invalid_phone_otp_error").html(data.responseJSON.message);
                setTimeout(function() {
                    $('.invalid_phone_otp_error').html('').hide();
                }, 5000);
            },
        });
    });

    $('.digit-group').find('input').each(function() {
        $(this).attr('maxlength', 1);
        $(this).on('keyup', function(e) {
            var parent = $($(this).parent());
            if (e.keyCode === 8 || e.keyCode === 37) {
                var prev = parent.find('input#' + $(this).data('previous'));
                if (prev.length) {
                    $(prev).select();
                }
            } else if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || (e
                    .keyCode >= 96 && e.keyCode <= 105) || e.keyCode === 39) {
                var next = parent.find('input#' + $(this).data('next'));
                if ((next.length) && ($(this).val() != '')) {
                    $(next).select();
                } else {
                    if (parent.data('autosubmit')) {
                        parent.submit();
                    }
                }
            }
        });
    });

    $('#send_password_reset_link').click(function() {
        var that = $(this);
        var email = $('#username').val();
        $('.invalid-feedback').html('');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                "email": email
            },
            url: forgot_password_url,
            success: function(res) {
                if (res.status == "Success") {
                    $('#success-msg').html(res.message).show();
                    setTimeout(function() {
                        $('#success-msg').html('').hide();
                    }, 5000);
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                let error_messages = response.errors;
                $.each(error_messages, function(key, error_message) {
                    $('#error-msg').html(error_message[0]).show();
                });
            }
        });
    });

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }

    // Added by Ovi
    // Check Slot Availability
    function checkSlotOrders()
    {
        var url = "{{route('checkSlotOrders')}}"
        var schedule_datetime = $('#schedule_datetime').val();
        var schedule_pickup_datetime = $('#pickup_schedule_datetime').val();
        var schedule_pickup_slot = $('#schedule_pickup_slot').val();
        var schedule_slot = $('#slot').val();
        var vendor_id = $('#vendor_id').val();

        $.ajax({
            type: "GET",
            data: {
                "schedule_pickup_datetime": schedule_pickup_datetime,
                "schedule_datetime":        schedule_datetime,
                "schedule_pickup_slot":     schedule_pickup_slot,
                "schedule_slot":            schedule_slot,
                "vendor_id":                vendor_id,
            },
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(output) {
                console.log(output.orders_per_slot);
                console.log(output.orderCount);
                // Check if orderCount is greaten equal to orders_per_slot
                if( (output.orderCount >= output.orders_per_slot) && (output.orders_per_slot != 0) ){
                    success_error_alert('error', 'All slots are full for the selected date & slot please choose another date or slot.', ".cart_response");
                    // Disable the place order button
                    $('#order_placed_btn').attr("disabled", true);
                }else{
                    // Enable the place order button
                    $('#order_placed_btn').attr("disabled", false);
                }
            },
            error: function(output) {
                // console.log(output);
            },
        });
    }


    function getExtension(filename) {
        return filename.split('.').pop().toLowerCase();
    }

    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var extension = getExtension(input.files[0].name);
            reader.onload = function(e) {
                if(extension == 'pdf'){
                    $(previewId).attr('src', "{{ asset('assets/images/pdf-icon-png-2072.png') }}");
                }else if(extension == 'csv'){
                    $(previewId).attr('src',text_image);
                }else if(extension == 'txt'){
                    $(previewId).attr('src',text_image);
                }else if(extension == 'xls'){
                    $(previewId).attr('src',text_image);
                }else if(extension == 'xlsx'){
                    $(previewId).attr('src',text_image);
                }else{
                    $(previewId).attr('src',e.target.result);
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Check Slot Availability
    function checkSlotAvailability(obj)
    {

        var url = "{{route('checkSlotOrders')}}"
        var schedule_datetime = $(obj).closest('.vendor_slot_cart').find('.vendor_schedule_datetime').val();
        var schedule_slot = $(obj).val();
        var vendor_id = $(obj).data('vendor_id');
        $.ajax({
            type: "GET",
            data: {
                "schedule_datetime": schedule_datetime,
                "schedule_slot":     schedule_slot,
                "vendor_id":         vendor_id,
            },
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(output) {
                console.log("checkSlotAvai");
                // Check if orderCount is greaten equal to orders_per_slot //&& (output.orders_per_slot !=0)
                if(output.orderCount >= output.orders_per_slot  ){
                    success_error_alert('error', 'All slots are full for the selected date & slot please choose another date or slot.', ".cart_response");
                    // Disable the place order button
                    $('#order_placed_btn').attr("disabled", true);
                    return false;
                }else{
                    // Enable the place order button
                    $('#order_placed_btn').attr("disabled", false);
                }
            },
            error: function(output) {
                // console.log(output);
            },
        });
    }

    $(document).delegate('#view_all_address', 'click', function() {

        $("#view_all_address").addClass("d-none");
        $("#view_all_address").removeClass("d-block");
        $("#view_all_address_div").removeClass("d-none");

    });

    $(document).on('change', '[id^=input_file_logo_]', function(event){
        var rel = $(this).data('rel');
        // $('#plus_icon_'+rel).hide();
        readURL(this, '#upload_logo_preview_'+rel);
    });







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
@endsection
@section('script-bottom-js')
<script defer type="text/javascript"  src="{{ asset('js/giftCard/cartGiftCard.js') }}"></script>
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
