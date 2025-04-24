@extends('layouts.store', [
'title' => (!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->title : '',
'meta_title'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_title:'',
'meta_keyword'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_keyword:'',
'meta_description'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_description:'',
])
@php
$clientData = \App\Models\Client::select('socket_url')->first();


@endphp
@section('css')
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css"/>
    <link rel="stylesheet" href="{{ asset('front-assets/css/swiper.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('front-assets/css/easyzoom.css') }}" />
    <link rel="stylesheet" href="{{ asset('front-assets/css/main.css') }}" /> -->

    <link rel="stylesheet" href="{{asset('css/jquery.exzoom.css')}}">
    @if($product->is_recurring_booking == 1)
        <link href="{{asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css" />
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style type="text/css">
    /* .main-menu .brand-logo{display:inline-block;padding-top:20px;padding-bottom:20px}.btn-disabled{opacity:.5;pointer-events:none}.fab{font:normal normal normal 14px/1 FontAwesome;font-size:inherit}
    #number{display:block}#exzoom{display:none}.exzoom .exzoom_btn a.exzoom_next_btn{right:-12px} .exzoom .exzoom_nav .exzoom_nav_inner{-webkit-transition:all .5s;-moz-transition:all .5s;transition:all .5s}

    @media screen and (max-width:768px){
        .exzoom .exzoom_zoom_outer{display:none}
        }
    */
    .border-product.al_disc ol,.border-product.al_disc ul{padding-left:30px}.border-product.al_disc ol li,.border-product.al_disc ul li{display:list-item;padding-left:0;padding-top:8px;list-style-type:disc;font-size:14px}.border-product.al_disc ol li{list-style-type:decimal}.productVariants .firstChild{min-width:150px;text-align:left!important;border-radius:0!important;margin-right:10px;cursor:default;border:none!important}.product-right .color-variant li,.productVariants .otherChild{height:35px;width:35px;border-radius:50%;margin-right:10px;cursor:pointer;border:1px solid #f7f7f7;text-align:center}.productVariants .otherSize{height:auto!important;width:auto!important;border:none!important;border-radius:0}.product-right .size-box ul li.active{background-color:inherit}
    #more  {display:  none;}

    .img-zoom-lens {
      position: absolute;
      border: 1px solid #d4d4d4;
      width: 150px;
      height: 150px;
      background-color: #fff;
      opacity: .2;
      display: block;
    }

    .img-zoom-result {
        border: 1px solid #d4d4d4;
        width: 100%;
        height: 500px;
        position: absolute;
        top: 0;
        right: -100%;
        z-index: 10;
        display: none;
        background-color: #fff;
    }
    body[dir="rtl"] .img-zoom-result {
        border: 1px solid #d4d4d4;
        width: 100%;
        height: 500px;
        position: absolute;
        top: 0;
        right: auto;
        left: -100%;
        z-index: 10;
        display: none;
    }
    .review-date.mt-2 { margin-left: 10px; }
    .review-images img { min-height: 60px;border-radius: 8px;}
    .review-images a {display: inline-block;width: auto;padding: 0;}
    .review-images a:first-child img{margin-left: 10px;}
    .select2-results__option{width:100%;}
    .select2-container{width:100%!important;}
    .customer_review .item{padding:10px;border:1px solid #ccc;}
    .review_header{display:flex;padding-bottom:5px;}
    .review_header p{font-size:14px;margin-bottom:0px;padding-left:10px;}
    .customer_review .heading{border-bottom:1px solid #ccc;}
    .customer_review .heading h2{font-size:30px;line-height:1.3;font-weight:700;}
    .customer_review_item_row{display:flex;align-items:center;padding:10px 0;}
    .customer_review_item_row  img{width:50px;height: 50px;border-radius:50%;}
    .customer_review_item_row h4{margin-bottom:0;font-size:18px;font-weight:600;padding-left:15px;margin-top:0;}
    .review-images img {width: 100%;max-width: 100px;margin: 10px 10px 10px 0px;}
    .label-disabled {pointer-events: none;opacity: 0.5;}

    .flex-container {
    display: flex;
    justify-content: space-between; /* Distribute items evenly */
}

.item-price {
    text-align: center;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin: 10px;
    flex: 1; /* Distribute available space evenly */
    background-color: #f7f7f7;
}

#summary-table {
    width: 100%;
    border-collapse: collapse;
    margin: 30px 0px;
}
#summary-table  > th, td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
#summary-table > .text-right {
    text-align: right;
}

.related-products .product-card-box {
    border-radius: 10px !important;
    overflow: hidden;
    box-shadow: 0 4px 14px 0 rgb(0 0 0 / 8%);
    padding: 0 !important;
}
.dark .related-products .product-card-box {
    background: #000 !important;
}
.related-products .product-card-box .media-body {
    padding: 0 20px 20px;
}

.slick-slide .inner_spacing p {
    display: block;
}

.border-product {
    border: 1px solid #e0e0e0;
    padding: 15px;
    border-radius: 5px;
    margin-top: 20px;
    background-color: #f9f9f9;
}

.product-title {
    font-size: 1.5em;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    margin-bottom: 0;
}

.table thead {
    background-color: #343a40;
    color: #fff;
}

.table-hover tbody tr:hover {
    background-color: #f1f1f1;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f9f9f9;
}

    </style>

@endsection
@section('css-compare')
@endsection

@section('content')

@if(!empty($category))
@include('frontend.included_files.products_breadcrumb')
@endif
@php
$category_name =  ($category->translation->first()) ? $category->translation->first()->name : $category->slug;
  $img = '';
  $additionalPreference = getAdditionalPreference(['is_token_currency_enable','token_currency', 'add_to_cart_btn']);
@endphp
<!-- <div class="toast">
    <div class="toast-header">
      Toast Header
    </div>
    <div class="toast-body">
      Some text inside the toast body
    </div>
  </div> -->

<section class="section-b-space alSingleProducts product_ddetails_page">
    <div class="collection-wrapper al">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-sm-left">
                        @if (\Session::has('success'))
                            <div class="alert alert-success">
                                <span>{!! \Session::get('success') !!}</span>
                            </div>
                        @endif
                        @if (\Session::has('error'))
                            <div class="alert alert-danger">
                                <span>{!! \Session::get('error') !!}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="container-fluid">
                        {{--<div class="row">
                            <div class="col-xl-12">
                                <div class="filter-main-btn mb-2">
                                    <span class="filter-btn">
                                        <i class="fa fa-filter" aria-hidden="true"></i> filter
                                    </span>
                                </div>
                            </div>
                        </div>--}}
                        <section class="buy_details">
                            <div class="row">
                                @if((!empty($set_template) && !empty($set_template->template_id) && ($set_template->template_id == '8' || $set_template->template_id == '9')))
                                <div class="col-md-1 pl-0">
                                    <div class="exzoom_nav side_nav_img">

                                        @if(!empty($product->media) && count($product->media) > 0)

                                        @foreach($product->media as $k => $image)
                                        @php
                                                        if(isset($image->pimage)){
                                                            $img = $image->pimage->image;
                                                        }else{
                                                            $img = $image->image;
                                                        }
                                                    @endphp
                                            @if(!is_null($img))
                                            <span class="img_active">
                                                <img class="blur-up lazyloaded pro_imgs myimage1"
                                                    data-src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}"
                                                    width="60" height="60"
                                                    src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}">
                                            </span>
                                            @endif
                                        @endforeach
                                        @else
                                        <span class="img_active">
                                                <img class="blur-up lazyloaded pro_imgs myimage1"
                                                    data-src="{{loadDefaultImage()}}"
                                                    width="60" height="60"
                                                    src="{{loadDefaultImage()}}">
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                <div class="{{(!empty($set_template) && !empty($set_template->template_id) && $set_template->template_id != '8') ? 'col-lg-5' : 'col-lg-4'}}  p-0 @php if(count($product->media) == 0){  echo 'd-block'; } @endphp ">
                                    {{-- <div class="product__carousel">
                                        <div class="gallery-parent">
                                            @php
                                                if($product->variant->first()->media->isNotEmpty()){
                                                    $product->media = $product->variant->first()->media;
                                                }

                                                if($product->media->isEmpty()){
                                                    $arr = [
                                                        'image' => (object)[
                                                            'path' => [
                                                                'image_fit' => \Config::get('app.FIT_URl'),
                                                                'image_path' => \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url('default/default_image.png').'@webp'
                                                            ]
                                                        ]
                                                    ];
                                                    $coll = (object)collect($arr)->all();
                                                    $product->media[] = $coll;
                                                }
                                            @endphp
                                            @foreach($product->variantSet as $key => $variant)
                                                @if($variant->type == 1 || $variant->type == 2)
                                                <div class="size-box">
                                                    <ul class="productVariants">
                                                        <li class="firstChild">{{$variant->title}}</li>
                                                        <li class="row otherSize">
                                                            @foreach($variant->option2 as $k => $optn)
                                                            <?php $var_id = $variant->variant_type_id;
                                                            $opt_id = $optn->variant_option_id;
                                                            $checked = ($selectedVariant == $optn->product_variant_id) ? 'checked' : '';
                                                            ?>
                                                            <label class="radio d-inline-block txt-14 col-3 position-relative"> {{$optn->title}}
                                                            <span class="color_var" style="padding:8px; border: 1px dotted #CCC; background:{{$optn->hexacode}};"></span>
                                                                <input id="lineRadio-{{$opt_id}}" name="{{'var_'.$var_id}}" vid="{{$var_id}}" optid="{{$opt_id}}" value="{{$opt_id}}" type="radio" class="changeVariant dataVar{{$var_id}}" {{$checked}}>
                                                                <span class="checkround"></span>
                                                            </label>
                                                            @endforeach
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}

                                    <div class="exzoom hidden w-100">
                                        <div class="exzoom_img_box mb-2">
                                            <ul class='exzoom_img_ul img-sidebar'>
                                            @if(!empty($product->media) && count($product->media) > 0)

                                                @foreach($product->media as $k => $image)
                                                    @php
                                                        if(isset($image->pimage)){
                                                            $img = $image->pimage->image;
                                                        }else{
                                                            $img = $image->image;
                                                        }
                                                    @endphp
                                                @endforeach
                                                @if(!is_null($img))
                                                    <img id="main_image" src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}" />
                                                @endif
                                                @else
                                                    <img id="main_image" class="blur-up lazyload" data-src="{{loadDefaultImage()}}" alt="">
                                                @endif
                                            </ul>
                                        </div>
                                        {{-- @if(count($product->media) > 1)
                                        <div class="exzoom_nav">
                                            @if(!empty($product->media))
                                            @foreach($product->media as $k => $image)
                                            @php
                                                            if(isset($image->pimage)){
                                                                $img = $image->pimage->image;
                                                            }else{
                                                                $img = $image->image;
                                                            }
                                                        @endphp
                                                @if(!is_null($img))
                                                <span class="">
                                                    <img class="blur-up lazyloaded pro_imgs myimage1"
                                                        data-src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}"
                                                        width="60" height="60"
                                                        src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}">
                                                </span>
                                                @endif
                                            @endforeach
                                            @endif
                                        </div>
                                        <p class="exzoom_btn">
                                            <a href="javascript:void(0);" class="exzoom_prev_btn">
                                                </a> <a href="javascript:void(0);" class="exzoom_next_btn"> >
                                            </a>
                                        </p>
                                        @endif --}}
                                    </div>
                                    <div id="myresult" class="img-zoom-result"></div>
                                </div>
                                <div class="@php if(is_category_p2p($product->category)){ echo 'col-lg-6'; }elseif(!empty($product->media) && count($product->media) > 0){ echo 'col-lg-4'; } else { echo 'col-lg-4'; } @endphp rtl-text p-0">
                                    <div class="product-right inner_spacing pl-sm-3 p-0 third-temp-lan">
                                        <h2 class="mb-0">
                                            {{ (!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->title : ''}}
                                            @if ($product->calories)({{$product->calories}} {{ __("calories") }})@endif
                                        </h2>
                                        <h6 class="sold-by">
                                            <b> <img class="blur-up lazyload" data-src="{{$product->vendor->logo['image_fit']}}200/200{{$product->vendor->logo['image_path']}}" alt="{{$product->vendor->Name}}"></b> <a href="{{ route('vendorDetail', $product->vendor->slug) }}"><b> {{$product->vendor->name}} </b></a>
                                        </h6>
                                        {{-- @dd(is_category_p2p($product->category)) --}}
                                        @if($client_preference_detail && !is_category_p2p($product->category))
                                            @if($client_preference_detail->rating_check == 1)
                                                @if($product->averageRating > 0)
                                                    <span class="rating">{{ decimal_format($product->averageRating) }} <i class="fa fa-star text-white p-0"></i></span>
                                                @endif
                                            @endif
                                        @endif
                                       {{-- <div class="description_txt mt-3">
                                            <p>{{ (!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_description : ''}}</p>
                                        </div>--}}
                                        <input type="hidden" name="available_product_variant" id="available_product_variant" value="{{$product->variant[0]->id ?? ''}}">
                                        <input type="hidden" name="start_time" id="start_time" value="">
                                        <input type="hidden" name="end_time" id="end_time" value="">

                                        <div id="product_variant_wrapper">
                                            <input type="hidden" name="variant_id" id="prod_variant_id" value="{{$product->variant[0]->id ?? ''}}">
                                            @if(Session::get('vendorType') != 'p2p' )
                                            @if($product->inquiry_only == 0)
                                                <h3 id="productPriceValue" class="mb-md-3">
                                                    @if($additionalPreference ['is_token_currency_enable'])
                                                        <b class="mr-1">{!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}<span class="product_fixed_price">{{getInToken($product->variant[0]->price * $product->variant[0]->multiplier)}}</span></b>
                                                        @if($product->variant[0]->compare_at_price > 0 )
                                                        <span class="org_price">{!!"<i class='fa fa-money' aria-hidden='true'></i> "!!}<span class="product_original_price">{{getInToken($product->variant[0]->compare_at_price * $product->variant[0]->multiplier)}}</span></span>
                                                        @endif
                                                    @else
                                                        <b class="mr-1">{{Session::get('currencySymbol')}}<span class="product_fixed_price">{{decimal_format($product->variant[0]->price * $product->variant[0]->multiplier)}}</span></b>
                                                        @if($product->variant[0]->compare_at_price > 0 )
                                                            <span class="org_price">{{Session::get('currencySymbol')}}<span class="product_original_price">{{decimal_format($product->variant[0]->compare_at_price * $product->variant[0]->multiplier)}}</span></span>
                                                        @endif
                                                    @endif
                                                </h3>
                                            @endif
                                            @endif
                                        </div>

                                    @if(!empty($product->translation) && isset($product->translation->first()->body_html))
                                        <div class="border-product al_disc">
                                            <h6 class="product-title">{{__('Product Details')}}</h6>
                                            <?php
                                            $content = strip_tags($product->translation->first()->body_html); // Strip HTML tags
                                            $maxContentLength = 200; // Set the maximum length (adjust as needed)

                                            if (strlen($content) > $maxContentLength) {
                                                $content = substr($content, 0, $maxContentLength) . '...';
                                                $fullContent = $product->translation->first()->body_html;
                                                $readMore = true;
                                            } else {
                                                $readMore = false;
                                            }
                                            ?>

                                            <p>
                                                <span id="productContent"><?= $content ?></span>
                                                @if ($readMore)
                                                  <span id="readMoreButton">
                                                    <a href="#" id="readMoreLink" class="read-more-button btn btn-solid">Read More</a>
                                                  </span>
                                                @endif
                                            </p>                                         
                                        </div>
                                    @endif
                                    @if($getAdditionalPreference['product_measurment'] == 1)
                                        <div class="border-product al_disc mb-2">
                                            <h6 class="product-title measurmentClick">{{ __('Product Measurement Details') }} <i class="fa fa-plus"></i></h6>
                                            <div class="table-responsive measurmentDiv" style="display:none">
                                                <table class="table table-striped table-hover">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            @if($product->has_variant)
                                                            <th>{{ __('Variant') }}</th>
                                                            @endif
                                                            @foreach($measurements as $keyData)
                                                                <th>{{ $keyData->key }} ({{ __('cm') }})</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        
                                                        @if($product->has_variant)
                                                            @foreach($variants as $varnt)
                                                                @php
                                                                    $title = $varnt->title;
                                                                    $parts = explode('-', $title);
                                                                    $letterAfterDash = end($parts);
                                                                @endphp
                                                                    <tr class="measurement-row">
                                                                        <td class="variant-cell">
                                                                            <label>{{$letterAfterDash}}</label>
                                                                        </td>
                                                                        @foreach($measurements as $data)
                                                                            <td>
                                                                                <span>{{$productMeasurementData[$data->id][$varnt->id] ?? '' }}</span>
                                                                            </td>
                                                                        @endforeach
                                                                    </tr>
                                                            @endforeach
                                                            @else
                                                            <tr class="measurement-row">
                                                                @foreach($measurements as $data)
                                                                <td>
                                                                    <span>{{ $productMeasurementData[$data->id][null] .'(cm)' ? : '' }}</span>
                                                                </td>
                                                                @endforeach
                                                            </tr>
                                                        @endif
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($processorProduct))
                                    @if (!empty($processorProduct) && $processorProduct->is_processor_enable == 1)
                                        <div class="border-product al_disc">
                                            <h6 class="product-title">{{__('Product processor Details')}}</h6>
                                            <p>{{$processorProduct->name}}</p>
                                            <p>{{$processorProduct->date}}</p>
                                            <p>{{$processorProduct->address}}</p>
                                        </div>
                                    @elseif (!empty($product) && $product->product_pickup_date != null)
                                        <div class="border-product al_disc">
                                            <h6 class="product-title">{{__('Product Vendor Details')}}</h6>
                                            <p>{{$product->product_pickup_date}}</p>
                                        </div>
                                    @endif
                                    @endif
                                    @if( p2p_module_status() && Session::get('vendorType') == 'p2p' )



                                     @if($product->category->categoryDetail->type_id == 13)
                                     <div class="border-product al_disc">
                                        <h6 class="product-title">{{__('Price')}}</h6>
                                        <p>{{Session::get('currencySymbol') . decimal_format($product->variant[0]->price)}}</p>
                                    </div>

                                     @endif
                                     @if($product->category->categoryDetail->type_id == 10)

                                     <div class="flex-container">
                                        <div class="item-price">
                                            <h2>Daily</h2>
                                            <p>{{Session::get('currencySymbol') . decimal_format($product->variant[0]->price)}}</p>
                                        </div>
                                        <div class="item-price">
                                            <h2>7 Days+</h2>
                                            <p>{{Session::get('currencySymbol') . decimal_format($product->variant[0]->week_price)}}</p>
                                        </div>
                                        <div class="item-price">
                                            <h2>30 Days+</h2>
                                            <p>{{Session::get('currencySymbol') . decimal_format($product->variant[0]->month_price)}}</p>
                                        </div>
                                    </div>
                                     @endif


                                    @endif

                                        @if( is_category_p2p($product->category) || is_attribute_enabled())
                                            @if( !empty($attr_array) )
                                                @foreach($attr_array as $attr_key => $attr_val)
                                                    <div class="container-badge">
                                                        <div class="value-badge pr-1"><b>{{ $attr_key }} :</b>
                                                        @if( !empty($attr_val) )
                                                            @foreach($attr_val as $inn_key => $inn_val)

                                                            @if($inn_val['type'] == 2) <!--- for color---->
                                                                <span style="background-color: {{$inn_val['hexacode']}}; width: 20px;height: 20px;margin-left: 5px;display: inline-block;border: 1px solid #ccc;"></span>
                                                            @else
                                                                <span> {{$inn_val['value']}}</span>
                                                            @endif
                                                            @endforeach
                                                        @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif

                                            {{-- Chat Button --}}
                                            <hr>
                                                <h6 class="sold-by">
                                                    @if($clientData->socket_url !='' && getAdditionalPreference(['chat_button'])['chat_button'])
                                                            <?php /*<span>Sold by : </span>
                                                            <b> <img class="blur-up lazyload" data-src="{{$product->vendor->logo['image_fit']}}200/200{{$product->vendor->logo['image_path']}}" alt="{{$product->vendor->Name}}"></b> <a href="{{ route('vendorDetail', $product->vendor->slug) }}"><b> {{$product->vendor->name}} </b></a> */ ?>
                                                            <a class="start_chat chat-icon btn btn-solid"  data-vendor_order_id="" data-chat_type="userToUser" data-vendor_id="{{ $product->vendor->id }}" data-orderid="" data-order_id="" data-product_id="{{ $product->id }}"><i class="fa fa-comments" aria-hidden="true"></i></a>
                                                            {{-- {{__('Chat')}} --}}

                                                    @endif
                                                    @if(getAdditionalPreference(['call_button'])['call_button'])
                                                        <a class="call-icon btn btn-solid" href="tel:"><i class="fa fa-phone-square" aria-hidden="true"></i></a>
                                                        {{-- {{__('Call Button')}} --}}
                                                    @endif
                                                </h6>
                                        @endif


                                        @if(((@$product->returnable && @$product->vendor->return_request) || $product->replaceable) && ($product->return_days > 0))
                                            <div class="discriptions">
                                                <h3>Return Policy</h3>
                                                <p>  <span>{{ $product->return_days }} days return policy is applicable on this product </span> </p>

                                            </div>
                                        @endif

                                        @if(!empty($product->variantSet))
                                            @include('frontend.product-part.product-variant')
                                        @endif



                                        <div id="variant_response">
                                            @if( p2p_module_status() && Session::get('vendorType') == 'p2p' && $product->category->categoryDetail->type_id == 10 )
                                            <input type="text" class="form-control" name="booking_availability" id="range-datepicker" placeholder="{{date('Y-m-d')}}">
                                            @endif
                                        </div>


                                            @if($product->is_recurring_booking == 1)
                                                 @include('frontend.product-part.recurring-booking')
                                            @endif
                                            @if(@getAdditionalPreference(['is_rental_weekly_monthly_price'])==1)
                                                @include('frontend.product-part.booking-slot-p2p-rental')
                                            @elseif($product->category->categoryDetail->type_id == 10 && Session::get('vendorType') == 'car_rental')
                                                @include('frontend.product-part.booking-slot')
                                                @elseif($product->category->categoryDetail->type_id == 10 && Session::get('vendorType') == 'rental')
                                                @include('frontend.product-part.booking')
                                            @endif


                                            @if(!empty($product->addOn) && $product->addOn->count() > 0)
                                            <div class="border-product">
                                                <h6 class="product-title">{{ __('Addon List')}}</h6>

                                                <div id="addon-table">
                                                    @foreach($product->addOn as $row => $addon)
                                                        <div class="addon-product">
                                                            <h4 addon_id="{{$addon->addon_id}}" class="header-title productAddonSet mb-2">{{$addon->title}}
                                                                @php
                                                                    $min_select = '';
                                                                    $minText = __('Minimum');
                                                                    $maxText = __('Maximum');
                                                                    $andText = __('and');
                                                                    if($addon->min_select > 0){
                                                                        $min_select = $minText.' '.$addon->min_select;
                                                                    }
                                                                    $max_select = '';
                                                                    if($addon->max_select > 0){
                                                                        $max_select = $maxText.' '.$addon->max_select;
                                                                    }
                                                                    if( ($min_select != '') && ($max_select != '') ){
                                                                        $min_select = $min_select.' '.$andText.' ';
                                                                    }
                                                                @endphp
                                                                @if( ($min_select != '') || ($max_select != '') )
                                                                    <small>({{__($min_select).__($max_select)}} {{ __('Selections Allowed')}})</small>
                                                                @endif
                                                            </h4>

                                                            <div class="productAddonSetOptions" data-min="{{$addon->min_select}}" data-max="{{$addon->max_select}}" data-addonset-title="{{$addon->title}}">
                                                                @foreach($addon->setoptions as $k => $option)
                                                                <div class="checkbox checkbox-success form-check-inline mb-1">
                                                                    <input type="checkbox" id="inlineCheckbox_{{$row.'_'.$k}}" class="productDetailAddonOption" name="addonData[$row][]" addonId="{{$addon->addon_id}}" addonOptId="{{$option->id}}" data-price="{{$option->price * $option->multiplier}}" data-fixed_price="{{decimal_format($product->variant[0]->price * $product->variant[0]->multiplier)}}" data-original_price="{{decimal_format($product->variant[0]->compare_at_price * $product->variant[0]->multiplier)}}">
                                                                    @if($additionalPreference ['is_token_currency_enable'])
                                                                    <label class="pl-2 mb-0" for="inlineCheckbox_{{$row.'_'.$k}}" data-toggle="tooltip" data-placement="top" title="{{$option->title .' ('.Session::get('currencySymbol').decimal_format($option->price).')' }}">
                                                                    {{$option->title ." ("}}<i class='fa fa-money' aria-hidden='true'></i> {{getInToken($option->price * $option->multiplier).')' }}</label>
                                                                    @else
                                                                    <label class="pl-2 mb-0" for="inlineCheckbox_{{$row.'_'.$k}}" data-toggle="tooltip" data-placement="top" title="{{$option->title .' ('.Session::get('currencySymbol').decimal_format($option->price).')' }}">
                                                                    {{$option->title .' ('.Session::get('currencySymbol').decimal_format($option->price * $option->multiplier).')' }}</label>
                                                                    @endif
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>


                                                {{--<table class="table table-centered table-nowrap table-striped d-none" id="addon-table">
                                                    <tbody>
                                                        @foreach($product->addOn as $row => $addon)
                                                        <tr>
                                                            <td>
                                                                <h4 addon_id="{{$addon->addon_id}}" class="header-title productAddonSet">{{$addon->title}}
                                                                    @php
                                                                        $min_select = '';
                                                                        if($addon->min_select > 0){
                                                                            $min_select = 'Minimum '.$addon->min_select;
                                                                        }
                                                                        $max_select = '';
                                                                        if($addon->max_select > 0){
                                                                            $max_select = 'Maximum '.$addon->max_select;
                                                                        }
                                                                        if( ($min_select != '') && ($max_select != '') ){
                                                                            $min_select = $min_select.' and ';
                                                                        }
                                                                    @endphp
                                                                    @if( ($min_select != '') || ($max_select != '') )
                                                                        <small>({{$min_select.$max_select}} {{ __('Selections Allowed')}})</small>
                                                                    @endif
                                                                </h4>
                                                            </td>
                                                        </tr>
                                                        <tr class="productAddonSetOptions" data-min="{{$addon->min_select}}" data-max="{{$addon->max_select}}" data-addonset-title="{{$addon->title}}">
                                                            <td>
                                                                @foreach($addon->setoptions as $k => $option)
                                                                <div class="checkbox checkbox-success form-check-inline">
                                                                    <input type="checkbox" id="inlineCheckbox_{{$row.'_'.$k}}" class="productDetailAddonOption" name="addonData[$row][]" addonId="{{$addon->addon_id}}" addonOptId="{{$option->id}}">
                                                                    <label class="pl-2" for="inlineCheckbox_{{$row.'_'.$k}}">
                                                                        {{$option->title .' ($'.decimal_format($option->price).')' }}</label>
                                                                </div>
                                                                @endforeach
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>--}}
                                            </div>
                                            @endif

                                            @if(@Auth::user()->role_id == 3)
                                                <div class="border-product">
                                                    <h6 class="product-title">{{ __('Bulk Order')}}</h6>
                                                    <div id="bulk-order-table">

                                                        @foreach ($product->productVariantByRoles as $key => $data)
                                                            @if($data->role_id == 3)
                                                                <h6 bulk_id="{{$data->id}}" class="header-title productAddonSet mb-1">{{__('Greater than or equal to quantity ').$data->quantity.' ( price '.Session::get('currencySymbol').''.$data->amount.' )'}}
                                                                </h6>
                                                            @endif
                                                        @endforeach
                                                        {{-- @foreach($product->addOn as $row => $addon)
                                                            <div class="addon-product">
                                                                <h4 addon_id="{{$addon->addon_id}}" class="header-title productAddonSet mb-2">{{$addon->title}}
                                                                    @php
                                                                        $min_select = '';
                                                                        $minText = __('Minimum');
                                                                        $maxText = __('Maximum');
                                                                        $andText = __('and');
                                                                        if($addon->min_select > 0){
                                                                            $min_select = $minText.' '.$addon->min_select;
                                                                        }
                                                                        $max_select = '';
                                                                        if($addon->max_select > 0){
                                                                            $max_select = $maxText.' '.$addon->max_select;
                                                                        }
                                                                        if( ($min_select != '') && ($max_select != '') ){
                                                                            $min_select = $min_select.' '.$andText.' ';
                                                                        }
                                                                    @endphp
                                                                    @if( ($min_select != '') || ($max_select != '') )
                                                                        <small>({{__($min_select).__($max_select)}} {{ __('Selections Allowed')}})</small>
                                                                    @endif
                                                                </h4>
                                                            </div>
                                                        @endforeach --}}
                                                    </div>


                                                    {{--<table class="table table-centered table-nowrap table-striped d-none" id="addon-table">
                                                        <tbody>
                                                            @foreach($product->addOn as $row => $addon)
                                                            <tr>
                                                                <td>
                                                                    <h4 addon_id="{{$addon->addon_id}}" class="header-title productAddonSet">{{$addon->title}}
                                                                        @php
                                                                            $min_select = '';
                                                                            if($addon->min_select > 0){
                                                                                $min_select = 'Minimum '.$addon->min_select;
                                                                            }
                                                                            $max_select = '';
                                                                            if($addon->max_select > 0){
                                                                                $max_select = 'Maximum '.$addon->max_select;
                                                                            }
                                                                            if( ($min_select != '') && ($max_select != '') ){
                                                                                $min_select = $min_select.' and ';
                                                                            }
                                                                        @endphp
                                                                        @if( ($min_select != '') || ($max_select != '') )
                                                                            <small>({{$min_select.$max_select}} {{ __('Selections Allowed')}})</small>
                                                                        @endif
                                                                    </h4>
                                                                </td>
                                                            </tr>
                                                            <tr class="productAddonSetOptions" data-min="{{$addon->min_select}}" data-max="{{$addon->max_select}}" data-addonset-title="{{$addon->title}}">
                                                                <td>
                                                                    @foreach($addon->setoptions as $k => $option)
                                                                    <div class="checkbox checkbox-success form-check-inline">
                                                                        <input type="checkbox" id="inlineCheckbox_{{$row.'_'.$k}}" class="productDetailAddonOption" name="addonData[$row][]" addonId="{{$addon->addon_id}}" addonOptId="{{$option->id}}">
                                                                        <label class="pl-2" for="inlineCheckbox_{{$row.'_'.$k}}">
                                                                            {{$option->title .' ($'.decimal_format($option->price).')' }}</label>
                                                                    </div>
                                                                    @endforeach
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>--}}
                                                </div>
                                            @endif

                                        @if($product->same_day_delivery == 1 && $product->next_day_delivery == 1 && $product->hyper_local_delivery == 1)
                                        <div class="enterPincodeMsg desktop-pin-message">
                                            <strong> Enter correct Pincode for hassle free timely delivery.</strong>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input type="number" class="form-control" name="pincode" id="pincode" value="" placeholder="Enter Pincode" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" autocomplete="off" data-vendor-id="{{$product->vendor->id??''}}"/>
                                                    <span class="pincode-err text-danger" style="font-size: 14px;"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input class="flatpickr flatpickr-input form-control" type="text" placeholder="Select Date.." data-id="minDate" name="date_input" id="date_input" readonly="readonly" disabled>
                                                    <input type="hidden" name="sele_slot_id" id="sele_slot_id" value="" />
                                                    <input type="hidden" name="sele_slot_price" id="sele_slot_price" value="" />
                                                    <div id="selected_slot"></div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @php
                                            // check if vendor is closed or not, if closed then get slots otherwise no need.
                                            if($vendor_info->is_vendor_closed == 1)
                                                $checkSlot = findSlot('',$product->vendor->id,'');
                                            else
                                                $checkSlot = 0;
                                        @endphp

                                        @if( $product->category->categoryDetail->type_id != 13 )
                                        <div class="btn-wrapper mt-3">
                                            <div id="product_variant_quantity_wrapper" style="display: <?php echo ($product->category->categoryDetail->type_id == 10) ? 'none':'inline-block'; ?>">
                                                @if($product->inquiry_only == 0)
                                                <div class="product-description border-product pb-0">
                                                    <h6 class="product-title mt-0">
                                                        @if($product->category->categoryDetail->slug == 'yacht')
                                                        {{__('Seats Booking')}}
                                                        @else
                                                        {{__('Quantity')}}
                                                        @endif
                                                        :
                                                        @if($product->has_inventory && !$product->variant[0]->quantity > 0 && $product->sell_when_out_of_stock != 1)
                                                            <span id="outofstock" style="color: red;">{{ __('Out of Stock')}}</span>
                                                        @else
                                                        @php
                                                        $product_quantity_in_cart = $product_in_cart->quantity??0;
                                                        @endphp
                                                        <input type="hidden" value="{{$product->has_inventory}}" id="hasInventory">
                                                        <input type="hidden" id="instock" value="{{ ($product->variant[0]->quantity - $product_quantity_in_cart)}}">
                                                        @endif
                                                    </h6>
                                                    @if(!$product->has_inventory || $product->variant[0]->quantity > 0 || $product->sell_when_out_of_stock == 1)
                                                    @if($product->minimum_order_count > 1)
                                                    {{-- <p class="mb-1 product_price">   {{__('Minimum Quantity') }} : {{ $product->minimum_order_count }} </p>
                                                    <p class="mb-1 product_price">   {{__('Batch') }} : {{ $product->batch_count }} </p> --}}
                                                    @endif
                                                    <div class="qty-box mb-3">
                                                        <div class="input-group">
                                                            <span class="input-group-prepend">
                                                                <button type="button" class="btn quantity-left-minus" data-type="minus" data-field="" data-batch_count={{$product->batch_count}} data-minimum_order_count={{$product->minimum_order_count}}><i class="fa fa-minus" aria-hidden="true"></i>
                                                                </button>
                                                            </span>
                                                            <input type="text" name="quantity"  onkeypress="return event.charCode > 47 && event.charCode < 58;" pattern="[0-9]{5}" id="quantity" class="form-control input-qty-number quantity_count"  value="{{$product->minimum_order_count??1}}" data-minimum_order_count={{$product->minimum_order_count}}>
                                                            <span class="input-group-prepend quant-plus">
                                                                <button type="button" class="btn quantity-right-plus" data-type="plus" data-field="" data-batch_count={{$product->batch_count}} data-minimum_order_count={{$product->minimum_order_count}}>
                                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                @endif

                                            </div>

                                            @if($product->category && $product->category->categoryDetail->slug == 'yacht')
                                                <div>
                                                    <label for="">Pickup Service</label>
                                                    <input type="checkbox" name="pickup_service" id="pickup_service">
                                                </div>
                                            @endif

                                            <div class="product-buttons">


                                                @if(!$product->has_inventory || $product->variant[0]->quantity > 0  || $product->sell_when_out_of_stock == 1)
                                                @if($is_inwishlist_btn && $is_available)
                                                <button type="button" class="btn btn-solid addWishList mr-2" proSku="{{$product->sku}}" remWishlist="{{ __('Remove From Wishlist') }}" addWishlist="{{ __('Add To Wishlist') }}">
                                                    {{ (isset($product->inwishlist) && (!empty($product->inwishlist))) ? __('Remove From Wishlist') : __('Add To Wishlist') }}
                                                </button>
                                                @endif
                                                @if($product->inquiry_only == 0)
                                                @php
                                                if($product->sell_when_out_of_stock == 1 && $product->variant[0]->quantity == 0){
                                                    $product_quantity_in_cart = 1;
                                                    $product->variant[0]->quantity = 2;
                                                }
                                                else
                                                $product_quantity_in_cart = $product_in_cart->quantity??0;


                                                @endphp
                                                {{-- @dd($product->variant[0]->quantity) --}}
                                                @if($is_available == 1)
                                                <button type="button" data-toggle="modal" data-target="#addtocart" class="btn btn-solid addToCart  {{ (($checkSlot == 0  && $vendor_info->is_vendor_closed == 1) || ($product->variant[0]->quantity <= $product_quantity_in_cart && $product->has_inventory)) ? 'btn-disabled' : '' }}" id="add_to_cart_btn">{{__('Add To Cart')}}</button>
                                                @endif

                                                    @if($vendor_info->is_vendor_closed == 1 && $checkSlot == 0)
                                                    <p class="text-danger">{{getNomenclatureName('Vendors', true) . __(' is not accepting orders right now.')}}</p>
                                                    @elseif($vendor_info->is_vendor_closed == 1 && $vendor_info->closed_store_order_scheduled == 1)
                                                    <p class="text-danger">{{ __('We are not accepting orders right now. You can schedule this for '). $checkSlot}}.</p>
                                                    @endif
                                                @else
                                                    <a href="#" data-toggle="modal" data-target="#inquiry_form" class="btn btn-solid inquiry_mode">{{ __('Inquire Now')}}</a>
                                                @endif
                                                @endif
                                            </div>

                                        </div>
                                        @else
                                        <div class="product-buttons">
                                            @if($is_inwishlist_btn && $is_available)
                                            <button type="button" class="btn btn-solid addWishList mr-2" proSku="{{$product->sku}}" remWishlist="{{ __('Remove From Wishlist') }}" addWishlist="{{ __('Add To Wishlist') }}">
                                                {{ (isset($product->inwishlist) && (!empty($product->inwishlist))) ? __('Remove From Wishlist') : __('Add To Wishlist') }}
                                            </button>
                                            @endif
                                            @php
                                                if($product->sell_when_out_of_stock == 1 && $product->variant[0]->quantity == 0){
                                                    $product_quantity_in_cart = 1;
                                                    $product->variant[0]->quantity = 2;
                                                }
                                                else
                                                    $product_quantity_in_cart = $product_in_cart->quantity??0;
                                                @endphp
                                            @if($is_available == 1 && $additionalPreference['add_to_cart_btn'] == 1)
                                                <a href="#" data-toggle="modal" data-target="#addtocart" class="btn btn-solid addToCart {{ (($checkSlot == 0  && $vendor_info->is_vendor_closed == 1) || ($product->variant[0]->quantity <= $product_quantity_in_cart && $product->has_inventory)) ? 'btn-disabled' : '' }}">{{__('Add To Cart')}}</a>
                                            @endif
                                        </div>
                                        @endif
                                        {{-- @dump($product) --}}
                                        <!-- <div class="border-product al_disc">
                                            <h6 class="product-title">{{__('Product Details')}}</h6>
                                            <p></p>
                                            {!!(!empty($product->translation) && isset($product->translation[0])) ?
                                                $product->translation[0]->body_html : ''!!}
                                        </div> -->
                                        <div class="border-product">
                                            <h6 class="product-title">{{__('Share It')}}</h6>
                                            <div class="product-icon w-100">
                                                <!-- <ul class="product-social"> -->
                                                    {!! $shareComponent !!}
                                                    <!-- <li><a href="#"><i class="fa fa-twitter"></i></a></li> -->
                                                    <!-- <li><a href="#"><i class="fa fa-facebook"></i></a></li> -->
                                                    <!-- <li><a href="#"><i class="fa fa-google-plus"></i></a></li> -->
                                                    <!-- <li><a href="#"><i class="fa fa-instagram"></i></a></li> -->
                                                <!-- </ul>   -->
                                            </div>
                                        </div>


                                    </div>

                                </div>
                                @if( p2p_module_status() && Session::get('vendorType') == 'p2p' )
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="summary-box" style="display:none;">
                                                <table id="summary-table">
                                                    <tr>
                                                        <th>Description</th>
                                                        <th class="text-right">Amount</th>
                                                    </tr>
                                                    <tr>
                                                        <td>Days <span class="days-count"></span></td>
                                                        <td class="text-right date-range"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="applied-price"></span> x <span class="days-count"></span> Days</td>
                                                        <td class="text-right applied-total-amount"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Platform Fee</td>
                                                        <td class="text-right platform-fee">$200</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total</strong></td>
                                                        <td class="text-right"><strong class="total-amount"></strong></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                </div>
                                @endif
                                @if( !is_category_p2p($product->category) && @$set_template->template_id == '8' )
                                    @include('frontend.product-coupon')
                            @endif
                            </div>
                        </section>
                        <div class="row mt-1">
                            <div class="col-md-12">
                                @if($client_preference_detail && $client_preference_detail->rating_check == 1 && !is_category_p2p($product->category))
                                <section class="tab-product custom-tabs">
                                    <div class="row">
                                        <div class="col-sm-12 col-lg-12">
                                            <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                                                <!-- <li class="nav-item"><a class="nav-link active" id="top-home-tab" data-toggle="tab" href="#top-home" role="tab" aria-selected="true"><i class="icofont icofont-ui-home"></i>{{__('Description')}}</a>
                                                    <div class="material-border"></div>
                                                </li>
                                                <li class="nav-item"><a class="nav-link" id="profile-top-tab" data-toggle="tab"
                                                        href="#top-profile" role="tab" aria-selected="false"><i
                                                            class="icofont icofont-man-in-glasses"></i>Details</a>
                                                    <div class="material-border"></div>
                                                </li> -->
                                                @if($client_preference_detail && $client_preference_detail->rating_check == 1 && count($rating_details)>0)
                                                <li class="nav-item "><a class="nav-link active" id="review-top-tab" data-toggle="tab" href="#top-review" role="tab" aria-selected="false"><i class="icofont icofont-contacts"></i>{{__('Ratings & Reviews')}}</a>
                                                    <div class="material-border"></div>
                                                </li>
                                                @endif

                                                @if(@getAdditionalPreference(['is_enable_compare_product'])['is_enable_compare_product'] &&
                                                (in_array($product->category->category_id,getVendorAdditionalPreference($product->vendor_id,'compare_categories'))))

                                                <li class="nav-item ml-3"><a class="nav-link {{(count($rating_details)>0)?'':'active'}}" id="compare-product-tab" data-toggle="tab" href="#compare-product" role="tab" aria-selected="false"><i class="icofont icofont-contacts"></i>{{__('Compare products')}}</a>
                                                    <div class="material-border"></div>
                                                </li>
                                @endif
                                            </ul>
                                            <div class="tab-content nav-material" id="top-tabContent">
                                                {{-- <div class="tab-pane fade" id="top-home" role="tabpanel" aria-labelledby="top-home-tab">
                                                    <p>{!! (!empty($product->translation) && isset($product->translation[0])) ?
                                                        $product->translation[0]->body_html : ''!!}</p>
                                                </div>
                                                <div class="tab-pane fade" id="top-profile" role="tabpanel" aria-labelledby="profile-top-tab">
                                                    <p>{!! (!empty($product->translation) && isset($product->translation[0])) ?
                                                        $product->translation[0]->body_html : ''!!}</p>
                                                </div> --}}
                                                <div class="tab-pane show {{(count($rating_details)>0)?'active':''}}" id="top-review" role="tabpanel" aria-labelledby="review-top-tab">
                                                    @forelse ($rating_details as $rating)
                                                    <div v-for="item in list" class="w-100 d-flex justify-content-between mb-2">
                                                        <div class="review-box customer_review">
                                                            <div class="">
                                                                <div class="customer_review_item_row">
                                                                    <div class="image">
                                                                        <img src="{{$rating->user->image['proxy_url'].'400/160'.$rating->user->image['image_path']}}" alt="{{$rating->user->name??'NA'}}">
                                                                    </div>
                                                                    <div class="">
                                                                        <h4>{{$rating->user->name??'NA'}}</h4>
                                                                    </div>
                                                                </div>
                                                                <div class="star review-author">
                                                                    <p>
                                                                        <i class="fa fa-star{{ $rating->rating >= 1 ? '' : '-o' }}" aria-hidden="true"></i>
                                                                        <i class="fa fa-star{{ $rating->rating >= 2 ? '' : '-o' }}" aria-hidden="true"></i>
                                                                        <i class="fa fa-star{{ $rating->rating >= 3 ? '' : '-o' }}" aria-hidden="true"></i>
                                                                        <i class="fa fa-star{{ $rating->rating >= 4 ? '' : '-o' }}" aria-hidden="true"></i>
                                                                        <i class="fa fa-star{{ $rating->rating >= 5 ? '' : '-o' }}" aria-hidden="true"></i>
                                                                    </p>
                                                                </div>
                                                                <div class="review-date mt-2">
                                                                    <time> {{ $rating->time_zone_created_at->diffForHumans();}} </time>
                                                                </div>
                                                                <div class="review-images">
                                                                    @if(isset($rating->reviewFiles))
                                                                        @foreach ($rating->reviewFiles as $files)
                                                                            <a target="_blank" href="{{$files->file['image_fit'].'900/900'.$files->file['image_path']}}" class="col review-photo mt-2 lightBoxGallery" data-gallery="">
                                                                                <img class="blur-up lazyload" data-src="{{$files->file['image_fit'].'300/300'.$files->file['image_path']}}">
                                                                            </a>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="review_dis">
                                                                <p>{{$rating->review??''}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @empty
                                                    <p>{{__('No Reviews Yet')}}</p>
                                                    @endforelse
                                                </div>
                                                @include('frontend.compare-product-table')
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- Related Products --}}
                    @if(!empty($set_template) && !empty($set_template->template_id) && ($set_template->template_id == '8' || $set_template->template_id == '9'))
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            @php
                                $similar_title = getNomenclatureName('Similar Product', true);
                                $similar_title_label = ($similar_title=="Similar Product")?__('Similar Product'):__($similar_title);
                            @endphp
                            @include('frontend.product-component.category-related-product', ['realted_produuct' => $suggested_category_products, 'title' => $similar_title_label.' In '.$category_name ])
                            @include('frontend.product-component.category-related-product', ['realted_produuct' => $suggested_brand_products, 'title' => 'Brand Related Product'])
                            @include('frontend.product-component.category-related-product', ['realted_produuct' => $suggested_vendor_products, 'title' => $similar_title_label.' By '. $product->vendor->name])
                        </div>
                    </div>
                    @endif
                    {{-- End of Related Products --}}


                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/template" id="variant_image_template">
    <% if(variant.media != '') { %>
        <div class="swiper-container gallery-top">
            <div class="swiper-wrapper">
                <% _.each(variant.media, function(img, key){ %>
                    <div class="swiper-slide easyzoom easyzoom--overlay">
                        <a href="<%= img.pimage.image.path['image_fit'] %>600/600<%= img.pimage.image.path['image_path'] %>">
                        <img class="blur-up lazyload" data-src="<%= img.pimage.image.path['image_fit'] %>600/600<%= img.pimage.image.path['image_path'] %>" alt="">
                        </a>
                    </div>
                <% }); %>
            </div>
            <!-- Add Arrows -->
            <div class="swiper-button-next swiper-button-white"></div>
            <div class="swiper-button-prev swiper-button-white"></div>
        </div>
        <div class="swiper-container gallery-thumbs">
            <div class="swiper-wrapper">
                <% _.each(variant.media, function(img, key){ %>
                    <div class="swiper-slide">
                        <img class="blur-up lazyload" data-src="<%= img.pimage.image.path['image_fit'] %>300/300<%= img.pimage.image.path['image_path'] %>" alt="">
                    </div>
                <% }); %>
            </div>
        </div>
    <% }else{ %>
        <div class="swiper-container gallery-top">
            <div class="swiper-wrapper">
                <% _.each(variant.product.media, function(img, key){ %>
                    <% if(img.image != null) {%>
                        <div class="swiper-slide easyzoom easyzoom--overlay">
                            <a href="<%= img.image.path['image_fit'] %>600/600<%= img.image.path['image_path'] %>">
                            <img class="blur-up lazyload" data-src="<%= img.image.path['image_fit'] %>600/600<%= img.image.path['image_path'] %>" alt="">
                            </a>
                        </div>
                    <% }; %>
                <% }); %>
            </div>
            <!-- Add Arrows -->
            <div class="swiper-button-next swiper-button-white"></div>
            <div class="swiper-button-prev swiper-button-white"></div>
        </div>
        <div class="swiper-container gallery-thumbs">
            <div class="swiper-wrapper">
                <% _.each(variant.product.media, function(img, key){ %>
                    <% if(img.image != null) {%>
                        <div class="swiper-slide">
                            <img class="blur-up lazyload" data-src="<%= img.image.path['image_fit'] %>300/300<%= img.image.path['image_path'] %>" alt="">
                        </div>
                    <% }; %>
                <% }); %>
            </div>
        </div>
    <% } %>
