//(async function(){
    

// var socket = new io('https://chat.royoorders.com');

// await socket.connect(); 

// // Add a connect listener

// // Add a connect listener
socket.on('new-message',function(data) {
    //newMessage(data)
    //alert();
   // notify(data,true);
    console.log('Received a message from the server!',data);
});
// // Add a disconnect listener
// socket.on('disconnect',function() {
//  console.log('The client has disconnected!');
// });

//})()




// let notificationBtn = document.querySelector("#notify")
askNotificationPermission();
// notificationBtn.addEventListener("click", askNotificationPermission);
function checkNotificationPromise() {
		try {
		Notification.requestPermission().then();
		} catch(e) {
		return false;
		}

		return true;
  	}

function askNotificationPermission() {

  function handlePermission(permission) {
    // if(Notification.permission === 'denied' || Notification.permission === 'default') {
    //   notificationBtn.style.display = 'block';
    // } else {
      //notificationBtn.style.display = 'none';
	  ///notify("Notifications are ON.", true)
    //}
  }

  if (!('Notification' in window)) {
		console.log("Uh-Oh. Your browser doesn't support it! Go to https://browsehappy.com and upgrade your browser.")
		alert("Uh-Oh. Your browser doesn't support it! Go to https://browsehappy.com and upgrade your browser.")
  } else {
    Notification.requestPermission()
      .then((permission) => {
        handlePermission(permission);
    })
  }
}

function notify (data, checker) {
    console.log(data);
	// if (checker && checkNotificationPromise()) { 
	// 	return new Notification('RoyoChat', { body: data.message.chatData.email+': '+data.message.chatData.message });
	// }
}
