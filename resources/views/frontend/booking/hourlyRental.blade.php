<style>



/*custom font*/
@import url(https://fonts.googleapis.com/css?family=Montserrat);

/*basic reset*/
* {
    margin: 0;
    padding: 0;
}

html {
    height: 100%;
    background: #eee;
}

body {
    font-family: Montserrat, arial, verdana;
    background: transparent;
    overflow: hidden;
}

/*form styles*/
#msform {
    text-align: center;
    position: relative;
    margin-top: 30px;
}

#msform fieldset {
    background: white;
    border: 0 none;
    border-radius: 8px;
    border:1px solid #ccc;    
    padding:15px;
    box-sizing: border-box;
    width: 95%;
    margin: 0 auto;

    /*stacking fieldsets above each other*/
    position: relative;
}
.custom-list .list-item {
    text-align: left;    
    padding-bottom: 5px;    
}
.booking-experienceNew{
    width: 100%!important;
}

/*Hide all except first fieldset*/
#msform fieldset:not(:first-of-type) {
    display: none;
}

/*inputs*/
#msform input, #msform textarea {
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-bottom: 10px;
    width: 100%;
    box-sizing: border-box;
    font-family: montserrat;
    color: #2C3E50;
    font-size: 13px;
}

#msform input:focus, #msform textarea:focus {
    -moz-box-shadow: none !important;
    -webkit-box-shadow: none !important;
    box-shadow: none !important;
    border: 1px solid #2098ce;
    outline-width: 0;
    transition: All 0.5s ease-in;
    -webkit-transition: All 0.5s ease-in;
    -moz-transition: All 0.5s ease-in;
    -o-transition: All 0.5s ease-in;
}

/*buttons*/
#msform .action-button , #msform .action-button-previous, #book_hourly_rental{
    width: 100%;
    background: var(--theme-deafult);
    font-weight: bold;
    color: white;
    border: 0 none;
    border-radius: 6px;
    border:1px solid var(--theme-deafult);
    cursor: pointer;
    padding: 10px 5px!important;
    margin: 10px auto 0;
    
}

#msform .action-button:hover, #msform .action-button:focus {
    background-color: transparent;
    color: var(--theme-deafult);
}
#book_hourly_rental:hover,  #book_hourly_rental:focus{
    background-color: transparent;
    color: var(--theme-deafult);
}

/* #msform .action-button-previous {
    width: 100px;
    background: #aCbEd0;
    font-weight: bold;
    color: white;
    border: 0 none;
    border-radius: 25px;
    cursor: pointer;
    padding: 10px 5px;
    margin: 10px 5px;
}

#msform .action-button-previous:hover, #msform .action-button-previous:focus {
    box-shadow: 0 0 0 2px white, 0 0 0 3px #aCbEd0;
} */

a.product-detail-box.d-flex.align-items-center.no-gutters.px-2.active {
    background-color: #f5f5f5!important;
    border-color:var(--theme-deafult);
    padding: 3px;
}
.vehical-container .product-detail-box{
    border: 1px solid transparent;
    padding: 3px;
    border-radius: 4px;
}
.al_body_template_nine .menu-slider .al_main_category a{
    color: #fff!important;
}
.al_body_template_nine .menu-slider .al_main_category a{
    border-color: #fff!important;
}
.menu-navigation.al:after{
    opacity: 1;
}
#main-menu{
    justify-content: center;
}
/*headings*/
.fs-title {
    font-size: 18px;
    text-transform: uppercase;
    color: #2C3E50;
    margin-bottom: 10px;
    letter-spacing: 2px;
    font-weight: 800;
}
#msform .text-right {
    font-weight: 600;
    color: var(--theme-deafult);
}
.fs-subtitle {
    font-weight: normal;
    font-size: 13px;
    color: #666;
    margin-bottom: 20px;
}

/*progressbar*/
#progressbar {
    margin-bottom: 30px;
    overflow: hidden;
    /*CSS counters to number the steps*/
    counter-reset: step;
}

