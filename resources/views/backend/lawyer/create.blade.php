@extends('layouts.vertical', ['demo' => 'creative', 'title' => __(getNomenclatureName('Lawyers', true))])
@section('css')
<!-- Intl-Tel-Input CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.min.css"/>


<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css" rel="stylesheet" />

<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
<style type="text/css">
@media(min-width: 1440px){.content{min-height: calc(100vh - 100px);}.dataTables_scrollBody {height: calc(100vh - 500px);}}
.dd-list .dd3-item {list-style: none;}
div.dataTables_wrapper div.dataTables_filter input {width: 180px;}
</style>
<style type="text/css">
    .pac-container,.pac-container .pac-item{z-index:99999!important}.fc-v-event{border-color:#43bee1;background-color:#43bee1}.dd-list .dd3-content{position:relative}span.inner-div{top:50%;-webkit-transform:translateY(-50%);-moz-transform:translateY(-50%);transform:translateY(-50%)}.button{position:relative;padding:8px 16px;background:#009579;border:none;outline:0;border-radius:50px;cursor:pointer}.button:active{background:#007a63}.button__text{font:bold 20px Quicksand,san-serif;color:#fff;transition:all .2s}.button--loading .button__text{visibility:hidden;opacity:0}.button--loading::after{content:"";position:absolute;width:16px;height:16px;top:0;left:0;right:0;bottom:0;margin:auto;border:4px solid transparent;border-top-color:#fff;border-radius:50%;animation:button-loading-spinner 1s ease infinite}@keyframes button-loading-spinner{from{transform:rotate(0turn)}to{transform:rotate(1turn)}}
</style>

@endsection
@section('content')

<h4>{{ __('CREATE LAWYERS') }}</h4>
<div class="d-flex justify-content">
    
    <form action="{{ route('lawyer.store') }}" method="POST" enctype="multipart/form-data" class="w-50">
        @csrf

        <div class="mb-3">
    <label for="image">{{ __('Image') }}</label>
    <input type="file" class="form-control" name="image" id="image">
    @error('image')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="mb-3">
    <label for="name">{{ __('Name') }}</label>
    <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required>
    @error('name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="mb-3">
    <label for="last_name">{{ __('Last Name') }}</label>
    <input type="text" class="form-control" name="last_name" id="last_name" value="{{ old('last_name') }}" required>
    @error('last_name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="mb-3">
    <label for="mobile">{{ __('Mobile') }}</label>
    <input type="tel" class="form-control" name="mobile" id="mobile" value="{{ old('mobile') }}" required>
    @error('mobile')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="mb-3">
    <label for="email">{{ __('Email') }}</label>
    <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required>
    @error('email')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="mb-3">
    <label for="password">{{ __('Password') }}</label>
    <input type="password" class="form-control" name="password" id="password" required>
    @error('password')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="mb-3">
    <label for="password_confirmation">{{ __('Confirm Password') }}</label>
    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
    @error('password_confirmation')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="mb-3">
    <label for="document">{{ __('Document') }}</label>
    <input type="file" class="form-control" name="document" id="document">
    @error('document')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>


        <div class="">
            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </div>
    </form>
</div>

<!-- Intl-Tel-Input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script>
   var input = document.querySelector("#mobile");
    window.intlTelInput(input, {
        initialCountry: "sa", // Set default to Saudi Arabia
        separateDialCode: true, // Show separate dial code
    });
</script>


@endsection