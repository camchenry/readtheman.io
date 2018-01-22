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
                <ais-results>
                    <template slot-scope="{ result }">
                        <div>
                        <h3>
                            <a :href="'{{ URL::to('/pages') }}' + '/' + result.name">
                                <ais-highlight :result="result" attribute-name="name"></ais-highlight>
                            </a>
                        </h3>
                        <p class="text-muted">
                            <ais-highlight :result="result" attribute-name="text"></ais-highlight>
                        </p>
                        </div>
                    </template>
                </ais-results>
            </ais-index>
        </div>
    </div>
@endsection
