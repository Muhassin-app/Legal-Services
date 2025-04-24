<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>{{__('Verify Mail')}}</title>
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
            padding: 0;
            max-width: 560px;
            margin: 0 auto;
            border-radius: 4px;
            background-repeat: repeat;
            width: 700px;
         }
         table {
            border-collapse: separate;
            text-indent: initial;
            border-spacing: 0;
            text-align: left;
         }
         table th,table td{
            padding: 10px 15px;
         }
         ul {
            margin: 0;padding: 0;
         }
         ul li{
            list-style: none;
         }
      </style>
   </head>
   <body>
      <section class="wrapper">
         <div class="container" style="background: #fff;border-radius: 10px;">
            <table style="width: 100%;">
              <thead>
                 <tr>
                    <th style="text-align: center;">
                        <a style="display: block;" href="#">
                           <img src="{{ (isset($mailData['logo'])) ? $mailData['logo'] : $logo }}" height="50px" alt="">
                        </a>
                    </th>
                 </tr>
              </thead>
              {!! (isset($mailData['email_template_content'])) ?  $mailData['email_template_content'] : $email_template_content !!}
            </table>
         </div>
      </section>
   </body>
</html>