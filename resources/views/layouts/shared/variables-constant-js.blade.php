
@php
//Chat variable
$socket_url = '';
$admin_chat = '';
$driver_chat = '';
$customer_chat = '';
$auth_id = '';
$db ='';
$image_url = '';
$user_name = '';
$authData = '';
$au = '';

if(Auth::check()){
	$au = [];
	$cl_data = \App\Models\Client::first();
	$socket_url = @$cl_data->socket_url;
	$admin_chat = @$cl_data->admin_chat;
	$driver_chat = @$cl_data->driver_chat;
	$customer_chat = @$cl_data->customer_chat;
	$db = @$cl_data->database_name;

	$auth_id = @Auth::user()->id;
	$image_url = @Auth::user()->image_url;
	$image_url = '';
	$authData = json_encode(@Auth::user()->toArray());
	$au['email'] = @Auth::user()->email;
	$au['phone_number'] = @Auth::user()->phone_number;
	$au['dial_code'] = @Auth::user()->dial_code;
	$au['image']['image_fit'] =  @Auth::user()->image->image_fit;
	$au['image']['image_path'] = @Auth::user()->image->image_path;
	$au['name'] = @Auth::user()->name;
	$au =  json_encode(@$au);
}

@endphp

<script>
    //Global Variable
    var service_period='';


	var sUrl = "{!! $socket_url !!}";
	var admin_chat = "{!! $admin_chat !!}";
	var driver_chat = "{!! $driver_chat !!}";
	var customer_chat = "{!! $customer_chat !!}";
	var auth = "{!! $auth_id !!}";
	var db = "{!! $db !!}";
    var authData =  `<?php  echo $authData  ?>`;
	var au = `<?php  echo $au   ?>`;



    //Chat Variables
	var socket = null;
	var Auth = {
		auth_id:auth,
		database_name:db,
        authData:authData,
		auData : au
		
	}

    var Chat = {
		orderData:{
			
		}
	}
	var SocketConstants = {
    	Socket_url : sUrl,
		admin_chat : admin_chat,
		driver_chat : driver_chat,
		customer_chat : customer_chat,
		socket:'',
	} 
</script>