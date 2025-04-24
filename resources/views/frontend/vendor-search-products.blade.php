@php
$add_to_cart =  route('addToCart') ;
    $additionalPreference = getAdditionalPreference(['is_token_currency_enable','is_service_product_price_from_dispatch','is_service_price_selection']);
    $getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
    $is_service_product_price_from_dispatch_forOnDemand = 0;
    $category_type_idForNotShowshPlusMinus = ['12'];
    if($getOnDemandPricingRule['is_price_from_freelancer'] ==1){
        $is_service_product_price_from_dispatch_forOnDemand =1;
        array_push($category_type_idForNotShowshPlusMinus,8);
    }

@endphp
<div class="col-sm-4 col-lg-3 border-right al_white_bg_round">
    <nav class="scrollspy-menu">
        <ul>    
            @forelse($listData as $key => $data)

            <li class="side-scroll-menu-li"><a href="#{{ str_replace(' ', '-', $data->category->slug) }}">{{ @$data->category->translation[0]->name??'' }}({{ $data->products_count }})</a></li>

            @empty
            @endforelse
        </ul>
    </nav>
</div>
<div class="col-md-8 col-lg-6 alScrollspyProduct">
    <div class="col-12 d-sm-flex justify-content-start mb-2 p-0">
        @if (isset($tags) && !empty($tags))
            @foreach ($tags as $key => $tag)
                <label class="label-switch switch-primary product_tag_filter mr-2 mb-0">
                    <input type="checkbox"
                        class="switch switch-bootstrap product_tag_filter status"
                        name="tag_id" id="product_tag_filter_{{ $key }}"
                        data-tag_id="{{ $tag->id }}" value="
                        {{ $tag->id }}" {{!is_null($tagId) && in_array($tag->id, $tagId) ? 'checked' : ''}}>
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
    <section class="scrolling_section" id="{{ str_replace(' ', '-', $data->category->slug) }}">
        @if (!empty($data->products))
            <h2 class="category-head mt-0 mb-3">
                {{ @$data->category->translation_one->name }}
                ({{ $data->products_count }})
            </h2>
            @forelse($data->products as $prod)
                <div class="row cart-box-outer al_white_bg_round product_row classes_wrapper no-gutters mb-2 pb-2 border-bottom"
                    data-p_sku="{{ $prod->sku }}"
                    data-slug="{{ $prod->url_slug }}">
                    <div class=" col-sm-2 col-4 mb-2">
                        <a target="_blank"
                            href="{{ route('productDetail', [$prod->vendor->slug, $prod->url_slug]) }}">
                            <div class="class_img product_image">
                                <img src="{{ $prod->product_image }}"
                                    alt="{{ $prod->translation_title }}">
                            </div>
                        </a>

                    </div>
                    <div class="col-sm-10 col-8  pl-md-3 pl-2">
                        <div class="row price_head">
                            <div class="col-sm-12">
                                <div
                                    class="d-flex align-items-start justify-content-between">
                                    <h5 class="mt-0">
                                        {{ $prod->translation_title }} @if ($prod->calories)
                                        ({{$prod->calories}} {{ __("calories") }})
                                    @endif 

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
                                            $redirec = ($data->is_recurring_booking ==1) ? route('productDetail', [$prod->vendor->slug, $prod->url_slug]) : 'javascript:void(0)' ;
                                            $class = ($data->is_recurring_booking ==1) ? 'add_vendor_product_btn' : 'add_vendor_product' ;
                                            
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

                                    @if ( ($is_service_product_price_from_dispatch_forOnDemand ==1) || ($vendor->is_vendor_closed == 0 || ($vendor->closed_store_order_scheduled != 0 && $checkSlot != 0)))
                                        @php
                                            $is_customizable = false;
                                            if ($isAddonExist > 0 && ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1)) {
                                                $is_customizable = true;
                                            }
                                        @endphp

                                        @if ($productVariantInCart > 0)
                                            {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                                            @if( $is_service_product_price_from_dispatch_forOnDemand ==1)
                                                <a class="btn btn-solid btn btn-solid view_on_demand_price"  style="display:none;" id="add_button_href{{$cartProductId}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                            @else
                                                <a class="add-cart-btn {{$class}}"
                                                    style="display:none;"
                                                    id="add_button_href{{ $cartProductId }}"
                                                    data-variant_id="{{ $productVariantIdInCart }}"
                                                    data-add_to_cart_url="{{ $add_to_cart }}"
                                                    data-vendor_id="{{ $vendor_id }}"
                                                    data-product_id="{{ $product_id }}"
                                                    data-addon="{{ $isAddonExist }}"
                                                    data-minimum_order_count="{{ $minimum_order_count }}"
                                                    data-batch_count="{{ $batch_count }}"
                                                    href="{{ $redirec }}">{{ __('Add') }}
                                                    @if ($minimum_order_count > 0)
                                                        ({{ $minimum_order_count }})
                                                    @endif
                                                </a>
                                            @endif
                                            @if(isset($data->category_type_id) && (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)) )
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
                                                    step="0.01"
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
                                                <a class="btn btn-solid " id="added_button_href{{$cartProductId}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                            @endif
                                        @else
                                            @if (  (in_array($data->category_type_id,[12,8]))  || ($prod->has_inventory == 0 || ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1)))
                                                @if(   $is_service_product_price_from_dispatch_forOnDemand ==1)
                                                    <a class="btn btn-solid btn btn-solid view_on_demand_price"  id="add_button_href{{$data->id }}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                @else 
                                                    {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                                                    <a class="add-cart-btn {{$class}}"
                                                        id="aadd_button_href{{ $data->id }}"
                                                        data-variant_id="{{ $data->variant[0]->id }}"
                                                        data-add_to_cart_url="{{ $add_to_cart }}"
                                                        data-vendor_id="{{ $data->vendor_id }}"
                                                        data-product_id="{{ $data->id }}"
                                                        data-addon="{{ $isAddonExist }}"
                                                        data-batch_count="{{ $batch_count }}"
                                                        data-minimum_order_count="{{ $minimum_order_count }}"
                                                        href="{{ $redirec }}">{{ __('Add') }}
                                                        @if ($minimum_order_count > 1)
                                                            ({{ $minimum_order_count }})
                                                        @endif
                                                    </a>
                                                @endif
                                                @if(isset($data->category_type_id) && (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)) )
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
                                                    <a class="btn btn-solid "  style="display:none;" id="added_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                @endif
                                            @else
                                                <span
                                                    class="text-danger">{{ __('Out of stock') }}</span>
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
                            @if ($prod->averageRating > 0 && $client_preference_detail->rating_check == 1)
                                <div class="rating-text-box">
                                    <span>{{ number_format($prod->averageRating, 1, '.', '') }}
                                    </span>
                                    <i class="fa fa-star"
                                        aria-hidden="true"></i>
                                </div>
                            @endif

                            @if ($prod->minimum_order_count > 0)
                                {{-- <p class="mb-1 product_price">   {{__('Minimum Quantity') }} : {{ $prod->minimum_order_count }} </p>
                                <p class="mb-1 product_price">   {{__('Batch') }} : {{ $prod->batch_count }} </p> --}}
                            @endif

                            <p class="mb-1 product_price">
                                @if($is_service_product_price_from_dispatch_forOnDemand !=1) 
                                {{-- price  not showing in vencor type in on demand and get price from dispatche--}}
                                    @if($additionalPreference ['is_token_currency_enable'])
                                        {!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{getInToken($prod->variant_price * $prod->variant_multiplier)}}
                                    @else
                                        {{ Session::get('currencySymbol') . decimal_format($prod->variant_price * $prod->variant_multiplier) }}
                                    @endif
                                    @if ($prod->variant[0]->compare_at_price > 0)
                                        @if($additionalPreference ['is_token_currency_enable'])
                                            <span class="org_price ml-1 font-14">{!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}{{getInToken($prod->variant[0]->compare_at_price * $prod->variant_multiplier)}}</span>
                                        @else
                                            <span class="org_price ml-1 font-14">{{ Session::get('currencySymbol') .decimal_format($prod->variant[0]->compare_at_price * $prod->variant_multiplier) }}</span>
                                        @endif
                                    @endif
                                @endif

                            </p>
                            <div class="member_no d-block mb-0">

                                <span>{!! strlen($prod->translation_description) > 140 ? substr($prod->translation_description, 0, 140) : $prod->translation_description !!}
                                @if(strlen($prod->translation_description) > 140)
                                    <span class="moreellipses">...&nbsp;</span>
                                    <span class="morecontent"><span style="display:none;">
                                        {!! substr($prod->translation_description, 140) !!} </span>&nbsp;&nbsp;<a href="" class="morelink">Read more</a></span></span>
                                @endif
                            </div>
                            <div id="product_variant_options_wrapper">
                                @if (!empty($prod->variantSet))
                                    @php
                                        $selectedVariant = $productVariantIdInCart > 0 ? $productVariantIdInCart : $prod->variant_id;
                                    @endphp
                                    @foreach ($prod->variantSet as $key => $variant)
                                        @if ($variant->type == 1 || $variant->type == 2)
                                            <?php $var_id = $variant->variant_type_id; ?>
                                            <select
                                                name="{{ 'var_' . $var_id }}"
                                                vid="{{ $var_id }}"
                                                class="changeVariant dataVar{{ $var_id }}">
                                                <option value="" disabled>
                                                    {{ $variant->title }}
                                                </option>
                                                @foreach ($variant->option2 as $k => $optn)
                                                    <?php
                                                    $opt_id = $optn->variant_option_id;
                                                    $selected = $selectedVariant == $optn->product_variant_id ? 'selected' : '';
                                                    ?>
                                                    <option
                                                        value="{{ $opt_id }}"
                                                        {{ $selected }}>
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
                                <span
                                    class="text-danger mb-2 mt-2 font-14"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
        @endforelse
    @else
        <h4 class="mt-3 mb-3 text-center">{{__('No product found')}}</h4>
    @endif
</section>
@empty
    <h4 class="mt-3 mb-3 text-center">{{__('No product found')}}</h4>
@endforelse

</div>
{{--@endif--}}
        <div class="col-12 col-lg-3 d-lg-inline-block d-none">
            <div class="card-box p-0 cart-main-box">
                <div class="p-2 d-flex align-items-center justify-content-between border-bottom">
                    <h4 class="right-card-title">{{ __('Cart') }}</h4>
                </div>
                <div class="cart-main-box-inside d-flex align-items-center">
                    <div class="spinner-box"  style="display:none">
                        <div class="circle-border">
                            <div class="circle-core"></div>
                        </div>
                    </div>
                    <div class="show-div shopping-cart flex-fill w-100" id="header_cart_main_ul_ondemand"></div>
                </div>
            </div>
        </div>
        <script>
            $(".side-scroll-menu-li").click(function(){
                $("body").removeClass("overflow-hidden");
                $(".scrollspy-menu").removeClass("side-menu-open");
                $(".manu-bars").removeClass("menu-btn");
             });              

            // function getVendorProduct(id)
            // {
            //     vendorProductsSearchResults(id);
            // }
            </script>        
        