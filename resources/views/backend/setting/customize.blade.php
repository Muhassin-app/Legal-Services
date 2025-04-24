@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Customize'])
@section('css')
<link href="https://itsjavi.com/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css" rel="stylesheet" type="text/css" />
<style>
.select2-multiple {visibility: hidden !important;}
</style>
@endsection

@section('content')
<div class="container-fluid" id="alCustomizePage">
    {{--<div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Customize") }}</h4>
            </div>
        </div>
    </div>--}}
    @if (\Session::has('success'))
    <div class="row mb-2 mt-2">
        <div class="col-sm-12">
            <div class="text-sm-left">

                <div class="alert alert-success">
                    <span>{!! \Session::get('success') !!}</span>
                </div>

            </div>
        </div>
    </div>
    @endif
    @if (\Session::has('error'))
    <div class="row mb-2 mt-2">
        <div class="col-sm-12">
            <div class="text-sm-left">

                <div class="alert alert-danger">
                    <span>{!! \Session::get('error') !!}</span>
                </div>

            </div>
        </div>
    </div>
    @endif

<!-- New Customize Page -->
@php
$getAdditionalPreference = getAdditionalPreference(['is_phone_signup', 'gtag_id','fpixel_id','is_token_currency_enable', 'token_currency','is_price_by_role','advance_booking_amount', 'advance_booking_amount_percentage','is_user_pre_signup']); //,'seller_sold_title','saller_platform_logo'

