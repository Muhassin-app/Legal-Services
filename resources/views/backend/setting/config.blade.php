@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Configurations'])
@section('css')
@endsection
@section('content')

    @php
        $sms_crendential = json_decode($preference->sms_credentials);
    @endphp


    <div class="container-fluid custom-toggle al">
        @if ($client_preference_detail->business_type != 'taxi')
            <div class="row">
                <div class="col-12">
                    <!-- Configurations start -->
                    <div class="page-title-box">
                        <h4 class="page-title text-uppercase">{{ __('Configurations') }}</h4>
                    </div><!-- Configurations end -->
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="text-sm-left">
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <span>{!! \Session::get('success') !!}</span>
                        </div>
                    @elseif(\Session::has('error'))
                        <div class="alert alert-danger">
                            <span>{!! \Session::get('error') !!}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row map-configration_dashboard">
            @if ($client_preference_detail->business_type != 'taxi')
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="row h-100">
                        <div class="col-12">
                            <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                                @csrf
                                <!-- Hyperlocal start -->
                                <div class="card-box h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h4 class="header-title mb-0">{{ __('Hyperlocal') }}</h4>
                                        <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                                    </div>
                                    <p class="sub-header">
                                        {{ __('Enable location based visibility of Vendors and set the Default Location.') }}
                                    </p>
                                    <input type="hidden" name="hyperlocals" id="hyperlocals" value="1">

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0">
                                                <label for="is_hyperlocal" class="mr-3">{{ __('Enable') }}</label>
                                                <input type="checkbox" data-plugin="switchery" name="is_hyperlocal"
                                                    id="is_hyperlocal" class="form-control" data-color="#43bee1"
                                                    @if (isset($preference) && $preference->is_hyperlocal == '1') checked @endif>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 mt-3 disableHyperLocal"
                                                    style="{{ isset($preference) && $preference->is_hyperlocal == '1' ? '' : 'display:none;' }}">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group mb-0">
                                                                <label
                                                                    for="Default_location_name">{{ __('Default Location') }}</label>
                                                                <div class="input-group">
                                                                    <input type="text" name="Default_location_name"
                                                                        id="Default_location_name"
                                                                        placeholder="Delhi, India" class="form-control"
                                                                        value="{{ old('Default_location_name', $preference->Default_location_name ?? '') }}">
                                                                    <div class="input-group-append">
                                                                        <button
                                                                            class="btn btn-xs btn-dark waves-effect waves-light showMap"
                                                                            type="button" num="add1"> <i
                                                                                class="mdi mdi-map-marker-radius"></i></button>
                                                                    </div>
                                                                </div>
                                                                @if ($errors->has('Default_location_name'))
                                                                    <span class="text-danger" role="alert">
                                                                        <strong>{{ $errors->first('Default_location_name') }}</strong>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group mt-3 mb-0">
                                                                <label for="Default_latitude">{{ __('Latitude') }}</label>
                                                                <input type="text" name="Default_latitude"
                                                                    id="Default_latitude" placeholder="24.9876755"
                                                                    class="form-control"
                                                                    value="{{ old('Default_latitude', $preference->Default_latitude ?? '') }}">
                                                                @if ($errors->has('Default_latitude'))
                                                                    <span class="text-danger" role="alert">
                                                                        <strong>{{ $errors->first('Default_latitude') }}</strong>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group mt-3 mb-0">
                                                                <label
                                                                    for="Default_longitude">{{ __('Longitude') }}</label>
                                                                <input type="text" name="Default_longitude"
                                                                    id="Default_longitude" placeholder="11.9871371723"
                                                                    class="form-control"
                                                                    value="{{ old('Default_longitude', $preference->Default_longitude ?? '') }}">
                                                                @if ($errors->has('Default_longitude'))
                                                                    <span class="text-danger" role="alert">
                                                                        <strong>{{ $errors->first('Default_longitude') }}</strong>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- Hyperlocal end -->
                            </form>
                        </div>
                    </div>
                </div>
            @endif
            <!-- <div class="col-lg-4 col-md-6 mb-3">
                <div class="row h-100">
                    <div class="col-12">
                        <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                            @csrf
                            <div class="card-box h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h4 class="header-title mb-0">{{ __('Vendor Delivery Option') }}</h4>
                                    <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                                </div>
                                <p class="sub-header">{{ __('Enable to show vendor delivery option.') }}</p>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                            <label for="is_same_day_delivery_switch"
                                                class="mr-2 mb-0">{{ __('Same Day Delivery') }}<small
                                                    class="d-block pr-5">{{ __('Enable to allow customers to same day delivery.') }}</small></label>
                                            <span> <input type="checkbox" data-plugin="switchery"
                                                    name="is_same_day_delivery_switch" id="is_same_day_delivery_switch"
                                                    class="form-control checkbox_change"
                                                    data-className="is_same_day_delivery" data-color="#43bee1"
                                                    @if (@$getAdditionalPreference['is_same_day_delivery'] == 1) checked='checked' @endif>
                                            </span>
                                            <input type="hidden"
                                                @if (@$getAdditionalPreference['is_same_day_delivery'] == 1) value="1" @else value="0" @endif
                                                name="is_same_day_delivery" id="is_same_day_delivery" />
                                        </div>

                                        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                            <label for="is_next_day_delivery_switch"
                                                class="mr-2 mb-0">{{ __('Next Day Delivery') }}<small
                                                    class="d-block pr-5">{{ __('Enable to allow customers to next day delivery.') }}</small></label>
                                            <span> <input type="checkbox" data-plugin="switchery"
                                                    name="is_next_day_delivery_switch" id="is_next_day_delivery_switch"
                                                    class="form-control checkbox_change"
                                                    data-className="is_next_day_delivery" data-color="#43bee1"
                                                    @if (@$getAdditionalPreference['is_next_day_delivery'] == 1) checked='checked' @endif>
                                            </span>
                                            <input type="hidden"
                                                @if (@$getAdditionalPreference['is_next_day_delivery'] == 1) value="1" @else value="0" @endif
                                                name="is_next_day_delivery" id="is_next_day_delivery" />
                                        </div>

                                        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                            <label for="is_hyper_local_delivery_switch"
                                                class="mr-2 mb-0">{{ __('Hyper Local Delivery') }}<small
                                                    class="d-block pr-5">{{ __('Enable to allow customers to hyper local delivery.') }}</small></label>
                                            <span> <input type="checkbox" data-plugin="switchery"
                                                    name="is_hyper_local_delivery_switch"
                                                    id="is_hyper_local_delivery_switch"
                                                    class="form-control checkbox_change"
                                                    data-className="is_hyper_local_delivery" data-color="#43bee1"
                                                    @if (@$getAdditionalPreference['is_hyper_local_delivery'] == 1) checked='checked' @endif>
                                            </span>
                                            <input type="hidden"
                                                @if (@$getAdditionalPreference['is_hyper_local_delivery'] == 1) value="1" @else value="0" @endif
                                                name="is_hyper_local_delivery" id="is_hyper_local_delivery" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div> -->

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="row h-100">
                    <div class="col-12">
                        <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                            @csrf
                            <div class="card-box h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h4 class="header-title mb-0">{{ __('Payment Option') }}</h4>
                                    <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                                </div>
                                <p class="sub-header">{{ __('Enable to show payment option.') }}</p>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                            <label for="is_cod_payment_switch"
                                                class="mr-2 mb-0">{{ __('COD Payment') }}<small
                                                    class="d-block pr-5">{{ __('Enable to allow customers to cod payment.') }}</small></label>
                                            <span> <input type="checkbox" data-plugin="switchery"
                                                    name="is_cod_payment_switch" id="is_cod_payment_switch"
                                                    class="form-control checkbox_change" data-className="is_cod_payment"
                                                    data-color="#43bee1"
                                                    @if (@$getAdditionalPreference['is_cod_payment'] == 1) checked='checked' @endif>
                                            </span>
                                            <input type="hidden"
                                                @if (@$getAdditionalPreference['is_cod_payment'] == 1) value="1" @else value="0" @endif
                                                name="is_cod_payment" id="is_cod_payment" />
                                        </div>

                                        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                            <label for="is_prepaid_payment_switch"
                                                class="mr-2 mb-0">{{ __('Prepaid Payment') }}<small
                                                    class="d-block pr-5">{{ __('Enable to allow customers to prepaid payment.') }}</small></label>
                                            <span> <input type="checkbox" data-plugin="switchery"
                                                    name="is_prepaid_payment_switch" id="is_prepaid_payment_switch"
                                                    class="form-control checkbox_change"
                                                    data-className="is_prepaid_payment" data-color="#43bee1"
                                                    @if (@$getAdditionalPreference['is_prepaid_payment'] == 1) checked='checked' @endif>
                                            </span>
                                            <input type="hidden"
                                                @if (@$getAdditionalPreference['is_prepaid_payment'] == 1) value="1" @else value="0" @endif
                                                name="is_prepaid_payment" id="is_prepaid_payment" />
                                        </div>

                                        <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                            <label for="is_partial_payment_switch"
                                                class="mr-2 mb-0">{{ __('Partial Payment') }}<small
                                                    class="d-block pr-5">{{ __('Enable to allow customers to partial payment.') }}</small></label>
                                            <span> <input type="checkbox" data-plugin="switchery"
                                                    name="is_partial_payment_switch" id="is_partial_payment_switch"
                                                    class="form-control checkbox_change"
                                                    data-className="is_partial_payment" data-color="#43bee1"
                                                    @if (@$getAdditionalPreference['is_partial_payment'] == 1) checked='checked' @endif>
                                            </span>
                                            <input type="hidden"
                                                @if (@$getAdditionalPreference['is_partial_payment'] == 1) value="1" @else value="0" @endif
                                                name="is_partial_payment" id="is_partial_payment" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($client_preference_detail->enable_inventory_service == 1)
            <div class="col-lg-3 col-md-6 mb-3">
                <!-- Order Panel section start -->
                <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                    @csrf
                    <div class="card-box h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="header-title mb-0">{{ __('Inventory Service') }}</h4>
                            <button class="btn btn-info d-block" type="submit" name="need_inventory_service_submit_btn"
                                value="1"> {{ __('Save') }} </button>
                        </div>
                        <p class="sub-header">{{ __('Offer Inventory Services with Order.') }}</p>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <div class="form-group mb-0 switchery-demo">
                                        <label for="need_inventory_service" class="mr-3">{{ __('Enable') }}</label>
                                        <input type="checkbox" data-plugin="switchery" name="need_inventory_service"
                                            id="need_inventory_service" class="form-control" data-color="#43bee1"
                                            @if (isset($preference) && $preference->need_inventory_service == '1') checked='checked' @endif>
                                    </div>
                                    <div class="form-group mt-3 mb-0 inventoryFields"
                                        style="{{ isset($preference) && $preference->need_inventory_service == '1' ? '' : 'display:none;' }}">
                                        <label for="inventory_service_key_url">{{ __('Inventory URL') }}
                                            *(https://www.abc.com)</label>
                                        <input type="text" name="inventory_service_key_url"
                                            id="inventory_service_key_url" placeholder="https://www.abc.com"
                                            class="form-control"
                                            value="{{ old('inventory_service_key_url', $preference->inventory_service_key_url ?? '') }}">
                                        @if ($errors->has('inventory_service_key_url'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ $errors->first('inventory_service_key_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group mt-3 mb-0 inventoryFields"
                                        style="{{ isset($preference) && $preference->need_inventory_service == '1' ? '' : 'display:none;' }}">
                                        <label for="inventory_service_key_code">{{ __('Inventory Short code') }}</label>
                                        <input type="text" name="inventory_service_key_code"
                                            id="inventory_service_key_code" placeholder="" class="form-control"
                                            value="{{ old('inventory_service_key_code', $preference->inventory_service_key_code ?? '') }}">
                                        @if ($errors->has('inventory_service_key_code'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ $errors->first('inventory_service_key_code') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                </div>
                                <div class="form-group mt-3 mb-0 inventoryFields"
                                    style="{{ isset($preference) && $preference->need_inventory_service == '1' ? '' : 'display:none;' }}">
                                    <label for="inventory_service_key_code">{{ __('Inventory Short code') }}</label>
                                    <input type="text" name="inventory_service_key_code"
                                        id="inventory_service_key_code" placeholder="" class="form-control"
                                        value="{{ old('inventory_service_key_code', $preference->inventory_service_key_code ?? '') }}">
                                    @if ($errors->has('inventory_service_key_code'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('inventory_service_key_code') }}</strong>
                                        </span>
                                    @endif
                                </div>



                            </div>

                        </div><!-- On Demand Services section end -->
                </form>
            </div>
        @endif


    </div>

    <div class="row">
        <div class="col-12">
            <!-- Social Logins title start -->
            <div class="page-title-box">
                <h4 class="page-title text-uppercase">{{ __('Social Logins') }}</h4>
            </div><!-- Social Logins title end -->
        </div>
    </div>

    <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
        <input type="hidden" name="social_login" id="social_login" value="1">
        @csrf
        <div class="row">
            <div class="col-xl-3 col-lg-6 mb-xl-0 mb-3">
                <!-- Facebook card start -->
                <div class="card-box h-100">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="fb_login" class="d-flex align-items-center justify-content-between">
                                    <h5 class="social_head"><i class="fab fa-facebook-f"></i>
                                        <span>{{ __('Facebook') }}</span>
                                    </h5>
                                    <button class="btn btn-info btn-block save_btn" type="submit">
                                        {{ __('Save') }} </button>
                                </label>
                                <label for="" class="mr-3">{{ __('Enable') }}</label>
                                <input type="checkbox" data-plugin="switchery" name="fb_login" id="fb_login"
                                    class="form-control" data-color="#43bee1"
                                    @if (isset($preference) && $preference->fb_login == '1') checked='checked' @endif>
                            </div>
                        </div>
                    </div>
                    <div class="row fb_row"
                        style="{{ isset($preference) && $preference->fb_login == '1' ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="fb_client_id">{{ __('Facebook Client Key') }}</label>
                                <input type="text" name="fb_client_id" id="fb_client_id" placeholder=""
                                    class="form-control"
                                    value="{{ old('fb_client_id', $preference->fb_client_id ?? '') }}">
                                @if ($errors->has('fb_client_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fb_client_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="fb_client_secret">{{ __('Facebook Client Secret') }}</label>
                                <input type="password" name="fb_client_secret" id="fb_client_secret" placeholder=""
                                    class="form-control"
                                    value="{{ old('fb_client_secret', $preference->fb_client_secret ?? '') }}">
                                @if ($errors->has('fb_client_secret'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fb_client_secret') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="fb_client_url">{{ __('Facebook Redirect URL') }}</label>
                                <input type="text" name="fb_client_url" id="fb_client_url" placeholder=""
                                    class="form-control"
                                    value="{{ old('fb_client_url', $preference->fb_client_url ?? '') }}">
                                @if ($errors->has('fb_client_url'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fb_client_url') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div><!-- Facebook card end -->
            </div>

            <!-- nafath -->
            <div class="col-xl-3 col-lg-6 mb-xl-0 mb-3">
                <!-- Facebook card start -->
                <div class="card-box h-100">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="nafath_login" class="d-flex align-items-center justify-content-between">
                                    <h5 class="social_head"><i class="fab fa-facebook-f"></i>
                                        <span>{{ __('Nafath') }}</span>
                                    </h5>
                                    <button class="btn btn-info btn-block save_btn" type="submit">
                                        {{ __('Save') }} </button>
                                </label>
                                <label for="" class="mr-3">{{ __('Enable') }}</label>
                                <input type="checkbox" data-plugin="switchery" name="nafath_login" id="nafath_login"
                                    class="form-control" data-color="#43bee1"
                                    @if (isset($preference) && $preference->nafath_login == '1') checked='checked' @endif>
                            </div>
                        </div>
                    </div>
                    <div class="row nafath_row"
                        style="{{ isset($preference) && $preference->nafath_login == '1' ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="nafath_client_id">{{ __('Nafath Client Key') }}</label>
                                <input type="text" name="nafath_client_id" id="nafath_client_id" placeholder=""
                                    class="form-control"
                                    value="{{ old('nafath_client_id', $preference->nafath_client_id ?? '') }}">
                                @if ($errors->has('nafath_client_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('nafath_client_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="nafath_client_secret">{{ __('Nafath Client Secret') }}</label>
                                <input type="password" name="nafath_client_secret" id="nafath_client_secret" placeholder=""
                                    class="form-control"
                                    value="{{ old('nafath_client_secret', $preference->nafath_client_secret ?? '') }}">
                                @if ($errors->has('nafath_client_secret'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('nafath_client_secret') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div><!-- Facebook card end -->
            </div>
            <div class="col-xl-3 col-lg-6 mb-xl-0 mb-3">
                <!-- Twitter card start -->
                <div class="card-box h-100">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="twitter_login" class="d-flex align-items-center justify-content-between">
                                    <h5 class="social_head"><i class="fab fa-twitter"></i>
                                        <span>{{ __('Twitter') }}</span>
                                    </h5>
                                    <button class="btn btn-info btn-block save_btn" type="submit">
                                        {{ __('Save') }} </button>
                                </label>
                                <label for="" class="mr-3">{{ __('Enable') }}</label>
                                <input type="checkbox" data-plugin="switchery" name="twitter_login" id="twitter_login"
                                    class="form-control" data-color="#43bee1"
                                    @if (isset($preference) && $preference->twitter_login == '1') checked='checked' @endif>
                            </div>
                        </div>
                    </div>
                    <div class="row  twitter_row"
                        style="{{ isset($preference) && $preference->twitter_login == '1' ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="twitter_client_id"></label>{{ __('Twitter Client Key') }}</label>
                                <label for="" class="mr-3">{{ __('Enable') }}</label>
                                <input type="text" name="twitter_client_id" id="twitter_client_id" placeholder=""
                                    class="form-control"
                                    value="{{ old('twitter_client_id', $preference->twitter_client_id ?? '') }}">
                                @if ($errors->has('twitter_client_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('twitter_client_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="twitter_client_secret">{{ __('Twitter Client Secret') }}</label>
                                <input type="password" name="twitter_client_secret" id="twitter_client_secret"
                                    placeholder="" class="form-control"
                                    value="{{ old('twitter_client_secret', $preference->twitter_client_secret ?? '') }}">
                                @if ($errors->has('twitter_client_secret'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('twitter_client_secret') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="twitter_client_url">{{ __('Twitter Redirect URL') }}</label>
                                <input type="text" name="twitter_client_url" id="twitter_client_url" placeholder=""
                                    class="form-control"
                                    value="{{ old('twitter_client_url', $preference->twitter_client_url ?? '') }}">
                                @if ($errors->has('twitter_client_url'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('twitter_client_url') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div><!-- Twitter card end -->
            </div>
            <div class="col-xl-3 col-lg-6 mb-xl-0 mb-3">
                <!-- Google card start -->
                <div class="card-box h-100">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="google_login" class="d-flex align-items-center justify-content-between">
                                    <h5 class="social_head"><i class="fab fa-google"></i>
                                        <span>{{ __('Google') }}</span>
                                    </h5>
                                    <button class="btn btn-info btn-block save_btn" type="submit">
                                        {{ __('Save') }} </button>
                                </label>
                                <label for="" class="mr-3">{{ __('Enable') }}</label>
                                <input type="checkbox" data-plugin="switchery" name="google_login" id="google_login"
                                    class="form-control" data-color="#43bee1"
                                    @if (isset($preference) && $preference->google_login == '1') checked='checked' @endif>
                            </div>
                        </div>
                    </div>
                    <div class="row google_row"
                        style="{{ isset($preference) && $preference->google_login == '1' ? '' : 'display:none;' }}">
                        <div class="col-md-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="google_client_id">{{ __('Google') }} {{ __('Client Key') }}</label>
                                <input type="text" name="google_client_id" id="google_client_id" placeholder=""
                                    class="form-control"
                                    value="{{ old('google_client_id', $preference->google_client_id ?? '') }}">
                                @if ($errors->has('google_client_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('google_client_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label for="google_client_secret">{{ __('Google') }}
                                    {{ __('Client Secret') }}</label>
                                <input type="password" name="google_client_secret" id="google_client_secret"
                                    placeholder="" class="form-control"
                                    value="{{ old('google_client_secret', $preference->google_client_secret ?? '') }}">
                                @if ($errors->has('google_client_secret'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('google_client_secret') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label for="google_client_url">{{ __('Google') }} {{ __('Redirect URL') }}</label>
                                <input type="text" name="google_client_url" id="google_client_url" placeholder=""
                                    class="form-control"
                                    value="{{ old('google_client_url', $preference->google_client_url ?? '') }}">
                                @if ($errors->has('google_client_url'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('google_client_url') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div><!-- Google card end -->
            </div>
            <div class="col-xl-3 col-lg-6 mb-xl-0 mb-3">
                <!-- Apple card start -->
                <div class="card-box h-100">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="apple_login" class="d-flex align-items-center justify-content-between">
                                    <h5 class="social_head"><i class="fab fa-apple"></i>
                                        <span>{{ __('Apple') }}</span>
                                    </h5>
                                    <button class="btn btn-info btn-block save_btn" type="submit">
                                        {{ __('Save') }} </button>
                                </label>
                                <label for="" class="mr-3">{{ __('Enable') }}</label>
                                <input type="checkbox" data-plugin="switchery" name="apple_login" id="apple_login"
                                    class="form-control" data-color="#43bee1"
                                    @if (isset($preference) && $preference->apple_login == '1') checked='checked' @endif>
                            </div>
                        </div>
                    </div>
                    <div class="row apple_row"
                        style="{{ isset($preference) && $preference->apple_login == '1' ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="apple_client_id">Apple {{ __('Client Key') }}</label>
                                <input type="text" name="apple_client_id" id="apple_client_id" placeholder=""
                                    class="form-control"
                                    value="{{ old('apple_client_id', $preference->apple_client_id ?? '') }}">
                                @if ($errors->has('apple_client_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('apple_client_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="apple_client_secret">Apple {{ __('Client Secret') }}</label>
                                <input type="password" name="apple_client_secret" id="apple_client_secret"
                                    placeholder="" class="form-control"
                                    value="{{ old('apple_client_secret', $preference->apple_client_secret ?? '') }}">
                                @if ($errors->has('apple_client_secret'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('apple_client_secret') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="apple_client_url"> Apple {{ __('Redirect URL') }}</label>
                                <input type="text" name="apple_client_url" id="apple_client_url" placeholder=""
                                    class="form-control"
                                    value="{{ old('apple_client_url', $preference->apple_client_url ?? '') }}">
                                @if ($errors->has('apple_client_url'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('apple_client_url') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div><!-- Apple card end -->
            </div>
        </div>
    </form>


    <div class="row">
        <div class="col-12">
            <!-- Map Sms Emails title start -->
            <div class="page-title-box">
                <h4 class="page-title text-uppercase">{{ __('Map Sms Emails') }}</h4>
            </div><!-- Map Sms Emails title end -->
        </div>
    </div>
    <div class="row map-configration_dashboard">
        <div class="col-lg-3 mb-3">
            <!-- Map Configuration start -->
            <form class="h-100" method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                @csrf
                <div class="card-box h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{ __('Map Configuration') }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                    </div>
                    <p class="sub-header">
                        {{ __("View and update your Map type and it's API key.") }}
                    </p>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="currency">{{ __('MAP PROVIDER') }}</label>
                                <select class="form-control" id="map_provider" name="map_provider">
                                    @foreach ($mapTypes as $map)
                                        <option value="{{ $map->id }}"
                                            {{ isset($preference) && $preference->map_provider == $map->id ? 'selected' : '' }}>
                                            {{ $map->provider }} </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('map_provider'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('map_provider') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="map_key">{{ __('API KEY') }}</label>
                                <input type="password" name="map_key" id="map_key" placeholder=""
                                    class="form-control" value="{{ old('map_key', $preference->map_key ?? '') }}">
                                @if ($errors->has('map_key'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('map_key') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="map_key_for_app">{{ __('API KEY FOR ANDROID APP') }}</label>
                                <input type="password" name="map_key_for_app" id="map_key_for_app" placeholder=""
                                    class="form-control"
                                    value="{{ old('map_key_for_app', $preference->map_key_for_app ?? '') }}">
                                @if ($errors->has('map_key_for_app'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('map_key_for_app') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="map_key_for_ios_app">{{ __('API KEY FOR IOS APP') }}</label>
                                <input type="password" name="map_key_for_ios_app" id="map_key_for_ios_app" placeholder=""
                                    class="form-control"
                                    value="{{ old('map_key_for_ios_app', $preference->map_key_for_ios_app ?? '') }}">
                                @if ($errors->has('map_key_for_ios_app'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('map_key_for_ios_app') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form><!-- Map Configuration end -->
        </div>
        <div class="col-lg-3 mb-3">
            <!-- SMS Configuration start -->
            <form class="h-100" method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                @csrf
                <div class="card-box h-100 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{ __('SMS Configuration') }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                    </div>
                    <p class="sub-header">{{ __("View and update your SMS Gateway and it's API keys.") }}</p>
                    <div class="d-flex align-items-center justify-content-between mt-3 mb-2">
                        <h5 class="font-weight-normal m-0">{{ __('Send Static Otp ') }}</h5>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="cancelOrderCustomSwitch_static_otp"
                                name="static_otp"
                                {{ isset($sms_crendential->static_otp) && $sms_crendential->static_otp == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="cancelOrderCustomSwitch_static_otp"></label>
                        </div>
                    </div>
                    <div class="row mb-0">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="sms_provider">{{ __('SMS PROVIDER') }}</label>
                                <select class="form-control" id="sms_provider" name="sms_provider"
                                    onchange="toggle_smsFields(this)">
                                    @foreach ($smsTypes as $sms)
                                        <option data-id="{{ $sms->keyword }}_fields" value="{{ $sms->id }}"
                                            {{ isset($preference) && $preference->sms_provider == $sms->id ? 'selected' : '' }}>
                                            {{ $sms->provider }} </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('sms_provider'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('sms_provider') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- For twillio -->
                        <div class="sms_fields row mx-0" id="twilio_fields"
                            style="display : {{ $preference->sms_provider == 1 ? 'flex' : 'none' }};">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="sms_from">{{ __('SMS From') }}</label>
                                    <input type="text" name="sms_from" id="sms_from" placeholder=""
                                        class="form-control" value="{{ old('sms_from', $preference->sms_from ?? '') }}">
                                    @if ($errors->has('sms_from'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('sms_from') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="sms_key">{{ __('API KEY') }}</label>
                                    <input type="text" name="sms_key" id="sms_key" placeholder=""
                                        class="form-control" value="{{ old('sms_key', $preference->sms_key ?? '') }}">
                                    @if ($errors->has('sms_key'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('sms_key') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="sms_secret">{{ __('API Secret') }}</label>
                                    <input type="password" name="sms_secret" id="sms_secret" placeholder=""
                                        class="form-control"
                                        value="{{ old('sms_secret', $preference->sms_secret ?? '') }}">
                                    @if ($errors->has('sms_secret'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('sms_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>


                        <!-- For mTalkz -->
                        <div class="row sms_fields mx-0" id="mTalkz_fields"
                            style="display : {{ $preference->sms_provider == 2 ? 'flex' : 'none' }};">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="mtalkz_api_key">{{ __('API Key') }}</label>
                                    <input type="text" name="mtalkz_api_key" id="mtalkz_api_key" placeholder=""
                                        class="form-control"
                                        value="{{ old('mtalkz_api_key', $sms_crendential->api_key ?? '') }}">
                                    @if ($errors->has('mtalkz_api_key'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('mtalkz_api_key') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="mtalkz_sender_id">{{ __('Sender ID') }}</label>
                                    <input type="text" name="mtalkz_sender_id" id="mtalkz_sender_id" placeholder=""
                                        class="form-control"
                                        value="{{ old('mtalkz_sender_id', $sms_crendential->sender_id ?? '') }}">
                                    @if ($errors->has('mtalkz_sender_id'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('mtalkz_sender_id') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- For mTalkz -->
                        <div class="row sms_fields mx-0" id="mazinhost_fields"
                            style="display : {{ $preference->sms_provider == 3 ? 'flex' : 'none' }};">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="mazinhost_api_key">{{ __('API Key') }}</label>
                                    <input type="text" name="mazinhost_api_key" id="mazinhost_api_key" placeholder=""
                                        class="form-control"
                                        value="{{ old('mazinhost_api_key', $sms_crendential->api_key ?? '') }}">
                                    @if ($errors->has('mazinhost_api_key'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('mazinhost_api_key') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- <input type="hidden" name='custom_mods_config_additional' value='1'>
                  <input type="hidden" name='is_hubspot' value='1'> --}}
                        {{-- <div class="row fb_row" style="{{((isset($preference) && $preference->client_preferences_additional->is_hubspot_enable == '0')) ? '' : 'display:none;'}}"> --}}
                        @php
                            $allRoles = \App\Models\RoleOld::get();
                        @endphp
                        <hr />
                        {{-- <div class="row hub_row alCustomToggleColor"
                            style="{{ isset($getAdditionalPreference['is_free_delivery_by_roles']) && $getAdditionalPreference['is_free_delivery_by_roles'] == 1 ? '' : 'display:none;' }}">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="mazinhost_sender_id">{{ __('Sender ID') }}</label>
                                    <input type="text" name="mazinhost_sender_id" id="mazinhost_sender_id"
                                        placeholder="" class="form-control"
                                        value="{{ old('mazinhost_sender_id', $sms_crendential->sender_id ?? '') }}">
                                    @if ($errors->has('mazinhost_sender_id'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('mazinhost_sender_id') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div> --}}

                        <!-- For unifonic_fields -->
                        <div class="row sms_fields mx-0" id="unifonic_fields"
                            style="display : {{ $preference->sms_provider == 4 ? 'flex' : 'none' }};">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="unifonic_app_id">{{ __('App Id') }}</label>
                                    <input type="text" name="unifonic_app_id" id="unifonic_app_id" placeholder=""
                                        class="form-control"
                                        value="{{ old('unifonic_app_id', $sms_crendential->unifonic_app_id ?? '') }}">
                                    @if ($errors->has('unifonic_app_id'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('unifonic_app_id') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="unifonic_account_email">{{ __('Unifonic Account Email') }}</label>
                                    <input type="text" name="unifonic_account_email" id="unifonic_account_email"
                                        placeholder="" class="form-control"
                                        value="{{ old('unifonic_account_email', $sms_crendential->unifonic_account_email ?? '') }}">
                                    @if ($errors->has('unifonic_account_email'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('unifonic_account_email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="unifonic_account_password">{{ __('Unifonic Account Password') }}</label>
                                    <input type="text" name="unifonic_account_password" id="unifonic_account_password"
                                        placeholder="" class="form-control"
                                        value="{{ old('unifonic_account_password', $sms_crendential->unifonic_account_password ?? '') }}">
                                    @if ($errors->has('unifonic_account_password'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('unifonic_account_password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- For arkesel -->
                        <div class="row sms_fields mx-0" id="arkesel_fields"
                            style="display : {{ $preference->sms_provider == 5 ? 'flex' : 'none' }};">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="arkesel_api_key">{{ __('API Key') }}</label>
                                    <input type="text" name="arkesel_api_key" id="arkesel_api_key" placeholder=""
                                        class="form-control"
                                        value="{{ old('arkesel_api_key', $sms_crendential->api_key ?? '') }}">
                                    @if ($errors->has('arkesel_api_key'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('arkesel_api_key') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="arkesel_sender_id">{{ __('Sender ID') }}</label>
                                    <input type="text" name="arkesel_sender_id" id="arkesel_sender_id" placeholder=""
                                        class="form-control"
                                        value="{{ old('arkesel_sender_id', $sms_crendential->sender_id ?? '') }}">
                                    @if ($errors->has('arkesel_sender_id'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('arkesel_sender_id') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- For Ethiopia -->
                        <div class="sms_fields row mx-0" id="ethiopia_fields"
                            style="display : {{ $preference->sms_provider == 9 ? 'flex' : 'none' }};">
                            <div class="col-12">
                                <span
                                    class="text-danger">{{ __('Only Available For +251, +09 And +9 Country Code') }}</span>
                                <div class="form-group mb-2">
                                    <label for="sms_username">{{ __('Username') }}</label>
                                    <input type="text" name="sms_username" id="sms_username" placeholder=""
                                        class="form-control"
                                        value="{{ old('sms_username', $sms_crendential->sms_username ?? '') }}">
                                    @if ($errors->has('sms_username'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('sms_username') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="sms_secret">{{ __('Password') }}</label>
                                    <input type="password" name="sms_password" id="sms_password" placeholder=""
                                        class="form-control"
                                        value="{{ old('sms_password', $sms_crendential->sms_password ?? '') }}">
                                    @if ($errors->has('sms_password'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('sms_password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- For afrTalk -->
                        <div class="row sms_fields mx-0" id="afrTalk_fields"
                            style="display : {{ $preference->sms_provider == 6 ? 'flex' : 'none' }};">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="afrTalk_api_key">{{ __('API Key') }}</label>
                                    <input type="text" name="afrTalk_api_key" id="afrTalk_api_key" placeholder=""
                                        class="form-control"
                                        value="{{ old('afrTalk_api_key', $sms_crendential->api_key ?? '') }}">
                                    @if ($errors->has('afrTalk_api_key'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('afrTalk_api_key') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="afrTalk_sender_id">{{ __('Sender ID') }}</label>
                                    <input type="text" name="afrTalk_sender_id" id="afrTalk_sender_id" placeholder=""
                                        class="form-control"
                                        value="{{ old('afrTalk_sender_id', $sms_crendential->sender_id ?? '') }}">
                                    @if ($errors->has('afrTalk_sender_id'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('afrTalk_sender_id') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row sms_fields mx-0" id="vonage_fields"
                            style="display : {{ $preference->sms_provider ==7 ? 'flex' : 'none' }};">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="vonage_api_key">{{ __('API Key') }}</label>
                                    <input type="text" name="vonage_api_key" id="vonage_api_key" placeholder=""
                                        class="form-control"
                                        value="{{ old('vonage_api_key', $sms_crendential->api_key ?? '') }}">
                                    @if ($errors->has('vonage_api_key'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('vonage_api_key') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="mtalkz_sender_id">{{ __('Secret Key') }}</label>
                                    <input type="password" name="vonage_secret_key" id="vonage_secret_key" placeholder=""
                                        class="form-control"
                                        value="{{ old('vonage_secret_key', $sms_crendential->secret_key ?? '') }}">
                                    @if ($errors->has('vonage_secret_key'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('vonage_secret_key') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                  <div class="row sms_fields mx-0" id="sms_country_fields"style="display : {{ $preference->sms_provider == 10 ? 'flex' : 'none' }};">
                    <div class="col-12">
                        <div class="form-group mb-2">
                            <label for="sms_sender_id">{{ __('Sender Id') }}</label>
                            <input type="text" name="sms_sender_id" id="sms_sender_id" placeholder=""
                                class="form-control"
                                value="{{ old('sms_sender_id', $sms_crendential->sms_sender_id ?? '') }}">
                            @if ($errors->has('sms_sender_id'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('sms_sender_id') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group mb-2">
                            <label for="sms_auth_key">{{ __('Auth Key') }}</label>
                            <input type="text" name="sms_auth_key" id="sms_auth_key" placeholder=""
                                class="form-control"
                                value="{{ old('sms_auth_key', $sms_crendential->sms_auth_key ?? '') }}">
                            @if ($errors->has('sms_auth_key'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('sms_auth_key') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group mb-2">
                            <label for="sms_auth_token">{{ __('Auth Token') }}</label>
                            <input type="text" name="sms_auth_token" id="sms_auth_token" placeholder=""
                                class="form-control"
                                value="{{ old('sms_auth_token', $sms_crendential->sms_auth_token ?? '') }}">
                            @if ($errors->has('sms_auth_token'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('sms_auth_token') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                    </div>
                </div>
            </form><!-- SMS Configuration end -->
        </div>
        <div class="col-lg-6 mb-3">
            <!-- Mail Configuration start -->
            <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}" class="h-100">
                @csrf
                <div class="card-box h-100 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{ __('Mail Configuration') }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                    </div>
                    <p class="sub-header"> {{ __('View and update your SMTP credentials.') }}</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="mail_type">{{ __('Mail Type') }}</label>
                                <input type="text" name="mail_type" id="mail_type" placeholder="SMTP"
                                    class="form-control" value="{{ old('mail_type', $preference->mail_type ?? '') }}">
                                @if ($errors->has('mail_type'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('mail_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="mail_driver">{{ __('Mail Driver') }}</label>
                                <input type="text" name="mail_driver" id="mail_driver" placeholder=""
                                    class="form-control"
                                    value="{{ old('mail_driver', $preference->mail_driver ?? '') }}">
                                @if ($errors->has('mail_driver'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('mail_driver') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="mail_host">{{ __('Mail Host') }}</label>
                                <input type="text" name="mail_host" id="mail_host" placeholder="SMTP"
                                    class="form-control" value="{{ old('mail_host', $preference->mail_host ?? '') }}">
                                @if ($errors->has('mail_host'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('mail_host') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="mail_port">{{ __('Mail Port') }}</label>
                                <input type="text" name="mail_port" id="mail_port" placeholder=""
                                    class="form-control" value="{{ old('mail_port', $preference->mail_port ?? '') }}">
                                @if ($errors->has('mail_port'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('mail_port') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="mail_username">{{ __('Mail Username') }}</label>
                                <input type="text" name="mail_username" id="mail_username" placeholder="username"
                                    class="form-control"
                                    value="{{ old('mail_username', $preference->mail_username ?? '') }}">
                                @if ($errors->has('mail_username'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('mail_username') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="mail_password">{{ __('Mail Password') }}</label>
                                <input type="password" name="mail_password" id="mail_password" placeholder=""
                                    class="form-control"
                                    value="{{ old('mail_password', $preference->mail_password ?? '') }}">
                                @if ($errors->has('mail_password'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('mail_password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="mail_encryption">{{ __('Mail Encryption') }}</label>
                                <input type="text" name="mail_encryption" id="mail_encryption" placeholder="username"
                                    class="form-control"
                                    value="{{ old('mail_encryption', $preference->mail_encryption ?? '') }}">
                                @if ($errors->has('mail_encryption'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('mail_encryption') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="mail_from">{{ __('Mail From') }}</label>
                                <input type="text" name="mail_from" id="mail_from" placeholder="service@xyz.com"
                                    class="form-control" value="{{ old('mail_from', $preference->mail_from ?? '') }}">
                                @if ($errors->has('mail_from'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('mail_from') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form><!-- Mail Configuration end -->
        </div>
        <div class="col-lg-6 mb-3">
            <!-- Firebase Notification Configuration start -->
            <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}" class="h-100" enctype="multipart/form-data">
                @csrf
                <div class="card-box h-100 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title text-uppercase mb-0">{{ __('Firebase Notification Configuration') }}
                        </h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                    </div>
                    <p class="sub-header">{{ __('View and update your Firebase Keys') }}</p>
                    <div class="row">
                        <div class="col-md-6">
                             <select class="form-control col-6"  name="fire_base_type" >
                            <option  value="FB" {{((getAdditionalPreference(['fire_base_type'])['fire_base_type'] == "FB" )? 'selected' : '')}}>Fire Base</option>
                            <option  value="AF" {{((getAdditionalPreference(['fire_base_type'])['fire_base_type'] == "AF" )? 'selected' : '')}}>App Flyer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_server_key">{{ __('Server Key') }}</label>
                                <input type="text" name="fcm_server_key" id="fcm_server_key" placeholder=""
                                    class="form-control"
                                    value="{{ old('fcm_server_key', $preference->fcm_server_key ?? '') }}" required>
                                @if ($errors->has('fcm_server_key'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_server_key') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="vendor_fcm_server_key">{{ __('Individual Vendor Server Key') }}</label>
                                <input type="text" name="vendor_fcm_server_key" id="vendor_fcm_server_key"
                                    placeholder="" class="form-control"
                                    value="{{ old('vendor_fcm_server_key', $preference->vendor_fcm_server_key ?? '') }}"
                                    required>
                                @if ($errors->has('vendor_fcm_server_key'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('vendor_fcm_server_key') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_api_key">{{ __('API Key') }}</label>
                                <input type="text" name="fcm_api_key" id="fcm_api_key" placeholder=""
                                    class="form-control"
                                    value="{{ old('fcm_api_key', $preference->fcm_api_key ?? '') }}" required>
                                @if ($errors->has('fcm_api_key'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_api_key') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_auth_domain">{{ __('Auth Domain') }}</label>
                                <input type="text" name="fcm_auth_domain" id="fcm_auth_domain" placeholder=""
                                    class="form-control"
                                    value="{{ old('fcm_auth_domain', $preference->fcm_auth_domain ?? '') }}" required>
                                @if ($errors->has('fcm_auth_domain'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_auth_domain') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_project_id">{{ __('Project ID') }}</label>
                                <input type="text" name="fcm_project_id" id="fcm_project_id" placeholder=""
                                    class="form-control"
                                    value="{{ old('fcm_project_id', $preference->fcm_project_id ?? '') }}" required>
                                @if ($errors->has('fcm_project_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_project_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_vendor_project_id">{{ __('Vendor Project ID') }}</label>
                                <input type="text" name="fcm_vendor_project_id" id="fcm_vendor_project_id" placeholder=""
                                    class="form-control"
                                    value="{{ old('fcm_vendor_project_id', $getAdditionalPreference['fcm_vendor_project_id']?? '') }}" required>
                                @if ($errors->has('fcm_vendor_project_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_vendor_project_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="firebase_account_json_file">{{ __('Firebase Service Account Json File') }}</label>
                                <input type="file" accept="json"  name="firebase_account_json_file" data-plugins="dropify"/>

                                @if ($errors->has('firebase_account_json_file'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('firebase_account_json_file') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="firebase_vendor_account_json_file">{{ __('Firebase Service Vendor Account Json File') }}</label>
                                <input type="file" accept="json"  name="firebase_vendor_account_json_file" data-plugins="dropify"/>

                                @if ($errors->has('firebase_vendor_account_json_file'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('firebase_vendor_account_json_file') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_storage_bucket">{{ __('Storage Bucket') }}</label>
                                <input type="text" name="fcm_storage_bucket" id="fcm_storage_bucket" placeholder=""
                                    class="form-control"
                                    value="{{ old('fcm_storage_bucket', $preference->fcm_storage_bucket ?? '') }}"
                                    required>
                                @if ($errors->has('fcm_storage_bucket'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_storage_bucket') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_messaging_sender_id">{{ __('Messaging Sender ID') }}</label>
                                <input type="text" name="fcm_messaging_sender_id" id="fcm_messaging_sender_id"
                                    placeholder="" class="form-control"
                                    value="{{ old('fcm_messaging_sender_id', $preference->fcm_messaging_sender_id ?? '') }}"
                                    required>
                                @if ($errors->has('fcm_messaging_sender_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_messaging_sender_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_app_id">{{ __('App ID') }}</label>
                                <input type="text" name="fcm_app_id" id="fcm_app_id" placeholder=""
                                    class="form-control" value="{{ old('fcm_app_id', $preference->fcm_app_id ?? '') }}"
                                    required>
                                @if ($errors->has('fcm_app_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_app_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="fcm_measurement_id">{{ __('Measurement ID') }}</label>
                                <input type="text" name="fcm_measurement_id" id="fcm_measurement_id" placeholder=""
                                    class="form-control"
                                    value="{{ old('fcm_measurement_id', $preference->fcm_measurement_id ?? '') }}">
                                @if ($errors->has('fcm_measurement_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('fcm_measurement_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form><!-- Firebase Notification Configuration end -->
        </div>


        <!-- Customer Support -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="row h-100">
                <div class="col-12">
                    <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}" class="h-100">
                        @csrf
                        <div class="card-box h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h4 class="header-title text-uppercase mb-0">{{ __('Customer Support') }}</h4>
                                <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                            </div>
                            <p class="sub-header">
                                {{ __("View and update your Customer Support, it's API key and Application ID") }}</p>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label for="customer_support">{{ __('Customer Support') }}</label>
                                        <select class="form-control" id="customer_support" name="customer_support">
                                            <option value="zen_desk"
                                                {{ isset($preference) && $preference->customer_support == 'zen_desk' ? 'selected' : '' }}>
                                                {{ __('Zen Desk') }}
                                            </option>
                                        </select>
                                        @if ($errors->has('customer_support'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ $errors->first('customer_support') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group mt-3 mb-0">
                                        <label for="customer_support_key">{{ __('API Key') }}</label>
                                        <input type="text" name="customer_support_key" id="customer_support_key"
                                            placeholder="{{ __('Please enter key') }}" class="form-control"
                                            value="{{ old('customer_support_key', $preference->customer_support_key ?? '') }}">
                                        @if ($errors->has('customer_support_key'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ $errors->first('customer_support_key') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group mt-3 mb-0">
                                        <label for="customer_support_application_id">{{ __('Application ID') }}</label>
                                        <input type="text" name="customer_support_application_id"
                                            id="customer_support_application_id"
                                            placeholder="{{ __('Please enter application ID') }}" class="form-control"
                                            value="{{ old('customer_support_application_id', $preference->customer_support_application_id ?? '') }}">
                                        @if ($errors->has('customer_support_application_id'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ $errors->first('customer_support_application_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Customer Support end -->


        <!-- sos Support -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-box h-100">
                <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}" class="h-100">
                    @csrf
                    <input type="hidden" name="sos_enable" value="1">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="sos" class="d-flex align-items-center justify-content-between">
                                    <h5 class="social_head"> <span>SOS</span></h5>
                                    <button class="btn btn-info btn-block save_btn" type="submit">
                                        {{ __('Save') }} </button>
                                </label>
                                <label for="" class="mr-3">{{ __('Enable') }}</label>
                                <input type="checkbox" data-plugin="switchery" name="sos" id="sos"
                                    class="form-control" data-color="#43bee1"
                                    @if (isset($preference) && $preference->sos == '1') checked='checked' @endif>
                            </div>
                        </div>
                    </div>
                    <div class="row  sos_row"
                        style="{{ isset($preference) && $preference->sos == '1' ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="sos_police_contact"></label>{{ __('Police Number') }}</label>
                                <input type="text" name="sos_police_contact" id="sos_police_contact"
                                    placeholder="" class="form-control"
                                    value="{{ old('twitter_client_id', $preference->sos_police_contact ?? '') }}">
                                @if ($errors->has('sos_police_contact'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('sos_police_contact') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="sos_ambulance_contact">{{ __('Ambulance Number') }}</label>
                                <input type="text" name="sos_ambulance_contact" id="sos_ambulance_contact"
                                    placeholder="" class="form-control"
                                    value="{{ old('sos_ambulance_contact', $preference->sos_ambulance_contact ?? '') }}">
                                @if ($errors->has('sos_ambulance_contact'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('sos_ambulance_contact') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>FB
                    </div>
                </form>
            </div><!-- Twitter card end -->
        </div>
        <!-- Customer Support end -->


    </div>


    <div class="row map-configration_dashboard">
        {{-- Third party Accounting --}}
       
        {{-- ends here Third party Accounting form --}}

        {{-- starts square POS integration --}}
        

        {{-- starts gofrugal POS integration --}}
     


        @if (isset($preference) && $preference->subscription_mode == '1')
            <div class="col-xl-4 col-lg-4 h-100">
                <div class="page-title-box">
                    <h4 class="page-title text-uppercase">
                        {{ getNomenclatureName('Vendors', true) . __(' Subscription rule') }}</h4>
                </div>

                <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                    @csrf
                    <input type="hidden" name="vendor_subcription_rule" id="vendor_subcription_rule"
                        value="1">
                    <!-- HubSpot card start -->
                    <div class="card-box h-100">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0 switchery-demo">
                                    <label class="d-flex align-items-center justify-content-between">
                                        <h5 class="social_head text-uppercase">
                                            <span>{{ __('Subscription rule') }}</span>
                                        </h5>

                                        <button class="btn btn-info btn-block save_btn" type="submit">
                                            {{ __('Save') }} </button>
                                    </label>
                                    <p class="sub-header">
                                        {{ __('vendor not showing in if thay dont have any Subscription') }}
                                    </p>
                                </div>
                                <div class="form-group mb-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <div
                                                class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                                <label for="is_show_vendor_on_subcription_switch"
                                                    class="mr-3">{{ __('Enable') }} <br /></label>
                                                <input type="checkbox" data-plugin="switchery"
                                                    name="is_show_vendor_on_subcription_switch"
                                                    id="is_show_vendor_on_subcription_switch"
                                                    class="form-control checkbox_change"
                                                    data-className="is_show_vendor_on_subcription" data-color="#43bee1"
                                                    @if ($getAdditionalPreference['is_show_vendor_on_subcription'] == 1) checked @endif>
                                                <input type="hidden"
                                                    @if ($getAdditionalPreference['is_show_vendor_on_subcription'] == 1) value="1" @else value="0" @endif
                                                    name="is_show_vendor_on_subcription"
                                                    id="is_show_vendor_on_subcription" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- <div class="col-xl-4 col-lg-4 h-100">
            <div class="page-title-box">
                <h4 class="page-title text-uppercase">Influencer Module</h4>
            </div>

            <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                @csrf
                <input type="hidden" name="influencer_mode" value="1">
                <div class="card-box h-100">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <label class="d-flex align-items-center justify-content-between">
                                    <h5 class="social_head text-uppercase">
                                        <span>{{ __('Manage Influencer') }}</span>
                                    </h5>
                                    <button class="btn btn-info btn-block save_btn" type="submit">
                                        {{ __('Save') }} </button>
                                </label>
                            </div>

                            @if ($client_preference_detail->business_type != 'taxi' && $client_preference_detail->business_type != 'laundry')
                                <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                    <label for="is_influencer_refer_and_earn_switch"
                                        class="mr-2 mb-0">{{ __('Influencer Module') }}<small
                                            class="d-block pr-5">{{ __('Enable to allow influencer module.') }}</small></label>
                                    <span> <input type="checkbox" data-plugin="switchery"
                                            name="is_influencer_refer_and_earn_switch"
                                            id="is_influencer_refer_and_earn_switch"
                                            class="form-control checkbox_change"
                                            data-className="is_influencer_refer_and_earn" data-color="#43bee1"
                                            @if ($getAdditionalPreference['is_influencer_refer_and_earn'] == 1) checked='checked' @endif>
                                    </span>
                                    <input type="hidden"
                                        @if ($getAdditionalPreference['is_influencer_refer_and_earn'] == 1) value="1" @else value="0" @endif
                                        name="is_influencer_refer_and_earn" id="is_influencer_refer_and_earn" />
                                </div>


                                <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                                    <label for="celebrity_check" class="mr-2 mb-0">
                                        {{ __('Refer and Earn by Influencer') }}
                                        <small
                                            class="d-block pr-5">{{ __('Leverage the Influencer era by adding Influencers and associate product with them to create curated lists of products') }}.</small></label>
                                    <span> <input type="checkbox" data-plugin="switchery" name="celebrity_check"
                                            id="celebrity_check" class="form-control" data-color="#43bee1"
                                            @if (isset($preference) && $preference->celebrity_check == '1') checked='checked' @endif></span>

                                </div>
                            @endif

                        </div>
                    </div>
                </div>


            </form>
        </div> -->
{{--
        <div class="col-xl-4 col-lg-4 mb-3">
            <div class="page-title-box">
                <h4 class="page-title text-uppercase">{{ __('Notification for Pickup Delivery') }}</h4>
            </div>

            <form method="POST" action="{{ route('additional.update') }}">
                <input type="hidden" name="crm" id="crm" value="1">
                <input type="hidden" name="send_to" id="send_to" value="configure">
                @csrf
                <!-- HubSpot card start -->
                <div class="card-box h-100">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h4 class="header-title text-uppercase mb-0">{{ __('Custom Notification') }}
                                    </h4>
                                    <button class="btn btn-info d-block" type="submit"> {{ __('Save') }}
                                    </button>
                                </div>
                                <p class="sub-header">
                                    {{ __('Change Notification time for scheduled pickup delivery order.') }}
                                </p>

                            </div>
                        </div>

                        <div class="col-12">

                            <div class="form-group mb-0 d-flex switchery-demo">
                                <label for="" class="mr-3">{{ __('Cron Notification Enable') }}</label>
                                <input type="checkbox" data-plugin="switchery" name="pickup_notification_switch"
                                    id="pickup_notification_switch" class="form-control checkbox_change"
                                    data-className="pickup_notification_before" data-color="#43bee1"
                                    @if (@$getAdditionalPreference['pickup_notification_before'] == 1) checked='checked' @endif>
                                <input type="hidden"
                                    @if (@$getAdditionalPreference['pickup_notification_before'] == 1) value="1" @else value="0" @endif
                                    name="pickup_notification_before" id="pickup_notification_before" />
                            </div>

                            <div class="row mt-2" id="pickup_notification_div"
                                style="display:@if (@$getAdditionalPreference['pickup_notification_before'] == 1) @else none @endif;">
                                <div class="col-8">
                                    <label for=""
                                        class="mr-3">{{ __('Accept Reject Notification (Minutes)') }}</label>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="pickup_notification_before_hours"
                                        id="pickup_notification_before_hours" placeholder="" class="form-control"
                                        value="{{ old('pickup_notification_before_hours', @$getAdditionalPreference['pickup_notification_before_hours'] ?? '') }}">
                                </div>
                                <hr />

                                <div class="col-8">
                                    <label for=""
                                        class="mr-3">{{ __('Reminder Notification (Minutes)') }}</label>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="pickup_notification_before2_hours"
                                        id="pickup_notification_before2_hours" placeholder="" class="form-control"
                                        value="{{ old('pickup_notification_before2_hours', @$getAdditionalPreference['pickup_notification_before2_hours'] ?? '') }}">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
--}}

        {{-- marg form --}}
        {{-- <div class="col-xl-4 col-lg-4 mb-3">
            <!-- Social Logins title start -->
            <div class="page-title-box">
                <h4 class="page-title text-uppercase">Marg</h4>
            </div><!-- Social Logins title end -->
                <div class="card-box">

            <form method="POST" action="{{ route('additional.update') }}">
                @csrf
                <!-- marg card start -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="fb_login" class="d-flex align-items-center justify-content-between">
                                    <h5 class="social_head"><i style="font-size: 24px;" class="mdi mdi-marg"></i>
                                        <span>{{ __('Marg') }}</span>
                                    </h5>

                                    <button class="btn btn-info btn-block save_btn" name="marg_submit"
                                        type="submit">{{ __('Save') }} </button>
                                </label>
                                <label for="" class="mr-3">{{ __('Enable') }}</label>
                                <input type="checkbox" data-plugin="switchery" id="is_marg_enable"
                                    class="form-control checkbox_change" data-className="is_marg_enable_hidden"
                                    data-color="#43bee1"
                                    @if (@$getAdditionalPreference['is_marg_enable'] == '1') checked='checked' value="1" @endif>
                                <input type="hidden"
                                    @if (isset($getAdditionalPreference['is_marg_enable']) == 1) value="1" @else value="0" @endif
                                    name="is_marg_enable" id="is_marg_enable_hidden" />

                                @if (isset($getAdditionalPreference['is_marg_enable']) == 1 && $getAdditionalPreference['marg_date_time'])
                                    <label for="" id="sycn_time" class="ml-3">{{ __('Last Sync Date & Time :') }}
                                    {{ convertDateTimeInClientTimeZone($getAdditionalPreference['marg_date_time'], 'd-m-Y h:i:s') }}</label >
                                @endif

                            </div>
                        </div>
                    </div>


                    <div class="row marg_row"
                        style="{{ isset($getAdditionalPreference['is_marg_enable']) && $getAdditionalPreference['is_marg_enable'] == 1 ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="marg_company_url">{{ __('Marg Company Url') }}</label>
                                <input type="text" name="marg_company_url" id="marg_company_url"
                                    placeholder="" class="form-control"
                                    value="{{ old('marg_company_url', $getAdditionalPreference['marg_company_url'] ?? '') }}">
                                @if ($errors->has('marg_company_url'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('marg_company_url') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>


                    <div class="row marg_row"
                        style="{{ isset($getAdditionalPreference['is_marg_enable']) && $getAdditionalPreference['is_marg_enable'] == 1 ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="marg_company_code">{{ __('Company Code') }}</label>
                                <input type="text" name="marg_company_code" id="marg_company_code"
                                    placeholder="" class="form-control"
                                    value="{{ old('marg_company_code', $getAdditionalPreference['marg_company_code'] ?? '') }}">
                                @if ($errors->has('marg_company_code'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('marg_company_code') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row marg_row"
                        style="{{ isset($getAdditionalPreference['is_marg_enable']) && $getAdditionalPreference['is_marg_enable'] == 1 ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="marg_access_token">{{ __('Marg ID') }}</label>
                                <input type="text" name="marg_access_token" id="marg_access_token"
                                    placeholder="" class="form-control"
                                    value="{{ old('marg_access_token', $getAdditionalPreference['marg_access_token'] ?? '') }}">
                                @if ($errors->has('marg_access_token'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('marg_access_token') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row marg_row"
                        style="{{ isset($getAdditionalPreference['is_marg_enable']) && $getAdditionalPreference['is_marg_enable'] == 1 ? '' : 'display:none;' }}">
                        <div class="col-12">
                            <div class="form-group mb-2 mt-2">
                                <label for="marg_decrypt_key">{{ __('Decrypt Key') }}</label>
                                <input type="text" name="marg_decrypt_key" id="marg_decrypt_key" placeholder=""
                                    class="form-control"
                                    value="{{ old('marg_decrypt_key', $getAdditionalPreference['marg_decrypt_key'] ?? '') }}">
                                @if ($errors->has('marg_decrypt_key'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('marg_decrypt_key') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
            </form>

            <div class="row marg_row"
                style="{{ isset($getAdditionalPreference['is_marg_enable']) && $getAdditionalPreference['is_marg_enable'] == 1 ? '' : 'display:none;' }}">
                <div class="col-12">
       @php
        {
                $marg_order =  App\Models\Order::where('marg_status', '=',null)->
                  where('marg_max_attempt', '>',2)->first();
                 $class= "";
                 if($marg_order){
                    $class= "disabled";
                 }
        }
        @endphp
                <button class="btn btn-info btn-block" id="sync_marg_btn" {{$class }}>{{ __('Sync Data') }} </button>

                </div>
            </div>
        </div><!-- marg card end --> --}}
   {{-- 
    <div class="col-xl-4 col-lg-4 mb-3">
        <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __('Marg Cron Schedular') }}</h4>
        </div>
        <div class="card-box">
        <form method="POST" action="{{ route('additional.update') }}">
            <input type="hidden" name="crm" id="crm" value="1">
            <input type="hidden" name="send_to" id="send_to" value="configure">
            @csrf

            <div class="d-flex align-items-center justify-content-between mb-2">
                <h4 class="header-title text-uppercase mb-0">{{ __('Marg Cron Schedular') }}
                </h4>
                <button class="btn btn-info d-block" type="submit"> {{ __('Save') }}
                </button>
            </div>

            <div class="col-12">

                <div class="form-group mb-0 d-flex switchery-demo">
                    <label for="" class="mr-3">{{ __('Select Marg Cron Configuration') }}</label>
                </div>
                @if (@$getAdditionalPreference['marg_cron_schedular_time'] != 0 && @$getAdditionalPreference['marg_cron_schedular_time'] != '')
                    @php
                        $marg_cron_schedular_time = '';
                    @endphp
                @else
                    @php
                        $marg_cron_schedular_time = $getAdditionalPreference['marg_cron_schedular_time'];
                    @endphp
                @endif
                <select name="marg_cron_schedular_time" id="" class="form-control">
                    <option value="">{{ __("Select Marg Cron") }}</option>
                    <option @if($marg_cron_schedular_time === "everyMinute") selected @endif value="everyMinute">{{ __("everyMinute")}}</option>
                    <option @if($marg_cron_schedular_time === "everyFiveMinutes") selected @endif value="everyFiveMinutes">{{ __("everyFiveMinutes")}}</option>
                    <option @if($marg_cron_schedular_time === "everyTenMinutes") selected @endif value="everyTenMinutes">{{ __("everyTenMinutes")}}</option>
                    <option @if($marg_cron_schedular_time === "everyFifteenMinutes") selected @endif value="everyFifteenMinutes">{{ __("everyFifteenMinutes")}}</option>
                    <option @if($marg_cron_schedular_time === "everyThirtyMinutes") selected @endif value="everyThirtyMinutes">{{ __("everyThirtyMinutes")}}</option>
                    <option @if($marg_cron_schedular_time === "hourly") selected @endif value="hourly">{{ __("hourly")}}</option>
                    <option @if($marg_cron_schedular_time === "everyTwoHours") selected @endif value="everyTwoHours">{{ __("everyTwoHours")}}</option>
                    <option @if($marg_cron_schedular_time === "everyThreeHours") selected @endif value="everyThreeHours">{{ __("everyThreeHours")}}</option>
                    <option @if($marg_cron_schedular_time === "everySixHours") selected @endif value="everySixHours">{{ __("everySixHours")}}</option>
                    <option @if($marg_cron_schedular_time === "daily") selected @endif value="daily">{{ __("daily")}}</option>
                    <option @if($marg_cron_schedular_time === "twiceDaily") selected @endif value="twiceDaily">{{ __("twiceDaily")}}</option>
                    <option @if($marg_cron_schedular_time === "weekly") selected @endif value="weekly">{{ __("weekly")}}</option>
                    <option @if($marg_cron_schedular_time === "monthly") selected @endif value="monthly">{{ __("monthly")}}</option>
                </select>

            </div>
        </form>
        </div>
    </div>
    --}}

    <!-- <div class="col-xl-4 col-lg-4 mb-3">
        <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __('Vendor Notification Product Stock') }}</h4>
        </div>
        <div class="card-box">
        <form method="POST" action="{{ route('additional.update') }}">
            <input type="hidden" name="crm" id="crm" value="1">
            <input type="hidden" name="send_to" id="send_to" value="configure">
            @csrf

            <div class="d-flex align-items-center justify-content-between mb-2">
                <h4 class="header-title text-uppercase mb-0">{{ __('Custom Notification') }}
                </h4>
                <button class="btn btn-info d-block" type="submit"> {{ __('Save') }}
                </button>
            </div>
            <p class="sub-header">
                {{ __('Send Vendor Notification when Product Stock Out.') }}
            </p>

                <div class="form-group mb-0 d-flex switchery-demo">
                    <label for="" class="mr-3">{{ __('Notification Enable') }}</label>
                    <input type="checkbox" data-plugin="switchery" name="stock_notification_switch"
                        id="stock_notification_switch" class="form-control checkbox_change"
                        data-className="stock_notification_before" data-color="#43bee1"
                        @if (@$getAdditionalPreference['stock_notification_before'] == 1) checked='checked' @endif>
                    <input type="hidden" @if (@$getAdditionalPreference['stock_notification_before'] == 1) value="1" @else value="0" @endif
                        name="stock_notification_before" id="stock_notification_before" />
                </div>

                <div class="row mt-2" id="stock_notification_div"
                    style="display:@if (@$getAdditionalPreference['stock_notification_before'] == 1) @else none @endif;">
                    <div class="col-8">
                        <label for="" class="mr-3">{{ __('Reminder For Minimum Product Quantity') }}</label>
                    </div>
                    <div class="col-4">
                        <input type="number" name="stock_notification_qunatity" id="stock_notification_qunatity"
                            placeholder="" class="form-control"
                            value="{{ old('stock_notification_qunatity', @$getAdditionalPreference['stock_notification_qunatity'] ?? '') }}">
                    </div>
                    <hr />
                </div>
            </div>
        </form>
        </div>
        <div class="col-xl-4 col-lg-4 mb-3">
            <form method="POST" class="h-100" action="{{ route('additional.update')}}">
            @csrf
                <input type="hidden" name="is_lumen" value="1">
                <div class="card-box h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="header-title text-uppercase mb-0">{{__("Lumen")}}</h4>
                        <button class="btn btn-outline-info d-block" type="submit"> {{__('Save')}} </button>
                    </div>
                    <div class="row align-items-start">
                        <div class="col-md-12">
                            <div class="form-group d-flex justify-content-between mb-3">
                                <label for="lumen" class="mr-2 mb-0">{{__("Enable")}} </label>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input " id="is_lumen_enabled" name="is_lumen_enabled" {{ ($getAdditionalPreference['is_lumen_enabled'] && ($getAdditionalPreference['is_lumen_enabled'] == 'on'))  ? 'checked':'' }}>
                                        <label class="custom-control-label" for="is_lumen_enabled"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="row lumen-field" style="display:{{ ($getAdditionalPreference['is_lumen_enabled'] && ($getAdditionalPreference['is_lumen_enabled'] == 'on'))  ? 'block':'none' }}">
                <div class="col-12 ">
                    <div class="form-group mb-3">
                        <div class="domain-outer border-0 d-flex align-items-center justify-content-between">
                            <label for="lumen_domain_url">{{ __('LUMEN DOMAIN URL') }}</label>

                        </div>
                        <input type="text" name="lumen_domain_url" id="lumen_domain_url"
                            placeholder="" class="form-control"
                            value="{{ old('lumen_domain_url', $getAdditionalPreference['lumen_domain_url'] ?? '') }}">
                        @if ($errors->has('lumen_domain_url'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('lumen_domain_url') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
                        </div>
                        <div class="row lumen-field" style="display: {{ ($getAdditionalPreference['is_lumen_enabled'] && ($getAdditionalPreference['is_lumen_enabled'] == 'on'))  ? 'block':'none' }}">

                <div class="col-12">
                    <div class="form-group mb-3">
                        <div class="domain-outer border-0 d-flex align-items-center justify-content-between">
                            <label for="lumen_access_token">{{ __('LUMEN ACCESS TOKEN') }}</label>
                            <span class="text-right col-6 col-md-6"><a
                                    href="javascript: generateLumenToken();">{{ __('Generate Key') }}</a></span>

                        </div>
                        <input type="text" name="lumen_access_token" id="lumen_access_token"
                            placeholder="kjadsasd66asdas" class="form-control"
                            value="{{ old('lumen_access_token',  $getAdditionalPreference['lumen_access_token'] ?? '') }}">
                        @if ($errors->has('lumen_access_token'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('lumen_access_token') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
                </div>

                </div>
            </form>

    </div> -->
</div>

    <!-- <div class="col-xl-4 col-lg-4 mb-3">
        <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __("Tax-Jar")}}</h4>
        </div>

        <form method="POST" action="{{ route('additional.update') }}">
            @csrf
            <div class="card-box h-100">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-0 switchery-demo">
                            <label for="fb_login" class="d-flex align-items-center justify-content-between">
                                <h5 class="social_head"><i style="font-size: 24px;" class="mdi mdi-marg"></i>
                                    <span>{{ __('Tax-Jar Api') }}</span>
                                </h5>

                                <button class="btn btn-info btn-block save_btn" name="taxjar_submit" type="submit">{{ __('Save') }} </button>
                            </label>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <label for="is_taxjar_enable" class="mr-3">{{ __('Enable') }}</label>
                                    <input type="checkbox" data-plugin="switchery" id="is_taxjar_enable" class="form-control checkbox_change" data-className="is_taxjar_enable_hidden" data-color="#43bee1"
                                        @if (@$getAdditionalPreference['is_taxjar_enable'] == '1') checked='checked' value="1" @endif>
                                        <input type="hidden" @if (isset($getAdditionalPreference['is_taxjar_enable']) == 1) value="1" @else value="0" @endif
                                        name="is_taxjar_enable" id="is_taxjar_enable_hidden" />
                                </div>

                                <div>
                                    <label for="taxjar_testmode" class="mr-3">{{ __('Sandbox') }}</label>
                                    <input type="checkbox" data-plugin="switchery" id="taxjar_testmode" class="form-control checkbox_change" data-className="taxjar_testmode_hidden" data-color="#43bee1"
                                        @if (@$getAdditionalPreference['taxjar_testmode'] == '1') checked='checked' value="1" @endif>
                                        <input type="hidden" @if (isset($getAdditionalPreference['taxjar_testmode']) == 1) value="1" @else value="0" @endif
                                        name="taxjar_testmode" id="taxjar_testmode_hidden" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row taxjar_row"
                    style="{{ isset($getAdditionalPreference['is_taxjar_enable']) && $getAdditionalPreference['is_taxjar_enable'] == 1 ? '' : 'display:none;' }}">
                    <div class="col-12">
                        <div class="form-group mb-2 mt-2">
                            <label for="taxjar_api_token">{{ __('Api Token') }}</label>
                            <input type="text" name="taxjar_api_token" id="taxjar_api_token"
                                placeholder="" class="form-control"
                                value="{{ old('taxjar_api_token', $getAdditionalPreference['taxjar_api_token'] ?? '') }}">
                            @if ($errors->has('taxjar_api_token'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('taxjar_api_token') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
        </form>



    </div> -->

    {{-- ends here marg form --}}
    </div>
    <!-- <div class="col-xl-4 col-lg-4 mb-3">
        <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __('Blockchain Route Formation') }}</h4>
        </div>
        <div class="card-box">
        <form method="POST" action="{{ route('additional.update') }}">
        <input type="hidden" name="is_blockchain_route" id="is_blockchain_route" value="1">
          @csrf

            <div class="d-flex align-items-center justify-content-between mb-2">
                <h4 class="header-title text-uppercase mb-0">{{ __('Blockchain Route Formation') }}
                </h4>
                <button class="btn btn-info d-block" type="submit"> {{ __('Save') }}
                </button>
            </div>


            <div class="col-12">

                <div class="form-group mb-0 d-flex switchery-demo">
                    <label for="" class="mr-3">{{ __('Enable') }}</label>
                    <input type="checkbox" data-plugin="switchery" name="blockchain_route_formation_switch"
                        id="blockchain_route_formation_switch" class="form-control checkbox_change"
                        data-className="blockchain_route_formation" data-color="#43bee1"
                        @if (@$getAdditionalPreference['blockchain_route_formation'] == 1) checked @endif>
                    <input type="hidden" @if (@$getAdditionalPreference['blockchain_route_formation'] == 1) value="1" @else value="0" @endif
                        name="blockchain_route_formation" id="blockchain_route_formation" />
                </div>

                <div class="row mt-2  @if (@$getAdditionalPreference['blockchain_route_formation'] != 1) d-none @endif;" id="blockchain_configuration_div">

                    <div class="col-6">
                    <label for="" class="mr-3">{{ __('Blockchain Api Domain') }}</label>
                        <input type="text" name="blockchain_api_domain" id="blockchain_api_domain"
                            placeholder="" class="form-control"
                            value="{{ old('blockchain_api_domain', @$getAdditionalPreference['blockchain_api_domain'] ?? '') }}">
                    </div>
                    <div class="col-6">
                    <label for="" class="mr-3">{{ __('Blockchain Address Id') }}</label>
                        <input type="text" name="blockchain_address_id" id="blockchain_address_id"
                            placeholder="" class="form-control"
                            value="{{ old('blockchain_address_id', @$getAdditionalPreference['blockchain_address_id'] ?? '') }}">
                    </div>
                    <hr />
                </div>

                </form>
            </div>
        </div>
    </div> -->

    <div class="col-xl-4 col-lg-4 mb-3">
        <div class="page-title-box">
            <h4 class="page-title text-uppercase">{{ __('Video Conferencing') }}</h4>
        </div>
        <div class="card-box">
            <form method="POST" action="{{ route('configure.update', ['code' => $client_preference_detail->client_code]) }}" enctype="multipart/form-data">
                @csrf

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title text-uppercase mb-0">
                        {{ __('Jitsi Configuration') }}
                    </h4>

                    <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                </div>


                <div class="col-12">
                    <div class="form-group mb-2">
                        <label for="jitsi_domain_url" class="form-label">{{ __('Jitsi Domain') }}</label>
                        <input type="text" name="jitsi_domain_url" value="{{ @$client_preference_detail->jitsi_preferences['domain_url'] }}"
                               class="form-control" />
                    </div>

                    <div class="form-group mb-2">
                        <label for="jitsi_api_key" class="form-label">{{ __('Jitsi Api Key') }}</label>
                        <input type="text" name="jitsi_api_key" value="{{ @$client_preference_detail->jitsi_preferences['api_key'] }}"
                               class="form-control" />
                    </div>

                    <div class="form-group mb-2">
                        <label for="jitsi_api_secret" class="form-label">{{ __('Jitsi Api Secret') }}</label>
                        <input type="password" name="jitsi_api_secret" value="{{ @$client_preference_detail->jitsi_preferences['api_secret'] }}"
                               class="form-control" />
                    </div>

                    <div class="form-group mb-2">
                        <label for="jitsi_private_key" class="form-label">{{ __('Jitsi Private Key') }}</label>
                        <input type="file" name="jitsi_private_key" data-plugins="dropify" />
                    </div>
                </div>
            </form>
        </div>
    </div>

      @if( Request::get('google_tag'))
        <div class="col-xl-4 col-lg-4 mb-3">
            <!-- Social Logins title start -->
            <div class="page-title-box">
                <h4 class="page-title text-uppercase">{{ __('Google Analytics') }}</h4>
            </div><!-- Social Logins title end -->
                            <div class="card-box">


            <form method="POST" action="{{ route('additional.update') }}">
                <input type="hidden" name="crm" id="crm" value="1">
                <input type="hidden" name="send_to" id="send_to" value="configure">
                @csrf
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title text-uppercase mb-0">{{ __('Google Analytics') }}
                    </h4>
                    <button class="btn btn-info d-block" type="submit"> {{ __('Save') }}
                    </button>
                </div>
                            <div class="col-12">

                <!-- HubSpot card start -->
                    <div class="form-group d-flex justify-content-between mb-3 alCustomToggleColor">
                        <label for="is_enable_google_analytics_switch" class="mr-2 mb-0">{{ __('Enable Google Analytics') }}</label>
                        <span> <input type="checkbox" data-plugin="switchery" name="is_enable_google_analytics"
                                id="is_enable_google_analytics_switch" class="form-control checkbox_change"
                                data-className="is_enable_google_analytics" data-color="#43bee1"
                                 @if (@$getAdditionalPreference['is_enable_google_analytics'] == 1) checked='checked' @endif>
                        </span>
                        <input type="hidden" @if ($getAdditionalPreference['is_enable_google_analytics'] == 1) value="1" @else value="0" @endif
                            name="is_enable_google_analytics" id="is_enable_google_analytics" />
                    </div>

                   <div class="form-group mt-3 mb-0">
                        <label for="header_script">{{ __('Header Script') }}</label>
                        <textarea class="form-control m-0" id="header_script" rows="5" name="header_script" cols="10">{{ old('header_script', @$getAdditionalPreference['header_script'] ?? '') }}</textarea>

                    </div>
                    <div class="form-group mt-3 mb-0">
                        <label for="footer_script">{{ __('Footer Script') }}</label>
                        <textarea class="form-control m-0" id="footer_script" rows="5" name="footer_script" cols="10">{{ old('footer_script', @$getAdditionalPreference['footer_script'] ?? '') }}</textarea>

                    </div>
                </div>
            </form>
        </div>
        </div>
	@endif
</div>
<div class="card-box h-100 h-100">
    <div class="row" style="margin-left: 14%;">

        <div class="col-md-12">
            <!-- Custom Mods start -->
            <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                @csrf
                <div class="card-box h-100 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="header-title text-uppercase mb-0">{{ __('Custom Mods') }}</h4>
                        <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                    </div>
                    <input type="hidden" name="custom_mods_config" id="custom_mods_config" value="1">

                    <div class="row align-items-start">
                        @include('backend.setting.customMode')
                    </div>


                </div>
                        </form>

        </div>
    </div>
        <!-- Custom Mods end -->
    </div>

    <div style="display:none;">
        <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
            @csrf
            <div class="row">
                <div class="col-xl-11 col-md-offset-1">
                    <div class="card-box">
                        <h4 class="header-title text-uppercase">{{ __('Email') }}</h4>
                        <p class="sub-header">
                            {{ __("Choose Email paid plan to whitelable 'From email address' and 'Sender Name' in the Email sent out from your account.") }}
                        </p>
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email_plan">{{ __('CURRENT SELECTION') }}</label>
                                    <select class="form-control" id="email_plan" name="email_plan">
                                        <option>{{ __('Select Plan') }}</option>
                                        <option value="free"
                                            {{ isset($preference) && $preference->email_plan == 'free' ? 'selected' : '' }}>
                                            {{ __('Free') }}
                                        </option>
                                        <option value="paid"
                                            {{ isset($preference) && $preference->email_plan == 'paid' ? 'selected' : '' }}>
                                            {{ __('Paid') }}
                                        </option>
                                    </select>
                                    @if ($errors->has('email_plan'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('email_plan') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div style="display:none;">
            <form method="POST" action="{{ route('configure.update', Auth::user()->code) }}">
                @csrf
                <div class="row">
                    <div class="col-xl-11 col-md-offset-1">
                        <div class="card-box">
                            <h4 class="header-title text-uppercase">{{ __('Email') }}</h4>
                            <p class="sub-header">
                                {{ __("Choose Email paid plan to whitelable 'From email address' and 'Sender Name' in the Email sent out from your account.") }}
                            </p>
                            <div class="row mb-0">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="email_plan">{{ __('CURRENT SELECTION') }}</label>
                                        <select class="form-control" id="email_plan" name="email_plan">
                                            <option>{{ __('Select Plan') }}</option>
                                            <option value="free"
                                                {{ isset($preference) && $preference->email_plan == 'free' ? 'selected' : '' }}>
                                                {{ __('Free') }}
                                            </option>
                                            <option value="paid"
                                                {{ isset($preference) && $preference->email_plan == 'paid' ? 'selected' : '' }}>
                                                {{ __('Paid') }}
                                            </option>
                                        </select>
                                        @if ($errors->has('email_plan'))
                                            <span class="text-danger" role="alert">
                                                <strong>{{ $errors->first('email_plan') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="sms_service_api_key">{{ __('PREVIEW') }}</label>
                                        <div class="card">
                                            <div class="card-body">
                                                <p class="mb-2"><span
                                                        class="font-weight-semibold mr-2">{{ __('From') }}:</span>
                                                    johndoe<span>
                                                    </span>contact@royodispatcher.com<span> ></span>
                                                </p>
                                                <p class="mb-2"><span
                                                        class="font-weight-semibold mr-2">{{ __('Reply To') }}:</span>
                                                    johndoe@gmail.com
                                                </p>
                                                <p class="mt-3 text-center">
                                                    {{ __('Your message note here..') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-2">
                                    <div class="form-group mb-0 text-center">
                                        <button class="btn btn-info btn-block" type="submit"> {{ __('Save') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div id="show-map-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" data-bs-backdrop="static" style="display: none;">
        <div class="modal-dialog modal-full-width">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Select Location') }}</h4>
                    <button type="button" class="close remove-modal-open" data-dismiss="modal"
                        aria-hidden="true">×</button>
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
                    <button type="submit"
                        class="btn btn-info waves-effect waves-light  remove-modal-open selectMapLocation">{{ __('Ok') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div id="custom-mode-verfication-modal" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true" data-bs-backdrop="static" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Custom Mods Verification') }}</h4>
                    <button type="button" class="close remove-modal-open" data-dismiss="modal"
                        aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <form id="task_form" action="#" method="POST" style="width: 100%">
                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <label for="verification_code">{{ __('Verification Code') }}</label>
                                    <input type="password" name="verification_code" id="verification_code"
                                        placeholder="Enter Verification Code" class="form-control" value="">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit"
                        class="btn btn-info waves-effect waves-light  remove-modal-open verification-code-sbt">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>


    <div id="add_driver_registration_document_modal" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="standard-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="standard-modalLabel">{{ __('Add Driver Registration Document') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form id="driverRegistrationDocumentForm" method="POST" action="javascript:void(0)">
                        @csrf
                        <div id="save_social_media">
                            <input type="hidden" name="driver_registration_document_id" value="">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group position-relative">
                                        <label for="">{{ __('Type') }}</label>
                                        <div class="input-group mb-2">
                                            <select class="form-control" name="file_type">
                                                @forelse($file_types_driver as $k => $file_type)
                                                    <option value="{{ $file_type }}">{{ $file_type }}
                                                    </option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @forelse($client_languages as $k => $client_language)
                                    <div class="col-md-6 mb-2">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group position-relative">
                                                    <label for="">{{ __('Name') }}
                                                        ({{ $client_language->langName }})
                                                    </label>
                                                    <input class="form-control"
                                                        name="language_id[{{ $k }}]" type="hidden"
                                                        value="{{ $client_language->langId }}">
                                                    <input class="form-control" name="name[{{ $k }}]"
                                                        type="text"
                                                        id="driver_registration_document_name_{{ $client_language->langId }}">
                                                </div>
                                                @if ($k == 0)
                                                    <span class="text-danger error-text social_media_url_err"></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-primary submitSaveDriverRegistrationDocument">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>


    <!-- modal for slots -->
    <div id="add_slot_modal" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="standard-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="standard-modalLabel">{{ __('Add Slot') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form id="slotForm" method="POST" action="javascript:void(0)">
                        @csrf
                        <div id="save_product_tag">
                            <input type="hidden" name="slot_id" value="">
                            <div class="row">

                                <div class="col-md-12 mb-2">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group position-relative">
                                                <label for="">{{ __('Name') }}</label>
                                                <input class="form-control" name="name" type="text"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group position-relative">
                                                <label for="">{{ __('Start Time') }}</label>
                                                <input class="form-control" name="start_time" type="time"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group position-relative">
                                                <label for="">{{ __('End Time') }}</label>
                                                <input class="form-control" name="end_time" type="time"
                                                    value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary submitSaveSlot">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end product Slot -->

@endsection
@section('script')
    <script src="{{ asset('assets\js\backend\backend_common.js') }}"></script>
    <script type="text/javascript">
        $(document).on("change", "#option_client_language", function() {
            let vendor_registration_document_id = $('input[name="vendor_registration_document_id"]').val();
            editVendorRegistrationForm(vendor_registration_document_id);
        });

        var is_marg_enable = $('#is_marg_enable');

        if (is_marg_enable.length > 0) {
            is_marg_enable[0].onchange = function() {

                if ($('#is_marg_enable:checked').length != 1) {
                    $('.marg_row').hide();
                } else {
                    $('.marg_row').show();
                }
            }
        }

        $(document).on('click', '.addOptionRow-Add', function(e) {
            var d = new Date();
            var n = d.getTime();
            var $tr = $('.optionTableAdd tbody>tr:first').next('tr');
            var $clone = $tr.clone();
            $clone.find(':text').val('');
            $clone.find('.hexa-colorpicker').attr("id", "hexa-colorpicker-" + n);
            $clone.find('.lasttd').html(
                '<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>'
            );
            $('.optionTableAdd').append($clone);

        });
        $('#add_slot_modal_btn').click(function(e) {
            document.getElementById("slotForm").reset();
            $('#add_slot_modal input[name=slot_id]').val("");
            $('#add_slot_modal').modal('show');
            $('#add_slot__modal #standard-modalLabel').html('Add Slot');
        });


        $(document).on("click", "#sync_marg_btn", function(e) {
            e.preventDefault();
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "{{ route('sync.marg') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    $('#sycn_time').html(response.time);
                    sweetAlert.success('Data Sycn Successfully!');
                },
                error: function(response) {
                    sweetAlert.error('Error!');
                }
            });
        });


        // Start Slot ////

        $(document).on("click", ".delete_slot_btn", function() {
            var tag_id = $(this).data('slot_id');
            if (confirm('Are you sure?')) {
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "{{ route('slot.delete') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        slot_id: tag_id
                    },
                    success: function(response) {
                        if (response.status == "Success") {
                            $.NotificationApp.send("Success", response.message, "top-right", "#5ba035",
                                "success");
                            setTimeout(function() {
                                location.reload()
                            }, 2000);
                        }
                    }
                });
            }
        });

        // Mark ////

        $(document).on("click", "#sync_marg_btn", function() {
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: "{{ route('sync.marg') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        dd(response);
                        if (response.status == "Success") {

                        }
                    }
                });
        });

        $(document).on('click', '.show-custom-mods-btn', function(e) {
            $('#custom-mode-verfication-modal').modal('show');
        });
        $(document).on('click', '.verification-code-sbt', function(e) {
            var varificationCode = $('#verification_code').val();
            if (varificationCode != '') {
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "{{ route('custom.mod.verification') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        varificationCode: varificationCode
                    },
                    success: function(response) {
                        // if (response.status == "Success") {
                        //    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                        //    setTimeout(function() {
                        //       location.reload()
                        //    }, 2000);
                        // }
                    }
                });
            }
        });

        $(document).on('click', '.submitSaveSlot', function(e) {
            var slot_id = $("#add_slot_modal input[name=slot_id]").val();
            if (slot_id) {
                var post_url = "{{ route('slot.update') }}";
            } else {
                var post_url = "{{ route('slot.create') }}";
            }
            $.ajax({
                url: post_url,
                method: 'POST',
                data: $('#slotForm').serialize(),
                success: function(response) {
                    if (response.status == 'Success') {
                        $('#add_or_edit_social_media_modal').modal('hide');
                        $.NotificationApp.send("Success", 'Slot Added Successfuly', "top-right",
                            "#5ba035", "success");
                        setTimeout(function() {
                            location.reload()
                        }, 2000);
                    } else {
                        $.NotificationApp.send("Error", 'Something went wrong', "top-right", "#ab0535",
                            "error");
                    }
                }
            });
        });
        $(document).on("click", ".edit_slot_btn", function() {
            let slot_id = $(this).data('slot_id');
            $('#add_slot_modal input[name=slot_id]').val(slot_id);
            $.ajax({
                method: 'GET',
                data: {
                    slot_id: slot_id
                },
                url: "{{ route('slot.edit') }}",
                success: function(response) {
                    if (response.status = 'Success') {
                        $("#add_slot_modal input[name=slot_id]").val(response.data.id);
                        $("#add_slot_modal input[name=name]").val(response.data.name);
                        $("#add_slot_modal input[name=start_time]").val(response.data.start_time);
                        $("#add_slot_modal input[name=end_time]").val(response.data.end_time);
                        $('#add_slot_modal #standard-modalLabel').html('Update Slot');
                        $('#add_slot_modal').modal('show');
                    }
                },
                error: function() {

                }
            });
        });
        // end Slot ////


        $('#add_driver_registration_document_modal_btn').click(function(e) {
            $('#add_driver_registration_document_modal').modal('show');
            $('#add_driver_registration_document_modal #standard-modalLabel').html(
                'Add Driver Registration Document');
        });
        $(document).on("click", ".delete_driver_registration_document_btn", function() {
            var driver_registration_document_id = $(this).data('driver_registration_document_id');
            Swal.fire({
                title: "{{ __('Are you Sure?') }}",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: "{{ route('driver.registration.document.delete') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            driver_registration_document_id: driver_registration_document_id
                        },
                        success: function(response) {
                            if (response.status == "Success") {
                                $.NotificationApp.send("Success", response.message, "top-right",
                                    "#5ba035", "success");
                                setTimeout(function() {
                                    location.reload()
                                }, 2000);
                            }
                        }
                    });
                }
            });
        });
        $(document).on('click', '.submitSaveDriverRegistrationDocument', function(e) {
            var driver_registration_document_id = $(
                "#add_driver_registration_document_modal input[name=driver_registration_document_id]").val();
            if (driver_registration_document_id) {
                var post_url = "{{ route('driver.registration.document.update') }}";
            } else {
                var post_url = "{{ route('driver.registration.document.create') }}";
            }
            var form_data = new FormData(document.getElementById("driverRegistrationDocumentForm"));
            $.ajax({
                url: post_url,
                method: 'POST',
                data: form_data,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status == 'Success') {
                        $('#add_or_edit_social_media_modal').modal('hide');
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035",
                            "success");
                        setTimeout(function() {
                            location.reload()
                        }, 2000);
                    } else {
                        $.NotificationApp.send("Error", response.message, "top-right", "#ab0535",
                            "error");
                    }
                },
                error: function(response) {
                    $('#add_driver_registration_document_modal .social_media_url_err').html(
                        'The default language name field is required.');
                }
            });
        });
        $(document).on("click", ".edit_driver_registration_document_btn", function() {
            let driver_registration_document_id = $(this).data('driver_registration_document_id');
            $('#add_driver_registration_document_modal input[name=driver_registration_document_id]').val(
                driver_registration_document_id);
            $.ajax({
                method: 'GET',
                data: {
                    driver_registration_document_id: driver_registration_document_id
                },
                url: "{{ route('driver.registration.document.edit') }}",
                success: function(response) {
                    if (response.status = 'Success') {
                        $('#add_driver_registration_document_modal').modal('show');
                        $("#add_driver_registration_document_modal input[name=file_type]").val(response
                            .data.file_type).change();
                        $("#add_driver_registration_document_modal input[name=driver_registration_document_id]")
                            .val(response.data.id);
                        $('#add_driver_registration_document_modal #standard-modalLabel').html(
                            'Update Driver Registration Document');
                        $.each(response.data.translations, function(index, value) {
                            $('#add_driver_registration_document_modal #driver_registration_document_name_' +
                                value.language_id).val(value.name);
                        });
                    }
                },
                error: function() {

                }
            });
        });
        $('.cleanSoftDeleted').click(function(e) {
            Swal.fire({
                title: "{{ __('Are you Sure?') }}",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if (result.value) {
                    e.preventDefault();
                    $.ajax({
                        url: "{{ route('config.cleanSoftDeleted') }}",
                        type: "POST",
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $.NotificationApp.send("Success", "Deleted Successfully",
                                "top-right", "#5ba035", "success");
                        },
                    });
                }
            });
        });

        $('.importDemoContent').click(function(e) {
            Swal.fire({
                title: "{{ __('Are you Sure you want to hard delete?') }}",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if (result.value) {
                    e.preventDefault();
                    $.ajax({
                        url: "{{ route('config.importDemoContent') }}",
                        type: "POST",
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $.NotificationApp.send("Success", "Deleted Successfully",
                                "top-right", "#5ba035", "success");
                        },
                    });
                }
            });
        });

        $('.hardDeleteEverything').click(function(e) {
            Swal.fire({
                title: "{{ __('Are you Sure you want to proceed?') }}",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if (result.value) {
                    e.preventDefault();
                    $.ajax({
                        url: "{{ route('config.hardDeleteEverything') }}",
                        type: "POST",
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $.NotificationApp.send("Success", "Deleted Successfully",
                                "top-right", "#5ba035", "success");
                        },
                    });
                }
            });
        });

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
        var autocomplete = {};
        var autocompletesWraps = [];
        var count = 1;
        editCount = 0;
        $(document).ready(function() {
            autocompletesWraps.push('Default_location_name');
            loadMap(autocompletesWraps);

            $('#get_estimations').on('change', function() {
                if ($('#get_estimations').is(':checked')) {
                    $('#estimation_in_category').show();
                } else {
                    $('#estimation_in_category').hide();
                }
            });
        });

        function loadMap(autocompletesWraps) {
            $.each(autocompletesWraps, function(index, name) {
                const geocoder = new google.maps.Geocoder;
                if ($('#' + name).length == 0) {
                    return;
                }
                autocomplete[name] = new google.maps.places.Autocomplete(document.getElementById(name), {
                    types: ['geocode']
                });
                if (is_map_search_perticular_country) {
                    autocomplete[name].setComponentRestrictions({
                        'country': [is_map_search_perticular_country]
                    });
                }
                google.maps.event.addListener(autocomplete[name], 'place_changed', function() {
                    var place = autocomplete[name].getPlace();
                    geocoder.geocode({
                        'placeId': place.place_id
                    }, function(results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            const lat = results[0].geometry.location.lat();
                            const lng = results[0].geometry.location.lng();
                            document.getElementById('Default_latitude').value = lat;
                            document.getElementById('Default_longitude').value = lng;
                        }
                    });
                });
            });

        }

        $('#show-map-modal').on('hide.bs.modal', function() {
            $('#add-customer-modal').removeClass('fadeIn');

        });
    //*************configurations hyperlocal map location selected by map*********************//
        $(document).on('click', '.showMap', function() {
            var no = $(this).attr('num');
            var lats = document.getElementById('Default_latitude').value;
            var lngs = document.getElementById('Default_longitude').value;

            document.getElementById('map_for').value = no;

                    if (lats == null || lats == '0') {
                    lats = 30.53899440;
                    }
                    if (lngs == null || lngs == '0') {
                    lngs = 75.95503290;
                    }

            var myLatlng = new google.maps.LatLng(lats, lngs);
                var mapProp = {
                center: myLatlng,
                zoom: 13,
                mapTypeId: google.maps.MapTypeId.ROADMAP

                };
            var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
                var marker = new google.maps.Marker({
                position: myLatlng,
                map: map,
                title: 'Hello World!',
                draggable: true
                });
                document.getElementById('lat_map').value = lats;
                document.getElementById('lng_map').value = lngs;
                google.maps.event.addListener(marker, 'drag', function(event) {

                document.getElementById('lat_map').value = event.latLng.lat();
                document.getElementById('lng_map').value = event.latLng.lng();
            });

            google.maps.event.addListener(marker, 'dragend', function(event) {

            var newLat = event.latLng.lat();
            var newLng = event.latLng.lng();
            var geocoder = new google.maps.Geocoder();
            var latlng = new google.maps.LatLng(newLat, newLng);
                geocoder.geocode({
                'latLng': latlng
                }, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                    var locationName = results[0].formatted_address;
                    document.getElementById('Default_location_name').value = locationName;
                }
                }
                });
                    document.getElementById('lat_map').value = newLat;
                    document.getElementById('lng_map').value = newLng;
                });
            $('#add-customer-modal').addClass('fadeIn');
            $('#show-map-modal').modal({
            keyboard: false
            });

            });

        $(document).on('click', '.selectMapLocation', function() {

            var mapLat = document.getElementById('lat_map').value;
            var mapLlng = document.getElementById('lng_map').value;
            var mapFor = document.getElementById('map_for').value;

            document.getElementById('Default_latitude').value = mapLat;
            document.getElementById('Default_longitude').value = mapLlng;

            $('#show-map-modal').modal('hide');
        });


        var hyprlocal = $('#is_hyperlocal');
        if (hyprlocal.length > 0) {
            hyprlocal[0].onchange = function() {

                if ($('#is_hyperlocal:checked').length != 1) {
                    $('.disableHyperLocal').hide();
                } else {
                    $('.disableHyperLocal').show();
                }
            }
        }

        var dispatcherDiv = $('#need_dispacher_ride');
        var need_dispacher_home_other_service = $('#need_dispacher_home_other_service');
        var laundry_service = $('#need_laundry_service');
        var need_inventory_service = $('#need_inventory_service');
        var laundry_service = $('#need_laundry_service');

        var is_hubspot_enable = $('#is_hubspot_enable');
        var is_marg_enable = $('#is_marg_enable');

        if (laundry_service.length > 0) {
            laundry_service[0].onchange = function() {

                if ($('#need_laundry_service:checked').length != 1) {
                    $('.laundryServiceFields').hide();
                } else {
                    $('.laundryServiceFields').show();
                }
            }
        }
        if (is_hubspot_enable.length > 0) {
            is_hubspot_enable[0].onchange = function() {

                if ($('#is_hubspot_enable:checked').length != 1) {
                    $('.hub_row').hide();
                } else {
                    $('.hub_row').show();
                }
            }
        }
        if (is_marg_enable.length > 0) {
            is_marg_enable[0].onchange = function() {

                if ($('#is_marg_enable:checked').length != 1) {
                    $('.marg_row').hide();
                } else {
                    $('.marg_row').show();
                }
            }
        }

        if (dispatcherDiv.length > 0) {
            dispatcherDiv[0].onchange = function() {
                console.log('ok');
                if ($('#need_dispacher_ride:checked').length != 1) {
                    $('.dispatcherFields').hide();
                } else {
                    $('.dispatcherFields').show();
                }
            }
        }

        if (need_dispacher_home_other_service.length > 0) {
            need_dispacher_home_other_service[0].onchange = function() {

                if ($('#need_dispacher_home_other_service:checked').length != 1) {
                    $('.home_other_dispatcherFields').hide();
                } else {
                    $('.home_other_dispatcherFields').show();
                }
            }
        }

        if (need_inventory_service.length > 0) {
            need_inventory_service[0].onchange = function() {
                if ($('#need_inventory_service:checked').length != 1) {
                    $('.inventoryFields').hide();
                    var is_order_edit_enable = $('#is_order_edit_enable_switch');

                    is_order_edit_enable[0].onchange = function() {
                        if ($('#is_order_edit_enable_switch:checked').length != 1) {
                            $('#edit_order_time_limit_div').hide();
                            $('#order_edit_before_hours').val(0);
                        } else {
                            $('.inventoryFields').show();
                        }
                    }
                }
            }
        }

        var fb_login = $('#fb_login');

        fb_login[0].onchange = function() {
            if ($('#fb_login:checked').length != 1) {
                $('.fb_row').hide();
            } else {
                $('.fb_row').show();
            }
        }

        var nafath_login = $('#nafath_login');
        nafath_login[0].onchange = function() {
            if ($('#nafath_login:checked').length != 1) {
                $('.nafath_row').hide();
            } else {
                $('.nafath_row').show();
            }
        }

        var is_one_push_book_enable = $('#is_one_push_book_enable_switch');
        var is_bid_ride_enable = $('#is_bid_ride_enable_switch');


        is_one_push_book_enable[0].onchange = function() {
            if ($('#is_one_push_book_enable_switch:checked').length != 1 && $('#is_bid_ride_enable_switch:checked')
                .length != 1) {
                $('#bid_expire_time_limit_div').hide();
                $('#bid_expire_time_limit_seconds').val(0);
            } else {
                $('#bid_expire_time_limit_div').show();
            }
        }

        is_bid_ride_enable[0].onchange = function() {
            if ($('#is_one_push_book_enable_switch:checked').length != 1 && $('#is_bid_ride_enable_switch:checked')
                .length != 1) {
                $('#bid_expire_time_limit_div').hide();
                $('#bid_expire_time_limit_seconds').val(0);
            } else {
                $('#bid_expire_time_limit_div').show();
            }
        }

        var dinein_option = $('#dinein_check');
        if (dinein_option.length > 0) {
            dinein_option[0].onchange = function() {
                optionsChecked("dinein_check");
            }
        }
        var twitter_login = $('#twitter_login');

        twitter_login[0].onchange = function() {
            if ($('#twitter_login:checked').length != 1) {
                $('.twitter_row').hide();
            } else {
                $('.twitter_row').show();
            }
        }

        var google_login = $('#google_login');

        google_login[0].onchange = function() {
            if ($('#google_login:checked').length != 1) {
                $('.google_row').hide();
            } else {
                $('.google_row').show();
            }
        }

        var apple_login = $('#apple_login');

        apple_login[0].onchange = function() {

            if ($('#apple_login:checked').length != 1) {
                $('.apple_row').hide();
            } else {
                $('.apple_row').show();
            }
        }
        var sos = $('#sos');

        sos[0].onchange = function() {

            if ($('#sos:checked').length != 1) {
                $('.sos_row').hide();
            } else {
                $('.sos_row').show();
            }
        }

        var is_order_edit_enable_switch = $('#is_order_edit_enable_switch');

        is_order_edit_enable_switch[0].onchange = function() {
            if ($('#is_order_edit_enable_switch:checked').length != 1) {
                $('#edit_order_time_limit_div').hide();
                $('#order_edit_before_hours').val(0);
            } else {
                $('#edit_order_time_limit_div').show();
            }
        }

        var is_taxjar_enable = $('#is_taxjar_enable');
        if (is_taxjar_enable.length > 0) {
            is_taxjar_enable[0].onchange = function() {

                if ($('#is_taxjar_enable:checked').length != 1) {
                    $('.taxjar_row').hide();
                } else {
                    $('.taxjar_row').show();
                }
            }
        }

        $('#pickup_notification_switch')[0].onchange = function() {
            if ($('#pickup_notification_switch:checked').length != 1) {
                $('#pickup_notification_div').hide();
                // $('#pickup_notification_before_hours').val(0);
            } else {
                $('#pickup_notification_div').show();
            }
        }

        $('#stock_notification_switch')[0].onchange = function() {
            if ($('#stock_notification_switch:checked').length != 1) {
                $('#stock_notification_div').hide();
                // $('#pickup_notification_before_hours').val(0);
            } else {
                $('#stock_notification_div').show();
            }
        }
        $('#blockchain_route_formation_switch').on('change', function() {

            if ($('#blockchain_route_formation_switch:checked').length != 1) {
                $('#blockchain_configuration_div').hide();
            } else {
                $('#blockchain_configuration_div').show();
            }

         });


        $('#pickup_notification_switch2')[0].onchange = function() {
            if ($('#pickup_notification_switch2:checked').length != 1) {
                $('#pickup_notification_div2').hide();
            } else {
                $('#pickup_notification_div2').show();
            }
        }
        var dinein_option = $('#dinein_check');
        if (dinein_option.length > 0) {
            dinein_option[0].onchange = function() {
                optionsChecked("dinein_check");
            }
        }

        var takeaway_option = $('#takeaway_check');
        if (takeaway_option.length > 0) {
            takeaway_option[0].onchange = function() {
                optionsChecked("takeaway_check");
            }
        }

        var delivery_option = $('#delivery_check');
        if (delivery_option > 0) {
            delivery_option[0].onchange = function() {
                optionsChecked("delivery_check");
            }
        }

        var third_party_accounting = $('#third_party_accounting');

        third_party_accounting[0].onchange = function() {
            if ($('#third_party_accounting:checked').length != 1) {
                $('#xero_config_div').hide();
                if ($('#xero_enable_switch').is(':checked') == 1) {
                    $('#xero_enable_switch').trigger("click");
                }
            } else {
                $('#xero_config_div').show();
            }
        }


        var xero_enable_switch = $('#xero_enable_switch');
        if (xero_enable_switch.length > 0) {
            xero_enable_switch[0].onchange = function() {

                if ($('#xero_enable_switch:checked').length != 1) {
                    $("#xero_client_id").val('');
                    $("#xero_secret_id").val('');
                    $('.xeroFields').hide();
                } else {
                    $('.xeroFields').show();
                }
            }
        }


        var square_enable_status_switch = $('#square_enable_status_switch');
        if (square_enable_status_switch.length > 0) {
            square_enable_status_switch[0].onchange = function() {

                if ($('#square_enable_status_switch:checked').length != 1) {
                    $("#xero_client_id").val('');
                    $("#xero_secret_id").val('');
                    $('.squareFields').hide();
                } else {
                    $('.squareFields').show();
                }
            }
        }

        function optionsChecked(id) {
            var delivery_checked = $("#delivery_check").is(":checked");
            var takeaway_checked = $("#takeaway_check").is(":checked");
            var dinein_checked = $("#dinein_check").is(":checked");
            if (dinein_checked == false && takeaway_checked == false && delivery_checked == false) {
                Swal.fire({
                    title: "Warning!",
                    text: "One option must be enables",
                    icon: "warning",
                    button: "OK",
                });
                $("#" + id).trigger('click');
            }
        }
        $(document).ready(function() {
            smsChange();
        });

        function toggle_smsFields(obj) {
            smsChange();
        }

        function smsChange() {
            var id = $("#sms_provider").find(':selected').attr('data-id');
            $('.sms_fields').css('display', 'none');
            $('#' + id).css('display', 'flex');
        }

        var square_enable_status_switch = $('#gofrugal_enable_status_switch');
        if (square_enable_status_switch.length > 0) {
            square_enable_status_switch[0].onchange = function() {

                if ($('#gofrugal_enable_status_switch:checked').length != 1) {
                    $("#gofrugal_api_key").val('');
                    $('.gofrugalFields').hide();
                } else {
                    $('.gofrugalFields').show();
                }
            }
        }
        function generateLumenToken() {
            var token = generateRandomString(30);

            $('#lumen_access_token').val(token);
        }

        $('#is_lumen_enabled').on('change',function(){

        if ($(this).is(":checked")) {
            $('.lumen-field').show();
        }else{
            $('.lumen-field').hide();
        }
        });
    </script>
@endsection
