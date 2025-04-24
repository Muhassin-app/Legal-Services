@if(!empty($product_delivery_slots_interval) && $product_delivery_slots_interval->count() > 0)
    @foreach ($product_delivery_slots_interval as $slot)
        <div class="form-group">
            <label>
                <input type="radio" name="delivery_slot_id" class="form-control delivery_slot_interval" value="{{$slot->id}}" data-price="{{$slot->price}}" data-slot-text="{{ $slot->title.' ( '.$slot->start_time.' - '.$slot->end_time.' '.Session::get('currencySymbol').$slot->price.' )' }}"/>
                <span>{{ $slot->title.' ( '.$slot->start_time.' - '.$slot->end_time.' '.Session::get('currencySymbol').$slot->price.' )' }}</span>
            </label>
        </div> 
    @endforeach
    @else
    <div class="slot-not-found text-center">No slots available.</div>
@endif
