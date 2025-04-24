@extends('layouts.god-vertical', ['title' => 'Client Subscriptions'])
@section('css')
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<style>
    .classbtnhidden{
        visibility: hidden;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Client Subscriptions') }}</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                {{ Form::open(array('id' => 'client_subscription_form', 'method' => 'post', 'enctype' => 'multipart/form-data')) }}
                    <div class="row mb-2">
                        <div class="col-sm-12">
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
                        <div class="col-sm-2">
                            <select id='clientid' name="clientid" class="form-control" onChange="getSubscriptionList();">
                                <option value="">Select Client From List</option>
                                @foreach($clientlists  as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select id='plantype' name="plantype" class="form-control" onChange="getSubscriptionList();">
                                <option value="">Select Plan Type From List</option>
                                @foreach($plantypelists as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select id='subsstatus' name="subsstatus" class="form-control" onChange="getSubscriptionList();">
                                <option value="">Select Subscription Status From List</option>
                                <option value="Active"> Active </option>
                                <option value="Expired"> Expired </option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select id='paymentstatus' name="paymentstatus" class="form-control" onChange="getSubscriptionList();">
                                <option value="">Select Payment Status From List</option>
                                @foreach($paymentstatuslist as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                        </div>
                        <div class="col-sm-2 text-right">
                            <a class="btn btn-info waves-effect waves-light text-sm-right" href="{{route('clientsubscription.add')}}"><i class="mdi mdi-plus-circle mr-1"></i> Add </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="client_subscription_table" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Plan') }}</th>
                                    <th>{{ __('Plan Type') }}</th>
                                    <th>{{ __('Timeframe') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Next Due Date') }}</th>
                                    <th>{{ __('Payment Status') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="client_subscription_tbody">
                                
                            </tbody>
                        </table>
                    </div>
                {{ Form::close() }}    
                </div>
                
            </div>
        </div>
    </div>
</div>
<!-- end Content-->

<div id="edit-subscription-payment" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-full-width">
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
 $(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });  
    getSubscriptionList();
}); 

function getSubscriptionList() {
    $('#client_subscription_table').DataTable({
        "dom": '<"toolbar">Bfrtip',
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "iDisplayLength": 50,
        language: {
            search: "",
            info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
            paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
            searchPlaceholder: '{{__("Search By Client, Plan, Price, Start Date, End Date, Due Date")}}'
        },
        "order": [[ 9, "ASC" ]],
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            $(".deletesubscrip").on("click", function() {
                var slug = $(this).attr('data-id');
                Swal.fire({
                    title: "Are you sure?",
                    text:"You want to delete this subscription ?.",
                    showCancelButton: true,
                    confirmButtonText: 'Ok',
                }).then((result) => {
                    if(result.value)
                    {   
                        let route = "{{ route('clientsubscription.delete', ':id') }}";
                        $.ajax({
                            type: "get",
                            dataType: "json",
                            url: route.replace(":id", slug),
                            success: function(jsondata) {
                                if(jsondata.success)
                                {var color = 'green';var heading="Success!";}else{var color = 'red';var heading="Error!";}
                                getSubscriptionList();
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
                                });
                            }
                        });
                    }
                });
            });

            $(".paymentbutton").on("click", function() {
                var slug = $(this).attr('data-id');
                
                var editpayment_details_url = "{{ route('clientsubscription.editpayment', ':id') }}";
                $.ajax({
                    type: "get",
                    dataType: "json",
                    url: editpayment_details_url.replace(":id", slug),
                    success: function(res) {
                        $("#edit-subscription-payment .modal-content").html(res.html);
                        $("#edit-subscription-payment").modal("show");

                        var drEvent = $('.dropify').dropify();
 
                        drEvent.on('dropify.beforeClear', function(event, element){
                            if(confirm("Do you really want to delete ?"))
                            {
                                $("#viewfilelink").remove();
                            }else{
                                return false;
                            }
                        });

                        $("#payment_method").on("change", function() {
                            if($(this).val() == 'Free'){
                                $("#paid_amount").val(0);
                            }else{
                                $("#paid_amount").val($("#real_subs_price").text());
                            }
                        });

                        $("#payment_date").flatpickr({
                            altInput: false,
                            allowInput: true,
                            dateFormat: "d-m-Y",
                        }); 
                    }
                });
            });
        },
        buttons: [{
                className:'btn btn-success waves-effect Export_btn waves-light classbtnhidden',
                id:'exp-btn', 
                text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>{{__("Export CSV")}}',
                action: function ( e, dt, node, config ) {
                    $('#export-form').trigger('submit');
                }
        }],
        ajax: {
            url: "{{route('clientsubscription.filter')}}",
            data: function (d) {
            d.search = $('input[type="search"]').val();
            d.clientid = $('#clientid').val();
            d.subscriptionstatus = $('#subsstatus').val();
            d.paymentstatus = $("#paymentstatus").val();
            d.plantype = $("#plantype").val();
            }
        },
        columns: [
            {data: 'client_name', name: 'client_name',orderable: true, searchable: true},
            {data: 'billing_plan_title', name: 'billing_plan_title',orderable: true, searchable: true},
            {data: 'plan_type_text', name: 'plan_type_text',orderable: true, searchable: true, render: function (dataField) {
                 return '<span class="badge bg-info" style="color:#fff;"> ' + dataField + '</span>'; 
            }},
            {data: 'billing_timeframe_title', name: 'billing_timeframe_title', orderable: true, searchable: true},
            {data: 'billing_price', name: 'billing_price', orderable: true, searchable: true},
            {data: 'start_date_text', name: 'start_date', orderable: true, searchable: true},
            {data: 'end_date_text', name: 'end_date', orderable: true, searchable: true},
            {data: 'next_due_date_text', name: 'next_due_date', orderable: true, searchable: true},
            {data: 'payment_text', name: 'payment_text', orderable: true, searchable: true},
            {data: 'status_text', name: 'status_text', orderable: true, searchable: true, render: function (dataField) {
                if(dataField == "Active"){
                    return '<span class="badge bg-success" style="color:#fff;"><i class="fas fa-check"></i> ' + dataField + '</span>'; 
                }else{
                    return '<span class="badge bg-danger" style="color:#fff;"><i class="fas fa-times-circle"></i> ' + dataField + '</span>'; 
                }
            }},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
}


</script>
@endsection
