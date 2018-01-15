@extends('base')

@section('title', $page->name)

@section('content')
    <article class="man-page">
        {!! $page->raw_html !!}
    </article>
@endsection
