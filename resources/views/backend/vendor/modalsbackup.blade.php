<div id="add-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                @php
                    $vendor = getNomenclatureName('vendors', false);
                    $vendor = ($vendor === 'Vendor') ? __('Vendor') : $vendor ;
                @endphp
                <h4 class="modal-title">{{ __("Add") }} {{ $vendor }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_banner_form" class="al_overall_form" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" >
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row mb-2">
                                <div class="col-md-3">
                                    <label>{{ __('Upload Logo') }} </label>
                                    <input type="file" accept="image/*" data-plugins="dropify" name="logo" class="dropify" data-default-file="" />
                                    <label class="logo-size text-right w-100">{{ __('Logo Size') }} 170x96</label>
                                </div>
                                <div class="col-md-9">
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

                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group" id="emailInput">
                                        <label for="">{{ __('Email') }}</label>
                                        {!! Form::text('email', null, ['class'=>'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" id="phone_noInput">
                                        <label for="">{{ __('Phone Number') }}</label>
                                        {!! Form::tel('phone_no', null, ['class'=>'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">{{ __('Website') }}</label>
                                        <input class="form-control" type="text" name="website">
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="add">
                                <div class="col-md-4">
                                    <div class="form-group mb-3" id="addressInput">
                                        {!! Form::label('title', __('Address'),['class' => 'control-label']) !!}
                                        <div class="input-group">
                                            <input type="text" name="address" id="add-address" placeholder="Delhi, India" class="form-control">
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
                                        {!! Form::label('title', __('Latitude'),['class' => 'control-label']) !!}
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
                                        {!! Form::label('title', __('Longitude'),['class' => 'control-label']) !!}
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
                                    <div class="form-group" id="descInput">
                                        {!! Form::label('title', __('Description'),['class' => 'control-label']) !!}
                                        {!! Form::textarea('desc', null, ['class' => 'form-control', 'rows' => '3']) !!}
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
                        </div>

                        <!-- al_add_user start -->
                        <div class="col-md-4 al_add_user">
                            <h5>{{__("SEARCH USER")}} </h5>
                            <div class="form-group">
                                <div class="btn-group w-100">
                                    {{-- data-toggle="dropdown" aria-haspopup="true" --}}
                                    <input type="text" id="search_user_for_permission" class="dropdown-toggle d-block form-control w-100" value="" name="user_search_name" autocomplete="off" aria-expanded="false" placeholder="search user">
                                </div>
                                <div id="userList_model"></div>
                                    <div class="dropdown-menu_al w-100" id="selected_user_ul" >
                                        <ul id="selected_user" class="pl-2 ">
                                        </ul>
                                    </div>
                            </div>
                            <h5>{{__('ADD USER')}} </h5>
                            <div id="adduesr_error"></div>
                            <div class="form-group">
                                <label for="title" class="control-label">{{__("Name")}}</label>
                                <input class="form-control" name="user_name" id="new_user_name" type="text">
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="title" class="control-label">Email</label>
                                    <input class="form-control" name="user_email" id="new_user_email" type="email">
                                </div>
                                <div class="col-md-12 form-group" id="phone_numberInput">
                                    {!! Form::label('title', __('Phone Number'),['class' => 'control-label']) !!}
                                    <input type="tel" class="form-control phone" id="new_user_phone_number" placeholder={{ __("Phone Number") }} name="phone_number" value="{{ old('full_number')}}">
                                    <input type="hidden" id="countryCode" name="country" value="{{ old('countryData') ? old('countryData') : 'us'}}">
                                    <input type="hidden" id="dialCode" name="dial_code" value="{{ old('dialCode') ? old('dialCode') : Session::get('default_country_phonecode',1) }}">

                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                                {{-- <div class="col-md-6 form-group">
                                    <label for="title" class="control-label">Phone Number</label>
                                    <input class="form-control" name="phone_number" id="new_user_phone_number" type="tel">
                                </div> --}}
                            </div>
                            <div class="form-group">
                                <label for="title" class="control-label">Password</label>
                                <input type="password" class="form-control" id="new_user_password"  placeholder="Password" required="" name="password" value="">
                            </div>
                            <a  id="addUserAddForm" class="btn w-100 btn-info waves-effect waves-light " data-url="{{route('customer.store')}}">{{__('ADD NEW USER')}}</a>

                        </div>
                    </div><!-- al_add_user end -->

                    <div class="row">
                        <!-- al_custom_modal start ADVANCED DETAILS -->
                        <div class="al_custom_modal col-md-12 pt-2 border-top">
                                <h5 class="mb-2">ADVANCED DETAILS</h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="al_advanced_details p-2">
                                            <p class="al_custom_title mb-1">Configuration</p>
                                            <form action="">
                                                <div class="form-group">
                                                    <label for="title" class="">Order Prepare Time(In minutes)</label>
                                                    <div class="position-relative">
                                                        <input class="form-control" onkeypress="return isNumberKey(event)" name="order_pre_time" id="Vendor_order_pre_time" type="text" value="0">
                                                        <div class="time-sloat d-flex align-items-center"><span class="" id="Vendor_order_pre_time_show">0 Min</span> </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="">24 * 7 Availability</label>
                                                            <div class="mt-md-1">
                                                                <input type="checkbox" data-plugin="switchery" name="dine_in" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="">Auto Accept Order</label>
                                                            <div class="mt-md-1">
                                                                <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="">Show Profile Details</label>
                                                            <div class="mt-md-1">
                                                                <input type="checkbox" data-plugin="switchery" name="delivery" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="">Auto reject time (In minutes, 0 for no rejection)</label>
                                                    <input type="text" class="form-control">
                                                </div>

                                                <div class="form-group">
                                                    <label for="">Auto reject time (In minutes, 0 for no rejection)</label>
                                                    <select class= "form-control" id="assignTo">
                                                        <option value="1" selected="&quot;selected&quot;">{{ __('Only Product') }}</option>
                                                        <option value="2">{{ __('Only Category') }}</option>
                                                        <option value="5">{{ __('Product with Category') }}</option>
                                                    </select>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="al_advanced_details p-2">
                                            <p class="al_custom_title mb-1">Commission(Visible for Admin)</p>
                                            <form action="">
                                                <div class="form-group">
                                                    <label for="">Commission percentage</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label for="">Service fee percent</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label for="">Commission fixed per order</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="al_advanced_details p-2">
                                            <p class="al_custom_title mb-1">Category Setup (Visible For Admin)</p>
                                            <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                                                <label for="title" class="control-label">Can Add Category</label>
                                                <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="title" class="control-label">Vendor Category</label>
                                                <div class="custom-dd dd nestable_list_1" id="nestable_list_1">
                                                    <ol class="dd-list">
                                                        @forelse($builds as $build)
                                                            @if($build['translation_one'])
                                                            <li class="dd-item dd3-item" data-category_id="{{$build['id']}}">
                                                                <div class="dd3-content">
                                                                    <img class="rounded-circle mr-1" src="{{$build['icon']['proxy_url']}}30/30{{$build['icon']['image_path']}}"> {{$build['translation_one']['name']}}
                                                                    <span class="inner-div text-right">
                                                                        <a class="action-icon" data-id="3" href="javascript:void(0)">
                                                                            @if(in_array($build['id'], $VendorCategory))
                                                                                <input type="checkbox" data-category_id="{{ $build['id'] }}" data-color="#43bee1" class="form-control activeCategory" data-plugin="switchery" checked>
                                                                            @else
                                                                                <input type="checkbox" data-category_id="{{ $build['id'] }}" data-color="#43bee1" class="form-control activeCategory" data-plugin="switchery" >
                                                                            @endif
                                                                            <input type="hidden" value="{{ $build['id'] }}">
                                                                        </a>
                                                                    </span>
                                                                </div>
                                                                @if(isset($build['children']))
                                                                    <x-category :categories="$build['children']" :vendorcategory="$VendorCategory" :vendor=/>
                                                                @endif
                                                                </li>
                                                            </li>
                                                            @endif
                                                            @empty
                                                            @endforelse
                                                        {{-- <li class="dd-item dd3-item" data-category_id="3">
                                                            <div class="dd3-content">
                                                                <img class="rounded-circle mr-1" src="https://images.royoorders.com/insecure/fill/30/30/ce/0/plain/https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/category/icon/Iw9YXkwmqOmxvSQlnYrkLNvte6slEQNcYPBsw8xH.svg"> Restaurants
                                                                <span class="inner-div text-right">
                                                                    <a class="action-icon" data-id="3" href="javascript:void(0)">
                                                                    <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'><input type="hidden" value="3">
                                                                    </a>
                                                                </span>
                                                            </div>
                                                            <ol class="dd-list">
                                                                <li class="dd-item dd3-item" data-id="20">
                                                                    <div class="dd3-content">
                                                                        <img class="rounded-circle mr-1" src="https://images.royoorders.com/insecure/fill/30/30/ce/0/plain/https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/category/icon/vveRdzXKw6cl7O8CUu89sxA57idFXLAqI9bj3deY.jpg">
                                                                        Venezia
                                                                        <span class="inner-div text-right">
                                                                            <a class="action-icon" data-id="6" href="javascript:void(0)">
                                                                            <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                                            </a>
                                                                        </span>
                                                                    </div>
                                                                </li>
                                                                <li class="dd-item dd3-item" data-id="20">
                                                                    <div class="dd3-content">
                                                                        <img class="rounded-circle mr-1" src="https://images.royoorders.com/insecure/fill/30/30/ce/0/plain/https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/category/icon/vveRdzXKw6cl7O8CUu89sxA57idFXLAqI9bj3deY.jpg">
                                                                        Venezia
                                                                        <span class="inner-div text-right">
                                                                            <a class="action-icon" data-id="6" href="javascript:void(0)">
                                                                            <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                                            </a>
                                                                        </span>
                                                                    </div>
                                                                </li>
                                                                <li class="dd-item dd3-item" data-id="20">
                                                                    <div class="dd3-content">
                                                                        <img class="rounded-circle mr-1" src="https://images.royoorders.com/insecure/fill/30/30/ce/0/plain/https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/category/icon/vveRdzXKw6cl7O8CUu89sxA57idFXLAqI9bj3deY.jpg">
                                                                        Venezia
                                                                        <span class="inner-div text-right">
                                                                            <a class="action-icon" data-id="6" href="javascript:void(0)">
                                                                            <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'>
                                                                            </a>
                                                                        </span>
                                                                    </div>
                                                                </li>
                                                            </ol>
                                                        </li>
                                                        <li class="dd-item dd3-item" data-category_id="3">
                                                            <div class="dd3-content">
                                                                <img class="rounded-circle mr-1" src="https://images.royoorders.com/insecure/fill/30/30/ce/0/plain/https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/category/icon/Iw9YXkwmqOmxvSQlnYrkLNvte6slEQNcYPBsw8xH.svg"> Restaurants
                                                                <span class="inner-div text-right">
                                                                    <a class="action-icon" data-id="3" href="javascript:void(0)">
                                                                    <input type="checkbox" data-plugin="switchery" name="takeaway" class="form-control validity" data-color="#43bee1" checked='checked'><input type="hidden" value="3">
                                                                    </a>
                                                                </span>
                                                            </div>
                                                        </li> --}}
                                                    </ol>
                                                </div>
                                            </div>

                                        </div>
                                    </div>


                                </div>
                            </div><!-- al_custom_modal end -->
                    </div>

                    <div class="row mt-3 al_licence_imgbox">
                        <div class="col-md-6">
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
                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Dummy 1</label>
                                <input type="text" class="w-100 form-control">
                            </div>
                            <div class="form-group">
                                <label for="">Dummy 2</label>
                                <input type="text" class="w-100 form-control">
                            </div>
                        </div> --}}
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
<div id="show-map-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">

            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Select Location") }}</h4>
                <button type="button" class="close remove-modal-open" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body p-4">

                <div class="row">
                    <form id="task_form" action="#" method="POST" style="width: 100%">
                        <div class="col-md-12">
                            <div id="googleMap" style="height: 500px; min-width: 500px; width:100%"></div>
                            <input type="hidden" name="lat_input" id="lat_map" value="0" />
                            <input type="hidden" name="lng_input" id="lng_map" value="0" />
                            <input type="hidden" name="for" id="map_for" value="" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info waves-effect waves-light remove-modal-open selectMapLocation">Ok</button>
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

