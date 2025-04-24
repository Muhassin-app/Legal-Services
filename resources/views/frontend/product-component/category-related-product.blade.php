{{-- Related Product  --}}
@if( !empty($realted_produuct) && $realted_produuct->count() > 0)
<h2>{{ __($title ?? '') }}</h2>
<div class="category-related-product similar-products-vitamin suggested-product">
    @foreach($realted_produuct as $scp)
    
    @if( !empty($scp->vendor->slug) && !empty($scp->url_slug) )
        <div class="product-card-box position-relative al_box_third_template al " style="width: 100%; display: inline-block;">
            <a class="common-product-box text-center" href="{{route('productDetail', [$scp->vendor->slug,$scp->url_slug])}}" tabindex="-1">                                                        
                <div class="img-outer-box position-relative"> 
                    @if(count($scp->media) > 0)
                        <div class="exzoom_nav">
                            @if(!empty($scp->media) && !empty($scp->media->first()))
                            @php $image = $scp->media->first() @endphp
                            
                            @php
                                if(isset($image->pimage))
                                    $img = $image->pimage->image;
                                else
                                    $img = $image->image;
                            @endphp
                                @if(!is_null($img))
                                <span class="">
                                    <img class="blur-up lazyloaded pro_imgs myimage1 dfasdfasdf"
                                        data-src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}"
                                        width="60" height="60"
                                        src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}">
                                </span>
                                @endif
                            @else
                            <span class="">
                                <img class="blur-up lazyloaded pro_imgs myimage1"
                                    data-src="{{loadDefaultImage()}}"
                                    width="60" height="60"
                                    src="{{loadDefaultImage()}}">
                            </span>
                            
                            @endif
                        </div>
                        <p class="exzoom_btn">
                            <a href="javascript:void(0);" class="exzoom_prev_btn">
                                < </a> <a href="javascript:void(0);" class="exzoom_next_btn"> >
                            </a>
                        </p>
                        @else
                            <span class="">
                                <img class="blur-up lazyloaded pro_imgs myimage1"
                                    data-src="{{loadDefaultImage()}}"
                                    width="60" height="60"
                                    src="{{loadDefaultImage()}}">
                            </span>
                        @endif

                    <div class="pref-timing"> </div>
                </div>
                <div class="media-body align-self-start">
                    <div class="inner_spacing px-0">
                        <div class="product-description">
                        @php
                                $price = $scp->variant[0]->price ?? 0;
                                $compare_at_price = $scp->variant[0]->compare_at_price ?? 0;
                                $multiplier = $scp->variant[0]->multiplier  ?? 0;
                                $priceVal = ($multiplier > 0) ? $price * $multiplier : $price;
                                @endphp
                            @if(productDiscountPercentage($price, $compare_at_price))
                                <span class="flag-discount">{{productDiscountPercentage($price, $compare_at_price)}}% Off</span>
                            @endif
                            
                            @if($set_template->template_id != 9)
                                <span class="rating">4.0 <i class="fa fa-star text-white p-0"></i></span>
                            @endif
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="card_title ellips">{{ (!empty($scp->translation) && isset($scp->translation[0])) ? $scp->translation[0]->title : ''}}</h6>                             
                            </div>
                            <div class="product-description_list border-bottom">
                                <p>
                                    {{optional($scp->vendor)->name ?? 'N/A'}}
                                </p>
                                <p class="al_product_category">
                                    <span>
                                In
                                {{optional($scp->categoryName)->name ?? 'N/A'}}</span>
                                </p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between al_clock pt-2">
                                
                                @if($additionalPreference ['is_token_currency_enable'])
                                    <b>{!!"<i class='fa fa-money' aria-hidden='true'></i> "!!} {{getInToken($priceVal)}} </b>
                                @else
                                    <b>{{showPriceWithCurrency($priceVal)}} </b>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endif
    @endforeach
</div>
@endif