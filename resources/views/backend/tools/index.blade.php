@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Tools'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/dropzone.css')}}" rel="stylesheet" />
@endsection

@section('content')

<!-- Start Content-->
<div class="container-fluid alToolsPage">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="text-sm-left">
                @if (\Session::has('success'))
                <div class="alert mt-2 mb-0 alert-success">
                    <span>{!! \Session::get('success') !!}</span>
                </div>
                @endif
                @if ( ($errors) && (count($errors) > 0) )
                <div class="alert mt-2 mb-0 alert-danger">
                    <ul class="m-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="row align-items-center">
        <div class="col-sm-12">
            <div class="text-sm-left">
                <div class="page-title-box">
                    <h4 class="page-title">{{ __("Tools") }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @if(Auth::user()->is_superadmin == 1)
        <div class="col-md-3">
            <form method="POST" id="catalog_copy_tools" action="{{route('tools.storeData')}}">
            @csrf
            @method('POST')
                <div class="card-box h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{ __('Catalog Copy Tool')}}</h4>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="copy_from" class="mr-3">{{ __("Copy From") }}</label>
                                <select class="form-control" id='copy_from' name="copy_from" required>
                                    <option value="">{{ __("Select vendor for copy") }}</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{@$vendor->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="copy_to" class="mr-3">{{ __("Copy To") }}</label>
                                <select class="form-control" id="copy_to" name="copy_to" data-placeholder="Choose ..." required>
                                    @foreach($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{@$vendor->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <div class="form-group mb-0">
                                <button class="btn btn-info btn-block" id="catalog_copy_button" type="submit"> {{ __("Copy") }} </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-3">
            <form method="POST" id="tax_copy_tools" action="{{route('tools.taxCopy')}}">
            @csrf
            @method('POST')
                <div class="card-box h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{ __('Tax Copy Tool')}}</h4>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="tax_category" class="mr-3">{{ __("Tax Category") }}</label>
                                <select class="form-control" id='tax_category' name="tax_category" required>
                                    <option value="">{{ __("Select Tax Category") }}</option>
                                    @foreach($taxCategory as $tax)
                                    <option value="{{$tax->id}}">{{$tax->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="product_category" class="mr-3">{{ __("Product Category") }}</label>
                                <select class="form-control select2-multiple" id='product_category' name="product_category[]" data-toggle="select2" multiple="multiple" data-placeholder="Choose ..." required>
                                    <option value="">{{ __("Select Product Category") }}</option>
                                    @foreach($categories as $category)
                                    <option value="{{$category->id}}">
                                        @if(!is_null($category->parent) && $category->parent_id > 1)
                                        {{@$category->parent->translation_one->name}}-> @endif
                                        {{@$category->translation_one->name}}
                                        @if(!is_null($category->vendor)) ({{@$category->vendor->name}}) @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <div class="form-group mb-0">
                                <button class="btn btn-info btn-block" id="tax_copy_button" type="submit"> {{ __("Update") }} </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @endif
        <div class="col-md-6">
            <form method="POST" id="upload_image_tool" action="{{route('tools.uploadImage')}}">
            @csrf
            @method('POST')
                <div class="card-box h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{ __('Upload Multiple Files Tool')}}</h4>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="card-box">
                                <!-- <div class="row mb-2"></div> -->
                                <div class="dropzone dropzone-previews" id="my-awesome-dropzone"></div>
                                <!-- <div class="imageDivHidden"></div> -->
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2 al_custom_copypath" style="display: none;">
                        <div class="col-md-12">

                            <table>
                                <tbody class="imageCopyName"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        @if(Auth::user()->is_superadmin == 1)
        <div class="col-md-3">
            <form method="POST" id="reset_config_form" action="{{route('reset.config')}}">
            @csrf
            @method('POST')
                <div class="card-box h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{ __('Reset to Default Configuration Settings')}}</h4>
                    </div>
                    <div class="row mt-2">
                       
                       
                        <div class="col-md-12 mt-3">
                            <div class="form-group mb-0">
                                <button class="btn btn-info btn-block" id="reset_config_btn" type="submit"> {{ __("Reset") }} </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

@endsection

@section('script')
<script src="{{asset('assets/js/dropzone.js')}}"></script>

<script type="text/javascript">
    var uploadedDocumentMap = {};
    Dropzone.autoDiscover = false;
    $(document).ready(function() {

        $("div#my-awesome-dropzone").dropzone({
            acceptedFiles: ".jpeg,.jpg,.png,.svg,.pdf",
             //acceptedFiles: "image/*,application/pdf",
            addRemoveLinks: true,
            url: "{{route('tools.uploadImage')}}",
            // params: {
            //     prodId: "1"
            // },
            parameter: "{{route('tools.uploadImage')}}",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(file, res) {
                $('.al_custom_copypath').show();
                if(res.data.ext == 'pdf'){
                    $('.imageCopyName').append('<tr id="'+res.data.image_id+'"><td style="padding: 5px"><img src="'+res.data.pdf_url+'"></td><td style="padding: 5px"><a href="'+res.data.show_image_url+'" target="_blank" style="font-size: 12px;" id="image_'+res.data.image_id+'">'+res.data.image_path+' </td><td style="padding: 5px"><label class="copy_link " id="cp_btn" title="copy" data-image_id="'+res.data.image_id+'" onclick="copyUrl(this)"><img src="{{asset("assets/icons/domain_copy_icon.svg")}}" alt="" style="margin: 0"><span class="copied_txt" id="show_copy_msg_on_click_copy" style="display: none;">Copied</span> </label></td></tr>');
                }else{
                    $('.imageCopyName').append('<tr id="'+res.data.image_id+'"><td style="padding: 5px"><img src="'+res.data.image_url+'"></td><td style="padding: 5px"><a href="'+res.data.show_image_url+'" target="_blank" style="font-size: 12px;" id="image_'+res.data.image_id+'">'+res.data.image_path+' </td><td style="padding: 5px"><label class="copy_link " id="cp_btn" title="copy" data-image_id="'+res.data.image_id+'" onclick="copyUrl(this)"><img src="{{asset("assets/icons/domain_copy_icon.svg")}}" alt="" style="margin: 0"><span class="copied_txt" id="show_copy_msg_on_click_copy" style="display: none;">Copied</span> </label></td></tr>');
                }

                uploadedDocumentMap[file.name] = res.data.image_id;
                var imageUrl = $('#pwd_spn').text();
                //alert();
                if(imageUrl != "")
                {
                    imageUrl = imageUrl+", ";
                }
                imageUrl = imageUrl+''+res.data.image_path;
                $('#pwd_spn').html(imageUrl);
            },
            removedfile: function(file) {
                file.previewElement.remove();
                var name = ''
                if (typeof file.file_name !== 'undefined') {
                    name = file.file_name
                } else {
                    name = uploadedDocumentMap[file.name]
                }
                $('#'+name).remove();

            },
        });

    });
$(document).ready(function() {
    $('#reset_config_form').submit(function(e) {

    	e.preventDefault();
    	
            Swal.fire({
                title: "{{__('Are you sure?')}}",
                text:"{{__('This will Reset all the configurations to default settings.')}}",
                    // icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes',
            }).then((result) => {
                if(result.value)
                {
                    $("#reset_config_form").off("submit").submit();
                }else{
                    return false;
                }
            });
       
    });
    $('#catalog_copy_tools').submit(function(e) {
        var copy_to = $("#copy_to").val();
    	var copy_from = $("#copy_from").val();
    	e.preventDefault();
    	if(copy_from != copy_to){
            Swal.fire({
                title: "{{__('Are you sure?')}}",
                text:"{{__('This will delete and overwrite all menu items.')}}",
                    // icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Copy',
            }).then((result) => {
                if(result.value)
                {
                    $("#catalog_copy_tools").off("submit").submit();
                }else{
                    return false;
                }
            });
        }else{
        	 Swal.fire ('Both vendors cannot be same', '', 'error');
        }
    });
    $('#tax_copy_tools').submit(function(e) {
        e.preventDefault();
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('This will update tax for all products in the selected category.')}}",
                // icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                $("#tax_copy_tools").off("submit").submit();
            }else{
                return false;
            }
        });
    });
});

    function copyUrl(obj)
    {
        var $temp = $("<input>");
        $("body").append($temp);
        var image_id = $(obj).data('image_id');
        $temp.val($('#image_'+image_id).text()).select();
        document.execCommand("copy");
        $temp.remove();
        $("#show_copy_msg_on_click_copy").show();
        setTimeout(function() {
            $("#show_copy_msg_on_click_copy").hide();
        }, 1000);
    }
</script>
@endsection
