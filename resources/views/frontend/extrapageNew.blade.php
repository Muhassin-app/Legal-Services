@extends('layouts.store', ['title' => 'Home'])
@section('css')
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<link rel='stylesheet' href='https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css'>
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/mohithg-switchery/mohithg-switchery.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    @media(min-width: 1440px){.content{min-height: calc(100vh - 100px);}.dataTables_scrollBody {height: calc(100vh - 500px);}}

        .pac-container,.pac-container .pac-item{z-index:99999!important}.fc-v-event{border-color:#43bee1;background-color:#43bee1}.dd-list .dd3-content{position:relative}span.inner-div{top:50%;-webkit-transform:translateY(-50%);-moz-transform:translateY(-50%);transform:translateY(-50%)}.button{position:relative;padding:8px 16px;background:#009579;border:none;outline:0;border-radius:50px;cursor:pointer}.button:active{background:#007a63}.button__text{font:bold 20px Quicksand,san-serif;color:#fff;transition:all .2s}.button--loading .button__text{visibility:hidden;opacity:0}.button--loading::after{content:"";position:absolute;width:16px;height:16px;top:0;left:0;right:0;bottom:0;margin:auto;border:4px solid transparent;border-top-color:#fff;border-radius:50%;animation:button-loading-spinner 1s ease infinite}@keyframes button-loading-spinner{from{transform:rotate(0turn)}to{transform:rotate(1turn)}}
    </style>
@endsection
@section('content')
<style>
/* .accordion a{position:relative;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;-ms-flex-direction:column;flex-direction:column;width:100%;padding:1rem 3rem 1rem 1rem;color:#7288a2;font-size:1.15rem;font-weight:400;border-bottom:1px solid #e5e5e5}.accordion a:hover,.accordion a:hover::after{cursor:pointer;color:#ff5353}.accordion a:hover::after{border:1px solid #ff5353}.accordion a.active{color:#ff5353;border-bottom:1px solid #ff5353}.accordion a::after{font-family:Ionicons;content:'\f218';position:absolute;float:right;right:1rem;font-size:1rem;color:#7288a2;padding:5px;width:30px;height:30px;-webkit-border-radius:50%;-moz-border-radius:50%;border-radius:50%;border:1px solid #7288a2;text-align:center}.accordion a.active::after{font-family:Ionicons;content:'\f209';color:#ff5353;border:1px solid #ff5353}.accordion .content{opacity:0;padding:0 1rem;max-height:0;border-bottom:1px solid #e5e5e5;overflow:hidden;clear:both;-webkit-transition:all .2s ease .15s;-o-transition:all .2s ease .15s;transition:all .2s ease .15s}.accordion .content p{font-size:1rem;font-weight:300}.accordion .content.active{opacity:1;padding:1rem;max-height:100%;-webkit-transition:all .35s ease .15s;-o-transition:all .35s ease .15s;transition:all .35s ease .15s} */
   input[data-plugin=switchery]{display:none!important}.time-sloat{position:absolute;right:10px;top:50%;transform:translateY(-50%);color:green;padding:0 7px 3px;font-size:12px;font-weight:900}
/*collapse css here--*/
#faq-accordion .collase1{background:#fff!important;padding:14px 19px!important;margin-bottom:0;font-size:14px;border-color:var(--theme-deafult)}.faq-collapse .open{font-size:13px;line-height:20px}.faq-card{margin-bottom:10px}.faq-header{padding:0 0}.faq-card{background-color:#f4f4f4!important}.faq-collapse{background:#fff}
/*end here-------------*/
#addressInput .pac-container{top:65px!important;left:15px!important}.dd-list .dd3-content img.rounded-circle.mr-1{height:30px}.al_details_vendor,.al_vendor_signup{background-color:#fff;border-radius:15px;width:100%}.al_advanced_details{background-color:rgba(66,190,225,.09);border-radius:2px}body .switchery{height:20px;width:40px;box-shadow:none!important}.nestable_list_1{height:400px;overflow-y:auto}.dd{max-width:100%!important}.nestable_list_1>.dd-list{padding:0}.dd-list .dd3-item{margin:5px 0;list-style:none;width:100%}.dd-list .dd3-content{position:relative;padding:8px 20px 8px 16px;font-weight:400;height:auto;border:none;background:#f3f7f9;color:#6c757d}.dd-list .dd3-content img.rounded-circle.mr-1{height:30px}span.inner-div{float:right}.action-icon{vertical-align:middle}body .switchery>small{width:20px;height:20px}.nestable_list_1 span.inner-div{position:absolute;right:5px;top:50%;-webkit-transform:translateY(-50%);-moz-transform:translateY(-50%);transform:translateY(-50%)}.nestable_list_1::-webkit-scrollbar-track{-webkit-box-shadow:inset 0 0 4px transparent;background-color:#fff;border-radius:5px}.nestable_list_1::-webkit-scrollbar{width:4px;background-color:#fff;border-radius:5px}.nestable_list_1::-webkit-scrollbar-thumb{background-color:#fff;border:2px solid #ddd;border-radius:5px}
</style>
@php
    $user = Auth::user();
@endphp
<section class="section-b-space new-pages mt-5 pt-5 custom-vender-outter">
    <div class="container custom-container mb-4">
        <div class="row">
            <div class="col-12 main-top-heading">
                <h2 class="mb-3">{{$page_detail->translations->first() ? $page_detail->translations->first()->title : $page_detail->primary->title}}</h2>
                <p>{!!$page_detail->translations->first() ? $page_detail->translations->first()->description : $page_detail->primary->description !!}</p>
            </div>
        </div>
        @php
            $getAdditionalPreference = getAdditionalPreference(['is_gst_required_for_vendor_registration', 'is_baking_details_required_for_vendor_registration', 'is_advance_details_required_for_vendor_registration', 'is_vendor_category_required_for_vendor_registration', 'is_seller_module']);
        @endphp
        @if($page_detail->primary->type_of_form == 1)

            <form class="vendor-signup col-md-12" id="vendor_signup_form">
                <!-- al_new_vendor_form -->

                <!-- al_new_vendor_form -->

                <!-- vendor_form other form-->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-success" role="alert" id="success_msg" style="display: none;"></div>
                        <h2 class="mb-0">{{__('Personal Details')}}</h2>
                        {{--<div class="needs-validation vendor-signup ">
                            <div class="al_vendor_signup col-md-12 p-3 mb-3">
                                <input type="hidden" name="user_id" value="{{$user ? $user->id : ''}}">
                                <div class="form-row">
                                    <div class="col-md-3 mb-2" id="full_nameInput">
                                        <label for="fullname">{{__('Full Name')}}</label>
                                        <input type="text" class="form-control" name="full_name" value="{{$user ? $user->name : ''}}" {{$user ? 'disabled' : ''}}>
                                        <span class="invalid-feedback" id="full_name_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-2" id="phone_numberInput">
                                        <label for="validationCustom02">{{__('Phone No.')}}</label>
                                        <input type="tel" class="form-control" name="phone_number" value="{{$user ? '+'.$user->dial_code.''.$user->phone_number : ''}}" id="phone" {{$user ? 'disabled' : ''}}>
                                        <span class="invalid-feedback" id="phone_number_error"><strong></strong></span>
                                        <input type="hidden" id="countryData" name="countryData" value="us">
                                        <input type="hidden" id="dialCode" name="dialCode" value="{{$user ? $user->dial_code : ''}}">

                                    </div>
                                    <div class="col-md-3 mb-2" id="titleInput">
                                        <label for="fullname">{{__('Title')}}</label>
                                        <input type="text" class="form-control" name="title" id="title" value="{{$user ? $user->title : ''}}">
                                        <span class="invalid-feedback" id="title_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-2" id="emailInput">
                                        <label for="email">{{__('Email')}}</label>
                                        <input type="text" class="form-control" name="email" value="{{$user ? $user->email :''}}" {{$user ? 'disabled' : ''}}>
                                        <span class="invalid-feedback" id="email_error"><strong></strong></span>
                                    </div>
                                </div>
                                <div class="form-row">

                                    @if(!$user)
                                        <div class="col-md-3 mb-2" id="passwordInput">
                                            <label for="password">{{__('Password')}}</label>
                                            <input type="password" class="form-control" name="password" value="" required="">
                                            <span class="invalid-feedback" id="password_error"><strong></strong></span>
                                            <span toggle="#password-field" class=" fa fa-eye-slash toggle-password" aria-hidden="true"></span>
                                        </div>
                                        <div class="col-md-3 mb-2" id="confirm_passwordInput">
                                            <label for="confirm_password">{{__('Confirm Password')}}</label>
                                            <input type="password" class="form-control" name="confirm_password" value="" required="">
                                            <span class="invalid-feedback" id="confirm_password_error"><strong></strong></span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>--}}
                        <div class="needs-validation vendor-signup ">
                            <div class="al_vendor_signup col-md-12 p-2 mb-0 pb-0">
                                <input type="hidden" name="user_id" value="{{$user ? $user->id : ''}}">
                                <div class="form-row">
                                    <div class="col-md-3 mb-2" id="full_nameInput">
                                        <label for="fullname">{{__('Full Name')}}</label>
                                        <input type="text" class="form-control" name="full_name" value="{{$user ? $user->name : ''}}" {{$user ? 'disabled' : ''}}>
                                        <span class="invalid-feedback" id="full_name_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-2" id="phone_numberInput">
                                        <label for="validationCustom02">{{__('Phone No.')}}</label>
                                        <input type="tel" class="form-control" name="phone_number" value="{{$user ? '+'.$user->dial_code.''.$user->phone_number : ''}}" id="phone" {{$user ? 'disabled' : ''}}>
                                        <span class="invalid-feedback" id="phone_number_error"><strong></strong></span>
                                        <input type="hidden" id="countryData" name="countryData" value="in">
                                        <input type="hidden" id="dialCode" name="dialCode" value="{{$user ? $user->dial_code : '91'}}">

                                    </div>
                                    <div class="col-md-3 mb-2" id="titleInput">
                                        <label for="fullname">{{__('Title')}}</label>
                                        <input type="text" class="form-control" name="title" id="title" value="{{$user ? $user->title : ''}}" placeholder="{{__('Mr./Miss/Mrs.')}}">
                                        <span class="invalid-feedback" id="title_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-2" id="emailInput">
                                        <label for="email">{{__('Email')}}</label>
                                        <input type="text" class="form-control" name="email" value="{{$user ? $user->email :''}}" {{$user ? 'disabled' : ''}}>
                                        <span class="invalid-feedback" id="email_error"><strong></strong></span>
                                    </div>
                                </div>
                                <div class="form-row">

                                    @if(!$user)
                                        <div class="item_password col-md-3 mb-3 resgiter_password" id="passwordInput" >
                                            <label for="password">{{__('Password')}}</label>
                                            <input type="password" class="form-control" name="password" value="" required="">
                                            <span class="invalid-feedback" id="password_error"><strong></strong></span>
                                            <span toggle="#password-field" class="fa fa-eye-slash toggle-password" aria-hidden="true"></span>
                                        </div>
                                        <div class="item_password col-md-3 mb-3 resgiter_password"  id="confirm_passwordInput">
                                            <label for="confirm_password">{{__('Confirm Password')}}</label>
                                            <input type="password" class="form-control" name="confirm_password" value="" required="">
                                            <span class="invalid-feedback" id="confirm_password_error"><strong></strong></span>
                                            <span toggle="#password-field" class="fa fa-eye-slash toggle-password" aria-hidden="true"></span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if(@$getAdditionalPreference['is_gst_required_for_vendor_registration'] == '1')
                            <h2 class="mb-0">{{getNomenclatureName('GST', true) .' '. __('Details')}}</h2>
                            <div class="al_vendor_signup col-md-12 p-3 mb-3">
                                <div class="form-row">
                                    <div class="col-md-3 mb-2" id="company_nameInput">
                                        <label for="companyname">{{__('Company Name')}}</label>
                                        <input type="text" class="form-control" name="company_name" placeholder="Company Name" value="{{$user ? $user->name : ''}}" {{$user ? 'disabled' : ''}}>
                                        <span class="invalid-feedback" id="company_name_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-2" id="gst_noInput">
                                        <label for="gstNoInput">{{__('GST Number')}}</label>
                                        <input type="text" class="form-control" name="gst_num_Input" placeholder="GST Number" value="{{$user ? $user->title : ''}}">
                                        <span class="invalid-feedback" id="title_error"><strong></strong></span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if(@$getAdditionalPreference['is_baking_details_required_for_vendor_registration'] == '1')
                            <h2 class="mb-0">{{getNomenclatureName('Banking', true) .' '. __('Details')}}</h2>
                            <div class="al_vendor_signup col-md-12 p-3 mb-3">
                                <div class="form-row">
                                    <div class="col-md-3 mb-2" id="account_nameInput">
                                        <label for="accountname">{{getNomenclatureName('Account Name', true)}}</label>
                                        <input type="text" class="form-control" name="account_name" placeholder="{{getNomenclatureName('Account Name', true) .' '. __('Account Name')}}" value="{{$user ? $user->name : ''}}" {{$user ? 'disabled' : ''}}>
                                        <span class="invalid-feedback" id="account_name_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-2" id="bank_nameInput">
                                        <label for="bankname">{{getNomenclatureName('Bank Name', true)}}</label>
                                        <input type="text" class="form-control" name="bank_name" placeholder="{{getNomenclatureName('Bank Name', true) .' '. __('Account Name')}}" value="{{$user ? $user->title : ''}}" placeholder="">
                                        <span class="invalid-feedback" id="title_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-2" id="account_numberInput">
                                        <label for="accountnumber">{{getNomenclatureName('Bank Name', true)}}</label>
                                        <input type="text" class="form-control" name="account_number" placeholder="{{getNomenclatureName('Bank Name', true) .' '. __('Account Number')}}" value="{{$user ? $user->title : ''}}" placeholder="">
                                        <span class="invalid-feedback" id="account_number_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-2" id="ifsc_codeInput">
                                        <label for="ifsccode">{{getNomenclatureName('IFSC Code', true)}}</label>
                                        <input type="text" class="form-control" name="ifsc_code" placeholder="{{getNomenclatureName('IFSC Code', true)}}" value="{{$user ? $user->title : ''}}" placeholder="">
                                        <span class="invalid-feedback" id="ifsc_code_error"><strong></strong></span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <h2 class="mb-0">{{getNomenclatureName('Vendors', true) .' '. __('Details')}}</h2>
                            <div class="al_details_vendor p-2 mb-3">
                                <div class="form-row">
                                    <div class="col-md-4 mb-3">

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
                                    <div class="col-md-8 mb-3">
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
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6 mb-3" id="nameInput">
                                        <label for="validationCustom01">{{getNomenclatureName('Vendors', true) .' '. __('Name')}}</label>
                                        <input type="text" class="form-control" name="name" value="">
                                        <span class="invalid-feedback" id="name_error"><strong></strong></span>
                                    </div>
                                    @if(@$getAdditionalPreference['is_seller_module'] == '1')
                                        <div class="col-md-6 mb-3" id="nameInput">
                                            <label for="vendortype">{{__('Vendor Type')}}</label>
                                            <select name="vendor_type" id="vendor_type" class="form-control">
                                                <option value="0">{{getNomenclatureName('Vendor', true)}}</option>
                                                <option value="1">{{getNomenclatureName('Seller', true)}}</option>
                                            </select>
                                        </div>
                                    @endif
                                    {{-- <div class="col-md-4 mb-3" id="nameInput">
                                        <label for="validationCustom01">{{__('Phone Number')}}</label>
                                        <input type="text" class="form-control" name="phone_no" value="">
                                        <span class="invalid-feedback" id="phone_no_error"><strong></strong></span>
                                    </div> --}}

                                </div>
                                <div class="form-row">
                                    <div class="col-md-6 mb-3">
                                        <label for="validationCustom02">{{__('Description')}}</label>
                                        <textarea class="form-control" name="vendor_description" cols="30" rows="5"></textarea>
                                        <span class="invalid-feedback"></span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="col-md-12 mb-1 p-0">
                                            <label for="validationCustom02">{{__('Website')}}</label>
                                            <input type="text" class="form-control" name="website" value="">
                                            <span class="valid-feedback"></span>
                                        </div>
                                        <div class="col-md-12 p-0" id="addressInput">
                                            <label for="validationCustom01">{{__('Address')}}</label>
                                            <input type="text" class="form-control" name="address" value="" id="vendor_address">
                                            <input type="hidden" class="form-control" name="longitude" value="" id="vendor_longitude">
                                            <input type="hidden" class="form-control" name="latitude" value="" id="vendor_latitude">
                                            {{-- <input type="hidden" class="form-control" name="pincode" value="" id="pincode">
                                            <input type="hidden" class="form-control" name="city" value="" id="city">
                                            <input type="hidden" class="form-control" name="state" value="" id="state">
                                            <input type="hidden" class="form-control" name="country" value="" id="country"> --}}
                                            <span class="invalid-feedback" id="address_error"><strong></strong></span>
                                            <div class="input-group-append">
                                                <button class="btn btn-xs btn-dark waves-effect waves-light showMap" type="button" num="vendor"> <i class="fa fa-map-marker"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-3 mb-3" id="pincodeInput">
                                        <label for="validationCustom01">{{getNomenclatureName('Zip Code', true) }}</label>
                                        <input type="text" class="form-control" id="pincode" name="pincode" value="">
                                        <span class="invalid-feedback" id="pincode_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-3" id="cityInput">
                                        <label for="validationCustom01">{{__('City')}}</label>
                                        <input type="text" class="form-control" id="city" name="city" value="">
                                        <span class="invalid-feedback" id="city_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-3" id="stateInput">
                                        <label for="validationCustom01">{{__('State')}}</label>
                                        <input type="text" class="form-control" id="state" name="state" value="">
                                        <span class="invalid-feedback" id="state_error"><strong></strong></span>
                                    </div>
                                    <div class="col-md-3 mb-3" id="countryInput">
                                        <label for="validationCustom01">{{__('Country')}}</label>
                                        <input type="text" class="form-control" id="country" name="country" value="">
                                        <span class="invalid-feedback" id="country_error"><strong></strong></span>
                                    </div>
                                </div>

                                @if($mod_count > 1)
                                @if($client_preferences)
                                    <div class="form-row">
                                        @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
                                            @php
                                                $clientVendorTypes = $vendor_typ_key.'_check';
                                                $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
                                                $vendor_DynamicTypeName = $vendor_typ_key == "dinein" ? 'Dine-In' : $vendor_typ_value ;
                                            @endphp
                                            @if($client_preferences->$clientVendorTypes == 1 )
                                                <div class="col mb-3">
                                                    <label for="">{{getDynamicTypeName($vendor_DynamicTypeName)}}</label>
                                                    <div class="mt-md-1">
                                                        <input type="checkbox" data-plugin="switchery" checked data-color="#43bee1" id="{{$VendorTypesName}}" name="{{$VendorTypesName}}">
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        {{-- @if($client_preferences->dinein_check == 1)
                                        @php
                                        $Dine_In = getNomenclatureName('Dine-In', true);
                                        $Dine_In = ($Dine_In === 'Dine-In') ? __('Dine-In') : $Dine_In;
                                         @endphp
                                            <div class="col-md-2 col-4 mb-3">
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
                                            <div class="col-md-2 col-4 mb-3">
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
                                                <div class="col-md-2 col-4 mb-3">
                                                    <label for="">{{$Delivery}}</label>
                                                    <div class="mt-md-1">
                                                        <input type="checkbox" data-plugin="switchery" data-color="#43bee1" id="delivery" name="delivery">
                                                    </div>
                                                </div>
                                        @endif --}}
                                    </div>
                                @endif
                                @endif
                                <div class="form-row">
                                    @foreach($vendor_registration_documents as $vendor_registration_document)
                                        @if(isset($vendor_registration_document->primary->slug) && !empty($vendor_registration_document->primary->slug))
                                            @if(strtolower($vendor_registration_document->file_type) == 'selector')
                                            <div class="col-md-6 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
                                                <label for="">{{$vendor_registration_document->primary ? __($vendor_registration_document->primary->name) : ''}}</label>
                                                <select class="form-control {{ (!empty($vendor_registration_document->is_required))?'required':''}}" name="{{$vendor_registration_document->primary->slug}}"  id="input_file_selector_{{$vendor_registration_document->id}}">
                                                    <option value="" >{{__('Please Select '). ($vendor_registration_document->primary ? $vendor_registration_document->primary->name : '') }}</option>
                                                    @foreach ($vendor_registration_document->options as $key =>$value )
                                                        <option value="{{$value->id}}">{{$value->translation? $value->translation->name: ""}}</option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug}}_error"><strong></strong></span>
                                            </div>
                                            @else
                                                <div class="col-md-6 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
                                                    <label for="">{{$vendor_registration_document->primary ? __($vendor_registration_document->primary->name) : ''}}</label>
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
                                    <div class="row">
                                        <!-- al_custom_modal start ADVANCED DETAILS -->
                                        <div class="al_custom_modal col-md-12 pt-2 border-top">
                                                @if(@$getAdditionalPreference['is_vendor_category_required_for_vendor_registration'] == '1' || @$getAdditionalPreference['is_advance_details_required_for_vendor_registration'] == '1')
                                                    <h5 class="mb-2">{{__('ADVANCED DETAILS').' ('.__('Optional').')'}}</h5>
                                                @endif
                                                <div class="row">
                                                    @if(@$getAdditionalPreference['is_advance_details_required_for_vendor_registration'] == '1')
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
                                                                                <label for="title" class="control-label">{{__('Absolute Min Order Value [AMOV]')}}  @include('backend.primary_currency')
                                                                                </label>
                                                                                <input class="form-control" onkeypress="return isNumberKey(event)" name="order_min_amount" type="text" value="{{@$vendor->order_min_amount}}" {{(isset($vendor)) ? (($vendor->status ?? 0) == 1 ? '' : 'disabled') : ''}}>
                                                                            </div>

                                                                    @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                    {{-- @if(isset($user->is_superadmin) && ($user->is_superadmin == 1))
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
                                                    @endif --}}
                                                    @if(@$getAdditionalPreference['is_vendor_category_required_for_vendor_registration'] == '1')
                                                    <div class="col-md-4">

                                                        <div class="col-md-12">
                                                            {!! Form::label('title', getNomenclatureName('Vendors', true) .' '. __('Category') ,['class' => 'control-label']) !!}
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
                                                    @endif
                                                </div>
                                            </div><!-- al_custom_modal end -->
                                    </div>
                                <div class="form-row">
                                    <div class="col-12 checkbox-input" id="check_conditionsCheckbox">
                                        <input type="checkbox" id="check_conditions" name="check_conditions" value="1">
                                        <label for="check_conditions" class="font-weight-bold">{{__('I accept the')}} <a href="{{url('page/terms-conditions')}}" target="_blank" class="text-primary">{{__('Terms And Conditions')}}</a> {{__('and have read the')}} <a href="{{url('page/privacy-policy')}}" target="_blank" class="text-primary"> {{__('Privacy Policy.')}}</a></label>
                                        <span class="invalid-feedback" id="check_conditions_error"><strong></strong></span>
                                    </div>
                                </div>
                                <button class="btn btn-solid mt-3 w-100" dir="ltr" data-style="expand-right" id="register_btn" type="button">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" id="register_btn_loader" style="display:none !important;"></span>
                                    <span class="ladda-label">{{__('Submit')}}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div><!-- vendor_form other form-->

            </form>

        @elseif ($page_detail->primary->type_of_form == 3)
            <div class="accordion" id="faq-accordion">
                @foreach ($page_detail->faqs_details as $key =>$value)
                <div class="card faq-card">
                    <div class="card-header faq-header" id="heading_{{$key}}">
                      <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collase1" type="button" data-toggle="collapse" data-target="#collapse_{{$key}}" aria-expanded="false" aria-controls="collapseOne" style="color: black !important">
                            {{$value->question}}
                        </button>
                      </h2>
                    </div>

                    <div id="collapse_{{$key}}" class="collapse faq-collapse" aria-labelledby="heading_{{$key}}" data-parent="#accordionExample_{{$key}}" style="color: black !important">
                      <div class="card-body open">
                        {{$value->answer}}
                      </div>
                    </div>
                  </div>

                        {{-- <div class="accordion-item">
                            <a>{{$value->question}}</a>
                            <div class="content">
                                <p>{{$value->answer}}</p>
                            </div>
                        </div> --}}



                @endforeach
            </div>
        @endif


    </div>
