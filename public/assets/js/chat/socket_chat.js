//(async function(){
   
createSocketConnection();
async function createSocketConnection(){
    if(SocketConstants.Socket_url != '' && SocketConstants.Socket_url != null && SocketConstants.Socket_url != undefined) {
        socket = new io(SocketConstants.Socket_url);
        await socket.connect(); 
        console.log(socket);
        console.log(SocketConstants.Socket_url);
    }
}
  

// var socket = new io('https://chat.royoorders.com');

// await socket.connect(); 

// // Add a connect listener
socket.on('connect',async function() {
    //await getChatRooms();
    console.log('Client has connected to the server!');
});
// // Add a connect listener
socket.on('new-message',function(data) {
    console.log('Received a mesddsage from the server!',data);
    newMessage(data)
});
socket.on('new-message2',function(data) {
    console.log('Received a message from the server!',data);

});
// // Add a disconnect listener
socket.on('disconnect',function() {
 console.log('The client has disconnected!');
});


socket.on('room-created',async function(data) {

        await newChatGroup(data)
});
//})()


// io.on('connection', socket => {
//     socket.on('joinRoom', ({ username, room }) => {
//       const user = newUser(socket.id, username, room);
  
//       socket.join(user.room);
  
//       // General welcome
//       socket.emit('message', formatMessage("Socket", 'Messages are limited to this room! '));
  
//       // Broadcast everytime users connects
//       socket.broadcast
//         .to(user.room)
//         .emit(
//           'message',
//           formatMessage("Socket", `${user.username} has joined the room`)
//         );
  
//       // Current active users and room name
//       io.to(user.room).emit('roomUsers', {
//         room: user.room,
//         users: getIndividualRoomUsers(user.room)
//       });
//     });
  
//     // Listen for client message
//     socket.on('chatMessage', msg => {
//       const user = getActiveUser(socket.id);
  
//       io.to(user.room).emit('message', formatMessage(user.username, msg));
//     });
  
//     // Runs when client disconnects
//     socket.on('disconnect', () => {
//       const user = exitRoom(socket.id);
  
//       if (user) {
//         io.to(user.room).emit(
//           'message',
//           formatMessage("Socket", `${user.username} has left the room`)
//         );
  
//         // Current active users and room name
//         io.to(user.room).emit('roomUsers', {
//           room: user.room,
//           users: getIndividualRoomUsers(user.room)
//         });
//       }
//     });
//   });
  