<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\FocusSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [RegisterController::class, 'store'])->name('register');
Route::post('/login', [LoginController::class, 'store'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/focus-sessions', [FocusSessionController::class, 'index'])->name('focus-sessions');
    Route::post('/focus-sessions', [FocusSessionController::class, 'store'])->name('focus-session-store');
    Route::delete('/focus-sessions', [FocusSessionController::class, 'destroyAll'])
        ->name('focus-sessions-destroy-all');
});
