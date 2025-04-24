<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="w-100 common-table table-striped">
                <thead>
                    <tr>
                        <th>{{__('Order id')}}</th>
                        <th>{{__('Vendor')}}</th>
                       <th>{{__('Customer Name')}}</th>
                        <th>{{__('Product')}}</th>
                        <th>{{__('Product Price')}}</th>
                        <th>{{__('Order Date & Time')}}</th>
                        <th>{{__('Return Request Date & Time')}}</th>
                    </tr>
                </thead>
                <tbody>
                   
                    @if(isset($orders) && count($orders))    
                    @foreach ($orders as $order)
                    <tr data-id="{{ $order->id }}" class="show-return-product-modal"  data-status="{{$status}}">
                        <td>
                            #{{$order->order->order_number??''}}
                        </td>
                        <td class="vendor-name">
                            <a class="round_img_box mb-1" href="{{ route('vendor.show', $order->product->vendor_id) }}"><img class="rounded-circle" src="{{$order->product->vendor->logo['proxy_url'].'90/90'.$order->product->vendor->logo['image_path']}}" alt="{{$order->product->vendor->id}}"></a>
                            <a href="{{ route('vendor.show', $order->product->vendor_id) }}">{{$order->product->vendor->name??''}}</a>
                        </td>
                       
                        <td>
                            <a href="#">{{$order->returnBy->name??''}}</a>
                        </td>
                       <td class="product-name">
                            <img class="d-block mb-1" src="{{ $order->product->image['proxy_url'].'50/50'.$order->product->image['image_path']}}">
                            <b class="text-capitalize">
                                {{$order->product->product_name??''}}
                            </b>
                        </td>
                        <td class="">
                            <b class="text-black">{{$clientCurrency->currency->symbol}}{{number_format($order->product->price,2)??''}}</b>
                        </td>
                        <td>
                        @if(@$order->order->created_at)
                        {{ dateTimeInUserTimeZone($order->order->created_at, $timezone)}}
                        @endif
                    </td>
                        <td>{{ dateTimeInUserTimeZone($order->created_at, $timezone)}}</td>
                    </tr>
                    @endforeach
                @else
                <tr><td colspan="7">{{__('No Records Found')}}</td></tr>
                @endif   
                </tbody>
            </table>
        </div>
    </div>
    </div>