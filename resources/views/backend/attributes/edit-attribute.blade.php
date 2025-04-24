<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6">
            	<div class="form-group">
            		{!! Form::label('title', __('Service Type'),['class' => 'control-label']) !!}
            		{!! Form::select('service_type', config('constants.VendorTypes'), $variant->service_type, ['class' => 'form-control selectize-select dropDownTypeAttr']) !!}
            	</div>
            </div>
            <div class="col-md-6">
            	<div class="form-group">
            		{!! Form::label('title', __('Attribute Type'),['class' => 'control-label']) !!}
            		{!! Form::select('type_id', $attributeType, $variant->type_id, ['class' => 'form-control selectize-select dropDownTypeAttr']) !!}
            	</div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                	{!! Form::label('field-type', __('Field Type'),['class' => 'control-label']) !!}
                    {!! Form::select('field_type', $fieldType, $variant->field_type, ['class' => 'form-control selectize-select dropDownTypeAttr', 'dataFor' => 'add']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
             <div class="col-md-6">
               <div class="form-group position-relative">
                  <label for="">{{ __('Is Required?') }}</label>
                  <div class="input-group mb-2">
                     <select class="form-control" name="is_required">
                        <option value="1">{{__('Yes')}}</option>
                        <option value="0">{{__('No')}}</option>
                     </select>
                  </div>
               </div>
            </div>
        </div>

        <div class="row rowYK">
            <div class="col-md-12">
                <h5>{{ __(getNomenclatureName('Variant')." Title") }}</h5>
            </div>
            <div class="col-md-12" style="overflow-x: auto;">
                <input type="hidden" name="submitHide" id="submitEditHidden" value="{{route('variant.update', $variant->id)}}">
                <table class="table table-responsive al_table_responsive_data table-borderless" id="edit_banner-datatable" >
                    <tr>
                        @foreach($languages as $langs)
                            <td>{{$langs->language->name}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($languages as $langs)

                            <?php $valueData = ''; ?>

                            @foreach($variant->translation as $trans)

                                @if($trans->language_id == $langs->language_id)

                                    <?php $valueData = $trans->title; ?>

                                @endif
                            @endforeach

                            <td>
                                {!! Form::hidden('language_id[]', $langs->language_id) !!}
                                <input type="text" name="title[]" class="form-control" value="{{$valueData}}" @if($langs->is_primary == 1) required @endif>
                            </td>
                        @endforeach

                    </tr>

                </table>
            </div>
        </div>

        <div class="row rowYK">
            <div class="col-md-12">
                <h5>{{ __(getNomenclatureName('Variant')." Options") }}</h5>
            </div>
            <div class="col-md-12" style="overflow-x: auto;">
                <table class="table table-responsive al_table_responsive_data table-borderless optionTableEditAttribute" id="edit_variant-datatable">
                    <tr class="trForClone">
                        <td class="hexacodeClass-edit" style="@if($variant->type == 1 || $variant->type == 3 || $variant->type == 4 || $variant->type == 5 || $variant->type == 6 || $variant->type == 7) display: none @endif">{{ __("Color Code") }}</td>
                        @foreach($languages as $langs)
                            <td>{{$langs->language->name}}</td>
                        @endforeach
                        <td></td>
                    </tr>

                   @foreach($variant->option as $first => $opt)
                   <tr>
                        <td style="min-width: 150px; @if($variant->type == 1 || $variant->type == 3 || $variant->type == 4 || $variant->type == 5 || $variant->type == 6 || $variant->type == 7) display: none @endif" class="hexacodeClass-edit">
                            <input type="text" name="hexacode[]" class="form-control hexa-colorpicker" value="{{$opt->hexacode}}" id="hexa-colorpicker-{{$opt->id}}">

                            {!! Form::hidden('option_id[]', $opt->id) !!}
                        </td>

                        @foreach($languages as $langs)

                            @php $optData = $optDataId = ''; @endphp
                            @foreach($opt->translation as $opt_trans)

                                @if($opt_trans->language_id == $langs->language_id)
                                    @php
                                        $optData = $opt_trans->title;
                                        $optDataId = $opt_trans->variant_option_id;
                                    @endphp

                                @endif
                            @endforeach

                            <td>
                                <input type="hidden" name="opt_id[{{$langs->language_id}}][]" class="form-control" value="{{$optDataId}}" @if($langs->is_primary == 1) required @endif>
                                <input type="text" name="opt_title[{{$langs->language_id}}][]" class="form-control" value="{{$optData}}" @if($langs->is_primary == 1) required @endif>
                            </td>
                        @endforeach


                        <td class="lasttd">
                            @if($first > 0)
                            <a href="#" class="action-icon deleteCurRow" data-delete_attr_id="{{$opt->id}}"> <i class="mdi mdi-delete"></i></a>
                            @endif
                        </td>
                    </tr>

                    @endforeach

                </table>
            </div>
            <div class="col-md-12">
                <button type="button" class="btn btn-info waves-effect waves-light addOptionRow-attribute-edit">{{ __("Add Option") }}</button>
            </div>
        </div>
    </div>
</div>