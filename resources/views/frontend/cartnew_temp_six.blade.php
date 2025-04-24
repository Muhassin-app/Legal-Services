@extends('layouts.store', ['title' => __('Cart')])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css" />

<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

<style type="text/css">
.swal2-title{margin:0;font-size:26px;font-weight:400;margin-bottom:28px}.discard_price{text-decoration:line-through;color:#6c757d}#category_kyc_form_in_cart .file--upload>label{width:100%;height:200px;border:1px solid #eee;border-radius:15px}#category_kyc_form_in_cart .file .update_pic{width:100%;height:auto;margin:auto;text-align:center;border:0;border-radius:0}#category_kyc_form_in_cart .file .update_pic img{height:130px;width:auto}.time{display:inline-block;font-size:26px;padding:5px;text-align:center;width:94px;margin-top:5px}#applepay-btn{width:100%;height:50px;display:none;border-radius:5px;margin-left:auto;margin-right:auto;margin-top:20px;background-image:-webkit-named-image(apple-pay-logo-white);background-position:50% 50%;background-color:#000;background-size:60%;background-repeat:no-repeat;color:#FFF!important}#save_prescription_form .modal-footer{display:block}.al_body_template_two .show-prescription-doc{overflow:auto;white-space:nowrap;overflow-y:hidden;width:100%}.show-prescription-close{position:relative;display:inline-block}.show-prescription-close i{position:absolute;right:-2px;top:0;font-size:11px;background:#eee;padding:1px 2px;border-radius:10px;cursor:pointer}.al_body_template_two .show-prescription-doc img{margin:2px}.al_body_template_one .vendor_slot_cart input{display:inline-block;width:52%}.al_body_template_one .vendor_slot_cart select{display:inline-block;width:45%}.grn_popop-total_amt label{font-size:12px!important}.vendor_cart-check label{display:inline-block}
@media (max-width:576px){.al_body_template_two .show-prescription-doc {width:100%;}}
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

<script type="text/template" id="address_template">
    <div class="col-md-12">
        <div class="delivery_box p-0 mb-3">
            <label class="radio m-0"><%= address.address %> <%= address.city %><%= address.state %> <%= address.pincode %>
                <input type="radio" checked="checked" name="address_id" value="<%= address.id %>">
                <span class="checkround"></span>
            </label>
        </div>
    </div>
</script>
<script type="text/template" id="empty_cart_template">
    <div class="container">
    <div class="row mt-2 mb-4 mb-lg-5">
        <div class="col-12 text-center">
            <div class="cart_img_outer" style="height:200px;">
                <img class="blur-up lazyload" data-src="{{asset('front-assets/images/empty_cart.png')}}">
            </div>
            <h3>{{__('Your Cart Is Empty!')}}</h3>
            <p>{{__('Add items to it now.')}}</p>
            <a class="btn btn-solid" href="{{url('/')}}">{{__('Continue Shopping')}}</a>
        </div>
    </div>
</div>
</script>
<div class="container cart_template_six">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h3 class="page-title text-uppercase mt-lg-4">{{__('Cart')}}</h3>
            </div>
            <div class="cart_response mt-3 mb-3 d-none">
                <div class="alert p-0" role="alert"></div>
            </div>
            @if (\Session::has('error'))
                <div class="alert alert-danger">
                    <span>{!! \Session::get('error') !!}</span>
                </div>
            @endif
        </div>
    </div>
</div>

<script type="text/template" id="cart_template">
    <%
    let fixed_fee=0;
    let fixed_fee_amount=0;
    let total_fixed_fee_amount=0;
    let price_bifurcation=0;
    let total_wallet_amount_used=0;
    let closed_store= 0;

    /* Getting other taxes */
    let tax_fixed_fee_percentage=0;
    let tax_container_charges_percentage=0;
    let tax_service_charges_percentage=0;
    let tax_delivery_charges_percentage=0;

    let product_container_charges_tax_amount=0;


    let other_taxes=0;
    let other_taxes_string="";
    _.each(cart_details.products, function(product, key){
        /*console.log(JSON.stringify(product));*/
       /* if (product.vendor.get_tax_fixed_fee != null) {
            tax_fixed_fee_percentage=product.vendor.get_tax_fixed_fee.tax_rate;
        }
        if (product.vendor.get_tax_container_charges != null) {
            tax_container_charges_percentage=product.vendor.get_tax_container_charges.tax_rate;
        }
        if (product.vendor.get_tax_service_charges != null) {
            tax_service_charges_percentage=product.vendor.get_tax_service_charges.tax_rate;
        }
        if (product.vendor.get_tax_delivery_charges != null) {
            tax_delivery_charges_percentage=product.vendor.get_tax_delivery_charges.tax_rate;
        }*/

        /* --- Vendor Tax Get Percentage ---- */
        _.each(cart_details.taxRates, function(tax, index){
            if(product.vendor.fixed_fee_tax_id!=null){
                if(product.vendor.fixed_fee_tax_id==index){
                    tax_fixed_fee_percentage=tax.tax_rate;
                }
            }

            if(product.vendor.service_charges_tax_id!=null){
                if(product.vendor.service_charges_tax_id==index){
                    tax_service_charges_percentage=tax.tax_rate;
                }
            }

            if(product.vendor.delivery_charges_tax_id!=null){
                if(product.vendor.delivery_charges_tax_id==index){
                    tax_delivery_charges_percentage=tax.tax_rate;
                }
            }

            if(product.vendor.container_charges_tax_id!=null){
                if(product.vendor.container_charges_tax_id==index){
                    tax_container_charges_percentage=tax.tax_rate;
                }
            }
        });




        /*console.log(tax_fixed_fee_percentage);
        if (product.vendor.get_tax_fixed_fee != null) {
            tax_fixed_fee_percentage=product.vendor.get_tax_fixed_fee.tax_rate;
        }tax_fixed_fee_percentage,tax_container_charges_percentage,tax_service_charges_percentage,tax_delivery_charges_percentage
        if (product.vendor.get_tax_container_charges != null) {
            tax_container_charges_percentage=product.vendor.get_tax_container_charges.tax_rate;
        }
        if (product.vendor.get_tax_service_charges != null) {
            tax_service_charges_percentage=product.vendor.get_tax_service_charges.tax_rate;
        }
        if (product.vendor.get_tax_delivery_charges != null) {
            tax_delivery_charges_percentage=product.vendor.get_tax_delivery_charges.tax_rate;
        }*/


        /*console.log("tax_fixed_fee_percentage"+tax_fixed_fee_percentage);
        console.log("tax_container_charges_percentage"+tax_container_charges_percentage);
        console.log("tax_service_charges_percentage"+tax_service_charges_percentage);
        console.log("tax_delivery_charges_percentage"+tax_delivery_charges_percentage);*/
        fixed_fee=product.vendor.fixed_fee;
        fixed_fee_amount=product.vendor.fixed_fee_amount;
        total_fixed_fee_amount=parseFloat(total_fixed_fee_amount)+parseFloat(product.vendor.fixed_fee_amount);
        price_bifurcation=product.vendor.price_bifurcation;
        if ( cart_details.wallet_amount_used > 0  ) {
            total_wallet_amount_used=parseFloat(total_wallet_amount_used)+parseFloat(cart_details.wallet_amount_used);
        }

        other_taxes=(parseFloat(total_fixed_fee_amount)*tax_fixed_fee_percentage/100)+(parseFloat(cart_details.total_service_fee)*tax_service_charges_percentage/100)+(parseFloat(cart_details.all_vendor_deliver_charges)*tax_delivery_charges_percentage/100);
        other_taxes_string='tax_fixed_fee:'+(parseFloat(total_fixed_fee_amount)*tax_fixed_fee_percentage/100)+',tax_service_charges:'+(parseFloat(cart_details.total_service_fee)*tax_service_charges_percentage/100)+',tax_delivery_charges:'+(parseFloat(cart_details.all_vendor_deliver_charges)*tax_delivery_charges_percentage/100);

        %>
        <div id="thead_<%= product.vendor.id %>">
            <div class="row">
                <div class="col-12">
                    <h5 class="m-0"><b><%= product.vendor.name %><%= product.fix_fee_tax %></b></h5>
                    <input type="hidden" name="category_name" id="category_name" value= "<%= product.vendor.name %>" />
                </div>
                <div class="col-12">
                    <div class="countdownholder alert-danger" id="min_order_validation_error_<%= product.vendor.id %>" style="display:none;">Your cart will be expired in </div>
                </div>
                <% if( product.is_vendor_closed == 1 && product.closed_store_order_scheduled == 0 ) {
                    closed_store= 1; %>
                    <div class="col-12">
                        <div class="text-danger">
                            <i class="fa fa-exclamation-circle"></i>{{getNomenclatureName('Vendors', true) . __(' is not accepting orders right now.')}}
                        </div>
                    </div>
                <% }else if( product.is_vendor_closed == 1 && product.closed_store_order_scheduled == 1 ){ %>
                    <div class="col-12">
                        <div class="text-danger">
                            <i class="fa fa-exclamation-circle"></i> {{__('We are not accepting orders right now. You can schedule this for ')}}<%= product.delaySlot %>
                        </div>
                    </div>
                <% } %>

                <% if( (parseFloat(product.vendor.order_min_amount) > 0) &&  (parseFloat(cart_details.total_payable_amount)+parseFloat(total_wallet_amount_used) < parseFloat(product.vendor.order_min_amount)) ) { %>
                    <div class="col-12" id="MOV_Notification">
                        <div class="text-danger">
                            <i class="fa fa-exclamation-circle"></i> {{__('We are not accepting orders less then ')}} {{Session::get('currencySymbol')}}<%= Helper.formatPrice(product.vendor.order_min_amount) %>
                        </div>

                    </div>
                <% } %>
                <div id="mov" style="display:none;"><%= product.vendor.order_min_amount %> </div>
                <% if( (product.isDeliverable != undefined) && (product.isDeliverable == 0) ) { %>
                    <div class="col-12">
                        <div class="text-danger">
                            <i class="fa fa-exclamation-circle"></i> Products for this vendor are not deliverable at your area. Please change address or remove product.
                        </div>
                    </div>
                <% } %>

            </div>
        </div>
        <hr class="mt-2">
        <div id="tbody_<%= product.vendor.id %>">
            <% _.each(product.vendor_products, function(vendor_product, vp){%>
                <div class="row  align-items-md-center vendor_products_tr alFourTemplateCartPage" id="tr_vendor_products_<%= vendor_product.id %>">
                    <div class="product-img col-3 col-md-2 pr-0">
                        <% if(vendor_product.pvariant.media_one) { %>
                            <img class='blur-up lazyload w-100' data-src="<%= vendor_product.pvariant.media_one.pimage.image.path.proxy_url %>200/200<%= vendor_product.pvariant.media_one.pimage.image.path.image_path %>">
                        <% }else if(vendor_product.pvariant.media_second && vendor_product.pvariant.media_second.image != null){ %>
                            <img class='blur-up lazyload w-100' data-src="<%= vendor_product.pvariant.media_second.image.path.proxy_url %>200/200<%= vendor_product.pvariant.media_second.image.path.image_path %>">
                        <% }else{ %>
                            <img class='blur-up lazyload w-100' data-src="<%= vendor_product.image_url %>">
                        <% } %>
                    </div>
                    <div class="col-9 col-md-10">
                        <div class="row align-items-md-center">
                            <div class="col-md-3 order-md-1">
                                <h4><%= vendor_product.product.category_name.name %></h4>
                                <h4 class="mt-0 mb-1" style="word-wrap: break-word; line-height:20px"><strong><%= vendor_product.product.translation_one ? vendor_product.product.translation_one.title :  vendor_product.product.sku %></strong></h4>
                                <input type="hidden" name="hidden_product_name" id="hidden_product_name" value= "<%= vendor_product.product.translation_one ? vendor_product.product.translation_one.title :  vendor_product.product.sku %>" />
                                <% _.each(vendor_product.pvariant.vset, function(vset, vs){%>
                                    <% if(vset.variant_detail.trans) { %>
                                        <label><span><b><%= vset.variant_detail.trans.title %>:</b></span> <%= vset.option_data.trans.title %></label>
                                    <% } %>
                                <% }); %>
                            </div>
                            <div class="col-6 col-md-2 mb-1 mb-md-0 order-md-2">
                                <span class="alFourTempTitle">Price</span>
                                <div class="items-price">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor_product.pvariant.price * vendor_product.pvariant.multiplier) %></div>
                            </div>
                            <div class="col-6 col-md-2 text-left order-md-4">
                                <span class="alFourTempTitle">Total</span>
                                <div class="items-price">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor_product.quantity_price) %></div>
                            </div>
                            <div class="col-10 col-md-4 text-md-center order-md-3">
                                <div class="number d-flex justify-content-md-center">
                                    <div class="counter-container d-flex align-items-center">
                                        <span class="minus qty-minus" data-minimum_order_count="<%= vendor_product.product.minimum_order_count %>"
                                        data-batch_count="<%= vendor_product.product.batch_count %>" data-id="<%= vendor_product.id %>" data-base_price=" <%= vendor_product.pvariant.price %>" data-vendor_id="<%= vendor_product.vendor_id %>">
                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                        </span>
                                        <input placeholder="1" type="text" data-minimum_order_count="<%= vendor_product.product.minimum_order_count %>"
                                        data-batch_count="<%= vendor_product.product.batch_count %>" value="<%= vendor_product.quantity %>" class="input-number" step="0.01" id="quantity_<%= vendor_product.id %>" readonly>
                                        <span class="plus qty-plus" data-minimum_order_count="<%= vendor_product.minimum_order_count %>"
                                            data-batch_count="<%= vendor_product.product.batch_count %>" data-id="<%= vendor_product.id %>" data-base_price=" <%= vendor_product.pvariant.price %>">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </div>
                                <% if(cart_details.pharmacy_check == 1){ %>
                                    <% if(vendor_product.product.pharmacy_check == 1){ %>
                                        <button type="button" class="btn btn-solid prescription_btn mt-2" data-cart="<%= vendor_product.cart_id %>" data-product="<%= vendor_product.product.id %>" data-vendor_id="<%= vendor_product.vendor_id %>">Add Prescription</button>
                                        <% if(vendor_product.cart_product_prescription > 0){ %>
                                            <h4 class="mt-0 mb-1" style="word-wrap: break-word; line-height:20px"><strong><%= vendor_product.cart_product_prescription %> Prescription Added</strong></h4>
                                        <% } %>
                                    <% } %>
                                <% } %>
                            </div>
                            <div class="col-2 col-md-1 text-right text-md-center p-in order-md-5">
                                <a class="action-icon d-block remove_product_via_cart" data-product="<%= vendor_product.id %>" data-vendor_id="<%= vendor_product.vendor_id %>">
                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                        <% if(vendor_product.addon.length != 0) { %>
                            <hr class="my-2">
                            <div class="row align-items-md-center">
                                <div class="col-12">
                                    <h6 class="m-0 pl-0"><b>{{__('Add Ons')}}</b></h6>
                                </div>
                            </div>
                            <% _.each(vendor_product.addon, function(addon, ad){%>
                            <% if(addon.option){%>
                                <div class="row">
                                    <div class="col-md-3 col col-sm-4 items-details">
                                        <p class="p-0 m-0"><%= addon.option.title %></p>
                                    </div>
                                    <div class="col-md-6 col col-sm-4">
                                        <div class="extra-items-price">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(addon.option.price_in_cart * addon.option.multiplier) %></div>
                                    </div>
                                    <div class="col-md-3 col col-sm-4">
                                        <div class="extra-items-price">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(addon.option.quantity_price) %></div>
                                    </div>
                                </div>
                            <% } %>
                            <% }); %>
                        <% } %>

                        <% if(vendor_product.pvariant.container_charges > 0){%>
                            <div class="row">
                                <div class="col-md-3 col-sm-4 items-details text-left">
                                    <p class="p-0 m-0 alert-danger">{{ __('Container Charges') }} *</p>
                                </div>
                                <div class="col-md-2 col-sm-4 text-center">
                                    <div class="extra-items-price">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor_product.pvariant.container_charges) %>
                                    <%

                                    /* --- Vendor Tax Get Percentage ---- */
                                    _.each(cart_details.taxRates, function(tax, index){
                                        if(vendor_product.product.container_charges_tax_id!=null){
                                            if(vendor_product.product.container_charges_tax_id==index){
                                                product_container_charges_tax_amount+=parseFloat(vendor_product.pvariant.container_charges)*parseFloat(tax.tax_rate)/100;
                                            }
                                        }
                                    });
                                     %></div>
                                </div>
                                <div class="col-md-7 col-sm-4 text-right">
                                    <div class="extra-items-price">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor_product.quantity_container_charges) %></div>
                                </div>
                            </div>
                        <% } %>
                    </div>

                    <% if( (vendor_product.product.delay_order_time.delay_order_hrs != undefined && vendor_product.product.delay_order_time.delay_order_min != undefined ) &&  ((vendor_product.product.delay_order_time.delay_order_hrs != 0) || (vendor_product.product.delay_order_time.delay_order_hrs != 0))) { %>
                        <div class="col-12">
                            <div class="text-danger" style="font-size:12px;">
                                <i class="fa fa-exclamation-circle"></i>Preparation Time is
                                <% if(vendor_product.product.delay_order_time.delay_order_hrs > 0) { %>
                                    <%= vendor_product.product.delay_order_time.delay_order_hrs %> Hrs
                                <% } %>
                                <% if(vendor_product.product.delay_order_time.delay_order_min > 0) { %>
                                    <%= vendor_product.product.delay_order_time.delay_order_min %> Minutes
                                <% } %>
                            </div>
                        </div>
                    <% } %>
                    <% if( (vendor_product.product_out_of_stock == 1 ) ) { %>
                        <div class="col-12">
                            <div class="text-danger" style="font-size:12px;">
                                <i class="fa fa-exclamation-circle"></i>{{__("This Product is out of stock")}}
                            </div>
                        </div>
                    <% } %>
                    @if($client_preference_detail->product_order_form ==1)
                        <% if( (vendor_product.faq_count > 0 ) && (vendor_product.user_product_order_form == '' || vendor_product.user_product_order_form == null ) ) { %>
                        <div class=" col-3 <%= vendor_product.faq_count %>  " id="product_faq_dev_<%= vendor_product.product_id %>">
                            <input type="hidden" name="product_faq_ids" value="<%= vendor_product.product_id %>">
                            <div class="text-center my-3 btn-product-order-form-div">
                                <button class="clproduct_cart_order_form btn btn-solid w-100" id="add__cart_product_form" data-dev_remove_id="product_faq_dev_<%= vendor_product.product_id %>" data-product_id="<%= vendor_product.product_id %>"  data-vendor_id="<%= vendor_product.vendor_id %>">{{$nomenclatureProductOrderForm}}</button>
                            </div>
                        </div>
                        <% } %>
                    @endif

                </div>
                <input type="hidden" name="cart_product_ids[]" value="<%= vendor_product.id %>">

                <hr class="my-1">
            <% }); %>
            <div class="row">
                 @if(!$guest_user)
                <div class="col-lg-6 ">
                <% if(product.is_promo_code_available > 0) { %>
                    <div class="coupon_box w-100 d-flex align-content-center">
                        <img class="blur-up lazyload" data-src="{{ asset('assets/images/discount_icon.svg') }}">
                        <label class="mb-0 ml-2">
                            <% if(product.coupon) { %>
                                <%= product.coupon.promo.name %>
                            <% }else{ %>
                                <a href="javascript:void(0)" class="promo_code_list_btn ml-1" data-vendor_id="<%= product.vendor.id %>" data-cart_id="<%= cart_details.id %>" data-amount="<%= product.product_sub_total_amount  %>">{{__('Select a promo code')}}</a>
                            <% } %>
                        </label>
                    </div>
                    <% if(product.coupon) { %>
                        <label class="p-1 m-0"><a href="javascript:void(0)" class="remove_promo_code_btn ml-1" data-coupon_id="<%= product.coupon ? product.coupon.promo.id : '' %>" data-cart_id="<%= cart_details.id %>">Remove</a></label>
                    <% } %>
                <% } %>
                </div>
                @endif
                <div class="col-lg-6">
                    <% if(product.delOptions) { %>
                        <div class="row mb-1 d-flex align-items-center   <% if( product.promo_free_deliver == 1  ) { %> <%= product.promo_free_deliver %> org_price <%}%> ">
                            <div class="col-5 text-lg-right">
                                <label class="m-0 radio">
                                    {{__('Delivery Fee')}} :</label>
                                </div>
                            <div class="col-7">
                                <%= product.delOptions %>
                            </div>
                        </div>
                    <% } %>
                    <% if(product.vendor.fixed_fee_amount>0) { %>
                        <div class="row mb-1 d-flex align-items-center">
                            <div class="col-5 text-lg-right">
                                <label class="m-0 radio">
                                    {{__($fixedFee)}} :</label>
                                </div>
                            <div class="col-7">
                            <%= product.vendor.fixed_fee_amount %>
                            </div>

                        </div>
                    <% } %>

                    {{-- Home Service Schedual code Start at down --}}
                    <% if((cart_details.closed_store_order_scheduled == 1 || client_preference_detail.off_scheduling_at_cart != 1) && cart_details.vendorCnt > 1) { %>
                        @if($client_preference_detail->business_type != 'laundry')
                        <div class="row mb-1 d-flex align-items-center" style="<%= ((product.schedule_type == 'schedule') ? '' : 'display:none!important') %>">
                            <div class="col-5 text-lg-right">
                                <label class="m-0 radio">
                                    {{__('Scheduled Slot')}} :</label>
                                </div>
                            <div class="col-7 vendor_slot_cart">
                                <% if(product.slotsCnt != 0) {%>
                                    <input type="date" class="form-control vendor_schedule_datetime" placeholder="Inline calendar" data-schedule_type="date" data-vendor_id="<%=  product.vendor_id %>" data-cart_product_id="<%=  product.cart_product_id %>" value="<%=  ((product.scheduled_date_time != '')?product.scheduled_date_time : product.delay_date ) %>"  min="<%= ((product.delay_date != '0') ? product.delay_date : '') %>" >
                                    <select onchange="checkSlotAvailability(this);" class="form-control vendor_schedule_slot" id="vendor_schedule_slot_<%=  product.vendor_id %>" data-schedule_type="time" data-vendor_id="<%=  product.vendor_id %>" data-cart_product_id="<%=  product.cart_product_id %>" >
                                        <option value="">{{__("Select Slot")}} </option>
                                        <% _.each(product.slots, function(slot, sl){%>
                                            <option value="<%= slot.value  %>" <%= slot.value == product.selected_slot ? 'selected' : '' %> ><%= slot.name %></option>
                                        <% }) %>
                                    </select>
                                <% } %>
                            </div>
                        </div>
                        @endif
                    <% } %>

                    {{-- Home Service Schedual code end at down --}}

                    <div class="row mb-1">
                        <div class="col-5 text-lg-right">
                            <% if(product.coupon_amount_used > 0) { %>
                                <label class="m-0 radio">{{__('Coupon Discount')}} :</label>
                            <% } %>
                        </div>
                        <div class="col-7 text-right">
                            <% if(product.coupon_amount_used > 0) { %>
                                <p class="total_amt m-0">{{Session::get('currencySymbol')}} <%= Helper.formatPrice(product.coupon_amount_used) %></p>
                                <% } %>
                        </div>
                    </div>

                    <div class="row">
                        <% if(cart_details.vendorCnt>1) { %>
                            <div class="col-5 text-lg-right">
                                <label class="m-0 radio">{{__('Sub Total')}} :</label>
                            </div>
                            <div class="col-7 text-right">
                                <p class="total_amt m-0">{{Session::get('currencySymbol')}} <%= Helper.formatPrice(parseFloat(product.product_total_amount)+parseFloat(product.vendor.fixed_fee_amount)) %></p>
                            </div>

                            <% } %>
                    </div>

                </div>
            </div>
        </div>
        <hr class="my-1">
    <% }); %>
    <div class="row">
    <input type="hidden" name="without_category_kyc" value="<%= cart_details.without_category_kyc %>">
        @if($client_preference_detail->category_kyc_documents ==1)
            <% if( (cart_details.category_kyc_count > 0 ) ) { %>
            <div class=" col-3 <%= cart_details.category_kyc_count %>  " id="category_kyc_dev_<%= cart_details.category_rendem_id %>">
                <input type="hidden" name="category_kyc_ids" value="<%= cart_details.category_rendem_id %>">
                <div class="text-center my-3 btn-category_kyc-div">
                    <button class="cl_category_kyc_form btn btn-solid w-100" id="add__category_kyc_form" data-dev_remove_id="category_kyc_dev_<%= cart_details.category_rendem_id %>" data-category_id="<%= cart_details.category_ids %>" >{{__('Order Documents')}}</button>
                </div>
            </div>

            <% } %>
        @endif
        <div class="col-12">
            @php
             //dd($data);
            @endphp
            @if(isset($cart) && !empty($cart) && $client_preference_detail->business_type == 'laundry')
            <div class="row">
                <div class="col-4">{{__('Comment for Pickup Driver ')}}</div>
                <div class="col-8"><input class="form-control" type="text" placeholder="{{__('Eg. Please reach before time if possible')}}" id="comment_for_pickup_driver" value ="{{$cart->comment_for_pickup_driver??''}}" name="comment_for_pickup_driver"></div>
            </div>
            <hr class="my-2">
            <div class="row">
                <div class="col-4">{{__('Comment for Dropoff Driver ')}}</div>
                <div class="col-8"><input class="form-control" type="text" placeholder="{{__('Eg. Do call me before drop off')}}" id="comment_for_dropoff_driver" value ="{{$cart->comment_for_dropoff_driver??''}}"  name="comment_for_dropoff_driver"></div>
            </div>
            <hr class="my-2">
            <div class="row">
                <div class="col-4">{{__('Comment for Vendor ')}}</div>
                <div class="col-8"><input class="form-control" type="text"  placeholder="{{__('Eg. Please do the whites separately')}}" id="comment_for_vendor" value ="{{$cart->comment_for_vendor??''}}"  name="comment_for_vendor"></div>
            </div>

            <hr class="my-2">
                @if($client_preference_detail->scheduling_with_slots == 1 && $client_preference_detail->off_scheduling_at_cart == 0 )
                    <div class="row">
                        <div class="col-md-6">
                            <label for="">{{__('Schedule Pickup ')}}</label> <span class="loaderforjs"><img class="img-fluid" style="display:none;" id="loaderforjs" src="{{asset('front-assets/images/loading.gif')}}" alt=""></span>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="hidden" class="custom-control-input check" id="vendor_id" name="vendor_id" value="<%= cart_details.vendor_id %>" >
                                    @if($client_preference_detail->same_day_delivery_for_schedule == 0)
                                        <input type="date" id="pickup_schedule_datetime" class="form-control pickup_schedule_datetime" placeholder="Inline calendar" min="<%= cart_details.delay_date %>" >
                                    @else
                                        <input type="date" id="pickup_schedule_datetime" class="form-control pickup_schedule_datetime" placeholder="Inline calendar" value="<%=  ((cart_details.scheduled_date_time != '')?cart_details.scheduled_date_time : cart_details.delay_date ) %>"  min="<%= cart_details.delay_date %>" >
                                    @endif
                                    <input type="hidden" id="checkPickUpSlot" value="1">
                                </div>
                                <div class="col-md-6 schedule_pickup_slot">
                                    <select name="schedule_pickup_slot" id="schedule_pickup_slot" class="form-control"  @if($client_preference_detail->isolate_single_vendor_order == 0) onchange="checkSlotOrders();" @endif>
                                        <option value="" selected>{{__("Select Slot")}} </option>
                                        @if($client_preference_detail->same_day_delivery_for_schedule == 1)
                                            <% _.each(cart_details.slotsForPickup, function(slot, sl){%>
                                                <option value="<%= slot.value  %>" <%= slot.value == cart_details.scheduled.slot ? 'selected' : '' %> ><%= slot.name %></option>
                                            <% }) %>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="">{{__('Schedule Dropoff ')}} </label> <span class="loaderfordrop"><img class="img-fluid" style="display:none;" id="loaderfordrop" src="{{asset('front-assets/images/loading.gif')}}" alt=""></span>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" id="dropoff_schedule_datetime" class="form-control dropoff_schedule_datetime" placeholder="Inline calendar" value="<%=  ((cart_details.dropoff_scheduled_date_time != '')?cart_details.dropoff_scheduled_date_time : cart_details.my_dropoff_delay_date ) %>"  min="<%= cart_details.my_dropoff_delay_date %>" >
                                    <input type="hidden" id="checkDropoffSlot" value="1">
                                </div>
                                <div class="col-md-6 schedule_dropoff_slot">
                                    <select name="schedule_dropoff_slot" id="schedule_dropoff_slot" class="form-control">
                                        <option value="" selected>{{__("Select Slot")}} </option>
                                        @if($client_preference_detail->same_day_delivery_for_schedule == 1)
                                            <% _.each(cart_details.slotsForDropoff, function(slot, sl){%>
                                                <option value="<%= slot.value  %>" <%= slot.value == cart_details.scheduled.slot ? 'selected' : '' %> ><%= slot.name %></option>
                                            <% }) %>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @else
            <div class="row">

                <div class="col-12 alFourSpecificInstructions">
                    {{__('Specific instructions')}}
                    <input class="form-control" type="text"  placeholder="{{__('Do you want to add any instructions?')}}" id="specific_instructions" value ="{{$cart->specific_instructions??''}}"  name="specific_instructions">
                </div>
            </div>
           @endif

        </div>
        <div class="offset-lg-5 col-lg-7 offset-xl-6 col-xl-6 mt-3">

            <% if(cart_details.sub_total > 0 ) { %>
                <div class="row">
                    <div class="col-6">{{__('Sub Total')}}</div>
                    <div class="col-6 text-right"><b> {{Session::get('currencySymbol')}}<%= Helper.formatPrice(cart_details.sub_total) %></b></div>
                </div>
                <hr class="my-2">
            <% } %>
        <% if(cart_details.total_service_fee > 0 && price_bifurcation!=1) { %>
                <div class="row">
                    <div class="col-6">{{__('Service Fee')}}</div>
                    <div class="col-6 text-right"><b> {{Session::get('currencySymbol')}}<%= Helper.formatPrice(cart_details.total_service_fee) %></b></div>
                </div>
                <hr class="my-2">
            <% } %>

            <% if(total_fixed_fee_amount > 0 && price_bifurcation!=1) { %>
                <div class="row">
                    <div class="col-6">{{__($fixedFee)}}</div>
                    <div class="col-6 text-right"><b>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(total_fixed_fee_amount) %></b></div>
                    <input type="hidden" name="total_fixed_fee_amount" data-curr="{{Session::get('currencySymbol')}}" value="<%= total_fixed_fee_amount %>">
                </div>
                <% } %>
                {{--
                <% if(cart_details.total_container_charges > 0 && price_bifurcation!=1) { %>
                <hr class="my-2">
                <div class="row">
                    <div class="col-6">{{__('Total Container Charges')}}</div>
                    <div class="col-6 text-right"><b>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(cart_details.total_container_charges) %></b></div>
                </div>
                <hr class="my-2">
            <% } %>
            --}}
            <%
            if(product_container_charges_tax_amount>0){
                other_taxes=other_taxes+product_container_charges_tax_amount;
                other_taxes_string=other_taxes_string+',tax_product_container_charges:'+product_container_charges_tax_amount;
                console.log('product');
            }else if(tax_container_charges_percentage>0){
                console.log('vendor');
                other_taxes=other_taxes+(parseFloat(cart_details.total_container_charges)*tax_container_charges_percentage/100);
                other_taxes_string=other_taxes_string+',tax_vendor_container_charges:'+(parseFloat(cart_details.total_container_charges)*tax_container_charges_percentage/100);
            }
            %>
            <input type="hidden" id="other_taxes_string" value="<%= other_taxes_string %>">


            <% if(price_bifurcation!=1){ %>
            <hr class="my-2">
            <div class="row">
                <div class="col-6">{{__('Total')}}</div>
                <div class="col-6 text-right"><b>{{Session::get('currencySymbol')}}<span id="gross_amount"><%= Helper.formatPrice(parseFloat(cart_details.gross_amount)) %></b></span>
                <span id="other_taxes" style="display:none;"><%= other_taxes %></span></div>
            </div>
            <hr class="my-2">
            <% } %>

            <% if(price_bifurcation!=1){  %>
                <div class="row">
                    <div class="col-6">{{__('Tax')}}</div>
                    <div class="col-6 text-right"><b>{{Session::get('currencySymbol')}}<span id="total_taxable_amount"><%= Helper.formatPrice(parseFloat(cart_details.total_taxable_amount)+parseFloat(other_taxes)) %></span></b></div>
                </div>
            <hr class="my-2">
            <% } %>
            <% if(cart_details.total_subscription_discount != undefined) { %>

                <div class="row">
                    <div class="col-6">{{__('Subscription Discount')}}</div>
                    <div class="col-6 text-right"><b> - {{Session::get('currencySymbol')}}<span id="total_subscription_discount"><%= Helper.formatPrice(cart_details.total_subscription_discount) %><span></b></div>
                </div>
                <hr class="my-2">
            <% } %>
            <% if(cart_details.loyalty_amount > 0 && price_bifurcation!=1) { %>

                <div class="row">
                    <div class="col-6">{{__('Loyalty Amount')}}</div>
                    <div class="col-6 text-right"><b> - {{Session::get('currencySymbol')}}<span id="loyalty_amount"><%= Helper.formatPrice(cart_details.loyalty_amount) %></span></b></div>
                </div>
                <hr class="my-2">
            <% } %>
            <% if(cart_details.wallet_amount_used > 0) { %>
                <div class="row">
                    <div class="col-6">{{__('Wallet Amount')}}</div>
                    <div class="col-6 text-right" id="wallet_amount_used"> - {{Session::get('currencySymbol')}}<%= Helper.formatPrice(parseFloat(cart_details.wallet_amount_used)+parseFloat(other_taxes)) %></div>
                    <div class="col-6 text-right" id="wallet_amount_used_fixed" style="display:none"><%= parseFloat(cart_details.wallet_amount_used)+parseFloat(other_taxes) %></div>
                    <div class="col-6 text-right" id="wallet_amount_available" style="display:none"><%= cart_details.wallet_amount_available %></div>
                </div>
                <hr class="my-2">
            <% }else{ %>
                <div class="col-6 text-right" id="wallet_amount_used" style="display:none">0</div>
                <% } %>

            <% if(client_preference_detail.tip_before_order == 1) { %>
            <div class="row">
                <div class="col-12">
                    <div class="mb-2">@if(getNomenclatureName('Want To Tip', true)!='Want To Tip') {{ getNomenclatureName('Want To Tip', true) }} @else {{__('Do you want to give a tip?')}} @endif</div>
                    <div class="tip_radio_controls">
                        <% if(cart_details.total_payable_amount > 0) { %>
                            <input type="radio" class="tip_radio" id="control_01" name="select" value="<%= cart_details.tip_5_percent %>" <% if(client_preference_detail.auto_implement_5_percent_tip == 1) { %> checked <% } %>>
                            <label class="tip_label" for="control_01">
                                <h5 class="m-0" id="tip_5">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(cart_details.tip_5_percent)  %></h5>
                                <p class="m-0">5%</p>
                            </label>

                            <input type="radio" class="tip_radio" id="control_02" name="select" value="<%= cart_details.tip_10_percent %>" >
                            <label class="tip_label" for="control_02">
                                <h5 class="m-0" id="tip_10">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(cart_details.tip_10_percent) %></h5>
                                <p class="m-0">10%</p>
                            </label>

                            <input type="radio" class="tip_radio" id="control_03" name="select" value="<%= cart_details.tip_15_percent %>" >
                            <label class="tip_label" for="control_03">
                                <h5 class="m-0" id="tip_15">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(cart_details.tip_15_percent) %></h5>
                                <p class="m-0">15%</p>
                            </label>

                            <input type="radio" class="tip_radio" id="custom_control" name="select" value="custom" >
                            <label class="tip_label" for="custom_control">
                                <h5 class="m-0">{{__('Custom')}}<br>{{__('Amount')}}</h5>
                            </label>
                        <% } %>
                    </div>
                    <div class="custom_tip my-1 <% if(cart_details.total_payable_amount > 0) { %> d-none <% } %>">
                        <input class="input-number form-control" name="custom_tip_amount" id="custom_tip_amount" placeholder="{{ __('Enter Custom Amount') }}" type="number" value="" step="0.1">
                    </div>
                </div>
            </div>
            <hr class="my-2">

            <% } %>
            <% if(client_preference_detail.gifting == 1) { %>
                <div class="row">
                    <div class="col-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" style="margin-left: 10px;"  id="is_gift" name="is_gift" value="1">

                                <label class="custom-control-label" for="is_gift"><img class="pr-1 align-middle blur-up lazyload" data-src="{{ asset('assets/images/gifts_icon.png') }}" alt=""> <span class="align-middle pt-1"> {{__('Does this include a gift?')}}</span></label>
                            </div>
                    </div>
                </div>
                <hr class="my-2">
            <% } %>
            <div class="row">
                <div class="col-6">
                    <p class="total_amt m-0">{{__('Amount Payable')}} <small>(incl. tax)</small> </p>
                </div>




                <div class="col-6 text-right">
                    <% if(client_preference_detail.auto_implement_5_percent_tip == 1) { %>
                        <% if(parseFloat(cart_details.wallet_amount_used) > 0) { %>
                            <p class="total_amt m-0" id="cart_total_payable_amount" data-cart_id="<%= cart_details.id %>">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(parseFloat(cart_details.total_payable_amount)+parseFloat(cart_details.tip_5_percent)) %></p>
                        <% } else { %>
                            <p class="total_amt m-0" id="cart_total_payable_amount" data-cart_id="<%= cart_details.id %>">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(parseFloat(cart_details.total_payable_amount)+parseFloat(cart_details.tip_5_percent)+parseFloat(other_taxes)) %></p>
                        <% } %>
                        <input type="hidden" name="cart_tip_amount" id="cart_tip_amount" value="<%= Helper.formatPrice(cart_details.tip_5_percent) %>">
                                <input type="hidden" name="cart_total_payable_amount" value="<%= parseFloat(cart_details.total_payable_amount)+parseFloat(cart_details.tip_5_percent)+parseFloat(other_taxes) %>" <% if(cart_details.stripe_fpx_client_secret != undefined) { %> data-client_secret="<%= cart_details.stripe_fpx_client_secret %>" <% } %>>

                        <% }else{ %>
                            <% if(parseFloat(cart_details.wallet_amount_used) > 0) { %>
                                <p class="total_amt m-0" id="cart_total_payable_amount" data-cart_id="<%= cart_details.id %>">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(parseFloat(cart_details.total_payable_amount)+parseFloat(other_taxes)) %></p>
                            <% } else { %>
                                <p class="total_amt m-0" id="cart_total_payable_amount" data-cart_id="<%= cart_details.id %>">{{Session::get('currencySymbol')}}<%= Helper.formatPrice(parseFloat(cart_details.total_payable_amount)+parseFloat(other_taxes)) %></p>
                            <% } %>
                            <input type="hidden" name="cart_tip_amount" id="cart_tip_amount" value="0">
                                    <input type="hidden" name="cart_total_payable_amount" value="<%= parseFloat(cart_details.total_payable_amount)+parseFloat(other_taxes) %>" <% if(cart_details.stripe_fpx_client_secret != undefined) { %> data-client_secret="<%= cart_details.stripe_fpx_client_secret %>" <% } %>>
                            <%
                        } %>
                        <div>
                        <input type="hidden" name="cart_payable_amount_original" id="cart_payable_amount_original" data-curr="{{Session::get('currencySymbol')}}" value="<%= parseFloat(cart_details.total_payable_amount)+parseFloat(other_taxes) %>">
                    </div>


                </div>
            </div>
            <hr class="my-2">



        </div>

    </div>
    {{-- Schedual code Start at down --}}
            <% if((cart_details.closed_store_order_scheduled == 1 || client_preference_detail.off_scheduling_at_cart != 1) && cart_details.vendorCnt==1) { %>
                @if($client_preference_detail->business_type != 'laundry')
            <div class="row arabic-lng position-relative mb-2" id="dateredio">
                <div class=" col-md-12 mb-2 mb-md-0 text-right">
                    <div class="login-form">
                        <ul class="list-inline ml-auto d-flex align-items-center justify-content-end">
                            <li class="d-inline-block mr-1">
                                <input type="hidden" class="custom-control-input check" id="vendor_id" name="vendor_id" value="<%= cart_details.vendor_id %>" >
                                <input type="hidden" class="custom-control-input check" id="tasknow" name="task_type" value="<%= ((cart_details.schedule_type == 'schedule') ? 'schedule' : 'now') %>" >
                           <!-- <button id="order_placed_btn" class="btn btn-solid d-none" type="button" {{$addresses->count() == 0 ? 'disabled': ''}}>{{__('Place Order')}}</button> -->
                            </li>
                            <% if(cart_details.delay_date == 0) { %>
                            {{-- <li class="d-inline-block mr-1">
                                <input type="radio" class="custom-control-input check" id="tasknow" name="tasktype" value="now" <%= ((cart_details.schedule_type == 'now' || cart_details.schedule_type == '' || cart_details.schedule_type == null) ? 'checked' : '') %> >
                                <label class="btn btn-solid" for="tasknow">{{__('Now')}}</label>
                            </li> --}}
                            <% } %>
                            <li class="d-inline-block ">
                                <input type="radio" class="custom-control-input check taskschedulebtn" id="taskschedule" name="tasktype" value="" <%= ((cart_details.schedule_type == 'schedule' || cart_details.delay_date != 0) ? 'checked' : '') %>  style="<%= ((cart_details.schedule_type != 'schedule') ? '' : 'display:none!important') %>">
                                <label class="btn btn-solid mb-0 taskschedulebtn" for="taskschedule" style="<%= ((cart_details.schedule_type != 'schedule') ? '' : 'display:none!important') %>">{{__('Schedule')}}</label>
                            </li>
                            <% if(cart_details.closed_store_order_scheduled != 1 && cart_details.deliver_status == 0) { %>
                            <li class="close-window">
                                <i class="fa fa-times cross" aria-hidden="true"></i>
                            </li>
                            <% }else{ %>
                                <li class="close-window">
                                    <i class="fa fa-times cross" style="display:none!important"  aria-hidden="true"></i>
                                </li>
                                <% } %>
                        </ul>
                        <div class=" col-sm-4 p-0 pull-right datenow d-flex align-items-center justify-content-end text-right" id="schedule_div" style="<%= ((cart_details.schedule_type == 'schedule') ? '' : 'display:none!important') %>">
                    <% if(cart_details.slotsCnt == 0) { %>
                    <% if(cart_details.delay_date != 0) { %>
                        <input type="datetime-local" id="schedule_datetime" class="form-control" placeholder="Inline calendar" value="<%= ((cart_details.schedule_type == 'schedule') ? cart_details.scheduled_date_time : '') %>"
                        min="<%= ((cart_details.delay_date != '0') ? cart_details.delay_date : '') %>">
                        <% } else { %>
                            <input type="datetime-local" id="schedule_datetime" class="form-control" placeholder="Inline calendar" value="<%= ((cart_details.schedule_type == 'schedule') ? cart_details.scheduled_date_time : '') %>"
                            min="<%= ((cart_details.delay_date != '0') ? cart_details.delay_date : '') %>">

                            <% } %>

                    <% } else { %>


                            <input type="date" id="schedule_datetime" class="form-control schedule_datetime" placeholder="Inline calendar" value="<%=  ((cart_details.scheduled_date_time != '')?cart_details.scheduled_date_time : cart_details.delay_date ) %>"  min="<%= cart_details.delay_date %>" >
                            <input type="hidden" id="checkSlot" value="1">
                            <select name="slots" id="slot" onchange="checkSlotOrders();" class="form-control">
                                <option value="">{{__("Select Slot")}} </option>
                                <% _.each(cart_details.slots, function(slot, sl){%>
                                <option value="<%= slot.value  %>" <%= slot.value == cart_details.scheduled.slot ? 'selected' : '' %> ><%= slot.name %></option>
                                <% }) %>
                            </select>
                    <% } %>

                </div>
                    </div>
                </div>

            </div>
            @endif
            <% } %>

            {{-- Schedual code end at down --}}
