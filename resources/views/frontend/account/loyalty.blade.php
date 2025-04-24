@extends('layouts.store', ['title' => 'Loyalty'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
</style>
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
@endsection
@section('content')

<section class="section-b-space">
    <div class="container">
        <div class="row my-md-3 mt-5 pt-4">
            <div class="col-lg-3">
                <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                @include('layouts.store/profile-sidebar')
            </div>
            <div class="col-lg-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2>{{ __('My Loyalty') }}</h2>
                        </div>
                        <div class="card-box al_inner_card">
                            <div class="row">
                            <div class="offset-md-3 col-md-6">
                                    @if($current_loyalty)
                                        <div class="card-box ">
                                            <div class="row align-items-center">
                                                <div class="col-4">
                                                    <div class="medal-img">
                                                        <img src="{{ $current_loyalty->image['proxy_url'] .'120/120'. $current_loyalty->image['image_path'] }}" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-8">
                                                    <h3 class="mt-0"><b>{{ __('You are on') }}</b></h3>
                                                    <div class="loalty-title">
                                                        {{ $current_loyalty->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @else
                                        <div class="text-center">{{__('Not Available')}}</div>
                                    @endif

                                        <div class="row">
                                            <div class="col-md-6 text-center">
                                                <div class="card-box earn-points p-2">
                                                    <div class="points-title">
                                                        {{ $loyalty_points_earned }}
                                                    </div>
                                                    <div class="ponits-heading">
                                                        {{ __('Total Points Earned') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-center">
                                                <div class="card-box spend-points p-2">
                                                    <div class="points-title">
                                                        {{ $loyalty_points_used }}
                                                    </div>
                                                    <div class="ponits-heading">
                                                        {{ __('Total Points Spent') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                          

                           <div class="row">
                                <div class="offset-lg-1 col-lg-10">
                                    @if($upcoming_loyalty->isNotEmpty())
                                    <div class="row">
                                        <div class="col-12 ">
                                            <h4 style="color: var(--theme-color)">{{__('Upcoming')}}</h4>
                                        </div>
                                        @foreach($upcoming_loyalty as $loyalty)
                                        <div class="col-md-4 mt-3 text-center">
                                            <div class="card-box al_gold_box">
                                                <div class="point-img-box">
                                                    <img src="{{ $loyalty->image['proxy_url'] .'200/200'. $loyalty->image['image_path'] }}" alt="">
                                                </div>
                                                <h4 class="mb-0 mt-3"><b><span class="alLoyaltyPrice"> {{$loyalty->points_to_reach}}</span> points to <span class="alLoyaltyName"> {{$loyalty->name}} </span></b></h4>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                               </div>
                           </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
@section('script')

@endsection