</section>

<!-- start map model for vendor location -->

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


<!-- end map model for vendor location -->
@php
$Default_latitude = '30.7187';
$Default_longitude = '76.8106';
$theme1 = \App\Models\ClientPreference::where(['id' => 1])->first('theme_admin','Default_latitude','Default_longitude');
if($theme1){
            $Default_latitude = $theme1->Default_latitude ? $theme1->Default_latitude : '30.7187' ;
            $Default_longitude = $theme1->Default_longitude ? $theme1->Default_longitude : '76.8106' ;
        }
@endphp

@endsection
@section('script')
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script src="{{asset('assets/libs/mohithg-switchery/mohithg-switchery.min.js')}}"></script>

<script src="{{asset('front-assets/js/fly-cart.js')}}"></script>
<script src="{{asset('js/phone_number_validation.js')}}"></script>
<script type="text/javascript">
function switchy(){
    $('[data-plugin=\"switchery\"]').each(function (idx, obj) {
        new Switchery($(this)[0], $(this).data());
    });
}
function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

    function vendorAddressInitialize() {
        var addressInput = document.getElementById('vendor_address');
        var autocomplete = new google.maps.places.Autocomplete(addressInput);
        if(is_map_search_perticular_country){
                autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
            }
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            document.getElementById('vendor_longitude').value = place.geometry.location.lng();
            document.getElementById('vendor_latitude').value = place.geometry.location.lat();
            for (let i = 1; i < place.address_components.length; i++) {
                let mapAddress = place.address_components[i];
                if (mapAddress.long_name != '') {
                    let streetAddress = '';
                    if (mapAddress.types[0] == "street_number") {
                        streetAddress += mapAddress.long_name;
                    }
                    if (mapAddress.types[0] == "route") {
                        streetAddress += mapAddress.short_name;
                    }
                    if ($('#street').length > 0) {
                        document.getElementById('street').value = streetAddress;
                    }
                    if (mapAddress.types[0] == "locality") {
                        document.getElementById('city').value = mapAddress.long_name;
                    }
                    if (mapAddress.types[0] == "administrative_area_level_1") {
                        document.getElementById('state').value = mapAddress.long_name;
                    }
                    if (mapAddress.types[0] == "postal_code") {
                        document.getElementById('pincode').value = mapAddress.long_name;
                    } else {
                        document.getElementById('pincode').value = '';
                    }
                    if (mapAddress.types[0] == "country") {
                        document.getElementById('country').value = mapAddress.long_name.toUpperCase();
                        // var country = document.getElementById('country');
                        // for (let i = 0; i < country.options.length; i++) {
                        //     if (country.options[i].text.toUpperCase() == mapAddress.long_name.toUpperCase()) {
                        //         country.value = country.options[i].value;
                        //         break;
                        //     }
                        // }
                    }
                }
            }
        });

        setTimeout(function(){
            $(".pac-container").appendTo('.vendor-signup #addressInput');
        }, 300);
    }

    var text_image = "{{url('images/104647.png')}}";
    $(document).ready(function() {

        switchy();
        @if($page_detail->primary->type_of_form == 1)
            @if($client_preference_detail->business_type != 'taxi')
                vendorOrderTime();
            @endif
            // $("").keypsress(function() {
            //     vendorAddressInitialize();
            // });
            $(document).on('input', '#vendor_address', function(event){
                vendorAddressInitialize();
            });
        @endif

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        function getExtension(filename) {
            return filename.split('.').pop().toLowerCase();
        }
        $("#phone").keypress(function(e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            return true;
        });
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
        $(document).on('change', '[id^=input_file_logo_]', function(event){
            var rel = $(this).data('rel');
            // $('#plus_icon_'+rel).hide();
            readURL(this, '#upload_logo_preview_'+rel);
        });
        $("#input_file_logo").change(function() {
            readURL(this, '#upload_logo_preview');
        });
        $("#input_file_banner").change(function() {
            readURL(this, '#upload_banner_preview');
        });

        var input = document.querySelector("#phone");
        var iti = window.intlTelInput(input, {
            separateDialCode: true,
            hiddenInput: "full_number",
            utilsScript: "{{asset('assets/js/utils.js?1638200991544')}}",
            initialCountry: "{{ Session::get('default_country_code','US') }}",
        });

        phoneNumbervalidation(iti, input);


        $('.iti__country').click(function() {
            var code = $(this).attr('data-country-code');
            $('#countryData').val(code);
            var dial_code = $(this).attr('data-dial-code');
            $('#dialCode').val(dial_code);
        });
        $('#register_btn').click(function() {
            var that = $(this);
            var form = document.getElementById('vendor_signup_form');
            var formData = new FormData(form);
            $(".activeCategory:checkbox:checked").each(function(){
                var category_id = $(this).data('category_id');
                formData.append('selectedCategories[]', category_id);
            });
            if($("#phone").hasClass("is-invalid")){
                $("#phone").focus();
                return false;
            }
            $(this).attr('disabled', true);
            $('#register_btn_loader').show();
            $('.form-control').removeClass("is-invalid");
            $('.invalid-feedback').children("strong").html('');

            $.ajax({
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                url: "{{ route('vendor.register') }}",
                headers: {
                    Accept: "application/json"
                },
                success: function(data) {
                    $('#register_btn_loader').hide();
                    that.attr('disabled', false);
                    if (data.status == 'success') {
                        $('input[type=file]').val('');
                        $("#vendor_signup_form")[0].reset();
                        $('#vendor_signup_form img').attr('src', '');
                        $('html,body').animate({scrollTop: '0px'}, 100);
                        $('#success_msg').html(data.message).show();
                        setTimeout(function() {
                            $('#success_msg').html('').hide();
                        }, 3000);
                    }
                },
                error: function(response) {
                    that.attr('disabled', false);
                    $('html,body').animate({scrollTop: '0px'}, 100);
                    $('#register_btn_loader').hide();
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            $("#" + key + "Input input").addClass("is-invalid");
                            $("#" + key + "_error").children("strong").text(errors[key][0]).show();
                            $("#" + key + "Input span.invalid-feedback").show();

                            $("#" + key + "Checkbox input").addClass("is-invalid");
                            $("#" + key + "_error").children("strong").text(errors[key][0]).show();
                            $("#" + key + "Checkbox span.invalid-feedback").show();
                        });
                    } else {
                        $(".show_all_error.invalid-feedback").show();
                        $(".show_all_error.invalid-feedback").text('Something went wrong, Please try Again.');
                    }
                }
            });
        });
    @if($page_detail->primary->type_of_form == 1)
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
    @endif
    });


    ///  start vendor register page map icon

    var Default_latitude  =  {{ $Default_latitude }};
    var Default_longitude =  {{ $Default_longitude }};

    $(document).on('click', '.showMap', function() {
        var no = $(this).attr('num');

        var lats = document.getElementById(no + '_latitude').value;
        var lngs = document.getElementById(no + '_longitude').value;
        var address = document.getElementById(no+'_address').value;

        var addressLatitude = document.getElementById('address-latitude');
        var addressLongitude = document.getElementById('address-longitude');

        if(addressLatitude != null && addressLongitude != null){
            var lats = addressLatitude.value;
            var lngs = addressLongitude.value;
        }

        document.getElementById('map_for').value = no;

        if (lats == null || lats == '0' || lats =='') {
            lats = Default_latitude;
        }
        if (lngs == null || lngs == '0'  || lngs == '') {
            lngs = Default_longitude ;
        }
        if(address==null){
            address= '';
        }

        var myLatlng = new google.maps.LatLng(lats, lngs);
        var mapProp = {
            center: myLatlng,
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.ROADMAP

        };
        document.getElementById('lat_map').value= lats;
        document.getElementById('lng_map').value= lngs ;
        document.getElementById('address_map').value= address ;
        var infowindow = new google.maps.InfoWindow();
        var geocoder = new google.maps.Geocoder();
        var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            title: 'Hello World!',
            draggable: true
        });
        document.getElementById('lat_map').value = lats;
        document.getElementById('lng_map').value = lngs;

        google.maps.event.addListener(marker, 'dragend', function() {
            geocoder.geocode({
            'latLng': marker.getPosition()
            }, function(results, status) {

            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                        document.getElementById('lat_map').value = marker.getPosition().lat();
                        document.getElementById('lng_map').value = marker.getPosition().lng();
                        document.getElementById('address_map').value= results[0].formatted_address;

                    infowindow.setContent(results[0].formatted_address);

                    infowindow.open(map, marker);
                }
            }
            });
        });

        $('#add-customer-modal').addClass('fadeIn');
        $('#show-map-modal').modal({
            //backdrop: 'static',
            keyboard: false
        });

    });

    $(document).on('click', '.selectMapLocation', function() {

    var mapLat = document.getElementById('lat_map').value;
    var mapLlng = document.getElementById('lng_map').value;
    var mapFor = document.getElementById('map_for').value;
    var address = document.getElementById('address_map').value;

    document.getElementById(mapFor + '_latitude').value = mapLat;
    document.getElementById(mapFor + '_longitude').value = mapLlng;
    document.getElementById(mapFor + '_address').value = address;

    $('#show-map-modal').modal('hide');
   });

    //// end vendor register page map icon
</script>

<script>
    $(document).ready(function(){
        $('.toggle-password').on('click',function(){
            if($(this).prev().prev().attr('type')=='password'){
                $(this).prev().prev().attr('type','text')
            }else{
                $(this).prev().prev().attr('type','password')
            }
        })
    })
</script>
@endsection
