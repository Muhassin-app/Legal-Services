
{{-- 
* it's use for get translation in js file
* @Author Mr amit mehra and Mr Harbans singh 
--}}
@php
$lang            = config('app.locale');
$langFile        = resource_path('langa/' . $lang . '.json');
if (!file_exists($langFile)) {
    $langFile        = resource_path('lang/en.json');
} 

$langFileString  = file_get_contents($langFile, $lang . '.json');
@endphp
@section('headerJs')

<script src="{{ asset('js/lang/langTranslation.js') }}"></script>
<script>
    var LangObjectJS = <?php  echo @$langFileString  ?>
</script>
@endsection
