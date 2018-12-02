@extends('base')

@section('title', 'Section ' . trim($section->section))

@if($section->full_description)
    @section('meta.description', $section->full_description)
@else
    @section('meta.description', "A list of all pages in section {$section->section}: {$section->description}.")
@endif

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
            <h2 class="display-3">{{ $letter }}</h2>
            <div class="row">
                @foreach($pages as $page)
                    <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                        <a href="{{ $page->getUrl() }}">{{ $page->name }}</a>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
