@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Estimate Products'])
@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<link href="{{asset('assets/libs/nestable2/nestable2.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/ion-rangeslider/ion-rangeslider.min.css')}}" rel="stylesheet" type="text/css" />
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__rendered{
        height:calc(1.5em + 0.9rem + 2px);
    }
    .product_category{
        height:calc(1.9em + 0.9rem + 2px);
    }
    .matching_logic option:nth-child(1), option:nth-child(4) {
    font-weight:bold;
}
</style>
@endsection
@section('content')



    <div class="container-fluid custom-toggle">
        <div class="row">
            <div class="col-10">
                <div class="page-title-box">
                    <h4 class="page-title text-uppercase">{{ __('Estimate Products') }}</h4>
                </div>
            </div>
            <div class="col-2">
                <div class="page-title-box text-right mt-3 mt-2">
                    <form action="{{route('estimations.updateEstimationMatchingLogic')}}" method="POST">
                        @csrf
                        <select onchange="this.form.submit()" class="matching_logic form-control" id="matching_logic" name="matching_logic">
                            @if (!empty($clientPreference) && !empty($clientPreference->estimation_matching_logic))
                                <option value="String Match" {{(($clientPreference->estimation_matching_logic == "String Match")?"Selected":"")}}>String Match</option>
                               
                                {{-- @if ($clientPreference->estimation_matching_logic == 'Automatic')
                                    <option value="String Match">String Match</option>
                                    <option value="Bidding">Bidding</option>
                                @elseif ($clientPreference->estimation_matching_logic == 'String Match')
                                    <option value="Automatic">Automatic</option>
                                    <option value="Bidding">Bidding</option>
                                @elseif ($clientPreference->estimation_matching_logic == 'Bidding')
                                    <option value="Automatic">Automatic</option>
                                    <option value="String Match">String Match</option>
                                @endif  --}}
                            @else
                                <option value="" selected>Choose Estimation Logic</option>
                                {{-- <option value="Automatic">Automatic</option> --}}
                                <option value="String Match">String Match</option>
                                {{-- <option value="Bidding">Bidding</option> --}}
                            @endif
                            
                        </select>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="text-sm-left">
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <span>{!! \Session::get('success') !!}</span>
                        </div>
                    @elseif(\Session::has('error'))
                        <div class="alert alert-danger">
                            <span>{!! \Session::get('error') !!}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>


        <div class="row">
         <div class="col-xl-6 mb-3">

            <form method="POST" class="h-100" action="{{ route('referandearn.update', Auth::user()->code) }}">
                @csrf
                <div class="card-box product-tags mb-0 pb-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="header-title text-uppercase">{{ __('Product') }}</h4>
                        <a class="btn btn-info d-block" id="add_product_tag_modal_btn">
                            <i class="mdi mdi-plus-circle mr-1"></i>{{ __('Add') }}
                        </a>
                    </div>
                    <div class="table-responsive mt-3 mb-1">
                        <table class="table table-centered table-nowrap table-striped" id="promo-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Icon') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="post_list">
                                @forelse($estimate_products as $tag)
                                    <tr>
                                        <td>
                                            @if (isset($tag->icon) && !empty($tag->icon)) <img src="{{ $tag->icon['proxy_url'] . '100/100' . $tag->icon['image_path'] }}">@endif
                                        </td>
                                        <td>
                                            <a class="edit_product_tag_btn" data-estimate_product_id="{{ $tag->id }}"
                                                href="javascript:void(0)">
                                                @if ($tag->category)
                                                    {{ $tag->category->translation ? $tag->category->translation[0]->name : 'N/A' }}
                                                @endif
                                                
                                            </a>
                                        </td>
                                        <td>
                                            <a class="edit_product_tag_btn" data-estimate_product_id="{{ $tag->id }}"
                                                href="javascript:void(0)">
                                                {{ $tag->primary ? $tag->primary->name : '' }}
                                            </a>
                                        </td>
                                        <td>
                                            <a class="edit_product_tag_btn" data-estimate_product_id="{{ $tag->id }}"
                                                href="javascript:void(0)">
                                                {{ $tag->primary->price ? $tag->primary->price : '0.00' }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="inner-div" style="float: left;">
                                                    <a class="action-icon edit_product_tag_btn"
                                                        data-estimate_product_id="{{ $tag->id }}"
                                                        href="javascript:void(0)">
                                                        <i class="mdi mdi-square-edit-outline"></i>
                                                    </a>
                                                </div>
                                                <div class="inner-div">
                                                    <button type="button"
                                                        class="btn btn-primary-outline action-icon delete_product_tag_btn"
                                                        data-estimate_product_id="{{ $tag->id }}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr align="center">
                                        <td colspan="4" style="padding: 20px 0">{{ __('Products not found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>


        <div class="col-xl-6">
            <div class="card-box">
                <div class="row">
                    <div class="col-sm-8">
                        <h4 class="mb-4"> {{ __('Addon Set') }}</h4>
                    </div>
                    <div class="col-sm-4 text-right">
                        <button class="btn btn-info waves-effect waves-light text-sm-right openAddonModal" dataid="0">
                            <i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                        </button>
                    </div>
                    <div class="col-md-12">
                        <div class="row addon-row">
                            <div class="col-md-12">
                                <form name="addon_order" id="addon_order" action="" method="post">
                                    @csrf
                                    <input type="hidden" name="orderData" id="orderVariantData" value="" />
                                </form>
                                <table class="table table-centered table-responsive table-nowrap table-striped" id="varient-datatable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Select(Min - Max)') }}</th>
                                            <th>{{ __('Options') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($addon_sets as $set)
                                            <tr>
                                                <td>{{ $set->id }}</td>
                                                <td>{{ $set->title }}</td>
                                                <td>{{ $set->min_select }} - {{ $set->max_select }}</td>
                                                <td>
                                                    @foreach ($set->option as $opt)
                                                        <span>{{ $opt->title }} -
                                                            {{ $clientCurrency->currency->symbol }}{{ $opt->price }}</span><br />
                                                        <span></span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <a class="action-icon editESAddonBtn" dataid="{{ $set->id }}"
                                                        href="javascript:void(0);">
                                                        <h3> <i class="mdi mdi-square-edit-outline"></i> </h3>
                                                    </a>

                                                    <a class="action-icon deleteAddon" dataid="{{ $set->id }}"
                                                        href="javascript:void(0);"> <i class="mdi mdi-delete"></i></a>
                                                    <form action="{{ route('estimationsAddon.destroy', $set->id) }}" method="POST"
                                                        style="display: none;" id="addonDeleteForm{{ $set->id }}">
                                                        <input type="hidden" name="id" value="{{Crypt::encrypt($set->id)}}">
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
        </div>
       


        <!-- modal for product tags -->
        <div id="add_product_tag_modal" class="modal fade" tabindex="-1" role="dialog"
            aria-labelledby="standard-modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h4 class="modal-title" id="standard-modalLabel">{{ __('Add Product') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <form id="productTagForm" method="POST" action="javascript:void(0)" enctype="multipart/form-data">
                            @csrf
                            <div id="save_product_tag">
                                <input type="hidden" name="estimate_product_id" value="">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ __('Upload Icon') }}</label>
                                        <input type="file" accept="image/*" data-plugins="dropify" name="icon"
                                            class="dropify" />
                                        <label class="logo-size text-right w-100">{{ __('Icon Size') }} 100X100</label>
                                    </div>

                                    @forelse($client_languages as $k => $client_language)
                                        <div class="col-md-6 mb-2">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group position-relative">
                                                        <label for="">{{ __('Name') }}
                                                            ({{ $client_language->langName }})</label>
                                                        <input class="form-control"
                                                            name="language_id[{{ $k }}]" type="hidden"
                                                            value="{{ $client_language->langId }}">
                                                        <input class="form-control" name="name[{{ $k }}]"
                                                            type="text"
                                                            id="product_tag_name_{{ $client_language->langId }}">
                                                    </div>
                                                    @if ($k == 0)
                                                        <span class="text-danger error-text product_tag_err"></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                    @endforelse
                                    <div class="col-md-4 mb-2">
                                        <label for="product_category">{{ __("Categories") }}</label>
                                        <select class="product_category form-control" id="product_category" name="product_category">
                                            <option value="" selected>--Choose--</option>
                                            @foreach($product_categories as $product_category)
                                            <option value="{{$product_category['id']}}">{{$product_category['hierarchy']}}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="product_addons">{{ __("Product Addons") }}</label>
                                        <select class="product_addons form-control select2-multiple" id="product_addons" name="product_addons[]" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                            @foreach($getAllEstimateAddonSets as $addonSet)
                                                <option value="{{$addonSet->id}}">{{$addonSet->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-2">
                                        <label for="product_addons">{{ __("Default Price") }}</label>
                                        <input type="text" class="form-control" id="product_tag_name_price" name="price" placeholder="" value="{{$client_language->price}}">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary submitSaveProductTag">
                            {{-- ADDED BY OVI  --}}
                            <img id="saveLoadingImg" src="{{asset('assets/images/loading_new.gif')}}" style="width:5%; display:none;" alt="">
                            {{ __('Save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>



        <div id="addAddonmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h4 class="modal-title">{{ __('Create AddOn Set') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <form id="addAddonForm" method="post" enctype="multipart/form-data"
                        action="{{ route('estimationsAddon.store') }}">
                        @csrf
                        <div class="modal-body" id="AddAddonBox">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row rowYK">
                                        <div class="col-md-12">
                                            <h5>{{ __('Addon Title') }}</h5>
                                        </div>
                                        <div class="col-md-12" style="overflow-x: auto;">
                                            <table class="table table-borderless mb-0 " id="banner-datatable">
                                                <tr>
                                                    @foreach ($languages as $langs)
                                                        <th>{{ $langs->langName }}</th>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    @foreach ($languages as $langs)
                                                        <td>
                                                            {!! Form::hidden('language_id[]', $langs->langId) !!}
                                                            <input type="text" name="title[]" value=""
                                                                class="form-control" @if ($langs->is_primary == 1) required @endif>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row rowYK mb-2">
                                        <div class="col-md-12">
                                            <h5>{{ __('Addon Options') }}</h5>
                                        </div>
                                        <div class="col-md-12" style="overflow-x: auto;">
                                            <table class="table table-borderless mb-0 optionTableAdd" id="banner-datatable">
                                                <tr class="trForClone">
                                                    <th>{{ __('Price') }}({{ $clientCurrency->currency->symbol }})
                                                    </th>
                                                    @foreach ($languages as $langs)
                                                        <th>{{ $langs->langName }}</th>
                                                    @endforeach
                                                    <th></th>
                                                </tr>
                                                <tr class="input_tr">
                                                    <td>{!! Form::text('price[]', null, ['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'min' => '1', 'required' => 'required']) !!}</td>
                                                    @foreach ($languages as $k => $langs)
                                                        <td><input type="text" name="opt_value[{{ $k }}][]"
                                                                class="form-control" @if ($langs->is_primary == 1) required @endif>
                                                        </td>
                                                    @endforeach
                                                    <td class="lasttd"></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="button"
                                                class="btn btn-info waves-effect waves-light addESOptionRow-Add">{{ __('Add Option') }}</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group" style="display:none;">
                                                {!! Form::label('title', __('Min Select'), ['class' => 'control-label']) !!}
                                                {!! Form::text('min_select', 1, ['class' => 'form-control', 'id' => 'min', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                                <span class="invalid-feedback" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group" style="display:none;">
                                                {!! Form::label('title', __('Max Select'), ['class' => 'control-label']) !!}
                                                {!! Form::text('max_select', 1, ['class' => 'form-control', 'id' => 'max', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                                <span class="invalid-feedback" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-2">
                                            <div class="price-range-slider">
                                                {!! Form::label('title', __('Min & Max Range'), ['class' => 'control-label']) !!}:<input type="text" id="slider_output" readonly=""
                                                    style="border:0; color:#f6931f; font-weight:bold;">
                                                <div id="slider-range" class="range-bar"></div>
                                            </div>
                                            <div class="row slider-labels">
                                                <div class="col-xs-6 caption">
                                                    <strong>{{ __('Min') }}:</strong> <span
                                                        id="slider-range-value1"></span>
                                                </div>
                                                <div class="col-xs-6 text-right caption">
                                                    <strong>{{ __('Max') }}:</strong> <span
                                                        id="slider-range-value2"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <p>{{ __('If max select is greater than total option than max will be total option') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit"
                                class="btn btn-info waves-effect waves-light addAddonSubmit">{{ __('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="editESAddonmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h4 class="modal-title">{{ __('Create AddOn Set') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <form id="editAddonForm" method="post" enctype="multipart/form-data" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-body" id="editAddonBox">

                        </div>
                        <div class="modal-footer">
                            <button type="submit"
                                class="btn btn-info waves-effect waves-light editAddonSubmit">{{ __('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endsection
    @section('script')
    <script src="{{asset('assets/libs/nestable2/nestable2.min.js')}}"></script>
<script src="{{asset('assets/js/pages/my-nestable.init.js')}}"></script>
<script src="{{asset('assets/js/jscolor.js')}}"></script>
<script src="{{asset('assets/libs/ion-rangeslider/ion-rangeslider.min.js')}}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/jquery.tagsinput-revisited.css') }}" />
<script src="{{ asset('assets/js/jquery.tagsinput-revisited.js') }}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
@include('backend.vendor.pagescript')
@include('backend.common.category-script')
        <script type="text/javascript">

    
            $('#add_product_tag_modal_btn').click(function(e) {
                $("#product_addons").find('option').attr("selected",false) ;
                $('#product_addons').val(null).trigger('change');

                document.getElementById("productTagForm").reset();
                $('#add_product_tag_modal input[name=estimate_product]').val("");
                $('#add_product_tag_modal').modal('show');
                $('#add_product_tag__modal #standard-modalLabel').html('Add Tag');
            });

            $(document).on("change", "#file_type_select", function() {
                var file_type = $(this).val();
                if (file_type == 'selector') {
                    $("#selector_div").removeClass("d-none");
                    var classoption_section = $('#option_div').find('.option_section');
                    if (classoption_section.length == 0) {
                        addoptionTemplate(0);
                    }
                } else {
                    $("#selector_div").addClass("d-none");
                }
            });
            $(document).on('click', '.add_more_button', function() {
                var main_id = $(this).data('id');
                addoptionTemplate(main_id);
                console.log($('.add_more_button').length);
            });
            $(document).on('click', '.remove_more_button', function() {
                var main_id = $(this).data('id');
                removeSeletOptionSectionTemplate(main_id);
                $('.add_more_button').each(function(key, value) {
                    if (key == ($('.add_more_button').length - 1)) {
                        $('#add_button_' + $(this).data('id')).show();
                    }
                });
            });
            $(document).on("change", "#option_client_language", function() {
                let vendor_registration_document_id = $('input[name="vendor_registration_document_id"]').val();
                editVendorRegistrationForm(vendor_registration_document_id);
            });

            function removeSeletOptionSectionTemplate(div_id) {
                $('#option_section_' + div_id).remove();
            }
            $(document).on('click', '.addESOptionRow-Add', function(e) {
                var d = new Date();
                var n = d.getTime();
                var $tr = $('.optionTableAdd tbody>tr:first').next('tr');
                var $clone = $tr.clone();
                $clone.find(':text').val('');
                $clone.find('.hexa-colorpicker').attr("id", "hexa-colorpicker-" + n);
                $clone.find('.lasttd').html(
                    '<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>'
                );
                $('.optionTableAdd').append($clone);
                var slider = $("#slider-range").data("ionRangeSlider");
                var from = slider.result.from;
                var to = $('#banner-datatable >tbody >tr.input_tr').length;
                slider.update({
                    min: from,
                    max: to,
                });

            });

            $(document).on('click', '.addESOptionRow-edit', function(e) {
                var d = new Date();
                var n = d.getTime();
                var $tr = $('.optionTableEdit tbody>tr:first').next('tr');
                var $clone = $tr.clone();
                $clone.find(':text').val('');
                $clone.find(':hidden').val('');
                $clone.find('.hexa-colorpicker').attr("id", "hexa-colorpicker-" + n);
                $clone.find('.lasttd').html(
                    '<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>'
                    );
                $('.optionTableEdit').append($clone);
                var slider = $("#slider-range1").data("ionRangeSlider");
                var from = slider.result.from;
                var to = $('#edit_addon-datatable >tbody >tr.input_tr').length;
                slider.update({
                    min: from,
                    max: to,
                });
            });

            $("#addAddonForm").on('click', '.deleteCurRow', function() {
        $(this).closest('tr').remove();
    });

    $("#editAddonForm").on('click', '.deleteCurRow', function() {
        $(this).closest('tr').remove();
    });

            function addoptionTemplate(section_id) {
                section_id = parseInt(section_id);
                section_id = section_id + 1;
                var data = '';

                var price_section_temp = $('#vendorSelectorTemp').html();
                var modified_temp = _.template(price_section_temp);
                var result_html = modified_temp({
                    id: section_id,
                    data: data
                });
                $("#table_body").append(result_html);
                $('.add_more_button').hide();
                $('#add_button_' + section_id).show();
            }
            $('#add_slot_modal_btn').click(function(e) {
                document.getElementById("slotForm").reset();
                $('#add_slot_modal input[name=slot_id]').val("");
                $('#add_slot_modal').modal('show');
                $('#add_slot__modal #standard-modalLabel').html('Add Slot');
            });



            ///   product tag ////
            $(document).on("click", ".delete_product_tag_btn", function() {
                var estimate_product_id = $(this).data('estimate_product_id');
                Swal.fire({
                    title: "{{ __('Are you Sure?') }}",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Ok',
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            type: "POST",
                            dataType: 'json',
                            url: "{{ route('estimations.delete') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                estimate_product_id: estimate_product_id
                            },
                            success: function(response) {
                                if (response.status == "Success") {
                                    $.NotificationApp.send("Success", response.message, "top-right",
                                        "#5ba035", "success");
                                    setTimeout(function() {
                                        location.reload()
                                    }, 2000);
                                }
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.submitSaveProductTag', function(e) {
                $("#saveLoadingImg").show();
                var estimate_product_id = $("#add_product_tag_modal input[name=estimate_product_id]").val();
                if (estimate_product_id) {
                    var post_url = "{{ route('estimations.update') }}";
                } else {
                    var post_url = "{{ route('estimations.create') }}";
                }
                var form_data = new FormData(document.getElementById("productTagForm"));
                $.ajax({
                    url: post_url,
                    method: 'POST',
                    data: form_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status == 'Success') {
                            $('#add_or_edit_social_media_modal').modal('hide');
                            $("#saveLoadingImg").hide();

                            $("#product_addons").find('option').attr("selected",false) ;
                            $('#product_addons').val(null).trigger('change');

                            $.NotificationApp.send("Success", response.message, "top-right", "#5ba035",
                                "success");
                            setTimeout(function() {
                                location.reload()
                            }, 2000);
                        } else {
                            $.NotificationApp.send("Error", response.message, "top-right", "#ab0535",
                                "error");
                                $("#saveLoadingImg").hide();
                        }
                    },
                    error: function(response) {
                        $('#add_product_tag_modal .product_tag_err').html(
                            'The default language name field is required.');
                            $("#saveLoadingImg").hide();
                    }
                });
            });
            $(document).on("click", ".edit_product_tag_btn", function() {
                $("#product_addons").find('option').attr("selected",false) ;
                $('#product_addons').val(null).trigger('change');

                let estimate_product_id = $(this).data('estimate_product_id');
                
                $('#add_product_tag_modal input[name=estimate_product_id]').val(estimate_product_id);
                $.ajax({
                    method: 'GET',
                    data: {
                        estimate_product_id: estimate_product_id
                    },
                    url: "{{ route('estimations.edit') }}",
                    success: function(response) {
                        if (response.status = 'Success') {
                            $("#add_product_tag_modal input[name=estimate_product_id]").val(response.data[0]
                                .id);
                            $('#add_product_tag_modal #product_tag_name_price').val(response.data[0].translations[0].price);
                            $('#add_product_tag_modal #standard-modalLabel').html('Update Product Tag');
                            $('#add_product_tag_modal').modal('show');
                            $.each(response.data[0].translations, function(index, value) {
                                $('#add_product_tag_modal #product_tag_name_' + value.language_id)
                                    .val(value.name);
                            });
                            $("#product_addons").val("");
                            $("#product_addons").trigger("change");
                            // OVI
                            $.each(response.data[1], function(index, value) {
                                $('#product_addons').append('<option selected value="'+value.estimate_addon_set.id+'">'+value.estimate_addon_set.title+'</option>');
                            });
                        }
                    },
                    error: function() {

                    }
                });
            });
            // end product tag ////

            function generateRandomString(length) {
                var text = "";
                var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                for (var i = 0; i < length; i++)
                    text += possible.charAt(Math.floor(Math.random() * possible.length));
                return text;
            }

            function genrateKeyAndToken() {
                var key = generateRandomString(30);
                var token = generateRandomString(60);
                $('#personal_access_token_v1').val(key);
                $('#personal_access_token_v2').val(token);
            }
            var autocomplete = {};
            var autocompletesWraps = [];
            var count = 1;
            editCount = 0;
            $(document).ready(function() {
                autocompletesWraps.push('Default_location_name');
                loadMap(autocompletesWraps);
            });

            function loadMap(autocompletesWraps) {
                $.each(autocompletesWraps, function(index, name) {
                    const geocoder = new google.maps.Geocoder;
                    if ($('#' + name).length == 0) {
                        return;
                    }
                    autocomplete[name] = new google.maps.places.Autocomplete(document.getElementById(name), {
                        types: ['geocode']
                    });
                    if(is_map_search_perticular_country){
                        autocomplete[name].setComponentRestrictions({'country': [is_map_search_perticular_country]});
                    }
                    google.maps.event.addListener(autocomplete[name], 'place_changed', function() {
                        var place = autocomplete[name].getPlace();
                        geocoder.geocode({
                            'placeId': place.place_id
                        }, function(results, status) {
                            if (status === google.maps.GeocoderStatus.OK) {
                                const lat = results[0].geometry.location.lat();
                                const lng = results[0].geometry.location.lng();
                                document.getElementById('Default_latitude').value = lat;
                                document.getElementById('Default_longitude').value = lng;
                            }
                        });
                    });
                });

            }
            $('#show-map-modal').on('hide.bs.modal', function() {
                $('#add-customer-modal').removeClass('fadeIn');

            });

            $(document).on('click', '.showMap', function() {
                var no = $(this).attr('num');
                var lats = document.getElementById('Default_latitude').value;
                var lngs = document.getElementById('Default_longitude').value;

                document.getElementById('map_for').value = no;

                if (lats == null || lats == '0') {
                    lats = 30.53899440;
                }
                if (lngs == null || lngs == '0') {
                    lngs = 75.95503290;
                }

                var myLatlng = new google.maps.LatLng(lats, lngs);
                var mapProp = {
                    center: myLatlng,
                    zoom: 13,
                    mapTypeId: google.maps.MapTypeId.ROADMAP

                };
                var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
                var marker = new google.maps.Marker({
                    position: myLatlng,
                    map: map,
                    title: 'Hello World!',
                    draggable: true
                });
                document.getElementById('lat_map').value = lats;
                document.getElementById('lng_map').value = lngs;
                google.maps.event.addListener(marker, 'drag', function(event) {
                    document.getElementById('lat_map').value = event.latLng.lat();
                    document.getElementById('lng_map').value = event.latLng.lng();
                });

                google.maps.event.addListener(marker, 'dragend', function(event) {
                    var zx = JSON.stringify(event);
                    console.log(zx);


                    document.getElementById('lat_map').value = event.latLng.lat();
                    document.getElementById('lng_map').value = event.latLng.lng();
                });
                $('#add-customer-modal').addClass('fadeIn');
                $('#show-map-modal').modal({
                    keyboard: false
                });

            });

            $(document).on('click', '.selectMapLocation', function() {

                var mapLat = document.getElementById('lat_map').value;
                var mapLlng = document.getElementById('lng_map').value;
                var mapFor = document.getElementById('map_for').value;

                document.getElementById('Default_latitude').value = mapLat;
                document.getElementById('Default_longitude').value = mapLlng;

                $('#show-map-modal').modal('hide');
            });


            var hyprlocal = $('#is_hyperlocal');
            if (hyprlocal.length > 0) {
                hyprlocal[0].onchange = function() {

                    if ($('#is_hyperlocal:checked').length != 1) {
                        $('.disableHyperLocal').hide();
                    } else {
                        $('.disableHyperLocal').show();
                    }
                }
            }

            var delivery_service = $('#need_delivery_service');
            var dispatcherDiv = $('#need_dispacher_ride');
            var need_dispacher_home_other_service = $('#need_dispacher_home_other_service');
            var laundry_service = $('#need_laundry_service');

            if (delivery_service.length > 0) {
                delivery_service[0].onchange = function() {

                    if ($('#need_delivery_service:checked').length != 1) {
                        $('.deliveryServiceFields').hide();
                    } else {
                        $('.deliveryServiceFields').show();
                    }
                }
            }

            if (laundry_service.length > 0) {
                laundry_service[0].onchange = function() {

                    if ($('#need_laundry_service:checked').length != 1) {
                        $('.laundryServiceFields').hide();
                    } else {
                        $('.laundryServiceFields').show();
                    }
                }
            }

            if (dispatcherDiv.length > 0) {
                dispatcherDiv[0].onchange = function() {
                    console.log('ok');
                    if ($('#need_dispacher_ride:checked').length != 1) {
                        $('.dispatcherFields').hide();
                    } else {
                        $('.dispatcherFields').show();
                    }
                }
            }

            if (need_dispacher_home_other_service.length > 0) {
                need_dispacher_home_other_service[0].onchange = function() {

                    if ($('#need_dispacher_home_other_service:checked').length != 1) {
                        $('.home_other_dispatcherFields').hide();
                    } else {
                        $('.home_other_dispatcherFields').show();
                    }
                }
            }


            // var fb_login = $('#fb_login');

            // fb_login[0].onchange = function() {
            //     if ($('#fb_login:checked').length != 1) {
            //         $('.fb_row').hide();
            //     } else {
            //         $('.fb_row').show();
            //     }
            // }

            // var twitter_login = $('#twitter_login');

            // twitter_login[0].onchange = function() {
            //     if ($('#twitter_login:checked').length != 1) {
            //         $('.twitter_row').hide();
            //     } else {
            //         $('.twitter_row').show();
            //     }
            // }

            // var google_login = $('#google_login');

            // google_login[0].onchange = function() {
            //     if ($('#google_login:checked').length != 1) {
            //         $('.google_row').hide();
            //     } else {
            //         $('.google_row').show();
            //     }
            // }

            // var apple_login = $('#apple_login');

            // apple_login[0].onchange = function() {

            //     if ($('#apple_login:checked').length != 1) {
            //         $('.apple_row').hide();
            //     } else {
            //         $('.apple_row').show();
            //     }
            // }

            var dinein_option = $('#dinein_check');
            if (dinein_option.length > 0) {
                dinein_option[0].onchange = function() {
                    optionsChecked("dinein_check");
                }
            }

            var takeaway_option = $('#takeaway_check');
            if (takeaway_option.length > 0) {
                takeaway_option[0].onchange = function() {
                    optionsChecked("takeaway_check");
                }
            }

            var delivery_option = $('#delivery_check');
            if (delivery_option > 0) {
                delivery_option[0].onchange = function() {
                    optionsChecked("delivery_check");
                }
            }


            function optionsChecked(id) {
                var delivery_checked = $("#delivery_check").is(":checked");
                var takeaway_checked = $("#takeaway_check").is(":checked");
                var dinein_checked = $("#dinein_check").is(":checked");
                if (dinein_checked == false && takeaway_checked == false && delivery_checked == false) {
                    Swal.fire({
                        title: "Warning!",
                        text: "One option must be enables",
                        icon: "warning",
                        button: "OK",
                    });
                    $("#" + id).trigger('click');
                }
            }

            function toggle_smsFields(obj) {
                var id = $(obj).find(':selected').attr('data-id');
                $('.sms_fields').css('display', 'none');
                $('#' + id).css('display', 'flex');
                console.log(id);
            }

            var tagList = "";
    tagList = tagList.split(',');
    console.log(tagList);

    function makeTag(tagList = '') {
        $('.myTag1').tagsInput({
            'autocomplete': {
                source: tagList
            }
        });
    }
    
            $(function() {
        var $d4 = $("#slider-range");
        $d4.ionRangeSlider({
            type: "double",
            grid: !0,
            min: 0,
            max: 1,
        });
        $d4.on("change", function() {
            var $inp = $(this);
            $("#min").val($inp.data("from"));
            $("#max").val($inp.data("to"));
        });
    });


    $('.editESAddonBtn').on('click', function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "<?php echo url('client/estimationsAddon'); ?>" + '/' + did + '/edit',
            data: {id: did},
            dataType: 'json',
            success: function(data) {
                console.log(data);
                $('#editESAddonmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#editAddonForm #editAddonBox').html(data.html);
                
                $('#editESAddonmodal .modal-title').html('Edit AddOn Set');
                $('#editESAddonmodal .editAddonSubmit').html('Update');
                document.getElementById('editAddonForm').action = data.submitUrl;
                setTimeout(function() {
                    var max = $('#edit_addon-datatable >tbody >tr.input_tr').length;
                    var $d4 = $("#editAddonForm #slider-range1");
                    $d4.ionRangeSlider({
                        type: "double",
                        grid: false,
                        min: 0,
                        max: max,
                        from: data.min_select,
                        to: data.max_select
                    });
                    $d4.on("change", function() {
                        var $inp = $(this);
                        $("#editAddonForm #max_select").val($inp.data("to"));
                        $("#editAddonForm #min_select").val($inp.data("from"));
                    });
                }, 1000);
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            },
            error: function(data) {
                console.log('error');
            }
        });
    });



  


        </script>
    @endsection
