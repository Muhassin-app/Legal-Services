{{-- Schedual code Start at down --}}

    @if ($cart_details->is_long_term_service != 1 &&
        ($cart_details->closed_store_order_scheduled == 1 || $client_preference_detail->off_scheduling_at_cart != 1) &&
        $cart_details->vendorCnt == 1 &&
        !in_array($serviceType, ['appointment', 'on_demand']))
        @if ($client_preference_detail->business_type != 'laundry')
            <div class="row arabic-lng position-relative my-3" id="dateredio">
                <div class=" col-md-12 mb-2 mb-md-0 text-right">
                    <div class="login-form col schedule_btn">
                        <ul
                            class="list-inline ml-auto d-flex align-items-center justify-content-end">
                            <li class="d-inline-block mr-1">
                                <input type="hidden" class="custom-control-input check"
                                    id="vendor_id" name="vendor_id"
                                    value="{{ $cart_details->vendor_id }}">
                                <input type="hidden" class="custom-control-input check"
                                    id="tasknow" name="task_type"
                                    value="{{ $cart_details->schedule_type == 'schedule' ? 'schedule' : 'now' }}">
                            </li>
                            @if ($cart_details->delay_date == 0)
                            @endif
                            <li class="d-inline-block ">
                                <input type="radio"
                                    class="custom-control-input check taskschedulebtn"
                                    id="taskschedule" name="tasktype" value=""
                                    {{( $cart_details->schedule_type == 'schedule' || $cart_details->is_vendor_closed != 1 ) ? 'checked' : '' }}
                                    style="{{ ( $cart_details->schedule_type == 'schedule' || $cart_details->is_vendor_closed != 1 ) ? '' : 'display:none!important' }}">
                                <label class="btn btn-solid mb-0 taskschedulebtn"
                                    for="taskschedule"
                                    style="{{ ( $cart_details->schedule_type == 'schedule' || $cart_details->is_vendor_closed != 1 ) ? '' : 'display:none!important' }}">{{ __('Schedule') }}</label>
                            </li>
                            @if ($cart_details->closed_store_order_scheduled != 1 && $cart_details->deliver_status == 0)
                                <li class="close-window">
                                    <i class="fa fa-times cross" aria-hidden="true"></i>
                                </li>
                            @else
                                <li class="close-window">
                                    <i class="fa fa-times cross"
                                        style="display:none!important" aria-hidden="true"></i>
                                </li>
                            @endif
                        </ul>
                        <div class=" col-sm-12 p-0 pull-right datenow d-flex align-items-center justify-content-end text-right mr-1 {{ $cart_details->schedule_type.'  '.$cart_details->is_vendor_closed }}"
                            id="schedule_div"
                            style="{{( $cart_details->schedule_type == 'schedule' && $cart_details->is_vendor_closed == 1 )? '' : 'display:none!important' }}">


                            @if ($cart_details->slotsCnt == 0)
                            
                                    <input type="datetime-local" min="<?=date('Y-m-d H:i')?>" id="schedule_datetime"
                                        class="form-control" placeholder="Inline calendar"
                                        value="{{ $cart_details->schedule_type == 'schedule' ? $cart_details->scheduled_date_time : '' }}"
                                        min="{{ $cart_details->delay_date != '0' ? $cart_details->delay_date : '' }}">
                            @else
                                <input type="date" id="schedule_datetime"
                                    class="form-control schedule_datetime"
                                    placeholder="Inline calendar"
                                    value="{{ !empty($cart_details->scheduled_date_time) ? $cart_details->scheduled_date_time : $cart_details->delay_date }}"
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
                    </div>
                </div>

            </div>
        @endif
    @endif

    {{-- Schedual code end at down --}}