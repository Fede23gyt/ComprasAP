<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\InsumoController; // <-- Importante: Añadir el controlador de Insumos

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// La ruta de login ya se maneja en auth.php, esta línea es redundante
// Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /* Oficinas (con middleware simplificado) */
    Route::get('/oficinas',              [OficinaController::class, 'index'])->name('oficinas.index');
    Route::get('/oficinas/create',       [OficinaController::class, 'create'])->name('oficinas.create');
    Route::post('/oficinas',             [OficinaController::class, 'store'])->name('oficinas.store');
    Route::get('/oficinas/{oficina}/edit', [OficinaController::class, 'edit'])->name('oficinas.edit');
    Route::patch('/oficinas/{oficina}',  [OficinaController::class, 'update'])->name('oficinas.update');
    Route::patch('/oficinas/{oficina}/toggle', [OficinaController::class, 'toggle'])->name('oficinas.toggle');

    /* Insumos - Rutas para el CRUD con Modal */
    Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
    /* Route::get('/insumos/create', [InsumoController::class, 'index'])->name('insumos.create');*/
    Route::post('/insumos', [InsumoController::class, 'store'])->name('insumos.store');
    Route::patch('/insumos/{insumo}', [InsumoController::class, 'update'])->name('insumos.update');
    Route::delete('/insumos/{insumo}', [InsumoController::class, 'destroy'])->name('insumos.destroy');

});

require __DIR__.'/auth.php';
