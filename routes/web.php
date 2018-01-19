<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $sections = \App\Page::select('section')->distinct()->get();
    return view('home', compact('sections'));
});
Route::get('/pages', function () {
    $pages = [];

    return view('pages', compact('pages', 'highlights', 'query'));
});
Route::get('/pages/{page}', function (\App\Page $page) {
    if (!$page) {
        abort(404);
    }
    else {
        return view('page', compact('page'));
    }
});
Route::get('/pages/search/{query}', function (string $search_value) {
    /* $sections = DB::table('pages') */
    /*     ->get() */
    /*     ->sortBy('section') */
    /*     ->groupBy('section'); */

    $algolia = new \AlgoliaSearch\Client(env('ALGOLIA_APP_ID'), env('ALGOLIA_SEARCH_KEY'));
    $index =$algolia->initIndex('live_man_pages');
    $query = $index->search(trim($search_value));

    $pages = [];
    $highlights = [];
    foreach($query['hits'] as $hit) {
        $page = \App\Page::where('name', '=', $hit['name'])->first();
        $pages[] = $page;

        $highlights[$page->name] = $hit['_highlightResult'];
    }

    return view('pages', compact('pages', 'highlights', 'query'));
});
