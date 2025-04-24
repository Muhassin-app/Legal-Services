 <!--- Left Sidebar filters -->
 <aside class="side_fillter">
                        <h5 class="title-border text-right">
                            <span class="filter-back d-lg-none d-inline-block">
                                <i class="fa fa-angle-left" aria-hidden="true"></i> {{__('Back')}}
                            </span>
                        </h5>
@if(@$category->type_id != 13)
    <div class="col-12 custom_filtter mt-2 mb-2">
        <select name="order_type" id='order_type' class="form-control sortingFilter p-1 mb-0">
            <option value="">{{__('Sort By')}}</option>
            <option value="featured">{{__('Featured')}}</option>
            <option value="a_to_z">{{__('A to Z')}}</option>
            <option value="z_to_a">{{__('Z to A')}}</option>
            <option value="low_to_high">{{__('Cost : Low to High')}}</option>
            <option value="high_to_low">{{__('Cost : High to Low')}}</option>
            <option value="rating">{{__('Avg. Customer Review')}}</option>
            <option value="newly_added">{{__('Newest Arrivals')}}</option>
        </select>
    </div>
@endif   
<!-- side-bar colleps block stat -->
<div class="collection-filter-block bg-transparent p-0 m-0 brand-left">
  @if(@$category->brands)
        @if(@count(@$category->brands->first()->translation) && count(@$category->brands->first()->translation) > 0)
            <div class="collection-collapse-block open mb-2">
                <h3 class="collapse-block-title">{{__('Brand')}}</h3>
                <div class="collection-collapse-block-content">
                    <div class="collection-brand-filter">
                        @foreach(@$category->brands as $key => $val)
                            <div class="custom-control custom-checkbox collection-filter-checkbox">
                                <input type="checkbox" class="custom-control-input productFilter" fid="{{$val->id}}" used="brands" id="brd{{$val->id}}">
                                @foreach($val->translation as $k => $v)
                                    <label class="custom-control-label" for="brd{{$val->id}}">{{$v->title}}</label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
     @endif
    @if(!empty($variantSets) && count($variantSets) > 0)
    @foreach($variantSets as $key => $sets)
        <div class="collection-collapse-block border-0 mb-2 open">
            <h3 class="collapse-block-title">{{$sets->title}}</h3>
            <div class="collection-collapse-block-content">
                <div class="collection-brand-filter">
                @if($sets->type == 2)
                    @foreach($sets->options as $ok => $opt)
                        <div class="chiller_cb small_label d-inline-block color-selector mt-2">
                            <?php $checkMark = ($key == 0) ? 'checked' : ''; ?>
                            <input class="custom-control-input productFilter" type="checkbox" {{$checkMark}} id="Opt{{$key.'-'.$opt->id}}" fid="{{$sets->variant_type_id}}" used="variants" optid="{{$opt->id}}">
                            <label for="Opt{{$key.'-'.$opt->id}}"></label>
                            @if(strtoupper($opt->hexacode) == '#FFF' || strtoupper($opt->hexacode) == '#FFFFFF')
                                <span style="background: #FFFFFF; border-color:#000;" class="check_icon white_check"></span>
                            @else
                                <span class="check_icon" style="background:{{$opt->hexacode}}; border-color: {{$opt->hexacode}};"></span>
                            @endif
                        </div>
                    @endforeach
                @else
                    @foreach($sets->options as $ok => $opt)
                        <div class="custom-control custom-checkbox collection-filter-checkbox">
                            <input type="checkbox" class="custom-control-input productFilter" id="Opt{{$key.'-'.$opt->id}}" fid="{{$sets->variant_type_id}}" type="variants" optid="{{$opt->id}}">
                            <label class="custom-control-label" for="Opt{{$key.'-'.$opt->id}}">{{$opt->title}}</label>
                        </div>
                    @endforeach
                @endif
                </div>
            </div>
        </div>
    @endforeach
    @endif
    <div class="collection-collapse-block border-0 mb-2 open">
        <h3 class="collapse-block-title">{{__('Price')}}</h3>
        <div class="collection-collapse-block-content">
            <div class="wrapper mt-3">
                <div class="range-slider">
                    <input type="text" class="js-range-slider rangeSliderPrice" value="" debounce="500"  />
                </div>
            </div>
        </div>
    </div>
</div>

