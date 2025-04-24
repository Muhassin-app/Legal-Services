@extends('layouts.god-vertical', ['title' => 'Pricings'])
@section('css')
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Pricings') }}</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-8">
                            <div class="text-sm-left">
                            @if (\Session::has('success'))
                                <div class="alert alert-success">
                                    <span>{!! \Session::get('success') !!}</span>
                                </div>
                            @endif
                            @if (\Session::has('error_delete'))
                                <div class="alert alert-danger">
                                    <span>{!! \Session::get('error') !!}</span>
                                </div>
                            @endif
                            @if ( ($errors) && (count($errors) > 0) )
                                <div class="alert alert-danger">
                                    <ul class="m-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <a class="btn btn-info waves-effect waves-light text-sm-right" href="#add" data-toggle="modal" data-target="#add-billing-pricing"><i class="mdi mdi-plus-circle mr-1"></i> Add </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <form name="saveBillingPricing" id="saveBillingTimeframe"> @csrf </form>
                        <table class="table table-centered table-nowrap table-striped" id="billing-pricings-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Plan') }}</th>
                                    <th>{{ __('Plan Type') }}</th>
                                    <th>{{ __('Timeframe') }}</th>
                                    <th>{{ __('Current Price') }}</th>
                                    <th>{{ __('Earlier Price') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="BillingPricing_list">
                                @foreach($billingpricings as $billingpricing)
                                <tr data-row-id="{{$billingpricing->slug}}">
                                    <td>{{$billingpricing->plan_name}}</td>
                                    <td><span class="badge bg-success" style="color:#fff;">{{$billingpricing->plantype}}</span></a></td>
                                    <td>{{$billingpricing->billingtimeframe->title}}</td>
                                    <td><a href="javascript:void(0)" class="editBillingPricingBtn" data-id="{{$billingpricing->slug}}">{{__(ucfirst($billingpricing->price))}}</a></td>
                                    <td>{{$billingpricing->old_price}}</td>
                                    <td>
                                        <input type="checkbox" data-id="{{$billingpricing->slug}}" data-plugin="switchery" name="userBillingPricingStatus" class="chk_box status_check" data-color="#43bee1" {{($billingpricing->status == 1) ? 'checked' : ''}} >
                                    </td>
                                    <td>
                                        <div class="form-ul" style="width: 60px;">
                                            <div class="inner-div" >
                                             
                                                    <a href="javascript:void(0)" class="action-icon editBillingPricingBtn" data-id="{{$billingpricing->slug}}"><i class="mdi mdi-square-edit-outline"></i></a>
                                               
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{ $billingpricings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end Content-->

        

    


    <div id="add-billing-pricing" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addBillingPricing_Label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Add Pricing') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            {{ Form::open(array('id' => 'billing_pricing_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => 'billingpricing.save')) }}
           
                @csrf
                <div class="modal-body" >
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">{{ __("Plan") }}</label>
                                        {!! Form::select('billing_plan_id', $billingplanlist, NULL, ['class' => 'form-control', 'id'=>'billing_plan_id', 'required' => 'required']) !!}
                                        
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">{{ __("Timeframe") }}</label>
                                        {!! Form::select('billing_timeframe_id', $billingtimeframelist, NULL, ['class' => 'form-control', 'id'=>'billing_timeframe_id', 'required' => 'required']) !!}
                                        
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">{{ __("Price") }}</label>
                                        {!! Form::number('price', NULL, ['class'=>'form-control', 'required'=>'required', 'id'=>'price', 'step'=>'any']) !!}
                                        
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('title', __('Status'),['class' => 'control-label']) !!}
                                        <div class="mt-md-1">
                                            <input type="checkbox" data-plugin="switchery" name="status" class="form-control status" data-color="#43bee1" checked="checked"}}>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light submitAddSubscriptionForm">{{ __("Submit") }}</button>
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

<div id="edit-billing-pricing" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editBillingPricing_Label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
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
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script type="text/javascript">
    var edit_billing_pricing_url = "{{ route('billingpricing.edit', ':id') }}";
    var update_billing_pricing_status_url = "{{route('billingpricing.updateStatus', ':id')}}";
    
    $(document).delegate(".editBillingPricingBtn", "click", function(){
        let slug = $(this).attr("data-id");
        $.ajax({
            type: "get",
            dataType: "json",
            url: edit_billing_pricing_url.replace(":id", slug),
            success: function(res) {
                $("#edit-billing-pricing .modal-content").html(res.html);
                $("#edit-billing-pricing").modal("show");
                $('#edit-billing-pricing .select2-multiple').select2();
                $('#edit-billing-pricing .dropify').dropify();
                var switchery = new Switchery($("#edit-billing-pricing .status")[0]);
            }
        });
    });

    $("#billing-pricings-datatable .status_check").on("change", function() {
        var slug = $(this).attr('data-id');
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
            url: update_billing_pricing_status_url.replace(":id", slug),
            data: {status: status},
            success: function(jsondata) {
               
                if(jsondata.success)
                {var color = 'green';var heading="Success!";}else{var color = 'red';var heading="Error!";}
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
</script>
@endsection
