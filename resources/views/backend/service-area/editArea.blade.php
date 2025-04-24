<div class="row">
    <input type="hidden" name="latlongs_edit" value="{{$area->geo_array}}" id="latlongs_edit" />
    <input type="hidden" name="zoom_level_edit" value="13" id="zoom_level_edit" />
    <div class="col-lg-12 mb-2">
        {!! Form::label('title', __('Area Name'),['class' => 'control-label']) !!}
        {!! Form::text('name', $area->name,['class' => 'form-control',  'placeholder' => 'Area Name']) !!}
        {!! Form::hidden('ven_id', $area->vendor_id,['class' => 'form-control']) !!}
    </div>
    <div class="col-lg-12 mb-2">
        {!! Form::label('title', __('Area Description'),['class' => 'control-label']) !!}
        {!! Form::textarea('description', $area->description,['class' => 'form-control', 'rows' => '3', 'placeholder' => 'Area Description']) !!}
    </div>
    <div class="col-lg-12 mb-2">
        <select class="form-control al_box_height" id="primary_language" name="primary_language" required>
            <option value=""> Primary Language </option>
            @foreach($languages as $language)
                <option value="{{$language->id}}" {{ $language->id == $area->primary_language ? 'selected' : '' }}>{{$language->name}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-12 mb-2">
        <select class="form-control al_box_height" id="primary_currency" name="primary_currency" required>
            <option value=""> Primary Currency </option>
            @foreach($currencies as $currencie)
                <option value="{{$currencie->id}}" {{ $currencie->id == $area->primary_currency ? 'selected' : '' }}> {{$currencie->iso_code.' '.$currencie->symbol}} </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-12">
        <div class="" style="height:96%;">
            <div id="edit_map-canvas" style="min-width: 300px; width:100%; height: 600px;"></div>
        </div>
    </div>
</div>
