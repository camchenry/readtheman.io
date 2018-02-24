@extends('base')

@section('title', 'Pages')

@section('scripts')
    <script async defer src="{{ url('js/search.js') }}"></script>
@endsection

@section('content')
    <div class="my-4 container">
        <h1 class="h4">Pages</h1>

        <div id="search_app">
            <ais-index
             app-id="{{ env('ALGOLIA_APP_ID') }}"
             api-key="{{ env('ALGOLIA_SEARCH_KEY') }}"
             index-name="live_man_pages"
             >
             <div class="row">
                 <div class="col">
                     <ais-search-box
                         class="mt-2 mb-3">
                         <div class="input-group">
                             <ais-input
                              placeholder="Search..."
                              autofocus="true"
                              :class-names="{'ais-input': 'form-control form-control-lg'}"
                              ></ais-input>

                             <div class="input-group-append">
                                 <ais-clear :class-names="{'ais-clear': 'btn btn-outline-secondary'}">
                                     <span aria-hidden="true">X</span>
                                 </ais-clear>
                                 <button class="btn btn-primary" type="submit">
                                     <span aria-hidden="true">Search</span>
                                 </button>
                             </div>
                         </div><!-- /input-group -->

                    </ais-search-box>
                 </div>
             </div>
             <div class="row">
                 <div class="col-md-4">
                     <h5 class="text-muted">Refine by</h5>
                     <ais-refinement-list attribute-name="category" :class-names="{
                         'ais-refinement-list__count': 'badge badge-light',
                         'ais-refinement-list__item': 'checkbox'
                         }"
                        :sort-by="['count:desc', 'name:asc']"
                     >
                         <h6 slot="header"><b>Category</b></h6>
                     </ais-refinement-list>

                     <ais-refinement-list attribute-name="source" :class-names="{
                         'ais-refinement-list__count': 'badge badge-light',
                         'ais-refinement-list__item': 'checkbox'
                         }"
                        :sort-by="['count:desc', 'name:asc']"
                     >
                         <h6 slot="header"><b>Source</b></h6>
                     </ais-refinement-list>

                     <ais-refinement-list attribute-name="os" :class-names="{
                         'ais-refinement-list__count': 'badge badge-light',
                         'ais-refinement-list__item': 'checkbox'
                         }"
                        :sort-by="['count:desc', 'name:asc']"
                     >
                         <h6 slot="header"><b>OS</b></h6>
                     </ais-refinement-list>

                     <ais-refinement-list attribute-name="section" :class-names="{
                         'ais-refinement-list__count': 'badge badge-light',
                         'ais-refinement-list__item': 'checkbox'
                         }"
                        :sort-by="['count:desc', 'name:asc']"
                     >
                         <h6 slot="header"><b>Section</b></h6>
                     </ais-refinement-list>
                 </div>
                 <div class="col-md-8">
                    <div class="row py-2">
                        <div class="col">
                            <ais-stats class="text-muted"></ais-stats>
                        </div>
                        <div class="col form-inline justify-content-end">
                            <ais-powered-by class="px-3"></ais-powered-by>
                        </div>
                    </div>
                    <ais-results>
                        <template slot-scope="{ result }">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h3>
                                        <a :href="'{{ URL::to('/pages') }}' + '/' + result.section + '/' + result.name">
                                            <ais-highlight :result="result" attribute-name="name"></ais-highlight>
                                        </a>
                                    </h3>
                                    <p>
                                    <ais-highlight :result="result" attribute-name="short_description"></ais-highlight>
                                    </p>
                                    <p class="text-muted">
                                    <ais-highlight :result="result" attribute-name="description"></ais-highlight>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </ais-results>
                    <ais-no-results>
                    </ais-no-results>
                 </div>
             </div>
            </ais-index>
        </div>
    </div>
@endsection
