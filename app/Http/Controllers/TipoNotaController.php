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
  public function index(Request $request)
  {
    $query = TipoNota::query()->orderBy('descripcion');

    // Filtro por búsqueda si existe
    if ($request->has('search') && !empty($request->search)) {
      $search = $request->search;
      $query->where('descripcion', 'ILIKE', "%{$search}%");
    }

    // Paginación de 15 elementos por página
    $tiposNota = $query->paginate(15)->withQueryString();

    return Inertia::render('TipoNota/Index', [
      'items' => $tiposNota,
      'filters' => $request->only(['search']),
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
        Rule::in(['Habilitado', 'No habilitado'])
      ]
    ], [
      'descripcion.required' => 'La descripción es obligatoria.',
      'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
      'descripcion.unique' => 'Ya existe un tipo de nota con esta descripción.',
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o No habilitado.'
    ]);

    try {
      $tipoNota = TipoNota::create($validated);

      if ($request->wantsJson()) {
        return response()->json([
          'success' => true,
          'message' => 'Tipo de nota creado correctamente.',
          'tipoNota' => $tipoNota
        ]);
      }

      return redirect()
        ->route('tipos-nota.index')
        ->with('message', 'Tipo de nota de pedido creado correctamente.')
        ->with('type', 'success');

    } catch (\Exception $e) {
      if ($request->wantsJson()) {
        return response()->json([
          'success' => false,
          'message' => 'Error al crear el tipo de nota: ' . $e->getMessage()
        ], 500);
      }

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
        Rule::in(['Habilitado', 'No habilitado'])
      ]
    ], [
      'descripcion.required' => 'La descripción es obligatoria.',
      'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
      'descripcion.unique' => 'Ya existe un tipo de nota con esta descripción.',
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o No habilitado.'
    ]);

    try {
      $tipoNota->update($validated);

      if ($request->wantsJson()) {
        return response()->json([
          'success' => true,
          'message' => 'Tipo de nota actualizado correctamente.',
          'tipoNota' => $tipoNota->fresh()
        ]);
      }

      return redirect()
        ->route('tipos-nota.index')
        ->with('message', 'Tipo de nota de pedido actualizado correctamente.')
        ->with('type', 'success');

    } catch (\Exception $e) {
      if ($request->wantsJson()) {
        return response()->json([
          'success' => false,
          'message' => 'Error al actualizar el tipo de nota: ' . $e->getMessage()
        ], 500);
      }

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
        Rule::in(['Habilitado', 'No habilitado'])
      ]
    ], [
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitado o No habilitado.'
    ]);

    try {
      $estadoAnterior = $tipoNota->estado;
      $tipoNota->update(['estado' => $validated['estado']]);

      $mensaje = $validated['estado'] === 'Habilitado'
        ? "Tipo de nota '{$tipoNota->descripcion}' habilitado correctamente."
        : "Tipo de nota '{$tipoNota->descripcion}' deshabilitado correctamente.";

      // Detectar si la solicitud espera JSON (AJAX)
      if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
          'success' => true,
          'message' => $mensaje,
          'tipoNota' => $tipoNota->fresh()
        ]);
      }

      return redirect()
        ->back()
        ->with('message', $mensaje)
        ->with('type', 'success');

    } catch (\Exception $e) {
      if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => 'Error al cambiar el estado: ' . $e->getMessage()
        ], 500);
      }

      return redirect()
        ->back()
        ->with('message', 'Error al cambiar el estado: ' . $e->getMessage())
        ->with('type', 'error');
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
   * Export tipos de nota to PDF or Excel
   */
  public function export(Request $request, $format)
  {
    // Validar formato
    if (!in_array($format, ['pdf', 'excel'])) {
      abort(400, 'Formato no válido');
    }

    // Construir la consulta
    $query = TipoNota::query()->orderBy('descripcion');

    // Aplicar filtros si existen
    if ($request->has('search') && !empty($request->search)) {
      $search = $request->search;
      $query->where('descripcion', 'ILIKE', "%{$search}%");
    }

    // Obtener todos los tipos de nota
    $tiposNota = $query->get();

    // Preparar nombre del archivo
    $filename = 'tipos_nota_pedido_' . now()->format('Y-m-d_H-i-s');

    if ($format === 'excel') {
      return $this->exportToExcel($tiposNota, $filename);
    } else {
      return $this->exportToPdf($tiposNota, $filename);
    }
  }

  /**
   * Export to Excel
   */
  protected function exportToExcel($tiposNota, $filename)
  {
    $headers = [
      'Content-Type' => 'text/csv; charset=utf-8',
      'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
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

  /**
   * Export to PDF
   */
  protected function exportToPdf($tiposNota, $filename)
  {
    // Aquí implementarías la generación del PDF
    // Por ejemplo, usando una librería como Dompdf, TCPDF o Snappy PDF

    // Ejemplo básico con Dompdf (necesitas instalar barryvdh/laravel-dompdf)
    /*
    $pdf = \PDF::loadView('pdf.tipos-nota', [
        'tiposNota' => $tiposNota,
        'fecha' => now()->format('d/m/Y H:i:s')
    ]);

    return $pdf->download($filename . '.pdf');
    */

    // Como alternativa temporal, exportamos a CSV
    return $this->exportToExcel($tiposNota, $filename);
  }
}
