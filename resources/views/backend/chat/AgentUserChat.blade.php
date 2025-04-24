@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('Product Reviews', True)])
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

    <style>


    </style>

@section('customcss')
<link href="{{ asset('assets/libs/chat/chat.css') }}" rel="stylesheet" type="text/css" />

@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ getNomenclatureName('Driver/User Chat', true) }}</h4>
                </div>
            </div>
        </div>

    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body position-relative p-0">

                            <div class="chat-body row overflow-hidden shadow bg-light rounded">

                                @include('backend.chat.agentpart.left')
                                @include('backend.chat.agentpart.right')
                                @include('backend.chat.mediaUpload.index')

                            </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });
        });
    </script>
      @php
      $authData = json_encode(@$data->toArray());
      $user_type = 'admin';
      $to_message = 'to_user_agent';
      $from_message = 'from_admin';
      $chat_type = 'agent_to_user';
      $startChatype = 'agent_to_user';
      $apiPre = 'client';
      $rePre = 'client/chat/user';
      $fetchDe = 'fetchAllRoom';
  @endphp
@endsection

@section('script-bottom')
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
<script src="https://momentjs.com/downloads/moment.min.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
{{-- <script src="{{asset('assets/js/chat/admin_user_agent_chat.js')}}"></script> --}}
<script src="{{asset('assets/js/chat/commonChat.js')}}"></script>
<script src="{{asset('assets/js/chat/socket_chat.js')}}"></script>
{{-- <script src="{{asset('assets/js/chat/chatNotifications.js')}}"></script> --}}
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
