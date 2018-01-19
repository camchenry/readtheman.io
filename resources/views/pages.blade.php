@extends('base')

@section('title', 'Pages')

@section('content')
    <div class="my-4 container">
        <h1>Pages</h1>

        @foreach($sections as $num => $section)
            <h2>Section {{ $num }}</h2>
            @foreach($section->chunk(3) as $chunk)
                <div class="row">
                    @foreach($chunk as $page)
                        <div class="col">
                            <a href="{{ url('pages/' . $page->name) }}">{{$page->name}}</a>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endforeach
    </div>
@endsection
