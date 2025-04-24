
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}" defer></script>
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">


        <form action="{{$url}}" method="POST" target="_blank">
            <input name="storeId" value="{{ $post_data->storeId }}" hidden = "true"/>
            <input name="amount" value="10" hidden = "true"/>
            <input name="postBackURL" value="{{ $post_data->postBackURL }}" hidden = "true"/>
            <input name="orderRefNum" value="{{ $post_data->orderRefNum }}" hidden = "true"/>
            <button class="btn btn-primary" type="submit">
                Pay 2
              </button>
            </form>
      </div>
    </body>
</html>
