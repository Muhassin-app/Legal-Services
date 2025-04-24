<div class="row al_new_vendor_form d-none"  >

    <div class="al_count_tabs_new_design col-md-10 offset-md-1 ">

        <ul class="nav nav-tabs d-flex justify-content-around" id="vendorTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link al_vendor_details active" id="vendor_details-tab" data-toggle="tab" href="#vendor_details" role="tab" aria-controls="home" aria-selected="true">
                    <span class="al_vendor_box"><img src=" {{asset('images/vendor_details.png')}}" alt=""></span>
                    {{__('Vendor Details')}}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link al_vendor_address" id="vendor_address-tab" data-toggle="tab" href="#vendor_address" role="tab" aria-controls="profile" aria-selected="false">
                    <span class="al_vendor_box"><img src="{{asset('images/vendor_address.png')}} " alt=""></span>
                    {{__('Restaurant Details')}}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link al_vendor_advanced" id="vendor_advanced-tab" data-toggle="tab" href="#vendor_advanced" role="tab" aria-controls="contact" aria-selected="false">
                    <span class="al_vendor_box"><img src="{{asset('images/vendor_advanced.png')}}" alt=""></span>
                    {{__('Advanced Details')}}
                </a>
            </li>
        </ul>

        <div class="tab-content p-md-3 p-2" id="myTabContent">

            <div class="tab-pane fade show active" id="vendor_details" role="tabpanel" aria-labelledby="vendor_details-tab">
                <p>{{__('Personal Details')}}</p>
                <div class="needs-validation vendor-signup ">
                    <div class="al_vendor_signup col-md-12 p-3 mb-3">
                        <input type="hidden" name="user_id" value="{{$user ? $user->id : ''}}">
                        <div class="form row">
                            <div class="col-md-6 mb-2" id="full_nameInput">
                                <label for="fullname">{{__('Full Name')}}</label>
                                <input type="text" class="form-control" name="full_name" value="{{$user ? $user->name : ''}}" {{$user ? 'disabled' : ''}}>
                                <span class="invalid-feedback" id="full_name_error"><strong></strong></span>
                            </div>
                            <div class="col-md-6 mb-2" id="phone_numberInput">
                                <label for="validationCustom02">{{__('Phone No.')}}</label>
                                <input type="tel" class="form-control" name="phone_number" value="{{$user ? '+'.$user->dial_code.''.$user->phone_number : ''}}" id="phone" {{$user ? 'disabled' : ''}}>
                                <span class="invalid-feedback" id="phone_number_error"><strong></strong></span>
                                <input type="hidden" id="countryData" name="countryData" value="us">
                                <input type="hidden" id="dialCode" name="dialCode" value="{{$user ? $user->dial_code : ''}}">
                            </div>
                            <div class="col-md-6 mb-2" id="titleInput">
                                <label for="fullname">{{__('Title')}}</label>
                                <input type="text" class="form-control" name="title" value="{{$user ? $user->title : ''}}">
                                <span class="invalid-feedback" id="title_error"><strong></strong></span>
                            </div>
                            <div class="col-md-6 mb-2" id="emailInput">
                                <label for="email">{{__('Email')}}</label>
                                <input type="text" class="form-control" name="email" value="{{$user ? $user->email :''}}" {{$user ? 'disabled' : ''}}>
                                <span class="invalid-feedback" id="email_error"><strong></strong></span>
                            </div>
                        </div>
                        <div class="form-row">

                            @if(!$user)
                                <div class="col-md-6 mb-2" id="passwordInput">
                                    <label for="password">{{__('Password')}}</label>
                                    <input type="password" class="form-control" name="password" value="" required="">
                                    <span class="invalid-feedback" id="password_error"><strong></strong></span>
                                </div>
                                <div class="col-md-6 mb-2" id="confirm_passwordInput">
                                    <label for="confirm_password">{{__('Confirm Password')}}</label>
                                    <input type="password" class="form-control" name="confirm_password" value="" required="">
                                    <span class="invalid-feedback" id="confirm_password_error"><strong></strong></span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

            </div>

            <div class="tab-pane fade" id="vendor_address" role="tabpanel" aria-labelledby="vendor_address-tab">
                <p class="">{{getNomenclatureName('Vendors', true) .' '. __('Details')}}</p>
                <div class="al_details_vendor p-3 mb-3">
                    <div class="form row">
                        <div class="col-md-2 mb-3">

                            <label for="">{{__('Upload Logo')}}</label>
                            <div class="file file--upload">
                                <label for="input_file_logo">
                                    <span class="update_pic">
                                        <img src="" id="upload_logo_preview">
                                    </span>
                                    <span class="plus_icon">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                </label>
                                <input id="input_file_logo" type="file" name="upload_logo" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="">{{__('Upload Banner')}}</label>
                            <div class="file file--upload">
                                <label for="input_file_banner">
                                    <span class="update_pic">
                                        <img src="" id="upload_banner_preview">
                                    </span>
                                    <span class="plus_icon"><i class="fa fa-plus"></i></span>
                                </label>
                                <input id="input_file_banner" type="file" name="upload_banner" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-4 ">
                            <div class="form row">
                                <div class="col-md-12 mb-2" id="nameInput">
                                    <label for="validationCustom01">{{getNomenclatureName('Vendors', true) .' '. __('Name')}}</label>
                                    <input type="text" class="form-control" name="name" value="">
                                    <span class="invalid-feedback" id="name_error"><strong></strong></span>
                                </div>
                                <div class="col-md-12">
                                    <label for="validationCustom02">{{__('Website')}}</label>
                                    <input type="text" class="form-control" name="website" value="">
                                    <span class="valid-feedback"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form row d-none">
                        <div class="col-md-6 mb-3" id="nameInput">
                            <label for="validationCustom01">{{getNomenclatureName('Vendors', true) .' '. __('Name')}}</label>
                            <input type="text" class="form-control" name="name" value="">
                            <span class="invalid-feedback" id="name_error"><strong></strong></span>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label for="validationCustom02">{{__('Website')}}</label>
                            <input type="text" class="form-control" name="website" value="">
                            <span class="valid-feedback"></span>
                        </div>

                        {{-- <div class="col-md-4 mb-3" id="nameInput">
                            <label for="validationCustom01">{{__('Email')}}</label>
                            <input type="text" class="form-control" name="email" value="">
                            <span class="invalid-feedback" id="email_error"><strong></strong></span>
                        </div>
                        <div class="col-md-4 mb-3" id="nameInput">
                            <label for="validationCustom01">{{__('Phone Number')}}</label>
                            <input type="text" class="form-control" name="phone_no" value="">
                            <span class="invalid-feedback" id="phone_no_error"><strong></strong></span>
                        </div> --}}

                    </div>
                    <div class="form row">
                        <div class="col-md-6 mb-3">
                            <label for="validationCustom02">{{__('Description')}}</label>
                            <textarea class="form-control" name="vendor_description" cols="30" rows="5"></textarea>
                            <span class="invalid-feedback"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            @if($mod_count > 1)
                            @if($client_preferences)
                            <div class="form row">
                                @if($client_preferences->dinein_check == 1)
                                @php
                                $Dine_In = getNomenclatureName('Dine-In', true);
                                $Dine_In = ($Dine_In === 'Dine-In') ? __('Dine-In') : $Dine_In;
                                @endphp
                                    <div class="col-md-4 mb-3">
                                        <label for="">{{$Dine_In}}</label>
                                        <div class="mt-md-1">
                                            <input type="checkbox" data-plugin="switchery" data-color="#43bee1" id="dine-in" name="dine_in">
                                        </div>
                                    </div>
                                @endif
                                @if($client_preferences->takeaway_check == 1)
                                @php
                                $Takeaway = getNomenclatureName('Takeaway', true);
                                $Takeaway = ($Takeaway === 'Takeaway') ? __('Takeaway') : $Takeaway;
                                @endphp
                                    <div class="col-md-4 mb-3">
                                        <label for="">{{$Takeaway}}</label>
                                        <div class="mt-md-1">
                                        <input type="checkbox" data-plugin="switchery" data-color="#43bee1" id="takeaway" name="takeaway">
                                        </div>
                                    </div>
                                @endif
                                @if($client_preferences->delivery_check == 1)
                                @php
                                $Delivery = getNomenclatureName('Delivery', true);
                                $Delivery = ($Delivery === 'Delivery') ? __('Delivery') : $Delivery;
                                @endphp
                                    <div class="col-md-4 mb-3">
                                        <label for="">{{$Delivery}}</label>
                                        <div class="mt-md-1">
                                            <input type="checkbox" data-plugin="switchery" data-color="#43bee1" id="delivery" name="delivery">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @endif

                        </div>
                    </div>
                    <div class="form row">
                        <div class="col-md-4 " id="addressInput">
                            <label for="validationCustom01">{{__('Address')}}</label>
                            <input type="text" class="form-control" name="address" value="" id="vendor_address">
                            <input type="hidden" class="form-control" name="longitude" value="" id="vendor_longitude">
                            <input type="hidden" class="form-control" name="latitude" value="" id="vendor_latitude">
                            {{-- <input type="hidden" class="form-control" name="pincode" value="" id="pincode">
                            <input type="hidden" class="form-control" name="city" value="" id="city">
                            <input type="hidden" class="form-control" name="state" value="" id="state">
                            <input type="hidden" class="form-control" name="country" value="" id="country"> --}}
                            <span class="invalid-feedback" id="address_error"><strong></strong></span>
                        </div>
                        <div class="col-md-2 mb-3" >
                            <label for="validationCustom01">{{getNomenclatureName('Zip Code', true) }}</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" value="">
                            <span class="invalid-feedback" id="pincode_error"><strong></strong></span>
                        </div>
                        <div class="col-md-2 mb-3" >
                            <label for="validationCustom01">{{__('City')}}</label>
                            <input type="text" class="form-control" id="city" name="city" value="">
                            <span class="invalid-feedback" id="city_error"><strong></strong></span>
                        </div>
                        <div class="col-md-2 mb-3" >
                            <label for="validationCustom01">{{__('State')}}</label>
                            <input type="text" class="form-control" id="state" name="state" value="">
                            <span class="invalid-feedback" id="state_error"><strong></strong></span>
                        </div>
                        <div class="col-md-2 mb-3" >
                            <label for="validationCustom01">{{__('Country')}}</label>
                            <input type="text" class="form-control" id="country" name="country" value="">
                            <span class="invalid-feedback" id="country_error"><strong></strong></span>
                        </div>
                    </div>


                    <div class="form-row">
                        @foreach($vendor_registration_documents as $vendor_registration_document)
                            @if(isset($vendor_registration_document->primary->slug) && !empty($vendor_registration_document->primary->slug))
                                @if(strtolower($vendor_registration_document->file_type) == 'selector')
                                <div class="col-md-4 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
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
                                    <div class="col-md-4 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
                                        <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}</label>
                                        @if(strtolower($vendor_registration_document->file_type) == 'text')
                                        <input id="input_file_logo_{{$vendor_registration_document->id}}" type="text" name="{{$vendor_registration_document->primary->slug}}" class="form-control {{ (!empty($vendor_registration_document->is_required))?'required':''}}">
                                        <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug??''}}_error"><strong></strong></span>
                                        @else
                                        <div class="file file--upload">
                                            <label for="input_file_logo_{{$vendor_registration_document->id}}">
                                                <span class="update_pic pdf-icon">
                                                    <img src=""  id="upload_logo_preview_{{$vendor_registration_document->id}}">
                                                </span>
                                                <span class="plus_icon" id="plus_icon_{{$vendor_registration_document->id}}">
                                                    <i class="fa fa-plus"></i>
                                                </span>
                                            </label>
                                            @if(strtolower($vendor_registration_document->file_type) == 'image')
                                                <input class="{{ (!empty($vendor_registration_document->is_required))?'required':''}}" id="input_file_logo_{{$vendor_registration_document->id}}" type="file" name="{{$vendor_registration_document->primary->slug}}" accept="image/*" data-rel="{{$vendor_registration_document->id}}">
                                            @else
                                                <input class="{{ (!empty($vendor_registration_document->is_required))?'required':''}}" id="input_file_logo_{{$vendor_registration_document->id}}" type="file" name="{{$vendor_registration_document->primary->slug}}" accept=".pdf" data-rel="{{$vendor_registration_document->id}}">
                                            @endif
                                            <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug}}_error"><strong></strong></span>
                                        </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>

                </div>
            </div>

            <div class="tab-pane fade" id="vendor_advanced" role="tabpanel" aria-labelledby="vendor_advanced-tab">
                <div class="row">
                    <p class="">{{__('Advanced Details')}}</p>
                    <!-- al_vendor_advanced start ADVANCED DETAILS -->
                    <div class="al_vendor_advancedtab col-md-12 p-3">


                        <div class="row">
                            <div class="col-md-4">
                                <div class="al_advanced_details">
                                    <p class="al_custom_title mb-2">{{__('Configuration')}}</p>
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
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {!! Form::label('title', __('24*7 Availability'),['class' => 'control-label']) !!}
                                                    <div class="mt-md-1">
                                                        <input type="checkbox" data-plugin="switchery" data-color="#43bee1" name="show_slot" class="form-control"  @if(@$vendor->show_slot == 1) checked @endif {{($vendor ?? false) ? ($vendor->status == 1 ? '' : 'disabled') : ''}}>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">

                                                    {!! Form::label('title', __('Auto Accept Order'),['class' => 'control-label']) !!}
                                                    <div class="mt-md-1">
                                                        <input type="checkbox" data-plugin="switchery" name="auto_accept_order" class="form-control" data-color="#43bee1" @if(@$vendor->auto_accept_order == 1) checked @endif {{($vendor ?? false) ? ($vendor->status == 1 ? '' : 'disabled') : ''}}>
                                                    </div>

                                                </div>
                                            </div>
                                            @endif
                                            @if(isset($user->is_superadmin) && ($user->is_superadmin == 1))
                                            <div class="col-md-4">
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
                            @if(isset($user->is_superadmin) && ($user->is_superadmin == 1))
                                <div class="col-md-4">
                                    <div class="al_advanced_details">
                                        <p class="al_custom_title mb-2"><span class="">{{ __("Commission") }}</span> ({{ __("Visible For Admin") }})</p>

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

                                <div class="col-md-12">
                                    <p class="al_custom_title">{!! Form::label('title', __('Vendor Category'),['class' => 'control-label']) !!}</p>
                                    <div class="custom-dd dd nestable_list_1" id="nestable_list_1">
                                        <ol class="dd-list">
                                            @forelse($builds as $build)
                                            @if($build['translation_one'])
                                            <li class="dd-item dd3-item" data-category_id="{{$build['id']}}">
                                                <div class="dd3-content">

                                                        <img class="rounded-circle mr-1" src="{{$build['icon']['proxy_url']}}30/30{{$build['icon']['image_path']}}">
                                                        {{$build['translation_one']['name']}}

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


                        <div class="form-row mt-2">
                            <div class="col-12 checkbox-input">
                                <input type="checkbox" id="html" name="check_conditions" value="1">
                                <label for="html">{{__('I accept the')}} <a href="{{url('page/terms-conditions')}}" target="_blank">{{__('Terms And Conditions')}}</a> {{__('and have read the')}} <a href="{{url('page/privacy-policy')}}" target="_blank"> {{__('Privacy Policy.')}}</a></label>
                                <span class="invalid-feedback" id="check_conditions_error"><strong></strong></span>
                            </div>
                        </div>
                        <button class="btn btn-solid mt-3 w-100" dir="ltr" data-style="expand-right" id="register_btn" type="button">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" id="register_btn_loader" style="display:none !important;"></span>
                            <span class="ladda-label">{{__('Submit')}}</span>
                        </button>
                    </div><!-- al_vendor_advanced end -->
            </div>
        </div>
    </div>





</div>