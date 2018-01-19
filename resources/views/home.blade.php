@extends('base')

@section('title', 'Home')

@section('content')
    <div class="jumbotron jumbotron-fluid">
        <div class="container">
            <h1>Online Linux Man Pages</h1>
            <p class="lead">Neatly formatted, actively updated, man pages online, for your convenience.</p>
            <p class="lead">
                <a class="btn btn-primary btn-lg" href="{{URL::to('/pages')}}" role="button">View pages</a>
            </p>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Sections</h1>
            </div>
            <div class="col">
                <h1>Categories</h1>
            </div>
            <div class="col">
                <h1>Sources</h1>
            </div>
        </div>
    </div>
@endsection
