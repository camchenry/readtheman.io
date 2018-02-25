@extends('base')

@section('title', 'Section ' . trim($section->section))

@section('meta.description', 'All man pages in man section ' . $section->section)

@section('content')
    <header class="man-page-header bg-light">
        <div class="container py-2 py-sm-3 py-lg-4 pb-1 mb-3 mb-lg-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ URL::to('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ URL::to('/pages') }}">Pages</a></li>
                    <li class="breadcrumb-item active">Section {{ $section->section }}</li>
                </ol>
            </nav>
            <h1 class="page-name">
                Section {{ trim($section->section) }}
                <small class="text-muted">{{ $section->description }}</small>
            </h1>
        </div>
    </header>
    <div class="container">
        @foreach($pages as $letter => $pages)
            <h2 class="display-2">{{ $letter }}</h2>
            <div class="row">
                @foreach($pages as $page)
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <h3><a href="{{ $page->getUrl() }}">{{ $page->name }}</a></h3>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
