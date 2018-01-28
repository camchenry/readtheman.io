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
    $categories = DB::table('pages')
        ->select('category', DB::raw('count(*) as total'))
        ->groupBy('category')
        ->orderBy('total', 'desc')
        ->get();
    $oses = DB::table('pages')
        ->select('os', DB::raw('count(*) as total'))
        ->whereNotNull('os')
        ->groupBy('os')
        ->orderBy('total', 'desc')
        ->get();
    $sources = DB::table('pages')
        ->select('source', DB::raw('count(*) as total'))
        ->whereNotNull('source')
        ->groupBy('source')
        ->orderBy('total', 'desc')
        ->get();
    return view('pages', compact('categories', 'oses', 'sources'));
});
Route::get('/pages/{page}', function (\App\Page $page) {
    if (!$page) {
        abort(404);
    }
    else {
        return view('page', compact('page'));
    }
});