#progressbar li {
    list-style-type: none;
    color: #666;
    text-transform: uppercase;
    font-size: 9px;
    width: 33.33%;
    float: left;
    position: relative;
    letter-spacing: 1px;
}

#progressbar li:before {
    content: counter(step);
    counter-increment: step;
    width: 24px;
    height: 24px;
    line-height: 26px;
    display: block;
    font-size: 12px;
    color: #333;
    background: white;
    border-radius: 25px;
    margin: 0 auto 10px auto;
}

/*progressbar connectors*/
#progressbar li:after {
    content: '';
    width: 100%;
    height: 2px;
    background: white;
    position: absolute;
    left: -50%;
    top: 9px;
    z-index: -1; /*put it behind the numbers*/
}

#progressbar li:first-child:after {
    /*connector not needed before the first step*/
    content: none;
}

/*marking active/completed steps blue*/
/*The number of the step and the connector before it = blue*/
#progressbar li.active:before, #progressbar li.active:after {
    background: #2098ce;
    color: white;
}

.custom-list {
    margin: 0;
    padding: 0;
}

.list-item {
    margin-left: 20px;
    position: relative;
}

.list-item:before {
    content: "\2022"; /* Unicode character for a bullet (•) */
    color: #000; /* Color of the bullet */
    font-size: 1.2em; /* Size of the bullet */
    position: absolute;
    left: -20px; /* Adjust as needed for your layout */
    top: 0;
}

.button-container {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price-box {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.price-text {
    font-weight: bold;
}

.price-value {
    font-size: 18px;
    color: #555; /* Adjust the color as needed */
}

.rounded-button {
    position: relative;
    display: inline-block;
    border: 0;
    border-radius: 6px;
    background-color: var(--theme-deafult);
    border:1px solid var(--theme-deafult);
    text-align: center;
    white-space: nowrap;
    cursor: pointer;
    font-family: 'Open Sans', sans-serif;
    color: #fff!important;
    font-weight: 400;
    text-transform: capitalize;
    width: 100%;
}
.rounded-button:hover, .rounded-button:focus{
    background-color: transparent;
    color: var(--theme-deafult)!important   ;
    border-color: var(--theme-deafult)!important;
}	

/* #rental container css */

.rental-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 10px;
        }

        .custom-button {
            width: 30px;
            height: 30px;
            font-size: 20px;
            cursor: pointer;
            border: 1px solid #ccc;
            background-color: white;
            border-radius: 50%; /* Make the buttons circular */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .button-text {
            font-size: 18px;
            font-weight: 600;
            color: var(--theme-deafult);
        }

        .box-container {
            display: flex;
            align-items: center;
            width: 100%;
            /* display: block !important; */

        }

        .custom-box {
            width: calc(100% / 12);
            height: 10px; /* Smaller box height */
            background-color: white;
            display: block !important;
            border: 1px solid #ccc;
        }

        .filled-box {
            background-color: var(--theme-deafult);
        }
		

        .custom-container {
            display: flex;
            justify-content: space-between;
        }

        .custom-column {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-right: 10px;
            cursor: pointer;
            text-align: center;
        }

        .highlighted {
            background-color: black;
            color: white;
        }
		
        .cab-outer{
            height: auto!important;
        }
        .location-inputs li{
            min-height: inherit;
            margin-bottom: 0;
        }
        .dots::after, .dots::before{
            content: unset!important;
        }
        .check-dropoff-secpond{
            border-radius: 6px;
        }
        #alTaxiBookingWrapper .location-inputs .title-24 {         
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        body.al_body_template_nine.p2p-module .location-search input.form-control {
            border-radius: 6px!important;
            border-color: var(--theme-deafult)!important;
            font-size: 16px!important;
            height: auto;
            line-height: 20px;
            color: var(--theme-deafult)!important;
            font-weight: 600;
            opacity: 1;
        }
        body.al_body_template_nine.p2p-module .location-search input.form-control::placeholder {
            color:var(--theme-deafult);
            font-size: 16px;
            opacity: 0.8;
        }
        .location-search{
            padding:14px 0 0 ;
        }
        .location-search i{
            right: 15px;
        }


@media (max-width:767px){
    body{
        overflow-x: hidden;
        overflow-y: auto;
    }
    header .inner-pages-offset  {
        margin: 0!important;
    }
    body .cab-booking #map_canvas, body .cab-booking #booking-map{
        /* height: auto!important; */
    }
    .alFullMapForm {
        max-width: 60% !important;     
        height: auto!important;
    }
    .booking-experienceNew{
        height: auto!important;
        padding-bottom: 50px;
        margin-bottom: 50px;
    }
    .alFullMapArea {
        height: auto!important;
    }
    #content-wrap{
        padding-bottom: 0!important;
    }
    .cab-booking{
        height: 100%!important;
    }
    #msform input{
        margin-top: 10px;
    }
    .booking-experienceNew {
        width: 90%!important;
    }
}
</style>
@if (isset($client_preference_detail) && $client_preference_detail->book_for_friend == 1)
<div class="tip_radio_controls_book_friend text-center mt-2">
    <input type="radio" class="tip_radio" id="for_me1" name="is_for_friend"
        value="0">
    <label class="tip_label mb-0  my-2 active is_for_friend" for="for_me" id="label_for_me1">
        <h5 class="m-0" id="tip_11">{{ __('For Me') }}</h5>
    </label>
    <input type="radio" class="tip_radio" id="for_friend1" name="is_for_friend"
        value="1">
    <label class="tip_label mb-0  my-2 is_for_friend" for="for_friend1" id="label_for_friend1">
        <h5 class="m-0" id="tip_15">{{ __('For Others') }}</h5>
    </label>
    @if (isset($client_preference_detail) && $client_preference_detail->is_hourly_pickup_rental == 1)
    <input type="radio" class="tip_radio " id="hourly_rental" name="is_for_friend"
    value="1">
    <label class="tip_label mb-0  my-2 for_hourly_rental" for="hourly_rental" id="label_for_hourly_rental1">
        <h5 class="m-0" id="tip_51">{{ __('Hourly Rental') }}</h5>
    </label>
    @endif
