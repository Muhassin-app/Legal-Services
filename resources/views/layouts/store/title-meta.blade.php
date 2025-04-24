<meta charset="utf-8" /> 
<title>{{$title ?? ' '}} | <?= $client_head ? ucfirst($client_head->company_name) : 'Royo' ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<meta name="_token" content="{{ csrf_token() }}">
<!-- Page tags -->
<meta name="title" content="{{$meta_title ?? ''}}">
<meta name="keywords" content="{{$meta_keyword ?? 'Royoorders'}}">
<meta name="description" content="{{$meta_description ?? ''}}">
<!-- End page tags -->
{{--@if(isset($category))
    @if($category->translation->first())
        <meta name="description" content="{{$category->translation->first()->meta_description}}" />
        <meta name="keywords" content="{{$category->translation->first()->meta_keywords}}">
    @endif
@else
    <meta name="keywords" content="Royoorders">
@endif --}}
<meta name="author" content="Royoorders">
<link rel="shortcut icon" href="<?= $favicon ?>">
<style>
    :root {--theme-deafult: green; }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0; 
}
</style>