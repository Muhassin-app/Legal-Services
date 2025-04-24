@extends('layouts.god-vertical', ['title' => 'Client'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.css')}}" rel="stylesheet" type="text/css" />

@endsection
@section('content')
<style type="text/css">
    .sub-domain-input #sub_domain {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-right: 0;
    }
    
    .sub-domain-input #inputGroupPrepend2 {
        font-size: 18px;
        padding: 0 30px;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Client</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php $disable = $style = ""; ?>
                    <?php $disable = 'disabled';
                    $style = "cursor:not-allowed;"; ?>
                    <form id="UpdateClient" method="post" action="{{route('client.update', $client->id)}}"
                        enctype="multipart/form-data" autocomplete="off">
                        @method('PUT')
                        @csrf
                        <div class="row mb-2">
                            <div class="col-sm-12 text-right">
                                <a class="btn btn-info waves-effect waves-light text-sm-right" href="{{route('client.index')}}"><< Back </a>
                            </div>
                        </div>
                        <div class=" row">
                           
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sub_domain" class="control-label">SUB DOMAIN</label>
                                    <div class="input-group">
                                        <div class="sub-domain-input input-group-prepend w-100">
                                            <input type="text" class="form-control" name="sub_domain" id="sub_domain"
                                            value="{{ old('sub_domain', $client->sub_domain ?? '')}}"
                                            placeholder="Enter sub domain">
                                             <span class="input-group-text" id="inputGroupPrepend2"></span>
                                        </div>
                                      </div>
                                    
                                        @if($errors->has('sub_domain'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('sub_domain') }}</strong>
                                        </span>
                                        @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="custom_domain" class="control-label">CUSTOM DOMAIN</label>
                                    <input type="text" class="form-control" name="custom_domain" id="custom_domain"
                                        value="{{ old('custom_domain', $client->custom_domain ?? '')}}"
                                        placeholder="Enter custom domain">
                                    @if($errors->has('custom_domain'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('custom_domain') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="languages">Client Type</label>
                                    <select class="form-control" id="client_type" name="client_type">
                                        @foreach($client_types as $key => $value)
                                            <option value="{{$key}}" @if($client->client_type == $key) selected="selected" @endif >{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="languages">Business Type</label>
                                    <select class="form-control" id="business_type" name="business_type">
                                        @foreach($business_types as $business)
                                            <option value="{{$business->slug}}" @if($client->business_type == $business->slug) selected="selected" @endif> {{$business->title}} </option>
                                        @endforeach

                                    
                                    </select>
                                </div>    
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-info waves-effect waves-light">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- default data after on board -->
    <div class="row">
        <div class="col-12">    
                   <div class="card">
                        <div class="card-body"><h3>Import Demo Content [Warning! All data will be lost.]</h3>
                        <form id="update_default_data" method="post" action="{{route('client.migrateDefaultData', $client->id)}}"
                            enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="languages">Business Type </label>
                                    <select class="form-control" id="business_type" name="business_type">
                                        <option value=""></option>
                                        <option value="grub.sql">Grub - Food Delivery</option>
                                        <option value="homeric.sql">Homeric - Home Service </option>
                                        <option value="gokab.sql">GoKab - Cab Booking </option>
                                        <option value="ace.sql">Ace - Super App </option>
                                        <option value="punnet.sql">Punnet - Single Vendor Food Delivery </option>
                                        <option value="suel.sql">Suel - Single Vendor Ecommerce </option>
                                        <option value="voltaic.sql">Voltaic - Ecommerce </option>
                                        <option value="gusto.sql">Gusto - Grocery Delivery </option>
                                        <option value="elixir.sql">Elixir - Pharmacy Delivery </option>
                                        <option value="zest.sql">Zest - Pickup & Delivery </option>
                                        <option value="emart.sql">E-mart</option>
                                    </select>
                                </div>    
                            </div>

                            <div class="col-md-3">
                                <div class="row">
                                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__('Submit')}}</button>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
            
        </div>
    </div>
    <!--end default --> 

      <!-- on single vendor panel  -->
      <div class="row">
        <div class="col-12">    
                   <div class="card">
                        <div class="card-body"><h3>{{__('Single Vendor')}}</h3>
                        <form id="update_default_data" method="post" action="{{route('client.update_single_vendor', $client->id)}}"
                            enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="languages">Enable/Disable </label>
                                    <select class="form-control" id="single_vendor" name="single_vendor">
                                        <option value="0" @if($client->single_vendor == 0) selected="selected" @endif >Disable</option>
                                        <option value="1" @if($client->single_vendor == 1) selected="selected" @endif >Enable</option>
                                    </select>
                                </div>    
                            </div>

                            <div class="col-md-3">
                                <div class="row">
                                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__('Submit')}}</button>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
            
        </div>
    </div>
    <!--end default --> 

      <!-- Migrate Client  -->
      <div class="row">
        <div class="col-12">    
                   <div class="card">
                        <div class="card-body"><h3>{{__('Migrate Client')}}</h3>
                        <form  method="post" action="{{route('client.exportdb',$client->database_name)}}"
                            enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="languages">Stage </label>
                                    <select class="form-control" id="dump_into" name="dump_into">
                                        <option value="DEV">DEV</option>
                                        <option value="STAGING">STAG</option>
                                        <option value="PROD">PROD</option>
                                    </select>
                                </div>    
                            </div>

                            <div class="col-md-3">
                                <div class="row">
                                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__('Submit')}}</button>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
            
        </div>
    </div>


      <!-- Migrate Client  -->
      <div class="row">
        <div class="col-12">    
                   <div class="card">
                        <div class="card-body"><h3>{{__('Socket Url')}}</h3>
                        <form  method="post" action="{{route('client.socketUpdate',$client->id)}}"
                            enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="languages">Socket Url </label>
                                    <select class="form-control" id="socket_url" name="socket_url">
                                        <option class="" value="" data-id="">Disable chat</option>
                                        @if(isset($ChatSocketUrl))
                                            @foreach ($ChatSocketUrl as $socketUrl)
                                                <option @if($client->socket_url == $socketUrl->domain_url) selected="selected" @endif class="" value="{{$socketUrl->domain_url}}" data-id="{{$socketUrl->id}}">{{ $socketUrl->domain_url }}</option>
                                            @endforeach
                                        @endif
                                        <!-- <option value="DEV">DEV</option>
                                        <option value="STAGING">STAG</option>
                                        <option value="PROD">PROD</option> -->
                                    </select>
                                </div>    
                            </div>
                            <div style="display: none" class=" row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('Admin chat', __('Admin chat '),['class' => 'control-label']) !!}
                                        <div class="mt-md-1">
                                            <input type="checkbox" {{($client->admin_chat == 1) ? 'checked' : ''}} data-action="admin_chat"  data-plugin="switchery" name="admin_chat" class="form-control chk_box" data-color="#43bee1" >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('Driver chat', __('Driver chat '),['class' => 'control-label']) !!}
                                        <div class="mt-md-1">
                                            <input type="checkbox" {{($client->driver_chat == 1) ? 'checked' : ''}} data-action="driver_chat" data-plugin="switchery" name="driver_chat" class="form-control chk_box" data-color="#43bee1" >
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>
                            <div style="display: none" class=" row">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('Customer chat', __('Customer chat '),['class' => 'control-label']) !!}
                                        <div class="mt-md-1">
                                            <input type="checkbox" {{($client->customer_chat == 1) ? 'checked' : ''}} data-action="customer_chat" data-plugin="switchery" name="customer_chat" class="form-control chk_box" data-color="#43bee1" >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row">
                                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__('Submit')}}</button>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
            
        </div>
    </div>
    <div class="row">

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body"><h3>{{__('Campaign Microservice')}}</h3>
                <div class="form-group d-flex justify-content-between mb-3">
                    <label for="campaign_service" class="mr-2 mb-0">{{__("Enable")}} </label>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="campaign_service" name="campaign_service" data-id = "{{$client->id}}" @if($client->campaign_service == 1) checked  @endif>
                            <label class="custom-control-label" for="campaign_service"></label>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>
    </div>

    <!--end default --> 

