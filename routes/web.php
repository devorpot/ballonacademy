<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return Inertia::render('Home');
});



Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::get('/register', [AuthController::class, 'registerForm']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
     return Inertia::render('Auth/Dashboard');
    });
});


Route::get('/admin', fn() => Inertia::render('Admin'))
    ->middleware(['auth', 'role:admin']);

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', fn () => Inertia::render('Admin'));
});