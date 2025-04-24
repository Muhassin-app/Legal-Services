<div class="container">
    <div class="row al_bg_color">
@foreach ($expected_vendors as  $key => $vendor)
 @php
 $total_price = 0;    
 @endphp

    <div class="col-md-6">
{{ $vendor->name }}
<div id="tbody_{{$vendor->id}}">
    @foreach($vendor->products as $vendor_product)
    
        <div class="row align-items-md-center vendor_products_tr pt-2" id="tr_vendor_products_{{ $vendor_product->id }}">
            <div class="product-img col-4 col-md-2">
                @php
                $image_url = $vendor_product->media->first() ? $vendor_product->media->first()->image->path['image_fit'] . '600/600' . $vendor_product->media->first()->image->path['image_path'] : '';
                @endphp
                
                <img class='blur-up lazyload w-100 mb-2' data-src="{{  $image_url }}">
              
            </div>
            <div class="col-8 col-md-10">
                <div class="row align-items-md-center">
                    <div class="col-md-8 order-0">
                        <h4 class="mt-0 mb-1">{{ isset($vendor_product->translation) ? $vendor_product->translation->first()->title :  $vendor_product->sku }}</h4>
                      
                    </div>
                    <div class="col-md-4 text-md-center order-1 mb-1 mb-md-0">
                        <div class="items-price">{{Session::get('currencySymbol').(decimal_format($vendor_product->variant->first()->price  * $clientCurrency->doller_compare))}}</div>
                        @php
                            $total_price = $total_price + $vendor_product->variant->first()->price;
                        @endphp
                    </div>
                   
                </div>
               
            </div>

        </div>
    @endforeach

    
        <div class="row al_total_sum d-flex justify-content-between border-top pl-4 pr-4 py-3">
            <button class="btn btn-solid w-50 login_continue_btn" type="button">{{__('Add')}}</button>
            <p>{{Session::get('currencySymbol').(decimal_format($total_price  * $clientCurrency->doller_compare))}}</p>
        </div>
</div>
</div>

<hr>
@endforeach
</div>
</div>


