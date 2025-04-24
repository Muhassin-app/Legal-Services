@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('vendors', true)])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css" rel="stylesheet" />

<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">


@endsection
@section('content')
<div class="container-fluid vendor-page">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Generate Vendor Payment Report') }}</h4>
            </div>
        </div>
    </div>
</div>
<!-- start product action popup -->
    <form action="{{route('vendorReportExport')}}" method="post" >
        <div class="row">
            @csrf
            <div class="col-lg-4">
                <label for="start_date">{{ __('Start Date') }}</label>
                <input type="date" name="start_date" class="form-control start_date" id="start_date" >
            </div>
            <div class="col-lg-4">
                <label for="end_date">{{ __('End Date') }}</label>

                <input type="date" name="end_date" class="form-control end_date" id="end_date">
            </div>

        <div class="col-lg-4 mt-3">
            <button type="submit" class="btn btn-primary exportReport" onclick="exportReport()"> {{ __('Generate Report') }}  </button>
        </div>
    </div>
</form>


@endsection
@section('script')

<script>
    function exportReport(){
        var startDate = document.getElementById('start_date');
        startDate.value = '';
        var endate = document.getElementById('end_date');
        endate.value = '';
    };
</script>
