@extends('layouts.store', ['title' => __('All Brands')])

@section('content')
    <section class="section-b-space new-pages pb-265 ad al_new_all_venders">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-3 mt-3">{{ __('All Brands')}}</h2>
                </div>
            </div>
            <div class="row margin-res">
                @forelse($brands as $brand)
                    
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-3">
                        <div  class="logo_brand">
                            <a class="brand-box d-block black-box" href="{{$brand->redirect_url }}">
                                <div class="brand-img">
                                    <!-- <img class="brand-banner" src="{{asset('images/template-8/brnd_img.png')}}" alt="" title="">  -->
                                    <img class="blur-up lazyload brand-logo" data-src="{{ get_file_path($brand->image,'FILL_URL','260','260') }}" alt="" title="">
                                </div>
                                <h4>{{ $brand->translation_title }}</h4>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <img class="no-store-image mt-2 mb-2 blur-up lazyload" data-src="{{ asset('images/no-stores.svg') }}" style="max-height: 250px;">
                        <h4>{{ __('There are no stores available in your area currently.') }}</h4>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection