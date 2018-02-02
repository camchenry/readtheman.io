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
import TurbolinksAdapter from 'vue-turbolinks';
Vue.use(InstantSearch);
Vue.use(TurbolinksAdapter);

var app_data = {
    query: ''
};

document.addEventListener('turbolinks:load', () => {
    const app = new Vue({
        el: '#search_app',
        data: app_data
    });
});
const app = new Vue({
    el: '#search_app',
    data: app_data
});
