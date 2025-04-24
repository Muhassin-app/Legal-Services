

		<div class="col-4 px-0">
			<div class="chat-list-container flex-column d-flex pr-4">
		
				<div class="p-2 outer_search position-relative">
					<input type="text" id="outer_search" class="form-control" placeholder="Search here for chats..">
					<span class="search-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"></path></svg></span>
				</div>
				<div class="messages-box flex flex-1 sortDiv">
					@php $layercount = count($chatrooms); @endphp

					@foreach ($chatrooms as $key => $chatroom)
					@php $zIndex = $layercount - 1 - $key;@endphp

						<script>
							var converted_date =  "{{ $chatroom['updated_date'] }}";
							var vData = new Date('{{ $chatroom['updated_date'] }}').toDateString().slice(0, 10) + ' ' + new Date('{{ $chatroom['updated_date'] }}').toLocaleTimeString().slice(0, 10);
		               		@php $vData = "<script>document.write(vData)</script>"@endphp
						</script>
						<div id="chatRooms_{{ $chatroom['_id']}}" data-text="{{ $chatroom['room_id'] }}" data-sort="{{ $zIndex }}" data-timestamp="{{$vData}}" class="list-group rounded-0 chatRoomsDivs">
							
							<div id="room_{{ $chatroom['_id']}}"   data-OrderID="{{ $chatroom['order_id'] }}" data-OrdervendorID="{{ $chatroom['order_vendor_id'] }}" data-id="{{$chatroom['_id']}}" data-roomID="{{$chatroom['room_id']}}" data-roomName="{{$chatroom['room_name']}}" class="chat-list-item row fetchChat">
								
								<div class="align-self-center col-3">
									<div class="user_show">
										<p class="orderNumber m-0 mb-2">#{{ $chatroom['room_id'] }}</p>
										@if(count($chatroom['user_Data']) > 0)
											@foreach ($chatroom['user_Data'] as $user )
											<a class="user_data_left" href="javascript:void(0)" >
												<img class="rounded-circle userImg" src="{{@$user['display_image']}}">
												
											</a>
											@endforeach
										@endif
									
									</div>
								</div>
								<div class="col-9 position-relative pl-0">
									<div class="alNameTime last_message">
									
											<h6 id="preview_message_name_{{$chatroom['_id']}}" class="mb-1 mt-0">{{ @$chatroom['chat_Data'][0]['username'] }}</h6>
											
											
											<span id="preview_message_time_{{$chatroom['_id']}}"><?php echo $vData;?>
											</span>

										
                                    </div>
                                    <p id="preview_message_{{$chatroom['_id']}}" class="orderChatMessage mb-0">{{@$chatroom['chat_Data'][0]['message']}} </p>

								</div>
							</div>
						</div>
					@endforeach
				</div>
			</div>

  		</div>