@endphp
   <!--Localization start -->
    <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __("Localization") }}</h4>
         </div>
      </div>
    </div>
    <div class="row col-spacing">
        <!-- Date & Time sec start -->
        <div class="col-lg-4 col-xl-3 mb-3">
            <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                @csrf
                <div class="card-box mb-0 h-100 pb-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{ __("Date, Time & Currency") }}</h4>
                        <input type="hidden" name="send_to" id="send_to" value="customize">
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <p class="sub-header">
                        {{ __("View and update the date, time & currency format.") }}
                    </p>
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label for="date_format">{{ __("Date Format") }}</label>
                                <select class="form-control al_box_height" id="date_format" name="date_format">
                                    <option value="DD-MM-YYYY" {{ ($preference && $preference->date_format =="DD-MM-YYYY")? "selected" : "" }}>
                                        DD-MM-YYYY</option>
                                    {{-- <option value="DD/MM/YYYY" {{ ($preference && $preference->date_format =="DD/MM/YYYY")? "selected" : "" }}>
                                        DD/MM/YYYY</option> --}}
                                    <option value="YYYY-MM-DD" {{ ($preference && $preference->date_format =="YYYY-MM-DD")? "selected" : "" }}>
                                        YYYY-MM-DD</option>
                                    <option value="MM/DD/YYYY" {{ ($preference && $preference->date_format =="MM/DD/YYYY")? "selected" : "" }}>
                                        MM/DD/YYYY</option>
                                </select>
                                @if($errors->has('date_format'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('date_format') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label for="time_format">{{ __("Time Format") }}</label>
                                <select class="form-control al_box_height" id="time_format" name="time_format">
                                    <option value="12" {{ ($preference && $preference->time_format =="12")? "selected" : "" }}>12 {{ __("hours") }}
                                    </option>
                                    <option value="24" {{ ($preference && $preference->time_format =="24")? "selected" : "" }}>24 {{ __("hours") }}
                                    </option>
                                </select>
                                @if($errors->has('time_format'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('time_format') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label for="time_format">{{ __("Currency Format (Digits After Decimal)") }}</label>
                                <select class="form-control al_box_height" id="digit_after_decimal" name="digit_after_decimal">
                                    <option value="0" {{ ($preference && $preference->digit_after_decimal == 0)? "selected" : "" }}> {{ __("No Decimal") }}
                                    </option>
                                    @for($i=1; $i<=8; $i++)
                                    <option value="{{$i}}" {{ ($preference && $preference->digit_after_decimal == $i)? "selected" : "" }}>{{$i}} {{ __("Digit") }}
                                    </option>
                                    @endfor
                                </select>
                                @if($errors->has('time_format'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('time_format') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- Date & Time sec end -->
        <!-- <div class="col-lg-4 col-xl-3 mb-3">
            <form method="POST" action="{{route('configure.update', Auth::user()->code)}}" class="h-100">
                <input type="hidden" name="distance_to_time_calc_config" id="distance_to_time_calc_config" value="1">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <div class="card-box mb-2 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                    <h4 class="header-title mb-0">{{__('Delivery Time Estimator')}}</h4>
                    <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <div class="row mt-2">
                    <div class="col-12 mb-2">
                        <label class="primaryCurText">{{__('Distance Unit')}}</label>
                        <select class="form-control al_box_height" id="distance_unit_for_time" name="distance_unit_for_time">
                            <option value="">{{__('Select unit')}}</option>
                            <option value="kilometer" @if((isset($preference) && $preference->distance_unit_for_time == 'kilometer')) selected @endif>{{__('Kilometer')}}</option>
                            <option value="mile" @if((isset($preference) && $preference->distance_unit_for_time == 'mile')) selected @endif>{{__('Mile')}}</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="primaryCurText">{{__('Distance to Time Multiplier (Per 1 distance unit)')}}</label>
                        <input class="form-control" type="number" id="distance_to_time_multiplier" name="distance_to_time_multiplier" value="{{ old('distance_to_time_multiplier', $preference->distance_to_time_multiplier  ?? '')}}" min="0">
                    </div>
                    </div>
                </div>
            </form>

        </div> -->
        <!-- Localization start -->
        <div class="col-lg-4 col-xl-6 mb-3">
            <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                @csrf
                <div class="card-box mb-0 h-100 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="header-title mb-0">{{ __("Languages & Currencies") }}</h4>
                        <input type="hidden" name="send_to" id="send_to" value="customize">
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <p class="sub-header">
                        {{ __("Define and update the languages and currencies") }}
                    </p>
                    {{-- @dd($preference->primary_country->country_id) --}}
                    @php
                        $primary_country_id =  $preference->primary_country ? $preference->primary_country->country_id : '';
                    @endphp
                    <div class="row col-spacing">
                        <div class="col-xl-4 mb-2">
                            <label for="country">{{ __("Primary Country") }}</label>
                            <select class="form-control al_box_height" id="primary_country" name="primary_country">
                                @foreach($countries as $country)
                                    <option {{(isset($preference) && ($country->id == $primary_country_id))? "selected" : "" }} value="{{$country->id}}"> {{$country->name}} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-8 mb-2">
                            <label for="languages">{{ __("Additional Countries") }}</label>
                            <select class="form-control al_box_height select2-multiple" id="countries" name="countries[]" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                @foreach($countries as $country)
                                @if($country->id != $primary_country_id)
                                    <option value="{{$country->id}}" {{ (isset($preference) && in_array($country->id, $cli_countries))? "selected" : "" }}>{{$country->name ??''}}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-4 mb-2">
                            <label for="languages">{{ __("Primary Language") }}</label>
                            <select class="form-control al_box_height" id="primary_language" name="primary_language">
                                @php
                                   $primary_language_id =  $preference->primarylang ? $preference->primarylang->language_id : '';
                                @endphp
                                @foreach($languages as $lang)
                                    <option {{(isset($preference) && ($lang->id == $primary_language_id))? "selected" : "" }} value="{{$lang->id}}"> {{$lang->name}} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-8 mb-2">
                            <label for="languages">{{ __("Additional Languages") }}</label>
                            <select class="form-control al_box_height select2-multiple" id="languages" name="languages[]" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                @foreach($languages as $lang)
                                @if($lang->id != $primary_language_id)
                                    <option value="{{$lang->id}}" {{ (isset($preference) && in_array($lang->id, $cli_langs))? "selected" : "" }}>{{$lang->name ??''}} ({{$lang->nativeName??''}})</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-4 mb-2">
                            <label for="primary_currency">{{ __("Primary Currency") }}</label>
                            <select class="form-control al_box_height" id="primary_currency" name="primary_currency">
                                @foreach($currencies as $currency)
                                <option iso="{{$currency->iso_code.' '.$currency->symbol}}" {{ (isset($preference) && $preference->primary->currency->id == $currency->id) ? "selected" : ""}} value="{{$currency->id}}"> {{$currency->iso_code.' '.$currency->symbol}} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-8">
                            <label for="currency">{{ __("Additional Currency") }}</label>
                            <select class="form-control al_box_height select2-multiple" id="currency" name="currency_data[]" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                @foreach($currencies as $currency)
                                @if($preference->primary->currency->id != $currency->id)
                                <option value="{{$currency->id}}" iso="{{$currency->iso_code}}" {{ (isset($preference) && in_array($currency->id, $cli_currs))? "selected" : "" }}> {{$currency->iso_code}} {{!empty($currency->symbol) ? $currency->symbol : ''}} </option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="row multiplierData">
                                @if($preference->currency)
                                @foreach($preference->currency as $ac)
                                <div class="col-sm-10 offset-sm-4 col-lg-12 offset-lg-0 col-xl-8 offset-xl-4 mb-2" id="addCur-{{$ac->currency->id}}">
                                    <label class="primaryCurText">1 {{$preference->primary->currency->iso_code}} {{!empty($preference->primary->currency->symbol) ? $preference->primary->currency->symbol : ''}} = </label>
                                    <input class="form-control al_box_height w-50 d-inline-block" type="text" value="{{$ac->doller_compare}}" step=".0001" name="multiply_by[{{$ac->currency->id}}]" oninput="changeCurrencyValue(this)"> {{$ac->currency->iso_code}} {{!empty($ac->currency->symbol) ? $ac->currency->symbol : ''}}
                                    <input type="hidden" name="cuid[]" class="curr_id" value="{{ $ac->currency->id }}">
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- Localization end -->
    </div>


    <!--Localization end -->
{{-- vendoe typs section aline by harbans singh :) --}}
    <div class="row col-spacing">
        <!--Vendor Type &  Distance to Time Calculator start -->
        <div class="col-xl-3 col-lg-3 mb-3">
            <div class="page-title-box">
                <h4 class="page-title text-uppercase">{{ __("Vendor Type") }}</h4>
            </div>
            {{-- @if($client_preference_detail->business_type != 'taxi' && $client_preference_detail->business_type != 'laundry' ) --}}
            @php
                $typeArray = getCategoryTypes();

            @endphp
            <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <input type="hidden" name="verify_vendor_type" id="verify_vendor_type" value="1">
                <div class="card-box mb-2 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="header-title mb-0">{{ __("Vendor Type") }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>

                    <div class="row align-items-start">
                        @foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)

                            @php
                                $VendorTypesName = $vendor_typ_key.'_check';
                            @endphp
                            @if(in_array($vendor_typ_key, $typeArray))
                                <div class="col-md-12">
                                    <div class="form-group d-flex justify-content-between">
                                        <label for="{{$VendorTypesName}}" class="mr-3 mb-0 ">{{getDynamicTypeName($vendor_typ_value)}}</label>
                                        <input type="checkbox" data-plugin="switchery" name="{{$VendorTypesName}}" id="{{$VendorTypesName}}" class="form-control vendorTypeChange" data-color="#43bee1" @if((isset($preference) && $preference->$VendorTypesName == '1')) checked='checked' @endif>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                    </div>
                </div>
            </form>
            {{-- @endif --}}
        </div>

        {{-- <div class="col-xl-3 col-lg-3 mb-3">
            <div class="page-title-box">
                <h4 class="page-title text-uppercase">{{ __("Seller Platform") }}</h4>
            </div>
            @php
                $typeArray = getCategoryTypes();
            @endphp
            <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <div class="card-box mb-2 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="header-title mb-0">{{ __("Seller Platform") }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <div class="col-md-6">
                        <label>{{ __('Upload Logo') }} </label>
                        <input type="file" accept="image/*" data-plugins="dropify" name="saller_platform_logo" class="dropify" data-default-file="" />
                        <label class="logo-size text-right w-100">{{ __('Logo Size') }} 170x96</label>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="seller_sold_title">Sold Title</label>
                            <input type="text" name="seller_sold_title" id="seller_sold_title" value=" @if( @$getAdditionalPreference['seller_sold_title'] != '') {{$getAdditionalPreference['seller_sold_title']??''}} @endif" class="form-control" placeholder="Sold Title" />
                        </div>
                    </div>
                </div>
            </form>
        </div> --}}
        <!--Vendor Type &  Distance to Time Calculator end -->

        @if($client_preference_detail->business_type != 'taxi' && $client_preference_detail->business_type != 'food_grocery_ecommerce' && $client_preference_detail->business_type != 'laundry' && $client_preference_detail->on_demand_check == 1)
        <div class="col-xl-3 col-lg-3 mb-3">
            <form method="POST" action="{{route('configure.update', Auth::user()->code)}}" class="h-100">
                @csrf
            <!-- On Demand Services section start -->
            <input type="hidden" name="send_to" id="send_to" value="customize">
            <div class="card-box h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title mb-0">{{ __('On Demand Services') }}</h4>
                    <button class="btn btn-info d-block" type="submit"  name="need_dispacher_home_other_service_submit_btn" value ="1"> {{ __("Save") }} </button>
                </div>
                <p class="sub-header">{{ __('Offer On Demand Services with Dispatcher.') }}</p>
                <div class="row">
                    <div class="col-12">
                    <div class="form-group mb-0">
                        <div class="form-group mb-0 switchery-demo">
                            <label for="need_dispacher_home_other_service" class="mr-3">{{ __('Enable') }}</label>
                            <input type="checkbox" data-plugin="switchery" name="need_dispacher_home_other_service" id="need_dispacher_home_other_service" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->need_dispacher_home_other_service == '1')) checked='checked' @endif>
                        </div>
                    </div>

                    <div class="form-group mt-3 mb-0 home_other_dispatcherFields" style="{{((isset($preference) && $preference->need_dispacher_home_other_service == '1')) ? '' : 'display:none;'}}">
                        <label for="dispacher_home_other_service_key_url">{{ __('Dispatcher URL') }} *(https://www.abc.com)</label>
                        <input type="text" name="dispacher_home_other_service_key_url" id="dispacher_home_other_service_key_url" placeholder="https://www.abc.com" class="form-control" value="{{ old('dispacher_home_other_service_key_url', $preference->dispacher_home_other_service_key_url ?? '')}}">
                        @if($errors->has('dispacher_home_other_service_key_url'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('dispacher_home_other_service_key_url') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group mt-3 mb-0 home_other_dispatcherFields" style="{{((isset($preference) && $preference->need_dispacher_home_other_service == '1')) ? '' : 'display:none;'}}">
                        <label for="dispacher_home_other_service_key_code">{{ __('Dispatcher Short code') }}</label>
                        <input type="text" name="dispacher_home_other_service_key_code" id="dispacher_home_other_service_key_code" placeholder="" class="form-control" value="{{ old('dispacher_home_other_service_key_code', $preference->dispacher_home_other_service_key_code ?? '')}}">
                        @if($errors->has('dispacher_home_other_service_key_code'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('dispacher_home_other_service_key_code') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group mt-3 mb-0 home_other_dispatcherFields" style="{{((isset($preference) && $preference->need_dispacher_home_other_service == '1')) ? '' : 'display:none;'}}">
                        <label for="dispacher_home_other_service_key">{{ __('Dispatcher API key') }}</label>
                        <input type="text" name="dispacher_home_other_service_key" id="dispacher_home_other_service_key" placeholder="" class="form-control" value="{{ old('dispacher_home_other_service_key', $preference->dispacher_home_other_service_key ?? '')}}">
                        @if($errors->has('dispacher_home_other_service_key'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('dispacher_home_other_service_key') }}</strong>
                        </span>
                        @endif
                    </div>

                    </div>
                </div>
            </div><!-- On Demand Services section end -->
            </form>
        </div>
        @endif

        @if($client_preference_detail->business_type == 'taxi' || $client_preference_detail->business_type == '' || $client_preference_detail->business_type == 'super_app' && $client_preference_detail->pick_drop_check == 1 )

        <div class="col-xl-3 col-lg-3 mb-3">
            <form method="POST" action="{{route('configure.update', Auth::user()->code)}}" class="h-100">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <!-- Pickup & Delivery section start -->
            <div class="card-box h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title mb-0">{{ __("Pickup & Delivery") }}</h4>
                    <button class="btn btn-info d-block" type="submit"  name="need_dispacher_ride_submit_btn" value ="1"> {{ __("Save") }} </button>
                </div>
                <p class="sub-header">{{ __("Offer Pickup & Delivery with Dispatcher.") }}</p>
                <div class="row">
                    <div class="col-12">
                    <div class="form-group mb-0">
                        <div class="form-group mb-0 switchery-demo">
                            <label for="need_dispacher_ride" class="mr-3">{{ __("Enable") }}</label>
                            <input type="checkbox" data-plugin="switchery" name="need_dispacher_ride" id="need_dispacher_ride" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->need_dispacher_ride == '1')) checked='checked' @endif>
                        </div>
                    </div>

                    <div class="form-group mt-3 mb-0 dispatcherFields" style="{{((isset($preference) && $preference->need_dispacher_ride == '1')) ? '' : 'display:none;'}}">
                        <label for="pickup_delivery_service_key_url">{{ __("Dispatcher URL") }} * ( https://www.abc.com )</label>
                        <input type="text" name="pickup_delivery_service_key_url" id="pickup_delivery_service_key_url" placeholder="https://www.abc.com" class="form-control" value="{{ old('pickup_delivery_service_key_url', $preference->pickup_delivery_service_key_url ?? '')}}">
                        @if($errors->has('pickup_delivery_service_key_url'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('pickup_delivery_service_key_url') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group mt-3 mb-0 dispatcherFields" style="{{((isset($preference) && $preference->need_dispacher_ride == '1')) ? '' : 'display:none;'}}">
                        <label for="delivery_service_key_code">{{ __("Dispatcher Short code") }}</label>
                        <input type="text" name="pickup_delivery_service_key_code" id="pickup_delivery_service_key_code" placeholder="" class="form-control" value="{{ old('pickup_delivery_service_key_code', $preference->pickup_delivery_service_key_code ?? '')}}">
                        @if($errors->has('pickup_delivery_service_key_code'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('pickup_delivery_service_key_code') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group mt-3 mb-0 dispatcherFields" style="{{((isset($preference) && $preference->need_dispacher_ride == '1')) ? '' : 'display:none;'}}">
                        <label for="pickup_delivery_service_key">{{ __("Dispatcher API key") }}</label>
                        <input type="text" name="pickup_delivery_service_key" id="pickup_delivery_service_key" placeholder="" class="form-control" value="{{ old('pickup_delivery_service_key', $preference->pickup_delivery_service_key ?? '')}}">
                        @if($errors->has('pickup_delivery_service_key'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('pickup_delivery_service_key') }}</strong>
                        </span>
                        @endif
                    </div>

                    </div>
                </div>
            </div><!-- Pickup & Delivery section end -->
            </form>
        </div>
        @endif

        @if($client_preference_detail->business_type == 'laundry' && $client_preference_detail->laundry_check == 1 )
        <div class="col-xl-3 col-lg-3 mb-3">
            <!-- laundry section start -->
            <form method="POST" action="{{route('configure.update', Auth::user()->code)}}">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
            <div class="card-box h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title mb-0">{{ __("Laundry") }}</h4>
                    <button class="btn btn-info d-block" type="submit" name="laundry_submit_btn" value ="1"> {{ __("Save") }} </button>
                </div>
                <p class="sub-header">{{ __("Offer laundry with Dispatcher.") }}</p>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-0">
                        <div class="form-group mb-0 switchery-demo">
                            <label for="need_laundry_service" class="mr-3">{{ __("Enable") }}</label>
                            <input data-plugin="switchery" name="need_laundry_service" id="need_laundry_service" class="form-control" data-color="#43bee1" type="checkbox" @if((isset($preference) && $preference->need_laundry_service == '1')) checked @endif >
                        </div>
                        </div>

                        <div class="form-group mt-3 mb-0 laundryServiceFields" style="{{((isset($preference) && $preference->need_laundry_service == '1')) ? '' : 'display:none;'}}">
                        <label for="laundry_service_key_url">{{ __("Dispatcher URL") }} * ( https://www.abc.com )</label>
                        <input type="text" name="laundry_service_key_url" id="laundry_service_key_url" placeholder="https://www.abc.com" class="form-control" value="{{ old('laundry_service_key_url', $preference->laundry_service_key_url ?? '')}}">
                        @if($errors->has('laundry_service_key_url'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('laundry_service_key_url') }}</strong>
                        </span>
                        @endif
                        </div>
                        <div class="form-group mt-3 mb-0 laundryServiceFields" style="{{((isset($preference) && $preference->need_laundry_service == '1')) ? '' : 'display:none;'}}">
                        <label for="laundry_service_key_code">{{ __("Dispatcher Short code") }}</label>
                        <input type="text" name="laundry_service_key_code" id="laundry_service_key_code" placeholder="" class="form-control" value="{{ old('laundry_service_key_code', $preference->laundry_service_key_code ?? '')}}">
                        @if($errors->has('laundry_service_key_code'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('laundry_service_key_code') }}</strong>
                        </span>
                        @endif
                        </div>
                        <div class="form-group mt-3 mb-0 laundryServiceFields" style="{{((isset($preference) && $preference->need_laundry_service == '1')) ? '' : 'display:none;'}}">
                        <label for="laundry_service_key">{{ __("Dispatcher API key") }}</label>
                        <input type="text" name="laundry_service_key" id="laundry_service_key" placeholder="" class="form-control" value="{{ old('laundry_service_key', $preference->laundry_service_key ?? '')}}">
                        @if($errors->has('laundry_service_key'))
                        <span class="text-danger" role="alert">
                            <strong>{{ $errors->first('laundry_service_key') }}</strong>
                        </span>
                        @endif
                        </div>

                        @if($laundry_teams != null && count($laundry_teams))
                        <div class="form-group mt-3 mb-0 laundryServiceFields" style="{{(isset($preference) && $preference->need_laundry_service == '1') ? '' : 'display: none;'}}" id="laundryPickupTeamListDiv">
                        <div class="form-group">
                            {!! Form::label('title', __('Team Tag For Laundry Pickup'),['class' => 'control-label']) !!}
                            <select class="form-control" id="laundryPickupTeamList" name="laundry_pickup_team" data-toggle="select2" >
                                <option value="0">{{__('Select Team Tag')}}</option>
                                @foreach($laundry_teams as $nm)
                                    <option value="{{$nm['name']}}" @if($preference->laundry_pickup_team == $nm['name']) selected="selected" @endif>{{$nm['name']}}</option>
                                @endforeach

                            </select>
                        </div>
                        </div>

                        <div class="form-group mt-3 mb-0 laundryServiceFields" style="{{(isset($preference) && $preference->need_laundry_service == '1') ? '' : 'display: none;'}}" id="laundryDropoffTeamListDiv">
                        <div class="form-group">
                            {!! Form::label('title', __('Team Tag For Laundry Dropoff'),['class' => 'control-label']) !!}
                            <select class="form-control" id="laundryDropoffTeamList" name="laundry_dropoff_team" data-toggle="select2" >
                                <option value="0">{{__('Select Team Tag')}}</option>
                                @foreach($laundry_teams as $nm)
                                    <option value="{{$nm['name']}}" @if($preference->laundry_dropoff_team == $nm['name']) selected="selected" @endif>{{$nm['name']}}</option>
                                @endforeach

                            </select>
                        </div>
                        </div>
                        @endif


                    </div>
                </div>
            </div>
            </form>
            </div>
        @endif

       {{-- @if( $client_preference_detail->appointment_check == 1 )
        <div class="col-xl-3 col-lg-3 mb-3">
            <!-- appointment section start -->
            <form method="POST" action="{{route('configure.update', Auth::user()->code)}}">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
            <div class="card-box h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title mb-0">{{ getDynamicTypeName('Appointment')  }}</h4>
                    <button class="btn btn-info d-block" type="submit" name="appointment_submit_btn" value ="1"> {{ __("Save") }} </button>
                </div>
                <p class="sub-header">{{ __("Offer appointment with Dispatcher.") }}</p>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-0">
                        <div class="form-group mb-0 switchery-demo">
                            <label for="need_appointment_service" class="mr-3">{{ __("Enable") }}</label>
                            <input data-plugin="switchery" name="need_appointment_service" id="need_appointment_service" class="form-control" data-color="#43bee1" type="checkbox" @if((isset($preference) && $preference->need_appointment_service == '1')) checked @endif >
                        </div>
                        </div>

                        <div class="form-group mt-3 mb-0 appointmentServiceFields" style="{{((isset($preference) && $preference->need_appointment_service == '1')) ? '' : 'display:none;'}}">
                            <label for="appointment_service_key_url">{{ __("Dispatcher URL") }} * ( https://www.abc.com )</label>
                                <input type="text" name="appointment_service_key_url" id="appointment_service_key_url" placeholder="https://www.abc.com" class="form-control" value="{{ old('appointment_service_key_url', $preference->appointment_service_key_url ?? '')}}">
                                @if($errors->has('appointment_service_key_url'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('appointment_service_key_url') }}</strong>
                                </span>
                                @endif
                        </div>
                        <div class="form-group mt-3 mb-0 appointmentServiceFields" style="{{((isset($preference) && $preference->need_appointment_service == '1')) ? '' : 'display:none;'}}">
                            <label for="appointment_service_key_code">{{ __("Dispatcher Short code") }}</label>
                            <input type="text" name="appointment_service_key_code" id="appointment_service_key_code" placeholder="" class="form-control" value="{{ old('appointment_service_key_code', $preference->appointment_service_key_code ?? '')}}">
                            @if($errors->has('appointment_service_key_code'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('appointment_service_key_code') }}</strong>
                            </span>
                            @endif
                        </div>
                        <div class="form-group mt-3 mb-0 appointmentServiceFields" style="{{((isset($preference) && $preference->need_appointment_service == '1')) ? '' : 'display:none;'}}">
                            <label for="appointment_service_key">{{ __("Dispatcher API key") }}</label>
                            <input type="text" name="appointment_service_key" id="appointment_service_key" placeholder="" class="form-control" value="{{ old('appointment_service_key', $preference->appointment_service_key ?? '')}}">
                            @if($errors->has('appointment_service_key'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('appointment_service_key') }}</strong>
                            </span>
                            @endif
                        </div>




                    </div>
                </div>
            </div>
            </form>
            </div>
        @endif --}}

    </div>
{{-- vendoe typs section --}}
        <!-- Links Start -->
    <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __("Links") }}</h4>
         </div>
      </div>
    </div>
    <div class="row col-spacing">
        <!-- Start custom domain  -->
        <div class="col-lg-4 col-xl-3 mb-3">
            <form method="POST" class="h-100" action="{{route('client.updateDomain', Auth::user()->code)}}">
                @csrf
                <div class="card-box mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="header-title mb-0">{{ __("Custom Domain") }}</h4>
                        <input type="hidden" name="send_to" id="send_to" value="customize">
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <p class="sub-header">{{ __("Update custom domain here.") }}</p>
                    <label for="custom_domain">*{{__("Make sure you already pointed to IP")}} ({{\env('IP')}}) {{__("from your domain.")}}</label>
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="custom_domain">{{ __("Custom Domain") }}</label>
                                <div class="domain-outer d-flex align-items-center">
                                    <div class="domain_name">https://</div>
                                    <input type="text" name="custom_domain" id="custom_domain" placeholder="" class="form-control al_box_height" value="{{ old('custom_domain', $preference->domain->custom_domain ?? '')}}">
                                </div>
                                @if($errors->has('custom_domain'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('custom_domain') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- End custom domain  -->
        <!-- app link start  -->
        <div class="col-lg-4 col-xl-3 mb-3">
            <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                <div class="card-box mb-0 h-100">
                    <input type="hidden" name="distance_to_time_calc_config" id="distance_to_time_calc_config" value="1">
                    @csrf
                    <input type="hidden" name="send_to" id="send_to" value="customize">
                    <div class="mb-0 py-0">
                       <div class="d-flex align-items-center justify-content-between">
                          <h4 class="header-title mb-0">{{ __("Android/IOS Link") }}</h4>
                          <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                       </div>
                       <div class="row mt-2">
                          <div class="col-12 mb-2">
                             <label class="primaryCurText">{{__('Android App Link')}}</label>
                             <input class="form-control" type="url" id="android_app_link" name="android_app_link" value="{{ old('android_app_link', $preference->android_app_link  ?? '')}}">
                          </div>
                          <div class="col-12">
                             <label class="primaryCurText">{{__('IOS App Link')}}</label>
                             <input class="form-control" type="url" id="ios_link" name="ios_link" value="{{ old('ios_link', $preference->ios_link  ?? '')}}" >
                          </div>
                       </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- app link end  -->
        <!-- Start Social Link -->
        <div class="col-lg-4 col-xl-3 mb-3">
            <div class="card-box mb-0 h-100 pb-1">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title mb-0">{{ __("Social Media") }}</h4>
                    <button class="btn btn-info d-block" id="add_social_media_modal_btn">
                        <i class="mdi mdi-plus-circle mr-1"></i>{{ __("Add") }}
                    </button>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-centered table-nowrap table-striped" id="promo-datatable">
                        <thead>
                            <tr>
                                <th>{{ __("Icon") }}</th>
                                <th>{{ __("URL") }}</th>
                                <th>{{ __("Action") }}</th>
                            </tr>
                        </thead>
                        <tbody id="post_list">
                            @forelse($social_media_details as $social_media_detail)
                            <tr>
                                <td>
                                    <i class="fab fa-{{$social_media_detail->icon}}" aria-hidden="true"></i>
                                </td>
                                <td>
                                    <a href="{{$social_media_detail->url}}" target="_blank">{{$social_media_detail->url}}</a>
                                </td>
                                <td>
                                    <div>
                                        <div class="inner-div" style="float: left;">
                                            <a class="action-icon edit_social_media_option_btn" data-social_media_detail_id="{{$social_media_detail->id}}">
                                                <i class="mdi mdi-square-edit-outline"></i>
                                            </a>
                                        </div>
                                        <div class="inner-div">
                                            <button type="button" class="btn btn-primary-outline action-icon delete_social_media_option_btn" data-social_media_detail_id="{{$social_media_detail->id}}">
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Social Link -->
    </div>
    <!-- Links End -->

    <!-- Nomenclature Start  -->
    <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __("Nomenclature") }}</h4>
         </div>
      </div>
    </div>
    <div class="row col-spacing">
        <div class="col-lg-12 col-lg-12 mb-3">
            <form method="POST" class="h-100" action="{{route('nomenclature.store', Auth::user()->code)}}">
                @csrf
                <div class="card-box mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="header-title mb-0">{{ __("Nomenclature") }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <p class="sub-header">{{ __("View and update the naming") }}</p>
                    <div class="table-responsive">
                        <div class="row mb-2 mx-0 flex-nowrap">
                            <div class="col-sm-2"></div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{$client_language->langName}}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Delivery") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="delivery_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="delivery_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId, 'Delivery') }}">
                                    @if($k == 0)
                                        @if($errors->has('delivery_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Dine-In") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="dinein_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="dinein_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Dine-In')}}">
                                    @if($k == 0)
                                        @if($errors->has('dinein_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Takeaway") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="takeaway_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="takeaway_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Takeaway')}}">
                                    @if($k == 0)
                                        @if($errors->has('takeaway_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                         <!-- add extra vendor types  add by harbans-->
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Rentals") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="rentals_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="rentals_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Rentals')}}">
                                    @if($k == 0)
                                        @if($errors->has('rentals_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Car-Rental") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="car_rental_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="car_rental_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId, 'Car-Rental') }}">
                                    @if($k == 0)
                                        @if($errors->has('car_rental_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Pick & Drop") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="pick_drop_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="pick_drop_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Pick & Drop')}}">
                                    @if($k == 0)
                                        @if($errors->has('pick_drop_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Services") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="on_demand_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="on_demand_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Services')}}">
                                    @if($k == 0)
                                        @if($errors->has('on_demand_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Laundry") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="laundry_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="laundry_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Laundry')}}">
                                    @if($k == 0)
                                        @if($errors->has('laundry_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Appointment") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="appointment_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="appointment_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Appointment')}}">
                                    @if($k == 0)
                                        @if($errors->has('appointment_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- end vendor types  add by harbans-->
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Vendors") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'vendors')}}">
                                    @if($k == 0)
                                        @if($errors->has('names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Sellers") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="seller_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="seller_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'sellers')}}">
                                    @if($k == 0)
                                        @if($errors->has('seller_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Loyalty Cards") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="loyalty_cards_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="loyalty_cards_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Loyalty Cards')}}">
                                    @if($k == 0)
                                        @if($errors->has('loyalty_cards_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Search") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="search_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="search_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Search')}}">
                                    @if($k == 0)
                                        @if($errors->has('search_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Wishlist") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="wishlist_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="wishlist_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Wishlist')}}">
                                    @if($k == 0)
                                        @if($errors->has('wishlist_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Zip Code") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="zipCode_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="zipCode_name[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Zip Code')}}">
                                    @if($k == 0)
                                        @if($errors->has('zipCode_name.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($want_to_tip_nomenclature)
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Do you want to give a tip") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="wantToTip_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="wantToTip_name[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId, $want_to_tip_nomenclature->id)}}">
                                    @if($k == 0)
                                        @if($errors->has('wantToTip_name.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The want To Tip field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        @if($include_gift_nomenclature)
                            <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                                <div class="col-sm-2">
                                    <div class="form-group mb-0">
                                        <label for="custom_domain">{{ __("Does this include a gift?") }}</label>
                                    </div>
                                </div>
                                @foreach($client_languages as $k => $client_language)
                                    <div class="col-sm-2">
                                        <div class="form-group mb-0">
                                            <input type="hidden" name="includeGift_language_ids[]" value="{{$client_language->langId}}">
                                            <input type="text" name="includeGift_name[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId, $include_gift_nomenclature->id)}}">
                                            @if($k == 0 && $errors->has('includeGift_name.0'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ __("The include Gift field is required.") }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if($control_panel_nomenclature)
                            <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                                <div class="col-sm-2">
                                    <div class="form-group mb-0">
                                        <label for="custom_domain">{{ __("Control Panel") }}</label>
                                    </div>
                                </div>
                                @foreach($client_languages as $k => $client_language)
                                    <div class="col-sm-2">
                                        <div class="form-group mb-0">
                                            <input type="hidden" name="controlPanel_language_ids[]" value="{{$client_language->langId}}">
                                            <input type="text" name="controlPanel_name[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId, $control_panel_nomenclature->id)}}">
                                            @if($k == 0 && $errors->has('controlPanel_name.0'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ __("The Control Panel field is required.") }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if(!empty($fixed_fee->id))
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Fixed Fee") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="FixedFee_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="FixedFee_name[]" class="form-control al_box_height" value="{{ ($fixed_fee) ? \App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId, $fixed_fee->id) : ''}}">
                                    @if($k == 0)
                                        @if($errors->has('FixedFee_name.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The Fixed Fee field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Royo Dispatcher") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="royo_dispatcher_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="royo_dispatcher_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Royo Dispatcher')}}">
                                    @if($k == 0)
                                        @if($errors->has('royo_dispatcher_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Referral Code") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="referral_code_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="referral_code_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Referral Code')}}">
                                    @if($k == 0)
                                        @if($errors->has('referral_code_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center" style="display: {{$client_preference_detail->business_type == 'taxi' ? '' : 'none'}} !important;">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Rides")}}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="rides_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="rides_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Rides')}}">
                                    @if($k == 0)
                                        @if($errors->has('rides_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center" style="display: {{$client_preference_detail->business_type == 'taxi' ? 'none' : ''}} !important;">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Orders") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="orders_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="orders_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Orders')}}">
                                    @if($k == 0)
                                        @if($errors->has('orders_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Product Order Form") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="product_order_form_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="product_order_form_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Product Order Form')}}">
                                    @if($k == 0)
                                        @if($errors->has('product_order_form_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Enter Drop Location") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="enter_drop_location_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="enter_drop_location_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Enter Drop Location')}}">
                                    @if($k == 0)
                                        @if($errors->has('enter_drop_location_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Vendor Name") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="enter_vendor_name_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="enter_vendor_name_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Vendor Name')}}">
                                    @if($k == 0)
                                        @if($errors->has('enter_vendor_name_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Ride Accepted") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="ride_accepted_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="ride_accepted_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Ride Accepted')}}">
                                    @if($k == 0)
                                        @if($errors->has('ride_accepted_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Searching For Nearby Drivers") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="search_nearby_driver_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="search_nearby_driver_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Searching For Nearby Drivers')}}">
                                    @if($k == 0)
                                        @if($errors->has('search_nearby_driver_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Hold On! We are looking for drivers nearby!") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="looking_driver_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="looking_driver_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Hold On! We are looking for drivers nearby!')}}">
                                    @if($k == 0)
                                        @if($errors->has('looking_driver_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>


                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Product Name") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="product_name_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="product_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Product Name')}}">
                                    @if($k == 0)
                                        @if($errors->has('product_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("IFSC Code") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="ifsc_code_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="ifsc_code[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'IFSC Code')}}">
                                    @if($k == 0)
                                        @if($errors->has('ifsc_code.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Stock Status") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="stock_status_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="stock_status_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Stock Status')}}">
                                    @if($k == 0)
                                        @if($errors->has('stock_status_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Variant") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="variant_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="variant_names[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Variant')}}">
                                    @if($k == 0)
                                        @if($errors->has('variant_names.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Account Name") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="account_name_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="account_name[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Account Name')}}">
                                    @if($k == 0)
                                        @if($errors->has('account_name.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Bank Name") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="bank_name_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="bank_name[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Bank Name')}}">
                                    @if($k == 0)
                                        @if($errors->has('bank_name.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Account Number") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="account_number_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="account_number[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Account Number')}}">
                                    @if($k == 0)
                                        @if($errors->has('account_number.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("IFSC Code") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="ifsc_code_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="ifsc_code[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId, 'IFSC Code')}}">
                                    @if($k == 0)
                                        @if($errors->has('ifsc_code.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Aadhaar Front") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="aadhaar_front_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="aadhaar_front[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Aadhaar Front')}}">
                                    @if($k == 0)
                                        @if($errors->has('aadhaar_front.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Aadhaar Back") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="aadhaar_back_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="aadhaar_back[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Aadhaar Back')}}">
                                    @if($k == 0)
                                        @if($errors->has('aadhaar_back.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Aadhaar Number") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="aadhaar_number_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="aadhaar_number[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Aadhaar Number')}}">
                                    @if($k == 0)
                                        @if($errors->has('aadhaar_number.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("UPI Id") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="upi_id_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="upi_id[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'UPI Id')}}">
                                    @if($k == 0)
                                        @if($errors->has('upi_id.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Similar Product") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="similar_product_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="similar_product[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Similar Product')}}">
                                    @if($k == 0)
                                        @if($errors->has('similar_product.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                      <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("P2P") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="p2p_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="p2p_id[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'p2p')}}">
                                    @if($k == 0)
                                        @if($errors->has('p2p_id.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                      <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Where can we pick you up?") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="pickup_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="pickup_id[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Where can we pick you up?')}}">
                                    @if($k == 0)
                                        @if($errors->has('pickup_id.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="custom_domain">{{ __("Where To?") }}</label>
                                </div>
                            </div>
                            @php

                            @endphp
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="where_to_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="where_to_id[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Where To?')}}">
                                    @if($k == 0)
                                        @if($errors->has('where_to_id.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="agree_term">{{ __("Agree Term") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="agree_term_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="agree_term[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Agree Term')}}">
                                    @if($k == 0)
                                        @if($errors->has('agree_term.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="agree_term">{{ __("Recurring") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="recurring_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="recurring[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Recurring')}}">
                                    @if($k == 0)
                                        @if($errors->has('recurring.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="agree_term">{{ __("Open") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="open_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="open[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Open')}}">
                                    @if($k == 0)
                                        @if($errors->has('open.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mb-2 mx-0 flex-nowrap d-flex align-items-center">
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <label for="agree_term">{{ __("Products") }}</label>
                                </div>
                            </div>
                            @foreach($client_languages as $k => $client_language)
                            <div class="col-sm-2">
                                <div class="form-group mb-0">
                                    <input type="hidden" name="products_language_ids[]" value="{{$client_language->langId}}">
                                    <input type="text" name="products[]" class="form-control al_box_height" value="{{ App\Models\NomenclatureTranslation::getNameBylanguageId($client_language->langId,'Products')}}">
                                    @if($k == 0)
                                        @if($errors->has('products.0'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ __("The primary language name field is required.") }}</strong>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Nomenclature End  -->

    <!--User onbarding start -->
    <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __("User Onboarding") }}</h4>
         </div>
      </div>
    </div>
    <div class="row col-spacing">
       <!-- User Authentication start-->
        <div class="col-md-4 mb-3">
            <div class="card-box pb-2 h-100">
                <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                <input type="hidden" name="verify_config" id="verify_config" value="1">
                <input type="hidden" name="send_to" id="send_to" value="customize">
                @csrf

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="header-title mb-0">{{ __("User Authentication") }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <h4 class="header-title text-uppercase"></h4>
                    <div class="row align-items-start">
                        <div class="col-sm-12">
                            <div class="form-group d-flex justify-content-between">
                                <label for="verify_email" class="mr-3 mb-0">{{ __("Verify Email") }}</label>
                                <input type="checkbox" data-plugin="switchery" name="verify_email" id="verify_email" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->verify_email == '1')) checked='checked' @endif>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group d-flex justify-content-between">
                                <label for="verify_phone" class="mr-3 mb-0">{{ __("Verify Phone") }}</label>
                                <input type="checkbox" data-plugin="switchery" name="verify_phone" id="verify_phone" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->verify_phone == '1')) checked='checked' @endif>
                            </div>
                        </div>
                        <!-- <div class="col-sm-12">
                            <div class="form-group d-flex justify-content-between">
                                <label for="verify_phone" class="mr-3 mb-0">{{ __("Concise SignUp") }}</label>
                                <input type="checkbox" data-plugin="switchery" name="concise_signup" id="concise_signup" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->concise_signup == '1')) checked='checked' @endif>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group d-flex justify-content-between">
                                <label for="Phone_signup" class="mr-3 mb-0">{{ __("Phone SignUp") }}</label>
                                <input type="checkbox" data-plugin="switchery" name="is_phone_signup_switch" id="is_phone_signup_switch" class="form-control checkbox_change" data-className="is_phone_signup"  data-color="#43bee1" @if( @$getAdditionalPreference['is_phone_signup'] == '1') checked='checked' @endif>
                                <input type="hidden"  @if(@$getAdditionalPreference['is_phone_signup'] == 1) value="1" @else value="0" @endif  name="is_phone_signup"  id="is_phone_signup"/>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group d-flex justify-content-between">
                                <label for="is_pre_signup" class="mr-3 mb-0">{{ __("User Pre SignUp") }}</label>
                                <input type="checkbox" data-plugin="switchery" name="is_user_pre_signup" id="is_user_pre_signup_switch" class="form-control checkbox_change" data-className="is_user_pre_signup"  data-color="#43bee1" @if( @$getAdditionalPreference['is_user_pre_signup'] == '1') checked='checked' @endif>
                                <input type="hidden"  @if(@$getAdditionalPreference['is_user_pre_signup'] == 1) value="1" @else value="0" @endif  name="is_user_pre_signup"  id="is_user_pre_signup"/>
                            </div>
                        </div> -->
                        @foreach($verify_options as $key => $opt)
                        @php $creds = json_decode($opt->credentials); @endphp
                        <input type="hidden" name="method_id[]" id="{{$opt->id}}" value="{{$opt->id}}">
                        <input type="hidden" name="method_name[]" id="{{$opt->code}}" value="{{$opt->code}}">
                        <div class="col-sm-12">
                            <div class="form-group d-flex justify-content-between">
                                <label for="verify_phone" class="mr-3 mb-0">{{ __("Verify Via") }} {{$opt->title}}</label>
                                <input type="checkbox" data-plugin="switchery" name="active[{{$opt->id}}]" class="form-control verification_options" data-id="{{$opt->id}}" data-title="{{$opt->code}}" data-color="#43bee1" @if( $opt->status == '1') checked='checked' @endif>
                            </div>
                            @if(strtolower($opt->code) == 'passbase')
                            <div class="verification_creds mt-2" id="passbase_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group d-flex justify-content-between">
                                            <label for="age_restriction_on_product_mode" class="mr-3 mb-0">{{ __("Age Restricted") }}</label>
                                            <input type="checkbox" data-plugin="switchery" name="age_restriction_on_product_mode" class="form-control" @if( (isset($preference) && $preference->age_restriction_on_product_mode == '1')) checked='checked' @endif>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-2">
                                            <label for="passbase_publish_key" class="mr-3">{{ __("Publishable API key") }}</label>
                                            <input type="text" name="passbase_publish_key" id="passbase_publish_key" class="form-control" value="{{$creds->publish_key ?? ''}}" @if($opt->status == 1) required @endif>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-2">
                                            <label for="passbase_secret_key" class="mr-3">{{ __("Secret API Key") }}</label>
                                            <input type="password" name="passbase_secret_key" id="passbase_secret_key" class="form-control" value="{{$creds->secret_key ?? ''}}" @if($opt->status == 1) required @endif>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
        <!-- User Authentication end-->
        <!-- Vendor Registration Documents start -->
        <div class="col-md-4 mb-3">
            <div class="card-box pb-2 h-100">
                <div class="d-flex align-items-center justify-content-between">
                   <h4 class="header-title m-0">{{ __("Vendor Registration Documents") }}</h4>
                   <a class="btn btn-info d-block" id="add_vendor_registration_document_modal_btn">
                      <i class="mdi mdi-plus-circle mr-1"></i>{{ __("Add") }}
                   </a>
                </div>
                <div class="table-responsive mt-3 mb-1">
                   <table class="table table-centered table-nowrap table-striped" id="promo-datatable">
                      <thead>
                         <tr>
                            <th>{{ __("Name") }}</th>
                            <th>{{ __("Type") }}</th>
                            <th>{{ __("Is Required?") }}</th>
                            <th>{{ __("Action") }}</th>
                         </tr>
                      </thead>
                      <tbody id="post_list">
                         @forelse($vendor_registration_documents as $vendor_registration_document)
                         <tr>
                            <td>
                               <a class="edit_vendor_registration_document_btn" data-vendor_registration_document_id="{{$vendor_registration_document->id}}" href="javascript:void(0)">
                                  {{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}
                               </a>
                            </td>
                            <td>{{$vendor_registration_document->file_type}}</td>
                            <td>{{ ($vendor_registration_document->is_required == 1)?__('Yes'):__('No') }}</td>
                            <td>
                               <div>
                                  <div class="inner-div" style="float: left;">
                                     <a class="action-icon edit_vendor_registration_document_btn" data-vendor_registration_document_id="{{$vendor_registration_document->id}}" href="javascript:void(0)">
                                        <i class="mdi mdi-square-edit-outline"></i>
                                     </a>
                                  </div>
                                  <div class="inner-div">
                                     <button type="button" class="btn btn-primary-outline action-icon delete_vendor_registration_document_btn" data-vendor_registration_document_id="{{$vendor_registration_document->id}}">
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
                      </tbody>
                   </table>
                </div>
            </div>
        </div>
        <!-- Vendor Registration Documents end -->
        <!-- User Registration Documents start -->
        <div class="col-md-4 mb-3">
            <div class="card-box pb-2 h-100">
                <div class="d-flex align-items-center justify-content-between">
                <h4 class="header-title m-0">{{ __("User Registration Documents") }}</h4>
                <a class="btn btn-info d-block" id="add_user_registration_document_modal_btn">
                    <i class="mdi mdi-plus-circle mr-1"></i>{{ __("Add") }}
                </a>
                </div>
                <div class="table-responsive mt-3 mb-1">
                <table class="table table-centered table-nowrap table-striped" id="promo-datatable">
                    <thead>
                        <tr>
                            <th>{{ __("Name") }}</th>
                            <th>{{ __("Type") }}</th>
                            <th>{{ __("Is Required?") }}</th>
                            <th>{{ __("Action") }}</th>
                        </tr>
                    </thead>
                    <tbody id="post_list">
                        @forelse($user_registration_documents as $user_registration_document)
                        <tr>
                            <td>
                            <a class="edit_user_registration_document_btn" data-user_registration_document_id="{{$user_registration_document->id}}" href="javascript:void(0)">
                                {{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}
                            </a>
                            </td>
                            <td>{{$user_registration_document->file_type}}</td>
                            <td>{{ ($user_registration_document->is_required == 1)?__('Yes'):__('No') }}</td>
                            <td>
                            <div>
                                <div class="inner-div" style="float: left;">
                                    <a class="action-icon edit_user_registration_document_btn" data-user_registration_document_id="{{$user_registration_document->id}}" href="javascript:void(0)">
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                </div>
                                <div class="inner-div">
                                    <button type="button" class="btn btn-primary-outline action-icon delete_user_registration_document_btn" data-user_registration_document_id="{{$user_registration_document->id}}">
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
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <!-- USer Registration Documents end -->
        @if($client_preference_detail->category_kyc_documents == 1 )
        <div class="col-md-4">
        <!-- Category Kyc Documents start -->
            <div class="card-box pb-2">
                <div class="d-flex align-items-center justify-content-between">
                <h4 class="header-title m-0">{{ __("User Place Order Documents") }}</h4>
                <a class="btn btn-info d-block" id="add_category_kyc_document_modal_btn">
                    <i class="mdi mdi-plus-circle mr-1"></i>{{ __("Add") }}
                </a>
                </div>
                <div class="table-responsive mt-3 mb-1">
                <table class="table table-centered table-nowrap table-striped" id="promo-datatable">
                    <thead>
                        <tr>
                            <th>{{ __("Name") }}</th>
                            <th>{{ __("Type") }}</th>
                            <th>{{ __("Is Required?") }}</th>
                            <th>{{ __("Categories") }}</th>
                            <th>{{ __("Action") }}</th>
                        </tr>
                    </thead>
                    <tbody id="post_list">
                        @forelse($category_kyc_documents as $category_kyc_document)
                        <tr>
                            <td>
                            <a class="edit_user_registration_document_btn" data-user_registration_document_id="{{$category_kyc_document->id}}" href="javascript:void(0)">
                                {{$category_kyc_document->primary ? $category_kyc_document->primary->name : ''}}
                            </a>
                            </td>
                            <td>{{$category_kyc_document->file_type}}</td>
                            <td>{{ ($category_kyc_document->is_required == 1)?__('Yes'):__('No') }}</td>
                            <td>
                                @php
                                $category_other='0';
                                @endphp
                                @foreach($category_kyc_document->categoryMapping as $category_ones)
                                {{ ($category_other =='1') ? ", " : "" }}
                                {{ $category_ones->category->translation_one->name ?? '' }}
                                @php
                                $category_other='1';
                                @endphp
                                @endforeach
                            </td>
                            <td>
                            <div>
                                <div class="inner-div" style="float: left;">
                                    <a class="action-icon edit_category_kyc_document_btn" data-category_kyc_document_id="{{$category_kyc_document->id}}" href="javascript:void(0)">
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                </div>
                                <div class="inner-div">
                                    <button type="button" class="btn btn-primary-outline action-icon delete_category_kyc_document_btn" data-category_kyc_documents_id="{{$category_kyc_document->id}}">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                            </td>
                        </tr>
                        @empty
                        <tr align ="center">
                            <td colspan="5" style="padding: 20px 0">{{ __("Result not found.") }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <!-- Category Kyc Documents end -->
        @endif
   </div>
   <!--User onbarding end -->

    <!-- Miscellaneous Start  -->
    <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __("Miscellaneous") }}</h4>
         </div>
      </div>
    </div>
    <div class="row col-spacing">
        <!-- Order Email Notification start -->
        <div class="col-lg-3 col-lg-3 mb-3">
            <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <div class="card-box pb-1 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="header-title ">{{ __('Order Email Notification') }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <div class="col-xl-12 my-2 p-0" id="addCur-160">
                        <label class="primaryCurText">{{ __('Email') }}</label>
                        <input class="form-control" type="email" id="admin_email" name="admin_email" value="{{ old('admin_email', $preference->admin_email)}}">
                    </div>
                </div>
            </form>
        </div>
        <!-- Order Email Notification end -->
        <!-- Start Refer and earn -->
        <div class="col-lg-3 mb-3">
            <form method="POST" class="h-100" action="{{route('referandearn.update', Auth::user()->code)}}">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <div class="card-box mb-0 pb-1 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                    <h4 class="header-title">{{ __("Refer and Earn") }}</h4>
                    <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <div class="col-xl-12 my-2" id="addCur-160">
                    <label class="primaryCurText">{{ __("Referred To Amount") }} = </label>
                    <input class="form-control" type="number" id="reffered_to_amount" name="reffered_to_amount" value="{{ old('reffered_to_amount', decimal_format($reffer_to) ?? '')}}" min="0" step="any">
                    </div>
                    <div class="col-xl-12 mb-2 mt-3" id="addCur-160">
                    <label class="primaryCurText">{{ __("Referred By Amount") }} = </label>
                    <input class="form-control" type="number" name="reffered_by_amount" id="reffered_by_amount" value="{{ old('reffered_by_amount', decimal_format($reffer_by) ?? '')}}" min="0" step="any">
                    </div>
                </div>
            </form>
        </div>
        <!-- Start Google analytics -->

        <!-- @if(!$preference->client_preferences_additional->isEmpty())
            @foreach($preference->client_preferences_additional as $addiPreference)
                @if($addiPreference->key_name == 'gtag_id')
                    @php $gtag_id = $addiPreference->key_value; @endphp
                @endif
                @if($addiPreference->key_name == 'fpixel_id')
                    @php $fpixel_id = $addiPreference->key_value; @endphp
                @endif
            @endforeach
        @endif
        <div class="col-lg-3 col-lg-3 mb-3">
            <form method="POST" class="h-100" action="{{route('additional.update')}}">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <div class="card-box pb-1 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="header-title ">{{ __('Google Analytics') }}</h4>
                        <button class="btn btn-info d-block" name="gtag_submit" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <div class="col-xl-12 my-2 p-0" id="addCur-160">
                        <label class="primaryCurText">{{ __('GA Measurment Id') }}</label>
                            <input class="form-control" type="text" id="gtag_id" name="gtag_id" value="{{ old('gtag_id',  $getAdditionalPreference['gtag_id'] ?? "")}}">
                    </div>
                </div>
            </form>
        </div> -->
        <!-- Start Google analytics -->
        <!-- <div class="col-lg-3 col-lg-3 mb-3">
            <form method="POST" class="h-100" action="{{route('additional.update')}}">
                @csrf
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <div class="card-box pb-1 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="header-title ">{{ __('Facebook Pixel') }}</h4>
                        <button class="btn btn-info d-block" name="fpixel_submit" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <div class="col-xl-12 my-2 p-0" id="addCur-160">
                        <label class="primaryCurText">{{ __('Pixel Id') }}</label>
                            <input class="form-control" type="text" id="fpixel_id" name="fpixel_id" value="{{ old('fpixel_id',  $getAdditionalPreference['fpixel_id'] ?? "")}}">
                    </div>
                </div>
            </form>
        </div> -->

        {{-- Added By Ovi --}}
        <!-- <div class="col-xl-9 col-lg-12 mb-3">
            <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                @csrf
                <input type="hidden" name="slotting_and_scheduling" id="slotting_and_scheduling" value="1">
                <input type="hidden" name="send_to" id="send_to" value="customize">
                <div class="card-box mb-0 pb-1 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="header-title">{{ __("Slotting & Orders Scheduling") }} </h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 my-2">
                            <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                <label for="off_scheduling_at_cart" class="mr-2 mb-0">{{__('Disable Scheduling Orders')}}<small class="d-block pr-5">{{__('Disable Order Scheduling across the platform to limit only to Instant Orders.')}}</small></label>
                            <span> <input type="checkbox" data-plugin="switchery" name="off_scheduling_at_cart" id="off_scheduling_at_cart" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->off_scheduling_at_cart == '1')) checked='checked' @endif>
                            </span>
                            </div>
                        </div> -->

                        <!-- <div class="col-lg-6 my-2">
                            <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                <label for="delay_order" class="mr-2 mb-0">{{__('Delay Order')}}<small class="d-block pr-5">{{__('Option to add delay time per product separately for Dine In/ Delivery/ Takeaway to restrict order to scheduling only with added Delay.')}}</small></label>
                            <span> <input type="checkbox" data-plugin="switchery" name="delay_order" id="delay_order" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->delay_order == '1')) checked='checked' @endif>
                            </span>
                            </div>
                        </div> -->
                        <!-- <div class="col-lg-6 my-2" id="scheduling_with_slots_div" style="display:none;">
                            <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                <label for="scheduling_with_slots" class="mr-2 mb-0">{{__('Schedule Pickup & Dropoff With Slots')}}<small class="d-block pr-5">{{__('Enable or disable schedule pickup & dropoff with slots for laundry.')}}</small></label>
                            <span> <input type="checkbox" data-plugin="switchery" name="scheduling_with_slots" id="scheduling_with_slots" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->scheduling_with_slots == '1')) checked='checked' @endif>
                                </span>
                            </div>
                        </div> -->
                        <!-- <div class="col-lg-6 my-2" id="same_day_delivery_for_schedule_div" style="display:none;">
                            <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                <label for="same_day_delivery_for_schedule" class="mr-2 mb-0">{{__('Same Day Pickup & Delivery For Scheduling')}}<small class="d-block pr-5">{{__('Enable or disable same day pickup & delivery for scheduling.')}}</small></label>
                            <span> <input type="checkbox" data-plugin="switchery" name="same_day_delivery_for_schedule" id="same_day_delivery_for_schedule" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->same_day_delivery_for_schedule == '1')) checked='checked' @endif>
                                </span>
                            </div>
                        </div> -->
                        <!-- <div class="col-lg-6 my-2" id="same_day_orders_for_rescheduing_div" style="display:none;">
                            <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                <label for="same_day_delivery_for_schedule" class="mr-2 mb-0">{{__('Same Day Pickup & Delivery For Rescheduling')}}<small class="d-block pr-5">{{__('Enable or disable same day pickup & delivery for rescheduling.')}}</small></label>
                            <span> <input type="checkbox" data-plugin="switchery" name="same_day_orders_for_rescheduing" id="same_day_orders_for_rescheduing" class="form-control" data-color="#43bee1" @if((isset($preference) && $preference->same_day_orders_for_rescheduing == '1')) checked='checked' @endif>
                                </span>
                            </div>
                        </div> -->

                    <!-- </div>
                </div>
            </form>
        </div> -->
        {{-- Added By Ovi --}}
        <!-- End refer and earn -->


         {{-- Added By harbans --}}
         <!-- static_dropoff List -->
         @if($preference->is_static_dropoff == '1')
          <!-- static dropoff Location start -->
        <div class="col-md-6 mb-3">
            <div class="card-box pb-2 h-100">
                <div class="d-flex align-items-center justify-content-between">
                   <h4 class="header-title m-0">{{ __("Static Dropoff Locations") }}</h4>
                   <a class="btn btn-info d-block" id="add_static_dropoff_modal_btn">
                      <i class="mdi mdi-plus-circle mr-1"></i>{{ __("Add") }}
                   </a>
                </div>
                <div class="table-responsive mt-3 mb-1">
                   <table class="table table-centered  nowrap table-striped  w-100" id="static_dropoff_datatable">
                      <thead>
                         <tr>
                            <th width="20%">{{ __("Name") }}</th>
                            <th class="text-wrap" width="60%">{{ __("Address") }}</th>
                            <th width="20%">{{ __("Action") }}</th>
                         </tr>
                      </thead>
                      <!-- <tbody id="post_list">
                         @forelse($staticDropoff as $static_dropoff)
                         <tr>
                            <td>
                               <a class="edit_static_dropoff_btn" data-static_dropoff_id="{{$static_dropoff->id}}" href="javascript:void(0)">
                                  {{$static_dropoff->title }}
                               </a>
                            </td>
                            <td>{{$static_dropoff->address}}</td>
                            <td>
                               <div>
                                  <div class="inner-div" style="float: left;">
                                     <a class="action-icon edit_static_dropoff_btn" data-static_dropoff_id="{{$static_dropoff->id}}" href="javascript:void(0)">
                                        <i class="mdi mdi-square-edit-outline"></i>
                                     </a>
                                  </div>
                                  <div class="inner-div">
                                     <button type="button" class="btn btn-primary-outline action-icon delete_static_dropoff_btn" data-static_dropoff_id="{{$static_dropoff->id}}">
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
                      </tbody> -->
                   </table>
                </div>
            </div>
        </div>
        <!-- Vendor Registration Documents end -->
         @endif
         <!-- static_dropoff Ends -->


    </div>
    <!-- Miscellaneous End  -->


