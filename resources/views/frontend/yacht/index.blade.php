@extends('layouts.car-rental', [
	'title' => 'Home'
])
{{-- @extends('layouts.store', [
'title' => (!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->name : $category->slug,
'meta_title'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_title:'',
'meta_keyword'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_keyword:'',
'meta_description'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_description:'',
]) --}}
@section('content')
<section class="banner">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="text">
                    <h1>Seamless Journeys, Unforgettable Rides </h1>
                    <p>Your Trusted Car Rental and Airport Transfer Solution.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="image">
                    <img src="yacht-images/banner.png" alt="banner">
                </div>
            </div>
        </div>
        <div class="banner_tab">
            <div class="tabs">
                {{-- <ul id="tabs-nav" class="d-flex align-items-center">
				    <li>
				    	<a href="#tab1">
				    		<img src="yacht-images/icons/1.png" alt="">
				    		Car Rental
				    	</a>
				    </li>
				    <li>
				    	<a href="#tab1">
				    		<img src="yacht-images/icons/2.png" alt="">
				    		Airport Pick and Drop
				    	</a>
				    </li>
					<li>
				    	<a href="#tab1">
				    		<img src="yacht-images/icons/2.png" alt="">
				    		Yacht Pick and Drop
				    	</a>
				    </li>				    
				  </ul> <!-- END tabs-nav --> --}}
                <form action="{{ route('productSearch') }}" method="GET">
                    @csrf
                    <div class="tab">
                        <div class="d-flex align-items-center">
                            <div class="form-group">
                                <input type="radio" value="rental" name="service" placeholder="" id="car" checked>
                                <label for="car">
                                    <img src="yacht-images/icons/1.png" alt="">
                                    Car Rental
                                </label>
                                <span></span>
                            </div>
                            {{-- <div class="form-group">
                                <input type="radio" value="airport" name="service" placeholder="" id="Airport">
                                <label for="Airport">
                                    <img src="yacht-images/icons/2.png" alt="">
                                    Airport Pick and Drop
                                </label>
                                <span></span>
                            </div> --}}
                            {{-- <div class="form-group">
                                <input type="radio" value="yacht" name="service" placeholder="" id="Yacht">
                                <label for="Yacht">
                                    <img src="yacht-images/icons/2.png" alt="">
                                    Yacht Pick and Drop
                                </label>
                                <span></span>
                            </div> --}}
                        </div>
                    </div>
                    <div id="tabs-content">
                        <div id="tab1" class="tab-content">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="item">
                                        <h5>Pickup Location</h5>
                                        <p><img src="yacht-images/icons/3.png" alt="">
                                            <input class="" type="text" name="pickup_location" id="pickup_location" value="" placeholder="1801 Oak Ridge Ln" required>
                                            <input type="hidden" name="pickup_longitude" id="pickup_longitude" value="">
                                            <input type="hidden" name="pickup_latitude" id="pickup_latitude" value="">
                                        </p>
                                    </div>
                                    <div class="form-group" id="diff-box">
                                        <input type="checkbox" name="diff_location" id="diff-location" />
                                        <label for="diff-location" class="different_cta">Different Return Location</label>
                                    </div>
                                </div>
                                <div class="col" id="dropoff-box" style="display:none;">
                                    <div class="item">
                                        <h5>Return Location</h5>
                                        <p><img src="yacht-images/icons/3.png" alt="">
                                            <input type="text" name="drop_location" id="drop_location" value="" placeholder="1801 Oak Ridge Ln">
                                            <input type="hidden" name="drop_longitude" id="drop_longitude" value="">
                                            <input type="hidden" name="drop_latitude" id="drop_latitude" value="">
                                        </p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="item">
                                        <h5>Pickup Dropoff Date & Time</h5>
                                        <p><img src="yacht-images/icons/4.png" alt="">
											<input type="text" id="range-datepicker" class="form-control flatpickr-input" placeholder="2018-10-03 to 2018-10-10" name="pick_drop_time" required="required"/>
                                            {{-- <input type="datetime-local" name="pickup_time"> --}}
                                        </p>
                                    </div>
                                </div>
                                    {{-- <div class="col d-none">
                                    <div class="item">
                                        <h5>Drop Date & Time</h5>
                                        <p><img src="yacht-images/icons/4.png" alt="">
                                            <input type="datetime-local" name="drop_time">
                                        </p>
                                    </div>
                                </div> --}}
                                <div class="col" style="display: none" id="seats_div">
                                    <div class="item">
                                        <h5>Seat Number</h5>
                                        <p>
                                            <input class="pl-0" type="number" name="seats" value="" placeholder="04">
                                        </p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="cta">
                                        <button type="submit" class="border-0">Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="ourServices">
    <div class="container">
        <div class="heading">
            <h2>Our Services</h2>
        </div>
        <div class="row mx-0">
            <!-- 1 -->
            <div class="col-md-4 px-0">
                <div class="item">
                    <div class="text">
                        <h3>Car Rental</h3>
                        <p>Economical, Family or Spacious Cars. Exclusive Offers & Low Prices. Book on our Web instead of Comparison Websites. Best Price Guaranteed.</p>
                    </div>
                    <div class="image">
                        <img src="yacht-images/1.png" alt="">
                    </div>
                </div>
            </div>
            <!-- 2 -->
            <div class="col-md-4 px-0">
                <div class="item">
                    <div class="image">
                        <img src="yacht-images/2.png" alt="">
                    </div>
                    <div class="text">
                        <h3>Airport Pick and Drop</h3>
                        <p>Economical, Family or Spacious Cars. Exclusive Offers & Low Prices. Book on our Web instead of Comparison Websites. </p>
                    </div>
                </div>
            </div>
            <!-- 3 -->
            <div class="col-md-4 px-0">
                <div class="item">
                    <div class="text">
                        <h3>Yacht services</h3>
                        <p>Economical, Family or Spacious Cars. Exclusive Offers & Low Prices. Book on our Web instead of Comparison Websites. </p>
                    </div>
                    <div class="image">
                        <img src="yacht-images/3.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="ourFleet">
    <div class="container">
        <div class="heading">
            <h2>Our Category</h2>
        </div>
        <div class="fleet_slider">
            @forelse($categories as $category)
            <div class="item">
                <div class="image">
                    <img src="{{ $category->icon['proxy_url'].'200/200'.$category->icon['image_path']}} " alt="">
                </div>
                <div class="text">
                    <h3>{{ $category->slug ?? ''}}</h3>
                </div>
            </div>
            @empty
            No Category Found
            @endforelse
        </div>
    </div>
</section>

<section class="offers_block">
    <div class="container">
        <div class="heading">
            <h2>Ongoing Offers</h2>
        </div>
        <div class="row">
            <!-- 1 -->
            <div class="col-md-4">
                <div class="item">
                    <div class="image">
                        <img src="yacht-images/offers/1.png" alt="">
                        <span class="offer">20% off</span>
                    </div>
                    <div class="text">
                        <h4>Superfast Pickup and Drop</h4>
                        <p>Pickup and drop services in no time.</p>
                    </div>
                </div>
            </div>
            <!-- 2 -->
            <div class="col-md-4">
                <div class="item">
                    <div class="image">
                        <img src="yacht-images/offers/2.png" alt="">
                        <span class="offer">10% off</span>
                    </div>
                    <div class="text">
                        <h4>Airport pickup service</h4>
                        <p>Enjoy shuttle service at low prices this month.</p>
                    </div>
                </div>
            </div>
            <!-- 3 -->
            <div class="col-md-4">
                <div class="item">
                    <div class="image">
                        <img src="yacht-images/offers/3.png" alt="">
                        <span class="offer">30% off</span>
                    </div>
                    <div class="text">
                        <h4>Your Trip in style</h4>
                        <p>Book SUV cars for your next desert Safari !</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mobileApp">
    <div class="container">
        <div class="mobileApp_text text-center">
            <h2>Our mobile app</h2>
            <h3>coming soon.</h3>
            <p>With best-in-class car rentals and services that provide you the best experience of all time !</p>
            <div class="d-flex justify-content-center">
                <a href=""><img src="yacht-images/playstore.png"></a>
                <a href=""><img src="yacht-images/appstore.png"></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 text-center left">
                <div class="image">
                    <img src="yacht-images/phone.png" alt="">
                </div>
            </div>
            <div class="col-md-6 text-center right">
                <div class="image">
                    <img src="yacht-images/iphone.png" alt="">
                </div>
            </div>
        </div>
    </div>
</section>
@endsection