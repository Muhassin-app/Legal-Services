@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Product Measuremnet'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/css/dropzone.css')}}" rel="stylesheet" />
@endsection

@section('content')

<!-- Start Content-->
<div class="container-fluid alToolsPage">

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
    <div class="row align-items-center">
        <div class="col-sm-12">
            <div class="text-sm-left">
                <div class="page-title-box">
                    <h4 class="page-title">{{ __("Product Measurement") }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @if(Auth::user()->is_superadmin == 1)

    @endif
</div>

@endsection

@section('script')
<script src="{{asset('assets/js/dropzone.js')}}"></script>

<script type="text/javascript">

</script>
@endsection
