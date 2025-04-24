

<html>

<head>
  <title>{{__('Cybersource Payment Flow')}}</title>
</head>
<?php
    foreach($postData as $name => $value) {
        $params[$name] = $value;
    }
?>
<body>
      
<form id="cyber_source" name="cyber_source" action="{{$url}}" method="post">

@foreach($params as $name => $value) 
        <input type="hidden" id= "{{$name}}" name="{{$name}}" value="{{$value}}">
    @endforeach
</form>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        document.getElementById("cyber_source").submit();
    });
    </script>
</body>
</html>
