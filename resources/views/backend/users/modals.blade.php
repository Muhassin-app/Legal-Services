<div id="user-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add Customer") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="add_user" action="{{ route('customer.store') }}"  enctype="multipart/form-data" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 offset-md-3 text-center">
                            <div class="form-group" id="imageInput">
                                <label>{{ __('Profile image') }}</label>
                                <input data-default-file="" type="file" data-plugins="dropify" name="image" accept="image/*" class="dropify" />
                                <label class="logo-size text-right w-100">{{ __('Image Size') }} 110x110 </label>
                                <span class="invalid-feedback" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" id="nameInput">
                                        {!! Form::label('title', __('Name'),['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="emailInput">
                                        {!! Form::label('title', __('Email'),['class' => 'control-label']) !!}
                                        {!! Form::email('email', null, ['class' => 'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="phone_numberInput">
                                        {!! Form::label('title', __('Phone Number'),['class' => 'control-label']) !!}
                                        <input type="tel" class="form-control phone" id="phone" placeholder={{ __("Phone Number") }} name="phone_number" value="{{ old('full_number')}}">
                                        <input type="hidden" id="countryCode" name="country" value="{{ old('countryData') ? old('countryData') : 'us'}}">
                                        <input type="hidden" id="dialCode" name="dial_code" value="{{ old('dialCode') ? old('dialCode') : Session::get('default_country_phonecode',1) }}">
                                        <input type="hidden" id="addphoneHidden" name="phoneHidden">
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="passwordInput">
                                        {!! Form::label('title', __('Password'),['class' => 'control-label']) !!}
                                        <input type="password" class="form-control" id="password" placeholder={{ __("Password") }} required="" name="password" value="{{ old('password')}}">
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" id="countryInput">
                                        {!! Form::label('title', __('Country'),['class' => 'control-label']) !!}
                                        <select class="selectize-select form-control" name="country_id">
                                            <option value="">{{ __('Select') }}</option>
                                            @foreach($countries as $key => $val)
                                            <option value="{{$val->id}}">{{$val->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{-- <div class="form-group" id="typeInput">
                                        {!! Form::label('title', 'Type',['class' => 'control-label']) !!}
                                        <select class="selectize-select form-control" name="role_id">
                                            <option value="1">Buyer</option>
                                            <option value="2">Seller</option>
                                        </select>
                                    </div> --}}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('title', __('Email Verified'),['class' => 'control-label']) !!}
                                        <div>
                                            <input type="checkbox" data-plugin="switchery" name="is_email_verified" class="form-control email_verify_add">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('title', __('Phone Verified'),['class' => 'control-label']) !!}
                                        <div>
                                            <input type="checkbox" data-plugin="switchery" name="is_phone_verified" class="form-control phone_verify_add">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($user_registration_documents as $user_registration_document)
                            @if(isset($user_registration_document->primary) && !empty($user_registration_document->primary))
                                @if(strtolower($user_registration_document->file_type) == 'selector')
                            @if(isset($user_registration_document->options))
                                    <div class="col-md-6 mb-3" id="{{$user_registration_document->primary->slug??''}}Input">
                                        <label for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}</label>
                                        <select class="form-control {{ (!empty($user_registration_document->is_required))?'required':''}}" name="{{$user_registration_document->primary->slug}}"  id="input_file_selector_{{$user_registration_document->id}}">
                                            <option value="" >{{__('Please Select '). ($user_registration_document->primary ? $user_registration_document->primary->name : '') }}</option>
                                            @foreach ($user_registration_document->options as $key =>$value )
                                                <option value="{{$value->id}}">{{$value->translation? $value->translation->name: ""}}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback" id="{{$user_registration_document->primary->slug}}_error"><strong></strong></span>
                                    </div>
                                    @endif
                                @else
                                    <div class="col-md-6" >
                                        <div class="form-group" id="{{$user_registration_document->primary->slug??''}}Input">
                                            <label for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}</label>
                                            @if(strtolower($user_registration_document->file_type) == 'text')
                                                <input id="input_file_logo_{{$user_registration_document->id}}" type="text" name="{{$user_registration_document->primary->slug}}" class="form-control">
                                            @else
                                                @if(strtolower($user_registration_document->file_type) == 'image')
                                                <input type="file" accept="image/*" data-plugins="dropify" name="{{$user_registration_document->primary->slug}}" class="dropify" data-default-file="" />
                                                @else
                                                <input type="file" accept=".pdf" data-plugins="dropify" name="{{$user_registration_document->primary->slug}}" class="dropify" data-default-file="" />
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
                <div class="modal-footer">
                    <input type="hidden" id="addCountryData" name="countryData" value="us">
                    <button type="button" class="btn btn-info w-100 submitCustomerForm">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-customer-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Edit Customer') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <form id="add_user" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6" id="imageInput">
                            <input data-default-file="" type="file" data-plugins="dropify" name="image" accept="image/*" class="dropify" />
                            <p class="text-muted text-center mt-2 mb-0">{{ __('Profile image') }}</p>
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" id="nameInputEdit">
                                        {!! Form::label('title', __('Name'),['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="emailInputEdit">
                                        {!! Form::label('title', __('Email'),['class' => 'control-label']) !!}
                                        {!! Form::email('email', null, ['class' => 'form-control']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="phone_numberInputEdit">
                                        {!! Form::label('title', __('Phone Number'),['class' => 'control-label']) !!}
                                        <input type="tel" class="form-control phone" id="phone" placeholder={{ __("Phone Number") }} name="phone_number" value="{{ old('full_number')}}">
                                        <input type="hidden" id="countryCode" name="country" value="{{ old('countryData') ? old('countryData') : 'us'}}">
                                        <input type="hidden" id="dialCode" name="country_code" value="{{ old('dialCode') ? old('dialCode') : Session::get('default_country_phonecode',1) }}">

                                        <input type="hidden" id="addphoneHidden" name="phoneHidden">
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="passwordInputEdit">
                                        {!! Form::label('title', __('Password'),['class' => 'control-label']) !!}
                                        <input type="password" class="form-control" id="password" placeholder={{ __("Password") }} required="" name="password" value="{{ old('password')}}">
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="typeInputEdit">
                                        {!! Form::label('title', __('Type'),['class' => 'control-label']) !!}
                                        <select class="selectize-select form-control" name="role_id">
                                            <option value="1">{{ __('Buyer') }}</option>
                                            <option value="2">{{ __('Seller') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="countryInputEdit">
                                        {!! Form::label('title', __('Country'),['class' => 'control-label']) !!}
                                        <select class="selectize-select form-control" name="country_id">
                                            <option value="">{{ __('Select') }}</option>
                                            @foreach($countries as $key => $val)
                                            <option value="{{$val->id}}">{{$val->nicename}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editCountryData" name="editCountryData" value="us">
                    <button type="submit" class="btn btn-info w-100">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="customer-wallet-transactions-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Wallet Transactions') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-centered table-striped w-100" id="customer_wallet_transactions_datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="text-nowrap">{{ __('Date') }}</th>
                                        <th>{{ __("Description") }}</th>
                                        <th>{{ __("Credit") }} / {{ __("Debit") }}</th>
                                        <th>{{ __("Remarks") }}</th>
                                        <th>{{ __("Created By") }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <div id="import-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Import Customers') }} </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form method="post" enctype="multipart/form-data" id="save_imported_customer">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="{{url('/sample_customer.csv')}}">{{ __("Download Sample file here!") }}</a>
                            </div>
                            <div class="col-md-12">
                                <div class="row mb-2">
                                    <div class="col-md-12">
                                        <input type="file" accept=".csv" onchange="submitImportUserForm()" data-plugins="dropify" name="customer_csv" class="dropify" data-default-file="" required/>
                                        <p class="text-muted text-center mt-2 mb-0">{{ __("Upload") }} CSV</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="table-responsive">
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
                                        @foreach($csvCustomers as $csv)
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
                                            <td class="position-relative text-center alTooltipHover">
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
                    </div>
                </form>
            </div>
        </div>
    </div>

<div id="pay-receive-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h4 class="modal-title">{{__("Pay") ."/". __("Receive") ." ". __("Money")}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="submitpayreceive" enctype="">
                @csrf
                <div class="row pt-2">
                    <div class="login-form col-md-12">
                        <ul class="list-inline d-flex justify-content-center">
                            <li class="d-inline-block mr-2">
                                <input type="radio" id="pay_radio" name="payment_type"  value="1" checked>
                                <label for="pay_radio"><span class="showspan">{{__("Pay")}}</span></label>
                            </li>
                            <li class="d-inline-block mr-2">
                                <input type="radio" id="receive_radio"  name="payment_type" value="2">
                                <label for="receive_radio"><span class="showspan">{{__("Receive")}}</span></label>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="modal-body px-3 py-0">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="customer_search" class="control-label">{{__("Customer")}}</label>
                                <input type="text" id='customer_search' class="form-control" name="customer_search" placeholder="{{__('Search Customer')}}" value="" required>
                                <input type="hidden" id='cusid' name="cusid" value="" readonly>
                                <div id="cus_search_wrapper" style="position:relative"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-2" class="control-label">{{__("Amount")}}</label>
                                <input name="amount" type="text" class="form-control" id="field-2" placeholder="100" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-3" class="control-label">{{__("Remarks")}}</label>
                                <textarea name="remarks" class="form-control" id="field-3" placeholder="{{__('Give some remarks')}}" rows="5" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span class="show_all_error invalid-feedback"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-blue waves-effect waves-light">{{__("Add")}}</button>
                </div>
            </form>
        </div>
    </div>
</div><!-- /.modal -->



