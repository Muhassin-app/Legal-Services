@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Customers'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<style type="text/css">

.datepicker_filter {
  position: relative;
  display: inline-block;
  border-bottom: 1px dotted black;
}

.datepicker_filter .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: black;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;

  /* Position the tooltip */
  position: absolute;
  z-index: 1;
  top: -23px;
    left: 16px;
}

.datepicker_filter:hover .tooltiptext {
  visibility: visible;
}
.iti__flag-container li,
.flag-container li {display: block;}
.iti.iti--allow-dropdown,
.allow-dropdown {position: relative;display: inline-block;width: 100%;}
.iti.iti--allow-dropdown .phone,
.flag-container .phone {padding: 17px 0 17px 100px !important;}
.mdi-icons {color: #43bee1;font-size: 26px;vertical-align: middle;}
.login-form input[type="radio"]:checked+label {
    border: 1px solid #6658dd;
}
.ui-menu.ui-autocomplete {
    z-index: 9000 !important;
}
.al_new_export_table.royo_customber_btn div.dataTables_wrapper div.dataTables_filter {position: absolute;right: 0;top: -92px;}
.al_new_export_table.royo_customber_btn .dt-buttons.btn-group.flex-wrap {position: absolute;top: -92px !important;}

.royo_customber_btn .position-absolute {top: 0;right: 32%;}
.royo_customber_btn .card {background: none !important;box-shadow: none !important;}
.royo_customber_btn .card-body {background: none !important;box-shadow: none !important;}


.table_customber_add.royo_customber_btn div.dataTables_wrapper div.dataTables_filter {position: inherit;top: 0px !important;}
.company-address {
    position: absolute;
    top: 20px;
    width: 100%;
}
@media  screen and (max-width:1800px){
.royo_customber_btn .position-absolute {
    left: 35%;
}

}


@media screen and (max-width:1199px){
.royo_customber_btn .position-absolute {
    left: 5%;
}

}


@media screen and (max-width:991px) {
.royo_customber_btn .position-absolute {
    left: 0%;
}
}

@media screen and (max-width:767px) {
.al_new_export_table.royo_customber_btn div.dataTables_wrapper div.dataTables_filter label input {
    width: 100px !important;
    height: 30px;
    font-size: 10px;
}
.al_new_export_table.royo_customber_btn .dt-buttons.btn-group.flex-wrap{
    left: 12%;
}
.royo_customber_btn .position-absolute .btn.btn-info{
    font-size:10px;
}
.al_new_export_table.royo_customber_btn .dt-buttons.btn-group.flex-wrap .btn-success.waves-effect.waves-light{
    height: 30px;
    font-size: 10px;
}
.al_new_export_table .position-absolute.mb-2{
    top: -4px;
}
}


@media screen and (max-width:520px) {
.al .sml_royo-responsive {
    margin-top: 15% !important;
}
.al_new_export_table.royo_customber_btn .dt-buttons.btn-group.flex-wrap{
    left:0px;
    width:40%;
    text-align:left;
}
.al_new_export_table.royo_customber_btn div.dataTables_wrapper div.dataTables_filter{
    left:43%;
}
.dataTables_filter label{
    float:left !important;
}

}
</style>
@endsection
@section('content')
<div class="container-fluid alCustomersPage">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Customers") }}</h4>
            </div>
        </div>


    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <div class="row">
                    <div class="col-6 col-md-4 mb-3 mb-md-0">
                        <div class="text-center">
                            <h3>
                                <i class="fa fa-user text-primary mdi-24px"></i>
                                <span data-plugin="counterup" id="total_vendor">{{$active_users}}</span>
                            </h3>
                            <p class="text-muted font-15 mb-0">{{ __("Active User Count") }}</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 mb-3 mb-md-0">
                        <div class="text-center">
                            <h3>
                                <i class="fas fa-user-clock text-primary mdi-24px"></i>
                                <span data-plugin="counterup" id="total_product">{{ $inactive_users }}</span>
                            </h3>
                            <p class="text-muted font-15 mb-0">{{ __("Inactive User Count") }}</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 mb-3 mb-md-0">
                        <div class="text-center">
                            <h3>
                                <i class="mdi mdi-login text-primary mdi-24px"></i>
                                <span data-plugin="counterup" id="total_product">{{ $social_logins }}</span>
                            </h3>
                            <p class="text-muted font-15 mb-0">{{ __("Social Login Count") }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row main-customer-page al">
        <div class="col-12">
            <div class="card-box set-height pb-0">
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
                                <span>{!! \Session::get('error_delete') !!}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @php
                    $hub_key = @getAdditionalPreference(['is_hubspot_enable'])['is_hubspot_enable'];
                @endphp
                <div class="al_new_export_table royo_customber_btn table_customber_add">
                    <div class="position-absolute mb-2">



                        @if($hub_key==1)
                            <button class="btn btn-info waves-effect waves-light text-sm-right sync_hubspot" userId="0"><i class="mdi mdi-sync mr-1"></i>{{ __('Sync with hubspot') }}
                            </button>
                        @endif
                        <button class="btn btn-info waves-effect waves-light text-sm-right exportUserModal" data-url="{{ route('customer.export') }}" userId="0"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Export') }}
                        </button>
                        <button class="btn btn-info waves-effect waves-light text-sm-right exportUsersPdf" ><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Export as PDF') }}
                        </button>
                        <button class="btn btn-info waves-effect waves-light text-sm-right importUserModal" userId="0"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Import') }}
                        </button>
                        <button class="btn btn-info waves-effect waves-light text-sm-right addUserModal" userId="0"><i class="mdi mdi-plus-circle mr-1"></i> {{ __("Add") }}
                        </button>
                        <!-- <button type="button" class="btn btn-info waves-effect waves-light" data-toggle="modal" data-target="#pay-receive-modal" data-backdrop="static" data-keyboard="false">{{__("Edit Wallet")}}</button> -->

                        <!-- <div class="col-sm-3 mb-1">
                            <select class="form-control al_box_height company-address" id="company_option_select_box" name="company_id" >
                                <option value="">{{ __('Select Company') }}</option>
                                @forelse($companies as $company)
                                    <option value="{{$company->id}}">{{$company->name}}</option>
                                @empty
                                @endforelse
                            </select>
                        </div> -->
                    </div>


                    <div class="row mt-1 sml_royo-responsive">
                        <div class="col-sm-12 col-lg-12 tab-product  pt-0">
                            <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="active-user" data-toggle="tab" href="#active_user" role="tab" aria-selected="false" data-rel="active_user_datatable" data-status="1">
                                        <i class="icofont icofont-man-in-glasses"></i>{{ __('Active') }}<sup class="total-items" id="active_user_count">({{$active_users}})</sup>
                                    </a>
                                    <div class="material-border"></div>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="inactive-user" data-toggle="tab" href="#inactive_user" role="tab" aria-selected="true" data-rel="inactive_user_datatble" data-status="0">
                                        <i class="icofont icofont-ui-home"></i>{{ __('InActive') }}<sup class="inactive_user_count">({{$inactive_users}})</sup>
                                    </a>
                                    <div class="material-border"></div>
                                </li>
                            </ul>
                            <div class="tab-content nav-material pt-0" id="top-tabContent">
                                <div class="tab-pane fade past-order show active" id="active_user" role="tabpanel" aria-labelledby="active-user">
                                    <div class="row">
                                        <div class="col-12">

                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <form name="saveOrder" id="saveOrder"> @csrf</form>
                                                        @php
                                                            $ordermenu = getNomenclatureName('Orders', true);
                                                            $ordermenulabel = ($ordermenu=="Orders")?__('Orders'):__($ordermenu);

                                                        @endphp
                                                        <table class="table table-centered table-nowrap table-striped" id="user_datatable" width="100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>{{ __('Image')}}</th>
                                                                    <th>{{ __('Name')}}</th>
                                                                    <!-- <th>{{ __('User Type')}}</th> -->
                                                                    <th>{{ __('Refferal Code')}}</th>
                                                                    <th>{{ __('Login Type') }}</th>
                                                                    <th>{{ __('Signup Date')}}</th>
                                                                    <th class="d-flex">{{ __('Last Login') }}
                                                                    <div class="datepicker_filter d-flex ml-1 mt-1">
                                                                        <span class="tooltiptext">No orders placed</span>
                                                                        <i class="fa fa-calendar" title="No orders placed" data-original-title="No orders placed" data-toggle="tooltip" data-placement="top" data-title="No orders placed"></i>
                                                                        <input type="hidden" id="range-datepicker" >
                                                                    </div>
                                                                </th>
                                                                    <th>{{ __('Email/Auth-id')}}</th>
                                                                    <th>{{ __('Phone')}}</th>
                                                                    <th>{{ __("Email OTP") }}</th>
                                                                    <th>{{ __("Phone OTP") }}</th>
                                                                    <th>{{ __('Wallet')}}</th>
                                                                    <th>{{ __($ordermenulabel)}}</th>
                                                                    <th>{{ __('Loyalty Card')}}</th>
                                                                    <th>{{ __('Active '. $ordermenulabel) }}</th>
                                                                    <th>{{ __('Total Order Value') }}</th>
                                                                    <th>{{ __('Total Order Discount') }}</th>
                                                                    <th>{{ __('Status')}}</th>
                                                                    <th>{{ __('Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="post_list">

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row address" id="def" style="display: none;">
                                            <input type="text" id="def-address" name="test" class="autocomplete form-control def_address">
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="inactive_user" role="tabpanel" aria-labelledby="inactive-user">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <form name="saveOrder" id="saveOrder"> @csrf</form>
                                                        <table class="table table-centered table-nowrap table-striped" id="inactive_user_datatable" width="100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>{{ __('Image')}}</th>
                                                                    <th>{{ __('Name')}}</th>
                                                                    <!-- <th>{{ __('User Type')}}</th> -->
                                                                    <th>{{ __('Login Type') }}</th>
                                                                    <th>{{ __('Signup Date')}}</th>
                                                                    <th>{{ __('Last Login') }}</th>
                                                                    <th>{{ __('Email/Auth-id')}}</th>
                                                                    <th>{{ __('Phone')}}</th>
                                                                    <th>{{ __("Email OTP") }}</th>
                                                                    <th>{{ __("Phone OTP") }}</th>
                                                                    <th>{{ __('Wallet')}}</th>
                                                                    <th>{{ __('Orders')}}</th>
                                                                    <th>{{ __('Loyalty Card')}}</th>
                                                                    <th>{{ __('Active Orders') }}</th>
                                                                    <th>{{ __('Total Order Value') }}</th>
                                                                    <th>{{ __('Total Order Discount') }}</th>
                                                                    <th>{{ __('Status')}}</th>
                                                                    <th>{{ __('Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="post_list">

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row address" id="def" style="display: none;">
                                            <input type="text" id="def-address" name="test" class="autocomplete form-control def_address">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('backend.users.modals')
<script type="text/javascript">
    $(document).ready(function() {

        var table;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
         initDataTable('user_datatable','active');
        $(document).on("click","#active-user",function() {
            $('input[type="search"]').val('');
            initDataTable('user_datatable','active');
        });

        $(document).on("click","#inactive-user",function() {
            $('input[type="search"]').val('');
            initDataTable('inactive_user_datatable','inactive');
        });


        $(document).on("change","#company_option_select_box",function() {
            initDataTable('user_datatable','active');
        });

        $(document).on("click", ".delete-vendor", function() {
            var destroy_url = $(this).data('destroy_url');
            var id = $(this).data('rel');
            if (confirm('Are you sure?')) {
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: destroy_url,
                    data: {
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "Success") {
                            $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                            window.location.reload();
                        }
                    }
                });
            }
        });


        // function initDataTable(table,type) {
             
        //     try {
        //         var ph_number = '';
        //         $('#'+table).DataTable({
        //             "dom": '<"toolbar">Bfrtip',
        //             "responsive": true,
        //             "destroy": true,
        //             "searching": true,
        //             "scrollX": false,
        //             "processing": true,
        //             "serverSide": true,
        //             "iDisplayLength": 20,
        //             language: {
        //                 search: "",
        //                 info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
        //                 paginate: {
        //                     previous: "<i class='mdi mdi-chevron-left'>",
        //                     next: "<i class='mdi mdi-chevron-right'>"
        //                 },
        //                 searchPlaceholder: '{{__("Search ")}}'
        //             },
        //             drawCallback: function() {
        //                 $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        //             },
        //             buttons: [{
        //                 className: 'btn btn-success waves-effect waves-light',
        //                 text: '<span class="btn-label"><i class="mdi mdi-file-pdf-box"></i></span>{{__("Export CSV")}}',
        //                 action: function(e, dt, node, config) {
        //                     window.location.href = "{{ route('customer.export') }}";
        //                 }
        //             },
        //                     {
        //                         extend: 'pdf',
        //                         text: 'Export to PDF',
        //                         className:'btn btn-success waves-effect Export_btn waves-light ml-2',
        //                         id:'exp-btn',
        //                         text: '<span class="btn-label"><i class="mdi mdi-file-pdf-box"></i></span>Export PDF',
        //                         orientation: 'landscape',
        //                         exportOptions: {
        //                             columns: ':visible'
        //                         },
        //                         customize: function (doc) {
        //                         doc.pageOrientation = 'landscape';
        //                         doc.pageSize = 'A3'; // Set the custom page size
        //                     }
        //                     }],
        //             ajax: {
        //                 url: "{{route('user.filterdata')}}",
        //                 data: function(d) {
        //                     d._token = "{{ csrf_token() }}";
        //                     // d.search = $('input[type="search"]').val();
        //                     d.search = $('#'+table).DataTable().search();
        //                     d.date_filter = $('#range-datepicker').val();
        //                     d.payment_option = $('#payment_option_select_box option:selected').val();
        //                     d.tax_type_filter = $('#tax_type_select_box option:selected').val();
        //                     d.company_filter = $('#company_option_select_box option:selected').val();
        //                     d.type = type;
        //                 }
        //             },
        //             "initComplete": function(settings, json) {
        //                 // var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
        //                 // elems.forEach(function(html) {
        //                 //     var switchery = new Switchery(html);
        //                 // });
        //                 $('.dataTables_filter input[type="search"]').css({
        //                     'width': '280px',
        //                     'display': 'inline-block'
        //                 });
        //                 $("#user_datatable_wrapper").find($(".dt-buttons.btn-group.flex-wrap")).css({
        //                     'right': '320px'
        //                 });
        //             },
        //             columns: [
        //                 {
        //                     data: 'id',
        //                     name: 'id',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, nRow, meta) {
        //                         return meta.row + meta.settings._iDisplayStart + 1;
        //                     }
        //                 },
        //                 {
        //                     data: 'image_url',
        //                     name: 'image_url',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, full) {
        //                         return "<img src='" + full.image_url + "' class='rounded-circle' alt='" + full.id + "' >";
        //                     }
        //                 },
        //                 {
        //                     data: 'name',
        //                     name: 'name',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, full) {
        //                         var improtId = '';
        //                         if(full.import_user_id){
        //                             improtId = "<br>("+ full.import_user_id +")";
        //                         }
        //                         return "<a href='" + full.edit_url + "'>" + full.name + "</a>"+ improtId;
        //                     }
        //                 },
        //                 // {
        //                 //     data: 'user_type',
        //                 //     name: 'user_type',
        //                 //     orderable: false,
        //                 //     searchable: false
        //                 // },
        //                 {
        //                     data: 'refferal',
        //                     name: 'refferal',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'login_type',
        //                     name: 'login_type',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'signup_date',
        //                     name: 'signup_date',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'last_login',
        //                     name: 'last_login',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'login_type_value',
        //                     name: 'login_type_value',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, full) {
        //                         if (full.is_email_verified == 1) {
        //                             return "<i class='mdi mdi-email-check mr-1 mdi-icons'></i>" + full.login_type_value;
        //                         } else {
        //                             return "<i class='mdi mdi-email-sync mr-1 mdi-icons'></i>" + full.login_type_value;
        //                         }
        //                     }
        //                 },
        //                 {
        //                     data: 'is_phone_verified',
        //                     name: 'is_phone_verified',
        //                     orderable: false,
        //                     searchable: true,
        //                     "mRender": function(data, type, full) {
        //                         if(full.dial_code){
        //                         var dialcode = full.dial_code;
        //                          ph_number = '+'+ dialcode + full.phone_number;
        //                         }else{
        //                             ph_number =  full.phone_number;
        //                         }

        //                         if (full.is_phone_verified == 1) {
        //                             if (full.phone_number) {
        //                                 return "<i class='mdi mdi-phone-check mr-1 mdi-icons'></i>" + ph_number;
        //                             } else {
        //                                 return "";
        //                             }
        //                         } else {
        //                             if (ph_number) {
        //                                 return "<i class='mdi mdi-phone mr-1 mdi-icons'></i>" + ph_number;
        //                             } else {
        //                                 return "";
        //                             }
        //                         }
        //                     }
        //                 },
        //                 {
        //                     data: 'email_token',
        //                     name: 'email_token',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'phone_token',
        //                     name: 'phone_token',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'balanceFloat',
        //                     name: 'balanceFloat',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, full) {
        //                         return "<a href='javascript:void(0)' class='customer_wallet_link' data-id='" + full.wallet_id + "'>" + data + "</a>";
        //                     }
        //                 },
        //                 {
        //                     data: 'orders_count',
        //                     name: 'orders_count',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, full) {

        //                         return "<a href='javascript:void(0)' class='customer_order_link'  data-id='" + full.id + "'>" + data + "</a>";
        //                     }
        //                 },
        //                  {
        //                     data: 'loyalty_name',
        //                     name: 'loyalty_name',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, full) {
        //                         return  data.loyalty_name +" ("+ data.count_loyalty_points_earned+")" ;
        //                     }
        //                 },
        //                 {
        //                     data: 'currently_working_orders_count',
        //                     name: 'currently_working_orders_count',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'total_order_value',
        //                     name: 'total_order_value',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'total_discount_value',
        //                     name: 'total_discount_value',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'status',
        //                     name: 'status',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, full) {
        //                         if (full.status == 1) {
        //                             return "<input type='checkbox' data-id='" + full.id + "' id='cur_" + full.id + "' data-plugin='switchery' name='userAccountStatus' class='chk_box' data-color='#43bee1' checked>";
        //                         } else {
        //                             return "<input type='checkbox' data-id='" + full.id + "' id='cur_" + full.id + "' data-plugin='switchery' name='userAccountStatus' class='chk_box' data-color='#43bee1'>";
        //                         }
        //                     }
        //                 },
        //                 {
        //                     data: 'is_superadmin',
        //                     name: 'is_superadmin',
        //                     orderable: false,
        //                     searchable: false,
        //                     "mRender": function(data, type, full) {
        //                         if (full.is_superadmin == 1 || canManageAdmins) {
        //                             return "<div class='form-ul'><div class='inner-div'><a href='" + full.edit_url + "' class='action-icon editIconBtn'><i class='mdi mdi-square-edit-outline'></i></a><a href='" + full.delete_url + "' class='action-icon delete_customer'><i class='mdi mdi-delete' title='Delete user'></i></a></div></div>";
        //                         }else{
        //                             return "-";
        //                         }
        //                     }
        //                 },
        //             ],
        //             "drawCallback": function (settings, json) {
        //                 var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
        //                 elems.forEach(function(html) {
        //                     var switchery = new Switchery(html);
        //                 });
        //                 $('.dataTables_filter input[type="search"]').css({
        //                     'width': '280px',
        //                     'display': 'inline-block'
        //                 });
        //                 $("#user_datatable_wrapper").find($(".dt-buttons.btn-group.flex-wrap")).css({
        //                     'right': '320px'
        //                 });
        //             }
        //         });
        //     } finally {
        //         var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
        //         // console.log(elems);
        //         elems.forEach(function(html) {
        //             var switchery = new Switchery(html);
        //         });
        //     }
        // }

        function initDataTable(table,type) {
            try {
                var ph_number = '';
                $('#'+table).DataTable({
                    "dom": '<"toolbar">Bfrtip',
                    "responsive": true,
                    "destroy": true,
                    "searching": true,
                    "scrollX": false,
                    "processing": true,
                    "serverSide": true,
                    "iDisplayLength": 20,
                    language: {
                        search: "",
                        info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        },
                        searchPlaceholder: '{{__("Search ")}}'
                    },
                    drawCallback: function() {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    },
                    buttons: [{
                        className: 'btn btn-success waves-effect waves-light',
                        text: '<span class="btn-label"><i class="mdi mdi-file-pdf-box"></i></span>{{__("Export CSV")}}',
                        action: function(e, dt, node, config) {
                            window.location.href = "{{ route('customer.export') }}";
                        }
                    },
                            {
                                extend: 'pdf',
                                text: 'Export to PDF',
                                className:'btn btn-success waves-effect Export_btn waves-light ml-2',
                                id:'exp-btn',
                                text: '<span class="btn-label"><i class="mdi mdi-file-pdf-box"></i></span>Export PDF',
                                orientation: 'landscape',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                customize: function (doc) {
                                doc.pageOrientation = 'landscape';
                                doc.pageSize = 'A3'; // Set the custom page size
                            }
                            }],
                    ajax: {
                        url: "{{route('user.filterdata')}}",
                        data: function(d) {
                            d._token = "{{ csrf_token() }}";
                            // d.search = $('input[type="search"]').val();
                            d.search = $('#'+table).DataTable().search();
                            d.date_filter = $('#range-datepicker').val();
                            d.payment_option = $('#payment_option_select_box option:selected').val();
                            d.tax_type_filter = $('#tax_type_select_box option:selected').val();
                            d.company_filter = $('#company_option_select_box option:selected').val();
                            d.type = type;
                        }
                    },
                    "initComplete": function(settings, json) {
                        // var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
                        // elems.forEach(function(html) {
                        //     var switchery = new Switchery(html);
                        // });
                        $('.dataTables_filter input[type="search"]').css({
                            'width': '280px',
                            'display': 'inline-block'
                        });
                        $("#user_datatable_wrapper").find($(".dt-buttons.btn-group.flex-wrap")).css({
                            'right': '320px'
                        });
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, nRow, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'image_url',
                            name: 'image_url',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                return "<img src='" + full.image_url + "' class='rounded-circle' alt='" + full.id + "' >";
                            }
                        },
                        {
                            data: 'name',
                            name: 'name',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                var improtId = '';
                                if(full.import_user_id){
                                    improtId = "<br>("+ full.import_user_id +")";
                                }
                                return "<a href='" + full.edit_url + "'>" + full.name + "</a>"+ improtId;
                            }
                        },
                        {
                            data: 'user_type',
                            name: 'user_type',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'login_type',
                            name: 'login_type',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'signup_date',
                            name: 'signup_date',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'last_login',
                            name: 'last_login',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'login_type_value',
                            name: 'login_type_value',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                if (full.is_email_verified == 1) {
                                    return "<i class='mdi mdi-email-check mr-1 mdi-icons'></i>" + full.login_type_value;
                                } else {
                                    return "<i class='mdi mdi-email-sync mr-1 mdi-icons'></i>" + full.login_type_value;
                                }
                            }
                        },
                        {
                            data: 'is_phone_verified',
                            name: 'is_phone_verified',
                            orderable: false,
                            searchable: true,
                            "mRender": function(data, type, full) {
                                if(full.dial_code){
                                var dialcode = full.dial_code;
                                 ph_number = '+'+ dialcode + full.phone_number;
                                }else{
                                    ph_number =  full.phone_number;
                                }

                                if (full.is_phone_verified == 1) {
                                    if (full.phone_number) {
                                        return "<i class='mdi mdi-phone-check mr-1 mdi-icons'></i>" + ph_number;
                                    } else {
                                        return "";
                                    }
                                } else {
                                    if (ph_number) {
                                        return "<i class='mdi mdi-phone mr-1 mdi-icons'></i>" + ph_number;
                                    } else {
                                        return "";
                                    }
                                }
                            }
                        },

                        
                        // {
                        //     data: 'email_token',
                        //     name: 'email_token',
                        //     orderable: false,
                        //     searchable: false,
                        //     "mRender": function(data, type, full) {
                        //         return "<p>" + full.email_token + "</p>";
                        //     }
                        // },
                        // {
                        //     data: 'phone_token',
                        //     name: 'phone_token',
                        //     orderable: false,
                        //     searchable: false,
                        //     "mRender": function(data, type, full) {
                        //         return "<p>" + full.phone_token + "</p>";
                        //     }
                        // },
                        {
                            data: 'balanceFloat',
                            name: 'balanceFloat',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                return "<a href='javascript:void(0)' class='customer_wallet_link' data-id='" + full.wallet_id + "'>" + data + "</a>";
                            }
                        },
                        {
                            data: 'orders_count',
                            name: 'orders_count',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {

                                return "<a href='javascript:void(0)' class='customer_order_link'  data-id='" + full.id + "'>" + data + "</a>";
                            }
                        },
                         {
                            data: 'loyalty_name',
                            name: 'loyalty_name',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                return  data.loyalty_name +" ("+ data.count_loyalty_points_earned+")" ;
                            }
                        },
                        {
                            data: 'currently_working_orders_count',
                            name: 'currently_working_orders_count',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'total_order_value',
                            name: 'total_order_value',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'total_discount_value',
                            name: 'total_discount_value',
                            orderable: false,
                            searchable: false
                        },


                        {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                if (full.status == 1) {
                                    return "<input type='checkbox' data-id='" + full.id + "' id='cur_" + full.id + "' data-plugin='switchery' name='userAccountStatus' class='chk_box' data-color='#43bee1' checked>";
                                } else {
                                    return "<input type='checkbox' data-id='" + full.id + "' id='cur_" + full.id + "' data-plugin='switchery' name='userAccountStatus' class='chk_box' data-color='#43bee1'>";
                                }
                            }
                        },
                        {
                            data: 'is_superadmin',
                            name: 'is_superadmin',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                if (full.is_superadmin == 1) {
                                    return "<div class='form-ul'><div class='inner-div'><a href='" + full.edit_url + "' class='action-icon editIconBtn'><i class='mdi mdi-square-edit-outline'></i></a><a href='" + full.delete_url + "' class='action-icon delete_customer'><i class='mdi mdi-delete' title='Delete user'></i></a></div></div>";
                                }else{
                                    return "-";
                                }
                            }
                        },
                    ],
                    "drawCallback": function (settings, json) {
                        var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
                        elems.forEach(function(html) {
                            var switchery = new Switchery(html);
                        });
                        $('.dataTables_filter input[type="search"]').css({
                            'width': '280px',
                            'display': 'inline-block'
                        });
                        $("#user_datatable_wrapper").find($(".dt-buttons.btn-group.flex-wrap")).css({
                            'right': '320px'
                        });
                    }
                });
            } finally {
                var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
                // console.log(elems);
                elems.forEach(function(html) {
                    var switchery = new Switchery(html);
                });
            }
        }


    });

    $(document).delegate(".customer_wallet_link", "click", function() {
        let id = $(this).attr("data-id");
        $('#customer-wallet-transactions-modal').modal('show');
        $('#customer_wallet_transactions_datatable').DataTable({
            "dom": '<"toolbar">Bfrtip',
            "destroy": true,
            "scrollX": true,
            "searching": true,
            "processing": true,
            "serverSide": true,
            "iDisplayLength": 10,
            language: {
                search: "",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                },
                searchPlaceholder: "Search By Date, Description, Amount"
            },
            drawCallback: function() {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            buttons: [],
            ajax: {
                url: "{{route('customer.filterWalletTransactions')}}",
                data: function(d) {
                    d.search = $('input[aria-controls="customer_wallet_transactions_datatable"]').val();
                    d.walletId = id;
                }
            },
            "initComplete": function(settings, json) {

            },
            columnDefs: [{
                targets: [1, 3],
                className: "text-nowrap",
            }],
            columns: [
                {data: '', name: 'serial', orderable: false, searchable: false, render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }},
                {
                    data: 'date',
                    name: 'date',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'description',
                    name: 'description',
                    orderable: false,
                    searchable: false,
                    "mRender": function(data, type, full) {
                        return '<span>' + data + '</span>';
                    }
                },
                {
                    data: 'amount',
                    name: 'amount',
                    orderable: false,
                    searchable: false,
                    "mRender": function(data, type, full) {
                        return '<span class="text-right ' + ((full.type == 'deposit') ? 'text-success' : ((full.type == 'withdraw') ? 'text-danger' : '')) + '">' + data + '</span>';
                    }
                },
                {data: 'remarks', name: 'remarks', orderable: false, searchable: false},
                {data: 'created_by', name: 'created_by', orderable: false, searchable: false}
            ]
        });
    });

    $(document).on('click','.delete_customer',function(e){
        var submit_url = $(this).attr('href');
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('You want to delete this customer.')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                window.location.href = submit_url;
            }else{
               return false;
            }
        });
        return false;
    })
