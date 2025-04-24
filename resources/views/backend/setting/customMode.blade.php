@if ($client_preference_detail->business_type != 'laundry' && $client_preference_detail->business_type != 'taxi')
        <!-- <div class="col-md-4">
            <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                <label for="pharmacy_check" class="mr-2 mb-0">{{ __('Pharmacy Mod') }}
                    <small class="d-block pr-5">Offer restricted products like medicines which
                        require prescription. Customer will have the option to add prescription
                        on the cart page.</small></label>
                <span><input type="checkbox" data-plugin="switchery" name="pharmacy_check" id="pharmacy_check"
                        class="form-control" data-color="#43bee1"
                        @if (isset($preference) && $preference->pharmacy_check == '1') checked='checked' @endif></span>
            </div>
        </div> -->
        <div class="col-md-4">
            <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                <label for="enquire_mode" class="mr-2 mb-0">{{ __('Inquiry Mod') }}<small
                        class="d-block pr-5">{{ __('Set products to be only available for Inquiry and hide the price.') }}</small></label>
                <span><input type="checkbox" data-plugin="switchery" name="enquire_mode" id="	enquire_mode"
                        class="form-control" data-color="#43bee1"
                        @if (isset($preference) && $preference->enquire_mode == '1') checked='checked' @endif></span>
            </div>
        </div>
    @endif

    {{-- <div class="col-md-4">
        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                    <label for="off_scheduling_at_cart" class="mr-2 mb-0">{{__('Disable Scheduling Orders')}}<small class="d-block pr-5">Disable Order Scheduling across the platform to limit only to Instant Orders.</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="off_scheduling_at_cart" id="off_scheduling_at_cart" class="form-control" data-color="#43bee1" @if (isset($preference) && $preference->off_scheduling_at_cart == '1') checked='checked' @endif>
        </span>
        </div>
    </div> --}}
    <div class="col-md-4">
        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
            <label for="isolate_single_vendor_order" class="mr-2 mb-0">{{ __('Isolate Single Vendor Order') }} <small
                    class="d-block pr-5">{{ __('Only allow customers to place order from one vendor at a time.') }}</small></label>
            <span> <input type="checkbox" data-plugin="switchery" name="isolate_single_vendor_order"
                    id="isolate_single_vendor_order" class="form-control" data-color="#43bee1"
                    @if (isset($preference) && $preference->isolate_single_vendor_order == '1') checked='checked' @endif>
            </span>
        </div>
    </div>

{{-- @endif --}}
 <!-- <div class="col-md-4">
        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
            <label for="subscription_mode" class="mr-2 mb-0">{{ __('Subscription Mod') }}<small
                    class="d-block pr-5">{{ __('Enable the option to create Subscriptions for Customers and Vendors.') }}</small></label>
            <span> <input type="checkbox" data-plugin="switchery" name="subscription_mode" id="subscription_mode"
                    class="form-control" data-color="#43bee1"
                    @if (isset($preference) && $preference->subscription_mode == '1') checked='checked' @endif>
            </span>
        </div>
    </div> -->

