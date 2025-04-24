
<link rel="stylesheet" href="{{ asset('css/rental.css') }}">
<link href="{{asset('assets/libs/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css" />
<script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>


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
                                    <label style="visibility: hidden;"> submit</label>
                                    <div class="cta">
                                        <button type="submit" class="border-0">Search</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                            <div class="col">
                                <div class="form-group" id="diff-box">
                                        <input type="checkbox" name="diff_location" id="diff-location" />
                                        <label for="diff-location" class="different_cta">Different Return Location</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layouts.store.remove_cart_model')
   
     @include('layouts.store/car-rental-footer')
    <script src="{{ asset('js/car-rental.js') }}"></script>
</section>