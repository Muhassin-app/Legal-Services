@extends('layouts.store', ['title' => $vendor->name])
@section('css')
<style type="text/css">
    .main-menu .brand-logo{display:inline-block;padding-top:20px;padding-bottom:20px}.productVariants .firstChild{min-width:150px;text-align:left!important;border-radius:0!important;margin-right:10px;cursor:default;border:none!important}.product-right .color-variant li,.productVariants .otherChild{height:35px;width:35px;border-radius:50%;margin-right:10px;cursor:pointer;border:1px solid #f7f7f7;text-align:center}.productVariants .otherSize{height:auto!important;width:auto!important;border:none!important;border-radius:0}.product-right .size-box ul li.active{background-color:inherit}.product-box .product-detail h4,.product-box .product-info h4{font-size:16px}select.changeVariant{color:#343a40;border:1px solid #bbb;border-radius:5px;font-size:14px}.counter-container{border:1px solid var(--theme-deafult);border-radius:5px;padding:2px}.switch{opacity:0;position:absolute;z-index:1;width:18px;height:18px;cursor:pointer}.switch+.lable{position:relative;display:inline-block;margin:0;line-height:20px;min-height:18px;min-width:18px;font-weight:400;cursor:pointer}.switch+.lable::before{cursor:pointer;font-family:fontAwesome;font-weight:400;font-size:12px;color:#32a3ce;content:"\a0";background-color:#fafafa;border:1px solid #c8c8c8;box-shadow:0 1px 2px rgba(0,0,0,.05);border-radius:0;display:inline-block;text-align:center;height:16px;line-height:14px;min-width:16px;margin-right:1px;position:relative;top:-1px}.switch:checked+.lable::before{display:inline-block;content:'\f00c';background-color:#f5f8fc;border-color:#adb8c0;box-shadow:0 1px 2px rgba(0,0,0,.05),inset 0 -15px 10px -12px rgba(0,0,0,.05),inset 15px 10px -12px rgba(255,255,255,.1)}.switch+.lable{margin:0 4px;min-height:24px}.switch+.lable::before{font-weight:400;font-size:11px;line-height:17px;height:20px;overflow:hidden;border-radius:12px;background-color:#f5f5f5;-webkit-box-shadow:inset 0 1px 1px 0 rgba(0,0,0,.15);box-shadow:inset 0 1px 1px 0 rgba(0,0,0,.15);border:1px solid #ccc;text-align:left;float:left;padding:0;width:52px;text-indent:-21px;margin-right:0;-webkit-transition:text-indent .3s ease;-o-transition:text-indent .3s ease;transition:text-indent .3s ease;top:auto}.switch.switch-bootstrap+.lable::before{font-family:FontAwesome;content:"\f00d";box-shadow:none;border-width:0;font-size:16px;background-color:#a9a9a9;color:#f2f2f2;width:52px;height:22px;line-height:21px;text-indent:32px;-webkit-transition:background .1s ease;-o-transition:background .1s ease;transition:background .1s ease}.switch.switch-bootstrap+.lable::after{content:'';position:absolute;top:2px;left:3px;border-radius:12px;box-shadow:0 -1px 0 rgba(0,0,0,.25);width:18px;height:18px;text-align:center;background-color:#f2f2f2;border:4px solid #f2f2f2;-webkit-transition:left .2s ease;-o-transition:left .2s ease;transition:left .2s ease}.switch.switch-bootstrap:checked+.lable::before{content:"\f00c";text-indent:6px;color:#fff;border-color:#b7d3e5}.switch-primary>.switch.switch-bootstrap:checked+.lable::before{background-color:#337ab7}.switch-success>.switch.switch-bootstrap:checked+.lable::before{background-color:#5cb85c}.switch-danger>.switch.switch-bootstrap:checked+.lable::before{background-color:#d9534f}.switch-info>.switch.switch-bootstrap:checked+.lable::before{background-color:#5bc0de}.switch-warning>.switch.switch-bootstrap:checked+.lable::before{background-color:#f0ad4e}.switch.switch-bootstrap:checked+.lable::after{left:32px;background-color:#fff;border:4px solid #fff;text-shadow:0 -1px 0 rgba(0,200,0,.25)}.switch-square{opacity:0;position:absolute;z-index:1;width:18px;height:18px;cursor:pointer}.switch-square+.lable{position:relative;display:inline-block;margin:0;line-height:20px;min-height:18px;min-width:18px;font-weight:400;cursor:pointer}.switch-square+.lable::before{cursor:pointer;font-family:fontAwesome;font-weight:400;font-size:12px;color:#32a3ce;content:"\a0";background-color:#fafafa;border:1px solid #c8c8c8;box-shadow:0 1px 2px rgba(0,0,0,.05);border-radius:0;display:inline-block;text-align:center;height:16px;line-height:14px;min-width:16px;margin-right:1px;position:relative;top:-1px}.switch-square:checked+.lable::before{display:inline-block;background-color:#f5f8fc;border-color:#adb8c0;box-shadow:0 1px 2px rgba(0,0,0,.05),inset 0 -15px 10px -12px rgba(0,0,0,.05),inset 15px 10px -12px rgba(255,255,255,.1)}.switch-square+.lable{margin:0 4px;min-height:24px}.switch.switch-bootstrap+.lable::before,.switch.switch-bootstrap:checked+.lable::before{content:"";width:40px;height:18px;line-height:21px}.switch.switch-bootstrap+.lable::after{width:14px;height:14px}.switch+.lable{line-height:14px}.switch.switch-bootstrap:checked+.lable::after{left:23px}.switch-square+.lable::before{font-weight:400;font-size:11px;line-height:17px;height:20px;overflow:hidden;border-radius:2px;background-color:#f5f5f5;-webkit-box-shadow:inset 0 1px 1px 0 rgba(0,0,0,.15);box-shadow:inset 0 1px 1px 0 rgba(0,0,0,.15);border:1px solid #ccc;text-align:left;float:left;padding:0;width:52px;text-indent:-21px;margin-right:0;-webkit-transition:text-indent .3s ease;-o-transition:text-indent .3s ease;transition:text-indent .3s ease;top:auto}.switch-square.switch-bootstrap+.lable::before{font-family:FontAwesome;box-shadow:none;border-width:0;font-size:16px;background-color:#a9a9a9;color:#f2f2f2;width:52px;height:22px;line-height:21px;text-indent:32px;-webkit-transition:background .1s ease;-o-transition:background .1s ease;transition:background .1s ease}.switch-square.switch-bootstrap+.lable::after{content:'';position:absolute;top:2px;left:3px;border-radius:12px;box-shadow:0 -1px 0 rgba(0,0,0,.25);width:18px;height:18px;text-align:center;background-color:#f2f2f2;border:4px solid #f2f2f2;-webkit-transition:left .2s ease;-o-transition:left .2s ease;transition:left .2s ease}.switch-square.switch-bootstrap:checked+.lable::before{text-indent:6px;color:#fff;border-color:#b7d3e5}.switch-primary>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#337ab7}.switch-success>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#5cb85c}.switch-danger>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#d9534f}.switch-info>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#5bc0de}.switch-warning>.switch-square.switch-bootstrap:checked+.lable::before{background-color:#f0ad4e}.switch-square.switch-bootstrap:checked+.lable::after{left:32px;background-color:#fff;border:4px solid #fff;text-shadow:0 -1px 0 rgba(0,200,0,.25)}.switch-square.switch-bootstrap+.lable::after{border-radius:2px}
    .profile_address ul.vendor-info li.d-block.vendor-location a{position: absolute;right: 0px;top:0px;padding:0px 6px;
    border-radius: 4px;border: 1px dotted#938a8a;background-color: #f8f1f8;}.social-icon-list {
    width: 100%;max-width: 90%;}.social-icon-list .modal-body {text-align: center;}.social-icon-list .modal-body .text-center a img {width: 40px;}
.social-icon-list .modal-body .text-center {display: inline-block;margin: 0px 6px;}.profile_address ul.vendor-info li.d-block.vendor-location a span {font-size: 13px;}.profile_address ul.vendor-info li.d-block.vendor-location a img {width: 12px;}
/* .vendor-description .vendor-details-left .vender-icon .vendor-stories a img {width: 110px;height: 110px;} */
.vendor-description .profile_address h3 {font-size: 24px;text-transform: capitalize;margin: 10px 0px 6px 0px;}
.vendor-description .profile_address h4 {font-size: 16px; margin: 0px;color: #6c757d;}
/* .vendor-description .profile_address ul.vendor-info li {padding: 2px 8px 3px 0px;} */
.al_body_template_two .vendor-description .vendor-reviwes{padding: 0;position: absolute;right: auto;left:0px;top:5px;}
.al_body_template_six.homeHeader .product-bottom-bar{padding: 20px 10px;}
.vendor-description .vendor-info .d-block.vendor-location{padding-left:0px;}
.al_body_template_six.homeHeader .vendor-stories{background: transparent;}
.vendor-description .vendor-details-left .vendor-location a {position: inherit !important;}
.al_body_template_six .vendor-description .vendor-info .vendor-location{margin-bottom:10px;}
.line_diff_between_products{border-top: 1px dotted rgb(61, 60, 60)}
span.alPriceValue, span.alPriceValue i {
    display: inline-flex;
    align-items: baseline;
}
</style>
    <link rel="stylesheet" type="text/css" href="{{ asset('front-assets/css/price-range.css') }}">
@endsection
@section('content')

<section class="section-b-space ratio_asos alProductCategories">
    <div class="collection-wrapper">
        <div class="container">
                <div class="row">
                        <div class="col-12">
                            @include('frontend.vendor-category-topbar-banner')   
                        </div>
                        @include('frontend.vendor-details-in-banner')
                       
                </div>
                <section class="scrolling_section m-5">
        <h2 class="category-head mt-0 mb-3">
            {{ $category->slug ?? '' }}
            ({{ $products->total() }})
        </h2>
        @forelse($products as $prod)
            <div class="row cart-box-outer al_white_bg_round product_row classes_wrapper no-gutters mb-2 pb-2 border-bottom"
                data-p_sku="{{ $prod->sku }}" data-slug="{{ $prod->url_slug }}">
                <div class=" col-sm-2 col-4 mb-2">
                    <a target="_blank" href="{{ route('productDetail', [$prod->vendor->slug, $prod->url_slug]) }}">
                        <div class="class_img product_image">
                            <img src="{{ $prod->product_image }}" alt="{{ $prod->translation_title }}">
                        </div>
                    </a>

                </div>
                <div class="col-sm-10 col-8  pl-md-3 pl-2">
                    <div class="row price_head">
                        <div class="col-sm-12">
                            <div class="d-flex align-items-start justify-content-between">
                                <h5 class="mt-0">
                                    {{ $prod->translation_title }}

                                </h5>
                                <div class="product_variant_quantity_wrapper">
                                    @php
                                        $data = $prod;
                                        $productVariantInCart = 0;
                                        $productVariantIdInCart = 0;
                                        $productVariantInCartWithDifferentAddons = 0;
                                        $cartProductId = 0;
                                        $cart_id = 0;
                                        $vendor_id = 0;
                                        $product_id = $data->id;
                                        $variant_id = $data->variant[0] ? $data->variant[0]->id : 0;
                                        $variant_price = 0;
                                        $variant_quantity = $prod->variant_quantity;
                                        $isAddonExist = 0;
                                        $minimum_order_count = $data->minimum_order_count == 0 ? 1 : $data->minimum_order_count;
                                        $batch_count = $data->batch_count;
                                        if (count($data->addOn) > 0) {
                                            $isAddonExist = 1;
                                        }
                                        // dd($data->variant);
                                    @endphp

                                    @foreach ($data->variant as $var)
                                        @if (isset($var->checkIfInCart) && count($var->checkIfInCart) > 0)
                                            @php
                                                //dd($var->_markup_price);
                                                $productVariantInCart = 1;
                                                $productVariantIdInCart = $var->checkIfInCart['0']['variant_id'];
                                                $cartProductId = $var->checkIfInCart['0']['id'];
                                                $cart_id = $var->checkIfInCart['0']['cart_id'];
                                                // $variant_quantity = $var->checkIfInCart['0']['quantity'];
                                                $variant_quantity = 0;
                                                $vendor_id = $data->vendor_id;
                                                $product_id = $data->id;
                                                $batch_count = $data->batch_count;
                                                $variant_price = decimal_format($var->price * $data->variant_multiplier);
                                                if (count($var->checkIfInCart) > 1) {
                                                    $productVariantInCartWithDifferentAddons = 1;
                                                }
                                                foreach ($var->checkIfInCart as $cartVar) {
                                                    $variant_quantity = $variant_quantity + $cartVar['quantity'];
                                                }
                                            @endphp
                                        @break

                                        ;
                                    @endif
                                @endforeach

                                @if ($vendor->is_vendor_closed == 0 || ($vendor->closed_store_order_scheduled != 0 && $checkSlot != 0))
                                    @php
                                        $is_customizable = false;
                                        if ($isAddonExist > 0 && ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1)) {
                                            $is_customizable = true;
                                        }
                                    @endphp

                                    @if ($productVariantInCart > 0)
                                        {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                                        <a class="add-cart-btn add_vendor_product" style="display:none;"
                                            id="add_button_href{{ $cartProductId }}"
                                            data-variant_id="{{ $productVariantIdInCart }}"
                                            data-add_to_cart_url="{{ route('addToCart') }}"
                                            data-vendor_id="{{ $vendor_id }}" data-product_id="{{ $product_id }}"
                                            data-addon="{{ $isAddonExist }}"
                                            data-minimum_order_count="{{ $minimum_order_count }}"
                                            data-batch_count="{{ $batch_count }}"
                                            href="javascript:void(0)">{{ __('Add') }}
                                            @if ($minimum_order_count > 0)
                                                ({{ $minimum_order_count }})
                                            @endif
                                        </a>
                                        <div class="number" id="show_plus_minus{{ $cartProductId }}">
                                            <span
                                                class="minus qty-minus-product {{ $productVariantInCartWithDifferentAddons ? 'remove-customize' : '' }}"
                                                data-variant_id="{{ $productVariantIdInCart }}"
                                                data-parent_div_id="show_plus_minus{{ $cartProductId }}"
                                                data-id="{{ $cartProductId }}" data-base_price="{{ $variant_price }}"
                                                data-vendor_id="{{ $vendor_id }}"
                                                data-product_id="{{ $product_id }}" data-cart="{{ $cart_id }}"
                                                data-addon="{{ $isAddonExist }}"
                                                data-minimum_order_count="{{ $minimum_order_count }}"
                                                data-batch_count="{{ $batch_count }}">
                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                            </span>
                                            <input
                                                style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                placeholder="1" type="text" value="{{ $variant_quantity }}"
                                                class="input-number" id="quantity_ondemand_{{ $cartProductId }}"
                                                readonly>
                                            <span
                                                class="plus qty-plus-product {{ $is_customizable ? 'repeat-customize' : '' }}"
                                                data-variant_id="{{ $productVariantIdInCart }}"
                                                data-id="{{ $cartProductId }}" data-base_price="{{ $variant_price }}"
                                                data-vendor_id="{{ $vendor_id }}"
                                                data-product_id="{{ $product_id }}" data-cart="{{ $cart_id }}"
                                                data-addon="{{ $isAddonExist }}"
                                                data-batch_count="{{ $batch_count }}">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    @else
                                        @if ($prod->has_inventory == 0 || ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1))
                                            {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                                            <a class="add-cart-btn add_vendor_product"
                                                id="aadd_button_href{{ $data->id }}"
                                                data-variant_id="{{ $data->variant[0]->id }}"
                                                data-add_to_cart_url="{{ route('addToCart') }}"
                                                data-vendor_id="{{ $data->vendor_id }}"
                                                data-product_id="{{ $data->id }}" data-addon="{{ $isAddonExist }}"
                                                data-batch_count="{{ $batch_count }}"
                                                data-minimum_order_count="{{ $minimum_order_count }}"
                                                href="javascript:void(0)">{{ __('Add') }}
                                                @if ($minimum_order_count > 1)
                                                    ({{ $minimum_order_count }})
                                                @endif
                                            </a>
                                            <div class="number" style="display:none;"
                                                id="ashow_plus_minus{{ $data->id }}">
                                                <span class="minus qty-minus-product"
                                                    data-parent_div_id="show_plus_minus{{ $data->id }}"
                                                    data-id="{{ $data->id }}"
                                                    data-base_price="{{ decimal_format($data->variant_price * $data->variant_multiplier) }}"
                                                    data-vendor_id="{{ $data->vendor_id }}"
                                                    data-batch_count="{{ $batch_count }}"
                                                    data-minimum_order_count="{{ $minimum_order_count }}">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <input
                                                    style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                    id="quantity_ondemand_d{{ $data->id }}" readonly
                                                    placeholder="{{ $minimum_order_count }}" type="text"
                                                    value="{{ $minimum_order_count }}" class="input-number input_qty"
                                                    step="0.01">
                                                <span class="plus qty-plus-product" data-id=""
                                                    data-base_price="{{ decimal_format($data->variant_price * $data->variant_multiplier) }}"
                                                    data-vendor_id="{{ $data->vendor_id }}"
                                                    data-batch_count="{{ $batch_count }}"
                                                    data-minimum_order_count="{{ $minimum_order_count }}">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-danger">{{ __('Out of stock') }}</span>
                                        @endif
                                    @endif
                                    @if ($is_customizable)
                                        <div class="customizable-text">
                                            {{ __('customizable') }}
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @if ($prod->averageRating > 0)
                            <div class="rating-text-box">
                                <span>{{ number_format($prod->averageRating, 1, '.', '') }}
                                </span>
                                <i class="fa fa-star" aria-hidden="true"></i>
                            </div>
                        @endif

                        <p class="mb-1 product_price ">
                            {{ Session::get('currencySymbol') . decimal_format($prod->variant_price * $prod->variant_multiplier, ',') }}
                            @if ($prod->variant[0]->compare_at_price > 0)
                                <span
                                    class="org_price ml-1 font-14">{{ Session::get('currencySymbol') . decimal_format($prod->variant[0]->compare_at_price * $prod->variant_multiplier) }}</span>
                            @endif
                        </p>
                        <div class="member_no d-block mb-0">

                            <span>{!! $prod->translation_description !!}</span>
                        </div>
                        <div id="product_variant_options_wrapper">
                            @if (!empty($prod->variantSet))
                                @php
                                    $selectedVariant = $productVariantIdInCart > 0 ? $productVariantIdInCart : $prod->variant_id;
                                @endphp
                                @foreach ($prod->variantSet as $key => $variant)
                                    @if ($variant->type == 1 || $variant->type == 2)
                                        <?php $var_id = $variant->variant_type_id; ?>
                                        <select name="{{ 'var_' . $var_id }}" vid="{{ $var_id }}"
                                            class="changeVariant dataVar{{ $var_id }}">
                                            <option value="" disabled>
                                                {{ $variant->title }}
                                            </option>
                                            @foreach ($variant->option2 as $k => $optn)
                                                <?php
                                                $opt_id = $optn->variant_option_id;
                                                $selected = $selectedVariant == $optn->product_variant_id ? 'selected' : '';
                                                ?>
                                                <option value="{{ $opt_id }}" {{ $selected }}>
                                                    {{ $optn->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="variant_response">
                            <span class="text-danger mb-2 mt-2 font-14"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
            <h4 class="mt-3 mb-3 text-center">No product found</h4>
        @endforelse
        {{ $products->links() }}
    </section>
        </div>
        
    </div>
</section>

    
@endsection

@section('script')
<script src="{{ asset('front-assets/js/rangeSlider.min.js') }}"></script>
<script src="{{ asset('front-assets/js/my-sliders.js') }}"></script>
<script>
    @if (!empty($vendor->banner))
        $(document).ready(function() {
            $("body").addClass("homeHeader");
        });
    @endif

    //Get the modal vendorStories
    var modal = document.getElementById("vendorStories");

    //Get the image and insert it inside the modal - use its "alt" text as a caption
    var img = document.getElementById("vendorStoriesImg");
    var modalImg = document.getElementById("img01");
    var captionText = document.getElementById("caption");
    img.onclick = function() {
        modal.style.display = "block";
        modalImg.src = this.src;
        captionText.innerHTML = this.alt;
    }

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    //When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }
</script>
<script>
    var get_product_addon_url = "{{ route('vendorProductAddons') }}"

    // jQuery(window).scroll(function() {

    //     var scroll = jQuery(window).scrollTop();
    //     var categories_list_height = $('.vendor-products-wrapper').height() +400;

    //     if (scroll >= 600) {
    //         jQuery(".categories-product-list").addClass("fixed-bar");
    //     } else {
    //         jQuery(".categories-product-list").removeClass("fixed-bar");
    //     }
    //     if(scroll >= categories_list_height){
    //         jQuery(".categories-product-list").removeClass("fixed-bar");
    //     }
    // });

    var addonids = [];
    var addonoptids = [];
    var showChar = 136;
    var ellipsestext = "...";
    var moretext = "Read more";
    var lesstext = "Read less";

    function addReadMoreLink() {
        $('.price_head .member_no span').each(function() {
            var content = $(this).html();
            if (content.length > showChar) {

                var firstContent = content.substr(0, showChar);
                var lastContent = content.substr(showChar, content.length - showChar);

                var html = firstContent + '<span class="moreellipses">' + ellipsestext +
                    '&nbsp;</span><span class="morecontent"><span style="display:none;">' + lastContent +
                    '</span><a href="" class="morelink">' + moretext + '</a></span>';

                $(this).html(html);
            }

        });
    }
    addReadMoreLink();

    $(document).on('click', '.morelink', function() {
        if ($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });

    $(document).delegate(".product_tag_filter", "change", function() {
        vendorProductsSearchResults();
    });
</script>
<script>
    var base_url = "{{ url('/') }}";
    var place_order_url = "{{ route('user.placeorder') }}";
    var payment_stripe_url = "{{ route('payment.stripe') }}";
    var user_store_address_url = "{{ route('address.store') }}";
    var promo_code_remove_url = "{{ route('remove.promocode') }}";
    var payment_paypal_url = "{{ route('payment.paypalPurchase') }}";
    var update_qty_url = "{{ url('product/updateCartQuantity') }}";
    var promocode_list_url = "{{ route('verify.promocode.list') }}";
    var payment_option_list_url = "{{ route('payment.option.list') }}";
    var apply_promocode_coupon_url = "{{ route('verify.promocode') }}";
    var payment_success_paypal_url = "{{ route('payment.paypalCompletePurchase') }}";
    var getTimeSlotsForOndemand = "{{ route('getTimeSlotsForOndemand') }}";
    var update_cart_schedule = "{{ route('cart.updateSchedule') }}";
    var showCart = "{{ route('showCart') }}";
    var update_addons_in_cart = "{{ route('addToCartAddons') }}";
    var vendor_products_page_search_url = "{{ route('vendorProductsSearchResults') }}";
    var get_last_added_product_variant_url = "{{ route('getLastAddedProductVariant') }}";
    var get_product_variant_with_different_addons_url = "{{ route('getProductVariantWithDifferentAddons') }}"
    var addonids = [];
    var addonoptids = [];
    var ajaxCall = 'ToCancelPrevReq';


    $(document).on('click', '.show_subet_addeon', function(e) {
        e.preventDefault();
        var show_class = $(this).data("div_id_show");
        $(this).addClass("d-none");
        $("#" + show_class).removeClass("d-none");
    });

    $(document).delegate('.changeVariant', 'change', function() {
        var variants = [];
        var options = [];
        var product_variant_url = "{{ route('productVariant', ':sku') }}";
        var sku = $(this).parents('.product_row').attr('data-p_sku');
        var that = this;
        $(that).parents('.product_row').find('.changeVariant').each(function() {
            if (this.val != '') {
                variants.push($(this).attr('vid'));
                options.push($(this).val());
            }
        });
        // console.log(variants);
        // console.log(options);
        // return 0;
        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: product_variant_url.replace(":sku", sku),
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
            success: function(response) {
                if (response.status == 'Success') {
                    response = response.data;
                    $(that).parents('.product_row').find(".variant_response span").html('');
                    if (response.variant != '') {

                        $(that).parents('.product_row').find(".add-cart-btn").attr(
                            'data-variant_id', response.variant.id);

                        $(that).parents('.product_row').find('.product_price').html('');
                        let variant_template = _.template($('#variant_template').html());
                        $(that).parents('.product_row').find('.product_price').append(
                            variant_template({
                                variant: response.variant
                            }));

                        $(that).parents('.product_row').find('.product_variant_quantity_wrapper')
                            .html('');
                        let variant_quantity_template = _.template($('#variant_quantity_template')
                            .html());
                        $(that).parents('.product_row').find('.product_variant_quantity_wrapper')
                            .append(variant_quantity_template({
                                variant: response.variant
                            }));

                        let variant_image_template = _.template($('#variant_image_template')
                            .html());

                        $(that).parents('.product_row').find('.product_image').html('');
                        $(that).parents('.product_row').find('.product_image').append(
                            variant_image_template({
                                media: response.variant
                            }));
                    }
                } else {
                    $(that).parents('.product_row').find(".variant_response span").html(response
                        .message);
                    $(that).parents('.product_row').find(".add-cart-btn").hide();
                    $(that).parents('.product_row').find(
                        ".product_variant_quantity_wrapper .text-danger").remove();
                }
            },
            error: function(data) {

            },
        });
    });

    $(document).delegate("#vendor_search_box", "input", function() {
        let keyword = $(this).val();
        vendorProductsSearchResults();
    });

    function vendorProductsSearchResults() {
        let keyword = $("#vendor_search_box").val();
        let order_type = $("#order_type").val();
        var checkboxesChecked = [];
        $("input:checkbox[name=tag_id]:checked").each(function() {
            checkboxesChecked.push($(this).val());
        });
        var checkedvalus = checkboxesChecked.length > 0 ? checkboxesChecked : null;
        // if (keyword.length > 2 || keyword.length == 0) {
        ajaxCall = $.ajax({
            type: "post",
            dataType: 'json',
            url: vendor_products_page_search_url,
            data: {
                tag_id: checkedvalus,
                keyword: keyword,
                order_type: order_type,
                vendor: "{{ $vendor->id }}",
                vendor_category: "{{ $vendor_category ?? '' }}"
            },
            beforeSend: function() {
                if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
            },
            success: function(response) {
                if (response.status == 'Success') {
                    var cart_html = $('.vendor-products-wrapper #header_cart_main_ul_ondemand').html();
                    $('.vendor-products-wrapper').html(response.html);
                    $('.vendor-products-wrapper #header_cart_main_ul_ondemand').html(cart_html);
                    addReadMoreLink();
                }
            }
        });
        // }
    }
</script>
@endsection
