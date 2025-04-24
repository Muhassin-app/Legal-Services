<html>

<head>
  <title>{{__('My Payment Flow')}}</title>
  <!-- link to the Square web payment SDK library -->
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/font-awesome.min.css')}}">
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/custom.css')}}">
    <style>
        .spinner-overlay .page-spinner .circle-border {
            background: linear-gradient(0deg, rgba(0, 0, 0, 0.5) 33%, rgba(255, 255, 255, 1) 100%);
        }
        @keyframes spin {
            from {
                transform:rotate(0deg);
            }
            to {
                transform:rotate(360deg);
            }
        }
        .payment-top-haeder{
            background: {{getClientPreferenceDetail()->web_color}}; 
        }
        button {
            background-color: {{getClientPreferenceDetail()->web_color}} ;
        }
    </style>
    <style type="text/css">
    .button {cursor: pointer;font-weight: 500;left: 3px;line-height: inherit;position: relative;text-decoration: none;text-align: center;border-style: solid;border-width: 1px;border-radius: 3px;-webkit-appearance: none;-moz-appearance: none;display: inline-block;}
    .button--small {padding: 10px 20px;font-size: 0.875rem;}
    .button--green {outline: none;background-color: #64d18a;border-color: #64d18a;color: white;transition: all 200ms ease;}
    .button--green:hover {background-color: #8bdda8;color: white;}
    </style>
</head>

<body>
	

  <!-- includes the Braintree JS client SDK -->
  <script src="http://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js"></script>


  <script type="text/javascript">
  	$(document).ready(function() {
      var encoded_url;
	    var originalString = '{!! $data !!}';
	    console.log('Original String: ' + originalString);
	    var key = '{{$key}}';
	    console.log('key: ' + key);
	    var parsed_key = CryptoJS.enc.Hex.parse(key);
	    var byte_IV = CryptoJS.lib.WordArray.random(16);
	    var encrypted_string = CryptoJS.AES.encrypt(CryptoJS.enc.Utf8.parse(originalString), parsed_key, {
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7,
        iv: byte_IV
      });
	    var Base64_IV = CryptoJS.enc.Base64.stringify(byte_IV);
	    encoded_url = encodeURIComponent(Base64_IV + encrypted_string);
      console.log("{{$endpoint}}"+"/WhiteLabel/"+"{{$uidd}}"+"?s="+encoded_url);
	    window.setTimeout(function(){ window.location = "{{$endpoint}}"+"/WhiteLabel/"+"{{$uidd}}"+"?s="+encoded_url; },2000);
    });
  </script>
</body>

</html>