</div>
@elseif (isset($client_preference_detail) && $client_preference_detail->is_hourly_pickup_rental == 1)
<div class="tip_radio_controls_book_friend text-center mt-2">
    <input type="radio" class="tip_radio is_for_friend" id="hourly_rental" name="hourly_rental"
        value="1">
    <label class="tip_label mb-0  my-2" for="hourly_rental" id="label_for_hourly_rental1">
        <h5 class="m-0" id="tip_51">{{ __('Hourly Rental') }}</h5>
    </label>
</div>
@endif
<div class="row">
    <div class="col-md-12 col-md-offset-3">
        <form action="" id="msform">
            
            @csrf
            <!-- fieldsets -->
            <fieldset>
                <div class="custom-list">
                    <h2 class="fs-title">Select Category</h2>
                    <hr>
                    
                        <!-- Check if $navCategories is not empty -->
                        @if (!empty($navCategories))
                    
                            <!-- Loop through each category -->
                            @foreach ($navCategories as $category)
                                <a class="category-view-box d-flex align-items-center no-gutters px-2" href="javascript:void(0)" data-category_id="{{ $category->id }}">
                                    <div class="col-2 category-icon">
                                        <img class='img-fluid' src="{{$category->icon['proxy_url'].'200/200'.$category->icon['image_path']}}" alt="{{ $category->slug }} Image">
                                    </div>
                                    <div class="col-10">
                                        <div class="row no-gutters">
                                            <div class="col category-details">
                                                <h4 class="m-0"><b>{{ $category->slug }}</b></h4>
                                            </div>
                                           
                                        </div>
                                    </div>
                                </a>
                                <hr class="m-0">
                            @endforeach
                    
                        <!-- If $navCategories is empty -->
                        @else
                            <div class="col-12 category-details text-center">
                                <img class="w-100" src="{{ asset('assets/images/noproductfound.png') }}" alt="No Category Found">
                                {{ __('No result found. Please try a new search') }}
                            </div>
                        @endif
                    
                </div>
                <input type="button" name="next" class="next action-button" value="Next"/>

            </fieldset>
            <fieldset>
                <div class="custom-list">
                    <h2 class="fs-title">Hourly Rentals</h2>
                    <hr>
                    <div class="list-item">Keep the car and driver for as long as you need</div>
                    <div class="list-item">Make as many stops as you want</div>
                    <div class="list-item">No hassles of driving or parking</div>
                    <div class="list-item">Book anytime and get confirmation within minutes</div>
                </div>
                <hr>
                <div class="row mt-2">
                   
                    <div class="col-6 text-left ">Starting at</div>
                    <div id="starting_price" class="col-6 text-right">/hr</div>
                </div>
                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
                <input type="button" name="next" class="next action-button" value="Get Started"/>

            </fieldset>
            
            <fieldset>
                <h2 class="fs-title">How much time do you need?</h2>
                <div class="container rental-container ">
                    <div class="button-container">
                        <div class="custom-button" id="minusButton">-     </div>
                        <div class="button-text" id="buttonText">1</div>
                        <div class="custom-button" id="plusButton">+</div>
                    </div>
                    <input type="hidden" id="rental_hours" value="1" />
                    <input type="hidden" id="rental_price" value="{{decimal_format(0)}}" />
                   
                    <div class="box-container mb-3">
                        <div class="custom-box filled-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                        <div class="custom-box"></div>
                </div>
                <input type="hidden" id="selected_rental_product" value="" />
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-primary rounded-button" id ="leave-now">Leave Now</button>

                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="datetime-picker" name="booking-date" placeholder="Leave Later">
                            
                        </div>
                    </div>
                </div>
                  <hr>
                <div class="row mt-2 mb-2">
                   
                    <div class="col-6 text-left ">Starting at</div>
                    <div class="col-6 hourly_price text-right">${{decimal_format(0,2)}}/hr</div>
                </div>
                
                
                <hr>
                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
                <input type="button" id="select_vendor" name="next" class="next action-button" value="Choose a trip"/>
            </fieldset>
            <fieldset>
                <h2 class="fs-title">Cabs Available</h2>
                <div class="vehical-container style-4" id="search_rental_product_main_div"></div>
                <div class="payment-promo-container p-2" id="paymentMethods">
                    <h4 class="d-flex align-items-center justify-content-between mb-2 rental_payment_method_selection"  data-toggle="modal" data-target="#payment_modal">
                        <span id="payment_type">
                            <i class="fa fa-money" aria-hidden="true"></i> {{__('Cash')}}
                        </span>
                        <input type="hidden" id="stripe_token" name="stripe_token" value="">
                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                    </h4>
                </div>
                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
                <input type="button" name="next"  id="choose_rental" class="next action-button" value="Choose Rental"/>
            </fieldset>
            <fieldset>
                <h2 class="fs-title">Rental Details</h2>  
                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
                <input type="button" name="next"  class="next action-button" value="Next"/>                                      
                <div class="cab-detail-box style-4 d-none" id="cab_detail_box"></div>
               
            </fieldset>
            <fieldset>
                <h2 class="fs-title">Choose your pick-up location</h2>
                
                <div class="location-search d-flex align-items-center check-pickup">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input class="form-control pickup-text pac-target-input" type="text"
                        name="pickup_location_name[]" placeholder="Add A Pick-Up Location" id="pickup_hourly_location"
                        autocomplete="off">
                </div>
                    <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
                    <button class="btn btn-solid w-100" id="book_hourly_rental" data-rel="pickup_now" data-task_type="" data-payment_method="1">{{__('Book Now')}}</button>

            </fieldset>
            
         
           
        </form>
    </div>
</div>

<script src="{{ asset('js/hourly-rental.js') }}"></script>
