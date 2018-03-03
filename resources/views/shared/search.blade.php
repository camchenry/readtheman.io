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
                             <ais-clear :class-names="{'ais-clear': 'btn btn-light'}">
                                 <span aria-hidden="true">
                                     <svg title="Clear" class="octicon" xmlns="http://www.w3.org/2000/svg" width="12" height="16" viewBox="0 0 12 16"><path fill-rule="evenodd" d="M7.48 8l3.75 3.75-1.48 1.48L6 9.48l-3.75 3.75-1.48-1.48L4.52 8 .77 4.25l1.48-1.48L6 6.52l3.75-3.75 1.48 1.48z"/></svg>
                                 </span>
                                 </ais-clear>
                                 <button class="btn btn-primary" type="submit">
                                     <span aria-hidden="true">
                                         <svg title="Search" class="octicon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15.7 13.3l-3.81-3.83A5.93 5.93 0 0 0 13 6c0-3.31-2.69-6-6-6S1 2.69 1 6s2.69 6 6 6c1.3 0 2.48-.41 3.47-1.11l3.83 3.81c.19.2.45.3.7.3.25 0 .52-.09.7-.3a.996.996 0 0 0 0-1.41v.01zM7 10.7c-2.59 0-4.7-2.11-4.7-4.7 0-2.59 2.11-4.7 4.7-4.7 2.59 0 4.7 2.11 4.7 4.7 0 2.59-2.11 4.7-4.7 4.7z"/></svg>
                                     </span>
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
