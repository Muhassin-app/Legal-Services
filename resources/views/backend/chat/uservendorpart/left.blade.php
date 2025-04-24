
{{-- <div class="container">
	<div class="chat-body row overflow-hidden shadow bg-light rounded"> --}}
		<div class="col-4 px-0">
			<div class="chat-list-container flex-column d-flex pr-4">
				<div class="py-2">
					<p class="h5 mb-0 py-1 chats-title">Chats</p>
				</div>
				<div class="messages-box flex flex-1">
					@foreach ($chatrooms as $chatroom)

					<div id="chatRooms" class="list-group rounded-0">
						<div id="room_{{ $chatroom['_id']}}" data-id="{{$chatroom['_id']}}" data-OrderID="{{ $chatroom['order_id'] }}" data-OrdervendorID="{{ $chatroom['order_vendor_id'] }}" data-roomName="{{$chatroom['room_name']}}" class="chat-list-item d-flex align-items-start rounded bg-white fetchChat">
							<div class="align-self-center mr-3">
								<div class="rounded-circle bg-gray" style="width: 8px; height: 8px; opacity: 0;"></div>
							</div>
						<div class="align-self-center mr-3">
							<div class="overflow-hidden rounded-circle">
								<svg width="32" height="32" viewBox="0 0 1651 1651" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="1651" height="1651" rx="14" fill="white"></rect><path d="M495.286 1098.96L497.967 1070.86L478.04 1050.88C408.572 981.233 368 891.771 368 795.344C368 585.371 565.306 402 826 402C1086.69 402 1284 585.371 1284 795.344C1284 1005.32 1086.69 1188.69 826 1188.69V1248.69L825.913 1188.69C779.837 1188.75 733.952 1182.77 689.432 1170.9L667.26 1164.98L646.8 1175.37C620.731 1188.61 562.74 1213.98 467.32 1235.35C480.554 1191.83 490.95 1144.39 495.286 1098.96Z" stroke="url(#paint0_linear)" stroke-width="120"></path><defs><linearGradient id="paint0_linear" x1="662.312" y1="397.956" x2="416.164" y2="1678.7" gradientUnits="userSpaceOnUse"><stop stop-color="#7514FB"></stop><stop offset="0.624243" stop-color="#F26D41"></stop><stop offset="1" stop-color="#F43B4B"></stop></linearGradient></defs></svg>
							</div>
						</div>
						<div class="media-body overflow-hidden">
							<h5 class="text-truncate font-size-14 mb-1">{{ $chatroom['room_name'] }}</h5>
							<div class="font-size-11">{{$chatroom['created_date']}}</div>
							<p id="preview_message_{{$chatroom['_id']}}" class="text-truncate mb-0">..</p>
						</div>
					</div>
					@endforeach


					</div>
				</div>
				{{-- <div class="row no-gutters align-items-center pl-4 pr-2 pb-3" style="height: inherit; flex: 0 1 0%; min-height: 50px;">
					<div class="col-8 d-flex align-items-center ">
						<div class="align-self-center mr-3"><img src="http://localhost:3000/avatars/9.jpg" alt="Pablo" class="rounded-circle avatar-xs" style="width: 32px; height: 32px; object-fit: cover;"></div>
						<div class="media-body">
							<h5 class="font-size-14 mt-0 mb-1">Pablo</h5>
							<div class="d-flex align-items-center">
								<div class="rounded-circle bg-success" style="width: 8px; height: 8px; opacity: 1;"></div>
								<p class="ml-2 text-muted mb-0">Active</p>
							</div>
						</div>
					</div>
					<div class="col-4 text-danger text-right" style="cursor: pointer;">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="1em" height="1em" fill="currentColor">
							<path d="M7.5 1v7h1V1h-1z"></path>
							<path d="M3 8.812a4.999 4.999 0 0 1 2.578-4.375l-.485-.874A6 6 0 1 0 11 3.616l-.501.865A5 5 0 1 1 3 8.812z"></path>
						</svg> Log out</div>
				</div> --}}
			</div>

  </div>
