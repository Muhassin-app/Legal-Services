@extends('layouts.store', ['title' => 'Exchange Orders'])
@section('css')
@endsection
@section('content')
@php  $sku = $product->sku;  @endphp
<section class="section-b-space order-page">
    <div class="container">
        <div class="row my-md-3">
            <div class="col-lg-3">
                <div class="account-sidebar"><a class="popup-btn">my account</a></div>
                <div class="dashboard-left mb-3">
                    <div class="collection-mobile-back"><span class="filter-back d-lg-none d-inline-block"><i class="fa fa-angle-left" aria-hidden="true"></i> back</span></div>
                    @include('layouts.store/profile-sidebar')
                </div>
            </div>
            <div class="col-lg-9">
            <form id="return-upload-form" class="theme-form" action="javascript:void(0)" method="post" enctype="multipart/form-data">
                                    @csrf
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2>{{__("Exchange Order")}}</h2>
                        </div>
                        <div class="welcome-msg">
                            <h5>{{__("Here are your for exchange product !")}}</h5>
                        </div>
                        <div class="row">
                            <div class="container">

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">



                                            <div class="row">
                                                <div class="col-lg-6 p-0 @php if(count($product->media) == 0){  echo 'd-none'; } @endphp ">


                                                    <div class="exzoom hidden w-100">
                                                        <div class="exzoom_img_box mb-2">
                                                            <ul class='exzoom_img_ul'>
                                                                @if(!empty($product->media))

                                                                @foreach($product->media as $k => $image)
                                                                @php
                                                                if(isset($image->pimage)){
                                                                $img = $image->pimage->image;
                                                                }else{
                                                                $img = $image->image;
                                                                }
                                                                @endphp
                                                                @endforeach
                                                                @if(!is_null($img))
                                                                <img id="main_image" src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}" />
                                                                @endif
                                                                @endif
                                                            </ul>
                                                        </div>
                                                        @if(count($product->media) > 1)
                                                        <div class="exzoom_nav">
                                                            @if(!empty($product->media))
                                                            @foreach($product->media as $k => $image)
                                                            @php
                                                            if(isset($image->pimage)){
                                                            $img = $image->pimage->image;
                                                            }else{
                                                            $img = $image->image;
                                                            }
                                                            @endphp
                                                            @if(!is_null($img))
                                                            <span class="">
                                                                <img class="blur-up lazyloaded pro_imgs myimage1" data-src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}" width="60" height="60" src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}">
                                                            </span>
                                                            @endif
                                                            @endforeach
                                                            @endif
                                                        </div>
                                                        <p class="exzoom_btn">
                                                            <a href="javascript:void(0);" class="exzoom_prev_btn">
                                                                < </a> <a href="javascript:void(0);" class="exzoom_next_btn"> >
                                                                    </a>
                                                        </p>
                                                        @endif
                                                    </div>
                                                    <div id="myresult" class="img-zoom-result"></div>
                                                </div>

                                                <div class="@php if(!empty($product->media) && count($product->media) > 0){ echo 'col-lg-6'; } else { echo 'offset-lg-4 col-lg-4'; } @endphp rtl-text p-0">
                                                    <div class="product-right inner_spacing pl-sm-3 p-0">
                                                        <h2 class="mb-0">
                                                            {{ (!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->title : ''}}
                                                        </h2>
                                                        <span class="rating main-rating">4.1<i class="fa fa-star" aria-hidden="true"></i></span>
                                                        <h6 class="sold-by">
                                                            <b> <img class="blur-up lazyload" data-src="{{$product->vendor->logo['image_fit']}}200/200{{$product->vendor->logo['image_path']}}" alt="{{$product->vendor->Name}}"></b> <a href="{{ route('vendorDetail', $product->vendor->slug) }}"><b> {{$product->vendor->name}} </b></a>
                                                        </h6>
                                                        @if($client_preference_detail)
                                                        @if($client_preference_detail->rating_check == 1)
                                                        @if($product->averageRating > 0)
                                                        <span class="rating">{{ decimal_format($product->averageRating) }} <i class="fa fa-star text-white p-0"></i></span>
                                                        @endif
                                                        @endif
                                                        @endif
                                                        <div class="description_txt mt-3">
                                                            <p>{{ (!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_description : ''}}</p>
                                                        </div>
                                                        <input type="hidden" name="available_product_variant" id="available_product_variant" value="{{$product->variant[0]->id}}">
                                                        <input type="hidden" name="start_time" id="start_time" value="">
                                                        <input type="hidden" name="end_time" id="end_time" value="">
                                                        <div id="product_variant_wrapper">
                                                            <input type="hidden" name="variant_id" id="prod_variant_id" value="{{$product->variant[0]->id}}">

                                                            @if($product->inquiry_only == 0)
                                                            <h3 id="productPriceValue" class="mb-md-3">
                                                                <input type="hidden" name="product_a_price" class="product_a_price" value="{{number_format($product->variant[0]->price * $product->variant[0]->multiplier,2,".",",")}}" />
                                                                <b class="mr-1">{{Session::get('currencySymbol')}}<span class="product_fixed_price">{{number_format($product->variant[0]->price * $product->variant[0]->multiplier,2,".",",")}}</span></b>
                                                                @if($product->variant[0]->compare_at_price > 0 )
                                                                    <span class="org_price">{{Session::get('currencySymbol')}}<span class="product_original_price">{{decimal_format($product->variant[0]->compare_at_price * $product->variant[0]->multiplier)}}</span></span>
                                                                @endif
                                                            </h3>
                                                        @endif
                                                        </div>

                                                        <div id="product_variant_options_wrapper">
                                                            @if(!empty($product->variantSet))
                                                            @php
                                                            $selectedVariant = isset($product->variant[0]) ? $product->variant[0]->id : 0;
                                                            if($product->minimum_order_count > 0)
                                                            $product->minimum_order_count = $product->minimum_order_count;
                                                            else
                                                            $product->minimum_order_count = 1;
                                                            @endphp
                                                            @foreach($product->variantSet as $key => $variant)
                                                            @if($variant->type == 1 || $variant->type == 2)
                                                            <div class="size-box">
                                                                <ul class="productVariants">
                                                                    <li class="firstChild">{{$variant->title}}</li>
                                                                    <li class="otherSize">
                                                                        @foreach($variant->option2 as $k => $optn)
                                                                        <?php $var_id = $variant->variant_type_id;
                                                                        $opt_id = $optn->variant_option_id;
                                                                        $checked = ($selectedVariant == $optn->product_variant_id) ? 'checked' : '';
                                                                        ?>
                                                                        <label class="radio d-inline-block txt-14 mr-2">{{$optn->title}}
                                                                            <input id="lineRadio-{{$opt_id}}" name="{{'var_'.$var_id}}" vid="{{$var_id}}" optid="{{$opt_id}}" value="{{$opt_id}}" type="radio" class="changeVariant dataVar{{$var_id}}" {{$checked}}>
                                                                            <span class="checkround"></span>
                                                                        </label>
                                                                        @endforeach
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @else
                                                            @endif
                                                            @endforeach
                                                            @endif
                                                        </div>
                                                        <div id="variant_response">
                                                            <span class="text-danger mb-2 mt-2"></span>
                                                        </div>


                                                        @if(!empty($product->addOn) && $product->addOn->count() > 0)

                                                        <div class="border-product">
                                                            <h6 class="product-title">{{ __('Addon List')}}</h6>

                                                            <div id="addon-table">
                                                                @foreach($product->addOn as $row => $addon)
                                                                <div class="addon-product">
                                                                    <h4 addon_id="{{$addon->addon_id}}" class="header-title productAddonSet mb-2">{{$addon->title}}
                                                                        @php
                                                                        $min_select = '';
                                                                        $minText = __('Minimum');
                                                                        $maxText = __('Maximum');
                                                                        $andText = __('and');
                                                                        if($addon->min_select > 0){
                                                                        $min_select = $minText.' '.$addon->min_select;
                                                                        }
                                                                        $max_select = '';
                                                                        if($addon->max_select > 0){
                                                                        $max_select = $maxText.' '.$addon->max_select;
                                                                        }
                                                                        if( ($min_select != '') && ($max_select != '') ){
                                                                        $min_select = $min_select.' '.$andText.' ';
                                                                        }
                                                                        @endphp
                                                                        @if( ($min_select != '') || ($max_select != '') )
                                                                        <small>({{__($min_select).__($max_select)}} {{ __('Selections Allowed')}})</small>
                                                                        @endif
                                                                    </h4>

                                                                    <div class="productAddonSetOptions" data-min="{{$addon->min_select}}" data-max="{{$addon->max_select}}" data-addonset-title="{{$addon->title}}">
                                                                    @if($addon->setoptions)
                                                                    @foreach($addon->setoptions as $k => $option)
                                                                        <div class="checkbox checkbox-success form-check-inline mb-1">
                                                                            <input type="checkbox" id="inlineCheckbox_{{$row.'_'.$k}}" class="productDetailAddonOption" name="addonData[$row][]" addonId="{{$addon->addon_id}}" addonOptId="{{$option->id}}" data-price="{{$option->price * $option->multiplier}}" data-fixed_price="{{decimal_format($product->variant[0]->price * $product->variant[0]->multiplier)}}" data-original_price="{{decimal_format($product->variant[0]->compare_at_price * $product->variant[0]->multiplier)}}">
                                                                            <label class="pl-2 mb-0" for="inlineCheckbox_{{$row.'_'.$k}}" data-toggle="tooltip" data-placement="top" title="{{$option->title .' ('.Session::get('currencySymbol').decimal_format($option->price).')' }}">
                                                                                {{$option->title .' ('.Session::get('currencySymbol').decimal_format($option->price * $option->multiplier).')' }}</label>
                                                                        </div>
                                                                        @endforeach
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>

                                                        </div>
                                                        @endif
                                                        @php
                                                        $checkSlot = 0;
                                                        @endphp
                                                        <div class="product-buttons">
                                                            @if(!$product->has_inventory || $product->variant[0]->quantity > 0 || $product->sell_when_out_of_stock == 1)

                                                            @if($product->inquiry_only == 0)
                                                            @php
                                                            if($product->sell_when_out_of_stock == 1 && $product->variant[0]->quantity == 0){
                                                            $product_quantity_in_cart = 1;
                                                            $product->variant[0]->quantity = 2;
                                                            }
                                                            else
                                                            $product_quantity_in_cart = $product_in_cart->quantity??0;

                                                            @endphp
                                                            @if($is_available == 1)
                                                            {{-- <a href="#" data-toggle="modal" data-target="#addtocart" class="btn btn-solid addToCart {{ (($checkSlot == 0  && $vendor_info->is_vendor_closed == 1) || ($product->variant[0]->quantity <= $product_quantity_in_cart && $product->has_inventory)) ? 'btn-disabled' : '' }}">{{__('Add To Cart')}}</a>--}}
                                                            @endif

                                                            @endif
                                                            @endif
                                                        </div>
                                                        <div class="border-product al_disc">
                                                            <h6 class="product-title">{{__('Product Details')}}</h6>
                                                            <p></p>
                                                            {!!(!empty($product->translation) && isset($product->translation[0])) ?
                                                            $product->translation[0]->body_html : ''!!}
                                                        </div>


                                                    </div>

                                                </div>
                                            </div>
                                            <!---------------------------------------------------END-->

                                            <input id="item_one" type="hidden" name="return_ids" value="{{ $product->id }}" required>
                                            {{-- <label class="order-items d-flex" for="item_one{{$key}}">
                                                <div class="item-img mx-1">
                                                    <img src="{{ $product->image_url }}" alt="">
                                                </div>
                                                <div class="items-name ml-2">
                                                    <h4 class="mt-0 mb-1"><b>{{ $product->product_name }}</b></h4>
                                                    <label><b>{{_("Quantity")}}</b>: {{ $product->quantity }}</label>
                                                </div>
                                            </label> --}}
                                        </div>

                                    </td>
                                </tr>


                                <div class="row  bg-light p-2 pb-3">
                                    <div class="col-md-6">
                                        <input type="hidden" name="order_vendor_product_id" value="{{ $order->products[0]->id }}">
                                        <input type="hidden" name="file_set" id="files_set" value="0">
                                        <div id="remove_files">
                                        </div>
                                        <div class="row rating_files my-2">
                                            <div class="col-md-12 mb-3">
                                                <label>{{__('Upload Images')}}</label>
                                                <div class="file file--upload w-100 h-100">
                                                    <label for="input-file">
                                                        <span class="plus_icon"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                                    </label>
                                                    <input id="input-file" type="file" name="images[]" accept="image/*"  multiple>

                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <span class="row show-multiple-image-preview" id="thumb-output">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label>{{__('Reason for exchange product')}}</label>
                                        <select class="form-control" name="reason" id="reason">
                                            @foreach ($reasons as $reason)
                                            <option value="{{$reason->title}}">{{$reason->title}}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-2">
                                            <label>{{__('Comments (Optional)')}}:</label>
                                            <textarea class="form-control" name="coments" id="comments" cols="20" rows="4"></textarea>
                                        </div>
                                    </div>

                                </div>




                                <div class="col-12 p-0 mt-3" id="address_template_main_div">
                                    <div class="row">

                                        @forelse($addresses as $k => $address)
                                            @if(!empty(Auth::user()) && $address->is_primary)
                                            <div class="col-12 mb-2 text-right">
                                                <a class="alEditAddressIcons" href="{{route('user.addressBook')}}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Add New Address</a>
                                            </div>
                                            @endif

                                        <div class="col-md-6 mb-2">
                                            <div class="delivery_box cart_delivery  card h-100 p-2 mb-sm-3 mb-1 position-relative">
                                                <!-- <a class="deleteAddress"><i class="fa fa-trash-o"></i></a> -->
                                                <label class="radio m-0">{{ ($address->house_number ?? false) ? $address->house_number."," : '' }} {{$address->address}}, {{$address->state}} {{$address->pincode}}
                                                    @if($address->is_primary)
                                                    <input type="radio" name="address_id" value="{{$address->id}}" checked="checked">
                                                    @else
                                                    <input type="radio" name="address_id" value="{{$address->id}}" {{$k == 0? 'checked="checked"' : '' }}>
                                                    @endif
                                                    <span class="checkround"></span>
                                                </label>
                                            </div>
                                        </div>

                                        @if((($k+1)%2)==0)
                                    </div>
                                    @endif

                                    @if($k ==1)
                                </div>
                                <div class="view_all_address col-12 p-0 d-none" id="view_all_address_div">
                                    @endif


                                    @if((($k+1)%2)==0)
                                    <div class="row">
                                        @endif
                                        {{-- @if($k ==2)


                                        <div class="view_all_address d-none" id="view_all_address_div" >
                                        @endif
                                            <div class="col-md-6 mb-2">
                                                <div class="delivery_box cart_delivery p-2 mb-sm-3 mb-1 position-relative">

                                                    @if(!empty(Auth::user()))
                                                        <a href="{{route('user.addressBook')}}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                                        <!-- <span>{{ __('Edit') }} {{( __('Address') }}</span> -->
                                        @endif
                                        <label class="radio m-0">{{ ($address->house_number ?? false) ? $address->house_number."," : '' }} {{$address->address}}, {{$address->state}} {{$address->pincode}}
                                            @if($address->is_primary)
                                            <input type="radio" name="address_id" value="{{$address->id}}" checked="checked">
                                            @else
                                            <input type="radio" name="address_id" value="{{$address->id}}" {{$k == 0? 'checked="checked"' : '' }}>
                                            @endif
                                            <span class="checkround"></span>
                                        </label>
                                    </div>
                                </div>

                                @if(($k >6 ) && ($k ==count($addresses) -1 ))
                            </div>
                        </div> --}}

                        {{-- @endif --}}
                        @empty
                        <div class="col-12 address-no-found">
                            <p>{{ __('Address not available.') }}</p>
                        </div>
                        @endforelse
                        <!-- <div class="col-12 mt-4 text-center" id="add_new_address_btn">
                        <a class="btn btn-solid w-100 mx-auto mb-4">
                            <i class="fa fa-plus mr-1" aria-hidden="true"></i>{{__('Add New Address')}}
                        </a>
                    </div> -->
                    </div>
                </div>

                <div class="row w-100 mt-2">
                    <div class="cart_address w-100 text-center">
                        <a class="d-block w-100" id="view_all_address" href="javascript:void(0)">{{ __('View all address') }}</a>
                    </div>
                </div>
                <div class="col-12 border-top pt-2 mt-2">
                    <span class="text-danger" id="error-msg"></span>
                    <span class="text-success" id="success-msg"></span>
                    <button type="submit" class="btn btn-solid float-right" id="return_form_button">{{__('EXCHANGE NOW')}}</button>
                </div>
                <!-- </form> -->
            </div>
        </div>



    </div>
    </div>
    </div>
    </div>
    </div>
</section>



@endsection

@section('script')
<script type="text/template" id="variant_quantity_template">
    <% if(variant.product.inquiry_only == 0) { %>
    <div class="product-description border-product pb-0">
        <h6 class="product-title mt-0">{{__('Quantity')}}:
            <% if(variant.product.has_inventory && !(variant.quantity > 0) && (variant.product.sell_when_out_of_stock != 1)){ %>
                <span id="outofstock" style="color: red;">{{__('Out of Stock')}}</span>
            <% }else{ %>
                <input type="hidden" id="instock" value="<%= variant.quantity %>">
            <% } %>
        </h6>
        <% if(!variant.product.has_inventory || (variant.quantity > 0) || (variant.product.sell_when_out_of_stock == 1)){ %>
        <div class="qty-box mb-3">
            <div class="input-group">
                <span class="input-group-prepend">
                    <button type="button" class="btn quantity-left-minus" data-type="minus" data-field="" data-batch_count="<%= variant.product.batch_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>"><i class="ti-angle-left"></i>
                    </button>
                </span>
                <input type="text" onkeypress="return event.charCode > 47 && event.charCode < 58;" pattern="[0-9]{5}" name="quantity" id="quantity" class="form-control input-qty-number quantity_count" value="<%= variant.product.minimum_order_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>">
                <span class="input-group-prepend quant-plus">
                    <button type="button" class="btn quantity-right-plus " data-type="plus" data-field="" data-batch_count="<%= variant.product.batch_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>">
                        <i class="ti-angle-right"></i>
                    </button>
                </span>
            </div>
        </div>
        <% } %>
    </div>
    <% } %>
</script>
<script type="text/template" id="variant_image_template">
    <% if(variant.media != '') { %>
        <div class="swiper-container gallery-top">
            <div class="swiper-wrapper">
                <% _.each(variant.media, function(img, key){ %>
                    <div class="swiper-slide easyzoom easyzoom--overlay">
                        <a href="<%= img.pimage.image.path['image_fit'] %>600/600<%= img.pimage.image.path['image_path'] %>">
                        <img class="blur-up lazyload" data-src="<%= img.pimage.image.path['image_fit'] %>600/600<%= img.pimage.image.path['image_path'] %>" alt="">
                        </a>
                    </div>
                <% }); %>
            </div>
            <!-- Add Arrows -->
            <div class="swiper-button-next swiper-button-white"></div>
            <div class="swiper-button-prev swiper-button-white"></div>
        </div>
        <div class="swiper-container gallery-thumbs">
            <div class="swiper-wrapper">
                <% _.each(variant.media, function(img, key){ %>
                    <div class="swiper-slide">
                        <img class="blur-up lazyload" data-src="<%= img.pimage.image.path['image_fit'] %>300/300<%= img.pimage.image.path['image_path'] %>" alt="">
                    </div>
                <% }); %>
            </div>
        </div>
    <% }else{ %>
        <div class="swiper-container gallery-top">
            <div class="swiper-wrapper">
                <% _.each(variant.product.media, function(img, key){ %>
                    <% if(img.image != null) {%>
                        <div class="swiper-slide easyzoom easyzoom--overlay">
                            <a href="<%= img.image.path['image_fit'] %>600/600<%= img.image.path['image_path'] %>">
                            <img class="blur-up lazyload" data-src="<%= img.image.path['image_fit'] %>600/600<%= img.image.path['image_path'] %>" alt="">
                            </a>
                        </div>
                    <% }; %>
                <% }); %>
            </div>
            <!-- Add Arrows -->
            <div class="swiper-button-next swiper-button-white"></div>
            <div class="swiper-button-prev swiper-button-white"></div>
        </div>
        <div class="swiper-container gallery-thumbs">
            <div class="swiper-wrapper">
                <% _.each(variant.product.media, function(img, key){ %>
                    <% if(img.image != null) {%>
                        <div class="swiper-slide">
                            <img class="blur-up lazyload" data-src="<%= img.image.path['image_fit'] %>300/300<%= img.image.path['image_path'] %>" alt="">
                        </div>
                    <% }; %>
                <% }); %>
            </div>
        </div>
    <% } %>
</script>

<script type="text/template" id="variant_template">
    <input type="hidden" name="variant_id" id="prod_variant_id" value="<%= variant.id %>">
    <% if(variant.product.inquiry_only == 0) { %>
        <h3 id="productPriceValue" class="mb-md-3">
        <input type="hidden" name="product_a_price" class="product_a_price" value="<%= Helper.formatPrice(variant.productPrice) %>" />
            <b class="mr-1">{{Session::get('currencySymbol')}}<span class="product_fixed_price"><%= Helper.formatPrice(variant.productPrice) %></span></b>
            <% if(variant.compare_at_price > 0 ) { %>
                <span class="org_price">{{Session::get('currencySymbol')}}<span class="product_original_price"><%= Helper.formatPrice(variant.compare_at_price) %></span></span>
            <% } %>
        </h3>
    <% } %>
</script>
<script type="text/javascript">
    var ajaxCall = 'ToCancelPrevReq';
    var vendor_id = "{{ $product->vendor_id }}";
    var product_id = "{{ $product->id }}";
    var add_to_cart_url = "{{ route('addToCart') }}";
    $('.changeVariant').click(function() {
        updatePrice();
    });
    function updatePrice()
    {

        var variants = [];
        var options = [];
        $('.changeVariant').each(function() {
            var that = this;
            if (this.checked == true) {
                variants.push($(that).attr('vid'));
                options.push($(that).attr('optid'));
            }
        });
        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('productVariant', $sku) }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "variants": variants,
                "options": options,
            },
            beforeSend: function() {
                if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
            },
            success: function(resp) {
                // console.log(resp);
                if(resp.status == 'Success'){
                    $("#variant_response span").html('');
                    var response = resp.data;
                    if(response.variant != ''){
                        if(vendor_type == 'rental'){
                            // $('.incremental_hrs').val(0);
                            // $('.base_hours_min').val();
                            $('.incremental_hrs').val(0);
                            $('#incremental_hrs_hidden').val(base_hours_min);
                            $('.incremental-left-minus').click();
                            //$('#blocktime, #blocktime2').change();
                        }

                        $('#product_variant_wrapper').html('');
                        let variant_template = _.template($('#variant_template').html());
                        response.variant.productPrice = (parseFloat(checkAddOnPrice()) + parseFloat(response.variant.productPrice)).toFixed(digit_count);
                        response.variant.compare_at_price = (parseFloat(checkAddOnPrice()) + parseFloat(response.variant.compare_at_price)).toFixed(digit_count);
                        $("#product_variant_wrapper").append(variant_template({ Helper: NumberFormatHelper, variant:response.variant}));
                        $('#product_variant_quantity_wrapper').html('');
                        let variant_quantity_template = _.template($('#variant_quantity_template').html());
                        $("#product_variant_quantity_wrapper").append(variant_quantity_template({variant:response.variant}));
                        // console.log(response.variant.quantity);
                        if(!response.is_available){
                            $(".addToCart, #addon-table").hide();
                        }else{
                            $(".addToCart, #addon-table").show();
                        }
                        let variant_image_template = _.template($('#variant_image_template').html());
                        $(".product__carousel .gallery-parent").html('');
                        $(".product__carousel .gallery-parent").append(variant_image_template({variant:response.variant}));
                        // easyZoomInitialize();
                        // $('.easyzoom').easyZoom();

                        if(response.variant.media != ''){
                            $(".product-slick").slick({ slidesToShow: 1, slidesToScroll: 1, arrows: !0, fade: !0, asNavFor: ".slider-nav" });
                            $(".slider-nav").slick({ vertical: !1, slidesToShow: 3, slidesToScroll: 1, asNavFor: ".product-slick", arrows: !1, dots: !1, focusOnSelect: !0 });
                        }
                    }
                }else{
                    $("#variant_response span").html(resp.message);
                    $(".addToCart, #addon-table").hide();
                }
            },
            error: function(data) {

            },
        });
    }
    function checkAddOnPrice()
    {
        price  = 0;
        $('.productDetailAddonOption').each(function(){
            if($(this).prop('checked') == true){
                var cp = $(this).data('price');
                price = price + parseFloat(cp);
            }
        });
        return price;
    }
