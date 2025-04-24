@extends('layouts.vertical', ['demo' => 'Bid list', 'title' => 'Bid Requests - Vendors'])
@section('css')
{{-- <link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> --}}
<style>
    .list-box.style-4{max-height:200px;overflow: auto;}
    .product_list{cursor: pointer;}
    .product_list:hover{background-color:#f5f5f5;}
    .product_list:nth-child(odd){
        border-width:1px 0px 1px 0px;
        border-color: #eee;
        border-style: solid;
    }
</style>
@endsection
@section('content')
<div class="content">

        <div class="row">
            <div class="col-sm-12">
                <div class="text-sm-left" >
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <span>{!! \Session::get('success') !!}</span>
                        </div>
                        @php
                            \Session::forget('success');
                        @endphp
                    @endif
                    @if (\Session::has('error'))
                        <div class="alert alert-danger">
                            <span>{!! \Session::get('error') !!}</span>
                        </div>
                        @php
                            \Session::forget('error');
                        @endphp
                    @endif
                    <div class="message d-none">
                        <div class="alert p-0"></div>
                    </div>
                </div>
            </div>
        </div>


    <div class="container-fluid alPayoutRequestsPage">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">{{ __("Bid Requests") }}</h4>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-sm-12 col-lg-12 tab-product pt-0">

                <div class="tab-content nav-material pt-0" id="top-tabContent">
                    <div class="tab-pane fade past-order show active" id="pending_payouts" role="tabpanel" aria-labelledby="pending-payouts">
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">

                                             <table class="table table-centered table-nowrap table-striped" id="client_customer_table" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ __('ID') }}</th>
                                                <th>{{ __('File') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ( $prescriptions as $prescription )
                                            <tr>
                                                <td>{{$loop->iteration}}</td>
                                                <td> <a href="{{$prescription->prescription}}" target="_blank"><i class="fa fa-file-pdf" style="font-size:24px;"></i></a></td>
                                                <td><span class="text-wrap">{{$prescription->description}}</span></td>
                                                <td>{{$prescription->created_at}}</td>
                                                <td>
                                                   {{-- <a href="{{$prescription->prescription}}" target="_blank"><i class="fa fa-eye-slash" aria-hidden="true"></i></a> --}}
                                                   @if($prescription->bid_count>0)
                                                   <a href="javascript:void(0)"><i class="fa fa-check" aria-hidden="true"></i></a>
                                                   @else
                                                   <a href="javascript:void(0)" class="biddingBitton" data-prescription="{{$prescription->id}}"  ><i class="fa fa-gavel" aria-hidden="true"></i></a>
                                                   @endif
                                                </td>
                                            </tr>
                                            @endforeach
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
</div>


<div class="modal fade biddingModel"  tabindex="-1" aria-labelledby="profile-modalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-block" id="profile-modalLabel">{{ __('Place Bid') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="vendor-search place-bid mb-sm-0 mb-2">
                    <div class="p-0 position-relative">
                            @php $searchPlaceholder=getNomenclatureName('Search', true); $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ? __('Search product, vendor, item') : $searchPlaceholder; @endphp
                        <input class="form-control typeahead" type="search" placeholder="{{$searchPlaceholder}}" id="search_box" autocomplete="off">
                        <div class="list-box style-4 mt-2" style="display:none"  id="search_box_div"></div>
                    </div>
                </div>

            </div>
            <form id="placeBidForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" value="{{$id}}" id="vendor_id" name="vendor_id"/>
                <div class="modal-body d-none" id="addProductBox">
                    <table class="table">
                        <thead>
                            <td>ID</td>
                            <td>Name</td>
                            {{-- <td>Varient</td> --}}
                            <td>Price</td>
                            <td>Quantity</td>
                            <td>Action</td>
                        </thead>
                        <tbody id="productTable">

                        </tbody>
                    </table>
                    <div class="form-group mt-2 d-none" id="discount-section">
                        <label for="discount">Discount %</label>
                        <input type="text"  onkeypress="return event.charCode >= 48 && event.charCode <= 57" class="form-control" name="discount" id="discount" required >
                        <input type="hidden" id="prescription_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-solid">{{ __('Place Bid') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@section('script')
<script>
    $(document).ready(function() {
        $("#phone").keypress(function(e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            return true;
        });
    });
    $(document).delegate('.iti__country', 'click', function() {
        var code = $(this).attr('data-country-code');
        $('#countryData').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#dialCode').val(dial_code);
    });

    $(document).on("click", ".product_list", function (e) {
        var pid = $(this).attr('data-pid');
        var name = $(this).find('.result-item-name b').text();
        var price = $(this).attr('data-price');
        var quantity = 1;
        $("#addProductBox").removeClass('d-none').addClass('d-block');
        $("#discount-section").removeClass('d-none').addClass('d-block');
        $("#productTable").append('<tr><td class="id">'+pid+'</td><td class="name">'+name+'</td><td class="price">'+price+'</td><td class="qty"><input type="number" class="form-control quantity" value="'+quantity+'" min="1" max="10"></td><td><a href="javascript:void(0)" class="removeProduct"><i class="fa fa-trash"></i></a></td></tr>');
    });

    $(document).on("click", ".removeProduct", function (e) {
        $(this).parent().parent().remove();
    });

    $("#placeBidForm").submit(function(e) {
        e.preventDefault();
        var formData = $("#productTable tr").map(function() {
        var $this = $(this);
        return {
            id: $this.find(".id").text(),
            name: $this.find(".name").text(),
            price: $this.find(".price").text(),
            qty:$this.find(".qty input").val(),
            discount:$("#discount").val(),
            prescription_id:$("#prescription_id").val(),
            vendor_id:$("#vendor_id").val(),
        };
        }).get();

        var form = JSON.stringify(formData);

        var actionUrl = "{{route('vendor.bid.store')}}";

        $.ajax({
            type: "POST",
            url: actionUrl,
            data:{'data':form},
            dataType: "json",
            success: function(data)
            {
               window.location.reload();
            }
        });
    });

    $("#search_box").blur(function (e) {
        setTimeout(function () {
            $('#search_box_div').html('').hide();
        },
            500);
    });

    $('input[type=search]').on('search', function () {
        $('#search_box_div').html('').hide();
    });

    $("#search_box").focus(function () {
        let keyword = $(this).val();
        searchResults(keyword);
    });
    $("#search_box").keyup(function () {
        let keyword = $(this).val();
        searchResults(keyword);
    });
    var searchAjaxCall = 'ToCancelPrevReq';

    function searchResults(keyword) {
        if (keyword.length <= 2) {
            $('#search_box_div').html('').hide();
        }
        if (keyword.length >= 2) {
            searchAjaxCall = $.ajax({
                type: "GET",
                dataType: 'json',
                url: "{{route('searchProduct')}}",
                data: { keyword: keyword , vendor_id:$("#vendor_id").val()},
                beforeSend: function () {
                    if (searchAjaxCall != 'ToCancelPrevReq' && searchAjaxCall.readyState < 4) {
                        searchAjaxCall.abort();
                    }
                },
                success: function (response) {
                    if (response.status == 'Success') {
                        $('#search_box_main_div').html('');
                        if (response.data.length != 0) {
                            let search_box_category_template = _.template($('#search_box_div_template').html());
                            $("#search_box_div").html(search_box_category_template({ results: response.data })).show();
                        } else {
                            $("#search_box_div").html('<p class="text-center my-3">No result found. Please try a new search</p>').show();
                        }
                    }
                }
            });
        }
    }

    $('.biddingBitton').click(function(){
        var prescription_id = $(this).attr('data-prescription');

        $('.biddingModel').modal('show');
        $('.biddingModel').find("#prescription_id").val(prescription_id);
    });
</script>

<script type="text/template" id="search_box_div_template">
    <div class="row mx-0">
        <% _.each(results, function(data, k){%>
            <% if(data.title !=''){ %>

                <div class="result-item-name product_heading">
                    <h4><%=data.title %></h4>
                </div>

            <%} %>
            <% _.each(data.result, function(result, k){%>
            <a class="col-12 list-items text-left py-1 product_list" data-pid="<%=result.id %>" data-price="<%=result.price%>">
                <!-- <img class="blur-up lazyload" data-src="<%=result.image_url%>" alt=""> -->
                <div class="result-item-name">
                    <b><%=result.name %></b>
                </div>
            </a>
            <%}); %>
        <%}); %>
    </div>
</script>

@endsection