</script>
<script type="text/template" id="variant_template">
    <input type="hidden" name="variant_id" id="prod_variant_id" value="<%= variant.id %>">
    <% if(variant.product.inquiry_only == 0) { %>
        <h3 id="productPriceValue" class="mb-md-3">
            <% if(is_token_enable == 1) { %>
                    <b class="mr-1"><i class='fa fa-money' aria-hidden='true'></i><span class="product_fixed_price"> <%= Helper.formatPrice(variant.productPrice * tokenAmount) %></span></b>
                    <% if(variant.compare_at_price > 0 ) { %>
                        <span class="org_price"><i class='fa fa-money' aria-hidden='true'></i><span class="product_original_price"> <%= Helper.formatPrice(variant.compare_at_price * tokenAmount) %></span></span>
                    <% } %>
                <% }else{%>
                    <b class="mr-1">{{Session::get('currencySymbol')}}<span class="product_fixed_price"><%= Helper.formatPrice(variant.productPrice) %></span></b>
                    <% if(variant.compare_at_price > 0 ) { %>
                        <span class="org_price">{{Session::get('currencySymbol')}}<span class="product_original_price"><%= Helper.formatPrice(variant.compare_at_price) %></span></span>
                    <% } %>
            <% } %>
        </h3>
    <% } %>
