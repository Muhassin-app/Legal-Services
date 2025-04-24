@extends('layouts.store', [
'title' => (!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->name : $category->slug,
'meta_title'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_title:'',
'meta_keyword'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_keyword:'',
'meta_description'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_description:'',
])

@section('css')
<style type="text/css">
.main-menu .brand-logo {display: inline-block;padding-top: 20px;padding-bottom: 20px;}.slick-track{margin-left: 0px;}.product-box .product-detail h4, .product-box .product-info h4{font-size: 16px;}
</style>
<style type="text/css">
    .main-menu .brand-logo {display: inline-block;padding-top: 20px;padding-bottom: 20px;}.slick-track{margin-left: 0px;}.product-box .product-detail h4, .product-box .product-info h4{font-size: 16px;}
    .main-fillter .side_fillter {
        background: transparent !important;
        border-top: 1px solid #D9D9D9;
        /* margin-top: 18px !important; */
        overflow-y: auto !important;
        border-right: 1px solid #D9D9D9;
        height: 600px;
        overflow-x: hidden !important;
    }
    .p2p-sidebar {
        padding: 20px;
    }
    
    .p2p-sidebar label.control-label {
        margin: 10px 0 10px;
        color: #000;
        font-size: 18px;
        font-weight: 500;
    }
    .p2p-sidebar .checkbox.checkbox-success {
        align-items: center;
        justify-content: flex-start;
        display: inline-flex;
        margin-bottom: 10px;
        width: 100%;
    }
    .p2p-sidebar .checkbox.checkbox-success label {
        margin-bottom: 0;
        padding-left: 10px;
    }
    .p2p-sidebar .form-check-inline label {
        margin: 0;
        padding-left: 10px;
    }
    .p2p-sidebar .form-check-inline {
        display: inline-flex;
        align-items: center;
        padding-left: 0;
        margin-right: 0;
        width: 49%;
    }
    .p2p-sidebar .form-check-inline.d-block {
        width: 100%;
    }
    .p2p-sidebar .custom-search {
        width: 100%;
        margin-bottom: 14px;
        background-color: white;
        border: 1px solid #aaa;
        border-radius: 4px;
        cursor: text;
        height: 40px;
    }
    .p2p-sidebar .select2-container{width:100% !important;}
    .select2-container--default .select2-results>.select2-results__options li {
        display: block !important;
        color: #222;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered li {
        list-style: none;
        color: #000;
    }
    .custom_filtter {
        border-bottom: 1px solid #D9D9D9;
    }
    .collection-product-wrapper .product-top-filter{border:none !important;}
    .custom_filtter ul {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }
    .custom_filtter ul li span {
        font-weight: 500;
        font-size: 20px;
        line-height: 24px;
        color: #0A0A0A;
        position: relative;
    }
    /* .custom_filtter ul li span:after {
        content: '';
        background: #D9D9D9;
        height: 1px;
        position: absolute;
        left: -49px;
        width: 37px;
        bottom: -13px;
    } */
    .custom_filtter ul li {
        flex-grow: 1;
        min-width: 0;
        max-width: 100%;
        line-height: 50px;
    }
    .custom_filtter ul li a.active {
        color: #E9248D;
    }
     .custom_filtter ul li a.active:after {
        content: '';
        background: #E9248D;
        width: 50px;
        height: 2px;
        position: absolute;
        left: 0;
        bottom: -13px;
    }
    .custom_filtter ul li a {
        font-style: normal;
        font-weight: 400;
        font-size: 18px;
        line-height: 22px;
        color: #6F6F6F;
        position: relative;
    }
    .main-fillter {
        margin-top: 8px;
    }
    .product-image img{
        height: 200px !important;
        object-fit: cover;
    }
    .irs-to {
        left: 85% !important;
    }
</style>
<link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/price-range.css')}}">
@endsection
@section('content')
@if(!empty($category))
@include('frontend.included_files.categories_breadcrumb')
@endif
@php
$additionalPreference = getAdditionalPreference(['is_token_currency_enable']);
@endphp
<section class="section-b-space ratio_asos">
    <div class="collection-wrapper">
        <div class="container" id="divFirst">
            <div class="row">
                <div class="col-12">
                    <div class="top-banner-wrapper text-center">
                       
                       @include('frontend.vendor-category-topbar-banner')
                       
                        <div class="top-banner-content small-section">
                            <h4>{{ $category->translation_name }}</h4>
                            {{-- @if(!empty($category->childs) && count($category->childs) > 0)
                                <div class="row">
                                    <div class="col-12">

                                        <div class="slide-6 no-arrow">
                                            @foreach($category->childs->toArray() as $cate)
                                            <div class="category-block">
                                                <a href="{{route('categoryDetail', $cate['slug'])}}">
                                                    <div class="category-image"><img alt="" class="blur-up lazyload" data-src="{{$cate['icon']['image_fit'] . '300/300' . $cate['icon']['image_path']}}" ></div>
                                                </a>
                                                <div class="category-details">
                                                    <a href="{{route('categoryDetail', $cate['slug'])}}">
                                                        <h5>{{$cate['translation_name']}}</h5>
                                                    </a>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4 homepageSix">
                <div class="collection-filter col-lg-3 main-fillter">
                        <!-- <ul class="breadcrumb p-0 mb-2 mt-3">
                            <li class="breadcrumb-item align-items-center"><a href="javascript:void(0)">Home <i class="fa fa-angle-right" aria-hidden="true"></i> <span>Pharmacy <i class="fa fa-angle-right" aria-hidden="true"></i>
                                </span><span class="active">Healthcare Device</span></a>
                            </li>
                        </ul> -->


                    <!--- Left Sidebar filters -->
                        @include('frontend.category-left-sidebar')
                    <!---End Left Sidebar filters -->

                </div>
                
                <div class="collection-content col-lg-9 outter-fillter-data fillter_product">
                    <div class="page-main-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="collection-product-wrapper">
                                    <div class="product-top-filter">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="filter-main-btn">
                                                    <span class="filter-btn btn btn-theme">
                                                       {{__('Filters')}} >
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="product-filter-content">
                                                    <!-- <div class="collection-view">
                                                        <ul>
                                                            <li><i class="fa fa-th grid-layout-view"></i></li>
                                                            <li><i class="fa fa-list-ul list-layout-view"></i></li>
                                                        </ul>
                                                    </div> -->
                                                    {{-- <div class="collection-grid-view">
                                                        <ul>
                                                            <li><img class="blur-up lazyload" data-src="{{asset('front-assets/images/icon/2.png')}}" alt="" class="product-2-layout-view"></li>
                                                            <li><img class="blur-up lazyload" data-src="{{asset('front-assets/images/icon/3.png')}}" alt="" class="product-3-layout-view"></li>
                                                            <li><img class="blur-up lazyload" data-src="{{asset('front-assets/images/icon/4.png')}}" alt="" class="product-4-layout-view"></li>
                                                            <li><img class="blur-up lazyload" data-src="{{asset('front-assets/images/icon/6.png')}}" alt="" class="product-6-layout-view"></li>
                                                        </ul>
                                                    </div> --}}
                                                    {{-- <div class="product-page-per-view">
                                                        <?php $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 8; ?>
                                                        <select class="customerPaginate">
                                                            <option value="8" @if($pagiNate == 8) selected @endif>Show 8
                                                            </option>
                                                            <option value="12" @if($pagiNate == 12) selected @endif>Show 12
                                                            </option>
                                                            <option value="24" @if($pagiNate == 24) selected @endif>Show 24
                                                            </option>
                                                            <option value="48" @if($pagiNate == 48) selected @endif>Show 48
                                                            </option>
                                                        </select>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="displayProducts main_category" id="category_products_filter">
                                 
                                    @include('frontend.ajax.product-card')
                                        <div class="pagination pagination-rounded justify-content-end mb-0 page-m-20">
                                            @if(!empty($listData))
                                                {{ $listData->links() }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="vendor_id" value="{{ isset($vendor_id) ? $vendor_id : ''}}">
</section>
@endsection
@section('script')
<script src="{{asset('front-assets/js/rangeSlider.min.js')}}"></script>
<script src="{{asset('front-assets/js/my-sliders.js')}}"></script>
<script>
    @if(!empty($category->image) && $category->image['is_original'])
    $(document).ready(function() {
        $("body").addClass("homeHeader");
    });
    @endif
</script>
<script>
    $(document).on("change",".attr_radio", function() {
        var parentClass = $(this).parent().prop('className');
        var attr_radio_class = $(this).data('class');
        $("."+parentClass+" .attr_radio").prop('checked', false);
        $(this).prop('checked', true);
    });

    $('.js-range-slider').ionRangeSlider({
        type: 'double',
        grid: false,
        min: 0,
        max: {{ $maxPrice??0 }},
        from: 0,
        to: {{ $maxPrice??0 }},
        prefix: " "
    });
    var ajaxCall = 'ToCancelPrevReq';
    
    $('.productFilter').click(function(){
        filterProducts();
    });

    $(document).on('click', '#category_products_filter .pagination a.page-link', function(e){
        e.preventDefault();
        var link = $(this).attr('href');
        var urlParams = new URL(link).searchParams;
        var page = urlParams.get('page');
        filterProducts(page);
    });

    $(document).on('change','.sortingFilter',function(){
        filterProducts();
    });
   
    var debounceTimeout;

    $('.js-range-slider ').on('input', function() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(function() {
        filterProducts();
    }, 300);
    });

    $('.attr_radio, .dynamic_checkbox, .dropdown_select, .text_field').change(function() {
        filterProducts();
    });

    function filterProducts(page='', limit=''){
        var brands = [];
        var variants = [];
        var options = [];
        var vendor_id =$("#vendor_id").val();
        var dropdown_options = {};
        var dynamic_options = {};
        var radio_option = {};
        var checkbox_option_arr = {};
        var text_field_search = {};

        $('.dropdown_select').each(function(i, obj) {
            dropdown_options[$(this).data('key')] = $(this).val();
        });
        $('.text_field').each(function(i, obj) {
            text_field_search[$(this).data('key')] = $(this).val();
        });

        $('.attr_radio').each(function(i, obj){
            if(this.checked) {
                radio_option[$(this).data('key')] = $(this).val();
            }
        });
        
        $('.dynamic_checkbox').each(function(i, obj){
            
            var dataType = typeof checkbox_option_arr[$(this).data('key')];
            if(dataType == 'undefined') {
                checkbox_option_arr[$(this).data('key')] = [];
            }
            if(this.checked) {
                checkbox_option_arr[$(this).data('key')].push($(this).val());
            }
        });
        
        dynamic_options['dropdown_options'] = dropdown_options;
        dynamic_options['radio_option'] = radio_option;
        dynamic_options['checkbox_option_arr'] = checkbox_option_arr;
        dynamic_options['text_field_search'] = text_field_search;

        $('.productFilter').each(function () {
            var that = this;
            if(this.checked == true){
                var forCheck = $(that).attr('used');
                if(forCheck == 'brands'){
                    brands.push($(that).attr('fid'));
                }else{
                    variants.push($(that).attr('fid'));
                    options.push($(that).attr('optid'));
                }
            }
        });
        var range = $('.rangeSliderPrice').val();
        var order_type = $('.sortingFilter').val();
        var latitude = $('#latitude').val();
        var longitude = $('#longitude').val();
        var ajaxData = {
            "_token": "{{ csrf_token() }}",
            "brands": brands,
            "vendor_id": vendor_id,
            "variants": variants,
            "options": options,
            "range": range,
            "order_type" : order_type,
            "dynamic_options" : dynamic_options,
            "filter_type" : 1,
            "latitude" : latitude,
            "longitude" : longitude
        };

        if(limit != ''){
            ajaxData.limit = limit;
        }
        if(page != ''){
            ajaxData.page = page;
        }

        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('productFilters', $category->id) }}",
            data: ajaxData,
            beforeSend : function() {
                if(ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
                $('.spinner-overlay').show();
            },
            success: function(response) {
                $('.displayProducts').html(response.html);
            },
            complete: function() {
                $('.spinner-overlay').hide();
            },
            error: function (data) {
                //location.reload();
            },
        });
    }
    $(".page-link").on("click" ,function(){
        document.getElementById("divFirst").scrollIntoView();

});
</script>
@endsection
