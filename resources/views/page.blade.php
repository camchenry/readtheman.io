@extends('base')

@section('title', $page->name)

@section('content')
    <header>
        <div>
            <h2>{{ $page->category }}</h2>
            <h3>Section {{ $page->section }}</h3>
            <h1>{{ $page->name }}</h1>
        </div>
    </header>
    <article class="man-page">
        {!! $page->raw_html !!}
    </article>
    <footer>
        <div>
            <p>Last updated: <span>{{ $page->page_updated_at->format('F j, Y') }}</span></p>
        </div>
    </footer>
@endsection
