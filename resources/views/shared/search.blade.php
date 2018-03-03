@push('scripts')
    <script defer src="{{ url('js/search.js') }}"></script>
@endpush

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
     <div class="main row">
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
                 <ais-no-results>
                     </ais-no-results>
         </div>
         <aside class="search-aside col-md-4">
             <h5 class="text-muted">Refine by</h5>
             <div class="row">
                 <ais-refinement-list class="col-xs-12 col-sm-6 col-md-12" attribute-name="category" :class-names="{
                 'ais-refinement-list__count': 'badge badge-light',
                 'ais-refinement-list__item': 'checkbox'
                 }"
               :sort-by="['count:desc', 'name:asc']"
               >
               <h6 class="refinement-header" slot="header">Category</h6>
               </ais-refinement-list>

               <ais-refinement-list class="col-xs-12 col-sm-6 col-md-12" attribute-name="source" :class-names="{
               'ais-refinement-list__count': 'badge badge-light',
               'ais-refinement-list__item': 'checkbox'
               }"
             :sort-by="['count:desc', 'name:asc']"
             >
             <h6 class="refinement-header" slot="header">Source</h6>
             </ais-refinement-list>

             <ais-refinement-list class="col-xs-12 col-sm-6 col-md-12" attribute-name="os" :class-names="{
             'ais-refinement-list__count': 'badge badge-light',
             'ais-refinement-list__item': 'checkbox'
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
     </ais-index>
</div>
