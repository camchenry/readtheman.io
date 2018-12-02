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
    $sections = \App\Section::get()->sortBy('id');
    return view('home', compact('sections'));
});
Route::get('/pages', function () {
    $sections = \App\Section::get()->sortBy('id');
    return view('home', compact('sections'));
});
Route::get('/pages/{section}/{page}', function (string $section, string $page) {
    $page = \App\Page::where('section', '=', $section)->where('name', '=', $page)->first();

    if (!$page) {
        abort(404);
    }
    else {
        $other_sections_pages = \App\Page::where('name', '=', $page->name)
            ->where('id', '!=', $page->id)
            ->orderBy('section', 'ASC')
            ->get();

        return view('page', compact('page', 'other_sections_pages'));
    }
});
Route::get('/section/{section}', function (string $section) {
    $section = \App\Section::where('section', '=', $section)->first();

    if (!$section) {
        abort(404);
    }

    $pages = \App\Page::where('section', '=', $section->section)
        ->orderBy('name', 'asc')
        ->get();

    if (!$pages) {
        abort(404);
    }

    $pages = $pages->groupBy(function ($page, $key) {
        return strtoupper(substr($page->name, 0, 1));
    })
    ->sortBy(function($page, $key) {
        return $key;
    });

    return view('section_list', compact('section', 'pages'));
});
