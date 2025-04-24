@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Vendor-Inventory Import'])

@section('css')

<style type="text/css">
    .fc-v-event {
        border-color: #43bee1;
        background-color: #43bee1;
    }

    .dd-list .dd3-content {
        position: relative;
    }

    span.inner-div {
        top: 50%;
        -webkit-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
        transform: translateY(-50%);
    }

    /**/
    .card.subscript-box {
        background-color: #fff;
        border: 1px solid #f7f7f7;
        border-radius: 16px;
        padding: 10px;
        box-shadow: 0 0.75rem 6rem rgb(56 65 74 / 7%);
    }

    .gold-icon {
        background: #ebcd71;
        height: 100%;
        display: flex;
        align-items: center;
        border-radius: 4px;
        justify-content: center;
        padding: 20px;
    }

    .gold-icon img {
        height: 120px;
    }

    .pricingtable {
        width: calc(100% - 10px);
        background: #fff;
        box-shadow: 0 0.75rem 6rem rgb(56 65 74 / 7%);
        color: #cad0de;
        margin: auto;
        border-radius: 10px;
        overflow: hidden;
    }

    .pricingtable .pricingtable-header {
        padding: 0 10px;
        background: rgb(0 0 0 / 20%);
        width: 100%;
        height: 100%;
        transition: all .5s ease 0s;
        text-align: right;
    }

    .pricingtable .pricingtable-header i {
        font-size: 50px;
        color: #858c9a;
        margin-bottom: 10px;
        transition: all .5s ease 0s
    }

    .pricingtable .price-value {
        font-size: 30px;
        color: #fff;
        transition: all .5s ease 0s
    }

    .pricingtable .month {
        display: block;
        font-size: 14px;
        color: #fff;
    }

    .pricingtable:hover .month,
    .pricingtable:hover .price-value,
    .pricingtable:hover .pricingtable-header i {
        color: #fff
    }

    .pricingtable .heading {
        font-size: 24px;
        margin-bottom: 20px;
        text-transform: uppercase
    }

    .pricingtable .pricing-content ul {
        list-style: none;
        padding: 0;
        margin-bottom: 30px
    }

    .pricingtable .pricing-content ul li {
        line-height: 30px;
        display: block;
        color: #a7a8aa
    }

    .pricingtable.blue .heading,
    .pricingtable.blue .price-value {
        color: #4b64ff
    }

    .pricingtable.blue:hover .pricingtable-header {
        background: #4b64ff
    }


    .pricingtable.red .heading,
    .pricingtable.red .price-value {
        color: #ff4b4b
    }

    .pricingtable.red:hover .pricingtable-header {
        background: #ff4b4b
    }

    .pricingtable.green .heading,
    .pricingtable.green .price-value {
        color: #40c952
    }

    .pricingtable.green:hover .pricingtable-header {
        background: #40c952
    }


    .pricingtable.blue:hover .price-value,
    .pricingtable.green:hover .price-value,
    .pricingtable.red:hover .price-value {
        color: #fff
    }
    .iti{
        width: 100%;
    }

    /**/
</style>
@endsection

@section('content')
<div class="container-fluid vendor-show-page">

    <!-- start page title -->
    <div class="row">
        <div class="col-12 d-flex align-items-center">
            <div class="page-title-box">
                <h4 class="page-title">{{ucfirst($vendor->name)}} {{ __('profile') }}</h4>
            </div>
            <div class="form-group mb-0 ml-3">
                <div class="site_link position-relative">
                    <a href="{{route('vendorDetail',$vendor->slug)}}" target="_blank"><span id="pwd_spn" class="password-span">{{route('vendorDetail',$vendor->slug)}}</span></a>
                    <label class="copy_link float-right" id="cp_btn" title="copy">
                        <img src="{{ asset('assets/icons/domain_copy_icon.svg')}}" alt="">
                        <span class="copied_txt" id="show_copy_msg_on_click_copy" style="display:none;">{{ __('Copied') }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-sm-12">
            <div class="text-sm-left">
                @if (\Session::has('success'))
                <div class="alert alert-success">
                    <span>{!! \Session::get('success') !!}</span>
                </div>
                @php
                \Session::forget('success');
                @endphp
                @endif
                @if (\Session::has('error_delete'))
                <div class="alert alert-danger">
                    <span>{!! \Session::get('error_delete') !!}</span>
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

    <div class="row">

        
        <input type="hidden" name="vendor_slug" value="{{$vendor->slug}}" id="vendor_slug">
        <div class="col-lg-4">
            <select name="store_list" class="form-control mb-3" id="store_list">
                <option>{{__('Select Store')}}</option>
                @foreach($store_list_data as $key => $store)
                    <option value="{{ $store['id']??0 }}"> {{ $store['name']??'' }}</option>
                @endforeach
            </select>
            <div  id="inventory_product_list"></div>

        </div>
    </div>
</div>

<script>
    
    $("select[name='store_list']").change(function() {
        
        var vendor_id = $('#store_list :selected').val();
        var vendor_slug = $('#vendor_slug').val();
        var url = "{{ route('get.inventory.store.products')}}";
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {vendor_id: vendor_id,vendor_slug:vendor_slug},
            success: function(response) {
                if (response.success == true) {
                    
                    $('#inventory_product_list').html('');
                    $('#inventory_product_list').html(response.html);
                    
                }
            }
        });
    });

    
    </script>

@endsection
