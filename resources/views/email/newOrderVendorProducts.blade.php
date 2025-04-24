@php
$timezone = @$user->timezone;
@endphp

<tr>
   <td colspan="2" style="text-align: center;">
       <h2 style="color: #000000;font-size: 15px;font-weight: 500;letter-spacing: 0;line-height: 19px;">{{__('ORDER NO')}}. {{@$order->order_number}} (<span style="color:{{!empty(getClientPreferenceDetail()) ? getClientPreferenceDetail()->primary_color : '#000'}};"><strong>{{getNomenclatureName($luxuryOptionTitle ?? '')}}</strong></span>)</h2>
       <p style="opacity: 0.41;color: #000000;font-size: 12px;letter-spacing: 0;line-height: 15px;">{{ dateTimeInUserTimeZone(@$order->created_at, @$timezone) }}</p>
   </td>
</tr>
<tr>
   <td>
       @foreach($cartData->products as $product)
       @if($product['vendor_id'] == $id)
       <tr>
          <td colspan="2" style="background-color: #d8d8d85e;">
             <table style="width: 100%;">
                <thead>
                   <tr>
                      <th style="padding-right: 0;padding-left: 0;font-weight: 400;">
                         <label style="height: 22px;width: 22px;background-color: #ddd;border-radius: 50%;display: inline-block;vertical-align: middle;margin-right: 5px;"></label>
                         <span style="color: #000000;font-size: 13px;letter-spacing: 0;line-height: 18px;">{{$product['vendor']['name']}}</span>
                      </th>
                      <th style="text-align: right;padding-right: 0;padding-left: 0;color: #000000;font-size: 13px;letter-spacing: 0;line-height: 18px;font-weight: 400;">
                         {{$product['vendor']['address']}}
                      </th>
                   </tr>
                </thead>
             </table>
          </td>
       </tr>
       <table  border="0" cellpadding="0" cellspacing="0" align="left" style="width: 100%;margin-bottom: 50px;">
          <tr>
              <td colspan="3" style="border-bottom: 1px solid #9797973b;padding: 3px 0 10px;"></td>
           </tr>
           @php
           $total_products = 0;
           @endphp
           @foreach($product['vendor_products'] as $vendor_product)
           <tr style="vertical-align: top;">
             <td style="width: 45%;padding: 15px 0 10px; ">
                <div style="display: flex;">
                   <div style=" height: 60px;width: 60px;background-color: #D8D8D8;">
                      @php
                      $img = '';
                      if(isset($vendor_product['product']['media'][0])){
                         $imga = $vendor_product['product']['media'][0]['image']['path']['image_fit'].'100/100'.$vendor_product['product']['media'][0]['image']['path']['image_path'];
                         $img = $vendor_product['product']['media'][0]['image']['path']['original_image'];
                      }
                      @endphp
                      {{-- <img style="width: 100%;height: 100%;border-radius: 3px;object-fit: cover;" src="{{ $img }}" alt=""> --}}
                      <img style="width: 100%;height: 100%;border-radius: 3px;object-fit: cover;" src="{{ $img }}" alt="">
                   </div>
                   <div style="padding: 0 0 0 15px;">
                      <h3 style="color: #000000;font-size: 15px;letter-spacing: 0;line-height: 19px;margin: 0 0 3px;">{{ ($vendor_product['product']['translation_one']['title'] ?? false) ? $vendor_product['product']['translation_one']['title'] : "" }}</h3>
                      @if(count($vendor_product['addon']))
                         @foreach ($vendor_product['addon'] as $addon)
                         <p style="color: #777777;font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;">{{$addon['option']['title']??''}}</p>
                         @endforeach
                      @endif
                      {{-- <p style="color: #777777;font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;">Extra olives</p>
                      <p style="color: #777777;font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;">Extra cheese</p> --}}
                   </div>
                </div>
             </td>
             <td style="width: 20%;padding: 15px 10px 10px; text-align: center;">
                <h3 style="color: #000000;font-size: 15px;letter-spacing: 0;line-height: 19px;margin: 0 0 3px;">x {{$vendor_product['quantity']}}</h3>
                {{-- <p style="font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;color: #777777;">x 1</p>
                <p style="font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;color: #777777;">x 1</p> --}}
             </td>
             <td style="width: 35%;padding: 15px 0 10px;  text-align: right;">
                <h3 style="color: #000000;font-size: 15px;letter-spacing: 0;line-height: 19px;margin: 0 0 3px;">{{ $currencySymbol . decimal_format(($vendor_product['pvariant']['price']))}}</h3>
                @if(count($vendor_product['addon']))
                   @foreach ($vendor_product['addon'] as $addon)
                   @php
                      $vendor_product['pvariant']['price'] = $vendor_product['pvariant']['price'] + $addon['option']['price_in_cart'];
                   @endphp
                   {{-- <p style="font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;color: #777777;">$90.00</p>
                   <p style="font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;color: #777777;">$90.00</p> --}}
                   <h3 style="color: #000000;font-size: 15px;letter-spacing: 0;line-height: 19px;margin: 5px 0 0;padding: 5px 0 0;color: #000000;display: inline-block;border-top: 1px solid #ddd;min-width: 80px;">{{ $currencySymbol . decimal_format(($vendor_product['pvariant']['price']*$vendor_product['quantity']))}}</h3>
                   @endforeach
                @endif
             </td>
          </tr>

         <tr>
            <td colspan="3" style="border-bottom: 1px solid #9797973b;padding: 5px 0;"></td>
         </tr>
         @endforeach
         <tr>
            <td style="text-align: left;"><b>{{__('Subtotal')}}:</b></td>
            <td></td>
            <td style="text-align: right;">{{$currencySymbol . decimal_format($total_products)}}</td>
         </tr>

         <tr>
            <td style="text-align: left;"><b>{{__('TAX')}}:</b></td>
            <td></td>
            <td style="text-align: right;">{{$currencySymbol . decimal_format($product['taxable_amount'])}}</td>
         </tr>

            @if(!in_array($order->luxury_option_id,[2,3]))
             <tr>
               <td style="text-align: left;"><b>{{__('Delivery Fee')}}:</b></td>
               <td style="text-align: right;">{{$currencySymbol . decimal_format($order->total_delivery_fee)}}</td>
            </tr>
            @endif

         <tr>
            <td style="text-align: left;"><b>{{__('Discount')}}:</b></td>
            <td></td>
            <td style="text-align: right;">{{$currencySymbol . decimal_format($product['discount_amount'])}}</td>
         </tr>

         <tr>
            <td style="text-align: left;"><b>{{__('Total')}}:</b></td>
            <td></td>
            <td style="text-align: right;">{{$currencySymbol .decimal_format($product['payable_amount'])}}</td>
         </tr>


     </table>
     @endif
     @endforeach
 </td>

</tr>