</script>
<script type="text/template" id="variant_options_template">
    <% _.each(availableSets, function(type, kkey){ %>
        <% if(type.variant_detail.type == 1 || type.variant_detail.type == 2) { %>
            <div class="size-box">
                <ul class="productVariants">
                    <li class="firstChild"><%= type.variant_detail.title %></li>
                    <li class="otherSize">
                        <% _.each(type.option_data, function(opt, key){ %>
                        <label class="radio d-inline-block txt-14 mr-2"><%= opt.title %>
                            <input id="lineRadio-<%= opt.id %>" name="var_<%= opt.variant_id %>" vid="<%= opt.variant_id %>" optid="<%= opt.id %>" value="<%= opt.id %>" type="radio" class="changeVariant dataVar<%= opt.variant_id %>">
                            <span class="checkround"></span>
                        </label>
                        <% }); %>
                    </li>
                </ul>
            </div>
        <% } %>
    <% }); %>
</script>
<script type="text/template" id="variant_quantity_template">
    <% if(variant.product.inquiry_only == 0) { %>
    <div class="product-description border-product pb-0">
        <h6 class="product-title mt-0">{{__('Quantity')}}:
            <% if(variant.product.has_inventory && !(variant.quantity > 0) && (variant.product.sell_when_out_of_stock != 1)){ %>
                <span id="outofstock" style="color: red;">{{__('Out of Stock')}}</span>
            <% }else{ %>
                <input type="hidden" id="instock" value="<%= variant.quantity %>">
            <% } %>
        </h6>
        <% if(!variant.product.has_inventory || (variant.quantity > 0) || (variant.product.sell_when_out_of_stock == 1)){ %>
        <div class="qty-box mb-3">
            <div class="input-group">
                <span class="input-group-prepend">
                    <button type="button" class="btn quantity-left-minus" data-type="minus" data-field="" data-batch_count="<%= variant.product.batch_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>"><i class="ti-angle-left"></i>
                    </button>
                </span>
                <input type="text" onkeypress="return event.charCode > 47 && event.charCode < 58;" pattern="[0-9]{5}" name="quantity" id="quantity" class="form-control input-qty-number quantity_count" value="<%= variant.product.minimum_order_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>">
                <span class="input-group-prepend quant-plus">
                    <button type="button" class="btn quantity-right-plus " data-type="plus" data-field="" data-batch_count="<%= variant.product.batch_count %>" data-minimum_order_count="<%= variant.product.minimum_order_count %>">
                        <i class="ti-angle-right"></i>
                    </button>
                </span>
            </div>
        </div>
        <% } %>
    </div>
    <% } %>
