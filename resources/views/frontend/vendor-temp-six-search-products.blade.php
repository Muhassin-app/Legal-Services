<div class="col-sm-4 col-lg-3 border-right al_white_bg_round">
    <nav class="scrollspy-menu">
        <ul>
            @forelse($listData as $key => $data)
                <li><a data-slug="{{ $data->category->slug??'#' }}" style="cursor: pointer;">{{ @$data->category->translation[0]->name??'' }}({{ $data->products_count }})</a>
                </li>
            @empty
            @endforelse
        </ul>
    </nav>
</div>
<div class="col-md-8 col-lg-6 alScrollspyProduct">
        {{-- <div class="row mt-2 d-flex align-items-start mb-sm-2 justify-content-center">
            <div class="col-7 vendor-search-bar mb-sm-0 mb-2">
                <div class="radius-bar w-100">
                    <div class="search_form d-flex align-items-center border">
                        <button class="btn"><i class="fa fa-search" aria-hidden="true"></i></button>
                        <input class="form-control border-0 typeahead" type="search"
                            placeholder="{{ __('Search') }}" id="vendor_search_box" value="{{$input['keyword']??''}}">
                    </div>
                    <div class="list-box style-4" style="display:none;" id="search_box_main_div">
                    </div>
                </div>
            </div>
            <div class="col-5 text-right pl-0"><span class="d-lg-inline-block d-none"> {{ __('Sort By') }} :</span>
                <select name="order_type" id='order_type' class="product_tag_filter p-1">
                    <option value="">{{__('Please Select')}}</option>
                    <option value="newly_added" {{isset($input['order_type']) && $input['order_type'] == "newly_added" ? 'selected' : ''}}>{{__('Newest Arrivals')}}</option>
                    <option value="featured" {{isset($input['order_type']) && $input['order_type'] == "featured" ? 'selected' : ''}}>{{__('Featured')}}</option>
                    <option value="a_to_z" {{isset($input['order_type']) && $input['order_type'] == "a_to_z" ? 'selected' : ''}}>{{__('A to Z')}}</option>
                    <option value="z_to_a" {{isset($input['order_type']) && $input['order_type'] == "z_to_a" ? 'selected' : ''}}>{{__('Z to A')}}</option>
                    <option value="low_to_high" {{isset($input['order_type']) && $input['order_type'] == "low_to_high" ? 'selected' : ''}}>{{__('Cost : Low to High')}}</option>
                    <option value="high_to_low" {{isset($input['order_type']) && $input['order_type'] == "high_to_low" ? 'selected' : ''}}>{{__('Cost : High to Low')}}</option>
                    <option value="rating" {{isset($input['order_type']) && $input['order_type'] == "rating" ? 'selected' : ''}}>{{__('Avg. Customer Review')}}</option>
                   
                </select>
            </div>
        </div> --}}

        <div class="col-12 d-sm-flex justify-content-start mb-2 p-0">
            @if (isset($tags) && !empty($tags))
                @foreach ($tags as $key => $tag)
                    <label class="label-switch switch-primary product_tag_filter mr-2 mb-0">
                        <input type="checkbox"
                            class="switch switch-bootstrap product_tag_filter status"
                            name="tag_id" id="product_tag_filter_{{ $key }}"
                            data-tag_id="{{ $tag->id }}" value="
                            {{ $tag->id }}">
                        <span class="lable">
                            @if (isset($tag->icon) && !empty($tag->icon))
                                <img class="ml-1"
                                    src="{{ $tag->icon['proxy_url'] . '100/100' . $tag->icon['image_path'] }}"
                                    alt="">
                            @endif <span
                                class="ml-1">{{ $tag->primary ? $tag->primary->name : '' }}</span>
                        </span>
                    </label>
                @endforeach
            @endif
        </div>
    @forelse($listData as $key => $data)
        <section class="scrolling_section " id="{{ $data->category->slug }}">
            @if (!empty($data->products))
                <h2 class="category-head mt-0 mb-3">
                    {{ $data->category->translation_one->name??'' }}
                    ({{ $data->products_count }})
                </h2>
                <div class="viewAllProductSec" >

                @forelse($data->products as $prod)

                    <div class="card mb-3 product_row"  data-p_sku="{{ $prod->sku }}"
                        data-slug="{{ $prod->url_slug }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between border-bottom">
                                <p class="m-0 productTitle"> {{ $prod->translation_title }}</p>
                                <ul class="m-0 p-0 d-flex align-items-center">
                                    <li>From</li>

                                    <li class="ml-2"><span class="productsPrice">
                                        {{ Session::get('currencySymbol') . decimal_format($prod->variant_price * $prod->variant_multiplier,',') }}
                                        @if ($prod->variant[0]->compare_at_price > 0)
                                            <span
                                                class="org_price ml-1 font-14">{{ Session::get('currencySymbol') .decimal_format($prod->variant[0]->compare_at_price * $prod->variant_multiplier) }}</span>
                                        @endif</span><br> <sup>per person (min. 1)</sup></li>
                                </ul>
                            </div>
                            <div class="productDetails pl-0 pr-lg-5 m-0 position-relative">
                                <p class="position-relative px-3 py-2">{!! $prod->translation_description !!} </p>
                            </div>
                            {{-- <ul class="productDetails pl-0 pr-lg-5 m-0 position-relative">
                                <li class="position-relative px-3 py-2">
                                    <p class="m-0">One night bed and breakfast</p>
                                </li>
                                <li class="position-relative px-3 py-2">
                                    <p class="m-0">Use of the leisure facilities</p>
                                </li>
                                <li class="position-relative px-3 py-2">
                                    <p class="m-0">Bottle of sparkling wine</p>
                                </li>
                                <li class="position-relative px-3 py-2">
                                    <p class="m-0">Robe, towel and slippers provided</p>
                                </li>
                            </ul> --}}
                            {{-- <ul class="p-0 m-0 productBookingBtns position-relative d-flex align-items-center justify-content-between">
                                <li><a href="#"><img src="{{asset('frontend/template_six/spaimages/flash_on.svg') }}"> Instant book</a></li>
                                <li class="d-flex align-items-center"><button class="alProductBtns mr-2">More details and book</button> <button class="alProductBtns outlineBtn">Buy as a gift</button></li>
                            </ul> --}}
                            <div class="productBookingBtns product_variant_quantity_wrapper">
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
                                @endphp

                                @foreach ($data->variant as $var)
                                    @if (isset($var->checkIfInCart) && count($var->checkIfInCart) > 0)
                                        @php
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
                                    <a class="add-cart-btn add_vendor_product alProductBtns"
                                        style="display:none;"
                                        id="add_button_href{{ $cartProductId }}"
                                        data-variant_id="{{ $productVariantIdInCart }}"
                                        data-add_to_cart_url="{{ route('addToCart') }}"
                                        data-vendor_id="{{ $vendor_id }}"
                                        data-product_id="{{ $product_id }}"
                                        data-addon="{{ $isAddonExist }}"
                                        data-minimum_order_count="{{ $minimum_order_count }}"
                                        data-batch_count="{{ $batch_count }}"
                                        href="javascript:void(0)">{{ __('Add') }}
                                        @if ($minimum_order_count > 0)
                                            ({{ $minimum_order_count }})
                                        @endif
                                    </a>
                                    <div class="number"
                                        id="show_plus_minus{{ $cartProductId }}">
                                        <span
                                            class="minus qty-minus-product {{ $productVariantInCartWithDifferentAddons ? 'remove-customize' : '' }}"
                                            data-variant_id="{{ $productVariantIdInCart }}"
                                            data-parent_div_id="show_plus_minus{{ $cartProductId }}"
                                            data-id="{{ $cartProductId }}"
                                            data-base_price="{{ $variant_price }}"
                                            data-vendor_id="{{ $vendor_id }}"
                                            data-product_id="{{ $product_id }}"
                                            data-cart="{{ $cart_id }}"
                                            data-addon="{{ $isAddonExist }}"
                                            data-minimum_order_count="{{ $minimum_order_count }}"
                                            data-batch_count="{{ $batch_count }}">
                                            <i class="fa fa-minus"
                                                aria-hidden="true"></i>
                                        </span>
                                        <input
                                            style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                            placeholder="1" type="text"
                                            value="{{ $variant_quantity }}"
                                            class="input-number"
                                            id="quantity_ondemand_{{ $cartProductId }}"
                                            readonly>
                                        <span
                                            class="plus qty-plus-product {{ $is_customizable ? 'repeat-customize' : '' }}"
                                            data-variant_id="{{ $productVariantIdInCart }}"
                                            data-id="{{ $cartProductId }}"
                                            data-base_price="{{ $variant_price }}"
                                            data-vendor_id="{{ $vendor_id }}"
                                            data-product_id="{{ $product_id }}"
                                            data-cart="{{ $cart_id }}"
                                            data-addon="{{ $isAddonExist }}"
                                            data-batch_count="{{ $batch_count }}">
                                            <i class="fa fa-plus"
                                                aria-hidden="true"></i>
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
                                            data-product_id="{{ $data->id }}"
                                            data-addon="{{ $isAddonExist }}"
                                            data-batch_count="{{ $batch_count }}"
                                            data-minimum_order_count="{{ $minimum_order_count }}"
                                            href="javascript:void(0)">{{ __('Add') }}
                                            @if ($minimum_order_count > 1)
                                                ({{ $minimum_order_count }})
                                            @endif
                                        </a>
                                        <div class="number"
                                            style="display:none;"
                                            id="ashow_plus_minus{{ $data->id }}">
                                            <span
                                                class="minus qty-minus-product"
                                                data-parent_div_id="show_plus_minus{{ $data->id }}"
                                                data-id="{{ $data->id }}"
                                                data-base_price="{{ decimal_format($data->variant_price * $data->variant_multiplier) }}"
                                                data-vendor_id="{{ $data->vendor_id }}"
                                                data-batch_count="{{ $batch_count }}"
                                                data-minimum_order_count="{{ $minimum_order_count }}">
                                                <i class="fa fa-minus"
                                                    aria-hidden="true"></i>
                                            </span>
                                            <input
                                                style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                id="quantity_ondemand_d{{ $data->id }}"
                                                readonly
                                                placeholder="{{ $minimum_order_count }}"
                                                type="text"
                                                value="{{ $minimum_order_count }}"
                                                class="input-number input_qty"
                                                step="0.01">
                                            <span
                                                class="plus qty-plus-product"
                                                data-id=""
                                                data-base_price="{{ decimal_format($data->variant_price * $data->variant_multiplier) }}"
                                                data-vendor_id="{{ $data->vendor_id }}"
                                                data-batch_count="{{ $batch_count }}"
                                                data-minimum_order_count="{{ $minimum_order_count }}">
                                                <i class="fa fa-plus"
                                                    aria-hidden="true"></i>
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
                    </div>
                @empty
                @endforelse
                </div>

            @else
                <h4 class="mt-3 mb-3 text-center">No product found</h4>
            @endif
        </section>
    @empty
        <h4 class="mt-3 mb-3 text-center">No product found</h4>
    @endforelse
</div>
<div class="col-12 col-lg-3 d-lg-inline-block d-none">
    <div class="card-box p-0 cart-main-box">
        <div class="p-2 d-flex align-items-center justify-content-between border-bottom">
            <h4 class="right-card-title">{{ __('Cart') }}</h4>
        </div>
        <div class="cart-main-box-inside">
            <div class="spinner-box">
                <div class="circle-border">
                    <div class="circle-core"></div>
                </div>
            </div>
            <div
                id="header_cart_main_ul_ondemand"></div>
        </div>
    </div>
</div>