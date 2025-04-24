<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">       
@foreach($productAttributes as $vk => $var)
@php $counter = 0; @endphp
    <div class="form-group">
        <label>{{$var->title??null}}</label>
        @if( !empty($var->type) && $var->type == 1 )
            @foreach($var->option as $key => $opt)
                <input type="hidden" name="attribute[{{$var->id}}][type]" value="{{$var->type}}">
                <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}"> 
                @php  $counter++; @endphp
            @endforeach
            <select name="attribute[{{$var->id}}][value][]" class="form-control select2-multiple attribute_option_id_{{$var->id}}"  multiple>
                @foreach($var->option as $key => $opt)
                    <option value="{{$opt->id}}" @if(in_array($opt->id, $attribute_value)) selected @endif>{{$opt->title}}</option>
                @endforeach
            </select>
            {{-- <button class="btn btn-sm add_attr_options" data-attribute_id="{{ $var->id }}" ><i class="fas fa-plus"></i></button> --}}
        @elseif( !empty($var->type) && $var->type == 4 )
            @foreach($var->option as $key => $opt)
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
            @endforeach
            {{-- <button class="btn btn-sm add_attr_options" data-attribute_id="{{ $var->id }}" ><i class="fas fa-plus"></i></button> --}}
        @elseif( !empty($var->type) && $var->type == 3 )
        
            @foreach($var->option as $key => $opt)
                @if(isset($opt) && !empty($opt->title) && isset($var) && !empty($var->title))
                    <div class="form-check-inline ">
                        <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                        <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                        <input type="hidden" name="attribute[{{$var->id}}][option][option_id]" value="{{$opt->id}}">
                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                        <div class="attr_radio_{{$var->id}}">
                        <input type="radio" name="attribute[{{$var->id}}][option][{{$counter}}][value]" class="form-control attr_radio mr-1"  
                        value="{{$opt->id}}" @if(in_array($opt->id, $attribute_value)) checked @endif>
                        </div>
                        <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                    </div>
                    {{-- <button class="btn btn-sm add_attr_options" data-attribute_id="{{ $var->id }}" ><i class="fas fa-plus"></i></button> --}}
                    @php  $counter++; @endphp
                @endif
            @endforeach
            @elseif( !empty($var->type) && $var->type == 6 )
                @foreach($var->option as $key => $opt)
                    <div class="form-group mb-3 w-100" id="addressInput">
                        <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                        <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                        <div class="input-group">
                            <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][latitude]" id="latitude" @if(in_array($opt->id, $attribute_value))  
                            value="{{$attribute_latitude[$opt->id]}}"
                            @else
                            value=""
                            @endif/>
                            <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][longitude]" id="longitude" @if(in_array($opt->id, $attribute_value))  
                            value="{{$attribute_longitude[$opt->id]}}"
                            @else
                            value=""
                            @endif/>
                            <input type="text" name="attribute[{{$var->id}}][option][{{$counter}}][value]" id="add-address" onkeyup="checkAddressString(this,'add')" placeholder="" class="form-control" @if(in_array($opt->id, $attribute_value))  
                            value="{{$attribute_key_value[$opt->id]}}"
                            @else
                            value=""
                            @endif>
                        </div>
                        <span class="invalid-feedback" role="alert">
                            <strong></strong>
                        </span>
                    </div>
                @endforeach
                @elseif( !empty($var->type) && $var->type == 7 )
                        
                @foreach($var->option as $key => $opt)
                    <div class="form-group mb-3 w-100" id="addressInput">
                        <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                        <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                        <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                        {{-- <input class="form-control" type="text" name="attribute[{{$var->id}}][option][{{$counter}}][value]" placeholder="Enter {{$var->title??null}}" 
                        
                        @if(in_array($opt->id, $attribute_value))  
                        value="{{$attribute_key_value[$opt->id]}}"
                        @else
                        value=""
                        @endif> --}}
                        {{-- @dd($attribute_latitude) --}}
                        <div class="form-check-inline w-100">
                            <input type="text" name="attribute[{{$var->id}}][option][{{$counter}}][value]" placeholder="" class="form-control datepicker" @if(in_array($opt->id, $attribute_value))  
                            value="{{$attribute_key_value[$opt->id]}}"
                            @else
                            value=""
                            @endif>
                            {{-- <div class="input-group-append">
                                <button class="btn btn-xs btn-dark waves-effect waves-light showMap" type="button" num="add"> <i class="mdi mdi-map-marker-radius"></i></button>
                            </div> --}}
                        </div>
                        <span class="invalid-feedback" role="alert">
                            <strong></strong>
                        </span>
                    </div>
                @endforeach
            @else
            @foreach($var->option as $key => $opt)
                <div class="checkbox checkbox-success form-check-inline pr-3">
                    <input type="hidden" name="attribute[{{$var->id}}][id]" value="{{$var->id}}">
                    <input type="hidden" name="attribute[{{$var->id}}][attribute_title]" value="{{$var->title}}">
                    <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_id]" value="{{$opt->id}}">
                    <input type="hidden" name="attribute[{{$var->id}}][option][{{$counter}}][option_title]" value="{{$opt->title}}">
                    <input type="checkbox" class="form-control" name="attribute[{{$var->id}}][option][{{$counter}}][value]" value="{{$opt->id}}" @if(in_array($opt->id, $attribute_value)) checked @endif>
                    <label for="attr_opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                </div>
                @php  $counter++; @endphp
            @endforeach
            {{-- <button class="btn btn-sm add_attr_options" data-attribute_id="{{ $var->id }}" ><i class="fas fa-plus"></i></button> --}}
        @endif
    </div>

@endforeach
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    $( function() {
      $( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
    } );
</script>
<script>
var autocomplete = {};
    var autocompletesWraps = [];
    var count = 1;
    editCount = 0;
    $(document).ready(function() {
        
        autocompletesWraps.push('def');
        loadMap(autocompletesWraps);
    });

    function loadMap(autocompletesWraps) {
        
        // console.log(autocompletesWraps);
        $.each(autocompletesWraps, function(index, name) {
            const geocoder = new google.maps.Geocoder;
            
            // if ($('#' + name).length == 0) {
            //     return;
            // }
            
            //autocomplete[name] = new google.maps.places.Autocomplete(('.form-control')[0], { types: ['geocode'] }); console.log('hello');
            autocomplete[name] = new google.maps.places.Autocomplete(document.getElementById('add-address'), {
                types: ['geocode']
            });
            if(is_map_search_perticular_country){
                autocomplete[name].setComponentRestrictions({'country': [is_map_search_perticular_country]});
            }
            google.maps.event.addListener(autocomplete[name], 'place_changed', function() {
                var place = autocomplete[name].getPlace();
                if (!place.geometry) {
                    window.alert("Autocomplete's returned place contains no geometry");
                    return;
                }
                geocoder.geocode({
                    'placeId': place.place_id
                }, function(results, status) {

                    if (status === google.maps.GeocoderStatus.OK) {
                        const lat = results[0].geometry.location.lat();
                        const lng = results[0].geometry.location.lng();
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                    }
                });
            });

        });
    }

 $('.select2-multiple').select2();
</script>
           