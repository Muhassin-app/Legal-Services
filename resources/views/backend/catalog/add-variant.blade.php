<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group scroll_res">
                    {!! Form::label('title', __('Select '. getNomenclatureName('Variant')),['class' => 'control-label']) !!}
                    <select class="selectize-select form-control" required id="cateSelectBox" name="cate_id">
                        <option value="">{{ __("Select Category") }}...</option>
                        @foreach($categories as $cate)
                            <option value="{{$cate['id']}}">{{$cate['hierarchy']}}</option>
                        @endforeach
                        {{-- @foreach($categories as $cate)
                            <option value="{{$cate->id}}">{{ ucfirst($cate->slug) }}</option>
                        @endforeach --}}
                    </select>
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('title', __('Type'),['class' => 'control-label']) !!}
                    <select class="form-control selectize-select dropDownType"  name="type" dataFor="add">
                        <option value="1">{{ __("DropDown") }}</option>
                        <option value="2">{{ __("Color") }}</option>
                    </select>
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="row rowYK ">
            <div class="col-md-12">
                <h5>{{ __(getNomenclatureName('Variant') ." Title") }}</h5>
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
                <h5>{{ __(getNomenclatureName('Variant') ." Options") }}</h5>
            </div>
            <div class="col-md-12" style="overflow-x: auto;">
                <table class="table table-borderless table-responsive al_table_responsive_data optionTableAdd optionTableAddVarient" id="banner-datatable">
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
                            <input type="text" name="opt_color[{{$key}}][]" class="form-control" @if($langs->is_primary == 1) required @endif>
                        </td>
                        @endforeach
                        <td class="lasttd"></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-12">
                <button type="button" class="btn btn-info waves-effect waves-light addOptionRow-Add">{{ __("Add Option") }}</button>
            </div>
        </div>
    </div>
</div>