</script>
@endsection
@section('script')
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
{{-- hubspot integration--}}
@if(@$hub_key==1)
<script src="{{asset('assets/libs/hubspot/hubspot.js')}}"></script>
<script>
     $(document).on('click','.sync_hubspot',function(e){
        //var submit_url = $(this).attr('href');
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('You want sync data with hubspot.')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                syncHubspotData();

            }else{
               return false;
            }
        });
        return false;
    })
</script>
@endif
{{-- end --}}

<script type="text/javascript">
        var canManageAdmins = @json(auth()->user()->can('can-manage-admins'));
    var mobile_number = '';
    // $('#add-agent-modal .xyz').val(mobile_number.getSelectedCountryData().dialCode);
    $('#add-agent-modal .xyz').change(function() {
        var phonevalue = $('.xyz').val();
        $("#countryCode").val(mobile_number.getSelectedCountryData().dialCode);
    });

    function phoneInput() {
        console.log('phone working');
        var input = document.querySelector(".xyz");

        var mobile_number_input = document.querySelector(".xyz");
        mobile_number = window.intlTelInput(mobile_number_input, {
            separateDialCode: true,
            hiddenInput: "full_number",
            utilsScript: "{{ asset('telinput/js/utils.js') }}",
        });
    }
    var input = document.querySelector("#phone");
    window.intlTelInput(input, {
        separateDialCode: true,
        hiddenInput: "contact",
        utilsScript: "{{asset('assets/js/utils.js')}}",
        initialCountry: "{{ Session::get('default_country_code','US') }}",
    });
    $(document).ready(function() {
        $("#phone").keypress(function(e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            return true;
        });
    });
    $('.iti__country').click(function() {
        var code = $(this).attr('data-country-code');
        $('#countryData').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#dialCode').val(dial_code);
    });
    // $('.iti__country').click(function() {
    //     var code = $(this).attr('data-country-code');
    //     document.getElementById('addCountryData').value = code;
    // })

    $('.datepicker_filter i').click(function(){
        var start   = moment().subtract(29, 'days');
        var end     = moment();
        $('.datepicker_filter').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);


    });
    function cb(start, end) {
        $('.datepicker_filter i').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        var start_date  = start.format('Y-M-D');
        var end_date    = end.format('Y-M-D');
        var datesearch  = start_date+','+end_date;
        var exporturl   = $(".exportUserModal").attr('data-url');
        exporturl  = exporturl+'?start_date='+start_date+'&end_date='+end_date;
        $(".exportUserModal").attr('data-url',exporturl);


        $('#user_datatable').DataTable({
                    "dom": '<"toolbar">Bfrtip',
                    "responsive": true,
                    "searching": true,
                    "destroy": true,
                    "scrollX": true,
                    "processing": true,
                    "serverSide": true,
                    "iDisplayLength": 10,
                    language: {
                        search: "",
                        info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        },
                        searchPlaceholder: '{{__("Search ")}}'
                    },
                    drawCallback: function(data) {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");

                    },
                    buttons: [{
                        className: 'btn btn-success waves-effect waves-light',
                        text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>{{__("Export CSV")}}',
                        action: function(e, dt, node, config) {
                            window.location.href = "{{ route('customer.export') }}";
                        }
                    },
                            {
                             extend: 'pdf',
                                text: 'Export to PDF',
                                className:'btn btn-success waves-effect Export_btn waves-light ml-2',
                                id:'exp-btn',
                                text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>Export PDF',
                                orientation: 'landscape',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                customize: function (doc) {
                                doc.pageOrientation = 'landscape';
                                doc.pageSize = 'A3'; // Set the custom page size
                            }
                            }],
                    ajax: {
                        url: "{{route('user.filterdata')}}",
                        data: function(d) {
                            d._token = "{{ csrf_token() }}";
                            d.search = $('input[type="search"]').val();
                            d.date_filter = datesearch;
                            d.payment_option = $('#payment_option_select_box option:selected').val();
                            d.tax_type_filter = $('#tax_type_select_box option:selected').val();
                        }
                    },
                    "initComplete": function(settings, json) {
                        // var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
                        // elems.forEach(function(html) {
                        //     var switchery = new Switchery(html);
                        // });
                        $('.dataTables_filter input[type="search"]').css({
                            'width': '280px',
                            'display': 'inline-block'
                        });
                        $("#user_datatable_wrapper").find($(".dt-buttons.btn-group.flex-wrap")).css({
                            'right': '320px'
                        });
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, nRow, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'image_url',
                            name: 'image_url',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                return "<img src='" + full.image_url + "' class='rounded-circle' alt='" + full.id + "' >";
                            }
                        },
                        {
                            data: 'name',
                            name: 'name',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                var improtId = '';
                                if(full.import_user_id){
                                    improtId = "<br>("+ full.import_user_id +")";
                                }
                                return "<a href='" + full.edit_url + "'>" + full.name + "</a>"+ improtId;
                            }
                        },
                        // {
                        //     data: 'user_type',
                        //     name: 'user_type',
                        //     orderable: false,
                        //     searchable: false
                        // },
                        {
                            data: 'login_type',
                            name: 'login_type',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'signup_date',
                            name: 'signup_date',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'last_login',
                            name: 'last_login',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'login_type_value',
                            name: 'login_type_value',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                if (full.is_email_verified == 1) {
                                    return "<i class='mdi mdi-email-check mr-1 mdi-icons'></i>" + full.login_type_value;
                                } else {
                                    return "<i class='mdi mdi-email-sync mr-1 mdi-icons'></i>" + full.login_type_value;
                                }
                            }
                        },
                        {
                            data: 'is_phone_verified',
                            name: 'is_phone_verified',
                            orderable: false,
                            searchable: true,
                            "mRender": function(data, type, full) {
                                if(full.dial_code){
                                var dialcode = full.dial_code;
                                full.phone_number = '+'+ dialcode + full.phone_number;
                                }else{
                                    full.phone_number =  full.phone_number;
                                }

                                if (full.is_phone_verified == 1) {
                                    if (full.phone_number) {
                                        return "<i class='mdi mdi-phone-check mr-1 mdi-icons'></i>" + full.phone_number;
                                    } else {
                                        return "";
                                    }
                                } else {
                                    if (full.phone_number) {
                                        return "<i class='mdi mdi-phone mr-1 mdi-icons'></i>" + full.phone_number;
                                    } else {
                                        return "";
                                    }
                                }
                            }
                        },
                        {
                            data: 'email_token',
                            name: 'email_token',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'phone_token',
                            name: 'phone_token',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'balanceFloat',
                            name: 'balanceFloat',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                return "<a href='javascript:void(0)' class='customer_wallet_link' data-id='" + full.wallet_id + "'>" + data + "</a>";
                            }
                        },
                        {
                            data: 'orders_count',
                            name: 'orders_count',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {

                                return "<a href='javascript:void(0)' class='customer_order_link'  data-id='" + full.id + "'>" + data + "</a>";
                            }
                        },
                         {
                            data: 'loyalty_name',
                            name: 'loyalty_name',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                return  data.loyalty_name +" ("+ data.count_loyalty_points_earned+")" ;
                            }
                        },
                        {
                            data: 'currently_working_orders_count',
                            name: 'currently_working_orders_count',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'total_order_value',
                            name: 'total_order_value',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'total_discount_value',
                            name: 'total_discount_value',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                if (full.status == 1) {
                                    return "<input type='checkbox' data-id='" + full.id + "' id='cur_" + full.id + "' data-plugin='switchery' name='userAccountStatus' class='chk_box' data-color='#43bee1' checked>";
                                } else {
                                    return "<input type='checkbox' data-id='" + full.id + "' id='cur_" + full.id + "' data-plugin='switchery' name='userAccountStatus' class='chk_box' data-color='#43bee1'>";
                                }
                            }
                        },
                        {
                            data: 'is_superadmin',
                            name: 'is_superadmin',
                            orderable: false,
                            searchable: false,
                            "mRender": function(data, type, full) {
                                if (full.is_superadmin == 1) {
                                    return "<div class='form-ul'><div class='inner-div'><a href='" + full.edit_url + "' class='action-icon editIconBtn'><i class='mdi mdi-square-edit-outline'></i></a><a href='" + full.delete_url + "' class='action-icon delete_customer'><i class='mdi mdi-delete' title='Delete user'></i></a></div></div>";
                                }else{
                                    return "-";
                                }
                            }
                        },
                    ],
                    "drawCallback": function (settings, json) {
                        var elems = Array.prototype.slice.call(document.querySelectorAll('.chk_box'));
                        elems.forEach(function(html) {
                            var switchery = new Switchery(html);
                        });


                        $('.dataTables_filter input[type="search"]').css({
                            'width': '280px',
                            'display': 'inline-block'
                        });
                        $("#user_datatable_wrapper").find($(".dt-buttons.btn-group.flex-wrap")).css({
                            'right': '320px'
                        });
                    }
                });



       // console.log('start_date',start_date,'end_date',end_date);
    }
    $('.exportUsersPdf').click(function(){

                  $('.buttons-pdf').click();
    });
</script>
@include('backend.users.pagescript')
@include('backend.export_pdf')

@endsection
