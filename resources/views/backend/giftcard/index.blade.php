@extends('layouts.vertical', ['demo' => 'Gift Card', 'title' => 'Gift Card'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">

@endsection
@section('content')
<div class="container-fluid alCustomersPage">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Gift Card") }}</h4>
            </div>
        </div>


    </div>

    <div class="row main-customer-page al">
        <div class="col-12">
            <div class="pb-0">
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
                        </div>
                    </div>
                </div>
                <div class="al_new_export_table royo_customber_btn table_customber_add">
                    <div class="position-absolute mb-2">
                            <button type="button" class="btn btn-info waves-effect waves-light" data-toggle="modal" data-target="#agiftCart_model" data-backdrop="static" data-keyboard="false">{{__("Add Gift Card")}}</button>
                        </button>
                    </div>


                    <div class="row mt-1 sml_royo-responsive">
                        <div class="col-sm-12 col-lg-12 tab-product  pt-0 mt-5">
                          
                            <div class="tab-content nav-material pt-0" id="top-tabContent">
                                <div class="tab-pane fade past-order show active" id="active_user" >
                                    <div class="row">
                                        <div class="col-12">

                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                      

                                                        <table class="table table-centered table-nowrap table-striped" id="giftCard_datatable" width="100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>{{ __('Image')}}</th>
                                                                    <th>{{ __('Name')}}</th>
                                                                    <th>{{ __('Price') }}</th>
                                                                    <th>{{ __('Expiry Date') }}</th>
                                                                    <th>{{ __('Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="post_list">

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row address" id="def" style="display: none;">
                                            <input type="text" id="def-address" name="test" class="autocomplete form-control def_address">
                                        </div>
                                    </div>
                                </div>
                               
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>
<div id="agiftCart_model" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Add Gift Card') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="giftCardForm" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" id="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12" id="imageInput">
                                    <label>{{ __("Upload Gift Card image") }}</label>
                                    @if(isset($promo->id))
                                        <input type="file" accept="image/*" data-plugins="dropify" name="image" class="dropify" />
                                    @else
                                        <input data-default-file="" type="file" data-plugins="dropify" name="image" accept="image/*" class="dropify"/>
                                    @endif
                                    <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 100x100</label>
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group" id="titleInput">
                                        {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
                                        {!! Form::text('title','', ['class' => 'form-control', 'placeholder'=>'Enter Title']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group" id="short_descInput">
                                        {!! Form::label('short_desc', __('Short Description'),['class' => 'control-label']) !!}
                                        {!! Form::textarea('short_desc','', ['class' => 'form-control', 'placeholder'=>'Enter Short Description', 'rows' => 3]) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                {{-- <div class="col-md-6">
                                    <div class="form-group" id="nameInput">
                                        {!! Form::label('title', __('Gift Card Code'),['class' => 'control-label']) !!}
                                        {!! Form::text('name', '', ['class' => 'form-control', 'placeholder'=>'Enter Gift Card Code']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div> --}}
                                
                    
                                <div class="col-md-6">
                                    <div class="form-group" id="amountInput">
                                        {!! Form::label('title', __('Amount'),['class' => 'control-label']) !!}
                                        {!! Form::number('amount', '', ['class' => 'form-control amountInputField', 'id' => 'amountInputField', 'placeholder'=> __('Enter total amount'), 'max' => "10000", 'min' => "1"]) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="">
                                        <div class="form-group" id="expiry_dateInput">
                                            @php
                                            $minDate = Date('Y-m-d');
                                            @endphp
                                            {!! Form::label('expiry_date', __('Expiry Date'),['class' => 'control-label']) !!}
                                            {!! Form::text('expiry_date','', ['class' => 'form-control downside datetime-datepicker', 'id' => 'start-datepicker', 'min' => $minDate]) !!}
                                            <span class="invalid-feedback" role="alert">
                                               
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button  class="btn btn-info waves-effect waves-light submitGiftCardForm">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="EditagiftCart_model" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Edit Gift Card') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="editgiftCardForm" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" id="editgiftCard">
                   
                    
                </div>
                <div class="modal-footer">
                    <button  class="btn btn-info waves-effect waves-light submitEditGiftCardForm">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="{{ asset('js/giftCard/giftcard.js') }}"></script>


@endsection
