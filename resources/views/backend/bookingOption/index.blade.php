@extends('layouts.vertical', ['title' => 'Booking Option'])
@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<link href="{{asset('assets/libs/nestable2/nestable2.min.css')}}" rel="stylesheet" type="text/css" />

<style>
    .select-category-lang label {
        font-size: 12px;
    }

    .select-category-lang select {
        height: 36px;
    }

    .modal-category-list {
        height: auto;
        overflow-y: hidden;
        flex-wrap: nowrap;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
    }

    .modal-category-list .col-sm {
        width: auto;
        max-width: 25%;
        min-width: 25%;
    }

    .category-modal-right {
        height: 100%;
        max-height: 650px;
        min-height: 650px;
        overflow-x: auto;
        overflow-y: scroll;
        border: 1px solid#eeeeeeab;
    }

    /*for custom  scrollbar css */
    .category-modal-right::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        background-color: #F5F5F5;
    }

    .category-modal-right::-webkit-scrollbar {
        width: 12px;
        background-color: #F5F5F5;
    }

    .category-modal-right::-webkit-scrollbar-thumb {
        border-radius: 10px;
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
        background-color: #D62929;
    }

    /*------end here*/
    #edit-category-form .modal-dialog.modal-dialog-centered.modal-md {
        max-width: 800px;
    }

    #add-category-form .modal-dialog.modal-dialog-centered.modal-md {
        max-width: 800px;
    }

    .Category-select_option select {
        border: none;
        padding-left: 0px;
        font-size: 14px;
        font-weight: 600;
        color: #6c757d;
    }

    .modal-category-list .select-category label:before {
        clip-path: polygon(0 0, 0 50%, 35% 0);
        font-size: 22px;
        padding: 2px 2px;
    }

    .modal-category-list .select-category label::after {
        display: none;
    }

    .modal-category-list .form-check-input:checked~label .category-img::after {
        opacity: 1;
    }

    .modal-category-list .select-category label .category-img {
        position: relative;
    }

    .modal-category-list .select-category label .category-img:after {
        background: rgb(0 0 0 / 31%);
        background-image: none !important;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        opacity: 0;
        content: "";
        position: absolute;
    }

    .category-icon-image .dropify-wrapper {
        height: 70px;
    }

    .modal-category-list .select-category .modal-category-btm-title h6 {
        text-align: left;
        font-size: 14px;
        margin: 0px;
        padding: 2px 0px;
    }

    .modal-category-list .select-category .modal-category-btm-title p {
        font-size: 12px;
    }

    .modal-category-list .select-category .modal-category-btm-title p i {
        font-size: 13px;
    }

    .modal-category-list .edit-cart-text input {
        font-size: 10px;
        border: 1px solid#eee;
        padding: 4px 10px;
        box-shadow: 1px 3px 4px #eee;
        border-radius: 8px;
        color: #9b9494;
        font-weight: 100;
    }

    /* .modal-category-list .edit-cart-text input::placeholder{
    font-size:12px;
} */



    /* .modal-category-list::-webkit-scrollbar {
        width: 1em;
    }

    .modal-category-list::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
    }

    .modal-category-list::-webkit-scrollbar-thumb {
        background-color: darkgrey;
        outline: 1px solid slategrey;
    } */

    .alHeightAutoScrooll {
        height: 300px;
        overflow: auto;
    }

    .alSmHeight {
        height: 150px;
        overflow: auto;
    }

