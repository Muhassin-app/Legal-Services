@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('vendors', true)])

@section('css')
    <link href="{{ asset('assets/libs/fullcalendar-list/fullcalendar-list.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .pac-container,
        .pac-container .pac-item {
            z-index: 99999 !important;
        }

        .fc-v-event {
            border-color: #43bee1;
            background-color: #43bee1;
        }

        .dd-list .dd3-content {
            position: relative;
        }

        .form-control:disabled, .form-control[readonly] {
            background-color: #f2f2f2;
        }

        span.inner-div {
            top: 50%;
            -webkit-transform: translateY(-50%);
            -moz-transform: translateY(-50%);
            transform: translateY(-50%);
        }

        .button {
            position: relative;
            padding: 8px 16px;
            background: #009579;
            border: none;
            outline: none;
            border-radius: 50px;
            cursor: pointer;
        }

        .button:active {
            background: #007a63;
        }

        .button__text {
            font: bold 20px "Quicksand", san-serif;
            color: #ffffff;
            transition: all 0.2s;
        }

        .button--loading .button__text {
            visibility: hidden;
            opacity: 0;
        }

        .button--loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 4px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: button-loading-spinner 1s ease infinite;
        }

        @keyframes button-loading-spinner {
            from {
                transform: rotate(0turn);
            }

            to {
                transform: rotate(1turn);
            }
        }

    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 d-flex align-items-center">
                <div class="page-title-box">
                    <h4 class="page-title">{{ ucfirst($vendor->name) }} {{ __('profile') }}</h4>
                </div>
                <div class="form-group mb-0 ml-3">
                    <div class="site_link position-relative">
                        <a href="{{ route('vendorDetail', $vendor->slug) }}" target="_blank"><span id="pwd_spn"
                                class="password-span">{{ route('vendorDetail', $vendor->slug) }}</span></a>
                        <label class="copy_link float-right" id="cp_btn" title="copy">
                            <img src="{{ asset('assets/icons/domain_copy_icon.svg') }}" alt="">
                            <span class="copied_txt" id="show_copy_msg_on_click_copy"
                                style="display:none;">{{ __('Copied') }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-sm-12">
                <div class="text-sm-left">

                    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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
        <div class="row">
            <div class="col-lg-3 col-xl-3">
                @include('backend.vendor.show-md-3')
            </div>
            <div class="col-lg-9 col-xl-9">
                <div>
                    @include('backend.vendor.topbar-tabs')
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card widget-inline">
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                            <div class="text-center">
                                                <h3>
                                                    <i class="fas fa-money-check-alt text-info"></i>
                                                    <span data-plugin="counterup" >{{ $total_order_value }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Lifetime Order Value') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                            <div class="text-center">
                                                <h3>
                                                    <i class="fas fa-money-check-alt text-info"></i>
                                                    <span data-plugin="counterup" >{{ $total_admin_commissions }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Admin Charges') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                            <div class="text-center">
                                                <h3>
                                                    <i class="fas fa-money-check-alt text-info"></i>
                                                    <span data-plugin="counterup" >{{ $total_promo_amount }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Promo Discount') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                            <div class="text-center">
                                                <h3>
                                                    <i class="fas fa-money-check-alt text-info"></i>
                                                    <span data-plugin="counterup" >{{ $past_payout_value }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Past Payouts') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3 col-lg mb-3 mb-md-0">
                                            <div class="text-center">
                                                <h3>
                                                    <i class="fas fa-money-check-alt text-info"></i>
                                                    <span data-plugin="counterup" >{{ $available_funds }}</span>
                                                </h3>
                                                <p class="text-muted font-15 mb-0">{{ __('Available Funds') }}</p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane {{ $tab == 'configuration' ? 'active show' : '' }} card-body"
                            id="configuration"></div>
                        <div class="tab-pane {{ $tab == 'category' ? 'active show' : '' }}" id="category"></div>
                        <div class="tab-pane {{ $tab == 'catalog' ? 'active show' : '' }}" id="catalog"></div>
                        <div class="tab-pane {{ $tab == 'payout' ? 'active show' : '' }}" id="payout">
                            <div class="card-box">
                                <div class="row">
                                    <div class="col-6">
                                        <h4 class="mb-0"> {{ __('Payout') }}</h4>
                                    </div>
                                    <div class="col-6 d-flex align-items-center justify-content-end mb-3">

                                        @foreach ($payout_options as $opt)
                                            @if($opt->code != 'cash')
                                            @if($opt->is_connected == 1)
                                                <h5 class="mr-2">
                                                    <i class="fa fa-check text-success mr-2"></i><b>{{ __('Connected to') .' '. __($opt->title) }}</b>
                                                </h5>
                                            @else
                                                <button type="button" class="btn btn-info waves-effect text-sm-right connect_btn mr-2" id="{{$opt->code}}_connect_btn" 
                                                    @if($opt->code == 'stripe')
                                                        onclick="location.href='{{$opt->stripe_connect_url}}'";
                                                    @elseif($opt->code == 'razorpay')
                                                    data-toggle="modal" data-target="#razorpay-connect-modal"
                                                    @endif
                                                data-vendor="{{$vendor->id}}"
                                                data-payout_option="{{$opt->code}}"
                                                >
                                                    {{ __("Connect to") .' '. __($opt->title) }}
                                                </button>
                                            @endif
                                            @endif
                                        @endforeach

                                        <button type="button" class="btn btn-info waves-effect text-sm-right" data-toggle="modal" data-target="#pay-receive-modal">{{ __("Payout") }}</button>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-centered table-nowrap table-striped" id="vendor_payouts_datatable" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __("Date") }}</th>
                                                        <th >{{ __("Amount") }}</th>
                                                        <th>{{ __("Type") }}</th>
                                                        {{-- <th>{{ __("Action") }}</th> --}}
                                                        <th>{{ __("Status") }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="vendor_payouts_tbody_list">

                                                </tbody>
                                            </table>
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

    <div id="pay-receive-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h4 class="modal-title">Payout</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="payout_form">
                    @csrf
                    <div class="modal-body px-3 py-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-1" class="control-label">Amount</label>
                                    <input name="payout_amount" id="payout_amount" type="text" class="form-control" placeholder="3000" required onkeypress="return isNumberKey(event)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-2" class="control-label">Available Funds</label>
                                    <input type="text" id="available_funds" class="form-control" value="{{ $available_funds }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            @foreach($payout_options as $opt)
                                @if( (isset($opt->is_connected) && ($opt->is_connected)) || ($opt->code == 'cash') )
                                    <div class="col-md-12 mb-2">
                                        <div class="radio radio-blue form-check-inline">
                                            <input type="radio" id="{{$opt->code}}" value="{{$opt->id}}" name="payout_option">
                                            <label for="{{$opt->code}}"> {{ $opt->title }} </label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div id="payout_response">
                                    <div class="alert alert-danger p-1" style="display:none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-info waves-effect waves-light">Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form id="new_vendor_payout_form" method="POST" action="{{route('vendor.payout.create', $vendor->id)}}">
                @csrf
                <input type="hidden" name="payout_option_id" id="payout_option_id" value="">
                <input type="hidden" name="transaction_id" id="transaction_id" value="">
                <input type="hidden" name="amount" id="amount" value="">
                <input type="hidden" name="status" id="status" value="">
            </form>
        </div>
    </div>

    <div class="modal fade" id="create_payout_account_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            
            </div>
        </div>
    </div>
    @include('backend.vendor.razorpay-api-account')

    <script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
    <!-- end product popup -->
    <script type="text/javascript">
        $(".all-product_check").click(function() {
            if ($(this).is(':checked')) {
                $("#action_product_button").css("display", "block");
                $('.single_product_check').prop('checked', true);
            } else {
                $("#action_product_button").css("display", "none");
                $('.single_product_check').prop('checked', false);
            }
        });

        $(document).on('change', '#action_for', function() {
            var actionfor = $('#action_for').val();
            $("#for_new").css("display", "none");
            $("#for_featured").css("display", "none");
            $("#for_last_mile").css("display", "none");
            $("#for_live").css("display", "none");
            $("#for_tax").css("display", "none");
            $("#for_sell_when_out_of_stock").css("display", "none");
            $("#"+ actionfor).css("display", "block");
        });

        $(document).on('change', '.single_product_check', function() {
            if ($('input:checkbox.single_product_check:checked').length > 0) {
                $("#action_product_button").css("display", "block");
            } else {
                $('.all-product_check').prop('checked', false);
                $("#action_product_button").css("display", "none");
            }
        });

        ////////   *******************  Save product action data ******************* ////////////////////////
        // $('#save_product_action_modal').on('submit', function(e) {
        //     e.preventDefault();
        //     var is_new = $('#is_new').val();
        //     var is_featured = $('#is_featured').val();
        //     var is_live = $('#is_live').val();
        //     var tax_category = $('#tax_category').val();
        //     var product_id = [];
        //      $('.single_product_check:checked').each(function(i){
        //         product_id[i] = $(this).val();
        //     });
        //     if (product_id.length == 0) {

        //         $("#action-product-modal .close").click();
        //         return;
        //     }
        //     console.log(product_id);
        //     return false;
        //     $.ajax({
        //         type: "POST",
        //         url: '{{route("product.update.action")}}',
        //         data: {_token: CSRF_TOKEN, is_new: is_new, is_featured: is_featured, is_live: is_live, tax_category: tax_category, product_id: product_id},
        //         success: function( msg ) {
        //             location.reload();
        //         }
        //     });
        // });

        $(document).on('click', '.submitProductAction', function(e) {
            var CSRF_TOKEN = $("input[name=_token]").val();
            var is_new = $('#is_new').prop('checked');
            var is_featured = $('#is_featured').prop('checked');
            var is_live = $('#is_live').val();
            var tax_category = $('#tax_category_for').val();
            var action_for = $('#action_for').val();
            var last_mile = $('#last_mile').prop('checked');
            var sell_when_out_of_stock = $('#sell_when_out_of_stock').prop('checked');
            var product_id = [];
             $('.single_product_check:checked').each(function(i){
                product_id[i] = $(this).val();
            });
            if (product_id.length == 0) {

                $("#action-product-modal .close").click();
                return;
            }
            if(action_for == 0){
                return false;
            }

            $.ajax({
                type: "post",
                url: '{{route("product.update.action")}}',
                data: {_token: CSRF_TOKEN,action_for:action_for,sell_when_out_of_stock:sell_when_out_of_stock,last_mile:last_mile, is_new: is_new, is_featured: is_featured, is_live: is_live, tax_category: tax_category, product_id: product_id},
                 success: function(resp) {
                    if (resp.status == 'success') {
                        $.NotificationApp.send("Success", resp.message, "top-right", "#5ba035",
                            "success");
                        location.reload();
                    }
                },
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                error: function(response) {

                        $(".show_all_error.invalid-feedback").show();
                        $(".show_all_error.invalid-feedback").text(
                            'Something went wrong, Please try Again.');

                    return response;
                }
            });
        });
        ////////  ****************** End save product action data *****************//////////////////////////

        $('#action_product_button').click(function() {
            $('#action-product-modal').modal({
                keyboard: false
            });
        });

        $('.addProductBtn').click(function() {
            $.ajax({
                type: "get",
                url: "{{route('vendor.specific_categories',$vendor->id)}}",
                success: function(response) {
                    if(response.status == 1){
                        $("#category_list").find('option').remove();
                        $("#category_list").append(response.options);
                        $('#category_list').selectize()[0].selectize.destroy();
                    }
                },
                error:function(error){

                }
            });
            $('#add-product').modal({
                keyboard: false
            });
        });
        $('.importProductBtn').click(function() {
            $('#import-product').modal({
                keyboard: false
            });
        });

        $("#csv_button").click(function() {
            $("#import_woocommerce").show();
            $("#import_csv").hide();
        });

        $("#import_woocommerce").hide();
        $("#woocommerce_button").click(function() {
            $("#import_csv").show();
            $("#import_woocommerce").hide();
        });

        var regexp = /^[a-zA-Z0-9-_]+$/;

        function setSkuFromName() {
            var n1 = $('#product_name').val();
            var sku_start = "{{ $sku_url }}" + ".";
            var total_sku = sku_start + n1;
            $('#sku').val(sku_start + n1);

            if (regexp.test(n1)) {
                var n1 = $('#product_name').val();
                $('#url_slug').val(n1);
                slugify();
            } else {
                $('#sku').val(total_sku.split(' ').join(''));
            }

            alplaNumeric();

        }

        function alplaNumeric() {
            var n1 = $('#sku').val();
            if (regexp.test(n1)) {
                var n1 = $('#sku').val();
                $('#url_slug').val(n1);
                slugify();
            } else {
                $('#sku').val(n1.split(' ').join(''));
            }
            // var charCode = String.fromCharCode(event.which || event.keyCode);
            // if (!regexp.test(charCode)) {
            //     return false;
            // }
            // var n1 = $('#sku').val();
            // $('#url_slug').val(n1+charCode)

            // return true;
        }

        function slugify() {
            //   var charCode = String.fromCharCode(event.which || event.keyCode);
            //   if (!regexp.test(charCode)) {
            //     return false;
            //   }
            var string = $('#url_slug').val();
            var slug = string.toString().trim().toLowerCase().replace(/\s+/g, "-").replace(/[^\w\-]+/g, "").replace(
                /\-\-+/g, "-").replace(/^-+/, "").replace(/-+$/, "");
            $('#url_slug').val(slug);
        }
        $(document).on('click', '#save_woocommerce_btn', function(e) {
            var that = $(this);
            $('.text-danger').html('');
            that.attr('disabled', true);
            $('#import_product_from_woocomerce').attr('disabled', true);
            var form = document.getElementById('woocommerces_form');
            var formData = new FormData(form);
            $.ajax({
                type: "post",
                url: "{{ route('woocommerce.save') }}",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    that.attr('disabled', false);
                    that.removeClass('button--loading');
                    $('#import_product_from_woocomerce').attr('disabled', false);
                    if (response.status == 'success') {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035",
                            "success");
                    } else {
                        $.NotificationApp.send("Error", response.message, "top-right", "#FF0000",
                            "error");
                    }
                },
                error: function(error) {
                    that.attr('disabled', false);
                    that.removeClass('button--loading');
                    $('#import_product_from_woocomerce').attr('disabled', false);
                    var response = $.parseJSON(error.responseText);
                    let error_messages = response.errors;
                    $.each(error_messages, function(key, error_message) {
                        $('#' + key + '_error').html(error_message[0]).show();
                    });
                }
            });
        });
        $(document).on('click', '#import_product_from_woocomerce', function(e) {
            var that = $(this);
            $('#save_woocommerce_btn').attr('disabled', true);
            that.attr('disabled', true);
            var vendor_id = $(this).data('vendor');
            $.ajax({
                type: "POST",
                data: {
                    vendor_id: vendor_id
                },
                url: "{{ route('product.import.woocommerce') }}",
                dataType: 'json',
                success: function(response) {
                    that.attr('disabled', false);
                    that.removeClass('button--loading');
                    $('#save_woocommerce_btn').attr('disabled', false);
                    if (response.status == 'success') {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035",
                            "success");
                    } else {
                        $.NotificationApp.send("Error", response.message, "top-right", "#FF0000",
                            "error");
                    }
                }
            });
        });
        $(document).on('click', '.submitProduct', function(e) {
            var form = document.getElementById('save_product_form');
            var formData = new FormData(form);
            $.ajax({
                type: "post",
                url: "{{ route('product.validate') }}",
                data: formData,
                contentType: false,
                processData: false,
                success: function(resp) {
                    if (resp.status == 'success') {
                        $('#save_product_form').submit();
                    }
                },
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                error: function(response) {
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            if (key == 'category.0') {
                                $("#categoryInput input").addClass("is-invalid");
                                $("#categoryInput span.invalid-feedback").children("strong")
                                    .text('The category field is required.');
                                $("#categoryInput span.invalid-feedback").show();
                            } else {
                                $("#" + key + "Input input").addClass("is-invalid");
                                $("#" + key + "Input span.invalid-feedback").children("strong")
                                    .text(errors[key][0]);
                                $("#" + key + "Input span.invalid-feedback").show();
                            }
                        });
                    } else {
                        $(".show_all_error.invalid-feedback").show();
                        $(".show_all_error.invalid-feedback").text(
                            'Something went wrong, Please try Again.');
                    }
                    return response;
                }
            });
        });

        $(document).delegate("#payout_form", "submit", function(e){
            e.preventDefault();
            var valid = true, message = '';
            var amount = parseFloat($('#payout_amount').val());
            var avl_funds = parseFloat($("#available_funds").val());
            if (amount == '') {
                message = "Please enter payout amount";
                valid = false;
            }
            else if (!$('input[name="payout_option"]').is(":checked")) {
                message = "Please select a valid payout option";
                valid = false;
            }
            else if(amount > avl_funds){
                message = "Payout amount is greater than available funds";
                valid = false;
            }
            if(!valid) {
                $("#payout_response .alert").html(message).show();
                setTimeout(function(){
                    $("#payout_response .alert").hide();
                },8000);
                return false;
            }
            var payout_opt = $('input[name="payout_option"]:checked').val();
            // if(payout_opt == 1){
                $("#new_vendor_payout_form #payout_option_id").val(payout_opt);
                $("#new_vendor_payout_form #transaction_id").val('');
                $("#new_vendor_payout_form #amount").val(amount);
                $("#new_vendor_payout_form #status").val(0);
                $("#new_vendor_payout_form").submit();
            // }else if(payout_opt == 2){
                // payoutViaStripe(amount, payout_opt)
            // }
        });

        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        initDataTable();

        // $("#range-datepicker").flatpickr({
        //     mode: "range",
        //     onClose: function(selectedDates, dateStr, instance) {
        //         initDataTable();
        //     }
        // });

        function initDataTable() {
            $('#vendor_payouts_datatable').DataTable({
                "dom": '<"toolbar">rtip', //'<"toolbar">Bfrtip',
                "destroy": true,
                "processing": true,
                "serverSide": true,
                "iDisplayLength": 50,
                // language: {
                //     search: "",
                //     paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                //     searchPlaceholder: "Search By Vendor Name"
                // },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                // buttons:[{
                //         className:'btn btn-success waves-effect waves-light',
                //         text: '<span class="btn-label"><i class="mdi mdi-export-variant"></i></span>Export CSV',
                //         action: function ( e, dt, node, config ) {
                //             window.location.href = "{{ route('account.vendor.export') }}";
                //         }
                // }],
                ajax: {
                  url: "{{route('vendor.payout.filter', $vendor->id)}}",
                //   data: function (d) {
                //     d.search = $('input[type="search"]').val();
                //     d.date_filter = $('#range-datepicker').val();
                //   }
                },
                columns: [
                    {data: 'date', name: 'date', orderable: true, searchable: false},
                    {data: 'amount', name: 'amount', orderable: false, searchable: false},
                    {data: 'type', name: 'type', orderable: false, searchable: false},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                ]
            });

        }

        $(document).on('click', '#pagarme_connect_btn', function(e) {
            var vendor_id = $(this).attr('data-vendor');
            var payout_option = $(this).attr('data-payout_option');
            $.ajax({
                type: "GET",
                data: {vendor: vendor_id, payout_option: payout_option},
                url: "{{ route('account.vendor.payout.createAccountDetails') }}",
                dataType: 'json',
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                success: function(response) {
                    if(response.status == 'Success'){
                        var res_html = response.data;
                        $("#create_payout_account_modal .modal-content").html(res_html);
                        $("#create_payout_account_modal").modal('show');
                    }
                },
                error: function(response){
                }
            });
        });

        $(document).on('click', '#pagarme_create_account', function(e) {
            $("#pagarme_account_form input").removeClass('is-invalid');
            $("#pagarme_account_form .invalid-feedback").html('');
            $.ajax({
                type: "POST",
                data: $("#pagarme_account_form").serializeArray(),
                url: "{{ route('vendor.payout.account.create.pagarme') }}",
                beforeSend: function() {
                    $(".loader_box").show();
                },
                complete: function() {
                    $(".loader_box").hide();
                },
                success: function(response) {
                    if(response.status == 'Success'){
                        window.location.reload();
                    }else{
                        $.NotificationApp.send("Error", response.message, "top-right", "#ff0808", "error");
                    }
                },
                error: function(error){
                    var response = $.parseJSON(error.responseText);
                    let error_messages = response.errors;
                    $.each(error_messages, function(key, error_message) {
                        $('#'+key).addClass('is-invalid');
                        $('#'+key+'_err').html(error_message[0]).show();
                    });
                }
            });
        });

    </script>
    {{-- @include('backend.vendor.modals') --}}
@endsection
@section('script')
    @include('backend.vendor.pagescript')
    <script>
        $(document).on('click', '.copy_link', function() {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($('#pwd_spn').text()).select();
            document.execCommand("copy");
            $temp.remove();
            $("#show_copy_msg_on_click_copy").show();
            setTimeout(function() {
                $("#show_copy_msg_on_click_copy").hide();
            }, 1000);
        })
    </script>
@endsection
