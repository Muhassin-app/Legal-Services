<div class="modal-header border-bottom">
    <h4 class="modal-title">{{ __('Edit Pricing') }}</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
@if(!empty($billingpricing))
{{ Form::open(array('id' => 'billing_pricing_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => array('billingpricing.save', $billingpricing->slug))) }}
    @csrf
    <div class="modal-body" >
        <div class="row">
            <div class="col-md-12">
                
                <div class="row">
                
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">{{ __("Plan") }}</label>
                            {!! Form::select('billing_plan_id', $billingplanlist, $billingpricing->billing_plan_id, ['class' => 'form-control', 'id'=>'billing_plan_id', 'required' => 'required']) !!}
                            
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">{{ __("Timeframe") }}</label>
                            {!! Form::select('billing_timeframe_id', $billingtimeframelist, $billingpricing->billing_timeframe_id, ['class' => 'form-control', 'id'=>'billing_timeframe_id', 'required' => 'required']) !!}
                            
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">{{ __("Current Price") }}</label>
                            {!! Form::number('price', $billingpricing->price, ['class'=>'form-control', 'required'=>'required', 'id'=>'price', 'step'=>'any']) !!}
                            
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">{{ __("Earlier Price") }}</label>
                            {!! Form::number('old_price', $billingpricing->old_price, ['class'=>'form-control', 'required'=>'required', 'id'=>'old_price', 'step'=>'any', 'readonly'=>'true']) !!}
                            
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('title', __('Status'),['class' => 'control-label']) !!}
                            <div class="mt-md-1">
                                <input type="checkbox" data-plugin="switchery" name="status" class="form-control status" data-color="#43bee1" {{($billingpricing->status == 1) ? 'checked' : ''}}>
                            </div>
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