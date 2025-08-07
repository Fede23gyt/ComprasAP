<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\TipoCompraController;
use App\Http\Controllers\TipoInsumoController;
use App\Http\Controllers\TipoNotaController;
use App\Http\Controllers\MemoController;

// Ruta de bienvenida (página principal)
Route::get('/', function () {
  return Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
  ]);
});

// Dashboard (requiere autenticación)
Route::get('/dashboard', function () {
  return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas que requieren autenticación
Route::middleware('auth')->group(function () {

  // Perfil de usuario
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  // Oficinas
  Route::get('/oficinas', [OficinaController::class, 'index'])->name('oficinas.index');
  Route::get('/oficinas/create', [OficinaController::class, 'create'])->name('oficinas.create');
  Route::post('/oficinas', [OficinaController::class, 'store'])->name('oficinas.store');
  Route::get('/oficinas/{oficina}/edit', [OficinaController::class, 'edit'])->name('oficinas.edit');
  Route::patch('/oficinas/{oficina}', [OficinaController::class, 'update'])->name('oficinas.update');
  Route::patch('/oficinas/{oficina}/toggle', [OficinaController::class, 'toggle'])->name('oficinas.toggle');

  // Insumos
  Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
  Route::post('/insumos', [InsumoController::class, 'store'])->name('insumos.store');
  Route::patch('/insumos/{insumo}', [InsumoController::class, 'update'])->name('insumos.update');
  Route::delete('/insumos/{insumo}', [InsumoController::class, 'destroy'])->name('insumos.destroy');
  Route::patch('/insumos/{insumo}/toggle-registrable', [InsumoController::class, 'toggleRegistrable'])->name('insumos.toggle-registrable');

// Nuevas rutas para exportación
  Route::get('/insumos/export/{format}', [InsumoController::class, 'export'])->name('insumos.export');

  // NOMENCLADORES con Resource Routes
  Route::resource('memos', MemoController::class)->only(['index', 'store', 'update', 'destroy']);
  //Route::resource('tipo_nota', TipoNotaController::class)->only(['index', 'store', 'update', 'destroy']);
  Route::resource('tipo_insumo', TipoInsumoController::class)->only(['index', 'store', 'update', 'destroy']);

  // Tipos de Compra (CON PREFIJO para evitar conflictos)
  Route::prefix('tipos-compras')->name('tipos-compras.')->group(function () {
    // CRUD básico
    Route::get('/', [TipoCompraController::class, 'index'])->name('index');
    Route::get('/create', [TipoCompraController::class, 'create'])->name('create');
    Route::post('/', [TipoCompraController::class, 'store'])->name('store');
    Route::get('/{tipoCompra}', [TipoCompraController::class, 'show'])->name('show');
    Route::get('/{tipoCompra}/edit', [TipoCompraController::class, 'edit'])->name('edit');
    Route::patch('/{tipoCompra}', [TipoCompraController::class, 'update'])->name('update');
    Route::delete('/{tipoCompra}', [TipoCompraController::class, 'destroy'])->name('destroy');

    // Rutas adicionales específicas
    Route::patch('/{tipoCompra}/toggle-estado', [TipoCompraController::class, 'toggleEstado'])->name('toggle-estado');
    Route::get('/api/activos', [TipoCompraController::class, 'getActivos'])->name('activos');
    Route::patch('/bulk/toggle-estado', [TipoCompraController::class, 'bulkToggleEstado'])->name('bulk.toggle-estado');
    Route::get('/api/check-unique', [TipoCompraController::class, 'checkUniqueDescripcion'])->name('check-unique');
    Route::get('/api/stats', [TipoCompraController::class, 'getStats'])->name('stats');
    Route::get('/export/{format?}', [TipoCompraController::class, 'export'])->name('export');
  });

  Route::prefix('tipos-nota')->name('tipos-nota.')->middleware('auth')->group(function () {

    // CRUD básico
    Route::get('/', [TipoNotaController::class, 'index'])->name('index');
    Route::get('/create', [TipoNotaController::class, 'create'])->name('create');
    Route::post('/', [TipoNotaController::class, 'store'])->name('store');
    Route::get('/{tipoNota}', [TipoNotaController::class, 'show'])->name('show');
    Route::get('/{tipoNota}/edit', [TipoNotaController::class, 'edit'])->name('edit');
    Route::patch('/{tipoNota}', [TipoNotaController::class, 'update'])->name('update');
    Route::delete('/{tipoNota}', [TipoNotaController::class, 'destroy'])->name('destroy');

    // Rutas adicionales con el mismo patrón
    Route::patch('/{tipoNota}/toggle-estado', [TipoNotaController::class, 'toggleEstado'])->name('toggle-estado');
    Route::get('/api/activos', [TipoNotaController::class, 'getActivos'])->name('activos');
    Route::patch('/bulk/toggle-estado', [TipoNotaController::class, 'bulkToggleEstado'])->name('bulk.toggle-estado');
    Route::get('/api/check-unique', [TipoNotaController::class, 'checkUniqueDescripcion'])->name('check-unique');
    Route::get('/api/stats', [TipoNotaController::class, 'getStats'])->name('stats');
    Route::get('/export/{format?}', [TipoNotaController::class, 'export'])->name('export');
  });
    // Rutas específicas con parámetros
    //Route::patch('/tipos-nota/{tipoNota}/toggle-estado', [TipoNotaController::class, 'toggleEstado'])->name('tipos-nota.toggle-estado');

    // Rutas del resource DESPUÉS
    //Route::resource('tipos-nota', TipoNotaController::class)->except(['create', 'show', 'edit']);
    // Alternativamente, también puedes usar resource para tipo_compra si prefieres:
  // Route::resource('tipo_compra', TipoCompraController::class)->only(['index', 'store', 'update', 'destroy']);
});

// Rutas de autenticación (SIEMPRE al final)
require __DIR__.'/auth.php';
