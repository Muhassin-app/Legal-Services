<div class="row">
    
    <div class="col-lg-12 mb-2">
        {!! Form::label('title', __('Logo'), ['class' => 'control-label']) !!}
        <input type="file" name="logo" >
        <a href="{{get_file_path($area->logo,'FILL_URL')}}" target="_blank" class="text-body"><img src="{{get_file_path($area->logo,'FILL_URL','60','60')}}"></a>
    </div>

    <div class="col-lg-12 mb-2">
        {!! Form::label('title', __('Area Name'),['class' => 'control-label']) !!}
        {!! Form::text('name', $area->name,['class' => 'form-control',  'placeholder' => 'Name']) !!}
        {!! Form::hidden('id', $area->id,['class' => 'form-control']) !!}
    </div>

    <div class="col-lg-12 mb-2">
        {!! Form::label('title', __('Email'), ['class' => 'control-label']) !!}
        {!! Form::email('email', $area->email, ['class' => 'form-control', 'placeholder' => 'Email', 'required' => 'required']) !!}
    </div>
    

    <div class="col-lg-12 mb-2">
        {!! Form::label('title', __('Phone Number'), ['class' => 'control-label']) !!}
        {!! Form::number('phone_number', $area->phone_number, ['class' => 'form-control', 'placeholder' => 'Number', 'required' => 'required']) !!}
    </div>

  
    <div class="col-lg-12 mb-2">
        {!! Form::label('title', __('Address'),['class' => 'control-label']) !!}
        {!! Form::textarea('address', $area->address,['class' => 'form-control', 'rows' => '3', 'placeholder' => 'Address']) !!}
    </div>
    
</div>
