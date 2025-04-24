<div class="product-card-box position-relative al_box_four_template al">
    <a class="common-product-box text-center" href="{{ $value["vendor"]->slug }}/product/{{ $value["url_slug"] }}">
        <div class="img-outer-box position-relative"> <img class="blur-up lazyload" data-src="{{ $value["image_url"] }}" alt="" title="">
            <div class="pref-timing"> </div>
        </div>
        <div class="media-body align-self-start">
            <div class="inner_spacing px-0">
                <div class="product-description mt-2 text-left">
                    <div class="al_productName">
                        <p class="al_vendorName mb-0 ellips">{{ $value["vendor_name"] }}</p>
                    </div>
                    <h6 class="card_title m-0 ellips">{{ $value["title"] }}</h6> 
                    @if($client_preference_detail && $client_preference_detail->rating_check==1)
                    @if($value["averageRating"] >0)

                    @endif @endif
                    <div class="product-description_list">
                        <p class="al_ratingNumber mb-0">
                            <span class="Stars" style="--rating: {{ $value["averageRating"] }}" aria-label="Rating of this product is {{ $value["averageRating"] }} out of 5."></span>
                        </p>
                        
                    </div>
                    <div class="d-flex align-items-center justify-content-end al_clock px-2">
                        <b> {!!$value["price"] ?? ''!!}</b>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>