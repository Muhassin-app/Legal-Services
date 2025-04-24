<html>

<head>
  <title>{{__('easypaisa confirm Payment Flow')}}</title>
</head>

<body>
    <form action="{{$url}}" name="redirect"  id="easypaisa" method="POST" >
        <input id="auth_token" name="auth_token" type="hidden" value="{{ $auth_token }}" />
        <input id="postBackURL" name="postBackURL" type="hidden" value="{{ $post_back_url }}" />
    </form>

    <div style="display: block;margin:15% 45%"><img src="{{asset('loader.webp')}}"></div>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
 document.getElementById("easypaisa").submit();
</script>
</body>
</html>
