@extends('layouts.god-vertical', ['title' => 'Timeframes'])
@section('css')
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Timeframes') }}</h4>
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
                            <a class="btn btn-info waves-effect waves-light text-sm-right" href="#add" data-toggle="modal" data-target="#add-billing-timeframe"><i class="mdi mdi-plus-circle mr-1"></i> Add </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <form name="saveBillingTimeframe" id="saveBillingTimeframe"> @csrf </form>
                        <table class="table table-centered table-nowrap table-striped" id="billing-timeframes-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Customised') }}</th>
                                    <th>{{ __('Lifetime') }}</th>
                                    <th>{{ __('Validity') }}</th>
                                    <th>{{ __('Buffer Period (Days)') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="BillingTimeframe_list">
                                @foreach($billingtimeframes as $billingtimeframe)
                                <tr data-row-id="{{$billingtimeframe->slug}}">
                                    <td><a href="javascript:void(0)" class="editBillingTimeframeBtn" data-id="{{$billingtimeframe->slug}}">{{$billingtimeframe->title}}</a></td>
                                    <td>{{__(ucfirst($billingtimeframe->custome_text))}}</td>
                                    <td>{{__(ucfirst($billingtimeframe->timelimit_text))}}</td>
                                    <td>{{$billingtimeframe->validity}} {{$billingtimeframe->validity_type}}</td>
                                    <td>{{$billingtimeframe->standard_buffer_period}}</td>
                                    <td>
                                        <input type="checkbox" data-id="{{$billingtimeframe->slug}}" data-plugin="switchery" name="userBillingTimeframeStatus" class="chk_box status_check" data-color="#43bee1" {{($billingtimeframe->status == 1) ? 'checked' : ''}} >
                                    </td>
                                    <td>
                                        <div class="form-ul" style="width: 60px;">
                                            <div class="inner-div" >
                                             
                                                    <a href="javascript:void(0)" class="action-icon editBillingTimeframeBtn" data-id="{{$billingtimeframe->slug}}"><i class="mdi mdi-square-edit-outline"></i></a>
                                               
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{ $billingtimeframes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end Content-->

        

    


    <div id="add-billing-timeframe" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addBillingTimeframe_Label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Add Timeframe') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            {{ Form::open(array('id' => 'billing_timeframe_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => 'billingtimeframes.save')) }}
           
                @csrf
                <div class="modal-body" >
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group" id="nameInput">
                                        {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
                                        {!! Form::text('title', null, ['class'=>'form-control', 'required'=>'required']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('title', __('Status'),['class' => 'control-label']) !!}
                                        <div class="mt-md-1">
                                            <input type="checkbox" data-plugin="switchery" name="status" class="form-control status" data-color="#43bee1" checked='checked'>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('title', __('Customised'),['class' => 'control-label']) !!}
                                        <div class="mt-md-1">
                                            <input type="checkbox" data-plugin="switchery" name="is_custom" class="form-control customised" data-color="#43bee1">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('title', __('Lifetime'),['class' => 'control-label']) !!}
                                        <div class="mt-md-1">
                                            <input type="checkbox" data-plugin="switchery" name="is_lifetime" id="is_lifetime" class="form-control timelimit" data-color="#43bee1">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6" id="div_validity">
                                    <div class="form-group">
                                        <label for="">{{ __("Validity ") }}</label>
                                        {!! Form::number('validity', null, ['class'=>'form-control', 'required'=>'required', 'id'=>'validity', 'min'=>'0', 'max'=>'365']) !!}
                                       
                                    </div>
                                </div>

                                <div class="col-md-6" id="div_validity_type">
                                    <div class="form-group">
                                        <label for="">{{ __("Validity Type") }}</label>
                                        {!! Form::select('validity_type', $validity_type, null, ['class' => 'form-control', 'id'=>'validity_type', 'required' => 'required']) !!}
                                       
                                    </div>
                                </div>

                                <div class="col-md-12" id="div_buffer_period">
                                    <div class="form-group">
                                        {!! Form::label('title', __('Buffer Period (Days)'),['class' => 'control-label']) !!}
                                        {!! Form::number('standard_buffer_period', null, ['class'=>'form-control', 'required'=>'required', 'id'=>'standard_buffer_period', 'min'=>'0', 'max'=>'365']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
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

<div id="edit-billing-timeframe" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editBillingTimeframe_Label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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
    var edit_billing_timeframe_url = "{{ route('billingtimeframes.edit', ':id') }}";
    var update_billing_timeframe_status_url = "{{route('billingtimeframes.updateStatus', ':id')}}";
    
    $(document).delegate(".editBillingTimeframeBtn", "click", function(){
        let slug = $(this).attr("data-id");
        $.ajax({
            type: "get",
            dataType: "json",
            url: edit_billing_timeframe_url.replace(":id", slug),
            success: function(res) {
                $("#edit-billing-timeframe .modal-content").html(res.html);
                $("#edit-billing-timeframe").modal("show");
                $('#edit-billing-timeframe .select2-multiple').select2();
                $('#edit-billing-timeframe .dropify').dropify();
                var switchery = new Switchery($("#edit-billing-timeframe .status")[0]);
                var switchery = new Switchery($("#edit-billing-timeframe .customised")[0]);
                var switchery = new Switchery($("#edit-billing-timeframe .timelimit")[0]);

                $("#edit-billing-timeframe .timelimit").on("change", function() {
                    if($(this).is(":checked")){
                        $("#div_buffer_period_edit,#div_validity_edit,#div_validity_type_edit").hide();
                        $("#standard_buffer_period_edit,#validity_edit").val(0);
                    }else{
                        $("#div_buffer_period_edit,#div_validity_edit,#div_validity_type_edit").show();
                    }
                });
                $("#edit-billing-timeframe .timelimit").change();
            }
        });
    });

    $("#billing-timeframes-datatable .status_check").on("change", function() {
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
            url: update_billing_timeframe_status_url.replace(":id", slug),
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

    $("#add-billing-timeframe .timelimit").on("change", function() {
        if($(this).is(":checked")){
            $("#div_buffer_period,#div_validity,#div_validity_type").hide();
            $("#standard_buffer_period,#validity").val(0);
        }else{
            $("#div_buffer_period,#div_validity,#div_validity_type").show();
        }
    });

    

</script>
@endsection
