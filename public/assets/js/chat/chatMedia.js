var uppy = new Uppy.Uppy()
.use(Uppy.Dashboard, {
  inline: true,
  target: '#uppy-progress',
  hidePoweredBy: true, // Remove "Powered by Uppy" message
  

})

.use(Uppy.Webcam, { target: Uppy.Dashboard })
.use(Uppy.ImageEditor, { target: Uppy.Dashboard })
.use(Uppy.ScreenCapture, { target: Uppy.Dashboard })
.use(Uppy.AwsS3, {
    // Configure your AWS S3 settings
    companionUrl: '/', // Set companionUrl to '/' since we are not using a companion server

    getUploadParameters(file) {
        return new Promise((resolve, reject) => {
        var room_id = $('.send_message').attr('data-id');
        var roomIdText = $(`#room_${room_id}`).attr('data-roomid');
        // Make a request to the Laravel endpoint to get the signed URL
        fetch('/common/s3-sign?filename=' + encodeURIComponent(`uploads/${auth}/${roomIdText}/${file.name}`))
            .then(response => response.json())
            .then(data => {
                resolve({
                    method: 'PUT', // Use the HTTP method specified by the signed URL
                    url: data.url, // Use the signed URL obtained from the Laravel endpoint
                    //thumbnail_url:data.thumbnail_url
                });
                //file.thumbnailUrl = data.thumbnail_url;
            })
            .catch(error => {
                reject('Error getting signed URL');
            });
        });
    }
})
uppy.on('complete', (result) => {
    console.log(result);
    var room_id = $('.send_message').attr('data-id');
    var roomIdText = $(`#room_${room_id}`).attr('data-roomid');

    if(!room_id ){
        return;
        
    }
        if(result.successful){
            $.each( result.successful, function( key, data ) {

                //if(status)
                
                    var media = {
                        is_media :true,
                        mediaUrl:data.response.uploadURL,
                        thumbnailUrl:data.preview,
                        mediaType:data.type,
                    }
                    //console.log(media);
                    //media.is_media = true;
                     sendMessage('',room_id,roomIdText,media)
            })
        }
    

    //console.log('Upload complete! We’ve uploaded these files:', result.successful)
  })


  function openMediaNav() {
        $('body').toggleClass('push_to_side');
        
}