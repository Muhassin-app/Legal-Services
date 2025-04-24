<style>
    a.deleteMultiBanner {
        color: #fff;
    }
    .add_field{display: inline-block;}td.lasttd.manage_social.text-center {vertical-align: middle;}.social_manage .addUrlRow-Add {font-size: 12px;}
</style>
<div class="card-box text-center p-0 overflow-hidden" style="">
    <div class="background pt-3 pb-2 px-2" style="background:url({{$vendor->banner['proxy_url'] . '200/100' . $vendor->banner['image_path']}}) no-repeat center center;background-size:cover;">
        <div class="vendor_text">
            <img src="{{$vendor->logo['proxy_url'] . '90/90' . $vendor->logo['image_path']}}" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">
            <h4 class="mb-0 text-white">{{ucfirst($vendor->name)}}</h4>
            <p class="text-white">{{$vendor->address}}</p>
            <button type="button" class="btn btn-success btn-sm waves-effect mb-2 waves-light openEditModal" data-toggle="modal" data-target="#exampleModal"> {{ __("Edit") }} </button>
            @if($vendor->status == 0 && Auth::user()->is_superadmin == 1)
            <button type="button" class="btn btn-success btn-sm waves-effect mb-2 waves-light" id="approve_btn" data-vendor_id="{{$vendor->id}}" data-status="1">{{ __("Accept") }}</button>
            <button type="button" class="btn btn-danger btn-sm waves-effect mb-2 waves-light" id="reject_btn" data-vendor_id="{{$vendor->id}}" data-status="2">{{ __("Reject") }}</button>
            @else
            @if(Auth::user()->is_superadmin == 1)
            <button type="button" class="btn btn-danger btn-sm waves-effect mb-2 waves-light" id="block_btn" data-vendor_id="{{$vendor->id}}" data-status="{{$vendor->status == 2  ? '1' : '2'}}">{{$vendor->status == 2 ? 'Unblock' : 'Block'}}</button>
            @endif

            @if($vendor_for_pickup_delivery > 0)
            <div class="for_pickup_delivery_service_only">
            @if($client_preferences->need_dispacher_ride == 1)
            <button type="button" class="btn btn-danger btn-sm waves-effect mb-2 waves-light openConfirmDispatcher" data-id="{{ $vendor->id }}"> {{ __("Login Into Dispatcher (Pickup & Delivery)") }} </button>
            @endif
            </div>
            @endif

            @if($vendor_for_ondemand > 0)
            <div class="for_on_demand_service_only">
            @if($client_preferences->need_dispacher_home_other_service == 1)
            <button type="button" class="btn btn-danger btn-sm waves-effect mb-2 waves-light openConfirmDispatcherOnDemand" data-id="{{ $vendor->id }}"> {{ __("Login Into Dispatcher (On Demand Services)") }} </button>
            @endif
            </div>
            @endif
            @if($vendor_for_appointment_delivery > 0 && ($client_preferences->appointment_check == 1) )
            <div class="for_appointment_delivery_service_only">
            @if( ($client_preferences->need_appointment_service == 1) && ($vendor->appointment == 1 ) )
            <button type="button" class="btn btn-danger btn-sm waves-effect mb-2 waves-light openConfirmAppointmentDispatcher" data-id="{{ $vendor->id }}"> {{ __("Login Into Dispatcher (Appointment)") }} </button>
            @endif
            </div>
            @endif

            @endif
            <div class="for_pickup_delivery_service_only">
                <a href="javascript:void(0)" class="openSocialMedia btn btn-info bg-info text-white">{{ __("Manage Social Media URLs") }}</a>
            </div>
        </div>
    </div>
    <div class="text-left mt-0 p-3">
        <p class="text-muted font-13 mb-0">
            {{$vendor->desc}}
        </p>
    </div>
    @if($vendorMultiBanner['webStyleId'] == 6)
    <div class="Upload_meltipat_banner row m-0">
        <div class="col-md-4 text-center mb-2">
            <a class="outer-box border-dashed d-flex align-items-center justify-content-center addBannner-btns" href="javascript:void(0)" data-toggle="modal" data-target="#addBannner-form">
                <i class="fa fa-plus-circle d-block mr-1" aria-hidden="true"></i>
                <h6 class="m-0">banner</h6>
            </a>
        </div>
        @foreach ($vendorMultiBanner['banner'] as $key =>$multiBanner )
        <div class="col-md-4 text-center mb-2">
            <div class="alProDuctBannerImg">
                <img src="{{$multiBanner->image['proxy_url'] . '200/100' . $multiBanner->image['image_path']}}" alt="" class="w-100">
                <span class=""><a class='deleteMultiBanner' data-banner_id="{{$multiBanner->id }}" href="javascript:void(0)"><i class="fa fa-times "  ></i></a></span>
            </div>
        </div>
        @endforeach
        {{-- <div class="col-md-4 text-center mb-2">
            <div class="alProDuctBannerImg">
                <img src="https://images.royoorders.com/insecure/fill/200/100/sm/0/plain/https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/vendor/bDZm2MWRNof7IyTlie6E9aQYWUsL1YI7DLCB9dJb.jpg@webp" alt="" class="w-100">
                <span class=""><i class="fa fa-times"></i></span>
            </div>
        </div>
        <div class="col-md-4 text-center mb-2">
            <div class="alProDuctBannerImg">
                <img src="https://images.royoorders.com/insecure/fill/200/100/sm/0/plain/https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/vendor/bDZm2MWRNof7IyTlie6E9aQYWUsL1YI7DLCB9dJb.jpg@webp" alt="" class="w-100">
                <span class=""><i class="fa fa-times"></i></span>
            </div>
        </div> --}}
    </div>
    @endif
