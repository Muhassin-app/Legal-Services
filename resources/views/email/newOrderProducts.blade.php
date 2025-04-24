@php
$timezone = @$user->timezone;
@endphp

<tr>
   <td colspan="2" style="text-align: center;">
       <h2 style="color: #000000;font-size: 15px;font-weight: 500;letter-spacing: 0;line-height: 19px;">{{__('ORDER NO')}}. {{@$order->order_number}} (<span style="color:{{!empty(getClientPreferenceDetail()) ? getClientPreferenceDetail()->primary_color : '#000'}};"><strong>{{getNomenclatureName($luxuryOptionTitle ?? '')}}</strong></span>)</h2>
       <p style="opacity: 0.41;color: #000000;font-size: 12px;letter-spacing: 0;line-height: 15px;">{{ dateTimeInUserTimeZone(@$order->created_at, @$timezone) }}</p>
   </td>
</tr>

@foreach($cartData->products as $product)

<tr>
   <td colspan="2" style="background-color: #d8d8d85e;">
      <table style="width: 100%;">
         <thead>
            <tr>
               <th style="padding-right: 0;padding-left: 0;font-weight: 400;">
                  <label style="height: 22px;width: 22px;background-color: #ddd;border-radius: 50%;display: inline-block;vertical-align: middle;margin-right: 5px;"></label>
                  <span style="color: #000000;font-size: 13px;letter-spacing: 0;line-height: 18px;">{{@$product['vendor']['name']}}</span>
               </th>
               <th style="text-align: right;padding-right: 0;padding-left: 0;color: #000000;font-size: 13px;letter-spacing: 0;line-height: 18px;font-weight: 400;">
                  {{$product['vendor']['address']}}
               </th>
            </tr>
         </thead>
      </table>
   </td>
</tr>
@php
   $total_products = 0;
@endphp
<tr>
  <td colspan="2">
      <table style="width: 100%;">
         <thead>
            <tr>
               <th colspan="3" style="padding: 10px 0 0; opacity: 0.44;color: #000000;font-size: 12px;letter-spacing: 0;line-height: 15px;">
                  <span>{{__('ITEMS ORDERED')}}</span>
               </th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <td colspan="3" style="border-bottom: 1px solid #9797973b;padding: 3px 0 10px;"></td>
            </tr>
            @foreach($product['vendor_products'] as $vendor_product)
            <tr style="vertical-align: top;">
               <td style="width: 45%;padding: 15px 0 10px; ">
                  <div style="display: flex;">
                    @php
                    $img = 'https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/default/default_image.png';
                    if(isset($vendor_product['product']['media'][0])){
                        $imga = $vendor_product['product']['media'][0]['image']['path']['image_fit'].'100/100'.$vendor_product['product']['media'][0]['image']['path']['image_path'];
                        $img = $vendor_product['product']['media'][0]['image']['path']['original_image'];
                    }
                    @endphp
                     <div style=" height:auto;width: 60px;background-color: #D8D8D8;">
                       {{-- <img style="width: 100px;height: 100px;border-radius: 3px;object-fit: cover;" src="{{$img}}" alt=""> --}}
                        <img style="width: 100px;height: 100px;border-radius: 3px;object-fit: cover;" src="{{$img}}" alt="">
                     </div>
                     <div style="padding: 0 0 0 45px;">
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
                     $vendor_product['pvariant']['price'] = $vendor_product['pvariant']['price'] + $addon['option']['price_in_cart']
                  @endphp
                  <h3 style="color: #000000;font-size: 15px;letter-spacing: 0;line-height: 19px;margin: 0 0 3px;">{{ $currencySymbol . decimal_format((@$addon['option']['quantity_price']))}}</h3>
                  @endforeach
                  @endif
                  {{-- <p style="font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;color: #777777;">$90.00</p>
                  <p style="font-size: 15px;letter-spacing: 0;line-height: 18px;margin: 0 0 3px;color: #777777;">$90.00</p> --}}
                  <!-- <h3 style="color: #000000;font-size: 15px;letter-spacing: 0;line-height: 19px;margin: 5px 0 0;padding: 5px 0 0;color: #000000;display: inline-block;border-top: 1px solid #ddd;min-width: 80px;">{{ $currencySymbol . decimal_format(($vendor_product['pvariant']['price']*$vendor_product['quantity']))}}</h3> -->
               </td>
            </tr>

            @php
               $total_products += $vendor_product['pvariant']['price'];
            @endphp

            <tr>
               <td colspan="3" style="border-bottom: 1px solid #9797973b;padding: 2px 0;"></td>
            </tr>
            @endforeach

         </tbody>
      </table>
  </td>
</tr>


@endforeach



