<html>

<head>
  <title>{{__('My Payment Flow')}}</title>
</head>

<body>
 
  <form method="post" style="opacity: .02" id="kongapay" action="https://www.kongapay.com/paymentgateway">
      {!! $inputs !!}
      <input type="submit" value="submit">
    </form>
      
        <div style="display: block;margin:15% 45%"><img src="{{url('/').'/loader.webp'}}"></div>
      
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
 document.getElementById("kongapay").submit();
</script>
</body>
</html>