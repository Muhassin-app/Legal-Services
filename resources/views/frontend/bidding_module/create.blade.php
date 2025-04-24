@extends('layouts.store', ['title' => 'Create Requests'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }

</style>
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
@endsection
@section('content')

<style type="text/css">
    .productVariants .firstChild {
        min-width: 150px;
        text-align: left !important;
        border-radius: 0% !important;
        margin-right: 10px;
        cursor: default;
        border: none !important;
    }

    .product-right .color-variant li,
    .productVariants .otherChild {
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 10px;
        cursor: pointer;
        border: 1px solid #f7f7f7;
        text-align: center;
    }

    .productVariants .otherSize {
        height: auto !important;
        width: auto !important;
        border: none !important;
        border-radius: 0%;
    }

    .product-right .size-box ul li.active {
        background-color: inherit;
    }

    .login-page .theme-card .theme-form input {
        margin-bottom: 5px;
    }

    .invalid-feedback {
        display: block;
    }
    .place-bid input {
    border: 1px solid rgba(112, 107, 107, 0.63);
    border-radius: 10px;
}
.place-bid .close{position: absolute;top:0px;right: 20px;}
</style>

<section class="section-b-space">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="text-sm-left">
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <span>{!! \Session::get('success') !!}</span>
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
        </div>
        <div class="row my-md-3 mt-5 pt-4">
            <div class="col-lg-2">
                <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                @include('layouts.store/profile-sidebar')
            </div>
            <div class="col-lg-10">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2>{{ __('Bid Request') }}</h2>
                        </div>
                        <div class="card-box">
                            <h5 class="mb-3">{{ __('Prescriptions') }}</h4>
                            <div class="row align-items-center">
                                <div class="table-responsive">
                                    <table class="table table-centered table-nowrap table-striped" id="client_customer_table" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ __('ID') }}</th>
                                                <th>{{ __('Prescription') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ( $prescriptions as $prescription )
                                            <tr>
                                                <td>{{$loop->iteration}}</td>
                                                <td><img src="{{$prescription->prescription}}" width="50" height="50"></td>
                                                <td>{{$prescription->created_at}}</td>
                                                <td>
                                                    <a href="{{$prescription->prescription}}" target="_blank"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>

                                                    <a href="javascript:void(0)" class="biddingBitton" data-prescription="{{$prescription->id}}"  ><i class="fa fa-gavel" aria-hidden="true"></i></a>
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
</section>
<div class="modal fade biddingModel"  tabindex="-1" aria-labelledby="profile-modalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="row align-items-center px-2">
                <div class="col-4">
                    <div class="modal-header">
                        <h5 class="modal-title d-block" id="profile-modalLabel">{{ __('Place Bid') }}</h5>

                    </div>
                </div>
                <div class="col-md-8">
                    <div class="vendor-search place-bid mb-sm-0 mb-2">
                        <div class="col d-inline-flex align-items-center justify-content-start p-0 position-relative">
                                @php $searchPlaceholder=getNomenclatureName('Search', true); $searchPlaceholder=($searchPlaceholder==='Search product, vendor, item') ? __('Search product, vendor, item') : $searchPlaceholder; @endphp
                            <input class="form-control typeahead" type="search" placeholder="{{$searchPlaceholder}}" id="search_box" autocomplete="off">
                            <div class="list-box style-4" style="display:none;" id="search_box_div"> </div>
                        </div>

                    </div>
                </div>

            </div>
            <form id="placeBidForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body d-none" id="addProductBox">
                    <table class="table">
                        <thead>
                            <td>ID</td>
                            <td>Name</td>
                            <td>Varient</td>
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
                    <button type="submit" class="btn btn-solid w-100">{{ __('Place Bid') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="{{asset('assets/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/libs/dropify/dropify.min.js')}}"></script>
<script src="{{asset('assets/js/pages/form-fileuploads.init.js')}}"></script>
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script type="text/javascript">


    $("#timezone").change(function(){
        $("#user_timezone_form").submit();
    });
    $("#copy_icon").click(function(){
        var temp = $("<input>");
        var url = $(this).data('url');
        $("body").append(temp);
        temp.val(url).select();
        document.execCommand("copy");
        temp.remove();
        $("#copy_message").text("{{ __('URL Copied!') }}").show();
        setTimeout(function(){
            $("#copy_message").text('').hide();
        }, 3000);
    });
</script>
<script>
    var loadFile = function(event) {
        var output = document.getElementById('output');
        output.src = URL.createObjectURL(event.target.files[0]);
    };
</script>
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
        $("#productTable").append('<tr><td class="id">'+pid+'</td><td class="name">'+name+'</td><td><select class="form-control" name="varients" id="varients"><option value="varient_id">varientOne</option></select></td><td class="price">'+price+'</td><td class="qty"><input type="number" class="form-control quantity" value="'+quantity+'" min="1" max="10"></td><td><a href="javascript:void(0)" class="removeProduct"><i class="fa fa-trash"></i></a></td></tr>');
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
        };
        }).get();

        var form = JSON.stringify(formData);

        var actionUrl = "{{route('bid.store')}}";

        $.ajax({
            type: "POST",
            url: actionUrl,
            data:{'data':form},
            dataType: "json",
            success: function(data)
            {
                window.location.href = "{{ url('/') }}";
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
                url: 'product-search',
                data: { keyword: keyword },
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
                            $("#search_box_div").append(search_box_category_template({ results: response.data })).show();
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
            <a class="col-12 text-center list-items pt-2 product_list" data-pid="<%=result.id %>" data-price="<%=result.price%>">
                <img class="blur-up lazyload" data-src="<%=result.image_url%>" alt="">
                <div class="result-item-name">
                    <b><%=result.name %></b>
                </div>
            </a>
            <%}); %>
        <%}); %>
    </div>
</script>
@endsection
