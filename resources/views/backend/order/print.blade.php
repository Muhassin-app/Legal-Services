<div class="al_print_area" style="background-color: #fff;padding: 10px" id="al_print_area">
    <table class="table table-borderless" cellspacing="0" border="0" width="100%" cellspacing="0">
        <tr>
            <td>
                <div class="al_print_header" style="width: 100%;">
                    <h5 style="color: #000;margin: 0px;font-family: Lato,sans-serif;display: inline-block;vertical-align: middle;">
                      
                        </h5>
                        <ul style="padding: 0; margin: 0;">
                            @if($order->luxury_option_name != '')
                                <li style="display: inline-block;vertical-align: middle;margin-right: 10px"><span style="font-family: Lato,sans-serif; background-color: #05C3DF;color: #fff;border-radius: 10px;font-size: 12px;padding: 2px 7px">{{ $order->luxury_option_name ?? 'Delivery' }}</span></li>
                            @endif
                            <li style="display: inline-block;vertical-align: middle;"><span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Items from Order") }} #{{$order->order_number}}</span></li>
                        </ul>
                    </div>
                </td>
            </tr>
            <tr>
                <td height="15"></td>
            </tr>

            <tr>
                <td>
                    <table  width="100%" cellpadding="0" border="0" cellspacing="0" class="table table-border table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th align="left">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{ __("Product Name") }}</p>
                                </th>
                                <th align="left">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{ __("Product") }}</p>
                                </th>
                                <th align="left">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{ __("Quantity") }}</p>
                                </th>
                                <th align="left">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{ __("Price") }}</p>
                                </th>
                                <th align="left">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{ __("Total") }}</p>
                                </th>
                            </tr>
                        </thead>
                        @foreach($order->vendors as $vendor)
                        <tbody>
                            @php
                                $sub_total = 0;
                                $taxable_amount = 0;
                                $vendor_service_fee = 0;
                            @endphp
                            @foreach($vendor->products as $product)
                            @if($product->order_id == $order->id)
                                @php
                                    $taxable_amount = $vendor->taxable_amount;
                                    $vendor_service_fee = $vendor->service_fee_percentage_amount;
                                    $sub_total += $product->total_amount;
                                @endphp
                            <tr>
                                <td style="width: 30%" scope="row" valign="top">
                                    <p style="vertical-align: top; font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif;">{{$product->product_name}}</b>

                                    </p>
                                   
                                    @if($product->product_variant_sets)
                                    <p style="vertical-align: top; margin: 0;padding: 10px;">{{ substr($product->product_variant_sets, 0, -2) }}</p>
                                    @endif

                                    @php $add_count = count($product->addon); @endphp
                                    @if($product->addon && $add_count)
                                        <ul style="margin: 0;vertical-align: top;padding-top: 0px; padding-left: 30px;">
                                            @foreach($product->addon as $key=>$addon)
                                            <li style="font-size: 14px;font-family: Lato,sans-serif;">{{ $addon->option->translation_title }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>

                                <td>
                                    <p style="font-size: 14px;vertical-align: top;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                    @if($product->image_path)
                                        <img style="height: 30px;" src="{{@$product->image_path['proxy_url'].'32/32'.@$product->image_path['image_path']}}" >
                                    @else
                                        @php $image_path = getDefaultImagePath(); @endphp
                                        <img style="height: 30px;" src="{{$image_path['proxy_url'].'32/32'.$image_path['image_path']}}" >
                                    @endif
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{ $product->quantity }}</p>
                                </td>
                                <td style="width: 20%">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$clientCurrency->currency->symbol}}{{decimal_format($product->price)}} </p>
                                    @if($product->product_variant_sets)
                                    <p style="vertical-align: top; margin: 0;padding: 10px;"></p>
                                    @endif
                                    @if($product->addon && $add_count) 
                                        <ul style="padding-left: 10px;margin: 0;">
                                            @foreach($product->addon as $key=>$addon)
                                            <li style="list-style: none;font-size: 14px;font-family: Lato,sans-serif;">{{$clientCurrency->currency->symbol}}{{ decimal_format($addon->option->price_in_cart) }} </li>
                                            @endforeach
                                        </ul>                                           
                                        
                                        @endif

                                   
                                </td>
                                <td>
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$clientCurrency->currency->symbol}}{{decimal_format($product->total_amount)}}</p>
                                </td>
                            </tr>
                            @endif

                            @endforeach
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif;">{{__('Delivery Fee')}} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$clientCurrency->currency->symbol}}{{decimal_format($vendor->delivery_fee)}}</p></td>
                            </tr>
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Sub Total") }} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$clientCurrency->currency->symbol}}{{decimal_format($sub_total)}}</p></td>
                            </tr>
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif;">{{__('Total Discount')}} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"> -{{$clientCurrency->currency->symbol}}{{decimal_format($order->total_discount)}}</p></td>
                            </tr>
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Estimated Tax") }} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$clientCurrency->currency->symbol}}{{ (decimal_format($taxable_amount) != 0 ) ? decimal_format($taxable_amount):$tax_amount}}</p></td>
                            </tr>
                            @if($vendor_service_fee > 0)
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Service Fee") }} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px;">{{$clientCurrency->currency->symbol}}{{decimal_format($vendor_service_fee)}}</p></td>
                            </tr>
                            @endif
                            {{--  <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif; width:200px;">{{$client_head->name}} {{ __("Revenue") }} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$clientCurrency->currency->symbol}}@money($revenue)</p></td>
                            </tr>
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif; width:200px;">{{ __("Store Earning") }} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$clientCurrency->currency->symbol}}@money($vendor->payable_amount * $clientCurrency->doller_compare - $revenue)</p></td>
                            </tr> --}}
                            @if($vendor->reject_reason)
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif; width:200px;">{{ __("Reject Reason") }} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$vendor->reject_reason}}</p></td>
                            </tr>
                            @endif

                            @if($order->tip_amount)
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif; width:200px;">{{ __("Tip Amount") }} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{decimal_format($order->tip_amount)}}</p></td>
                            </tr>
                            @endif
                            <tr>
                                <td scope="row" colspan="4">
                                    <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px"><b style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Total") }} :</b></p>
                                </td>
                                <td><p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">{{$clientCurrency->currency->symbol}}{{decimal_format(( $vendor->payable_amount * $clientCurrency->doller_compare )  + ($order->tip_amount ?? 0) )}}</p></td>
                            </tr>
                        </tbody>
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr><td height="25"></td></tr>
            <tr>
                <td>
                    <table width="100%">
                        <td align="left" valign="top">
                            @if($order->address)
                            <table>
                                <thead>
                                    <tr>
                                        <th align="left"><p style="font-size: 18px;font-family: Lato,sans-serif;margin: 0;padding: 10px"> {{ __("Delivery Information") }}</p> </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                                <b style="font-size: 14px;font-family: Lato,sans-serif;">{{$order->user->name}}</b>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                                <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Email") }} :</span> {{ $order->user->email ? $order->user->email : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @if(!is_null($order->user) && isset($order->user->phone_number))
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Phone')}} :</span> {{'+'.$order->user->dial_code.$order->user->phone_number}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Address") }} :</span> {{ $order->address->house_number ? $order->address->house_number."," : ''}} {{ $order->address ? $order->address->address : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @if(isset($order->address) && !empty($order->address->street))
                                     <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{__('Street')}} :</span> {{ $order->address ? $order->address->street : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    @if(isset($order->address) && !empty($order->address->city))
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{__('City')}} :</span> {{ $order->address ? $order->address->city : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    @if(isset($order->address) && !empty($order->address->state))
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("State") }} :</span> {{ $order->address ? $order->address->state : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ getNomenclatureName('Zip Code', true) }} :</span> {{ $order->address ? $order->address->pincode : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            @elseif( ($order->luxury_option_id == 2) || ($order->luxury_option_id == 3) )
                            <table>
                                <thead>
                                    <tr>
                                        <th align="left"><p style="font-size: 18px;font-family: Lato,sans-serif;margin: 0;padding: 10px"> {{ __("User Information") }}</p> </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                                <b style="font-size: 14px;font-family: Lato,sans-serif;">{{$order->user->name}}</b>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                                <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Address") }} :</span> {{ $order->user->address && $order->user->address->first() ? $order->user->address->first()->address : __('Not Available')}}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("Mobile") }} :</span> {{$order->user->phone_number ? $order->user->phone_number : __('Not Available')}}
                                            </p>
                                        </td>
                                    </tr>
                                    @if(isset($order->address) && !empty($order->address->street))
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{__('Street')}} :</span> {{ $order->address ? $order->address->street : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{__('City')}} :</span> {{ $order->address ? $order->address->city : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __("State") }} :</span> {{ $order->address ? $order->address->state : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ getNomenclatureName('Zip Code', true) }} :</span> {{ $order->address ? $order->address->pincode : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            @endif

                        </td>
                        <td align="left" valign="top">
                            <table>
                                <thead>
                                    <tr>
                                        <th align="left"><p style="font-size: 18px;font-family: Lato,sans-serif;margin: 0;padding: 10px"> {{ __('Payment Information') }}</p> </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Payment By') }} :</span> {{ $order->paymentOption  ? $order->paymentOption->title : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @if($order->payment)
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Transaction Id') }} :</span> {{ $order->payment  ? $order->payment->transaction_id : ''}}
                                            </p>
                                        </td>
                                    </tr>
                                     @endif
                                    <tr>
                                        <td height="20"></td>
                                    </tr>

                                </tbody>
                            </table>
                            <table>
                                <thead>
                                    <tr>
                                        <th align="left"><p style="font-size: 18px;font-family: Lato,sans-serif;margin: 0;padding: 10px"> {{ __('Comment/Schedule Information') }}</p> </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($order->comment_for_pickup_driver)
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Comment for Pickup Driver') }} :</span> {{ $order->comment_for_pickup_driver ?? ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    @if($order->comment_for_dropoff_driver)
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Comment for Dropoff Driver') }} :</span> {{ $order->comment_for_dropoff_driver ?? ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    @if($order->comment_for_vendor)
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Comment for Vendor') }} :</span> {{ $order->comment_for_vendor ?? ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    @if($order->schedule_pickup)
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Schedule Pickup') }} :</span> {{dateTimeInUserTimeZone($order->schedule_pickup, $timezone)}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    @if($order->schedule_dropoff)
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Schedule Dropoff') }} :</span> {{dateTimeInUserTimeZone($order->schedule_dropoff, $timezone)}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    @if($order->specific_instructions)
                                    <tr>
                                        <td align="left">
                                            <p style="font-size: 14px;font-family: Lato,sans-serif;margin: 0;padding: 10px">
                                            <span style="font-size: 14px;font-family: Lato,sans-serif;">{{ __('Specific instructions') }} :</span> {{ $order->specific_instructions ?? ''}}
                                            </p>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td height="20"></td>
                                    </tr>

                                </tbody>
                            </table>
                        </td>
                    </table>
                </td>

            </tr>
        </table>
    </div>