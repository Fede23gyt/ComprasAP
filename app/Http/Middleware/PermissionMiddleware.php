<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next, ...$permissions): Response
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

    // Si no se especificaron permisos, solo verificar que esté autenticado y activo
    if (empty($permissions)) {
      return $next($request);
    }

    // Verificar que el usuario tenga al menos uno de los permisos requeridos
    $hasPermission = false;
    foreach ($permissions as $permission) {
      if ($user->hasPermission($permission)) {
        $hasPermission = true;
        break;
      }
    }

    if (!$hasPermission) {
      abort(403, 'No tienes los permisos necesarios para realizar esta acción.');
    }

    return $next($request);
  }
}
