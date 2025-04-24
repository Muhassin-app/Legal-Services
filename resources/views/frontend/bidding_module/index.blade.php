@extends('layouts.store', ['title' => 'Bid Requests'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
    .modal-body div#loader {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
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
    .showBidsModel .modal-content {
        min-height: 400px;
    }
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
            <div class="col-lg-1">
                <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                {{-- @include('layouts.store/profile-sidebar') --}}
            </div>
            <div class="col-lg-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        <div class="page-title">
                            <h2>{{ __('Place a Bid Request') }}</h2>
                        </div>
                        <div class="card-box">
                            <div class="row">
                                <div class="col-6 align-items-center border-right">
                                    <div class="page-title">
                                        <h5 class="mb-3">{{ __('Upload Prescription To Place a Bid') }}</h5>
                                    </div>
                                    <form class="" action="{{ route('bid.uploadPrescription') }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="card ">
                                            <div class="dropify-wrapper report-upload-subt w-100">
                                                <div class="dropify-loader"></div>
                                                <div class="dropify-errors-container">
                                                    <ul></ul>
                                                </div>
                                                <input required type="file" accept="image/*,.pdf,.doc" data-plugins="dropify" name="prescription" class="dropify" data-default-file="">
                                                <button type="button" class="dropify-clear">Remove</button>
                                                <div class="dropify-preview">
                                                    <span class="dropify-render"></span>
                                                    <div class="dropify-infos">
                                                        <div class="dropify-infos-inner">
                                                            <p class="dropify-filename">
                                                                <span class="file-icon"></span>
                                                                <span class="dropify-filename-inner"></span>
                                                            </p>
                                                            <p class="dropify-infos-message">Drag and drop or click to
                                                                replace</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" id="getBedRequests"class="w-50 mt-3 btn btn-info waves-effect waves-light mt-2">Submit</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-6">
                                    <div class="col-md-12">
                                        <div class="page-title">
                                            <h4>{{ __('Prescriptions') }}</h4>
                                        </div>
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
                                                    @foreach ($prescriptions as $prescription)
                                                    <tr>
                                                        <td>{{$loop->iteration}}</td>
                                                        <td><img src="{{$prescription->prescription}}" width="50" height="50"></td>
                                                        <td>{{$prescription->created_at}}</td>
                                                        <td>
                                                            <a href="{{$prescription->prescription}}" target="_blank"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
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

    @if(Request::get('success') == 'done')
    <div class="modal fade showBidsModel"  tabindex="-1" aria-labelledby="profile-modalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="profile-modalLabel">{{ __('Requests From Vendors') }}</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- <div class="" id='loader'>
                        <img src="{{asset('assets/images/loading_new.gif')}}" alt=""  width="40px" class="pr-2">
                        <p class="mb-0">we are looking for bids...</p>
                    </div> --}}

                    @foreach ($bids as $bid)
                        <div id="accordion">
                            <div class="card">
                            <div class="card-header headingOne_{{ $bid->id }}" id="headingOne">
                                <h5 class="m-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                {{$bid->vendor->name}}
                                </button>
                                </h5>
                            </div>
                            <div class="" id="VendorProductBox">
                                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                                    <div class="card-body">
                                        <form id="placeBidForm_{{ $bid->id }}" action="{{route('bidding-cart',$bid->id)}}" enctype="multipart/form-data">
                                            @csrf
                                                <table class="table">
                                                    <thead>
                                                        <td>S.No</td>
                                                        <td>name</td>
                                                        <td>Price</td>
                                                        <td>Quantity</td>
                                                    </thead>
                                                    <tbody id="productTable">
                                                        @foreach ($bid->bidProducts as $product )

                                                            <tr>
                                                                <td>{{$loop->iteration}}</td>
                                                                <td>{{$product->product->title}}</td>
                                                                <td>{{$product->price}}</td>
                                                                <td>{{$product->quantity}}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <th><span>Total : {{$bid->bid_total}}</span></th>
                                                        <th><span>Discont % : <br> {{$bid->discount}}</span></th>
                                                        <th><span>Final Total : {{$bid->final_amount}}</span></th>
                                                        <th>
                                                            <a href="#"><button type="submit" class="btn btn-success">Accept / Add to Cart</button></a>
                                                        </th>
                                                        <th>
                                                            <button class="btn btn-danger removeBid" data-bid="{{ $bid->id}}">Reject</button>
                                                        </th>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
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
    <?php
        if(Request::get('success') == 'done'){
    ?>
    $(document).ready(function() {
            $(".showBidsModel").modal({
            backdrop: 'static',
            keyboard: false
            });
        });
    <?php
    }
    ?>
    $(document).delegate('.showBidsModel .close', 'click', function() {
        window.location.href = "{{ url('/index') }}";
    });

    $(document).on("click", ".removeBid", function (e) {
        var bid = $(this).attr('data-bid');
        $("#placeBidForm_"+bid).remove();
        $(".headingOne_"+bid).remove();
    });
</script>


@endsection
