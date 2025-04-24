@extends('layouts.vertical', ['demo' => 'creative',  'title' => __(getNomenclatureName('Lawyers', true))])
@section('css')
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
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<a href="{{ route('lawyer.create') }}">
    <button class="btn btn-info waves-effect waves-light text-sm-right float-right ">
        <i class="mdi mdi-plus-circle mr-1"></i> {{ __("Add") }}
    </button>
</a>
<h4>{{ __('LAWYERS') }}</h4>

<table class="table table-centered table-nowrap table-striped" id="vendor_active_datatable" width="100%">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('Enrollment Number') }}</th>
            <th>{{ __('Image') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Last Name') }}</th>
            <th>{{ __('Mobile') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Document') }}</th>
            <!-- <th>{{ __('Refferal Code') }}</th> -->
            <th>{{ __('Status') }}</th>
            <th class="text-center">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody id="post_list">
        @foreach($users as $index => $user)
            <tr>
                <td>{{ $index + 1 }}</td> <!-- Serial Number -->
                <td>{{ $user->enrollment_no }}</td>
                @if($user->image && isset($user->image['image_path']))
                <td><img src="{{$user->image['proxy_url'].'50/50'.$user->image['image_path']}}" alt="" style="border-radius: 50%;"></td>
                @else
                    <td></td>
                @endif
                <td>{{ $user->name }}</td>
                <td>{{ $user->last_name ? $user->last_name  : '-' }}</td>
                <td>{{ $user->phone_number ? $user->phone_number : '-'}}</td>
                <td>{{ $user->email ? $user->email : '-'  }}</td>
                @if($user->document)
                <td><a href="{{ Storage::disk('s3')->url($user->document) }}" download target="_blank">
                   {{__('Download')}}
                </a></td>
                @else
                <td>{{ __('Not Uploaded') }}</td>
                @endif
                {{-- <td>
                    @if($user->refferal_code)
                        {{ $user->refferal_code }}
                    @else
                        <a href="{{ route('generate.refferalcode', $user->id) }}" class="btn btn-success">{{ __('Generate Code') }}</a>
                    @endif
                </td> --}}
                <td>{{ $user->status ? __('Active') : __('Inactive') }}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info w-100"
                                style="min-width: 100px;"
                                onclick="toggleStatus({{ $user->id }})">
                            {{ $user->status ? __('Deactivate') : __('Activate') }}
                        </button>

                        <a href="{{ route('lawyer.delete', $user->id) }}" 
                        class="btn btn-sm btn-danger w-100"
                        style="min-width: 100px;">
                            {{ __('Delete') }}
                        </a>
                    </div>
                </td>


            </tr>
        @endforeach
    </tbody>
</table>
<script>
function toggleStatus(userId) {
    $.ajax({
        url: '/client/lawyer/status/' + userId, 
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}', 
            status: 'toggle'  
        },
        success: function(response) {
            if (response.success) {
              location.reload();
            }
        },
        error: function() {
            alert('Something went wrong. Please try again.');
        }
    });
   
}
</script>
@endsection