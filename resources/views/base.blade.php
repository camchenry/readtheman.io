<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title') | Man Pages</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto+Mono|Roboto:400,700" rel="stylesheet">
        <link href="{{ url('css/app.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>
        @yield('content')
    </body>
</html>
