@php
    $additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch','is_service_price_selection']);
    $getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
@endphp
@if(($getOnDemandPricingRule['is_price_from_freelancer']==1) && ($getOnDemandPricingRule['is_ondemand_multi_pricing']==1) )
<div class="ondemand_price_selecton">

</div>
@endif