</script>
@if($product->related_products->count() > 0)


<section class="section-b-space ratio_asos alProductsPage">
    <div class="container">
        <div class="row m-0">
            <div class="col-12 p-0">
                <h3>{{__('Related products')}}</h3>
            </div>
        </div>
    </div>
    <div class="container pb-md-4">
        <div class="product-m  related-products pb-2  related-css">
            @forelse($product->related_products as $related_product)
            <div class="product-card-box position-relative al_box_third_template al ">
                {{-- <a class="common-product-box scale-effect text-center" href="{{route('productDetail',[$related_product->vendor->slug,$related_product->url_slug])}}"> </a> --}}
                <div class="img-outer-box position-relative">
                    <a class="common-product-box scale-effect text-center" href="{{route('productDetail',[$related_product->vendor->slug,$related_product->url_slug])}}">
                    <img class="img-fluid blur-up lazyload" data-src="{{ $related_product->image_url }}" alt="">
                    </a>
                    <!-- <div class="pref-timing">
                        <span>5-10 min</span>
                    </div> -->
                    <!-- <i class="fa fa-heart-o fav-heart" aria-hidden="true"></i> -->
                </div>
                <div class="media-body align-self-center">
                    <div class="inner_spacing px-0">
                        <div class="product-description">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="card_title ellips">{{ $related_product->translation_title }}</h6>
                            </div>
                            <p>{{ $related_product->vendor_name }}</p>
                            <p class="border-bottom pb-1">In {{$related_product->category_name}}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <b>
                                    @if($related_product->inquiry_only == 0)
                                    {{ Session::get('currencySymbol') . $related_product->variant_price }}
                                    @endif
                                </b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            @endforelse
        </div>
    </div>
