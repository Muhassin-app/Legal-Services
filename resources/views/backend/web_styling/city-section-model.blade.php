@section('popup-id','vendor_city')

@section('popup-header')
{{ __('City') }}:<p class="sku-name pl-1"></p>
@endsection
@section('popup-content')
<div class='row'>
    <div class='col-md-5'>
        <form id="vedorCityForm" method="POST" action="javascript:void(0)" enctype="multipart/form-data">
            @csrf
            <div id="save_vendor_city" class="p-2 bg-light" style="border-radius: 15px;">
               <input type="hidden" id="vendor_city_id" name="vendor_city_id" value="">
               <div class="row">
                  <div class="col-md-12">
                     <label>{{ __('Image') }}</label>
                     <div class="vendor_city_image">
                        <input type="file" accept="image/*" data-plugins="dropify" name="vendor_city_image" class="dropify"  />
                     </div>
                     <label class="logo-size text-right w-100">{{ __("City image") }} 1000X1000</label>
                 </div>
              </div>
                <div class="row m-0">
                    <div class="col-12 selector-option-al mb-3 bg-white py-2">
                    <label>{{ __('Title') }}</label>
                        <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                            <tr class="trForClone">

                                @foreach($langs as $lang)
                                    <th>{{$lang->langName}}</th>
                                @endforeach
                                <th></th>
                            </tr>
                            <tbody >
                                <tr>
                                @foreach($langs as $key => $lang)
                                    <td>
                                        <input class="form-control name_{{ $key }}"  name="language_id[{{$key}}]" type="hidden" value="{{$lang->langId}}">
                                        <input class="form-control name_{{ $key }}" name="name[{{$key}}]" type="text" id="city_name_{{$lang->langId}}">
                                    </td>
                                    @if($key == 0)
                                    <span class="text-danger error-text city_err"></span>
                                    @endif
                                    @endforeach
                                    <td class="lasttd"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-3" id="addressInput">
                            {!! Form::label('title', __('Address'),['class' => 'control-label']) !!}
                            <div class="input-group">
                                <input type="text" name="address" id="city-address" placeholder="Delhi, India" class="form-control address" value="" >
                                <div class="input-group-append">
                                    <button class="btn btn-xs btn-dark waves-effect waves-light showMap" type="button" num="edit"> <i class="mdi mdi-map-marker-radius"></i></button>
                                </div>
                            </div>
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3" id="latitudeInput">
                            {!! Form::label('title', __('Latitude'),['class' => 'control-label']) !!}
                            <input type="text" name="latitude" id="city_latitude" placeholder="24.9876755" class="form-control latitude" value="">
                            @if($errors->has('latitude'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('latitude') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3" id="longitudeInput">
                            {!! Form::label('title', __('Longitude'),['class' => 'control-label']) !!}
                            <input type="text" name="longitude" id="city_longitude" placeholder="11.9871371723" class="form-control longitude" value="">
                            @if($errors->has('longitude'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('longitude') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <input type="hidden" name="place_id" id="place_id"   value="">
                </div>
                <div class="modal-footer p-0">
                    <button type="button" class="btn btn-primary submitSaveVendorCity">{{ __("Save") }}</button>
                 </div>


            </div>
         </form>
    </div>
    <div class='col-md-7'> 
        <table id="vendorCity" class="display" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Image') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            
        </table>
    </div>
</div>
@endsection

@section('popup-js')
<script src="{{ asset('assets/js/city/citySection.js')}}"></script>
@endsection