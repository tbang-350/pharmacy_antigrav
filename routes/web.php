<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/products', \App\Livewire\ProductManager::class)->name('products');
    Route::get('/pos', \App\Livewire\Pos::class)->name('pos');
    Route::get('/purchases', \App\Livewire\PurchaseManager::class)->name('purchases');
});