<tr>
   <td colspan="2">
       <table class="order-total-price" style="width: 100%;">
          <tbody>
             <tr>
                <td style="text-align: left;"><b>{{__('Subtotal')}}:</b></td>
                <td style="text-align: right;">{{$currencySymbol . decimal_format($order->total_amount)}}</td>
             </tr>
             <tr>
                <td style="text-align: left;"><b>{{__('Tax')}}:</b></td>
                <td style="text-align: right;">{{$currencySymbol . decimal_format($order->taxable_amount)}}</td>
             </tr>
            @if(!in_array($order->luxury_option_id,[2,3]))
             <tr>
               <td style="text-align: left;"><b>{{__('Delivery Fee')}}:</b></td>
               <td style="text-align: right;">{{$currencySymbol . decimal_format($order->total_delivery_fee)}}</td>
            </tr>
            @endif
            @if($order->tip_amount > 0)
            <tr>
               <td style="text-align: left;"><b>{{__('Tip')}} :</b></td>
               <td style="text-align: right;">{{$currencySymbol . decimal_format($order->tip_amount)}}</td>
            </tr>
            @endif
            <tr>
               <td style="text-align: left;"><b>{{__('Service fee')}}:</b></td>
               <td style="text-align: right;">{{$currencySymbol . decimal_format($order->total_service_fee)}}</td>
            </tr>
             <tr>
                <td style="text-align: left;"><b>{{__('Total')}}:</b></td>
                <td style="text-align: right;"><b>{{$currencySymbol . decimal_format($order->payable_amount + $order->loyalty_amount_saved + $order->total_discount)}}</b></td>
             </tr>
          </tbody>
       </table>
   </td>
 </tr>


<!-- total amount -->
<tr>
  <td colspan="2" style="padding-top: 0;">
      <table class="order-total-price" style="width: 100%;background-color: rgba(216,215,215,0.2);padding: 10px;">
         <tbody>
            @if($order->loyalty_amount_saved > 0)
            <tr>
               <td style="text-align: left;"><b>{{__('Loyalty')}} :</b></td>
               <td style="text-align: right;">{{$currencySymbol . decimal_format($order->loyalty_amount_saved)}}</td>
            </tr>
            @endif

            @if($order->total_discount > 0)
            <tr>
               <td style="text-align: left;"><b>{{__('Discount')}} :</b></td>
               <td style="text-align: right;"><b>{{$currencySymbol . decimal_format($order->total_discount)}}</b></td>
            </tr>
            @endif
            <tr>
               <td colspan="2" style="padding: 10px 0 20px;">
                  <span style="border-bottom: 1px solid rgb(151 151 151 / 23%);display: block;"></span>
               </td>
            </tr>
            <tr style=" color: #308FE4;font-size: 15px;font-weight: 600;line-height: 19px;">
               <td style="text-transform: uppercase;">{{ ($order->payment_option_id == 1) ? __('Cash to be collected') : __('Amount paid')}}:</td>
               <td style="text-align: right;">{{$currencySymbol . decimal_format($order->payable_amount)}}</td>
            </tr>
         </tbody>
      </table>
  </td>
</tr>
<!-- payment type , customer address -->
<tr>
   <td colspan="2" style="padding-top: 0;">
      <table class="payment-method" style="width: 100%;">
         <thead>
            <tr style="color: rgb(0 0 0 / 44%);font-size: 12px;line-height: 15px;">
               <th>{{__('Payment Method')}}</th>
               <th style="text-align: right;">{{__('Customer Details')}}</th>
            </tr>
         </thead>
         <tbody>
            <tr style="vertical-align: top;">
               <td style="width: 40%;">

                  @if($order->payment_option_id != '1')
                  <div style="margin: 0 0 5px;">
                     <label style="vertical-align: middle;width: 45px;display: inline-block;"><img style="width: 40px;" src="https://cdn-icons-png.flaticon.com/512/6356/6356353.png" alt=""></label>
                     <span style="vertical-align: middle;font-size: 13px;line-height: 18px;color: #000000;">{{__('Card')}}</span>
                  </div>
                  @else
                  <div style="margin: 0 0 5px;">
                     <label style="vertical-align: middle;width: 45px;display: inline-block;"><img style="width: 30px;" src="https://cdn-icons-png.flaticon.com/512/1019/1019709.png" alt=""></label>
                     <span style="vertical-align: middle;font-size: 13px;line-height: 18px;color: #000000;">{{__('Cash on Delivery')}}</span>
                  </div>
                  @endif

               </td>
               <td style="width: 60%;text-align: right;font-size: 13px;line-height: 18px;color: #000000;">
                  <p style="width: 240px;margin-left: auto;">{{@$user->name}}
                     @php
                     $address ="";
                     $address_arr = \App\Models\UserAddress::where(['id' => $order->address_id])->first();
                     if(!empty($address_arr)){
                        $address = $address_arr->address . ', ' . $address_arr->state . ', ' . $address_arr->country . ', ' . $address_arr->pincode;
                     }
                     @endphp
                     {{$address}}
                    <a style="display: block;color: #32C5FF;" href="mailto:{{@$user->email}}">{{@$user->email}}</a>
                    {{@$user->dial_code}}{{@$user->phone_number }}</p>
               </td>
            </tr>
         </tbody>
      </table>
  </td>

</tr>



<tr>
   <td colspan="2" style="padding: 5px 15px;">
      <span style="border-bottom: 1px solid rgb(151 151 151 / 23%);display: block;"></span>
   </td>
</tr>

