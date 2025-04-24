<div class="modal-header border-bottom">
    <h4 class="modal-title">{{ __('Edit Timeframe') }}</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
@if(!empty($billingtimeframe))
{{ Form::open(array('id' => 'billing_timeframe_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => array('billingtimeframes.save', $billingtimeframe->slug))) }}
    @csrf
    <div class="modal-body" >
        <div class="row">
            <div class="col-md-12">
                
                <div class="row">
                
                    <div class="col-md-12">
                        <div class="form-group" id="nameInput">
                            {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
                            {!! Form::text('title', $billingtimeframe->title, ['class'=>'form-control', 'required'=>'required']) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('title', __('Status'),['class' => 'control-label']) !!}
                            <div class="mt-md-1">
                                <input type="checkbox" data-plugin="switchery" name="status" class="form-control status" data-color="#43bee1" {{($billingtimeframe->status == 1) ? 'checked' : ''}}>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('title', __('Customised'),['class' => 'control-label']) !!}
                            <div class="mt-md-1">
                                <input type="checkbox" data-plugin="switchery" name="is_custom" class="form-control customised" data-color="#43bee1" {{($billingtimeframe->is_custom == 1) ? 'checked' : ''}}>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('title', __('Lifetime'),['class' => 'control-label']) !!}
                            <div class="mt-md-1">
                                <input type="checkbox" data-plugin="switchery" name="is_lifetime" id="is_lifetime" class="form-control timelimit" data-color="#43bee1" {{($billingtimeframe->is_lifetime == 1) ? 'checked' : ''}}>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" id="div_validity_edit">
                        <div class="form-group">
                            <label for="">{{ __("Validity ") }}</label>
                            {!! Form::number('validity', $billingtimeframe->validity, ['class'=>'form-control', 'required'=>'required', 'id'=>'validity_edit', 'min'=>'0', 'max'=>'365']) !!}
                            
                        </div>
                    </div>

                    <div class="col-md-6" id="div_validity_type_edit">
                        <div class="form-group">
                            <label for="">{{ __("Validity Type") }}</label>
                            {!! Form::select('validity_type', $validity_type, $billingtimeframe->validity_type, ['class' => 'form-control', 'id'=>'validity_type', 'required' => 'required']) !!}
                            
                        </div>
                    </div>

                    <div class="col-md-12" id="div_buffer_period_edit">
                        <div class="form-group">
                            {!! Form::label('title', __('Buffer Period (Days)'),['class' => 'control-label']) !!}
                            {!! Form::number('standard_buffer_period', $billingtimeframe->standard_buffer_period, ['class'=>'form-control', 'required'=>'required', 'id'=>'standard_buffer_period_edit', 'min'=>'0', 'max'=>'365']) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="submit" class="btn btn-info waves-effect waves-light submitAddSubscriptionForm">{{ __('Submit') }}</button>
    </div>
    
{{ Form::close() }}
@else
    <div class="modal-body" >
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <ul class="m-0">
                        Something went wrong.
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endif