<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="subscription_tab_taxi" class="mr-2 mb-0">{{ __('Subscription Tab') }}<small
                class="d-block pr-5">{{ __('Enable subscription tab for taxi/cab.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="subscription_tab_taxi" id="subscription_tab_taxi"
                class="form-control" data-color="#43bee1" @if (isset($preference) && $preference->subscription_tab_taxi == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="tip_before_order" class="mr-2 mb-0">{{ __('Pre Order Tips') }}<small
                class="d-block pr-5">{{ __('Manage the option to Tip before the Order.') }}.</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="tip_before_order" id="tip_before_order"
                class="form-control" data-color="#43bee1" @if (isset($preference) && $preference->tip_before_order == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->

<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="tip_after_order" class="mr-2 mb-0">{{ __('Post Order Tips') }}<small
                class="d-block pr-5">{{ __('Manage the option to Tip after the Order.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="tip_after_order" id="tip_after_order"
                class="form-control" data-color="#43bee1" @if (isset($preference) && $preference->tip_after_order == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="auto_implement_5_percent_tip" class="mr-2 mb-0">{{ __('Auto Implement Tip 5%') }}<small
                class="d-block pr-5">{{ __('Enable to apply auto implement 5 percent tip.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="auto_implement_5_percent_tip"
                id="auto_implement_5_percent_tip" class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->auto_implement_5_percent_tip == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="product_order_form" class="mr-2 mb-0">{{ $nomenclatureProductOrderForm }}<small
                class="d-block pr-5">{{ __('Add a Product Order form. Create Dynamic questions per product.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="product_order_form" id="product_order_form"
                class="form-control" data-color="#43bee1" @if (isset($preference) && $preference->product_order_form == '1') checked='checked' @endif>
        </span>
    </div>
</div>
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="gifting" class="mr-2 mb-0">{{ __('Gifting') }}<small
                class="d-block pr-5">{{ __('Enable option to mark an Order to be gift wrapped.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="gifting" id="gifting" class="form-control"
                data-color="#43bee1" @if (isset($preference) && $preference->gifting == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="pickup_delivery_service_area" class="mr-2 mb-0">{{ __('Pickup Delivery Service Area') }}<small
                class="d-block pr-5">{{ __('Option to show Pickup Delivery Vendors based on First location restricted to Service Areas only') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="pickup_delivery_service_area"
                id="pickup_delivery_service_area" class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->pickup_delivery_service_area == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->


<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="minimum_order_batch" class="mr-2 mb-0">{{ __('Minimum Order/Increment') }}<small
                class="d-block pr-5">
                {{ __('Set the minimum order and minimum increment per product.') }}.</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="minimum_order_batch" id="minimum_order_batch"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->minimum_order_batch == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="static_delivey_fee" class="mr-2 mb-0">{{ __('Static Delivery fee') }}<small
                class="d-block pr-5">{{ __('Set a static Delivery Price per vendor based on Minimum Order Value.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="static_delivey_fee" id="static_delivey_fee"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->static_delivey_fee == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
@if ($client_preference_detail->business_type == 'laundry')
    <div class="col-md-4">
        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
            <label for="get_estimations" class="mr-2 mb-0">{{ __('Get Estimations') }}<small
                    class="d-block pr-5">Enable to create product catalog to get estimations
                    based on Auto String Matching or Bidding across Vendors.</small></label>
            <span> <input type="checkbox" data-plugin="switchery" name="get_estimations" id="get_estimations"
                    class="form-control" data-color="#43bee1"
                    @if (isset($preference) && $preference->get_estimations == '1') checked='checked' @endif>
            </span>
        </div>
    </div>

    <div class="col-md-4" @if ($client_preference_detail->get_estimations == 0) style="display:none;" @endif
        id="estimation_in_category">
        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
            <label for="view_get_estimation_in_category"
                class="mr-2 mb-0">{{ __('Enable Estimation Link in Header Category') }}<small
                    class="d-block pr-5">Enable or disable get estimation section in
                    categories on the header.</small></label>
            <span> <input type="checkbox" data-plugin="switchery" name="view_get_estimation_in_category"
                    id="view_get_estimation_in_category" class="form-control" data-color="#43bee1"
                    @if (isset($preference) && $preference->view_get_estimation_in_category == '1') checked='checked' @endif>
            </span>
        </div>
    </div>
@endif
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="max_safety_mod" class="mr-2 mb-0">{{ __('Max Safety') }}<small
                class="d-block pr-5">{{ __('Enable to give max safety option to vendors.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="max_safety_mod" id="max_safety_mod"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->max_safety_mod == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->

<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="hide_order_address" class="mr-2 mb-0">{{ __('Hide customer details') }}<small
                class="d-block pr-5">{{ __('Enable to hide customer details from order.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="hide_order_address" id="hide_order_address"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->hide_order_address == '1') checked='checked' @endif>
        </span>
    </div>
</div>
<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="category_kyc_documents" class="mr-2 mb-0">{{ __('User Place Order Documents') }}<small
                class="d-block pr-5">{{ __('Enable to require documents at the time of placing on order.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="category_kyc_documents"
                id="category_kyc_documents" class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->category_kyc_documents == '1') checked='checked' @endif>
        </span>
    </div>
</div>

<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="vendor_return_request" class="mr-2 mb-0">{{ __('Return Request') }}<small
                class="d-block pr-5">{{ __('Enable to show return request functionality for vendors.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="vendor_return_request"
                id="vendor_return_request" class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->vendor_return_request == '1') checked='checked' @endif>
        </span>
    </div>
</div>

<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="hide_order_prepare_time" class="mr-2 mb-0">{{ __('Hide Order Preparation Time') }}<small
                class="d-block pr-5">{{ __('Enable to hide order preparation time.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="hide_order_prepare_time"
                id="hide_order_prepare_time" class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->hide_order_prepare_time == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->

<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_cancel_order_user" class="mr-2 mb-0">{{ __('Order Cancellation By User') }}<small
                class="d-block pr-5">{{ __('Enable to give permission to user for cancelling order.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_cancel_order_user" id="is_cancel_order_user"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->is_cancel_order_user == '1') checked='checked' @endif>
        </span>
    </div>
</div>

<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="book_for_friend" class="mr-2 mb-0">{{ __('Book for a Friend') }}<small
                class="d-block pr-5">{{ __('Enable to add book for a friend functionality for customers.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="book_for_friend" id="book_for_friend"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->book_for_friend == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->

<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_static_dropoff" class="mr-2 mb-0">{{ __('Static dropoff location') }}<small
                class="d-block pr-5">{{ __('Enable to add the predefined list and this will reflect in the drop-off location.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_static_dropoff" id="is_static_dropoff"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->is_static_dropoff == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
@if ($client_preference_detail->business_type == 'laundry')
    <div class="col-md-4">
        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
            <label for="is_scan_qrcode_bag" class="mr-2 mb-0">{{ __('Scan Bag QR code') }}<small
                    class="d-block pr-5">{{ __('Enable to scan bag QR code for orders.') }}</small></label>
            <span> <input type="checkbox" data-plugin="switchery" name="is_scan_qrcode_bag" id="is_scan_qrcode_bag"
                    class="form-control" data-color="#43bee1"
                    @if (isset($preference) && $preference->is_scan_qrcode_bag == '1') checked='checked' @endif>
            </span>
        </div>
    </div>
@endif
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_tags" class="mr-2 mb-0">{{ __('Vendor Tags') }}<small
                class="d-block pr-5">{{ __('Enable to add vendor tags.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_vendor_tags" id="is_vendor_tags"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->is_vendor_tags == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_service_area_for_banners" class="mr-2 mb-0">{{ __('Service Area For Banners') }}<small
                class="d-block pr-5">{{ __('Enable service area for banners.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_service_area_for_banners"
                id="is_service_area_for_banners" class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->is_service_area_for_banners == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="stop_order_acceptance_for_users" class="mr-2 mb-0">{{ __('Stop Order Acceptance') }}<small
                class="d-block pr-5">{{ __('Activate to display a busy message to customers and stop accepting orders.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="stop_order_acceptance_for_users"
                id="stop_order_acceptance_for_users" class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->stop_order_acceptance_for_users == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="map_on_search_screen" class="mr-2 mb-0">{{ __('Show map on search screen') }}<small
                class="d-block pr-5">{{ __("Enable to show activate vendor's in map-view on search screen.") }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="map_on_search_screen" id="map_on_search_screen"
                class="form-control" data-color="#43bee1"
                @if (isset($preference) && $preference->map_on_search_screen == '1') checked='checked' @endif>
        </span>
    </div>
</div> -->

<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="update_order_product_price" class="mr-2 mb-0">{{ __('Order Update By Vendor') }}<small
                class="d-block pr-5">{{ __('Enable to show edit button on order detail for vendor.') }}</small></label>
        <span><input type="checkbox" data-plugin="switchery" name="update_order_product_price_switch"
                id="update_order_product_price_switch" class="form-control checkbox_change"
                data-className="update_order_product_price" data-color="#43bee1"
                @if (@$getAdditionalPreference['update_order_product_price'] == '1') checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['update_order_product_price'] == 1) value="1" @else value="0" @endif
            name="update_order_product_price" id="update_order_product_price" />
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_order_bid_switch" class="mr-2 mb-0">{{ __('Enable Bidding') }}<small
                class="d-block pr-5">{{ __('Enable to allow customers to bid on orders.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_order_bid_switch" id="is_order_bid_switch"
                class="form-control checkbox_change" data-className="is_bid_enable" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_bid_enable'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_bid_enable'] == 1) value="1" @else value="0" @endif
            name="is_bid_enable" id="is_bid_enable" />
    </div>
</div> -->

<!-- <div class="col-md-4" id="slots_with_service_area_div">
<div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
    <label for="slots_with_service_area"
        class="mr-2 mb-0">{{ __('Food Truck Service') }}<small
            class="d-block pr-5">{{ __('Enable or disable multiple service area for trucks') }}</small></label>
    <span> <input type="checkbox" data-plugin="switchery"
            name="slots_with_service_area" id="slots_with_service_area"
            class="form-control" data-color="#43bee1"
            @if (isset($preference) && $preference->slots_with_service_area == '1') checked='checked' @endif>
    </span>
</div>
</div> -->
<!--div class="col-md-4">
<div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
    <label for="is_price_by_role_switch"
        class="mr-2 mb-0">{{ __('Price By Role') }}<small
            class="d-block pr-5">{{ __("Enable to show price by role on edit's vendor screen.") }}</small></label>
    <span>
        <input type="checkbox" data-plugin="switchery" name="is_price_by_role_switch"
            id="is_price_by_role_switch" class="form-control checkbox_change"
            data-className="is_price_by_role" data-color="#43bee1"
            @if ($getAdditionalPreference['is_price_by_role'] == '1') checked='checked' @endif>
        <input type="hidden"
            @if ($getAdditionalPreference['is_price_by_role'] == 1) value="1" @else value="0" @endif
            name="is_price_by_role" id="is_price_by_role" />
    </span>
</div>
</div-->

<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_attribute_switch" class="mr-2 mb-0">{{ __('Attribute') }}<small
                class="d-block pr-5">{{ __('Enable to show attribute on catalog screen.') }}</small></label>
        <span>
            <input type="checkbox" data-plugin="switchery" name="is_attribute_switch" id="is_attribute_switch"
                class="form-control checkbox_change" data-className="is_attribute" data-color="#43bee1"
                @if ($getAdditionalPreference['is_attribute'] == '1') checked='checked' @endif>
            <input type="hidden" @if ($getAdditionalPreference['is_attribute'] == 1) value="1" @else value="0" @endif
                name="is_attribute" id="is_attribute" />
        </span>
    </div>
</div>

<!-- 
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_long_term_service_switch" class="mr-2 mb-0">{{ __('Long Term Service') }}<small
                class="d-block pr-5">{{ __('Enable to add long term service.') }}</small></label>
        <span>
            <input type="checkbox" data-plugin="switchery" name="is_long_term_service_switch"
                id="is_long_term_service_switch" class="form-control checkbox_change"
                data-className="is_long_term_service" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_long_term_service'] == '1') checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_long_term_service'] == 1) value="1" @else value="0" @endif
            name="is_long_term_service" id="is_long_term_service" />
    </div>
</div> -->

<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_gst_required_for_vendor_registration_switch"
            class="mr-2 mb-0">{{ __('GST Details for vendor') }}<small
                class="d-block pr-5">{{ __('Enable to show GST details for vendor registration') }}</small></label>
        <span>
            <input type="checkbox" data-plugin="switchery" name="is_gst_required_for_vendor_registration_switch"
                id="is_gst_required_for_vendor_registration_switch" class="form-control checkbox_change"
                data-className="is_gst_required_for_vendor_registration" data-color="#43bee1"
                @if ($getAdditionalPreference['is_gst_required_for_vendor_registration'] == '1') checked='checked' @endif>
            <input type="hidden" @if ($getAdditionalPreference['is_gst_required_for_vendor_registration'] == 1) value="1" @else value="0" @endif
                name="is_gst_required_for_vendor_registration" id="is_gst_required_for_vendor_registration" />
        </span>
    </div>
</div>
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_baking_details_required_for_vendor_registration_switch"
            class="mr-2 mb-0">{{ __('Banking Details for vendor') }}<small
                class="d-block pr-5">{{ __('Enable to show Banking details for vendor registration') }}</small></label>
        <span>
            <input type="checkbox" data-plugin="switchery"
                name="is_baking_details_required_for_vendor_registration_switch"
                id="is_baking_details_required_for_vendor_registration_switch" class="form-control checkbox_change"
                data-className="is_baking_details_required_for_vendor_registration" data-color="#43bee1"
                @if ($getAdditionalPreference['is_baking_details_required_for_vendor_registration'] == '1') checked='checked' @endif>
            <input type="hidden" @if ($getAdditionalPreference['is_baking_details_required_for_vendor_registration'] == 1) value="1" @else value="0" @endif
                name="is_baking_details_required_for_vendor_registration"
                id="is_baking_details_required_for_vendor_registration" />
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_advance_details_required_for_vendor_registration_switch"
            class="mr-2 mb-0">{{ __('Advanced Details for vendor') }}<small
                class="d-block pr-5">{{ __('Enable to show Advanced details for vendor registration') }}</small></label>
        <span>
            <input type="checkbox" data-plugin="switchery"
                name="is_advance_details_required_for_vendor_registration_switch"
                id="is_advance_details_required_for_vendor_registration_switch" class="form-control checkbox_change"
                data-className="is_advance_details_required_for_vendor_registration" data-color="#43bee1"
                @if ($getAdditionalPreference['is_advance_details_required_for_vendor_registration'] == '1') checked='checked' @endif>
            <input type="hidden" @if ($getAdditionalPreference['is_advance_details_required_for_vendor_registration'] == 1) value="1" @else value="0" @endif
                name="is_advance_details_required_for_vendor_registration"
                id="is_advance_details_required_for_vendor_registration" />
        </span>
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_category_required_for_vendor_registration_switch"
            class="mr-2 mb-0">{{ __('Vendor Category for vendor') }}<small
                class="d-block pr-5">{{ __('Enable to show Vendor Category for vendor registration') }}</small></label>
        <span>
            <input type="checkbox" data-plugin="switchery"
                name="is_vendor_category_required_for_vendor_registration_switch"
                id="is_vendor_category_required_for_vendor_registration_switch" class="form-control checkbox_change"
                data-className="is_vendor_category_required_for_vendor_registration" data-color="#43bee1"
                @if ($getAdditionalPreference['is_vendor_category_required_for_vendor_registration'] == '1') checked='checked' @endif>
            <input type="hidden" @if ($getAdditionalPreference['is_vendor_category_required_for_vendor_registration'] == 1) value="1" @else value="0" @endif
                name="is_vendor_category_required_for_vendor_registration"
                id="is_vendor_category_required_for_vendor_registration" />
        </span>
    </div>
</div> -->
<!-- 
<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_seller_module_switch" class="mr-2 mb-0">{{ __('Show Seller Module') }}<small
                class="d-block pr-5">{{ __('Enable to show Seller Module') }}</small></label>
        <span>
            <input type="checkbox" data-plugin="switchery" name="is_seller_module_switch"
                id="is_seller_module_switch" class="form-control checkbox_change" data-className="is_seller_module"
                data-color="#43bee1" @if ($getAdditionalPreference['is_seller_module'] == '1') checked='checked' @endif>
            <input type="hidden" @if ($getAdditionalPreference['is_seller_module'] == 1) value="1" @else value="0" @endif
                name="is_seller_module" id="is_seller_module" />
        </span>
    </div>
</div> -->

<!-- 
<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_gift_card_switch" class="mr-2 mb-0">{{ __('Gift Card') }}<small
                class="d-block pr-5">{{ __('Enable to allow Gift Card.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_gift_card_switch" id="is_gift_card_switch"
                class="form-control checkbox_change" data-className="is_gift_card" data-color="#43bee1"
                @if ($getAdditionalPreference['is_gift_card'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_gift_card'] == 1) value="1" @else value="0" @endif name="is_gift_card"
            id="is_gift_card" />
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_cab_pooling_switch" class="mr-2 mb-0">{{ __('Cab Pooling') }}<small
                class="d-block pr-5">{{ __('Enable to allow customers to book Cab Pooling.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_cab_pooling_switch"
                id="is_cab_pooling_switch" class="form-control checkbox_change" data-className="is_cab_pooling"
                data-color="#43bee1" @if ($getAdditionalPreference['is_cab_pooling'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_cab_pooling'] == 1) value="1" @else value="0" @endif
            name="is_cab_pooling" id="is_cab_pooling" />
    </div>
</div> -->


<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_tracking_url_switch" class="mr-2 mb-0">{{ __('Tracking Url') }}<small
                class="d-block pr-5">{{ __('Enable to allow guest customers to tracking url.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_tracking_url_switch"
                id="is_tracking_url_switch" class="form-control checkbox_change" data-className="is_tracking_url"
                data-color="#43bee1" @if ($getAdditionalPreference['is_tracking_url'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_tracking_url'] == 1) value="1" @else value="0" @endif
            name="is_tracking_url" id="is_tracking_url" />
    </div>
</div> -->

<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_tracking_url_sms_switch" class="mr-2 mb-0">{{ __('Tracking Url Sms Enable') }}<small
                class="d-block pr-5">{{ __('Enable to access guest customers to tracking url via sms otp.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_tracking_url_sms_switch"
                id="is_tracking_url_sms_switch" class="form-control checkbox_change"
                data-className="is_tracking_sms_url" data-color="#43bee1"
                @if ($getAdditionalPreference['is_tracking_sms_url'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_tracking_sms_url'] == 1) value="1" @else value="0" @endif
            name="is_tracking_sms_url" id="is_tracking_sms_url" />
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_place_order_delivery_zero_switch"
            class="mr-2 mb-0">{{ __('Place Order To Dispatcher even if delivery fee is zero') }}<small
                class="d-block pr-5">{{ __('Enable to place order To dispatcher even if delivery fee is zero.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_place_order_delivery_zero_switch"
                id="is_place_order_delivery_zero_switch" class="form-control checkbox_change"
                data-className="is_place_order_delivery_zero" data-color="#43bee1"
                @if ($getAdditionalPreference['is_place_order_delivery_zero'] == '1') checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_place_order_delivery_zero'] == 1) value="1" @else value="0" @endif
            name="is_place_order_delivery_zero" id="is_place_order_delivery_zero" />
    </div>
</div> -->

<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_user_kyc_for_registration_switch"
            class="mr-2 mb-0">{{ __('Enable to save kyc details for user registration') }}<small
                class="d-block pr-5">{{ __('Enable to save kyc details for user registration.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_user_kyc_for_registration_switch"
                id="is_user_kyc_for_registration_switch" class="form-control checkbox_change"
                data-className="is_user_kyc_for_registration" data-color="#43bee1"
                @if ($getAdditionalPreference['is_user_kyc_for_registration'] == '1') checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_user_kyc_for_registration'] == 1) value="1" @else value="0" @endif
            name="is_user_kyc_for_registration" id="is_user_kyc_for_registration" />
    </div>
</div> -->
<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_cust_success_signup_email_switch"
            class="mr-2 mb-0">{{ __('Customer Successfull Signup Email') }}<small
                class="d-block pr-5">{{ __('Enable to Send Email on Customer Successfull Signup.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_cust_success_signup_email_switch"
                id="is_cust_success_signup_email_switch" class="form-control checkbox_change"
                data-className="is_cust_success_signup_email" data-color="#43bee1"
                @if ($getAdditionalPreference['is_cust_success_signup_email'] == '1') checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_cust_success_signup_email'] == 1) value="1" @else value="0" @endif
            name="is_cust_success_signup_email" id="is_cust_success_signup_email" />
    </div>
</div>
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_file_cart_instructions_switch" class="mr-2 mb-0">{{ __('Upload file In instructions') }}<small
                class="d-block pr-5">{{ __("Enable to show price by role on edit's vendor screen.") }}</small></label>
        <span>
            <input type="checkbox" data-plugin="switchery" name="is_file_cart_instructions_switch"
                id="is_file_cart_instructions_switch" class="form-control checkbox_change"
                data-className="is_file_cart_instructions" data-color="#43bee1"
                @if ($getAdditionalPreference['is_file_cart_instructions'] == '1') checked='checked' @endif>
            <input type="hidden" @if ($getAdditionalPreference['is_file_cart_instructions'] == 1) value="1" @else value="0" @endif
                  name="is_file_cart_instructions" id="is_file_cart_instructions" />
        </span>
    </div>
</div> -->
<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_admin_vendor_rating" class="mr-2 mb-0">{{ __('Admin Vendor Rating') }}<small
                class="d-block pr-5">{{ __('Enable to show vendor on the basis of rating .') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_admin_vendor_rating_switch"
                id="is_admin_vendor_rating_switch" class="form-control checkbox_change"
                data-className="is_admin_vendor_rating" data-color="#43bee1"
                @if ($getAdditionalPreference['is_admin_vendor_rating'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_admin_vendor_rating'] == 1) value="1" @else value="0" @endif
            name="is_admin_vendor_rating" id="is_admin_vendor_rating" />
    </div>
</div>
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_enable_compare_product" class="mr-2 mb-0">{{ __('Compare Product') }}<small
                class="d-block pr-5">{{ __('Enable Compare Product Option in Product Details Recomended For ECommerce.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_enable_compare_product"
                id="is_enable_compare_product_switch" class="form-control checkbox_change"
                data-className="is_enable_compare_product" data-color="#43bee1"
                @if ($getAdditionalPreference['is_enable_compare_product'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_enable_compare_product'] == 1) value="1" @else value="0" @endif
            name="is_enable_compare_product" id="is_enable_compare_product" />
    </div>
</div>
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_service_product_price_from_dispatch_switch"
            class="mr-2 mb-0">{{ __('Freelancer Mod for Service Booking') }}<small
                class="d-block pr-5">{{ __('To view list of agents on booking') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_service_product_price_from_dispatch_switch"
                id="is_service_product_price_from_dispatch_switch" class="form-control checkbox_change"
                data-className="is_service_product_price_from_dispatch" data-color="#43bee1"
                @if ($getAdditionalPreference['is_service_product_price_from_dispatch'] == '1') checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_service_product_price_from_dispatch'] == '1') value="1" @else value="0" @endif
            name="is_service_product_price_from_dispatch" id="is_service_product_price_from_dispatch" />
    </div>
</div> -->
@if ($getAdditionalPreference['is_service_product_price_from_dispatch'] == '1')
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_service_price_selection_switch" class="mr-2 mb-0">{{ __('Service Freelancer/ Vendor Module') }}<small
                class="d-block pr-5">{{ __('Enable this if you want to use both freelancer and vendor module simultaneously') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_service_price_selection_switch"
                id="is_service_price_selection_switch" class="form-control checkbox_change"
                data-className="is_service_price_selection" data-color="#43bee1"
                @if ($getAdditionalPreference['is_service_price_selection'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_service_price_selection'] == 1) value="1" @else value="0" @endif
            name="is_service_price_selection" id="is_service_price_selection" />
    </div>
</div>
@endif
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_service_price_selection_switch" class="mr-2 mb-0">{{ __('Map Configuration') }}<small
                class="d-block pr-5">{{ __('Enable this if you want to search address within your country') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_map_search_perticular_country_switch"
                id="is_map_search_perticular_country_switch" class="form-control checkbox_change"
                data-className="is_map_search_perticular_country" data-color="#43bee1"
                @if ($getAdditionalPreference['is_map_search_perticular_country'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_map_search_perticular_country'] == 1) value="1" @else value="0" @endif
            name="is_map_search_perticular_country" id="is_map_search_perticular_country" />
    </div>
</div>
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_particular_driver_switch" class="mr-2 mb-0">{{ __('Request for Particular Driver') }}<small
                class="d-block pr-5">{{ __('Enable to allow customers to book particular driver.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_particular_driver_switch"
                id="is_particular_driver_switch" class="form-control checkbox_change" data-className="is_particular_driver"
                data-color="#43bee1" @if ($getAdditionalPreference['is_particular_driver'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if ($getAdditionalPreference['is_particular_driver'] == 1) value="1" @else value="0" @endif
            name="is_particular_driver" id="is_particular_driver" />
    </div>
</div> -->
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_recurring_booking" class="mr-2 mb-0">{{ __('Recurring Booking') }}<small
                class="d-block pr-5">{{ __('Enable Recurring Booking.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_recurring_booking"
                id="is_recurring_booking_switch" class="form-control checkbox_change"
                data-className="is_recurring_booking" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_recurring_booking'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_recurring_booking'] == 1) value="1" @else value="0" @endif
            name="is_recurring_booking" id="is_recurring_booking" />
    </div>
</div>

<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_rental_weekly_monthly_price" class="mr-2 mb-0">{{__('Rental Weekly Monthly Price')}}<small class="d-block pr-5">{{__("Enable to add weekly and monthly price for product in rental.")}}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_rental_weekly_monthly_price" id="is_rental_weekly_monthly_price_switch" class="form-control checkbox_change" data-className="is_rental_weekly_monthly_price"  data-color="#43bee1" @if(@$getAdditionalPreference['is_rental_weekly_monthly_price'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden"  @if(@$getAdditionalPreference['is_rental_weekly_monthly_price'] == 1) value="1" @else value="0" @endif  name="is_rental_weekly_monthly_price"  id="is_rental_weekly_monthly_price"/>
    </div>
</div> -->
<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_share_ride_users" class="mr-2 mb-0">{{ __('Share Ride Users') }}<small
                class="d-block pr-5">{{ __('.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_share_ride_users"
                id="is_share_ride_users_switch" class="form-control checkbox_change"
                data-className="is_share_ride_users" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_share_ride_users'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_share_ride_users'] == 1) value="1" @else value="0" @endif
            name="is_share_ride_users" id="is_share_ride_users" />
    </div>
</div> -->

<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_enable_curb_side" class="mr-2 mb-0">{{ __('Curb Side') }}<small
                class="d-block pr-5">{{ __('Enable Curb Side Notification To Vendor.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_enable_curb_side"
                id="is_enable_curb_side_switch" class="form-control checkbox_change"
                data-className="is_enable_curb_side" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_enable_curb_side'] == 1) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_enable_curb_side'] == 1) value="1" @else value="0" @endif
            name="is_enable_curb_side" id="is_enable_curb_side" />
    </div>
</div> -->
<!-- 
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_enable_allergic_items" class="mr-2 mb-0">{{ __('Customer Allergic Items') }}<small
                class="d-block pr-5">{{ __('Enable this for Customer add Allergic Items.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_enable_allergic_items"
                id="is_enable_allergic_items_switch" class="form-control checkbox_change"
                data-className="is_enable_allergic_items" data-color="#43bee1"
                @if (@getAdditionalPreference(['is_enable_allergic_items'])['is_enable_allergic_items']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_enable_allergic_items'] == 1) value="1" @else value="0" @endif
            name="is_enable_allergic_items" id="is_enable_allergic_items" />
    </div>
</div> -->

<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_marg_configuration" class="mr-2 mb-0">{{ __('Vendor Marg Configuration') }}<small
                class="d-block pr-5">{{ __('Enable this for Vendor add own Marg Configuration.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_vendor_marg_configuration"
                id="is_vendor_marg_configuration_switch" class="form-control checkbox_change"
                data-className="is_vendor_marg_configuration" data-color="#43bee1"
                @if (@getAdditionalPreference(['is_vendor_marg_configuration'])['is_vendor_marg_configuration']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_vendor_marg_configuration'] == 1) value="1" @else value="0" @endif
            name="is_vendor_marg_configuration" id="is_vendor_marg_configuration" />
    </div>
</div> -->
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_marg_configuration" class="mr-2 mb-0">{{ __('Manage Roles & Permission') }}<small
                class="d-block pr-5">{{ __('Enable role and permission for users.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_role_and_permission_enable"
                id="is_role_and_permission_enable_switch" class="form-control checkbox_change"
                data-className="is_role_and_permission_enable" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_role_and_permission_enable']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_role_and_permission_enable'] == 1) value="1" @else value="0" @endif
            name="is_role_and_permission_enable" id="is_role_and_permission_enable" />
    </div>
</div>
<!-- <div class="col-md-4 d-none">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_hourly_pickup_rental" class="mr-2 mb-0">{{ __('Hourly Pick & Drop Rental') }}<small
            class="d-block pr-5">{{ __('Enable Hourly Pick & Drop Rental') }}</small></label>
    <span> <input type="checkbox" data-plugin="switchery" name="is_hourly_pickup_rental_switch"
            id="is_hourly_pickup_rental_switch" class="form-control checkbox_change"
            data-className="is_hourly_pickup_rental" data-color="#43bee1"
            @if (isset($getAdditionalPreference) && $getAdditionalPreference['is_hourly_pickup_rental'] == '1') checked='checked' @endif>
    </span>
    <input type="hidden" @if (isset($getAdditionalPreference) && $getAdditionalPreference['is_hourly_pickup_rental'] == '1')  value="1" @else value="0" @endif
        name="is_hourly_pickup_rental" id="is_hourly_pickup_rental" />
</div>
</div> -->
<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_marg_configuration" class="mr-2 mb-0">{{ __('Car Rental') }}<small
                class="d-block pr-5">{{ __('Enable For Rental Protection,Booking Options and for Destination.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_car_rental_enable"
                id="is_car_rental_enable_switch" class="form-control checkbox_change"
                data-className="is_car_rental_enable" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_car_rental_enable']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_car_rental_enable'] == 1) value="1" @else value="0" @endif
            name="is_car_rental_enable" id="is_car_rental_enable" />
    </div>
</div> -->
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_marg_configuration" class="mr-2 mb-0">{{ __('SMS on Complete Order') }}<small
                class="d-block pr-5">{{ __('Enable sms for complete order.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_sms_complete_order"
                id="is_sms_complete_order_switch" class="form-control checkbox_change"
                data-className="is_sms_complete_order" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_sms_complete_order']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_sms_complete_order'] == 1) value="1" @else value="0" @endif
            name="is_sms_complete_order" id="is_sms_complete_order" />
    </div>
</div>

<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_marg_configuration" class="mr-2 mb-0">{{ __('SMS on Cancel Order') }}<small
                class="d-block pr-5">{{ __('Enable sms for cancel order.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_sms_cancel_order"
                id="is_sms_cancel_order_switch" class="form-control checkbox_change"
                data-className="is_sms_cancel_order" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_sms_cancel_order']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_sms_cancel_order'] == 1) value="1" @else value="0" @endif
            name="is_sms_cancel_order" id="is_sms_cancel_order" />
    </div>
</div>

<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_marg_configuration" class="mr-2 mb-0">{{ __('SMS on Booked Ride') }}<small
                class="d-block pr-5">{{ __('Enable sms for booked ride.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_sms_booked_ride"
                id="is_sms_booked_ride_switch" class="form-control checkbox_change"
                data-className="is_sms_booked_ride" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_sms_booked_ride']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_sms_booked_ride'] == 1) value="1" @else value="0" @endif
            name="is_sms_booked_ride" id="is_sms_booked_ride" />
    </div>
</div> -->
<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_vendor_marg_configuration" class="mr-2 mb-0">{{ __('Product Measurement') }}<small
                class="d-block pr-5">{{ __('Enable measurement in (CM/KG).') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_product_measurement_in_cm_kg"
                id="is_product_measurement_in_cm_kg_switch" class="form-control checkbox_change"
                data-className="is_product_measurement_in_cm_kg" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_product_measurement_in_cm_kg']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_product_measurement_in_cm_kg'] == 1) value="1" @else value="0" @endif
            name="is_product_measurement_in_cm_kg" id="is_product_measurement_in_cm_kg" />
    </div>
</div> -->

<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_freelance_on_homepage" class="mr-2 mb-0">{{ __('Enable Freelancer Location in Homepage') }}<small
                class="d-block pr-5">{{ __('Enable Freelancer Location in Homepage.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_freelance_on_homepage"
                id="is_freelance_on_homepage_switch" class="form-control checkbox_change"
                data-className="is_freelance_on_homepage" data-color="#43bee1"
                @if (@$getAdditionalPreference['is_freelance_on_homepage']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['is_freelance_on_homepage'] == 1) value="1" @else value="0" @endif
            name="is_freelance_on_homepage" id="is_freelance_on_homepage" />
    </div>
</div> -->

<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="is_enable_compare_product" class="mr-2 mb-0">Bulk Order Product<small class="d-block pr-5">Enable Bulk Order Product Recomended For ECommerce.</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="is_corporate_user" id="is_corporate_user_switch" class="form-control checkbox_change" data-classname="is_corporate_user" data-color="#43bee1"   @if (@$getAdditionalPreference['is_corporate_user']) checked='checked' @endif ></span>
        <input type="hidden" @if (@$getAdditionalPreference['is_corporate_user'] == 1) value="1" @else value="0" @endif name="is_corporate_user" id="is_corporate_user">
    </div>
</div> -->
<!-- <div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3">
        <label for="enable_pwa_switch" class="mr-2 mb-0">{{ __('Enable PWA') }}</label>
        <input type="hidden" @if (@$getAdditionalPreference['enable_pwa'] == 1) value="1" @else value="0" @endif name="enable_pwa" id="enable_pwa">

        <span><input type="checkbox" data-plugin="switchery" name="enable_pwa_switch" id="enable_pwa_switch"
                class="form-control" data-color="#43bee1"
                @if (@$getAdditionalPreference['enable_pwa'] == 1) checked='checked' @endif></span>
    </div>
</div> -->

<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="vendor_online_status_switch" class="mr-2 mb-0">{{ __('Vendor Online Status Enable/Disable') }}<small
                class="d-block pr-5">{{ __('Change Online Status In Vendor App Enable/Disable.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="vendor_online_status"
                id="vendor_online_status_switch" class="form-control checkbox_change"
                data-className="vendor_online_status" data-color="#43bee1"
                @if (@$getAdditionalPreference['vendor_online_status']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['vendor_online_status'] == 1) value="1" @else value="0" @endif
            name="vendor_online_status" id="vendor_online_status" />
    </div>
</div>

<!-- <div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="distance_matrix_app_switch" class="mr-2 mb-0">{{ __('Google Matrix with Only App') }}<small
                class="d-block pr-5">{{ __('Change Google Matrix Api /AppDisable.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="distance_matrix_app_status"
                id="distance_matrix_app_switch" class="form-control checkbox_change"
                data-className="distance_matrix_app_status" data-color="#43bee1"
                @if (@$getAdditionalPreference['distance_matrix_app_status']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['distance_matrix_app_status'] == 1) value="1" @else value="0" @endif
            name="distance_matrix_app_status" id="distance_matrix_app_status" />
    </div>
</div> -->

<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="cart_cms_page_switch" class="mr-2 mb-0">{{ __('Cart CMS Pages') }}<small
                class="d-block pr-5">{{ __('Show CMS pages on cart page for all modules') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="cart_cms_page_status"
                id="cart_cms_page_switch" class="form-control checkbox_change"
                data-className="cart_cms_page_status" data-color="#43bee1"
                @if (@$getAdditionalPreference['cart_cms_page_status']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (@$getAdditionalPreference['cart_cms_page_status'] == 1) value="1" @else value="0" @endif
            name="cart_cms_page_status" id="cart_cms_page_status" />
    </div>
</div>
<!-- 
<div class="col-md-4 ">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="document_report_switch" class="mr-2 mb-0">{{ __('Upload document report by vendor') }}<small
                class="d-block pr-5">{{ __('Enable upload report in vendor.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="document_report"
                id="document_report_switch" class="form-control checkbox_change"
                data-className="document_report" data-color="#43bee1"
                @if (getAdditionalPreference(['document_report'])['document_report']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (getAdditionalPreference(['document_report'])['document_report'] == 1) value="1" @else value="0" @endif
            name="document_report" id="document_report" />
    </div>
</div> -->
<div class="col-md-4">
    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
        <label for="product_measurment_switch" class="mr-2 mb-0">{{ __('Product Measurment') }}<small
                class="d-block pr-5">{{ __('To Add Measurment for Product.') }}</small></label>
        <span> <input type="checkbox" data-plugin="switchery" name="product_measurment"
                id="product_measurment_switch" class="form-control checkbox_change"
                data-className="product_measurment" data-color="#43bee1"
                @if (getAdditionalPreference(['product_measurment'])['product_measurment']) checked='checked' @endif>
        </span>
        <input type="hidden" @if (getAdditionalPreference(['product_measurment'])['product_measurment'] == 1) value="1" @else value="0" @endif
            name="product_measurment" id="product_measurment" />
    </div>
</div>
