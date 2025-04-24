<html>

<head>
  <title>{{__('Payphone Payment Flow')}}</title>
</head>

<body>
    <div style="display: block;margin:15% 45%"><img src="{{asset('loader.webp')}}"></div>
      
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
 if("{{$url}}"){
        window.location.href= "{{$url}}";
    }
</script>
</body>
</html>