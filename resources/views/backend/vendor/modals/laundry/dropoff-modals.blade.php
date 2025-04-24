<div class="modal fade dropoff_standard_modal" id="dropoff-standard-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3 px-3 border-bottom-0 d-block">
                <button type="button" class="close" data-dismiss="modal"
                    aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="modal-title">{{ __('Book Slot') }}</h5>
            </div>
            <div class="modal-body px-3 pb-3 pt-0">
                <form class="needs-validation" name="dropoff_slot-form" id="dropoff_slot-event" action="{{ route('vendor.dropoff.saveSlot', $vendor->id) }}" method="post">
                    @csrf
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("Start Time(24 hours format)") }}</label>
                                <input class="form-control" placeholder="Start Time" type="text" name="dropoff_start_time" id="dropoff_start_time" required />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("End Time(24 hours format)") }}</label>
                                <input class="form-control" placeholder="End Time" type="text" name="dropoff_end_time" id="dropoff_end_time" required />
                            </div>
                        </div>

                        <div class="col-md-6 dropoff_slotForDiv">
                            {!! Form::label('title', 'Slot For',['class' => 'control-label']) !!}
                            <div class="form-group">
                                <ul class="list-inline">
                                    <li class="d-inline-block ml-3 mb-1 custom-radio-design">
                                        <input type="radio" class="custom-control-input check dropoff_slotTypeRadio" id="dropoff_slotDay" name="dropoff_stot_type" value="day" checked="">
                                        <label class="custom-control-label" for="dropoff_slotDay">{{ __('Days') }}&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    </li>
                                    <li class="d-inline-block ml-3 mb-1 custom-radio-design">
                                        <input type="radio" class="custom-control-input check dropoff_slotTypeRadio" id="dropoff_slotDate" name="dropoff_stot_type" value="date">
                                        <label class="custom-control-label" for="dropoff_slotDate">{{ __('Date') }}</label>
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
                                    <input name="dropoff_slot_type[]" type="checkbox" id="dropoff_dine_in" checked value="dine_in">
                                    <label for="dropoff_dine_in"> {{ __("Dine in") }}</label>
                                </div>
                            @endif
                            @if($vendor->takeaway == 1)
                                <div class="checkbox checkbox-success form-check pl-0 mb-1"  @if($client_preferences->takeaway_check == 0) style="display: none;" @endif >
                                    <input name="dropoff_slot_type[]" type="checkbox" id="dropoff_takeaway" checked value="takeaway">
                                    <label for="dropoff_takeaway"> {{ __("Takeaway") }} </label>
                                </div>
                            @endif
                            @if($vendor->delivery == 1)
                                <div class="checkbox checkbox-success form-check pl-0 mb-1"  @if($client_preferences->delivery_check == 0) style="display: none;" @endif>
                                    <input name="dropoff_slot_type[]" type="checkbox" id="dropoff_delivery" checked value="delivery">
                                    <label for="dropoff_delivery"> {{ __("Delivery") }} </label>
                                </div>
                            @endif
                            @if($vendor->laundry == 1)
                            <div class="checkbox checkbox-success form-check pl-0 mb-1"  @if($client_preferences->laundry_check == 0) style="display: none;" @endif>
                                <input name="dropoff_slot_type[]" type="checkbox" id="dropoff_laundry" checked value="laundry">
                                <label for="dropoff_laundry"> {{ __("Laundry") }} </label>
                            </div>
                        @endif
                        </div>
                    </div>
                    <div class="row mb-2 dropoff_weekDays">
                        <div class="col-md-12">
                            <div class="">
                            {!! Form::label('title', __('Select days of week'),['class' => 'control-label']) !!}
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="dropoff_week_day[]" type="checkbox" id="dropoff_day_1" value="1">
                                <label for="dropoff_day_1"> {{ __("Sunday") }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="dropoff_week_day[]" type="checkbox" id="dropoff_day_2" value="2">
                                <label for="dropoff_day_2"> {{ __('Monday') }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="dropoff_week_day[]" type="checkbox" id="dropoff_day_3" value="3">
                                <label for="dropoff_day_3"> {{ __("Tuesday") }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="dropoff_week_day[]" type="checkbox" id="dropoff_day_4" value="4">
                                <label for="dropoff_day_4"> {{ __("Wednesday") }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="dropoff_week_day[]" type="checkbox" id="dropoff_day_5" value="5">
                                <label for="dropoff_day_5"> {{ __('Thursday') }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="dropoff_week_day[]" type="checkbox" id="dropoff_day_6" value="6">
                                <label for="dropoff_day_6"> {{ __('Friday') }} </label>
                            </div>
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="dropoff_week_day[]" type="checkbox" id="dropoff_day_7" value="7">
                                <label for="dropoff_day_7"> {{ __('Saturday') }} </label>
                            </div>
                        </div>
                    </div>

                    <div class="row dropoff_forDate" style="display: none;">
                        <div class="col-md-12" >
                            <div class="form-group">
                                <label class="control-label">{{ __("Slot Date") }}</label>
                                <input class="form-control date-datepicker" placeholder={{ __("Select Date") }} type="text" name="dropoff_slot_date" id="dropoff_slot_date" required />
                            </div>
                        </div>

                    </div>
                    <div class="row mt-2">
                        <div class="col-12 d-sm-flex justify-content-between">
                            <button type="button" class="btn btn-light mr-1" data-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-info" id="dropoff_btn-save-slot">{{ __('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal standard_modal fade" id="dropoff_edit-slot-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3 px-3 border-bottom-0 d-block">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="modal-title">Edit Slot</h5>
                <form method="post" action="{{ route('vendor.dropoff.deleteSlot', $vendor->id) }}" id="dropoff_deleteSlotForm">
                    @csrf
                    <div>
                        <input type="hidden" name="dropoff_slot_day_id" id="dropoff_deleteSlotDayid" value="" >
                        <input type="hidden" name="dropoff_slot_id" id="dropoff_deleteSlotId" value="" >
                        <input type="hidden" name="dropoff_slot_type" id="dropoff_deleteSlotType" value="" >
                        <input type="hidden" name="dropoff_old_slot_type" id="dropoff_deleteSlotTypeOld" value="" >
                        <input type="hidden" name="dropoff_slot_date" id="dropoff_deleteSlotDate" value="" >
                       <button type="button" class="btn btn-primary-outline action-icon" style="display: none;"></button>
                    </div>
                </form>
            </div>
            <div class="modal-body px-3 pb-3 pt-0">
                <form class="needs-validation" name="dropoff_slot-form" id="dropoff_update-event" action="{{ route('vendor.dropoff.updateSlot', $vendor->id) }}" method="post">
                    @csrf
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("Start Time(24 hours format)") }}</label>
                                <input class="form-control" placeholder={{ __("Start Time") }} type="text" name="dropoff_start_time" id="dropoff_edit_start_time" required />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("End Time(24 hours format)") }}</label>
                                <input class="form-control" placeholder={{ __("End Time") }} type="text" name="dropoff_end_time" id="dropoff_edit_end_time" required />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 slotForDiv">
                            {!! Form::label('title', __('Slot For'),['class' => 'control-label']) !!}
                            <div class="form-group">
                                <ul class="list-inline">
                                    <li class="d-block pl-1 ml-3 mb-1 custom-radio-design">
                                        <input type="radio" class="custom-control-input check slotTypeEdit" id="dropoff_edit_slotDay" name="dropoff_slot_type_edit" value="day" checked="">
                                        <label class="custom-control-label" id="dropoff_edit_slotlabel" for="dropoff_edit_slotDay">Days</label>
                                    </li>
                                    <li class="d-block pl-1 ml-1 mb-1 custom-radio-design"> &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="radio" class="custom-control-input check slotTypeEdit" id="dropoff_edit_slotDate" name="dropoff_slot_type_edit" value="date">
                                        <label class="custom-control-label" for="dropoff_edit_slotDate">Date</label>
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-12 dropoff_weekDays">
                            <div class="">
                            {!! Form::label('title', __('Slot Type'),['class' => 'control-label']) !!}
                            </div>
                            @if($vendor->dine_in == 1)
                            <div class="checkbox checkbox-success form-check pl-1 mb-1">
                                <input name="dropoff_slot_type[]" type="checkbox" id="dropoff_edit_dine_in" checked value="dine_in">
                                <label for="dropoff_edit_dine_in"> {{ __("Dine in") }} </label>
                            </div>
                            @endif
                            @if($vendor->takeaway == 1)
                            <div class="checkbox checkbox-success form-check pl-1 mb-1">
                                <input name="dropoff_slot_type[]" type="checkbox" id="dropoff_edit_takeaway" checked value="takeaway">
                                <label for="dropoff_edit_takeaway"> {{ __('Takeaway') }} </label>
                            </div>
                            @endif
                            @if($vendor->delivery == 1)
                            <div class="checkbox checkbox-success form-check pl-1 mb-1">
                                <input name="dropoff_slot_type[]" type="checkbox" id="dropoff_edit_delivery" checked value="delivery">
                                <label for="dropoff_edit_delivery"> {{ __("Delivery") }} </label>
                            </div>
                            @endif
                            @if($vendor->laundry == 1)
                            <div class="checkbox checkbox-success form-check pl-0 mb-1">
                                <input name="dropoff_slot_type[]" type="checkbox" id="dropoff_edit_laundry" checked value="laundry">
                                <label for="dropoff_edit_laundry"> {{ __("Laundry") }} </label>
                            </div>
                        @endif
                        </div>
                    </div>
                    <!--<div class="row mb-2 dropoff_weekDaysEdit">
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

                    <div class="row dropoff_forDateEdit" style="display: none;">
                        <div class="col-md-12" >
                            <div class="form-group">
                                <label class="control-label">{{ __('Slot Date') }}</label>
                                <input class="form-control date-datepicker" placeholder="Select Date" type="text" name="dropoff_slot_date" id="dropoff_edit_slot_date" required />
                            </div>
                            <input  name="dropoff_edit_type" type="hidden" id="dropoff_edit_type" value="">
                            <input  name="dropoff_edit_day" type="hidden" id="dropoff_edit_day" value="">
                            <input name="dropoff_edit_type_id" type="hidden" id="dropoff_edit_type_id" value="">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12 mb-2">
                            <button type="button" class="btn btn-danger w-100" id="dropoff_deleteSlotBtn">{{ __("Delete Slot") }}</button>
                        </div>
                        <div class="col-12 d-sm-flex justify-content-between">
                            <button type="button" class="btn btn-light mr-1" data-dismiss="modal">{{ __("Close") }}</button>
                            <button type="submit" class="btn btn-info" id="dropoff_btn-update-slot">{{ __("Save") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
