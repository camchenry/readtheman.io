@extends('base')

@section('title', $page->name)

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
    </div>
    <header>
        <div class="container">
            <h1 class="py-4 display-4">{{ $page->name }}</h1>
        </div>
    </header>
    <div class="container">
        <h5>{{ $page->category }} - Section {{ $page->section }}</h5>
        <p class="text-muted">
            Last updated: <span>{{ $page->page_updated_at->format('F j, Y') }}</span>
            &nbsp;&bullet;&nbsp;
            Last fetched: <span>{{ $page->updated_at->format('F j, Y') }}</span>
        </p>
        <hr>
        <article class="man-page">
            {!! $page->raw_html !!}
        </article>
    </div>
@endsection
