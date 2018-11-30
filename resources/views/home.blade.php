@extends('base')

@section('title', 'Searchable, enhanced online man pages.')

@push('body_scripts')
    <script defer>
        var ALGOLIA_APP_ID = '{{ env('ALGOLIA_APP_ID') }}';
        var ALGOLIA_SEARCH_KEY = '{{ env('ALGOLIA_SEARCH_KEY') }}';
        var SEARCH_INDEX = '{{ env('ALGOLIA_INDEX') }}';
        var BASE_URL = '{{ URL::to('/') }}';
    </script>
    <script defer src="{{ url('js/home_search.js') }}"></script>
@endpush

@section('content')
    <div id="app" class="homepage" v-bind:class="{queried: hasQueried}">
        <ais-index index-name="live_man_pages" :search-store="searchStore">
            <div class="jumbotron jumbotron-fluid">
                <div class="text-center container">
                    <h1 class="title mb-0 display-4">ReadTheMan</h1>
                    <p class="lead">Searchable, enhanced online man pages.</p>
                    <div class="search-wrapper d-flex flex-row">
                        <home-search></home-search>
                    </div>
                </div>
            </div>
            <div class="container" v-cloak v-show="searchStore.query.length > 0">
                <div class="row">
                     <div class="col-md-8">
                         <div class="row pb-2">
                             <div class="col">
                                 <span>
                                     <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Category</button>
                                     <div class="dropdown-menu">
                                         <div class="px-3 py-2">
                                             <ais-refinement-list attribute-name="category" :class-names="{
                                             'ais-refinement-list__count': 'badge badge-light',
                                             'ais-refinement-list__checkbox': 'checkbox mr-1'
                                             }"
                                              :sort-by="['count:desc', 'name:asc']"
                                              >
                                              </ais-refinement-list>
                                         </div>
                                     </div>
                                 </span>

                                 <span>
                                     <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Source</button>
                                     <div id="source_dropdown" class="dropdown-menu">
                                         <div class="px-3 py-2">
                                               <ais-refinement-list attribute-name="source" :class-names="{
                                               'ais-refinement-list__count': 'badge badge-light',
                                               'ais-refinement-list__checkbox': 'checkbox mr-1'
                                               }"
                                             :sort-by="['count:desc', 'name:asc']"
                                             >
                                             </ais-refinement-list>
                                         </div>
                                     </div>
                                 </span>

                                 <span>
                                     <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">OS</button>
                                     <div class="dropdown-menu">
                                         <div class="px-3 py-2">
                                             <ais-refinement-list attribute-name="os" :class-names="{
                                             'ais-refinement-list__count': 'badge badge-light',
                                             'ais-refinement-list__checkbox': 'checkbox mr-1'
                                             }"
                                               :sort-by="['count:desc', 'name:asc']"
                                               >
                                               </ais-refinement-list>
                                         </div>
                                     </div>
                                 </span>

                                 <span>
                                     <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Section</button>
                                     <div class="dropdown-menu">
                                         <div class="px-3 py-2">
                                            <ais-refinement-list attribute-name="section" :class-names="{
                                                'ais-refinement-list__count': 'badge badge-light',
                                                'ais-refinement-list__checkbox': 'checkbox mr-1'
                                                }"
                                                :sort-by="['count:desc', 'name:asc']"
                                            >
                                            </ais-refinement-list>
                                         </div>
                                     </div>
                                 </span>
                             </div>
                         </div>
                         <div class="row pb-2">
                             <div class="col">
                                 <ais-stats class="text-muted"></ais-stats>
                             </div>
                             <div class="col form-inline justify-content-end">
                                 <ais-powered-by class="px-3"></ais-powered-by>
                             </div>
                         </div>
                         <ais-results>
                             <template slot-scope="{ result }">
                                 <div class="search-result mb-4">
                                     <h3>
                                         <a :href="'{{ URL::to('/pages') }}' + '/' + result.section + '/' + result.name">
                                             <ais-highlight :result="result" attribute-name="name"></ais-highlight>
                                         </a>
                                     </h3>
                                     <p>
                                     <ais-highlight :result="result" attribute-name="short_description"></ais-highlight>
                                     </p>
                                     <p class="text-muted">
                                     <ais-snippet :result="result" attribute-name="description"></ais-snippet>
                                     </p>
                                 </div>
                             </template>
                             </ais-results>
                             <ais-no-results class="mb-4">
                             </ais-no-results>
                     </div>
                     <aside class="col-md-4">
                     </aside>
                 </div>
            </div>
            <div class="container">
                <div class="row">
                    <h1 class="col-12">Sections</h1>
                    @foreach($sections as $section)
                        <a class="col-sm-12 col-md-6 col-lg-4" href="{{ $section->getUrl() }}">
                            <div class="h5 shadow-sm border p-3 mb-2">
                                {{ $section->description }}
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </ais-index>
    </div>
@endsection
