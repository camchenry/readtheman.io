@extends('base')

@section('title', 'Home')

@push('body_scripts')
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
                        <home-search
                        @query="alert(1)"
                        ></home-search>
                    </div>
                </div>
            </div>
            <div class="container" v-cloak v-show="searchStore.query.length > 0">
                <div class="row">
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
                     <aside class="search-aside col-md-4">
                         <h5 class="text-muted">Refine by</h5>
                         <div class="row">
                             <ais-refinement-list class="col-xs-12 col-sm-6 col-md-12" attribute-name="category" :class-names="{
                             'ais-refinement-list__count': 'badge badge-light',
                             'ais-refinement-list__item': 'checkbox pr-1'
                             }"
                           :sort-by="['count:desc', 'name:asc']"
                           >
                           <h6 class="refinement-header" slot="header">Category</h6>
                           </ais-refinement-list>

                           <ais-refinement-list class="col-xs-12 col-sm-6 col-md-12" attribute-name="source" :class-names="{
                           'ais-refinement-list__count': 'badge badge-light',
                           'ais-refinement-list__item': 'checkbox pr-1'
                           }"
                         :sort-by="['count:desc', 'name:asc']"
                         >
                         <h6 class="refinement-header" slot="header">Source</h6>
                         </ais-refinement-list>

                         <ais-refinement-list class="col-xs-12 col-sm-6 col-md-12" attribute-name="os" :class-names="{
                         'ais-refinement-list__count': 'badge badge-light',
                         'ais-refinement-list__item': 'checkbox pr-1'
                         }"
                           :sort-by="['count:desc', 'name:asc']"
                           >
                           <h6 class="refinement-header" slot="header">OS</h6>
                           </ais-refinement-list>

                            <ais-refinement-list class="col-xs-12 col-sm-6 col-md-12" attribute-name="section" :class-names="{
                                'ais-refinement-list__count': 'badge badge-light',
                                'ais-refinement-list__item': 'checkbox'
                                }"
                                :sort-by="['count:desc', 'name:asc']"
                            >
                                <h6 class="refinement-header" slot="header">Section</h6>
                            </ais-refinement-list>
                         </div>
                     </aside>
                 </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <h1>Sections</h1>
                        <ul class="list-unstyled">
                        @foreach($sections as $section)
                            <li class="h5"><a href="{{ $section->getUrl() }}">{{ $section->description }} ({{$section->section}})</a></li>
                        @endforeach
                        </ul>
                    </div>
                    {{-- <div class="col-sm-12 col-md-6 col-lg-4"> --}}
                    {{--     <h1>Categories</h1> --}}
                    {{-- </div> --}}
                    {{-- <div class="col-sm-12 col-md-6 col-lg-4"> --}}
                    {{--     <h1>Sources</h1> --}}
                    {{-- </div> --}}
                </div>
            </div>
        </ais-index>
    </div>
@endsection
