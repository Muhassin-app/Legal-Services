

    //await getALLchat();
    
    $(document).on('click','.start_chat',async function(){
        var vendor_order_id = $(this).attr('data-vendor_order_id');
        var vendor_id = $(this).attr('data-vendor_id');
        var order_id = $(this).attr('data-order_id');
       
        if(!vendor_order_id && !vendor_id && !order_id){
            return;
            
        }
        $('#order_list_order').show();
        await startChat(vendor_order_id,vendor_id,order_id);
    });

    var input = document.getElementById("message_box");
    if(input){
        input.addEventListener("keypress", function(event) {
            // If the user presses the "Enter" key on the keyboard
            if (event.key === "Enter") {
                // Cancel the default action, if needed
                event.preventDefault();
                // Trigger the button element with a click
                document.getElementById("send_message").click();
            }
        });
    }
    // Execute a function when the user presses a key on the keyboard
  
    $(document).on('click','.fetchChat',async function(){
        var roomId = $(this).attr('data-id');
        var roomIdText = $(this).attr('data-roomid');
        var roomName = $(this).attr('data-roomName');
        var roomIDn = $(this).attr('data-roomID');
        var OrdervendorID = $(this).attr('data-ordervendorid');
        var order_id = $(this).attr('data-orderid');
       
        if(!roomId && !OrdervendorID && !order_id){
            $('#chatHistory').removeClass('room_'+roomId);
            return;
            
        }
        $('#roomName').html(roomIDn);
        await fetchOderVendorDetails(OrdervendorID,order_id);
        await getALLchat(roomId,roomIdText);
        await getAllUser(roomId,roomIdText);
    });

    $(document).on('click','.join_room',async function(){
        var room_id = $(this).attr('data-id');
        // var vendor_id = $(this).attr('data-vendor_id');
        // var order_id = $(this).attr('data-order_id');
       
        if(!room_id){
            return;
            
        }
        //$('#order_list_order').show();
        await JoinRoom(room_id);
    });

    $(document).on('click','.send_message',async function(){
        var room_id = $(this).attr('data-id');
        var roomIdText = $(`#room_${room_id}`).attr('data-roomid');

         var message = $('#message_box').val();
        // var vendor_id = $(this).attr('data-vendor_id');
        // var order_id = $(this).attr('data-order_id');
       
        if(!room_id || !message){
            return;
            
        }
        //$('#order_list_order').show();
        await sendMessage(message,room_id,roomIdText);
    });

    $(document).on('keyup','#outer_search',function() {
        var n = $(this).val(); //convert value to lowercase for case-insensitive comparison
        $(".chatRoomsDivs").each( function(){
           var $this = $(this);
           var value = $this.attr( "data-text" ); //convert attribute value to lowercase
           $this.toggleClass( "hidden", !value.includes( n ) );
        })
    });

    async function startChat(vendor_order_id,vendor_id,order_id){

        axios.post(`/client/chat/startChat`, {
            sub_domain: window.location.origin,
            client_id:  1,
            db_name:Auth.database_name,
            user_id:  Auth.auth_id,   
            type:'vendor_to_user',
            agent_id:'',
            vendor_order_id:vendor_order_id,
            vendor_id:vendor_id,
            order_id:order_id      
        })
        .then(async response => {
             console.log(response.data.status);
             $('#order_list_order').hide();
             if(response.data.status === true) {
                var data = response.data;
                window.location.href = `/client/chat/vendorUser/${data.roomData._id}`;
                
             } else {
                Swal.fire(
                    'error',
                    'Something went wrong, try again later!',                                    
                    
                )
             }

            
        })
        .catch(e => {
            Swal.fire(
                'Something went wrong, try again later!',                                    
                'error'
            )
        })
    }
    function convertDateTime(cdate){
        return cdate.toDateString() +' '+ cdate.toLocaleTimeString();
    }

    function convertDateTimeStamp(cdate){
        var currentTimeStamp = new Date(cdate);
        return currentTimeStamp
        // console.log(currentTimeStamp);
        // return cdate.toDateString() +' '+ cdate.toLocaleTimeString();
    }
    async function fetchOderVendorDetails(order_vendor_id,order_id){
        axios.post(`/client/chat/fetchOrderDetail`, {
            order_vendor_id: order_vendor_id,
            order_id:order_id
        })
        .then(async response => {
            if(response.data.status) {
                if(response.data.orderData != undefined) {
                    var data = response.data.orderData;
                    Chat.orderData.order_number  =  (data.order_number != undefined ) ? data.order_number : '';
                    Chat.orderData.payable_amount = (data.vendors[0].payable_amount != undefined ) ? data.vendors[0].payable_amount : '';
                    Chat.orderData.vendor_name = (data.vendors[0].vendor.name != undefined ) ? data.vendors[0].vendor.name : '';
                    $('#order_num').html(Chat.orderData.order_number);
                    $('#vendor_name').html(Chat.orderData.vendor_name);
                    $('#order_vendor_price').html(NumberFormatHelper.formatPrice(Chat.orderData.payable_amount));
                }
                
            }
             
        })
        .catch(e => {
            Swal.fire(
                'Something went wrong, try again later!',                                    
                'error'
            )
        })
    }

    async function getALLchat(roomId,roomIdText){
        var html='';
        toggleClass(roomId);
        axios.get(`${SocketConstants.Socket_url}/api/chat/${roomId}`)
        .then(async response => {
            console.log(response);
            if(response.status == 200) {
                if(response.data.length > 0) {
                   await response.data.forEach(function (data) {
                    var className= 'left-message';
                    var flex = '';
                    var cdate = new Date(data.created_date);
                    //cdate.toDateString();
                    //moment(1382086394000).format("DD-MM-YYYY h:mm:ss");

                    if( Auth.auth_id == data.auth_user_id && data.from_message == "from_vendor") {
                         className= 'right-message';
                         //flex = '<div style="flex: 110%;"></div>';
                    }
                    // <div class="conversation-name text-left text-primary mr-4" style="font-weight: 600;">${data.email}</div>
                    // <p class="chat-time m-0 p-0" >
                    //                             <svg width="12" height="12" class="prefix__MuiSvgIcon-root prefix__jss80 prefix__MuiSvgIcon-fontSizeLarge" viewBox="0 0 24 24" aria-hidden="true">
                    //                                 <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"></path>
                    //                                 <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path>
                    //                             </svg> ${data.created_date}</p>

                        html+= `<div class=" ${className}">
                                ${flex}
                                <div class="mb-4">
                                    <div class="conversation-list d-inline-block px-3 py-2" style="border-radius: 12px;">
                                        <div class="ctext-wrap">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="conversation-name text-left text-primary mr-4" style="font-weight: 600;">${data.username} (${data.user_type})</div>
                                                <p class="chat-time m-0 p-0" >
                                                <svg width="12" height="12" class="prefix__MuiSvgIcon-root prefix__jss80 prefix__MuiSvgIcon-fontSizeLarge" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"></path>
                                                    <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path>
                                                </svg> ${ convertDateTime(cdate)}</p>
                                            </div>
                                            
                                            <p class="text-left">${data.message}</p>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                    });
                    $('.user_data').attr('id',`right_room_${roomId}`);
                    $('#rightChat').show();
                    $('#chatHistory').addClass('room_'+roomId);
                    $('.join_room').attr('data-id',roomId);
                    $('.send_message').attr('data-id',roomId);
                    await $('#chatHistory').html(html);
                    scrollDown();
                    await getAllUser(room_id,roomIdText);
                    
                } else {
                    $('#chatHistory').html(``);
                    $('.join_room').attr('data-id',roomId);
                    $('.user_data').attr('id',`right_room_${roomId}`);
                    $('.send_message').attr('data-id',roomId);
                    $('#rightChat').show();
                    $('#chatHistory').addClass('room_'+roomId);
                }

             } else {
                $('.user_data').attr('id',``);
                $('#chatHistory').html(``);
             }
        })
        .catch(e => {
        
        })
    }

    async function getAllUser(roomId,roomIdText,notify=0,message=''){
        var html='';
        var html2='';
        axios.get(`${SocketConstants.Socket_url}/api/chat/getRoomUser/${roomId}`)
        .then(async response => {
            console.log(response);
            if(response.status == 200) {
                if(response.data.userData.length > 0) {
                    html2+= `<p class="orderNumber m-0 mb-2">#${roomIdText}</p>`;
                    await response.data.userData.forEach(function (data) {
                        html+= `<div class="alPhoneNumberDetails">
                                <ul class="p-0 m-0 d-lg-flex align-items-center text-lg-left text-center">
                                    <li class="mr-xl-2"><img class="rounded-circle userImg" src="${data.display_image}"></li>
                                    <li><span class="alUserName">${data.username}  (${data.user_type}) </span><p class="m-0 alPhoneNumber">${data.phone_num}</p></li>
                                </ul>
                            </div>`;
                            html2+=   `<a class="user_data_left" href="javascript:void(0)">
                                <img class="rounded-circle userImg" src="${data.display_image}">
                            </a>`;

                        });
                        if(notify) {
                            sendNotification(response.data.userData,message,roomId,roomIdText)
                        }
                    // await response.data.userData.forEach(function (data) {
                        
                     
   
                    //    });
                 $(`#right_room_${roomId}`).html(html);
                 $(`#room_${roomId}`).find('.user_show').html(html2)
                } else {
                    
                    $(`#right_room_${roomId}`).html('');
                 $(`#room_${roomId}`).find('.user_show').html('');

                }

             } else {
                $(`#right_room_${roomId}`).html('');
                $(`#room_${roomId}`).find('.user_show').html('');

             }
        })
        .catch(e => {
            $(`#right_room_${roomId}`).html('');
            $(`#room_${roomId}`).find('.user_data').html('');
        })
    }




    async function newMessage(message){
        console.log(message);
        var data = message.message.chatData;
        var roomData = message.message.roomData;
        if(data.message ==  undefined || data.message ==  'undefined'){
            return;
        }
        var html='';
        var className= 'left-message';
        var flex = '';
        var cdate = new Date(data.created_date);
        if( Auth.auth_id == data.auth_user_id && data.from_message == "from_vendor") {
             className= 'right-message';
            //  flex = '<div style="flex: 110%;"></div>';
        }
        var updateDate =  roomData?.updated_date;
            html = `<div class=" ${className}">
                ${flex}
                <div class="mb-4">
                    <div class="conversation-list d-inline-block px-3 py-2" style="border-radius: 12px;">
                        <div class="ctext-wrap">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="conversation-name text-left text-primary mr-4" style="font-weight: 600;">${data.username} (${data.user_type})</div>
                                <p class="chat-time m-0 p-0" >
                                <svg width="12" height="12" class="prefix__MuiSvgIcon-root prefix__jss80 prefix__MuiSvgIcon-fontSizeLarge" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"></path>
                                    <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path>
                                </svg> ${ convertDateTime(cdate)}</p>
                            </div>
                            
                            <p class="text-left">${data.message}</p>
                            
                        </div>
                    </div>
                </div>
            </div>`;
        
            await $('.room_'+data.room).append(html);
            await $('#preview_message_'+data.room).addClass('newMessage');
            await $('#preview_message_'+data.room).html(`${data.message}`);
            await $('#preview_message_name_'+data.room).html(data.username);
            await $('#preview_message_time_'+data.room).html(convertDateTime(cdate));
            await $('#chatRooms_'+data.room).attr('data-timestamp',convertDateTimeStamp(updateDate));
            var length =  $('.chatRoomsDivs').first().attr('data-sort');
            await $('#chatRooms_'+data.room).attr('data-sort',parseInt(length)+parseInt(1));
            
            sortChatBox();
            scrollDown();
            await getAllUser(roomData._id,roomData.room_id);

                  
    }

    

    function scrollDown(){
        var messageBody = document.querySelector('.chatitem');
        messageBody.scrollTop = messageBody.scrollHeight - messageBody.clientHeight;
    }


    async function JoinRoom(room_id) {
        axios.post(`/client/chat/joinChatRoom`, {
            sub_domain: window.location.origin,
            client_id:  1,
            db_name:Auth.database_name,
            user_id:  Auth.auth_id,   
            type:'vendor_to_user',
            room_id:room_id
            //vendor_order_id:vendor_order_id,
            //vendor_id:vendor_id,
            //order_id:order_id      
        })
        .then(async response => {
             console.log(response.data.status);
             if(response.data.status) {
                socket.emit('save-message', { room: room_id, nickname: 'test', message: 'Join this room', created_date: new Date() });
             }
             
        })
        .catch(e => {
            Swal.fire(
                'Something went wrong, try again later!',                                    
                'error'
            )
        })
    
    }

    // function sendMessage(message,room_id){

    //     axios.post(`/client/chat/sendMessage`, {
    //         sub_domain: window.location.origin,
    //         client_id:  1,
    //         db_name:Auth.database_name,
    //         user_id:  Auth.auth_id,   
    //         message:message,
    //         room_id:room_id ,
    //         chattype:'vendor_to_user',
    //         from:'vendor',
    //     })
    //     .then(async response => {
    //          console.log(response.data.status);
    //          if(response.data.status) {
    //             socket.emit('save-message', response.data)
    //             $('#message_box').val('');
    //          }
             
    //     })
    //     .catch(e => {
    //         Swal.fire(
    //             'Something went wrong, try again later!',                                    
    //             'error'
    //         )
    //     })
          
    // }


    function sendMessage(message,room_id,roomIdText){
        // if($data['from'] == 'vendor') {
        //     $messageData = $this->sendSocketMessage($data,$user,'to_user','vendor','from_vendor','vendor_to_user');
        // } else {
        //     $messageData = $this->sendSocketMessage($data,$user,'to_vendor','user','from_user','vendor_to_user');
        // }
        var authDataParseData = JSON.parse(authData);
        var dImage = authDataParseData.image.image_fit+'500/500'+authDataParseData.image.image_path;
        axios.post(`${SocketConstants.Socket_url}/api/chat/sendMessageJoin`, {
            'room_id' : room_id,
            'message': message,
            'user_type': 'vendor',
            'to_message': 'to_user',
            'from_message': 'from_vendor',
            'user_id': Auth.auth_id,
            'email': authDataParseData.email,
            'username': authDataParseData.name,
            'phone_num': '+'+authDataParseData.dial_code+ ' ' +authDataParseData.phone_number,
            'display_image': dImage,
            'sub_domain' : window.location.host,
            //'room_name' =>$data->name,
            'chat_type': 'vendor_to_user',
        })
        .then(async response => {
             console.log(response.data.status);
             if(response.data.status) {
                //if($('#chatHistory >  div').length == 0){
                var notify = 1;
                await getAllUser(room_id,roomIdText,notify,message);

               // }
                socket.emit('save-message', response.data)
                $('#message_box').val('');
             }
             
        })
        .catch(e => {
            Swal.fire(
                'Something went wrong, try again later!',                                    
                'error'
            )
        })
          
    }




    jQuery.fn.sortBy = function() {  
        var selectors = arguments;
    
        this.sort(function(a, b) {
            // run through each selector, and return first non-zero match
            for(var i = 0; i < selectors.length; i++) {
                var selector = selectors[i];
    
                var first = $(selector, a).text();
                var second = $(selector, b).text();
    
    
                var isNumeric = Number(first) && Number(second);
                if(isNumeric) {
                    var diff = first - second;
                    if(diff != 0) {
                        return diff;
                    }
                }
                else if(first != second) {
                    return first < second ? -1 : 1;
                }
            }
    
            return 0;
        });
    
        this.appendTo(this.parent());
    
        return this;
    };



    async function sortChatBox(){
      
        var $wrap = $('.sortDiv');
        $wrap.find('.chatRoomsDivs').sort(function(a, b) 
        {
            return +b.dataset.sort -
                +a.dataset.sort;
        })
        .appendTo($wrap);

    }


    function search() {
        var input, filter, ul, li, a, i, txtValue;
        input = document.getElementById("myInput");
        filter = input.value.toUpperCase();
        ul = document.getElementById("myUL");
        li = ul.getElementsByTagName("li");
        for (i = 0; i < li.length; i++) {
            a = li[i].getElementsByTagName("a")[0];
            txtValue = a.textContent || a.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                li[i].style.display = "";
            } else {
                li[i].style.display = "none";
            }
        }
    }


    async function newChatGroup(message){
       
        //var data = message.message.chatData;
        //console.log('lp',message);
         var roomData = message.roomData[0];
        // console.log(roomData);
        if(roomData ==  undefined || roomData ==  'undefined'){
            return;
        }
        if(roomData[0].order_user_id != auth && roomData[0].type != 'vendor_to_user') {
            return;
        }
        var html='';
        console.log('dd',message.message.roomData);
        var updateDate =  new Date(roomData.updated_date);
        html = `<div id="chatRooms_${roomData._id}" data-text="${roomData.room_id}" data-sort="" data-timestamp="" class="list-group rounded-0 chatRoomsDivs">
                    <div id="room_${roomData._id}"  data-OrderID="${roomData.order_id}" data-OrdervendorID="${roomData.order_vendor_id}" data-id="${roomData._id}" data-roomID="${roomData.room_id}" data-roomName="${roomData.room_name}" class="chat-list-item d-flex align-items-start rounded fetchChat">
            
                    <div class="align-self-center col-md-3">
                        <div class="user_show">
                            <p class="orderNumber m-0 mb-2">#${roomData.room_id}</p>                        
                        </div>
                    </div>
                    <div class="col-md-9 position-relative">
                        <div class="alNameTime last_message">
                            <h6 id="preview_message_name_${roomData._id}" class="mb-1 mt-0">Sales Demo</h6>
                            <span id="preview_message_time_${roomData._id}">
                            ${convertDateTime(updateDate)}
                            </span>
                        </div>
                        <p id="preview_message_${roomData._id}" class="orderChatMessage mb-0">${last_message} </p>
                    </div>
                </div>
            </div>	`;
        //console.log(document.getElementById(`chatRooms_${roomData._id}`));
        if(document.getElementById(`chatRooms_${roomData._id}`) === null) {
            //alert();
            $('.sortDiv').prepend(html);
        }

        
    }

    async function renderUser(data){
        var html2='';
        html2+= `<p class="orderNumber m-0 mb-2">#${data.room_id}</p>`;
        if(data.user_Data.length > 0) {
            
             await data.user_Data.forEach(function (data) {
                html2+=   `<a class="user_data_left" href="javascript:void(0)">
                <img class="rounded-circle userImg" src="${data.display_image}">
                </a>`;
            });
            return html2; 
        }
        //console.log(html2);
        return html2; 
        
    }

    async function fetchChatGroups(client_data){
         //console.log(client_data);
         var client_data = JSON.parse(client_data);
         if(client_data == undefined && client_data == 'undefined'){
            return;
         }
        // console.log(client_data.vendor_id);
        // console.log("client_data");

        var html='';
         axios.post(`${SocketConstants.Socket_url}/api/room/fetchRoomByVendor`, {
            sub_domain: window.location.host,
            type:'vendor_to_user',
            db_name:Auth.database_name,
            vendor_id:client_data.vendor_id,
            client_id: client_data.id,
        })
        .then(async response => {
            console.log(response);
            if(response.status == 200) {
                if(response.data.roomData.length > 0) {
                    await response.data.roomData.reverse().forEach(async function (data,i) {
                    console.log(data.updated_date);
                    var renderUserd = await renderUser(data);
                    var last_message =  data.chat_Data[0]!=undefined?data.chat_Data[0].message:'';
                    var last_message_name = data.chat_Data[0]!=undefined?data.chat_Data[0].username : '';
                    var updateDate =  new Date(data.updated_date);
                    html = `<div id="chatRooms_${data._id}" data-text="${data.room_id}" data-sort="${i}" data-timestamp="" class="list-group rounded-0 chatRoomsDivs">
                        <div id="room_${data._id}" data-orderid="${data.order_id}" data-ordervendorid="${data.order_vendor_id}" data-id="${data._id}" data-roomid="${data.room_id}" data-roomname="${data.room_id}" class="chat-list-item row fetchChat">
                            <div class="align-self-center col-4 test">
                                <div class="user_show">
                                ${renderUserd}
                                </div>
                            </div>
                            <div class="col-8 position-relative pl-0">
                                <div class="alNameTime last_message">
                                    <h6 id="preview_message_name_${data._id}" class="mb-1 mt-0">${last_message_name}</h6>
                                    <span id="preview_message_time_${data._id}">
                                    ${convertDateTime(updateDate)}
                                    </span>
                                </div>
                                <p id="preview_message_${data._id}" class="orderChatMessage mb-0">${last_message} </p>
                            </div>
                        </div>
                        </div>`;
                        //if(document.getElementById(`chatRooms_${roomData._id}`) === null) {
                            //alert();
                          //console.log(html);
                          await  $('.sortDiv').prepend(html);
                          
                        //}
                    });
                 
                    
                } else {
                    $('.sortDiv').html('')
                }

             } else {
                $('.sortDiv').html('')
             }
        })
        .catch(e => {
            $('.sortDiv').html('')
        })
        

    }
    function sendNotification(user_ids,message,roomId,roomIdText){
        axios.post(`/common/chat/sendNotificationToUser`, {
            user_ids: user_ids,
            text_message:message,
            roomId:roomId,
            roomIdText:roomIdText
        })
        .then(async response => {
        })
        .catch(e => {
            
        })
    }


    function toggleClass(id){
        $('.chatRoomsDivs').removeClass('active');
        $('#chatRooms_'+id).addClass('active');
        $('#preview_message_'+id).removeClass('newMessage');
    }