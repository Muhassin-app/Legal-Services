@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Report'])

@section('css')
<link href="{{asset('assets/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<!-- Start Content-->
<div class="container-fluid" id="ProductPerformanceReport">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            @php
                $productmenu = getNomenclatureName('Products', true);
                $productmenulabel = ($productmenu=="Products")?__('Products'):__($productmenu);

            @endphp
            <div class="page-title-box">
                <h4 class="page-title">{{ __($productmenulabel. " Performance Report") }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 mb-2">
            <div class="row align-items-center ">
                <div class="col-sm-2 mb-1">
                </div>
                <div class="col-sm-2 mb-1">
                    <select class="form-control" id="limit_select_box">
                        <option value="10">{{ __("Top") }} 10</option>
                        <option value="20">{{ __("Top") }} 20</option>
                        <option value="50">{{ __("Top") }} 50</option>
                        <option value="100">{{ __("Top") }} 100</option>
                        <option value="All">{{ __("All") }}</option>
                    </select>
                </div>
                <div class="col-sm-2 mb-1">
                    <input type="text" id="range-datepicker" class="form-control flatpickr-input" placeholder="{{ __('Select Date Range') }}" readonly="readonly">
                </div>
                <div class="col-sm-4  mb-1">
                    <select class="form-control" multiple="multiple" id="product_select_box" name="product_select_box[]">
                        <option value="">{{ __('Select Product') }}</option>

                    </select>
                </div>
                <div class="col-sm-2  mb-1">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-danger waves-effect waves-light mr-3" id="clear_filter_btn_icon">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body ">
                    <div id="productnotfound" style="display:none;">
                        <div class="error-msg mt-3">
                            <img class="mb-2" src="{{asset('images/no-order.svg')}}">
                            <p>{{ __("Product not found.") }}</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-12 col-lg-12 p-0 tab-product pt-0">
                                <ul class="nav nav-tabs nav-material" id="top-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="top_performing_products_tab" data-toggle="tab" href="#top_performing_products" role="tab" aria-selected="false" data-rel="top_performing_products">
                                            <i class="icofont icofont-man-in-glasses"></i>{{ __('Top Performing '.$productmenulabel) }} <sup class="total-items" id="totalitem_sup1"></sup>
                                        </a>
                                        <div class="material-border"></div>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="most_wishlist_products_tab" data-toggle="tab" href="#most_wishlist_products" role="tab" aria-selected="true" data-rel="most_wishlist_products">
                                            <i class="icofont icofont-ui-home"></i>{{ __('Most Wishlist '.$productmenulabel) }} <sup class="total-items" id="totalitem_sup2"></sup>
                                        </a>
                                        <div class="material-border"></div>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="most_refunded_products_tab" data-toggle="tab" href="#most_refunded_products" role="tab" aria-selected="false" data-rel="most_refunded_products">
                                            <i class="icofont icofont-man-in-glasses"></i>{{ __('Most Refunded '.$productmenulabel) }} <sup class="total-items" id="totalitem_sup3"></sup>
                                        </a>
                                        <div class="material-border"></div>
                                    </li>
                                </ul>
                                <div class="tab-content nav-material  order_data_box scroll-style" id="top-tabContent">
                                    <div class="tab-pane fade past-order show active position-relative h-100" id="top_performing_products" role="tabpanel" aria-labelledby="top_performing_products_tab">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="table-responsive mt-2">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th width="10%">{{ __("S.No") }}</th>
                                                            <th width="30%">{{ __("Product") }}</th>
                                                            <th width="30%">{{ __("Vendor") }}</th>
                                                            <th width="30%">{{ __("No. Of ".$productmenulabel) }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody id="tbody_tab1">
                                                        </tbody>

                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-sm-6" id="chartparent1">
                                                <div id="graph_tab1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade position-relative h-100" id="most_wishlist_products" role="tabpanel" aria-labelledby="most_wishlist_products_tab">
                                    <div class="row">
                                            <div class="col-sm-6">
                                                <div class="table-responsive mt-2">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th width="10%">{{ __("S.No") }}</th>
                                                            <th width="30%">{{ __("Product") }}</th>
                                                            <th width="30%">{{ __("Vendor") }}</th>
                                                            <th width="30%">{{ __("No. Of Wishlist") }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody id="tbody_tab2">
                                                        </tbody>

                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-sm-6" id="chartparent2">
                                                <div id="graph_tab2"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade past-order position-relative h-100" id="most_refunded_products" role="tabpanel" aria-labelledby="most_refunded_products_tab">
                                    <div class="row">
                                            <div class="col-sm-6">
                                                <div class="table-responsive mt-2">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th width="10%">{{ __("S.No") }}</th>
                                                            <th width="30%">{{ __("Product") }}</th>
                                                            <th width="30%">{{ __("Vendor") }}</th>
                                                            <th width="30%">{{ __("No. Of Returns") }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody id="tbody_tab3">
                                                        </tbody>

                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-sm-6" id="chartparent3">
                                                <div id="graph_tab3"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
</div>
@endsection
@section('script')
<script src="{{asset('assets/libs/select2/select2.min.js')}}"></script>
<script src="{{asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script type="text/javascript">
$(document).ready(function() {

    $('#top_performing_products_tab, #most_wishlist_products_tab, #most_refunded_products_tab').click(function(){
        setTimeout(function() { loadproductperformancereport(); }, 300);
    });
    $("#product_select_box").select2({
        placeholder: "{{ __('Select Products')}}",
        allowClear:true,
        ajax: {
            url: "{{ route('report.searchproduct') }}",
            type: "post",
            delay: 0,
            dataType: 'json',
            data: function(params) {
                return {
                    query: params.term,
                    "_token": "{{ csrf_token() }}",
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
    loadproductperformancereport();
});
    var ajaxCall = 'ToCancelPrevReq';
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
    $("#range-datepicker").flatpickr({
        mode: "range",
        onClose: function(selectedDates, dateStr, instance) {
            loadproductperformancereport();
        }
    });

    $("#product_select_box, #limit_select_box").change(function() {
        loadproductperformancereport();
    });
    $("#clear_filter_btn_icon").click(function() {
        $('#range-datepicker').val('');
        $('#product_select_box').val([]).trigger('change');
        $("#limit_select_box").val(10);
        loadproductperformancereport();
    });

    function autoloaddashboad(){
        var type =  $("a.nav-link.active").data('rel');
        loadproductperformancereport();
    }


    function loadproductperformancereport() {
        var url = "{{ route('report.loadproductreport') }}";
        if($('#top_performing_products').hasClass('active'))
        {
            var tableid = "tbody_tab1";
            var chartdivid = "graph_tab1";
            var chartparent = "chartparent1";
            var tabid = 1;
        }
        if($('#most_wishlist_products').hasClass('active'))
        {
            var tableid = "tbody_tab2";
            var chartdivid = "graph_tab2";
            var chartparent = "chartparent2";
            var tabid = 2;
        }
        if($('#most_refunded_products').hasClass('active'))
        {
            var tableid = "tbody_tab3";
            var chartdivid = "graph_tab3";
            var chartparent = "chartparent3";
            var tabid = 3;
        }

        var product_select_box = $("#product_select_box").val();
        var date_filter = $('#range-datepicker').val();
        var limit_filter = $('#limit_select_box').val();
        $('#'+tableid).empty('');
        $('#'+chartparent).empty('');
        $('#'+tableid).append('<tr><td colspan="4" style="height: 50px;"><div class="spinner-border text-blue m-2" role="status"></div></td></tr>');

        $.ajax({
            type: "POST",
            dataType: "json",
            url: url,
            data: {
                product_select_box: product_select_box,
                date_filter: date_filter,
                limit_filter: limit_filter,
                tabid:tabid
            },
            success: function(res) {
                if(res.success)
                {
                    if(res.productdata!='')
                    {
                        $('#'+tableid).empty('');
                        var i = 0;var series = [];var labels = [];
                        $.each(res.productdata, function (index, value) {i++;
                            if(value.title == null)
                            {
                                var product_name = value.sku;
                            }else{
                                if(typeof value.translation.title =="undefined")
                                {
                                    var product_name = value.title ;
                                }else{
                                    var product_name = value.translation[0].title;
                                }
                            }
                            if(tabid == 1)
                            {
                                var order_count = value.order_product_count;
                            }
                            if(tabid == 2)
                            {
                                var order_count = value.user_wishlist_count;
                            }
                            if(tabid == 3)
                            {
                                var order_count = value.order_return_request_count;
                            }
                            series.push(order_count);
                            labels.push(product_name);
                            $('#'+tableid).append('<tr><td>'+i+'</td><td><a href="{{url("/client/product")}}/'+value.id+'/edit" target="_blank">'+product_name+'</a></td><td><a href="{{url("/client/vendor/catalogs")}}/'+value.vendor.id+'" target="_blank">'+value.vendor.name+'</a></td><td><span class="badge bg-success" style="color:#fff;font-size:14px;">'+order_count+'</span></td></tr>');
                        });
                        $('#'+chartparent).html('<div id="'+chartdivid+'" ></div>');
                        var xhartdivwidth = $('#'+chartparent).width();
                        if(limit_filter != 10)
                        {
                            var height = parseInt(xhartdivwidth) - 100 + parseInt(res.procount)*3;
                        }else{
                            var height = parseInt(xhartdivwidth) - 100;
                        }
                        $("#totalitem_sup"+tabid).text(parseInt(res.procount));
                        var options = {
                            series: series,
                            responsive:true,
                            chart: {
                            height:height,
                            type: 'pie',
                            },
                            labels: labels,
                            legend:{show:!0,position:"bottom",horizontalAlign:"center",verticalAlign:"middle",floating:!1,fontSize:"14px",offsetX:0,offsetY:7},
                            responsive: [{
                            breakpoint: parseInt(xhartdivwidth) - 100,
                            options: {
                                chart: {
                                height:height
                                },
                                legend: {
                                    show:!1,
                                }
                            }
                            }]
                            };

                        var chart = new ApexCharts(document.querySelector("#"+chartdivid), options);
                        chart.render();

                    }
                    else{
                        $('#'+tableid).empty('');
                        $('#'+tableid).append('<tr><td colspan="4" style="height: 50px;">'+$("#productnotfound").html()+'</div></td></tr>');
                    }
                }
            }
        });
    }
</script>


@endsection
