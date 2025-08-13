<?php

// app/Http/Middleware/LogOficinaChanges.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogOficinaChanges
{
  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next): Response
  {
    $response = $next($request);

    // Solo logear cambios en oficinas
    if ($request->is('oficinas*') && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
      $this->logAction($request, $response);
    }

    return $response;
  }

  /**
   * Registrar la acción realizada
   */
  private function logAction(Request $request, Response $response): void
  {
    $user = $request->user();
    $method = $request->method();
    $path = $request->path();
    $ip = $request->ip();
    $userAgent = $request->userAgent();

    $logData = [
      'user_id' => $user?->id,
      'user_name' => $user?->name,
      'method' => $method,
      'path' => $path,
      'ip' => $ip,
      'user_agent' => $userAgent,
      'status_code' => $response->getStatusCode(),
      'timestamp' => now()->toISOString(),
    ];

    // Agregar datos específicos según la acción
    switch ($method) {
      case 'POST':
        $logData['action'] = 'create';
        $logData['data'] = $request->except(['password', 'password_confirmation']);
        break;
      case 'PUT':
      case 'PATCH':
        $logData['action'] = str_contains($path, 'toggle') ? 'toggle_status' : 'update';
        $logData['data'] = $request->except(['password', 'password_confirmation']);
        break;
      case 'DELETE':
        $logData['action'] = 'delete';
        break;
    }

    Log::channel('oficinas')->info('Oficina action logged', $logData);
  }
}
