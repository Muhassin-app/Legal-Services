<h5 class="modal-title">
    Return Product
</h5>
<form class="return-order-form" action="{{route('update.rental.product.return')}}" method="post">
    @csrf
    <div class="table-responsive">
        <table class="w-100">
            
            <tbody>
                @php $continue = 0; @endphp
           
               
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            @if(isset($product->productReturn->status))
                             {{ __($product->productReturn->status) }}
                            @else
                            @php $continue = 1; @endphp
                            <input id="item_one" type="hidden" name="order_vendor_product_id" value="{{ $product->id }}" required>
                            <input id="request_type" type="hidden" name="request_type" value="{{ $type }}" required>
                            @endif
                            <label class="order-items d-flex" for="item_one">  
                                <div class="item-img mx-1">
                                    @if($product->pvariant->media->isNotEmpty())
                                        <img src="{{ $product->pvariant->media->first()->pimage->image->path['image_fit'].'74/100'.$product->pvariant->media->first()->pimage->image->path['image_path'] }}" alt="">
                                    @else
                                        <img src="{{ $product->image['image_fit'].'74/100'.$product->image['image_path'] }}" alt="">
                                    @endif
                                </div>    
                                <div class="items-name ml-2">
                                    <h4 class="mt-0 mb-1"><b>{{ $product->product_name }}</b></h4>
                                    <label><b>Quantity</b>: {{ $product->quantity }}</label>
                                </div>
                            </label>
                        </div>
                    </td>
                    <td class="order_address">
                        @if($product->order->address)
                            {{$product->order->address->address}}, {{$product->order->address->street}}, {{$product->order->address->city}}, {{$product->order->address->state}}, {{$product->order->address->country}} {{$product->order->address->pincode}}
                        @else
                        {{__('NA')}}
                        @endif
                    </td>
                    
                </tr>
               
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center" colspan="3">
                        @if($continue == 1)
                        <button class="btn btn-solid" type="submit">Continue</button>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</form>