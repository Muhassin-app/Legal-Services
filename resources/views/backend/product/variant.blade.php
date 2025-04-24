
<div class="card-box" >
    <div class="row mb-2 bg-light">
        <div class="col-8" >
            <h5 class="text-uppercase mt-0 bg-light p-2">{{ __("Variant Information") }}</h5>
        </div>
        {{-- @if($productVariants->count() > 0)
        <div class="col-4 p-2 mt-0 text-right" style="margin:auto; ">
            <button type="button" class="btn btn-info makeVariantRow"> {{ __("Make Variant Sets") }}</button>
        </div>
        @endif --}}
    </div>
    {{-- <p>{{ __("Select or change category to get variants") }}</p> --}}

    <div class="row" style="width:100%; overflow-x: scroll;">
        {{-- <div id="variantAjaxDiv" class="col-12 mb-2">
            <h5 class="">{{__('Variant List')}}</h5>
            <div class="row mb-2">
                @foreach($productVariants as $vk => $var)
                <div class="col-sm-3">
                    <label class="control-label">{{$var->title??null}}</label>
                </div>
                <div class="col-sm-9">
                    @foreach($var->option as $key => $opt)
                    @if(isset($opt) && !empty($opt->title) && isset($var) && !empty($var->title) )
                        <div class="checkbox checkbox-success form-check-inline pr-3">
                            <input type="checkbox" name="variant{{$var->id}}" class="intpCheck" opt="{{$opt->id.';'.$opt->title}}" varId="{{$var->id.';'.$var->title}}" id="opt_vid_{{$opt->id}}" @if(in_array($opt->id, $existOptions)) checked @endif>
                            <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label>
                        </div>
                    @endif
                    @endforeach
                </div>
                @endforeach
            </div>
        </div> --}}

        {{-- @if($product->has_variant == 1) --}}
        <div class="col-12" id="exist_variant_div">
            <h5 class="">{{ __("Applied Variants Set") }}</h5>
            <table class="table table-centered table-nowrap table-striped">
                <thead>
                    <th>{{ __("Image") }}</th>
                    <th>{{ __("Name") }}</th>
                    {{-- <th>{{ __("Variants Sets") }}</th> --}}
                        @if($productVariants)
                            @foreach($productVariants as $vk => $var)
                                <th>{{$var->title??null}}</th>
                            @endforeach
                        @endif
                    <th>{{ __("Price") }}</th>
                    <th>{{ __('Duration (Hrs.)') }}</th>
                    <th>{{ __('Incremental Price') }}</th>
                    <th>{{ __("Action") }}</th>
                </thead>
                <tbody id="product_tbody_{{$product->id}}" class="product_variant_table">
                    @php
                        $variant_count = count($product->variant)-1;
                    @endphp
                    @foreach($product->variant as $key => $varnt)
                     @include('backend.product.part.addRows')
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- @endif --}}
        <div id="variantRowDiv" class="col-12"></div>
    </div>
</div>
@section('js-script')
<script>

</script>
@endsection