@extends('layouts.vertical', ['title' => 'Profile'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<!-- Start Content-->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="text-sm-left">
                @if (\Session::has('success'))
                    <div class="alert alert-success">
                        <span>{!! \Session::get('success') !!}</span>
                    </div>
                @elseif(\Session::has('error'))
                    <div class="alert alert-danger">
                        <span>{!! \Session::get('error') !!}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ ('Module-wise Cache Management')}}</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="row h-100">
                <div class="col-12">
                    <form method="POST" action="{{ route('configure.updateAdditional', Auth::user()->code) }}">
                        @csrf
                        <!-- Hyperlocal start -->
                        <div class="card-box h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h4 class="header-title mb-0">{{ __('Home Page Cache') }}</h4>
                                <button class="btn btn-info d-block" type="submit"> {{ __('Save') }} </button>
                            </div>
                            <p class="sub-header">
                                {{ __('Enable cache will cache the home page data in redis.') }}
                            </p>
                            <input type="hidden" name="is_cache_enable_for_home" id="is_cache_enable_for" value="0">
{{-- @php
    pr($additionalPreferences);
@endphp --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label for="is_cache_enable_for_home" class="mr-3">{{ __('Enable') }}</label>
                                        <input type="checkbox" data-plugin="switchery" name="is_cache_enable_for_home"
                                            id="is_cache_enable_for_home"  data-type="for_home" class="form-control is_cache_enable_for_home" data-color="#43bee1"
                                            @if (isset($additionalPreferences) && $additionalPreferences['is_cache_enable_for_home'] == '1') checked @endif>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 mt-3 is_cache_enable_for_home"
                                            style="{{ isset($additionalPreferences) && $additionalPreferences['is_cache_enable_for_home'] == '1' ? '' : 'display:none;' }}">
                                            <div class="row">
                                                <div class="col-12">
                                                   
                                                    <div class="form-group mt-3 mb-0">
                                                        <label for="cache_reset_time_for_home">{{ __('Cache reset time') }}/sec</label>
                                                        <input type="text" name="cache_reset_time_for_home"
                                                            id="cache_reset_time_for_home" placeholder="3600 seconds default"
                                                            class="form-control"
                                                            value="{{ old('cache_reset_time_for_home', $additionalPreferences['cache_reset_time_for_home'] ?? '') }}">
                                                        @if ($errors->has('cache_reset_time_for_home'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('cache_reset_time_for_home') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="form-group mt-3 mb-0">
                                                        <label for="cache_radius_for_home">{{ __('Cache Radius (will be used when hyperl local enabled)') }}/km</label>
                                                        <input type="text" name="cache_radius_for_home"
                                                            id="cache_radius_for_home" placeholder="5km default"
                                                            class="form-control"
                                                            value="{{ old('cache_radius_for_home', $additionalPreferences['cache_radius_for_home'] ?? '') }}">
                                                        @if ($errors->has('cache_radius_for_home'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('cache_radius_for_home') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="form-group mt-3 mb-0">
                                                       <a class="purge_cache" data-code="{{ Auth::user()->code }}" href="javascript:(0);">Purge Cache</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- Hyperlocal end -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script type="text/javascript">

        var is_cahce_enable_for_home = $('.is_cache_enable_for_home');
        $(document).on('change','.is_cache_enable_for_home',function(){
            var tt = $(this).attr('data-type');
            if($(this).prop('checked')) {
                $(`.is_cache_enable_${tt}`).show();
            } else {
                $(`.is_cache_enable_${tt}`).hide();
            }
            // if ($('#is_hyperlocal:checked').length != 1) {
            //     $('.disableHyperLocal').hide();
            // } else {
            //     $('.disableHyperLocal').show();
            // }
        });

    $(document).on('click', '.purge_cache', function() {
            Swal.fire({
                title: "{{__('Are you sure?')}}",
                text:"{{__('This will delete homepage cache.')}}",
                    // icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Delete',
            }).then((result) => {

                if(result.value)
                {
                    spinnerJS.showSpinner();
                    var code = $(this).attr('data-code');
                    purgeCache(code)
                }else{
                    return false;
                }
                
        });
    });
    async function purgeCache(code){
        
        axios.get(`/client/deleteKeysContainingWord/${code}`)
        .then(async response => {
         //console.log(response);
            if(response.data.success){
                sweetAlert.success('success',)
                spinnerJS.hideSpinner();
                sweetAlert.success('Success','Deleted successfully!')
               
            } else{
                spinnerJS.hideSpinner();
                sweetAlert.error('Oops...',response.data.message)
            }
        })
        .catch(e => {
            spinnerJS.hideSpinner();
            sweetAlert.error('Oops...',response.data.message)
        })    

    }
        
    </script>
@endsection
