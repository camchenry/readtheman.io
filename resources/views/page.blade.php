@extends('base')

@section('title', $page->name)

@section('meta.description', $page->short_description);

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js" async></script>
    <script>
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
            <div class="col-sm-12 col-lg-4 input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text"><b>$</b></div>
                </div>
                <input id="view_in_terminal" class="form-control" type="text" readonly value="man {{ $page->section }} {{ trim($page->name) }}">
                <div class="input-group-append">
                    <button class="copy btn btn-secondary" type="button" data-clipboard-target="#view_in_terminal">Copy</button>
                </div>
            </div>
        </div>
    </div>
    <header>
        <div class="container">
            <h1 class="py-4 display-4">{{ $page->name }}</h1>
        </div>
    </header>
    <div class="container">
        <p class="my-2 text-muted">
            Last updated: <span>{{ $page->page_updated_at->format('F j, Y') }}</span>
            &nbsp;&bullet;&nbsp;
            Last fetched: <span>{{ $page->updated_at->format('F j, Y') }}</span>
        </p>
        <hr>
        <article class="mb-4 man-page">
            {!! $page->raw_html !!}
        </article>
    </div>
@endsection