</div>
<script src="{{asset('assets/libs/mohithg-switchery/mohithg-switchery.min.js')}}"></script>

<script src="{{asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.js')}}"></script>
<script src="{{asset('assets/js/pages/toastr.init.js')}}"></script>
<script type="text/javascript">

$(document).ready(function() {
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('input[name="_token"]').val()}
        });

    });

    
$(document).ready(function(){
    var update_status_chat = "{{route('client.socketUpdateAction', ':id')}}";

    var loc = "{{route('client.index')}}";
    $('#side-menu').find('a').each(function() {
        if($(this).attr('href') == loc)
        {  
            $(this).toggleClass('active');
            $(this).parent().toggleClass('menuitem-active');
        }
    });
    var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
        elems.forEach(function(html) {
        var switchery =new Switchery(html);
    });

    $(document).on("change",'.chk_box' ,function() {
            var action = $(this).attr('data-action');
            var client_id = '{!! $client->id !!}';
            var status = 2;
            if($(this).is(":checked")){
                status = 1;
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });
            $.ajax({
                type: "post",
                dataType: "json",
                url: update_status_chat.replace(":id", client_id),
                data: {status: status,action:action},
                success: function(jsondata) {
                    if(jsondata.success)
                    {var color = 'green';var heading ="Success!";}else{var color = 'red';var heading ="Error!";}
                    $.toast({ 
                    heading:heading,
                    text : jsondata.message, 
                    showHideTransition : 'slide', 
                    bgColor : color,              
                    textColor : '#eee',            
                    allowToastClose : true,      
                    hideAfter : 5000,            
                    stack : 5,                   
                    textAlign : 'left',         
                    position : 'top-right'      
                    })
                }
            });
        });
    
    });

    $('#campaign_service').on('change',function(){
        var is_campaign  = 0;
        var client_id  = $(this).data('id');
        if ($(this).is(":checked")) {
            is_campaign  = 1;
        }else{
            is_campaign  = 0;

        }

        $.ajax({
                    url: "{{route('enable-campaign-service')}}",
                    type: "POST",
                    dataType: 'json',
                    data: 
                    { 
                      client_id:client_id,
                      campaign_service:is_campaign
                    },
                    headers: {Accept: "application/json"},
                    success: function(response) {
                        console.log('in success');
                    }
                });
      

});

</script>
@endsection
