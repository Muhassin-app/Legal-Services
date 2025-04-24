@extends('layouts.god-vertical', ['title' => 'Dashboard'])

@section('css')
    <!-- Plugins css -->
    <link href="{{asset('assets/libs/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/libs/selectize/selectize.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ __('Dashboard') }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-3">
                            <div class="avatar-lg bg-blue rounded">
                               <i class="fe-users avatar-title font-22 text-white"></i>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="text-right">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{$onboardclients}}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Onboard Clients</p>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-3">
                            <div class="avatar-lg bg-success rounded">
                                <i class="fe-user-check avatar-title font-22 text-white"></i>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="text-right">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{$activeSubs}}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Active Subscriptions</p>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-3">
                            
                            <div class="avatar-lg bg-danger rounded ">
                                <i class="fe-user-x font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="text-right">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{$expSofSubs}}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Software License Expired</p>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-3">
                            
                            <div class="avatar-lg bg-danger rounded ">
                                <i class="fe-user-x font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="text-right">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{$expHosSubs}}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Hosting Plan Expired</p>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-3">
                            
                            <div class="avatar-lg bg-warning rounded ">
                                <i class="fe-user-x font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="text-right">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{$clientwithnosubs}}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Clients with zero Subscriptions</p>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>

        </div>
        

        

        
        
        
    </div> <!-- container -->
    
@endsection

@section('script')
    <!-- Plugins js-->
    <script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>
    <script src="{{asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{asset('assets/libs/selectize/selectize.min.js')}}"></script>

    <!-- Dashboar 1 init js-->
    <script src="{{asset('assets/js/pages/dashboard-1.init.js')}}"></script>
@endsection
