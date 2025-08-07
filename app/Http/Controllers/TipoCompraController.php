<?php

namespace App\Http\Controllers;

use App\Models\TipoCompra;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TipoCompraController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $tiposCompras = TipoCompra::orderBy('descripcion')->get();

    return Inertia::render('TipoCompra/Index', [
      'items' => $tiposCompras
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return Inertia::render('TiposCompras/Create');
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
        'unique:tipo_compra,descripcion'
      ],
      'estado' => [
        'required',
        'in:Habilitado,Deshabilitado'
      ]
    ], [
      'descripcion.required' => 'La descripción es obligatoria.',
      'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
      'descripcion.unique' => 'Ya existe un tipo de compra con esta descripción.',
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o Deshabilitado.'
    ]);

    try {
      TipoCompra::create($validated);

      return redirect()
        ->route('tipos-compras.index')
        ->with('message', 'Tipo de compra creado correctamente.')
        ->with('type', 'success');

    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('message', 'Error al crear el tipo de compra: ' . $e->getMessage())
        ->with('type', 'error');
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(TipoCompra $tipoCompra)
  {
    return Inertia::render('TiposCompras/Show', [
      'tipoCompra' => $tipoCompra
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(TipoCompra $tipoCompra)
  {
    return Inertia::render('TiposCompras/Edit', [
      'tipoCompra' => $tipoCompra
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, TipoCompra $tipoCompra)
  {
    $validated = $request->validate([
      'descripcion' => [
        'required',
        'string',
        'max:255',
        Rule::unique('tipo_compra', 'descripcion')->ignore($tipoCompra->id)
      ],
      'estado' => [
        'required',
        'in:Habilitado,Deshabilitado'
      ]
    ], [
      'descripcion.required' => 'La descripción es obligatoria.',
      'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
      'descripcion.unique' => 'Ya existe un tipo de compra con esta descripción.',
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o Deshabilitado.'
    ]);

    try {
      $tipoCompra->update($validated);

      return redirect()
        ->route('tipos-compras.index')
        ->with('message', 'Tipo de compra actualizado correctamente.')
        ->with('type', 'success');

    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('message', 'Error al actualizar el tipo de compra: ' . $e->getMessage())
        ->with('type', 'error');
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(TipoCompra $tipoCompra)
  {
    try {
      // Verificar si el tipo de compra está siendo usado en otras tablas
      // Ejemplo: si tiene relación con compras
      /*
      if ($tipoCompra->compras()->exists()) {
          return redirect()
              ->back()
              ->with('message', 'No se puede eliminar este tipo de compra porque está siendo utilizado en compras existentes.')
              ->with('type', 'error');
      }
      */

      $descripcion = $tipoCompra->descripcion;
      $tipoCompra->delete();

      return redirect()
        ->route('tipos-compras.index')
        ->with('message', "Tipo de compra '{$descripcion}' eliminado correctamente.")
        ->with('type', 'success');

    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('message', 'Error al eliminar el tipo de compra: ' . $e->getMessage())
        ->with('type', 'error');
    }
  }

  /**
   * Toggle the estado field of the specified resource.
   */
  public function toggleEstado(Request $request, TipoCompra $tipoCompra)
  {
    $validated = $request->validate([
      'estado' => [
        'required',
        'in:Habilitado,Deshabilitado'
      ]
    ], [
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o Deshabilitado.'
    ]);

    try {
      $estadoAnterior = $tipoCompra->estado;
      $tipoCompra->update(['estado' => $validated['estado']]);

      $mensaje = $validated['estado'] === 'Habilitado'
        ? "Tipo de compra '{$tipoCompra->descripcion}' habilitado correctamente."
        : "Tipo de compra '{$tipoCompra->descripcion}' deshabilitado correctamente.";

      return redirect()
        ->back()
        ->with('message', $mensaje)
        ->with('type', 'success');

    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('message', 'Error al cambiar el estado: ' . $e->getMessage())
        ->with('type', 'error');
    }
  }

  /**
   * Get all active tipos de compra for selects/dropdowns
   */
  public function getActivos()
  {
    $tiposCompras = TipoCompra::where('estado', 'Habilitado')
      ->orderBy('descripcion')
      ->get(['id', 'descripcion']);

    return response()->json($tiposCompras);
  }

  /**
   * Bulk update estados
   */
  public function bulkToggleEstado(Request $request)
  {
    $validated = $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:tipos_compras,id',
      'estado' => 'required|in:Habilitado,Deshabilitado'
    ]);

    try {
      $count = TipoCompra::whereIn('id', $validated['ids'])
        ->update(['estado' => $validated['estado']]);

      $accion = $validated['estado'] === 'Habilitado' ? 'habilitados' : 'deshabilitados';
      $mensaje = "{$count} tipos de compra {$accion} correctamente.";

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

    $query = TipoCompra::where('descripcion', $descripcion);

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
      'total' => TipoCompra::count(),
      'habilitados' => TipoCompra::where('estado', 'Habilitado')->count(),
      'deshabilitados' => TipoCompra::where('estado', 'Deshabilitado')->count(),
      'recientes' => TipoCompra::where('created_at', '>=', now()->subDays(30))->count()
    ];

    return response()->json($stats);
  }

  /**
   * Export tipos de compra to CSV/Excel
   */
  public function export(Request $request)
  {
    $format = $request->get('format', 'csv'); // csv, excel

    $tiposCompras = TipoCompra::orderBy('descripcion')->get();

    if ($format === 'csv') {
      $filename = 'tipos_compras_' . now()->format('Y-m-d_H-i-s') . '.csv';

      $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
      ];

      $callback = function() use ($tiposCompras) {
        $file = fopen('php://output', 'w');

        // Headers CSV
        fputcsv($file, ['ID', 'Descripción', 'Estado', 'Creado', 'Actualizado']);

        // Data
        foreach ($tiposCompras as $tipo) {
          fputcsv($file, [
            $tipo->id,
            $tipo->descripcion,
            $tipo->estado,
            $tipo->created_at?->format('Y-m-d H:i:s'),
            $tipo->updated_at?->format('Y-m-d H:i:s')
          ]);
        }

        fclose($file);
      };

      return response()->stream($callback, 200, $headers);
    }

    // Para Excel necesitarías una librería como Laravel Excel
    return redirect()->back()->with('message', 'Formato no soportado')->with('type', 'error');
  }
}
