<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
  /**
   * Handle an incoming request.
   * Middleware específico para funciones administrativas
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Verificar que el usuario esté autenticado
    if (!auth()->check()) {
      return redirect()->route('login');
    }

    $user = auth()->user();

    // Verificar que el usuario esté activo
    if (!$user->isActive()) {
      auth()->logout();
      return redirect()->route('login')
        ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
    }

    // Verificar que el usuario pueda administrar configuración
    if (!$user->canManageConfig()) {
      abort(403, 'No tienes permisos de administrador para acceder a esta sección.');
    }

    return $next($request);
  }
}
