@extends('layouts.store', ['title' => __('Refer and earn')  ])
@section('css')
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
</style>
@endsection
@section('content')

<style type="text/css">
    .productVariants .firstChild{
        min-width: 150px;
        text-align: left !important;
        border-radius: 0% !important;
        margin-right: 10px;
        cursor: default;
        border: none !important;
    }
    .product-right .color-variant li, .productVariants .otherChild{
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 10px;
        cursor: pointer;
        border: 1px solid #f7f7f7;
        text-align: center;
    }
    .productVariants .otherSize{
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
    .invalid-feedback{
        display: block;
    }
    .outer-box{
        min-height: 280px;
    }
    #address-map-container #pick-address-map {
        width: 100%;
        height: 100%;
    }
    .address-input-group{
        position: relative;
    }
    .address-input-group .pac-container{
        top:35px!important;
        left:0!important;
    }
    .cursor-pointer{
        cursor: pointer;
    }
    input.referral_code {
        width: 140px;
        border: 1px solid #ccc;
        padding: 7px;
        cursor: not-allowed;
        background: #c1c1c157;
        color: #333;
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
                <div id="success-msg"></div>
            </div>
        </div>
        <div class="row my-md-3">
            <div class="col-lg-3">
                <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                <div class="dashboard-left mb-3">
                    <div class="collection-mobile-back">
                        <span class="filter-back d-lg-none d-inline-block">
                            <i class="fa fa-angle-left" aria-hidden="true"></i>{{ __('Back') }}
                        </span>
                    </div>
                    @include('layouts.store/profile-sidebar')
                </div>
            </div>
            <div class="col-lg-9">
                <div class="dashboard-right">
                    <div class="dashboard">
                        @if(empty($influencer_user))
                            <div class="page-title">
                                    <h2>{{ __('Choose the type of influencer you are') }}</h2>
                            </div>
                            <div class="box-account box-info order-address">
                            @if( !empty($influencer_category) && count($influencer_category) > 0) 
                                <div class="row">
                                    @foreach($influencer_category as $key => $val)
                                        <div class="col-md-4 mb-2">
                                            <a class="alert alert-dark cursor-pointer d-block text-center" role="alert" href="{{ route('refer-earn.form', $val->id) }}">
                                                {{$val->name ?? ''}}
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else 
                            <div class="text-center"><h3><strong>{{__('No Data Found')}}</strong></h3></div> 
                            @endif 
                            </div>
                        @else
                            @if($influencer_user->is_approved == 1) {{-- && $influencer_user->status == 1 --}}
                                <div class="row welcome-msg justify-content-between refer_code">
                                    <div class="col-md-6 mt-3">
                                        <h4 class="d-inline-block m-0 mb-3">
                                            <span>{{__('Your Referral Code:')}}</span> <input type="text" class="referral_code" name="referral_code" value="{{$influencer_user->reffered_code}}" readonly>
                                            <span id="copy_message" class="copy-message text-success" style="font-size: 14px;"></span>    
                                        </h4>
                                        <sup class="position-relative">
                                            <a class="copy-icon ml-1" id="copy_icon" title="Copy" style="cursor:pointer;"><i class="fa fa-copy"></i></a>
                                            <a class="edit-icon ml-1" id="edit_refferal_icon" title="Edit" data-code="{{$influencer_user->reffered_code}}" data-id="{{$influencer_user->id}}" style="cursor:pointer;"><i class="fa fa-edit"></i></a>
                                        </sup>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="total_amount_details">
                                            <div class="total-amount">
                                                <label>Earning Amount ({{Session::get('currencySymbol')}})</label>
                                                <h3>00.00</h3>
                                            </div>
                                            <div class="Earning-amount">
                                                <label>Discount per order ({{Session::get('currencySymbol')}})</label>
                                                <h3>{{$influencer_user->tier->commision}} ({{($influencer_user->tier->commision_type==1)?'% Percentage':(($influencer_user->tier->commision_type==2)?'Fixed':'')}})</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-1 profile-page">
                                    <div class="col-md-12">
                                        <div class="custom-table">
                                            <div class="group-item">
                                                <input type="text" placeholder="Search by order id">
                                            </div>
                                            <table class="table table-responsive">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Order ID</th>
                                                        <th scope="col">Customer Name</th>
                                                        <th scope="col">Product Name</th>
                                                        <th scope="col">Total Amount</th>
                                                        <th scope="col">User Discount</th>
                                                        <th scope="col">Earning Amount</th>
                                                        <th scope="col">Order Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(!empty($order_user_promo_product) && $order_user_promo_product->count() > 0)
                                                        @foreach($order_user_promo_product as $order)
                                                            <tr>
                                                                <td>{{$order->orderDetail->order_number ?? ''}}</td>
                                                                <td>{{$order->user->name ?? ''}}</td>
                                                                <td>Bruschetta</td>
                                                                <td>{{Session::get('currencySymbol')}} {{$order->subtotal_amount??0}}</td>
                                                                <td>{{Session::get('currencySymbol')}} {{$order->discount_amount??0}}</td>
                                                                <td>{{$influencer_user->tier->commision}}</td>
                                                                <td>{{$order->created_at??0}}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($influencer_user->is_approved == 2)
                                <div class="text-center"><h3><strong>Request Rejected By Admin</strong></h3></div>
                            @else
                                <div class="text-center"><h3><strong>Waiting For Admin Approval</strong></h3></div>
                            @endif
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<div class="modal fade" id="removeAddressConfirmation" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_addressLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title" id="remove_addressLabel">{{ __('Delete Address') }} </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <h6 class="m-0">
                {{ __('Do you really want to delete this address ?') }}
        </h6>
      </div>
      <div class="modal-footer flex-nowrap justify-content-center align-items-center">
        <button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="button" class="btn btn-solid" id="remove_address_confirm_btn" data-id="">{{ __('Delete') }}</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="updateRefferalCode" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_addressLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header border-bottom">
                <h5 class="modal-title" id="remove_addressLabel">{{ __('Update Refferal Code') }} </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger print-error-msg" style="display:none">
                        <ul></ul>
                    </div>
                    <input type="hidden" name="influencer_user_id" id="influencer_user_id" value="">
                    <div class="form-group">
                        <label for="refferal_code_edit">Refferal Code:</label>
                        <input type="text" class="form-control" name="refferal_code" id="refferal_code_edit" value="" placeholder="Enter Refferal Code" required>
                        @if ($errors->has('refferal_code'))
                            <span class="text-danger">{{ $errors->first('refferal_code') }}</span>
                        @endif
                    </div>
                </div>
                <div class="modal-footer text-center" style="display: block;">
                    <button type="button" class="btn btn-solid text-left" id="update_refferal_code_btn" data-id="">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
  </div>

@endsection
@section('script')
<script>
$(document).ready(function(){
    $('#edit_refferal_icon').on('click', function(){
        var code = $(this).data('code');
        var id = $(this).data('id');
        $('#updateRefferalCode #refferal_code_edit').val(code);
        $('#updateRefferalCode #influencer_user_id').val(id);
        $('#updateRefferalCode').modal();
    });

    $("#copy_icon").click(function(){
        var temp = $("<input>");
        var selector = $('.referral_code').val();
        $("body").append(temp);
        temp.val(selector).select();
        console.log(temp.val(selector).select());
        document.execCommand("copy");
        temp.remove();
        $("#copy_message").text("{{ __('Copied!') }}").show();
        setTimeout(function(){
            $("#copy_message").text('').hide();
        }, 3000);
    });

    $('#update_refferal_code_btn').on('click', function(){
        var influencer_user_id = $('#influencer_user_id').val();
        var refferal_code = $('#refferal_code_edit').val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url : "{{ route('refer-earn.updateRefferalCode') }}",
            data : {'influencer_user_id': influencer_user_id,'refferal_code': refferal_code},
            type : 'POST',
            dataType : 'json',
            success : function(result){
                if($.isEmptyObject(result.error)){
                    $('#success-msg').html('<div class="alert alert-success" role="alert">'+result.success+'</div>');
                    $('#updateRefferalCode').modal('hide');
                    $('.refer_code .referral_code').val(refferal_code);
                }else{
                    printErrorMsg(result.error);
                }
            }
        });
    });

    function printErrorMsg (msg) {
        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display','block');
        $.each( msg, function( key, value ) {
            $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
        });
    }
});
</script>
@endsection