</div>
<!-- <div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="mb-2">Public URL</h4>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <div class="form-group mb-0">
                        <div class="site_link position-relative">
                            <a href="{{route('vendorDetail',$vendor->slug)}}" target="_blank"><span id="pwd_spn" class="password-span">{{route('vendorDetail',$vendor->slug)}}</span></a>
                            <label class="copy_link float-right" id="cp_btn" title="copy">
                                <img src="{{ asset('assets/icons/domain_copy_icon.svg')}}" alt="">
                                <span class="copied_txt" id="show_copy_msg_on_click_copy" style="display:none;">Copied</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->
{{-- <div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <form name="config-form" action="{{route('vendor.config.update', $vendor->id)}}" class="needs-validation" id="slot-configs" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-2 "> <span class="">{{ __("Settings") }}</span></h4>
                    </div>
                </div>
                <div class="row mb-2">
                    @if($client_preference_detail->business_type != 'taxi')
                    <div class="col-md-12">
                        <div class="form-group" id="order_pre_timeInput">
                            {!! Form::label('title', __('Order Prepare Time(In minutes)'),['class' => 'control-label']) !!}
                            <div class="position-relative">
                                <input class="form-control" onkeypress="return isNumberKey(event)" name="order_pre_time" id="Vendor_order_pre_time" type="text" value="{{ ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0 }}" {{$vendor->status == 1 ? '' : 'disabled'}}>
                                <div class="time-sloat d-flex align-items-center"><span class="" id="Vendor_order_pre_time_show" ></span> </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($client_preference_detail->business_type != 'taxi')
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('24*7 Availability'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="show_slot" class="form-control" data-color="#43bee1" @if($vendor->show_slot == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>

                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between" style="display:{{$vendor->show_slot == 1 ? 'none!important' : 'block'}}" id="sch_vendor_close">
                        {!! Form::label('title', __('Schedule order if vendor closed?'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="closed_store_order_scheduled" class="form-control" data-color="#43bee1" @if($vendor->closed_store_order_scheduled == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>



                    <div class="col-md-12 d-flex align-items-center justify-content-between">
                        <div class="form-group w-100">
                        {!! Form::label('title', __('Slot Duration (In minutes)'),['class' => 'control-label']) !!}
                            <select class="form-control" name="slot_minutes">
                                <option value="">{{__('Slot Duration')}}</option>
                                <option value="15" {{$vendor->slot_minutes == '15'? 'selected':''}}>15 {{__(' Minutes')}}</option>
                                <option value="30" {{$vendor->slot_minutes == '30'? 'selected':''}}>30 {{__(' Minutes')}}</option>
                                <option value="45" {{$vendor->slot_minutes == '45'? 'selected':''}}>45 {{__(' Minutes')}}</option>
                                @for($i=1;$i<=8;$i++)
                                    <option value="{{$i*60}}" {{$vendor->slot_minutes == ($i*60)? 'selected':''}}>{{ $i. __(' Hour')}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 mb-2">
                        <div class="form-group" id="orders_per_slotInput">
                            {!! Form::label('title', __('Maximum Orders Per Slot'),['class' => 'control-label']) !!}
                            <input class="form-control" onkeypress="return isNumberKey(event)" name="orders_per_slot" type="text" value="{{$vendor->orders_per_slot}}">
                        </div>
                    </div>

                    @endif
                    @if($client_preference_detail->business_type != 'taxi')
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __($vendor->fixedFeeNomenclatures),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="fixed_fee" class="form-control" data-color="#43bee1" @if($vendor->fixed_fee == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>
                    <div class="col-md-12 mb-2 align-items-center justify-content-between" style="display:{{$vendor->fixed_fee == 0 ? 'none!important' : 'block'}}" id="fixed_fee_amount">
                    {!! Form::label('title',  __($vendor->fixedFeeNomenclatures).' value',['class' => 'control-label']) !!}
                            <input class="form-control" onkeypress="return isNumberKey(event)" name="fixed_fee_amount" type="text" value="{{$vendor->fixed_fee_amount}}" {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between inline-toltip">
                        <label class="position-relative control-label" >Hide Price Bifurcation
                            <div class="alInfoIocn">
                                <i class="fa fa-info-circle"></i>
                                <span class="tooltiptext">Hide Fixed Price, Service Fee, Container Charges, Taxes, Subtotal</span>
                            </div>
                        </label>
                        <input type="checkbox" data-plugin="switchery" name="price_bifurcation" class="form-control" data-color="#43bee1" @if($vendor->price_bifurcation == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>
                    @endif
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('Auto Accept Order'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="auto_accept_order" class="form-control" data-color="#43bee1" @if($vendor->auto_accept_order == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>
                    @if($client_preference_detail->business_type != 'taxi')
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('Need Container Charges?'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="need_container_charges" class="form-control" data-color="#43bee1" @if($vendor->need_container_charges == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>

                    @if(Auth::user()->is_superadmin == 1 || $client_preference_detail->vendor_return_request == 1)
                        <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                            {!! Form::label('title', __('Return Request'),['class' => 'control-label']) !!}
                            <input type="checkbox" data-plugin="switchery" name="return_request" class="form-control" data-color="#43bee1" @if($vendor->return_request == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                        </div>
                    @endif

                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('Cancel Order In Processing'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="cancel_order_in_processing" class="form-control" data-color="#43bee1" @if($vendor->cancel_order_in_processing == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>

                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('Return Auto Approve'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="return_auto_approve" class="form-control" data-color="#43bee1" @if($vendor->return_auto_approve == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>

                    <div class="col-md-12" id="auto_reject_timeInput" style="display:{{$vendor->auto_accept_order == 1 ? 'none' : 'block'}}">
                        <div class="form-group">
                            {!! Form::label('title', __('Auto Reject Time(In minutes, 0 for no rejection)'),['class' => 'control-label']) !!}
                            <input class="form-control" name="auto_reject_time" type="number" value="{{$vendor->auto_reject_time}}" min="0" {{$vendor->status == 1 ? '' : 'disabled'}} >
                        </div>
                    </div>
                    @endif

                    @if(isset($getAdditionalPreference['is_price_by_role']) && $getAdditionalPreference['is_price_by_role'] == '1')
                        @if(isset($roles))
                            @foreach($roles as $role)
                                <div class="col-md-12">
                                    <div class="form-group" id="order_min_amountInput">
                                        @php
                                            $label = 'Absolute Min Order Value ['.$role->role.']';
                                        @endphp
                                        {!! Form::label('title',  $label,['class' => 'control-label']) !!}
                                        <input class="form-control" onkeypress="return isNumberKey(event)" name="order_min_amount_arr[{{$role->id}}]" type="text" value="{{$role->order_min_amount ?? '0.00' }}" {{$vendor->status == 1 ? '' : 'disabled'}}>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @else
                        <div class="col-md-12">
                            <div class="form-group" id="order_min_amountInput">
                                {!! Form::label('title',  __('Absolute Min Order Value [AMOV]'),['class' => 'control-label']) !!}
                                <input class="form-control" onkeypress="return isNumberKey(event)" name="order_min_amount" type="text" value="{{$vendor->order_min_amount}}" {{$vendor->status == 1 ? '' : 'disabled'}}>
                            </div>
                        </div>
                    @endif


                    @if($client_preference_detail->static_delivey_fee == 1)
                    <div class="col-md-12">
                        <div class="form-group" id="order_amount_for_delivery_feeInput">
                            {!! Form::label('title', __('Min Order Value (with Delivery fee) [MOV]'),['class' => 'control-label']) !!}
                            <input class="form-control" onkeypress="return isNumberKey(event)" name="order_amount_for_delivery_fee" type="text" value="{{$vendor->order_amount_for_delivery_fee}}" {{$vendor->status == 1 ? '' : 'disabled'}}>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group" id="delivery_fee_minimumInput">
                            {!! Form::label('title', __('Delivery Fee For Below MOV'),['class' => 'control-label']) !!}
                            <input class="form-control" onkeypress="return isNumberKey(event)" name="delivery_fee_minimum" type="text" value="{{$vendor->delivery_fee_minimum}}" {{$vendor->status == 1 ? '' : 'disabled'}}>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group" id="delivery_fee_maximumInput">
                            {!! Form::label('title', __('Delivery Fee For Above MOV'),['class' => 'control-label']) !!}
                            <input class="form-control" onkeypress="return isNumberKey(event)" name="delivery_fee_maximum" type="text" value="{{$vendor->delivery_fee_maximum}}" {{$vendor->status == 1 ? '' : 'disabled'}}>
                        </div>
                    </div>
                    @endif


                    @if(EasebuzzSubMerchent() == 1)
                    <div class="col-md-12">
                        <div class="form-group" id="social_link">
                            {!! Form::label('title', 'Easebuzz Sub Merchent Id',['class' => 'control-label']) !!}
                            <input class="form-control" name="easebuzz_sub_merchent_id" type="text" value="{{$vendor->easebuzz_sub_merchent_id}}" >
                        </div>
                    </div>
                    @endif
                    @if($client_preference_detail->is_vendor_tags == '1')
                        @if(!empty($facilties))
                            <div class="col-md-12">
                                <div class="form-group" id="social_link">
                                    {!! Form::label('title', 'Vendor Tags',['class' => 'control-label']) !!}
                                    <select class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ..." id="facilty_list" name="facilty_ids[]">
                                        @foreach ($facilties as $facilty)
                                        <option value="{{ $facilty->id }}" {{ in_array($facilty->id, $vendor_facilty_ids) ? "selected" : '' }}>{{ $facilty->primary->name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                        @endif
                    @endif
                    <div class="col-md-12">
                        <div class="form-group" id="social_link">
                            {!! Form::label('title', 'Dynamic Html',['class' => 'control-label']) !!}
                            <textarea class="form-control" id="edit_description" rows="9" name="dynamic_html" cols="100">{{$vendor->dynamic_html}}
                            </textarea>
                        </div>
                    </div>


                    <div class="col-12">
                        <button class="btn btn-info waves-effect waves-light w-100" {{$vendor->status == 1 ? '' : 'disabled'}}>{{ __("Save") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> --}}


@if(Auth::user()->is_superadmin == 1)

@if(isset($checkAhoyShip) && $checkAhoyShip != 0)
{{-- <div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <form name="config-form" action="{{route('vendor.config.ahoy.pickuplocation', $vendor->id)}}" class="needs-validation" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-2"> <span class="">{{ __("Add Pickup Location Ahoy Delivery") }}</span></h4>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        <input type="text" name="location_name" class="form-control" value="{{@$vendor->ahoy_location? json_decode($vendor->ahoy_location)->locationName :''}}" {{(($vendor->ahoy_location)? 'disabled' :'')}} placeholder="{{__('Location Name')}}" required>
                    </div>
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        <div class="form-group w-100">
                            <label class="radio">{{__('Location Type')}}</label>
                        <select class="form-control" name="location_type">
                            <option value="1" {{@$vendor->ahoy_location? (json_decode($vendor->ahoy_location)->locationType =='1')?'Selected':'' : ''}}>Tower, (either office or apartment)</option>
                            <option value="2" {{@$vendor->ahoy_location? (json_decode($vendor->ahoy_location)->locationType =='2')?'Selected':'' : ''}}>Building (villa, police station. etc)</option>
                            <option value="3" {{@$vendor->ahoy_location? (json_decode($vendor->ahoy_location)->locationType =='3')?'Selected':'' : ''}}>Commercial (warehouse)</option>
                        </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-info waves-effect waves-light w-100" {{@$vendor->ahoy_location? (json_decode($vendor->ahoy_location)->locationName)?'disabled':'' : ''}} >{{ __("Save") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> --}}
@endif

@if(isset($checkShip) && $checkShip != 0)
<div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <form name="config-form" action="{{route('vendor.config.pickuplocation', $vendor->id)}}" class="needs-validation" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-2"> <span class="">{{ __("Add Pickup Location Shiprocket") }}</span></h4>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        <input type="text" name="shiprocket_pickup_name" class="form-control" value="{{@$vendor->shiprocket_pickup_name}}" {{(($vendor->shiprocket_pickup_name)? '' :'')}} placeholder="{{__('Pickup Location Name')}}" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-info waves-effect waves-light w-100" {{(($vendor->shiprocket_pickup_name)? '' :'')}}>{{ __("Save") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- <div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <form name="config-form" action="{{route('vendor.config.update.profile', $vendor->id)}}" class="needs-validation" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-2"> <span class="">{{ __("Profile") }} ({{ __("Visible For Admin") }})</span></h4>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('Show Profile Details'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="is_show_vendor_details" class="form-control" data-color="#43bee1" @if($vendor->is_show_vendor_details == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-info waves-effect waves-light w-100" {{$vendor->status == 1 ? '' : 'disabled'}}>{{ __("Save") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> --}}

{{-- <div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <form name="config-form" action="{{route('vendor.config.update', $vendor->id)}}" class="needs-validation" method="post">
                @csrf
                {{-- <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-2"> <span class="">{{ __("Commission") }}</span> ({{ __("Visible For Admin") }})</h4>
                    </div>
                </div> 

                <div class="row">

                    @if(Auth::user()->is_superadmin == 1)
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="mb-2 "> <span class="">{{ __("Commission") }} & {{ __("Taxes") }}</span>   ({{ __("Visible For Admin") }})</span></h4>
                        </div>
                    </div>
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Service Charges'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="service_charges_tax" class="form-control" data-color="#43bee1" @if($vendor->service_charges_tax == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>

                    <div class="form-group w-100" style="display:{{$vendor->service_charges_tax == 0 ? 'none!important' : 'block'}}" id="service_charges_tax_id">
                     {!! Form::label('title', __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="service_charges_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$vendor->service_charges_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('Add Markup Price?'),['class' => 'control-label']) !!}({{ __("Visible For Admin") }})
                        <input type="checkbox" data-plugin="switchery" name="add_markup_price" class="form-control" data-color="#43bee1" @if($vendor->add_markup_price == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>


                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Delivery Charges'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="delivery_charges_tax" class="form-control" data-color="#43bee1" @if($vendor->delivery_charges_tax == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>

                    <div class="form-group w-100" style="display:{{$vendor->delivery_charges_tax == 0 ? 'none!important' : 'block'}}" id="delivery_charges_tax_id">
                     {!! Form::label('title', __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="delivery_charges_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$vendor->delivery_charges_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($vendor->need_container_charges == 1)
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Container Charges'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="container_charges_tax" class="form-control" data-color="#43bee1" @if($vendor->container_charges_tax == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>



                    <div class="form-group w-100" style="display:{{$vendor->container_charges_tax == 0 ? 'none!important' : 'block'}}" id="container_charges_tax_id">
                     {!! Form::label('title', __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="container_charges_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$vendor->container_charges_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Fixed Fee'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="fixed_fee_tax" class="form-control" data-color="#43bee1" @if($vendor->fixed_fee_tax == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>


                    <div class="form-group w-100" style="display:{{$vendor->fixed_fee_tax == 0 ? 'none!important' : 'block'}}" id="fixed_fee_tax_id">
                     {!! Form::label('title', __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="fixed_fee_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$vendor->fixed_fee_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($vendor->add_markup_price == 1)
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Markup Price'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="markup_fee_tax" class="form-control" data-color="#43bee1" @if($vendor->add_markup_price == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
                    </div>


                    <div class="form-group w-100" style="display:{{$vendor->add_markup_price == 0 ? 'none!important' : 'block'}}" id="markup_price_tax_id">
                     {!! Form::label('title', __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="markup_price_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$vendor->markup_price_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @endif

                </div>

                <div class="row mb-2">

                    <div class="col-md-12">
                        <div class="form-group" id="commission_percentInput">
                            {!! Form::label('title', __('Commission Percent'),['class' => 'control-label']) !!}
                            <input class="form-control" name="commission_percent" type="text" value="{{$vendor->commission_percent}}" onkeypress="return isNumberKey(event)"  onkeydown="if(this.value.length > 6) return false;">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group" id="commission_fixed_per_orderInput">
                            {!! Form::label('title', __('Commission Fixed Per Order'),['class' => 'control-label']) !!}
                            <input class="form-control" name="commission_fixed_per_order" type="text" value="{{$vendor->commission_fixed_per_order}}" onkeypress="return isNumberKey(event)">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group" id="commission_monthlyInput">
                            {!! Form::label('title', 'Commission Monthly',['class' => 'control-label']) !!}
                            <input class="form-control" onkeypress="return isNumberKey(event)" name="commission_monthly" type="text" value="{{$vendor->commission_monthly}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group" id="service_fee_percentInput">
                            {!! Form::label('title', __('Service Fee Percent'),['class' => 'control-label']) !!}
                            <input class="form-control" name="service_fee_percent" type="text" min="0" maxlength="5" value="{{$vendor->service_fee_percent}}" onkeypress="return isNumberKey(event)" onkeydown="if(this.value.length > 6) return false;">
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-info waves-effect waves-light w-100" {{$vendor->status == 1 ? '' : 'disabled'}}>{{ __("Save") }}</button>
                    </div>
                </div>
            </form> 
        </div>
    </div>
</div> --}}

@if($client_preference_detail->business_type == 'laundry')
{{-- <div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <form name="config-form" action="{{route('vendor.config.update', $vendor->id)}}" class="needs-validation" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-2"> <span class="">{{ __("Rescheduling Order Charges") }} ({{ __("Visible For Admin") }})</span></h4>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12">
                        <div class="form-group" id="rescheduling_chargesInput">
                            {!! Form::label('title', __('Rescheduling Charges'),['class' => 'control-label']) !!}
                            <input class="form-control" name="rescheduling_charges" type="text" min="0" maxlength="5" value="{{$vendor->rescheduling_charges}}" onkeypress="return isNumberKey(event)">
                            <small>(When rescheduling is done on the day of delivery.)</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-info waves-effect waves-light w-100" {{$vendor->status == 1 ? '' : 'disabled'}}>{{ __("Save") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <form name="config-form" action="{{route('vendor.config.update', $vendor->id)}}" class="needs-validation" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-2"> <span class="">{{ __("Pickup Cancelling/Rescheduling Charges") }} ({{ __("Visible For Admin") }})</span></h4>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12">
                        <div class="form-group" id="rescheduling_chargesInput">
                            {!! Form::label('title', __('Pickup Cancelling & Rescheduling Charges'),['class' => 'control-label']) !!}
                            <input class="form-control" name="pickup_cancelling_charges" type="text" min="0" maxlength="5" value="{{$vendor->pickup_cancelling_charges}}" onkeypress="return isNumberKey(event)">
                            <small>(When cancelling or rescheduling is done on the day of pickup.)</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-info waves-effect waves-light w-100" {{$vendor->status == 1 ? '' : 'disabled'}}>{{ __("Save") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> --}}
@endif
<style type="text/css">
    #nestable_list_1 ol,
    #nestable_list_1 ul {
        list-style-type: none;
    }
</style>
<div class="card-box">
    <div class="row text-left">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="mb-2"> <span class="">{{ __("Category Setup") }}</span> ({{ __("Visible For Admin") }})</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @if($client_preference_detail->business_type != 'taxi')
        <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
            {!! Form::label('title', __('Can Add Category'),['class' => 'control-label']) !!}
            <input type="checkbox" data-plugin="switchery" name="can_add_category" class="form-control can_add_category1" data-color="#43bee1" @if($vendor->add_category == 1) checked @endif {{$vendor->status == 1 ? '' : 'disabled'}}>
        </div>
        <div class="col-md-12">
            {!! Form::label('title', __('Vendor Detail To Show'),['class' => 'control-label ']) !!}
        </div>

        <div class="col-md-12 mb-3">
            <select class="selectize-select form-control assignToSelect" id="assignTo" {{$vendor->status == 1 ? '' : 'disabled'}}>
                @foreach($templetes as $templete)
                    <option value="{{$templete->id}}" {{$vendor->vendor_templete_id == $templete->id ? 'selected="selected"' : ''}}>{{$templete->title}}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-12">
            {!! Form::label('title', __('Vendor Category'),['class' => 'control-label']) !!}
            <div class="col-sm-12 text-sm-left catalogupdate" style="display: none">
                <div class="alert alert-success">
                    <span class="cattxt"></span>
                </div>
            </div>
            <div class="custom-dd dd nestable_list_1" id="nestable_list_1">
                <ol class="dd-list">
                    @forelse($builds as $build)
                    @if($build['translation_one'])
                    <li class="dd-item dd3-item" data-category_id="{{$build['id']}}">
                        <div class="dd3-content">
                            <img class="rounded-circle mr-1" src="{{$build['icon']['proxy_url']}}30/30{{$build['icon']['image_path']}}"> {{$build['translation_one']['name']}}
                            <span class="inner-div text-right">
                                <a class="action-icon" data-id="3" href="javascript:void(0)">
                                    @if(in_array($build['id'], $VendorCategory))
                                        <input type="checkbox" data-category_id="{{ $build['id'] }}" data-color="#43bee1" class="form-control activeCategory" data-plugin="switchery" checked {{$vendor->status == 1 ? '' : 'disabled'}}>
                                    @else
                                        <input type="checkbox" data-category_id="{{ $build['id'] }}" data-color="#43bee1" class="form-control activeCategory" data-plugin="switchery" {{$vendor->status == 1 ? '' : 'disabled'}}>
                                    @endif
                                    <input type="hidden" value="{{ $build['id'] }}">
                                </a>
                            </span>
                        </div>
                        @if(isset($build['children']))
                            <x-category :categories="$build['children']" :vendorcategory="$VendorCategory" :vendor="$vendor"/>
                        @endif
                        </li>
                    </li>
                    @endif
                    @empty
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
</div>
@endif
<style type="text/css">
    #nestable_list_1 ol, #nestable_list_1 ul{
        list-style-type: none;
    }
</style>


 <div class="card-box">
    <h4 class="header-title mb-0 mt-2 d-inline-block align-middle">{{ __('Users') }}</h4>
    <h4 class="header-title mb-0 float-right"><a class="btn addUsersBtn" dataid="0" href="javascript:void(0);"><i class="mdi mdi-plus-circle mr-1" ></i> {{ __("Add Users") }}
    </a></h4>

    <div class="inbox-widget mt-3" data-simplebar style="max-height: 350px;">
        @foreach($vendor->permissionToUser as $users)
            @if($users->user)
                <div class="inbox-item pb-0">
                    <div class="inbox-item-img">
                        <img src="{{$users->user ? $users->user->image['proxy_url'].'40/40'.$users->user->image['image_path'] : asset('assets/images/users/user-2.jpg')}}" class="rounded-circle" alt="">
                    </div>
                    <p class="inbox-item-author">{{ $users->user->name??'' }}  </p>
                    <p class="inbox-item-text"><label class="d-block"><i class="fa fa-envelope mr-1" aria-hidden="true"></i> {{ $users->user->email??'' }}
                        @if($users->user)
                        </label><label class="d-block"><i class="fa fa-phone mr-1" aria-hidden="true"></i> {{ $users->user->phone_number??'' }}</label> </p>
                        @endif
                    </p>
                    @if($users->user && $users->user->id != Auth::id())
                    <form class="delete-user position-absolute" method="POST" action="{{route('user.vendor.permission.destroy', $users->id)}}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-primary-outline" onclick="return confirm('Are you sure ?');"> <i class="mdi mdi-delete"></i></button>
                    </form>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</div>

<div id="manageSocialMedia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg social_manage">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Manage Social Media URLs") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
                <form id="add_manage_social_media" method="post" enctype="multipart/form-data" action="{{route('permissionsForUserViaVendor')}}" autocomplete="off">
                @csrf
                <div class="modal-body" id="AddAddonBox">
                {!! Form::hidden('vendor_id', $vendor->id) !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row rowYK mb-2">
                                <div class="col-md-12">
                                    <h5 class="add_field">{{ __("Add URLs") }}</h5>
                                    <!-- <button type="button" class="btn btn-info waves-effect waves-light addUrlRow-Add float-right">{{ __("Add URLs") }}</button> -->
                                </div>
                                <div class="col-md-12" style="overflow-x: auto;">
                                    <table class="table table-borderless mb-0 urlTableAdd" id="banner-datatable">
                                        <tr class="trForClone">
                                            <th>{{ __("Icon") }}</th>
                                            <th>{{ __("URL") }}</th>
                                            <th></th>
                                        </tr>
                                        <tr class="input_tr">
                                            <td><select class="form-control" id="social_icon" name="icon">
                                                <option value="facebook"> Facebook </option>
                                                <option value="github"> Github </option>
                                                <option value="reddit"> Reddit </option>
                                                <option value="whatsapp"> Whatsapp </option>
                                                <option value="instagram"> Instagram </option>
                                                <option value="tumblr"> Tumblr </option>
                                                <option value="twitch"> Twitch </option>
                                                <option value="twitter"> Twitter </option>
                                                <option value="pinterest"> Pinterest </option>
                                                <option value="youtube"> Youtube </option>
                                                <option value="snapchat"> Snapchat </option>
                                                <option value="linkedin"> Linkedin-in </option>
                                            </select></td>
                                            <td><input type="text" class="form-control" id="social_url" name="url" required='required'></td>
                                            <td class="lasttd manage_social text-center"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <button type="submit" class="btn btn-info waves-effect waves-light addUrlSubmit w-100">{{ __("Submit") }}</button>
                    </div>
                </div>
                </form>
                <div class="modal-footer">
                    <div class="table-responsive mt-3">
                        <table class="table table-centered table-nowrap table-striped" id="social-media-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __("Icon") }}</th>
                                    <th class="text-center">{{ __("URL") }}</th>
                                    <th>{{ __("Action") }}</th>
                                </tr>
                            </thead>
                            <tbody id="social-media-list">
                            @php
                            $mediaIcons = [];
                            @endphp

                            @if (isset($socialMediaUrls))
                                @forelse($socialMediaUrls as $socialMediaUrl)

                                <tr align="center">
                                @php
                                $mediaIcons[] = $socialMediaUrl->icon;
                                @endphp
                                    <td>
                                        <i class="fab fa-{{$socialMediaUrl->icon}}  social-media-{{$socialMediaUrl->icon}}" aria-hidden="true"></i>
                                    </td>
                                    <td>
                                        <a href="{{$socialMediaUrl->url}}" class="social-media-url-{{$socialMediaUrl->icon}}" target="_blank">{{$socialMediaUrl->url}}</a>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="inner-div">
                                                <button type="button" class="btn btn-primary-outline action-icon delete_social_media_option_btn" data-social_media_detail_id="{{$socialMediaUrl->id}}">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr align="center">
                                    <td colspan="4" style="padding: 20px 0">{{ __("Result not found.") }}</td>
                                </tr>
                                @endforelse
                            @endif
                            </tbody>
                        </table>
                        <input type="hidden" id="added-icons" value="{{ json_encode($mediaIcons) }}">
                    </div>
                </div>


        </div>
    </div>
</div>

<div id="addBannner-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add Banner Image") }} </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>


            <form id="save_multi_banner_form" method="post" enctype="multipart/form-data">
                @csrf
              <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                <div class="modal-body" id="editCardBox">
                    <div class="">
                        <label>{{ __('Upload Banner') }}</label>
                        <input type="file" accept="image/*" data-plugins="dropify" name="banner_image" class="dropify" />

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light submitMultibannerForm">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">

                @php
                $vendors = getNomenclatureName('vendors', false);
                $newvendors = ($vendors === "vendors") ? __('vendors') : $vendors ;
                @endphp

                <h4 class="modal-title">{{ __("Edit") }} {{ $newvendors }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>


            <form id="save_edit_banner_form" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editCardBox">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light submitEditForm">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div id="add-user-permission" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __("Add User") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="add_user_permission_vendor" method="post" enctype="multipart/form-data" action="{{route('permissionsForUserViaVendor')}}" autocomplete="off">
                @csrf
                <input type="hidden" name="vendor_id" value="{{$vendor->id}}" id="set-vendor_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <div class="form-group" id="skuInput">
                                        {!! Form::text('search', null, ['class' => 'form-control', 'placeholder' => __('Search User'), 'id' => 'id_search_user_for_permission', 'required' => 'required']) !!}
                                        <input type="hidden" id='cusid' name="ids" readonly>
                                        <div id="userList">
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <span class="text-danger" id="error-msg"></span>
                <span class="text-success" id="success-msg"></span>
                <div class="modal-footer">
                    <button  class="btn btn-info waves-effect waves-light" id="user_permission_form_button">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- <script src="{{ asset('assets/ck_editor/ckeditor.js')}}"></script> --}}
<script type="text/javascript">

$('.addUsersBtn').click(function() {
    $('#add-user-permission').modal({
        keyboard: false
    });
});


$( document ).ready(function() {
    @if($client_preference_detail->business_type != 'taxi')
        vendorOrderTime();
    @endif
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
    $(document).on('change', '#Vendor_order_pre_time', function(){
        vendorOrderTime();
    });
    function vendorOrderTime(){
       var min = $('#Vendor_order_pre_time').val();
       //alert(min);
       if(min >=60){
            var hours = Math.floor(min / 60);
            var minutes = min % 60;
            if( minutes <= 9)
            minutes ='0'+minutes;


            var txt = '~ '+hours+':'+minutes+" {{__('Hours')}}";
            $('#Vendor_order_pre_time_show').text(txt);
       }else{
            var txt = min+" {{__('Min')}}";
            $('#Vendor_order_pre_time_show').text(txt);
       }
    }

    $('#add_manage_social_media').submit(function(e) {

        e.preventDefault();

        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: "{{ route('vendor.social.media.urls') }}",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
            $("#user_permission_form_button").html(
                        '<i class="fa fa-spinner fa-spin fa-custom"></i> Loading').prop(
                        'disabled', true);
            },
            success: (res) => {
                if(res.status == 'Success'){
                    var icon = "success";
                    var addedIcon = 'social-media-' + res.message.icon;

                    console.log('addedIcon', addedIcon);
                    if ( $('.'+addedIcon).length ) {
                        var addedurl = 'social-media-url-'+ res.message.icon;
                        $("."+addedurl).text(res.message.url);
                        $("."+addedurl).attr('href', res.message.url);
                    }else{
                        $('#social-media-datatable tr:last').after('<tr><td><i class="fab fa-'+res.message.icon+'  social-media-'+res.message.icon+'" aria-hidden="true"></i></td>'+
                                '<td><a href="'+res.message.url+'" class="social-media-url-'+res.message.icon+'" target="_blank">'+res.message.url+'</a></td>'+
                                '<td><div><div class="inner-div"><button type="button" class="btn btn-primary-outline action-icon delete_social_media_option_btn" data-social_media_detail_id="'+res.message.media+'"><i class="mdi mdi-delete"></i></button></div></div></td></tr>');
                    }

                    $('#social_url').val('');


                }else{
                    var icon = "error";
                }

                Swal.fire({
                    text: res.data,
                    icon: icon,
                    button: "OK",
                });

            },
            error: function(data) {
                $('#error-msg').text(data.message);
                $("#user_permission_form_button").html('Submit').prop('disabled',
                    false);
            }
        });
    });

    // $(".addUrlSubmit").click(function(e) {
    //     e.preventDefault();
    //     var addedIcons = $('#added-icons').val();
    //     var socialIcon = $('#social_icon').val();
    //     var socialUrl = $('#social_url').val();

    //     var ajaxUrl = "{{route('vendor.social.media.urls')}}";

    // });

    $(".openSocialMedia").click(function(e) {
        $('#manageSocialMedia').modal({
            backdrop: 'static',
            keyboard: false
        });
        $('#social_url').val('');
    });
    $(document).on('click', '.addUrlRow-Add', function(e) {
        var rowCount = $('#social-media-list tr').length;

        if(rowCount == 12){
            console.log('rowCount', rowCount);
            return false;
        }
        var $tr = $('.urlTableAdd tbody>tr:first').next('tr');
        var $clone = $tr.clone();
        $clone.find(':text').val('');
        $clone.find('.lasttd').html('<a href="javascript:void(0);" class="action-icon deleteUrlRow"> <i class="mdi mdi-delete"></i></a>');
        $('.urlTableAdd').append($clone);
    });

    $("#manageSocialMedia").on('click', '.deleteUrlRow', function() {
        $(this).closest('tr').remove();
    });

    // search users for set permission
    $('#id_search_user_for_permission').keyup(function(){
        var query = $(this).val();
        var vendor_id = $('#set-vendor_id').val();
        if(query != '')
        {
            var _token = $('input[name="_token"]').val();
            $.ajax({
            url:"{{ route('searchUserForPermission') }}",
            method:"POST",
            data:{query:query, _token:_token, vendor_id:vendor_id},
            success:function(data){
            $('#userList').fadeIn();
            $('#userList').html(data);
            }
            });
        }
    });

    $(document).on('click', 'li', function(){
        $('#id_search_user_for_permission').val($(this).text());
        $('#cusid').val($(this).attr('data-id'));
        $('#userList').fadeOut();
    });


    // submit permission for user
    $('#add_user_permission_vendor').submit(function(e) {

        e.preventDefault();

        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: "{{ route('permissionsForUserViaVendor') }}",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
            $("#user_permission_form_button").html(
                        '<i class="fa fa-spinner fa-spin fa-custom"></i> Loading').prop(
                        'disabled', true);
            },
            success: (data) => {
                if (data.status == 'Success') {
                $("#user_permission_form_button").html('Submitted');
                location.reload();
                } else {
                    $('#error-msg').text(data.message);
                    $("#user_permission_form_button").html('Submit').prop('disabled',
                        false);
                }
            },
            error: function(data) {
                $('#error-msg').text(data.message);
                $("#user_permission_form_button").html('Submit').prop('disabled',
                    false);
            }
        });
    });

    $(document).on('click', '#approve_btn, #reject_btn, #block_btn', function(){
        var that  = $(this);
        var status = that.data('status');
        var vendor_id = that.data('vendor_id');
        var text = that.text().toLowerCase();
        var message = "Are you sure want to "+text+" this vendor?";
        if(confirm(message)){
            $.ajax({
                type: "POST",
                url: "{{route('vendor.status')}}",
                data: { vendor_id: vendor_id , status:status},
                success: function(data) {
                    if(data.status == 'success'){
                    $.NotificationApp.send("Success", data.message, "top-right", "#5ba035", "success");
                    window.location.href = "{{ route('vendor.index') }}";
                    }
                }
            });
        }
    });
    $(document).on('change', '.can_add_category1', function(){
        var vendor_id = "{{$vendor->id}}";
        var can_add_category = $(this).is(":checked");
        var url = "{{ url('client/vendor/activeCategory').'/'.$vendor->id}}"
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {vendor_id:vendor_id, can_add_category:can_add_category},
            success: function(response) {
                if (response.status == 'Success') {

                }
            }
        });
    });
    $(document).on('change', '#assignTo', function(){
        var assignTo = $(this).val();
        var vendor_id = "{{$vendor->id}}";
        var url = "{{ url('client/vendor/activeCategory').'/'.$vendor->id}}"
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {vendor_id:vendor_id, assignTo:assignTo},
            success: function(response) {
                if (response.status == 'Success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    });
    $(document).on('change', '.activeCategory', function(){
        var vendor_id = "{{$vendor->id}}";
        var status = $(this).is(":checked");
        var category_id = $(this).data('category_id');
        var url = "{{ url('client/vendor/activeCategory').'/'.$vendor->id}}"
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {category_id: category_id, status:status, vendor_id:vendor_id},
            success: function(response) {
                if (response.status == 'Success') {
                    // console.log(response.data);
                    // $('.cattxt').text('Updated successfully');
                    $.NotificationApp.send("Success", 'Updated successfully', "top-right", "#5ba035", "success");
                    // $('.catalogupdate').css('display','');
                    setTimeout(function() {
                        // $('.cattxt').text('');
                        // $('.catalogupdate').css('display','none');
                    }, 1000);



                    if(response.data.check_pickup_delivery_service == 1)
                    {
                        $('.for_pickup_delivery_service_only').html('<button type="button" class="btn btn-danger btn-sm waves-effect mb-2 waves-light openConfirmDispatcher" data-id="'+response.data.product_categories[0].vendor_id+'">{{__("Login Into Dispatcher (Pickup & Delivery)")}} </button>');
                    }else{
                        $('.for_pickup_delivery_service_only').html('');
                    }
                    if(response.data.check_on_demand_service == 1)
                    {
                        $('.for_on_demand_service_only').html('<button type="button" class="btn btn-danger btn-sm waves-effect mb-2 waves-light openConfirmDispatcherOnDemand" data-id="'+response.data.product_categories[0].vendor_id+'">{{__("Login Into Dispatcher (On Demand Services)")}} </button>');
                    }else{
                        $('.for_on_demand_service_only').html('');
                    }
                    if(response.data.check_appointment_service == 1)
                    {
                        $('.for_appointment_delivery_service_only').html('<button type="button" class="btn btn-danger btn-sm waves-effect mb-2 waves-light openConfirmAppointmentDispatcher" data-id="'+response.data.product_categories[0].vendor_id+'">{{__("Login Into Dispatcher (On Demand Services)")}} </button>');
                    }else{
                        $('.for_appointment_delivery_service_only').html('');
                    }


                    $('#category_list').html('');
                    $('#category_list').html('<option value="">Select Category...</option>');
                    $('#category_list').selectize()[0].selectize.destroy();
                    $.each(response.data.product_categories, function (key, value) {
                        if(value.category.type_id == 1){
                        $('#category_list').append('<option value='+value.category_id+'>'+value.category.title+'</option>');
                        }
                    });
                }
            }
        });
    });
});

$("input[name='auto_accept_order']").change(function() {
    if($(this).prop('checked')){
        $("#auto_reject_timeInput").css("display", "none");
    } else {
        $("#auto_reject_timeInput").css("display", "block");
    }
});

$("input[name='show_slot']").change(function() {
    if($(this).prop('checked')){
        $("#sch_vendor_close").css("display", "none");
    } else {
        $("#sch_vendor_close").css("display", "block");
    }
})

$("input[name='fixed_fee']").change(function() {
    if($(this).prop('checked')){
        $("#fixed_fee_amount").css("display", "block");
    } else {
        $("#fixed_fee_amount").css("display", "none");
    }
})

$("input[name='delivery_charges_tax']").change(function() {
    if($(this).prop('checked')){
        $("#delivery_charges_tax_id").css("display", "block");
    } else {
        $("#delivery_charges_tax_id").css("display", "none");
    }
})

$("input[name='service_charges_tax']").change(function() {
    if($(this).prop('checked')){
        $("#service_charges_tax_id").css("display", "block");
    } else {
        $("#service_charges_tax_id").css("display", "none");
    }
})

$("input[name='container_charges_tax']").change(function() {
    if($(this).prop('checked')){
        $("#container_charges_tax_id").css("display", "block");
    } else {
        $("#container_charges_tax_id").css("display", "none");
    }
})

$("input[name='fixed_fee_tax']").change(function() {
    if($(this).prop('checked')){
        $("#fixed_fee_tax_id").css("display", "block");
    } else {
        $("#fixed_fee_tax_id").css("display", "none");
    }
})

$("input[name='markup_fee_tax']").change(function() {
    if($(this).prop('checked')){
        $("#markup_fee_tax_id").css("display", "block");
    } else {
        $("#markup_fee_tax_id").css("display", "none");
    }
})

$("input[name='need_container_charges']").change(function() {
    if($(this).prop('checked')){
        $("#need_container_charges").css("display", "none");
    } else {
        $("#need_container_charges").css("display", "block");
    }
})
</script>
{{-- <script>
    var dynamic_html = "";
    CKEDITOR.replace('edit_description');
    CKEDITOR.config.height = 250;
</script> --}}
