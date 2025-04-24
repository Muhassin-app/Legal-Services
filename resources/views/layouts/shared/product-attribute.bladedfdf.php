<div class="card-box" >
    <div class="row mb-2 bg-light">
        <div class="col-8" style="">
            <h5 class="text-uppercase bg-light p-2">{{ __(getNomenclatureName('Attribute')." Information") }}</h5>
        </div>
    </div>

    <p>{{ __("Select or change category to get ".getNomenclatureName('Attribute')) }}</p>

    <div class="row" style="width:100%; overflow-x: scroll;">
        <div id="variantAjaxDiv" class="col-12 mb-2">
            <h5 class="">{{__(getNomenclatureName('Attribute').' List')}}</h5>
            <div class=" mb-2 form-label">
                
                @foreach($productAttributes as $vk => $var)
                @php $counter = 0; @endphp
                <div class="row mb-2">
                    <div class="col-sm-3">
                        <label class="control-label">{{$var->title??null}}</label>
                    </div>
                    <div class="col-sm-9">
                        
                        @if( !empty($var->type) && $var->type == 1 )
                            @foreach($var->option as $key => $opt)
                                <input type="hidden" name="attribute[{{$var->id}}][type]" value="{{$var->type}}">
                                <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                                <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                                <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                                <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}"> 
                                @php  $counter++; @endphp
                            @endforeach
                            <select name="attribute[{{$var->id}}][value][]" class="select2-multiple attribute_option_id_{{$var->id}}"  multiple>
                                @foreach($var->option as $key => $opt)
                                    <option value="{{$opt->id}}" @if(in_array($opt->id, $attribute_value)) selected @endif>{{$opt->title}}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm add_attr_options" data-attribute_id="{{ $var->id }}" ><i class="fas fa-plus"></i></button>
                        @elseif( !empty($var->type) && $var->type == 4 )
                            <div class="form-check-inline w-100">
                                <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                                <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                                <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                                <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                                <input class="form-control" type="text" name="attribute[{{$var->id}}][option][{{$counter}}][value]"  
                                
                                @if(in_array($opt->id, $attribute_value))  
                                value="{{$attribute_key_value[$opt->id]}}"
                                @else
                                value=""
                                @endif>
                            </div>
                            {{-- <button class="btn btn-sm add_attr_options" data-attribute_id="{{ $var->id }}" ><i class="fas fa-plus"></i></button> --}}
                        @elseif( !empty($var->type) && $var->type == 3 )
                        
                            @foreach($var->option as $key => $opt)
                                @if(isset($opt) && !empty($opt->title) && isset($var) && !empty($var->title))
                                    <div class="form-check-inline ">
                                        <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                                        <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                                        <div class="attr_radio_{{$var->id}}">
                                        <input type="radio" name="attribute[{{$var->id}}][option][{{$counter}}][value]" class="attr_radio mr-1"  
                                        value="{{$opt->id}}" @if(in_array($opt->id, $attribute_value)) checked @endif>
                                        </div>
                                        <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                                    </div>
                                    {{-- <button class="btn btn-sm add_attr_options" data-attribute_id="{{ $var->id }}" ><i class="fas fa-plus"></i></button> --}}
                                    @php  $counter++; @endphp
                                @endif
                            @endforeach
                        @else
                            @foreach($var->option as $key => $opt)
                                <div class="checkbox checkbox-success form-check-inline pr-3">
                                    <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                                    <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                                    <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                                    <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                                    <input type="checkbox" name="attribute[{{$var->id}}][option][{{$counter}}][value]" value="{{$opt->id}}" @if(in_array($opt->id, $attribute_value)) checked @endif>
                                    <label for="attr_opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                                </div>
                                @php  $counter++; @endphp
                            @endforeach
                            <button class="btn btn-sm add_attr_options" data-attribute_id="{{ $var->id }}" ><i class="fas fa-plus"></i></button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>