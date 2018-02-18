@extends('base')

@section('title', trim($page->name))

@section('meta.description', $page->short_description)

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
        </div>
        <header>
            <h1 class="page-name py-4 display-4">{{ $page->name }}</h1>
        </header>
        <hr>
        <div class="row">
            <div class="col-lg-8">
                <article class="mb-4 man-page">
                    {!! $page->raw_html !!}
                </article>
            </div>
            <div class="col-lg-4">
                <!-- Copy command -->
                <div class="mb-3 input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text"><b>$</b></div>
                    </div>
                    <input id="view_in_terminal" class="form-control" type="text" readonly value="man {{ $page->section }} {{ trim($page->name) }}">
                    <div class="input-group-append">
                        <button class="copy btn btn-secondary" type="button" data-clipboard-target="#view_in_terminal">Copy</button>
                    </div>
                </div>
                {!! $page->table_of_contents_html !!}
                <p class="my-2 text-muted">
                Last updated: <span>{{ $page->page_updated_at->format('F j, Y') }}</span>
                <br>
                Last generated: <span>{{ $page->updated_at->format('F j, Y') }}</span>
                </p>
            </div>
        </div>
    </div>
@endsection
