@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('vendors', true)])
@section('css')
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
    <link href="{{ asset('assets/libs/fullcalendar-list/fullcalendar-list.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .pac-container,
        .pac-container .pac-item {
            z-index: 99999 !important;
        }

        .fc-v-event {
            border-color: #43bee1;
            background-color: #43bee1;
        }

        .dd-list .dd3-content {
            position: relative;
        }

        span.inner-div {
            top: 50%;
            -webkit-transform: translateY(-50%);
            -moz-transform: translateY(-50%);
            transform: translateY(-50%);
        }

        .button {
            position: relative;
            padding: 8px 16px;
            background: #009579;
            border: none;
            outline: none;
            border-radius: 50px;
            cursor: pointer;
        }

        .button:active {
            background: #007a63;
        }

        .button__text {
            font: bold 20px "Quicksand", san-serif;
            color: #ffffff;
            transition: all 0.2s;
        }

        .button--loading .button__text {
            visibility: hidden;
            opacity: 0;
        }

        .button--loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 4px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: button-loading-spinner 1s ease infinite;
        }
        .iti{
            width: 100%;
        }

        @keyframes button-loading-spinner {
            from {
                transform: rotate(0turn);
            }

            to {
                transform: rotate(1turn);
            }
        }
        /* NO BORDER SPINNER */


    </style>
