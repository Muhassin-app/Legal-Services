
<div class="row mb-1 d-flex align-items-center " style="{{(($cart_details->schedule_type == 'schedule') ? '' : 'display:none!important')}}">
    <div class="col-sm-5 offset-sm-3 text-lg-right">
        <label class="m-0">
            {{__('Scheduled Slot')}} :</label>
    </div>
    <div class="col-sm-4 vendor_slot_cart">
        <input type="hidden" class="custom-control-input check" id="tasknow" name="task_type" value='schedule' >
        <input type="date" class="form-control vendor_product_schedule_datetime" placeholder="Inline calendar" data-schedule_type="date" data-vendor_id="{{$product->vendor_id}}" data-cart_product_id="{{$vendor_product->id}}" data-product_id="{{$vendor_product->product->id}}"    value="{{(($vendor_product->scheduled_date_time != '')?$vendor_product->scheduled_date_time : $product->delay_date ) }}"  min="{{(($vendor_product->vendorStartDate != '0') ? $vendor_product->vendorStartDate : $product->delay_date) }}" id="vendor_schedule_date_{{$vendor_product->id}}" >

            <select  class="form-control vendor_product_schedule_slot " id="vendor_schedule_slot_selecter_{{$vendor_product->id }}" data-schedule_type="time"  data-vendor_id="{{$product->vendor_id}}" data-cart_product_id="{{$vendor_product->id}}" >
                @if((isset($vendor_product->dispatchAgents)) && (isset($vendor_product->dispatchAgents->slots)) && (count((array)$vendor_product->dispatchAgents->slots) > 0) )
                    <option value="">{{__("Select Slot")}} </option>
                    @foreach($vendor_product->dispatchAgents->slots as $slot)

                        <option value="{{$slot->value}}" {{$slot->value == $product->schedule_slot ? "selected" : ""}}  data-show_agent="{{ json_encode($slot->agent_id,TRUE)}}" >{{$slot->name }}</option>
                    @endforeach
                @else
                <option value="">{{__("No Slot Available")}} </option>
                @endif
            </select>


    </div>
</div>