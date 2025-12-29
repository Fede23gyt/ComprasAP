<?php

// routes/web.php - Rutas protegidas con middleware

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\TipoNotaController;
use App\Http\Controllers\TipoCompraController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotaPedidoController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\OfertaController;

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

    Route::resource('tipos-nota', TipoNotaController::class)->parameters(['tipos-nota' => 'tipoNota']);
    Route::patch('tipos-nota/{tipoNota}/toggle', [TipoNotaController::class, 'toggleEstado'])->name('tipos-nota.toggle');
    Route::get('tipos-nota/export/{format}', [TipoNotaController::class, 'export'])->name('tipos-nota.export');

    Route::resource('tipos-compra', TipoCompraController::class)->parameters(['tipos-compra' => 'tipoCompra']);
    Route::patch('tipos-compra/{tipoCompra}/toggle', [TipoCompraController::class, 'toggleEstado'])->name('tipos-compra.toggle');
    Route::get('tipos-compra/export/{format}', [TipoCompraController::class, 'export'])->name('tipos-compra.export');

    Route::resource('memos', MemoController::class)->parameters(['memos' => 'memo']);

    // Nomenclador de insumos con funcionalidad completa
    Route::get('insumos', [\App\Http\Controllers\InsumoController::class, 'index'])->name('insumos.index');
    Route::get('insumos/{insumo}', [\App\Http\Controllers\InsumoController::class, 'show'])->name('insumos.show');
    Route::get('insumos/{insumo}/edit', [\App\Http\Controllers\InsumoController::class, 'edit'])->name('insumos.edit');
    Route::put('insumos/{insumo}', [\App\Http\Controllers\InsumoController::class, 'update'])->name('insumos.update');
    Route::get('insumos/export/{format}', [\App\Http\Controllers\InsumoController::class, 'export'])->name('insumos.export');

    // Gestión de proveedores
    Route::resource('proveedores', \App\Http\Controllers\ProveedorController::class);
  });

  // Rutas de notas de pedido - Todos los usuarios autenticados
  Route::prefix('notas-pedido')->name('notas-pedido.')->group(function () {

    // CRUD básico - Todos los usuarios activos
    Route::get('/', [NotaPedidoController::class, 'index'])->name('index');
    Route::get('mis-notas', [NotaPedidoController::class, 'misNotas'])->name('mis-notas');
    Route::get('create', [NotaPedidoController::class, 'create'])->name('create');
    Route::post('/', [NotaPedidoController::class, 'store'])->name('store');
    Route::get('{notaPedido}', [NotaPedidoController::class, 'show'])->name('show');
    Route::get('{notaPedido}/edit', [NotaPedidoController::class, 'edit'])->name('edit');
    Route::put('{notaPedido}', [NotaPedidoController::class, 'update'])->name('update');

    // Acciones de estado - Todos los usuarios pueden confirmar sus notas
    Route::patch('{notaPedido}/confirmar', [NotaPedidoController::class, 'confirmar'])->name('confirmar');
    Route::patch('{notaPedido}/rechazar', [NotaPedidoController::class, 'rechazar'])->name('rechazar');
    
    // PDF de nota de pedido
    Route::get('{notaPedido}/pdf', [NotaPedidoController::class, 'pdf'])->name('pdf');
    
    // Notas por confirmar - Solo usuarios que pueden autorizar
    Route::middleware(['role:administrador,supervisor,secretario,director'])->group(function () {
      Route::get('por-confirmar', [NotaPedidoController::class, 'porConfirmar'])->name('por-confirmar');
    });
    
    // Reabrir nota - Solo administradores y supervisores
    Route::middleware(['role:administrador,supervisor'])->group(function () {
      Route::patch('{notaPedido}/reabrir', [NotaPedidoController::class, 'reabrir'])->name('reabrir');
    });

    // API para búsqueda de insumos
    Route::get('api/buscar-insumos', [NotaPedidoController::class, 'buscarInsumos'])->name('api.buscar-insumos');
  });

  // Presupuestos - Solo admin, secretario, director
  Route::middleware(['role:administrador,secretario,director'])->prefix('presupuestos')->name('presupuestos.')->group(function () {
    Route::get('/', [PresupuestoController::class, 'index'])->name('index');
    Route::get('create', [PresupuestoController::class, 'create'])->name('create');
    Route::post('/', [PresupuestoController::class, 'store'])->name('store');
    Route::get('{presupuesto}', [PresupuestoController::class, 'show'])->name('show');
    Route::get('{presupuesto}/edit', [PresupuestoController::class, 'edit'])->name('edit');
    Route::put('{presupuesto}', [PresupuestoController::class, 'update'])->name('update');
    
    // Acciones de estado
    Route::post('{presupuesto}/enviar', [PresupuestoController::class, 'enviar'])->name('enviar'); // Confirmar presupuesto
    Route::post('{presupuesto}/aprobar', [PresupuestoController::class, 'aprobar'])->name('aprobar')->middleware('role:director,administrador'); // Directores y administradores
    Route::post('{presupuesto}/rechazar', [PresupuestoController::class, 'rechazar'])->name('rechazar')->middleware('role:director,administrador'); // Directores y administradores
    Route::post('{presupuesto}/marcar-desierto', [PresupuestoController::class, 'marcarDesierto'])->name('marcar-desierto');
    Route::post('{presupuesto}/cerrar', [PresupuestoController::class, 'cerrar'])->name('cerrar');
    Route::post('{presupuesto}/adjudicar', [PresupuestoController::class, 'adjudicar'])->name('adjudicar');
    Route::post('{presupuesto}/adjudicar-parcial', [PresupuestoController::class, 'adjudicarParcial'])->name('adjudicar-parcial');
    Route::post('{presupuesto}/reabrir', [PresupuestoController::class, 'reabrir'])->name('reabrir');
    Route::post('{presupuesto}/anular', [PresupuestoController::class, 'anular'])->name('anular');
    
    // Rutas específicas
    Route::get('nota-pedido/{notaPedido}', [PresupuestoController::class, 'porNotaPedido'])->name('por-nota-pedido');
    Route::get('comparar/{notaPedido}', [PresupuestoController::class, 'comparar'])->name('comparar');

    // Rutas para gestión de ofertas
    Route::get('{presupuesto}/ofertas', [\App\Http\Controllers\OfertaController::class, 'ofertasPresupuesto'])->name('ofertas');
    Route::get('{presupuesto}/evaluar-ofertas', [\App\Http\Controllers\OfertaController::class, 'evaluarOfertas'])->name('evaluar-ofertas');
    Route::post('{presupuesto}/adjudicar-ofertas', [\App\Http\Controllers\OfertaController::class, 'adjudicarOfertas'])->name('adjudicar-ofertas');
    Route::post('{presupuesto}/seleccionar-ofertas', [\App\Http\Controllers\OfertaController::class, 'seleccionarOfertas'])->name('seleccionar-ofertas');
    Route::post('{presupuesto}/generar-ordenes-compra', [PresupuestoController::class, 'generarOrdenesCompra'])->name('generar-ordenes-compra');
    
    // API para búsqueda de proveedores
    Route::get('api/buscar-proveedores', [PresupuestoController::class, 'buscarProveedores'])->name('api.buscar-proveedores');
    Route::get('api/{presupuesto}/detalles', [PresupuestoController::class, 'obtenerDetalles'])->name('api.detalles');

    // PDF
    Route::get('{presupuesto}/pdf', [PresupuestoController::class, 'pdf'])->name('pdf');
  });

  // Ofertas - Gestión completa de ofertas
  Route::middleware(['role:administrador,secretario,director,operador'])->prefix('ofertas')->name('ofertas.')->group(function () {
    Route::get('/', [\App\Http\Controllers\OfertaController::class, 'index'])->name('index');
    Route::get('create', [\App\Http\Controllers\OfertaController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\OfertaController::class, 'store'])->name('store');
    Route::get('{oferta}', [\App\Http\Controllers\OfertaController::class, 'show'])->name('show');
    Route::get('{oferta}/edit', [\App\Http\Controllers\OfertaController::class, 'edit'])->name('edit');
    Route::put('{oferta}', [\App\Http\Controllers\OfertaController::class, 'update'])->name('update');
    Route::delete('{oferta}', [\App\Http\Controllers\OfertaController::class, 'destroy'])->name('destroy');

    // Acciones adicionales para ofertas
    Route::post('{oferta}/anular', [\App\Http\Controllers\OfertaController::class, 'anular'])->name('anular');
    Route::patch('{oferta}/cambiar-estado', [\App\Http\Controllers\OfertaController::class, 'cambiarEstado'])->name('cambiar-estado');

    // API
    Route::get('api/buscar', [\App\Http\Controllers\OfertaController::class, 'apiBuscar'])->name('api.buscar');
    Route::get('api/por-presupuesto/{presupuesto}', [\App\Http\Controllers\OfertaController::class, 'porPresupuesto'])->name('api.por-presupuesto');
  });

  // Órdenes de compra - Solo admin, secretario, director
  Route::middleware(['role:administrador,secretario,director'])->prefix('ordenes-compra')->name('ordenes-compra.')->group(function () {
    Route::get('/', [\App\Http\Controllers\OrdenCompraController::class, 'index'])->name('index');
    Route::get('create', [\App\Http\Controllers\OrdenCompraController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\OrdenCompraController::class, 'store'])->name('store');
    Route::get('{ordenCompra}', [\App\Http\Controllers\OrdenCompraController::class, 'show'])->name('show');
    Route::get('{ordenCompra}/edit', [\App\Http\Controllers\OrdenCompraController::class, 'edit'])->name('edit');
    Route::put('{ordenCompra}', [\App\Http\Controllers\OrdenCompraController::class, 'update'])->name('update');
    Route::delete('{ordenCompra}', [\App\Http\Controllers\OrdenCompraController::class, 'destroy'])->name('destroy');
    
    // Acciones de estado
    Route::post('{ordenCompra}/enviar-aprobacion', [\App\Http\Controllers\OrdenCompraController::class, 'enviarAprobacion'])->name('enviar-aprobacion');
    Route::post('{ordenCompra}/aprobar', [\App\Http\Controllers\OrdenCompraController::class, 'aprobar'])->name('aprobar');
    Route::post('{ordenCompra}/rechazar', [\App\Http\Controllers\OrdenCompraController::class, 'rechazar'])->name('rechazar');
    Route::post('{ordenCompra}/enviar-proveedor', [\App\Http\Controllers\OrdenCompraController::class, 'enviarProveedor'])->name('enviar-proveedor');
    Route::post('{ordenCompra}/registrar-recepcion', [\App\Http\Controllers\OrdenCompraController::class, 'registrarRecepcion'])->name('registrar-recepcion');
    Route::post('{ordenCompra}/registrar-factura', [\App\Http\Controllers\OrdenCompraController::class, 'registrarFactura'])->name('registrar-factura');
    Route::post('{ordenCompra}/registrar-pago', [\App\Http\Controllers\OrdenCompraController::class, 'registrarPago'])->name('registrar-pago');
    Route::post('{ordenCompra}/anular', [\App\Http\Controllers\OrdenCompraController::class, 'anular'])->name('anular');

    // PDF
    Route::get('{ordenCompra}/pdf', [\App\Http\Controllers\OrdenCompraController::class, 'imprimirPdf'])->name('pdf');
  });

  // Reportes - Acceso según rol
  Route::prefix('reportes')->name('reportes.')->group(function () {

    // Reportes principales - Accesible para todos los roles con permisos
    Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');

    // Reportes específicos - Solo para roles superiores
    Route::middleware(['role:administrador,secretario,director'])->group(function () {
      Route::get('notas-por-oficina', [\App\Http\Controllers\ReportController::class, 'notasPorOficina'])->name('notas-por-oficina');
      Route::get('proveedores-ofertas', [\App\Http\Controllers\ReportController::class, 'proveedoresConOfertas'])->name('proveedores-ofertas');
      Route::get('ordenes-por-estado', [\App\Http\Controllers\ReportController::class, 'ordenesCompraPorEstado'])->name('ordenes-por-estado');
      Route::get('insumos-solicitados', [\App\Http\Controllers\ReportController::class, 'insumosMasSolicitados'])->name('insumos-solicitados');
      Route::get('exportar-csv', [\App\Http\Controllers\ReportController::class, 'exportarCSV'])->name('exportar-csv');
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

// Ruta raíz - Redirige al dashboard si está autenticado, sino al login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

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