@php $show_new_Products = 0; @endphp
                    @if($show_new_Products && !empty($newProducts) && count($newProducts) > 0)
                    <div class="theme-card custom-inner-card">
                        <h5 class="title-border d-flex align-items-center justify-content-between">
                            <span>{{__('New Product')}}</span>
                            <span class="filter-back d-lg-none d-inline-block">
                                <i class="fa fa-angle-left" aria-hidden="true"></i> {{__('Back')}}
                            </span>
                        </h5>

                        <div class="offer-slider al">
                           
                                @foreach($newProducts as $newProds)
                                    <div class="col-12 p-0">
                                    @foreach($newProds as $new)
                                        <?php /*$imagePath = '';
                                        foreach ($new['media'] as $k => $v) {
                                            $imagePath = $v['image']['path']['image_fit'].'300/300'.$v['image']['path']['image_path'];
                                        }*/ ?>
                                        <div class=" common-product-box scale-effect mb-2">
                                            <a class="row  w-100" href="{{route('productDetail', [$new['vendor']['slug'],$new['url_slug']])}}">
                                                <div class="col-4">
                                                    <div class="img-outer-box position-relative  pr-0">
                                                        <img class="blur-up lazyload p-0" data-src="{{$new['image_url']}}" alt="">
                                                        <div class="pref-timing"></div>
                                                        {{--<i class="fa fa-heart-o fav-heart" aria-hidden="true"></i>--}}
                                                    </div>
                                                </div>
                                                <div class="col-8">
                                                    <div class="media-body align-self-center ">
                                                        <div class="inner_spacing px-0">
                                                            <div class="product-description">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <h6 class="card_title ellips">{{ $new['translation_title'] }}</h6>
                                                                    <!--<span class="rating-number">2.0</span>-->
                                                                </div>
                                                                <!-- <h3 class="mb-0 mt-2">{{ $new['translation_title'] }}</h3> -->
                                                                <p>{{$new['vendor']['name']}}</p>
                                                                <p class="pb-1">{{__('In')}} {{$new['category_name']}}</p>
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <b>
                                                                        @if($new['inquiry_only'] == 0)
                                                                            <?php $multiply = $new['variant_multiplier']; ?>
                                                                            {{ Session::get('currencySymbol').' '.(decimal_format($new['variant_price'] * $multiply))}}
                                                                        @endif
                                                                    </b>

                                                                    <!-- @if($client_preference_detail)
                                                                        @if($client_preference_detail->rating_check == 1)
                                                                            @if($new['averageRating'] > 0)
                                                                                <div class="rating-box">
                                                                                    <i class="fa fa-star" aria-hidden="true"></i>
                                                                                    <span>{{ $new['averageRating'] }}</span>
                                                                                </div>
                                                                            @endif
                                                                        @endif
                                                                    @endif   -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                    </div>
                                @endforeach
                            
                        </div>
                    </div>
                    @endif

                    @php $getAdditionalPreference = getAdditionalPreference(['is_attribute']); @endphp
                    @if( isset($getAdditionalPreference['is_attribute']) && !empty($productAttributes))
                        <div class="p2p-sidebar" >
                            
                            <div class="row">
                                <div id="variantAjaxDiv" class="col-12 mb-2">
                                    <div class="row mb-2">
                                        
                                        @foreach($productAttributes as $vk => $var)
                                        @php $counter = 0; @endphp
                                        <div class="col-sm-12">
                                            <label class="control-label">{{$var->title??null}}</label>
                                        </div>
                                        <div class="col-sm-12">
                                            @if( !empty($var->type) && $var->type == 1 )
                                            {{-- <select class="form-control " name="free_delivery_roles[]" data-toggle="select2" multiple="multiple" placeholder="Select role..."> --}}
                                                @if($var->option->count() <= 6)
                                                    <div class="flex-wrap checkbox checkbox-success form-check-inline">
                                                        @foreach($var->option as $key => $opt)
                                                            <div class="checkbox_filter">
                                                                <input type="checkbox" name="" value="{{$opt->id}}" class="dynamic_checkbox" data-key="{{$var->title}}">
                                                                {{-- <option value=""></option> --}}
                                                                <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <select name="" class="dropdown_select select2-multiple" data-key="{{$var->title}}" multiple>
                                                        @foreach($var->option as $key => $opt)
                                                        <option value="{{$opt->id}}">{{$opt->title}}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            @else
                                            
                                            @foreach($var->option as $key => $opt)
                                            
                                            @if(isset($opt) && isset($var) && !empty($var->title) )

                                                @if( !empty($var->type) && $var->type == 3 )
                                                    <div class="form-check-inline radio_Btn">
                                                        <div class="attr_radio_{{$var->id}}">                                                            
                                                            <input type="radio" name="attribute[{{$var->id}}][option][{{$counter}}][value]" class="attr_radio"  
                                                            value="{{$opt->id}}" data-key="{{$var->title}}">
                                                            <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                                                        </div>
                                                        
                                                    </div>


                                                @elseif( !empty($var->type) && $var->type == 4 )
                                                    <div class="form-check-inline d-block">
                                                        <input type="textbox" class="text_field custom-search" name="attribute[{{$var->id}}][option][{{$counter}}][value]" value="" data-key="{{$var->title}}">
                                                    </div>
                                                @elseif( !empty($var->type) && $var->type == 6 )
                                                    <input type="hidden" name="latitude" id="latitude" value="">
                                                    <input type="hidden" name="longitude" id="longitude" value="">
                                                    <input type="text" name="search_location" id="search_location" onkeyup="checkAddressString(this,'add')" placeholder="" class="form-control" value="">
                                                @else
                                                    <div class="checkbox checkbox-success form-check-inline">
                                                        <input type="checkbox" name="" value="{{$opt->id}}" class="dynamic_checkbox" data-key="{{$var->title}}">
                                                        <option value=""></option>
                                                        <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                                                    </div>
                                                @endif
                                                @php $counter++; @endphp
                                            @endif
                                            @endforeach
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    @endif
                    
</aside>
<!---End Left Sidebar filters -->