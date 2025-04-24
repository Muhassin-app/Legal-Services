<div>
    <a class="card scale-effect text-center" href="{{ $value['vendor']->slug }}/product/{{ $value['url_slug'] }}">
        <label class="product-tag">@if($value["tag_title"] != 0) {{$value["tag_title"]}} @else {{$homePageLabel->title}}@endif </label>
        <div class="product-image">
            <img class="blur-up lazyloaded" src="{{ $value['image_url'] }}" alt="">
        </div>
        <div class="media-body align-self-center">
            <div class="inner_spacing px-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="m-0">{{ $value["title"] }}</h3>
                    @if($client_preference_detail)
                        @if($client_preference_detail->rating_check == 1)
                            @if($value["averageRating"] >0)
                                <span class="rating">{{ $value["averageRating"] }} <i class="fa fa-star text-white p-0"></i></span>
                            @endif 
                        @endif
                    @endif
                </div>
                <p>{{ $value["vendor_name"] }}</p>
                <h4>
                    @if($value["inquiry_only"] == 0)
                    {!!$value["price"] ?? ''!!}
                    @endif
                </h4>
            </div>
        </div>
    </a>
</div>