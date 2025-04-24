
<section class="section-b-space ratio_asos">
    <div class="collection-wrapper">
        <div class="container">
            <div class="position-relative">
                <div class="categories-product-list">
                    <div class="row">
                        <div class="col-12">
                            <div class="row vendor-products-wrapper">
                                <div class="col-md-12 col-lg-12">
                                    @forelse($vendors as $vendor)
                                        <section class="scrolling_section" id="{{ $vendor->slug }}">
                                            <?php $productDetails = \App\Models\Product::where('vendor_id', $vendor->id)->get(); ?>
                                            @if (!empty($vendor->products))
                                          
                                                <h2 class="category-head mt-0 mb-1" style="position:initial;">
                                                    {{ $vendor->name }}
                                                    ({{ $vendor->products->count() }}) </h2>
                                                @forelse($vendor->products as $prod)
                                                    <?php
                                                        $price = 0; $addon_id = array(); $option_id = array();
                                                        foreach($prod->sets as $set){
                                                            array_push($addon_id, $set->addon_id);
                                                            $price = $set->setoptions->sum('price');

                                                            foreach($set->setoptions as $key => $option){
                                                                array_push($option_id, $option->id );
                                                            }
                                                        } 

                                                        $addon_id_new = implode(',',$addon_id);
                                                        $option_id_new = implode(',',array_unique($option_id));
                                                    ?>
                                                    <div class="row cart-box-outer product_row classes_wrapper no-gutters mb-1"
                                                        data-p_sku="{{ $prod->sku }}"
                                                        data-slug="{{ $prod->url_slug }}">
                                                        <div class="col-2">
                                                            <a target="_blank"
                                                                href="{{ route('productDetail', [$prod->vendor->slug, $prod->url_slug]) }}">
                                                                <div class="class_img product_image">
                                                                    @php
                                                                        $image = $prod->media->isNotEmpty() ? $prod->media->first()->image->path['image_fit'] . '300/300' . $prod->media->first()->image->path['image_path'] : \App\Http\Controllers\Front\FrontController::loadDefaultImage();
                                                                    @endphp
                                                                    <img src="{{ $image }}"
                                                                        alt="{{ $prod->title }}">
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="col-10">
                                                            <div class="row price_head pl-2">
                                                                <div class="col-sm-12 pl-2">
                                                                    <div
                                                                        class="d-flex align-items-start justify-content-between">
                                                                        <h5 class="mt-0">
                                                                            {{ $prod->title }}
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
                                                                                $product_id = $prod->id;
                                                                                $variant_id = $prod->variant->isNotEmpty() ? $prod->variant->first()->id : 0;
                                                                                // $prod->variant[0] ? $prod->variant[0]->id : 0;
                                                                                $variant_price = $prod->variant->isNotEmpty() ? $prod->variant->first()->price : 0;
                                                                                $variant_quantity = $prod->variant->isNotEmpty() ? $prod->variant->first()->quantity : 0;
                                                                                $isAddonExist = 0;
                                                                                $minimum_order_count = $prod->minimum_order_count;
                                                                                $batch_count = $prod->batch_count;
                                                                                if (count($prod->addOn) > 0) {
                                                                                    $isAddonExist = 1;
                                                                                }
                                                                            @endphp

                                                                            @foreach ($prod->variant as $var)
                                                                                @if (isset($var->checkIfInCart) && count($var->checkIfInCart) > 0)
                                                                                    @php
                                                                                        $productVariantInCart = 1;
                                                                                        $productVariantIdInCart = $var->checkIfInCart['0']['variant_id'];
                                                                                        $cartProductId = $var->checkIfInCart['0']['id'];
                                                                                        $cart_id = $var->checkIfInCart['0']['cart_id'];
                                                                                        // $variant_quantity = $var->checkIfInCart['0']['quantity'];
                                                                                        $variant_quantity = 0;
                                                                                        $vendor_id = $prod->vendor_id;
                                                                                        $product_id = $prod->id;
                                                                                        $batch_count = $prod->batch_count;
                                                                                        $variant_price = $var->price * $prod->variant_multiplier;
                                                                                        if (count($var->checkIfInCart) > 1) {
                                                                                            $productVariantInCartWithDifferentAddons = 1;
                                                                                        }
                                                                                        foreach ($var->checkIfInCart as $cartVar) {
                                                                                            $variant_quantity = $variant_quantity + $cartVar['quantity'];
                                                                                        }
                                                                                    @endphp
                                                                                @break;
                                                                            @endif
                                                @endforeach

                                                @if ($vendor->is_vendor_closed == 0)
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
                                                            data-vendor_id="{{ $vendor_id }}"
                                                            data-addon_id="{{$vendor_id}}"
                                                            data-product_id="{{ $product_id }}"
                                                            data-addon="{{ $isAddonExist }}"
                                                            data-minimum_order_count="{{ $minimum_order_count }}"
                                                            data-batch_count="{{ $batch_count }}"
                                                            href="javascript:void(0)">{{ __('Add') }}
                                                            @if ($minimum_order_count > 0) ({{ $minimum_order_count }}) @endif
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
                                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                                            </span>
                                                            <input
                                                                style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                                placeholder="1" type="text"
                                                                value="{{ $variant_quantity }}"
                                                                class="input-number" step="0.01"
                                                                id="quantity_ondemand_{{ $cartProductId }}" readonly>
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
                                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                                            </span>
                                                        </div>
                                                    @else

                                                        @if ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1)
                                                            {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                                                            <a class="add-cart-btn add_real_cart"
                                                                id="aadd_button_href{{ $prod->id }}"
                                                                data-variant_id="{{ $prod->variant[0]->id }}"
                                                                data-add_to_cart_url="{{ route('addToCart') }}"
                                                                data-vendor_id="{{ $prod->vendor_id }}"
                                                                data-product_id="{{ $prod->id }}"
                                                                data-addon="{{ $addon_id_new }}"
                                                                data-option_id="{{ $option_id_new }}"
                                                                data-batch_count="{{ $batch_count }}"
                                                                data-minimum_order_count="{{ $minimum_order_count }}"
                                                                href="javascript:void(0)">{{ __('Add') }}@if ($minimum_order_count > 1) ({{ $minimum_order_count }}) @endif</a>
                                                            <div class="number" style="display:none;"
                                                                id="ashow_plus_minus{{ $data->id }}">
                                                                <span class="minus qty-minus-product"
                                                                    data-parent_div_id="show_plus_minus{{ $prod->id }}"
                                                                    data-id="{{ $prod->id }}"
                                                                    data-base_price="{{ $prod->variant_price * $prod->variant_multiplier }}"
                                                                    data-vendor_id="{{ $prod->vendor_id }}"
                                                                    data-batch_count="{{ $batch_count }}"
                                                                    data-minimum_order_count="{{ $minimum_order_count }}">
                                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                                </span>
                                                                <input
                                                                    style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                                    id="quantity_ondemand_d{{ $prod->id }}"
                                                                    readonly placeholder="{{ $minimum_order_count }}"
                                                                    type="text" value="{{ $minimum_order_count }}"
                                                                    class="input-number input_qty" step="0.01">
                                                                <span class="plus qty-plus-product" data-id=""
                                                                    data-base_price="{{ $prod->variant_price * $prod->variant_multiplier }}"
                                                                    data-vendor_id="{{ $prod->vendor_id }}"
                                                                    data-batch_count="{{ $batch_count }}"
                                                                    data-minimum_order_count="{{ $minimum_order_count }}">
                                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                                </span>
                                                            </div>
                                                        @else
                                                            <span
                                                                class="text-danger">{{ __('Out of stock') }}</span>
                                                        @endif
                                                    @endif
                                                @endif
                                </div>
                            </div>


                            <div class="rating-text-box">
                                <span style="font-size: 8px; margin-right:0px;">{{ ($prod->match != "") ? $prod->match : 'Partial Match'; }} </span>

                            </div>



                            <p class="mb-1 product_price">
                                 {{ Session::get('currencySymbol') . number_format($prod->variant_price+$price * $prod->variant_multiplier, 2, '.', '') }}
                                @if ($prod->variant[0]->compare_at_price > 0)
                                    <span
                                        class="org_price ml-1 font-14">{{ Session::get('currencySymbol') .number_format($prod->variant[0]->compare_at_price * $prod->variant_multiplier, 2, '.', '') }}</span>
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
                                                <option value="" disabled>{{ $variant->title }}</option>
                                                @foreach ($variant->option2 as $k => $optn)
                                                    <?php
                                                    $opt_id = $optn->variant_option_id;
                                                    $selected = $selectedVariant == $optn->product_variant_id ? 'selected' : '';
                                                    ?>
                                                    <option value="{{ $opt_id }}" {{ $selected }}>
                                                        {{ $optn->title }}</option>
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
                @endforelse
            @else
                <h4 class="mt-3 mb-3 text-center">No product found</h4>
                @endif
    </section>
@empty
    <h4 class="mt-3 mb-3 text-center">No product found</h4>
    @endforelse
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </section>
    
