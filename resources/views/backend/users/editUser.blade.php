
@extends('layouts.vertical', ['title' =>  'Customer' ])


@section('content')
<style type="text/css">
.alNewOrderListingView_orderImages{position: relative;}
.alNewOrderListingView_orderImages img{
    height: 40px;width: 40px;border-radius: 50%;
}
.alNewOrderListingView_orderImages sup{position: absolute;height: 20px;width: 20px;line-height: 20px;color: #fff;background-color: #43bee1;
    right: -10px;
    top: -10px;
    border-radius: 50%;
    text-align: center;
}
.alNewOrderTabs.nav-tabs .nav-link.active,
.alNewOrderTabs.nav-tabs .nav-item.show .nav-link {
    color: #43bee1;
    background-color: transparent;
    border-color: transparent;
}
#alLightBg{background-color: #F3F7F9;}
.alNewOrderTabs.nav-tabs .nav-link.active {
    border-bottom: 2px solid #43bee1;
}
.alNewOrderTabs.nav-tabs .nav-item {
    margin-bottom: 0;
}
.alNewOrderListingView_orderAddress {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
    display: inherit;
}
.alNewOrderListingViewHead span {
    font-size: 12px;
}
.alNewOrderListingViewHead{padding-bottom: 5px;}
.alNewOrderListingView_orderStatus small {
    background-color: #FDF2D1;
    padding: 0 5px;
    border-radius: 3px;
    color: orange;
}
.alNewOrderListingView_orderName {
    font-weight: 600;
}
.alNewOrderListingView_orderStatus img {
    height: 22px;
}
#alLightBg .price_box_bottom li {
    margin: 0 0 3px;
    font-size: 12px;
}
</style>
<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            @include('alert')
            <div class="page-title-box">
                @if(isset($subadmin))
                <h4 class="page-title">{{ __('Update Customer') }}</h4>
                @endif
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    @if(isset($subadmin))
                    <form id="UpdateSubadmin"  enctype="multipart/form-data" method="post" action="{{route('customer.new.update', $subadmin->id)}}"
                        enctype="multipart/form-data">
                        @method('PUT')
                    @else
                    <form id="StoreSubadmin"  enctype="multipart/form-data" method="post" action="{{route('acl.store')}}" enctype="multipart/form-data">
                    @endif
                        @csrf

                        <div class=" row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="control-label">{{ __("NAME") }}</label>
                                    <input type="text" class="form-control" name="name" id="name"
                                        value="{{ old('name', $subadmin->name ?? '')}}" placeholder="John Doe" >
                                    @if($errors->has('name'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                       
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="control-label">{{ __('EMAIL') }}</label>
                                     <input type="email" class="form-control" id="email" name="email"
                                        value="{{ old('email', $subadmin->email ?? '')}}" placeholder="Enter email address" >
                                    @if($errors->has('email'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image" class="control-label">{{ __('IMAGE') }}</label>
                                    <input type="file" class="form-control" id="image" name="image">
                                    @if($errors->has('image'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('image') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->can('user-add-role-permission') || auth()->user()->is_superadmin)
                      
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="control-label">{{ __("User Role") }}</label>
                                    <select name="role" class="form-control" id="user-roles">
                                        <option value="" >{{ ('Select Role') }}</option>
                                        @foreach($rolesNew as $role)
                                        <option value="{{$role->id}}" @if($role->id==$userRole) selected @endif>{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                    <div class="form-group">
                                    {!! Form::label('title', __('Select Geo Fence Regions'),['class' => 'control-label'],['placeholder'=>'Search']) !!}
                                    <select class="permissoin-multiple selectToGeo" name="geo_ids[]" multiple="multiple">
                                        @foreach($serviceArea as $perm)
                                        <option value="{{$perm->id}}" @if(in_array($perm->id,$geoIds)) selected @endif >{{$perm->name}}</option>
                                        @endforeach
                                    </select>
                                    </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="control-label">{{ __("Status") }}</label>
                                    <select name="status" class="form-control">
                                        <option value="1" @if($subadmin->status==1) selected @endif>{{ __("Active") }}</option>
                                        <option value="3" @if($subadmin->status==3) selected @endif>{{ __("Inactive") }}</option>

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="control-label">{{ __("Provide Admin Access") }}</label>
                                     <select name="is_admin" class="form-control">
                                        <option value="0" @if($subadmin->is_admin==0) selected @endif>{{ __("No") }}</option>
                                        <option value="1" @if($subadmin->is_admin==1) selected @endif>{{ __("Yes") }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                         {{--   @if (isset($getAdditionalPreference['is_price_by_role']))
                                @if($getAdditionalPreference['is_price_by_role'] == '1')
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="roles" class="control-label">{{ __("User Roles") }}</label>
                                            <select name="role_id" class="form-control">
                                                @if (isset($roles))
                                                    @foreach ($roles as $key => $_role)
                                                        <option value="{{$_role['id'] ?? ''}}" @if($subadmin->role_id == $_role['id']) selected @endif>{{ $_role['role'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            @endif--}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('title', __('Email Verified'),['class' => 'control-label']) !!}
                                    <div>
                                        <input type="checkbox" data-plugin="switchery" name="is_email_verified" class="form-control email_verify_add" @if($subadmin->is_email_verified == 1) checked @endif>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('title', __('Phone Verified'),['class' => 'control-label']) !!}
                                    <div>
                                        <input type="checkbox" data-plugin="switchery" name="is_phone_verified" class="form-control phone_verify_add" @if($subadmin->is_phone_verified == 1) checked @endif>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row mt-2">
                            @if(!empty($user_registration_documents) && count($user_registration_documents) > 0)
                            @foreach($user_registration_documents as $user_registration_document)
                            @php
                                $field_value = "";
                                if(!empty($user_docs) && count($user_docs) > 0){
                                    foreach($user_docs as $key => $user_doc){
                                        if($user_registration_document->id == $user_doc->user_registration_document_id){
                                            if($user_registration_document->file_type == 'Text' || $user_registration_document->file_type == 'selector' ){
                                                $field_value = $user_doc->file_name;
                                            } else {
                                                $field_value = $user_doc->image_file['storage_url'];
                                            }
                                        }
                                    }
                                }
                            @endphp
                            @if(strtolower($user_registration_document->file_type) == 'selector')
                                @if($user_registration_document->options)    
                                <div class="col-md-6 mb-3" id="{{$user_registration_document->primary->slug??''}}Input">
                                        <label for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}</label>
                                        <select class="form-control {{ (!empty($user_registration_document->is_required))?'required':''}}" name="{{$user_registration_document->primary->slug}}"  id="input_file_selector_{{$user_registration_document->id}}">
                                            <option value="" >{{__('Please Select '). ($user_registration_document->primary ? $user_registration_document->primary->name : '') }}</option>
                                            @foreach ($user_registration_document->options as $key =>$value )
                                                <option value="{{$value->id}}" {{ ($value->id == $field_value) ? 'selected':'' }} >{{$value->translation? $value->translation->name: ""}}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback" id="{{$user_registration_document->primary->slug}}_error"><strong></strong></span>
                                    </div>
                                    @endif
                            @else
                            <div class="col-md-6" >
                                <div class="form-group" id="{{$user_registration_document->primary->slug??''}}Input">
                                    @if(strtolower($user_registration_document->file_type) == 'text')
                                        <label for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}</label>
                                        <input id="input_file_logo_{{$user_registration_document->id}}" type="text" name="{{$user_registration_document->primary->slug??''}}" class="form-control" value="{{ $field_value }}">
                                    @else
                                        @if(strtolower($user_registration_document->file_type) == 'image')
                                        <label for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}</label>
                                        <input type="file" accept="image/*" data-plugins="dropify" name="{{$user_registration_document->primary->slug??''}}" class="dropify" data-default-file="{{ $field_value }}" />
                                        @else
                                        <label class="d-flex align-items-center justify-content-between" for="">{{$user_registration_document->primary ? $user_registration_document->primary->name : ''}}
                                            @if($field_value)
                                                <a href="{{ $field_value }}" target="__blank">
                                                    <i class="fa fa-file-pdf" aria-hidden="true"></i>
                                                </a>
                                            @endif
                                    </label>
                                        <input type="file" accept=".pdf" data-plugins="dropify" name="{{$user_registration_document->primary->slug??''}}" class="dropify" data-default-file="{{ $field_value }}" />
                                        @endif
                                    @endif
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            @endif
                            @endforeach
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-lg-0 mb-3 user_perm_section table-responsive d-none">
                                @php
                                    $userpermissions = [];
                                    if(isset($user_permissions))
                                    {
                                        foreach ($user_permissions as $singlepermission) {
                                            $userpermissions[] = $singlepermission->permission_id;
                                        }
                                    }
                                @endphp
                                <table class="table table-borderless table-nowrap table-hover table-centered m-0">

                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __("Permission Name") }}</th>
                                            <th>{{ __("Status") }}</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        @php
                                        $brity = \App\Models\ClientPreference::where(['id' => 1])->first('celebrity_check');
                                        @endphp
                                        @foreach($permissions as $singlepermission)
                                        @if($singlepermission->name == 'CELEBRITY')

                                                @if(!empty($brity) && $brity->celebrity_check == 1)
                                                <tr>
                                                <td>
                                                    <h5 class="m-0 font-weight-normal">{{ ucwords(strtolower($singlepermission->name)) }}</h5>
                                                </td>

                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input event_type" data-id="{{ $singlepermission->id }}" data-event-type="permission" id="permission_{{ $singlepermission->id}}" name="permissions[]" value="{{ $singlepermission->id }}" @if(in_array($singlepermission->id, $userpermissions)) checked @endif >

                                                        <label class="custom-control-label" for="permission_{{ $singlepermission->id}}"></label>
                                                    </div>
                                                </td>
                                                </tr>
                                                @endif

                                        @elseif($singlepermission->name == 'CMS Pages' || $singlepermission->name == 'CMS Emails')
                                            <tr>
                                                <td>
                                                    <h5 class="m-0 font-weight-normal">{{ $singlepermission->name }}</h5>
                                                </td>

                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input event_type" data-id="{{ $singlepermission->id }}" data-event-type="permission" id="permission_{{ $singlepermission->id}}" name="permissions[]" value="{{ $singlepermission->id }}" @if(in_array($singlepermission->id, $userpermissions)) checked @endif >

                                                        <label class="custom-control-label" for="permission_{{ $singlepermission->id}}"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td>
                                                    <h5 class="m-0 font-weight-normal">{{ ucwords(strtolower($singlepermission->name)) }}</h5>
                                                </td>

                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input event_type" data-id="{{ $singlepermission->id }}" data-event-type="permission" id="permission_{{ $singlepermission->id}}" name="permissions[]" value="{{ $singlepermission->id }}" @if(in_array($singlepermission->id, $userpermissions)) checked @endif >

                                                        <label class="custom-control-label" for="permission_{{ $singlepermission->id}}"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>

                            <div class="col-lg-6 team_perm_section table-responsive d-none">

                                <table class="table table-borderless table-nowrap table-hover table-centered m-0">

                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{__('Vendors')}}</th>
                                            <th>{{ __("Status") }}</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        {{-- <tr>
                                            <td>
                                                <h5 class="m-0 font-weight-normal">{{__('Select All')}}</h5>
                                            </td>

                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input all_vendor_check" id="vendor_permission_all"
                                                    data-event-type="vendor_permission"
                                                    name="vendor_permission_all"
                                                   >
                                                    <label class="custom-control-label" for="vendor_permission_all"></label>
                                                </div>
                                            </td>
                                        </tr>  --}}

                                        @foreach($vendors as $vendor)
                                        <tr>
                                            <td>
                                                <h5 class="m-0 font-weight-normal">{{ $vendor->name }}</h5>
                                            </td>

                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input vendor_permission_check" data-id="{{ $vendor->id }}"
                                                    data-event-type="vendor_permission" id="vendor_permission_{{ $vendor->id}}"
                                                    name="vendor_permissions[]" value="{{ $vendor->id }}"
                                                    @if(in_array($vendor->id, $vendor_permissions)) checked @endif>
                                                    <label class="custom-control-label" for="vendor_permission_{{ $vendor->id}}"></label>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>

                        </div>
                        @endif

                        @if (count($subadmin->allergicItems))
                           <b> {{ __('Customer Allergic Items')}} </b> <br>
                        @endif
                        @forelse ($subadmin->allergicItems as $item)
                            {{ $item->title }}@if(!$loop->last),@endif
                        @empty
                            <b>{{ __('No Allergic Item Found')}}</b><br>
                        @endforelse
                        
                        <div class="row mb-2 mt-4">
                            <div class="col-12">
                                <div class="form-group mb-0 text-center">
                                    <button class="btn btn-blue btn-block" type="submit"> {{ __("Submit") }} </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-5" id="alLightBg">
            <div class="page-title-box d-flex justify-content-between">
                <h4 class="page-title mr-3">Orders History</h4>
            </div>

            <ul class="nav nav-tabs nav-material alNewOrderTabs mb-2" id="top-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active_orders-tab" data-toggle="tab" href="#active_orders" role="tab" aria-selected="false" data-rel="pending_orders">
                        <i class="icofont icofont-man-in-glasses"></i>Active Orders <sup class="total-items" id="active-orders">({{count($active_orders)}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="complete_orders_tab" data-toggle="tab" href="#complete_orders" role="tab" aria-selected="true" data-rel="active_orders">
                        <i class="icofont icofont-ui-home"></i>Complete Orders <sup class="total-items" id="complete-orders">({{count($completed_orders)}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>
            </ul>

            <div class="tab-content nav-material  order_data_box scroll-style" id="top-tabContent">
                <div class="tab-pane fade past-order show active position-relative h-100" id="active_orders"  role="tabpanel" aria-labelledby="active_order-tab">
                    @forelse($active_orders as $order)
                    <div class="card alNewOrderListingView">
                        <div class="card-body p-2">
                            <div class="row no-gutters border-bottom alNewOrderListingViewHead">
                                <div class="col-sm-3">
                                    <span class="alNewOrderListingView_orderId">#{{$order->orderDetail->order_number}}</span>
                                </div>
                                <div class="col-sm-4">
                                    <span class="alNewOrderListingView_orderDateTime">{{$order->created_date}}</span>
                                </div>
                                @if($order->orderDetail->address)
                                <div class="col-sm-5">
                                    <span class="alNewOrderListingView_orderAddress">{{$order->orderDetail->address->house_number?$order->orderDetail->address->house_number.', ' : ''}}{{$order->orderDetail->address->address}}</span>
                                </div>
                                @endif
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-3">
                                    <span class="alNewOrderListingView_orderName">{{$order->vendor->name ?? ''}}</span>
                                    <span class="alNewOrderListingView_orderStatus d-flex align-items-center"><img src="{{asset('assets/images/order-icon.svg')}}"> <small>{{$order->order_status}}</small></span>
                                </div>
                                <div class="col-sm-4">
                                    @foreach($order->products as $product)
                                    <span class="alNewOrderListingView_orderImages">
                                        <img src="{{$product->image_path['proxy_url']}}74/100{{$product->image_path['image_path']}}">
                                        <sup>X{{$product->quantity}}</sup>
                                    </span>
                                    @endforeach
                                </div>
                                <div class="col-sm-5">
                                    <div class="alNewOrderListingView_orderTotalPrice">
                                        <ul class="price_box_bottom m-0 p-0">
                                            @if($order->subtotal_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">Total</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->subtotal_amount)}}</span>
                                            </li>
                                            @endif
                                            @if($order->discount_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Promocode') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->discount_amount)}}</span>
                                            </li>
                                            @endif
                                            @if($order->total_container_charges > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Container Charges') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->total_container_charges??'0.00')}}</span>
                                            </li>
                                            @endif
                                            @if($order->taxable_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Tax') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->taxable_amount??'0.00')}}</span>
                                            </li>
                                            @endif
                                            @if($order->service_fee_percentage_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Service Fee') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->service_fee_percentage_amount??'0.00')}}</span>
                                            </li>
                                            @endif
                                            @if($order->fixed_fee_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __($fixedFee) }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->fixed_fee_amount??'0.00')}}</span>
                                            </li>
                                            @endif
                                            @if($order->delivery_fee > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Delivery') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->delivery_fee??'0.00')}}</span>
                                            </li>
                                            @endif

                                            <li class="grand_total d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Amount') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{ decimal_format($order->subtotal_amount) - decimal_format($order->discount_amount)  + decimal_format($order->total_container_charges) + decimal_format($order->taxable_amount) + decimal_format($order->service_fee_percentage_amount) + decimal_format($order->fixed_fee_amount) + decimal_format($order->delivery_fee ) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    {{__('No Order History Found.')}}
                    @endforelse


                </div>
                <div class="tab-pane fade past-order position-relative h-100" id="complete_orders"  role="tabpanel" aria-labelledby="cpmplete_order-tab">
                    @forelse($completed_orders as $order)
                    <div class="card alNewOrderListingView">
                        <div class="card-body p-2">
                            <div class="row no-gutters border-bottom alNewOrderListingViewHead">
                                <div class="col-sm-3">
                                    <span class="alNewOrderListingView_orderId">#{{$order->orderDetail->order_number}}</span>
                                </div>
                                <div class="col-sm-4">
                                    <span class="alNewOrderListingView_orderDateTime">{{$order->created_date}}</span>
                                </div>
                                @if($order->orderDetail->address)
                                <div class="col-sm-5">
                                    <span class="alNewOrderListingView_orderAddress">{{$order->orderDetail->address->house_number?$order->orderDetail->address->house_number.', ' : ''}}{{$order->orderDetail->address->address}}</span>
                                </div>
                                @endif
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-3">
                                    <span class="alNewOrderListingView_orderName">{{$order->vendor->name ?? ''}}</span>
                                    <span class="alNewOrderListingView_orderStatus d-flex align-items-center"><img src="{{asset('assets/images/order-icon.svg')}}"> <small>{{$order->order_status}}</small></span>
                                </div>
                                <div class="col-sm-4">
                                    @foreach($order->products as $product)
                                    <span class="alNewOrderListingView_orderImages">
                                        <img src="{{$product->image_path['proxy_url']}}74/100{{$product->image_path['image_path']}}">
                                        <sup>X{{$product->quantity}}</sup>
                                    </span>
                                    @endforeach
                                </div>
                                <div class="col-sm-5">
                                    <div class="alNewOrderListingView_orderTotalPrice">
                                        <ul class="price_box_bottom m-0 p-0">
                                            @if($order->subtotal_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">Total</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->subtotal_amount)}}</span>
                                            </li>
                                            @endif
                                            @if($order->discount_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Promocode') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->discount_amount)}}</span>
                                            </li>
                                            @endif
                                            @if($order->total_container_charges > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Container Charges') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->total_container_charges??'0.00')}}</span>
                                            </li>
                                            @endif
                                            @if($order->taxable_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Tax') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->taxable_amount??'0.00')}}</span>
                                            </li>
                                            @endif
                                            @if($order->service_fee_percentage_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Service Fee') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->service_fee_percentage_amount??'0.00')}}</span>
                                            </li>
                                            @endif
                                            @if($order->fixed_fee_amount > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __($fixedFee) }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->fixed_fee_amount??'0.00')}}</span>
                                            </li>
                                            @endif
                                            @if($order->delivery_fee > 0)
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Delivery') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{decimal_format($order->delivery_fee??'0.00')}}</span>
                                            </li>
                                            @endif

                                            <li class="grand_total d-flex align-items-center justify-content-between">
                                                <label class="m-0">{{ __('Amount') }}</label>
                                                <span>{{$clientCurrency->currency->symbol}}{{ decimal_format($order->subtotal_amount) - decimal_format($order->discount_amount)  + decimal_format($order->total_container_charges) + decimal_format($order->taxable_amount) + decimal_format($order->service_fee_percentage_amount) + decimal_format($order->fixed_fee_amount) + decimal_format($order->delivery_fee ) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    {{__('No Order History Found.')}}
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@section('script')

    <script src="{{ asset('assets/js/jquery-ui.min.js') }}" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css') }}">
    <script>
         $('.permissoin-multiple').select2();
         $(".all_vendor_check").click(function() {
            if ($(this).is(':checked')) {
                $('.vendor_permission_check').prop('checked', true);
            } else {
                $('.vendor_permission_check').prop('checked', false);
            }
        });
        
        $(document).ready(function(){
        	var role = '{{ $userRole??'' }}';
        	getRoleVal(role);
        });
        
        $("#user-roles").change(function(){
        	 getRoleVal($(this).val());
        });
        
        function getRoleVal(role){
        	if(role == 4){
        		$(".team_perm_section").removeClass("d-none");
        	}else{
        		$(".team_perm_section").addClass("d-none");
        	}
        }
   </script>

@endsection
