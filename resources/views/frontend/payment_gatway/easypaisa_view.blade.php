<html>

<head>
  <title>{{__('easypaisa Payment Flow')}}</title>
</head>

<body>
    <form action="{{$url}}" name="redirect"  id="easypaisa" method="POST" >
        <input name="storeId" value="{{ $post_data->storeId }}" hidden = "true"/>
        <input name="amount" value="{{ $post_data->amount }}" hidden = "true"/>
        <input name="postBackURL" value="{{ $post_data->postBackURL }}" hidden = "true"/>
        <input name="orderRefNum" value="{{ $post_data->orderRefNum }}" hidden = "true"/>
    </form>

    <div style="display: block;margin:15% 45%"><img src="{{asset('loader.webp')}}"></div>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
 document.getElementById("easypaisa").submit();
</script>
</body>
</html>
