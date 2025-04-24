<div id="product_variant_options_wrapper">
    @php
        $selectedVariant = isset($product->variant[0]) ? $product->variant[0]->id : 0;
        if ($product->minimum_order_count > 0) {
            $product->minimum_order_count = $product->minimum_order_count;
        } else {
            $product->minimum_order_count = 1;
        }
        $t_var = count($product->variantSet);
    @endphp
    @foreach ($product->variantSet as $key => $variant)
        @php
            // dd($product->variantSet->toArray());
            $lastIndex = count($product->variantSet) - 1;
        @endphp
        @if ($variant->type == 1 || $variant->type == 2)
            @if ($key == $lastIndex)
                <div id="variant_options">
            @endif
            <div class="size-box">
                <ul class="productVariants">
                    <li class="firstChild">{{ $variant->title }}</li>
                    <li class="row otherSize m-0">
                        @foreach ($variant->option2 as $k => $optn) 
                            <?php $var_id = $variant->variant_type_id;
                            $opt_id = $optn->variant_option_id;
                            $checked = $selectedVariant == $optn->product_variant_id ? 'checked' : '';
                            ?>
                            <label class="radio d-inline-block txt-14 col-4 position-relative pl-3 pr-2"
                                data-title="{{ $key === 0 ? $variant->title : '' }}"> 
                                <span
                                    class="color_name ellipsis">{{ $optn->title }}</span>
                                    <input id="lineRadio-{{ $opt_id }}" name="{{ 'var_' . $var_id }}"
                                    vid="{{ $var_id }}" data-option-title="{{ $variant->title }}"
                                    optid="{{ $opt_id }}" value="{{ $opt_id }}" type="radio"
                                    class="selected_variant {{ $key != $t_var - 1 ? 'changeVariant' : '' }} dataVar{{ $var_id }} changeVariant"
                                    {{ $checked }} data-row-key = {{$key}} data-varient-id="{{$optn->product_variant_id}}">
                                @if ($variant->type == 2)
                                    <span
                                        class="color_var var_{{ $var_id }} @if ($checked == 'checked') var-active radio-active @endif"
                                        style="padding:8px; border: 1px solid #CCC; background:{{ $optn->hexacode }};"
                                        data-id="{{ $var_id }}"></span>
                                @else
                                    <span
                                        class="color_var radio_var var_{{ $var_id }} @if ($checked == 'checked') var-active radio-active @endif"
                                        style="padding:8px; border: 1px solid var(--theme-deafult); background:#fff;"
                                        data-id="{{ $var_id }}"></span>
                                @endif
                                
                                {{-- <span class="checkround"></span> --}}
                            </label>
                        @endforeach
                    </li>
                </ul>
            </div>
            @if ($key == $lastIndex)
</div>
@endif
@else
@endif
@endforeach
</div>
