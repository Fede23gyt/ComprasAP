<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreInsumoRequest; // Asegúrate de que este Request exista y sea correcto
use App\Models\Insumo;
use App\Models\ClasifEconomica;
use Illuminate\Database\Eloquent\Model;
use Inertia\Inertia;
use Inertia\Response;
use function PHPUnit\Framework\isTrue;

class InsumoController extends Controller
{
    /**
     * Muestra la lista de insumos
     */
    public function index(Request $request)
    {
      $search = $request->get('search', '');

      // Query base con la relación
      $query = Insumo::with('clasifEconomica');

      // Aplicar filtros de búsqueda si existe
      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where('codigo', 'LIKE', "%{$search}%")
            ->orWhere('descripcion', 'LIKE', "%{$search}%")
            ->orWhere('clasificacion', 'LIKE', "%{$search}%")
            ->orWhereHas('clasifEconomica', function ($subq) use ($search) {
              $subq->where('descripcion', 'LIKE', "%{$search}%");
            });
        });
      }

      // Paginación
      $insumos = $query->orderBy('descripcion')
        ->paginate(15)
        ->withQueryString(); // Mantener parámetros de búsqueda

      return Inertia::render('Insumos/Index', [
        'insumos' => $insumos,
        'padres' => [] // Si necesitas esta prop para algo más
      ]);
    }

    /**
     * Guarda un nuevo insumo en la base de datos.
     */
    public function store(StoreInsumoRequest $request)
    {
      $validated = $request->validate([
        'codigo' => 'required|string|max:50|unique:insumos,codigo',
        'descripcion' => 'required|string|max:500',
        'clasificacion' => 'nullable|string|max:20',
        'registrable' => 'boolean',
        'presentacion' => 'nullable|string|max:50',
        'unidad_solicitud' => 'nullable|string|max:50',
        'precio_insumo' => 'nullable|numeric|min:0|max:999999999.99',
        'precio_testigo' => 'boolean',
        'inventariable' => 'boolean',
        'rend_tribunal' => 'boolean',
        'conversion' => 'nullable|numeric|min:0|max:9999.9999',
        'fecha_ultima' => 'nullable|date',
      ], [
        'codigo.required' => 'El código es obligatorio.',
        'codigo.unique' => 'Ya existe un insumo con este código.',
        'codigo.max' => 'El código no puede exceder los 50 caracteres.',
        'descripcion.required' => 'La descripción es obligatoria.',
        'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.',
        'clasificacion.max' => 'La clasificación no puede exceder los 20 caracteres.',
        'presentacion.max' => 'La presentación no puede exceder los 50 caracteres.',
        'unidad_solicitud.max' => 'La unidad de solicitud no puede exceder los 50 caracteres.',
        'precio_insumo.numeric' => 'El precio debe ser un número válido.',
        'precio_insumo.min' => 'El precio no puede ser negativo.',
        'precio_insumo.max' => 'El precio excede el límite permitido.',
        'conversion.numeric' => 'La conversión debe ser un número válido.',
        'conversion.min' => 'La conversión no puede ser negativa.',
        'conversion.max' => 'La conversión excede el límite permitido.',
        'fecha_ultima.date' => 'La fecha debe ser una fecha válida.',
      ]);

      try {
        Insumo::create($validated);

        return redirect()
          ->route('insumos.index')
          ->with('message', 'Insumo creado correctamente.')
          ->with('type', 'success');

      } catch (\Exception $e) {
        return redirect()
          ->back()
          ->withInput()
          ->with('message', 'Error al crear el insumo: ' . $e->getMessage())
          ->with('type', 'error');
      }
    }

    /**
     * Actualiza un insumo existente.
     */
    public function update(StoreInsumoRequest $request, Insumo $insumo)
    {
      $validated = $request->validate([
        'codigo' => 'required|string|max:50|unique:insumos,codigo,' . $insumo->id,
        'descripcion' => 'required|string|max:500',
        'clasificacion' => 'nullable|string|max:20',
        'registrable' => 'boolean',
        'presentacion' => 'nullable|string|max:50',
        'unidad_solicitud' => 'nullable|string|max:50',
        'precio_insumo' => 'nullable|numeric|min:0|max:999999999.99',
        'precio_testigo' => 'boolean',
        'inventariable' => 'boolean',
        'rend_tribunal' => 'boolean',
        'conversion' => 'nullable|numeric|min:0|max:9999.9999',
        'fecha_ultima' => 'nullable|date',
      ], [
        'codigo.required' => 'El código es obligatorio.',
        'codigo.unique' => 'Ya existe un insumo con este código.',
        'codigo.max' => 'El código no puede exceder los 50 caracteres.',
        'descripcion.required' => 'La descripción es obligatoria.',
        'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.',
        'clasificacion.max' => 'La clasificación no puede exceder los 20 caracteres.',
        'presentacion.max' => 'La presentación no puede exceder los 50 caracteres.',
        'unidad_solicitud.max' => 'La unidad de solicitud no puede exceder los 50 caracteres.',
        'precio_insumo.numeric' => 'El precio debe ser un número válido.',
        'precio_insumo.min' => 'El precio no puede ser negativo.',
        'precio_insumo.max' => 'El precio excede el límite permitido.',
        'conversion.numeric' => 'La conversión debe ser un número válido.',
        'conversion.min' => 'La conversión no puede ser negativa.',
        'conversion.max' => 'La conversión excede el límite permitido.',
        'fecha_ultima.date' => 'La fecha debe ser una fecha válida.',
      ]);

      try {
        $insumo->update($validated);

        return redirect()
          ->route('insumos.index')
          ->with('message', 'Insumo actualizado correctamente.')
          ->with('type', 'success');

      } catch (\Exception $e) {
        return redirect()
          ->back()
          ->withInput()
          ->with('message', 'Error al actualizar el insumo: ' . $e->getMessage())
          ->with('type', 'error');
      }
    }

    /**
     * Elimina un insumo.
     */
    public function destroy(Insumo $insumo)
    {
      try {
        $codigo = $insumo->codigo;
        $descripcion = $insumo->descripcion;

        $insumo->delete();

        return redirect()
          ->route('insumos.index')
          ->with('message', "Insumo '{$codigo} - {$descripcion}' eliminado correctamente.")
          ->with('type', 'success');

      } catch (\Exception $e) {
        return redirect()
          ->back()
          ->with('message', 'Error al eliminar el insumo: ' . $e->getMessage())
          ->with('type', 'error');
      }
    }


  public function create()
  {
    return inertia('Oficinas/Create', [
      'padres' => Oficina::habilitadas()->orderBy('nombre')->get(['id', 'nombre']),
    ]);


  }


  public function export(Request $request, $format = 'pdf')
  {
    $search = $request->get('search', '');

    // Obtener los insumos con filtros y relación
    $query = Insumo::with('clasifEconomica');

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('descripcion', 'LIKE', "%{$search}%")
          ->orWhere('codigo', 'LIKE', "%{$search}%")
          ->orWhere('clasificacion', 'LIKE', "%{$search}%")
          ->orWhereHas('clasifEconomica', function ($subq) use ($search) {
            $subq->where('descripcion', 'LIKE', "%{$search}%");
          });
      });
    }

    $insumos = $query->orderBy('descripcion')->get();

    if ($format === 'pdf') {
      return $this->exportToPDF($insumos, $search);
    } elseif ($format === 'excel') {
      return $this->exportToExcel($insumos, $search);
    }

    return redirect()->back()->with('error', 'Formato no válido');
  }

  /**
   * Export to PDF
   */
  private function exportToPDF($insumos, $search = '')
  {
    // Si tienes DomPDF instalado
    // composer require barryvdh/laravel-dompdf

    $pdf = app('dompdf.wrapper');

    $html = view('exports.insumos-pdf', [
      'insumos' => $insumos,
      'search' => $search,
      'fecha' => now()->format('d/m/Y H:i:s'),
      'total' => $insumos->count()
    ])->render();

    $pdf->loadHTML($html);
    $pdf->setPaper('A4', 'landscape');

    $filename = 'insumos_' . now()->format('Y-m-d_H-i-s') . '.pdf';

    return $pdf->download($filename);
  }

  /**
   * Export to Excel
   */
  private function exportToExcel($insumos, $search = '')
  {
    $filename = 'insumos_' . now()->format('Y-m-d_H-i-s') . '.csv';

    $headers = [
      'Content-Type' => 'text/csv; charset=utf-8',
      'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function() use ($insumos) {
      $file = fopen('php://output', 'w');

      // BOM para UTF-8
      fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

      // Headers CSV
      fputcsv($file, [
        'ID',
        'Código',
        'Descripción',
        'Clasificación',
        'Clasificación Económica',
        'Presentación',
        'Unidad Solicitud',
        'Precio',
        'Registrable',
        'Precio Testigo',
        'Inventariable',
        'Rinde Tribunal',
        'Conversión',
        'Fecha Última',
        'Creado',
        'Actualizado'
      ]);

      // Data
      foreach ($insumos as $insumo) {
        fputcsv($file, [
          $insumo->id,
          $insumo->codigo,
          $insumo->descripcion,
          $insumo->clasificacion,
          $insumo->clasifEconomica?->descripcion ?? '',
          $insumo->presentacion ?? '',
          $insumo->unidad_solicitud ?? '',
          $insumo->precio_insumo ?? '',
          $insumo->registrable ? 'Sí' : 'No',
          $insumo->precio_testigo ? 'Sí' : 'No',
          $insumo->inventariable ? 'Sí' : 'No',
          $insumo->rend_tribunal ? 'Sí' : 'No',
          $insumo->conversion ?? '',
          $insumo->fecha_ultima?->format('d/m/Y') ?? '',
          $insumo->created_at?->format('d/m/Y H:i:s'),
          $insumo->updated_at?->format('d/m/Y H:i:s')
        ]);
      }

      fclose($file);
    };

    return response()->stream($callback, 200, $headers);
  }
  /**
   * Toggle registrable status with SweetAlert confirmation
   */
  public function toggleRegistrable(Request $request, Insumo $insumo)
  {
    $validated = $request->validate([
      'registrable' => 'required|boolean'
    ]);

    try {
      $insumo->update(['registrable' => $validated['registrable']]);

      $estado = $validated['registrable'] ? 'registrable' : 'no registrable';

      return redirect()->back()->with('success', "Insumo marcado como {$estado} correctamente");

    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
    }
  }
  public function getStats()
  {
    $stats = [
      'total' => Insumo::count(),
      'registrables' => Insumo::where('registrable', true)->count(),
      'inventariables' => Insumo::where('inventariable', true)->count(),
      'precio_testigo' => Insumo::where('precio_testigo', true)->count(),
      'con_precio' => Insumo::whereNotNull('precio_insumo')->where('precio_insumo', '>', 0)->count(),
      'sin_precio' => Insumo::whereNull('precio_insumo')->orWhere('precio_insumo', '<=', 0)->count(),
      'precio_actualizado' => Insumo::whereNotNull('fecha_ultima')
        ->where('fecha_ultima', '>=', now()->subDays(30))
        ->count(),
      'precio_desactualizado' => Insumo::whereNotNull('fecha_ultima')
        ->where('fecha_ultima', '<', now()->subDays(30))
        ->count(),
    ];

    return response()->json($stats);
  }

}
