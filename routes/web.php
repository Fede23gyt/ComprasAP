<?php

// routes/web.php - Rutas protegidas con middleware

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\TipoNotaController;
use App\Http\Controllers\TipoCompraController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotaPedidoController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

  // Dashboard dinámico basado en rol
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // Reportes administrativos
    Route::prefix('reports')->name('reports.')->group(function () {
      Route::get('/', [ReportController::class, 'index'])
        ->name('index');
      Route::get('users', [ReportController::class, 'users'])
        ->name('users');
      Route::get('roles', [ReportController::class, 'roles'])
        ->name('roles');
      Route::get('offices', [ReportController::class, 'offices'])
        ->name('offices');
      Route::get('activity', [ReportController::class, 'activity'])
        ->name('activity');
      Route::get('export/users', [ReportController::class, 'exportUsers'])
        ->name('export.users');
    });

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

  // Rutas para nomencladores CRUD - Todos los usuarios autenticados
  Route::prefix('nomencladores')->name('nomencladores.')->group(function () {

    // Nomencladores con CRUD completo
    Route::resource('oficinas', OficinaController::class);
    Route::patch('oficinas/{oficina}/toggle', [OficinaController::class, 'toggle'])->name('oficinas.toggle');

    Route::resource('tipos-nota', TipoNotaController::class);
    Route::patch('tipos-nota/{tipoNota}/toggle', [TipoNotaController::class, 'toggleEstado'])->name('tipos-nota.toggle');
    Route::get('tipos-nota/export/{format}', [TipoNotaController::class, 'export'])->name('tipos-nota.export');

    Route::resource('tipos-compra', TipoCompraController::class);
    Route::patch('tipos-compra/{tipoCompra}/toggle', [TipoCompraController::class, 'toggleEstado'])->name('tipos-compra.toggle');
    Route::get('tipos-compra/export/{format}', [TipoCompraController::class, 'export'])->name('tipos-compra.export');

    // Nomenclador de insumos con funcionalidad completa
    Route::get('insumos', [\App\Http\Controllers\InsumoController::class, 'index'])->name('insumos.index');
    Route::get('insumos/{insumo}', [\App\Http\Controllers\InsumoController::class, 'show'])->name('insumos.show');
    Route::get('insumos/{insumo}/edit', [\App\Http\Controllers\InsumoController::class, 'edit'])->name('insumos.edit');
    Route::put('insumos/{insumo}', [\App\Http\Controllers\InsumoController::class, 'update'])->name('insumos.update');
    Route::get('insumos/export/{format}', [\App\Http\Controllers\InsumoController::class, 'export'])->name('insumos.export');
  });

  // Rutas de notas de pedido - Todos los usuarios autenticados
  Route::prefix('notas-pedido')->name('notas-pedido.')->group(function () {

    // CRUD básico - Todos los usuarios activos
    Route::get('/', [NotaPedidoController::class, 'index'])->name('index');
    Route::get('create', [NotaPedidoController::class, 'create'])->name('create');
    Route::post('/', [NotaPedidoController::class, 'store'])->name('store');
    Route::get('{notaPedido}', [NotaPedidoController::class, 'show'])->name('show');
    Route::get('{notaPedido}/edit', [NotaPedidoController::class, 'edit'])->name('edit');
    Route::put('{notaPedido}', [NotaPedidoController::class, 'update'])->name('update');

    // Acciones de estado - Todos los usuarios pueden confirmar sus notas
    Route::patch('{notaPedido}/confirmar', [NotaPedidoController::class, 'confirmar'])->name('confirmar');
    Route::patch('{notaPedido}/rechazar', [NotaPedidoController::class, 'rechazar'])->name('rechazar');
    
    // Reabrir nota - Solo administradores y supervisores
    Route::middleware(['role:administrador,supervisor'])->group(function () {
      Route::patch('{notaPedido}/reabrir', [NotaPedidoController::class, 'reabrir'])->name('reabrir');
    });

    // API para búsqueda de insumos
    Route::get('api/buscar-insumos', [NotaPedidoController::class, 'buscarInsumos'])->name('api.buscar-insumos');
  });

  // Presupuestos - Solo admin, secretario, director
  Route::middleware(['role:administrador,secretario,director'])->group(function () {
    Route::get('presupuestos', function () {
      return \Inertia\Inertia::render('Presupuestos/Index', ['title' => 'Presupuestos']);
    })->name('presupuestos.index');
  });

  // Órdenes de compra - Solo admin, secretario, director
  Route::middleware(['role:administrador,secretario,director'])->group(function () {
    Route::get('ordenes-compra', function () {
      return \Inertia\Inertia::render('OrdenesCompra/Index', ['title' => 'Órdenes de Compra']);
    })->name('ordenes-compra.index');
  });

  // Reportes - Acceso según rol
  Route::prefix('reportes')->name('reportes.')->group(function () {

    // Reportes básicos - Todos los usuarios
    Route::get('/', function () {
      return \Inertia\Inertia::render('Reportes/Index', ['title' => 'Reportes']);
    })->name('index');
    Route::get('mis-notas', function () {
      return \Inertia\Inertia::render('Reportes/MisNotas', ['title' => 'Mis Notas']);
    })->name('mis-notas');

    // Reportes completos - Solo roles superiores
    Route::middleware(['role:administrador,secretario,director'])->group(function () {
      Route::get('todas-notas', function () {
        return \Inertia\Inertia::render('Reportes/TodasNotas', ['title' => 'Todas las Notas']);
      })->name('todas-notas');
      Route::get('por-oficina', function () {
        return \Inertia\Inertia::render('Reportes/PorOficina', ['title' => 'Reportes por Oficina']);
      })->name('por-oficina');
      Route::get('estadisticas', function () {
        return \Inertia\Inertia::render('Reportes/Estadisticas', ['title' => 'Estadísticas']);
      })->name('estadisticas');
    });
  });

  // API para obtener datos según permisos del usuario
  Route::prefix('api')->name('api.')->group(function () {

    // Oficinas disponibles para el usuario
    Route::get('mis-oficinas', function () {
      return response()->json([]);
    })->name('mis-oficinas');

    // Tipos de nota activos
    Route::get('tipos-nota-activos', function () {
      return response()->json([]);
    })->name('tipos-nota-activos');
  });

  // DEBUG: Ruta temporal para verificar datos del usuario
  Route::get('debug/user', function () {
    return \Inertia\Inertia::render('Debug/UserInfo');
  })->name('debug.user');

});

require __DIR__.'/auth.php';

// Rutas para perfil de usuario - Todos los usuarios autenticados
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  Route::get('profile', function () {
    return \Inertia\Inertia::render('Profile/Show', ['title' => 'Mi Perfil']);
  })->name('profile.show');
  
  Route::get('profile/edit', function () {
    return \Inertia\Inertia::render('Profile/Edit', ['title' => 'Editar Perfil']);
  })->name('profile.edit');
  
  // Ruta en español también
  Route::get('perfil', function () {
    return \Inertia\Inertia::render('Profile/Show', ['title' => 'Mi Perfil']);
  })->name('perfil.show');
});
