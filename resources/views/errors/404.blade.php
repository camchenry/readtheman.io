@extends('base')

@section('title', '404')

@section('content')
    <header class="man-page-header bg-light">
        <div class="container py-2 py-sm-3 py-lg-4 pb-1 mb-3 mb-lg-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ URL::to('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">404</li>
                </ol>
            </nav>
            <h1 class="page-name">
                404 Page Not Found
            </h1>
        </div>
    </header>
@endsection
