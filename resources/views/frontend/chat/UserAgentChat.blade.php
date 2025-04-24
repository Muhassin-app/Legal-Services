@extends('layouts.store', ['title' =>  __('Chats')   ])
@section('css')
    <link href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/dropify/dropify.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/mohithg-switchery/mohithg-switchery.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/multiselect/multiselect.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/selectize/selectize.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/bootstrap-selectroyoorders/bootstrap-select.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/bootstrap-touchspin/bootstrap-touchspin.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/nestable2/nestable2.min.css') }}" rel="stylesheet" type="text/css" />

@endsection
@section('customcss')
<link href="{{ asset('assets/libs/chat/userchat.css') }}" rel="stylesheet" type="text/css" />

@endsection
@section('content')
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
        <div class="row my-md-3">
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
                <div class="page-title-box">
                    <h4 class="page-title">{{ getNomenclatureName('User/Driver Chat', true) }}</h4>
                </div>
                <div class="container-fluid p-0">
                    <div class="card">
                        <div class="card-body position-relative p-0">
                            <div class="chat-body row overflow-hidden shadow bg-light rounded">
                                @include('frontend.chat.useragentpart.left')
                                @include('frontend.chat.useragentpart.right')
                                @include('backend.chat.mediaUpload.index')

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
    <!-- <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });
        });
    </script> -->
@php
$authData = json_encode(@$data->toArray());
$user_type = 'user';
$to_message = 'to_agent';
$from_message = 'from_user';
$chat_type = 'agent_to_user';
$startChatype = 'agent_to_user';
$apiPre = 'client';
$rePre = 'client/chat/user';
$fetchDe = 'fetchRoomByUserId';
@endphp
@endsection
@section('script')
<script>
    var to_message = `<?php echo $to_message; ?>`;
    var user_type = `<?php echo $user_type; ?>`;
    var from_message = `<?php echo $from_message; ?>`;
    var chat_type = `<?php echo $chat_type; ?>`;
    var startChatype = `<?php echo $startChatype; ?>`;
    var apiPre = `<?php echo $apiPre; ?>`;
    var rePre = `<?php echo $rePre; ?>`;
    var fetchDe = `<?php echo $fetchDe; ?>`;
</script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
{{-- <script src="{{asset('assets/js/chat/user_agent_chat.js')}}"></script> --}}
<script src="{{asset('assets/js/chat/commonChat.js')}}"></script>

<script src="{{asset('assets/js/chat/socket_chat.js')}}"></script>
<script>
    var client_data = `<?php echo $authData; ?>`;
    fetchChatGroups(client_data);

</script>


<script>
    $(document).ready(async function(){
//alert(window.location.pathname.split('/')[4])
          // Create SocketIO instance, connect
          if(window.location.pathname.split('/')[4] !=  undefined && window.location.pathname.split('/')[4] !=null) {
            await $('#room_'+window.location.pathname.split('/')[4]).click();
          }

    })

  </script>
@endsection
