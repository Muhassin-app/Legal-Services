@php
$show_long_term = (getAdditionalPreference(['is_long_term_service'])['is_long_term_service'] ==1)?1:0;
    $clientData = \App\Models\Client::select('socket_url')->first();
@endphp
@extends('layouts.store', ['title' => __('My Bids')])
@section('css')

<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
    input:invalid,
        input:out-of-range {
            border-color: hsl(0, 50%, 50%);
            background: hsl(0, 50%, 90%);
        }
    .error{
        font-size:10px;
        color:red;
    }
    .btn.btn-solid{
        padding: 6px 19px;
        margin: 2px;
    }
    li.bg-txt i {
        font-size: 15px;
    }
    label.rating-star.cancel_order, .rating-star.request_cancel_order {
        position: relative;
        left: 70px;
        top: 4px;
        background: #a22c7f;
        color: #fff;
        font-weight: 600;
        font-size: 10px;
        padding: 5px 10px 4px 10px;
        text-transform: uppercase;
    }
</style>
@endsection
@section('content')
    @php
    $timezone = Auth::user()->timezone;
    @endphp

    <style type="text/css">
        .productVariants .firstChild{min-width:150px;text-align:left!important;border-radius:0!important;margin-right:10px;cursor:default;border:none!important}.product-right .color-variant li,.productVariants .otherChild{height:35px;width:35px;border-radius:50%;margin-right:10px;cursor:pointer;border:1px solid #f7f7f7;text-align:center}.productVariants .otherSize{height:auto!important;width:auto!important;border:none!important;border-radius:0}.product-right .size-box ul li.active{background-color:inherit}.login-page .theme-card .theme-form input{margin-bottom:5px}.invalid-feedback{display:block}.al_body_template_one .order_popop .modal-body{padding:5px 15px 15px;background:#89898905;box-shadow:4px 10px 6px #838282}.al_body_template_one .order_popop p{font-size:13px;line-height:19px}.al_body_template_one .order_popop .modal-body textarea{border:1px solid#d9d3d3}.al_body_template_one .order_popop .modal-body textarea::placeholder{padding:5px 10px}.al_body_template_one .order_popop .modal-body button.close{position:absolute;right:5px;top:0;padding:0;margin:0}.al_body_template_one .order_popop .modal-body label{display:inline-block;font-size:18px!important;font-weight:400;}
    </style>
    <section class="section-b-space order-page">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-sm-left" id="wallet_response">
                        @if (\Session::has('success'))
                            <div class="alert alert-success">
                                <span>{!! \Session::get('success') !!}</span>
                            </div>
                            @php
                                \Session::forget('success');
                            @endphp
                        @endif
                        @if (\Session::has('error'))
                            <div class="alert alert-danger">
                                <span>{!! \Session::get('error') !!}</span>
                            </div>
                            @php
                                \Session::forget('error');
                            @endphp
                        @endif
                        <div class="message d-none">
                            <div class="alert p-0"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row my-md-3">
                <div class="col-lg-3">
                    <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                    <div class="dashboard-left mb-3">
                        <div class="collection-mobile-back"><span class="filter-back d-lg-none d-inline-block"><i
                                    class="fa fa-angle-left" aria-hidden="true"></i>{{ __('Back') }}</span></div>
                        @include('layouts.store/profile-sidebar')
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="dashboard-right">
                        <div class="dashboard">
                            <div class="page-title">
                                <h2>{{ __('My Bids') }}</h2>
                            </div>
                            <div class="order_response mt-3 mb-3 d-none">
                                <div class="alert p-0" role="alert"></div>
                            </div>
                            <div class="col-md-12">
                                <div class="row" id="orders_wrapper">
                                    <div class="col-sm-12 col-lg-12 tab-product al_custom_ordertabs mt-md-3 p-0">
                                        <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link {{ Request::query('pageType') === null || Request::query('pageType') == 'activeOrders' ? 'active show' : '' }}"
                                                    id="active-orders-tab" data-toggle="tab" href="#active-orders" role="tab"
                                                    aria-selected="true"><i
                                                        class="icofont icofont-ui-home"></i>{{ __('Active ') }}</a>
                                                <div class="material-border"></div>
                                            </li>
                                            {{-- <li class="nav-item">
                                                <a class="nav-link {{ Request::query('pageType') == 'pastOrders' ? 'active show' : '' }}"
                                                    id="past_order-tab" data-toggle="tab" href="#past_order" role="tab"
                                                    aria-selected="false"><i
                                                        class="icofont icofont-man-in-glasses"></i>{{ __('Past ') }}</a>
                                                <div class="material-border"></div>
                                            </li> --}}
                                          
                                            {{-- <li class="nav-item">
                                                <a class="nav-link {{ Request::query('pageType') == 'rejectedOrders' ? 'active show' : '' }}"
                                                    id="return_order-tab" data-toggle="tab" href="#rejected_order" role="tab"
                                                    aria-selected="false"><i
                                                        class="icofont icofont-man-in-glasses"></i>{{ __('Rejected/Cancel ') }}</a>
                                                <div class="material-border"></div>
                                            </li> --}}
                                        </ul>
                                        <div class="tab-content nav-material al" id="top-tabContent">
                                            <div class="tab-pane fade {{ Request::query('pageType') === null || Request::query('pageType') == 'activeOrders' ? 'active show' : '' }}"
                                                id="active-orders" role="tabpanel" aria-labelledby="active-orders-tab">
                                                <div class="row">
                                                    @if ($activeOrders->isNotEmpty())
                                                        @foreach ($activeOrders as $key => $order)
                                                        @php
                                                          $totalPrice = 0;
                                                        @endphp
                                                            <div class="col-12">
                                                                <div class="row no-gutters order_head">
                                                                    <div class="col-md-3 alOrderStatus">
                                                                        <h4>{{ __('Bid Number') }}</h4>
                                                                        <span>#{{ $order->bid_order_number }}</span>   
                                                                    </div>
                                                                    <div class="col-md-3 alOrderStatus">
                                                                        <h4>{{ __('Date & Time') }}</h4>
                                                                        <span>{{ dateTimeInUserTimeZone($order->created_at, $timezone) }}</span>
                                                                    </div>
                                                                    <div class="col-md-3 alOrderStatus">
                                                                        <h4>{{ __(getNomenclatureName('Vendor Name',true)) }}</h4>
                                                                        <span><a class="text-capitalize">{{ $order->vendor->name }}</a></span>
                                                                    </div>

                                                                    <div class="col-md-3 alOrderStatus">
                                                                        
                                                                        @if(@$order->status == 0)
                                                                            <div class="float-left">
                                                                                <a href="{{route('bid.accept',[$order->bid_req_id,$order->id])}}" class="btn btn-primary btn-sm m-2" onclick="return confirm('Are you sure?')" >{{ __('Accept')}}</a>
                                                                            </div>
                                                                            <div class="float-left">
                                                                                <a href="{{route('bid.reject',[$order->bid_req_id,$order->id])}}" class="btn btn-danger btn-sm m-2" onclick="return confirm('Are you sure?')" >{{ __('Reject')}}</a>
                                                                            </div>
                                                                        @elseif(@$order->status == 1)
                                                                            <div>
                                                                                <a href="#" class="btn btn-success btn-sm m-2" >{{ __('Accepted')  }}</a>
                                                                            </div>
                                                                        @else
                                                                            <div>
                                                                                <a href="#" class="btn btn-danger btn-sm m-2" >{{ __('Rejected')  }}</a>
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        </div>

                                                                </div>
                                                               
                                                                <div class="row mt-2">
                                                                    <div class="col-md-9 mb-3">
                                                                                                                                          
                                                                            <div class="order_detail order_detail_data align-items-top pb-3 card-box no-gutters mb-0">
                                                                                
                                                                            <table class="table table-centered table-nowrap table-striped" width="100%">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>{{ __('Product Name') }}</th>
                                                                                        <th>{{ __('Price') }}</th>
                                                                                        <th>{{ __('Quantity') }}</th>
                                                                                        {{-- <th>{{ __('Status') }}</th> --}}
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                @foreach ($order->bidProducts as $key => $products)
                                                                                    @php
                                                                                        $totalPrice += @$products->product->variant[0]->price * $products->quantity??0;
                                                                                    @endphp
                                                                                    <tr>
                                                                                        <td>{{$products->product->title}}</td>
                                                                                        <td>{{decimal_format($products->product->variant[0]->price)}}</td>
                                                                                        <td>{{$products->quantity}}</td>
                                                                                        
                                                                                    </tr>
                                                                                @endforeach
                                                                                </tbody>
                                                                            </table>

                                                                            </div>
                                                                        </div>

                                                                         @php
                                                                            $disTotalPrice = $totalPrice*$order->discount/100;
                                                                        @endphp

                                                                    <div class="col-md-3 mb-3 pl-lg-0">
                                                                        <div class="card-box p-2 mb-0 h-100">
                                                                            <ul class="price_box_bottom m-0 pl-0 pt-1">
                                                                                <li
                                                                                    class="d-flex align-items-center justify-content-between">
                                                                                    <label
                                                                                        class="m-0">{{ __('Discount') }}</label>
                                                                                    <span>{{$order->discount}}%</span>
                                                                                </li>
                                                                                
                                                                                
                                                                                    <li
                                                                                        class="d-flex align-items-center justify-content-between">
                                                                                        <label
                                                                                            class="m-0">{{ __('Product Total Amount') }}</label>
                                                                                        <span>{{decimal_format($totalPrice)}}</span>
                                                                                    </li>
                                                                                
                                                                                    <li
                                                                                        class="d-flex align-items-center justify-content-between">
                                                                                        <label
                                                                                            class="m-0">{{ __('Disount Amount') }}</label>
                                                                                        <span>{{decimal_format($disTotalPrice)}}</span>
                                                                                    </li>

                                                                               
                                                                                <li class="grand_total d-flex align-items-center justify-content-between">
                                                                                    <label
                                                                                        class="m-0">{{ __('Total Payable') }}</label>
                                                                                    <span>{{decimal_format($totalPrice - $disTotalPrice)}}</span>
                                                                                </li>
                                                                               
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="col-12">
                                                            <div class="no-gutters order_head">
                                                                <h4 class="text-center">{{ __('No Active Order Found') }}
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                {{ $activeOrders->appends(['pageType' => 'activeOrders'])->links() }}
                                            </div>

                                                                                       
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-account box-info">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection

@section('script')
    <script type="text/javascript">
      
        $('.acceptBtn').on("click", function () {
            var bid_req_Id = $(this).attr("data-accept-req-id");
            var bid_Id = $(this).attr("data-accept-id");
           
            $.ajax({
                type: "post",
                dataType: 'json',
                url: "{{route('bid.accept')}}",
                data:  `bid_req_Id=${bid_req_Id}&bid_Id=${bid_Id}`,
                success: function (response) {
                    if (response.status == 'Success') {
                        window.location.reload();
                    }
                }
            });

        });

    
</script>
@endsection
