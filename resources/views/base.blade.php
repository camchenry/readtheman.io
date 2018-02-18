<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="keywords" content="@yield('meta.keywords', 'linux,help,man,page,man page,online,kernel,c,unix')"/>
        <meta name="description" content="@yield('meta.description', 'Online Linux Man Pages')"/>

        <title>@yield('title') | Man Pages</title>

        <!-- Fonts -->
        <link href="{{ url('css/app.css') }}" rel="stylesheet" type="text/css">
        @yield('stylesheets')

        @yield('scripts')
        <script async defer src="{{ url('js/bootstrap.bundle.min.js') }}"></script>
    </head>
    <body class="full-page">
        <nav class="navbar navbar-expand-md navbar-light bg-light">
            <a class="navbar-brand" href="{{ URL::to('/') }}">ReadTheMan</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ URL::to('/') }}">
                            Home @if(Request::is('/'))<span class="sr-only">(current)</span>@endif
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('pages') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ URL::to('/pages') }}">
                            Pages @if(Request::is('/'))<span class="sr-only">(current)</span>@endif
                        </a>
                    </li>
                </ul>
                {{--
                <form class="form-inline my-2 my-lg-0">
                    <input class="form-control mr-sm-2" type="search" placeholder="Search..." aria-label="Search">
                </form>
                --}}
            </div>
        </nav>
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
