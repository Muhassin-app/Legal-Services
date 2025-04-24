@php
use app\Models\SubscriptionPlansUser;
@endphp
<div class="modal-header border-bottom">
    <h4 class="modal-title">{{ __('Edit Plan') }}</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<form id="user_subscription_form" method="post" enctype="multipart/form-data" action="{{ route('subscription.plan.save.user', $plan->slug) }}">
    @csrf
    <div class="modal-body" >
        <div class="row">
            <div class="col-md-12">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label>{{ __('Upload Image') }}</label>
                        <input type="file" accept="image/*" data-plugins="dropify" name="image" class="dropify" data-default-file="{{ $plan->image['proxy_url'].'100/100'.$plan->image['image_path'] }}" />
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('title', __('Enable'),['class' => 'control-label']) !!}
                            <div class="mt-md-1">
                                <input type="checkbox" data-plugin="switchery" name="status" class="form-control status" data-color="#43bee1" {{($plan->status == 1) ? 'checked' : ''}}>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group" id="nameInput">
                            {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
                            {!! Form::text('title', $plan->title, ['class'=>'form-control', 'required'=>'required']) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="nameInput">
                            {!! Form::label('subscriptionType', __('Subscription Type'),['class' => 'control-label']) !!}
                            {!! Form::select('type_id', SubscriptionPlansUser::subscriptionTypes(), $plan->type_id ,['class'=>'form-control','id' => 'edit-subscriptionType']) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 meal-sub-field">
                        <div class="form-group" id="nameInput">
                            {!! Form::label('orderLimit', __('Order Limit'),['class' => 'control-label']) !!}
                            {!! Form::text('order_limit', $plan->order_limit ,['class'=>'form-control', 'id' => 'orderLimit', 'onkeypress' => "return isNumberKey(event)"]) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 category_wrapper meal-sub-field">
						<div class="form-group">
							{!! Form::label('title', __('Select Category'),['class' => 'control-label']) !!} 
							<select
								class="form-control select2-multiple category_features"
								name="categories[]" data-toggle="select2" multiple="multiple"
								data-placeholder="Select Category...">
								@foreach($categories as $pc)
								<option value="{{$pc->id}}" {{ (in_array($pc->id, $subPlanCategoryIds)) ? "selected" : "" }}>{{$pc->translation_one ?
									ucfirst($pc->translation_one['name']) : ' '}}</option>
								@endforeach
							</select>
						</div>
					</div>
                    @php
                    $feature_percent_value = '';
                    @endphp
                    <div class="col-md-6 features_wrapper user-sub-field">
                        <div class="form-group">
                            <label for="">{{ __("Features") }}</label>
                            <select class="form-control select2-multiple subscription_features" name="features[]" data-toggle="select2" multiple="multiple" data-placeholder="Choose ..." required="required">
                                @foreach($features as $feature)
                                    @php
                                    if(in_array(2, $subPlanFeaturesIds)){
                                        $off_on_order_feature = $planFeatures->where('feature_id', 2)->first();
                                        $feature_percent_value = $off_on_order_feature ? $off_on_order_feature->percent_value : '';
                                    }
                                    @endphp
                                    <option value="{{$feature->id}}" {{ (in_array($feature->id, $subPlanFeaturesIds)) ? "selected" : "" }}> {{$feature->title}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 percentage_value_wrapper" style="{{ (in_array(2, $subPlanFeaturesIds)) ? 'display:block' : '' }}">
                        <div class="form-group">
                            <label for="percent_value">{{ __('Percent Value') }}</label>
                            <input class="form-control" type="number" id="percent_value" name="percent_value" min="0" value="{{$feature_percent_value}}" placeholder="Percent Value" onKeyPress="if(this.value.length==6) return false;" {{ (in_array(2, $subPlanFeaturesIds)) ? 'required=true' : '' }}>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">{{ __('Price') }}</label>
                            <input class="form-control" type="number" name="price" min="0" value="{{ decimal_format($plan->price) }}" required="required">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">{{ __("Frequency") }}</label>
                            <select class="form-control" name="frequency" value="{{ $plan->frequency }}" required="required">
                                <option value="weekly" {{ $plan->frequency == 'weekly' ? 'selected' : '' }}>{{ __("Weekly") }}</option>
                                <option value="monthly" {{ $plan->frequency == 'monthly' ? 'selected' : '' }}>{{ __("Monthly") }}</option>
                                <option value="yearly" {{ $plan->frequency == 'yearly' ? 'selected' : '' }}>{{ __("Yearly") }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6 meal-sub-field">
                            <div class="row">
                            @foreach($additionalAttributes as $attribute)
                                @if(isset($attribute->primary) && !empty($attribute->primary))
                                    @if(strtolower($attribute->field_type) == 'selector')
                                        <div class="col-md-6 mb-3" id="{{$attribute->primary->slug??''}}Input">
                                            <label for="">{{$attribute->title}}</label>
                                            <select class="form-control {{ (!empty($attribute->is_required))?'required':''}}" name="{{$attribute->primary->slug}}"  id="input_file_selector_{{$attribute->id}}">
                                                <option value="" >{{__('Please Select '). ($attribute->title) }}</option>
                                                @foreach ($attribute->option as $key =>$value )
                                                    <option value="{{$value->id}}">{{$value->trans? $value->trans->name: ""}}</option>
                                                @endforeach
                                            </select>
                                            <span class="invalid-feedback" id="{{$attribute->primary->slug}}_error"><strong></strong></span>
                                        </div>
                                    @elseif(strtolower($attribute->field_type) == 'checkbox')
                                    	<div class="col-md-6 form-group" id="{{$attribute->primary->slug??''}}Input">
                                        	<label for="">{{$attribute->title}}</label>
                                            	<div class="">
                                            		@foreach($attribute->option as $options)
                                                        @php
                                                        	$translation = $options->trans;
                                    	                	$col = array_column(json_decode($attributeProduct, true), 'product_data');
                                	
                                                        	$checked = '';
                                                        	if(in_array($translation->title, $col)){
                                                        		$checked = 'checked'; 
                                                        	}
                                                    	@endphp
    													<div class="specialOption">
                                                            <input type="checkbox" name="{{$attribute->primary->slug}}[{{$translation->title}}]" class="intpCheck" opt="" varId="" id="opt_ven_{{$translation->id}}" {{$checked}}>
                                                            <label for="opt_ven_{{$translation->id}}">{{$translation->title}}</label>
                                                        </div> 
                                                	@endforeach
                                            </div>
                                        </div>
                                        @elseif(strtolower($attribute->field_type) == 'radio')
                                    	<div class="col-md-6 form-group" id="{{$attribute->primary->slug??''}}Input">
                                        	<label for="">{{$attribute->title}}</label>
                                            	<div class="">
                                            		@foreach($attribute->option as $options)
                                                        @php
                                                        	$translation = $options->trans;
                                    	                	$col = array_column(json_decode($attributeProduct, true), 'product_data');
                                	
                                                        	$checked = '';
                                                        	if(in_array($translation->title, $col)){
                                                        		$checked = 'checked'; 
                                                        	}
                                                    	@endphp
    													<div class="specialOption">
                                                            <input type="radio" name="{{$attribute->primary->slug}}[]" class="intpCheck" opt="" varId="" id="opt_ven_{{$translation->id}}" {{$checked}}>
                                                            <label for="opt_ven_{{$translation->id}}">{{$translation->title}}</label>
                                                        </div> 
                                                	@endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-6" >
                                            <div class="form-group" id="{{$attribute->primary->slug??''}}Input">
                                                <label for="">{{$attribute->title}}</label>
                                                @if(strtolower($attribute->field_type) == 'text')
                                                    <input id="input_file_logo_{{$attribute->id}}" type="text" name="{{$attribute->primary->slug}}" class="form-control">
                                                @else
                                                    @if(strtolower($attribute->file_type) == 'image')
                                                    <input type="file" accept="image/*" data-plugins="dropify" name="{{$attribute->primary->slug}}" class="dropify" data-default-file="" />
                                                    @else
                                                    <input type="file" accept=".pdf" data-plugins="dropify" name="{{$attribute->primary->slug}}" class="dropify" data-default-file="" />
                                                    @endif
                                                @endif
                                                <span class="invalid-feedback" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                            </div>
                        </div>
                    <?php /* ?><div class="col-md-6">
                        <div class="form-group">
                            <label for="">Sort Order</label>
                            <input class="form-control" type="number" name="sort_order" min="1" value="{{ $plan->sort_order }}" required="required">
                        </div>
                    </div><?php */ ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('title', __('Description'),['class' => 'control-label']) !!}
                            {!! Form::textarea('description', $plan->description, ['class' => 'form-control', 'rows' => '3']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-info waves-effect waves-light submitAddSubscriptionForm">{{ __('Submit') }}</button>
    </div>
</form>