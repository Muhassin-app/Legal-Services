<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<script type="text/javascript" src="http://local.myorder.com/front-assets/js/jquery-3.3.1.min.js"></script>
</body>
<script>
 var form = $(document.createElement('form'));
            $(form).attr("action", "https://easypay.easypaisa.com.pk/easypay/Confirm.jsf");
            $(form).attr("method", "POST");
            
            var input1 = $("<input>")
                .attr("type", "hidden")
                .attr("name", "auth_token")
                .val("<?php echo $_GET['auth_token'] ?>" );
            var input2 = $("<input>")
                .attr("type", "hidden")
                .attr("name", "postBackURL")
                .val("http://local.myorder.com/sgsdfg" );
            
            $(form).append($(input1)).append($(input2));
            form.appendTo(document.body)
            $(form).submit();
</script>
</html>
