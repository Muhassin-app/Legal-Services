<style>
    .radip_cta_form label {
        font-weight: 500;
        display: inline-block;
        text-transform: capitalize;
        padding-left: 5px;
        min-width: 126px;
        text-align: left;
    }
    form.radip_cta_form {
        width: 80%;
        margin: 0 auto;
        padding: 0 16px;
    }
    .select_heading{
        font-size:25px; 
        font-weight: 600;
    }
    .select_heading {
        font-size: 24px;
        line-height: 1.5;
        max-width: 80%;
        margin: 0 auto;
        color: #000;
    }
    .radip_cta_form  .form-group {
        margin-bottom: 5px;
    }
    .radip_cta_form .select_on_demand_pricing_by_user {
        padding: 5px 10px;
        background-size: 415px;
        width: 140px!important;
        border-radius: 8px;
    }
</style>
<div class="modal fade" id="ondemand_price_selection_model" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="close text-right" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            <div class="modal-body text-center">
                {{-- <img style="height:150px" class="img-fluid blur-up lazyload" data-src="{{asset('assets/images/18.png')}}" alt=""> --}}
                <p class="select_heading mb-0 mt-3">{{ __('Select the type of service you wish to order') }}</p>
                <p class="mb-0"></p>
            </div>
            <form action="" class="radip_cta_form text-left"> 
                <div class="form-group text-left">
                    <input type="radio" name="onDemandpricingselection" id="VendorService" {{ (Session::get('onDemandPricingSelected') =='vendor' || Session::get('onDemandPricingSelected') =='') ? 'checked' :'' }} value="vendor"> 
                    <label for="VendorService">{{ __(config('constants.onDemandPricingType.vendor')) }}</label>
                </div>
                <div class="form-group  text-left">
                    <input type="radio" name="onDemandpricingselection" id="freelancer" {{ Session::get('onDemandPricingSelected') =='freelancer' ? 'checked' :'' }} value="freelancer">
                    <label for="freelancer">{{ __(config('constants.onDemandPricingType.freelancer')) }}</label>
                </dtext-centeriv>
            </form>
            <div class="modal-footer d-block">
                <div class="row no-gutters">
                    <div class="col-6 pr-1 mx-auto" >
                        <button type="button" class="btn btn-solid w-100 select_on_demand_pricing_by_user" data-selected_mode="freelancer" data-dismiss="modal">{{__('continue')}}</button>
                    </div>
                    {{-- <div class="col-6 pl-1">
                        <button type="button" class="btn btn-solid w-100 select_on_demand_pricing_by_user" data-selected_mode="vendor" data-dismiss="modal">{{__('vendor service')}}</button>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
