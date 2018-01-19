@extends('base')

@section('title', 'Pages')

@section('content')
    <div class="my-4 container">
        <h1>Pages</h1>

        @foreach($pages as $page)
            <div class="search-result">
                <div class="row">
                    <div class="col">
                        <h3><a href="{{ url('pages/' . $page->name) }}">{{$page->name}}</a></h3>
                        <p>{!! $highlights[$page->name]['text']['value'] !!}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