</section>
@endif
<div class="modal fade product-rating" id="product_rating" tabindex="-1" aria-labelledby="product_ratingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="review-rating-form-modal"></div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="inquiry_form" tabindex="-1" aria-labelledby="inquiry_formLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="inquiry_formLabel">{{__('Inquiry')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @php
                $user = Auth::user();
                @endphp
                <form id="inquiry-form">
                    <div class="row">
                        <input type="hidden" name="vendor_id" value="{{$product->vendor_id}}" />
                        <input type="hidden" name="product_id" value="{{$product->id}}" />
                        <div class="col-md-6 form-group">
                            <label>{{__('Name')}}</label>
                            <input class="form-control" name="name" id="name" value="{{$user ? $user->name : '' }}" type="text" placeholder="{{__('Name')}}">
                            <span class="text-danger error-text nameError"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('Email')}}</label>
                            <input class="form-control" name="email" id="email" value="{{$user ? $user->email : '' }}" type="text" placeholder="{{__('Email')}}">
                            <span class="text-danger error-text emailError"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('Phone Number')}}</label>
                            <input class="form-control" name="number1" id="number1" value="{{$user ? $user->phone_number : '' }}" type="text" placeholder="{{__('Phone Number')}}" style="display:inline-block;">
                            <span class="text-danger error-text numberError"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('Company Name')}}</label>
                            <input class="form-control" name="company_name" id="company_name" type="text" placeholder="{{__('Company Name')}}">
                        </div>
                        <div class="col-12 form-group">
                            <label>{{__('Message')}}</label>
                            <textarea class="form-control" name="message" id="message" cols="30" rows="8" placeholder="{{__('Message')}}"></textarea>
                            <span class="text-danger error-texprapt messageError"></span>
                        </div>
                        <div class="col-12 form-group checkbox-input">
                            <input type="checkbox" id="agree" name="agree" required>
                            <label for="agree">{{__('I accept the')}} <a href="{{url('page/terms-conditions')}}" target="_blank">{{__('Terms And Conditions')}}</a> {{__('and have read the')}} <a href="{{url('page/privacy-policy')}}" target="_blank"> {{__('Privacy Policy')}}</a></label>
                            <span class="d-block text-danger error-text agreeError"></span>
                        </div>
                        <div class="col-12 mt-2">
                            <button type="button" class="btn btn-solid w-100 submitInquiryForm">{{__('Submit')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@php

$user_type = 'user';
$to_message = 'to_user';
$from_message = 'from_user';
$chat_type = 'user_to_user';
$startChatype = 'user_to_user';
$apiPre = 'client';
$rePre = 'user/chat/userToUser';
$fetchDe = 'fetchRoomByUserIdUserToUser';
@endphp

<script>
    var to_message = `<?php echo $to_message; ?>`;
    var user_type = `<?php echo $user_type; ?>`;
    var from_message = `<?php echo $from_message; ?>`;
    var chat_type = `<?php echo $chat_type; ?>`;
    var startChatype = `<?php echo $startChatype; ?>`;
    var apiPre = `<?php echo $apiPre; ?>`;
    var rePre = `<?php echo $rePre; ?>`;
    var fetchDe = `<?php echo $fetchDe; ?>`;
</script>
<div class="modal fade" id="delivery_form" tabindex="-1" aria-labelledby="delivery_formLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="delivery_formLabel">{{__('Select Delivery Slot')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="delivery_option">

            </div>
        </div>
    </div>
</div>

@endsection
@section('js-script')
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
{{-- <script src="{{asset('assets/js/chat/user_vendor_chat.js')}}"></script> --}}
<script src="{{asset('assets/js/chat/commonChat.js')}}"></script>
<script type="text/javascript"src="{{asset('front-assets/js/slick.js')}}"></script>
<script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>
<script type="text/javascript" src="{{asset('front-assets/js/jquery.elevatezoom.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection
@section('script')
<script>
    var recurringformPost = {};
    var maximumquantitylert = "{{__('Quantity is not available in stock')}}";
    var minimumquantitylert = "{{__('Minimum Quantity count is')}}";

    $(document).on('click', '.submitInquiryForm', function(e) {
        e.preventDefault();
        var formData = new FormData(document.getElementById("inquiry-form"));
        formData.append("variant_id", $('#prod_variant_id').val());
        var submit_url = "{{ route('inquiryMode.store') }}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: submit_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#inquiry_form').modal('hide');
            },
            error: function(response) {
                // console.log(response);
                $('.messageError').html(response.responseJSON.errors.message[0]);
                $('.agreeError').html(response.responseJSON.errors.agree[0]);
                $('.numberError').html(response.responseJSON.errors.number[0]);
                $('.emailError').html(response.responseJSON.errors.email[0]);
                $('.nameError').html(response.responseJSON.errors.name[0]);
            },
            complete: function() {}
        });
    });

    $("#pincode").blur(function(e){
        e.preventDefault();
        var vendor_id = $(this).data('vendor-id');
        var pincode = $(this).val();
        var url = "{{ route('pincode.checkVendorPincode') }}";
        if(pincode != ''){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "post",
                headers: {
                    Accept: "application/json"
                },
                url: url,
                data: {vendor_id:vendor_id,pincode:pincode},
                dataType: "json",
                success: function(response) {
                    if(response.success == true){
                        $('#date_input').prop("disabled", false);
                        $('.pincode-err').text('');
                    }else{
                        $('#date_input').val("");
                        $('#date_input').prop("disabled", true);
                        $('.pincode-err').text('This product cannot be delivered here');
                    }
                },
                error: function(response) {
                    console.log(response);
                },
                complete: function() {}
            });
        }
    });

    // $('#date_input').change(function(){
    //     var input_date = $(this).val();
    //     $.ajax({
    //         url: "{{route('pincode.getShippingMethod')}}",
    //         type: "get",
    //         datatype: "html",
    //         data: {input_date:input_date},
    //         success: function(data){
    //             $('#delivery_form').modal('show');
    //             $("#delivery_option").empty().html(data);
    //         },
    //         error: function() {
    //             $("#delivery_option").empty().html('Something went wrong');
    //         }
    //     });
    // });

    $('#date_input').change(function(){
        var input_date = $(this).val();
        var product_id = "{{$product->id}}";
        // var vendor_cutOff_time = "{{$product->vendor->cutOff_time??''}}";
        if(input_date != ''){
            $.ajax({
                url: "{{route('product.getShippingProductDeliverySlots')}}",
                type: "get",
                datatype: "html",
                data: {input_date:input_date,product_id:product_id}, //,vendor_cutOff_time:vendor_cutOff_time
                success: function(data){
                    $('#delivery_form').modal({backdrop: 'static', keyboard: false});
                    $("#delivery_option").empty().html(data);
                },
                error: function() {
                    $("#delivery_option").empty().html('Something went wrong');
                }
            });
        }
    });

    // $(document).on('change', '#delivery_form .delivery_option', function(){
    //     var shipping_method_id = $(this).val();
    //     var product_id = "{{$product->id}}";
    //     $.ajax({
    //         url: "{{route('product.getShippingProductDeliverySlots')}}",
    //         type: "get",
    //         datatype: "html",
    //         data: {shipping_method_id:shipping_method_id, product_id:product_id},
    //         success: function(data){
    //             $('#delivery_form .modal-title').text('Select Delivery Slots');
    //             $("#delivery_option").empty().html(data);
    //         },
    //         error: function() {
    //             $("#delivery_option").empty().html('Something went wrong');
    //         }
    //     });
    // });

    $(document).on('change', '#delivery_form .delivery_slot', function(){
        $.ajax({
            url: "{{route('product.getShippingSlotsInterval')}}",
            type: "get",
            datatype: "html",
            data: {slot_id:$(this).val()},
            success: function(data){
                $('#delivery_form .modal-title').text('Select Delivery Slots');
                $("#delivery_option").empty().html(data);
            },
            error: function() {
                $("#delivery_option").empty().html('Something went wrong');
            }
        });
    });

    $(document).on('change', '#delivery_form .delivery_slot_interval', function(){
        var slot_price = $(this).data('price');
        var slot_id = $(this).val();
        var slot_text = $(this).data('slot-text');
        $('#sele_slot_id').val(slot_id);
        $('#sele_slot_price').val(slot_price);
        $('#selected_slot').text(slot_text);
        $('#delivery_form').modal('hide');
    });


    $(document).ready(function(){
        var cutOff_time = "{{@$current_time_response}}";
        var date_var;

        if( cutOff_time == 1) {
            date_var = new Date();
        } else {
            date_var = new Date();
            date_var.setDate(date_var.getDate()+1);
        }

        $('.flatpickr').flatpickr({
            enableTime: false,
            startDate: date_var,
            minDate: date_var,
            dateFormat: "Y-m-d" //H:i
        });
    });

    var valueHover = 0;

    function calcSliderPos(e, maxV) {
        return (e.offsetX / e.target.clientWidth) * parseInt(maxV, 10);
    }

    $(".starrate").on("click", function() {
        $(this).data('val', valueHover);
        $(this).addClass('saved')
    });

    $(".starrate").on("mouseout", function() {
        upStars($(this).data('val'));
    });


    $(".starrate span.ctrl").on("mousemove", function(e) {
        var maxV = parseInt($(this).parent("div").data('max'))
        valueHover = Math.ceil(calcSliderPos(e, maxV) * 2) / 2;
        upStars(valueHover);
    });


    function upStars(val) {
        var val = parseFloat(val);
        $("#test").html(val.toFixed(1));

        var full = Number.isInteger(val);
        val = parseInt(val);
        var stars = $("#starrate i");

        stars.slice(0, val).attr("class", "fa fa-star");
        if (!full) {
            stars.slice(val, val + 1).attr("class", "fa fa-star-half-o");
            val++
        }
        stars.slice(val, 5).attr("class", "fa fa-star-o");
    }


    $(document).ready(function() {
        $(".starrate span.ctrl").width($(".starrate span.cont").width());
        $(".starrate span.ctrl").height($(".starrate span.cont").height());
        // $(document).on("click",".color_var", function() {
        // 	var name  = $(this).attr("data-id");
        //     $(".var_"+name).removeClass("var-active");
        //     $(this).toggleClass("var-active");
        // });
        // $(document).on("click",".radio_var", function() {
        // 	var name  = $(this).attr("data-id");
        //     $(".radio_"+name).removeClass("radio-active");
        //     //$(this).toggleClass("radio-active");
        // });
        $(document).on("click",".radio", function() {
             var name = $(this).find(".changeVariant").attr("vid");
            $(`.var_${name}`).removeClass("radio-active");
            $(this).children().last().addClass("radio-active");

        });
    });