<!-- End New Customize page -->

    <div class="row mb-4">
             <!-- Order Email Notification start -->
             <div class="col-lg-4 col-xl-3 mb-3">
                <div class="col-12">
                    <div class="page-title-box">
                    <h4 class="page-title text-uppercase">{{ __("Policy") }}</h4>
                    </div>
                </div>
                 <form method="POST" class="h-100" action="{{route('configure.update', Auth::user()->code)}}">
                     @csrf
                     <input type="hidden" name="send_to" id="send_to" value="customize">
                     <div class="card-box pb-1 h-100">
                         <div class="d-flex align-items-center justify-content-between">
                             <h4 class="header-title ">{{ __('Cancellation Policy') }}</h4>
                             <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                         </div>
                         <div class="col-xl-12 my-2 p-0" id="addCur-160">
                             <label class="primaryCurText">{{ __('Free Cancellation Upto') }}</label>
                             <input class="form-control" type="number" min="0" id="order_cancellation_time" name="order_cancellation_time" value="{{ !empty($preference->order_cancellation_time)? $preference->order_cancellation_time : 0}}" step="0">
                             <!-- <select class="form-control al_box_height" id="order_cancellation_time" name="order_cancellation_time">
                                 <option value="0"  {{ (isset($preference->order_cancellation_time) && $preference->order_cancellation_time == 0)? 'selected' : '' }}>{{__('No Cancellation')}}</option>
                                 <option value="80" {{ (isset($preference->order_cancellation_time) && $preference->order_cancellation_time == 80)? 'selected' : '' }}>{{__('80 Minutes')}}</option>
                                 <option value="90" {{ (isset($preference->order_cancellation_time) && $preference->order_cancellation_time == 90)? 'selected' : '' }}>{{__('90 Minutes')}}</option>
                                 <option value="100" {{ (isset($preference->order_cancellation_time) && $preference->order_cancellation_time == 100)? 'selected' : '' }}>{{__('100 Minutes')}}</option>
                                 <option value="120" {{ (isset($preference->order_cancellation_time) && $preference->order_cancellation_time == 120)? 'selected' : '' }}>{{__('120 Minutes')}}</option>
                             </select> -->
                         </div>
                         <div class="col-xl-12 my-2 p-0" id="late-cancellation" style="{{ (isset($preference->order_cancellation_time) && $preference->order_cancellation_time > 0)? 'display:block;' : 'display:none;'}}">
                             <label class="primaryCurText">{{ __('Late Cancellation Fee') }}(%)</label>
                             <input class="form-control" type="number" min="0" id="cancellation_percentage" name="cancellation_percentage" value="{{ !empty($preference->cancellation_percentage)? $preference->cancellation_percentage : 20}}">
                         </div>
                     </div>
                 </form>
             </div>

            <div class="col-lg-4 col-xl-3 mb-3 d-none">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title text-uppercase">{{ __("Token") }}</h4>
                    </div>
                </div>
                <form method="POST" class="h-100" action="{{route('additional.update')}}">
                    @csrf
                    <input type="hidden" name="send_to" id="send_to" value="customize">
                    <div class="card-box pb-1 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="header-title ">{{ __('Token currency') }}</h4>
                            <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                        </div>
                        <div class="col-xl-12 my-2 p-0" id="">
                                        <!-- Token card start -->

                    <div class="row">
                       <div class="col-12">
                          <div class="form-group mb-0 switchery-demo">
                             <label for="" class="mr-3">{{ __("Enable") }}</label>
                             <input type="checkbox" data-plugin="switchery" id="is_token_currency_enable" class="form-control checkbox_change" data-className="is_token_currency_enable_hidden" data-color="#43bee1"
                             @if(@$getAdditionalPreference['is_token_currency_enable'] == '1') checked='checked' value="1"  @endif>
                             <input type="hidden"  @if(isset($getAdditionalPreference['is_token_currency_enable']) == 1) value="1" @else value="0" @endif name="is_token_currency_enable" id="is_token_currency_enable_hidden"/>
                          </div>
                       </div>
                    </div>

                    <div class="row token_row" style="{{((isset($getAdditionalPreference['is_token_currency_enable']) && $getAdditionalPreference['is_token_currency_enable'] == 1)) ? '' : 'display:none;'}}">
                       <div class="col-12">
                          <div class="form-group row mt-2 d-flex align-items-center">
                             <label class="col-3 m-0">1 {{$preference->primary->currency->iso_code}} {{!empty($preference->primary->currency->symbol) ? $preference->primary->currency->symbol : ''}} = </label>
                             <div class="col-9">
                                <input type="text" name="token_currency" id="token_currency" placeholder="" class="form-control" value="{{ old('token_currency',  $getAdditionalPreference['token_currency'] ?? '')}}">
                                @if($errors->has('token_client_id'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('token_access_token') }}</strong>
                                </span>
                                @endif
                            </div>
                          </div>
                       </div>
                    </div>
                <!-- Token card end -->



                        </div>
                    </div>
                </form>
            </div>

            {{-- mohit sir branch code added by sohail --}}
            {{--
            <div class="col-lg-4 col-xl-3 mb-3">
                <div class="col-12">
                    <div class="page-title-box">
                    <h4 class="page-title text-uppercase">{{ __("Advance Booking For Takeaway") }}</h4>
                    </div>
                </div>
                <form method="POST" action="{{route('additional.update')}}">
                    @csrf
                    <input type="hidden" name="send_to" id="send_to" value="customize">
                    <div class="card-box h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="header-title mb-0">{{ getDynamicTypeName('Advance Booking')  }}</h4>
                            <button class="btn btn-info d-block" type="submit" name="appointment_submit_btn" value ="1"> {{ __("Save") }} </button>
                        </div>
                        <!-- <p class="sub-header">{{ __("Get token amount before user place order.") }}</p> -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <div class="form-group mb-0 switchery-demo">
                                        <label for="need_appointment_service" class="mr-3">{{ __("Enable") }}</label>
                                        <input type="checkbox" data-plugin="switchery" id="advance_booking_amount" class="form-control checkbox_change" data-className="advance_booking_amount_hidden" data-color="#43bee1" @if(@$getAdditionalPreference['advance_booking_amount'] == '1') checked='checked' value="1"  @endif>
                                        <input type="hidden"  @if(isset($getAdditionalPreference['advance_booking_amount']) == 1) value="1" @else value="0" @endif  name="advance_booking_amount"  id="advance_booking_amount_hidden"/>
                                    </div>
                                </div>
                                <div class="form-group mt-3 advance_booking_amount_row" style="{{((isset($getAdditionalPreference['advance_booking_amount']) && $getAdditionalPreference['advance_booking_amount'] == 1)) ? '' : 'display:none;'}}">
                                    <label for="fb_client_id">{{ __("Advance Booking Amount %") }}</label>
                                    <input type="number" min="1" max="100" required name="advance_booking_amount_percentage" id="advance_booking_amount_percentage" placeholder="" class="form-control" value="{{ old('advance_booking_amount_percentage',  $getAdditionalPreference['advance_booking_amount_percentage'] ?? '')}}">
                                    @if($errors->has('advance_booking_amount_percentage'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('advance_booking_amount_percentage') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            --}}
            {{-- till here --}}

            {{-- Roles Enable setting for price, that is, is_enable_pricing (START) --}}
            @if (isset($getAdditionalPreference['is_price_by_role']))
                @if($getAdditionalPreference['is_price_by_role'] == '1')
                    <div class="col-lg-4 col-xl-3 mb-3">
                        <div class="col-12">
                            <div class="page-title-box">
                            <h4 class="page-title text-uppercase">{{ __("Role") }}</h4>
                            </div>
                        </div>
                        <form method="POST" class="h-100" action="{{route('customize.updateIsPriceEnable')}}">
                            @csrf
                            <div class="card-box pb-1 h-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h4 class="header-title ">{{ __('Enable price based on Roles') }}</h4>
                                    <button class="btn btn-info d-block" type="submit"> {{ __("Save") }} </button>
                                </div>
                                <div class="col-xl-12 my-2 p-0" id="">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0 switchery-demo">
                                                @if (isset($roles))
                                                    @foreach ($roles as $key => $_role)

                                                        <input type="text" name="role[{{ $_role['id'] }}]" id="role{{ $_role['id'] }}" value="{{ $_role['role'] }}">
                                                        <input type="hidden" name="role_id[{{ $_role['id'] }}]" value="{{ $_role['id'] }}">

                                                        <input type="checkbox"  name="is_enable_pricing[{{ $_role['id'] }}]" data-plugin="switchery" id="is_enable_pricing" class="form-control checkbox_change" data-className="is_enable_pricing_hidden" data-color="#43bee1"
                                                        @if(@$_role['is_enable_pricing'] == '1') checked='checked' value="1"  @endif data-role-id={{$_role['id']}}>
                                                        <br>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            @endif
            {{-- Roles Enable setting for price, that is, is_enable_pricing (END) --}}
     </div>

<div id="add_or_edit_social_media_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title" id="standard-modalLabel">{{ __("Add Social Media") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div id="save_social_media">
                    <input type="hidden" name="social_media_id" value="">
                    <div class="form-group position-relative">
                        <label for="">{{ __("Icon") }}</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fab fa-facebook"></i></div>
                            </div>
                            <select class="form-control al_box_height" id="social_icons" name="icon">
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
                            </select>
                        </div>
                    </div>
                    <div class="form-group position-relative">
                        <label for="">{{ __("URL") }}</label>
                        <input class="form-control al_box_height" name="url" type="text" placeholder="http://www.google.com">
                        <span class="text-danger error-text social_media_url_err"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary submitSaveSocialForm">{{ __("Save") }}</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Vendor Registration Document Modal -->
<div id="add_user_registration_document_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  standard-modalLabel aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom al">
               <h4 class="modal-title" id="standard-modalLabel">{{ __("Add Vendor Registration Document") }}</h4>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
               <form id="userRegistrationDocumentForm" method="POST" action="javascript:void(0)">
                  @csrf
                  <div id="save_social_media">
                     <input type="hidden" name="user_registration_document_id" value="">
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group position-relative">
                              <label for="">Type</label>
                              <div class="input-group mb-2">
                                 <select class="form-control" name="file_type" id="user_file_type_select">
                                    <option value="Text">Text</option>
                                    <option value="Image">Image</option>
                                    <option value="Pdf">PDF</option>
                                    <option value="selector">selector</option>
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group position-relative">
                              <label for="">{{__('Is Required?')}}</label>
                              <div class="input-group mb-2">
                                 <select class="form-control" name="is_required">
                                    <option value="1">{{__('Yes')}}</option>
                                    <option value="0">{{__('No')}}</option>
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 selector-option-al ">
                            <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                                <tr class="trForClone">

                                    @foreach($client_languages as $langs)
                                        <th>{{$langs->langName}}</th>
                                    @endforeach
                                    <th></th>
                                </tr>
                                <tbody id="table_body">
                                        <tr>
                                    @foreach($client_languages as $lankey => $User_langs)
                                        <td>
                                            <input class="form-control" name="language_id[{{$lankey}}]" type="hidden" value="{{$User_langs->langId}}">
                                            <input class="form-control" name="name[{{$lankey}}]" type="text" id="user_registration_document_name_{{$User_langs->langId}}">
                                        </td>
                                    @endforeach
                                    <td class="lasttd"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="user_selector_div" class="col-md-12 d-none">
                            <div class="card">
                            <div class="card-box mb-0 ">
                                <div class="d-flex align-items-center justify-content-between">
                                   <h4 class="header-title text-uppercase">{{__('Options')}}</h4>

                                </div>
                                <div id="option_div">

                                        <div class="selector-option-al ">
                                            <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="vendor-selector-datatable">
                                                <tr class="trForClone">

                                                    @foreach($client_languages as $langs)
                                                        <th>{{$langs->langName}}</th>
                                                    @endforeach
                                                    <th></th>
                                                </tr>
                                                <tbody id="table_body">
                                                </tbody>
                                            </table>
                                        </div>
                                </div>
                            </div>
                            </div>
                        </div>

                     </div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-primary submitSaveUserRegistrationDocument">{{ __("Save") }}</button>
            </div>
         </div>
      </div>
   </div>


<!-- Add Vendor Registration Document Modal -->
<div id="add_vendor_registration_document_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  standard-modalLabel aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom al">
               <h4 class="modal-title" id="standard-modalLabel">{{ __("Add Vendor Registration Document") }}</h4>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
               <form id="vendorRegistrationDocumentForm" method="POST" action="javascript:void(0)">
                  @csrf
                  <div id="save_social_media">
                     <input type="hidden" name="vendor_registration_document_id" value="">
                     <div class="row">
                        <div class="col-md-4">
                           <div class="form-group position-relative">
                              <label for="">Type</label>
                              <div class="input-group mb-2">
                                 <select class="form-control" name="file_type" id="file_type_select">
                                    <option value="Text">Text</option>
                                    <option value="Image">Image</option>
                                    <option value="Pdf">PDF</option>
                                    <option value="selector">Selector</option>
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group position-relative">
                              <label for="">{{ __('Is Required?') }}</label>
                              <div class="input-group mb-2">
                                 <select class="form-control" name="is_required">
                                    <option value="1">{{__('Yes')}}</option>
                                    <option value="0">{{__('No')}}</option>
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group position-relative">
                               <label for="">Need Expiration Date</label>
                               <div class="input-group mb-2">
                                <select class="form-control" name="need_expiration_date">
                                   <option value="1">{{__('Yes')}}</option>
                                   <option value="0">{{__('No')}}</option>
                                </select>
                             </div>
                            </div>
                         </div>
                        <div class="col-md-12 selector-option-al ">
                            <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                                <tr class="trForClone">

                                    @foreach($client_languages as $langs)
                                        <th>{{$langs->langName}}</th>
                                    @endforeach
                                    <th></th>
                                </tr>
                                <tbody id="table_body">
                                        <tr>
                                    @foreach($client_languages as $key => $vendor_langs)

                                        <td>
                                            <input class="form-control" name="language_id[{{$key}}]" type="hidden" value="{{$vendor_langs->langId}}">
                                            <input class="form-control" name="name[{{$key}}]" type="text" id="vendor_registration_document_name_{{$vendor_langs->langId}}">
                                        </td>
                                    @endforeach
                                    <td class="lasttd"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- @forelse($client_languages as $k => $client_language)
                        <div class="col-md-6 mb-2">
                           <div class="row">
                              <div class="col-12">
                                 <div class="form-group position-relative">
                                    <label for="">{{ __("Name") }} ({{$client_language->langName}})</label>
                                    <input class="form-control" name="language_id[{{$k}}]" type="hidden" value="{{$client_language->langId}}">
                                    <input class="form-control" name="name[{{$k}}]" type="text" id="vendor_registration_document_name_{{$client_language->langId}}">
                                 </div>
                                 @if($k == 0)
                                    <span class="text-danger error-text social_media_url_err"></span>
                                 @endif
                              </div>
                           </div>
                        </div>
                        @empty
                        @endforelse -->
                        <div id="selector_div" class="col-md-12 d-none">
                            <div class="card">
                            <div class="card-box mb-0 ">
                                <div class="d-flex align-items-center justify-content-between">
                                   <h4 class="header-title text-uppercase">{{__('Options')}}</h4>
                                   {{-- <div class="col-md-4 col-xl-4 mb-2 ">
                                        <div class="form-group mb-0">
                                            <select class="form-control" name="option_language_id" id="option_client_language">
                                            @foreach($client_languages as $client_language)
                                                <option value="{{$client_language->langId}}">{{$client_language->langName}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div> --}}
                                </div>
                                <div id="option_div">

                                        <div class="selector-option-al ">
                                            <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="vendor-selector-datatable">
                                                <tr class="trForClone">

                                                    @foreach($client_languages as $langs)
                                                        <th>{{$langs->langName}}</th>
                                                    @endforeach
                                                    <th></th>
                                                </tr>
                                                <tbody id="table_body">
                                                    {{-- <tr>
                                                    @foreach($client_languages as $key => $langs)
                                                        <td>
                                                            {{-- <input type="hidden" name="option_language_id[]"  value="{{$langs->langId}}" class="form-control">
                                                            <input type="hidden" name="option_id[{{$key}}][]"   class="form-control" >
                                                            <input type="text" name="option_names[{{$key}}][]" class="form-control" @if($langs->is_primary == 1) required @endif>
                                                        </td>
                                                        @endforeach
                                                        <td class="lasttd"></td>
                                                    </tr> --}}
                                                </tbody>
                                            </table>
                                        </div>
                                </div>
                            </div>
                            </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-primary submitSaveVendorRegistrationDocument">{{ __("Save") }}</button>
            </div>
         </div>
      </div>
   </div>
<!--End Add Vendor Registration Document Modal -->

<!-- Add Vendor Registration Document Modal -->
<div id="add_static_dropoff_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  standard-modalLabel aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom al">
               <h4 class="modal-title" id="standard-modalLabel">{{ __("Add Vendor Registration Document") }}</h4>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
               <form id="staticDropoffForm" method="POST" action="javascript:void(0)">
                  @csrf
                  <div id="save_social_media">
                     <input type="hidden" name="static_address_id" id="static_address_id" value="">
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group position-relative">
                              {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
                              <input type="text" name="location_title" id="location_title" placeholder="" class="form-control" >
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group position-relative">
                           {!! Form::label('title', __('Address'),['class' => 'control-label']) !!}
                           <div class="input-group">
                                <input type="text" name="static_address" id="static-address" onkeyup="checkAddressString(this,'static')" placeholder="" class="form-control">
                                <div class="input-group-append">
                                    <button class="btn btn-xs btn-dark waves-effect waves-light showMap" type="button" num="add"> <i class="mdi mdi-map-marker-radius"></i></button>
                                </div>
                            </div>
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>

                           </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3" id="latitudeInput">
                                {!! Form::label('title', __('Latitude'),['class' => 'control-label']) !!}
                                <input type="text" name="static_latitude" id="static_latitude" placeholder="" class="form-control" value="">
                                @if($errors->has('static_latitude'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('static_latitude') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3" id="longitudeInput">
                                {!! Form::label('title', __('Longitude'),['class' => 'control-label']) !!}
                                <input type="text" name="static_longitude" id="static_longitude" placeholder="" class="form-control" value="">
                                @if($errors->has('static_longitude'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('static_longitude') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-3" id="longitudeInput">
                                {!! Form::label('title', __('Place id'),['class' => 'control-label']) !!}
                                <input type="text" name="static_place_id" id="static_place_id" placeholder="" class="form-control" value="">
                                @if($errors->has('static_place_id'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('static_place_id') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                     </div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-primary submitSaveStaticDropoff">{{ __("Save") }}</button>
            </div>
         </div>
      </div>
   </div>
<!--End Add Vendor Registration Document Modal -->
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
                            <input type="hidden" name="place_id" id="place_id" value="" />
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

<!--End Add facilty Modal -->
<div id="add_facilty_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  standard-modalLabel aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom al">
               <h4 class="modal-title" id="standard-modalLabel">{{ __("Add facilty") }}</h4>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
               <form id="faciltyForm" method="POST" action="javascript:void(0)">
                  @csrf
                  <div id="save_social_media">
                     <input type="hidden" name="facilty_id" value="">
                     <div class="row">

                        <div class="col-md-6">
                                <label>{{ __('Upload Logo') }} </label>
                                <input type="file" accept="image/*" data-plugins="dropify" name="facilty_image" class="dropify" data-default-file="" />
                                <label class="logo-size text-right w-100">{{ __('Logo Size') }} 170x96</label>
                        </div>
                        <div class="col-md-12 selector-option-al ">
                            <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                                <tr class="trForClone">

                                    @foreach($client_languages as $langs)
                                        <th>{{$langs->langName}}</th>
                                    @endforeach
                                    <th></th>
                                </tr>
                                <tbody id="table_body">
                                        <tr>
                                    @foreach($client_languages as $lankey => $User_langs)
                                        <td>
                                            <input class="form-control" name="language_id[{{$lankey}}]" type="hidden" value="{{$User_langs->langId}}">
                                            <input class="form-control" name="name[{{$lankey}}]" type="text" id="facilty_name_{{$User_langs->langId}}">
                                        </td>
                                    @endforeach
                                    <td class="lasttd"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                     </div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-primary submitSaveFacilty">{{ __("Save") }}</button>
            </div>
         </div>
      </div>
   </div>
</div>

   <!-- Add category kyc Document Modal -->
<div id="add_category_kyc_document_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  standard-modalLabel aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom al">
               <h4 class="modal-title" id="standard-modalLabel">{{ __("Add User Place Order Document") }}</h4>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
               <form id="CategoryKycDocumentForm" method="POST" action="javascript:void(0)">
                  @csrf
                  <div id="save_social_media">
                     <input type="hidden" name="category_kyc_document_id" value="">
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group position-relative">
                              <label for="">Type</label>
                              <div class="input-group mb-2">
                                 <select class="form-control" name="file_type" id="category_kyc_file_type_select">
                                    <option value="Image">Image</option>
                                    <option value="Pdf">PDF</option>
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group position-relative">
                              <label for="">{{ __('Is Required?') }}</label>
                              <div class="input-group mb-2">
                                 <select class="form-control" name="is_required">
                                    <option value="1">{{__('Yes')}}</option>
                                    <option value="0">{{__('No')}}</option>
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group position-relative">
                              <label for="">{{('Category')}}</label>
                              <div class="input-group mb-2">
                                 <select class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ..." id="category_list" name="category_id[]">

                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 selector-option-al ">
                            <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                                <tr class="trForClone">

                                    @foreach($client_languages as $langs)
                                        <th>{{$langs->langName}}</th>
                                    @endforeach
                                    <th></th>
                                </tr>
                                <tbody id="table_body">
                                        <tr>
                                    @foreach($client_languages as $category_lankey => $category_kyc_langs)
                                        <td>
                                            <input class="form-control" name="language_id[{{$category_lankey}}]" type="hidden" value="{{$category_kyc_langs->langId}}">
                                            <input class="form-control" name="name[{{$category_lankey}}]" type="text" id="category_kyc_document_name_{{$category_kyc_langs->langId}}">
                                        </td>
                                    @endforeach
                                    <td class="lasttd"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                     </div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-primary submitcategoryKycDocument">{{ __("Save") }}</button>
            </div>
         </div>
      </div>
   </div>



   <!-- end product tags -->
   <script type="text/template" id="vendorSelectorTemp">
        <tr class ="option_section" id ="option_section_<%= id %>" data-section_number="<%= id %>">
        <input type="hidden" name="option_id[<%= id-1 %>][]"  id="option_id<%= id %>" data-id ="<%= id %>" value ="<%= data?data.id:'' %>">
        @foreach($client_languages as $key => $langs)
        <td>
            <div class="form-group mb-0">
                <input type="hidden" name="option_lang_id[<%= id-1 %>][]"   value ="{{$langs->langId}}">
                <input type="text" name="option_name[<%= id-1 %>][]" class="form-control" @if($langs->is_primary == 1) required @endif   id="option_name_<%= id-1 %>_{{$langs->langId}}" placeholder="" data-id ="<%= id %>" value ="<%= data?(data.translations?data.translations.name:''):'' %>">
            </div>
        </td>

        @endforeach
        <td class="lasttd d-flex align-items-center justify-content-center">
            <% if(id > 1) { %>
                <a href="javascript:void(0)" class="action-icon remove_more_button"  id ="remove_button_<%= id %>" data-id ="<%= id %>"> <i class="mdi mdi-delete"></i></a>
            <% } %>
            <a href="javascript:void(0)" class="add_more_button" id ="add_button_<%= id %>" data-id ="<%= id %>"><i class="fa fa-plus-circle" aria-hidden="true"></i></a>

        </td>

    </tr>


</script>
@endsection
@section('script')
<script src="{{asset('assets/js/jscolor.js')}}"></script>
<script src="{{ asset('assets\js\backend\backend_common.js') }}"></script>
<script src="https://itsjavi.com/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js"></script>
<script type="text/javascript">

$(document).ready(function(){
    if($('#off_scheduling_at_cart').is(':checked') != true){
        $('#scheduling_with_slots_div').show();
    }
    if($('#scheduling_with_slots_div').is(':checked') != true){
        $('#same_day_delivery_for_schedule_div').show();
        $('#same_day_orders_for_rescheduing_div').show();
    }

    $('#off_scheduling_at_cart').on('change', function() {
        if($('#off_scheduling_at_cart').is(':checked')){
            $('#scheduling_with_slots_div').hide();
            $('#same_day_delivery_for_schedule_div').hide();
            $('#same_day_orders_for_rescheduing_div').hide();
        }else{
            $('#scheduling_with_slots_div').show();
            $('#same_day_delivery_for_schedule_div').show();
            $('#same_day_orders_for_rescheduing_div').show();
        }
    });
});

    var advance_booking_amount = $('#advance_booking_amount');
    if(advance_booking_amount.length > 0){
        advance_booking_amount[0].onchange = function() {
        if ($('#advance_booking_amount:checked').length != 1) {
            $('.advance_booking_amount_row').hide();
        } else {
            $('.advance_booking_amount_row').show();
        }
        }
    }

    $('#social_icons').on('change', function() {
        $(".input-group-text").html('<i class="fab fa-'+this.value+'"></i>');
    });

    // Vendor Registration Document Script
    $(document).on("change", "#file_type_select", function() {
        var file_type = $(this).val();
        if(file_type == 'selector'){
            $("#selector_div").removeClass("d-none");
            var classoption_section = $('#option_div').find('.option_section');
            if(classoption_section.length==0){
                addoptionTemplate(0);
            }
        }
        else{
            $("#selector_div").addClass("d-none");
        }
    });

      // User Registration Document Script
      $(document).on("change", "#user_file_type_select", function() {
        var file_type = $(this).val();
        if(file_type == 'selector'){
            $("#user_selector_div").removeClass("d-none");
            var classoption_section = $('#option_div').find('.option_section');
            if(classoption_section.length==0){
                addoptionTemplate(0);
            }
        }
        else{
            $("#user_selector_div").addClass("d-none");
        }
    });

    function addoptionTemplate(section_id){
        section_id                = parseInt(section_id);
        section_id                = section_id +1;
        var data                  = '';

        var price_section_temp    = $('#vendorSelectorTemp').html();
        var modified_temp         = _.template(price_section_temp);
        var result_html           = modified_temp({id:section_id,data:data});
        $("#vendor-selector-datatable #table_body").append(result_html);
        $('.add_more_button').hide();
        $('#vendor-selector-datatable #add_button_'+section_id).show();
    }
     $(document).on('click','.add_more_button',function(){
        var main_id = $(this).data('id');
        addoptionTemplate(main_id);
    });
    $(document).on('click','.remove_more_button',function(){
        var main_id =$(this).data('id');
        removeSeletOptionSectionTemplate(main_id);
        $('.add_more_button').each(function(key,value){
            if(key == ($('.add_more_button').length-1)){
                $('#add_button_'+$(this).data('id')).show();
            }
        });
    });
    function removeSeletOptionSectionTemplate(div_id){
        $('#option_section_'+div_id).remove();
    }
    $('#add_vendor_registration_document_modal_btn').click(function(e) {
        document.getElementById("vendorRegistrationDocumentForm").reset();
        $('#add_vendor_registration_document_modal input[name=vendor_registration_document_id]').val("");
        $('#add_vendor_registration_document_modal').modal('show');
        $('#add_vendor_registration_document_modal #standard-modalLabel').html('Add Vendor Registration Document');
    });


    $(document).on('click', '.submitSaveUserRegistrationDocument', function(e) {
        // alert('af');
        // return false;
        var user_registration_document_id = $("#add_user_registration_document_modal input[name=user_registration_document_id]").val();

        if (user_registration_document_id) {
            var post_url = "{{ route('user.registration.document.update') }}";
        } else {
            var post_url = "{{ route('user.registration.document.create') }}";
        }
        var form_data = new FormData(document.getElementById("userRegistrationDocumentForm"));
        $.ajax({
            url: post_url,
            method: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
               if (response.status == 'Success') {
                  $('#add_or_edit_social_media_modal').modal('hide');
                  $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                     location.reload()
                  }, 2000);
               } else {
                  $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
               $('#add_vendor_registration_document_modal .social_media_url_err').html('The default language name field is required.');
            }
        });
    });
    $(document).on("click", ".edit_user_registration_document_btn", function() {
        let user_registration_document_id = $(this).data('user_registration_document_id');
        editUserRegistrationForm(user_registration_document_id);
    });
    $(document).on("change", "#order_cancellation_time", function() {
        $("#late-cancellation").css("display", "none");
        if($(this).val() > 0){
            $("#late-cancellation").css("display", "block");
        }
    });
    function editUserRegistrationForm(user_registration_document_id){
        let language_id = $('#option_client_language').val();
         $('#add_user_registration_document_modal input[name=user_registration_document_id]').val(user_registration_document_id);
         $.ajax({
            method: 'GET',
            data: {
               user_registration_document_id: user_registration_document_id,
               language_id:language_id
            },
            url: "{{ route('user.registration.document.edit') }}",
            success: function(response) {
               if (response.status = 'Success') {

                if(response.data.file_type=="selector"){
                        $("#selector_div").removeClass("d-none");
                        $('.option_section').remove();
                        var options = response.data.options;
                        var section_id =0
                        var row =0
                        var option_section_temp    = $('#vendorSelectorTemp').html();
                        var modified_temp         = _.template(option_section_temp);
                        $(options).each(function(index, value) {
                            section_id                = parseInt(section_id);
                            row                       = parseInt(section_id)
                            section_id                = section_id +1;
                            $('#vendor-selector-datatable #table_body').append(modified_temp({ id:section_id,data:value}));
                            var options_trans = value.translations;
                            $(options_trans).each(function(trans_index, trans_value) {
                                var input_id = '#option_name_'+row+'_'+trans_value.language_id;
                                $(input_id).val(trans_value.name);
                            });
                            $('.add_more_button').hide();
                            $('#vendor-selector-datatable #add_button_'+section_id).show();
                        });
                    }else{
                        $('.option_section').remove();
                        $("#selector_div").addClass("d-none");
                    }


                  $(document).find("#add_user_registration_document_modal select[name=file_type]").val(response.data.file_type).change();

                  $("#add_user_registration_document_modal input[name=user_registration_document_id]").val(response.data.id);
                  $(document).find("#add_user_registration_document_modal select[name=is_required]").val(response.data.is_required).change();
                  $('#add_user_registration_document_modal #standard-modalLabel').html('Update User Registration Document');
                  $('#add_user_registration_document_modal').modal('show');
                  $.each(response.data.translations, function( index, value ) {
                    $('#add_user_registration_document_modal #user_registration_document_name_'+value.language_id).val(value.name);
                  });

                  $.each(response.data.options, function( index, value ) {
                    $.each(value.translations, function( index1, value1 ) {
                        $('#add_user_registration_document_modal #option_name_'+index+'_'+value1.language_id).val(value1.name);
                     });
                    });
               }
            },
            error: function() {}
        });
    }
    $(document).on("click", ".delete_user_registration_document_btn", function() {
         var user_registration_document_id = $(this).data('user_registration_document_id');
         Swal.fire({
            title: "{{__('Are you Sure?')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
         }).then((result) => {
            if (result.value) {
               $.ajax({
                  type: "POST",
                  dataType: 'json',
                  url: "{{ route('user.registration.document.delete') }}",
                  data: {
                     _token: "{{ csrf_token() }}",
                     user_registration_document_id: user_registration_document_id
                  },
                  success: function(response) {
                     if (response.status == "Success") {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                        setTimeout(function() {
                           location.reload()
                        }, 2000);
                     }
                  }
               });
            }
        });
    });


    //category kyc document model
    $('#add_category_kyc_document_modal_btn').click(function(e) {
        document.getElementById("CategoryKycDocumentForm").reset();
        $('#add_category_kyc_document_modal_btn input[name=category_kyc_document_id]').val("");
        $.ajax({
                type: "get",

                url: "{{route('categorykyc.getCategory')}}",
                success: function(response) {
                    console.log(response);
                    if(response.status == 1){
                        $('#category_list').selectize()[0].selectize.destroy();
                        $("#category_list").find('option').remove();
                        $("#category_list").append(response.options);
                    }
                },
                error:function(error){

                }
            });
        $('#add_category_kyc_document_modal').modal('show');
        $('#add_category_kyc_document_modal #standard-modalLabel').html('{{__("Add User Place Order Document")}}');
    });

     //category kyc form submit document
    $(document).on('click', '.submitcategoryKycDocument', function(e) {
        var category_kyc_document_id = $("#add_category_kyc_document_modal input[name=category_kyc_document_id]").val();
        if (category_kyc_document_id) {
            var post_url = "{{ route('categorykyc.document.update') }}";
        } else {
            var post_url = "{{ route('categorykyc.document.create') }}";
        }
        var form_data = new FormData(document.getElementById("CategoryKycDocumentForm"));
        $.ajax({
            url: post_url,
            method: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log(response);
               if (response.status == 'Success') {
                  $('#add_category_kyc_document_modal').modal('hide');
                  $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                     location.reload()
                  }, 2000);
               } else {
                  $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
               $('#add_category_kyc_document_modal .social_media_url_err').html('The default language name field is required.');
            }
        });
    });

    //
    $(document).on("click", ".edit_category_kyc_document_btn", function() {
        let category_kyc_document_id = $(this).data('category_kyc_document_id');
        edikYCForm(category_kyc_document_id);
    });

    function edikYCForm(category_kyc_document_id){
        let language_id = $('#option_client_language').val();
         $('#add_category_kyc_document_modal input[name=category_kyc_document_id]').val(category_kyc_document_id);
         $.ajax({
            method: 'GET',
            data: {
                category_kyc_document_id: category_kyc_document_id,
                language_id:language_id
            },
            url: "{{ route('categorykyc.document.edit') }}",
            success: function(response) {
               if (response.status = 'Success') {
                $.ajax({
                    type: "get",
                    data: {category_kyc_document_id:category_kyc_document_id },
                    url: "{{route('categorykyc.getCategory')}}",
                    success: function(response) {
                        console.log(response);
                        if(response.status == 1){
                            $('#category_list').selectize()[0].selectize.destroy();
                            $("#category_list").find('option').remove();
                            $("#category_list").append(response.options);
                        }
                    },
                    error:function(error){

                    }
                });
                  $(document).find("#add_category_kyc_document_modal select[name=file_type]").val(response.data.file_type).change();

                  $("#add_category_kyc_document_modal input[name=vendor_registration_document_id]").val(response.data.id);
                  $(document).find("#add_category_kyc_document_modal select[name=is_required]").val(response.data.is_required).change();
                  $('#add_category_kyc_document_modal #standard-modalLabel').html('{{__("Update User Place Order Documents")}}');
                  $('#add_category_kyc_document_modal').modal('show');
                  $.each(response.data.translations, function( index, value ) {
                    $('#add_category_kyc_document_modal #category_kyc_document_name_'+value.language_id).val(value.name);
                  });
               }
            },
            error: function() {}
        });
    }
    // delete kyc document
    $(document).on("click", ".delete_category_kyc_document_btn", function() {
         var category_kyc_document_id = $(this).data('category_kyc_documents_id');
         Swal.fire({
            title: "{{__('Are you Sure?')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
         }).then((result) => {
            if (result.value) {
               $.ajax({
                  type: "POST",
                  dataType: 'json',
                  url: "{{ route('categorykyc.document.delete') }}",
                  data: {
                     _token: "{{ csrf_token() }}",
                     category_kyc_document_id: category_kyc_document_id
                  },
                  success: function(response) {
                     if (response.status == "Success") {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                        setTimeout(function() {
                           location.reload()
                        }, 2000);
                     }
                  }
               });
            }
        });
    });

    //vendor registration document
    $(document).on('click', '.submitSaveVendorRegistrationDocument', function(e) {
        var vendor_registration_document_id = $("#add_vendor_registration_document_modal input[name=vendor_registration_document_id]").val();
        if (vendor_registration_document_id) {
            var post_url = "{{ route('vendor.registration.document.update') }}";
        } else {
            var post_url = "{{ route('vendor.registration.document.create') }}";
        }
        var form_data = new FormData(document.getElementById("vendorRegistrationDocumentForm"));
        $.ajax({
            url: post_url,
            method: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
               if (response.status == 'Success') {
                  $('#add_or_edit_social_media_modal').modal('hide');
                  $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                     location.reload()
                  }, 2000);
               } else {
                  $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
               $('#add_vendor_registration_document_modal .social_media_url_err').html('The default language name field is required.');
            }
        });
    });
    $(document).on("click", ".edit_vendor_registration_document_btn", function() {
        let vendor_registration_document_id = $(this).data('vendor_registration_document_id');
        editVendorRegistrationForm(vendor_registration_document_id);
    });
    function editVendorRegistrationForm(vendor_registration_document_id){
        let language_id = $('#option_client_language').val();
         $('#add_vendor_registration_document_modal input[name=vendor_registration_document_id]').val(vendor_registration_document_id);
         $.ajax({
            method: 'GET',
            data: {
               vendor_registration_document_id: vendor_registration_document_id,
               language_id:language_id
            },
            url: "{{ route('vendor.registration.document.edit') }}",
            success: function(response) {
               if (response.status = 'Success') {
                    if(response.data.file_type=="selector"){
                        $("#selector_div").removeClass("d-none");
                        $('.option_section').remove();
                        var options = response.data.options;
                        var section_id =0
                        var row =0
                        var option_section_temp    = $('#vendorSelectorTemp').html();
                        var modified_temp         = _.template(option_section_temp);
                        $(options).each(function(index, value) {
                            section_id                = parseInt(section_id);
                            row                       = parseInt(section_id)
                            section_id                = section_id +1;
                            $('#vendor-selector-datatable #table_body').append(modified_temp({ id:section_id,data:value}));
                            var options_trans = value.translations;
                            $(options_trans).each(function(trans_index, trans_value) {
                                var input_id = '#option_name_'+row+'_'+trans_value.language_id;
                                $(input_id).val(trans_value.name);
                            });
                            $('.add_more_button').hide();
                            $('#vendor-selector-datatable #add_button_'+section_id).show();
                        });
                    }else{
                        $('.option_section').remove();
                        $("#selector_div").addClass("d-none");
                    }
                  $(document).find("#add_vendor_registration_document_modal select[name=file_type]").val(response.data.file_type).change();

                  $("#add_vendor_registration_document_modal input[name=vendor_registration_document_id]").val(response.data.id);
                  $(document).find("#add_vendor_registration_document_modal select[name=is_required]").val(response.data.is_required).change();
                  $('#add_vendor_registration_document_modal #standard-modalLabel').html('Update Vendor Registration Document');
                  $('#add_vendor_registration_document_modal').modal('show');
                  $.each(response.data.translations, function( index, value ) {
                    $('#add_vendor_registration_document_modal #vendor_registration_document_name_'+value.language_id).val(value.name);
                  });
                  $.each(response.data.options, function( index, value ) {
                    $.each(value.translations, function( index1, value1 ) {
                        $('#add_vendor_registration_document_modal #option_name_'+index+'_'+value1.language_id).val(value1.name);
                     });
                    });

               }
            },
            error: function() {}
        });
    }
    $(document).on("click", ".delete_vendor_registration_document_btn", function() {
         var vendor_registration_document_id = $(this).data('vendor_registration_document_id');
         Swal.fire({
            title: "{{__('Are you Sure?')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
         }).then((result) => {
            if (result.value) {
               $.ajax({
                  type: "POST",
                  dataType: 'json',
                  url: "{{ route('vendor.registration.document.delete') }}",
                  data: {
                     _token: "{{ csrf_token() }}",
                     vendor_registration_document_id: vendor_registration_document_id
                  },
                  success: function(response) {
                     if (response.status == "Success") {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                        setTimeout(function() {
                           location.reload()
                        }, 2000);
                     }
                  }
               });
            }
        });
    });

    //user document model
    $('#add_facilties_modal_btn').click(function(e) {
        document.getElementById("userRegistrationDocumentForm").reset();
        $('#faciltyForm input[name=facilty_id]').val("");
        $('#add_facilty_modal').modal('show');
        $('#add_facilty_modal #standard-modalLabel').html('Add facilty');
    });
    //vendor registration document
    $(document).on('click', '.submitSaveFacilty', function(e) {
        var vendor_registration_document_id = $("#add_facilty_modal input[name=facilty_id]").val();
        if (vendor_registration_document_id) {
            var post_url = "{{ route('facilty.update') }}";
        } else {
            var post_url = "{{ route('facilty.store') }}";
        }
        var form_data = new FormData(document.getElementById("faciltyForm"));
        $.ajax({
            url: post_url,
            method: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
               if (response.status == 'Success') {
                  $('#add_or_edit_social_media_modal').modal('hide');
                  $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                     location.reload()
                  }, 2000);
               } else {
                  $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
               $('#add_vendor_registration_document_modal .social_media_url_err').html('The default language name field is required.');
            }
        });
    });

    $(document).on("click", ".edit_facilty_btn", function() {
        let facilty_id = $(this).data('facilty_id');
        //console.log(facilty_id);
        editfaciltyForm(facilty_id);
    });
    function editfaciltyForm(facilty_id){
        let language_id = $('#option_client_language').val();
         $('#faciltyForm input[name=facilty_id]').val(facilty_id);
         $.ajax({
            method: 'GET',
            data: {
                facilty_id: facilty_id,
                language_id:language_id
            },
            url: "{{ route('facilty.edit') }}",
            success: function(response) {
               if (response.status = 'Success') {
                   console.log(response.data);
                   console.log(response.data.image.image_fit+'90/90'+response.data.image.image_path);
                //   $(document).find("#add_vendor_registration_document_modal select[name=file_type]").val(response.data.file_type).change();

                  $("#add_facilty_modal input[name=facilty_id]").val(response.data.id);

                  $("#add_facilty_modal input[name=facilty_image]").attr('data-default-file',response.data.image.image_fit+'90/90'+response.data.image.image_path );
                  $('.dropify').dropify();
                  $('#add_facilty_modal #standard-modalLabel').html('Update facilty');
                  $('#add_facilty_modal').modal('show');

                  $.each(response.data.translations, function( index, value ) {
                    $('#add_facilty_modal #facilty_name_'+value.language_id).val(value.name);
                  });
               }
            },
            error: function() {}
        });
    }
     // delete kyc document
     $(document).on("click", ".delete_facilty_btn", function() {
         var facilty_id = $(this).data('facilty_id');
         Swal.fire({
            title: "{{__('Are you Sure?')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
         }).then((result) => {
            if (result.value) {
               $.ajax({
                  type: "POST",
                  dataType: 'json',
                  url: "{{ route('facilty.delete') }}",
                  data: {
                     _token: "{{ csrf_token() }}",
                     facilty_id: facilty_id
                  },
                  success: function(response) {
                     if (response.status == "Success") {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                        setTimeout(function() {
                           location.reload()
                        }, 2000);
                     }
                  }
               });
            }
        });
    });


 $('#add_facilties_modal_btn').click(function(e) {
        document.getElementById("userRegistrationDocumentForm").reset();
        $('#faciltyForm input[name=facilty_id]').val("");
        $('#add_facilty_modal').modal('show');
        $('#add_facilty_modal #standard-modalLabel').html('Add facilty');
    });
    //End Vendor Registration Document Script
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $(document).on("click", ".delete_social_media_option_btn", function() {
            var social_media_detail_id = $(this).data('social_media_detail_id');
            Swal.fire({
                title: "{{__('Are you Sure?')}}",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if(result.value)
                {
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: "{{ route('social.media.delete') }}",
                        data: {
                            social_media_detail_id: social_media_detail_id
                        },
                        success: function(response) {
                            if (response.status == "Success") {
                                $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                                setTimeout(function() {
                                    location.reload()
                                }, 2000);
                            }
                        }
                    });
                }
            });
        });
        $(document).on("click", ".edit_social_media_option_btn", function() {
            let social_media_detail_id = $(this).data('social_media_detail_id');
            $('#add_or_edit_social_media_modal input[name=social_media_id]').val(social_media_detail_id);
            $.ajax({
                method: 'GET',
                data: {
                    social_media_detail_id: social_media_detail_id
                },
                url: "{{ route('social.media.edit') }}",
                success: function(response) {
                    if (response.status = 'Success') {
                        $('#add_or_edit_social_media_modal').modal('show');
                        $("#add_or_edit_social_media_modal input[name=url]").val(response.data.url);
                        $("#add_or_edit_social_media_modal .input-group-text").html('<i class="fab fa-'+response.data.icon+'"></i>');
                        $("#add_or_edit_social_media_modal #social_icons").val(response.data.icon);
                        $("#add_or_edit_social_media_modal input[name=social_media_id]").val(response.data.id);
                        $('#add_or_edit_social_media_modal #standard-modalLabel').html('Update Social Media');
                    }
                },
                error: function() {

                }
            });

        });
        $(document).on("click", "#add_social_media_modal_btn", function() {
            $('#add_or_edit_social_media_modal #standard-modalLabel').html('Add Social Media');
            $('#add_or_edit_social_media_modal').modal('show');
        });
        $(document).on('click', '.submitSaveSocialForm', function(e) {
            var social_media_url = $("#add_or_edit_social_media_modal input[name=url]").val();
            var social_media_icon = $("#add_or_edit_social_media_modal #social_icons").val();
            var social_media_id = $("#add_or_edit_social_media_modal input[name=social_media_id]").val();
            if (social_media_id) {
                var post_url = "{{ route('social.media.update') }}";
            } else {
                var post_url = "{{ route('social.media.create') }}";
            }
            $.ajax({
                url: post_url,
                method: 'POST',
                data: {
                    social_media_id: social_media_id,
                    social_media_url: social_media_url,
                    social_media_icon: social_media_icon
                },
                success: function(response) {
                    if (response.status == 'Success') {
                        $('#add_or_edit_social_media_modal').modal('hide');
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                        setTimeout(function() {
                            location.reload()
                        }, 2000);
                    } else {
                        $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
                    }
                },
                error: function(response) {
                    $('.social_media_url_err').html(response.responseJSON.errors.social_media_url[0]);
                }
            });
        });
    });
</script>
<script type="text/javascript">
    // var options = {
    //     zIndex: 9999
    // }
    // $(document).ready(function() {
        // var color1 = new jscolor('#primary_color', options);
        // var color2 = new jscolor('#secondary_color', options);
    // });
    function checkAddressString(obj,name)
    {
        if($(obj).val() == "")
        {
            document.getElementById(name + '_latitude').value = '';
            document.getElementById(name + '_longitude').value = '';
            document.getElementById(name + '_place_id').value = '';
        }
    }
    function generateRandomString(length) {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for (var i = 0; i < length; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
    }

    function genrateKeyAndToken() {
        var key = generateRandomString(30);
        var token = generateRandomString(60);
        $('#personal_access_token_v1').val(key);
        $('#personal_access_token_v2').val(token);
    }
    var existCid = [];
    $('#primary_currency').change(function() {
        var pri_curr = $('#primary_currency option:selected').text();
        console.log(pri_curr);
        $(document).find('.primaryCurText').html('1 ' + pri_curr + '  = ');
    });
    $('#currency').change(function() {
        var activeCur = [];
        var pri_curr = $('#primary_currency option:selected').text();
        var cidText = $('#currency').select2('data');
        for (i = 0; i < cidText.length; i++) {
            activeCur.push(cidText[i].id);
        }
        $(".curr_id").each(function() {
            var cv = $(this).val();
            if (existCid.indexOf(cv) === -1) {
                existCid.push(cv);
            }
        });
        for (i = 0; i < existCid.length; i++) {
            if (activeCur.indexOf(existCid[i]) === -1) {
                $('#addCur-' + existCid[i]).remove();
            }
        }
        for (i = 0; i < cidText.length; i++) {
            if (existCid.indexOf(cidText[i].id) === -1) {
                var text = '<div class="col-sm-10 offset-sm-4 col-lg-12 offset-lg-0 col-xl-8 offset-xl-4 mb-2" id="addCur-' + cidText[i].id + '"><label class="primaryCurText">1 ' + pri_curr + '  = </label> <input type="number" name="multiply_by['+cidText[i].id+']"  oninput="changeCurrencyValue(this)" min="0.00000001" value="0" step=".00000001">' + cidText[i].text + '<input type="hidden" name="cuid[]" class="curr_id" value="' + cidText[i].id + '"></div>';
                $('.multiplierData').append(text);
            }
        }
    });
    function changeCurrencyValue(obj)
    {
        var value = $(obj).val();
        var new_value = value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, $1);
        $(obj).val(new_value);
    }
    //for verification options
    $('.verification_options').change(function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var code = title.toLowerCase();
        if ($(this).is(":checked")) {
            $("#" + code + "_fields_wrapper").show();
            $("#" + code + "_fields_wrapper").find('input').attr('required', true);
        } else {
            $("#" + code + "_fields_wrapper").hide();
            $("#" + code + "_fields_wrapper").find('input').removeAttr('required');
        }
    });

    var dispatcherDiv = $('#need_dispacher_ride');
    var need_dispacher_home_other_service = $('#need_dispacher_home_other_service');
    var laundry_service = $('#need_laundry_service');
    var need_appointment_service = $('#need_appointment_service');
    var is_token_currency_enable = $('#is_token_currency_enable');
    if(is_token_currency_enable.length > 0){
         is_token_currency_enable[0].onchange = function() {

            if ($('#is_token_currency_enable:checked').length != 1) {
               $('.token_row').hide();
            } else {
               $('.token_row').show();
            }
         }
      }
    if(dispatcherDiv.length > 0){
        dispatcherDiv[0].onchange = function() {
            if ($('#need_dispacher_ride:checked').length != 1) {
                $('.dispatcherFields').hide();
            } else {
                $('.dispatcherFields').show();
            }
        }
    }

    if(need_dispacher_home_other_service.length > 0){
        need_dispacher_home_other_service[0].onchange = function() {
            if ($('#need_dispacher_home_other_service:checked').length != 1) {
                $('.home_other_dispatcherFields').hide();
            } else {
                $('.home_other_dispatcherFields').show();
            }
        }
    }
    if(laundry_service.length > 0){
        laundry_service[0].onchange = function() {
            if ($('#need_laundry_service:checked').length != 1) {
                $('.laundryServiceFields').hide();
            } else {
                $('.laundryServiceFields').show();
            }
        }
    }
    if(need_appointment_service.length > 0){
        need_appointment_service[0].onchange = function() {
            if ($('#need_appointment_service:checked').length != 1) {
                $('.appointmentServiceFields').hide();
            } else {
                $('.appointmentServiceFields').show();
            }
        }
    }

    $('#add_user_registration_document_modal_btn').click(function(e) {
        document.getElementById("userRegistrationDocumentForm").reset();
        $('#add_user_registration_document_modal input[name=user_registration_document_id]').val("");
        $('#add_user_registration_document_modal').modal('show');
        $('#add_user_registration_document_modal #standard-modalLabel').html('Add User Registration Document');
    });

</script>

{{-- Insert role_id (Start) --}}
    {{-- <script>
        $(".is_enable_pricing_via_role").on("change paste keyup", function() {
            var role_id = $(this).attr('data-role-id');
            $('#role_id_for_pricing').val(role_id);
        });
    </script> --}}
{{-- Insert role_id (End) --}}

@if($preference->is_static_dropoff == '1')
    @include('backend.setting.customizeDatatablescript')
@endif

@endsection
