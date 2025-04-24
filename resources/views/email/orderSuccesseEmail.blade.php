<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Order</title>
      <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
      <style type="text/css">
         body{
            padding: 0;
            margin: 0;font-family: 'Lato', sans-serif;
            font-weight: 400;
         }
         a{
            text-decoration: none;
         }
         h1,h2,h3,h4{
            font-weight: 700;
            margin: 0;
         }
         p{
            font-size: 16px;
            line-height: 22px;
            margin: 0 0 5px;
         }
         .container {
            background: #fff;
            padding: 0 33px;
            max-width: 100%;
            margin: 0 auto;
            width: 600px;
         }
         table {
            border-collapse: separate;
            text-indent: initial;
            border-spacing: 0;
            text-align: left;
         }
         table th,table td{
            padding: 10px 30px;
            border: 0 !important;
         }
         ul {
            margin: 0;padding: 0;
         }
         ul li{
            list-style: none;
         }
         .order-total-price td {
            padding: 0 0 10px;
         }
         .payment-method th,.payment-method td{
            padding: 10px 0;
         }
      </style>
   </head>
   <body>
      <section class="wrapper">
         <div class="container" style="background: #308fe442;">
        {!! $mailData['email_template_content'] !!}
        <table class="main-bg-light text-center top-0" align="center" border="0" cellpadding="0" cellspacing="0" style="width:100%; background-color:#fff; padding: 0 15px;">
            <tr>
                <td>
                    @php
                        $currYear = \Carbon\Carbon::now()->year;
                        $prevYear = $currYear - 1;
                        $currYear = substr($currYear, -2);
                    @endphp
                    <p>&copy; {{$prevYear}}-{{$currYear}} | {{ __("All rights reserved") }}</p>

                </td>
            </tr>
        </table>
    </div>
</body>

</html>