</script>

<script type="text/javascript">
    var ajaxCall = 'ToCancelPrevReq';
    var is_token_currency_enable = "{{$additionalPreference['is_token_currency_enable']}}";
    var token_currency = "{{$additionalPreference['token_currency']}}";
    let vendor_id = "{{ $product->vendor_id }}";
    let product_id = "{{ $product->id }}";
    var add_to_cart_url = "{{ route('addToCart') }}";
    $(document).on('click', '.changeVariant', function() {
        var $this = $(this);

        // var data_id = $(this).attr('data-variant-id');
        // // Set session variable
        // sessionStorage.setItem('selected_variant', data_id);
        // var myValue = sessionStorage.getItem('selected_variant');
        var myValue = []; // Initialize an empty array

        $('.selected_variant:checked').each(function() {
            var value = $(this).attr('data-variant-id'); // Get the value of the 'data' attribute
            myValue.push(value); // Push the value into the array
        });

        var variant_val = $(this).val();
        var option_title = $(this).data('option-title');
        $('.changeVariant_'+option_title).removeAttr('checked');
        $this.attr('checked', 'checked');
       $key =  $(this).data('row-key');
        updatePrice(myValue ,$key);
    });

    $(document).on('click', '.selected_variant', function() {
        var $this = $(this);
        var option_title = $(this).data('option-title');
        $('.changeVariant_'+option_title).removeAttr('checked');
        // $this.attr('checked', 'checked');
        // var isSelected = $this.is(':checked');
        // if(isSelected){
        //     // alert($(this).data('variant-id'));
        //     $('#prod_variant_id').val($(this).data('variant-id'));
        // }
    });

    function updatePrice(myValue ,key){
        var variants = [];
        var options = [];
        var firstCheckedSelectedTitle = $('.changeVariant:checked').first().parent().data('title');
        $('.changeVariant').each(function() {
            if (this.checked == true) {
                var that = this;
                variants.push($(that).attr('vid'));
                options.push($(that).attr('optid'));
            }
        });
        $.ajax({
            url: "{{ route('productVariant', $product->sku) }}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                "variants": variants,
                "options": options,
                "selected_variant_title": firstCheckedSelectedTitle,
                "key": key,
                'is_variant_checked':myValue
            },
            success: function(response) {
                if(response.status == "Success"){
                    // if(response.html != ''){
                    //     $("#variant_options").html('');
                    //     $("#variant_options").html(response.html);
                        $('#prod_variant_id').val(response.selected_variant.product_variant_id);
                    //}

                    if(response.selected_variant.price != null){
                        let price = parseFloat(response.selected_variant.price);
                        let compare_at_price = parseFloat(response.selected_variant.compare_at_price);
                        $('.product_fixed_price').html(price.toFixed(2));
                        $('.product_original_price').html(compare_at_price.toFixed(2));
                    }

                }
                // Handle the successful response
            },
            error: function(xhr) {
                console.log(xhr);
                // Handle the error
            }
        });
    }


    // function updatePrice(){
    //     var variants = [];
    //     var options = [];
    //     var selected_variant_title = "";
    //     $('.changeVariant').each(function() {
    //         var that = this;
    //         if (this.checked == true) {
    //             variants.push($(that).attr('vid'));
    //             options.push($(that).attr('optid'));
    //             selected_variant_title = $(that).parent().attr('data-title');
    //         }
    //     });
    //     ajaxCall = $.ajax({
    //         type: "post",
    //         dataType: "json",
    //         url: "{{ route('productVariant', $product->sku) }}",
    //         data: {
    //             "_token": "{{ csrf_token() }}",
    //             "variants": variants,
    //             "options": options,
    //             "selected_variant_title": selected_variant_title
    //         },
    //         beforeSend: function() {
    //             if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
    //                 ajaxCall.abort();
    //             }
    //         },
    //         success: function(resp) {
    //             console.log(resp);
    //             if(resp.status == 'Success'){
    //                 $("#variant_response span").html('');
    //                 var response = resp.data;
    //                 if(response.variant != ''){
    //                     if(vendor_type == 'rental'){
    //                         // $('.incremental_hrs').val(0);
    //                         // $('.base_hours_min').val();
    //                         $('.incremental_hrs').val(0);
    //                         $('#incremental_hrs_hidden').val(base_hours_min);
    //                         $('.incremental-left-minus').click();
    //                         //$('#blocktime, #blocktime2').change();
    //                     }
    //                     // if(additionalPreference != 0){
    //                     //     response.variant.productPrice = token_currency * response.variant.productPrice;
    //                     // }
    //                     $('#product_variant_wrapper').html('');
    //                     let variant_template = _.template($('#variant_template').html());
    //                     response.variant.productPrice = (parseFloat(checkAddOnPrice()) + parseFloat(response.variant.productPrice)).toFixed(digit_count);
    //                     response.variant.compare_at_price = (parseFloat(checkAddOnPrice()) + parseFloat(response.variant.compare_at_price)).toFixed(digit_count);
    //                     $("#product_variant_wrapper").append(variant_template({ Helper: NumberFormatHelper, variant:response.variant, tokenAmount: response.tokenAmount, is_token_enable: response.is_token_enable}));
    //                     $('#product_variant_quantity_wrapper').html('');
    //                     let variant_quantity_template = _.template($('#variant_quantity_template').html());
    //                     $("#product_variant_quantity_wrapper").append(variant_quantity_template({variant:response.variant}));
    //                     // console.log(response.variant.quantity);
    //                     if(!response.is_available){
    //                         $(".addToCart, #addon-table").hide();
    //                     }else{
    //                         $(".addToCart, #addon-table").show();
    //                     }
    //                     let variant_image_template = _.template($('#variant_image_template').html());
    //                     $(".product__carousel .gallery-parent").html('');
    //                     $(".product__carousel .gallery-parent").append(variant_image_template({variant:response.variant}));
    //                     // easyZoomInitialize();
    //                     // $('.easyzoom').easyZoom();

    //                     if(response.variant.media != ''){
    //                         $(".product-slick").slick({ slidesToShow: 1, slidesToScroll: 1, arrows: !0, fade: !0, asNavFor: ".slider-nav" });
    //                         $(".slider-nav").slick({ vertical: !1, slidesToShow: 3, slidesToScroll: 1, asNavFor: ".product-slick", arrows: !1, dots: !1, focusOnSelect: !0 });
    //                     }
    //                 }
    //             }else{
    //                 $("#variant_response span").html(resp.message);
    //                 $(".addToCart, #addon-table").hide();
    //             }
    //         },
    //         error: function(data) {

    //         },
    //     });
    // }

    function checkAddOnPrice()
    {
        price  = 0;
        $('.productDetailAddonOption').each(function(){
            if($(this).prop('checked') == true){
                var cp = $(this).data('price');
                price = price + parseFloat(cp);
            }
        });
        return price;
    }
