<div class="modal fade pickup_standard_modal" id="pickup-standard-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3 px-3 border-bottom-0 d-block">
                <button type="button" class="close" data-dismiss="modal"
                    aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="modal-title">{{ __('Book Slot') }}</h5>
            </div>
            <div class="modal-body px-3 pb-3 pt-0">
                <form class="needs-validation" name="pickup_slot-form" id="pickup_slot-event" action="{{ route('vendor.pickup.saveSlot', $vendor->id) }}" method="post">
                    @csrf
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("Start Time(24 hours format)") }}</label>
                                <input class="form-control" placeholder="Start Time" type="text" name="pickup_start_time" id="pickup_start_time" required />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("End Time(24 hours format)") }}</label>
                                <input class="form-control" placeholder="End Time" type="text" name="pickup_end_time" id="pickup_end_time" required />
                            </div>
                        </div>

                        <div class="col-md-6 pickup_slotForDiv">
                            {!! Form::label('title', 'Slot For',['class' => 'control-label']) !!}
                            <div class="form-group">
                                <ul class="list-inline">
                                    <li class="d-inline-block ml-3 mb-1 custom-radio-design">
                                        <input type="radio" class="custom-control-input check pickup_slotTypeRadio" id="pickup_slotDay" name="pickup_stot_type" value="day" checked="">
                                        <label class="custom-control-label" for="pickup_slotDay">{{ __('Days') }}&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    </li>
                                    <li class="d-inline-block ml-3 mb-1 custom-radio-design">
                                        <input type="radio" class="custom-control-input check pickup_slotTypeRadio" id="pickup_slotDate" name="pickup_stot_type" value="date">
                                        <label class="custom-control-label" for="pickup_slotDate">{{ __('Date') }}</label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="">
                                {!! Form::label('title', __('Slot Type'),['class' => 'control-label']) !!}
                            </div>
                            @if($vendor->dine_in == 1)
                                <div class="checkbox checkbox-success form-check pl-0 mb-1"  @if($client_preferences->dinein_check == 0) style="display: none;" @endif>
                                    <input name="pickup_slot_type[]" type="checkbox" id="pickup_dine_in" checked value="dine_in">
                                    <label for="pickup_dine_in"> {{ __("Dine in") }}</label>
                                </div>
                            @endif
                            @if($vendor->takeaway == 1)
                                <div class="checkbox checkbox-success form-check pl-0 mb-1"  @if($client_preferences->takeaway_check == 0) style="display: none;" @endif >
                                    <input name="pickup_slot_type[]" type="checkbox" id="pickup_takeaway" checked value="takeaway">
                                    <label for="pickup_takeaway"> {{ __("Takeaway") }} </label>
                                </div>
                            @endif
                            @if($vendor->delivery == 1)
                                <div class="checkbox checkbox-success form-check pl-0 mb-1"  @if($client_preferences->delivery_check == 0) style="display: none;" @endif>
                                    <input name="pickup_slot_type[]" type="checkbox" id="pickup_delivery" checked value="delivery">
                                    <label for="pickup_delivery"> {{ __("Delivery") }} </label>
                                </div>
                            @endif
                            @if($vendor->laundry == 1)
                                <div class="checkbox checkbox-success form-check pl-0 mb-1"  @if($client_preferences->laundry_check == 0) style="display: none;" @endif>
                                    <input name="pickup_slot_type[]" type="checkbox" id="pickup_laundry" checked value="laundry">
                                    <label for="pickup_laundry"> {{ __("Laundry") }} </label>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2 pickup_weekDays">
                        <div class="col-md-12">
                            <div class="">
                            {!! Form::label('title', __('Select days of week'),['class' => 'control-label']) !!}
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="pickup_week_day[]" type="checkbox" id="pickup_day_1" value="1">
                                <label for="pickup_day_1"> {{ __("Sunday") }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="pickup_week_day[]" type="checkbox" id="pickup_day_2" value="2">
                                <label for="pickup_day_2"> {{ __('Monday') }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="pickup_week_day[]" type="checkbox" id="pickup_day_3" value="3">
                                <label for="pickup_day_3"> {{ __("Tuesday") }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="pickup_week_day[]" type="checkbox" id="pickup_day_4" value="4">
                                <label for="pickup_day_4"> {{ __("Wednesday") }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="pickup_week_day[]" type="checkbox" id="pickup_day_5" value="5">
                                <label for="pickup_day_5"> {{ __('Thursday') }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="pickup_week_day[]" type="checkbox" id="pickup_day_6" value="6">
                                <label for="pickup_day_6"> {{ __('Friday') }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="pickup_week_day[]" type="checkbox" id="pickup_day_7" value="7">
                                <label for="pickup_day_7"> {{ __('Saturday') }} </label>
                            </div>
                        </div>
                    </div>

                    <div class="row pickup_forDate" style="display: none;">
                        <div class="col-md-12" >
                            <div class="form-group">
                                <label class="control-label">{{ __("Slot Date") }}</label>
                                <input class="form-control date-datepicker" placeholder={{ __("Select Date") }} type="text" name="pickup_slot_date" id="pickup_slot_date" required />
                            </div>
                        </div>

                    </div>
                    <div class="row mt-2">
                        <div class="col-12 d-sm-flex justify-content-between">
                            <button type="button" class="btn btn-light mr-1" data-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-info" id="pickup_btn-save-slot">{{ __('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal standard_modal fade" id="pickup_edit-slot-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3 px-3 border-bottom-0 d-block">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="modal-title">Edit Slot</h5>
                <form method="post" action="{{ route('vendor.pickup.deleteSlot', $vendor->id) }}" id="pickup_deleteSlotForm">
                    @csrf
                    <div>
                        <input type="hidden" name="pickup_slot_day_id" id="pickup_deleteSlotDayid" value="" >
                        <input type="hidden" name="pickup_slot_id" id="pickup_deleteSlotId" value="" >
                        <input type="hidden" name="pickup_slot_type" id="pickup_deleteSlotType" value="" >
                        <input type="hidden" name="pickup_old_slot_type" id="pickup_deleteSlotTypeOld" value="" >
                        <input type="hidden" name="pickup_slot_date" id="pickup_deleteSlotDate" value="" >
                       <button type="button" class="btn btn-primary-outline action-icon" style="display: none;"></button>
                    </div>
                </form>
            </div>
            <div class="modal-body px-3 pb-3 pt-0">
                <form class="needs-validation" name="pickup_slot-form" id="pickup_update-event" action="{{ route('vendor.pickup.updateSlot', $vendor->id) }}" method="post">
                    @csrf
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("Start Time(24 hours format)") }}</label>
                                <input class="form-control" placeholder={{ __("Start Time") }} type="text" name="pickup_start_time" id="pickup_edit_start_time" required />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("End Time(24 hours format)") }}</label>
                                <input class="form-control" placeholder={{ __("End Time") }} type="text" name="pickup_end_time" id="pickup_edit_end_time" required />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 slotForDiv">
                            {!! Form::label('title', __('Slot For'),['class' => 'control-label']) !!}
                            <div class="form-group">
                                <ul class="list-inline">
                                    <li class="d-block pl-1 ml-3 mb-1 custom-radio-design">
                                        <input type="radio" class="custom-control-input check slotTypeEdit" id="pickup_edit_slotDay" name="pickup_slot_type_edit" value="day" checked="">
                                        <label class="custom-control-label" id="pickup_edit_slotlabel" for="pickup_edit_slotDay">Days</label>
                                    </li>
                                    <li class="d-block pl-1 ml-1 mb-1 custom-radio-design"> &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="radio" class="custom-control-input check slotTypeEdit" id="pickup_edit_slotDate" name="pickup_slot_type_edit" value="date">
                                        <label class="custom-control-label" for="pickup_edit_slotDate">Date</label>
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-12 pickup_weekDays">
                            <div class="">
                            {!! Form::label('title', __('Slot Type'),['class' => 'control-label']) !!}
                            </div>
                            @if($vendor->dine_in == 1)
                            <div class="checkbox checkbox-success form-check pl-1 mb-1">
                                <input name="pickup_slot_type[]" type="checkbox" id="pickup_edit_dine_in" checked value="dine_in">
                                <label for="pickup_edit_dine_in"> {{ __("Dine in") }} </label>
                            </div>
                            @endif
                            @if($vendor->takeaway == 1)
                            <div class="checkbox checkbox-success form-check pl-1 mb-1">
                                <input name="pickup_slot_type[]" type="checkbox" id="pickup_edit_takeaway" checked value="takeaway">
                                <label for="pickup_edit_takeaway"> {{ __('Takeaway') }} </label>
                            </div>
                            @endif
                            @if($vendor->delivery == 1)
                            <div class="checkbox checkbox-success form-check pl-1 mb-1">
                                <input name="pickup_slot_type[]" type="checkbox" id="pickup_edit_delivery" checked value="delivery">
                                <label for="pickup_edit_delivery"> {{ __("Delivery") }} </label>
                            </div>
                            @endif
                            @if($vendor->laundry == 1)
                            <div class="checkbox checkbox-success form-check pl-0 mb-1" >
                                <input name="pickup_slot_type[]" type="checkbox" id="pickup_edit_laundry" checked value="laundry">
                                <label for="pickup_edit_laundry"> {{ __("Laundry") }} </label>
                            </div>
                        @endif
                        </div>
                    </div>
                    <!--<div class="row mb-2 pickup_weekDaysEdit">
                        <div class="col-md-12">
                            <div class="">
                            {!! Form::label('title', 'Select days of week',['class' => 'control-label']) !!}
                            </div>
                            <div class="checkbox checkbox-success form-check-inline">
                                <input name="week_day[]" type="checkbox" id="edit_day_1" value="1">
                                <label for="day_1"> Sunday </label>
                            </div>
                            <div class="checkbox checkbox-success form-check-inline">
                                <input name="week_day[]" type="checkbox" id="edit_day_2" value="2">
                                <label for="day_2"> Monday </label>
                            </div>
                            <div class="checkbox checkbox-success form-check-inline">
                                <input name="week_day[]" type="checkbox" id="edit_day_3" value="3">
                                <label for="day_3"> Tuesday </label>
                            </div>
                            <div class="checkbox checkbox-success form-check-inline">
                                <input name="week_day[]" type="checkbox" id="edit_day_4" value="4">
                                <label for="day_4"> Wednesday </label>
                            </div>
                            <div class="checkbox checkbox-success form-check-inline">
                                <input name="week_day[]" type="checkbox" id="edit_day_5" value="5">
                                <label for="day_5"> Thursday </label>
                            </div>
                            <div class="checkbox checkbox-success form-check-inline">
                                <input name="week_day[]" type="checkbox" id="edit_day_6" value="6">
                                <label for="day_6"> Friday </label>
                            </div>
                            <div class="checkbox checkbox-success form-check-inline">
                                <input name="week_day[]" type="checkbox" id="edit_day_7" value="7">
                                <label for="day_7"> Saturday </label>
                            </div>
                        </div>
                    </div> -->

                    <div class="row pickup_forDateEdit" style="display: none;">
                        <div class="col-md-12" >
                            <div class="form-group">
                                <label class="control-label">{{ __('Slot Date') }}</label>
                                <input class="form-control date-datepicker" placeholder="Select Date" type="text" name="pickup_slot_date" id="pickup_edit_slot_date" required />
                            </div>
                            <input  name="pickup_edit_type" type="hidden" id="pickup_edit_type" value="">
                            <input  name="pickup_edit_day" type="hidden" id="pickup_edit_day" value="">
                            <input name="pickup_edit_type_id" type="hidden" id="pickup_edit_type_id" value="">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12 mb-2">
                            <button type="button" class="btn btn-danger w-100" id="pickup_deleteSlotBtn">{{ __("Delete Slot") }}</button>
                        </div>
                        <div class="col-12 d-sm-flex justify-content-between">
                            <button type="button" class="btn btn-light mr-1" data-dismiss="modal">{{ __("Close") }}</button>
                            <button type="submit" class="btn btn-info" id="pickup_btn-update-slot">{{ __("Save") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
