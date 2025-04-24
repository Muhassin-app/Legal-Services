@extends('layouts.store', ['title' => __('My-Ads')])
@section('css-links')
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<link href="{{asset('assets/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('css')
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
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
    .iti__flag-container li, .flag-container li{
        display: block;
    }
    .iti.iti--allow-dropdown, .allow-dropdown {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    .iti.iti--allow-dropdown .phone, .flag-container .phone {
        padding: 17px 0 17px 100px !important;
    }
    .social-logins{
        text-align: center;
    }
    .social-logins img{
        width: 100px;
        height: 100px;
        border-radius: 100%;
        margin-right: 20px;
    }
    .register-page .theme-card .theme-form input {
        margin-bottom: 5px;
    }
    .invalid-feedback{
        display: block;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@endsection
@section('content')
@php
$clientData = \App\Models\Client::where('id', '>', 0)->first();
$urlImg = $clientData ? $clientData->logo['original'] : ' ';
$pages = \App\Models\Page::with(['translations' => function($q) {$q->where('language_id', session()->get('customerLanguage') ??1);}])->whereHas('translations', function($q) {$q->where(['is_published' => 1, 'language_id' => session()->get('customerLanguage') ??1]);})->orderBy('order_by','ASC')->get();
$languageList = \App\Models\ClientLanguage::with('language')->where('is_active', 1)->orderBy('is_primary', 'desc')->get();
$currencyList = \App\Models\ClientCurrency::with('currency')->orderBy('is_primary', 'desc')->get();
$paymentMethod = \App\Models\PaymentMethod::where('is_show',1)->get();
$applocale = 'en';
if(session()->has('applocale')){
$applocale = session()->get('applocale');
}
@endphp

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
                    @if (\Session::has('error'))
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
                    <div id="success-msg"></div>
                    <div class="dashboard">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="page-title">
                                <h2>{{__('My Allergic Items')}}</h2>
                            </div>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
                                {{__('Add Item')}}
                          </button>
                          
                        </div>
                        {{-- <div class="outer-box"> --}}
                            <div class="box-account wishlist_product box-info mt-md-3 mt-2">
                                <div class="row">
                                    <div class="col-sm-12 table-responsive table-responsive-xs">
                                        <table class="table wishlist-table border">
                                            <thead>
                                                <tr class="table-head">
                                                    <th scope="col">{{__('SN')}}</th>
                                                    <th scope="col">{{__('Title')}}</th>
                                                    <th scope="col">{{__('Action')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $i=0;
                                                @endphp
                                                @forelse($items as $key => $item)
                                                <tr class="wishlist-row">
                                                    <td>
                                                        <div class="product-icon">
                                                            {{$i + 1}}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="product-title pl-1">
                                                            <h4 class="m-0">{{ $item['title'] }}</h4>
                                                        </div>
                                                    </td>
                                                    <td><a href="{{ route('removeItem', $item['id']) }}" class="icon me-3"><i class="ti-close"></i> </a></td>
                                                </tr>

                                                @php
                                                    $i=$i+1;
                                                @endphp
                                                @empty
                                                <tr><td align="center" colspan="6">{{__('No Item Exists In Your Allergic Items')}}</td></tr>
                                                @endforelse
    
                                            </tbody>
                                            
                                        </table>
                                    </div>
                                </div>
                            </div>
                        {{-- </div> --}}

                        @if (auth()->user()->custom_allergic_items)
                            <div class="page-title">
                                <h2>{{__('Custom Allergic Items')}}</h2>
                            </div>

                            {{ auth()->user()->custom_allergic_items }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLongTitle">{{__('Add Allergic Items')}}</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <form action="{{ route('add.allergicItems')}}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="col pl-1 multipleItems">
                                <select class=" form-control select2-multiple" required id="multiple" multiple name="allergic_item_ids[]" data-toggle="select2"  data-placeholder="Choose ...">

                                {{-- <select class="form-control w-100">  --}}
                                    @foreach ($allergic_items as $item)
                                    <option value="{{$item->id}}"
                                        @if(in_array($item->id, $selected_ids))
                                        selected="selected"
                                        @endif
                                        >
                                        {{$item->title??''}}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-2">
                                <input type="text" class="form-control" name="custom_allergic_items" placeholder="Enter Custom Allergic Items" value="{{ auth()->user()->custom_allergic_items ?? ''}}">
                            </div>

                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                        <button type="submit" class="btn btn-primary">{{__('Save Item')}}</button>
                        </div>
                    </form>
                  </div>
                </div>
              </div>

        </div>
    </div>
</section>
@endsection
@section('script')
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script>
    // var input = document.querySelector("#phone");
    // window.intlTelInput(input, {
    //     separateDialCode: true,
    //     hiddenInput: "full_number",
    //     utilsScript: "{{asset('assets/js/utils.js')}}",
    //     initialCountry: "{{ Session::get('default_country_code','US') }}",
    // });
    // $(document).ready(function () {
    //     $("#phone").keypress(function (e) {
    //         if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
    //             return false;
    //         }
    //         return true;
    //     });
    // });
    // $('.iti__country').click(function(){
    //     var code = $(this).attr('data-country-code');
    //     $('#countryData').val(code);
    // });
    $(document).on('click', 'button.update_product_status', function(){
        var status = $(this).data('status');
        var product_id = $(this).data('product_id');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            url: "{{route('user.updatePostStatus')}}",
            data: {
                'status': status,
                'product_id': product_id
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if(data.status == 'success'){
                    $('#success-msg').html('<div class="alert alert-success">'+ data.message +'</div>');
                    setTimeout(function(){
                        location.reload(); 
                    }, 2000); 
                }
            },
            error: function(data) {
            }
        });
    });

    $("#single").select2({
          placeholder: "Select a programming language",
          allowClear: true
      });
      $("#multiple").select2({
          placeholder: "Select a programming language",
          allowClear: true
      });

</script>
@endsection