</style>
@endsection
@section('content')
<div class="container-fluid alCatalogPage">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Booking Option') }}</h4>
            </div>
        </div>
        <div class="col-sm-12 text-sm-left">
            <div class="alert alert-success deletecategorymsg" style="display:none">
                <span></span>
            </div>
            @if (\Session::has('success'))
            <div class="alert alert-success">
                <span>{!! \Session::get('success') !!}</span>
            </div>
            @endif
            @if (\Session::has('error_delete'))
            <div class="alert alert-danger">
                <span>{!! \Session::get('error_delete') !!}</span>
            </div>
            @endif
        </div>
    </div>
    <div class="row catalog_box al_catalog_box">
        <div class="col-lg-8">
            <div class="card-box">
                <div class="row" style="max-height: 600px; overflow-x: auto">
                    <div class="col-sm-12 mb-2 d-flex justify-content-between align-items-center">
                        <h4 class=""> {{ __("Booking Option") }}</h4>
                        <button class="btn btn-info waves-effect waves-light text-sm-right openAddonModal" dataid="0">
                            <i class="mdi mdi-plus-circle mr-1"></i> {{ __("Add") }}
                        </button>
                    </div>
                    <div class="col-md-12">
                        <div class="row addon-row">
                            <div class="col-md-12">
                                <form name="addon_order" id="addon_order" action="" method="post">
                                    @csrf
                                    <input type="hidden" name="orderData" id="orderVariantData" value="" />
                                </form>
                                <table class="table table-centered table-nowrap table-striped" id="varient-datatable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __("Title") }}</th>
                                            <th>{{ __("Action") }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookingOption as $set)
                                        <tr>
                                            <td>{{$set->id}}</td>
                                            <td>{{$set->title}}</td>
                                            <td>
                                                <a class="action-icon editBookingOption" dataid="{{$set->id}}" href="javascript:void(0);">
                                                    <h3> <i class="mdi mdi-square-edit-outline"></i> </h3>
                                                </a>

                                                <a class="action-icon deleteBookingOption" dataid="{{$set->id}}" href="javascript:void(0);"> <i class="mdi mdi-delete"></i></a>
                                                <form action="{{route('booking.option.delete', $set->id)}}" method="POST" style="display: none;" id="bookingOptionDeleteForm{{$set->id}}">
                                                    @csrf
                                                    @method('DELETE')

                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="editdAddonmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h4 class="modal-title">{{ __("Update Rental Protection") }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <form id="editAddonForm" method="post" enctype="multipart/form-data" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-body" id="editAddonBox">

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-info waves-effect waves-light editAddonSubmit">{{ __("Submit") }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="addAddonmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h4 class="modal-title">{{ __("Create Booking Option") }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <form id="addAddonForm" method="post" enctype="multipart/form-data" action="{{route('booking.option.store')}}">
                        @csrf
                        <div class="modal-body" id="AddAddonBox">
                            <div class="row">
                                    <div class="col-6 mb-2">
                                        {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
                                        {!! Form::text('title','', ['class'=>'form-control', 'id' => 'title', 'required' => 'required']) !!}
                                    </div>
                                    <div class="col-6 mb-2">
                                        {!! Form::label('price', __('Price('.$clientCurrency->currency->symbol.')'),['class' => 'control-label']) !!}
                                        {!! Form::text('price', '', ['class'=>'form-control', 'id' => 'price']) !!}
                                    </div>
                                    <div class="col-12 mb-2">
                                        {!! Form::label('description', __('Description'),['class' => 'control-label']) !!}
                                        {!! Form::textarea('description', '', ['class'=>'form-control', 'id' => 'body_html', 'rows' => '5']) !!}
                                    </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-info waves-effect waves-light addAddonSubmit">{{ __("Submit") }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('backend.common.category-modals')
@include('backend.catalog.modals')
@endsection
@section('script')
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="{{asset('assets/libs/nestable2/nestable2.min.js')}}"></script>
<script src="{{asset('assets/js/pages/my-nestable.init.js')}}"></script>
<script src="{{asset('assets/libs/dragula/dragula.min.js')}}"></script>
<script src="{{asset('assets/js/pages/dragula.init.js')}}"></script>
<script src="{{asset('assets/js/jscolor.js')}}"></script>
<script src="{{ asset('assets/js/jquery.tagsinput-revisited.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/jquery.tagsinput-revisited.css') }}" />
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>


@include('backend.common.category-script')
@include('backend.catalog.pagescript')
@include('backend.vendor.pagescript')
<script type="text/javascript">
    var tagList = "";
    tagList = tagList.split(',');

    function makeTag(tagList = '') {
        $('.myTag1').tagsInput({
            'autocomplete': {
                source: tagList
            }
        });
    }
    $('.saveList').on('click', function(e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token-z"]').attr('content')
            }
        });
        var data = $('.dd').nestable('serialize');
        document.getElementById('orderDta').value = JSON.stringify(data);
        $('#category_order').submit();
    });
    //facilty_id model
    $('#add_facilties_modal_btn').click(function(e) {
        document.getElementById("faciltyForm").reset();
        $('#faciltyForm input[name=facilty_id]').val("");
        $('#add_facilty_modal').modal('show');
        $('#add_facilty_modal #standard-modalLabel').html('Add Vendor Tags');
    });
    //vendor registration document
    $(document).on('click', '.submitSaveFacilty', function(e) {
        e.disabled = true;
        var vendor_registration_document_id = $("#add_facilty_modal input[name=facilty_id]").val();
        if (vendor_registration_document_id) {
            var post_url = "{{ route('facilty.update') }}";
        } else {
            var post_url = "{{ route('facilty.store') }}";
        }
        var form_data = new FormData(document.getElementById("faciltyForm"));
        $.ajax({
            url: post_url
            , method: 'POST'
            , data: form_data
            , contentType: false
            , processData: false
            , success: function(response) {
                if (response.status == 'Success') {
                    $('#add_or_edit_social_media_modal').modal('hide');
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                    setTimeout(function() {
                        location.reload()
                    }, 2000);
                } else {
                    $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
                }
            }
            , error: function(response) {
                e.disabled = false;
                $('#add_facilty_modal .social_media_url_err').html('The default language name field is required.');
            }
        });
    });

    $(document).on("click", ".edit_facilty_btn", function() {
        let facilty_id = $(this).data('facilty_id');
        //console.log(facilty_id);
        editfaciltyForm(facilty_id);
    });

    function editfaciltyForm(facilty_id) {
        let language_id = $('#option_client_language').val();
        $('#faciltyForm input[name=facilty_id]').val(facilty_id);
        $.ajax({
            method: 'GET'
            , data: {
                facilty_id: facilty_id
                , language_id: language_id
            }
            , url: "{{ route('facilty.edit') }}"
            , success: function(response) {
                if (response.status = 'Success') {
                    console.log(response.data);
                    var imagePath = response.data.image.image_fit + '90/90' + response.data.image.image_path;
                    imagePath = imagePath.replace('@webp', '');
                    console.log(imagePath);
                    //   $(document).find("#add_vendor_registration_document_modal select[name=file_type]").val(response.data.file_type).change();

                    $("#add_facilty_modal input[name=facilty_id]").val(response.data.id);

                    $("#add_facilty_modal input[name='facilty_image']").attr('data-default-file', imagePath);
                    $('#add_facilty_modal #standard-modalLabel').html('Update facilty');
                    $('#add_facilty_modal').modal('show');
                    $('.dropify').dropify();

                    $.each(response.data.translations, function(index, value) {
                        $('#add_facilty_modal #facilty_name_' + value.language_id).val(value.name);
                    });
                }
            }
            , error: function() {}
        });
    }
    // delete kyc document
    $(document).on("click", ".delete_facilty_btn", function() {
        var facilty_id = $(this).data('facilty_id');
        Swal.fire({
            title: "{{__('Are you Sure?')}}"
            , icon: 'info'
            , showCancelButton: true
            , confirmButtonText: 'Ok'
        , }).then((result) => {
            if (result.value) {
                $.ajax({
                    type: "POST"
                    , dataType: 'json'
                    , url: "{{ route('facilty.delete') }}"
                    , data: {
                        _token: "{{ csrf_token() }}"
                        , facilty_id: facilty_id
                    }
                    , success: function(response) {
                        if (response.status == "Success") {
                            $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                            setTimeout(function() {
                                location.reload()
                            }, 2000);
                        }
                    }
                });
            }
        });
    });


    $('#add_facilties_modal_btn').click(function(e) {
        document.getElementById("faciltyForm").reset();
        $('#faciltyForm input[name=facilty_id]').val("");
        $('#add_facilty_modal').modal('show');
        $('#add_facilty_modal #standard-modalLabel').html('Add Vendor Tags');
    });


    $('#addBrandForm').submit(function() {
        var gR = $("#cateSelectBox :checked");
        var valid = 0;
        var flag = true;
        $(this).find('input[type=text]').each(function() {
            if ($.trim($(this).val()) != "") valid = 1;
        });
        if (valid == 0) {
            $("#brand-title-error").css("color", "red");
            $("#brand-title-error").html("Please enter at least one title");
            flag = false;
        }

        if (gR.length == 0) {
            $("#cat-error").css("color", "red");
            $("#cat-error").html("Please select at least one category");
            flag = false;
        }
        return flag;
    });
    $(document).on('change', "#cateSelectBox", function() {
        var none = $("#cateSelectBox :checked");
        if (none.length > 0) {
            $("#cat-error").html('');
        } else {
            $("#cat-error").css("color", "red");
            $("#cat-error").html("Please select at least one category");
        }
    });

    $(document).on('keyup', 'input[type=text]', function() {
        var valid = 0;

        $(this).each(function() {
            if ($.trim($(this).val()) != "") valid = 1;
        });

        if (valid > 0) {
            $("#brand-title-error").html("");
        } else {
            $("#brand-title-error").css("color", "red");
            $("#brand-title-error").html("Please enter at least one title");
        }
    });
</script>

@endsection
