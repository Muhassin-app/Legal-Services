@php
    $getAdditionalPreference = getAdditionalPreference(['is_gst_required_for_vendor_registration', 'is_baking_details_required_for_vendor_registration', 'is_advance_details_required_for_vendor_registration', 'is_vendor_category_required_for_vendor_registration', 'is_seller_module']);
@endphp
<div class="row al">
    <div class="col-md-12">
        <div class="row mb-2">
            <div class="col-md-3">
                <label>{{ __('Upload Logo') }}</label>
                <input type="file" accept="image/*" data-plugins="dropify" name="logo" class="dropify" data-default-file="{{ $vendor->logo['proxy_url'].'90/90'.$vendor->logo['image_path'] }}" />
                <label class="logo-size text-right w-100">{{ __("Logo Size") }} 170x96</label>
            </div>
            <div class="col-md-6">
                <label>{{ __("Upload banner image") }}</label>
                <input type="file" accept="image/*" data-plugins="dropify" data-default-file="{{$vendor->banner['proxy_url'] . '700/200' . $vendor->banner['image_path']}}" name="banner" class="dropify" />
                <label class="logo-size text-right w-100">{{ __('Image Size') }} 830x200</label>
            </div>
             {{--@if(isset($vendor_docs))
                @if($vendor_docs->count() > 0)
                    <div class="col-md-3">
                        <label>{{ __('Upload Document') }}</label>
                        @foreach($vendor_docs as $k => $vendor_doc)
                        <div class="d-flex align-items-center justify-content-between">
                            <label>{{$vendor_doc->vendor_registration_document->primary->name}}</label>
                            <a class="d-block mb-1 document-btn" target="_blank" href="{{$vendor_doc->image_file['storage_url']}}">
                                <i class="fa fa-eye float-right"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif
            @endif--}}
        </div>
        {!! Form::hidden('vendor_id', $vendor->id, ['class'=>'form-control']) !!}

        <div class="row">
            <div class="col-md-6">
                <div class="form-group" id="nameInput">
                    {!! Form::label('title', __('Name'),['class' => 'control-label']) !!}
                    {!! Form::text('name', $vendor->name, ['class'=>'form-control']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group" id="emailInput">
                    <label for="">{{ __('Email') }}</label>
                    {!! Form::text('email', $vendor->email, ['class'=>'form-control']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group" id="phone_noInput">
                    @php
                     if($vendor){
                        if($vendor->dial_code){
                            $phn = '+'.$vendor->dial_code.$vendor->phone_no;
                        }
                        else if($vendor->phone_no){
                            $phn = $vendor->phone_no;
                        }
                        else{
                            $phn = ' ';
                        }
                     }
                     else{
                         $phn = ' ';
                     }
                    @endphp
                    <label class="w-100" for="">{{ __('Phone Number') }}</label>
                    <!-- {!! Form::text('phone_no', $vendor->phone_no, ['class'=>'form-control']) !!} -->
                    <input type="tel" class="form-control phone" id="vendor_phone_number" placeholder={{ __("Phone Number") }} name="phone_no" value="{{ $phn }}">
                    <input type="hidden" id="vendorCountryCode" name="vendor_country" value="{{ old('vendor_country') ? old('vendor_country') : 'us'}}">
                    <input type="hidden" id="vendorDialCode" name="vendor_dial_code" value="{{ old('vendor_dial_code') ? old('vendor_dial_code') : Session::get('default_country_phonecode',1) }}">
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="">{{ __('Website') }}</label>
                    {!! Form::text('website', $vendor->website, ['class'=>'form-control']) !!}
                </div>
            </div>


        </div>
        <div class="row">

        </div>
        <div class="row mb-2" id="edit">
            <div class="col-md-12">
                <div class="form-group mb-3" id="addressInput">
                    {!! Form::label('title', __('Address'),['class' => 'control-label']) !!}
                    <div class="input-group">
                        <input type="text" name="address" id="edit-address" placeholder="Delhi, India" class="form-control" value="{{$vendor->address}}" onkeyup="checkAddressString(this,'edit')">
                        <div class="input-group-append">
                            <button class="btn btn-xs btn-dark waves-effect waves-light showMap" type="button" num="edit"> <i class="mdi mdi-map-marker-radius"></i></button>
                        </div>
                    </div>
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="row mb-2" >
            <div class="col-md-4">
                <div class="form-group mb-3" >
                    {!! Form::label('title', __('Pincode'),['class' => 'control-label']) !!}
                    <input type="text" name="pincode" id="pincode" placeholder="Pincode" class="form-control" value="{{@$vendor->pincode}}">
                    @if($errors->has('Pincode'))
                    <span class="text-danger" role="alert">
                        <strong>{{ $errors->first('Pincode') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    {!! Form::label('title', __('City'),['class' => 'control-label']) !!}
                    <input type="text" name="city" id="city" placeholder="City" class="form-control" value="{{$vendor->city}}">
                    @if($errors->has('city'))
                    <span class="text-danger" role="alert">
                        <strong>{{ $errors->first('city') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    {!! Form::label('title', __('State'),['class' => 'control-label']) !!}
                    <input type="text" name="state" id="state" placeholder="State" class="form-control" value="{{$vendor->state}}">
                    <input type="hidden" name="state_code" id="state_code" placeholder="" class="form-control" value="{{$vendor->state_code}}">
                    @if($errors->has('state'))
                    <span class="text-danger" role="alert">
                        <strong>{{ $errors->first('state') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    {!! Form::label('title', __('Country'),['class' => 'control-label']) !!}
                    <input type="text" name="country" id="country" placeholder="Country" class="form-control" value="{{$vendor->country}}">
                    <input type="hidden" name="country" id="country_code" placeholder="" class="form-control" value="{{$vendor->country_code}}">
                    @if($errors->has('country'))
                    <span class="text-danger" role="alert">
                        <strong>{{ $errors->first('country') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3" id="latitudeInput">
                    {!! Form::label('title', __('Latitude'),['class' => 'control-label']) !!}
                    <input type="text" name="latitude" id="edit_latitude" placeholder="24.9876755" class="form-control" value="{{$vendor->latitude}}">
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
                    <input type="text" name="longitude" id="edit_longitude" placeholder="11.9871371723" class="form-control" value="{{$vendor->longitude}}">
                    @if($errors->has('longitude'))
                    <span class="text-danger" role="alert">
                        <strong>{{ $errors->first('longitude') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            @if(@$getAdditionalPreference['is_seller_module'] == '1')
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vendortype">{{__('Vendor Type')}}</label>
                        <select name="vendor_type" id="vendor_type" class="form-control">
                            <option value="1" @if ($vendor->is_seller == 1) {{'selected="selected"'}} @endif >Seller</option>
                            <option value="0" @if ($vendor->is_seller == 0) {{'selected="selected"'}} @endif>Vendor</option>
                        </select>
                    </div>
                </div>
            @endif

            @if(@$getAdditionalPreference['is_gst_required_for_vendor_registration'] == '1')
                <div class="row p-2">
                    <div class="col-md-12"><h5 class="mb-2">{{__('GST DETAILS')}}</h5></div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="companyname">{{__('Company Name')}}</label>
                            <input type="text" class="form-control" name="company_name" placeholder="Company Name" value="{{$vendor->VendorAdditionalInfo->company_name??''}}">
                            <span class="invalid-feedback" id="company_name_error"><strong></strong></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="gstNoInput">{{__('GST Number')}}</label>
                            <input type="text" class="form-control" name="gst_num_Input" placeholder="GST Number" value="{{$vendor->VendorAdditionalInfo->gst_number??''}}">
                            <span class="invalid-feedback" id="title_error"><strong></strong></span>
                        </div>
                    </div>
                </div>
            @endif

            @if(@$getAdditionalPreference['is_baking_details_required_for_vendor_registration'] == '1')
               
                <div class="row p-2">
                    <div class="col-md-12"> <h5 class="mb-2">{{__('Banking DETAILS')}}</h5></div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="accountname">{{__('Account Name')}}</label>
                            <input type="text" class="form-control" name="account_name" placeholder="Account Name" value="{{$vendor->VendorAdditionalInfo->account_name??''}}">
                            <span class="invalid-feedback" id="account_name_error"><strong></strong></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bankname">{{__('Bank Name')}}</label>
                            <input type="text" class="form-control" name="bank_name" placeholder="Bank Name" value="{{$vendor->VendorAdditionalInfo->bank_name??''}}" placeholder="">
                            <span class="invalid-feedback" id="title_error"><strong></strong></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="accountnumber">{{__('Account Number')}}</label>
                            <input type="text" class="form-control" name="account_number" placeholder="Account Number" value="{{$vendor->VendorAdditionalInfo->account_number??''}}" placeholder="">
                            <span class="invalid-feedback" id="account_number_error"><strong></strong></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ifsccode">{{getNomenclatureName('IFSC Code', true)}}</label>
                            <input type="text" class="form-control" name="ifsc_code" placeholder="{{getNomenclatureName('IFSC Code', true)}}" value="{{$vendor->VendorAdditionalInfo->ifsc_code??''}}" placeholder="">
                            <span class="invalid-feedback" id="ifsc_code_error"><strong></strong></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group" id="descInput">
                    {!! Form::label('title', __('Description'),['class' => 'control-label']) !!}
                    {!! Form::textarea('desc', $vendor->desc, ['class' => 'form-control', 'rows' => '3']) !!}
                </div>
                <div class="form-group" id="descInput">
                    {!! Form::label('title', __('Short Description'),['class' => 'control-label']) !!}
                    {!! Form::textarea('short_desc', $vendor->short_desc, ['class' => 'form-control', 'rows' => '2']) !!}
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
                @php
                $typeArray = getCategoryTypes();
                @endphp
                   @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
                        @php
                            $clientVendorTypes = $vendor_typ_key.'_check';
                            $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                        @endphp
                        @if(($client_preferences->$clientVendorTypes == 1) && (in_array($vendor_typ_key, $typeArray)) )
                        <div class="col-md-12">
                            <div class="form-group d-flex justify-content-between">
                                <label for="{{$VendorTypesName}}" class="mr-3 mb-0">{{getDynamicTypeName($vendor_typ_value)}}</label>
                                <input type="checkbox" data-plugin="switchery" name="{{$VendorTypesName}}" id="{{$VendorTypesName}}" class="form-control editSwitchery {{$VendorTypesName}}" data-color="#43bee1" @if($vendor->$VendorTypesName == 1) checked @endif>
                                
                            </div>
                        </div>
                        @endif
                    @endforeach
                    <!-- <div class="col-md-4">
                        <div class="form-group" @if($client_preferences->dinein_check == 0) style="display: none;" @endif >
                            {!! Form::label('title', getNomenclatureName('Dine-In', true) ,['class' => 'control-label']) !!}
                            <div>
                                <input type="checkbox" data-plugin="switchery" name="dine_in" class="form-control dine_in" data-color="#43bee1" @if($vendor->dine_in == 1) checked @endif>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" @if($client_preferences->takeaway_check == 0) style="display: none;" @endif >
                            {!! Form::label('title', getNomenclatureName('Takeaway', true),['class' => 'control-label']) !!}
                            <div>
                                <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control takeaway" data-color="#43bee1" @if($vendor->takeaway == 1) checked @endif>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" @if($client_preferences->delivery_check == 0) style="display: none;" @endif >
                            {!! Form::label('title', getNomenclatureName('Delivery', true) ,['class' => 'control-label']) !!}
                            <div>
                                <input type="checkbox" data-plugin="switchery" name="delivery" class="form-control delivery" data-color="#43bee1" @if($vendor->delivery == 1) checked @endif>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
        <div class="row">
            @if(!empty($vendor_registration_documents) && count($vendor_registration_documents) > 0)
            @foreach($vendor_registration_documents as $vendor_registration_document)
            @php
                $field_value = "";
                if(!empty($vendor_docs) && count($vendor_docs) > 0){
                    foreach($vendor_docs as $key => $vendor_doc){
                        if($vendor_registration_document->id == $vendor_doc->vendor_registration_document_id){
                            if($vendor_registration_document->file_type == 'Text' || $vendor_registration_document->file_type == 'selector' ){
                                $field_value = $vendor_doc->file_name;
                            } else {
                                $field_value =  isset($vendor_doc->image_file['storage_url'])?$vendor_doc->image_file['storage_url']:'';
                            }
                        }
                    }
                }
            @endphp
            @if(strtolower($vendor_registration_document->file_type) == 'selector')
                    <div class="col-md-6 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
                        <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}</label>
                        <select class="form-control {{ (!empty($vendor_registration_document->is_required))?'required':''}}" name="{{$vendor_registration_document->primary->slug}}"  id="input_file_selector_{{$vendor_registration_document->id}}">
                            <option value="" >{{__('Please Select '). ($vendor_registration_document->primary ? $vendor_registration_document->primary->name : '') }}</option>
                            @foreach ($vendor_registration_document->options as $key =>$value )
                                <option value="{{$value->id}}" {{ ($value->id == $field_value) ? 'selected':'' }} >{{$value->translation? $value->translation->name: ""}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug}}_error"><strong></strong></span>
                    </div>
            @else
            <div class="col-md-6" >
                <div class="form-group" id="{{$vendor_registration_document->primary->slug??''}}Input">
                    @if(strtolower($vendor_registration_document->file_type) == 'text')
                        <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}</label>
                        <input id="input_file_logo_{{$vendor_registration_document->id}}" type="text" name="{{$vendor_registration_document->primary->slug??''}}" class="form-control" value="{{ $field_value }}">
                    @else
                        @if(strtolower($vendor_registration_document->file_type) == 'image')
                        <label class="d-flex align-items-center justify-content-between" for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}<a href="{{ $field_value }}" target="__blank"><i class="fa fa-eye" aria-hidden="true"></i></a></label>
                        <input type="file" accept="image/*" data-plugins="dropify" name="{{$vendor_registration_document->primary->slug??''}}" class="dropify" data-default-file="{{ $field_value }}" />
                        @else
                        <label class="d-flex align-items-center justify-content-between" for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}<a href="{{ $field_value }}" target="__blank"><i class="fa fa-eye" aria-hidden="true"></i></a></label>
                        <input type="file" accept=".pdf" data-plugins="dropify" name="{{$vendor_registration_document->primary->slug??''}}" class="dropify" data-default-file="{{ $field_value }}" />
                        @endif
                    @endif
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            @endif
            @endforeach
            @endif
        </div>
    </div>
</div>
