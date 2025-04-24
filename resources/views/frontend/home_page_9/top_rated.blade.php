    <div class="col">
        <div class="product-card-box position-relative al_box_third_template al">
            {{--<div class="add-to-fav 12">
                <input id="fav_pro_one" type="checkbox">
                <label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label>
            </div>--}}
            <a class="common-product-box text-center" href="{{ $product['vendor']->slug }}/product/{{ $product['url_slug'] }}">
                <div class="img-outer-box position-relative"> <img class="blur-up lazyload" data-src="{{ $product['image_url'] }}" alt="" title="">
                    <div class="pref-timing"> </div>
                </div>
                <div class="media-body align-self-start">
                    <div class="inner_spacing">
                        <div class="product-description">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="card_title ellips">{{ $product["title"] }}</h6> @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
                                @if($product["averageRating"] >0)<span class="rating-number">{{ $product["averageRating"] }}</span>
                                @endif @endif @endif </div>
                            <div class="product-description_list border-bottom">
                                <span class="flag-discount">30% Off</span>
                                <p>
                                    {{ $product["vendor_name"] }}
                                </p>
                                <p class="al_product_category">
                                    <span>
                                {{__('In')}}
                                {{$product["category"] ?? ''}}</span>
                                <span class="rating"><i class="fa fa-star" aria-hidden="true"></i>4.5</span>
                                </p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between al_clock pt-2">
                                <b>{{$product["price"] ?? ''}} </b>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
