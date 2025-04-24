
<link rel="stylesheet" href="{{asset('compare-assets/css/reset.css')}}">
<link rel="stylesheet" href="{{asset('compare-assets/css/style.css')}}">


@if(@getAdditionalPreference(['is_enable_compare_product'])['is_enable_compare_product'] && 
    (in_array($product->category->category_id,getVendorAdditionalPreference($product->vendor_id,'compare_categories'))))
              
                {{-- New Section  --}}

    <section class="cd-products-comparison-table product_compare_design tab-pane show {{(count($rating_details)>0)?'':'active'}}" id="compare-product" role="tabpanel" aria-labelledby="compare-product-tab" >
                    <header>           
                        <div class="actions">
                            <a href="#0" class="reset">Reset</a>
                            <a href="#0" class="filter active">Filter</a>
                        </div>
                    </header>
            
                    <div class="cd-products-table">
                        <div class="features">
                            <div class="top-info model_comp">Models</div>
                            <ul class="cd-features-list">
                                <li>Price</li>
                                <li>Rating</li>
                                <li class="compare_pro_discription">Description</li>
                                <li class="seller_comp">Seller</li>
                            </ul>
                        </div> <!-- .features -->
                        
                        <div class="cd-products-wrapper custom_scroll-compare">
                            <ul class="cd-products-columns">

                            @foreach($suggested_category_products as $compare)

                            @php
                            if(isset($compare->media->first()->image)){
                                $image_fit = $compare->media->first()->image->path['image_fit'];
                                $image_path = $compare->media->first()->image->path['image_path'];
                                $product_image = $image_fit . '200/150' . $image_path;
                            }
                        @endphp

                                <li class="product addClass">
                                    <div class="top-info">
                                        <div class="check"></div>
                                        <a target="_blank"
                    href="{{ route('productDetail', [$compare->vendor->slug, $compare->url_slug]) }}" class="compare_img">
                    <img src="{{$product_image??'N/A'}}" alt="{{$compare->title??$compare->slug}}"></a>
                                        <h3>{{$compare->title??$compare->slug}}</h3>
                                    </div> <!-- .top-info -->
            
                                    <ul class="cd-features-list">
                                        <li>{{showPriceWithCurrency($compare->variant->first()->price)}}</li>
                                        <li class="rate{{round($compare->averageRating)}}"><span>{{ number_format($compare->averageRating, 1, '.', '')}}</span></li>
                                        <li class="compare_pro_discription">{!!  $compare->translation->first()->body_html?? 'N/A' !!}
                                        </li>
                                        <li><a href="{{route('vendorDetail',[$compare->vendor->slug])}}"> {{$compare->vendor->name}}</a></li>
                                    </ul>
                                </li> <!-- .product -->
                                @endforeach
            
                            </ul> <!-- .cd-products-columns -->
                        </div> <!-- .cd-products-wrapper -->
                        
                        <ul class="cd-table-navigation">
                            <li><a href="#0" class="prev inactive">Prev</a></li>
                            <li><a href="#0" class="next">Next</a></li>
                        </ul>
                    </div> <!-- .cd-products-table -->
                </section> <!-- .cd-products-comparison-table -->

                {{-- End Section --}}

    @endif
    
@section('script-bottom-js')

<script src="{{asset('compare-assets/js/main.js')}}"></script>

<script>
    // $('.product').each(function(index) {
    //     console.log('hi in '+index);
    //     if(index < 3){
    //         $(this).addClass('selected');
    //     }else {
    //         return false;
    //     }
    // });
    // $('.filter .active').trigger();
</script>
@endsection