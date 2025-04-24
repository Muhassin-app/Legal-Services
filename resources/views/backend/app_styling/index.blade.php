@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Styling - App Styling'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css">
<link href="{{asset('assets/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="col-12">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box ">
                <h4 class="page-title">{{ __("App Styling") }}</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="style-cols">
        <div class="row">
            <div class="col-lg-12 col-xl-6">
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <div class="card mb-0 h-100">
                            <div class="card-body">
                                <h4 class="header-title">{{ __("Font Styles") }}</h4>
                                <div class="mb-2">
                                    <label class="form-label">{{ __("Selecting regular font") }}</label>
                                    <select class="form-control al_box_height" name="fonts" onchange="submitRegularFontForm()" id="save_regular_fonts">
                                        @foreach($regular_font_options as $regular_font)
                                        <option value="{{$regular_font->id}}" {{$regular_font->is_selected == 1 ? 'selected' : ''}}>{{$regular_font->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __("Selecting medium font") }}</label>
                                    <select class="form-control al_box_height" name="fonts" onchange="submitMediumFontForm()" id="save_medium_fonts">
                                        @foreach($medium_font_options as $medium_font)
                                        <option value="{{$medium_font->id}}" {{$medium_font->is_selected == 1 ? 'selected' : ''}}>{{$medium_font->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __("Selecting bold font") }}</label>
                                    <select class="form-control al_box_height" name="fonts" onchange="submitBoldFontForm()" id="save_bold_fonts">
                                        @foreach($bold_font_options as $bold_font)
                                        <option value="{{$bold_font->id}}" {{$bold_font->is_selected == 1 ? 'selected' : ''}}>{{$bold_font->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h4 class="header-title mb-0">{{ __("Signup Tagline") }}</h4>
                                    </div>
                                    <input type="text" class="form-control al_box_height" data-id="{{ $signup_tag_line_text->id??'' }}" id="signup_tagline" name="signup_tagline" value="{{ $signup_tag_line_text->name??'' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card mb-0 h-100">
                            <div class="card-body">
                                <h4 class="header-title">{{ __("Color Picker") }}</h4>
                                <div class="form-group mb-3">
                                    <label for="primary_color">{{ __("Primary Color") }}</label>
                                    <input type="text" id="primary_color_option" onchange="submitPrimaryColorForm()" name="primary_color" class="form-control al_box_height" value="{{ old('primary_color', $primary_color_options->name ?? 'cccccc')}}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="secondary_color">{{ __("Secondary Color") }}</label>
                                    <input type="text" id="secondary_color_option" onchange="submitSecondaryColorForm()" name="secondary_color" class="form-control al_box_height" value="{{ old('secondary_color', $secondary_color_options->name ?? 'cccccc')}}">
                                </div>
                                <div class="form-group mb-0">
                                    <label for="tertiary_color">{{ __("Tertiary Color") }}</label>
                                    <input type="text" id="tertiary_color_option" onchange="submitTertiaryColorForm()" name="tertiary_color" class="form-control al_box_height" value="{{ old('tertiary_color', $tertiary_color_options->name ?? 'cccccc')}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card mb-0 h-100 last-child">
                            <div class="card-body">
                                <h4 class="header-title mb-2">{{ __("Tab Bar Style") }}</h4>
                                <div class="row">
                                    @foreach($tab_style_options as $tab_style)
                                    <div class="col-12">
                                        <div class="card al_card mb-3 shadow-none bg-tranparent">
                                            <div class="card-body px-2 py-0">
                                                <div class="row">
                                                    <div class="col-sm-12 custom-control custom-radio radio_new p-0">
                                                        <input type="radio" {{$tab_style->is_selected == 1 ? 'checked' : ''}} onchange="submitTabBarForm(this.id)" value="{{$tab_style->id}}" id="{{$tab_style->id}}" name="tab_bars" class="custom-control-input tab_bar_options" }}>
                                                        <label class="custom-control-label w-100" for="{{$tab_style->id}}">
                                                            <img class="card-img-top img-fluid" src="{{url('images/'.$tab_style->image)}}" alt="Card image cap">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body" id="homepage_tutorial_dropzone">
                        <h4 class="header-title mb-0">{{ __("Tutorial images") }}</h4>
                        <div class="row tutorial_main_div">
                            <div class="col-sm-6 col-md-4 col-lg-3 tutorial_inner_div mt-3">
                                <form class="h-100" action="{{ route('styling.addTutorials') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="card card-box h-100 px-2">
                                        <!-- <div class="d-flex align-items-center justify-content-between mb-3">
                                            <h4 class="header-title mb-0">{{ __('Upload Images') }}</h4>
                                        </div> -->
                                        <input type="file" accept="image/*" data-plugins="dropify" name="file_name" class="dropify" data-default-file="" />
                                        {{-- <label class="logo-size text-right w-100">{{ __("Logo Size") }} 170x96</label> --}}
                                        <button type="submit" class="btn btn-info waves-effect waves-light mt-2">{{ __('Submit') }}</button>
                                    </div>
                                </form>
                            </div>
                            @if(!empty($dynamicTutorials) && count($dynamicTutorials)>0)
                                @foreach($dynamicTutorials as $dynamicTutorial)
                                    <div class="col-sm-6 col-md-4 col-lg-3 tutorial_inner_div mt-3" data-id="{{$dynamicTutorial->id}}" data-sort="{{$dynamicTutorial->sort}}">
                                        <div class="card mb-0">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-12 custom-control custom-radio radio_new p-0">
                                                        <label class="custom-control-label" for="">
                                                            <img class="card-img-top img-fluid" src="{{$dynamicTutorial->file_name['proxy_url'] . '215/400' . $dynamicTutorial->file_name['image_path']}}" alt="Image">
                                                        </label>
                                                        <form action="{{ route('styling.deleteTutorials',$dynamicTutorial->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="remove-button">
                                                                <button type="submit" class="btn btn-danger waves-effect waves-light mt-1" onclick="return confirm('Are you sure? You want to delete this tutorial.')" ><b>Remove</b></button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{ __("Home Page Style") }}</h4>
                        <div class="row">
                            @foreach($homepage_style_options as $homepage_style)
                                    <div class="col-sm-6 col-md-4 col-lg-3 mb-2">
                                        <div class="card mb-0">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-12 custom-control custom-radio radio_new p-0">
                                                        <input type="radio" {{$homepage_style->is_selected == 1 ? 'checked' : ''}} value="{{$homepage_style->id}}" onchange="submitHomePageForm(this.id)" id="{{$homepage_style->id}}" name="home_styles" class="custom-control-input " }}>
                                                        <label class="custom-control-label" for="{{$homepage_style->id}}">
                                                            <img class="card-img-top img-fluid" src="{{url('images/'.$homepage_style->image)}}" alt="Card image cap">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="alTemplateName mt-3 w-100">{{$homepage_style->name}}</span>
                                    </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- cab booking template -->
