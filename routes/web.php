<?php

// routes/web.php - Rutas protegidas con middleware

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\TipoNotaController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

  // Rutas de administración - Solo admin, secretario, director
  Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

    // Gestión de usuarios
    Route::resource('usuarios', UserController::class)->names([
      'index' => 'users.index',
      'create' => 'users.create',
      'store' => 'users.store',
      'show' => 'users.show',
      'edit' => 'users.edit',
      'update' => 'users.update',
      'destroy' => 'users.destroy',
    ]);

    // Rutas adicionales para usuarios
    Route::patch('usuarios/{user}/toggle-status', [UserController::class, 'toggleStatus'])
      ->name('users.toggle-status');
    Route::patch('usuarios/{user}/office-authorization', [UserController::class, 'updateOfficeAuthorization'])
      ->name('users.office-authorization');

    // Gestión de roles
    Route::resource('roles', RoleController::class);
    Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])
      ->name('roles.toggle-status');

    // APIs para selects
    Route::get('api/users', [UserController::class, 'getUsersForSelect'])
      ->name('api.users');
    Route::get('api/roles', [RoleController::class, 'getRolesForSelect'])
      ->name('api.roles');
  });

  // Gestión de oficinas - Solo admin, secretario, director
  Route::middleware(['role:administrador,secretario,director'])->group(function () {
    Route::resource('oficinas', OficinaController::class)->except(['destroy']);
    Route::patch('oficinas/{oficina}/toggle', [OficinaController::class, 'toggle'])
      ->name('oficinas.toggle');

    // APIs de oficinas
    Route::prefix('api/oficinas')->group(function () {
      Route::get('/', [OficinaController::class, 'getOficinas'])
        ->name('api.oficinas.index');
      Route::get('{oficina}/hierarchy', [OficinaController::class, 'getHierarchy'])
        ->name('api.oficinas.hierarchy');
    });
  });

  // Gestión de tipos de nota - Solo admin, secretario, director
  Route::middleware(['role:administrador,secretario,director'])->group(function () {
    Route::resource('tipos-nota', TipoNotaController::class)->names([
      'index' => 'tipos-nota.index',
      'create' => 'tipos-nota.create',
      'store' => 'tipos-nota.store',
      'show' => 'tipos-nota.show',
      'edit' => 'tipos-nota.edit',
      'update' => 'tipos-nota.update',
      'destroy' => 'tipos-nota.destroy',
    ]);

    Route::patch('tipos-nota/{tipoNota}/toggle', [TipoNotaController::class, 'toggleEstado'])
      ->name('tipos-nota.toggle');
    Route::get('tipos-nota/export/{format}', [TipoNotaController::class, 'export'])
      ->name('tipos-nota.export');
  });

  // Rutas para nomencladores (solo lectura) - Todos los usuarios autenticados
  Route::prefix('nomencladores')->name('nomencladores.')->group(function () {
    Route::get('insumos', [InsumoController::class, 'nomenclador'])
      ->name('insumos.index');
    Route::get('tipos-nota', [TipoNotaController::class, 'nomenclador'])
      ->name('tipos-nota.index');
    Route::get('tipos-compra', [TipoCompraController::class, 'nomenclador'])
      ->name('tipos-compra.index');
  });

  // Rutas de notas de pedido - Según permisos específicos
  Route::prefix('notas-pedido')->name('notas-pedido.')->group(function () {

    // Listar y crear notas - Todos los usuarios activos
    Route::get('/', [NotaPedidoController::class, 'index'])
      ->name('index');
    Route::get('create', [NotaPedidoController::class, 'create'])
      ->name('create');
    Route::post('/', [NotaPedidoController::class, 'store'])
      ->name('store');

    // Ver y editar notas específicas
    Route::get('{notaPedido}', [NotaPedidoController::class, 'show'])
      ->name('show');
    Route::get('{notaPedido}/edit', [NotaPedidoController::class, 'edit'])
      ->name('edit');
    Route::put('{notaPedido}', [NotaPedidoController::class, 'update'])
      ->name('update');

    // Consultas - Todos los usuarios
    Route::get('consultas', [NotaPedidoController::class, 'consultas'])
      ->name('consultas');

    // Confirmar/Autorizar - Solo usuarios con permiso de autorización
    Route::middleware(['permission:authorize_notas'])->group(function () {
      Route::get('confirmar', [NotaPedidoController::class, 'confirmar'])
        ->name('confirmar');
      Route::patch('{notaPedido}/autorizar', [NotaPedidoController::class, 'autorizar'])
        ->name('autorizar');
    });
  });

  // Presupuestos - Solo admin, secretario, director
  Route::middleware(['role:administrador,secretario,director'])->group(function () {
    Route::resource('presupuestos', PresupuestoController::class);
  });

  // Órdenes de compra - Solo admin, secretario, director
  Route::middleware(['role:administrador,secretario,director'])->group(function () {
    Route::resource('ordenes-compra', OrdenCompraController::class);
  });

  // Reportes - Acceso según rol
  Route::prefix('reportes')->name('reportes.')->group(function () {

    // Reportes básicos - Todos los usuarios
    Route::get('/', [ReporteController::class, 'index'])
      ->name('index');
    Route::get('mis-notas', [ReporteController::class, 'misNotas'])
      ->name('mis-notas');

    // Reportes completos - Solo roles superiores
    Route::middleware(['permission:view_all_reports'])->group(function () {
      Route::get('todas-notas', [ReporteController::class, 'todasNotas'])
        ->name('todas-notas');
      Route::get('por-oficina', [ReporteController::class, 'porOficina'])
        ->name('por-oficina');
      Route::get('estadisticas', [ReporteController::class, 'estadisticas'])
        ->name('estadisticas');
    });
  });

  // API para obtener datos según permisos del usuario
  Route::prefix('api')->name('api.')->group(function () {

    // Oficinas disponibles para el usuario
    Route::get('mis-oficinas', [UserController::class, 'getMisOficinas'])
      ->name('mis-oficinas');

    // Tipos de nota activos
    Route::get('tipos-nota-activos', [TipoNotaController::class, 'getActivos'])
      ->name('tipos-nota-activos');
  });

});

// Ruta para perfil de usuario - Todos los usuarios autenticados
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  Route::get('perfil', [PerfilController::class, 'show'])
    ->name('perfil.show');
  Route::put('perfil', [PerfilController::class, 'update'])
    ->name('perfil.update');
});
