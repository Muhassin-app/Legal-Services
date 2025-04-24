<div class="modal-header border-bottom">
    <h4 class="modal-title">{{ __('Edit Plan') }}</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
@if(!empty($billingplan))
{{ Form::open(array('id' => 'billing_plan_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => array('billingplans.save', $billingplan->slug))) }}
    @csrf
    <div class="modal-body" >
        <div class="row">
            <div class="col-md-12">
                <div class="row mb-2">
                    <div class="col-md-12">
                        <label>{{ __('Upload Image') }}</label>
                        <input type="file" accept="image/*" data-plugins="dropify" name="image" class="dropify" data-default-file="{{ $billingplan->image['proxy_url'].'100/100'.$billingplan->image['image_path'] }}" />
                    </div> 
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('title', __('Status'),['class' => 'control-label']) !!} 
                            <div class="mt-md-1">
                                <input type="checkbox" data-plugin="switchery" name="status" class="form-control status" data-color="#43bee1" {{($billingplan->status == 1) ? 'checked' : ''}}>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group" id="nameInput">
                            {!! Form::label('title', __('Title'),['class' => 'control-label']) !!} 
                            {!! Form::text('title', $billingplan->title, ['class'=>'form-control', 'required'=>'required']) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">{{ __("Plan Type") }}</label>
                            {!! Form::select('plan_type', $billingplanstype, $billingplan->plan_type, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">{{ __("Description") }}</label>
                            {!! Form::textarea('description', $billingplan->description, ['id' => 'description', 'rows' => 4, 'class' => 'form-control']) !!}
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