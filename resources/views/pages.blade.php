@extends('base')

@section('title', 'Pages')

@section('scripts')
    <script async defer src="{{ url('js/search.js') }}"></script>
@endsection

@section('content')
    <div class="my-4 container">
        <h1>Pages</h1>

        <div id="search_app">
            <ais-index
             app-id="{{ env('ALGOLIA_APP_ID') }}"
             api-key="{{ env('ALGOLIA_SEARCH_KEY') }}"
             index-name="live_man_pages"
             >
             <div class="row">
                 <div class="col">
                     <ais-search-box>
                         <div class="input-group">
                             <ais-input
                              placeholder="Search..."
                              :class-names="{'ais-input': 'mt-2 mb-3 form-control form-control-lg'}"
                              />

                             <span class="input-group-btn">
                                 <ais-clear :class-names="{'ais-clear': 'btn btn-default'}">
                                     <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                     </ais-clear>
                                     <button class="btn btn-default" type="submit">
                                         <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                     </button>
                             </span>
                         </div><!-- /input-group -->

                    </ais-search-box>
                 </div>
             </div>
             <div class="row">
                 <div class="col-md-3">
                     <h5 class="text-muted">Refine by</h5>
                     <h6><b>Category</b></h6>
                     <ul class="list-unstyled">
                     @foreach ($categories as $category)
                         <li>
                             <label>
                                 <input type="checkbox">
                                 {{ $category->category }} <small class="text-muted">({{ $category->total }})</small>
                             </label>
                         </li>
                     @endforeach
                     <h6><b>OS</b></h6>
                     <ul class="list-unstyled">
                     @foreach ($oses as $os)
                         <li>
                             <label>
                                 <input type="checkbox">
                                 {{ $os->os }} <small class="text-muted">({{ $os->total }})</small>
                             </label>
                         </li>
                     @endforeach
                     <h6><b>Source</b></h6>
                     <ul class="list-unstyled">
                     @foreach ($sources as $source)
                         <li>
                             <label>
                                 <input type="checkbox">
                                 {{ $source->source }} <small class="text-muted">({{ $source->total }})</small>
                             </label>
                         </li>
                     @endforeach
                     </ul>
                 </div>
                 <div class="col-md-9">
                <ais-results>
                    <template slot-scope="{ result }">
                        <div class="card mb-2">
                            <div class="card-body">
                                <h3>
                                    <a :href="'{{ URL::to('/pages') }}' + '/' + result.name">
                                        <ais-highlight :result="result" attribute-name="name"></ais-highlight>
                                    </a>
                                </h3>
                                <p class="lead">
                                <ais-highlight :result="result" attribute-name="short_description"></ais-highlight>
                                </p>
                                <p class="text-muted">
                                <ais-highlight :result="result" attribute-name="description"></ais-highlight>
                                </p>
                            </div>
                        </div>
                    </template>
                </ais-results>
                 </div>
             </div>
            </ais-index>
        </div>
    </div>
@endsection
