    <div class="row mb-6">
    <div>
    {!! Form::hidden('login_user_type', session('login_user_type'), ['class'=>'form-control']) !!}
    {!! Form::hidden('login_user_id', auth()->user()->id, ['class'=>'form-control']) !!}
    </div>
    <div class="col-sm-4">
        <div class="round_img">
            <input type="file" accept="image/*" data-plugins="dropify" name="image" class="dropify" data-default-file="{{$user->image['image_fit'].'1000/1000'.$user->image['image_path']}}" />
        </div>
        <p class="text-muted text-center mt-2 mb-0">{{ __('Upload Profile Picture') }}</p>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group" id="nameInputEdit">
                    <label class="control-label">{{ __('Name') }}</label>
                    {!! Form::text('name', $user->name, ['class'=>'form-control', 'required' => 'required']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group" id="emailInputEdit">
                    <label class="control-label">{{ __('Email') }}</label>
                    {!! Form::text('email', $user->email, ['class'=>'form-control']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <label for="">{{ __('Phone No.') }}</label>
                @php
                 if($user){
                    if($user->dial_code){
                        $phn = '+'.$user->dial_code.$user->phone_number;
                    }
                    else if($user->phone_number){
                        $phn = $user->phone_number;
                    }
                    else{
                        $phn = ' ';
                    }
                 }
                 else{
                     $phn = ' ';
                 }
                @endphp
                <input type="tel" class="form-control phone @error('phone_number') is-invalid @enderror" id="phone" placeholder="Phone Number" name="phone_number" value="{{$phn ? $phn : old('phone_number')}}" required="required">
                <input type="hidden" id="countryData" name="countryData" value="us">
                <input type="hidden" id="dialCode" name="dialCode" value="{{$user->dial_code}}">
                @error('phone_number')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="col-lg-12">
                <div class="form-group" id="descriptionInputEdit">
                    <label class="control-label">{{ __('About Me') }}</label>
                    {!! Form::textarea('description', $user->description, ['class'=>'form-control']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-lg-12">
                <label class="mb-1">{{ __('Time Zone') }}</label>
                {!! $timezone_list !!}
            </div>
        </div>
        <div class="row mt-2">
            @if(!empty($user_registration_documents) && count($user_registration_documents) > 0)
            @foreach($user_registration_documents as $user_registration_document)
            @php
                $field_value = "";
                if(!empty($user_docs) && count($user_docs) > 0){
                    foreach($user_docs as $key => $user_doc){
                        if($user_registration_document->id == $user_doc->user_registration_document_id){
                            if($user_registration_document->file_type == 'Text' || $user_registration_document->file_type == 'selector' ){
                                $field_value = $user_doc->file_name;
                            } else {
                                $field_value = $user_doc->image_file['storage_url'];
                            }
                        }
                    }
                }
            @endphp
            @if(strtolower($user_registration_document->file_type) == 'selector')
                    <div class="col-md-12 mb-3" id="{{$user_registration_document->primary->slug??''}}Input">
                        <label for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}</label>
                        <select class="form-control {{ (!empty($user_registration_document->is_required))?'required':''}}" name="{{$user_registration_document->primary->slug}}"  id="input_file_selector_{{$user_registration_document->id}}">
                            <option value="" >{{__('Please Select '). ($user_registration_document->primary ? $user_registration_document->primary->name : '') }}</option>
                            @foreach ($user_registration_document->options as $key =>$value )
                                <option value="{{$value->id}}" {{ ($value->id == $field_value) ? 'selected':'' }} >{{$value->translation? $value->translation->name: ""}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback" id="{{$user_registration_document->primary->slug}}_error"><strong></strong></span>
                    </div>
            @else
            <div class="col-md-12" >
                <div class="form-group" id="{{$user_registration_document->primary->slug??''}}Input">
                    @if(strtolower($user_registration_document->file_type) == 'text')
                        <label for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}</label>
                        <input id="input_file_logo_{{$user_registration_document->id}}" type="text" name="{{$user_registration_document->primary->slug??''}}" class="form-control" value="{{ $field_value }}">
                    @else
                        @if(strtolower($user_registration_document->file_type) == 'image')
                        <label for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}</label>
                        <input type="file" accept="image/*" data-plugins="dropify" name="{{$user_registration_document->primary->slug??''}}" class="dropify" data-default-file="{{ $field_value }}" />
                        @else
                        <label class="d-flex align-items-center justify-content-between" for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}
                           @if($field_value)
                            <a href="{{ $field_value }}" target="__blank">
                              <i class="fa fa-eye" aria-hidden="true"></i>
                            </a>
                           @endif
                        </label>
                        <input type="file" accept=".pdf" data-plugins="dropify" name="{{$user_registration_document->primary->slug??''}}" class="dropify" data-default-file="{{ $field_value }}" />
                        @endif
                    @endif
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            @endif
            @endforeach
            @endif
        </div>
    </div>

</div>

