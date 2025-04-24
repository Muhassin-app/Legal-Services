<div class="row">

    <div class="col-sm-12 mb-2">
        {!! Form::label('title', __('Name'),['class' => 'control-label']) !!}
        {!! Form::text('name', $influencer_tier->name,['class' => 'form-control', 'placeholder' => 'Tier Name', 'required'=>'required']) !!}
    </div>
    <div class="col-sm-12 mb-2">
        {!! Form::label('title', __('Target'),['class' => 'control-label']) !!}
        {!! Form::number('target', $influencer_tier->target,['class' => 'form-control', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => 'Target Tier', 'required'=>'required']) !!}
    </div>
    <div class="col-sm-12 mb-2">
        {!! Form::label('title', __('Commision Type'),['class' => 'control-label']) !!}
        <select class="selectize-select form-control" name="commision_type" required>

            <option value="1" @if($influencer_tier->commision_type == 1) selected @endif>{{__('Percentage')}}</option>
            <option value="2" @if($influencer_tier->commision_type == 2) selected @endif>{{__('Fixed')}}</option>

        </select>
    </div>
    <div class="col-sm-12 mb-2">
        {!! Form::label('title', __('Commision'),['class' => 'control-label']) !!}
        {!! Form::number('commision', $influencer_tier->commision,['class' => 'form-control', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => 'Commision', 'required'=>'required']) !!}
    </div>

    <div class="col-sm-12 mb-2">
        {!! Form::label('title', __('Status'),['class' => 'control-label']) !!}
        <select class="selectize-select form-control" name="status" required>
            <option value="1" @if($influencer_tier->status == 1) selected @endif>{{__('Active')}}</option>
            <option value="0" @if($influencer_tier->status == 0) selected @endif>{{__('Inactive')}}</option>
        </select>
    </div>

</div>