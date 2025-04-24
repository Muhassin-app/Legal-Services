@extends('layouts.store', ['title' => __('Not Available')])


@section('content')
@php
    $now = \Carbon\Carbon::now()->format('Y-m-d\TH:i');
    if(Auth::user()){
        $timezone = Auth::user()->timezone;
        $now = convertDateTimeInTimeZone($now, $timezone, 'Y-m-d\TH:i');
    }
@endphp


<div class="container">
    <div class="row mt-2 mb-4 mb-lg-5">
        <div class="col-12 text-center">
            <div class="cart_img_outer">
                <img src="{{asset('front-assets/images/empty_cart.png')}}">
            </div>
            <h3>{{__('Not Available at your selected location!')}}</h3>
            <a class="btn btn-solid" href="{{url('/')}}">{{__('Continue Shopping')}}</a>
        </div>
    </div>
</div>



@endsection
