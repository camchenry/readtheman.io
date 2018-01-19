<?php

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
    $sections = DB::table('pages')
        ->get()
        ->sortBy('section')
        ->groupBy('section');

    foreach($sections as $index => $section)
    {
        $sections[$index] = $section->sortBy('name');
    }

    return view('pages', compact('sections'));
});
Route::get('/pages/{page}', function (\App\Page $page) {
    if (!$page) {
        abort(404);
    }
    else {
        return view('page', compact('page'));
    }
});
