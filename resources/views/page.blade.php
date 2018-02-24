@extends('base')

@section('title', trim($page->name))

@section('meta.description', $page->short_description)

@section('scripts')
    <script async defer src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js" async></script>
    <script async defer>
    document.addEventListener('DOMContentLoaded', function(){
        var clipboard = new Clipboard('.copy');
        console.log(clipboard);
    })
    </script>
@endsection

@section('content')
    <div class="mt-4"></div>
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ URL::to('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ URL::to('/pages') }}">Pages</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $page->name }}</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-sm-12 col-lg-8">
                <h5>{{ $page->category }} - Section {{ $page->section }}</h5>
            </div>
        </div>
        <header class="py-3 mb-3">
            <h1 class="page-name">{{ $page->name }}</h1>
        </header>
        <div class="main row">
            <div class="col-lg-8">
                <article class="mb-4 man-page">
                    {!! $page->raw_html !!}
                </article>
            </div>
            <aside class="col-lg-4">
                {!! $page->table_of_contents_html !!}
                <!-- Copy command -->
                <div class="mb-3 input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text"><b>$</b></div>
                    </div>
                    <input id="view_in_terminal" class="form-control" type="text" readonly value="man {{ $page->section }} {{ trim($page->name) }}">
                    <div class="input-group-append">
                        <button class="copy btn btn-light" type="button" data-clipboard-target="#view_in_terminal" title="Copy to clipboard">
                            <svg aria-hidden="true" class="octicon octicon-clippy" height="16" version="1.1" viewBox="0 0 14 16" width="14"><path fill-rule="evenodd" d="M2 13h4v1H2v-1zm5-6H2v1h5V7zm2 3V8l-3 3 3 3v-2h5v-2H9zM4.5 9H2v1h2.5V9zM2 12h2.5v-1H2v1zm9 1h1v2c-.02.28-.11.52-.3.7-.19.18-.42.28-.7.3H1c-.55 0-1-.45-1-1V4c0-.55.45-1 1-1h3c0-1.11.89-2 2-2 1.11 0 2 .89 2 2h3c.55 0 1 .45 1 1v5h-1V6H1v9h10v-2zM2 5h8c0-.55-.45-1-1-1H8c-.55 0-1-.45-1-1s-.45-1-1-1-1 .45-1 1-.45 1-1 1H3c-.55 0-1 .45-1 1z"></path></svg>
                        </button>
                    </div>
                </div>
                <p class="my-2 text-muted">
                Last updated: <span>{{ $page->page_updated_at->format('F j, Y') }}</span>
                <br>
                Last generated: <span>{{ $page->updated_at->format('F j, Y') }}</span>
                </p>
            </aside>
        </div>
    </div>
@endsection
