@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Edit Product'])

@section('css')
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet"> -->
<!--<link rel="stylesheet" href="{{ asset('assets/ck_editor/samples/css/samples.css') }}">-->
<link rel="stylesheet" href="{{ asset('assets/ck_editor/samples/toolbarconfigurator/lib/codemirror/neo.css') }}">
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .image-upload>input {
        display: none;
    }

    .product-img-box {
        width: 100%;
        height: 150px;
        border: 1px solid #ccc;
    }

    .product-img-box img {
        height: 100%;
        width: 100%;
        object-fit: cover;
        object-position: center;
    }

    .upload-btn-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }



    .upload-btn-wrapper input[type=file] {
        font-size: 100px;
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
    }
.form-label label{font-weight: bold;}
    .product-img-box input[type="checkbox"] {
        position: absolute;
        top: 0;
        right: 11px;
    }

    .product-img-box label {
        width: 100%;
        height: 100%;
        display: block;
    }

    .product-img-box .form-group {
        height: 100%;
    }

    .product-img-box label:before {
        right: -1px;
        left: auto;
        top: -1px;
    }

    .product-img-box .checkbox-success input[type="checkbox"]:checked+label::after {
        left: auto;
        right: 6px;
        top: 4px;
    }

    .product-box.editPage .product-action {
        padding: 0.5rem 1rem 0 0.5rem;
    }

    .product-box.editPage .product-action .btn {
        padding: 0px 2px;
    }
    .saveVariantOrder {
        display: none;
    }
    button.btn.btn-sm.add_attr_options {
    background: #ccc;
    height: 30px;
    width: 30px;
    color: #fff;
}
div#attribute_section button.btn.btn-sm.add_attr_options {
    background: #a42c7f;
    height: 30px;
    width: 30px;
    color: #fff;
    padding: 0;
    position: absolute;
    right: 0;
}
div#attribute_section .select2-container {
    width: 95% !important;
}
div#attribute_section .col-sm-9 .checkbox.checkbox-success.form-check-inline {
    width: 20%;
    margin-bottom: 12px;
}
div#attribute_section .col-sm-9 .form-check-inline.w-100 {
    width: 95% !important;
}
.css-loader {
    border: 10px solid #ffffff;
    border-radius: 50%;
    border-top: 10px solid #3498db;
    width: 80px;
    height: 80px;
    -webkit-animation: spin 2s linear infinite;
    animation: spin 2s linear infinite;
    position: absolute;
    z-index: 9999999999;
    left: 40%;
    top: 50%;
    transform: translate(-50%, -50%);
    margin: 0 auto;
}
.outter-loader {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    height: 100%;
    width: 100%;
    margin: 0 auto;
    background: #0000004a;
    z-index: 9;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.field_wrapper a.add_button i {
    font-size: 30px;
    margin-top: -4px;
    display: inline-block;
}
.field_wrapper a.remove_button i {
    font-size: 30px;
    margin-top: -4px;
    display: inline-block;
    color: #bb0e0e;
}
</style>
@endsection
@php
$lastmileShow = array('7','11'); //,'10'

$brandNotShow = array('7','8','12');
$on_demand_check = array('8','12');

if($client_preference_detail->appointment_check == 1 && ($client_preference_detail->need_appointment_service == '1') ){
    $lastmileShow = array_diff($lastmileShow,['11']);
}
@endphp
@section('content')
<div class="container-fluid">

    <div class="row">
        <span class="delete_options d-none">Error while deleting options</span>
        <div class="col-8 d-flex align-items-center">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Edit Product") }}</h4>
            </div>
            <div class="site_link position-relative ml-3">
                <a href="{{route('productDetail',[$product->vendor->slug,$product->url_slug])}}" target="_blank"><span id="pwd_spn" class="password-span"> {{route('productDetail',[$product->vendor->slug,$product->url_slug])}}</span></a>
                <label class="copy_link float-right" id="cp_btn" title="copy">
                    <img src="{{ asset('assets/icons/domain_copy_icon.svg')}}" alt="">
                    <span class="copied_txt" id="show_copy_msg_on_click_copy" style="display:none;">{{ __("Copied") }}</span>
                </label>
            </div>
        </div>
        @if(isset($product->vendor->need_sync_with_order) && $product->vendor->need_sync_with_order != 1)
            <div class="col-4 text-right" style="margin: auto;">
                <button type="submit" class="btn btn-info waves-effect waves-light text-sm-right saveProduct"> {{ __("Submit") }}</button>
            </div>
        @endif
    </div>
    <a href="{{route('vendor.catalogs',$product->vendor_id)}}">{{ $product->vendor->name}} </a>
    <div class="row mb-2">
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
                @if ( ($errors) && (count($errors) > 0) )
                    <div class="alert alert-danger">
                        <button type="button" class="close p-0" data-dismiss="alert">x</button>
                        <ul class="m-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @elseif(Session::has('url_slug_error'))
                    <div class="alert alert-danger">
                        <button type="button" class="close p-0" data-dismiss="alert">x</button>
                        <ul class="m-0">
                                <li>{{ Session::get('url_slug_error') }}</li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <form action="{{route('product.update', $product->id)}}" enctype="multipart/form-data" method="post" class="product_form">
        <div class="row">
            <div class="col-lg-7">
                @csrf
                @method('PUT')
                <div class="card-box" style="display:{{(($product->global_product_id!='')?'none':'block')}}">
                    <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">{{ __("General") }}</h5>
                    <div class="row mb-2 row-spacing">
                        <div class="col-md-5 mb-2" style="cursor: not-allowed;">
                            {!! Form::label('title', __('SKU (a-z, A-Z, 0-9, -,_)'),['class' => 'control-label']) !!}
                            <span class="text-danger">*</span>
                            {!! Form::text('sku', $product->sku, ['class'=>'form-control','id' => 'sku', 'onkeypress' => "return alplaNumeric(event)",'name' => 'sku']) !!}
                            {!! Form::hidden('vendor_id', $product->vendor_id, ['name' => 'vendor_id']) !!}

                            @if($errors->has('sku'))
                            <span class="text-danger" role="alert">
                                {{ $errors->first('sku') }}
                            </span>
                            @endif
                        </div>

                        <div class="col-md-4" style="cursor: not-allowed;">
                            {!! Form::label('title', __('URL Slug'),['class' => 'control-label']) !!}
                            <span class="text-danger">*</span>
                            {!! Form::text('url_slug', $product->url_slug, ['class'=>'form-control', 'id' => 'url_slug','onkeypress' => "return alplaNumericSlug(event)"]) !!}

                            @if($errors->has('url_slug'))
                            <span class="text-danger" role="alert">
                                {{ $errors->first('url_slug') }}
                            </span>
                             @elseif(Session::has('url_slug_error'))
                            <span class="text-danger" role="alert">
                                {{ Session::get('url_slug_error') }}
                            </span>
                            @endif
                        </div>

                        <div class="col-md-3" style="cursor: not-allowed;">
                            {!! Form::label('title', __('Category'),['class' => 'control-label']) !!}
                            {!! Form::text('category', $product->category ? $product->category->cat->name : '', ['class'=>'form-control', 'style' => 'pointer-events:none;']) !!}
                            <input type="hidden" name="category_id" value="{{$product->category ? $product->category->cat->category_id : $product->category_id}}">
                        </div>
                    </div>
                </div>
                <div class="card-box " style="display:{{(($product->global_product_id!='')?'none':'block')}}">
                    <div class="row mb-2 bg-light">
                        <div class="col-8" style="margin:auto; padding: 8px !important;">
                            <h5 class="text-uppercase  mt-0 mb-0">{{ __("Product Information") }}</h5>
                        </div>
                        <div class="col-4 p-2 mt-0" style="margin:auto; padding: 8px !important;">
                            <select class="selectize-select form-control" id="language_id" name="language_id">
                                @foreach($languages as $lang)
                                <option value="{{$lang->langId}}" {{ ($lang->is_primary == 1) ? 'selected' : ''}}>{{__($lang->langName)}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 mb-2">
                            {!! Form::label('title', __('Product Name'),['class' => 'control-label']) !!}
                            {!! Form::text('product_name', $product->primary ? $product->primary->title : '', ['class'=>'form-control', 'id' => 'product_name', 'placeholder' => 'Apple iMac', 'required' => 'required']) !!}
                        </div>
                        <div class="col-12 mb-2">
                            {!! Form::label('title', __('Product Description'),['class' => 'control-label']) !!}
                            {!! Form::textarea('body_html', $product->primary ? $product->primary->body_html : '', ['class'=>'form-control', 'id' => 'body_html', 'placeholder' => 'Description', 'rows' => '5']) !!}
                        </div>
                        <div class="col-12 mb-2">
                            {!! Form::label('title', __('Meta Title'),['class' => 'control-label']) !!}
                            {!! Form::text('meta_title', $product->primary ? $product->primary->meta_title : '', ['class'=>'form-control', 'id' => 'meta_title', 'placeholder' => 'Meta Title']) !!}
                        </div>
                        <div class="col-12 mb-2">
                            {!! Form::label('title', __('Meta Keyword'),['class' => 'control-label']) !!}
                            {!! Form::textarea('meta_keyword', $product->primary ? $product->primary->meta_keyword : '', ['class'=>'form-control', 'id' => 'meta_keyword', 'placeholder' => 'Meta Keyword', 'rows' => '3']) !!}
                        </div>
                        <div class="col-12 mb-2">
                            {!! Form::label('title', __('Meta Description'),['class' => 'control-label']) !!}
                            {!! Form::textarea('meta_description', $product->primary ? $product->primary->meta_description : '', ['class'=>'form-control', 'id' => 'meta_description', 'placeholder' => 'Meta Description', 'rows' => '3']) !!}
                            </div>
                    </div>
                </div>
                {{-- @php
                pr($product->toArray()); @endphp --}}
                @if($product->category->categoryDetail->type_id != 7)
                <div class="card-box">
                    {{-- @dd($product->vendor) --}}
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2 def">{{ __("Pricing Information") }}</h5>
                    @if($product->has_variant == 0)
                    <div class="row mb-2">
                        @if(@$product->vendor->is_seller == 1 && Auth::user()->is_superadmin == 1)
                            <div class="col-4 mb-2">
                                {!! Form::label('title', __('Cost price'), ['class' => 'control-label']) !!}
                                @include('backend.primary_currency')
                                {!! Form::text('cost_price', decimal_format($product->variant[0]->cost_price), ['class'=>'form-control', 'id' => 'cost_price', 'placeholder' => '200', 'onkeypress' => 'return isNumberKey(event)']) !!}
                            </div>
                        @elseif (@$product->vendor->is_seller == 1)
                            <div class="col-4 mb-2">
                                {!! Form::label('title', __('Cost price'), ['class' => 'control-label']) !!}
                                @include('backend.primary_currency')
                                {!! Form::text('cost_price', decimal_format($product->variant[0]->cost_price), ['class'=>'form-control', 'id' => 'cost_price', 'placeholder' => '200', 'onkeypress' => 'return isNumberKey(event)']) !!}
                            </div>
                        @endif

                        @if(@$product->vendor->is_seller == 0 || Auth::user()->is_superadmin == 1 )
                            <div class="col-4 mb-2">
                                {!! Form::label('title', __('Price'), ['class' => 'control-label']) !!}
                                @include('backend.primary_currency')
                                @if (isset($getAdditionalPreference['is_price_by_role']))
                                    @if($getAdditionalPreference['is_price_by_role'] == '1')
                                        {!! Form::text('price', decimal_format($product->variant[0]->getRawOriginal('price')), ['class'=>'form-control', 'id' => 'price', 'placeholder' => '200', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                    @else
                                        {!! Form::text('price', decimal_format($product->variant[0]->actual_price), ['class'=>'form-control', 'id' => 'price', 'placeholder' => '200', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                    @endif
                                @endif

                            </div>

                            <div class="col-4 mb-2">
                                {!! Form::label('title', __('Compare at price (Optional)'), ['class' => 'control-label']) !!}
                                @include('backend.primary_currency')
                                {!! Form::text('compare_at_price', decimal_format($product->variant[0]->compare_at_price), ['class'=>'form-control', 'id' => 'compare_at_price', 'placeholder' => '200', 'onkeypress' => 'return isNumberKey(event)']) !!}
                            </div>
                        @endif
                        @if($product->vendor->need_container_charges == 1)
                        <div class="col-4 mb-2">
                            {!! Form::label('title', __('Container Charges (Optional)'), ['class' => 'control-label']) !!}
                            @include('backend.primary_currency')
                            {!! Form::text('container_charges', $product->variant[0]->container_charges, ['class'=>'form-control', 'id' => 'container_charges', 'placeholder' => '0', 'onkeypress' => 'return isNumberKey(event)']) !!}
                        </div>
                        @endif
                        @if($product->vendor->add_markup_price == 1 && Auth::user()->is_superadmin == 1)
                        <div class="col-4 mb-2">
                            {!! Form::label('title', __('Markup Price'), ['class' => 'control-label']) !!}
                            @include('backend.primary_currency') ({{ __("Visible For Admin") }})
                            {!! Form::text('markup_price', $product->variant[0]->markup_price, ['class'=>'form-control', 'id' => 'markup_price', 'placeholder' => '0', 'onkeypress' => 'return isNumberKey(event)']) !!}
                        </div>
                        @endif
                        @if( in_array( $product->category->categoryDetail->type_id , [12]) )
                        <div class="col-4  mb-2">
                            {!! Form::label('title', __('Appointment Duration').' '. __('min:'), ['class' => 'control-label']) !!}
                            {!!Form::input('number','minimum_duration_min', $product->minimum_duration_min, ['min' => '0','max' => '59','class'=>'form-control', 'id' => 'minimum_duration_min', 'placeholder' => '0', 'onkeyup' => 'return isNumberKeyMax(event)']) !!}
                        </div>
                        @endif
                        {{-- <div class="col-4 mb-2">
                            {!! Form::label('title', 'Cost Price (Optional)', ['class' => 'control-label']) !!}
                            {!! Form::text('cost_price', $product->variant[0]->cost_price, ['class'=>'form-control', 'id' => 'cost_price', 'placeholder' => '200', 'onkeypress' => 'return isNumberKey(event)']) !!}
                        </div> --}}
                    </div>
                    @endif
                    <div class="row mb-2">
                        @if(!in_array($product->category->categoryDetail->type_id,[8,9,10,12]))
                        <div class="col-sm-4">
                            {!! Form::label('title', __('Track Inventory')) !!} <br>
                            <input type="checkbox" bid="" id="has_inventory" data-plugin="switchery" name="has_inventory" class="chk_box" data-color="#43bee1" {{$product->has_inventory == 1 ? 'checked' : ''}}>
                        </div>
                        @endif

                        <div class="col-sm-8 check_inventory ">
                            <div class="row">
                                @if( !in_array($product->category->categoryDetail->type_id,[8,10,12]) )
                                @if($product->has_variant == 0)
                                <div class="col-sm-4">
                                    {!! Form::label('title', __('Quantity'),['class' => 'control-label']) !!}
                                    {!! Form::number('quantity', $product->variant[0]->quantity, ['class'=>'form-control', 'id' => 'quantity', 'placeholder' => '0', 'min' => '0', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                </div>
                                @endif
                                <div class="col-sm-3">
                                    {!! Form::label('title', __('Sell When Out Of Stock'),['class' => 'control-label']) !!} <br />
                                    <input type="checkbox" bid="" id="sell_stock_out" data-plugin="switchery" name="sell_stock_out" class="chk_box" data-color="#43bee1" @if($product->sell_when_out_of_stock == 1) checked @endif>
                                </div>
                                @endif


                                @if($configData->need_dispacher_home_other_service == 1 && $product->category->categoryDetail->type_id == 8)
                                {{-- <div class="col-sm-4">
                                    {!! Form::label('title', 'Need Price From Dispatcher',['class' => 'control-label']) !!} <br />
                                    <input type="checkbox" bid="" id="need_price_from_dispatcher" data-plugin="switchery" name="need_price_from_dispatcher" class="chk_box" data-color="#43bee1" @if($product->need_price_from_dispatcher == 1) checked @endif>
                                </div> --}}
                                @endif

                            </div>
                        </div>
                    </div>

                    {{-- Input Filed of price based on roles (START) --}}
                    @if($product->has_variant == 0)
                        {{-- @if (isset($getAdditionalPreference['is_price_by_role'])) --}}
                            {{-- @if($getAdditionalPreference['is_price_by_role'] == '1') --}}
                                <div class="row mb-2">
                                    @if (isset($roles))
                                        @foreach ($roles as $key => $_role)
                                            {{-- @if( $_role['role'] === 'Corporate_user') --}}
                                                <div class="col-12">

                                                    <div class="field_wrapper">
                                                        @foreach ($product->productVariantByRoles as $role)
                                                            {{-- @if($role->role_id == 3) --}}
                                                                <div class="row corporate-row">
                                                                    <div class="col-md-5">
                                                                        <div class="form-group">
                                                                            <input type="number" class="form-control" min="0" id="corporate_user_price" onkeyup="isNumberKey(event)" placeholder="Corporate User Price" name="corporate_user_price[]" value="{{$role->amount}}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <div class="form-group"><input type="number" class="form-control" min="0" onkeyup="isNumberKey(event)" placeholder="Quantity" name="minimum_order_count_corporate_user[]" value="{{$role->quantity}}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <a href="javascript:void(0);" class="remove_button" title="Remove field"><i class="mdi mdi-minus-circle mr-1"></i></a>
                                                                    </div>
                                                                </div>
                                                            {{-- @endif --}}
                                                        @endforeach
                                                        <div class="row corporate-row">
                                                            <div class="col-md-5">
                                                                <div class="form-group">
                                                                    <input type="number" class="form-control" min="0" id="{{lcfirst($_role['role'])}}_price" onkeyup="isNumberKey(event)" placeholder="Corporate User Price" name="{{lcfirst($_role['role'])}}_price[]" value="">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="form-group">
                                                                    <input type="number" class="form-control" min="0" onkeyup="isNumberKey(event)" placeholder="Quantity" name="minimum_order_count_corporate_user[]" value="">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <a href="javascript:void(0);" class="add_button" title="Add field"><i class="mdi mdi-plus-circle mr-1"></i></a>
                                                            </div>
                                                            <input type="hidden" class="form-control" min="0" name="role_id[{{lcfirst($_role['role'])}}]" value="{{$_role['id']}}">
                                                        </div>
                                                    </div>
                                                </div>
                                            {{-- @else
                                                <div class="col-4 mb-2">
                                                    {!! Form::label('title', $_role['role'].' '. __('Price'), ['class' => 'control-label']) !!}
                                                    <input type="number" class="form-control" min="0" id="{{lcfirst($_role['role'])}}_price" onkeyup="isNumberKey(event)" placeholder="0" name="role_price[{{$_role['id']}}]" value="{{ isset($product->productVariantByRoles[$key]) ? (decimal_format($product->productVariantByRoles[$key]->amount) ?? 0.00) : 0.00 }}">
                                                    <input type="hidden" class="form-control" min="0" name="role_id[{{lcfirst($_role['role'])}}]" value="{{$_role['id']}}">
                                                </div>
                                            @endif --}}
                                        @endforeach
                                    @endif
                                </div>
                            {{-- @endif
                        @endif --}}
                    @endif
                    {{-- Input Filed of price based on roles (END) --}}

                    @if(  in_array( $product->category->categoryDetail->type_id , [10]) )
                        <div class="row col-md-12 mb-2">
                            <div class="col-12 mb-2 row">
                                <div class="col-12">
                                    {!! Form::label('title', __('Minimum Duration'), ['class' => 'control-label ml-2']) !!}
                                </div>
                                <div class="col-6 pl-3">
                                    {!! Form::label('title', __('hrs:'), ['class' => 'control-label']) !!}

                                    {!!Form::input('number','minimum_duration', $product->minimum_duration, ['min' => '00','class'=>'form-control', 'id' => 'minimum_duration', 'placeholder' => '00', 'onkeyup' => 'return isNumberKey(event)']) !!}
                                </div>
                                <div class="col-6 pr-3">
                                    {!! Form::label('title', __('min:'), ['class' => 'control-label']) !!}
                                    {!!Form::input('number','minimum_duration_min', $product->minimum_duration_min, ['min' => '00','max' => '59','class'=>'form-control', 'id' => 'minimum_duration_min', 'placeholder' => '00', 'onkeyup' => 'return isNumberKeyMax(event)']) !!}
                                </div>
                            </div>
                            <div class="col-12 mb-2 row">
                                <div class="col-12">
                                    {!! Form::label('title', __('Additional Increment Duration'), ['class' => 'control-label ml-2']) !!}
                                </div>
                                <div class="col-6 pl-3">
                                    {!! Form::label('title', __('hrs:'), ['class' => 'control-label']) !!}
                                    {!! Form::input('number','additional_increments', $product->additional_increments, ['min' => '0','max' => '59','class'=>'form-control', 'id' => 'additional_increments', 'placeholder' => '0', 'onkeyup' => 'return isNumberKey(event)']) !!}
                                </div>
                                <div class="col-6 pr-3">
                                    {!! Form::label('title', __('min:'), ['class' => 'control-label']) !!}
                                    {!! Form::input('number','additional_increments_min', $product->additional_increments_min, ['min' => '0','max' => '59','class'=>'form-control', 'id' => 'additional_increments_min', 'placeholder' => '0', 'onkeyup' => 'return isNumberKeyMax(event)']) !!}
                                </div>

                            </div>
                            <div class="col-12 mb-2 row">
                                <div class="col-12">
                                    {!! Form::label('title', __('Buffer time Duration'), ['class' => 'control-label ml-2']) !!}
                                </div>
                                <div class="col-6 pl-3">
                                    {!! Form::label('title', __('hrs:'), ['class' => 'control-label']) !!}
                                    {!! Form::input('number','buffer_time_duration', $product->buffer_time_duration, ['min' => '0','class'=>'form-control', 'id' => 'buffer_time_duration', 'placeholder' => '0', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                </div>
                                <div class="col-6 pr-3">
                                    {!! Form::label('title', __('min:'), ['class' => 'control-label']) !!}
                                    {!! Form::input('number','buffer_time_duration_min', $product->buffer_time_duration_min, ['min' => '0','class'=>'form-control', 'id' => 'buffer_time_duration_min', 'placeholder' => '0', 'onkeypress' => 'return isNumberKeyMax(event)']) !!}
                                </div>

                            </div>
  						<div class="col-12 mb-2 row">
                            <div class="col-6 pl-3">
                                {!! Form::label('title', __('Security Amount'), ['class' => 'control-label']) !!}
                                @include('backend.primary_currency')
                                {!! Form::text('security_amount', decimal_format($product->security_amount), ['class'=>'form-control', 'id' => 'security_amount', 'placeholder' => '200', 'onkeypress' => 'return isNumberKey(event)']) !!}
                            </div>

                            <div class="col-6 pr-3">
                                {!! Form::label('title', __('Quantity'),['class' => 'control-label']) !!}
                                {!! Form::number('variant_quantity[]', $product->variant[0]->quantity, ['class'=>'form-control', 'id' => 'quantity', 'placeholder' => '0', 'min' => '0', 'onkeypress' => 'return isNumberKey(event)']) !!}
                            </div>
							</div>
                        </div>
                        {{-- <div class="row mb-2">

                        </div> --}}
                        @if($product->category->categoryDetail->type_id  ==  10)
                            <div class="row mb-2" style="display: none;">
                                <div class="col-sm-3">
                                    {!! Form::label('title', __('Fix Check-in time'),['class' => 'control-label']) !!} <br />
                                    <input type="checkbox" bid="" id="is_fix_check_in_time" data-plugin="switchery" name="is_fix_check_in_time" class="chk_box" data-color="#43bee1" @if($product->is_fix_check_in_time == 1) checked @endif>
                                </div>
                                <div class="col-4 mb-2 check_in_time @if($product->is_fix_check_in_time != 1) d-none @endif">
                                    {!! Form::label('title', __('Check in time'), ['class' => 'control-label']) !!}
                                    {!! Form::text('check_in_time', $product->check_in_time, ['class'=>'form-control', 'id' => 'range-datepicker', 'placeholder' => '00:00']) !!}
                                </div>

                            </div>
                        @endif
                    @endif

                </div>
                @endif

                 {{-- marg data --}}
                 @if(!is_null($margProduct) && count($margProduct)>0)
                 <div class="card-box">
                     <h5 class="text-uppercase mt-0 mb-3 bg-light p-2 def">{{ __("Marg Data") }}</h5>
                     <div class="row mb-2">
                         @foreach ($margProduct as  $key => $feild)
                             @if ($key == 'id' || $key == 'product_id' ||  $key == 'rid' || $key == 'created_at' || $key == 'updated_at' || $key == 'Is_Deleted')
                                 @continue
                             @endif
                             @switch($key)
                                 @case('catcode')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Item Category Code'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_catcode', $feild, ['class'=>'form-control', 'id' => 'marg_catcode', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('code')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('	Item Code'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_code', $feild, ['class'=>'form-control', 'id' => 'marg_code', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('name')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Item Name'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_name', $feild, ['class'=>'form-control', 'id' => 'marg_name', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('stock')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Current Stock'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_stock', $feild, ['class'=>'form-control', 'id' => 'marg_stock', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('remark')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __($key), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_remark', $feild, ['class'=>'form-control', 'id' => 'marg_remark', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('company')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Name of Product Company'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_company', $feild, ['class'=>'form-control', 'id' => 'marg_company', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('shopcode')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Shop Code'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_shopcode', $feild, ['class'=>'form-control', 'id' => 'marg_shopcode', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('MRP')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Maximum Retail Price'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_MRP', $feild, ['class'=>'form-control', 'id' => 'marg_MRP', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('Rate')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Billing Price'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_Rate', $feild, ['class'=>'form-control', 'id' => 'marg_Rate', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('Deal')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Deal on'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_Deal', $feild, ['class'=>'form-control', 'id' => 'marg_Deal', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('Free')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Free Qty'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_Free', $feild, ['class'=>'form-control', 'id' => 'marg_Free', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('PRate')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Purchase Price'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_PRate', $feild, ['class'=>'form-control', 'id' => 'marg_PRate', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('curbatch')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Current Running batch of the item'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_curbatch', $feild, ['class'=>'form-control', 'id' => 'marg_curbatch', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('exp')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Expiry Date of current batch'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_exp', $feild, ['class'=>'form-control', 'id' => 'marg_exp', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('gcode')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Product Company Code'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_gcode', $feild, ['class'=>'form-control', 'id' => 'marg_gcode', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('MargCode')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Marg Code'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_MargCode', $feild, ['class'=>'form-control', 'id' => 'marg_MargCode', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('Conversion')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Item conversion'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_Conversion', $feild, ['class'=>'form-control', 'id' => 'marg_Conversion', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('Salt')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Salt code'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_Salt', $feild, ['class'=>'form-control', 'id' => 'marg_Salt', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('ENCODE')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Barcode'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_ENCODE', $feild, ['class'=>'form-control', 'id' => 'marg_ENCODE', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('remarks')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('Remarks information'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_remarks', $feild, ['class'=>'form-control', 'id' => 'marg_remarks', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break                                    
                             
                                 @case('Gcode6')
                                 <div class="col-4 mb-2">
                                 {!! Form::label('title', __('HSN code (internal code)'), ['class' => 'control-label']) !!}
                                 {!! Form::text('marg_Gcode6', $feild, ['class'=>'form-control', 'id' => 'marg_Gcode6', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                             
                                 @case('ProductCode')
                                 <div class="col-4 mb-2">
                                     {!! Form::label('title', __('Product Code'), ['class' => 'control-label']) !!}         
                                     {!! Form::text('marg_ProductCode', $feild, ['class'=>'form-control', 'id' => 'marg_ProductCode', 'placeholder' => '-- -- --', 'onkeypress' => 'return isNumberKey(event)', 'style'=>"cursor: not-allowed;"]) !!}
                                 </div> 
                                     @break
                         
                             @default
                                 Default case...
                         @endswitch

                         @endforeach
                     </div>
                 </div>
             @endif
             {{-- marg data --}}
             
                @if(($product->category->categoryDetail->type_id == 10) || (($product->category->categoryDetail->type_id == 7) && $client_preference_detail->is_hourly_pickup_rental == 1) )
                    @include('backend.product.popup.scheduleTableRows')
                    {{-- @include('backend.product.popup.addBlockTimeTablePopup') --}}
                    @include('backend.product.variant')
                @else


                    @if($productVariants->count() > 0)
                    <div class="card-box" >
                        <div class="row mb-2 bg-light">
                            <div class="col-8" style="margin:auto;">
                                <h5 class="text-uppercase mt-0 bg-light p-2">{{ __(getNomenclatureName('Variant')." Information") }}</h5>
                            </div>
                            @if(!empty($productVariants))
                            <div class="col-4 p-2 mt-0 text-right" style="margin:auto; ">
                                <button type="button" class="btn btn-info makeVariantRow"> {{ __("Make ".getNomenclatureName('Variant')." Sets") }}</button>
                            </div>
                            @endif
                        </div>

                        <p>{{ __("Select or change category to get ".getNomenclatureName('Variant')) }}</p>

                        <div class="row" style="width:100%; overflow-x: scroll;">
                            <div id="variantAjaxDiv" class="col-12 mb-2">
                                <h5 class="">{{__(getNomenclatureName('Variant').' List')}}</h5>
                                <div class="row mb-2">
                                    @foreach($productVariants as $vk => $var)
                                    <div class="col-sm-3">
                                        <label class="control-label">{{$var->title??null}}</label>
                                    </div>
                                    <div class="col-sm-9">
                                        @foreach($var->option as $key => $opt)
                                        @if(isset($opt) && !empty($opt->title) && isset($var) && !empty($var->title) )
                                            <div class="checkbox checkbox-success form-check-inline pr-3">
                                                <input type="checkbox" name="variant{{$var->id}}" class="intpCheck" opt="{{$opt->id.';'.$opt->title}}" varId="{{$var->id.';'.$var->title}}" id="opt_vid_{{$opt->id}}" @if(in_array($opt->id, $existOptions)) checked @endif>
                                                <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                                            </div>
                                        @endif
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            @if($product->has_variant == 1)
                            <div class="col-12" id="exist_variant_div">
                                <h5 class="">{{ __("Applied Variants Set") }}</h5>
                                <table class="table table-centered table-nowrap table-striped">
                                    <thead>
                                        <th>{{ __("Image") }}</th>
                                        <th>{{ __("Name") }}</th>
                                        <th>{{ __("Variants") }}</th>
                                        <th>{{ __("Price") }}</th>
                                        {{-- Role Price Column (START) --}}
                                        @if (isset($getAdditionalPreference['is_price_by_role']))
                                            @if($getAdditionalPreference['is_price_by_role'] == '1')
                                                <th>{{ __("Role's Price") }}</th>
                                            @endif
                                        @endif
                                        {{-- Role Price Column (END) --}}
                                        <th>{{ __('Compare at price') }}</th>
                                        <th>{{ __('Cost Price') }}</th>
                                        <th class="check_inventory">{{ __("Quantity") }}</th>
                                        <th>{{ __("Action") }}</th>
                                    </thead>
                                    <tbody id="product_tbody_{{$product->id}}">
                                        @foreach($product->variant as $varnt)
                                        @if($varnt->quantity > 0)
                                            <?php
                                            $existSet = array();

                                            $mediaPath = Storage::disk('s3')->url('default/default_image.png');

                                            if (!empty($varnt->vimage) && isset($varnt->vimage->pimage->image)) {
                                                $mediaPath = $varnt->vimage->pimage->image->path['proxy_url'] . '100/100' . $varnt->vimage->pimage->image->path['image_path'];
                                            }
                                            $existSet = explode('-', $varnt->sku);
                                            $vsets = '';

                                            foreach ($varnt->set as $vs) {
                                                if(isset($vs) && !empty($vs->title)){
                                                    $vsets .= $vs->title . ', ';
                                                }
                                            }
                                            ?>
                                            <tr id="tr_{{$varnt->id}}">
                                                <td>
                                                    <div class="image-upload">
                                                        <label class="file-input uploadImages" for="{{$varnt->id}}">
                                                            <img src="{{$mediaPath}}" width="30" height="30" for="{{$varnt->id}}" />
                                                        </label>
                                                    </div>
                                                    <div class="imageCountDiv{{$varnt->id}}"></div>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="variant_ids[]" value="{{$varnt->id}}">
                                                    <input type="hidden" class="exist_sets" value="{{$existSet[(count($existSet) - 1)]}}">
                                                    <input type="text" name="variant_titles[]" value="{{$varnt->title??null}}">
                                                </td>
                                                <td>{{rtrim($vsets, ', ')}}</td>
                                                <td>
                                                    @if (isset($getAdditionalPreference['is_price_by_role']))
                                                        @if($getAdditionalPreference['is_price_by_role'] == '1')
                                                            <input type="text" style="width: 70px;" name="variant_price[]" value="{{decimal_format($varnt->getRawOriginal('price') )}}" onkeypress="return isNumberKey(event)">
                                                        @else
                                                            <input type="text" style="width: 70px;" name="variant_price[]" value="{{decimal_format($varnt->price)}}" onkeypress="return isNumberKey(event)">
                                                        @endif
                                                    @endif
                                                </td>
                                                @if (isset($getAdditionalPreference['is_price_by_role']))
                                                    @if($getAdditionalPreference['is_price_by_role'] == '1')
                                                        <td>
                                                            <a href="javascript:void(0);" title="Add Price Based On Roles" class="action-icon rolePriceModal" data-toggle="modal" data-target="#rolePriceModal" data-varient-id="{{$varnt->id}}" data-product-id="{{$product->id}}">
                                                                <i class="mdi mdi-loupe"></i>
                                                            </a>
                                                        </td>
                                                    @endif
                                                @endif
                                                <td>
                                                    <input type="text" style="width: 100px;" name="variant_compare_price[]" value="{{decimal_format($varnt->compare_at_price)}}" onkeypress="return isNumberKey(event)">
                                                </td>
                                                <td>
                                                    <input type="text" style="width: 70px;" name="variant_cost_price[]" value="{{decimal_format($varnt->cost_price)}}" onkeypress="return isNumberKey(event)">
                                                </td>
                                                <td class="check_inventory">
                                                    <input type="text" style="width: 70px;" name="variant_quantity[]" value="{{$varnt->quantity}}" onkeypress="return isNumberKey(event)">
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);" data-varient_id="{{$varnt->id}}" class="action-icon deleteExistRow">
                                                        <i class="mdi mdi-delete"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    </tbody>
                                </table>
                            </div>
                            @endif
                            <div id="variantRowDiv" class="col-12"></div>
                        </div>

                    </div>
                    @endif
                @endif
                @if($getAdditionalPreference['product_measurment'] == 1)
                        <div class="card-box">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-centered table-nowrap table-striped" id="measurementsTable">
                                        <thead>
                                            <tr>
                                                @if($product->has_variant)
                                                <th>{{__('Variant')}}</th>
                                                @endif
                                                @foreach($measurements as $data)
                                                    <th>{{ $data->key }} (In cm)</th>
                                                @endforeach
                                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($product->has_variant)
                                                @foreach($product->variant as $varnt)
                                                @if($varnt->quantity > 0)

                                                @php
                                                    $title = $varnt->title;
                                                    $parts = explode('-', $title);
                                                    $letterAfterDash = end($parts);
                                                @endphp
                                                    <tr class="measurement-row">
                                                        <td class="variant-cell">
                                                            <label for="variant_id" name="variant_id[]" value="{{ $varnt->id }}">{{ $letterAfterDash }}</label>
                                                        </td>
                                                        @foreach($measurements as $data)
                                                        <td>
                                                            <input type="hidden" name="key_id[]" value="{{ $data->id }}">
                                                            <input type="{{ $data->field_type == 0 ? 'text' : '' }}" class="form-control" name="key_value[{{ $data->id }}][{{ $varnt->id }}][]" value="{{ isset($productMeasurementData[$data->id][$varnt->id]) ? $productMeasurementData[$data->id][$varnt->id] : '' }}" placeholder="Enter the measurement in cm" required>
                                                        </td>
                                                        @endforeach

                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    </tr>
                                                @endif
                                                @endforeach
                                            @else
                                                <tr class="measurement-row">
                                                    @foreach($measurements as $data)
                                                    <td>
                                                        <input type="hidden" name="key_id[]" value="{{ $data->id }}">
                                                        <input type="{{ $data->field_type == 0 ? 'text' : '' }}" class="form-control" name="key_value[]" value="{{ isset($productMeasurementData[$data->id][null]) ? $productMeasurementData[$data->id][null] : '' }}" placeholder="Enter the measurement in cm" required>
                                                    </td>
                                                    @endforeach
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                @if( p2p_module_status() || is_attribute_enabled())
                    @if(!empty($productAttributes))
                    <div id="attribute_section">
                        @include('layouts.shared.product-attribute')
                    </div>
                    @endif
                @endif
            </div>
            <div class="col-lg-5">
                <!-- <div class="card-box ">
                    <div class="row mb-2 bg-light">
                        <div class="col-6" style="margin:auto; padding: 8px !important;">
                            <h5 class="text-uppercase  mt-0 mb-0">Public URL</h5>
                        </div>
                        <div class="col-6 p-2 mt-0" style="margin:auto; padding: 8px !important;">
                            <div class="site_link position-relative">
                                <a href="{{route('productDetail',$product->url_slug)}}" target="_blank"><span id="pwd_spn" class="password-span">{{route('productDetail',$product->url_slug)}}</span></a>
                                <label class="copy_link float-right" id="cp_btn" title="copy">
                                    <img src="{{ asset('assets/icons/domain_copy_icon.svg')}}" alt="">
                                    <span class="copied_txt" id="show_copy_msg_on_click_copy" style="display:none;">Copied</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="card-box">
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">{{ __("Other Information") }}</h5>
                    <div class="row mb-2">

                        @if(@getAdditionalPreference(['is_recurring_booking'])['is_recurring_booking'] == 1)
                            <div class="col-md-6 d-flex justify-content-between mb-2">
                                {!! Form::label('title', __('Recurring Booking'),['class' => 'control-label']) !!}
                                <input type="checkbox" id="is_recurring_booking" data-plugin="switchery" name="is_recurring_booking" class="chk_box" data-color="#43bee1" @if($product->is_recurring_booking == 1) checked @endif>
                            </div>
                        @endif
                        @if(!in_array($client_preference_detail->business_type,['taxi','laundry']))
                                @if(Auth::user()->is_superadmin == 1)
                                    <div class="col-md-6 d-flex justify-content-between mb-2">
                                        {!! Form::label('title', __('New'),['class' => 'control-label']) !!}
                                        <input type="checkbox" id="is_new" data-plugin="switchery" name="is_new" class="chk_box" data-color="#43bee1" @if($product->is_new == 1) checked @endif>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-between mb-2">
                                        {!! Form::label('title', __('Featured'),['class' => 'control-label']) !!}
                                        <input type="checkbox" id="is_featured" data-plugin="switchery" name="is_featured" class="chk_box" data-color="#43bee1" @if($product->is_featured == 1) checked @endif>
                                    </div>
                            @endif
                        @endif
                        {{-- $configData->need_delivery_service == 1 &&  --}}
                        {{-- @if($product->category->categoryDetail->type_id != 7 && (!in_array($client_preference_detail->business_type,['taxi','laundry']))) --}}
                        @if((!in_array($product->category->categoryDetail->type_id,$lastmileShow)) && (!in_array($client_preference_detail->business_type,['taxi','laundry'])))
                        <!-- <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Requires Last Mile Delivery'),['class' => 'control-label']) !!}
                            <input type="checkbox" id="last_mile" data-plugin="switchery" name="last_mile" class="chk_box" data-color="#43bee1" @if($product->Requires_last_mile == 1) checked @endif>
                        </div> -->
                        @endif
                        @if($configData->pharmacy_check == 1)
                        <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Requires Prescription'),['class' => 'control-label']) !!}
                            <input type="checkbox" bid="" id="pharmacy_check" data-plugin="switchery" name="pharmacy_check" class="chk_box" data-color="#43bee1" @if($product->pharmacy_check == 1) checked @endif>
                        </div>
                        <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Validate Prescription'),['class' => 'control-label']) !!}
                            <input type="checkbox" bid="" id="validate_prescription_check" data-plugin="switchery" name="validate_prescription_check" class="chk_box" data-color="#43bee1" @if($product->validate_pharmacy_check == 1) checked @endif>
                        </div>
                        @endif
                        <!-- @if($configData->enquire_mode == 1)
                        <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Inquiry Only'),['class' => 'control-label']) !!}
                            <input type="checkbox" bid="" id="inquiry_only" data-plugin="switchery" name="inquiry_only" class="chk_box" data-color="#43bee1" @if($product->inquiry_only == 1) checked @endif>
                        </div>
                        @endif -->

                        <!-- <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Returnable'),['class' => 'control-label']) !!}
                            <input type="checkbox" bid="" id="returnable" data-plugin="switchery" name="returnable" class="chk_box" data-color="#43bee1" @if($product->returnable == 1) checked @endif>
                        </div> -->

                        <!-- <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Replaceable'),['class' => 'control-label']) !!}
                            <input type="checkbox" bid="" id="replaceable" data-plugin="switchery" name="replaceable" class="chk_box" data-color="#43bee1" @if($product->replaceable == 1) checked @endif>
                        </div> -->
                        <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Spotlight Deals'),['class' => 'control-label']) !!}
                            <input type="checkbox" bid="" id="replaceable" data-plugin="switchery" name="spotlight_deals" class="chk_box" data-color="#43bee1" @if($product->spotlight_deals == 1) checked @endif>
                        </div>
                        @if($configData->need_dispacher_ride == 1 && $product->category->categoryDetail->type_id == 7)
                        <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Dispatcher Tags'),['class' => 'control-label']) !!}
                            <select class="selectize-select1 form-control" name="tags" required>
                                @if($agent_dispatcher_tags != null && count($agent_dispatcher_tags))
                                @foreach($agent_dispatcher_tags as $key => $tags)
                                <option value="{{ $tags['name'] }}" @if($product->tags == $tags['name']) selected="selected" @endif>{{ ucfirst($tags['name']) }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        @elseif($product->category->categoryDetail->type_id == 1)
                        <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Individual Delivery Fee'),['class' => 'control-label']) !!}
                            <input type="checkbox" bid="" id="individual_delivery_fee" data-plugin="switchery" name="individual_delivery_fee" class="chk_box" data-color="#43bee1" @if($product->individual_delivery_fee == 1) checked @endif>
                        </div>


                        <div class="col-md-6 justify-content-between mb-2" id="dispatcher_tags_div">
                            <div class="row">
                                <div class="col-md-5">
                                    {!! Form::label('title', __('Dispatcher Tags'),['class' => 'control-label']) !!}
                                </div>
                                <div class="col-md-7">
                                    <select class="selectize-select1 form-control" name="tags">

                                        @if($agent_dispatcher_tags != null && count($agent_dispatcher_tags))
                                        @foreach($agent_dispatcher_tags as $key => $tags)
                                        <option value="{{ $tags['name'] }}" @if($product->tags == $tags['name']) selected="selected" @endif>{{ ucfirst($tags['name']) }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($product->vendor->same_day_delivery == 1 && $getAdditionalPreference['is_same_day_delivery'])
                            <div class="col-md-6 d-flex justify-content-between mb-2">
                                {!! Form::label('title', __('Same Day Delivery'),['class' => 'control-label']) !!}
                                <input type="checkbox" id="same_day_delivery" data-plugin="switchery" name="same_day_delivery" class="chk_box" data-color="#43bee1" @if($product->same_day_delivery == 1) checked @endif>
                            </div>
                        @endif
                        @if($product->vendor->next_day_delivery == 1 && $getAdditionalPreference['is_next_day_delivery'])
                            <div class="col-md-6 d-flex justify-content-between mb-2">
                                {!! Form::label('title', __('Next Day Delivery'),['class' => 'control-label']) !!}
                                <input type="checkbox" id="next_day_delivery" data-plugin="switchery" name="next_day_delivery" class="chk_box" data-color="#43bee1" @if($product->next_day_delivery == 1) checked @endif>
                            </div>
                        @endif
                        @if($product->vendor->hyper_local_delivery == 1 && $getAdditionalPreference['is_hyper_local_delivery'])
                            <div class="col-md-6 d-flex justify-content-between mb-2">
                                {!! Form::label('title', __('Hyper Local Delivery'),['class' => 'control-label']) !!}
                                <input type="checkbox" id="hyper_local_delivery" data-plugin="switchery" name="hyper_local_delivery" class="chk_box" data-color="#43bee1" @if($product->hyper_local_delivery == 1) checked @endif>
                            </div>
                        @endif

                        @if($product->vendor->next_day_delivery == 1 || $product->vendor->same_day_delivery == 1)
                            @php
                                $pro_delivery_slot_ids = $product->syncProductDeliverySlot->pluck('id')->toArray();
                            @endphp
                            <div class="col-sm-12 custom_select">
                                {!! Form::label('title', __('Choose Slots'),['class' => 'control-label']) !!}
                                <select class="selectizeInput form-control" id="select_slot" name="slot_ids[]" multiple>
                                    <option value="">Choose Slots</option>
                                    @if(@$delivery_slots)
                                        @foreach ($delivery_slots ?? '' as $slot)
                                            <option value="{{$slot->id}}" @if(in_array($slot->id, $pro_delivery_slot_ids)) selected @endif>{{$slot->title.' ( '.$slot->start_time.'-'.$slot->end_time.' )'}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif
                        
                        @if($product->vendor->pick_drop == 1 && $product->vendor->is_vendor_instant_booking == 1 && $configData->is_one_push_book_enable == 1 && $product->category->categoryDetail->type_id == 7)
                        <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Available for Instant Booking'),['class' => 'control-label']) !!}
                            <input type="checkbox" data-plugin="switchery" name="is_product_instant_booking" id="is_product_instant_booking" class="form-control" data-color="#43bee1" @if($product->is_product_instant_booking == 1) checked @endif>
                        </div>
                        @endif
                       <input type="hidden" name="mode_of_service" value="schedule">
                        <!-- <div class="col-md-6 d-flex justify-content-between mb-2">
                                {!! Form::label('title', __('Mode Of Service'),['class' => 'control-label']) !!}
                                <select class="selectize-select1 form-control" name="mode_of_service" id="mode_of_service" required>
                                    <option value="instant" @if($product->mode_of_service == 'instant') selected="selected" @endif>{{ __('Instant') }}</option>
                                    <option value="schedule" @if($product->mode_of_service == 'schedule') selected="selected" @endif>{{ __('Schedule') }}</option>
                                </select>
                        </div> -->

                        @if(($configData->need_dispacher_home_other_service == 1 && ($product->category->categoryDetail->type_id == 8)) || ($configData->need_appointment_service == 1 && $product->category->categoryDetail->type_id == 12 ) )
                        @if($product->Requires_last_mile == 1 )
                            <div class="col-md-6 d-flex justify-content-between mb-2">
                                {!! Form::label('title', __('Dispatcher Tags'),['class' => 'control-label']) !!}
                                <select class="selectize-select1 form-control" name="tags" required>
                                    @if($agent_dispatcher_on_demand_tags != null && count($agent_dispatcher_on_demand_tags))
                                        @foreach($agent_dispatcher_on_demand_tags as $key => $tags)
                                        <option value="{{ $tags['name'] }}" @if($product->tags == $tags['name']) selected="selected" @endif>{{ ucfirst($tags['name']) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                         @endif
                            
                            @if($product->Requires_last_mile == 1 )
                            @if($configData->need_appointment_service == 1 && $product->category->categoryDetail->type_id == 12 )
                                <div class="col-md-6 d-flex justify-content-between mb-2 dispatch_Agent">
                                    {!! Form::label('title', __('Sloting from Dispatch'),['class' => 'control-label']) !!}
                                    <input type="checkbox" id="is_slot_from_dispatch" data-plugin="switchery" name="is_slot_from_dispatch" class="chk_box" data-color="#43bee1" @if(@$product->is_slot_from_dispatch == 1) checked @endif>
                                </div>
                                <div class="col-md-6 d-flex justify-content-between mb-2 dispatch_Agent">
                                    {!! Form::label('title', __('Show Dispatch Agent'),['class' => 'control-label']) !!}
                                    <input type="checkbox" id="is_show_dispatcher_agent" data-plugin="switchery" name="is_show_dispatcher_agent" class="chk_box" data-color="#43bee1" @if(@$product->is_show_dispatcher_agent == 1) checked @endif>
                                </div>
                            @endif
                            @endif
                        @endif
                        @if($configData->age_restriction_on_product_mode == 1)
                        <div class="col-md-6 d-flex justify-content-between mb-2">
                            {!! Form::label('title', __('Age Restriction'),['class' => 'control-label']) !!}
                            <input type="checkbox" bid="" id="age_restriction" data-plugin="switchery" name="age_restriction" class="chk_box" data-color="#43bee1" @if($product->age_restriction == 1) checked @endif>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-sm-12 mb-2">
                            {!! Form::label('title', __('Live'),['class' => 'control-label']) !!}
                            <select class="selectizeInput form-control" id="is_live" name="is_live">
                                <option value="0" @if($product->is_live == 0) selected @endif>{{ __('Unavailable')}}</option>
                                @if (isset($getAdditionalPreference['is_seller_module']) && $getAdditionalPreference['is_seller_module'] == 1)
                                    @if(Auth::user()->is_superadmin == 1)
                                        <option value="1" @if($product->is_live == 1) selected @endif>{{ __('Available')}}</option>
                                    @endif
                                     @if($product->vendor->is_seller == 0)
                                        <option value="1" @if($product->is_live == 1) selected @endif>{{ __('Available')}}</option>
                                    @endif
                                @else
                                    <option value="1" @if($product->is_live == 1) selected @endif>{{ __('Available')}}</option>
                                @endif
                            </select>
                        </div>

                        @if( !in_array($product->category->categoryDetail->type_id,[8,7,12]))
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Brand'),['class' => 'control-label']) !!}
                            <select class="form-control " id="brand_idBox" name="brand_id">
                                <option value="">{{ __('Select')}}</option>
                                @foreach($brands as $brand)
                                <option value="{{$brand->id}}" @if(!empty($product->brand) && $product->brand->id == $brand->id) selected @endif>{{$brand->title??null}}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Tax Category'),['class' => 'control-label']) !!}
                            <select class="form-control " id="typeSelectBox" name="tax_category">
                                <option value="">{{ __('Select')}}</option>
                                @foreach($taxCate as $cate)
                                <option value="{{$cate->id}}" {{ $product->tax_category_id == $cate->id ? 'selected' : ''}} >{{$cate->title??null}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                   
                    {{--@if($configData->minimum_order_batch == 1 || $product->minimum_order_count > 0)--}}
                    <div class="row">
                        @if($getAdditionalPreference['is_price_by_role'] == '1')
                            <div class="col-md-6 mb-2">
                                {!! Form::label('title', __('Minimum Order Count'),['class' => 'control-label']) !!}
                                {!! Form::number('minimum_order_count', $product->getRawOriginal('minimum_order_count'), ['class'=>'form-control', 'id' => 'minimum_order_count', 'placeholder' => '0', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)']) !!}
                            </div>
                        @else
                            <div class="col-md-6 mb-2">
                                {!! Form::label('title', __('Minimum Order Count'),['class' => 'control-label']) !!}
                                {!! Form::number('minimum_order_count', $product->minimum_order_count, ['class'=>'form-control', 'id' => 'minimum_order_count', 'placeholder' => '0', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)']) !!}
                            </div>
                        @endif

                        <!-- <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Minimum Increment'),['class' => 'control-label']) !!}
                            {!! Form::number('batch_count', $product->batch_count, ['class'=>'form-control', 'id' => 'batch_count', 'placeholder' => '0', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)']) !!}
                        </div> -->

                        <!-- <div class="col-md-6 mb-2">
                            <div class="form-group" id="per_hour_price">
                                {!! Form::label('title', __('Per Hour Price'),['class' => 'control-label']) !!}
                                <input class="form-control" name="per_hour_price" type="text" value="{{$product->per_hour_price}}" onkeypress="return isNumberKey(event)" maxlength="6">
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-group" id="km_included">
                                {!! Form::label('title', __('Kilometers Inclued with Rental'),['class' => 'control-label']) !!}
                                <input class="form-control" name="km_included" type="text" value="{{$product->km_included}}" onkeypress="return isNumberKey(event)" maxlength="4">
                            </div>
                        </div> -->
                        
                        <!-- <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Return/Replace Days'),['class' => 'control-label']) !!}
                            {!! Form::number('return_days', $product->return_days, ['class'=>'form-control', 'id' => 'return_days', 'placeholder' => '0', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)']) !!}
                        </div> -->
                        @if($product->category->categoryDetail->slug == 'yacht')
                            <div class="col-md-6 mb-2">
                                {!! Form::label('title', __('Select Destination'),['class' => 'control-label']) !!}
                                {!! Form::select('destination_id', [], $product->destination_id ,['class'=>'form-control', 'id' => 'destination_id']) !!}
                            </div>
                        @endif
                        <!-- <div class="col-md-6 mb-2">
                            <label for="title" class="control-label">{{ __("Pickup Date") }}</label>
                            <input class="form-control" id="pickup_time" name="pickup_time" type="datetime-local" value="{{ $product->pickup_time ?? ''}}">
                        </div> -->
                        <!-- <div class="col-md-6 mb-2">
                            <label for="title" class="control-label">{{ __("Drop Date") }}</label>
                            <input class="form-control" id="drop_time" name="drop_time" type="datetime-local" value="{{ $product->drop_time ?? ''}}">
                        </div> -->
                        <!-- <div class="col-md-6 mb-2">
                        {!! Form::label('title', __('Cashback'),['class' => 'control-label']) !!}
                        {!! Form::number('cashback', $product->cashback, ['class'=>'form-control', 'id' => 'cashback', 'placeholder' => '0', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)']) !!}
                    </div> -->
                        @if($product->category->categoryDetail->slug == 'rental')
                            <div class="col-md-6 mb-2">
                                {!! Form::label('title', __('Select Booking Option'),['class' => 'control-label']) !!}
                                <select class="form-control select2-multiple" name="booking_option[]" data-toggle="select2" multiple="multiple" placeholder="Select booking option...">
                                    @foreach($bookingOption as $set)
                                    <option value="{{$set->id}}" @if(in_array($set->id, $productBookingOption)) selected @endif>{{$set->title??null}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                {!! Form::label('title', __('Select Rental Protection'),['class' => 'control-label']) !!}
                                <select class="form-control select2-multiple" name="rental_protection[]" data-toggle="select2" multiple="multiple" placeholder="Select Rental Protection...">
                                    @foreach($rentalProtection as $set)
                                    <option value="{{$set->id}}" @if(in_array($set->id, $productRentalProtection)) selected @endif>{{$set->title??null}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                {!! Form::label('title', __('Included Rental Protection'),['class' => 'control-label']) !!}
                                <select class="form-control select2-multiple" name="included_rental_protection[]" data-toggle="select2" multiple="multiple" placeholder="Select Included Rental Protection...">
                                    @foreach($rentalProtection as $set)
                                    <option value="{{$set->id}}" @if(in_array($set->id, $inlcudedProductRentalProtection)) selected @endif>{{$set->title??null}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($product->category->categoryDetail->slug == 'yacht')
                            <div class="col-md-6 mb-2">
                                {!! Form::label('captain_name', __('Captain Name'),['class' => 'control-label']) !!}
                                <input class="form-control" id="captain_name" name="captain_name" type="text" value="{{ $product->captain_name ?? ''}}">
                            </div>
                            <div class="col-md-6 mb-2">
                                {!! Form::label('captain_description', __('Captain Description'),['class' => 'control-label']) !!}
                                <input class="form-control" id="captain_description" name="captain_description" type="text" value="{{ $product->captain_description ?? ''}}">
                            </div>
                            <div class="col-md-6 mb-2">
                                {!! Form::label('captain_profile', __('Captain Profile'),['class' => 'control-label']) !!}
                                <input class="form-control" id="captain_profile" name="captain_profile" type="file">
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        @if(isset($getAdditionalPreference['is_price_by_role']) && $getAdditionalPreference['is_price_by_role'] == '1')
                            @if (isset($roles))
                                @foreach ($roles as $key => $role)
                                    @php
                                        $label_min = 'Minimum Order Count ['.$role->role.']';
                                        $input_min = 'minimum_order_count['.$role->role.']';
                                    @endphp

                                    @if( $role->role != 'Corporate_user')
                                        <div class="col-md-6 mb-2">
                                            {!! Form::label('title', $label_min,['class' => 'control-label']) !!}
                                            <input type="number" class="form-control" min="0" onkeyup="isNumberKey(event)" placeholder="0" name="minimum_order_count_arr[{{$role['id']}}]" value="{{ isset($product->productByRoleForAdmin[$key]) ? (decimal_format($product->productByRoleForAdmin[$key]->minimum_order_count) ?? 0.00) : 0.00 }}">
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        @endif
                    </div>

                    {{-- product free delivery fees --}}
                    @if($getAdditionalPreference['is_free_delivery_by_roles'] == '1' && 0)
                        <div class="row">
                            <div class="col-md-12">
                                <label class="control-label">Free Delivery (Select Roles)</label>
                                <select class="form-control select2-multiple" name="free_delivery_roles[]" data-toggle="select2" multiple="multiple" placeholder="Select role...">
                                    @foreach($allRoles as $allRole)
                                        <option value="{{$allRole->id}}" @if(in_array($allRole->id, $selectedRoles)) selected @endif>{{ $allRole->role }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                    @endif
                    {{--@endif--}}



                    {{--
                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Service Charges'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="service_charges_tax" class="chk_box" data-color="#43bee1" @if($product->service_charges_tax == 1) checked @endif>
                    </div>

                    <div class="form-group w-100" style="display:{{$product->service_charges_tax == 0 ? 'none!important' : 'block'}}" id="service_charges_tax_id">
                     {!! Form::label('title', __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="service_charges_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$product->service_charges_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div>



                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Delivery Charges'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="delivery_charges_tax" class="form-control" data-color="#43bee1" @if($product->delivery_charges_tax == 1) checked @endif>
                    </div>

                    <div class="form-group w-100" style="display:{{$product->delivery_charges_tax == 0 ? 'none!important' : 'block'}}" id="delivery_charges_tax_id">
                     {!! Form::label('title', __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="delivery_charges_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$product->delivery_charges_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div>



                    <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Fixed Fee'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="fixed_fee_tax" class="form-control" data-color="#43bee1" @if($product->fixed_fee_tax == 1) checked @endif>
                    </div>

                    <div class="form-group w-100" style="display:{{$product->fixed_fee_tax == 0 ? 'none!important' : 'block'}}" id="fixed_fee_tax_id">
                     {!! Form::label('title', __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="fixed_fee_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$product->fixed_fee_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div> --}}

                    {{-- <div class="row">
                        <div class="col-md-12">
                        <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">{{ __("Taxes") }}</h5>
                        </div>
                    </div> --}}

                    {{-- <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                        {!! Form::label('title', __('On Container Charges'),['class' => 'control-label']) !!}
                        <input type="checkbox" data-plugin="switchery" name="container_charges_tax" class="form-control" data-color="#43bee1" @if($product->container_charges_tax == 1) checked @endif>
                    </div> --}}


                    @if($product->vendor->need_container_charges)
                    <div class="form-group w-100" id="container_charges_tax_id">
                        <input type="hidden" value="on" name="container_charges_tax">
                     {!! Form::label('title',__('On Container Charges') .' '. __('Taxes Available'),['class' => 'control-label']) !!}
                        <select class="form-control" name="container_charges_tax_id">
                            <option value="">{{__('Select any')}}</option>
                            @foreach(taxRates() as $row)
                                <option value="{{$row->id}}" {{$product->container_charges_tax_id == $row->id ? 'selected' : ''}}>{{$row->identifier}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if($configData->delay_order == 1 || $product->delay_order_hrs > 0 || $product->delay_order_min > 0)
                    @if(in_array($configData->business_type,['laundry']))
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            {!! Form::label('title', __('Set Pickup Delay Time'),['class' => 'control-label mb-0']) !!}
                         </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Hrs'),['class' => 'control-label']) !!}
                             <input type="number"  class="form-control" value="{{$product->pickup_delay_order_hrs}}" name="pickup_delay_order_hrs" placeholder="{{__('hrs')}}">
                        </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Minutes'),['class' => 'control-label']) !!}
                           <input type="number"  class="form-control" value="{{$product->pickup_delay_order_min}}" name="pickup_delay_order_min" placeholder="{{__('minutes')}}">
                       </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-2">
                            {!! Form::label('title', __('Set DropoffDelay Time'),['class' => 'control-label mb-0']) !!}
                         </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Hrs'),['class' => 'control-label']) !!}
                             <input type="number"  class="form-control" value="{{$product->dropoff_delay_order_hrs}}" name="dropoff_delay_order_hrs" placeholder="{{__('hrs')}}">
                        </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Minutes'),['class' => 'control-label']) !!}
                           <input type="number"  class="form-control" value="{{$product->dropoff_delay_order_min}}" name="dropoff_delay_order_min" placeholder="{{__('minutes')}}">
                       </div>
                    </div>
                    @else
                    @php
                        $Delivery = getNomenclatureName('Delivery', true);
                        $Delivery = ($Delivery === 'Delivery') ? __('Delivery') : $Delivery;
                        $Dine_In = getNomenclatureName('Dine-In', true);
                        $Dine_In = ($Dine_In === 'Dine-In') ? __('Dine-In') : $Dine_In;
                        $Takeaway = getNomenclatureName('Takeaway', true);
                        $Takeaway = ($Takeaway === 'Takeaway') ? __('Takeaway') : $Takeaway;
                    @endphp
                    <div class="row mt-2">
                        <label class="control-label">{{__('Set Delay Time')}}</label>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::label('title', __('For ').$Delivery,['class' => 'control-label']) !!}
                         </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Hrs'),['class' => 'control-label']) !!}
                             <input type="number"  class="form-control" value="{{$product->delay_order_hrs}}" name="delay_order_hrs" placeholder="{{__('hrs')}}">
                        </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Minutes'),['class' => 'control-label']) !!}
                           <input type="number"  class="form-control" value="{{$product->delay_order_min}}" name="delay_order_min" placeholder="{{__('minutes')}}">
                       </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::label('title', __('For ').$Dine_In,['class' => 'control-label']) !!}
                         </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Hrs'),['class' => 'control-label']) !!}
                             <input type="number"  class="form-control" value="{{$product->delay_order_hrs_for_dine_in}}" name="delay_order_hrs_for_dine_in" placeholder="{{__('hrs')}}">
                        </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Minutes'),['class' => 'control-label']) !!}
                           <input type="number"  class="form-control" value="{{$product->delay_order_min_for_dine_in}}" name="delay_order_min_for_dine_in" placeholder="{{__('minutes')}}">
                       </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::label('title',__('For ').$Takeaway,['class' => 'control-label']) !!}
                         </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Hrs'),['class' => 'control-label']) !!}
                             <input type="number"  class="form-control" value="{{$product->delay_order_hrs_for_takeway}}" name="delay_order_hrs_for_takeway" placeholder="{{__('hrs')}}">
                        </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Minutes'),['class' => 'control-label']) !!}
                           <input type="number"  class="form-control" value="{{$product->delay_order_min_for_takeway}}" name="delay_order_min_for_takeway" placeholder="{{__('minutes')}}">
                       </div>
                    </div>
                    @endif
                    @endif
                    <!-- @if (isset($getAdditionalPreference['is_product_measurement_in_cm_kg']) && $getAdditionalPreference['is_product_measurement_in_cm_kg'] == 1)
                        <div class="row mt-2 mb-2 physicalDiv" style="{{ ($product->is_physical == 1) ? '' : '' }}">
                            <div class="col-sm-4">
                                {!! Form::label('title', 'Length (In Centimeter)',['class' => 'control-label']) !!}
                                {!! Form::text('length', $product->length,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => '10.0']) !!}
                            </div>

                            <div class="col-sm-4">
                                {!! Form::label('title', 'Width (In Centimeter)',['class' => 'control-label']) !!}
                                {!! Form::text('breadth', $product->breadth,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => '12.0']) !!}
                            </div>

                            <div class="col-sm-4">
                                {!! Form::label('title', 'Height (In Centimeter)',['class' => 'control-label']) !!}
                                {!! Form::text('height', $product->height,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => '8.0']) !!}
                            </div>

                            <div class="col-sm-4">
                                {!! Form::label('title', 'Weight (In Kg)',['class' => 'control-label']) !!}
                                {!! Form::text('weight', $product->weight,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => '15.0']) !!}
                            </div>
                        </div>
                    @else
                        <div class="row mt-2 mb-2 physicalDiv" style="{{ ($product->is_physical == 1) ? '' : '' }}">
                            <div class="col-sm-4">
                                {!! Form::label('title', 'Length (In Inches)',['class' => 'control-label']) !!}
                                {!! Form::text('length', $product->length,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => '10.0']) !!}
                            </div>

                            <div class="col-sm-4">
                                {!! Form::label('title', 'Width (In Inches)',['class' => 'control-label']) !!}
                                {!! Form::text('breadth', $product->breadth,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => '12.0']) !!}
                            </div>

                            <div class="col-sm-4">
                                {!! Form::label('title', 'Height (In Inches)',['class' => 'control-label']) !!}
                                {!! Form::text('height', $product->height,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => '8.0']) !!}
                            </div>

                            <div class="col-sm-4">
                                {!! Form::label('title', 'Weight (In Pounds)',['class' => 'control-label']) !!}
                                {!! Form::text('weight', $product->weight,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => '15.0']) !!}
                            </div>
                        </div>
                    @endif -->

                    <!-- <div class="row mb-2">
                        {!! Form::label('title', 'Physical',['class' => 'control-label col-sm-2']) !!}
                        <div class="col-sm-4">
                            <input type="checkbox" bid="" id="is_physical" data-plugin="switchery" name="is_physical" class="chk_box" data-color="#43bee1" @if($product->is_physical == 1) checked @endif>
                        </div>
                    </div>
                    <div class="row mb-2 physicalDiv" style="{{ ($product->is_physical == 1) ? '' : 'display: none;' }}">
                        <div class="col-sm-6">
                            {!! Form::label('title', 'Weight',['class' => 'control-label']) !!}
                            {!! Form::text('weight', $product->weight,['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)']) !!}
                        </div>
                        <div class="col-sm-6">
                            {!! Form::label('title', 'Weight Unit',['class' => 'control-label']) !!}
                            {!! Form::text('weight_unit', $product->weight_unit,['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="row mb-2 physicalDiv" style="{{ ($product->is_physical==1) ? '' : 'display:none;' }}">
                        {!! Form::label('title', 'Required Shipping',['class' => 'control-label col-sm-3 mb-2']) !!}
                        <div class="col-sm-3 mb-2">
                            <input type="checkbox" id="requiredShipping" data-plugin="switchery" name="require_ship" class="chk_box" data-color="#43bee1" @if($product->requires_shipping == 1) checked @endif>
                        </div>
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6 shippingDiv" style="{{($product->requires_shipping == 1) ? '' : 'display:none;' }}">
                            {!! Form::label('title', 'Country Origin',['class' => 'control-label']) !!}
                            <select class="form-control selectizeInput" id="country_origin_id" name="country_origin_id">
                                @foreach($countries as $coun)
                                <option value="{{$coun->id}}" @if($product->country_origin_id == $coun->id) selected @endif>{{$coun->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> -->

                    @if($product->vendor->pick_drop == 1 && $product->category->categoryDetail->type_id == 7)
                    <div class="row mb-2">
                        <div class="col-md-6 d-flex align-items-center justify-content-between">
                            {!! Form::label('title', __('Toll Tax'),['class' => 'control-label']) !!}
                            <input type="checkbox" data-plugin="switchery" name="is_toll_tax" id="is_toll_tax" class="form-control" data-color="#43bee1" @if($product->is_toll_tax == 1) checked @endif>
                        </div>
                        <div class="col-sm-6" id="is_toll_tax_div1" style="display:@if($product->is_toll_tax == 1) @else none @endif;">
                            {!! Form::label('title', __('Travel Mode'),['class' => 'control-label']) !!} <a href="https://developers.google.com/maps/documentation/routes_preferred/reference/rest/Shared.Types/RouteTravelMode" target="_blank"><i class="fas fa-info-circle"></i></a>
                            <select class="form-control" name="travel_mode" data-toggle="select2" placeholder="Select Travel Mode...">
                                @foreach($travelMode as $cel)
                                <option value="{{$cel->id}}" @if($cel->id==$product->travel_mode_id) selected @endif> {{$cel->travel_mode_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2" id="is_toll_tax_div2" style="display:@if($product->is_toll_tax == 1) @else none @endif;">
                        <div class="col-sm-6 mb-1">
                            {!! Form::label('title', __('TollPass eg. IN_FASTAG'),['class' => 'control-label']) !!} <a href="https://developers.google.com/maps/documentation/routes_preferred/reference/rest/Shared.Types/TollPass" target="_blank"><i class="fas fa-info-circle"></i></a>
                            <select class="form-control" name="toll_passes" data-toggle="select2" placeholder="Select Tollpass...">
                                @foreach($tollPassOrigin as $cel)
                                <option value="{{$cel->id}}" @if($cel->id == $product->toll_pass_id) selected @endif > {{$cel->toll_pass_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 mb-1">
                            {!! Form::label('title', __('Emission Type eg. GASOLINE'),['class' => 'control-label']) !!} <a href="https://developers.google.com/maps/documentation/routes_preferred/reference/rest/Shared.Types/VehicleEmissionType" target="_blank"><i class="fas fa-info-circle"></i></a>
                            <select class="form-control" name="emission_type" data-toggle="select2" placeholder="Select Emission Type...">
                                @foreach($vehicleEmissionType as $cel)
                                <option value="{{$cel->id}}" @if($cel->id == $product->emission_type_id) selected @endif > {{$cel->emission_type_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    @if($product->vendor->pick_drop == 1 && $configData->is_cab_pooling == 1 && $product->category->categoryDetail->type_id == 7)
                    <div class="row">
                        <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                            {!! Form::label('title', __('Available for Pooling'),['class' => 'control-label']) !!}
                            <input type="checkbox" data-plugin="switchery" name="available_for_pooling" id="available_for_pooling" class="form-control" data-color="#43bee1" @if($product->available_for_pooling == 1) checked @endif>
                        </div>
                    </div>
                    <div class="row" id="available_for_pooling_div" style="display:@if($product->available_for_pooling == 1) @else none @endif;">
                        <div class="col-sm-6 mb-1">
                            {!! Form::label('title', __('Total Number Of Seats'),['class' => 'control-label']) !!}
                            <input type="number"  class="form-control" value="{{$product->seats}}" name="seats" id="seats" placeholder="{{__('Number Of Seats')}}">
                        </div>
                        <div class="col-sm-6 mb-1">
                            {!! Form::label('title', __('Number Of Seats Available for Pooling'),['class' => 'control-label']) !!}
                            <input type="number"  class="form-control" value="{{$product->seats_for_booking}}" name="seats_for_booking" id="seats_for_booking" placeholder="{{__('Number Of Seats Available for Booking')}}">
                        </div>
                    </div>
                    @endif
                </div>

                <div class="card-box">
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">{{ __("Product Images") }}</h5>
                     <div class="row mb-2">
                        @if(isset($product->media) && !empty($product->media))
                        @foreach($product->media as $media)
                         @php
                            $mediaPath = Storage::disk('s3')->url('default/default_image.png');
                            if (isset($media->image) && is_array($media->image->path)) {
                                $mediaPath = $media->image->path['proxy_url'] . '300/300' . $media->image->path['image_path'];
                                }
                           @endphp
                        <div class="col-4 product-box editPage mt-1" style="overflow: hidden;">
                           
                            <div class="product-action">
                                @if(isset($media))
                                <a href="{{route('product.deleteImg',[$product->id,  $media->id])}}" class="btn btn-danger btn-xs waves-effect waves-light" onclick="return confirm('Are you sure? You want to delete the image.')"><i class="mdi mdi-close" {{$media->image}}></i></a>
                                @endif
                            </div>
                            <div class="bg-light">
                                <img src="{{$mediaPath}}" style="width:100%;" class="vimg_{{$media->id}}" />
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                    <div class="dropzone dropzone-previews" id="my-awesome-dropzone"></div>
                    <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 540x715</label>
                    <div class="imageDivHidden"></div>
                </div>
                <div class="card-box" >
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">{{ __("Bundle Up Services") }}</h5>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Select Services'),['class' => 'control-label']) !!}
                            
                            <select class="form-control " name="bundleup[]" data-toggle="select2" multiple="multiple" placeholder="Select Services...">
                                @foreach($otherProducts as $otherProduct)
                                 <option value="{{$otherProduct->id}}" @if(in_array($otherProduct->id, $bundle_product_ids)) selected @endif > {{$otherProduct->title??null}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <!-- @if($client_preference_detail->business_type != 'taxi')
                <div class="card-box" style="display:{{(($product->global_product_id!='')?'none':'block')}}">
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">{{ __("Relate with other products") }}</h5>
                    <div class="row">
                        @if($configData->celebrity_check == 1)
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Select Celebrity'),['class' => 'control-label']) !!}
                            <select class="form-control select2-multiple" name="celebrities[]" data-toggle="select2" multiple="multiple" placeholder="Select celebrity...">
                                @foreach($celebrities as $cel)
                                <option value="{{$cel->id}}" @if(in_array($cel->id, $bundle_product_ids)) selected @endif> {{$cel->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Select Addon Set'),['class' => 'control-label']) !!}
                            <select class="form-control select2-multiple" name="addon_sets[]" data-toggle="select2" multiple="multiple" placeholder="Select addon...">
                                @foreach($addons as $set)
                                 <option value="{{$set->id}}" @if(in_array($set->id, $addOn_ids)) selected @endif>{{$set->title??null}}</option>
                                @endforeach
                            </select>
                        </div>
                        @if( !in_array($product->category->categoryDetail->type_id ,[8,12]))
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Up Sell Products'),['class' => 'control-label']) !!}
                            <select class="form-control select2-multiple" name="up_cell[]" data-toggle="select2" multiple="multiple" placeholder="Select gear...">
                                @foreach($otherProducts as $otherProduct)
                                    @if(isset($otherProduct) && !empty($otherProduct->primary))
                                    <option value="{{$otherProduct->id}}" @if(in_array($otherProduct->id, $upSell_ids)) selected @endif>{{$otherProduct->primary->title??null}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Cross Sell Products'),['class' => 'control-label']) !!}
                            <select class="form-control select2-multiple" name="cross_cell[]" data-toggle="select2" multiple="multiple" placeholder="Select gear...">
                                @foreach($otherProducts as $otherProduct)
                                    @if(isset($otherProduct) && !empty($otherProduct->primary))
                                    <option value="{{$otherProduct->id}}" @if(in_array($otherProduct->id, $crossSell_ids)) selected @endif>{{$otherProduct->primary->title??null}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Related Products'),['class' => 'control-label']) !!}
                            <select class="form-control select2-multiple" name="releted_product[]" data-toggle="select2" multiple="multiple" placeholder="Select gear...">
                                @foreach($otherProducts as $otherProduct)
                                    @if(isset($otherProduct) && !empty($otherProduct->primary))
                                    <option value="{{$otherProduct->id}}" @if(in_array($otherProduct->id, $related_ids)) selected @endif>{{$otherProduct->primary->title??null}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if(count($pro_tags))
                        <div class="col-md-6 mb-2">
                            {!! Form::label('title', __('Select Tag Set'),['class' => 'control-label']) !!}
                            <select class="form-control select2-multiple" name="tag_sets[]" data-toggle="select2" multiple="multiple" placeholder="Select tag...">
                                @foreach($pro_tags as $sets)
                                  <option value="{{$sets->id??0}}" @if(isset($set_product_tags) && in_array($sets->id, $set_product_tags)) selected @endif>{{$sets->primary->name??null}}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
                @endif -->

               

                <!-- start product faqs -->
                @if($configData->product_order_form == 1)
                <div class="row">
                    <div class="col-lg-12">
                             <div class="card-box pb-2">
                                <div class="d-flex align-items-center justify-content-between">
                                   <h4 class="header-title text-uppercase m-0">{{ __($nomenclatureProductOrderForm) }}</h4>
                                   <a class="btn btn-info d-block" id="add_product_faq_modal_btn">
                                      <i class="mdi mdi-plus-circle mr-1"></i>{{ __("Add") }}
                                   </a>
                                </div>
                                <div class="table-responsive mt-3 mb-1">
                                   <table class="table table-centered table-nowrap table-striped" id="promo-datatable">
                                      <thead>
                                         <tr>
                                            <th>{{ __("Question") }}</th>
                                            <th>{{ __("Is Required?") }}</th>
                                            <th>{{ __("Action") }}</th>
                                         </tr>
                                      </thead>
                                      <tbody id="post_list">
                                         @forelse($product_faqs as $product_faq)
                                         <tr>
                                            <td>
                                               <a class="edit_product_faq_btn" data-product_faq_id="{{$product_faq->id}}" href="javascript:void(0)">
                                                  {{$product_faq->primary ? $product_faq->primary->name : ''}}
                                               </a>
                                            </td>
                                            <td>{{ ($product_faq->is_required == 1)?"Yes":"No" }}</td>
                                            <td>
                                               <div>
                                                  <div class="inner-div" style="float: left;">
                                                     <a class="action-icon edit_product_faq_btn" data-product_faq_id="{{$product_faq->id}}" href="javascript:void(0)">
                                                        <i class="mdi mdi-square-edit-outline"></i>
                                                     </a>
                                                  </div>
                                                  <div class="inner-div">
                                                     <button type="button" class="btn btn-primary-outline action-icon delete_product_faq_btn" data-product_faq_id="{{$product_faq->id}}">
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
                 </div>
                 @endif
                 <!-- end product faqs -->

            </div>
            
        </div>
    </form>
</div>
<div id="upload-media" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add Product Image") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="AddCardBox">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info waves-effect waves-light selectVaiantImages">{{ __("Select") }}</button>
            </div>
        </div>
    </div>
</div>

<style>

</style>

<!-- product faq modal -->
<div id="add_product_faq_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
          <div class="modal-header border-bottom">
             <h4 class="modal-title" id="standard-modalLabel">{{ __("Add Product Order Form Question") }}</h4>
             <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          </div>
          <div class="modal-body">
             <form id="productFaqForm" method="POST" action="javascript:void(0)">
                @csrf
                <div id="save_social_media">
                   <input type="hidden" name="product_faq_id" value="">
                   <input type="hidden" name="product_id" value="{{$product->id}}">
                   <div class="row">

                      <div class="col-md-6">
                         <div class="form-group position-relative">
                            <label for="">{{ __('Type')}}</label>
                            <div class="input-group mb-2">
                                <select class="form-control" name="file_type" id="file_type_select">
                                    <option value="Text">{{ __('Text')}}</option>
                                    <option value="selector">{{ __('Selector') }}</option>
                                    <option value="checkbox">{{ __('Checkbox') }}</option>
                                    <option value="file">{{ __('File') }}</option>
                                 </select>
                            </div>
                         </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group position-relative">
                           <label for="">{{__('Is Required?') }}</label>
                           <div class="input-group mb-2">
                              <select class="form-control" name="is_required">
                                 <option value="1">{{__('Yes')}}</option>
                                 <option value="0">{{__('No')}}</option>
                              </select>
                           </div>
                        </div>
                     </div>

                      <!--- Start -->

                      <div class="col-md-12 selector-option-al ">
                        <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                            <tr class="trForClone">

                                @foreach($languages as $langs)
                                    <th>{{ __("Question") }} ({{$langs->langName}})</th>
                                @endforeach
                                <th></th>
                            </tr>
                            <tbody id="table_body">
                                    <tr>
                                @foreach($languages as $key => $vendor_langs)
                                    <td>
                                        <input class="form-control" name="language_id[{{$key}}]" type="hidden" value="{{$vendor_langs->langId}}">
                                        <input class="form-control" name="name[{{$key}}]" type="text" id="product_faq_name_{{$vendor_langs->langId}}">
                                    </td>
                                @endforeach
                                <td class="lasttd"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="selector_div" class="col-md-12 d-none">
                        <div class="card">
                        <div class="card-box mb-0 ">
                            <div class="d-flex align-items-center justify-content-between">
                               <h4 class="header-title text-uppercase">{{__('Options')}}</h4>
                            </div>
                            <div id="option_div">

                                    <div class="selector-option-al ">
                                        <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="vendor-selector-datatable">
                                            <tr class="trForClone">

                                                @foreach($languages as $langs)
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

                      <!-- End -->


                   </div>
                </div>
             </form>
          </div>
          <div class="modal-footer">
             <button type="button" class="btn btn-primary submitSaveProductFaq">{{ __("Save") }}</button>
          </div>
       </div>
    </div>
 </div>
<!-- end product faq -->

<!-- Role based Price (Start) -->
<div class="modal fade" id="rolePriceModal" tabindex="-1" role="dialog" aria-labelledby="rolePriceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="rolePriceModalLabel">Insert Price Based On Roles</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('product.updateRolePrice')}}" method="POST">
                @csrf
                <div class="modal-body">
                    @if (isset($roles))
                        @foreach ($roles as $key => $_role)
                            <div class="col-4 mb-2">
                                {!! Form::label('title', $_role['role'].' '. __('Price'), ['class' => 'control-label']) !!}
                                <input type="number" class="form-control" min="0" id="{{lcfirst($_role['id'])}}_price" onkeyup="isNumberKey(event)" placeholder="0" name="role_price[{{$_role['id']}}]" value="0.00">
                                <input type="hidden" class="form-control" min="0" name="role_id[{{lcfirst($_role['role'])}}]" value="{{$_role['id']}}">
                            </div>
                        @endforeach
                    @endif
                    <input type="hidden" id="role_variant_id" name="variant_id" value="">
                    <input type="hidden" id="role_product_id" name="product_id" value="">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>
<!-- Role based Price (END) -->

<script type="text/template" id="vendorSelectorTemp">
    <tr class ="option_section" id ="option_section_<%= id %>" data-section_number="<%= id %>">
    <input type="hidden" name="option_id[<%= id-1 %>][]"  id="option_id<%= id %>" data-id ="<%= id %>" value ="<%= data?data.id:'' %>">
    @foreach($languages as $key => $langs)
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
@include('backend.catalog.modals')
@endsection

@section('script-bottom')

<link href="{{asset('assets/css/dropzone.css')}}" rel="stylesheet" />
<script src="{{asset('assets/js/dropzone.js')}}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script src="{{ asset('assets/ck_editor/ckeditor.js')}}"></script>
<script src="{{ asset('assets/ck_editor/samples/js/sample.js')}}"></script>
<script src="{{asset('assets/libs/select2/select2.min.js')}}"></script>
{{-- <script src="{{asset('js/cab_booking.js')}}"></script> --}}
<script>
    CKEDITOR.replace('body_html');
    CKEDITOR.config.height = 150;
</script>

<script type="text/javascript">

    //mohit sir branch code added by sohail
    var isProcessorEnable = $('#is-processor-enable');
    $(document).on("click", ".is-processor-enable", function() {
        $('.processor-enable-row').hide();
        if($(this).val() == 1){
            $('.vendor-enable-row').hide();
            $('.processor-enable-row').show();
        }else{
            $('.vendor-enable-row').show();
        }
    });
    function checkAddressString(obj,name)
    {
        if($(obj).val() == "")
        {
            document.getElementById(name + '_latitude').value = '';
            document.getElementById(name + '_longitude').value = '';
        }
    }
    //till here

    $("#pickup_location").keydown(function(){
        var input = document.getElementById('pickup_location');
        if(input){
            var autocomplete = new google.maps.places.Autocomplete(input);
            if(is_map_search_perticular_country){
                autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
            }
            if(is_map_search_perticular_country){
                autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
            }
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();
                $('#pickup_location_latitude_home').val(place.geometry.location.lat());
                $('#pickup_location_longitude_home').val(place.geometry.location.lng());
            });
        }
    });

    $(document).on("change", "#file_type_select", function() {
        var file_type = $(this).val();
        if(file_type == 'selector' || file_type == 'checkbox'){
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
        console.log($('.add_more_button').length);
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

    $('#requiredShipping').change(function() {
        var val = $(this).prop('checked');
        if (val == true) {
            $('.shippingDiv').show();
        } else {
            $('.shippingDiv').hide();
        }
    });
    $('#is_physical').change(function() {
        var val = $(this).prop('checked');
        if (val == true) {
            $('.physicalDiv').show();
        } else {
            $('.physicalDiv').hide();
        }
    });



    var regexp = /^[a-zA-Z0-9-_]+$/;

    function removeVariant(product_id, product_variant_id, is_product_delete) {
        var redirect_url = "{{url('client/vendor/catalogs/'.$product->vendor_id)}}";
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "{{route('product.deleteVariant')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                product_id: product_id,
                product_variant_id: product_variant_id,
                is_product_delete: is_product_delete
            },
            success: function(response) {
                $('#tr_' + product_variant_id).remove();
                if (is_product_delete) {
                    window.location.href = redirect_url;
                }
            }
        });
    }
    $(document).on('click', '.deleteExistRow', function() {
        var that = $(this);
        var product_id = "{{$product->id}}";
        var product_variant_id = $(this).data('varient_id');
        var rowCount = $('#product_tbody_' + product_id + ' tr').length;
        if (rowCount == 1) {
            var is_product_delete = 1;
            if (confirm("Are you sure? You want to delete this variant.")) {
                removeVariant(product_id, product_variant_id, is_product_delete);
            }
        } else {
            var is_product_delete = 0;
            if (confirm("Are you sure? You want to delete this variant.")) {
                removeVariant(product_id, product_variant_id, is_product_delete);
            }
        }
    });
    $(document).on('click', '.deleteExistRowRental', function() {
        var that = $(this);
        var product_id = "{{$product->id}}";
        var product_variant_id = $(this).data('varient_id');
        var rowCount = $('#product_tbody_' + product_id + ' tr').length;
        if (rowCount == 1) {
            var is_product_delete = 1;
            Swal.fire({
                title: 'Warning!',
                text: 'Are you sure you?',
                input: 'text',
                inputPlaceholder: 'Delete',
              }).then(({value}) => {
                if (value === "Delete") {
                    removeVariant(product_id, product_variant_id, is_product_delete);
                    $(".addExistRow").css('display','none');
                    $('.addExistRow').last().show();
                    Swal.fire('Deleted!', 'Row has been deleted!', 'success')
                }
              });
        } else {
            var is_product_delete = 0;
            Swal.fire({
                title: 'Warning!',
                text: 'Are you sure you?',
                input: 'text',
                inputPlaceholder: 'Delete',
              }).then(({value}) => {
                if (value === "Delete") {
                    removeVariant(product_id, product_variant_id, is_product_delete);
                    $(".addExistRow").css('display','none');
                    $('.addExistRow').last().show();
                    Swal.fire('Deleted!', 'Row has been deleted!', 'success')
                }
              });
        }
    });

    function alplaNumeric(evt) {
        var charCode = String.fromCharCode(event.which || event.keyCode);
        if (!regexp.test(charCode)) {
            return false;
        }
        var n1 = document.getElementById('sku');
        var n2 = document.getElementById('url_slug');
        n2.value = n1.value + charCode;
        return true;
    }

    function alplaNumericSlug(evt) {
        var charCode = String.fromCharCode(event.which || event.keyCode);
        if (!regexp.test(charCode)) {
            return false;
        }
        // var n2 = document.getElementById('url_slug');
        // n2.value = n2.value + charCode;
        return true;
    }
    $('.saveProduct').click(function() {
        $('.product_form').submit();
    });

    var uploadedDocumentMap = {};
    Dropzone.autoDiscover = false;



    $(document).ready(function() {

        var val = $('#has_inventory').prop('checked');

        if (val == true) {
            $('.check_inventory').show();
        } else {
            $('.check_inventory').hide();
        }
        // $('#body_html').summernote({
        //     placeholder: 'Description',
        //     tabsize: 2,
        //     height: 120,
        //     toolbar: [
        //         ['style', ['style']],
        //         // ['color', ['color']],
        //         ['table', ['table']],
        //         ['para', ['ul', 'ol', 'paragraph']],
        //         ['font', ['bold', 'underline', 'clear']],
        //         ['view', ['fullscreen', 'codeview', 'help']]
        //     ]
        // });
        $('#has_inventory').change(function() {
            var val = $(this).prop('checked');

            if (val == true) {
                $('.check_inventory').show();
            } else {
                $('.check_inventory').hide();
            }
        });

        $('#available_for_pooling').change(function() {
            var val = $(this).prop('checked');
            if (val == true) {
                $('#available_for_pooling_div').show();
            } else {
                $('#available_for_pooling_div').hide();
                $("#seats_for_booking, #seats").val(0);
            }
        });

        $('#is_toll_tax').change(function() {
            var val = $(this).prop('checked');
            if (val == true) {
                $('#is_toll_tax_div1, #is_toll_tax_div2').show();
            } else {
                $('#is_toll_tax_div1, #is_toll_tax_div2').hide();
            }
        });

        $("div#my-awesome-dropzone").dropzone({
            acceptedFiles: ".jpeg,.jpg,.png,.svg",
            addRemoveLinks: true,
            url: "{{route('product.images')}}",
            params: {
                prodId: "{{$product->id}}"
            },
            parameter: "{{route('product.images')}}",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(file, res) {

                $('.imageDivHidden').append('<input type="hidden" name="fileIds[]" value="' + res.imageId + '">')
                uploadedDocumentMap[file.name] = res.imageId;

            },
            removedfile: function(file) {
                file.previewElement.remove();
                console.log(file);
                var name = ''
                if (typeof file.file_name !== 'undefined') {
                    name = file.file_name
                } else {
                    name = uploadedDocumentMap[file.name]
                }
                $('form').find('input[name="fileIds[]"][value="' + name + '"]').remove();
            },
        });

        $("input[name='delivery_charges_tax']").change(function() {
            if($(this).prop('checked')){
                $("#delivery_charges_tax_id").css("display", "block");
            } else {
                $("#delivery_charges_tax_id").css("display", "none");
            }
        });
        $("input[name='service_charges_tax']").change(function() {
            if($(this).prop('checked')){
                $("#service_charges_tax_id").css("display", "block");
            } else {
                $("#service_charges_tax_id").css("display", "none");
            }
        });
        $("input[name='container_charges_tax']").change(function() {
            if($(this).prop('checked')){
                $("#container_charges_tax_id").css("display", "block");
            } else {
                $("#container_charges_tax_id").css("display", "none");
            }
        });
        $("input[name='fixed_fee_tax']").change(function() {
            if($(this).prop('checked')){
                $("#fixed_fee_tax_id").css("display", "block");
            } else {
                $("#fixed_fee_tax_id").css("display", "none");
            }
        });
    });

    $('#category_list').change(function() {

        var cid = $(this).val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        var uri = "{{url('client/variant/cate')}}" + '/' + cid;

        $.ajax({
            type: "get",
            url: uri,
            data: '',
            dataType: 'json',
            success: function(data) {
                $('#variantAjaxDiv').html(data.resp);
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            },
            error: function(data) {
                console.log('data2');
            }
        });
    });

    $('.makeVariantRow').click(function() {
        var psku = $('#sku').val();
        var pid = "{{$product->id}}";
        if (psku.trim() == '') {
            Swal.fire({
               title: "Warning!",
               text: "Enter Product sku.",
               icon: "warning",
               button: "OK",
            });
            // alert('Enter Product sku.');
            return false;
        }
        var vids = [];
        var optids = [];
        var exist = [];
        $("#variantAjaxDiv .intpCheck").each(function() {
            var $this = $(this);
            if ($this.is(":checked")) {
                optids.push($this.attr('opt'));
                vids.push($this.attr('varid'));
            }
        });
        $("#exist_variant_div .exist_sets").each(function() {
            exist.push($(this).val());
        });
        $.ajax({
            type: "post",
            url: "{{route('product.makeRows')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                'variantIds': vids,
                'optionIds': optids,
                'sku': psku,
                'existing': exist,
                'pid': pid
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success == 'false') {
                    Swal.fire({
                       title: "Error!",
                       text: resp.msg,
                       icon: "error",
                       button: "OK",
                    });
                    // alert(resp.msg);
                    $('#variantRowDiv').html('');
                } else {
                    $('#variantRowDiv').html(resp.html);
                }

            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            },
            error: function(resp) {
                console.log('data2');
            }
        });
    });

    $(document).on('click', '.deleteCurRow', function() {
        $(this).closest('tr').remove();
    });

    $('#processor-date,#product-pickup-date').flatpickr({
        enableTime: false,
        startDate: new Date(),
        minDate: new Date(),
        dateFormat: "Y-m-d"
    });

    $(document).on('change', '.vimageNew', function() {

        var file = this.files[0];
        var fileType = file['type'];
        var validImageTypes = ['image/gif', 'image/jpeg', 'image/png'];
        if (!validImageTypes.includes(fileType)) {
            Swal.fire({
                title: "Warning!",
                text: "Select only images",
                icon: "warning",
                button: "OK",
            });
            // alert('select only images');
        } else {

            var form = document.getElementById('modalImageForm');
            var formData = new FormData(form);

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
                url: "{{route('product.images')}}",
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    $('#upload-media .lastDiv').before(data.htmlData);

                },
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });

        }
    });

    function readURL(input, forv) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('.vimg_' + forv).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).on('change', '#language_id', function() {
        var forv = $(this).val();
        var pid = "{{$product->id}}";
        $.ajax({
            type: "post",
            url: "{{route('product.translation')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                'prod_id': pid,
                'lang_id': forv
            },
            dataType: 'json',
            success: function(resp) {
                if (resp) {
                    $('#product_name').val(resp.data.title);
                    // $('#body_html').val(resp.data.body_html);
                    CKEDITOR.instances.body_html.setData(resp.data.body_html);
                    $('#meta_title').val(resp.data.meta_title);
                    $('#meta_keyword').val(resp.data.meta_keyword);
                    $('#meta_description').val(resp.data.meta_description);
                }
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
    });

    $(document).on('click', '.uploadImages', function() {

        var vari_id = $(this).attr('for');
        var pid = "{{$product->id}}";
        var vendor = "{{$product->vendor_id}}";
        $.ajax({
            type: "post",
            url: "{{route('productImage.get')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                'prod_id': pid,
                'variant_id': vari_id,
                'vendor_id': vendor
            },
            dataType: 'json',
            success: function(data) {
                $('#upload-media #AddCardBox').html(data.htmlData);
                $('#upload-media').modal({
                    keyboard: false
                });
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            },
        });
    });

    $(document).on('click', '.selectVaiantImages', function() {
        var variantId = $('#upload-media #modalVariantId').val();
        var productId = "{{$product->id}}";
        var imageId = [];
        $("#upload-media .imgChecks").each(function() {
            var $this = $(this);
            if ($this.is(":checked")) {
                imageId.push($this.attr('imgId'));
            }
        });
        $.ajax({
            type: "post",
            url: "{{route('product.variant.update')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                'prod_id': productId,
                'variant_id': variantId,
                'image_id': imageId
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success != 'false') {
                    $('#upload-media').modal('hide');
                } else {
                    Swal.fire({
                       title: "Error!",
                       text: resp.msg,
                       icon: "error",
                       button: "OK",
                    });
                    // alert(resp.msg);
                    $('#upload-media').modal('hide');
                }
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
    });
</script>
<script>
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
</script>

<!-- start product faq -->
<script>
    $(document).on("change",".attr_radio", function() {

    var parentClass = $(this).parent().prop('className');
    var attr_radio_class = $(this).data('class');
    $("."+parentClass+" .attr_radio").prop('checked', false);
    $(this).prop('checked', true);
    // $('.'+attr_radio_class).not(this).prop('checked', false);
});
 $('#add_product_faq_modal_btn').click(function(e) {
         document.getElementById("productFaqForm").reset();
         $('#add_product_faq_modal input[name=product_faq_id]').val("");
         $('#add_product_faq_modal').modal('show');
         $('#add_product_faq_modal #standard-modalLabel').html(__('Add Product Order Form Question'));
      });

      $(document).on("click", ".delete_product_faq_btn", function() {
         var product_faq_id = $(this).data('product_faq_id');
         if (confirm('Are you sure?')) {
            $.ajax({
               type: "POST",
               dataType: 'json',
               url: "{{ route('product.faq.delete') }}",
               data: {
                  _token: "{{ csrf_token() }}",
                  product_faq_id: product_faq_id
               },
               success: function(response) {
               if (response.status == 'Success') {

                  $.NotificationApp.send("{{__('Success')}}", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                     location.reload()
                  }, 2000);
               } else {
                  $.NotificationApp.send({{__('Errors')}}, response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
               $('#add_product_faq_modal .social_media_url_err').html('Error in delete.');
            }
            });
         }
      });

      $(document).on('click', '.submitSaveProductFaq', function(e) {
         var product_faq_id = $("#add_product_faq_modal input[name=product_faq_id]").val();
         if (product_faq_id) {
            var post_url = "{{ route('product.faq.update') }}";
         } else {
            var post_url = "{{ route('product.faq.create') }}";
         }
         var form_data = new FormData(document.getElementById("productFaqForm"));
         $.ajax({
            url: post_url,
            method: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
               if (response.status == 'Success') {
                  $('#add_or_edit_social_media_modal').modal('hide');

                  $.NotificationApp.send("{{__('Success')}}", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                     location.reload()
                  }, 2000);
               } else {
                  $.NotificationApp.send({{__('Errors')}}, response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
               $('#add_product_faq_modal .social_media_url_err').html('The default language name field is required.');
            }
         });
      });

      $(document).on("click", ".edit_product_faq_btn", function() {
        let product_faq_id = $(this).data('product_faq_id');

        editProductOrderForm(product_faq_id);
    });

    function editProductOrderForm(product_faq_id){
        let language_id = $('#option_client_language').val();
        $('#add_product_faq_modal input[name=product_faq_id]').val(product_faq_id);
         $.ajax({
            method: 'GET',
            data: {
                product_faq_id: product_faq_id,
               language_id:language_id
            },
            url: "{{ route('product.faq.edit') }}",
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
                  $(document).find("#add_product_faq_modal select[name=file_type]").val(response.data.file_type).change();

                  $("#add_product_faq_modal input[name=vendor_registration_document_id]").val(response.data.id);
                  $(document).find("#add_product_faq_modal select[name=is_required]").val(response.data.is_required).change();
                  $('#add_product_faq_modal #standard-modalLabel').html('Update Vendor Registration Document');
                  $('#add_product_faq_modal').modal('show');
                  $.each(response.data.translations, function( index, value ) {
                    $('#add_product_faq_modal #product_faq_name_'+value.language_id).val(value.name);
                  });
               }
            },
            error: function() {}
        });
    }

      $(document).on("click", ".edit_product_faq_btnOld", function() {
         let product_faq_id = $(this).data('product_faq_id');
         $('#add_product_faq_modal input[name=product_faq_id]').val(product_faq_id);
         $.ajax({
            method: 'GET',
            data: {
                product_faq_id: product_faq_id
            },
            url: "{{ route('product.faq.edit') }}",
            success: function(response) {
               if (response.status = 'Success') {
                  $("#add_product_faq_modal input[name=product_faq_id]").val(response.data.id);
                  $(document).find("#add_product_faq_modal select[name=is_required]").val(response.data.is_required).change();
                  $('#add_product_faq_modal #standard-modalLabel').html('Update Product Order Form Question');
                  $('#add_product_faq_modal').modal('show');
                  $.each(response.data.translations, function( index, value ) {
                    $('#add_product_faq_modal #product_faq_name_'+value.language_id).val(value.name);
                  });
               }
            },
            error: function() {

            }
         });
      });

      $('#mode_of_service').change(function(){
           var selected_value =$(this).val();
          // console.log(selected_value);
            if(selected_value == 'schedule'){
                $('.dispatch_Agent').removeClass('d-none')
                $('.dispatch_Agent').addClass('d-flex ')
            } else {
                $('.dispatch_Agent').removeClass('d-flex')
               $('.dispatch_Agent').addClass('d-none ')
            }
        })

    </script>
    <script type="text/javascript">
        $(document).ready(function(){
            var maxField = 10; //Input fields increment limitation
            var addButton = $('.add_button'); //Add button selector
            var wrapper = $('.field_wrapper'); //Input field wrapper
            var fieldHTML = '<div class="row corporate-row"><div class="col-md-5"><div class="form-group"><input type="number" class="form-control" min="0" id="corporate_user_price" onkeyup="isNumberKey(event)" placeholder="Corporate User Price"  name="corporate_user_price[]" value=""></div></div><div class="col-md-5"><div class="form-group"><input type="number" class="form-control" min="0" onkeyup="isNumberKey(event)" placeholder="Quantity" name="minimum_order_count_corporate_user[]" value=""></div></div><div class="col-md-2"><a href="javascript:void(0);" class="remove_button" title="Remove field"><i class="mdi mdi-minus-circle mr-1"></i></a></div></div>'; //New input field html
            var x = $("div.corporate-row").length; //Initial field counter is 1

            //Once add button is clicked
            $(addButton).click(function(){
                //Check maximum number of input fields
                if(x < maxField){
                    x++; //Increment field counter
                    $(wrapper).append(fieldHTML); //Add field html
                }
            });

            //Once remove button is clicked
            $(wrapper).on('click', '.remove_button', function(e){
                e.preventDefault();
                $(this).parent().parent('div').remove(); //Remove field html
                x--; //Decrement field counter
            });
        });
    </script>
<!-- end product faq -->

{{-- Insert Value to Role Price Modal (Start) --}}
    <script>
        $( document ).delegate( ".rolePriceModal", "click", function() {
            var variant_id = $(this).attr('data-varient-id');
            var product_id =  $(this).attr('data-product-id');

            $('#role_variant_id').val(variant_id);
            $('#role_product_id').val(product_id);

            // Calling ajax to show the price based on roles (if its present in product_variant_by_roles table)
            $.ajax({
                    url: "{{ route('product.getRolePrice') }}",
                    type: "POST",
                    data: {
                        variant_id: variant_id,
                        product_id: product_id,
                        _token: '{{csrf_token()}}'
                    },
                    success: function(result) {
                        console.log(result);
                        $('#rolePriceModal').modal('show');
                        $(':input[type="number"]').val('');

                        let data = result.result;
                        data.forEach((val) => {

                            if(val != undefined){
                                $('#'+val.role_id+'_price').val(val.amount);
                            }

                        });
                    }
                });
        });
        $('.select2-multiple').select2();

        $('select[name="included_rental_protection[]"], select[name="rental_protection[]"]').change(function(){
            if($(this).val().length){
                array1 = $('select[name="included_rental_protection[]"]').val()
                array2 = $('select[name="rental_protection[]"]').val()
                var filteredArray = array1.filter(function(n) {
                    return array2.indexOf(n) !== -1;
                });
                
                if(filteredArray.length){
                    
                    $(this).find("option[value='"+filteredArray[0]+"']").prop("selected", false);
                    $(this).trigger('change.select2');
                }
            }
        })
    </script>
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const table = document.querySelector('#measurementsTable tbody');

        function updateVariantOptions() {
            const selectedVariants = Array.from(document.querySelectorAll('.variant-select'))
                .map(select => select.value)
                .filter(value => value);

            document.querySelectorAll('.variant-select').forEach(select => {
                const currentValue = select.value;
                select.querySelectorAll('option').forEach(option => {
                    if (option.value && selectedVariants.includes(option.value) && option.value !== currentValue) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
            });

            const addButton = document.querySelector('.add-row');
            if (addButton) {
                addButton.disabled = selectedVariants.length >= {{ count($product->variant) }};
            }
        }

        table.addEventListener('change', function(e) {
            if (e.target && e.target.matches('.variant-select')) {
                const variantIdInput = e.target.closest('tr').querySelector('.variant-id');
                variantIdInput.value = e.target.value;
                updateVariantOptions();
            }
        });

        table.addEventListener('click', function(e) {
            if (e.target && e.target.matches('button.add-row')) {
                let currentRow = e.target.closest('.measurement-row');
                let newRow = currentRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');

                let newSelect = newRow.querySelector('.variant-select');
                newSelect.value = '';

                let newVariantIdInput = newRow.querySelector('.variant-id');
                newVariantIdInput.value = '';

                let removeButton = newRow.querySelector('.remove-row');
                if (removeButton) {
                    removeButton.style.display = 'inline-block';
                }

                currentRow.after(newRow);

                updateVariantOptions();
            }

            if (e.target && e.target.matches('button.remove-row')) {
                let currentRow = e.target.closest('.measurement-row');

                if (table.querySelectorAll('.measurement-row').length > 1) {
                    currentRow.remove();
                    updateVariantOptions();
                }
            }
        });

        updateVariantOptions();
    });
    </script>

{{-- Insert Value to Role Price Modal (End) --}}
@include('backend.catalog.pagescript')
<script src="{{ asset('assets/js/backend/product/edit_product.js')}}"></script>
@endsection
