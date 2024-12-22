<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    if(auth()->check()) {
        return redirect()->route('home');
    }
    return view('auth.login');
});

Route::get('/r/{shortenedUrl}', [HomeController::class, 'redirect']);

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/add-url', [HomeController::class, 'addUrl'])->name('addUrl');
    Route::get('/update-url/{id}', [HomeController::class, 'updateUrl'])->name('updateUrl');
});

Auth::routes();


