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

Route::get('/pages/{page}', function (\App\Page $page) {
    if (!$page) {
        abort(404);
    }
    else {
        return view('page', compact('page'));
    }
});

Route::get('/', function () {
    $sections = DB::table('pages')
        ->get()
        ->groupBy('section');

    return view('pages', compact('sections'));
});