</script>
<script type="text/javascript">
    $(document).ready(function(e) {


        $(document).delegate('#view_all_address', 'click', function() {

            $("#view_all_address").addClass("d-none");
            $("#view_all_address_div").removeClass("d-none");

        });

        $('body').delegate('.local-img-del', 'click', function() {
            var img_id = $(this).data('id');
            $(this).prev().remove();
            $(this).remove();
            $("#" + img_id).remove();
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(function() {


            $('#input-file').on('change', function() {
                $('#files_set').val(1);
                $(this).closest("form").submit();
            });

            $('.server-img-del').on('click', function(e) {
                var img_id = $(this).data('id');
                $(this).prev().remove();
                $(this).remove();
                $("#remove_files").append("<input type='hidden' name='remove_files[]' value='" + img_id + "'>");
            });





        });
        $('#return-upload-form').submit(function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            let TotalImages = $('#input-file')[0].files.length; //Total Images
            let comments = $('#comments').val();
            if(TotalImages > 0)
            {

                let images = $('#input-file')[0];
                for (let i = 0; i < TotalImages; i++) {
                formData.append('images' + i, images.files[i]);
                }
                formData.append('TotalImages', TotalImages);
                formData.append('folder', '/return');

                    $.ajax({
                        type:'POST',
                        url: "{{ route('uploadfile')}}",
                        data: formData,
                        cache:false,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            if(TotalImages > 0)
                                $("#return_form_button").html('<i class="fa fa-spinner fa-spin fa-custom"></i> Loading').prop('disabled', true);
                            },
                        success: (data) => {
                        if(data.status == 'Success')
                            {
                                $("#input-file").val('');
                                for(var i = 0; i < data.data.length; i++) {
                                    $("#remove_files").append("<input type='hidden' name='add_files[]' id='"+ data.data[i]['ids'] +"' = value='"+ data.data[i]['name'] +"'>");
                                    $("#thumb-output").append("<div class='col-6 col-md-3 col-lg-2'> <img class=\"update_pic\" src=\"" + data.data[i]['img_path'] + "\" />" +
                                    "<i class='fa fa-trash local-img-del' aria-hidden='true' data-id='"+ data.data[i]['ids'] +"'></i></div>");
                                }

                                $("#return_form_button").html('Request').prop('disabled', false);
                            }else{
                                $('#error-msg').text(data.message);
                                $("#return_form_button").html('Request').prop('disabled', false);
                            }
                        },
                        error: function(data){
                            $('#error-msg').text(data.message);
                            $("#return_form_button").html('Request').prop('disabled', false);
                        }
                    });
            }
            else
            {


                $.ajax({
                    type: 'POST',
                    url: "{{ route('update.order.replace')}}",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        if (comments.length > 0)
                            $("#return_form_button").html('<i class="fa fa-spinner fa-spin fa-custom"></i> Loading').prop('disabled', true);
                    },
                    success: (data) => {
                        if (data.status == 'Success') {
                            console.log('dfasdf');
                            // if (comments.length == 0) {
                            //     console.log('dfasdf');
                            //     $("#return_form_button").html('Request').prop('disabled', false);
                            // } else {
                                console.log('dfasdf');
                                $("#return_form_button").html('Request');
                                var url = "{{route('user.orders',['pageType' => 'returnOrders'])}}";
                                $(location).prop('href', url);
                            // }
                            $('#error-msg').text('');
                        } else {
                            $('#error-msg').text(data.message);
                            $("#return_form_button").html('Request').prop('disabled', false);
                        }
                    },
                    error: function(data) {
                        $('#error-msg').text(data.message);
                        $("#review_form_button").html('Request').prop('disabled', false);
                    }
                });
            }

        });

    });
