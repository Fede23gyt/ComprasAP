<?php

namespace App\Http\Controllers;

use App\Models\TipoNota;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TipoNotaController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $tiposNota = TipoNota::orderBy('descripcion')->get();

    return Inertia::render('TipoNota/Index', [
      'items' => $tiposNota
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return Inertia::render('TipoNota/Create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'descripcion' => [
        'required',
        'string',
        'max:255',
        'unique:tipo_nota,descripcion'
      ],
      'estado' => [
        'required',
        'in:Habilitado,No Habilitado'
      ]
    ], [
      'descripcion.required' => 'La descripción es obligatoria.',
      'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
      'descripcion.unique' => 'Ya existe un tipo de nota con esta descripción.',
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o No Habilitado.'
    ]);

    try {
      TipoNota::create($validated);

      return redirect()
        ->route('tipos-nota.index')
        ->with('message', 'Tipo de nota de pedido creado correctamente.')
        ->with('type', 'success');

    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('message', 'Error al crear el tipo de nota: ' . $e->getMessage())
        ->with('type', 'error');
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(TipoNota $tipoNota)
  {
    return Inertia::render('TipoNota/Show', [
      'tipoNota' => $tipoNota
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(TipoNota $tipoNota)
  {
    return Inertia::render('TipoNota/Edit', [
      'tipoNota' => $tipoNota
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, TipoNota $tipoNota)
  {
    $validated = $request->validate([
      'descripcion' => [
        'required',
        'string',
        'max:255',
        Rule::unique('tipo_nota', 'descripcion')->ignore($tipoNota->id)
      ],
      'estado' => [
        'required',
        'in:Habilitado,No Habilitado'
      ]
    ], [
      'descripcion.required' => 'La descripción es obligatoria.',
      'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
      'descripcion.unique' => 'Ya existe un tipo de nota con esta descripción.',
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o No Habilitado.'
    ]);

    try {
      $tipoNota->update($validated);

      return redirect()
        ->route('tipos-nota.index')
        ->with('message', 'Tipo de nota de pedido actualizado correctamente.')
        ->with('type', 'success');

    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('message', 'Error al actualizar el tipo de nota: ' . $e->getMessage())
        ->with('type', 'error');
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(TipoNota $tipoNota)
  {
    try {
      // Verificar si el tipo de nota está siendo usado
      /*
      if ($tipoNota->notasPedido()->exists()) {
          return redirect()
              ->back()
              ->with('message', 'No se puede eliminar este tipo de nota porque está siendo utilizado en notas de pedido existentes.')
              ->with('type', 'error');
      }
      */

      $descripcion = $tipoNota->descripcion;
      $tipoNota->delete();

      return redirect()
        ->route('tipos-nota.index')
        ->with('message', "Tipo de nota '{$descripcion}' eliminado correctamente.")
        ->with('type', 'success');

    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('message', 'Error al eliminar el tipo de nota: ' . $e->getMessage())
        ->with('type', 'error');
    }
  }

  /**
   * Toggle the estado field of the specified resource.
   */
  public function toggleEstado(Request $request, TipoNota $tipoNota)
  {
    $validated = $request->validate([
      'estado' => [
        'required',
        'in:Habilitado,No habilitado'
      ]
    ], [
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o No Habilitado.'
    ]);

    try {
      $estadoAnterior = $tipoNota->estado;
      $tipoNota->update(['estado' => $validated['estado']]);

      $mensaje = $validated['estado'] === 'Habilitado'
        ? "Tipo de nota '{$tipoNota->descripcion}' habilitado correctamente."
        : "Tipo de nota '{$tipoNota->descripcion}' deshabilitado correctamente.";

      // Devolver JSON en lugar de redirect
      return response()->json([
        'success' => true,
        'message' => $mensaje,
        'tipoNota' => $tipoNota->fresh()
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al cambiar el estado: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get all active tipos de nota for selects/dropdowns
   */
  public function getActivos()
  {
    $tiposNota = TipoNota::where('estado', 'Habilitado')
      ->orderBy('descripcion')
      ->get(['id', 'descripcion']);

    return response()->json($tiposNota);
  }

  /**
   * Bulk update estados
   */
  public function bulkToggleEstado(Request $request)
  {
    $validated = $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:tipo_nota,id',
      'estado' => 'required|in:Habilitado,No habilitado'
    ]);

    try {
      $count = TipoNota::whereIn('id', $validated['ids'])
        ->update(['estado' => $validated['estado']]);

      $accion = $validated['estado'] === 'Habilitado' ? 'habilitados' : 'No habilitado';
      $mensaje = "{$count} tipos de nota {$accion} correctamente.";

      return redirect()
        ->back()
        ->with('message', $mensaje)
        ->with('type', 'success');

    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('message', 'Error en la operación masiva: ' . $e->getMessage())
        ->with('type', 'error');
    }
  }

  /**
   * Check if descripcion is unique (for real-time validation)
   */
  public function checkUniqueDescripcion(Request $request)
  {
    $descripcion = $request->get('descripcion');
    $excludeId = $request->get('exclude_id');

    $query = TipoNota::where('descripcion', $descripcion);

    if ($excludeId) {
      $query->where('id', '!=', $excludeId);
    }

    $exists = $query->exists();

    return response()->json([
      'available' => !$exists,
      'message' => $exists ? 'Esta descripción ya está en uso.' : 'Descripción disponible.'
    ]);
  }

  /**
   * Get statistics for dashboard
   */
  public function getStats()
  {
    $stats = [
      'total' => TipoNota::count(),
      'habilitados' => TipoNota::where('estado', 'Habilitado')->count(),
      'No Habilitado' => TipoNota::where('estado', 'No habilitado')->count(),
      'recientes' => TipoNota::where('created_at', '>=', now()->subDays(30))->count()
    ];

    return response()->json($stats);
  }

  /**
   * Export tipos de nota to CSV
   */
  public function export(Request $request)
  {
    $tiposNota = TipoNota::orderBy('descripcion')->get();

    $filename = 'tipos_nota_pedido_' . now()->format('Y-m-d_H-i-s') . '.csv';

    $headers = [
      'Content-Type' => 'text/csv; charset=utf-8',
      'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function() use ($tiposNota) {
      $file = fopen('php://output', 'w');

      // BOM para UTF-8
      fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

      // Headers CSV
      fputcsv($file, ['ID', 'Descripción', 'Estado', 'Creado', 'Actualizado']);

      // Data
      foreach ($tiposNota as $tipo) {
        fputcsv($file, [
          $tipo->id,
          $tipo->descripcion,
          $tipo->estado,
          $tipo->created_at?->format('d/m/Y H:i:s'),
          $tipo->updated_at?->format('d/m/Y H:i:s')
        ]);
      }

      fclose($file);
    };

    return response()->stream($callback, 200, $headers);
  }
}
