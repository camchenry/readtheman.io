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
    return view('home');
});
Route::get('/pages', function () {
    return view('pages');
});
Route::get('/pages/{section}/{page}', function (string $section, string $page) {
    $page = \App\Page::where('section', '=', $section)->where('name', '=', $page)->first();

    if (!$page) {
        abort(404);
    }
    else {
        return view('page', compact('page'));
    }
});
Route::get('/section/{section}', function (string $section) {
    $pages = \App\Page::where('section', '=', $section)
        ->orderBy('name', 'asc')
        ->get();

    if (!$pages) {
        abort(404);
    }

    $section = \App\Section::where('section', '=', $section)->first();

    $pages = $pages->groupBy(function ($page, $key) {
        return strtoupper(substr($page->name, 0, 1));
    })
    ->sortBy(function($page, $key) {
        return $key;
    });

    return view('section_list', compact('section', 'pages'));
});
