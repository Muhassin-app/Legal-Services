
<form id="category_kyc_form_in_cart" class=" theme-form category-kyc-form-name_{{$category_id}}" name="category-kyc-form-name"  action="{{ route('updateCartCategoryKyc') }}" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="category_id" value="{{$category_ids}}">
    <div class="form-row">
        @foreach($category_kyc_documents as $vendor_registration_document)
        @if(isset($vendor_registration_document->primary->slug) && !empty($vendor_registration_document->primary->slug))
        @if(strtolower($vendor_registration_document->file_type) == 'selector')
        <div class="col-md-6 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
            <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}{{ (!empty($vendor_registration_document->is_required))?'*':''}}</label>
            <select class="form-control {{ (!empty($vendor_registration_document->is_required))?'required':''}}" name="{{$vendor_registration_document->primary->slug}}" id="input_file_selector_{{$vendor_registration_document->id}}">
                <option value="">{{__('Please Select '). ($vendor_registration_document->primary ? $vendor_registration_document->primary->name : '') }}</option>
                @foreach ($vendor_registration_document->options as $key =>$value )
                <option value="{{$value->id}}">{{$value->translation? $value->translation->name: ""}}</option>
                @endforeach
            </select>
            <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug}}_error"><strong></strong></span>
        </div>
        @else
        <div class="col-md-12 mb-3" id="{{$vendor_registration_document->primary->slug??''}}Input">
            <label for="">{{$vendor_registration_document->primary ? $vendor_registration_document->primary->name : ''}}{{ (!empty($vendor_registration_document->is_required))?'*':''}}</label>
            @if(strtolower($vendor_registration_document->file_type) == 'text')
            <input id="input_file_logo_{{$vendor_registration_document->id}}" type="text" name="{{$vendor_registration_document->primary->slug}}" class="form-control {{ (!empty($vendor_registration_document->is_required))?'required':''}}">
            <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug??''}}_error"><strong></strong></span>
            @else
            <div class="file file--upload">
                <label for="input_file_logo_{{$vendor_registration_document->id}}">
                    <span class="update_pic pdf-icon">
                        <img src="" id="upload_logo_preview_{{$vendor_registration_document->id}}">
                    </span>
                    <span class="plus_icon" id="plus_icon_{{$vendor_registration_document->id}}">
                        <i class="fa fa-plus"></i>
                    </span>
                </label>
                @if(strtolower($vendor_registration_document->file_type) == 'image')
                <input class="{{ (!empty($vendor_registration_document->is_required))?'required':''}}" id="input_file_logo_{{$vendor_registration_document->id}}" type="file" name="{{$vendor_registration_document->primary->slug}}" accept="image/*" data-rel="{{$vendor_registration_document->id}}">
                @else
                <input class="{{ (!empty($vendor_registration_document->is_required))?'required':''}}" id="input_file_logo_{{$vendor_registration_document->id}}" type="file" name="{{$vendor_registration_document->primary->slug}}" accept=".pdf" data-rel="{{$vendor_registration_document->id}}">
                @endif
                <span class="invalid-feedback" id="{{$vendor_registration_document->primary->slug}}_error"><strong></strong></span>
            </div>
            @endif
        </div>
        @endif
        @endif
        @endforeach
        <span class="text-danger category_kyc_form_error mb-2" id="error-msg"></span>
        <span class="text-success" id="success-msg"></span>
        <div class="col-md-12">
            <button class="btn btn-solid w-100 buttonload" type="submit"   data-dev_remove_id="category_kyc_dev_{{$category_id}}" data-category_id="{{$category_id}}" id="category_kycform_submit">{{__('Submit')}} <img style="width:5%; display:none;" id="proceed_to_pay_loader" src="{{asset('front-assets/images/loader.gif')}}"/></button>
            
        </div>
    </div>
</form>