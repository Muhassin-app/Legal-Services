@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Verification Options'])

@section('content')

<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="text-sm-left">
                @if (\Session::has('success'))
                <div class="alert mt-2 mb-0 alert-success">
                    <span>{!! \Session::get('success') !!}</span>
                </div>
                @endif
                @if ( ($errors) && (count($errors) > 0) )
                <div class="alert mt-2 mb-0 alert-danger">
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

    <form method="POST" id="payment_option_form" action="{{route('verifyoption.store')}}">
        @csrf
        @method('POST')
        <div class="row align-items-center">
            <div class="col-sm-8">
                <div class="text-sm-left">
                    <div class="page-title-box">
                        <h4 class="page-title">{{ __("Verification Options") }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 text-right">
                <button class="btn btn-info waves-effect waves-light save_btn" type="submit"> {{ __("Save") }}</button>
            </div> 
        </div>
        <div class="row">
            @foreach($verify_options as $key => $opt)
            <div class="col-6 col-md-3 col-xl-2 mb-3">

                <input type="hidden" name="method_id[]" id="{{$opt->id}}" value="{{$opt->id}}">
                <input type="hidden" name="method_name[]" id="{{$opt->code}}" value="{{$opt->code}}"> 

                <?php
                $creds = json_decode($opt->credentials);
                ?>

                <div class="card-box h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="header-title mb-0">{{$opt->title}}</h4>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="" class="mr-0 d-block">{{ __("Enable") }}</label>
                                <input type="checkbox" data-id="{{$opt->id}}" data-title="{{$opt->code}}" data-plugin="switchery" name="active[{{$opt->id}}]" class="chk_box all_select" data-color="#43bee1" @if($opt->status == 1) checked @endif>
                            </div>
                        </div>
                        @if ( (strtolower($opt->code) != 'yoti'))
                        <div class="col-6">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="" class="mr-0 d-block">{{ __('Sandbox') }}</label>
                                <input type="checkbox" data-id="{{$opt->id}}" data-title="{{$opt->code}}" data-plugin="switchery" name="sandbox[{{$opt->id}}]" class="chk_box" data-color="#43bee1" @if($opt->test_mode == 1) checked @endif>
                            </div>
                        </div>
                        @endif

                        {{-- <div class="col-6">
                            <div class="form-group mb-0 switchery-demo">
                                <label for="" class="mr-0 d-block">{{ __('Sandbox') }}</label>
                                <input type="checkbox" data-id="{{$opt->id}}" data-title="{{$opt->code}}" data-plugin="switchery" name="sandbox[{{$opt->id}}]" class="chk_box" data-color="#43bee1" @if($opt->test_mode == 1) checked @endif>
                            </div>
                        </div> --}}
                    </div>

                    @if ( (strtolower($opt->code) == 'passbase') )
                    <div class="mt-2" id="passbase_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="passbase_publish_key" class="mr-3">{{ __("Publishable API key") }}</label>
                                    <input type="text" name="passbase_publish_key" id="passbase_publish_key" class="form-control" value="{{$creds->publish_key ?? ''}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="passbase_secret_key" class="mr-3">{{ __("Secret API Key") }}</label>
                                    <input type="password" name="passbase_secret_key" id="passbase_secret_key" class="form-control" value="{{$creds->secret_key ?? ''}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif


                    @if ( (strtolower($opt->code) == 'yoti') )
                    <div class="mt-2" id="yoti_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="yoti_auth_key" class="mr-3">{{ __("Authorization key") }}</label>
                                    <input type="text" name="yoti_auth_key" id="yoti_auth_key" class="form-control" value="{{$creds->yoti_auth_key ?? ''}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="yoti_sdk_id" class="mr-3">{{ __("Yoti SDK Id") }}</label>
                                    <input type="text" name="yoti_sdk_id" id="yoti_sdk_id" class="form-control" value="{{$creds->yoti_sdk_id ?? ''}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>


            @endforeach
        </div>
    </form>

</div>

@endsection

@section('script')
<script type="text/javascript">
        $('.all_select').change(function() {
        var id = $(this).data('id');
        // console.log(id);
        var title = $(this).data('title');
        var code = title.toLowerCase();
        if ($(this).is(":checked")) {
            $("#" + code + "_fields_wrapper").show();
            $("#" + code + "_fields_wrapper").find('input').attr('required', true);
        } else {
            $("#" + code + "_fields_wrapper").hide();
            $("#" + code + "_fields_wrapper").find('input').removeAttr('required');
        }
    });
</script>

@endsection
