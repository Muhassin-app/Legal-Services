@extends('layouts.vertical', ['demo' => 'Orders', 'title' => 'Accounting - Orders'])
@section('css')
{{-- <link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" /> --}}
<style>
.dataTables_filter,.toolbar,.dt-buttons.btn-group.flex-wrap {position: absolute;height:40px;}.dataTables_filter{right:0;top: -50px;}
.dataTables_filter label{margin:0;height:40px;}.dataTables_filter label input{margin:0;height:40px;}.dt-buttons.btn-group.flex-wrap{right: 200px;top: -50px;}
.table-responsive{position: relative;overflow:visible;margin-top:10px;}table.dataTable{margin-top:0 !important;}
div.dataTables_wrapper div.dataTables_filter input {width: 285px;}
.dt-buttons.btn-group.flex-wrap {right: 310px;top: -50px;}
</style>
@endsection
@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ __('Marg Config') }}</h4>
                </div>
            </div>
        </div>
       
    </div>
</div>
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<div class="col-xl-4 col-lg-4 mb-3">
    <!-- Social Logins title start -->
    <div class="page-title-box">
        <h4 class="page-title text-uppercase">{{ __("Marg")}}</h4>
    </div><!-- Social Logins title end -->

    <form method="POST" action="{{ route('vendorMargConfig.update',$vendor_id) }}">
        @csrf
        <!-- marg card start -->
        <div class="card-box h-100">
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-0 switchery-demo">
                        <label for="fb_login" class="d-flex align-items-center justify-content-between">
                            <h5 class="social_head"><i style="font-size: 24px;" class="mdi mdi-marg"></i>
                                <span>{{ __('Marg') }}</span>
                            </h5>

                            <button class="btn btn-info btn-block save_btn" name="marg_submit"
                                type="submit">{{ __('Save') }} </button>
                        </label>
                        <label for="" class="mr-3">{{ __('Enable') }}</label>
                        <input type="checkbox" data-plugin="switchery" id="is_marg_enable"
                            class="form-control checkbox_change" data-className="is_marg_enable_hidden"
                            data-color="#43bee1"
                            @if (@$vendorMargConfig->is_marg_enable == '1') checked='checked' value="1" @endif>
                        <input type="hidden"
                            @if (isset($vendorMargConfig->is_marg_enable) == 1) value="1" @else value="0" @endif
                            name="is_marg_enable" id="is_marg_enable_hidden" />

                        @if (isset($vendorMargConfig->is_marg_enable) == 1 && $vendorMargConfig->marg_last_date_time)
                            <label for="" id="sycn_time" class="ml-3">{{ __('Last Sync Date & Time :') }}
                            {{ convertDateTimeInClientTimeZone($vendorMargConfig->marg_last_date_time, 'Y-m-d h:i:s') }}</label >
                        @endif

                    </div>
                </div>
            </div>

            @php
            $getAdditionalPreference = getAdditionalPreference(['marg_company_url']);
            @endphp
            <div class="row marg_row"
                style="{{ isset($vendorMargConfig->is_marg_enable) && $vendorMargConfig->is_marg_enable == 1 ? '' : 'display:none;' }}">
                <div class="col-12">
                    <div class="form-group mb-2 mt-2">
                        <label for="marg_company_url">{{ __('Marg Company Url') }}</label>
                        <input type="text" name="marg_company_url" id="marg_company_url" 
                        @if ($getAdditionalPreference['marg_company_url'] || $getAdditionalPreference['marg_company_url'] != '0')
                            readonly        
                        @endif
                            placeholder="" class="form-control" required
                            value="{{ $getAdditionalPreference['marg_company_url'] == '0' ? old('marg_company_url', $vendorMargConfig->marg_company_url ?? '') : $getAdditionalPreference['marg_company_url'] }}">
                        @if ($errors->has('marg_company_url'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('marg_company_url') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>


            <div class="row marg_row"
                style="{{ isset($vendorMargConfig->is_marg_enable) && $vendorMargConfig->is_marg_enable == 1 ? '' : 'display:none;' }}">
                <div class="col-12">
                    <div class="form-group mb-2 mt-2">
                        <label for="marg_company_code">{{ __('Company Code') }}</label>
                        <input type="text" name="marg_company_code" id="marg_company_code" required
                            placeholder="" class="form-control"
                            value="{{ old('marg_company_code', $vendorMargConfig->marg_company_code ?? '') }}">
                        @if ($errors->has('marg_company_code'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('marg_company_code') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row marg_row"
                style="{{ isset($vendorMargConfig->is_marg_enable) && $vendorMargConfig->is_marg_enable == 1 ? '' : 'display:none;' }}">
                <div class="col-12">
                    <div class="form-group mb-2 mt-2">
                        <label for="marg_access_token">{{ __('Marg ID') }}</label>
                        <input type="text" name="marg_access_token" id="marg_access_token" required
                            placeholder="" class="form-control"
                            value="{{ old('marg_access_token', $vendorMargConfig->marg_access_token ?? '') }}">
                        @if ($errors->has('marg_access_token'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('marg_access_token') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row marg_row"
                style="{{ isset($vendorMargConfig->is_marg_enable) && $vendorMargConfig->is_marg_enable == 1 ? '' : 'display:none;' }}">
                <div class="col-12">
                    <div class="form-group mb-2 mt-2">
                        <label for="marg_decrypt_key">{{ __('Decrypt Key') }}</label>
                        <input type="text" name="marg_decrypt_key" id="marg_decrypt_key" placeholder=""
                            class="form-control" required
                            value="{{ old('marg_decrypt_key', $vendorMargConfig->marg_decrypt_key ?? '') }}">
                        @if ($errors->has('marg_decrypt_key'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('marg_decrypt_key') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row marg_row"
                style="{{ isset($vendorMargConfig->is_marg_enable) && $vendorMargConfig->is_marg_enable == 1 ? '' : 'display:none;' }}">
                <div class="col-12">
                    <div class="form-group mb-2 mt-2">
                        <label for="marg_date_time">{{ __('Date Time (yyyy-mm-dd 00:00:00)') }}</label>
                        <input type="text" name="marg_date_time" id="marg_date_time" placeholder=""
                            class="form-control"
                            value="{{ old('marg_date_time', $vendorMargConfig->marg_date_time ?? '') }}">
                        @if ($errors->has('marg_date_time'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('marg_date_time') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
    </form>
    
    <div class="row marg_row"style="{{ isset($vendorMargConfig->is_marg_enable) && $vendorMargConfig->is_marg_enable == 1 ? '' : 'display:none;' }}">
        <div class="col-12">
        @php
            {
                $marg_order =  App\Models\Order::whereHas('vendors',function($q) use($vendor_id){
                    $q->where('vendor_id', $vendor_id);
                })->where('marg_status', '=',null)->where('marg_max_attempt', '>',2)->first();

                $vendor_config = App\Models\VendorMargConfig::where('vendor_id',$vendor_id)->first();
                $class= "";
                if($marg_order || (!$vendor_config)){
                    $class= "disabled";
                }                
            }    
        @endphp

        <button class="btn btn-info btn-block" id="sync_marg_btn" {{ $class }} >{{ __('Sync Data') }} </button>

        </div>
    </div>
</div>

@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/gh/AmagiTech/JSLoader/amagiloader.js"></script>
<script>
    var is_marg_enable = $('#is_marg_enable');

if (is_marg_enable.length > 0) {
    is_marg_enable[0].onchange = function() {

        if ($('#is_marg_enable:checked').length != 1) {
            $('.marg_row').hide();
        } else {
            $('.marg_row').show();
        }
    }
}

$(document).on("click", "#sync_marg_btn", function(e) {
        AmagiLoader.show();
        e.preventDefault();
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "{{ route('sync.margVendor',$vendor_id) }}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                $('#sycn_time').html(response.time);
                AmagiLoader.hide();
                sweetAlert.success('Data Sycn Successfully!');
            },
            error: function(response) {
                AmagiLoader.hide();
                sweetAlert.error('Error!','Marg api - '+response.responseJSON.message);
            }
        });
    });
</script>
@endsection
