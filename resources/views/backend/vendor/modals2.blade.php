<div id="add-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">

                @php
                    $vendor = getNomenclatureName('vendors', false);
                    $vendor = ($vendor === 'Vendor') ? __('Vendor') : $vendor ;
                @endphp


                <h4 class="modal-title">{{ __("Add") }} {{ $vendor }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_banner_form" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" >
                    <div class="row">
                        <div class="col-md-12 d-none">
                            <h4 class="modal-title">personal details</h4>
                            <div class="form-group" id="user_search">
                                {!! Form::text('search', null, ['class' => 'form-control', 'placeholder' => __('Search User'), 'id' => 'search_user_for_vendor_permission', 'required' => 'required']) !!}
                                <input type="hidden" id='userId' name="userId" readonly>
                                <div id="userList">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row mb-2">
                                <div class="col-md-3">
                                    <label>{{ __('Upload Logo') }}</label>
                                    <input type="file" accept="image/*" data-plugins="dropify" name="logo" class="dropify" data-default-file="" />
                                    <label class="logo-size text-right w-100">{{ __('Logo Size') }} 170x96</label>
                                </div>
                                <div class="col-md-6">
                                    <label>{{ __('Upload banner image') }}</label>
                                    <input type="file" accept="image/*" data-plugins="dropify" name="banner" class="dropify" data-default-file="" />
                                    <label class="logo-size text-right w-100">{{ __("Image Size") }} 830x200</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group" id="nameInput">
                                        {!! Form::label('title', __('Name'),['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class'=>'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group" id="descInput">
                                        {!! Form::label('title', __('Description'),['class' => 'control-label']) !!}
                                        {!! Form::textarea('desc', null, ['class' => 'form-control', 'rows' => '3']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" id="emailInput">
                                        <label for="">{{ __('Email') }}</label>
                                        {!! Form::text('email', null, ['class'=>'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="phone_noInput">
                                        <label for="">{{ __('Phone Number') }}</label>
                                        {!! Form::tel('phone_no', null, ['class'=>'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="add">
                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="addressInput">
                                        {!! Form::label('title', __('Address'),['class' => 'control-label']) !!}
                                        <div class="input-group">
                                            <input type="text" name="address" id="add-address" placeholder="Delhi, India" class="form-control" onkeyup="checkAddressString(this,'add')">
                                            <div class="input-group-append">
                                                <button class="btn btn-xs btn-dark waves-effect waves-light showMap" type="button" num="add"> <i class="mdi mdi-map-marker-radius"></i></button>
                                            </div>
                                        </div>
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="latitudeInput">
                                        {!! Form::label('title', __('latitude'),['class' => 'control-label']) !!}
                                        <input type="text" name="latitude" id="add_latitude" placeholder="24.9876755" class="form-control" value="">
                                        @if($errors->has('latitude'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('latitude') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="longitudeInput">
                                        {!! Form::label('title', __('longitude'),['class' => 'control-label']) !!}
                                        <input type="text" name="longitude" id="add_longitude" placeholder="11.9871371723" class="form-control" value="">
                                        @if($errors->has('longitude'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('longitude') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">{{ __('Website') }}</label>
                                        <input class="form-control" type="text" name="website" value="111">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            @if($client_preferences->dinein_check == 1)
                                                {!! Form::label('title', getNomenclatureName('Dine-In', true),['class' => 'control-label']) !!}
                                                <div class="mt-md-1">
                                                    <input type="checkbox" data-plugin="switchery" name="dine_in" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                </div>
                                            @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            @if($client_preferences->takeaway_check == 1)
                                                {!! Form::label('title', getNomenclatureName('Takeaway', true),['class' => 'control-label']) !!}
                                                <div class="mt-md-1">
                                                    <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                </div>
                                            @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            @if($client_preferences->delivery_check == 1)
                                                {!! Form::label('title', getNomenclatureName('Delivery', true) ,['class' => 'control-label']) !!}
                                                <div class="mt-md-1">
                                                    <input type="checkbox" data-plugin="switchery" name="delivery" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                </div>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @foreach($vendor_registration_documents as $vendor_registration_document)
                                    @if(isset($vendor_registration_document->primary) && !empty($vendor_registration_document->primary))
                                        @if(strtolower($vendor_registration_document->file_type) == 'selector')
                                            <div class="col-md-6 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
                                                <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}</label>
                                                <select class="form-control {{ (!empty($vendor_registration_document->is_required))?'required':''}}" name="{{$vendor_registration_document->primary->slug}}"  id="input_file_selector_{{$vendor_registration_document->id}}">
                                                    <option value="" >{{__('Please Select '). ($vendor_registration_document->primary ? $vendor_registration_document->primary->name : '') }}</option>
                                                    @foreach ($vendor_registration_document->options as $key =>$value )
                                                        <option value="{{$value->id}}">{{$value->translation? $value->translation->name: ""}}</option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug}}_error"><strong></strong></span>
                                            </div>
                                        @else
                                            <div class="col-md-6" >
                                                <div class="form-group" id="{{$vendor_registration_document->primary->slug??''}}Input">
                                                    <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}</label>
                                                    @if(strtolower($vendor_registration_document->file_type) == 'text')
                                                        <input id="input_file_logo_{{$vendor_registration_document->id}}" type="text" name="{{$vendor_registration_document->primary->slug}}" class="form-control">
                                                    @else
                                                        @if(strtolower($vendor_registration_document->file_type) == 'image')
                                                        <input type="file" accept="image/*" data-plugins="dropify" name="{{$vendor_registration_document->primary->slug}}" class="dropify" data-default-file="" />
                                                        @else
                                                        <input type="file" accept=".pdf" data-plugins="dropify" name="{{$vendor_registration_document->primary->slug}}" class="dropify" data-default-file="" />
                                                        @endif
                                                    @endif
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong></strong>
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                 @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light submitAddForm">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="import-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Import') }} {{ $vendor }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form method="post" enctype="multipart/form-data" id="save_imported_vendors">
                @csrf
                <div class="modal-body">
                    <div class="row">
                    <div class="col-md-12 text-center">
                            <a href="{{url('file-download'.'/sample_vendor.csv')}}">{{ __("Download Sample file here!") }}</a>
                        </div>
                        <div class="col-md-12">
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <input type="file" accept=".csv" onchange="submitImportForm()" data-plugins="dropify" name="vendor_csv" class="dropify" data-default-file="" required/>
                                    <p class="text-muted text-center mt-2 mb-0">{{ __("Upload") }} {{ $vendor }} CSV</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <table class="table table-centered table-nowrap table-striped" id="">
                            <p id="p-message" style="color:red;"></p>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('File Name') }}</th>
                                        <th colspan="2">{{ __('Status') }}</th>
                                        <th>{{ __('Link') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="post_list">
                                    @foreach($csvVendors as $csv)
                                    <tr data-row-id="{{$csv->id}}">
                                        <td> {{ $loop->iteration }} </td>
                                        <td> {{ $csv->name }} </td>
                                        @if($csv->status == 1)
                                        <td>{{ __('Pending') }}</td>
                                        <td></td>
                                        @elseif($csv->status == 2)
                                        <td>{{ __('Success') }}</td>
                                        <td></td>
                                        @else
                                        <td>{{ __('Errors') }}</td>
                                        <td class="position-relative text-center">
                                            <i class="mdi mdi-exclamation-thick"></i>
                                            <ul class="tooltip_error">
                                                <?php $error_csv = json_decode($csv->error); ?>
                                                @foreach($error_csv as $err)
                                                <li>
                                                   {{$err}}
                                                </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        @endif
                                        <td> <a href="{{ $csv->path }}">{{ __('Download') }}</a> </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="show-map-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-bs-backdrop="static" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">

            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Select Location") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body p-4">

                <div class="row">
                    <form id="task_form" action="#" method="POST" style="width: 100%">
                        <div class="col-md-12">
                            <div id="googleMap" style="height: 500px; min-width: 500px; width:100%"></div>
                            <input type="hidden" name="lat_input" id="lat_map" value="0" />
                            <input type="hidden" name="lng_input" id="lng_map" value="0" />
                            <input type="hidden" name="address_map" id="address_map" value="" />
                            <input type="hidden" name="for" id="map_for" value="" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info waves-effect waves-light selectMapLocation">Ok</button>
                <!--<button type="Cancel" class="btn btn-info waves-effect waves-light cancelMapLocation">cancel</button>-->
            </div>
        </div>
    </div>
</div>

<div id="dispatcher-login-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Dispatcher Login") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_edit_banner_form" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editCardBox">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light submitDispatcherForm">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
