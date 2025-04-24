@section('customcss')
<link defer type="text/css" href="{{asset('css/ondemand.css')}}" rel="stylesheet" id="bs-default-stylesheet" />
<style>
.home-serivces .step-indicator .step1 p{
    width: max-content;
}   
</style>
@endsection
@php
$add_to_cart =  route('addToCart') ;
$additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch','is_service_price_selection']);
$getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);

$is_service_product_price_from_dispatch_forOnDemand = 0;
$category_type_idForNotShowshPlusMinus = ['12'];
if($getOnDemandPricingRule['is_price_from_freelancer']==1){
    $is_service_product_price_from_dispatch_forOnDemand =1;
    array_push($category_type_idForNotShowshPlusMinus,8);
}

@endphp
<section class="home-serivces" id="alSixHomeServices">
    <div class="container">
        <div class="row mb-lg-5 mb-md-4 mb-3">
        <!-- class="col-xl-8 offset-xl-2 replace class to col-md-12" -->
            <div class="col-md-12">
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

                         <!-- Start Main Nav -->
                            @if(!empty($category->childs) && count($category->childs) > 0)
                            <nav id='main-nav'>
                                <ul id='main-nav-list'>
                                @if(!empty($category->childs) && count($category->childs) > 0)
                                        @foreach ($category->childs as $key => $childs)

                                            @if( in_array($childs->type_id , [8,12]))
                                            <li><a href="#section_set{{$key}}">{{ $childs['translation_name'] ?? ''}}</a></li>
                                            @endif
                                        @endforeach
                                    @endif
                                </ul>
                            </nav>
                            @endif
                        <!-- End Main Nav -->

                        @endif

                        <div class="card-box ">
                                     <!-- static html -->

                                @if(app('request')->input('step') == '1' || empty(app('request')->input('step')))
                                     @if(!empty($category->childs) && count($category->childs) > 0)

                                    <!-- Start Conent Wrapper -->
                                    <div id='main-wrapper'  class="@if(app('request')->input('addons') == 1) d-none @endif">
                                                @foreach ($category->childs as $key => $childs)
                                               
                                                @if( in_array($childs->type_id , [8,12]))

                                                <h4><b>{{ $childs->translation_name }}</b></h4>
                                                    <div class='' id='section_set{{$key}}'>
                                                        @if(!empty($childs))
                                                        <div class="service-img mb-3">
                                                            <img class="img-fluid" src="{{$childs->image['proxy_url'] . '1000/300' . $childs->image['image_path']}}" alt="">
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
                                                                $data->category_type_id = $childs->type_id
                                                            @endphp

                                                            <div class="row classes_wrapper no-gutters align-items-center" href="#">
                                                                <div class="col-md-9 col-sm-8 pr-md-2 ">
                                                                    <h5 class="mb-1"><b>{!! (!empty($data->translation->first())) ? $data->translation->first()->title : $data->sku !!}</b></h5>
                                                                    <div class="productDetails pr-2">
                                                                        <p class="mb-1 ">{!! (!empty($data->translation->first())) ? $data->translation->first()->body_html : $data->sku !!}</p>
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
                                                                <div class="col-12 ac-royo-btn">
                                                                    <div class="d-flex align-items-center justify-content-between productBookingBtns">
                                                                        @if($productInquiryCheck == 0)
                                                                    
                                                                            <h5 class="my-sm-0 my-3">
                                                                                @if($is_service_product_price_from_dispatch_forOnDemand !=1)
                                                                                    {{Session::get('currencySymbol').(decimal_format($data->variant_price * $data->variant_multiplier))}}
                                                                                    <span class="alProductViewPriceMin"> {{ $data->minimum_duration_min > 0 ? $data->minimum_duration_min . __(' min') : '' }}</span>
                                                                                @endif
                                                                            </h5>
                                                                       

                                                                            @if( (isset($data->variant[0]->checkIfInCart) && count($data->variant[0]->checkIfInCart) > 0) )
                                                                            @php
                                                                                $cartcount = 1;
                                                                            @endphp
                                                                            @if(   $is_service_product_price_from_dispatch_forOnDemand ==1)


                                                                                                                                                            
                                                                            <a class="btn btn-solid btn btn-solid view_on_demand_price"  style="display:none;" id="add_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                            @else
                                                                                <a class="btn btn-solid {{  $class }}" style="display:none;" id="add_button _href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="{{ $redirec }}">Add <i class="fa fa-plus"></i></a>
                                                                            @endif

                                                                            @if(
                                                                                isset($data->category_type_id) && 
                                                                                ( 
                                                                                    (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)) 
                                                                                )  
                                                                            )
                                                                                <div class="number" id="show_plus_minus{{$data->variant[0]->checkIfInCart['0']['id']}}">
                                                                                    <span class="minus qty-minus-ondemand sd"  data-parent_div_id="show_plus_minus{{$data->variant[0]->checkIfInCart['0']['id']}}" data-id="{{$data->variant[0]->checkIfInCart['0']['id']}}" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
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
                                                                            

                                                                            @if(   $is_service_product_price_from_dispatch_forOnDemand ==1)
                                                                                    
                                                                                <a class="btn btn-solid btn btn-solid view_on_demand_price"  id="add_button_href{{$data->id }}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                            @else
                                                                              <a class="btn btn-solid {{  $class }}" id="add_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="{{ $redirec }}">{{ __('Add') }} <i class="fa fa-plus"></i></a>
                                                                            @endif 
                                                                            @if(
                                                                                isset($data->category_type_id) && 
                                                                                ( 
                                                                                    (!in_array($data->category_type_id,$category_type_idForNotShowshPlusMinus)) 
                                                                                )  
                                                                            )
                                                                                <div class="number" style="display:none;" id="ashow_plus_minus{{$data->id}}">
                                                                                    <span class="minus qty-minus-ondemand 132"  data-parent_div_id="show_plus_minus{{$data->id}}" readonly data-id="{{$data->id}}" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                                                                    </span>
                                                                                    <input style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;" id="quantity_ondemand_d{{$data->id}}" readonly placeholder="1" type="text" value="1" class="input-number input_qty" step="0.01">
                                                                                    <span class="plus qty-plus-ondemand"  data-id="" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                                                                    </span>
                                                                                </div>
                                                                            @else
                                                                                <a class="btn btn-solid "  style="display:none;" id="added_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                            @endif 

                                                                            @endif
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
                                    @endif
                                @endif



                            <!-- end statis html -->

                                    <!-- for single level category -->

                            @if(!empty($category->childs) && count($category->childs) == 0)

                                        @if(app('request')->input('step') == '1' || empty(app('request')->input('step')))
                                        <div class="outter-scroller  service-data-wrapper al @if(app('request')->input('addons') == 1) d-none @endif"  id="step-1-ondemand" >
                                            <div class="service-data">
                                                @if($category->translation_name !='')<h4><b>{{ $category->translation_name }}</b></h4>@endif


                                                @if(!empty($category->image))
                                                <div class="service-img mb-3">
                                                    <img class="img-fluid" src="{{$category->image['proxy_url'] . '1000/300' . $category->image['image_path']}}" alt="">
                                                </div>
                                                @endif
                                                @if($listData->isNotEmpty())
                                                @foreach($listData as $key => $data)

                                                {{-- new product design  --}}
                                                <div class="row classes_wrapper no-gutters align-items-center" href="#">
                                                    <div class="col-md-9 col-sm-8 pr-md-2">
                                                        <h5 class="mb-1"><b>{!! $data->translation_title !!}</b></h5>
                                                        <span class="mb-1 font-weight-bold">{!! $data->vendor->name !!}</span>
                                                        <div class="productDetails pr-2">
                                                            <p class="mb-1">{!! $data->translation_description !!}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-4 mb-3">
                                                        <?php $imagePath = $imagePath2 = '';
                                                            $mediaCount = count($data->media);
                                                            for ($i = 0; $i < $mediaCount && $i < 2; $i++) {
                                                                if($i == 0){
                                                                    $imagePath = $data->media[$i]->image->path['proxy_url'].'300/300'.$data->media[$i]->image->path['image_path'];
                                                                }
                                                                $imagePath2 = $data->media[$i]->image->path['proxy_url'].'300/300'.$data->media[$i]->image->path['image_path'];
                                                            } 
                                                            $productInquiryCheck = $data->inquiry_only  ; 
                                                            ?>
                                                        <div class="class_img">
                                                            @if($imagePath != '')
                                                            <img src="{{$imagePath}}" alt="">
                                                            @else

                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-12 ac-royo-btn">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            @if($productInquiryCheck == 0)
                                                            @php
                                                                $cartcount = 0;
                                                                $redirec = ($data->is_recurring_booking ==1) ? route('productDetail', [@$data->vendor->slug, $data->url_slug]) : 'javascript:void(0)' ;
                                                                $class = ($data->is_recurring_booking ==1) ? 'add_on_demand_btn' : 'add_on_demand' ;
                                                            @endphp
                                                            @if(isset($data->variant[0]->checkIfInCart) && count($data->variant[0]->checkIfInCart) > 0)
                                                                @php
                                                                    $cartcount = 1;
                                                                @endphp
                                                            @endif
                                                                @if($is_service_product_price_from_dispatch_forOnDemand==1)
                                                                @if($cartcount > 0)
                                                                        <h5 class="my-sm-0 my-3 "></h5>
                                                                        <a class="btn btn-solid float-right"  id="added_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                    <a class="btn btn-solid view_on_demand_price"  style="display:none;" id="add_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                    @else
                                                                    <h5 class="my-sm-0 my-3 "></h5>
                                                                    <a class="btn btn-solid view_on_demand_price"  id="add_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('view Price') }}</a>
                                                                    <a class="btn btn-solid float-right"  style="display:none;" id="added_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                    @endif
                                                                
                                                                @else {{-- else is_service_product_price_from_dispatch --}}
                                                                  
                                                                    <h5 class="my-sm-0 my-3 ">
                                                                        {{Session::get('currencySymbol').(decimal_format($data->variant_price * $data->variant_multiplier))}}
                                                                    
                                                                        <span class="alProductViewPriceMin"> {{ $data->minimum_duration_min > 0 ? $data->minimum_duration_min . __(' min') : '' }}</span>
                                                                    </h5>

                                                                    @if($cartcount > 0)
                                                                    
                                                                        @if(isset($data->category_type_id) && (!in_array($data->category_type_id,[12])) )
                                                                            <a class="btn btn-solid  {{$class}} " style="display:none;" id="add_button_href{{$data->variant[0]->checkIfInCart['0']['id']}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="{{ $redirec }}">Add <i class="fa fa-plus"></i></a>
                                                                            <div class="number" id="show_plus_minus{{$data->variant[0]->checkIfInCart['0']['id']}}">
                                                                                <span class="minus qty-minus-ondemand 245"  data-parent_div_id="show_plus_minus{{$data->variant[0]->checkIfInCart['0']['id']}}" data-id="{{$data->variant[0]->checkIfInCart['0']['id']}}" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                                                </span>
                                                                                <input style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;" placeholder="1" type="text" value="{{$data->variant[0]->checkIfInCart['0']['quantity']}}" class="input-number" step="0.01" id="quantity_ondemand_{{$data->variant[0]->checkIfInCart['0']['id']}}" readonly>
                                                                                <span class="plus qty-plus-ondemand"  data-id="{{$data->variant[0]->checkIfInCart['0']['id']}}" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                                                </span>
                                                                            </div>
                                                                        @else
                                                                            <a class="btn btn-solid " id="add_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>

                                                                        @endif
                                                                    @else
                                                                    <div class="d-flex">
                                                                        <a class="btn btn-solid {{ $class }}" id="add_button_href{{ $data->id }}" 
                                                                        data-variant_id="{{ $data->variant[0]->id }}" 
                                                                        data-add_to_cart_url="{{ $add_to_cart }}" 
                                                                        data-vendor_id="{{ $data->vendor_id }}" 
                                                                        data-product_id="{{ $data->id }}" 
                                                                        href="{{ $redirec }}">
                                                                        Book Slots </i>
                                                                        </a>

                                                                        <a class="btn btn-solid {{ $class }}" id="add_button_href{{ $data->id }}" 
                                                                        data-variant_id="{{ $data->variant[0]->id }}" 
                                                                        data-mode_of_service="instant"
                                                                        data-add_to_cart_url="{{ $add_to_cart }}" 
                                                                        data-vendor_id="{{ $data->vendor_id }}" 
                                                                        data-product_id="{{ $data->id }}" 
                                                                        href="{{ $redirec }}"
                                                                        style="margin-left: 15px;">
                                                                        Express Service </i>
                                                                        </a>
                                                                    </div>

                                                                        @if(isset($data->category_type_id) && (!in_array($data->category_type_id,[12])) )
                                                                        <div class="number" style="display:none;" id="ashow_plus_minus{{$data->id}}">
                                                                            <span class="minus qty-minus-ondemand 256"  data-parent_div_id="show_plus_minus{{$data->id}}" readonly data-id="{{$data->id}}" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                                                            </span>
                                                                            <input style="text-align:center;width: 80px;margin:auto;height: 24px;padding-bottom: 3px;" id="quantity_ondemand_d{{$data->id}}" readonly placeholder="1" type="text" value="1" class="input-number input_qty" step="0.01">
                                                                            <span class="plus qty-plus-ondemand"  data-id="" data-base_price="{{$data->variant_price * $data->variant_multiplier}}" data-vendor_id="{{$data->vendor_id}}">
                                                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                                                            </span>
                                                                        </div>
                                                                        @else
                                                                                <a class="btn btn-solid " style="display:none;"   id="add_button_href{{$data->id}}" data-variant_id = {{$data->variant[0]->id}} data-add_to_cart_url = "{{ $add_to_cart }}" data-vendor_id="{{$data->vendor_id}}" data-product_id="{{$data->id}}" href="javascript:void(0)">{{ __('Added') }}</a>
                                                                        @endif
                                                                    @endif
                                                                
                                                                @endif {{-- end is_service_product_price_from_dispatch --}}
                                                            @endif

                                                            </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                @endforeach
                                                @if(count($listData))
                                                <div class="pagination pagination-rounded justify-content-end mb-0 page-m-20">
                                                    {{ $listData->links() }}
                                                </div>
                                                @endif
                                            @else
                                                <div class="col-xl-12 col-12 mt-4"><h5 class="text-center">{{ __('No Product Found') }}</h5></div>
                                            @endif



                                            </div>
                                        </div>

                                        @endif
                            @endif
                            <!-- end single level category -->

                             <!-- Step if addons avilable  Html -->

                            @if(app('request')->input('addons') == '1' && app('request')->input('dateset') != '1')
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
                            @endif
                             <!--Step if addons avilable  Html -->


                            <!-- Step Two Html -->


                            @if(app('request')->input('step') == '2')
                             
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
                                        
                                        @if(in_array($cart_data->cateTypeId , [8,12]) && ($additionalPreference['is_service_product_price_from_dispatch'] !=1))
                                          
                                            @if(request()->get('mode') != 'instant')
                                                @php
                                                    $productDate = trim(date('Y-m-d', strtotime($cart_data->scheduled_date_time)));
                                                @endphp

                                               
                                            <div  id="date_time_set_div{{$cart_data->id}}" class="booking_date_section">

                                                <h4 class="mb-2" ><b>{{ __('When would you like your service?')}}</b></h4>
                                                @if(count($cart_data->period)>0)
                                                <div class="date-items radio-btns hide">

                                                    @foreach ($cart_data->period as $key => $date)
                                                        <div>
                                                            @php
                                                         
                                                            $checked = '';
                                                            $singleDate =  trim(date('Y-m-d', strtotime($date)));
                                                            if($productDate == $singleDate && !empty($productDate)){
                                                                $checked = "checked";
                                                            }
                                                            $dateRandNo = rand(10,100);
                                                            @endphp
                                                            <div class="radios">
                                                                <p>{{date('D', strtotime($date))}}</p>
                                                                <div class="alCustomHomeServiceRadio ">
                                                                    <input type="radio"  class="check-time-slots booking_date ondemand-time-slots ondemand_{{ $checked }}" data-product_vendor_id="{{$cart_data->vendor_id}}" data-cart_product_id = "{{$cart_data->id}}" data-product_id ="{{$cart_data->product->id}}" data-product_tag ="{{$cart_data->product->tags}}" data-product_category_type ="{{$cart_data->product->productcategory->type_id}}"  value='{{date('Y-m-d', strtotime($date))}}' name='booking_date_{{$cart_data->id}}' id='radio{{$cd}}{{$key}}{{ $dateRandNo }}' {{$checked }} @if(($key == 0 && $checked == "")) checked @endif />
                                                                    <label for='radio{{$cd}}{{$key}}{{$dateRandNo  }}'>
                                                                    <span class="customCheckbox" aria-hidden="true" >{{date('d', strtotime($date))}}</span>
                                                                    </label>
                                                                    <input type="hidden" name="productid" id="productid" value="{{$cart_data->id}}" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach

                                                </div>
                                                @else
                                                <h5 class="text-center">{{ __("Vendor has not created slots for this Date yet.") }}</h5>
                                                @endif
                                               
                                                @if($cart_data->is_dispatch_slot == 1)
                                                @php

                                                $dispatch_agents = $cart_data->dispatchAgents ?? [];
                                                $cart_product_id = $cart_data->id;
                                                $dispatch_agent_id = @$cart_data->dispatch_agent_id;
                                                $show_dispatcher_agent = @$cart_data->product->is_show_dispatcher_agent;
                                                $selected_agent_id = @$cart_data->dispatch_agent_id;
                                                $schedule_slot = $cart_data->schedule_slot;

                                                @endphp
                                                <div class="booking-time-wrapper" id="show-all-time-slots{{$cart_data->id}}" >
                                                    {{-- style="@if($cart_data->schedule_slot != '')  @else display: none; @endif " --}}
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
                                                                        <input type="radio" value='{{$date}} - {{@$time_slots[$key+1]}}' name='booking_time' id='time{{$cart_data->id}}{{$key+1}}' @if($checked) {{$checked}} @endif class="ondemand_{{$checked }}" />
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
                                        @endif   
                                    @endforeach

                                        {{-- <div class="booking-time-wrapper">
                                            <input type="hidden" id="last_cart_product_id" value="{{ $last_cart_product_id }}">
                                            <h4 class="mt-4 mb-2"><b>{{__('Do you have any specific instructions?')}}</b></h4>
                                            <textarea class="form-control" name="specific_instructions" id="specific_instructions" cols="30" rows="7"></textarea>
                                        </div> --}}


                                </div>
                            @endif
                            <!--end step 2 html -->



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

                    <div class="col-md-4 side-card">
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
                                                        <a  class="action-icon d-block mb-3 remove_product_via_cart" data-product="<%= vendor_product.id %>" data-vendor_id="<%= vendor_product.vendor_id %>" data-product_id="<%= vendor_product.product.id %>">
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
                                        @if(request()->get('mode') != 'instant')
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
                                                    <span>{{Session::get('currencySymbol')}}<%=  Helper.formatPrice(cart_details.sub_total) %></span>
                                                </h6>
                                            </div>
                                        </li>
                                        <!-- <li class="alVendorProductTotals">
                                            <div class='media-body'>
                                                <h6 class="d-flex align-items-center justify-content-between">
                                                    <span class="ellips">{{__('Delivery Charges')}}</span>
                                                    <span>{{Session::get('currencySymbol')}}<%=  Helper.formatPrice(cart_details.delivery_charges) %></span>
                                                </h6>
                                            </div>
                                        </li> -->

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
                                 <ul class="show-div shopping-cart d-none" id="header_cart_main_ul_ondemand">
                                 </ul>


                            </div>
                        </div>
                        <div class="footer-card">
                            @if((app('request')->input('step') == '1' || empty(app('request')->input('step'))) && empty(app('request')->input('addons')))
                                <a href="?step=2{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '&mode=instant' : '' }}" id="next-button-ondemand-2" style="display: none;"><span class="btn btn-solid float-right">{{__('Next')}}</span></a>
                                @elseif(app('request')->input('step') == '1' && app('request')->input('addons') == '1')
                                    <a href="?step=1{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '&mode=instant' : '' }}"><span class="btn btn-solid float-left"><</span></a>
                                    <a href="?step=2&dateset=1&addons=1{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '&mode=instant' : '' }}" id="next-button-ondemand-2"><span class="btn btn-solid float-right">{{__('Next')}}</span></a>
                                @elseif(app('request')->input('step') == '2' && empty(app('request')->input('addons')))
                                    <a href="?step=1{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '&mode=instant' : '' }}"><span class="btn btn-solid float-left"><</span></a>
                                    @if(Auth::guest())
                                        <a href="{{route('customer.login')}}{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '?mode=instant' : '' }}" id="next-button-ondemand-3"><span class="btn btn-solid float-right">Continue</span></a>
                                    @else
                                        <a href="#" id="next-button-ondemand-3"><span class="btn btn-solid float-right">Continue</span></a>
                                    @endif
                                @elseif(app('request')->input('step') == '2' && !empty(app('request')->input('dateset')))
                                        <a href="?step=1{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '&mode=instant' : '' }}"><span class="btn btn-solid float-left"><</span></a>
                                        @if(Auth::guest())
                                            <a href="{{route('customer.login')}}{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '?mode=instant' : '' }}" id="next-button-ondemand-3" ><span class="btn btn-solid float-right">Continue</span></a>
                                        @else
                                            <a href="#" id="next-button-ondemand-3"><span class="btn btn-solid float-right">Continue</span></a>
                                        @endif
                                @elseif(app('request')->input('step') == '3')
                                    <a href="?step=2{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '&mode=instant' : '' }}"><span class="btn btn-solid"><</span></a>
                                    <a href="?step=3{{ isset($_GET['mode']) && $_GET['mode'] == 'instant' ? '&mode=instant' : '' }}" id="next-button-ondemand-4"><span class="btn btn-solid float-right">Continue</span></a>
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

<script>
$(document).ready(function() {
    // Handle express service button click
    $('a[data-mode_of_service="instant"]').on('click', function(e) {
        e.preventDefault();
        // Add mode=instant to URL
        var url = new URL(window.location.href);
        url.searchParams.set('mode', 'instant');
        window.location.href = url.toString();
    });
});
</script>
@endsection
