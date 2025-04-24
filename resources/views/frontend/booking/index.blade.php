@extends('layouts.store', ['title' => 'Product'])
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/intlTelInput.css') }}">
    <link href="{{asset('assets/css/azul.css')}}" rel="stylesheet" type="text/css" />
    {{-- <link rel="stylesheet" href="{{asset('assets/libs/jquery.datetimepicker.min.css')}}"> --}}
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/slick-theme.css')}}"/>
    <link rel="stylesheet" href="{{asset('front-assets/css/slick.css')}}">
@endsection
@section('content')
    <style type="text/css">
        .option {
            background: #fff;
            height: 100%;
            width: 100%;
            text-align: center;
            cursor: pointer;
            -webkit-transition: all .3s ease;
            transition: all .3s ease
        }

        input.alCheckMark[type=radio] {
            display: none
        }

        .alRiderImg {
            display: inline-block;
            height: 50px;
            width: 50px;
            border-radius: 50%;
            line-height: 45px;
            color: #fff;
            border: 3px solid transparent;
            font-size: 24px;
            text-transform: capitalize
        }

        .alRiderName {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 12px
        }

        .alCheckMark:checked:checked~.option .alRiderImg {
            border: 4px solid #fff;
            box-shadow: 0 2px 10px rgb(0 0 0 / 30%);
            text-transform: capitalize
        }

        .alCheckMark:checked:checked~.option span.alCloseBtn {
            display: none
        }

        .alAddRiderSecOuter {
            max-height: 220px;
            overflow-y: auto;
            overflow-x: hidden
        }

        /* #alTaxiBookingWrapper .vehical-container {
            max-height: 200px
        } */

        #alTaxiBookingWrapper .location-box {
            padding: 10px 24px
        }

        #alTaxiBookingWrapper .location-inputs .title-24 {
            font-size: 16px;
            line-height: 24px;
            font-weight: 500
        }

        #alTaxiBookingWrapper .scheduled-ride {
            padding: 10px
        }

        .alAddRiderSecOuter button.btn.rounded {
            border: 1px solid
        }

        span.alCloseBtn {
            position: absolute;
            background-color: #fff;
            height: 15px;
            width: 15px;
            font-size: 10px;
            font-weight: 600;
            line-height: 15px;
            color: #000;
            border-radius: 30px;
            right: 28px;
            margin: 0 auto;
            top: -3px;
            box-shadow: 0 0 5px rgb(0 0 0 / 30%);
            z-index: 99;
            cursor: pointer;
            display: none;
            -webkit-transition: all .3s ease;
            transition: all .3s ease
        }

        .alHoverRiderBox:hover span.alCloseBtn {
            display: block
        }

        .input-hidden {
            position: absolute;
            left: -9999px
        }

        ::-webkit-scrollbar {
            width: 3px
        }

        ::-webkit-scrollbar-track {
            box-shadow: inset 0 0 2px grey;
            border-radius: 10px
        }

        ::-webkit-scrollbar-thumb {
            background: var(--theme-deafult);
            border-radius: 10px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #b30000
        }

        .alRiderRadioBox label {
            border: 2px solid transparent;
            position: relative
        }

        .alRiderRadioBox label:before {
            position: absolute;
            top: 0;
            left: 0;
            content: "";
            background-color: var(--theme-deafult);
            height: 25px;
            width: 25px;
            z-index: 1;
            border-radius: 0 0 50px 0;
            display: none
        }

        .alRiderRadioBox label:after {
            position: absolute;
            content: "";
            left: 7px;
            top: 4px;
            width: 6px;
            height: 10px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
            z-index: 2;
            display: none
        }

        .alRiderRadioBox input[type=radio]:checked+label {
            border: 2px solid var(--theme-deafult);
            box-shadow: 0 3px 3px rgba(0, 0, 0, .2)
        }

        .alRiderRadioBox input[type=radio]:checked+label:after,
        .alRiderRadioBox input[type=radio]:checked+label:before {
            display: block
        }

        .alFullMapForm {
            max-width: 450px !important;
            margin-left: 15px !important;
            top: 50px;
            left: 0;
        }

        body .alFullMapForm .scheduled-footer .btn {
            font-size: 14px !important;
            padding: 0 !important;
            line-height: 3;
            min-height: auto;
            border: 1px solid var(--theme-deafult)
        }

        /* body .alFullMapForm .scheduled-footer .btn:hover{ } */
        .booking-experienceNew {
            background-color: #fff;
            left: 0;
            height: 100%;
            /* height: auto; */
            overflow-x: hidden;
            overflow-y: scroll;
            width: 100%;
            padding-bottom:30px;
        }

        .slick_bid_ride .slick-items{
  width:290%;
  margin:0px auto;
}
.slick_bid_ride .slick-slide{
  margin:6px;border:1px solid#eee;cursor: pointer;
  border-radius:10px;
}
.slick_bid_ride .slick-slide img{
  width:100%;
  border-radius:11px;
}

.slick_bid_ride .slick-slide a:hover{
  border-radius:10px;
  border: 1px solid #6b6f74;
}

.slick_bid_ride .slick-slide .active{
  border-radius:10px;
  border: 1px solid #6b6f74;
}

.slick_bid_ride .slick-slide h5 {
    font-size: 12px;
    font-weight:400;
}

.slider .slick-prev:before, .slick-next:before{    font-size: 30px;
    line-height: 1;
    opacity: .75;
    color: #000;}

    .cab-bg {
    background: #eee;
    padding: 2px 0px;
}
.recommend-column p {
    display: inline-block;
}
.recommend-column p {
    font-size: 20px;
}

.recommendated_price {
    font-size: 13px;
   /*  background: #d1d0d0a6; */
    padding: 2px 9px;
    border-radius: 2px;
}

.recommendated_price span {
    font-size: 12px;
}

.slick_bid_ride .slick-prev.slick-arrow{left:0px;}
.slick_bid_ride .slick-next.slick-arrow{right:5px;}
.slick_bid_ride .slick-prev:before {
    font-size: 18px;
}
.slick_bid_ride .slick-next:before {
    font-size: 18px;
}

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0;
}

.bid-btnleft {
    width: 100%;
    max-width: 50%;
    margin: 0 auto;
}

.driver_info img {
    width: 126px;
    margin-right: 20px;
}
.driver_info {
    display: flex;
    align-items: center;
    justify-content: space-around;
}

.driver_info .user-info h4 {
    font-size: 14px;
    font-weight: 600;
    color: #000;
}

.driver_info .user-info p {
    font-size: 14px;
}

.driver_info .user-info p span {
    float: right;
}

.driver_info .user-info button {
    border: none;
    padding: 6px 20px;
}
.driver_info .user-info button:hover{
    background-color:#51089b;color:#fff !important;
}

.text-loader {
    text-align: center;
    padding: 40px 0px;
    font-size: 14px;
}

