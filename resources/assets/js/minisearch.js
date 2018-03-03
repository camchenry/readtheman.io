/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

var algolia = require('algoliasearch');
var autocomplete = require('autocomplete.js');

var client = algolia(ALGOLIA_APP_ID, ALGOLIA_SEARCH_KEY);
var index = client.initIndex(SEARCH_INDEX);
const options = {
    hint: true,
    autoselect: true,
    autoselectOnBlur: true,
    templates: {
        footer: '<div class="pt-1 pb-2 px-2 border-top"><small class="text-muted">Powered by <a href="https://www.algolia.com/"><img style="height: 1rem;" src="https://www.algolia.com/static_assets/images/press/downloads/algolia-logo-light.svg" /> </a></small></div>'
    }
};
autocomplete('#mini_search_input', options, [
    {
        source: autocomplete.sources.hits(index, { hitsPerPage: 5 }),
        displayKey: 'name',
        templates: {
            suggestion: function(suggestion) {
                var name = suggestion._highlightResult.name.value;
                var section = suggestion.section;
                return name + '(' + section + ')';
            },
            footer: function(suggestion) {
                return suggestion.short_description;
            },
            empty: function(suggestion) {
                return '<div class="p-2">No pages found.</div>';
            }
        }
    }
]).on('autocomplete:selected', function(event, suggestion, dataset) {
    window.location.href = BASE_URL + '/pages/' + suggestion.section + '/' + suggestion.name;
});
