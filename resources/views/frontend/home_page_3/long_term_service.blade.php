<div class="product-card-box position-relative al_box_third_template al"  >
    {{-- {{ dd($product)}} --}}
    {{--<div class="add-to-fav 12">
        <input id="fav_pro_one" type="checkbox">
        <label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label>
    </div>--}}	
    {{-- @php
							pr($value);
						@endphp --}}
    <a class="common-product-box text-center" href="{{ $value['vendor']->slug }}/product/{{ $value['url_slug'] }}">
        <div class="img-outer-box position-relative"> <img class="blur-up lazyload" data-src="{{ $value['image_url'] }}" alt="" title="">
            <div class="pref-timing"> </div>
        </div>
        <div class="media-body align-self-start">
            <div class="inner_spacing px-0">
                <div class="product-description">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="card_title ellips">{{ $value["title"] }}</h6> @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
                        @if($value["averageRating"] >0)<span class="rating-number">{{ $value["averageRating"] }}</span>
                        @endif @endif @endif </div>
                    <div class="product-description_list border-bottom">
                        <p>
                            {{ $value["vendor_name"] }}
                        </p>
                        
                    </div>
                    <div class="d-flex align-items-center justify-content-between al_clock pt-2">
                        <b>{!!$value["price"] ?? ''!!} </b>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>