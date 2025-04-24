@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('sellers', true)])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<style type="text/css">
@media(min-width: 1440px){.content{min-height: calc(100vh - 100px);}.dataTables_scrollBody {height: calc(100vh - 500px);}}
.dd-list .dd3-item {list-style: none;}
div.dataTables_wrapper div.dataTables_filter input {width: 180px;}
</style>
<style type="text/css">
    .pac-container,.pac-container .pac-item{z-index:99999!important}.fc-v-event{border-color:#43bee1;background-color:#43bee1}.dd-list .dd3-content{position:relative}span.inner-div{top:50%;-webkit-transform:translateY(-50%);-moz-transform:translateY(-50%);transform:translateY(-50%)}.button{position:relative;padding:8px 16px;background:#009579;border:none;outline:0;border-radius:50px;cursor:pointer}.button:active{background:#007a63}.button__text{font:bold 20px Quicksand,san-serif;color:#fff;transition:all .2s}.button--loading .button__text{visibility:hidden;opacity:0}.button--loading::after{content:"";position:absolute;width:16px;height:16px;top:0;left:0;right:0;bottom:0;margin:auto;border:4px solid transparent;border-top-color:#fff;border-radius:50%;animation:button-loading-spinner 1s ease infinite}@keyframes button-loading-spinner{from{transform:rotate(0turn)}to{transform:rotate(1turn)}}
</style>
@endsection
@section('content')
<div class="container-fluid vendor-page">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="page-title-box">

                @php
                $vendors = getNomenclatureName('sellers', true);
                $newvendors = ($vendors === "sellers") ? __('sellers') : $vendors ;
                @endphp

                <h4 class="page-title">{{ $newvendors }}</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card widget-inline">
                <div class="card-body p-2">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <div class="text-center">
                                <h3>
                                    <i class="mdi mdi-storefront text-primary mdi-24px"></i>
                                    <span data-plugin="counterup" id="total_earnings_by_vendors">{{$active_vendor_count}}</span>
                                </h3>
                                <p class="text-muted font-15 mb-0">{{ __('Total') }} {{ $newvendors }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <div class="text-center">
                                <h3>
                                    <i class="mdi mdi-store-24-hour text-primary mdi-24px"></i>
                                    <span data-plugin="counterup" id="total_order_count">{{$available_vendors_count}}</span>
                                </h3>
                                <p class="text-muted font-15 mb-0"> {{ __('Open') }} {{getNomenclatureName('Sellers', true)}}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <div class="text-center">
                                <h3>
                                    <i class="fas fa-money-check-alt text-primary"></i>
                                    <span data-plugin="counterup" id="total_cash_to_collected">{{$vendors_product_count}}</span>
                                </h3>
                                <p class="text-muted font-15 mb-0">{{ __('Total Products') }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <div class="text-center">
                                <h3>
                                    <i class="fas fa-money-check-alt text-primary"></i>
                                    <span data-plugin="counterup" id="total_delivery_fees">{{$vendors_active_order_count}}</span>
                                </h3>
                                <p class="text-muted font-15 mb-0">{{ __('Total Active Orders') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-lg-12 tab-product vendor-products invisible">
            <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-vendor" data-toggle="tab" href="#active_vendor" role="tab" aria-selected="false" data-rel="vendor_active_datatable" data-status="1">
                        <i class="icofont icofont-man-in-glasses"></i>{{ __('Active') }}<sup class="total-items" id="active_vendor_count">({{$active_vendor_count}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="awaiting-vendor" data-toggle="tab" href="#awaiting_vendor" role="tab" aria-selected="true" data-rel="awaiting__Approval_vendor_datatble" data-status="0">
                        <i class="icofont icofont-ui-home"></i>{{ __('Awaiting Approval') }}<sup class="total-items">({{$awaiting__Approval_vendor_count}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="block-vendor" data-toggle="tab" href="#block_vendor" role="tab" aria-selected="false" data-rel="blocked_vendor_datatble" data-rel="blocked_vendor_datatble" data-status="2">
                        <i class="icofont icofont-man-in-glasses"></i>{{ __('Blocked') }}<sup class="total-items">({{$blocked_vendor_count}})</sup>
                    </a>
                    <div class="material-border"></div>
                </li>
                <li class="nav-item">
                    <a class="btn btn-info  waves-effect waves-light text-sm-right action_vendor_button" dataid="0" id="action_vendor_button" href="javascript:void(0);" style="display: none;"><i class="mdi mdi-plus-circle mr-1"></i>{{ __('Action') }}</a>
                    <div class="material-border"></div>
                </li>
            </ul>
            <div class="tab-content nav-material pt-0" id="top-tabContent">
                <div class="tab-pane fade past-order show active" id="active_vendor" role="tabpanel" aria-labelledby="active-vendor">
                    <div class="row">
                        <div class="col-12">

                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <form name="saveOrder" id="saveOrder"> @csrf</form>

                                        <table class="table table-centered table-nowrap table-striped" id="vendor_active_datatable" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" class="all-vendor_check"
                                                                name="all_vendor_id" id="all-vendor_check"></th>
                                                    <th>{{ __('Icon') }}</th>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Address') }}</th>
                                                    <th>{{ __('Offers') }}</th>
                                                    <th class="text-center">{{ __('Can Add') }} <br> {{ __('Category') }}</th>
                                                    <th class="text-center">{{ __('Commission') }} <br> {{ __('Percentage') }}</th>
                                                    <th class="text-center">{{ __('Products') }}</th>
                                                    <th class="text-center">{{ __('Orders') }}</th>
                                                    <th class="text-center">{{ __('Active') }} <br> {{ __('Orders') }}</th>
                                                    <th class="text-center">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="post_list"></tbody>
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
                <div class="tab-pane fade" id="awaiting_vendor" role="tabpanel" aria-labelledby="awaiting-vendor">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <form name="saveOrder" id="saveOrder"> @csrf</form>
                                        <table class="table table-centered table-nowrap table-striped" id="awaiting__Approval_vendor_datatble" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ __("Icon") }}</th>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Address') }}</th>
                                                    <th>{{ __('Offers') }}</th>
                                                    <th class="text-center">{{ __('Can Add') }} <br> {{ __('Category') }}</th>
                                                    <th class="text-center">{{ __('Commission') }} <br> {{ __('Percentage') }}</th>
                                                    <th class="text-center">{{ __('Products') }}</th>
                                                    <th class="text-center">{{ __('Orders') }}</th>
                                                    <th class="text-center">{{ __('Active') }} <br> {{ __('Orders') }}</th>
                                                    <th class="text-center">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="post_list"></tbody>
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
                <div class="tab-pane fade past-order" id="block_vendor" role="tabpanel" aria-labelledby="block-vendor">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-striped" id="blocked_vendor_datatble" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Icon') }}</th>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Address') }}</th>
                                                    <th>{{ __('Offers') }}</th>
                                                    <th class="text-center">{{ __('Can Add') }} <br> {{ __('Category') }}</th>
                                                    <th class="text-center">{{ __('Commission') }} <br> {{ __('Percentage') }}</th>
                                                    <th class="text-center">{{ __("Products") }}</th>
                                                    <th class="text-center">{{ __('Orders') }}</th>
                                                    <th class="text-center">{{ __("Active") }} <br> {{ __("Orders") }}</th>
                                                    <th class="text-center">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="post_list"></tbody>
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
    <div id="edit_vendor_modal" class="modal al fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Edit') }} {{getNomenclatureName('vendors', false)}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="update_vendor_form" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" id="editVendorBox">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info waves-effect waves-light" id="update_vendor_modal">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- start product action popup -->
<div id="action-vendor-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Vendor Action') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>

                <div class="modal-body">

                        <div class="card-box">
                            <form id="save_product_action_modal" method="post" enctype="multipart/form-data"
                            action="#">
                            @csrf

                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    {!! Form::label('title', __('Action For '), ['class' => 'control-label']) !!}
                                    <select class="form-control" id="action_for" name="action_for" required>
                                        <option value="">{{__('Select')}}</option>
                                        <option value="delete">{{__('Delete')}}</option>
                                    </select>
                                </div>



                            </div>
                            <div class="modal-footer">
                                <button type="button"
                                    class="btn btn-info waves-effect waves-light submitVendorAction">{{ __('Submit') }}</button>
                            </div>

                            </form>


                        </div>

                </div>

            </div>
        </div>
    </div>

@include('backend.seller.modals')
<script type="text/template" id="user_id_section">
    <li class="d-flex justify-content-start align-items-center position-relative" id ="user_selected_<%= id %>" data-section_number="<%= id %>">
        <p class="al_checkbox m-0 py-2 ">
            <input type="hidden" class="user_hidden_ids" name="userIDs[]" value="<%= user_id %>" class="mt-2 mr-1">
            <img class="user_img mr-2" src="<%= image %>" alt="">
        </p>
        <p class="al_username m-0 py-2">
            <span> <%= name %> </span>
            <small><%= email %></small>
        </p>
        <sup class="">&#128473;</sup>
    </li>
</script>
@endsection
@section('script')
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
<script type="text/javascript">
    var mobile_number = '';
    var updateVendorAll = '{{route("vendor.updateall")}}';
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
    var input = document.querySelector("#new_user_phone_number");
    if(input){
        window.intlTelInput(input, {
        separateDialCode: true,
        hiddenInput: "contact",
        utilsScript: "{{asset('assets/js/utils.js')}}",
        initialCountry: "{{ Session::get('default_country_code','US') }}",
    });
    }
    var input = document.querySelector("#vendor_phone_number");
    if(input){
        window.intlTelInput(input, {
        separateDialCode: true,
        hiddenInput: "contact",
        utilsScript: "{{asset('assets/js/utils.js')}}",
        initialCountry: "{{ Session::get('default_country_code','US') }}",
    });
    }

    $(document).ready(function() {
        $("#new_user_phone_number").keypress(function(e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            return true;
        });
        $("#vendor_phone_number").keypress(function(e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            return true;
        });
        @if($client_preference_detail->business_type != 'taxi')
            vendorOrderTime();
        @endif
    });
    $('#phone_numberInput .iti__country').click(function() {
        var code = $(this).attr('data-country-code');
        $('#countryData').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#dialCode').val(dial_code);
    });
    $(document).on('click', '#phone_noInput .iti__country', function() {
        var code = $(this).attr('data-country-code');
        $('#vendorCountryCode').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#vendorDialCode').val(dial_code);
    });
    $(document).on('change', '#Vendor_order_pre_time', function(){
        vendorOrderTime();
    });
    function vendorOrderTime(){
       var min = $('#Vendor_order_pre_time').val();
       //alert(min);
       if(min >=60){
            var hours = Math.floor(min / 60);
            var minutes = min % 60;

            if( minutes <= 9)
            minutes ='0'+minutes;

            var txt = '~ '+hours+':'+minutes+" {{__('Hours')}}";
            $('#Vendor_order_pre_time_show').text(txt);
       }else{
            var txt = min+" {{__('Min')}}";
            $('#Vendor_order_pre_time_show').text(txt);
       }
    }
    // $(document).on('change', '.activeCategory', function(){
    //     //var status = $(this).is(":checked");
    //     var category_id = $(this).data('category_id');
    //    var input= "  <input type='hideen' name='category_ids[]' value='"+category_id+"' >";
    //    $('#nestable_list_1').append(input);

    // });
    // $('.iti__country').click(function() {
    //     var code = $(this).attr('data-country-code');
    //     document.getElementById('addCountryData').value = code;
    // })
</script>
@include('backend.vendor.pagescript')
<script src="{{asset('js/admin_seller.js')}}"></script>
<script type="text/javascript">
    var search_text = "{{__('Search By '). getNomenclatureName('sellers', false) . __(' Name')}}";
    var table_info = '{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}';
</script>
@endsection
xxxx