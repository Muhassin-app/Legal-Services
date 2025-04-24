@extends('layouts.god-vertical', ['title' => 'Client Subscriptions'])
@section('css')
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
                <h4 class="page-title">Edit Client Subscription</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                <div class="row mb-2">
                        <div class="col-sm-8">
                        </div>
                        <div class="col-sm-4 text-right">
                            <a class="btn btn-info waves-effect waves-light text-sm-right" href="{{route('clientsubscription')}}"><< Back </a>
                        </div>
                    </div>
                    {{ Form::open(array('id' => 'client_subscription_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => array('clientsubscription.update', $subscriptiondata->slug))) }}
                        @csrf
                        <h4 class="header-title">Subscription Details</h4>
                        <div class=" row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="Client" class="control-label">Client</label><br/>
                                    <strong>{{$subscriptiondata->client->name}}</strong>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="Software Subscription Plans" class="control-label">Subscription Plan</label><br/>
                                    <strong>{{$subscriptiondata->billing_plan_title}}</strong>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="Timeframe" class="control-label">Timeframe</label><br/>
                                    <strong>{{ $subscriptiondata->billing_timeframe_title }}</strong>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Timeframe" class="control-label">Subscription Price</label><br/>
                                    {!! Form::number('price', $subscriptiondata->billing_price, ['class'=>'form-control', 'required'=>'required', 'id'=>'price']) !!}
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('title', __('Start Date'),['class' => 'control-label']) !!}
                                    {!! Form::text('start_date', date('d-m-Y',strtotime($subscriptiondata->start_date)), ['class'=>'form-control', 'required'=>'required', 'id'=>'start_date', 'placeholder'=>'dd-mm-yyyy', 'autocomplete'=>'off']) !!}
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('title', __('End Date'),['class' => 'control-label']) !!}
                                    {!! Form::text('end_date', $subscriptiondata->end_date, ['class'=>'form-control', 'id'=>'end_date', 'placeholder'=>'dd-mm-yyyy', 'autocomplete'=>'off']) !!}
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                    
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('title', __('Next Due Date'),['class' => 'control-label']) !!}
                                    {!! Form::text('next_due_date', $subscriptiondata->next_due_date, ['class'=>'form-control', 'id'=>'next_due_date', 'placeholder'=>'dd-mm-yyyy', 'autocomplete'=>'off']) !!}
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                            @if($subscriptionnew == 0)
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-info waves-effect waves-light" onClick="return getValidate();">Submit</button>
                            </div>
                            @endif
                        </div>
                        {{ Form::close() }}

                   

                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{asset('assets/libs/selectize/selectize.min.js')}}"></script>
<script src="{{asset('assets/libs/mohithg-switchery/mohithg-switchery.min.js')}}"></script>
<script src="{{asset('assets/libs/multiselect/multiselect.min.js')}}"></script>
<script src="{{asset('assets/libs/select2/select2.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-select/bootstrap-select.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-touchspin/bootstrap-touchspin.min.js')}}"></script>
<script src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js')}}"></script>
<script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>
<script src="{{asset('front-assets/js/underscore.min.js')}}"></script>
<script src="{{asset('assets/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/libs/dropify/dropify.min.js')}}"></script>
<script src="{{asset('front-assets/js/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.js')}}"></script>
<script src="{{asset('assets/libs/clockpicker/clockpicker.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<script src="{{asset('assets/libs/devbridge-autocomplete/devbridge-autocomplete.min.js')}}"></script>
<script src="{{asset('assets/js/pages/form-fileuploads.init.js')}}"></script>
<script src="{{asset('assets/js/pages/my-form-advanced.init.js')}}"></script>
<script src="{{asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.js')}}"></script>
<script src="{{asset('assets/js/pages/toastr.init.js')}}"></script>
<script type="text/javascript">
$(document).ready(function(){
    var loc = "{{route('clientsubscription')}}";
    $('#side-menu').find('a').each(function() {
        if($(this).attr('href') == loc)
        {        
        $(this).toggleClass('active');
        $(this).parent().toggleClass('menuitem-active');
        $(this).parent().parent().parent().parent().toggleClass('menuitem-active');
        $(this).parents(".collapse").toggleClass('show');
        }
    });
    $("#start_date,#end_date,#next_due_date").flatpickr({
        altInput: true,
        altFormat: 'd-m-Y',
        dateFormat: "d-m-Y",
    }); 
});

function getShowPlanBox()
{
    if($("#plan_type").val()==1)
    {
        $("#softwarediv").show();
        $("#hostingdiv").hide();
    }else{
        $("#softwarediv").hide();
        $("#hostingdiv").show();
    }
}

function getValidate()
{
    return true;
}


</script>
@endsection