.text-loader i {
    font-size: 16px !important;
}
    </style>
    <section id="alTaxiBookingWrapper" class="cab-booking pt-0 pb-0">
        <div class="alFullMapArea col-md-12 p-0 h-100">
            <div id="booking-map" style="width: 100%; height: 100%;"></div>
            <input id="booking-latitude" type="hidden" value="-34">
            <input id="booking-longitude" type="hidden" value="151">
            <input id="selected_category_id" type="hidden" value="">
            <input id="selected_vendor_id" type="hidden" value="">
            <input id="starting_rental_price" type="hidden" value="">

        </div>
        <div class="alFullMapForm col-md-12 p-0 position-absolute">
            <div class="booking-experienceNew">
                <div class="address-form">
                    @if (isset($client_preference_detail) && $client_preference_detail->book_for_friend == 1)
                        <div class="tip_radio_controls_book_friend text-center mt-2">
                            <input type="radio" class="tip_radio is_for_friend" id="for_me" name="is_for_friend"
                                value="0">
                            <label class="tip_label mb-0  my-2 active " for="for_me" id="label_for_me">
                                <h5 class="m-0" id="tip_5">{{ __('For Me') }}</h5>
                            </label>
                            <input type="radio" class="tip_radio is_for_friend" id="for_friend" name="is_for_friend"
                                value="1">
                            <label class="tip_label mb-0  my-2" for="for_friend" id="label_for_friend">
                                <h5 class="m-0" id="tip_5">{{ __('For Others') }}</h5>
                            </label>
                            @if (isset($client_preference_detail) && $client_preference_detail->is_hourly_pickup_rental == 1)
                            <input type="radio" class="tip_radio is_for_friend" id="hourly_rental" name="is_for_friend"
                            value="1">
                            <label class="tip_label mb-0  my-2" for="hourly_rental" id="label_for_hourly_rental">
                                <h5 class="m-0" id="tip_5">{{ __('Hourly Rental') }}</h5>
                            </label>
                            @endif
                        </div>
                    @elseif (isset($client_preference_detail) && $client_preference_detail->is_hourly_pickup_rental == 1)
                        <div class="tip_radio_controls_book_friend text-center mt-2">
                            <input type="radio" class="tip_radio is_for_friend" id="hourly_rental" name="hourly_rental"
                                value="1">
                            <label class="tip_label mb-0  my-2" for="hourly_rental" id="label_for_hourly_rental">
                                <h5 class="m-0" id="tip_5">{{ __('Hourly Rental') }}</h5>
                            </label>
                        </div>
                    @endif


                    @if($is_cab_pooling == 1 || $is_bid_ride_enable == 1 || $is_recurring_booking == 1 || $is_particular_driver == 1)
                        <div class="pool_radio_controls text-center">
                            <div class="item">
                                <input type="radio" class="pool_radio is_cab_pooling_radio" id="cab_booking" name="is_cab_pooling_radio"
                                    value="0" checked>
                                <label class="pool_label mb-0  my-2 active " for="cab_booking" id="label_cab_booking">
                                    <h5 class="m-0" id="pool_5">{{ __('Booking') }}</h5>
                                </label>


                            </div>
                            <div class="item mx-1">
                            <input type="radio" class="pool_radio is_cab_pooling_radio" id="cab_pooling" name="is_cab_pooling_radio"
                                      value="1">
                                <label class="pool_label mb-0  my-2" for="cab_pooling" id="label_cab_pooling">
                                    <h5 class="m-0" id="pool_5">{{ __('Pooling') }}</h5>
                                </label>


                            </div>
                            @endif

                            @if($is_bid_ride_enable == 1)
                            <div class="item mx-1">
                                <input type="radio" id="bid_radio" class="pool_radio is_cab_pooling_radio"  name="is_cab_pooling_radio" value="0">
                                <label class="pool_label mb-0  my-2" >
                                    <h5 class="m-0" id="pool_5">Bid</h5>
                                </label>
                            </div>
                            @endif

                            @if($is_particular_driver == 1)
                            <div class="item">

                                <input type="radio" id="particular_driver_radio" class="pool_radio is_cab_pooling_radio"  name="is_cab_pooling_radio" value="2">
                                <label class="mb-0  my-2" >
                                    <h5 class="m-0" id="">Request to Driver</h5>
                                </label>
                            </div>
                            @endif
                            @if($is_recurring_booking == 1)
                            <div class="item">

                            <input type="radio" id="is_recurring_booking" class="pool_radio is_cab_pooling_radio"  name="is_cab_pooling_radio" value="4">
                            <label class="mb-0  my-2" >
                                <h5 class="m-0" id="">{{ __(getDynamicTypeName('Recurring')) }}</h5>
                            </label>
                            </div>
                            @endif
                        </div>

                    <div class="location-box check-pick-first">
                        <div class="where-to-go">
                            <div class="title title-36">{{ __(getDynamicTypeName('Where can we pick you up?')) }}</div>
                        </div>
                    </div>
                    <div class="location-box check-dropoff-secpond" style="display:none">
                        <ul class="location-inputs position-relative pl-2" id="location_input_main_div">
                            <li class="d-flex dots">
                                <div class="title title-24 position-relative edit-pickup"> {{ __('From') }} - <span
                                        id="pickup-where-from"></span><i class="fa fa-angle-down" aria-hidden="true"></i>
                                </div>
                            </li>
                            <li class="d-flex dots where-to-first">
                                <div class="title title-36 pr-3 position-relative">{{ __(getDynamicTypeName('Where To?')) }}</div>
                            </li>
                            <li class="d-flex dots where-to-second" style="display:none !important;">
                                <div class="title title-24 position-relative edit-dropoff"> {{ __('To') }} - <span
                                        id="dropoff-where-to"></span><i class="fa fa-angle-down" aria-hidden="true"></i>
                                </div>
                                    <i class="fa fa-times ml-1 apremove delete-drop-off" aria-hidden="true" data-rel=""></i>
                            </li>
                        </ul>
                        <a class="add-more-location position-relative pl-2" style="display:none"
                            href="javascript:void(0)">{{ __('Add Destination') }}</a>
                    </div>
                    <input type="hidden" name="pickup_location_latitude[]" value="" id="pickup_location_latitude">
                    <input type="hidden" name="pickup_location_longitude[]" value="" id="pickup_location_longitude">
                    <input type="hidden" name="destination_location_latitude[]" value=""
                        id="destination_location_latitude" />
                    <input type="hidden" name="destination_location_longitude[]" value=""
                        id="destination_location_longitude" />

                    <input type="hidden" name="default_cab_vendor" value="" id="default_cab_vendor">
                    <input type="hidden" name="default_cab_vendor_id" value="" id="default_cab_vendor_id">

                    <input type="hidden" id="address-input" value="" />
                    <input type="hidden" id="address-latitude" value="" />
                    <input type="hidden" id="address-longitude" value="" />
                    <input type="hidden" name="schedule_date" value="" id="schedule_date" />
                    <div class="location-containerNew style-4">
                        <div class="location-search d-flex align-items-center check-pickup">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            <input class="form-control pickup-text pac-target-input" type="text"
                                name="pickup_location_name[]" placeholder="Add A Pick-Up Location" id="pickup_location"
                                autocomplete="off">
                        </div>
                        <div class="location-search d-flex align-items-center" style="display:none !important;"
                            id="destination_location_add_more">
                        </div>
                        <div class="location-search d-flex align-items-center check-dropoff"
                            style="display:none !important;">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            <input class="form-control pickup-text" name="destination_location_name[]" type="text"
                                placeholder="{{ __('Add A Stop') }}" id="destination_location" />
                        </div>
                        <div class="location-search d-flex align-items-center" style="display:none !important;"
                            id="destination_location_add_temp">

                        </div>
                        <div class="scheduled-ride">
                            <button><i class="fa fa-clock-o" aria-hidden="true"></i> <span
                                    class="mx-2 scheduleDateTimeApnd">{{ __('Now') }}</span> <i
                                    class="fa fa-angle-down" aria-hidden="true"></i></button>
                        </div>
                        @if ($wallet_balance < 0)
                            <div class="row">
                                <div class="col-md-7">
                                    <h6 style="color: red;">{{ __('* Please recharge your wallet.') }}
                                </div>
                                <div class="col-md-5 text-md-right text-center">
                                    <button type="button" class="btn btn-solid" id="topup_wallet_btn"
                                        data-toggle="modal" data-target="#topup_wallet">{{ __('Topup Wallet') }}</button>
                                </div>
                            </div>
                        @endif
                        <div class="cab-booking-loader"></div>
                        <div class="location-list style-4">
                            <a class="select-location row align-items-center" id="get-current-location"
                                href="javascript:void(0)">
                                <div class="col-2 text-center pl-4">
                                    <div class="round-shape active-location">
                                        <i class="fa fa-crosshairs" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <div class="col-10 pl-3">
                                    <h4><b>{{ __('Allow location Access') }}</b></h4>
                                    <div class="current-location ellips text-color mb-2">{{ __('Your current location') }}
                                    </div>
                                    <hr class="m-0">

                                </div>
                            </a>
                            @forelse($user_addresses as $user_address)
                                <!-- <a class="search-location-result position-relative d-block" href="javascript:void(0);" data-address="{{ $user_address->address }}" data-latitude="{{ $user_address->latitude }}" data-longitude="{{ $user_address->longitude }}">
                                        <h4 class="mt-0 mb-1"><b>{{ $user_address->address }}</b></h4>
                                        <p class="ellips mb-0">{{ $user_address->city }}, {{ $user_address->state }}, {{ $user_address->country }}</p>
                                    </a> -->
                                <a class="search-location-result position-relative row align-items-center mt-2"
                                    href="javascript:void(0);" data-address="{{ $user_address->address }}"
                                    data-latitude="{{ $user_address->latitude }}"
                                    data-longitude="{{ $user_address->longitude }}">
                                    <div class="col-2 text-center pl-3">
                                        <div class="round-shape">
                                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                    <div class="col-10 pl-3">
                                        <h4 class="mt-0 mb-1"><b>{{ $user_address->address }}</b></h4>
                                        <div class="current-location ellips mb-2">{{ $user_address->city }},
                                            {{ $user_address->state }}, {{ $user_address->country }}</div>
                                        <hr class="m-0">
                                    </div>
                                </a>
                            @empty
                            @endforelse
                        </div>
                        <div class="scheduled-ride-list">
                            <div class="scheduled-ride-list-heading d-flex align-items-center justify-content-between">
                                <h3>{{ __('Choose Date And Time') }}</h3>
                                <span class="skip-clear">
                                    {{ __('Skip') }}
                                </span>
                            </div>

                            <div class="date-radio-list1 style-4">
                                <div class="datepicker date input-group p-2">
                                    <input type="text" name="schedule_pickup_date" placeholder="Choose Date"
                                        class="form-control" id="schedule_pickup_date">
                                    <div class="input-group-append">
                                        <span class="input-group-text calendar_icon" for="schedule_pickup_date"><i
                                                class="fa fa-calendar"></i></span>
                                    </div>
                                </div>

                                @if($is_recurring_booking == 1)
                                    <div class="recurring-option">
                                        @include('frontend.product-part.recurring-booking')
                                    </div>
                                @endif

                            </div>

                            <div class="scheduled-footer">

                            </div>
                        </div>
                        <div class="table-responsive style-4">
                            <div class="cab-button d-flex flex-nowrap align-items-center py-2 pl-2" id="vendor_main_div">
                            </div>
                        </div>
                        <div class="vehical-container style-4" id="search_product_main_div"></div>
                        <div class="vehical-container style-4" id="search_product_rider_main_div" style="display:none;">
                        </div>

                        <!-- Riders Code -->
                        <div class="alAddRiderSecOuter" id="rider_section" style="display:none;">
                            <div class="col-12 d-flex justify-content-between align-items-center">
                                @if (count($riders) > 0)
                                    <p class="m-0">Riders : <span id="rider_count">{{ count($riders) }}</span></p>
                                @endif
                                <button class="btn rounded {{ count($riders) == 0 ? 'w-100' : '' }}  add_rider_button"
                                    data-toggle="modal" data-target="#alAddRiderSecModal"><i class="fa fa-plus"></i>
                                    {{ __('Add Rider') }}</button>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="row alRiderImgBox">
                                    @foreach ($riders as $key => $rider)
                                        <div class="col-3 text-center alHoverRiderBox">

                                            <input class="alCheckMark" type="radio" name="rider_id"
                                                id="option-{{ $key }}" {{ $key == 0 ? 'checked' : '' }}
                                                value="{{ $rider->id }}">
                                            <label for="option-{{ $key }}"
                                                class="option option-{{ $key }}">

                                                <div class="alRiderImg mb-1" style="background-color:<?php printf("#%06X\n", mt_rand(0, 0xffffff)); ?>">
                                                    {{ substr($rider->first_name, 0, 1) }}</div>
                                                <div class="dalRiderInfo">
                                                    <p class="alRiderName mb-0">{{ $rider->first_name }}</p>
                                                </div>

                                            </label>
                                            <span class="alCloseBtn deleteRider" data-id="{{ $rider->id }}">X</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="product_rider_div" style="display:none;">
                            <button class="btn btn-solid w-100"
                                id="submit_product_rider_button">{{ __('Next') }}</button>
                        </div>


                    </div>
                </div>
                <div class="hourly-rental-container">
                </div>
                <script type="text/template" id="rider_template">

                    <div class="col-12 d-flex justify-content-between align-items-center">
                <% if(riders.length > 0){%>
                    <p class="m-0">Riders : <%= riders.length %></p>
                <% } %>
                <button class="btn rounded <% if(riders.length > 0){'w-100'} %> add_rider_button" data-toggle="modal" data-target="#alAddRiderSecModal"><i class="fa fa-plus"></i> {{__('Add Rider')}}</button>
            </div>
            <div class="col-12 mt-2">
                <div class="row alRiderImgBox">
                    <% _.each(riders, function(rider, key){%>
                        <%
                        var randomColor = "#" + ((1<<24)*Math.random() | 0).toString(16);
                        %>
                        <div class="col-3 text-center alHoverRiderBox">
                            <input class="alCheckMark" type="radio" name="rider_id" id="option-<%= key %>" value="<%= rider.id %>"  <% if(key == 0){'checked'} %> >
                            <label for="option-<%= key %>" class="option option-<%= key %>">
                                <div class="alRiderImg mb-1" style="background-color: <%=randomColor%> "> <%= (rider.first_name).charAt(0)%></div>
                                <div class="dalRiderInfo">
                                    <p class="alRiderName mb-0"><%=rider.first_name%></p>
                                </div>
                            </label>
                            <span class="alCloseBtn deleteRider" data-id="<%=rider.id%>">X</span>
                        </div>
                    <% }); %>
                </div>
            </div>
        </script>
                <script type="text/template" id="vendors_template">
            <% _.each(results, function(result, key){%>
                <a class="btn btn-solid ml-2 vendor-list" href="javascript:void(0);" data-vendor="<%= result.id %>"><%= result.name %></a>
            <% }); %>
        </script>
                <script type="text/template" id="products_rider_template">
            <% if(results != ''){ %>
            <% _.each(results, function(result, key){%>
                <div class="vehical-view-box-1 alRiderRadioBox" data-product_id="<%= result.id %>">
                    <input type="radio" name="rider_product_id" value="<%= result.id %>" <% if(key == 0){'checked'} %>  id="alRiderRadio_<%= key %>" class="input-hidden" />
                    <label for="alRiderRadio_<%= key %>" class="d-flex align-items-center no-gutters px-2">
                        <div class="col-3 vehicle-icon">
                            <img class='img-fluid' src='<%= result.image_url %>'>
                        </div>
                        <div class="col-9">
                            <div class="row no-gutters">
                                <div class="col vehicle-details">
                                    <h4 class="m-0"><b><%= result.name %></b></h4>
                                </div>
                                <div class="col ride-price pl-2 text-right">
                                    <p class="mb-0"><b>{{Session::get('currencySymbol')}}<%= result.tags_price%></b></p>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
                <hr class="m-0">
            <% }); %>
            <% }else{ %>
                <div class="col-12 vehicle-details  text-center">
                    <img class="w-100" src="{{asset('assets/images/noproductfound.png')}}" alt="noproductfound">
                    {{ __('No result found. Please try a new search') }}
                </div>
            <% } %>
        </script>
        <script type="text/template" id="products_template">
            <% if(results != ''){ %>
            <% _.each(results, function(result, key){%>
                <a class="vehical-view-box d-flex align-items-center no-gutters px-2" href="javascript:void(0)" data-product_id="<%= result.id %>">
                    <div class="col-2 vehicle-icon">
                        <img class='img-fluid' src='<%= result.image_url %>'>
                    </div>
                    <div class="col-10">

                        <div class="row no-gutters">
                            <div class="col vehicle-details">
                                <h4 class="m-0"><b><%= result.name %></b></h4>
                               <h6 class="m-0"><%= result.description %></h6>
                            </div>
                            <div class="col ride-price pl-2 text-right">
                            <p class="mb-0"><b>{{Session::get('currencySymbol')}}<%= result.tags_price%></b></p>
                            </div>
                        </div>

                    </div>
                </a>
                <hr class="m-0">
            <% }); %>
            <% }else{ %>
                <div class="col-12 vehicle-details text-center">
                    <img class="w-100" src="{{asset('assets/images/noproductfound.png')}}" alt="noproductfound">
                    {{ __('No result found. Please try a new search') }}
                </div>
            <% } %>
        </script>

        <script type="text/template" id="products_template_bid_ride">
            <% if(results != ''){ %>
                <div class="vehicle_view_bid_ride_container">
                    <div class="slider pr-2 pl-2">
                        <div class="row slick_bid_ride">
                            <div class="slick-items text-center">
                    <% _.each(results, function(result, key){%>
                                <a href="javascript:void(0)" class="vehical-view-box-bid-ride" data-totalTagPrice="<%= result.tags_price%>" data-totalMinTagPrice="<%= result.total_minimum%>" data-totalDistance="<%= result.distance%>" data-product_id="<%= result.id %>" data-productName="<%= result.name %>"><img src="<%= result.image_url %>">
                                <h5 class="m-0 text-center"><%= result.name %></h5>
                               <h6 class="m-0"><%= result.description %></h6>
                                </a>
                    <% }); %>
                            </div>
                        </div>
                    </div>
                    <div class="row recommendated_price mt-1">
                        <div class="col-6 text-center">
                            <div class="text-center">{{ __('Recommended Price') }} {{Session::get('currencySymbol')}}<span id="recommended_price_span"></span></div>
                            <div class="text-center">{{ __('Distance') }} <span id="recommended_distance_span"></span> {{ __('KM') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="input-get-value">
                                <div class="input-group">
                                    <span class="input-group-btn mr-10">
                                        <button type="button" class="btn btn-danger btn-price-up-down" data-type="minus">
                                            <i class="fa fa-minus" aria-hidden="true"></i> 10
                                        </button>
                                    </span>
                                    <input type="text" name="cab_bid_price" id="cab_bid_price" class="form-control price-number-up-down text-center" value="" min="" max="">
                                    <span class="input-group-btn ml-10">
                                        <button type="button" class="btn btn-success btn-price-up-down" data-type="plus">
                                            <i class="fa fa-plus" aria-hidden="true"></i> 10
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row pr-3 pl-3">
                        <div class="col-12 mt-2">
                            <span class="input-group-btn ml-10">
                                <button type="button" class="btn btn-solid w-100" id="bid_ride_now" data-rel="bid_ride_now" data-product_id="">
                                {{__('Bid Now')}}
                                </button>
                            </span>
                        </div>
                    </div>
                <div>
            <% }else{ %>
                <div class="col-12 vehicle-details text-center">
                    <img class="w-100" src="{{asset('assets/images/noproductfound.png')}}" alt="noproductfound">
                    {{ __('No result found. Please try a new search') }}
                </div>
            <% } %>
        </script>

        <script type="text/template" id="scheduleTime_template">
            <div class="scheduleTime">
                <select class="scheduleHour" onchange="checkScheduleDateTime(this)" ><option value="">HH</option><option value="1">01</option><option value="2">02</option><option value="3">03</option><option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option><option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option></select>
                <select class="scheduleMinute" onchange="checkScheduleDateTime(this)" ><option value="">MM</option><option value="0">00</option><option value="1">05</option><option value="2">10</option><option value="3">15</option><option value="4">20</option><option value="5">25</option><option value="6">30</option><option value="7">35</option><option value="8">40</option><option value="9">45</option><option value="10">50</option><option value="11">55</option></select>
                <select class="scheduleAmPm" onchange="checkScheduleDateTime(this)" ><option value="">AM/PM</option><option value="am">AM</option><option value="pm">PM</option></select>
            </div>
        </script>
                <script type="text/template" id="destination_location_template">
            <i class="fa fa-search destination-icon" aria-hidden="true"></i>
            <input class="form-control pickup-text" type="text" name="destination_location_name[]" placeholder="{{__('Add A Stop')}}" id="destination_location_<%= random_id %>" data-rel="<%= random_id %>"/>
            <input type="hidden" name="destination_location_latitude[]" value="" id="destination_location_latitude_<%= random_id %>" data-rel="<%= random_id %>"/>
            <input type="hidden" name="destination_location_longitude[]" value="" id="destination_location_longitude_<%= random_id %>" data-rel="<%= random_id %>"/>
        </script>
                <script type="text/template" id="destination_location_template_li">
            <li class="d-flex dots" id="dots_<%= random_id %>">
                <div class="title title-24 position-relative edit-other-stop" id="<%= random_id %>">  {{__('To')}} - <span id="dropoff-where-to-<%= random_id %>"></span><i class="fa fa-angle-down" aria-hidden="true"></i></div>
                <i class="fa fa-times ml-1 apremove" aria-hidden="true" data-rel="<%= random_id %>"></i>
            </li>
        </script>

        <script type="text/template" id="vehicle_bid_template">
            <div class="cab-outer style-4">
                <div class="bg-white p-2">
                    <a class="close-cab-detail-box" href="javascript:void()">✕</a>
                    <div class="cab-image-box w-100 d-flex align-items-center justify-content-center">
                        <img src="<%= result.image_url %>">
                    </div>
                    <div class="cab-location-details">
                    <div style="height:5px;"><div class="loader cab-detail-main-loader" style="display: none;"></div></div>


                    <h4 class="d-flex align-items-center justify-content-between"><b><%= result.name %></b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount">{{Session::get('currencySymbol')}}<%= result.tags_price%></b></label></h4>
                    <% if(result.toll_fee > 0){ %>
                        <span class="d-flex align-items-center justify-content-between mt-2"><b>{{ __('Toll Fee') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount_less_toll">{{Session::get('currencySymbol')}}<%= result.toll_fee%></b></label></span>
                    <% } %>

                    <% if(result.service_charge_amount > 0){ %>
                        <span class="d-flex align-items-center justify-content-between"><b>{{ __('Service Charge') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount_toll_fee">{{Session::get('currencySymbol')}}<%= result.service_charge_amount%></b></label></span>
                    <% } %>

                    <% if(result.service_charge_amount > 0 || result.toll_fee > 0){ %>
                        <h4 class="d-flex align-items-center justify-content-between"><b>{{ __('Total') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_total_amount">{{Session::get('currencySymbol')}}<%= (result.total_tags_price)%></b></label></h4>
                    <% } %>

                    </div>
                </div>
                <div class="cab-amount-details px-2">
                    <div class="row">
                        <div class="col-6 mb-2">{{__('Distance')}}</div>
                        <div class="col-6 mb-2 text-right" id="distance"><%= result.distance %> {{__($client_preference_detail->distance_unit_for_time)}}</div>
                        <div class="col-6 mb-2">{{__('Duration')}}</div>
                        <div class="col-6 mb-2 text-right" id="duration"><%= result.duration %> {{__('mins')}}</div>
                        <% if((result.subscription_percent_value) && (result.subscription_percent_value) > 0 ){ %>
                            <div class="col-6 mb-2">{{__('Subscription Discount')}}</div>
                            <div class="col-6 mb-2 text-right" id="subscription-percent"><%= result.subscription_percent_value+'%' %></div>
                            <input type="hidden" id="subscription-percent-h" value="<%= result.subscription_percent_value %>">
                            <div class="col-6 mb-2"><p class="total_amt m-0">{{__('Amount Payable')}}</p></div>
                            <div class="col-6 mb-2 text-right" id="discount"><p class="total_amt m-0" id="subscription-amout">{{Session::get('currencySymbol')}}<%= result.subscription_discount %></p></div>
                            <input type="hidden" id="subscription-amout-h" value="<%= result.subscription_discount %>">
                        <% } %>
                        <% if((result.loyalty_amount_saved) && (result.loyalty_amount_saved) > 0 ){ %>
                            <div class="col-6 mb-2">Loyalty</div>
                            <div class="col-6 mb-2 text-right">-{{Session::get('currencySymbol')}}<%= result.loyalty_amount_saved %></div>
                        <% } %>
                    </div>
                </div>
            </div>

            <div id="create_bid_btns">
                <div class="col-6 my-2 bid-btnleft">
                    <div class="input-get-value">
                        <div class="input-group">
                            <span class="input-group-btn mr-10">
                                <button type="button" class="btn btn-danger btn-price-up-down" data-type="minus">
                                    <i class="fa fa-minus" aria-hidden="true"></i> 10
                                </button>
                            </span>
                            <input type="number" name="cab_bid_price" value="<%= (result.total_tags_price)%>" id="cab_bid_price" class="form-control price-number-up-down text-center" min="<%= (result.min_tags_price)%>" max="">
                            <span class="input-group-btn ml-10">
                                <button type="button" class="btn btn-success btn-price-up-down" data-type="plus">
                                    <i class="fa fa-plus" aria-hidden="true"></i> 10
                                </button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 create-bid-btn">
                        <button class="btn btn-solid w-100" id="create_bid" data-product_id="<%= result.id %>" data-vendor_id="<%= result.vendor_id %>" data-amount="<%= result.original_tags_price%>" data-servicechargeamount="<%= result.service_charge_amount%>" data-totalamount="<%= (result.total_tags_price)%>" data-task_type="bid_ride_request" data-tags="<%=(result.tags)%>">{{__('Create Bid')}}</button>
                    </div>
                </div>
            </div>

            <div class="payment-promo-container p-2 d-none" id="paymentMethods">
                <input type="hidden" id="payment-method-for-bid" value="1">
                <h4 class="d-flex align-items-center justify-content-between mb-2 cab_payment_method_selection"  data-toggle="modal" data-target="#payment_modal_bid" type='bid'>
                    <span id="payment_type_bid">
                        <i class="fa fa-money" aria-hidden="true"></i> {{__('Cash')}}
                    </span>
                    <i class="fa fa-angle-down" aria-hidden="true"></i>
                </h4>
            </div>

            <div id="driver_acceptance_list" class="d-none">
                <div class="text-loader">
                    <i class="fa fa-circle-o-notch fa-spin"></i>
                    Wait for driver acceptance
                </div>
            </div>

            <span id="show_error_of_bid" class="text-danger"></span>

            {{-- <div class="payment-promo-container p-2">
                <h4 class="d-flex align-items-center justify-content-between mb-2 cab_payment_method_selection"  data-toggle="modal" data-target="#payment_modal">
                    <span id="payment_type">
                        <i class="fa fa-money" aria-hidden="true"></i> {{__('Cash')}}
                    </span>
                    <i class="fa fa-angle-down" aria-hidden="true"></i>
                </h4>
                <div class="row">
                    <div class="col-12">
                    <%
                    var payableAmout = '';
                    if((result.subscription_percent_value) && (result.subscription_percent_value) > 0 ){
                        payableAmout = result.subscription_discount;
                    }
                    %>
                        <input type="hidden" id="stripe_token" name="stripe_token" value="">
                        <button disabled class="btn btn-solid w-100" id="pickup_now" data-payment_method="1" data-product_id="<%= result.id %>" data-coupon_id =""  data-subscriptionPayableAmount ="<%= payableAmout %>" data-vendor_id="<%= result.vendor_id %>" data-amount="<%= result.original_tags_price%>" data-tollamount="<%= result.toll_fee%>" data-servicechargeamount="<%= result.service_charge_amount%>" data-totalamount="<%= (result.total_tags_price)%>" data-image="<%= result.image_url %>" data-rel="pickup_now" data-task_type="now">{{__('Book Now')}}</button>
                    </div>
                </div>
            </div> --}}
        </script>

        <script type="text/template" id="particular_driver_template">
            <div class="cab-outer style-4">
                <div class="bg-white p-2">
                    <a class="close-cab-detail-box" href="javascript:void()">✕</a>
                    <div class="cab-image-box w-100 d-flex align-items-center justify-content-center">
                        <img src="<%= result.image_url %>">
                    </div>
                    <div class="cab-location-details">
                    <div style="height:5px;"><div class="loader cab-detail-main-loader" style="display: none;"></div></div>


                    <h4 class="d-flex align-items-center justify-content-between"><b><%= result.name %></b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount">{{Session::get('currencySymbol')}}<%= result.tags_price%></b></label></h4>
                    <% if(result.toll_fee > 0){ %>
                        <span class="d-flex align-items-center justify-content-between mt-2"><b>{{ __('Toll Fee') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount_less_toll">{{Session::get('currencySymbol')}}<%= result.toll_fee%></b></label></span>
                    <% } %>

                    <% if(result.service_charge_amount > 0){ %>
                        <span class="d-flex align-items-center justify-content-between"><b>{{ __('Service Charge') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount_toll_fee">{{Session::get('currencySymbol')}}<%= result.service_charge_amount%></b></label></span>
                    <% } %>

                    <% if(result.service_charge_amount > 0 || result.toll_fee > 0){ %>
                        <h4 class="d-flex align-items-center justify-content-between"><b>{{ __('Total') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_total_amount">{{Session::get('currencySymbol')}}<%= (result.total_tags_price)%></b></label></h4>
                    <% } %>

                    <input type="hidden" id="stripe_token" value=""/>
                    <input type="hidden" id="hddn_amount_toll_fee" value="<%= (result.toll_fee)%>"/>
                    <input type="hidden" name="cart_product_ids[]" value="<%= result.id %>">
                    <input type="hidden" id="hddn_real_amount" value="<%= (result.tags_price)%>"/>
                    <input type="hidden" id="hddn_service_charge_amount" value="<%= (result.service_charge_amount)%>"/>
                    <input type="hidden" id="hddn_currency_symbol" value="{{Session::get('currencySymbol')}}"/>

                    </div>
                </div>
                <div class="cab-amount-details px-2">
                    <div class="row">
                        <div class="col-6 mb-2">{{__('Distance')}}</div>
                        <div class="col-6 mb-2 text-right" id="distance"><%= result.distance %> {{__($client_preference_detail->distance_unit_for_time)}}</div>
                        <div class="col-6 mb-2">{{__('Duration')}}</div>
                        <div class="col-6 mb-2 text-right" id="duration"><%= result.duration %> {{__('mins')}}</div>
                        <% if((result.subscription_percent_value) && (result.subscription_percent_value) > 0 ){ %>
                            <div class="col-6 mb-2">{{__('Subscription Discount')}}</div>
                            <div class="col-6 mb-2 text-right" id="subscription-percent"><%= result.subscription_percent_value+'%' %></div>
                            <input type="hidden" id="subscription-percent-h" value="<%= result.subscription_percent_value %>">
                            <div class="col-6 mb-2"><p class="total_amt m-0">{{__('Amount Payable')}}</p></div>
                            <div class="col-6 mb-2 text-right" id="discount"><p class="total_amt m-0" id="subscription-amout">{{Session::get('currencySymbol')}}<%= result.subscription_discount %></p></div>
                            <input type="hidden" id="subscription-amout-h" value="<%= result.subscription_discount %>">
                        <% } %>
                        <% if((result.loyalty_amount_saved) && (result.loyalty_amount_saved) > 0 ){ %>
                            <div class="col-6 mb-2">Loyalty</div>
                            <div class="col-6 mb-2 text-right">-{{Session::get('currencySymbol')}}<%= result.loyalty_amount_saved %></div>
                        <% } %>
                    </div>
                </div>
            </div>

            <div id="">
                <div class="col-12 my-2">
                    <div class="w-100 d-flex justify-content-between align-items-center input-get-value">
                        <div class="input-group col">
                            <input type="text" name="driver_unique_id" value="" id="driver_unique_id" class="form-control text-center" placeholder="Enter Driver Unique Id" required>
                        </div>
                        <div class="input-group col">
                            <input type="datetime-local" name="schedule_date_for_driver" value="{{ date("Y-m-d H:i") }}" id="schedule_date_for_driver" class="form-control text-center" min="{{ date("Y-m-d H:i") }}" required>
                        </div>

                    </div>
                <span id="driver_request_error" class="text-danger"> </span>
                </div>

                <div class="payment-promo-container p-2" id="paymentMethods">
                    <input type="hidden" id="payment-method-for-bid" value="1">
                    <h4 class="d-flex align-items-center justify-content-between mb-2 cab_payment_method_selection"  data-toggle="modal" data-target="#payment_modal_bid" type='bid'>
                        <span id="payment_type_bid">
                            <i class="fa fa-money" aria-hidden="true"></i> {{__('Cash')}}
                        </span>
                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                    </h4>
                </div>

                <div class="row">
                    <div class="col-md-12 create-bid-btn">
                        <button class="btn btn-solid w-100" id="pickup_now" data-payment_method="1" data-product_id="<%= result.id %>" data-vendor_id="<%= result.vendor_id %>" data-amount="<%= result.original_tags_price%>" data-servicechargeamount="<%= result.service_charge_amount%>" data-totalamount="<%= (result.total_tags_price)%>" data-task_type="schedule" booking-type='driver_request' data-tags="<%=(result.tags)%>">{{__('Request For Driver')}}</button>
                    </div>
                </div>
            </div>

            <span id="show_errors" class="text-danger"></span>

        </script>

        <script type="text/template" id="driver_biding_list">
            <div>
                <% _.each(results, function(result, key){%>

                    <div class="driver_info">
                        <img src="<%=result.driver_image%>" alt="">
                        <div class="user-info">
                            <h4><%=result.driver_name%></h4>
                            <p>Price<span>{{Session::get('currencySymbol')}} <%=parseFloat(result.bid_price).toFixed(2)%></span></p>
                            {{-- <button class="btn-solid btn" type="button" id="accept_driver_bid" data-bid_id="<%=result.id%>">Accept</button> --}}
                            <button class="btn btn-solid w-100" id="pickup_now_bid" data-payment_method="1" data-product_id="<%= product_id %>" data-driver_id="<%= result.driver_id %>" data-bid_id="<%=result.id%>" data-coupon_id =""  data-subscriptionPayableAmount ="" data-vendor_id="<%= vendor_id %>" data-amount="<%= result.bid_price%>" data-tollamount="<%= result.toll_fee%>" data-servicechargeamount="<%= result.service_charge_amount%>" data-totalamount="<%= (result.total_tags_price)%>" data-image="<%= result.image_url %>" data-rel="pickup_now" data-task_type="now" booking-type="bid">{{__('Accept')}}</button>

                        </div>
                    </div>

                <% }); %>
            </div>
        </script>

                <script type="text/template" id="cab_detail_box_template">
            <div class="cab-outer style-4">
                <div class="bg-white p-2">
                    <a class="close-cab-detail-box" href="javascript:void()">✕</a>
                    <div class="cab-image-box w-100 d-flex align-items-center justify-content-center">
                        <img src="<%= result.image_url %>">
                    </div>
                    <div class="cab-location-details">
                    <div style="height:5px;"><div class="loader cab-detail-main-loader" style="display: none;"></div></div>
                    @if($is_cab_pooling == 1)
                    <div class="show_no_of_seats_if_pooling" style="display:none;">
                        <div class="row mt-2">
                            <div class="col-md-7">
                                <div class="number_seats">
                                    <h5>{{ __('Number Of Seats') }}</h5>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-get-value">
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-danger btn-number-up-down" data-type="minus">
                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                        <input type="text" name="no_seats_for_pooling" id="no_seats_for_pooling" class="form-control seats-number-up-down text-center" value="<%= result.no_seats_for_pooling%>" min="1" max="<%= result.seats_for_booking%>">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-success btn-number-up-down" data-type="plus">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <h4 class="d-flex align-items-center justify-content-between"><b><%= result.name %></b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount">{{Session::get('currencySymbol')}}<%= result.tags_price%></b></label></h4>
                    <% if(result.toll_fee > 0){ %>
                        <span class="d-flex align-items-center justify-content-between mt-2"><b>{{ __('Toll Fee') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount_less_toll">{{Session::get('currencySymbol')}}<%= result.toll_fee%></b></label></span>
                    <% } %>
                    <% if((result.loyalty_amount_saved) && (result.loyalty_amount_saved) > 0 ){ %>
                        <span class="d-flex align-items-center justify-content-between"><b>{{ __('Loyalty') }}</b> <label><sub class="ling-throgh" id
                            ="discount_amount" style="display:none;"></sub> <b id="real_amount_toll_fee">- {{Session::get('currencySymbol')}}<%= result.loyalty_amount_saved%></b></label></span>
                    <% } %>
                    <% if(result.service_charge_amount > 0){ %>
                        <span class="d-flex align-items-center justify-content-between"><b>{{ __('Service Charge') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_amount_toll_fee">{{Session::get('currencySymbol')}}<%= result.service_charge_amount%></b></label></span>
                    <% } %>
                    <% if((result.loyalty_amount_saved) && (result.loyalty_amount_saved) > 0 ){ %>
                        <span class="d-flex align-items-center justify-content-between"><b>{{ __('Loyalty') }}</b> <label><sub class="ling-throgh" id
                            ="discount_amount" style="display:none;"></sub> <b id="real_amount_toll_fee">- {{Session::get('currencySymbol')}}<%= result.loyalty_amount_saved%></b></label></span>
                    <% } %>
                    <% if((result.product_tax) && (result.product_tax) > 0 ){ %>
                        <span class="d-flex align-items-center justify-content-between"><b><%= result.product_tax_name %></b> <label><sub class="ling-throgh" id
                            ="discount_amount" style="display:none;"></sub> <b id="real_amount_toll_fee">{{Session::get('currencySymbol')}}<%= result.total_other_taxes %></b></label></span>
                    <% } %>

                    <% if(result.wallet_amount_used > 0){ %>
                        <span class="d-flex align-items-center justify-content-between mt-2"><b>{{ __('Wallet amount used') }}</b> <label><sub class="ling-throgh"
                            style="display:none;"></sub> <b>-{{Session::get('currencySymbol')}}<%= result.wallet_amount_used%></b></label></span>
                    <% } %>
                    <% if(result.service_charge_amount > 0 || result.toll_fee > 0 || result.wallet_amount_used > 0){ %>
                        <h4 class="d-flex align-items-center justify-content-between"><b>{{ __('Total') }}</b> <label><sub class="ling-throgh" id
                        ="discount_amount" style="display:none;"></sub> <b id="real_total_amount">{{Session::get('currencySymbol')}}<%= (result.total_tags_price)%></b></label></h4>
                    <% } %>
                        <input type="hidden" id="hddn_amount_toll_fee" value="<%= (result.toll_fee)%>"/>
                        <input type="hidden" name="cart_product_ids[]" value="<%= result.id %>">
                        <input type="hidden" id="hddn_real_amount" value="<%= (result.tags_price)%>"/>
                        <input type="hidden" id="hddn_currency_symbol" value="{{Session::get('currencySymbol')}}"/>
                        <input type="hidden" id="hddn_service_charge_amount" value="<%= (result.service_charge_amount)%>"/>
                    </div>
                </div>
                <div class="cab-amount-details px-2">
                    <div class="row">
                        <div class="col-6 mb-2">{{__('Distance')}}</div>
                        <div class="col-6 mb-2 text-right" id="distance"><%= result.distance %> {{__($client_preference_detail->distance_unit_for_time)}}</div>
                        <div class="col-6 mb-2">{{__('Duration')}}</div>
                        <div class="col-6 mb-2 text-right" id="duration"><%= result.duration %> {{__('mins')}}</div>
                        <% if((result.subscription_percent_value) && (result.subscription_percent_value) > 0 ){ %>
                        <div class="col-6 mb-2">{{__('Subscription Discount')}}</div>
                        <div class="col-6 mb-2 text-right" id="subscription-percent"><%= result.subscription_percent_value+'%' %></div>
                        <input type="hidden" id="subscription-percent-h" value="<%= result.subscription_percent_value %>">
                        <div class="col-6 mb-2"><p class="total_amt m-0">{{__('Amount Payable')}}</p></div>
                        <div class="col-6 mb-2 text-right" id="discount"><p class="total_amt m-0" id="subscription-amout">{{Session::get('currencySymbol')}}<%= result.subscription_discount %></p></div>
                        <input type="hidden" id="subscription-amout-h" value="<%= result.subscription_discount %>">
                        <% } %>

                        <% if((result.yacht)){ %>
                            {{-- <input type="number" class="from-control" id="seats" value="" name="seats" placeholder="Number of Seats Booking"> --}}
                        <% } %>

                        <% if((result.loyalty_amount_saved) && (result.loyalty_amount_saved) > 0 ){ %>
                            <div class="col-6 mb-2">Loyalty</div>
                            <div class="col-6 mb-2 text-right">-{{Session::get('currencySymbol')}}<%= result.loyalty_amount_saved %></div>
                        <% } %>
                    </div>
                </div>
                <div class="coupon_box d-flex w-100 py-2 align-items-center justify-content-between">
                    <label class="mb-0 ml-1">
                <span class="code-text">{{__('Select a promo code')}}</span>
            </label>

                    <a href="javascript:void(0)" class="ml-1" data-product_id="<%= result.id %>"  data-vendor_id="<%= result.vendor_id %>" data-amount="<%= result.tags_price%>" data-tollamount="<%= result.toll_fee%>" data-servicechargeamount="<%= result.service_charge_amount%>" id="promo_code_list_btn_cab_booking">Apply</a>
                    <a class="remove-coupon" href="javascript:void(0)" id="remove_promo_code_cab_booking_btn" data-product_id="<%= result.id %>" data-vendor_id="<%= result.vendor_id %>" data-amount="<%= result.tags_price%>" data-tollamount="<%= result.toll_fee%>" data-servicechargeamount="<%= result.service_charge_amount%>" style="display:none;">Remove</a>

        </div>
        <% if((result.faqlist) && (result.faqlist) > 0 ){ %>
        <div class="text-center my-3 btn-product-order-form-div">
            <button class="clproduct_order_form btn btn-solid w-100"  id="add_product_order_form"  data-product_id="<%= result.id %>" data-vendor_id="<%= result.vendor_id %>" >{{__('Product Order Form')}}</button>
        </div>
        <% } %>
        <div class="form-group pmd-textfield pmd-textfield-floating-label" style="display:none;" id="schedule_datetime_main_div">
            <label class="control-label" for="datetimepicker-default">{{__('Select Date and Time')}}</label>
            <input type="datetime-local" id="schedule_datetime" class="form-control" placeholder="Inline calendar" value="">
        </div>
        <div class="for_friend_fields_div px-2 py-2">
            <input type="hidden" name="friendName" value="<%= result.friend_name %>">
            <input type="hidden" name="friendPhoneNumber" value="<%= result.friend_phone_name %>">
        </div>

    </div>
    <span id="show_error_of_booking" class="error"></span>

    <div class="payment-promo-container p-2">
    <% if(result.tags_price > 0){ %>
        <h4 class="d-flex align-items-center justify-content-between mb-2 cab_payment_method_selection"  data-toggle="modal" data-target="#payment_modal">
            <span id="payment_type">
                <i class="fa fa-money" aria-hidden="true"></i> {{__('Cash')}}
            </span>
            <i class="fa fa-angle-down" aria-hidden="true"></i>
        </h4>
    <% } %>
        <div class="row">
            <div class="col-12">
            <%
            var payableAmout = '';
            if((result.subscription_percent_value) && (result.subscription_percent_value) > 0 ){
                payableAmout = result.subscription_discount;
            }
            %>
                <input type="hidden" id="stripe_token" name="stripe_token" value="">
                <button class="btn btn-solid w-100" id="pickup_now" data-payment_method="1" data-product_id="<%= result.id %>" data-coupon_id =""  data-subscriptionPayableAmount ="<%= payableAmout %>" data-vendor_id="<%= result.vendor_id %>" data-amount="<%= result.original_tags_price%>" data-tollamount="<%= result.toll_fee%>" data-servicechargeamount="<%= result.service_charge_amount%>" data-totalamount="<%= (result.total_tags_price)%>" data-image="<%= result.image_url %>" data-rel="pickup_now" data-task_type="now">{{__('Book Now')}}</button>
            </div>
            <!--<div class="col-6">
                <button class="btn btn-solid w-100" id="pickup_later" data-payment_method="1" data-product_id="<%= result.id %>" data-coupon_id ="" data-vendor_id="<%= result.vendor_id %>" data-amount="<%= result.original_tags_price%>" data-image="<%= result.image_url %>" data-rel="pickup_later">Pickup Later</button>
            </div>-->
        </div>
    </div>
</script>

                <script type="text/template" id="payment_methods_template">
    <% if(payment_options != '') { %>
        <form method="POST" id="cab_payment_method_form">
            @csrf
            @method('POST')
            <% _.each(payment_options, function(payment_option, k){%>
                <div>
                    <label class="radio mt-2">
                        <span><%= payment_option.title %></span>
                        <input type="radio" class="select_cab_payment_method" name="select_cab_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.id %>" data-payment_method="<%= payment_option.id %>">
                        <span class="checkround"></span>
                    </label>
                    <% if(payment_option.code == 'stripe') { %>
                        <div class="col-md-12 mt-3 mb-3 stripe_element_wrapper d-none">
                            <div class="form-control">
                                <label class="d-flex flex-row pt-1 pb-1 mb-0">
                                    <div id="stripe-card-element"></div>
                                </label>
                            </div>
                            <span class="error text-danger" id="stripe_card_error"></span>
                        </div>
                    <% } %>
                    <% if(payment_option.slug == 'yoco') { %>
                        <div class="col-md-12 mt-3 mb-3 yoco_element_wrapper d-none">
                            <div class="form-control">
                                <div id="yoco-card-frame">
                                <!-- Yoco Inline form will be added here -->
                                </div>
                            </div>
                            <span class="error text-danger" id="yoco_card_error"></span>
                        </div>
                    <% } %>

                    <% if(payment_option.slug == 'powertrans') { %>
                        <div class="col-md-12 mt-3 mb-3 powertrans_element_wrapper option-wrapper d-none">
                            <div class="row no-gutters">
                                <div class="col-6">
                                    <input type="number" min="16" maxlength="16" style=" border-right: none;" class="form-control" id="card-element-powertrans" placeholder="Enter card Number" required
                                    oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                                </div>
                                <div class="col-3">
                                    <input type="number" style=" border-left: none; border-right: none;" class="form-control" maxLength="4"  id="date-element-powertrans" placeholder="YYMM" required
                                    oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"/>
                                </div>
                                <div class="col-3">
                                    <input type="password" maxLength="4" style=" border-left: none;"  class="form-control" id="cvv-element-powertrans" placeholder="CVV" required />
                                </div>
                            </div>

                            <span class="error text-danger" id="card_error_powertrans"></span>
                        </div>
                    <% } %>

                </div>
            <% }); %>

            {{-- <div>
                <label class="radio mt-2">
                    <span>{{__('Wallet/Card')}}</span>
                    <input type="radio" class="select_cab_payment_method" name="select_cab_payment_method" id="radio-wallet" value="2" data-payment_method="2">
                    <span class="checkround"></span>
                </label>
            </div> --}}
            <div class="modal-footer d-block text-center">
                <div class="row">
                    <div class="col-sm-12 p-0 d-flex flex-fill">
                        <button type="button" class="btn btn-solid ml-1 select_payment_option_done" data-type="<%= type %>">{{__('Done')}}</button>
                    </div>
                </div>
            </div>
        </form>
    <% } %>
</script>

                <script type="text/template" id="cab_booking_promo_code_template">
    <% _.each(promo_codes, function(promo_code, key){%>
        <div class="col-12 mt-2">
            <div class="coupon-code mt-0">
                <div class="p-2">
                    <img src="<%= promo_code.image.image_fit %>100/35<%= promo_code.image.image_path %>" alt="">
                    <h6 class="mt-0"><%= promo_code.title %></h6>
                </div>
                <hr class="m-0">
                <div class="code-outer p-2 text-uppercase d-flex align-items-center justify-content-between">
                    <label class="m-0"><%= promo_code.name %></label>
                    <a class="btn btn-solid cab_booking_apply_promo_code_btn" data-vendor_id="<%= vendor_id %>" data-coupon_id="<%= promo_code.id %>" data-product_id="<%= product_id %>" data-amount="<%= amount %>" style="cursor: pointer;">Apply</a>
                </div>
                <hr class="m-0">
                <div class="offer-text p-2">
                    <p class="m-0"><%= promo_code.short_desc %></p>
                </div>
            </div>
        </div>
    <% }); %>
</script>

                <script type="text/template" id="order_success_template">
    <div class="bg-white p-2">
        <div class="w-100 h-100">
            <img src="<%= product_image %>" alt="">
        </div>
        <div class="cab-location-details" id="searching_main_div">
            <h4><b>{{__(getNomenclatureName('Searching For Nearby Drivers',true))}}</b></h4>
            <div class="new-loader"></div>
        </div>
        <div class="cab-location-details" id="driver_details_main_div" style="display:none;">
           <div class="row align-items-center">

                <div class="col-4">
                   <div class="taxi-img">
                       <img src="" id="driver_image">
                   </div>
                </div>

                <div class="col-8" >
                    <h4 id="driver_name"><b><%= result.user_name %></b></h4>
                    <p class="mb-0" id="driver_phone_number"><%= result.phone_number %></p>
                </div>

           </div>
        </div>
    </div>
    <div class="cab-amount-details px-2">
        <div class="row">
            <div class="col-6 mb-2">{{__('ETA')}}</div>
            <div class="col-6 mb-2 text-right" id="distance">--</div>
            <div class="col-6 mb-2">{{__('Order ID')}}</div>
            <div class="col-6 mb-2 text-right" id=""><%= result.order_number %></div>
            <div class="col-6 mb-2">{{__('Amount Paid')}}</div>
            <div class="col-6 mb-2 text-right">$<%= result.total_amount %></div>
        </div>
    </div>
</script>

                <div class="cab-detail-box style-4 d-none" id="cab_detail_box"></div>
                <div class="promo-box style-4 d-none">
                    <a class="d-block mt-2 close-promo-code-detail-box" href="javascript:void(0)">✕</a>
                    <div class="row" id="cab_booking_promo_code_list_main_div">

                    </div>
                </div>
        </div>

    </div>

</section>

<!-- Plugandpay Modal -->
<div class="modal fade payment-modal payment-modal-width" id="plugpaymethod" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="plugpaymethodLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="payment_modalLabel">{{__('PlugPay Credit Card')}}</h5>
                <button type="button" class="close right-top" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body booking_mayment_method">
                <div class="col-md-12 mt-3 mb-3 plugnpay_element_wrapper option-wrapper">
                    <div class="row no-gutters">
                        <div class="col-6">
                            <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="plugnpay-card-element" placeholder="Enter card Number" required />
                        </div>
                        <div class="col-3">
                            <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="5"  id="plugnpay-date-element" placeholder="MM/YY" required />
                        </div>
                        <div class="col-3">
                            <input type="password" max="3" style=" border-left: none;"  class="form-control" id="plugnpay-cvv-element" placeholder="CVV" required />
                        </div>
                    </div>

                    <span class="error text-danger" id="plugnpay_card_error"></span>
                    <a class="btn btn-solid w-100 mt-2" id="paywithplugpay">Pay
                        <img style="width:5%; display:none;" id="proceed_to_pay_loader" src="{{asset('assets/images/loader.gif')}}">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Plugandpay Modal -->
<div class="modal fade payment-modal payment-modal-width" id="azulpaymethod" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="azulpaymethodLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="payment_modalLabel">{{__('AzulPay Credit Card')}}</h5>
                <button type="button" class="close right-top" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body booking_mayment_method">
                <div class="col-md-12 mt-3 mb-3 azulpay_element_wrapper option-wrapper">

                         <div class="tab">
    <a class="tablinks active" onclick="clickHandle(event, 'Add-Card')" href="javascript:void(0);">Add Card</a>
    <a class="tablinks" onclick="clickHandle(event, 'Card-List')" href="javascript:void(0);">Card List</a>
  </div>

  <div id="Add-Card" class="tabcontent show" style="display:block">
     <div class="row no-gutters">
                            <div class="col-6">
                                <input type="text"  maxlength="16" style=" border-right: none;" class="form-control demoInputBox" id="azul-card-element" placeholder="Enter Card Number" />
                            </div>
                            <div class="col-3">
                                <input type="text" style=" border-left: none; border-right: none;" class="form-control demoInputBox" onkeyup="addSlashes(this)" maxlength=7  id="azul-date-element" placeholder="MM/YYYY" />
                            </div>
                            <div class="col-3">
                                <input type="password" max="4" style=" border-left: none;"  class="form-control demoInputBox" id="azul-cvv-element" placeholder="CVV" />
                            </div>
                        </div>
                        <div class="row">
<div class="col-md-4 save-card-custom">
                     <input type="checkbox" name="save_card" class="form-check-input" id="azul-save_card" value="1">
                                    <label for="azul-save_card" class="">{{ __('Save Card') }}</label>
            </div>
</div>
                        <span class="error text-danger" id="azul_card_error"></span>
  </div>
  <div id="Card-List" class="tabcontent">
  </div>
                    </div>
                    <a class="btn btn-solid w-100 mt-2" id="paywithazulpay">Pay
                        <img style="width:5%; display:none;" id="proceed_to_azulpay_loader" src="{{asset('assets/images/loader.gif')}}">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Select Payment Option -->
<div class="modal fade select-payment-option payment-modal-width" id="select_payment_option" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="select_payment_optionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="select_payment_optionLabel">{{__('Choose payment method')}}</h5>
                <button type="button" class="close right-top" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <h4 class="d-flex  justify-content-between mb-2 mt-3 select_cab_payment_methodx"><span ><i class="fa fa-money mr-3" aria-hidden="true"></i> {{__('Cash')}}</span></h4>
            </div>
        </div>
    </div>
 </div>

    <!-- Paymentoption Modal -->
    <div class="modal fade payment-modal payment-modal-width" id="payment_modal" data-backdrop="static"
        data-keyboard="false" tabindex="-1" aria-labelledby="payment_modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header pb-0">
                    <h5 class="modal-title" id="payment_modalLabel">{{ __('Select Payment Method') }}</h5>
                    <button type="button" class="close right-top" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body booking_mayment_method">
                    {{-- <h4 class="d-flex align-items-center justify-content-between mb-2 mt-3 px-3 select_cab_payment_method" data-payment_method="1"><span><i class="fa fa-money mr-3" aria-hidden="true"></i> {{__('Cash')}}</span></h4>
                <h4 class="d-flex align-items-center justify-content-between mb-2 mt-3 px-3 select_cab_payment_method" data-payment_method="2"><span><i class="fa fa-money mr-3" aria-hidden="true"></i> {{__('Wallet/Card')}}</span></h4> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade payment-modal payment-modal-width" id="payment_modal_bid" data-backdrop="static"
    data-keyboard="false" tabindex="-1" aria-labelledby="payment_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="payment_modalLabel">{{ __('Select Payment Method') }}</h5>
                <button type="button" class="close right-top" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body booking_mayment_method">
                {{-- <h4 class="d-flex align-items-center justify-content-between mb-2 mt-3 px-3 select_cab_payment_method" data-payment_method="1"><span><i class="fa fa-money mr-3" aria-hidden="true"></i> {{__('Cash')}}</span></h4>
            <h4 class="d-flex align-items-center justify-content-between mb-2 mt-3 px-3 select_cab_payment_method" data-payment_method="2"><span><i class="fa fa-money mr-3" aria-hidden="true"></i> {{__('Wallet/Card')}}</span></h4> --}}
            </div>
        </div>
    </div>
</div>
    <!-- Select Payment Option -->
    <div class="modal fade select-payment-option payment-modal-width" id="select_payment_option" data-backdrop="static"
        data-keyboard="false" tabindex="-1" aria-labelledby="select_payment_optionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="select_payment_optionLabel">{{ __('Choose payment method') }}</h5>
                    <button type="button" class="close right-top" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 class="d-flex  justify-content-between mb-2 mt-3 select_cab_payment_methodx"><span><i class="fa fa-money mr-3" aria-hidden="true"></i> {{ __('Cash') }}</span></h4>
                </div>
            </div>
        </div>
    </div>
    <!-- topup wallet -->
    <div class="modal fade" id="topup_wallet" tabindex="-1" aria-labelledby="topup_walletLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title text-17 mb-0 mt-0" id="topup_walletLabel">{{ __('Available Balance') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" id="wallet_topup_form">
                    @csrf
                    @method('POST')
                    <div class="modal-body pb-0">
                        <div class="form-group">
                            <div class="text-36">{{ Session::get('currencySymbol') }}<span
                                    class="wallet_balance">{{ decimal_format(Auth::user()->balanceFloat * (isset($clientCurrency->doller_compare) ? $clientCurrency->doller_compare : 1)) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <h5 class="text-17 mb-2">{{ __('Topup Wallet') }}</h5>
                        </div>
                        <div class="form-group">
                            <label for="wallet_amount">{{ __('Amount') }}</label>
                            <input class="form-control" name="wallet_amount" id="wallet_amount" type="text"
                                placeholder="Enter Amount">
                            <span class="error-msg" id="wallet_amount_error"></span>
                        </div>
                        <div class="form-group">
                            <div><label for="custom_amount">{{ __('Recommended') }}</label></div>
                            <button type="button" class="btn btn-solid mb-2 custom_amount">+10</button>
                            <button type="button" class="btn btn-solid mb-2 custom_amount">+20</button>
                            <button type="button" class="btn btn-solid mb-2 custom_amount">+50</button>
                        </div>
                        <hr class="mt-0 mb-1" />
                        <div class="payment_response">
                            <div class="alert p-0 m-0" role="alert"></div>
                        </div>
                        <h5 class="text-17 mb-2">{{ __('Debit From') }}</h5>
                        <div class="form-group" id="wallet_payment_methods">
                        </div>
                        <span class="error-msg" id="wallet_payment_methods_error"></span>
                    </div>
                    <div class="modal-footer d-block text-center">
                        <div class="row">
                            <div class="col-sm-12 p-0 d-flex justify-space-around">
                                <button type="button"
                                    class="btn btn-block btn-solid mr-1 mt-2 topup_wallet_confirm">{{ __('Topup Wallet') }}</button>
                                <button type="button" class="btn btn-block btn-solid ml-1 mt-2"
                                    data-dismiss="modal">{{ __('Cancel') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script type="text/template" id="payment_method_template">
    <% if(payment_options == '') { %>
        <h6>{{__('Payment Options Not Avaialable')}}</h6>
    <% }else{ %>
        <% _.each(payment_options, function(payment_option, k){%>

            <% if( (payment_option.slug != 'cash_on_delivery') && (payment_option.slug != 'loyalty_points') ) { %>
                <label class="radio mt-2">
                    <%= payment_option.title %>
                    <input type="radio" name="wallet_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.slug %>" data-payment_option_id="<%= payment_option.id %>">
                    <span class="checkround"></span>
                </label>
                <% if(payment_option.slug == 'stripe') { %>
                    <div class="col-md-12 mt-3 mb-3 stripe_element_wrapper d-none">
                        <div class="form-control">
                            <label class="d-flex flex-row pt-1 pb-1 mb-0">
                                <div id="stripe-card-element2"></div>
                            </label>
                        </div>
                        <span class="error text-danger" id="stripe_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'payphone') { %>
                    <div id="pp-button"></div>
                <% } %>

                <% if(payment_option.slug == 'plugnpay') { %>
                    <div class="col-md-12 mt-3 mb-3 plugnpay_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="plugnpay-card-element" placeholder="Enter card Number" required />
                            </div>
                            <div class="col-3">
                                <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="5"  id="plugnpay-date-element" placeholder="MM/YY" required />
                            </div>
                            <div class="col-3">
                                <input type="password" max="3" style=" border-left: none;"  class="form-control" id="plugnpay-cvv-element" placeholder="CVV" required />
                            </div>
                        </div>

                        <span class="error text-danger" id="plugnpay_card_error"></span>
                    </div>
                <% } %> <% if(payment_option.slug == 'azulpay') { %>
                    <div class="col-md-12 mt-3 mb-3 azulpay_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="azul-card-element" placeholder="Enter card Number" required />
                            </div>
                            <div class="col-3">
                                <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="6"  id="azul-date-element" placeholder="YYYYMM" required />
                            </div>
                            <div class="col-3">
                                <input type="password" max="4" style=" border-left: none;"  class="form-control" id="azul-cvv-element" placeholder="CVV" required />
                            </div>
                        </div>

                        <span class="error text-danger" id="azul_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'azulpay') { %>
                    <div class="col-md-12 mt-3 mb-3 azulpay_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="azul-card-element" placeholder="Enter card Number" required />
                            </div>
                            <div class="col-3">
                                <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="6"  id="azul-date-element" placeholder="YYYYMM" required />
                            </div>
                            <div class="col-3">
                                <input type="password" max="4" style=" border-left: none;"  class="form-control" id="azul-cvv-element" placeholder="CVV" required />
                            </div>
                        </div>

                        <span class="error text-danger" id="azul_card_error"></span>
                    </div>
                <% } %>

                <% if(payment_option.slug == 'powertrans') { %>
                    <div class="col-md-12 mt-3 mb-3 powertrans_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="number" min="16" maxlength="16" style=" border-right: none;" class="form-control" id="card-element-powertrans" placeholder="Enter card Number" required
                                oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                            </div>
                            <div class="col-3">
                                <input type="number" style=" border-left: none; border-right: none;" class="form-control" maxLength="4"  id="date-element-powertrans" placeholder="YYMM" required
                                oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"/>
                            </div>
                            <div class="col-3">
                                <input type="password" maxLength="4" style=" border-left: none;"  class="form-control" id="cvv-element-powertrans" placeholder="CVV" required />
                            </div>
                        </div>

                        <span class="error text-danger" id="card_error_powertrans"></span>
                    </div>
                <% } %>

            <% } %>
        <% }); %>
    <% } %>
</script>


    <!-- end topup wallet -->
    <!-- modal for product order form -->
    <div class="modal fade product-order-form
" id="product_order_form" tabindex="-1"
        aria-labelledby="product_order_formLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div id="product-order-form-modal">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end modal for product order form -->
    <!--Start Add Rider Modal -->
    <div class="modal fade" id="alAddRiderSecModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Add Rider') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add_rider_form" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="firstname" class="col-form-label">{{ __('First Name') }}:</label>
                            <input type="text" class="form-control" name="first_name">
                        </div>
                        <div class="form-group">
                            <label for="lastname" class="col-form-label">{{ __('Last Name') }}:</label>
                            <input type="text" class="form-control" name="last_name">
                        </div>
                        <div class="form-group">
                            <label for="phonenumber" class="col-form-label">{{ __('Phone Number') }}:</label>
                            <input type="tel" class="form-control phone @error('phone_number') is-invalid @enderror"
                                id="phone" placeholder="Phone Number" name="phone_number"
                                value="{{ old('phone_number') }}" autofocus>
                            <input type="hidden" id="countryData" name="countryData"
                                value="{{ strtolower(Session::get('default_country_code', 'US')) }}">
                            <input type="hidden" id="dialCode" name="dial_code"
                                value="{{ old('dialCode') ? old('dialCode') : Session::get('default_country_phonecode', '1') }}">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-solid add_rider_submit_button">Add</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End add rider modal -->
@endsection

@section('script')
<script src="{{asset('js/credit-card-validator.js')}}"></script>
    <script src="{{ asset('assets/js/intlTelInput.js') }}"></script>
    <script src="{{ asset('js/pick_drop.js') }}"></script>
    <script type="text/javascript">
        $('.iti__country').click(function() {
            var code = $(this).attr('data-country-code');
            $('#countryData').val(code);
            var dial_code = $(this).attr('data-dial-code');
            $('#dial_code').val(dial_code);
        });
    </script>

    @if (in_array('stripe', $client_payment_options) ||
        in_array('stripe_fpx', $client_payment_options) ||
        in_array('stripe_oxxo', $client_payment_options) ||
        in_array('stripe_ideal', $client_payment_options))
        <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    @endif
    @if (in_array('stripe_oxxo', $client_payment_options))
        <script>
            var stripe_oxxo_publishable_key = '{{ $stripe_oxxo_publishable_key }}';
        </script>
    @endif
    @if (in_array('stripe_ideal', $client_payment_options))
        <script>
            var stripe_ideal_publishable_key = '{{ $stripe_ideal_publishable_key }}';
        </script>
    @endif
    @if (in_array('payphone', $client_payment_options))
        <script src="https://pay.payphonetodoesposible.com/api/button/js?appId={{ $payphone_id }}"></script>
    @endif
    @if (in_array('khalti', $client_payment_options))
        <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
    @endif
    @if (in_array('mastercard', $client_payment_options))
        <script src="https://{{mastercardGateway()}}/static/checkout/checkout.min.js"></script>
    @endif
    <!-- <script src="https://js.stripe.com/v3/"></script> -->
    <script type="text/javascript">
        var ajaxCall = 'ToCancelPrevReq';
        var create_viva_wallet_pay_url = "{{ route('vivawallet.pay') }}";
        var create_mvodafone_pay_url = "{{ route('mvodafone.pay') }}";
        var create_konga_hash_url = "{{ route('kongapay.createHash') }}";
        var create_payphone_url = "{{ route('payphone.createHash') }}";
        var create_dpo_tocken_url = "{{ route('dpo.createTocken') }}";
        var create_easypaisa_hash_url = "{{ route('easypaisa.createHash') }}";
        var create_flutterwave_url = "{{ route('flutterwave.createHash') }}";
        var create_windcave_hash_url = "{{ route('windcave.createHash') }}";
        var create_paytech_hash_url = "{{ route('paytech.createHash') }}";
        var create_dpo_tocken = "{{ route('dpo.createTocken') }}";
        var create_ccavenue_url = "{{ route('ccavenue.pay') }}";
        var credit_wallet_url = "{{ route('user.creditWallet') }}";
        var payment_stripe_url = "{{ route('payment.stripe') }}";
        var payment_paypal_url = "{{ route('payment.paypalPurchase') }}";
        var wallet_payment_options_url = "{{ route('wallet.payment.option.list') }}";
        var payment_success_paypal_url = "{{ route('payment.paypalCompletePurchase') }}";
        var payment_paystack_url = "{{ route('payment.paystackPurchase') }}";
        var payment_success_paystack_url = "{{ route('payment.paystackCompletePurchase') }}";
        var payment_payfast_url = "{{ route('payment.payfastPurchase') }}";
        var payment_khalti_url = "{{ route('payment.khaltiVerification') }}";
        var payment_khalti_complete_purchase = "{{ route('payment.khaltiCompletePurchase') }}";
        var cabbookingwallet = 1;
        var amount_required_error_msg = "{{ __('Please enter amount.') }}";
        var payment_method_required_error_msg = "{{ __('Please select payment method.') }}";
        var product_order_form_element_data = [];
        var utilsScript_path = "{{ asset('assets/js/utils.js') }}";
        var initial_country_code = "{{ Session::get('default_country_code', 'US') }}";
        var add_rider_url = "{{ route('rider.create') }}";
        var remove_rider_url = "{{ route('rider.remove') }}";
        var payment_plugnpay_url = "{{route('payment.plugnpay.beforePayment')}}";
        var payment_azulpay_url = "{{route('payment.azulpay.beforePayment')}}";
        var user_cards_url = "{{ route('payment.azulpay.getCards') }}";
        var payment_obo_url = "{{route('obo.pay')}}";
        var data_trans_url = "{{route('payment.payByDataTrans')}}";
        var create_bid_url = "{{route('createBid')}}";
        var driver_biding_list_url = "{{route('getBidsRelatedToOrderRide')}}";
        var accept_bid_by_customer = "{{route('acceptBidByCustomer')}}";
        var livee_payment_url="{{route('livee.pay')}}";
        var payment_hitpay_url="{{ route('make.hitpay.payment') }}";
        var payment_orangepay_url =  "{{ route('orangepay.initiate.payment') }}";
        var payment_cybersource_url =  "{{ route('cybersource.initiate.payment') }}";

        @if ($client_preference_detail->distance_unit_for_time == 'mile')
            var distance_unit = "IMPERIAL";
        @else
            var distance_unit = "METRIC";
        @endif
        $('#wallet_amount').keypress(function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
        $('.verifyEmail').click(function() {
            verifyUser('email');
        });
        $('.verifyPhone').click(function() {
            verifyUser('phone');
        });

        function verifyUser($type = 'email') {
            ajaxCall = $.ajax({
                type: "post",
                dataType: "json",
                url: "{{ route('verifyInformation', Auth::user()->id) }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "type": $type,
                },
                beforeSend: function() {
                    if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                        ajaxCall.abort();
                    }
                },
                success: function(response) {
                    var res = response.result;
                },
                error: function(data) {},
            });
        }
        $(document).delegate(".custom_amount", "click", function() {
            let wallet_amount = $("#wallet_amount").val();
            let amount = $(this).text();
            if (wallet_amount == '') {
                wallet_amount = 0;
            }
            let new_amount = parseInt(amount) + parseInt(wallet_amount);
            $("#wallet_amount").val(new_amount);
        });

        $(document).on('change', '#wallet_payment_methods input[name="wallet_payment_method"]', function() {
            $('#wallet_payment_methods_error').html('');
            var method = $(this).val();
            if (method == 'stripe') {
                $("#wallet_payment_methods .stripe_element_wrapper").removeClass('d-none');
            } else if(method == 'powertrans') {
                $("#wallet_payment_methods .powertrans_element_wrapper").removeClass('d-none');
                $("#wallet_payment_methods .stripe_element_wrapper").addClass('d-none');
            } else {
                $("#wallet_payment_methods .stripe_element_wrapper").addClass('d-none');
            }
        });
    </script>
    <script>
        var loadFile = function(event) {
            var output = document.getElementById('output');
            output.src = URL.createObjectURL(event.target.files[0]);
        };
        var hourly_rental_url = "{{  route('front.booking.updateRentalPrice')}}";
        var csrf_token = "{{ csrf_token()}}";
    </script>
    @if (in_array('kongapay', $client_payment_options))
        <script src="https://kongapay-pg.kongapay.com/js/v1/production/pg.js"></script>
    @endif
    @if (in_array('flutterwave', $client_payment_options))
        <script src="https://checkout.flutterwave.com/v3.js"></script>
    @endif
    <script type="text/javascript" src="{{ asset('js/developer.js') }}"></script>
    <script src="{{ asset('js/payment.js') }}"></script>
    @if(in_array('data_trans',$client_payment_options))
        <script src="{{ $data_trans_script_url }}"></script>
    @endif

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/cab_booking.js') }}"></script>
    <script src="{{ asset('js/biding.js') }}"></script>
    <script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>

    <script>
        var category_id = "{{ $category->id ?? '' }}";
        var category_name = "{{ @$category->translation[0]->name ?? '' }}";
        var routeset = "{{ route('pickup-delivery-route', ':category_id') }}";
        var is_hourly_rental_enabled = 1;
        var autocomplete_urls = routeset.replace(":category_id", category_id);
        var wallet_balance = {{ $wallet_balance }}
        var payment_stripe_url = "{{ route('payment.stripe') }}";
        var get_rental_vehicle_list = "{{ route('get-list-of-rental-vehicles') }}";
        var get_rental_view = "{{route('get-rental-view')}}";


        var get_product_detail = "{{ url('looking/product-detail') }}";
        var get_payment_options = "{{ url('looking/payment/options') }}";
        var promo_code_list_url = "{{ route('verify.promocode.list') }}";
        var get_vehicle_list = "{{ url('looking/get-list-of-vehicles') }}";
        var cab_booking_create_order = "{{ url('looking/create-order') }}";
        var live_location = "{{ URL::asset('/images/live_location.gif') }}";
        var no_coupon_available_message = "{{ __('No Other Coupons Available.') }}";
        var order_tracking_details_url = "{{ url('looking/order-tracking-details') }}";
        var cab_booking_promo_code_remove_url = "{{ url('looking/promo-code/remove') }}";
        var apply_cab_booking_promocode_coupon_url = "{{ route('verify.cab.booking.promo-code') }}";
        var no_result_message = "{{ __('No result found. Please try a new search') }}";
        var create_mtn_momo_token = "{{route('mtn.momo.createToken')}}";
        var powertrans_payment_url = "{{ route('powertrans.payment') }}";
        var mastercard_create_session_url = "{{ route('payment.mastercard.createSession') }}";

        var pesapal_payment_url = "{{ route('pesapal.payment') }}";
        /// ************* product order form **************///////
        $('body').on('click', '.clproduct_order_form', function(event) {
            event.preventDefault();
            var product_id = $(this).data('product_id');
            $.get('/looking/get-product-order-form?product_id=' + product_id, function(markup) {
                $('#product_order_form').modal('show');
                $('#product-order-form-modal').html(markup);

            });
        });


        ///  ************ end product form ***************///////
    </script>


    <script type="text/javascript">
        $(document).ready(function(e) {
            $("#get-current-location").trigger("click");
            var daterang = $('input[name="schedule_pickup_date"]').daterangepicker({
                singleDatePicker: true,
                startDate: moment().add('10', 'minutes'),
                minDate: moment(),
                showDropdowns: false,
                timePicker: true,
                timePicker24Hour: false,
                timePickerIncrement: 1,
                autoUpdateInput: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm',
                }
            });
            $('.calendar_icon').click(function() {
                $('#schedule_pickup_date').click();
            })
            var path = window.location.pathname;
            var inputs = path.split("/");
            var lastslug = inputs[inputs.length - 1];
            //console.log(lastslug);
            $('#main-menu a').each(function(index, val) {
                var href = $(val).attr('href');
                if ((href.indexOf('category') !== -1) && (href.indexOf(lastslug) !== -1)) {
                    $(val).addClass('active');
                    $(val).parents("li").addClass('active');
                }
            })
            // if(parseInt($('input[name=is_for_friend]:checked')).val()==1){
            //     $('#label_for_me').removeClass('active');
            //     $('#label_for_friend').addClass('active');
            //     alert("here");
            // }
            $('#label_for_friend').click(function() {
                $('#label_for_me').removeClass('active');
                $('.address-form').removeClass('d-none');
                $(".hourly-rental-container").addClass('d-none');
                $('.hourly-rental-container').empty();
                $(".hourly-rental-container").removeClass('active');

                $('.location-containerNew').removeClass('d-none');

            });

            $('#label_for_hourly_rental').click(function() {

            $('.address-form').addClass('d-none');
            $('.location-containerNew').addClass('d-none');
            $('.check-dropoff-secpond').addClass('d-none');
            $('.for_friend').removeClass('active');
            $(".hourly-rental-container").removeClass('d-none');
            $(".hourly-rental-container").removeClass('active');
            $('#label_for_friend').removeClass('active');

            $('.hourly-rental-container').empty();

            // Make an AJAX request to load the view.
            $.ajax({
                url: "{{route('get-rental-view')}}",
                method: 'POST',
                success: function(response) {
                    // Append the retrieved view to the desired element.
                    $('.hourly-rental-container').html(response.view);

                },
                error: function(xhr, status, error) {
                    // Handle errors if necessary.
                }
            });
            });

            $(document).delegate('#submit_productfaq', 'click', function() {
                var product_order_form_element = getFormData('#product-order-form-name');
                $('#product_order_form').modal('hide');
            });


            function getFormData(dom_query) {
                var out = {};
                var s_data = $(dom_query).serializeArray();

                //transform into simple data/value object
                for (var i = 0; i < s_data.length; i++) {
                    var record = s_data[i];
                    out[record.name] = record.value;
                    product_order_form_element_data.push({
                        'question': record.name,
                        'answer': record.value
                    })
                }
                return out;
            }

        });

    function addSlashes (element) {
        let ele = document.getElementById(element.id);
        ele = ele.value.split('/').join('');    // Remove slash (/) if mistakenly entered.
        if(ele.length < 4 && ele.length > 0){
            let finalVal = ele.match(/.{1,2}/g).join('/');

            document.getElementById(element.id).value = finalVal;
        }
    }

    </script>

@endsection
