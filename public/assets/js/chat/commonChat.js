

    //await getALLchat();
    let productIdC = '';
    let OrdervendorID = '';
    let order_id = '';
    let vendoridC = '';
    let const_img = '/assets/js/chat/profile-pic-dummy.png'
    $(document).on('click','.start_chat',async function(e){
        e.preventDefault();
        var vendor_order_id = $(this).attr('data-vendor_order_id');
        var vendor_id = $(this).attr('data-vendor_id');
        var order_id = $(this).attr('data-order_id');
        var type = $(this).attr('data-chat_type');
        
        if(type == 'userToUser'){
               product_id = $(this).attr('data-product_id');
                //startChatype ='user_to_user';
                if(!vendor_order_id && !vendor_id && !order_id){
                    return;
                }
        } else{
                if(!vendor_order_id && !vendor_id && !order_id){
                    return;
                }
        }
        
        $('#order_list_order').show();
        await startChat(vendor_order_id,vendor_id,order_id);
    });

    $(document).on('click','.fetchChat',async function(){
        var roomId = $(this).attr('data-id');
        var roomIdText = $(this).attr('data-roomid');
        var roomName = $(this).attr('data-roomName');
        var roomIDn = $(this).attr('data-roomID');
        OrdervendorID = $(this).attr('data-ordervendorid');
        order_id = $(this).attr('data-orderid');
        var chat_type = $(this).attr('data-chat_type');
        var productIdC = $(this).attr('data-product_id');
        var product_name = $(this).attr('data-product_name');
        var vendor_name = $(this).attr('data-vendor_name');
        vendoridC = $(this).attr('data-vendor_id');

        if(chat_type=="userToUser"){

            if(!roomId && !OrdervendorID ){
                $('#chatHistory').removeClass('room_'+roomId);
                return;
            }
        }else{
            if(!roomId && !OrdervendorID && !order_id){
                $('#chatHistory').removeClass('room_'+roomId);
                return;
            }
        }
        
        var authDataParseData = JSON.parse(Auth.auData);
        var email = authDataParseData.email;
        $('#roomName').html(roomIDn);
       // socket.emit('joinRoom', { email: email, roomId: roomId, message: 'Join this room', created_date: new Date() });
        await fetchOderVendorDetails(OrdervendorID,order_id, productIdC);
        if(chat_type=="userToUser"){
            await getALLchat(roomId,roomName);
            await getAllUser(roomId,roomName);
        }else{
            await getALLchat(roomId,roomIdText);
            await getAllUser(roomId,roomIdText);
        }
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
   
    async function startChat(vendor_order_id,vendoridC,order_id){
      


        axios.post(`/${apiPre}/chat/startChat`, {
            sub_domain: window.location.origin,
            client_id:  1,
            db_name:Auth.database_name,
            user_id:  Auth.auth_id,   
            type:startChatype,
            vendor_order_id:vendor_order_id,
            vendor_id:vendoridC,
            order_id:order_id,
            product_id:product_id     
        })
        .then(async response => {
          
             $('#order_list_order').hide();
             if(response.data.status === true) {
                var data = response.data;
               
                window.location.href = `/${rePre}/${data.roomData._id}`;
                
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
    async function fetchOderVendorDetails(order_vendor_id,order_id, product_id = ''){
       await axios.post(`/${apiPre}/chat/fetchOrderDetail`, {
            order_vendor_id: order_vendor_id,
            order_id:order_id,
            product_id:product_id
        })
        .then(async response => {
         
            if(response.data.status) {
                if(response.data.orderData != undefined) {
                    //console.log('hohohjo',response.data.orderData);
                    var data = response.data.orderData;
                    

                    if(product_id!=''){
                        Chat.orderData.order_number  =  (data.title != undefined ) ? data.title : '';
                        Chat.orderData.payable_amount = (data.variant[0].price != undefined ) ? data.variant[0].price : '';
                        Chat.orderData.vendor_name = (data.vendor.name != undefined ) ? data.vendor.name : '';
                    }else{
                        Chat.orderData.order_number  =  (data.order_number != undefined ) ? data.order_number : '';
                        Chat.orderData.payable_amount = (data.vendors[0].payable_amount != undefined ) ? data.vendors[0].payable_amount : '';
                        Chat.orderData.vendor_name = (data.vendors[0].vendor.name != undefined ) ? data.vendors[0].vendor.name : '';
                    }
                    
                    $('#order_num').html(Chat.orderData.order_number);
                    $('#vendor_name').html(Chat.orderData.vendor_name);
                    $('#order_vendor_price').html(NumberFormatHelper.formatPrice(Chat.orderData.payable_amount));
                }
                
            }
             
        })
        .catch(e => {
            console.log(e);
            // Swal.fire(
            //     'Something went wrong, try again later!',                                    
            //     'error'
            // )
        })
    }

    async function getALLchat(roomId,roomIdText,OrdervendorID,order_id){
        var html='';
        toggleClass(roomId);
        axios.get(`${SocketConstants.Socket_url}/api/chat/${roomId}`)
        .then(async response => {
            //console.log(response);
            if(response.status == 200) {
                if(response.data.length > 0) {
                   await response.data.forEach(async function (data) {
                    var className= 'left-message';
                    var flex = '';
                    var cdate = new Date(data.created_date);
                    //cdate.toDateString();
                    //moment(1382086394000).format("DD-MM-YYYY h:mm:ss");

                    if( Auth.auth_id == data.auth_user_id && data.from_message == from_message) {
                         className= 'right-message';
                         //flex = '<div style="flex: 110%;"></div>';
                    }
                    // <div class="conversation-name text-left text-primary mr-4" style="font-weight: 600;">${data.email}</div>
                    // <p class="chat-time m-0 p-0" >
                    //                             <svg width="12" height="12" class="prefix__MuiSvgIcon-root prefix__jss80 prefix__MuiSvgIcon-fontSizeLarge" viewBox="0 0 24 24" aria-hidden="true">
                    //                                 <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"></path>
                    //                                 <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path>
                    //                             </svg> ${data.created_date}</p>
                    var mesHtml = await  appendMessage(data);

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
                                            
                                                ${mesHtml}                                            
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
                    await getAllUser(roomId,roomIdText);
                    
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
        console.log(e);
        })
    }

    async function getAllUser(roomId,roomIdText,notify=0,message=''){
        var html='';
        var html2='';
        axios.get(`${SocketConstants.Socket_url}/api/chat/getRoomUser/${roomId}`)
        .then(async response => {
            //console.log(response);
            if(response.status == 200) {
                if(startChatype == 'user_to_user'){
                   
                }else{
                    html2+= `<p class="orderNumber m-0 mb-2">#${roomIdText}</p>`;
                }
                
                if(response.data.userData.length > 0) {
                   
                    await response.data.userData.forEach(function (data) {
                     html+= `<div class="alPhoneNumberDetails">
                            <ul class="p-0 m-0 d-lg-flex align-items-center text-lg-left text-center">
                                <li class="mr-xl-2"><img class="rounded-circle userImg" onError="this.onerror=null;this.src='${const_img}';" src="${data.display_image}"></li>
                                <li><span class="alUserName">${data.username}  (${data.user_type}) </span><p class="m-0 alPhoneNumber">${data.phone_num}</p></li>
                            </ul>
                        </div>`;
                      
                        html2+=   `<a class="user_data_left" href="javascript:void(0)">
                            <img class="rounded-circle userImg" onError="this.onerror=null;this.src='${const_img}';" src="${data.display_image}">
                        </a>`;

                    });
                    if(notify) {
                        sendNotification(response.data.userData,message,roomId,roomIdText,OrdervendorID,order_id,vendoridC)
                    }
                   
                    // await response.data.userData.forEach(function (data) {
                        
                       
   
                    //    });
                 $(`#right_room_${roomId}`).html(html);
                 $(`#room_${roomId}`).find('.user_show').html(html2)
                } else {
                    $(`#right_room_${roomId}`).html('');
                   $(`#room_${roomId}`).find('.user_show').html(html2);

                }

             } else {
                $(`#right_room_${roomId}`).html('');
                $(`#room_${roomId}`).find('.user_show').html('');

             }
        })
        .catch(e => {
            console.log(e);
            $(`#right_room_${roomId}`).html('');
            $(`#room_${roomId}`).find('.user_data').html('');
        })
    }




    async function newMessage(message){
        var data = message.message.chatData;
        var roomData = message.message.roomData;
        if(data.message ==  undefined || data.message ==  'undefined'){
            return;
        }

        if(roomData.type == 'user_to_user' && Auth.vendor_id == roomData.vendor_id) {
            return;
        }
        var html='';
        var className= 'left-message';
        var flex = '';
        var cdate = new Date(data.created_date);
        if( Auth.auth_id == data.auth_user_id && data.from_message == from_message) {
             className= 'right-message';
            //  flex = '<div style="flex: 110%;"></div>';
        }
        //console.log(data);
        var updateDate =  roomData?.updated_date;
        var mesHtml = await  appendMessage(data);
       
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
                            
                           
                            ${mesHtml}
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
        axios.post(`/${apiPre}/chat/joinChatRoom`, {
            sub_domain: window.location.origin,
            client_id:  1,
            db_name:Auth.database_name,
            user_id:  Auth.auth_id,   
            type:'agent_to_user',
            room_id:room_id
            //vendor_order_id:vendor_order_id,
            //vendor_id:vendor_id,
            //order_id:order_id      
        })
        .then(async response => {
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


    function sendMessage(message,room_id,roomIdText, media = {}){
        // if($data['from'] == 'vendor') {
        //     $messageData = $this->sendSocketMessage($data,$user,'to_user','vendor','from_vendor','vendor_to_user');
        // } else {
        //     $messageData = $this->sendSocketMessage($data,$user,'to_vendor','user','from_user','vendor_to_user');
        // }
        var authDataParseData = JSON.parse(Auth.auData);
        var dImage = authDataParseData.image.image_fit+'500/500'+authDataParseData.image.image_path;
        axios.post(`${SocketConstants.Socket_url}/api/chat/sendMessageJoin`, {
            'room_id' : room_id,
            'message': message,
            'user_type': 'User',
            'to_message': to_message,
            'from_message': from_message,
            'user_id': Auth.auth_id,
            'email': authDataParseData.email,
            'username': authDataParseData.name,
            'phone_num': '+'+authDataParseData.dial_code+ ' ' +authDataParseData.phone_number,
            'display_image': dImage,
            'sub_domain' : window.location.host,
            'is_media':media.is_media,
            'mediaUrl':media.mediaUrl,
            'thumbnailUrl':media.thumbnailUrl,
            'mediaType':media.mediaType,
            //'room_name' =>$data->name,
            'chat_type': chat_type,
        })
        .then(async response => {
             //console.log(response.data.status);
             if(response.data.status) {
                var notify = 1;
               
                await getAllUser(room_id,roomIdText,notify,message);
                //if($('#chatHistory >  div').length == 0){
                   // await getAllUser(room_id,roomIdText);

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
       //alert();
        //var data = message.message.chatData;
        //console.log('lp',message);
         var roomData = message.roomData[0];
         //console.log(roomData);
        if(roomData ==  undefined || roomData ==  'undefined'){
            return;
        }
        if(roomData.type != chat_type) {
            return;
        } 
        if(roomData.db_name != Auth.database_name) {
            return;
        }

        if(roomData.type == 'user_to_user' && Auth.vendor_id == roomData.vendor_id) {
            return;
        }
        var html='';
        //console.log('dd',message.message.roomData);
        var last_message = roomData.chat_Data[0].message??roomData.chat_Data[0].message;
        var updateDate =  new Date(roomData.updated_date);
        if(roomData.type == 'user_to_user'){
            html = `<div id="chatRooms_${roomData._id}" data-text="${roomData.room_id}" data-sort="" data-timestamp="" class="list-group rounded-0 chatRoomsDivs">
            <div id="room_${roomData._id}"  data-OrderID="${roomData.order_id}" data-OrdervendorID="${roomData.order_vendor_id}" data-id="${roomData._id}" data-roomID="${roomData.room_id}" data-roomName="${roomData.product_name}" class="chat-list-item d-flex align-items-start rounded fetchChat">`;
        }else{
            html = `<div id="chatRooms_${roomData._id}" data-text="${roomData.room_id}" data-sort="" data-timestamp="" class="list-group rounded-0 chatRoomsDivs">
            <div id="room_${roomData._id}"  data-OrderID="${roomData.order_id}" data-OrdervendorID="${roomData.order_vendor_id}" data-id="${roomData._id}" data-roomID="${roomData.room_id}" data-roomName="${roomData.room_name}" class="chat-list-item d-flex align-items-start rounded fetchChat">`;
        }
        html += `<div id="chatRooms_${roomData._id}" data-text="${roomData.room_id}" data-sort="" data-timestamp="" class="list-group rounded-0 chatRoomsDivs">
                    <div id="room_${roomData._id}"  data-OrderID="${roomData.order_id}" data-OrdervendorID="${roomData.order_vendor_id}" data-id="${roomData._id}" data-roomID="${roomData.room_id}" data-roomName="${roomData.room_name}" class="chat-list-item d-flex align-items-start rounded fetchChat">
            
                    <div class="align-self-center col-md-3">
                        <div class="user_show">
                            <p class="orderNumber m-0 mb-2">#${roomData.room_id}</p>                        
                        </div>
                    </div>
                    <div class="col-md-9 position-relative">
                        <div class="alNameTime last_message">
                         <h6 id="preview_message_name_${roomData._id}" class="mb-1 mt-0"></h6>
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
        if(fetchDe=='fetchRoomByUserIdUserToUser'){
           
        }else{
            html2+= `<p class="orderNumber m-0 mb-2">#${data.room_id}</p>`;
        }
        
        if(data.user_Data.length > 0) {
            await data.user_Data.forEach(function (data) {
                html2+=   `<a class="user_data_left" href="javascript:void(0)">
                    <img class="rounded-circle userImg" onError="this.onerror=null;this.src='${const_img}';" src="${data.display_image}">
                </a>`;
            });
            return html2; 
        }
        //console.log(html2);
        return html2; 
        
    }

     async function fetchChatGroups(client_data){
        var client_data = JSON.parse(client_data);
        var chatType = '';
         if(client_data == undefined && client_data == 'undefined'){
            return;
         }
         var apiurl = 'fetchAllRoom';
         if(fetchDe!=undefined){
            apiurl = fetchDe;
         }
         var p2p_id = '';
         if(fetchDe=='fetchRoomByUserIdUserToUser'){
            p2p_id = client_data.vendor_id;
         }
        var html='';
         axios.post(`${SocketConstants.Socket_url}/api/room/${apiurl}`, {
            sub_domain: window.location.host,
            type:chat_type,
            db_name:Auth.database_name,
            vendor_id:client_data.vendor_id,
            order_user_id:auth,
            client_id: client_data.id,
            p2p_id: p2p_id
        })
        .then(async response => {
            let pathSegments = window.location.pathname.split('/');
            let chatId = pathSegments[pathSegments.length - 1]; // Extract chat ID
            if(response.status == 200) {
                if(response.data.roomData.length > 0) {
                    var productSelected = '';
                      // Filter roomData if the URL contains "order_id"
                      let filteredData = isNaN(chatId) ? response.data.roomData : response.data.roomData.filter(data => data.order_id == chatId);
                    filteredData.reverse().forEach(async function (data, i) {
                        // if(data._id == user_room_id){
                        //     productSelected = data.product_id;
                        // }
                   
                    var renderUserd = await renderUser(data);
                    var last_message_name = data.chat_Data[0]!=undefined?data.chat_Data[0].username : '';
                    // if(fetchDe=='fetchRoomByUserIdUserToUser'){
                    //     last_message_name = data.vendor_name!=undefined?(data.vendor_name + ' (' +   data.product_name +')' ): '';
                    //     chatType= 'userToUser';
                    // }

                    if(data.p2p_id!= undefined && data.p2p_id!=''){
                        last_message_name = data.vendor_name!=undefined?(data.vendor_name + ' (' +   data.product_name +')' ): '';
                        chatType= 'userToUser';
                    }
                    
                    var last_message =  data.chat_Data[0]!=undefined?data.chat_Data[0].message:'';
                    var updateDate =  new Date(data.updated_date);
                    html = `<div id="chatRooms_${data._id}" data-text="${data.room_id}" data-chat_type="${chatType}" data-product_id="${data.product_id!=undefined?data.product_id:''}"  data-sort="${i}" data-timestamp="" class="list-group rounded-0 chatRoomsDivs">
                        <div id="room_${data._id}" data-orderid="${data.order_id}" data-chat_type="${chatType}" data-ordervendorid="${data.order_vendor_id}" data-vendor_id="${data.vendor_id}" data-product_id="${data.product_id!=undefined?data.product_id:''}"  data-id="${data._id}" data-roomid="${data.room_id}" data-roomname="${data.room_id}" class="chat-list-item row fetchChat">
                            <div class="align-self-center col-3">
                                <div class="user_show">
                                ${renderUserd}
                                </div>
                            </div>
                            <div class="col-9 position-relative pl-0">
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
                    // if(user_room_id){
                    //     await fetchOderVendorDetails('','', productSelected);
                    //     await getALLchat(user_room_id,user_room_id);
                    //     await getAllUser(user_room_id,user_room_id);
                    // }
                    
                    
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

    function sendNotification(user_ids,message,roomId,roomIdText,OrdervendorID='',order_id='',vendoridC=''){
        axios.post(`/common/chat/sendNotificationToUser`, {
            user_ids: user_ids,
            text_message:message,
            roomId:roomId,
            roomIdText:roomIdText,
            auth_id:Auth.auth_id,
            order_vendor_id:OrdervendorID,
            order_id:order_id,
            vendor_id:vendoridC,
            web:'true'
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

    async function appendMessage(data){
        //return new Promise((resolve, reject) => {
            var mesHtml =  `<p class="text-left">${data.message}</p>`;

            if(data.is_media == true){
                var media_html = `<iframe src="${data.mediaUrl}" />`;
                if(data.mediaType.includes('video')) {
                    media_html = `<video preload="none" width="320" height="240" controls>
                                    <source autostart="false" src="${data.mediaUrl}" >
                                </video>"`;
                } else if(data.mediaType.includes('image')) {
                    media_html = `<img src="${data.mediaUrl}" alt="Royo" width="200" height="200">`;
                }  

                mesHtml = `<p class="text-left">${media_html}</p>`;
            }
            return mesHtml;
        //})
    }

    //fetchChatGroups();