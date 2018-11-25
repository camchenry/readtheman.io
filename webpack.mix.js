let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/assets/sass/app.scss', 'public/css');

mix.js('resources/assets/js/search.js', 'public/js');
mix.js('resources/assets/js/minisearch.js', 'public/js');
mix.js('resources/assets/js/home_search.js', 'public/js');

mix.js('node_modules/clipboard/dist/clipboard.min.js', 'public/js');

mix.js('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/js');
