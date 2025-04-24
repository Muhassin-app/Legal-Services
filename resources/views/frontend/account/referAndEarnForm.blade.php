@extends('layouts.store', ['title' => __('Address Book') ])
@section('css')
<link href="{{asset('assets/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .productVariants .firstChild {
        min-width: 150px;
        text-align: left !important;
        border-radius: 0% !important;
        margin-right: 10px;
        cursor: default;
        border: none !important;
    }

    .product-right .color-variant li,
    .productVariants .otherChild {
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 10px;
        cursor: pointer;
        border: 1px solid #f7f7f7;
        text-align: center;
    }

    .productVariants .otherSize {
        height: auto !important;
        width: auto !important;
        border: none !important;
        border-radius: 0%;
    }

    .product-right .size-box ul li.active {
        background-color: inherit;
    }

    .login-page .theme-card .theme-form input {
        margin-bottom: 5px;
    }

    .invalid-feedback {
        display: block;
    }

    .outer-box {
        min-height: 280px;
    }

    #address-map-container #pick-address-map {
        width: 100%;
        height: 100%;
    }

    .address-input-group {
        position: relative;
    }

    .address-input-group .pac-container {
        top: 35px !important;
        left: 0 !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .box-account.box-info.order-address .select2-container {
        width: 100% !important;
        margin-bottom: 15px;
    }

    body .select2-results__option[aria-selected] {
        display: block !important;
    }

    .box-account.box-info.order-address .checkbox.checkbox-success.form-check-inline label {
        margin: 0 10px;
    }
</style>
@endsection
@section('content')
<section class="section-b-space">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="text-sm-left">
                    @if (\Session::has('success'))
                    <div class="alert alert-success">
                        <span>{!! \Session::get('success') !!}</span>
                    </div>
                    @endif
                    @if ( ($errors) && (count($errors) > 0) )
                    <div class="alert alert-danger">
                        <ul class="m-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row my-md-3">
            <div class="col-lg-3">
                <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                <div class="dashboard-left mb-3">
                    <div class="collection-mobile-back">
                        <span class="filter-back d-lg-none d-inline-block">
                            <i class="fa fa-angle-left" aria-hidden="true"></i>{{ __('Back') }}
                        </span>
                    </div>
                    @include('layouts.store/profile-sidebar')
                </div>
            </div>
            <div class="col-lg-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2>{{ __('Refer and earn') }}</h2>
                        </div>
                        <div class="box-account box-info order-address">
                            @if( !empty($productAttributes) )
                            <form action="{{ route('refer-earn.save') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    @foreach($productAttributes as $vk => $var)

                                    @php $counter = 0; @endphp

                                    <div class="col-sm-2">
                                        <label class="control-label">{{$var->title??null}}</label>
                                    </div>
                                    <div class="col-sm-10">

                                        @if( !empty($var->type) && $var->type == 1 )
                                        @foreach($var->option as $key => $opt)

                                        <input type="hidden" name="attribute[{{$var->id}}][type]" value="{{$var->type}}">
                                        <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                                        <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                                        @php $counter++; @endphp
                                        @endforeach
                                        <select name="attribute[{{$var->id}}][value][]" class="select2-multiple attribute_option_id_{{$var->id}}" multiple>
                                            @foreach($var->option as $key => $opt)
                                            <option value="{{$opt->id}}">{{$opt->title}}</option>
                                            @endforeach
                                        </select>
                                        @elseif( !empty($var->type) && $var->type == 4 )
                                        @foreach($var->option as $key => $opt)
                                        <div class="form-check-inline w-100 mb-2">
                                            <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                                            <input class="form-control w-100 m-0" type="text" name="attribute[{{$var->id}}][option][{{$counter}}][value]" value="">
                                        </div>
                                        @endforeach
                                        @elseif( !empty($var->type) && $var->type == 3 )

                                        @foreach($var->option as $key => $opt)
                                        @if(isset($opt) && !empty($opt->title) && isset($var) && !empty($var->title))
                                        <div class="form-check-inline ">
                                            <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                                            <div class="attr_radio_{{$var->id}}">
                                                <input type="radio" name="attribute[{{$var->id}}][option][{{$counter}}][value]" class="attr_radio mr-1" value="{{$opt->id}}">
                                            </div>
                                            <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                                        </div>
                                        @php $counter++; @endphp
                                        @endif
                                        @endforeach
                                        @else
                                        @foreach($var->option as $key => $opt)
                                        <div class="checkbox checkbox-success form-check-inline pr-3">
                                            <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                                            <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                                            <input type="checkbox" name="attribute[{{$var->id}}][option][{{$counter}}][value]" value="{{$opt->id}}">
                                            <label for="attr_opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                                        </div>
                                        @php $counter++; @endphp
                                        @endforeach
                                        @endif
                                    </div>

                                    @endforeach
                                </div>
                                @if(@$influencer_category->kyc)
                                    @include('frontend.account.kycForm')
                                @endif
                                <div class="row">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-10 mt-2">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" id="removeAddressConfirmation" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_addressLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="remove_addressLabel">{{ __('Delete Address') }} </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <h6 class="m-0">
                    {{ __('Do you really want to delete this address ?') }}
                </h6>
            </div>
            <div class="modal-footer flex-nowrap justify-content-center align-items-center">
                <button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-solid" id="remove_address_confirm_btn" data-id="">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script src="{{asset('assets/libs/select2/select2.min.js')}}"></script>
<script>
    $(document).ready(function() {
        console.log('ready function called');
        $('.select2-multiple').select2();
    });
</script>
@endsection