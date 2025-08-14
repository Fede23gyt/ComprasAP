<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next, ...$roles): Response
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

    // Si no se especificaron roles, solo verificar que esté autenticado y activo
    if (empty($roles)) {
      return $next($request);
    }

    // Verificar que el usuario tenga uno de los roles requeridos
    if (!$user->hasAnyRole($roles)) {
      abort(403, 'No tienes permisos para acceder a esta sección.');
    }

    return $next($request);
  }
}
