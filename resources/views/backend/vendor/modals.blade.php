<div id="add-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                @php
                    $vendor_name = getNomenclatureName('vendors', false);
                    $vendor_name = ($vendor_name === 'Vendor') ? __('Vendor') : $vendor_name ;
                @endphp
                <h4 class="modal-title">{{ __("Add") }} {{ $vendor_name }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_banner_form" class="al_overall_form" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="new_model" value="1">
                <div class="modal-body" >
                    <div class="row">
                        <div class="col-md-8">

                            <div class="row mb-2">
                                <div class="col-md-3">
                                    <label>{{ __('Upload Logo') }} </label>
                                    <input type="file" accept="image/*" data-plugins="dropify" name="logo" class="dropify" data-default-file="" />
                                    <label class="logo-size text-right w-100">{{ __('Logo Size') }} 170x96</label>
                                </div>
                                <div class="col-md-9">
                                    <label>{{ __('Upload banner image') }}</label>
                                    <input type="file" accept="image/*" data-plugins="dropify" name="banner" class="dropify" data-default-file="" />
                                    <label class="logo-size text-right w-100">{{ __("Image Size") }} 830x200</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" id="nameInput">
                                        {!! Form::label('title', __('Name'),['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class'=>'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="emailInput">
                                        <label for="">{{ __('Email') }}</label>
                                        {!! Form::text('email', null, ['class'=>'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                            </div>
                            <div class="row">


                                <div class="col-md-4 form-group" id="phone_noInput">
                                    {!! Form::label('title', __('Phone Number'),['class' => 'control-label']) !!}
                                    <input type="tel" class="form-control phone" id="vendor_phone_number" placeholder={{ __("Phone Number") }} name="phone_no" value="{{ old('full_number')}}">
                                    <input type="hidden" id="vendorCountryCode" name="vendor_country" value="{{ old('vendor_country') ? old('vendor_country') : 'us'}}">
                                    <input type="hidden" id="vendorDialCode" name="vendor_dial_code" value="{{ old('vendor_dial_code') ? old('vendor_dial_code') : Session::get('default_country_phonecode',1) }}">

                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">{{ __('Website') }}</label>
                                        <input class="form-control" type="text" name="website">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3"  id="pincodeInput">
                                        {!! Form::label('title', __('Pincode'),['class' => 'control-label']) !!}
                                        <input type="text" name="pincode" id="pincode" placeholder="" class="form-control" value="{{@$vendor->pincode}}">
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="add">
                                <div class="col-md-12">
                                    <div class="form-group mb-3" id="addressInput">
                                        {!! Form::label('title', __('Address'),['class' => 'control-label']) !!}
                                        <div class="input-group">
                                            <input type="text" name="address" id="add-address" onkeyup="checkAddressString(this,'add')" placeholder="" class="form-control">
                                            <div class="input-group-append">
                                                <button class="btn btn-xs btn-dark waves-effect waves-light showMap" type="button" num="add"> <i class="mdi mdi-map-marker-radius"></i></button>
                                            </div>
                                        </div>
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="latitudeInput">
                                        {!! Form::label('title', __('Latitude'),['class' => 'control-label']) !!}
                                        <input type="text" name="latitude" id="add_latitude" placeholder="" class="form-control" value="">
                                        @if($errors->has('latitude'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('latitude') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="longitudeInput">
                                        {!! Form::label('title', __('Longitude'),['class' => 'control-label']) !!}
                                        <input type="text" name="longitude" id="add_longitude" placeholder="" class="form-control" value="">
                                        @if($errors->has('longitude'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('longitude') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="cityInput">
                                        {!! Form::label('title', __('City'),['class' => 'control-label']) !!}
                                        <input type="text" name="city" id="city" placeholder="" class="form-control" value="{{@$vendor->city}}">
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="stateInput">
                                        {!! Form::label('title', __('State'),['class' => 'control-label']) !!}
                                        <input type="text" name="state" id="state" placeholder="" class="form-control" value="{{@$vendor->state}}">
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="countryInput" >
                                        {!! Form::label('title', __('Country'),['class' => 'control-label']) !!}
                                        <input type="text" name="country" id="country" placeholder="" class="form-control" value="{{@$vendor->country}}">
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    @if(@$getAdditionalPreference['is_seller_module'] == '1')
                                        <div class="form-group">
                                            <label for="vendortype">{{__('Vendor Type')}}</label>
                                            <select name="vendor_type" id="vendor_type" class="form-control">
                                                <option value="1">Seller</option>
                                                <option value="0">Vendor</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group" id="descInput">
                                        {!! Form::label('title', __('Description'),['class' => 'control-label']) !!}
                                        {!! Form::textarea('desc', null, ['class' => 'form-control', 'rows' => '3','style' => 'height: 100px;']) !!}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                <div class="row">
                                    @php
                                        $typeArray = getCategoryTypes();
                                    @endphp
                                    @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
                                        @php
                                            $clientVendorTypes = $vendor_typ_key.'_check';
                                            $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                                        @endphp
                                        @if(($client_preferences->$clientVendorTypes == 1) && in_array($vendor_typ_key, $typeArray) )

                                            <div class="col-sm-3">
                                                <div class="form-group d-flex justify-content-between">
                                                    <label for="{{$VendorTypesName}}" class="mr-3 mb-0">{{getDynamicTypeName($vendor_typ_value)}}</label>
                                                    <input type="checkbox" data-plugin="switchery" name="{{$VendorTypesName}}" id="{{$VendorTypesName}}" class="form-control vendorTypeChange" data-color="#43bee1" checked='checked'>
                                                </div>
                                            </div>

                                        @endif
                                    @endforeach
                                    </div>
                                    <!-- <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            @if($client_preferences->dinein_check == 1)
                                                {!! Form::label('title', getNomenclatureName('Dine-In', true),['class' => 'control-label']) !!}
                                                <div class="mt-md-1">
                                                    <input type="checkbox" data-plugin="switchery" name="dine_in" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                </div>
                                            @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            @if($client_preferences->takeaway_check == 1)
                                                {!! Form::label('title', getNomenclatureName('Takeaway', true),['class' => 'control-label']) !!}
                                                <div class="mt-md-1">
                                                    <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                </div>
                                            @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            @if($client_preferences->delivery_check == 1)
                                                {!! Form::label('title', getNomenclatureName('Delivery', true) ,['class' => 'control-label']) !!}
                                                <div class="mt-md-1">
                                                    <input type="checkbox" data-plugin="switchery" name="delivery" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                </div>
                                            @endif
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>

                        <!-- al_add_user start -->
                        <div class="col-md-4 al_add_user">
                            <h5>{{__("SEARCH USER")}} </h5>
                            <div class="form-group">
                                <div class="btn-group w-100">
                                    {{-- data-toggle="dropdown" aria-haspopup="true" --}}
                                    <input type="text" id="search_user_for_permission" class="dropdown-toggle d-block form-control w-100" value="" name="user_search_name" autocomplete="off" aria-expanded="false" placeholder="search user">
                                </div>
                                <div id="userList_model"></div>
                                    <div class="dropdown-menu_al w-100" id="selected_user_ul" >
                                        <ul id="selected_user" class="pl-2 ">
                                        </ul>
                                    </div>
                            </div>
                            <h5>{{__('ADD USER')}} </h5>
                            <div id="adduesr_error"></div>
                            <div class="form-group">
                                <label for="title" class="control-label">{{__("Name")}}</label>
                                <input class="form-control" name="user_name" id="new_user_name" type="text">
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="title" class="control-label">Email</label>
                                    <input class="form-control" name="user_email" id="new_user_email" type="email">
                                </div>
                                <div class="col-md-12 form-group" id="phone_numberInput">
                                    {!! Form::label('title', __('Phone Number'),['class' => 'control-label']) !!}
                                    <input type="tel" class="form-control phone" id="new_user_phone_number" placeholder={{ __("Phone Number") }} name="phone_number" value="{{ old('full_number')}}">
                                    <input type="hidden" id="countryCode" name="country_code" value="{{ old('countryData') ? old('countryData') : 'us'}}">
                                    <input type="hidden" id="dialCode" name="dial_code" value="{{ old('dialCode') ? old('dialCode') : Session::get('default_country_phonecode',1) }}">

                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                                {{-- <div class="col-md-6 form-group">
                                    <label for="title" class="control-label">Phone Number</label>
                                    <input class="form-control" name="phone_number" id="new_user_phone_number" type="tel">
                                </div> --}}
                            </div>
                            <div class="form-group">
                                <label for="title" class="control-label">Password</label>
                                <input type="password" class="form-control" id="new_user_password"  placeholder="Password" required="" name="password" value="">
                            </div>
                            <a  id="addUserAddForm" class="btn w-100 btn-info waves-effect waves-light " data-url="{{route('customer.store')}}">{{__('ADD NEW USER')}}</a>

                        </div>
                    </div><!-- al_add_user end -->

                    @if(@$getAdditionalPreference['is_gst_required_for_vendor_registration'] == '1')
                    <h5 class="mb-2">{{__('GST DETAILS')}}</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="companyname">{{__('Company Name')}}</label>
                                    <input type="text" class="form-control" name="company_name" placeholder="Company Name" value="">
                                    <span class="invalid-feedback" id="company_name_error"><strong></strong></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gstNoInput">{{__('GST Number')}}</label>
                                    <input type="text" class="form-control" name="gst_num_Input" placeholder="GST Number" value="">
                                    <span class="invalid-feedback" id="title_error"><strong></strong></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(@$getAdditionalPreference['is_baking_details_required_for_vendor_registration'] == '1')
                    <h5 class="mb-2">{{__('Banking DETAILS')}}</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="accountname">{{__('Account Name')}}</label>
                                    <input type="text" class="form-control" name="account_name" placeholder="Account Name" value="">
                                    <span class="invalid-feedback" id="account_name_error"><strong></strong></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bankname">{{__('Bank Name')}}</label>
                                    <input type="text" class="form-control" name="bank_name" placeholder="Bank Name" value="" placeholder="">
                                    <span class="invalid-feedback" id="title_error"><strong></strong></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="accountnumber">{{__('Account Number')}}</label>
                                    <input type="text" class="form-control" name="account_number" placeholder="Account Number" value="" placeholder="">
                                    <span class="invalid-feedback" id="account_number_error"><strong></strong></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ifsccode">{{getNomenclatureName('IFSC Code', true)}}</label>
                                    <input type="text" class="form-control" name="ifsc_code" placeholder="{{getNomenclatureName('IFSC Code', true)}}" value="" placeholder="">
                                    <span class="invalid-feedback" id="ifsc_code_error"><strong></strong></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <!-- al_custom_modal start ADVANCED DETAILS -->
                        <div class="al_custom_modal col-md-12 pt-2 border-top">
                                <h5 class="mb-2">{{__('ADVANCED DETAILS')}}</h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="al_advanced_details p-2">
                                            <p class="al_custom_title mb-1">{{__('Configuration')}}</p>
                                                 @if($client_preference_detail->business_type != 'taxi')
                                                    <div class="form-group">

                                                        {!! Form::label('title', __('Order Prepare Time(In minutes)'),['class' => 'control-label']) !!}
                                                        <div class="position-relative">
                                                            <input class="form-control" onkeypress="return isNumberKey(event)" name="order_pre_time" id="Vendor_order_pre_time" type="text" value="{{ (isset($vendor)) ? @$vendor->order_pre_time : 0 }}" {{(isset($vendor)) ? (($vendor->status ?? 0) == 1 ? '' : 'disabled') : ''}}>
                                                            <div class="time-sloat d-flex align-items-center"><span class="" id="Vendor_order_pre_time_show" ></span> </div>
                                                        </div>

                                                    </div>
                                                @endif
                                                <div class="row">

                                                    @if($client_preference_detail->business_type != 'taxi')
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            {!! Form::label('title', __('24*7 Availability'),['class' => 'control-label']) !!}
                                                            <div class="mt-md-1">
                                                                <input type="checkbox" data-plugin="switchery" name="show_slot" class="form-control" data-color="#43bee1" @if(@$vendor->show_slot == 1) checked @endif {{($vendor ?? false) ? ($vendor->status == 1 ? '' : 'disabled') : ''}}>
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">

                                                            {!! Form::label('title', __('Auto Accept Order'),['class' => 'control-label']) !!}
                                                            <div class="mt-md-1">
                                                                <input type="checkbox" data-plugin="switchery" name="auto_accept_order" class="form-control" data-color="#43bee1" @if(@$vendor->auto_accept_order == 1) checked @endif {{($vendor ?? false) ? ($vendor->status == 1 ? '' : 'disabled') : ''}}>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">

                                                            {!! Form::label('title', __('Return Request'),['class' => 'control-label']) !!}
                                                            <div class="mt-md-1">
                                                                <input type="checkbox" data-plugin="switchery" name="return_request" class="form-control" data-color="#43bee1" @if(@$vendor->return_request == 1) checked @endif {{($vendor ?? false) ? ($vendor->status == 1 ? '' : 'disabled') : ''}}>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(Auth::user()->is_superadmin == 1)
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            {!! Form::label('title', __('Show Profile Details'),['class' => 'control-label']) !!}
                                                            <div class="mt-md-1">
                                                                <input type="checkbox" data-plugin="switchery" name="is_show_vendor_details" class="form-control" data-color="#43bee1" @if(@$vendor->is_show_vendor_details == 1) checked @endif {{($vendor ?? false) ? ($vendor->status == 1 ? '' : 'disabled') : ''}}>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                @if($client_preference_detail->business_type != 'taxi')
                                                    <div class="form-group">
                                                        {!! Form::label('title', __('Auto Reject Time(In minutes, 0 for no rejection)'),['class' => 'control-label']) !!}
                                                        <input class="form-control" name="auto_reject_time" type="number" value="{{@$vendor->auto_reject_time}}" min="0" {{(isset($vendor)) ? (($vendor->status ?? 0) == 1 ? '' : 'disabled') : ''}} >

                                                    </div>

                                                    <div class="form-group">
                                                        {!! Form::label('title', __('Slot Duration (In minutes)'),['class' => 'control-label']) !!}
                                                        <select class="form-control" name="slot_minutes">
                                                            <option value="">{{__('Slot Duration')}}</option>
                                                            <option value="15" {{ isset($vendor) ? ($vendor->slot_minutes == '15'? 'selected':'') : ''}}>15 {{__(' Minutes')}}</option>
                                                            <option value="30" {{ isset($vendor) ? ($vendor->slot_minutes == '30'? 'selected':'') : ''}}>30 {{__(' Minutes')}}</option>
                                                            <option value="45" {{ isset($vendor) ? ($vendor->slot_minutes == '45'? 'selected':'') : ''}}>45 {{__(' Minutes')}}</option>
                                                            @for($i=1;$i<=8;$i++)
                                                                <option value="{{$i*60}}" {{ isset($vendor) ? ($vendor->slot_minutes == ($i*60)? 'selected':'') : ''}}>{{ $i. __(' Hour')}}</option>
                                                            @endfor
                                                        </select>
                                                    </div>

                                                        <div class="form-group" id="order_min_amountInput">
                                                            {!! Form::label('title', 'Absolute Min Order Value [AMOV]',['class' => 'control-label']) !!}
                                                            <input class="form-control" onkeypress="return isNumberKey(event)" name="order_min_amount" type="text" value="{{@$vendor->order_min_amount}}" {{(isset($vendor)) ? (($vendor->status ?? 0) == 1 ? '' : 'disabled') : ''}}>
                                                        </div>

                                                @endif
                                        </div>
                                    </div>
                                    @if(Auth::user()->is_superadmin == 1)
                                        <div class="col-md-4">
                                            <div class="al_advanced_details p-2">
                                                <p class="al_custom_title mb-1"><span class="">{{ __("Commission") }}</span> ({{ __("Visible For Admin") }})</p>

                                                    <div class="form-group">
                                                        {!! Form::label('title', __('Commission Percent'),['class' => 'control-label']) !!}
                                                        <input class="form-control" name="commission_percent" type="text" value="{{@$vendor->commission_percent}}" onkeypress="return isNumberKey(event)"  onkeydown="if(this.value.length > 6) return false;">

                                                    </div>
                                                    <div class="form-group">
                                                        {!! Form::label('title', __('Commission Fixed Per Order'),['class' => 'control-label']) !!}
                                                        <input class="form-control" name="commission_fixed_per_order" type="text" value="{{@$vendor->commission_fixed_per_order}}" onkeypress="return isNumberKey(event)">
                                                    </div>
                                                    <div class="form-group">
                                                        {!! Form::label('title', __('Service Fee Percent'),['class' => 'control-label']) !!}
                                                        <input class="form-control" name="service_fee_percent" type="text" min="0" maxlength="5" value="{{@$vendor->service_fee_percent}}" onkeypress="return isNumberKey(event)" onkeydown="if(this.value.length > 6) return false;">

                                                    </div>

                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-4">
                                        @if($client_preference_detail->business_type != 'taxi')
                                        <div class="mb-2 d-flex align-items-center justify-content-between">
                                            {!! Form::label('title', __('Can Add Category'),['class' => 'control-label']) !!}
                                            <input type="checkbox" data-plugin="switchery" name="can_add_category" class="form-control can_add_category1" data-color="#43bee1" @if( (@$vendor->add_category == 1)) checked @endif >
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-1">
                                                {!! Form::label('title', __('Vendor Detail To Show'),['class' => 'control-label ']) !!}
                                            </div>

                                            <div class="col-md-6 mb-1 pl-0">
                                                <select class="selectize-select form-control assignToSelect" name="assignTo" id="assignTo" >
                                                    @foreach($templetes as $templete)
                                                        <option value="{{$templete->id}}" {{@$vendor->vendor_templete_id == $templete->id ? 'selected="selected"' : ''}}>{{$templete->title}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-12 p-0">
                                            {!! Form::label('title', __('Vendor Category'),['class' => 'control-label']) !!}
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
                                                                        <input type="checkbox" data-category_id="{{ $build['id'] }}" data-color="#43bee1" class="form-control activeCategory" data-plugin="switchery" checked >
                                                                    @else
                                                                        <input type="checkbox" data-category_id="{{ $build['id'] }}" data-color="#43bee1" class="form-control activeCategory" data-plugin="switchery" >
                                                                    @endif
                                                                    <input type="hidden" value="{{ $build['id'] }}">
                                                                </a>
                                                            </span>
                                                        </div>
                                                        @if(isset($build['children']))
                                                            <x-category :categories="$build['children']" :vendorcategory="$VendorCategory" :vendor="@$vendor"/>
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
                            </div><!-- al_custom_modal end -->
                    </div>

                    <div class="row mt-3 al_licence_imgbox">
                        <div class="col-md-6">
                            <div class="row">
                            @foreach($vendor_registration_documents as $vendor_registration_document)
                                @if(isset($vendor_registration_document->primary) && !empty($vendor_registration_document->primary))
                                    @if(strtolower($vendor_registration_document->file_type) == 'selector')
                                        <div class="col-md-6 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
                                            <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}</label>
                                            <select class="form-control {{ (!empty($vendor_registration_document->is_required))?'required':''}}" name="{{$vendor_registration_document->primary->slug}}"  id="input_file_selector_{{$vendor_registration_document->id}}">
                                                <option value="" >{{__('Please Select '). ($vendor_registration_document->primary ? $vendor_registration_document->primary->name : '') }}</option>
                                                @foreach ($vendor_registration_document->options as $key =>$value )
                                                    <option value="{{$value->id}}">{{$value->translation? $value->translation->name: ""}}</option>
                                                @endforeach
                                            </select>
                                            <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug}}_error"><strong></strong></span>
                                        </div>
                                    @else
                                        <div class="col-md-6" >
                                            <div class="form-group" id="{{$vendor_registration_document->primary->slug??''}}Input">
                                                <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}</label>
                                                @if(strtolower($vendor_registration_document->file_type) == 'text')
                                                    <input id="input_file_logo_{{$vendor_registration_document->id}}" type="text" name="{{$vendor_registration_document->primary->slug}}" class="form-control">
                                                @else
                                                    @if(strtolower($vendor_registration_document->file_type) == 'image')
                                                    <input type="file" accept="image/*" data-plugins="dropify" name="{{$vendor_registration_document->primary->slug}}" class="dropify" data-default-file="" />
                                                    @else
                                                    <input type="file" accept=".pdf" data-plugins="dropify" name="{{$vendor_registration_document->primary->slug}}" class="dropify" data-default-file="" />
                                                    @endif
                                                @endif
                                                <span class="invalid-feedback" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                            </div>
                        </div>
                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Dummy 1</label>
                                <input type="text" class="w-100 form-control">
                            </div>
                            <div class="form-group">
                                <label for="">Dummy 2</label>
                                <input type="text" class="w-100 form-control">
                            </div>
                        </div> --}}
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light " submitEditForm id="add_vendor_form">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="import-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Import') }} {{ $vendor_name }}</h4>
                <button type="button" class="close " data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form method="post" enctype="multipart/form-data" id="save_imported_vendors">
                @csrf
                <div class="modal-body">
                    <div class="row">
                    <div class="col-md-12 text-center">
                            <a as="{{url('file-download'.'/sample_vendor.csv')}}" href="{{ route('vendor.export') }}">{{ __("Download Sample file here!") }}</a>
                        </div>
                        <div class="col-md-12">
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <input type="file" accept=".csv" onchange="submitImportForm()" data-plugins="dropify" name="vendor_csv" class="dropify" data-default-file="" required/>
                                    <p class="text-muted text-center mt-2 mb-0">{{ __("Upload") }} {{ $vendor_name }} CSV</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <table class="table table-responsive table-centered table-nowrap table-striped" id="">
                            <p id="p-message" style="color:red;"></p>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('File Name') }}</th>
                                        <th colspan="2">{{ __('Status') }}</th>
                                        <th>{{ __('Link') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="post_list">
                                    @foreach($csvVendors as $csv)
                                    <tr data-row-id="{{$csv->id}}">
                                        <td> {{ $loop->iteration }} </td>
                                        <td> {{ $csv->name }} </td>
                                        @if($csv->status == 1)
                                        <td>{{ __('Pending') }}</td>
                                        <td></td>
                                        @elseif($csv->status == 2)
                                        <td>{{ __('Success') }}</td>
                                        <td></td>
                                        @else
                                        <td>{{ __('Errors') }}</td>
                                        <td class="position-relative text-center alTooltipHover">
                                            <i class="mdi mdi-exclamation-thick"></i>
                                            <ul class="tooltip_error">
                                                <?php $error_csv = json_decode($csv->error); ?>
                                                @foreach($error_csv as $err)
                                                <li>
                                                   {{$err}}
                                                </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        @endif
                                        <td> <a href="{{ $csv->storage_url }}">{{ __('Download') }}</a> </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="show-map-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">

            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Select Location") }}</h4>
                <button type="button" class="close remove-modal-open" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body p-4">

                <div class="row">
                    <form id="task_form" action="#" method="POST" style="width: 100%">
                        <div class="col-md-12">
                            <div id="googleMap" style="height: 500px; min-width: 500px; width:100%"></div>
                            <input type="hidden" name="lat_input" id="lat_map" value="0" />
                            <input type="hidden" name="lng_input" id="lng_map" value="0" />
                            <input type="hidden" name="address_map" id="address_map" value="" />
                            <input type="hidden" name="for" id="map_for" value="" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info waves-effect waves-light remove-modal-open selectMapLocation">Ok</button>
                <!--<button type="Cancel" class="btn btn-info waves-effect waves-light cancelMapLocation">cancel</button>-->
            </div>
        </div>
    </div>
</div>

<div id="dispatcher-login-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Dispatcher Login") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_edit_banner_form" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editCardBox">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light submitDispatcherForm">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

