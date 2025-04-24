@if(!empty($product_delivery_slots) && $product_delivery_slots->count() > 0)
    @foreach ($product_delivery_slots as $delivery_slot)
        <div class="form-group">
            <label>
                <input type="radio" name="delivery_slot_id" class="form-control delivery_slot" value="{{$delivery_slot->deliverySlot->id}}" data-price="{{$delivery_slot->deliverySlot->price}}" data-slot-text="{{ $delivery_slot->deliverySlot->title.' ( '.$delivery_slot->deliverySlot->start_time.' - '.$delivery_slot->deliverySlot->end_time.' '.Session::get('currencySymbol').$delivery_slot->deliverySlot->price.' )' }}"/>
                <span>{{ $delivery_slot->deliverySlot->title.' ( '.$delivery_slot->deliverySlot->start_time.' - '.$delivery_slot->deliverySlot->end_time.' '.Session::get('currencySymbol').$delivery_slot->deliverySlot->price.' )' }}</span>
            </label>
        </div> 
    @endforeach
    @else
    <div class="slot-not-found text-center">No slots available.</div>
@endif