</script>

<script>
    var addonids = [];
    var addonoptids = [];
    $(function() {
        $(".productDetailAddonOption").click(function(e) {
            var addon_elem = $(this).closest('tr');
            var addon_minlimit = addon_elem.data('min');
            var addon_maxlimit = addon_elem.data('max');
            if(addon_elem.find(".productDetailAddonOption:checked").length > addon_maxlimit) {
                this.checked = false;
            }else{
                var addonId = $(this).attr("addonId");
                var addonOptId = $(this).attr("addonOptId");
                if ($(this).is(":checked")) {
                    addonids.push(addonId);
                    addonoptids.push(addonOptId);
                } else {
                    addonids.splice(addonids.indexOf(addonId), 1);
                    addonoptids.splice(addonoptids.indexOf(addonOptId), 1);
                }
                if($('.changeVariant').length > 0)
                {
                    updatePrice();
                }else{
                    addOnPrice = parseFloat(checkAddOnPrice());
                    org_price = parseFloat($(this).data('original_price')) + addOnPrice;
                    fixed_price = parseFloat($(this).data('fixed_price')) + addOnPrice;
                    $('.product_fixed_price').html(fixed_price.toFixed(digit_count));
                    $('.product_a_price').val(fixed_price.toFixed(digit_count));
                    $('.product_original_price').html(org_price.toFixed(digit_count));
                }
            }
        });
    });
</script>



@endsection