</script>
<script>
    var addonids = [];
    var addonoptids = [];
    $(function() {
        $(".productDetailAddonOption").click(function(e) {
           // var addon_elem = $(this).closest('tr');
            var addon_elem = $(this).parents('.productAddonSetOptions');

            var addon_minlimit = addon_elem.data('min');
            var addon_maxlimit = addon_elem.data('max');
            if(addon_elem.find(".productDetailAddonOption:checked").length > addon_maxlimit) {
                this.checked = false;
            }else{
                var addonId = $(this).attr("addonId");
                var addonOptId = $(this).attr("addonOptId");
                if ($(this).is(":checked")) {
                    addonids.push(addonId);
                    addonoptids.push(addonOptId);
                } else {
                    addonids.splice(addonids.indexOf(addonId), 1);
                    addonoptids.splice(addonoptids.indexOf(addonOptId), 1);
                }
                if($('.changeVariant').length > 0)
                {
                    updatePrice();
                }else{
                    addOnPrice = parseFloat(checkAddOnPrice());
                    org_price = parseFloat($(this).data('original_price')) + addOnPrice;
                    fixed_price = parseFloat($(this).data('fixed_price')) + addOnPrice;
                    if(is_token_currency_enable > 0){
                        org_price = token_currency * org_price;
                        fixed_price = token_currency * fixed_price;
                    }
                    $('.product_fixed_price').html(fixed_price.toFixed(digit_count));
                    $('.product_original_price').html(org_price.toFixed(digit_count));
                }
            }
        });
    });
