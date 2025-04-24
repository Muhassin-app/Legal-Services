<div class="modal-header">
    <h5 class="modal-title" id="customize_repeated_itemLabel">{{__('Customization for ').$cartProducts->first()->product->translation_title}}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    @forelse($cartProducts as $cart)
        <div class="row cart-box-outer customized_product_row classes_wrapper no-gutters mb-3">
            <div class="col-2">
                <div class="class_img product_image">
                    <img src="{{ $cart->product->product_image }}" alt="{{ $cart->product->translation_title }}">
                </div>
            </div>
            <div class="col-10">
                <div class="row price_head">
                    <div class="col-sm-12">
                        <div class="d-flex align-items-start justify-content-between">
                            <h5 class="mt-0">
                                {{$cart->product->translation_title}}
                            </h5>
                            <div class="product_variant_quantity_wrapper">
                            @php
                                $data = $cart;
                                $cart_product_id = $data->id;
                                $cart_id = $data->cart_id;
                                $vendor_id = $data->vendor_id;
                                $product_id = $data->product_id;
                                $variant_id = $data->variant_id;
                                $variant_price = $data->variant_price;
                                $total_variant_price = $data->total_variant_price;
                                $variant_quantity = $data->quantity;
                            @endphp

                            @if($data->is_vendor_closed == 0)

                                <div class="number" id="show_plus_minus{{$cart_product_id}}">
                                    <span class="minus qty-minus-product remove-customize m-open"
                                        data-variant_id="{{$variant_id}}"
                                        data-parent_div_id="show_plus_minus{{$cart_product_id}}"
                                        data-id="{{$cart_product_id}}"
                                        data-base_price="{{$variant_price}}"
                                        data-vendor_id="{{$vendor_id}}"
                                        data-product_id="{{$product_id}}"
                                        data-cart="{{$cart_id}}">
                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                    </span>
                                    <input style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;" placeholder="1" type="text" value="{{$variant_quantity}}" class="input-number addon_variant_quantity_{{$cart_product_id}}" step="0.01" readonly>
                                    <span class="plus qty-plus-product repeat-customize m-open"
                                        data-variant_id="{{$variant_id}}"
                                        data-id="{{$cart_product_id}}"
                                        data-base_price="{{$variant_price}}"
                                        data-vendor_id="{{$vendor_id}}"
                                        data-product_id="{{$product_id}}"
                                        data-cart="{{$cart_id}}">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </span>
                                </div>
                            @endif
                            </div>
                        </div>
                        <div class="d-flex align-items-start justify-content-between">
                            <p class="mb-1 product_price">
                                {{Session::get('currencySymbol').(decimal_format($variant_price))}}
                            </p>
                            <p class="mb-1">
                                {{Session::get('currencySymbol')}}<span class="total_product_price">{{(decimal_format($total_variant_price))}}</span>
                            </p>
                        </div>
                        <div style="line-height:15px">
                        <small>
                            @if($cart->addon_set)
                                @foreach ($cart->addon_set as $set)
                                    {{$set->addon_set_translation_title}} :
                                    @foreach ($set->options as $option)
                                        {{$option->option_translation_title}} <br>
                                    @endforeach
                                @endforeach
                            @endif
                        </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
    @endforelse
</div>