@extends('base')

@section('title', 'Pages')

@section('scripts')
    <script async defer src="{{ url('js/search.js') }}"></script>
@endsection

@section('content')
    <div class="my-2 my-lg-4 my-sm-3 container">
        <h1 class="h4">Pages</h1>

        @include('shared.search')
    </div>
@endsection
