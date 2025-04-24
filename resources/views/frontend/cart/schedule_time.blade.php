<div class=" col-sm-12 p-0 pull-right datenow d-flex align-items-center justify-content-end text-right mr-1"
id="schedule_div"
style="{{ $cart_details->schedule_type == 'schedule' ? '' : 'display:none!important' }}">
     @if ($cart_details->slotsCnt == 0)
             <input type="datetime-local" id="schedule_datetime"
                    class="form-control" placeholder="Inline calendar"
                    value="{{ $cart_details->schedule_type == 'schedule' ? $cart_details->scheduled_date_time : '' }}"
                    min="{{ $cart_details->delay_date != '0' ? $cart_details->delay_date : '' }}">
        
        @else
            <input type="date" id="schedule_datetime"
                class="form-control schedule_datetime"
                placeholder="Inline calendar"
                value="{{ $cart_details->scheduled_date_time != '' ? $cart_details->scheduled_date_time : $cart_details->delay_date }}"
                min="{{ $cart_details->delay_date }}">
            <input type="hidden" id="checkSlot" value="1">
            <select name="slots" id="slot"
                onchange="checkSlotOrders();" class="form-control">
                <option value="">{{ __('Select Slot') }} </option>
                @foreach ($cart_details->slots as $slot)
                    <option value="{{ $slot->value }}"
                        {{ $slot->value == $cart_details->scheduled->slot ? 'selected' : '' }}>
                        {{ $slot->name }}</option>
                @endforeach
            </select>
        @endif
</div>