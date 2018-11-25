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

import InstantSearch from 'vue-instantsearch';
import Search from './Search.vue';
import HomeSearch from './HomeSearch.vue';
Vue.use(InstantSearch);
Vue.component('search', Search);
Vue.component('home-search', HomeSearch);

import algoliaClient from 'algoliasearch';
import { createFromAlgoliaClient } from 'vue-instantsearch';

const client = algoliaClient(ALGOLIA_APP_ID, ALGOLIA_SEARCH_KEY);
const index = client.initIndex(SEARCH_INDEX);
const searchStore = createFromAlgoliaClient(client);

var app_data = {
    searchStore: searchStore,
    hasQueried: false,
};

const app = new Vue({
    el: '#app',
    data: app_data,
    watch: {
        'searchStore.query'(newQuery, oldQuery) {
            app_data.hasQueried = true;
        }
    }
});
