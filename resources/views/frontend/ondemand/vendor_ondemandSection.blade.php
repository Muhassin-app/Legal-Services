@section('customcss')
<link defer type="text/css" href="{{asset('css/ondemand.css')}}" rel="stylesheet" id="bs-default-stylesheet" />
@endsection
@php
$add_to_cart =  route('addToCart') ;
$is_service_product_price_from_dispatch_forOnDemand = 0;
$additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch','is_service_price_selection']);
$getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
$category_type_idForNotShowshPlusMinus = ['12'];
if($getOnDemandPricingRule['is_price_from_freelancer']==1){
    $is_service_product_price_from_dispatch_forOnDemand =1;
    array_push($category_type_idForNotShowshPlusMinus,8);
}

@endphp
<section class="home-serivces" id="alSixHomeServices">
    <div class="container">
        <div class="row mb-lg-5 mb-md-4 mb-3">
            <div class="col-xl-12">
                <div class="step-indicator">

                    <div class="step step1 @if(app('request')->input('step') >= '1' || empty(app('request')->input('step'))) active @endif">
                        <div class="step-icon">1</div>
                        <p>{{__('Service Details')}}</p>
                    </div>

                    <div class="indicator-line  @if(app('request')->input('step') >= '1' && !empty(app('request')->input('step'))) active @endif"></div>

                    <div class="step step2  @if(app('request')->input('step') >= '2' && !empty(app('request')->input('step'))) active @endif">
                        <div class="step-icon">2</div>
                        <p>{{__('Date & Time')}}</p>
                    </div>

                    <div class="indicator-line  @if(app('request')->input('step') == '3' && !empty(app('request')->input('step'))) active @endif"></div>

                    <div class="step step3   @if(app('request')->input('step') == '3' && !empty(app('request')->input('step'))) active @endif">
                        <div class="step-icon">3</div>
                        <p>{{__('Payment')}}</p>
                    </div>

                </div>

                <div class="row mt-4">

                    <div class="col-md-8">
                        @if((app('request')->input('step') == '1' || empty(app('request')->input('step'))) && app('request')->input('addons') != 1)

                            @if(!empty($category->childs) && count($category->childs) > 0)
                            @foreach ($category->childs as $key => $childs)
                            @if($childs->type_id == 8)
                            <!-- Start Main Nav -->
                            <nav id='main-nav'>
                                <ul id='main-nav-list'>

                                            <li><a href="#section_set{{$key}}">{{ $childs['translation_name'] ?? ''}}</a></li>

                                </ul>
                            </nav>
                            <!-- End Main Nav -->
                            @endif
                            @endforeach
                            @endif


                        @endif

                        <div class="card-box">


                            @if(app('request')->input('step') == '1' || empty(app('request')->input('step')))
                                    @if(!empty($category->childs) && count($category->childs) > 0)
                                    <!-- static html -->

                                <!-- Start Conent Wrapper -->
                                <div id='main-wrapper'  class="@if(app('request')->input('addons') == 1) d-none @endif">
                                            @foreach ($category->childs as $key => $childs)
                                                @if(in_array($childs->type_id , [8,12]) )

                                                    <h4><b>{{ $childs->translation_name }}</b></h4>
                                                    <div class='' id='section_set{{$key}}'>
                                                        @if(!empty($childs))
                                                        <div class="service-img mb-3">
                                                            <img class="img-fluid" src="{{$childs->image['proxy_url'] . '1000/200' . $childs->image['image_path']}}" alt="">
                                                        </div>
                                                        @endif


                                                            @foreach ($childs->products as $data)

                                                            @php
                                                                $data->translation_title = (!empty($data->translation->first())) ? $data->translation->first()->title : $data->sku;
                                                                $data->translation_description = (!empty($data->translation->first())) ? $data->translation->first()->body_html : $data->sku;
                                                                $data->variant_multiplier = (!empty($clientCurrency)) ? $clientCurrency->doller_compare : 1;
                                                                $data->variant_price = (!empty($data->variant->first())) ? $data->variant->first()->price : 0;
                                                                $productInquiryCheck = $data->inquiry_only  ; 
                                                                $redirec = ($data->is_recurring_booking ==1) ? route('productDetail', [@$data->vendor->slug, $data->url_slug]) : 'javascript:void(0)' ;
                                                                $class = ($data->is_recurring_booking ==1) ? 'add_on_demand_btn' : 'add_on_demand' ;
                                                            @endphp

                                                            <div class="row classes_wrapper no-gutters align-items-center" href="#">
                                                                <div class="col-md-9 col-sm-8 pr-md-2 ">
                                                                    <div class="alSixHomeServiceSteps">
                                                                        <h5 class="mb-1"><b>{!! (!empty($data->translation->first())) ? $data->translation->first()->title : $data->sku !!}</b></h5>
                                                                        <p class="mb-1">{!! (!empty($data->translation->first())) ? $data->translation->first()->body_html : $data->sku !!}</p>
                                                                    </div>
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        @if($productInquiryCheck == 0) {{-- start productInquiryCheck --}}
                                                                        <h5 class="my-sm-0 my-3">
                                                                            @if($is_service_product_price_from_dispatch_forOnDemand !=1)  
                                                                            {{Session::get('currencySymbol').(decimal_format($data->variant_price * $data->variant_multiplier))}}
                                                                            @endif
                                                                        </h5>


                                                                        @if(isset($data->variant[0]->checkIfInCart) && count($data->variant[0]->checkIfInCart) > 0)
                                                                        @php
                                                                            $cartcount = 1;
                                                                        @endphp
                                                                            @if(   $is_service_product_price_from_dispatch_forOnDemand ==1)
                                                                                <a class="btn btn-solid btn btn-solid view_on_demand_price"  style="display:none;" id="add_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                            @else
                                                                                <a class="btn btn-solid {{  $class }}" style="display:none;" id="add_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="{{  $redirec }}">Add <i class="fa fa-plus"></i></a>
                                                                            @endif
                                                                            @if(
                                                                                isset($data->category_type_id) && 
                                                                                ( 
                                                                                    (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)) 
                                                                                )  
                                                                            )
                                                                                <div class="number" id="show_plus_minus{{$data->variant[0]->checkIfInCart['0']['id']}}">
                                                                                    <span class="minus qty-minus-ondemand"  data-parent_div_id="show_plus_minus{{$data->variant[0]->checkIfInCart['0']['id']}}" data-id="{{$data->variant[0]->checkIfInCart['0']['id']}}" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                                                                    </span>
                                                                                    <input style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;" placeholder="1" type="text" value="{{$data->variant[0]->checkIfInCart['0']['quantity']}}" class="input-number" step="0.01" id="quantity_ondemand_{{$data->variant[0]->checkIfInCart['0']['id']}}" readonly>
                                                                                    <span class="plus qty-plus-ondemand"  data-id="{{$data->variant[0]->checkIfInCart['0']['id']}}" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                                                                    </span>
                                                                                </div>
                                                                            @else
                                                                                <a class="btn btn-solid " id="added_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                            @endif
                                                                        @else
                                                                        <a class="btn btn-solid {{  $class }}" id="aadd_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="{{ $redirec }}">Add <i class="fa fa-plus"></i></a>
                                                                        <div class="number" style="display:none;" id="ashow_plus_minus{{$data->id}}">
                                                                            <span class="minus qty-minus-ondemand"  data-parent_div_id="show_plus_minus{{$data->id}}" readonly data-id="{{$data->id}}" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                                                            </span>
                                                                            <input style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;" id="quantity_ondemand_d{{$data->id}}" readonly placeholder="1" type="text" value="1" class="input-number input_qty" step="0.01">
                                                                            <span class="plus qty-plus-ondemand"  data-id="" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                                                            </span>
                                                                        </div>

                                                                        @endif
                                                                        @endif   {{-- end productInquiryCheck --}}

                                                                      
                                                                    </div>
                                                                </div>




                                                                <div class="col-md-3 col-sm-4 mb-sm-0 mb-3">
                                                                    <?php $imagePath = $imagePath2 = '';
                                                                        $mediaCount = count($data->media);
                                                                        for ($i = 0; $i < $mediaCount && $i < 2; $i++) {
                                                                            if($i == 0){
                                                                                $imagePath = $data->media[$i]->image->path['proxy_url'].'300/300'.$data->media[$i]->image->path['image_path'];
                                                                            }
                                                                            $imagePath2 = $data->media[$i]->image->path['proxy_url'].'300/300'.$data->media[$i]->image->path['image_path'];
                                                                        } ?>
                                                                    
                                                                    <div class="class_img">
                                                                        @if($imagePath != '') 
                                                                        <img src="{{$imagePath}}" alt="">
                                                                        @else
                                                                        
                                                                        @endif
                                                                    </div>
                                                                </div>


                                                            </div>
                                                            <hr>
                                                            @endforeach

                                                    </div>
                                                @endif
                                            @endforeach

                                </div>
                                <!-- End Content Wrapper -->
                                    <!-- end statis html -->
                                @endif
                            @endif


                            @if(app('request')->input('step') == '1' || empty(app('request')->input('step')))
                                <!-- for single level category -->
                                <div class="service-data-wrapper al @if(app('request')->input('addons') == 1) d-none @endif"  id="step-1-ondemand" >
                                        <!--  service data -->
                                    <div class="service-data">
                                        <h4><b>{{ $vendor->name }}</b></h4>

                                        @if (!empty($vendor->banner))
                                            <div class="service-img mb-3">
                                                <img class="img-fluid" src="{{$vendor->banner['proxy_url'] . '1000/300' . $vendor->banner['image_path']}}" alt="">
                                            </div>
                                        @endif


                                        @forelse($listData as $key => $data)
                                            <!-- scrolling_section -->
                                            <section class="scrolling_section " id="{{ $data->category->slug }}">
                                                    <!-- viewAllProductSec -->
                                                    <div class="viewAllProductSec" >

                                                        @forelse($data->products as $prod)

                                                            <div class="card mb-3 product_row"  data-p_sku="{{ $prod->sku }}"
                                                                data-slug="{{ $prod->url_slug }}">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-center justify-content-between border-bottom">
                                                                        <p class="m-0 productTitle"> {{ $prod->translation_title }}</p>
                                                                        <ul class="m-0 p-0 d-flex align-items-center">
                                                                            <li>{{ __('From') }}</li>

                                                                            <li class="ml-2">
                                                                                @if($is_service_product_price_from_dispatch_forOnDemand !=1)
                                                                                <span class="productsPrice">
                                                                                    {{ Session::get('currencySymbol') . decimal_format($prod->variant_price * $prod->variant_multiplier,',') }}
                                                                                    @if ($prod->variant[0]->compare_at_price > 0)
                                                                                        <span
                                                                                            class="org_price ml-1 font-14">{{ Session::get('currencySymbol') .decimal_format($prod->variant[0]->compare_at_price * $prod->variant_multiplier) }}</span>
                                                                                    @endif
                                                                                </span>
                                                                                @endif
                                                                                <br> <sup>{{ __('per person') }} {{ __('min ') . $prod->minimum_duration_min }}</sup></li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="productDetails pl-0 pr-lg-5 m-0 position-relative">
                                                                        <p class="position-relative px-3 py-2">{!! $prod->translation_description !!} </p>
                                                                    </div>
                                                                    {{-- <div class=" pt-3 m-0 position-relative row">
                                                                        <div class="col-md-9 col-sm-8 pr-md-2 productDetails">
                                                                        <p class="position-relative px-3 py-2">{!! $prod->translation_description !!} </p>
                                                                        </div>
                                                                        <div class="col-md-3 col-sm-4 mb-sm-0 mb-3">
                                                                         
                                                                            <?php $imagePath = $imagePath2 = '';
                                                                            $mediaCount = count($prod->media);
                                                                            for ($i = 0; $i < $mediaCount && $i < 2; $i++) {
                                                                                if($i == 0){
                                                                                    $imagePath = $prod->media[$i]->image->path['proxy_url'].'300/300'.$prod->media[$i]->image->path['image_path'];
                                                                                }
                                                                                $imagePath2 = $prod->media[$i]->image->path['proxy_url'].'300/300'.$prod->media[$i]->image->path['image_path'];
                                                                            } ?>
                                                                        <div class="class_img">
                                                                            @if($imagePath != '')
                                                                            <img src="{{$imagePath}}" alt="">
                                                                            @else
    
                                                                            @endif
    
                                                                        </div>
                                                                    </div>
                                                                </div> --}}
                                                                    {{-- <ul class="productDetails pl-0 pr-lg-5 m-0 position-relative">
                                                                        <li class="position-relative px-3 py-2">
                                                                            <p class="m-0">One night bed and breakfast</p>
                                                                        </li>
                                                                        <li class="position-relative px-3 py-2">
                                                                            <p class="m-0">Use of the leisure facilities</p>
                                                                        </li>
                                                                        <li class="position-relative px-3 py-2">
                                                                            <p class="m-0">Bottle of sparkling wine</p>
                                                                        </li>
                                                                        <li class="position-relative px-3 py-2">
                                                                            <p class="m-0">Robe, towel and slippers provided</p>
                                                                        </li>
                                                                    </ul> --}}
                                                                    {{-- <ul class="p-0 m-0 productBookingBtns position-relative d-flex align-items-center justify-content-between">
                                                                        <li><a href="#"><img src="{{asset('frontend/template_six/spaimages/flash_on.svg') }}"> Instant book</a></li>
                                                                        <li class="d-flex align-items-center"><button class="alProductBtns mr-2">More details and book</button> <button class="alProductBtns outlineBtn">Buy as a gift</button></li>
                                                                    </ul> --}}
                                                                    <div class="productBookingBtns product_variant_quantity_wrapper">
                                                                        @php
                                                                            $data = $prod;
                                                                           
                                                                            $productVariantInCart = 0;
                                                                            $productVariantIdInCart = 0;
                                                                            $productVariantInCartWithDifferentAddons = 0;
                                                                            $cartProductId = 0;
                                                                            $cart_id = 0;
                                                                            $vendor_id = 0;
                                                                            $product_id = $data->id;
                                                                            $variant_id = $data->variant[0] ? $data->variant[0]->id : 0;
                                                                            $variant_price = 0;
                                                                            $variant_quantity = $prod->variant_quantity;
                                                                            $isAddonExist = 0;
                                                                            $minimum_order_count = $data->minimum_order_count == 0 ? 1 : $data->minimum_order_count;
                                                                            $batch_count = $data->batch_count;
                                                                            if (count($data->addOn) > 0) {
                                                                                $isAddonExist = 1;
                                                                            }
                                                                            $productInquiryCheck = $data->inquiry_only  ; 
                                                                            $redirec = ($data->is_recurring_booking ==1) ? route('productDetail', [@$data->vendor->slug, $data->url_slug]) : 'javascript:void(0)' ;
                                                                             $class = ($data->is_recurring_booking ==1) ? 'add_on_demand_btn' : 'add_on_demand' ;
                                                                        @endphp

                                                                        @foreach ($data->variant as $var)
                                                                            @if ( isset($var->checkIfInCart) && count($var->checkIfInCart) > 0)
                                                                                @php
                                                                                    $productVariantInCart = 1;
                                                                                    $productVariantIdInCart = $var->checkIfInCart['0']['variant_id'];
                                                                                    $cartProductId = $var->checkIfInCart['0']['id'];
                                                                                    $cart_id = $var->checkIfInCart['0']['cart_id'];
                                                                                    // $variant_quantity = $var->checkIfInCart['0']['quantity'];
                                                                                    $variant_quantity = 0;
                                                                                    $vendor_id = $data->vendor_id;
                                                                                    $product_id = $data->id;
                                                                                    $batch_count = $data->batch_count;
                                                                                    $variant_price = decimal_format($var->price * $data->variant_multiplier);
                                                                                    if (count($var->checkIfInCart) > 1) {
                                                                                        $productVariantInCartWithDifferentAddons = 1;
                                                                                    }
                                                                                    foreach ($var->checkIfInCart as $cartVar) {
                                                                                        $variant_quantity = $variant_quantity + $cartVar['quantity'];
                                                                                    }
                                                                                @endphp
                                                                            @break

                                                                            ;
                                                                        @endif
                                                                    @endforeach
                                                                    @if($productInquiryCheck  ==0)     

                                                                        @if (( $is_service_product_price_from_dispatch_forOnDemand ==1) || ( $vendor->is_vendor_closed == 0 || ($vendor->closed_store_order_scheduled != 0 && $checkSlot != 0)))
                                                                            @php
                                                                                $is_customizable = false;
                                                                                if ($isAddonExist > 0 && ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1)) {
                                                                                    $is_customizable = true;
                                                                                }
                                                                            @endphp

                                                                            @if ( $productVariantInCart > 0)
                                                                                @if(   $is_service_product_price_from_dispatch_forOnDemand ==1)


                                                                                  
                                                                                <a class="btn btn-solid btn btn-solid view_on_demand_price"  style="display:none;" id="add_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                                @else
                                                                                    {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a>  alProductBtns--}}
                                                                                    <a class="add-cart-btn  btn btn-solid {{$class}} "
                                                                                        style="display:none;"
                                                                                        id="add_button_href{{ $cartProductId }}"
                                                                                        data-variant_id="{{ $productVariantIdInCart }}"
                                                                                        data-add_to_cart_url="{{ $add_to_cart }}"
                                                                                        data-vendor_id="{{ $vendor_id }}"
                                                                                        data-product_id="{{ $product_id }}"
                                                                                        data-aaddon="{{ $isAddonExist }}"
                                                                                        data-minimum_order_count="{{ $minimum_order_count }}"
                                                                                        data-batch_count="{{ $batch_count }}"
                                                                                        href="{{ $redirec }}">{{ __('Add') }}
                                                                                        @if ($minimum_order_count > 0)
                                                                                            ({{ $minimum_order_count }})
                                                                                        @endif
                                                                                    </a>
                                                                            @endif

                                                                                @if(
                                                                                    isset($data->category_type_id) && 
                                                                                    ( 
                                                                                        (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)) 
                                                                                    )  
                                                                                )
                                                                                <div class="number"
                                                                                    id="first {{ $data->category_type_id }} show_plus_minus{{ $cartProductId }}">
                                                                                    <span
                                                                                        class="minus qty-minus-product  {{ $productVariantInCartWithDifferentAddons ? 'remove-customize' : '' }}"
                                                                                        data-variant_id="{{ $productVariantIdInCart }}"
                                                                                        data-parent_div_id="show_plus_minus{{ $cartProductId }}"
                                                                                        data-id="{{ $cartProductId }}"
                                                                                        data-base_price="{{ $variant_price }}"
                                                                                        data-vendor_id="{{ $vendor_id }}"
                                                                                        data-product_id="{{ $product_id }}"
                                                                                        data-cart="{{ $cart_id }}"
                                                                                        data-aaddon="{{ $isAddonExist }}"
                                                                                        data-minimum_order_count="{{ $minimum_order_count }}"
                                                                                        data-batch_count="{{ $batch_count }}">
                                                                                        <i class="fa fa-minus"
                                                                                            aria-hidden="true"></i>
                                                                                    </span>
                                                                                    <input
                                                                                        style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                                                        placeholder="1" type="text"
                                                                                        value="{{ $variant_quantity }}"
                                                                                        class="input-number"
                                                                                        id="quantity_ondemand_{{ $cartProductId }}"
                                                                                        readonly>
                                                                                    <span
                                                                                        class="plus qty-plus-product {{ $is_customizable ? 'repeat-customize' : '' }}"
                                                                                        data-variant_id="{{ $productVariantIdInCart }}"
                                                                                        data-id="{{ $cartProductId }}"
                                                                                        data-base_price="{{ $variant_price }}"
                                                                                        data-vendor_id="{{ $vendor_id }}"
                                                                                        data-product_id="{{ $product_id }}"
                                                                                        data-cart="{{ $cart_id }}"
                                                                                        data-aaddon="{{ $isAddonExist }}"
                                                                                        data-batch_count="{{ $batch_count }}">
                                                                                        <i class="fa fa-plus"
                                                                                            aria-hidden="true"></i>
                                                                                    </span>
                                                                                </div>
                                                                                @else
                                                                                <a class="btn btn-solid " id="added_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                                @endif
                                                                            @else
                                                                            
                                                                                {{-- not need to check quantity in case of on demand and appointment --}}
                                                                                {{-- @if ( ($variant_quantity > 0 || $prod->sell_when_out_of_stock == 1)) --}} 

                                                                                    @if(   $is_service_product_price_from_dispatch_forOnDemand ==1)
                                                                                    
                                                                                        <a class="btn btn-solid btn btn-solid view_on_demand_price"  id="add_button_href{{$data->id }}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                                    @else
                                                                                    {{-- <a class="add_vendor-fav" href="#"><i class="fa fa-heart"></i></a> --}}
                                                                                        <a class="add-cart-btn btn btn-solid  {{$class}}"
                                                                                            id="aadd_button_href{{ $data->id }}"
                                                                                            data-variant_id="{{ $data->variant[0]->id }}"
                                                                                            data-add_to_cart_url="{{ $add_to_cart }}"
                                                                                            data-vendor_id="{{ $data->vendor_id }}"
                                                                                            data-product_id="{{ $data->id }}"
                                                                                            data-aaddon="{{ $isAddonExist }}"
                                                                                            data-batch_count="{{ $batch_count }}"
                                                                                            data-minimum_order_count="{{ $minimum_order_count }}"
                                                                                            href="{{ $redirec }}">{{ __('Add') }}
                                                                                            @if ($minimum_order_count > 1)
                                                                                                ({{ $minimum_order_count }})
                                                                                            @endif
                                                                                        </a>
                                                                                    @endif

                                                                                    @if(
                                                                                            isset($data->category_type_id) && 
                                                                                            ( 
                                                                                                (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)) 
                                                                                            )  
                                                                                        )
                                                                                        <div class="number"
                                                                                            style="display:none;"
                                                                                            id="ashow_plus_minus{{ $data->id }}">
                                                                                            <span
                                                                                                class="minus qty-minus-product"
                                                                                                data-parent_div_id="show_plus_minus{{ $data->id }}"
                                                                                                data-id="{{ $data->id }}"
                                                                                                data-base_price="{{ decimal_format($data->variant_price * $data->variant_multiplier) }}"
                                                                                                data-vendor_id="{{ $data->vendor_id }}"
                                                                                                data-batch_count="{{ $batch_count }}"
                                                                                                data-minimum_order_count="{{ $minimum_order_count }}">
                                                                                                <i class="fa fa-minus"
                                                                                                    aria-hidden="true"></i>
                                                                                            </span>
                                                                                            <input
                                                                                                style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;"
                                                                                                id="quantity_ondemand_d{{ $data->id }}"
                                                                                                readonly
                                                                                                placeholder="{{ $minimum_order_count }}"
                                                                                                type="text"
                                                                                                value="{{ $minimum_order_count }}"
                                                                                                class="input-number input_qty"
                                                                                                step="0.01">
                                                                                            <span
                                                                                                class="plus qty-plus-product"
                                                                                                data-id=""
                                                                                                data-base_price="{{ decimal_format($data->variant_price * $data->variant_multiplier) }}"
                                                                                                data-vendor_id="{{ $data->vendor_id }}"
                                                                                                data-batch_count="{{ $batch_count }}"
                                                                                                data-minimum_order_count="{{ $minimum_order_count }}">
                                                                                                <i class="fa fa-plus"
                                                                                                    aria-hidden="true"></i>
                                                                                            </span>
                                                                                        </div>
                                                                                    @else
                                                                                        <a class="btn btn-solid "  style="display:none;" id="added_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                                    @endif

                                                                                {{-- @else
                                                                                    <span class="text-danger asd">{{ __('Out of stock') }}</span>
                                                                                @endif --}}
                                                                        
                                                                            @endif

                                                                            {{-- @if ($is_customizable)
                                                                                <div class="customizable-text">
                                                                                    {{ __('customizable') }}
                                                                                </div>
                                                                            @endif --}}
                                                                        @endif
                                                                    @endif    
                                                                </div>

                                                                </div>
                                                            </div>
                                                        @empty
                                                            <h4 class="mt-3 mb-3 text-center">No product found</h4>
                                                            @endforelse
                                                    </div>
                                                        <!-- end viewAllProductSec -->
                                            </section>
                                                <!-- end scrolling_section -->
                                        @empty
                                            <h4 class="mt-3 mb-3 text-center">No product found</h4>
                                        @endforelse



                                    </div>
                                    <!-- end service data -->
                                    <!-- end single level category -->
                                </div>
                            @endif



                            @if(app('request')->input('addons') == '1' && app('request')->input('dateset') != '1')
                                 <!-- Step if addons avilable  Html -->

                             <div id="step-4-ondemand">
                                @foreach ($cartData as $key => $cart_data)
                                    <!-- show add ons -->
                                     @if(!empty($cart_data->product->addOn) && $cart_data->product->addOn->count() > 0)
                                        <div class="border-product" id="addon_div{{$cart_data->id}}">
                                            <h4 class="mb-2"><b>{!! (!empty($cart_data->product->translation->first())) ? $cart_data->product->translation->first()->title : $cart_data->product->sku !!}</b></h4>
                                            @foreach($cart_data->product->addOn as $row => $addon)
                                            <div class="add-on-main-div">
                                                <h6 class="product-title">{{ $addon->addOnName->translation_one->title }}
                                                        @php
                                                            $min_select = '';
                                                            if($addon->addOnName->min_select > 0){
                                                                $min_select = 'Minimum '.$addon->addOnName->min_select;
                                                            }
                                                            $max_select = '';
                                                            $type_input = 'checkbox';
                                                            if($addon->addOnName->max_select > 0){
                                                                $max_select = 'Maximum '.$addon->addOnName->max_select;

                                                                if($addon->addOnName->max_select > 1)
                                                                $type_input = 'checkbox';
                                                                else
                                                                $type_input = 'radio';
                                                            }
                                                            if( ($min_select != '') && ($max_select != '') ){
                                                                $min_select = $min_select.' and ';
                                                            }
                                                        @endphp
                                                </h6>
                                                <span class="productAddonSetOptions" data-min="{{$addon->addOnName->min_select}}" data-cart_id="{{$cart_data->cart_id}}" data-cart_product_id="{{$cart_data->id}}" data-max="{{$addon->addOnName->max_select}}" data-addonset-title="{{$addon->addOnName->title}}">

                                                <div class="booking-time radio-btns long-radio mb-0">
                                                        @foreach($addon->setoptions as $k => $option)
                                                            @php $checked = ''; @endphp
                                                            @foreach ($cart_data->addon as $value)
                                                               @if($checked != 'checked')
                                                                    @if($addon->addon_id == $value->addon_id && $value->option_id == $option->id  && $value->cart_product_id  == $cart_data->id)
                                                                    @php $checked = 'checked'; @endphp
                                                                    @else
                                                                    @php $checked = ''; @endphp
                                                                    @endif
                                                               @endif

                                                            @endforeach
                                                            <div>
                                                                <div class="radios">
                                                                <input type="{{$type_input}}" class="productAddonOption " {{ $checked }} id="inlineCheckbox_{{$key}}{{$row.'_'.$k}}"  class="productAddonOption"  name="addonData{{$row}}[{{$cart_data->id}}][]" addonId="{{$addon->addon_id}}" addonOptId="{{$option->id}}"/>
                                                                    <label for='inlineCheckbox_{{$key}}{{$row.'_'.$k}}'>
                                                                        <span class="customCheckbox productAddonOptionspan_{{ $checked }}" aria-hidden="true">{{$option->translation_one->title .' ('.Session::get('currencySymbol').decimal_format($option->price,',').')' }} </span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                </div>
                                                </span>
                                            </div>
                                        @endforeach
                                        <hr>
                                        </div>

                                     @endif
                                     <!-- end show add ons -->

                                @endforeach
                              </div>
                              <!--Step if addons avilable  Html -->
                            @endif



                            @if(app('request')->input('step') == '2')
                              <!-- Step Two Html -->
                                <div id="step-2-ondemand">
                                   @php
                                   $lastKey = count($cartData) - 1;
                                   $last_cart_product_id = '';
                                   @endphp
                                   {{ Arr::last($cartData)}}
                                    @foreach ($cartData as $cd => $cart_data)
                                        @php
                                        $last_cart_product_id =  $cart_data->id
                                        @endphp
                                        {{-- && (count($cart_data->timeSlots)) > 0 --}}
                                      @if(!empty($cart_data->product->mode_of_service) && ($cart_data->product->mode_of_service == 'schedule')  )

                                        @php
                                            $productDate = trim(date('Y-m-d', strtotime($cart_data->scheduled_date_time)));
                                        @endphp


                                        <div  id="date_time_set_div{{$cart_data->id}}" class="booking_date_section">
                                            {{-- <div  id="date_time_set_div{{$cart_data->id}}" @if(count($cartData)>1 && ($cd !=  $lastKey)) style="pointer-events:none" @endif> --}}

                                            <h4 class="mb-2"><b>{{__('When would you like your service?')}}</b></h4>
                                            <div class="date-items radio-btns hide">
                                                @foreach ($period as $key => $date)
                                                    <div>
                                                        @php
                                                        $checked = '';
                                                        $singleDate =  trim(date('Y-m-d', strtotime($date)));
                                                        if($productDate == $singleDate && !empty($productDate)){
                                                            $checked = "checked";
                                                        }
                                                        @endphp
                                                        <div class="radios {{ $checked }}" >
                                                            <p>{{date('D', strtotime($date))}}</p>
                                                            <div class="alCustomHomeServiceRadio">
                                                                <input type="radio" class="check-time-slots ondemand-time-slots  ondemand_{{$checked }}" data-product_vendor_id="{{$cart_data->vendor_id}}" data-cart_product_id = "{{$cart_data->id}}" value='{{date('Y-m-d', strtotime($date))}}' name='booking_date' id='radio{{$cd}}{{$key}}' {{$checked }} @if(($key == 0 && $checked == "")) checked @endif />
                                                                <label for='radio{{$cd}}{{$key}}'>
                                                                <span class="customCheckbox" aria-hidden="true" >{{date('d', strtotime($date))}}</span>
                                                                </label>
                                                                <input type="hidden" name="productid" id="productid" value="{{$cart_data->id}}" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($cart_data->is_dispatch_slot == 1)
                                                @php
                                            
                                                $dispatch_agents = $cart_data->dispatchAgents ?? [];
                                                $cart_product_id = $cart_data->id;
                                              
                                                $show_dispatcher_agent = @$cart_data->product->is_show_dispatcher_agent;
                                                $selected_agent_id = @$cart_data->dispatch_agent_id;
                                                $schedule_slot = $cart_data->schedule_slot;
                                                
                                                @endphp
                                                <div class="booking-time-wrapper" id="show-all-time-slots{{$cart_data->id}}" style="@if($cart_data->schedule_slot != '')  @else display: none; @endif ">
                                                    @include('frontend.ondemand.dispatcher_agent_slots')
                                                </div>
                                            @else
                                                @php
                                                $time_slots = [];
                                                $cart_product_id = $cart_data->id;
                                                $schedule_slot = $cart_data->schedule_slot;
                                                if(!empty($cart_data->timeSlots)){
                                                    $time_slots = $cart_data->timeSlots;
                                                }
                                                @endphp
                                                <div class="booking-time-wrapper" id="show-all-time-slots{{$cart_data->id}}" ">
                                                    {{-- style="@if($cart_data->schedule_slot != '')  @else display: none; @endif   --}}
                                                    @include('frontend.ondemand.time-slots-for-date')
                                                </div>
                                                {{-- <div class="booking-time-wrapper" id="show-all-time-slots{{$cart_data->id}}" style="@if($cart_data->schedule_slot != '')  @else display: none; @endif ">
                                                    <h4 class="mt-4 mb-2"><b>{{__('What time would you like us to start?')}}</b></h4>

                                                    <div class="booking-time radio-btns long-radio mb-0">
                                                        @php
                                                        if(!empty($cart_data->timeSlots)){
                                                            $time_slots = $cart_data->timeSlots;
                                                        }
                                                        @endphp
                                                        @foreach ($time_slots as $key => $date)
                                                        @if($key+1 < count($time_slots))
                                                        @php
                                                        $checked='';
                                                            $slotTime = $date.' - '.@$time_slots[$key+1];
                                                            if(isset($cart_data->schedule_slot) && $cart_data->schedule_slot == $slotTime){
                                                                echo $checked="checked";
                                                            }
                                                        @endphp
                                                        <div>
                                                            <div class="radios">
                                                                <div class="alCustomHomeServiceRadio">
                                                                    <input type="radio" value='{{$date}} - {{@$time_slots[$key+1]}}' name='booking_time' id='time{{$cart_data->id}}{{$key+1}}' @if($checked) {{$checked}} @endif class="ondemand_{{$checked }}"/>
                                                                    <label for='time{{$cart_data->id}}{{$key+1}}'>
                                                                        <span class="customCheckbox selected-time" aria-hidden="true"  data-value='{{$date}} - {{@$time_slots[$key+1]}}' data-cart_product_id='{{$cart_data->id}}'>{{$date}} - {{@$time_slots[$key+1]}}</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @endforeach
                                                    </div>
                                                    <P id="message_of_time{{$cart_data->id}}"></P>
                                                </div> --}}
                                            @endif

                                            <input type="hidden" class="custom-control-input check" id="taskschedule" name="task_type" value="schedule" checked>
                                        </div>
                                        <hr>
                                        @endif
                                    @endforeach

                                        {{-- <div class="booking-time-wrapper">
                                            <input type="hidden" id="last_cart_product_id" value="{{ $last_cart_product_id }}">
                                            <h4 class="mt-4 mb-2"><b>{{__('Do you have any specific instructions?')}}</b></h4>
                                            <textarea class="form-control" name="specific_instructions" id="specific_instructions" cols="30" rows="7"></textarea>
                                        </div> --}}

                                </div>
                                 <!--end step 2 html -->
                            @endif


                            @if(app('request')->input('step') == '3')
                            <!-- step 3 payment page -->
                            <form method="post" action="" id="placeorder_form_ondemand">
                                    @csrf
                                    <div class="card-box">
                                        <div class="row d-flex justify-space-around">
                                            @if(!$guest_user)
                                                <div class="col-lg-8 left_box">

                                                </div>
                                            @endif

                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-sm-6 text-md-right">
                                                <button id="order_placed_btn" class="btn btn-solid d-none" type="button" {{$addresses->count() == 0 ? 'disabled': ''}}>{{__('Continue')}}</button>
                                            </div>
                                        </div>
                                    </div>

                                </form>

                                <div class="col-sm-6 text-md-right">
                                    <button id="order_placed_btn" class="btn btn-solid d-none" type="button" {{$addresses->count() == 0 ? 'disabled': ''}}>{{__('Continue')}}</button>
                                </div>

                            @endif


                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card-box">
                            <div class="product-order">
                                <div class="total-sec border-0 py-0 my-0">
                                    {{-- <h5 class="d-flex align-items-center justify-content-between pb-2 border-bottom"><b>City</b><b>Dubai</b></h5> --}}
                                    <h5 class="d-flex align-items-center justify-content-between pb-2">{{__('SERVICE DETAILS')}} </h5>
                                </div>
                                <div class="spinner-box">
                                    <div class="circle-border">
                                        <div class="circle-core"></div>
                                    </div>
                                </div>
                                <ul class="show-div shopping-cart d-none" id="header_cart_main_ul_ondemand">
                                </ul>
                                <script type="text/template" id="header_cart_template_ondemand">
                                        <% _.each(cart_details.products, function(product, key){%>
                                            <li class="alVendorName">
                                                <h6 class="d-flex align-items-center justify-content-between"> <%= product.vendor.name %> </h6>
                                            </li>

                                            <% if( (product.isDeliverable != undefined) && (product.isDeliverable == 0) ) { %>
                                                <li class="border_0">
                                                    <th colspan="7">
                                                        <div class="text-danger">
                                                        {{__('Products for this vendor are not deliverable at your area. Please change address or remove product.')}}
                                                        </div>
                                                    </th>
                                                </li>
                                                <% } %>
                                            <% _.each(product.vendor_products, function(vendor_product, vp){%>
                                                <li class="alVendorProductDetails" id="cart_product_<%= vendor_product.id %>" data-qty="<%= vendor_product.quantity %>">
                                                        <div class='media-body'>
                                                            <h6 class="d-flex align-items-center justify-content-between">
                                                                <span class="ellips"><%= vendor_product.quantity %>x <%= vendor_product.product.translation_one ? vendor_product.product.translation_one.title :  vendor_product.product.sku %></span>
                                                                <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor_product.pvariant.price) %></span>
                                                            </h6>
                                                        </div>

                                                    <div class='close-circle'>
                                                        <a  class="action-icon d-block mb-3 remove_product_via_cart" data-product="<%= vendor_product.id %>" data-product_id="<%= vendor_product.product_id %>" data-vendor_id="<%= vendor_product.vendor_id %>">
                                                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                        </a>
                                                    </div>
                                                </li>

                                                <% if(vendor_product.addon.length != 0) { %>
                                                    <div class="row align-items-md-center m-0">
                                                        <div class="col-12 alVendorProductDetails">
                                                            <h6 class="m-0 pl-0"><b>{{__('Add Ons')}}</b></h6>
                                                        </div>
                                                    </div>
                                                    <% _.each(vendor_product.addon, function(addon, ad){%>
                                                    <div class="row alVendorProductDetails m-0">
                                                        <div class="col-md-3 col-sm-4 items-details text-left">
                                                            <p class="p-0 m-0"><%= addon.option.title %></p>
                                                        </div>
                                                        <div class="col-md-2 col-sm-4 text-center">
                                                            <div class="extra-items-price">{{Session::get('currencySymbol')}}<%=  Helper.formatPrice(addon.option.price_in_cart) %></div>
                                                        </div>
                                                        <div class="col-md-7 col-sm-4 text-right">
                                                            <div class="extra-items-price">{{Session::get('currencySymbol')}}<%=  Helper.formatPrice(addon.option.quantity_price) %></div>
                                                        </div>
                                                    </div>
                                                    <% }); %>
                                                <% } %>
                                                <hr class="my-2">


                                            <% }); %>
                                        <% }); %>


                                        @foreach ($cartData as $cd => $cart_data)
                                        @if(!empty($cart_data->product->mode_of_service) && $cart_data->product->mode_of_service == 'schedule')
                                        <h4 class="mb-2"><b>{!! (!empty($cart_data->product->translation->first())) ? $cart_data->product->translation->first()->title : $cart_data->product->sku !!}</b></h4>

                                        <h5 class="d-flex align-items-center justify-content-between pb-2">{{__('DATE & TIME')}} </h5>
                                        <li class="alVendorProductTotals">
                                            <div class='media-body'>
                                                <h6 class="d-flex align-items-center justify-content-between">
                                                    <span class="ellips">{{__('Date')}}</span>
                                                    <span id="show_date{{$cart_data->id}}">@if(isset($cart_data->scheduled_date_time)) {{ date('d-m-Y', strtotime($cart_data->scheduled_date_time)) }}  @else -- @endif </span>
                                                </h6>
                                            </div>
                                        </li>

                                        <li class="alVendorProductTotals">
                                            <div class='media-body'>
                                                <h6 class="d-flex align-items-center justify-content-between">
                                                    <span class="ellips">{{__('Start Time')}}</span>
                                                    <span id="show_time{{$cart_data->id}}">@if(isset($cart_data->scheduled_date_time)) {{ $cart_data->schedule_slot }}  @else -- @endif</span>
                                                </h6>
                                            </div>
                                        </li>
                                        @endif
                                        @endforeach

                                        <h5 class="d-flex align-items-center justify-content-between pb-2">{{__('PRICE DETAILS')}} </h5>
                                        <li class="alVendorProductTotals">
                                            <div class='media-body'>
                                                <h6 class="d-flex align-items-center justify-content-between">
                                                    <span class="ellips">{{__('Price')}}</span>
                                                    <span>{{Session::get('currencySymbol')}}<%=  Helper.formatPrice(cart_details.gross_amount) %></span>
                                                </h6>
                                            </div>
                                        </li>

                                        <li class="alVendorProductTotals">
                                            <div class='media-body'>
                                                <h6 class="d-flex align-items-center justify-content-between">
                                                    <span class="ellips">{{__('Tax')}}</span>
                                                    <span>{{Session::get('currencySymbol')}}<%= cart_details.total_taxable_amount %></span>
                                                </h6>
                                            </div>
                                        </li>

                                        <% if(cart_details.loyalty_amount > 0) { %>
                                        <li class="alVendorProductTotals">
                                            <div class='media-body'>
                                                <h6 class="d-flex align-items-center justify-content-between">
                                                    <span class="ellips">{{__('Loyalty Amount')}} </span>
                                                    <span>{{Session::get('currencySymbol')}}<%=  Helper.formatPrice(cart_details.loyalty_amount) %></span>
                                                </h6>
                                            </div>
                                        </li>
                                        <% } %>

                                        <li class="alVendorProductTotals">
                                            <div class='media-body'>
                                                <h6 class="d-flex align-items-center justify-content-between">
                                                    <span class="ellips">{{__('Total')}}</span>
                                                    <span>{{Session::get('currencySymbol')}}<%=  Helper.formatPrice(cart_details.total_payable_amount) %></span>
                                                </h6>
                                            </div>
                                        </li>

                                </script>



                            </div>
                        </div>
                        <div class="footer-card">
                            @if((app('request')->input('step') == '1' || empty(app('request')->input('step'))) && empty(app('request')->input('addons')))
                                <a href="?step=2" id="next-button-ondemand-2" style="display: none;"><span class="btn btn-solid float-right">{{__('Next')}}</span></a>
                                @elseif(app('request')->input('step') == '1' && app('request')->input('addons') == '1')
                                    <a href="?step=1"><span class="btn btn-solid float-left"><</span></a>
                                    <a href="?step=2&dateset=1&addons=1" id="next-button-ondemand-2"><span class="btn btn-solid float-right">{{__('Next')}}</span></a>
                                @elseif(app('request')->input('step') == '2' && empty(app('request')->input('addons')))
                                    <a href="?step=1"><span class="btn btn-solid float-left"><</span></a>
                                    @if(Auth::guest())
                                        <a href="{{route('customer.login')}}" id="next-button-ondemand-3"><span class="btn btn-solid float-right">Continue</span></a>
                                    @else
                                        <a href="#" id="next-button-ondemand-3"><span class="btn btn-solid float-right">Continue</span></a>
                                    @endif
                                @elseif(app('request')->input('step') == '2' && !empty(app('request')->input('dateset')))
                                        <a href="?step=1"><span class="btn btn-solid float-left"><</span></a>
                                        @if(Auth::guest())
                                            <a href="{{route('customer.login')}}" id="next-button-ondemand-3" ><span class="btn btn-solid float-right">Continue</span></a>
                                        @else
                                            <a href="#" id="next-button-ondemand-3"><span class="btn btn-solid float-right">Continue</span></a>
                                        @endif
                                @elseif(app('request')->input('step') == '3')
                                    <a href="?step=2"><span class="btn btn-solid"><</span></a>
                                    <a href="?step=3" id="next-button-ondemand-4"><span class="btn btn-solid float-right">Continue</span></a>
                                @else
                            @endif
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>
</section>
@include('frontend.ondemand.productPriceModel')
@section('custom-js')
<script src="{{ asset('js/onDemand/GetDispatcherPrice.js') }}"></script>
<script src="{{ asset('js/onDemand/AgentSlot.js') }}"></script>
@endsection