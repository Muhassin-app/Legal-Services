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
                <h4 class="page-title">Create Client Subscription</h4>
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
                    {{ Form::open(array('id' => 'client_subscription_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => 'clientsubscription.save')) }}
                        @csrf
                        <h4 class="header-title">Subscription Details</h4>
                        <div class=" row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Client" class="control-label">Client</label>
                                    {!! Form::select('client', $clientlist, old('client'), ['class' => 'form-control', 'id'=>'client', 'required' => 'required']) !!}
                                    @if($errors->has('client'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('client') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Susbcription Plan Type" class="control-label">Susbcription Plan Type</label>
                                    {!! Form::select('plan_type', $billingplantypelist, old('plan_type'), ['class' => 'form-control', 'id'=>'plan_type', 'required' => 'required', 'onChange'=>'getShowPlanBox();']) !!}
                                    @if($errors->has('plan_type'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('plan_type') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6" id="softwarediv" style="display:none;">
                                <div class="form-group">
                                    <label for="Software Subscription Plans" class="control-label">Software Subscription Plans</label>
                                    {!! Form::select('software_plans', $billingsoftwareplanlist, old('software_plans'), ['class' => 'form-control', 'id'=>'software_plans', 'required' => 'required']) !!}
                                    @if($errors->has('software_plans'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('software_plans') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6" id="hostingdiv" style="display:none;">
                                <div class="form-group">
                                    <label for="Hosting Subscription Plans" class="control-label">Hosting Subscription Plans</label>
                                    {!! Form::select('hosting_plans', $billinghostingplanlist, old('hosting_plans'), ['class' => 'form-control', 'id'=>'hosting_plans', 'required' => 'required']) !!}
                                    @if($errors->has('hosting_plans'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('hosting_plans') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Timeframe" class="control-label">Timeframe</label>
                                    {!! Form::select('pricing', [''=>'---Select From List---'], old('pricing'), ['class' => 'form-control', 'id'=>'pricing', 'required' => 'required']) !!}
                                    @if($errors->has('pricing'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('pricing') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('title', __('Price'),['class' => 'control-label']) !!}
                                    {!! Form::number('price', old('price'), ['class'=>'form-control', 'required'=>'required', 'id'=>'price']) !!}
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Pricing" class="control-label" id='old_prive_level'>Old Price</label>
                                    <div id='oldpricingdiv'></div>
                                </div>
                            </div>
                        </div>

                        <h4 class="header-title">Client Billing Date & Subscription Details</h4>

                        <div class=" row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('title', __('Billing Start Date'),['class' => 'control-label']) !!}
                                    {!! Form::text('start_billing_date', null, ['class'=>'form-control', 'required'=>'required', 'id'=>'start_billing_date', 'placeholder'=>'dd-mm-yyyy', 'autocomplete'=>'off']) !!}
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap table-striped">
                                    <thead>
                                        <tr>
                                            <th>Plan</th>
                                            <th>Timeframe</th>
                                            <th>Price</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Next Due Date</th>
                                            <th>Payment Status</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id='substbody'>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-12" id="buttonsubmitDiv">
                                <button type="submit" class="btn btn-info waves-effect waves-light" onClick="return getValidate();">Submit</button>
                            </div>
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
    
    getShowPlanBox();
    let pricinglistjson = <?php echo $billingpricinglistjson;?>;
    
    $("#client,#plan_type").change(function(){
        let clientid = $("#client").val();
        let plantype = $("#plan_type").val();
        let route = "{{ route('getclientbillingdetails.details', [':id',':plantype']) }}";
        $.ajax({
            type: "get",
            dataType: "json",
            url: route.replace(":id", clientid).replace(":plantype", plantype),
            success: function(res) {
                
                if(res.success)
                {
                    $("#start_billing_date").flatpickr().destroy();
                    if(res.billing_start_date!='')
                    {
                        $("#start_billing_date").val(res.billing_start_date);
                    }else{
                        $("#start_billing_date").flatpickr({
                            altInput: true,
                            altFormat: 'd-m-Y',
                            dateFormat: "d-m-Y",
                            allowInput: true,
                        }); 
                    }
                    if(res.lastsubscriptiondata!='')
                    {
                        if(res.lastsubscriptiondata.payment_text == "Paid"){var classname = "success";}else{var classname = "danger";}
                        if(res.lastsubscriptiondata.status_text == "Active"){var classname1 = "success";}else{var classname1 = "danger";}
                        $("#substbody").html('<tr><td>'+res.lastsubscriptiondata.billing_plan_title+'</td><td>'+res.lastsubscriptiondata.billing_timeframe_title+'</td><td>'+res.lastsubscriptiondata.billing_price+'</td><td>'+res.lastsubscriptiondata.start_date+'</td><td>'+res.lastsubscriptiondata.end_date_text+'</td><td>'+res.lastsubscriptiondata.next_due_date+'</td><td><span class="badge bg-'+classname+'" style="color:#fff;">'+res.lastsubscriptiondata.payment_text+'</span></td><td><span class="badge bg-'+classname1+'" style="color:#fff;">'+res.lastsubscriptiondata.status_text+'</span></td></tr>');
                        if(res.lastsubscriptiondata.status_text == 'Active' )
                        {
                            $("#buttonsubmitDiv").hide();
                        }else{
                            if(res.lastsubscriptiondata.payment_text == "Paid")
                            {
                                $("#buttonsubmitDiv").show();
                            }
                            if($("#plan_type").val()==1)
                            {
                                $("#software_plans").val(res.lastsubscriptiondata.billing_plan_id);
                                $("#software_plans").change();
                            }else{
                                $("#hosting_plans").val(res.lastsubscriptiondata.billing_plan_id);
                                $("#hosting_plans").change();
                            }
                            $("#pricing").val(res.lastsubscriptiondata.billing_price_id);
                            $("#pricing").change();
                        }
                    }
                    else{
                        $("#substbody").html('<tr><td style="text-align:center;" colspan="8">No '+$("#plan_type option:selected").text()+' Subscribtion activated yet.</td></tr>');
                        $("#buttonsubmitDiv").show();
                    }
                } 
            }
        });
    });

    $("#software_plans,#hosting_plans,#plan_type").change(function(){
        if($("#plan_type").val()==1)
        {
            var planid = $("#software_plans").val();
        }else{
            var planid = $("#hosting_plans").val();
        }
        $("#pricing").empty();
        var options = $("#pricing");
        options.append('<option value="">---Select From List---</option>');
        if(planid!='')
        {
            $.each(pricinglistjson[planid], function() {
                options.append($("<option />").val(this.id).text(this.title));
            });
        }
        $("#pricing").change();
    });

    $("#pricing").change(function(){
        $("#oldpricingdiv").empty();
        $("#old_prive_level").hide();
        $("#price").val(0);
        if($("#pricing").val()!='')
        {
            if($("#plan_type").val()==1)
            {
                var planid = $("#software_plans").val();
            }else{
                var planid = $("#hosting_plans").val();
            }
            if(planid!='')
            {
                $.each(pricinglistjson[planid], function() {
                    if($("#pricing").val() == this.id)
                    {
                        $("#price").val(this.price);
                        if(this.old_price > 0)
                        {
                            $("#oldpricingdiv").html('<strong><span class="badge bg-success" style="color:#fff;font-size:14px;"><del>'+this.old_price+'</del></span></strong>');
                            $("#old_prive_level").show();
                        }
                    }
                });
            }
        }
    });
    $("#client").change();
    $("#hosting_plans").change();
    $("#pricing").val({{old('pricing')}});
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