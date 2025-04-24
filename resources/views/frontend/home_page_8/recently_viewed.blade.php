
<div class="col">
    <div class="product-card-box position-relative al_box_third_template al"  >
        {{-- {{ dd($product)}} --}}
        {{--<div class="add-to-fav 12">
            <input id="fav_pro_one" type="checkbox">
            <label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label>
        </div>--}}
        {{-- <span class="rating-number">4.0</span> --}}
        <a class="common-product-box text-center" href="{{ $product->vendor_slug }}/product/{{ $product->url_slug }}">
            <div class="img-outer-box position-relative"> <img class="blur-up lazyload" data-src="{{ get_file_path($product->path,'FILL_URL','260','260') }}" alt="" title="">
                <div class="pref-timing"> </div>
            </div>
            <div class="media-body align-self-start">
                <div class="inner_spacing">
                    <div class="product-description">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="card_title ellips">{{ $product->title }}</h6> 
                                @if($client_preference_detail && $client_preference_detail->rating_check==1) 
                                    @if($product->averageRating >0)
                                        <span class="rating-number">{{ $product->averageRating }}</span>
                                    @endif 
                                @endif 
                            </div>
                            <div class="product-description_list border-bottom">
                                @if($dicountPercentage = productDiscountPercentage(@$product->price_numeric, @$product->compare_price))
                                    <span class="flag-discount">{{$dicountPercentage}}% Off</span>
                                @endif
                                <p>
                                    {{ $product->vendor_name }}
                                </p>
                          
                        </div>
                        <div class="d-flex align-items-center justify-content-left al_clock ">
                            {{-- <b>{!!$product->price_numeric ?? ''!!}</b> --}}
                            <b> {{ session()->get('currencySymbol').' '.($product->price_numeric ?? 0) }} </b>

    
                            <!-- <p><i class="fa fa-clock-o"></i> 30-40 min</p>  -->
                            @php
                            $comp = @$product->compare_price_numeric??0;
                            @endphp
                            @if(@$comp && $comp>0)
                            <del class="ml-2 compare_at_price" >{{  session()->get('currencySymbol').' '.($product->compare_price_numeric ?? 0) }} </del>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    </div>
    