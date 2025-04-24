<h4 class="mt-4 mb-2"><b>{{ __('What time would you like us to start?') }}</b></h4>
<div class="booking-timea  radio-btns long-radio">   
    <div class="alCustomHomeServiceRadio SlotItems items">
        @foreach ($time_slots as $key => $slot)
            <div class="item  vendor_slot  {{ ($slot['value'] ==@$schedule_slot) ? 'checked_item': ''  }}">
                <div class="radios ">
                    <input type="radio" value="{{ $slot['value'] }}" name='booking_time_{{$cart_product_id}}' id='time{{$cart_product_id}}{{$key+1}}'  {{ ($slot['value'] ==@$schedule_slot) ? 'checked': ''  }}  class="booking_time {{ ($slot['value'] ==@$schedule_slot) ? 'ondemand_checked': ''  }} "/>          
                    <label for='time{{$cart_product_id}}{{$key+1}}'><span class="customCheckbox selected-time" aria-hidden="true"  data-value="{{ $slot['value'] }}" data-cart_product_id='{{$cart_product_id}}'>{{ $slot['name']  }}</span></label>
                    
                </div>
            </div>
        @endforeach
    </div>
</div>
<P id="message_of_time{{$cart_product_id}}"></P>
