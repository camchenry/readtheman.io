@extends('base')

@section('title', 'Home')

@section('content')
    <h1>Pages</h1>

    @foreach($sections as $num => $section)
        <h2>Section {{ $num }}</h2>
        <ul>
        @foreach($section as $page)
            <li><a href="{{ url('pages/' . $page->name) }}">{{$page->name}}</a></li>
        @endforeach
        </ul>
    @endforeach
@endsection
