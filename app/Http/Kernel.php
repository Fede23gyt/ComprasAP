<?php


// app/Http/Kernel.php
// Agregar en el array $middlewareAliases (Laravel 11) o $routeMiddleware (Laravel 10)

protected
$middlewareAliases = [
  // ... otros middleware existentes

  // Middleware de roles y permisos
  'role' => \App\Http\Middleware\RoleMiddleware::class,
  'permission' => \App\Http\Middleware\PermissionMiddleware::class,
  'admin' => \App\Http\Middleware\AdminMiddleware::class,
];

// O si usas Laravel 10:
// protected $routeMiddleware = [
//     // ... otros middleware existentes
//     'role' => \App\Http\Middleware\RoleMiddleware::class,
//     'permission' => \App\Http\Middleware\PermissionMiddleware::class,
//     'admin' => \App\Http\Middleware\AdminMiddleware::class,
// ];
