@extends('layouts.vertical', ['title' => 'Catalog'])
@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<link href="{{asset('assets/libs/nestable2/nestable2.min.css')}}" rel="stylesheet" type="text/css" />

<style>
    .select-category-lang label { font-size: 12px;}.select-category-lang select { height: 36px;}
    .modal-category-list {
        height: auto;
        overflow-y: hidden;
        flex-wrap: nowrap;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
    }

    .modal-category-list .col-sm{
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
    .category-modal-right::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	border-radius: 10px;
	background-color: #F5F5F5;
}

.category-modal-right::-webkit-scrollbar
{
	width: 12px;
	background-color: #F5F5F5;
}

.category-modal-right::-webkit-scrollbar-thumb
{
	border-radius: 10px;
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
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
.modal-category-list .select-category label:before{
    clip-path: polygon(0 0,0 50%,35% 0);
    font-size: 22px;
    padding: 2px 2px;
}
.modal-category-list .select-category label::after{
    display:none;
}
.modal-category-list .form-check-input:checked~label .category-img::after {
    opacity: 1;
}
.modal-category-list .select-category label .category-img{position: relative;}
.modal-category-list .select-category label .category-img:after{
    background: rgb(0 0 0 / 31%);
    background-image: none!important;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    opacity: 0;
    content: "";
    position: absolute;
}
.category-icon-image .dropify-wrapper{
    height:70px;
}
.modal-category-list .select-category .modal-category-btm-title h6{
    text-align:left;
    font-size: 14px;
    margin:0px;
    padding:2px 0px;
}
.modal-category-list .select-category .modal-category-btm-title p{
    font-size:12px;
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

    .alHeightAutoScrooll{height: 300px;overflow: auto;}
    .alSmHeight{height: 150px;overflow: auto;}
</style>
@endsection
@section('content')
<div class="container-fluid alCatalogPage">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Catalog') }}</h4>
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
        <div class="col-xl-4 col-lg-6 mb-4 order-list-view">
            <div class="card-box h-100">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="page-title">{{ __('Category') }}</h4>
                            <button class="btn btn-info waves-effect waves-light text-sm-right openCategoryModal" dataid="0" is_vendor="0"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                            </button>
                        </div>
                        <p class="sub-header ">
                            <!-- {{ __('Drag & drop Categories to make child parent relation') }} -->
                        </p>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12">
                        <form name="category_order" id="category_order" action="{{route('category.order')}}" method="post">
                            @csrf
                            <input type="hidden" name="orderDta" id="orderDta" value="" />
                        </form>
                        <div class="custom-dd-empty dd" id="nestable_list_3">
                            <div class="table-responsive outer-box">
                                <?php print_r($html); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- <div class="col-sm-12 text-right btn_bottom">
                        <button class="btn btn-info waves-effect waves-light text-sm-right saveList">{{ __('Save Order') }}</button>
                    </div> -->
                </div>
            </div>
        </div>

        @if($client_preference_detail->business_type != 'taxi')
        <!-- <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card-box h-100">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="page-title">{{ getNomenclatureName("Variant") }}</h4>
                            <button class="btn btn-info waves-effect waves-light text-sm-right addVariantbtn" dataid="0"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                            </button>
                        </div>
                        <p class="sub-header">
                            {{ __("Drag & drop Variant to change the position") }}
                        </p>
                    </div>
                </div>
                <div class="row variant-row">
                    <div class="col-md-12">
                        <form name="variant_order" id="variant_order" action="{{route('variant.order')}}" method="post">
                            @csrf
                            <input type="hidden" name="orderData" id="orderVariantData" value="" />
                        </form>
                        <div class="table-responsive outer-box">
                            <table class="table table-centered table-nowrap table-striped" id="varient-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Options') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($variants as $key => $variant)
                                        @if(!empty($variant->translation_one))
                                            <tr class="variantList" data-row-id="{{$variant->id}}">
                                                <td><span class="dragula-handle"></span></td>
                                                <td><a class="editVariantBtn" dataid="{{$variant->id}}" href="javascript:void(0);">{{$variant->title}}</a> <br> <b>{{isset($variant->varcategory->cate->primary->name) ? $variant->varcategory->cate->primary->name : ''}}</b></td>
                                                <td>
                                                    @foreach($variant->option as $key => $value)
                                                    <label style="margin-bottom: 3px;">
                                                        @if(isset($variant) && !empty($variant->type) && $variant->type == 2)
                                                        <span style="padding:8px; float: left; border: 1px dotted #ccc; background:{{$value->hexacode}};"> </span>
                                                        @endif
                                                        &nbsp;&nbsp; {{$value->title}}</label> <br />
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <a class="action-icon editVariantBtn" dataid="{{$variant->id}}" href="javascript:void(0);">
                                                        <i class="mdi mdi-square-edit-outline"></i>
                                                    </a>
                                                    <a class="action-icon deleteVariant" dataid="{{$variant->id}}" href="javascript:void(0);">
                                                        <i class="mdi mdi-delete"></i>
                                                    </a>
                                                    <form action="{{route('variant.destroy', $variant->id)}}" method="POST" style="display: none;" id="varDeleteForm{{$variant->id}}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-icon btn btn-primary-outline" dataid="{{$variant->id}}" onclick="return confirm('Are you sure? You want to delete the variant.')"> <i class="mdi mdi-delete"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-sm-12 text-right btn_bottom">
                        <button class="btn btn-info waves-effect waves-light text-sm-right saveVariantOrder">{{ __('Save Order') }}</button>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="col-xl-4 col-lg-6 mb-4">
            <!-- <div class="card-box ">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="page-title">{{ __('Brand') }}</h4>
                            <button class="btn btn-info waves-effect waves-light text-sm-right addBrandbtn" dataid="0">
                                <i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                            </button>
                        </div>
                        <p class="sub-header"></p>
                    </div>
                </div>
                <div class="row brand-row">
                    <div class="col-md-12">
                        <form name="brand_order" id="brand_order" action="{{route('brand.order')}}" method="post">
                            @csrf
                            <input type="hidden" name="orderData" id="orderBrandData" value="" />
                        </form>
                        <div class="table-responsive {{ (($client_preference_detail->is_vendor_tags == '1') && (count($brands) > 2) ) ?  ( (count($brands) > 3) ? 'alSmHeight' : 'alHeightAutoScrooll' ) : ( (count($brands) > 3) ?  'alHeightAutoScrooll' :'') }}  ">
                            <table class="table table-centered table-nowrap table-striped" id="brand-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Icon') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($brands as $key => $brand)
                                    @if(isset($brand->translation) && !empty($brand->translation))
                                    <tr class="brandList" data-row-id="{{$brand->id}}">
                                        <td><span class="dragula-handle"></span></td>
                                        <td><img class="rounded-circle" src="{{$brand->image['proxy_url'].'30/30'.$brand->image['image_path']}}"></td>
                                        <td><a class="editBrandBtn" dataid="{{$brand->id}}" href="javascript:void(0);">{{$brand->title}}</a> <br> <b>
                                                @foreach($brand->bc as $cat)
                                                {{-- @foreach($categories as $cate)
                                                @if($cat->category_id == $cate->id && $cat->brand_id==$brand->id)
                                                {{$cate->translation_one['name']??''}}
                                                @endif
                                                @endforeach --}}
                                                    @if(isset($cat->categoryDetail->translation) && !empty($cat->categoryDetail->translation))
                                                      {{ $cat->categoryDetail->translation->first()->name ?? ''}}
                                                    @endif

                                                @endforeach
                                            </b></td>
                                        <td>
                                            <a class="action-icon editBrandBtn" dataid="{{$brand->id}}" href="javascript:void(0);">
                                                <i class="mdi mdi-square-edit-outline"></i>
                                            </a>
                                            <a class="action-icon deleteBrand" dataid="{{$brand->id}}" href="javascript:void(0);"> <i class="mdi mdi-delete"></i> </a>
                                            <form action="{{route('brand.destroy', $brand->id)}}" method="POST" style="display: none;" id="brandDeleteForm{{$brand->id}}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-icon btn btn-primary-outline" dataid="{{$brand->id}}"> <i class="mdi mdi-delete"></i> </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-sm-12 text-right btn_bottom">
                        <button class="btn btn-info waves-effect waves-light text-sm-right saveBrandOrder">{{ __('Save Order') }}</button>
                    </div>
                </div>
            </div> -->
            {{--
            <div class="card-box">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="page-title">{{ __('Tags') }}</h4>
                            <button class="btn btn-info waves-effect waves-light text-sm-right addTagbtn" dataid="0" id="add_product_tag_modal_btn">
                            <i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                        </button>
                        </div>
                        <p class="sub-header"></p>
                    </div>
                </div>
                <div class="row brand-row">
                    <div class="col-md-12">
                        <form name="tag_order" id="tag_order" action="{{route('brand.order')}}" method="post">
                            @csrf
                            <input type="hidden" name="orderData" id="orderTagData" value="" />
                        </form>
                        <div class="table-responsive {{ (($client_preference_detail->is_vendor_tags == '1') && (count($tags) > 2) ) ?  ( (count($tags) > 3) ? 'alSmHeight' : 'alHeightAutoScrooll' ) : ( (count($tags) > 3) ?  'alHeightAutoScrooll' :'') }} ">
                            <table class="table table-centered table-nowrap table-striped" id="tag-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Icon') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tags as $key => $tag)
                                    <tr class="tagList" data-row-id="{{$tag->id}}">
                                        <td><span class="dragula-handle"></span></td>
                                        <td>@if(isset($tag->icon) && !empty($tag->icon)) <img style="height: 25px;width: auto;" src="{{ $tag->icon['proxy_url'].'100/100'.$tag->icon['image_path'] }}">@endif</td>
                                        <td>
                                            <a class="edit_product_tag_btn" data-tag_id="{{$tag->id}}" href="javascript:void(0)">
                                                {{$tag->primary ? $tag->primary->name : ''}}
                                            </a>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="inner-div" style="float: left;">
                                                    <a class="action-icon edit_product_tag_btn" data-tag_id="{{$tag->id}}" href="javascript:void(0)">
                                                        <i class="mdi mdi-square-edit-outline"></i>
                                                    </a>
                                                </div>
                                                <div class="inner-div">
                                                    <button type="button" class="btn btn-primary-outline action-icon delete_product_tag_btn" data-tag_id="{{$tag->id}}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                   <!--  <div class="col-sm-12 text-right btn_bottom">
                        <button class="btn btn-info waves-effect waves-light text-sm-right saveBrandOrder">{{ __('Save Order') }}</button>
                    </div> -->
                </div>
            </div> 
            --}}
            @if( p2p_module_status() || is_attribute_enabled()) 
                @include('layouts.shared.attribute')
            @endif
            @if($client_preference_detail->is_vendor_tags == '1')
            {{--  facilty section --}}

            <div class="card-box pb-2 ">
                <div class="d-flex align-items-center justify-content-between mb-2">
                <h4 class="header-title m-0">{{ __("Vendor Tags") }}</h4>
                <a class="btn btn-info d-block" id="add_facilties_modal_btn">
                    <i class="mdi mdi-plus-circle mr-1"></i>{{ __("Add") }}
                </a>
                </div>
                <div class="table-responsive {{ (count($facilties) > 3) ? 'alSmHeight' : 'alHeightAutoScrooll' }} ">
                <table class="table table-centered  nowrap table-striped  w-100" id="Facilties_datatable">
                    <thead>
                        <tr>
                            <th>{{ __("Icon") }}</th>
                            <th>{{ __("Name") }}</th>
                            <th>{{ __("Action") }}</th>
                        </tr>
                    </thead>
                    <tbody id="post_list">
                        @forelse($facilties as $facilty)
                        <tr>
                            <td>
                                <img class="rounded-circle" src="{{$facilty->image['proxy_url'].'30/30'.$facilty->image['image_path']}}">
                            </td>
                            <td  width="60%"> <a class="edit_facilty_btn" data-facilty_id="{{$facilty->id}}" href="javascript:void(0)">
                                {{$facilty->primary ? $facilty->primary->name : 'NA' }}
                            </a></td>
                            <td>
                            <div>
                                <div class="inner-div" style="float: left;">
                                    <a class="action-icon edit_facilty_btn" data-facilty_id="{{$facilty->id}}" href="javascript:void(0)">
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                </div>
                                <div class="inner-div">
                                    <button type="button" class="btn btn-primary-outline action-icon delete_facilty_btn" data-facilty_id="{{$facilty->id}}">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                            </td>
                        </tr>
                        @empty
                        <tr align="center">
                            <td colspan="4" style="padding: 20px 0">{{ __("Result not found.") }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>

            <!--End Add facilty Modal -->
            <div id="add_facilty_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  standard-modalLabel aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content">
                        <div class="modal-header border-bottom al">
                        <h4 class="modal-title" id="standard-modalLabel">{{ __("Add Vendor Tags") }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                        <form id="faciltyForm" method="POST" action="javascript:void(0)" enctype="multipart/form-data">
                            @csrf
                            <div id="save_social_media">
                                <input type="hidden" name="facilty_id" value="">
                                <div class="row">

                                    <div class="col-md-6">
                                            <label>{{ __('Upload Logo') }} </label>
                                            <input type="file" accept="image/*" data-plugins="dropify" name="facilty_image" class="dropify" data-default-file="" />
                                            <label class="logo-size text-right w-100">{{ __('Logo Size') }} 170x96</label>
                                    </div>
                                    <div class="col-md-12 selector-option-al ">
                                        <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                                            <tr class="trForClone">

                                                @foreach($languages as $langs)
                                                    <th>{{$langs->langName}}</th>
                                                @endforeach
                                                <th></th>
                                            </tr>
                                            <tbody id="table_body">
                                                    <tr>
                                                @foreach($languages as $lankey => $User_langs)
                                                    <td>
                                                        <input class="form-control" name="language_id[{{$lankey}}]" type="hidden" value="{{$User_langs->language_id}}">
                                                        <input class="form-control" name="name[{{$lankey}}]" type="text" id="facilty_name_{{$User_langs->language_id}}">
                                                    </td>
                                                @endforeach
                                                <td class="lasttd"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </form>
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-primary submitSaveFacilty">{{ __("Save") }}</button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            {{--  facilty section Ends--}}
            @endif

        </div>
        @endif

    </div>
<!-- <div class="col-xl-8">
    <div class="card-box">
        <div class="row" style="max-height: 600px; overflow-x: auto">
            <div class="col-sm-12 mb-2 d-flex justify-content-between align-items-center">
                <h4 class=""> {{ __("Addon Set") }}</h4>
                <button class="btn btn-info waves-effect waves-light text-sm-right openAddonModal" dataid="0">
                    <i class="mdi mdi-plus-circle mr-1"></i> {{ __("Add") }}
                </button>
            </div>
            <div class="col-sm-4 text-right">

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
                                    <th>{{ __("Select(Min - Max)") }}</th>
                                    <th>{{ __("Options") }}</th>
                                    <th>{{ __("Action") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($addon_sets as $set)
                                <tr>
                                    <td>{{$set->id}}</td>
                                    <td>{{$set->title}}</td>
                                    <td>{{$set->min_select}} - {{$set->max_select}}</td>
                                    <td>
                                        @foreach($set->option as $opt)
                                        <span>{{$opt->title}} - {{$clientCurrency->currency->symbol}}{{decimal_format($opt->price)}}</span><br />
                                        <span></span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a class="action-icon editAddonBtn" dataid="{{$set->id}}" href="javascript:void(0);">
                                            <h3> <i class="mdi mdi-square-edit-outline"></i> </h3>
                                        </a>

                                        <a class="action-icon deleteAddon" dataid="{{$set->id}}" href="javascript:void(0);"> <i class="mdi mdi-delete"></i></a>
                                        <form action="{{route('addon.destroy', $set->id)}}" method="POST" style="display: none;" id="addonDeleteForm{{$set->id}}">
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
</div> -->

<div id="editdAddonmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Create AddOn Set") }}</h4>
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
                <h4 class="modal-title">{{ __("Create AddOn Set") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="addAddonForm" method="post" enctype="multipart/form-data" action="{{route('addon.store')}}">
                @csrf
                <div class="modal-body" id="AddAddonBox">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row rowYK">
                                <div class="col-md-12">
                                    <h5>{{ __("Addon Title") }}</h5>
                                </div>
                                <div class="col-md-12" style="overflow-x: auto;">
                                    <table class="table table-borderless mb-0" id="banner-datatable">
                                        <tr>
                                            @foreach($languages as $langs)
                                            <th>{{$langs->language->name}}</th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($languages as $langs)
                                            <td>
                                                {!! Form::hidden('language_id[]', $langs->language_id) !!}
                                                <input type="text" name="title[]" value="" class="form-control" @if($langs->is_primary == 1) required @endif>
                                            </td>
                                            @endforeach
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="row rowYK mb-2">
                                <div class="col-md-12">
                                    <h5>{{ __("Addon Options") }}</h5>
                                </div>
                                <div class="col-md-12" style="overflow-x: auto;">
                                    <table class="table table-borderless mb-0 optionTableAdd" id="banner-datatable">
                                        <tr class="trForClone">
                                            <th>{{ __("Price") }}({{$clientCurrency->currency->symbol}})</th>

                                            @if (isset($getAdditionalPreference['is_price_by_role']))
                                                @if($getAdditionalPreference['is_price_by_role'] == '1')
                                                    @if (isset($roles))
                                                        @foreach ($roles as $_role)
                                                            <th>{!! Form::label('title', $_role['role'].' '. __('Price'), ['class' => 'control-label']) !!}</th>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            @endif

                                            @foreach($languages as $langs)
                                            <th>{{$langs->language->name}}</th>
                                            @endforeach
                                            <th></th>
                                        </tr>
                                        <tr class="input_tr">
                                            <td>{!! Form::text('price[]', null, ['class' => 'form-control', 'onkeypress' => 'return isNumberKey(event)', 'min' => '1', 'required' => 'required']) !!}</td>

                                            @if (isset($getAdditionalPreference['is_price_by_role']))
                                                @if($getAdditionalPreference['is_price_by_role'] == '1')
                                                    @if (isset($roles))
                                                        @foreach ($roles as $_role)
                                                        <td>
                                                            <input type="number" class="form-control" min="0" id="{{lcfirst($_role['role'])}}_price" onkeyup="isNumberKeyMax(event)" placeholder="0" name="role_price[{{lcfirst($_role['role'])}}]" value="0.00">
                                                        </td>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            @endif

                                            @foreach($languages as $k => $langs)
                                            <td>
                                                <input type="text" name="opt_value[{{$k}}][]" class="form-control" @if($langs->is_primary == 1) required @endif>
                                            </td>
                                            @endforeach
                                            <td class="lasttd"></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-info waves-effect waves-light addOptionRow-AddOn">{{ __("Add Option") }}</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" style="display:none;">
                                        {!! Form::label('title', __('Min Select'),['class' => 'control-label']) !!}
                                        {!! Form::text('min_select', 0, ['class' => 'form-control', 'id' => 'min', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="display:none;">
                                        {!! Form::label('title', __('Max Select'),['class' => 'control-label']) !!}
                                        {!! Form::text('max_select', 1, ['class' => 'form-control', 'id' => 'max', 'onkeypress' => 'return isNumberKey(event)']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="price-range-slider">
                                        {!! Form::label('title', __('Min & Max Range'),['class' => 'control-label']) !!}:<input type="text" id="slider_output" readonly="" style="border:0; color:#f6931f; font-weight:bold;">
                                        <div id="slider-range" class="range-bar"></div>
                                    </div>
                                    <div class="row slider-labels">
                                        <div class="col-xs-6 caption">
                                            <strong>{{ __("Min") }}:</strong> <span id="slider-range-value1"></span>
                                        </div>
                                        <div class="col-xs-6 text-right caption">
                                            <strong>{{ __("Max") }}:</strong> <span id="slider-range-value2"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <p>{{ __("If max select is greater than total option than max will be total option") }}</p>
                                </div>
                            </div>
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
            url: post_url,
            method: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
               if (response.status == 'Success') {
                  $('#add_or_edit_social_media_modal').modal('hide');
                  $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                     location.reload()
                  }, 2000);
               } else {
                  $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
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
    function editfaciltyForm(facilty_id){
        let language_id = $('#option_client_language').val();
         $('#faciltyForm input[name=facilty_id]').val(facilty_id);
         $.ajax({
            method: 'GET',
            data: {
                facilty_id: facilty_id,
                language_id:language_id
            },
            url: "{{ route('facilty.edit') }}",
            success: function(response) {
               if (response.status = 'Success') {
                   console.log(response.data);
                   var imagePath = response.data.image.image_fit+'90/90'+response.data.image.image_path;
                   imagePath = imagePath.replace('@webp', '');
                   console.log(imagePath);
                //   $(document).find("#add_vendor_registration_document_modal select[name=file_type]").val(response.data.file_type).change();

                  $("#add_facilty_modal input[name=facilty_id]").val(response.data.id);

                  $("#add_facilty_modal input[name='facilty_image']").attr('data-default-file',imagePath);
                  $('#add_facilty_modal #standard-modalLabel').html('Update facilty');
                  $('#add_facilty_modal').modal('show');
                  $('.dropify').dropify();

                  $.each(response.data.translations, function( index, value ) {
                    $('#add_facilty_modal #facilty_name_'+value.language_id).val(value.name);
                  });
               }
            },
            error: function() {}
        });
    }
     // delete kyc document
     $(document).on("click", ".delete_facilty_btn", function() {
         var facilty_id = $(this).data('facilty_id');
         Swal.fire({
            title: "{{__('Are you Sure?')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
         }).then((result) => {
            if (result.value) {
               $.ajax({
                  type: "POST",
                  dataType: 'json',
                  url: "{{ route('facilty.delete') }}",
                  data: {
                     _token: "{{ csrf_token() }}",
                     facilty_id: facilty_id
                  },
                  success: function(response) {
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
    	var	valid = 0;
    	var flag = true;
    	$(this).find('input[type=text]').each(function(){
            if($.trim($(this).val()) != "") valid=1;
        });
        if(valid==0){
          	$("#brand-title-error").css("color","red");
          	$("#brand-title-error").html("Please enter at least one title");
          	flag = false;
          }
        
          if(gR.length==0){
          	$("#cat-error").css("color","red");
          	$("#cat-error").html("Please select at least one category");
          		flag = false;
          } 
    	return flag;
   });
   	$(document).on('change', "#cateSelectBox",function() {
        var none = $("#cateSelectBox :checked");
        if (none.length > 0) {
            $("#cat-error").html('');
        }else{
        	$("#cat-error").css("color","red");
          	$("#cat-error").html("Please select at least one category");
        }
	});
    
    $(document).on('keyup', 'input[type=text]',function() {
        	var	valid = 0;
    
        $(this).each(function(){
            if($.trim($(this).val()) != "") valid=1;
        });
        
         if(valid>0){
          	$("#brand-title-error").html("");
          } else {
           	$("#brand-title-error").css("color","red");
          	$("#brand-title-error").html("Please enter at least one title");
          }
	});

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
    
    
</script>

@endsection