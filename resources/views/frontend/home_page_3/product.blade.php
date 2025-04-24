<div class="product-card-box position-relative al_box_third_template al"  >
    {{-- {{ dd($product)}} --}}
    {{--<div class="add-to-fav 12">
        <input id="fav_pro_one" type="checkbox">
        <label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label>
    </div>--}}
    <a class="common-product-box text-center" href="{{ $product->vendor_slug }}/product/{{ $product->url_slug }}">
        <div class="img-outer-box position-relative"> <img class="blur-up lazyload" data-src="{{ get_file_path($product->path,'FILL_URL','260','260') }}" alt="" title="">
            <div class="pref-timing"> </div>
        </div>
        <div class="media-body align-self-start">
            <div class="inner_spacing px-0">
                <div class="product-description">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="card_title ellips">{{ $product->title }} @if ($product->calories)
                            ({{$product->calories}} {{ __("calories") }})
                        @endif </h6> 
                        @if($client_preference_detail && $client_preference_detail->rating_check==1) 
                            @if($product->averageRating >0)
                                <span class="rating-number">{{ $product->averageRating }}</span>
                            @endif 
                        @endif 
                    </div>
                    <p class="al_productText ellips">
                        {{ $product->vendor_name }}
                    </p>
                    <p class="border-bottom pb-1 d-none">
                        <span>{{__('In ') . $product->category_name}} </span>
                    </p>
                    @if($is_service_product_price_from_dispatch_forOnDemand!=1) 
                    <div class="d-flex align-items-center justify-content-between al_clock"> 
                        {{-- <b>{!!$product->price_numeric ?? ''!!}</b> --}}
                        <b> {{ showPriceWithCurrency($product->price_numeric) }} </b>

                        <!-- <p><i class="fa fa-clock-o"></i> 30-40 min</p>  -->
                        @php
                        $comp = @$product->compare_price_numeric??0;
                        @endphp
                        @if(@$comp && $comp>0)
                            {!!showPriceWithCurrency($product->compare_price_numeric,'1') !!}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </a>
</div>