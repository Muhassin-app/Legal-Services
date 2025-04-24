@extends('layouts.store', [
'title' => (!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->title : '',
'meta_title'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_title:'',
'meta_keyword'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_keyword:'',
'meta_description'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_description:'',
])
@section('content')
<section class="single_product_block">
    <div class="container">
        <div class="row">
            <div class="left col-md-7">
                <div class="product_slider prodct_slider">
                    @if(!empty($product->media) && count($product->media) > 0)
                    @foreach($product->media as $k => $image)
                    @php
                    if(isset($image->pimage)){
                    $img = $image->pimage->image;
                    }else{
                    $img = $image->image;
                    }
                    @endphp
                    <div class="item">
                        <div class="image">
                            <img src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}" alt="" data-src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}">
                        </div>
                    </div>
                    {{-- <div class="item">
                            <div class="image">
                                <img src="images/product1.png" alt="">
                            </div>
                        </div> --}}
                    @endforeach
                    @endif
                    {{-- <div class="item">
                            <div class="">
                                @if(!empty($product->media) && count($product->media) > 0)
                                @foreach($product->media as $k => $image)
                                @php
                                if(isset($image->pimage)){
                                $img = $image->pimage->image;
                                }else{
                                $img = $image->image;
                                }
                                @endphp
                                @if(!is_null($img))
                                <img data-src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}" src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}">
                    @endif
                    @endforeach
                    @else
                    <img data-src="{{loadDefaultImage()}}" width="60" height="60" src="{{loadDefaultImage()}}">
                    @endif
                </div>
            </div> --}}
        </div>
        <ul class="slider product_list_slider">
            <li><img src="images/product.png" alt=""></li>
            <li><img src="images/product1.png" alt=""></li>
        </ul>
    </div>
        @php
    $fields = [];
    $desc = [];
    $detail = [
    'Mileage',
    'Engine',
    'Transmission',
    'BHP',
    'Seats',
    'Boot Space',
    'Fuel Type',
    'Cabins',
    'Baths',
    'Berths'
    ];
    foreach ($product->ProductAttribute as $productAttribute) {
    $attribute = $productAttribute->attribute;
    $img = $attribute->icon['proxy_url'] . '100/100' . $attribute->icon['image_path'];
    if ($productAttribute->attributeOption()->exists()) {
    $title = $productAttribute->attributeOption->title ?? $productAttribute->key_value;
    if(in_array($productAttribute->key_name, $detail)){
    $fields[$productAttribute->key_name]['title'] = $title;
    $fields[$productAttribute->key_name]['img'] = $img;
    }else{
    $desc[$productAttribute->key_name]['title'] = $title;
    $desc[$productAttribute->key_name]['img'] = $img;
    }
    }
    }
    $allReviews = array_column($product->vendor->products()->with('reviews')->get()->toArray(),'reviews');
    
    $rating = array_sum(array_column($allReviews,'rating'));
    @endphp
    <div class="right col-md-5">
        <div class="text">
            <h2>{{ (!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->title : ''}}</h2>
            <h6 class="sold-by mt-2">
                <b> <img class="blur-up lazyload" data-src="{{$product->vendor->logo['image_fit']}}100/100{{$product->vendor->logo['image_path']}}" alt="{{$product->vendor->Name}}"></b> <a href="{{ route('vendorDetail', $product->vendor->slug) }}"><b> {{$product->vendor->name}} </b></a>
            </h6>

            <div class="location">
                <img src="" alt="">
                <span>{{$product->vendor->address}}</span>
            </div>

            {{-- <div class="pass-detail">
                <ul class="m-0 p-0">
                    <li><span>Flexible</span> {!!$product->returnable ? 'Cancellation <br/>Policy' : 'No <br/> Cancellation'!!}</li>
                    <li><span>Passenger</span> Up to {{$desc['Passangers']['title'] ?? 0}} <br/> Passengers</li>
                    <li><span>Captained</span> Captain <br/>{{$product->captain_name ? 'Available' : 'Not Available'}}</li>
                </ul>
            </div>

            <div class="booking-option mt-1">
                <h5>Booking Option</h5>
            </div> --}}
            <div class="product_price">
                @if($category->slug == 'rental')
                <p>{{Session::get('currencySymbol')}}{{decimal_format($product->variant[0]->price)}}</p>
                @elseif($category->slug == 'yacht')
                @foreach($product->variantSet as $key => $variant)
                <div class="vset-box mt-2">
                    <div class="d-flex justify-content-around">
                        @foreach($variant->option2 as $key => $option)
                        <div class="border border">
                          <input type="radio" name="booking_duration" value="{{$option->product_variant_id}}" />
                            <h6>{{$option->title}}</h6>
                            <h5>{{Session::get('currencySymbol')}}{{decimal_format($option->price)}}</h5>
                            <span></span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @endif
                {{-- <p>AED<span> 599.00/day</span></p> --}}
            </div>
            <div class="productList d-flex justify-content-between">
                <ul class="product-features">
                    @if($category->slug == 'rental' &&  Session::get('vendorType') == 'car_rental')
                    <li><a href="javascript:void(0);">{{$fields['Transmission']['title'] ?? ''}}</a></li>
                    <li><a href="javascript:void(0);">{{$fields['Fuel Type']['title'] ?? ''}}</a></li>
                    <li><a href="javascript:void(0);">{{$fields['Seats']['title'] ?? '0'}} Seats</a></li>
                    {{-- @elseif($category->slug == 'yacht')
                    <li><a href="javascript:void(0);">{{$fields['Cabins']['title'] ?? '0'}} Cabins</a></li>
                    <li><a href="javascript:void(0);">{{$fields['Baths']['title'] ?? '0'}} Baths</a></li>
                    <li><a href="javascript:void(0);">{{$fields['Berths']['title'] ?? '0'}} Berths</a></li> --}}
                    @endif
                </ul>
            </div>
            @if($category->slug == 'rental' && Session::get('vendorType') == 'car_rental')
                <div class="product_iteslist">
                    <ul>
                        @foreach ($fields as $key => $productAttribute)
                        <li><img src="{{$productAttribute['img']}}"> <span>{{$key}} <b>{{$productAttribute['title']}}</b> </span></li>
                        @endforeach
                    </ul>
                
                    <div class="mb-2">
                        <p><b>Rental Start Time:-</b> <span>{{$pickup_time}}</span></p>
                        <p><b>Rental End Time:- </b>{{$drop_time}}</p>
                    </div>
                
                </div>
            @endif
            <div class="product_cta">
                @php
                /*$newRequest->merge(['product_id'=> $product->product_id, 'quantity'=>$product->quantity, 'variant_id'=>$product->product->variant[0]->id, 'vendor_id'=>$product->product->vendor_id,'bid_number'=>(($is_bid_enable)?$id:null),'bid_discount'=>(($is_bid_enable)?$product->bids->discount:null)]);
                $data = $CartController->postAddToCart($newRequest);*/
                @endphp
                <a href="javascript:void(0);" id="add_to_cart_btn" class="addToCart">Next</a>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>

<section class="single_product_description">
    <div class="container">
        <h3>Description</h3>
        {!! ($product->translation_one->body_html) !!}
    </div>
</section>

@if($category->slug == 'yacht')
<section class="container map-container mt-5">
    <div class="row">
        <div class="col-md-3">
            <div class="vendor-box">
                <div id="vendor-detail">
                    <div class="vendor-info d-flex">
                        <div class="image-box">
                            <img class="blur-up lazyload border" data-src="{{$product->vendor->logo['image_fit']}}100/100{{$product->vendor->logo['image_path']}}" alt="{{$product->vendor->Name}}"/>
                        </div>
                        <div class="text">
                            <h4><b> {{$product->vendor->name}} </b></h4>
                            <p class="">
                            <span id="rating"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                                    <path d="M7.11958 1.17082C7.23932 0.802296 7.76068 0.802296 7.88042 1.17082L9.31856 5.59696C9.37211 5.76177 9.5257 5.87336 9.69899 5.87336L14.3529 5.87336C14.7404 5.87336 14.9015 6.3692 14.588 6.59696L10.8229 9.33247C10.6827 9.43433 10.6241 9.61487 10.6776 9.77968L12.1158 14.2058C12.2355 14.5743 11.8137 14.8808 11.5002 14.653L7.73511 11.9175C7.59492 11.8157 7.40508 11.8157 7.26489 11.9175L3.49978 14.653C3.1863 14.8808 2.76451 14.5743 2.88425 14.2058L4.32239 9.77968C4.37594 9.61487 4.31728 9.43433 4.17708 9.33247L0.411978 6.59696C0.0984929 6.3692 0.259603 5.87336 0.647093 5.87336L5.30101 5.87336C5.4743 5.87336 5.62789 5.76177 5.68144 5.59696L7.11958 1.17082Z" fill="#E89732"/>
                                </svg><small>{{$rating}}</small></span><span id="bookings">{{$productBookingsCount}} Bookings</span></p>
                        </div>
                    </div>
                    <div class="res-time">
                        <p>AVG. RESPONSE TIME</p>
                        <p>2 HOURS</p>
                    </div>

                    <div class="btn-message">
                        <a href="#">MESSAGE OWNER</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div id="map"></div>
        </div>
    </div>
</section>
<section class="container py-5 extra-details">
    <div id="accordion">
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#specs" aria-expanded="true" aria-controls="specs">
                        SPECIFICATIONS
                    </button>
                </h5>
            </div>
            <div id="specs" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    {{$desc['Specification']['title'] ?? ''}}
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#cancellation" aria-expanded="true" aria-controls="cancellation">
                        CANCELLATION
                    </button>
                </h5>
            </div>
            <div id="cancellation" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    {{$product->returnable ? "Cancellable" : "Non-Cancelable"}}
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#owner" aria-expanded="true" aria-controls="owner">
                        COMMERCIAL OWNER
                    </button>
                </h5>
            </div>
            <div id="owner" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    {{$desc['Commercial Owner']['title'] ?? ''}}
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#secDeposit" aria-expanded="true" aria-controls="secDeposit">
                        SECURITY DEPOSIT
                    </button>
                </h5>
            </div>
            <div id="secDeposit" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    {{$product->security_amount ? Session::get('currencySymbol').$product->security_amount. ' need to be paid as security amount' : 'No Security Amount'}}
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#captain" aria-expanded="true" aria-controls="captain">
                        CAPTAIN INFO
                    </button>
                </h5>
            </div>
            <div id="captain" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    <img src="{{$product->captain_profile}}"/>
                    {{$product->captain_name ? $product->captain_name : 'No Captain Avialable'}}
                    <p>{{$product->captain_description}}</p>
                </div>
            </div>
        </div>
    </div>
    
</section>
@endif

<section class="product_dis_list d-none">
    <div class="container">
        <input type="hidden" name="available_product_variant" id="available_product_variant" value="{{$product->variant[0]->id}}">
        <input type="hidden" name="start_time" id="start_time" value="{{$pickup_time}}">
        <input type="hidden" name="is_template" id="is_template" value="1">
        <input type="hidden" name="end_time" id="end_time" value="{{$drop_time}}">
        <input type="hidden" name="variant_id" id="prod_variant_id" value="{{$product->variant[0]->id}}">
        <input type="hidden" name="sele_slot_id" id="sele_slot_id" value="" />
        <input type="hidden" name="sele_slot_price" id="sele_slot_price" value="" />
        <input type="hidden" name="product_id" id="product_id" value="{{$product->id}}" />
        <input type="hidden" name="prod_variant_id" id="prod_variant_id" value="" />
        <div id="selected_slot"></div>
          @include('frontend.product-part.booking-slot')
        {{-- <h3>Car specifications</h3>
        <ul class="specifications_list">
            <li>Stunning swimming pool and Gym access</li>
            <li>Full Marina View</li>
            <li>Allocated Parking</li>
            <li>Balcony</li>
            <li>Free high-speed </li>
            <li>WiFi</li>
            <li>Kitchen with full appliances</li>
            <li>5 Star Hotel facilities</li>
        </ul> --}}
    </div>
</section>
@if(!empty($desc) && $category->slug == 'rental')
<section class="product_dis_list">
    <div class="container">
        <h3>Additional Features</h3>
        <ul class="specifications_list additional_list">
            @foreach($desc as $key => $value)
            <li>{{$key}} <span>{{$value['title']}}</span></li>
            @endforeach
        </ul>
    </div>
</section>
@endif
@endsection
@section('script')
<script>
    var addonids = [];
    var addonoptids = [];
    var ajaxCall = 'ToCancelPrevReq';
    let vendor_id = "{{ $product->vendor_id }}";
    let product_id = "{{ $product->id }}";
    var add_to_cart_url = "{{ route('addToCart') }}";
    var serviceType = "{{$category->slug}}";

    var companyLocation = {
        lat: 37.7749
        , lng: -122.4194
    };

    document.addEventListener("DOMContentLoaded", () => {
        var map = new google.maps.Map(document.getElementById('map'), {
            center: companyLocation
            , zoom: 14
        });

        console.log(map);

        var marker = new google.maps.Marker({
            position: companyLocation
            , map: map
            , title: 'Company Location'
        });

        $('.prodct_slider').slick({
            slidesToShow: 1
            , slidesToScroll: 1
            , arrows: false
            , fade: true
            , asNavFor: '.product_list_slider'
        });

        $('.product_list_slider').slick({
            slidesToShow: 6
            , slidesToScroll: 1
            , asNavFor: '.prodct_slider'
            , dots: false
            , focusOnSelect: true
        });
        $('a[data-slide]').click(function(e) {
            e.preventDefault();
            var slideno = $(this).data('slide');
            $('.product_list_slider').slick('slickGoTo', slideno - 1);
        });
    });

</script>
