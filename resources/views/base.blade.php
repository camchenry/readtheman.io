<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@yield('meta.description', 'ReadTheMan is the best place to search thousands of structured, formatted help and reference documents.')"/>

        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#2b5797">
        <meta name="theme-color" content="#1993e2">

        @if(Request::is('pages*') || Request::is('section*'))
            <meta property="og:title" content="@yield('title') | ReadTheMan" />
        @else
            <meta property="og:title" content="ReadTheMan | @yield('title')" />
        @endif

        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ URL::current() }}" />
        <meta property="og:image" content="{{ URL::to('/') . '/og_readtheman.png' }}" />
        <meta property="og:description" content="@yield('meta.description', 'ReadTheMan is the best place to search thousands of structured, formatted help and reference documents.')"/>

        @if(Request::is('pages*') || Request::is('section*'))
            <title>@yield('title') | ReadTheMan</title>
        @else
            <title>ReadTheMan | @yield('title')</title>
        @endif

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Lato:400,700" rel="stylesheet">
        <link href="{{ url('css/app.css') }}" rel="stylesheet" type="text/css">
        @yield('stylesheets')

        <script defer src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
                integrity="sha256-3edrmyuQ0w65f8gfBsqowzjJe2iM6n0nKciPUp8y+7E="
                crossorigin="anonymous"></script>
        <script defer src="{{ url('js/bootstrap.bundle.min.js') }}"></script>
        @stack('scripts')
    </head>
    <body class="full-page">
        <nav class="navbar-main navbar navbar-expand-md navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="{{ URL::to('/') }}">ReadTheMan</a>
                <ul class="navbar-nav mr-auto">
                    {{--
                    <li class="nav-item {{ Request::is('pages') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ URL::to('/pages') }}">
                            Pages @if(Request::is('/'))<span class="sr-only">(current)</span>@endif
                        </a>
                    </li>
                    --}}
                </ul>
                @if(!Request::is('/'))
                    @include('shared.minisearch')
                @endif
            </div>
        </nav>
        <div class="full-page-content mb-3">
            @yield('content')
        </div>

        <div class="colophon">
            @yield('colophon')
        </div>

        <footer class="footer">
            <div class="container py-3">
                <p class="d-inline text-muted m-0">Made by <a href="https://camchenry.com">Cameron McHenry</a></p>
                -
                <a href="#">Go to top</a>
            </div>
        </footer>
        @stack('body_scripts')
        @include('shared.tracking')
    </body>
</html>
