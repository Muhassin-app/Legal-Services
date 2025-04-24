<div>
    <a class="card scale-effect text-center" href="{{ $product->vendor_slug }}/product/{{ $product->url_slug }}">
        <label class="product-tag">@if($product->title != 0) {{$product->title}} @else {{$homePageLabel->title}}@endif </label>
        <div class="product-image">
            <img class="blur-up lazyloaded" src="{{ get_file_path($product->path,'FILL_URL','260','260') }}" alt="">
        </div>
        <div class="media-body align-self-center">
            <div class="inner_spacing px-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="m-0">{{ $product->title }}</h3>
                        @if($client_preference_detail && $client_preference_detail->rating_check==1) 
                            @if($product->averageRating >0)
                                <span class="rating-number">{{ $product->averageRating }}</span>
                            @endif 
                        @endif 
                </div>
                <p>{{  $product->vendor_name  }}</p>
                @if($is_service_product_price_from_dispatch_forOnDemand!=1) 
                <h4>
                    <b> {!! (showPriceWithCurrency($product->price_numeric)) !!} </b>

                        <!-- <p><i class="fa fa-clock-o"></i> 30-40 min</p>  -->
                        @php
                        $comp = @$product->compare_price_numeric??0;
                        @endphp
                        @if(@$comp && $comp>0)
                            {!!showPriceWithCurrency($product->compare_price_numeric,'1') !!}
                        @endif
                </h4>
                @endif
            </div>
        </div>
    </a>
</div>