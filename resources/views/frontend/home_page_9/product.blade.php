<div class="item">
    <div class="product-card-box position-relative al_box_third_template al test"  >
      
        {{--<div class="add-to-fav 12">
            <input id="fav_pro_one" type="checkbox">
            <label for="fav_pro_one"><i class="fa fa-heart-o fav-heart" aria-hidden="true"></i></label>
        </div>--}}
        {{-- @dd($product['ProductAttribute'][0]->key_value) --}}
        <a class="common-product-box text-center" href="{{ $product->vendor_slug }}/product/{{ $product->url_slug }}">
            <div class="img-outer-box position-relative">
                
                
            @if(@$product->type_id == 13)
            <span class="for_sale">For Sale</span>
            @endif
            
            @if(@$product->type_id == 10)
            <span class="for_rent">For Rent</span>
            @endif
                <img class="blur-up lazyload" data-src="{{ get_file_path($product->path,'FILL_URL','260','260') }}" alt="" title="">
                {{-- <div class="pref-timing"> </div> --}}
                {{-- <div class="wishlist-icon btn-default" prosku="{{ $product['sku'] }}" remwishlist='<i class="fa fa-heart-o wishListCount" aria-hidden="true"></i>' addwishlist='<i class="fa fa-heart-o" aria-hidden="true"></i>'>
                    @if($product['is_inwishlist_btn'] == 0) 
                        <i class="fa fa-heart-o" aria-hidden="true"></i>
                        @else
                        <i class="fa fa-heart-o wishListCount" aria-hidden="true"></i>
                    @endif
                </div> --}}
            </div>
            <div class="media-body align-self-start">
                <div class="inner_spacing">
                    <div class="product-description">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="card_title ellips" style="width:100%;">{{  $product->title }}</h6> 
                            {{-- @if($client_preference_detail) @if($client_preference_detail->rating_check==1)
                            @if($product["averageRating"] >0)
                            <!-- <span class="rating-number">{{ $product["averageRating"] }}</span> -->
                            <span class="d-flex"><strong>AED</strong><h3>{!!$product["price"] ?? ''!!} </h3></span>
                            @endif @endif @endif  --}}
                        </div>
                        <div class="product-description_list">
                            <!-- <span class="flag-discount">30% Off</span> -->
                            <!-- <p>
                                {{ $product->vendor_name  }}
                            </p> -->
                            <!-- <p class="al_product_category">
                                <span class="product_discription">
                            {{__('In')}}
                            {{$product->category_name ?? ''}}</span>
                            <span class="rating"><i class="fa fa-star" aria-hidden="true"></i>4.5</span>
                            </p> -->
                            @if(@$product->type_id == 13)
                            <div class="chat-button">
                                @if(getAdditionalPreference(['chat_button'])['chat_button'])
                                    <a class="start_chat chat-icon btn btn-solid"  data-vendor_order_id="" data-chat_type="userToUser" data-vendor_id="{{$product->vendor_id}}" data-orderid="" data-order_id="" data-product_id="{{$product->id}}"><i class="fa fa-comments" aria-hidden="true"></i></a>
                                    {{-- {{__('Chat')}} --}}
                                @endif
                                @if(getAdditionalPreference(['call_button'])['call_button'])
                                    <a class="call-icon btn btn-solid" href="tel:"><i class="fa fa-phone    " aria-hidden="true"></i></a>
                                    {{-- {{__('Call Button')}} --}}
                                @endif
                            </div>
                            @endif
                        </div>
                        @if(!empty($product->attribute_key_value))
                            <div class="d-flex align-items-center justify-content-between prod_location pt-2">
                                <b> <span class="loction ellips"><i class="fa fa-map-marker" aria-hidden="true"></i> {{$product->attribute_key_value}}</span></b>
                               
                            </div>
                        @endif
                        <div class="d-flex align-items-center justify-content-between al_clock pt-2 update_year">
                            <b>Updated {{ convertDateToHumanReadable($product->updated_at) }} </b>
                        </div>
                        <div class="prod-details">
                            <div class="d-flex align-items-center justify-content-between al_clock pt-2">
                                <b>{{showPriceWithCurrency($product->price_numeric)}} </b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@section('script')
<script>
    // Home Page Wishlist Function
    $(document).on('click', '.wishlist-icon', function(e){
        e.preventDefault();
        var sku = $(this).attr('proSku');
        var remveFrmWishlist = $(this).attr('remWishlist');
        var addWishlist = $(this).attr('addWishlist');
        var _this = $(this);
        $.ajax({
            type: "post",
            dataType: "json",
            url: add_to_whishlist_url,
            data: {
                "_token": $('meta[name="_token"]').attr('content'),
                "sku": sku,
                "variant_id": $('#prod_variant_id').val()
            },
            success: function (res) {
                if (res.status == "success") {
                    if (_this.hasClass('btn-default')) {
                        if (res.message.indexOf('added') !== -1) {
                            _this.html(remveFrmWishlist);
                        } else {
                            _this.html(addWishlist);
                        }
                    }
                } else {
                    location.reload();
                }
            }
        });
    });
</script>
@endsection
