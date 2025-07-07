<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.instructions');
    return view('welcome');
});

Route::get('/dashboard', function () {
    if(auth()->user()->is_admin) {
        return redirect()->route('admin.instructions');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin-instructions', function () {
    return view('admin-instructions');
})->middleware(['auth', 'verified'])->name('admin.instructions');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/product-movements/{productMovement}/print', [\App\Http\Controllers\ProductMovementController::class, 'printView'])
    ->name('product-movements.print');

require __DIR__.'/auth.php';
