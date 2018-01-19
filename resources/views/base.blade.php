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
    <body class="full-page">
        <div class="full-page-content">
            @yield('content')
        </div>

        <footer class="footer mt-3">
            <div class="container py-3">
                <p class="text-muted m-0">Made by <a href="https://camchenry.com">Cameron McHenry</a></p>
            </div>
        </footer>
    </body>
</html>
