<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6">
            	<div class="form-group">
            		{!! Form::label('title', __('Service Type'),['class' => 'control-label']) !!}
            		{!! Form::select('service_type', config('constants.VendorTypes'), null, ['class' => 'form-control selectize-select dropDownTypeAttr']) !!}
            	</div>
            </div>
            <div class="col-md-6">
            	<div class="form-group">
            		{!! Form::label('title', __('Attribute Type'),['class' => 'control-label']) !!}
            		{!! Form::select('type_id', $attributeType, null, ['class' => 'form-control selectize-select dropDownTypeAttr']) !!}
            	</div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('field-type', __('Field Type'),['class' => 'control-label']) !!}
                    {!! Form::select('field_type', $fieldType, null, ['class' => 'form-control selectize-select dropDownTypeAttr', 'dataFor' => 'add']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            
            <div class="col-md-6">
               <div class="form-group position-relative">
                  <label for="">Is Required?</label>
                  <div class="input-group mb-2">
                     <select class="form-control" name="is_required">
                        <option value="1">{{__('Yes')}}</option>
                        <option value="0">{{__('No')}}</option>
                     </select>
                  </div>
               </div>
            </div>
        </div>

        <div class="row rowYK ">
            <div class="col-md-12">
                <h5>{{ __(getNomenclatureName('Attribute') ." Title") }}</h5>
            </div>
            <div class="col-md-12" style="overflow-x: auto;">

                <table class="table table-borderless table-responsive al_table_responsive_data" id="banner-datatable" >
                    <tr >
                        @foreach($languages as $langs)
                            <th>{{$langs->language->name}}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($languages as $langs)
                            @if($langs->is_primary == 1)
                                <td >
                                    {!! Form::hidden('language_id[]', $langs->language_id) !!}
                                    {!! Form::text('title[]', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </td>
                            @else
                                <td >
                                    {!! Form::hidden('language_id[]', $langs->language_id) !!}
                                    {!! Form::text('title[]', null, ['class' => 'form-control']) !!}
                                </td>
                            @endif
                        @endforeach
                    </tr>
                </table>
            </div>
        </div>

        <div class="row rowYK">
            <div class="col-md-12">
                <h5>{{ __(getNomenclatureName('Attribute') ." Options") }}</h5>
            </div>
            <div class="col-md-12" style="overflow-x: auto;">
                <table class="table table-borderless table-responsive al_table_responsive_data optionTableEditAttribute" id="banner-datatable">
                    <tr class="trForClone">
                        <th class="hexacodeClass-add" style="display:none;">{{ __("Color Code") }}</th>
                        @foreach($languages as $langs)
                            <th>{{$langs->language->name}}</th>
                        @endforeach
                        <th></th>
                    </tr>
                    <tr>
                        <td style="min-width: 200px; display:none;" class="hexacodeClass-add col-md-6">
                            <input type="text" name="hexacode[]" class="form-control hexa-colorpicker" value="cccccc" id="add-hexa-colorpicker-1">
                        </td>
                       @foreach($languages as $key => $langs)
                        <td>
                            <input type="text" name="opt_color[{{$key}}][]" class="form-control attr-text-box" @if($langs->is_primary == 1) required @endif>
                        </td>
                        @endforeach
                        <td class="lasttd"></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-12">
                <button type="button" class="btn btn-info waves-effect waves-light addOptionRow-attribute-edit">{{ __("Add Option") }}</button>
            </div>
        </div>
    </div>
</div>