</script>

<!-----  rating product if delivered -->

<script type="text/javascript">
    $(document).ready(function(e) {
        $('.rating-star-click').click(function() {
            $('.rating_files').show();
            $('.form-row').show();
            $('#product_rating').modal('show');
        });
        $('body').on('click', '.add_edit_review', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.get('/rating/get-product-rating?id=' + id, function(markup) {
                $('#product_rating').modal('show');
                $('#review-rating-form-modal').html(markup);
            });
        });


        var enableDates = {!! $productAvailability !!};



        if (typeof enableDates === 'string') {
            enableDates = enableDates.split(',').map(function(dateString) {
                return dateString.trim();
            });
        }
        $("#range-datepicker").flatpickr({
                dateFormat: "Y-m-d",
                mode: "range",
                enable : enableDates,
                onChange: function (selectedDates, dateStr, instance) {
                    // Update the summary-data template
                    updateSummary(selectedDates);
                }
            });
            // Function to update summary data
            function updateSummary(selectedDates) {
                const startDate = selectedDates[0];
                const endDate = selectedDates[selectedDates.length - 1];
                const days = Math.round((endDate - startDate) / (24 * 60 * 60 * 1000)) + 1;



                let dailyRate;
                if (days < 7) {
                    dailyRate = {{$product->variant[0]->price}};
                } else if (days >= 7 && days < 30) {
                    dailyRate = {{$product->variant[0]->week_price ?? 0 }};
                } else {
                    dailyRate = {{$product->variant[0]->month_price ?? 0 }};
                }
                const totalAmount = days * dailyRate ;


                // Update values in the template
                $(".summary-box").show();
                $(".days-count").text(days);
                $(".applied-total-amount").text(showCurrencySymbol(days * dailyRate));
                $(".applied-price").text(showCurrencySymbol(dailyRate));
                $(".date-range").text(startDate.toDateString() + " - " + endDate.toDateString());
                $(".total-amount").text(showCurrencySymbol(totalAmount));
            }
            function showCurrencySymbol(amount){
                return "{{Session::get('currencySymbol')}}" + amount;
            }
    });

    $(document).ready(function() {
        $('.measurmentClick').on('click', function() {
            $('.measurmentDiv').toggle('5');
             // Toggle the icon
                var icon = $(this).find('i');
                if (icon.hasClass('fa-plus')) {
                    icon.removeClass('fa-plus').addClass('fa-minus');
                } else {
                    icon.removeClass('fa-minus').addClass('fa-plus');
                }
        });
    });

</script>


<script>
    var timeout= null;
    var width =250;
    var height = 250;
    function imageZoom(imgID, resultID) {
        var img, lens, result, cx, cy;
        img = document.getElementById(imgID);
        result = document.getElementById(resultID);
        /*create lens:*/
        lens = document.createElement("DIV");
        lens.setAttribute("class", "img-zoom-lens");
        /*insert lens:*/
        img.parentElement.insertBefore(lens, img);
        /*calculate the ratio between result DIV and lens:*/
        cx = result.offsetWidth / width;
        cy = result.offsetHeight / height;
        console.log(cx+4);
        /*set background properties for the result DIV:*/

        result.style.backgroundImage = "url('" + img.src + "')";
        result.style.backgroundSize = (img.width * cx) + "px " + (img.height * cy) + "px";
        /*execute a function when someone moves the cursor over the image, or the lens:*/
        lens.addEventListener("mousemove", moveLens);
        img.addEventListener("mousemove", moveLens);
        /*and also for touch screens:*/
        lens.addEventListener("touchmove", moveLens);
        img.addEventListener("touchmove", moveLens);
        function moveLens(e) {
          var pos, x, y;
          /*prevent any other actions that may occur when moving over the image:*/
          e.preventDefault();
          /*get the cursor's x and y positions:*/
          pos = getCursorPos(e);
          /*calculate the position of the lens:*/
          x = pos.x - (lens.offsetWidth / 2);
          y = pos.y - (lens.offsetHeight / 2);
          /*prevent the lens from being positioned outside the image:*/
          if (x > img.width - lens.offsetWidth) {x = img.width - width;}
          if (x < 0) {x = 0;}
          if (y > img.height - lens.offsetHeight) {y = img.height - height;}
          if (y < 0) {y = 0;}
          /*set the position of the lens:*/
          lens.style.left = x + "px";
          lens.style.top = y + "px";
          /*display what the lens "sees":*/
        //   console.log(x)
        //   console.log(y)
          result.style.backgroundPosition = "-" + (x * cx) + "px -" + (y * cy) + "px";
        }
        function getCursorPos(e) {
          var a, x = 0, y = 0;
          e = e || window.event;
          /*get the x and y positions of the image:*/
          a = img.getBoundingClientRect();
          /*calculate the cursor's x and y coordinates, relative to the image:*/
          x = e.pageX - a.left;
          y = e.pageY - a.top;
          /*consider any page scrolling:*/
          x = x - window.pageXOffset;
          y = y - window.pageYOffset;
          return {x : x, y : y};
        }
      }
        $('#main_image').mouseover(function() {
            var imageId = this.id;
            $('.img-zoom-lens').remove();
            $('.img-zoom-result').show();
            imageZoom(imageId, "myresult");
        });

        $('.myimage1').click(function(){
            var new_image = $(this).attr('src');
            $('#main_image').attr('src',new_image);
        })
        $('.exzoom_img_ul').mouseleave(() =>{
            $('.img-zoom-result').hide();
            $('.img-zoom-lens').remove();
        });

        $(".suggested-product").slick({
            infinite: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            responsive: [
            { breakpoint: 1199, settings: { slidesToShow: 3, slidesToScroll: 1, infinite: true, dots: false, centerMode: true, } },
            { breakpoint: 991, settings: { slidesToShow: 2, slidesToScroll: 1, dots: false, centerMode: true, } },
            { breakpoint: 767, settings: { slidesToShow: 1, slidesToScroll: 1, dots: false, centerMode: true, } },
            { breakpoint: 576, settings: { slidesToShow: 1, slidesToScroll: 1, dots: false, centerMode: true, centerPadding: '0', } }
        ]
        });




        $(document).ready(function() {
            $(".img_active").click(function(){

                $(".img_active").find('img').removeClass("active");
                $(this).find('img').addClass("active");
            });
        });


        $(document).on("click",".color_name",function(){
        	if($(this).hasClass("ellipsis")){
        		$(this).removeClass("ellipsis");
        	}else{
        		$(this).addClass("ellipsis");
        	}
        });

        function showLessTextFunction() {
            $("#show_product_text_less").attr("style", "display:none");
            $("#show_product_text_more").attr("style", "display:block");
        }

        function showMoreTextFunction() {
            $("#show_product_text_less").attr("style", "display:block");
            $("#show_product_text_more").attr("style", "display:none");
        }


        document.addEventListener("DOMContentLoaded", function() {
        var content = document.getElementById("productContent");
        var readMoreLink = document.getElementById("readMoreLink");
        var fullContent = <?= json_encode($fullContent ?? "") ?>;
        var isFullContentDisplayed = false;

    if (readMoreLink) { // Check if readMoreLink exists
        readMoreLink.addEventListener("click", function(e) {
            e.preventDefault();
            if (isFullContentDisplayed) {
                content.innerHTML = <?= json_encode($content ?? "") ?>;
                readMoreLink.innerText = "Read More";
            } else {
                content.innerHTML = fullContent;
                readMoreLink.innerText = "Read Less";
            }
            isFullContentDisplayed = !isFullContentDisplayed;
        });
    }
});

        </script>

@endsection

