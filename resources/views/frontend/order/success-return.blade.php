@extends('layouts.store', ['title' => 'Success'])
@section('content')

<section class="section-b-space light-layout">
    <div class="container">
        <div class="row">
            <div class="col-md-12 mt-5 mb-5">
                <div class="success-text">
                	<i class="fa fa-check-circle" aria-hidden="true"></i>
                    <h2>{{__('Thank You')}}</h2>
                    <p>{{__('Your order has been placed successfully')}}</p>
                    <p><a href="{{ route('user.orders') }}">{{__('View Order')}}</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection