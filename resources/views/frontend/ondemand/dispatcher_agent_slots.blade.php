
<h4 class="mt-4 mb-2"><b>{{ __('What time would you like us to start?') }}</b></h4>
<div class="booking-tim  radio-btns long-radio">   
    <div class="grid-item main radios agent_{{ $cart_product_id }}">
        <div class="alCustomHomeServiceRadio  SlotItems items">
         @if((isset($dispatch_agents)) && (isset($dispatch_agents['slots'])) && (count($dispatch_agents['slots']) > 0) )
            @foreach ($dispatch_agents['slots'] as $key => $slot)
            @php
            $randemNo = rand(10,100);
            @endphp
                <div class="item  driver_slot {{ ($slot['value'] ==@$schedule_slot) ? 'checked_item': ''  }}">
                    <input type="radio" value='{{ $slot['value'] }}' name='booking_time_{{$cart_product_id}}' {{ ($slot['value'] ==@$schedule_slot) ? 'checked': ''  }} id='time{{$cart_product_id}}{{$slot['value']}}{{$randemNo  }}' class="booking_time"/>  
                    <label for='time{{$cart_product_id}}{{$slot['value']}}{{$randemNo }}'>
                        <span class="customCheckbox selected-time" aria-hidden="true"  data-show_agent="{{ $show_dispatcher_agent }}" data-selected_agnet_id="{{ @$selected_agent_id  }}" data-agent_ids="{{ json_encode($slot['agent_id'],TRUE)}}" data-value='{{$slot['value']}}' data-cart_product_id='{{$cart_product_id}}' >{{$slot['name']}}</span>
                    </label>
                </div>
            @endforeach
        @else
         <h5>{{ __('No Slot Available!') }}</h5>
        @endif
        </div>
    </div>
           
</div>
<div class="agent_slots{{ $cart_product_id }}" id="avail_slot">
</div>
<P id="message_of_time{{$cart_product_id}}"></P>
@section('js-script')
<script>
    var dispatch_agents = {
        agent: {!!json_encode(($dispatch_agents['agents'] ?? ''))!!}
    } 
   
</script>


@endsection