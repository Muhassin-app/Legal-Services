<!DOCTYPE html>
<html>
<head>
  <title>Product Import Error Logs</title>
  <style type="text/css">
    .container{
      width: 600px;
      margin:auto;
      padding:10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="invoice-container" ref="document" id="html">
        <ul class="tooltip_error">
            <?php $error_csv = json_decode($csv->error); ?>
            @foreach ($error_csv as $err)
                <li>
                    {{ $err }}
                </li>
            @endforeach
        </ul>
    </div>
  </div>
</body>
</html>