<form id="favicon-form-pickup" method="post" enctype="multipart/form-data">
<div class="row" >
    <div class="col-md-9" ondrop="drop(event)" ondragover="allowDrop(event)">
        <div class="card-box home-options-list h-100">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h4 class="page-title mt-0">{{ __('Home Page')}}</h4>
                    <p class="sub-header">
                        {{ __("Drag & drop to edit different sections.") }}
                    </p>
                </div>
                {{-- <div class="col-sm-4 text-right">
                    <button class="btn btn-info waves-effect waves-light text-sm-right" id="add_pickup_delivery_section_button"   data-toggle="modal" data-target="#add_pickup_delivery_section">Add</button>
                </div> --}}
                <div class="col-sm-4 text-right">
                    <button class="btn btn-info waves-effect waves-light text-sm-right" id="save_home_page_pickup">{{ __("Save") }}</button>
                </div>
            </div>

            <div class="custom-dd-empty dd" id="pickup_datatable">
                <ol class="dd-list p-0" id="pickup_ol" >
                    @foreach($cab_booking_layouts as $key => $home_page_label)
                    <li id="al_web_styling" class="item_dev_row row  dd-item align-items-center dd3-item on_click{{$home_page_label->slug}}" data-id="1" data-row-id="{{$home_page_label->id}}">
                            <a herf="#" class="dd-handle dd3-handle d-block mr-auto">
                                {{$home_page_label->title}}
                            </a>
                            <div class="language-input style-4">
                                <div class="row no-gutters flex-nowrap align-items-center my-2">
                                    @foreach($langs as $lang)
                                    @php
                                    $exist = 0;
                                    $value = '';
                                    @endphp
                                    <div class="col pl-1">
                                        <input class="form-control" type="hidden" value="{{$home_page_label->id}}" name="home_labels[]">
                                        <input class="form-control" type="hidden" value="{{$lang->langId}}" name="languages[]">
                                        @foreach($home_page_label->translations as $translation)
                                        @if($translation->language_id == $lang->langId)
                                        @php
                                        $exist = 1;
                                        $value = $translation->title;
                                        @endphp
                                        @endif
                                        @endforeach
                                        <input class="form-control" value="{{$exist == 1 ? $value : '' }}" type="text" name="names[]" placeholder="{{ $lang->langName }}">
                                    </div>

                                    @endforeach

                                </div>
                            </div>
                                @if($home_page_label->slug == 'pickup_delivery')
                                    <div class="col pl-1">
                                        <select class="form-control select2-multiple" required id="categories" name="categories[{{$key}}][check]" data-toggle="select2"  data-placeholder="Choose ...">

                                        {{-- <select class="form-control w-100">  --}}
                                            @foreach ($all_pickup_category as $category)
                                            <option value="{{$category->id}}"
                                                @if(isset($home_page_label->pickupCategories->first()->categoryDetail) && !empty($home_page_label->pickupCategories->first()) && $home_page_label->pickupCategories->first()->categoryDetail->id == $category->id)
                                                selected="selected"
                                                @endif>{{$category->translation_one->name??''}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif


                                @if($home_page_label->slug == 'pickup_delivery')
                                <a class="action-icon openBannerModal" userId="{{$home_page_label->id}}" data-row-id="{{$home_page_label->id}}" href="javascript:void(0);">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                                @endif


                                @if($home_page_label->slug == 'selected_products')
                                <a class="action-icon openProductsModal" userId="{{$home_page_label->id}}" data-row-id="{{$home_page_label->id}}" href="javascript:void(0);">
                                    <div class="col pl-1">
                                    <select class="form-control select2-multiple" id='product' name="selected_products[]" data-toggle="select2" multiple="multiple" data-placeholder="Choose ..." required>
                                        <option value="">{{ __("Select Product") }}</option>
                                        @foreach($select_products as $products)
                                        <option value="{{$products->id}}" @if(!empty($selected_ids) && in_array($products->id, $selected_ids)) selected @endif>{{$products->title}}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </a>
                                @endif

                                @if($home_page_label->slug == 'single_category_products')
                                <div class="language-input style-4">
                                <div class="row no-gutters flex-nowrap align-items-center my-2">
                                <div class="col pl-1">
                                    <select class="form-control" id='product_category' name="product_category"  data-placeholder="Choose ..." required>
                                        <option value="">{{ __("Select Product Category") }}</option>
                                        @foreach($single_category_products['categories'] as $category)
                                        <option value="{{$category->id}}" @if(@$selected_single_category_products->category_id == $category->id) selected="selected" @endif>
                                            @if(!is_null($category->parent) && $category->parent_id > 1)
                                            {{@$category->parent->translation_one->name}}-> @endif
                                            {{@$category->translation_one->name}}
                                            @if(!is_null($category->vendor)) ({{@$category->vendor->name}}) @endif
                                        </option>
                                        @endforeach

                                </div>
                                </div>
                                </div>
                                @endif
                                @if($home_page_label->slug == 'dynamic_page')
                                <a class="action-icon edit_dynamic_page" data-row-id="{{$home_page_label->id}}" href="javascript:void(0);">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                                @endif
                                @if($home_page_label->slug == 'cities')
                                <a class="action-icon edit_cities_page" data-row-id="{{$home_page_label->id}}" href="javascript:void(0);">
                                    <i class="mdi mdi-pencil"></i>
                                </a>

                                @endif
                                @if($home_page_label->slug == 'dynamic_page')
                                <input type="checkbox" name="for_no_product_found_html[{{$key}}]" {{$home_page_label->for_no_product_found_html == 1 ? 'checked' : ''}} >{{__('For No Records')}}
                                @else
                                <input type="hidden" name="for_no_product_found_html[{{$key}}]">
                                @endif
                                <div class="mb-0 ml-1">
                                    <input class="form-control" type="hidden" value="{{$home_page_label->id}}" name="pickup_labels[]">

                                    <input type="checkbox" {{$home_page_label->is_active == 1 ? 'checked' : ''}} id="{{$home_page_label->slug}}" data-plugin="switchery" name="is_active[{{$key}}][check]" class="chk_box2" data-color="#43bee1">
                                </div>

                                <a class="action-icon deletePickupSectionx" href="{{route('pickup.delete.section', $home_page_label->id)}}" onclick="return confirm('Are you sure you want to delete this section?');"  dataid="{{$home_page_label->id}}" href="javascript:void(0);">
                                    <i class="mdi mdi-delete"></i>
                                </a>

                    </li>

                    @endforeach
                </ol>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-box home-options-list">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h4 class="page-title mt-0">{{ __('Home Page Sections') }}</h4>
                    <p class="sub-header">
                        {{ __('Drag & drop to home page sections') }}
                    </p>
                </div>

            </div>

            <div class="custom-dd-empty dd" id="homepage_datatablex">
                <ol class="dd-list p-0" id="homepage_ol">
                    @foreach($home_page_labels as $home_page_label)
                    <li class="dd-item dd3-item d-flex align-items-center" id="drag{{$home_page_label->id}}" data-id="1" data-row-id="{{$home_page_label->id}}" draggable="true" ondragstart="drag(event)">
                        <a herf="#" class="dd-handle dd3-handle d-block mr-auto">
                            @if($home_page_label->slug == "vendors")

                            @php
                                $vendorLable = getNomenclatureName('Vendors', true);
                                $vendorLable = ($vendorLable === 'Vendors') ? __('Vendors') : $vendorLable;
                            @endphp

                            {{ $vendorLable }}
                            @else
                            {{$home_page_label->title}}
                            @endif
                        </a>
                    </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>

</form>
</div>
@endsection

@section('script')
<script src="{{asset('assets/js/jscolor.js')}}"></script>
<script src="{{ asset('assets/ck_editor/ckeditor.js')}}"></script>
<script src="{{ asset('assets/ck_editor/samples/js/sample.js')}}"></script>
<script type="text/javascript">

$("#save_home_page_pickup").click(function(event) {
        event.preventDefault();
        submitDataNewPickup();
    });

    function allowDrop(ev) {
        console.log('allowDrop');
       ev.preventDefault();
    }

    function drag(ev) {
        console.log('drag');
      var attod =   $(ev.target).attr('data-row-id');
      ev.dataTransfer.setData("row_id", attod);
    }

    function drop(ev) {
      console.log('drop');
      ev.preventDefault();
      var row_id = ev.dataTransfer.getData("row_id");

      submitDataWithNewSection(row_id);
      console.log(row_id);
      //ev.target.appendChild(document.getElementById(row-id));
    }

    function submitDataNewPickup() {
        var form = document.getElementById('favicon-form-pickup');
        for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
        }
        var formData = new FormData(form);
        var data_uri = "{{route('styling.updateAppStylesNew')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });


        $.ajax({
            type: "post",
            url: data_uri,
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                Accept: "application/json"
            },
            success: function(response) {
                if (response.status == 'success') {
                    console.log(response.message);
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                    var r = document.querySelector(':root');
                    r.style.setProperty('--theme-deafult', 'lightblue');
                }
            }
        });
    }

    $("#homepage_datatable ol").sortable({
        placeholder: "ui-state-highlight",
        update: function(event, ui) {
            var post_order_ids = new Array();
            $('#homepage_ol li').each(function() {
                post_order_ids.push($(this).data("row-id"));
            });
            console.log(post_order_ids);
            saveOrder(post_order_ids);
        }
    });

    $("#pickup_datatable ol").sortable({
         placeholder: "ui-state-highlight",
        update: function(event, ui) {
            var post_order_ids = new Array();
            $('#pickup_ol .item_dev_row').each(function() {
                post_order_ids.push($(this).data("row-id"));
            });
            console.log(post_order_ids);
            saveOrderPickup(post_order_ids);

        }
    });

    function saveOrder(orderVal) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ url('client/homepagelabel/saveOrder') }}",
            data: {
                order: orderVal
            },
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            },
        });
    }

    function saveOrderPickup(orderVal) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ url('client/pickuplabel/saveOrder') }}",
            data: {
                order: orderVal
            },
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            },
        });
    }
    </script>
