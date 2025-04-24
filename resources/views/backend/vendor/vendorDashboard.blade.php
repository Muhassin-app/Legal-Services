@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('vendors', true)])
{{-- @php
pr($products->toArray());
@endphp --}}
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
                                                        id="total_earnings_by_vendors">{{ @$lifetimeEarning }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Total lifetime Earnings Amount value') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                            <div class="text-center">
                                                <h3>
                                                    <i class="mdi mdi-package-variant text-primary mdi-24px"></i>
                                                    <span data-plugin="counterup"
                                                        id="total_order_count">{{ @$total_sold_products }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Total Products Sold') }}</p>
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
    @endsection
    @section('script')
        @include('backend.vendor.pagescript')
    @endsection
