@extends('layouts.store', ['title' => 'My Bids Requests'])
@section('css')
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
</style>
@endsection
@section('content')
@php
$user = Auth::user();
$timezone = $user->timezone;
$user_wallet_balance = $user->balanceFloat ? ($user->balanceFloat * ($clientCurrency->doller_compare ?? 1) ) : 0;
@endphp

<style type="text/css">
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
    .box-info table tr:first-child td {
        padding-top: .85rem;
    }
    #wallet_transfer_error_msg{
        display: none;
    }
</style>
<section class="section-b-space">
    <div class="container">

        <div class="row">
            <div class="col-sm-12">
                <div class="text-sm-left" >
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <span>{!! \Session::get('success') !!}</span>
                        </div>
                        @php
                            \Session::forget('success');
                        @endphp
                    @endif
                    @if (\Session::has('error'))
                        <div class="alert alert-danger">
                            <span>{!! \Session::get('error') !!}</span>
                        </div>
                        @php
                            \Session::forget('error');
                        @endphp
                    @endif
                    <div class="message d-none">
                        <div class="alert p-0"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row my-md-3">
            <div class="col-lg-3 profile-sidebar">
                <div class="account-sidebar"><a class="popup-btn">{{__('My Account')}}</a></div>
                <div class="dashboard-left mb-3">
                    <div class="collection-mobile-back">
                        <span class="filter-back d-lg-none d-inline-block">
                            <i class="fa fa-angle-left" aria-hidden="true"></i>{{__('Back')}}
                        </span>
                        </div>
                    @include('layouts.store/profile-sidebar')
                </div>
            </div>
            <div class="col-lg-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2 class="">{{__('Make Bid Request')}}</h3>
                        </div>
                        <div class="box-account box-info">
                            <div class="card-box">
                                <div class="row ">

                                    <div class="col-md-12 ">
                                        <button type="button" class="btn btn-solid float-right" id="my_bid_btn" data-toggle="modal" data-target="#myBidModal">{{ __('Place bid request') }}</button>

                                    </div>
                                </div>
                            </div>
                            </div>

                            <div class="card-box" >
                                <div class="table-responsive table-responsive-xs">
                                     <table class="table table-centered table-nowrap table-striped" id="client_customer_table" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('ID') }}</th>
                                                        <th>{{ __('Prescription') }}</th>
                                                        <th>{{ __('Description') }}</th>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($prescriptions as $prescription)
                                                    <tr>
                                                        <td>{{$loop->iteration}}</td>
                                                        <td><a target ="_blank" href="{{$prescription->prescription}}"><i class="fa fa-file-pdf-o" style="font-size:24px;color:red"></i></a></td>
                                                        <td><span class="text-wrap">{{$prescription->description??null}}</span></td>
                                                        <td>{{$prescription->created_at}}</td>
                                                        <td>
                                                            <a href="{{route('bid.details',['id'=>$prescription->id])}}" class="viewBids" data-id="{{$prescription->id}}">View Bids ({{$prescription->bid_counts_count??0}})</a>
                                                        </td>

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
    </div>
</section>



    @if(@getAdditionalPreference(['is_bid_enable'])['is_bid_enable'] == 1)

         @include('frontend.bidding_module.modal')

        <div class="modal fade" id="myBidModal" tabindex="-1" aria-labelledby="myBidModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title text-17 mb-0 mt-0" id="topup_walletLabel">{{__('Upload file')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
                     <div class="modal-body">
                         <form  method="POST" action="{{route('bid.update_pdf')}}" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                <div class="col-md-12 mb-2">
                                    <textarea required  name="description" class="form-control " id="description" placeholder="Description"></textarea>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <input required type="file" accept=".pdf" name="prescription" class="form-control">
                                </div>
                                <div class="col-md-12">
                                    <input type="submit" id="button" class="btn btn-solid btn-sm float-right" value="Submit">
                                </div>
                            </div>
                        </form>
                        </div>
            </div>
        </div>
        </div>

    @endif

@endsection

