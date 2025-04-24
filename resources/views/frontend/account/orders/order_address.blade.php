@php
$orderAddress  = __('NA');
if(in_array($order->luxury_option_id,[2,3])){
    if ( count($order->vendors) >0)
    {
        $orderAddress  = $order->vendors->first() ? ($order->vendors->first()->vendor ? ($order->vendors->first()->vendor->address) : __('NA')) : __('NA') ;
    }
}else{
    if ($order->address){
        $orderAddress  =    $order->address->address .",". $order->address->street .",". $order->address->city.",". $order->address->state.",". $order->address->country .",". $order->address->pincode;
    }
}
@endphp

<span class="ellipsis alTTitle" data-toggle="tooltip"
    data-placement="top" title="{{$orderAddress}}">
    {{$orderAddress}}
</span>