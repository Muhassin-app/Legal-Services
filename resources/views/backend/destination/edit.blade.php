<div class="row">
    <div class="col-6 mb-2">
        {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
        {!! Form::text('title',$destination->title, ['class'=>'form-control', 'id' => 'title', 'required' => 'required']) !!}
    </div>
    <div class="col-6 mb-2">
        {!! Form::label('address', __('Address'),['class' => 'control-label']) !!}
        {!! Form::text('address', $destination->address, ['class'=>'form-control', 'id' => 'address']) !!}
        {!! Form::hidden('longitude', $destination->longitude, ['class'=>'form-control', 'id' => 'longitude']) !!}
        {!! Form::hidden('latitude', $destination->latitude, ['class'=>'form-control', 'id' => 'latitude']) !!}
    </div>
    <div class="col-6 mb-2">
        {!! Form::label('image', __('Image'),['class' => 'control-label']) !!}
        {!! Form::file('image', ['class'=>'form-control', 'id' => 'image', 'required' => 'required']) !!}
    </div>
    @if(!empty($destination->image))
        <img src="{{$destination->image['image_fit'].'1000/1000'.@$destination->image['image_path']}}" width="100" height="100"/>
    @endif
</div>