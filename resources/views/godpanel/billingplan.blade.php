@extends('layouts.god-vertical', ['title' => 'Plans'])
@section('css')
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Plans') }}</h4>
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
                            <a class="btn btn-info waves-effect waves-light text-sm-right" href="#add" data-toggle="modal" data-target="#add-billing-plan"><i class="mdi mdi-plus-circle mr-1"></i> Add </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <form name="savebillingplan" id="savebillingplan"> @csrf </form>
                        <table class="table table-centered table-nowrap table-striped" id="billing-plans-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Plan Type') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="billingplan_list">
                                @foreach($billingplans as $billingplan)
                                <?php
                                ?>
                                <tr data-row-id="{{$billingplan->slug}}">
                                    <td>
                                        <img src="{{$billingplan->image['proxy_url'].'40/40'.$billingplan->image['image_path']}}" class="rounded-circle" alt="{{$billingplan->slug}}" >
                                    </td>
                                    <td><a href="javascript:void(0)" class="editBillingPlanBtn" data-id="{{$billingplan->slug}}">{{__($billingplan->title)}}</a></td>
                                    <td>{{__(ucfirst($billingplan->plantype->title))}}</td>
                                    <td>{{__($billingplan->description)}}</td>
                                    <td>
                                        <input type="checkbox" data-id="{{$billingplan->slug}}" data-plugin="switchery" name="userBillingPlanStatus" class="chk_box status_check" data-color="#43bee1" {{($billingplan->status == 1) ? 'checked' : ''}} >
                                    </td>
                                    <td>
                                        <div class="form-ul" style="width: 60px;">
                                            <div class="inner-div" >
                                             
                                                    <a href="javascript:void(0)" class="action-icon editBillingPlanBtn" data-id="{{$billingplan->slug}}"><i class="mdi mdi-square-edit-outline"></i></a>
                                               
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{ $billingplans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end Content-->

        

    


    <div id="add-billing-plan" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addBillingPlan_Label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Add Plan') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            {{ Form::open(array('id' => 'billing_plan_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => 'billingplans.save')) }}
           
                @csrf
                <div class="modal-body" >
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <label>{{ __('Upload Image') }}</label>
                                    <input type="file" accept="image/*" data-plugins="dropify" name="image" class="dropify" data-default-file="" />
                                    <label class="logo-size text-right w-100">{{ __('Image Size') }} 120x120</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('title', __('Status'),['class' => 'control-label']) !!}
                                        <div class="mt-md-1">
                                            <input type="checkbox" data-plugin="switchery" name="status" class="form-control status" data-color="#43bee1" checked='checked'>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group" id="nameInput">
                                        {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
                                        {!! Form::text('title', null, ['class'=>'form-control', 'required'=>'required']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">{{ __("Plan Type") }}</label>
                                        {!! Form::select('plan_type', $billingplanstype, null, ['class' => 'form-control', 'required' => 'required']) !!}
                                       
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">{{ __("Description") }}</label>
                                        {!! Form::textarea('description', null, ['id' => 'description', 'rows' => 4, 'class' => 'form-control']) !!}
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

<div id="edit-billing-plan" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editBillingPlan_Label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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
    var edit_billing_plan_url = "{{ route('billingplans.edit', ':id') }}";
    var update_billing_plan_status_url = "{{route('billingplans.updateStatus', ':id')}}";
    
    $(document).delegate(".editBillingPlanBtn", "click", function(){
        let slug = $(this).attr("data-id");
        $.ajax({
            type: "get",
            dataType: "json",
            url: edit_billing_plan_url.replace(":id", slug),
            success: function(res) {
                $("#edit-billing-plan .modal-content").html(res.html);
                $("#edit-billing-plan").modal("show");
                $('#edit-billing-plan .select2-multiple').select2();
                $('#edit-billing-plan .dropify').dropify();
                var switchery = new Switchery($("#edit-billing-plan .status")[0]);
            }
        });
    });

    $("#billing-plans-datatable .status_check").on("change", function() {
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
            url: update_billing_plan_status_url.replace(":id", slug),
            data: {status: status},
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

</script>
@endsection
