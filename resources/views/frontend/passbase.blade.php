<!DOCTYPE html>
<html>
  <head>
    <script type="text/javascript" src="https://unpkg.com/@passbase/button"></script> 
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- <link rel="stylesheet" href="index.css" /> -->
    <title>Verify your Identity</title> 
    <meta charset="utf-8">
     <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style type="text/css">
.al_heightBody{margin: 0 !important;}
.al_heightBody:after{position: absolute;content: "";z-index: 0;
/*background: transparent;
background: -webkit-linear-gradient(to right, transparent, #3A1C71);
background: linear-gradient(to right, transparent, #3A1C71);*/
height: 100%; width:100%;
opacity: 0.1;
top: 0;
}
p.title {
font-size: 32px;
font-weight: 600;
color: #777;
display: block;
width: 100%;
margin-top:150px;
}
.subtitle {
color: #777;
margin-bottom: 30px ;
display: block;
width: 100%;
}

.al_text_center div#passbase-button div {
text-align: center;
margin: 10px auto 0;
}
.align-items-center {
    -webkit-box-align: center!important;
    -ms-flex-align: center!important;
    align-items: center!important;
}
.d-flex {
    display: -webkit-box!important;
    display: -ms-flexbox!important;
    display: flex!important;
}
.justify-content-center {
    -webkit-box-pack: center!important;
    -ms-flex-pack: center!important;
    justify-content: center!important;
}
.ml-auto, .mx-auto {
    margin-left: auto!important;
}
.mr-auto, .mx-auto {
    margin-right: auto!important;
}
.align-self-center {
    -ms-flex-item-align: center!important;
    align-self: center!important;
}
.h-100 {
    height: 100%!important;
}
.text-center{text-align: center;}
.container {
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
    margin-right: auto;
    margin-left: auto;
}
.row {
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}
.al_text_center {
  position: relative;
  z-index: 999;
}
    </style>
  </head>

  <body class="al_heightBody">

    <img
      class="img-fluid passbase"
      src="https://passbase.com/assets/images/logo.png"
      alt="Passbase"
    />

    <div class="container h-100 ">
      <div class="row al_text_center text-center justify-content-center align-items-center">
        <p class="title w-100">Verify your identity now</p>
        <p class="subtitle w-100">You can verify your identity by clicking the verification button below.</p>

        <!-- 1. This is the Passbase Component -->
        <div id="passbase-button"></div>
      </div>
    </div>

    <script type="text/javascript">
      // This is the logic for the Passbase component
      const element = document.getElementById("passbase-button");
      // update the below variable with your own publishable API key**your, which you can find in the [API settings](https://app.passbase.com/settings/api) section.
      const apiKey = "{{$data['publish_key']}}";
      Passbase.renderButton(element, apiKey, {
        // Speed up the verification flow by providing some information you might already have like the user's email to skip the email step
        prefillAttributes: {
          email: "{{Auth::user()->email}}"
          // country: "in"
        },
        onSubmitted: (identityAccessKey) => {
          console.log('-----------------On Submit--------------------');
          console.log(identityAccessKey);

        },
        onFinish: (identityAccessKey) => {
          console.log('-----------------On Finish--------------------');
            console.log(identityAccessKey);
          // Do what you want after the flow finished
          sendAuthKeyToBackend(identityAccessKey);
          // window.location.href =("https://sales.alerthire.com");
        },
        onError: (errorCode) => {},
        onStart: () => {},
      });

      // Optional - Example to send identity access key to your backend
      const sendAuthKeyToBackend = (identityAccessKey) => {  
        $.ajax({
          url: "{{route('passbase.store')}}", 
          type: "POST",
          data: {
            identityAccessKey: identityAccessKey,
            "_token": "{{ csrf_token() }}",
          },
          success: function(response){
            console.log(response);
            if(response.status == 'Success')
            {
              console.log(response.data);
              window.location.replace("{{$data['redirect_url']}}");
            }else{
              console.log(error);
            }
          }
        });
      };
    </script>
  </body>
</html>