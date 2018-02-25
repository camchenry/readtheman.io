@extends('base')

@section('title', 'Home')

@section('content')
    <div class="homepage">
        <div class="jumbotron jumbotron-fluid">
            <div class="container py-lg-5">
                <h1 class="display-4">ReadTheMan</h1>
                <p>Searchable, convenient, online man pages.</p>
                <p class="lead">
                    <a class="btn btn-primary btn-lg" href="{{URL::to('/pages')}}" role="button">View pages</a>
                </p>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <h1>Sections</h1>
                    <ul class="list-unstyled">
                    @foreach($sections as $section)
                        <li class="h5"><a href="{{ $section->getUrl() }}">{{ $section->description }} ({{$section->section}})</a></li>
                    @endforeach
                    </ul>
                </div>
                {{-- <div class="col-sm-12 col-md-6 col-lg-4"> --}}
                {{--     <h1>Categories</h1> --}}
                {{-- </div> --}}
                {{-- <div class="col-sm-12 col-md-6 col-lg-4"> --}}
                {{--     <h1>Sources</h1> --}}
                {{-- </div> --}}
            </div>
        </div>
    </div>
@endsection