</script>

<script type="text/template" id="promo_code_template">
    <% _.each(promo_codes, function(promo_code, key){%>
        <div class="col-lg-6 mt-3">
            <div class="coupon-code mt-0">
                <div class="p-2">
                    <img class="blur-up lazyload p-1" data-src="<%= promo_code.image.proxy_url %>100/70<%= promo_code.image.image_path %>" alt="">
                    <h6 class="mt-0"><%= promo_code.title %></h6>
                </div>
                <hr class="m-0">
                <div class="code-outer p-2 text-uppercase d-flex align-items-center justify-content-between">
                    <label class="m-0"><%= promo_code.name %></label>
                    <a class="btn btn-solid apply_promo_code_btn" data-vendor_id="<%= vendor_id %>" data-cart_id="<%= cart_id %>" data-coupon_id="<%= promo_code.id %>" data-amount="<%= amount %>" style="cursor: pointer;">{{__('Apply')}}</a>
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
<div id="cart_main_page">
    <div class="container">
        @if($cartData)
        <form method="post" action="" id="placeorder_form">
            @csrf
            <div class="card-box">
                <div class="row d-flex justify-space-around">
                    @if(!$guest_user)
                    <div class="col-lg-4 left_box">

                    </div>
                    @endif
                    <div class="{{ $guest_user ? 'col-md-12' : 'col-lg-8' }}">
                        <div class="spinner-box">
                            <div class="circle-border">
                                <div class="circle-core"></div>
                            </div>
                        </div>

                        <div class="cart-page-layout" id="cart_table"></div>

                    </div>
                </div>
                <div class="row mb-md-3 alFourTemplateCartButtons">
                    <div class="col-sm-6 col-lg-4 mb-2 mb-sm-0 d-lg-flex align-items-lg-center justify-content-lg-between">
                        <a class="btn btn-solid" href="{{ url('/') }}">{{__('Continue Shopping')}}</a>
                        @if(!empty(Auth::user()))
                        <a href="{{route('user.addressBook')}}"><i class="fa fa-pencil" aria-hidden="true"></i> <span>{{ __('Edit Address') }} </span> </a>
                        @endif
                    </div>



                    <div class="col-sm-6 col-lg-8 text-sm-right">

                        @if(isset($ageVerify->status) && $ageVerify->status == 1)
                            {{-- <button id="verify_your_age" class="btn btn-solid " type="button" >{{__('Verify Your Age')}}</button> --}}
                        @endif

                        <button id="order_placed_btn" class="btn btn-solid d-none" type="button" {{$addresses->count() == 0 ? 'disabled': ''}}>{{__('Place Order')}}</button>
                    </div>
                </div>
            </div>

        </form>
        @else
        <div class="row mt-2 mb-4 mb-lg-5">
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
                            <span class="invalid-feedback manual_promocode" role="alert">

                            </span>
                        </div>
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
<div class="modal fade remove-item-modal" id="remove_item_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_itemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="remove_itemLabel">{{__('Remove Item')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <input type="hidden" id="vendor_id" value="">
                <input type="hidden" id="cartproduct_id" value="">
                <h6 class="m-0 px-4">{{__('Are You Sure You Want To Remove This Item?')}}</h6>
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

                    <div class="login-with-username">
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

                        <div class="divider-line mb-2"></div>
                        <p class="new-user mb-0">New to {{getClientDetail()->company_name}}? <a href="{{route('customer.register')}}">Create an
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
                        <div class="col-sm-6 position-relative" id="imageInput">
                            <input type="hidden" id="vendor_idd" name="vendor_idd" value="" />
                            <input type="hidden" id="product_id" name="product_id" value="" />
                            <input data-default-file="" accept="image/*" type="file" data-plugins="dropify" name="prescriptions[]" class="dropify uploaded-prescription-img" multiple />
                            <!-- <img id="uploaded-prescription" style="margin-top: 9px;display:none;" src="#"/> -->
                            <div class="uploaded-prescription"></div>
                            <p class="text-muted text-center mt-2 mb-0">{{__('Uploaded Prescription(s)')}}</p>
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>

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
<?php ?>

{{-- <form action="{{ route('payment.razorpayCompletePurchase',[app('request')->input('amount'),app('request')->input('order')]) }}" method="POST" id="razorpay_gateway">
    @csrf
    <script src="https://checkout.razorpay.com/v1/checkout.js"
        data-key="<?php echo app('request')->input('api_key'); ?>"
        data-amount="<?php echo app('request')->input('amount'); ?>"
        data-buttontext="Pay"
        data-name="Razorpay Payment gateway"
        data-description="Rozerpay"
        data-prefill.name="name"
        data-prefill.email="email"
        data-theme.color="#ff7529">
    </script>
</form> --}}

@endsection

@section('script')

<script type="text/javascript" src="{{asset('assets/libs/jquery-clock-timepicker/jquery-clock-timepicker.js')}}"></script>
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
    // setTimeout(function () {
    //     $('.standard').clockTimePicker();
    //     $('.required').clockTimePicker({
    //         required: true
    //     });
    //     $('.separatorTime').clockTimePicker({
    //         separator: '.'
    //     });
    //     $('.precisionTime5').clockTimePicker({
    //         precision: 5
    //     });
    //     $('.precisionTime10').clockTimePicker({
    //         precision: 10
    //     });
    //     $('.precisionTime15').clockTimePicker({
    //         precision: 15
    //     });
    //     $('.precisionTime30').clockTimePicker({
    //         precision: 30
    //     });
    //     $('.precisionTime60').clockTimePicker({
    //         precision: 60
    //     });
    //     $('.simpleTime').clockTimePicker({
    //         onlyShowClockOnMobile: true
    //     });
    //     $('.duration').clockTimePicker({
    //         duration: true,
    //         maximum: '80:00'
    //     });
    //     $('.durationNegative').clockTimePicker({
    //         duration: true,
    //         durationNegative: true
    //     });
    //     $('.durationMinMax').clockTimePicker({
    //         duration: true,
    //         minimum: '1:00',
    //         maximum: '5:30'
    //     });
    //     $('.durationNegativeMinMax').clockTimePicker({
    //         duration: true,
    //         durationNegative: true,
    //         minimum: '-5:00',
    //         maximum: '5:00',
    //         precision: 5
    //     });
    // }, 2500);
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

<script type="text/javascript" src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script type="text/javascript" src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script type="text/javascript">
    var business_type = "<?= $client_preferences->business_type; ?>";
    var scheduling_with_slots = "<?= $client_preferences->scheduling_with_slots; ?>";
    var off_scheduling_at_cart = "<?= $client_preferences->off_scheduling_at_cart; ?>";
</script>
<script type="text/javascript" src="{{asset('js/developer.js')}}"></script>
<script type="text/javascript" src="{{asset('js/payment.js')}}"></script>
<script type="text/javascript" src="{{asset('js/apple_pay.js')}}"></script>

<script type="text/javascript">
    var stripe_fpx = '';
    var fpxBank = '';
    var idealBank = {};
    var guest_cart = {{ $guest_user ? 1 : 0 }};
    var base_url = "{{url('/')}}";
    var place_order_url = "{{route('user.placeorder')}}";
    var create_konga_hash_url = "{{route('kongapay.createHash')}}";
    var create_payphone_url = "{{route('payphone.createHash')}}";
    var create_easypaisa_hash_url = "{{route('easypaisa.createHash')}}";
    var create_windcave_hash_url = "{{route('windcave.createHash')}}";
    var create_dpo_tocken_url = "{{route('dpo.createTocken')}}";
    var create_paytech_hash_url = "{{route('paytech.createHash')}}";
    var create_flutterwave_url = "{{route('flutterwave.createHash')}}";
    var create_viva_wallet_pay_url = "{{route('vivawallet.pay')}}";
    var create_mvodafone_pay_url = "{{route('mvodafone.pay')}}";
    var create_ccavenue_url = "{{route('ccavenue.pay')}}";
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
    var update_qty_url = "{{ url('product/updateCartQuantity') }}";

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
    var product_order_form_element_data = [];
    var error_Slot_is_required = "{{__('Slot is required')}}";
    var error_Schedule_date_is_required = "{{__('Schedule date time is required')}}";
    var error_Invalid_Schedule_date = "{{__('Invalid schedule date time')}}";

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

    $.ajax({
        type: "POST",
        dataType: "json",
        url: product_faq_update_url,
        data: {product_id:product_id,user_product_order_form:product_order_form_element},
        success: function(response) {
            $("#"+remove_div_id).remove();
            //window.location.reload();
        },
        error: function(data) {
            $(".product_order_form_error").html(data.responseJSON.message);
            setTimeout(function() {
                $('.product_order_form_error').html('').hide();
            }, 5000);
        },
    });
        $('#cart_product_order_form').modal('hide');
    });


    function getFormData(dom_query){
        var product_order_form_element_data = [];
        var out = {};
        var s_data = $(dom_query).serializeArray();

            //transform into simple data/value object
            for(var i = 0; i < s_data.length; i++){
                var record = s_data[i];
                // console.log(record);
                out[record.name] = record.value;
                var product_faq_id = $(dom_query+' input[name="'+record.name+'"]').attr('data-product_faq_id');
                var is_required = $(dom_query+' input[name="'+record.name+'"]').attr('data-required');
                console.log(is_required);

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

        if (code != '') {
            $("#cart_payment_form .option-wrapper").addClass('d-none');
            $("#cart_payment_form ."+code+"_element_wrapper").removeClass('d-none');
        } else {
            $("#cart_payment_form .option-wrapper").addClass('d-none');
        }

        if (code == 'yoco') {
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

        if (code == 'checkout') {
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
        var schedule_pickup_datetime = $('#pickup_schedule_datetime').val();
        var schedule_pickup_slot = $('#schedule_pickup_slot').val();
        var vendor_id = $('#vendor_id').val();
        $.ajax({
            type: "GET",
            data: {
                "schedule_pickup_datetime": schedule_pickup_datetime,
                "schedule_pickup_slot":     schedule_pickup_slot,
                "vendor_id":                vendor_id,
            },
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(output) {
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
                "vendor_id":                vendor_id,
            },
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(output) {
                // Check if orderCount is greaten equal to orders_per_slot
                if(output.orderCount >= output.orders_per_slot){
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

    $(document).delegate('#view_all_address', 'click', function() {

        $("#view_all_address").addClass("d-none");
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

@endsection