@endsection
@php
    $getAdditionalPreference = getAdditionalPreference(['is_recurring_booking', 'is_long_term_service', 'square_enable_status']);
    $productsNom = getNomenclatureName('Products', true);
    $productsNom = ($productsNom=="Products")?__('Products'):__($productsNom);
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 d-md-flex align-items-center">
                <div class="page-title-box">
                    <h4 class="page-title">{{ ucfirst($vendor->name) }} {{ __('profile') }}</h4>
                </div>
                <div class="form-group mb-0 ml-sm-3">
                    <div class="site_link position-relative">
                        <a href="{{ route('vendorDetail', $vendor->slug) }}" target="_blank"><span id="pwd_spn"
                                class="password-span">{{ route('vendorDetail', $vendor->slug) }}</span></a>
                        <label class="copy_link float-right" id="cp_btn" title="copy">
                            <img src="{{ asset('assets/icons/domain_copy_icon.svg') }}" alt="">
                            <span class="copied_txt" id="show_copy_msg_on_click_copy"
                                style="display:none;">{{ __('Copied') }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-sm-12">
                <div class="text-sm-left">
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <span>{!! \Session::get('success') !!}</span>
                        </div>
                    @endif
                    @if (\Session::has('error_delete'))
                        <div class="alert alert-danger">
                            <span>{!! \Session::get('error_delete') !!}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-xl-3">
                @include('backend.vendor.show-md-3')
            </div>
            <div class="col-lg-8 col-xl-9">
                <div class="">
                @include('backend.vendor.topbar-tabs')

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card widget-inline">
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                            <div class="text-center">
                                                <h3>
                                                    <i class="mdi mdi-package-variant-closed text-primary mdi-24px"></i>
                                                    <span data-plugin="counterup"
                                                        id="total_earnings_by_vendors">{{ $product_count }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Total ') }} {{ __($productsNom)}}</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                            <div class="text-center">
                                                <h3>
                                                    <i class="mdi mdi-package-variant text-primary mdi-24px"></i>
                                                    <span data-plugin="counterup"
                                                        id="total_order_count">{{ $published_products }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Published ') }} {{ __($productsNom)}}</p>
                                            </div>
                                        </div>
                                        @if ($client_preference_detail->business_type != 'taxi')
                                            <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                                <div class="text-center">
                                                    <h3>
                                                        <i class="mdi mdi-store-24-hour text-primary mdi-24px"></i>
                                                        <span data-plugin="counterup"
                                                            id="total_cash_to_collected">{{ $last_mile_delivery }}</span>
                                                    </h3>
                                                    <p class="text-muted font-15 mb-0">{{ __('Last Mile Deliverables') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                                <div class="text-center">
                                                    <h3>
                                                        <i class="mdi mdi-new-box text-primary mdi-24px"></i>
                                                        <span data-plugin="counterup"
                                                            id="total_delivery_fees">{{ $new_products }}</span>
                                                    </h3>
                                                    <p class="text-muted font-15 mb-0">{{ __('New ') }} {{ __($productsNom)}}</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                                <div class="text-center">
                                                    <h3>
                                                        <i class="mdi mdi-diamond text-primary mdi-24px"></i>
                                                        <span data-plugin="counterup"
                                                            id="total_delivery_fees">{{ $featured_products }}</span>
                                                    </h3>
                                                    <p class="text-muted font-15 mb-0">{{ __('Featured ') }} {{ __($productsNom)}}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane {{ $tab == 'configuration' ? 'active show' : '' }} card-body"
                            id="configuration"></div>
                        <div class="tab-pane {{ $tab == 'category' ? 'active show' : '' }}" id="category"></div>
                        <div class="tab-pane {{ $tab == 'catalog' ? 'active show' : '' }}" id="catalog">
                            <div class="card-box">
                                <div class="row">
                                    <div class="col-md-2">
                                        <h4 class="mb-0"> {{ __('Catalog') }}</h4>
                                    </div>
                                    <div class="col-md-10 d-md-flex align-items-center justify-content-end mb-3">

                                            <div class="vendor-search mb-sm-0 mb-2">
                                                <input class="form-control" id="vendor_search" type="search" placeholder="Product Search" aria-controls="vendor_product_table">
                                            </div>
                                            <div class="vendor-search mb-sm-0 mb-2">
                                                <select class="form-control" name="product_is_live" id="product_is_live">
                                                    <option value="">{{__("Select")}} </option>
                                                    <option value="0">{{ __("Draft") }}</option>
                                                    <option value="1">{{ __("Published") }}</option>
                                                </select>
                                            </div>
                                            @if(isset($vendor['need_sync_with_order']) && $vendor['need_sync_with_order'] != 1)
                                            <a class="btn btn-info  waves-effect waves-light text-sm-right action_product_button" dataid="0"
                                                id="action_product_button" href="javascript:void(0);"
                                                style="display: none;"><i class="mdi mdi-plus-circle mr-1"></i>
                                                {{ __('Action') }}
                                            </a>
                                            @endif

                                            <a class="btn btn-info waves-effect waves-light ml-1 text-sm-right @if($vendor->status == 1) importProductBtn @endif  {{ $vendor->status == 1 ? '' : 'disabled' }}"
                                                dataid="0" href="javascript:void(0);"
                                                {{ $vendor->status == 1 ? '' : 'disabled' }}><i
                                                    class="mdi mdi-plus-circle mr-1"></i> {{ __('Import') }}
                                            </a>
                                            <a class="btn btn-info waves-effect waves-light ml-1 text-sm-right @if($vendor->status == 1)  exportProductPdf @endif  {{ $vendor->status == 1 ? '' : 'disabled' }}"
                                                dataid="0" href="javascript:void(0);"
                                                {{ $vendor->status == 1 ? '' : 'disabled' }}><i
                                                    class="mdi mdi-plus-circle mr-1"></i> {{ __('Export as PDF') }}
                                            </a>

                                        @if(isset($vendor['need_sync_with_order']) && $vendor['need_sync_with_order'] != 1)
                                            <a class="btn btn-info waves-effect waves-light text-sm-right mx-1" dataid="0" href="{{ route('vendor.product.export', $vendor->id) }}"><i
                                                    class="mdi mdi-plus-circle mr-1"></i> {{ __('Export') }}
                                            </a>
                                            <a class="btn btn-info waves-effect waves-light text-sm-right alAddProductBtn  @if($vendor->status == 1) addProductBtn @endif {{ $vendor->status == 1 ? '' : 'disabled' }}"
                                                dataid="0" href="javascript:void(0);"><i
                                                    class="mdi mdi-plus-circle mr-1"></i> {{ __('Add Product') }}
                                            </a>
                                        @endif
                                    </div>
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-centered dataTable table-nowrap table-striped w-100" id="vendor_product_table">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" class="all-product_check"
                                                                name="all_product_id" id="all-product_check"></th>
                                                        <th>#</th>
                                                        <th>{{ __('Name') }}</th>
                                                        <th>{{ __('Category') }}</th>
                                                        @if ($client_preference_detail->business_type != 'taxi')
                                                            <th>{{ __('Brand') }}</th>
                                                            <th>{{ __('Quantity') }}</th>
                                                            <th>{{ __('Rented Product') }}</th>
                                                            <th>{{ __('Price') }}</th>
                                                        @endif
                                                        <th>{{ __('Bar Code') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        <th>{{ __('Expiry Date') }}</th>
                                                        @if(@$getAdditionalPreference['is_recurring_booking'] == 1)
                                                            <th>{{ __('Recurring Booking') }}</th>
                                                        @endif
                                                        @if ($client_preference_detail->business_type != 'taxi')
                                                            <th>{{ __('New') }}</th>
                                                            <th>{{ __('Featured') }}</th>
                                                            <th>{{ __('Requires Last') }}<br>{{ __('Mile Delivery') }}
                                                            </th>
                                                        @endif

                                                        <th>{{ __('Action') }}</th>
                                                    </tr>
                                                </thead>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(@$getAdditionalPreference['is_long_term_service'] ==1)
                            <div class="card-box">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="mb-0"> {{ __('Long Term service') }}</h4>
                                    </div>
                                    <div class="col-md-6 d-md-flex align-items-center justify-content-end mb-3">
                                            <a class="btn btn-info waves-effect waves-light text-sm-right alAddProductBtn  @if($vendor->status == 1) addServiceBtn @endif {{ $vendor->status == 1 ? '' : 'disabled' }}"
                                                dataid="0" href="javascript:void(0);"><i
                                                    class="mdi mdi-plus-circle mr-1"></i> {{ __('Add Service') }}
                                            </a>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-centered dataTable table-nowrap table-striped w-100" id="vendor_longTerm_service_table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ __('Name') }}</th>
                                                        <th>{{ __('Product Name') }}</th>
                                                        <th>{{ __('No. of Bookings') }}</th>
                                                        <th>{{ __('Period') }}</th>
                                                        <th>{{ __('Price') }}</th>
                                                        <th>{{ __('Action') }}</th>
                                                    </tr>
                                                </thead>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row address" id="def" style="display: none;">
        <input type="text" id="def-address" name="test" class="autocomplete form-control def_address">
    </div>
    @if(@$getAdditionalPreference['is_long_term_service'] ==1)
    <div id="add-service" class="modal fade add_service" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add Service') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="save_service_form" method="post" enctype="multipart/form-data">
                    @csrf
                    {!! Form::hidden('vendor_id', $vendor->id) !!}
                    <input type="hidden" name="long_term__service_id" id="long_term__service_id">
                    <div class="modal-body pb-0">
                        <div class="col-md-12  ">
                            <label>{{ __('Upload Service Image') }}</label>

                            <div class="service_image">
                                <input type="file" data-plugins="dropify" name="file" id="service_image" class="dropify" />
                             </div>
                        </div>
                        <div class="col-md-12 selector-option-al ">
                            {!! Form::label('title', __('Service Name'), ['class' => 'control-label']) !!}
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
                                            <input class="form-control" @if($lankey ==0) onkeyup='setServiceSkuFromName(event,"service_name_1","srviceSku")' @endif  name="name[{{$lankey}}]" type="text" id="service_name_{{$User_langs->langId}}" autocomplete='off'>
                                        </td>
                                    @endforeach
                                    <td class="lasttd"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div id="Service_nameInput">
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="form-group" id="skuInput">
                                    {!! Form::label('title', __('SKU'), ['class' => 'control-label']) !!}
                                    <span class="text-danger">*</span>
                                    {!! Form::text('sku', null, ['class' => 'form-control', 'id' => 'srviceSku', 'onkeyup' => 'return alplaNumeric(event)', 'placeholder' =>  __('SKU')]) !!}
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                    <span class="valid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group" id="serice_priceInput">
                                    {!! Form::label('title', __('Service Price'), ['class' => 'control-label']) !!}
                                    {!! Form::text('serice_price',null ,['class'=>'form-control', 'id' => 'price', 'placeholder' => '200', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group" id="product_quantityInput">
                                    {!! Form::label('title', __('No. of Bookings'), ['class' => 'control-label']) !!}
                                    {!! Form::text('product_quantity',null ,['class'=>'form-control', 'id' => 'quantity', 'placeholder' => '10', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group" id="service_periodInput">
                                    {!! Form::label('title', __('Select Time Period'),['class' => 'control-label']) !!}
                                        <select class="form-control select2-multiple" id="service_period" data-toggle="select2" multiple="multiple" name="service_period[]">
                                            <option value="days">{{ __('Day') }}</option>
                                            <option value="week">{{ __('Weekly') }}</option>
                                            <option value="months">{{ __('Monthly') }}</option>
                                        </select>
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group" id="service_durationInput">
                                    {!! Form::label('title', __('Select Time Duration (Months)'),['class' => 'control-label']) !!}
                                    <input type="number" class="form-control" min='1' name="service_duration" placeholder="{{ __('No. of Months') }}">
                                        {{-- <select class="form-control selectizeInput" id="service_duration" name="service_duration">
                                            <option value="1">1 {{ __('Month') }}</option>
                                            <option value="3">3 {{ __('Months') }}</option>
                                            <option value="6">6 {{ __('Months') }}</option>
                                            <option value="12">1 {{ __('Year') }}</option>
                                        </select> --}}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group" id="service_product_idInput">
                                    {!! Form::label('title', __('Select Product'),['class' => 'control-label']) !!}
                                <select class="form-control selectizeInput" id="service_product_list" name="service_product_id">
                                    <option value="">{{ __("Select Product") }}...</option>

                                    @foreach($products->where('category_id','!=','7') as $product)
                                        <option value="{{$product['id']}}" data-category_id="{{ $product->category_id ?? '' }} data-product_title="{{ $product->primary->title ?? '' }}">{{ Str::limit(isset($product->primary->title) && !empty($product->primary->title) ? $product->primary->title : '', 30) }}</option>
                                    @endforeach
                                    </select>
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group" id="service_product_variantInput">
                                    {!! Form::label('title', __('Product variant'),['class' => 'control-label']) !!}
                                     <select class="form-control selectizeInput" id="service_product_variant" name="service_product_variant_id">

                                    </select>
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-12" id="addonSection"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-info waves-effect waves-light submitServiceProduct">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    <div id="add-product" class="modal fade add_product" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add Product') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="save_product_form" method="post" enctype="multipart/form-data"
                    action="{{ route('product.store') }}" class="123456879">
                    @csrf
                    <div class="modal-body pb-0">

                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <div class="form-group" id="product_nameInput">
                                            {!! Form::label('title', __('Product Name'), ['class' => 'control-label']) !!}
                                            <span class="text-danger">*</span>
                                            {!! Form::text('product_name', null, ['class' => 'form-control', 'id' => 'product_name', 'onkeyup' => 'return setSkuFromName(event)', 'placeholder' => __('Product Name'), 'autocomplete' => 'off']) !!}

                                            <span class="invalid-feedback" role="alert">
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group" id="skuInput">
                                            {!! Form::label('title', __('SKU'), ['class' => 'control-label']) !!}
                                            <span class="text-danger">*</span>
                                            {!! Form::text('sku', null, ['class' => 'form-control', 'id' => 'sku', 'onkeyup' => 'return alplaNumeric(event)', 'placeholder' =>  __('SKU')]) !!}
                                            <span class="invalid-feedback" role="alert">
                                                <strong></strong>
                                            </span>
                                            <span class="valid-feedback" role="alert">
                                                <strong></strong>
                                            </span>
                                            {!! Form::hidden('type_id', 1) !!}
                                            {!! Form::hidden('vendor_id', $vendor->id) !!}
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group" id="url_slugInput">
                                            {!! Form::label('title', __('URL Slug'), ['class' => 'control-label']) !!}
                                            {!! Form::text('url_slug', null, ['class' => 'form-control', 'id' => 'url_slug', 'placeholder' =>  __('URL Slug'), 'onkeypress' => 'return slugify(event)']) !!}
                                            <span class="invalid-feedback" role="alert">
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group" id="categoryInput">
                                            {!! Form::label('title', __('Category'),['class' => 'control-label']) !!}
                                        <select class="form-control selectizeInput" id="category_list" name="category">
                                            {{-- <option value="">{{ __("Select Category") }}...</option> --}}
                                            @foreach($product_categories as $product_category)
                                                <option value="{{$product_category['id']}}">{{$product_category['hierarchy']}}</option>
                                            @endforeach

                                            {{--@foreach($product_categories as $product_category)
                                                @if($product_category->category)
                                                    @if( ($product_category->category->type_id == 1) || ($product_category->category->type_id == 3) || ($product_category->category->type_id == 7))
                                                        <option value="{{$product_category->category_id}}">{{(isset($product_category->category->primary->name)) ? $product_category->category->primary->name : $product_category->category->slug}}</option>
                                                    @endif
                                                @endif
                                            @endforeach --}}
                                            </select>
                                            <span class="invalid-feedback" role="alert">
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-info waves-effect waves-light submitProduct">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="import-product" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add Product') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>

                <div class="modal-body">
                    <div class="row">


                        <div class="col-md-12 text-center">

                            <div id="import_csv" class="row align-items-center mb-3">
                                <div class="col-md-12 text-center mb-2">
                                    <button class="btn btn-info button" id="csv_button"
                                        type="button">{{ __('Import form Woocommerce') }}</button>
                                </div>

                                @if($client_preference_detail->enable_inventory_service == 1)
                                <a href="{{route('get.inventory.import',$vendor->slug)}}">
                                    <div class="col-12 text-center mb-2">
                                        <button class="btn btn-info button"
                                            type="button">{{ __('Import form Inventory') }}</button>
                                    </div>
                                </a>
                                @endif

                            @if($client_preference_detail->business_type == 'laundry')
                                <div class="col-md-12 text-center mb-2">
                                    <button class="btn btn-info button" id="import_global"
                                        type="button">{{ __('Import Global Product') }}</button>
                                </div>

                                {{-- <div class="col-md-12 text-center mb-2">
                                    <button class="btn btn-info button" id="import_bagqrcode"
                                        type="button">{{ __('Import Bag Qrcode') }}</button>
                                </div> --}}
                            @endif

                                <div class="col-md-12">
                                    <form method="post" enctype="multipart/form-data" id="save_imported_products">
                                        @csrf
                                        @if(session()->get("applocale_admin") == "ta")
                                        <a
                                            href="{{ url('file-download' . '/tamil_sample_product.csv') }}">{{ __('Download Sample file here!') }}</a>

                                        @elseif(session()->get("applocale_admin") == "am")
                                        <a
                                            href="{{ url('file-download' . '/arabic_sample_product.csv') }}">{{ __('Download Sample file here!') }}</a>

                                        @elseif(session()->get("applocale_admin") == "fr")
                                        <a
                                            href="{{ url('file-download' . '/french_sample_product.csv') }}">{{ __('Download Sample file here!') }}</a>

                                        @elseif(session()->get("applocale_admin") == "de")
                                        <a
                                            href="{{ url('file-download' . '/german_sample_product.csv') }}">{{ __('Download Sample file here!') }}</a>

                                        @else
                                        <a
                                            href="{{ url('file-download' . '/sample_product.csv') }}">{{ __('Download Sample file here!') }}</a>
                                        @endif

                                        <input type="hidden" value="{{ $vendor->id }}" name="vendor_id" />
                                        <input type="file" accept=".csv" onchange="submitProductImportForm()"
                                            data-plugins="dropify" name="product_excel" class="dropify" />
                                    </form>
                                </div>
                            </div>

                            <div id="import_woocommerce" class="row align-items-center mb-3">
                                <div class="col-12 text-right mb-2">
                                    <button class="btn btn-info button" id="woocommerce_button"
                                        type="button">{{ __('Import CSV') }}</button>
                                </div>
                                <div class="col-md-12">
                                    <form id="woocommerces_form">
                                        <div class="form-group">
                                            <input class="form-control" type="url" name="domain_name"
                                                placeholder="Domain Name"
                                                value="{{ $woocommerce_detail ? $woocommerce_detail->url : '' }}">
                                            <span class="text-danger" id="domain_name_error"></span>
                                        </div>
                                        <div class="form-group">
                                            <input class="form-control" type="text" name="consumer_key"
                                                placeholder="Consumer Key"
                                                value="{{ $woocommerce_detail ? $woocommerce_detail->consumer_key : '' }}">
                                            <span class="text-danger" id="consumer_key_error"></span>
                                        </div>
                                        <div class="form-group">
                                            <input class="form-control" type="text" name="consumer_secret"
                                                placeholder="Consumer Secret"
                                                value="{{ $woocommerce_detail ? $woocommerce_detail->consumer_secret : '' }}">
                                            <span class="text-danger" id="consumer_secret_error"></span>
                                        </div>
                                        <button class="btn btn-info button" id="save_woocommerce_btn" type="button"
                                            onclick="this.classList.toggle('button--loading')">Save</button>
                                        <button class="btn btn-info button" id="import_product_from_woocomerce"
                                            data-vendor="{{ $vendor->id }}"
                                            onclick="this.classList.toggle('button--loading')">{{ __('Import Products From Woocommerce') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap table-striped" id="">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('File Name') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Link') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="post_list">
                                        @foreach ($csvProducts as $csv)

                                            <tr data-row-id="{{ $csv->id }}">
                                                <td> {{ $loop->iteration }}</td>
                                                <td> {{ $csv->name }}</td>
                                                @if ($csv->status == 1)
                                                    <td>{{ __('Pending') }}</td>
                                                @elseif($csv->status == 2)
                                                    <td>{{ __('Success') }}</td>
                                                @else
                                                    <td>{{ __('Errors') }}</td>
                                                    {{-- <td class="position-relative text-center alTooltipHover">
                                                        <i class="mdi mdi-exclamation-thick"></i>
                                                        <ul class="tooltip_error d-none">

                                                            @foreach ($error_csv as $err)
                                                                <li>
                                                                    {{ $err }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </td> --}}
                                                @endif
                                                @if(isset($csv->error))
                                                    <td>
                                                       <a href="{{ route('productImport.error',['id' => $csv->id]) }}">{{ __('Download Logs') }}</a>
                                                    </td>
                                                @else
                                                    <td>{{ "--" }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
 <!-- start product action popup -->
 <div id="action-product-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
 aria-hidden="true" style="display: none;">
 <div class="modal-dialog modal-dialog-centered">
     <div class="modal-content">
         <div class="modal-header border-bottom">
             <h4 class="modal-title">{{ __('Product Action') }}</h4>
             <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>

         <div class="modal-body">

                 <div class="card-box">
                     <form id="save_product_action_modal" method="post" enctype="multipart/form-data"
                     action="#">
                     @csrf

                     <div class="row mb-2">
                         <div class="col-md-6 mb-2">
                             {!! Form::label('title', __('Action For '), ['class' => 'control-label']) !!}
                             <select class="form-control" id="action_for" name="action_for" required>
                                 <option value="0">{{__('Select')}}</option>
                                 @if ($client_preferences->business_type != 'taxi')
                                  <option value="for_new">{{__('For  New')}}</option>
                                  <option value="for_featured">{{__('For Featured')}}</option>
                                  @endif
                                  @if ($client_preferences->need_delivery_service == 1 || OnLAstMileDelivery()>0)
                                  <option value="for_last_mile">{{__('For Requires Last Mile Delivery')}}</option>
                                  @endif
                                  <option value="for_live">{{__('Draft/Published')}}</option>
                                  <option value="for_tax">{{__('Tax Category')}}</option>
                                @if(@$vendor->add_markup_price)
                                  <option value="for_markup">{{__('Markup Price')}}</option>
                                @endif

                                @if(@$getAdditionalPreference['is_recurring_booking'] == 1)
                                    <option value="is_recurring_booking">{{__('Recurring Booking')}}</option>
                                @endif
                                  <option value="for_sell_when_out_of_stock">{{__('Sell when out of stock')}}</option>
                                @if(@$getAdditionalPreference['square_enable_status'] == 1)
                                  <option value="sync_for_square_post">{{__('Sync For Square POS')}}</option>
                                @endif
                                  <option value="delete">{{__('Delete')}}</option>
                             </select>
                         </div>



                     </div>

                     <div class="row mb-2">
                         @if ($client_preferences->business_type != 'taxi')
                             <div class="col-md-6 justify-content-between mb-2" id="for_new" style="display:none;">
                                 {!! Form::label('title', __('New'), ['class' => 'control-label']) !!}
                                 <input type="checkbox" id="is_new" data-plugin="switchery" name="is_new"
                                     class="chk_box" data-color="#43bee1">
                             </div>
                             <div class="col-md-6 justify-content-between mb-2" id="for_markup" style="display:none;">
                                {!! Form::label('title', __('Markup Price'), ['class' => 'control-label']) !!}
                                <input type="number" id="markup_price"  name="markup_price" class="form-control">
                            </div>
                               <div class="col-md-6 justify-content-between mb-2"   id="for_featured" style="display:none;">
                                 {!! Form::label('title', __('Featured'), ['class' => 'control-label']) !!}
                                 <input type="checkbox" id="is_featured" data-plugin="switchery" name="is_featured"
                                     class="chk_box" data-color="#43bee1">
                             </div>
                         @endif
                         @if ($client_preferences->need_delivery_service == 1 || OnLAstMileDelivery()>0)
                              <div class="col-md-6  justify-content-between mb-2"    id="for_last_mile"  style="display:none;">
                                 {!! Form::label('title', __('Requires Last Mile Delivery'), ['class' => 'control-label']) !!}
                                 <input type="checkbox" id="last_mile" data-plugin="switchery" name="last_mile"
                                     class="chk_box" data-color="#43bee1">
                             </div>
                         @endif

                     </div>
                     <div class="row">
                           <div class="col-md-6 mb-2"  id="for_live"  style="display: none;">
                             {!! Form::label('title', __('Live'), ['class' => 'control-label']) !!}
                             <select class="selectizeInput form-control" id="is_live" name="is_live">
                                 <option value="0">Draft</option>
                                 <option value="1">Published</option>
                             </select>
                           </div>


                         <div class="col-md-6 mb-2"  id="for_tax"  style="display: none;">
                             {!! Form::label('title', __('Tax Category'), ['class' => 'control-label']) !!}
                             <select class="form-control " id="tax_category_for" name="tax_category">
                                 <option value="">Select</option>
                                 @foreach ($taxCate as $cate)
                                     <option value="{{ $cate->id }}">{{ $cate->title }}</option>
                                 @endforeach
                             </select>
                         </div>
                         <div class="col-md-6 justify-content-between mb-2"   id="for_sell_when_out_of_stock" style="display:none;">
                             {!! Form::label('title', __('Sell when out of stock'), ['class' => 'control-label']) !!}
                             <input type="checkbox" id="sell_when_out_of_stock" data-plugin="switchery" name="sell_when_out_of_stock"
                                 class="chk_box" data-color="#43bee1">
                         </div>
                     </div>

                     <div class="modal-footer">
                         <button type="button"
                             class="btn btn-info waves-effect waves-light submitProductAction">{{ __('Submit') }}</button>
                     </div>

                     </form>


                 </div>

         </div>

     </div>
 </div>
</div>
<!-- end product popup -->

    <!-- Global product import popup -->
    <div id="global-product-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-scrollable modal-lg ">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Product Action') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>

                <div class="modal-body">
                    <div class="row ">
                        <div class="col-12">
                        <button type="submit" class="submitGlobalProductAction submit btn btn-primary float-right" >Submit</button>
                        </div>
                        <div class="col-12">
                        <table class="table table-centered dataTable table-nowrap table-striped w-100" id="global_product_table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="all-global-product_check"
                                                name="all_global_product_id" id="all-global-product_check"></th>
                                        <th>{{__('Image')}}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Category') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Global product import popup -->

    <!-- import qrcode modal popup -->

    <div id="import-bagqrcode-modal" class="modal fade importQrcodeBtn" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Import QR Codes') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>

                <div class="modal-body">
                    <div class="row">


                        <div class="col-md-12 text-center">

                            <div class="col-md-4 text-right mb-2">
                                <button class="btn btn-info button"
                                    type="button"> <a href="{{ route('estimations.barcode',$vendor->id) }}">{{ __('View Bag Qrcode') }}</a></button>
                            </div>

                            <div id="import_csv" class="row align-items-center mb-3">

                                <div class="col-md-12">
                                    <form method="post" enctype="multipart/form-data" id="save_imported_qrcode">
                                        @csrf

                                        <a href="{{ url('file-download' . '/sample_qrcode.csv') }}">{{ __('Download Sample file here!') }}</a>
                                        <input type="hidden" value="{{ $vendor->id }}" name="vendor_id" />
                                        <input type="file" accept=".csv" onchange="submitQrcodeImportForm()"
                                            data-plugins="dropify" name="qrcode_excel" class="dropify" />
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap table-striped" id="">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('File Name') }}</th>
                                            <th colspan="2">{{ __('Status') }}</th>
                                            <th>{{ __('Link') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody >
                                        @forelse ($files as $csv)

                                        <tr data-row-id="{{ $csv->id }}">
                                            <td> {{ $loop->iteration }}</td>
                                            <td> {{ $csv->name }}</td>
                                            @if ($csv->status == 1)
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
                                                        @foreach ($error_csv as $err)
                                                            <li>
                                                                {{ $err }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                            @endif
                                            <td> <a href="{{ $csv->storage_url }}">{{ __('Download') }}</a> </td>
                                        </tr>
                                        @empty
                                        <tr><td>{{ __('No record found.') }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--- End popup qrcode -->
    @if(@$getAdditionalPreference['is_long_term_service'] == 1)
      <script src="{{asset('js/adminVendor.js')}}"></script>
    @endif
    <script type="text/javascript">
    var  sku_start = "{{ $sku_url }}" + ".";

        $(".all-product_check").click(function() {
            if ($(this).is(':checked')) {
                $("#action_product_button").css("display", "block");
                $('.single_product_check').prop('checked', true);
            } else {
                $("#action_product_button").css("display", "none");
                $('.single_product_check').prop('checked', false);
            }
        });

        $(".all-global-product_check").click(function() {
            if ($(this).is(':checked')) {
                $('.global_product_check').prop('checked', true);
            } else {
                $('.global_product_check').prop('checked', false);
            }
        });

        $(document).on('change', '#action_for', function() {
            var actionfor = $('#action_for').val();
            $("#for_new").css("display", "none");
            $("#for_featured").css("display", "none");
            $("#for_last_mile").css("display", "none");
            $("#for_live").css("display", "none");
            $("#for_tax").css("display", "none");
            $("#for_markup").css("display", "none");
            $("#for_sell_when_out_of_stock").css("display", "none");
            $("#"+ actionfor).css("display", "block");
        });

        $(document).on('change', '.single_product_check', function() {
            if ($('input:checkbox.single_product_check:checked').length > 0) {
                $("#action_product_button").css("display", "block");
            } else {
                $('.all-product_check').prop('checked', false);
                $("#action_product_button").css("display", "none");
            }
        });

        ////////   *******************  Save product action data ******************* ////////////////////////
        // $('#save_product_action_modal').on('submit', function(e) {
        //     e.preventDefault();
        //     var is_new = $('#is_new').val();
        //     var is_featured = $('#is_featured').val();
        //     var is_live = $('#is_live').val();
        //     var tax_category = $('#tax_category').val();
        //     var product_id = [];
        //      $('.single_product_check:checked').each(function(i){
        //         product_id[i] = $(this).val();
        //     });
        //     if (product_id.length == 0) {

        //         $("#action-product-modal .close").click();
        //         return;
        //     }
        //     console.log(product_id);
        //     return false;
        //     $.ajax({
        //         type: "POST",
        //         url: '{{route("product.update.action")}}',
        //         data: {_token: CSRF_TOKEN, is_new: is_new, is_featured: is_featured, is_live: is_live, tax_category: tax_category, product_id: product_id},
        //         success: function( msg ) {
        //             location.reload();
        //         }
        //     });
        // });

        $(document).on('click', '.submitProductAction', function(e) {
            var CSRF_TOKEN = $("input[name=_token]").val();
            var is_new = $('#is_new').prop('checked');
            var is_featured = $('#is_featured').prop('checked');
            var is_live = $('#is_live').val();
            var markup_price = $('#markup_price').val();
            var tax_category = $('#tax_category_for').val();
            var action_for = $('#action_for').val();
            var last_mile = $('#last_mile').prop('checked');
            var sell_when_out_of_stock = $('#sell_when_out_of_stock').prop('checked');
            var product_id = [];
             $('.single_product_check:checked').each(function(i){
                product_id[i] = $(this).val();
            });
            if (product_id.length == 0) {

                $("#action-product-modal .close").click();
                return;
            }
            if(action_for == 0){
                return false;
            }

            $.ajax({
                type: "post",
                url: '{{route("product.update.action")}}',
                data: {_token: CSRF_TOKEN,action_for:action_for,sell_when_out_of_stock:sell_when_out_of_stock,last_mile:last_mile, is_new: is_new, is_featured: is_featured, is_live: is_live, tax_category: tax_category,markup_price:markup_price, product_id: product_id},
                 success: function(resp) {
                    if (resp.status == 'success') {
                        $.NotificationApp.send("Success", resp.message, "top-right", "#5ba035",
                            "success");
                        location.reload();
                    }
                },
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                error: function(response) {

                        $(".show_all_error.invalid-feedback").show();
                        $(".show_all_error.invalid-feedback").text(
                            'Something went wrong, Please try Again.');

                    return response;
                }
            });
        });
        ////////  ****************** End save product action data *****************//////////////////////////

        $('#action_product_button').click(function() {
            $('#action-product-modal').modal({
                keyboard: false
            });
        });

        $('.addProductBtn').click(function() {
            $.ajax({
                type: "get",
                url: "{{route('vendor.specific_categories',$vendor->id)}}",
                success: function(response) {
                    if(response.status == 1){
                        $('#category_list').selectize()[0].selectize.destroy();
                        $("#category_list").find('option').remove();
                        $("#category_list").append(response.options);
                    }
                },
                error:function(error){
                }
            });
            $('#add-product').modal({
                keyboard: false
            });
        });
        $('.importProductBtn').click(function() {
            $('#import-product').modal({
                keyboard: false
            });
        });

        $("#csv_button").click(function() {
            $("#import_woocommerce").show();
            $("#import_csv").hide();
        });

        $("#import_global").click(function() {
            $("#import-product").modal('hide');
            $("#global-product-modal").modal('show');
        });

        $("#import_bagqrcode").click(function() {
            $("#import-product").modal('hide');
            $("#import-bagqrcode-modal").modal('show');
        });

        $("#import_woocommerce").hide();
        $("#woocommerce_button").click(function() {
            $("#import_csv").show();
            $("#import_woocommerce").hide();
        });

        $(document).delegate("#skuInput #sku", "blur focusout", function(){
            var sku = $(this).val();
            $.ajax({
                type: "post",
                url: "{{route('product.sku.validate')}}",
                data: {sku : sku},
                success: function(response) {
                    if(response.status == 'Success'){
                        $("#skuInput input").removeClass("is-invalid").addClass('valid');
                        $("#skuInput span.invalid-feedback").children("strong").text('');
                        $("#skuInput span.valid-feedback").children("strong").text(response.message);
                        $("#skuInput span.invalid-feedback").hide();
                        $("#skuInput span.valid-feedback").show();
                    }
                },
                error:function(response){
                    if (response.status === 422) {
                        let error = response.responseJSON;
                        $("#skuInput input").removeClass("valid").addClass("is-invalid");
                        $("#skuInput span.invalid-feedback").children("strong").text(error.message);
                        $("#skuInput span.invalid-feedback").show();
                        $("#skuInput span.valid-feedback").hide();
                    }else{

                    }
                }
            });
        });

        var regexp = /^[a-zA-Z0-9-_]+$/;

        function setSkuFromName() {
            var n1 = $('#product_name').val();
            n1 = n1.replace(/[.*+?^${}()/|[\]\\]+/g, '-');
            var sku_start = "{{ $sku_url }}" + ".";
            var total_sku = sku_start + n1;
            $('#sku').val(sku_start + n1);

            if (regexp.test(n1)) {
                var n1 = $('#product_name').val();
                $('#url_slug').val(n1);
                slugify();setSkuFromName
            } else {
            $('#sku').val(total_sku.split(' ').join(''));
            }
            // alplaNumeric();
        }


        function alplaNumeric() {
            var n1 = $('#sku').val();
            if (regexp.test(n1)) {
                var n1 = $('#sku').val();
                $('#url_slug').val(n1);
                slugify();
            } else {
                $('#sku').val(n1.split(' ').join(''));
            }
            // var charCode = String.fromCharCode(event.which || event.keyCode);
            // if (!regexp.test(charCode)) {

            //     return false;
            // }

            // var n1 = $('#sku').val();
            // $('#url_slug').val(n1+charCode)

            // return true;
        }

        function slugify() {
            //   var charCode = String.fromCharCode(event.which || event.keyCode);
            //   if (!regexp.test(charCode)) {
            //     return false;
            //   }
            var string = $('#url_slug').val();
            var slug = string.toString().trim().toLowerCase().replace(/\s+/g, "-").replace(/[^\w\-]+/g, "").replace(
                /\-\-+/g, "-").replace(/^-+/, "").replace(/-+$/, "");
            $('#url_slug').val(slug);
        }
        $(document).on('click', '#save_woocommerce_btn', function(e) {
            var that = $(this);
            $('.text-danger').html('');
            that.attr('disabled', true);
            $('#import_product_from_woocomerce').attr('disabled', true);
            var form = document.getElementById('woocommerces_form');
            var formData = new FormData(form);
            $.ajax({
                type: "post",
                url: "{{ route('woocommerce.save') }}",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    that.attr('disabled', false);
                    that.removeClass('button--loading');
                    $('#import_product_from_woocomerce').attr('disabled', false);
                    if (response.status == 'success') {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035",
                            "success");
                    } else {
                        $.NotificationApp.send("Error", response.message, "top-right", "#FF0000",
                            "error");
                    }
                },
                error: function(error) {
                    that.attr('disabled', false);
                    that.removeClass('button--loading');
                    $('#import_product_from_woocomerce').attr('disabled', false);
                    var response = $.parseJSON(error.responseText);
                    let error_messages = response.errors;
                    $.each(error_messages, function(key, error_message) {
                        $('#' + key + '_error').html(error_message[0]).show();
                    });
                }
            });
        });
        $(document).on('click', '#import_product_from_woocomerce', function(e) {
            var that = $(this);
            $('#save_woocommerce_btn').attr('disabled', true);
            that.attr('disabled', true);
            var vendor_id = $(this).data('vendor');
            $.ajax({
                type: "POST",
                data: {
                    vendor_id: vendor_id
                },
                url: "{{ route('product.import.woocommerce') }}",
                dataType: 'json',
                success: function(response) {
                    that.attr('disabled', false);
                    that.removeClass('button--loading');
                    $('#save_woocommerce_btn').attr('disabled', false);
                    if (response.status == 'success') {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035",
                            "success");
                    } else {
                        $.NotificationApp.send("Error", response.message, "top-right", "#FF0000",
                            "error");
                    }
                }
            });
        });
        $(document).on('click', '.submitProduct', function(e) {
            var form = document.getElementById('save_product_form');
            var formData = new FormData(form);
            $.ajax({
                type: "post",
                url: "{{ route('product.validate') }}",
                data: formData,
                contentType: false,
                processData: false,
                success: function(resp) {
                    if (resp.status == 'success') {
                        $('#save_product_form').submit();
                    }
                },
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                error: function(response) {
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            if (key == 'category.0') {
                                $("#categoryInput input").addClass("is-invalid");
                                $("#categoryInput span.invalid-feedback").children("strong")
                                    .text('The category field is required.');
                                $("#categoryInput span.invalid-feedback").show();
                            } else {
                                $("#" + key + "Input input").addClass("is-invalid");
                                $("#" + key + "Input span.invalid-feedback").children("strong")
                                    .text(errors[key][0]);
                                $("#" + key + "Input span.invalid-feedback").show();
                            }
                        });
                    } else {
                        $(".show_all_error.invalid-feedback").show();
                        $(".show_all_error.invalid-feedback").text(
                            'Something went wrong, Please try Again.');
                    }
                    return response;
                }
            });
        });
    </script>
    @include('backend.vendor.modals')
@endsection
@section('script')

    @include('backend.vendor.pagescript')
    @include('backend.export_pdf')

    <script>
        var vendor_id = `{{ $vendor->id }}`;
        $(document).on('click', '.copy_link', function() {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($('#pwd_spn').text()).select();
            document.execCommand("copy");
            $temp.remove();
            $("#show_copy_msg_on_click_copy").show();
            setTimeout(function() {
                $("#show_copy_msg_on_click_copy").hide();
            }, 1000);
        })

        $(document).on("click",".delete-product",function() {
            var destroy_url = $(this).data('destroy_url');
            var id = $(this).data('rel');
            Swal.fire({
                title: "Are you sure?",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if(result.value)
                {
                    $('form#deleteproduct_'+id).submit();
                }
            });
        });
        $(document).on("input","#vendor_search",function() {
           let search = $('#vendor_search').val();
           datatable_intent(search);
        });

        $(document).on('change','#product_is_live',function() {
            let is_live = $(this).val();
            search = $('#vendor_search').val();
            datatable_intent(search);
        });

        function datatable_intent(search =''){
            $('#vendor_product_table').DataTable({
                "responsive": true,
                "bAutoWidth": false,
                // "scrollX": true,
                "destroy": true,
                // "processing": true,
                "serverSide": true,
                "iDisplayLength": 25,
                "lengthChange" : false,
                "searching": false,
                "ordering": true,
                "dom": '<"toolbar">Bftrip',

                language: {
                            search: "",
                            info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                            paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                            searchPlaceholder: "{{__('Search Product')}}",
                            // 'loadingRecords': '&nbsp;',
                            // 'processing': '<div class="spinner"></div>'
                },
                buttons:[
                            {
                             extend: 'pdf',
                                text: 'Export to PDF',
                                className:'btn btn-success waves-effect Export_btn waves-light ml-2 d-none',
                                id:'exp-btn',
                                text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>Export PDF',
                                orientation: 'landscape',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                customize: function (doc) {
                                doc.pageOrientation = 'landscape';
                                doc.pageSize = 'A3'; // Set the custom page size
                            }
                            }
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },

                ajax: {
                    url: "{{url('client/vendor/product/list').'/'.$vendor->id}}",
                    data: function (d) {
                        d.search = $('#vendor_search').val();
                        d.is_live = $('#product_is_live').val();
                    }
                },
                columns: dataTableColumn(),

            });
        }
        $(document).ready(function() {
            datatable_intent();
        });

    function dataTableColumn(){
       var business_type         =  "{{$client_preference_detail->business_type}}";
            if(business_type == 'taxi'){
                return [
                    {data: 'single_product_check', name: 'single_product_check', orderable: false, searchable: false},
                    {data: 'product_image', name: 'product_image', orderable: false, searchable: false},
                    {data: 'product_name', name: 'product_name', orderable: true, searchable: false},
                    {data: 'product_category', name: 'phone_number', orderable: false, searchable: false},
                    {data: 'bar_code', name: 'bar_code', orderable: false, searchable: false},
                    {data: 'product_is_live', name: 'product_is_live', orderable: false, searchable: false},
                    {data: 'expiry_date', name: 'expiry_date', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ];
            }else{
                return [
                    {data: 'single_product_check', name: 'single_product_check', orderable: false, searchable: false},
                    {data: 'product_image', name: 'product_image', orderable: false, searchable: false},
                    {data: 'product_name', name: 'product_name', orderable: true, searchable: false},
                    {data: 'product_category', name: 'phone_number', orderable: false, searchable: false},
                    {data: 'product_brand', name: 'product_brand', orderable: false, searchable: false},
                    {data: 'product_quantity', name: 'product_quantity', orderable: false, searchable: false},
                    {data: 'rental_product_count', name: 'rental_product_count', orderable: false, searchable: false},
                    {data: 'product_price', name: 'product_price', orderable: false, searchable: false},
                    {data: 'bar_code', name: 'bar_code', orderable: false, searchable: false},
                    {data: 'product_is_live', name: 'product_is_live', orderable: false, searchable: false},
                    {data: 'expiry_date', name: 'expiry_date', orderable: false, searchable: false},
                    @if(@$getAdditionalPreference['is_recurring_booking'] == 1)
                      {data: 'is_recurring_booking', name: 'is_recurring_booking', orderable: false, searchable: false},
                    @endif
                    {data: 'product_is_new', name: 'product_is_new', orderable: false, searchable: false},
                    {data: 'product_is_featured', name: 'product_is_featured', orderable: false, searchable: false},
                    {data: 'product_last_mile', name: 'product_last_mile', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},

                ]
            }
        }

        $(document).ready(function() {
            datatable_global_product();
        });

        function datatable_global_product(){
            $('#global_product_table').DataTable({
                "responsive": true,
                "scrollX": true,
                "destroy": true,
                "processing": true,
                "serverSide": true,
                "iDisplayLength": 25,
                "lengthChange" : false,
                "searching": false,
                "ordering": true,

                language: {
                            info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                            paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                            'processing': '<div class="spinner"></div>'
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },

                ajax: {
                    url: "{{url('client/global/product/list')}}"
                },
                columns: dataTableGlobalProducts(),
            });
        }


        function dataTableGlobalProducts(){
                return [
                    {data: 'global_product_check', name: 'global_product_check', orderable: false, searchable: false},
                    {data: 'product_image', name: 'product_image', orderable: false, searchable: false},
                    {data: 'product_name', name: 'product_name', orderable: true, searchable: false},
                    {data: 'product_category', name: 'product_category', orderable: false, searchable: false}
                ];
        }


        $(document).on('click', '.submitGlobalProductAction', function(e) {
            var CSRF_TOKEN = $("input[name=_token]").val();
            var product_id = [];
             $('.global_product_check:checked').each(function(i){
                product_id[i] = $(this).val();
            });
            if (product_id.length == 0) {
                $("#global-product-modal .close").click();
                return;
            }

            $.ajax({
                type: "post",
                url: '{{route("import.global.product")}}',
                data: {_token: CSRF_TOKEN, product_id: product_id,vid:"{{$vendor->id}}"},
                 success: function(resp) {
                     console.log(resp);
                    if (resp.success == true) {
                        $.NotificationApp.send("Success", resp.message, "top-right", "#5ba035",
                            "success");
                        location.reload();
                    }
                },
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                error: function(response) {

                        $(".show_all_error.invalid-feedback").show();
                        $(".show_all_error.invalid-feedback").text(
                            'Something went wrong, Please try Again.');

                    return response;
                }
            });
        });



        $('.importQrcodeBtn').click(function() {
            $('#import-qrcode').modal({
                keyboard: false
            });
        });

        function submitQrcodeImportForm() {
        var form = document.getElementById('save_imported_qrcode');
        var formData = new FormData(form);
        var data_uri = "{{route('qrcode.import')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // location.reload();
                if (response.status == 'success') {
                    $(".modal .close").click();
                    location.reload();
                } else {

                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            beforeSend: function() {

                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
                setTimeout(function() {
                    location.reload();
                }, 2000);

            }
        });
    }
    $('.exportProductPdf').click(function(){

            $('.buttons-pdf').click();
});
    </script>
@endsection
