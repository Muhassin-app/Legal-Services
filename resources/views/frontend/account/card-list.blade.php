@extends('layouts.store', ['title' =>  __('Address Book')  ])
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
                        <div class="page-title">
                                <h2>{{ __('Saved Cards') }}</h2>
                        </div>
                        <div class="box-account box-info order-address">
                            <div class="row">
                                @foreach($cards as $add)
                                    <div class="col-xl-4 col-md-6 mt-2">
                                        <div class="outer-box  px-0">
                                            <div class="address-type w-100">
                                                <div class="default_address border-bottom mb-1 px-2">
                                                    <h6 class="mt-0 mb-2"><i class="fa fa-credit-card mr-1" aria-hidden="true"></i> </h6>
                                                </div>
                                                <div class="px-2">
                                                    <p class="mb-1">{{$add->card_hint }}</p>
                                                    <p class="mb-1">{{$add->expiration}}</p>
                                                    <p class="mb-1">{{$add->brand}}</p>
                                                </div>
                                            </div>
                                            <div class="address-btn d-flex align-items-center justify-content-end w-100 mt-sm-4 px-2">
                                                @if($add->is_default == 1)
                                                    <a class="btn btn-solid disabled" href="#">{{ __('Default') }}</a>
                                                @else
                                                    <a class="btn btn-solid" href="{{ route('setDefaultCard', $add->id) }}" class="mr-2">{{ __('Set As Default') }}</a>                                              
                                                @endif
                                                <a class="btn btn-solid delete_card_btn" href="javascript:void(0)" data-toggle="modal" data-target="#removeCardConfirmation" data-id="{{$add->id}}">{{ __('Delete') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" id="removeCardConfirmation" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_addressLabel">
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
        <button type="button" class="btn btn-solid" id="remove_card_confirm_btn" data-id="">{{ __('Delete') }}</button>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
    var delete_address_url = "{{ route('delete.azul.card', ':id') }}";
   

    var ajaxCall = 'ToCancelPrevReq';
    $(document).delegate(".delete_card_btn", "click", function(){
        var addressID = $(this).attr("data-id");
        $("#remove_card_confirm_btn").attr("data-id", addressID);
    });
    $(document).delegate("#remove_card_confirm_btn", "click", function(){
        var addressID = $(this).attr("data-id");
        var url = delete_address_url.replace(':id', addressID);
        location.href = url;
    });
</script>
@endsection