@extends('base')

@section('title', trim($page->name))

@section('meta.description', $page->short_description)

@section('scripts')
    <script async defer src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js" async></script>
    <script async defer>
    document.addEventListener('DOMContentLoaded', function(){
        var clipboard = new Clipboard('.copy');
        console.log(clipboard);
    })
    </script>
@endsection

@section('content')
    <header class="man-page-header bg-light">
        <div class="container py-2 py-sm-3 py-lg-4 pb-1 mb-3 mb-lg-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ URL::to('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ URL::to('/pages') }}">Pages</a></li>
                    <li class="breadcrumb-item"><a href="{{ URL::to("/section/{$page->section}") }}">{{ $page->getSection()->description }} ({{ $page->getSection()->section }})</a></li>
                    <li class="breadcrumb-item">{{ $page->category }}</li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $page->name }}</li>
                </ol>
            </nav>
            <h1 class="page-name">
                {{ trim($page->name) }}
                <small class="text-muted">({{ $page->getSection()->section }})</small>
            </h1>
        </div>
    </header>
    <div class="container">
        <div class="main row">
            <div class="col-lg-8">
                <article class="mb-4 man-page">
                    {!! $page->raw_html !!}
                </article>
            </div>
            <aside class="man-page-aside col-lg-4">
                <div class="actions mb-3 d-flex">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">View in terminal</button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="px-3 py-2">
                            <!-- Copy command -->
                            <p>Paste this in your terminal:</p>
                            <div class="input-group">
                                <input id="view_in_terminal" class="form-control" type="text" readonly value="man {{ $page->section }} {{ trim($page->name) }}">
                                <div class="input-group-append">
                                    <button class="copy btn btn-light" type="button" data-clipboard-target="#view_in_terminal" title="Copy to clipboard">
                                        <svg aria-hidden="true" class="octicon octicon-clippy" height="16" version="1.1" viewBox="0 0 14 16" width="14"><path fill-rule="evenodd" d="M2 13h4v1H2v-1zm5-6H2v1h5V7zm2 3V8l-3 3 3 3v-2h5v-2H9zM4.5 9H2v1h2.5V9zM2 12h2.5v-1H2v1zm9 1h1v2c-.02.28-.11.52-.3.7-.19.18-.42.28-.7.3H1c-.55 0-1-.45-1-1V4c0-.55.45-1 1-1h3c0-1.11.89-2 2-2 1.11 0 2 .89 2 2h3c.55 0 1 .45 1 1v5h-1V6H1v9h10v-2zM2 5h8c0-.55-.45-1-1-1H8c-.55 0-1-.45-1-1s-.45-1-1-1-1 .45-1 1-.45 1-1 1H3c-.55 0-1 .45-1 1z"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {!! $page->table_of_contents_html !!}
                @if(count($other_sections_pages) > 0)
                    <div>
                        <h5>Other sections</h5>
                        <ul>
                            @foreach($other_sections_pages as $other_page)
                                <li><a href="{{ $other_page->getUrl() }}">{{ $other_page->name }} ({{ $other_page->section }}) - {{ $other_page->getSection()->description }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection

@section('colophon')
    <div class="bg-light">
        <div class="container">
            <div class="py-2 py-lg-4 row">
                <div class="col">
                    <h3>Information</h3>
                    <dl>
                        <dt>Source</dt>
                        <dd>{{ $page->source }}</dd>
                        <dt>OS/version</dt>
                        <dd>{{ $page->os }}</dd>
                        <dt>Source updated</dt>
                        <dd>{{ $page->page_updated_at->format('F j, Y') }}</dd>
                        <dt>Page created</dt>
                        <dd>{{ $page->created_at->format('F j, Y') }}</dd>
                        <dt>Page generated</dt>
                        <dd>{{ $page->updated_at->format('F j, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