<script type="text/javascript">
    var options = {
        zIndex: 9999
    }
    $(document).ready(function() {
        var color1 = new jscolor('#primary_color_option', options);
        var color3 = new jscolor('#tertiary_color_option', options);
        var color2 = new jscolor('#secondary_color_option', options);
    });



    function submitDataWithNewSection(row_id) {
        console.log('ajax');
       var data_uri = "{{route('app.pickup.append.section')}}";
       $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                row_id: row_id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                    var r = document.querySelector(':root');
                    r.style.setProperty('--theme-deafult', 'lightblue');
                    location.reload();
                }
            }
        });
    }

    function submitHomePageForm(id) {
        var data_uri = "{{route('styling.updateHomePage')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        let home_styles = id;
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                home_styles: home_styles
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    }

    function submitTabBarForm(id) {
        var data_uri = "{{route('styling.updateTabBar')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        let tab_bars = id;
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                tab_bars: tab_bars
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    }

    function submitRegularFontForm() {
        var data_uri = "{{route('styling.updateFont')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        let fonts = $('#save_regular_fonts').val();
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                fonts: fonts
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    }

    function submitMediumFontForm() {
        var data_uri = "{{route('styling.updateFont')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        let fonts = $('#save_medium_fonts').val();
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                fonts: fonts
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    }

    function submitBoldFontForm() {
        var data_uri = "{{route('styling.updateFont')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        let fonts = $('#save_bold_fonts').val();
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                fonts: fonts
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    }

    function submitPrimaryColorForm() {
        var data_uri = "{{route('styling.updateColor')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        let primary_color = $('#primary_color_option').val();
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                primary_color: primary_color,
                color_type: 'Primary'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    }

    function submitSecondaryColorForm() {
        var data_uri = "{{route('styling.updateColor')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        let secondary_color = $('#secondary_color_option').val();
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                secondary_color: secondary_color,
                color_type: 'Secondary'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    }

    function submitTertiaryColorForm() {
        var data_uri = "{{route('styling.updateColor')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        let tertiary_color = $('#tertiary_color_option').val();
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: {
                tertiary_color: tertiary_color,
                color_type: 'Tertiary'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    }

    $("#signup_tagline").on('blur',function() {
        var updated_text = $(this).val();
        var id = $(this).data('id');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            type: "post",
            url: "{{route('styling.updateSignupTagLine')}}",
            data: { updated_text : updated_text, id : id },
            dataType: 'json',
            headers: {
                Accept: "application/json"
            },
            success: function(response) {
                if (response.status == 'success') {
                    console.log(response.message);
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    });

    $("#homepage_tutorial_dropzone .tutorial_main_div").sortable({
        axis: 'x',
        placeholder: "ui-state-highlight",
        update: function(event, ui) {
            var post_order_ids = new Array();
            $('#homepage_tutorial_dropzone .tutorial_inner_div').each(function() {
                post_order_ids.push({"row_id" : $(this).data("id"), "sort" : $(this).data("sort")});
            });
            saveTutorialOrder(post_order_ids);
        }
    });

    function saveTutorialOrder(orderVal) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ url('client/app_styling/saveOrderTutorials') }}",
            data: {
                order: orderVal
            },
            success: function(response) {
                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            },
        });
    }
</script>

@endsection
