@push('body_scripts')
    <script defer>
        var ALGOLIA_APP_ID = '{{ env('ALGOLIA_APP_ID') }}';
        var ALGOLIA_SEARCH_KEY = '{{ env('ALGOLIA_SEARCH_KEY') }}';
        var SEARCH_INDEX = 'live_man_pages';
        var BASE_URL = '{{ URL::to('/') }}';
    </script>
    <script defer src="{{ url('js/minisearch.js') }}"></script>
@endpush

<form class="form-inline my-2 my-lg-0" autocomplete="off" spellcheck="false">
    <div id="mini_search">
        <div class="input-group">
            <input id="mini_search_input" placeholder="Search..." class="form-control">
        </div>
    </div>
</form>
