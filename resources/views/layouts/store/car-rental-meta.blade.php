<meta charset="utf-8" /> 
<title>{{ucfirst($title) ?? ' '}} | <?= $client_head ? ucfirst($client_head->company_name) : 'Royo' ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<meta name="_token" content="{{ csrf_token() }}">
<!-- Page tags -->
<meta name="title" content="{{$meta_title ?? ''}}">
<meta name="keywords" content="{{$meta_keyword ?? 'Royoorders'}}">
<meta name="description" content="{{$meta_description ?? ''}}">

<meta name="author" content="Royoorders">
<link rel="shortcut icon" href="<?= $favicon ?>">