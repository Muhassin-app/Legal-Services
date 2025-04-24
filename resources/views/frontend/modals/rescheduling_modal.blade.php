<div class="row">
    <div class="col-md-12 mb-3">
        <div class="order_detail order_detail_data align-items-top pb-3 card-box no-gutters mb-0">
            @if ($vendor->delivery_fee > 0 || !empty($order->scheduled_date_time) || $order->luxury_option_id > 0)
                <div class="progress-order font-12  d-flex align-items-center justify-content-between pr-2">
                    @if ($order->luxury_option_id > 0)
                        @php
                            $luxury_option = \App\Models\LuxuryOption::where('id', $order->luxury_option_id)->first();
                            if ($luxury_option->title == 'takeaway') {
                                $luxury_option_name = getNomenclatureName('Takeaway', Session::get('customerLanguage'), false);
                            } elseif ($luxury_option->title == 'dine_in') {
                                $luxury_option_name = 'Dine-In';
                            } else {        
                                //$luxury_option_name = 'Delivery';
                                $luxury_option_name = getNomenclatureName($luxury_option->title);
                            }
                        @endphp
                        <span
                            class="badge badge-info ml-2 my-1">{{ __($luxury_option_name) }}</span>
                    @endif

                    @if (!empty($order->scheduled_date_time))
                        <span class="badge badge-success ml-2">Scheduled</span>
                        <span class="ml-2 text-right">
                            Slots: 
                            @if($clientPreference->scheduling_with_slots == 1 && $clientPreference->business_type == 'laundry')
                                {{'Pickup: '. date('Y-m-d', strtotime(dateTimeInUserTimeZone($order->schedule_pickup, $timezone))).' '.$order->scheduled_slot.' | ' }}

                                @if ($order->dropoff_scheduled_slot != "")
                                    {{'Dropoff: '.date('Y-m-d', strtotime(dateTimeInUserTimeZone($order->schedule_dropoff, $timezone))).' '.$order->dropoff_scheduled_slot }}
                                @else
                                    Dropoff: N/A
                                @endif
                            @else
                                {{ (($order->scheduled_slot)?dateTimeInUserTimeZone($order->scheduled_date_time, $timezone,true,false,false).'. Slot: '.$order->scheduled_slot:dateTimeInUserTimeZone($order->scheduled_date_time, $timezone) ) }}
                            @endif
                        </span>
                    @elseif(!empty($vendor->ETA))
                        <span class="ml-2">{{__('Your
                            order will arrive by')}}
                            {{ $vendor->ETA }}</span>
                    @endif
                    @if ($order->is_gift == '1')
                        <div class="gifted-icon">
                            <img class="p-1 align-middle"
                                src="{{ asset('assets/images/gifts_icon.png') }}"
                                alt="">
                            <span
                                class="align-middle">This
                                is a gift.</span>
                        </div>
                    @endif
                </div>
            @endif
            <span class="left_arrow pulse"></span>
            <div class="row">
                <div class="cart_response d-none">
                    <div class="alert p-0" style="font-size:12px;" role="alert"></div>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <label style="color:#6c757d;" for="">{{__('Schedule Pickup ')}}</label> <span class="loaderforjs"><img class="img-fluid" style="display:none;" id="loaderforjs" src="{{asset('front-assets/images/loading.gif')}}" alt=""></span>
                            <div class="row" id="pickup_schedule_datetime_{{$order->id}}">
                                <?php
                                  $vendor_id = $order->vendors->first()->vendor_id;
                                  $duration = \App\Models\Vendor::where('id',$vendor_id)->select('slot_minutes')->first();
                                  if($clientPreference->same_day_orders_for_rescheduing == 1 && $clientPreference->business_type == 'laundry'){
                                        $pickupDate = date('Y-m-d');
                                        $slotsForPickup  = (object)showSlot(date('Y-m-d'),$vendor_id,'delivery',$duration->slot_minutes,1);
                                        $dropoffDate = date('Y-m-d');
                                        $slotsForDropoff = (object)showSlot(date('Y-m-d'),$vendor_id,'delivery',$duration->slot_minutes,2); 
                                  }else{
                                        $pickupDate = date('Y-m-d',strtotime('+1 day'));
                                        $slotsForPickup  = (object)showSlot(date('Y-m-d',strtotime('+1 day')),$vendor_id,'delivery',$duration->slot_minutes,1);
                                        $dropoffDate = date('Y-m-d',strtotime('+2 day'));
                                        $slotsForDropoff = (object)showSlot(date('Y-m-d',strtotime('+2 day')),$vendor_id,'delivery',$duration->slot_minutes,2); 
                                  }
                                ?>
                                <div class="col-md-6">
                                    <input type="hidden" class="custom-control-input check" id="vendor_id" name="vendor_id" value="{{$order->vendors->first()->vendor_id}}" >
                                        <input type="date" id="pickup_schedule_datetime_re" name="pickup_schedule_datetime" class="form-control pickup_schedule_datetime_re {{$newPickupClass}}" placeholder="Inline calendar" value="{{$pickupDate}}" min="{{$pickupDate}}" >
                                    <input type="hidden" id="checkPickUpSlot" value="1">
                                </div>
                                <div class="col-md-6 schedule_pickup_slot" id="schedule_pickup_slot_parent_{{$order->id}}">
                                    <select name="schedule_pickup_slot" id="schedule_pickup_slot_{{$order->id}}" class="form-control schedule_pickup_slot_select">
                                        <option value="" selected>{{__("Select Slot")}} </option>
                                            @foreach ($slotsForPickup as $slotForPickup)
                                                <option value="{{$slotForPickup['value']}}">{{$slotForPickup['name']}}</option>
                                            @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label style="color:#6c757d;" for="">{{__('Schedule Dropoff ')}} </label> <span class="loaderfordrop"><img class="img-fluid" style="display:none;" id="loaderfordrop" src="{{asset('front-assets/images/loading.gif')}}" alt=""></span>
                            <div class="row" id="dropoff_schedule_datetime_{{$order->id}}">
                                <div class="col-md-6">
                                    <input type="date" id="dropoff_schedule_datetime_re" name="dropoff_schedule_datetime" class="form-control dropoff_schedule_datetime {{$newDropoffClass}}" placeholder="Inline calendar" value="{{$dropoffDate}}" min="{{$dropoffDate}}" >
                                    <input type="hidden" id="checkDropoffSlot" value="1">
                                </div>
                                <div class="col-md-6 schedule_dropoff_slot" id="schedule_dropoff_slot_parent_{{$order->id}}">
                                    <select name="schedule_dropoff_slot" id="schedule_dropoff_slot_{{$order->id}}" class="form-control">
                                        <option value="" selected>{{__("Select Slot")}} </option>
                                        @foreach ($slotsForDropoff as $slotForDropoff)
                                            <option value="{{$slotForDropoff['value']}}">{{$slotForDropoff['name']}}</option>
                                         @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>