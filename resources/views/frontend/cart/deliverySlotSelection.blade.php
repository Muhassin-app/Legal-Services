<hr class="my-1">
<div class="row mb-1 pl-2 d-flex align-items-center LongTermProduct">
    <div class="product">
        <input type="hidden" name="prod_delivery_date" id="prod_delivery_date" value="{{$vendor_product->delivery_date}}"/>
        <input type="hidden" name="prod_slot_price" id="prod_slot_price" value="{{$vendor_product->slot_price}}"/>
        <input type="hidden" name="prod_slot_id" id="prod_slot_id" value="{{$vendor_product->slot_id}}"/>
        <h6 class="product-title mb-0">{{ __('Delivery Date') }}:
            <span class="ml-1">

              {{ isset($vendor_product->delivery_date) && !empty($vendor_product->delivery_date) ? $vendor_product->delivery_date : "na"  }}
            </span>
        </h5>
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="product-title mt-0 mb-0">{{ __('Delivery Slot') }}:
                <span class="ml-1">

                    @if(!empty($vendor_product->product_delivery_slot))
                        {{ $vendor_product->product_delivery_slot->title.' ( '.$vendor_product->product_delivery_slot->start_time.' - '.$vendor_product->product_delivery_slot->end_time.' )' }}
                    @endif
                </span>
            </h6>
        </div>
        <h6 class="product-title mt-0">{{ __('Slot Price') }}:
            <span class="ml-1">
                
                @if(!empty($vendor_product->product_delivery_slot))
                    {{ $vendor_product->product_delivery_slot->price }}
                @endif
            </span>
        </h6>      
    </div>
</div> 