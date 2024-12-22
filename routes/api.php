<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/urls', [ApiController::class, 'index']);
    Route::get('/urls/{url}', [ApiController::class, 'show']);
    Route::post('/urls', [ApiController::class, 'store']);
    Route::patch('/urls/{url}', [ApiController::class, 'update']);
    Route::delete('/urls/{url}', [ApiController::class, 'destroy']);
});
